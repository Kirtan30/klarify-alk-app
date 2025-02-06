<?php
use App\Http\Controllers\Proxy\RegionPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [RegionPageController::class, 'pollenSitemap']);
Route::get('/{regionHandle}', [RegionPageController::class, 'pollenPage']);
