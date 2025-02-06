<?php

use App\Models\User;

return [
    getSubdomain(User::KLARIFY_SK_STORE) => [
        'url_prefix' => 'vyhladat-alergologa',
        'meta_description' => 'Náš online vyhľadávač vám pomôže nájsť alergológa v :city. Dáme vám aj tipy, na čo sa ho opýtať, keď budete konzultovať vaše príznaky.',
        'search_suffix' => 'Slovensko'
    ]
];
