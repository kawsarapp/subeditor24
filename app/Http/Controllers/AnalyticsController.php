<?php

namespace App\Http\Controllers;

use App\Models\NewsItem;
use App\Models\User;
use App\Models\UserSetting;
use App\Models\CreditHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin' && !$admin->hasPermission('can_analytics')) {
            abort(403, 'Unauthorized access - You need Analytics permission.');
        }

        $days = (int) $request->input('days', 30);
        if (!in_array($days, [7, 15, 30, 60])) {
            $days = 30;
        }
        
        $since = now()->subDays($days)->startOfDay();
        $cacheKey = "analytics_{$admin->id}_{$days}";

        $data = Cache::remember($cacheKey, 3600, function() use ($admin, $since) {
            // Get all relevant user IDs (admin + all staff)
            $staffIds = User::where('parent_id', $admin->id)
                            ->where('role', 'staff')
                            ->pluck('id')->toArray();
                            
            $allIds = array_merge([$admin->id], $staffIds);

            // Fetch overall news stats
            $newsStats = NewsItem::withoutGlobalScopes()
                ->where(function($query) use ($allIds, $staffIds) {
                    $query->whereIn('user_id', $allIds)
                          ->orWhereIn('staff_id', $staffIds);
                })
                ->where('created_at', '>=', $since)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN website_id IS NOT NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as scraped,
                    SUM(CASE WHEN reporter_id IS NOT NULL THEN 1 ELSE 0 END) as reporter_submitted,
                    SUM(CASE WHEN website_id IS NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as manual_count,
                    SUM(CASE WHEN is_rewritten = 1 THEN 1 ELSE 0 END) as ai_rewritten,
                    SUM(CASE WHEN card_created_at IS NOT NULL THEN 1 ELSE 0 END) as cards_created,
                    SUM(card_download_count) as card_downloads,
                    SUM(CASE WHEN fb_status = \'success\' THEN 1 ELSE 0 END) as fb_posts,
                    SUM(CASE WHEN tg_status = \'success\' THEN 1 ELSE 0 END) as tg_posts
                ')
                ->first();
            
            // For older records where is_rewritten might not be reliable, fall back to credit history
            $aiRewritesFromCredits = CreditHistory::whereIn('user_id', $allIds)
                ->where('action_type', 'ai_rewrite')
                ->where('created_at', '>=', $since)
                ->count();
                
            if ($aiRewritesFromCredits > $newsStats->ai_rewritten) {
                $newsStats->ai_rewritten = $aiRewritesFromCredits;
            }

            // Get Staff Breakdown
            $staffBreakdown = User::whereIn('id', $staffIds)->get()->map(function($staff) use ($since) {
                $stats = NewsItem::withoutGlobalScopes()
                    ->where('staff_id', $staff->id)
                    ->where('created_at', '>=', $since)
                    ->selectRaw('
                        SUM(CASE WHEN website_id IS NOT NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as scraped,
                        SUM(CASE WHEN website_id IS NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as manual_count,
                        SUM(CASE WHEN is_rewritten = 1 THEN 1 ELSE 0 END) as ai_rewritten,
                        SUM(CASE WHEN card_created_at IS NOT NULL THEN 1 ELSE 0 END) as cards_created,
                        SUM(card_download_count) as card_downloads
                    ')
                    ->first();
                
                $staff->scraped = $stats->scraped ?? 0;
                $staff->manual = $stats->manual_count ?? 0;
                $staff->ai_rewritten = $stats->ai_rewritten ?? 0;
                $staff->cards_created = $stats->cards_created ?? 0;
                $staff->card_downloads = $stats->card_downloads ?? 0;
                
                return $staff;
            });

            // Admin themselves stats
            $adminStats = NewsItem::withoutGlobalScopes()
                ->where('user_id', $admin->id)
                ->whereNull('staff_id')
                ->where('created_at', '>=', $since)
                ->selectRaw('
                    SUM(CASE WHEN website_id IS NOT NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as scraped,
                    SUM(CASE WHEN website_id IS NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as manual_count,
                    SUM(CASE WHEN is_rewritten = 1 THEN 1 ELSE 0 END) as ai_rewritten,
                    SUM(CASE WHEN card_created_at IS NOT NULL THEN 1 ELSE 0 END) as cards_created,
                    SUM(card_download_count) as card_downloads
                ')
                ->first();

            $adminBreakdown = (object)[
                'name' => $admin->name . ' (Admin)',
                'scraped' => $adminStats->scraped ?? 0,
                'manual' => $adminStats->manual_count ?? 0,
                'ai_rewritten' => $adminStats->ai_rewritten ?? 0,
                'cards_created' => $adminStats->cards_created ?? 0,
                'card_downloads' => $adminStats->card_downloads ?? 0,
            ];
            
            $staffBreakdown->prepend($adminBreakdown);

            // ROI Calculation
            $roiConfig = null;
            if ($admin->role !== 'super_admin') {
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin && $superAdmin->settings) {
                    $roiConfig = $superAdmin->settings->roi_config;
                }
            } else {
                $roiConfig = $admin->settings->roi_config ?? null;
            }
            
            if (is_string($roiConfig)) {
                $roiConfig = json_decode($roiConfig, true);
            }

            $hourlyRate = $roiConfig['hourly_rate'] ?? 100;
            $newsMinutes = $roiConfig['news_minutes'] ?? 20;
            $cardMinutes = $roiConfig['card_minutes'] ?? 15;

            $totalScraped = $newsStats->scraped ?? 0;
            $totalCardsCreated = $newsStats->cards_created ?? 0;

            $timeSavedMinutes = ($totalScraped * $newsMinutes) + ($totalCardsCreated * $cardMinutes);
            $timeSavedHours = round($timeSavedMinutes / 60, 1);
            $costSaved = round($timeSavedHours * $hourlyRate, 2);

            // Daily Trend Query for Chart.js
            $trendStats = NewsItem::withoutGlobalScopes()
                ->where(function($query) use ($allIds, $staffIds) {
                    $query->whereIn('user_id', $allIds)
                          ->orWhereIn('staff_id', $staffIds);
                })
                ->where('created_at', '>=', $since)
                ->selectRaw('
                    DATE(created_at) as date,
                    SUM(CASE WHEN website_id IS NOT NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as scraped,
                    SUM(CASE WHEN website_id IS NULL AND reporter_id IS NULL THEN 1 ELSE 0 END) as manual_count,
                    SUM(CASE WHEN is_rewritten = 1 THEN 1 ELSE 0 END) as ai_rewritten,
                    SUM(CASE WHEN card_created_at IS NOT NULL THEN 1 ELSE 0 END) as cards_created
                ')
                ->groupBy('date')
                ->orderBy('date', 'ASC')
                ->get();
                
            $chartDates = [];
            $chartScraped = [];
            $chartManual = [];
            $chartAi = [];
            $chartCards = [];
            
            $currentDate = clone $since;
            while ($currentDate <= now()) {
                $dateStr = $currentDate->format('Y-m-d');
                $stat = $trendStats->firstWhere('date', $dateStr);
                
                $chartDates[] = $currentDate->format('d M');
                $chartScraped[] = $stat ? $stat->scraped : 0;
                $chartManual[] = $stat ? $stat->manual_count : 0;
                $chartAi[] = $stat ? $stat->ai_rewritten : 0;
                $chartCards[] = $stat ? $stat->cards_created : 0;
                
                $currentDate->addDay();
            }
            
            $chartTrendData = [
                'labels' => $chartDates,
                'scraped' => $chartScraped,
                'manual' => $chartManual,
                'ai' => $chartAi,
                'cards' => $chartCards
            ];

            return compact('newsStats', 'staffBreakdown', 'timeSavedHours', 'costSaved', 'hourlyRate', 'newsMinutes', 'cardMinutes', 'chartTrendData');
        });

        $data['days'] = $days;
        return view('admin.analytics.index', $data);
    }
}
