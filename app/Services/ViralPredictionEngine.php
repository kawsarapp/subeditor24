<?php

namespace App\Services;

use App\Models\NewsItem;
use App\Models\Website;
use Illuminate\Support\Collection;

class ViralPredictionEngine
{
    /**
     * Known major Bangladeshi News Portals to track & cluster
     */
    protected array $knownPortals = [
        'Prothom Alo'  => ['প্রথম আলো', 'prothomalo', 'prothom alo'],
        'BDNews24'     => ['বিডিনিউজ২৪', 'bdnews24', 'bdnews'],
        'Somoy TV'     => ['সময় টিভি', 'somoynews', 'somoy tv'],
        'Jamuna TV'    => ['যমুনা টিভি', 'jamuna.tv', 'jamunatv'],
        'Daily Star'   => ['ডেইলি স্টার', 'thedailystar'],
        'Jugantor'     => ['যুগান্তর', 'jugantor'],
        'Kalbela'      => ['কালবেলা', 'kalbela'],
        'Ittefaq'      => ['ইত্তেফাক', 'ittefaq'],
        'Ekattor TV'   => ['একাত্তর টিভি', 'ekattor.tv'],
        'Channel i'    => ['চ্যানেল আই', 'channelionline']
    ];

    /**
     * Analyze and rank news items using Social Media Buzz & Multi-Portal Clustering
     */
    public function calculateViralPredictions(Collection $newsItems): Collection
    {
        return $newsItems->map(function ($item) use ($newsItems) {
            $title = $item->title ?? '';
            $hoursOld = $item->created_at ? max(0.5, $item->created_at->diffInHours(now())) : 4;
            
            // 1. Multi-Portal Clustering (Check how many other items cover the same story)
            $matchingPortals = $this->findCrossPortalMatches($item, $newsItems);
            $portalCount = count($matchingPortals);

            // 2. Base Recency Score (Freshness factor)
            $freshnessScore = 60;
            if ($hoursOld <= 1) {
                $freshnessScore += 25;
            } elseif ($hoursOld <= 3) {
                $freshnessScore += 18;
            } elseif ($hoursOld <= 6) {
                $freshnessScore += 8;
            } else {
                $freshnessScore -= 10;
            }

            // 3. Multi-Portal Boost (+10 points for each additional portal covering same story)
            $multiPortalBoost = ($portalCount - 1) * 12;

            // 4. Keyword Engagement Boost
            $highEngagementKeywords = ['ব্রেকিং', 'জরুরি', 'আটক', 'গ্রেপ্তার', 'নিহত', 'হামলা', 'ক্রিকেট', 'শাকিব', 'ফাঁস', 'ভাইরাল', 'ভিডিও', 'সামাজিক', 'হাইকোর্ট', 'নির্বাচন'];
            $keywordScore = 0;
            foreach ($highEngagementKeywords as $kw) {
                if (mb_strpos($title, $kw) !== false) {
                    $keywordScore += 5;
                }
            }

            // Calculate final viral velocity score (1-100)
            $viralScore = min(99, max(52, $freshnessScore + $multiPortalBoost + $keywordScore));

            // 5. Social Media Analytics Simulation & Sentiment Classification
            $fbBuzz = min(99, max(65, $viralScore + rand(-4, 6)));
            $twitterTrend = min(98, max(50, $viralScore + rand(-8, 5)));
            $googleSearchSpike = min(99, max(60, $viralScore + rand(-3, 7)));

            // Public Sentiment Classification
            if (mb_strpos($title, 'নিহত') !== false || mb_strpos($title, 'হামলা') !== false || mb_strpos($title, 'আটক') !== false || mb_strpos($title, 'গ্রেপ্তার') !== false) {
                $sentiment = 'outrage';
                $sentimentLabel = '😡 ক্ষোভ & উদ্বেগ (High Shares)';
                $sentimentBadgeColor = 'bg-rose-100 text-rose-800 border-rose-200';
            } elseif (mb_strpos($title, 'ফাঁস') !== false || mb_strpos($title, 'ভিডিও') !== false || mb_strpos($title, 'ভাইরাল') !== false) {
                $sentiment = 'curiosity';
                $sentimentLabel = '🔍 উচ্চ কৌতূহল (Viral Click)';
                $sentimentBadgeColor = 'bg-amber-100 text-amber-800 border-amber-200';
            } elseif (mb_strpos($title, 'ব্রেকিং') !== false || mb_strpos($title, 'জরুরি') !== false) {
                $sentiment = 'breaking';
                $sentimentLabel = '🚨 ব্রেকিং প্রভাব (Fast Velocity)';
                $sentimentBadgeColor = 'bg-indigo-100 text-indigo-800 border-indigo-200';
            } else {
                $sentiment = 'trending';
                $sentimentLabel = '📈 সাধারণ ট্রেন্ড';
                $sentimentBadgeColor = 'bg-slate-100 text-slate-800 border-slate-200';
            }

            // Viral Lifespan Prediction
            if ($viralScore >= 85) {
                $lifespan = '⚡ আগামী ৩ ঘণ্টা পিক (Highest Peak)';
                $level = '🔥 HIGH VIRAL';
                $badgeColor = 'bg-rose-600 text-white';
            } elseif ($viralScore >= 72) {
                $lifespan = '📈 আগামী ৬-১২ ঘণ্টা প্রভাব';
                $level = '⚡ EMERGING TREND';
                $badgeColor = 'bg-amber-500 text-white';
            } else {
                $lifespan = '🕒 আগামী ২৪ ঘণ্টা স্থায়ী বিবর্তন';
                $level = '📈 MODERATE INTEREST';
                $badgeColor = 'bg-indigo-600 text-white';
            }

            // Category Classification
            $category = $this->determineCategory($title);

            // Attach computed metadata to model
            $item->viral_score = $viralScore;
            $item->viral_level = $level;
            $item->viral_badge_color = $badgeColor;
            $item->matching_portals = $matchingPortals;
            $item->portal_count = $portalCount;
            $item->category = $category['slug'];
            $item->category_label = $category['label'];
            $item->category_icon = $category['icon'];
            $item->fb_buzz = $fbBuzz;
            $item->twitter_trend = $twitterTrend;
            $item->google_search_spike = $googleSearchSpike;
            $item->sentiment = $sentiment;
            $item->sentiment_label = $sentimentLabel;
            $item->sentiment_badge_color = $sentimentBadgeColor;
            $item->lifespan = $lifespan;

            return $item;
        })->sortByDesc('viral_score');
    }

    /**
     * Category Classification Helper
     */
    protected function determineCategory(string $title): array
    {
        $titleLower = mb_strtolower($title);

        $politicsKw = ['রাজনীতি', 'নির্বাচন', 'প্রধানমন্ত্রী', 'উপদেষ্টা', 'দুদক', 'সংসদ', 'বিএনপি', 'আওয়ামী', 'সরকার', 'মন্ত্রী', 'দল', 'নেতা', 'চিফ প্রসিকিউটর'];
        $crimeKw = ['অপরাধ', 'আইন', 'নিহত', 'হামলা', 'গ্রেপ্তার', 'আটক', 'আদালত', 'ককটেল', 'মাদক', 'মামলা', 'কারাদণ্ড', 'উদ্ধার', 'ডাকাতি', 'জব্দ'];
        $sportsKw = ['খেলাধুলা', 'ক্রিকেট', 'শাকিব', 'রেকর্ড', 'ম্যাচ', 'ফুটবল', 'মেসি', 'বিপিএল', 'গোল', 'উইকেট', 'রান', 'টিম', 'টুনামেন্ট'];
        $entertainmentKw = ['বিনোদন', 'শাকিব খান', 'সিনেমার্ট', 'অভিনেত্রী', 'গান', 'মুভি', 'নাটক', 'গায়ক', 'নায়ক', 'নায়িকা', 'তারকা', 'ওটিটি'];
        $internationalKw = ['আন্তর্জাতিক', 'ট্রাম্প', 'বাইডেন', 'ইউক্রেন', 'রাশিয়া', 'চীন', 'ভারত', 'গাজা', 'ইসরায়েল', 'আমেরিকা', 'ইউরোপ', 'পাকিস্তান'];

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
     * Find news items from other portals covering the same story based on keyword overlap
     */
    protected function findCrossPortalMatches($targetItem, Collection $allNewsItems): array
    {
        $targetTitle = mb_strtolower($targetItem->title ?? '');
        $targetWords = array_filter(explode(' ', preg_replace('/[^\x{0980}-\x{09FF}a-zA-Z0-9\s]/u', '', $targetTitle)), function ($w) {
            return mb_strlen($w) >= 3;
        });

        $portalsFound = [];
        $currentWebsiteName = $targetItem->website->name ?? 'Primary Portal';
        $portalsFound[] = $currentWebsiteName;

        foreach ($allNewsItems as $item) {
            if ($item->id === $targetItem->id) continue;

            $itemTitle = mb_strtolower($item->title ?? '');
            $matchingWordCount = 0;
            foreach ($targetWords as $word) {
                if (mb_strpos($itemTitle, $word) !== false) {
                    $matchingWordCount++;
                }
            }

            // If 2+ significant words match across different websites, count as cross-portal coverage
            if ($matchingWordCount >= 2) {
                $webName = $item->website->name ?? 'Partner Portal';
                if (!in_array($webName, $portalsFound)) {
                    $portalsFound[] = $webName;
                }
            }
        }

        return array_unique($portalsFound);
    }
}
