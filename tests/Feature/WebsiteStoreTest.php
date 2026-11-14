<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WebsiteStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_store_website_without_undefined_variable_error()
    {
        $admin = User::factory()->create([
            'role' => 'super_admin'
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('websites.store'), [
            'name' => 'Test Portal',
            'url' => 'https://example.com/news',
            'target_language' => 'en',
            'scrape_interval_minutes' => 10,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('websites', [
            'name' => 'Test Portal',
            'url' => 'https://example.com/news',
            'target_language' => 'en',
            'user_id' => $admin->id,
            'is_central_active' => 1,
        ]);
    }

    public function test_non_super_admin_cannot_store_website()
    {
        $user = User::factory()->create([
            'role' => 'client',
            'permissions' => ['can_scrape'],
        ]);

        $this->actingAs($user);

        $response = $this->post(route('websites.store'), [
            'name' => 'Client Portal',
            'url' => 'https://example.com/news2',
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('websites', [
            'name' => 'Client Portal',
        ]);
    }
}
