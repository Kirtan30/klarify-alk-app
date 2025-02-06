<?php

namespace App\Traits;

Use App\Helpers\ProxyHelper;

trait ClinicIndexHtmlDomTrait
{
    use HtmlDomTrait;

    public function manageClinicIndexColumns($html) {
        $element = $this->getElement($html, 'clinic-index-columns');
        if (empty($element)) {
            return null;
        }

        $cardElement = $element->firstChild();
        if (empty($cardElement)) {
            return null;
        }

        return [
            'clinicIndexColumns' => $element,
            'cardElement' => $cardElement
        ];
    }

    public function getRegionCardBlockBody($cardElement) {
        return $this->getElement($cardElement, '.regions-block-body', 'find');
    }

    public function getCardBlockBody($cardElement) {
        return $this->getElement($cardElement, '.cities-block-body', 'find');
    }

    public function removeClinicIndexChildren($data) {
        foreach ($data->children() as $child) {
            $child->remove();
        }
    }

    public function removeCardBlockBodyChildren($cardBlockBody) {
        foreach ($cardBlockBody->children() as $child) {
            $child->remove();
        }
    }

    public function setShopifySectionFooter($html) {
        $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
        if (!empty($element)) {
            foreach ($element->children() as $child) {
                if (empty($child->innertext)) {
                    $child->innertext = ' ';
                }
            }
        }
    }

    public function getRegionClinicHtml($shop, $cardElement, $regions, $urlPrefix) {
        $regionClinicHtml = '';
        foreach ($regions as $region => $clinics) {
            $this->setRegionsBlockTitle($cardElement, $region);

            $clinicsHtml = '';
            foreach ($clinics as $clinic) {
                $isDoctor = data_get($clinic, 'is_doctor');
                $clinicHandle = $isDoctor ? data_get($clinic, 'doctor_handle') : data_get($clinic, 'clinic_handle');
                $clinicUrl = ProxyHelper::getUrl($shop, $clinicHandle, $urlPrefix);

                $doctorName = data_get($clinic, 'doctor_name');
                $clinicName = data_get($clinic, 'clinic_name');
                $linkText = $isDoctor ? $doctorName : $clinicName;
                $linkText = empty($linkText) ? ($doctorName ?: $clinicName) : $linkText;

                $clinicsHtml .= '<li><a href="'. $clinicUrl .'">'. $linkText .'</a></li>';
            }

            $this->setRegionsBlockBody($cardElement, $clinicsHtml);

            $regionClinicHtml .= $cardElement->outertext;
        }

        return $regionClinicHtml;
    }

    public function getCityClinicHtml($shop, $cardElement, $cities, $urlPrefix) {
        $cityClinicHtml = '';
        foreach ($cities as $city => $clinics) {
            $this->setCitiesBlockTitle($cardElement, $city);

            $clinicsHtml = '';
            foreach ($clinics as $clinic) {
                $isDoctor = data_get($clinic, 'is_doctor');
                $clinicHandle = $isDoctor ? data_get($clinic, 'doctor_handle') : data_get($clinic, 'clinic_handle');
                $clinicUrl = ProxyHelper::getUrl($shop, $clinicHandle, $urlPrefix);

                $doctorName = data_get($clinic, 'doctor_name');
                $clinicName = data_get($clinic, 'clinic_name');
                $linkText = trim("$doctorName $clinicName") ?: ($isDoctor ? $doctorName : $clinicName);

                $clinicsHtml .= '<li><a href="'. $clinicUrl .'">'. $linkText .'</a></li>';
            }

            $this->setCitiesBlockBody($cardElement, $clinicsHtml);

            $cityClinicHtml .= $cardElement->outertext;
        }

        return $cityClinicHtml;
    }

    public function setRegionsBlockTitle($cardElement, $region) {
        $this->setInnerText($cardElement, '.regions-block-title', (string) str($region)->title(), 'find');
    }

    public function setCitiesBlockTitle($cardElement, $city) {
        $this->setInnerText($cardElement, '.cities-block-title', (string) str($city)->title(), 'find');
    }

    public function setRegionsBlockBody($cardElement, $data) {
        $this->setInnerText($cardElement, '.regions-block-body', "<ul>$data</ul>", 'find');
    }

    public function setCitiesBlockBody($cardElement, $data) {
        $this->setInnerText($cardElement, '.cities-block-body', "<ul>$data</ul>", 'find');
    }

    public function setClinicIndexLanguageLinksJsonScriptEl($html, $languageUrls) {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'languageLinksJson');
        $element->innertext = 'window.languageLinks = ' . json_encode($languageUrls);
        if (!empty($body)) $body->appendChild($element);
    }
}
