<?php

namespace App\Http\Controllers;

use App\Models\PollenCalendar;
use Illuminate\Http\Request;

class PollenCalendarController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'calendarFile' => 'required|file',
            'language' => 'required',
            'type' => 'required'
        ]);

        $shop = $request->user();
        $language = $request->input('language');
        $pollenData = $request->file('calendarFile')->get();

        $pollenCalendar = PollenCalendar::updateOrCreate([
            'user_id' => $shop->id,
            'type' => str($request->input('type'))->lower(),
            'key' => "pollen-$language"
        ], [
            'data' => json_decode($pollenData, true),
        ]);

        return response(['pollenCalendar' => $pollenCalendar]);
    }
}
