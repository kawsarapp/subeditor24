<?php

namespace App\Http\Controllers;

use App\Models\FacebookPage;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FacebookPageController extends Controller
{
    /**
     * Get Facebook App ID and App Secret with DB / .env fallbacks
     */
    protected function getFacebookAppCredentials(): array
    {
        $appId = config('services.facebook.app_id') ?: env('FACEBOOK_APP_ID');
        $appSecret = config('services.facebook.app_secret') ?: env('FACEBOOK_APP_SECRET');

        if (empty($appId) || empty($appSecret)) {
            $superAdminSetting = UserSetting::whereNotNull('fb_app_id')->where('fb_app_id', '!=', '')->first();
            if ($superAdminSetting) {
                $appId = $superAdminSetting->fb_app_id ?: $appId;
                $appSecret = $superAdminSetting->fb_app_secret ?: $appSecret;
            }
        }

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

        $redirectUri = route('fb-pages.callback');
        $scope = implode(',', [
            'pages_show_list',
            'pages_read_engagement',
            'pages_manage_posts',
            'pages_read_user_content',
            'public_profile',
            'business_management'
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
        $sessionState = session('fb_oauth_state');

        if ($request->has('error')) {
            $errorDesc = $request->get('error_description', 'ব্যবহারকারী ফেসবুক কানেকশন বাতিল করেছেন।');
            return redirect()->route('settings.index')
                ->with('error', '❌ ফেসবুক কানেকশন ব্যর্থ হয়েছে: ' . $errorDesc);
        }

        if (empty($state) || $state !== $sessionState) {
            return redirect()->route('settings.index')
                ->with('error', '❌ সিকিউরিটি টোকেন মিসম্যাচ (CSRF State Error)! অনুগ্রহ করে পুনরায় চেষ্টা করুন।');
        }

        $code = $request->get('code');
        if (empty($code)) {
            return redirect()->route('settings.index')
                ->with('error', '❌ ফেসবুক থেকে কোনো কোড পাওয়া যায়নি।');
        }

        [$appId, $appSecret] = $this->getFacebookAppCredentials();
        $redirectUri = route('fb-pages.callback');

        try {
            // ১. Code দিয়ে Short-Lived User Access Token নেওয়া
            $tokenRes = Http::asForm()->post('https://graph.facebook.com/v19.0/oauth/access_token', [
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
            $exchangeRes = Http::get('https://graph.facebook.com/v19.0/oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => $appId,
                'client_secret'     => $appSecret,
                'fb_exchange_token' => $shortLivedToken,
            ]);

            $exchangeData = $exchangeRes->json();
            $longLivedUserToken = (!empty($exchangeData['access_token'])) ? $exchangeData['access_token'] : $shortLivedToken;

            // Permissions Check
            $permsRes = Http::get('https://graph.facebook.com/v19.0/me/permissions', [
                'access_token' => $longLivedUserToken,
            ]);
            Log::info("Facebook User Permissions: " . $permsRes->body());

            // ৩. ইউজারের সকল Facebook Pages ও পার্মানেন্ট Page Access Token সংগ্রহ
            $pagesRes = Http::get('https://graph.facebook.com/v19.0/me/accounts', [
                'fields'       => 'id,name,access_token,category,picture{url},tasks,is_published',
                'limit'        => 100,
                'access_token' => $longLivedUserToken,
            ]);

            $pagesData = $pagesRes->json();
            Log::info("Facebook /me/accounts Response: " . $pagesRes->body());

            if (!$pagesRes->successful() || empty($pagesData['data'])) {
                // Check if user has business accounts or pages
                $bizRes = Http::get('https://graph.facebook.com/v19.0/me/businesses', [
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
                        ->with('warning', '⚠️ আপনার ফেসবুক আইডির আন্ডারে কোনো সক্রিয় ফেসবুক পেজ পাওয়া যায়নি অথবা লগইন করার সময় পেজ সিলেক্ট করে পারমিশন দেওয়া হয়নি। অনুগ্রহ করে লগইন ডায়ালগে আপনার পেজটি টিক চিহ্ন দিয়ে নির্বাচন করুন।');
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

        $token = trim($request->access_token);

        try {
            $response = Http::timeout(10)->get('https://graph.facebook.com/v19.0/me/accounts', [
                'fields'       => 'id,name,access_token,category,picture{url}',
                'access_token' => $token,
            ]);

            $data = $response->json();

            if (!$response->successful() || empty($data['data'])) {
                // If token itself is a Page Token, try fetching single page
                $pageRes = Http::timeout(10)->get('https://graph.facebook.com/v19.0/me', [
                    'fields'       => 'id,name,picture{url}',
                    'access_token' => $token,
                ]);
                $singleData = $pageRes->json();
                if ($pageRes->successful() && isset($singleData['id'])) {
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

                $errMsg = $data['error']['message'] ?? 'কোনো পেজ পাওয়া যায়নি। টোকেনটি সঠিক কিনা যাচাই করুন।';
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
            // App Access Token handshake: appId|appSecret
            $appToken = "{$appId}|{$appSecret}";
            $response = Http::timeout(10)->get("https://graph.facebook.com/v19.0/{$appId}", [
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
     * নতুন Facebook Page Test করে Save করা (Manual)
     */
    public function store(Request $request)
    {
        $request->validate([
            'page_id'      => 'required|string',
            'access_token' => 'required|string',
            'page_name'    => 'nullable|string|max:100',
            'comment_link' => 'nullable|boolean',
        ]);

        $pageId = trim($request->page_id);
        $token  = trim($request->access_token);

        try {
            $response = Http::timeout(10)->get("https://graph.facebook.com/v19.0/{$pageId}", [
                'fields'       => 'id,name',
                'access_token' => $token,
            ]);

            $data = $response->json();

            if (!$response->successful() || !isset($data['id'])) {
                $errorMsg = $data['error']['message'] ?? 'Unknown Facebook Error';
                return response()->json(['success' => false, 'message' => '❌ Test Failed: ' . $errorMsg]);
            }

            $fetchedName = $data['name'] ?? 'My Facebook Page';
            $finalName   = $request->filled('page_name') ? $request->page_name : $fetchedName;

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '❌ Connection Error: ' . $e->getMessage()]);
        }

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
                'test_status'       => 'connected',
                'last_tested_at'    => now(),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "✅ পেজ কানেক্টেড: {$finalName}",
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
     * সেভ করা পেজ আপডেট করা (যেমন: টোকেন বা নাম পরিবর্তন)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'access_token' => 'required|string',
            'page_name'    => 'nullable|string|max:100',
        ]);

        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);
        $token  = trim($request->access_token);

        try {
            $response = Http::timeout(10)->get("https://graph.facebook.com/v19.0/{$page->page_id}", [
                'fields'       => 'id,name',
                'access_token' => $token,
            ]);

            $data = $response->json();

            if (!$response->successful() || !isset($data['id'])) {
                $errorMsg = $data['error']['message'] ?? 'Unknown Facebook Error';
                return response()->json(['success' => false, 'message' => '❌ Update Failed: ' . $errorMsg]);
            }

            $fetchedName = $data['name'] ?? $page->page_name;
            $finalName   = $request->filled('page_name') ? $request->page_name : $fetchedName;

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => '❌ Connection Error: ' . $e->getMessage()]);
        }

        $page->update([
            'page_name'      => $finalName,
            'access_token'   => $token,
            'test_status'    => 'connected',
            'last_tested_at' => now(),
        ]);

        return response()->json([
            'success'   => true,
            'message'   => "✅ পেজ আপডেট সফল: {$finalName}",
            'page_name' => $finalName
        ]);
    }

    /**
     * সেভ করা পেজ পুনরায় Test করা
     */
    public function test($id)
    {
        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);

        try {
            $response = Http::timeout(10)->get("https://graph.facebook.com/v19.0/{$page->page_id}", [
                'fields'       => 'id,name',
                'access_token' => $page->access_token,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                $page->update(['test_status' => 'connected', 'last_tested_at' => now()]);
                return response()->json(['success' => true, 'message' => '✅ Connected: ' . ($data['name'] ?? $page->page_name)]);
            } else {
                $errorMsg = $data['error']['message'] ?? 'Unknown Error';
                $page->update(['test_status' => 'failed', 'last_tested_at' => now()]);
                return response()->json(['success' => false, 'message' => '❌ Failed: ' . $errorMsg]);
            }
        } catch (\Exception $e) {
            $page->update(['test_status' => 'failed', 'last_tested_at' => now()]);
            return response()->json(['success' => false, 'message' => '❌ Error: ' . $e->getMessage()]);
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
            'message'      => $page->comment_link ? '✅ লিংক কমেন্টে যাবে' : '⏸️ লিংক ক্যাপশনে যাবে',
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
     * পেজ Delete করা
     */
    public function destroy($id)
    {
        $page = FacebookPage::where('user_id', Auth::id())->findOrFail($id);
        $pageName = $page->page_name;
        $page->delete();

        return response()->json(['success' => true, 'message' => "🗑️ '{$pageName}' মুছে ফেলা হয়েছে।"]);
    }
}

