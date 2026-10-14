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

    // রেজিস্ট্রেশন পেজ দেখানো (রেজিস্ট্রেশন বন্ধ থাকায় লগইন পেজে রিডাইরেক্ট)
    public function showRegisterForm(Request $request)
    {
        return redirect()->route('login');
    }

    // রেজিস্ট্রেশন সাবমিট ও ভ্যালিডেশন (রেজিস্ট্রেশন বন্ধ থাকায় লগইন পেজে রিডাইরেক্ট)
    public function register(Request $request)
    {
        return redirect()->route('login');
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

            $request->session()->regenerate();
            $request->session()->flash('meta_login_event', true);

            // ২. সাবস্ক্রিপশন মেয়াদ চেক (Subscription Expire Check)
            if ($user->role !== 'super_admin' && !$user->isSubscriptionActive()) {
                if ($user->parent_id) {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->withErrors([
                        'email' => 'আপনার প্রতিষ্ঠানের সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে। অনুগ্রহ করে কর্তৃপক্ষের সাথে যোগাযোগ করুন।',
                    ]);
                }

                // Redirect expired user directly to billing page so they can self-service renew!
                return redirect()->route('billing.my-subscription')->with('warning', 'আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে। সেবা চালু রাখতে অনুগ্রহ করে প্যাকেজটি রিনিউ করুন।');
            }

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