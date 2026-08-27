<?php

namespace App\Modules\SeoIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoInstantIndexingLog extends Model
{
    protected $table = 'seo_instant_indexing_logs';

    protected $fillable = [
        'seo_website_id',
        'url',
        'engine',
        'api_status',
        'response_code',
        'indexing_status',
        'notes',
        'pushed_at',
    ];

    protected $casts = [
        'pushed_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(SeoWebsite::class, 'seo_website_id');
    }
}
