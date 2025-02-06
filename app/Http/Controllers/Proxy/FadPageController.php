<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\CityPageTrait;
use App\Traits\FadPageTrait;
use App\Traits\PollenCityPageTrait;
use App\Traits\ProxyTrait;
use Illuminate\Http\Request;

class FadPageController extends Controller
{
    use FadPageTrait, ProxyTrait;

    public function page(Request $request)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $domain = strtolower($shop->name);

        $path = $request->path();

        $page = match ($domain) {
            User::KLARIFY_US_STORE => $this->usPage($request, $shop),
            User::DEMO_STORE => $this->usPage($request, $shop),
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
