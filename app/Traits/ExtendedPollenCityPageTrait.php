<?php

namespace App\Traits;

use App\Helpers\ProxyHelper;
use Illuminate\Support\Carbon;

trait ExtendedPollenCityPageTrait
{
    use ProxyTrait;
    public function getPollenInitialEntities($shop, $cityHandle, $languageCode = null) {

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $language = null;
        if ($languageCode) {
            $language = $shop->languages->where('code', $languageCode)->first();
        }

        if (empty($language)) {
            $language = $defaultLanguage;
        }

        $cityPageHandle = data_get($language, 'pivot.pollen_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $city = $shop->pollenCities()
            ->with('pollenPageContent')
            ->where('language_id', $language->id)
            ->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        return [
            'domain' => $shop->public_domain ?: $shop->name,
            'country' => $country,
            'defaultLanguage' => $defaultLanguage,
            'language' => $language,
            'city' => $city,
            'cityPage' => $cityPage
        ];
    }

    public function createPollenCityMedicalSchemaJson($shop, $city, $cityHandle, $languageCode) {
        $domain = $shop->public_domain ?: $shop->name;
        $subDomain = getSubdomain($shop->name);
        $url = "https://$domain";
        $staticContentCityName = data_get($city, 'variables.city') ?: data_get($city, 'variables.city_d1');
        $cityName = data_get($city, 'name');
        $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, null, ProxyHelper::TYPE_POLLEN, $languageCode);
        $pageDescriptionKey = "pollen.$subDomain.meta_description";
        $pageDescription = getLocale($pageDescriptionKey, $languageCode, ['cityName' => $staticContentCityName ?: $cityName]);

        $data['@context'] = 'https://schema.org';
        $data['@type'] = 'WebPage';
        if (!empty($staticContentCityName) || !empty($cityName)) $data['name'] = "$cityName Webpage";
        if (!empty($pageDescription)) $data['description'] = $pageDescription;
        $data['lastReviewed'] = Carbon::parse($city->updated_at)->format('d/m/Y');
        if (!empty($proxyUrl)) {
            $data['url'] = $proxyUrl;
            $data['@id'] = $proxyUrl;
        }

        /*$data['mainEntity'] = $this->createPollenMainEntitySchemaJson($shop, $languageCode);*/
        return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    /*public function createPollenMainEntitySchemaJson($shop, $languageCode) {

        $mainEntity['@type'] = 'FAQPage';

        return $mainEntity;
    }*/
}
