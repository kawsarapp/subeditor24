<?php

namespace App\Modules\SeoIntelligence\Services;

use App\Modules\SeoIntelligence\Models\SeoWebsite;

class DiscoverOptimizerService
{
    /**
     * Audit article and domain for Google News & Discover readiness
     */
    public function auditDiscoverReadiness(SeoWebsite $website, string $title): array
    {
        $audits = $website->pageAudits;
        $totalAudits = $audits->count();

        // Check how many pages have max-image-preview:large and og:image
        $missingMaxImagePreviewCount = 0;
        $missingOgImageCount = 0;
        $newsArticleSchemaCount = 0;

        foreach ($audits as $audit) {
            $issues = is_array($audit->issues_found) ? $audit->issues_found : [];
            foreach ($issues as $issue) {
                if (($issue['code'] ?? '') === 'missing_max_image_preview') $missingMaxImagePreviewCount++;
                if (($issue['code'] ?? '') === 'missing_og_image') $missingOgImageCount++;
            }
            if (is_array($audit->schema_detected) && (in_array('NewsArticle', $audit->schema_detected) || in_array('Article', $audit->schema_detected))) {
                $newsArticleSchemaCount++;
            }
        }

        $hasNewsSitemap = !empty($website->sitemap_url);
        $titleLen = mb_strlen($title);

        $hasMaxImageMeta = $totalAudits > 0 ? ($missingMaxImagePreviewCount === 0) : true;
        $hasOgImages = $totalAudits > 0 ? ($missingOgImageCount === 0) : true;

        $score = 100;
        if (!$hasNewsSitemap) $score -= 20;
        if ($missingMaxImagePreviewCount > 0) $score -= 15;
        if ($missingOgImageCount > 0) $score -= 25;
        if ($titleLen < 25 || $titleLen > 75) $score -= 10;

        $score = max(30, min(100, $score));

        $headlineSuggestions = [
            "🔥 ভাইরালের শীর্ষে: {$title}",
            "⚡ ব্রেকিং অনলাইন বুলেটিন: পড়ুন {$title} সংক্রান্ত নতুন তথ্য!",
            "📌 এক্সক্লুসিভ আপডেট: जानिए {$title} নিয়ে সর্বশেষ সিদ্ধান্ত!",
            "🚨 তোলপাড় সৃষ্টি করেছে: {$title} এর ভাইরাল প্রতিবেদন!",
        ];

        return [
            'discover_score' => $score,
            'status' => $score >= 85 ? 'High Discover Potential' : ($score >= 60 ? 'Moderate Discover Potential' : 'Needs Optimization for Discover'),
            'checks' => [
                ['label' => 'Large Image Preview Meta Tag (max-image-preview:large)', 'passed' => $hasMaxImageMeta],
                ['label' => 'Featured Article Image (og:image min 1200px)', 'passed' => $hasOgImages],
                ['label' => 'Google News XML Sitemap Compliance', 'passed' => $hasNewsSitemap],
                ['label' => 'NewsArticle / Article JSON-LD Schema', 'passed' => $newsArticleSchemaCount > 0],
            ],
            'viral_headlines' => $headlineSuggestions
        ];
    }
}
