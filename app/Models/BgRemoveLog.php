<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BgRemoveLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_image_url',
        'output_image_url',
        'credits_deducted',
        'status',
        'error_message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
