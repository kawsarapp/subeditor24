<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use App\Models\UserSetting;
use App\Services\SocialPostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacebookPageController extends Controller
{
    /**
     * Clean token string (removes Bearer prefix, extra whitespace, surrounding quotes)
     */
    protected function cleanToken(?string $token): string
    {
        if (empty($token)) {
            return '';
        }
        $token = trim($token);
        $token = preg_replace('/^Bearer\s+/i', '', $token);
        return trim($token, " \t\n\r\0\x0B\"'");
    }

    /**
     * Clean page ID or extract numeric ID from Facebook URL
     */
    protected function cleanPageId(?string $pageId): string
    {
        if (empty($pageId)) {
            return '';
        }
        $pageId = trim($pageId);

        // If user pasted a full URL with numeric ID (e.g. /profile.php?id=12345 or /123456789)
        if (preg_match('/(?:id=|\/profile\.php\?id=|\/)([0-9]{5,})/i', $pageId, $matches)) {
            return $matches[1];
        }

        // Strip domain and trailing slashes if vanity URL was pasted
        $pageId = preg_replace('#^https?://(www\.)?facebook\.com/#i', '', $pageId);
        $pageId = trim($pageId, " /");

        return $pageId;
    }

    /**
     * Get Facebook App ID and App Secret with DB / .env fallbacks
     */
    protected function getFacebookAppCredentials(): array
    {
        // 1. Check logged-in user's own setting first
        if (Auth::check()) {
            $userSetting = UserSetting::where('user_id', Auth::id())->first();
            if ($userSetting && !empty($userSetting->fb_app_id) && !empty($userSetting->fb_app_secret)) {
                return [trim($userSetting->fb_app_id), trim($userSetting->fb_app_secret)];
            }
        }

        // 2. Check superadmin setting in DB
        $superAdminSetting = UserSetting::whereNotNull('fb_app_id')->where('fb_app_id', '!=', '')->first();
        if ($superAdminSetting && !empty($superAdminSetting->fb_app_id) && !empty($superAdminSetting->fb_app_secret)) {
            return [trim($superAdminSetting->fb_app_id), trim($superAdminSetting->fb_app_secret)];
        }

        // 3. Fallback to config / .env
        $appId = config('services.facebook.app_id') ?: env('FACEBOOK_APP_ID');
        $appSecret = config('services.facebook.app_secret') ?: env('FACEBOOK_APP_SECRET');

        return [trim($appId ?? ''), trim($appSecret ?? '')];
    }

    /**
     * 🚀 1-Click Facebook OAuth Connect
     * Redirects user to Meta Login & Page Permission dialog
     */
    public function redirectToFacebook(Request $request)
    {
        [$appId, $appSecret] = $this->getFacebookAppCredentials();

        if (empty($appId)) {
            return redirect()->route('settings.index')
                ->with('error', '❌ ফেসবুক অ্যাপ কনফিগার করা হয়নি! অনুগ্রহ করে Settings-এ Facebook App ID এবং App Secret যুক্ত করুন।');
        }

        $state = Str::random(32);
        session(['fb_oauth_state' => $state]);
        session()->save();

        if (Auth::check()) {
            cache()->put('fb_oauth_state_' . Auth::id(), $state, now()->addMinutes(15));
        }

        $redirectUri = url('/facebook-pages/callback');
        session(['fb_oauth_redirect_uri' => $redirectUri]);
        session()->save();

        $scope = implode(',', [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            'public_profile'
        ]);

        $authUrl = "https://www.facebook.com/v19.0/dialog/oauth?" . http_build_query([
            'client_id'     => $appId,
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
            'scope'         => $scope,
            'response_type' => 'code',
            'auth_type'     => 'rerequest',
        ]);

        return redirect()->away($authUrl);
    }

    /**
     * 🔄 Handle Meta OAuth Callback
     * Exchanges code -> user token -> 60-day long-lived token -> permanent page tokens
     */
    public function handleFacebookCallback(Request $request)
    {
        $state = $request->get('state');
        $sessionState = session('fb_oauth_state') ?: (Auth::check() ? cache()->get('fb_oauth_state_' . Auth::id()) : null);

        if ($request->has('error')) {
            $errorDesc = $request->get('error_description', 'ব্যবহারকারী ফেসবুক কানেকশন বাতিল করেছেন।');
            return redirect()->route('settings.index')
                ->with('error', '❌ ফেসবুক কানেকশন বাতিল বা ব্যর্থ হয়েছে: ' . $errorDesc);
        }

        if (empty($state) || ($sessionState && $state !== $sessionState)) {
            return redirect()->route('settings.index')
                ->with('error', '❌ সিকিউরিটি টোকেন মিসম্যাচ (CSRF State Error)! অনুগ্রহ করে পুনরায় চেষ্টা করুন।');
        }

        $code = $request->get('code');
        if (empty($code)) {
            return redirect()->route('settings.index')
                ->with('error', '❌ ফেসবুক থেকে কোনো কোড পাওয়া যায়নি।');
        }

        [$appId, $appSecret] = $this->getFacebookAppCredentials();
        $redirectUri = session('fb_oauth_redirect_uri') ?: url('/facebook-pages/callback');

        try {
            // ১. Code দিয়ে Short-Lived User Access Token নেওয়া
            $tokenRes = Http::withoutVerifying()->asForm()->timeout(15)
                ->post('https://graph.facebook.com/v19.0/oauth/access_token', [
                    'client_id'     => $appId,
                    'client_secret' => $appSecret,
                    'redirect_uri'  => $redirectUri,
                    'code'          => $code,
                ]);

            $tokenData = $tokenRes->json();
            if (!$tokenRes->successful() || empty($tokenData['access_token'])) {
                $errMsg = $tokenData['error']['message'] ?? 'User token retrieval failed.';
                return redirect()->route('settings.index')->with('error', '❌ টোকেন এরর: ' . $errMsg);
            }

            $shortLivedToken = $tokenData['access_token'];

            // ২. Short-Lived Token কে 60-Day Long-Lived Token এ রূপান্তর
            $exchangeRes = Http::withoutVerifying()->timeout(15)
                ->get('https://graph.facebook.com/v19.0/oauth/access_token', [
                    'grant_type'        => 'fb_exchange_token',
                    'client_id'         => $appId,
                    'client_secret'     => $appSecret,
                    'fb_exchange_token' => $shortLivedToken,
                ]);

            $exchangeData = $exchangeRes->json();
            $longLivedUserToken = (!empty($exchangeData['access_token'])) ? $exchangeData['access_token'] : $shortLivedToken;

            // ৩. ইউজারের সকল Facebook Pages ও পার্মানেন্ট Page Access Token সংগ্রহ
            $pagesRes = Http::withoutVerifying()->timeout(15)
                ->get('https://graph.facebook.com/v19.0/me/accounts', [
                    'fields'       => 'id,name,access_token,picture{url},tasks,is_published',
                    'limit'        => 100,
                    'access_token' => $longLivedUserToken,
                ]);

            $pagesData = $pagesRes->json();
            Log::info("Facebook /me/accounts Response: " . $pagesRes->body());

            if (!$pagesRes->successful() || empty($pagesData['data'])) {
                // Check if user has business accounts or pages
                $bizRes = Http::withoutVerifying()->timeout(15)
                    ->get('https://graph.facebook.com/v19.0/me/businesses', [
                        'fields'       => 'id,name,client_pages{id,name,access_token},owned_pages{id,name,access_token}',
                        'access_token' => $longLivedUserToken,
                    ]);
                $bizData = $bizRes->json();
                Log::info("Facebook /me/businesses Response: " . $bizRes->body());

                $foundPages = [];
                if (!empty($bizData['data'])) {
                    foreach ($bizData['data'] as $biz) {
                        $allBizPages = array_merge(
                            $biz['client_pages']['data'] ?? [],
                            $biz['owned_pages']['data'] ?? []
                        );
                        foreach ($allBizPages as $bp) {
                            if (!empty($bp['id']) && !empty($bp['access_token'])) {
                                $foundPages[] = $bp;
                            }
                        }
                    }
                }

                if (empty($foundPages)) {
                    return redirect()->route('settings.index')
                        ->with('warning', '⚠️ আপনার ফেসবুক আইডির আন্ডারে কোনো সক্রিয় ফেসবুক পেজ পাওয়া যায়নি অথবা লগইন করার সময় পেজ সিলেক্ট করে পারমিশন দেওয়া হয়নি। অনুগ্রহ করে লগইন ডায়ালগে আপনার পেজটি টিক চিহ্ন দিয়ে নির্বাচন করুন, অথবা "ম্যানুয়াল পেজ যুক্ত করুন" অপশন ব্যবহার করুন।');
                }

                $pagesData['data'] = $foundPages;
            }

            $savedCount = 0;
            $savedNames = [];

            foreach ($pagesData['data'] as $p) {
                $pId    = $p['id'];
                $pName  = $p['name'] ?? 'Facebook Page';
                $pToken = $p['access_token'] ?? null;

                if ($pId && $pToken) {
                    FacebookPage::updateOrCreate(
                        [
                            'user_id' => Auth::id(),
                            'page_id' => $pId,
                        ],
                        [
                            'page_name'         => $pName,
                            'access_token'      => $pToken,
                            'is_active'         => true,
                            'is_studio_default' => true,
                            'comment_link'      => false,
                            'test_status'       => 'connected',
                            'last_tested_at'    => now(),
                        ]
                    );
                    $savedCount++;
                    $savedNames[] = $pName;
                }
            }

            // স্বয়ংক্রিয়ভাবে post_to_fb সক্রিয় করা
            UserSetting::updateOrCreate(
                ['user_id' => Auth::id()],
                ['post_to_fb' => true]
            );

            $namesList = implode(', ', $savedNames);
            return redirect()->route('settings.index')
                ->with('success', "🎉 সফলভাবে {$savedCount}টি ফেসবুক পেজ কানেক্ট হয়েছে: {$namesList}");

        } catch (\Exception $e) {
            Log::error("Facebook 1-Click OAuth Exception: " . $e->getMessage());
            return redirect()->route('settings.index')
                ->with('error', '❌ কানেকশন এরর: ' . $e->getMessage());
        }
    }

    /**
     * ⚡ স্মার্ট পেজ অটো-ডিটেকশন (Token Paste করলে সব পেজ ফেচ করে দিবে)
     */
    public function fetchPagesWithToken(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        $token = $this->cleanToken($request->access_token);

        if (empty($token)) {
            return response()->json(['success' => false, 'message' => '❌ সঠিক টোকেন প্রদান করুন।']);
        }

        try {
            // ১. চেষ্টা করি /me/accounts (User Access Token ধরে)
            $response = Http::withoutVerifying()->timeout(15)
                ->get('https://graph.facebook.com/v19.0/me/accounts', [
                    'fields'       => 'id,name,access_token,picture{url}',
                    'access_token' => $token,
                ]);

            $data = $response->json();

            // ২. যদি /me/accounts এ না পায়, তবে টোকেনটি নিজেই Page Access Token হতে পারে!
            if (!$response->successful() || empty($data['data'])) {
                $pageRes = Http::withoutVerifying()->timeout(15)
                    ->get('https://graph.facebook.com/v19.0/me', [
                        'fields'       => 'id,name,picture{url}',
                        'metadata'     => 1,
                        'access_token' => $token,
                    ]);

                $singleData = $pageRes->json();
                $nodeType = $singleData['metadata']['type'] ?? null;
                $isExplicitUser = ($nodeType === 'user');

                if ($pageRes->successful() && isset($singleData['id']) && !$isExplicitUser) {
                    return response()->json([
                        'success' => true,
                        'pages'   => [[
                            'id'           => $singleData['id'],
                            'name'         => $singleData['name'] ?? 'Facebook Page',
                            'access_token' => $token,
                            'picture'      => $singleData['picture']['data']['url'] ?? null,
                        ]],
                    ]);
                }

                // ৩. Business Manager Pages চেক করি
                $bizRes = Http::withoutVerifying()->timeout(15)
                    ->get('https://graph.facebook.com/v19.0/me/businesses', [
                        'fields'       => 'id,name,client_pages{id,name,access_token},owned_pages{id,name,access_token}',
                        'access_token' => $token,
                    ]);
                $bizData = $bizRes->json();
                $foundPages = [];
                if (!empty($bizData['data'])) {
                    foreach ($bizData['data'] as $biz) {
                        $allBizPages = array_merge(
                            $biz['client_pages']['data'] ?? [],
                            $biz['owned_pages']['data'] ?? []
                        );
                        foreach ($allBizPages as $bp) {
                            if (!empty($bp['id']) && !empty($bp['access_token'])) {
                                $foundPages[] = [
                                    'id'           => $bp['id'],
                                    'name'         => $bp['name'] ?? 'Facebook Page',
                                    'access_token' => $bp['access_token'],
                                    'picture'      => null,
                                ];
                            }
                        }
                    }
                }

                if (!empty($foundPages)) {
                    return response()->json([
                        'success' => true,
                        'pages'   => $foundPages,
                    ]);
                }

                // ইউজার টোকেন কিন্তু পেজ পারমিশন না থাকলে স্পষ্ট বাংলা গাইডলাইন
                if ($isExplicitUser || (isset($singleData['id']) && empty($data['data']))) {
                    $userName = $singleData['name'] ?? 'Facebook User';
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ আপনি \"{$userName}\" এর ব্যক্তিগত ইউজার টোকেন (User Token) পেস্ট করেছেন। পেজ কানেক্ট করতে হলে Graph API Explorer-এ 'User or Page' ড্রপডাউন থেকে আপনার পেজটি নির্বাচন করে Page Access Token কপি করুন, অথবা টোকেন তৈরির সময় 'pages_show_list', 'pages_read_engagement', 'pages_manage_posts' পারমিশন দিন।"
                    ]);
                }

                $errMsg = $data['error']['message'] ?? $singleData['error']['message'] ?? 'কোনো পেজ পাওয়া যায়নি। টোকেনটি সঠিক কিনা এবং পেজ পারমিশন আছে কিনা যাচাই করুন।';
                return response()->json(['success' => false, 'message' => '❌ ' . $errMsg]);
            }

            $pages = [];
            foreach ($data['data'] as $p) {
                $pages[] = [
                    'id'           => $p['id'],
                    'name'         => $p['name'],
                    'access_token' => $p['access_token'] ?? $token,
                    'picture'      => $p['picture']['data']['url'] ?? null,
                ];
            }

            return response()->json([
                'success' => true,
                'pages'   => $pages,
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '❌ এরর: ' . $e->getMessage()]);
        }
    }

    /**
     * ⚡ Meta App Handshake Test (App ID & Secret validity check)
     */
    public function testAppCredentials(Request $request)
    {
        $appId = trim($request->fb_app_id ?? '');
        $appSecret = trim($request->fb_app_secret ?? '');

        if (empty($appId) || empty($appSecret)) {
            [$appId, $appSecret] = $this->getFacebookAppCredentials();
        }

        if (empty($appId) || empty($appSecret)) {
            return response()->json(['success' => false, 'message' => '❌ App ID এবং App Secret দিতে হবে।']);
        }

        try {
            $appToken = "{$appId}|{$appSecret}";
            $response = Http::withoutVerifying()->timeout(12)
                ->get("https://graph.facebook.com/v19.0/{$appId}", [
                    'fields'       => 'id,name',
                    'access_token' => $appToken,
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                $appName = $data['name'] ?? 'Meta App';
                return response()->json([
                    'success' => true,
                    'message' => "✅ Meta App কানেকশন সফল!\nApp Name: {$appName}\nApp ID: {$data['id']}\nস্ট্যাটাস: ১-ক্লিক লগইন ও পেজ কানেকশনের জন্য প্রস্তুত 🚀",
                ]);
            } else {
                $errMsg = $data['error']['message'] ?? 'Meta API validation failed';
                return response()->json(['success' => false, 'message' => '❌ কানেকশন ফেইল্ড: ' . $errMsg]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '❌ নেটওয়ার্ক এরর: ' . $e->getMessage()]);
        }
    }

    /**
     * Save Meta App ID & Secret for Super Admin
     */
    public function saveAppCredentials(Request $request)
    {
        if (Auth::user()->role !== 'super_admin' && !Auth::user()->hasPermission('can_settings')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'fb_app_id'     => 'required|string',
            'fb_app_secret' => 'required|string',
        ]);

        $setting = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
        $setting->update([
            'fb_app_id'     => trim($request->fb_app_id),
            'fb_app_secret' => trim($request->fb_app_secret),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Meta App Credentials সফলভাবে সেভ হয়েছে!',
        ]);
    }

    /**
     * নতুন Facebook Page Test করে Save করা (Manual / Smart Auto-Detect)
     */
    public function store(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
            'page_id'      => 'nullable|string',
            'page_name'    => 'nullable|string|max:100',
            'comment_link' => 'nullable|boolean',
            'force_save'   => 'nullable|boolean',
        ]);

        $token  = $this->cleanToken($request->access_token);
        $pageId = $this->cleanPageId($request->page_id);
        $forceSave = $request->boolean('force_save', false);

        if (empty($token)) {
            return response()->json(['success' => false, 'message' => '❌ অ্যাক্সেস টোকেন প্রদান করুন।'], 422);
        }

        $resolvedName = trim($request->page_name ?? '');
        $testPassed   = false;
        $errorMsg     = null;

        // ১. Smart Resolution: টোকেন দিয়ে /me থেকে পেজ ডিটেক্ট করার চেষ্টা করি
        try {
            $meRes = Http::withoutVerifying()->timeout(12)
                ->get("https://graph.facebook.com/v19.0/me", [
                    'fields'       => 'id,name',
                    'access_token' => $token,
                ]);

            $meData = $meRes->json();

            if ($meRes->successful() && isset($meData['id'])) {
                // টোকেনটি যদি একটি Page Access Token হয়, তবে /me সরাসরি পেজের ID ও Name দিবে
                if (empty($pageId) || $pageId === $meData['id']) {
                    $pageId = $meData['id'];
                    $resolvedName = !empty($resolvedName) ? $resolvedName : ($meData['name'] ?? 'Facebook Page');
                    $testPassed = true;
                }
            }
        } catch (\Throwable $e) {
            $errorMsg = $e->getMessage();
        }

        // ২. যদি /me থেকে নিশ্চিত না হয় এবং $pageId দেওয়া থাকে, তবে /{page_id} টেস্ট করি
        if (!$testPassed && !empty($pageId)) {
            try {
                $response = Http::withoutVerifying()->timeout(12)
                    ->get("https://graph.facebook.com/v19.0/{$pageId}", [
                        'fields'       => 'id,name',
                        'access_token' => $token,
                    ]);

                $data = $response->json();

                if ($response->successful() && isset($data['id'])) {
                    $pageId = $data['id'];
                    $resolvedName = !empty($resolvedName) ? $resolvedName : ($data['name'] ?? 'Facebook Page');
                    $testPassed = true;
                } else {
                    $errorMsg = $data['error']['message'] ?? 'Meta API validation failed';
                }
            } catch (\Throwable $e) {
                $errorMsg = $e->getMessage();
            }
        }

        // ৩. যদি এখনও $pageId না পাওয়া যায়, তবে /me/accounts থেকে পেজ খোঁজার চেষ্টা করি
        if (empty($pageId)) {
            try {
                $accRes = Http::withoutVerifying()->timeout(12)
                    ->get("https://graph.facebook.com/v19.0/me/accounts", [
                        'fields'       => 'id,name,access_token',
                        'access_token' => $token,
                    ]);
                $accData = $accRes->json();
                if ($accRes->successful() && !empty($accData['data'][0]['id'])) {
                    $firstPage = $accData['data'][0];
                    $pageId = $firstPage['id'];
                    $resolvedName = !empty($resolvedName) ? $resolvedName : ($firstPage['name'] ?? 'Facebook Page');
                    $token = $firstPage['access_token'] ?? $token;
                    $testPassed = true;
                }
            } catch (\Throwable $e) {
                // Continue
            }
        }

        // যদি $pageId একেবারেই না থাকে এবং forceSave ও না থাকে
        if (empty($pageId)) {
            if ($forceSave && $request->filled('page_name')) {
                // Force save with fake or temporary page ID
                $pageId = 'manual_' . time();
            } else {
                return response()->json([
                    'success'        => false,
                    'can_force_save' => true,
                    'message'        => '❌ Facebook Page ID পাওয়া যায়নি! অনুগ্রহ করে সঠিক Page ID প্রদান করুন অথবা "স্মার্ট টোকেন ফেচ" ব্যবহার করুন। ' . ($errorMsg ? "({$errorMsg})" : ''),
                ], 422);
            }
        }

        // যদি টেস্ট ফেইল করে কিন্তু ইউজার Force Save না চায়
        if (!$testPassed && !$forceSave) {
            return response()->json([
                'success'        => false,
                'can_force_save' => true,
                'message'        => '❌ ফেসবুক টেস্ট ফেইল্ড: ' . ($errorMsg ?: 'টোকেন অথবা পেজ আইডি ভুল।') . ' আপনি চাইলে "বাধ্যতামূলক সেভ" করতে পারেন।',
            ]);
        }

        $finalName = !empty($resolvedName) ? $resolvedName : 'My Facebook Page';
        $testStatus = $testPassed ? 'connected' : 'failed';

        // ডাটাবেজে সেভ
        $page = FacebookPage::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'page_id' => $pageId,
            ],
            [
                'page_name'         => $finalName,
                'access_token'      => $token,
                'is_active'         => true,
                'is_studio_default' => true,
                'comment_link'      => $request->boolean('comment_link', false),
                'test_status'       => $testStatus,
                'last_tested_at'    => now(),
            ]
        );

        // ইউজার সেটিংসে post_to_fb সক্রিয় করা
        UserSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            ['post_to_fb' => true]
        );

        $statusMsg = $testPassed
            ? "✅ পেজ সফলভাবে যুক্ত ও কানেক্ট হয়েছে: {$finalName}"
            : "⚠️ পেজটি সেভ হয়েছে, তবে ফেসবুক ভেরিফিকেশন ফেইল করেছে: {$finalName} (স্ট্যাটাস: Failed)";

        return response()->json([
            'success' => true,
            'warning' => !$testPassed,
            'message' => $statusMsg,
            'page'    => [
                'id'                => $page->id,
                'page_name'         => $page->page_name,
                'page_id'           => $page->page_id,
                'is_active'         => $page->is_active,
                'is_studio_default' => $page->is_studio_default,
                'comment_link'      => $page->comment_link,
                'test_status'       => $page->test_status,
            ],
        ]);
    }

    /**
     * 🚀 Multi-Page Batch Save (একাধিক পেজ একসাথে কানেক্ট করা)
     */
    public function storeMultiple(Request $request)
    {
        $request->validate([
            'pages' => 'required|array|min:1',
        ]);

        $pages = $request->input('pages', []);
        $savedCount = 0;
        $savedNames = [];

        foreach ($pages as $p) {
            $pId    = $this->cleanPageId($p['page_id'] ?? $p['id'] ?? null);
            $pToken = $this->cleanToken($p['access_token'] ?? null);
            $pName  = trim($p['page_name'] ?? $p['name'] ?? 'Facebook Page');

            if (!empty($pId) && !empty($pToken)) {
                FacebookPage::updateOrCreate(
                    [
                        'user_id' => Auth::id(),
                        'page_id' => $pId,
                    ],
                    [
                        'page_name'         => $pName,
                        'access_token'      => $pToken,
                        'is_active'         => true,
                        'is_studio_default' => true,
                        'comment_link'      => (bool)($p['comment_link'] ?? false),
                        'test_status'       => 'connected',
                        'last_tested_at'    => now(),
                    ]
                );
                $savedCount++;
                $savedNames[] = $pName;
            }
        }

        // ইউজার সেটিংসে post_to_fb সক্রিয় করা
        UserSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            ['post_to_fb' => true]
        );

        $namesList = implode(', ', $savedNames);

        return response()->json([
            'success' => true,
            'count'   => $savedCount,
            'message' => "🎉 সফলভাবে {$savedCount}টি পেজ কানেক্ট হয়েছে: {$namesList}",
        ]);
    }

    /**
     * সেভ করা পেজ আপডেট করা (যেমন: টোকেন বা নাম পরিবর্তন)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'access_token' => 'required|string',
            'page_name'    => 'nullable|string|max:100',
            'force_save'   => 'nullable|boolean',
        ]);

        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);
        $token = $this->cleanToken($request->access_token);
        $forceSave = $request->boolean('force_save', false);

        $testPassed = false;
        $errorMsg = null;
        $fetchedName = $page->page_name;

        try {
            // Try /me first
            $response = Http::withoutVerifying()->timeout(12)
                ->get("https://graph.facebook.com/v19.0/me", [
                    'fields'       => 'id,name',
                    'access_token' => $token,
                ]);
            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                $testPassed = true;
                $fetchedName = $data['name'] ?? $page->page_name;
            } else {
                // Try /{page_id}
                $response = Http::withoutVerifying()->timeout(12)
                    ->get("https://graph.facebook.com/v19.0/{$page->page_id}", [
                        'fields'       => 'id,name',
                        'access_token' => $token,
                    ]);
                $data = $response->json();
                if ($response->successful() && isset($data['id'])) {
                    $testPassed = true;
                    $fetchedName = $data['name'] ?? $page->page_name;
                } else {
                    $errorMsg = $data['error']['message'] ?? 'Meta API error';
                }
            }
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
        }

        if (!$testPassed && !$forceSave) {
            return response()->json([
                'success'        => false,
                'can_force_save' => true,
                'message'        => '❌ ফেসবুক আপডেট টেস্ট ফেইল্ড: ' . ($errorMsg ?: 'টোকেনটি সঠিক নয়।'),
            ]);
        }

        $finalName = $request->filled('page_name') ? trim($request->page_name) : $fetchedName;

        $page->update([
            'page_name'      => $finalName,
            'access_token'   => $token,
            'test_status'    => $testPassed ? 'connected' : 'failed',
            'last_tested_at' => now(),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => $testPassed ? "✅ পেজ আপডেট সফল: {$finalName}" : "⚠️ পেজ আপডেট হয়েছে (ভেরিফিকেশন সতর্কতা): {$finalName}",
            'page_name' => $finalName
        ]);
    }

    /**
     * সেভ করা পেজ পুনরায় Test করা
     */
    public function test($id)
    {
        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);

        if (empty($page->access_token)) {
            $page->update(['test_status' => 'failed', 'last_tested_at' => now()]);
            return response()->json(['success' => false, 'message' => '❌ এই পেজের কোনো অ্যাক্সেস টোকেন নেই!']);
        }

        try {
            // ১. চেষ্টা করি /me
            $response = Http::withoutVerifying()->timeout(12)
                ->get("https://graph.facebook.com/v19.0/me", [
                    'fields'       => 'id,name',
                    'access_token' => $page->access_token,
                ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                $page->update(['test_status' => 'connected', 'last_tested_at' => now()]);
                return response()->json(['success' => true, 'message' => '✅ কানেকশন সফল! পেজ: ' . ($data['name'] ?? $page->page_name)]);
            }

            // ২. চেষ্টা করি /{page_id}
            $response2 = Http::withoutVerifying()->timeout(12)
                ->get("https://graph.facebook.com/v19.0/{$page->page_id}", [
                    'fields'       => 'id,name',
                    'access_token' => $page->access_token,
                ]);

            $data2 = $response2->json();

            if ($response2->successful() && isset($data2['id'])) {
                $page->update(['test_status' => 'connected', 'last_tested_at' => now()]);
                return response()->json(['success' => true, 'message' => '✅ কানেকশন সফল! পেজ: ' . ($data2['name'] ?? $page->page_name)]);
            }

            $errorMsg = $data2['error']['message'] ?? $data['error']['message'] ?? 'Unknown Error';
            $page->update(['test_status' => 'failed', 'last_tested_at' => now()]);
            return response()->json(['success' => false, 'message' => '❌ কানেকশন ব্যর্থ: ' . $errorMsg]);

        } catch (\Exception $e) {
            $page->update(['test_status' => 'failed', 'last_tested_at' => now()]);
            return response()->json(['success' => false, 'message' => '❌ এরর: ' . $e->getMessage()]);
        }
    }

    /**
     * Active / Inactive Toggle
     */
    public function toggle($id)
    {
        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);
        $page->update(['is_active' => !$page->is_active]);

        return response()->json([
            'success'   => true,
            'is_active' => $page->is_active,
            'message'   => $page->is_active ? '✅ পেজ Active করা হয়েছে' : '⏸️ পেজ Inactive করা হয়েছে',
        ]);
    }

    /**
     * Comment Link Toggle
     */
    public function toggleCommentLink($id)
    {
        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);
        $page->update(['comment_link' => !$page->comment_link]);

        return response()->json([
            'success'      => true,
            'comment_link' => $page->comment_link,
            'message'      => $page->comment_link ? '✅ লিংক কমেন্টে যাবে' : '📝 লিংক ক্যাপশনে যাবে',
        ]);
    }

    /**
     * Studio Default Toggle — Studio modal এ auto-check হবে কিনা
     */
    public function setDefault($id)
    {
        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);
        $page->update(['is_studio_default' => !$page->is_studio_default]);

        return response()->json([
            'success'           => true,
            'is_studio_default' => $page->is_studio_default,
            'message'           => $page->is_studio_default
                ? '✅ Studio তে default checked হবে'
                : '⬜ Studio তে default unchecked হবে',
        ]);
    }

    /**
     * Master Auto-Post Switch Toggle
     */
    public function toggleMasterAutoPost(Request $request)
    {
        $setting = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
        $enabled = $request->has('enabled') ? $request->boolean('enabled') : !$setting->post_to_fb;
        $setting->update(['post_to_fb' => $enabled]);

        return response()->json([
            'success'    => true,
            'post_to_fb' => $enabled,
            'message'    => $enabled ? '✅ অটো-ফেসবুক পোস্টিং চালু করা হয়েছে' : '⏸️ অটো-ফেসবুক পোস্টিং বন্ধ করা হয়েছে',
        ]);
    }

    /**
     * পেজ Delete করা
     */
    public function destroy($id)
    {
        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);
        $pageName = $page->page_name;
        $page->delete();

        return response()->json(['success' => true, 'message' => "🗑️ '{$pageName}' মুছে ফেলা হয়েছে।"]);
    }

    /**
     * 🚀 Direct Facebook Publishing with First Comment support
     * POST /facebook/direct-publish
     */
    public function directPublish(Request $request, SocialPostService $socialPostService)
    {
        if (!$request->has('pages') && $request->has('page_ids')) {
            $request->merge(['pages' => (array) $request->input('page_ids')]);
        }
        if (!$request->has('message') && $request->has('caption')) {
            $request->merge(['message' => $request->input('caption')]);
        }

        $request->validate([
            'pages'         => 'required|array|min:1',
            'pages.*'       => 'required',
            'message'       => 'required_without:image|nullable|string',
            'image'         => 'nullable',
            'first_comment' => 'nullable|string|max:5000',
        ], [
            'pages.required'           => 'অন্তত একটি ফেসবুক পেজ নির্বাচন করুন।',
            'pages.min'                => 'অন্তত একটি ফেসবুক পেজ নির্বাচন করুন।',
            'message.required_without' => 'ছবি না থাকলে পোস্টের টেক্সট বা ক্যাপশন প্রদান করা আবশ্যক।',
        ]);

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'file|image|max:20480',
            ], [
                'image.image' => 'আপলোড করা ফাইলটি একটি সঠিক ইমেজ (JPG, PNG, WebP) হতে হবে।',
                'image.max'   => 'ইমেজের সাইজ সর্বোচ্চ ২০ মেগাবাইট হতে পারবে।',
            ]);
        }

        $user = Auth::user();
        $pageInput = $request->input('pages', []);

        // Strict tenant isolation: fetch only active/connected pages belonging to this user
        $pages = $user->facebookPages()
            ->where(function ($query) use ($pageInput) {
                $query->whereIn('id', $pageInput)
                      ->orWhereIn('page_id', $pageInput);
            })
            ->get();

        if ($pages->isEmpty()) {
            $errorPayload = [
                'success' => false,
                'message' => '❌ নির্বাচিত কোনো ফেসবুক পেজ পাওয়া যায়নি অথবা আপনার অ্যাকাউন্টে যুক্ত নেই।',
                'results' => [],
            ];

            if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
                return response()->json($errorPayload, 404);
            }

            return back()->with('error', $errorPayload['message']);
        }

        $image = $request->file('image') ?: $request->input('image');
        $message = $request->input('message');
        $firstComment = $request->input('first_comment');

        $results = $socialPostService->publishDirectToPages($pages, $message, $image, $firstComment);

        $hasSuccess = collect($results)->contains(fn($r) => !empty($r['success']));
        $allSuccess = collect($results)->every(fn($r) => !empty($r['success']));

        $statusMessage = $allSuccess
            ? '✅ সকল নির্বাচিত পেজে সফলভাবে পোস্ট সম্পন্ন হয়েছে!'
            : ($hasSuccess
                ? '⚠️ কিছু পেজে সফলভাবে পোস্ট হয়েছে, তবে কিছু পেজে ব্যর্থ হয়েছে।'
                : '❌ ফেসবুক পেজে পোস্ট প্রকাশ করতে ব্যর্থ হয়েছে।');

        if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
            return response()->json([
                'success'     => $hasSuccess,
                'all_success' => $allSuccess,
                'message'     => $statusMessage,
                'results'     => $results,
            ], $hasSuccess ? 200 : 422);
        }

        if ($allSuccess) {
            return back()->with('success', $statusMessage)->with('publish_results', $results);
        } elseif ($hasSuccess) {
            return back()->with('warning', $statusMessage)->with('publish_results', $results);
        } else {
            return back()->with('error', $statusMessage)->with('publish_results', $results);
        }
    }
}
