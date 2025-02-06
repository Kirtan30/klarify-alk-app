<?php

namespace Database\Seeders;

use App\Models\WeekDay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WeekDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $weekDays = [
            [
                'index' => 0,
                'name' => 'Monday',
                'translations' => ['en' => 'Monday']
            ],
            [
                'index' => 1,
                'name' => 'Tuesday',
                'translations' => ['en' => 'Tuesday']
            ],
            [
                'index' => 2,
                'name' => 'Wednesday',
                'translations' => ['en' => 'Wednesday']
            ],
            [
                'index' => 3,
                'name' => 'Thursday',
                'translations' => ['en' => 'Thursday']
            ],
            [
                'index' => 4,
                'name' => 'Friday',
                'translations' => ['en' => 'Friday']
            ],
            [
                'index' => 5,
                'name' => 'Saturday',
                'translations' => ['en' => 'Saturday']
            ],
            [
                'index' => 6,
                'name' => 'Sunday',
                'translations' => ['en' => 'Sunday']
            ]
        ];

        foreach ($weekDays as $weekDay) {
            WeekDay::updateOrCreate(['index' => $weekDay['index']], $weekDay);
        }
    }
}
