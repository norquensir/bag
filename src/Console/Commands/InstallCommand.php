<?php

namespace Norquensir\Bag\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\File;
use Illuminate\Support\Carbon;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    protected $signature = 'bag:install';
    protected $description = 'Install the BAG package';

    public function handle()
    {
        $this->checkDb();
        $this->moveMigrations();
        $this->runMigrations();
    }

    private function checkDb(): void
    {
        if (!array_key_exists('bag', Config::get('database.connections'))) {
            $this->alert('BAG: Database connection is not defined');
            exit();
        }

        $dbSettings = [
            'BAG_DB_HOST',
            'BAG_DB_PORT',
            'BAG_DB_DATABASE',
            'BAG_DB_USERNAME',
            'BAG_DB_PASSWORD',
        ];

        foreach ($dbSettings as $dbSetting) {
            if (!Env::get($dbSetting)) {
                $this->alert('BAG: Missing DB setting ' . $dbSetting);
                exit();
            }
        }
    }

    private function moveMigrations(): void
    {
        $this->customAlert('Creating migration files');

        $storage = Storage::build([
            'driver' => 'local',
            'root' => __DIR__ . '/../../../database/migrations',
        ]);

        foreach ($storage->files() as $file) {
            $newFile = Str::of($file)->substrReplace(join('_', [
                Carbon::today()->format('Y'),
                Carbon::today()->format('m'),
                Carbon::today()->format('d')
            ]), 0, 10);

            Storage::build([
                'driver' => 'local',
                'root' => database_path('migrations'),
            ])->putFileAs('bag', new File($storage->path($file)), $newFile);

            $this->info('Created ' . $newFile);
        }

        $this->customAlert('Creating migration files');
        $this->newLine();
    }

    private function runMigrations(): void
    {
        $this->customAlert('Running migrations');

        if (!Schema::connection('bag')->hasTable('migrations')) {
            $this->call('migrate:install', [
                '--database' => 'bag',
            ]);
        }

        $this->customAlert('Running migrations');
    }

    private function customAlert(string $message): void
    {
        $length = Str::length($message) + 12;

        $this->comment(str_repeat('*', $length));
        $this->comment('*     ' . $message . '     *');
        $this->comment(str_repeat('*', $length));
    }
}
