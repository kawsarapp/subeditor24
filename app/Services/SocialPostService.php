<?php

namespace App\Services;

use App\Models\FacebookPage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class SocialPostService
{
    /**
     * Post to active Facebook Pages of the user.
     * If $selectedPageIds is non-empty, only those pages receive the post.
     *
     * @param  mixed  $settings
     * @param  string $title
     * @param  string $imagePathOrUrl
     * @param  string $newsLink
     * @param  array  $selectedPageIds  DB IDs of FacebookPage records to post to
     * @return array  [['page_name' => ..., 'success' => bool, 'message' => ...], ...]
     */
    public function postToFacebook($settings, $title, $imagePathOrUrl, $newsLink, array $selectedPageIds = [], ?string $firstComment = null)
    {
        // ১. User এর সব active Facebook page গুলো নিই
        $user  = User::with('facebookPages')->find($settings->user_id);
        $pages = $user?->facebookPages()->where('is_active', true)->get() ?? collect();

        // ২. যদি specific page IDs দেওয়া থাকে, শুধু সেগুলোতেই পোস্ট হবে
        if (!empty($selectedPageIds)) {
            $pages = $pages->filter(fn($p) => in_array($p->id, $selectedPageIds))->values();
        }

        // ৩. যদি নতুন multi-page সিস্টেমে কোনো page না থাকে,
        //    তাহলে পুরনো user_settings এর single page দিয়ে fallback করি
        if ($pages->isEmpty()) {
            if (empty($settings->fb_page_id) || empty($settings->fb_access_token)) {
                return [['success' => false, 'message' => 'কোনো Facebook Page কানেক্ট করা নেই।']];
            }
            $fakePage = (object)[
                'page_name'    => 'My Facebook Page',
                'page_id'      => $settings->fb_page_id,
                'access_token' => $settings->fb_access_token,
                'comment_link' => $settings->fb_comment_link ?? false,
            ];
            $pages = collect([$fakePage]);
        }

        $results = [];

        // ৪. প্রতিটি page-এ আলাদাভাবে পোস্ট করি
        foreach ($pages as $page) {
            $result                = $this->sendToOnePage($page, $title, $imagePathOrUrl, $newsLink, $firstComment);
            $result['page_name']   = $page->page_name ?? 'Unknown Page';
            $results[]             = $result;
        }

        return $results;
    }

    /**
     * একটি নির্দিষ্ট page-এ পোস্ট পাঠানো (internal helper)
     */
    private function sendToOnePage($page, $title, $imagePathOrUrl, $newsLink, ?string $firstComment = null)
    {
        try {
            $postId = null;
            $data   = [];

            if (!empty($imagePathOrUrl)) {
                // Meta Best Practice for Timeline Feed Story:
                // Step 1: Upload image asset to /{page_id}/photos with published=false
                // Step 2: Publish feed story to /{page_id}/feed with attached_media to guarantee timeline visibility
                $photoUploadEndpoint = "https://graph.facebook.com/v19.0/{$page->page_id}/photos";
                $photoPayload = [
                    'access_token' => $page->access_token,
                    'published'    => false,
                ];

                $photoResponse = $this->sendPhotoRequest($photoUploadEndpoint, $photoPayload, $imagePathOrUrl);
                $photoData     = $photoResponse->json() ?? [];
                $photoId       = $photoData['id'] ?? null;

                if ($photoResponse->successful() && !empty($photoId)) {
                    // Step 2: Publish feed story
                    try {
                        $feedEndpoint = "https://graph.facebook.com/v19.0/{$page->page_id}/feed";
                        $feedPayload  = [
                            'message'        => $title,
                            'attached_media' => json_encode([['media_fbid' => (string) $photoId]]),
                            'access_token'   => $page->access_token,
                        ];

                        $feedResponse = Http::withoutVerifying()->timeout(30)->post($feedEndpoint, $feedPayload);
                        $feedData     = $feedResponse->json() ?? [];

                        if ($feedResponse->successful() && !empty($feedData['id'])) {
                            $postId = $feedData['id'];
                            $data   = $feedData;
                        } else {
                            Log::warning("⚠️ FB auto-post feed story with attached_media failed [{$page->page_name}]: " . json_encode($feedData) . ". Falling back to direct /photos.");
                        }
                    } catch (\Throwable $feedEx) {
                        Log::warning("⚠️ FB auto-post feed story exception [{$page->page_name}]: " . $feedEx->getMessage() . ". Falling back to direct /photos.");
                    }
                } else {
                    Log::warning("⚠️ FB auto-post unpublished photo upload failed [{$page->page_name}]: " . json_encode($photoData) . ". Falling back to direct /photos.");
                }

                // Fallback: If 2-step publishing did not produce postId, fallback to direct /photos with published=true & no_story=false
                if (!$postId) {
                    $fallbackPayload = [
                        'caption'      => $title,
                        'message'      => $title,
                        'access_token' => $page->access_token,
                        'published'    => true,
                        'no_story'     => false,
                    ];

                    $fallbackResponse = $this->sendPhotoRequest($photoUploadEndpoint, $fallbackPayload, $imagePathOrUrl);
                    $data             = $fallbackResponse->json() ?? [];
                    $postId           = $data['post_id'] ?? $data['id'] ?? null;
                }
            } else {
                // Text Only: POST /{page_id}/feed with message
                $feedEndpoint = "https://graph.facebook.com/v19.0/{$page->page_id}/feed";
                $feedPayload  = [
                    'message'      => $title,
                    'access_token' => $page->access_token,
                    'published'    => true,
                ];

                $feedResponse = Http::withoutVerifying()->timeout(30)->post($feedEndpoint, $feedPayload);
                $data         = $feedResponse->json() ?? [];
                if ($feedResponse->successful() && !empty($data['id'])) {
                    $postId = $data['id'];
                }
            }

            if ($postId) {
                // Comment-এ link দেওয়া: custom first comment দেওয়া থাকলে সেটা,
                // অন্যথায় news link থাকলে স্বয়ংক্রিয়ভাবে 'বিস্তারিত পড়ুন: ...' দেওয়া হবে।
                $resolvedNewsLink = $newsLink;
                if ($newsLink instanceof \App\Models\NewsItem) {
                    $resolvedNewsLink = $newsLink->target_post_url ?: $newsLink->live_url;
                }

                $firstCommentTrimmed = trim((string)$firstComment);
                if ($newsLink instanceof \App\Models\NewsItem && !empty($firstCommentTrimmed) && !empty($newsLink->original_link) && str_contains($firstCommentTrimmed, $newsLink->original_link)) {
                    $firstCommentTrimmed = !empty($resolvedNewsLink)
                        ? str_replace($newsLink->original_link, $resolvedNewsLink, $firstCommentTrimmed)
                        : trim(str_replace(['বিস্তারিত পড়ুন: ' . $newsLink->original_link, $newsLink->original_link], '', $firstCommentTrimmed));
                }

                $commentMessage = !empty($firstCommentTrimmed) 
                    ? $firstCommentTrimmed 
                    : (!empty($resolvedNewsLink) ? 'বিস্তারিত পড়ুন: ' . $resolvedNewsLink : null);

                if (!empty($commentMessage)) {
                    try {
                        Http::withoutVerifying()->timeout(15)->post("https://graph.facebook.com/v19.0/{$postId}/comments", [
                            'message'      => $commentMessage,
                            'access_token' => $page->access_token,
                        ]);
                    } catch (\Exception $e) {
                        Log::error("❌ FB Comment Error [{$page->page_name}]: " . $e->getMessage());
                    }
                }

                Log::info("✅ FB Post Success [{$page->page_name}]: $postId");
                return ['success' => true, 'message' => null];
            } else {
                $errorData   = $data['error'] ?? [];
                $detailedMsg = $errorData['error_user_msg'] ?? null;
                $titleMsg    = $errorData['error_user_title'] ?? null;
                $genericMsg  = $errorData['message'] ?? 'Unknown Facebook Error';
                $finalError  = $detailedMsg ?: ($titleMsg ? "$titleMsg: $genericMsg" : $genericMsg);

                Log::error("❌ FB Error [{$page->page_name}]: " . json_encode($data));
                return ['success' => false, 'message' => $finalError];
            }

        } catch (\Exception $e) {
            Log::error("❌ FB Exception [{$page->page_name}]: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Post to Telegram Channel
     * Returns: ['success' => bool, 'message' => string|null]
     */
    public function postToTelegram($settings, $title, $imagePathOrUrl, $newsLink)
    {
        if (empty($settings->telegram_channel_id) || empty($settings->telegram_bot_token)) {
            return ['success' => false, 'message' => 'Setup Missing'];
        }

        if ($newsLink instanceof \App\Models\NewsItem) {
            $newsLink = $newsLink->target_post_url ?: $newsLink->live_url;
        }

        try {
            $botToken = $settings->telegram_bot_token;
            $chatId   = $settings->telegram_channel_id;
            $endpoint = "https://api.telegram.org/bot{$botToken}/sendPhoto";

            $caption = "📢 <b>{$title}</b>";
            if (!empty($newsLink)) {
                $caption .= "\n\n👇 বিস্তারিত পড়তে লিংকে ক্লিক করুন:\n{$newsLink}";
            }

            $payload = [
                'chat_id'    => $chatId,
                'caption'    => $caption,
                'parse_mode' => 'HTML',
            ];

            if (file_exists($imagePathOrUrl)) {
                $response = Http::attach('photo', file_get_contents($imagePathOrUrl), 'news.jpg')
                    ->post($endpoint, $payload);
            } else {
                $payload['photo'] = $imagePathOrUrl;
                $response         = Http::post($endpoint, $payload);
            }

            if ($response->successful()) {
                Log::info('✅ Telegram Sent');
                return ['success' => true, 'message' => null];
            } else {
                $errorMsg = $response->json()['description'] ?? 'Unknown Telegram Error';
                Log::error('❌ Telegram Failed: ' . $errorMsg);
                return ['success' => false, 'message' => $errorMsg];
            }

        } catch (\Exception $e) {
            Log::error('❌ Telegram Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Post to Twitter / X
     * Returns: ['success' => bool, 'message' => string|null]
     */
    public function postToTwitter($settings, $title, $imagePathOrUrl, $newsLink)
    {
        if (
            empty($settings->twitter_api_key) || 
            empty($settings->twitter_api_secret) || 
            empty($settings->twitter_access_token) || 
            empty($settings->twitter_access_secret)
        ) {
            return ['success' => false, 'message' => 'Twitter Setup Missing'];
        }

        if ($newsLink instanceof \App\Models\NewsItem) {
            $newsLink = $newsLink->target_post_url ?: $newsLink->live_url;
        }

        try {
            $tweetMessage = !empty($newsLink) 
                ? "📢 {$title}\n\n👇 বিস্তারিত পড়ুন:\n{$newsLink}" 
                : "📢 {$title}";

            $connection = new \Abraham\TwitterOAuth\TwitterOAuth(
                $settings->twitter_api_key,
                $settings->twitter_api_secret,
                $settings->twitter_access_token,
                $settings->twitter_access_secret
            );
            $connection->setApiVersion('1.1');

            $tmpPath = $imagePathOrUrl;
            $isUrl = filter_var($imagePathOrUrl, FILTER_VALIDATE_URL);
            
            if ($isUrl) {
                $tmpPath = storage_path('app/public/tmp_twitter_'.time().'.jpg');
                file_put_contents($tmpPath, file_get_contents($imagePathOrUrl));
            }

            $media = $connection->upload('media/upload', ['media' => $tmpPath]);

            if ($isUrl && file_exists($tmpPath)) {
                try {
                    if (is_file($tmpPath)) {
                        File::delete($tmpPath);
                    }
                } catch (\Throwable $e) {
                    Log::warning("⚠️ Failed to delete Twitter temp file [{$tmpPath}]: " . $e->getMessage());
                }
            }

            if (isset($media->media_id_string)) {
                $connection->setApiVersion('2');
                
                $parameters = [
                    'text' => $tweetMessage,
                    'media' => ['media_ids' => [$media->media_id_string]]
                ];
                
                $result = $connection->post('tweets', $parameters);

                if ($connection->getLastHttpCode() == 201) {
                    Log::info('✅ Twitter Sent');
                    return ['success' => true, 'message' => null];
                } else {
                    $errorMsg = $result->detail ?? 'Unknown Twitter Post Error';
                    Log::error('❌ Twitter Failed: ' . json_encode($result));
                    return ['success' => false, 'message' => $errorMsg];
                }
            } else {
                return ['success' => false, 'message' => 'Failed to upload media to Twitter'];
            }

        } catch (\Exception $e) {
            Log::error('❌ Twitter Exception: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Publish a direct post to Facebook Page(s) with optional image and first comment.
     * Supports a single page (FacebookPage model or ID) or multiple pages (array/Collection).
     *
     * @param  mixed        $pageId        FacebookPage model, DB ID, FB page_id, or array/Collection of them
     * @param  string|null  $message       Post text/caption
     * @param  mixed        $image         UploadedFile, local path, base64 data, URL, or null
     * @param  string|null  $firstComment  Optional first comment
     * @return array                       Summary array (or array of summary arrays if multiple)
     */
    public function publishDirectPost($pageId, ?string $message = null, $image = null, ?string $firstComment = null)
    {
        if (is_array($pageId) || $pageId instanceof \Illuminate\Support\Collection) {
            return $this->publishDirectToPages($pageId, $message, $image, $firstComment);
        }

        return $this->publishDirectToOnePage($pageId, $message, $image, $firstComment);
    }

    /**
     * Publish direct post to multiple Facebook Pages in a loop.
     *
     * @param  iterable     $pages
     * @param  string|null  $message
     * @param  mixed        $image
     * @param  string|null  $firstComment
     * @return array        Array of status summaries for each page
     */
    public function publishDirectToPages(iterable $pages, ?string $message = null, $image = null, ?string $firstComment = null): array
    {
        $summaries = [];
        foreach ($pages as $page) {
            $summaries[] = $this->publishDirectToOnePage($page, $message, $image, $firstComment);
        }
        return $summaries;
    }

    /**
     * Publish direct post to a single Facebook Page.
     *
     * @param  mixed        $pageIdentifier
     * @param  string|null  $message
     * @param  mixed        $image
     * @param  string|null  $firstComment
     * @return array        Status summary: [page_id, page_name, success, post_id, comment_status, comment_id, error]
     */
    public function publishDirectToOnePage($pageIdentifier, ?string $message = null, $image = null, ?string $firstComment = null): array
    {
        // 1. Resolve page model
        $page = $this->resolveFacebookPage($pageIdentifier);

        if (!$page) {
            $rawId = is_object($pageIdentifier) ? ($pageIdentifier->page_id ?? $pageIdentifier->id ?? null) : $pageIdentifier;
            return [
                'page_id'        => $rawId,
                'page_name'      => is_object($pageIdentifier) ? ($pageIdentifier->page_name ?? 'Unknown Page') : 'Unknown Page',
                'success'        => false,
                'post_id'        => null,
                'comment_status' => 'skipped',
                'comment_id'     => null,
                'error'          => 'ফেসবুক পেজ খুঁজে পাওয়া যায়নি।',
            ];
        }

        $pageName    = $page->page_name ?? 'Facebook Page';
        $fbPageId    = $page->page_id ?? null;
        $accessToken = $page->access_token ?: ($page->page_access_token ?? null);

        if (!empty($accessToken)) {
            $accessToken = trim(preg_replace('/^Bearer\s+/i', '', (string)$accessToken), " \t\n\r\0\x0B\"'");
        }

        if (empty($fbPageId) || empty($accessToken)) {
            return [
                'page_id'        => $fbPageId,
                'page_name'      => $pageName,
                'success'        => false,
                'post_id'        => null,
                'comment_status' => 'skipped',
                'comment_id'     => null,
                'error'          => "পেজ '{$pageName}' এর অ্যাক্সেস টোকেন বা পেজ আইডি অনুপস্থিত।",
            ];
        }

        try {
            $hasImage = !empty($image);
            $postId = null;
            $data = [];

            if ($hasImage) {
                // Meta Best Practice for Timeline Feed Story:
                // Step 1: Upload image asset to /{page_id}/photos with published=false
                // Step 2: Publish feed story to /{page_id}/feed with attached_media to guarantee timeline visibility
                $photoUploadEndpoint = "https://graph.facebook.com/v19.0/{$fbPageId}/photos";
                $photoPayload = [
                    'access_token' => $accessToken,
                    'published'    => false,
                ];

                $photoResponse = $this->sendPhotoRequest($photoUploadEndpoint, $photoPayload, $image);
                $photoData = $photoResponse->json() ?? [];

                if ($photoResponse->successful() && !empty($photoData['id'])) {
                    $mediaFbid = $photoData['id'];

                    // Step 2: Publish feed story to guarantee timeline visibility
                    try {
                        $feedEndpoint = "https://graph.facebook.com/v19.0/{$fbPageId}/feed";
                        $feedPayload = [
                            'message'        => $message ?? '',
                            'attached_media' => json_encode([['media_fbid' => (string) $mediaFbid]]),
                            'access_token'   => $accessToken,
                        ];

                        $response = Http::withoutVerifying()->timeout(35)->post($feedEndpoint, $feedPayload);
                        $data = $response->json() ?? [];

                        if ($response->successful() && !empty($data['id'])) {
                            $postId = $data['id'];
                        } else {
                            Log::warning("⚠️ FB 2-step feed post failed [{$pageName}]: " . json_encode($data) . ". Falling back to direct /photos.");
                        }
                    } catch (\Throwable $feedEx) {
                        Log::warning("⚠️ FB 2-step feed post exception [{$pageName}]: " . $feedEx->getMessage() . ". Falling back to direct /photos.");
                    }
                } else {
                    Log::warning("⚠️ FB unpublished photo upload failed [{$pageName}]: " . json_encode($photoData) . ". Falling back to direct /photos.");
                }

                // Fallback: If 2-step publishing did not produce postId, fallback to direct /photos with published=true & no_story=false
                if (!$postId) {
                    $fallbackPayload = [
                        'caption'      => $message ?? '',
                        'message'      => $message ?? '',
                        'access_token' => $accessToken,
                        'published'    => true,
                        'no_story'     => false,
                    ];
                    $response = $this->sendPhotoRequest($photoUploadEndpoint, $fallbackPayload, $image);
                    $data = $response->json() ?? [];
                    $postId = $data['post_id'] ?? $data['id'] ?? null;
                }
            } else {
                // Text Only: POST /{page_id}/feed with message
                $endpoint = "https://graph.facebook.com/v19.0/{$fbPageId}/feed";
                $payload = [
                    'message'      => $message ?? '',
                    'access_token' => $accessToken,
                    'published'    => 'true',
                ];

                $response = Http::withoutVerifying()->timeout(35)->post($endpoint, $payload);
                $data = $response->json() ?? [];
                if ($response->successful() && !empty($data['id'])) {
                    $postId = $data['id'];
                }
            }

            // Verify post id exists

            if ($postId) {
                Log::info("✅ FB Direct Post Success [{$pageName}] Page ID [{$fbPageId}]: Post ID {$postId}");

                // First Comment handling
                $commentStatus = 'skipped';
                $commentId = null;

                if (!empty(trim((string)$firstComment))) {
                    try {
                        $commentRes = Http::withoutVerifying()->timeout(20)
                            ->post("https://graph.facebook.com/v19.0/{$postId}/comments", [
                                'message'      => trim((string)$firstComment),
                                'access_token' => $accessToken,
                            ]);

                        $commentData = $commentRes->json() ?? [];

                        if ($commentRes->successful() && !empty($commentData['id'])) {
                            $commentId = $commentData['id'];
                            $commentStatus = 'published';
                            Log::info("✅ FB First Comment Success [{$pageName}] on Post [{$postId}]: Comment ID {$commentId}");
                        } else {
                            $commentStatus = 'failed';
                            $errDetail = $commentData['error']['message'] ?? 'Unknown error';
                            Log::warning("⚠️ FB First Comment Failed [{$pageName}] on Post [{$postId}]: {$errDetail}");
                        }
                    } catch (\Throwable $ce) {
                        $commentStatus = 'failed';
                        Log::warning("⚠️ FB First Comment Exception [{$pageName}] on Post [{$postId}]: " . $ce->getMessage());
                    }
                }

                return [
                    'page_id'        => $fbPageId,
                    'page_name'      => $pageName,
                    'success'        => true,
                    'post_id'        => $postId,
                    'comment_status' => $commentStatus,
                    'comment_id'     => $commentId,
                    'error'          => null,
                ];
            } else {
                $errorData   = $data['error'] ?? [];
                $detailedMsg = $errorData['error_user_msg'] ?? null;
                $titleMsg    = $errorData['error_user_title'] ?? null;
                $genericMsg  = $errorData['message'] ?? 'Unknown Facebook Graph API Error';
                $finalError  = $detailedMsg ?: ($titleMsg ? "{$titleMsg}: {$genericMsg}" : $genericMsg);

                Log::error("❌ FB Direct Post Failed [{$pageName}]: " . json_encode($data));

                return [
                    'page_id'        => $fbPageId,
                    'page_name'      => $pageName,
                    'success'        => false,
                    'post_id'        => null,
                    'comment_status' => 'skipped',
                    'comment_id'     => null,
                    'error'          => $finalError,
                ];
            }

        } catch (\Throwable $e) {
            Log::error("❌ FB Direct Post Exception [{$pageName}]: " . $e->getMessage());

            return [
                'page_id'        => $fbPageId,
                'page_name'      => $pageName,
                'success'        => false,
                'post_id'        => null,
                'comment_status' => 'skipped',
                'comment_id'     => null,
                'error'          => $e->getMessage(),
            ];
        }
    }

    /**
     * Dispatch photo upload request via file attachment or public URL.
     */
    protected function sendPhotoRequest(string $endpoint, array $payload, $image)
    {
        // 1. UploadedFile
        if ($image instanceof UploadedFile && $image->isValid()) {
            return Http::withoutVerifying()->timeout(60)
                ->attach('source', file_get_contents($image->getRealPath()), $image->getClientOriginalName() ?: 'upload.jpg')
                ->post($endpoint, $payload);
        }

        // 2. Base64 data string (e.g. data:image/png;base64,...)
        if (is_string($image) && preg_match('/^data:image\/(\w+);base64,/', $image, $matches)) {
            $raw = substr($image, strpos($image, ',') + 1);
            $decoded = base64_decode($raw);
            $ext = strtolower($matches[1] ?? 'png');
            return Http::withoutVerifying()->timeout(60)
                ->attach('source', $decoded, 'image.' . $ext)
                ->post($endpoint, $payload);
        }

        // 3. Local file path check
        $localPath = null;
        if (is_string($image)) {
            if (file_exists($image)) {
                $localPath = $image;
            } elseif (file_exists(public_path($image))) {
                $localPath = public_path($image);
            } elseif (file_exists(storage_path('app/public/' . ltrim($image, '/')))) {
                $localPath = storage_path('app/public/' . ltrim($image, '/'));
            }
        }

        if ($localPath && is_file($localPath)) {
            return Http::withoutVerifying()->timeout(60)
                ->attach('source', file_get_contents($localPath), basename($localPath))
                ->post($endpoint, $payload);
        }

        // 4. URL string
        if (is_string($image) && filter_var($image, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($image);
            $host = $parsed['host'] ?? '';
            $isLocal = in_array($host, ['localhost', '127.0.0.1', '::1']) || str_ends_with($host, '.test') || str_ends_with($host, '.local');
            if ($isLocal && !empty($parsed['path'])) {
                $devPath = public_path(ltrim($parsed['path'], '/'));
                if (file_exists($devPath) && is_file($devPath)) {
                    return Http::withoutVerifying()->timeout(60)
                        ->attach('source', file_get_contents($devPath), basename($devPath))
                        ->post($endpoint, $payload);
                }
            }

            $payload['url'] = $image;
            return Http::withoutVerifying()->timeout(60)->post($endpoint, $payload);
        }

        // 5. Fallback: if raw binary or string given
        if (is_string($image) && strlen($image) > 0) {
            return Http::withoutVerifying()->timeout(60)
                ->attach('source', $image, 'photo.jpg')
                ->post($endpoint, $payload);
        }

        // Final fallback: normal post without attachment
        return Http::withoutVerifying()->timeout(60)->post($endpoint, $payload);
    }

    /**
     * Resolve FacebookPage instance from model, ID or array
     */
    protected function resolveFacebookPage($pageIdentifier): ?FacebookPage
    {
        if ($pageIdentifier instanceof FacebookPage) {
            return $pageIdentifier;
        }

        if (is_numeric($pageIdentifier)) {
            $page = FacebookPage::find($pageIdentifier);
            if ($page) {
                return $page;
            }
            return FacebookPage::where('page_id', (string)$pageIdentifier)->first();
        }

        if (is_string($pageIdentifier) && !empty($pageIdentifier)) {
            $page = FacebookPage::where('page_id', $pageIdentifier)->first();
            if ($page) {
                return $page;
            }
            return FacebookPage::find($pageIdentifier);
        }

        if (is_array($pageIdentifier)) {
            $p = new FacebookPage();
            $p->forceFill($pageIdentifier);
            return $p;
        }

        if (is_object($pageIdentifier) && isset($pageIdentifier->access_token) && isset($pageIdentifier->page_id)) {
            $p = new FacebookPage();
            $p->forceFill((array)$pageIdentifier);
            return $p;
        }

        return null;
    }
}