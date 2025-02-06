<?php

use App\Models\User;

return [

    getSubdomain(User::KLARIFY_SK_STORE) => [
        'url_prefix' => 'lekara',
        'meta_description' => 'Ste pripravení využiť každý deň naplno? Získajte denne informácie o peli na základe miesta, kde sa nachádzate. Sledujte množstvo peľu stromov, burín a tráv.',
        'adult' => 'Dospelých Klinika',
        'children' => 'Detská klinika',
        'pediatric' => 'Detská klinika',
        'telehealth' => 'Inštitúcia',
        'waiting_time' => 'čakacia doba',
    ]
];
