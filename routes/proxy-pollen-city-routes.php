<?php
use App\Http\Controllers\Proxy\CityPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [CityPageController::class, 'pollenSitemap']);
Route::get('/{cityHandle}', [CityPageController::class, 'pollenPage']);
