<?php

namespace App\Traits;

use App\Models\Language;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Helpers\ProxyHelper;

trait CityPageTrait
{
    use HtmlDomTrait, ClinicTrait, ProxyTrait, FadCommonTrait, ExtendedCityPageTrait, CityHtmlDomTrait;

    public function nlPage($request, $shop, $cityHandle)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $defaultLanguageCode = data_get($defaultLanguage, 'code');

        $cityPageHandle = data_get($defaultLanguage, 'pivot.fad_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $city = $shop->fadCities()
            ->where('language_id', $defaultLanguage->id)
            ->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        $states = $shop->fadStates()
            ->where('language_id', $defaultLanguage->id)
            ->with(['fadCities' => function ($query) use ($defaultLanguage) {
                $query->where('language_id', $defaultLanguage->id)
                    ->orderBy('handle');
            }])
            ->whereHas('fadCities')->orderBy('handle')->get();

        if (!empty($city->fadState) && data_get($city, 'fadState.latitude') && data_get($city, 'fadState.longitude')) {
            $states = $shop->fadStates()
                ->where('language_id', $defaultLanguage->id)
                ->selectRaw(
                    'fad_states.*, (6371000 * acos(cos(radians(?)) * cos(radians(fad_states.latitude)) * cos(radians(fad_states.longitude) - radians(?)) + sin(radians(?)) * sin(radians(fad_states.latitude)))) AS distance',
                    [data_get($city, 'fadState.latitude'), data_get($city, 'fadState.longitude'), data_get($city, 'fadState.latitude')]
                )->with(['fadCities' => function ($query) use ($defaultLanguage) {
                    $query->where('language_id', $defaultLanguage->id)
                        ->orderBy('handle');
                }])->whereHas('fadCities')->orderBy('distance')->orderBy('handle')->get();
        }

        $cityName = data_get($city, 'name');

        $clinics = ProxyHelper::getCityClinics($request, $shop, $city);
        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $defaultLanguageCode
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Vind een allergiespecialist in jouw $cityName";

            $this->setInnerText($html, 'title', $fadPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');
            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $fadPageTitle], 'find');
            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $fadPageTitle], 'find');

            $metaDescription = "Vind eenvoudig een allergiespecialist, allergiecentrum of locatie voor het uitvoeren van allergietesten in jouw $cityName of omgeving via onze handige zoekfunctie";
            $this->setAttributes($html, 'meta[name=description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $metaDescription], 'find');

            $element = $this->getElement($html, 'fad-search');
            if (!empty($element)) {
                $searchString = implode(', ', array_filter([
                    $cityName,
                    'Netherlands',
                ]));
                $element->setAttribute('value', $searchString);
                $element->setAttribute('data-search', $searchString);
            }

            if ($city->has_static_content) {

                $mainElement = $this->getElement($html, 'main', 'name');
                if (empty($mainElement)) {
                    return $html->save();
                }

                $breadcrumbHtml = ' <div class="breadcrumb-top mt-10 text-center" id="breadcrumb-top">
                    <ul class="breadcrumb-custom list-unstyled text-center d-inline-block">
                        <li class="font-size-xl float-start">
                            <a href="/" class="text-body">Home</a>
                            <span class="px-2 d-inline-block">/</span>
                        </li>
                        <li class="font-size-xl float-start">
                            <a href="/pages/algemeen" class="text-body">Algemeen</a>
                            <span class="px-2 d-inline-block">/</span>
                        </li>
                        <li class="font-size-xl float-start">
                            <a href="/pages/allergie-testen" class="text-body">Allergie testen</a>
                            <span class="px-2 d-inline-block">/</span>
                        </li>
                        <li class="font-size-xl float-start">
                            <a href="/pages/allergiespecialisten" class="text-body">Allergiespecialisten</a>
                            <span class="px-2 d-inline-block">/</span>
                        </li>
                        <li class="font-size-xl float-start fw-bold">'
                         . $cityName .
                        '</li>
                    </ul>
                </div>';

                $mapHtml = $linkingHtml = $staticContentHtml = '';

                $element = $this->getElement($mainElement, 'fad-map-full');
                if (!empty($element)) {
                    $mapHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $element = $this->getElement($mainElement, 'fad-location-index');
                if (!empty($element)) {
                    $linkingHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $staticContentHtml = '<div>' . data_get($city, 'fadPageContent.content') . '</div>';
                $staticContentHtml = str_replace('[[city]]', $cityName, $staticContentHtml);
                $staticContentHtml = str_replace(
                    '[[city]]',
                    implode(', ', array_filter([
                        $cityName
                    ])),
                    $staticContentHtml
                );

                $staticContentHtml = str_get_html($staticContentHtml);
                if (empty($staticContentHtml) && empty($staticContentHtml->firstChild())) {
                    return $html->save();
                }

                $element = $this->getElement($staticContentHtml, 'breadcrumb-top');
                if (!empty($element)) {
                    $element->outertext = $breadcrumbHtml;
                }

                $element = $this->getElement($staticContentHtml, 'fad-map-full');
                if (!empty($element)) {
                    $element->outertext = $mapHtml;
                }

                $element = $this->getElement($staticContentHtml, 'fad-location-index');
                if (!empty($element)) {
                    $element->outertext = $this->prepareLinkingModule($shop, $html, $states) ?: $linkingHtml;
                }

                $staticContentHtml = $staticContentHtml->firstChild()?->innertext;

                foreach ($mainElement->children() as $childNode) {
                    $mainElement->removeChild($childNode);
                }

                $mainHtml = $staticContentHtml;

                $mainElement->innertext = $mainHtml;
            }

            $this->removeElement($html, 'clinics-seo');

            $this->setCityMedicalSchemaJsonScriptEl(
                $html,
                $this->createMedicalSchemaJson($shop, $city, $cityPageHandle, $clinics, $defaultLanguage->code)
            );

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    /*public function nlPage($request, $shop, $cityHandle)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $cityPageHandle = data_get($defaultLanguage, 'pivot.fad_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $city = $shop->fadCities()
            ->where('language_id', $defaultLanguage->id)
            ->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        $cityName = data_get($city, 'name');

        $clinics = ProxyHelper::getCityClinics($request, $shop, $city);
        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            // $this->setInnerText($html, 'title', 'Alles over allergie', 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => "https://$domain/apps/pages/clinics/cities/$cityHandle"], 'find');

            $this->setAttributes($html, 'meta[property=og:url]', ['content' => "https://$domain/apps/pages/clinics/cities/$cityHandle"], 'find');

            $element = $this->getElement($html, 'fad-search');
            if (!empty($element)) {
                $searchString = implode(', ', array_filter([
                    $cityName,
                    'Netherlands',
                ]));
                $element->setAttribute('value', $searchString);
                $element->setAttribute('data-search', $searchString);
            }

            $this->removeElement($html, 'clinics-seo');

            $this->setCityMedicalSchemaJsonScriptEl(
                $html,
                $this->createMedicalSchemaJson($shop, $city, $cityPageHandle, $clinics, $defaultLanguage->code)
            );

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }*/

    public function dePage($request, $shop, $cityHandle)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $cityPageHandle = data_get($defaultLanguage, 'pivot.fad_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $city = $shop->fadCities()->with(['fadState', 'fadPageContent'])
            ->where('enabled', true)
            ->where('language_id', $defaultLanguage->id)
            ->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        $clinics = ProxyHelper::getCityClinics($request, $shop, $city);
        $states = $shop->fadStates()
            ->where('enabled', true)
            ->where('language_id', $defaultLanguage->id)
            ->with(['fadCities' => function ($query) use ($defaultLanguage) {
                $query->where('enabled', true)
                    ->where('language_id', $defaultLanguage->id)
                    ->orderBy('handle');
            }])
            ->whereHas('fadCities')->orderBy('handle')->get();

        if (!empty($city->fadState) && data_get($city, 'fadState.latitude') && data_get($city, 'fadState.longitude')) {
            $states = $shop->fadStates()
                ->where('enabled', true)
                ->where('language_id', $defaultLanguage->id)
                ->selectRaw(
                    'fad_states.*, (6371000 * acos(cos(radians(?)) * cos(radians(fad_states.latitude)) * cos(radians(fad_states.longitude) - radians(?)) + sin(radians(?)) * sin(radians(fad_states.latitude)))) AS distance',
                    [data_get($city, 'fadState.latitude'), data_get($city, 'fadState.longitude'), data_get($city, 'fadState.latitude')]
                )->with(['fadCities' => function ($query) use ($defaultLanguage) {
                    $query->where('enabled', true)
                        ->where('language_id', $defaultLanguage->id)
                        ->orderBy('handle');
                }])->whereHas('fadCities')->orderBy('distance')->orderBy('handle')->get();
        }

        $cityName = (string) str($city->name)->title();
        $state = data_get($city, 'fadState.name');

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrl = "https://$domain/apps/pages/allergologensuche/stadt/$cityHandle";

            // $clinicsCount = $shop->clinics()->where('city', $city->name)->count();

            $fadPageTitle = "Allergologe in $cityName | Über 1.500 Allergieärzte finden";
            $fadBreadcrumbTitle = "Allergologinnen und Allergologen in $cityName";

            $this->setInnerText($html, 'title', $fadPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');
            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $fadPageTitle], 'find');
            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $fadPageTitle], 'find');

            $metaDescription = "Erhalte eine Übersicht von Allergologinnen und Allergologen in deiner Nähe in $cityName inklusive Kontaktinformationen. Vereinbare gleich einen Termin!";
            $this->setAttributes($html, 'meta[name=description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $metaDescription], 'find');

            $element = $this->getElement($html, 'fad-search');
            if (!empty($element)) {
                $searchString = implode(', ', array_filter([
                    $cityName,
                    $state,
                    'Deutschland',
                ]));
                $element->setAttribute('value', $searchString);
                $element->setAttribute('data-search', $searchString);
            }

            if ($city->has_static_content) {

                $mainElement = $this->getElement($html, 'main', 'name');
                if (empty($mainElement)) {
                    return $html->save();
                }

                $breadcrumbHtml = '<div class="breadcrumb-top" id="breadcrumb-top">
    <div class="mt-3 mt-lg-5 text-center breadcrumb-spacing">
        <div class="container mt-5 col-12 mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a href="/">Startseite</a><span>/</span></li>
                <li><a href="/pages/allergologensuche"> Allergologensuche </a><span>/</span></li>
                <li class="fw-bold"> ' . $fadBreadcrumbTitle . ' </li>
            </ul>
        </div>
    </div>
</div>';

                $mapHtml = $linkingHtml = $staticContentHtml = '';

                $element = $this->getElement($mainElement, 'fad-map-full');
                if (!empty($element)) {
                    $mapHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $element = $this->getElement($mainElement, 'fad-location-index');
                if (!empty($element)) {
                    $linkingHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $staticContentHtml = '<div>' . data_get($city, 'fadPageContent.content') . '</div>';
                $staticContentHtml = str_replace('[[city]]', $cityName, $staticContentHtml);
                $staticContentHtml = str_replace(
                    '[[city, state]]',
                    implode(', ', array_filter([
                        $cityName,
                        $state
                    ])),
                    $staticContentHtml
                );

                $staticContentHtml = str_get_html($staticContentHtml);
                if (empty($staticContentHtml) && empty($staticContentHtml->firstChild())) {
                    return $html->save();
                }

                $element = $this->getElement($staticContentHtml, 'breadcrumb-top');
                if (!empty($element)) {
                    $element->outertext = $breadcrumbHtml;
                }

                $element = $this->getElement($staticContentHtml, 'fad-map-full');
                if (!empty($element)) {
                    $element->outertext = $mapHtml;
                }

                $element = $this->getElement($staticContentHtml, 'fad-location-index');
                if (!empty($element)) {
                    $element->outertext = $this->prepareLinkingModule($shop, $html, $states) ?: $linkingHtml;
                }

                $staticContentHtml = $staticContentHtml->firstChild()?->innertext;

                foreach ($mainElement->children() as $childNode) {
                    $mainElement->removeChild($childNode);
                }

                $mainHtml = $staticContentHtml;

                $mainElement->innertext = $mainHtml;
            }

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Letztes Update: ' . Carbon::parse($city->updated_at)->format('d/m/Y');
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .social-icon ', 'find');
            if (!empty($element)) {
                $element->remove();
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            $this->removeElement($html, 'BreadcrumbList-schema');
            $this->removeElement($html, 'faq-schema-json');

            $this->setCityMedicalSchemaJsonScriptEl(
                $html,
                $this->createMedicalSchemaJson($shop, $city, $cityPageHandle, $clinics, $defaultLanguage->code)
            );

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function usPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $states = ProxyHelper::getFadStates($shop, $language, $city);

        $cityName = ucwords(strtolower($city->name));
        $state = data_get($city, 'fadState.name') ? ucwords(strtolower(data_get($city, 'fadState.name'))) : strtoupper(data_get($city, 'fadState.code'));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Find a Doctor in $cityName";
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $state,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            $this->prepareLinkingModule($shop, $html, $states);

            // Selectors [[city]]
            $staticContentData = [
                'state' => $state,
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function atPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode, true);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $cityName = ucwords(strtolower($city->name));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Allergie-Spezialisten in $cityName finden";
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $metaDescription = "Unsere Facharztsuche kann dir helfen, eine Facharztpraxis mit Spezialisierung im Bereich Allergologie in $cityName zu finden. Wir geben dir Tipps, welche Fragen du der Ärztin oder dem Arzt stellen solltest.";
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            // Selectors [[city]]
            $staticContentData = [
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function czPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode, true);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');
        $cityPageHandle = data_get($initialEntities, 'cityPageHandle');

        $cityName = ucwords(strtolower($city->name));
        $clinics = ProxyHelper::getCityClinics($request, $shop, $city);

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Hledáte alergologa ve městě $cityName? Vyzkoušejte funkci Vyhledat lékaře";
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $metaDescription = "Náš online vyhledávač vám pomůže najít alergologa ve městě $cityName. Dáme vám také tipy, nač se ho zeptat při konzultaci svých příznaků.";
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            // Selectors [[city]]
            $staticContentData = [
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->setCityMedicalSchemaJsonScriptEl(
                $html,
                $this->createMedicalSchemaJson($shop, $city, $cityPageHandle, $clinics, $defaultLanguage->code)
            );

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function skPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode, true);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');
        $cityPageHandle = data_get($initialEntities, 'cityPageHandle');

        $cityName = ucwords(strtolower($city->name));
        $staticContentCityName = data_get($city, 'variables.city_d1');
        $clinics = ProxyHelper::getCityClinics($request, $shop, $city);

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = 'Hľadáte alergológa v ' . ($staticContentCityName ?: $cityName) . '? Skúste funkciu Vyhľadať lekára.';
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $metaDescription = 'Náš online vyhľadávač vám pomôže nájsť alergológa v ' . ($staticContentCityName ?: $cityName) . '. Dáme vám aj tipy, na čo sa ho opýtať, keď budete konzultovať vaše príznaky.';
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            // Selectors [[city]]
            $staticContentData = [
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->setCityMedicalSchemaJsonScriptEl(
                $html,
                $this->createMedicalSchemaJson($shop, $city, $cityPageHandle, $clinics, $defaultLanguage->code)
            );

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function chPage($request, $shop, $cityHandle, $languageCode = 'de')
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode, true);
        if (empty($initialEntities)) {
            return null;
        }

        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');
        $cityPageHandle = data_get($initialEntities, 'cityPageHandle');

        $cityName = ucwords(strtolower($city->name));
        $subDomain = getSubdomain($shop->name);
        $clinics = ProxyHelper::getCityClinics($request, $shop, $city);

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $languageUrls = ProxyHelper::getLanguageUrls($shop, ProxyHelper::TYPE_FAD, null, $city);
            $proxyUrl = data_get($languageUrls, $languageCode);

            $translationData = [
                'city' => $cityName
            ];

            $fadPageTitle = getLocale("fad.$subDomain.page_title", $languageCode, $translationData);
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $metaDescription = getLocale("fad.$subDomain.meta_description", $languageCode, $translationData);
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            // Selectors [[city]]
            $staticContentData = [
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);
            $this->setCityLanguageLinksJsonScriptEl($html, $languageUrls);

            /*$this->setCityMedicalSchemaJsonScriptEl(
                $html,
                $this->createMedicalSchemaJson($shop, $city, $cityPageHandle, $clinics, $languageCode)
            );*/

            $this->removeHrefLangTags($html);
//            $this->removeMedicalSchemaTag($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function caPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $cityName = ucwords(strtolower($city->name));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Need an allergist in $cityName? Try our easy Doc Finder";
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $metaDescription = "Our online search tool can help you find an allergist in $cityName. Plus, get tips on what to ask them when you discuss your symptoms.";
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            // Selectors [[city]]
            $staticContentData = [
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function odactraPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $states = ProxyHelper::getFadStates($shop, $language, $city);

        $cityName = ucwords(strtolower($city->name));
        $state = data_get($city, 'fadState.name') ? ucwords(strtolower(data_get($city, 'fadState.name'))) : strtoupper(data_get($city, 'fadState.code'));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Find a Doctor in $cityName";
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $this->setNoIndexTag($html);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $state,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            $this->prepareLinkingModule($shop, $html, $states);

            // Selectors [[city]]
            $staticContentData = [
                'state' => $state,
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function ragwitekPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $states = ProxyHelper::getFadStates($shop, $language, $city);

        $cityName = ucwords(strtolower($city->name));
        $state = data_get($city, 'fadState.name') ? ucwords(strtolower(data_get($city, 'fadState.name'))) : strtoupper(data_get($city, 'fadState.code'));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Find a Doctor in $cityName";
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $this->setNoIndexTag($html);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $state,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            $this->prepareLinkingModule($shop, $html, $states);

            // Selectors [[city]]
            $staticContentData = [
                'state' => $state,
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function grastekPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $states = ProxyHelper::getFadStates($shop, $language, $city);

        $cityName = ucwords(strtolower($city->name));
        $state = data_get($city, 'fadState.name') ? ucwords(strtolower(data_get($city, 'fadState.name'))) : strtoupper(data_get($city, 'fadState.code'));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $fadPageTitle = "Find a Doctor in $cityName";
            $this->setTitleTag($html, $fadPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $fadPageTitle);
            $this->setMetaTwitterTitleTag($html, $fadPageTitle);

            $this->setNoIndexTag($html);

            $subDomain = getSubdomain($shop->name);
            $key = "fad.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $state,
                $value
            ]));
            $this->setFadSearch($html, $searchString);

            $this->prepareLinkingModule($shop, $html, $states);

            // Selectors [[city]]
            $staticContentData = [
                'state' => $state,
                'cityName' => $cityName
            ];
            $this->setFadStaticContent($html, $city, $staticContentData);

            $this->removeHrefLangTags($html);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function sitemapUrls($request, $shop, $languageCode = null)
    {
        try {

            $language = empty($languageCode) ?
                $shop->languages->where('pivot.default', true)->first() :
                $shop->languages->where('code', $languageCode)->first();

            $enabledCitiesStores = [
                User::ALK_DE_STORE,
                User::KLARIFY_AT_STORE,
                User::KLARIFY_CZ_STORE,
                User::KLARIFY_SK_STORE,
                User::KLARIFY_CH_STORE,
            ];

            if (in_array($shop->name, $enabledCitiesStores)) {

                $fadCities = $shop->fadCities()
                    ->where('language_id', $language->id)
                    ->where('enabled', true)->get()->toArray();
            } else {

                $fadCities = $shop->fadCities->where('language_id', $language->id)->toArray();
            }

            $xmlData = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"><!--  This is the sitemap linking to additional city pages  -->';

            foreach ($fadCities as $fadCity) {

                $fadCityHandle = data_get($fadCity, 'handle');

                if (empty($fadCityHandle)) continue;

                $domain = $shop->public_domain ?: $shop->name;
                $url = match ($shop->name) {
                    User::ALK_NL_STORE => "https://$domain/apps/pages/allergiespecialisten-testen/$fadCityHandle",
                    User::ALK_DE_STORE => "https://$domain/apps/pages/allergologensuche/stadt/$fadCityHandle",
                    User::KLARIFY_US_STORE => "https://$domain/apps/pages/find-a-doctor/$fadCityHandle",
                    User::KLARIFY_AT_STORE => "https://$domain/apps/pages/facharztsuche/$fadCityHandle",
                    User::KLARIFY_CZ_STORE => "https://$domain/apps/pages/vyhledat-alergologa/$fadCityHandle",
                    User::KLARIFY_SK_STORE => "https://$domain/apps/pages/vyhladat-alergologa/$fadCityHandle",
                    User::KLARIFY_CH_STORE => match ($languageCode) {
                        'de' => "https://$domain/apps/pages/allergologensuche/$fadCityHandle",
                        'fr' => "https://$domain/apps/pages/trouver-un-allergologue/$fadCityHandle",
                        'it' => "https://$domain/apps/pages/trova-un-allergologo/$fadCityHandle",
                        default => "https://$domain/apps/pages/allergologensuche/$fadCityHandle",
                    },
                    User::KLARIFY_CA_STORE => "https://$domain/apps/pages/find-an-allergist/$fadCityHandle",
                    User::ALK_ODACTRA_STORE => "https://$domain/apps/pages/find-an-allergy-specialist/$fadCityHandle",
                    User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE => "https://$domain/apps/pages/find-doctor/$fadCityHandle",
                    default => "https://$domain/apps/pages/clinics/cities/$fadCityHandle",
                };

                $xmlData .= "<url><loc>$url</loc></url>";
            }
            $xmlData .= '</urlset>';
            return response($xmlData, 200)->header('Content-type', 'application/xml');

        } catch (\Exception $e) {

            return response("", 500)->header('Content-type', 'application/xml');
        }
    }
}
