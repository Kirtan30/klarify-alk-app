<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Clinic\DatahubClinic;
use Illuminate\Support\Str;
use Illuminate\Console\Command;

class ClinicSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clinic:sync {--C|shop=* : Only sync clinics for the specified shop (domain). Default: all shops} {--I|interactive : Manually specify the shops}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize clinic data across all countries';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $shops = User::with('country')
            ->whereNotIn('name', [User::ALK_DK_STORE])
            ->whereHas('country')
            ->get();

        if ($this->option('shop')) {
            $shops = $shops->whereIn('name', $this->option('shop'));
        }

        if ($this->option('interactive')) {
            $selectedShops = $this->choice('Which domains to sync the clinics for?', $shops->pluck('name')->toArray(), null, null, true);
            $shops = $shops->whereIn('name', $selectedShops);
        }

        $countries = $shops->pluck('country')->flatten()->pluck('code')
                ->unique()->values()->toArray();

        foreach ($countries as $country) {

            $this->info('Syncing clinics data for ' . Str::upper($country));
            (new DatahubClinic($country))->syncData();
        }
    }
}
