<?php

use Illuminate\Support\Facades\Route;
use App\Modules\SeoIntelligence\Controllers\SeoDashboardController;
use App\Modules\SeoIntelligence\Controllers\SeoConnectController;

/*
|--------------------------------------------------------------------------
| Isolated SEO & Website Intelligence Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth', 'nocache'])->prefix('seo')->name('seo.')->group(function () {
    Route::get('/', [SeoDashboardController::class, 'index'])->name('index');
    Route::get('/guide', [SeoDashboardController::class, 'guide'])->name('guide');
    Route::post('/connect', [SeoConnectController::class, 'store'])->name('connect.store');
    Route::delete('/connect/{id}', [SeoConnectController::class, 'destroy'])->name('connect.destroy');
    Route::post('/crawl/{id}', [SeoDashboardController::class, 'crawl'])->name('crawl');

    // Google OAuth Routes for GSC & GA4
    Route::get('/google/redirect', [SeoConnectController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/google/callback', [SeoConnectController::class, 'handleGoogleCallback'])->name('google.callback');

    // Advanced Enterprise Features Routes
    Route::post('/instant-index/{id}', [SeoDashboardController::class, 'instantIndex'])->name('instant_index');
    Route::get('/instant-index-logs/{id}', [SeoDashboardController::class, 'instantIndexLogs'])->name('instant_index_logs');
    Route::post('/approve-link', [SeoDashboardController::class, 'approveInternalLink'])->name('approve_link');
    Route::post('/discover-check/{id}', [SeoDashboardController::class, 'discoverCheck'])->name('discover_check');
    Route::post('/telegram-alert/{id}', [SeoDashboardController::class, 'telegramAlert'])->name('telegram_alert');
    Route::post('/utm-generate', [SeoDashboardController::class, 'generateUtm'])->name('utm_generate');
    Route::post('/competitor-gap/{id}', [SeoDashboardController::class, 'competitorGap'])->name('competitor_gap');
    Route::post('/content-decay/{id}', [SeoDashboardController::class, 'contentDecay'])->name('content_decay');
    Route::post('/gsc-sync/{id}', [SeoDashboardController::class, 'gscSync'])->name('gsc_sync');
});
