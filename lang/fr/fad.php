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

    getSubdomain(User::KLARIFY_CH_STORE) => [
        'url_prefix' => 'trouver-un-allergologue',
        'search_suffix' => 'Suisse',
        'page_title' => 'Trouver un/une allergologue à :city',
        'meta_description' => 'Notre outil de recherche de spécialistes t’aide à trouver un cabinet d’allergologie à :city. Nous te donnons des conseils sur les questions à poser au médecin.',
    ]

];
