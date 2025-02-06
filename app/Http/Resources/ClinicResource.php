<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicResource extends JsonResource
{

    public static $wrap = 'clinic';

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'clinic_name' => $this->clinic_name,
            'clinic_handle' => $this->clinic_handle,
            'doctor_name' => $this->doctor_name,
            'doctor_handle' => $this->doctor_handle,
            'email' => $this->email,
            'phone' => $this->phone,
            'website' => $this->website,
            'street' => $this->street,
            'zipcode' => $this->zipcode,
            'city' => $this->city,
            'state' => $this->state,
            'region' => $this->region,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_doctor' => $this->is_doctor,
            'only_private_patients' => $this->only_private_patients,
            'reference_required' => $this->reference_required,
            'is_allergy_specialist' => $this->is_allergy_specialist,
            'is_allergy_diagnostic' => $this->is_allergy_diagnostic,
            'is_subcutaneous_immunotherapy' => $this->is_subcutaneous_immunotherapy,
            'is_sublingual_immunotherapy' => $this->is_sublingual_immunotherapy,
            'is_venom_immunotherapy' => $this->is_venom_immunotherapy,
            'is_insect_allergy_diagnostic' => $this->is_insect_allergy_diagnostic,
            'online_appointment_url' => $this->online_appointment_url,
            'telehealth' => $this->telehealth,
            'waiting_time' => $this->waiting_time,
            'description' => $this->description,
            'other' => $this->other,
            'manual_inserted' => $this->manual_inserted,
            'insurance_companies' => $this->whenLoaded('insuranceCompanies', $this->insuranceCompanies->pluck('name')->toArray()),
            'specialist_areas' => $this->whenLoaded('specialistAreas', $this->specialistAreas->pluck('name')->toArray()),
            'specialist_area_titles' => $this->whenLoaded('specialistAreaTitles', $this->specialistAreaTitles->pluck('name')->toArray()),
            'diagnostic_services' => $this->whenLoaded('diagnosticServices', $this->diagnosticServices->pluck('name')->toArray()),
            'other_services' => $this->whenLoaded('otherServices', $this->otherServices->pluck('name')->toArray()),
            'timings' => (new ClinicWeekDayCollection($this->whenLoaded('clinicWeekDays')))->toArray($request),
            'clinic_types' => $this->whenLoaded('clinicTypes', $this->clinicTypes->pluck('name')->toArray()),
            'updated_at' => $this->updated_at
        ];

        try {
            $shop = $request->user();
            if (in_array(data_get($shop, 'name'), [User::KLARIFY_AT_STORE, User::KLARIFY_US_STORE])) {
                $data['distance'] = $this->distance;
            }
        } catch (\Exception $e) {}

        return $data;
    }
}
