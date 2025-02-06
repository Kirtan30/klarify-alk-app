<?php

use App\Models\User;

return [

    getSubdomain(User::ALK_DK_STORE) => [
        'url_prefix' => 'allergilaeger',
        'parent_title' => 'Find en allergilæge eller allergiklinik nær dig',
        'title' => 'Allergilæger i :regionName: Allergitest, diagnose & behandling',
        'meta_description' => 'Brug vores søgefunktion, og find en allergiklinik eller allergilæge i :regionName, som kan hjælpe med din pollenallergi.',
        'breadcrumb' => [
            'home' => [
                'title' => 'Forside',
                'url' => 'https://:domain/'
            ],
            'fad_page' => [
                'title' => 'Find allergilaeger',
                'url' => 'https://:domain/pages/:fadPageHandle'
            ],
            'fad_region_page' => [
                'title' => ':region',
                'url' => 'https://:domain/apps/pages/:fadRegionPagePrefix/:fadRegionPageHandle'
            ]
        ]
    ]
];
