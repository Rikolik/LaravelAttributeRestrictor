<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class LaravelAttributeRestrictorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/restriction.php',
            'restriction' // Config key
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/restriction.php' => config_path('restriction.php'),
        ], 'config');
    }
}