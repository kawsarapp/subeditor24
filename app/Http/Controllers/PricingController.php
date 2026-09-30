<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PricingPlan;
use App\Models\PricingCoupon;

use App\Models\User;
use App\Models\UserSetting;

class PricingController extends Controller
{
    /**
     * Get dynamic pricing page layout & text configuration
     */
    public static function getPageConfig(): array
    {
        $default = [
            'page_badge'               => '৬ মাসের জন্য বিশেষ Discount Offer',
            'page_title'               => 'Special Pricing Plans',
            'page_subtitle'            => 'প্রতিটি প্যাকেজে থাকছে ৬ মাসের Special Discount। সাবস্ক্রাইব করলে পাবেন অতিরিক্ত Early Bird Discount এবং লাইফটাইম প্রাইস লক সুবিধা!',
            'tab_special_title'        => '🔥 October Special (সর্বোচ্চ ছাড়)',
            'tab_regular_title'        => '⏳ Regular 6-Month Discount',
            'tab_standard_title'       => 'Standard Price',
            'free_trial_banner_title'  => '🎁 ৭ দিনের নো-রিস্ক ফ্রি ট্রায়াল ও ২০ ফ্রি ক্রেডিট!',
            'free_trial_banner_desc'   => 'কোনো ক্রেডিট কার্ড বা অগ্রিম পেমেন্ট ছাড়াই আজই আপনার নিউজ পোর্টালের জন্য অটোমেশন শুরু করুন।',
            'free_trial_banner_badge'  => 'Free Access',
            'free_trial_btn_text'      => 'ফ্রি সাইন-আপ করুন',
            'table_heading'            => '🔥 OFFER SUMMARY & LIMITS COMPARISON',
            'table_subheading'         => 'এক নজরে সকল প্যাকেজের অফার মূল্য ও লিমিট তুলনা',
            'policy_card_1_title'      => '⏳ Regular Discount Policy',
            'policy_card_1_desc'       => 'প্রথম ৬ মাস পর্যন্ত প্রতিটি প্ল্যানে থাকছে সর্বোচ্চ ৫০% থেকে ৬৫% পর্যন্ত নিয়মিত ছাড়ের সুবিধা।',
            'policy_card_2_title'      => '🔥 Early Bird (Lifetime Lock)',
            'policy_card_2_desc'       => 'অফারের মধ্যে সাবস্ক্রাইব করলে Special Price-এর ছাড় পাবেন এবং সেই নির্ধারিত মূল্য আপনার জন্য সবসময়ের জন্য (Lifetime) Lock থাকবে!',
            'contact_email'            => 'support@newsmanage24.com',
            'contact_phone'            => '+880 1771-545972',
            'whatsapp_number'          => '8801771545972',

            // 👑 Special VIP / Custom Premium Solution for Media Giants
            'vip_plan_enabled'            => true,
            'vip_plan_badge'              => '👑 VIP / CUSTOM ENTERPRISE',
            'vip_plan_title'              => 'কাস্টম এন্টারপ্রাইজ ও মিডিয়া এজেন্সি স্যুট',
            'vip_plan_subtitle'           => 'বড় মিডিয়া হাউজ, টিভি চ্যানেল, মাল্টি-পোর্টাল ও জাতীয় দৈনিকের জন্য আনলিমিটেড পাওয়ার, ডেডিকেটেড সার্ভার ও কাস্টম এআই ইন্টিগ্রেশন।',
            'vip_plan_price_label'        => 'Custom Tailored Pricing',
            'vip_plan_price_sub'          => 'আপনার পোর্টাল ও ট্রাফিক সাইজ অনুযায়ী বিশেষ মূল্য নির্ধারণ',
            'vip_plan_features'           => [
                '⚡ ডেডিকেটেড হাই-পারফরম্যান্স ক্লাউড সার্ভার ক্লাস্টার (No Traffic Delay)',
                '🤖 আপনার পোর্টালের সম্পাদকীয় ধরণ অনুযায়ী কাস্টম AI ফাইন-টিউনিং',
                '🏢 মাল্টিপল নিউজ পোর্টাল ও আনলিমিটেড সাব-ডোমেইন সেন্ট্রাল কন্ট্রোল',
                '👥 আনলিমিটেড রিপোর্টার ও বিভাগীয় সম্পাদকীয় ম্যানেজমেন্ট সিস্টেম',
                '🎨 কাস্টম ব্র্যান্ডেড আনলিমিটেড ফটো কার্ড ও অটো-ওয়াটারমার্ক ইঞ্জিন',
                '🔒 প্রাইভেট এনক্রিপশন ও কাস্টম ডোমেইন হোয়াইট-লেবেল ব্রান্ডিং',
                '📞 ২৪/৭ ডেডিকেটেড ভিআইপি একাউন্ট ম্যানেজার ও প্রায়োরিটি হটলাইন সাপোর্ট'
            ],
            'vip_plan_btn_text'           => 'ভিআইপি কনসালটেশন ও ডেমো বুক করুন 🚀',
            'vip_plan_btn_url'            => '',
            'vip_plan_secondary_btn_text' => 'Executive WhatsApp Chat',
        ];

        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            $setting = UserSetting::where('user_id', $superAdmin->id)->first();
            if ($setting && !empty($setting->pricing_page_config)) {
                $saved = is_array($setting->pricing_page_config) 
                    ? $setting->pricing_page_config 
                    : json_decode($setting->pricing_page_config, true);
                if (is_array($saved)) {
                    return array_merge($default, $saved);
                }
            }
        }

        return $default;
    }

    /**
     * Display the public / user-facing pricing page
     */
    public function index(Request $request)
    {
        $plans = PricingPlan::active()->regular()->get();
        $activeCouponsCount = PricingCoupon::where('is_active', true)->count();
        $config = self::getPageConfig();

        return view('pricing.index', compact('plans', 'activeCouponsCount', 'config'));
    }

    /**
     * Display the dedicated VIP & Custom Enterprise Pricing Page
     */
    public function vipIndex(Request $request)
    {
        $vipPlans = PricingPlan::active()->vip()->get();
        $config = \App\Http\Controllers\Admin\VipPricingAdminController::getVipPageConfig();

        return view('pricing.vip', compact('vipPlans', 'config'));
    }

    /**
     * Live Ajax coupon verification and recalculation
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code'      => 'required|string',
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
