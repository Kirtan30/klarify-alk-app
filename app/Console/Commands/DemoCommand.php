<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:pollenCalendarNewMetafield';

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
        $shop = User::where('name', User::DEMO_STORE)->first();
        $response = $shop->api()->rest('DELETE', '/admin/metafields/29866722361553.json');
        dd($response);
        die();
        /*$shop = User::where('name', User::ALK_NL_STORE)->first();
        $pollenData = public_path('images/pollen/pollen-nl.json');
        $pollenData = file_get_contents($pollenData);

        try {
            $namespace = "pollen-nl";

            $data = [
                [
                    'key' => 'calendar-new',
                    'value' => json_encode(json_decode($pollenData, true)),
                ]
            ];

            foreach ($data as $datum) {
                $metafieldData = [
                    "metafield" => [
                        "namespace" => $namespace,
                        "key" => data_get($datum, 'key'),
                        "value" => data_get($datum, 'value', []),
                        "type" => "json"
                    ]
                ];

                $response = $shop->api()->rest('POST', '/admin/metafields.json', $metafieldData);
                echo data_get($response, 'status');
            }
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }*/
    }
}
