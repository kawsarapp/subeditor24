<?php

namespace App\Modules\SeoIntelligence\Services;

use App\Modules\SeoIntelligence\Models\SeoWebsite;

class CanonicalDuplicateService
{
    /**
     * Audit Canonical Tags and Duplicate Content Protection
     */
    public function auditCanonicalTags(SeoWebsite $website): array
    {
        $totalChecked = $website->pageAudits->count();
        $validCanonical = $website->pageAudits->whereNotNull('canonical_url')->count();
        $missingCanonical = $totalChecked - $validCanonical;

        $riskLabel = $missingCanonical === 0 
            ? 'Low Risk (0 Duplicate Syndication Penalty)' 
            : ($missingCanonical > 5 ? 'High Risk (' . $missingCanonical . ' Pages Missing Canonical Tag)' : 'Moderate Risk (' . $missingCanonical . ' Missing Canonical)');

        return [
            'total_checked' => $totalChecked,
            'valid_canonical' => $validCanonical,
            'missing_canonical' => $missingCanonical,
            'duplicate_risk' => $riskLabel,
        ];
    }
}
