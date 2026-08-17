<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScraperLog extends Model
{
    use HasFactory;

    // We only have created_at timestamp
    const UPDATED_AT = null;

    protected $fillable = [
        'website_id',
        'url',
        'job_type',
        'status',
        'strategy',
        'http_status',
        'error_message',
        'retry_count',
        'created_at',
    ];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }
}
