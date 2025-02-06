<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function index() {
        return response(['languages' => Language::all()], 200);
    }
}
