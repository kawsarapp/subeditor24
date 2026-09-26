<?php

namespace App\Http\Controllers;

use App\Models\UserPhotocardTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class FreePhotocardController extends Controller
{
    /**
     * Display the Free Photo Card Generator dashboard.
     */
    public function index()
    {
        $userId = Auth::id();
        $templates = UserPhotocardTemplate::where('user_id', $userId)->latest()->get();

        // Default layout preset for 1200x1200 or 1080x1080 cards
        $defaultLayout = [
            'canvas_width'    => 1200,
            'canvas_height'   => 1200,
            'image_x'         => 0,
            'image_y'         => 0,
            'image_w'         => 1200,
            'image_h'         => 800,
            'image_fit'       => 'cover', // cover, contain
            'title_x'         => 60,
            'title_y'         => 860,
            'title_w'         => 1080,
            'title_max_lines' => 3,
            'font_family'     => 'SolaimanLipi',
            'font_size'       => 52,
            'font_weight'     => 'bold',
            'font_color'      => '#ffffff',
            'line_height'     => 1.35,
            'text_align'      => 'center', // left, center, right
            'bg_color'        => '#111827',
            'show_date'       => true,
            'date_x'          => 60,
            'date_y'          => 810,
            'date_font_size'  => 26,
            'date_font_color' => '#f3f4f6',
        ];

        return view('free-photocard.index', compact('templates', 'defaultLayout'));
    }

    /**
     * Fetch news metadata (Title, Featured Image, Category, Date) from any URL.
     */
    public function fetchUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $url = trim($request->url);

        // 🛡️ SSRF Defense: Block private/internal networks, loopback, and cloud metadata
        if (!$this->isSafeExternalUrl($url)) {
            return response()->json([
                'success' => false,
                'message' => 'অননুমোদিত বা অনিরাপদ URL। দয়া করে একটি সঠিক পাবলিক নিউজ লিঙ্ক দিন।',
            ], 400);
        }

        $userId = Auth::id();

        $metadata = [
            'title'    => null,
            'image'    => null,
            'category' => 'News',
            'date'     => date('d M Y'),
        ];

        // 1. Try Internal NewsScraperService
        try {
            $scraper = app(\App\Services\NewsScraperService::class);
            $scraped = $scraper->scrape($url, [], $userId);
            if ($scraped && (!empty($scraped['title']) || !empty($scraped['image']))) {
                $metadata['title'] = $scraped['title'] ?? null;
                $metadata['image'] = $scraped['image'] ?? null;
                $metadata['category'] = $scraped['category'] ?? 'News';
                if (!empty($scraped['date'])) {
                    $metadata['date'] = $scraped['date'];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("FreePhotoCard NewsScraperService notice: " . $e->getMessage());
        }

        // 2. If metadata missing, fetch HTML using multi-method browser client
        if (empty($metadata['title']) || empty($metadata['image'])) {
            $html = $this->fetchHtmlFromUrl($url);
            if (!empty($html)) {
                $parsed = $this->parseMetadataFromHtml($html, $url);
                $metadata['title'] = $metadata['title'] ?: $parsed['title'];
                $metadata['image'] = $metadata['image'] ?: $parsed['image'];
                $metadata['category'] = $metadata['category'] !== 'News' ? $metadata['category'] : $parsed['category'];
                $metadata['date'] = $metadata['date'] !== date('d M Y') ? $metadata['date'] : $parsed['date'];
            }
        }

        // 3. Fallback to Jina Reader if still empty
        if (empty($metadata['title']) || empty($metadata['image'])) {
            try {
                $jinaResp = Http::withHeaders(['X-Target-Selector' => 'h1, title, img, meta'])
                    ->timeout(10)
                    ->get('https://r.jina.ai/' . $url);

                if ($jinaResp->successful()) {
                    $body = $jinaResp->body();
                    if (empty($metadata['title']) && preg_match('/Title:\s*(.+)/i', $body, $tm)) {
                        $metadata['title'] = trim($tm[1]);
                    }
                    if (empty($metadata['image']) && preg_match('/!\[.*?\]\((https?:\/\/[^\s\)]+)\)/i', $body, $im)) {
                        $metadata['image'] = trim($im[1]);
                    }
                }
            } catch (\Throwable $jinaErr) {
                Log::warning("FreePhotoCard Jina notice: " . $jinaErr->getMessage());
            }
        }

        // Clean up title
        if (!empty($metadata['title'])) {
            $metadata['title'] = preg_replace('/(\s*[-|–—]\s*[^–—|-]+)$/u', '', $metadata['title']);
            $metadata['title'] = trim(html_entity_decode($metadata['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // Resolve relative image URLs
        if (!empty($metadata['image'])) {
            $metadata['image'] = $this->resolveAbsoluteUrl($metadata['image'], $url);
        }

        if (empty($metadata['title']) && empty($metadata['image'])) {
            return response()->json([
                'success' => false,
                'message' => 'Could not detect article title or featured image from this page. Please enter manually.',
            ], 404);
        }

        // Convert image to Base64 to bypass browser CORS on canvas
        $imageBase64 = null;
        if (!empty($metadata['image'])) {
            $imageBase64 = $this->convertImageToBase64($metadata['image'], $url);
        }

        return response()->json([
            'success'         => true,
            'title'           => $metadata['title'] ?: 'Headline Not Found',
            'image_url'       => $metadata['image'] ?: '',
            'image_base64'    => $imageBase64,
            'proxy_image_url' => $metadata['image'] ? route('free-photocard.proxy-image', ['url' => $metadata['image']]) : '',
            'category'        => $metadata['category'] ?: 'News',
            'date'            => $metadata['date'] ?: date('d M Y'),
        ]);
    }

    /**
     * Resilient HTML fetcher using cURL with full browser TLS fingerprint headers.
     */
    private function fetchHtmlFromUrl(string $url): ?string
    {
        // Method A: cURL with browser headers
        if (function_exists('curl_init')) {
            try {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL            => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_TIMEOUT        => 12,
                    CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                    CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    CURLOPT_HTTPHEADER     => [
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                        'Accept-Language: bn,en-US,en;q=0.9',
                        'Sec-Ch-Ua: "Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
                        'Sec-Ch-Ua-Mobile: ?0',
                        'Sec-Ch-Ua-Platform: "Windows"',
                        'Sec-Fetch-Dest: document',
                        'Sec-Fetch-Mode: navigate',
                        'Sec-Fetch-Site: none',
                        'Sec-Fetch-User: ?1',
                        'Upgrade-Insecure-Requests: 1',
                    ],
                ]);
                $content = curl_exec($ch);
                $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($status >= 200 && $status < 400 && !empty($content)) {
                    return $content;
                }
            } catch (\Throwable $curlErr) {
                Log::warning("fetchHtmlFromUrl cURL notice: " . $curlErr->getMessage());
            }
        }

        // Method B: Laravel HTTP Client
        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            ])->timeout(10)->withoutVerifying()->get($url);

            if ($resp->successful()) {
                return $resp->body();
            }
        } catch (\Throwable $httpErr) {
            Log::warning("fetchHtmlFromUrl Http notice: " . $httpErr->getMessage());
        }

        return null;
    }

    /**
     * Parse Title, Featured Image, Date, and Category using DomCrawler and Schema.org JSON-LD.
     */
    private function parseMetadataFromHtml(string $html, string $baseUrl): array
    {
        $data = [
            'title'    => null,
            'image'    => null,
            'category' => 'News',
            'date'     => date('d M Y'),
        ];

        // 1. Check Schema.org JSON-LD (Most reliable on all news websites)
        if (preg_match_all('/<script[^>]+type=[\'"]application\/ld\+json[\'"][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            foreach ($matches[1] as $jsonString) {
                $ld = json_decode(trim($jsonString), true);
                if (!$ld) continue;

                // Flatten @graph if present
                $items = isset($ld['@graph']) && is_array($ld['@graph']) ? $ld['@graph'] : [$ld];

                foreach ($items as $item) {
                    if (!is_array($item)) continue;
                    $type = $item['@type'] ?? '';

                    // Headline
                    if (empty($data['title']) && !empty($item['headline'])) {
                        $data['title'] = is_string($item['headline']) ? $item['headline'] : null;
                    }

                    // Image
                    if (empty($data['image']) && !empty($item['image'])) {
                        if (is_string($item['image'])) {
                            $data['image'] = $item['image'];
                        } elseif (is_array($item['image'])) {
                            if (isset($item['image']['url'])) {
                                $data['image'] = $item['image']['url'];
                            } elseif (isset($item['image'][0])) {
                                $data['image'] = is_string($item['image'][0]) ? $item['image'][0] : ($item['image'][0]['url'] ?? null);
                            }
                        }
                    }

                    // Date
                    if (!empty($item['datePublished'])) {
                        $data['date'] = $item['datePublished'];
                    }

                    // Category / Section
                    if (!empty($item['articleSection'])) {
                        $data['category'] = is_string($item['articleSection']) ? $item['articleSection'] : $data['category'];
                    }
                }
            }
        }

        // 2. Use DomCrawler for OpenGraph, Twitter, and DOM Fallbacks
        try {
            $crawler = new Crawler($html);

            // Title Selectors
            if (empty($data['title'])) {
                $titleSelectors = [
                    'meta[property="og:title"]'        => 'content',
                    'meta[name="twitter:title"]'       => 'content',
                    'meta[name="title"]'               => 'content',
                    'h1.title'                         => 'text',
                    'h1.news-title'                    => 'text',
                    'h1'                               => 'text',
                    'title'                            => 'text',
                ];
                foreach ($titleSelectors as $sel => $attr) {
                    try {
                        $node = $crawler->filter($sel);
                        if ($node->count() > 0) {
                            $val = $attr === 'text' ? trim($node->first()->text()) : trim($node->first()->attr($attr));
                            if (!empty($val)) {
                                $data['title'] = $val;
                                break;
                            }
                        }
                    } catch (\Throwable $e) {}
                }
            }

            // Image Selectors
            if (empty($data['image'])) {
                $imageSelectors = [
                    'meta[property="og:image"]'            => 'content',
                    'meta[property="og:image:secure_url"]' => 'content',
                    'meta[name="twitter:image"]'           => 'content',
                    'meta[name="twitter:image:src"]'       => 'content',
                    'link[rel="image_src"]'                => 'href',
                    'meta[itemprop="image"]'               => 'content',
                    'article figure img'                   => 'src',
                    'article img'                          => 'src',
                    '.featured-image img'                  => 'src',
                    '.news-details img'                    => 'src',
                    'main img'                             => 'src',
                ];
                foreach ($imageSelectors as $sel => $attr) {
                    try {
                        $node = $crawler->filter($sel);
                        if ($node->count() > 0) {
                            $val = trim($node->first()->attr($attr));
                            if (!empty($val) && !str_starts_with($val, 'data:')) {
                                $data['image'] = $val;
                                break;
                            }
                        }
                    } catch (\Throwable $e) {}
                }
            }

            // Date Selectors
            if ($data['date'] === date('d M Y')) {
                $dateSelectors = [
                    'meta[property="article:published_time"]' => 'content',
                    'meta[name="pubdate"]'                    => 'content',
                    'meta[name="publish_date"]'               => 'content',
                    'time'                                    => 'datetime',
                ];
                foreach ($dateSelectors as $sel => $attr) {
                    try {
                        $node = $crawler->filter($sel);
                        if ($node->count() > 0) {
                            $val = trim($node->first()->attr($attr));
                            if (!empty($val)) {
                                $data['date'] = $val;
                                break;
                            }
                        }
                    } catch (\Throwable $e) {}
                }
            }

        } catch (\Throwable $crawlErr) {
            Log::warning("parseMetadataFromHtml Crawler notice: " . $crawlErr->getMessage());
        }

        return $data;
    }

    /**
     * Resolve relative image URL to absolute URL.
     */
    private function resolveAbsoluteUrl(string $url, string $baseUrl): string
    {
        if (preg_match('/^https?:\/\//i', $url)) {
            return $url;
        }

        $parsed = parse_url($baseUrl);
        $scheme = $parsed['scheme'] ?? 'http';
        $host = $parsed['host'] ?? '';

        if (str_starts_with($url, '//')) {
            return $scheme . ':' . $url;
        }

        if (str_starts_with($url, '/')) {
            return $scheme . '://' . $host . $url;
        }

        return $scheme . '://' . $host . '/' . $url;
    }

    /**
     * Proxy image to avoid browser canvas CORS taint.
     */
    public function proxyImage(Request $request)
    {
        $imageUrl = $request->query('url');
        if (empty($imageUrl) || !$this->isSafeExternalUrl($imageUrl)) {
            return response('Invalid or disallowed image URL', 400);
        }

        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept'     => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])->timeout(12)->withoutVerifying()->get($imageUrl);

            if ($resp->successful()) {
                $contentType = $resp->header('Content-Type') ?: 'image/jpeg';
                return response($resp->body(), 200)
                    ->header('Content-Type', $contentType)
                    ->header('Cache-Control', 'public, max-age=86400')
                    ->header('Access-Control-Allow-Origin', '*');
            }
        } catch (\Throwable $e) {
            Log::warning("Proxy Image Error: " . $e->getMessage());
        }

        return response('Failed to load image', 404);
    }

    /**
     * Save or update a custom PNG frame template.
     */
    public function saveTemplate(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:100',
            'frame_image'  => 'nullable|file|mimes:png,webp|max:10240',
            'frame_path'   => 'nullable|string',
            'layout_data'  => 'required',
            'template_id'  => 'nullable|integer',
        ]);

        $userId = Auth::id();
        $framePath = $request->frame_path;

        // Handle PNG Frame upload
        if ($request->hasFile('frame_image')) {
            $file = $request->file('frame_image');
            $filename = 'frame_' . $userId . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('photocard_frames', $filename, 'public');
            $framePath = asset('storage/' . $path);
        }

        if (empty($framePath)) {
            return response()->json([
                'success' => false,
                'message' => 'Please upload a PNG frame image.',
            ], 422);
        }

        $layoutData = is_array($request->layout_data) 
            ? $request->layout_data 
            : json_decode($request->layout_data, true);

        if (!$layoutData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid layout coordinate data.',
            ], 422);
        }

        if ($request->filled('template_id')) {
            $template = UserPhotocardTemplate::where('id', $request->template_id)
                ->where('user_id', $userId)
                ->firstOrFail();

            $template->update([
                'name'        => $request->name,
                'frame_path'  => $framePath,
                'layout_data' => $layoutData,
            ]);
        } else {
            $template = UserPhotocardTemplate::create([
                'user_id'     => $userId,
                'name'        => $request->name,
                'frame_path'  => $framePath,
                'layout_data' => $layoutData,
                'is_default'  => true,
            ]);
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Template saved successfully!',
            'template' => $template,
        ]);
    }

    /**
     * Delete a saved template.
     */
    public function deleteTemplate($id)
    {
        $template = UserPhotocardTemplate::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully.',
        ]);
    }

    /**
     * Convert external image to base64 Data URL to prevent CORS canvas blocks.
     */
    private function convertImageToBase64(?string $imageUrl, ?string $refererUrl = null): ?string
    {
        if (empty($imageUrl) || !$this->isSafeExternalUrl($imageUrl)) {
            return null;
        }
        try {
            $resp = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Referer'    => $refererUrl ?: $imageUrl,
                'Accept'     => 'image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
            ])->timeout(8)->withoutVerifying()->get($imageUrl);

            if ($resp->successful() && strlen($resp->body()) > 100) {
                $contentType = $resp->header('Content-Type') ?: 'image/jpeg';
                return 'data:' . $contentType . ';base64,' . base64_encode($resp->body());
            }
        } catch (\Throwable $e) {
            Log::warning("Could not convert image to base64: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Validate that a given URL points to a safe public internet address
     * to strictly prevent Server-Side Request Forgery (SSRF).
     */
    private function isSafeExternalUrl(?string $url): bool
    {
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parsed = parse_url($url);
        $scheme = strtolower($parsed['scheme'] ?? '');
        $host = strtolower($parsed['host'] ?? '');

        // Only allow standard HTTP/HTTPS schemes
        if (!in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        if (empty($host)) {
            return false;
        }

        // Block obvious loopback/internal hosts and cloud metadata endpoints
        $blockedHosts = [
            'localhost',
            '127.0.0.1',
            '0.0.0.0',
            '[::1]',
            '::1',
            '169.254.169.254',
            'metadata.google.internal',
            'instance-data',
        ];

        if (in_array($host, $blockedHosts, true) || str_ends_with($host, '.local') || str_ends_with($host, '.internal') || str_ends_with($host, '.ddev.site')) {
            return false;
        }

        // If host is a raw IP address, validate it directly against private/reserved ranges
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
        }

        // Resolve DNS to IP addresses and verify none point to private/reserved networks
        $resolvedIps = @gethostbynamel($host);
        if ($resolvedIps === false || empty($resolvedIps)) {
            // If DNS resolution fails, block request
            return false;
        }

        foreach ($resolvedIps as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        return true;
    }
}
