<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\PollenPageTrait;
use App\Traits\ProxyTrait;
use Illuminate\Http\Request;

class PollenPageController extends Controller
{
    use PollenPageTrait, ProxyTrait;

    public function pollenPage(Request $request)
    {
        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $domain = strtolower($shop->name);

        $path = $request->path();

        $page = match ($domain) {
            User::KLARIFY_US_STORE => str($path)->contains('/pollen-forecast') ? $this->usPollenPage($request, $shop) : null,
            User::DEMO_STORE => $this->usPollenPage($request, $shop),
            default => null,
        };

        $status = 200;

        if (empty($page)) {
            $status = 404;
            $notFoundPageHandle = '404';
            $page = $this->getShopifyPage($shop, $notFoundPageHandle);
        }

        return response($page, $status);
    }
}
