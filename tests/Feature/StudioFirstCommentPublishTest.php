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
}
