<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PricingPlan;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class VipPricingAdminController extends Controller
{
    /**
     * Authorize Super Admin
     */
    protected function authorizeSuperAdmin(): void
    {
        $user = Auth::user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'অননুমোদিত অ্যাক্সেস! শুধুমাত্র Super Admin ভিআইপি প্রাইসিং পরিচালনা করতে পারবেন।');
        }
    }

    /**
     * Get VIP Page Dynamic Configuration
     */
    public static function getVipPageConfig(): array
    {
        $default = [
            'vip_badge'          => '👑 VIP / CUSTOM ENTERPRISE',
            'vip_title'          => 'কাস্টম এন্টারপ্রাইজ ও মিডিয়া এজেন্সি স্যুট',
            'vip_subtitle'       => 'বড় মিডিয়া হাউজ, টিভি চ্যানেল, মাল্টি-পোর্টাল ও জাতীয় দৈনিকের জন্য আনলিমিটেড পাওয়ার, ডেডিকেটেড ক্লাউড সার্ভার ও কাস্টম AI ইন্টিগ্রেশন।',
            'tab_special_title'  => '🔥 Special Price',
            'tab_regular_title'  => '⏳ Regular 6-Month',
            'tab_standard_title' => 'Standard Price',
            'whatsapp_number'    => '8801975389599',
            'contact_phone'      => '+880 1975-389599',
            'contact_email'      => 'support@newsmanage24.com',
            'cta_title'          => 'আপনার নিউজ হাউজের জন্য কাস্টম সমাধান প্রয়োজন?',
            'cta_desc'           => 'আমাদের এক্সিকিউটিভ টিমের সাথে সরাসরি আলোচনা করে আপনার পোর্টালের জন্য সবচেয়ে সাশ্রয়ী ও পাওয়ারফুল VIP প্যাকেজ নির্ধারণ করুন।',
            'vip_plan_features'  => [
                '⚡ ডেডিকেটেড ক্লাউড সার্ভার ক্লাস্টার (No Traffic Lag)',
                '🤖 আপনার পোর্টালের সম্পাদকীয় ধরণ অনুযায়ী কাস্টম AI ফাইন-টিউনিং',
                '🏢 মাল্টিপল নিউজ পোর্টাল ও আনলিমিটেড সাব-ডোমেইন সেন্ট্রাল কন্ট্রোল',
                '👥 আনলিমিটেড রিপোর্টার ও বিভাগীয় সম্পাদকীয় ম্যানেজমেন্ট সিস্টেম',
                '🎨 কাস্টম ব্র্যান্ডেড আনলিমিটেড ফটো কার্ড ও অটো-ওয়াটারমার্ক ইঞ্জিন',
                '🔒 প্রাইভেট এনক্রিপশন ও কাস্টম ডোমেইন হোয়াইট-লেবেল ব্রান্ডিং',
                '📞 ২৪/৭ ডেডিকেটেড ভিআইপি একাউন্ট ম্যানেজার ও প্রায়োরিটি হটলাইন সাপোর্ট'
            ],
        ];

        $superAdmin = User::where('role', 'super_admin')->first();
        if ($superAdmin) {
            $setting = UserSetting::where('user_id', $superAdmin->id)->first();
            if ($setting && !empty($setting->vip_pricing_page_config)) {
                $saved = is_array($setting->vip_pricing_page_config)
                    ? $setting->vip_pricing_page_config
                    : json_decode($setting->vip_pricing_page_config, true);
                if (is_array($saved)) {
                    return array_merge($default, $saved);
                }
            }
        }

        return $default;
    }

    /**
     * VIP Pricing Admin Dashboard
     */
    public function index()
    {
        $this->authorizeSuperAdmin();

        $vipPlans = PricingPlan::where('is_vip', true)->orderBy('sort_order', 'asc')->get();
        $config = self::getVipPageConfig();

        return view('admin.vip-pricing.index', compact('vipPlans', 'config'));
    }

    /**
     * Store or Update VIP Pricing Plan
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
            'features'               => 'nullable|string',
            'is_popular'             => 'nullable|boolean',
            'is_active'              => 'nullable|boolean',
            'sort_order'             => 'nullable|integer',
        ]);

        $featuresArray = [];
        if ($request->filled('features')) {
            $featuresArray = array_values(array_filter(array_map('trim', explode("\n", $request->features))));
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
            'daily_news_limit'       => (int) ($customLimits['Daily News'] ?? -1),
            'news_photocard_limit'   => (int) ($customLimits['News Photocard'] ?? -1),
            'quotation_cards_limit'  => (int) ($customLimits['Quotation Cards'] ?? -1),
            'reporters_limit'        => (int) ($customLimits['Reporters'] ?? -1),
            'bangla_websites_limit'  => (int) ($customLimits['Bangla Websites'] ?? -1),
            'english_websites_limit' => (int) ($customLimits['English Websites'] ?? -1),
            'features'               => $featuresArray,
            'custom_limits'          => $customLimits,
            'is_popular'             => $request->boolean('is_popular'),
            'is_vip'                 => true, // Strictly a VIP Plan
            'is_active'              => $request->boolean('is_active', true),
            'sort_order'             => (int) ($request->sort_order ?? 0),
        ];

        if ($request->filled('id')) {
            $plan = PricingPlan::findOrFail($request->id);
            $plan->update($planData);
        } else {
            $plan = PricingPlan::create($planData);
        }

        return redirect()->back()->with('success', "👑 ভিআইপি প্ল্যান '{$plan->name}' সফলভাবে সংরক্ষিত হয়েছে!");
    }

    /**
     * Delete VIP Pricing Plan
     */
    public function deletePlan($id)
    {
        $this->authorizeSuperAdmin();

        $plan = PricingPlan::where('id', $id)->where('is_vip', true)->firstOrFail();
        $name = $plan->name;
        $plan->delete();

        return redirect()->back()->with('success', "🗑️ ভিআইপি প্ল্যান '{$name}' মুছে ফেলা হয়েছে।");
    }

    /**
     * Save Global VIP Page Configuration
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
            'vip_badge'          => $request->input('vip_badge'),
            'vip_title'          => $request->input('vip_title'),
            'vip_subtitle'       => $request->input('vip_subtitle'),
            'tab_special_title'  => $request->input('tab_special_title'),
            'tab_regular_title'  => $request->input('tab_regular_title'),
            'tab_standard_title' => $request->input('tab_standard_title'),
            'whatsapp_number'    => $request->input('whatsapp_number'),
            'contact_phone'      => $request->input('contact_phone'),
            'contact_email'      => $request->input('contact_email'),
            'cta_title'          => $request->input('cta_title'),
            'cta_desc'           => $request->input('cta_desc'),
            'vip_plan_features'  => !empty($vipFeatures) ? $vipFeatures : ($setting->vip_pricing_page_config['vip_plan_features'] ?? []),
        ];

        $setting->update(['vip_pricing_page_config' => $configData]);

        return redirect()->back()->with('success', '👑 VIP প্রাইসিং পেজের সকল টেক্সট ও সেটিংস সফলভাবে সংরক্ষিত হয়েছে!');
    }
}
