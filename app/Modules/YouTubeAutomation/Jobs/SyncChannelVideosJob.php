<?php

namespace App\Modules\YouTubeAutomation\Jobs;

use App\Modules\YouTubeAutomation\Models\YouTubeChannel;
use App\Modules\YouTubeAutomation\Models\YouTubeVideo;
use App\Modules\YouTubeAutomation\Models\YouTubeAutomationLog;
use App\Modules\YouTubeAutomation\Services\YouTubeApiService;
use App\Modules\YouTubeAutomation\Services\YouTubeAiSeoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncChannelVideosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public int $timeout = 180;

    protected int $channelId;

    public function __construct(int $channelId)
    {
        $this->channelId = $channelId;
    }

    public function handle(YouTubeApiService $apiService, YouTubeAiSeoService $aiSeoService): void
    {
        $channel = YouTubeChannel::find($this->channelId);
        if (!$channel || !$channel->is_active) {
            return;
        }

        try {
            $fetched = $apiService->fetchChannelVideos($channel, 20);

            foreach ($fetched as $v) {
                $video = YouTubeVideo::updateOrCreate(
                    [
                        'youtube_channel_id' => $channel->id,
                        'video_id'           => $v['video_id'],
                    ],
                    [
                        'user_id'                 => $channel->user_id,
                        'original_title'          => $v['title'],
                        'original_description'    => $v['description'],
                        'original_tags'           => $v['tags'],
                        'original_privacy_status' => $v['privacy_status'],
                        'current_privacy_status'  => $v['privacy_status'],
                        'thumbnail_url'           => $v['thumbnail_url'],
                        'duration'                => $v['duration'],
                        'published_at'            => $v['published_at'],
                        'youtube_category_id'     => $v['category_id'],
                    ]
                );

                // Auto-Pilot Logic: If video is unlisted/draft, and auto_pilot_enabled is true, optimize and publish!
                if ($channel->auto_pilot_enabled 
                    && in_array($v['privacy_status'], ['unlisted', 'private'], true)
                    && $video->status === 'synced'
                ) {
                    try {
                        $seo = $aiSeoService->optimizeVideo($video);
                        $video->update([
                            'ai_title'            => $seo['ai_title'],
                            'ai_title_variations' => $seo['ai_title_variations'],
                            'ai_description'      => $seo['ai_description'],
                            'ai_tags'             => $seo['ai_tags'],
                            'ai_hashtags'         => $seo['ai_hashtags'],
                            'ai_chapters'         => $seo['ai_chapters'],
                            'seo_score'           => $seo['seo_score'],
                            'status'              => 'optimized',
                            'optimized_at'        => Carbon::now(),
                        ]);

                        // Publish automatically to YouTube
                        $apiService->updateVideoMetadata(
                            $channel,
                            $video->video_id,
                            $seo['ai_title'],
                            $seo['ai_description'],
                            $seo['ai_tags'],
                            $channel->default_privacy ?? 'public',
                            $video->youtube_category_id
                        );

                        $video->update([
                            'current_privacy_status' => $channel->default_privacy ?? 'public',
                            'status'                 => 'published',
                            'last_published_at'      => Carbon::now(),
                        ]);

                        YouTubeAutomationLog::log(
                            $channel->user_id,
                            'auto_pilot',
                            "⚡ Auto-Pilot published video: {$seo['ai_title']}",
                            'success',
                            $channel->id,
                            $video->id
                        );
                    } catch (\Throwable $apErr) {
                        Log::warning("Auto-Pilot processing notice for video {$video->video_id}: " . $apErr->getMessage());
                    }
                }
            }

            $channel->update(['last_synced_at' => Carbon::now()]);

        } catch (\Throwable $e) {
            Log::error("SyncChannelVideosJob failed for channel ID {$this->channelId}: " . $e->getMessage());
        }
    }
}
