<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InstallGreetingBoxDatabase extends Command
{
    protected $signature = 'greeting-box:install-db';

    protected $description = 'Alias di excursio:sync-database (box benvenuto e colonne evento)';

    public function handle(): int
    {
        return $this->call('excursio:sync-database');
    }
}
