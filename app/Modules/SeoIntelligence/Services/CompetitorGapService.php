<?php

namespace App\Modules\SeoIntelligence\Services;

use App\Modules\SeoIntelligence\Models\SeoWebsite;

class CompetitorGapService
{
    /**
     * Audit Competitor Keyword Gap Analysis
     */
    public function analyzeKeywordGap(SeoWebsite $website, string $competitorDomain): array
    {
        $existingKeywords = $website->keywordMetrics->pluck('keyword')->toArray();

        $allTrendingKeywords = [
            ['keyword' => 'লাইভ বাজেট বুলেটিন ' . date('Y'), 'competitor_pos' => 1, 'vol' => 45000, 'difficulty' => 'Medium'],
            ['keyword' => 'টি-২০ টুর্নামেন্ট তাজা খবর', 'competitor_pos' => 2, 'vol' => 28000, 'difficulty' => 'Low'],
            ['keyword' => 'আবহাওয়া পূর্বাভাস আপডেট', 'competitor_pos' => 1, 'vol' => 62000, 'difficulty' => 'Low'],
            ['keyword' => 'জাতীয় নির্বাচন ফলাফল লাইভ', 'competitor_pos' => 3, 'vol' => 85000, 'difficulty' => 'High'],
        ];

        $missingKeywords = array_values(array_filter($allTrendingKeywords, function ($k) use ($existingKeywords) {
            return !in_array($k['keyword'], $existingKeywords);
        }));

        $missingCount = count($missingKeywords);
        $gapScore = round(max(40, min(100, 100 - ($missingCount * 12))));

        return [
            'target_domain' => $website->domain,
            'competitor_domain' => $competitorDomain,
            'gap_score' => $gapScore,
            'missing_keywords' => $missingKeywords
        ];
    }
}
