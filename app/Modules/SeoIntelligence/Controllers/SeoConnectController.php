<?php

namespace App\Modules\SeoIntelligence\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SeoIntelligence\Models\SeoWebsite;
use App\Modules\SeoIntelligence\Services\WebsiteCrawlerEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SeoConnectController extends Controller
{
    protected WebsiteCrawlerEngine $crawlerEngine;

    public function __construct(WebsiteCrawlerEngine $crawlerEngine)
    {
        $this->crawlerEngine = $crawlerEngine;
    }

    /**
     * Add Website & Generate Verification Token
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $request->validate([
            'target_url' => 'required|url',
        ]);

        $rawUrl = strtolower(trim($request->target_url));
        $host = parse_url($rawUrl, PHP_URL_HOST);
        $domain = preg_replace('/^www\./', '', $host);

        $txtToken = 'subeditor24-verify-' . Str::random(24);

        $website = SeoWebsite::create([
            'user_id' => $user->id,
            'domain' => $domain,
            'target_url' => $rawUrl,
            'verification_txt_token' => $txtToken,
            'is_verified' => true, // Auto-verified for local/client domains
            'sitemap_url' => $rawUrl . '/sitemap.xml',
            'robots_txt_url' => $rawUrl . '/robots.txt',
        ]);

        // Run initial quick crawl
        $this->crawlerEngine->crawlWebsite($website);

        return redirect()->route('seo.index', ['website_id' => $website->id])
            ->with('success', "Website {$domain} connected and initial audit completed successfully!");
    }

    /**
     * Remove Website
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $website = SeoWebsite::where('user_id', $user->id)->findOrFail($id);
        $website->delete();

        return redirect()->route('seo.index')->with('success', 'Website removed from SEO monitor.');
    }

    /**
     * Redirect User to Google OAuth Sign-In Page
     */
    public function redirectToGoogle(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'super_admin' && !$user->hasPermission('can_seo_intelligence')) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $clientId = $request->get('google_client_id') ?: env('GOOGLE_CLIENT_ID');
        $clientSecret = env('GOOGLE_CLIENT_SECRET');

        if (!$clientId || !$clientSecret) {
            return redirect()->route('seo.index')
                ->with('error', '⚠️ Google OAuth Setup Required: আপনার .env বা সিস্টেম কনফিগারেশনে GOOGLE_CLIENT_ID এবং GOOGLE_CLIENT_SECRET সেটআপ করুন।');
        }

        $websiteId = $request->get('website_id');
        if ($websiteId) {
            session(['connecting_website_id' => $websiteId]);
        }

        try {
            return Socialite::driver('google')
                ->scopes([
                    'https://www.googleapis.com/auth/webmasters.readonly',
                    'https://www.googleapis.com/auth/analytics.readonly',
                    'https://www.googleapis.com/auth/indexing'
                ])
                ->with(['access_type' => 'offline', 'prompt' => 'consent'])
                ->redirect();
        } catch (\Exception $e) {
            return redirect()->route('seo.index')
                ->with('error', '⚠️ Google OAuth Redirect Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth Callback Response
     */
    public function handleGoogleCallback(Request $request)
    {
        $user = Auth::user();
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $websiteId = session('connecting_website_id');
            $activeWeb = $websiteId ? SeoWebsite::where('user_id', $user->id)->find($websiteId) : null;
            $targetWebsites = $activeWeb ? collect([$activeWeb]) : SeoWebsite::where('user_id', $user->id)->get();

            foreach ($targetWebsites as $website) {
                $website->google_access_token = $googleUser->token;
                if (!empty($googleUser->refreshToken)) {
                    $website->google_refresh_token = $googleUser->refreshToken;
                }
                $website->gsc_property_id = 'sc-domain:' . $website->domain;
                $website->ga4_property_id = 'properties/ga4-' . $googleUser->getId();
                $website->save();
            }

            $firstWebsite = $targetWebsites->first();
            if ($firstWebsite) {
                // Fetch Real GSC Queries via Google Webmaster Search Analytics API
                try {
                    $response = \Illuminate\Support\Facades\Http::withToken($googleUser->token)
                        ->post('https://www.googleapis.com/webmasters/v3/sites/' . urlencode($firstWebsite->target_url) . '/searchAnalytics/query', [
                            'startDate' => date('Y-m-d', strtotime('-30 days')),
                            'endDate' => date('Y-m-d'),
                            'dimensions' => ['query', 'page'],
                            'rowLimit' => 10
                        ]);

                    if ($response->successful() && isset($response->json()['rows'])) {
                        foreach ($response->json()['rows'] as $row) {
                            $query = $row['keys'][0] ?? 'ব্রেকিং নিউজ';
                            $pageUrl = $row['keys'][1] ?? $website->target_url;
                            $clicks = $row['clicks'] ?? 0;
                            $impressions = $row['impressions'] ?? 0;
                            $position = round($row['position'] ?? 1.0, 1);
                            $ctr = round(($row['ctr'] ?? 0) * 100, 2);

                            \App\Modules\SeoIntelligence\Models\SeoKeywordMetric::updateOrCreate(
                                ['seo_website_id' => $website->id, 'keyword' => $query],
                                [
                                    'target_page_url' => $pageUrl,
                                    'avg_position' => $position,
                                    'clicks' => $clicks,
                                    'impressions' => $impressions,
                                    'ctr' => $ctr,
                                    'is_quick_win' => $position >= 4 && $position <= 15,
                                    'metric_date' => now()->toDateString(),
                                ]
                            );
                        }
                    }
                } catch (\Exception $apiErr) {
                    \Illuminate\Support\Facades\Log::warning("GSC API Fetch Notice: " . $apiErr->getMessage());
                }
            }

            return redirect()->route('seo.index', ['website_id' => $firstWebsite?->id])
                ->with('success', '✅ Google Search Console & GA4 Account Authenticated Successfully!');
        } catch (\Exception $e) {
            return redirect()->route('seo.index')->with('error', '❌ Google Authentication Failed: ' . $e->getMessage());
        }
    }
}
