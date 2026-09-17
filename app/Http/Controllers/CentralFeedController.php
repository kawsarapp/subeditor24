<?php

namespace App\Http\Controllers;

use App\Models\CentralNewsPool;
use App\Models\NewsItem;
use App\Models\User;
use App\Models\Website;
use App\Jobs\GenerateAIContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CentralFeedController extends Controller
{
    private function getEffectiveAdmin()
    {
        $user = Auth::user();
        return in_array($user->role, ['staff', 'reporter']) ? User::find($user->parent_id) : $user;
    }

    /**
     * Display the Central Live Feed pool (Supports both full page & AJAX dynamic load).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        $search = $request->input('search');
        $websiteId = $request->input('website_id');
        $hours = $request->input('hours');

        $query = CentralNewsPool::with(['website' => function ($q) {
            $q->withoutGlobalScopes();
        }]);

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($websiteId) {
            $query->where('website_id', $websiteId);
        }

        if ($hours) {
            $query->where('created_at', '>=', now()->subHours((int) $hours));
        }

        $newsItems = $query->latest('created_at')->paginate(24)->withQueryString();

        // If AJAX request (for instant live search / filter / pagination)
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('news.partials.central-feed-grid', compact('newsItems'))->render(),
                'pagination' => (string) $newsItems->links(),
                'total' => $newsItems->total(),
                'latest_id' => $newsItems->first()->id ?? 0,
            ]);
        }

        // Get all websites active in central pool for the filter dropdown
        $websites = Website::withoutGlobalScopes()
            ->where('is_central_active', true)
            ->orderBy('name')
            ->get();

        // Feed statistics
        $stats = [
            'total_today' => CentralNewsPool::where('created_at', '>=', now()->startOfDay())->count(),
            'total_pool' => CentralNewsPool::count(),
            'active_sources' => Website::withoutGlobalScopes()->where('is_central_active', true)->count(),
        ];

        return view('news.central-feed', compact('newsItems', 'websites', 'stats'));
    }

    /**
     * Instant check & fetch for new central pool items (Live AJAX Injection).
     */
    public function checkNewFeed(Request $request)
    {
        $lastId = (int) $request->input('last_id', 0);
        $fetchItems = $request->boolean('fetch_items', false);

        $query = CentralNewsPool::with(['website' => function ($q) {
            $q->withoutGlobalScopes();
        }])->where('id', '>', $lastId);

        $newCount = $query->count();

        if ($fetchItems && $newCount > 0) {
            $newItems = $query->latest('id')->limit(20)->get();
            $cardsHtml = '';
            foreach ($newItems as $item) {
                $cardsHtml .= view('news.partials.central-feed-card', compact('item'))->render();
            }

            return response()->json([
                'new_count' => $newCount,
                'html' => $cardsHtml,
                'latest_id' => $newItems->first()->id ?? $lastId,
            ]);
        }

        return response()->json(['new_count' => $newCount]);
    }

    /**
     * Copy a central pool item to user's private news items and perform chosen action (AJAX & Form supported).
     */
    public function importNews(Request $request, $id)
    {
        $poolItem = CentralNewsPool::findOrFail($id);
        $adminUser = $this->getEffectiveAdmin();
        $action = $request->input('action', 'draft'); // 'draft', 'ai', 'studio'

        // Check if user already imported this article
        $existing = NewsItem::withoutGlobalScopes()
            ->where('user_id', $adminUser->id)
            ->where(function ($q) use ($poolItem) {
                $q->where('original_link', $poolItem->original_link)
                  ->orWhere('title', $poolItem->title);
            })
            ->first();

        if ($existing) {
            $newsItem = $existing;
        } else {
            $newsItem = NewsItem::create([
                'user_id' => $adminUser->id,
                'staff_id' => Auth::id() !== $adminUser->id ? Auth::id() : null,
                'website_id' => $poolItem->website_id,
                'title' => $poolItem->title,
                'content' => $poolItem->content ?: $poolItem->title,
                'thumbnail_url' => $poolItem->thumbnail_url,
                'original_link' => $poolItem->original_link,
                'published_at' => $poolItem->published_at ?? now(),
                'status' => 'draft',
                'is_posted' => false,
                'is_rewritten' => false,
            ]);
        }

        if ($action === 'studio') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'action' => 'studio',
                    'studio_url' => route('news.studio', $newsItem->id),
                    'message' => 'স্টুডিও ওপেন হচ্ছে...',
                ]);
            }
            return redirect()->route('news.studio', $newsItem->id);
        }

        if ($action === 'ai') {
            // Trigger AI Rewrite if user has permissions & credits
            if ($adminUser->role !== 'super_admin') {
                if ($adminUser->credits <= 0) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'আপনার ক্রেডিট শেষ! কিন্তু নিউজটি ড্রাফটে সেভ হয়েছে।']);
                    }
                    return redirect()->route('news.index')->with('error', 'আপনার ক্রেডিট শেষ! কিন্তু নিউজটি আপনার ড্রাফটে যোগ হয়েছে।');
                }
                if (method_exists($adminUser, 'hasDailyLimitRemaining') && !$adminUser->hasDailyLimitRemaining()) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'আজকের ডেইলি লিমিট শেষ! কিন্তু নিউজটি ড্রাফটে সেভ হয়েছে।']);
                    }
                    return redirect()->route('news.index')->with('error', 'আজকের ডেইলি লিমিট শেষ! কিন্তু নিউজটি আপনার ড্রাফটে যোগ হয়েছে।');
                }
                
                try {
                    DB::transaction(function () use ($adminUser, $newsItem) {
                        $adminUser->decrement('credits', 1);
                        \App\Models\CreditHistory::create([
                            'user_id' => $adminUser->id,
                            'staff_id' => Auth::id() !== $adminUser->id ? Auth::id() : null,
                            'action_type' => 'ai_rewrite',
                            'description' => 'Central Feed AI: ' . \Illuminate\Support\Str::limit($newsItem->title, 40),
                            'credits_change' => -1,
                            'balance_after' => $adminUser->credits,
                        ]);
                    });
                } catch (\Exception $e) {
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json(['success' => false, 'message' => 'ক্রেডিট প্রসেসিং এরর।']);
                    }
                    return redirect()->route('news.index')->with('error', 'ক্রেডিট প্রসেসিং এরর।');
                }
            }

            $newsItem->update([
                'status' => 'processing',
                'error_message' => null,
                'ai_title' => 'Writing...',
                'ai_content' => null,
            ]);

            GenerateAIContent::dispatch($newsItem->id, Auth::id());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'action' => 'ai',
                    'news_id' => $newsItem->id,
                    'message' => '⚡ AI রিরাইট শুরু হয়েছে! আপনার ফিডে যোগ হয়েছে।',
                ]);
            }

            return redirect()->route('news.index')->with('success', '⚡ সেন্ট্রাল ফিড থেকে নিউজটি আপনার একাউন্টে নিয়ে AI রিরাইট শুরু হয়েছে!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'action' => 'draft',
                'news_id' => $newsItem->id,
                'message' => '✅ সফলভাবে আপনার প্রাইভেট ড্রাফটে যোগ হয়েছে!',
            ]);
        }

        return redirect()->route('news.index')->with('success', '✅ সেন্ট্রাল ফিড থেকে নিউজটি আপনার কালেকশনে যুক্ত হয়েছে!');
    }

    /**
     * Bulk import multiple central news items into user's private drafts.
     */
    public function bulkImport(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json(['success' => false, 'message' => 'কোনো নিউজ সিলেক্ট করা হয়নি।']);
        }

        $adminUser = $this->getEffectiveAdmin();
        $staffId = Auth::id() !== $adminUser->id ? Auth::id() : null;

        $poolItems = CentralNewsPool::whereIn('id', $ids)->get();
        $importedCount = 0;

        foreach ($poolItems as $item) {
            $exists = NewsItem::withoutGlobalScopes()
                ->where('user_id', $adminUser->id)
                ->where(function ($q) use ($item) {
                    $q->where('original_link', $item->original_link)
                      ->orWhere('title', $item->title);
                })
                ->exists();

            if (!$exists) {
                NewsItem::create([
                    'user_id' => $adminUser->id,
                    'staff_id' => $staffId,
                    'website_id' => $item->website_id,
                    'title' => $item->title,
                    'content' => $item->content ?: $item->title,
                    'thumbnail_url' => $item->thumbnail_url,
                    'original_link' => $item->original_link,
                    'published_at' => $item->published_at ?? now(),
                    'status' => 'draft',
                    'is_posted' => false,
                    'is_rewritten' => false,
                ]);
                $importedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "সফলভাবে {$importedCount}টি নিউজ আপনার প্রাইভেট ফিডে ইম্পোর্ট করা হয়েছে!",
        ]);
    }
}
