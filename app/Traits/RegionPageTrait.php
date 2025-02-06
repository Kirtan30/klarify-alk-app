<?php

namespace App\Traits;

use App\Helpers\ProxyHelper;
use App\Models\User;
use Illuminate\Support\Carbon;

trait RegionPageTrait
{
    use HtmlDomTrait, RegionHtmlDomTrait, ExtendedRegionPageTrait, ProxyTrait;

    public function noPage($request, $shop, $regionHandle)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $regionHandle = strtolower($regionHandle);

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $fadPageHandle = data_get($defaultLanguage, 'pivot.fad_page');

        $regionPageHandle = data_get($defaultLanguage, 'pivot.fad_region_page');
        $regionPage = $this->getShopifyPage($shop, $regionPageHandle);

        if (!$regionPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $region = $shop->fadRegions()
            ->with('fadPageContent')
            ->where('language_id', $defaultLanguage->id)
            ->where('handle', $regionHandle)->first();

        if (empty($region)) {
            return null;
        }

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($regionPage);

            $regionPageTitle = "$region->name - Finn din nærmeste allergispesialist";
            $proxyUrl = "https://$domain/apps/pages/allergi-klinikker/fylke/$regionHandle";

            $this->setInnerText($html, 'title', $regionPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $regionPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $regionPageTitle], 'find');

            $this->removeElement($html, 'meta[name=description]', 'find');

            $mainElement = $this->getElement($html, 'main', 'name');

            $element = $this->getElement($html, 'fad-search');
            if (!empty($element)) {
                $element->setAttribute('value', "$region->name, Norge");
                $element->setAttribute('data-search', "$region->name, Norge");
            }

            if (empty($mainElement)) {
                return $html->save();
            }

            $breadcrumbHtml = '<div class="breadcrumb-top" id="breadcrumb-top">
    <div class="mt-3 mt-lg-5 text-center breadcrumb-spacing">
        <div class="container mt-5 col-12 mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a href="/">Hjem</a><span>/</span></li>
                <li><a href="/pages/fa-hjelp-med-allergien"> Få Hjelp med Allergien </a><span>/</span></li>
                <li><a href="/pages/'.$fadPageHandle.'"> Søk lege - Her finnes hjelp </a><span>/</span></li>
                <li class="fw-bold"> Klinikker i ' . $region->name . ' </li>
            </ul>
        </div>
    </div>
</div>';

            $mapHtml = $linkingHtml = $staticContentHtml = '';

            $element = $this->getElement($mainElement, 'fad-map-full');
            if (!empty($element)) {
                $mapHtml = $element->outertext;
            }

            $element = $this->getElement($mainElement, 'fad-location-index');
            if (!empty($element)) {
                $linkingHtml = $element->outertext;
            }

            if ($region->has_static_content) {

                $staticContentHtml = data_get($region, 'fadPageContent.content');
                $staticContentHtml = '<div>' . $staticContentHtml . '</div>';

                $staticContentVariables = data_get($region, 'fadPageContent.variables') ?: [];

                foreach ($staticContentVariables as $staticContentVariable) {
                    $variableValue = data_get($region, "variables.$staticContentVariable");
                    $staticContentHtml = str_replace("[[$staticContentVariable]]", $variableValue, $staticContentHtml);
                }

                $staticContentHtml = str_get_html($staticContentHtml);
                if (empty($staticContentHtml) && empty($staticContentHtml->firstChild())) {
                    return $html->save();
                }

                $element = $this->getElement($staticContentHtml, 'fad-map-full');
                if (!empty($element)) {
                    $element->outertext = $mapHtml;
                }

                $element = $this->getElement($staticContentHtml, 'fad-location-index');
                if (!empty($element)) {
                    $element->outertext = $linkingHtml;
                }

                $staticContentHtml = $staticContentHtml->firstChild()?->innertext;
            }

            foreach ($mainElement->children() as $childNode) {
                $mainElement->removeChild($childNode);
            }

            $mainHtml = $breadcrumbHtml . $staticContentHtml;

            $mainElement->innertext = $mainHtml;

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
                                'name' => 'Få Hjelp med Allergien',
                                'item' => "https://$domain/pages/fa-hjelp-med-allergien"
                            ]),
                            array_merge($listElement, [
                                'position' => 2,
                                'name' => 'Søk lege - Her finnes hjelp',
                                'item' => "https://$domain/pages/sok-legeher-finnes-hjelp"
                            ]),
                            array_merge($listElement, [
                                'position' => 3,
                                'name' => "Klinikker i $region->name",
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

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Sist oppdatert ' . Carbon::parse($region->updated_at)->format('d/m/Y');
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .social-icon ', 'find');
            if (!empty($element)) {
                $element->remove();
            }

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function dkPage($request, $shop, $regionHandle)
    {
        $domain = $shop->public_domain ?: $shop->name;
        $subDomain = getSubdomain($shop->name);

        $regionHandle = strtolower($regionHandle);

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }
        $languageCode = data_get($defaultLanguage, 'code');

        $fadPageHandle = data_get($defaultLanguage, 'pivot.fad_page');

        $regionPageHandle = data_get($defaultLanguage, 'pivot.fad_region_page');
        $regionPage = $this->getShopifyPage($shop, $regionPageHandle);

        if (!$regionPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $region = $shop->fadRegions()
            ->with('fadPageContent')
            ->where('language_id', $defaultLanguage->id)
            ->where('handle', $regionHandle)->first();

        if (empty($region)) {
            return null;
        }

        $clinics = ProxyHelper::getRegionClinics($request, $shop, $region);

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($regionPage);

            $this->removeNoIndexTag($html);

            $regionName = $region->name;
            $regionPageTitleKey = "fad_region.$subDomain.title";
            $regionPageTitle = getLocale($regionPageTitleKey, $languageCode, ['regionName' => $regionName]);
            $proxyUrl = "https://$domain/apps/pages/allergilaeger/$regionHandle";

            $this->setInnerText($html, 'title', $regionPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $regionPageTitle], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $regionPageTitle], 'find');

            $this->setMetaDescriptionTag(
                $html,
                "Brug vores søgefunktion, og find en allergiklinik eller allergilæge i $regionName, som kan hjælpe med din pollenallergi."
            );

            $mainElement = $this->getElement($html, 'main', 'name');

            $element = $this->getElement($html, 'fad-search');
            if (!empty($element)) {
                $element->setAttribute('value', "$regionName, Danmark");
            }

            if (empty($mainElement)) {
                return $html->save();
            }

            $breadcrumbHtml = '<div class="breadcrumb-direct-top" id="breadcrumb-top">
    <div>
        <div class="container mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a class="text-decoration-none" href="/">Forside</a><span>/</span></li>
                <li><a class="text-decoration-none" href="/pages/'.$fadPageHandle.'"> Find allergilaeger </a><span>/</span></li>
                <li class="fw-bold"> Region ' . $regionName . ' </li>
            </ul>
        </div>
    </div>
</div>';

            $mapHtml = $linkingHtml = $staticContentHtml = '';

            $element = $this->getElement($mainElement, 'fad-map-full');
            if (!empty($element)) {
                $mapHtml = $element->outertext;
            }

            $element = $this->getElement($mainElement, 'fad-location-index');
            if (!empty($element)) {
                $linkingHtml = $element->outertext;
            }

            if ($region->has_static_content) {

                $staticContentHtml = data_get($region, 'fadPageContent.content');
                $staticContentHtml = '<div>' . $staticContentHtml . '</div>';

                $staticContentVariables = data_get($region, 'fadPageContent.variables') ?: [];

                foreach ($staticContentVariables as $staticContentVariable) {
                    $variableValue = data_get($region, "variables.$staticContentVariable");
                    $staticContentHtml = str_replace("[[$staticContentVariable]]", $variableValue, $staticContentHtml);
                }

                $staticContentHtml = str_get_html($staticContentHtml);
                if (empty($staticContentHtml) && empty($staticContentHtml->firstChild())) {
                    return $html->save();
                }

                $element = $this->getElement($staticContentHtml, 'fad-map-full');
                if (!empty($element)) {
                    $element->outertext = $mapHtml;
                }

                $element = $this->getElement($staticContentHtml, 'fad-location-index');
                if (!empty($element)) {
                    $element->outertext = $linkingHtml;
                }

                $staticContentHtml = $staticContentHtml->firstChild()?->innertext;
            }

            foreach ($mainElement->children() as $childNode) {
                $mainElement->removeChild($childNode);
            }

            $mainHtml = $breadcrumbHtml . $staticContentHtml;

            $mainElement->innertext = $mainHtml;

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                /*$element->innertext = 'Sidst opdateret ' . Carbon::parse($region->updated_at)->format('d/m/Y');*/ //after launch
                $element->innertext = 'Sidst opdateret 05/02/2024';
            }

            $this->removeElement($html, 'BreadcrumbList-schema');
            $this->setRegionMedicalStructureJsonScriptEl(
                $html,
                $this->createRegionMedicalSchemaJson($shop, $region, $fadPageHandle, $clinics, $languageCode)
            );

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .social-icon ', 'find');
            if (!empty($element)) {
                $element->remove();
            }

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function sitemapUrls($request, $shop)
    {
        try {
            $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

            $fadRegions = $shop->fadRegions->where('language_id', $defaultLanguage->id)->toArray();

            $xmlData = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"><!--  This is the sitemap linking to additional city pages  -->';

            foreach ($fadRegions as $fadRegion) {

                $fadRegionHandle = data_get($fadRegion, 'handle');

                if (empty($fadRegionHandle)) continue;

                $domain = $shop->public_domain ?: $shop->name;

                $url = match ($shop->name) {
                    User::ALK_NO_STORE => "https://$domain/apps/pages/allergi-klinikker/fylke/$fadRegionHandle",
                    User::ALK_DK_STORE => "https://$domain/apps/pages/allergilaeger/$fadRegionHandle",
                    default => "https://$domain/apps/pages/clinics/regions/$fadRegionHandle",
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
