<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicWeekDay extends Model
{
    use HasFactory;

    protected $table = 'clinic_week_day';

    protected $guarded = [];

    public $timestamps = false;

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function weekDay()
    {
        return $this->belongsTo(WeekDay::class);
    }

    public function clinicWeekDayOpeningHours()
    {
        return $this->hasMany(ClinicWeekDayOpeningHour::class, 'clinic_week_day_id', 'id');
    }
}
