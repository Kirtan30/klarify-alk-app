<?php

use App\Http\Controllers\Proxy\PollenPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PollenPageController::class, 'pollenPage']);
