<?php

namespace App\Traits;

use App\Helpers\ProxyHelper;
use App\Models\User;
use Illuminate\Support\Carbon;

trait ExtendedPollenRegionPageTrait
{
    use ProxyTrait;

    public function createPollenRegionMedicalSchemaJson($shop, $region, $pollenPageHandle, $languageCode) {
        $domain = $shop->public_domain ?: $shop->name;
        $subDomain = getSubdomain($domain);
        $url = "https://$domain";
        $regionHandle = data_get($region, 'handle');
        $proxyUrl = "https://$domain/apps/pages/pollental/$regionHandle";
        $regionName = data_get($region, 'name');
        $pageDescriptionKey = "pollen_region.$subDomain.meta_description";
        $pageDescription = getLocale($pageDescriptionKey, $languageCode);

        $data['@context'] = 'https://schema.org';
        $data['@type'] = 'WebPage';
        $data['name'] = "$regionName Webpage";
        $data['description'] = $pageDescription;
        $data['lastReviewed'] = Carbon::parse($region->updated_at)->format('d/m/Y');
        $data['url'] = $proxyUrl;
        $data['@id'] = $proxyUrl;
        $data['isPartOf'] = [
            "@type" => "WebSite",
            "name" => str($subDomain)->title() . ' Website',
            "url" => $url,
            "@id" => $url
        ];

        $breadcrumbItemList = $this->createPollenBreadcrumbSchemaJson($shop, $region, $pollenPageHandle, $languageCode);
        if (!empty($breadcrumbItemList)) {
            $data['breadcrumb'] = [
                "@type" => "BreadcrumbList",
                "itemListElement" => $breadcrumbItemList
            ];
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function createPollenBreadcrumbSchemaJson($shop, $region, $pollenPageHandle, $languageCode) {
        $breadcrumbs = $this->getPollenBreadcrumbsList($shop, $region, $pollenPageHandle, $languageCode);

        $breadcrumbItemList = [];
        foreach ($breadcrumbs as $position => $breadcrumb) {
            $breadcrumbItemList[] = [
                "@type" => "ListItem",
                "position" => $position + 1,
                "item" => [
                    "@id" => data_get($breadcrumb, 'url'),
                    "name" => data_get($breadcrumb, 'title'),
                ]
            ];
        }

        return $breadcrumbItemList;
    }

    public function getPollenBreadcrumbsList($shop, $region, $pollenPageHandle, $languageCode) {
        $domain = ProxyHelper::getDomain($shop);
        $subDomain = getSubdomain($shop->name);
        $breadcrumbKeys = ['home', 'pollen_page', 'pollen_region_page'];
        $key = "pollen_region.$subDomain.url_prefix";
        $urlPrefix = getLocale($key, $languageCode);

        $localeData = [
            'domain' => $domain,
            'region' => data_get($region, 'name'),
            'pollenPageHandle' => $pollenPageHandle,
            'pollenRegionPagePrefix' => $urlPrefix,
            'pollenRegionPageHandle' => data_get($region, 'handle')
        ];

        $breadcrumbs = [];
        foreach ($breadcrumbKeys as $breadcrumbKey) {
            $title = getLocale(
                "pollen_region.$subDomain.breadcrumb.$breadcrumbKey.title",
                $languageCode,
                $localeData
            );
            $url = getLocale(
                "pollen_region.$subDomain.breadcrumb.$breadcrumbKey.url",
                $languageCode,
                $localeData
            );

            if (!empty($title) && !empty($url)) {
                $breadcrumbs[] = [
                    'title' => $title,
                    'url' => $url
                ];
            }
        }

        return $breadcrumbs;
    }
}
