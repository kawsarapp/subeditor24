<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PricingPlan;
use App\Models\PricingCoupon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PricingAdminController extends Controller
{
    /**
     * Authorize Super Admin
     */
    protected function authorizeSuperAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'অননুমোদিত অ্যাক্সেস! শুধুমাত্র Super Admin প্রাইসিং প্ল্যান ও কুপন পরিবর্তন করতে পারবেন।');
        }
    }

    /**
     * Admin Pricing & Coupon Manager Dashboard
     */
    public function index()
    {
        $this->authorizeSuperAdmin();

        $plans = PricingPlan::orderBy('sort_order', 'asc')->get();
        $coupons = PricingCoupon::orderByDesc('created_at')->get();

        return view('admin.pricing.index', compact('plans', 'coupons'));
    }

    /**
     * Store or Update Pricing Plan
     */
    public function savePlan(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'id'                     => 'nullable|exists:pricing_plans,id',
            'name'                   => 'required|string|max:100',
            'slug'                   => 'required|string|max:100',
            'badge'                  => 'nullable|string|max:100',
            'standard_price'         => 'required|numeric|min:0',
            'regular_discount_price' => 'required|numeric|min:0',
            'special_price'          => 'required|numeric|min:0',
            'special_offer_name'     => 'required|string|max:100',
            'daily_news_limit'       => 'nullable|integer',
            'news_photocard_limit'   => 'nullable|integer',
            'quotation_cards_limit'  => 'nullable|integer',
            'reporters_limit'        => 'nullable|integer',
            'bangla_websites_limit'  => 'nullable|integer',
            'english_websites_limit' => 'nullable|integer',
            'features'               => 'nullable|string',
            'is_popular'             => 'nullable|boolean',
            'is_active'              => 'nullable|boolean',
            'sort_order'             => 'nullable|integer',
        ]);

        $featuresArray = [];
        if ($request->filled('features')) {
            $featuresArray = array_filter(array_map('trim', explode("\n", $request->features)));
        }

        // Extract Dynamic Custom Limits
        $customLimits = [];
        $keys = $request->input('custom_limit_keys', []);
        $vals = $request->input('custom_limit_values', []);

        if (is_array($keys) && is_array($vals)) {
            foreach ($keys as $idx => $key) {
                $trimmedKey = trim((string)$key);
                if ($trimmedKey !== '') {
                    $customLimits[$trimmedKey] = trim((string)($vals[$idx] ?? ''));
                }
            }
        }

        $planData = [
            'name'                   => strtoupper(trim($request->name)),
            'slug'                   => Str::slug($request->slug ?: $request->name),
            'badge'                  => $request->badge,
            'standard_price'         => $request->standard_price,
            'regular_discount_price' => $request->regular_discount_price,
            'special_price'          => $request->special_price,
            'special_offer_name'     => $request->special_offer_name,
            'daily_news_limit'       => (int) ($request->daily_news_limit ?? ($customLimits['Daily News'] ?? 50)),
            'news_photocard_limit'   => (int) ($request->news_photocard_limit ?? ($customLimits['News Photocard'] ?? -1)),
            'quotation_cards_limit'  => (int) ($request->quotation_cards_limit ?? ($customLimits['Quotation Cards'] ?? 5)),
            'reporters_limit'        => (int) ($request->reporters_limit ?? ($customLimits['Reporters'] ?? 10)),
            'bangla_websites_limit'  => (int) ($request->bangla_websites_limit ?? ($customLimits['Bangla Websites'] ?? 10)),
            'english_websites_limit' => (int) ($request->english_websites_limit ?? ($customLimits['English Websites'] ?? 0)),
            'features'               => array_values($featuresArray),
            'custom_limits'          => $customLimits,
            'is_popular'             => $request->boolean('is_popular'),
            'is_active'              => $request->boolean('is_active', true),
            'sort_order'             => (int) ($request->sort_order ?? 0),
        ];

        if ($request->filled('id')) {
            $plan = PricingPlan::findOrFail($request->id);
            $plan->update($planData);
        } else {
            $plan = PricingPlan::create($planData);
        }

        return redirect()->back()->with('success', "✅ প্ল্যান '{$plan->name}' সফলভাবে সংরক্ষিত হয়েছে!");
    }

    /**
     * Delete Pricing Plan
     */
    public function deletePlan($id)
    {
        $this->authorizeSuperAdmin();

        $plan = PricingPlan::findOrFail($id);
        $name = $plan->name;
        $plan->delete();

        return redirect()->back()->with('success', "🗑️ প্ল্যান '{$name}' মুছে ফেলা হয়েছে।");
    }

    /**
     * Store or Update Coupon
     */
    public function saveCoupon(Request $request)
    {
        $this->authorizeSuperAdmin();

        $request->validate([
            'id'               => 'nullable|exists:pricing_coupons,id',
            'code'             => 'required|string|max:50',
            'description'      => 'nullable|string|max:255',
            'discount_type'    => 'required|in:percentage,fixed',
            'discount_value'   => 'required|numeric|min:1',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_uses'         => 'nullable|integer|min:1',
            'expires_at'       => 'nullable|date',
            'is_active'        => 'nullable|boolean',
        ]);

        $code = strtoupper(trim(str_replace(' ', '', $request->code)));

        $couponData = [
            'code'             => $code,
            'description'      => $request->description,
            'discount_type'    => $request->discount_type,
            'discount_value'   => $request->discount_value,
            'min_order_amount' => (float) ($request->min_order_amount ?? 0),
            'max_uses'         => $request->max_uses ? (int) $request->max_uses : null,
            'expires_at'       => $request->expires_at ? $request->expires_at : null,
            'is_active'        => $request->boolean('is_active', true),
        ];

        if ($request->filled('id')) {
            $coupon = PricingCoupon::findOrFail($request->id);
            $coupon->update($couponData);
        } else {
            $coupon = PricingCoupon::create($couponData);
        }

        return redirect()->back()->with('success', "✅ কুপন কোড '{$coupon->code}' সফলভাবে সংরক্ষিত হয়েছে!");
    }

    /**
     * Delete Coupon
     */
    public function deleteCoupon($id)
    {
        $this->authorizeSuperAdmin();

        $coupon = PricingCoupon::findOrFail($id);
        $code = $coupon->code;
        $coupon->delete();

        return redirect()->back()->with('success', "🗑️ কুপন '{$code}' মুছে ফেলা হয়েছে।");
    }
}
