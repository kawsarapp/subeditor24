<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

trait ScraperEnginesTrait
{
    public function getProxyConfig($userId = null, $url = null)
    {
        $uid = null;
        if ($userId) {
            $uid = $userId;
        } elseif (Auth::check()) {
            $user = Auth::user();
            $uid = in_array($user->role, ['staff', 'reporter']) ? $user->parent_id : $user->id;
        }

        // 1. Check user-specific or fallback Super Admin proxy from database
        $proxyHost = \App\Models\UserSetting::getSettingWithFallback($uid, 'proxy_host');
        $proxyPort = \App\Models\UserSetting::getSettingWithFallback($uid, 'proxy_port');
        
        if ($proxyHost && $proxyPort) {
            $proxyUser = \App\Models\UserSetting::getSettingWithFallback($uid, 'proxy_username');
            $proxyPass = \App\Models\UserSetting::getSettingWithFallback($uid, 'proxy_password');
            $auth = ($proxyUser && $proxyPass) ? "{$proxyUser}:{$proxyPass}@" : "";
            return "http://{$auth}{$proxyHost}:{$proxyPort}";
        }

        // 2. Fallback to .env proxy (for automated cron jobs or unconfigured admins)
        $envHost = env('SMARTPROXY_HOST');
        $envPort = env('SMARTPROXY_PORT');
        if ($envHost && $envPort) {
            $envUser = env('SMARTPROXY_USER');
            $envPass = env('SMARTPROXY_PASS');
            $auth = ($envUser && $envPass) ? "{$envUser}:{$envPass}@" : "";
            return "http://{$auth}{$envHost}:{$envPort}";
        }

        return null;
    }

    public function runPythonScraper($url, $userId = null)
    {
        $proxy = $this->getProxyConfig($userId, $url);
        $scriptPath = base_path("scraper.py"); 
        if (!file_exists($scriptPath)) return null;

        $pythonCmd = env('PYTHON_PATH'); 
        if (!$pythonCmd) {
            $pythonCmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
        }

        $command = "$pythonCmd " . escapeshellarg($scriptPath) . " " . escapeshellarg($url);
        if ($proxy) $command .= " " . escapeshellarg($proxy);
        $command .= " 2>&1";

        $output = shell_exec($command);
        $data = json_decode($output, true);
        
        return (json_last_error() === JSON_ERROR_NONE && isset($data['body'])) ? $data : null;
    }

    public function fetchHtmlWithPython($url, $userId = null)
    {
        $proxy = $this->getProxyConfig($userId, $url);
        $scriptPath = base_path("fetch_list.py"); 
        if (!file_exists($scriptPath)) return null;

        $pythonCmd = env('PYTHON_PATH'); 
        if (!$pythonCmd) {
            $pythonCmd = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3';
        }

        $command = "$pythonCmd " . escapeshellarg($scriptPath) . " " . escapeshellarg($url);
        if ($proxy) $command .= " " . escapeshellarg($proxy);
        $command .= " 2>&1";

        Log::info("🔄 Fetching List HTML with Python curl_cffi...");
        $output = shell_exec($command);
        
        return (strlen($output) > 500) ? $output : null;
    }

    protected function scrapeWithPuppeteer($url, $customSelectors, $userId)
    {
        $htmlContent = $this->runPuppeteer($url, $userId);

        if ($htmlContent && strlen($htmlContent) > 500) {
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
        $proxy = $this->getProxyConfig($userId, $url);
        $scriptPath = base_path("scraper-engine.js");
        if (!file_exists($scriptPath)) {
            Log::warning("⚠️ Puppeteer SKIPPED: scraper-engine.js not found at " . base_path("scraper-engine.js"));
            return null;
        }

        $tempFile = storage_path("app/public/temp_" . uniqid() . "_" . rand(1000,9999) . ".html");
        $nodeCmd = env('NODE_PATH') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'node' : 'node');

        $command = "$nodeCmd " . escapeshellarg($scriptPath) . " " . escapeshellarg($url) . " " . escapeshellarg($tempFile) . " " . escapeshellarg($proxy ?? '') . " 2>&1";
        
        Log::info("🔄 Engaging Node (Puppeteer) Engine...");
        $rawOutput = shell_exec($command);
        
        $htmlContent = null;
        if (file_exists($tempFile)) {
            $htmlContent = file_get_contents($tempFile);
            unlink($tempFile);
        } else {
            Log::warning("⚠️ Puppeteer: No output file created for [$url]. Raw stderr: " . substr($rawOutput ?? 'empty/null', 0, 800));
        }

        if ($htmlContent && (str_contains($htmlContent, "This site can't be reached") || str_contains($htmlContent, 'ERR_NAME_NOT_RESOLVED') || str_contains($htmlContent, 'ERR_CONNECTION_TIMED_OUT'))) {
            Log::warning("⚠️ Puppeteer returned Chrome Error Page. Skipping...");
            return null;
        }
        
        return (strlen($htmlContent) > 500) ? $htmlContent : null;
    }

    /**
     * 🚀 SmartProxy Universal Scraping API
     * Used for hard-blocked sites like Jamuna TV (Datadome).
     * Offloads all rendering, proxy rotation, and CAPTCHA solving to their cloud.
     */
    public function fetchWithUniversalScrapingApi($url, $userId = null)
    {
        $token = \App\Models\UserSetting::getSettingWithFallback($userId, 'smartproxy_api_token') ?? env('SMARTPROXY_SCRAPING_API_TOKEN');
        if (!$token) {
            Log::warning("⚠️ SMARTPROXY_SCRAPING_API_TOKEN not set in DB or .env — skipping Universal API.");
            return null;
        }

        // Clean up Basic auth token header prefix if user included it in settings/input
        if (preg_match('/^Basic\s+(.*)$/i', trim($token), $matches)) {
            $tokenValue = $matches[1];
        } else {
            $tokenValue = trim($token);
        }

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

        try {
            if ($isSmartProxyOrg) {
                Log::info("🌐 Calling SmartProxy.org Web Scraper API (/v1/scrape) for: $url");
                
                $payload = json_encode([
                    'js_render' => ['enabled' => true],
                    'request'   => ['only_main_content' => false, 'device' => 'desktop'],
                    'output'    => ['formats' => ['html']],
                    'proxy'     => ['location' => 'BD'],
                    'url'       => $url
                ]);

                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Basic ' . $tokenValue,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ])->withBody($payload, 'application/json')
                  ->timeout(120)
                  ->post('https://scraper.smartproxy.org/v1/scrape');

                if ($response->status() === 429) {
                    Log::warning("⚠️ SmartProxy 429 Rate Limit. Backing off 2s and retrying for: $url");
                    sleep(2);
                    $response = \Illuminate\Support\Facades\Http::withHeaders([
                        'Authorization' => 'Basic ' . $tokenValue,
                        'Content-Type'  => 'application/json',
                        'Accept'        => 'application/json'
                    ])->withBody($payload, 'application/json')
                      ->timeout(120)
                      ->post('https://scraper.smartproxy.org/v1/scrape');
                }

                if ($response->successful()) {
                    $html = $response->json('data.html');
                    if ($html && strlen($html) > 500) {
                        Log::info("✅ SmartProxy.org Universal Scraping API Success. HTML length: " . strlen($html));
                        return $html;
                    }
                }
            } else {
                Log::info("🌐 Calling Decodo (SmartProxy) Web Scraping API v2 for: $url");

                $payloadData = [
                    'url'        => $url,
                    'target'     => 'universal',
                    'headless'   => 'html'
                ];
                if (str_contains($url, '.bd') || str_contains($url, 'prothomalo.com') || str_contains($url, 'jamuna.tv') || str_contains($url, 'kalerkantho.com') || str_contains($url, 'somoynews.tv') || str_contains($url, 'samakal.com') || str_contains($url, 'dailyamardesh.com')) {
                    $payloadData['geo'] = 'Bangladesh';
                }

                $payloadJson = json_encode($payloadData);

                // 🔥 Stream Context with HTTP/1.1 to avoid OpenSSL unexpected EOF cURL errors
                $streamOptions = [
                    'http' => [
                        'method'        => 'POST',
                        'header'        => "Authorization: Basic " . $tokenValue . "\r\n" .
                                           "Content-Type: application/json\r\n" .
                                           "Accept: application/json\r\n",
                        'content'       => $payloadJson,
                        'timeout'       => 90,
                        'ignore_errors' => true
                    ],
                    'ssl' => [
                        'verify_peer'      => false,
                        'verify_peer_name' => false,
                    ]
                ];

                $context = stream_context_create($streamOptions);
                $rawBody = @file_get_contents('https://scraper-api.smartproxy.com/v2/scrape', false, $context);

                if ($rawBody) {
                    $json = json_decode($rawBody, true);
                    $html = $json['results'][0]['content'] ?? $json['results'][0]['html'] ?? $json['data']['html'] ?? null;
                    if ($html && strlen($html) > 500) {
                        Log::info("✅ Decodo Universal Scraping API Success. HTML length: " . strlen($html));
                        return $html;
                    }
                }

                // Fallback to Http client
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => 'Basic ' . $tokenValue,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ])->withBody($payloadJson, 'application/json')
                  ->timeout(90)
                  ->post('https://scraper-api.smartproxy.com/v2/scrape');

                if ($response->successful()) {
                    $json = $response->json();
                    $html = $json['results'][0]['content'] ?? $json['results'][0]['html'] ?? $json['data']['html'] ?? null;
                    if ($html && strlen($html) > 500) {
                        Log::info("✅ Decodo Universal Scraping API Success. HTML length: " . strlen($html));
                        return $html;
                    }
                }
            }

            Log::warning("⚠️ Universal Scraping API failed for: $url");
        } catch (\Exception $e) {
            Log::warning("⚠️ Universal Scraping API exception: " . $e->getMessage());
        }

        return null;
    }
}