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
        'url_prefix' => 'charge-pollinique-aujourdhui',
        'page_title' => 'Pollen à :cityName | Consulte les prévisions pollen du jour de ta ville',
        'meta_description' => 'Consulte les prévisions polliniques du jour à :cityName. Sers-toi des prévisions polliniques pour mieux gérer tes symptômes du rhume des foins!',
        'banner_title' => 'Pollinisation :cityName',
        'banner_description' => '<p>Tu veux profiter pleinement de chaque jour?<br>Consulte ici chaque jour la pollinisation à :cityName – pour les pollens d’arbres, de plantes herbacées et de graminées.</p>',
        'search_suffix' => 'Suisse',
    ],

    getSubdomain(User::KLARIFY_CA_STORE) => [
        'url_prefix' => 'comptage-du-pollen-aujourdhui',
        'page_title' => 'Niveau de pollen à :city | Consultez les prévisions polliniques quotidiennes pour votre ville',
        'meta_description' => "Vérifiez le niveau de pollen à :city aujourd'hui. Découvrez comment les prévisions polliniques peuvent vous aider à gérer vos symptômes du rhume des foins",
        'banner_title' => 'Niveau de pollen dans la :city',
        'banner_description' => '<p>Prêt à tirer le meilleur parti de chaque jour?<br>Revenez ici tous les jours pour consulter le niveau de pollen actuel à :city, pour les arbres, les mauvaises herbes et les graminées.</p>',
        'search_suffix' => 'Canada'
    ],

];
