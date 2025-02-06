<?php

namespace App\Traits;

use App\Models\Setting;

trait SettingTrait
{
    public function getGoogleApiKey($settings) {
        return $this->getValue($settings, Setting::GOOGLE_API_KEY);
    }

    public function enabledProxyCache($settings) {
        return $this->getValue($settings, Setting::ENABLED_PROXY_CACHE);
    }

    public function getValue($settings, $key) {
        return data_get($settings, "$key.value") ?: $settings->where('key', $key)->first()?->value;
    }
}
