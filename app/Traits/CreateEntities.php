<?php

namespace App\Traits;

use App\Models\Clinic;
use App\Models\ClinicType;
use App\Models\ClinicWeekDay;
use App\Models\DiagnosticService;
use App\Models\InsuranceCompany;
use App\Models\OtherService;
use App\Models\SpecialistArea;
use App\Models\SpecialistAreaTitle;

trait CreateEntities
{

    public function createEntities(Clinic $clinic, $data, $weekDays = null)
    {

        $entity = data_get($data, 'specialist_areas') ?: [];
        $this->createSpecialistAreas($clinic, $entity);

        $entity = data_get($data, 'specialist_area_titles') ?: [];
        $this->createSpecialistAreaTitles($clinic, $entity);

        $entity = data_get($data, 'insurance_companies') ?: [];
        $this->createInsuranceCompanies($clinic, $entity);

        $entity = data_get($data, 'other_services') ?: [];
        $this->createOtherServices($clinic, $entity);

        $entity = data_get($data, 'diagnostic_services') ?: [];
        $this->createDiagnosticServices($clinic, $entity);

        $entity = data_get($data, 'clinic_types') ?: [];
        $this->createClinicTypes($clinic, $entity);

        if (!empty($weekDays)) {
            $entity = data_get($data, 'timings') ?: [];
            $this->createWeekDayTimings($clinic, $weekDays, $entity);
        }
    }

    public function createInsuranceCompanies(Clinic $clinic, $data): array
    {

        $ids = [];
        foreach ($data as $name) $ids[] = InsuranceCompany::firstOrCreate(['name' => $name])->id;
        $clinic->insuranceCompanies()->sync($ids);
        return $ids;
    }

    public function createSpecialistAreas(Clinic $clinic, $data): array
    {

        $ids = [];
        foreach ($data as $name) $ids[] = SpecialistArea::firstOrCreate(['name' => $name])->id;
        $clinic->specialistAreas()->sync($ids);
        return $ids;
    }

    public function createSpecialistAreaTitles(Clinic $clinic, $data): array
    {

        $ids = [];
        foreach ($data as $name) $ids[] = SpecialistAreaTitle::firstOrCreate(['name' => $name])->id;
        $clinic->specialistAreaTitles()->sync($ids);
        return $ids;
    }

    public function createDiagnosticServices(Clinic $clinic, $data): array
    {

        $ids = [];
        foreach ($data as $name) $ids[] = DiagnosticService::firstOrCreate(['name' => $name])->id;
        $clinic->diagnosticServices()->sync($ids);
        return $ids;
    }

    public function createOtherServices(Clinic $clinic, $data): array
    {

        $ids = [];
        foreach ($data as $name) $ids[] = OtherService::firstOrCreate(['name' => $name])->id;
        $clinic->otherServices()->sync($ids);
        return $ids;
    }

    public function createClinicTypes(Clinic $clinic, $data): array
    {

        $ids = [];
        foreach ($data as $name) $ids[] = ClinicType::firstOrCreate(['name' => $name])->id;
        $clinic->clinicTypes()->sync($ids);
        return $ids;
    }

    public function createWeekDayTimings(Clinic $clinic, $weekDays, $data)
    {

        $clinic->clinicWeekDays()->delete();

        if (empty($data)) {
            return;
        }

        $weekDays = $weekDays->keyBy('index');

        foreach ($data as $datum) {

            $weekDay = data_get($weekDays, data_get($datum, 'index'));

            if (empty($weekDay)) {
                continue;
            }

            if (empty(data_get($datum, 'opening_hours'))) {
                continue;
            }

            $translations = data_get($datum, 'translations');

            if (!empty(array_diff_assoc($weekDay->translations, $translations)) ||
                !empty(array_diff_assoc($translations, $weekDay->translations))) {

                $weekDay->translations = $translations;
                $weekDay->save();
            }

            $clinicWeekDay = [
                'clinic_id' => $clinic->id,
                'week_day_id' => $weekDay->id,
            ];
            $clinicWeekDay = ClinicWeekDay::firstOrCreate($clinicWeekDay, $clinicWeekDay);

            $openingHours = [];
            foreach (data_get($datum, 'opening_hours') ?: [] as $openingHour) {
                $openingHour['clinic_week_day_id'] = $clinicWeekDay->id;
                $openingHours[] = $openingHour;
            }

            $clinicWeekDay->clinicWeekDayOpeningHours()->insert($openingHours);
        }
    }
}
