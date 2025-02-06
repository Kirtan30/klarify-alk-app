<?php

use App\Http\Controllers\Proxy\CityPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [CityPageController::class, 'sitemap']);
Route::get('/fad-sitemap.xml', [CityPageController::class, 'sitemap']);
Route::get('/{cityHandle}', [CityPageController::class, 'page']);
