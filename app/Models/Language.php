<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function shops()
    {
        return $this->belongsToMany(User::class, 'user_language', 'language_id', 'user_id')
            ->withPivot([
                'id',
                'fad_page', 'fad_static_page', 'fad_region_page', 'fad_region_static_page',
                'pollen_page', 'pollen_static_page', 'pollen_region_page', 'pollen_region_static_page',
                'clinic_page', 'clinic_index_page',
                'lexicon_page', 'fad_iframe_page', 'default'
            ])->withTimestamps();
    }
}
