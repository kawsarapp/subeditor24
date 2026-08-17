<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\ScraperLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ScraperMonitorController extends Controller
{
    public function index(Request $request)
    {
        // 1. Get all websites (without global scopes so we can see all configured sites)
        $websites = Website::withoutGlobalScopes()->get();

        $websiteStats = [];
        $totalRunsToday = 0;
        $successRunsToday = 0;
        $failedRunsToday = 0;

        foreach ($websites as $web) {
            // Stats for the last 24 hours
            $totalRuns = ScraperLog::where('website_id', $web->id)
                ->where('created_at', '>=', now()->subDay())
                ->count();

            $successRuns = ScraperLog::where('website_id', $web->id)
                ->where('status', 'success')
                ->where('created_at', '>=', now()->subDay())
                ->count();

            $failedRuns = $totalRuns - $successRuns;

            $successRate = $totalRuns > 0 ? round(($successRuns / $totalRuns) * 100, 1) : 100.0;

            // Global stats summation
            $totalRunsToday += $totalRuns;
            $successRunsToday += $successRuns;
            $failedRunsToday += $failedRuns;

            // Inactivity status check (no success in last 3 hours)
            $recentSuccessCount = ScraperLog::where('website_id', $web->id)
                ->where('status', 'success')
                ->where('created_at', '>=', now()->subHours(3))
                ->count();

            $recentTotalCount = ScraperLog::where('website_id', $web->id)
                ->where('created_at', '>=', now()->subHours(3))
                ->count();

            // Status logic:
            // - 'active': Has at least 1 success run in last 3 hours.
            // - 'failing': Has attempts but 0 success runs in the last 3 hours.
            // - 'inactive': Has no runs at all in the last 3 hours.
            if ($recentTotalCount > 0 && $recentSuccessCount === 0) {
                $status = 'failing';
            } elseif ($recentTotalCount === 0) {
                $status = 'inactive';
            } else {
                $status = 'active';
            }

            // Get last attempt details
            $lastAttempt = ScraperLog::where('website_id', $web->id)
                ->latest('created_at')
                ->first();

            $websiteStats[] = [
                'website' => $web,
                'total_runs_24h' => $totalRuns,
                'success_runs_24h' => $successRuns,
                'failed_runs_24h' => $failedRuns,
                'success_rate_24h' => $successRate,
                'status' => $status,
                'last_attempt_at' => $lastAttempt ? $lastAttempt->created_at : null,
                'last_scraped_at' => $web->last_scraped_at,
            ];
        }

        // Global success rate
        $globalSuccessRate = $totalRunsToday > 0 ? round(($successRunsToday / $totalRunsToday) * 100, 1) : 100.0;

        // Paginated Failed logs list
        $failedLogs = ScraperLog::with('website')
            ->where('status', 'failed')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.scraper-monitor.index', compact(
            'websiteStats',
            'totalRunsToday',
            'successRunsToday',
            'failedRunsToday',
            'globalSuccessRate',
            'failedLogs'
        ));
    }
}
