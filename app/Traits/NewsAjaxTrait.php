<?php

namespace App\Traits;

use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

trait NewsAjaxTrait
{
    // 🔥 হেল্পার ফাংশন: স্টাফ বা রিপোর্টার হলে তার অ্যাডমিনকে বের করবে
    private function getEffectiveAdminForAjax() {
        $user = Auth::user();
        return in_array($user->role, ['staff', 'reporter']) ? User::find($user->parent_id) : $user;
    }

    public function proxyImage(Request $request)
    {
        $url = $request->query('url');
        if (!$url) abort(404);
        $cacheKey = 'proxy_img_' . md5($url);

        $imageData = Cache::remember($cacheKey, now()->addDays(7), function () use ($url) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ])->timeout(15)->get($url);

                if ($response->successful()) {
                    return ['body' => base64_encode($response->body()), 'type' => $response->header('Content-Type')];
                }
            } catch (\Exception $e) {}
            return null;
        });

        if (!$imageData) abort(404);
        return response(base64_decode($imageData['body']))
            ->header('Content-Type', $imageData['type'])
            ->header('Cache-Control', 'public, max-age=2592000, immutable')
            ->header('Access-Control-Allow-Origin', '*');
    }

    public function suggestLinks(Request $request)
    {
        $keyword = trim($request->input('keyword', ''));
        $focusKeyword = trim($request->input('focus_keyword', ''));
        $title = trim($request->input('title', ''));
        $currentNewsId = $request->input('news_id');

        $adminUser = $this->getEffectiveAdminForAjax();
        $settings = $adminUser->settings;

        // Base query: only published or live/rewritten news of this tenant
        $query = \App\Models\NewsItem::withoutGlobalScopes()
            ->whereIn('user_id', array_unique([$adminUser->id, Auth::id()]))
            ->where(function($q) {
                $q->where('status', 'published')
                  ->orWhere('is_posted', true)
                  ->orWhere('is_rewritten', true);
            });

        if (!empty($currentNewsId)) {
            $query->where('id', '!=', $currentNewsId);
        }

        $applyTermFilter = function($subQ, $term) {
            $cleanTerm = trim($term);
            if (empty($cleanTerm)) return;
            
            // If short ASCII term (<= 3 chars, e.g. "AI", "US", "UK"), use word boundary to avoid false positives (e.g. "remain", "contain")
            if (preg_match('/^[a-zA-Z0-9]{1,3}$/', $cleanTerm)) {
                $pattern = "\\b" . preg_quote($cleanTerm, '/') . "\\b";
                $subQ->orWhereRaw("title REGEXP ?", [$pattern])
                     ->orWhereRaw("ai_title REGEXP ?", [$pattern])
                     ->orWhere('tags', 'LIKE', "%{$cleanTerm}%")
                     ->orWhere('hashtags', 'LIKE', "%{$cleanTerm}%");
            } else {
                $subQ->orWhere('title', 'LIKE', "%{$cleanTerm}%")
                     ->orWhere('ai_title', 'LIKE', "%{$cleanTerm}%")
                     ->orWhere('tags', 'LIKE', "%{$cleanTerm}%")
                     ->orWhere('hashtags', 'LIKE', "%{$cleanTerm}%");
            }
        };

        // Case 1: Explicit search term typed by user
        if (!empty($keyword)) {
            $terms = array_filter(explode(' ', $keyword));
            $query->where(function($q) use ($keyword, $terms, $applyTermFilter) {
                $applyTermFilter($q, $keyword);
                foreach ($terms as $term) {
                    if (mb_strlen($term) >= 2) {
                        $applyTermFilter($q, $term);
                    }
                }
            });
        } 
        // Case 2: Auto-suggest by Focus Keywords
        elseif (!empty($focusKeyword)) {
            $kwList = array_filter(array_map('trim', explode(',', $focusKeyword)));
            $query->where(function($q) use ($kwList, $applyTermFilter) {
                foreach ($kwList as $kw) {
                    if (mb_strlen($kw) >= 2) {
                        $applyTermFilter($q, $kw);
                    }
                }
            });
        }
        // Case 3: Auto-suggest by extracting meaningful title keywords
        elseif (!empty($title)) {
            $cleanTitle = preg_replace('/[।!?:;,"\'\(\)\[\]\{\}]/u', ' ', $title);
            $words = array_values(array_filter(explode(' ', trim($cleanTitle))));
            $stopWords = [
                'এবং', 'ও', 'বা', 'কিন্তু', 'যদি', 'তবে', 'জন্য', 'নিয়ে', 'দিয়ে', 'থেকে', 'হতে', 'করে', 
                'হয়ে', 'হলো', 'হবে', 'করলো', 'গেছে', 'আছে', 'ছিল', 'বলেন', 'জানান', 'পর', 'এই', 'সেই', 
                'তার', 'তাদের', 'the', 'a', 'an', 'in', 'on', 'to', 'for', 'of', 'with', 'by', 'as', 'is'
            ];
            $significantWords = array_values(array_filter($words, function($w) use ($stopWords) {
                return mb_strlen($w) >= 2 && !in_array(mb_strtolower($w), $stopWords);
            }));

            if (!empty($significantWords)) {
                $query->where(function($q) use ($significantWords, $applyTermFilter) {
                    // Match 2-word phrase if available
                    if (count($significantWords) >= 2) {
                        $phrase = $significantWords[0] . ' ' . $significantWords[1];
                        $applyTermFilter($q, $phrase);
                    }
                    foreach (array_slice($significantWords, 0, 3) as $w) {
                        $applyTermFilter($q, $w);
                    }
                });
            } else {
                return response()->json([]);
            }
        } else {
            return response()->json([]);
        }

        $relatedNews = $query->orderBy('created_at', 'desc')->limit(8)->get();

        $formattedNews = $relatedNews->map(function ($news) use ($settings) {
            // Smart URL construction for WordPress / Laravel / Custom PHP / Node.js
            $url = null;
            if ($news->wp_post_id && optional($settings)->wp_url) {
                $url = rtrim($settings->wp_url, '/') . '/?p=' . $news->wp_post_id;
            } elseif (optional($settings)->post_to_laravel && optional($settings)->laravel_site_url) {
                $prefix = trim($settings->laravel_route_prefix ?? 'news', '/');
                $id = $news->wp_post_id ?? $news->id;
                $url = rtrim($settings->laravel_site_url, '/') . '/' . $prefix . '/' . $id;
            } elseif (!empty($news->live_url)) {
                $url = $news->live_url;
            } elseif (!empty($news->original_link)) {
                $url = $news->original_link;
            } else {
                $url = url('/news/' . $news->id);
            }

            return [
                'id'            => $news->id,
                'title'         => $news->ai_title ?: ($news->title ?: 'Untitled News'),
                'live_url'      => $url,
                'thumbnail_url' => $news->thumbnail_url ?: asset('images/placeholder.png'),
                'published_at'  => $news->published_at ? $news->published_at->format('d M, Y') : ($news->created_at ? $news->created_at->format('d M, Y') : null),
                'time_ago'      => $news->created_at ? $news->created_at->diffForHumans() : null,
            ];
        });

        return response()->json($formattedNews->values());
    }

    public function toggleQueue($id)
    {
        $adminUser = $this->getEffectiveAdminForAjax();
        $news = NewsItem::withoutGlobalScopes()->whereIn('user_id', [$adminUser->id, Auth::id()])->findOrFail($id);
        
        if ($news->status == 'published') return back()->with('error', 'ইতিমধ্যে পোস্ট করা হয়েছে!');
        
        $news->is_queued = !$news->is_queued;
        $news->save();
        return back()->with('success', $news->is_queued ? '📌 অটো-পোস্ট লিস্টে যুক্ত হয়েছে' : 'লিস্ট থেকে সরানো হয়েছে');
    }

    public function toggleAutomation(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasPermission('can_auto_post') && $user->role !== 'super_admin') {
            return back()->with('error', 'আপনার অটোমেশন ব্যবহার করার অনুমতি নেই।');
        }
        
        $request->validate(['interval' => 'nullable|integer|min:1|max:60']);
        
        $adminUser = $this->getEffectiveAdminForAjax();
        
        // 🔥 অ্যাডমিনের সেটিংসে পরিবর্তন সেভ হবে
        $settings = $adminUser->settings()->firstOrCreate(['user_id' => $adminUser->id]);
        $settings->is_auto_posting = !$settings->is_auto_posting;
        
        if ($request->filled('interval')) $settings->auto_post_interval = $request->interval;
        if ($settings->is_auto_posting) $settings->last_auto_post_at = now();
        
        $settings->save();
        return back()->with('success', "অটোমেশন সফলভাবে " . ($settings->is_auto_posting ? "চালু" : 'বন্ধ') . " করা হয়েছে।");
    }

    public function checkAutoPostStatus()
    {
        $adminUser = $this->getEffectiveAdminForAjax();
        $settings = $adminUser->settings; // 🔥 অ্যাডমিনের সেটিংস
        
        if (!$settings || !$settings->is_auto_posting) return response()->json(['status' => 'off']);
        
        $nextPost = (\Carbon\Carbon::parse($settings->last_auto_post_at ?? now()))->addMinutes($settings->auto_post_interval ?? 10);
        return response()->json(['status' => 'on', 'next_post_time' => $nextPost->format('Y-m-d H:i:s')]);
    }

    public function checkScrapeStatus()
    {
        $adminUser = $this->getEffectiveAdminForAjax();
        
        // 🔥 জবের আইডি যেহেতু অ্যাডমিনের দেওয়া, তাই ক্যাশেও অ্যাডমিনের আইডি চেক করবে
        $isScraping = Cache::has('scraping_user_' . $adminUser->id);
        
        if (!$isScraping && request()->query('force_wait') === 'true') {
            sleep(2); 
            $isScraping = Cache::has('scraping_user_' . $adminUser->id);
        }
        return response()->json(['scraping' => $isScraping]);
    }

    public function checkDraftUpdates(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) return response()->json([]);
        
        // শুধু স্ট্যাটাস আপডেট দেখাবে
        return response()->json(NewsItem::withoutGlobalScopes()->whereIn('id', $ids)->get(['id', 'status', 'error_message']));
    }

    public function handlePreviewFeedback(Request $request, $id) 
    {
        $adminUser = $this->getEffectiveAdminForAjax();
        $news = NewsItem::withoutGlobalScopes()->whereIn('user_id', [$adminUser->id, Auth::id()])->findOrFail($id);
        
        if ($request->input('status') == 'approved') {
            $news->status = 'draft'; 
            $news->error_message = '✅ Boss Approved this news.';
        } else {
            $news->status = 'failed';
            $news->error_message = '❌ Rejected: ' . $request->input('note');
        }
        $news->save();
        return back()->with('success', 'মতামত গ্রহণ করা হয়েছে।');
    }

    public function incrementDownloadCount(Request $request, $id)
    {
        $adminUser = $this->getEffectiveAdminForAjax();
        $news = NewsItem::withoutGlobalScopes()
            ->whereIn('user_id', [$adminUser->id, Auth::id()])
            ->find($id);

        if ($news) {
            $news->increment('card_download_count');
            return response()->json(['success' => true, 'count' => $news->card_download_count]);
        }

        return response()->json(['success' => false], 404);
    }

    /**
     * 🔍 Real-time Smart News Deduplication Check (Tenant-Scoped)
     */
    public function checkDuplicates(Request $request, \App\Services\NewsDeduplicationService $dedupService)
    {
        $title = $request->input('title', '');
        $excludeId = $request->input('exclude_id');

        if (empty(trim($title)) || mb_strlen(trim($title)) < 5) {
            return response()->json([
                'success'    => true,
                'duplicates' => []
            ]);
        }

        $user = Auth::user();
        $duplicates = $dedupService->findDuplicates($user, $title, $excludeId ? (int)$excludeId : null, 55.0);

        return response()->json([
            'success'    => true,
            'count'      => count($duplicates),
            'duplicates' => $duplicates
        ]);
    }

    /**
     * ✨ 1-Click 3-Option Viral Headline Generator Endpoint
     */
    public function generateHeadlines(Request $request, \App\Services\AIWriterService $aiWriter)
    {
        $title = $request->input('title', '');
        $content = $request->input('content', '');
        $newsId = $request->input('news_id');

        if ($newsId && (empty($title) || empty($content))) {
            $news = NewsItem::withoutGlobalScopes()->find($newsId);
            if ($news) {
                $title = $title ?: ($news->ai_title ?: $news->title);
                $content = $content ?: ($news->ai_content ?: $news->content);
            }
        }

        if (empty(trim($title))) {
            return response()->json(['success' => false, 'message' => 'শিরোনাম ছাড়া এআই আইডিয়া জেনারেট করা সম্ভব নয়!'], 422);
        }

        $adminUser = $this->getEffectiveAdminForAjax();
        $headlines = $aiWriter->generateViralHeadlines($title, $content, $adminUser->id);

        return response()->json([
            'success'   => true,
            'headlines' => $headlines
        ]);
    }

    /**
     * 🔍 1-Click SEO Focus Keyword & Meta Generator Endpoint
     */
    public function generateFocusKeywords(Request $request, \App\Services\AIWriterService $aiWriter)
    {
        $title = $request->input('title', '');
        $content = $request->input('content', '');
        $newsId = $request->input('news_id');

        if ($newsId && (empty($title) || empty($content))) {
            $news = NewsItem::withoutGlobalScopes()->find($newsId);
            if ($news) {
                $title = $title ?: ($news->ai_title ?: $news->title);
                $content = $content ?: ($news->ai_content ?: $news->content);
            }
        }

        if (empty(trim($title))) {
            return response()->json(['success' => false, 'message' => 'শিরোনাম ছাড়া ফোকাস কী-ওয়ার্ড জেনারেট করা সম্ভব নয়!'], 422);
        }

        $adminUser = $this->getEffectiveAdminForAjax();
        $targetLanguage = $request->input('target_language', 'bn');
        $seoData = $aiWriter->extractFocusKeywords($title, $content, $adminUser->id, $targetLanguage);

        return response()->json([
            'success' => true,
            'data'    => $seoData
        ]);
    }
}