<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function questions() {
        return $this->hasMany(QuizQuestion::class, 'quiz_id', 'id');
    }

    public function results() {
        return $this->hasMany(QuizResult::class, 'quiz_id', 'id');
    }
}
