<?php

namespace App\Http\Controllers;

use App\Models\UserPhotocardTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        $userId = Auth::id();
        $url = trim($request->url);
        $title = null;
        $image = null;
        $category = 'News';
        $date = date('d M Y');

        // 1. Primary Attempt: NewsScraperService (curl_cffi, proxies, universal scraper)
        try {
            $scraper = app(\App\Services\NewsScraperService::class);
            $scraped = $scraper->scrape($url, [], $userId);
            if ($scraped && (!empty($scraped['title']) || !empty($scraped['image']))) {
                $title = $scraped['title'] ?? null;
                $image = $scraped['image'] ?? null;
                $category = $scraped['category'] ?? 'News';
                if (!empty($scraped['date'])) {
                    $date = $scraped['date'];
                }
            }
        } catch (\Throwable $scraperErr) {
            Log::warning("FreePhotoCard NewsScraperService notice: " . $scraperErr->getMessage());
        }

        // 2. Direct HTTP Fallback with Full Browser Headers
        if (empty($title) || empty($image)) {
            try {
                $response = Http::withHeaders([
                    'User-Agent'                => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                    'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
                    'Accept-Language'           => 'bn,en-US,en;q=0.9',
                    'Sec-Ch-Ua'                 => '"Chromium";v="124", "Google Chrome";v="124", "Not-A.Brand";v="99"',
                    'Sec-Ch-Ua-Mobile'          => '?0',
                    'Sec-Ch-Ua-Platform'        => '"Windows"',
                    'Sec-Fetch-Dest'            => 'document',
                    'Sec-Fetch-Mode'            => 'navigate',
                    'Sec-Fetch-Site'            => 'none',
                    'Sec-Fetch-User'            => '?1',
                    'Upgrade-Insecure-Requests' => '1',
                ])->timeout(12)->withoutVerifying()->get($url);

                if ($response->successful()) {
                    $html = $response->body();
                    $title = $title ?: $this->extractMeta($html, ['og:title', 'twitter:title', 'title']);
                    $image = $image ?: $this->extractMeta($html, ['og:image', 'twitter:image', 'og:image:secure_url', 'image_src']);
                    $category = $category === 'News' ? ($this->extractMeta($html, ['article:section', 'category', 'news_keywords']) ?: 'News') : $category;
                    $date = $this->extractMeta($html, ['article:published_time', 'pubdate', 'date']) ?: $date;

                    if (empty($title) && preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                        $title = trim(html_entity_decode(strip_tags($matches[1])));
                    }
                }
            } catch (\Throwable $httpErr) {
                Log::warning("FreePhotoCard Direct HTTP notice: " . $httpErr->getMessage());
            }
        }

        // 3. Fallback: Jina AI Reader proxy for heavy Cloudflare WAF protected portals
        if (empty($title) || empty($image)) {
            try {
                $jinaUrl = 'https://r.jina.ai/' . $url;
                $jinaResp = Http::withHeaders([
                    'X-Target-Selector' => 'h1, title, img, meta',
                ])->timeout(10)->get($jinaUrl);

                if ($jinaResp->successful()) {
                    $body = $jinaResp->body();
                    if (empty($title) && preg_match('/Title:\s*(.+)/i', $body, $tm)) {
                        $title = trim($tm[1]);
                    }
                    if (empty($image) && preg_match('/!\[.*?\]\((https?:\/\/[^\s\)]+)\)/i', $body, $im)) {
                        $image = trim($im[1]);
                    }
                }
            } catch (\Throwable $jinaErr) {
                Log::warning("FreePhotoCard Jina fallback notice: " . $jinaErr->getMessage());
            }
        }

        // Clean up title
        if (!empty($title)) {
            $title = preg_replace('/(\s*[-|–—]\s*[^–—|-]+)$/u', '', $title);
            $title = trim(html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        }

        // Resolve relative image URLs
        if (!empty($image) && !preg_match('/^https?:\/\//i', $image)) {
            $parsed = parse_url($url);
            $scheme = $parsed['scheme'] ?? 'http';
            $host = $parsed['host'] ?? '';
            if (str_starts_with($image, '//')) {
                $image = $scheme . ':' . $image;
            } elseif (str_starts_with($image, '/')) {
                $image = $scheme . '://' . $host . $image;
            } else {
                $image = $scheme . '://' . $host . '/' . $image;
            }
        }

        if (empty($title) && empty($image)) {
            return response()->json([
                'success' => false,
                'message' => 'Could not detect article title or featured image from this page. Please enter manually.',
            ], 404);
        }

        // Convert image to Base64 to bypass browser CORS / hotlink protection on canvas
        $imageBase64 = null;
        if (!empty($image)) {
            $imageBase64 = $this->convertImageToBase64($image, $url);
        }

        return response()->json([
            'success'          => true,
            'title'            => $title ?: 'Headline Not Found',
            'image_url'        => $image ?: '',
            'image_base64'     => $imageBase64,
            'proxy_image_url'  => $image ? route('free-photocard.proxy-image', ['url' => $image]) : '',
            'category'         => $category ?: 'News',
            'date'             => $date ?: date('d M Y'),
        ]);
    }

    /**
     * Proxy image to avoid browser canvas CORS taint.
     */
    public function proxyImage(Request $request)
    {
        $imageUrl = $request->query('url');
        if (empty($imageUrl) || !filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return response('Invalid image URL', 400);
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
        if (empty($imageUrl)) return null;
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
     * Helper to extract OpenGraph and Meta tags.
     */
    private function extractMeta($html, array $tags): ?string
    {
        foreach ($tags as $tag) {
            // Check meta property
            if (preg_match('/<meta[^>]+property=[\'"]' . preg_quote($tag, '/') . '[\'"][^>]+content=[\'"]([^\'"]+)[\'"]/i', $html, $m)) {
                return trim(html_entity_decode($m[1]));
            }
            // Check meta name
            if (preg_match('/<meta[^>]+name=[\'"]' . preg_quote($tag, '/') . '[\'"][^>]+content=[\'"]([^\'"]+)[\'"]/i', $html, $m)) {
                return trim(html_entity_decode($m[1]));
            }
            // Check inverted attributes (content first)
            if (preg_match('/<meta[^>]+content=[\'"]([^\'"]+)[\'"][^>]+(?:property|name)=[\'"]' . preg_quote($tag, '/') . '[\'"]/i', $html, $m)) {
                return trim(html_entity_decode($m[1]));
            }
        }
        return null;
    }
}
