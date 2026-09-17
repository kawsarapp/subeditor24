<?php

namespace App\Jobs;

use App\Models\CentralNewsPool;
use App\Models\Website;
use App\Services\NewsScraperService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCentralNewsItem implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $link;
    protected $title;
    protected $websiteId;
    protected $listImage;
    protected $sourceName;
    protected $sourceDomain;

    public $timeout = 180;
    public $tries = 2;
    public $backoff = [3, 10];

    public function __construct($link, $title, $websiteId, $listImage = null, $sourceName = null, $sourceDomain = null)
    {
        $this->link = $link;
        $this->title = $title;
        $this->websiteId = $websiteId;
        $this->listImage = $listImage;
        $this->sourceName = $sourceName;
        $this->sourceDomain = $sourceDomain;
    }

    public function handle(NewsScraperService $scraper)
    {
        try {
            $slugHash = CentralNewsPool::generateHash($this->link);

            // 1. Fast O(1) Duplicate Check
            if (CentralNewsPool::where('slug_hash', $slugHash)->exists()) {
                return;
            }

            // 2. Fetch Website Selectors
            $website = Website::withoutGlobalScopes()->find($this->websiteId);
            $customSelectors = $website ? ['content' => $website->selector_content] : [];

            // 3. Ultra Scraper
            $scrapedData = $scraper->scrape($this->link, $customSelectors, null);

            if (!$scrapedData || empty($scrapedData['body'])) {
                return;
            }

            // 4. Validate Content (Reject error pages)
            $finalTitle = !empty($scrapedData['title']) && strlen($scrapedData['title']) > 10
                ? trim($scrapedData['title'])
                : trim($this->title);

            $errorPatterns = ["This site can't be reached", "403 Forbidden", "Access Denied", "Attention Required! | Cloudflare"];
            foreach ($errorPatterns as $errPattern) {
                if (stripos($finalTitle, $errPattern) !== false || stripos($scrapedData['body'] ?? '', $errPattern) !== false) {
                    return;
                }
            }

            $finalImage = $scrapedData['image'] ?? $this->listImage;

            // 5. Store in Central Pool
            CentralNewsPool::create([
                'website_id'    => $this->websiteId,
                'title'         => $finalTitle,
                'slug_hash'     => $slugHash,
                'original_link' => $this->link,
                'thumbnail_url' => $finalImage,
                'content'       => $scrapedData['body'],
                'source_name'   => $this->sourceName ?: ($website?->name ?? 'News Source'),
                'source_domain' => $this->sourceDomain ?: parse_url($this->link, PHP_URL_HOST),
                'published_at'  => now(),
            ]);

            Log::info("⚡ [Central Pool] News Captured: {$finalTitle} from {$this->sourceName}");

        } catch (\Exception $e) {
            Log::warning("⚠️ [Central Pool Job Error] {$this->link}: " . $e->getMessage());
        }
    }
}
