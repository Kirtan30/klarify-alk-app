<?php

namespace App\Jobs;

use App\Services\GoogleApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncMetafields implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $shop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $country = $this->shop->country;

        if (empty($country)) {
            Log::error('Country is required');
            return;
        }

        $errors = [];

        foreach ($this->shop->languages as $language) {
            try {
                $namespace = "fad-$language->code";

                $stateLinks = $regionLinks = $enabledStateLinks = $enabledRegionLinks = $stateLatLong = $regionLatLong = [];
                $updateStates = [];
                $updateRegions = [];
                $updateCities = [];
                $googleApi = new GoogleApi($this->shop);

                $allCities = $this->shop->fadCities()->where('language_id', $language->id)->with(['fadState','fadRegion', 'parentCity'])->orderBy('handle')->get();

                foreach ($allCities as $index => $city) {

                    $latitude = data_get($city, 'latitude');
                    $longitude = data_get($city, 'longitude');
                    $parentTerritory = $city->fadState ? ($city->fadState->name ?: $city->fadState->code) : null;
                    $parentTerritory = $parentTerritory ?: ($city->fadRegion ? $city->fadRegion->name : null);

                    if (empty($latitude) || empty($longitude)) {
                        if ($city->parent_id) {
                            $latitude = data_get($city, 'parentCity.latitude');
                            $longitude = data_get($city, 'parentCity.longitude');

                        } else {
                            $locationInfo = $googleApi->getLocationInfo(['address' => implode(
                                    ', ',  array_filter([
                                        $city->name,
                                        $parentTerritory,
                                        $country->code
                                    ])
                                )]
                            );
                            sleep(1);

                            $latitude = data_get($locationInfo, 'latitude');
                            $longitude = data_get($locationInfo, 'longitude');
                        }

                        if (empty($latitude) || empty($longitude)) continue;

                        $updateCities[] = [
                            'id' => $city->id,
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                        ];

                        $city->latitude = $latitude;
                        $city->longitude = $longitude;
                        $allCities[$index] = $city;
                    }
                }

                bulkUpdate('fad_cities', $updateCities);

                sleep(1);

                $regions = $this->shop->fadRegions()->where('language_id', $language->id)->with(['fadCities' => function ($query) {
                    $query->orderBy('handle');
                }, 'parentRegion'])->orderBy('handle')->get();

                $states = $this->shop->fadStates()->where('language_id', $language->id)->with(['fadCities' => function ($query) {
                    $query->orderBy('handle');
                }, 'parentState'])->orderBy('handle')->get();

                $cities = $allCities->whereNull('fad_state_id')
                    ->whereNull('fad_region_id')
                    ->where('language_id', $language->id)
                    ->where('enabled', true);

                foreach ($states as $index => $state) {

                    $latitude = data_get($state, 'latitude');
                    $longitude = data_get($state, 'longitude');
                    $stateName = data_get($state, 'name');

                    if (empty($latitude) || empty($longitude)) {
                        if ($state->parent_id) {
                            $latitude = data_get($state, 'parentState.latitude');
                            $longitude = data_get($state, 'parentState.longitude');

                            if (empty($stateName)) {
                                $stateName = data_get($state, 'parentState.name');
                            }
                        } else {
                            $locationInfo = $googleApi->getLocationInfo(['address' => implode(', ', [$state->name ?: $state->code , $country->code])]);
                            $latitude = data_get($locationInfo, 'latitude');
                            $longitude = data_get($locationInfo, 'longitude');
                            if (empty($stateName)) {
                                $stateName = data_get($locationInfo, 'state_name');
                            }
                        }

                        if (empty($latitude) || empty($longitude)) continue;

                        $updateStates[] = [
                            'id' => $state->id,
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'name' => $stateName,
                        ];

                        $state->latitude = $latitude;
                        $state->longitude = $longitude;
                        $states[$index] = $state;
                    }


                    $stateLinks[$index] = [
                        'state' => $state->name ?: $state->code,
                        'handle' => $state->handle,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'is_popular' => data_get($state, 'is_popular') ?: false,
                        'cities' => []
                    ];

                    foreach ($state->fadCities as $city) {
                        $stateLinks[$index]['cities'][] = [
                            'city' => data_get($city, 'name'),
                            'handle' => data_get($city, 'handle'),
                            'is_popular' => data_get($city, 'is_popular') ?: false,
                        ];
                    }
                }
                $stateLinks = array_values($stateLinks);

                foreach ($regions as $index => $region) {

                    $latitude = data_get($region, 'latitude');
                    $longitude = data_get($region, 'longitude');

                    if (empty($latitude) || empty($longitude)) {
                        if ($region->parent_id) {
                            $latitude = data_get($region, 'parentRegion.latitude');
                            $longitude = data_get($region, 'parentRegion.longitude');
                        } else {
                            $locationInfo = $googleApi->getLocationInfo(['address' => "$region->name, $country->code"]);
                            $latitude = data_get($locationInfo, 'latitude');
                            $longitude = data_get($locationInfo, 'longitude');

                            $updateRegions[] = [
                                'id' => $region->id,
                                'latitude' => $latitude,
                                'longitude' => $longitude
                            ];
                        }

                        if (empty($latitude) || empty($longitude)) continue;

                        $region->latitude = $latitude;
                        $region->longitude = $longitude;
                        $regions[$index] = $region;
                    }

                    $regionLinks[$index] = [
                        'region' => $region->name,
                        'handle' => $region->handle,
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'is_popular' => data_get($region, 'is_popular') ?: false,
                        'cities' => []
                    ];


                    foreach ($region->fadCities as $city) {
                        $regionLinks[$index]['cities'][] = [
                            'city' => data_get($city, 'name'),
                            'handle' => data_get($city, 'handle'),
                            'is_popular' => data_get($city, 'is_popular') ?: false,
                        ];
                    }
                }

                bulkUpdate('fad_states', $updateStates);
                bulkUpdate('fad_regions', $updateRegions);

                $enabledRegions = $regions->where('enabled', true)->values();
                $enabledStates = $states->where('enabled', true)->values();

                foreach ($enabledStates as $index => $enabledState) {

                    $enabledStateLinks[$index] = [
                        'state' => $enabledState->name ?: $enabledState->code,
                        'handle' => $enabledState->handle,
                        'latitude' => $enabledState->latitude,
                        'longitude' => $enabledState->longitude,
                        'is_popular' => data_get($enabledState, 'is_popular') ?: false,
                        'cities' => []
                    ];

                    foreach ($enabledState->fadCities->where('enabled', true) as $city) {
                        $enabledStateLinks[$index]['cities'][] = [
                            'city' => data_get($city, 'name'),
                            'handle' => data_get($city, 'handle'),
                            'is_popular' => data_get($city, 'is_popular') ?: false,
                        ];
                    }
                }

                foreach ($enabledRegions as $index => $enabledRegion) {

                    $enabledRegionLinks[$index] = [
                        'region' => $enabledRegion->name,
                        'handle' => $enabledRegion->handle,
                        'latitude' => $enabledRegion->latitude,
                        'longitude' => $enabledRegion->longitude,
                        'is_popular' => data_get($enabledRegion, 'is_popular') ?: false,
                        'cities' => []
                    ];


                    foreach ($enabledRegion->fadCities->where('enabled', true) as $city) {
                        $enabledRegionLinks[$index]['cities'][] = [
                            'city' => data_get($city, 'name'),
                            'handle' => data_get($city, 'handle'),
                            'is_popular' => data_get($city, 'is_popular') ?: false,
                        ];
                    }
                }

                $preparedCities = [];
                foreach ($cities as $city) {
                    $preparedCities[] = [
                        'city' => data_get($city, 'name'),
                        'handle' => data_get($city, 'handle'),
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

                    $enabledRegionLinks[] = [
                        'region' => '',
                        'handle' => '',
                        'latitude' => null,
                        'longitude' => null,
                        'is_popular' => false,
                        'cities' => $preparedCities
                    ];

                    $enabledStateLinks[] = [
                        'state' => '',
                        'handle' => '',
                        'latitude' => null,
                        'longitude' => null,
                        'is_popular' => false,
                        'cities' => $preparedCities
                    ];
                }

                $dynamicCityLinks = [];
                if (!empty($regionLinks)) {
                    $dynamicCityLinks['regions'] = $regionLinks;
                }
                if (!empty($stateLinks)) {
                    $dynamicCityLinks['states'] = $stateLinks;
                }

                $staticCityLinks = [];
                if (!empty($enabledRegionLinks)) {
                    $staticCityLinks['regions'] = $enabledRegionLinks;
                }
                if (!empty($enabledStateLinks)) {
                    $staticCityLinks['states'] = $enabledStateLinks;
                }

                $data = [
                    [
                        'key' => 'linking-module-dynamic',
                        'value' => json_encode($dynamicCityLinks),
                    ],
                    [
                        'namespace' => "pollen-$language->code",
                        'key' => 'linking-module-dynamic',
                        'value' => json_encode($dynamicCityLinks),
                    ],
                    [
                        'key' => 'linking-module-static',
                        'value' => json_encode($staticCityLinks),
                    ],
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

                    $response = $this->shop->api()->rest('POST', '/admin/metafields.json', $metafieldData);

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
    }
}
