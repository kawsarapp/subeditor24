<?php

namespace App\Modules\YouTubeAutomation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\YouTubeAutomation\Models\YouTubeChannel;
use App\Modules\YouTubeAutomation\Models\YouTubeAutomationLog;
use App\Modules\YouTubeAutomation\Services\YouTubeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class YouTubeConnectController extends Controller
{
    protected YouTubeApiService $apiService;

    public function __construct(YouTubeApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Redirect to Google OAuth consent screen.
     */
    public function redirect()
    {
        try {
            $authUrl = $this->apiService->getAuthUrl();
            return redirect()->away($authUrl);
        } catch (\Throwable $e) {
            Log::error('YouTube OAuth Redirect Error: ' . $e->getMessage());
            return redirect()->route('youtube.channels.index')
                ->with('error', 'Google OAuth initialization failed. Please check Google Client ID & Secret in settings.');
        }
    }

    /**
     * Handle Google OAuth Callback.
     */
    public function callback(Request $request)
    {
        if ($request->has('error')) {
            $err = $request->get('error_description', $request->get('error'));
            return redirect()->route('youtube.channels.index')
                ->with('error', 'YouTube Authorization cancelled or failed: ' . $err);
        }

        $code = $request->get('code');
        if (empty($code)) {
            return redirect()->route('youtube.channels.index')
                ->with('error', 'No authorization code received from Google.');
        }

        $userId = Auth::id();

        try {
            // 1. Exchange code for tokens
            $tokens = $this->apiService->exchangeCodeForTokens($code);
            $accessToken = $tokens['access_token'] ?? null;
            $refreshToken = $tokens['refresh_token'] ?? null;
            $expiresIn = (int) ($tokens['expires_in'] ?? 3600);

            if (!$accessToken) {
                throw new \Exception('No access token returned from Google.');
            }

            // 2. Discover YouTube channels
            $channels = $this->apiService->fetchChannels($accessToken);

            if (empty($channels)) {
                return redirect()->route('youtube.channels.index')
                    ->with('error', 'No YouTube channel found under this Google Account. Please create a channel first.');
            }

            $connectedCount = 0;

            // 3. Save or update each discovered channel (multi-channel support)
            foreach ($channels as $chData) {
                $existing = YouTubeChannel::where('user_id', $userId)
                    ->where('channel_id', $chData['channel_id'])
                    ->first();

                // If existing channel already had a refresh token and new response didn't include one, keep the old one
                $finalRefreshToken = $refreshToken ?: ($existing?->refresh_token ?? null);

                $channel = YouTubeChannel::updateOrCreate(
                    [
                        'user_id'    => $userId,
                        'channel_id' => $chData['channel_id'],
                    ],
                    [
                        'channel_title'       => $chData['channel_title'],
                        'channel_description' => $chData['channel_description'],
                        'custom_url'          => $chData['custom_url'],
                        'thumbnail_url'       => $chData['thumbnail_url'],
                        'subscriber_count'    => $chData['subscriber_count'],
                        'video_count'         => $chData['video_count'],
                        'view_count'          => $chData['view_count'],
                        'access_token'        => $accessToken,
                        'refresh_token'       => $finalRefreshToken,
                        'token_expires_at'    => Carbon::now()->addSeconds($expiresIn),
                        'is_active'           => true,
                    ]
                );

                YouTubeAutomationLog::log(
                    $userId,
                    'auth',
                    "Connected YouTube channel: {$channel->channel_title}",
                    'success',
                    $channel->id
                );

                // Initial background sync for latest unlisted/draft videos
                try {
                    $fetchedVideos = $this->apiService->fetchChannelVideos($channel, 15);
                    foreach ($fetchedVideos as $vData) {
                        \App\Modules\YouTubeAutomation\Models\YouTubeVideo::updateOrCreate(
                            [
                                'youtube_channel_id' => $channel->id,
                                'video_id'           => $vData['video_id'],
                            ],
                            [
                                'user_id'                 => $userId,
                                'original_title'          => $vData['title'],
                                'original_description'    => $vData['description'],
                                'original_tags'           => $vData['tags'],
                                'original_privacy_status' => $vData['privacy_status'],
                                'current_privacy_status'  => $vData['privacy_status'],
                                'thumbnail_url'           => $vData['thumbnail_url'],
                                'duration'                => $vData['duration'],
                                'published_at'            => $vData['published_at'],
                                'youtube_category_id'     => $vData['category_id'],
                            ]
                        );
                    }
                    $channel->update(['last_synced_at' => Carbon::now()]);
                } catch (\Throwable $syncErr) {
                    Log::warning('Initial video sync notice: ' . $syncErr->getMessage());
                }

                $connectedCount++;
            }

            return redirect()->route('youtube.channels.index')
                ->with('success', "🎉 Successfully connected {$connectedCount} YouTube Channel(s) with permanent offline token!");

        } catch (\Throwable $e) {
            Log::error('YouTube Connect Callback Error: ' . $e->getMessage());
            YouTubeAutomationLog::log($userId, 'auth', 'Connection failed: ' . $e->getMessage(), 'failed');

            return redirect()->route('youtube.channels.index')
                ->with('error', 'Failed to connect YouTube Channel: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect a YouTube Channel.
     */
    public function disconnect($id)
    {
        $channel = YouTubeChannel::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $title = $channel->channel_title;
        $channel->delete();

        YouTubeAutomationLog::log(Auth::id(), 'auth', "Disconnected YouTube channel: {$title}", 'info');

        return redirect()->route('youtube.channels.index')
            ->with('success', "YouTube Channel '{$title}' has been disconnected successfully.");
    }
}
