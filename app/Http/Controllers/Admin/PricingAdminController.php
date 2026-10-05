<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PricingPlan;
use App\Models\PricingCoupon;
use App\Models\User;
use App\Models\UserSetting;
use App\Http\Controllers\PricingController;
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

        $plans = PricingPlan::where('is_vip', false)->orderBy('sort_order', 'asc')->get();
        $coupons = PricingCoupon::orderByDesc('created_at')->get();
        $config = PricingController::getPageConfig();

        return view('admin.pricing.index', compact('plans', 'coupons', 'config'));
    }

    /**
     * Save Global Pricing Page Layout & Text Settings
     */
    public function savePageConfig(Request $request)
    {
        $this->authorizeSuperAdmin();

        $superAdmin = Auth::user();
        $setting = UserSetting::firstOrCreate(['user_id' => $superAdmin->id]);

        $vipFeatures = [];
        if ($request->filled('vip_plan_features')) {
            $vipFeatures = array_values(array_filter(array_map('trim', explode("\n", (string)$request->input('vip_plan_features')))));
        }

        $configData = [
            'hide_pricing_from_nav'       => $request->boolean('hide_pricing_from_nav'),
            'hide_vip_pricing_from_nav'   => $request->boolean('hide_vip_pricing_from_nav'),
            'page_badge'                  => $request->input('page_badge'),
            'page_title'                  => $request->input('page_title'),
            'page_subtitle'               => $request->input('page_subtitle'),
            'tab_special_title'           => $request->input('tab_special_title'),
            'tab_regular_title'           => $request->input('tab_regular_title'),
            'tab_standard_title'          => $request->input('tab_standard_title'),
            'free_trial_banner_title'     => $request->input('free_trial_banner_title'),
            'free_trial_banner_desc'      => $request->input('free_trial_banner_desc'),
            'free_trial_banner_badge'     => $request->input('free_trial_banner_badge'),
            'free_trial_btn_text'         => $request->input('free_trial_btn_text'),
            'table_heading'               => $request->input('table_heading'),
            'table_subheading'            => $request->input('table_subheading'),
            'policy_card_1_title'         => $request->input('policy_card_1_title'),
            'policy_card_1_desc'          => $request->input('policy_card_1_desc'),
            'policy_card_2_title'         => $request->input('policy_card_2_title'),
            'policy_card_2_desc'          => $request->input('policy_card_2_desc'),
            'contact_email'               => $request->input('contact_email'),
            'contact_phone'               => $request->input('contact_phone'),
            'whatsapp_number'             => $request->input('whatsapp_number'),
            'facebook_pixel_id'           => $request->input('facebook_pixel_id'),

            // 👑 VIP Custom Premium Plan Settings
            'vip_plan_enabled'            => $request->boolean('vip_plan_enabled', true),
            'vip_plan_badge'              => $request->input('vip_plan_badge'),
            'vip_plan_title'              => $request->input('vip_plan_title'),
            'vip_plan_subtitle'           => $request->input('vip_plan_subtitle'),
            'vip_plan_price_label'        => $request->input('vip_plan_price_label'),
            'vip_plan_price_sub'          => $request->input('vip_plan_price_sub'),
            'vip_plan_features'           => !empty($vipFeatures) ? $vipFeatures : ($setting->pricing_page_config['vip_plan_features'] ?? []),
            'vip_plan_btn_text'           => $request->input('vip_plan_btn_text'),
            'vip_plan_btn_url'            => $request->input('vip_plan_btn_url'),
            'vip_plan_secondary_btn_text' => $request->input('vip_plan_secondary_btn_text'),
        ];

        $setting->update(['pricing_page_config' => $configData]);

        return redirect()->back()->with('success', '✅ প্রাইসিং পেজের সকল টেক্সট ও লেআউট সেটিংস সফলভাবে সংরক্ষিত হয়েছে!');
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
            'post_limit_type'        => 'nullable|in:daily,monthly',
            'daily_news_limit'       => 'nullable|integer',
            'monthly_post_limit'     => 'nullable|integer',
            'news_photocard_limit'   => 'nullable|integer',
            'quotation_cards_limit'  => 'nullable|integer',
            'reporters_limit'        => 'nullable|integer',
            'bangla_websites_limit'  => 'nullable|integer',
            'english_websites_limit' => 'nullable|integer',
            'features'               => 'nullable|string',
            'is_popular'             => 'nullable|boolean',
            'is_vip'                 => 'nullable|boolean',
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
            'post_limit_type'        => $request->input('post_limit_type', 'daily'),
            'daily_news_limit'       => (int) ($request->daily_news_limit ?? ($customLimits['Daily News'] ?? 50)),
            'monthly_post_limit'     => $request->filled('monthly_post_limit') ? (int) $request->monthly_post_limit : null,
            'news_photocard_limit'   => (int) ($request->news_photocard_limit ?? ($customLimits['News Photocard'] ?? -1)),
            'quotation_cards_limit'  => (int) ($request->quotation_cards_limit ?? ($customLimits['Quotation Cards'] ?? 5)),
            'reporters_limit'        => (int) ($request->reporters_limit ?? ($customLimits['Reporters'] ?? 10)),
            'bangla_websites_limit'  => (int) ($request->bangla_websites_limit ?? ($customLimits['Bangla Websites'] ?? 10)),
            'english_websites_limit' => (int) ($request->english_websites_limit ?? ($customLimits['English Websites'] ?? 0)),
            'features'               => array_values($featuresArray),
            'custom_limits'          => $customLimits,
            'is_popular'             => $request->boolean('is_popular'),
            'is_vip'                 => $request->boolean('is_vip'),
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
