<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\CreditHistory;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected array $disposableEmailDomains = [
        'mailinator.com', '10minutemail.com', 'tempmail.com', 'temp-mail.org', 'guerrillamail.com',
        'yopmail.com', 'sharklasers.com', 'dispostable.com', 'throwawaymail.com', 'getairmail.com',
        'fakeinbox.com', 'mohmal.com', 'crazymailing.com', 'nada.ltd', 'mytemp.email',
        'tempail.com', 'burnermail.io', 'dropmail.me', 'trashmail.com', 'generator.email',
        'emailondeck.com', 'fakemailgenerator.com', 'disposablemail.com', 'tempinbox.com',
        'guerrillamailblock.com', 'guerrillamail.net', 'guerrillamail.org', 'grr.la', 'inboxkitten.com',
        'trashmail.net', 'temp-mail.io', 'getnada.com', 'fakemail.net', 'tempmail.net', 'disposable.email',
        'throwaway.email', 'tempinbox.xyz', 'tmpmail.org', 'tmpmail.net', '10mail.org', 'crazymail.com'
    ];

    // ========================================================
    // 📝 SELF REGISTRATION (SIGN UP)
    // ========================================================

    // রেজিস্ট্রেশন পেজ দেখানো
    public function showRegisterForm(Request $request)
    {
        $selectedPlan = $request->query('plan', 'starter');
        return view('auth.register', compact('selectedPlan'));
    }

    // রেজিস্ট্রেশন সাবমিট ও ভ্যালিডেশন
    public function register(Request $request)
    {
        // ১. অ্যান্টি-বট হানিপট (Honeypot Trap Check)
        if (!empty($request->input('extra_website_trap')) || !empty($request->input('b_username_field'))) {
            \Illuminate\Support\Facades\Log::warning('Registration honeypot triggered from IP: ' . $request->ip());
            sleep(1);
            return redirect()->route('login')->with('success', 'Registration submitted successfully.');
        }

        // ২. সাধারণ ফিল্ড ভ্যালিডেশন
        $validated = $request->validate([
            'name'                  => 'required|string|min:2|max:70',
            'brand_name'            => 'required|string|min:2|max:100',
            'website_url'           => 'required|string|max:190',
            'email'                 => 'required|email:filter|max:190|unique:users,email',
            'phone'                 => 'required|string|max:20',
            'password'              => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required',
            'terms'                 => 'accepted',
        ], [
            'name.required'                  => 'আপনার পুরো নাম আবশ্যক।',
            'brand_name.required'            => 'আপনার নিউজ পোর্টাল/প্রতিষ্ঠানের নাম দিন।',
            'website_url.required'           => 'আপনার নিউজ পোর্টালের ওয়েবসাইট লিঙ্ক দিন।',
            'email.required'                 => 'একটি সক্রিয় ইমেইল এড্রেস আবশ্যক।',
            'email.email'                    => 'সঠিক ইমেইল ফরম্যাট প্রদান করুন।',
            'email.unique'                   => 'এই ইমেইলটি দিয়ে ইতিমধ্যে অ্যাকাউন্ট রয়েছে।',
            'phone.required'                 => 'আপনার সচল বাংলাদেশি মোবাইল নম্বর দিন।',
            'password.required'              => 'পাসওয়ার্ড প্রদান করুন।',
            'password.min'                   => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'password.confirmed'             => 'পাসওয়ার্ড কনফার্মেশন মিলছে না।',
            'terms.accepted'                 => 'ব্যবহারের নিয়ম ও শর্তাবলী মেনে নেওয়া আবশ্যক।',
        ]);

        // ৩. ডিসপোজেবল / ফেক ইমেইল ডোমেইন যাচাই
        $emailParts = explode('@', strtolower(trim($request->email)));
        $domain = end($emailParts);
        if (in_array($domain, $this->disposableEmailDomains)) {
            return back()->withInput()->withErrors([
                'email' => '❌ ক্ষণস্থায়ী বা ফেক ইমেইল গ্রহণযোগ্য নয়। অনুগ্রহ করে অফিসিয়াল বা ব্যক্তিগত সঠিক ইমেইল ব্যবহার করুন।'
            ]);
        }

        // ৪. বাংলাদেশি মোবাইল নম্বর ভ্যালিডেশন ও নরমালাইজেশন
        $normalizedPhone = $this->normalizeBdPhone($request->phone);
        if (!$normalizedPhone) {
            return back()->withInput()->withErrors([
                'phone' => '❌ সঠিক বাংলাদেশি ১১ ডিজিটের মোবাইল নম্বর দিন (যেমন: 017XXXXXXXX বা +88017XXXXXXXX)।'
            ]);
        }

        // মোবাইল নম্বরের ডুপ্লিকেট চেক
        if (User::where('phone', $normalizedPhone)->exists()) {
            return back()->withInput()->withErrors([
                'phone' => '❌ এই মোবাইল নম্বরটি দিয়ে ইতিমধ্যে একটি অ্যাকাউন্ট রয়েছে।'
            ]);
        }

        // ৫. ওয়েবসাইট লিঙ্ক নরমালাইজেশন
        $normalizedUrl = $this->normalizeWebsiteUrl($request->website_url);

        try {
            DB::beginTransaction();

            // ৬. ইউজার তৈরি (Client SaaS Admin with 7 Days Free Trial & 20 Credits)
            $user = User::create([
                'name'                  => trim($request->name),
                'email'                 => strtolower(trim($request->email)),
                'phone'                 => $normalizedPhone,
                'password'              => Hash::make($request->password),
                'role'                  => 'admin', // Client Newsroom Admin
                'is_active'             => true,
                'subscription_status'   => 'trial',
                'subscription_cycle'    => 'trial',
                'joining_date'          => Carbon::today(),
                'credits'               => 20,
                'total_credits_limit'   => 20,
                'daily_post_limit'      => 10,
                'daily_bg_remove_limit' => 10,
                'daily_crawl_limit'     => 20,
                'daily_ai_limit'        => 20,
                'staff_limit'           => 5,
                'expire_date'           => Carbon::now()->addDays(7),
                'permissions'           => [
                    'can_scrape', 'can_ai', 'can_studio', 'can_direct_publish', 
                    'can_view_published', 'can_auto_post', 'can_central_feed', 
                    'can_custom_photo_card', 'can_manage_staff'
                ],
            ]);

            // ৭. ইউজারের ডিফল্ট সেটিংস সেটআপ
            UserSetting::create([
                'user_id'            => $user->id,
                'brand_name'         => trim($request->brand_name),
                'wp_url'             => $normalizedUrl,
                'allowed_templates'  => ['ntv', 'rtv', 'dhakapost', 'todayevents'],
                'default_template'   => 'dhakapost',
                'scraper_method'     => 'direct',
                'target_language'    => 'bn',
                'is_auto_posting'    => false,
            ]);

            // ৮. ওয়েলকাম ক্রেডিট হিস্ট্রি লগ
            CreditHistory::create([
                'user_id'        => $user->id,
                'action_type'    => 'welcome_bonus',
                'description'    => '🎉 ওয়েলকাম বোনাস: ৭ দিনের ফ্রি ট্রায়াল ও ২০ ফ্রি ক্রেডিট',
                'credits_change' => 20,
                'balance_after'  => 20,
            ]);

            DB::commit();

            // ৯. স্বয়ংক্রিয় লগইন
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->flash('meta_registration_event', true);

            return redirect()->route('news.index')->with(
                'success',
                '🎉 অভিনন্দন ' . $user->name . '! আপনার অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে। আপনি ৭ দিনের ফ্রি ট্রায়াল এবং ২০ ক্রেডিট পেয়েছেন।'
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Registration Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withInput()->withErrors([
                'email' => 'রেজিস্ট্রেশন করতে সমস্যা হয়েছে: ' . $e->getMessage()
            ]);
        }
    }

    protected function normalizeBdPhone($phone): ?string
    {
        $phone = preg_replace('/[^\d+]/', '', trim((string)$phone));
        if (preg_match('/^(?:\+?880|880|0)?(1[3-9]\d{8})$/', $phone, $matches)) {
            return '0' . $matches[1];
        }
        return null;
    }

    protected function normalizeWebsiteUrl($url): string
    {
        $url = trim((string)$url);
        if (!empty($url) && !preg_match('/^https?:\/\//i', $url)) {
            $url = 'https://' . $url;
        }
        return rtrim($url, '/');
    }

    // ========================================================
    // 🔐 LOGIN & LOGOUT
    // ========================================================

    // লগইন পেজ দেখানো
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // লগইন প্রসেস করা
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // ১. অ্যাকাউন্ট সক্রিয়তা চেক
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'আপনার অ্যাকাউন্টটি নিষ্ক্রিয় করা আছে। অনুগ্রহ করে এডমিনের সাথে যোগাযোগ করুন।',
                ]);
            }

            // ২. মেয়াদোত্তীর্ণ হওয়ার ডেট চেক (Expire Date Check)
            if ($user->expire_date && \Carbon\Carbon::parse($user->expire_date)->endOfDay()->isPast()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'আপনার অ্যাকাউন্টের মেয়াদ শেষ হয়ে গেছে। অনুগ্রহ করে এডমিনের সাথে যোগাযোগ করুন।',
                ]);
            }

            $request->session()->regenerate();
            $request->session()->flash('meta_login_event', true);

            if ($user->role === 'super_admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('news.index');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    // লগআউট প্রসেস (ক্যাশ ক্লিয়ার হেডারসহ)
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully! 👋')
            ->withHeaders([
                'Cache-Control' => 'no-cache, no-store, max-age=0, must-revalidate',
                'Pragma'        => 'no-cache',
                'Expires'       => 'Sun, 02 Jan 1990 00:00:00 GMT',
            ]);
    }

    // ========================================================
    // 🔐 FORGOT PASSWORD
    // ========================================================

    // Forgot Password পেজ দেখানো
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    // Password Reset Link পাঠানো
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email'], [
            'email.exists' => 'এই ইমেইলে কোনো অ্যাকাউন্ট পাওয়া যায়নি।',
        ]);

        // পুরনো token মুছে নতুন token তৈরি
        $token = Str::random(64);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => Carbon::now(),
        ]);

        $resetLink = route('password.reset', ['token' => $token, 'email' => $request->email]);

        // ইমেইল পাঠানো
        try {
            Mail::send('auth.emails.reset-password', [
                'resetLink' => $resetLink,
                'user'      => User::where('email', $request->email)->first(),
            ], function ($m) use ($request) {
                $m->to($request->email)
                  ->subject('🔐 Password Reset — Subeditor24');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'ইমেইল পাঠাতে সমস্যা হয়েছে: ' . $e->getMessage()]);
        }

        return back()->with('status', '✅ Password reset link আপনার ইমেইলে পাঠানো হয়েছে!');
    }

    // ========================================================
    // 🔑 RESET PASSWORD
    // ========================================================

    // Reset Password form দেখানো
    public function showResetForm(Request $request, $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    // নতুন পাসওয়ার্ড সেট করা
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'token'                 => 'required',
            'password'              => 'required|min:8|confirmed',
            'password_confirmation' => 'required',
        ], [
            'password.min'       => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড দুটো মিলছে না।',
        ]);

        // Token verify করা
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['email' => '❌ Invalid বা Expired link। আবার চেষ্টা করুন।']);
        }

        // Token 60 মিনিটের বেশি পুরনো হলে expire
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => '⏰ Link মেয়াদ শেষ। আবার reset request করুন।']);
        }

        // পাসওয়ার্ড আপডেট
        User::where('email', $request->email)->update([
            'password' => Hash::make($request->password),
        ]);

        // Token মুছে দেওয়া
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', '✅ পাসওয়ার্ড সফলভাবে পরিবর্তন হয়েছে! লগইন করুন।');
    }
}