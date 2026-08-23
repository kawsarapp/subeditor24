<?php

namespace App\Modules\SeoIntelligence\Services;

class UtmTrackerService
{
    /**
     * Generate Tracked Social Share Link with UTM Parameters
     */
    public function generateUtmLink(string $url, string $platform): string
    {
        $parsed = parse_url($url);
        $baseUrl = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . ($parsed['path'] ?? '');

        $utmParams = http_build_query([
            'utm_source' => strtolower($platform),
            'utm_medium' => 'social_share',
            'utm_campaign' => 'subeditor24_viral',
        ]);

        return $baseUrl . '?' . $utmParams;
    }
}
