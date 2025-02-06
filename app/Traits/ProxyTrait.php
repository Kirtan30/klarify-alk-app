<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait ProxyTrait
{
    use SettingTrait;

    function loadSimpleHtmlDom()
    {
        define('simplehtmldom\MAX_FILE_SIZE', 10000000);
        include_once(app_path('../vendor/simplehtmldom/simplehtmldom/simple_html_dom.php'));
    }

    public function getShopifyPage($shop, $handle)
    {
        $url = null;
        try {
            $local = config('app.env') === 'local';
            $localDomain = "g35s1px8zl2js9bd-34635907212.shopifypreview.com";

            $domain = $local ? $localDomain : $shop->name;

            if (in_array($shop->name, [User::DEMO_STORE, User::ALK_RAGWITEK_STORE])) {
                $domain = 'b8jyn8vpu62dnwwq-62703730837.shopifypreview.com';
            }
            if (in_array($shop->name, [User::DEMO_STORE, User::ALK_GRASTEK_STORE])) {
                 $domain = 'ee70dxha9uvc3r6k-57010192474.shopifypreview.com';
            }
/*            if (in_array($shop->name, [User::DEMO_STORE, User::ALK_DK_STORE])) {
                 $domain = 'vhld1v1smgez4es1-34184495237.shopifypreview.com';
            }*/

            $domain = 'allesoverallergie.nl';
            $url = "https://$domain/pages/$handle";

            $cacheEnabled = $this->enabledProxyCache($shop->settings);

            if ($cacheEnabled && Cache::has($url)) {
                return Cache::get($url);
            }

            $count = 3;

            do {
                $response = Http::get($url);
                if ($response->successful()) {
                    if ($cacheEnabled) {
                        Cache::put($url, $response->body(), now()->addDay());
                    }
                    return $response->body();

                } else {

                    Log::error("Unable to get Shopify page => Status => $count => $url => " . $response->status() . ' Error => ' . $response->body());

                    if (in_array($response->status(), [429, 430])) {
                        $count--;
                        sleep(2);
                    } else {
                        return null;
                    }
                }
            } while ($count > 0);

        } catch (\Exception $e) {
            Log::error("Unable to get Shopify page => Status code => $url => " . $e->getCode() . ' Error => ' . $e->getMessage());
        }

        return null;
    }
}
