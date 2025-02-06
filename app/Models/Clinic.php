<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    use HasFactory;

    public const RELATIONS = [
        'specialistAreas', 'specialistAreaTitles', 'insuranceCompanies', 'otherServices', 'diagnosticServices', 'clinicTypes', 'clinicWeekDays'
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_doctor' => 'boolean',
        'only_private_patients' => 'boolean',
        'reference_required' => 'boolean',
        'is_allergy_specialist' => 'boolean',
        'is_allergy_diagnostic' => 'boolean',
        'is_insect_allergy_diagnostic' => 'boolean',
        'is_subcutaneous_immunotherapy' => 'boolean',
        'is_sublingual_immunotherapy' => 'boolean',
        'is_venom_immunotherapy' => 'boolean',
        'manual_inserted' => 'boolean'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function specialistAreas()
    {
        return $this->belongsToMany(SpecialistArea::class, 'clinic_specialist_area', 'clinic_id', 'specialist_area_id');
    }

    public function specialistAreaTitles()
    {
        return $this->belongsToMany(SpecialistAreaTitle::class, 'clinic_specialist_area_title', 'clinic_id', 'specialist_area_title_id');
    }

    public function insuranceCompanies()
    {
        return $this->belongsToMany(InsuranceCompany::class, 'clinic_insurance_company', 'clinic_id', 'insurance_company_id');
    }

    public function otherServices()
    {
        return $this->belongsToMany(OtherService::class, 'clinic_other_service', 'clinic_id', 'other_service_id');
    }

    public function diagnosticServices()
    {
        return $this->belongsToMany(DiagnosticService::class, 'clinic_diagnostic_service', 'clinic_id', 'diagnostic_service_id');
    }

    public function clinicWeekDays()
    {
        return $this->hasMany(ClinicWeekDay::class)->with('weekDay', 'clinicWeekDayOpeningHours');
    }

    public function clinicTypes()
    {
        return $this->belongsToMany(ClinicType::class, 'clinic_clinic_type', 'clinic_id', 'clinic_type_id');
    }
}
