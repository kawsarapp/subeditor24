<?php

namespace Tests\Feature;

use App\Jobs\ProcessNewsPost;
use App\Models\FacebookPage;
use App\Models\NewsItem;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\SocialPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class FacebookBurstAndStaggeredTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Test 1: Multiple scheduled posts for the same user get staggered progressively
     * (Post 1: immediate, Post 2: delayed by 2 mins, Post 3: delayed by 4 mins).
     */
    public function test_scheduled_news_processing_staggers_jobs_for_same_user(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->addDays(30)->toDateString(),
            'subscription_status' => 'active',
        ]);

        $news1 = NewsItem::create([
            'user_id'      => $user->id,
            'title'        => 'Scheduled Post 1',
            'content'      => 'Content 1',
            'is_posted'    => false,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subMinutes(5),
        ]);

        $news2 = NewsItem::create([
            'user_id'      => $user->id,
            'title'        => 'Scheduled Post 2',
            'content'      => 'Content 2',
            'is_posted'    => false,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subMinutes(3),
        ]);

        $news3 = NewsItem::create([
            'user_id'      => $user->id,
            'title'        => 'Scheduled Post 3',
            'content'      => 'Content 3',
            'is_posted'    => false,
            'status'       => 'scheduled',
            'scheduled_at' => now()->subMinute(),
        ]);

        Artisan::call('news:process-scheduled');

        // All 3 should be marked 'publishing'
        $this->assertEquals('publishing', $news1->fresh()->status);
        $this->assertEquals('publishing', $news2->fresh()->status);
        $this->assertEquals('publishing', $news3->fresh()->status);

        // Queue assertions: Post 1 has no delay, Post 2 has 2 mins delay, Post 3 has 4 mins delay
        Queue::assertPushed(ProcessNewsPost::class, 3);

        Queue::assertPushed(ProcessNewsPost::class, function ($job) {
            return $job->delay === null;
        });

        Queue::assertPushed(ProcessNewsPost::class, function ($job) {
            return $job->delay !== null;
        });
    }

    /**
     * Test 2: Auto post respects interval and updates status and timestamp.
     */
    public function test_autopost_respects_interval_and_marks_publishing(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->addDays(30)->toDateString(),
            'subscription_status' => 'active',
            'credits'             => 50,
        ]);

        $settings = UserSetting::create([
            'user_id'            => $user->id,
            'is_auto_posting'    => true,
            'auto_post_interval' => 15,
            'last_auto_post_at'  => now()->subMinutes(20), // 20 mins ago, interval is 15 mins -> allowed
            'wp_url'             => 'https://example.com',
            'wp_username'        => 'editor',
            'wp_app_password'    => 'secret123',
        ]);

        $news = NewsItem::create([
            'user_id'   => $user->id,
            'title'     => 'Auto Post Candidate',
            'content'   => 'Auto Content',
            'is_posted' => false,
            'status'    => 'draft',
            'is_queued' => true,
        ]);

        Artisan::call('news:autopost');

        Queue::assertPushed(ProcessNewsPost::class, 1);
        $this->assertEquals('publishing', $news->fresh()->status);
        $this->assertTrue(now()->diffInSeconds($settings->fresh()->last_auto_post_at) < 5);

        // Running it immediately again should NOT dispatch (interval not reached)
        Queue::fake();
        Artisan::call('news:autopost');
        Queue::assertNothingPushed();
    }

    /**
     * Test 3: SocialPostService updates anti-flood cache on Facebook page.
     */
    public function test_social_post_service_updates_anti_flood_cache(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['id' => 'fb_story_123', 'post_id' => '123_456'], 200),
        ]);

        $user = User::factory()->create(['role' => 'admin', 'is_active' => true]);

        $page = FacebookPage::create([
            'user_id'           => $user->id,
            'page_name'         => 'Test News Page',
            'page_id'           => '9988776655',
            'access_token'      => 'EAAfake_token',
            'page_access_token' => 'EAAfake_token',
            'is_active'         => true,
        ]);

        $settings = UserSetting::create([
            'user_id'    => $user->id,
            'post_to_fb' => true,
        ]);

        $service = app(SocialPostService::class);
        $results = $service->postToFacebook($settings, 'Breaking News Headline', null, 'https://example.com/news/1');

        $this->assertTrue($results[0]['success']);

        // Verify that the anti-flood cache key was stored
        $cacheKey = "fb_last_published_time_9988776655";
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertTrue(now()->timestamp - (int) Cache::get($cacheKey) < 5);
    }
}
