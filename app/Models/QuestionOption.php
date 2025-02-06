<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'correct' => 'boolean',
    ];

    public function question() {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }
}
