<?php


namespace App\Traits;


trait HtmlDomTrait
{
    function getElement($html, $selector, $type = 'id') {
        if ($type === 'id') {
            $element = $html->getElementById($selector);
        }
        else if ($type === 'name') {
            $element = $html->getElementByTagName($selector);
        }
        else {
            $element = $html->find($selector);
            $element = data_get($element, 0);
        }

        return $element;
    }

    function setAttributes($html, $selector, $data = [], $type = 'id') {

        $element = $this->getElement($html, $selector, $type);

        if (empty($element)) return;

        foreach ($data as $key => $value) {
            $element->setAttribute($key, $value);
        }
    }

    function setInnerText($html, $selector, $data = "", $type = 'id', $removeIfEmpty = false) {

        $element = $this->getElement($html, $selector, $type);

        if (empty($element)) {
            return;
        }

        if ($removeIfEmpty && empty($data)) {
            $element->remove();
        } else {
            $element->innertext = $data;
        }
    }

    function setOuterText($html, $selector, $data = "", $type = 'id', $removeIfEmpty = false) {

        $element = $this->getElement($html, $selector, $type);

        if (empty($element)) {
            return;
        }

        if ($removeIfEmpty && empty($data)) {
            $element->remove();
        } else {
            $element->outertext = $data;
        }
    }

    function removeElement($html, $selector, $type = 'id', $parent = false) {

        $element = $this->getElement($html, $selector, $type);

        if (!empty($element)) {
            if (empty($parent)) $element->remove();
            else $element->parent()?->remove();
        }
    }

    public function setTitleTag($html, $title)
    {
        $this->setInnerText($html, 'title', $title, 'name');
    }

    public function setCanonicalLinkTag($html, $proxyUrl)
    {
        $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');
    }

    public function setMetaOgUrlTag($html, $proxyUrl)
    {
        $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');
    }

    public function setMetaTitleTag($html, $title)
    {
        $this->setAttributes($html, 'meta[property=og:title]', ['content' => $title], 'find');
    }

    public function setMetaTwitterTitleTag($html, $title) {
        $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $title], 'find');
    }

    public function setAllMetaTitleTags($html, $title)
    {
        $this->setMetaTitleTag($html, $title);
        $this->setMetaTwitterTitleTag($html, $title);
    }

    public function setMetaOgTypeTag($html, $content = 'Clinic')
    {
        $this->setAttributes($html, 'meta[property=og:type]', ['content' => $content], 'find');
    }

    public function setMetaDescriptionTag($html, $value) {
        $element = $this->getElement($html, 'meta[name=description]', 'find');
        if (empty($element)) {
            $headEl = $this->getElement($html, 'head', 'name');
            $meta = $html->createElement('meta');
            $meta->setAttribute('name', 'description');
            $meta->setAttribute('content', $value);
            $headEl->appendChild($meta);
        } else {
            $element->setAttribute('content', $value);
        }
    }

    public function setMetaOgDescriptionTag($html, $value) {
        $this->setAttributes($html, 'meta[property=og:description]', ['content' => $value], 'find');
    }

    public function setMetaTwitterDescriptionTag($html, $value) {
        $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $value], 'find');
    }

    public function setAllMetaDescriptionTags($html, $metaDescription) {
        $this->setMetaDescriptionTag($html, $metaDescription);
        $this->setMetaOgDescriptionTag($html, $metaDescription);
        $this->setMetaTwitterDescriptionTag($html, $metaDescription);
    }

    public function setNoIndexTag($html) {
        $headEl = $this->getElement($html, 'head', 'name');
        if (!empty($headEl)) {
            $meta = $html->createElement('meta');
            $meta->setAttribute('name', 'robots');
            $meta->setAttribute('content', 'noindex');
            $headEl->appendChild($meta);
        }
    }

    public function removeNoIndexTag($html) {
        $elements = $html->find('meta[name=robots][content^=noindex]');
        foreach($elements ?: [] as $el) {
            if (!empty($el)) $el->remove();
        }
    }

    public function removeHrefLangTags($html) {
        $hrefLangTags = $html->find('link[hreflang]');
        foreach ($hrefLangTags ?: [] as $hrefLangTag) {
            $hrefLangTag->remove();
        }
    }

    public function removeMedicalSchemaTag($html) {
        $medicalSchemaTag = $this->getElement($html, 'fadMedicalSchemaJson');
        if ($medicalSchemaTag) {
            $medicalSchemaTag->remove();
        }
    }
}
