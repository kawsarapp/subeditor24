<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait ApiPostingTrait
{
    /**
     * Execute API Post to external client website (Laravel / Custom API / Next.js / Node.js etc.)
     */
    protected function executeApiPost($news, $settings, $finalTitle, $finalContent, $categories, $websiteImage, $hashtags, $remotePostId, $publishedUrl)
    {
        $result = [
            'success'       => false,
            'remote_id'     => $remotePostId,
            'published_url' => $publishedUrl,
            'error'         => null
        ];

        $baseUrl = rtrim($settings->laravel_site_url ?? '', '/');

        try {
            // 🎙️ Embed AI Voice Narration HTML5 Audio Player
            if (empty($news->audio_url) && (!empty($settings->tts_enabled) || stripos($finalContent, '[audio') !== false)) {
                try {
                    $user = !empty($news->staff_id) ? \App\Models\User::find($news->staff_id) : (\Illuminate\Support\Facades\Auth::user() ?? \App\Models\User::find($news->user_id));
                    $audioService = app(\App\Modules\AudioNarration\Services\AudioNarrationService::class);
                    $audioResult = $audioService->generateForNews($news, $user);
                    if (!empty($audioResult['audio_url'])) {
                        $news->audio_url = $audioResult['audio_url'];
                        $news->audio_path = $audioResult['audio_path'] ?? null;
                        $news->audio_provider = $audioResult['audio_provider'] ?? null;
                        $news->audio_status = 'completed';
                        $news->save();
                    }
                } catch (\Exception $e) {
                    Log::warning("⚠️ Audio auto-generation on API publish failed: " . $e->getMessage());
                }
            }

            $embedMode = $settings->tts_embed_mode ?? 'top';
            $embedder = app(\App\Modules\AudioNarration\Services\AudioPlayerEmbedderService::class);
            $finalContent = $embedder->embedPlayer($finalContent, $news->audio_url, $embedMode, $finalTitle);

            // 🟢 1. CUSTOM API LOGIC (Dynamic Webhook / Mapping)
            if (!empty($settings->custom_api_url) && !empty($settings->custom_api_mapping)) {
                $apiUrl  = $settings->custom_api_url;
                $mapping = is_array($settings->custom_api_mapping) 
                    ? $settings->custom_api_mapping 
                    : (json_decode($settings->custom_api_mapping, true) ?? []);

                Log::info("🟢 Sending Dynamic Custom Request to: " . $apiUrl);

                $token        = $settings->laravel_api_token;
                $imageFormat  = $mapping['image_format'] ?? 'url'; // 'url', 'file', or 'base64'
                $authType     = $mapping['auth_type'] ?? ($mapping['header_auth'] ?? 'Bearer'); // 'Bearer', 'header', 'basic', 'body'
                $authHeader   = $mapping['auth_header_name'] ?? 'Authorization';
                $categoryType = $mapping['category_type'] ?? 'id'; // 'id' or 'name'

                // Build Base Data
                $slug = Str::slug($finalTitle) ?: 'news-' . time();
                $dateStr = now()->format('Y-m-d H:i:s');
                $categoryValue = ($categoryType === 'name') 
                    ? ($news->category ?? 'General') 
                    : $categories;

                $headers = [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'Subeditor24-Publisher/2.0 (+https://subeditor24.com)',
                ];

                // Configure Authentication Header
                if (!empty($token)) {
                    if ($authType === 'Bearer') {
                        $headers['Authorization'] = 'Bearer ' . $token;
                    } elseif ($authType === 'header' || $authType === 'custom_header') {
                        $headers[$authHeader] = $token;
                    } elseif ($authType === 'basic') {
                        $headers['Authorization'] = 'Basic ' . base64_encode($token);
                    }
                }

                // If sending as Direct Binary Multipart File
                if ($imageFormat === 'file' && !empty($websiteImage) && isset($mapping['image'])) {
                    $multipart = [];
                    $addPart = function($name, $val) use (&$multipart) {
                        $multipart[] = ['name' => (string)$name, 'contents' => (string)($val ?? '')];
                    };

                    if (isset($mapping['title'])) $addPart($mapping['title'], $finalTitle);
                    if (isset($mapping['content'])) $addPart($mapping['content'], $finalContent);
                    if (isset($mapping['tags'])) $addPart($mapping['tags'], $hashtags);
                    if (isset($mapping['date'])) $addPart($mapping['date'], $dateStr);
                    if (isset($mapping['slug'])) $addPart($mapping['slug'], $slug);
                    if (isset($mapping['original_link'])) $addPart($mapping['original_link'], $news->original_link ?? '');
                    if (isset($mapping['audio_url'])) $addPart($mapping['audio_url'], $news->audio_url ?? '');

                    // Categories
                    if (isset($mapping['category'])) {
                        $catKey = str_replace('[]', '', $mapping['category']);
                        if (is_array($categoryValue)) {
                            foreach ($categoryValue as $cat) {
                                $multipart[] = ['name' => $catKey . '[]', 'contents' => (string)$cat];
                            }
                        } else {
                            $addPart($mapping['category'], $categoryValue);
                        }
                    }

                    // Body Token if auth is body
                    if ($authType === 'body' && !empty($token)) {
                        $tokenKey = $mapping['token'] ?? 'token';
                        $addPart($tokenKey, $token);
                    }

                    // Static Extra Fields
                    if (isset($mapping['extra']) && is_array($mapping['extra'])) {
                        foreach ($mapping['extra'] as $key => $val) {
                            $addPart((string)$key, $val);
                        }
                    }

                    // Fetch and attach binary image
                    $imgData = $this->getImageBinaryAndMime($websiteImage);
                    if ($imgData && isset($mapping['image'])) {
                        $multipart[] = [
                            'name'     => (string)$mapping['image'],
                            'contents' => $imgData['bytes'],
                            'filename' => $imgData['filename']
                        ];
                        Log::info("🖼️ Binary Image attached for Multipart API: {$imgData['filename']}");
                    }

                    $client = new \GuzzleHttp\Client([
                        'timeout'         => 120,
                        'connect_timeout' => 30,
                        'verify'          => false,
                    ]);

                    $guzzleResponse = $client->post($apiUrl, [
                        'multipart'   => $multipart,
                        'headers'     => $headers,
                        'http_errors' => false,
                    ]);

                    $responseBody = $guzzleResponse->getBody()->getContents();
                    $statusCode   = $guzzleResponse->getStatusCode();

                } else {
                    // Standard JSON / Form Body Request
                    $jsonPayload = [];

                    if (isset($mapping['title'])) $jsonPayload[$mapping['title']] = $finalTitle;
                    if (isset($mapping['content'])) $jsonPayload[$mapping['content']] = $finalContent;
                    if (isset($mapping['tags'])) $jsonPayload[$mapping['tags']] = $hashtags;
                    if (isset($mapping['date'])) $jsonPayload[$mapping['date']] = $dateStr;
                    if (isset($mapping['slug'])) $jsonPayload[$mapping['slug']] = $slug;
                    if (isset($mapping['original_link'])) $jsonPayload[$mapping['original_link']] = $news->original_link ?? '';
                    if (isset($mapping['audio_url'])) $jsonPayload[$mapping['audio_url']] = $news->audio_url ?? '';
                    elseif (!empty($news->audio_url)) $jsonPayload['audio_url'] = $news->audio_url;

                    // Categories
                    if (isset($mapping['category'])) {
                        $jsonPayload[$mapping['category']] = $categoryValue;
                    }

                    // Image handling (URL or Base64)
                    $normalizedImgUrl = $this->normalizeImageUrl($websiteImage);
                    if (isset($mapping['image']) && !empty($normalizedImgUrl)) {
                        if ($imageFormat === 'base64') {
                            $imgData = $this->getImageBinaryAndMime($websiteImage);
                            if ($imgData) {
                                $jsonPayload[$mapping['image']] = 'data:' . $imgData['mime'] . ';base64,' . base64_encode($imgData['bytes']);
                            } else {
                                $jsonPayload[$mapping['image']] = $normalizedImgUrl;
                            }
                        } else {
                            $jsonPayload[$mapping['image']] = $normalizedImgUrl;
                        }
                    }

                    // Body Token if auth is body
                    if ($authType === 'body' && !empty($token)) {
                        $tokenKey = $mapping['token'] ?? 'token';
                        $jsonPayload[$tokenKey] = $token;
                    }

                    // Static Extra Fields
                    if (isset($mapping['extra']) && is_array($mapping['extra'])) {
                        foreach ($mapping['extra'] as $key => $val) {
                            $jsonPayload[$key] = $val;
                        }
                    }

                    $httpResponse = Http::timeout(120)
                        ->withOptions(['verify' => false])
                        ->withHeaders($headers)
                        ->post($apiUrl, $jsonPayload);

                    $responseBody = $httpResponse->body();
                    $statusCode   = $httpResponse->status();
                }

                Log::info("🔍 Custom API Response (HTTP {$statusCode}): " . $responseBody);

                if ($statusCode >= 200 && $statusCode < 300) {
                    $result['success'] = true;
                    $respData = json_decode($responseBody, true) ?? [];
                    
                    $idKey = $mapping['response_id_key'] ?? 'post_id';
                    $extractedId = $respData[$idKey] ?? ($respData['data'][$idKey] ?? ($respData['id'] ?? ($respData['data']['id'] ?? $remotePostId)));
                    $result['remote_id'] = $extractedId;

                    $siteBase = rtrim($settings->laravel_site_url ?? '', '/');
                    $prefix   = trim($settings->laravel_route_prefix ?? 'news', '/');

                    $urlKey = $mapping['response_url_key'] ?? 'live_url';
                    $liveUrl = $respData[$urlKey] ?? ($respData['data'][$urlKey] ?? ($respData['url'] ?? ($respData['link'] ?? ($respData['data']['URLAlies'] ?? null))));

                    if ($liveUrl) {
                        $result['published_url'] = filter_var($liveUrl, FILTER_VALIDATE_URL) ? $liveUrl : ($siteBase . '/' . ltrim($liveUrl, '/'));
                    } elseif (!empty($siteBase) && !empty($result['remote_id'])) {
                        $result['published_url'] = $siteBase . '/' . $prefix . '/' . $result['remote_id'];
                    }

                    Log::info("✅ Custom API Success. ID: {$result['remote_id']}");
                } else {
                    $result['error'] = "HTTP {$statusCode}: " . Str::limit($responseBody, 200);
                    Log::error("❌ Custom API Failed: HTTP {$statusCode} - {$responseBody}");
                }

            } 
            // 🔵 2. DEFAULT API LOGIC (/api/external-news-post)
            else {
                if (empty($baseUrl)) {
                    $result['error'] = 'No website URL configured.';
                    return $result;
                }

                $formattedImageUrl = $this->normalizeImageUrl($websiteImage);

                $apiUrl = $baseUrl . '/api/external-news-post';
                $payload = [
                    'token'               => $settings->laravel_api_token,
                    'title'               => $finalTitle,
                    'content'             => $finalContent,
                    'image_url'           => $formattedImageUrl,
                    'image'               => $formattedImageUrl,
                    'featured_image'      => $formattedImageUrl,
                    'featured_image_url'  => $formattedImageUrl,
                    'thumbnail'           => $formattedImageUrl,
                    'thumbnail_url'       => $formattedImageUrl,
                    'photo'               => $formattedImageUrl,
                    'cover_image'         => $formattedImageUrl,
                    'audio_url'           => $news->audio_url ?? null,
                    'hashtags'            => $hashtags,
                    'slug'                => Str::slug($finalTitle) ?: ('news-' . time()),
                    'category_name'       => $news->category ?? 'General',
                    'category_ids'        => $categories,
                    'category_id'         => is_array($categories) ? ($categories[0] ?? 1) : $categories,
                    'original_link'       => $news->original_link ?? '',
                    'published_at'        => now()->format('Y-m-d H:i:s')
                ];
                
                if ($news->wp_post_id) {
                    $payload['remote_id'] = $news->wp_post_id;
                }

                $headers = [
                    'Accept'     => 'application/json',
                    'User-Agent' => 'Subeditor24-Publisher/2.0 (+https://subeditor24.com)'
                ];

                if (!empty($settings->laravel_api_token)) {
                    $headers['Authorization'] = 'Bearer ' . $settings->laravel_api_token;
                }

                Log::info("🚀 Dispatched Default Laravel API Post to {$apiUrl}", [
                    'title'     => $finalTitle,
                    'image_url' => $formattedImageUrl,
                    'auth'      => !empty($settings->laravel_api_token) ? 'Bearer Token Included' : 'No Token'
                ]);

                $response = Http::timeout(120)
                    ->withOptions(['verify' => false])
                    ->withHeaders($headers)
                    ->post($apiUrl, $payload);

                if ($response && $response->successful()) {
                    $result['success'] = true;
                    $respData = $response->json();
                    $result['remote_id'] = $respData['post_id'] ?? ($respData['id'] ?? $remotePostId);
                    
                    $siteBase = rtrim($settings->laravel_site_url, '/');
                    $prefix   = trim($settings->laravel_route_prefix ?? 'news', '/');
                    
                    $result['published_url'] = $respData['live_url'] ?? ($respData['link'] ?? ($respData['url'] ?? ($siteBase . '/' . $prefix . '/' . $result['remote_id'])));
                    Log::info("✅ Default API Success. ID: {$result['remote_id']}");
                } else {
                    $status = $response ? $response->status() : '0';
                    $body   = $response ? $response->body() : 'No Response';
                    $result['error'] = "HTTP {$status}: " . Str::limit($body, 200);
                    Log::error("❌ Default API Failed: " . $body);
                }
            }
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
            Log::error("❌ API Connection Error: " . $e->getMessage());
        }

        return $result;
    }

    /**
     * Helper: Normalize image URL to fully qualified public URL
     */
    protected function normalizeImageUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // Protocol-relative URL //example.com/img.jpg
        if (str_starts_with($url, '//')) {
            return 'https:' . $url;
        }

        // Already fully qualified absolute URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        // Relative URL with leading slash
        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return url('/' . $url);
    }

    /**
     * Helper: Fetch binary data and MIME for image (checking local disk first)
     */
    protected function getImageBinaryAndMime($imageUrl)
    {
        if (empty($imageUrl)) {
            return null;
        }

        // 1. Check local storage / public directory first
        $localPath = null;
        if (str_contains($imageUrl, '/storage/')) {
            $storageSubPath = Str::after($imageUrl, '/storage/');
            $candidate = storage_path('app/public/' . $storageSubPath);
            if (file_exists($candidate)) {
                $localPath = $candidate;
            }
        } elseif (str_starts_with($imageUrl, '/') && !str_starts_with($imageUrl, '//')) {
            $candidate = public_path(ltrim($imageUrl, '/'));
            if (file_exists($candidate)) {
                $localPath = $candidate;
            }
        }

        if ($localPath && file_exists($localPath)) {
            $bytes = @file_get_contents($localPath);
            if ($bytes !== false && strlen($bytes) > 0) {
                $mime = mime_content_type($localPath) ?: 'image/jpeg';
                $filename = basename($localPath);
                return ['bytes' => $bytes, 'mime' => $mime, 'filename' => $filename];
            }
        }

        // 2. Remote HTTP fetch
        $normalizedUrl = $this->normalizeImageUrl($imageUrl);
        if (!$normalizedUrl) {
            return null;
        }

        try {
            $resp = Http::timeout(25)
                ->withOptions(['verify' => false])
                ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Subeditor24/2.0'])
                ->get($normalizedUrl);

            if ($resp->successful() && strlen($resp->body()) > 0) {
                $bytes = $resp->body();
                $mime = $resp->header('Content-Type') ?: 'image/jpeg';
                $pathOnly = parse_url($normalizedUrl, PHP_URL_PATH);
                $filename = basename($pathOnly) ?: ('news_image_' . time() . '.jpg');
                return ['bytes' => $bytes, 'mime' => $mime, 'filename' => $filename];
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Image Fetch Failed for {$normalizedUrl}: " . $e->getMessage());
        }

        return null;
    }
}