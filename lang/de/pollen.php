<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Common Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the common tool to build.
    |
    */

    getSubdomain(User::KLARIFY_CH_STORE) => [
        'url_prefix' => 'pollenbelastung-heute',
        'page_title' => 'Pollenflug in :cityName | Erhalte die heutige Pollenvorhersage deiner Stadt',
        'meta_description' => 'Beobachte den heutigen Pollenflug in :cityName. Nutze die Pollenvorhersage, um mit deinen Heuschnupfen-Symptomen besser zurechtzukommen!',
        'banner_title' => 'Pollenflug :cityName',
        'banner_description' => '<p>Bereit, das Beste aus jedem Tag zu machen?<br>Erhalte hier jeden Tag aktuelle Informationen zum Pollenflug in :cityName – für Baum-, Kräuter- und Gräserpollen.</p>',
        'search_suffix' => 'Schweiz',
    ],

    getSubdomain(User::KLARIFY_AT_STORE) => [
        'url_prefix' => 'pollenflugvorhersage',
        'search_suffix' => 'Austria'
    ]

];
