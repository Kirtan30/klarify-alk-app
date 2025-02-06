<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = [
            [
                'name' => 'Czech',
                'code' => 'cz',
            ],
            [
                'name' => 'Danish',
                'code' => 'da',
            ],
            [
                'name' => 'Dutch',
                'code' => 'nl',
            ],
            [
                'name' => 'English',
                'code' => 'en',
            ],
            [
                'name' => 'French',
                'code' => 'fr',
            ],
            [
                'name' => 'German',
                'code' => 'de',
            ],
            [
                'name' => 'Italian',
                'code' => 'it',
            ],
            [
                'name' => 'Slovak',
                'code' => 'sk',
            ],
            [
                'name' => 'Norwegian',
                'code' => 'no',
            ],
            [
                'name' => 'Spanish',
                'code' => 'es',
            ],
            [
                'name' => 'Swedish',
                'code' => 'sv',
            ],
        ];

        foreach ($languages as $language) {
            Language::updateOrCreate(['code' => $language['code']], $language);
        }
    }
}
