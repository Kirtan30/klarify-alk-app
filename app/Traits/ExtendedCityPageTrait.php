<?php

namespace App\Traits;

use App\Helpers\ProxyHelper;
use App\Models\User;
use Illuminate\Support\Carbon;

trait ExtendedCityPageTrait
{
    use ProxyTrait;

    public function getInitialEntities($shop, $cityHandle, $languageCode = null, $onlyEnabledCities = false) {

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $language = null;
        if ($languageCode) {
            $language = $shop->languages->where('code', $languageCode)->first();
        }

        if (empty($language)) {
            $language = $defaultLanguage;
        }

        $cityPageHandle = data_get($language, 'pivot.fad_page');
        $cityPage = $this->getShopifyPage($shop, $cityPageHandle);

        if (!$cityPage) {
            return null;
        }

        $country = $shop->country;
        if (empty($country)) {
            return null;
        }

        $city = $shop->fadCities()->with(['fadPageContent', 'parentCity.childCities'])
            ->where('language_id', $language->id)
            ->where('handle', $cityHandle);

        if ($onlyEnabledCities) {
            $city = $city->where('enabled', true);
        }

        $city = $city->first();

        if (empty($city)) {
            return null;
        }

        return [
            'domain' => $shop->public_domain ?: $shop->name,
            'country' => $country,
            'defaultLanguage' => $defaultLanguage,
            'language' => $language,
            'city' => $city,
            'cityPage' => $cityPage,
            'cityPageHandle' => $cityPageHandle
        ];
    }

    public function createMedicalSchemaJson($shop, $city, $fadPageHandle, $clinics, $languageCode) {

        $domain = $shop->public_domain ?: $shop->name;
        $subDomain = getSubdomain($shop->name);
        $url = "https://$domain";
        $cityHandle = $city->handle;
        $proxyUrl = ProxyHelper::getUrl($shop, $cityHandle, null, ProxyHelper::TYPE_FAD, $languageCode);

        $localeData = [
            'city' => $city->name
        ];
        $schemaName = getLocale(
            "fad.$subDomain.schema_name",
            $languageCode,
            $localeData
        );
        $schemaDescription = getLocale(
            "fad.$subDomain.meta_description",
            $languageCode,
            $localeData
        );

        $data['@context'] = 'https://schema.org';
        $data['@type'] = 'MedicalWebPage';
        $data['name'] = $schemaName ?: "$city->name Webpage";
        $data['@id'] = $proxyUrl;
        if ($shop->name === User::KLARIFY_SK_STORE) {
            $data['lastReviewed'] = Carbon::parse($city->updated_at)->format('d/m/Y');
        }
        $data['description'] = $schemaDescription ?: 'Description of page content';
        $data['url'] = $proxyUrl;

        if (in_array($shop->name, [User::ALK_DE_STORE, User::ALK_NL_STORE])) {
            $data['isPartOf'] = [
                "@type" => "WebSite",
                "name" => str($subDomain)->title() . ' Website',
                "url" => $url,
                "@id" => $url
            ];
        }

        $data['potentialAction'] = [
            "@type" => "SearchAction",
            "target" => [
                "@type" => "EntryPoint",
                "urlTemplate" => "$url/search?q=[search_term_string]"
            ],
            "query-input" => "required name=search_term_string"
        ];

        if (in_array($shop->name, [User::ALK_DE_STORE, User::ALK_NL_STORE])) {
            $breadcrumbItemList = $this->createBreadcrumbSchemaJson($shop, $city, $fadPageHandle, $languageCode);
            if (!empty($breadcrumbItemList)) {
                $data['breadcrumb'] = [
                    "@type" => "BreadcrumbList",
                    "itemListElement" => $breadcrumbItemList
                ];
            }
        }

        $data['mainEntity'] = $this->createMainEntitySchemaJson($shop, $city, $clinics, $languageCode);

        return $data;
    }

    public function createBreadcrumbSchemaJson($shop, $city, $fadPageHandle, $languageCode) {
        $breadcrumbs = $this->getBreadcrumbsList($shop, $city, $fadPageHandle, $languageCode);

        $breadcrumbItemList = [];
        foreach ($breadcrumbs as $position => $breadcrumb) {
            $breadcrumbItemList[] = [
                "@type" => "ListItem",
                "position" => $position + 1,
                "item" => [
                    "@id" => data_get($breadcrumb, 'url'),
                    "name" => data_get($breadcrumb, 'title'),
                ]
            ];
        }

        return $breadcrumbItemList;
    }

    public function createMainEntitySchemaJson($shop, $city, $clinics, $languageCode) {

        $mainEntity['@type'] = 'ItemList';
        if (in_array($shop->name, [User::KLARIFY_SK_STORE, User::KLARIFY_CZ_STORE, User::DEMO_STORE])) {
            $domain = $shop->public_domain ?: $shop->name;
            $subDomain = getSubdomain($shop->name);

            $localeData = [
                'city' => $city->name
            ];
            $schemaDescription = getLocale(
                "fad.$subDomain.meta_description",
                $languageCode,
                $localeData
            );

            $mainEntity['name'] = "$city->name Webpage";
            $mainEntity['description'] = $schemaDescription;
        } else {
            $mainEntity['name'] = 'List of Physicians';
            $mainEntity['description'] = 'A list of physicians in a specific area';
        }

        $itemListElements = [];
        foreach ($clinics as $index => $clinic) {

            // For now only for ALK_DE_STORE
            $clinicHandle = ProxyHelper::getClinicHandle($clinic);
            if (empty($clinicHandle)) continue;

            $clinicPageUrl = ProxyHelper::getUrl(
                $shop,
                $clinicHandle,
                null,
                ProxyHelper::TYPE_CLINIC,
                $languageCode
            );

            $itemElement['@type'] = 'ListItem';
            $itemElement['position'] = $index + 1;
            $itemElement['url'] = $clinicPageUrl;
            $itemElement['item'] = [];

            $addressSchema = [];
            $addressSchema['@type'] = 'PostalAddress';
            if (data_get($clinic, 'street')) $addressSchema['streetAddress'] = data_get($clinic, 'street');
            if (data_get($clinic, 'city')) $addressSchema['addressLocality'] = data_get($clinic, 'city');
            if (data_get($clinic, 'zipcode')) $addressSchema['postalCode'] = data_get($clinic, 'zipcode');
            $addressSchema['addressCountry'] = strtoupper(data_get($shop, 'country.code'));

            $openingHoursSchema = [];
            foreach (data_get($clinic, 'timings') ?: [] as $timing) {
                $openingHours = data_get($timing, 'opening_hours') ?: [];
                foreach ($openingHours as $openingHour) {
                    $timingSchema['@type'] = 'OpeningHoursSpecification';
                    $timingSchema['dayOfWeek'] = data_get($timing, 'name');
                    $timingSchema['opens'] = data_get($openingHour, 'opening_time');
                    $timingSchema['closes'] = data_get($openingHour, 'closing_time');

                    $openingHoursSchema[] = $timingSchema;
                }
            }

            $medicalSpecialtySchema = [];
            $medicalSpecialties = $this->getMedicalSpecialistSchema($shop, $clinic);
            foreach ($medicalSpecialties as $medicalSpecialty) {
                $medicalSpecialtySchema[] = [
                    '@type' => 'MedicalSpecialty',
                    'name' => $medicalSpecialty
                ];
            }

            // Copied from ClinicPageTrait dePage
            $availableServices = [
                'is_allergy_diagnostic' => data_get($clinic, 'is_allergy_diagnostic'),
                'is_insect_allergy_diagnostic' => data_get($clinic, 'is_insect_allergy_diagnostic'),
                'is_subcutaneous_immunotherapy' => data_get($clinic, 'is_subcutaneous_immunotherapy'),
                'is_sublingual_immunotherapy' => data_get($clinic, 'is_sublingual_immunotherapy'),
                'is_venom_immunotherapy' => data_get($clinic, 'is_venom_immunotherapy')
            ];
            $availableServices = array_filter($availableServices);

            $preparedAvailableServices = [];
            foreach ($availableServices as $availableService => $value) {
                $preparedAvailableServices[] = match($availableService) {
                    'is_allergy_diagnostic' => 'Allergiediagnostik inhalative Allergene',
                    'is_insect_allergy_diagnostic' => 'Allergiediagnostik Insekten',
                    'is_subcutaneous_immunotherapy', 'is_sublingual_immunotherapy' => 'Spezifische Immuntherapie',
                    'is_venom_immunotherapy' => 'Spezifische Immuntherapie Insektengift',
                    default => null
                };
            }

            $preparedAvailableServices = array_values(array_unique(array_filter($preparedAvailableServices)));

            $availableServicesSchema = [];
            foreach ($preparedAvailableServices as $preparedAvailableService) {
                $availableServiceSchema['@type'] = 'MedicalTherapy';
                $availableServiceSchema['name'] = $preparedAvailableService;
                $availableServicesSchema[] = $availableServiceSchema;
            }

            if ($shop->name === User::KLARIFY_SK_STORE) {
                $medicalTests = ['Alergologické krvné test', 'Kožný prick test'];
                foreach ($medicalTests as $medicalTest) {
                    $availableServiceSchema['@type'] = 'MedicalTest';
                    $availableServiceSchema['name'] = $medicalTest;
                    $availableServicesSchema[] = $availableServiceSchema;
                }
            }

            $itemElement['item']['@type'] = 'Physician';
            $itemElement['item']['name'] = data_get($clinic, 'doctor_name') ?: data_get($clinic, 'clinic_name');
            $itemElement['item']['url'] = $clinicPageUrl;
            $itemElement['item']['@id'] = $clinicPageUrl;
            if (data_get($clinic, 'phone')) $itemElement['item']['telephone'] = data_get($clinic, 'phone');
            if (data_get($clinic, 'email')) $itemElement['item']['email'] = data_get($clinic, 'email');
            $itemElement['item']['address'] = $addressSchema;
            if ($openingHoursSchema) $itemElement['item']['openingHoursSpecification'] = $openingHoursSchema;
            if ($medicalSpecialtySchema) $itemElement['item']['medicalSpecialty'] = $medicalSpecialtySchema;
            if ($availableServicesSchema) $itemElement['item']['availableService'] = $availableServicesSchema;

            $itemListElements[] = $itemElement;
        }

        $mainEntity['itemListElement'] = $itemListElements;

        return $mainEntity;
    }

    public function getMedicalSpecialistSchema($shop, $clinic) {
        $otolaryngologic = 'https://schema.org/Otolaryngologic';
        $pulmonary = 'https://schema.org/Pulmonary';
        $dermatology = 'https://schema.org/Dermatology';
        $pediatric  = 'https://schema.org/Pediatric';
        $emergency = 'https://schema.org/Emergency';

        $medicalSpecialties = [];
        if ($shop->name === User::ALK_NL_STORE) {
            $specialties = trim(strtolower(data_get($clinic, 'other')));
            if (str($specialties)->contains('allergologie')) {

                $medicalSpecialties[] = $otolaryngologic;
                $medicalSpecialties[] = $pulmonary;
            }

            if (str($specialties)->contains('longziekten') || str($specialties)->contains('long')) {

                $medicalSpecialties[] = $pulmonary;
            }

            if (str($specialties)->contains('dermatologie')) {

                $medicalSpecialties[] = $dermatology;
            }

            if (str($specialties)->contains('kinderallergologie') || str($specialties)->contains('kinder')) {

                $medicalSpecialties[] = $pediatric;
            }

            if (str($specialties)->contains('kno')) {

                $medicalSpecialties[] = $otolaryngologic;
            }
        } else {
            foreach (data_get($clinic, 'specialist_areas') ?: [] as $specialtyArea) {
                $preparedSpecialtyArea = trim(strtolower($specialtyArea));

                if ($preparedSpecialtyArea === 'allergologie' || $preparedSpecialtyArea === 'alergológ') {
                    $medicalSpecialties[] = $otolaryngologic;
                    $medicalSpecialties[] = $pulmonary;

                } elseif ($preparedSpecialtyArea === 'dermatologie') {
                    $medicalSpecialties[] = $dermatology;

                } elseif ($preparedSpecialtyArea === 'pädiatrie') {
                    $medicalSpecialties[] = $pediatric;

                } elseif ($preparedSpecialtyArea === 'Špecialista na bodnutie hmyzom') {
                    $medicalSpecialties[] = $emergency;
                }
            }
        }
        return array_values(array_unique($medicalSpecialties));
    }

    public function getBreadcrumbsList($shop, $city, $fadPageHandle, $languageCode) {
        $domain = ProxyHelper::getDomain($shop);
        $subDomain = getSubdomain($shop->name);
        $breadcrumbKeys = ['home', 'fad_page', 'fad_city_page'];

        $localeData = [
            'domain' => $domain,
            'city' => data_get($city, 'name'),
            'fadPageHandle' => $fadPageHandle,
            'fadCityPagePrefix' => ProxyHelper::getUrlPrefix($shop, ProxyHelper::TYPE_FAD, $languageCode),
            'fadCityPageHandle' => data_get($city, 'handle')
        ];

        $breadcrumbs = [];
        foreach ($breadcrumbKeys as $breadcrumbKey) {
            $title = getLocale(
                "fad.$subDomain.breadcrumb.$breadcrumbKey.title",
                $languageCode,
                $localeData
            );
            $url = getLocale(
                "fad.$subDomain.breadcrumb.$breadcrumbKey.url",
                $languageCode,
                $localeData
            );

            if (!empty($title) && !empty($url)) {
                $breadcrumbs[] = [
                    'title' => $title,
                    'url' => $url
                ];
            }
        }

        return $breadcrumbs;
    }
}
