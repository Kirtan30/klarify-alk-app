<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Str;

trait ClinicHtmlDomTrait
{
    use HtmlDomTrait;

    public function setDoctorNameEl($html, $displayName) {
        $this->setInnerText($html, 'doctor-name', $displayName, 'id', true);
    }

    public function setClinicNameEl($html, $clinicName) {
        $this->setInnerText($html, 'doctor-clinic-name', $clinicName, 'id', true);
    }

    public function setClinicEmailEl($html, $clinic) {
        $email = data_get($clinic, 'email');

        if (empty($email)) {
            $this->removeClinicEmailEl($html);
        } else {
            $element = $this->getElement($html, 'doctor-email');
            if (!empty($element)) {
                $element->innertext = $email;
                $element->setAttribute('href', "mailto:{$email}");
            }
        }
    }

    public function setClinicPhoneEl($html, $clinic, $trimPhone = false) {
        $phone = data_get($clinic, 'phone');

        if ($trimPhone) {
            $phone = Str::replace(['(', ')'], '', $phone);
        }

        if (empty($phone)) {
            $this->removeClinicPhoneEl($html);
        } else {
            $element = $this->getElement($html, 'doctor-phone');
            if (!empty($element)) {
                $element->innertext = $phone;
                $element->setAttribute('href', "tel:$phone");
            }
        }
    }

    public function setClinicAddressEl($html, $address) {
        if (empty($address)) {
            $this->removeClinicAddressEl($html);
        } else {
            $this->setInnerText($html, 'doctor-address', $address);
        }
    }

    public function setClinicWebsiteEl($html, $clinic) {
        $website = data_get($clinic, 'website');

        if (empty($website)) {
            $this->removeClinicWebsiteEl($html);
        } else {
            $this->setAttributes($html, 'doctor-website', ['href' => $website]);
        }
    }

    public function setClinicTypesEl($html, $shop, $clinicTypes, $languageCode) {
        if (empty($clinicTypes)) {
            $this->removeClinicTypeEl($html);
        } else {
            $subDomain = getSubdomain($shop->name);
            $element = $this->getElement($html, 'doctor-clinic-types');
            if (!empty($element)) {
                foreach ($clinicTypes as $index => $clinicType) {
                    $type = strtolower($clinicType);
                    $clinicTypes[$index] = getLocale("clinic.$subDomain.$type", $languageCode) ?: $clinicType;
                }
                $types = implode(', ', array_filter($clinicTypes));
                if ($types) $element->innertext = " ($types)";
            }
        }
    }

    public function setClinicAllergySpecialistEl($shop, $html, $clinic, $languageCode) {
        $subDomain = getSubdomain($shop->name);
        $specialistAreaTitles = implode(', ', array_filter(data_get($clinic, 'specialist_area_titles')));
        $key = "clinic.$subDomain.is_allergy_specialist";
        $data = ['specialistAreaTitles' => $specialistAreaTitles];
        $value = __($key, $data, $languageCode) !== $key ? __($key, $data, $languageCode) . ' <br>' : null;
        $data = data_get($clinic, 'is_allergy_specialist') ? $value : null;
        $this->setInnerText($html, 'doctor-is-allergy-specialist', $data, 'id', true);
    }

    public function setClinicGeneralTextEl($html, $shop, $clinic, $languageCode) {

        $allergySpecialistSubDomains = [
            User::KLARIFY_US_STORE
        ];

        $subDomain = getSubdomain($shop->name);

        $doctorGeneralText = [];

        if (in_array($shop->name, $allergySpecialistSubDomains) && data_get($clinic, 'is_allergy_specialist')) {

            $doctorGeneralText[] = getLocale("clinic.$subDomain.is_allergy_specialist", $languageCode);
        }
        if (data_get($clinic, 'telehealth')) {
            $doctorGeneralText[] = getLocale("clinic.$subDomain.telehealth", $languageCode);
        }
        if (data_get($clinic, 'waiting_time')) {
            $doctorGeneralText[] = implode(' ', [
                data_get($clinic, 'waiting_time'),
                getLocale("clinic.$subDomain.waiting_time", $languageCode)
            ]);
        }

        $doctorGeneralText = implode(', ', array_filter($doctorGeneralText));

        if (empty($doctorGeneralText)) {
            $this->removeClinicGeneralTextEl($html);
        } else {
            $this->setInnerText($html, 'doctor-general-text', $doctorGeneralText);
        }
    }

    public function setClinicOnlineAppointmentUrlEl($html, $clinic) {
        $url = data_get($clinic, 'online_appointment_url');

        if (empty($url)) {
            $this->removeClinicOnlineAppointmentUrlEl($html);
        } else {
            $this->setAttributes($html, 'doctor-online-appointment-url', ['href' => $url]);
        }
    }

    public function setClinicTelehealthUrl($html, $clinic) {
        $url = data_get($clinic, 'telehealth');

        if (empty($url)) {
            $this->removeClinicTelehealthUrlEl($html);
        } else {
            $this->setAttributes($html, 'doctor-telehealth', ['href' => $url]);
        }
    }

    public function setDoctorDescriptionEl($html, $clinic) {
        $clinicDescription = data_get($clinic, 'description');
        $this->setInnerText($html, 'doctor-description', $clinicDescription, 'id', true);
    }

    public function setDoctorOtherEl($html, $clinic, $replaceWithBr = false) {
        $clinicOther = $replaceWithBr ? str_replace("\n", '<br />', $clinic['other']) : data_get($clinic, 'other');
        if(empty($clinicOther)) {
            $this->removeDoctorOtherEl($html);
        } else {
            $this->setInnerText($html, 'doctor-other', " $clinicOther", 'id', true);
        }
    }

    public function setDoctorRegionEl($html, $clinic, $regionUrl) {
        $region = data_get($clinic, 'region');
        if(empty($region)) {
            $this->removeDoctorRegionEl($html);
        } else {
            $this->setAttributes($html, 'doctor-region', ['href' => $regionUrl]);
            $this->setInnerText($html, 'doctor-region-name', $region);
        }
    }

    public function setDoctorBreadcrumbNameEl($html, $name) {
        $this->setInnerText($html, 'breadcrumb-clinic-name', $name, 'id', true);
    }

    public function setDoctorBreadcrumbRegionNameEl($html, $name, $url) {
        if (!empty($name)) {
            $this->setInnerText($html, '#breadcrumb-region-name a', $name, 'find');
            $this->setAttributes($html, '#breadcrumb-region-name a', ['href' => $url]);
        } else {
            $this->removeElement($html, 'breadcrumb-region-name');
        }
    }

    public function setClinicTreatmentsEl($html, $shop, $clinic, $languageCode) {
        $treatments = [
            'is_allergy_diagnostic' => data_get($clinic, 'is_allergy_diagnostic'),
            'is_insect_allergy_diagnostic' => data_get($clinic, 'is_insect_allergy_diagnostic'),
            'is_sublingual_immunotherapy' => data_get($clinic, 'is_sublingual_immunotherapy'),
            'is_subcutaneous_immunotherapy' => data_get($clinic, 'is_subcutaneous_immunotherapy'),
            'is_venom_immunotherapy' => data_get($clinic, 'is_venom_immunotherapy')
        ];

        $treatments = array_filter($treatments);
        $subDomain = getSubdomain($shop->name);

        if (empty($treatments)) {
            $this->removeClinicTreatmentsEl($html);
        } else {
            $treatmentText = [];
            foreach ($treatments as $treatment => $value) {
                $text = getLocale("clinic.$subDomain.$treatment", $languageCode);
                if (!empty($text)) $treatmentText[] = $text;
            }

            $this->setListAndRemoveChildNodes($html, $treatmentText, 'doctor-treatments');
        }
    }

    public function setClinicTypesListEl($html, $shop, $clinicTypes, $languageCode) {
        if (empty($clinicTypes)) {
            $this->removeClinicTypesListEl($html);
        } else {
            $subDomain = getSubdomain($shop->name);
            foreach ($clinicTypes as $index => $clinicType) {
                $type = strtolower($clinicType);
                $clinicTypes[$index] = getLocale("clinic.$subDomain.$type", $languageCode) ?: $clinicType;
            }
            $this->setListAndRemoveChildNodes($html, $clinicTypes, 'doctor-clinic-types');
        }
    }

    public function setClinicAllergyDiagnosticsServicesEl($html, $clinic) {
        $diagnosticServices = data_get($clinic, 'diagnostic_services');

        if (empty($diagnosticServices)) {
            $this->removeClinicAllergyDiagnosticsServicesEl($html);
        } else {
            $this->setListAndRemoveChildNodes($html, $diagnosticServices, 'doctor-allergy-diagnostics-services');
        }
    }

    public function setClinicAllergyTherapyServicesEl($html, $clinic) {
        $therapyServices = data_get($clinic, 'therapy_services');

        if (empty($therapyServices)) {
            $this->removeClinicAllergyTherapyServicesEl($html);
        } else {
            $this->setListAndRemoveChildNodes($html, $therapyServices, 'doctor-allergy-therapy-services-wrapper');
        }
    }

    public function setClinicSpecialistAreasEl($html, $clinic) {
        $specialistAreas = data_get($clinic, 'specialist_areas');

        if (empty($specialistAreas)) {
            $this->removeClinicSpecialistAreasEl($html);
        } else {
            $this->setListAndRemoveChildNodes($html, $specialistAreas, 'doctor-specialist-areas');
        }
    }

    public function setClinicOtherServicesEl($html, $clinic) {
        $otherServices = data_get($clinic, 'other_services');

        if (empty($otherServices)) {
            $this->removeClinicOtherServicesEl($html);
        } else {
            $this->setListAndRemoveChildNodes($html, $otherServices, 'doctor-other-services');
        }
    }

    public function setClinicInsuranceCompaniesEl($html, $clinic) {
        $insuranceCompanies = data_get($clinic, 'insurance_companies');

        if (empty($insuranceCompanies)) {
            $this->removeClinicInsuranceCompaniesEl($html);
        } else {
            $this->setListAndRemoveChildNodes($html, $insuranceCompanies, 'doctor-insurance-companies');
        }
    }

    public function setListAndRemoveChildNodes($html, $data, $selector) {
        $element = $this->getElement($html, $selector);
        if (!empty($element)) {

            $innerHtml = '';

            foreach ($element->childNodes() as $node) {
                $element->removeChild($node);
            }

            foreach ($data as $datum) {
                $innerHtml .= "<li> $datum</li>";
            }

            $element->innertext = $innerHtml;
        }
    }

    public function setClinicTimingsEl($html, $clinic, $languageCode) {
        if (empty(data_get($clinic, 'timings'))) {
            $this->removeClinicTimingsEl($html);
        } else {
            $element = $this->getElement($html, 'doctor-timings');
            if (!empty($element)) {
                $innerHtml = '';

                $child = $element->find('.opening-hours p');
                $dayEl = data_get($child, 0);
                $timeEl = data_get($child, 1);

                if ($dayEl && $timeEl) {
                    $prepareTimings = [];
                    foreach ($clinic['timings'] as $timing) {
                        foreach (data_get($timing, 'opening_hours') ?: [] as $openingHour) {
                            $day = data_get($timing, "translations.$languageCode") ?: data_get($timing, 'name');
                            $openingTime = trim(data_get($openingHour, 'opening_time'));
                            $closingTime = trim(data_get($openingHour, 'closing_time'));
                            $time = "$openingTime-$closingTime";

                            if ($day && $openingTime && $closingTime) {
                                $prepareTimings[$day][] = $time;
                            }
                        }
                    }

                    foreach ($prepareTimings as $day => $time) {
                        $dayEl->innertext = $day;
                        $timeEl->innertext = implode(' / ', $time);
                        $innerHtml .= $element->innertext;
                    }
                }

                foreach ($element->childNodes() as $node) {
                    $element->removeChild($node);
                }
                $element->innertext = $innerHtml;
            }
        }
    }

    public function setClinicOnlyPrivatePatientsEl($shop, $html, $clinic, $languageCode, $setTextAlways = false) {
        $onlyPrivatePatients = data_get($clinic, 'only_private_patients');
        if (empty($onlyPrivatePatients)) {
            if ($setTextAlways) {
                $subDomain = getSubdomain($shop->name);
                $key = "clinic.$subDomain.all_patients";
                $value = __($key, [], $languageCode) !== $key ? __($key, [], $languageCode) : null;
                $this->setInnerText($html, 'doctor-only-private-patients', " $value");
            } else {
                $this->removeClinicReferenceRequiredEl($html, true);
            }
        } else {
            $subDomain = getSubdomain($shop->name);
            $key = "clinic.$subDomain.only_private_patients";
            $value = __($key, [], $languageCode) !== $key ? __($key, [], $languageCode) : null;
            $this->setInnerText($html, 'doctor-only-private-patients', " $value");
        }
    }

    public function setIsDoctorEl($shop, $html, $clinic, $languageCode) {
        $subDomain = getSubdomain($shop->name);
        $isDoctor = data_get($clinic, 'is_doctor');
        $key = $isDoctor ? "clinic.$subDomain.is_doctor" : "clinic.$subDomain.is_not_doctor";
        $value = __($key, [], $languageCode) !== $key ? __($key, [], $languageCode) : null;
        $this->setInnerText($html, 'doctor-is-doctor', " $value");
    }

    public function removeClinicReferenceRequiredEl($html, $parent = false) {
        $this->removeElement($html, 'doctor-reference-required', 'id', $parent);
    }

    public function removeClinicOnlyPrivatePatientsEl($html, $parent = false) {
        $this->removeElement($html, 'doctor-only-private-patients', 'id', $parent);
    }

    public function removeClinicNameEl($html, $parent = false) {
        $this->removeElement($html, 'doctor-clinic-name', 'id', $parent);
    }

    public function removeClinicTypeEl($html) {
        $this->removeElement($html, 'doctor-clinic-types');
    }

    public function removeClinicTypesListEl($html) {
        $this->removeElement($html, 'doctor-clinic-types-wrapper');
    }

    public function removeClinicGeneralTextEl($html) {
        $this->removeElement($html, 'doctor-general-text');
    }

    public function removeClinicEmailEl($html) {
        $this->removeElement($html, 'doctor-email-wrapper');
    }

    public function removeClinicPhoneEl($html) {
        $this->removeElement($html, 'doctor-phone-wrapper');
    }

    public function removeClinicAddressEl($html) {
        $this->removeElement($html, 'doctor-address-wrapper');
    }

    public function removeClinicWebsiteEl($html) {
        $this->removeElement($html, 'doctor-website-wrapper');
    }

    public function removeClinicOnlineAppointmentUrlEl($html) {
        $this->removeElement($html, 'doctor-online-appointment-url');
    }

    public function removeClinicTelehealthUrlEl($html) {
        $this->removeElement($html, 'doctor-telehealth');
    }

    public function removeDoctorOtherEl($html) {
        $this->removeElement($html, 'doctor-other-wrapper');
    }

    public function removeDoctorRegionEl($html) {
        $this->removeElement($html, 'doctor-region-wrapper');
    }

    public function removeClinicTreatmentsEl($html) {
        $this->removeElement($html, 'doctor-treatments-wrapper');
    }

    public function removeClinicAllergyDiagnosticsServicesEl($html) {
        $this->removeElement($html, 'doctor-allergy-diagnostics-services-wrapper');
    }

    public function removeClinicAllergyTherapyServicesEl($html) {
        $this->removeElement($html, 'doctor-allergy-therapy-services-wrapper');
    }

    public function removeClinicSpecialistAreasEl($html) {
        $this->removeElement($html, 'doctor-specialist-areas-wrapper');
    }

    public function removeClinicOtherServicesEl($html) {
        $this->removeElement($html, 'doctor-other-services-wrapper');
    }

    public function removeClinicInsuranceCompaniesEl($html) {
        $this->removeElement($html, 'doctor-insurance-companies-wrapper');
    }

    public function removeClinicTimingsEl($html) {
        $this->removeElement($html, 'doctor-timings-wrapper');
    }

    public function setClinicDirectionEl($html, $clinic) {
        $clinicLatitude = data_get($clinic, 'latitude');
        $clinicLongitude = data_get($clinic, 'longitude');

        $data = [
            'href' => "https://www.google.com/maps/place/$clinicLatitude,$clinicLongitude",
            'data-latitude' => data_get($clinic, 'latitude'),
            'data-longitude' => data_get($clinic, 'longitude')
        ];

        $this->setAttributes($html, 'doctor-direction', $data);
    }

    public function setClinicMapEl($html, $clinic) {
        $data = [
            'data-latitude' => data_get($clinic, 'latitude'),
            'data-longitude' => data_get($clinic, 'longitude'),
            'data-zipcode' => data_get($clinic, 'zipcode'),
            'data-state' => data_get($clinic, 'state'),
        ];

        $this->setAttributes($html, 'doctor-map', $data);
    }

    public function setClinicMedicalStructureJsonScriptEl($html, $jsonData = '') {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'MedicalClinicJSONStructureData');
        $element->setAttribute('type', 'application/ld+json');
        $element->innertext = $jsonData;
        if (!empty($body)) $body->appendChild($element);
    }

    public function setClinicJsonScriptEl($html, $clinic) {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'clinicJson');
        $element->innertext = 'window.clinic = ' . json_encode($clinic);
        if (!empty($body)) $body->appendChild($element);
    }

    public function setClinicLanguageLinksJsonScriptEl($html, $languageUrls) {
        $body = $this->getElement($html, 'body', 'name');
        $element = $html->createElement('script');
        $element->setAttribute('id', 'languageLinksJson');
        $element->innertext = 'window.languageLinks = ' . json_encode($languageUrls);
        if (!empty($body)) $body->appendChild($element);
    }
}
