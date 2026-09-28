<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GoogleTrendsService
{
    /**
     * Cache key for live Google Trends BD search queries
     */
    const CACHE_KEY = 'google_trends_bd_live_keywords';
    const CACHE_TTL = 300; // 5 minutes

    /**
     * Fetch trending search queries and topics from Google Trends BD & Google News BD
     */
    public function getLiveGoogleTrendingKeywords(): array
    {
        try {
            return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
                return $this->fetchDirectGoogleKeywords();
            });
        } catch (\Throwable $cacheErr) {
            return $this->fetchDirectGoogleKeywords();
        }
    }

    /**
     * Fetch direct keywords from Google without cache reliance
     */
    protected function fetchDirectGoogleKeywords(): array
    {
        $keywords = [];

        // 1. Google Trends Daily Search Trends RSS for Bangladesh
        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept'     => 'application/rss+xml, application/xml, text/xml',
            ])->timeout(5)->withoutVerifying()->get('https://trends.google.com/trending/rss?geo=BD');

            if ($resp->successful()) {
                $xml = @simplexml_load_string($resp->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
                if ($xml && isset($xml->channel->item)) {
                    foreach ($xml->channel->item as $item) {
                        $title = trim((string) ($item->title ?? ''));
                        if (!empty($title)) {
                            $keywords[] = mb_strtolower($title);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Google Trends RSS notice: " . $e->getMessage());
        }

        // 2. Google News Bangladesh Top Stories RSS
        try {
            $newsResp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept'     => 'application/rss+xml, application/xml, text/xml',
            ])->timeout(5)->withoutVerifying()->get('https://news.google.com/rss?hl=bn&gl=BD&ceid=BD:bn');

            if ($newsResp->successful()) {
                $newsXml = @simplexml_load_string($newsResp->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
                if ($newsXml && isset($newsXml->channel->item)) {
                    $count = 0;
                    foreach ($newsXml->channel->item as $item) {
                        if ($count >= 15) break;
                        $title = trim((string) ($item->title ?? ''));
                        if (!empty($title)) {
                            $cleanTitle = preg_replace('/(\s*[-|–—]\s*[^–—|-]+)$/u', '', $title);
                            $keywords[] = mb_strtolower(trim($cleanTitle));
                            $count++;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Google News RSS notice: " . $e->getMessage());
        }

        return array_unique(array_filter($keywords));
    }

    /**
     * Check if a news title matches any current Google Search trending keywords
     */
    public function matchGoogleTrends(string $title, array $liveKeywords): bool
    {
        if (empty($liveKeywords)) {
            return false;
        }

        $titleLower = mb_strtolower($title);
        $titleWords = array_filter(explode(' ', preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s]/u', ' ', $titleLower)), fn($w) => mb_strlen($w) >= 3);

        foreach ($liveKeywords as $keyword) {
            // Direct substring match
            if (mb_strpos($titleLower, $keyword) !== false || mb_strpos($keyword, $titleLower) !== false) {
                return true;
            }

            // Word overlap check
            $kwWords = array_filter(explode(' ', preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s]/u', ' ', $keyword)), fn($w) => mb_strlen($w) >= 3);
            $overlap = array_intersect($titleWords, $kwWords);
            if (count($overlap) >= 2) {
                return true;
            }
        }

        return false;
    }
}
