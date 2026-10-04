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
                return $lastScraped->lte(now()->subMinutes($interval));
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
            $method = $website->scraper_method; // 'scrape_do', 'decodo', 'curl', 'python', 'node', 'auto'
            $scrapeDoToken = \App\Models\UserSetting::getSettingWithFallback(null, 'scrape_do_token') ?? env('SCRAPE_DO_TOKEN');
            $decodoToken = \App\Models\UserSetting::getSettingWithFallback(null, 'smartproxy_api_token') ?? env('SMARTPROXY_SCRAPING_API_TOKEN');

            $forceApiDomains = [
                'prothomalo.com', 'somoynews.tv', 'bangla.bdnews24.com', 'bdnews24.com', 
                'jamuna.tv', 'kalerkantho.com', 'dawn.com', 'aninews.in', 'thedailystar.net', 
                'starnews.com.bd', 'samakal.com', 'bartabazar.com', 'bd-pratidin.com', 
                'rtvonline.com', 'jagonews24.com', 'dailyamardesh.com', 'itvbd.com', 
                'bvnews24.com', 'dbcnews.tv', 'jugantor.com', 'japantimes.co.jp', 'thediplomat.com'
            ];
            $shouldUseApi = $website->use_scraping_api || collect($forceApiDomains)->some(fn($d) => str_contains($website->url, $d));

            // Fetch list page HTML using selected engine
            $html = null;

            if ($method === 'scrape_do') {
                $html = $scraper->fetchWithScrapeDo($website->url, $scrapeDoToken);
            } elseif ($method === 'decodo') {
                $html = $scraper->fetchWithDecodoApi($website->url, $decodoToken);
            } elseif ($method === 'curl') {
                try {
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    ])->timeout(20)->get($website->url);
                    if ($response->successful()) {
                        $html = $response->body();
                    }
                } catch (\Exception $e) {}
            } elseif ($method === 'python') {
                $html = $scraper->fetchHtmlWithPython($website->url, null);
            } elseif ($method === 'node') {
                $html = $scraper->runPuppeteer($website->url, null);
            } elseif ($shouldUseApi) {
                $html = $scraper->fetchWithUniversalScrapingApi($website->url, null);
            }

            if (!$html || strlen($html) < 500 || \App\Traits\ScraperEnginesTrait::isErrorHtml($html)) {
                $html = $scraper->fetchHtmlWithPython($website->url, null);
            }

            if (!$html || strlen($html) < 500 || \App\Traits\ScraperEnginesTrait::isErrorHtml($html)) {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                ])->timeout(20)->get($website->url);

                if ($response->successful()) {
                    $html = $response->body();
                }
            }

            if (!$html || strlen($html) < 500 || \App\Traits\ScraperEnginesTrait::isErrorHtml($html)) {
                $this->warn("⚠️ Empty or error HTML for {$website->name}, skipping.");
                return;
            }

            $crawler = new Crawler($html);
            $domainConfig = $this->getDomainConfig($website->url);
            $containerSelector = $website->selector_container ?: ($domainConfig['container'] ?? 'article a, .post a, .news a, .card a, h1 a, h2 a, h3 a, a[href*="/news/"], a[href*="/article/"]');
            $titleSelector = $website->selector_title ?: ($domainConfig['title'] ?? null);

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

                if (!$link || strlen($title) < 5 || \App\Traits\ScraperEnginesTrait::isErrorTitleOrText($title)) return;

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

                // Skip social / video links
                $skipDomains = ['facebook.com', 'twitter.com', 'youtube.com', 'instagram.com', 'linkedin.com'];
                foreach ($skipDomains as $sd) {
                    if (str_contains($link, $sd)) return;
                }

                // Skip obvious non-news archive/search/tag pages
                $skipPatterns = ['/archive/', '/author/', '/search/', '/tag/'];
                foreach ($skipPatterns as $sp) {
                    if (str_contains($link, $sp)) return;
                }

                // Skip generic root category links if they don't contain article id / slug
                $cleanPath = trim(parse_url($link, PHP_URL_PATH) ?? '', '/');
                if (!empty($cleanPath)) {
                    $pathSegments = explode('/', $cleanPath);
                    if (count($pathSegments) === 1 && !preg_match('/\d/', $cleanPath) && !str_contains($cleanPath, '-') && strlen($cleanPath) < 20) {
                        return; // Pure root category e.g. /sports, /bangladesh
                    }
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
                            ?: ($imgNode->first()->attr('data-lazy-src')
                            ?: $imgNode->first()->attr('src')));
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

    /**
     * 🔥 Specific portal selectors fallback
     */
    private function getDomainConfig($url)
    {
        if (str_contains($url, 'samakal.com')) {
            return [
                'container' => '.latest-news-list .cat-post-item, .main-ticker a, a[href*="/article/"], .media a, .news-item a, article a, h2 a, h3 a',
                'title'     => 'h4.media-heading a, h3 a, h2 a, .title a'
            ];
        }
        if (str_contains($url, 'jamuna.tv')) {
            return [
                'container' => '.latest-news-list .news-item, .category-news-list a, .recent-news a, article a, h2 a, h3 a',
                'title'     => 'h3.title > a'
            ];
        }
        if (str_contains($url, 'prothomalo.com')) {
            return [
                'container' => '.news_with_item a, .content-area a, .story-card a, .story-data a, .headline-title a, article a',
                'title'     => null
            ];
        }
        if (str_contains($url, 'somoynews.tv')) {
            return [
                'container' => 'a[href*="/news/"], a[href*="/article/"], h2 a, h3 a, .card a, article a',
                'title'     => null
            ];
        }
        if (str_contains($url, 'dhakapost.com')) {
            return [
                'container' => 'a.group, .category-lead a, .section-content a',
                'title'     => 'h2'
            ];
        }
        if (str_contains($url, 'kalerkantho.com')) {
            return [
                'container' => 'div.card h5.card-title a, .col-md-3 a, .col-sm-6 a, h5 a, h4 a, h3 a, h2 a, .card a',
                'title'     => null
            ];
        }
        if (str_contains($url, 'jugantor.com')) {
            return [
                'container' => 'a[href*="jugantor.com/"], a[href*="/national/"], a[href*="/politics/"], .media a, .card a, article a',
                'title'     => null
            ];
        }

        return [
            'container' => 'article a, .post a, .news a, .card a, h1 a, h2 a, h3 a, a[href*="/news/"], a[href*="/article/"]',
            'title'     => null
        ];
    }
}
