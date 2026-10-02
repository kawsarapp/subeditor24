<?php

namespace App\Modules\AudioNarration\Jobs;

use App\Models\NewsItem;
use App\Models\User;
use App\Modules\AudioNarration\Services\AudioNarrationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateNewsAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $newsId;
    public ?int $userId;
    public array $overrides;

    public int $tries = 3;
    public int $timeout = 180;

    /**
     * Create a new job instance.
     */
    public function __construct(int $newsId, ?int $userId = null, array $overrides = [])
    {
        $this->newsId    = $newsId;
        $this->userId    = $userId;
        $this->overrides = $overrides;
    }

    /**
     * Execute the job.
     */
    public function handle(AudioNarrationService $service): void
    {
        $news = NewsItem::find($this->newsId);
        if (!$news) {
            Log::warning("⚠️ GenerateNewsAudioJob: News Item #{$this->newsId} not found.");
            return;
        }

        $user = $this->userId ? User::find($this->userId) : null;

        Log::info("🚀 Processing GenerateNewsAudioJob for News #{$this->newsId}");

        $result = $service->generateForNews($news, $user, $this->overrides);

        if (!$result['success']) {
            Log::warning("⚠️ GenerateNewsAudioJob failed for News #{$this->newsId}: " . ($result['error'] ?? 'Unknown'));
        }
    }
}
