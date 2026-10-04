<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use App\Models\SubscriptionOrder;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminBillingController extends Controller
{
    /**
     * Display list of all subscription orders
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search');

        $query = SubscriptionOrder::with(['user', 'pricingPlan', 'approvedBy'])->latest();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('transaction_id', 'like', "%{$search}%")
                  ->orWhere('sender_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->paginate(20)->withQueryString();

        // Summary Stats
        $stats = [
            'pending_count'   => SubscriptionOrder::where('status', 'pending')->count(),
            'approved_count'  => SubscriptionOrder::where('status', 'approved')->count(),
            'total_revenue'   => SubscriptionOrder::where('status', 'approved')->sum('final_amount'),
            'pending_revenue' => SubscriptionOrder::where('status', 'pending')->sum('final_amount'),
        ];

        return view('admin.billing.orders', compact('orders', 'stats', 'status', 'search'));
    }

    /**
     * ⚡ 1-Click Approve Order & Automatically Activate User Plan
     */
    public function approve(Request $request, $id)
    {
        $order = SubscriptionOrder::with(['user', 'pricingPlan'])->findOrFail($id);

        if ($order->status === 'approved') {
            return back()->with('info', 'এই অর্ডারটি ইতোমধ্যে সক্রিয় রয়েছে।');
        }

        $user = $order->user;
        if (!$user) {
            return back()->with('error', 'অর্ডারের ইউজার একাউন্ট খুঁজে পাওয়া যায়নি।');
        }

        $plan = $order->pricingPlan ?: PricingPlan::where('name', $order->plan_name)->first();
        if (!$plan) {
            $plan = PricingPlan::where('is_popular', true)->first();
        }

        // 🚀 AUTOMATED PLAN PROVISIONING
        $user->activatePlan($plan, $order->billing_cycle, $order);

        // Update Order Status
        $order->update([
            'status'              => 'approved',
            'approved_by_user_id' => Auth::id(),
            'approved_at'         => now(),
            'admin_notes'         => $request->input('admin_notes', 'অর্ডার যাচাইপূর্বক প্ল্যানটি সফলভাবে সক্রিয় করা হয়েছে।'),
        ]);

        Log::info("✅ Plan Approved & Activated: User #{$user->id} ({$user->name}) -> Plan {$plan->name} until {$user->expire_date}");

        return back()->with('success', "🎉 অর্ডার নং {$order->order_number} সফলভাবে অনুমোদিত হয়েছে এবং গ্রাহক ({$user->name})-এর একাউন্টে '{$plan->name}' প্ল্যান সক্রিয় করা হয়েছে!");
    }

    /**
     * Reject Order
     */
    public function reject(Request $request, $id)
    {
        $order = SubscriptionOrder::findOrFail($id);

        $order->update([
            'status'      => 'rejected',
            'admin_notes' => $request->input('admin_notes', 'পেমেন্ট ট্রানজেকশন আইডি বা তথ্যের অসঙ্গতির কারণে অর্ডারটি বাতিল করা হয়েছে।'),
        ]);

        Log::warning("❌ Subscription Order Rejected: {$order->order_number}");

        return back()->with('info', "অর্ডার নং {$order->order_number} বাতিল করা হয়েছে।");
    }

    /**
     * Display and edit manual payment method settings
     */
    public function paymentSettings(Request $request)
    {
        $config = UserSetting::getManualPaymentConfig();
        return view('admin.billing.payment-settings', compact('config'));
    }

    /**
     * Update manual payment method accounts and instructions
     */
    public function updatePaymentSettings(Request $request)
    {
        $superAdmin = Auth::user();
        if ($superAdmin->role !== 'super_admin') abort(403);

        $setting = UserSetting::firstOrCreate(['user_id' => $superAdmin->id]);

        $paymentData = [
            'bkash_number'        => $request->input('bkash_number'),
            'bkash_type'          => $request->input('bkash_type', 'Personal (Send Money)'),
            'bkash_instruction'   => $request->input('bkash_instruction'),
            'nagad_number'        => $request->input('nagad_number'),
            'nagad_type'          => $request->input('nagad_type', 'Personal (Send Money)'),
            'nagad_instruction'   => $request->input('nagad_instruction'),
            'rocket_number'       => $request->input('rocket_number'),
            'rocket_type'         => $request->input('rocket_type', 'Personal'),
            'rocket_instruction'  => $request->input('rocket_instruction'),
            'bank_name'           => $request->input('bank_name'),
            'bank_account_name'   => $request->input('bank_account_name'),
            'bank_account_no'     => $request->input('bank_account_no'),
            'bank_branch'         => $request->input('bank_branch'),
            'bank_routing_no'     => $request->input('bank_routing_no'),
            'bank_instruction'    => $request->input('bank_instruction'),
            'support_phone'       => $request->input('support_phone'),
            'support_whatsapp'    => $request->input('support_whatsapp'),
        ];

        $setting->manual_payment_methods = $paymentData;
        $setting->save();

        return back()->with('success', '✅ ম্যানুয়াল পেমেন্ট মেথড ও অ্যাকাউন্ট তথ্য সফলভাবে আপডেট হয়েছে!');
    }
}
