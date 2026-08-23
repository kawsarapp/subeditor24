<?php

namespace App\Modules\SeoIntelligence\Models;

use Illuminate\Database\Eloquent\Model;

class SeoCoreWebVital extends Model
{
    protected $table = 'seo_core_web_vitals';

    protected $fillable = [
        'seo_website_id',
        'url',
        'lcp_sec',
        'inp_ms',
        'cls_score',
        'fcp_sec',
        'ttfb_ms',
        'overall_rating',
    ];

    public function website()
    {
        return $this->belongsTo(SeoWebsite::class, 'seo_website_id');
    }
}
