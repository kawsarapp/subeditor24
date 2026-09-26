<?php

namespace App\Modules\YouTubeAutomation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class YouTubeAutomationLog extends Model
{
    protected $table = 'youtube_automation_logs';

    protected $fillable = [
        'user_id',
        'youtube_channel_id',
        'youtube_video_id',
        'action',
        'status',
        'message',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(YouTubeChannel::class, 'youtube_channel_id');
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(YouTubeVideo::class, 'youtube_video_id');
    }

    public static function log(
        int $userId,
        string $action,
        string $message,
        string $status = 'info',
        ?int $channelId = null,
        ?int $videoId = null,
        ?array $details = null
    ): self {
        return self::create([
            'user_id'            => $userId,
            'youtube_channel_id' => $channelId,
            'youtube_video_id'   => $videoId,
            'action'             => $action,
            'status'             => $status,
            'message'            => $message,
            'details'            => $details,
        ]);
    }
}
