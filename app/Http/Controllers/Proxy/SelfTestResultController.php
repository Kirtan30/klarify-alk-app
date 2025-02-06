<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SelfTestResultController as AppSelfTestResultController;
use App\Models\SelfTestResult;
use Illuminate\Http\Request;

class SelfTestResultController extends Controller
{
    public $selfTestResultController;

    public function __construct() {
        $this->selfTestResultController = new AppSelfTestResultController();
    }

    public function download(Request $request, SelfTestResult $result) {
        return $this->selfTestResultController->download($request, $result);
    }
}
