<?php

use App\Http\Controllers\API\AllergyTestGermanController;
use App\Http\Controllers\API\AllergyTestSwedishController;
use App\Http\Controllers\API\ClinicController;
use App\Http\Controllers\API\NewsController;
use App\Http\Controllers\API\PollenCalendarController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SelfTestResultController;
use App\Http\Controllers\API\QuizController;
use App\Http\Controllers\API\PollController;
use App\Http\Controllers\API\AllergyTestController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'auth:api'], function () {

    Route::group(['prefix' => 'self-test/results'], function () {

        Route::post('/', [SelfTestResultController::class, 'store']);
    });

    Route::group(['prefix' => 'allergy-test-german'], function () {

        Route::post('/', [AllergyTestGermanController::class, 'store']);
    });

    Route::group(['prefix' => 'allergy-test-swedish'], function () {

        Route::post('/', [AllergyTestSwedishController::class, 'store']);
    });

    Route::get('/quizzes/{quiz:uuid}', [QuizController::class, 'show']);

    Route::group(['prefix' => 'polls'], function () {
        Route::put('/{poll:uuid}/answers', [PollController::class, 'updateCount']);
        Route::get('/{poll:uuid}', [PollController::class, 'show']);
    });

    Route::group(['prefix' => 'clinics'], function () {
        Route::get('/', [ClinicController::class, 'index'])->middleware('compressed.api');
        Route::get('/filters', [ClinicController::class, 'filters'])->middleware('compressed.api');
        Route::get('/compressed', [ClinicController::class, 'compressedIndex'])->middleware('compressed.api');
        Route::get('/{handle}', [ClinicController::class, 'show']);
    });

    Route::group(['prefix' => 'allergy-tests'], function () {
       Route::get('/{allergyTest:uuid}', [AllergyTestController::class, 'show']);
    });

    Route::group(['prefix' => 'news'], function () {
       Route::get('/', [NewsController::class, 'index']);
    });

    Route::group(['prefix' => 'pollen-calendar'], function () {
       Route::get('/types', [PollenCalendarController::class, 'types']);
       Route::get('/allergens', [PollenCalendarController::class, 'allergens']);
    });
});
