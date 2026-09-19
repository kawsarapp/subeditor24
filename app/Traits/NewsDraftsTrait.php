<?php

namespace App\Traits;

use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait NewsDraftsTrait
{
    // 🔥 হেল্পার ফাংশন: স্টাফ বা রিপোর্টার হলে তার অ্যাডমিনকে বের করবে
    private function getEffectiveAdmin() {
        $user = Auth::user();
        return in_array($user->role, ['staff', 'reporter']) ? User::find($user->parent_id) : $user;
    }

    public function drafts()
    {
        $user = Auth::user();
        $adminUser = $this->getEffectiveAdmin();
        $settings = $adminUser->settings;

        $drafts = NewsItem::with(['website' => function ($q) {
                $q->withoutGlobalScopes();
            }])
            ->whereIn('user_id', [$user->id, $adminUser->id]) // স্টাফ এবং অ্যাডমিন উভয়ের নিউজ দেখাবে
            ->where(function($q) {
                $q->where('is_rewritten', 1) 
                  ->orWhere(function($subQ) {
                      $subQ->whereNull('website_id')->whereNull('reporter_id'); 
                  })
                  ->orWhereIn('status', ['processing', 'publishing', 'failed']);
            })
            ->where('status', '!=', 'published') 
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        // 🔍 Smart News Deduplication Annotation (Tenant-Scoped)
        app(\App\Services\NewsDeduplicationService::class)->annotateCollection($drafts);

        return view('news.drafts', compact('drafts', 'settings'));
    }

    public function published()
    {
        $user = Auth::user();
        $adminUser = $this->getEffectiveAdmin();
        $settings = $adminUser->settings;

        $published = NewsItem::with(['website' => function ($q) {
            $q->withoutGlobalScopes();
        }])
        ->whereIn('user_id', [$user->id, $adminUser->id])
        ->where('status', 'published')
        ->orderBy('updated_at', 'desc')
        ->paginate(20);

        return view('news.published', compact('published', 'settings'));
    }

    public function updateDraft(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'image_url' => 'nullable|url',
            'hashtags' => 'nullable|string'
        ]);

        $user = Auth::user();
        $adminUser = $this->getEffectiveAdmin();
        
        $news = NewsItem::whereIn('user_id', [$user->id, $adminUser->id])->findOrFail($id);
        
        if ($request->hasFile('image_file')) {
            try {
                $news->thumbnail_url = app(\App\Services\ImageOptimizerService::class)->optimizeAndStore($request->file('image_file'));
            } catch (\Exception $e) {
                Log::error("Image Upload Failed: " . $e->getMessage());
            }
        } 
        elseif ($request->filled('image_url')) {
            $news->thumbnail_url = $request->image_url;
        }

        $news->title = $request->title;
        $news->ai_title = $request->title; 
        $news->content = $request->content;
        $news->ai_content = $request->content;
        $news->hashtags = $request->hashtags ?? $request->focus_keyword;
        if ($request->filled('meta_description')) {
            $news->short_summary = $request->meta_description;
        }
        if ($request->filled('focus_keyword')) {
            $news->tags = $request->focus_keyword;
        }
        $news->is_rewritten = 1;
        $news->status = 'draft';
        $news->updated_at = now();
        
        $news->save();

        return response()->json(['success' => true, 'message' => 'ড্রাফট এবং ইমেজ সফলভাবে সেভ হয়েছে।']);
    }
    
    public function getDraftContent($id)
    {
        $news = NewsItem::withoutGlobalScopes()->with(['lockedBy', 'website' => function ($q) { $q->withoutGlobalScopes(); }])->findOrFail($id);
        $user = Auth::user();
        $adminUser = $this->getEffectiveAdmin();

        if ($news->locked_by_user_id && $news->locked_by_user_id !== $user->id) {
            return response()->json([
                'success' => false, 
                'message' => '⚠️ এটি বর্তমানে ' . ($news->lockedBy->name ?? 'অন্য একজন') . ' এডিট করছেন।'
            ]);
        }

        $news->update(['locked_by_user_id' => $user->id, 'locked_at' => now()]);

        $title = !empty($news->ai_title) ? $news->ai_title : $news->title;
        $content = !empty($news->ai_content) ? $news->ai_content : $news->content;

        $extraImages = [];
        if (!empty($news->tags)) {
            $decodedTags = json_decode($news->tags, true);
            if (is_array($decodedTags)) $extraImages = $decodedTags;
        }

        // 🔍 Deduplication Check for this specific news item
        $duplicates = app(\App\Services\NewsDeduplicationService::class)->findDuplicates($user, $news->title, $news->id, 55.0);

        $rawText = strip_tags($content);
        $rawText = preg_replace('/\s+/', ' ', $rawText);
        $fallbackMeta = mb_substr(trim($rawText), 0, 150);
        $metaDescription = !empty($news->short_summary) ? $news->short_summary : $fallbackMeta;

        $focusKeywords = $news->tags ?: $news->hashtags;
        if (empty($focusKeywords) && !empty($title)) {
            $cleanTitle = preg_replace('/[।!?:;,"\'\(\)\[\]\{\}]/u', ' ', $title);
            $words = array_values(array_filter(explode(' ', trim($cleanTitle))));
            $stopWords = [
                'এবং', 'ও', 'বা', 'কিন্তু', 'যদি', 'তবে', 'জন্য', 'নিয়ে', 'দিয়ে', 'থেকে', 'হতে', 'করে', 
                'হয়ে', 'হলো', 'হবে', 'করলো', 'গেছে', 'আছে', 'ছিল', 'বলেন', 'জানান', 'পর', 'এই', 'সেই', 
                'তার', 'তাদের', 'the', 'a', 'an', 'in', 'on', 'to', 'for', 'of', 'with', 'by', 'as', 'is', 'are'
            ];
            $filtered = [];
            foreach ($words as $w) {
                $wClean = trim($w);
                $wLower = mb_strtolower($wClean);
                if (mb_strlen($wLower) > 1 && !in_array($wLower, $stopWords)) {
                    $filtered[] = $wClean;
                }
            }
            if (count($filtered) >= 2) {
                $focusKeywords = $filtered[0] . ' ' . $filtered[1] . ', ' . $filtered[0] . ', ' . $filtered[1];
            } elseif (count($filtered) === 1) {
                $focusKeywords = $filtered[0];
            } else {
                $focusKeywords = mb_substr($title, 0, 30);
            }
        }

        return response()->json([
            'success'          => true,
            'title'            => $title,
            'content'          => $content,
            'original_title'   => $news->title,
            'original_content' => $news->content,
            'source_name'      => $news->website->name ?? 'Custom / Reporter',
            'hashtags'         => $news->hashtags,
            'focus_keyword'    => $focusKeywords,
            'meta_description' => $metaDescription,
            'short_summary'    => $metaDescription,
            'image_url'        => $news->thumbnail_url,
            'extra_images'     => $extraImages,
            'location'         => $news->location,
            'original_link'    => $news->original_link,
            // 🔥 ফিক্স: স্টাফ এখন তার অ্যাডমিনের ক্যাটাগরি দেখতে পাবে
            'categories'       => $adminUser->settings->category_mapping ?? [],
            'plagiarism_score' => $news->plagiarism_score,
            'fact_check_status'=> $news->fact_check_status,
            'fact_check_report'=> $news->fact_check_report,
            'duplicates'       => $duplicates
        ]);
    }

    public function analyzePlagiarism($id, Request $request, \App\Services\AIWriterService $aiWriter, \App\Services\FactCheckService $factChecker)
    {
        $request->validate([
            'content' => 'required|string',
            'title'   => 'nullable|string',
        ]);

        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_fact_check')) {
            return response()->json([
                'success' => false,
                'message' => 'দুঃখিত, আপনার কাছে তথ্য যাচাই ও প্লাজিয়ারিজম চেক করার পারমিশন নেই।'
            ], 403);
        }
        $adminUser = $this->getEffectiveAdmin();

        $news = NewsItem::withoutGlobalScopes()
            ->whereIn('user_id', array_unique([$user->id, $adminUser->id]))
            ->findOrFail($id);

        try {
            $title = $request->input('title') ?: ($news->ai_title ?: $news->title);
            $editorContent = $request->input('content');
            $originalContent = $news->content ?: '';

            // Calculate textual similarity percentage with original content
            $safeOriginal = mb_substr(strip_tags($originalContent), 0, 4000, 'UTF-8');
            $safeRewritten = mb_substr(strip_tags($editorContent), 0, 4000, 'UTF-8');
            similar_text($safeOriginal, $safeRewritten, $simPercent);
            $plagiarismScore = min(100, max(0, round($simPercent)));
            $uniquenessScore = max(0, 100 - $plagiarismScore);

            // Run Enterprise Zero-Hallucination Fact Check
            $verification = $factChecker->verifyNewsArticle($title, $editorContent, $adminUser->id, $originalContent);

            $factStatus = $verification['overall_verdict'] ?? 'verified';
            $factReport = $verification['summary_report'] ?? 'তথ্য যাচাই সম্পন্ন হয়েছে।';

            $news->update([
                'plagiarism_score' => $plagiarismScore,
                'fact_check_status' => $factStatus,
                'fact_check_report' => $factReport,
            ]);

            return response()->json([
                'success'             => true,
                'plagiarism_score'    => $plagiarismScore,
                'uniqueness_score'    => $uniquenessScore,
                'credibility_score'   => $verification['credibility_score'] ?? 85,
                'overall_verdict'     => $factStatus,
                'verdict_title'       => $verification['verdict_title'] ?? 'তথ্য যাচাই সম্পন্ন',
                'fact_check_status'   => $factStatus,
                'fact_check_report'   => $factReport,
                'summary_report'      => $factReport,
                'claims'              => $verification['claims'] ?? [],
                'official_factchecks' => $verification['official_factchecks'] ?? [],
                'pool_matches'        => $verification['pool_matches'] ?? [],
                'red_flags'           => $verification['red_flags'] ?? [],
                'has_official_debunk' => $verification['has_official_debunk'] ?? false,
            ]);
        } catch (\Exception $e) {
            Log::error("Fact check request failed: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'ফ্যাক্ট-চেক সম্পন্ন করা সম্ভব হয়নি: ' . $e->getMessage()
            ], 500);
        }
    }
}