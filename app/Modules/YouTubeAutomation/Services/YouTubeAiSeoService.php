<?php

namespace App\Modules\YouTubeAutomation\Services;

use App\Models\UserSetting;
use App\Modules\YouTubeAutomation\Models\YouTubeChannel;
use App\Modules\YouTubeAutomation\Models\YouTubeVideo;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeAiSeoService
{
    /**
     * Optimize video SEO: Title, Description, Tags, Hashtags, and Chapters.
     */
    public function optimizeVideo(YouTubeVideo $video): array
    {
        $channel = $video->channel;
        $userId = $video->user_id;

        $targetLanguage = $channel->default_language ?? 'bn';
        $titleStyle = $channel->title_style ?? 'viral_curiosity';
        $footerTemplate = $channel->custom_description_footer ?? '';
        $customTags = $channel->custom_tags_template ?? '';
        $customPrompt = $channel->custom_ai_prompt ?? '';

        $systemPrompt = $this->buildSystemPrompt($targetLanguage, $titleStyle, $customPrompt);
        $userContext = $this->buildUserContext($video, $targetLanguage);

        $response = $this->callAiProvider($systemPrompt, $userContext, $userId);

        // 1. Title Optimization (Toggle Check)
        if ($channel->opt_title !== false) {
            $mainTitle = $response['best_title'] ?? ($response['title_variations'][0] ?? $video->original_title);
            $mainTitle = mb_substr(trim($mainTitle), 0, 100, 'UTF-8');
            $titleVariations = $response['title_variations'] ?? [$mainTitle];
        } else {
            $mainTitle = $video->original_title;
            $titleVariations = [$video->original_title];
        }

        // 2. Description Optimization (Toggle Check)
        if ($channel->opt_description !== false) {
            $description = $response['description'] ?? ($video->original_description ?: '');
        } else {
            $description = $video->original_description ?: '';
        }

        // Append Footer (Toggle Check)
        if ($channel->append_footer !== false && !empty($footerTemplate)) {
            $description .= "\n\n" . trim($footerTemplate);
        }
        $description = mb_substr(trim($description), 0, 5000, 'UTF-8');

        // 3. Tags Optimization (Toggle Check)
        if ($channel->opt_tags !== false) {
            $tags = $response['tags'] ?? [];
            if (!is_array($tags)) {
                $tags = array_map('trim', explode(',', (string) $tags));
            }
        } else {
            $tags = $video->original_tags ?? [];
        }

        // Merge Fixed Brand Tags (Toggle Check)
        if ($channel->merge_brand_tags !== false && !empty($customTags)) {
            $extra = array_map('trim', explode(',', $customTags));
            $tags = array_unique(array_merge($tags, $extra));
        }

        // Ensure total tags string fits within YouTube 500-char limit
        $finalTags = [];
        $charCount = 0;
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if (empty($tag)) continue;
            if ($charCount + mb_strlen($tag, 'UTF-8') + 1 > 480) break;
            $finalTags[] = $tag;
            $charCount += mb_strlen($tag, 'UTF-8') + 1;
        }

        // 4. Hashtags (Toggle Check)
        $hashtags = ($channel->opt_hashtags !== false) ? ($response['hashtags'] ?? []) : [];
        if (!is_array($hashtags)) {
            $hashtags = array_map('trim', explode(' ', (string) $hashtags));
        }

        // 5. Chapters (Toggle Check)
        $chapters = ($channel->opt_chapters !== false) ? ($response['chapters'] ?? []) : [];

        // 6. Thumbnail Hook Ideas (Toggle Check)
        $thumbnailIdeas = ($channel->opt_thumbnail_ideas !== false) ? ($response['thumbnail_ideas'] ?? []) : [];

        // 7. Pinned Engagement Comment (Toggle Check)
        $pinnedComment = ($channel->opt_pinned_comment !== false) ? ($response['pinned_comment'] ?? null) : null;

        // 8. Search Intent Keywords
        $searchIntentKeywords = $response['search_intent_keywords'] ?? [];

        // Calculate SEO Score
        $seoScore = $this->calculateSeoScore($mainTitle, $description, $finalTags, $hashtags);

        return [
            'ai_title'                  => $mainTitle,
            'ai_title_variations'       => $titleVariations,
            'ai_thumbnail_ideas'        => $thumbnailIdeas,
            'ai_description'            => $description,
            'ai_tags'                   => $finalTags,
            'ai_hashtags'               => $hashtags,
            'ai_chapters'               => $chapters,
            'ai_pinned_comment'         => $pinnedComment,
            'ai_search_intent_keywords' => $searchIntentKeywords,
            'seo_score'                 => $seoScore,
        ];
    }

    private function buildSystemPrompt(string $language, string $titleStyle, ?string $customPrompt = null): string
    {
        $langName = $language === 'bn' ? 'Bengali (বাংলা)' : ($language === 'hi' ? 'Hindi' : 'English');

        $customSection = !empty(trim($customPrompt ?? ''))
            ? "\n### USER CUSTOM INSTRUCTIONS & RULES:\n" . trim($customPrompt) . "\n"
            : "";

        return <<<EOT
You are an Elite YouTube Growth Strategist, Search Algorithm Engineer & Master Video SEO Copywriter.
Your goal is to optimize a YouTube video for **Maximum Search Ranking (VSEO), High Click-Through Rate (CTR), and Viral Audience Engagement**.

Language Requirement: **{$langName}**
Title Strategy: **{$titleStyle}**
{$customSection}
CRITICAL SEO & RANKING RULES:
1. **Title (Max 100 characters):**
   - High curiosity, urgent, or high-intent search hook.
   - Naturally include the primary search keyword in the first 50 characters.
   - Dual-Language Search Intent: Provide title variations in natural Bengali and hybrid (Bengali + English high-search terms) so both Bengali & English-script searchers discover the video.
   - Provide 3 distinct title variations (Viral/Curiosity, Direct/Breaking, SEO Search Query).
   - Select the single best title.

2. **Thumbnail Text Ideas (CTR Booster):**
   - Provide 2 to 4 ultra-punchy, 2-4 word bold text ideas specifically designed to be written on the video thumbnail image (e.g. "আসল সত্য ফাঁস!", "কী ঘটল?", "বিস্ফোরক তথ্য!", "Shocking Truth!").

3. **Description (Max 4500 characters):**
   - **First 3 Lines (Above the fold):** High-converting search summary naturally embedding 2-3 target keywords (crucial for YouTube/Google search snippets).
   - **Body Breakdown:** Comprehensive, engaging overview of the video script / events with bullet points and clear context.
   - **Key Timestamps / Chapters:** Extract main topical shifts with timestamps (e.g. 00:00 Intro, 01:20 Main Event, etc.) if script or context provides them.
   - **Hashtags:** 3 to 5 targeted viral hashtags at the bottom (e.g. #BanglaNews #BreakingNews).

4. **High-Ranking Search Tags (CRITICAL - Max 460 total characters combined):**
   - **DO NOT provide generic or single useless words** (like "news", "bangla", "video").
   - Provide **15 to 25 real, high-volume search phrases & queries** that viewers actually type in the YouTube search bar.
   - Mix:
     1. Exact Match Primary Keyword Phrases (e.g. "xyz news today", "xyz live update").
     2. Long-Tail Search Intent Queries (e.g. "xyz ki holo", "xyz latest viral video").
     3. Common misspellings and transliterations in English & Bengali (e.g. "bangla news", "bangladesh latest update").
     4. Competitor Search Topics.
   - Keep total combined characters under 460 chars so it easily fits within YouTube's 500-character limit.

5. **Pinned Engagement Comment:**
   - Write a high-converting pinned comment with an intriguing question or call-to-action (CTA) asking the audience for their opinion or reaction to trigger rapid comment velocity.

OUTPUT FORMAT:
Return ONLY a valid JSON object matching this schema without any markdown code fences or conversational text:
{
    "best_title": "Best YouTube Title Here",
    "title_variations": [
        "Option 1: Viral & Curiosity Hook",
        "Option 2: Direct Breaking News Style",
        "Option 3: SEO Search & Question Style"
    ],
    "thumbnail_ideas": [
        "আসল সত্য ফাঁস!",
        "বিস্ফোরক তথ্য!",
        "কী ঘটল?"
    ],
    "description": "Rich structured YouTube description with hook, summary, and hashtags...",
    "tags": ["high volume search query 1", "exact match phrase 2", "long tail search tag 3", "keyword phrase 4"],
    "hashtags": ["#Topic1", "#Topic2", "#Topic3"],
    "chapters": [
        {"time": "00:00", "title": "Introduction"},
        {"time": "01:15", "title": "Main Event"}
    ],
    "pinned_comment": "আপনার কি মনে হয় এই বিষয়ে? আপনার মতামত নিচে কমেন্ট করে জানান! 👇",
    "search_intent_keywords": ["keyword 1", "keyword 2", "keyword 3"]
}
EOT;
    }

    private function buildUserContext(YouTubeVideo $video, string $language): string
    {
        $title = $video->original_title;
        $desc = $video->original_description ?: 'No initial description provided.';
        $rawTags = !empty($video->original_tags) ? implode(', ', $video->original_tags) : 'None';
        
        $scriptContext = '';
        if (!empty($video->video_script)) {
            $safeScript = mb_substr(strip_tags($video->video_script), 0, 8000, 'UTF-8');
            $scriptContext = "\n\n### FULL VIDEO SCRIPT / TRANSCRIPT / TALKING POINTS:\n" . $safeScript;
        }

        return "### VIDEO ORIGINAL DRAFT METADATA:\n"
            . "- Draft Title: {$title}\n"
            . "- Draft Description: {$desc}\n"
            . "- Initial Tags: {$rawTags}"
            . $scriptContext
            . "\n\nPlease extract key facts, quotes, timeline, and generate the ultimate Search-Ranked YouTube SEO Package in {$language}.";
    }

    private function callAiProvider(string $systemPrompt, string $userContext, int $userId): array
    {
        $settings = UserSetting::where('user_id', $userId)->first();
        $primaryAi = $settings?->primary_ai ?? 'deepseek';

        $providers = [
            'deepseek' => fn() => $this->callDeepSeek($systemPrompt, $userContext, $userId),
            'gemini'   => fn() => $this->callGemini($systemPrompt, $userContext, $userId),
            'openai'   => fn() => $this->callOpenAI($systemPrompt, $userContext, $userId),
            'groq'     => fn() => $this->callGroq($systemPrompt, $userContext, $userId),
        ];

        $order = [$primaryAi];
        foreach (['deepseek', 'gemini', 'openai', 'groq'] as $p) {
            if ($p !== $primaryAi) $order[] = $p;
        }

        $lastException = null;
        foreach ($order as $provider) {
            if (!isset($providers[$provider])) continue;
            try {
                return $providers[$provider]();
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning("YouTube AI SEO Provider {$provider} failed: " . $e->getMessage());
            }
        }

        throw new \Exception("All AI Providers failed for YouTube SEO: " . ($lastException ? $lastException->getMessage() : 'Unknown error'));
    }

    private function callDeepSeek(string $systemPrompt, string $userContext, int $userId): array
    {
        $apiKey = UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') 
            ?? config('services.deepseek.key') 
            ?? env('DEEPSEEK_API_KEY');

        if (!$apiKey) throw new \Exception('DeepSeek API Key Missing');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(45)->post('https://api.deepseek.com/chat/completions', [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContext],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        return $this->parseResponse($response, 'DeepSeek');
    }

    private function callGemini(string $systemPrompt, string $userContext, int $userId): array
    {
        $apiKey = UserSetting::getSettingWithFallback($userId, 'gemini_api_key') 
            ?? config('services.gemini.key') 
            ?? env('GEMINI_API_KEY');

        if (!$apiKey) throw new \Exception('Gemini API Key Missing');

        $models = ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-2.0-flash'];

        foreach ($models as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(40)
                    ->post($url, [
                        'contents' => [[
                            'parts' => [['text' => $systemPrompt . "\n\n" . $userContext]]
                        ]],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json',
                            'temperature'      => 0.7,
                        ],
                    ]);

                if ($response->successful()) {
                    $raw = $response->json('candidates.0.content.parts.0.text');
                    return $this->processJsonString($raw, 'Gemini');
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        throw new \Exception('Gemini API call failed');
    }

    private function callOpenAI(string $systemPrompt, string $userContext, int $userId): array
    {
        $apiKey = UserSetting::getSettingWithFallback($userId, 'openai_api_key') 
            ?? config('services.openai.key') 
            ?? env('OPENAI_API_KEY');

        if (!$apiKey) throw new \Exception('OpenAI API Key Missing');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(45)->post('https://api.openai.com/v1/chat/completions', [
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContext],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        return $this->parseResponse($response, 'OpenAI');
    }

    private function callGroq(string $systemPrompt, string $userContext, int $userId): array
    {
        $apiKey = UserSetting::getSettingWithFallback($userId, 'groq_api_key') 
            ?? env('GROQ_API_KEY');

        if (!$apiKey) throw new \Exception('Groq API Key Missing');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->timeout(40)->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userContext],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature' => 0.7,
        ]);

        return $this->parseResponse($response, 'Groq');
    }

    private function parseResponse($response, string $provider): array
    {
        if (!$response->successful()) {
            throw new \Exception("{$provider} API error: " . $response->status());
        }

        $raw = $response->json('choices.0.message.content');
        return $this->processJsonString($raw, $provider);
    }

    private function processJsonString(?string $raw, string $provider): array
    {
        if (empty($raw)) {
            throw new \Exception("{$provider} returned empty response");
        }

        $clean = trim($raw);
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $clean, $m)) {
            $clean = trim($m[1]);
        } elseif (preg_match('/```\s*([\s\S]*?)\s*```/', $clean, $m)) {
            $clean = trim($m[1]);
        }

        $json = json_decode($clean, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            return $json;
        }

        // Advanced Regex Recovery
        if (preg_match('/"best_title"\s*:\s*"([^"\r\n]+)"/u', $clean, $tm)) {
            $title = trim($tm[1]);
            $desc = '';
            if (preg_match('/"description"\s*:\s*"(.*?)(?=",\s*"tags"|\}\s*$)/su', $clean, $dm)) {
                $desc = stripcslashes(trim($dm[1]));
            }
            return [
                'best_title'       => $title,
                'title_variations' => [$title],
                'description'      => $desc,
                'tags'             => [],
                'hashtags'         => [],
                'chapters'         => [],
            ];
        }

        throw new \Exception("{$provider} returned invalid JSON format");
    }

    private function calculateSeoScore(string $title, string $desc, array $tags, array $hashtags): int
    {
        $score = 40; // Base score

        // Title score (Length 40 to 80 chars is optimal for YouTube CTR)
        $tLen = mb_strlen($title, 'UTF-8');
        if ($tLen >= 35 && $tLen <= 85) $score += 20;
        elseif ($tLen > 15) $score += 10;

        // Description score (300+ chars)
        $dLen = mb_strlen($desc, 'UTF-8');
        if ($dLen >= 300) $score += 20;
        elseif ($dLen >= 100) $score += 10;

        // Tags score (8+ tags)
        if (count($tags) >= 10) $score += 10;
        elseif (count($tags) >= 5) $score += 5;

        // Hashtags score (3-5 hashtags)
        if (count($hashtags) >= 3) $score += 10;
        elseif (count($hashtags) >= 1) $score += 5;

        return min(100, $score);
    }
}
