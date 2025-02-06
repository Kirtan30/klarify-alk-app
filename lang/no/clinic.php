<?php

use App\Models\User;

return [
    getSubdomain(User::ALK_NO_STORE) => [
        'url_prefix' => 'klinikker',
        'adult' => 'Voksenklinikk',
        'children' => 'Barneklinikk',
        'timestamp-date' => 'Sist oppdatert'
    ]
];
