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

        // 🛡️ Login & Auth Rate Limiting (Anti Brute-Force & Credential Stuffing)
        RateLimiter::for('auth-login', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));
            $key = 'login|' . ($email ?: 'guest') . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withErrors([
                    'email' => '⚠️ অতিরিক্ত ভুল লগইন চেষ্টা করা হয়েছে। অনুগ্রহ করে ১ মিনিট পর আবার চেষ্টা করুন।',
                ]);
            });
        });

        RateLimiter::for('auth-forgot', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));
            $key = 'forgot|' . ($email ?: 'guest') . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function () {
                return back()->withErrors([
                    'email' => '⚠️ খুব ঘন ঘন পাসওয়ার্ড রিসেট রিকোয়েস্ট পাঠানো হচ্ছে। অনুগ্রহ করে ১ মিনিট পর চেষ্টা করুন।',
                ]);
            });
        });

        RateLimiter::for('photocard-fetch', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ খুব দ্রুত লিঙ্ক ফেচ রিকোয়েস্ট পাঠানো হচ্ছে। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।'
                ], 429);
            });
        });
    }
}
