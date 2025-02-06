<?php

namespace App\Services\Clinic;

use App\Models\Clinic;
use App\Models\Country;
use App\Models\FadCity;
use App\Models\FadRegion;
use App\Models\FadState;
use App\Models\User;
use App\Models\WeekDay;
use App\Services\Datahub;
use App\Traits\CreateEntities;
use Illuminate\Support\Facades\DB;
use App\Traits\ClinicTrait;

class DatahubClinic
{
    use CreateEntities, ClinicTrait;

    public function __construct($country)
    {
        $this->country = Country::with('shops')->where('code', strtolower($country))->firstOrFail();
        $this->shops = $this->country->shops;
    }

    public function fetchData()
    {

        $http = new Datahub();
        $params = [
            'country' => $this->country->code,
            'all' => 1
        ];

        $response = [];

        try {

            $response = $http->api('get', '/clinics', $params);
            if ($response->successful()) {
                $response = $response->json();
                $response = data_get($response, 'clinics');
            } else {
                $response['error'] = true;
            }

            return $response;

        } catch (\Exception $e) {

            $response['error'] = true;
            report($e);
        }

        return $response;
    }

    public function syncData()
    {

        $data = $this->fetchData();

        if (!empty(data_get($data, 'error'))) {
            return;
        }

        $weekDays = WeekDay::get()->keyBy('index');

        foreach ($this->shops as $shop) {
            DB::transaction(function () use ($shop, $data, $weekDays) {

                $shop->clinics()->where(function ($query) use ($shop) {
                    $query->whereNotNull('datahub_clinic_id')
                          ->orWhereNot('country_id', $shop->country_id);
                })->delete();

                $languages = $shop->languages;
                $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
                if (empty($defaultLanguage)) return;

                $fadLanguageCities = $fadLanguageRegions = $fadLanguageStates = [];
                foreach ($languages as $language) {
                    if ($language->id !== $defaultLanguage->id) {
                        $fadLanguageCities[$language->id] = $shop->fadCities->where('language_id', $language->id)->whereNotNull('parent_id')->keyBy('parent_id');
                        $fadLanguageRegions[$language->id] = $shop->fadRegions->where('language_id', $language->id)->whereNotNull('parent_id')->keyBy('parent_id');
                        $fadLanguageStates[$language->id] = $shop->fadStates->where('language_id', $language->id)->whereNotNull('parent_id')->keyBy('parent_id');
                    }
                }

                $fadCities = $shop->fadCities->where('language_id', $defaultLanguage->id)->keyBy('handle');
                $fadRegions = $shop->fadRegions->where('language_id', $defaultLanguage->id)->keyBy('handle');
                $fadStates = $shop->fadStates->where('language_id', $defaultLanguage->id)->keyBy('handle');

                $fadCityIds = $fadRegionIds = $fadStateIds = [];

                foreach ($data as $index => $datum) {

                    $clinic = new Clinic();
                    $clinic = $this->saveClinic($clinic, $shop, $this->country, $datum);
                    $clinic->save();

                    $this->createEntities($clinic, $datum, $weekDays);

                    $city = trim(data_get($datum, 'city'));
                    $region = trim(data_get($datum, 'region'));
                    $state = trim(data_get($datum, 'state'));

                    $cityHandle = in_array($shop->name, [User::KLARIFY_US_STORE, User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE]) ? $this->prepareCityPageHandle($city, $state) : (string) str($city)->slug();
                    $regionHandle = (string) str($region)->slug();
                    $stateHandle = (string) str($state)->slug();

                    if ($shop->name === User::ALK_DK_STORE) {
                        $regionHandle = (string) str($regionHandle)->start('region-');
                    }

                    $dbFadCity = data_get($fadCities, $cityHandle);
                    $dbFadRegion = data_get($fadRegions, $regionHandle);
                    $dbFadState = data_get($fadStates, $stateHandle);

                    if (!empty($stateHandle)) {
                        $prepareState = [
                            'user_id' => $shop->id,
                            in_array($shop->name, [User::KLARIFY_US_STORE, User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE]) ? 'code' : 'name' => $state,
                            'handle' => $stateHandle,
                        ];

                        if (empty($dbFadState)) {
                            $prepareState['language_id'] = $defaultLanguage->id;
                            $dbFadState = FadState::create($prepareState);
                            $fadStates[$stateHandle] = $dbFadState;
                        }

                        foreach ($fadLanguageStates as $languageId => $childStates) {
                            $childStateExists = data_get($childStates, $dbFadState->id);
                            if (empty($childStateExists)) {
                                $prepareState['language_id'] = $languageId;
                                $prepareState['parent_id'] = $dbFadState->id;
                                $dbFadChildState = FadState::create($prepareState);
                                $fadLanguageStates[$languageId][$dbFadState->id] = $dbFadChildState;
                            }
                        }
                        $fadStateIds[] = data_get($dbFadState, 'id');
                    }

                    if (!empty($regionHandle)) {
                        $prepareRegion = [
                            'user_id' => $shop->id,
                            'name' => $region,
                            'handle' => $regionHandle,
                        ];

                        if (empty($dbFadRegion)) {
                            $prepareRegion['language_id'] = $defaultLanguage->id;
                            $dbFadRegion = FadRegion::create($prepareRegion);
                            $fadRegions[$regionHandle] = $dbFadRegion;
                        }
                        foreach ($fadLanguageRegions as $languageId => $childRegions) {
                            $childRegionExists = data_get($childRegions, $dbFadRegion->id);
                            if (empty($childRegionExists)) {
                                $prepareRegion['language_id'] = $languageId;
                                $prepareRegion['parent_id'] = $dbFadRegion->id;
                                $dbFadChildRegion = FadRegion::create($prepareRegion);
                                $fadLanguageRegions[$languageId][$dbFadRegion->id] = $dbFadChildRegion;
                            }
                        }
                        $fadRegionIds[] = data_get($dbFadRegion, 'id');
                    }

                    if (!empty($cityHandle)) {
                        $prepareCity = [
                            'user_id' => $shop->id,
                            'name' => $city,
                            'handle' => $cityHandle,
                        ];

                        $stateId = data_get($dbFadState, 'id') ?: null;
                        $regionId = data_get($dbFadRegion, 'id') ?: null;

                        if (empty($dbFadCity)) {
                            $prepareCity['language_id'] = $defaultLanguage->id;
                            $prepareCity['fad_state_id'] = $stateId;
                            $prepareCity['fad_region_id'] = $regionId;

                            $dbFadCity = FadCity::create($prepareCity);
                            $fadCities[$cityHandle] = $dbFadCity;
                        }

                        foreach ($fadLanguageCities as $languageId => $childCities) {
                            $childCityExists = data_get($childCities, $dbFadCity->id);
                            if (empty($childCityExists)) {
                                $prepareCity['language_id'] = $languageId;
                                $prepareCity['parent_id'] = $dbFadCity->id;

                                $prepareCity['fad_state_id'] = $stateId ? (data_get($fadLanguageStates, "$languageId.$stateId.id") ?: null) : null;
                                $prepareCity['fad_region_id'] = $regionId ? (data_get($fadLanguageRegions, "$languageId.$regionId.id") ?: null) : null;
                                $dbFadChildCity = FadCity::create($prepareCity);
                                $fadLanguageCities[$languageId][$dbFadCity->id] = $dbFadChildCity;
                            }
                        }
                        $fadCityIds[] = data_get($dbFadCity, 'id');
                    }
                }

                $languageIds = $languages->pluck('id')->toArray();
                $shop->fadCities()->where(function ($query) use ($fadCityIds, $languageIds) {
                    $query->where(function ($q) use ($fadCityIds) {
                        $q->whereNull('parent_id')
                            ->whereNotIn('id', $fadCityIds);
                    })->orWhereNotIn('language_id', $languageIds);
                })->delete();

                $shop->fadStates()->where(function ($query) use ($fadStateIds, $languageIds) {
                    $query->where(function ($q) use ($fadStateIds) {
                        $q->whereNull('parent_id')
                            ->whereNotIn('id', $fadStateIds);
                    })->orWhereNotIn('language_id', $languageIds);
                })->delete();

                $shop->fadRegions()->where(function ($query) use ($fadRegionIds, $languageIds) {
                    $query->where(function ($q) use ($fadRegionIds) {
                        $q->whereNull('parent_id')
                            ->whereNotIn('id', $fadRegionIds);
                    })->orWhereNotIn('language_id', $languageIds);
                })->delete();
            });
        }
    }
}
