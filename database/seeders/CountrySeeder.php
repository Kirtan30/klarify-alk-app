<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Language;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = [
            ['code' => 'dk', 'name' => 'Denmark', 'timezone' => 'Europe/Copenhagen'],
            ['code' => 'fr', 'name' => 'France', 'timezone' => null],
            ['code' => 'nl', 'name' => 'Netherlands', 'timezone' => 'Europe/Amsterdam'],
            ['code' => 'no', 'name' => 'Norway', 'timezone' => null],
            ['code' => 'se', 'name' => 'Sweden', 'timezone' => null],
            ['code' => 'de', 'name' => 'Germany', 'timezone' => 'Europe/Berlin'],
            ['code' => 'gb', 'name' => 'United Kingdom', 'timezone' => 'Europe/London'],
            ['code' => 'us', 'name' => 'United States of America', 'timezone' => 'America/Chicago'],
            ['code' => 'sk', 'name' => 'Slovakia', 'timezone' => 'Europe/Bratislava'],
            ['code' => 'es', 'name' => 'Spain', 'timezone' => 'Europe/Madrid'],
            ['code' => 'cz', 'name' => 'Czechia', 'timezone' => 'Europe/Prague'],
            ['code' => 'ie', 'name' => 'Ireland', 'timezone' => 'Europe/Dublin'],
            ['code' => 'ca', 'name' => 'Canada', 'timezone' => null],
            ['code' => 'at', 'name' => 'Austria', 'timezone' => null],
            ['code' => 'ch', 'name' => 'Switzerland', 'timezone' => null],
        ];

        foreach ($countries as $country) {

            Country::updateOrCreate(
                ['code' => $country['code']],
                ['name' => $country['name'], 'timezone' => $country['timezone']]
            );
        }
    }
}
