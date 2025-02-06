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

    'ch' => [
        'url_prefix' => '',
        'page_title' => '',
        'meta_description' => '',
        'banner_title' => '',
        'banner_description' => '',
    ],

    getSubdomain(User::KLARIFY_CA_STORE) => [
        'url_prefix' => 'pollen-forecast',
        'page_title' => 'Pollen count :city | See the daily pollen forecast for your city',
        'meta_description' => 'Check the pollen count in :city today. Discover how the pollen forecast can help you manage your hay fever symptoms',
        'banner_title' => 'Pollen Count :city',
        'banner_description' => '<p>Ready to make the most of every day?<br>Check back here daily for the latest pollen count in :city – for trees, weeds and grasses.</p>',
        'search_suffix' => 'Canada'
    ],

    getSubdomain(User::KLARIFY_US_STORE) => [
        'url_prefix' => 'pollen-forecast',
        'search_suffix' => 'United States'
    ],

];
