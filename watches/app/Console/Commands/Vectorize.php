<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Vectorize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:vectorize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vectorize the CSV file and stores it into the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \App\Jobs\Vectorize::dispatchSync();
    }
}
