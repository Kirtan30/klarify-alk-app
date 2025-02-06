<?php

namespace App\Traits;

use App\Http\Resources\ClinicResource;
use App\Models\Clinic;

trait ExtendedClinicIndexPageTrait
{
    use ProxyTrait;
    public function getClinicIndexInitialEntities($request, $shop, $languageCode = null) {

        $country = $shop->country;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        $language = null;
        if ($languageCode) {
            $language = $shop->languages->where('code', $languageCode)->first();
        }

        if (empty($language)) {
            $language = $defaultLanguage;
        }

        $clinicIndexPageHandle = data_get($language, 'pivot.clinic_index_page');
        $clinicIndexPage = $this->getShopifyPage($shop, $clinicIndexPageHandle);

        if (empty($country) || empty($defaultLanguage) || empty($clinicIndexPage)) {
            return null;
        }

        return [
            'domain' => $shop->public_domain ?: $shop->name,
            'country' => $country,
            'defaultLanguage' => $defaultLanguage,
            'language' => $language,
            'clinicIndexPage' => $clinicIndexPage
        ];
    }
}
