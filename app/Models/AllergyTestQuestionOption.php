<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllergyTestQuestionOption extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function relatedQuestions() {
        return $this->hasMany(AllergyTestQuestion::class, 'related_option_id', 'id')->orderBy('order', 'asc');
    }
}
