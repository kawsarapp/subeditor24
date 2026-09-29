<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PricingPlan;
use App\Models\PricingCoupon;

class PricingController extends Controller
{
    /**
     * Display the public / user-facing pricing page
     */
    public function index(Request $request)
    {
        $plans = PricingPlan::active()->get();
        $activeCouponsCount = PricingCoupon::where('is_active', true)->count();

        return view('pricing.index', compact('plans', 'activeCouponsCount'));
    }

    /**
     * Live Ajax coupon verification and recalculation
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code'      => 'required|string|trim',
            'plan_slug' => 'required|string',
            'mode'      => 'required|string|in:special,regular,standard',
        ]);

        $plan = PricingPlan::where('slug', $request->plan_slug)->where('is_active', true)->first();

        if (!$plan) {
            return response()->json([
                'success' => false,
                'message' => '❌ প্ল্যানটি খুঁজে পাওয়া যায়নি।',
            ], 404);
        }

        // Determine base price based on mode
        $basePrice = match ($request->mode) {
            'standard' => (float) $plan->standard_price,
            'regular'  => (float) $plan->regular_discount_price,
            default    => (float) $plan->special_price,
        };

        $couponCode = strtoupper(trim($request->code));
        $coupon = PricingCoupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => '❌ অবৈধ বা ভুল কুপন কোড!',
            ], 422);
        }

        $result = $coupon->isValidForPlan($plan->slug, $basePrice);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => '❌ ' . $result['message'],
            ], 422);
        }

        return response()->json([
            'success'   => true,
            'message'   => $result['message'],
            'data'      => $result,
            'plan_name' => $plan->name,
        ]);
    }
}
