<?php

namespace Tests\Feature;

use App\Models\NewsItem;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnglishProfileAndPhotoCardTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_profile_language_preference_to_english()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'tenant@example.com',
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('settings.update-profile'), [
            'name' => 'John Doe',
            'email' => $user->email,
            'phone' => '01711111111',
            'target_language' => 'en',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $settings = UserSetting::where('user_id', $user->id)->first();
        $this->assertNotNull($settings);
        $this->assertEquals('en', $settings->target_language);

        $freshUser = $user->fresh();
        $this->assertEquals('en', $freshUser->getEffectiveLanguage());
        $this->assertTrue($freshUser->isEnglish());
    }

    public function test_user_can_switch_profile_language_back_to_bengali()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'tenant_bn@example.com',
            'is_active' => true,
        ]);

        UserSetting::create([
            'user_id' => $user->id,
            'target_language' => 'en',
        ]);

        $this->assertEquals('en', $user->getEffectiveLanguage());
        $this->assertTrue($user->isEnglish());

        $this->actingAs($user);

        $response = $this->post(route('settings.update-profile'), [
            'name' => 'John Doe',
            'email' => $user->email,
            'phone' => '01711111111',
            'target_language' => 'bn',
        ]);

        $response->assertSessionHasNoErrors();

        $freshUser = $user->fresh();
        $this->assertEquals('bn', $freshUser->getEffectiveLanguage());
        $this->assertFalse($freshUser->isEnglish());
    }

    public function test_staff_or_reporter_inherits_parent_tenant_language_preference()
    {
        $tenantAdmin = User::factory()->create([
            'role' => 'admin',
            'email' => 'parent_admin@example.com',
            'is_active' => true,
        ]);

        UserSetting::create([
            'user_id' => $tenantAdmin->id,
            'target_language' => 'en',
        ]);

        $staffMember = User::factory()->create([
            'role' => 'sub_editor',
            'parent_id' => $tenantAdmin->id,
            'email' => 'subeditor@example.com',
            'is_active' => true,
        ]);

        $this->assertEquals('en', $staffMember->getEffectiveLanguage());
        $this->assertTrue($staffMember->isEnglish());
    }

    public function test_studio_and_custom_card_views_include_english_fonts_and_language_config()
    {
        $user = User::factory()->create([
            'role' => 'admin',
            'email' => 'studio_user@example.com',
            'is_active' => true,
        ]);

        UserSetting::create([
            'user_id' => $user->id,
            'target_language' => 'en',
        ]);

        $news = NewsItem::create([
            'user_id' => $user->id,
            'title' => 'Test News Item For Studio',
            'original_title' => 'Test News Item For Studio',
            'slug' => 'test-news-item-for-studio',
            'source' => 'Manual',
            'is_posted' => 0,
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        // Test News Studio view contains English fonts
        $studioResponse = $this->get(route('news.studio', ['id' => $news->id]));
        $studioResponse->assertStatus(200);
        $studioResponse->assertSee('Inter');
        $studioResponse->assertSee('Montserrat');
        $studioResponse->assertSee('Oswald');
        $studioResponse->assertSee('Poppins');

        // Test Custom Photo Card Studio view contains userLanguage config
        $customCardResponse = $this->get(route('custom-photo-card.index'));
        $customCardResponse->assertStatus(200);
        $customCardResponse->assertSee('userLanguage: "en"', false);
        $customCardResponse->assertSee('Inter');
        $customCardResponse->assertSee('Modern English');
    }
}
