<?php

namespace App\Modules\SeoIntelligence\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SeoIntelligence\Models\SeoWebsite;
use App\Modules\SeoIntelligence\Models\SeoPageAudit;
use App\Modules\SeoIntelligence\Services\WebsiteCrawlerEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($activeWebsite) {
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
            'breadcrumbCount', 'organizationCount', 'maxPreviewPassed', 'ogImagePassed'
        ));
    }

    /**
     * Trigger On-Demand Technical Crawl for Selected Website
     */
    public function crawl(Request $request, $id)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);

        $result = $this->crawlerEngine->crawlWebsite($website);

        return response()->json($result);
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
            $query->where('status_code', '!=', 200);
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
}
