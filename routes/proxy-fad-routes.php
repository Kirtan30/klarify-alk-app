<?php

use App\Http\Controllers\Proxy\FadPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FadPageController::class, 'page']);
