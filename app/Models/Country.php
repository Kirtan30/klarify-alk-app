<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function clinics()
    {
        return $this->hasMany(Clinic::class);
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'country_language', 'country_id', 'language_id')->withTimestamps();
    }

    public function defaultLanguage()
    {
        return $this->belongsToMany(Language::class, 'country_language', 'country_id', 'language_id')->where('default', true)->first();
    }

    public function shops()
    {
        return $this->hasMany(User::class, 'country_id', 'id');
    }
}
