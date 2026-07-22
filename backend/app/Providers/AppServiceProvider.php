<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register application-wide service bindings here when they are introduced.
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure application-wide runtime behavior here when it is introduced.
    }
}
