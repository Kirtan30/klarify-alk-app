<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\CityPageTrait;
use App\Traits\ClinicTrait;
use App\Traits\HtmlDomTrait;
use App\Traits\PollenCityPageTrait;
use App\Traits\ProxyTrait;
use Illuminate\Http\Request;

class CityPageController extends Controller
{
    use CityPageTrait, PollenCityPageTrait, ProxyTrait;

    public function page(Request $request, $cityHandle)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();
        $cityHandle = trim($cityHandle);

        $domain = strtolower($shop->name);

        $path = $request->path();

        $language = null;

        if (in_array($domain, [User::ALK_NL_STORE])) {
            if (str($path)->contains('/clinics/cities/')) {
                $preparedUrl = "https://$shop->public_domain/apps/pages/allergiespecialisten-testen/$cityHandle";
                return redirect()->to($preparedUrl, 301);
            }
        }

        if ($domain === User::KLARIFY_CH_STORE) {
            if (str($path)->contains('/allergologensuche/')) $language = 'de';
            elseif (str($path)->contains('/trouver-un-allergologue/')) $language = 'fr';
            elseif (str($path)->contains('/trova-un-allergologo/')) $language = 'it';
        }

        $page = match ($domain) {
            User::ALK_NL_STORE => str($path)->contains('/allergiespecialisten-testen/') ? $this->nlPage($request, $shop, $cityHandle) : null,
            User::ALK_DE_STORE => str($path)->contains('/allergologensuche/stadt/') ? $this->dePage($request, $shop, $cityHandle) : null,
            User::KLARIFY_US_STORE => str($path)->contains('/find-a-doctor/') ? $this->usPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_AT_STORE => str($path)->contains('/facharztsuche') ? $this->atPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_CZ_STORE => str($path)->contains('/vyhledat-alergologa') ? $this->czPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_SK_STORE => str($path)->contains('/vyhladat-alergologa') ? $this->skPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_CH_STORE => $language ? $this->chPage($request, $shop, $cityHandle, $language) : null,
            User::KLARIFY_CA_STORE => str($path)->contains('/find-an-allergist/') ? $this->caPage($request, $shop, $cityHandle) : null,
            User::ALK_ODACTRA_STORE => str($path)->contains('/find-an-allergy-specialist/') ? $this->odactraPage($request, $shop, $cityHandle) : null,
            User::ALK_RAGWITEK_STORE => str($path)->contains('/find-doctor/') ? $this->ragwitekPage($request, $shop, $cityHandle) : null,
            User::ALK_GRASTEK_STORE => str($path)->contains('/find-doctor/') ? $this->grastekPage($request, $shop, $cityHandle) : null,
            User::DEMO_STORE => $this->nlPage($request, $shop, $cityHandle),
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

    public function pollenPage(Request $request, $cityHandle)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();
        $cityHandle = trim($cityHandle);

        $domain = strtolower($shop->name);
        $path = $request->path();

        $language = null;
        if ($domain === User::KLARIFY_CH_STORE) {
            if (str($path)->contains('/pollenbelastung-heute/')) $language = 'de';
            elseif (str($path)->contains('/charge-pollinique-aujourdhui/')) $language = 'fr';
            elseif (str($path)->contains('/la-concentrazione-pollinica-oggi/')) $language = 'it';
        }
        else if ($domain === User::KLARIFY_CA_STORE) {
            if (str($path)->contains('/pollen-forecast/')) $language = 'en';
            elseif (str($path)->contains('/comptage-du-pollen-aujourdhui/')) $language = 'fr';
        }

        $page = match ($domain) {
            User::ALK_NO_STORE => str($path)->contains('/pollenvarsel/by/') ? $this->noPollenPage($request, $shop, $cityHandle) : null,
            User::ALK_NL_STORE => str($path)->contains('/hooikoorts-pollenradar/') ? $this->nlPollenPage($request, $shop, $cityHandle) : null,
            User::ALK_DK_STORE => str($path)->contains('/pollental/by/') ? $this->dkPollenPage($request, $shop, $cityHandle) : null,
            User::ALK_DE_STORE => str($path)->contains('/pollenflug-vorhersage/stadt/') ? $this->dePollenPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_US_STORE => str($path)->contains('/pollen-forecast/') ? $this->usPollenPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_AT_STORE => str($path)->contains('/pollenflugvorhersage/') ? $this->atPollenPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_CZ_STORE => str($path)->contains('/mnozstvi-pylu-dnes/') ? $this->czPollenPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_SK_STORE => str($path)->contains('/mnozstvo-pelu-dnes/') ? $this->skPollenPage($request, $shop, $cityHandle) : null,
            User::KLARIFY_CH_STORE => $language ? $this->chPollenPage($request, $shop, $cityHandle, $language) : null,
            User::KLARIFY_CA_STORE => $language ? $this->caPollenPage($request, $shop, $cityHandle, $language) : null,
            User::DEMO_STORE => $this->nlPollenPage($request, $shop, $cityHandle, $language),
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

        $language = null;
        if ($domain === User::KLARIFY_CH_STORE) {
            if (str($path)->contains('/allergologensuche/')) $language = 'de';
            elseif (str($path)->contains('/trouver-un-allergologue/')) $language = 'fr';
            elseif (str($path)->contains('/trova-un-allergologo/')) $language = 'it';
        }

        $sitemaps = match ($domain) {
            User::ALK_NL_STORE => str($path)->contains('/allergiespecialisten-testen/') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_DE_STORE => str($path)->contains('/allergologensuche/stadt/') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_US_STORE => $this->sitemapUrls($request, $shop),
            User::KLARIFY_AT_STORE => str($path)->contains('/facharztsuche') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_CZ_STORE => str($path)->contains('/vyhledat-alergologa') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_SK_STORE => str($path)->contains('/vyhladat-alergologa') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_CH_STORE => $language ? $this->sitemapUrls($request, $shop, $language) : null,
            User::KLARIFY_CA_STORE => str($path)->contains('/find-an-allergist') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_ODACTRA_STORE => str($path)->contains('/find-an-allergy-specialist/') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_RAGWITEK_STORE => str($path)->contains('/find-doctor') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_GRASTEK_STORE => str($path)->contains('/find-doctor') ? $this->sitemapUrls($request, $shop) : null,
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

        $language = null;
        if ($domain === User::KLARIFY_CH_STORE) {
            if (str($path)->contains('/pollenbelastung-heute/')) $language = 'de';
            elseif (str($path)->contains('/charge-pollinique-aujourdhui/')) $language = 'fr';
            elseif (str($path)->contains('/la-concentrazione-pollinica-oggi/')) $language = 'it';
        }
        else if ($domain === User::KLARIFY_CA_STORE) {
            if (str($path)->contains('/pollen-forecast/')) $language = 'en';
            elseif (str($path)->contains('/comptage-du-pollen-aujourdhui/')) $language = 'fr';
        }

        $sitemaps = match ($domain) {
            User::ALK_NO_STORE => str($path)->contains('/pollenvarsel/by/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::ALK_NL_STORE => str($path)->contains('/hooikoorts-pollenradar/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::ALK_DK_STORE => str($path)->contains('/pollental/by/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::ALK_DE_STORE => str($path)->contains('/pollenflug-vorhersage/stadt/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::KLARIFY_US_STORE => str($path)->contains('/pollen-forecast/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::KLARIFY_AT_STORE => str($path)->contains('/pollenflugvorhersage/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::KLARIFY_CZ_STORE => str($path)->contains('/mnozstvi-pylu-dnes/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::KLARIFY_SK_STORE => str($path)->contains('/mnozstvo-pelu-dnes/') ? $this->pollenSitemapUrls($request, $shop) : null,
            User::KLARIFY_CH_STORE => $language ? $this->pollenSitemapUrls($request, $shop, $language) : null,
            User::KLARIFY_CA_STORE => $language ? $this->pollenSitemapUrls($request, $shop, $language) : null,
            User::DEMO_STORE => $this->pollenSitemapUrls($request, $shop),
            default => null,
        };

        if (empty($sitemaps)) {
            abort('404');
        }

        return $sitemaps;
    }
}
