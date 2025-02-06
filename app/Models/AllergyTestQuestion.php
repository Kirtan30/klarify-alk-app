<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergyTestQuestion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'question_data' => 'array',
        'has_redirect_result' => 'boolean',
        'has_redirect_question' => 'boolean',
        'redirect_result_data' => 'array',
        'redirect_question_data' => 'array',
    ];

    public function options() {
        return $this->hasMany(AllergyTestQuestionOption::class, 'allergy_test_question_id', 'id')->orderBy('order', 'asc');
    }

    public function subQuestions() {
        return $this->hasMany(AllergyTestQuestion::class, 'parent_id', 'id')->orderBy('order', 'asc');
    }
}
