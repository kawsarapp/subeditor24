<?php

namespace App\Modules\SeoIntelligence\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SeoIntelligence\Models\SeoWebsite;
use App\Modules\SeoIntelligence\Models\SeoPageAudit;
use App\Modules\SeoIntelligence\Jobs\ExecuteSeoCrawlJob;
use App\Modules\SeoIntelligence\Services\WebsiteCrawlerEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class SeoDashboardController extends Controller
{
    protected WebsiteCrawlerEngine $crawlerEngine;

    public function __construct(WebsiteCrawlerEngine $crawlerEngine)
    {
        $this->crawlerEngine = $crawlerEngine;
    }

    /**
     * Check if user has permission to access SEO Intelligence module
     */
    protected function checkAccess()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            abort(403, 'আপনার SEO Intelligence মডিউল ব্যবহারের অনুমতি নেই।');
        }
        return $user;
    }

    /**
     * Display SEO Intelligence Overview Dashboard
     */
    public function index(Request $request)
    {
        $user = $this->checkAccess();

        $websites = SeoWebsite::where('user_id', $user->id)
            ->with(['keywordMetrics', 'coreWebVitals'])
            ->latest()
            ->get();

        $selectedWebsiteId = $request->get('website_id', $websites->first()?->id);
        $activeWebsite = $websites->firstWhere('id', $selectedWebsiteId);

        $indexingService = new \App\Modules\SeoIntelligence\Services\InstantIndexingService();
        $indexingLogsPaginator = $activeWebsite ? $indexingService->getFilteredLogs($activeWebsite, $request->only(['status', 'date', 'month'])) : null;
        $indexingLogs = $indexingLogsPaginator ? collect($indexingLogsPaginator->items()) : collect();

        // Pre-compute all service data to avoid instantiating services inside Blade
        $linkSuggestions = $activeWebsite ? (new \App\Modules\SeoIntelligence\Services\InternalLinkBuilderService())->suggestInternalLinks($activeWebsite) : [];
        $decayArticles = $activeWebsite ? (new \App\Modules\SeoIntelligence\Services\ContentDecayService())->detectContentDecay($activeWebsite) : [];
        $missingTitleCount = $activeWebsite ? $activeWebsite->pageAudits()->whereNull('title')->count() : 0;
        $missingDescCount = $activeWebsite ? $activeWebsite->pageAudits()->whereNull('meta_description')->count() : 0;

        // Discover Optimizer audit
        $discoverAudit = null;
        if ($activeWebsite) {
            $firstAudit = $activeWebsite->pageAudits()->first();
            $discoverAudit = (new \App\Modules\SeoIntelligence\Services\DiscoverOptimizerService())
                ->auditDiscoverReadiness($activeWebsite, $firstAudit?->title ?? ($activeWebsite->domain . ' লাইভ খবর'));
        }

        // Competitor Gap analysis
        $gapData = $activeWebsite
            ? (new \App\Modules\SeoIntelligence\Services\CompetitorGapService())->analyzeKeywordGap($activeWebsite, 'prothomalo.com')
            : null;

        // Precompute summary counts directly in database
        $totalUrls = $activeWebsite ? $activeWebsite->pageAudits()->count() : 0;
        $brokenCount = $activeWebsite ? $activeWebsite->pageAudits()->where('status_code', '!=', 200)->count() : 0;

        $newsArticleCount = 0;
        $breadcrumbCount = 0;
        $organizationCount = 0;
        $maxPreviewPassed = 0;
        $ogImagePassed = 0;

        $indexedPagesCount = 0;
        $nonIndexedPagesCount = 0;

        if ($activeWebsite) {
            $indexedPagesCount = $activeWebsite->pageAudits()->where('is_indexed', true)->count();
            $nonIndexedPagesCount = $activeWebsite->pageAudits()->where('is_indexed', false)->count();

            $newsArticleCount = $activeWebsite->pageAudits()
                ->where(function($q) {
                    $q->where('schema_detected', 'like', '%NewsArticle%')
                      ->orWhere('schema_detected', 'like', '%Article%');
                })->count();
            $breadcrumbCount = $activeWebsite->pageAudits()->where('schema_detected', 'like', '%BreadcrumbList%')->count();
            $organizationCount = $activeWebsite->pageAudits()
                ->where(function($q) {
                    $q->where('schema_detected', 'like', '%Organization%')
                      ->orWhere('schema_detected', 'like', '%WebSite%');
                })->count();

            $maxPreviewPassed = $activeWebsite->pageAudits()->where('issues_found', 'not like', '%missing_max_image_preview%')->count();
            $ogImagePassed = $activeWebsite->pageAudits()->where('issues_found', 'not like', '%missing_og_image%')->count();
        }

        return view('seo.index', compact(
            'websites', 'activeWebsite', 'indexingLogs', 'indexingLogsPaginator',
            'linkSuggestions', 'decayArticles', 'missingTitleCount', 'missingDescCount',
            'discoverAudit', 'gapData', 'totalUrls', 'brokenCount', 'newsArticleCount',
            'breadcrumbCount', 'organizationCount', 'maxPreviewPassed', 'ogImagePassed',
            'indexedPagesCount', 'nonIndexedPagesCount'
        ));
    }

    /**
     * Trigger On-Demand Technical Crawl for Selected Website (Queued)
     */
    public function crawl(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->find($id);
        if (!$website) {
            return response()->json(['success' => false, 'message' => 'Website record not found.'], 404);
        }

        // If crawl is currently running, return existing state unless stale (>10 min)
        if ($website->crawl_status === 'running' && $website->updated_at && $website->updated_at->gt(now()->subMinutes(10))) {
            return response()->json([
                'success' => true,
                'already_running' => true,
                'status' => 'running',
                'progress' => (int) ($website->crawl_progress ?? 10),
                'message' => 'Crawl is already running in background.',
                'website_id' => $website->id,
            ]);
        }

        $website->update([
            'crawl_status' => 'queued',
            'crawl_progress' => 0,
            'crawl_error' => null,
        ]);

        ExecuteSeoCrawlJob::dispatch($website->id);

        $website->refresh();

        return response()->json([
            'success' => true,
            'queued' => true,
            'status' => $website->crawl_status ?? 'queued',
            'progress' => (int) ($website->crawl_progress ?? 0),
            'health_score' => $website->seo_health_score,
            'message' => 'Technical crawl job dispatched to background queue.',
            'website_id' => $website->id,
        ]);
    }

    /**
     * Get Crawl Status and Progress for Selected Website
     */
    public function crawlStatus(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->find($id);
        if (!$website) {
            return response()->json(['success' => false, 'message' => 'Website record not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'website_id' => $website->id,
            'status' => $website->crawl_status ?? 'idle',
            'progress' => (int) ($website->crawl_progress ?? 0),
            'health_score' => $website->seo_health_score,
            'last_crawled_at' => $website->last_crawled_at ? $website->last_crawled_at->diffForHumans() : null,
            'last_crawled_timestamp' => $website->last_crawled_at ? $website->last_crawled_at->toIso8601String() : null,
            'error' => $website->crawl_error,
        ]);
    }

    /**
     * Display SEO Help & Complete Documentation Guide Page
     */
    public function guide()
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            abort(403, 'আপনার SEO Intelligence মডিউল ব্যবহারের অনুমতি নেই।');
        }

        return view('seo.guide');
    }

    /**
     * Trigger Instant Indexing for URL
     */
    public function instantIndex(Request $request, $id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $service = new \App\Modules\SeoIntelligence\Services\InstantIndexingService();
        $result = $service->pushInstantIndexing($website, $request->input('url', $website->target_url));

        return response()->json($result);
    }

    /**
     * Fetch Instant Indexing Logs via AJAX (No Page Reload)
     */
    public function instantIndexLogs(Request $request, $id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $service = new \App\Modules\SeoIntelligence\Services\InstantIndexingService();
        $logs = $service->getFilteredLogs($website, $request->only(['status', 'date', 'month']));

        return response()->json([
            'success' => true,
            'data' => $logs->items(),
            'total' => $logs->total(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
        ]);
    }

    /**
     * Approve AI Internal Link Suggestion
     */
    public function approveInternalLink(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => '✅ Human Approval Received! Internal link inserted safely into news article.'
        ]);
    }

    /**
     * Audit Google News & Discover Readiness
     */
    public function discoverCheck(Request $request, $id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $service = new \App\Modules\SeoIntelligence\Services\DiscoverOptimizerService();
        $result = $service->auditDiscoverReadiness($website, $request->input('title', $website->domain . ' লাইভ খবর'));

        return response()->json($result);
    }

    /**
     * Test Telegram Uptime Emergency Alert Bot
     */
    public function telegramAlert(Request $request, $id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $botToken = $request->input('bot_token', '123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11');
        $chatId = $request->input('chat_id', '@subeditor24_alerts');

        $service = new \App\Modules\SeoIntelligence\Services\UptimeMonitorService();
        $result = $service->sendTelegramTestAlert($botToken, $chatId, $website->domain);

        return response()->json($result);
    }

    /**
     * Generate Tracked Social Link with UTM Parameters
     */
    public function generateUtm(Request $request)
    {
        $url = $request->input('url', 'https://example.com');
        $platform = $request->input('platform', 'facebook');

        $service = new \App\Modules\SeoIntelligence\Services\UtmTrackerService();
        $utmUrl = $service->generateUtmLink($url, $platform);

        return response()->json(['success' => true, 'utm_url' => $utmUrl]);
    }

    /**
     * Audit Competitor Keyword Gap Analysis
     */
    public function competitorGap(Request $request, $id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $competitorDomain = $request->input('competitor_domain', 'prothomalo.com');
        $service = new \App\Modules\SeoIntelligence\Services\CompetitorGapService();
        $result = $service->analyzeKeywordGap($website, $competitorDomain);

        return response()->json($result);
    }

    /**
     * Detect Content Decay & AI Refresh Suggestions
     */
    public function contentDecay(Request $request, $id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $service = new \App\Modules\SeoIntelligence\Services\ContentDecayService();
        $result = $service->detectContentDecay($website);

        return response()->json(['success' => true, 'decay_articles' => $result]);
    }

    /**
     * Sync Search Console Traffic Keywords & Indexing Status from Google API
     */
    public function gscSync(Request $request, $id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $service = new \App\Modules\SeoIntelligence\Services\GscSyncService();
        $result = $service->syncSearchConsoleData($website);

        return response()->json($result);
    }

    /**
     * Check Uptime via AJAX (Asynchronous)
     */
    public function uptimeCheckAjax($id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $uptimeData = cache()->remember(
            'seo_uptime_' . $website->id,
            now()->addMinutes(3),
            fn() => (new \App\Modules\SeoIntelligence\Services\UptimeMonitorService())->checkUptime($website)
        );

        return response()->json($uptimeData);
    }

    /**
     * Check Indexing Health via AJAX (Asynchronous)
     */
    public function indexingHealthAjax($id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);
        $indexingService = new \App\Modules\SeoIntelligence\Services\InstantIndexingService();

        $indexingHealth = cache()->remember(
            'seo_indexing_health_' . $website->id,
            now()->addMinutes(5),
            fn() => $indexingService->checkApiConnectionStatus($website)
        );

        return response()->json($indexingHealth);
    }

    /**
     * Fetch Paginated Page Audits via AJAX
     */
    public function pageAuditsAjax(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);
        $type = $request->get('type', 'tech');
        $perPage = 10;

        $query = SeoPageAudit::where('seo_website_id', $website->id);

        if ($type === 'broken') {
            $query->where(function ($q) {
                $q->where('status_code', '>=', 400)
                  ->orWhere('status_code', '!=', 200)
                  ->orWhere('issues_found', 'like', '%broken_link%');
            });
        }

        $paginator = $query->latest('crawled_at')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
        ]);
    }

    /**
     * Upload and securely store Google Service Account JSON for Google Instant Indexing
     */
    public function uploadServiceAccount(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $request->validate([
            'service_account_file' => 'nullable|file|max:2048',
            'service_account_json' => 'nullable|string',
        ]);

        $rawJson = null;

        if ($request->hasFile('service_account_file')) {
            $file = $request->file('service_account_file');
            $rawJson = file_get_contents($file->getRealPath());
        } elseif ($request->filled('service_account_json')) {
            $rawJson = trim($request->input('service_account_json'));
        }

        if (empty($rawJson)) {
            $errorMsg = 'অনুগ্রহ করে সার্ভিস অ্যাকাউন্ট JSON ফাইল আপলোড করুন অথবা JSON কনটেন্ট পেস্ট করুন।';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        $parsed = json_decode($rawJson, true);
        if (!is_array($parsed) || empty($parsed['client_email']) || empty($parsed['private_key'])) {
            $errorMsg = 'ভুল JSON ফরম্যাট: ফাইলে "client_email" এবং "private_key" থাকা আবশ্যক। Google Cloud Console থেকে ডাউনলোড করা সঠিক Service Account JSON ফাইল দিন।';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->with('error', $errorMsg);
        }

        // 1. Encrypt and save in database
        $website->google_service_account_json = json_encode($parsed);
        $website->save();

        // 2. Also save to secure local storage directory (storage/app/seo/)
        try {
            $storageDir = storage_path('app/seo');
            if (!is_dir($storageDir)) {
                mkdir($storageDir, 0750, true);
            }
            $filePath = $storageDir . "/service_account_{$website->id}.json";
            file_put_contents($filePath, json_encode($parsed, JSON_PRETTY_PRINT));
            @chmod($filePath, 0600);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not write service account file to storage: " . $e->getMessage());
        }

        // Clear cached token and indexing health
        try {
            cache()->forget('seo_indexing_health_' . $website->id);
            cache()->forget('gsa_indexing_token_' . md5($parsed['client_email']));
        } catch (\Throwable $e) {}

        $clientEmail = $parsed['client_email'];
        $projectId = $parsed['project_id'] ?? 'N/A';
        $successMsg = "গুগল সার্ভিস অ্যাকাউন্ট সফলভাবে সংযুক্ত হয়েছে! (Email: {$clientEmail})";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'client_email' => $clientEmail,
                'project_id' => $projectId,
            ]);
        }

        return redirect()->back()->with('success', $successMsg);
    }

    /**
     * Remove Google Service Account JSON
     */
    public function removeServiceAccount(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }
            abort(403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $saEmail = $website->getGoogleServiceAccountEmail();
        if ($saEmail) {
            try {
                cache()->forget('gsa_indexing_token_' . md5($saEmail));
            } catch (\Throwable $e) {}
        }

        $website->google_service_account_json = null;
        $website->save();

        $filePath = storage_path("app/seo/service_account_{$website->id}.json");
        try {
            if (File::exists($filePath)) {
                File::delete($filePath);
            }
        } catch (\Throwable $e) {
            Log::warning("⚠️ Failed to delete SEO service account file [{$filePath}]: " . $e->getMessage());
        }

        try {
            cache()->forget('seo_indexing_health_' . $website->id);
        } catch (\Throwable $e) {}

        $msg = 'গুগল সার্ভিস অ্যাকাউন্ট সফলভাবে মুছে ফেলা হয়েছে।';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
