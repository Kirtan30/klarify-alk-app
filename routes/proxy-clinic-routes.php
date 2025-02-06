<?php

use App\Http\Controllers\Proxy\ClinicPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [ClinicPageController::class, 'sitemap']);
Route::get('/{clinicHandle}', [ClinicPageController::class, 'page']);
