<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RolePermissionRbacTest extends TestCase
{
    use RefreshDatabase;
    public function test_roles_table_contains_all_system_roles(): void
    {
        $roles = Role::pluck('name')->toArray();

        $this->assertContains('super_admin', $roles);
        $this->assertContains('admin', $roles);
        $this->assertContains('editor', $roles);
        $this->assertContains('sub_editor', $roles);
        $this->assertContains('staff', $roles);
        $this->assertContains('reporter', $roles);
    }

    public function test_all_permissions_dictionary_returns_34_permissions(): void
    {
        $dictionary = Role::getAllPermissionsDictionary();

        $this->assertCount(6, $dictionary);
        $this->assertArrayHasKey('Editorial', $dictionary);
        $this->assertArrayHasKey('Creative Studio', $dictionary);
        $this->assertArrayHasKey('AI & Automation', $dictionary);
        $this->assertArrayHasKey('SEO Intelligence', $dictionary);
        $this->assertArrayHasKey('Team & Reporters', $dictionary);
        $this->assertArrayHasKey('Settings', $dictionary);

        $slugs = Role::getAllPermissionSlugs();
        $this->assertCount(34, $slugs);
    }

    public function test_user_role_boolean_helpers(): void
    {
        $superAdmin = new User(['role' => 'super_admin']);
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertFalse($superAdmin->isAdmin());

        $admin = new User(['role' => 'admin']);
        $this->assertTrue($admin->isAdmin());

        $editor = new User(['role' => 'editor']);
        $this->assertTrue($editor->isEditor());

        $subEditor = new User(['role' => 'sub_editor']);
        $this->assertTrue($subEditor->isSubEditor());

        $reporter = new User(['role' => 'reporter']);
        $this->assertTrue($reporter->isReporter());

        $staff = new User(['role' => 'staff']);
        $this->assertTrue($staff->isStaff());
    }

    public function test_has_permission_super_admin_always_true(): void
    {
        $superAdmin = new User(['role' => 'super_admin']);
        $this->assertTrue($superAdmin->hasPermission('can_latest_news'));
        $this->assertTrue($superAdmin->hasPermission('can_studio'));
        $this->assertTrue($superAdmin->hasPermission('any_random_permission'));
    }

    public function test_has_permission_explicit_override_overrules_role(): void
    {
        $staff = new User([
            'role' => 'staff',
            'permissions' => ['can_scrape'],
        ]);

        $this->assertTrue($staff->hasPermission('can_scrape'));
        $this->assertFalse($staff->hasPermission('can_studio'));
    }

    public function test_has_permission_fallback_to_role_default_permissions(): void
    {
        $editor = User::create([
            'name' => 'Editor User',
            'email' => 'editor_test@example.com',
            'password' => bcrypt('password'),
            'role' => 'editor',
            'permissions' => null,
        ]);

        $this->assertTrue($editor->hasPermission('can_latest_news'));
        $this->assertTrue($editor->hasPermission('can_central_feed'));
        $this->assertTrue($editor->hasPermission('can_studio'));
        $this->assertFalse($editor->hasPermission('can_scrape'));

        $editor->delete();
    }

    public function test_laravel_gates_are_defined_for_all_permissions(): void
    {
        $slugs = Role::getAllPermissionSlugs();
        $user = new User(['role' => 'super_admin']);

        foreach ($slugs as $slug) {
            $this->assertTrue(Gate::forUser($user)->allows($slug), "Gate {$slug} should allow super_admin");
        }
    }
}
