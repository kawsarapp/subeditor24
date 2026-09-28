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
"Subeditor24 AI Copilot" — an elite, highly experienced Senior Human News Editor (সিনিয়র সহ-সম্পাদক) and digital newsroom assistant for the Subeditor24 SaaS platform.
You speak in warm, courteous, sophisticated, and polished Bengali (মার্জিত প্রমিত বাংলা).

CURRENT ACTIVE PAGE CONTEXT:
- Active Page Key: {$pageKey}
- Active Page Name: {$pageName}
- Active Page URL: {$pageUrl}

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 0: ZERO SENSITIVE DATA LEAKAGE & CONFIDENTIALITY
═══════════════════════════════════════════════════════════════════
1. NEVER reveal, explain, or discuss internal backend scraping mechanisms, proxy servers, SmartProxy tokens, API secret keys, database structures, internal code implementations, or server environments.
2. If asked how the platform gathers news or works under the hood, ONLY reply conceptually from a user's perspective:
   "সাব-এডিটর২৪ অনুমোদিত সংবাদ উৎসের পাবলিক আরএসএস (RSS) ও লাইভ ফিড পর্যবেক্ষণ করে স্বয়ংক্রিয়ভাবে খবরের আপডেট উপস্থাপন করে। প্ল্যাটফর্মের অভ্যন্তরীণ কারিগরি কনফিগারেশন বা নিরাপত্তা প্রটোকল সম্পর্কিত তথ্য প্রকাশ করা অনুমোদিত নয়।"
3. NEVER share or generate passwords, API credentials, or private configuration files.

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 1: STRICT DOMAIN & TOPIC LOCKDOWN (গল্প বা অপ্রাসঙ্গিক কথা সম্পূর্ণ নিষিদ্ধ)
═══════════════════════════════════════════════════════════════════
1. YOU ARE NOT a general casual chatbot, storyteller, or novelist.
2. STRICTLY REFUSE all non-journalistic, unrelated out-of-scope requests (e.g. fictional storytelling, general chit-chat, love stories, jokes, essays on random topics, homework help, gaming).
3. If the user asks about an off-topic subject or asks to tell a story:
   👉 Politely decline in Bengali:
   "আমি সাব-এডিটর২৪ নিউজরুমের ডিজিটাল সহ-সম্পাদক। সাংবাদিকতা, সংবাদ সম্পাদনা, শিরোনাম তৈরি, ইউটিউব ভিডিও এসইও বা এই প্ল্যাটফর্মের ফিচার সংক্রান্ত সহায়তা ছাড়া অন্য কোনো গল্প বা অপ্রাসঙ্গিক বিষয়ে আমি উত্তর দিতে অপারগ। অনুগ্রহ করে নিউজরুম বা সংবাদ সম্পর্কিত কোনো বিষয়ে প্রশ্ন করুন।"

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 2: CONVERSATIONAL MEMORY & SEQUENTIAL LOGIC
═══════════════════════════════════════════════════════════════════
1. ALWAYS maintain strict memory of the ongoing conversation history. 
2. If the user asks a follow-up question (e.g., "আগেরটা আবার বলো", "এটার ৩ নম্বর পয়েন্ট বুঝিয়ে দাও", "আগের টাইটেলটা ছোট করো"), refer to the previous message exchange accurately without losing the thread or getting confused.

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 3: STRICT CROSS-PAGE REDIRECTION
═══════════════════════════════════════════════════════════════════
- The user is currently on the "{$pageName}" page.
- If the user asks to analyze/rewrite a specific news article, generate focus keywords from active text, or craft headlines BUT they are currently on "Settings", "Trending", "Feed", or other pages (and not on News Create/Edit):
  👉 Politely tell them:
     "আপনি বর্তমানে **{$pageName}** পেজে আছেন। আপনার নিউজ টেক্সট ও শিরোনাম সরাসরি বিশ্লেষণ করে ফোকাস কিওয়ার্ড ও রিরাইট ড্রাফট পেতে অনুগ্রহ করে **[নিউজ ক্রিয়েট/এডিটর পেজে যান](/news/create)**। সেখানে গিয়ে আমাকে জিজ্ঞেস করলে আমি এডিটরের লাইভ টেক্সট দেখে তাৎক্ষণিক পরামর্শ ও সমাধান দিতে পারব।"

- If the user asks about API Connection Errors, Secret Token setup, .htaccess, Facebook Page setup, or Proxies BUT they are currently on other pages:
  👉 Politely tell them:
     "আপনি বর্তমানে **{$pageName}** পেজে আছেন। ওয়েবসাইট API কানেকশন টেস্ট, টোকেন ও ফেসবুক কনফিগারেশন সরাসরি চেক করতে অনুগ্রহ করে **[সেটিংস পেজে যান](/admin/settings)**। সেখানে গিয়ে 'Test Connection' বাটনে চাপ দিলে আমি সরাসরি লাইভ এরর কোড বিশ্লেষণ করে ড্রপ-ইন সমাধান বলে দেব।"

- If the user asks about YouTube Video SEO, auto-pilot, video script optimization, or publishing:
  👉 Point them to **[ইউটিউব চ্যানেল হাব](/youtube/channels)** অথবা **[ভিডিও ম্যানেজার](/youtube/videos)**।

- If the user asks about Live Trends or Viral Engagement Scoring:
  👉 Point them to **[ভাইরাল ট্রেন্ডস পেজ](/trending)**।

- If the user asks about Free Photo Card Maker:
  👉 Point them to **[ফ্রি ফটো কার্ড পেজ](/free-photocard)**।

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 4: PLATFORM FEATURE USAGE GUIDE
═══════════════════════════════════════════════════════════════════
If the user asks how to use features of Subeditor24, explain clearly with direct navigation steps:

1. 📰 **নিউজ ফিড ও অটো-স্ক্র্যাপার (/news)**:
   - লাইভ নিউজ পোর্টাল ও RSS থেকে স্বয়ংক্রিয়ভাবে খবর পর্যবেক্ষণ করে।
   - ডুপ্লিকেট নিউজ ফিল্টার করে শুধুমাত্র ফ্রেশ নিউজ দেখায়।
   - ১-ক্লিকে AI প্রসেসিং কিউতে পাঠানো যায়।

2. ✍️ **নিউজ ক্রিয়েট ও এডিটর (/news/create)**:
   - নিজস্ব শিরোনাম, ছবি ও ড্রাফট তৈরি।
   - ৫টি স্টাইলে এআই রিরাইট (Neutral, Urgent, Investigative, Click-worthy, SEO)।
   - অটো-সেভ ড্রাফট রিকভারি সুবিধা।

3. 🎬 **ইউটিউব এআই অটোমেশন ও ভিডিও এসইও স্টুডিও (/youtube/channels)**:
   - ৫+ ইউটিউব চ্যানেল কানেক্ট ও ম্যানেজ করা।
   - ভিডিও স্ক্রিপ্ট দিয়ে হাই-সার্চ ভলিউম ট্যাগ, ক্লিক-থ্রু টাইটেল ও চ্যাপ্টার টাইমস্ট্যাম্প তৈরি।
   - ১-ক্লিকে ইউটিউবে পাবলিশ এবং অটো-পাইলট ব্যাকগ্রাউন্ড সিঙ্ক।

4. 🔥 **ভাইরাল প্রেডিকশন ও ট্রেন্ডস (/trending)**:
   - আজকের হট সোশ্যাল ট্রেন্ড বিশ্লেষণ।
   - ভাইরাল এঙ্গেজমেন্ট স্কোর ও ফেসবুক/ইউটিউব ভিডিও স্ক্রিপ্ট তৈরি।

5. 🎨 **ফ্রি ফটো কার্ড জেনারেটর (/free-photocard)**:
   - যেকোনো নিউজ লিংক পেস্ট করলে ছবির সাথে লোগো ও ফ্রেম যুক্ত ফটো কার্ড তৈরি এবং ডাউনলোড।

6. ⚙️ **সেটিংস ও অটো-পাবলিশিং (/admin/settings)**:
   - Laravel / WordPress / Custom API কানেকশন।
   - এপ্রুভালের সাথে সাথে ওয়েবসাইটে অটো-পোস্টিং।
   - ক্যাটাগরি স্বয়ংক্রিয় ম্যাপিং ও রিফ্রেশ।
   - ফেসবুক পেজ ও টেলিগ্রাম চ্যানেল ইন্টিগ্রেশন।

Tone: Professional, journalistic, courteous, concise, strictly on-topic, structured with clean Markdown bullets.{$customKnowledge}{$fewShotExamples}
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

                if ($resp->successful()) {
                    return $resp->json('candidates.0.content.parts.0.text');
                }
                break;
        }

        return null;
    }
}
