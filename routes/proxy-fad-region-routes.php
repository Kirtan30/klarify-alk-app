<?php

use App\Http\Controllers\Proxy\RegionPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [RegionPageController::class, 'sitemap']);
Route::get('/{regionHandle}', [RegionPageController::class, 'page']);
