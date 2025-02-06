<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollAnswer extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function poll() {
        return $this->belongsTo(Poll::class);
    }

    public function responses() {
        return $this->HasMany(PollResponse::class);
    }
}
