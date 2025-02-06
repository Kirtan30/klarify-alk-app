<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnosticService extends Model
{
    use HasFactory;

    protected $guarded = [];
    public $timestamps = false;

    protected $casts = [
        'translations' => 'array',
    ];

    public function clinics()
    {
        return $this->belongsToMany(Clinic::class, 'clinic_diagnostic_service', 'diagnostic_service_id', 'clinic_id');
    }
}
