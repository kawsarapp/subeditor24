<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\UserSetting;

trait ScraperEnginesTrait
{
    /**
     * Check if a proxy or scraping API provider is currently in cooldown.
     */
    public static function isProviderOnCooldown(string $provider): bool
    {
        return (bool) Cache::get("proxy_provider_cooldown_{$provider}", false);
    }

    /**
     * Put a provider on temporary cooldown due to quota exhaustion, rate limiting, or auth failure.
     */
    public static function markProviderCooldown(string $provider, int $minutes = 5, string $reason = ''): void
    {
        Cache::put("proxy_provider_cooldown_{$provider}", true, now()->addMinutes($minutes));
        Log::warning("⛔ Provider [{$provider}] placed on {$minutes}-minute cooldown. Reason: " . ($reason ?: 'Quota exhausted / Rate limited / Auth failure'));
    }

    /**
     * Clear provider cooldown if manually reset.
     */
    public static function clearProviderCooldown(string $provider): void
    {
        Cache::forget("proxy_provider_cooldown_{$provider}");
    }

    /**
     * Detect quota exhaustion, rate-limiting, or proxy authentication errors.
     */
    public static function isQuotaOrRateLimitError(int $status, string $body = ''): bool
    {
        // Direct quota / rate-limiting / proxy auth error HTTP status codes
        if (in_array($status, [429, 402, 407])) {
            return true;
        }

        // 401 Unauthorized or 403 Forbidden with quota / credit / auth / limit indicators
        if (in_array($status, [401, 403])) {
            if (empty($body)) {
                return true;
            }
            $lowerBody = strtolower($body);
            $quotaSignatures = [
                'quota',
                'credit',
                'balance',
                'bandwidth',
                'limit exceeded',
                'limit reached',
                'rate limit',
                'ratelimit',
                'too many requests',
                'request limit',
                'insufficient',
                'exhausted',
                'suspended',
                'payment required',
                'unauthorized',
                'forbidden',
                'invalid token',
                'invalid api key',
                'plan expired',
                'concurrency limit',
                'account blocked',
            ];
            foreach ($quotaSignatures as $sig) {
                if (str_contains($lowerBody, $sig)) {
                    return true;
                }
            }
        }

        // 502 / 503 / 504 server or proxy capacity exhaustion
        if (in_array($status, [502, 503, 504])) {
            $lowerBody = strtolower($body);
            if (str_contains($lowerBody, 'quota') || str_contains($lowerBody, 'rate limit') || str_contains($lowerBody, 'overloaded') || str_contains($lowerBody, 'capacity')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Safe realistic desktop browser headers for direct HTTP requests.
     */
    public static function getSafeDesktopHeaders(): array
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:125.0) Gecko/20100101 Firefox/125.0',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36 Edg/124.0.0.0'
        ];

        return [
            'User-Agent' => $userAgents[array_rand($userAgents)],
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'bn-BD,bn;q=0.9,en-US;q=0.8,en;q=0.7',
            'Upgrade-Insecure-Requests' => '1',
            'Cache-Control' => 'max-age=0',
            'Sec-Ch-Ua' => '"Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Sec-Fetch-Dest' => 'document',
            'Sec-Fetch-Mode' => 'navigate',
            'Sec-Fetch-Site' => 'none',
            'Sec-Fetch-User' => '?1',
            'Connection' => 'keep-alive'
        ];
    }

    /**
     * Retrieve standard rotating proxy configuration.
     * Ensures proxy configuration is fetched from Super Admin settings.
     * If standard rotating proxy is throttled/in cooldown, returns null to allow fallback.
     */
    public function getProxyConfig($userId = null, $url = null)
    {
        // If standard rotating proxy is throttled / on cooldown, return null to trigger fallback
        if (self::isProviderOnCooldown('rotating_proxy')) {
            Log::warning("⚠️ Standard rotating proxy is currently throttled/in cooldown. Falling back to alternative scraping routing.");
            return null;
        }

        $uid = null;
        if ($userId) {
            $uid = $userId;
        } elseif (Auth::check()) {
            $user = Auth::user();
            $uid = in_array($user->role, ['staff', 'reporter']) ? $user->parent_id : $user->id;
        }

        $proxyHost = null;
        $proxyPort = null;
        $proxyUser = null;
        $proxyPass = null;

        // 1. Fetch user-specific settings or fallback to Super Admin from database
        try {
            $proxyHost = UserSetting::getSettingWithFallback($uid, 'proxy_host');
            $proxyPort = UserSetting::getSettingWithFallback($uid, 'proxy_port');
            $proxyUser = UserSetting::getSettingWithFallback($uid, 'proxy_username');
            $proxyPass = UserSetting::getSettingWithFallback($uid, 'proxy_password');

            // Explicitly ensure Super Admin proxy is checked if not resolved
            if (!$proxyHost || !$proxyPort) {
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $saSetting = UserSetting::where('user_id', $superAdmin->id)->first();
                    if ($saSetting && !empty($saSetting->proxy_host) && !empty($saSetting->proxy_port)) {
                        $proxyHost = $saSetting->proxy_host;
                        $proxyPort = $saSetting->proxy_port;
                        $proxyUser = $saSetting->proxy_username;
                        $proxyPass = $saSetting->proxy_password;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("⚠️ Error retrieving proxy settings: " . $e->getMessage());
        }

        if ($proxyHost && $proxyPort) {
            $auth = ($proxyUser && $proxyPass) ? "{$proxyUser}:{$proxyPass}@" : "";
            return "http://{$auth}{$proxyHost}:{$proxyPort}";
        }

        // 2. Fallback to .env proxy (for automated cron jobs or unconfigured admins)
        $envHost = env('SMARTPROXY_HOST') ?: env('PROXY_HOST');
        $envPort = env('SMARTPROXY_PORT') ?: env('PROXY_PORT');
        if ($envHost && $envPort) {
            $envUser = env('SMARTPROXY_USER') ?: env('PROXY_USER');
            $envPass = env('SMARTPROXY_PASS') ?: env('PROXY_PASS');
            $auth = ($envUser && $envPass) ? "{$envUser}:{$envPass}@" : "";
            return "http://{$auth}{$envHost}:{$envPort}";
        }

        return null;
    }

    /**
     * Resilient fetcher: Attempts standard rotating proxy first.
     * If standard rotating proxy fails or is throttled, automatically falls back
     * to universal scraping APIs (scrape_do / smartproxy) or direct HTTP with safe desktop headers.
     */
    public function fetchWithProxyOrFallback(string $url, $userId = null): ?string
    {
        $proxy = $this->getProxyConfig($userId, $url);

        // 1. Try rotating proxy if available and not throttled
        if ($proxy) {
            $html = $this->fetchHtmlWithProxy($url, $proxy);
            if ($html && strlen($html) > 500 && !self::isErrorHtml($html)) {
                return $html;
            }
            Log::warning("⚠️ Rotating proxy failed for {$url}. Falling back to Universal Scraping APIs / Direct HTTP...");
        }

        // 2. Fallback to Universal Scraping API (scrape_do / smartproxy with multi-provider failover)
        $apiHtml = $this->fetchWithUniversalScrapingApi($url, $userId);
        if ($apiHtml && strlen($apiHtml) > 500 && !self::isErrorHtml($apiHtml)) {
            return $apiHtml;
        }

        // 3. Fallback to Direct HTTP with safe desktop headers
        Log::info("🌐 Falling back to Direct HTTP with safe desktop headers for: {$url}");
        return $this->fetchHtmlDirect($url);
    }

    /**
     * Fetch HTML using HTTP client with proxy and realistic 15s timeout.
     */
    public function fetchHtmlWithProxy(string $url, string $proxy): ?string
    {
        if (self::isProviderOnCooldown('rotating_proxy')) {
            return null;
        }

        try {
            $cleanUrl = str_replace(' ', '%20', trim($url));
            $response = Http::withHeaders(self::getSafeDesktopHeaders())
                ->timeout(15)
                ->connectTimeout(5)
                ->withOptions([
                    'verify' => false,
                    'proxy'  => $proxy,
                ])
                ->get($cleanUrl);

            $status = $response->status();
            $body = $response->body();

            if (self::isQuotaOrRateLimitError($status, $body)) {
                self::markProviderCooldown('rotating_proxy', 5, "Proxy HTTP {$status} rate limit/auth error");
                return null;
            }

            if ($response->successful() && strlen($body) > 500 && !self::isErrorHtml($body)) {
                return $body;
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("⚠️ Rotating proxy connect timeout (15s limit reached): " . $e->getMessage());
            self::markProviderCooldown('rotating_proxy', 3, "Connect timeout");
        } catch (\Throwable $e) {
            Log::warning("⚠️ Rotating proxy HTTP exception: " . $e->getMessage());
            if (str_contains($e->getMessage(), '407') || str_contains($e->getMessage(), 'Failed to connect')) {
                self::markProviderCooldown('rotating_proxy', 5, $e->getMessage());
            }
        }

        return null;
    }

    /**
     * Fetch HTML directly using safe desktop headers and realistic 15s timeout.
     */
    public function fetchHtmlDirect(string $url): ?string
    {
        try {
            $cleanUrl = str_replace(' ', '%20', trim($url));
            $response = Http::withHeaders(self::getSafeDesktopHeaders())
                ->timeout(15)
                ->connectTimeout(5)
                ->withOptions(['verify' => false])
                ->get($cleanUrl);

            $body = $response->body();
            if ($response->successful() && strlen($body) > 500 && !self::isErrorHtml($body)) {
                return $body;
            }
        } catch (\Throwable $e) {
            Log::warning("⚠️ Direct HTTP exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * 🛡️ Comprehensive Error HTML Detector
     * Detects Chrome error pages, Cloudflare challenge/blocks, Nginx/Apache 4xx/5xx pages, DNS failures, etc.
     */
    public static function isErrorHtml($html): bool
    {
        if (empty($html) || !is_string($html)) return true;
        if (strlen(trim($html)) < 300) return true;

        $lower = strtolower($html);
        $errorSignatures = [
            "this site can't be reached",
            "this site can’t be reached",
            "this site cannot be reached",
            "site can't be reached",
            "site can’t be reached",
            "err_name_not_resolved",
            "err_connection_timed_out",
            "err_connection_refused",
            "err_connection_closed",
            "err_connection_reset",
            "err_tunnel_connection_failed",
            "err_ssl_protocol_error",
            "err_too_many_redirects",
            "err_empty_response",
            "err_timed_out",
            "err_address_unreachable",
            "err_internet_disconnected",
            "dns_probe_finished",
            "dns_probe_finished_nxdomain",
            "attention required! | cloudflare",
            "just a moment...",
            "checking your browser",
            "enable javascript and cookies to continue",
            "cloudflare ray id",
            "web server is down",
            "the page cannot be found",
            "request blocked",
            "access denied",
            "403 forbidden",
        ];

        foreach ($errorSignatures as $signature) {
            if (str_contains($lower, $signature)) {
                return true;
            }
        }

        // Check Chrome / Cloudflare / Nginx title error patterns
        if (preg_match('/<title[^>]*>(.*?)(403 forbidden|access denied|404 not found|502 bad gateway|503 service unavailable|504 gateway|this site can|just a moment|attention required)(.*?)<\/title>/i', $html)) {
            return true;
        }

        return false;
    }

    /**
     * 🛡️ Comprehensive Error Title & Body Text Detector
     */
    public static function isErrorTitleOrText($text): bool
    {
        if (empty($text) || !is_string($text)) return false;
        $lower = strtolower(trim($text));

        $errorPhrases = [
            "this site can't be reached",
            "this site can’t be reached",
            "this site cannot be reached",
            "site can't be reached",
            "site can’t be reached",
            "err_name_not_resolved",
            "err_connection_timed_out",
            "err_connection_refused",
            "err_connection_closed",
            "err_connection_reset",
            "err_tunnel_connection_failed",
            "err_ssl_protocol_error",
            "err_too_many_redirects",
            "err_empty_response",
            "err_timed_out",
            "err_address_unreachable",
            "err_internet_disconnected",
            "dns_probe_finished",
            "403 forbidden",
            "401 unauthorized",
            "404 not found",
            "500 internal server error",
            "502 bad gateway",
            "503 service unavailable",
            "504 gateway time-out",
            "504 gateway timeout",
            "access denied",
            "attention required! | cloudflare",
            "attention required",
            "just a moment...",
            "just a moment",
            "checking your browser",
            "enable javascript and cookies to continue",
            "cloudflare ray id",
            "ray id:",
            "web server is down",
            "the page cannot be found",
            "temporarily unavailable",
            "request blocked",
            "untitled news",
            "welcome to nginx",
            "default webpage",
            "apache2 default",
        ];

        foreach ($errorPhrases as $phrase) {
            if (str_contains($lower, $phrase)) {
                return true;
            }
        }

        return false;
    }

    public function runPythonScraper($url, $userId = null)
    {
        $cleanUrl = trim($url);
        $proxy = $this->getProxyConfig($userId, $cleanUrl);
        $scriptPath = base_path("scraper.py"); 
        if (!file_exists($scriptPath)) return null;

        $pythonCmd = env('PYTHON_PATH'); 
        if (!$pythonCmd) {
            $pythonCmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
        }

        $command = "$pythonCmd " . escapeshellarg($scriptPath) . " " . escapeshellarg($cleanUrl);
        if ($proxy) $command .= " " . escapeshellarg($proxy);
        $command .= " 2>&1";

        $output = shell_exec($command);

        // Detect proxy errors in Python output
        if ($proxy && $output && (str_contains($output, 'ProxyError') || str_contains($output, '407') || str_contains($output, 'Proxy Authentication') || str_contains($output, 'Proxy CONNECT aborted'))) {
            self::markProviderCooldown('rotating_proxy', 5, 'Python scraper proxy error');
        }

        $data = json_decode($output, true);
        
        if (json_last_error() === JSON_ERROR_NONE && isset($data['body'])) {
            if (self::isErrorTitleOrText($data['title'] ?? '') || self::isErrorTitleOrText($data['body'] ?? '')) {
                Log::warning("⚠️ Python scraper returned error title or body. Discarding: $cleanUrl");
                return null;
            }
            return $data;
        }

        return null;
    }

    public function fetchHtmlWithPython($url, $userId = null)
    {
        $cleanUrl = trim($url);
        $proxy = $this->getProxyConfig($userId, $cleanUrl);
        $scriptPath = base_path("fetch_list.py"); 
        if (!file_exists($scriptPath)) return null;

        $pythonCmd = env('PYTHON_PATH'); 
        if (!$pythonCmd) {
            $pythonCmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
        }

        $command = "$pythonCmd " . escapeshellarg($scriptPath) . " " . escapeshellarg($cleanUrl);
        if ($proxy) $command .= " " . escapeshellarg($proxy);
        $command .= " 2>&1";

        Log::info("🔄 Fetching List HTML with Python curl_cffi...");
        $output = shell_exec($command);

        // Detect proxy errors in Python output
        if ($proxy && $output && (str_contains($output, 'ProxyError') || str_contains($output, '407') || str_contains($output, 'Proxy Authentication') || str_contains($output, 'Proxy CONNECT aborted'))) {
            self::markProviderCooldown('rotating_proxy', 5, 'Python curl_cffi proxy error');
        }
        
        if ($output && strlen($output) > 500 && !self::isErrorHtml($output)) {
            return $output;
        }

        if ($output && self::isErrorHtml($output)) {
            Log::warning("⚠️ Python curl_cffi returned Error/Blocked HTML. Skipping...");
        }

        return null;
    }

    protected function scrapeWithPuppeteer($url, $customSelectors, $userId)
    {
        $htmlContent = $this->runPuppeteer($url, $userId);

        if ($htmlContent && strlen($htmlContent) > 500 && !self::isErrorHtml($htmlContent)) {
            $scrapedData = $this->processHtml($htmlContent, $url, $customSelectors);
            if (isset($scrapedData['image'])) {
                $scrapedData['image'] = $this->fixVendorImages($scrapedData['image']);
            }
            return $scrapedData;
        }
        return null;
    }

    public function runPuppeteer($url, $userId = null)
    {
        $cleanUrl = trim($url);
        $proxy = $this->getProxyConfig($userId, $cleanUrl);
        $scriptPath = base_path("scraper-engine.js");
        if (!file_exists($scriptPath)) {
            Log::warning("⚠️ Puppeteer SKIPPED: scraper-engine.js not found at " . base_path("scraper-engine.js"));
            return null;
        }

        $tempFile = storage_path("app/public/temp_" . uniqid() . "_" . rand(1000,9999) . ".html");
        $nodeCmd = env('NODE_PATH') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'node' : 'node');

        $command = "$nodeCmd " . escapeshellarg($scriptPath) . " " . escapeshellarg($cleanUrl) . " " . escapeshellarg($tempFile) . " " . escapeshellarg($proxy ?? '') . " 2>&1";
        
        Log::info("🔄 Engaging Node (Puppeteer) Engine...");
        $rawOutput = shell_exec($command);

        // Detect proxy errors in Puppeteer output
        if ($proxy && $rawOutput && (str_contains($rawOutput, 'ERR_TUNNEL_CONNECTION_FAILED') || str_contains($rawOutput, 'ERR_PROXY_AUTH_REQUESTED') || str_contains($rawOutput, 'ERR_PROXY_CONNECTION_FAILED'))) {
            self::markProviderCooldown('rotating_proxy', 5, 'Puppeteer proxy connection failure');
        }
        
        $htmlContent = null;
        if (file_exists($tempFile)) {
            $htmlContent = file_get_contents($tempFile);
            unlink($tempFile);
        } else {
            Log::warning("⚠️ Puppeteer: No output file created for [$cleanUrl]. Raw stderr: " . substr($rawOutput ?? 'empty/null', 0, 800));
        }

        if ($htmlContent && self::isErrorHtml($htmlContent)) {
            Log::warning("⚠️ Puppeteer returned Error/Blocked Page. Skipping...");
            return null;
        }
        
        return ($htmlContent && strlen($htmlContent) > 500) ? $htmlContent : null;
    }

    /**
     * 🚀 Universal Scraping API (Scrape.do & Decodo/SmartProxy)
     * Used for hard-blocked sites (Cloudflare, Datadome, Akamai, Turnstile).
     * Offloads rendering, proxy rotation, and CAPTCHA solving to cloud scrapers.
     * Features quota exhaustion detection, automatic cooldown, and multi-provider failover.
     */
    public function fetchWithUniversalScrapingApi($url, $userId = null)
    {
        $cleanUrl = str_replace(' ', '%20', trim($url));

        // 1. Fetch provider configuration with Super Admin fallback
        $preferredProvider = null;
        $scrapeDoToken = null;
        $decodoToken = null;

        try {
            $preferredProvider = UserSetting::getSettingWithFallback($userId, 'scraping_api_provider') ?? 'scrape_do';
            $scrapeDoToken = UserSetting::getSettingWithFallback($userId, 'scrape_do_token');
            $decodoToken = UserSetting::getSettingWithFallback($userId, 'smartproxy_api_token');

            // Explicit Super Admin fallback if tokens not resolved
            if (empty($scrapeDoToken) || empty($decodoToken)) {
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $saSettings = UserSetting::where('user_id', $superAdmin->id)->first();
                    if ($saSettings) {
                        $scrapeDoToken = $scrapeDoToken ?: $saSettings->scrape_do_token;
                        $decodoToken = $decodoToken ?: $saSettings->smartproxy_api_token;
                        if (!$preferredProvider && $saSettings->scraping_api_provider) {
                            $preferredProvider = $saSettings->scraping_api_provider;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("⚠️ Error fetching scraping API settings: " . $e->getMessage());
        }

        $preferredProvider = $preferredProvider ?: 'scrape_do';
        $scrapeDoToken = $scrapeDoToken ?: env('SCRAPE_DO_TOKEN');
        $decodoToken = $decodoToken ?: (env('SMARTPROXY_SCRAPING_API_TOKEN') ?: env('SMARTPROXY_TOKEN'));

        // 2. Build prioritized provider list based on preferences & cooldown status
        $providers = [];
        if ($preferredProvider === 'decodo' || $preferredProvider === 'smartproxy') {
            $providers = ['smartproxy', 'scrape_do'];
        } else {
            $providers = ['scrape_do', 'smartproxy'];
        }

        // 3. Try each Universal Scraping API provider with automatic failover
        foreach ($providers as $provider) {
            if ($provider === 'scrape_do') {
                if (empty($scrapeDoToken)) {
                    continue;
                }
                if (self::isProviderOnCooldown('scrape_do')) {
                    Log::warning("⚠️ Scrape.do is currently in cooldown. Skipping to alternative provider for: {$cleanUrl}");
                    continue;
                }

                $html = $this->fetchWithScrapeDo($cleanUrl, $scrapeDoToken);
                if ($html && strlen($html) > 500 && !self::isErrorHtml($html)) {
                    return $html;
                }
                Log::warning("⚠️ Scrape.do failed for {$cleanUrl}. Checking next provider in failover chain...");
            } elseif ($provider === 'smartproxy') {
                if (empty($decodoToken)) {
                    continue;
                }
                if (self::isProviderOnCooldown('smartproxy') || self::isProviderOnCooldown('decodo')) {
                    Log::warning("⚠️ Smartproxy/Decodo is currently in cooldown. Skipping to alternative provider for: {$cleanUrl}");
                    continue;
                }

                $html = $this->fetchWithDecodoApi($cleanUrl, $decodoToken);
                if ($html && strlen($html) > 500 && !self::isErrorHtml($html)) {
                    return $html;
                }
                Log::warning("⚠️ Smartproxy/Decodo API failed for {$cleanUrl}. Checking next provider in failover chain...");
            }
        }

        // 4. Failover: Rotating Proxy
        $proxy = $this->getProxyConfig($userId, $cleanUrl);
        if ($proxy && !self::isProviderOnCooldown('rotating_proxy')) {
            Log::info("🔄 Universal Scraping APIs failed/cooled down. Failing over to Rotating Proxy for: {$cleanUrl}");
            $html = $this->fetchHtmlWithProxy($cleanUrl, $proxy);
            if ($html && strlen($html) > 500 && !self::isErrorHtml($html)) {
                return $html;
            }
        }

        // 5. Final Failover: Direct HTTP with safe desktop browser headers
        Log::info("🌐 Universal APIs and rotating proxy failed. Failing over to Direct HTTP with safe desktop headers for: {$cleanUrl}");
        $directHtml = $this->fetchHtmlDirect($cleanUrl);
        if ($directHtml && strlen($directHtml) > 500 && !self::isErrorHtml($directHtml)) {
            return $directHtml;
        }

        Log::warning("⚠️ All universal scraping providers, rotating proxy, and direct HTTP failed for: {$cleanUrl}");
        return null;
    }

    /**
     * 🌐 Scrape.do Web Scraping API
     * Handles Cloudflare Turnstile, DataDome, Akamai, JS-rendering, and rotating residential proxies.
     * Enforces 15s max timeout per request and automatic cooldown caching on quota exhaustion.
     */
    public function fetchWithScrapeDo($url, $token)
    {
        try {
            $tokenValue = trim($token ?? '');
            if (empty($tokenValue)) return null;

            $cleanUrl = str_replace(' ', '%20', trim($url));

            if (self::isProviderOnCooldown('scrape_do')) {
                Log::warning("⚠️ Scrape.do is in cooldown. Skipping for: {$cleanUrl}");
                return null;
            }

            Log::info("🌐 Calling Scrape.do Web Scraping API for: {$cleanUrl}");

            $params = [
                'token'  => $tokenValue,
                'url'    => $cleanUrl,
                'render' => 'true',
            ];

            // If Bangladesh news site, use BD geo-targeting
            if (str_contains($cleanUrl, '.bd') || str_contains($cleanUrl, 'prothomalo.com') || str_contains($cleanUrl, 'jamuna.tv') || str_contains($cleanUrl, 'kalerkantho.com') || str_contains($cleanUrl, 'somoynews.tv') || str_contains($cleanUrl, 'samakal.com') || str_contains($cleanUrl, 'dailyamardesh.com')) {
                $params['geoCode'] = 'bd';
            }

            $apiUrl = 'https://api.scrape.do?' . http_build_query($params);

            // Realistic timeout: 15s max per request, 5s connect timeout
            $response = Http::timeout(15)
                ->connectTimeout(5)
                ->withOptions(['verify' => false])
                ->get($apiUrl);

            $status = $response->status();
            $body = $response->body();

            // Detect quota exhaustion / rate-limiting (e.g. 429, 403 quota reached, 407 proxy auth error)
            if (self::isQuotaOrRateLimitError($status, $body)) {
                self::markProviderCooldown('scrape_do', 5, "HTTP {$status} Quota/Rate-limit: " . substr(strip_tags($body), 0, 150));
                return null;
            }

            if ($response->successful()) {
                if ($body && strlen($body) > 500 && !self::isErrorHtml($body)) {
                    Log::info("✅ Scrape.do API Success. HTML length: " . strlen($body));
                    return $body;
                }
                if ($body && self::isErrorHtml($body)) {
                    Log::warning("⚠️ Scrape.do returned Error/Blocked HTML for: {$cleanUrl}");
                }
            } else {
                Log::warning("⚠️ Scrape.do HTTP {$status}: " . substr($body, 0, 300));
                if ($status === 503 || $status === 502) {
                    self::markProviderCooldown('scrape_do', 2, "HTTP {$status} Service Unavailable");
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("⚠️ Scrape.do Connection Timeout (15s limit reached): " . $e->getMessage());
        } catch (\Throwable $e) {
            Log::warning("⚠️ Scrape.do API Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * 🌐 Decodo / SmartProxy Web Scraping API
     * Handles SmartProxy.org (/v1/scrape) and Decodo (/v2/scrape).
     * Enforces 15s max timeout per request and automatic cooldown caching on quota exhaustion.
     */
    public function fetchWithDecodoApi($url, $token)
    {
        try {
            $cleanUrl = str_replace(' ', '%20', trim($url));

            if (self::isProviderOnCooldown('smartproxy') || self::isProviderOnCooldown('decodo')) {
                Log::warning("⚠️ Smartproxy / Decodo is in cooldown. Skipping for: {$cleanUrl}");
                return null;
            }

            // Clean up Basic auth token header prefix if user included it in settings/input
            if (preg_match('/^Basic\s+(.*)$/i', trim($token ?? ''), $matches)) {
                $tokenValue = $matches[1];
            } else {
                $tokenValue = trim($token ?? '');
            }

            if (empty($tokenValue)) return null;

            // Detect if this is a SmartProxy.org token or a Decodo token
            $decoded = base64_decode($tokenValue);
            $isSmartProxyOrg = false;
            if ($decoded) {
                $parts = explode(':', $decoded, 2);
                $username = $parts[0] ?? '';
                if (str_starts_with($username, 'smart-')) {
                    $isSmartProxyOrg = true;
                }
            }

            if ($isSmartProxyOrg) {
                Log::info("🌐 Calling SmartProxy.org Web Scraper API (/v1/scrape) for: {$cleanUrl}");
                
                $payload = [
                    'js_render' => ['enabled' => true],
                    'request'   => ['only_main_content' => false, 'device' => 'desktop'],
                    'output'    => ['formats' => ['html']],
                    'proxy'     => ['location' => 'BD'],
                    'url'       => $cleanUrl
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . $tokenValue,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ])->timeout(15)
                  ->connectTimeout(5)
                  ->withOptions(['verify' => false])
                  ->post('https://scraper.smartproxy.org/v1/scrape', $payload);

                $status = $response->status();
                $body = $response->body();

                // Rate limit backoff retry (1s max)
                if ($status === 429) {
                    Log::warning("⚠️ SmartProxy 429 Rate Limit. Backing off 1s and retrying once for: {$cleanUrl}");
                    usleep(1000000);
                    $response = Http::withHeaders([
                        'Authorization' => 'Basic ' . $tokenValue,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json'
                    ])->timeout(15)
                      ->connectTimeout(5)
                      ->withOptions(['verify' => false])
                      ->post('https://scraper.smartproxy.org/v1/scrape', $payload);

                    $status = $response->status();
                    $body = $response->body();
                }

                if (self::isQuotaOrRateLimitError($status, $body)) {
                    self::markProviderCooldown('smartproxy', 5, "SmartProxy.org HTTP {$status} Quota/Rate-limit");
                    self::markProviderCooldown('decodo', 5, "HTTP {$status}");
                    return null;
                }

                if ($response->successful()) {
                    $html = $response->json('data.html');
                    if ($html && strlen($html) > 500 && !self::isErrorHtml($html)) {
                        Log::info("✅ SmartProxy.org Universal Scraping API Success. HTML length: " . strlen($html));
                        return $html;
                    }
                } else {
                    Log::warning("⚠️ SmartProxy.org HTTP {$status}: " . substr($body, 0, 300));
                }
            } else {
                Log::info("🌐 Calling Decodo (SmartProxy) Web Scraping API v2 for: {$cleanUrl}");

                $payloadData = [
                    'url'      => $cleanUrl,
                    'target'   => 'universal',
                    'headless' => 'html'
                ];
                if (str_contains($cleanUrl, '.bd') || str_contains($cleanUrl, 'prothomalo.com') || str_contains($cleanUrl, 'jamuna.tv') || str_contains($cleanUrl, 'kalerkantho.com') || str_contains($cleanUrl, 'somoynews.tv') || str_contains($cleanUrl, 'samakal.com') || str_contains($cleanUrl, 'dailyamardesh.com')) {
                    $payloadData['geo'] = 'Bangladesh';
                }

                try {
                    $response = Http::withHeaders([
                        'Authorization' => 'Basic ' . $tokenValue,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json'
                    ])->timeout(15)
                      ->connectTimeout(5)
                      ->withOptions([
                          'verify'  => false,
                          'version' => 1.1,
                      ])
                      ->post('https://scraper-api.smartproxy.com/v2/scrape', $payloadData);

                    $status = $response->status();
                    $body = $response->body();
                } catch (\Throwable $e) {
                    Log::warning("⚠️ Decodo Http client exception, trying stream context fallback: " . $e->getMessage());
                    $payloadJson = json_encode($payloadData);
                    $streamOptions = [
                        'http' => [
                            'method'        => 'POST',
                            'header'        => "Authorization: Basic " . $tokenValue . "\r\n" .
                                               "Content-Type: application/json\r\n" .
                                               "Accept: application/json\r\n",
                            'content'       => $payloadJson,
                            'timeout'       => 15,
                            'ignore_errors' => true
                        ],
                        'ssl' => [
                            'verify_peer'      => false,
                            'verify_peer_name' => false,
                            'verify_host'      => false,
                        ]
                    ];
                    $context = stream_context_create($streamOptions);
                    $rawBody = @file_get_contents('https://scraper-api.smartproxy.com/v2/scrape', false, $context);
                    $status = 0;
                    if (isset($http_response_header[0]) && preg_match('/HTTP\/\S+\s+(\d+)/', $http_response_header[0], $m)) {
                        $status = (int) $m[1];
                    }
                    $body = $rawBody ?: '';
                }

                if (self::isQuotaOrRateLimitError($status, $body)) {
                    self::markProviderCooldown('smartproxy', 5, "Decodo HTTP {$status} Quota/Rate-limit: " . substr(strip_tags($body), 0, 150));
                    self::markProviderCooldown('decodo', 5, "HTTP {$status}");
                    return null;
                }

                if ($status >= 200 && $status < 300) {
                    $json = json_decode($body, true);
                    $html = $json['results'][0]['content'] ?? $json['results'][0]['html'] ?? $json['data']['html'] ?? null;
                    if ($html && strlen($html) > 500 && !self::isErrorHtml($html)) {
                        Log::info("✅ Decodo Universal Scraping API Success. HTML length: " . strlen($html));
                        return $html;
                    }
                } else {
                    Log::warning("⚠️ Decodo HTTP {$status}: " . substr($body, 0, 300));
                    if ($status === 503 || $status === 502) {
                        self::markProviderCooldown('smartproxy', 2, "Decodo HTTP {$status} Service Unavailable");
                        self::markProviderCooldown('decodo', 2, "Decodo HTTP {$status}");
                    }
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning("⚠️ Decodo/SmartProxy Connection Timeout (15s limit reached): " . $e->getMessage());
        } catch (\Throwable $e) {
            Log::warning("⚠️ Decodo Scraping API exception: " . $e->getMessage());
        }

        return null;
    }
}