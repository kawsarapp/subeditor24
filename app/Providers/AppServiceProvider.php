<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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

        // 🛡️ Flexible, High-Capacity Rate Limiters (Production Protected)
        RateLimiter::for('ai-operations', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ খুব দ্রুত এআই রিকোয়েস্ট পাঠানো হচ্ছে। অনুগ্রহ করে কয়েক সেকেন্ড অপেক্ষা করে আবার চেষ্টা করুন।'
                ], 429);
            });
        });

        RateLimiter::for('live-polling', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('dedup-check', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });
    }
}
