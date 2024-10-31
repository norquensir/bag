<?php

namespace App\Console\Commands;

use App\Models\Address;
use Illuminate\Console\Command;

class RunTestCommand extends Command
{
    protected $signature = 'run:test';

    protected $description = 'Run test command';

    public function handle()
    {
        $test = Address::query()->select('postal')->distinct()->get();

        dd($test->pluck('postal'));
    }
}
