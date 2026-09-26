<?php

namespace App\Modules\YouTubeAutomation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\YouTubeAutomation\Models\YouTubeChannel;
use App\Modules\YouTubeAutomation\Models\YouTubeVideo;
use App\Modules\YouTubeAutomation\Models\YouTubeAutomationLog;
use App\Modules\YouTubeAutomation\Services\YouTubeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class YouTubeVideoController extends Controller
{
    protected YouTubeApiService $apiService;

    public function __construct(YouTubeApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Display all videos list with channel & status filters.
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $channels = YouTubeChannel::where('user_id', $userId)->get();

        $selectedChannelId = $request->get('channel_id');
        $status = $request->get('status', 'all');
        $search = $request->get('q');

        $query = YouTubeVideo::where('user_id', $userId)->with('channel');

        if ($selectedChannelId && $selectedChannelId !== 'all') {
            $query->where('youtube_channel_id', $selectedChannelId);
        }

        if ($status === 'drafts') {
            $query->whereIn('current_privacy_status', ['unlisted', 'private']);
        } elseif ($status === 'optimized') {
            $query->where('status', 'optimized');
        } elseif ($status === 'published') {
            $query->where('status', 'published');
        } elseif ($status === 'synced') {
            $query->where('status', 'synced');
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('original_title', 'like', "%{$search}%")
                  ->orWhere('ai_title', 'like', "%{$search}%")
                  ->orWhere('video_id', 'like', "%{$search}%");
            });
        }

        $videos = $query->latest()->paginate(16)->withQueryString();

        return view('youtube.videos.index', compact('videos', 'channels', 'selectedChannelId', 'status', 'search'));
    }

    /**
     * Sync videos for a specific YouTube channel.
     */
    public function sync(Request $request, $channelId)
    {
        $channel = YouTubeChannel::where('id', $channelId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        try {
            $fetched = $this->apiService->fetchChannelVideos($channel, 25);
            $newCount = 0;

            foreach ($fetched as $v) {
                $video = YouTubeVideo::updateOrCreate(
                    [
                        'youtube_channel_id' => $channel->id,
                        'video_id'           => $v['video_id'],
                    ],
                    [
                        'user_id'                 => Auth::id(),
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

                if ($video->wasRecentlyCreated) {
                    $newCount++;
                }
            }

            $channel->update(['last_synced_at' => Carbon::now()]);

            YouTubeAutomationLog::log(
                Auth::id(),
                'sync',
                "Synced " . count($fetched) . " videos ({$newCount} new) from {$channel->channel_title}",
                'success',
                $channel->id
            );

            return redirect()->back()
                ->with('success', "Synced " . count($fetched) . " videos successfully from {$channel->channel_title}!");

        } catch (\Throwable $e) {
            YouTubeAutomationLog::log(
                Auth::id(),
                'sync',
                "Failed to sync channel {$channel->channel_title}: " . $e->getMessage(),
                'failed',
                $channel->id
            );

            return redirect()->back()
                ->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }
}
