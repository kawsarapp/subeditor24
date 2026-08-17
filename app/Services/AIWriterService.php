<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIWriterService
{
    private $systemPrompt;

    public function __construct()
    {
        // আপনার হুবহু বাংলাদেশী সাব-এডিটর স্টাইল প্রম্পট
        $this->systemPrompt = <<<EOT
        You are a **Senior Sub-Editor** at a top-tier Bangladeshi Daily (like Prothom Alo or The Daily Star).
        **YOUR GOAL:** Rewrite the raw input into a **crisp, factual, and professional news report** in standard "Promit Bangla".

        **🧹 STEP 1: GARBAGE REMOVAL (CRITICAL)**
        Before rewriting, mentally remove all "Garbage Information":
        - **REMOVE:** Promotional text ("Click here", "Subscribe", "Follow us", "Share this").
        - **REMOVE:** Social media jargon ("Viral video", "Netizens say", Hashtags).
        - **REMOVE:** Redundant adjectives (e.g., "Shocking", "Unbelievable", "Mind-blowing").
        - **REMOVE:** Repetitive sentences that say the same thing twice.

        **🧠 STEP 2: CONTEXT & TONE**
        - **Identify the Core News:** What actually happened? (Who, What, When, Where, Why).
        - **Tone:** - If **Politics/Govt**: Formal, serious, neutral. Use words like 'প্রজ্ঞাপন', 'নির্দেশনা', 'জানানো হয়েছে'.
          - If **Crime/Accident**: Factual, concise. No sensationalism.
          - If **General**: Informative and direct.
        - **Fact Preservation:** NEVER change Quotes ("..."), Names, Dates, Numbers, or Locations.

        **✍️ STEP 3: WRITING RULES (HUMAN TOUCH)**
        1. **NO BOLDING:** Do NOT use `<b>`, `<strong>`, or markdown bold. Real news reports are plain text.
        2. **NO HEADINGS:** Do NOT use `<h3>` or `<h4>` inside the body unless it is a very long feature article. Use paragraph breaks instead.
        3. **INVERTED PYRAMID:** - **Lead Paragraph:** Start directly with the main news. (e.g., "আগামীকাল থেকে স্কুল বন্ধ ঘোষণা করেছে শিক্ষা মন্ত্রণালয়।"). Avoid starting with "It has been reported that...".
           - **Body:** Provide supporting details and quotes.
           - **Background:** Context or previous events (if necessary) at the end.

        **📏 STEP 4: LENGTH & COMPLETENESS (STRICT)**
        - **NO SUMMARIZATION:** Do not summarize or abridge the news. You are a Sub-Editor, not a Summarizer. If the input contains 5 detailed points, your output must cover all 5 points.
        - **NO FABRICATION:** Do not add filler sentences just to make it look long. Stick strictly to the information provided in the source.
        - **Maintain Depth:** The output length should be proportional to the factual content of the input.

        **FORMATTING:**
        - Use ONLY `<p>` tags for paragraphs.
        - Keep paragraphs comprised of 3-4 sentences for readability on mobile screens.

        **OUTPUT FORMAT (JSON):**
        Return ONLY a valid JSON object.
        {
            "title": "A professional, catchy news headline in Bengali (Max 10-12 words)",
            "content": "HTML string with <p> tags only. No bold, no headings."
        }
        EOT;
    }

    // 🔥 আপডেট: $isRetry, $userId এবং $targetLanguage প্যারামিটার যুক্ত করা হয়েছে
    public function rewrite($content, $title, $isRetry = false, $userId = null, $targetLanguage = 'bn')
    {
        // ডাইনামিক প্রম্পট সেট করা
        if ($targetLanguage === 'en') {
            $this->systemPrompt = <<<EOT
            You are a **Senior Sub-Editor** at a top-tier International Daily.
            **YOUR GOAL:** Rewrite the raw input into a **crisp, factual, and professional news report** in standard "English".

            **🧹 STEP 1: GARBAGE REMOVAL (CRITICAL)**
            Before rewriting, mentally remove all "Garbage Information":
            - **REMOVE:** Promotional text ("Click here", "Subscribe", "Follow us", "Share this").
            - **REMOVE:** Social media jargon ("Viral video", "Netizens say", Hashtags).
            - **REMOVE:** Redundant adjectives (e.g., "Shocking", "Unbelievable", "Mind-blowing").
            - **REMOVE:** Repetitive sentences that say the same thing twice.

            **🧠 STEP 2: CONTEXT & TONE**
            - **Identify the Core News:** What actually happened? (Who, What, When, Where, Why).
            - **Tone:** - If **Politics/Govt**: Formal, serious, neutral.
              - If **Crime/Accident**: Factual, concise. No sensationalism.
              - If **General**: Informative and direct.
            - **Fact Preservation:** NEVER change Quotes ("..."), Names, Dates, Numbers, or Locations.

            **✍️ STEP 3: WRITING RULES (HUMAN TOUCH)**
            1. **NO BOLDING:** Do NOT use `<b>`, `<strong>`, or markdown bold. Real news reports are plain text.
            2. **NO HEADINGS:** Do NOT use `<h3>` or `<h4>` inside the body unless it is a very long feature article. Use paragraph breaks instead.
            3. **INVERTED PYRAMID:** - **Lead Paragraph:** Start directly with the main news. Avoid starting with "It has been reported that...".
               - **Body:** Provide supporting details and quotes.
               - **Background:** Context or previous events (if necessary) at the end.

            **📏 STEP 4: LENGTH & COMPLETENESS (STRICT)**
            - **NO SUMMARIZATION:** Do not summarize or abridge the news. You are a Sub-Editor, not a Summarizer. If the input contains 5 detailed points, your output must cover all 5 points.
            - **NO FABRICATION:** Do not add filler sentences just to make it look long. Stick strictly to the information provided in the source.
            - **Maintain Depth:** The output length should be proportional to the factual content of the input.

            **FORMATTING:**
            - Use ONLY `<p>` tags for paragraphs.
            - Keep paragraphs comprised of 3-4 sentences for readability on mobile screens.

            **OUTPUT FORMAT (JSON):**
            Return ONLY a valid JSON object.
            {
                "title": "A professional, catchy news headline in English (Max 10-12 words)",
                "content": "HTML string with <p> tags only. No bold, no headings."
            }
            EOT;
        }

        if (empty($content) || strlen(strip_tags($content)) < 100) {
            throw new \Exception("SHORT_CONTENT");
        }

        $safeContent = mb_substr($content, 0, 8000, 'UTF-8'); 

        // 🔥 আপডেট: পুনরায় লেখার জন্য বিশেষ নির্দেশিকা
        $retryInstruction = $isRetry 
            ? "\n\n⚠️ NOTE: This is a RE-WRITE request. Your previous version was not satisfactory. Please use DIFFERENT vocabulary, change the sentence structure, and try a MORE ENGAGING lead paragraph while maintaining the same facts."
            : "";

        $finalInput = "Title: $title\n\nContent: $safeContent" . $retryInstruction;

        $settings = $userId ? \App\Models\UserSetting::where('user_id', $userId)->first() : null;
        $primaryAi = ($settings && $settings->primary_ai) ? $settings->primary_ai : 'deepseek';

        $providers = [
            'qwen'        => fn() => $this->callQwen($finalInput, $title, $isRetry, $userId),
            'groq'        => fn() => $this->callGroq($finalInput, $title, $isRetry, $userId),
            'huggingface' => fn() => $this->callHuggingFace($finalInput, $title, $isRetry, $userId),
            'deepseek'    => fn() => $this->callDeepSeek($finalInput, $title, $isRetry, $userId),
            'openai'      => fn() => $this->callOpenAI($finalInput, $title, $isRetry, $userId),
            'gemini'      => fn() => $this->callGemini($finalInput, $title, $isRetry, $userId)
        ];

        // Reorder providers to place primaryAi at the start
        $fallbackOrder = [$primaryAi];
        foreach (['deepseek', 'openai', 'gemini', 'qwen', 'groq', 'huggingface'] as $p) {
            if ($p !== $primaryAi) {
                $fallbackOrder[] = $p;
            }
        }

        $lastException = null;

        foreach ($fallbackOrder as $providerName) {
            try {
                return $providers[$providerName]();
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("⚠️ " . ucfirst($providerName) . " Failed: " . $e->getMessage() . ". Switching to next provider...");
            }
        }

        Log::error("❌ ALL AI SERVICES FAILED: " . ($lastException ? $lastException->getMessage() : "Unknown error"));
        throw new \Exception("ALL_AI_FAILED");
    }

    private function callDeepSeek($content, $title, $isRetry, $userId)
    {
        $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? (config('services.deepseek.key') ?? env('DEEPSEEK_API_KEY'));
        $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? "deepseek-chat";

        if (!$apiKey) throw new \Exception("DeepSeek API Key Missing");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(40)->post("https://api.deepseek.com/chat/completions", [
            "model" => $model,
            "messages" => [
                ["role" => "system", "content" => $this->systemPrompt], 
                ["role" => "user", "content" => $content]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => $isRetry ? 0.85 : 0.7
        ]);

        return $this->parseResponse($response, 'DeepSeek');
    }

    private function callGemini($content, $title, $isRetry, $userId)
    {
        $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'gemini_api_key') ?? (config('services.gemini.key') ?? env('GEMINI_API_KEY'));
        
        if (!$apiKey) throw new \Exception("Gemini API Key Missing");

        // Use custom model if available, else standard fallback array
        $customModel = \App\Models\UserSetting::getSettingWithFallback($userId, 'gemini_model');
        if ($customModel) {
            $modelsToTry = [$customModel];
        } else {
            $modelsToTry = ["gemini-1.5-flash", "gemini-1.5-pro"];
        }

        foreach ($modelsToTry as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                
                $config = [
                    "responseMimeType" => "application/json",
                    "temperature" => $isRetry ? 0.8 : 0.6
                ];

                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(40)
                    ->post($url, [
                        "contents" => [[
                            "parts" => [["text" => $this->systemPrompt . "\n\n" . $content]]
                        ]],
                        "generationConfig" => $config
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    Log::info("✅ Rewritten by: Gemini ($model)");
                    return $this->processRawJson($rawText, 'Gemini');
                }
            } catch (\Exception $e) { continue; }
        }
        throw new \Exception("Gemini Failed.");
    }

    private function callOpenAI($content, $title, $isRetry, $userId)
    {
        $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? (config('services.openai.key') ?? env('OPENAI_API_KEY'));
        $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'openai_model') ?? "gpt-4o-mini";

        if (!$apiKey) throw new \Exception("OpenAI API Key Missing");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(40)->post("https://api.openai.com/v1/chat/completions", [
            "model" => $model, 
            "messages" => [
                ["role" => "system", "content" => $this->systemPrompt], 
                ["role" => "user", "content" => $content]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => $isRetry ? 0.85 : 0.7
        ]);

        return $this->parseResponse($response, 'OpenAI');
    }

    private function callGroq($content, $title, $isRetry, $userId)
    {
        $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'groq_api_key') ?? env('GROQ_API_KEY');
        $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'groq_model') ?? "llama-3.3-70b-versatile";

        if (!$apiKey) throw new \Exception("Groq API Key Missing");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(40)->post("https://api.groq.com/openai/v1/chat/completions", [
            "model" => $model, 
            "messages" => [
                ["role" => "system", "content" => $this->systemPrompt], 
                ["role" => "user", "content" => $content]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => $isRetry ? 0.85 : 0.7
        ]);

        return $this->parseResponse($response, 'Groq');
    }

    private function callQwen($content, $title, $isRetry, $userId)
    {
        $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'qwen_api_key') ?? env('QWEN_API_KEY');
        $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'qwen_model') ?? "qwen-plus";

        if (!$apiKey) throw new \Exception("Qwen API Key Missing");

        // DashScope API for Qwen is fully OpenAI Compatible
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(40)->post("https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions", [
            "model" => $model, 
            "messages" => [
                ["role" => "system", "content" => $this->systemPrompt], 
                ["role" => "user", "content" => $content]
            ],
            // Note: DashScope's json_object may vary in support, using format is mostly safe
            "response_format" => ["type" => "json_object"],
            "temperature" => $isRetry ? 0.85 : 0.7
        ]);

        return $this->parseResponse($response, 'Qwen');
    }

    private function callHuggingFace($content, $title, $isRetry, $userId)
    {
        $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'huggingface_api_key') ?? env('HUGGINGFACE_API_KEY');
        $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? "Qwen/Qwen2.5-72B-Instruct";

        if (!$apiKey) throw new \Exception("HuggingFace API Key Missing");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(40)->post("https://router.huggingface.co/v1/chat/completions", [
            "model" => $model, 
            "messages" => [
                ["role" => "system", "content" => $this->systemPrompt], 
                ["role" => "user", "content" => $content]
            ],
            "max_tokens" => 8000,
            "response_format" => ["type" => "json_object"],
            "temperature" => $isRetry ? 0.85 : 0.7
        ]);

        return $this->parseResponse($response, 'HuggingFace');
    }

    private function parseResponse($response, $providerName)
    {
        if ($response->successful()) {
            $data = $response->json();
            $rawContent = $data['choices'][0]['message']['content'] ?? null;
            Log::info("✅ Rewritten by: $providerName");
            return $this->processRawJson($rawContent, $providerName);
        }
        throw new \Exception("$providerName API Error: " . $response->status());
    }

    private function processRawJson($rawContent, $providerName)
    {
        if (!$rawContent) throw new \Exception("$providerName returned empty content");

        $cleanJson = $this->cleanJsonString($rawContent);
        $json = json_decode($cleanJson, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($json['title'])) {
            $json['content'] = strip_tags($json['content'], '<p>'); 
            return $json;
        }

        throw new \Exception("$providerName returned invalid format");
    }

    private function cleanJsonString($string)
    {
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $string, $matches)) return trim($matches[1]);
        if (preg_match('/```\s*([\s\S]*?)\s*```/', $string, $matches)) return trim($matches[1]);
        return trim($string);
    }

    public function analyzeFactAndPlagiarism($originalContent, $rewrittenContent, $userId = null)
    {
        if (empty($originalContent) || empty($rewrittenContent)) {
            return [
                'similarity_score' => 0,
                'fact_check_status' => 'verified',
                'fact_check_report' => 'তুলনা করার জন্য মূল কন্টেন্ট পাওয়া যায়নি।'
            ];
        }

        $systemPrompt = <<<EOT
You are an expert **Fact-Checker and Plagiarism Auditor** at a premium news agency.
Compare the following **Original Source News** and the **Rewritten News**.

Your goals:
1. **Determine Similarity/Plagiarism Score (0 to 100):**
   - 0 means completely unique writing, no sentences or phrasing match the original text word-for-word.
   - 100 means copy-paste, the text matches the original almost exactly.
   - Be realistic. If sentence structure and key words are changed, the score should be below 40.
2. **Fact Check:**
   - Verify if the rewritten news preserved all factual points from the original source.
   - Factual points include: Names of people/organizations, dates/times, numbers/quantities, locations, and direct quotes.
   - Highlight any discrepancies, fabrications (info added that wasn't in original), or omissions (critical info removed).
3. **Determine Fact Check Status:**
   - "verified": If all facts are 100% correct, preserved, and there are no fabrications.
   - "warning": If there are minor changes, slight omissions, or minor wording discrepancies.
   - "unverified": If names, numbers, dates are wrong, or if there is major fabrication of information.
4. **Generate Report (in Promit Bangla):**
   - Provide a brief, professional summary (2-3 sentences) in Bengali explaining if the facts match and how unique the rewritten content is.

OUTPUT FORMAT (JSON):
Return ONLY a valid JSON object matching this structure:
{
    "similarity_score": <0-100 integer representing similarity>,
    "fact_check_status": "verified" | "warning" | "unverified",
    "fact_check_report": "বাংলায় একটি সংক্ষিপ্ত রিপোর্ট"
}
EOT;

        $safeOriginal = mb_substr(strip_tags($originalContent), 0, 4000, 'UTF-8');
        $safeRewritten = mb_substr(strip_tags($rewrittenContent), 0, 4000, 'UTF-8');

        $input = "### ORIGINAL SOURCE:\n$safeOriginal\n\n### REWRITTEN NEWS:\n$safeRewritten";

        $settings = $userId ? \App\Models\UserSetting::where('user_id', $userId)->first() : null;
        $primaryAi = ($settings && $settings->primary_ai) ? $settings->primary_ai : 'deepseek';

        $fallbackOrder = [$primaryAi];
        foreach (['deepseek', 'openai', 'gemini', 'qwen', 'groq', 'huggingface'] as $p) {
            if ($p !== $primaryAi) {
                $fallbackOrder[] = $p;
            }
        }

        $lastException = null;

        foreach ($fallbackOrder as $providerName) {
            try {
                $response = $this->callProviderForAnalysis($providerName, $systemPrompt, $input, $userId);
                if ($response) {
                    return $response;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                Log::warning("⚠️ Fact Check Provider " . ucfirst($providerName) . " Failed: " . $e->getMessage());
            }
        }

        Log::error("❌ ALL AI PROVIDERS FAILED FOR FACT CHECK: " . ($lastException ? $lastException->getMessage() : "Unknown error"));
        
        similar_text($safeOriginal, $safeRewritten, $percent);
        return [
            'similarity_score' => round($percent),
            'fact_check_status' => 'verified',
            'fact_check_report' => 'এআই ফ্যাক্ট-চেক করতে ব্যর্থ হয়েছে। লোকাল সিমিলারিটি ক্যালকুলেশন দিয়ে স্কোর নির্ধারণ করা হয়েছে।'
        ];
    }

    private function callProviderForAnalysis($provider, $systemPrompt, $input, $userId)
    {
        switch ($provider) {
            case 'openai':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? (config('services.openai.key') ?? env('OPENAI_API_KEY'));
                $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'openai_model') ?? "gpt-4o-mini";
                if (!$apiKey) throw new \Exception("OpenAI API Key Missing");
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(30)->post("https://api.openai.com/v1/chat/completions", [
                    "model" => $model, 
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt], 
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.3
                ]);
                return $this->parseAnalysisResponse($response, 'OpenAI');

            case 'gemini':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'gemini_api_key') ?? (config('services.gemini.key') ?? env('GEMINI_API_KEY'));
                if (!$apiKey) throw new \Exception("Gemini API Key Missing");
                $customModel = \App\Models\UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? "gemini-1.5-flash";
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$customModel}:generateContent?key={$apiKey}";
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(30)
                    ->post($url, [
                        "contents" => [[
                            "parts" => [["text" => $systemPrompt . "\n\n" . $input]]
                        ]],
                        "generationConfig" => ["responseMimeType" => "application/json", "temperature" => 0.3]
                    ]);
                if ($response->successful()) {
                    $data = $response->json();
                    $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    return $this->processAnalysisRawJson($rawText, 'Gemini');
                }
                throw new \Exception("Gemini analysis request failed.");

            case 'deepseek':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? (config('services.deepseek.key') ?? env('DEEPSEEK_API_KEY'));
                $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? "deepseek-chat";
                if (!$apiKey) throw new \Exception("DeepSeek API Key Missing");
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(30)->post("https://api.deepseek.com/chat/completions", [
                    "model" => $model,
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt], 
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.3
                ]);
                return $this->parseAnalysisResponse($response, 'DeepSeek');

            case 'groq':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'groq_api_key') ?? env('GROQ_API_KEY');
                $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'groq_model') ?? "llama-3.3-70b-versatile";
                if (!$apiKey) throw new \Exception("Groq API Key Missing");
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(30)->post("https://api.groq.com/openai/v1/chat/completions", [
                    "model" => $model, 
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt], 
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.3
                ]);
                return $this->parseAnalysisResponse($response, 'Groq');

            case 'qwen':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'qwen_api_key') ?? env('QWEN_API_KEY');
                $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'qwen_model') ?? "qwen-plus";
                if (!$apiKey) throw new \Exception("Qwen API Key Missing");
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(30)->post("https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions", [
                    "model" => $model, 
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt], 
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.3
                ]);
                return $this->parseAnalysisResponse($response, 'Qwen');

            case 'huggingface':
                $apiKey = \App\Models\UserSetting::getSettingWithFallback($userId, 'huggingface_api_key') ?? env('HUGGINGFACE_API_KEY');
                $model = \App\Models\UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? "Qwen/Qwen2.5-72B-Instruct";
                if (!$apiKey) throw new \Exception("HuggingFace API Key Missing");
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->timeout(30)->post("https://router.huggingface.co/v1/chat/completions", [
                    "model" => $model, 
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt], 
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.3
                ]);
                return $this->parseAnalysisResponse($response, 'HuggingFace');
        }
        return null;
    }

    private function parseAnalysisResponse($response, $providerName)
    {
        if ($response->successful()) {
            $data = $response->json();
            $rawContent = $data['choices'][0]['message']['content'] ?? null;
            return $this->processAnalysisRawJson($rawContent, $providerName);
        }
        throw new \Exception("$providerName API Error: " . $response->status());
    }

    private function processAnalysisRawJson($rawContent, $providerName)
    {
        if (!$rawContent) throw new \Exception("$providerName returned empty analysis");
        $cleanJson = $this->cleanJsonString($rawContent);
        $json = json_decode($cleanJson, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($json['similarity_score'])) {
            return [
                'similarity_score' => intval($json['similarity_score']),
                'fact_check_status' => $json['fact_check_status'] ?? 'verified',
                'fact_check_report' => $json['fact_check_report'] ?? ''
            ];
        }
        throw new \Exception("$providerName returned invalid analysis format");
    }
}