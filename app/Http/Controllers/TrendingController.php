<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsItem;
use App\Services\AIWriterService;
use App\Services\ViralPredictionEngine;
use App\Services\ExternalLiveTrendsService;
use Illuminate\Support\Facades\Auth;

class TrendingController extends Controller
{
    protected $aiWriter;
    protected $viralEngine;
    protected $externalLiveService;

    public function __construct(
        AIWriterService $aiWriter,
        ViralPredictionEngine $viralEngine,
        ExternalLiveTrendsService $externalLiveService
    ) {
        $this->aiWriter = $aiWriter;
        $this->viralEngine = $viralEngine;
        $this->externalLiveService = $externalLiveService;
    }

    /**
     * Display AI Viral Predictor & Trending Dashboard
     */
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'super_admin' && !Auth::user()->hasPermission('can_viral_predictor')) {
            abort(403, 'আপনার AI Viral Predictor ব্যবহারের অনুমতি নেই। সুপার অ্যাডমিনের সাথে যোগাযোগ করুন।');
        }

        $timeframe = $request->get('timeframe', '6');

        if ($timeframe === 'external') {
            // Fetch live real-time internet trends from external feeds & Google News BD
            $trendingList = collect($this->externalLiveService->fetchLiveExternalTrends());
            return view('trending.index', compact('trendingList', 'timeframe'));
        }
        
        $query = NewsItem::with('website');

        if (in_array($timeframe, ['1', '3', '6', '12', '24'])) {
            $hours = (int) $timeframe;
            $cutoff = now()->subHours($hours);

            $query->where(function ($q) use ($cutoff) {
                $q->where('created_at', '>=', $cutoff)
                  ->orWhere('published_at', '>=', $cutoff);
            });
        } elseif ($timeframe === 'all') {
            // No time filter - show all news
        } else {
            $timeframe = '6';
            $cutoff = now()->subHours(6);
            $query->where(function ($q) use ($cutoff) {
                $q->where('created_at', '>=', $cutoff)
                  ->orWhere('published_at', '>=', $cutoff);
            });
        }

        $newsItems = $query->latest()->take(50)->get();

        // Compute Multi-Portal Event Clustering & Social Buzz Gauges via ViralPredictionEngine
        $trendingList = $this->viralEngine->calculateViralPredictions($newsItems);

        return view('trending.index', compact('trendingList', 'timeframe'));
    }

    /**
     * Generate 3-Hour AI Viral Script & Social Package
     */
    public function generateScript(Request $request)
    {
        $request->validate([
            'news_id' => 'nullable',
            'title'   => 'nullable|string',
            'content' => 'nullable|string'
        ]);

        $userId = Auth::id();
        $title = $request->title;
        $content = $request->content;

        if ($request->news_id && strpos($request->news_id, 'ext_') === false) {
            $newsItem = NewsItem::find($request->news_id);
            if ($newsItem) {
                $title = $newsItem->title;
                $content = $newsItem->content ?? $newsItem->title;
            }
        }

        if (empty($title)) {
            return response()->json([
                'success' => false,
                'message' => 'Valid news title is required for AI script generation.'
            ], 422);
        }

        try {
            $result = $this->aiWriter->generateViralPredictionScript(
                $title,
                $content ?? $title,
                $userId
            );

            return response()->json([
                'success' => true,
                'original_title' => $title,
                'viral_score' => $result['viral_score'] ?? 85,
                'viral_angle' => $result['viral_angle'] ?? 'আগামী ৩ ঘণ্টায় সোশ্যাল মিডিয়ায় আলোচনা তৈরি করার উপযোগী নিউজ।',
                'photocard_punchline' => $result['photocard_punchline'] ?? ("🔥 " . mb_substr($title, 0, 70)),
                'catchy_headlines' => $result['catchy_headlines'] ?? [$title],
                'reels_script' => $result['reels_script'] ?? '',
                'facebook_caption' => $result['facebook_caption'] ?? ''
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'AI Script generation failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
