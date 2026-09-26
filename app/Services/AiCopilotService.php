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
        $temperature = 0.3;
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
                if (!empty($settings->primary_ai)) {
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
        $userPrompt = $this->buildContextAwareUserPrompt($message, $context, $history);

        $providers = array_unique(array_filter([$primaryAi, 'deepseek', 'gemini', 'openai', 'groq']));

        foreach ($providers as $provider) {
            try {
                $response = $this->callProvider($provider, $systemPrompt, $userPrompt, $userId, $temperature);
                if (!empty($response)) {
                    return [
                        'success'  => true,
                        'provider' => $provider,
                        'reply'    => $response,
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
     * 🧑‍💼 Context-Aware System Prompt with Cross-Page Redirection Rules
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
"Subeditor24 AI Copilot" — an elite, highly experienced Senior Human News Editor (সিনিয়র সহ-সম্পাদক) and friendly digital newsroom colleague.
You speak in warm, courteous, sophisticated, and polished Bengali (মার্জিত প্রমিত বাংলা).

CURRENT ACTIVE PAGE CONTEXT:
- Active Page Key: {$pageKey}
- Active Page Name: {$pageName}
- Active Page URL: {$pageUrl}

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 1: STRICT CROSS-PAGE REDIRECTION (অন্য পেজের কাজ হলে নির্দিষ্ট পেজে যাওয়ার পরামর্শ দিন)
═══════════════════════════════════════════════════════════════════
- The user is currently on the "{$pageName}" page.
- If the user asks to analyze/rewrite a specific news article, generate focus keywords from active text, or craft headlines BUT they are currently on "Settings", "Trending", "Feed", or other pages (and not on News Create/Edit):
  👉 Politely tell them:
     "আপনি বর্তমানে **{$pageName}** পেজে আছেন। আপনার নিউজ টেক্সট ও শিরোনাম সরাসরি বিশ্লেষণ করে ফোকাস কিওয়ার্ড ও রিরাইট ড্রাফট পেতে অনুগ্রহ করে **[নিউজ ক্রিয়েট/এডিটর পেজে যান](/news/create)**। সেখানে গিয়ে আমাকে জিজ্ঞেস করলে আমি এডিটরের লাইভ টেক্সট দেখে তাৎক্ষণিক পরামর্শ ও সমাধান দিতে পারব।"

- If the user asks about API Connection Errors, Secret Token setup, .htaccess, Facebook Page setup, or Scraper Proxies BUT they are currently on the "News Editor", "Trending", or other pages:
  👉 Politely tell them:
     "আপনি বর্তমানে **{$pageName}** পেজে আছেন। ওয়েবসাইট API কানেকশন টেস্ট, টোকেন ও ফেসবুক কনফিগারেশন সরাসরি চেক করতে অনুগ্রহ করে **[সেটিংস পেজে যান](/admin/settings)**। সেখানে গিয়ে 'Test Connection' বাটনে চাপ দিলে আমি সরাসরি লাইভ এরর কোড বিশ্লেষণ করে ড্রপ-ইন সমাধান বলে দেব।"

- If the user asks about YouTube Video SEO, auto-pilot, video script optimization, or publishing:
  👉 Point them to **[ইউটিউব চ্যানেল হাব](/youtube/channels)** অথবা **[ভিডিও ম্যানেজার](/youtube/videos)**।

- If the user asks about Live Trends or Viral Engagement Scoring while on Settings/Editor:
  👉 Point them to **[ভাইরাল ট্রেন্ডস পেজ](/trending)**।

- If the user asks about Free Photo Card Maker while elsewhere:
  👉 Point them to **[ফ্রি ফটো কার্ড পেজ](/free-photocard)**।

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 2: PROJECT FEATURE USAGE GUIDE (প্ল্যাটফর্মের ফিচার ব্যবহার শেখানো)
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

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 3: PROBLEM CLARIFICATION BEFORE BLIND RESPONSES
═══════════════════════════════════════════════════════════════════
- Never dump generic guesswork if the problem is unclear.
- Ask 1-2 focused, polite questions to understand the exact situation before providing the ultimate solution.

Tone: Professional, warm, respectful, concise, structured with clean Markdown bullets.{$customKnowledge}{$fewShotExamples}
EOT;
    }

    /**
     * 📝 Build User Prompt with Active Context
     */
    private function buildContextAwareUserPrompt(string $message, array $context, array $history = []): string
    {
        $prompt = "";

        if (!empty($history) && is_array($history)) {
            $prompt .= "--- RECENT CHAT HISTORY ---\n";
            $recentHistory = array_slice($history, -6);
            foreach ($recentHistory as $item) {
                $role = ($item['role'] ?? '') === 'user' ? 'User' : 'Assistant';
                $content = $item['content'] ?? '';
                $prompt .= "{$role}: {$content}\n";
            }
            $prompt .= "--- END OF HISTORY ---\n\n";
        }

        $prompt .= "User Message: {$message}\n";

        if (!empty($context['article_title'])) {
            $prompt .= "\n[Active News Headline in Editor]: " . Str::limit($context['article_title'], 250);
        }

        if (!empty($context['article_content'])) {
            $prompt .= "\n[Active News Body in Editor]: " . Str::limit(strip_tags($context['article_content']), 800);
        }

        if (!empty($context['error_context'])) {
            $prompt .= "\n[Active Error on Screen]: " . Str::limit($context['error_context'], 500);
        }

        return $prompt;
    }

    /**
     * 🌐 Call Provider
     */
    private function callProvider(string $provider, string $systemPrompt, string $userPrompt, int $userId, float $temperature = 0.3): ?string
    {
        switch ($provider) {
            case 'gemini':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'gemini_api_key') ?? env('GEMINI_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? 'gemini-1.5-flash';
                if (!$apiKey) return null;

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $resp = Http::timeout(25)->post($url, [
                    'contents' => [
                        ['parts' => [['text' => "{$systemPrompt}\n\n{$userPrompt}"]]]
                    ],
                    'generationConfig' => [
                        'temperature' => $temperature,
                        'maxOutputTokens' => 1500
                    ]
                ]);

                if ($resp->successful()) {
                    return $resp->json('candidates.0.content.parts.0.text');
                }
                break;

            case 'deepseek':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? env('DEEPSEEK_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? 'deepseek-chat';
                if (!$apiKey) return null;

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.deepseek.com/chat/completions", [
                        "model" => $model,
                        "messages" => [
                            ["role" => "system", "content" => $systemPrompt],
                            ["role" => "user", "content" => $userPrompt]
                        ],
                        "temperature" => $temperature,
                        "max_tokens" => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'openai':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? env('OPENAI_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? 'gpt-4o-mini';
                if (!$apiKey) return null;

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.openai.com/v1/chat/completions", [
                        "model" => $model,
                        "messages" => [
                            ["role" => "system", "content" => $systemPrompt],
                            ["role" => "user", "content" => $userPrompt]
                        ],
                        "temperature" => $temperature,
                        "max_tokens" => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'groq':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'groq_api_key') ?? env('GROQ_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? 'llama-3.3-70b-versatile';
                if (!$apiKey) return null;

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.groq.com/openai/v1/chat/completions", [
                        "model" => $model,
                        "messages" => [
                            ["role" => "system", "content" => $systemPrompt],
                            ["role" => "user", "content" => $userPrompt]
                        ],
                        "temperature" => $temperature,
                        "max_tokens" => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;
        }

        return null;
    }
}
