<?php

namespace App\Traits;

use App\Models\User;

trait PollenCommonTrait
{
    use HtmlDomTrait;

    public function preparePollenLinkingModule($shop, $html, $states = [], $language = null)
    {
        $domain = $shop->public_domain ?: $shop->name;
        $subDomain = getSubdomain($shop->name);
        $key = "pollen.$subDomain.url_prefix";

        $prefix = $language && $shop->name === User::KLARIFY_CA_STORE ? getLocale($key, data_get($language, 'code')) : '';

        $pollenLocationIndex = $this->getElement($html, 'pollen-location-index');
        if (empty($pollenLocationIndex)) return;

        $element = $this->getElement($pollenLocationIndex, 'pollen-location-blocks');
        if (empty($element)) return;

        $viewAllLocations = $element->lastChild();

        if (empty($viewAllLocations)) return;


        $viewAllLocations = $viewAllLocations->outertext;

        $linkingModuleBlock = $element->firstChild();
        if (empty($linkingModuleBlock)) return;

        $citiesBlock = $this->getElement($linkingModuleBlock, 'ul', 'name');

        if (empty($citiesBlock)) return;

        foreach ($citiesBlock->children() as $child) {
            $child->remove();
        }

        $linkingModuleBlockInnerHtml = '';

        $stateEl = $this->getElement($linkingModuleBlock, '.cities-block-title', 'find');
        if (empty($stateEl)) return;

        $states = $states->splice(0, 4);

        foreach ($states as $state) {

            $stateEl->innertext = $state->name ?: strtoupper($state->code);
            $citiesBlockHtml = '';

            $cities = empty($language) ? $state->pollenCities->splice(0, 3) : $state->pollenCities->where('language_id', $language->id)->splice(0, 3);
            foreach ($cities as $city) {

                $link = match ($shop->name) {
                    User::ALK_NO_STORE => "https://$domain/apps/pages/pollenvarsel/by/$city->handle",
                    User::ALK_NL_STORE => "https://$domain/apps/pages/hooikoorts-pollenradar/$city->handle",
                    User::ALK_DE_STORE => "https://$domain/apps/pages/pollenflug-vorhersage/stadt/$city->handle",
                    User::KLARIFY_US_STORE => "https://$domain/apps/pages/pollen-forecast/$city->handle",
                    User::KLARIFY_CA_STORE => "https://$domain/apps/pages/$prefix/$city->handle",
                    default => "https://$domain/apps/pages/forecast/cities/$city->handle",
                };

                $citiesBlockHtml .= '<li>
                                        <a href="'.$link.'">'.$city->name.'</a>
                                    </li>';
            }

            $citiesBlock->innertext = $citiesBlockHtml;

            $linkingModuleBlockInnerHtml .= $linkingModuleBlock->outertext;
        }

        foreach ($element->children() as $child) {
            $child->remove();
        }

        $element->innertext = $linkingModuleBlockInnerHtml . $viewAllLocations;

        return $pollenLocationIndex->parent() ? $pollenLocationIndex->parent()->outertext : $pollenLocationIndex->outertext;
    }
}
