<?php

namespace App\Helpers;

use App\Http\Resources\ClinicCollection;
use App\Models\Clinic;
use App\Models\FadRegion;
use App\Models\User;

class ProxyHelper
{
    const TYPE_CLINIC = 'clinic';
    const TYPE_POLLEN = 'pollen';
    const TYPE_FAD = 'fad';
    const TYPE_CLINIC_INDEX = 'clinic_index';
    const TYPE_FAD_REGION = 'fad_region';

    static function getDomain($shop) {
        return $shop->public_domain ?: $shop->name;
    }

    static function getUrl($shop, $handle, $prefix = null, $type = null, $languageCode = null, $defaultLanguageCode = null) {
        if (!$prefix) {
            $prefix = self::getUrlPrefix($shop, $type, $languageCode, $defaultLanguageCode);
        }

        $domain = self::getDomain($shop);
        return "https://$domain/apps/pages/$prefix/$handle";
    }

    static function getUrlPrefix($shop, $type, $languageCode, $defaultLanguageCode = null) {
        $languageCode = strtolower($languageCode);
        $defaultLanguageCode = $defaultLanguageCode ? strtolower($defaultLanguageCode) : null;

        $subDomain = getSubdomain($shop->name);
        $key = "$type.$subDomain.url_prefix";
        $urlPrefix = getLocale($key, $languageCode);
        $defaultUrlPrefix = $defaultLanguageCode ? getLocale($key, $defaultLanguageCode) : null;

        return $urlPrefix ?: $defaultUrlPrefix;
    }

    static function getClinicName($clinic) {
        return trim(data_get($clinic, 'clinic_name'));
    }

    static function getDoctorName($clinic) {
        return trim(data_get($clinic, 'doctor_name'));
    }

    static function getRegionName($clinic) {
        return trim(data_get($clinic, 'region'));
    }

    static function getClinicFullName($clinic, $separator = ' ') {
        $name = [];
        $clinicName = self::getClinicName($clinic);
        $doctorName = self::getDoctorName($clinic);

        if (!empty($clinicName)) {
            $name[] = $clinicName;
        }
        if (!empty($doctorName)) {
            $name[] = $doctorName;
        }

        return implode($separator, $name);
    }

    static function getClinicDisplayName($clinic) {

        $isDoctor = self::isDoctor($clinic);
        $clinicName = self::getClinicName($clinic);
        $doctorName = self::getDoctorName($clinic);
        $name = self::getClinicFullName($clinic);

        $displayName = $isDoctor ? $doctorName : $clinicName;
        return empty($displayName) ? $name : $displayName;
    }

    static function getClinicTypes($clinic) {
        return data_get($clinic, 'clinic_types') ?: [];
    }

    static function isDoctor($clinic) {
        return (boolean) data_get($clinic, 'is_doctor');
    }

    static function getClinicHandle($clinic) {
        return self::isDoctor($clinic) ? data_get($clinic, 'doctor_handle') : data_get($clinic, 'clinic_handle');
    }

    static function getClinics($shop, $withRegion = false) {
        return ($withRegion ? $shop->clinics()->whereNotNull('region')->orderBy('region')->get() : $shop->clinics()->whereNotNull('city')->orderBy('city')->get()) ?: [];
    }

    static function getClinicsRegions($clinics) {
        $regions = [];
        foreach ($clinics as $clinic) {
            $region = strtolower(data_get($clinic, 'region'));
            if (!empty($region)) {
                $regions[$region][] = $clinic;
            }
        }

        return $regions;
    }

    static function getClinicsCities($clinics) {
        $cities = [];
        foreach ($clinics as $clinic) {
            $city = strtolower(data_get($clinic, 'city'));
            if (!empty($city)) {
                $cities[$city][] = $clinic;
            }
        }

        return $cities;
    }

    static function getClinicCity($clinic) {
        return trim(data_get($clinic, 'city'));
    }

    static function getClinicStateName($shop, $clinic, $language) {

        $clinicState = trim(data_get($clinic, 'state'));

        $stateCodeMarkets = [
            User::KLARIFY_US_STORE,
            User::ALK_ODACTRA_STORE,
            User::ALK_RAGWITEK_STORE,
            User::ALK_GRASTEK_STORE
        ];

        if (!empty($clinicState) && in_array($shop->name, $stateCodeMarkets)) {
            $state = $shop->fadStates()
                ->where('language_id', data_get($language, 'id'))
                ->where('code', $clinicState)->first();

            return data_get($state, 'name') ?: str($clinicState)->upper();
        }

        return $clinicState;
    }

    static function getClinicRegion($clinic, $languageCode) {
        $region = null;
        $regionName = self::getRegionName($clinic);

        if (!empty($regionName)) {
            $region = FadRegion::where([
                ['name', $regionName],
                ['language_id', $languageCode]
            ])->first();
        }

        return $region;
    }

    static function getLanguageUrls($shop, $type, $handle = null, $city = null) {

        $languages = data_get($shop, 'languages') ?: collect([]);
        $languageUrls = [];

        $subDomain = getSubdomain($shop->name);
        $key = "$type.$subDomain.url_prefix";

        $handles = $city && in_array($type, [self::TYPE_FAD, self::TYPE_POLLEN]) ? self::getLanguageHandles($languages, $city) : [];

        $languageCodes = $languages->pluck('code')->toArray();

        foreach($languageCodes as $languageCode) {
            $urlPrefix = getLocale($key, $languageCode);
            if ($urlPrefix) {
                $languageUrls[$languageCode] = self::getUrl(
                    $shop,
                    $handles ? data_get($handles, $languageCode) : $handle,
                    $urlPrefix
                );
            }
        }

        return $languageUrls;
    }

    static function getLanguageHandles($languages, $city) {
        $languageCodes = $languages->pluck('code', 'id')->toArray();
        $languageCities = [];

        $parentCity = data_get($city, 'parentCity') ?: data_get($city, 'pollenParent');
        if ($parentCity) {
            $languageCities[data_get($languageCodes, data_get($parentCity, 'language_id'))] = data_get($parentCity, 'handle');
        } else {
            $languageCities[data_get($languageCodes, data_get($city, 'language_id'))] = data_get($city, 'handle');
        }

        foreach (data_get($parentCity ?: $city, 'childCities') ?: [] as $childCity) {
            $languageCities[data_get($languageCodes, data_get($childCity, 'language_id'))] = data_get($childCity, 'handle');
        }

        return $languageCities;
    }

    static function getFadStates($shop, $language, $city = []) {
        $states = $shop->fadStates()
            ->where('language_id', $language->id)
            ->with(['fadCities' => function ($query) use ($language) {
                $query->where('language_id', $language->id)->orderBy('handle');
            }])->whereHas('fadCities')->orderBy('handle')->get();

        if ($city && !empty(data_get($city, 'fadState')) && data_get($city, 'fadState.latitude') && data_get($city, 'fadState.longitude')) {
            $states = $shop->fadStates()
                ->where('language_id', $language->id)
                ->selectRaw(
                    'fad_states.*, (6371000 * acos(cos(radians(?)) * cos(radians(fad_states.latitude)) * cos(radians(fad_states.longitude) - radians(?)) + sin(radians(?)) * sin(radians(fad_states.latitude)))) AS distance',
                    [data_get($city, 'fadState.latitude'), data_get($city, 'fadState.longitude'), data_get($city, 'fadState.latitude')]
                )->with(['fadCities' => function ($query) use ($language) {
                    $query->where('language_id', $language->id)->orderBy('handle');
                }])->whereHas('fadCities')->orderBy('distance')->orderBy('handle')->get();
        }

        return $states;
    }

    static function getPollenStates($shop, $language, $city = []) {
        $states = $shop->pollenStates()
            ->where('language_id', $language->id)
            ->with(['pollenCities' => function ($query) use ($language) {
                $query->where('language_id', $language->id)->orderBy('handle');
            }])->whereHas('pollenCities')->orderBy('handle')->get();

        if (!empty($city->pollenState) && data_get($city, 'pollenState.latitude') && data_get($city, 'pollenState.longitude')) {
            $states = $shop->pollenStates()
                ->where('language_id', $language->id)
                ->selectRaw(
                    'pollen_states.*, (6371000 * acos(cos(radians(?)) * cos(radians(pollen_states.latitude)) * cos(radians(pollen_states.longitude) - radians(?)) + sin(radians(?)) * sin(radians(pollen_states.latitude)))) AS distance',
                    [data_get($city, 'pollenState.latitude'), data_get($city, 'pollenState.longitude'), data_get($city, 'pollenState.latitude')]
                )->with(['pollenCities' => function ($query) use ($language) {
                    $query->where('language_id', $language->id)->orderBy('handle');
                }])->whereHas('pollenCities')->orderBy('distance')->orderBy('handle')->get();
        }

        return $states;
    }

    static function getCityClinics($request, $shop, $city) {
        $clinicCity = !empty($city->parent_id) ? (data_get($city, 'parentCity.name')) : (data_get($city, 'name'));

        $clinics = $shop->clinics()
            ->where('city', $clinicCity)
            ->with(Clinic::RELATIONS)
            ->get();

        $clinics = !empty($clinics) ? new ClinicCollection($clinics) : [];
        if (!empty($clinics)) {
            $clinics = $clinics->toArray($request);
        }

        return $clinics;
    }

    static function getRegionClinics($request, $shop, $region) {
        $clinicRegion = !empty($region->parent_id) ? (data_get($region, 'parentRegion.name')) : (data_get($region, 'name'));

        $clinics = $shop->clinics()
            ->where('region', $clinicRegion)
            ->with(Clinic::RELATIONS)
            ->get();

        $clinics = !empty($clinics) ? new ClinicCollection($clinics) : [];
        if (!empty($clinics)) {
            $clinics = $clinics->toArray($request);
        }

        return $clinics;
    }
}
