<?php

namespace App\Services;

use App\Models\CentralNewsPool;
use App\Models\NewsItem;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FactCheckService
{
    /**
     * 🔍 Complete Enterprise Fact Check Orchestrator
     *
     * @param string $title
     * @param string $content
     * @param int|null $userId
     * @param string|null $originalContent
     * @return array
     */
    public function verifyNewsArticle(string $title, string $content, ?int $userId = null, ?string $originalContent = null): array
    {
        $cleanTitle = trim(strip_tags($title));
        $cleanContent = trim(strip_tags($content));
        $cleanOriginal = $originalContent ? trim(strip_tags($originalContent)) : '';

        if (empty($cleanTitle) && empty($cleanContent)) {
            return [
                'success' => false,
                'message' => 'যাচাই করার জন্য শিরোনাম বা বিবরণ পাওয়া যায়নি।'
            ];
        }

        // 1. Check Official Google Fact Check Tools ClaimReview Database
        $officialFactChecks = $this->queryGoogleFactCheckApi($cleanTitle, $userId);

        // 2. Cross-reference with Central News Pool (Other newspapers covering this topic)
        $poolContext = $this->searchCentralPoolContext($cleanTitle, $userId);

        // 3. AI Grounded Claim-by-Claim Verification
        $aiVerification = $this->performAiClaimVerification($cleanTitle, $cleanContent, $cleanOriginal, $poolContext, $officialFactChecks, $userId);

        // 4. Merge and calculate final credibility score
        return $this->buildFinalReport($aiVerification, $officialFactChecks, $poolContext);
    }

    /**
     * 🌐 1. Google Fact Check Tools ClaimReview API Query
     */
    public function queryGoogleFactCheckApi(string $query, ?int $userId = null): array
    {
        $apiKey = UserSetting::getSettingWithFallback($userId, 'gemini_api_key') 
            ?? env('GOOGLE_FACT_CHECK_API_KEY') 
            ?? env('GEMINI_API_KEY');

        if (!$apiKey) {
            return [];
        }

        try {
            // Clean search query to extract key terms (max 10 words)
            $searchTerms = Str::limit($query, 120, '');
            
            $url = 'https://factchecktools.googleapis.com/v1alpha1/claims:search';
            $response = Http::timeout(10)->get($url, [
                'query' => $searchTerms,
                'key' => $apiKey,
                'pageSize' => 5,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $claims = $data['claims'] ?? [];
                
                $results = [];
                foreach ($claims as $item) {
                    $claimReview = $item['claimReview'][0] ?? null;
                    if ($claimReview) {
                        $results[] = [
                            'text' => $item['text'] ?? '',
                            'claimant' => $item['claimant'] ?? 'Unknown',
                            'claim_date' => $item['claimDate'] ?? null,
                            'publisher' => $claimReview['publisher']['name'] ?? 'Fact Checker',
                            'publisher_site' => $claimReview['publisher']['site'] ?? '',
                            'rating' => $claimReview['textualRating'] ?? 'Unrated',
                            'review_url' => $claimReview['url'] ?? '',
                            'review_title' => $claimReview['title'] ?? '',
                        ];
                    }
                }
                return $results;
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Google Fact Check API Query Failed: " . $e->getMessage());
        }

        return [];
    }

    /**
     * 📰 2. Cross-reference with Central News Pool for Multi-Outlet Coverage
     */
    private function searchCentralPoolContext(string $title, ?int $userId = null): array
    {
        try {
            // Extract nouns / major keywords from title
            $words = preg_split('/\s+/u', preg_replace('/[^\p{L}\p{N}\s]/u', '', $title));
            $keywords = array_filter($words, function($w) {
                return mb_strlen($w, 'UTF-8') >= 3;
            });
            $keywords = array_slice(array_values($keywords), 0, 4);

            if (empty($keywords)) {
                return [];
            }

            $query = CentralNewsPool::query();
            $query->where(function($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('title', 'LIKE', '%' . $kw . '%');
                }
            });

            $matches = $query->latest('published_at')->limit(3)->get(['source_name', 'title', 'source_url', 'published_at']);

            return $matches->map(function($m) {
                return [
                    'source_name' => $m->source_name,
                    'title' => $m->title,
                    'url' => $m->source_url,
                    'published_at' => $m->published_at ? $m->published_at->toFormattedDateString() : null
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::warning("⚠️ Pool context search failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * 🤖 3. Grounded AI Claim-by-Claim Verification Engine (Zero-Hallucination)
     */
    private function performAiClaimVerification(
        string $title, 
        string $content, 
        string $originalContent, 
        array $poolContext, 
        array $officialFactChecks, 
        ?int $userId = null
    ): array {
        $systemPrompt = <<<EOT
You are an expert **Chief Fact-Checker and Disinformation Auditor** for a prestigious Bangladeshi daily newspaper.
Your task is to conduct a **rigorous, evidence-based, zero-hallucination fact verification** of the given news article.

### 📰 JOURNALISTIC CONTEXT & CREDIBILITY:
- **Mainstream Media Recognition:** You recognize accredited Bangladeshi and international news organizations (e.g. সমকাল / Samakal, প্রথম আলো / Prothom Alo, ডেইলি স্টার / The Daily Star, ইত্তেফাক / Ittefaq, যুগান্তর / Jugantor, কালের কণ্ঠ / Kaler Kantho, বিডিনিউজ২৪ / bdnews24, বাসস / BSS, বিবিসি বাংলা / BBC Bangla, রয়টার্স / Reuters, ইত্যাদি).
- **Standard Journalistic Reporting:** When an article reports real-world events, administrative/court notices, official government releases, press briefings, sports scores, or quotes with journalistic attribution from accredited sources, evaluate the factual claims as `"verified_true"` (🟢 সত্য).
- **Zero-Hallucination & Fair Auditing:** 
  - Mark as `"verified_true"` if facts match real-world knowledge, official public records, or accredited journalistic consensus.
  - Mark as `"partially_true"` if the headline or text exaggerates, leaves out vital context, or is clickbait.
  - Mark as `"unverified"` ONLY if the claim is anonymous, completely uncorroborated, dubious, or unscientific.
  - Mark as `"false"` if it is a known hoax, debunked rumor, or directly contradicts established factual records.

### 🎯 ACCURATE CREDIBILITY SCORING (0-100%):
- **92% - 98%**: All major claims are factually accurate, verified, and sourced from accredited journalism (🟢 সত্য ও প্রমাণিত).
- **82% - 90%**: Strong, reliable news report with standard journalistic attribution (🟢 নির্ভরযোগ্য).
- **55% - 79%**: Contains sensationalism, unverified claims, or missing crucial context (🟡 আংশিক সত্য / সতর্কতা).
- **35% - 54%**: Mostly unverified claims from dubious sources (⚪ যাচাই আবশ্যক).
- **10% - 30%**: Proven false, fabricated, or officially debunked misinformation (🔴 অসত্য / ভুয়া).

### OUTPUT FORMAT (JSON ONLY):
Return ONLY a valid JSON object strictly matching this schema:
{
    "credibility_score": <dynamic integer 0-100 based on claims>,
    "overall_verdict": "verified" | "warning" | "false" | "unverified",
    "verdict_title": "সংক্ষিপ্ত বাংলা রায় (যেমন: খবরটি সম্পূর্ণ তথ্যনির্ভর ও সত্য)",
    "summary_report": "বাংলা ভাষায় ৩-৪ লাইনের বিস্তারিত সাংবাদিক মূল্যায়ন রিপোর্ট।",
    "claims": [
        {
            "claim_text": "দাবির সংক্ষিপ্ত বিবরণ (যেমন: ঘটনার স্থান, তারিখ বা বক্তব্য)",
            "type": "statistical" | "quote" | "event" | "date_location",
            "status": "verified_true" | "partially_true" | "false" | "unverified",
            "confidence": 95,
            "explanation": "বাংলায় স্পষ্ট ব্যাখ্যা কেন এটি সত্য/মিথ্যা/অযাচাইকৃত।",
            "source_hint": "উৎস (যেমন: দৈনিক সমকাল, বাসস, প্রেস ব্রিফিং)",
            "suggested_correction": null
        }
    ],
    "red_flags": [
        "অতিরিক্ত চাঞ্চল্যকর ভাষা বা অস্পষ্টতা থাকলে উল্লেখ করুন"
    ]
}
EOT;

        $userPrompt = "### শিরোনাম:\n{$title}\n\n### বিস্তারিত সংবাদ:\n{$content}\n\n";

        if (!empty($originalContent)) {
            $userPrompt .= "### মূল সোর্স কনটেন্ট (Original Raw):\n" . Str::limit($originalContent, 2000) . "\n\n";
        }

        if (!empty($officialFactChecks)) {
            $userPrompt .= "### অফিশিয়াল ফ্যাক্ট-চেকারদের ডেটাবেজ হিট (Google Fact Check Tools):\n" . json_encode($officialFactChecks, JSON_UNESCAPED_UNICODE) . "\n\n";
        }

        if (!empty($poolContext)) {
            $userPrompt .= "### একই বিষয়ে অন্যান্য পত্রিকার রিপোর্ট:\n" . json_encode($poolContext, JSON_UNESCAPED_UNICODE) . "\n\n";
        }

        // Try Providers in Order
        $settings = $userId ? UserSetting::where('user_id', $userId)->first() : null;
        $primaryAi = ($settings && $settings->primary_ai) ? $settings->primary_ai : 'deepseek';

        $providers = array_unique([$primaryAi, 'deepseek', 'gemini', 'openai', 'groq']);

        foreach ($providers as $provider) {
            try {
                $result = $this->callProviderForFactCheck($provider, $systemPrompt, $userPrompt, $userId);
                if ($result && isset($result['claims']) && is_array($result['claims'])) {
                    return $result;
                }
            } catch (\Exception $e) {
                Log::warning("⚠️ Fact check provider failed ({$provider}): " . $e->getMessage());
            }
        }

        // Deterministic Fallback if AI providers unavailable
        return $this->generateDeterministicReport($title, $content, $officialFactChecks, $poolContext);
    }

    /**
     * 📡 Call AI Provider with Optional Search Grounding (for Gemini)
     */
    private function callProviderForFactCheck(string $provider, string $systemPrompt, string $input, ?int $userId): ?array
    {
        switch ($provider) {
            case 'gemini':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'gemini_api_key') 
                    ?? UserSetting::getSettingWithFallback($userId, 'smartproxy_api_token') 
                    ?? env('GEMINI_API_KEY');
                if (!$apiKey) throw new \Exception("Gemini Key Missing");

                $model = UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? "gemini-1.5-flash";
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                // Gemini with Google Search tool enabled for live grounding
                $payload = [
                    "contents" => [
                        ["parts" => [["text" => $systemPrompt . "\n\n" . $input]]]
                    ],
                    "tools" => [
                        ["google_search" => new \stdClass()]
                    ],
                    "generationConfig" => [
                        "temperature" => 0.2
                    ]
                ];

                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(30)
                    ->post($url, $payload);

                if ($response->successful()) {
                    $raw = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    return $this->parseJsonSafely($raw);
                }
                break;

            case 'deepseek':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? env('DEEPSEEK_API_KEY');
                if (!$apiKey) throw new \Exception("DeepSeek Key Missing");

                $model = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? "deepseek-chat";
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ])->timeout(30)->post("https://api.deepseek.com/chat/completions", [
                    "model" => $model,
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt],
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.2
                ]);

                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return $this->parseJsonSafely($raw);
                }
                break;

            case 'openai':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? env('OPENAI_API_KEY');
                if (!$apiKey) throw new \Exception("OpenAI Key Missing");

                $model = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? "gpt-4o-mini";
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ])->timeout(30)->post("https://api.openai.com/v1/chat/completions", [
                    "model" => $model,
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt],
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.2
                ]);

                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return $this->parseJsonSafely($raw);
                }
                break;

            case 'groq':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'groq_api_key') ?? env('GROQ_API_KEY');
                if (!$apiKey) throw new \Exception("Groq Key Missing");

                $model = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? "llama-3.3-70b-versatile";
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json'
                ])->timeout(30)->post("https://api.groq.com/openai/v1/chat/completions", [
                    "model" => $model,
                    "messages" => [
                        ["role" => "system", "content" => $systemPrompt],
                        ["role" => "user", "content" => $input]
                    ],
                    "response_format" => ["type" => "json_object"],
                    "temperature" => 0.2
                ]);

                if ($response->successful()) {
                    $raw = $response->json()['choices'][0]['message']['content'] ?? null;
                    return $this->parseJsonSafely($raw);
                }
                break;
        }

        return null;
    }

    /**
     * 📊 4. Assemble Final Cohesive Report
     */
    private function buildFinalReport(array $aiVerification, array $officialFactChecks, array $poolContext): array
    {
        $hasOfficialDbHits = !empty($officialFactChecks);
        $officialRatingIsFalse = false;

        foreach ($officialFactChecks as $ofc) {
            $r = strtolower($ofc['rating'] ?? '');
            if (str_contains($r, 'false') || str_contains($r, 'fake') || str_contains($r, 'misleading') || str_contains($r, 'ভুয়া') || str_contains($r, 'মিথ্যা')) {
                $officialRatingIsFalse = true;
                break;
            }
        }

        $claims = $aiVerification['claims'] ?? [];
        $credibilityScore = (int)($aiVerification['credibility_score'] ?? 85);
        $overallVerdict = $aiVerification['overall_verdict'] ?? 'verified';

        if (!empty($claims)) {
            $totalClaims = count($claims);
            $trueCount = 0;
            $partialCount = 0;
            $unverifiedCount = 0;
            $falseCount = 0;

            foreach ($claims as $c) {
                $st = strtolower($c['status'] ?? 'unverified');
                if (str_contains($st, 'true') || $st === 'verified') {
                    $trueCount++;
                } elseif (str_contains($st, 'partial') || str_contains($st, 'warning')) {
                    $partialCount++;
                } elseif (str_contains($st, 'false')) {
                    $falseCount++;
                } else {
                    $unverifiedCount++;
                }
            }

            // Mathematical weighted formula
            $computedScore = round(
                (($trueCount * 100) + ($partialCount * 65) + ($unverifiedCount * 45) + ($falseCount * 10)) / $totalClaims
            );

            // If all claims are true, score is 92-98%
            if ($trueCount === $totalClaims) {
                $credibilityScore = max($computedScore, 95);
                $overallVerdict = 'verified';
            } elseif ($falseCount > 0 && $falseCount >= ($totalClaims / 2)) {
                $credibilityScore = min($computedScore, 25);
                $overallVerdict = 'false';
            } elseif ($falseCount > 0) {
                $credibilityScore = min($computedScore, 45);
                $overallVerdict = 'false';
            } elseif ($trueCount > 0 && $partialCount === 0 && $unverifiedCount === 0) {
                $credibilityScore = max($computedScore, 90);
                $overallVerdict = 'verified';
            } elseif ($trueCount >= $unverifiedCount) {
                $credibilityScore = max($computedScore, 82);
                $overallVerdict = 'verified';
            } else {
                $credibilityScore = $computedScore;
                $overallVerdict = ($credibilityScore < 50) ? 'unverified' : 'warning';
            }
        }

        if ($officialRatingIsFalse) {
            $credibilityScore = min($credibilityScore, 20);
            $overallVerdict = 'false';
        }

        return [
            'success' => true,
            'credibility_score' => $credibilityScore,
            'overall_verdict' => $overallVerdict,
            'verdict_title' => $aiVerification['verdict_title'] ?? ($overallVerdict === 'verified' ? 'তথ্য সম্পূর্ণ সঠিক ও প্রমাণিত' : 'তথ্য পর্যালোচনা সম্পন্ন'),
            'summary_report' => $aiVerification['summary_report'] ?? 'সংবাদের তথ্য যাচাই করা হয়েছে।',
            'claims' => $aiVerification['claims'] ?? [],
            'red_flags' => $aiVerification['red_flags'] ?? [],
            'official_factchecks' => $officialFactChecks,
            'pool_matches' => $poolContext,
            'has_official_debunk' => $officialRatingIsFalse
        ];
    }

    /**
     * 🛡️ Safe Local Fallback when all AI providers fail
     */
    private function generateDeterministicReport(string $title, string $content, array $officialFactChecks, array $poolContext): array
    {
        $hasMatches = !empty($poolContext);
        $score = $hasMatches ? 85 : 70;
        $verdict = $hasMatches ? 'verified' : 'unverified';

        return [
            'credibility_score' => $score,
            'overall_verdict' => $verdict,
            'verdict_title' => $hasMatches ? 'একাধিক সূত্রে সংবাদের মিল পাওয়া গেছে' : 'অযাচাইকৃত তথ্য - নিজস্ব সোর্সে চেক করুন',
            'summary_report' => $hasMatches 
                ? "আমাদের সেন্ট্রাল পুলে থাকা অন্যান্য জাতীয় সংবাদমাধ্যমের রিপোর্টের সাথে এই সংবাদের মূল প্রসঙ্গের সামঞ্জস্য রয়েছে।"
                : "সংবাদটির বিষয়ে ইন্টারনেটে তাৎক্ষণিক পর্যাপ্ত স্বাধীন প্রমাণ মেলেনি। সংবাদের মূল দাবিগুলো নিজস্ব সোর্সে যাচাই করে প্রকাশের পরামর্শ দেওয়া হলো।",
            'claims' => [
                [
                    'claim_text' => Str::limit($title, 80),
                    'type' => 'event',
                    'status' => $hasMatches ? 'verified_true' : 'unverified',
                    'confidence' => $hasMatches ? 85 : 50,
                    'explanation' => $hasMatches ? 'অন্যান্য গণমাধ্যমে একই খবর প্রকাশিত হয়েছে।' : 'কোনো আনুষ্ঠানিক খণ্ডন বা নিশ্চিতকরণ পাওয়া যায়নি।',
                    'source_hint' => $hasMatches ? ($poolContext[0]['source_name'] ?? 'জাতীয় গণমাধ্যম') : 'যাচাই আবশ্যক',
                    'suggested_correction' => null
                ]
            ],
            'red_flags' => []
        ];
    }

    /**
     * 🧹 Clean and safely parse JSON strings
     */
    private function parseJsonSafely(?string $raw): ?array
    {
        if (empty($raw)) return null;

        $raw = preg_replace('/^```json\s*/i', '', trim($raw));
        $raw = preg_replace('/```$/i', '', trim($raw));

        // Find outer curly braces
        $start = strpos($raw, '{');
        $end = strrpos($raw, '}');
        if ($start !== false && $end !== false) {
            $raw = substr($raw, $start, ($end - $start + 1));
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }
}
