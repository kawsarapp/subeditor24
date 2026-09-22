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

        try {
            // 1. Primary: Use powerful NewsScraperService (handles Cloudflare, WAF, Python curl_cffi, proxies)
            try {
                $scraper = app(\App\Services\NewsScraperService::class);
                $scraped = $scraper->scrape($url, [], $userId);
                if ($scraped && (!empty($scraped['title']) || !empty($scraped['image']))) {
                    return response()->json([
                        'success'   => true,
                        'title'     => $scraped['title'] ?? 'Headline Not Found',
                        'image_url' => $scraped['image'] ?? '',
                        'category'  => $scraped['category'] ?? 'News',
                        'date'      => !empty($scraped['date']) ? $scraped['date'] : date('d M Y'),
                    ]);
                }
            } catch (\Throwable $scraperErr) {
                Log::warning("FreePhotoCard NewsScraperService notice: " . $scraperErr->getMessage());
            }

            // 2. Direct HTTP request with realistic browser headers as fallback
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
            ])->timeout(15)->withoutVerifying()->get($url);

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unable to access the news URL. (HTTP Status: ' . $response->status() . ')',
                ], 422);
            }

            $html = $response->body();

            // Extract Metadata
            $title = $this->extractMeta($html, ['og:title', 'twitter:title', 'title']);
            $image = $this->extractMeta($html, ['og:image', 'twitter:image', 'og:image:secure_url', 'image_src']);
            $category = $this->extractMeta($html, ['article:section', 'category', 'news_keywords']);
            $date = $this->extractMeta($html, ['article:published_time', 'pubdate', 'date']);

            // Fallback for title if tags are empty
            if (empty($title)) {
                if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
                    $title = trim(html_entity_decode(strip_tags($matches[1])));
                }
            }

            // Cleanup title (strip site branding suffixes e.g. " - Prothom Alo", " | Samakal")
            if (!empty($title)) {
                $title = preg_replace('/(\s*[-|–—]\s*[^–—|-]+)$/u', '', $title);
                $title = trim(html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            }

            // Resolve relative image URLs to absolute
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
                    'message' => 'Could not detect article title or featured image from this page.',
                ], 404);
            }

            return response()->json([
                'success'   => true,
                'title'     => $title ?: 'Headline Not Found',
                'image_url' => $image ?: '',
                'category'  => $category ?: 'News',
                'date'      => $date ?: date('d M Y'),
            ]);

        } catch (\Exception $e) {
            Log::error("Free PhotoCard Scraper Error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reading website: ' . $e->getMessage(),
            ], 500);
        }
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
            $path = $file->storeAs('public/photocard_frames', $filename);
            $framePath = Storage::url($path);
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
