<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergyTest extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function questions() {
        return $this->hasMany(AllergyTestQuestion::class, 'allergy_test_id', 'id')
            ->where([
                ['parent_id', '=', null],
                ['related_option_id', '=', null]
            ])
            ->orderBy('order', 'asc');
    }

    public function results() {
        return $this->hasMany(AllergyTestResult::class, 'allergy_test_id', 'id');
    }
}
