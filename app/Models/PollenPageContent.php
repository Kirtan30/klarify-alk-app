<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollenPageContent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'variables' => 'array'
    ];
}
