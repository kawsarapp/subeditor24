<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait WordPressPostingTrait
{
    protected function executeWordPressPost($wpService, $news, $user, $settings, $finalTitle, $finalContent, $categories, $websiteImage, $hashtags, $publishedUrl)
    {
        $result = ['success' => false, 'remote_id' => $news->wp_post_id, 'published_url' => $publishedUrl];

        try {
            if ($news->wp_post_id) {
                Log::info("🔄 Updating existing WordPress post: ID {$news->wp_post_id}");
                $postResult = $wpService->updatePost(
                    $news->wp_post_id, $news, $user, $finalTitle, $finalContent, $categories, $websiteImage, $hashtags
                );
            } else {
                Log::info("🆕 Creating new WordPress post");
                $postResult = $wpService->createPost(
                    $news, $user, $finalTitle, $finalContent, $categories, $websiteImage, $hashtags
                );
            }

            if ($postResult['success']) {
                $result['success'] = true;
                $result['remote_id'] = $postResult['post_id'];
                $result['published_url'] = $postResult['link'] ?? $publishedUrl;
                if (!empty($result['published_url'])) {
                    $news->update(['target_post_url' => $result['published_url']]);
                }
                Log::info("✅ WP Action Success: ID {$result['remote_id']} | Link: {$result['published_url']}");
            } else {
                $errorMsg = $postResult['message'] ?? 'Unknown WP Error';
                $result['error'] = $errorMsg;
                Log::error("❌ WP Action Failed: " . $errorMsg);
                \App\Services\SystemAlertService::notifyIssue(
                    $user,
                    "WordPress পোস্টিং ব্যর্থ",
                    "নিউজ '{$finalTitle}' WordPress সাইটে পোস্ট হতে পারেনি। কারণ: {$errorMsg}",
                    'publishing',
                    'error',
                    route('news.drafts'),
                    ['news_id' => $news->id, 'wp_url' => $settings->wp_url, 'error' => $errorMsg]
                );
                if (!$settings->post_to_laravel) throw new \Exception("WP Failed: " . $errorMsg);
            }
        } catch (\Exception $e) {
            Log::error("❌ WP Exception: " . $e->getMessage());
            $result['error'] = $e->getMessage();
            \App\Services\SystemAlertService::notifyIssue(
                $user,
                "WordPress কানেকশন ত্রুটি",
                "নিউজ '{$finalTitle}' WordPress এ আপলোড করার সময় ত্রুটি ঘটেছে: {$e->getMessage()}",
                'publishing',
                'error',
                route('news.drafts'),
                ['news_id' => $news->id, 'wp_url' => $settings->wp_url, 'error' => $e->getMessage()]
            );
            if (!$settings->post_to_laravel) throw $e;
        }

        return $result;
    }
}