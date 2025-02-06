<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $shop = $request->user();

        $news = $shop->news()->orderBy('date', 'desc')->get();

        return response(['data' => $news]);
    }
}
