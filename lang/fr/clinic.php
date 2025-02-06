<?php

use App\Models\User;

return [

    getSubdomain(User::KLARIFY_CH_STORE) => [
        'url_prefix' => 'allergologue',
        'meta_description' => 'Věříme, že i s alergiemi lze žít pohodový život. Získejte informace podložené výzkumem, reálné tipy a praktická řešení, která vám pomohou snížit dopad alergií na váš život.',
        'adult' => 'Clinique pour adultes',
        'children' => 'Clinique pour enfants',
        'telehealth' => 'Institution',
        'waiting_time' => "temps d'attente",
    ],
];
