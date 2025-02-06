<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ClinicPageTrait;
use App\Traits\PollenRegionPageTrait;
use App\Traits\ProxyTrait;
use App\Traits\RegionPageTrait;
use Illuminate\Http\Request;

class RegionPageController extends Controller
{
    use RegionPageTrait, PollenRegionPageTrait, ProxyTrait;

    public function page(Request $request, $regionHandle)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $domain = strtolower($shop->name);
        $path = $request->path();

        $page = match ($domain) {
            User::ALK_NO_STORE => str($path)->contains('/allergi-klinikker/fylke/') ? $this->noPage($request, $shop, $regionHandle) : null,
            User::ALK_DK_STORE => str($path)->contains('/allergilaeger/') ? $this->dkPage($request, $shop, $regionHandle) : null,
            User::DEMO_STORE => $this->dkPage($request, $shop, $regionHandle),
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

    public function pollenPage(Request $request, $regionHandle)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $domain = strtolower($shop->name);
        $path = $request->path();

        $page = match ($domain) {
            User::ALK_NO_STORE => str($path)->contains('/pollenvarsel/fylke/') ? $this->noPollenPage($request, $shop, $regionHandle) : null,
            User::ALK_DK_STORE => str($path)->contains('/pollental/') ? $this->dkPollenPage($request, $shop, $regionHandle) : null,
            User::DEMO_STORE => $this->dkPollenPage($request, $shop, $regionHandle),
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
            User::ALK_NO_STORE => str($path)->contains('/allergi-klinikker/fylke/') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_DK_STORE => str($path)->contains('/allergilaeger/') ? $this->sitemapUrls($request, $shop) : null,
            User::DEMO_STORE => $this->sitemapUrls($request, $shop),
            default => null,
        };

        if (empty($sitemaps)) {
            abort('404');
        }

        return $sitemaps;
    }

    public function pollenSitemap(Request $request)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();
        $domain = strtolower($shop->name);

        $path = $request->path();

        $sitemaps = match ($domain) {
            User::ALK_NO_STORE => str($path)->contains('/pollenvarsel/fylke/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::ALK_DK_STORE => str($path)->contains('/pollental/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::DEMO_STORE => $this->pollenSitemapUrls($request, $shop),
            default => null,
        };

        if (empty($sitemaps)) {
            abort('404');
        }

        return $sitemaps;
    }
}
