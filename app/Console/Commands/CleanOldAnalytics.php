<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsItem;
use Carbon\Carbon;

class CleanOldAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean analytics and news data older than 60 days to keep the database fast.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting analytics data cleanup...');
        
        $dateLimit = Carbon::now()->subDays(60);
        
        // Delete news_items entirely older than 60 days
        $deletedBaseNews = NewsItem::where('created_at', '<', $dateLimit)->delete();
        
        // Also clean up old credit histories
        $deletedCreditHistories = \App\Models\CreditHistory::where('created_at', '<', $dateLimit)->delete();
        
        // Clear caches related to analytics
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        
        $this->info("Cleanup completed successfully. Deleted {$deletedBaseNews} news items and {$deletedCreditHistories} credit history records older than 60 days.");
    }
}
