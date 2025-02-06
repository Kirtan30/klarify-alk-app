<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Pollencontroller extends Controller
{
    public function sync(Request $request) {

        $request->validate([
            'calendarFile' => 'required|file',
            'language' => 'required'
        ]);

        $shop = $request->user();
        $language = $request->input('language');
        $pollenData = $request->file('calendarFile')->get();

        try {
            $namespace = "pollen-$language";

            $data = [
                [
                    'key' => 'calendar',
                    'value' => json_encode(json_decode($pollenData, true)),
                ]
            ];

            foreach ($data as $datum) {
                $metafieldData = [
                    "metafield" => [
                        "namespace" => $namespace,
                        "key" => data_get($datum, 'key'),
                        "value" => data_get($datum, 'value', []),
                        "type" => "json"
                    ]
                ];

                $shop->api()->rest('POST', '/admin/metafields.json', $metafieldData);
                return response(['message' => 'Metafield synced successfully'], 200);
            }
        }
        catch (\Exception $e) {
            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function pollenLanguages(Request $request) {
        $shop = $request->user();
        if (empty($shop)) return;

        $shopLanguages = $shop->languages;
        $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
        $defaultLanguageId = data_get($defaultLanguage, 'id');

        return response(['pollenLanguages' => $shopLanguages, 'defaultLanguageId' => $defaultLanguageId]);
    }
}
