<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AllergyTestGerman;
use Illuminate\Http\Request;

class AllergyTestGermanController extends Controller
{
    public function store(Request $request) {

        $shop = $request->user();

        $request->validate([
            'answers' => 'required|array',
            'resultType' => 'required'
        ]);

        try {
            $result = AllergyTestGerman::create([
                'uuid' => str()->uuid(),
                'user_id' => $shop->id,
                'answers' => $request->input('answers'),
                'result_type' => $request->input('resultType'),
            ]);

            $domain = $shop->public_domain ?: $shop->name;
            return response(['link' => "https://$domain/apps/pages/allergy-test-german/$result->uuid/download"],200);
        } catch (\Exception $e) {

            return response(['message' => $e->getMessage()], 500);
        }
    }
}
