<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

// 🔥 Traits Import
use App\Traits\ScraperEnginesTrait;
use App\Traits\ScraperHtmlParserTrait;
use App\Traits\ScraperHelperTrait;

class NewsScraperService
{
    use ScraperEnginesTrait, ScraperHtmlParserTrait, ScraperHelperTrait;

    public function scrape($url, $customSelectors = [], $userId = null)
    {
        $proxy = $this->getProxyConfig($userId, $url);
        
        $host = parse_url($url, PHP_URL_HOST);
        if ($host) {
            $host = str_replace('www.', '', $host);
            $website = \App\Models\Website::withoutGlobalScopes()->where('url', 'like', '%' . $host . '%')->first();
        } else {
            $website = null;
        }
        $method = $website ? $website->scraper_method : null; // 'scrape_do', 'decodo', 'curl', 'python', 'node', 'auto'
        $useApi = $website ? (bool) $website->use_scraping_api : false;
        
        if ($method === 'scrape_do' || $method === 'decodo') {
            $useApi = true;
        }

        // 🔥 Force Universal API for CF-protected Nuxt/React SSR sites regardless of dashboard toggle
        $forceApiDomains = [
            'prothomalo.com', 'somoynews.tv', 'bangla.bdnews24.com', 'bdnews24.com', 
            'jamuna.tv', 'kalerkantho.com', 'dawn.com', 'aninews.in', 'thedailystar.net', 
            'starnews.com.bd', 'samakal.com', 'bartabazar.com', 'bd-pratidin.com', 
            'rtvonline.com', 'jagonews24.com', 'dailyamardesh.com', 'itvbd.com', 
            'bvnews24.com', 'dbcnews.tv', 'jugantor.com', 'japantimes.co.jp', 'thediplomat.com'
        ];
        if (!$useApi && $method !== 'curl' && collect($forceApiDomains)->some(fn($d) => str_contains($url, $d))) {
            Log::info("🔐 Force-API domain detected ($url) — overriding to Universal Scraping API.");
            $useApi = true;
        }

        // 🔥 STRICT SECURITY ENFORCEMENT
        if (!$proxy && !$useApi && $method !== 'curl') {
            if (config('app.env') === 'local') {
                Log::warning("⚠️ Running on LOCALHOST without Proxy/API. Proceeding directly (DEV MODE).");
            } else {
                Log::error("❌ Security Block [Article]: No Proxy configured AND API disabled. Aborting to protect Hosting Server IP.");
                $this->logScraperRun($website?->id, $url, 'article', 'failed', 'None', 403, 'Security Block: No Proxy/API configured in production.');
                return null;
            }
        }

        $proxyLog = $proxy ? parse_url($proxy, PHP_URL_HOST) : ($method ?: "Universal API");
        Log::info("🚀 START SCRAPE: $url | Engine: " . ($method ?: 'Auto') . " | via $proxyLog");

        $lastError = 'Unknown error';
        $httpStatus = null;
        $phpRetries = 0;

        // 🌟 STEP 0: SPECIFIC API OR UNIVERSAL SCRAPING API (If enabled)
        $htmlContent = null;
        if ($method === 'scrape_do') {
            $scrapeDoToken = \App\Models\UserSetting::getSettingWithFallback($userId, 'scrape_do_token') ?? env('SCRAPE_DO_TOKEN');
            Log::info("🚀 Using Scrape.do API specifically for article body.");
            $htmlContent = $this->fetchWithScrapeDo($url, $scrapeDoToken);
        } elseif ($method === 'decodo') {
            $decodoToken = \App\Models\UserSetting::getSettingWithFallback($userId, 'smartproxy_api_token') ?? env('SMARTPROXY_SCRAPING_API_TOKEN');
            Log::info("🌐 Using Decodo API specifically for article body.");
            $htmlContent = $this->fetchWithDecodoApi($url, $decodoToken);
        } elseif ($useApi) {
            Log::info("🔐 Using Universal Scraping API for article body.");
            $htmlContent = $this->fetchWithUniversalScrapingApi($url, $userId);
        }

        if ($htmlContent && strlen($htmlContent) > 500 && !self::isErrorHtml($htmlContent)) {
            $scrapedData = $this->processHtml($htmlContent, $url, $customSelectors);
            
            if (!empty($scrapedData) && !empty($scrapedData['body'])) {
                if (isset($scrapedData['image'])) {
                    $scrapedData['image'] = $this->fixVendorImages($scrapedData['image']);
                }
                if (isset($scrapedData['title'])) {
                    $scrapedData['title'] = $this->cleanTitle($scrapedData['title']);
                }

                if (self::isErrorTitleOrText($scrapedData['title'] ?? '') || self::isErrorTitleOrText($scrapedData['body'] ?? '')) {
                    Log::warning("⚠️ Processed article contains error text/title. Discarding: $url");
                } else {
                    $this->logScraperRun($website?->id, $url, 'article', 'success', $method ?: 'Universal API', 200, null, 0);
                    return $scrapedData;
                }
            }
            $lastError = "API fetched HTML, but empty or error body parsed.";
            Log::warning("⚠️ API fetched HTML, but parser returned empty/error body. Falling back...");
        }

        // If specific engine was node (Puppeteer)
        if ($method === 'node') {
            Log::info("🤖 Using Node.js (Puppeteer) engine as specified.");
            $puppeteerData = $this->scrapeWithPuppeteer($url, $customSelectors, $userId);
            if ($puppeteerData && !empty($puppeteerData['body'])) {
                $this->logScraperRun($website?->id, $url, 'article', 'success', 'Puppeteer', 200, null, 0);
                return $puppeteerData;
            }
        }

        // If specific engine was curl (Direct cURL)
        if ($method === 'curl') {
            Log::info("⚡ Using Direct cURL engine as specified.");
            try {
                $httpRequest = Http::withHeaders($this->getRealBrowserHeaders())
                    ->timeout(20)
                    ->withOptions(['verify' => false, 'connect_timeout' => 10]);
                if ($proxy) $httpRequest->withOptions(['proxy' => $proxy]);
                $response = $httpRequest->get($url);
                if ($response->successful()) {
                    $html = $response->body();
                    if ($html && strlen($html) > 500) {
                        $scrapedData = $this->processHtml($html, $url, $customSelectors);
                        if (!empty($scrapedData) && !empty($scrapedData['body'])) {
                            if (isset($scrapedData['image'])) $scrapedData['image'] = $this->fixVendorImages($scrapedData['image']);
                            if (isset($scrapedData['title'])) $scrapedData['title'] = $this->cleanTitle($scrapedData['title']);
                            $this->logScraperRun($website?->id, $url, 'article', 'success', 'Direct cURL', 200, null, 0);
                            return $scrapedData;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning("⚠️ Direct cURL engine error: " . $e->getMessage());
            }
        }

        $hardSites = ['jamuna.tv', 'kalerkantho.com', 'somoynews.tv', 'dailyamardesh.com', 'samakal.com', 'bartabazar.com'];
        $isHardSite = false;
        foreach ($hardSites as $site) {
            if (str_contains($url, $site)) {
                $isHardSite = true;
                break;
            }
        }

        if (!$proxy) {
            if (config('app.env') === 'local') {
                // Log::warning("⚠️ Running on LOCALHOST without Proxy/API. Proceeding directly (DEV MODE).");
            } else {
                Log::error("❌ Security Block [Article Fallback]: Universal API failed and NO PROXY available. Aborting instead of leaking Hosting Server IP.");
                $this->logScraperRun($website?->id, $url, 'article', 'failed', 'None', 403, 'Security Block: Fallback to direct download blocked.');
                return null;
            }
        }

        // 🐍 STEP 1: PYTHON SCRAPER
        $pythonData = $this->runPythonScraper($url, $userId);

        if ($pythonData && !empty($pythonData['body'])) {
            Log::info("✅ Python Scraper Success");
            $this->logScraperRun($website?->id, $url, 'article', 'success', 'Python Scraper', 200, null, 0);
            return [
                'title'      => $this->cleanTitle($pythonData['title'] ?? null), // 🔥 Title Cleaned
                'image'      => $this->fixVendorImages($pythonData['image'] ?? null), // 🔥 Vendor Image Fixed
                'body'       => $this->cleanHtml($pythonData['body']), 
                'source_url' => $url
            ];
        }

        $lastError = "Python Scraper failed to retrieve content.";
        Log::info("⚠️ Python failed. Checking fallback strategy...");

        // 🐘 STEP 2: PHP HTTP REQUEST
        $htmlContent = null;

        if (!$isHardSite) {
            try {
                $htmlContent = retry(2, function () use ($url, $proxy, &$httpStatus, &$phpRetries) {
                    $phpRetries++;
                    $timeout = $proxy ? 20 : 15; 
                    $httpRequest = Http::withHeaders($this->getRealBrowserHeaders())
                        ->timeout($timeout)
                        ->withOptions(['verify' => false, 'connect_timeout' => 10]);

                    if ($proxy) $httpRequest->withOptions(['proxy' => $proxy]);

                    $response = $httpRequest->get($url);
                    $httpStatus = $response->status();
                    if ($response->successful()) return $response->body();
                    
                    throw new \Exception("HTTP Status: " . $response->status());
                }, 3000);
                
            } catch (\Exception $e) {
                $lastError = "PHP HTTP Failed after {$phpRetries} retries: " . $e->getMessage();
                Log::warning($lastError);
            }
        } else {
            $lastError = "Skipped PHP HTTP for CF-protected site.";
            Log::info("🛡️ Skipping PHP fallback for Hard Site (Cloudflare protected).");
        }

        // 🤖 STEP 3: PUPPETEER (Last Resort)
        if (empty($htmlContent) || str_contains($htmlContent, 'Just a moment') || strlen($htmlContent) < 600) {
            Log::info("🔄 All Fast Methods Failed. Engaging Puppeteer Engine...");
            $puppeteerData = $this->scrapeWithPuppeteer($url, $customSelectors, $userId);
            if ($puppeteerData && !empty($puppeteerData['body'])) {
                $this->logScraperRun($website?->id, $url, 'article', 'success', 'Puppeteer', 200, null, $phpRetries);
                return $puppeteerData;
            }
            $lastError = "All scraping strategies (Universal API, Python, PHP HTTP, Puppeteer) failed.";
        }

        // 4️⃣ FINAL PROCESSING
        if ($htmlContent && strlen($htmlContent) > 500 && !self::isErrorHtml($htmlContent)) {
            $scrapedData = $this->processHtml($htmlContent, $url, $customSelectors);
            
            // 🔥 Image Cleaned Here
            if (isset($scrapedData['image'])) {
                $scrapedData['image'] = $this->fixVendorImages($scrapedData['image']);
            }
            
            // 🔥 Title Cleaned Here
            if (isset($scrapedData['title'])) {
                $scrapedData['title'] = $this->cleanTitle($scrapedData['title']);
            }
            
            if (empty($scrapedData['title']) || empty($scrapedData['body']) || self::isErrorTitleOrText($scrapedData['title']) || self::isErrorTitleOrText($scrapedData['body'])) {
                 Log::warning("⚠️ Content Parsing Failed or contained error text. Retrying with Puppeteer...");
                 $puppeteerData = $this->scrapeWithPuppeteer($url, $customSelectors, $userId);
                 if ($puppeteerData && !empty($puppeteerData['body']) && !self::isErrorTitleOrText($puppeteerData['title'] ?? '') && !self::isErrorTitleOrText($puppeteerData['body'])) {
                     $this->logScraperRun($website?->id, $url, 'article', 'success', 'Puppeteer (after parser fail)', 200, null, $phpRetries);
                     return $puppeteerData;
                 }
                 $lastError = "HTML parser output was empty or error page, Puppeteer fallback also failed.";
            } else {
                 $this->logScraperRun($website?->id, $url, 'article', 'success', 'PHP HTTP', 200, null, $phpRetries);
                 return $scrapedData;
            }
        }

        Log::error("❌ CRITICAL: Scrape totally failed for: $url");
        $this->logScraperRun($website?->id, $url, 'article', 'failed', 'None', $httpStatus, $lastError, $phpRetries);
        return null;
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