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
}
