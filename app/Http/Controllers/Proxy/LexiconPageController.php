<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\LexiconPageTrait;
use App\Traits\ProxyTrait;
use Illuminate\Support\Facades\Redirect;

class LexiconPageController extends Controller
{
    use LexiconPageTrait, ProxyTrait;

    public function page(Request $request, $lexiconHandle = null)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $domain = strtolower($shop->name);

        $path = $request->path();

        $page = match ($domain) {
            User::ALK_DE_STORE => $this->dePage($request, $shop, $lexiconHandle),
            User::DEMO_STORE => $this->dePage($request, $shop, $lexiconHandle),
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

    public function sitemap(Request $request)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();
        $domain = strtolower($shop->name);

        $path = $request->path();

        $sitemaps = match ($domain) {
            User::ALK_DE_STORE => $this->sitemapUrls($request, $shop),
            User::DEMO_STORE => $this->sitemapUrls($request, $shop),
            default => null,
        };

        if (empty($sitemaps)) {
            abort('404');
        }

        return $sitemaps;
    }
}
