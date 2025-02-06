<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'country_id' => 'required',

            'languages' => 'nullable|array',
            'languages.*.id' => 'required',
            'languages.*.pivot.fad_page' => 'required',

            'default_language' => 'required',
        ]);

        $shop = $request->user();

        DB::transaction(function () use ($request, $shop) {

            $shop->public_domain = $request->input('public_domain') ?: null;
            $shop->country_id = strtolower($request->input('country_id'));

            $storeLanguages = [];
            foreach ($request->input('languages') ?: [] as $language) {
                $storeLanguages[$language['id']] = [
                    'user_id' => $shop->id,
                    'default' => data_get($request, 'default_language.id') === data_get($language, 'id'),
                    'fad_page' => data_get($language, 'pivot.fad_page'),
                    'fad_static_page' => data_get($language, 'pivot.fad_static_page') ?: null,
                    'fad_region_page' => data_get($language, 'pivot.fad_region_page') ?: null,
                    'fad_region_static_page' => data_get($language, 'pivot.fad_region_static_page') ?: null,
                    'pollen_page' => data_get($language, 'pivot.pollen_page') ?: null,
                    'pollen_static_page' => data_get($language, 'pivot.pollen_static_page') ?: null,
                    'pollen_region_page' => data_get($language, 'pivot.pollen_region_page') ?: null,
                    'pollen_region_static_page' => data_get($language, 'pivot.pollen_region_static_page') ?: null,
                    'clinic_page' => data_get($language, 'pivot.clinic_page') ?: null,
                    'clinic_index_page' => data_get($language, 'pivot.clinic_index_page') ?: null,
                    'lexicon_page' => data_get($language, 'pivot.lexicon_page') ?: null,
                    'fad_iframe_page' => data_get($language, 'pivot.fad_iframe_page') ?: null,
                ];
            }

            $shop->languages()->sync($storeLanguages);

            $settings = $shop->settings->keyBy('key');
            $updateSettings = [];
            $createSettings = [];

            foreach ($request->input('settings') ?: [] as $key => $value) {
                $tempSetting = [
                    'user_id' => $shop->id,
                    'key' => $key,
                    'value' => $value,
                    'updated_at' => now(),
                ];

                if (empty(data_get($settings, $key))) {
                    $tempSetting['created_at'] = now();
                    $createSettings[] = $tempSetting;
                } else {
                    $tempSetting['id'] = data_get($settings, "$key.id");
                    $updateSettings[] = $tempSetting;
                }
            }

            if (!empty($createSettings)) {
                Setting::insert($createSettings);
            }

            if (!empty($updateSettings)) {
                bulkUpdate('settings', $updateSettings);
            }

            if ($shop->isDirty()) {
                $shop->save();

                if ($shop->country_id !== $request->input('country_id')) {
                    Artisan::call('clinic:sync', ['--shop' => $shop->name]);
                }
            }
        });

        return response(['message' => 'Settings Updated Successfully'], 200);
    }

    public function show(Request $request)
    {
        $shop = $request->user();
        $shop->languages;
        $settings = $shop->settings->pluck('value', 'key');
        $shop->default_language = $shop->languages->where('pivot.default', true)->first();

        $countries = Country::get();

        return response(['shop' => $shop, 'settings' => $settings, 'countries' => $countries], 200);
    }

    public function clearCache(Request $request)
    {
        $request->validate([
            'type' => 'required'
        ]);
        $shop = $request->user();
        $domain = $shop->name;

        if ($request->input('type') === 'proxy') {
            $pages = [
                'fad_page',
                'fad_static_page',
                'fad_region_page',
                'fad_region_static_page',
                'pollen_page',
                'pollen_static_page',
                'pollen_region_page',
                'pollen_region_static_page',
                'clinic_page',
                'clinic_index_page',
                'lexicon_page',
                'fad_iframe_page'
            ];

            foreach ($shop->languages->toArray() as $language) {
                $dbPages = data_get($language, 'pivot');

                foreach ($pages as $page) {
                    $handle = data_get($dbPages, $page);
                    $url = "https://$domain/pages/$handle";

                    Cache::forget($url);
                }
            }
        }

        return response(['message' => 'Cache removed successfully']);
    }
}
