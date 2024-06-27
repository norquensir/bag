<?php

namespace Norquensir\Bag\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UninstallCommand extends Command
{
    protected $signature = 'bag:uninstall';
    protected $description = 'Uninstall the BAG package';

    public function handle()
    {
        Schema::connection('bag')->dropAllTables();

        Storage::build([
            'driver' => 'local',
            'root' => database_path('migrations'),
        ])->deleteDirectory('bag');

    }

    private function customAlert(string $message): void
    {
        $length = Str::length($message) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $message . '     *');
        $this->comment(str_repeat('*', $length));
    }
}
