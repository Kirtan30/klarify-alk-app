<?php

use App\Http\Controllers\IFrame\IFrameController;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| IFrame Routes
|--------------------------------------------------------------------------
|
*/

Route::group(['prefix' => 'iframe'], function () {
      Route::get('/', [IFrameController::class, 'index']);
});
