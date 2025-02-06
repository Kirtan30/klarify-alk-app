<?php

use App\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | FaD Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the fad tool to build.
    |
    */

    getSubdomain(User::ALK_DE_STORE) => [
        'url_prefix' => 'allergologensuche/stadt',
        'search_suffix' => 'Deutschland',
        'schema_name' => 'Allergologinnen und Allergologen in :city',
        'meta_description' => 'Erhalte eine Übersicht von Allergologinnen und Allergologen in deiner Nähe in :city inklusive Kontaktinformationen. Vereinbare gleich einen Termin!',
        'breadcrumb' => [
            'home' => [
                'title' => 'Startseite',
                'url' => 'https://:domain/'
            ],
            'fad_page' => [
                'title' => 'Allergologensuche',
                'url' => 'https://:domain/pages/:fadPageHandle'
            ],
            'fad_city_page' => [
                'title' => 'Allergologinnen und Allergologen in :city',
                'url' => 'https://:domain/apps/pages/:fadCityPagePrefix/:fadCityPageHandle'
            ]
        ]
    ],

    getSubdomain(User::KLARIFY_CH_STORE) => [
        'url_prefix' => 'allergologensuche',
        'search_suffix' => 'Schweiz',
        'page_title' => 'Allergologin/Allergologen in :city finden',
        'meta_description' => 'Unsere Facharztsuche kann dir helfen, eine Facharztpraxis für Allergologie in :city zu finden. Wir geben dir Tipps, welche Fragen du der Ärztin oder dem Arzt stellen solltest.',
    ],

    getSubdomain(User::KLARIFY_AT_STORE) => [
        'url_prefix' => 'facharztsuche',
        'search_suffix' => 'Österreich'
    ]
];
