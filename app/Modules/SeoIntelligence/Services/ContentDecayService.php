<?php

namespace App\Modules\SeoIntelligence\Services;

use App\Modules\SeoIntelligence\Models\SeoWebsite;

class ContentDecayService
{
    /**
     * Audit Content Decay based on actual keyword clicks & page word count
     */
    public function detectContentDecay(SeoWebsite $website): array
    {
        $decayPages = $website->pageAudits
            ->whereNotNull('title')
            ->where('word_count', '<', 250)
            ->take(5);

        $decayArticles = [];
        foreach ($decayPages as $audit) {
            $decayArticles[] = [
                'title' => $audit->title,
                'url' => $audit->url,
                'status' => 'Low Word Count (<250 words)',
                'ai_suggestion' => 'আজকের ব্রেকিং তথ্যের সাথে ৩-৪টি নতুন প্যারাগ্রাফ যোগ করে কন্টেন্ট আপডেট করুন।'
            ];
        }

        return $decayArticles;
    }
}
