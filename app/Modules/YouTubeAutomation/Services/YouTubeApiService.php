<?php

namespace App\Modules\YouTubeAutomation\Services;

use App\Modules\YouTubeAutomation\Models\YouTubeChannel;
use App\Modules\YouTubeAutomation\Models\YouTubeAutomationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class YouTubeApiService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $redirectUri;

    public function __construct()
    {
        $this->clientId = config('services.youtube.client_id') 
            ?? config('services.google.client_id') 
            ?? env('YOUTUBE_CLIENT_ID', env('GOOGLE_CLIENT_ID', ''));

        $this->clientSecret = config('services.youtube.client_secret') 
            ?? config('services.google.client_secret') 
            ?? env('YOUTUBE_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET', ''));

        $this->redirectUri = config('services.youtube.redirect') 
            ?? env('YOUTUBE_REDIRECT_URI', url('/youtube/auth/callback'));
    }

    /**
     * Generate Google OAuth authorization URL requesting offline access (refresh_token).
     */
    public function getAuthUrl(): string
    {
        $params = [
            'client_id'             => $this->clientId,
            'redirect_uri'          => $this->redirectUri,
            'response_type'         => 'code',
            'scope'                 => implode(' ', [
                'https://www.googleapis.com/auth/youtube.force-ssl',
                'https://www.googleapis.com/auth/youtube.readonly',
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile',
            ]),
            'access_type'           => 'offline',
            'prompt'                => 'consent', // Forces Google to always return a refresh token
            'include_granted_scopes'=> 'true',
        ];

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query($params);
    }

    /**
     * Exchange OAuth authorization code for Access & Refresh tokens.
     */
    public function exchangeCodeForTokens(string $code): array
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->redirectUri,
        ]);

        if (!$response->successful()) {
            Log::error('YouTube Token Exchange Error', ['body' => $response->body()]);
            throw new \Exception('Failed to exchange authorization code: ' . ($response->json('error_description') ?? $response->body()));
        }

        return $response->json();
    }

    /**
     * Refresh access token for a YouTubeChannel if expired.
     */
    public function refreshAccessToken(YouTubeChannel $channel): string
    {
        if (!$channel->isTokenExpired() && !empty($channel->access_token)) {
            return $channel->access_token;
        }

        if (empty($channel->refresh_token)) {
            throw new \Exception("No refresh token found for channel {$channel->channel_title}. Please reconnect the channel.");
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'refresh_token' => $channel->refresh_token,
            'grant_type'    => 'refresh_token',
        ]);

        if (!$response->successful()) {
            Log::error('YouTube Token Refresh Error', ['channel_id' => $channel->id, 'body' => $response->body()]);
            YouTubeAutomationLog::log(
                $channel->user_id,
                'token_refresh',
                "Failed to refresh YouTube token for channel: {$channel->channel_title}",
                'failed',
                $channel->id
            );
            throw new \Exception('Failed to refresh YouTube token: ' . ($response->json('error_description') ?? $response->body()));
        }

        $data = $response->json();
        $newAccessToken = $data['access_token'];
        $expiresIn = (int) ($data['expires_in'] ?? 3600);

        $channel->update([
            'access_token'     => $newAccessToken,
            'token_expires_at' => Carbon::now()->addSeconds($expiresIn),
        ]);

        YouTubeAutomationLog::log(
            $channel->user_id,
            'token_refresh',
            "Access token refreshed successfully for channel: {$channel->channel_title}",
            'success',
            $channel->id
        );

        return $newAccessToken;
    }

    /**
     * Fetch all YouTube channels associated with the authenticated Google token.
     */
    public function fetchChannels(string $accessToken): array
    {
        $response = Http::withToken($accessToken)->get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'snippet,contentDetails,statistics',
            'mine' => 'true',
        ]);

        if (!$response->successful()) {
            throw new \Exception('Could not fetch YouTube channels: ' . $response->body());
        }

        $items = $response->json('items') ?? [];
        $channels = [];

        foreach ($items as $item) {
            $snippet = $item['snippet'] ?? [];
            $stats = $item['statistics'] ?? [];

            $channels[] = [
                'channel_id'          => $item['id'],
                'channel_title'       => $snippet['title'] ?? 'Untitled Channel',
                'channel_description' => $snippet['description'] ?? '',
                'custom_url'          => $snippet['customUrl'] ?? null,
                'thumbnail_url'       => $snippet['thumbnails']['medium']['url'] ?? ($snippet['thumbnails']['default']['url'] ?? null),
                'subscriber_count'    => (int) ($stats['subscriberCount'] ?? 0),
                'video_count'         => (int) ($stats['videoCount'] ?? 0),
                'view_count'          => (int) ($stats['viewCount'] ?? 0),
                'uploads_playlist_id' => $item['contentDetails']['relatedPlaylists']['uploads'] ?? null,
            ];
        }

        return $channels;
    }

    /**
     * Sync and fetch uploaded/unlisted/draft videos for a channel.
     */
    public function fetchChannelVideos(YouTubeChannel $channel, int $maxResults = 25): array
    {
        $token = $this->refreshAccessToken($channel);

        // 1. Get Uploads playlist ID if not cached
        $chResponse = Http::withToken($token)->get('https://www.googleapis.com/youtube/v3/channels', [
            'part' => 'contentDetails',
            'id'   => $channel->channel_id,
        ]);

        $uploadsPlaylistId = $chResponse->json('items.0.contentDetails.relatedPlaylists.uploads');
        if (!$uploadsPlaylistId) {
            return [];
        }

        // 2. Fetch Playlist items
        $playlistResponse = Http::withToken($token)->get('https://www.googleapis.com/youtube/v3/playlistItems', [
            'part'       => 'snippet,contentDetails,status',
            'playlistId' => $uploadsPlaylistId,
            'maxResults' => $maxResults,
        ]);

        if (!$playlistResponse->successful()) {
            throw new \Exception('Failed to fetch playlist items: ' . $playlistResponse->body());
        }

        $items = $playlistResponse->json('items') ?? [];
        $videoIds = array_filter(array_map(fn($it) => $it['contentDetails']['videoId'] ?? null, $items));

        if (empty($videoIds)) {
            return [];
        }

        // 3. Fetch detailed video data (status, snippet)
        $videosResponse = Http::withToken($token)->get('https://www.googleapis.com/youtube/v3/videos', [
            'part' => 'snippet,status,contentDetails',
            'id'   => implode(',', $videoIds),
        ]);

        if (!$videosResponse->successful()) {
            throw new \Exception('Failed to fetch video details: ' . $videosResponse->body());
        }

        $videoDetails = $videosResponse->json('items') ?? [];
        $formattedVideos = [];

        foreach ($videoDetails as $v) {
            $snip = $v['snippet'] ?? [];
            $stat = $v['status'] ?? [];

            $formattedVideos[] = [
                'video_id'                => $v['id'],
                'title'                   => $snip['title'] ?? 'Untitled Video',
                'description'             => $snip['description'] ?? '',
                'tags'                    => $snip['tags'] ?? [],
                'privacy_status'          => $stat['privacyStatus'] ?? 'unlisted', // 'public', 'unlisted', 'private'
                'thumbnail_url'           => $snip['thumbnails']['maxres']['url'] 
                    ?? ($snip['thumbnails']['high']['url'] 
                    ?? ($snip['thumbnails']['medium']['url'] ?? null)),
                'duration'                => $v['contentDetails']['duration'] ?? null,
                'published_at'            => !empty($snip['publishedAt']) ? Carbon::parse($snip['publishedAt']) : null,
                'category_id'             => $snip['categoryId'] ?? '25', // 25 = News & Politics
            ];
        }

        return $formattedVideos;
    }

    /**
     * Update video metadata on YouTube (Title, Description, Tags, Category, and Privacy Status).
     */
    public function updateVideoMetadata(
        YouTubeChannel $channel,
        string $videoId,
        string $title,
        string $description,
        array $tags = [],
        string $privacyStatus = 'public',
        ?string $categoryId = '25'
    ): array {
        $token = $this->refreshAccessToken($channel);

        $payload = [
            'id' => $videoId,
            'snippet' => [
                'title'       => mb_substr($title, 0, 100, 'UTF-8'), // YouTube max 100 chars
                'description' => mb_substr($description, 0, 5000, 'UTF-8'), // YouTube max 5000 chars
                'tags'        => array_slice($tags, 0, 40), // YouTube max 500 chars total
                'categoryId'  => $categoryId ?: '25',
            ],
            'status' => [
                'privacyStatus'           => in_array($privacyStatus, ['public', 'unlisted', 'private'], true) ? $privacyStatus : 'public',
                'selfDeclaredMadeForKids' => false,
            ],
        ];

        $response = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->put('https://www.googleapis.com/youtube/v3/videos?part=snippet,status', $payload);

        if (!$response->successful()) {
            Log::error('YouTube Video Update Error', [
                'video_id' => $videoId,
                'status'   => $response->status(),
                'body'     => $response->body(),
            ]);
            throw new \Exception('YouTube API Error (' . $response->status() . '): ' . ($response->json('error.message') ?? $response->body()));
        }

        return $response->json();
    }
}
