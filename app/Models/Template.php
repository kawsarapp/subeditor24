<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'thumbnail_url',
        'frame_url',
        'layout_data',
        'font_url',
        'is_active'
    ];

    // ২. layout_data ডাটাবেসে JSON হিসেবে থাকে, তাই এখানে array কাস্ট করা হলো
    protected $casts = [
        'layout_data' => 'array',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}