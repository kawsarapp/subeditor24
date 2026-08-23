<?php

namespace App\Modules\SeoIntelligence\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;

class SeoWebsite extends Model
{
    protected $table = 'seo_websites';

    protected $fillable = [
        'user_id',
        'domain',
        'target_url',
        'verification_txt_token',
        'is_verified',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'gsc_property_id',
        'ga4_property_id',
        'cms_detected',
        'sitemap_url',
        'robots_txt_url',
        'seo_health_score',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'google_token_expires_at' => 'datetime',
        'seo_health_score' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pageAudits()
    {
        return $this->hasMany(SeoPageAudit::class, 'seo_website_id');
    }

    public function keywordMetrics()
    {
        return $this->hasMany(SeoKeywordMetric::class, 'seo_website_id');
    }

    public function coreWebVitals()
    {
        return $this->hasMany(SeoCoreWebVital::class, 'seo_website_id');
    }

    // Encrypted token accessors & mutators for bank-grade security
    public function setGoogleAccessTokenAttribute($value)
    {
        $this->attributes['google_access_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getGoogleAccessTokenAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function setGoogleRefreshTokenAttribute($value)
    {
        $this->attributes['google_refresh_token'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getGoogleRefreshTokenAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }
}
