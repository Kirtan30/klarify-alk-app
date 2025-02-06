<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ProxyTrait;
use Illuminate\Http\Request;

class ProxyController extends Controller
{
    use ProxyTrait;

    public function notFoundPage(Request $request)
    {
        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $notFoundPageHandle = '404';

        return $this->getShopifyPage($shop, $notFoundPageHandle);
    }
}
