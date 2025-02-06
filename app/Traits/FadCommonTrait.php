<?php

namespace App\Traits;

use App\Models\User;

trait FadCommonTrait
{
    use HtmlDomTrait;

    public function prepareLinkingModule($shop, $html, $states = [], $language = null)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $fadLocationIndex = $this->getElement($html, 'fad-location-index');
        if (empty($fadLocationIndex)) return;

        $element = $this->getElement($fadLocationIndex, 'fad-location-blocks');
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

            $cities = empty($language) ? $state->fadCities->splice(0, 3) : $state->fadCities->where('language_id', $language->id)->splice(0, 3);
            foreach ($cities as $city) {

                $link = match ($shop->name) {
                    User::ALK_NO_STORE => "https://$domain/apps/pages/klinikker/by/$city->handle",
                    User::ALK_NL_STORE => "https://$domain/apps/pages/allergiespecialisten-testen/$city->handle",
                    User::ALK_DE_STORE => "https://$domain/apps/pages/allergologensuche/stadt/$city->handle",
                    User::KLARIFY_US_STORE => "https://$domain/apps/pages/find-a-doctor/$city->handle",
                    User::KLARIFY_CA_STORE => "https://$domain/apps/pages/find-an-allergist/$city->handle",
                    User::ALK_ODACTRA_STORE => "https://$domain/apps/pages/find-an-allergy-specialist/$city->handle",
                    User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE => "https://$domain/apps/pages/find-doctor/$city->handle",
                    default => "https://$domain/apps/pages/clinics/cities/$city->handle",
                };

                $citiesBlockHtml .= '<li>
                                        <a href="'.$link.'">'.(in_array($shop->name, [User::KLARIFY_US_STORE, User::KLARIFY_CA_STORE, User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE]) ? str(strtolower($city->name))->ucfirst() : $city->name).'</a>
                                    </li>';
            }

            $citiesBlock->innertext = $citiesBlockHtml;

            $linkingModuleBlockInnerHtml .= $linkingModuleBlock->outertext;
        }

        foreach ($element->children() as $child) {
            $child->remove();
        }

        $element->innertext = $linkingModuleBlockInnerHtml . $viewAllLocations;

        return $fadLocationIndex->parent() ? $fadLocationIndex->parent()->outertext : $fadLocationIndex->outertext;
    }
}
