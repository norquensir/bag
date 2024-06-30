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
        $this->dropTables();
        $this->removeFiles();
    }

    private function dropTables(): void
    {
        $tables = [
            'files',
            'places',
            'public_spaces',
            'addresses',
            'boat_spots',
            'trailer_spots',
            'residential_objects',
            'buildings',
            'address_boat_spot',
            'building_residential_object',
            'address_names',
        ];

        foreach (array_reverse($tables) as $table) {
            Schema::connection('bag')->dropIfExists($table);
        }

        Schema::connection('bag')->dropAllTables();
    }

    private function removeFiles(): void
    {
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
