<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSetting;
use App\Models\Website;
use App\Models\NewsItem;
use App\Models\CreditHistory;
use App\Models\Template;
use App\Models\Role;
use App\Models\Department;
use App\Models\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientStaffController extends Controller
{
    /**
     * স্টাফ লিস্ট, ফিল্টারিং এবং অ্যাডমিনের ডাটা লোড করা
     */
    public function index(Request $request)
    {
        $admin = Auth::user();

        // ১. সিকিউরিটি চেক: শুধু অ্যাডমিন বা সুপার অ্যাডমিন অথবা যাদের can_manage_staff পারমিশন আছে
        if (!in_array($admin->role, ['admin', 'super_admin']) && !$admin->hasPermission('can_manage_staff')) {
            abort(403, 'Unauthorized action.');
        }

        // ২. পারমিশন চেক
        $permissions = is_array($admin->permissions) ? $admin->permissions : json_decode($admin->permissions, true) ?? [];
        if ($admin->role !== 'super_admin' && !in_array('can_manage_staff', $permissions) && $admin->role !== 'admin') {
            return back()->with('error', 'আপনার স্টাফ তৈরি করার অনুমতি নেই।');
        }

        // 🔍 ফিল্টার প্যারামিটারগুলো নেওয়া
        $search = $request->input('search');
        $dateFilter = $request->input('date_filter', 'all');
        $roleFilter = $request->input('role');

        // ৩. টেন্যান্ট আইসোলেশন: শুধুমাত্র বর্তমান অ্যাডমিনের আন্ডারে থাকা মেম্বাররা লোড হবে
        $assignableRoleKeys = array_keys(Role::getAssignableRoles());

        $staffQuery = User::where('parent_id', $admin->id)
            ->where(function($q) use ($assignableRoleKeys) {
                $q->whereIn('role', $assignableRoleKeys)
                  ->orWhereNull('role');
            })
            ->with(['accessibleWebsites', 'settings', 'department', 'designation', 'roleRelation']);

        if (!empty($roleFilter) && in_array($roleFilter, $assignableRoleKeys)) {
            $staffQuery->where('role', $roleFilter);
        }

        // 🔍 সার্চ লজিক অ্যাপ্লাই
        if (!empty($search)) {
            $staffQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $staffs = $staffQuery->latest()->paginate(10)->through(function($staff) use ($dateFilter) {
            
            // বেইস কোয়েরি তৈরি
            $newsQuery = NewsItem::withoutGlobalScopes()->where('staff_id', $staff->id);
            $creditQuery = CreditHistory::where('staff_id', $staff->id);

            // 📅 ডেট ফিল্টার অ্যাপ্লাই
            if ($dateFilter === 'today') {
                $newsQuery->where('created_at', '>=', now()->subHours(24));
                $creditQuery->where('created_at', '>=', now()->subHours(24));
            } elseif ($dateFilter === '7days') {
                $newsQuery->where('created_at', '>=', now()->subDays(7));
                $creditQuery->where('created_at', '>=', now()->subDays(7));
            } elseif ($dateFilter === 'month') {
                $newsQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                $creditQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
            }

            // ডাইনামিক ফিল্টার্ড ডাটা
            $staff->total_published = (clone $newsQuery)->where('status', 'published')->count();
            $staff->total_drafts    = (clone $newsQuery)->where('status', '!=', 'published')->count();
            $staff->custom_news     = (clone $newsQuery)->whereNull('website_id')->count();
            $staff->reporter_news   = (clone $newsQuery)->whereNotNull('reporter_id')->count();
            
            $staff->credits_used    = (clone $creditQuery)->where('credits_change', '<', 0)->sum('credits_change') * -1;
            $staff->ai_rewrites     = (clone $creditQuery)->where('action_type', 'ai_rewrite')->count();

            // ⏳ ২৪ ঘণ্টার ডাটা
            $staff->published_24h = NewsItem::withoutGlobalScopes()
                                        ->where('staff_id', $staff->id)
                                        ->where('status', 'published')
                                        ->where('created_at', '>=', now()->subHours(24))
                                        ->count();

            // 📰 সাম্প্রতিক ৩টি নিউজ (Activity Feed)
            $staff->recent_news = NewsItem::withoutGlobalScopes()
                                        ->where('staff_id', $staff->id)
                                        ->latest()
                                        ->limit(3)
                                        ->pluck('title')
                                        ->toArray();

            return $staff;
        });
                      
        // ৪. অ্যাডমিনের নিজের তৈরি করা সোর্স এবং সুপার অ্যাডমিনের দেওয়া সোর্স একসাথে আনা
        $adminWebsites = Website::withoutGlobalScopes()
            ->where(function($query) use ($admin) {
                $query->where('user_id', $admin->id)
                      ->orWhereHas('users', function($q) use ($admin) {
                          $q->where('users.id', $admin->id); 
                      });
            })->get();
            
        $adminTemplates = $admin->settings->allowed_templates ?? [];

        // DB templates
        $dbTemplateKeys = Template::where('is_active', true)->pluck('name', 'id')
            ->mapWithKeys(fn($name, $id) => ['custom_db_' . $id => '🖼 ' . $name])
            ->toArray();

        if ($admin->role === 'super_admin') {
            $adminTemplates = array_merge(
                array_keys(UserSetting::AVAILABLE_TEMPLATES),
                array_keys($dbTemplateKeys)
            );
        }

        $departments = Department::where('user_id', $admin->id)->with('designations')->get();

        // 🎭 Available Roles and Permissions dictionary
        $roles = Role::getAssignableRoles();
        $allPermissions = Role::getAllPermissions();
        $permissionCategories = Role::getPermissionsByCategory();
        
        // Map of default permissions per role for JavaScript auto-check
        $roleDefaults = [];
        foreach ($roles as $rKey => $rVal) {
            $roleDefaults[$rKey] = Role::getDefaultPermissionsFor($rKey);
        }

        return view('client.staff.index', compact(
            'staffs',
            'admin',
            'adminWebsites',
            'adminTemplates',
            'dbTemplateKeys',
            'departments',
            'roles',
            'allPermissions',
            'permissionCategories',
            'roleDefaults'
        ));
    }

    /**
     * নতুন স্টাফ / টিম মেম্বার তৈরি করা (Strict Tenant Isolation & Staff Limit)
     */
    public function store(Request $request)
    {
        $admin = Auth::user();

        // ১. লিমিট চেক: টেন্যান্টের অধীন মোট সদস্য সংখ্যা গণনা
        $currentStaffCount = User::where('parent_id', $admin->id)->count();
        if ($admin->role !== 'super_admin' && $currentStaffCount >= ($admin->staff_limit ?? 0)) {
            return back()->with('error', "❌ আপনার স্টাফ লিমিট ({$admin->staff_limit} জন) শেষ! নতুন সদস্য যুক্ত করতে সাবস্ক্রিপশন প্ল্যান আপগ্রেড করুন।");
        }

        $assignableRoles = array_keys(Role::getAssignableRoles());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => ['required', 'string', Rule::in($assignableRoles)],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after_or_equal:joining_date',
            'district' => 'nullable|string|max:255',
            'upazila' => 'nullable|string|max:255',
            'working_location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'nid' => 'nullable|string|max:30',
            'emergency_contact' => 'nullable|string|max:20',
            'blood_group' => 'nullable|string|max:10',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
        ]);

        // ২. সিকিউরিটি চেক: Department ও Designation অবশ্যই লগইন করা অ্যাডমিনের হতে হবে
        if ($request->filled('department_id')) {
            Department::where('user_id', $admin->id)->findOrFail($request->department_id);
        }
        if ($request->filled('designation_id')) {
            Designation::where('user_id', $admin->id)->findOrFail($request->designation_id);
        }

        $selectedRole = $request->input('role', 'staff');

        // ৩. পারমিশন লজিক:
        // ম্যানুয়ালি নির্দিষ্ট পারমিশন চেক না করা হলে, রোলের default_permissions অটো-অ্যাসাইন হবে
        if ($request->has('permissions') && is_array($request->permissions) && count($request->permissions) > 0) {
            $assignedPermissions = $request->permissions;
        } else {
            $assignedPermissions = Role::getDefaultPermissionsFor($selectedRole);
        }

        // ক্লায়েন্ট (Admin) শুধুমাত্র সেই পারমিশনই স্টাফকে দিতে পারবে যা নিজের আছে (Super Admin ব্যতীত)
        if ($admin->role !== 'super_admin') {
            $adminPermissions = is_array($admin->permissions) ? $admin->permissions : json_decode($admin->permissions, true) ?? [];
            if ($admin->role === 'admin' && empty($adminPermissions)) {
                $adminPermissions = [
                    'can_latest_news',
                    'can_settings', 'can_settings_branding', 'can_settings_target_language',
                    'can_settings_wp_laravel', 'can_settings_social', 'can_settings_category',
                    'can_settings_ai_prompt', 'can_scrape', 'can_ai', 'can_studio', 
                    'can_direct_publish', 'can_view_published', 'can_auto_post', 
                    'can_central_feed', 'can_custom_photo_card', 'can_manage_staff',
                    'manage_reporters', 'reporter_direct'
                ];
            }
            $finalPermissions = array_values(array_intersect($assignedPermissions, $adminPermissions));
        } else {
            $finalPermissions = array_values($assignedPermissions);
        }

        $adminTemplates = $admin->settings->allowed_templates ?? [];

        try {
            DB::beginTransaction();

            $staff = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $selectedRole,
                'parent_id' => $admin->id, // 🛡️ Strict Tenant Isolation
                'is_active' => true,
                'permissions' => $finalPermissions,
                'department_id' => $request->department_id,
                'designation_id' => $request->designation_id,
                'joining_date' => $request->joining_date,
                'expire_date' => $request->expire_date,
                'district' => $request->district,
                'upazila' => $request->upazila,
                'working_location' => $request->working_location,
                'phone' => $request->phone,
                'nid' => $request->nid,
                'emergency_contact' => $request->emergency_contact,
                'blood_group' => $request->blood_group,
                'present_address' => $request->present_address,
                'permanent_address' => $request->permanent_address,
            ]);

            // স্টাফের জন্য সেটিংস তৈরি (অ্যাডমিনের টেমপ্লেট দিয়ে)
            UserSetting::create([
                'user_id' => $staff->id,
                'allowed_templates' => $adminTemplates,
                'default_template' => $admin->settings->default_template ?? 'default'
            ]);

            // অ্যাডমিনের সব ওয়েবসাইট অটোমেটিক স্টাফকে এক্সেস দেওয়া
            $adminWebsiteIds = Website::withoutGlobalScopes()
                ->where('user_id', $admin->id)
                ->pluck('id')->toArray();
                
            $staff->accessibleWebsites()->sync($adminWebsiteIds);

            DB::commit();
            return back()->with('success', 'নতুন মেম্বার (' . ($roles[$selectedRole]['display_name'] ?? $selectedRole) . ') সফলভাবে তৈরি হয়েছে!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'অ্যাকাউন্ট তৈরি করতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * ✏️ স্টাফের নাম/ইমেইল/রোল/পারমিশন আপডেট (Strict Tenant Isolation)
     */
    public function update(Request $request, $id)
    {
        return $this->updateInfo($request, $id);
    }

    /**
     * ✏️ স্টাফের তথ্য ও রোল আপডেট
     */
    public function updateInfo(Request $request, $id)
    {
        $admin = Auth::user();
        
        // 🛡️ Strict Tenant Isolation: শুধুমাত্র নিজের অধীনে থাকা ইউজার এডিট করা যাবে
        $staff = User::where('parent_id', $admin->id)->findOrFail($id);

        $assignableRoles = array_keys(Role::getAssignableRoles());

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($staff->id)],
            'password' => 'nullable|min:6',
            'role'     => ['nullable', 'string', Rule::in($assignableRoles)],
            'permissions' => 'nullable|array',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'joining_date' => 'nullable|date',
            'expire_date' => 'nullable|date|after_or_equal:joining_date',
            'district' => 'nullable|string|max:255',
            'upazila' => 'nullable|string|max:255',
            'working_location' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'nid' => 'nullable|string|max:30',
            'emergency_contact' => 'nullable|string|max:20',
            'blood_group' => 'nullable|string|max:10',
            'present_address' => 'nullable|string',
            'permanent_address' => 'nullable|string',
        ]);

        // Security check: Verify department and designation belong to the logged-in admin
        if ($request->filled('department_id')) {
            Department::where('user_id', $admin->id)->findOrFail($request->department_id);
        }
        if ($request->filled('designation_id')) {
            Designation::where('user_id', $admin->id)->findOrFail($request->designation_id);
        }

        $oldRole = $staff->role;
        $staff->name  = $request->name;
        $staff->email = $request->email;
        $staff->department_id = $request->department_id;
        $staff->designation_id = $request->designation_id;
        $staff->joining_date = $request->joining_date;
        $staff->expire_date = $request->expire_date;
        $staff->district = $request->district;
        $staff->upazila = $request->upazila;
        $staff->working_location = $request->working_location;
        $staff->phone = $request->phone;
        $staff->nid = $request->nid;
        $staff->emergency_contact = $request->emergency_contact;
        $staff->blood_group = $request->blood_group;
        $staff->present_address = $request->present_address;
        $staff->permanent_address = $request->permanent_address;
        
        if ($request->filled('role')) {
            $staff->role = $request->role;
        }

        if ($request->filled('password')) {
            $staff->password = Hash::make($request->password);
        }

        // পারমিশন আপডেট লজিক
        if ($request->has('permissions')) {
            $requestedPerms = $request->permissions;
            if (empty($requestedPerms) && $staff->role) {
                $requestedPerms = Role::getDefaultPermissionsFor($staff->role);
            }

            if ($admin->role !== 'super_admin') {
                $adminPermissions = is_array($admin->permissions) ? $admin->permissions : json_decode($admin->permissions, true) ?? [];
                $finalPerms = array_values(array_intersect($requestedPerms, $adminPermissions));
            } else {
                $finalPerms = array_values($requestedPerms);
            }
            $staff->permissions = $finalPerms;
        } elseif ($request->filled('role') && $request->role !== $oldRole && empty($staff->permissions)) {
            // যদি রোল পরিবর্তন হয় এবং পারমিশন ফাঁকা থাকে, নতুন রোলের ডিফল্ট পারমিশন সেট হবে
            $staff->permissions = Role::getDefaultPermissionsFor($request->role);
        }

        $staff->save();

        return back()->with('success', 'মেম্বারের তথ্য ও রোল সফলভাবে আপডেট করা হয়েছে!');
    }

    /**
     * স্টাফের পারমিশন আপডেট
     */
    public function updatePermissions(Request $request, $id)
    {
        $admin = Auth::user();
        
        // 🛡️ Strict Tenant Isolation: শুধুমাত্র নিজের স্টাফ
        $staff = User::where('parent_id', $admin->id)->findOrFail($id);

        $requestedPermissions = $request->input('permissions', []);
        
        // যদি কোনো পারমিশন সিলেক্ট না করা হয় কিন্তু রোল থাকে, রোলের ডিফল্ট পারমিশন অ্যাসাইন করা যায়
        if (empty($requestedPermissions) && $request->filled('role')) {
            $requestedPermissions = Role::getDefaultPermissionsFor($request->role);
        }
        
        if ($admin->role !== 'super_admin') {
            $adminPermissions = is_array($admin->permissions) ? $admin->permissions : json_decode($admin->permissions, true) ?? [];
            if ($admin->role === 'admin' && empty($adminPermissions)) {
                $adminPermissions = [
                    'can_latest_news',
                    'can_settings', 'can_settings_branding', 'can_settings_target_language',
                    'can_settings_wp_laravel', 'can_settings_social', 'can_settings_category',
                    'can_settings_ai_prompt', 'can_scrape', 'can_ai', 'can_studio', 
                    'can_direct_publish', 'can_view_published', 'can_auto_post', 
                    'can_central_feed', 'can_custom_photo_card', 'can_manage_staff',
                    'manage_reporters', 'reporter_direct'
                ];
            }
            $finalPermissions = array_values(array_intersect($requestedPermissions, $adminPermissions));
        } else {
            $finalPermissions = array_values($requestedPermissions);
        }
        
        $staff->permissions = $finalPermissions;
        if ($request->filled('role')) {
            $staff->role = $request->role;
        }
        $staff->save();

        return back()->with('success', 'পারমিশন আপডেট করা হয়েছে!');
    }

    /**
     * 📰 একজন স্টাফের সব নিউজ দেখা
     */
    public function showNews(Request $request, $id)
    {
        $admin = Auth::user();
        $staff = User::where('parent_id', $admin->id)->with('accessibleWebsites')->findOrFail($id);

        $query = NewsItem::withoutGlobalScopes()
            ->where('staff_id', $staff->id)
            ->with(['website', 'reporter'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_filter')) {
            match($request->date_filter) {
                'today'  => $query->where('created_at', '>=', now()->startOfDay()),
                '7days'  => $query->where('created_at', '>=', now()->subDays(7)),
                'month'  => $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year),
                default  => null,
            };
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhereHas('website', function($w) use ($search) {
                      $w->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('reporter', function($r) use ($search) {
                      $r->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        $news = $query->paginate(20);

        $todayQuery = NewsItem::withoutGlobalScopes()->where('staff_id', $staff->id)->where('created_at', '>=', now()->startOfDay());

        $stats = [
            'total'           => NewsItem::withoutGlobalScopes()->where('staff_id', $staff->id)->count(),
            'published'       => NewsItem::withoutGlobalScopes()->where('staff_id', $staff->id)->where('status', 'published')->count(),
            'draft'           => NewsItem::withoutGlobalScopes()->where('staff_id', $staff->id)->where('status', '!=', 'published')->count(),
            'today_total'     => (clone $todayQuery)->count(),
            'today_published' => (clone $todayQuery)->where('status', 'published')->count(),
            'today_rewritten' => (clone $todayQuery)->where('is_rewritten', true)->count(),
            'today_custom'    => (clone $todayQuery)->whereNull('website_id')->count(),
        ];

        return view('client.staff.news', compact('staff', 'news', 'stats'));
    }

    /**
     * 🔄 স্টাফের Active/Inactive স্ট্যাটাস টগল
     */
    public function toggleStatus($id)
    {
        $admin = Auth::user();
        $staff = User::where('parent_id', $admin->id)->findOrFail($id);
        $staff->is_active = !$staff->is_active;
        $staff->save();

        $status = $staff->is_active ? 'সক্রিয়' : 'নিষ্ক্রিয়';
        return back()->with('success', "{$staff->name} কে {$status} করা হয়েছে।");
    }

    /**
     * 🔑 স্টাফের পাসওয়ার্ড রিসেট
     */
    public function resetPassword($id)
    {
        $admin = Auth::user();
        $staff = User::where('parent_id', $admin->id)->findOrFail($id);

        $newPassword = \Illuminate\Support\Str::random(8);
        $staff->password = Hash::make($newPassword);
        $staff->save();

        return back()->with('success', "পাসওয়ার্ড রিসেট হয়েছে! নতুন পাসওয়ার্ড: {$newPassword}");
    }

    /**
     * সোর্স (ওয়েবসাইট) এক্সেস আপডেট
     */
    public function updateWebsites(Request $request, $id)
    {
        $admin = Auth::user();
        $staff = User::where('parent_id', $admin->id)->findOrFail($id);
        
        $requestedWebsites = $request->input('websites', []);
        
        $adminWebsiteIds = Website::withoutGlobalScopes()
            ->where(function($query) use ($admin) {
                $query->where('user_id', $admin->id)
                      ->orWhereHas('users', function($q) use ($admin) {
                          $q->where('users.id', $admin->id);
                      });
            })->pluck('id')->toArray();
        
        $validWebsites = array_intersect($requestedWebsites, $adminWebsiteIds);
        $staff->accessibleWebsites()->sync($validWebsites);
        
        return back()->with('success', 'সোর্স এক্সেস আপডেট করা হয়েছে!');
    }

    /**
     * টেমপ্লেট এক্সেস আপডেট
     */
    public function updateTemplates(Request $request, $id)
    {
        $admin = Auth::user();
        $staff = User::where('parent_id', $admin->id)->findOrFail($id);
        
        $requestedTemplates = $request->input('templates', []);
        $adminTemplates = $admin->settings->allowed_templates ?? [];

        if ($admin->role === 'super_admin') {
            $dbKeys = Template::where('is_active', true)->pluck('id')
                ->map(fn($id) => 'custom_db_' . $id)->toArray();
            $adminTemplates = array_merge(array_keys(UserSetting::AVAILABLE_TEMPLATES), $dbKeys);
        }
        
        $validTemplates = array_values(array_intersect($requestedTemplates, $adminTemplates));
        
        $settings = UserSetting::firstOrCreate(['user_id' => $staff->id]);
        $settings->allowed_templates = $validTemplates;
        
        if (in_array($request->input('default_template'), $validTemplates)) {
            $settings->default_template = $request->input('default_template');
        }
        
        $settings->save();
        
        return back()->with('success', 'টেমপ্লেট এক্সেস আপডেট করা হয়েছে!');
    }

    /**
     * স্টাফ ডিলিট (Strict Tenant Isolation)
     */
    public function destroy($id)
    {
        $adminId = Auth::id();
        $staff = User::where('parent_id', $adminId)->findOrFail($id);
        
        $staff->delete();
        
        return back()->with('success', 'স্টাফ অ্যাকাউন্ট মুছে ফেলা হয়েছে।');
    }

    /**
     * ✍️ স্টাফের Author Signature আপডেট করা
     */
    public function updateSignature(Request $request, $id)
    {
        $request->validate([
            'author_signature'    => 'nullable|string|max:100',
            'signature_placement' => 'required|in:top,bottom',
        ]);

        $staff = User::where('parent_id', Auth::id())->findOrFail($id);

        $staff->author_signature    = $request->author_signature ?: null;
        $staff->signature_placement = $request->signature_placement;
        $staff->save();

        return back()->with('success', 'Author Signature আপডেট হয়েছে!');
    }
}
