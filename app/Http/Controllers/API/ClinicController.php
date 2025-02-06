<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClinicCollection;
use App\Http\Resources\ClinicResource;
use App\Models\Country;
use App\Models\User;
use App\Traits\ClinicTrait;
use Illuminate\Http\Request;

class ClinicController extends Controller
{
    use ClinicTrait;

    public function index(Request $request)
    {
        $request->validate([
            'latitude' => ['required_with:longitude'],
            'longitude' => ['required_with:latitude'],
            'radius' => ['required_with_all:latitude,longitude'],
        ]);

        $shop = $request->user();

        $country = $shop->country;

        $region = $request->get('region');
        $state = $request->get('state');
        $city = $request->get('city');

        $sortOrder = $request->get('order') === 'desc' ? $request->get('order') : 'asc';

        if (empty($country)) {
            return response(['message' => 'Please select country for this store'], 422);
        }

        $countryCode = $country->code;

        $hasPagination = !empty($request->get('page'));
        $hasDistance = !empty($request->get('latitude')) && !empty($request->get('longitude'));

        $perPage = $request->query('length') ?: 15;

        $clinicsQuery = $shop->clinics()
            ->with($this->relations());

        // For pollenkontroll.no & allergiecheck.de & us.klarify.me
        if (in_array(strtolower($countryCode), ['no', 'de', 'us', 'ch', 'dk'])) {

            if (isset($request->is_doctor)) $clinicsQuery->where('is_doctor', (bool) $request->is_doctor);
            if (isset($request->only_private_patients)) $clinicsQuery->where('only_private_patients', (bool) $request->only_private_patients);
            if (isset($request->reference_required)) $clinicsQuery->where('reference_required', (bool) $request->reference_required);
            if (isset($request->telehealth)) $clinicsQuery->where('telehealth', (bool) $request->telehealth);

            $treatmentsFilterOptions = array_keys($request->only(['is_allergy_diagnostic', 'is_insect_allergy_diagnostic', 'is_sublingual_immunotherapy', 'is_subcutaneous_immunotherapy', 'is_venom_immunotherapy']));
            if (!empty($treatmentsFilterOptions)) {

                $clinicsQuery->where(function ($q) use ($treatmentsFilterOptions) {

                    foreach ($treatmentsFilterOptions as $treatmentsFilterOption) $q->orWhere($treatmentsFilterOption, true);
                });
            }
        }

        /*if (!empty($request->country)) {

            $clinicsQuery->whereHas('country', function ($query) use ($countryCode) {

                $query->where('code', $countryCode);
            });
        }*/

        if ($region || $city || $state) {

            $hasPagination = false;

            if (!empty($region)) {
                if ($shop->name === User::ALK_DK_STORE) {
                    $region = $shop->fadRegions->where('handle', $region)->first();
                    if ($region->parent_id) {
                        $region = $region->parentRegion;
                    }
                    $region = !empty($region) ? $region->name : null;
                }
                $clinicsQuery->where('region', $region);
            }
            if (!empty($city)) {
                $dbCity = $shop->fadCities->where('handle', $city)->first();
                if ($dbCity->parent_id) {
                    $dbCity = $dbCity->parentCity;
                }
                $city = !empty($dbCity) ? $dbCity->name : $city;
                $clinicsQuery->where('city', $city);
            }
            if (!empty($state)) {
                $clinicsQuery->where('state', $state);
            }
        } else {

            if ($hasDistance) {

                $clinicsQuery->selectRaw(
                    'clinics.*, (6371000 * acos(cos(radians(?)) * cos(radians(clinics.latitude)) * cos(radians(clinics.longitude) - radians(?)) + sin(radians(?)) * sin(radians(clinics.latitude)))) AS distance',
                    [$request->latitude, $request->longitude, $request->latitude]
                );

                $clinicsQuery->having('distance', '<=', $request->radius);

                $clinicsQuery->orderBy('distance', $sortOrder);
            }
        }

        if (!empty($request->insurance_companies)) {

            $clinicsQuery->whereHas('insuranceCompanies', function ($query) use ($request) {

                $query->whereIn('name', is_array($request->insurance_companies) ? $request->insurance_companies : [$request->insurance_companies]);
            });
        }

        if (!empty($request->specialist_areas)) {

            $clinicsQuery->whereHas('specialistAreas', function ($query) use ($request) {

                $query->whereIn('name', is_array($request->specialist_areas) ? $request->specialist_areas : [$request->specialist_areas]);
            });
        }

        if (!empty($request->specialist_area_titles)) {

            $clinicsQuery->whereHas('specialistAreaTitles', function ($query) use ($request) {

                $query->whereIn('name', is_array($request->specialist_area_titles) ? $request->specialist_area_titles : [$request->specialist_area_titles]);
            });
        }

        if (!empty($request->other_services)) {

            $clinicsQuery->whereHas('otherServices', function ($query) use ($request) {

                $query->whereIn('name', is_array($request->other_services) ? $request->other_services : [$request->other_services]);
            });
        }

        if (!empty($request->diagnostic_services)) {

            $clinicsQuery->whereHas('diagnosticServices', function ($query) use ($request) {

                $query->whereIn('name', is_array($request->diagnostic_services) ? $request->diagnostic_services : [$request->diagnostic_services]);
            });
        }

        if (!empty($request->clinic_types)) {

            $clinicsQuery->whereHas('clinicTypes', function ($query) use ($request) {

                $query->whereIn('name', is_array($request->clinic_types) ? $request->clinic_types : [$request->clinic_types]);
            });
        }

        if (!empty($request->zipcode)) $clinicsQuery->where('zipcode', $request->zipcode);

        $clinics = $hasPagination ? $clinicsQuery->paginate($perPage)->withQueryString() : $clinicsQuery->get();

        return new ClinicCollection($clinics);
    }

    public function show(Request $request, $handle)
    {
        $shop = $request->user();

        $country = $shop->country;

        if (empty($country)) {
            return response(['message' => 'Please select country for this store'], 422);
        }

        $clinicsQuery = $shop->clinics()
            ->with($this->relations());

        $clinic = $clinicsQuery->where(function ($query) use ($handle) {
            $query->where('doctor_handle', $handle)
                  ->orWhere('clinic_handle', $handle);
        })->first();

        return !empty($clinic) ? new ClinicResource($clinic) : abort(404, 'Clinic / Doctor not found');
    }

    public function filters(Request $request) {

        $shop = $request->user();

        $clinics = $shop->clinics->load($this->relations());

        $specialistAreas = $clinics->pluck('specialistAreas')->flatten()->pluck('name')->unique()->values()->toArray();
        $specialistAreaTitles = $clinics->pluck('specialistAreaTitles')->flatten()->pluck('name')->unique()->values()->toArray();
        $otherServices = $clinics->pluck('otherServices')->flatten()->pluck('name')->unique()->values()->toArray();
        $insuranceCompanies = $clinics->pluck('insuranceCompanies')->flatten()->pluck('name')->unique()->values()->toArray();
        $diagnosticServices = $clinics->pluck('diagnosticServices')->flatten()->pluck('name')->unique()->values()->toArray();
        $clinicTypes = $clinics->pluck('clinicTypes')->flatten()->pluck('name')->unique()->values()->toArray();

        return response([
            'specialist_areas' => $specialistAreas,
            'specialist_area_titles' => $specialistAreaTitles,
            'other_services' => $otherServices,
            'insurance_companies' => $insuranceCompanies,
            'diagnostic_services' => $diagnosticServices,
            'clinic_types' => $clinicTypes
        ]);
    }

    function compressedIndex(Request $request) {
        return $this->index($request);
    }
}
