<?php

namespace App\Traits;

trait PollenHtmlDomTrait
{
    use HtmlDomTrait;

    public function setPollenBannerTitle($html, $title) : void {
        $this->setInnerText($html, 'pollen-banner-title', $title);
    }

    public function setPollenBannerDescription($html, $description, $removeChildren = false) : void {
        $selector = 'pollen-banner-description';
        if ($removeChildren) {
            $element = $this->getElement($html, $selector);
            if (!empty($element)) {
                foreach ($element->children() ?: [] as $child) {
                    $child->remove();
                }
            }
        }
        $this->setInnerText($html, $selector, $description);
    }

    public function setPollenSearch($html, $value) : void {
        $element = $this->getElement($html, 'pollen-search');
        if (!empty($element)) {
            $element->setAttribute('value', $value);
            $element->setAttribute('data-value', $value);
        }
    }

    public function setPollenSearchLatLong($html, $city) : void {
        $element = $this->getElement($html, 'pollen-search');
        if (!empty($element)) {
            $element->setAttribute('data-latitude', data_get($city, 'latitude'));
            $element->setAttribute('data-longitude', data_get($city, 'longitude'));
        }
    }

    public function setPollenLatLong($html, $city, $value) : void {
        $selectors = ['pollen_page', 'pollen-main'];
        foreach ($selectors as $selector) {
            $element = $this->getElement($html, $selector);
            if (!empty($element)) {
                $element->setAttribute('value', $value);
                $element->setAttribute('data-value', $value);
                $element->setAttribute('data-latitude', data_get($city, 'latitude'));
                $element->setAttribute('data-longitude', data_get($city, 'longitude'));
            }
        }
    }

    public function setPollenStaticContent($html, $city, $data) {
        $cityName = data_get($data, 'cityName') ?: '';

        if ($city->has_static_content) {

            $staticContentHtml = data_get($city, 'pollenPageContent.content');
            $element = $this->getElement($html, 'pollen_page_content');
            if (!empty($element)) {
                $staticContentVariables = data_get($city, 'pollenPageContent.variables') ?: [];

                foreach ($staticContentVariables as $staticContentVariable) {
                    $variableValue = data_get($city, "variables.$staticContentVariable");
                    if (empty($variableValue) && in_array($staticContentVariable, ['city'])) {
                        $variableValue = $cityName;
                    }
                    $staticContentHtml = str_replace("[[$staticContentVariable]]", $variableValue, $staticContentHtml);
                }

                $element->outertext = $staticContentHtml;
            }
        }
    }

    public function setPollenCityMedicalStructureJsonScriptEl($html, $jsonData = '') {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'MedicalPollenCityJSONStructureData');
        $element->setAttribute('type', 'application/ld+json');
        $element->innertext = $jsonData;
        if (!empty($body)) $body->appendChild($element);
    }

    public function setPollenRegionMedicalStructureJsonScriptEl($html, $jsonData = '') {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'MedicalPollenRegionJSONStructureData');
        $element->setAttribute('type', 'application/ld+json');
        $element->innertext = $jsonData;
        if (!empty($body)) $body->appendChild($element);
    }

    public function setPollenLanguageLinksJsonScriptEl($html, $languageUrls) {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'languageLinksJson');
        $element->innertext = 'window.languageLinks = ' . json_encode($languageUrls);
        if (!empty($body)) $body->appendChild($element);
    }
}
