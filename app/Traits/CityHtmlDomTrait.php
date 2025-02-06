<?php

namespace App\Traits;

trait CityHtmlDomTrait
{
    use HtmlDomTrait;

    public function setFadSearch($html, $value) {
        $element = $this->getElement($html, 'fad-search');
        if (!empty($element)) {
            $element->setAttribute('value', $value);
            $element->setAttribute('data-search', $value);
        }
    }

    public function setFadStaticContent($html, $city, $data) {
        $state = data_get($data, 'state') ?: '';
        $cityName = data_get($data, 'cityName') ?: '';

        if ($city->has_static_content) {
            $staticContentHtml = data_get($city, 'fadPageContent.content');

            $element = $this->getElement($html, 'find-a-doctor-content');
            if (!empty($element)) {

                $staticContentVariables = data_get($city, 'fadPageContent.variables') ?: [];

                foreach ($staticContentVariables as $staticContentVariable) {
                    $variableValue = data_get($city, "variables.$staticContentVariable");
                    if (empty($variableValue) && in_array($staticContentVariable, ['city', 'state'])) {
                        $variableValue = $staticContentVariable === 'city' ? $cityName : $state;
                    }
                    $staticContentHtml = str_replace("[[$staticContentVariable]]", $variableValue, $staticContentHtml);
                }

                $element->outertext = $staticContentHtml;
            }
        }
    }

    public function setCityLanguageLinksJsonScriptEl($html, $languageUrls) {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'languageLinksJson');
        $element->innertext = 'window.languageLinks = ' . json_encode($languageUrls);
        if (!empty($body)) $body->appendChild($element);
    }

    public function setCityMedicalSchemaJsonScriptEl($html, $medicalSchema = []) {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('type', 'application/ld+json');
        $element->setAttribute('id', 'medicalSchemaJson');
        $element->innertext = json_encode($medicalSchema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if (!empty($body)) $body->appendChild($element);
    }
}
