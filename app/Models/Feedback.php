<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'description',
        'category',
        'status',
        'admin_response',
        'votes_count',
    ];

    /**
     * Relationship to the user who created this feedback.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship to votes.
     */
    public function votes()
    {
        return $this->hasMany(FeedbackVote::class);
    }

    /**
     * Check if a specific user has voted on this feedback.
     */
    public function isVotedBy(?int $userId): bool
    {
        if (!$userId) return false;
        return $this->votes()->where('user_id', $userId)->exists();
    }

    /**
     * Masked author display for public privacy protection.
     */
    public function getAnonymousAuthorAttribute(): string
    {
        // Deterministic masked identifier based on user_id
        $hashNum = ($this->user_id * 37) % 900 + 100;
        return "Editor #" . $hashNum;
    }
}
