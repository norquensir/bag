<?php

namespace App\Console\Commands;

use App\Jobs\RunAddressNamesJob;
use Illuminate\Console\Command;
use function Laravel\Prompts\error;

class RunAddressNamesCommand extends Command
{
    protected $signature = 'run:address-names {--type=}';

    protected $description = 'Create names for every address';

    public function handle()
    {
        if (empty($this->option('type'))) {
            error('--type is required');
            exit();
        }

        $correctTypes = ['create', 'delete'];
        if (in_array($this->option('type'), $correctTypes)) {
            RunAddressNamesJob::dispatch($this->option('type'));
        } else {
            error('Incorrect command type');
        }
    }
}
