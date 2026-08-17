<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Models\User; // 🔥 User মডেল ইমপোর্ট করা হলো
use App\Jobs\ScrapeWebsite; // ✅ Job ক্লাস ইমপোর্ট
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebsiteController extends Controller
{
    // 🔥 হেল্পার ফাংশন: স্টাফ বা রিপোর্টার হলে তার অ্যাডমিনকে বের করবে
    private function getEffectiveAdmin() {
        $user = Auth::user();
        return in_array($user->role, ['staff', 'reporter']) ? User::find($user->parent_id) : $user;
    }

    // ==========================================
    // ১. ওয়েবসাইট লিস্ট দেখা (Role ভিত্তিক)
    // ==========================================
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            // সুপার অ্যাডমিন সব ওয়েবসাইট দেখবে
            $websites = Website::withoutGlobalScopes()->get();
        } elseif (in_array($user->role, ['user', 'admin'])) {
            // অ্যাডমিন (Client) তার নিজের তৈরি করা এবং সুপার অ্যাডমিনের দেওয়া সাইটগুলো দেখবে
            $websites = Website::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->orWhereHas('users', function($q) use ($user) {
                    $q->where('users.id', $user->id); // 🔥 Data ambiguity এড়াতে users.id দেওয়া হলো
                })->get();
        } else {
            // Staff বা Reporter: অ্যাডমিন তাদেরকে যে সোর্সগুলো পারমিশন দিয়েছে, শুধু সেগুলো দেখবে
            $websites = $user->accessibleWebsites()
                        ->withoutGlobalScopes()
                        ->get();
        }
        
        return view('websites.index', compact('websites'));
    }

    // ==========================================
    // ২. ওয়েবসাইট যোগ করা (শুধু সুপার অ্যাডমিন)
    // ==========================================
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'অনুমতি নেই।');
        }

        $request->validate([
            'name' => 'required',
            'url' => 'required|url',
            'selector_container' => 'nullable',
            'selector_title' => 'nullable',
            'target_language' => 'nullable|in:bn,en',
        ]);

        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['selector_container'] = $request->input('selector_container') ?: '.desktopSectionLead, .news-item, .post-item, article, .story-element, .news-card';
        $data['selector_title'] = $request->input('selector_title') ?: 'h1, h2, h3, .title, .heading';
        $data['use_scraping_api'] = $request->has('use_scraping_api') ? 1 : 0;

        Website::create($data);

        return back()->with('success', 'Website added successfully!');
    }

    // ==========================================
    // ৩. ওয়েবসাইট স্ক্র্যাপ করা (Observe)
    // ==========================================
    public function scrape($id)
    {
        $user = Auth::user();
        $adminUser = $this->getEffectiveAdmin(); // 🔥 স্টাফের অ্যাডমিনকে কল করা হলো

        // ডাইনামিক লিমিট ও কুলডাউন নির্ধারণ (সুপার এডমিন সেটিংস থেকে অথবা ডিফ্লট ৫ মিনিট ও ৩টি সাইট)
        $cooldownMinutes = (int) \App\Models\UserSetting::getSettingWithFallback($adminUser->id, 'scrape_cooldown_minutes') ?: 5;
        $concurrentLimit = (int) \App\Models\UserSetting::getSettingWithFallback($adminUser->id, 'scrape_concurrent_limit') ?: 3;

        // রেট লিমিট চেক: এক ইউজার নির্ধারিত কুলডাউন সময়ের মধ্যে সর্বোচ্চ $concurrentLimit টি সাইট স্ক্র্যাপ করতে পারবে
        $rateLimitKey = 'scrape_limit_' . $user->id;
        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, $concurrentLimit)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($rateLimitKey);
            $minutes = floor($seconds / 60);
            $remainingSeconds = $seconds % 60;
            $waitTime = $minutes > 0 ? "{$minutes} মিনিট {$remainingSeconds} সেকেন্ড" : "{$seconds} সেকেন্ড";
            return back()->with('error', "আপনি একই সাথে সর্বোচ্চ {$concurrentLimit}টি সাইট স্ক্র্যাপ করতে পারবেন। অনুগ্রহ করে {$waitTime} অপেক্ষা করুন।");
        }

        // ১. ওয়েবসাইট ভ্যালিডেশন / এক্সেস চেক (Role অনুযায়ী)
        if ($user->role === 'super_admin') {
            $website = Website::withoutGlobalScopes()->findOrFail($id);
        } elseif (in_array($user->role, ['user', 'admin'])) {
            $website = Website::withoutGlobalScopes()
                ->where(function($query) use ($user) {
                    $query->where('user_id', $user->id)
                          ->orWhereHas('users', function($q) use ($user) {
                              $q->where('users.id', $user->id); // 🔥 Data ambiguity এড়াতে users.id দেওয়া হলো
                          });
                })->findOrFail($id);
        } else {
            // স্টাফের যদি এই সাইট স্ক্র্যাপ করার অনুমতি থাকে, তবেই সে পারবে
            $website = $user->accessibleWebsites()
                ->withoutGlobalScopes()
                ->where('websites.id', $id)
                ->firstOrFail();
        }

        // ২. 🔥 ডাইনামিক মিনিটের চেকিং লজিক (Cool-down Check for the specific website, PER USER)
        $userScrapeKey = 'scrape_time_user_' . $adminUser->id . '_website_' . $website->id;
        $lastScraped = \Illuminate\Support\Facades\Cache::get($userScrapeKey);

        if ($lastScraped) {
            $diffInSeconds = now()->diffInSeconds($lastScraped);
            $cooldownSeconds = $cooldownMinutes * 60; 

            if ($diffInSeconds < $cooldownSeconds) {
                $wait = $cooldownSeconds - $diffInSeconds;
                $minutes = floor($wait / 60);
                $seconds = $wait % 60;
                return back()->with('error', "এই সাইটটি সম্প্রতি স্ক্র্যাপ করা হয়েছে। অনুগ্রহ করে {$minutes} মিনিট {$seconds} সেকেন্ড পর আবার চেষ্টা করুন।");
            }
        }

        // রেকর্ড দ্য হিট ($cooldownMinutes * 60 সেকেন্ডের জন্য)
        \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, $cooldownMinutes * 60);

        // ৩. টাইমস্ট্যাম্প আপডেট করা (User Specific)
        \Illuminate\Support\Facades\Cache::put($userScrapeKey, now(), now()->addMinutes($cooldownMinutes));
        
        // Optionally update global timestamp for super admin tracking (optional, kept for record)
        $website->update(['last_scraped_at' => now()]);

        // ৪. জব ডিসপ্যাচ (🔥 এখানে $adminUser->id দেওয়া হলো, যাতে প্রক্সি এবং লিমিট অ্যাডমিনের প্রোফাইল থেকে নেয়)
        ScrapeWebsite::dispatch($website->id, $adminUser->id);
        
        return redirect()->route('news.index', ['scraping' => 'started'])
            ->with('success', '⏳ স্ক্র্যাপিং শুরু হয়েছে! অনুগ্রহ করে অপেক্ষা করুন...');
    }

    // ==========================================
    // ৪. ওয়েবসাইট আপডেট করা
    // ==========================================
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'Permission Denied');
        }
        
        $website = Website::withoutGlobalScopes()->findOrFail($id);
        
        $data = $request->validate([
            'name' => 'required',
            'url' => 'required|url',
            'selector_container' => 'required',
            'selector_title' => 'required',
            'target_language' => 'nullable|in:bn,en',
        ]);
        
        $data = array_merge($request->all(), $data);
        $data['use_scraping_api'] = $request->has('use_scraping_api') ? 1 : 0;
        
        $website->update($data);
        
        return back()->with('success', 'Website Updated');
    }

    // ==========================================
    // ৫. ⚡ RSS, Sitemap & Selector Auto-Discovery
    // ==========================================
    public function discoverSelectors(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $url = $request->input('url');

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
                ])->get($url);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'সাইট থেকে রেসপন্স পাওয়া যায়নি (Status: ' . $response->status() . ')']);
            }

            $html = $response->body();
            $crawler = new \Symfony\Component\DomCrawler\Crawler($html);

            // 📡 1. RSS Feed Discovery
            $rssUrl = null;
            $crawler->filter('link[type*="rss"], link[type*="atom"], link[type*="xml"]')->each(function ($node) use (&$rssUrl, $url) {
                if (!$rssUrl && $node->attr('href')) {
                    $href = $node->attr('href');
                    $rssUrl = str_starts_with($href, 'http') ? $href : rtrim($url, '/') . '/' . ltrim($href, '/');
                }
            });

            // 🗺️ 2. Sitemap Discovery
            $sitemapUrl = null;
            $parsedUrl = parse_url($url);
            $baseUrl = ($parsedUrl['scheme'] ?? 'https') . '://' . ($parsedUrl['host'] ?? '');
            $commonSitemaps = [$baseUrl . '/sitemap.xml', $baseUrl . '/sitemap-news.xml', $baseUrl . '/news-sitemap.xml'];
            
            foreach ($commonSitemaps as $sm) {
                try {
                    $smRes = \Illuminate\Support\Facades\Http::timeout(4)->get($sm);
                    if ($smRes->successful() && (str_contains($smRes->body(), '<urlset') || str_contains($smRes->body(), '<sitemapindex'))) {
                        $sitemapUrl = $sm;
                        break;
                    }
                } catch (\Exception $e) {}
            }

            // 🎯 3. Auto-Detect Container & Heading Selectors
            $suggestedContainer = '.desktopSectionLead, .news-item, .post-item, article, .story-element, .news-card';
            $suggestedTitle = 'h1, h2, h3, .title, .heading';
            $suggestedContent = '.dt-news-details, .news-details, .details-content, .article-body, .post-content, .entry-content, .story-content';
            $suggestedImage = 'img';

            // Find best matching container in HTML
            $foundContainer = null;
            $candidates = ['.desktopSectionLead', '.lead-news', '.news-item', '.post-item', 'article', '.story-element', '.news-card', '.lead', '.news_box', '.col-md-4', '.card'];
            foreach ($candidates as $candidate) {
                if ($crawler->filter($candidate)->count() > 0) {
                    $foundContainer = $candidate;
                    break;
                }
            }

            // Find best matching body content selector in HTML
            $foundContent = null;
            $contentCandidates = ['.dt-news-details', '.news-details', '.details-content', '.article-body', '.post-content', '.entry-content', '.story-content', '.description'];
            foreach ($contentCandidates as $cCandidate) {
                if ($crawler->filter($cCandidate)->count() > 0) {
                    $foundContent = $cCandidate;
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'rss_feed' => $rssUrl,
                'sitemap' => $sitemapUrl,
                'container' => $foundContainer ?? $suggestedContainer,
                'title' => $suggestedTitle,
                'content' => $foundContent ?? $suggestedContent,
                'image' => $suggestedImage,
                'message' => 'অটো-ডিটেকশন সফল হয়েছে!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'এরর: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // ৬. 🗑️ ওয়েবসাইট মুছে ফেলা (Safety Confirmation Required)
    // ==========================================
    public function destroy(Request $request, $id)
    {
        if (Auth::user()->role !== 'super_admin') {
            return back()->with('error', 'অনুমতি নেই।');
        }

        $confirmText = strtoupper(trim($request->input('confirm_text', '')));
        if ($confirmText !== 'DELETE') {
            return back()->with('error', 'ডিলিট সম্পন্ন করতে কনফার্মেশন বক্সে "DELETE" শব্দটি সঠিকভাবে টাইপ করুন।');
        }

        $website = Website::withoutGlobalScopes()->findOrFail($id);
        $websiteName = $website->name;
        $website->delete();

        return back()->with('success', "নিউজ সোর্স '{$websiteName}' সফলভাবে মুছে ফেলা হয়েছে।");
    }
}