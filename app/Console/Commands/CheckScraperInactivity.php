<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;
use App\Models\ScraperLog;
use App\Models\NewsItem;
use App\Mail\ScraperInactivityAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CheckScraperInactivity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:check-inactivity';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if any scraper source has been inactive (no success runs) for the last 3 hours and alert via email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Checking scraper inactivity...");

        // Fetch all websites
        $websites = Website::withoutGlobalScopes()->get();

        foreach ($websites as $web) {
            // Count successful runs in the last 3 hours
            $successCount = ScraperLog::where('website_id', $web->id)
                ->where('status', 'success')
                ->where('created_at', '>=', now()->subHours(3))
                ->count();

            // If successCount is 0, it means it has been inactive for the last 3 hours!
            if ($successCount === 0) {
                $this->warn("Source inactive: {$web->name}");

                // To prevent email spamming, check if an alert was already sent in the last 6 hours
                $cacheKey = 'scraper_alert_sent_' . $web->id;
                if (Cache::has($cacheKey)) {
                    $this->info("Alert already sent recently for {$web->name}. Skipping.");
                    continue;
                }

                // Get last successful scrape time from website column or NewsItem
                $lastScrapedAt = $web->last_scraped_at;
                if (!$lastScrapedAt) {
                    $lastScrapedAt = NewsItem::where('website_id', $web->id)
                        ->latest('created_at')
                        ->value('created_at');
                }

                // Calculate inactive duration
                $inactiveDuration = 'Unknown';
                if ($lastScrapedAt) {
                    $diff = Carbon::parse($lastScrapedAt)->diff(now());
                    $inactiveDuration = sprintf('%d days, %d hours, %d minutes', $diff->d, $diff->h, $diff->i);
                } else {
                    $inactiveDuration = 'More than 3 hours (Never scraped successfully)';
                }

                // Get last attempt details
                $lastAttempt = ScraperLog::where('website_id', $web->id)
                    ->latest('created_at')
                    ->first();

                $lastAttemptAt = $lastAttempt ? $lastAttempt->created_at : null;
                $lastStrategy = $lastAttempt ? $lastAttempt->strategy : 'N/A';
                $lastHttpCode = $lastAttempt ? $lastAttempt->http_status : 'N/A';
                $lastError = $lastAttempt ? $lastAttempt->error_message : 'No recent attempts recorded';

                $dashboardUrl = url('/admin/scraper-monitor');

                try {
                    // Send Email alert
                    Mail::to('kawsarapps72@gmail.com')->send(new ScraperInactivityAlert(
                        $web->name,
                        $web->url,
                        $lastAttemptAt,
                        $lastScrapedAt,
                        $inactiveDuration,
                        $lastStrategy,
                        $lastHttpCode,
                        $lastError,
                        $dashboardUrl
                    ));

                    // Cache for 6 hours
                    Cache::put($cacheKey, true, now()->addHours(6));
                    Log::info("📨 Scraper Inactivity Alert Email sent for {$web->name} to kawsarapps72@gmail.com");
                    $this->info("Email sent successfully!");

                } catch (\Exception $e) {
                    Log::error("❌ Failed to send scraper inactivity email: " . $e->getMessage());
                }
            }
        }

        $this->info("Inactivity check completed.");
    }
}
