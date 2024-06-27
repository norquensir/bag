<?php

namespace Norquensir\Bag;

use Illuminate\Support\ServiceProvider;
use Norquensir\Bag\Console\Commands\InstallCommand;

class BagServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->commands([
            InstallCommand::class,
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
