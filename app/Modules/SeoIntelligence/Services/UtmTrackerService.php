<?php

namespace App\Modules\SeoIntelligence\Services;

class UtmTrackerService
{
    /**
     * Generate Tracked Social Share Link with UTM Parameters (Preserves Existing Query Strings)
     */
    public function generateUtmLink(string $url, string $platform): string
    {
        $parsed = parse_url(trim($url));
        if (!$parsed || !isset($parsed['host'])) {
            return $url;
        }

        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'];
        $path = $parsed['path'] ?? '/';

        $existingParams = [];
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $existingParams);
        }

        $utmParams = [
            'utm_source' => strtolower(trim($platform)),
            'utm_medium' => 'social_share',
            'utm_campaign' => 'subeditor24_viral',
        ];

        $mergedParams = array_merge($existingParams, $utmParams);
        $queryString = http_build_query($mergedParams);

        return "{$scheme}://{$host}{$path}?{$queryString}";
    }
}
