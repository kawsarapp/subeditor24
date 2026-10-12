<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SaaSRolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles if not present
        (new \Database\Seeders\RoleSeeder())->run();
    }

    /**
     * Test ClientStaffController index returns assignable roles and permissions
     */
    public function test_client_staff_index_renders_with_roles_and_permissions(): void
    {
        $admin = User::create([
            'name' => 'SaaS Client Admin',
            'email' => 'client_admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'staff_limit' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('client.staff.index'));

        $response->assertStatus(200);
        $response->assertViewHas('roles');
        $response->assertViewHas('allPermissions');
        $response->assertViewHas('roleDefaults');
    }

    /**
     * Test creating a staff member auto-assigns role default permissions when not checked
     */
    public function test_store_staff_auto_assigns_role_default_permissions_when_empty(): void
    {
        $admin = User::create([
            'name' => 'SaaS Client Admin',
            'email' => 'client_admin2@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'staff_limit' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('client.staff.store'), [
            'name' => 'Sub Editor John',
            'email' => 'john_subeditor@example.com',
            'password' => 'secret123',
            'role' => 'sub_editor',
            // permissions omitted: should auto-assign sub_editor defaults
        ]);

        $response->assertSessionHas('success');

        $staff = User::where('email', 'john_subeditor@example.com')->first();
        $this->assertNotNull($staff);
        $this->assertEquals($admin->id, $staff->parent_id);
        $this->assertEquals('sub_editor', $staff->role);

        // Sub editor default permissions should be present
        $this->assertTrue($staff->hasPermission('can_latest_news'));
        $this->assertTrue($staff->hasPermission('can_studio'));
        $this->assertTrue($staff->hasPermission('can_custom_photo_card'));
    }

    /**
     * Test tenant isolation enforces staff limit
     */
    public function test_store_staff_enforces_tenant_staff_limit(): void
    {
        $admin = User::create([
            'name' => 'SaaS Client Admin Limit',
            'email' => 'client_limit@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'staff_limit' => 1,
            'is_active' => true,
        ]);

        // Create 1st staff
        $this->actingAs($admin)->post(route('client.staff.store'), [
            'name' => 'Staff 1',
            'email' => 'staff1@example.com',
            'password' => 'secret123',
            'role' => 'staff',
        ])->assertSessionHas('success');

        // Create 2nd staff - should exceed limit of 1
        $response = $this->actingAs($admin)->post(route('client.staff.store'), [
            'name' => 'Staff 2',
            'email' => 'staff2@example.com',
            'password' => 'secret123',
            'role' => 'staff',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(User::where('email', 'staff2@example.com')->first());
    }

    /**
     * Test tenant isolation prevents managing other tenants' staff
     */
    public function test_tenant_isolation_prevents_updating_other_tenants_staff(): void
    {
        $admin1 = User::create([
            'name' => 'Admin 1',
            'email' => 'admin1@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'staff_limit' => 5,
        ]);

        $admin2 = User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'staff_limit' => 5,
        ]);

        $staffAdmin1 = User::create([
            'name' => 'Admin 1 Staff',
            'email' => 'staff_a1@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'parent_id' => $admin1->id,
        ]);

        // Admin 2 attempts to edit Admin 1's staff
        $response = $this->actingAs($admin2)->put("/client/staff/{$staffAdmin1->id}/info", [
            'name' => 'Hacked Staff Name',
            'email' => 'staff_a1@example.com',
            'role' => 'editor',
        ]);

        // Should return 404 (because where('parent_id', $admin2->id)->findOrFail fails)
        $response->assertStatus(404);
        $this->assertEquals('Admin 1 Staff', $staffAdmin1->fresh()->name);
    }

    /**
     * Test Super Admin Roles Matrix access and updating role default permissions
     */
    public function test_super_admin_roles_matrix_access_and_update(): void
    {
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'regular@example.com',
            'password' => bcrypt('password'),
            'role' => 'editor',
        ]);

        // Non-super-admin is forbidden and redirected by AdminMiddleware
        $this->actingAs($regularUser)->get(route('admin.roles.index'))
            ->assertRedirect('/');

        // Super Admin gets 200
        $this->actingAs($superAdmin)->get(route('admin.roles.index'))
            ->assertStatus(200)
            ->assertViewHas('roles')
            ->assertViewHas('categories');

        // Update reporter permissions
        $response = $this->actingAs($superAdmin)->post(route('admin.roles.update-permissions', 'reporter'), [
            'permissions' => ['can_direct_publish', 'can_view_published', 'reporter_direct', 'can_studio'],
        ]);

        $response->assertSessionHas('success');

        $updatedPerms = Role::getDefaultPermissionsFor('reporter');
        $this->assertContains('can_studio', $updatedPerms);
    }

    /**
     * Test route guards: permission:can_manage_staff and permission:can_custom_photo_card
     */
    public function test_route_guards_enforce_required_permissions(): void
    {
        $unauthorizedStaff = User::create([
            'name' => 'Limited Staff',
            'email' => 'limited_staff@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'permissions' => ['can_latest_news'], // Does not have can_manage_staff or can_custom_photo_card
        ]);

        // Staff route blocked
        $this->actingAs($unauthorizedStaff)->get(route('client.staff.index'))
            ->assertStatus(403);

        // Direct FB publish blocked
        $this->actingAs($unauthorizedStaff)->post(route('facebook.direct-publish'), [])
            ->assertStatus(403);

        // Staff with permissions
        $authorizedStaff = User::create([
            'name' => 'Authorized Staff',
            'email' => 'authorized_staff@example.com',
            'password' => bcrypt('password'),
            'role' => 'editor',
            'permissions' => ['can_manage_staff', 'can_custom_photo_card'],
        ]);

        // Allowed into staff index
        $this->actingAs($authorizedStaff)->get(route('client.staff.index'))
            ->assertStatus(200);
    }
}
