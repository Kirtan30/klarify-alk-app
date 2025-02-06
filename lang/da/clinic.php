<?php

use App\Models\User;

return [

    getSubdomain(User::ALK_DK_STORE) => [
        'url_prefix' => 'klinikker',
        'parent_title' => 'Allergilæger i :parentName: Allergitest, diagnose & behandling',
        'is_allergy_specialist' => 'Allergologi',
        'only_private_patients' => 'Private patienter',
        'all_patients' => 'Lovpligtige forsikrede og private patienter',
        'is_doctor' => 'Læge',
        'is_not_doctor' => 'Klinik/universitetsklinik',
        'timestamp-date' => 'Sidste ændring:',
        'is_allergy_diagnostic' => 'Allergidiagnostik for inhalerede allergener',
        'is_insect_allergy_diagnostic' => 'Allergi diagnostiske insekter',
        'is_subcutaneous_immunotherapy' => 'Specifik immunterapi',
        'is_sublingual_immunotherapy' => 'Specifik immunterapi',
        'is_venom_immunotherapy' => 'Specifik immunterapi insektgift',
        'telehealth' => 'Institution',
        'waiting_time' => 'Ventetid',
        'adult' => 'Voksenklinik',
        'children' => 'Børneklinik',
        'meta_description' => 'I denne klinik kan du få hjælp af en allergilæge med allergitest, diagnose og behandling for din pollenallergi. Find ud af, om henvisning er nødvendig.',
        'breadcrumb' => [
            'home' => [
                'title' => 'Forside',
                'url' => 'https://:domain/'
            ],
            'fad_page' => [
                'title' => 'Find allergilaeger',
                'url' => 'https://:domain/pages/:fadPageHandle'
            ],
            'clinic_page' => [
                'title' => ':clinic',
                'url' => 'https://:domain/apps/pages/:clinicPagePrefix/:clinicPageHandle'
            ]
        ]
    ]
];
