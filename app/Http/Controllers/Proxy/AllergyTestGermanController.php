<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\AllergyTestGerman;
use App\Http\Controllers\AllergyTestGermanController as AppAllergyTestGermanController;
use Illuminate\Http\Request;

class AllergyTestGermanController extends Controller
{
    public $allergyTestGermanController;

    public function __construct() {
        $this->allergyTestGermanController = new AppAllergyTestGermanController();
    }

    public function download(Request $request, AllergyTestGerman $result) {
        return $this->allergyTestGermanController->download($request, $result);
    }
}
