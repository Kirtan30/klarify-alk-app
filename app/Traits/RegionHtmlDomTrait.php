<?php

namespace App\Traits;

trait RegionHtmlDomTrait
{
    public function setRegionMedicalStructureJsonScriptEl($html, $jsonData = '') {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'MedicalRegionJSONStructureData');
        $element->setAttribute('type', 'application/ld+json');
        $element->innertext = $jsonData;
        if (!empty($body)) $body->appendChild($element);
    }
}
