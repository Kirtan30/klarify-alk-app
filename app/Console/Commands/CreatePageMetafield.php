<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Osiset\ShopifyApp\Contracts\Commands\Shop;
use Osiset\ShopifyApp\ShopifyAppProvider;
use GuzzleHttp\Client;

class CreatePageMetafield extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:page-seo-title-metafield';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->getPages();
    }

    public function getPages()
    {
        $shopUrl = 'demo-kirtan-agrawal.myshopify.com';
        $adminApiKey = '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';
        $storeFrontKey = '89531da80d59439c0a65e0301b34c594';
        $query = <<<GRAPHQL
        {
          pages {
            edges {
              node {
                title
                seo {
                  title
                }
              }
            }
          }
        }
        GRAPHQL;

        $client = new Client();

        $response = $client->post("https://{$shopUrl}/api/2024-01/graphql.json", [
            'headers' => [
                'X-Shopify-Storefront-Access-Token' => $storeFrontKey
            ],
            'json' => [
                'query' => $query,
            ],
        ]);

        $pagesData = json_decode($response->getBody(), true)['data']['pages']['edges'];
        dd($pagesData);

        foreach ($pagesData as $page) {
            $node = $page['node'];
            $pageTitle = $node['title'];
            $seoTitle = $node['seo']['title'] ?? null;

            echo "Page Title: $pageTitle, SEO Title: $seoTitle\n";
        }
    }
}
