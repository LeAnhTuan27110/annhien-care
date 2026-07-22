<?php

namespace App\Providers;

use App\Domains\Auth\Contracts\AuthServiceInterface;
use App\Domains\Auth\Services\AuthService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind the Auth domain contract to its concrete application service.
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Configure application-wide runtime behavior here when it is introduced.
    }
}
