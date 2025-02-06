<?php

namespace App\Traits;

use App\Helpers\ProxyHelper;
use App\Http\Resources\ClinicResource;
use App\Models\User;
use Illuminate\Support\Carbon;

trait ClinicPageTrait
{
    use HtmlDomTrait, ProxyTrait, ClinicTrait, ExtendedClinicPageTrait, ClinicHtmlDomTrait;

    public function nlPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);
            $state = ProxyHelper::getClinicStateName($shop, $clinic, $language);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $metaDescription = "Op zoek naar $clinicName in $city? Klik hier! Let op, je hebt een doorverwijzing nodig van de huisarts.";
            $this->setMetaDescriptionTag($html, $metaDescription);

            $this->setTitleTag($html, "$clinicName | Alles over allergie");
            $this->setAllMetaTitleTags($html, "$clinicName $city");
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaOgTypeTag($html);

            $this->setDoctorNameEl($html, $clinicName);

            $address = data_get($clinic, 'street') . '<br>' . data_get($clinic, 'zipcode') . ' ' . data_get($clinic, 'city') . '<br>' . strtoupper($state);
            $this->setClinicAddressEl($html, $address);

            $this->setClinicPhoneEl($html, $clinic);
            $this->setClinicEmailEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setDoctorDescriptionEl($html, $clinic);
            $this->setClinicWebsiteEl($html, $clinic);

            if (empty($clinic['only_private_patients'])) $this->removeClinicOnlyPrivatePatientsEl($html, true);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setDoctorOtherEl($html, $clinic);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            $element = $html->createElement('script');
            $element->setAttribute('id', 'MedicalClinicJSONStructureData');
            $element->setAttribute('type', 'application/ld+json');
            $element->innertext = $this->prepareClinicMedicalStructureJson($clinic, $shop);
            $body = $this->getElement($html, 'body', 'name');
            if (!empty($body)) $body->appendChild($element);

            // Breadcrumb
            $this->setInnerText($html, 'breadcrumb-clinic-name', $clinicName);

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $lastListElementIndex = array_key_last($itemListElements);
                    data_set($breadcrumbJson, "itemListElement.$lastListElementIndex.name", $clinicName);
                    data_set(
                        $breadcrumbJson,
                        "itemListElement.$lastListElementIndex.item",
                        "https://allesoverallergie.nl/apps/pages/clinics/$clinicHandle"
                    );
                }
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            return null;
        }
    }
    public function dePage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');
            $dbClinic = data_get($initialEntities, 'dbClinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $isDoctor = data_get($clinic, 'is_doctor');

            $name = $isDoctor ? trim("$clinicName $doctorName") : $clinicName;

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $clinicPageTitle = trim("$clinicName $doctorName") ." in $city";
            $this->setTitleTag($html, $clinicPageTitle);
            $this->setAllMetaTitleTags($html, $clinicPageTitle);

            $metaDescription = "Suchst du nach einer Allergologin oder einem Allergologen in $city? Kontaktiere ". trim("$clinicName $doctorName") ." unter " .data_get($clinic, 'phone') .". Mehr Infos gibt's bei uns!";
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setMetaOgTypeTag($html);

            $this->setClinicNameEl($html, ($isDoctor ? $clinicName : trim("$clinicName $doctorName")) . '<br>');
            $this->setDoctorNameEl($html, $doctorName ?: $clinicName);

            $this->setClinicAllergySpecialistEl($shop, $html, $clinic, $languageCode);
            $this->setInnerText($html, 'doctor-specialist-areas', !empty(data_get($clinic, 'specialist_areas')) ? implode(', ', data_get($clinic, 'specialist_areas')) . '<br>' : null, 'id', true);

            $address = trim($clinicName . '<br>' . data_get($clinic, 'street') . '<br>' .  data_get($clinic, 'zipcode') . ', ' . data_get($clinic, 'city'));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);

            $this->setClinicWebsiteEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);
            if (empty($clinic['online_appointment_url']) && empty($clinic['website'])) {
                $this->removeElement($html, 'doctor-links-wrapper');
            }
            $this->setClinicTelehealthUrl($html, $clinic);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic);

            $this->setClinicOnlyPrivatePatientsEl($shop, $html, $clinic, $languageCode, true);
            $this->setIsDoctorEl($shop, $html, $clinic, $languageCode);

            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);

            $this->setDoctorOtherEl($html, $clinic, true);
            $this->setClinicTimingsEl($html, $clinic, $languageCode);

            $medicalSchemaOpeningHours = '';
            $openingHourStart = [];
            $openingHourEnd = [];

            if (!empty($clinic['timings'])) {
                foreach ($clinic['timings'] as $timing) {
                    foreach (data_get($timing, 'opening_hours') ?: [] as $index => $openingHour) {
                        $openingTime = trim(data_get($openingHour, 'opening_time'));
                        $closingTime = trim(data_get($openingHour, 'closing_time'));

                        $dayCode = match (strtolower(data_get($timing, 'name'))) {
                            'monday' => 'Mo',
                            'tuesday' => 'Tu',
                            'wednesday' => 'We',
                            'thursday' => 'Th',
                            'friday' => 'Fr',
                            'saturday' => 'Sa',
                            'sunday' => 'Su'
                        };

                        if ($index === 0) {
                            $openingHourStart[] = "$dayCode $openingTime-$closingTime";
                        } else {
                            $openingHourEnd[] = "$dayCode $openingTime-$closingTime";
                        }
                    }
                }

                $medicalSchemaOpeningHours = implode(', ', array_merge($openingHourStart, $openingHourEnd));
            }

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);
            $preparedTreatmentsSchema = [];

            if (!empty($treatments)) {
                foreach ($treatments as $treatment => $value) {
                    $preparedTreatmentsSchema[] = match($treatment) {
                        'is_subcutaneous_immunotherapy', 'is_sublingual_immunotherapy' => 'Spezifische Immuntherapie',
                        'is_venom_immunotherapy' => 'Spezifische Immuntherapie Insektengift',
                        default => null
                    };
                }
            }

            // Breadcrumb
            $this->setInnerText($html, 'breadcrumb-clinic-name', $name);

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $lastBreadcrumb = array_pop($itemListElements);
                    $appendBreadcrumbs = [
                        array_merge($lastBreadcrumb, [
                            'name' => $name,
                            'item' => $proxyUrl
                        ])
                    ];

                    $itemListElements = array_merge($itemListElements, $appendBreadcrumbs);
                }
                data_set($breadcrumbJson, 'itemListElement', $itemListElements);
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }

            $element = $html->createElement('script');
            $element->setAttribute('id', 'MedicalClinicJSONStructureData');
            $element->setAttribute('type', 'application/ld+json');
            $medicalSpeciality = [];
            $medicalSpecialities = data_get($clinic, 'specialist_areas');
            if (data_get($clinic, 'is_allergy_specialist')) {
                $medicalSpecialities[] = 'Allergologie';
            }
            $medicalSpecialities = array_values(array_unique($medicalSpecialities));

            foreach ($medicalSpecialities as $specialistArea) {
                $medicalSpeciality[] = match (strtolower($specialistArea)) {
                    'allergologie' => 'https://schema.org/Otolaryngologic',
                    'dermatologie' => 'https://schema.org/Dermatology',
                    'pädiatrie' => 'https://schema.org/Pediatric',
                    default => null
                };
                if (strtolower($specialistArea) === 'allergologie') {
                    $medicalSpeciality[] = 'https://schema.org/Pulmonary';
                }
            }

            $medicalSchemaData = [
                "@context" => "https://schema.org/",
                "@type" => "https://schema.org/Physician",
                "name" => $doctorName ?: $name,
                "telephone" => data_get($clinic, 'phone'),
                "email" => data_get($clinic, 'email'),
                "address" => [
                    "@type" => "https://schema.org/PostalAddress",
                    "streetAddress" => data_get($clinic, 'street'),
                    "addressLocality" => data_get($clinic, 'city'),
                    "postalCode" => data_get($clinic, 'zipcode')
                ],
                "medicalSpecialty" => array_values(array_unique(array_filter($medicalSpeciality))),
                "openingHours" => $medicalSchemaOpeningHours,
                "availableService" => [
                    [
                        "@type" => "https://schema.org/MedicalTherapy",
                        "name" => array_values(array_unique(array_filter($preparedTreatmentsSchema)))
                    ],
                    [
                        "@type" => "https://schema.org/MedicalTest",
                        "name" => "Allergiediagnostik"
                    ]
                ]
            ];
            $element->innertext = json_encode($medicalSchemaData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

            $body = $this->getElement($html, 'body', 'name');
            if (!empty($body)) $body->appendChild($element);

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Letztes Update: ' . Carbon::parse(data_get($dbClinic, 'updated_at'))->format('d/m/Y');
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .social-icon ', 'find');
            if (!empty($element)) {
                $element->remove();
            }

            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            return null;
        }
    }

    /*public function dePage($request, $shop, $clinicHandle)
    {
        $domain = $shop->public_domain ?: $shop->name;

        $country = $shop->country;

        if (empty($country)) {
            return null;
        }

        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();

        if (!$defaultLanguage) {
            return null;
        }

        $clinicPageHandle = data_get($defaultLanguage, 'pivot.clinic_page');

        try {
            $clinicsQuery = $shop->clinics()->with($this->relations());

            $clinic = $clinicsQuery->where(function ($query) use ($clinicHandle) {
                $query->where('doctor_handle', $clinicHandle)
                    ->orWhere('clinic_handle', $clinicHandle);
            })->first();

            $clinic = !empty($clinic) ? new ClinicResource($clinic) : [];
            $dbClinic = $clinic;

            if (empty($clinic)) {
                return null;
            }

            $clinic = $clinic->toArray($request);

            $clinicName = trim(data_get($clinic, 'clinic_name'));
            $doctorName = trim(data_get($clinic, 'doctor_name'));

            $isDoctor = data_get($clinic, 'is_doctor');

            $name = $isDoctor ? trim("$clinicName $doctorName") : $clinicName;

            $city = ucwords(trim(data_get($clinic, 'city')));

            $clinicPage = $this->getShopifyPage($shop, $clinicPageHandle);

            if (!$clinicPage) {
                return null;
            }

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $proxyUrl = "https://$domain/apps/pages/facharztpraxen/$clinicHandle";

            $clinicPageTitle = trim("$clinicName $doctorName") ." in $city";

            $element = $this->getElement($html, 'meta[name=robots]', 'find');
            if (!empty($element)) $element->remove();

            $metaDescription = "Suchst du nach einer Allergologin oder einem Allergologen in $city? Kontaktiere ". trim("$clinicName $doctorName") ." unter " .data_get($clinic, 'phone') .". Mehr Infos gibt's bei uns!";
            $this->setAttributes($html, 'meta[name=description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[property=og:description]', ['content' => $metaDescription], 'find');
            $this->setAttributes($html, 'meta[name=twitter:description]', ['content' => $metaDescription], 'find');


            $this->setInnerText($html, 'title', $clinicPageTitle, 'name');

            $this->setAttributes($html, 'link[rel=canonical]', ['href' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:url]', ['content' => $proxyUrl], 'find');

            $this->setAttributes($html, 'meta[property=og:title]', ['content' => $clinicPageTitle], 'find');
            $this->setAttributes($html, 'meta[name=twitter:title]', ['content' => $clinicPageTitle], 'find');

            $this->setAttributes($html, 'meta[property=og:type]', ['content' => 'Clinic'], 'find');

            $this->setInnerText($html, 'doctor-clinic-name', $doctorName ?: $clinicName);
//            $this->removeElement($html, 'clinic-title');
//            $this->setInnerText($html, 'doctor-title', $doctorName ?: $clinicName, 'id', true);

            $this->setInnerText($html, 'doctor-is-allergy-specialist', data_get($clinic, 'is_allergy_specialist') ? "Allergologie <br>" : null, 'id', true);

            $this->setInnerText($html, 'doctor-specialist-areas', !empty(data_get($clinic, 'specialist_areas')) ? implode(', ', data_get($clinic, 'specialist_areas')) . '<br>' : null, 'id', true);

            $this->setInnerText($html, 'doctor-name', ($isDoctor ? $clinicName : trim("$clinicName $doctorName")) . '<br>', 'id', true);

            $this->setInnerText($html, 'doctor-street', data_get($clinic, 'street'), 'id', true);

            $this->setInnerText($html, 'doctor-zipcode', data_get($clinic, 'zipcode') . '&nbsp;', 'id', true);

            $this->setInnerText($html, 'doctor-city', data_get($clinic, 'city'), 'id', true);

            $this->setAttributes($html, 'doctor-map', [
                'data-latitude' => data_get($clinic, 'latitude'),
                'data-longitude' => data_get($clinic, 'longitude')
            ], 'id');

            if (empty($clinic['website'])) {
                $this->removeElement($html, 'doctor-website-wrapper', 'id');
            } else {
                $element = $this->getElement($html, 'doctor-website', 'id');
                if (!empty($element)) {
                    $element->setAttribute('href', $clinic['website']);
                }
            }

            if (empty($clinic['online_appointment_url'])) {
                $this->removeElement($html, 'doctor-online-appointment-url-wrapper', 'id');
            } else {
                $element = $this->getElement($html, 'doctor-online-appointment-url', 'id');
                if (!empty($element)) {
                    $element->setAttribute('href', $clinic['online_appointment_url']);
                }
            }

            if (empty($clinic['online_appointment_url']) && empty($clinic['website'])) {
                $this->removeElement($html, 'doctor-links-wrapper');
            }

            if (empty($clinic['phone'])) {
                $this->removeElement($html, 'doctor-tel-wrapper', 'id');
            } else {
                $element = $this->getElement($html, 'doctor-tel', 'id');
                if (!empty($element)) {
                    $element->setAttribute('href', "tel:{$clinic['phone']}");
                    $element->innertext = $clinic['phone'];
                }
            }

            if (empty($clinic['email'])) {
                $this->removeElement($html, 'doctor-email-wrapper', 'id');
            } else {
                $element = $this->getElement($html, 'doctor-email', 'id');
                if (!empty($element)) {
                    $element->innertext = $clinic['email'];
                    $element->setAttribute('href', "mailto:{$clinic['email']}");
                }
            }

            $this->setInnerText(
                $html,
                'doctor-only-private-patients',
                !empty($clinic['only_private_patients']) ? 'Privatpatienten' : 'Gesetzlich Versicherte und Privatpatienten'
            );

            $this->setInnerText($html, 'doctor-is-doctor', !empty($clinic['is_doctor']) ? 'Arzt / Ärztin' : 'Klinik / Uniklinik');

            if (empty($clinic['other'])) {
                $this->removeElement($html, 'doctor-other-wrapper', 'id');
            } else {
                $element = $this->getElement($html, 'doctor-other', 'id');
                if (!empty($element)) {
                    $element->innertext = str_replace("\n", '<br />', $clinic['other']);
                }
            }

            $medicalSchemaOpeningHours = '';

            if (empty($clinic['timings'])) {
                $this->removeElement($html, 'doctor-timings-wrapper', 'id');
            } else {
                $element = $this->getElement($html, 'doctor-timings', 'id');
                if (!empty($element)) {
                    $innerHtml = '';

                    foreach ($element->childNodes() as $node) {
                        $element->removeChild($node);
                    }

                    $openingHourStart = [];
                    $openingHourEnd = [];

                    foreach ($clinic['timings'] as $timing) {
                        foreach (data_get($timing, 'opening_hours') ?: [] as $index => $openingHour) {
                            $day = data_get($timing, 'translations.de') ?: data_get($timing, 'name');
                            $openingTime = trim(data_get($openingHour, 'opening_time'));
                            $closingTime = trim(data_get($openingHour, 'closing_time'));
                            $time = "$openingTime - $closingTime";

                            if ($day && $openingTime && $closingTime) {
                                $innerHtml .= "<tr>
                                    <th>$day:</th>
                                    <td>$time</td>
                               </tr>";
                            }

                            $dayCode = match (strtolower(data_get($timing, 'name'))) {
                                'monday' => 'Mo',
                                'tuesday' => 'Tu',
                                'wednesday' => 'We',
                                'thursday' => 'Th',
                                'friday' => 'Fr',
                                'saturday' => 'Sa',
                                'sunday' => 'Su'
                            };

                            if ($index === 0) {
                                $openingHourStart[] = "$dayCode $openingTime-$closingTime";
                            } else {
                                $openingHourEnd[] = "$dayCode $openingTime-$closingTime";
                            }
                        }
                    }

                    $medicalSchemaOpeningHours = implode(', ', array_merge($openingHourStart, $openingHourEnd));

                    $element->innertext = $innerHtml;
                }
            }


            $treatments = [
                'is_allergy_diagnostic' => data_get($clinic, 'is_allergy_diagnostic'),
                'is_insect_allergy_diagnostic' => data_get($clinic, 'is_insect_allergy_diagnostic'),
                'is_sublingual_immunotherapy' => data_get($clinic, 'is_sublingual_immunotherapy'),
                'is_subcutaneous_immunotherapy' => data_get($clinic, 'is_subcutaneous_immunotherapy'),
                'is_venom_immunotherapy' => data_get($clinic, 'is_venom_immunotherapy')
            ];

            $treatments = array_filter($treatments);
            $preparedTreatmentsSchema = [];

            if (empty($treatments)) {

                $this->removeElement($html, 'doctor-treatments-wrapper');
            } else {
                $preparedTreatments = [];
                foreach ($treatments as $treatment => $value) {
                    $preparedTreatments[] = match($treatment) {
                        'is_allergy_diagnostic' => 'Allergiediagnostik inhalative Allergene',
                        'is_insect_allergy_diagnostic' => 'Allergiediagnostik Insekten',
                        'is_sublingual_immunotherapy' => 'Allergie-Immuntherapie sublingual (Tropfen/Tablette)',
                        'is_subcutaneous_immunotherapy' => 'Allergie-Immuntherapie subkutan (Spritze)',
                        'is_venom_immunotherapy' => 'Allergie-Immuntherapie Insektengift',
                        default => null
                    };

                    $preparedTreatmentsSchema[] = match($treatment) {
                        'is_subcutaneous_immunotherapy', 'is_sublingual_immunotherapy' => 'Spezifische Immuntherapie',
                        'is_venom_immunotherapy' => 'Spezifische Immuntherapie Insektengift',
                        default => null
                    };
                }

                $preparedTreatments = array_values(array_unique(array_filter($preparedTreatments)));

                $element = $this->getElement($html, 'doctor-treatments');
                if (!empty($element) && !empty($preparedTreatments)) {

                    $element->innertext = implode(', ', $preparedTreatments);
                }
            }

            // Breadcrumb
            $this->setInnerText($html, 'breadcrumb-clinic-name', $name);

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $lastBreadcrumb = array_pop($itemListElements);
                    $appendBreadcrumbs = [
                        array_merge($lastBreadcrumb, [
                            'name' => $name,
                            'item' => $proxyUrl
                        ])
                    ];

                    $itemListElements = array_merge($itemListElements, $appendBreadcrumbs);
                }
                data_set($breadcrumbJson, 'itemListElement', $itemListElements);
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }

            $element = $html->createElement('script');
            $element->setAttribute('id', 'MedicalClinicJSONStructureData');
            $element->setAttribute('type', 'application/ld+json');
            $medicalSpeciality = [];
            $medicalSpecialities = data_get($clinic, 'specialist_areas');
            if (data_get($clinic, 'is_allergy_specialist')) {
                $medicalSpecialities[] = 'Allergologie';
            }
            $medicalSpecialities = array_values(array_unique($medicalSpecialities));

            foreach ($medicalSpecialities as $specialistArea) {
                $medicalSpeciality[] = match (strtolower($specialistArea)) {
                    'allergologie' => 'https://schema.org/Otolaryngologic',
                    'dermatologie' => 'https://schema.org/Dermatology',
                    'pädiatrie' => 'https://schema.org/Pediatric',
                    default => null
                };
                if (strtolower($specialistArea) === 'allergologie') {
                    $medicalSpeciality[] = 'https://schema.org/Pulmonary';
                }
            }

            $medicalSchemaData = [
                "@context" => "https://schema.org/",
                "@type" => "https://schema.org/Physician",
                "name" => $doctorName ?: $name,
                "telephone" => data_get($clinic, 'phone'),
                "email" => data_get($clinic, 'email'),
                "address" => [
                    "@type" => "https://schema.org/PostalAddress",
                    "streetAddress" => data_get($clinic, 'street'),
                    "addressLocality" => data_get($clinic, 'city'),
                    "postalCode" => data_get($clinic, 'zipcode')
                ],
                "medicalSpecialty" => array_values(array_unique(array_filter($medicalSpeciality))),
                "openingHours" => $medicalSchemaOpeningHours,
                "availableService" => [
                    [
                        "@type" => "https://schema.org/MedicalTherapy",
                        "name" => array_values(array_unique(array_filter($preparedTreatmentsSchema)))
                    ],
                    [
                        "@type" => "https://schema.org/MedicalTest",
                        "name" => "Allergiediagnostik"
                    ]
                ]
            ];
            $element->innertext = json_encode($medicalSchemaData, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

            $body = $this->getElement($html, 'body', 'name');
            if (!empty($body)) $body->appendChild($element);

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Letztes Update: ' . Carbon::parse(data_get($dbClinic, 'updated_at'))->format('d/m/Y');
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .social-icon ', 'find');
            if (!empty($element)) {
                $element->remove();
            }

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            return null;
        }
    }*/

    public function noPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');
            $dbClinic = data_get($initialEntities, 'dbClinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);
            $name = ProxyHelper::getClinicFullName($clinic);
            $regionName = ProxyHelper::getRegionName($clinic);
            $regionPageHandle = str($regionName)->slug();

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setClinicTypesListEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            $clinicPageTitle = ($clinicName ?: $doctorName) . ' | ' . ucwords($city);
            $this->setTitleTag($html, $clinicPageTitle);
            $this->setAllMetaTitleTags($html, $clinicPageTitle);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);
            $this->setMetaOgTypeTag($html);
            $this->removeNoIndexTag($html);

            $regionPageUrl = ProxyHelper::getUrl(
                $shop,
                $regionPageHandle,
                null,
                ProxyHelper::TYPE_FAD_REGION,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $this->setDoctorRegionEl($html, $clinic, $regionPageUrl);
            $parsedRegionPageUrl = parse_url($regionPageUrl, PHP_URL_PATH);

            $this->setDoctorBreadcrumbNameEl($html, $displayName);
            $this->setDoctorBreadcrumbRegionNameEl($html, $regionName, $parsedRegionPageUrl);
            $this->setDoctorNameEl($html, $name);
            $address = trim(data_get($clinic, 'street') . ',<br>' . data_get($clinic, 'city') . ', ' . data_get($clinic, 'zipcode'));
            $this->setClinicAddressEl($html, $address);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic);
            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicWebsiteEl($html, $clinic);
            $this->setDoctorOtherEl($html, $clinic);

            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (empty($onlyPrivatePatients)) {
                $this->setInnerText($html, 'doctor-only-private-patients', 'Henvisning er ikke nødvendig');
            } else {
                $this->setInnerText($html, 'doctor-only-private-patients', 'Henvisning er nødvendig');
            }

            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->removeClinicTimingsEl($html);

            $regionBreadcrumbName = "Klinikker i $regionName";

            $element = $this->getElement($html, 'BreadcrumbList-schema');
            if (!empty($element)) {
                $breadcrumbJson = isJSON($element->innertext) ? json_decode($element->innertext, true) : [];
                $itemListElements = data_get($breadcrumbJson, 'itemListElement') ?: [];
                if (!empty($itemListElements)) {
                    $lastBreadcrumb = array_pop($itemListElements);
                    $appendBreadcrumbs = [
                        array_merge($lastBreadcrumb, [
                            'name' => $regionBreadcrumbName,
                            'item' => $regionPageUrl
                        ]),
                        array_merge($lastBreadcrumb, [
                            'position' => (int) data_get($lastBreadcrumb, 'position') + 1,
                            'name' => $name,
                            'item' => $proxyUrl
                        ])
                    ];

                    $itemListElements = array_merge($itemListElements, $appendBreadcrumbs);
                }
                data_set($breadcrumbJson, 'itemListElement', $itemListElements);
                $element->innertext = json_encode($breadcrumbJson, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }

            $element = $html->createElement('script');
            $element->setAttribute('id', 'MedicalClinicJSONStructureData');
            $element->setAttribute('type', 'application/ld+json');
            $element->innertext = $this->prepareClinicMedicalStructureJson($clinic, $shop);
            $body = $this->getElement($html, 'body', 'name');
            if (!empty($body)) $body->appendChild($element);

            $element = $this->getElement($html, '#shopify-section-footer footer .footer-content .row', 'find');
            if (!empty($element)) {
                foreach ($element->children() as $child) {
                    if (empty($child->innertext)) {
                        $child->innertext = ' ';
                    }
                }
            }

            $this->setClinicJsonScriptEl($html, $clinic);

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Sist oppdatert ' . Carbon::parse(data_get($dbClinic, 'updated_at'))->format('d/m/Y');
            }

            $element = $this->getElement($html, '#shopify-section-footer footer .social-icon ', 'find');
            if (!empty($element)) {
                $element->remove();
            }

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            return null;
        }
    }

    public function usPage($request, $shop, $clinicHandle, $languageCode = null)
    {

        try {

            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $stateName = ProxyHelper::getClinicStateName($shop, $clinic, $language);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $doctorName ?: $clinicName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $displayName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            if (empty($clinicName) && empty($clinic['clinic_types'])) {
                $this->removeClinicNameEl($html, true);
            }

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim($stateName . ' ' . data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->removeClinicTimingsEl($html);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->prepareClinicMedicalStructureJson($clinic, $shop)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function atPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {

            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $stateName = ProxyHelper::getClinicStateName($shop, $clinic, $language);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $doctorName ?: $clinicName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $doctorName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            if (empty($clinicName) && empty($clinic['clinic_types'])) {
                $this->removeClinicNameEl($html, true);
            }

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic, true);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim($stateName . ' ' . data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->setClinicTimingsEl($html, $clinic, $languageCode);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->prepareClinicMedicalStructureJson($clinic, $shop)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function czPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $fadPageHandle = data_get($defaultLanguage, 'pivot.fad_page');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $stateName = ProxyHelper::getClinicStateName($shop, $clinic, $language);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $doctorName ?: $clinicName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $doctorName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            if (empty($clinicName) && empty($clinic['clinic_types'])) {
                $this->removeClinicNameEl($html, true);
            }

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic, true);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim($stateName . ' ' . data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->setClinicTimingsEl($html, $clinic, $languageCode);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->createClinicMedicalSchemaJson($shop, $clinic, $fadPageHandle, $language)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function skPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $fadPageHandle = data_get($defaultLanguage, 'pivot.fad_page');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $displayName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $doctorName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic, true);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim(data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->removeClinicTreatmentsEl($html);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->setClinicTimingsEl($html, $clinic, $languageCode);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->createClinicMedicalSchemaJson($shop, $clinic, $fadPageHandle, $language)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function chPage($request, $shop, $clinicHandle, $languageCode = 'de')
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $language = data_get($initialEntities, 'language');
            $fadPageHandle = data_get($language, 'pivot.fad_page');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);
/*            $cityName = $shop->fadCities
                ->where('language_id', $language->id)
                ->where('handle', str($city)->slug())
                ->first();

            if ($cityName) $city = $cityName;*/

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $languageUrls = ProxyHelper::getLanguageUrls($shop, ProxyHelper::TYPE_CLINIC, $clinicHandle);
            $proxyUrl = data_get($languageUrls, $languageCode);

            $this->setTitleTag($html, $displayName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $doctorName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic, true);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim(data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->removeClinicTreatmentsEl($html);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->setClinicTimingsEl($html, $clinic, $languageCode);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->createClinicMedicalSchemaJson($shop, $clinic, $fadPageHandle, $language)
            );
            $this->setClinicJsonScriptEl($html, $clinic);
            $this->setClinicLanguageLinksJsonScriptEl($html, $languageUrls);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function caPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $displayName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $doctorName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic, true);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim(data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->removeClinicTreatmentsEl($html);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->setClinicTimingsEl($html, $clinic, $languageCode);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->prepareClinicMedicalStructureJson($clinic, $shop)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function odactraPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $stateName = ProxyHelper::getClinicStateName($shop, $clinic, $language);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $doctorName ?: $clinicName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $displayName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            if (empty($clinicName) && empty($clinic['clinic_types'])) {
                $this->removeClinicNameEl($html, true);
            }

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim($stateName . ' ' . data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->removeClinicTimingsEl($html);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->prepareClinicMedicalStructureJson($clinic, $shop)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function ragwitekPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $stateName = ProxyHelper::getClinicStateName($shop, $clinic, $language);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

//            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $doctorName ?: $clinicName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $displayName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            if (empty($clinicName) && empty($clinic['clinic_types'])) {
                $this->removeClinicNameEl($html, true);
            }

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim($stateName . ' ' . data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->removeClinicTimingsEl($html);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->prepareClinicMedicalStructureJson($clinic, $shop)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function grastekPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $city = ProxyHelper::getClinicCity($clinic);

            $stateName = ProxyHelper::getClinicStateName($shop, $clinic, $language);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

//            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $doctorName ?: $clinicName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, "$displayName $city");

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (!empty($onlyPrivatePatients) || !empty($referenceRequired)) {
                if (empty($onlyPrivatePatients)) {
                    $this->removeClinicOnlyPrivatePatientsEl($html);
                }

                if (empty($referenceRequired)) {
                    $this->removeClinicReferenceRequiredEl($html);
                }
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }

            $this->setDoctorNameEl($html, $displayName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            if (empty($clinicName) && empty($clinic['clinic_types'])) {
                $this->removeClinicNameEl($html, true);
            }

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                data_get($clinic, 'city'),
                trim($stateName . ' ' . data_get($clinic, 'zipcode')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->removeClinicTimingsEl($html);
            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->prepareClinicMedicalStructureJson($clinic, $shop)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function dkPage($request, $shop, $clinicHandle, $languageCode = null)
    {
        try {
            $initialEntities = $this->getClinicInitialEntities($request, $shop, $clinicHandle, $languageCode);
            if (empty($initialEntities)) return null;

            $defaultLanguage = data_get($initialEntities, 'defaultLanguage');
            $fadPageHandle = data_get($defaultLanguage, 'pivot.fad_page');
            $language = data_get($initialEntities, 'language');
            $clinicPage = data_get($initialEntities, 'clinicPage');
            $clinic = data_get($initialEntities, 'clinic');

            $languageCode = strtolower(data_get($language, 'code'));

            $clinicName = ProxyHelper::getClinicName($clinic);
            $doctorName = ProxyHelper::getDoctorName($clinic);
            $regionName = ProxyHelper::getRegionName($clinic);
            $regionPageHandle = str($regionName)->slug()->start('region-');

            $displayName = ProxyHelper::getClinicDisplayName($clinic);

            $this->loadSimpleHtmlDom();
            $html = str_get_html($clinicPage);

            $this->removeNoIndexTag($html);

            $proxyUrlPrefix = ProxyHelper::getUrlPrefix(
                $shop,
                ProxyHelper::TYPE_CLINIC,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $proxyUrl = ProxyHelper::getUrl($shop, $clinicHandle, $proxyUrlPrefix);

            $this->setTitleTag($html, $clinicName ?: $doctorName);
            $this->setCanonicalLinkTag($html, $proxyUrl);
            $this->setMetaOgUrlTag($html, $proxyUrl);

            $this->setAllMetaTitleTags($html, $clinicName ?: $doctorName);

            $metaDescription = "I denne klinik kan du få hjælp af en allergilæge med allergitest, diagnose og behandling for din pollenallergi. Find ud af, om henvisning er nødvendig.";
            $this->setAllMetaDescriptionTags($html, $metaDescription);

            $this->setMetaOgTypeTag($html);

            $referenceRequired = data_get($clinic, 'reference_required') ?: false;
            $onlyPrivatePatients = data_get($clinic, 'only_private_patients') ?: false;

            if (empty($onlyPrivatePatients)) {
                $this->removeClinicOnlyPrivatePatientsEl($html);
            }

            if (empty($referenceRequired)) {
                $this->setInnerText($html, 'doctor-reference-required', 'Henvisning er ikke nødvendig');
            }

            $regionPageUrl = ProxyHelper::getUrl(
                $shop,
                $regionPageHandle,
                null,
                ProxyHelper::TYPE_FAD,
                $languageCode,
                data_get($defaultLanguage, 'code')
            );
            $this->setDoctorRegionEl($html, $clinic, $regionPageUrl);
            $parsedRegionPageUrl = parse_url($regionPageUrl, PHP_URL_PATH);

            $this->setDoctorBreadcrumbNameEl($html, $displayName);
            $this->setDoctorBreadcrumbRegionNameEl($html, $regionName, $parsedRegionPageUrl);
            $this->setDoctorNameEl($html, $displayName);
            $this->setClinicNameEl($html, $clinicName);
            $this->setClinicTypesListEl($html, $shop, ProxyHelper::getClinicTypes($clinic), $languageCode);

            if (empty($clinicName) && empty($clinic['clinic_types'])) {
                $this->removeClinicNameEl($html, true);
            }

            $this->setClinicGeneralTextEl($html, $shop, $clinic, $languageCode);

            $this->setClinicEmailEl($html, $clinic);
            $this->setClinicPhoneEl($html, $clinic);

            $address = implode(', ', array_filter([
                data_get($clinic, 'street'),
                trim('<br/>' . data_get($clinic, 'zipcode') . ' ' . data_get($clinic, 'city')),
            ]));

            $this->setClinicAddressEl($html, $address);

            $this->setClinicWebsiteEl($html, $clinic);

            $this->setClinicDirectionEl($html, $clinic);
            $this->setClinicMapEl($html, $clinic);
            $this->setClinicOnlineAppointmentUrlEl($html, $clinic);
            $this->setDoctorOtherEl($html, $clinic);

            $this->setClinicTreatmentsEl($html, $shop, $clinic, $languageCode);

            $this->setClinicAllergyDiagnosticsServicesEl($html, $clinic);
            $this->setClinicAllergyTherapyServicesEl($html, $clinic);
            $this->setClinicSpecialistAreasEl($html, $clinic);
            $this->setClinicOtherServicesEl($html, $clinic);
            $this->setClinicInsuranceCompaniesEl($html, $clinic);
            $this->removeClinicTimingsEl($html);

            $element = $this->getElement($html, 'timestamp-date');
            if (!empty($element)) {
                $element->innertext = 'Sidst opdateret ' . Carbon::parse(data_get($clinic, 'updated_at'))->format('d/m/Y');
            }

            $this->setClinicMedicalStructureJsonScriptEl(
                $html,
                $this->createClinicMedicalSchemaJson($shop, $clinic, $fadPageHandle, $language)
            );
            $this->setClinicJsonScriptEl($html, $clinic);

            $this->removeElement($html, 'BreadcrumbList-schema');
            $this->removeHrefLangTags($html);

            return $html->save();

        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function sitemapUrls($request, $shop, $languageCode = null)
    {
        try {
            $domain = strtolower($shop->name);
            $publicDomain = $shop->public_domain ?: $shop->name;

            $clinics = $shop->clinics->toArray();

            $xmlData = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"><!--  This is the sitemap linking to additional city pages  -->';

            foreach($clinics as $clinic) {

                $clinicHandle = match ($domain) {
                    User::ALK_NL_STORE => data_get($clinic, 'clinic_handle'),
                    User::KLARIFY_US_STORE, User::KLARIFY_AT_STORE, User::KLARIFY_CZ_STORE, User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE => data_get($clinic, 'doctor_handle'),
                    default => data_get($clinic, 'is_doctor') ?
                        data_get($clinic, 'doctor_handle') :
                        data_get($clinic, 'clinic_handle'),
                };

                if (empty($clinicHandle) && in_array($domain, [User::KLARIFY_US_STORE, User::KLARIFY_AT_STORE, User::KLARIFY_CZ_STORE, User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE])) {
                    $clinicHandle = data_get($clinic, 'clinic_handle') ?: data_get($clinic, 'doctor_handle');
                }

                if (empty($clinicHandle)) continue;

                $url = match ($shop->name) {
                    User::ALK_NO_STORE => "https://$publicDomain/apps/pages/klinikker/$clinicHandle",
                    User::ALK_DK_STORE => "https://$publicDomain/apps/pages/klinikker/$clinicHandle",
                    User::ALK_DE_STORE => "https://$publicDomain/apps/pages/facharztpraxen/$clinicHandle",
                    User::KLARIFY_US_STORE => "https://$publicDomain/apps/pages/doctors/$clinicHandle",
                    User::KLARIFY_AT_STORE => "https://$publicDomain/apps/pages/facharztpraxen/$clinicHandle",
                    User::KLARIFY_CZ_STORE => "https://$publicDomain/apps/pages/lekare/$clinicHandle",
                    User::KLARIFY_SK_STORE => "https://$publicDomain/apps/pages/lekara/$clinicHandle",
                    User::KLARIFY_CH_STORE => match ($languageCode) {
                        'de' => "https://$publicDomain/apps/pages/facharztpraxen/$clinicHandle",
                        'fr' => "https://$publicDomain/apps/pages/allergologue/$clinicHandle",
                        'it' => "https://$publicDomain/apps/pages/allergologo/$clinicHandle",
                        default => "https://$publicDomain/apps/pages/facharztpraxen/$clinicHandle",
                    },
                    User::KLARIFY_CA_STORE => "https://$publicDomain/apps/pages/allergists/$clinicHandle",
                    User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE => "https://$publicDomain/apps/pages/doctors/$clinicHandle",
                    default => "https://$publicDomain/apps/pages/clinics/$clinicHandle",
                };

                $xmlData .= "<url><loc>$url</loc></url>";
            }
            $xmlData .= '</urlset>';
            return response($xmlData, 200)->header('Content-type', 'application/xml');

        } catch (\Exception $e) {

            return response("", 500)->header('Content-type', 'application/xml');
        }
    }
}
