<?php

namespace App\Modules\SeoIntelligence\Models;

use Illuminate\Database\Eloquent\Model;

class SeoPageAudit extends Model
{
    protected $table = 'seo_page_audits';

    protected $fillable = [
        'seo_website_id',
        'url',
        'url_hash',
        'status_code',
        'title',
        'meta_description',
        'h1_tag',
        'canonical_url',
        'word_count',
        'load_time_ms',
        'is_indexed',
        'issues_found',
        'schema_detected',
        'crawled_at',
    ];

    protected $casts = [
        'issues_found' => 'array',
        'schema_detected' => 'array',
        'is_indexed' => 'boolean',
        'crawled_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(SeoWebsite::class, 'seo_website_id');
    }
}
