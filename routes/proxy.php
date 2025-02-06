<?php

use App\Http\Controllers\Proxy\AllergyTestGermanController;
use App\Http\Controllers\Proxy\AllergyTestSwedishController;
use App\Http\Controllers\Proxy\ProxyController;
use App\Http\Controllers\Proxy\SelfTestResultController;
use Illuminate\Support\Facades\Route;

// NL Market
Route::group(['prefix' => 'clinics'], function () {

    Route::group(['prefix' => 'cities'], function () {
        require 'proxy-fad-city-routes.php';
    });

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'allergiespecialisten-testen'], function () {
    require 'proxy-fad-city-routes.php';
});

Route::group(['prefix' => 'allergieklinieken-overzicht'], function () {
    require 'proxy-clinic-index-routes.php';
});

Route::group(['prefix' => 'hooikoorts-pollenradar'], function () {

    require 'proxy-pollen-city-routes.php';
});

// NO Market
Route::group(['prefix' => 'klinikker'], function () {

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'allergi-klinikker'], function () {

    Route::group(['prefix' => 'fylke'], function () {

        require 'proxy-fad-region-routes.php';
    });
});

Route::group(['prefix' => 'pollenvarsel'], function () {

    Route::group(['prefix' => 'by'], function () {

        require 'proxy-pollen-city-routes.php';
    });

    Route::group(['prefix' => 'fylke'], function () {

        require 'proxy-pollen-region-routes.php';
    });
});

Route::group(['prefix' => 'klinikker-fylke-indeks'], function () {
    require 'proxy-clinic-index-routes.php';
});

// DE Market
Route::group(['prefix' => 'facharztpraxen'], function () {

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'allergologensuche'], function () {

    Route::group(['prefix' => 'stadt'], function () {
        require 'proxy-fad-city-routes.php';
    });
});

Route::group(['prefix' => 'pollenflug-vorhersage'], function () {

    Route::group(['prefix' => 'stadt'], function () {
        require 'proxy-pollen-city-routes.php';
    });
});

Route::group(['prefix' => 'ubersicht-facharztpraxen'], function () {

    require 'proxy-clinic-index-routes.php';
});

Route::group(['prefix' => 'allergie-lexikon'], function () {

    require 'proxy-lexicon-routes.php';
});

// US Market
Route::group(['prefix' => 'doctors'], function () {

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'find-a-doctor'], function () {

    require 'proxy-fad-city-routes.php';
});

Route::group(['prefix' => 'pollen-forecast'], function () {

    require 'proxy-pollen-city-routes.php';
});

// AT Market
Route::group(['prefix' => 'facharztsuche'], function () {

    require 'proxy-fad-city-routes.php';
});

Route::group(['prefix' => 'ubersicht-facharztpraxen'], function () {

    require 'proxy-clinic-index-routes.php';
});

Route::group(['prefix' => 'pollenflugvorhersage'], function () {

    require 'proxy-pollen-city-routes.php';
});

// CZ Market
Route::group(['prefix' => 'lekare'], function () {

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'vyhledat-alergologa'], function () {

    require 'proxy-fad-city-routes.php';
});

Route::group(['prefix' => 'mnozstvi-pylu-dnes'], function () {

    require 'proxy-pollen-city-routes.php';
});

Route::group(['prefix' => 'prehled-odbornych-praxi'], function () {

    require 'proxy-clinic-index-routes.php';
});

// SK Market
Route::group(['prefix' => 'lekara'], function () {

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'vyhladat-alergologa'], function () {

    require 'proxy-fad-city-routes.php';
});

Route::group(['prefix' => 'mnozstvo-pelu-dnes'], function () {

    require 'proxy-pollen-city-routes.php';
});

Route::group(['prefix' => 'prehlad-odbornych-praxi'], function () {

    require 'proxy-clinic-index-routes.php';
});

// CH Market
Route::group(['prefix' => 'facharztpraxen'], function () { // Clinic pages for CH-DE

    require 'proxy-clinic-routes.php';
});
Route::group(['prefix' => 'allergologue'], function () { // Clinic pages for CH-FR

    require 'proxy-clinic-routes.php';
});
Route::group(['prefix' => 'allergologo'], function () { // Clinic pages for CH-IT

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'allergologensuche'], function () { // FaD city pages for CH-DE

    require 'proxy-fad-city-routes.php';
});
Route::group(['prefix' => 'trouver-un-allergologue'], function () { // FaD city pages for CH-FR

    require 'proxy-fad-city-routes.php';
});
Route::group(['prefix' => 'trova-un-allergologo'], function () { // FaD city pages for CH-IT

    require 'proxy-fad-city-routes.php';
});
Route::group(['prefix' => 'ubersicht-facharztpraxen'], function () { // Clinic Index page for CH-DE

    require 'proxy-clinic-index-routes.php';
});

Route::group(['prefix' => 'apercu-cabinets-medicaux-specialises'], function () { // Clinic Index page for CH-FR

    require 'proxy-clinic-index-routes.php';
});

Route::group(['prefix' => 'panoramica-studi-medici-specialistici'], function () { // Clinic Index page for CH-IT

    require 'proxy-clinic-index-routes.php';
});

Route::group(['prefix' => 'pollenbelastung-heute'], function () { // Pollen city pages for CH-DE

    require 'proxy-pollen-city-routes.php';
});
Route::group(['prefix' => 'charge-pollinique-aujourdhui'], function () { // Pollen city pages for CH-FR

    require 'proxy-pollen-city-routes.php';
});
Route::group(['prefix' => 'la-concentrazione-pollinica-oggi'], function () { // Pollen city pages for CH-IT

    require 'proxy-pollen-city-routes.php';
});

// CA Market
Route::group(['prefix' => 'find-an-allergist'], function () {

    require 'proxy-fad-city-routes.php';
});
Route::group(['prefix' => 'allergists'], function () {

    require 'proxy-clinic-routes.php';
});

Route::group(['prefix' => 'pollen-forecast'], function () {

    require 'proxy-pollen-city-routes.php';
});

Route::group(['prefix' => 'comptage-du-pollen-aujourdhui'], function () {

    require 'proxy-pollen-city-routes.php';
});

// Ragwitek and Grastek Market
Route::group(['prefix' => 'find-doctor'], function () {

    require 'proxy-fad-city-routes.php';
});

// DK Market
Route::group(['prefix' => 'allergilaeger'], function () {

    require 'proxy-fad-region-routes.php';
});
Route::group(['prefix' => 'klinikker'], function () {

    require 'proxy-clinic-routes.php';
});
Route::group(['prefix' => 'pollental'], function () {

    Route::group(['prefix' => 'by'], function () {

        require 'proxy-pollen-city-routes.php';
    });

    require 'proxy-pollen-region-routes.php';
});
Route::group(['prefix' => 'liste-allergiklinikker'], function () {

    require 'proxy-clinic-index-routes.php';
});

Route::get('self-test/results/{result:uuid}/download', [SelfTestResultController::class, 'download']);
Route::get('allergy-test/{result}/download', [AllergyTestSwedishController::class, 'download']);
Route::get('allergy-test-german/{result:uuid}/download', [AllergyTestGermanController::class, 'download']);
Route::get('allergy-test-swedish/{result}/download', [AllergyTestSwedishController::class, 'download']);

Route::any('/{any?}', [ProxyController::class, 'notFoundPage'])->where('any', '.*');
