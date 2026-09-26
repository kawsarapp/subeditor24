<?php

namespace App\Modules\YouTubeAutomation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\YouTubeAutomation\Models\YouTubeVideo;
use App\Modules\YouTubeAutomation\Models\YouTubeAutomationLog;
use App\Modules\YouTubeAutomation\Services\YouTubeApiService;
use App\Modules\YouTubeAutomation\Services\YouTubeAiSeoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class YouTubeStudioController extends Controller
{
    protected YouTubeApiService $apiService;
    protected YouTubeAiSeoService $aiSeoService;

    public function __construct(YouTubeApiService $apiService, YouTubeAiSeoService $aiSeoService)
    {
        $this->apiService = $apiService;
        $this->aiSeoService = $aiSeoService;
    }

    /**
     * Show the AI Video SEO Studio for a video.
     */
    public function show($id)
    {
        $video = YouTubeVideo::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('channel')
            ->firstOrFail();

        return view('youtube.studio.show', compact('video'));
    }

    /**
     * Generate / Regenerate AI SEO Metadata (AJAX & Form).
     */
    public function optimizeAi(Request $request, $id)
    {
        $video = YouTubeVideo::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('channel')
            ->firstOrFail();

        if ($request->has('video_script')) {
            $video->video_script = $request->video_script;
        }

        $video->status = 'optimizing';
        $video->error_message = null;
        $video->save();

        try {
            $seoResult = $this->aiSeoService->optimizeVideo($video);

            $video->update([
                'ai_title'            => $seoResult['ai_title'],
                'ai_title_variations' => $seoResult['ai_title_variations'],
                'ai_description'      => $seoResult['ai_description'],
                'ai_tags'             => $seoResult['ai_tags'],
                'ai_hashtags'         => $seoResult['ai_hashtags'],
                'ai_chapters'         => $seoResult['ai_chapters'],
                'seo_score'           => $seoResult['seo_score'],
                'status'              => 'optimized',
                'optimized_at'        => Carbon::now(),
                'error_message'       => null,
            ]);

            YouTubeAutomationLog::log(
                Auth::id(),
                'ai_seo',
                "AI SEO generated successfully for video: {$video->ai_title}",
                'success',
                $video->youtube_channel_id,
                $video->id
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'AI SEO Optimization completed! 🎉',
                    'video'   => $video,
                ]);
            }

            return redirect()->back()
                ->with('success', 'AI SEO Optimization completed successfully!');

        } catch (\Throwable $e) {
            $video->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            YouTubeAutomationLog::log(
                Auth::id(),
                'ai_seo',
                "AI SEO failed for video ID {$video->video_id}: " . $e->getMessage(),
                'failed',
                $video->youtube_channel_id,
                $video->id
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'AI Optimization failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'AI Optimization failed: ' . $e->getMessage());
        }
    }

    /**
     * Save manual edits to SEO metadata.
     */
    public function saveMetadata(Request $request, $id)
    {
        $video = YouTubeVideo::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'ai_title'       => 'required|string|max:100',
            'ai_description' => 'required|string|max:5000',
            'ai_tags'        => 'nullable',
        ]);

        $tags = is_array($request->ai_tags) 
            ? $request->ai_tags 
            : array_filter(array_map('trim', explode(',', (string) $request->ai_tags)));

        $video->update([
            'ai_title'       => $request->ai_title,
            'ai_description' => $request->ai_description,
            'ai_tags'        => $tags,
            'status'         => 'optimized',
        ]);

        return redirect()->back()
            ->with('success', 'Metadata saved successfully! You can now publish to YouTube.');
    }

    /**
     * 1-Click Update Metadata & Publish to YouTube.
     */
    public function publishNow(Request $request, $id)
    {
        $video = YouTubeVideo::where('id', $id)
            ->where('user_id', Auth::id())
            ->with('channel')
            ->firstOrFail();

        $request->validate([
            'privacy_status' => 'required|string|in:public,unlisted,private',
            'title'          => 'nullable|string|max:100',
            'description'    => 'nullable|string|max:5000',
            'tags'           => 'nullable',
        ]);

        $finalTitle = $request->title ?: ($video->ai_title ?: $video->original_title);
        $finalDesc = $request->description ?: ($video->ai_description ?: $video->original_description);
        
        $tags = $request->filled('tags')
            ? (is_array($request->tags) ? $request->tags : array_map('trim', explode(',', (string) $request->tags)))
            : ($video->ai_tags ?: ($video->original_tags ?: []));

        $privacyStatus = $request->privacy_status;

        $video->update(['status' => 'publishing']);

        try {
            $this->apiService->updateVideoMetadata(
                $video->channel,
                $video->video_id,
                $finalTitle,
                $finalDesc,
                $tags,
                $privacyStatus,
                $video->youtube_category_id
            );

            $video->update([
                'current_privacy_status' => $privacyStatus,
                'ai_title'               => $finalTitle,
                'ai_description'         => $finalDesc,
                'ai_tags'                => $tags,
                'status'                 => 'published',
                'last_published_at'      => Carbon::now(),
                'error_message'          => null,
            ]);

            YouTubeAutomationLog::log(
                Auth::id(),
                'publish',
                "Successfully published video '{$finalTitle}' ({$privacyStatus}) to channel: {$video->channel->channel_title}",
                'success',
                $video->youtube_channel_id,
                $video->id
            );

            return redirect()->route('youtube.videos.index')
                ->with('success', "🚀 Video successfully updated and set to '" . ucfirst($privacyStatus) . "' on YouTube!");

        } catch (\Throwable $e) {
            $video->update([
                'status'        => 'failed',
                'error_message' => 'Publish failed: ' . $e->getMessage(),
            ]);

            YouTubeAutomationLog::log(
                Auth::id(),
                'publish',
                "Publish failed for video {$video->video_id}: " . $e->getMessage(),
                'failed',
                $video->youtube_channel_id,
                $video->id
            );

            return redirect()->back()
                ->with('error', 'YouTube Update/Publish failed: ' . $e->getMessage());
        }
    }
}
