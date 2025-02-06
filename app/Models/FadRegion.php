<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FadRegion extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'has_static_content' => 'boolean',
        'is_popular' => 'boolean',
        'enabled' => 'boolean',
        'variables' => 'array'
    ];

    public function fadCities()
    {
        return $this->hasMany(FadCity::class);
    }

    public function fadPageContent() {
        return $this->belongsTo(FadPageContent::class);
    }

    public function parentRegion() {
        return $this->belongsTo(FadRegion::class, 'parent_id', 'id');
    }
}
