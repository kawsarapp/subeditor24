<?php

namespace App\Modules\SeoIntelligence\Jobs;

use App\Modules\SeoIntelligence\Models\SeoWebsite;
use App\Modules\SeoIntelligence\Services\WebsiteCrawlerEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExecuteSeoCrawlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 300;

    public int $websiteId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $websiteId)
    {
        $this->websiteId = $websiteId;
    }

    /**
     * Execute the job.
     */
    public function handle(WebsiteCrawlerEngine $crawlerEngine): void
    {
        $website = SeoWebsite::find($this->websiteId);
        if (!$website) {
            Log::warning("ExecuteSeoCrawlJob: SeoWebsite with ID {$this->websiteId} not found.");
            return;
        }

        $website->update([
            'crawl_status' => 'running',
            'crawl_progress' => 5,
            'crawl_error' => null,
        ]);

        try {
            $result = $crawlerEngine->crawlWebsite($website);

            $website->refresh();

            if (!empty($result['success'])) {
                $website->update([
                    'crawl_status' => 'completed',
                    'crawl_progress' => 100,
                    'last_crawled_at' => now(),
                    'crawl_error' => null,
                ]);
                Log::info("ExecuteSeoCrawlJob completed successfully for {$website->domain} (ID: {$website->id}).");
            } else {
                $errorMessage = $result['message'] ?? 'Crawl execution failed.';
                $website->update([
                    'crawl_status' => 'failed',
                    'crawl_progress' => 0,
                    'crawl_error' => $errorMessage,
                ]);
                Log::warning("ExecuteSeoCrawlJob finished with error for {$website->domain}: {$errorMessage}");
            }
        } catch (Throwable $e) {
            Log::error("ExecuteSeoCrawlJob encountered an exception for website ID {$this->websiteId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            $website->update([
                'crawl_status' => 'failed',
                'crawl_progress' => 0,
                'crawl_error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(?Throwable $exception): void
    {
        $error = $exception ? $exception->getMessage() : 'Job timed out or failed permanently.';
        Log::error("ExecuteSeoCrawlJob permanently failed for website ID {$this->websiteId}: {$error}");

        SeoWebsite::where('id', $this->websiteId)->update([
            'crawl_status' => 'failed',
            'crawl_progress' => 0,
            'crawl_error' => $error,
        ]);
    }
}
