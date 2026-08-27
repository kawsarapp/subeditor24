<?php

namespace App\Modules\SeoIntelligence\Services;

use App\Modules\SeoIntelligence\Models\SeoWebsite;
use App\Modules\SeoIntelligence\Models\SeoKeywordMetric;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GscSyncService
{
    /**
     * Sync Search Console Traffic Keywords & Indexing Status from Google API
     */
    public function syncSearchConsoleData(SeoWebsite $website): array
    {
        $token = $this->getValidGoogleToken($website);
        if (empty($token)) {
            return ['success' => false, 'message' => 'Google Account permission expired or not authenticated. Reconnect Google Account.'];
        }

        $siteUrlsToTry = [
            'sc-domain:' . $website->domain,
            rtrim($website->target_url, '/') . '/',
            $website->target_url
        ];

        $syncedKeywords = 0;
        $totalClicks = 0;
        $totalImpressions = 0;

        foreach ($siteUrlsToTry as $siteUrl) {
            try {
                $apiUrl = 'https://www.googleapis.com/webmasters/v3/sites/' . urlencode($siteUrl) . '/searchAnalytics/query';
                $response = Http::withToken($token)->post($apiUrl, [
                    'startDate' => date('Y-m-d', strtotime('-30 days')),
                    'endDate' => date('Y-m-d'),
                    'dimensions' => ['query', 'page'],
                    'rowLimit' => 25
                ]);

                if ($response->successful() && isset($response->json()['rows'])) {
                    foreach ($response->json()['rows'] as $row) {
                        $query = $row['keys'][0] ?? null;
                        $pageUrl = $row['keys'][1] ?? $website->target_url;
                        $clicks = (int)($row['clicks'] ?? 0);
                        $impressions = (int)($row['impressions'] ?? 0);
                        $position = round((float)($row['position'] ?? 1.0), 1);
                        $ctr = round(((float)($row['ctr'] ?? 0)) * 100, 2);

                        if ($query) {
                            SeoKeywordMetric::updateOrCreate(
                                ['seo_website_id' => $website->id, 'keyword' => $query],
                                [
                                    'target_page_url' => $pageUrl,
                                    'avg_position' => $position,
                                    'clicks' => $clicks,
                                    'impressions' => $impressions,
                                    'ctr' => $ctr,
                                    'is_quick_win' => $position >= 4 && $position <= 15,
                                    'metric_date' => now()->toDateString(),
                                ]
                            );
                            $syncedKeywords++;
                            $totalClicks += $clicks;
                            $totalImpressions += $impressions;
                        }
                    }
                    Log::info("GSC API Sync Success for {$website->domain} via property: {$siteUrl}");
                    break;
                }
            } catch (\Exception $e) {
                Log::warning("GSC API Sync Attempt failed for {$siteUrl}: " . $e->getMessage());
            }
        }

        $audits = $website->pageAudits;
        $totalNewsScanned = $audits->count();
        $indexedNewsCount = $audits->where('is_indexed', true)->count();
        $nonIndexedNewsCount = $totalNewsScanned - $indexedNewsCount;

        return [
            'success' => true,
            'synced_keywords' => $syncedKeywords,
            'total_search_clicks' => $totalClicks,
            'total_search_impressions' => $totalImpressions,
            'total_news_scanned' => $totalNewsScanned,
            'indexed_news_count' => $indexedNewsCount,
            'non_indexed_news_count' => $nonIndexedNewsCount
        ];
    }

    /**
     * Get valid Google Access Token, refreshing via Refresh Token if needed
     */
    protected function getValidGoogleToken(SeoWebsite $website): ?string
    {
        if (!empty($website->google_access_token) && !empty($website->google_refresh_token)) {
            try {
                $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                    'client_id' => env('GOOGLE_CLIENT_ID'),
                    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
                    'refresh_token' => $website->google_refresh_token,
                    'grant_type' => 'refresh_token',
                ]);
                if ($response->successful() && isset($response->json()['access_token'])) {
                    $newToken = $response->json()['access_token'];
                    $website->google_access_token = $newToken;
                    $website->save();
                    return $newToken;
                }
            } catch (\Exception $e) {
                Log::warning("GSC Sync Token Refresh Notice: " . $e->getMessage());
            }
        }

        return $website->google_access_token;
    }
}
