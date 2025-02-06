<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class RemoveExportFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:exports';

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
        $selfResultsDirectories = Storage::disk('public')->allDirectories('exports/self_tests');
        $allergyResultsDirectories = Storage::disk('public')->allDirectories('exports/allergy_tests_german');

        $date = Carbon::now()->subHours(1);

        foreach ($selfResultsDirectories as $directory) {
            $name = basename($directory);
            $newName = Carbon::createFromTimestamp($name);

            if ($newName->lt($date)) {
                Storage::disk('public')->deleteDirectory($directory);
            }

        }

        foreach ($allergyResultsDirectories as $directory) {
            $name = basename($directory);
            $newName = Carbon::createFromTimestamp($name);

            if ($newName->lt($date)) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        }
    }
}
