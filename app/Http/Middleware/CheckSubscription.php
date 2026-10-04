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

        // Whitelist essential account & billing management routes
        $exemptRouteNames = [
            'login',
            'logout',
            'register',
            'pricing.*',
            'billing.*',
            'profile.*',
            'password.*',
            'verification.*',
        ];

        foreach ($exemptRouteNames as $exempt) {
            if ($request->routeIs($exempt)) {
                return $next($request);
            }
        }

        // Check if user's subscription is active
        if ($user->isSubscriptionActive()) {
            return $next($request);
        }

        // 🛑 Subscription is Expired or Inactive
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'              => false,
                'subscription_expired' => true,
                'message'              => '⚠️ আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে! সেবাটি সচল রাখতে অনুগ্রহ করে প্যাকেজটি রিনিউ করুন।',
                'redirect_url'         => route('billing.my-subscription'),
            ], 403);
        }

        return redirect()->route('billing.my-subscription')->with('subscription_warning', '⚠️ আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে! নিউজ স্ক্র্যাপিং ও এআই সেবা চালু রাখতে অনুগ্রহ করে প্যাকেজটি রিনিউ করুন।');
    }
}
