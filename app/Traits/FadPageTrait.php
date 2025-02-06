<?php

namespace App\Traits;

trait FadPageTrait
{
    use HtmlDomTrait, ProxyTrait, FadCommonTrait;

    public function usPage($request, $shop)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $notFoundPageHandle = '404';

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }

        $fadPageHandle = data_get($defaultLanguage, 'pivot.fad_page');
        $fadPage = $this->getShopifyPage($shop, $fadPageHandle);

        if (!$fadPage) {
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }

        $country = $shop->country;
        if (empty($country)) {
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }


        $states = $shop->fadStates()
            ->where('language_id', $defaultLanguage->id)
            ->with(['fadCities' => function ($query) use ($defaultLanguage) {
                $query->where('language_id', $defaultLanguage->id)
                    ->orderBy('handle');
            }])->whereHas('fadCities')->orderBy('handle')->get();

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($fadPage);

            $proxyUrl = "https://$domain/apps/find-a-doctor";

            $this->setInnerText($html, 'title', 'Find a Doctor', 'name');

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
            $element = $html->find('meta[name=robots]');
            $element = data_get($element, 0);
            $isNoIndex = $element ? $element->getAttribute('content') : null;
            $isNoIndex = str($isNoIndex)->contains('noindex');
            if (!$isNoIndex) {
                $headEl = $html->getElementByTagName('head');
                if (!empty($headEl)) {
                    $meta = $html->createElement('meta');
                    $meta->setAttribute('name', 'robots');
                    $meta->setAttribute('content', 'noindex,nofollow');
                    $headEl->appendChild($meta);
                }
            }

            $this->prepareLinkingModule($shop, $html, $states);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return $this->getShopifyPage($shop, $notFoundPageHandle);
        }
    }
}
