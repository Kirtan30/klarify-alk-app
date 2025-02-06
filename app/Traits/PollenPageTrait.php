<?php

namespace App\Traits;

trait PollenPageTrait
{
    use HtmlDomTrait, ProxyTrait, PollenCommonTrait;

    public function usPollenPage($request, $shop)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $notFoundPageHandle = '404';

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }

        $pollenPageHandle = data_get($defaultLanguage, 'pivot.pollen_page');
        $pollenPage = $this->getShopifyPage($shop, $pollenPageHandle);

        if (!$pollenPage) {
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }

        $country = $shop->country;
        if (empty($country)) {
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }

        $states = $shop->pollenStates()->with(['pollenCities' => function ($query) {
            $query->orderBy('handle');
        }])->whereHas('pollenCities')->orderBy('handle')->get();

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($pollenPage);

            $proxyUrl = "https://$domain/apps/pages/pollen-forecast";

            $this->setInnerText($html, 'title', 'Pollen Forecast', 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            if (empty($this->getElement($html, 'meta[name=robots]', 'find'))) {
                $headEl = $this->getElement($html, 'head', 'name');
                if (!empty($headEl)) {
                    $meta = $html->createElement('meta');
                    $meta->setAttribute('name', 'robots');
                    $meta->setAttribute('content', 'noindex');
                    $headEl->appendChild($meta);
                }
            }

            $this->preparePollenLinkingModule($shop, $html, $states);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }
    }
}
