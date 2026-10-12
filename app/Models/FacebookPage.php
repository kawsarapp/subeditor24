<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class FacebookPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'page_name',
        'page_id',
        'access_token',
        'is_active',
        'is_studio_default',
        'comment_link',
        'last_tested_at',
        'test_status',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'is_studio_default' => 'boolean',
        'comment_link'      => 'boolean',
        'last_tested_at'    => 'datetime',
    ];

    /**
     * Safely decrypt access token without crashing if plaintext or invalid payload
     */
    public function getAccessTokenAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            // Return raw value if not encrypted or decryption fails
            return $value;
        }
    }

    /**
     * Safely encrypt access token
     */
    public function setAccessTokenAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['access_token'] = null;
            return;
        }
        try {
            // Check if already encrypted to avoid double encryption
            Crypt::decryptString($value);
            $this->attributes['access_token'] = $value;
        } catch (\Throwable $e) {
            $this->attributes['access_token'] = Crypt::encryptString(trim($value));
        }
    }

    /**
     * Alias for code calling page_access_token
     */
    public function getPageAccessTokenAttribute(): ?string
    {
        return $this->access_token;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
