<?php

namespace Tests\Feature;

use App\Models\FacebookPage;
use App\Models\User;
use App\Services\SocialPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DirectFacebookPublishTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_direct_post_text_only_with_first_comment(): void
    {
        $user = User::factory()->create();
        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Test News Page',
            'page_id'      => '1234567890',
            'access_token' => 'EAABtesttoken123',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/1234567890/feed' => Http::response([
                'id' => '1234567890_987654321',
            ], 200),
            'https://graph.facebook.com/v19.0/1234567890_987654321/comments' => Http::response([
                'id' => 'comment_id_555',
            ], 200),
        ]);

        $service = new SocialPostService();
        $result = $service->publishDirectPost($page, 'Breaking News: Direct Post!', null, 'First comment here with details.');

        $this->assertTrue($result['success']);
        $this->assertEquals('1234567890_987654321', $result['post_id']);
        $this->assertEquals('published', $result['comment_status']);
        $this->assertEquals('comment_id_555', $result['comment_id']);
        $this->assertNull($result['error']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v19.0/1234567890/feed' &&
                   $request['message'] === 'Breaking News: Direct Post!' &&
                   $request['access_token'] === 'EAABtesttoken123';
        });

        Http::assertSent(function ($request) {
            return $request->url() === 'https://graph.facebook.com/v19.0/1234567890_987654321/comments' &&
                   $request['message'] === 'First comment here with details.' &&
                   $request['access_token'] === 'EAABtesttoken123';
        });
    }

    public function test_publish_direct_post_with_image_file(): void
    {
        $user = User::factory()->create();
        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Image News Page',
            'page_id'      => '999888777',
            'access_token' => 'EAABtokenPhoto',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/999888777/photos' => Http::response([
                'id'      => 'photo_111',
                'post_id' => '999888777_post_222',
            ], 200),
        ]);

        $file = UploadedFile::fake()->image('test_card.jpg', 600, 400);

        $service = new SocialPostService();
        $result = $service->publishDirectPost($page, 'Photo caption here', $file, null);

        $this->assertTrue($result['success']);
        $this->assertEquals('999888777_post_222', $result['post_id']);
        $this->assertEquals('skipped', $result['comment_status']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/photos');
        });
    }

    public function test_first_comment_failure_does_not_break_main_post_success(): void
    {
        $user = User::factory()->create();
        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Resilient Page',
            'page_id'      => '111222333',
            'access_token' => 'EAABtokenResilient',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/111222333/feed' => Http::response([
                'id' => '111222333_post_444',
            ], 200),
            'https://graph.facebook.com/v19.0/111222333_post_444/comments' => Http::response([
                'error' => ['message' => 'Rate limit exceeded on comments'],
            ], 400),
        ]);

        $service = new SocialPostService();
        $result = $service->publishDirectPost($page, 'Main post should succeed', null, 'Failed comment');

        $this->assertTrue($result['success']);
        $this->assertEquals('111222333_post_444', $result['post_id']);
        $this->assertEquals('failed', $result['comment_status']);
        $this->assertNull($result['error']);
    }

    public function test_publish_to_multiple_pages_in_loop(): void
    {
        $user = User::factory()->create();
        $page1 = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Page 1',
            'page_id'      => 'page_1_id',
            'access_token' => 'token_1',
            'is_active'    => true,
        ]);
        $page2 = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Page 2',
            'page_id'      => 'page_2_id',
            'access_token' => 'token_2',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/page_1_id/feed' => Http::response(['id' => 'post_1'], 200),
            'https://graph.facebook.com/v19.0/page_2_id/feed' => Http::response(['id' => 'post_2'], 200),
        ]);

        $service = new SocialPostService();
        $results = $service->publishDirectPost([$page1, $page2], 'Multi-page post message');

        $this->assertCount(2, $results);
        $this->assertEquals('Page 1', $results[0]['page_name']);
        $this->assertTrue($results[0]['success']);
        $this->assertEquals('Page 2', $results[1]['page_name']);
        $this->assertTrue($results[1]['success']);
    }

    public function test_controller_direct_publish_endpoint(): void
    {
        $user = User::factory()->create();
        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Controller Page',
            'page_id'      => 'ctrl_page_id',
            'access_token' => 'ctrl_token',
            'is_active'    => true,
        ]);

        Http::fake([
            'https://graph.facebook.com/v19.0/ctrl_page_id/feed' => Http::response(['id' => 'post_ctrl_123'], 200),
            'https://graph.facebook.com/v19.0/post_ctrl_123/comments' => Http::response(['id' => 'comment_ctrl_456'], 200),
        ]);

        $response = $this->actingAs($user)->postJson('/facebook/direct-publish', [
            'pages'         => [$page->id],
            'message'       => 'Posted from Controller endpoint',
            'first_comment' => 'First comment from Controller',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'     => true,
            'all_success' => true,
        ]);
        $response->assertJsonFragment([
            'page_name'      => 'Controller Page',
            'post_id'        => 'post_ctrl_123',
            'comment_status' => 'published',
            'comment_id'     => 'comment_ctrl_456',
        ]);
    }

    public function test_controller_validation_requires_message_if_no_image(): void
    {
        $user = User::factory()->create();
        $page = FacebookPage::create([
            'user_id'      => $user->id,
            'page_name'    => 'Valid Page',
            'page_id'      => 'valid_id',
            'access_token' => 'valid_token',
            'is_active'    => true,
        ]);

        $response = $this->actingAs($user)->postJson('/facebook/direct-publish', [
            'pages' => [$page->id],
            // missing message and image
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message']);
    }

    public function test_controller_tenant_isolation_prevents_posting_to_other_users_pages(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $pageOfUser2 = FacebookPage::create([
            'user_id'      => $user2->id,
            'page_name'    => 'User 2 Page',
            'page_id'      => 'u2_page_id',
            'access_token' => 'u2_token',
            'is_active'    => true,
        ]);

        $response = $this->actingAs($user1)->postJson('/facebook/direct-publish', [
            'pages'   => [$pageOfUser2->id],
            'message' => 'Should fail due to tenant isolation',
        ]);

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
        ]);
    }
}
