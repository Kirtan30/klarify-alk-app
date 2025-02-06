<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Http\Controllers\AllergyTestSwedishController as AppAllergyTestSwedishController;
use App\Models\AllergyTestSwedish;
use Illuminate\Http\Request;

class AllergyTestSwedishController extends Controller
{
    public $allergyTestSwedishController;

    public function __construct() {
        $this->allergyTestSwedishController = new AppAllergyTestSwedishController();
    }

    public function download(Request $request, $result) {
        return $this->allergyTestSwedishController->download($request, $result);
    }
}
