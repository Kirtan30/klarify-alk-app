<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicWeekDayOpeningHour extends Model
{
    use HasFactory;

    protected $table = 'clinic_week_day_opening_hours';

    protected $guarded = [];

    public $timestamps = false;

    protected $casts = [
        'opening_second' => 'integer',
        'closing_second' => 'integer',
        'opening_time' => 'string',
        'closing_time' => 'string',
        'optional' => 'boolean'
    ];
}
