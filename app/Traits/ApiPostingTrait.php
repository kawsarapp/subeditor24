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
                    try {
                        $imgResponse = Http::timeout(25)->withOptions(['verify' => false])->get($websiteImage);
                        if ($imgResponse->successful()) {
                            $multipart[] = [
                                'name'     => (string)$mapping['image'],
                                'contents' => $imgResponse->body(),
                                'filename' => basename(parse_url($websiteImage, PHP_URL_PATH)) ?: 'news_image.jpg'
                            ];
                            Log::info("🖼️ Binary Image attached for Multipart API");
                        }
                    } catch (\Exception $e) {
                        Log::warning("⚠️ Image Fetch Failed: " . $e->getMessage());
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

                    // Categories
                    if (isset($mapping['category'])) {
                        $jsonPayload[$mapping['category']] = $categoryValue;
                    }

                    // Image handling (URL or Base64)
                    if (isset($mapping['image']) && !empty($websiteImage)) {
                        if ($imageFormat === 'base64') {
                            try {
                                $imgResp = Http::timeout(25)->withOptions(['verify' => false])->get($websiteImage);
                                if ($imgResp->successful()) {
                                    $mime = $imgResp->header('Content-Type') ?: 'image/jpeg';
                                    $jsonPayload[$mapping['image']] = 'data:' . $mime . ';base64,' . base64_encode($imgResp->body());
                                } else {
                                    $jsonPayload[$mapping['image']] = $websiteImage;
                                }
                            } catch (\Exception $e) {
                                $jsonPayload[$mapping['image']] = $websiteImage;
                            }
                        } else {
                            $jsonPayload[$mapping['image']] = $websiteImage;
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

                $apiUrl = $baseUrl . '/api/external-news-post';
                $payload = [
                    'token'         => $settings->laravel_api_token,
                    'title'         => $finalTitle,
                    'content'       => $finalContent,
                    'image_url'     => $websiteImage,
                    'hashtags'      => $hashtags,
                    'slug'          => Str::slug($finalTitle),
                    'category_name' => $news->category ?? 'General',
                    'category_ids'  => $categories,
                    'original_link' => $news->original_link ?? '',
                    'published_at'  => now()->format('Y-m-d H:i:s')
                ];
                
                if ($news->wp_post_id) {
                    $payload['remote_id'] = $news->wp_post_id;
                }

                $response = Http::timeout(120)
                    ->withOptions(['verify' => false])
                    ->withHeaders([
                        'Accept'     => 'application/json',
                        'User-Agent' => 'Subeditor24-Publisher/2.0 (+https://subeditor24.com)'
                    ])
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
}