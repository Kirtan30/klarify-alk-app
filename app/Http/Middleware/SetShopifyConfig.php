<?php

namespace App\Http\Middleware;

use App\Models\User;
use Assert\AssertionFailedException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Osiset\ShopifyApp\Objects\Values\SessionToken;

class SetShopifyConfig
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws AssertionFailedException
     */
    public function handle(Request $request, Closure $next)
    {
        $shopDomain = $request->get('shop');
        if(!$shopDomain) {
            $shopDomain = $request->header('x-shop-domain') ?: $request->header('x-shopify-shop-domain');
            if(!$shopDomain) {
                $token = $request->get('token');
                if(isset($token)) {
                    $newSessionToken = new SessionToken($token, false);
                    $shopDomain = $newSessionToken->getShopDomain()->toNative();
                } elseif($request->bearerToken()) {
                    $bearerTokens = Collection::make(explode(',', $request->header('Authorization', '')));
                    $token = Str::substr(trim($bearerTokens->last()), 7);
                    $newSessionToken = new SessionToken($token, false);
                    $shopDomain = $newSessionToken->getShopDomain()->toNative();
                }
            }
        }

        if ($shopDomain) {
            $shop = User::where('name', $shopDomain)->first();

            $apiKey = data_get($shop, 'shopify_api_key') ?: env('SHOPIFY_API_KEY');
            $apiSecret = data_get($shop, 'shopify_api_secret') ?: env('SHOPIFY_API_SECRET');
            Config::set('shopify-app.api_key', $apiKey);
            Config::set('shopify-app.api_secret', $apiSecret);
        }

        return $next($request);
    }
}
