<?php

namespace App\Http\Controllers\IFrame;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ProxyTrait;
use Illuminate\Http\Request;

class IFrameController extends Controller
{
    use ProxyTrait;

    public function index(Request $request)
    {
        $domain = strtolower($request->header('shop') ?: data_get($request, 'shop'));
        $lang = strtolower($request->header('lang') ?: data_get($request, 'lang'));

        // $userId = $request->hasHeader('user-id') ? $request->header('user-id') : data_get($request, 'user-id');
        // $address = $request->hasHeader('address') ? $request->header('address') : data_get($request, 'address');

        if (empty($domain)) {
            abort(401);
        }

        $shop = User::where('name', $domain)->orWhere('public_domain', $domain)->first();

        if (!$shop) {
            abort(401);
        }

        $handle = !empty($lang) ? "fad-iframe-$lang" : 'fad-iframe';

        $page = $this->getShopifyPage($shop, $handle);

        $status = 200;

        if (empty($page)) {
            $status = 404;
            $page = $this->getShopifyPage($shop, '404');
        }

        $scriptEl = "<script>
            // Set to the same value as the web property used on the site
            var gaProperty = 'UA-114042244-1';

            // Disable tracking if the opt-out cookie exists.
            var disableStr = 'ga-disable-' + gaProperty;
            if (document.cookie.indexOf(disableStr + '=true') > -1) {
                window[disableStr] = true;
            }

            // Opt-out function
            function gaOptout() {
                document.cookie = disableStr + '=true; expires=Thu, 31 Dec 2099 23:59:59 UTC; path=/';
                window[disableStr] = true;
            }
          </script>";

        $page = str_replace($scriptEl,'', $page);

        $scriptEl = "<script>var _gaUTrackerOptions = {'allowLinker': true};ga('create', 'UA-114042244-3', 'auto', _gaUTrackerOptions);ga('send', 'pageview');
            (function() {
                ga('require', 'linker');
                    function addListener(element, type, callback) {
                        if (element.addEventListener) {
                            element.addEventListener(type, callback);
                        }
                    else if (element.attachEvent) {
                        element.attachEvent('on' + type, callback);
                    }
                }
                function decorate(event) {
                    event = event || window.event;
                    var target = event.target || event.srcElement;
                    if (target && (target.action || target.href)) {
                        ga(function (tracker) {
                            var linkerParam = tracker.get('linkerParam');
                            document.cookie = '_shopify_ga=' + linkerParam + '; ' + 'path=/';
                        });
                    }
                }
                addListener(window, 'load', function(){
                    for (var i=0; i<document.forms.length; i++) {
                        if(document.forms[i].action && document.forms[i].action.indexOf('/cart') >= 0) {
                            addListener(document.forms[i], 'submit', decorate);
                        }
                    }
                    for (var i=0; i<document.links.length; i++) {
                        if(document.links[i].href && document.links[i].href.indexOf('/checkout') >= 0) {
                            addListener(document.links[i], 'click', decorate);
                        }
                    }
                })
            }());
            </script>";

        $page = str_replace($scriptEl, '', $page);

        $page = str_replace(
            '<script async="async" src="https://www.google-analytics.com/analytics.js"></script>',
            '',
            $page
        );

        return response($page, $status);
    }
}
