<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClinicCollection;
use App\Http\Resources\ClinicResource;
use App\Models\Clinic;
use App\Models\Country;
use App\Models\FadCity;
use App\Models\FadRegion;
use App\Models\FadState;
use App\Models\User;
use App\Models\WeekDay;
use App\Rules\LatitudeRule;
use App\Rules\LongitudeRule;
use App\Services\GoogleApi;
use App\Traits\ClinicTrait;
use App\Traits\CreateEntities;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClinicController extends Controller
{
    use ClinicTrait, CreateEntities;

    public function index(Request $request)
    {

        $shop = $request->user();

        $country = $shop->country;

        if (empty($country)) {
            return response(['message' => 'Please select country for this store'], 422);
        }

        $requestData = $request->all();

        $perPage = data_get($requestData, 'perPage') ?: 10;
        $search = data_get($requestData, 'search');

        $clinics = $shop->clinics()->with($this->relations());

        if ($search) {
            $clinics = $clinics->where(function ($query) use ($search) {
                $query->where('clinic_name', 'like', "%$search%")
                    ->orWhere('clinic_handle', 'like', "%$search%")
                    ->orWhere('doctor_name', 'like', "%$search%")
                    ->orWhere('doctor_handle', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%")
                    ->orWhere('title', 'like', "%$search%");
            });
        }

        $clinics = $clinics->paginate($perPage);

        return response(['clinics' => $clinics]);
    }

    public function show(Request $request, $clinic)
    {
        $shop = $request->user();

        $clinic = Clinic::findOrFail($clinic);

        $country = $shop->country;

        if (empty($country)) {
            return response(['message' => 'Please select country for this store'], 422);
        }

        $clinic->load($this->relations());

        return new ClinicResource($clinic);
    }

    public function sync(Request $request)
    {
        $shop = $request->user();

        $country = $shop->country;

        if (empty($country)) {
            return response(['message' => 'Please select country for this store'], 422);
        }

        $errors = [];

        foreach ($shop->languages as $language) {
            try {
                $namespace = "fad-$language->code";

                $clinics = $shop->clinics->load($this->relations());
                $clinics = new ClinicCollection($clinics);

                $clinics = $clinics->toArray($request);

                $ignoreState = in_array(strtolower(data_get($country, 'code')), ['nl', 'de', 'no']);

                foreach($clinics as $index => $clinic) {
                    $clinics[$index]['city_handle'] = $this->prepareCityPageHandle(data_get($clinic, 'city'), $ignoreState ? null : data_get($clinic, 'state'));
                }

                $clinics = collect($clinics);

                /*$stateLinks = [];
                $regionLinks = [];
                $googleApi = new GoogleApi($shop);
                $index = 0;
                foreach ($clinics->sortBy(['state', 'city'])->groupBy('state') as $state => $clinicGroup) {

                    if (!empty($state)) {
                        $locationInfo = $googleApi->getLocationInfo(['address' => "$state, $country->code"]);

                        if (empty($locationInfo)) continue;

                        $stateLinks[$index] = [
                            'state' => $state,
                            'handle' => str($state)->slug(),
                            'state_code' => data_get($locationInfo, 'state_code'),
                            'latitude' => data_get($locationInfo, 'latitude'),
                            'longitude' => data_get($locationInfo, 'longitude'),
                            'cities' => []
                        ];
                    } else {
                        $stateLinks[$index] = [
                            'state' => $state,
                            'handle' => str($state)->slug(),
                            'state_code' => null,
                            'latitude' => null,
                            'longitude' => null,
                            'cities' => []
                        ];
                    }

                    foreach (collect($clinicGroup)->unique('city') as $clinic) {
                        $stateLinks[$index]['cities'][] = [
                            'city' => data_get($clinic, 'city'),
                            'handle' => data_get($clinic, 'city_handle')
                        ];
                    }
                    $index++;
                }

                $index = 0;
                foreach ($clinics->sortBy(['region', 'city'])->groupBy('region') as $region => $clinicGroup) {

                    if (!empty($region)) {
                        $locationInfo = $googleApi->getLocationInfo(['address' => "$region, $country->code"]);

                        if (empty($locationInfo)) continue;

                        $regionLinks[$index] = [
                            'region' => $region,
                            'handle' => str($region)->slug(),
                            'latitude' => data_get($locationInfo, 'latitude'),
                            'longitude' => data_get($locationInfo, 'longitude'),
                            'cities' => []
                        ];
                    } else {
                        $regionLinks[$index] = [
                            'region' => $region,
                            'handle' => str($region)->slug(),
                            'latitude' => null,
                            'longitude' => null,
                            'cities' => []
                        ];
                    }

                    foreach (collect($clinicGroup)->unique('city') as $clinic) {
                        $regionLinks[$index]['cities'][] = [
                            'city' => data_get($clinic, 'city'),
                            'handle' => data_get($clinic, 'city_handle')
                        ];
                    }
                    $index++;
                }

                $dynamicCityLinks = [];
                if (!empty($regionLinks)) {
                    $dynamicCityLinks['regions'] = $regionLinks;
                }
                if (!empty($stateLinks)) {
                    $dynamicCityLinks['states'] = $stateLinks;
                }

                $cityClinics = [];
                foreach($clinics->groupBy(['city_handle']) as $cityHandle => $clinicGroup) {
                    $cityClinics[$cityHandle]['search'] = $this->prepareSearchText(data_get($clinicGroup, '0.city'), data_get($clinicGroup, '0.state'), $country->code);
                    $cityClinics[$cityHandle]['clinics'] = $clinicGroup;
                }

                $filters = [
                    'specialist_areas' => $clinics->pluck('specialist_areas')->flatten()->unique()->values(),
                    'specialist_area_titles' => $clinics->pluck('specialist_area_titles')->flatten()->unique()->values(),
                    'insurance_companies' => $clinics->pluck('insurance_companies')->flatten()->unique()->values(),
                    'diagnostic_services' => $clinics->pluck('diagnostic_services')->flatten()->unique()->values(),
                    'other_services' => $clinics->pluck('other_services')->flatten()->unique()->values(),
                    'clinic_types' => $clinics->pluck('clinic_types')->flatten()->unique()->values(),
                ];*/

                $data = [
                    [
                        'key' => 'doctors',
                        'value' => json_encode($clinics),
                    ],
                    /*[
                        'key' => 'city-doctors',
                        'value' => json_encode($cityClinics),
                    ],
                    [
                        'key' => 'filters',
                        'value' => json_encode($filters),
                    ],
                    [
                        'key' => 'linking-module',
                        'value' => json_encode($stateLinks),
                    ],
                    [
                        'key' => 'linking-module-dynamic',
                        'value' => json_encode($dynamicCityLinks),
                    ],
                    [
                        'namespace' => "pollen-$language->code",
                        'key' => 'linking-module-dynamic',
                        'value' => json_encode($dynamicCityLinks),
                    ],*/
                ];

                foreach ($data as $datum) {
                    $metafieldData = [
                        "metafield" => [
                            "namespace" => data_get($datum, 'namespace') ?: $namespace,
                            "key" => data_get($datum, 'key'),
                            "value" => data_get($datum, 'value', []),
                            "type" => "json"
                        ]
                    ];

                    $response = $shop->api()->rest('POST', '/admin/metafields.json', $metafieldData);

                    if (!empty(data_get($response, 'errors'))) {
                        $errors[] = [
                            'message' => data_get($response, 'body'),
                            'status' => data_get($response, 'status') ?: 500,
                        ];
                    }
                }
            }
            catch (\Exception $e) {
                $errors[] = [
                    'message' => $e->getMessage(),
                    'status' =>  500,
                ];
                continue;
            }
        }

        $status = !empty($errors) ? data_get($errors, '0.status') : 200;
        $message = !empty($errors) ? data_get($errors, '0.message') : 'Metafields synced successfully';
        return response(['message' => $message, 'errors' => $errors], $status);
    }

    public function modify(Request $request)
    {

        $request->validate([
            'city' => 'required',
            'latitude' => ['required', new LatitudeRule],
            'longitude' => ['required', new LongitudeRule],
            'country' => 'required',
            'specialist_areas.*.value' => 'required_with:specialist_areas',
            'insurance_companies.*.value' => 'required_with:insurance_companies',
            'other_services.*.value' => 'required_with:other_services',
            'diagnostic_services.*.value' => 'required_with:diagnostic_services',
            'clinic_types.*.value' => 'required_with:clinic_types',
        ]);

        $shop = $request->user();

        $languages = $shop->languages;
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        if (empty($defaultLanguage)) {
            return response(['message' => 'Please select a default language for this store']);
        }

        $defaultLanguageId = data_get($defaultLanguage, 'id');

        $country = $shop->country;

        if (empty($country)) {
            return response(['message' => 'Please select country for this store'], 422);
        }

        $clinic = DB::transaction(function () use ($request, $shop, $languages, $defaultLanguageId) {

            if ($request->input('id')) {
                $clinic = Clinic::findOrFail($request->input('id'));
            } else {
                $clinic = new Clinic();
            }

            $clinic = $this->saveClinic($clinic, $shop, $shop->country, $request->all(), true);
            $clinic->save();

            $clinicState = trim(data_get($clinic, 'state'));
            $clinicRegion = trim(data_get($clinic, 'region'));
            $clinicCity = trim(data_get($clinic, 'city'));

            $stateHandle = (string) str($clinicState)->slug();
            $regionHandle = (string) str($clinicRegion)->slug();
            $cityHandle = in_array($shop->name, [User::KLARIFY_US_STORE, User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE]) ? $this->prepareCityPageHandle($clinicCity, $clinicState) : (string) str($clinicCity)->slug();

            if ($shop->name === User::ALK_DK_STORE) {
                $regionHandle = (string) str($regionHandle)->start('region-');
            }

            $fadStateId = null;
            if (!empty($clinicState)) {
                $fadState = FadState::where([
                    ['user_id', $shop->id],
                    ['handle', $stateHandle],
                    ['language_id', $defaultLanguageId]
                ])->first();

                $prepareState = [
                    'user_id' => $shop->id,
                    'language_id' => $defaultLanguageId,
                    'has_static_content' => false,
                    'content' => null,
                    'enabled' => false,
                    in_array($shop->name, [User::KLARIFY_US_STORE, User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE]) ? 'code' : 'name' => $clinicState,
                    'handle' => $stateHandle,
                ];

                if (empty($fadState)) {
                    $fadState = FadState::create($prepareState);
                }

                $prepareState['parent_id'] = $fadStateId = data_get($fadState, 'id');

                foreach ($languages as $language) {
                    $fadStateNotExist = FadState::where([
                        ['parent_id', $fadStateId],
                        ['language_id', $language->id]
                    ])->doesntExist();

                    if (($language->id !== $defaultLanguageId) && $fadStateNotExist) {
                        $prepareState['language_id'] = $language->id;
                        FadState::create($prepareState);
                    }
                }
            }

            $fadRegionId = null;
            if (!empty($clinicRegion)) {
                $fadRegion = FadRegion::where([
                    ['user_id', $shop->id],
                    ['handle', $regionHandle],
                    ['language_id', $defaultLanguageId]
                ])->first();

                $prepareRegion = [
                    'user_id' => $shop->id,
                    'language_id' => $defaultLanguageId,
                    'has_static_content' => false,
                    'content' => null,
                    'enabled' => false,
                    'name' => $clinicRegion,
                    'handle' => $regionHandle,
                ];

                if (empty($fadRegion)) {
                    $fadRegion = FadRegion::create($prepareRegion);
                }

                $prepareRegion['parent_id'] = $fadRegionId = data_get($fadRegion, 'id');

                foreach ($languages as $language) {
                    $fadRegionNotExist = FadRegion::where([
                        ['parent_id', $fadRegionId],
                        ['language_id', $language->id]
                    ])->doesntExist();

                    if (($language->id !== $defaultLanguageId) && $fadRegionNotExist) {
                        $prepareRegion['language_id'] = $language->id;
                        FadRegion::create($prepareRegion);
                    }
                }
            }

            if (!empty($clinicCity)) {
                $fadCity = FadCity::where([
                    ['user_id', $shop->id],
                    ['fad_region_id', $fadRegionId],
                    ['fad_state_id', $fadStateId],
                    ['handle', $cityHandle],
                    ['language_id', $defaultLanguageId]
                ])->first();

                $prepareCity = [
                    'user_id' => $shop->id,
                    'fad_state_id' => $fadStateId,
                    'fad_region_id' => $fadRegionId,
                    'language_id' => $defaultLanguageId,
                    'has_static_content' => false,
                    'content' => null,
                    'enabled' => false,
                    'name' => $clinicCity,
                    'handle' => $cityHandle,
                ];

                if (empty($fadCity)) {
                    $fadCity = FadCity::create($prepareCity);
                }

                $prepareCity['parent_id'] = $fadCityId = data_get($fadCity, 'id');

                foreach ($languages as $language) {
                    $fadCityNotExist = FadCity::where([
                        ['parent_id', $fadCityId],
                        ['language_id', $language->id]
                    ])->doesntExist();

                    if (($language->id !== $defaultLanguageId) && $fadCityNotExist) {
                        $prepareCity['language_id'] = $language->id;
                        FadCity::create($prepareCity);
                    }
                }
            }

            $timings = $request->input('timings') ?: [];
            foreach ($timings as $timingIndex => $timing) {
                $openingHours = data_get($timing, 'opening_hours') ?: [];
                foreach ($openingHours as $openingHourIndex => $openingHour) {
                   if (!empty($openingHour)) {
                       if (empty(data_get($openingHour, 'opening_second'))) {
                           $openingHours[$openingHourIndex]['opening_second'] = $this->getSeconds(data_get($openingHour, 'opening_time'));
                       }
                       if (empty(data_get($openingHour, 'closing_second'))) {
                           $openingHours[$openingHourIndex]['closing_second'] = $this->getSeconds(data_get($openingHour, 'closing_time'));
                       }
                       $openingHours[$openingHourIndex]['optional'] = data_get($openingHours[$openingHourIndex], 'optional') ?: false;
                   } else {
                       unset($openingHours[$openingHourIndex]);
                   }
                }
                $timings[$timingIndex]['opening_hours'] = array_values($openingHours);
            }

            $data = [
                'specialist_areas' => collect($request->input('specialist_areas'))->pluck('value')->toArray(),
                'insurance_companies' => collect($request->input('insurance_companies'))->pluck('value')->toArray(),
                'other_services' => collect($request->input('other_services'))->pluck('value')->toArray(),
                'diagnostic_services' => collect($request->input('diagnostic_services'))->pluck('value')->toArray(),
                'clinic_types' => collect($request->input('clinic_types'))->pluck('value')->toArray(),
                'timings' => $timings
            ];

            $weekDays = WeekDay::get()->keyBy('index');

            $this->createEntities($clinic, $data, $weekDays);

            return $clinic;
        });

        return response(['clinic' => $clinic]);
    }

    public function entities(Request $request)
    {
        $shop = $request->user();
        $clinics = $shop->clinics->load($this->relations());

        $specialistAreas = $clinics->pluck('specialistAreas')->flatten()->pluck('name')->unique()->values()->toArray();
        $otherServices = $clinics->pluck('otherServices')->flatten()->pluck('name')->unique()->values()->toArray();
        $insuranceCompanies = $clinics->pluck('insuranceCompanies')->flatten()->pluck('name')->unique()->values()->toArray();
        $diagnosticServices = $clinics->pluck('diagnosticServices')->flatten()->pluck('name')->unique()->values()->toArray();
        $clinicTypes = $clinics->pluck('clinicTypes')->flatten()->pluck('name')->unique()->values()->toArray();
        $weekDays = WeekDay::orderBy('index')->get()->keyBy('index');
        $countries = Country::get();

        return response([
            'specialist_areas' => $specialistAreas,
            'other_services' => $otherServices,
            'insurance_companies' => $insuranceCompanies,
            'diagnostic_services' => $diagnosticServices,
            'clinic_types' => $clinicTypes,
            'week_days' => $weekDays,
            'countries' => $countries
        ]);
    }
}
