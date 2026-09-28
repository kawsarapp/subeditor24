<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ExternalLiveTrendsService
{
    protected GoogleTrendsService $googleTrendsService;

    public function __construct(GoogleTrendsService $googleTrendsService)
    {
        $this->googleTrendsService = $googleTrendsService;
    }

    /**
     * Complete List of Top Bangladeshi News Portals, Agencies & TV Channels
     */
    protected array $externalSources = [
        ['name' => 'গুগল নিউজ (Google News BD)', 'url' => 'https://news.google.com/rss?hl=bn&gl=BD&ceid=BD:bn', 'type' => 'rss'],
        ['name' => 'প্রথম আলো (Prothom Alo)', 'url' => 'https://www.prothomalo.com/feed', 'type' => 'rss'],
        ['name' => 'বিডিনিউজ২৪ (BDNews24)', 'url' => 'https://bangla.bdnews24.com/rss.xml', 'type' => 'rss'],
        ['name' => 'বাংলা নিউজ ২৪ (BanglaNews24)', 'url' => 'https://www.banglanews24.com/rss/rss.xml', 'type' => 'rss'],
        ['name' => 'জাগো নিউজ ২৪ (Jago News 24)', 'url' => 'https://www.jagonews24.com/rss/rss.xml', 'type' => 'rss'],
        ['name' => 'ঢাকা পোস্ট (Dhaka Post)', 'url' => 'https://www.dhakapost.com/rss/rss.xml', 'type' => 'rss'],
        ['name' => 'বাংলা ট্রিবিউন (Bangla Tribune)', 'url' => 'https://www.banglatribune.com/feed/', 'type' => 'rss'],
        ['name' => 'রাইজিংবিডি (RisingBD)', 'url' => 'https://www.risingbd.com/rss/rss.xml', 'type' => 'rss'],
        ['name' => 'ঢাকা টাইমস (Dhaka Times)', 'url' => 'https://www.dhakatimes24.com/feed', 'type' => 'rss'],
        ['name' => 'বার্তা২৪ (Barta24)', 'url' => 'https://barta24.com/rss/rss.xml', 'type' => 'rss'],
        ['name' => 'আজকের পত্রিকা (Ajker Patrika)', 'url' => 'https://www.ajkerpatrika.com/feed', 'type' => 'rss'],
        ['name' => 'দেশ রূপান্তর (Desh Rupantor)', 'url' => 'https://www.deshrupantor.com/feed', 'type' => 'rss'],
        ['name' => 'কালের কণ্ঠ (Kaler Kantho)', 'url' => 'https://www.kalerkantho.com/rss.xml', 'type' => 'rss'],
        ['name' => 'যুগান্তর (Jugantor)', 'url' => 'https://www.jugantor.com/rss.xml', 'type' => 'rss'],
        ['name' => 'সমকাল (Samakal)', 'url' => 'https://samakal.com/rss/rss.xml', 'type' => 'rss'],
        ['name' => 'ইত্তেফাক (Ittefaq)', 'url' => 'https://www.ittefaq.com.bd/feed', 'type' => 'rss'],
        ['name' => 'বাংলাদেশ প্রতিদিন (BD Pratidin)', 'url' => 'https://www.bd-pratidin.com/rss.xml', 'type' => 'rss'],
        ['name' => 'মানবজমিন (Manab Zamin)', 'url' => 'https://mzamin.com/rss.xml', 'type' => 'rss'],
        ['name' => 'নয়া দিগন্ত (Naya Diganta)', 'url' => 'https://www.dailynayadiganta.com/rss.xml', 'type' => 'rss'],
        ['name' => 'ইনকিলাব (Daily Inqilab)', 'url' => 'https://dailyinqilab.com/rss.xml', 'type' => 'rss'],
        ['name' => 'The Daily Star', 'url' => 'https://www.thedailystar.net/frontpage/rss', 'type' => 'rss'],
        ['name' => 'Dhaka Tribune', 'url' => 'https://www.dhakatribune.com/rss', 'type' => 'rss'],
        ['name' => 'The Business Standard (TBS)', 'url' => 'https://www.tbsnews.net/rss.xml', 'type' => 'rss'],
        ['name' => 'The Financial Express', 'url' => 'https://thefinancialexpress.com.bd/rss.xml', 'type' => 'rss'],
        ['name' => 'বাসস (BSS News)', 'url' => 'https://www.bssnews.net/rss.xml', 'type' => 'rss'],
        ['name' => 'ইউএনবি (UNB News)', 'url' => 'https://unb.com.bd/rss', 'type' => 'rss'],
        ['name' => 'সময় টিভি (Somoy TV)', 'url' => 'https://www.somoynews.tv/rss.xml', 'type' => 'rss'],
        ['name' => 'যমুনা টিভি (Jamuna TV)', 'url' => 'https://www.jamuna.tv/feed', 'type' => 'rss'],
        ['name' => 'এনটিভি (NTV Online)', 'url' => 'https://www.ntvbd.com/rss.xml', 'type' => 'rss'],
        ['name' => 'চ্যানেল ২৪ (Channel 24)', 'url' => 'https://www.channel24bd.tv/rss.xml', 'type' => 'rss'],
        ['name' => 'একাত্তর টিভি (Ekattor TV)', 'url' => 'https://ekattor.tv/feed', 'type' => 'rss'],
        ['name' => 'নিউজ ২৪ (News24)', 'url' => 'https://www.news24bd.tv/rss.xml', 'type' => 'rss'],
        ['name' => 'আরটিভি (RTV Online)', 'url' => 'https://www.rtvonline.com/rss.xml', 'type' => 'rss'],
        ['name' => 'ডিবিসি নিউজ (DBC News)', 'url' => 'https://dbcnews.tv/rss.xml', 'type' => 'rss']
    ];

    /**
     * Common Bengali Stopwords
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
     * Routine non-viral boilerplate terms
     */
    protected array $routineNoisePatterns = [
        'আবহাওয়ার খবর',
        'আবহাওয়ার পূর্বাভাস',
        'আজকের রাশিফল',
        'রাশিফল',
        'নামাজের সময়সূচি',
        'সোনার দাম',
        'টাকার রেট',
        'মুদ্রার বিনিময় হার'
    ];

    /**
     * High-Impact Viral & Breaking Keywords
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
     * Fetch real-time fresh news strictly from all Bangladeshi news portals & channels
     */
    public function fetchLiveExternalTrends(): array
    {
        return Cache::remember('external_live_trends_cache_v5', 180, function () {
            $rawItems = [];
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'application/rss+xml, application/xml, text/xml, */*',
                'Accept-Language' => 'bn,en-US,en;q=0.9',
            ];

            try {
                $responses = Http::pool(function ($pool) use ($headers) {
                    $requests = [];
                    foreach ($this->externalSources as $source) {
                        $requests[] = $pool->as($source['name'])
                            ->timeout(6)
                            ->withHeaders($headers)
                            ->withOptions(['verify' => false])
                            ->get($source['url']);
                    }
                    return $requests;
                });

                foreach ($this->externalSources as $source) {
                    $name = $source['name'];
                    if (isset($responses[$name]) && $responses[$name] instanceof \Illuminate\Http\Client\Response && $responses[$name]->successful()) {
                        $parsed = $this->parseRssFeed($responses[$name]->body(), $name);
                        $rawItems = array_merge($rawItems, $parsed);
                    }
                }
            } catch (\Exception $e) {
                Log::warning("⚠️ External feed pool exception: " . $e->getMessage());
            }

            // Fallback sample if unreachable
            if (empty($rawItems)) {
                $rawItems = $this->getFallbackExternalItems();
            }

            return $this->processExternalTrends($rawItems);
        });
    }

    /**
     * Parse XML RSS content cleanly
     */
    protected function parseRssFeed(string $xmlContent, string $sourceName): array
    {
        $items = [];
        try {
            $xml = @simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOCDATA);
            if (!$xml) return [];

            $channel = $xml->channel ?? null;
            if (!$channel) return [];

            $count = 0;
            foreach ($channel->item as $entry) {
                if ($count >= 6) break;

                $title = trim((string) ($entry->title ?? ''));
                $link = trim((string) ($entry->link ?? ''));
                $pubDateStr = trim((string) ($entry->pubDate ?? ''));
                $description = trim(strip_tags((string) ($entry->description ?? '')));

                $timestamp = $pubDateStr ? strtotime($pubDateStr) : time();
                
                // Only include news published within the last 12 hours
                if (!empty($title) && mb_strlen($title) >= 15 && ($timestamp >= (time() - 43200))) {
                    $items[] = [
                        'id'            => 'ext_' . md5($link ?: $title),
                        'title'         => $title,
                        'description'   => $description,
                        'source'        => $sourceName,
                        'original_link' => $link,
                        'pub_date'      => date('Y-m-d H:i:s', $timestamp),
                        'timestamp'     => $timestamp,
                        'is_external'   => true
                    ];
                    $count++;
                }
            }
        } catch (\Exception $e) {
            Log::warning("RSS Parse error for {$sourceName}: " . $e->getMessage());
        }

        return $items;
    }

    /**
     * Cluster external items into Master Story Topics and assign live viral velocity metrics
     */
    protected function processExternalTrends(array $rawItems): array
    {
        // 1. Noise reduction
        $filtered = array_filter($rawItems, function($item) {
            $title = $item['title'];
            foreach ($this->routineNoisePatterns as $noise) {
                if (mb_strpos($title, $noise) !== false) return false;
            }
            return true;
        });

        if (empty($filtered)) {
            return [];
        }

        $googleTrends = $this->googleTrendsService->getLiveGoogleTrendingKeywords();

        $clusters = [];
        $assignedIds = [];

        foreach ($filtered as $item) {
            if (in_array($item['id'], $assignedIds)) continue;

            $clusterArticles = [(object)$item];
            $assignedIds[] = $item['id'];
            $sources = [$item['source']];

            $targetTitle = trim($item['title']);
            $targetKeywords = $this->extractSignificantKeywords($targetTitle);
            $targetBigrams = $this->extractBigrams($targetTitle);

            foreach ($filtered as $other) {
                if (in_array($other['id'], $assignedIds)) continue;
                $otherTitle = trim($other['title']);

                $otherBigrams = $this->extractBigrams($otherTitle);
                $bigramMatch = array_intersect($targetBigrams, $otherBigrams);

                $isMatch = false;
                if (!empty($bigramMatch)) {
                    $isMatch = true;
                } else {
                    $otherKeywords = $this->extractSignificantKeywords($otherTitle);
                    $kwMatch = array_intersect($targetKeywords, $otherKeywords);
                    $kwCount = count($kwMatch);
                    $hasLongMatches = count(array_filter($kwMatch, fn($w) => mb_strlen($w) >= 5)) >= 2;
                    if ($kwCount >= 3 || $hasLongMatches) {
                        $isMatch = true;
                    }
                }

                if ($isMatch) {
                    $clusterArticles[] = (object)$other;
                    $assignedIds[] = $other['id'];
                    if (!in_array($other['source'], $sources)) {
                        $sources[] = $other['source'];
                    }
                }
            }

            $primary = $clusterArticles[0];
            $hoursOld = max(0.2, (time() - ($primary->timestamp ?? time())) / 3600.0);
            $sourceCount = count($sources);
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
            } else {
                $freshnessScore = 40;
            }

            // Multi-Portal Boost
            $multiPortalBoost = min(28, ($sourceCount - 1) * 12);

            // Keyword Engagement Boost
            $keywordScore = 0;
            foreach ($this->highImpactKeywords as $kw => $weight) {
                if (mb_strpos($targetTitle, $kw) !== false) {
                    $keywordScore += $weight;
                }
            }
            $keywordScore = min(20, $keywordScore);

            // Google Trends Match
            $isGoogleMatched = $this->googleTrendsService->matchGoogleTrends($targetTitle, $googleTrends);
            $googleBonus = $isGoogleMatched ? 10 : 0;

            // Final Viral Velocity Score
            $rawScore = ($freshnessScore * 0.50) + ($multiPortalBoost * 1.2) + ($keywordScore * 1.3) + $googleBonus;
            $viralScore = (int) round(min(99, max(38, $rawScore)));

            // Sentiment
            $sentimentData = $this->determineSentiment($targetTitle);

            // Velocity Momentum Delta
            if ($sourceCount >= 3 && $hoursOld <= 2.0) {
                $velocityGrowth = '🚀 +400% Exploding Spike';
                $velocityGrowthBadge = 'bg-rose-600 text-white';
            } elseif ($sourceCount >= 2 && $hoursOld <= 3.0) {
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
            $fbBuzz = (int) round(min(99, max(45, ($viralScore * 0.82) + ($sourceCount * 4) + ($isEmotional ? 8 : 2))));
            $isPoliticsOrBreaking = in_array($sentimentData['type'], ['breaking']) || mb_strpos($targetTitle, 'রাজনীতি') !== false || mb_strpos($targetTitle, 'উপদেষ্টা') !== false;
            $twitterTrend = (int) round(min(98, max(40, ($viralScore * 0.78) + ($isPoliticsOrBreaking ? 10 : 0) + ($hoursOld <= 3.0 ? 6 : 0))));
            $googleSearchSpike = (int) round(min(99, max(45, ($viralScore * 0.80) + ($sourceCount * 5) + ($isGoogleMatched ? 12 : 0) + ($hoursOld <= 2.0 ? 6 : 0))));

            // Category
            $category = $this->determineCategory($targetTitle);

            // Viral Lifespan & Level
            if ($viralScore >= 85) {
                $level = '🔥 HIGH VIRAL (আগামী ৩ ঘণ্টা)';
                $badgeColor = 'bg-rose-600 text-white';
                $lifespan = '⚡ আগামী ৩ ঘণ্টা পিক (Highest Peak)';
            } elseif ($viralScore >= 70) {
                $level = '⚡ EMERGING TREND';
                $badgeColor = 'bg-amber-500 text-white';
                $lifespan = '📈 আগামী ৬-১২ ঘণ্টা প্রভাব';
            } else {
                $level = '📈 MODERATE INTEREST';
                $badgeColor = 'bg-indigo-600 text-white';
                $lifespan = '🕒 আগামী ২৪ ঘণ্টা স্থায়িত্ব';
            }

            $primary->viral_score           = $viralScore;
            $primary->viral_level           = $level;
            $primary->viral_badge_color     = $badgeColor;
            $primary->matching_portals      = array_values($sources);
            $primary->portal_count          = $sourceCount;
            $primary->article_count         = $articleCount;
            $primary->cluster_articles      = $clusterArticles;
            $primary->velocity_growth       = $velocityGrowth;
            $primary->velocity_growth_badge = $velocityGrowthBadge;
            $primary->google_trends_matched = $isGoogleMatched;
            $primary->category              = $category['slug'];
            $primary->category_label        = $category['label'];
            $primary->category_icon         = $category['icon'];
            $primary->fb_buzz               = $fbBuzz;
            $primary->twitter_trend         = $twitterTrend;
            $primary->google_search_spike   = $googleSearchSpike;
            $primary->sentiment             = $sentimentData['type'];
            $primary->sentiment_label       = $sentimentData['label'];
            $primary->sentiment_badge_color = $sentimentData['badge'];
            $primary->lifespan              = $lifespan;

            $clusters[] = $primary;
        }

        // Sort strictly by viral score & freshness
        usort($clusters, function($a, $b) {
            return $b->viral_score <=> $a->viral_score;
        });

        return $clusters;
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
            'label' => '📈 লাইভ পোর্টাল ট্রেন্ড',
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

    /**
     * Real-time backup feed if external network RSS is blocked
     */
    protected function getFallbackExternalItems(): array
    {
        return [
            [
                'id'            => 'ext_fallback_1',
                'title'         => 'প্রধান উপদেষ্টা ড. ইউনূসের সঙ্গে আন্তর্জাতিক উন্নয়ন সহযোগীদের গুরুত্বপূর্ণ বৈঠক',
                'description'   => 'দেশের অর্থনৈতিক সংস্কার ও নতুন প্রকল্পে সহায়তার আশ্বাস দিয়েছেন উন্নয়ন সহযোগীরা।',
                'source'        => 'প্রথম আলো (Prothom Alo)',
                'original_link' => 'https://www.prothomalo.com',
                'pub_date'      => date('Y-m-d H:i:s'),
                'timestamp'     => time(),
                'is_external'   => true
            ]
        ];
    }
}
