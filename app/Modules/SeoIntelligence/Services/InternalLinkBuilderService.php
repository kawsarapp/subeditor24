<?php

namespace App\Modules\SeoIntelligence\Services;

use App\Modules\SeoIntelligence\Models\SeoWebsite;

class InternalLinkBuilderService
{
    /**
     * Generate smart internal link suggestions with Human Approval Guard
     */
    public function generateLinkSuggestions(SeoWebsite $website): array
    {
        $audits = $website->pageAudits()->whereNotNull('title')->take(10)->get();

        $suggestions = [];
        foreach ($audits as $index => $audit) {
            $relatedAudit = $audits->where('id', '!=', $audit->id)->first() ?? $audit;
            
            $suggestions[] = [
                'id' => 'link_sug_' . ($index + 1),
                'source_url' => $audit->url,
                'target_url' => $relatedAudit->url,
                'keyword' => $relatedAudit->title ? mb_substr($relatedAudit->title, 0, 30) . '...' : 'আজকের বিশেষ খবর',
                'context_paragraph' => "আজকের গুরুত্বপূর্ণ সংবাদের সাথে সম্পর্কিত প্রতিবেদন পড়ুন: \"" . ($relatedAudit->title ?? 'বিস্তারিত বুলেটিন') . "\"।",
                'status' => 'pending_human_approval',
                'created_at' => now()->diffForHumans(),
            ];
        }

        return $suggestions;
    }
}
