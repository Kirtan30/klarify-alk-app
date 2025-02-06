<?php

use App\Http\Controllers\Proxy\ClinicIndexPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ClinicIndexPageController::class, 'page']);
