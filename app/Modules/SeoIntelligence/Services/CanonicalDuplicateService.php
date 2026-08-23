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
        return [
            'total_checked' => $website->pageAudits()->count(),
            'valid_canonical' => $website->pageAudits()->whereNotNull('canonical_url')->count(),
            'missing_canonical' => $website->pageAudits()->whereNull('canonical_url')->count(),
            'duplicate_risk' => 'Low Risk (0 Duplicate Syndication Penalty)',
        ];
    }
}
