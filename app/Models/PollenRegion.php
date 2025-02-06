<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollenRegion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'has_static_content' => 'boolean',
        'is_popular' => 'boolean',
        'variables' => 'array'
    ];

    public function pollenCities()
    {
        return $this->hasMany(PollenCity::class);
    }

    public function pollenPageContent() {
        return $this->belongsTo(PollenPageContent::class);
    }

    public function pollenLanguage() {
        return $this->belongsTo(Language::class, 'language_id', 'id');
    }

    public function pollenParent() {
        return $this->belongsTo(PollenRegion::class, 'parent_id', 'id');
    }
}
