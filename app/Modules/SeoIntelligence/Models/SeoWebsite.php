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
        'crawl_status',
        'crawl_progress',
        'last_crawled_at',
        'crawl_error',
        'google_service_account_json',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'google_token_expires_at' => 'datetime',
        'seo_health_score' => 'integer',
        'crawl_progress' => 'integer',
        'last_crawled_at' => 'datetime',
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

    public function setGoogleServiceAccountJsonAttribute($value)
    {
        $this->attributes['google_service_account_json'] = $value ? Crypt::encryptString(is_array($value) ? json_encode($value) : $value) : null;
    }

    public function getGoogleServiceAccountJsonAttribute($value)
    {
        if (!$value) return null;
        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return $value;
        }
    }

    /**
     * Get parsed Service Account JSON data
     */
    public function getGoogleServiceAccountData(): ?array
    {
        $raw = $this->google_service_account_json;
        if (!$raw) {
            $path = storage_path("app/seo/service_account_{$this->id}.json");
            if (file_exists($path)) {
                $raw = @file_get_contents($path);
            }
        }
        if (!$raw) {
            return null;
        }

        $data = json_decode($raw, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Check if website has Google Service Account configured
     */
    public function hasServiceAccount(): bool
    {
        return !empty($this->getGoogleServiceAccountData());
    }

    /**
     * Get Service Account Client Email
     */
    public function getGoogleServiceAccountEmail(): ?string
    {
        $data = $this->getGoogleServiceAccountData();
        return $data['client_email'] ?? null;
    }

    /**
     * Get Service Account Project ID
     */
    public function getGoogleServiceAccountProjectId(): ?string
    {
        $data = $this->getGoogleServiceAccountData();
        return $data['project_id'] ?? null;
    }
}
