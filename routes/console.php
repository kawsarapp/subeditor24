<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\NewsItem;
use App\Models\User;
use App\Models\Notification;
use App\Jobs\ProcessNewsPost;
use Carbon\Carbon;

// --- AUTO POST COMMAND ---
Artisan::command('news:autopost', function () {
    
    $this->info("🔄 অটোমেশন চেক শুরু হচ্ছে...");

    // ১. একটিভ অটোমেশন ইউজার খোঁজা
    $users = User::whereHas('settings', function($q) {
        $q->where('is_auto_posting', true);
    })->where('is_active', true)->get();

    $this->info("বোট: মোট " . $users->count() . " জন একটিভ ইউজার পাওয়া গেছে।");

    foreach ($users as $user) {
        if (!$user->isSubscriptionActive()) {
            Log::info("Skipping autopost for expired tenant ID: {$user->id}");
            $this->warn("⛔ User {$user->name} (ID: {$user->id}) subscription has expired. Skipping autopost.");
            continue;
        }
		
		if (!$user->hasPermission('can_auto_post')) {
            $this->warn("⛔ User {$user->name} does not have 'can_auto_post' permission. Skipping.");
            continue;
        }
        $this->info("--- চেকিং ইউজার: {$user->name} ---");

        // ক্রেডিট চেক (সুপার এডমিন বাদে)
        if ($user->role !== 'super_admin' && $user->credits <= 0) {
            $this->warn("⛔ User {$user->name} has no credits. Skipping.");
            continue;
        }

        $settings = $user->settings;

        // 🔥 সংশোধিত লজিক: WP অথবা Laravel কানেকশন চেক (লুপের ভেতরে)
        if (!$settings) {
            $this->error("❌ সেটিংস পাওয়া যায়নি। স্কিপ করছি।");
            continue;
        }

        $hasWP = $settings->wp_url && $settings->wp_username;
        $hasLaravel = $settings->post_to_laravel && $settings->laravel_site_url && $settings->laravel_api_token;

        // যদি দুটোর কোনোটিই না থাকে, তবে স্কিপ করবে
        if (!$hasWP && !$hasLaravel) {
            $this->error("❌ সেটিংস নেই (WP বা Laravel কোনোটিই সেট করা নেই)। স্কিপ করছি।");
            continue;
        }

        // ২. সময় চেক করা (Interval Check)
        $lastPostTime = $settings->last_auto_post_at ? Carbon::parse($settings->last_auto_post_at) : null;
        $intervalMinutes = (int) ($settings->auto_post_interval ?? 10);
        if ($intervalMinutes < 1) $intervalMinutes = 10;

        if ($lastPostTime) {
            $nextAllowedTime = $lastPostTime->copy()->addMinutes($intervalMinutes);
            if (now()->lessThan($nextAllowedTime)) {
                $wait = now()->diffInMinutes($nextAllowedTime, false) + 1;
                $this->info("⏳ সময় হয়নি। আরও প্রায় {$wait} মিনিট অপেক্ষা করতে হবে।");
                continue; 
            }
        }

        // ৩. নিউজ খোঁজা
        $newsToPost = NewsItem::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->where('is_posted', false)
            ->where('is_queued', true)
            ->whereNotIn('status', ['published', 'publishing'])
            ->oldest()
            ->first();

        // যদি Queue তে না থাকে, সাধারণ পেন্ডিং নিউজ নাও
        if (!$newsToPost) {
            $newsToPost = NewsItem::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->where('is_posted', false)
                ->whereNotIn('status', ['published', 'publishing'])
                ->oldest() 
                ->first();
        }

        if (!$newsToPost) {
            $this->warn("⚠️ সকল নিউজ পোস্ট করা হয়েছে বা পেন্ডিং নিউজ নাই।");
            continue;
        }

        $this->info("✅ নিউজ পাওয়া গেছে: {$newsToPost->title}");

        try {
            // তাৎক্ষণিক স্ট্যাটাস ও টাইম আপডেট যাতে অন্য ক্রন বা থ্রেড একই নিউজ পুনরায় পিক না করে
            $newsToPost->update(['status' => 'publishing']);
            $settings->last_auto_post_at = now();
            $settings->save();

            // জব ডিসপ্যাচ করা
            ProcessNewsPost::dispatch($newsToPost->id, $user->id);

            $this->info("🚀 Job Dispatched successfully!");
        } catch (\Exception $e) {
            $this->error("❌ Job Dispatch Failed: " . $e->getMessage());
        }
    }
    
    $this->info("🏁 চেক শেষ।");

})->purpose('Auto post news via Queue Job');

// --- ⏰ SCHEDULED NEWS PUBLISHER COMMAND ---
Artisan::command('news:process-scheduled', function () {
    $now = now();
    $scheduledNews = NewsItem::withoutGlobalScopes()
        ->where('is_posted', false)
        ->whereNotNull('scheduled_at')
        ->where('scheduled_at', '<=', $now)
        ->whereNotIn('status', ['published', 'publishing'])
        ->limit(20)
        ->get();

    $userDispatchedCount = [];

    foreach ($scheduledNews as $news) {
        $user = User::find($news->user_id);
        if (!$user) continue;

        if (!$user->isSubscriptionActive()) {
            Log::info("Skipping scheduled news publish [ID: {$news->id}] for expired tenant ID: {$user->id}");
            continue;
        }

        // Stagger posts for the same user by 2 minutes to prevent bursts
        $userDispatchedCount[$user->id] = ($userDispatchedCount[$user->id] ?? 0);
        $staggerMinutes = $userDispatchedCount[$user->id] * 2;
        $userDispatchedCount[$user->id]++;

        Log::info("⏰ Processing Scheduled News [ID: {$news->id}] for User: {$user->name}" . ($staggerMinutes > 0 ? " (staggered by {$staggerMinutes}m)" : ""));
        $news->update(['status' => 'publishing']);

        if ($staggerMinutes > 0) {
            ProcessNewsPost::dispatch($news->id, $user->id, [], true)->delay(now()->addMinutes($staggerMinutes));
        } else {
            ProcessNewsPost::dispatch($news->id, $user->id, [], true);
        }
    }
})->purpose('Publish scheduled news when due time arrives');

// --- 🎬 YOUTUBE AUTO-PILOT CRON ---
Artisan::command('youtube:autopilot-sync', function () {
    $channels = \App\Modules\YouTubeAutomation\Models\YouTubeChannel::where('is_active', true)
        ->where('auto_pilot_enabled', true)
        ->with('user')
        ->get();

    foreach ($channels as $channel) {
        $user = $channel->user ?? User::find($channel->user_id);
        if ($user && !$user->isSubscriptionActive()) {
            Log::info("Skipping YouTube autopilot sync for channel [ID: {$channel->id}] belonging to expired tenant ID: {$user->id}");
            continue;
        }

        \App\Modules\YouTubeAutomation\Jobs\SyncChannelVideosJob::dispatch($channel->id);
    }
})->purpose('Auto-sync and process YouTube channels that have Auto-Pilot enabled');

// --- 🛡️ ERROR & BLOCKED NEWS PRUNING COMMAND ---
Artisan::command('news:clean-error-news', function () {
    $this->info("🧹 Scanning database for error/blocked news items...");
    
    $errorPatterns = [
        '%This site can%',
        '%This site cannot%',
        '%Site can%reached%',
        '%ERR_NAME_NOT_RESOLVED%',
        '%ERR_CONNECTION_%',
        '%ERR_SSL_%',
        '%ERR_TOO_MANY_REDIRECTS%',
        '%ERR_EMPTY_RESPONSE%',
        '%ERR_TIMED_OUT%',
        '%DNS_PROBE_%',
        '%403 Forbidden%',
        '%401 Unauthorized%',
        '%404 Not Found%',
        '%502 Bad Gateway%',
        '%503 Service Unavailable%',
        '%504 Gateway%',
        '%Access Denied%',
        '%Attention Required!%',
        '%Just a moment...%',
        '%Checking your browser%',
        '%Enable JavaScript and cookies%',
        '%Cloudflare Ray ID%',
        '%Ray ID:%',
        '%The page cannot be found%',
        '%Untitled News%',
        '%Welcome to nginx%',
        '%Apache2 Default Page%'
    ];

    $newsItemQuery = NewsItem::withoutGlobalScopes()->where(function ($q) use ($errorPatterns) {
        foreach ($errorPatterns as $pat) {
            $q->orWhere('title', 'like', $pat);
        }
    });
    $newsCount = $newsItemQuery->count();
    $newsItemQuery->delete();

    $centralQuery = \App\Models\CentralNewsPool::where(function ($q) use ($errorPatterns) {
        foreach ($errorPatterns as $pat) {
            $q->orWhere('title', 'like', $pat);
        }
    });
    $centralCount = $centralQuery->count();
    $centralQuery->delete();

    $this->info("✅ Pruning complete! Deleted {$newsCount} error items from news_items and {$centralCount} from central_news_pool.");
})->purpose('Prune error and blocked page news items from database');

// --- 💳 SUBSCRIPTION EXPIRY & STATUS CHECK COMMAND ---
Artisan::command('subscription:check-expiry', function () {
    $this->info("🔄 Checking expired user subscriptions and dispatching alerts...");
    $today = now()->startOfDay();
    $todayStr = now()->toDateString();

    // Query active and trial users with expire_date set (excluding super_admin)
    $users = User::where('role', '!=', 'super_admin')
        ->whereIn('subscription_status', ['active', 'trial'])
        ->whereNotNull('expire_date')
        ->get();

    $expiredCount = 0;
    $alertCount = 0;

    foreach ($users as $user) {
        $expireDate = Carbon::parse($user->expire_date)->startOfDay();

        // 1. Check if expire_date has passed (< $today)
        if ($expireDate->lt($today)) {
            $user->subscription_status = 'expired';
            $user->save();
            $expiredCount++;

            $cacheKey = "sub_expired_notif_{$user->id}_{$todayStr}";
            if (!Cache::has($cacheKey)) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => '⛔ আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়েছে',
                    'message' => 'আপনার প্যাকেজের মেয়াদ শেষ হয়ে গেছে। সেবা চালু রাখতে অনুগ্রহ করে প্যাকেজটি রিনিউ করুন।',
                    'type' => 'subscription_expired',
                    'is_read' => false,
                ]);
                Cache::put($cacheKey, true, now()->addDays(2));
            }

            $this->warn("⛔ User {$user->name} (ID: {$user->id}) marked as expired.");
            continue;
        }

        // 2. Pre-Expiry In-App Alerts (3 Days & 1 Day remaining)
        $daysLeft = (int) round($today->diffInDays($expireDate, false));

        if ($user->isExpiringSoon(3) && $daysLeft === 3) {
            $cacheKey = "sub_expiring_3days_notif_{$user->id}_{$todayStr}";
            if (!Cache::has($cacheKey)) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => '⚠️ সাবস্ক্রিপশনের মেয়াদ ৩ দিন বাকি',
                    'message' => '⚠️ সাবস্ক্রিপশনের মেয়াদ আর মাত্র ৩ দিন বাকি! নিরবচ্ছিন্ন কাজের সুবিধার্থে মেয়াদ শেষ হওয়ার আগেই প্যাকেজটি রিনিউ করে নিন।',
                    'type' => 'subscription_expiring_soon',
                    'is_read' => false,
                ]);
                Cache::put($cacheKey, true, now()->addDays(2));
                $alertCount++;
                $this->info("⚠️ 3-day pre-expiry alert sent to {$user->name} (ID: {$user->id}).");
            }
        } elseif ($daysLeft === 1) {
            $cacheKey = "sub_expiring_1day_notif_{$user->id}_{$todayStr}";
            if (!Cache::has($cacheKey)) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => '🚨 সাবস্ক্রিপশনের মেয়াদ আগামীকাল শেষ হবে',
                    'message' => '🚨 আগামীকাল আপনার সাবস্ক্রিপশন শেষ হবে! জরুরি ভিত্তিতে প্যাকেজটি রিনিউ করে নিন।',
                    'type' => 'subscription_expiring_critical',
                    'is_read' => false,
                ]);
                Cache::put($cacheKey, true, now()->addDays(2));
                $alertCount++;
                $this->warn("🚨 1-day critical pre-expiry alert sent to {$user->name} (ID: {$user->id}).");
            }
        }
    }

    $this->info("✅ Expiry check completed. {$expiredCount} subscriptions marked as expired, {$alertCount} pre-expiry alerts dispatched.");
})->purpose('Check and update expired user subscriptions');

// শিডিউল সেটআপ
Schedule::call(fn() => app(\App\Services\DynamicCronService::class)->recordHeartbeat('cli'))->everyMinute();
Schedule::command('news:autopost')->everyMinute();
Schedule::command('news:process-scheduled')->everyMinute();
Schedule::command('news:check-inactivity')->everyThirtyMinutes();
Schedule::command('news:central-pool-sync')->everyMinute();
Schedule::command('youtube:autopilot-sync')->everyFiveMinutes();
Schedule::command('trends:sync')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('news:clean-error-news')->hourly();
Schedule::command('subscription:check-expiry')->daily();

Schedule::call(function () {
    $settingsList = \App\Models\UserSetting::get();
    $totalDeleted = 0;
    
    foreach ($settingsList as $setting) {
        $days = (int) ($setting->auto_clean_days ?? 7);
        $days = $days > 0 ? $days : 7; // Prevent 0/negative day accidental clearouts
        
        $count = NewsItem::where('user_id', $setting->user_id)
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
            
        $totalDeleted += $count;
    }
    
    // Fallback delete orphaned news (no user attached) after default 7 days
    $orphanedCount = NewsItem::whereNull('user_id')
        ->where('created_at', '<', now()->subDays(7))
        ->delete();
        
    $totalDeleted += $orphanedCount;
    
    // Auto-clean central news pool older than 48 hours to maintain high database performance
    $centralDeleted = \App\Models\CentralNewsPool::where('created_at', '<', now()->subHours(48))->delete();
    $totalDeleted += $centralDeleted;
    
    if ($totalDeleted > 0) {
        Log::info("🧹 Dynamic Auto Clean: {$totalDeleted} garbage items deleted ({$centralDeleted} central pool items).");
    }
})->hourly();

Schedule::command('analytics:clean')->daily();