<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AllergyTest;
use Illuminate\Http\Request;
use App\Http\Controllers\AllergyTestController as AppAllergyTestController;

class AllergyTestController extends Controller
{
    public $appAllergyTestController;

    public function __construct() {
        $this->appAllergyTestController = new AppAllergyTestController();
    }

    public function show(AllergyTest $allergyTest) {
        return $this->appAllergyTestController->show($allergyTest);
    }
}
