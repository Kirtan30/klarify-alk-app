<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistArea extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'translations' => 'array',
    ];

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_specialist_area', 'specialist_area_id', 'clinic_id');
    }
}
