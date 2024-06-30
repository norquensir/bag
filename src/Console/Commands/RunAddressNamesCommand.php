<?php

namespace Norquensir\Bag\Console\Commands;

use Illuminate\Console\Command;
use Norquensir\Bag\Jobs\RunAddressNamesJob;

class RunAddressNamesCommand extends Command
{
    protected $signature = 'run:address-names {--type=}';

    protected $description = 'Create names for every address';

    public function handle()
    {
        if (empty($this->option('type'))) {
            $this->error('--type is required');
            exit();
        }

        $correctTypes = ['create', 'delete'];
        if (in_array($this->option('type'), $correctTypes)) {
            RunAddressNamesJob::dispatch($this->option('type'));
        } else {
            $this->error('Incorrect command type');
        }
    }
}
