<?php

namespace App\Jobs;

use App\Models\Website;
use App\Models\NewsItem;
use App\Services\NewsScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\User;
use App\Notifications\NewsScrapedNotification;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver; 
use Illuminate\Support\Facades\Storage;

class ScrapeWebsite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $websiteId;
    protected $userId;
    public $timeout = 600; 

    public function __construct($websiteId, $userId)
    {
        $this->websiteId = $websiteId;
        $this->userId = $userId;
    }

    public function handle(NewsScraperService $scraper)
    {
        \Illuminate\Support\Facades\Cache::put('scraping_user_' . $this->userId, true, now()->addMinutes(5));

        try {
            $realId = is_array($this->websiteId) ? ($this->websiteId['id'] ?? null) : $this->websiteId;
            $website = Website::withoutGlobalScopes()->find($realId);

            if (!$website) {
                Log::error("❌ Website not found for ID: " . var_export($realId, true));
                $this->logScraperRun($realId, null, 'list', 'failed', 'None', 404, 'Website record not found in database.');
                return;
            }

            Log::info("🚀 JOB STARTED: {$website->name} | URL: {$website->url}");

            // ১. প্রক্সি লোড করা
            $proxy = $scraper->getProxyConfig($this->userId, $website->url);
            if ($proxy) Log::info("🌐 Scraping with Proxy: " . parse_url($proxy, PHP_URL_HOST));

            // 🔥 STRICT SECURITY ENFORCEMENT
            if (!$proxy && !$website->use_scraping_api) {
                if (config('app.env') === 'local') {
                    Log::warning("⚠️ Running on LOCALHOST without Proxy/API. Proceeding directly (DEV MODE).");
                } else {
                    Log::error("❌ Security Block [List]: No Proxy configured AND API disabled. Aborting to protect Hosting Server IP.");
                    $this->logScraperRun($website->id, $website->url, 'list', 'failed', 'None', 403, 'Security Block: No Proxy configured AND Scraping API disabled.');
                    return;
                }
            }

            // ২. লিস্ট পেজ লোড (Raw HTML)
            $listPageHtml = null;

            // 🚀 SmartProxy Universal Scraping API — Enabled per-website from Dashboard
            if ($website->use_scraping_api) {
                Log::info("🔐 Scraping API enabled for [{$website->name}] — using Universal Scraping API.");
                $listPageHtml = $scraper->fetchWithUniversalScrapingApi($website->url, $this->userId);

                // Fallback to Python if API is not configured or fails
                if (!$listPageHtml || strlen($listPageHtml) < 500) {
                    if (!$proxy) {
                        Log::error("❌ Security Block: Universal API failed and no Proxy available. Aborting.");
                        $this->logScraperRun($website->id, $website->url, 'list', 'failed', 'Universal API', 502, 'Universal API failed and no fallback Proxy available.');
                        return;
                    }
                    Log::info("🔄 Universal API failed or unconfigured — falling back to Python/Puppeteer.");
                    $listPageHtml = $scraper->fetchHtmlWithPython($website->url, $this->userId);
                }
                if (!$listPageHtml || strlen($listPageHtml) < 500) {
                    if (!$proxy) {
                        $this->logScraperRun($website->id, $website->url, 'list', 'failed', 'None', 500, 'Universal API returned empty/short HTML and no fallback proxy is configured.');
                        return; // Prevent fallback
                    }
                    $listPageHtml = $scraper->runPuppeteer($website->url, $this->userId);
                }
            } else {
                // 🔥 JS-Rendered সাইটের জন্য সরাসরি Puppeteer ব্যবহার
                // Note: somoynews.tv is removed from here — it requires Universal API (CF-protected Nuxt SSR)
                $jsRenderedDomains = ['ekhon.tv', 'dbcnews.tv', 'banglatribune.com', 'prothomalo.com', 'channel24bd.tv', 'kalerkantho.com'];
                $isJsRendered = $website->scraper_method === 'node' || collect($jsRenderedDomains)->some(fn($d) => str_contains($website->url, $d));

                // 🚀 Force Universal Scraping API for hard CF-protected sites (no dashboard toggle needed)
                $forceApiDomains = ['prothomalo.com', 'somoynews.tv', 'bangla.bdnews24.com', 'jamuna.tv', 'kalerkantho.com', 'dawn.com', 'aninews.in', 'thedailystar.net', 'starnews.com.bd'];
                $forceApi = collect($forceApiDomains)->some(fn($d) => str_contains($website->url, $d));

                if ($forceApi) {
                    Log::info("🔐 Force-API site detected ({$website->url}) — using Universal Scraping API.");
                    $listPageHtml = $scraper->fetchWithUniversalScrapingApi($website->url, $this->userId);
                    if (!$listPageHtml || strlen($listPageHtml) < 500) {
                        Log::warning("⚠️ Universal API failed for force-API site. Trying Puppeteer fallback.");
                        $listPageHtml = $scraper->runPuppeteer($website->url, $this->userId);
                    }
                } elseif ($isJsRendered) {
                    Log::info("🎭 JS-Rendered Site detected. Using Puppeteer directly for list.");
                    $listPageHtml = $scraper->runPuppeteer($website->url, $this->userId);
                } else {
                    try {
                        // ১. Python bypass (curl_cffi) - সবচেয়ে দ্রুত এবং নির্ভরযোগ্য
                        $listPageHtml = $scraper->fetchHtmlWithPython($website->url, $this->userId);

                        // ২. Default Http Facade (যদি পাইথন কাজ না করে)
                        if (!$listPageHtml || strlen($listPageHtml) < 500) {
                            $response = \Illuminate\Support\Facades\Http::withHeaders([
                                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                            ])->withOptions([
                                'proxy' => $proxy,
                                'verify' => false,
                                'connect_timeout' => 20,
                            ])->timeout(60)->get($website->url);

                            if ($response->successful()) {
                                $listPageHtml = $response->body();
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("⚠️ Direct HTTP/Python Failed (Will try Puppeteer): " . $e->getMessage());
                    }

                    // ৩. Puppeteer (Last Resort)
                    if (!$listPageHtml || strlen($listPageHtml) < 500) {
                        Log::info("🔄 Falling back to Puppeteer with Proxy...");
                        $listPageHtml = $scraper->runPuppeteer($website->url, $this->userId);
                    }
                }
            }

            if (!$listPageHtml || strlen($listPageHtml) < 500) {
                Log::error("❌ Failed to load list page content.");
                $this->logScraperRun($website->id, $website->url, 'list', 'failed', null, null, 'Failed to load list page content (empty HTML or less than 500 chars)');
                return;
            }

            $crawler = new Crawler($listPageHtml);

            // ==========================================
            // 🔥 SMART SELECTOR STRATEGY LOOP
            // ==========================================
            
            $strategies = [];

            // ড্যাশবোর্ড vs কোড কনফিগ — প্রায়রিটি নির্ধারণ
            // যদি কোড-কনফিগ থাকে, আগে সেটা চেষ্টা করা হবে (সঠিক selector guaranteed)
            // না থাকলে ড্যাশবোর্ড আগে চেষ্টা হবে (user-defined selector)
            $codeConfig = $this->getDomainConfig($website->url);

            if ($codeConfig) {
                // १. কোড কনফিগ (PRIORITY for known domains)
                $strategies[] = [
                    'source'    => 'CODE (HARDCODED)',
                    'container' => $codeConfig['container'],
                    'title'     => $codeConfig['title']
                ];
            }

            // २. ড্যাশবোর্ড সিলেক্টর (user-defined — runs after code config)
            if (!empty($website->selector_container)) {
                $strategies[] = [
                    'source'    => 'DASHBOARD',
                    'container' => $website->selector_container,
                    'title'     => $website->selector_title
                ];
            }

            // ३. জেনেরিক স্মার্ট সিলেক্টর
            $strategies[] = [
                'source'    => 'GENERIC (SMART)',
                'container' => 'article a, .post a, .news a, h2 a, h3 a', 
                'title'     => null
            ];

            $activeContainer = null;
            $activeTitleSelector = null;
            $foundItems = null;
            $limit = 5;

            // ==========================================
            // 🔥 SPECIAL: Next.js __NEXT_DATA__ JSON Parser
            // For React/Next.js sites that don't put links in static HTML
            // ==========================================
            $nextJsDomains = ['dbcnews.tv'];
            $isNextJsSite = collect($nextJsDomains)->some(fn($d) => str_contains($website->url, $d));

            if ($isNextJsSite && $listPageHtml) {
                $nextDataLinks = $this->extractNextJsLinks($listPageHtml, $website->url);
                if (!empty($nextDataLinks)) {
                    Log::info("✅ Next.js __NEXT_DATA__ Parser: Found " . count($nextDataLinks) . " items.");
                    $count = 0;
                    foreach (array_slice($nextDataLinks, 0, $limit ?? 5) as $item) {
                        if (!empty($item['link']) && !empty($item['title']) && strlen($item['title']) > 5) {
                            Log::info("⚡ Dispatching Job for: " . \Illuminate\Support\Str::limit($item['title'], 30));
                            \App\Jobs\ProcessSingleNews::dispatch($item['link'], $item['title'], $this->userId, $website->id, $item['image'] ?? null);
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        Log::info("🏁 MAIN JOB FINISHED. Queued: {$count} jobs.");
                        \Illuminate\Support\Facades\Cache::forget('scraping_user_' . $this->userId);
                        $website->update(['last_scraped_at' => now()]);
                        $this->logScraperRun($website->id, $website->url, 'list', 'success', 'Next.js Parser', 200, "Successfully scanned list page and queued {$count} news items via Next.js Parser.");
                        return;
                    }
                }
            }

            // ==========================================
            // 🔥 SPECIAL: Quintype / Bold CMS Parser (Prothom Alo & similar)
            // ==========================================
            $isQuintypeSite = str_contains($website->url, 'prothomalo.com') || str_contains($listPageHtml, '"qt":{');
            if ($isQuintypeSite && $listPageHtml) {
                $qtLinks = $this->extractQuintypeLinks($listPageHtml, $website->url);
                if (!empty($qtLinks)) {
                    Log::info("✅ Quintype CMS Parser: Found " . count($qtLinks) . " items for {$website->url}");
                    $count = 0;
                    foreach (array_slice($qtLinks, 0, $limit ?? 5) as $item) {
                        if (!empty($item['link']) && !empty($item['title']) && strlen($item['title']) > 5) {
                            Log::info("⚡ Dispatching Job for: " . \Illuminate\Support\Str::limit($item['title'], 30));
                            \App\Jobs\ProcessSingleNews::dispatch(
                                $item['link'],
                                $item['title'],
                                $this->userId,
                                $website->id,
                                $item['image'] ?? null
                            );
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        Log::info("🏁 MAIN JOB FINISHED. Queued: {$count} jobs via Quintype Parser.");
                        \Illuminate\Support\Facades\Cache::forget('scraping_user_' . $this->userId);
                        $website->update(['last_scraped_at' => now()]);
                        $this->logScraperRun($website->id, $website->url, 'list', 'success', 'Quintype Parser', 200, "Successfully scanned list page and queued {$count} news items via Quintype Parser.");
                        return;
                    }
                }
            }

            foreach ($strategies as $strat) {
                try {
                    $tempItems = $crawler->filter($strat['container']);
                    $count = $tempItems->count();
                } catch (\Symfony\Component\CssSelector\Exception\SyntaxErrorException $e) {
                    Log::warning("⚠️ Selector syntax error [{$strat['source']}]: " . $e->getMessage() . " — Skipping.");
                    continue;
                } catch (\Exception $e) {
                    Log::warning("⚠️ Selector error [{$strat['source']}]: " . $e->getMessage() . " — Skipping.");
                    continue;
                }

                if ($count > 0) {
                    Log::info("✅ Selector Success using [{$strat['source']}]: Found {$count} items.");
                    $activeContainer = $tempItems;
                    $activeTitleSelector = $strat['title'];
                    $foundItems = $count;
                    break; 
                }
            }

            if (!$activeContainer || $foundItems === 0) {
                Log::error("❌ All strategies failed! Could not find any news items.");
                $this->logScraperRun($website->id, $website->url, 'list', 'failed', null, null, 'All selector strategies failed (0 news items found)');
                return;
            }

            $count = 0;
            $limit = 5; // লিমিট

            $activeContainer->each(function (Crawler $node, $i) use ($website, &$count, $limit, $activeTitleSelector, $scraper) {
                
                if ($count >= $limit) return false; 

                try {
                    $title = "";
                    $link = null;

                    // --- LINK & TITLE EXTRACTION LOGIC (PRESERVED FOR ACCURACY) ---
                    if ($node->nodeName() === 'a') {
                        $link = $node->attr('href');
                        
                        // If user provided a specific title selector inside the anchor
                        if ($activeTitleSelector && $node->filter($activeTitleSelector)->count() > 0) {
                            $title = trim($node->filter($activeTitleSelector)->first()->text());
                        } else {
                            $title = trim($node->text());
                            if (empty($title) && $node->filter('h1, h2, h3, h4, h5, h6, span')->count() > 0) {
                                $title = trim($node->filter('h1, h2, h3, h4, h5, h6, span')->first()->text());
                            }
                        }
                    } 
                    else {
                        $titleNode = $node->filter($activeTitleSelector ?? 'h2');
                        if ($titleNode->count() > 0) {
                            $title = trim($titleNode->text());
                            if ($titleNode->nodeName() === 'a') {
                                $link = $titleNode->attr('href');
                            } elseif ($titleNode->filter('a')->count() > 0) {
                                $link = $titleNode->filter('a')->attr('href');
                            }
                        }
                        if (!$link && $node->filter('a')->count() > 0) {
                            $link = $node->filter('a')->first()->attr('href');
                            if (empty($title)) $title = trim($node->text());
                        }
                    }

                    // ভ্যালিডেশন — CSS selector string that leaked as title is rejected
                    $looksLikeCssSelector = preg_match('/^[.#\[\w-]+(\s+[.#\[\w-]+)*$/', trim($title)) && !preg_match('/[\x{0980}-\x{09FF}]/u', $title);
                    if (!$link || strlen($title) < 5 || $looksLikeCssSelector) return;

                    // URL Fix
                    $parsedUrl = parse_url($website->url);
                    $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'https';
                    $baseUrl = $scheme . '://' . $parsedUrl['host'];

                    $lowerLink = strtolower($link);
                    if (str_starts_with($lowerLink, 'javascript:') || str_starts_with($lowerLink, 'tel:') || str_starts_with($lowerLink, 'mailto:') || str_starts_with($lowerLink, 'whatsapp:')) {
                        return;
                    }

                    if (str_starts_with($link, '//')) {
                        $link = $scheme . ':' . $link;
                    } elseif (!str_starts_with($link, 'http')) {
                        $link = $baseUrl . '/' . ltrim($link, '/');
                    }

                    if (strlen($link) > 700) {
                        return;
                    }

                    // 🔥 Skip Homepage / Root URL exactly
                    if (rtrim($link, '/') === rtrim($baseUrl, '/')) {
                        return;
                    }

                    // 🚨 Skip Non-News URLs explicitly
                    $skipPatterns = ['/category/', '/tag/', '/archive/', '/page/', '/author/', '/search/', '/latest-news$', '/recent$', '/live$', '/live/', 'facebook.com', 'twitter.com', 'youtube.com', 'instagram.com', 'linkedin.com'];
                    foreach ($skipPatterns as $pattern) {
                        if (str_ends_with($pattern, '$') ? str_ends_with($link, rtrim($pattern, '$')) : str_contains($link, $pattern)) {
                            return;
                        }
                    }

                    // 🚨 Strict Check (News URLs must contain an ID or Year)
                    if (str_contains($website->url, 'bd-pratidin.com') && !preg_match('/\d{4,}/', $link)) {
                        return;
                    }
                    if (str_contains($website->url, 'ekhon.tv') && !preg_match('/[a-f0-9]{24}$/', parse_url($link, PHP_URL_PATH))) {
                        return;
                    }

                    // 🔥 Smart Validation: Skip pure alphabetic nav categories (e.g. /national, /epaper)
                    $cleanPath = parse_url($link, PHP_URL_PATH) ?? '';
                    $cleanPath = trim($cleanPath, '/');
                    if (!empty($cleanPath)) {
                        $pathSegments = explode('/', $cleanPath);
                        $segmentCount = count($pathSegments);
                        
                        $hasNumbers = preg_match('/\d/', $cleanPath);
                        $hasHyphens = str_contains($cleanPath, '-');

                        // Categories are usually short and don't contain numbers or hyphens (like slugs)
                        if ($segmentCount == 1 && !$hasNumbers && strlen($cleanPath) < 20) {
                            return; // Highly likely a main category like /sports, /national
                        }

                        // 🔥 Exception: BBC article slugs are alphanumeric with no numbers — don't skip them
                        $isBbcUrl = str_contains($website->url, 'bbc.com');
                        if ($segmentCount == 2 && !$hasNumbers && !$hasHyphens && strlen($cleanPath) < 25 && !$isBbcUrl) {
                            return; // e.g. /news/national
                        }
                    }

                    // Duplicate Check (Database এ চেক করে ডিসপ্যাচ এড়ানোর জন্য)
                    if (NewsItem::where('original_link', $link)
                                ->where('user_id', $this->userId)
                                ->exists()) {
                        return; 
                    }

                    // Image Logic (লিস্ট পেজে ইমেজ থাকলে সেটা নিয়ে নেওয়া ভালো)
                    $listImage = null;
                    try {
                        $imgSelector = $website->selector_image ?? 'img';
                        $node->filter($imgSelector)->each(function ($imgNode) use (&$listImage, $scraper) {
                            if ($listImage) return;
                            $src = $imgNode->attr('data-src') ?? $imgNode->attr('data-original') ?? $imgNode->attr('src');
                            if ($src && method_exists($scraper, 'isGarbageImage') && !$scraper->isGarbageImage($src)) {
                                $listImage = $src;
                            } elseif ($src && !method_exists($scraper, 'isGarbageImage')) {
                                $listImage = $src;
                            }
                        });
                    } catch (\Exception $e) {}

                    // ==========================================
                    // 🔥 DISPATCH SINGLE JOB
                    // ==========================================
                    Log::info("⚡ Dispatching Job for: " . Str::limit($title, 30));
                    
                    // আপনার নতুন জবে প্যারামিটার হিসেবে যা যা লাগবে তা পাস করা হলো
                    \App\Jobs\ProcessSingleNews::dispatch(
                        $link, 
                        $title, 
                        $this->userId, 
                        $website->id, 
                        $listImage // অপশনাল: লিস্ট পেজের ইমেজ পাস করলে ভালো
                    );

                    $count++;

                } catch (\Exception $e) {
                    Log::warning("⚠️ Loop Error: " . $e->getMessage());
                }
            });

            Log::info("🏁 MAIN JOB FINISHED. Queued: {$count} jobs.");
            \Illuminate\Support\Facades\Cache::forget('scraping_user_' . $this->userId);
            
            $website->update(['last_scraped_at' => now()]);
            $this->logScraperRun($website->id, $website->url, 'list', 'success', null, null, "Successfully scanned list page and queued {$count} news items.");

            if ($count > 0) {
                $user = \App\Models\User::find($this->userId);
                if ($user) {
                    // $user->notify(new \App\Notifications\NewsScrapedNotification($count)); 
                }
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::forget('scraping_user_' . $this->userId);
            Log::error("🔥 CRITICAL JOB ERROR: " . $e->getMessage());
            
            $realId = is_array($this->websiteId) ? ($this->websiteId['id'] ?? null) : $this->websiteId;
            $this->logScraperRun($realId, null, 'list', 'failed', null, null, 'Critical exception during job run: ' . $e->getMessage());
        }
    }

    /**
     * 🔥 FALLBACK CONFIGURATION
     */
    private function getDomainConfig($url)
    {
        if (str_contains($url, 'jugantor.com')) {
            return ['container' => '#loadMoreContent .col-12, #loadMoreContent .row', 'title' => 'a.text-decoration-none'];
        }
        if (str_contains($url, 'kalerkantho.com')) {
            return ['container' => 'div.card h5.card-title a, .col-md-3 a, .col-sm-6 a, .col-xs-12 a, h5 a, h4 a, h3 a, h2 a, .card a', 'title' => null];
        }
        if (str_contains($url, 'thedailystar.net')) {
            return ['container' => 'h3.card-title a, h3.title a, div.card-presentation, div.card-view', 'title' => null];
        }
        if (str_contains($url, 'jamuna.tv')) {
            return ['container' => '.latest-news-list .news-item, .category-news-list a, .recent-news a, article a, h2 a, h3 a', 'title' => 'h3.title > a'];
        }
        if (str_contains($url, 'dhakapost.com')) {
             return ['container' => 'a.group, .category-lead a, .section-content a', 'title' => 'h2'];
        }
        if (str_contains($url, 'samakal.com')) {
             return ['container' => '.latest-news-list .cat-post-item, .main-ticker a', 'title' => 'h4.media-heading a'];
        }
        if (str_contains($url, 'bartabazar.com')) {
            // bartabazar: articles are at /news/123456/ pattern
            return ['container' => 'a[href*="/news/"]', 'title' => null];
        }
        if (str_contains($url, 'somoynews.tv')) {
            // somoynews is full React — Puppeteer renders it; article links contain /news/
            return ['container' => 'a[href*="/news/"], a[href*="/article/"], h2 a, h3 a, .card a, article a', 'title' => null];
        }
        if (str_contains($url, 'ekhon.tv')) {
            // Ekhon TV (Astro SSR) — articles have /category/hexid paths, list pages have /category/ only
            return ['container' =>
                'main a[href*="/national-news/"], main a[href*="/international/"], ' .
                'main a[href*="/politics/"], main a[href*="/sports/"], ' .
                'main a[href*="/entertainment/"], main a[href*="/economy/"], ' .
                'main a[href*="/crime/"], main a[href*="/education/"], ' .
                'main a[href*="/health/"], main a[href*="/religion-and-belief/"]',
                'title' => null
            ];
        }

        if (str_contains($url, 'dbcnews.tv')) {
            // dbcnews uses Tailwind CSS. We use broad headings and Tailwind grid/typography classes
            return ['container' => 'h2 a, h3 a, h4 a, .text-xl a, .text-lg a, .font-bold a, .col-span-12 a, .col-span-6 a', 'title' => null];
        }
        if (str_contains($url, 'banglatribune.com')) {
            // Bangla Tribune exact article target classes
            return ['container' => '.contents .title_holder a, .listing .title a, .top-news .title a, .feature_news .title a, .list_items h2 a, .story_list h2 a, .more_news_list a', 'title' => null];
        }
        if (str_contains($url, 'bd-pratidin.com')) {
            // Bangladesh Pratidin (stong URL filter allows very broad CSS selector)
            return ['container' => '.col-sm-3 a, .col-sm-4 a, .col-sm-6 a, .col-sm-8 a, .col-md-3 a, .col-md-4 a, .col-md-6 a, .col-md-8 a, .media a, .thumbnail a, .row a, ul li a, article a', 'title' => null];
        }
        if (str_contains($url, 'bdnews24.com')) {
            // bdnews24.com news links are inside SubCat-wrapper and similar grid classes
            return ['container' => '.SubcatList-detail a, .SubCat-wrapper a, .category-wrapper a, .story-content a, h1 a, h2 a, h3 a, h4 a, h5 a, h6 a, .title a, section a', 'title' => null];
        }
        if (str_contains($url, 'prothomalo.com')) {
            // Prothom Alo uses generic <a> tags without semantic classes natively.
            // We capture links via direct category slug structures + fallback classes
            return ['container' => '.news_with_item a, .content-area a, .story-card a, .story-data a, .headline-title a, .card-with-image-zoom a, .contents a, article a', 'title' => null];
        }
        if (str_contains($url, 'npbnews.com')) {
            // npbnews: Dashboard selector was returning CSS text — using hardcoded article selector
            return ['container' => '.all_news_content_block h3 a, .all_news_content_block h2 a, .all_news_content_block .title a, .latest-news-list h3 a, h3.post-title a, article h2 a, article h3 a', 'title' => null];
        }
        if (str_contains($url, 'asia-post.com')) {
            // override the dashboard .col-md-12 which wraps all news instead of individual cards
            return ['container' => '.row a, .col-md-4 a, .col-md-8 a, .col-sm-6 a, .newsList a, article a, h2 a, h3 a', 'title' => null];
        }
        if (str_contains($url, 'itvbd.com')) {
            // itvbd: h2 a gives clean article links with bangla titles
            return ['container' => 'h2 a, h3 a', 'title' => null];
        }
        if (str_contains($url, 'bvnews24.com')) {
            // bvnews24: articles are at /category/news/123456 pattern
            return ['container' => 'a[href*="/news/"]', 'title' => null];
        }
        if (str_contains($url, 'channel24bd.tv')) {
            // override strategies failing
            return ['container' => '.DCategoryListNews a, .DBottomNews a', 'title' => null];
        }
        if (str_contains($url, 'jagonews24.com')) {
            // jagonews24: updated fallback broad selectors
            return ['container' => '.list_content a, .newsList a, article a, h2 a, h3 a, a[href*="/national/"], a[href*="/politics/"], a[href*="/sports/"], a[href*="/international/"]', 'title' => null];
        }
        if (str_contains($url, 'bbc.com/bengali')) {
            // BBC Bengali: article URLs follow /bengali/articles/<alphanumeric-id> or /bengali/live/<id>
            return ['container' => 'a[href*="/bengali/articles/"], a[href*="/bengali/live/"]', 'title' => null];
        }
        if (str_contains($url, 'dawn.com')) {
            // Dawn News: article links are wrapped in article.story
            return ['container' => 'article.story', 'title' => 'h2.story__title'];
        }
        if (str_contains($url, 'aninews.in')) {
            // ANI News: article links are wrapped in div.card
            return ['container' => 'div.card', 'title' => 'h1.title'];
        }
        return null;
    }

    /**
     * 🔥 Extract article links from Next.js __NEXT_DATA__ JSON
     * For sites like dbcnews.tv and ekhon.tv that use React SSR/SSG
     */
    private function extractNextJsLinks($html, $baseUrl): array
    {
        $links = [];
        $host = parse_url($baseUrl, PHP_URL_SCHEME) . '://' . parse_url($baseUrl, PHP_URL_HOST);

        // Extract __NEXT_DATA__ JSON
        if (!preg_match('/<script id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $matches)) {
            return [];
        }

        $json = json_decode($matches[1], true);
        if (!$json) return [];

        // Recursively walk all strings in the JSON and find URL-like values with news slugs
        $allStrings = [];
        array_walk_recursive($json, function($val, $key) use (&$allStrings) {
            if (is_string($val) && strlen($val) > 10) {
                $allStrings[] = ['key' => $key, 'val' => $val];
            }
        });

        // Find potential article titles and slugs
        $slugPattern = '/^\/[a-z-]+\/[a-z0-9-]+(\?.*)?$/'; // /category/article-slug
        $banglaPattern = '/[\x{0980}-\x{09FF}]/u';
        
        $titleMap = [];
        foreach ($allStrings as $item) {
            if (in_array($item['key'], ['title', 'headline', 'name', 'heading'])) {
                $titleMap[] = $item['val'];
            }
        }

        $i = 0;
        foreach ($allStrings as $item) {
            if (in_array($item['key'], ['slug', 'url', 'href', 'link', 'path', 'permalink'])) {
                $slug = $item['val'];
                if (!str_starts_with($slug, 'http')) {
                    $slug = $host . '/' . ltrim($slug, '/');
                }
                $title = $titleMap[$i] ?? '';
                if ($title && preg_match($banglaPattern, $title)) {
                    $links[] = ['link' => $slug, 'title' => $title];
                    $i++;
                }
                if (count($links) >= 15) break;
            }
        }

        return $links;
    }

    /**
     * 🔥 Extract article links from Quintype (Bold CMS) JSON
     * For sites like prothomalo.com that store articles in {"qt": ...} script tag
     */
    private function extractQuintypeLinks($html, $baseUrl): array
    {
        $links = [];
        $startPos = strpos($html, '{"qt":{');
        if ($startPos === false) return [];

        $endPos = strpos($html, '</script>', $startPos);
        if ($endPos === false) return [];

        $jsonStr = trim(substr($html, $startPos, $endPos - $startPos));
        $data = json_decode($jsonStr, true);
        if (!$data || empty($data['qt'])) return [];

        $cdnHost = $data['qt']['config']['cdn-image'] ?? 'media.prothomalo.com';
        if (!str_starts_with($cdnHost, 'http')) {
            $cdnHost = 'https://' . $cdnHost;
        }

        $allStories = [];
        $walk = function ($obj) use (&$walk, &$allStories) {
            if (is_array($obj)) {
                if (isset($obj['headline']) && (isset($obj['url']) || isset($obj['slug']))) {
                    $allStories[] = $obj;
                }
                foreach ($obj as $val) {
                    if (is_array($val)) $walk($val);
                }
            }
        };
        $walk($data['qt']);

        $seen = [];
        foreach ($allStories as $story) {
            $title = trim($story['headline'] ?? '');
            if (empty($title) || strlen($title) < 5 || isset($seen[$title])) continue;
            $seen[$title] = true;

            $link = $story['url'] ?? null;
            if (!$link && !empty($story['slug'])) {
                $link = 'https://www.prothomalo.com/' . ltrim($story['slug'], '/');
            }
            if (!$link) continue;

            $image = null;
            if (!empty($story['hero-image-s3-key'])) {
                $image = rtrim($cdnHost, '/') . '/' . ltrim($story['hero-image-s3-key'], '/');
            } elseif (!empty($story['hero-image-url'])) {
                $image = $story['hero-image-url'];
            }

            $links[] = [
                'link'  => $link,
                'title' => $title,
                'image' => $image,
            ];

            if (count($links) >= 15) break;
        }

        return $links;
    }

    private function logScraperRun($websiteId, $url, $jobType, $status, $strategy = null, $httpStatus = null, $errorMessage = null, $retryCount = 0)
    {
        try {
            \App\Models\ScraperLog::create([
                'website_id'    => $websiteId,
                'url'           => $url,
                'job_type'      => $jobType,
                'status'        => $status,
                'strategy'      => $strategy,
                'http_status'   => $httpStatus,
                'error_message' => $errorMessage,
                'retry_count'   => $retryCount,
                'created_at'    => now(),
            ]);
        } catch (\Exception $e) {
            Log::warning("⚠️ Failed to write scraper log to DB: " . $e->getMessage());
        }
    }
}