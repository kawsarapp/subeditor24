<?php

namespace Tests\Feature;

use App\Models\FacebookPage;
use App\Models\NewsItem;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudioFirstCommentPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_photo_card_publish_with_first_comment(): void
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
            'first_comment' => 'https://news.example.com/story-123',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'post_id' => '10001_photo_post',
        ]);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/photos');
        });

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/10001_photo_post/comments') &&
                   $request['message'] === 'https://news.example.com/story-123';
        });
    }
}
