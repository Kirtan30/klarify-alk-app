<?php

namespace App\Services;

use App\Traits\SettingTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleApi
{
    use SettingTrait;

    public $http;
    public $googleApiKey;

    public function __construct($shop)
    {
        $this->googleApiKey = $this->getGoogleApiKey($shop->settings->keyBy('key'));
        $baseUrl = 'https://maps.googleapis.com';
        $this->http = Http::timeout(20)->baseUrl($baseUrl);
    }

    function getLocationInfo($data)
    {

        $cacheKey = data_get($data, 'address');
        if (!empty($cacheKey) && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $data['sensor'] = !empty($data['sensor']);
        $data['key'] = empty($data['key']) ? $this->googleApiKey : $data['key'];

        try {
            sleep(1);
            $response = $this->http->get('/maps/api/geocode/json', $data);
            $response->throw();
        } catch (\Exception $e) {
            report($e);
            return [];
        }

        $response = $response->json();

        if (data_get($response, 'error_message') || empty(data_get($response, 'results'))) {
            Log::error('Google API call failed => ' . json_encode($response));
            return [];
        }

        $response = data_get($response, 'results.0');

        $stateCode = null;
        $stateName = null;
        if (!empty($response)) {
            foreach(data_get($response, 'address_components') ?: [] as $addressComponent){
                if (!empty(data_get($addressComponent, 'types')) && in_array('administrative_area_level_1', data_get($addressComponent, 'types'))) {
                    $stateCode = data_get($addressComponent, 'short_name');
                    $stateName = data_get($addressComponent, 'long_name');
                }
            }
        }

        $latitude = data_get($response, 'geometry.location.lat');
        $longitude = data_get($response, 'geometry.location.lng');

        $response = [
            'state_code' => $stateCode,
            'state_name' => $stateName,
            'formatted_city' => data_get($response, 'formatted_address'),
            'latitude' => $latitude ? round($latitude, 8) : null,
            'longitude' => $longitude ? round($longitude, 8) : null
        ];

        Cache::forever($cacheKey, $response);

        return $response;
    }
}
