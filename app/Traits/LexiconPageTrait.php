<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Carbon;

trait LexiconPageTrait
{
    use HtmlDomTrait, ProxyTrait;

    public function dePage($request, $shop, $lexiconHandle = null)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $country = $shop->country;

        if (empty($country)) {
            return null;
        }

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $lexiconPageHandle = data_get($defaultLanguage, 'pivot.lexicon_page');

        try {
            if ($lexiconHandle) {
                $lexicon = $shop->lexicons()->where('handle', $lexiconHandle)->first();
            } else {
                $lexicon = $shop->lexicons()->orderBy('name')->first();
            }

            if (empty($lexicon)) {
                return null;
            }

            $lexiconPage = $this->getShopifyPage($shop, $lexiconPageHandle);

            if (!$lexiconPage) {
                return null;
            }

            $this->loadSimpleHtmlDom();
            $html = str_get_html($lexiconPage);

            $element = $this->getElement($html, 'meta[name=robots]', 'find');
            if (!empty($element)) $element->remove();

            if ($lexiconHandle) {
                $lexiconType = data_get($lexicon, 'name');
                $lexiconPageTitle = "$lexiconType - Entdecke hier Informationen über $lexiconType";
                $proxyUrl = "https://$domain/apps/pages/allergie-lexikon/$lexiconHandle";

                $this->setInnerText($html, 'title', $lexiconPageTitle, 'name');
                $this->setAttributes($html, 'meta[property=og:title]', ['content' => $lexiconPageTitle], 'find');
                $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $lexiconPageTitle], 'find');

                $metaDescription = "Lies unsere Definition zu $lexiconType um zu verstehen, was es bedeutet. In unserem Allergielexikon findest du weitere Informationen rund um das Thema Allergie.";
                $this->setAttributes($html, 'meta[name=description]', ['content' => $metaDescription], 'find');
                $this->setAttributes($html, 'meta[property=og:description]', ['content' => $metaDescription], 'find');
                $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $metaDescription], 'find');

                // Breadcrumb
                $breadcrumbHtml = '<div class="breadcrumb-top" id="breadcrumb-top">
    <div class="mt-3 mt-lg-5 text-center breadcrumb-spacing">
        <div class="container mt-5 col-12 mx-auto text-center">
            <ul class="breadcrumb-custom">
                <li><a href="/">Startseite</a><span>/</span></li>
                <li><a href="/pages/service">Service</a><span>/</span></li>
                <li><a href="/apps/pages/allergie-lexikon"> Allergie-Lexikon </a><span>/</span></li>
                <li class="fw-bold"> ' . ucwords(strtolower($lexicon->name)) . ' </li>
            </ul>
        </div>
    </div>
</div>';
                $element = $this->getElement($html, 'breadcrumb-top');
                if (!empty($element)) {
                    $element->outertext = $breadcrumbHtml;
                }
            } else {
                $proxyUrl = "https://$domain/apps/pages/allergie-lexikon";
            }

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');
            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $staticContent = data_get($lexicon, 'content');
            $element = $this->getElement($html, 'lexicon-static-content');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    $child->remove();
                }
                $element->innertext = $staticContent;
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
                $element->innertext = 'Letztes Update: ' . Carbon::parse(data_get($lexicon, 'date') ?: data_get($lexicon, 'updated_at'))->format('d/m/Y');
            }

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $lastBreadcrumb = array_pop($itemListElements);
                    $appendBreadcrumbs = [
                        array_merge($lastBreadcrumb, [
                            'name' => 'Allergie-Lexikon',
                            'item' => "https://$domain/apps/pages/allergie-lexikon"
                        ])
                    ];
                    if ($lexiconHandle) {
                        $appendBreadcrumbs[] = array_merge($lastBreadcrumb, [
                            'position' => (int) data_get($lastBreadcrumb, 'position') + 1,
                            'name' => ucwords(strtolower($lexicon->name)),
                            'item' => $proxyUrl
                        ]);
                    }

                    $itemListElements = array_merge($itemListElements, $appendBreadcrumbs);
                }
                data_set($breadcrumbJson, 'itemListElement', $itemListElements);
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }

            return $html->save();

        } catch (\Exception $e) {
            return null;
        }
    }

    public function sitemapUrls($request, $shop)
    {
        try {
            $lexicons = $shop->lexicons->toArray();

            $xmlData = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"><!--  This is the sitemap linking to additional city pages  -->';

            foreach($lexicons as $lexicon) {

                $lexiconHandle = data_get($lexicon, 'handle');

                if (empty($lexiconHandle)) continue;

                $domain = $shop->public_domain ?: $shop->name;

                $url = "https://$domain/apps/pages/allergie-lexikon/$lexiconHandle";

                $xmlData .= "<url><loc>$url</loc></url>";
            }
            $xmlData .= '</urlset>';
            return response($xmlData, 200)->header('Content-type', 'application/xml');

        } catch (\Exception $e) {

            return response("", 500)->header('Content-type', 'application/xml');
        }
    }
}
