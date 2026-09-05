<?php

namespace App\Services;

use App\Models\NewsItem;
use App\Models\User;
use Illuminate\Support\Collection;

class NewsDeduplicationService
{
    /**
     * Clean and tokenize string into unique word set (Bengali & English friendly)
     */
    public function tokenize(string $text): array
    {
        // Remove HTML tags
        $clean = strip_tags($text);
        
        // Lowercase (for English/alphanumeric)
        $clean = mb_strtolower($clean, 'UTF-8');
        
        // Remove punctuation and special symbols except Bengali and alphanumeric characters
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean);
        
        // Split into words
        $words = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);
        
        // Common Bengali & English stop words to filter out for higher precision
        $stopWords = [
            'এবং', 'ও', 'কিন্তু', 'বা', 'যে', 'সে', 'তিনি', 'তারা', 'করে', 'করা', 'হয়েছে',
            'হবে', 'হয়', 'একটি', 'এই', 'সেই', 'কোন', 'কি', 'কেন', 'কিভাবে', 'থেকে', 'দ্বারা',
            'দিয়ে', 'জন্য', 'পর', 'আগে', 'হতে', 'পর্যন্ত', 'এর', 'কে', 'তে', 'এ', 'র',
            'the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'in', 'for', 'to', 'of'
        ];
        $stopWordsMap = array_flip($stopWords);
        
        $filtered = array_filter($words, function ($w) use ($stopWordsMap) {
            return strlen($w) > 2 && !isset($stopWordsMap[$w]);
        });

        return array_values(array_unique($filtered));
    }

    /**
     * Compute similarity percentage between two titles or texts (0 to 100)
     */
    public function calculateSimilarity(string $text1, string $text2): float
    {
        if (empty($text1) || empty($text2)) return 0.0;
        
        // Exact match
        if (trim($text1) === trim($text2)) return 100.0;

        $tokens1 = $this->tokenize($text1);
        $tokens2 = $this->tokenize($text2);

        if (empty($tokens1) || empty($tokens2)) {
            // Fallback to basic string similarity if token sets are empty
            similar_text($text1, $text2, $percent);
            return round($percent, 1);
        }

        // Jaccard similarity between word token sets
        $intersection = array_intersect($tokens1, $tokens2);
        $union = array_unique(array_merge($tokens1, $tokens2));

        $jaccard = count($union) > 0 ? (count($intersection) / count($union)) : 0;

        // String level similarity
        similar_text($text1, $text2, $strPercent);

        // Weighted combination: 70% Jaccard word overlap + 30% character sequence similarity
        $combined = ($jaccard * 100 * 0.7) + ($strPercent * 0.3);

        return round(min(100.0, max(0.0, $combined)), 1);
    }

    /**
     * Check duplicates for a specific title in tenant's news pool (SaaS isolated)
     *
     * @param int|User $user
     * @param string $title
     * @param int|null $excludeId
     * @param float $threshold Minimum similarity percentage (e.g. 60%)
     * @return array
     */
    public function findDuplicates($user, string $title, ?int $excludeId = null, float $threshold = 60.0): array
    {
        if (empty(trim($title))) return [];

        $effectiveAdminId = $this->resolveAdminId($user);
        
        // Cache candidates for 90 seconds to minimize database hits during rapid live typing
        $cacheKey = "tenant_recent_candidates_{$effectiveAdminId}";
        $candidates = \Illuminate\Support\Facades\Cache::remember($cacheKey, 90, function () use ($user, $effectiveAdminId) {
            $query = NewsItem::withoutGlobalScopes()
                ->select(['id', 'user_id', 'website_id', 'title', 'ai_title', 'original_link', 'thumbnail_url', 'status', 'published_at', 'created_at'])
                ->with(['website' => function ($q) { $q->withoutGlobalScopes()->select(['id', 'name']); }])
                ->where(function ($q) use ($user, $effectiveAdminId) {
                    $uid = is_object($user) ? $user->id : $user;
                    $q->where('user_id', $effectiveAdminId)
                      ->orWhere('user_id', $uid);
                })
                ->where('created_at', '>=', now()->subDays(3));

            return $query->orderBy('created_at', 'desc')->limit(60)->get();
        });

        $duplicates = [];

        foreach ($candidates as $item) {
            if ($excludeId && $item->id == $excludeId) {
                continue;
            }

            $candidateTitle = $item->ai_title ?: $item->title;
            $similarity = $this->calculateSimilarity($title, $candidateTitle);

            if ($similarity >= $threshold) {
                $duplicates[] = [
                    'id'            => $item->id,
                    'title'         => $candidateTitle,
                    'website_name'  => $item->website->name ?? 'Custom / Reporter',
                    'original_link' => $item->original_link,
                    'published_at'  => $item->published_at ? $item->published_at->diffForHumans() : $item->created_at->diffForHumans(),
                    'similarity'    => $similarity,
                    'status'        => $item->status,
                    'thumbnail_url' => $item->thumbnail_url
                ];
            }
        }

        // Sort highest similarity first
        usort($duplicates, function ($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        return $duplicates;
    }

    /**
     * Annotate a collection of NewsItems in-memory with duplicate flags (for fast view rendering)
     */
    public function annotateCollection($newsItems)
    {
        if (method_exists($newsItems, 'items')) {
            $items = $newsItems->items();
        } elseif ($newsItems instanceof Collection) {
            $items = $newsItems->all();
        } else {
            $items = (array)$newsItems;
        }

        $count = count($items);

        // Initialize default duplicate info
        foreach ($items as $item) {
            $item->is_duplicate = false;
            $item->duplicate_info = null;
        }

        // Pairwise comparison across the loaded collection
        for ($i = 0; $i < $count; $i++) {
            $itemA = $items[$i];
            $titleA = $itemA->ai_title ?: $itemA->title;

            for ($j = $i + 1; $j < $count; $j++) {
                $itemB = $items[$j];
                $titleB = $itemB->ai_title ?: $itemB->title;

                $similarity = $this->calculateSimilarity($titleA, $titleB);

                if ($similarity >= 60.0) {
                    $sourceA = $itemA->website->name ?? 'অনলাইন সোর্স';
                    $sourceB = $itemB->website->name ?? 'অনলাইন সোর্স';

                    $itemB->is_duplicate = true;
                    $itemB->duplicate_info = [
                        'matched_id'     => $itemA->id,
                        'matched_title'  => $titleA,
                        'matched_source' => $sourceA,
                        'similarity'     => $similarity
                    ];

                    if (!$itemA->is_duplicate) {
                        $itemA->is_duplicate = true;
                        $itemA->duplicate_info = [
                            'matched_id'     => $itemB->id,
                            'matched_title'  => $titleB,
                            'matched_source' => $sourceB,
                            'similarity'     => $similarity
                        ];
                    }
                }
            }
        }

        return $newsItems;
    }

    /**
     * Helper to resolve admin ID for multi-tenant SaaS scoping
     */
    private function resolveAdminId($user): int
    {
        if (is_numeric($user)) {
            $userObj = User::find($user);
            return ($userObj && in_array($userObj->role, ['staff', 'reporter']) && $userObj->parent_id) 
                ? $userObj->parent_id 
                : (int)$user;
        }

        if ($user instanceof User) {
            return in_array($user->role, ['staff', 'reporter']) && $user->parent_id 
                ? $user->parent_id 
                : $user->id;
        }

        return 0;
    }
}
