<?php

namespace App\Traits;

use App\Helpers\ProxyHelper;
use App\Models\User;
use Illuminate\Support\Carbon;

trait ExtendedRegionPageTrait
{
    public function createRegionMedicalSchemaJson($shop, $region, $fadPageHandle, $clinics, $languageCode) {
        $domain = $shop->public_domain ?: $shop->name;
        $subDomain = getSubdomain($shop->name);
        $url = "https://$domain";
        $parentUrl = "https://$domain/pages/$fadPageHandle";
        $regionHandle = data_get($region, 'handle');
        $proxyUrl = "https://$domain/apps/pages/allergilaeger/$regionHandle";
        $regionName = data_get($region, 'name');
        $pageDescriptionKey = "fad_region.$subDomain.meta_description";
        $pageDescription = getLocale($pageDescriptionKey, $languageCode, ['regionName' => $regionName]);
        $parentPageTitleKey = "fad_region.$subDomain.parent_title";
        $parentPageTitle = getLocale($parentPageTitleKey, $languageCode);

        $data['@context'] = 'https://schema.org';
        $data['@type'] = 'MedicalWebPage';
        $data['name'] = "$regionName Webpage";
        $data['@id'] = $proxyUrl;
        $data['description'] = $pageDescription;
        $data['lastReviewed'] = Carbon::parse($region->updated_at)->format('d/m/Y');
        $data['url'] = $proxyUrl;
        $data['isPartOf'] = [
            "@type" => "WebSite",
            "name" => $parentPageTitle,
            "url" => $parentUrl,
            "@id" => $parentUrl
        ];
        $data['potentialAction'] = [
            "@type" => "SearchAction",
            "target" => [
                "@type" => "EntryPoint",
                "urlTemplate" => "$url/search?q=[search_term_string]"
            ],
            "query-input" => "required name=search_term_string"
        ];

        $breadcrumbItemList = $this->createBreadcrumbSchemaJson($shop, $region, $fadPageHandle, $languageCode);
        if (!empty($breadcrumbItemList)) {
            $data['breadcrumb'] = [
                "@type" => "BreadcrumbList",
                "itemListElement" => $breadcrumbItemList
            ];
        }

        $data['mainEntity'] = $this->createMainEntitySchemaJson($shop, $clinics, $languageCode);

        return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function createBreadcrumbSchemaJson($shop, $region, $pollenPageHandle, $languageCode) {
        $breadcrumbs = $this->getBreadcrumbsList($shop, $region, $pollenPageHandle, $languageCode);

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

    public function getBreadcrumbsList($shop, $region, $fadPageHandle, $languageCode) {
        $domain = ProxyHelper::getDomain($shop);
        $subDomain = getSubdomain($shop->name);
        $breadcrumbKeys = ['home', 'fad_page', 'fad_region_page'];
        $key = "fad_region.$subDomain.url_prefix";
        $urlPrefix = getLocale($key, $languageCode);

        $localeData = [
            'domain' => $domain,
            'region' => data_get($region, 'name'),
            'fadPageHandle' => $fadPageHandle,
            'fadRegionPagePrefix' => $urlPrefix,
            'fadRegionPageHandle' => data_get($region, 'handle')
        ];

        $breadcrumbs = [];
        foreach ($breadcrumbKeys as $breadcrumbKey) {
            $title = getLocale(
                "fad_region.$subDomain.breadcrumb.$breadcrumbKey.title",
                $languageCode,
                $localeData
            );
            $url = getLocale(
                "fad_region.$subDomain.breadcrumb.$breadcrumbKey.url",
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

    public function createMainEntitySchemaJson($shop, $clinics, $languageCode) {

        $mainEntity['@type'] = 'ItemList';
        $mainEntity['name'] = 'List of Physicians';
        $mainEntity['description'] = 'A list of physicians in a specific area';

        $itemListElements = [];
        foreach ($clinics as $index => $clinic) {

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

                if ($preparedSpecialtyArea === 'allergologie') {
                    $medicalSpecialties[] = $otolaryngologic;
                    $medicalSpecialties[] = $pulmonary;

                } elseif ($preparedSpecialtyArea === 'dermatologie') {
                    $medicalSpecialties[] = $dermatology;

                } elseif ($preparedSpecialtyArea === 'pädiatrie') {
                    $medicalSpecialties[] = $pediatric;
                }
            }
        }
        return array_values(array_unique($medicalSpecialties));
    }
}
