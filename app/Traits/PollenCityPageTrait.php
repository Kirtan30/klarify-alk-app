<?php

namespace App\Traits;

use App\Models\PollenCity;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Helpers\ProxyHelper;

trait PollenCityPageTrait
{

    use HtmlDomTrait, ProxyTrait, PollenCommonTrait, PollenHtmlDomTrait, ExtendedPollenCityPageTrait;
    public function noPollenPage($request, $shop, $cityHandle)
    {
        $cityHandle = strtolower($cityHandle);
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $cityPageHandle = data_get($defaultLanguage, 'pivot.pollen_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $cities = $shop->pollenCities()
            ->with(['pollenRegion', 'pollenPageContent'])
            ->where('language_id', $defaultLanguage->id)
            ->orderBy('handle')->get();
        $city = $cities->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrl = "https://$domain/apps/pages/pollenvarsel/by/$cityHandle";
            $pollenCityPageTitle = "Pollenvarsel $city->name | Sjekk spredningen i ditt område!";
            $pollenCityMetaDescription = "Plagsomme allergisymptomer i $city->name? Få hjelp til å planlegge dagen med nøyaktig og oppdatert pollenvarsel for ditt område – se her";

            $this->setInnerText($html, 'title', $pollenCityPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $pollenCityPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $pollenCityPageTitle], 'find');

            $this->setAttributes($html, 'meta[name=description]', ['content' => $pollenCityMetaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $pollenCityMetaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $pollenCityMetaDescription], 'find');

            $this->setInnerText($html, 'h1', "Pollenvarsel $city->name", 'find');

            $element = $this->getElement($html, 'pollen-search');
            if (empty($element)) {
                return $html->save();
            }

            $searchParent = $element->parent();
            $element->remove();

            $element = $html->createElement('select');
            $element->setAttribute('id', 'pollen-city-select');

            foreach ($cities as $cityData) {
                $optionElement = $html->createElement('option');
                $optionElement->setAttribute('name', $cityData->name);
                $optionElement->setAttribute('value', $cityData->handle);

                if ($city->id === $cityData->id) {
                    $optionElement->setAttribute('selected', true);
                    $element->setAttribute('data-name', $city->name);
                    $element->setAttribute('data-latitude', $city->latitude);
                    $element->setAttribute('data-longitude', $city->longitude);
                }

                $optionElement->innertext = $cityData->name;

                $element->appendChild($optionElement);
            }

            $searchParent->appendChild($element);

            $breadcrumbHtml = '<div class="breadcrumb-top" id="breadcrumb-top">
    <div class="mt-3 mt-lg-5 text-center breadcrumb-spacing">
        <div class="container mt-5 col-12 mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a href="/">Hjem</a><span>/</span></li>
                <li><a href="/pages/pollenvarsel"> Pollenvarsel </a><span>/</span></li>
                ' . ($city->pollenRegion ? '<li><a href="/apps/pages/pollenvarsel/fylke/' . $city->pollenRegion->handle .'"> Pollenvarsel ' . $city->pollenRegion->name . '</a><span>/</span></li>' : '') . '
                <li class="fw-bold"> Pollenvarsel ' . $city->name . ' </li>
            </ul>
        </div>
    </div>
</div>';
            $element = $this->getElement($html, 'main', 'name');
            if (empty($element)) {
                return $html->save();
            }
            $innerHtml = $element->innertext;

            $pollenCountHtml = $this->getElement($html, 'pollen-counter-block');
            if (!empty($pollenCountHtml)) {
                $pollenCountHtml = $pollenCountHtml->outertext;
            }

            $linkingModuleHtml = $this->getElement($html, 'pollen-location-index');
            if (!empty($linkingModuleHtml)) {
                $linkingModuleHtml = $linkingModuleHtml->parent();
                $linkingModuleHtml = $linkingModuleHtml ? $linkingModuleHtml->outertext : '';
            }

            foreach ($element->children() as $child) {
                $child->remove();
            }

            if ($city->has_static_content) {
                $pollenStaticContent = data_get($city, 'pollenPageContent.content');
                $pollenStaticContentVariables = data_get($city, 'pollenPageContent.variables') ?: [];

                foreach ($pollenStaticContentVariables as $pollenStaticContentVariable) {
                    $variableValue = data_get($city, "variables.$pollenStaticContentVariable");
                    $pollenStaticContent = str_replace("[[$pollenStaticContentVariable]]", $variableValue, $pollenStaticContent);
                }

                $pollenStaticContentEl = str_get_html('<div id="pollen-static-content">'.$pollenStaticContent.'</div>');
                if (!empty($pollenStaticContentEl)) {
                    $pollenCountEl = $this->getElement($pollenStaticContentEl, 'pollen-counter-block');
                    $this->setOuterText($pollenStaticContentEl, 'pollen-location-index', $linkingModuleHtml);

                    if (!empty($pollenCountEl)) {
                        $pollenCountEl->outertext = $pollenCountHtml;
                        $pollenStaticContentEl = $pollenStaticContentEl->firstChild();

                        $pollenStaticContentHtml = !empty($pollenStaticContentEl) ? $pollenStaticContentEl->innertext : '';

                        $innerHtml = $breadcrumbHtml . $pollenStaticContentHtml;
                    } else {
                        $innerHtml = $breadcrumbHtml . $innerHtml;
                    }
                }

            } else {
                $innerHtml = $breadcrumbHtml . $innerHtml;
            }

            $element->innertext = $innerHtml;

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $listElement = data_get($itemListElements, 0, []) ?: [];
                    if ($listElement) {
                        $appendBreadcrumbs = [
                            array_merge($listElement, [
                                'position' => 1,
                                'name' => 'Pollenvarsel',
                                'item' => "https://$domain/pages/pollenvarsel"
                            ])
                        ];

                        if ($city->pollenRegion) {
                            $appendBreadcrumbs[] = array_merge($listElement, [
                                'position' => 2,
                                'name' => "Pollenvarsel {$city->pollenRegion->name}",
                                'item' => "https://$domain/apps/pages/pollenvarsel/fylke/{$city->pollenRegion->handle}"
                            ]);
                        }

                        $appendBreadcrumbs[] = array_merge($listElement, [
                            'position' => $city->pollenRegion ? 3 : 2,
                            'name' => "Pollenvarsel $city->name",
                            'item' => $proxyUrl
                        ]);

                        $itemListElements = $appendBreadcrumbs;
                    }
                }
                data_set($breadcrumbJson, 'itemListElement', $itemListElements);
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function dkPollenPage($request, $shop, $cityHandle)
    {
        $cityHandle = strtolower($cityHandle);
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $cityPageHandle = data_get($defaultLanguage, 'pivot.pollen_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $cities = $shop->pollenCities()
            ->with(['pollenRegion', 'pollenPageContent'])
            ->where('language_id', $defaultLanguage->id)
            ->orderBy('handle')->get();
        $city = $cities->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrl = "https://$domain/apps/pages/pollental/by/$cityHandle";
            $pollenCityPageTitle = "Dagens pollental for $city->name | Tjek pollental i dit område!";
            $pollenCityMetaDescription = "Vær opdateret på de nyeste pollental i $city->name. Når du følger Pollental, kan du bedre planlægge din dag. Se her";

            $this->setInnerText($html, 'title', $pollenCityPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $pollenCityPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $pollenCityPageTitle], 'find');

            $this->setAttributes($html, 'meta[name=description]', ['content' => $pollenCityMetaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $pollenCityMetaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $pollenCityMetaDescription], 'find');

            $element = $this->getElement($html, 'pollen-select-location');
            if (empty($element)) {
                return $html->save();
            }

            $searchParent = $element->parent();
            $element->remove();

            $element = $html->createElement('select');
            $element->setAttribute('id', 'pollen-city-select');

            foreach ($cities as $cityData) {
                $optionElement = $html->createElement('option');
                $optionElement->setAttribute('name', $cityData->name);
                $optionElement->setAttribute('value', $cityData->handle);

                if ($city->id === $cityData->id) {
                    $optionElement->setAttribute('selected', true);
                    $element->setAttribute('data-name', $city->name);
                    $element->setAttribute('data-latitude', $city->latitude);
                    $element->setAttribute('data-longitude', $city->longitude);
                }

                $optionElement->innertext = $cityData->name;

                $element->appendChild($optionElement);
            }

            $searchParent->appendChild($element);


            $breadcrumbHtml = '<div class="breadcrumb-direct-top" id="breadcrumb-top">
    <div>
        <div class="container mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a class="text-decoration-none" href="/">Forside</a><span>/</span></li>
                <li><a class="text-decoration-none" href="/pages/'.$cityPageHandle.'"> Pollental </a><span>/</span></li>
                ' . ($city->pollenRegion ? '<li><a class="text-decoration-none" href="/apps/pages/pollental/' . $city->pollenRegion->handle .'"> ' . $city->pollenRegion->name . ' </a><span>/</span></li>' : '') . '
                <li class="fw-bold"> ' . $city->name . ' </li>
            </ul>
        </div>
    </div>
</div>';
            $element = $this->getElement($html, 'main', 'name');
            if (empty($element)) {
                return $html->save();
            }
            $innerHtml = $element->innertext;

            $pollenCountHtml = $this->getElement($html, 'pollen-counter-block');
            if (!empty($pollenCountHtml)) {
                $pollenCountHtml = $pollenCountHtml->outertext;
            }

            $linkingModuleHtml = $this->getElement($html, 'pollen-location-index');
            if (!empty($linkingModuleHtml)) {
                $linkingModuleHtml = $linkingModuleHtml->parent();
                $linkingModuleHtml = $linkingModuleHtml ? $linkingModuleHtml->outertext : '';
            }

            foreach ($element->children() as $child) {
                $child->remove();
            }

            if ($city->has_static_content) {
                $pollenStaticContent = data_get($city, 'pollenPageContent.content');
                $pollenStaticContentVariables = data_get($city, 'pollenPageContent.variables') ?: [];

                foreach ($pollenStaticContentVariables as $pollenStaticContentVariable) {
                    $variableValue = data_get($city, "variables.$pollenStaticContentVariable");
                    $pollenStaticContent = str_replace("[[$pollenStaticContentVariable]]", $variableValue, $pollenStaticContent);
                }

                $pollenStaticContentEl = str_get_html('<div id="pollen-static-content">'.$pollenStaticContent.'</div>');
                if (!empty($pollenStaticContentEl)) {
                    $pollenCountEl = $this->getElement($pollenStaticContentEl, 'pollen-counter-block');
                    $this->setOuterText($pollenStaticContentEl, 'pollen-location-index', $linkingModuleHtml);

                    if (!empty($pollenCountEl)) {
                        $pollenCountEl->outertext = $pollenCountHtml;
                        $pollenStaticContentEl = $pollenStaticContentEl->firstChild();

                        $pollenStaticContentHtml = !empty($pollenStaticContentEl) ? $pollenStaticContentEl->innertext : '';

                        $innerHtml = $breadcrumbHtml . $pollenStaticContentHtml;
                    } else {
                        $innerHtml = $breadcrumbHtml . $innerHtml;
                    }
                }

            } else {
                $innerHtml = $breadcrumbHtml . $innerHtml;
            }

            $element->innertext = $innerHtml;

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $listElement = data_get($itemListElements, 0, []) ?: [];
                    if ($listElement) {
                        $appendBreadcrumbs = [
                            array_merge($listElement, [
                                'position' => 1,
                                'name' => 'Pollental',
                                'item' => "https://$domain/pages/pollental"
                            ])
                        ];

                        if ($city->pollenRegion) {
                            $appendBreadcrumbs[] = array_merge($listElement, [
                                'position' => 2,
                                'name' => $city->pollenRegion->name,
                                'item' => "https://$domain/apps/pages/pollental/{$city->pollenRegion->handle}"
                            ]);
                        }

                        $appendBreadcrumbs[] = array_merge($listElement, [
                            'position' => $city->pollenRegion ? 3 : 2,
                            'name' => "$city->name",
                            'item' => $proxyUrl
                        ]);

                        $itemListElements = $appendBreadcrumbs;
                    }
                }
                data_set($breadcrumbJson, 'itemListElement', $itemListElements);
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function dePollenPage($request, $shop, $cityHandle)
    {
        $cityHandle = strtolower($cityHandle);
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $cityPageHandle = data_get($defaultLanguage, 'pivot.pollen_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $cities = $shop->pollenCities()
            ->with(['pollenState', 'pollenPageContent'])
            ->where('language_id', $defaultLanguage->id)
            ->orderBy('handle')->get();
        $city = $cities->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        $states = $shop->pollenStates()
            ->where('language_id', $defaultLanguage->id)
            ->with(['pollenCities' => function ($query) use ($defaultLanguage) {
            $query->where('language_id', $defaultLanguage->id)->orderBy('handle');
        }])->whereHas('pollenCities')->orderBy('handle')->get();

        if (!empty($city->pollenState) && data_get($city, 'pollenState.latitude') && data_get($city, 'pollenState.longitude')) {
            $states = $shop->pollenStates()
                ->where('language_id', $defaultLanguage->id)
                ->selectRaw(
                'pollen_states.*, (6371000 * acos(cos(radians(?)) * cos(radians(pollen_states.latitude)) * cos(radians(pollen_states.longitude) - radians(?)) + sin(radians(?)) * sin(radians(pollen_states.latitude)))) AS distance',
                [data_get($city, 'pollenState.latitude'), data_get($city, 'pollenState.longitude'), data_get($city, 'pollenState.latitude')]
            )->with(['pollenCities' => function ($query) use ($defaultLanguage) {
                $query->where('language_id', $defaultLanguage->id)->orderBy('handle');
            }])->whereHas('pollenCities')->orderBy('distance')->orderBy('handle')->get();
        }

        $cityName = ucwords(strtolower($city->name));
        $state = data_get($city, 'pollenState.name');

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrl = "https://$domain/apps/pages/pollenflug-vorhersage/stadt/$cityHandle";
            $pollenCityPageTitle = "Pollenflug in $cityName | Welche Pollen fliegen in deiner Nähe?";

            $this->setInnerText($html, 'title', $pollenCityPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $pollenCityPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $pollenCityPageTitle], 'find');

            $metaDescription = "Pollenflugvorhersage für $cityName entdecken und sehen, was dich erwartet. Vorhersage für x Pollenarten inkl. Bäume, Gräser und Kräuter ansehen.";
            $this->setAttributes($html, 'meta[name=description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $metaDescription], 'find');

            $element = $this->getElement($html, 'pollen-search');
            if (empty($element)) {
                return $html->save();
            }

            $searchParent = $element->parent();
            $element->remove();

            $element = $html->createElement('select');
            $element->setAttribute('id', 'pollen-city-select');

            foreach ($cities as $cityData) {
                $optionElement = $html->createElement('option');
                $optionElement->setAttribute('name', $cityData->name);
                $optionElement->setAttribute('value', $cityData->handle);

                if ($city->id === $cityData->id) {
                    $optionElement->setAttribute('selected', true);
                    $element->setAttribute('data-name', $city->name);
                    $element->setAttribute('data-latitude', $city->latitude);
                    $element->setAttribute('data-longitude', $city->longitude);
                }

                $optionElement->innertext = $cityData->name;

                $element->appendChild($optionElement);
            }

            $searchParent->appendChild($element);

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
                <li><a href="/pages/pollenflug-vorhersage"> Pollenflug </a><span>/</span></li>
                <li class="fw-bold"> Pollenflug in ' . $cityName . ' </li>
            </ul>
        </div>
    </div>
</div>';

                $pollenCountHtml = $linkingHtml = $staticContentHtml = '';

                $element = $this->getElement($mainElement, 'pollen-counter-block');
                if (!empty($element)) {
                    $pollenCountHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $element = $this->getElement($mainElement, 'pollen-location-index');
                if (!empty($element)) {
                    $linkingHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $staticContentHtml = '<div>' . data_get($city, 'pollenPageContent.content') . '</div>';
                $variables = data_get($city, 'variables') ?: [];
                if (empty($variables)) {
                    $staticContentHtml = str_replace('[[city]]', $cityName, $staticContentHtml);
                } else {
                    foreach ($variables as $key => $value) {
                        $staticContentHtml = str_replace("[[$key]]", $value, $staticContentHtml);
                    }
                }

                $staticContentHtml = str_get_html($staticContentHtml);
                if (empty($staticContentHtml) && empty($staticContentHtml->firstChild())) {
                    return $html->save();
                }

                $element = $this->getElement($staticContentHtml, 'breadcrumb-top');
                if (!empty($element)) {
                    $element->outertext = $breadcrumbHtml;
                }

                $element = $this->getElement($staticContentHtml, 'pollen-counter-block');
                if (!empty($element)) {
                    $element->outertext = $pollenCountHtml;
                }

                $element = $this->getElement($staticContentHtml, 'pollen-location-index');
                if (!empty($element)) {
                    $element->outertext = $this->preparePollenLinkingModule($shop, $html, $states) ?: $linkingHtml;
                }

                $staticContentHtml = $staticContentHtml->firstChild()?->innertext;

                foreach ($mainElement->children() as $childNode) {
                    $mainElement->removeChild($childNode);
                }

                $mainHtml = $staticContentHtml;

                $mainElement->innertext = $mainHtml;
            }

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $listElement = data_get($itemListElements, 0, []) ?: [];
                    if ($listElement) {
                        $appendBreadcrumbs = [
                            array_merge($listElement, [
                                'position' => 1,
                                'name' => 'Pollenflug',
                                'item' => "https://$domain/pages/pollenflug-vorhersage"
                            ])
                        ];

                        $appendBreadcrumbs[] = array_merge($listElement, [
                            'position' => 2,
                            'name' => "Pollenflug in $cityName",
                            'item' => $proxyUrl
                        ]);

                        $itemListElements = $appendBreadcrumbs;
                    }
                }
                data_set($breadcrumbJson, 'itemListElement', $itemListElements);
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Letztes Update: ' . Carbon::parse($city->updated_at)->format('d/m/Y');
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            $this->removeElement($html, 'faq-schema-json');

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function nlPollenPage($request, $shop, $cityHandle)
    {
        $cityHandle = strtolower($cityHandle);
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $cityPageHandle = data_get($defaultLanguage, 'pivot.pollen_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $cities = $shop->pollenCities()
            ->with(['pollenState', 'pollenPageContent'])
            ->where('language_id', $defaultLanguage->id)
            ->orderBy('handle')->get();
        $city = $cities->where('handle', $cityHandle)->first();

        if (empty($city)) {
            return null;
        }

        $regions = $shop->pollenRegions()
            ->where('language_id', $defaultLanguage->id)
            ->with(['pollenCities' => function ($query) use ($defaultLanguage) {
                $query->where('language_id', $defaultLanguage->id)->orderBy('handle');
            }])->whereHas('pollenCities')->orderBy('handle')->get();

        if (!empty($city->pollenRegion) && data_get($city, 'pollenRegion.latitude') && data_get($city, 'pollenRegion.longitude')) {
            $regions = $shop->pollenRegions()
                ->where('language_id', $defaultLanguage->id)
                ->selectRaw(
                    'pollen_regions.*, (6371000 * acos(cos(radians(?)) * cos(radians(pollen_regions.latitude)) * cos(radians(pollen_regions.longitude) - radians(?)) + sin(radians(?)) * sin(radians(pollen_regions.latitude)))) AS distance',
                    [data_get($city, 'pollenRegion.latitude'), data_get($city, 'pollenRegion.longitude'), data_get($city, 'pollenRegion.latitude')]
                )->with(['pollenCities' => function ($query) use ($defaultLanguage) {
                    $query->where('language_id', $defaultLanguage->id)->orderBy('handle');
                }])->whereHas('pollenCities')->orderBy('distance')->orderBy('handle')->get();
        }

        $cityName = ucwords(strtolower($city->name));
        $region = data_get($city, 'pollenRegion.name');

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrl = "https://$domain/apps/pages/hooikoorts-pollenradar/$cityHandle";
            $pollenCityPageTitle = "Pollenradar $cityName | Kijk wat er vandaag in bloei staat";

            $this->setInnerText($html, 'title', $pollenCityPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $pollenCityPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $pollenCityPageTitle], 'find');

            $metaDescription = "Bekijk de pollenradar in $cityName. Ontdek hoe de pollenverwachting je kan helpen bij het verlichten van je hooikoortssymptomen.";
            $this->setAttributes($html, 'meta[name=description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $metaDescription], 'find');

            $element = $this->getElement($html, 'pollen-search');
            if (empty($element)) {
                return $html->save();
            }

            $searchParent = $element->parent();
            foreach ($searchParent->children() ?: [] as $child) {
                $child->remove();
            }

            $element = $html->createElement('select');
            $element->setAttribute('id', 'pollen-city-select');

            foreach ($cities as $cityData) {
                $optionElement = $html->createElement('option');
                $optionElement->setAttribute('name', $cityData->name);
                $optionElement->setAttribute('value', $cityData->handle);

                if ($city->id === $cityData->id) {
                    $optionElement->setAttribute('selected', true);
                    $element->setAttribute('data-name', $city->name);
                    $element->setAttribute('data-latitude', $city->latitude);
                    $element->setAttribute('data-longitude', $city->longitude);
                }

                $optionElement->innertext = $cityData->name;

                $element->appendChild($optionElement);
            }

            $searchParent->appendChild($element);

            if ($city->has_static_content) {

                $mainElement = $this->getElement($html, 'main', 'name');
                if (empty($mainElement)) {
                    return $html->save();
                }

                $breadcrumbHtml = '<div class="mt-14 pt-5 pt-md-2 pt-lg-7">
   <div class="container mt-5 col-11  mx-auto text-center">
      <ul class="breadcrumb-custom list-unstyled text-center d-inline-block">
         <li class="font-size-xl float-start">
            <a href="/" class="text-body">Home</a><span class="px-2 d-inline-block">/</span>
        </li>
         <li class="font-size-xl float-start ">
            <a href="/pages/hooikoorts-pollenradar" class="text-body"> Pollenradar
            </a><span class="px-2 d-inline-block">/</span>
         </li>
         <li class="font-size-xl float-start fw-bold">
            Pollenradar in ' . $cityName . '
         </li>
      </ul>
   </div>
</div>';

                $pollenCountHtml = $linkingHtml = $staticContentHtml = '';

                $element = $this->getElement($mainElement, 'pollen-div');
                if (!empty($element)) {
                    $pollenCountHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $element = $this->getElement($mainElement, 'pollen-location-index');
                if (!empty($element)) {
                    $linkingHtml = $element->parent() ? $element->parent()->outertext : $element->outertext;
                }

                $staticContentHtml = '<div>' . data_get($city, 'pollenPageContent.content') . '</div>';
                $variables = data_get($city, 'variables') ?: [];
                if (empty($variables)) {
                    $staticContentHtml = str_replace('[[city]]', $cityName, $staticContentHtml);
                } else {
                    foreach ($variables as $key => $value) {
                        $staticContentHtml = str_replace("[[$key]]", $value, $staticContentHtml);
                    }
                }

                $staticContentHtml = str_get_html($staticContentHtml);
                if (empty($staticContentHtml) && empty($staticContentHtml->firstChild())) {
                    return $html->save();
                }

                $element = $this->getElement($staticContentHtml, 'breadcrumb-top');
                if (!empty($element)) {
                    $element->outertext = $breadcrumbHtml;
                }

                $element = $this->getElement($staticContentHtml, 'pollen-counter-block');
                if (!empty($element)) {
                    $element->outertext = $pollenCountHtml;
                }

                $element = $this->getElement($staticContentHtml, 'pollen-location-index');
                if (!empty($element)) {
                    $element->outertext = $this->preparePollenLinkingModule($shop, $html, $regions) ?: $linkingHtml;
                }

                $staticContentHtml = $staticContentHtml->firstChild()?->innertext;

                foreach ($mainElement->children() as $childNode) {
                    $mainElement->removeChild($childNode);
                }

                $mainHtml = $staticContentHtml;

                $mainElement->innertext = $mainHtml;
            }

            $element = $this->getElement($html, 'timestamp-date'); // In live selector is not present
            if (!empty($element)) {
                $element->innertext = 'Laatst bewerkt: ' . Carbon::parse('2024-05-07')->format('d/m/Y');
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            $this->removeElement($html, 'faq-schema-json');

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function usPollenPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getPollenInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $states = ProxyHelper::getPollenStates($shop, $language, $city);

        $cityName = ucwords(strtolower($city->name));
        $state = data_get($city, 'pollenState.name') ? ucwords(strtolower(data_get($city, 'pollenState.name'))) : strtoupper(data_get($city, 'pollenState.code'));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_POLLEN,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $pollenPageTitle = "$cityName pollen count today | Stay 1 step ahead of your hay fever";
            $this->setTitleTag($html, $pollenPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $pollenPageTitle);
            $this->setMetaTwitterTitleTag($html, $pollenPageTitle);

            $this->setAllMetaDescriptionTags($html, "Pollen allergy? Check the pollen count in  $cityName. Use it to help manage your allergy symptoms and make the most of every day.");

            $this->setPollenBannerTitle($html, "$cityName Pollen Count");
            $this->setPollenBannerDescription($html, "<p>Ready to make the most of every day?<br>Get daily updates on the $cityName pollen count for trees, weeds and grasses.</p>", true);

            $subDomain = getSubdomain($shop->name);
            $key = "pollen.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $state,
                $value
            ]));
            $this->setPollenSearch($html, $searchString);
            $this->setPollenSearchLatLong($html, $city);

            $this->preparePollenLinkingModule($shop, $html, $states);

            // Selectors [[city]] and [[city, state]]
            $pollenStaticContentData = [
                'cityName' => $cityName,
                'state' => $state
            ];
            $this->setPollenStaticContent($html, $city, $pollenStaticContentData);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function atPollenPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getPollenInitialEntities($shop, $cityHandle, $languageCode);
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
                ProxyHelper::TYPE_POLLEN,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $pollenPageTitle = "Pollenflug in $cityName | Erhalte die heutige Pollenvorhersage deiner Stadt";
            $pollenMetaDescription = "Beobachte den heutigen Pollenflug in $cityName. Nutze die Pollenvorhersage, um mit deinen Heuschnupfen-Symptomen besser zurechtzukommen!";
            $this->setTitleTag($html, $pollenPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $pollenPageTitle);
            $this->setMetaTwitterTitleTag($html, $pollenPageTitle);
            $this->setAllMetaDescriptionTags($html, $pollenMetaDescription);

            $this->removeNoIndexTag($html);
            $this->setPollenBannerTitle($html, "Pollenflugvorhersage für $cityName");
            $this->setPollenBannerDescription($html, "<p>Bereit, um das Beste aus jedem Tag zu machen?<br>Erhalte tägliche Pollendaten für $cityName. Informiere dich über die Pollenbelastung durch Bäume, Gräser und Kräuter in deiner Region.</p>", true);

            $subDomain = getSubdomain($shop->name);
            $key = "pollen.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setPollenSearch($html, $searchString);
            $this->setPollenSearchLatLong($html, $city);

            $this->preparePollenLinkingModule($shop, $html);

            // Selectors [[city]] and [[city, state]]
            $pollenStaticContentData = [
                'cityName' => $cityName,
            ];
            $this->setPollenStaticContent($html, $city, $pollenStaticContentData);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function czPollenPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getPollenInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $cityName = ucwords(strtolower($city->name));
        $staticContentCityName = data_get($city, 'variables.city');

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_POLLEN,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $pollenPageTitle = 'Množství pylu v ' . ($staticContentCityName ?: $cityName) . ' | Podívejte se na pylovou předpověď pro vaše město';
            $this->setTitleTag($html, $pollenPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $pollenPageTitle);
            $this->setMetaTwitterTitleTag($html, $pollenPageTitle);

            $subDomain = getSubdomain($shop->name);
            $metaDescriptionKey = "pollen.$subDomain.meta_description";
            $metaDescription = getLocale($metaDescriptionKey, $languageCode, ['cityName' => $staticContentCityName ?: $cityName]);
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $this->setPollenBannerTitle($html, 'Množství pylu v ' . ($staticContentCityName ?: $cityName));
            $this->setPollenBannerDescription($html, '<p>Chcete vytěžit z každého dne maximum?<br>Sledujte naši stránku, kde se denně objevují aktuální informace o množství pylu stromů, plevelů a trav v ' . ($staticContentCityName ?: $cityName) .'.</p>', true);

            $key = "pollen.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setPollenSearch($html, $searchString);
            $this->setPollenSearchLatLong($html, $city);

            // Selectors [[city]] and [[city, state]]
            $pollenStaticContentData = [
                'cityName' => $cityName
            ];
            $this->setPollenStaticContent($html, $city, $pollenStaticContentData);

            $this->setPollenCityMedicalStructureJsonScriptEl(
                $html,
                $this->createPollenCityMedicalSchemaJson($shop, $city, $cityHandle, $languageCode)
            );

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function skPollenPage($request, $shop, $cityHandle, $languageCode = null)
    {
        $initialEntities = $this->getPollenInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $cityName = ucwords(strtolower($city->name));
        $staticContentCityName = data_get($city, 'variables.city_d1');

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_POLLEN,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, $proxyUrlPrefix);

            $pollenPageTitle = 'Množstvo peľu v ' . ($staticContentCityName ?: $cityName) . ' | Pozrite si predpoveď peľu pre vaše mesto';
            $this->setTitleTag($html, $pollenPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $pollenPageTitle);
            $this->setMetaTwitterTitleTag($html, $pollenPageTitle);

            $metaDescription = 'Skontrolujte množstvo peľu v ' . ($staticContentCityName ?: $cityName) . '. Zistite, ako vám peľová predpoveď pomôže zvládnuť príznaky alergickej nádchy';
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $this->setPollenBannerTitle($html, 'Množstvo peľu v ' . ($staticContentCityName ?: $cityName));
            $this->setPollenBannerDescription($html, '<p>Chcete vyťažiť z každého dňa maximum?<br>Sledujte našu stránku, kde každý deň nájdete aktuálne informácie o množstve peľu v ' . ($staticContentCityName ?: $cityName) . ' zo stromov, burín a tráv.</p>', true);

            $subDomain = getSubdomain($shop->name);
            $key = "pollen.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));
            $this->setPollenSearch($html, $searchString);
            $this->setPollenSearchLatLong($html, $city);

            $pollenStaticContentData = [
                'cityName' => $cityName
            ];
            $this->setPollenStaticContent($html, $city, $pollenStaticContentData);

            $this->setPollenCityMedicalStructureJsonScriptEl(
                $html,
                $this->createPollenCityMedicalSchemaJson($shop, $city, $cityHandle, $languageCode)
            );

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function chPollenPage($request, $shop, $cityHandle, $languageCode = 'de')
    {
        $initialEntities = $this->getPollenInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $cityName = ucwords(strtolower($city->name));
        $subDomain = getSubdomain($shop->name);

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $this->removeNoIndexTag($html);

            $languageUrls = ProxyHelper::getLanguageUrls($shop, ProxyHelper::TYPE_POLLEN, null, $city);
            $proxyUrl = data_get($languageUrls, $languageCode);

            $translationData = [
                'cityName' => $cityName
            ];

            $pollenPageTitle = getLocale("pollen.$subDomain.page_title", $languageCode, $translationData);
            $this->setTitleTag($html, $pollenPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $pollenPageTitle);
            $this->setMetaTwitterTitleTag($html, $pollenPageTitle);

            $metaDescription = getLocale("pollen.$subDomain.meta_description", $languageCode, $translationData);
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $this->setPollenBannerTitle($html, getLocale("pollen.$subDomain.banner_title", $languageCode, $translationData));
            $this->setPollenBannerDescription($html, getLocale("pollen.$subDomain.banner_description", $languageCode, $translationData), true);

            $key = "pollen.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));

            $this->setPollenSearch($html, $searchString);
            $this->setPollenSearchLatLong($html, $city);

            $pollenStaticContentData = [
                'cityName' => $cityName
            ];
            $this->setPollenStaticContent($html, $city, $pollenStaticContentData);

            $this->setPollenLanguageLinksJsonScriptEl($html, $languageUrls);

            $this->setPollenCityMedicalStructureJsonScriptEl(
                $html,
                $this->createPollenCityMedicalSchemaJson($shop, $city, $cityHandle, $languageCode)
            );

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function caPollenPage($request, $shop, $cityHandle, $languageCode = 'en')
    {
        $initialEntities = $this->getPollenInitialEntities($shop, $cityHandle, $languageCode);
        if (empty($initialEntities)) {
            return null;
        }

        $language = data_get($initialEntities, 'language');
        $languageCode = data_get($language, 'code');
        $cityPage = data_get($initialEntities, 'cityPage');
        $city = data_get($initialEntities, 'city');

        $states = ProxyHelper::getPollenStates($shop, $language, $city);

        $cityName = ucwords(strtolower($city->name));
        $subDomain = getSubdomain($shop->name);

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($cityPage);

            $this->removeNoIndexTag($html);

            $languageUrls = ProxyHelper::getLanguageUrls($shop, ProxyHelper::TYPE_POLLEN, null, $city);
            $proxyUrl = data_get($languageUrls, $languageCode);

            $translationData = [
                'city' => $cityName
            ];
            $pollenPageTitle = getLocale("pollen.$subDomain.page_title", $languageCode, $translationData);
            $this->setTitleTag($html, $pollenPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaTitleTag($html, $pollenPageTitle);
            $this->setMetaTwitterTitleTag($html, $pollenPageTitle);

            $metaDescription = getLocale("pollen.$subDomain.meta_description", $languageCode, $translationData);
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $this->setPollenBannerTitle($html, getLocale("pollen.$subDomain.banner_title", $languageCode, $translationData));
            $this->setPollenBannerDescription($html, getLocale("pollen.$subDomain.banner_description", $languageCode, $translationData), true);

            $key = "pollen.$subDomain.search_suffix";
            $value = getLocale($key, $languageCode);
            $searchString = implode(', ', array_filter([
                $cityName,
                $value
            ]));

            $this->setPollenSearch($html, $searchString);
            $this->setPollenSearchLatLong($html, $city);

            $pollenStaticContentData = [
                'cityName' => $cityName
            ];
            $this->setPollenStaticContent($html, $city, $pollenStaticContentData);
            $this->preparePollenLinkingModule($shop, $html, $states, $language);
            $this->setPollenLanguageLinksJsonScriptEl($html, $languageUrls);

            return  $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function pollenSitemapUrls($request, $shop, $languageCode = null)
    {
        try {
            $language = empty($languageCode) ?
                $shop->languages->where('pivot.default', true)->first() :
                $shop->languages->where('code', $languageCode)->first();

            $pollenCities = $shop->pollenCities->where('language_id', $language->id)->toArray();

            $xmlData = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"><!--  This is the sitemap linking to additional city pages  -->';

            foreach ($pollenCities as $pollenCity) {

                $pollenCityHandle = data_get($pollenCity, 'handle');

                if (empty($pollenCityHandle)) continue;

                $domain = $shop->public_domain ?: $shop->name;

                $url = match ($shop->name) {
                    User::ALK_NO_STORE => "https://$domain/apps/pages/pollenvarsel/by/$pollenCityHandle",
                    User::ALK_NL_STORE => "https://$domain/apps/pages/hooikoorts-pollenradar/$pollenCityHandle",
                    User::ALK_DK_STORE => "https://$domain/apps/pages/pollental/by/$pollenCityHandle",
                    User::ALK_DE_STORE => "https://$domain/apps/pages/pollenflug-vorhersage/stadt/$pollenCityHandle",
                    User::KLARIFY_US_STORE => "https://$domain/apps/pages/pollen-forecast/$pollenCityHandle",
                    User::KLARIFY_AT_STORE => "https://$domain/apps/pages/pollenflugvorhersage/$pollenCityHandle",
                    User::KLARIFY_CZ_STORE => "https://$domain/apps/pages/mnozstvi-pylu-dnes/$pollenCityHandle",
                    User::KLARIFY_SK_STORE => "https://$domain/apps/pages/mnozstvo-pelu-dnes/$pollenCityHandle",
                    User::KLARIFY_CH_STORE => match ($languageCode) {
                        'de' => "https://$domain/apps/pages/pollenbelastung-heute/$pollenCityHandle",
                        'fr' => "https://$domain/apps/pages/charge-pollinique-aujourdhui/$pollenCityHandle",
                        'it' => "https://$domain/apps/pages/la-concentrazione-pollinica-oggi/$pollenCityHandle",
                        default => "https://$domain/apps/pages/pollenbelastung-heute/$pollenCityHandle",
                    },
                    User::KLARIFY_CA_STORE => match ($languageCode) {
                        'de' => "https://$domain/apps/pages/pollen-forecast/$pollenCityHandle",
                        'fr' => "https://$domain/apps/pages/comptage-du-pollen-aujourdhui/$pollenCityHandle",
                        default => "https://$domain/apps/pages/pollen-forecast/$pollenCityHandle",
                    },
                    default => "https://$domain/apps/pages/forecast/cities/$pollenCityHandle",
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
