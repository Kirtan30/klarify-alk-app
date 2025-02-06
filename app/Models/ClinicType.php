<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicType extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $table = 'clinic_types';
}
