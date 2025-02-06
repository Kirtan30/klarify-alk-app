<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCompany extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_insurance_company', 'insurance_company_id', 'clinic_id');
    }
}
