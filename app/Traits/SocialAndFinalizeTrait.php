<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Notifications\PostPublishedNotification;

trait SocialAndFinalizeTrait
{
    protected function executeFinalization($news, $user, $settings, $wpSuccess, $laravelSuccess, $socialOnly, $skipSocial, $remotePostId, $publishedUrl, $websiteImage, $socialImage, $hashtags, $finalTitle, $socialPoster, $cardGenerator)
    {
        if (!$wpSuccess && !$laravelSuccess && !$socialOnly) {
            throw new \Exception("Posting failed on all configured endpoints.");
        }

        // 🔥 Staff ID বের করা (Job-এর $this->userId থেকে)
        $staffId = ($this->userId != $user->id) ? $this->userId : null;

        DB::transaction(function () use ($news, $user, $remotePostId, $publishedUrl, $websiteImage, $socialOnly, $hashtags, $staffId) {
            $updateData = [
                'is_posted' => true, 'posted_at' => now(), 'status' => 'published',
                'target_post_url' => $publishedUrl ?: $news->target_post_url,
                'error_message' => null, 'hashtags' => $hashtags
            ];

            if ($remotePostId) $updateData['wp_post_id'] = $remotePostId;
            if (!$socialOnly) $updateData['thumbnail_url'] = $websiteImage;

            // 🔥 স্টাফ আইডি আপডেট
            if ($staffId) {
                $updateData['staff_id'] = $staffId;
            }

            $news->update($updateData);

            if (!$this->skipCreditDeduction && $user->role !== 'super_admin' && $user->credits > 0) {
                $user->decrement('credits');
                \App\Models\CreditHistory::create([
                    'user_id'        => $user->id,
                    'staff_id'       => $staffId,
                    'action_type'    => 'auto_post',
                    'description'    => 'Published/Updated via Job',
                    'credits_change' => -1,
                    'balance_after'  => $user->credits,
                ]);
            }
        });

        // 🔥 চেক করা হচ্ছে পোস্টটি স্টুডিও (Design) থেকে এসেছে কি না
        $isFromStudio = isset($this->customData['social_image']);

        // 🔥 Studio Publish Modal এ ইউজার যে specific page IDs সিলেক্ট করেছে
        $selectedPageIds = isset($this->customData['selected_fb_page_ids'])
            ? array_map('intval', (array) $this->customData['selected_fb_page_ids'])
            : [];

        // ফেসবুকে পোস্ট (selected pages অথবা সব active pages)
        $skipFb = $this->customData['skip_fb'] ?? false;
        $hasActiveFbPages = \App\Models\FacebookPage::where('user_id', $settings->user_id)->where('is_active', true)->exists();
        $shouldPostFb = ($settings->post_to_fb || $hasActiveFbPages || !empty($selectedPageIds)) && !$skipFb;

        // 🟢 শুধুমাত্র স্টুডিও থেকে আসলে সোশ্যাল মিডিয়ায় পোস্ট হবে
        if (!$skipSocial && $isFromStudio && ($shouldPostFb || $settings->post_to_telegram || $settings->post_to_twitter)) {

            $imageToPost = $socialImage;
            Log::info("✨ Studio Post Detected. Sending Design to Social Media.");

            $appUrl = config('app.url');
            if (strpos($imageToPost, $appUrl) !== false) {
                $relativePath = ltrim(strtok(str_replace($appUrl, '', $imageToPost), '?'), '/');
                if (file_exists(public_path($relativePath))) $imageToPost = public_path($relativePath);
            } elseif (strpos($imageToPost, '/storage/') !== false) {
                $parts = explode('/storage/', $imageToPost);
                if (count($parts) > 1 && file_exists(storage_path('app/public/' . strtok($parts[1], '?')))) {
                    $imageToPost = storage_path('app/public/' . strtok($parts[1], '?'));
                }
            }

            $newsLink      = $publishedUrl ?: ($news->target_post_url ?: ($news->live_url ?: $news->original_link));
            $captionToPost = ($this->customData['social_caption'] ?? $finalTitle) . (!empty($hashtags) ? "\n\n" . $hashtags : "");

            if ($shouldPostFb) {
                $customFirstComment = $this->customData['first_comment'] ?? null;
                if (!empty($customFirstComment) && !empty($news->original_link) && str_contains($customFirstComment, $news->original_link)) {
                    if (!empty($newsLink)) {
                        $customFirstComment = str_replace($news->original_link, $newsLink, $customFirstComment);
                    } else {
                        $customFirstComment = trim(str_replace(['বিস্তারিত পড়ুন: ' . $news->original_link, $news->original_link], '', $customFirstComment));
                    }
                }
                $fbResults = $socialPoster->postToFacebook($settings, $captionToPost, $imageToPost, $newsLink ?: $news, $selectedPageIds, $customFirstComment);

                // সব results loop করে যেকোনো ১টি success হলে 'success' রাখি
                $fbOverallSuccess = collect($fbResults)->contains('success', true);
                $fbFirstError     = collect($fbResults)->firstWhere('success', false)['message'] ?? null;

                $news->update([
                    'fb_status' => $fbOverallSuccess ? 'success' : 'failed',
                    'fb_error'  => $fbOverallSuccess ? null : $fbFirstError,
                ]);

                foreach ($fbResults as $r) {
                    $status = $r['success'] ? '✅' : '❌';
                    Log::info("{$status} FB [{$r['page_name']}]: " . ($r['message'] ?? 'OK'));
                }

                // 🔥 Alert User & Admin if any Facebook Page failed
                $failedPages = collect($fbResults)->where('success', false);
                if ($failedPages->isNotEmpty()) {
                    $failedSummary = $failedPages->map(fn($p) => ($p['page_name'] ?? 'Page') . ': ' . ($p['message'] ?? 'Failed'))->implode(' | ');
                    \App\Services\SystemAlertService::notifyIssue(
                        $user,
                        "ফেসবুক পেজ পোস্ট ত্রুটি",
                        "নিউজ '{$finalTitle}' এর ফেসবুক পোস্ট ব্যর্থ হয়েছে: {$failedSummary}",
                        'social',
                        $fbOverallSuccess ? 'warning' : 'error',
                        route('news.studio', $news->id),
                        ['news_id' => $news->id, 'failures' => $failedPages->values()->toArray()]
                    );
                }
            }

            // টেলিগ্রামে পোস্ট
            if ($settings->post_to_telegram) {
                $tgResult = $socialPoster->postToTelegram($settings, $captionToPost, $imageToPost, $newsLink);
                $news->update([
                    'tg_status' => $tgResult['success'] ? 'success' : 'failed',
                    'tg_error'  => $tgResult['message'] ?? null,
                ]);

                if (!$tgResult['success']) {
                    \App\Services\SystemAlertService::notifyIssue(
                        $user,
                        "টেলিগ্রাম পোস্ট ব্যর্থ",
                        "নিউজ '{$finalTitle}' টেলিগ্রামে পোস্ট হতে পারেনি: " . ($tgResult['message'] ?? 'ত্রুটি'),
                        'social',
                        'error',
                        route('news.studio', $news->id),
                        ['news_id' => $news->id, 'error' => $tgResult['message'] ?? null]
                    );
                }
            }

            // টুইটারে পোস্ট
            if ($settings->post_to_twitter) {
                $twitterResult = $socialPoster->postToTwitter($settings, $captionToPost, $imageToPost, $newsLink);
                if ($twitterResult['success']) {
                    Log::info("✅ Twitter Post Success for News #{$news->id}");
                } else {
                    $twError = $twitterResult['message'] ?? 'Unknown Error';
                    Log::error("❌ Twitter Post Failed for News #{$news->id}: " . $twError);
                    \App\Services\SystemAlertService::notifyIssue(
                        $user,
                        "টুইটার (X) পোস্ট ব্যর্থ",
                        "নিউজ '{$finalTitle}' টুইটারে পোস্ট হতে পারেনি: {$twError}",
                        'social',
                        'error',
                        route('news.studio', $news->id),
                        ['news_id' => $news->id, 'error' => $twError]
                    );
                }
            }

            // স্টুডিওর টেম্পরারি ইমেজ ডিলিট করে দেওয়া
            if (strpos($imageToPost, 'news-cards/studio') !== false) {
                try {
                    if (file_exists($imageToPost) && is_file($imageToPost)) {
                        \Illuminate\Support\Facades\File::delete($imageToPost);
                    }
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("⚠️ Failed to delete temporary studio card [{$imageToPost}]: " . $e->getMessage());
                }
            }
        } else {
            if ($isFromStudio && !$skipFb && !$hasActiveFbPages && empty($selectedPageIds) && !$settings->post_to_fb) {
                \App\Services\SystemAlertService::notifyIssue(
                    $user,
                    "ফেসবুক অটো-পোস্ট সক্রিয় নেই",
                    "নিউজ '{$finalTitle}' স্টুডিও থেকে পাঠানো হয়েছে, কিন্তু কোনো সক্রিয় ফেসবুক পেজ না থাকায় সোশ্যাল মিডিয়াতে পোস্ট করা যায়নি। Settings থেকে ফেসবুক পেজ অন বা যুক্ত করুন।",
                    'social',
                    'warning',
                    route('settings.index'),
                    ['news_id' => $news->id]
                );
            }
            if (!$isFromStudio) {
                Log::info("⏭️ Regular News Post. Skipping Social Media completely.");
            }
        }

        try { $user->notify(new PostPublishedNotification($finalTitle)); } catch (\Exception $e) {
            Log::warning("⚠️ PostPublishedNotification failed: " . $e->getMessage());
        }
    }
}