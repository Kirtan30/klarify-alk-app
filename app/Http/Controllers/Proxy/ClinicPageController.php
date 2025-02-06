<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClinicResource;
use App\Models\User;
use App\Services\Datahub;
use App\Traits\ClinicPageTrait;
use App\Traits\ClinicTrait;
use App\Traits\HtmlDomTrait;
use App\Traits\ProxyTrait;
use Illuminate\Http\Request;

class ClinicPageController extends Controller
{
    use ClinicPageTrait, ProxyTrait;

    public function page(Request $request, $clinicHandle)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $domain = strtolower($shop->name);

        $path = $request->path();

        $language = null;
        if ($domain === User::KLARIFY_CH_STORE || $domain === User::DEMO_STORE) {
            if (str($path)->contains('/facharztpraxen/')) $language = 'de';
            elseif (str($path)->contains('/allergologue/')) $language = 'fr';
            elseif (str($path)->contains('/allergologo/')) $language = 'it';
        }

        $page = match ($domain) {
            User::ALK_NL_STORE => $this->nlPage($request, $shop, $clinicHandle),
            User::ALK_DE_STORE => str($path)->contains('/facharztpraxen/') ? $this->dePage($request, $shop, $clinicHandle) : null,
            User::ALK_NO_STORE => str($path)->contains('/klinikker/') ? $this->noPage($request, $shop, $clinicHandle) : null,
            User::KLARIFY_US_STORE => str($path)->contains('/doctors/') ? $this->usPage($request, $shop, $clinicHandle) : null,
            User::KLARIFY_AT_STORE => str($path)->contains('/facharztpraxen/') ? $this->atPage($request, $shop, $clinicHandle) : null,
            User::KLARIFY_CZ_STORE => str($path)->contains('/lekare/') ? $this->czPage($request, $shop, $clinicHandle) : null,
            User::KLARIFY_SK_STORE => str($path)->contains('/lekara/') ? $this->skPage($request, $shop, $clinicHandle) : null,
            User::KLARIFY_CH_STORE => $language ? $this->chPage($request, $shop, $clinicHandle, $language) : null,
            User::KLARIFY_CA_STORE => str($path)->contains('/allergists/') ? $this->caPage($request, $shop, $clinicHandle) : null,
            User::ALK_ODACTRA_STORE => str($path)->contains('/doctors/') ? $this->odactraPage($request, $shop, $clinicHandle) : null,
            User::ALK_RAGWITEK_STORE => str($path)->contains('/doctors/') ? $this->ragwitekPage($request, $shop, $clinicHandle) : null,
            User::ALK_GRASTEK_STORE => str($path)->contains('/doctors/') ? $this->grastekPage($request, $shop, $clinicHandle) : null,
            User::ALK_DK_STORE => str($path)->contains('/klinikker/') ? $this->dkPage($request, $shop, $clinicHandle) : null,
            User::DEMO_STORE => $this->chPage($request, $shop, $clinicHandle, $language),
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
            if (str($path)->contains('/facharztpraxen/')) $language = 'de';
            elseif (str($path)->contains('/allergologue/')) $language = 'fr';
            elseif (str($path)->contains('/allergologo/')) $language = 'it';
        }

        $sitemaps = match ($domain) {
            User::ALK_NL_STORE => $this->sitemapUrls($request, $shop),
            User::ALK_DE_STORE => str($path)->contains('/facharztpraxen/') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_NO_STORE => str($path)->contains('/klinikker/') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_US_STORE => str($path)->contains('/doctors/') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_AT_STORE => str($path)->contains('/facharztpraxen/') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_CZ_STORE => str($path)->contains('/lekare/') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_SK_STORE => str($path)->contains('/lekara/') ? $this->sitemapUrls($request, $shop) : null,
            User::KLARIFY_CH_STORE => $language ? $this->sitemapUrls($request, $shop, $language) : null,
            User::KLARIFY_CA_STORE => str($path)->contains('/allergists/') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_DK_STORE => str($path)->contains('/klinikker/') ? $this->sitemapUrls($request, $shop) : null,
            User::ALK_ODACTRA_STORE, User::ALK_RAGWITEK_STORE, User::ALK_GRASTEK_STORE => str($path)->contains('/doctors/') ? $this->sitemapUrls($request, $shop) : null,
            User::DEMO_STORE => $this->sitemapUrls($request, $shop, $language),
            default => null,
        };

        if (empty($sitemaps)) {
            abort('404');
        }

        return $sitemaps;
    }
}
