<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AllergyTestSwedish;
use App\Models\User;
use Illuminate\Http\Request;

class AllergyTestSwedishController extends Controller
{
    public function store(Request $request) {

        $shop = $request->user();

        $request->validate([
            'answers' => 'required|array',
            'resultType' => 'required'
        ]);

        try {
            $result = AllergyTestSwedish::create([
                'uuid' => str()->uuid(),
                'user_id' => $shop->id,
                'answers' => data_get($request, 'answers') ?: [],
                'result_type' => data_get($request, 'resultType'),
                'vas_score' => data_get($request, 'vasScore') ?: 0
            ]);

            $domain = $shop->public_domain ?: $shop->name;

            $link = match ($shop->name) {
                User::ALK_NO_STORE => "https://$domain/apps/pages/allergy-test/$result->uuid/download",
                User::ALK_DK_STORE => "https://$domain/apps/pages/allergy-test/$result->id/download",
                default => "https://$domain/apps/pages/allergy-test-swedish/$result->uuid/download",
            };

            return response(['link' => $link],200);
        } catch (\Exception $e) {

            return response(['message' => $e->getMessage()], 500);
        }
    }
}
