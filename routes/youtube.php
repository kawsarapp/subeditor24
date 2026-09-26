<?php

use Illuminate\Support\Facades\Route;
use App\Modules\YouTubeAutomation\Controllers\{
    YouTubeConnectController,
    YouTubeChannelController,
    YouTubeVideoController,
    YouTubeStudioController
};

/*
|--------------------------------------------------------------------------
| YouTube Automation & AI SEO Module Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'nocache'])->prefix('youtube')->name('youtube.')->group(function () {

    // 1. Google OAuth Authentication & Channel Connection
    Route::get('/auth/connect', [YouTubeConnectController::class, 'redirect'])->name('auth.redirect');
    Route::get('/auth/callback', [YouTubeConnectController::class, 'callback'])->name('auth.callback');
    Route::delete('/channels/{id}/disconnect', [YouTubeConnectController::class, 'disconnect'])->name('channels.disconnect');

    // 2. Channel Management
    Route::get('/channels', [YouTubeChannelController::class, 'index'])->name('channels.index');
    Route::post('/channels/{id}/settings', [YouTubeChannelController::class, 'updateSettings'])->name('channels.settings');
    Route::post('/channels/{id}/toggle-autopilot', [YouTubeChannelController::class, 'toggleAutoPilot'])->name('channels.toggle-autopilot');

    // 3. Video Management & Sync
    Route::get('/videos', [YouTubeVideoController::class, 'index'])->name('videos.index');
    Route::post('/channels/{channelId}/sync', [YouTubeVideoController::class, 'sync'])->name('videos.sync');

    // 4. AI Video SEO Studio & 1-Click Publishing
    Route::get('/studio/{id}', [YouTubeStudioController::class, 'show'])->name('studio.show');
    Route::post('/studio/{id}/optimize', [YouTubeStudioController::class, 'optimizeAi'])->name('studio.optimize');
    Route::post('/studio/{id}/save', [YouTubeStudioController::class, 'saveMetadata'])->name('studio.save');
    Route::post('/studio/{id}/publish', [YouTubeStudioController::class, 'publishNow'])->name('studio.publish');

});
