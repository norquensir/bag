<?php

namespace Norquensir\Bag\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Norquensir\Bag\Models\Address;

class InstallCommand extends Command
{
    protected $signature = 'bag:install';
    protected $description = 'Install the BAG package';

    public function handle()
    {
        $this->checkDb();
        $this->checkMigrations();

        dd(Address::first());
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

    private function checkMigrations()
    {
        $storage = Storage::build([
            'driver' => 'local',
            'root' => __DIR__ . '/../../../database/migrations',
        ]);

        foreach ($storage->files() as $file) {
            $date = Str::of(':year:_:month:_:day:')
                ->replace(':year:', Carbon::today()->format('Y'))
                ->replace(':month:', Carbon::today()->format('m'))
                ->replace(':day:', Carbon::today()->format('d'));

            $storage->move($file, Str::of($file)->substrReplace($date, 0, 10));
        }
    }
}
