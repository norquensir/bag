<?php

namespace Norquensir\Bag;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Norquensir\Bag\Console\Commands\InstallCommand;
use Norquensir\Bag\Console\Commands\RunJobCommand;
use Norquensir\Bag\Console\Commands\UninstallCommand;

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
            UninstallCommand::class,
            RunJobCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../config/bag.php' => config_path('bag.php'),
        ]);

        Route::middleware(config('bag.routes.middleware'))->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }
}
