<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationDisabledTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_register_redirects_to_login(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_post_register_redirects_to_login_and_creates_no_user(): void
    {
        $initialUserCount = User::count();

        $response = $this->post('/register', [
            'name'                  => 'Test New User',
            'brand_name'            => 'Test Portal',
            'website_url'           => 'https://testportal24.com',
            'email'                 => 'newuser@example.com',
            'phone'                 => '01712345678',
            'password'              => 'Secret1234!',
            'password_confirmation' => 'Secret1234!',
            'terms'                 => '1',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $this->assertEquals($initialUserCount, User::count());
        $this->assertDatabaseMissing('users', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_login_page_does_not_contain_registration_links(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertDontSee(route('register'));
        $response->assertDontSee('Create Account Free');
        $response->assertDontSee('রেজিস্টার');
    }

    public function test_welcome_page_does_not_contain_registration_link(): void
    {
        $response = $this->view('welcome');

        $response->assertDontSee(route('register'));
        $response->assertDontSee('Register');
    }

    public function test_pricing_page_does_not_contain_registration_links(): void
    {
        $response = $this->get(route('pricing'));

        $response->assertStatus(200);
        $response->assertDontSee(route('register'));
        $response->assertDontSee('ফ্রি সাইন-আপ করুন');
    }
}
