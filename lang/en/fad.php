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

    getSubdomain(User::KLARIFY_US_STORE) => [
        'url_prefix' => 'find-a-doctor',
        'search_suffix' => 'USA'
    ],

    getSubdomain(User::KLARIFY_CA_STORE) => [
        'url_prefix' => 'find-an-allergist',
        'search_suffix' => 'Canada'
    ],

    getSubdomain(User::ALK_ODACTRA_STORE) => [
        'url_prefix' => 'find-an-allergy-specialist',
        'search_suffix' => 'USA'
    ],

    getSubdomain(User::ALK_RAGWITEK_STORE) => [
        'url_prefix' => 'find-doctor',
        'search_suffix' => 'USA'
    ],

    getSubdomain(User::ALK_GRASTEK_STORE) => [
        'url_prefix' => 'find-doctor',
        'search_suffix' => 'USA'
    ],
];
