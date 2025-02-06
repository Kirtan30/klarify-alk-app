<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FadCity extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'has_static_content' => 'boolean',
        'is_popular' => 'boolean',
        'enabled' => 'boolean',
        'variables' => 'array'
    ];

    public function fadState()
    {
        return $this->belongsTo(FadState::class);
    }

    public function fadRegion()
    {
        return $this->belongsTo(FadRegion::class);
    }

    public function fadPageContent() {
        return $this->belongsTo(FadPageContent::class);
    }

    public function parentCity() {
        return $this->belongsTo(FadCity::class, 'parent_id', 'id');
    }

    public function childCities() {
        return $this->hasMany(FadCity::class, 'parent_id', 'id');
    }
}
