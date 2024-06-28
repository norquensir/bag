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
        $this->removeMigrations();
    }

    private function dropTables(): void
    {
        $tables = [
            'address_boat_spot',
            'address_names',
            'addresses',
            'boat_spots',
            'building_residential_object',
            'buildings',
            'files',
            'places',
            'public_spaces',
            'residential_objects',
            'trailer_spots',
        ];

        foreach ($tables as $table) {
            Schema::connection('bag')->drop($table);
        }
    }

    private function removeFiles(): void
    {

    }

    private function customAlert(string $message): void
    {
        $length = Str::length($message) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $message . '     *');
        $this->comment(str_repeat('*', $length));
    }
}
