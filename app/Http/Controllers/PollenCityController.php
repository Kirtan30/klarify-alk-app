<?php

namespace App\Http\Controllers;

use App\Models\PollenCity;
use Illuminate\Http\Request;
use App\Rules\LatitudeRule;
use App\Rules\LongitudeRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PollenCityController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();
        $perPage = data_get($request, 'perPage') ?: 10;
        $search = data_get($request, 'search');
        $languageId = data_get($request, 'languageId');

        $pollenCities = PollenCity::where('user_id', $shop->id)->with(['pollenRegion', 'pollenState']);

        if (!empty($languageId)) {
            $pollenCities =  $pollenCities->where('language_id', $languageId);
        }

        if (!empty($search)) {
            $pollenCities = $pollenCities->where(function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('handle', 'like', "%$search%");
            });
        }
        $pollenCities = $pollenCities->paginate($perPage);

        $shopLanguages = $shop->languages->mapWithKeys(function ($item, $key) {
            return [$item['id'] => $item['name']];
        });

        return response(['pollenCities' => $pollenCities, 'shopLanguages' => $shopLanguages], 200);
    }

    public function show(Request $request, PollenCity $pollenCity)
    {
        $pollenCity->load(['pollenState', 'pollenRegion', 'pollenPageContent', 'pollenLanguage', 'pollenParent']);
        return response(['pollenCity' => $pollenCity], 200);
    }

    public function store(Request $request)
    {
        $shop = $request->user();
        $languageId = data_get($request, 'language_id');
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');
        $parentId = data_get($request, 'parent_id');

        $request->validate([
            'language_id' => [
                'required',
                Rule::exists('user_language', 'language_id')->where('user_id', $shop->id)
            ],
            'pollen_state_id' => 'sometimes|exists:pollen_states,id',
            'pollen_region_id' => 'sometimes|exists:pollen_regions,id',
            'name' => 'required',
            'handle' => [Rule::unique('pollen_cities')->where('user_id', $shop->id)->where('language_id', $languageId), 'required'],
            'pollen_page_content_id' => 'required_if:has_static_content,true',
            'parent_id' => array_filter([
                'sometimes',
                $parentId ? Rule::exists('pollen_cities', 'id')->where('user_id', $shop->id) : null,
                $parentId ? Rule::prohibitedIf($defaultLanguageId === $languageId) : null
            ]),
            'latitude' => ['required', new LatitudeRule],
            'longitude' => ['required', new LongitudeRule],
        ],
        [
            'language_id.required' => 'Language field is required',
            'parent_id.prohibited' => 'Parent id cannot be added to default language'
        ]);

        $pollenCity = DB::transaction(function () use ($request, $shop, $languageId) {
            return PollenCity::create([
                'user_id' => $shop->id,
                'language_id' => $languageId,
                'pollen_state_id' => $request->input('pollen_state_id') ?: null,
                'pollen_region_id' => $request->input('pollen_region_id') ?: null,
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'latitude' => $request->input('latitude') ?: null,
                'longitude' => $request->input('longitude') ?: null,
                'has_static_content' => $request->input('has_static_content') ?: false,
                'pollen_page_content_id' => data_get($request, 'has_static_content') ? $request->input('pollen_page_content_id') : null,
                'parent_id' => $request->input('parent_id') ?: null,
                'variables' => $request->input('variables') ?: null,
                'is_popular' => $request->input('is_popular') ?: false,
            ]);
        });

        return response(['pollenCity' => $pollenCity, 'message' => 'Pollen city saved successfully'], 200);
    }

    public function update(Request $request, PollenCity $pollenCity)
    {
        $shop = $request->user();
        $languageId = data_get($request, 'language_id');
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');
        $parentId = data_get($request, 'parent_id');

        $request->validate([
            'language_id' => [
                'required',
                Rule::exists('user_language', 'language_id')->where('user_id', $shop->id)
            ],
            'pollen_state_id' => 'sometimes|exists:pollen_states,id',
            'pollen_region_id' => 'sometimes|exists:pollen_regions,id',
            'name' => 'required',
            'handle' => [Rule::unique('pollen_cities')->where('user_id', $shop->id)->where('language_id', $languageId)->ignore($pollenCity), 'required'],
            'pollen_page_content_id' => 'required_if:has_static_content,true',
            'parent_id' => array_filter([
                'sometimes',
                $parentId ? Rule::exists('pollen_cities', 'id')->where('user_id', $shop->id) : null,
                $parentId ? Rule::prohibitedIf($defaultLanguageId === $languageId) : null
            ]),
            'latitude' => ['required', new LatitudeRule],
            'longitude' => ['required', new LongitudeRule],
        ],
        [
            'language_id.required' => 'Language field is required',
            'parent_id.prohibited' => 'Parent id cannot be added to default language'
        ]);

        $pollenCity = DB::transaction(function () use ($request, $shop, $pollenCity, $languageId) {
            $pollenCity->update([
                'language_id' => $languageId,
                'pollen_state_id' => $request->input('pollen_state_id') ?: null,
                'pollen_region_id' => $request->input('pollen_region_id') ?: null,
                'name' => $request->input('name'),
                'handle' => str($request->input('handle'))->slug(),
                'latitude' => $request->input('latitude') ?: null,
                'longitude' => $request->input('longitude') ?: null,
                'has_static_content' => $request->input('has_static_content') ?: false,
                'pollen_page_content_id' => data_get($request, 'has_static_content') ? $request->input('pollen_page_content_id') : $pollenCity->pollen_page_content_id,
                'parent_id' => $request->input('parent_id') ?: null,
                'variables' => $request->input('variables') ?: null,
                'is_popular' => $request->input('is_popular') ?: false,
            ]);

            return $pollenCity;
        });

        return response(['pollenCity' => $pollenCity, 'message' => 'Pollen city updated successfully'], 200);
    }

    public function delete(PollenCity $pollenCity) {
        $pollenCity->delete();
        return response(['message' => 'deleted successfully'], 200);
    }

    public function defaultCities(Request $request) {
        $shop = $request->user();
        if (empty($shop)) return;

        $pollenCityId = data_get($request, 'pollenCityId');
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');

        $defaultCities = PollenCity::where('user_id', $shop->id)->where('language_id', $defaultLanguageId);
        if (!empty($pollenCityId)) {
            $defaultCities = $defaultCities->where('id', '!=', $pollenCityId);
        }

        $defaultCities = $defaultCities->get();
        return response(['defaultCities' => $defaultCities], 200);
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
                $namespace = "pollen-$language->code";

                $stateLinks = [];
                $regionLinks = [];
                $regions = $shop->pollenRegions()
                    ->where('language_id', $language->id)
                    ->with(['pollenCities' => function ($query) use ($language) {
                    $query->where('language_id', $language->id)->orderBy('handle');
                }])->orderBy('handle')->get();

                $states = $shop->pollenStates()
                    ->where('language_id', $language->id)
                    ->with(['pollenCities' => function ($query) use ($language) {
                    $query->where('language_id', $language->id)->orderBy('handle');
                }])->orderBy('handle')->get();

                $cities = $shop->pollenCities()
                    ->where('language_id', $language->id)
                    ->whereNull('pollen_state_id')->whereNull('pollen_region_id')
                    ->orderBy('handle')->get();

                foreach ($states as $index => $state) {

                    $stateLinks[$index] = [
                        'state' => $state->name,
                        'handle' => $state->handle,
                        'latitude' => data_get($state, 'latitude'),
                        'longitude' => data_get($state, 'longitude'),
                        'is_popular' => data_get($state, 'is_popular') ?: false,
                        'cities' => []
                    ];


                    foreach ($state->pollenCities as $city) {
                        $stateLinks[$index]['cities'][] = [
                            'city' => data_get($city, 'name'),
                            'handle' => data_get($city, 'handle'),
                            'latitude' => data_get($city, 'latitude'),
                            'longitude' => data_get($city, 'longitude'),
                            'is_popular' => data_get($city, 'is_popular') ?: false,
                        ];
                    }
                }

                foreach ($regions as $index => $region) {

                    $regionLinks[$index] = [
                        'region' => $region->name,
                        'handle' => $region->handle,
                        'latitude' => data_get($region, 'latitude'),
                        'longitude' => data_get($region, 'longitude'),
                        'is_popular' => data_get($region, 'is_popular') ?: false,
                        'cities' => []
                    ];


                    foreach ($region->pollenCities as $city) {
                        $regionLinks[$index]['cities'][] = [
                            'city' => data_get($city, 'name'),
                            'handle' => data_get($city, 'handle'),
                            'latitude' => data_get($city, 'latitude'),
                            'longitude' => data_get($city, 'longitude'),
                            'is_popular' => data_get($city, 'is_popular') ?: false,
                        ];
                    }
                }

                $preparedCities = [];
                foreach ($cities as $city) {
                    $preparedCities[] = [
                        'city' => data_get($city, 'name'),
                        'handle' => data_get($city, 'handle'),
                        'latitude' => data_get($city, 'latitude'),
                        'longitude' => data_get($city, 'longitude'),
                        'is_popular' => data_get($city, 'is_popular') ?: false,
                    ];
                }

                if (!empty($preparedCities)) {
                    $regionLinks[] = [
                        'region' => '',
                        'handle' => '',
                        'latitude' => null,
                        'longitude' => null,
                        'is_popular' => false,
                        'cities' => $preparedCities
                    ];

                    $stateLinks[] = [
                        'state' => '',
                        'handle' => '',
                        'latitude' => null,
                        'longitude' => null,
                        'is_popular' => false,
                        'cities' => $preparedCities
                    ];
                }

                $staticCityLinks = [];
                if (!empty($regionLinks)) {
                    $staticCityLinks['regions'] = $regionLinks;
                }
                if (!empty($stateLinks)) {
                    $staticCityLinks['states'] = $stateLinks;
                }

                $data = [
                    [
                        'key' => 'linking-module-static',
                        'value' => json_encode($staticCityLinks),
                    ],
                ];

                foreach ($data as $datum) {
                    $metafieldData = [
                        "metafield" => [
                            "namespace" => $namespace,
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
}
