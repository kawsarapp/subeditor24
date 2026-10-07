<?php

namespace App\Jobs;

use App\Models\NewsItem;
use App\Services\AIWriterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Notifications\AIRewriteCompletedNotification;

class GenerateAIContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $newsId;
    protected $userId;

    public $tries = 1;
    public $timeout = 120;    

    public function __construct($newsId, $userId)
    {
        $this->newsId = $newsId;
        $this->userId = $userId;
    }

    public function handle(AIWriterService $aiWriter)
    {
        Log::info("🚀 AI Job Started for News ID: {$this->newsId}");

        $news = NewsItem::withoutGlobalScopes()->find($this->newsId);
        $user = User::find($this->userId);

        if (!$news) {
            Log::error("❌ News not found ID: {$this->newsId}");
            return;
        }

        // 🔥 Staff ID বের করা (যাতে ব্যাকগ্রাউন্ডেও স্টাফ ট্র্যাক হয়)
        $staffId = ($user && in_array($user->role, ['staff', 'reporter'])) ? $user->id : null;

        $news->update([
            'status' => 'processing',
            'staff_id' => $staffId ?? $news->staff_id
        ]);

        try {
            $title = $news->title ?? '';
            $body = $news->content ?? '';

            $textOnly = strip_tags(str_replace(['</p>', '<br>', '</div>'], ["\n\n", "\n", "\n"], $body));
            $cleanBody = trim($textOnly);
            
            $fullContext = "Headline: " . $title . "\n\nDetails: " . $cleanBody;

            // 🔥 পরিবর্তন: চেক করা হচ্ছে নিউজটি আগে রি-রাইট করা হয়েছে কি না
            $isRetry = (bool) $news->is_rewritten;

            // 🌍 টার্গেট ল্যাঙ্গুয়েজ নির্ধারণ (Smart Language Resolution)
            $effectiveUserId = $this->staffId ?? $news->user_id;
            $userTargetLang = \App\Models\UserSetting::where('user_id', $effectiveUserId)->value('target_language');
            
            $websiteTargetLang = null;
            if ($news->website_id) {
                $website = \App\Models\Website::withoutGlobalScopes()->find($news->website_id);
                if ($website && !empty($website->target_language)) {
                    $websiteTargetLang = $website->target_language;
                }
            }

            // Priority: User's direct profile setting -> Website specific profile -> Content auto-detection
            if (!empty($userTargetLang) && in_array($userTargetLang, ['bn', 'en'])) {
                $targetLanguage = $userTargetLang;
            } elseif (!empty($websiteTargetLang) && in_array($websiteTargetLang, ['bn', 'en'])) {
                $targetLanguage = $websiteTargetLang;
            } else {
                // Auto-detect: If headline or content contains Bengali script, write in Bangla
                $isBangla = (bool) preg_match('/[\x{0980}-\x{09FF}]/u', $title . ' ' . $cleanBody);
                $targetLanguage = $isBangla ? 'bn' : 'en';
            }

            // 🔥 পরিবর্তন: rewrite মেথডে $isRetry, $effectiveUserId এবং $targetLanguage প্যারামিটারটি পাস করা হচ্ছে
            $aiResponse = $aiWriter->rewrite($fullContext, $title, $isRetry, $effectiveUserId, $targetLanguage);

            $cleanText = strip_tags($aiResponse['content']);
            $cleanText = preg_replace('/\s+/', ' ', $cleanText);
            $autoSummary = !empty($aiResponse['meta_description']) ? $aiResponse['meta_description'] : mb_substr(trim($cleanText), 0, 150);
            
            $focusKw = !empty($aiResponse['focus_keyword']) ? trim($aiResponse['focus_keyword']) : '';
            $tagsList = (!empty($aiResponse['tags']) && is_array($aiResponse['tags'])) ? implode(', ', $aiResponse['tags']) : $focusKw;

            $news->update([
                'ai_title' => $aiResponse['title'] ?? $news->title,
                'ai_content' => $aiResponse['content'],
                'short_summary' => $news->short_summary ?: $autoSummary,
                'tags' => $news->tags ?: ($tagsList ?: $focusKw),
                'hashtags' => $news->hashtags ?: ($tagsList ?: $focusKw),
                'status' => 'draft',
                'is_rewritten' => true,
                'staff_id' => $staffId ?? $news->staff_id, // 🔥 স্টাফ আইডি সেভ করা হলো
                'error_message' => null 
            ]);

            Log::info("✅ AI Job Completed. ID: {$this->newsId}");

            if ($user) {
                $safeTitle = mb_convert_encoding($news->ai_title, 'UTF-8', 'UTF-8');
                $user->notify(new AIRewriteCompletedNotification($safeTitle, $news->id));
            }

            // 🎙️ Auto-generate AI Audio Narration if enabled in settings
            try {
                $ttsSetting = $user ? \App\Models\UserSetting::where('user_id', $user->id)->first() : null;
                if ($ttsSetting && $ttsSetting->tts_enabled && ($ttsSetting->tts_auto_generate_on_draft ?? true)) {
                    \App\Modules\AudioNarration\Jobs\GenerateNewsAudioJob::dispatch($news->id, $user ? $user->id : null);
                }
            } catch (\Throwable $audioEx) {
                Log::warning("⚠️ Audio Narration Auto-Dispatch Error: " . $audioEx->getMessage());
            }

        } catch (\Exception $e) {
            
            $msg = $e->getMessage();
            $userMessage = "Rewrite Failed. Try Again."; // ডিফল্ট মেসেজ

            if ($msg === "SHORT_CONTENT") {
                $userMessage = "News too short. Please scrape again.";
            } 
            elseif ($msg === "API_KEY_MISSING" || $msg === "API_KEY_INVALID") {
                $userMessage = "System Error: API Key Missing/Invalid.";
            }
            elseif ($msg === "SERVER_BUSY" || $msg === "RATE_LIMIT_EXCEEDED") {
                $userMessage = "AI Server Busy. Please try again in 5 mins.";
            }

            elseif ($msg === "SERVER_ERROR") {
                $userMessage = "AI Provider Error. Try again later.";
            }
			
            elseif ($msg === "INSUFFICIENT_BALANCE") {
                $userMessage = "AI Credits Finished (Contact Admin).";
            }
			
            elseif ($msg === "ALL_AI_FAILED") {
                $userMessage = "All AI Services are currently down. Try again later.";
            }


            Log::error("🔥 AI Job Failed: $msg");

            $news->update([
                'status' => 'failed',
                'error_message' => $userMessage,
                'staff_id' => $staffId ?? $news->staff_id, // 🔥 স্টাফ আইডি সেভ করা হলো
                'ai_content' => null
            ]);
            
            $this->fail($e);
        }
    }
}