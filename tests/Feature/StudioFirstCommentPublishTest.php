<?php

namespace Tests\Feature;

use App\Models\FacebookPage;
use App\Models\NewsItem;
use App\Models\User;
use App\Models\UserSetting;
use App\Services\SocialPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudioFirstCommentPublishTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Custom Photo Card: Manual first comment should be posted when provided.
     */
    public function test_custom_photo_card_publish_with_manual_first_comment(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Studio Test Page',
            'page_id'      => '555444333',
            'access_token' => 'EAABstudioToken',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/555444333/photos' => Http::response([
                'id' => '10001_photo_post',
            ], 200),
            'https://graph.facebook.com/v19.0/10001_photo_post/comments' => Http::response([
                'id' => 'comment_9999',
            ], 200),
        ]);

        // 1x1 transparent PNG base64
        $dummyBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $response = $this->actingAs($user)->postJson(route('custom-photo-card.publish-facebook'), [
            'page_id'       => $page->id,
            'image'         => $dummyBase64,
            'caption'       => 'Custom Photo Card Caption',
            'first_comment' => 'Manual comment text',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'post_id' => '10001_photo_post',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/10001_photo_post/comments') &&
                   $request['message'] === 'Manual comment text';
        });
    }

    /**
     * Custom Photo Card: When first_comment is empty, NO automatic comment should be made.
     */
    public function test_custom_photo_card_publish_without_first_comment_does_not_post_comment(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Studio Manual Only Page',
            'page_id'      => '555444333',
            'access_token' => 'EAABstudioToken',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/555444333/photos' => Http::response([
                'id' => '20002_photo_post',
            ], 200),
        ]);

        $dummyBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $response = $this->actingAs($user)->postJson(route('custom-photo-card.publish-facebook'), [
            'page_id'       => $page->id,
            'image'         => $dummyBase64,
            'caption'       => 'Card without first comment',
            'first_comment' => '',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'post_id' => '20002_photo_post',
        ]);

        // Verify comments endpoint was NOT called
        Http::assertNotSent(function ($request) {
            return str_contains($request->url(), '/comments');
        });
    }

    /**
     * News Card Publishing: Automatically sets the news link in the first comment when empty.
     */
    public function test_news_card_publish_auto_first_comment_with_news_link(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $settings = UserSetting::create([
            'user_id' => $user->id,
            'wp_url'  => 'https://mynews.com',
        ]);

        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'News Card Page',
            'page_id'      => '666777888',
            'access_token' => 'EAABnewsCardToken',
            'is_active'    => true,
            'comment_link' => false, // Does not require comment_link to be true for studio news item
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/666777888/feed' => Http::response([
                'id' => '30003_feed_post',
            ], 200),
            'https://graph.facebook.com/v19.0/30003_feed_post/comments' => Http::response([
                'id' => 'comment_7777',
            ], 200),
        ]);

        $service = app(SocialPostService::class);
        $results = $service->postToFacebook(
            $settings,
            'News title caption',
            null,
            'https://mynews.com/2026/my-published-story',
            [$page->id],
            null // Empty first comment -> should automatically use news link
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]['success']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/30003_feed_post/comments') &&
                   $request['message'] === 'বিস্তারিত পড়ুন: https://mynews.com/2026/my-published-story';
        });
    }

    /**
     * News Card Publishing: Uses custom first comment if provided.
     */
    public function test_news_card_publish_with_custom_first_comment(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $settings = UserSetting::create([
            'user_id' => $user->id,
            'wp_url'  => 'https://mynews.com',
        ]);

        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'News Card Page',
            'page_id'      => '666777888',
            'access_token' => 'EAABnewsCardToken',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/666777888/feed' => Http::response([
                'id' => '30004_feed_post',
            ], 200),
            'https://graph.facebook.com/v19.0/30004_feed_post/comments' => Http::response([
                'id' => 'comment_8888',
            ], 200),
        ]);

        $service = app(SocialPostService::class);
        $results = $service->postToFacebook(
            $settings,
            'News title caption',
            null,
            'https://mynews.com/2026/my-published-story',
            [$page->id],
            'Custom studio comment text'
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]['success']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/30004_feed_post/comments') &&
                   $request['message'] === 'Custom studio comment text';
        });
    }

    /**
     * Enforce target_post_url priority, staff parent inheritance, and zero original_link leak in NewsItem live_url.
     */
    public function test_news_item_live_url_prefers_target_post_url_and_inherits_parent_settings(): void
    {
        $admin = User::factory()->create(['role' => 'client']);
        UserSetting::create([
            'user_id' => $admin->id,
            'wp_url'  => 'https://client-portal.com',
        ]);

        $reporter = User::factory()->create([
            'role'      => 'reporter',
            'parent_id' => $admin->id,
        ]);

        // 1. When target_post_url is present, it must be returned directly
        $newsWithTarget = NewsItem::create([
            'user_id'         => $reporter->id,
            'title'           => 'Staff news story',
            'original_link'   => 'https://source.com/leaked-source-news',
            'target_post_url' => 'https://client-portal.com/news/target-story',
            'wp_post_id'      => 456,
        ]);

        $this->assertEquals('https://client-portal.com/news/target-story', $newsWithTarget->live_url);

        // 2. When target_post_url is empty, it calculates from parent admin settings
        $newsWithoutTarget = NewsItem::create([
            'user_id'       => $reporter->id,
            'title'         => 'Staff news story 2',
            'original_link' => 'https://source.com/leaked-source-news-2',
            'wp_post_id'    => 789,
        ]);

        $this->assertEquals('https://client-portal.com/?p=789', $newsWithoutTarget->live_url);

        // 3. When neither target_post_url nor wp_post_id is present, NEVER leak original_link
        $draftNews = NewsItem::create([
            'user_id'       => $reporter->id,
            'title'         => 'Draft news story',
            'original_link' => 'https://source.com/leaked-source-news-3',
        ]);

        $this->assertNull($draftNews->live_url);
    }

    /**
     * SocialPostService uses target_post_url and purges original_link from comments.
     */
    public function test_social_post_service_never_leaks_original_link_and_resolves_target_post_url(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $settings = UserSetting::create([
            'user_id' => $user->id,
            'wp_url'  => 'https://mynews.com',
        ]);

        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Security Check Page',
            'page_id'      => '888999000',
            'access_token' => 'EAABtoken',
            'is_active'    => true,
        ]);

        $newsItem = NewsItem::create([
            'user_id'         => $user->id,
            'title'           => 'Test Leaked Link Cleanup',
            'original_link'   => 'https://source-leak.com/breaking/12345',
            'target_post_url' => 'https://mynews.com/2026/purified-story',
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/888999000/feed' => Http::response([
                'id' => '40004_feed_post',
            ], 200),
            'https://graph.facebook.com/v19.0/40004_feed_post/comments' => Http::response([
                'id' => 'comment_9999',
            ], 200),
        ]);

        $service = app(SocialPostService::class);
        $results = $service->postToFacebook(
            $settings,
            'News caption',
            null,
            $newsItem, // passing NewsItem object
            [$page->id],
            'বিস্তারিত পড়ুন: https://source-leak.com/breaking/12345' // Contains original source link
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]['success']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/40004_feed_post/comments') &&
                   $request['message'] === 'বিস্তারিত পড়ুন: https://mynews.com/2026/purified-story' &&
                   !str_contains($request['message'], 'source-leak.com');
        });
    }

    /**
     * Photo Card Posting: Comments on photo card automatically with fallback to photo ID if feed fails.
     */
    public function test_photo_card_auto_first_comment_with_fallback_to_photo_id(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        $settings = UserSetting::create([
            'user_id' => $user->id,
            'wp_url'  => 'https://mynews.com',
        ]);

        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Photo Card Page',
            'page_id'      => '111222333',
            'access_token' => 'EAABtokenPhotoCard',
            'is_active'    => true,
        ]);

        $dummyBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        Http::fake([
            'https://graph.facebook.com/v19.0/111222333/photos' => Http::response([
                'id' => 'photo_node_999',
            ], 200),
            'https://graph.facebook.com/v19.0/111222333/feed' => Http::response([
                'id' => 'feed_post_888',
            ], 200),
            // When comment on feed_post fails (e.g. #100 object does not support comments):
            'https://graph.facebook.com/v19.0/feed_post_888/comments' => Http::response([
                'error' => ['message' => 'Comments can not be attached to this post', 'code' => 100],
            ], 400),
            // Fallback comment on photo_node succeeds:
            'https://graph.facebook.com/v19.0/photo_node_999/comments' => Http::response([
                'id' => 'comment_success_123',
            ], 200),
        ]);

        $service = app(SocialPostService::class);
        $results = $service->postToFacebook(
            $settings,
            'Breaking News Photo Card Caption',
            $dummyBase64, // Providing the photo card!
            'https://estatenews.net/news/breaking-story-100',
            [$page->id],
            null // Empty -> should auto-generate 'বিস্তারিত পড়ুন: ...'
        );

        $this->assertCount(1, $results);
        $this->assertTrue($results[0]['success']);

        // Verify fallback comment on photo_node was called and succeeded
        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/photo_node_999/comments') &&
                   $request['message'] === 'বিস্তারিত পড়ুন: https://estatenews.net/news/breaking-story-100';
        });
    }
}
