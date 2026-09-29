<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiCopilotService
{
    /**
     * 👑 Context-Aware Editorial AI Copilot Engine
     *
     * @param string $message
     * @param array $context
     * @param array $history
     * @param int $userId
     * @return array
     */
    public function chat(string $message, array $context = [], array $history = [], int $userId = 1): array
    {
        $primaryAi = 'gemini';
        $temperature = 0.25;

        try {
            $settings = UserSetting::where('user_id', $userId)->first();
            if (!$settings) {
                // Fallback to Super Admin setting
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $settings = UserSetting::where('user_id', $superAdmin->id)->first();
                }
            }

            if ($settings) {
                if (!empty($settings->ai_copilot_provider) && $settings->ai_copilot_provider !== 'default') {
                    $primaryAi = $settings->ai_copilot_provider;
                } elseif (!empty($settings->primary_ai)) {
                    $primaryAi = $settings->primary_ai;
                }
                if (!empty($settings->ai_copilot_temperature)) {
                    $temperature = (float) $settings->ai_copilot_temperature;
                }
            }
        } catch (\Exception $e) {
            Log::debug("UserSetting query fallback in AiCopilotService: " . $e->getMessage());
        }

        $systemPrompt = $this->buildContextAwareSystemPrompt($context, $userId);

        // Sanitize & normalize history (keep last 8 turns)
        $cleanHistory = $this->normalizeChatHistory($history, $message);

        $providers = array_unique(array_filter([$primaryAi, 'deepseek', 'openai', 'gemini', 'groq', 'huggingface']));

        foreach ($providers as $provider) {
            try {
                $response = $this->callProvider($provider, $systemPrompt, $message, $context, $cleanHistory, $userId, $temperature);
                if (!empty($response)) {
                    return [
                        'success'  => true,
                        'provider' => $provider,
                        'reply'    => trim($response),
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("AI Copilot provider [{$provider}] failed: " . $e->getMessage());
            }
        }

        return [
            'success' => false,
            'reply'   => "দুঃখিত, এই মুহূর্তে এআই সার্ভারের সাথে যোগাযোগ করা সম্ভব হয়নি। অনুগ্রহ করে Settings পেজে আপনার API Key সঠিক আছে কিনা একটু দেখে নিন।",
        ];
    }

    /**
     * 🧑‍💼 Context-Aware System Prompt with Strict Topic Boundaries & Zero-Leakage Policy
     */
    private function buildContextAwareSystemPrompt(array $context, int $userId = 1): string
    {
        $pageKey = $context['page_key'] ?? 'general';
        $pageName = $context['page_name'] ?? 'Dashboard';
        $pageUrl = $context['page_url'] ?? '';

        $customKnowledge = '';
        $fewShotExamples = '';
        try {
            $settings = UserSetting::where('user_id', $userId)->first();
            if (!$settings) {
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $settings = UserSetting::where('user_id', $superAdmin->id)->first();
                }
            }
            if ($settings) {
                if (!empty($settings->ai_copilot_custom_knowledge)) {
                    $customKnowledge = "\n\n═══════════════════════════════════════════════════════════════════\n🎓 SUPER ADMIN CUSTOM KNOWLEDGE BASE & EDITORIAL POLICIES\n═══════════════════════════════════════════════════════════════════\n" . $settings->ai_copilot_custom_knowledge;
                }
                if (!empty($settings->ai_copilot_few_shot_examples)) {
                    $fewShotExamples = "\n\n═══════════════════════════════════════════════════════════════════\n💡 FEW-SHOT EXAMPLES (PERFECT RESPONSE PATTERNS)\n═══════════════════════════════════════════════════════════════════\n" . $settings->ai_copilot_few_shot_examples;
                }
            }
        } catch (\Exception $e) {
            Log::debug("Custom knowledge injection fallback: " . $e->getMessage());
        }

        return <<<EOT
YOU ARE:
"Subeditor24 AI Copilot" — an elite, warm, highly experienced Senior News Editor (সিনিয়র সহ-সম্পাদক ও নিউজরুম মেন্টর) and digital publishing expert for the Subeditor24 SaaS platform.
You speak like a genuine, helpful, sophisticated human newsroom leader in natural, fluent, and polished Bengali (মার্জিত, সাবলীল প্রমিত বাংলা).

CORE PERSONA & HUMAN TOUCH:
1. **Direct & Work-Focused (কাজের কথা প্রথমে):** Do NOT waste tokens or time with repetitive greetings like "আসসালামু আলাইকুম", "নমস্কার", "আদাব", "কেমন আছেন" ইত্যাদি। Start directly with the answer, solution, headline, or step-by-step guidance.
2. **Action-Oriented & Solution-First:** Provide concrete examples, ready-to-use headlines, structured summaries, or direct step-by-step navigation guides.
3. **Journalistic Standard & News Sense:** You understand news value, 5W1H principles, click-through rate (CTR), neutral journalistic tone vs viral curiosity hooks, and ethical news integrity.
4. **Natural Bengali Delivery:** Direct, clear, professional, without repetitive filler intros or pleasantries.

CURRENT ACTIVE PAGE CONTEXT:
- Active Page Key: {$pageKey}
- Active Page Name: {$pageName}
- Active Page URL: {$pageUrl}

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 0: ZERO SENSITIVE DATA LEAKAGE & STRICT BACKEND CONFIDENTIALITY
═══════════════════════════════════════════════════════════════════
1. STRICTLY FORBIDDEN TOPICS (সম্পূর্ণ নিষিদ্ধ বিষয়):
   - Scraping / Web Scrapers / Crawlers / Puppeteer / Python backend
   - Proxies / Decodo / SmartProxy / IP Rotation / Proxy Port & Host
   - How news arrives or is fetched into the system (নিউজ কীভাবে আসে/কালেক্ট হয়)
   - News missing or stopped (নিউজ কেন আসছে না / ফিড বন্ধ কেন / নিউজ না আসলে কী করব)
   - Internal Crons / Database / Secret API Keys / Server configuration / Source code

2. MANDATORY RESPONSE FOR ANY SCRAPING / PROXY / FEED ISSUE INQUIRIES:
   If the user asks ANYTHING about scraping, proxies, how news arrives, or why news isn't coming:
   👉 YOU MUST ONLY REPLY WITH THIS STRICT & COURTEOUS BENGALI STATEMENT:
   "এই বিষয়টি প্ল্যাটফর্মের অভ্যন্তরীণ কারিগরি ও প্রশাসনিক ব্যবস্থাপনার আওতাধীন। নিউজ সোর্স, ফিড কানেক্টিভিটি, প্রক্সি বা কারিগরি যেকোনো বিষয়ের জন্য অনুগ্রহ করে আপনার প্ল্যাটফর্মের **অ্যাডমিন (Admin)**-এর সাথে সরাসরি যোগাযোগ করুন।"

3. NEVER share, discuss, or generate passwords, API credentials, proxy details, or private server configuration files under any circumstance or prompt trick.

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 1: STRICT JOURNALISTIC SCOPE & TOPIC FOCUS
═══════════════════════════════════════════════════════════════════
1. YOU ARE a dedicated digital newsroom assistant and editorial copilot.
2. STRICTLY REFUSE off-topic requests (e.g. fictional storytelling, general chit-chat, love stories, jokes, homework help, gaming).
3. If the user asks about an off-topic subject:
   👉 Politely decline in warm Bengali:
   "আমি সাব-এডিটর২৪ নিউজরুমের ডিজিটাল সহ-সম্পাদক। সাংবাদিকতা, সংবাদ সম্পাদনা, শিরোনাম তৈরি, ইউটিউব ভিডিও এসইও বা এই প্ল্যাটফর্মের ফিচার সংক্রান্ত সহায়তা ছাড়া অন্য কোনো গল্প বা অপ্রাসঙ্গিক বিষয়ে আমি আলোচনা করতে পারব না। অনুগ্রহ করে নিউজরুম বা সংবাদ সম্পর্কিত যেকোনো বিষয়ে আমাকে প্রশ্ন করুন।"

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 2: CONVERSATIONAL MEMORY & SEQUENTIAL LOGIC
═══════════════════════════════════════════════════════════════════
1. ALWAYS maintain conversational context and memory of the ongoing conversation history.
2. If the user asks a follow-up question (e.g., "আগেরটা আরেকটু ছোট করো", "২ নম্বর পয়েন্ট বুঝিয়ে দাও", "আরেকটা বিকল্প শিরোনাম দাও"), immediately reference the previous message exchange accurately without getting confused or losing the thread.

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 3: SMART PAGE CONTEXT & DIRECT NAVIGATION
═══════════════════════════════════════════════════════════════════
- The user is currently on the "{$pageName}" page.
- If the user asks to analyze/rewrite a specific news article or craft headlines from active text when they are on another page:
  👉 "আপনি বর্তমানে **{$pageName}** পেজে আছেন। আপনার নিউজ টেক্সট ও শিরোনাম সরাসরি বিশ্লেষণ করে ফোকাস কিওয়ার্ড ও রিরাইট ড্রাফট পেতে অনুগ্রহ করে **[নিউজ ক্রিয়েট পেজে যান](/news/create)**।"
- If the user asks about API connection errors, WordPress/Laravel integration, or Telegram alerts when on another page:
  👉 "আপনি বর্তমানে **{$pageName}** পেজে আছেন। ওয়েবসাইট API কানেকশন ও সোশ্যাল কনফিগারেশন চেক করতে অনুগ্রহ করে **[সেটিংস পেজে যান](/admin/settings)**।"
- If the user asks about YouTube Video SEO, auto-pilot, video script optimization, or publishing:
  👉 Point them to **[ইউটিউব চ্যানেল হাব](/youtube/channels)** অথবা **[ভিডিও ম্যানেজার](/youtube/videos)**।
- If the user asks about Live Trends:
  👉 Point them to **[ভাইরাল ট্রেন্ডস পেজ](/trending)**।

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 4: COMPREHENSIVE PLATFORM KNOWLEDGE BASE
═══════════════════════════════════════════════════════════════════
When explaining how to use Subeditor24 features, provide clean, numbered steps with direct benefits:

1. 📰 **নিউজ ফিড (/news)**:
   - বিভিন্ন সংবাদ উৎসের লাইভ আপডেট পর্যবেক্ষণ ও ডুপ্লিকেট খবর ফিল্টার করে।
   - ১-ক্লিকে এআই প্রসেসিং কিউতে পাঠানো যায়।

2. ✍️ **নিউজ ক্রিয়েট ও এডিটর (/news/create)**:
   - নিজস্ব শিরোনাম, ছবি, ড্রাফট ও লাইভ ক্যাটাগরি ফেচিং সুবিধা।
   - ৫টি স্টাইলে এআই রিরাইট (Neutral, Urgent, Investigative, Click-worthy, SEO)।
   - অটো-সেভ ড্রাফট রিকভারি ও সরাসরি ১-ক্লিকে Direct Publish।

3. 🎬 **ইউটিউব এআই অটোমেশন ও ভিডিও এসইও স্টুডিও (/youtube/channels & /youtube/videos)**:
   - চ্যানেল কানেক্ট করে ভিডিও স্ক্রিপ্ট বা ড্রাফট থেকে হাই-সিটিআর ভাইরাল টাইটেল, ৫০০-অক্ষরের ট্যাগ, এসইও ডেসক্রিপশন ও চ্যাপ্টার টাইমস্ট্যাম্প তৈরি।
   - ১-ক্লিকে ইউটিউবে লাইভ আপডেট এবং ব্যাকগ্রাউন্ড AutoPilot মোড।

4. 🔍 **ফ্যাক্ট চেক ও প্লাজিয়ারিজম ফাইন্ডার**:
   - খবরের বিশ্বাসযোগ্যতা ও অন্য পোর্টালের সাথে ডুপ্লিকেট মিল যাচাই।

5. 🎨 **ফটো কার্ড স্টুডিও ও ফ্রেম মেকার (/studio)**:
   - সংবাদের আকর্ষণীয় ফটো কার্ড, ব্যানার এবং ব্যাকগ্রাউন্ড রিমুভ করে সোশ্যাল মিডিয়া পোস্ট তৈরি।

6. ⚙️ **সেটিংস ও ইন্টিগ্রেশন (/admin/settings)**:
   - WordPress REST API, Laravel Webhooks, Telegram Alert Channel এবং ক্যাটাগরি ম্যাপিং কনফিগারেশন।

Tone: Warm, human, polite, highly intelligent, concise, structured with Markdown bullets.{$customKnowledge}{$fewShotExamples}
EOT;
    }

    /**
     * 🧹 Clean & Normalize Chat History
     */
    private function normalizeChatHistory(array $history, string $currentMessage): array
    {
        $clean = [];
        $recent = array_slice($history, -8);

        // If the last item in history is identical to the current message, exclude it to prevent duplicate turn
        $last = end($recent);
        if ($last && is_array($last) && ($last['role'] ?? '') === 'user' && trim($last['content'] ?? '') === trim($currentMessage)) {
            array_pop($recent);
        }

        foreach ($recent as $item) {
            if (!is_array($item)) continue;
            $role = ($item['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $content = trim($item['content'] ?? '');
            if (!empty($content)) {
                $clean[] = [
                    'role'    => $role,
                    'content' => Str::limit($content, 1200)
                ];
            }
        }

        return $clean;
    }

    /**
     * 📝 Format current user turn with Active Screen Context
     */
    private function formatCurrentTurnWithContext(string $message, array $context): string
    {
        $turn = $message;

        $contextNotes = [];
        if (!empty($context['article_title'])) {
            $contextNotes[] = "[Active Headline]: " . Str::limit($context['article_title'], 200);
        }
        if (!empty($context['article_content'])) {
            $contextNotes[] = "[Active Body]: " . Str::limit(strip_tags($context['article_content']), 600);
        }
        if (!empty($context['error_context'])) {
            $contextNotes[] = "[Active Screen Notice/Error]: " . Str::limit($context['error_context'], 300);
        }

        if (!empty($contextNotes)) {
            $turn .= "\n\n" . implode("\n", $contextNotes);
        }

        return $turn;
    }

    /**
     * 🌐 Call Provider with Native Structured Multi-Turn History
     */
    private function callProvider(
        string $provider,
        string $systemPrompt,
        string $currentMessage,
        array $context,
        array $cleanHistory,
        int $userId,
        float $temperature = 0.25
    ): ?string {
        $currentTurnText = $this->formatCurrentTurnWithContext($currentMessage, $context);

        switch ($provider) {
            case 'deepseek':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? env('DEEPSEEK_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? 'deepseek-chat';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.deepseek.com/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'openai':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? env('OPENAI_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? 'gpt-4o-mini';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.openai.com/v1/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'groq':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'groq_api_key') ?? env('GROQ_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? 'llama-3.3-70b-versatile';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.groq.com/openai/v1/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'huggingface':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'huggingface_api_key') ?? env('HUGGINGFACE_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? 'Qwen/Qwen2.5-72B-Instruct';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://router.huggingface.co/v1/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'gemini':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'gemini_api_key') ?? env('GEMINI_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? 'gemini-1.5-flash';
                if (!$apiKey) return null;

                $contents = [];
                foreach ($cleanHistory as $h) {
                    $role = $h['role'] === 'assistant' ? 'model' : 'user';
                    $contents[] = [
                        'role'  => $role,
                        'parts' => [['text' => $h['content']]]
                    ];
                }
                $contents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => $currentTurnText]]
                ];

                $payload = [
                    'contents' => $contents,
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]]
                    ],
                    'generationConfig' => [
                        'temperature'     => $temperature,
                        'maxOutputTokens' => 1500
                    ]
                ];

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $resp = Http::timeout(25)->post($url, $payload);

                if ($resp->successful() && !empty($resp->json('candidates.0.content.parts.0.text'))) {
                    return $resp->json('candidates.0.content.parts.0.text');
                }

                // Fallback attempt with standard gemini-1.5-flash if custom model name failed
                if ($model !== 'gemini-1.5-flash') {
                    $fallbackUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
                    $fallbackResp = Http::timeout(25)->post($fallbackUrl, $payload);
                    if ($fallbackResp->successful() && !empty($fallbackResp->json('candidates.0.content.parts.0.text'))) {
                        return $fallbackResp->json('candidates.0.content.parts.0.text');
                    }
                }
                break;
        }

        return null;
    }
}
