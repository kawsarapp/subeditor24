<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\CentralNewsPool;
use App\Jobs\ProcessCentralNewsItem;
use App\Services\NewsScraperService;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Log;

class CentralPoolScraperCommand extends Command
{
    protected $signature = 'news:central-pool-sync {--limit=12 : Max websites to process per batch}';
    protected $description = 'Sync active websites into Central News Pool based on their custom scrape intervals';

    public function handle(NewsScraperService $scraper)
    {
        $limit = (int) $this->option('limit') ?: 12;

        // 1. Fetch eligible websites due for scraping
        $websites = Website::withoutGlobalScopes()
            ->where(function ($q) {
                $q->where('is_central_active', true)
                  ->orWhereNull('is_central_active');
            })
            ->get()
            ->filter(function ($site) {
                if (!$site->last_scraped_at) return true;
                $lastScraped = is_string($site->last_scraped_at) ? \Carbon\Carbon::parse($site->last_scraped_at) : $site->last_scraped_at;
                $interval = (int) ($site->scrape_interval_minutes ?: 5);
                return now()->diffInMinutes($lastScraped) >= $interval;
            })
            ->take($limit);

        if ($websites->isEmpty()) {
            $this->info("⏳ No websites due for central sync right now.");
            return 0;
        }

        $this->info("🚀 [Central Pool Sync] Processing " . $websites->count() . " websites...");

        foreach ($websites as $website) {
            $this->processWebsiteList($website, $scraper);
            $website->update(['last_scraped_at' => now()]);
        }

        $this->info("🏁 [Central Pool Sync] Batch complete.");
        return 0;
    }

    private function processWebsiteList(Website $website, NewsScraperService $scraper)
    {
        $this->line("→ Scanning: {$website->name} ({$website->url})");

        try {
            // Fetch list page HTML using NewsScraperService
            $html = null;

            if ($website->use_scraping_api) {
                $html = $scraper->fetchWithUniversalScrapingApi($website->url, null);
            }

            if (!$html || strlen($html) < 500) {
                $html = $scraper->fetchHtmlWithPython($website->url, null);
            }

            if (!$html || strlen($html) < 500) {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])->timeout(20)->get($website->url);

                if ($response->successful()) {
                    $html = $response->body();
                }
            }

            if (!$html || strlen($html) < 500) {
                $this->warn("⚠️ Empty HTML for {$website->name}, skipping.");
                return;
            }

            $crawler = new Crawler($html);
            $containerSelector = $website->selector_container ?: 'article a, .post a, .news a, .card a, h1 a, h2 a, h3 a, a[href*="/news/"]';
            $titleSelector = $website->selector_title;

            $nodes = $crawler->filter($containerSelector);
            if ($nodes->count() === 0) {
                $nodes = $crawler->filter('h1 a, h2 a, h3 a, .title a, article a');
            }

            $newItemsCount = 0;
            $parsedUrl = parse_url($website->url);
            $scheme = $parsedUrl['scheme'] ?? 'https';
            $baseUrl = $scheme . '://' . ($parsedUrl['host'] ?? '');
            $host = $parsedUrl['host'] ?? '';

            $nodes->each(function (Crawler $node) use ($website, $baseUrl, $scheme, $host, $titleSelector, &$newItemsCount) {
                if ($newItemsCount >= 5) return false;

                $link = null;
                $title = "";

                if ($node->nodeName() === 'a') {
                    $link = $node->attr('href');
                    if ($titleSelector && $node->filter($titleSelector)->count() > 0) {
                        $title = trim($node->filter($titleSelector)->first()->text());
                    } else {
                        $title = trim($node->text());
                    }
                } else {
                    $titleNode = $node->filter($titleSelector ?: 'h2, h3, a');
                    if ($titleNode->count() > 0) {
                        $title = trim($titleNode->first()->text());
                    }
                    if ($node->filter('a')->count() > 0) {
                        $link = $node->filter('a')->first()->attr('href');
                    }
                }

                if (!$link || strlen($title) < 5) return;

                // Fix relative links
                if (str_starts_with($link, '//')) {
                    $link = $scheme . ':' . $link;
                } elseif (!str_starts_with($link, 'http')) {
                    $link = $baseUrl . '/' . ltrim($link, '/');
                }

                // Filter out non-article URLs
                if (rtrim($link, '/') === rtrim($baseUrl, '/') || str_contains($link, '#') || strlen($link) > 700) {
                    return;
                }

                $skipPatterns = ['/category/', '/tag/', '/archive/', '/author/', '/search/', 'facebook.com', 'twitter.com', 'youtube.com'];
                foreach ($skipPatterns as $sp) {
                    if (str_contains($link, $sp)) return;
                }

                $slugHash = CentralNewsPool::generateHash($link);

                // 🔥 O(1) Duplicate Check in Central Pool
                if (CentralNewsPool::where('slug_hash', $slugHash)->exists()) {
                    return; // Already in pool -> skip in 0.001s!
                }

                // Image Extraction
                $listImage = null;
                try {
                    $imgNode = $node->filter('img');
                    if ($imgNode->count() > 0) {
                        $listImage = $imgNode->first()->attr('data-src')
                            ?: ($imgNode->first()->attr('data-original')
                            ?: $imgNode->first()->attr('src'));
                    }
                } catch (\Exception $e) {}

                // Dispatch article ingest
                ProcessCentralNewsItem::dispatch(
                    $link,
                    $title,
                    $website->id,
                    $listImage,
                    $website->name,
                    $host
                );

                $newItemsCount++;
            });

            $this->info("  ✓ Found {$newItemsCount} new news item(s).");

        } catch (\Exception $e) {
            Log::warning("⚠️ Central pool scan error for {$website->name}: " . $e->getMessage());
            $this->error("  ✕ Error: " . $e->getMessage());
        }
    }
}
