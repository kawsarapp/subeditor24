<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\NewsItem;
use App\Models\Website;
use Illuminate\Http\Request;
use App\Models\UserSetting;
use App\Models\PricingPlan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // ==========================================
    // 👑 1. SaaS Dashboard (Super Admin Only)
    // ==========================================
    public function index()
    {
        if (auth()->user()->role !== 'super_admin') {
            abort(403, 'Unauthorized Access!');
        }

        // 🔥 ক্লায়েন্টদের রোল 'admin' হবে
        $totalUsers = User::where('role', 'admin')->count();
        $totalNews = NewsItem::withoutGlobalScopes()->count();
        $totalWebsites = Website::withoutGlobalScopes()->count();
        $allWebsites = Website::withoutGlobalScopes()->get();
        
        $pricingPlans = PricingPlan::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $users = User::where('role', 'admin')->with(['accessibleWebsites', 'settings', 'pricingPlan'])->latest()->paginate(20);

        // Fetch DB Templates
        $dbTemplates = \App\Models\Template::where('is_active', true)->pluck('name', 'id')->mapWithKeys(function($name, $id) {
            return ['custom_db_' . $id => $name];
        })->toArray();
        $allTemplates = array_merge(\App\Models\UserSetting::AVAILABLE_TEMPLATES, $dbTemplates);

        return view('admin.dashboard', compact('users', 'totalUsers', 'totalNews', 'totalWebsites', 'allWebsites', 'allTemplates', 'pricingPlans'));
    }
    
    // ==========================================
    // ⚙️ Client Management Features
    // ==========================================
    public function updateTemplates(Request $request, $userId)
    {
        $request->validate([
            'templates' => 'required|array',
            'default_template' => 'required|string'
        ]);

        $settings = UserSetting::firstOrCreate(['user_id' => $userId]);
        
        $settings->allowed_templates = $request->templates;
        $settings->default_template = $request->default_template;
        $settings->save();

        return back()->with('success', 'টেমপ্লেট পারমিশন আপডেট করা হয়েছে!');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();

        $status = $user->is_active ? 'অ্যাক্টিভ' : 'নিষ্ক্রিয়';
        return back()->with('success', "ইউজার এখন {$status}!");
    }

    public function addCredits(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|integer|min:1'
        ]);

        $user = User::findOrFail($id);
        $user->increment('credits', $request->amount);
        $user->increment('total_credits_limit', $request->amount);

        \App\Models\CreditHistory::create([
            'user_id' => $user->id,
            'action_type' => 'admin_add',
            'description' => 'Admin added credits',
            'credits_change' => $request->amount,
            'balance_after' => $user->credits
        ]);

        return back()->with('success', "{$request->amount} ক্রেডিট সফলভাবে যোগ করা হয়েছে।");
    }
    
    public function updateLimit(Request $request, $id)
    {
        $request->validate([
            'post_limit_type'    => 'required|in:daily,monthly',
            'daily_post_limit'   => 'nullable|integer|min:1',
            'monthly_post_limit' => 'nullable|integer|min:1',
        ]);

        $user = User::findOrFail($id);
        $user->post_limit_type = $request->post_limit_type;
        if ($request->filled('daily_post_limit')) {
            $user->daily_post_limit = (int) $request->daily_post_limit;
            $user->daily_ai_limit = (int) $request->daily_post_limit;
        }
        if ($request->filled('monthly_post_limit')) {
            $user->monthly_post_limit = (int) $request->monthly_post_limit;
        }
        $user->save();

        $label = $user->post_limit_label;
        return back()->with('success', "পোস্ট লিমিট সফলভাবে আপডেট করা হয়েছে: {$label}");
    }
    
    public function updateWebsiteAccess(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $user->accessibleWebsites()->sync($request->websites ?? []);

        return back()->with('success', 'সোর্স পারমিশন আপডেট করা হয়েছে!');
    }
    
    public function updateScraperSettings(Request $request, $id)
    {
        $request->validate([
            'scraper_method' => 'nullable|in:node,python',
            'auto_clean_days' => 'required|integer|min:1|max:90',
            'scrape_cooldown_minutes' => 'required|integer|min:1|max:1440',
            'scrape_concurrent_limit' => 'required|integer|min:1|max:100'
        ]);
        
        $settings = UserSetting::firstOrCreate(['user_id' => $id]);
        $settings->scraper_method = $request->scraper_method;
        $settings->auto_clean_days = $request->auto_clean_days;
        $settings->scrape_cooldown_minutes = $request->scrape_cooldown_minutes;
        $settings->scrape_concurrent_limit = $request->scrape_concurrent_limit;
        $settings->save();

        return back()->with('success', 'User scraper preference updated!');
    }
    
    // ==========================================
    // 👑 2. Create New SaaS Client (Admin)
    // ==========================================

    public function store(Request $request)
    {
        // দুটি মেথডের ভ্যালিডেশন একসাথে যুক্ত করা হয়েছে
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'credits' => 'nullable|integer',
            'daily_post_limit' => 'nullable|integer',
            'staff_limit' => 'nullable|integer' // 🔥 নতুন
        ]);

        try {
            DB::transaction(function () use ($request) {
                // নতুন ইউজার তৈরি (Role হবে admin, ক্লায়েন্ট রোল)
                $user = User::create([
                    'name' => strip_tags($request->name),
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'credits' => $request->credits ?? 10,
                    'daily_post_limit' => $request->daily_post_limit ?? 10,
                    'staff_limit' => $request->staff_limit ?? 0, // 🔥 স্টাফ লিমিট সেভ
                    'role' => 'admin', // 🔥 SaaS Client Role (ডুপ্লিকেট Role রিমুভ করে সঠিকটা রাখা হয়েছে)
                    'is_active' => true
                ]);

                // ডিফল্ট সেটিংস তৈরি
                UserSetting::create([
                    'user_id' => $user->id,
                    'daily_post_limit' => $request->daily_post_limit ?? 10,
                    'allowed_templates' => ['ntv', 'rtv', 'dhakapost'], // ডিফল্ট টেমপ্লেট
                ]);
            });

            return back()->with('success', 'নতুন ক্লায়েন্ট সফলভাবে তৈরি করা হয়েছে!');
        } catch (\Exception $e) {
            return back()->with('error', 'অ্যাকাউন্ট তৈরিতে সমস্যা হয়েছে: ' . $e->getMessage());
        }
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'staff_limit' => 'nullable|integer|min:0',
            'pricing_plan_id' => 'nullable',
            'expire_date' => 'nullable|date',
            'subscription_status' => 'nullable|string|in:active,expired,trial,lifetime,pending',
            'credits' => 'nullable|integer|min:0',
            'post_limit_type' => 'nullable|in:daily,monthly',
            'daily_post_limit' => 'nullable|integer|min:0',
            'monthly_post_limit' => 'nullable|integer|min:0',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        
        if ($request->has('staff_limit')) {
            $user->staff_limit = (int) $request->staff_limit;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('pricing_plan_id')) {
            $oldPlanId = $user->pricing_plan_id;
            $newPlanId = $request->pricing_plan_id ?: null;
            $user->pricing_plan_id = $newPlanId;

            // If plan changed and new plan exists, auto-sync defaults if fields were untouched
            if ($newPlanId && $oldPlanId != $newPlanId) {
                $plan = PricingPlan::find($newPlanId);
                if ($plan) {
                    if ($plan->post_limit_type) {
                        $user->post_limit_type = $plan->post_limit_type;
                    }
                    if ($plan->monthly_post_limit) {
                        $user->monthly_post_limit = $plan->monthly_post_limit;
                    }
                    if ($plan->daily_news_limit) {
                        $user->daily_post_limit = $plan->daily_news_limit;
                        $user->daily_ai_limit = $plan->daily_news_limit;
                    }
                    if ($plan->reporters_limit) {
                        $user->staff_limit = $plan->reporters_limit;
                    }
                }
            }
        }

        if ($request->filled('expire_date')) {
            $user->expire_date = $request->expire_date;
        }

        if ($request->filled('subscription_status')) {
            $user->subscription_status = $request->subscription_status;
            if ($request->subscription_status === 'active' || $request->subscription_status === 'lifetime') {
                $user->is_active = true;
            } elseif ($request->subscription_status === 'expired') {
                $user->is_active = false;
            }
        }

        if ($request->has('credits') && $request->credits !== null) {
            $user->credits = (int) $request->credits;
        }

        if ($request->has('post_limit_type') && $request->filled('post_limit_type')) {
            $user->post_limit_type = $request->post_limit_type;
        }

        if ($request->has('daily_post_limit') && $request->daily_post_limit !== null) {
            $user->daily_post_limit = (int) $request->daily_post_limit;
            $user->daily_ai_limit = (int) $request->daily_post_limit;
        }

        if ($request->has('monthly_post_limit') && $request->monthly_post_limit !== null) {
            $user->monthly_post_limit = (int) $request->monthly_post_limit;
        }

        $user->save();

        return back()->with('success', 'ইউজারের সাবস্ক্রিপশন প্ল্যান ও প্রোফাইল সফলভাবে আপডেট/আপগ্রেড করা হয়েছে!');
    }
    
    public function destroy($id)
    {
        $news = NewsItem::findOrFail($id);
        if (auth()->user()->role !== 'super_admin' && $news->user_id !== auth()->id()) {
            return back()->with('error', 'আপনার অনুমতি নেই।');
        }
        $news->delete();
        return back()->with('success', 'নিউজটি সফলভাবে মুছে ফেলা হয়েছে।');
    }
            
    // ==========================================
    // 📊 History & Reports
    // ==========================================
    public function postHistory(Request $request)
    {
        // ড্রপডাউনের জন্য লিস্ট (SaaS ক্লায়েন্ট)
        $users = User::where('role', 'admin')->get();
        $websites = Website::withoutGlobalScopes()->get();

        // মেইন কুয়েরি
        $query = NewsItem::withoutGlobalScopes()
            ->with(['user.settings', 'website']) // Eager Loading (Fast Query)
            ->where('is_posted', true);

        if ($request->filled('search')) {
            $query->where('title', 'like', "%{$request->search}%");
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('website_id')) {
            $query->where('website_id', $request->website_id);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('posted_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('posted_at', '<=', $request->date_to);
        }

        // ডাটা ফেচ (Pagination)
        $allPosts = $query->latest('posted_at')->paginate(50)->withQueryString();

        return view('admin.post_history', compact('allPosts', 'users', 'websites'));
    }

    // ==========================================
    // 🕵️ Impersonation (Login As User)
    // ==========================================
    public function loginAsUser($id)
    {
        if (auth()->user()->role !== 'super_admin') {
            return back()->with('error', 'অনুমতি নেই।');
        }

        $originalAdminId = auth()->id(); // অ্যাডমিনের নিজের আইডি
        $user = User::findOrFail($id);

        // সেশনে অ্যাডমিনের আইডি সেভ করে রাখা
        session()->put('admin_impersonator_id', $originalAdminId);

        // ইউজারের আইডিতে লগইন করা
        Auth::login($user);

        return redirect()->route('news.index')->with('success', "Logged in as {$user->name}");
    }

    public function stopImpersonate()
    {
        if (session()->has('admin_impersonator_id')) {
            
            $adminId = session('admin_impersonator_id');
            session()->forget('admin_impersonator_id');

            Auth::loginUsingId($adminId);

            return redirect()->route('admin.dashboard')->with('success', 'স্বাগতম! অ্যাডমিন প্যানেলে ফিরে এসেছেন।');
        }

        return redirect()->route('news.index');
    }
    
    public function updatePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->permissions = $request->input('permissions', []); // চেক না করলে খালি অ্যারে সেভ হবে
        $user->save();

        return back()->with('success', 'ইউজার পারমিশন সফলভাবে আপডেট করা হয়েছে!');
    }
}