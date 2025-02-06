<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class TokenGenerate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'token:generate {shop}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to generate token for user';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $shop = $this->argument('shop');
        $user = User::where('name', $shop)->exists() ? User::where('name', $shop)->first() : [];
        if ($user) {
            $accessToken = $user->createToken($shop)->accessToken;
            echo "$accessToken\n";
        } else {
            $this->error('shop does not exist');
        }
    }
}
