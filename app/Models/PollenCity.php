<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollenCity extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'has_static_content' => 'boolean',
        'is_popular' => 'boolean',
        'variables' => 'array'
    ];

    public function pollenState()
    {
        return $this->belongsTo(PollenState::class);
    }

    public function pollenRegion()
    {
        return $this->belongsTo(PollenRegion::class);
    }

    public function pollenPageContent() {
        return $this->belongsTo(PollenPageContent::class);
    }

    public function pollenLanguage() {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function pollenParent() {
        return $this->belongsTo(PollenCity::class, 'parent_id', 'id');
    }

    public function childCities() {
        return $this->hasMany(PollenCity::class, 'parent_id', 'id');
    }
}
