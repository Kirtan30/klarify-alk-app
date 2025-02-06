<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    // Setting keys
    const GOOGLE_API_KEY = 'google_api_key';
    const ENABLED_PROXY_CACHE = 'enabled_proxy_cache';

    /**
     * Get the setting's value.
     */
    protected function value(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => isJSON($value) ? json_decode($value, true) : $value,
            set: fn ($value) => is_array($value) ? json_encode($value, true) : $value,
        );
    }
}
