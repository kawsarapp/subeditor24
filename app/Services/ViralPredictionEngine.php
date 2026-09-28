<?php

namespace App\Services;

use App\Models\NewsItem;
use App\Models\Website;
use Illuminate\Support\Collection;

class ViralPredictionEngine
{
    protected GoogleTrendsService $googleTrendsService;

    public function __construct(GoogleTrendsService $googleTrendsService)
    {
        $this->googleTrendsService = $googleTrendsService;
    }

    /**
     * Common Bengali Stopwords to filter out during clustering to prevent false-positive matches
     */
    protected array $stopWords = [
        'ও', 'এবং', 'কিন্তু', 'বা', 'অথবা', 'হলো', 'হবে', 'হয়েছে', 'হলে', 'হওয়ায়', 'হওয়ার', 'হয়ে', 'হচ্ছে',
        'নিয়ে', 'করে', 'করা', 'করল', 'করার', 'করেছে', 'করতে', 'থেকে', 'পর', 'পর্যন্ত', 'পরের', 'থাকা',
        'গেছে', 'গেল', 'যাওয়া', 'এক', 'দুই', 'তিন', 'চার', 'পাঁচ', 'ছয়', 'সাত', 'আট', 'নয়', 'দশ',
        'এই', 'সেই', 'ওই', 'তার', 'তাদের', 'তিনি', 'তিনিও', 'তাকে', 'আমি', 'আমরা', 'তুমি', 'তোমরা',
        'আপনি', 'আপনারা', 'যে', 'যা', 'যার', 'যাদের', 'কোন', 'কোনো', 'কিছু', 'সব', 'সকল', 'অন্য',
        'অন্যান্য', 'আগে', 'পরে', 'সাথে', 'সঙ্গে', 'মধ্যে', 'ভেতরে', 'বাইরে', 'উপরে', 'নিচে', 'কাছে',
        'জন্য', 'কারণে', 'মতো', 'ছাড়া', 'বাদে', 'শুধু', 'মাত্র', 'কেন', 'কি', 'কী', 'কিভাবে', 'কখন',
        'কোথায়', 'এমন', 'কেমন', 'তখন', 'এখন', 'তখনই', 'এখনই', 'আজ', 'কাল', 'গতকাল', 'আগামীকাল',
        'দিন', 'রাত', 'বছর', 'মাস', 'সপ্তাহ', 'বলে', 'বলেন', 'জানান', 'জানায়', 'দাবী', 'দাবি',
        'খবর', 'সংবাদ', 'প্রতিবেদন', 'নতুন', 'পুরাতন', 'বড়', 'ছোট', 'প্রথম', 'শেষ', 'দেখা', 'দেওয়ার',
        'দেওয়া', 'দিলেন', 'দিল', 'দেয়', 'নেওয়ার', 'নেওয়া', 'নিলেন', 'নিল', 'নেয়', 'জানা', 'গেলে'
    ];

    /**
     * Routine non-viral boilerplate terms to filter out
     */
    protected array $routineNoisePatterns = [
        'আবহাওয়ার খবর',
        'আবহাওয়ার পূর্বাভাস',
        'আজকের রাশিফল',
        'রাশিফল',
        'নামাজের সময়সূচি',
        'সোনার দাম',
        'টাকার রেট',
        'মুদ্রার বিনিময় হার',
        'পাসপোর্টের জন্য',
        'পাসপোর্ট পাওয়ার নিয়ম'
    ];

    /**
     * High-Impact Viral & Breaking Keywords with explicit score weightings
     */
    protected array $highImpactKeywords = [
        'ব্রেকিং'       => 8,
        'জরুরি'        => 7,
        'সরাসরি'       => 6,
        'লাইভ'         => 6,
        'গ্রেপ্তার'    => 7,
        'আটক'          => 7,
        'নিহত'         => 8,
        'হামলা'        => 7,
        'হাইকোর্ট'     => 6,
        'সুপ্রিম কোর্ট' => 6,
        'দুদক'         => 6,
        'মামলা'        => 5,
        'আদালত'        => 5,
        'কারাদণ্ড'     => 5,
        'ককটেল'        => 6,
        'গুলি'         => 7,
        'ফাঁস'         => 7,
        'ভাইরাল'       => 6,
        'ভিডিও'        => 5,
        'অডিও'         => 6,
        'তোলপাড়'       => 6,
        'চাঞ্চল্যকর'   => 6,
        'নির্বাচন'     => 6,
        'প্রধান উপদেষ্টা' => 7,
        'উপদেষ্টা'     => 5,
        'রাষ্ট্রপতি'   => 5,
        'পদত্যাগ'      => 7,
        'স্থগিত'       => 5,
        'নিষিদ্ধ'      => 6,
        'শাকিব'        => 5,
        'সাকিব'        => 5,
        'মেসি'         => 5,
        'রেকর্ড'       => 5,
        'বিপিএল'       => 5,
        'বিশ্বকাপ'     => 6
    ];

    /**
     * Analyze, Cluster into Master Story Topics, and rank news items
     */
    public function calculateViralPredictions(Collection $newsItems): Collection
    {
        // 1. Noise Reduction Filter
        $filteredItems = $newsItems->filter(function ($item) {
            $title = trim($item->title ?? '');
            if (mb_strlen($title) < 10) return false;
            foreach ($this->routineNoisePatterns as $noise) {
                if (mb_strpos($title, $noise) !== false) return false;
            }
            return true;
        });

        if ($filteredItems->isEmpty()) {
            return collect();
        }

        // 2. Fetch live Google Trends BD search queries
        $googleTrends = $this->googleTrendsService->getLiveGoogleTrendingKeywords();

        // 3. Cluster into Story Topic Groups
        $clusters = [];
        $assignedItemIds = [];

        foreach ($filteredItems as $item) {
            if (in_array($item->id, $assignedItemIds)) continue;

            $clusterArticles = [$item];
            $assignedItemIds[] = $item->id;
            $portalNames = [$item->website->name ?? 'Portal'];

            $targetTitle = $item->title ?? '';
            $targetKeywords = $this->extractSignificantKeywords($targetTitle);
            $targetBigrams = $this->extractBigrams($targetTitle);

            // Find matching articles from other news items
            foreach ($filteredItems as $candidate) {
                if (in_array($candidate->id, $assignedItemIds)) continue;

                $candTitle = $candidate->title ?? '';
                $candBigrams = $this->extractBigrams($candTitle);
                $bigramMatch = array_intersect($targetBigrams, $candBigrams);

                $isMatch = false;
                if (!empty($bigramMatch)) {
                    $isMatch = true;
                } else {
                    $candKeywords = $this->extractSignificantKeywords($candTitle);
                    $kwMatch = array_intersect($targetKeywords, $candKeywords);
                    $kwCount = count($kwMatch);
                    $hasLongMatches = count(array_filter($kwMatch, fn($w) => mb_strlen($w) >= 5)) >= 2;
                    if ($kwCount >= 3 || $hasLongMatches) {
                        $isMatch = true;
                    }
                }

                if ($isMatch) {
                    $clusterArticles[] = $candidate;
                    $assignedItemIds[] = $candidate->id;
                    $candWebName = $candidate->website->name ?? 'Portal';
                    if (!in_array($candWebName, $portalNames)) {
                        $portalNames[] = $candWebName;
                    }
                }
            }

            // Calculate Master Story Cluster Metrics
            $primaryItem = $clusterArticles[0];
            $hoursOld = $primaryItem->created_at ? max(0.2, (float) $primaryItem->created_at->diffInMinutes(now()) / 60.0) : 4.0;
            $portalCount = count($portalNames);
            $articleCount = count($clusterArticles);

            // Freshness Score
            $freshnessScore = 60;
            if ($hoursOld <= 1.0) {
                $freshnessScore = 88;
            } elseif ($hoursOld <= 3.0) {
                $freshnessScore = 78;
            } elseif ($hoursOld <= 6.0) {
                $freshnessScore = 68;
            } elseif ($hoursOld <= 12.0) {
                $freshnessScore = 55;
            } elseif ($hoursOld <= 24.0) {
                $freshnessScore = 44;
            } else {
                $freshnessScore = 32;
            }

            // Multi-Portal Boost
            $multiPortalBoost = min(28, ($portalCount - 1) * 12);

            // High-Impact Keywords
            $keywordScore = 0;
            foreach ($this->highImpactKeywords as $kw => $weight) {
                if (mb_strpos($targetTitle, $kw) !== false) {
                    $keywordScore += $weight;
                }
            }
            $keywordScore = min(20, $keywordScore);

            // Google Trends Live Match Boost
            $isGoogleTrendsMatched = $this->googleTrendsService->matchGoogleTrends($targetTitle, $googleTrends);
            $googleBonus = $isGoogleTrendsMatched ? 10 : 0;

            // Final Viral Velocity Score (35 - 99)
            $rawScore = ($freshnessScore * 0.50) + ($multiPortalBoost * 1.2) + ($keywordScore * 1.3) + $googleBonus;
            $viralScore = (int) round(min(99, max(38, $rawScore)));

            // Sentiment
            $sentimentData = $this->determineSentiment($targetTitle);

            // Velocity Momentum Delta
            if ($portalCount >= 3 && $hoursOld <= 2.0) {
                $velocityGrowth = '🚀 +400% Exploding Spike';
                $velocityGrowthBadge = 'bg-rose-600 text-white';
            } elseif ($portalCount >= 2 && $hoursOld <= 3.0) {
                $velocityGrowth = '⚡ +250% Rapid Surge';
                $velocityGrowthBadge = 'bg-amber-500 text-white';
            } elseif ($hoursOld <= 1.5) {
                $velocityGrowth = '🔥 +150% Breaking Velocity';
                $velocityGrowthBadge = 'bg-indigo-600 text-white';
            } else {
                $velocityGrowth = '📈 Steady Topic Flow';
                $velocityGrowthBadge = 'bg-slate-700 text-white';
            }

            // Deterministic Social Signals
            $isEmotional = in_array($sentimentData['type'], ['outrage', 'curiosity']);
            $fbBuzz = (int) round(min(99, max(45, ($viralScore * 0.82) + ($portalCount * 4) + ($isEmotional ? 8 : 2))));
            $isPoliticsOrBreaking = in_array($sentimentData['type'], ['breaking']) || mb_strpos($targetTitle, 'রাজনীতি') !== false || mb_strpos($targetTitle, 'উপদেষ্টা') !== false;
            $twitterTrend = (int) round(min(98, max(40, ($viralScore * 0.78) + ($isPoliticsOrBreaking ? 10 : 0) + ($hoursOld <= 3.0 ? 6 : 0))));
            $googleSearchSpike = (int) round(min(99, max(45, ($viralScore * 0.80) + ($portalCount * 5) + ($isGoogleTrendsMatched ? 12 : 0) + ($hoursOld <= 2.0 ? 6 : 0))));

            // Viral Lifespan & Level
            if ($viralScore >= 85) {
                $lifespan = '⚡ আগামী ৩ ঘণ্টা পিক (Highest Peak)';
                $level = '🔥 HIGH VIRAL';
                $badgeColor = 'bg-rose-600 text-white';
            } elseif ($viralScore >= 70) {
                $lifespan = '📈 আগামী ৬-১২ ঘণ্টা প্রভাব';
                $level = '⚡ EMERGING TREND';
                $badgeColor = 'bg-amber-500 text-white';
            } else {
                $lifespan = '🕒 আগামী ২৪ ঘণ্টা ধারাবাহিক কভারেজ';
                $level = '📈 MODERATE INTEREST';
                $badgeColor = 'bg-indigo-600 text-white';
            }

            $category = $this->determineCategory($targetTitle);

            // Structure Master Topic Cluster
            $primaryItem->viral_score = $viralScore;
            $primaryItem->viral_level = $level;
            $primaryItem->viral_badge_color = $badgeColor;
            $primaryItem->matching_portals = $portalNames;
            $primaryItem->portal_count = $portalCount;
            $primaryItem->article_count = $articleCount;
            $primaryItem->cluster_articles = $clusterArticles;
            $primaryItem->velocity_growth = $velocityGrowth;
            $primaryItem->velocity_growth_badge = $velocityGrowthBadge;
            $primaryItem->google_trends_matched = $isGoogleTrendsMatched;
            $primaryItem->category = $category['slug'];
            $primaryItem->category_label = $category['label'];
            $primaryItem->category_icon = $category['icon'];
            $primaryItem->fb_buzz = $fbBuzz;
            $primaryItem->twitter_trend = $twitterTrend;
            $primaryItem->google_search_spike = $googleSearchSpike;
            $primaryItem->sentiment = $sentimentData['type'];
            $primaryItem->sentiment_label = $sentimentData['label'];
            $primaryItem->sentiment_badge_color = $sentimentData['badge'];
            $primaryItem->lifespan = $lifespan;

            $clusters[] = $primaryItem;
        }

        return collect($clusters)->sortByDesc('viral_score');
    }

    /**
     * Determine accurate public sentiment from title
     */
    protected function determineSentiment(string $title): array
    {
        if (
            mb_strpos($title, 'নিহত') !== false ||
            mb_strpos($title, 'হামলা') !== false ||
            mb_strpos($title, 'আটক') !== false ||
            mb_strpos($title, 'গ্রেপ্তার') !== false ||
            mb_strpos($title, 'দুর্ঘটনা') !== false ||
            mb_strpos($title, 'মৃত্যু') !== false
        ) {
            return [
                'type'  => 'outrage',
                'label' => '😡 ক্ষোভ & উদ্বেগ (High Shares)',
                'badge' => 'bg-rose-100 text-rose-800 border-rose-200'
            ];
        }

        if (
            mb_strpos($title, 'ফাঁস') !== false ||
            mb_strpos($title, 'ভিডিও') !== false ||
            mb_strpos($title, 'ভাইরাল') !== false ||
            mb_strpos($title, 'অডিও') !== false ||
            mb_strpos($title, 'চাঞ্চল্যকর') !== false ||
            mb_strpos($title, 'তোলপাড়') !== false
        ) {
            return [
                'type'  => 'curiosity',
                'label' => '🔍 উচ্চ কৌতূহল (Viral Click)',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200'
            ];
        }

        if (
            mb_strpos($title, 'ব্রেকিং') !== false ||
            mb_strpos($title, 'জরুরি') !== false ||
            mb_strpos($title, 'পদত্যাগ') !== false ||
            mb_strpos($title, 'স্থগিত') !== false
        ) {
            return [
                'type'  => 'breaking',
                'label' => '🚨 ব্রেকিং প্রভাব (Fast Velocity)',
                'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200'
            ];
        }

        return [
            'type'  => 'trending',
            'label' => '📈 সাধারণ ট্রেন্ড',
            'badge' => 'bg-slate-100 text-slate-800 border-slate-200'
        ];
    }

    /**
     * Category Classification Helper
     */
    protected function determineCategory(string $title): array
    {
        $titleLower = mb_strtolower($title);

        $politicsKw = ['রাজনীতি', 'নির্বাচন', 'প্রধানমন্ত্রী', 'উপদেষ্টা', 'দুদক', 'সংসদ', 'বিএনপি', 'আওয়ামী', 'সরকার', 'মন্ত্রী', 'দল', 'নেতা', 'চিফ প্রসিকিউটর', 'রাষ্ট্রপতি'];
        $crimeKw = ['অপরাধ', 'আইন', 'নিহত', 'হামলা', 'গ্রেপ্তার', 'আটক', 'আদালত', 'ককটেল', 'মাদক', 'মামলা', 'কারাদণ্ড', 'উদ্ধার', 'ডাকাতি', 'জব্দ', 'খুন', 'হত্যা'];
        $sportsKw = ['খেলাধুলা', 'ক্রিকেট', 'শাকিব', 'রেকর্ড', 'ম্যাচ', 'ফুটবল', 'মেসি', 'বিপিএল', 'গোল', 'উইকেট', 'রান', 'টিম', 'টুনামেন্ট', 'সিরিজ'];
        $entertainmentKw = ['বিনোদন', 'শাকিব খান', 'সিনেমার্ট', 'অভিনেত্রী', 'গান', 'মুভি', 'নাটক', 'গায়ক', 'নায়ক', 'নায়িকা', 'তারকা', 'ওটিটি', 'ছবি'];
        $internationalKw = ['আন্তর্জাতিক', 'ট্রাম্প', 'বাইডেন', 'ইউক্রেন', 'রাশিয়া', 'চীন', 'ভারত', 'গাজা', 'ইসরায়েল', 'আমেরিকা', 'ইউরোপ', 'পাকিস্তান', 'ইরান'];

        foreach ($politicsKw as $kw) {
            if (mb_strpos($titleLower, $kw) !== false) return ['slug' => 'politics', 'label' => 'রাজনীতি', 'icon' => 'fa-landmark'];
        }
        foreach ($crimeKw as $kw) {
            if (mb_strpos($titleLower, $kw) !== false) return ['slug' => 'crime', 'label' => 'অপরাধ & আইন', 'icon' => 'fa-scale-balanced'];
        }
        foreach ($sportsKw as $kw) {
            if (mb_strpos($titleLower, $kw) !== false) return ['slug' => 'sports', 'label' => 'খেলাধুলা', 'icon' => 'fa-baseball-bat-ball'];
        }
        foreach ($entertainmentKw as $kw) {
            if (mb_strpos($titleLower, $kw) !== false) return ['slug' => 'entertainment', 'label' => 'বিনোদন', 'icon' => 'fa-film'];
        }
        foreach ($internationalKw as $kw) {
            if (mb_strpos($titleLower, $kw) !== false) return ['slug' => 'international', 'label' => 'আন্তর্জাতিক', 'icon' => 'fa-globe-americas'];
        }

        return ['slug' => 'general', 'label' => 'সাধারণ', 'icon' => 'fa-newspaper'];
    }

    /**
     * Smart Bengali NLP Tokenizer & Stopword Filter
     */
    protected function extractSignificantKeywords(string $text): array
    {
        $clean = preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s]/u', ' ', mb_strtolower($text));
        $words = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);

        $filtered = [];
        foreach ($words as $w) {
            if (mb_strlen($w) >= 3 && !in_array($w, $this->stopWords)) {
                $filtered[] = $w;
            }
        }

        return array_unique($filtered);
    }

    /**
     * Extract 2-word Bengali phrases (Bigrams) for high-accuracy phrase matching
     */
    protected function extractBigrams(string $text): array
    {
        $clean = preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s]/u', ' ', mb_strtolower($text));
        $words = preg_split('/\s+/u', $clean, -1, PREG_SPLIT_NO_EMPTY);

        $bigrams = [];
        $count = count($words);
        for ($i = 0; $i < $count - 1; $i++) {
            $w1 = $words[$i];
            $w2 = $words[$i + 1];
            if (mb_strlen($w1) >= 3 && mb_strlen($w2) >= 3 && !in_array($w1, $this->stopWords) && !in_array($w2, $this->stopWords)) {
                $bigrams[] = $w1 . ' ' . $w2;
            }
        }

        return $bigrams;
    }
}
