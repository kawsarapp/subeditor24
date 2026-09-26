<?php

namespace App\Modules\YouTubeAutomation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\YouTubeAutomation\Models\YouTubeChannel;
use App\Modules\YouTubeAutomation\Models\YouTubeVideo;
use App\Modules\YouTubeAutomation\Models\YouTubeAutomationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class YouTubeChannelController extends Controller
{
    /**
     * Display connected channels dashboard.
     */
    public function index()
    {
        $userId = Auth::id();
        $channels = YouTubeChannel::where('user_id', $userId)
            ->withCount([
                'videos',
                'videos as draft_count' => fn($q) => $q->whereIn('current_privacy_status', ['unlisted', 'private']),
                'videos as optimized_count' => fn($q) => $q->where('status', 'optimized'),
                'videos as published_count' => fn($q) => $q->where('status', 'published'),
            ])
            ->latest()
            ->get();

        $stats = [
            'total_channels'  => $channels->count(),
            'total_videos'    => YouTubeVideo::where('user_id', $userId)->count(),
            'draft_videos'    => YouTubeVideo::where('user_id', $userId)->whereIn('current_privacy_status', ['unlisted', 'private'])->count(),
            'optimized_ready' => YouTubeVideo::where('user_id', $userId)->where('status', 'optimized')->count(),
            'total_published' => YouTubeVideo::where('user_id', $userId)->where('status', 'published')->count(),
        ];

        $recentLogs = YouTubeAutomationLog::where('user_id', $userId)
            ->with('channel')
            ->latest()
            ->take(10)
            ->get();

        return view('youtube.channels.index', compact('channels', 'stats', 'recentLogs'));
    }

    /**
     * Update Channel Settings (Auto-Pilot, Language, Footer, Tags template).
     */
    public function updateSettings(Request $request, $id)
    {
        $channel = YouTubeChannel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'default_language'          => 'required|string|in:bn,en,hi,ar',
            'title_style'               => 'required|string|in:viral_curiosity,breaking_news,seo_keyword,storytelling',
            'default_privacy'           => 'required|string|in:public,unlisted,private',
            'auto_pilot_enabled'        => 'nullable|boolean',
            'opt_title'                 => 'nullable|boolean',
            'opt_description'           => 'nullable|boolean',
            'opt_tags'                  => 'nullable|boolean',
            'opt_hashtags'              => 'nullable|boolean',
            'opt_chapters'              => 'nullable|boolean',
            'opt_thumbnail_ideas'       => 'nullable|boolean',
            'opt_pinned_comment'        => 'nullable|boolean',
            'opt_dual_language'         => 'nullable|boolean',
            'append_footer'             => 'nullable|boolean',
            'merge_brand_tags'          => 'nullable|boolean',
            'custom_ai_prompt'          => 'nullable|string|max:2000',
            'custom_tags_template'      => 'nullable|string|max:1000',
            'custom_description_footer' => 'nullable|string|max:2000',
        ]);

        $channel->update([
            'default_language'          => $request->default_language,
            'title_style'               => $request->title_style,
            'default_privacy'           => $request->default_privacy,
            'auto_pilot_enabled'        => $request->boolean('auto_pilot_enabled'),
            'opt_title'                 => $request->boolean('opt_title'),
            'opt_description'           => $request->boolean('opt_description'),
            'opt_tags'                  => $request->boolean('opt_tags'),
            'opt_hashtags'              => $request->boolean('opt_hashtags'),
            'opt_chapters'              => $request->boolean('opt_chapters'),
            'opt_thumbnail_ideas'       => $request->boolean('opt_thumbnail_ideas'),
            'opt_pinned_comment'        => $request->boolean('opt_pinned_comment'),
            'opt_dual_language'         => $request->boolean('opt_dual_language'),
            'append_footer'             => $request->boolean('append_footer'),
            'merge_brand_tags'          => $request->boolean('merge_brand_tags'),
            'custom_ai_prompt'          => $request->custom_ai_prompt,
            'custom_tags_template'      => $request->custom_tags_template,
            'custom_description_footer' => $request->custom_description_footer,
        ]);

        return redirect()->back()
            ->with('success', "Settings for channel '{$channel->channel_title}' updated successfully!");
    }

    /**
     * Toggle Auto-Pilot Mode (AJAX).
     */
    public function toggleAutoPilot(Request $request, $id)
    {
        $channel = YouTubeChannel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $channel->auto_pilot_enabled = !$channel->auto_pilot_enabled;
        $channel->save();

        YouTubeAutomationLog::log(
            Auth::id(),
            'auto_pilot',
            "Auto-Pilot " . ($channel->auto_pilot_enabled ? 'Enabled' : 'Disabled') . " for {$channel->channel_title}",
            'info',
            $channel->id
        );

        return response()->json([
            'success'            => true,
            'auto_pilot_enabled' => $channel->auto_pilot_enabled,
            'message'            => $channel->auto_pilot_enabled ? 'Auto-Pilot Activated ⚡' : 'Auto-Pilot Paused',
        ]);
    }
}
