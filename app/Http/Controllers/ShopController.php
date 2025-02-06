<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function authenticated(Request $request) {
        $shop = $request->user();
        if ($shop) {
            $shop->load('country');
        }
        return response(['shop' => $shop ?: null]);
    }
}
