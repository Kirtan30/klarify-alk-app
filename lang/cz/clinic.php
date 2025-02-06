<?php

use App\Models\User;

return [
    getSubdomain(User::KLARIFY_CZ_STORE) => [
        'url_prefix' => 'lekare',
        'meta_description' => 'Věříme, že i s alergiemi lze žít pohodový život. Získejte informace podložené výzkumem, reálné tipy a praktická řešení, která vám pomohou snížit dopad alergií na váš život.',
        'adult' => 'Dospělé klinika',
        'children' => 'Dětská klinika',
        'pediatric' => 'Pediatric Clinic',
        'is_allergy_diagnostic' => 'Allergie-Immuntherapie mit: Tabletten',
        'is_insect_allergy_diagnostic' => 'Insektengift-Immuntherapie',
        'is_subcutaneous_immunotherapy' => 'Allergie-Immuntherapie mit: Tropfen',
        'is_sublingual_immunotherapy' => 'Allergie-Immuntherapie mit: Spritzen',
        'is_venom_immunotherapy' => 'Venom Therapie',
        'telehealth' => 'Instituce',
        'waiting_time' => 'čekací doba',
    ]
];
