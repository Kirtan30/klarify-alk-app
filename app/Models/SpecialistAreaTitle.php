<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialistAreaTitle extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'translations' => 'array',
    ];

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_specialist_area_title', 'specialist_area_title_id', 'clinic_id');
    }
}
