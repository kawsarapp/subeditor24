<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DynamicCronService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    /**
     * Webhook endpoint to trigger dynamic cron execution via HTTP GET/POST
     */
    public function handleWebCron(Request $request, DynamicCronService $cronService)
    {
        $token = $request->get('token');
        $expectedToken = config('app.key');

        // Optional token verification if token param is passed
        if ($token && $token !== substr($expectedToken, 0, 16)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid cron execution token.'
            ], 403);
        }

        try {
            $cronService->recordHeartbeat('web_http');
            
            // Execute Laravel Scheduler
            Artisan::call('schedule:run');

            return response()->json([
                'success'    => true,
                'message'    => 'Dynamic Web-Cron executed successfully.',
                'timestamp'  => now()->toDateTimeString(),
                'health'     => $cronService->getHealthStatus()
            ]);
        } catch (\Throwable $e) {
            Log::error("WebCron HTTP error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cron execution error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * JSON Health status endpoint for monitoring tools
     */
    public function health(DynamicCronService $cronService)
    {
        return response()->json($cronService->getHealthStatus());
    }
}
