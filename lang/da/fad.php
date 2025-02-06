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

    getSubdomain(User::ALK_DK_STORE) => [
        'url_prefix' => 'allergilaeger',
        'search_suffix' => 'Danmark',
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
                'title' => 'Region :region',
                'url' => 'https://:domain/apps/pages/:fadRegionPagePrefix/:fadRegionPageHandle'
            ]
        ]
    ]
];
