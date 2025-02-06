<?php


namespace App\Services;


use Illuminate\Support\Facades\Http;

class Datahub
{
    public $http;

    public function __construct()
    {
        $domain = str(config('services.datahub.domain'))->trim('/');
        $version = str(config('services.datahub.version'));
        $baseUrl = str("https://$domain/api/$version")->finish('/');
        $this->http = Http::timeout(20)->withToken(config('services.datahub.token'))->baseUrl($baseUrl);
    }

    public function api($method, ...$details) {
        $method = strtolower($method);
        return $this->http->$method(...$details);
    }
}
