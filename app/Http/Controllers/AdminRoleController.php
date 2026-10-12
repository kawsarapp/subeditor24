<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminRoleController extends Controller
{
    /**
     * Display the Roles & Permissions Matrix for Super Admin
     */
    public function index()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized Access!');
        }

        $roles = Role::getRolesWithPermissions();
        $categories = Role::getPermissionsByCategory();
        $allPermissions = Role::getAllPermissions();

        // Count of active users per role
        $userCounts = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        return view('admin.roles.index', compact('roles', 'categories', 'allPermissions', 'userCounts'));
    }

    /**
     * Update a role's default permissions
     */
    public function updatePermissions(Request $request, string $role)
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized Access!');
        }

        $assignable = array_keys(Role::getAssignableRoles());
        if (!in_array($role, $assignable)) {
            return back()->with('error', "অবৈধ রোল '{$role}' প্রদান করা হয়েছে!");
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'sync_existing' => 'nullable|boolean',
        ]);

        $permissions = $request->input('permissions', []);

        // Update default permissions in DB/Cache
        Role::updateDefaultPermissions($role, $permissions);

        // Optionally cascade to existing users of that role if requested
        if ($request->boolean('sync_existing')) {
            User::where('role', $role)->update([
                'permissions' => array_values(array_unique($permissions))
            ]);
        }

        $roleMeta = Role::getAssignableRoles()[$role] ?? null;
        $displayName = $roleMeta['display_name'] ?? ucfirst($role);

        return back()->with('success', "{$displayName} রোলের ডিফল্ট পারমিশন সফলভাবে আপডেট করা হয়েছে!");
    }
}
