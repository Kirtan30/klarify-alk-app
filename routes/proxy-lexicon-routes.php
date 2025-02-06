<?php

use App\Http\Controllers\Proxy\LexiconPageController;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [LexiconPageController::class, 'sitemap']);
Route::get('/{lexiconHandle?}', [LexiconPageController::class, 'page']);
