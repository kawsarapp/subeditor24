<?php

namespace App\Modules\SeoIntelligence\Models;

use Illuminate\Database\Eloquent\Model;

class SeoKeywordMetric extends Model
{
    protected $table = 'seo_keyword_metrics';

    protected $fillable = [
        'seo_website_id',
        'keyword',
        'target_page_url',
        'clicks',
        'impressions',
        'ctr',
        'avg_position',
        'is_quick_win',
        'trend_status',
        'metric_date',
    ];

    protected $casts = [
        'is_quick_win' => 'boolean',
        'clicks' => 'integer',
        'impressions' => 'integer',
        'ctr' => 'float',
        'avg_position' => 'float',
        'metric_date' => 'date',
    ];

    public function website()
    {
        return $this->belongsTo(SeoWebsite::class, 'seo_website_id');
    }
}
