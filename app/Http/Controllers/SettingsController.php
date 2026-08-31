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
}
