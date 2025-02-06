<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poll extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function answers() {
        return $this->hasMany(PollAnswer::class, 'poll_id', 'id');
    }

    public function responses() {
        return $this->hasMany(PollResponse::class, 'poll_id', 'id');
    }
}
