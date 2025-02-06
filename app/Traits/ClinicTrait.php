<?php


namespace App\Traits;


use App\Models\User;

trait ClinicTrait
{
    public function prepareCityPageHandle($city, $state = null)
    {
        $handle = [];
        $city = trim($city);
        $stateCode = trim($state);

        if ($city) {
            $handle[] = str($city)->slug();
        }
        if ($stateCode) {
            $handle[] = str($state)->slug();
        }

        return implode('-', $handle);
    }

    public function prepareSearchText($city, $state, $country)
    {
        $handle = [];
        $city = trim($city);
        $stateCode = trim($state);
        $country = trim($country);

        if ($city) {
            $handle[] = str($city)->ucfirst();
        }
        if ($stateCode) {
            $handle[] = str($state)->upper();
        }
        if ($country) {
            $handle[] = str($country)->upper();
        }

        return implode(', ', $handle);
    }

    public function prepareClinicMedicalStructureJson($clinic, $shop)
    {
        $clinicName = data_get($clinic, 'is_doctor') ?
            trim(data_get($clinic, 'doctor_name')) :
            trim(data_get($clinic, 'clinic_name'));

        $city = trim(data_get($clinic, 'city'));

        $medicalSpecialty = [];
        $other = data_get($clinic, 'other');

        if ($shop->name === User::ALK_NL_STORE) {

            $clinicName = trim(data_get($clinic, 'clinic_name'));

            if (!empty($other)) {
                $preparedOther = trim(strtolower($other));

                if (str($preparedOther)->contains('allergologie')) {

                    $medicalSpecialty[] = "https://schema.org/Otolaryngologic";
                    $medicalSpecialty[] = "https://schema.org/Pulmonary";
                }

                if (str($preparedOther)->contains('longziekten') || str($preparedOther)->contains('long')) {

                    $medicalSpecialty[] = "https://schema.org/Pulmonary";
                }

                if (str($preparedOther)->contains('dermatologie')) {

                    $medicalSpecialty[] = "https://schema.org/Dermatology";
                }

                if (str($preparedOther)->contains('kinderallergologie') || str($preparedOther)->contains('kinder')) {

                    $medicalSpecialty[] = "https://schema.org/Pediatric";
                }

                if (str($preparedOther)->contains('kno')) {

                    $medicalSpecialty[] = "https://schema.org/Otolaryngologic";
                }
            }

            if (empty($medicalSpecialty)) {
                $medicalSpecialty = ["https://schema.org/Otolaryngologic", "https://schema.org/Pulmonary"];
            }
        }

        $data = [
            "@context" => "https://schema.org/",
            "@type" => "https://schema.org/MedicalClinic",
            "name" => "$clinicName $city",
            "address" => [
                "@type" => "https://schema.org/PostalAddress",
                "streetAddress" => data_get($clinic, 'street'),
                "addressLocality" => data_get($clinic, 'city'),
                "postalCode" => data_get($clinic, 'zipcode')
            ]
        ];

        if ($shop->name === User::ALK_NL_STORE) {
            $data['medicalSpecialty'] = $medicalSpecialty;
            $data['availableService'] = [
                [
                    "@type" => "https://schema.org/MedicalTherapy",
                    "name" => data_get($clinic, 'specialist_areas', [])
                ]
            ];
        } elseif ($shop->name === User::ALK_NO_STORE) {

            $data['telephone'] = data_get($clinic, 'phone');

            /*if ($other) {
                $preparedName = trim($other);
                $preparedName = head(explode("\n", $preparedName));
                $preparedName = trim(head(explode(',', $preparedName)));

                $data['medicalSpecialty'] = [
                    '@type' => 'https://schema.org/Physician',
                    'name' => $preparedName
                ];
            }*/
        }

        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    public function getMaxResults($country)
    {
        return  match (strtolower($country)) {
            'us' => 25,
            'ca' => 20,
            'de' => 30,
            'nl' => 25,
            'no' => 20,
            default => 25,
        };
    }

    public function getSeconds($time)
    {
        sscanf($time, "%d:%d:%d", $hours, $minutes, $seconds);
        return ($hours * 3600) + ($minutes * 60) + ($seconds ?? 0);
    }

    public function saveClinic($clinic, $shop, $country, $data, $fromApp = false)
    {
        $clinic->user_id = data_get($shop, 'id');
        $clinic->datahub_clinic_id = !$fromApp ? data_get($data, 'id') : null;
        $clinic->title = data_get($data, 'title') ?: null;
        $clinic->first_name = data_get($data, 'first_name') ?: null;
        $clinic->last_name = data_get($data, 'last_name') ?: null;
        $clinic->clinic_name = data_get($data, 'clinic_name') ?: null;
        $clinic->clinic_handle = data_get($data, 'clinic_handle') ?: null;
        $clinic->doctor_name = data_get($data, 'doctor_name') ?: null;
        $clinic->doctor_handle = data_get($data, 'doctor_handle') ?: null;
        $clinic->email = data_get($data, 'email') ?: null;
        $clinic->phone = data_get($data, 'phone') ?: null;
        $clinic->website = data_get($data, 'website') ?: null;
        $clinic->street = data_get($data, 'street') ?: null;
        $clinic->zipcode = data_get($data, 'zipcode') ?: null;
        $clinic->city = data_get($data, 'city') ?: null;
        $clinic->state = data_get($data, 'state') ?: null;
        $clinic->region = data_get($data, 'region') ?: null;
        $clinic->country_id = data_get($country, 'id');
        $clinic->country = data_get($country, 'name');
        $clinic->latitude = data_get($data, 'latitude') ?: null;
        $clinic->longitude = data_get($data, 'longitude') ?: null;
        $clinic->is_doctor = data_get($data, 'is_doctor') ?: false;
        $clinic->only_private_patients = data_get($data, 'only_private_patients') ?: false;
        $clinic->reference_required = data_get($data, 'reference_required') ?: false;
        $clinic->is_allergy_specialist = data_get($data, 'is_allergy_specialist') ?: false;
        $clinic->is_allergy_diagnostic = data_get($data, 'is_allergy_diagnostic') ?: false;
        $clinic->is_subcutaneous_immunotherapy = data_get($data, 'is_subcutaneous_immunotherapy') ?: false;
        $clinic->is_sublingual_immunotherapy = data_get($data, 'is_sublingual_immunotherapy') ?: false;
        $clinic->is_venom_immunotherapy = data_get($data, 'is_venom_immunotherapy') ?: false;
        $clinic->is_insect_allergy_diagnostic = data_get($data, 'is_insect_allergy_diagnostic') ?: false;
        $clinic->online_appointment_url = data_get($data, 'online_appointment_url') ?: null;
        $clinic->telehealth = data_get($data, 'telehealth') ?: null;
        $clinic->waiting_time = data_get($data, 'waiting_time') ?: null;
        $clinic->description = data_get($data, 'description') ?: null;
        $clinic->other = data_get($data, 'other') ?: null;
        $clinic->manual_inserted = $fromApp ? true : (data_get($data, 'manual_inserted') ?: false);

        if ($shop->name == User::ALK_DK_STORE) {
            $clinic->updated_at = data_get($data, 'updated_at') ?: now();
        }

        return $clinic;
    }

    public function relations()
    {
        return [
            'specialistAreas', 'insuranceCompanies', 'otherServices', 'diagnosticServices', 'clinicTypes', 'clinicWeekDays'
        ];
    }
}
