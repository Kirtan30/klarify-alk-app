<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergyTestSwedish extends Model
{
    use HasFactory;

    protected $table = 'allergy_test_swedish';

    protected $guarded = ['id'];

    protected $casts = [
        'answers' => 'array'
    ];
}
