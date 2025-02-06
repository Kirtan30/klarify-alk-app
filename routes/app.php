<?php

use App\Http\Controllers\AllergyTestSwedishController;
use App\Http\Controllers\ClinicController;
use App\Http\Controllers\FadCityController;
use App\Http\Controllers\FadPageContentController;
use App\Http\Controllers\LexiconController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\PollController;
use App\Http\Controllers\PollenCalendarController;
use App\Http\Controllers\PollenPageContentController;
use App\Http\Controllers\ShopController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SelfTestResultController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Pollencontroller;
use App\Http\Controllers\AllergyTestGermanController;
use App\Http\Controllers\AllergyTestController;
use App\Http\Controllers\FadRegionController;
use App\Http\Controllers\FadStateController;
use App\Http\Controllers\PollenCityController;
use App\Http\Controllers\PollenStateController;
use App\Http\Controllers\PollenRegionController;

Route::group(['middleware' => ['verify.shopify']], function () {

    Route::group(['prefix' => 'shops'], function () {
       Route::get('/auth', [ShopController::class, 'authenticated']);
    });

    Route::group(['prefix' => 'self-test/results'], function () {

        Route::get('/', [SelfTestResultController::class, 'index']);
        Route::get('/{result:uuid}/download', [SelfTestResultController::class, 'download']);
        Route::get('/exportcsv', [SelfTestResultController::class, 'exportCsv']);
    });

    Route::group(['prefix' => 'allergy-test-german'], function () {

        Route::get('/', [AllergyTestGermanController::class, 'index']);
        Route::get('/{result:uuid}/download', [AllergyTestGermanController::class, 'download']);
        Route::get('/exportcsv', [AllergyTestGermanController::class, 'exportCsv']);
    });

    Route::group(['prefix' => 'allergy-test-swedish'], function () {

        Route::get('/', [AllergyTestSwedishController::class, 'index']);
        Route::get('/{result:uuid}/download', [AllergyTestSwedishController::class, 'download']);
        Route::get('/exportcsv', [AllergyTestSwedishController::class, 'exportCsv']);
    });

    Route::group(['prefix' => 'clinics'], function () {

        Route::get('/', [ClinicController::class, 'index']);
        Route::post('/sync', [ClinicController::class, 'sync']);
        Route::post('/', [ClinicController::class, 'modify']);
        Route::get('/entities', [ClinicController::class, 'entities']);

        Route::group(['prefix' => '{clinic}'], function () {

            Route::get('/', [ClinicController::class, 'show']);
            Route::delete('/', [ClinicController::class, 'delete']);
            Route::put('/', [ClinicController::class, 'update']);
        });
    });

/*    Route::group(['prefix' => 'allergy-tests'], function () {
        Route::get('/', [AllergyTestController::class, 'index']);
        Route::post('/', [AllergyTestController::class, 'store']);

        Route::group(['prefix' => '{allergyTest}'], function () {
            Route::get('/', [AllergyTestController::class, 'show']);
            Route::delete('/', [AllergyTestController::class, 'delete']);
            Route::put('/', [AllergyTestController::class, 'update']);
        });
    });*/

    Route::group(['prefix' => 'quizzes'], function () {

        Route::get('/', [QuizController::class, 'index']);
        Route::post('/', [QuizController::class, 'store']);

        Route::group(['prefix' => '{quiz}'], function () {

            Route::get('/', [QuizController::class, 'show']);
            Route::delete('/', [QuizController::class, 'delete']);
            Route::put('/', [QuizController::class, 'update']);
            Route::post('/upload', [QuizController::class, 'upload']);
        });
    });

    Route::group(['prefix' => 'polls'], function () {
       Route::get('/', [PollController::class, 'index']);
       Route::post('/', [PollController::class, 'store']);
        Route::put('/{poll}', [PollController::class, 'update']);
        Route::delete('/{poll}', [PollController::class, 'delete']);
        Route::get('/{poll}', [PollController::class, 'show']);
    });

    Route::group(['prefix' => 'news'], function () {
       Route::get('/', [NewsController::class, 'index']);
       Route::post('/', [NewsController::class, 'store']);

        Route::group(['prefix' => '{news}'], function () {
            Route::get('/', [NewsController::class, 'show']);
            Route::put('/', [NewsController::class, 'update']);
            Route::post('/upload', [NewsController::class, 'upload']);
            Route::delete('/', [NewsController::class, 'delete']);
        });
    });

    Route::group(['prefix' => 'fad'], function () {
        Route::group(['prefix' => 'cities'], function () {
            Route::get('/', [FadCityController::class, 'index']);
            Route::post('/sync', [FadCityController::class, 'sync']);
            Route::post('/', [FadCityController::class, 'store']);

            Route::group(['prefix' => '{fadCity}'], function () {
                Route::get('/', [FadCityController::class, 'show']);
                Route::put('/', [FadCityController::class, 'update']);
//                Route::delete('/', [FadCityController::class, 'delete']);
            });
        });

        Route::group(['prefix' => 'regions'], function () {
            Route::get('/', [FadRegionController::class, 'index']);
            Route::post('/', [FadRegionController::class, 'store']);

            Route::group(['prefix' => '{fadRegion}'], function () {
                Route::get('/', [FadRegionController::class, 'show']);
                Route::put('/', [FadRegionController::class, 'update']);
//                Route::delete('/', [FadRegionController::class, 'delete']);
            });
        });

        Route::group(['prefix' => 'states'], function () {
            Route::get('/', [FadStateController::class, 'index']);
//            Route::post('/', [FadStateController::class, 'store']);

            Route::group(['prefix' => '{fadState}'], function () {
                Route::get('/', [FadStateController::class, 'show']);
                Route::put('/', [FadStateController::class, 'update']);
//                Route::delete('/', [FadStateController::class, 'delete']);
            });
        });

        Route::group(['prefix' => 'static-contents'], function () {
            Route::get('/', [FadPageContentController::class, 'index']);
            Route::post('/', [FadPageContentController::class, 'store']);

            Route::group(['prefix' => '{fadPageContent}'], function () {
                Route::get('/', [FadPageContentController::class, 'show']);
                Route::put('/', [FadPageContentController::class, 'update']);
                Route::delete('/', [FadPageContentController::class, 'delete']);
            });
        });
    });

    Route::group(['prefix' => 'pollen'], function () {
        Route::group(['prefix' => 'cities'], function () {
            Route::get('/', [PollenCityController::class, 'index']);
            Route::post('/sync', [PollenCityController::class, 'sync']);
            Route::post('/', [PollenCityController::class, 'store']);
            Route::get('/default', [PollenCityController::class, 'defaultCities']);

            Route::group(['prefix' => '{pollenCity}'], function () {
                Route::get('/', [PollenCityController::class, 'show']);
                Route::put('/', [PollenCityController::class, 'update']);
                Route::delete('/', [PollenCityController::class, 'delete']);
            });
        });

        Route::group(['prefix' => 'languages'], function () {
            Route::get('/', [PollenController::class, 'pollenLanguages']);
        });

        Route::group(['prefix' => 'regions'], function () {
            Route::get('/', [PollenRegionController::class, 'index']);
            Route::post('/', [PollenRegionController::class, 'store']);
            Route::get('/default', [PollenRegionController::class, 'defaultRegions']);

            Route::group(['prefix' => '{pollenRegion}'], function () {
                Route::get('/', [PollenRegionController::class, 'show']);
                Route::put('/', [PollenRegionController::class, 'update']);
                Route::delete('/', [PollenRegionController::class, 'delete']);
            });
        });

        Route::group(['prefix' => 'states'], function () {
            Route::get('/', [PollenStateController::class, 'index']);
            Route::post('/', [PollenStateController::class, 'store']);
            Route::get('/default', [PollenStateController::class, 'defaultStates']);

            Route::group(['prefix' => '{pollenState}'], function () {
                Route::get('/', [PollenStateController::class, 'show']);
                Route::put('/', [PollenStateController::class, 'update']);
                Route::delete('/', [PollenStateController::class, 'delete']);
            });
        });

        Route::group(['prefix' => 'static-contents'], function () {
            Route::get('/', [PollenPageContentController::class, 'index']);
            Route::post('/', [PollenPageContentController::class, 'store']);

            Route::group(['prefix' => '{pollenPageContent}'], function () {
                Route::get('/', [PollenPageContentController::class, 'show']);
                Route::put('/', [PollenPageContentController::class, 'update']);
                Route::delete('/', [PollenPageContentController::class, 'delete']);
            });
        });
    });

    Route::group(['prefix' => 'lexicons'], function () {
        Route::get('/', [LexiconController::class, 'index']);
        Route::post('/', [LexiconController::class, 'store']);
        Route::post('/sync', [LexiconController::class, 'sync']);

        Route::group(['prefix' => '{lexicon}'], function () {
            Route::get('/', [LexiconController::class, 'show']);
            Route::put('/', [LexiconController::class, 'update']);
            Route::delete('/', [LexiconController::class, 'delete']);
        });
    });

    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingController::class, 'show']);
        Route::post('/', [SettingController::class, 'store']);

        Route::group(['prefix' => 'cache'], function () {
            Route::post('/clear', [SettingController::class, 'clearCache']);
        });
    });

    Route::group(['prefix' => 'languages'], function () {
        Route::get('/', [LanguageController::class, 'index']);
    });

    Route::group(['prefix' => 'pollen'], function () {
       Route::group(['prefix' => 'calendar'], function () {
          Route::post('/', [PollenCalendarController::class, 'store']);
          Route::post('/sync', [Pollencontroller::class, 'sync']);
       });
    });
});
