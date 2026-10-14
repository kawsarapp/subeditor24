<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Super Admin always bypasses subscription restrictions
        if ($user->role === 'super_admin') {
            return $next($request);
        }

        // Always allow logout unconditionally
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        // Effective user resolution: Team members (editor, sub_editor, staff, reporter) inherit tenant owner status
        $effectiveUser = $user->parent_id ? ($user->parentUser ?? $user) : $user;

        // Check if effective user's subscription is active
        if ($effectiveUser->isSubscriptionActive()) {
            return $next($request);
        }

        // 🛑 Subscription is Expired or Inactive

        // 1. Team Members (staff, reporter, editor, sub_editor with parent_id):
        // Cannot manage billing/renewals; block them and redirect to login with organization notice
        if ($user->parent_id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success'              => false,
                    'subscription_expired' => true,
                    'message'              => '⚠️ আপনার প্রতিষ্ঠানের সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে! সেবা চালু রাখতে অনুগ্রহ করে কর্তৃপক্ষের সাথে যোগাযোগ করুন।',
                    'redirect_url'         => route('login'),
                ], 403);
            }

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => '⚠️ আপনার প্রতিষ্ঠানের সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে! সেবা চালু রাখতে অনুগ্রহ করে কর্তৃপক্ষের সাথে যোগাযোগ করুন।',
            ]);
        }

        // 2. Tenant Owner: Whitelist essential renewal & account management routes
        $exemptRouteNames = [
            'login',
            'register',
            'pricing',
            'pricing.*',
            'billing.*',
            'subscription.*',
            'profile.*',
            'password.*',
            'verification.*',
        ];

        foreach ($exemptRouteNames as $exempt) {
            if ($request->routeIs($exempt)) {
                return $next($request);
            }
        }

        // Tenant owner attempting to access restricted operational routes
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'              => false,
                'subscription_expired' => true,
                'message'              => '⚠️ আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে! সেবাটি সচল রাখতে অনুগ্রহ করে প্যাকেজটি রিনিউ করুন।',
                'redirect_url'         => route('billing.my-subscription'),
            ], 403);
        }

        return redirect()->route('billing.my-subscription')
            ->with('warning', 'আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে। সেবা চালু রাখতে অনুগ্রহ করে প্যাকেজটি রিনিউ করুন।')
            ->with('subscription_warning', '⚠️ আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে! নিউজ স্ক্র্যাপিং ও এআই সেবা চালু রাখতে অনুগ্রহ করে প্যাকেজটি রিনিউ করুন।');
    }
}
