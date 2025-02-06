<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SelfTestResult;
use Illuminate\Http\Request;

class SelfTestResultController extends Controller
{
    public function store(Request $request) {

        $shop = $request->user();

        $request->validate([
            'answers' => 'required|array',
            'resultText' => 'required',
            'score' => 'required'
        ]);

        try {
            $result = SelfTestResult::create([
                'uuid' => str()->uuid(),
                'user_id' => $shop->id,
                'answers' => $request->input('answers'),
                'result' => $request->input('resultText'),
                'score' => $request->input('score')
            ]);

            $domain = $shop->public_domain ?: $shop->name;
            return response(['link' => "https://$domain/apps/pages/self-test/results/$result->uuid/download"],200);
        } catch (\Exception $e) {

            return response(['message' => $e->getMessage()], 500);
        }

    }
}
