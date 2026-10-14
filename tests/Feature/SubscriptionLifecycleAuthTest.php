<?php

namespace Tests\Feature;

use App\Models\PricingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionLifecycleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_tenant_owner_can_login_and_redirects_to_billing(): void
    {
        $tenantOwner = User::factory()->create([
            'email'               => 'owner@example.com',
            'password'            => bcrypt('password123'),
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(5)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'owner@example.com',
            'password' => 'password123',
        ]);

        // Should not hard logout or reject; redirects to billing.my-subscription
        $response->assertRedirect(route('billing.my-subscription'));
        $response->assertSessionHas('warning');
        $this->assertAuthenticatedAs($tenantOwner);
    }

    public function test_expired_tenant_owner_can_access_billing_and_pricing_routes(): void
    {
        $tenantOwner = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(5)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $this->actingAs($tenantOwner);

        // Can access my-subscription
        $response = $this->get(route('billing.my-subscription'));
        $response->assertOk();

        // Can access pricing
        $pricingResponse = $this->get(route('pricing.index'));
        $pricingResponse->assertOk();
    }

    public function test_expired_tenant_owner_is_blocked_from_operational_routes(): void
    {
        $tenantOwner = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(5)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $this->actingAs($tenantOwner);

        // Attempting to visit /news redirects to billing
        $response = $this->get(route('news.index'));
        $response->assertRedirect(route('billing.my-subscription'));
        $response->assertSessionHas('warning');

        // AJAX request returns 403 JSON
        $ajaxResponse = $this->getJson(route('news.index'));
        $ajaxResponse->assertStatus(403);
        $ajaxResponse->assertJson([
            'success'              => false,
            'subscription_expired' => true,
            'redirect_url'         => route('billing.my-subscription'),
        ]);
    }

    public function test_team_member_of_expired_tenant_is_blocked_on_login(): void
    {
        $parent = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(5)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $staff = User::factory()->create([
            'email'     => 'reporter@example.com',
            'password'  => bcrypt('password123'),
            'role'      => 'reporter',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $response = $this->post(route('login.post'), [
            'email'    => 'reporter@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_team_member_of_expired_tenant_is_blocked_by_middleware(): void
    {
        $parent = User::factory()->create([
            'role'                => 'admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(5)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $editor = User::factory()->create([
            'role'      => 'editor',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $this->actingAs($editor);

        // Web request logs out and redirects to login
        $response = $this->get(route('news.index'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_super_admin_bypasses_subscription_restrictions(): void
    {
        $superAdmin = User::factory()->create([
            'email'               => 'super@example.com',
            'password'            => bcrypt('password123'),
            'role'                => 'super_admin',
            'is_active'           => true,
            'expire_date'         => now()->subDays(10)->toDateString(),
            'subscription_status' => 'expired',
        ]);

        $this->actingAs($superAdmin);

        $response = $this->get(route('news.index'));
        $response->assertOk();
    }
}
