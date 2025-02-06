<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Carbon;
use App\Helpers\ProxyHelper;

trait ClinicIndexPageTrait
{
    use HtmlDomTrait, ProxyTrait, ExtendedClinicIndexPageTrait, ClinicIndexHtmlDomTrait;

    public function commonPage($request, $shop, $languageCode = null)
    {
        $initialEntities = $this->getClinicIndexInitialEntities($request, $shop, $languageCode);
        if (empty($initialEntities)) return null;

        $domain = data_get($initialEntities, 'domain');
        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $clinicIndexPage = data_get($initialEntities, 'clinicIndexPage');
        $languageCode = strtolower(data_get($language, 'code'));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicIndexPage);

            if (!in_array($shop->name, [User::KLARIFY_CZ_STORE, User::KLARIFY_SK_STORE, User::ALK_NL_STORE])) {
                $this->removeNoIndexTag($html);
            }

            $data = $this->manageClinicIndexColumns($html);
            if (empty($data)) return $html->save();

            $clinicIndexColumns = data_get($data, 'clinicIndexColumns');
            $cardElement = data_get($data, 'cardElement');

            $clinics = ProxyHelper::getClinics($shop);
            $cities = ProxyHelper::getClinicsCities($clinics);

            $cardElement = $cardElement->save();
            $cardElement = str_get_html($cardElement);

            $cardBlockBody = $this->getCardBlockBody($cardElement);
            if (empty($cardBlockBody)) return $html->save();

            $this->removeCardBlockBodyChildren($cardBlockBody);
            $clinicUrlPrefix = ProxyHelper::getUrlPrefix($shop, ProxyHelper::TYPE_CLINIC, $languageCode, data_get($defaultLanguage, 'code'));
            $cityClinicHtml = $this->getCityClinicHtml($shop, $cardElement, $cities, $clinicUrlPrefix);

            $this->removeClinicIndexChildren($clinicIndexColumns);
            $clinicIndexColumns->innertext = $cityClinicHtml;

            if (in_array($shop->name, [User::KLARIFY_CH_STORE])) {
                $languageUrls = [
                    'de' => 'https://ch.klarify.me/apps/pages/ubersicht-facharztpraxen',
                    'fr' => 'https://ch.klarify.me/apps/pages/apercu-cabinets-medicaux-specialises',
                    'it' => 'https://ch.klarify.me/apps/pages/panoramica-studi-medici-specialistici'
                ];
                $this->setClinicIndexLanguageLinksJsonScriptEl($html, $languageUrls);
            }

            $this->setShopifySectionFooter($html);

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }

    public function noPage($request, $shop)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $clinicIndexPageHandle = data_get($defaultLanguage, 'pivot.clinic_index_page');

        $clinicIndexPage = $this->getShopifyPage($shop, $clinicIndexPageHandle);

        if (!$clinicIndexPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicIndexPage);

            /*$element = $this->getElement($html, 'meta[name=robots]', 'find');
            if (!empty($element)) $element->remove();*/

            $element = $this->getElement($html, 'clinic-index-columns');
            if (empty($element)) {
                return $html->save();
            }

            $cardElement = $element->firstChild();
            if (empty($cardElement)) {
                return $html->save();
            }

            $clinics = $shop->clinics()->whereNotNull('region')->orderBy('region')->get();
            $regions = [];
            foreach ($clinics as $clinic) {
                $region = strtolower(data_get($clinic, 'region'));
                if (!empty($region)) {
                    $regions[$region][] = $clinic;
                }
            }

            $fadRegions = $shop->fadRegions()
                ->where('language_id', $defaultLanguage->id)
                ->selectRaw('*, LOWER(name) as region_name')->get()->keyBy('region_name');

            $cardElement = $cardElement->save();
            $cardElement = str_get_html($cardElement);
            $regionClinicHtml = '';

            $cardBlockBody = $this->getElement($cardElement, '.cities-block-body', 'find');
            if (empty($cardBlockBody)) {
                return $html->save();
            }

            foreach ($cardBlockBody->children() as $child) {
                $child->remove();
            }

            foreach ($fadRegions as $regionName => $fadRegion) {

                $this->setInnerText($cardElement, '.cities-block-title a', (string) str($regionName)->title(), 'find');
                $this->setAttributes($cardElement, '.cities-block-title a', [
                    'href' => "https://$domain/apps/pages/allergi-klinikker/fylke/$fadRegion->handle"
                ], 'find');

                $clinicsHtml = '';
                $clinics = data_get($regions, $regionName) ?: [];

                foreach ($clinics as $clinic) {
                    $clinicUrl = "https://$domain/apps/pages/klinikker/" . ($clinic->clinic_handle ?: $clinic->doctor_handle);

                    $isDoctor = data_get($clinic, 'is_doctor');
                    $doctorName = data_get($clinic, 'doctor_name');
                    $clinicName = data_get($clinic, 'clinic_name');
                    $linkText = $clinicName ?: $doctorName;

                    $clinicsHtml .= '<li><a href="'. $clinicUrl .'">'. $linkText .'</a></li>';
                }

                $this->setInnerText($cardElement, '.cities-block-body', "<ul>$clinicsHtml</ul>", 'find');

                $regionClinicHtml .= $cardElement->outertext;
            }

            foreach ($element->children() as $child) {
                $child->remove();
            }

            $element->innertext = $regionClinicHtml;

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

    public function dkPage($request, $shop, $languageCode = null)
    {
        $initialEntities = $this->getClinicIndexInitialEntities($request, $shop, $languageCode);
        if (empty($initialEntities)) return null;

        $domain = data_get($initialEntities, 'domain');
        $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
        $language = data_get($initialEntities, 'language');
        $clinicIndexPage = data_get($initialEntities, 'clinicIndexPage');
        $languageCode = strtolower(data_get($language, 'code'));

        try {
            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicIndexPage);

            $data = $this->manageClinicIndexColumns($html);
            if (empty($data)) return $html->save();

            $this->removeNoIndexTag($html);

            $clinicIndexColumns = data_get($data, 'clinicIndexColumns');
            $cardElement = data_get($data, 'cardElement');

            $clinics = ProxyHelper::getClinics($shop, true);
            $regions = ProxyHelper::getClinicsRegions($clinics);

            $cardElement = $cardElement->save();
            $cardElement = str_get_html($cardElement);

            $cardBlockBody = $this->getRegionCardBlockBody($cardElement);
            if (empty($cardBlockBody)) return $html->save();

            $this->removeCardBlockBodyChildren($cardBlockBody);
            $clinicUrlPrefix = ProxyHelper::getUrlPrefix($shop, ProxyHelper::TYPE_CLINIC, $languageCode, data_get($defaultLanguage, 'code'));
            $regionClinicHtml = $this->getRegionClinicHtml($shop, $cardElement, $regions, $clinicUrlPrefix);

            $this->removeClinicIndexChildren($clinicIndexColumns);
            $clinicIndexColumns->innertext = $regionClinicHtml;

            $this->setShopifySectionFooter($html);

            return  $html->save();

        } catch (\Exception $e) {

            return null;
        }
    }
}
