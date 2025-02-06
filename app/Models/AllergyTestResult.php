<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergyTestResult extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'percentage_range' => 'array',
        'result_data' => 'array',
        'default' => 'boolean',
    ];

    public function result_ctas() {
        return $this->hasMany(AllergyTestResultCta::class, 'allergy_test_result_id', 'id');
    }
}
