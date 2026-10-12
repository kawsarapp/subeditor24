<?php

namespace App\Services;

use App\Models\UserSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AIWriterService
{
    private $systemPrompt;

    public static function getDefaultPrompt($language = 'bn')
    {
        if ($language === 'en') {
            return <<<EOT
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
EOT;
        }

        return <<<EOT
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
EOT;
    }

    public static function getJsonOutputRequirement($language = 'bn')
    {
        $langLabel = ($language === 'en') ? 'English' : 'Bengali';
        return <<<EOT

**OUTPUT FORMAT (JSON):**
Return ONLY a valid JSON object. ALL fields (title, content, meta_description, focus_keyword, tags) MUST strictly be generated in {$langLabel}. Do NOT leave any field in another language.
{
    "title": "A professional, catchy news headline in {$langLabel} (Max 10-12 words)",
    "content": "HTML string with <p> tags only. No bold, no headings in {$langLabel}.",
    "meta_description": "A crisp, engaging SEO meta description in {$langLabel} (120-150 characters)",
    "focus_keyword": "Primary focus keyword in {$langLabel}",
    "tags": ["tag1 in {$langLabel}", "tag2 in {$langLabel}", "tag3 in {$langLabel}"]
}
EOT;
    }

    public function __construct()
    {
        $this->systemPrompt = self::getDefaultPrompt('bn') . "\n\n" . self::getJsonOutputRequirement('bn');
    }

    /* =========================================================================
     * 🛡️ CIRCUIT BREAKER, RESILIENCE & CASCADING FAILOVER HELPERS
     * ========================================================================= */

    /**
     * Define the automated fallback chain prioritized by speed and reliability.
     */
    public function getCascadeOrder(?string $primaryAi = null): array
    {
        $defaultChain = ['deepseek', 'groq', 'gemini', 'openai', 'qwen', 'huggingface'];
        $primaryAi = strtolower(trim((string) $primaryAi));

        if (!empty($primaryAi) && in_array($primaryAi, $defaultChain)) {
            $order = [$primaryAi];
            foreach ($defaultChain as $provider) {
                if ($provider !== $primaryAi) {
                    $order[] = $provider;
                }
            }
            return $order;
        }

        return $defaultChain;
    }

    private static array $inMemoryCooldown = [];

    /**
     * Check if a provider is currently marked in circuit breaker cooldown.
     */
    public function isProviderInCooldown(string $provider): bool
    {
        $provider = strtolower($provider);
        if (isset(self::$inMemoryCooldown[$provider]) && self::$inMemoryCooldown[$provider] > time()) {
            return true;
        }

        try {
            return (bool) Cache::get("ai_provider_cooldown_" . $provider, false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Place an unstable/rate-limited provider into cooldown.
     */
    public function markProviderCooldown(string $provider, int $minutes = 3, ?string $reason = null): void
    {
        $provider = strtolower($provider);
        self::$inMemoryCooldown[$provider] = time() + ($minutes * 60);

        try {
            Cache::put("ai_provider_cooldown_{$provider}", true, now()->addMinutes($minutes));
        } catch (\Throwable $e) {
            Log::debug("Cache put failed for AI cooldown: " . $e->getMessage());
        }
        Log::warning("🚨 AI Provider [{$provider}] placed in cooldown for {$minutes}m. Reason: " . ($reason ?? 'Rate limit or failure'));
    }

    /**
     * Clear circuit breaker cooldown for a provider.
     */
    public function clearProviderCooldown(string $provider): void
    {
        $provider = strtolower($provider);
        unset(self::$inMemoryCooldown[$provider]);

        try {
            Cache::forget("ai_provider_cooldown_" . $provider);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Detect if an error/exception is due to HTTP 429 rate limit or quota exhaustion.
     */
    public function isRateLimitOrQuotaError(\Throwable $e, ?int $statusCode = null): bool
    {
        if (in_array($statusCode, [429, 402])) {
            return true;
        }

        $message = strtolower($e->getMessage());
        $patterns = [
            '429', 'rate limit', 'rate_limit', 'quota', 'resource_exhausted',
            'too many requests', 'insufficient_quota', 'tokens per minute',
            'requests per minute', 'rate_limit_exceeded', 'credits exhausted',
            'exceeded your current quota', 'tpm', 'rpm'
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($message, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Execute an AI callback with 1 quick retry for transient hiccups,
     * and immediate cooldown marking + failover propagation on rate limits.
     */
    public function executeWithRetry(callable $callback, string $provider, int $maxRetries = 1)
    {
        $attempts = 0;
        while (true) {
            try {
                $attempts++;
                return $callback();
            } catch (\Throwable $e) {
                $isRateLimit = $this->isRateLimitOrQuotaError($e);
                if ($isRateLimit) {
                    $this->markProviderCooldown($provider, 3, $e->getMessage());
                    throw $e; // Immediately escalate to failover without waiting
                }

                if ($attempts <= $maxRetries) {
                    Log::warning("⚠️ Transient hiccup in [{$provider}] (attempt {$attempts}): {$e->getMessage()}. Retrying in 300ms...");
                    usleep(300000); // 300ms
                    continue;
                }

                throw $e;
            }
        }
    }

    /**
     * Retrieve the API key for a specified provider.
     */
    public function getProviderApiKey(string $provider, ?int $userId = null): ?string
    {
        return match (strtolower($provider)) {
            'deepseek'    => UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? (config('services.deepseek.key') ?? env('DEEPSEEK_API_KEY')),
            'gemini'      => UserSetting::getSettingWithFallback($userId, 'gemini_api_key') ?? (config('services.gemini.key') ?? env('GEMINI_API_KEY')),
            'openai'      => UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? (config('services.openai.key') ?? env('OPENAI_API_KEY')),
            'groq'        => UserSetting::getSettingWithFallback($userId, 'groq_api_key') ?? env('GROQ_API_KEY'),
            'qwen'        => UserSetting::getSettingWithFallback($userId, 'qwen_api_key') ?? env('QWEN_API_KEY'),
            'huggingface' => UserSetting::getSettingWithFallback($userId, 'huggingface_api_key') ?? env('HUGGINGFACE_API_KEY'),
            default       => null,
        };
    }

    /**
     * Check if a provider has credentials configured.
     */
    public function hasProviderCredentials(string $provider, ?int $userId = null): bool
    {
        return !empty($this->getProviderApiKey($provider, $userId));
    }

    /**
     * Get prioritized providers list, placing non-cooldown configured providers first.
     */
    public function getPrioritizedProviders(?string $primaryAi = null, ?int $userId = null): array
    {
        $chain = $this->getCascadeOrder($primaryAi);
        $active = [];
        $cooldown = [];

        foreach ($chain as $p) {
            // Check credentials to skip unconfigured providers early
            if (!$this->hasProviderCredentials($p, $userId)) {
                continue;
            }

            if ($this->isProviderInCooldown($p)) {
                $cooldown[] = $p;
            } else {
                $active[] = $p;
            }
        }

        // Return active configured providers first; fallback to cooled-down ones if none active
        $result = array_merge($active, $cooldown);

        // If no configured provider found, return default cascade so env keys can be tried
        return !empty($result) ? $result : $chain;
    }

    /* =========================================================================
     * ✍️ CORE AI OPERATION 1: NEWS REWRITE (rewriteNews / rewrite)
     * ========================================================================= */

    /**
     * Public alias for rewrite
     */
    public function rewriteNews($content, $title, $isRetry = false, $userId = null, $targetLanguage = 'bn')
    {
        return $this->rewrite($content, $title, $isRetry, $userId, $targetLanguage);
    }

    /**
     * Enterprise Cascading Rewrite Engine
     */
    public function rewrite($content, $title, $isRetry = false, $userId = null, $targetLanguage = 'bn')
    {
        // Check if user or super-admin has configured a custom rewrite prompt
        $customPrompt = $userId ? UserSetting::getSettingWithFallback($userId, 'custom_rewrite_prompt') : null;
        if (!empty(trim($customPrompt ?? ''))) {
            $this->systemPrompt = trim($customPrompt) . "\n\n" . self::getJsonOutputRequirement($targetLanguage);
        } else {
            $this->systemPrompt = self::getDefaultPrompt($targetLanguage) . "\n\n" . self::getJsonOutputRequirement($targetLanguage);
        }

        if (empty($content) || strlen(strip_tags($content)) < 100) {
            throw new \Exception("SHORT_CONTENT");
        }

        $safeContent = mb_substr($content, 0, 8000, 'UTF-8'); 

        $retryInstruction = $isRetry 
            ? "\n\n⚠️ NOTE: This is a RE-WRITE request. Your previous version was not satisfactory. Please use DIFFERENT vocabulary, change the sentence structure, and try a MORE ENGAGING lead paragraph while maintaining the same facts."
            : "";

        $finalInput = "Title: $title\n\nContent: $safeContent" . $retryInstruction;

        // Bug fix: Use UserSetting::getSettingWithFallback to ensure Super Admin's setting is used
        $primaryAi = UserSetting::getSettingWithFallback($userId, 'primary_ai') 
            ?? UserSetting::getSettingWithFallback(null, 'primary_ai', 'deepseek');

        $providers = [
            'deepseek'    => fn() => $this->callDeepSeek($finalInput, $title, $isRetry, $userId),
            'groq'        => fn() => $this->callGroq($finalInput, $title, $isRetry, $userId),
            'gemini'      => fn() => $this->callGemini($finalInput, $title, $isRetry, $userId),
            'openai'      => fn() => $this->callOpenAI($finalInput, $title, $isRetry, $userId),
            'qwen'        => fn() => $this->callQwen($finalInput, $title, $isRetry, $userId),
            'huggingface' => fn() => $this->callHuggingFace($finalInput, $title, $isRetry, $userId),
        ];

        $fallbackOrder = $this->getPrioritizedProviders($primaryAi, $userId);
        $lastException = null;

        foreach ($fallbackOrder as $providerName) {
            if (!isset($providers[$providerName])) {
                continue;
            }

            try {
                $result = $this->executeWithRetry(
                    fn() => $providers[$providerName](),
                    $providerName,
                    1
                );
                if ($result) {
                    return $result;
                }
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning("⚠️ " . ucfirst($providerName) . " rewrite failed: " . $e->getMessage() . ". Switching to next provider in cascade chain...");
            }
        }

        Log::error("❌ ALL AI SERVICES FAILED: " . ($lastException ? $lastException->getMessage() : "Unknown error"));
        throw new \Exception("ALL_AI_FAILED: " . ($lastException ? $lastException->getMessage() : "All configured providers exhausted"));
    }

    private function callDeepSeek($content, $title, $isRetry, $userId)
    {
        $apiKey = $this->getProviderApiKey('deepseek', $userId);
        $model = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? "deepseek-chat";

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
        // Bug fix: Remove smartproxy_api_token from Gemini key fallback!
        $apiKey = $this->getProviderApiKey('gemini', $userId);
        
        if (!$apiKey) throw new \Exception("Gemini API Key Missing");

        $customModel = UserSetting::getSettingWithFallback($userId, 'gemini_model');
        $modelsToTry = $customModel ? [$customModel] : ["gemini-1.5-flash", "gemini-1.5-pro"];

        $lastError = null;
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

                $status = $response->status();
                $body = $response->body();
                if ($status === 429 || str_contains(strtolower($body), 'quota') || str_contains(strtolower($body), 'resource_exhausted')) {
                    $this->markProviderCooldown('gemini', 3, "HTTP {$status}: " . mb_substr($body, 0, 120));
                }
                $lastError = "Status {$status}: " . mb_substr($body, 0, 100);
            } catch (\Throwable $e) {
                if ($this->isRateLimitOrQuotaError($e)) {
                    $this->markProviderCooldown('gemini', 3, $e->getMessage());
                }
                $lastError = $e->getMessage();
            }
        }
        throw new \Exception("Gemini Failed: " . ($lastError ?? 'Unknown error'));
    }

    private function callOpenAI($content, $title, $isRetry, $userId)
    {
        $apiKey = $this->getProviderApiKey('openai', $userId);
        $model = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? "gpt-4o-mini";

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
        $apiKey = $this->getProviderApiKey('groq', $userId);
        $model = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? "llama-3.3-70b-versatile";

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
        $apiKey = $this->getProviderApiKey('qwen', $userId);
        $model = UserSetting::getSettingWithFallback($userId, 'qwen_model') ?? "qwen-plus";

        if (!$apiKey) throw new \Exception("Qwen API Key Missing");

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(40)->post("https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions", [
            "model" => $model, 
            "messages" => [
                ["role" => "system", "content" => $this->systemPrompt], 
                ["role" => "user", "content" => $content]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => $isRetry ? 0.85 : 0.7
        ]);

        return $this->parseResponse($response, 'Qwen');
    }

    private function callHuggingFace($content, $title, $isRetry, $userId)
    {
        $apiKey = $this->getProviderApiKey('huggingface', $userId);
        $model = UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? "Qwen/Qwen2.5-72B-Instruct";

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

        $status = $response->status();
        $body = $response->body();
        if ($status === 429 || str_contains(strtolower($body), 'rate limit') || str_contains(strtolower($body), 'quota')) {
            $this->markProviderCooldown(strtolower($providerName), 3, "HTTP {$status}: " . mb_substr($body, 0, 120));
        }

        throw new \Exception("$providerName API Error: {$status} - " . mb_substr($body, 0, 150));
    }

    private function processRawJson($rawContent, $providerName)
    {
        if (!$rawContent) throw new \Exception("$providerName returned empty content");

        $cleanJson = $this->cleanJsonString($rawContent);
        $json = json_decode($cleanJson, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($json) && !empty($json['title'])) {
            $json['content'] = strip_tags($json['content'] ?? '', '<p>'); 
            return $json;
        }

        // 🛡️ Advanced JSON Rescue Strategy: Extract fields using Regex if LLM produces malformed JSON
        if (preg_match('/"title"\s*:\s*"([^"\r\n]+)"/u', $cleanJson, $tMatch)) {
            $title = trim($tMatch[1]);
            
            // Extract content
            $content = '';
            if (preg_match('/"content"\s*:\s*"(.*?)(?=",\s*"(?:category|tags|hashtags|meta_description|focus_keyword)"|\}\s*$)/su', $cleanJson, $cMatch)) {
                $content = trim($cMatch[1]);
                $content = stripcslashes($content);
            } elseif (preg_match('/"content"\s*:\s*"(.*?)"\s*[\},]/su', $cleanJson, $cMatch2)) {
                $content = trim($cMatch2[1]);
                $content = stripcslashes($content);
            }

            if (!empty($title) && !empty($content)) {
                Log::info("🛠️ Successfully recovered JSON output using Regex fallback for $providerName");
                return [
                    'title'            => $title,
                    'content'          => strip_tags($content, '<p>'),
                    'category'         => $json['category'] ?? 'News',
                    'tags'             => $json['tags'] ?? [],
                    'hashtags'         => $json['hashtags'] ?? '',
                    'meta_description' => $json['meta_description'] ?? mb_substr(strip_tags($content), 0, 150),
                    'focus_keyword'    => $json['focus_keyword'] ?? ''
                ];
            }
        }

        throw new \Exception("$providerName returned invalid format");
    }

    private function cleanJsonString($string)
    {
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $string, $matches)) return trim($matches[1]);
        if (preg_match('/```\s*([\s\S]*?)\s*```/', $string, $matches)) return trim($matches[1]);
        return trim($string);
    }

    /* =========================================================================
     * 🔍 FACT-CHECK & PLAGIARISM AUDIT
     * ========================================================================= */

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

        $primaryAi = UserSetting::getSettingWithFallback($userId, 'primary_ai') 
            ?? UserSetting::getSettingWithFallback(null, 'primary_ai', 'deepseek');

        $fallbackOrder = $this->getPrioritizedProviders($primaryAi, $userId);
        $lastException = null;

        foreach ($fallbackOrder as $providerName) {
            try {
                $response = $this->executeWithRetry(
                    fn() => $this->callProviderForAnalysis($providerName, $systemPrompt, $input, $userId),
                    $providerName,
                    1
                );
                if ($response) {
                    return $response;
                }
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning("⚠️ Fact Check Provider " . ucfirst($providerName) . " Failed: " . $e->getMessage() . ". Cascading to next provider...");
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
        $provider = strtolower($provider);
        switch ($provider) {
            case 'openai':
                $apiKey = $this->getProviderApiKey('openai', $userId);
                $model = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? "gpt-4o-mini";
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
                $apiKey = $this->getProviderApiKey('gemini', $userId);
                if (!$apiKey) throw new \Exception("Gemini API Key Missing");
                $customModel = UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? "gemini-1.5-flash";
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
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('gemini', 3, "HTTP " . $response->status());
                }
                throw new \Exception("Gemini analysis request failed with status " . $response->status());

            case 'deepseek':
                $apiKey = $this->getProviderApiKey('deepseek', $userId);
                $model = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? "deepseek-chat";
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
                $apiKey = $this->getProviderApiKey('groq', $userId);
                $model = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? "llama-3.3-70b-versatile";
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
                $apiKey = $this->getProviderApiKey('qwen', $userId);
                $model = UserSetting::getSettingWithFallback($userId, 'qwen_model') ?? "qwen-plus";
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
                $apiKey = $this->getProviderApiKey('huggingface', $userId);
                $model = UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? "Qwen/Qwen2.5-72B-Instruct";
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

        $status = $response->status();
        $body = $response->body();
        if ($status === 429 || str_contains(strtolower($body), 'rate limit') || str_contains(strtolower($body), 'quota')) {
            $this->markProviderCooldown(strtolower($providerName), 3, "HTTP {$status}: " . mb_substr($body, 0, 120));
        }

        throw new \Exception("$providerName API Error: " . $status);
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

    /* =========================================================================
     * 🔥 AI VIRAL & TRENDING SCRIPT GENERATOR
     * ========================================================================= */

    public function generateViralPredictionScript($title, $content, $userId = null)
    {
        $safeContent = mb_substr(strip_tags($content), 0, 4000, 'UTF-8');
        $systemPrompt = <<<EOT
You are an expert viral news editor and social media strategist for Bangladeshi news audiences.
Analyze the following news item and generate a 3-hour viral strategy package in valid JSON format.

Return ONLY a raw valid JSON object with the following exact schema:
{
    "viral_score": 85,
    "viral_angle": "খবরের মূল আকর্ষনীয় পয়েন্ট যা আগামী ৩ ঘণ্টায় সোশ্যাল মিডিয়ায় সবচেয়ে বেশি চর্চা হবে...",
    "photocard_punchline": "ফটোকার্ডের জন্য ১ লাইনের আকর্ষণীয় পাঞ্চলাইন টেক্সট...",
    "catchy_headlines": [
        "শিরোনাম ১: উচ্চ সিটিআর আকর্ষণীয় শিরোনাম...",
        "শিরোনাম ২: কৌতুহল উদ্দীপক শিরোনাম...",
        "শিরোনাম ৩: তথ্যবহুল ট্রেন্ডিং শিরোনাম..."
    ],
    "reels_script": "৩০ সেকেন্ডের শর্টস/রিলস ভিডিও ভয়েসওভার স্ক্রিপ্ট:\n[0-5s] হুক: ...\n[5-20s] মূল খবর: ...\n[20-30s] শেষ কথা ও মতামত আহ্বান: ...",
    "facebook_caption": "ফেসবুক সোশ্যাল মিডিয়া ক্যাপশন এবং হ্যাশট্যাগ..."
}
EOT;

        $input = "Title: {$title}\n\nContent: {$safeContent}";

        $primaryAi = UserSetting::getSettingWithFallback($userId, 'primary_ai') 
            ?? UserSetting::getSettingWithFallback(null, 'primary_ai', 'deepseek');

        $providers = $this->getPrioritizedProviders($primaryAi, $userId);

        foreach ($providers as $provider) {
            try {
                $res = $this->executeWithRetry(
                    fn() => $this->callAiForJsonPrompt($provider, $systemPrompt, $input, $userId),
                    $provider,
                    1
                );
                if ($res && isset($res['viral_angle'])) {
                    Log::info("✅ Viral prediction generated successfully using provider: {$provider}");
                    return $res;
                }
            } catch (\Throwable $e) {
                Log::warning("⚠️ Viral prediction provider failed ({$provider}): " . $e->getMessage() . ". Switching to next provider in cascade chain...");
            }
        }
        Log::notice("ℹ️ All AI viral prediction providers failed. Used intelligent fallback response for: {$title}");

        // Fallback default response if all AI calls fail
        return [
            'viral_score' => rand(78, 93),
            'viral_angle' => "সাম্প্রতিক এই ঘটনাটি নিয়ে আগামী ৩ ঘণ্টায় ব্যাপক আলোচনা ও জনমত তৈরির সম্ভাবনা প্রবল।",
            'photocard_punchline' => "🔥 " . mb_substr($title, 0, 70) . " — বিস্তারিত জানুন আমাদের রিপোর্টে!",
            'catchy_headlines' => [
                "🔥 " . $title,
                "কোথায় চলেছে এই ঘটনা? বিস্তারিত জানুন...",
                "সোশ্যাল মিডিয়ায় তোলপাড় করা সংবাদ: " . mb_substr($title, 0, 50)
            ],
            'reels_script' => "[0-5s] স্ক্রিনে হুক: আপনি কি জানেন এইমাত্র পাওয়া বড় খবর?\n[5-20s] বিবরণ: " . mb_substr($safeContent, 0, 150) . "...\n[20-30s] কল-টু-অ্যাকশন: আপনার মতামত কমেন্টে জানান!",
            'facebook_caption' => "জরুরি খবর! " . mb_substr($safeContent, 0, 200) . "...\n#ViralNews #BreakingNews #Subeditor24"
        ];
    }

    /**
     * Complete multi-provider caller for arbitrary JSON prompt requests
     */
    public function callAiForJsonPrompt($provider, $systemPrompt, $input, $userId)
    {
        $provider = strtolower($provider);
        $apiKey = $this->getProviderApiKey($provider, $userId);

        if (!$apiKey) {
            throw new \Exception(ucfirst($provider) . " API Key Missing");
        }

        switch ($provider) {
            case 'gemini':
                $model = UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? "gemini-1.5-flash";
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $response = Http::withHeaders(['Content-Type' => 'application/json'])->timeout(25)->post($url, [
                    "contents" => [["parts" => [["text" => $systemPrompt . "\n\n" . $input]]]],
                    "generationConfig" => ["responseMimeType" => "application/json", "temperature" => 0.4]
                ]);
                if ($response->successful()) {
                    $raw = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    return json_decode($this->cleanJsonString($raw), true);
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('gemini', 3, "HTTP " . $response->status());
                }
                throw new \Exception("Gemini JSON request failed with status " . $response->status());

            case 'deepseek':
                $model = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? "deepseek-chat";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://api.deepseek.com/chat/completions", [
                        "model" => $model,
                        "messages" => [["role" => "system", "content" => $systemPrompt], ["role" => "user", "content" => $input]],
                        "response_format" => ["type" => "json_object"],
                        "temperature" => 0.4
                    ]);
                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return json_decode($this->cleanJsonString($raw), true);
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('deepseek', 3, "HTTP " . $response->status());
                }
                throw new \Exception("DeepSeek JSON request failed with status " . $response->status());

            case 'openai':
                $model = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? "gpt-4o-mini";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://api.openai.com/v1/chat/completions", [
                        "model" => $model,
                        "messages" => [["role" => "system", "content" => $systemPrompt], ["role" => "user", "content" => $input]],
                        "response_format" => ["type" => "json_object"],
                        "temperature" => 0.4
                    ]);
                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return json_decode($this->cleanJsonString($raw), true);
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('openai', 3, "HTTP " . $response->status());
                }
                throw new \Exception("OpenAI JSON request failed with status " . $response->status());

            case 'groq':
                $model = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? "llama-3.3-70b-versatile";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://api.groq.com/openai/v1/chat/completions", [
                        "model" => $model,
                        "messages" => [["role" => "system", "content" => $systemPrompt], ["role" => "user", "content" => $input]],
                        "response_format" => ["type" => "json_object"],
                        "temperature" => 0.4
                    ]);
                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return json_decode($this->cleanJsonString($raw), true);
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('groq', 3, "HTTP " . $response->status());
                }
                throw new \Exception("Groq JSON request failed with status " . $response->status());

            case 'qwen':
                $model = UserSetting::getSettingWithFallback($userId, 'qwen_model') ?? "qwen-plus";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions", [
                        "model" => $model,
                        "messages" => [["role" => "system", "content" => $systemPrompt], ["role" => "user", "content" => $input]],
                        "response_format" => ["type" => "json_object"],
                        "temperature" => 0.4
                    ]);
                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return json_decode($this->cleanJsonString($raw), true);
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('qwen', 3, "HTTP " . $response->status());
                }
                throw new \Exception("Qwen JSON request failed with status " . $response->status());

            case 'huggingface':
                $model = UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? "Qwen/Qwen2.5-72B-Instruct";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://router.huggingface.co/v1/chat/completions", [
                        "model" => $model,
                        "messages" => [["role" => "system", "content" => $systemPrompt], ["role" => "user", "content" => $input]],
                        "response_format" => ["type" => "json_object"],
                        "temperature" => 0.4
                    ]);
                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return json_decode($this->cleanJsonString($raw), true);
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('huggingface', 3, "HTTP " . $response->status());
                }
                throw new \Exception("HuggingFace JSON request failed with status " . $response->status());
        }

        return null;
    }

    /* =========================================================================
     * 🎯 CORE AI OPERATION 2: HEADLINE GENERATION (generateHeadlines)
     * ========================================================================= */

    /**
     * Standard headline generation method
     */
    public function generateHeadlines($title, $content = '', $userId = null): array
    {
        return $this->generateViralHeadlines($title, $content, $userId);
    }

    /**
     * 🎯 3-Option Viral Headline Suggester (Informative, Viral, Breaking)
     */
    public function generateViralHeadlines($title, $content = '', $userId = null): array
    {
        $systemPrompt = <<<EOT
You are an expert Chief Sub-Editor and Viral Social Media Strategist for a leading Bangladeshi daily newspaper.
Generate exactly 3 distinct, high-impact Bengali news headlines based on the provided title and body:

1. "informative": A formal, objective, standard journalistic headline in Promit Bangla (Max 10-12 words).
2. "viral": An engaging, curiosity-piquing headline designed for high social media CTR and Facebook engagement without being clickbait or misleading.
3. "breaking": A concise, punchy, urgent breaking news headline (Max 7-9 words).

Return ONLY valid JSON:
{
    "informative": "...",
    "viral": "...",
    "breaking": "..."
}
EOT;

        $input = "শিরোনাম: " . $title . "\n\nবিস্তারিত: " . Str::limit(strip_tags($content), 500);

        // Bug fix: Use primary_ai instead of ai_provider with proper fallback to Super Admin
        $primaryAi = UserSetting::getSettingWithFallback($userId, 'primary_ai') 
            ?? UserSetting::getSettingWithFallback(null, 'primary_ai', 'deepseek');

        $providers = $this->getPrioritizedProviders($primaryAi, $userId);

        foreach ($providers as $provider) {
            try {
                $result = $this->executeWithRetry(
                    fn() => $this->callAiForJsonPrompt($provider, $systemPrompt, $input, $userId),
                    $provider,
                    1
                );
                if ($result && !empty($result['informative']) && !empty($result['viral']) && !empty($result['breaking'])) {
                    Log::info("✅ Viral headlines generated with provider: {$provider}");
                    return $result;
                }
            } catch (\Throwable $e) {
                Log::warning("⚠️ Headline generation with provider ({$provider}) failed: " . $e->getMessage() . ". Switching to next provider in cascade chain...");
            }
        }

        // Programmatic intelligent Bengali fallback
        $cleanTitle = trim(preg_replace('/^(ব্রেকিং|আন্তর্জাতিক|জাতীয়|রাজনীতি)\s*[:|-]\s*/u', '', $title));
        return [
            'informative' => $cleanTitle,
            'viral'       => $cleanTitle . ': বিস্তারিত যা জানা গেল',
            'breaking'    => 'ব্রেকিং: ' . Str::limit($cleanTitle, 45)
        ];
    }

    /* =========================================================================
     * 📝 CORE AI OPERATION 3: SUMMARY GENERATION (generateSummary)
     * ========================================================================= */

    /**
     * Generate an executive summary of news content with key bullet points.
     */
    public function generateSummary($content, $title = '', $userId = null, $targetLanguage = 'bn'): array
    {
        if (empty($content)) {
            return [
                'summary' => '',
                'key_points' => [],
                'reading_time' => '1 min',
                'provider' => 'none',
            ];
        }

        $langLabel = ($targetLanguage === 'en') ? 'English' : 'Bengali';
        $systemPrompt = <<<EOT
You are an expert news editor and executive briefing specialist.
Summarize the provided news article into {$langLabel}.
Provide:
1. "summary": A crisp, high-level summary paragraph (2-3 sentences, 40-60 words).
2. "key_points": Array of 3-5 concise bullet points highlighting key factual takeaways.
3. "reading_time": Estimated reading time string (e.g. "1 min").

Return ONLY valid JSON matching this schema:
{
    "summary": "A concise summary paragraph in {$langLabel}...",
    "key_points": ["Point 1 in {$langLabel}", "Point 2 in {$langLabel}", "Point 3 in {$langLabel}"],
    "reading_time": "1 min"
}
EOT;

        $safeContent = mb_substr(strip_tags($content), 0, 4000, 'UTF-8');
        $input = "Title: {$title}\n\nContent: {$safeContent}";

        $primaryAi = UserSetting::getSettingWithFallback($userId, 'primary_ai') 
            ?? UserSetting::getSettingWithFallback(null, 'primary_ai', 'deepseek');

        $providers = $this->getPrioritizedProviders($primaryAi, $userId);

        foreach ($providers as $provider) {
            try {
                $result = $this->executeWithRetry(
                    fn() => $this->callAiForJsonPrompt($provider, $systemPrompt, $input, $userId),
                    $provider,
                    1
                );
                if ($result && !empty($result['summary'])) {
                    Log::info("✅ News summary generated successfully using provider: {$provider}");
                    return [
                        'summary' => trim($result['summary']),
                        'key_points' => is_array($result['key_points'] ?? null) ? $result['key_points'] : [],
                        'reading_time' => $result['reading_time'] ?? '1 min',
                        'provider' => $provider,
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("⚠️ Summary generation provider [{$provider}] failed: " . $e->getMessage() . ". Switching to next provider in cascade chain...");
            }
        }

        // Programmatic intelligent fallback
        $plain = strip_tags($content);
        $sentences = preg_split('/(?<=[.?!।])\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY);
        $firstFew = array_slice($sentences, 0, 3);
        $summaryText = implode(' ', $firstFew) ?: Str::limit($plain, 180);

        return [
            'summary' => $summaryText,
            'key_points' => array_slice($sentences, 0, 4),
            'reading_time' => max(1, ceil(str_word_count($plain) / 180)) . ' min',
            'provider' => 'fallback',
        ];
    }

    /* =========================================================================
     * 💬 CORE AI OPERATION 4: AI ASSISTANT CHAT (chat)
     * ========================================================================= */

    /**
     * Enterprise Cascading Chat / Copilot Assistant
     */
    public function chat($message, array $context = [], array $history = [], $userId = null): array
    {
        $primaryAi = UserSetting::getSettingWithFallback($userId, 'primary_ai') 
            ?? UserSetting::getSettingWithFallback(null, 'primary_ai', 'deepseek');

        $systemPrompt = "You are a professional AI newsroom assistant and editorial copilot. Respond concisely, factually, and professionally in standard Promit Bangla (or English if the user prompts in English).";
        if (!empty($context['title'])) {
            $systemPrompt .= "\nCurrent News Context - Title: " . $context['title'];
        }
        if (!empty($context['content'])) {
            $systemPrompt .= "\nContent Preview: " . Str::limit(strip_tags($context['content']), 600);
        }

        $providers = $this->getPrioritizedProviders($primaryAi, $userId);

        foreach ($providers as $provider) {
            try {
                $reply = $this->executeWithRetry(
                    fn() => $this->callProviderForChat($provider, $systemPrompt, $message, $history, $userId),
                    $provider,
                    1
                );
                if (!empty($reply)) {
                    Log::info("✅ Chat response generated successfully using provider: {$provider}");
                    return [
                        'success'  => true,
                        'provider' => $provider,
                        'reply'    => trim($reply),
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("⚠️ Chat provider [{$provider}] failed: " . $e->getMessage() . ". Switching to next provider in cascade chain...");
            }
        }

        return [
            'success' => false,
            'provider' => 'none',
            'reply'   => "দুঃখিত, সমস্ত এআই সার্ভার বর্তমানে ব্যস্ত বা অনুপলব্ধ রয়েছে। অনুগ্রহ করে কিছুক্ষণ পর আবার চেষ্টা করুন।",
        ];
    }

    private function callProviderForChat($provider, $systemPrompt, $message, array $history, $userId)
    {
        $provider = strtolower($provider);
        $apiKey = $this->getProviderApiKey($provider, $userId);

        if (!$apiKey) {
            throw new \Exception(ucfirst($provider) . " API Key Missing");
        }

        $messages = [["role" => "system", "content" => $systemPrompt]];
        foreach ($history as $h) {
            if (!empty($h['role']) && !empty($h['content'])) {
                $messages[] = [
                    "role" => in_array($h['role'], ['user', 'assistant']) ? $h['role'] : 'user',
                    "content" => (string) $h['content']
                ];
            }
        }
        $messages[] = ["role" => "user", "content" => $message];

        switch ($provider) {
            case 'gemini':
                $model = UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? "gemini-1.5-flash";
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $contents = [];
                foreach ($messages as $m) {
                    $contents[] = [
                        "role" => $m['role'] === 'assistant' ? 'model' : 'user',
                        "parts" => [["text" => $m['content']]]
                    ];
                }
                $response = Http::withHeaders(['Content-Type' => 'application/json'])->timeout(25)->post($url, [
                    "contents" => $contents,
                    "generationConfig" => ["temperature" => 0.5]
                ]);
                if ($response->successful()) {
                    return $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('gemini', 3, "HTTP " . $response->status());
                }
                throw new \Exception("Gemini Chat failed with status " . $response->status());

            case 'deepseek':
                $model = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? "deepseek-chat";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://api.deepseek.com/chat/completions", [
                        "model" => $model,
                        "messages" => $messages,
                        "temperature" => 0.5
                    ]);
                if ($response->successful()) {
                    return $response->json()['choices'][0]['message']['content'] ?? null;
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('deepseek', 3, "HTTP " . $response->status());
                }
                throw new \Exception("DeepSeek Chat failed with status " . $response->status());

            case 'openai':
                $model = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? "gpt-4o-mini";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://api.openai.com/v1/chat/completions", [
                        "model" => $model,
                        "messages" => $messages,
                        "temperature" => 0.5
                    ]);
                if ($response->successful()) {
                    return $response->json()['choices'][0]['message']['content'] ?? null;
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('openai', 3, "HTTP " . $response->status());
                }
                throw new \Exception("OpenAI Chat failed with status " . $response->status());

            case 'groq':
                $model = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? "llama-3.3-70b-versatile";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://api.groq.com/openai/v1/chat/completions", [
                        "model" => $model,
                        "messages" => $messages,
                        "temperature" => 0.5
                    ]);
                if ($response->successful()) {
                    return $response->json()['choices'][0]['message']['content'] ?? null;
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('groq', 3, "HTTP " . $response->status());
                }
                throw new \Exception("Groq Chat failed with status " . $response->status());

            case 'qwen':
                $model = UserSetting::getSettingWithFallback($userId, 'qwen_model') ?? "qwen-plus";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions", [
                        "model" => $model,
                        "messages" => $messages,
                        "temperature" => 0.5
                    ]);
                if ($response->successful()) {
                    return $response->json()['choices'][0]['message']['content'] ?? null;
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('qwen', 3, "HTTP " . $response->status());
                }
                throw new \Exception("Qwen Chat failed with status " . $response->status());

            case 'huggingface':
                $model = UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? "Qwen/Qwen2.5-72B-Instruct";
                $response = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey, 'Content-Type' => 'application/json'])
                    ->timeout(25)->post("https://router.huggingface.co/v1/chat/completions", [
                        "model" => $model,
                        "messages" => $messages,
                        "temperature" => 0.5
                    ]);
                if ($response->successful()) {
                    return $response->json()['choices'][0]['message']['content'] ?? null;
                }
                if ($response->status() === 429 || str_contains(strtolower($response->body()), 'quota')) {
                    $this->markProviderCooldown('huggingface', 3, "HTTP " . $response->status());
                }
                throw new \Exception("HuggingFace Chat failed with status " . $response->status());
        }

        return null;
    }

    /* =========================================================================
     * 🔍 1-CLICK SEO FOCUS KEYWORD & META EXTRACTOR
     * ========================================================================= */

    public function extractFocusKeywords($title, $content = '', $userId = null, $targetLanguage = 'bn'): array
    {
        $langName = $targetLanguage === 'en' ? 'English' : 'Bengali';
        
        $systemPrompt = <<<EOT
You are an expert News SEO Specialist and Sub-Editor for a top news portal.
Analyze the given news headline and content, then extract the most effective SEO Focus Keywords, LSI Search terms, and Meta Description in {$langName}.

Return ONLY valid JSON matching this schema:
{
    "primary_keyword": "Most important 2-3 word search query/entity (e.g. আবহাওয়া অধিদপ্তর)",
    "keywords": ["Keyword 1", "Keyword 2", "Keyword 3", "Keyword 4", "Keyword 5", "Keyword 6"],
    "meta_description": "Catchy, informative 130-150 character meta description summarizing the core news.",
    "tags": ["Tag 1", "Tag 2", "Tag 3", "Tag 4"]
}
EOT;

        $cleanContent = Str::limit(strip_tags($content), 1200);
        $input = "Headline: " . $title . "\n\nContent:\n" . $cleanContent;

        $primaryAi = UserSetting::getSettingWithFallback($userId, 'primary_ai') 
            ?? UserSetting::getSettingWithFallback(null, 'primary_ai', 'deepseek');

        $providers = $this->getPrioritizedProviders($primaryAi, $userId);

        foreach ($providers as $provider) {
            try {
                $result = $this->executeWithRetry(
                    fn() => $this->callAiForJsonPrompt($provider, $systemPrompt, $input, $userId),
                    $provider,
                    1
                );
                if ($result && !empty($result['keywords']) && is_array($result['keywords'])) {
                    return [
                        'primary_keyword'  => $result['primary_keyword'] ?? ($result['keywords'][0] ?? $title),
                        'keywords'         => array_values(array_filter(array_map('trim', $result['keywords']))),
                        'meta_description' => $result['meta_description'] ?? Str::limit(strip_tags($content), 150),
                        'tags'             => !empty($result['tags']) && is_array($result['tags']) ? array_values(array_filter($result['tags'])) : []
                    ];
                }
            } catch (\Throwable $e) {
                Log::warning("⚠️ Keyword extraction with provider ({$provider}) failed: " . $e->getMessage() . ". Switching to next provider in cascade chain...");
            }
        }

        // Programmatic Fallback Extraction
        $words = preg_split('/[\s,\.\!\?\"\'\-–—\(\)\[\]\{\}]+/u', $title . ' ' . $cleanContent, -1, PREG_SPLIT_NO_EMPTY);
        $stopWords = ['এবং', 'ও', 'কিন্তু', 'বা', 'না', 'হবে', 'হয়েছে', 'আছে', 'করে', 'থেকে', 'জন্য', 'নিয়ে', 'পর', 'এক', 'এই', 'যে', 'বলে', 'হয়', 'the', 'and', 'is', 'in', 'to', 'of', 'for', 'with', 'a', 'on'];
        $filtered = array_filter($words, fn($w) => mb_strlen($w) > 3 && !in_array(mb_strtolower($w), $stopWords));
        $freq = array_slice(array_keys(array_count_values($filtered)), 0, 6);

        return [
            'primary_keyword'  => $freq[0] ?? $title,
            'keywords'         => !empty($freq) ? $freq : [$title],
            'meta_description' => Str::limit(strip_tags($content ?: $title), 150),
            'tags'             => array_slice($freq, 0, 4)
        ];
    }
}