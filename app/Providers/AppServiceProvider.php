<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

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
        try {
            $version = Cache::get('github_version', 'v1.0.0');
        } catch (\Throwable $e) {
            $version = 'v1.0.0';
        }
        view()->share('appVersion', $version);
    }
}
