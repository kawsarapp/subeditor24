<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsItem;
use App\Services\ViralPredictionEngine;
use App\Services\ExternalLiveTrendsService;
use App\Services\GoogleTrendsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncTrendingSignals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trends:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Precompute, cluster and warm-up all Trending Stories & Topic Signals caches';

    /**
     * Execute the console command.
     */
    public function handle(
        ViralPredictionEngine $viralEngine,
        ExternalLiveTrendsService $externalService,
        GoogleTrendsService $googleTrendsService
    ) {
        $this->info('🔄 [1/3] Syncing live Google Trends BD search queries...');
        Cache::forget(GoogleTrendsService::CACHE_KEY);
        $googleKeywords = $googleTrendsService->getLiveGoogleTrendingKeywords();
        $this->info('✅ Google Trends loaded: ' . count($googleKeywords) . ' trending terms.');

        $this->info('🔄 [2/3] Fetching and clustering External Live Trends from 30+ BD portals...');
        Cache::forget('external_live_trends_cache_v5');
        $externalTrends = $externalService->fetchLiveExternalTrends();
        $this->info('✅ External Live Trends clustered: ' . count($externalTrends) . ' story clusters.');

        $this->info('🔄 [3/3] Precomputing internal database news clusters across all timeframes...');
        $timeframes = ['3', '6', '12', 'all'];

        foreach ($timeframes as $tf) {
            $query = NewsItem::with('website');
            if (in_array($tf, ['3', '6', '12'])) {
                $hours = (int) $tf;
                $cutoff = now()->subHours($hours);
                $query->where(function ($q) use ($cutoff) {
                    $q->where('created_at', '>=', $cutoff)
                      ->orWhere('published_at', '>=', $cutoff);
                });
            }
            $items = $query->latest()->take(60)->get();
            $clusters = $viralEngine->calculateViralPredictions($items);
            Cache::put("trending_internal_cache_{$tf}", $clusters, 300);
            $this->info("   -> Timeframe [{$tf}]: " . count($clusters) . " clusters cached.");
        }

        $this->info('🎉 All Trending Stories & Topic Signals successfully synchronized and cached!');
        return Command::SUCCESS;
    }
}
