<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DynamicCronService
{
    const HEARTBEAT_KEY = 'laravel_cron_last_heartbeat';
    const STATS_KEY = 'laravel_cron_stats';
    const LOCK_KEY = 'laravel_cron_execution_lock';

    /**
     * Record a heartbeat from a real CLI or Web cron execution
     */
    public function recordHeartbeat(string $source = 'cli'): void
    {
        Cache::put(self::HEARTBEAT_KEY, now()->timestamp, 86400);

        $stats = Cache::get(self::STATS_KEY, [
            'total_runs'   => 0,
            'last_source'  => 'unknown',
            'last_run_at'  => null,
            'status'       => 'healthy'
        ]);

        $stats['total_runs'] = ($stats['total_runs'] ?? 0) + 1;
        $stats['last_source'] = $source;
        $stats['last_run_at'] = now()->toDateTimeString();
        $stats['status'] = 'healthy';

        Cache::put(self::STATS_KEY, $stats, 86400 * 7);
    }

    /**
     * Check if cron is healthy (has run within last 3 minutes)
     */
    public function getHealthStatus(): array
    {
        $lastHeartbeat = Cache::get(self::HEARTBEAT_KEY);
        $stats = Cache::get(self::STATS_KEY, [
            'total_runs'   => 0,
            'last_source'  => 'never',
            'last_run_at'  => null,
            'status'       => 'not_configured'
        ]);

        $secondsAgo = $lastHeartbeat ? (now()->timestamp - $lastHeartbeat) : null;

        $isHealthy = $lastHeartbeat && ($secondsAgo <= 180);

        return [
            'healthy'      => $isHealthy,
            'seconds_ago'  => $secondsAgo,
            'last_run_at'  => $stats['last_run_at'] ?? 'Never',
            'last_source'  => $stats['last_source'] ?? 'None',
            'total_runs'   => $stats['total_runs'] ?? 0,
            'status_label' => $isHealthy ? '🟢 Active & Running' : ($lastHeartbeat ? '🟡 Delayed' : '🔴 Inactive / Not Running')
        ];
    }

    /**
     * Self-healing Dynamic Cron Runner (Triggers if server crontab is not active)
     */
    public function triggerDynamicCronIfDue(): bool
    {
        $lastHeartbeat = Cache::get(self::HEARTBEAT_KEY);
        $secondsAgo = $lastHeartbeat ? (now()->timestamp - $lastHeartbeat) : 999;

        // If cron has already run within the last 60 seconds, no need to trigger
        if ($secondsAgo < 60) {
            return false;
        }

        // Use cache lock to prevent multiple concurrent web requests from running schedule at the same instant
        $lock = Cache::lock(self::LOCK_KEY, 50);

        if ($lock->get()) {
            try {
                // Update heartbeat immediately so other requests won't attempt
                $this->recordHeartbeat('web_fallback');

                // If fastcgi is available, finish HTTP response first so user experience is instant
                if (function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }

                // Execute Laravel Scheduler
                Artisan::call('schedule:run');

                Log::info("⚡ Dynamic Web-Cron executed successfully (Self-Healing Cron).");
                return true;
            } catch (\Throwable $e) {
                Log::warning("⚠️ Dynamic Web-Cron execution notice: " . $e->getMessage());
            } finally {
                $lock->release();
            }
        }

        return false;
    }
}
