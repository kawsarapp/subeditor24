<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    AdminController,
    WebsiteController,
    NewsController,
    SettingsController,
    TelegramBotController,
    ReporterController,
    ReporterManagementController,
    AdminTemplateController,
    TemplateController
};
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\FacebookPageController;

/*
|--------------------------------------------------------------------------
| Web Routes - SubEditorBD Full Routing System
|--------------------------------------------------------------------------
*/

// --- ১. পাবলিক এবং গেস্ট রুটস ---
Route::get('/', function () {
    return redirect()->route('login');
});

// 🔥 বসের জন্য পাবলিক প্রিভিউ এবং ফিডব্যাক রুটস
Route::get('/preview/{id}', [NewsController::class, 'publicPreview'])->name('news.public-preview');
Route::post('/preview/{id}/feedback', [NewsController::class, 'handlePreviewFeedback'])->name('news.preview-feedback');

// 📖 Public API Integration Guide (No Auth Required)
Route::get('/docs/api-guide', [SettingsController::class, 'apiGuide'])->name('docs.api-guide');

// টেলিগ্রাম ওয়েবহুক (সতর্কতা: CSRF exception এ অ্যাড করতে ভুলবেন না)
Route::post('/telegram/webhook', [TelegramBotController::class, 'handle']);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    // 🔐 Forgot & Reset Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});



// --- ২. লগইন করা সকল ইউজারের জন্য কমন রুটস (Auth & NoCache Middleware) ---
Route::middleware(['auth', 'nocache'])->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/stop-impersonate', [AdminController::class, 'stopImpersonate'])->name('stop.impersonate');

    // নোটিফিকেশন রিড
    Route::get('/notifications/read', function () {
        auth()->user()->unreadNotifications->markAsRead();
        return back();
    })->name('notifications.read');

    // 🔥 AI Viral & Trending Predictor Routes
    Route::get('/trending', [\App\Http\Controllers\TrendingController::class, 'index'])->name('trending.index');
    Route::post('/trending/generate-script', [\App\Http\Controllers\TrendingController::class, 'generateScript'])->name('trending.generate-script');

    // প্রোফাইল ও ক্রেডিট
    Route::get('/credits', [SettingsController::class, 'credits'])->name('credits.index');
    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.update-profile');

    // --- ৩. সেটিংস ম্যানেজমেন্ট ---
    Route::middleware(['permission:can_settings'])->group(function () {
        Route::get('/admin/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/admin/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/admin/settings/upload-logo', [SettingsController::class, 'uploadLogo'])->name('settings.upload-logo');
        
        Route::prefix('settings/test')->name('settings.')->group(function () {
            Route::post('/facebook', [SettingsController::class, 'testFacebookConnection'])->name('test-facebook');
            Route::post('/telegram', [SettingsController::class, 'testTelegramConnection'])->name('test-telegram');
            Route::post('/twitter', [SettingsController::class, 'testTwitterConnection'])->name('test-twitter');
            Route::post('/wordpress', [SettingsController::class, 'testWordPressConnection'])->name('test-wordpress');
            Route::post('/custom-api', [SettingsController::class, 'testCustomApiConnection'])->name('test-custom-api');
            Route::post('/photoroom', [SettingsController::class, 'testPhotoRoomConnection'])->name('test-photoroom');
            Route::post('/decodo-proxy', [SettingsController::class, 'testDecodoProxyConnection'])->name('test-decodo-proxy');
            Route::post('/ai-provider', [SettingsController::class, 'testAiProviderConnection'])->name('test-ai-provider');
        });

        // 🔥 Multi-Facebook Page Routes
        Route::prefix('facebook-pages')->name('fb-pages.')->group(function () {
            Route::post('/', [FacebookPageController::class, 'store'])->name('store');
            Route::put('/{id}', [FacebookPageController::class, 'update'])->name('update');
            Route::delete('/{id}', [FacebookPageController::class, 'destroy'])->name('destroy');
            Route::patch('/{id}/toggle', [FacebookPageController::class, 'toggle'])->name('toggle');
            Route::patch('/{id}/toggle-comment', [FacebookPageController::class, 'toggleCommentLink'])->name('toggle-comment');
            Route::post('/{id}/test', [FacebookPageController::class, 'test'])->name('test');
            Route::patch('/{id}/toggle-default', [FacebookPageController::class, 'setDefault'])->name('toggle-default');
        });
    });

    // রিপোর্টার সেকশন
    Route::prefix('reporter')->name('reporter.')->group(function () {
        Route::get('/news/create', [ReporterController::class, 'create'])->name('news.create');
        Route::post('/news/store', [ReporterController::class, 'store'])->name('news.store');
        Route::get('/my-news', [ReporterController::class, 'index'])->name('news.index');
    });

    // প্রতিনিধি ম্যানেজমেন্ট
    Route::middleware(['permission:manage_reporters'])->group(function () {
        Route::prefix('manage')->name('manage.')->group(function () {
            Route::get('/reporters', [ReporterManagementController::class, 'index'])->name('reporters.index');
            Route::post('/reporters/store', [ReporterManagementController::class, 'store'])->name('reporters.store');
            Route::delete('/reporters/{id}', [ReporterManagementController::class, 'destroy'])->name('reporters.destroy');
            Route::get('/reporter-news', [ReporterManagementController::class, 'newsReport'])->name('reporters.news');
        });
    });

    // --- ৪. নিউজ কোর সেকশন ---
    Route::controller(NewsController::class)->prefix('news')->name('news.')->group(function () {
        
        Route::get('/', 'index')->name('index');
        Route::delete('/{id}', 'destroy')->name('destroy');
        Route::post('/{id}/post', 'postToWordPress')->name('post');
        Route::post('/{id}/manual-publish', 'publishManualFromIndex')->name('manual-publish');
        Route::post('/{id}/queue', 'toggleQueue')->name('queue');
        
        // 🔥 স্ট্যাটাস চেকিং (GET এবং POST আলাদা করা হলো)
        Route::get('/check-status', 'checkAutoPostStatus')->name('check-auto-status');
        Route::post('/check-status', 'checkStatus')->name('check-status'); // Smart Polling এর জন্য
        
        Route::get('/check-scrape-status', 'checkScrapeStatus')->name('check-scrape-status');
        Route::get('/check-new-news', 'checkNewNews')->name('check-new-news');
        Route::post('/toggle-automation', 'toggleAutomation')->name('toggle-automation');
        Route::post('/check-draft-updates', 'checkDraftUpdates')->name('check-draft-updates');
        Route::get('/published', 'published')->name('published');
        Route::get('/suggest-links', 'suggestLinks')->name('suggest-links');
        Route::post('/check-duplicates', 'checkDuplicates')->name('check-duplicates');
        Route::post('/generate-headlines', 'generateHeadlines')->name('generate-headlines');
        Route::post('/bulk-process-ai', 'bulkProcessAi')->name('bulk-process-ai');
        Route::post('/bulk-destroy', 'bulkDestroy')->name('bulk-destroy');
        
        Route::get('/{id}/unlock', 'unlockNews')->name('unlock');
        Route::get('/{id}/get-draft', 'getDraftContent')->name('get-draft');
        Route::post('/{id}/update-draft', 'updateDraft')->name('update-draft');
        Route::post('/{id}/analyze-plagiarism', 'analyzePlagiarism')->name('analyze-plagiarism');
        Route::post('/{id}/process-ai', 'sendToAiQueue')->name('process-ai');
        
        Route::middleware(['permission:can_direct_publish'])->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/store-custom', 'storeCustom')->name('store-custom');
        });

        Route::middleware(['permission:can_ai'])->group(function () {
            Route::get('/drafts', 'drafts')->name('drafts');
            Route::post('/{id}/publish-draft', 'publishDraft')->name('publish-draft');
            Route::post('/{id}/confirm-publish', 'confirmPublish')->name('confirm-publish');
        });

        Route::middleware(['permission:can_studio'])->group(function () {
            Route::get('/{id}/studio', 'studio')->name('studio');
            Route::post('/{id}/publish-studio', 'publishStudioDesign')->name('publish-studio');
            Route::post('/{id}/track-download', 'incrementDownloadCount')->name('track-download');
        });
    });

    // নিউজ স্ক্র্যাপিং
    Route::middleware(['permission:can_scrape'])->group(function () {
        Route::post('/websites/discover', [WebsiteController::class, 'discoverSelectors'])->name('websites.discover');
        Route::resource('websites', WebsiteController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::get('/websites/{id}/scrape', [WebsiteController::class, 'scrape'])->name('websites.scrape');
    });
    
    Route::get('/proxy-image', [NewsController::class, 'proxyImage'])->name('proxy.image');

    // Route::resource('buy-credits', PaymentController::class)->names('payment')->only(['create', 'store']);
    Route::get('/settings/fetch-categories', [SettingsController::class, 'fetchCategories'])->name('settings.fetch-categories');
    Route::post('/settings/save-design', [SettingsController::class, 'saveDesign'])->name('settings.save-design');
    Route::post('/settings/upload-frame', [SettingsController::class, 'uploadFrame'])->name('settings.upload-frame');

    // Admin / Analytics (Protected by controller logic)
    Route::get('/admin/analytics', [\App\Http\Controllers\AnalyticsController::class, 'index'])->name('admin.analytics.index');

    // 🎨 Custom Photo Card Studio & AI Background Remover
    Route::controller(\App\Http\Controllers\CustomPhotoCardController::class)->prefix('studio/custom-photo-card')->name('custom-photo-card.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/remove-bg', 'removeBackground')->name('remove-bg');
        Route::get('/media-frames', 'getMediaFrames')->name('media-frames');
        Route::post('/upload-frame', 'uploadFrame')->name('upload-frame');
        Route::post('/save', 'saveCard')->name('save');
        Route::post('/save-template', 'saveTemplate')->name('save-template');
        Route::delete('/delete-template/{id}', 'deleteTemplate')->name('delete-template');
        Route::post('/publish-facebook', 'publishToFacebook')->name('publish-facebook');
        Route::post('/attach-news', 'attachToNews')->name('attach-news');
    });
});



// Admin (Client) er Staff Management Routes
Route::middleware(['auth'])->prefix('client')->name('client.')->group(function () {
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    
    // 🔥 নতুন পারমিশন, সোর্স এবং টেমপ্লেট রাউট
    Route::put('/staff/{id}/permissions', [StaffController::class, 'updatePermissions'])->name('staff.permissions');
    Route::put('/staff/{id}/websites', [StaffController::class, 'updateWebsites'])->name('staff.websites');
    Route::put('/staff/{id}/templates', [StaffController::class, 'updateTemplates'])->name('staff.templates');
    Route::put('/staff/{id}/signature', [StaffController::class, 'updateSignature'])->name('staff.signature');
    Route::put('/staff/{id}/info', [StaffController::class, 'updateInfo'])->name('staff.update_info');
    Route::post('/staff/{id}/toggle-status', [StaffController::class, 'toggleStatus'])->name('staff.toggle_status');
    Route::post('/staff/{id}/reset-password', [StaffController::class, 'resetPassword'])->name('staff.reset_password');
    Route::get('/staff/{id}/news', [StaffController::class, 'showNews'])->name('staff.news');
    
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // --- 🏢 Departments & Designations Management ---
    Route::post('/departments', [\App\Http\Controllers\DepartmentDesignationController::class, 'storeDepartment'])->name('departments.store');
    Route::delete('/departments/{id}', [\App\Http\Controllers\DepartmentDesignationController::class, 'destroyDepartment'])->name('departments.destroy');
    Route::post('/designations', [\App\Http\Controllers\DepartmentDesignationController::class, 'storeDesignation'])->name('designations.store');
    Route::delete('/designations/{id}', [\App\Http\Controllers\DepartmentDesignationController::class, 'destroyDesignation'])->name('designations.destroy');
    Route::get('/departments/{id}/designations-ajax', [\App\Http\Controllers\DepartmentDesignationController::class, 'ajaxGetDesignations'])->name('departments.designations.ajax');
});

// --- ৫. সুপার অ্যাডমিন রুটস (nocache যোগ করা হয়েছে) ---
Route::middleware(['auth', 'nocache', AdminMiddleware::class])->group(function () {
    
    Route::prefix('admin/templates')->name('admin.templates.')->group(function () {
        Route::get('/',          [TemplateController::class, 'index'])  ->name('index');
        Route::get('/create',    [TemplateController::class, 'create']) ->name('create');
        Route::post('/',         [TemplateController::class, 'store'])  ->name('store');
        Route::get('/{id}/edit', [TemplateController::class, 'edit'])   ->name('edit');
        Route::put('/{id}',      [TemplateController::class, 'update']) ->name('update');
        Route::post('/{id}/toggle', [TemplateController::class, 'toggle'])->name('toggle');
        Route::delete('/{id}',   [TemplateController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('admin/media')->name('admin.media.')->group(function () {
        Route::get('/', [\App\Http\Controllers\MediaController::class, 'index'])->name('index');
        Route::post('/upload', [\App\Http\Controllers\MediaController::class, 'upload'])->name('upload');
        Route::post('/rename', [\App\Http\Controllers\MediaController::class, 'rename'])->name('rename');
        Route::post('/delete', [\App\Http\Controllers\MediaController::class, 'delete'])->name('delete');
    });

    Route::prefix('admin/users/{id}')->name('admin.users.')->group(function () {
        Route::post('/permissions', [AdminController::class, 'updatePermissions'])->name('permissions');
    });

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

        Route::get('/scraper-monitor', [\App\Http\Controllers\SuperAdmin\ScraperMonitorController::class, 'index'])->name('scraper-monitor');
        Route::post('/scraper-monitor/clear', [\App\Http\Controllers\SuperAdmin\ScraperMonitorController::class, 'clearLogs'])->name('scraper-monitor.clear');

        Route::prefix('users/{id}')->name('users.')->group(function () {
            Route::post('/toggle-status', [AdminController::class, 'toggleStatus'])->name('toggle');
            Route::post('/add-credits', [AdminController::class, 'addCredits'])->name('credits');
            Route::post('/templates', [AdminController::class, 'updateTemplates'])->name('templates');
            Route::post('/limit', [AdminController::class, 'updateLimit'])->name('limit');
            Route::post('/websites', [AdminController::class, 'updateWebsiteAccess'])->name('websites');
            Route::post('/scraper', [AdminController::class, 'updateScraperSettings'])->name('scraper');
            Route::get('/login-as', [AdminController::class, 'loginAsUser'])->name('login-as');
            Route::put('/update', [AdminController::class, 'updateUser'])->name('update');
        });
        Route::post('/users/create', [AdminController::class, 'store'])->name('users.store');

        // Route::controller(PaymentController::class)->prefix('payments')->name('payments.')->group(function () {
        //     Route::get('/', 'adminIndex')->name('index');
        //     Route::post('/{id}/approve', 'approve')->name('approve');
        //     Route::post('/{id}/reject', 'reject')->name('reject');
        // });
    });
});

// Load Isolated SEO Intelligence Module Routes
require __DIR__ . '/seo.php';

