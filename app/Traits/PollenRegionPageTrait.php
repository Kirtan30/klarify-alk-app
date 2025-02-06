<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Carbon;

trait PollenRegionPageTrait
{
    use HtmlDomTrait, PollenHtmlDomTrait, ProxyTrait, ExtendedPollenRegionPageTrait;

    public function noPollenPage($request, $shop, $regionHandle)
    {
        $regionHandle = strtolower($regionHandle);
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $regionPageHandle = data_get($defaultLanguage, 'pivot.pollen_region_page');
        $regionPage = $this->getShopifyPage($shop, $regionPageHandle);

        if (!$regionPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $regions = $shop->pollenRegions()
            ->with('pollenPageContent')
            ->where('language_id', $defaultLanguage->id)
            ->orderBy('handle')->get();
        $region = $regions->where('handle', $regionHandle)->first();

        if (empty($region)) {
            return null;
        }

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($regionPage);

            $proxyUrl = "https://$domain/apps/pages/pollenvarsel/fylke/$regionHandle";
            $pollenRegionPageTitle = "Pollenvarsel for $region->name | Sjekk pollenprognonsen i dag!";
            $pollenRegionMetaDescription = "Bor du i $region->name og har allergisymptomer? Få hjelp til å planlegge dagen. For nøyaktig og alltid oppdatert pollenvarsel for ditt område – se her";

            $this->setInnerText($html, 'title', $pollenRegionPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $pollenRegionPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $pollenRegionPageTitle], 'find');

            $this->setAttributes($html, 'meta[name=description]', ['content' => $pollenRegionMetaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $pollenRegionMetaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $pollenRegionMetaDescription], 'find');

            $element = $this->getElement($html, 'pollen-search');
            if (empty($element)) {
                return $html->save();
            }

            $searchParent = $element->parent();
            $element->remove();

            $element = $html->createElement('select');
            $element->setAttribute('id', 'pollen-region-select');

            foreach ($regions as $regionData) {
                $optionElement = $html->createElement('option');
                $optionElement->setAttribute('name', $regionData->name);
                $optionElement->setAttribute('value', $regionData->handle);

                if ($region->id === $regionData->id) {
                    $optionElement->setAttribute('selected', true);
                    $element->setAttribute('data-name', $region->name);
                    $element->setAttribute('data-latitude', $region->latitude);
                    $element->setAttribute('data-longitude', $region->longitude);
                }

                $optionElement->innertext = $regionData->name;

                $element->appendChild($optionElement);
            }

            $searchParent->appendChild($element);

            $breadcrumbHtml = '<div class="breadcrumb-top" id="breadcrumb-top">
    <div class="mt-3 mt-lg-5 text-center breadcrumb-spacing">
        <div class="container mt-5 col-12 mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a href="/">Hjem</a><span>/</span></li>
                <li><a href="/pages/pollenvarsel"> Pollenvarsel </a><span>/</span></li>
                <li class="fw-bold"> Pollenvarsel ' . $region->name . ' </li>
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

            if ($region->has_static_content) {
                $pollenStaticContent = data_get($region, 'pollenPageContent.content');
                $pollenStaticContent = str_replace('[[region]]', $region->name, $pollenStaticContent);

                $staticContentVariables = data_get($region, 'pollenPageContent.variables') ?: [];

                foreach ($staticContentVariables as $staticContentVariable) {
                    $variableValue = data_get($region, "variables.$staticContentVariable");
                    if (in_array($staticContentVariable, ['region']) && empty($variableValue)) {
                        $variableValue = $region->name;
                    }
                    $pollenStaticContent = str_replace("[[$staticContentVariable]]", $variableValue, $pollenStaticContent);
                }

                $pollenStaticContentEl = str_get_html('<div id="pollen-static-content">'.$pollenStaticContent.'</div>');
                if (!empty($pollenStaticContentEl)) {
                    $pollenCountEl = $this->getElement($pollenStaticContentEl, 'pollen-counter-block');
                    $this->setOuterText($pollenStaticContentEl, 'pollen-location-index', $linkingModuleHtml);

                    if (!empty($pollenCountEl)) {
                        $pollenCountEl->outertext = $pollenCountHtml;
                        $pollenStaticContentEl = $pollenStaticContentEl->firstChild();

                        $pollenStaticContentHtml = !empty($pollenStaticContentEl) ? $pollenStaticContentEl->innertext : '';

                        if (str_contains($pollenStaticContent, 'pollen-location-index')) {
                            $innerHtml = $breadcrumbHtml . $pollenStaticContentHtml;
                        } else {
                            $innerHtml = $breadcrumbHtml . $pollenStaticContentHtml . $linkingModuleHtml;
                        }

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
                            ]),
                            array_merge($listElement, [
                                'position' => 2,
                                'name' => "Pollenvarsel $region->name",
                                'item' => $proxyUrl
                            ])
                        ];

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

            /*$element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Sist oppdatert ' . Carbon::parse($region->updated_at)->format('d/m/Y');
            }*/

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function dkPollenPage($request, $shop, $regionHandle)
    {
        $regionHandle = strtolower($regionHandle);
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $languageCode = strtolower(data_get($defaultLanguage, 'code'));

        $pollenPageHandle = data_get($defaultLanguage, 'pivot.pollen_region_page');
        $regionPage = $this->getShopifyPage($shop, $pollenPageHandle);

        if (!$regionPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $regions = $shop->pollenRegions()
            ->with('pollenPageContent')
            ->where('language_id', $defaultLanguage->id)
            ->orderBy('handle')->get();
        $region = $regions->where('handle', $regionHandle)->first();

        if (empty($region)) {
            return null;
        }

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($regionPage);

            $proxyUrl = "https://$domain/apps/pages/pollental/$regionHandle";
            $pollenRegionPageTitle = "Pollen | Dagens pollental for $region->name | Pollentjek ";
            $pollenRegionMetaDescription = "Vær opdateret på de nyeste pollental. Når du følger Pollental, kan du bedre planlægge din dag. Se her";

            $this->setInnerText($html, 'title', $pollenRegionPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $pollenRegionPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $pollenRegionPageTitle], 'find');

            $this->setAttributes($html, 'meta[name=description]', ['content' => $pollenRegionMetaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $pollenRegionMetaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $pollenRegionMetaDescription], 'find');

            $element = $this->getElement($html, 'pollen-select-location');
            if (empty($element)) {
                return $html->save();
            }

            $searchParent = $element->parent();
            $element->remove();

            $element = $html->createElement('select');
            $element->setAttribute('id', 'pollen-region-select');

            foreach ($regions as $regionData) {
                $optionElement = $html->createElement('option');
                $optionElement->setAttribute('name', $regionData->name);
                $optionElement->setAttribute('value', $regionData->handle);

                if ($region->id === $regionData->id) {
                    $optionElement->setAttribute('selected', true);
                    $element->setAttribute('data-name', $region->name);
                    $element->setAttribute('data-latitude', $region->latitude);
                    $element->setAttribute('data-longitude', $region->longitude);
                }

                $optionElement->innertext = $regionData->name;

                $element->appendChild($optionElement);
            }

            $searchParent->appendChild($element);

            $breadcrumbHtml = '<div class="breadcrumb-direct-top" id="breadcrumb-top">
    <div>
        <div class="container mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a class="text-decoration-none" href="/">Forside</a><span>/</span></li>
                <li><a class="text-decoration-none" href="/pages/'.$pollenPageHandle.'"> Pollental </a><span>/</span></li>
                <li class="fw-bold"> ' . $region->name . ' </li>
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

            if ($region->has_static_content) {
                $pollenStaticContent = data_get($region, 'pollenPageContent.content');
                $pollenStaticContent = str_replace('[[region]]', $region->name, $pollenStaticContent);

                $staticContentVariables = data_get($region, 'pollenPageContent.variables') ?: [];

                foreach ($staticContentVariables as $staticContentVariable) {
                    $variableValue = data_get($region, "variables.$staticContentVariable");
                    if (in_array($staticContentVariable, ['region']) && empty($variableValue)) {
                        $variableValue = $region->name;
                    }
                    $pollenStaticContent = str_replace("[[$staticContentVariable]]", $variableValue, $pollenStaticContent);
                }

                $pollenStaticContentEl = str_get_html('<div id="pollen-static-content">'.$pollenStaticContent.'</div>');
                if (!empty($pollenStaticContentEl)) {
                    $pollenCountEl = $this->getElement($pollenStaticContentEl, 'pollen-counter-block');
                    $this->setOuterText($pollenStaticContentEl, 'pollen-location-index', $linkingModuleHtml);

                    if (!empty($pollenCountEl)) {
                        $pollenCountEl->outertext = $pollenCountHtml;
                        $pollenStaticContentEl = $pollenStaticContentEl->firstChild();

                        $pollenStaticContentHtml = !empty($pollenStaticContentEl) ? $pollenStaticContentEl->innertext : '';

                        if (str_contains($pollenStaticContent, 'pollen-location-index')) {
                            $innerHtml = $breadcrumbHtml . $pollenStaticContentHtml;
                        } else {
                            $innerHtml = $breadcrumbHtml . $pollenStaticContentHtml . $linkingModuleHtml;
                        }

                    } else {
                        $innerHtml = $breadcrumbHtml . $innerHtml;
                    }
                }

            } else {
                $innerHtml = $breadcrumbHtml . $innerHtml;
            }

            $element->innertext = $innerHtml;

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                /*$element->innertext = 'Sidst opdateret ' . Carbon::parse($region->updated_at)->format('d/m/Y');*/ //should be used after launch
                $element->innertext = 'Sidst opdateret 05/02/2024';
            }

            $this->removeElement($html, 'BreadcrumbList-schema');
            $this->setPollenRegionMedicalStructureJsonScriptEl(
                $html,
                $this->createPollenRegionMedicalSchemaJson($shop, $region, $pollenPageHandle, $languageCode)
            );

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


    public function pollenSitemapUrls($request, $shop)
    {
        try {
            $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

            $pollenRegions = $shop->pollenRegions->where('language_id', $defaultLanguage->id)->toArray();

            $xmlData = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"><!--  This is the sitemap linking to additional city pages  -->';

            foreach ($pollenRegions as $pollenRegion) {

                $pollenRegionHandle = data_get($pollenRegion, 'handle');

                if (empty($pollenRegionHandle)) continue;

                $domain = $shop->public_domain ?: $shop->name;

                $url = match ($shop->name) {
                    User::ALK_NO_STORE => "https://$domain/apps/pages/pollenvarsel/fylke/$pollenRegionHandle",
                    User::ALK_DK_STORE => "https://$domain/apps/pages/pollental/$pollenRegionHandle",
                    default => "https://$domain/apps/pages/forecast/regions/$pollenRegionHandle",
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
