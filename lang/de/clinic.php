<?php

use App\Models\User;

return [

    getSubdomain(User::ALK_DE_STORE) => [
        'url_prefix' => 'facharztpraxen',
        'is_allergy_specialist' => ':specialistAreaTitles, Allergologie',
        'only_private_patients' => 'Privat',
        'all_patients' => 'Gesetzlich Versicherte und Privat',
        'is_doctor' => 'Arzt / Ärztin',
        'is_not_doctor' => 'Klinik / Uniklinik',
        'timestamp-date' => 'Letztes Update:',
        'is_allergy_diagnostic' => 'Allergiediagnostik inhalative Allergene',
        'is_insect_allergy_diagnostic' => 'Allergiediagnostik Insekten',
        'is_sublingual_immunotherapy' => 'Allergie-Immuntherapie sublingual (Tropfen/Tablette)',
        'is_subcutaneous_immunotherapy' => 'Allergie-Immuntherapie subkutan (Spritze)',
        'is_venom_immunotherapy' => 'Allergie-Immuntherapie Insektengift',
        'telehealth' => 'Institution',
        'waiting_time' => 'Wartezeit',
    ],

    getSubdomain(User::KLARIFY_AT_STORE) => [
        'url_prefix' => 'facharztpraxen',
        'adult' => 'Adult Clinic',
        'children' => 'Children Clinic',
        'pediatric' => 'Pediatric Clinic',
        'is_allergy_diagnostic' => 'Allergie-Immuntherapie mit: Tabletten',
        'is_insect_allergy_diagnostic' => 'Insektengift-Immuntherapie',
        'is_subcutaneous_immunotherapy' => 'Allergie-Immuntherapie mit: Tropfen',
        'is_sublingual_immunotherapy' => 'Allergie-Immuntherapie mit: Spritzen',
        'is_venom_immunotherapy' => 'Venom Therapie',
        'telehealth' => 'Institution',
        'waiting_time' => 'Wartezeit',
    ],

    getSubdomain(User::KLARIFY_CH_STORE) => [
        'url_prefix' => 'facharztpraxen',
        'meta_description' => 'Bereit, um das Beste aus jedem Tag zu machen? Erhalte täglich standortbasierte Pollendaten. Tracke deine lokale Pollenbelastung für Bäume, Gräser und Kräuter.',
        'adult' => 'Klinik für Erwachsene',
        'children' => 'Kinderklinik',
        'is_allergy_diagnostic' => 'Allergie-Immuntherapie mit: Tabletten',
        'is_insect_allergy_diagnostic' => 'Insektengift-Immuntherapie',
        'is_subcutaneous_immunotherapy' => 'Allergie-Immuntherapie mit: Tropfen',
        'is_sublingual_immunotherapy' => 'Allergie-Immuntherapie mit: Spritzen',
        'is_venom_immunotherapy' => 'Venom Therapie',
        'telehealth' => 'Institution',
        'waiting_time' => 'Wartezeit',
    ]
];
