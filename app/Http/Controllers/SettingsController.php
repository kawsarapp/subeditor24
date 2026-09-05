<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Auth;
use App\Services\WordPressService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cache;
use App\Services\PhotoRoomService;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * ১. সেটিংস পেজ ভিউ
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_settings')) {
            return redirect()->route('news.index')->with('error', 'আপনার সেটিংস পরিবর্তনের অনুমতি নেই।');
        }

        $settings = $user->settings ?? new UserSetting(['user_id' => $user->id]);
        $fbPages  = $user->facebookPages()->orderByDesc('is_active')->get();
        return view('settings.index', compact('settings', 'fbPages'));
    }

    /**
     * 📖 Public API Integration Guide
     */
    public function apiGuide()
    {
        return view('settings.api-guide');
    }

    /**
     * ২. সেটিংস আপডেট
     */
    public function update(Request $request)
    {
        if (Auth::user()->role !== 'super_admin' && !Auth::user()->hasPermission('can_settings')) {
            return abort(403, 'Unauthorized');
        }

        $request->validate([
            'brand_name'           => 'nullable|string|max:50',
            'wp_url'               => 'nullable|url',
            'wp_username'          => 'nullable|string',
            'wp_app_password'      => 'nullable|string',
            'fb_page_id'           => 'nullable|string',
            'fb_access_token'      => 'nullable|string',
            'telegram_bot_token'   => 'nullable|string',
            'telegram_channel_id'  => 'nullable|string',
            'twitter_api_key'      => 'nullable|string',
            'twitter_api_secret'   => 'nullable|string',
            'twitter_access_token' => 'nullable|string',
            'twitter_access_secret'=> 'nullable|string',
            'laravel_site_url'     => 'nullable|url',
            'laravel_api_token'    => 'nullable|string',
            'laravel_route_prefix' => 'nullable|string|max:40',
            'proxy_username'       => 'nullable|string',
            'proxy_password'       => 'nullable|string',
            'proxy_host'           => 'nullable|string',
            'proxy_port'           => 'nullable|string',
            'custom_api_url'       => 'nullable|url',
            'custom_category_url'  => 'nullable|url',
            'custom_api_mapping'   => 'nullable|json',
            'auto_clean_days'      => 'nullable|integer|min:1|max:90',

            // AI Options
            'openai_api_key'       => 'nullable|string',
            'openai_model'         => 'nullable|string',
            'gemini_api_key'       => 'nullable|string',
            'gemini_model'         => 'nullable|string',
            'deepseek_api_key'     => 'nullable|string',
            'deepseek_model'       => 'nullable|string',
            'primary_ai'           => 'nullable|string|in:deepseek,openai,gemini,groq,qwen,huggingface',
            'groq_api_key'         => 'nullable|string',
            'groq_model'           => 'nullable|string',
            'qwen_api_key'         => 'nullable|string',
            'qwen_model'           => 'nullable|string',
            'huggingface_api_key'  => 'nullable|string',
            'huggingface_model'    => 'nullable|string',
            'photoroom_api_key'    => 'nullable|string',
            'target_language'      => 'nullable|in:bn,en',
        ]);
        
        $settings = UserSetting::firstOrCreate(['user_id' => Auth::id()]);

        $user = Auth::user();
        $isSuperAdmin = $user->role === 'super_admin';

        // 🌐 Proxy & Scraper Settings
        if ($isSuperAdmin || $user->hasPermission('can_settings_proxy')) {
            if ($request->has('proxy_username')) $settings->proxy_username = $request->proxy_username;
            if ($request->has('proxy_password')) $settings->proxy_password = $request->proxy_password;
            if ($request->has('proxy_host')) $settings->proxy_host     = $request->proxy_host;
            if ($request->has('proxy_port')) $settings->proxy_port     = $request->proxy_port;
            if ($request->has('smartproxy_api_token')) $settings->smartproxy_api_token = $request->smartproxy_api_token;
            
            // 🧹 Auto Clean Days
            if ($request->filled('auto_clean_days')) {
                $settings->auto_clean_days = (int) $request->auto_clean_days;
            }
        }

        // 🎨 Branding Settings
        if ($isSuperAdmin || $user->hasPermission('can_settings_branding')) {
            if ($request->has('brand_name')) $settings->brand_name = $request->brand_name;
            if ($request->has('default_theme_color')) $settings->default_theme_color = $request->default_theme_color ?? 'red';
            if ($request->filled('logo_url')) $settings->logo_url = $request->logo_url;
        }

        // 🔗 WordPress & Laravel API Settings
        if ($isSuperAdmin || $user->hasPermission('can_settings_wp_laravel')) {
            if ($request->has('wp_url')) $settings->wp_url = $request->wp_url;
            if ($request->has('wp_username')) $settings->wp_username = $request->wp_username;
            if ($request->has('wp_app_password')) $settings->wp_app_password = $request->wp_app_password;

            if ($request->has('laravel_site_url')) $settings->laravel_site_url = $request->laravel_site_url;
            if ($request->has('laravel_api_token')) $settings->laravel_api_token  = $request->laravel_api_token;
            if ($request->has('post_to_laravel')) $settings->post_to_laravel    = $request->has('post_to_laravel');
            if ($request->has('laravel_route_prefix')) $settings->laravel_route_prefix = $request->laravel_route_prefix ?? 'news';

            if ($request->has('custom_api_url')) $settings->custom_api_url      = $request->custom_api_url;
            if ($request->has('custom_category_url')) $settings->custom_category_url = $request->custom_category_url;
            if ($request->has('custom_api_mapping')) $settings->custom_api_mapping  = $request->custom_api_mapping;
        }

        // 📱 Social Media Settings
        if ($isSuperAdmin || $user->hasPermission('can_settings_social')) {
            if ($request->has('fb_page_id')) $settings->fb_page_id = $request->fb_page_id;
            if ($request->has('fb_access_token')) $settings->fb_access_token = $request->fb_access_token;
            // `has('post_to_fb')` is an isset check for checkboxes
            $settings->post_to_fb = $request->has('post_to_fb');
            $settings->fb_comment_link = $request->has('fb_comment_link');

            if ($request->has('telegram_bot_token')) $settings->telegram_bot_token = $request->telegram_bot_token;
            if ($request->has('telegram_channel_id')) $settings->telegram_channel_id = $request->telegram_channel_id;
            $settings->post_to_telegram = $request->has('post_to_telegram');

            if ($request->has('twitter_api_key')) $settings->twitter_api_key = $request->twitter_api_key;
            if ($request->has('twitter_api_secret')) $settings->twitter_api_secret = $request->twitter_api_secret;
            if ($request->has('twitter_access_token')) $settings->twitter_access_token  = $request->twitter_access_token;
            if ($request->has('twitter_access_secret')) $settings->twitter_access_secret = $request->twitter_access_secret;
            $settings->post_to_twitter = $request->has('post_to_twitter');
        }

        // 📂 Category Mapping
        if ($isSuperAdmin || $user->hasPermission('can_settings_category')) {
            if ($request->has('category_mapping')) {
                $settings->category_mapping = $request->category_mapping;
            }
        }

        // 🌍 Target Language Settings
        if ($isSuperAdmin || $user->hasPermission('can_settings_target_language')) {
            if ($request->has('target_language')) {
                $settings->target_language = $request->target_language;
            }
        }

        // 🤖 AI Configuration
        if ($isSuperAdmin || $user->hasPermission('can_settings_ai')) {
            if ($request->filled('primary_ai')) {
                $settings->primary_ai = $request->primary_ai;
            }
            if ($request->has('openai_api_key')) $settings->openai_api_key   = $request->openai_api_key;
            if ($request->has('openai_model')) $settings->openai_model     = $request->openai_model;
            if ($request->has('gemini_api_key')) $settings->gemini_api_key   = $request->gemini_api_key;
            if ($request->has('gemini_model')) $settings->gemini_model     = $request->gemini_model;
            if ($request->has('deepseek_api_key')) $settings->deepseek_api_key = $request->deepseek_api_key;
            if ($request->has('deepseek_model')) $settings->deepseek_model   = $request->deepseek_model;
            if ($request->has('groq_api_key')) $settings->groq_api_key     = $request->groq_api_key;
            if ($request->has('groq_model')) $settings->groq_model       = $request->groq_model;
            if ($request->has('qwen_api_key')) $settings->qwen_api_key     = $request->qwen_api_key;
            if ($request->has('qwen_model')) $settings->qwen_model       = $request->qwen_model;
            if ($request->has('huggingface_api_key')) $settings->huggingface_api_key = $request->huggingface_api_key;
            if ($request->has('huggingface_model')) $settings->huggingface_model   = $request->huggingface_model;
            if ($request->has('photoroom_api_key')) $settings->photoroom_api_key = $request->photoroom_api_key;
        }

        // 💰 ROI Config
        if (Auth::user()->role === 'super_admin') {
            $roiConfig = [
                'hourly_rate' => $request->input('roi_hourly_rate', 100),
                'news_minutes' => $request->input('roi_news_minutes', 20),
                'card_minutes' => $request->input('roi_card_minutes', 15),
            ];
            $settings->roi_config = json_encode($roiConfig);
        }

        try {
            $settings->save();
            Log::info("Settings updated successfully for user ID: " . Auth::id());
        } catch (\Exception $e) {
            Log::error("Settings update failed for user ID: " . Auth::id() . " - Error: " . $e->getMessage());
            return back()->with('error', 'সেটিংস সেভ করতে সমস্যা হয়েছে। লগ চেক করুন।');
        }

        return back()->with('success', 'সব সেটিংস (প্রক্সিসহ) সফলভাবে সেভ করা হয়েছে!');
    }

    /**
     * ৩. ফেসবুক কানেকশন টেস্ট (Legacy single-page)
     */
    public function testFacebookConnection(Request $request)
    {
        $pageId = $request->input('fb_page_id');
        $token  = $request->input('fb_access_token');

        if (!$pageId || !$token) {
            return response()->json(['success' => false, 'message' => 'Page ID এবং Token দিতে হবে।']);
        }

        try {
            $response = Http::get("https://graph.facebook.com/v19.0/{$pageId}", [
                'fields'       => 'id,name',
                'access_token' => $token,
            ]);

            $data = $response->json();

            if ($response->successful() && isset($data['id'])) {
                return response()->json([
                    'success' => true,
                    'message' => "✅ কানেকশন সফল!\nPage: " . $data['name'],
                ]);
            } else {
                return response()->json([
                    'success' => false, 
                    'message' => "❌ ফেইল্ড: " . ($data['error']['message'] ?? 'Unknown Error'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'API Error: ' . $e->getMessage()]);
        }
    }

    /**
     * ৪. টেলিগ্রাম কানেকশন টেস্ট
     */
    public function testTelegramConnection(Request $request)
    {
        $botToken  = $request->input('telegram_bot_token');
        $channelId = $request->input('telegram_channel_id');

        if (!$botToken || !$channelId) {
            return response()->json(['success' => false, 'message' => 'Bot Token এবং Channel ID দিতে হবে।']);
        }

        try {
            $meResponse = Http::get("https://api.telegram.org/bot{$botToken}/getMe");
            if (!$meResponse->successful()) {
                return response()->json(['success' => false, 'message' => '❌ Bot Token ভুল!']);
            }

            $chatResponse = Http::get("https://api.telegram.org/bot{$botToken}/getChat", [
                'chat_id' => $channelId,
            ]);

            $chatData = $chatResponse->json();

            if ($chatResponse->successful() && $chatData['ok']) {
                $title = $chatData['result']['title'] ?? 'Unknown Channel';
                return response()->json([
                    'success' => true,
                    'message' => "✅ টেলিগ্রাম কানেক্টেড!\nChannel: $title",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "❌ চ্যানেল পাওয়া যায়নি বা বট এডমিন নেই।\nError: " . ($chatData['description'] ?? 'Unknown'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Network Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Twitter কানেকশন টেস্ট
     */
    public function testTwitterConnection(Request $request)
    {
        $apiKey       = $request->input('twitter_api_key');
        $apiSecret    = $request->input('twitter_api_secret');
        $accessToken  = $request->input('twitter_access_token');
        $accessSecret = $request->input('twitter_access_secret');

        if (!$apiKey || !$apiSecret || !$accessToken || !$accessSecret) {
            return response()->json(['success' => false, 'message' => 'সবগুলো Twitter API Keys দিতে হবে।']);
        }

        try {
            $connection = new \Abraham\TwitterOAuth\TwitterOAuth($apiKey, $apiSecret, $accessToken, $accessSecret);
            $connection->setApiVersion('2');
            $user = $connection->get('users/me');

            if ($connection->getLastHttpCode() == 200 && isset($user->data->username)) {
                return response()->json([
                    'success' => true,
                    'message' => "✅ টুইটার কানেক্টেড!\nAccount: @" . $user->data->username,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "❌ কানেকশন ফেইল্ড! স্ট্যাটাস কোড: " . $connection->getLastHttpCode() . "\n" . ($user->detail ?? 'Invalid credentials'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Twitter Error: ' . $e->getMessage()]);
        }
    }

    /**
     * ৫. ওয়ার্ডপ্রেস কানেকশন টেস্ট
     */
    public function testWordPressConnection(Request $request)
    {
        $url      = $request->input('wp_url');
        $username = $request->input('wp_username');
        $password = $request->input('wp_app_password');

        if (!$url || !$username || !$password) {
            return response()->json(['success' => false, 'message' => 'সব ফিল্ড পূরণ করুন।']);
        }

        try {
            $apiUrl   = rtrim($url, '/') . '/wp-json/wp/v2/users/me';
            $response = Http::withBasicAuth($username, $password)->get($apiUrl);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => true,
                    'message' => "✅ ওয়ার্ডপ্রেস কানেক্টেড!\nUser: " . ($data['name'] ?? $username),
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "❌ কানেকশন ফেইল্ড! স্ট্যাটাস কোড: " . $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'WP Error: ' . $e->getMessage()]);
        }
    }

    /**
     * ⚡ কাস্টম এপিআই / লারাভেল / Next.js কানেকশন টেস্ট
     */
    public function testCustomApiConnection(Request $request)
    {
        $customApiUrl = $request->input('custom_api_url');
        $baseUrl      = $request->input('laravel_site_url');
        $token        = $request->input('laravel_api_token');
        $mappingInput = $request->input('custom_api_mapping');

        $mapping = [];
        if (!empty($mappingInput)) {
            $mapping = is_array($mappingInput) ? $mappingInput : (json_decode($mappingInput, true) ?? []);
        }

        // ১. যদি কাস্টম এপিআই URL থাকে
        if (!empty($customApiUrl)) {
            $apiUrl       = $customApiUrl;
            $authType     = $mapping['auth_type'] ?? ($mapping['header_auth'] ?? 'Bearer');
            $authHeader   = $mapping['auth_header_name'] ?? 'Authorization';
            $imageFormat  = $mapping['image_format'] ?? 'url';
            $categoryType = $mapping['category_type'] ?? 'id';

            $headers = [
                'Accept'     => 'application/json',
                'User-Agent' => 'Subeditor24-Publisher/2.0 (+https://subeditor24.com)',
            ];

            if (!empty($token)) {
                if ($authType === 'Bearer') {
                    $headers['Authorization'] = 'Bearer ' . $token;
                } elseif ($authType === 'header' || $authType === 'custom_header') {
                    $headers[$authHeader] = $token;
                } elseif ($authType === 'basic') {
                    $headers['Authorization'] = 'Basic ' . base64_encode($token);
                }
            }

            $testTitle   = 'টেস্ট নিউজ — Subeditor24 Connection Verification';
            $testContent = '<p>এটি Subeditor24 থেকে একটি স্বয়ংক্রিয় টেস্ট পোস্ট ভেরিফিকেশন।</p>';
            $testSlug    = 'test-subeditor24-' . time();
            $testCat     = ($categoryType === 'name') ? 'টেস্ট' : [1];
            $testImage   = 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=800';

            $payload = [];
            if (isset($mapping['title'])) $payload[$mapping['title']] = $testTitle;
            if (isset($mapping['content'])) $payload[$mapping['content']] = $testContent;
            if (isset($mapping['tags'])) $payload[$mapping['tags']] = '#Test #Verification';
            if (isset($mapping['date'])) $payload[$mapping['date']] = now()->format('Y-m-d H:i:s');
            if (isset($mapping['slug'])) $payload[$mapping['slug']] = $testSlug;
            if (isset($mapping['category'])) $payload[$mapping['category']] = $testCat;
            if (isset($mapping['image'])) $payload[$mapping['image']] = $testImage;

            if ($authType === 'body' && !empty($token)) {
                $tokenKey = $mapping['token'] ?? 'token';
                $payload[$tokenKey] = $token;
            }

            if (isset($mapping['extra']) && is_array($mapping['extra'])) {
                foreach ($mapping['extra'] as $k => $v) {
                    $payload[$k] = $v;
                }
            }

            // যদি কোনো ফিল্ড ম্যাপিং না থাকে, তবে স্ট্যান্ডার্ড পেলোড পাঠাই
            if (empty($payload)) {
                $payload = [
                    'token'   => $token,
                    'title'   => $testTitle,
                    'content' => $testContent,
                    'slug'    => $testSlug,
                    'image'   => $testImage,
                ];
            }

            try {
                $response = Http::timeout(20)
                    ->withOptions(['verify' => false])
                    ->withHeaders($headers)
                    ->post($apiUrl, $payload);

                $statusCode   = $response->status();
                $responseBody = $response->body();
                $respData     = $response->json();

                if ($response->successful()) {
                    $idKey = $mapping['response_id_key'] ?? 'post_id';
                    $postId = $respData[$idKey] ?? ($respData['data'][$idKey] ?? ($respData['id'] ?? 'OK'));
                    
                    return response()->json([
                        'success' => true,
                        'message' => "✅ কাস্টম API কানেকশন সফল! (HTTP {$statusCode})\nসার্ভার রেসপন্স ID: {$postId}",
                        'data'    => $respData
                    ]);
                } elseif ($statusCode === 401 || $statusCode === 403) {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ অথেনটিকেশন ফেইল্ড! (HTTP {$statusCode})\nআপনার Secret Token বা Auth Headers চেক করুন।\nসার্ভার রেসপন্স: " . \Illuminate\Support\Str::limit($responseBody, 150)
                    ]);
                } elseif ($statusCode === 404) {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ এন্ডপয়েন্ট পাওয়া যায়নি! (HTTP 404)\nAPI URL ঠিক আছে কিনা এবং সার্ভারে রাউট রেজিস্টার্ড কিনা চেক করুন।"
                    ]);
                } elseif ($statusCode === 422) {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ ডাটা ভ্যালিডেশন এরর! (HTTP 422)\nম্যাপিং ফিল্ডের নামগুলো ক্লায়েন্ট সাইটের রিকোয়ার্ড ফিল্ডের সাথে মিলছে না।\nসার্ভার এরর: " . \Illuminate\Support\Str::limit($responseBody, 150)
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ রেসপন্স এরর! (HTTP {$statusCode})\nসার্ভার রেসপন্স: " . \Illuminate\Support\Str::limit($responseBody, 200)
                    ]);
                }

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ নেটওয়ার্ক বা কানেকশন এরর: ' . $e->getMessage()
                ]);
            }
        }

        // ২. যদি ডিফল্ট লারাভেল / Webhook URL থাকে
        if (!empty($baseUrl)) {
            $apiUrl = rtrim($baseUrl, '/') . '/api/external-news-post';
            $payload = [
                'token'         => $token,
                'title'         => 'টেস্ট নিউজ — Subeditor24 Verification',
                'content'       => '<p>Subeditor24 কানেকশন টেস্ট পোস্ট।</p>',
                'image_url'     => 'https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=800',
                'category_ids'  => [1],
                'category_name' => 'General',
                'hashtags'      => '#Test',
                'slug'          => 'test-subeditor24-' . time(),
                'published_at'  => now()->format('Y-m-d H:i:s'),
            ];

            try {
                $response = Http::timeout(20)
                    ->withOptions(['verify' => false])
                    ->withHeaders([
                        'Accept'     => 'application/json',
                        'User-Agent' => 'Subeditor24-Publisher/2.0'
                    ])
                    ->post($apiUrl, $payload);

                $statusCode = $response->status();
                $respData   = $response->json();

                if ($response->successful()) {
                    $postId = $respData['post_id'] ?? ($respData['id'] ?? 'OK');
                    return response()->json([
                        'success' => true,
                        'message' => "✅ লারাভেল API কানেকশন সফল! (HTTP {$statusCode})\nপোস্ট আইডি: {$postId}",
                        'data'    => $respData
                    ]);
                } elseif ($statusCode === 401) {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ অথেনটিকেশন ফেইল্ড (HTTP 401)! API Token অমিল।"
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ কানেকশন ফেইল্ড! HTTP {$statusCode}: " . \Illuminate\Support\Str::limit($response->body(), 150)
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ কানেকশন এরর: ' . $e->getMessage()
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'দয়া করে ওয়েবসাইট Base URL অথবা Custom API URL প্রদান করুন।'
        ]);
    }

    /**
     * PhotoRoom API কানেকশন টেস্ট
     */
    public function testPhotoRoomConnection(Request $request, PhotoRoomService $photoRoomService)
    {
        $key = $request->input('photoroom_api_key');
        $result = $photoRoomService->testConnection($key);
        return response()->json($result);
    }

    /**
     * Decodo Universal API ও প্রক্সি কানেকশন টেস্ট
     */
    public function testDecodoProxyConnection(Request $request)
    {
        $token = $request->input('smartproxy_api_token');
        $proxyHost = $request->input('proxy_host');
        $proxyPort = $request->input('proxy_port');
        $proxyUser = $request->input('proxy_username');
        $proxyPass = $request->input('proxy_password');

        // Fallback to saved settings or .env if empty
        if (empty($token)) {
            $user = Auth::user();
            $settings = $user->settings;
            $token = $settings->smartproxy_api_token ?? env('SMARTPROXY_SCRAPING_API_TOKEN');
        }

        if (empty($token) && empty($proxyHost)) {
            return response()->json([
                'success' => false,
                'message' => '❌ দয়া করে Decodo Universal API Token অথবা Proxy Host প্রদান করুন।'
            ]);
        }

        $results = [];

        // 1. Test Decodo / Smartproxy Universal API
        if (!empty($token)) {
            $tokenValue = preg_replace('/^Basic\s+/i', '', trim($token));
            $decoded = base64_decode($tokenValue);
            $isSmartProxyOrg = ($decoded && str_starts_with(explode(':', $decoded, 2)[0] ?? '', 'smart-'));

            $startTime = microtime(true);

            try {
                if ($isSmartProxyOrg) {
                    $endpoint = 'https://scraper.smartproxy.org/v1/scrape';
                    $payload = [
                        'js_render' => ['enabled' => true],
                        'request'   => ['device' => 'desktop'],
                        'output'    => ['formats' => ['html']],
                        'proxy'     => ['location' => 'BD'],
                        'url'       => 'https://httpbin.org/ip'
                    ];
                } else {
                    $endpoint = 'https://scraper-api.decodo.com/v2/scrape';
                    $payload = [
                        'url'        => 'https://httpbin.org/ip',
                        'target'     => 'universal',
                        'headless'   => 'html',
                        'geo'        => 'Bangladesh'
                    ];
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . $tokenValue,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ])->timeout(35)->post($endpoint, $payload);

                $elapsed = round(microtime(true) - $startTime, 2);

                if ($response->successful()) {
                    $body = $response->body();
                    // Parse IP from response
                    if (preg_match('/origin[\\\"\\\'\s:]+([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $body, $m)) {
                        $ip = $m[1];
                    } elseif (preg_match('/([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})/', $body, $m)) {
                        $ip = $m[1];
                    }

                    return response()->json([
                        'success' => true,
                        'message' => "✅ Decodo Universal API ১০০% সফল ও সক্রিয়!\n🌍 রেসিডেন্সিয়াল এক্সিট আইপি: {$ip} (Bangladesh Geo)\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড\n🔒 Cloudflare ও Anti-Bot Bypass: সম্পূর্ণ কার্যকর।"
                    ]);
                }

                if ($response->status() === 401 || $response->status() === 403) {
                    return response()->json([
                        'success' => false,
                        'message' => "❌ Decodo অথেনটিকেশন ফেইল্ড (HTTP {$response->status()})! আপনার API Token টি সঠিক নয় বা মেয়াদোত্তীর্ণ।"
                    ]);
                }

                if ($response->status() === 429) {
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ Decodo Rate Limit (HTTP 429): কনকারেন্ট রিকোয়েস্ট সীমা পার হয়েছে।"
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => "❌ Decodo API এরর (HTTP {$response->status()}): " . Str::limit($response->body(), 120)
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Decodo কানেকশন এরর: ' . $e->getMessage()
                ]);
            }
        }

        // 2. Test Standard HTTP/SOCKS Proxy
        if (!empty($proxyHost) && !empty($proxyPort)) {
            try {
                $proxyUrl = $proxyHost . ':' . $proxyPort;
                if (!empty($proxyUser) && !empty($proxyPass)) {
                    $proxyUrl = $proxyUser . ':' . $proxyPass . '@' . $proxyUrl;
                }

                $startTime = microtime(true);
                $response = Http::withOptions([
                    'proxy' => 'http://' . $proxyUrl,
                    'verify' => false,
                ])->timeout(15)->get('https://httpbin.org/ip');

                $elapsed = round(microtime(true) - $startTime, 2);

                if ($response->successful()) {
                    $ip = $response->json('origin') ?? 'OK';
                    return response()->json([
                        'success' => true,
                        'message' => "✅ HTTP প্রক্সি কানেকশন সফল!\n🌍 প্রক্সি আইপি: {$ip}\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড"
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => "❌ প্রক্সি কানেকশন ব্যর্থ (HTTP {$response->status()})"
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ প্রক্সি কানেকশন এরর: ' . $e->getMessage()
                ]);
            }
        }
    }

    /**
     * AI Provider লাইভ কানেকশন টেস্ট (Gemini, DeepSeek, OpenAI, Groq, Qwen, HuggingFace)
     */
    public function testAiProviderConnection(Request $request)
    {
        $provider = strtolower($request->input('provider', ''));
        $apiKey   = trim((string) $request->input('api_key', ''));
        $model    = trim((string) $request->input('model', ''));

        $user = Auth::user();
        $settings = $user ? $user->settings : null;

        $providerNames = [
            'gemini'      => 'Gemini (Google AI)',
            'deepseek'    => 'DeepSeek AI',
            'openai'      => 'OpenAI (ChatGPT)',
            'groq'        => 'Groq (Ultra-Fast LPU)',
            'qwen'        => 'Qwen (DashScope API)',
            'huggingface' => 'Hugging Face Inference'
        ];

        $displayName = $providerNames[$provider] ?? ucfirst($provider);

        // Fallback for API keys if user didn't enter one in test input
        if (empty($apiKey)) {
            switch ($provider) {
                case 'gemini':
                    $apiKey = ($settings ? $settings->gemini_api_key : null) ?? (config('services.gemini.key') ?? env('GEMINI_API_KEY'));
                    break;
                case 'deepseek':
                    $apiKey = ($settings ? $settings->deepseek_api_key : null) ?? (config('services.deepseek.key') ?? env('DEEPSEEK_API_KEY'));
                    break;
                case 'openai':
                    $apiKey = ($settings ? $settings->openai_api_key : null) ?? (config('services.openai.key') ?? env('OPENAI_API_KEY'));
                    break;
                case 'groq':
                    $apiKey = ($settings ? $settings->groq_api_key : null) ?? env('GROQ_API_KEY');
                    break;
                case 'qwen':
                    $apiKey = ($settings ? $settings->qwen_api_key : null) ?? env('QWEN_API_KEY');
                    break;
                case 'huggingface':
                    $apiKey = ($settings ? $settings->huggingface_api_key : null) ?? env('HUGGINGFACE_API_KEY');
                    break;
            }
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => "❌ অনুগ্রহ করে {$displayName} এর API Key প্রদান করুন বা সেটিংস সেভ করুন।"
            ]);
        }

        $startTime = microtime(true);

        try {
            switch ($provider) {
                case 'gemini':
                    $selectedModel = !empty($model) ? $model : ($settings->gemini_model ?? 'gemini-1.5-flash');
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/{$selectedModel}:generateContent?key={$apiKey}";
                    
                    $response = Http::withHeaders(['Content-Type' => 'application/json'])
                        ->timeout(25)
                        ->post($url, [
                            "contents" => [[
                                "parts" => [["text" => "Ping test. Reply with exactly: Connected successfully."]]
                            ]]
                        ]);

                    $elapsed = round(microtime(true) - $startTime, 2);

                    if ($response->successful()) {
                        $reply = $response->json('candidates.0.content.parts.0.text') ?? 'OK';
                        return response()->json([
                            'success' => true,
                            'message' => "✅ {$displayName} কানেকশন ১০০% সফল ও সক্রিয়!\n🤖 মডেল: {$selectedModel}\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড\n💬 এআই টেস্ট রেসপন্স: " . trim($reply)
                        ]);
                    }
                    break;

                case 'deepseek':
                    $selectedModel = !empty($model) ? $model : ($settings->deepseek_model ?? 'deepseek-chat');
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type'  => 'application/json',
                    ])->timeout(25)->post("https://api.deepseek.com/chat/completions", [
                        "model" => $selectedModel,
                        "messages" => [
                            ["role" => "user", "content" => "Ping test. Reply with: Connected successfully."]
                        ],
                        "max_tokens" => 30
                    ]);

                    $elapsed = round(microtime(true) - $startTime, 2);

                    if ($response->successful()) {
                        $reply = $response->json('choices.0.message.content') ?? 'OK';
                        return response()->json([
                            'success' => true,
                            'message' => "✅ {$displayName} কানেকশন ১০০% সফল ও সক্রিয়!\n🤖 মডেল: {$selectedModel}\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড\n💬 এআই টেস্ট রেসপন্স: " . trim($reply)
                        ]);
                    }
                    break;

                case 'openai':
                    $selectedModel = !empty($model) ? $model : ($settings->openai_model ?? 'gpt-4o-mini');
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type'  => 'application/json',
                    ])->timeout(25)->post("https://api.openai.com/v1/chat/completions", [
                        "model" => $selectedModel,
                        "messages" => [
                            ["role" => "user", "content" => "Ping test. Reply with: Connected successfully."]
                        ],
                        "max_tokens" => 30
                    ]);

                    $elapsed = round(microtime(true) - $startTime, 2);

                    if ($response->successful()) {
                        $reply = $response->json('choices.0.message.content') ?? 'OK';
                        return response()->json([
                            'success' => true,
                            'message' => "✅ {$displayName} কানেকশন ১০০% সফল ও সক্রিয়!\n🤖 মডেল: {$selectedModel}\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড\n💬 এআই টেস্ট রেসপন্স: " . trim($reply)
                        ]);
                    }
                    break;

                case 'groq':
                    $selectedModel = !empty($model) ? $model : ($settings->groq_model ?? 'llama-3.3-70b-versatile');
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type'  => 'application/json',
                    ])->timeout(25)->post("https://api.groq.com/openai/v1/chat/completions", [
                        "model" => $selectedModel,
                        "messages" => [
                            ["role" => "user", "content" => "Ping test. Reply with: Connected successfully."]
                        ],
                        "max_tokens" => 30
                    ]);

                    $elapsed = round(microtime(true) - $startTime, 2);

                    if ($response->successful()) {
                        $reply = $response->json('choices.0.message.content') ?? 'OK';
                        return response()->json([
                            'success' => true,
                            'message' => "✅ {$displayName} কানেকশন ১০০% সফল ও সক্রিয়!\n🤖 মডেল: {$selectedModel}\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড\n💬 এআই টেস্ট রেসপন্স: " . trim($reply)
                        ]);
                    }
                    break;

                case 'qwen':
                    $selectedModel = !empty($model) ? $model : ($settings->qwen_model ?? 'qwen-turbo');
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type'  => 'application/json',
                    ])->timeout(25)->post("https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions", [
                        "model" => $selectedModel,
                        "messages" => [
                            ["role" => "user", "content" => "Ping test. Reply with: Connected successfully."]
                        ],
                        "max_tokens" => 30
                    ]);

                    $elapsed = round(microtime(true) - $startTime, 2);

                    if ($response->successful()) {
                        $reply = $response->json('choices.0.message.content') ?? 'OK';
                        return response()->json([
                            'success' => true,
                            'message' => "✅ {$displayName} কানেকশন ১০০% সফল ও সক্রিয়!\n🤖 মডেল: {$selectedModel}\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড\n💬 এআই টেস্ট রেসপন্স: " . trim($reply)
                        ]);
                    }
                    break;

                case 'huggingface':
                    $selectedModel = !empty($model) ? $model : ($settings->huggingface_model ?? 'Qwen/Qwen2.5-72B-Instruct');
                    
                    $response = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type'  => 'application/json',
                    ])->timeout(25)->post("https://api-inference.huggingface.co/models/{$selectedModel}", [
                        "inputs" => "Ping test. Reply with: Connected successfully.",
                        "parameters" => ["max_new_tokens" => 30]
                    ]);

                    $elapsed = round(microtime(true) - $startTime, 2);

                    if ($response->successful()) {
                        return response()->json([
                            'success' => true,
                            'message' => "✅ {$displayName} কানেকশন ১০০% সফল ও সক্রিয়!\n🤖 মডেল: {$selectedModel}\n⚡ রেসপন্স টাইম: {$elapsed} সেকেন্ড"
                        ]);
                    }
                    break;

                default:
                    return response()->json([
                        'success' => false,
                        'message' => '❌ অজানা AI প্রোভাইডার সিলেক্ট করা হয়েছে।'
                    ]);
            }

            $status = $response->status();
            $body = $response->body();

            if ($status === 401 || $status === 403) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ {$displayName} অথেনটিকেশন ফেইল্ড (HTTP {$status})! API Key টি সঠিক নয় বা ইনভ্যালিড।"
                ]);
            }

            if ($status === 429) {
                return response()->json([
                    'success' => false,
                    'message' => "⚠️ {$displayName} কোটা লিমিট অতিক্রম করেছে (HTTP 429)! আপনার অ্যাকাউন্টের ব্যালেন্স/ক্রেডিট শেষ অথবা রেট লিমিট হয়েছে।"
                ]);
            }

            if ($status === 404) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ {$displayName} মডেল বা এন্ডপয়েন্ট পাওয়া যায়নি (HTTP 404)! মডেল নামটি সঠিক কি না যাচাই করুন।"
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => "❌ {$displayName} এরর (HTTP {$status}): " . Str::limit($body, 150)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "❌ {$displayName} কানেকশন ব্যর্থ: " . $e->getMessage()
            ]);
        }
    }

    /**
     * ৬. ক্যাটাগরি ফেচ করা
     */
    public function fetchCategories(WordPressService $wpService)
    {
        $user = Auth::user();
        
        $adminUser = in_array($user->role, ['staff', 'reporter']) ? \App\Models\User::find($user->parent_id) : $user;
        $settings  = $adminUser->settings;

        if (!$settings) {
            return response()->json(['error' => 'Settings not found'], 400);
        }

        $cacheKey = 'user_categories_' . $adminUser->id;

        if (request()->has('refresh')) {
            Cache::forget($cacheKey);
        }

        $categories = Cache::remember($cacheKey, now()->addHours(24), function () use ($settings, $wpService) {
            
            if ($settings->post_to_laravel && $settings->laravel_site_url && $settings->laravel_api_token) {
                try {
                    if (!empty($settings->custom_category_url)) {
                        $apiUrl  = $settings->custom_category_url;
                        $headers = [];
                        if (!empty($settings->laravel_api_token)) {
                            $headers['Authorization'] = 'Bearer ' . $settings->laravel_api_token;
                        }

                        $response = Http::withHeaders($headers)->timeout(10)->get($apiUrl);
                        
                        if ($response->successful()) {
                            $resData = $response->json();
                            if (isset($resData['data']) && is_array($resData['data'])) {
                                return collect($resData['data'])->map(function ($item) {
                                    return [
                                        'id'   => $item['CategoryID'] ?? $item['id'] ?? null,
                                        'name' => $item['CategoryName'] ?? $item['name'] ?? 'Unknown',
                                    ];
                                })->toArray();
                            }
                            return $resData;
                        }
                    } else {
                        $baseUrl  = rtrim($settings->laravel_site_url, '/');
                        $apiUrl   = $baseUrl . '/api/get-categories';
                        $response = Http::timeout(10)->get($apiUrl, ['token' => $settings->laravel_api_token]);
                        
                        if ($response->successful()) {
                            return $response->json();
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Laravel Category Fetch Error: " . $e->getMessage());
                }
            }

            if ($settings->wp_url && $settings->wp_username && $settings->wp_app_password) {
                try {
                    return $wpService->getCategories(
                        $settings->wp_url,
                        $settings->wp_username,
                        $settings->wp_app_password
                    );
                } catch (\Exception $e) {
                    Log::error("WP Category Fetch Error: " . $e->getMessage());
                }
            }

            return [];
        });

        if (empty($categories)) {
            return response()->json(['error' => 'No Categories Found or Connection Failed'], 400);
        }

        return response()->json($categories);
    }

    /**
     * ৭. লোগো আপলোড
     */
    public function uploadLogo(Request $request)
    {
        $request->validate(['logo' => 'required|image|max:2048']);
        if ($request->hasFile('logo')) {
            $path     = $request->file('logo')->store('logos', 'public');
            $settings = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
            $settings->logo_url = asset('storage/' . $path);
            $settings->save();
            return response()->json(['success' => true, 'url' => asset('storage/' . $path)]);
        }
        return response()->json(['success' => false], 400);
    }

    /**
     * ৮. ফ্রেম আপলোড
     */
    public function uploadFrame(Request $request)
    {
        $request->validate(['frame' => 'required|image|mimes:png|max:2048']);
        if ($request->hasFile('frame')) {
            $path = $request->file('frame')->store('frames', 'public');
            return response()->json(['success' => true, 'url' => asset('storage/' . $path)]);
        }
        return response()->json(['success' => false], 400);
    }

    /**
     * ৯. ক্রেডিট হিস্ট্রি
     */
    public function credits()
    {
        $user      = Auth::user();
        $histories = method_exists($user, 'creditHistories') ? $user->creditHistories()->latest()->paginate(15) : collect();
        return view('settings.credits', compact('histories', 'user'));
    }

    /**
     * ১০. ডিজাইন প্রেফারেন্স সেভ
     */
    public function saveDesign(Request $request)
    {
        try {
            $settings = UserSetting::firstOrCreate(['user_id' => Auth::id()]);
            $settings->design_preferences = $request->preferences;
            $settings->save();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * ১১. প্রোফাইল আপডেট
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();
        return back()->with('success', 'প্রোফাইল আপডেট হয়েছে!');
    }

    /**
     * ১২. ১-ক্লিক ফুল সিস্টেম হেলথ ডায়াগনস্টিকস
     */
    public function runSystemDiagnostics(Request $request)
    {
        $user = Auth::user();
        $adminUser = in_array($user->role, ['staff', 'reporter']) ? \App\Models\User::find($user->parent_id) : $user;
        $settings = $adminUser->settings;

        $results = [];

        // ১. ডাটাবেস ও কিউ চেক
        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $pendingJobs = DB::table('jobs')->count();
            $results[] = [
                'category' => 'Database & Queue',
                'name'     => 'ডাটাবেস ও কিউ সিস্টেম',
                'status'   => 'ok',
                'badge'    => 'Healthy',
                'message'  => "ডাটাবেস ({$dbName}) সংযুক্ত। কিউতে পেন্ডিং জব: {$pendingJobs}টি।"
            ];
        } catch (\Throwable $e) {
            $results[] = [
                'category' => 'Database & Queue',
                'name'     => 'ডাটাবেস সিস্টেম',
                'status'   => 'error',
                'badge'    => 'Error',
                'message'  => "ডাটাবেস কানেকশনে সমস্যা: " . $e->getMessage()
            ];
        }

        // ২. এআই প্রোভাইডার চেক
        $primaryAi = $settings->primary_ai ?? 'gemini';
        $hasKey = false;
        $aiKeyName = '';

        if ($primaryAi === 'gemini' && !empty($settings->gemini_api_key)) { $hasKey = true; $aiKeyName = 'Google Gemini'; }
        elseif ($primaryAi === 'deepseek' && !empty($settings->deepseek_api_key)) { $hasKey = true; $aiKeyName = 'DeepSeek AI'; }
        elseif ($primaryAi === 'openai' && !empty($settings->openai_api_key)) { $hasKey = true; $aiKeyName = 'OpenAI (ChatGPT)'; }
        elseif ($primaryAi === 'groq' && !empty($settings->groq_api_key)) { $hasKey = true; $aiKeyName = 'Groq Fast AI'; }
        elseif (!empty($settings->gemini_api_key) || !empty($settings->deepseek_api_key) || !empty($settings->openai_api_key) || !empty($settings->groq_api_key)) {
            $hasKey = true;
            $aiKeyName = 'Multi-Provider Fallback';
        }

        if ($hasKey) {
            $results[] = [
                'category' => 'AI Engine',
                'name'     => "এআই ইঞ্জিন ({$aiKeyName})",
                'status'   => 'ok',
                'badge'    => 'Configured',
                'message'  => "প্রাইমারি এআই হিসেবে '{$primaryAi}' সক্রিয় ও API কী কনফিগার করা আছে।"
            ];
        } else {
            $results[] = [
                'category' => 'AI Engine',
                'name'     => 'এআই ইঞ্জিন',
                'status'   => 'warning',
                'badge'    => 'No API Key',
                'message'  => "কোনো এআই API কী সেট করা নেই! AI Settings থেকে Gemini বা DeepSeek কী দিন।"
            ];
        }

        // ৩. ওয়ার্ডপ্রেস / CMS কানেকশন চেক
        if ($settings && !empty($settings->wp_url) && !empty($settings->wp_username)) {
            try {
                $wpUrl = rtrim($settings->wp_url, '/');
                $response = Http::timeout(6)->withOptions(['verify' => false])->get("{$wpUrl}/wp-json");
                if ($response->successful()) {
                    $siteName = $response->json('name') ?? 'WordPress Site';
                    $results[] = [
                        'category' => 'WordPress CMS',
                        'name'     => 'ওয়ার্ডপ্রেস REST API',
                        'status'   => 'ok',
                        'badge'    => 'Connected',
                        'message'  => "সাইট '{$siteName}' সংযুক্ত। REST API রেসপন্স স্বাভাবিক (HTTP 200)।"
                    ];
                } else {
                    $results[] = [
                        'category' => 'WordPress CMS',
                        'name'     => 'ওয়ার্ডপ্রেস REST API',
                        'status'   => 'warning',
                        'badge'    => 'HTTP ' . $response->status(),
                        'message'  => "ওয়ার্ডপ্রেস সাইট থেকে অস্বাভাবিক রেসপন্স পাওয়া গেছে (HTTP {$response->status()})।"
                    ];
                }
            } catch (\Throwable $e) {
                $results[] = [
                    'category' => 'WordPress CMS',
                    'name'     => 'ওয়ার্ডপ্রেস REST API',
                    'status'   => 'error',
                    'badge'    => 'Unreachable',
                    'message'  => "ওয়ার্ডপ্রেস সাইটে কানেক্ট করা যায়নি: " . \Illuminate\Support\Str::limit($e->getMessage(), 100)
                ];
            }
        } else {
            $results[] = [
                'category' => 'WordPress CMS',
                'name'     => 'ওয়ার্ডপ্রেস CMS',
                'status'   => 'info',
                'badge'    => 'Not Configured',
                'message'  => "ওয়ার্ডপ্রেস সাইট URL ও ইউজারনেম এখনো কনফিগার করা হয়নি।"
            ];
        }

        // ৪. ফেসবুক পেজ ইন্টিগ্রেশন চেক
        try {
            $fbPages = \App\Models\FacebookPage::where('user_id', $adminUser->id)->get();
            if ($fbPages->count() > 0) {
                $activeCount = $fbPages->where('is_active', true)->count();
                $results[] = [
                    'category' => 'Social Media',
                    'name'     => 'ফেসবুক পেজ কানেকশন',
                    'status'   => 'ok',
                    'badge'    => "{$activeCount} Active",
                    'message'  => "মোট {$fbPages->count()}টি পেজ কনফিগার করা রয়েছে ({$activeCount}টি অটো-পোস্ট সক্রিয়)।"
                ];
            } else {
                $results[] = [
                    'category' => 'Social Media',
                    'name'     => 'ফেসবুক পেজ',
                    'status'   => 'info',
                    'badge'    => 'Optional',
                    'message'  => "কোনো ফেসবুক পেজ যুক্ত করা নেই।"
                ];
            }
        } catch (\Throwable $e) {
            // Ignore if table not present
        }

        // ৫. ব্যাকগ্রাউন্ড ক্রন জব ও অটো-পোস্ট স্ট্যাটাস
        $lastAuto = ($settings && $settings->last_auto_post_at) ? \Carbon\Carbon::parse($settings->last_auto_post_at)->diffForHumans() : 'এখনো রান হয়নি';
        $isAutoOn = $settings && !empty($settings->is_auto_posting);
        $interval = $settings->auto_post_interval ?? 10;

        $results[] = [
            'category' => 'Automation & Scheduler',
            'name'     => 'ব্যাকগ্রাউন্ড অটোমেশন',
            'status'   => 'ok',
            'badge'    => $isAutoOn ? 'Auto-Post ON' : 'Manual Mode',
            'message'  => "অটো-পোস্ট: " . ($isAutoOn ? "সক্রিয় (ইন্টারভাল: {$interval} মিনিট)" : "ম্যানুয়াল মোড") . "। সর্বশেষ একশন: {$lastAuto}।"
        ];

        return response()->json([
            'success'   => true,
            'timestamp' => now()->format('d M, Y h:i A'),
            'checks'    => $results
        ]);
    }
}
