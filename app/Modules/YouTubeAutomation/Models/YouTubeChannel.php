<?php

namespace App\Modules\YouTubeAutomation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class YouTubeChannel extends Model
{
    protected $table = 'youtube_channels';

    protected $fillable = [
        'user_id',
        'channel_id',
        'channel_title',
        'channel_description',
        'custom_url',
        'thumbnail_url',
        'subscriber_count',
        'video_count',
        'view_count',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'auto_pilot_enabled',
        'default_language',
        'default_privacy',
        'title_style',
        'opt_title',
        'opt_description',
        'opt_tags',
        'opt_hashtags',
        'opt_chapters',
        'opt_thumbnail_ideas',
        'opt_pinned_comment',
        'opt_dual_language',
        'append_footer',
        'merge_brand_tags',
        'custom_ai_prompt',
        'custom_tags_template',
        'custom_description_footer',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'token_expires_at'    => 'datetime',
        'last_synced_at'      => 'datetime',
        'auto_pilot_enabled'  => 'boolean',
        'opt_title'           => 'boolean',
        'opt_description'     => 'boolean',
        'opt_tags'            => 'boolean',
        'opt_hashtags'        => 'boolean',
        'opt_chapters'        => 'boolean',
        'opt_thumbnail_ideas' => 'boolean',
        'opt_pinned_comment'  => 'boolean',
        'opt_dual_language'   => 'boolean',
        'append_footer'       => 'boolean',
        'merge_brand_tags'    => 'boolean',
        'is_active'           => 'boolean',
        'subscriber_count'   => 'integer',
        'video_count'        => 'integer',
        'view_count'         => 'integer',
        // Encrypt tokens for high security
        'access_token'       => 'encrypted',
        'refresh_token'      => 'encrypted',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(YouTubeVideo::class, 'youtube_channel_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(YouTubeAutomationLog::class, 'youtube_channel_id');
    }

    public function isTokenExpired(): bool
    {
        if (!$this->token_expires_at) {
            return true;
        }
        // Buffer of 60 seconds before actual expiry
        return $this->token_expires_at->subSeconds(60)->isPast();
    }
}
