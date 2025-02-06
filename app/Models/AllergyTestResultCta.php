<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergyTestResultCta extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'target_blank' => 'boolean',
    ];
}
