<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ONLY force HTTPS if the current environment is NOT local AND we aren't using a localhost URL
        if (app()->environment('production')) {
            $url = config('app.url');
            if (!str_contains($url, 'localhost') && !str_contains($url, '127.0.0.1')) {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }
    }
}
