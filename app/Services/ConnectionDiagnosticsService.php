<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ConnectionDiagnosticsService
{
    /**
     * 🛡️ সিকিউর ও স্যানিটাইজড কানেকশন এরর ডায়াগনস্টিকস
     *
     * @param int $statusCode
     * @param string $rawErrorBody
     * @param string $targetUrl
     * @param array $context
     * @param int|null $userId
     * @return array
     */
    public function diagnoseError(int $statusCode, string $rawErrorBody, string $targetUrl = '', array $context = [], $userId = null): array
    {
        // ১. সংবেদনশীল তথ্য ও ইন্টারনাল সার্ভার পাথ স্যানিটাইজ করা
        $sanitizedError = $this->sanitizeErrorText($rawErrorBody);

        // ২. রুল-বেসড ইনস্ট্যান্ট অ্যানালাইসিস (সুপার-ফাস্ট এবং ০-টোকেন কস্ট)
        $ruleBasedResult = $this->analyzeWithRules($statusCode, $sanitizedError, $targetUrl, $context);
        if ($ruleBasedResult !== null) {
            return $ruleBasedResult;
        }

        // ৩. যদি আনকমন বা জটিল স্ট্যাক-ট্রেস হয়, তবে নিরাপদ AI ডায়াগনস্টিকস
        try {
            $aiResult = $this->analyzeWithAI($statusCode, $sanitizedError, $targetUrl, $context, $userId);
            if ($aiResult !== null) {
                return $aiResult;
            }
        } catch (\Exception $e) {
            Log::warning("AI connection diagnostics failed: " . $e->getMessage());
        }

        // ৪. ফলব্যাক জেনেরিক ডায়াগনস্টিকস
        return $this->getGenericFallbackDiagnostics($statusCode, $sanitizedError);
    }

    /**
     * 🧹 ইন্টারনাল সার্ভার পাথ, টোকেন ও পাসওয়ার্ড মুছে ফেলা
     */
    private function sanitizeErrorText(string $text): string
    {
        // সাব-এডিটর বা ইন্টারনাল সার্ভার পাথ রিমুভ করা
        $text = preg_replace('/(\/[a-zA-Z0-9_\-\.]+)+(\/app\/|\/resources\/|\/vendor\/|\/var\/|\/home\/)/i', '.../', $text);
        
        // ফুল সিক্রেট টোকেন মাস্ক করা (e.g. sec_12345678 -> sec_******)
        $text = preg_replace('/(sec_[a-zA-Z0-9_\-]{3})[a-zA-Z0-9_\-]+/i', '$1******', $text);
        $text = preg_replace('/(Bearer\s+[a-zA-Z0-9_\-]{4})[a-zA-Z0-9_\-]+/i', '$1******', $text);
        
        // ডাটাবেস পাসওয়ার্ড বা ক্রেডেনশিয়াল মাস্ক করা
        $text = preg_replace('/(password|db_password|api_key|secret)\s*[:=]\s*["\']?[^"\'\s,]+/i', '$1: ******', $text);

        return Str::limit($text, 1500);
    }

    /**
     * ⚡ কমন এররগুলোর জন্য ইনস্ট্যান্ট ডায়াগনস্টিকস রুলস
     */
    private function analyzeWithRules(int $statusCode, string $error, string $targetUrl, array $context): ?array
    {
        $lowerError = strtolower($error);

        // ১. PHP Namespace Backslash Issue (non-compound name)
        if (str_contains($lowerError, 'non-compound name') || str_contains($lowerError, 'appmodelsnewspost') || str_contains($lowerError, 'appmodelscategory')) {
            return [
                'type' => 'syntax_namespace',
                'badge' => 'PHP Namespace Error',
                'problem' => 'PHP নেমস্পেসে ব্যাকস্ল্যাশ (\) বাদ পড়েছে বা এক শব্দ হয়ে গেছে।',
                'reason' => 'কোড কপি-পেস্ট করার সময় `use App\Models\NewsPost;` এর জায়গায় ব্যাকস্ল্যাশ ছাড়া `use AppModelsNewsPost;` লেখা হয়েছে।',
                'fix_instructions' => 'আপনার ওয়েবসাইটের `routes/api.php` ফাইলের একদম উপরের `use` লাইনগুলোতে সঠিক ব্যাকস্ল্যাশ (\) বসিয়ে দিন।',
                'fix_code' => "use Illuminate\\Http\\Request;\nuse Illuminate\\Support\\Facades\\Route;\nuse App\\Models\\Post;\nuse App\\Models\\Category;",
            ];
        }

        // ২. Missing Model / Model Not Found
        if (str_contains($lowerError, 'class "app\\models\\newspost" not found') || str_contains($lowerError, 'class \'newspost\' not found') || str_contains($lowerError, 'class "newspost" not found')) {
            return [
                'type' => 'model_not_found',
                'badge' => 'Model Not Found',
                'problem' => 'আপনার ওয়েবসাইটের ডাটাবেস মডেল `NewsPost` খুঁজে পাওয়া যায়নি।',
                'reason' => 'আপনার Laravel প্রজেক্টে নিউজের মূল মডেলটির নাম সম্ভবত `Post` (বা অন্য কোনো নাম), কিন্তু কোডে `NewsPost` কল করা হয়েছে।',
                'fix_instructions' => '`routes/api.php` ফাইলে `NewsPost::create` এর জায়গায় আপনার আসল মডেল `Post::create` ব্যবহার করুন।',
                'fix_code' => "use App\\Models\\Post; // আপনার সাইটের পোস্ট মডেল\n\n// কোডে পরিবর্তন করুন:\n\$post = Post::create([...]);",
            ];
        }

        // ৩. Database Unknown Column
        if (str_contains($lowerError, 'unknown column') || str_contains($lowerError, 'column not found')) {
            preg_match('/unknown column [\'"`]([^\'"`]+)[\'"`]/i', $error, $colMatch);
            $colName = $colMatch[1] ?? 'name';

            return [
                'type' => 'db_column_missing',
                'badge' => 'Database Column Mismatch',
                'problem' => "ডাটাবেস টেবিলে `{$colName}` নামের কলামটি পাওয়া যায়নি।",
                'reason' => "আপনার ডাটাবেস টেবিলে কলামের নাম হয়তো আলাদা (যেমন `name` এর বদলে `title` বা `category_name`)।",
                'fix_instructions' => "আপনার রিসিভার কোডে কলামের নাম মিলিয়ে দিন অথবা Settings পেজের 'Field Mapping' অপশন ব্যবহার করুন।",
                'fix_code' => "// ক্যাটাগরি কোয়েরিতে কলাম এলিয়াস ব্যবহার করুন:\nCategory::select('id', 'title as name')->get();",
            ];
        }

        // ৪. HTTP 401 Unauthorized (Token Mismatch / Apache CGIPassAuth)
        if ($statusCode === 401 || str_contains($lowerError, 'unauthorized') || str_contains($lowerError, 'invalid api secret token')) {
            return [
                'type' => 'auth_token',
                'badge' => 'Authentication Error (401)',
                'problem' => 'API Secret Token মেলেনি অথবা সার্ভার অথেনটিকেশন হেডার আটকে দিচ্ছে।',
                'reason' => 'Subeditor24 এর সেটিংস পেজের সিক্রেট টোকেন এবং আপনার ওয়েবসাইটের `.env` ফাইলের `SUBEDITOR_API_SECRET` এক নয়। অথবা cPanel/Apache সার্ভারে `Authorization` হেডার পাস হচ্ছে না।',
                'fix_instructions' => '১. `.env` ফাইলে টোকেনটি নিশ্চিত করে `php artisan config:clear` রান করুন।<br>২. cPanel হলে প্রজেক্টের `.htaccess` ফাইলে `CGIPassAuth On` যোগ করুন।',
                'fix_code' => "# Apache .htaccess ফাইলে যোগ করুন:\n<IfModule mod_rewrite.c>\n    RewriteEngine On\n    CGIPassAuth On\n    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]\n</IfModule>",
            ];
        }

        // ৫. HTTP 404 Not Found (Missing Route / Wrong Base URL)
        if ($statusCode === 404) {
            return [
                'type' => 'route_not_found',
                'badge' => 'Endpoint Not Found (404)',
                'problem' => 'সার্ভারে API রুটটি খুঁজে পাওয়া যায়নি (HTTP 404)।',
                'reason' => '১. ওয়েবসাইটের Base URL এর শেষে ভুল স্ল্যাশ থাকতে পারে।<br>২. আপনার `routes/api.php` ফাইলে `Route::post(\'/external-news-post\', ...)` রুটটি ডিফাইন করা নেই।',
                'fix_instructions' => 'আপনার Laravel প্রজেক্টের `routes/api.php` ফাইলে নিউজ রিসিভার রুটটি যোগ করুন।',
                'fix_code' => "// routes/api.php ফাইলে নিশ্চিত করুন:\nRoute::post('/external-news-post', function (Request \$request) {\n    // Receiver Code Here\n});",
            ];
        }

        // ৬. HTTP 419 CSRF Token Error
        if ($statusCode === 419 || str_contains($lowerError, 'csrf') || str_contains($lowerError, 'page expired')) {
            return [
                'type' => 'csrf_error',
                'badge' => 'CSRF Token Mismatch (419)',
                'problem' => 'CSRF টোকেন ভ্যালিডেশন এরর (HTTP 419 Page Expired)।',
                'reason' => 'API রুটটি ভুলবশত `routes/web.php` ফাইলে লেখা হয়েছে, যার ফলে Laravel ব্রাউজার CSRF টোকেন খুঁজছে।',
                'fix_instructions' => 'রুটটি `routes/web.php` থেকে সরিয়ে অবশ্যই `routes/api.php` ফাইলে বসান।',
                'fix_code' => "// সঠিক ফাইল: routes/api.php (এখানে CSRF টোকেনের প্রয়োজন নেই)",
            ];
        }

        // ৭. HTTP 422 Validation Error
        if ($statusCode === 422 || str_contains($lowerError, 'validation') || str_contains($lowerError, 'unprocessable')) {
            return [
                'type' => 'validation_error',
                'badge' => 'Validation Failed (422)',
                'problem' => 'ডাটা ভ্যালিডেশন ফেইল করেছে (HTTP 422)।',
                'reason' => 'আপনার রিসিভার কোডে কোনো ফিল্ডকে `required` করা আছে যা পাঠানো হয়নি, অথবা ফিল্ডের নামের অমিল রয়েছে।',
                'fix_instructions' => 'অপ্রয়োজনীয় ফিল্ডগুলোকে `nullable` করুন যাতে ভ্যালিডেশন এরর না আসে।',
                'fix_code' => "\$validated = \$request->validate([\n    'title'       => 'required|string',\n    'content'     => 'required|string',\n    'image'       => 'nullable',\n    'category_id' => 'nullable',\n    'tags'        => 'nullable|string',\n    'slug'        => 'nullable|string',\n]);",
            ];
        }

        // ৮. cURL / SSL / Connection Refused
        if (str_contains($lowerError, 'curl error') || str_contains($lowerError, 'connection refused') || str_contains($lowerError, 'timed out') || str_contains($lowerError, 'could not resolve host')) {
            return [
                'type' => 'network_error',
                'badge' => 'Network / DNS / SSL Error',
                'problem' => 'ওয়েবসাইটের ডোমেইনে কানেক্ট করা যাচ্ছে না।',
                'reason' => 'ডোমেইন স্পেলিং ভুল, সার্ভার অফলাইন অথবা SSL সার্টিফিকেটে সমস্যা থাকতে পারে।',
                'fix_instructions' => 'আপনার দেওয়া Website URL ব্রাউজারে লোড হয় কি না চেক করুন এবং সঠিক প্রোটোকল (https://) দিন।',
                'fix_code' => "// সঠিক ডোমেইন ফরম্যাট উদাহরণ:\nhttps://mywebsite.com",
            ];
        }

        return null;
    }

    /**
     * 🧠 জটিল বা নতুন ধরনের এররের জন্য নিরাপদ AI ডায়াগনস্টিকস
     */
    private function analyzeWithAI(int $statusCode, string $sanitizedError, string $targetUrl, array $context, $userId = null): ?array
    {
        $systemPrompt = <<<EOT
You are an expert, read-only API Troubleshooting Assistant for news websites connecting to Subeditor24.
The user's website returned an HTTP error during connection verification.

CRITICAL SECURITY RULES:
1. You are running in STRICT READ-ONLY mode.
2. NEVER mention or refer to internal host paths, database credentials, server configuration, or infrastructure.
3. Focus ONLY on explaining why the CLIENT's remote API code failed and how they can fix their own controller / routes / config.
4. Output MUST be in natural, polite, helpful Bengali.

OUTPUT SCHEMA (JSON ONLY):
{
    "badge": "Short 2-3 word English Category Badge",
    "problem": "সমস্যাটি সংক্ষেপে ১ লাইনে লিখুন",
    "reason": "কেন ক্লায়েন্টের সার্ভারে এই সমস্যাটি হয়েছে তা সহজ বাংলায় বুঝিয়ে লিখুন",
    "fix_instructions": "কীভাবে তাদের ফাইলে ঠিক করতে হবে তার স্টেপ-বাই-স্টেপ নির্দেশনা",
    "fix_code": "সরাসরি ড্রপ-ইন PHP/Laravel/WordPress কোড স্নিপেট যা তারা কপি-পেস্ট করতে পারবে"
}
EOT;

        $userPrompt = "Target URL: {$targetUrl}\nHTTP Status: {$statusCode}\nError Payload:\n{$sanitizedError}";

        $settings = $userId ? \App\Models\UserSetting::where('user_id', $userId)->first() : null;
        $primaryAi = ($settings && $settings->primary_ai) ? $settings->primary_ai : 'deepseek';

        $providers = [$primaryAi, 'gemini', 'openai', 'groq', 'deepseek'];
        $providers = array_unique(array_filter($providers));

        foreach ($providers as $provider) {
            try {
                $rawResult = $this->callAiProviderForDiagnostics($provider, $systemPrompt, $userPrompt, $userId);
                if ($rawResult && isset($rawResult['problem'])) {
                    return $rawResult;
                }
            } catch (\Exception $e) {
                // Continue to next provider
            }
        }

        return null;
    }

    /**
     * 🤖 AI Provider Call Wrapper
     */
    private function callAiProviderForDiagnostics(string $provider, string $systemPrompt, string $userPrompt, $userId = null): ?array
    {
        $apiKey = null;
        $model = null;

        switch ($provider) {
            case 'gemini':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'gemini_api_key') ?? env('GEMINI_API_KEY');
                $model  = \App\Models\UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? 'gemini-1.5-flash';
                if (!$apiKey) return null;

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $resp = Http::timeout(15)->post($url, [
                    'contents' => [
                        ['parts' => [['text' => "{$systemPrompt}\n\nUser Input:\n{$userPrompt}"]]]
                    ],
                    'generationConfig' => ['responseMimeType' => 'application/json']
                ]);

                if ($resp->successful()) {
                    $content = $resp->json('candidates.0.content.parts.0.text');
                    return json_decode($content, true);
                }
                break;

            case 'deepseek':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? env('DEEPSEEK_API_KEY');
                $model  = \App\Models\UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? 'deepseek-chat';
                if (!$apiKey) return null;

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(15)->post("https://api.deepseek.com/chat/completions", [
                        "model" => $model,
                        "messages" => [
                            ["role" => "system", "content" => $systemPrompt],
                            ["role" => "user", "content" => $userPrompt]
                        ],
                        "response_format" => ["type" => "json_object"],
                        "temperature" => 0.2
                    ]);

                if ($resp->successful()) {
                    $content = $resp->json('choices.0.message.content');
                    return json_decode($content, true);
                }
                break;

            case 'openai':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? env('OPENAI_API_KEY');
                $model  = \App\Models\UserSetting::getSettingWithFallback($userId, 'openai_model') ?? 'gpt-4o-mini';
                if (!$apiKey) return null;

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(15)->post("https://api.openai.com/v1/chat/completions", [
                        "model" => $model,
                        "messages" => [
                            ["role" => "system", "content" => $systemPrompt],
                            ["role" => "user", "content" => $userPrompt]
                        ],
                        "response_format" => ["type" => "json_object"],
                        "temperature" => 0.2
                    ]);

                if ($resp->successful()) {
                    $content = $resp->json('choices.0.message.content');
                    return json_decode($content, true);
                }
                break;
        }

        return null;
    }

    /**
     * 🛡️ জেনেরিক ফলব্যাক মেসেজ
     */
    private function getGenericFallbackDiagnostics(int $statusCode, string $error): array
    {
        return [
            'type' => 'generic_error',
            'badge' => "HTTP {$statusCode} Error",
            'problem' => "সার্ভার থেকে HTTP {$statusCode} এরর রেসপন্স এসেছে।",
            'reason' => "সার্ভারের রিসিভার কোডে কোনো এক্সেপশন বা কনফিগারেশন সমস্যা হতে পারে।",
            'fix_instructions' => "আপনার ওয়েবসাইটের `routes/api.php` কোডটি এবং `.env` ফাইলের সিক্রেট টোকেন যাচাই করুন।",
            'fix_code' => "Route::post('/external-news-post', function (Request \$request) {\n    // Check token and process article\n});",
        ];
    }
}
