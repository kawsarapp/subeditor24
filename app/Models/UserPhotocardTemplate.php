<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPhotocardTemplate extends Model
{
    use HasFactory;

    protected $table = 'user_photocard_templates';

    protected $fillable = [
        'user_id',
        'name',
        'frame_path',
        'layout_data',
        'is_default',
    ];

    protected $casts = [
        'layout_data' => 'array',
        'is_default'  => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFramePathAttribute($value)
    {
        if (empty($value)) {
            return '';
        }

        // Clean up legacy erroneous paths e.g. /storage/public/photocard_frames/...
        $cleaned = str_replace('/storage/public/photocard_frames/', '/storage/photocard_frames/', $value);
        $cleaned = str_replace('public/photocard_frames/', 'photocard_frames/', $cleaned);

        if (str_starts_with($cleaned, 'http://') || str_starts_with($cleaned, 'https://')) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '/storage/')) {
            return asset(ltrim($cleaned, '/'));
        }

        if (str_starts_with($cleaned, 'storage/')) {
            return asset($cleaned);
        }

        return asset('storage/' . ltrim($cleaned, '/'));
    }
}
