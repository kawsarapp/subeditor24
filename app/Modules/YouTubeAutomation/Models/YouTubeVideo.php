<?php

namespace App\Modules\YouTubeAutomation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class YouTubeVideo extends Model
{
    protected $table = 'youtube_videos';

    protected $fillable = [
        'youtube_channel_id',
        'user_id',
        'video_id',
        'original_title',
        'original_description',
        'video_script',
        'original_tags',
        'original_privacy_status',
        'current_privacy_status',
        'thumbnail_url',
        'duration',
        'published_at',
        'youtube_category_id',
        'ai_title',
        'ai_title_variations',
        'ai_thumbnail_ideas',
        'ai_description',
        'ai_tags',
        'ai_hashtags',
        'ai_chapters',
        'ai_pinned_comment',
        'ai_search_intent_keywords',
        'seo_score',
        'status',
        'error_message',
        'optimized_at',
        'last_published_at',
    ];

    protected $casts = [
        'original_tags'             => 'array',
        'ai_title_variations'       => 'array',
        'ai_thumbnail_ideas'        => 'array',
        'ai_tags'                   => 'array',
        'ai_hashtags'               => 'array',
        'ai_chapters'               => 'array',
        'ai_search_intent_keywords' => 'array',
        'seo_score'                 => 'integer',
        'published_at'              => 'datetime',
        'optimized_at'              => 'datetime',
        'last_published_at'         => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(YouTubeChannel::class, 'youtube_channel_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getWatchUrlAttribute(): string
    {
        return 'https://www.youtube.com/watch?v=' . $this->video_id;
    }

    public function getStudioUrlAttribute(): string
    {
        return 'https://studio.youtube.com/video/' . $this->video_id . '/edit';
    }
}
