<?php

use App\Models\User;

return [

    getSubdomain(User::ALK_DK_STORE) => [
        'url_prefix' => 'pollental',
        'meta_description' => "Vær opdateret på de nyeste pollental. Når du følger Pollental, kan du bedre planlægge din dag. Se her",
        'breadcrumb' => [
            'home' => [
                'title' => 'Forside',
                'url' => 'https://:domain/'
            ],
            'pollen_page' => [
                'title' => 'Pollenflug',
                'url' => 'https://:domain/pages/:pollenPageHandle'
            ],
            'pollen_region_page' => [
                'title' => ':region',
                'url' => 'https://:domain/apps/pages/:pollenRegionPagePrefix/:pollenRegionPageHandle'
            ]
        ]
    ]
];
