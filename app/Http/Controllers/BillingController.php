<?php

namespace App\Http\Controllers;

use App\Models\PricingCoupon;
use App\Models\PricingPlan;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BillingController extends Controller
{
    /**
     * Display checkout page for selected plan
     */
    public function checkout(Request $request, $slug)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('info', 'প্যাকেজটি অর্ডার করতে অনুগ্রহ করে প্রথমে সাইন-ইন বা রেজিস্টার করুন।');
        }

        $user = Auth::user();
        $plan = PricingPlan::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $mode = $request->input('mode', 'special'); // special, regular, standard
        $cycle = $request->input('cycle', 'half_yearly'); // monthly, half_yearly, yearly, lifetime

        $monthlyRate = match ($mode) {
            'standard' => (float) $plan->standard_price,
            'regular'  => (float) $plan->regular_discount_price,
            default    => (float) $plan->special_price,
        };

        // If monthly rate is 0 (e.g. Free Trial), keep 0
        if ($monthlyRate <= 0) {
            $basePrice = 0.00;
        } else {
            $basePrice = match ($cycle) {
                'monthly'  => (float) $monthlyRate,
                'yearly'   => (float) round($monthlyRate * 12 * 0.85), // 15% yearly discount
                'lifetime' => (float) round($monthlyRate * 36 * 0.70),
                default    => (float) round($monthlyRate * 6), // 6-month bundle
            };
        }

        $paymentConfig = UserSetting::getManualPaymentConfig();
        $pageConfig = PricingController::getPageConfig();
        $price = $basePrice;

        return view('billing.checkout', compact('plan', 'mode', 'cycle', 'monthlyRate', 'basePrice', 'price', 'paymentConfig', 'pageConfig', 'user'));
    }

    /**
     * Submit manual payment order
     */
    public function submitOrder(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $request->validate([
            'plan_slug'       => 'required|exists:pricing_plans,slug',
            'billing_cycle'   => 'required|string|in:monthly,half_yearly,yearly,lifetime',
            'pricing_mode'    => 'required|string|in:special,regular,standard',
            'payment_method'  => 'required|string|in:bkash,nagad,rocket,bank_transfer,manual',
            'sender_number'   => 'required|string|max:50',
            'transaction_id'  => 'required|string|max:100',
            'coupon_code'     => 'nullable|string|max:50',
            'payment_proof'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'customer_notes'  => 'nullable|string|max:500',
        ]);

        $plan = PricingPlan::where('slug', $request->plan_slug)->firstOrFail();

        // 1. Calculate Base Price
        $monthlyRate = match ($request->pricing_mode) {
            'standard' => (float) $plan->standard_price,
            'regular'  => (float) $plan->regular_discount_price,
            default    => (float) $plan->special_price,
        };

        if ($monthlyRate <= 0) {
            $basePrice = 0.00;
        } else {
            $basePrice = match ($request->billing_cycle) {
                'monthly'  => (float) $monthlyRate,
                'yearly'   => (float) round($monthlyRate * 12 * 0.85),
                'lifetime' => (float) round($monthlyRate * 36 * 0.70),
                default    => (float) round($monthlyRate * 6),
            };
        }

        // 2. Check Coupon
        $discountAmount = 0.00;
        $couponCode = null;

        if ($request->filled('coupon_code')) {
            $code = strtoupper(trim($request->coupon_code));
            $coupon = PricingCoupon::where('code', $code)->first();
            if ($coupon) {
                $couponRes = $coupon->isValidForPlan($plan->slug, $basePrice);
                if ($couponRes['valid']) {
                    $discountAmount = (float) $couponRes['discount_amount'];
                    $couponCode = $code;
                    $coupon->increment('uses_count');
                }
            }
        }

        $finalAmount = max(0.00, $basePrice - $discountAmount);

        // 3. Handle Payment Proof Screenshot Upload
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $file = $request->file('payment_proof');
            $filename = 'proof_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $proofPath = $file->storeAs('payment_proofs', $filename, 'public');
        }

        // 4. Create Subscription Order
        $orderNumber = SubscriptionOrder::generateOrderNumber();
        $order = SubscriptionOrder::create([
            'order_number'    => $orderNumber,
            'user_id'         => $user->id,
            'pricing_plan_id' => $plan->id,
            'plan_name'       => $plan->name,
            'billing_cycle'   => $request->billing_cycle,
            'pricing_mode'    => $request->pricing_mode,
            'base_price'      => $basePrice,
            'discount_amount' => $discountAmount,
            'coupon_code'     => $couponCode,
            'final_amount'    => $finalAmount,
            'payment_method'  => $request->payment_method,
            'sender_number'   => trim($request->sender_number),
            'transaction_id'  => trim($request->transaction_id),
            'payment_proof'   => $proofPath,
            'customer_notes'  => $request->customer_notes,
            'status'          => 'pending',
        ]);

        // 🔔 Send Notification to User & Super Admins
        try {
            $user->notify(new \App\Notifications\SubscriptionNotification(
                'অর্ডার গ্রহণ করা হয়েছে',
                "আপনার {$plan->name} প্যাকেজ অর্ডারটি (নং {$orderNumber}) সফলভাবে জমা হয়েছে। পেমেন্ট যাচাইপূর্বক দ্রুত একাউন্ট সক্রিয় করা হবে।",
                route('billing.my-subscription'),
                'info',
                'fa-solid fa-receipt'
            ));

            $superAdmins = User::where('role', 'super_admin')->get();
            foreach ($superAdmins as $admin) {
                $admin->notify(new \App\Notifications\SubscriptionNotification(
                    'নতুন পেমেন্ট অর্ডার',
                    "গ্রাহক {$user->name} ({$plan->name} - ৳" . number_format($finalAmount) . ") একটি নতুন পেমেন্ট অর্ডার জমা দিয়েছেন। (TrxID: {$order->transaction_id})",
                    route('admin.billing.orders', ['status' => 'pending']),
                    'warning',
                    'fa-solid fa-money-check-dollar'
                ));
            }
        } catch (\Exception $e) {
            Log::error("Notification trigger error: " . $e->getMessage());
        }

        Log::info("💳 Subscription Order Created: {$orderNumber} by User {$user->id} ({$user->name}) for {$plan->name}");

        return redirect()->route('billing.my-subscription')->with('success', "🎉 আপনার অর্ডারটি (নং: {$orderNumber}) সফলভাবে জমা হয়েছে! পেমেন্ট যাচাই করে অ্যাডমিন শীঘ্রই আপনার প্ল্যানটি সক্রিয় করে দেবেন।");
    }

    /**
     * User's dedicated Subscription, Plan Validity & Billing Dashboard
     */
    public function mySubscription(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // Effective Admin resolution for staff/reporters
        $adminUser = in_array($user->role, ['staff', 'reporter']) ? User::find($user->parent_id) : $user;
        if (!$adminUser) $adminUser = $user;

        $currentPlan = $adminUser->pricingPlan ?: PricingPlan::where('is_popular', true)->first();
        $orders = SubscriptionOrder::where('user_id', $adminUser->id)->latest()->paginate(10);
        
        $paymentConfig = UserSetting::getManualPaymentConfig();
        $pageConfig = PricingController::getPageConfig();

        return view('billing.my-subscription', compact('user', 'adminUser', 'currentPlan', 'orders', 'paymentConfig', 'pageConfig'));
    }

    /**
     * View / Print Order Invoice
     */
    public function invoice(Request $request, $orderNumber)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $order = SubscriptionOrder::with(['user', 'pricingPlan'])->where('order_number', $orderNumber)->firstOrFail();

        // Permission check: User can view own, super_admin can view all
        if ($order->user_id !== $user->id && $user->role !== 'super_admin' && $order->user_id !== $user->parent_id) {
            abort(403, 'Unauthorized access to this invoice.');
        }

        $paymentConfig = UserSetting::getManualPaymentConfig();

        return view('billing.invoice', compact('order', 'paymentConfig'));
    }
}
