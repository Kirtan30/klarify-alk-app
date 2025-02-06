<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PollenCalendar;
use Illuminate\Http\Request;

class PollenCalendarController extends Controller
{
    public function allergens(Request $request)
    {
        $shop = $request->user();

        $pollenCalendar = PollenCalendar::where('user_id', $shop->id)->where('type', 'allergens')->firstOrFail();

        return response(['data' => $pollenCalendar]);
    }

    public function types(Request $request)
    {
        $shop = $request->user();

        $pollenCalendar = PollenCalendar::where('user_id', $shop->id)->where('type', 'types')->firstOrFail();

        return response(['data' => $pollenCalendar]);
    }
}
