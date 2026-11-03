<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\Illuminate\Contracts\Foundation\MaintenanceMode::class, function () {
            return new \Illuminate\Foundation\FileBasedMaintenanceMode();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
            isset($_SERVER['VERCEL']) ||
            getenv('VERCEL') ||
            config('app.env') === 'production'
        ) {
            URL::forceScheme('https');
        }
    }
}

