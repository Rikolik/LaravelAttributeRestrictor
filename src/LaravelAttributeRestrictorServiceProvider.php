<?php

namespace Enriko\LaravelAttributeRestrictor;

use Illuminate\Support\ServiceProvider;

class LaravelAttributeRestrictorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/enriko/attribute-restriction.php',
            'enriko.attribute-restriction' // Config key
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/enriko/attribute-restriction.php' => config_path('enriko/attribute-restriction.php'),
        ], 'config');
    }
}