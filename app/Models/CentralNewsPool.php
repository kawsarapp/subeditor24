<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentralNewsPool extends Model
{
    use HasFactory;

    protected $table = 'central_news_pool';

    protected $fillable = [
        'website_id',
        'title',
        'slug_hash',
        'original_link',
        'thumbnail_url',
        'content',
        'source_name',
        'source_domain',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    /**
     * Generate deterministic SHA-256 hash of URL for O(1) deduplication
     */
    public static function generateHash(string $url): string
    {
        $cleanUrl = trim(strtolower($url));
        $cleanUrl = rtrim($cleanUrl, '/');
        // Remove tracking query params (utm_*, fbclid, etc.)
        $parsed = parse_url($cleanUrl);
        $base = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . ($parsed['path'] ?? '');
        return hash('sha256', $base);
    }
}
