<?php

use App\Models\User;

return [
    getSubdomain(User::KLARIFY_CZ_STORE) => [
        'url_prefix' => 'vyhledat-alergologa',
        'meta_description' => 'Náš online vyhledávač vám pomůže najít alergologa ve městě :city. Dáme vám také tipy, nač se ho zeptat při konzultaci svých příznaků.',
        'search_suffix' => 'Česko'
    ]
];
