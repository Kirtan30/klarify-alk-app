<?php

namespace App\Traits;

use App\Helpers\ProxyHelper;
use App\Http\Resources\ClinicResource;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Carbon;

trait ExtendedClinicPageTrait
{
    use ProxyTrait;

    public function getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode = null) {

        $country = $shop->country;

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        $language = null;
        if ($languageCode) {
            $language = $shop->languages->where('code', $languageCode)->first();
        }

        if (empty($language)) {
            $language = $defaultLanguage;
        }

        $clinic = $shop->clinics()
            ->with(Clinic::RELATIONS)
            ->where(function ($query) use ($clinicHandle) {
                $query->where('doctor_handle', $clinicHandle)
                    ->orWhere('clinic_handle', $clinicHandle);
            })
            ->first();

        $clinicPageHandle = data_get($language, 'pivot.clinic_page');
        $clinicPage = $this->getShopifyPage($shop, $clinicPageHandle);

        if (empty($country) || empty($defaultLanguage) || empty($clinicPage) || empty($clinic)) {
            return null;
        }

        return [
            'domain' => $shop->public_domain ?: $shop->name,
            'country' => $country,
            'defaultLanguage' => $defaultLanguage,
            'language' => $language,
            'dbClinic' => $clinic,
            'clinicPage' => $clinicPage,
            'clinic' => (new ClinicResource($clinic))->toArray($request)
        ];
    }

    public function createClinicMedicalSchemaJson($shop, $clinic, $fadPageHandle, $language) {
        $domain = $shop->public_domain ?: $shop->name;
        $subDomain = getSubdomain($shop->name);
        $languageCode = strtolower(data_get($language, 'code'));
        $languageId = data_get($language, 'id');
        $url = "https://$domain";
        $clinicHandle = ProxyHelper::getClinicHandle($clinic);
        $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, null, ProxyHelper::TYPE_CLINIC, $languageCode);
        $clinicName = ProxyHelper::getClinicDisplayName($clinic);
        $pageDescriptionKey = "clinic.$subDomain.meta_description";
        $pageDescription = getLocale($pageDescriptionKey, $languageCode);
        $parentTitleKey = "clinic.$subDomain.parent_title";

        $data['@context'] = 'https://schema.org';
        $data['@type'] = 'MedicalWebPage';
        if (!empty($clinicName)) $data['name'] = "$clinicName Webpage";
        if (!empty($pageDescription)) $data['description'] = $pageDescription;
        $data['lastReviewed'] = Carbon::parse(data_get($clinic, 'updated_at'))->format('d/m/Y');
        if (!empty($proxyUrl)) {
            $data['url'] = $proxyUrl;
            $data['@id'] = $proxyUrl;
        }

        if (in_array($shop->name, [User::ALK_DK_STORE])) {
            $parentName = ProxyHelper::getRegionName($clinic);
            $parent = ProxyHelper::getClinicRegion($clinic, $languageId);

            $parentUrl = !empty($parent) ? ProxyHelper::getUrl($shop, data_get($parent, 'handle'), null, ProxyHelper::TYPE_FAD_REGION, $languageCode) : null;
            $parentTitle = !empty($parentName) ? getLocale($parentTitleKey, $languageCode, ['parentName' => $parentName]) : null;

            $isPartOf = [
                "@type" => "WebSite"
            ];

            if (!empty($parentTitle)) $isPartOf["name"] = $parentTitle;
            if (!empty($parentUrl)) {
                $isPartOf["url"] = $parentUrl;
                $isPartOf["@id"] = $parentUrl;
            }

            $data['isPartOf'] = $isPartOf;

            $breadcrumbItemList = $this->createBreadcrumbSchemaJson($shop, $clinic, $fadPageHandle, $languageCode);
            if (!empty($breadcrumbItemList)) {
                $data['breadcrumb'] = [
                    "@type" => "BreadcrumbList",
                    "itemListElement" => $breadcrumbItemList
                ];
            }
        }

        $data['mainEntity'] = $this->createMainEntitySchemaJson($shop, $clinic, $languageCode);

        return json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }

    public function createBreadcrumbSchemaJson($shop, $clinic, $fadPageHandle, $languageCode) {
        $breadcrumbs = $this->getBreadcrumbsList($shop, $clinic, $fadPageHandle, $languageCode);

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

    public function createMainEntitySchemaJson($shop, $clinic, $languageCode) {

        $clinicHandle = ProxyHelper::getClinicHandle($clinic);
        $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, null, ProxyHelper::TYPE_CLINIC, $languageCode);
        $clinicName = ProxyHelper::getClinicDisplayName($clinic);
        $clinicPhone = data_get($clinic, 'phone');
        $clinicEmail = data_get($clinic, 'email');

        $mainEntity['@type'] = 'Physician';
        if (!empty($clinicName)) $mainEntity['name'] = $clinicName;
        if (!empty($proxyUrl)) $mainEntity['url'] = $proxyUrl;
        if (!empty($clinicPhone)) $mainEntity['telephone'] = $clinicPhone;
        if (!empty($clinicEmail)) $mainEntity['email'] = $clinicEmail;

        $mainEntityAddress = [
            "@type" => 'PostalAddress',
        ];

        if (!empty(data_get($clinic, 'street') ?: null)) $mainEntityAddress["streetAddress"] = data_get($clinic, 'street');
        if (!empty(data_get($clinic, 'city') ?: null)) $mainEntityAddress["addressLocality"] = data_get($clinic, 'city');
        if (!empty(data_get($clinic, 'zipcode') ?: null)) $mainEntityAddress["postalCode"] = data_get($clinic, 'zipcode');
        if (!empty(strtoupper(data_get($shop, 'country.code')))) $mainEntityAddress["addressCountry"] = strtoupper(data_get($shop, 'country.code'));
        $mainEntity['address'] = $mainEntityAddress;

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

        if ($openingHoursSchema) $mainEntity['openingHoursSpecification'] = $openingHoursSchema;

        $medicalSpecialtySchema = [];
        $medicalSpecialties = $this->getMedicalSpecialistSchema($shop, $clinic);
        foreach ($medicalSpecialties as $medicalSpecialty) {
            $medicalSpecialtySchema[] = [
                '@type' => 'MedicalSpecialty',
                'name' => $medicalSpecialty
            ];
        }

        if ($medicalSpecialtySchema) $mainEntity['medicalSpecialty'] = $medicalSpecialtySchema;

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

        if ($availableServicesSchema) $mainEntity['availableService'] = $availableServicesSchema;

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

    public function getBreadcrumbsList($shop, $clinic, $fadPageHandle, $languageCode) {
        $domain = ProxyHelper::getDomain($shop);
        $subDomain = getSubdomain($shop->name);
        $breadcrumbKeys = ['home', 'fad_page', 'clinic_page'];

        $localeData = [
            'domain' => $domain,
            'clinic' => ProxyHelper::getClinicDisplayName($clinic),
            'fadPageHandle' => $fadPageHandle,
            'clinicPagePrefix' => ProxyHelper::getUrlPrefix($shop, ProxyHelper::TYPE_CLINIC, $languageCode),
            'clinicPageHandle' => ProxyHelper::getClinicHandle($clinic)
        ];

        $breadcrumbs = [];
        foreach ($breadcrumbKeys as $breadcrumbKey) {
            $title = getLocale(
                "clinic.$subDomain.breadcrumb.$breadcrumbKey.title",
                $languageCode,
                $localeData
            );
            $url = getLocale(
                "clinic.$subDomain.breadcrumb.$breadcrumbKey.url",
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
