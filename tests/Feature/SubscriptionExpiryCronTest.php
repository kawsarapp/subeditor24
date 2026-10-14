<?php

namespace Tests\Feature;

use App\Jobs\ProcessNewsPost;
use App\Models\NewsItem;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserSetting;
use App\Modules\YouTubeAutomation\Jobs\SyncChannelVideosJob;
use App\Modules\YouTubeAutomation\Models\YouTubeChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SubscriptionExpiryCronTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test 1: Active user whose expire_date is in the past gets marked as expired
     * and receives a subscription_expired in-app notification.
     */
    public function test_subscription_check_expiry_marks_expired_users_and_creates_notification(): void
    {
        $user = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDay()->toDateString(),
            'subscription_status' => 'active',
        ]);

        $exitCode = Artisan::call('subscription:check-expiry');
        $this->assertSame(0, $exitCode);

        // Status must be updated to expired
        $this->assertSame('expired', $user->fresh()->subscription_status);

        // Notification must exist with type subscription_expired and Bengali expiration message
        $notification = Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->first();

        $this->assertNotNull($notification, 'Expected notification to be created for expired user.');
        $this->assertSame('subscription_expired', $notification->type);
        $this->assertStringContainsString('মেয়াদ শেষ', $notification->title);
    }

    /**
     * Test 2: Active user with exactly 3 days remaining receives a 3-day warning notification
     * and their status remains active.
     */
    public function test_subscription_check_expiry_sends_3_day_warning_notification(): void
    {
        $user = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->addDays(3)->toDateString(),
            'subscription_status' => 'active',
        ]);

        $exitCode = Artisan::call('subscription:check-expiry');
        $this->assertSame(0, $exitCode);

        // Status must remain active
        $this->assertSame('active', $user->fresh()->subscription_status);

        // Notification must exist with type subscription_expiring_soon and Bengali warning title
        $notification = Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->first();

        $this->assertNotNull($notification, 'Expected 3-day warning notification to be created.');
        $this->assertSame('subscription_expiring_soon', $notification->type);
        $this->assertStringContainsString('৩ দিন', $notification->title);
    }

    /**
     * Test 3: Active user with 1 day remaining receives critical 1-day warning notification
     * and their status remains active.
     */
    public function test_subscription_check_expiry_sends_1_day_warning_notification(): void
    {
        $user = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->addDay()->toDateString(),
            'subscription_status' => 'active',
        ]);

        $exitCode = Artisan::call('subscription:check-expiry');
        $this->assertSame(0, $exitCode);

        // Status must remain active
        $this->assertSame('active', $user->fresh()->subscription_status);

        // Notification must exist with type subscription_expiring_critical and Bengali warning title
        $notification = Notification::where('notifiable_id', $user->id)
            ->where('notifiable_type', User::class)
            ->first();

        $this->assertNotNull($notification, 'Expected 1-day warning notification to be created.');
        $this->assertSame('subscription_expiring_critical', $notification->type);
        $this->assertStringContainsString('আগামীকাল', $notification->title);
    }

    /**
     * Test 4: Running subscription:check-expiry multiple times deduplicates notifications
     * via Cache, ensuring the user only gets 1 notification.
     */
    public function test_subscription_check_expiry_does_not_duplicate_notifications_on_subsequent_runs(): void
    {
        $user = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDay()->toDateString(),
            'subscription_status' => 'active',
        ]);

        // First run
        Artisan::call('subscription:check-expiry');
        $this->assertSame(1, Notification::where('notifiable_id', $user->id)->count());

        // Second run on the same day
        Artisan::call('subscription:check-expiry');
        $this->assertSame(1, Notification::where('notifiable_id', $user->id)->count());

        // Also test warning notification deduplication
        $warningUser = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->addDays(3)->toDateString(),
            'subscription_status' => 'active',
        ]);

        // First run for warningUser
        Artisan::call('subscription:check-expiry');
        $this->assertSame(1, Notification::where('notifiable_id', $warningUser->id)->count());

        // Second run for warningUser
        Artisan::call('subscription:check-expiry');
        $this->assertSame(1, Notification::where('notifiable_id', $warningUser->id)->count());
    }

    /**
     * Test 5: Super admin with a past expire_date is excluded from expiration
     * and is never marked as expired, receiving no notification.
     */
    public function test_super_admin_is_never_marked_expired(): void
    {
        $superAdmin = User::factory()->create([
            'role'                => 'super_admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(30)->toDateString(),
            'subscription_status' => 'active',
        ]);

        $exitCode = Artisan::call('subscription:check-expiry');
        $this->assertSame(0, $exitCode);

        // Super admin status remains active
        $this->assertSame('active', $superAdmin->fresh()->subscription_status);

        // No notification should be created for super admin
        $notificationCount = Notification::where('notifiable_id', $superAdmin->id)->count();
        $this->assertSame(0, $notificationCount);
    }

    /**
     * Test 6: news:autopost worker guard gracefully skips expired users,
     * preventing any news from being queued or published.
     */
    public function test_news_autopost_skips_expired_users(): void
    {
        Queue::fake();

        // Expired tenant user
        $expiredUser = User::factory()->create([
            'name'                => 'Expired Tenant',
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(2)->toDateString(),
            'subscription_status' => 'expired',
            'credits'             => 100,
        ]);

        UserSetting::create([
            'user_id'         => $expiredUser->id,
            'is_auto_posting' => true,
            'wp_url'          => 'https://expired-portal.com',
            'wp_username'     => 'admin',
        ]);

        $newsItem = NewsItem::create([
            'user_id'       => $expiredUser->id,
            'title'         => 'Test News on Expired Account',
            'original_link' => 'https://news-source.com/article-1',
            'is_posted'     => false,
            'is_queued'     => true,
        ]);

        $exitCode = Artisan::call('news:autopost');
        $output = Artisan::output();

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('subscription has expired. Skipping autopost', $output);

        // Ensure no queue jobs dispatched for expired user
        Queue::assertNotPushed(ProcessNewsPost::class);

        // Ensure news item was not marked as posted
        $this->assertFalse($newsItem->fresh()->is_posted);
    }

    /**
     * Worker Guard: news:process-scheduled worker guard skips expired users.
     */
    public function test_news_process_scheduled_skips_expired_users(): void
    {
        Queue::fake();

        $expiredUser = User::factory()->create([
            'name'                => 'Expired Scheduled Tenant',
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(2)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $scheduledNews = NewsItem::create([
            'user_id'       => $expiredUser->id,
            'title'         => 'Scheduled News on Expired Account',
            'original_link' => 'https://news-source.com/article-scheduled',
            'is_posted'     => false,
            'scheduled_at'  => now()->subMinute(),
            'status'        => 'scheduled',
        ]);

        $exitCode = Artisan::call('news:process-scheduled');
        $this->assertSame(0, $exitCode);

        // Ensure no job was dispatched for the expired tenant
        Queue::assertNotPushed(ProcessNewsPost::class);
        $this->assertFalse($scheduledNews->fresh()->is_posted);
        $this->assertSame('scheduled', $scheduledNews->fresh()->status);
    }

    /**
     * Worker Guard: youtube:autopilot-sync worker guard skips channels belonging to expired users.
     */
    public function test_youtube_autopilot_sync_skips_expired_users(): void
    {
        Queue::fake();

        $expiredUser = User::factory()->create([
            'name'                => 'Expired YT Tenant',
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(5)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $channel = YouTubeChannel::create([
            'user_id'            => $expiredUser->id,
            'channel_id'         => 'UC_TEST_CHANNEL_123',
            'channel_title'      => 'Expired Channel',
            'access_token'       => 'fake_access_token',
            'is_active'          => true,
            'auto_pilot_enabled' => true,
        ]);

        $exitCode = Artisan::call('youtube:autopilot-sync');
        $this->assertSame(0, $exitCode);

        // Ensure SyncChannelVideosJob was not pushed
        Queue::assertNotPushed(SyncChannelVideosJob::class);
    }

    /**
     * Trial users whose trial period has passed are also marked expired.
     */
    public function test_trial_user_expires_properly(): void
    {
        $trialUser = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDay()->toDateString(),
            'subscription_status' => 'trial',
        ]);

        Artisan::call('subscription:check-expiry');

        $this->assertSame('expired', $trialUser->fresh()->subscription_status);

        $notification = Notification::where('notifiable_id', $trialUser->id)->first();
        $this->assertNotNull($notification);
        $this->assertSame('subscription_expired', $notification->type);
    }

    /**
     * Active users with more than 3 days remaining receive no warning notification.
     */
    public function test_active_user_with_safe_margin_receives_no_notification(): void
    {
        $safeUser = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->addDays(15)->toDateString(),
            'subscription_status' => 'active',
        ]);

        Artisan::call('subscription:check-expiry');

        $this->assertSame('active', $safeUser->fresh()->subscription_status);
        $this->assertSame(0, Notification::where('notifiable_id', $safeUser->id)->count());
    }
}
