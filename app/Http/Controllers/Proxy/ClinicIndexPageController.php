<?php

namespace App\Http\Controllers\Proxy;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ClinicIndexPageTrait;
use Illuminate\Http\Request;

class ClinicIndexPageController extends Controller
{
    use ClinicIndexPageTrait;

    public function page(Request $request)
    {

        $shop = config('app.env') === 'local' ? User::first() : $request->user();

        $languageCode = null;
        $domain = strtolower($shop->name);

        $path = $request->path();

        if ($domain === User::KLARIFY_CH_STORE) {
            if (str($path)->contains('/ubersicht-facharztpraxen')) $languageCode = 'de';
            elseif (str($path)->contains('/apercu-cabinets-medicaux-specialises')) $languageCode = 'fr';
            elseif (str($path)->contains('/panoramica-studi-medici-specialistici')) $languageCode = 'it';
        }

        $page = match ($domain) {
            User::ALK_NO_STORE => str($path)->contains('/klinikker-fylke-indeks') ? $this->noPage($request, $shop) : null,
            User::ALK_DK_STORE => str($path)->contains('/liste-allergiklinikker') ? $this->dkPage($request, $shop) : null,
            default => $this->commonPage($request, $shop, $languageCode),
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
