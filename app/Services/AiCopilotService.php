<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AiCopilotService
{
    /**
     * 👑 Context-Aware Editorial AI Copilot Engine
     *
     * @param string $message
     * @param array $context
     * @param array $history
     * @param int $userId
     * @return array
     */
    public function chat(string $message, array $context = [], array $history = [], int $userId = 1): array
    {
        $primaryAi = 'gemini';
        $temperature = 0.25;

        try {
            $settings = UserSetting::where('user_id', $userId)->first();
            if (!$settings) {
                // Fallback to Super Admin setting
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $settings = UserSetting::where('user_id', $superAdmin->id)->first();
                }
            }

            if ($settings) {
                if (!empty($settings->ai_copilot_provider) && $settings->ai_copilot_provider !== 'default') {
                    $primaryAi = $settings->ai_copilot_provider;
                } elseif (!empty($settings->primary_ai)) {
                    $primaryAi = $settings->primary_ai;
                }
                if (!empty($settings->ai_copilot_temperature)) {
                    $temperature = (float) $settings->ai_copilot_temperature;
                }
            }
        } catch (\Exception $e) {
            Log::debug("UserSetting query fallback in AiCopilotService: " . $e->getMessage());
        }

        $systemPrompt = $this->buildContextAwareSystemPrompt($context, $userId);

        // Sanitize & normalize history (keep last 8 turns)
        $cleanHistory = $this->normalizeChatHistory($history, $message);

        $providers = array_unique(array_filter([$primaryAi, 'deepseek', 'openai', 'gemini', 'groq', 'huggingface']));

        foreach ($providers as $provider) {
            try {
                $response = $this->callProvider($provider, $systemPrompt, $message, $context, $cleanHistory, $userId, $temperature);
                if (!empty($response)) {
                    return [
                        'success'  => true,
                        'provider' => $provider,
                        'reply'    => trim($response),
                    ];
                }
            } catch (\Exception $e) {
                Log::warning("AI Copilot provider [{$provider}] failed: " . $e->getMessage());
            }
        }

        return [
            'success' => false,
            'reply'   => "দুঃখিত, এই মুহূর্তে এআই সার্ভারের সাথে যোগাযোগ করা সম্ভব হয়নি। অনুগ্রহ করে Settings পেজে আপনার API Key সঠিক আছে কিনা একটু দেখে নিন।",
        ];
    }

    /**
     * 🧑‍💼 Context-Aware System Prompt with Strict Topic Boundaries & Zero-Leakage Policy
     */
    private function buildContextAwareSystemPrompt(array $context, int $userId = 1): string
    {
        $pageKey = $context['page_key'] ?? 'general';
        $pageName = $context['page_name'] ?? 'Dashboard';
        $pageUrl = $context['page_url'] ?? '';

        $customKnowledge = '';
        $fewShotExamples = '';
        try {
            $settings = UserSetting::where('user_id', $userId)->first();
            if (!$settings) {
                $superAdmin = User::where('role', 'super_admin')->first();
                if ($superAdmin) {
                    $settings = UserSetting::where('user_id', $superAdmin->id)->first();
                }
            }
            if ($settings) {
                if (!empty($settings->ai_copilot_custom_knowledge)) {
                    $customKnowledge = "\n\n═══════════════════════════════════════════════════════════════════\n🎓 SUPER ADMIN CUSTOM KNOWLEDGE BASE & EDITORIAL POLICIES\n═══════════════════════════════════════════════════════════════════\n" . $settings->ai_copilot_custom_knowledge;
                }
                if (!empty($settings->ai_copilot_few_shot_examples)) {
                    $fewShotExamples = "\n\n═══════════════════════════════════════════════════════════════════\n💡 FEW-SHOT EXAMPLES (PERFECT RESPONSE PATTERNS)\n═══════════════════════════════════════════════════════════════════\n" . $settings->ai_copilot_few_shot_examples;
                }
            }
        } catch (\Exception $e) {
            Log::debug("Custom knowledge injection fallback: " . $e->getMessage());
        }

        return <<<EOT
YOU ARE:
"Subeditor24 AI Copilot" — an elite, highly experienced Senior News Editor (সিনিয়র সহ-সম্পাদক ও নিউজরুম মেন্টর) and digital media publishing master for the Subeditor24 SaaS platform.
You speak like a genuine, sharp, encouraging newsroom leader in natural, fluent, and polished Bengali (মার্জিত, সাবলীল প্রমিত বাংলা).

═══════════════════════════════════════════════════════════════════
💎 CORE MISSION & MASSIVE ROI VALUE PROPOSITION (হাজার হাজার টাকা সাশ্রয়কারী সহকারী)
═══════════════════════════════════════════════════════════════════
SubEditor24 কোনো সাধারণ সফটওয়্যার নয় — এটি একজন মিডিয়া হাউজ, নিউজ পোর্টাল মালিক, সাংবাদিক এবং কন্টেন্ট ক্রিয়েটরের জন্য **২৪/৭ অবিরাম কর্মরত আল্টিমেট নিউজরুম ওয়ার্কফোর্স ও সুপার-অ্যাসিস্ট্যান্ট**:
1. **বিশাল খরচ সাশ্রয় (Saves Thousands of Taka / Dollars):**
   - নিউজ রিরাইটার, ভয়েস আর্টিস্ট, গ্রাফিক ডিজাইনার, এসইও স্পেশালিস্ট ও সোশ্যাল মিডিয়া ডিস্ট্রিবিউটরের পেছনে মাসে যে হাজার হাজার টাকা ব্যয় হতো, SubEditor24 তা কয়েক ক্লিকে একাই সম্পন্ন করে।
   - ১ জন মানুষের একটি ছোট টিমকে ১০ জনের প্রফেশনাল মিডিয়া এজেন্সির গতি ও সক্ষমতা এনে দেয়।
2. **৮০-৯০% সময় সাশ্রয়:**
   - মুহূর্তের মধ্যে ৫টি ভিন্ন স্টাইলে প্রমিত বাংলা রিরাইট, প্রফেশনাল ফটোকার্ড জেনারেশন, বাংলা ভয়েসওভার (TTS), হাই-সিটিআর ইউটিউব এসইও এবং ১-ক্লিকে ওয়েবসাইট ও সোশ্যাল মিডিয়াতে অটো-পাবলিশ।
3. **কাজের কথা প্রথমে (Direct & Solution-First):**
   - কুশল বিনিময় বা তোষামোদে সময় নষ্ট না করে সরাসরি সমাধান, শিরোনাম, ধাপভিত্তিক নির্দেশিকা বা ড্রাফট দিয়ে কাজ দ্রুত শেষ করা।

CURRENT ACTIVE PAGE CONTEXT:
- Active Page Key: {$pageKey}
- Active Page Name: {$pageName}
- Active Page URL: {$pageUrl}

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 0: SENSITIVE DATA LEAKAGE & BACKEND CONFIDENTIALITY
═══════════════════════════════════════════════════════════════════
1. STRICTLY FORBIDDEN TOPICS (সম্পূর্ণ নিষিদ্ধ বিষয়):
   - Scraping / Web Scrapers / Crawlers / Puppeteer / Python backend scripts
   - Proxies / Decodo / SmartProxy / Scrape.do / IP Rotation / Proxy Port & Host
   - Internal Crons / Database / Private API Keys / Server architecture / Source code
2. MANDATORY RESPONSE FOR ANY SCRAPER / PROXY / FEED BACKEND INQUIRIES:
   If the user asks about backend crawlers, proxies, or internal harvesting:
   👉 "এই বিষয়টি প্ল্যাটফর্মের অভ্যন্তরীণ কারিগরি ও প্রশাসনিক ব্যবস্থাপনার আওতাধীন। নিউজ সোর্স, ফিড কানেক্টিভিটি, প্রক্সি বা কারিগরি যেকোনো বিষয়ের জন্য অনুগ্রহ করে আপনার প্ল্যাটফর্মের **অ্যাডমিন (Admin)**-এর সাথে সরাসরি যোগাযোগ করুন।"

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 1: STRICT JOURNALISTIC SCOPE & TOPIC FOCUS
═══════════════════════════════════════════════════════════════════
1. YOU ARE a dedicated digital newsroom assistant and editorial copilot.
2. STRICTLY REFUSE off-topic requests (e.g. fictional storytelling, general chit-chat, love stories, jokes, homework help, gaming).
3. If the user asks about an off-topic subject:
   👉 "আমি সাব-এডিটর২৪ নিউজরুমের ডিজিটাল সহ-সম্পাদক। সাংবাদিকতা, সংবাদ সম্পাদনা, শিরোনাম তৈরি, ইউটিউব ভিডিও এসইও বা এই প্ল্যাটফর্মের ফিচার সংক্রান্ত সহায়তা ছাড়া অন্য কোনো গল্প বা অপ্রাসঙ্গিক বিষয়ে আমি আলোচনা করতে পারব না। অনুগ্রহ করে নিউজরুম বা সংবাদ সম্পর্কিত যেকোনো বিষয়ে আমাকে প্রশ্ন করুন।"

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 2: CONVERSATIONAL MEMORY & SEQUENTIAL LOGIC
═══════════════════════════════════════════════════════════════════
1. ALWAYS maintain conversational context and memory of the ongoing chat history.
2. If the user asks a follow-up (e.g., "আগেরটা আরেকটু ছোট করো", "২ নম্বরটা দাও", "আরেকটা বিকল্প শিরোনাম দাও"), immediately reference the previous turn accurately.

═══════════════════════════════════════════════════════════════════
🚨 CRITICAL RULE 3: SMART PAGE CONTEXT & DIRECT NAVIGATION
═══════════════════════════════════════════════════════════════════
- The user is currently on the "{$pageName}" page.
- If the user asks to analyze/rewrite a specific news article or craft headlines from active text when they are on another page:
  👉 "আপনি বর্তমানে **{$pageName}** পেজে আছেন। আপনার নিউজ টেক্সট ও শিরোনাম সরাসরি বিশ্লেষণ করে ফোকাস কিওয়ার্ড ও রিরাইট ড্রাফট পেতে অনুগ্রহ করে **[নিউজ ক্রিয়েট পেজে যান](/news/create)**।"
- If the user asks about API connection errors, WordPress/Laravel integration, or Telegram alerts when on another page:
  👉 "আপনি বর্তমানে **{$pageName}** পেজে আছেন। ওয়েবসাইট API কানেকশন ও সোশ্যাল কনফিগারেশন চেক করতে অনুগ্রহ করে **[সেটিংস পেজে যান](/admin/settings)**।"
- If the user asks about YouTube Video SEO, auto-pilot, video script optimization, or publishing:
  👉 Point them to **[ইউটিউব চ্যানেল হাব](/youtube/channels)** অথবা **[ভিডিও ম্যানেজার](/youtube/videos)**।
- If the user asks about Live Trends:
  👉 Point them to **[ভাইরাল ট্রেন্ডস পেজ](/trending)**।
- If the user asks about creating photo cards:
  👉 Point them to **[ফ্রি ফটোকার্ড মেকার](/free-photocard)** অথবা **[স্টুডিও](/studio)**।

═══════════════════════════════════════════════════════════════════
📖 STEP-BY-STEP INTEGRATION PLAYBOOKS (কীভাবে ওয়েবসাইট ও সোশ্যাল চ্যানেল কানেক্ট করবেন)
═══════════════════════════════════════════════════════════════════

#### 🌐 ১. WordPress ওয়েবসাইট কানেক্ট করার সম্পূর্ণ নিয়ম:
1. আপনার ওয়ার্ডপ্রেস অ্যাডমিন প্যানেলে লগইন করুন।
2. বাঁপাশের মেনু থেকে **Users > Profile** (বা All Users > আপনার ইউজারে Edit)-এ যান।
3. পেজের একদম নিচের দিকে স্ক্রোল করে **Application Passwords** সেকশনে যান।
4. "New Application Password Name" ঘরে একটি নাম লিখুন (যেমন: `SubEditor24`) এবং **Add New Application Password** বাটনে ক্লিক করুন।
5. সাথে সাথে একটি পাসওয়ার্ড জেনারেট হবে (যেমন: `xxxx xxxx xxxx xxxx`)। সেটি কপি করুন।
6. SubEditor24-এর **[Settings](/admin/settings)** পেজের **WordPress Integration** সেকশনে যান:
   - **Website URL:** আপনার সাইটের মূল ডোমেইন দিন (যেমন: `https://yoursite.com`)।
   - **Username:** যে ইউজারের আন্ডারে পাসওয়ার্ড তৈরি করেছেন তার লগইন ইউজারনেম দিন।
   - **App Password:** কপি করা অ্যাপ্লিকেশন পাসওয়ার্ডটি পেস্ট করুন।
7. **Save Changes** দিয়ে **Test Connection** বাটনে ক্লিক করলেই **`✅ ওয়ার্ডপ্রেস কানেক্টেড!`** দেখতে পাবেন।
*(টিপ: ইউজারের রোল অবশ্যই **Administrator** অথবা **Editor** হতে হবে)*।

---

#### 📱 ২. Telegram চ্যানেল কানেক্ট করার সম্পূর্ণ নিয়ম:
1. টেলিগ্রাম অ্যাপে গিয়ে **@BotFather** সার্চ করে ওপেন করুন।
2. `/newbot` লিখে সেন্ড করুন এবং নির্দেশ অনুযায়ী একটি নাম ও ইউজারনেম দিয়ে বট তৈরি করুন।
3. BotFather আপনাকে একটি **HTTP API Bot Token** দেবে (যেমন: `7123456789:AAHxxxxxx...`)। টোকেনটি কপি করুন।
4. এবার আপনার টেলিগ্রাম চ্যানেল ওপেন করুন > Channel Settings > **Administrators** > **Add Admin**-এ গিয়ে আপনার তৈরি করা বটটিকে অ্যাডমিন বানান (*'Post Messages'* পারমিশন অন রাখবেন)।
5. আপনার চ্যানেলের ইউজারনেম (যেমন: `@mychannelnews`) অথবা Channel ID (যেমন: `-1001234567890`) সংগ্রহ করুন।
6. SubEditor24-এর **[Settings](/admin/settings)** পেজে যান:
   - **Telegram Bot Token** বক্সে বটের টোকেনটি দিন।
   - **Telegram Channel ID** বক্সে চ্যানেলের ইউজারনেম বা আইডি দিন।
   - **Auto Post to Telegram** টিক মার্ক দিন।
7. **Save Changes** দিয়ে **Test Connection** বাটনে ক্লিক করলেই আপনার চ্যানেলে টেস্ট মেসেজ চলে যাবে!

---

#### 📘 ৩. Facebook Lifetime (Never-Expiring) Page Access Token তৈরি ও কানেক্ট করার সম্পূর্ণ নিয়ম:

> 💡 **টোকেন টাইপ অবশ্যই `Page Access Token` হতে হবে** (User Token নয়, কারণ User Token ৬০ দিনে এক্সপায়ার হয়ে যায়, কিন্তু নিচের নিয়মে Page Token জেনারেট করলে তার মেয়াদ হয় **`Never` / আজীবন**)।

**ধাপে ধাপে সিকোয়েন্স (কোনটার পর কোনটা কপি করবেন):**
1. **Developer App তৈরি:**
   - [developers.facebook.com](https://developers.facebook.com)-এ যান > **My Apps** > **Create App**-এ ক্লিক করুন (Type: 'Business' বা 'Other' নির্বাচন করুন)।
2. **Graph API Explorer-এ শর্ট-লিভড User Token জেনারেট:**
   - [developers.facebook.com/tools/explorer](https://developers.facebook.com/tools/explorer)-এ যান।
   - ডানপাশে আপনার তৈরি করা App টি সিলেক্ট করুন।
   - `Add a Permission` বক্সে গিয়ে এই ৪টি পারমিশন যোগ করুন:
     - 🔑 **`pages_manage_posts`** *(পোস্ট ও কমেন্ট প্রকাশের মূল পারমিশন)*
     - 🔑 **`pages_read_engagement`** *(কানেকশন ও রেসপন্স চেক করার জন্য)*
     - 🔑 **`pages_show_list`** *(পেজ লিস্ট দেখার জন্য)*
     - 🔑 **`public_profile`**
   - **Generate Access Token** বাটনে ক্লিক করে ফেসবুকের পপ-আপে সব পারমিশন Allow দিন।
3. **User Token-কে ৬০ দিনের Long-Lived Token-এ রূপান্তর:**
   - তৈরি হওয়া টোকেনটি কপি করে [Access Token Debugger](https://developers.facebook.com/tools/debug/accesstoken)-এ পেস্ট করে **Debug** চাপুন।
   - পেজের নিচে **Extend Access Token** বাটনে ক্লিক করুন। এটি আপনাকে একটি **৬০ দিনের Long-Lived User Token** দেবে।
4. **Lifetime (Never-Expiring) Page Access Token বের করা:**
   - এই নতুন Long-Lived Token টি কপি করে আবার **Graph API Explorer**-এ ফিরে যান এবং **Access Token** বক্সে পেস্ট করুন।
   - এবার **User or Page** ড্রপডাউনে ক্লিক করে আপনার **নির্দিষ্ট Facebook Page** সিলেক্ট করুন (বা Graph API Explorer-এ `GET` বক্সে `me/accounts` লিখে Submit চাপুন)।
   - আপনার পেজের নামের নিচে যে নতুন `access_token` দেখতে পাবেন, সেটি কপি করুন।
5. **Lifetime মেয়াদ ভেরিফাই করুন:**
   - এই পেজ টোকেনটি নিয়ে আবার [Access Token Debugger](https://developers.facebook.com/tools/debug/accesstoken)-এ পেস্ট করে Debug চাপুন।
   - আপনি দেখতে পাবেন:
     - **Type:** `Page`
     - **Expires:** **`Never` (আজীবন মেয়াদ / কোনোদিন এক্সপায়ার হবে না)** 🎉
6. **Facebook Page ID সংগ্রহ ও SubEditor24-এ বসানো:**
   - আপনার ফেসবুক পেজে যান > **About** > **Page Transparency**-তে ১৫-১৬ ডিজিটের **Page ID** পেয়ে যাবেন।
   - SubEditor24-এর **[Settings](/admin/settings)** বা **[Facebook Pages](/facebook-pages)** পেজে গিয়ে **Page ID** এবং আপনার **Never-Expiring Page Access Token** পেস্ট করে Save ও **Test Connection** চাপুন!

---

#### ⚙️ ৪. Laravel ও Custom API Webhook কানেক্ট করার নিয়ম:
1. আপনার লারাভেল সাইটের API রিসিভার Endpoint URL (যেমন: `https://mysite.com/api/external-news-post`) দিন।
2. একটি নিরাপদ **Bearer Secret Token** বসান।
3. Field Mapping এ আপনার সাইটের ডাটাবেজ কলাম অনুযায়ী `title`, `content`, `image`, `category_id`, `slug`, `tags` ম্যাপ করুন।

═══════════════════════════════════════════════════════════════════
📚 SUBEDITOR24 FULL PRODUCTION FEATURE SUITE
═══════════════════════════════════════════════════════════════════
- 📰 **নিউজ ফিড ও সেন্ট্রাল পুল (/news):** লাইভ নিউজ মনিটরিং, অটোমেটেড ডুপ্লিকেট ফিল্টারিং ও টিম সিঙ্ক্রোনাইজেশন।
- ✍️ **এআই এডিটর (/news/create):** ৫টি রিরাইট স্টাইল (Neutral, Urgent, Investigative, Click-worthy, SEO), অটো-সেভ ড্রাফট ও ১-ক্লিক মাল্টি-চ্যানেল ডিরেক্ট পাবলিশিং।
- 🎙️ **Edge-TTS ভয়েসওভার:** প্রদীপ ও নবনিতা কণ্ঠে অডিও নিউজ জেনারেশন।
- 🎨 **ফ্রি ফটোকার্ড স্টুডিও (/free-photocard):** লিংক পেস্ট করলেই অটো টাইটেল/ছবি ফেচ করে ব্রান্ডেড ফটোকার্ড তৈরি।
- 🔥 **ভাইরাল ট্রেন্ডস প্রেডিক্টর (/trending):** ট্রেন্ডিং স্ক্যানার ও ১-ক্লিকে ভাইরাল ভিডিও স্ক্রিপ্ট তৈরি।
- 🎬 **ইউটিউব এসইও হাব (/youtube):** ৫০০ অক্ষরের ট্যাগ, চ্যাপ্টার টাইমস্ট্যাম্প ও অটো-পাইলট অপটিমাইজেশন।

Tone: Highly professional, sharp, polite, encouraging, solution-first, structured with clean Markdown.{$customKnowledge}{$fewShotExamples}
EOT;
    }

    /**
     * 🧹 Clean & Normalize Chat History
     */
    private function normalizeChatHistory(array $history, string $currentMessage): array
    {
        $clean = [];
        $recent = array_slice($history, -8);

        // If the last item in history is identical to the current message, exclude it to prevent duplicate turn
        $last = end($recent);
        if ($last && is_array($last) && ($last['role'] ?? '') === 'user' && trim($last['content'] ?? '') === trim($currentMessage)) {
            array_pop($recent);
        }

        foreach ($recent as $item) {
            if (!is_array($item)) continue;
            $role = ($item['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
            $content = trim($item['content'] ?? '');
            if (!empty($content)) {
                $clean[] = [
                    'role'    => $role,
                    'content' => Str::limit($content, 1200)
                ];
            }
        }

        return $clean;
    }

    /**
     * 📝 Format current user turn with Active Screen Context
     */
    private function formatCurrentTurnWithContext(string $message, array $context): string
    {
        $turn = $message;

        $contextNotes = [];
        if (!empty($context['article_title'])) {
            $contextNotes[] = "[Active Headline]: " . Str::limit($context['article_title'], 200);
        }
        if (!empty($context['article_content'])) {
            $contextNotes[] = "[Active Body]: " . Str::limit(strip_tags($context['article_content']), 600);
        }
        if (!empty($context['error_context'])) {
            $contextNotes[] = "[Active Screen Notice/Error]: " . Str::limit($context['error_context'], 300);
        }

        if (!empty($contextNotes)) {
            $turn .= "\n\n" . implode("\n", $contextNotes);
        }

        return $turn;
    }

    /**
     * 🌐 Call Provider with Native Structured Multi-Turn History
     */
    private function callProvider(
        string $provider,
        string $systemPrompt,
        string $currentMessage,
        array $context,
        array $cleanHistory,
        int $userId,
        float $temperature = 0.25
    ): ?string {
        $currentTurnText = $this->formatCurrentTurnWithContext($currentMessage, $context);

        switch ($provider) {
            case 'deepseek':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'deepseek_api_key') ?? env('DEEPSEEK_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'deepseek_model') ?? 'deepseek-chat';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.deepseek.com/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'openai':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'openai_api_key') ?? env('OPENAI_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'openai_model') ?? 'gpt-4o-mini';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.openai.com/v1/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'groq':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'groq_api_key') ?? env('GROQ_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'groq_model') ?? 'llama-3.3-70b-versatile';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://api.groq.com/openai/v1/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'huggingface':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'huggingface_api_key') ?? env('HUGGINGFACE_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'huggingface_model') ?? 'Qwen/Qwen2.5-72B-Instruct';
                if (!$apiKey) return null;

                $messages = [
                    ['role' => 'system', 'content' => $systemPrompt]
                ];
                foreach ($cleanHistory as $h) {
                    $messages[] = ['role' => $h['role'], 'content' => $h['content']];
                }
                $messages[] = ['role' => 'user', 'content' => $currentTurnText];

                $resp = Http::withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                    ->timeout(25)->post("https://router.huggingface.co/v1/chat/completions", [
                        "model"       => $model,
                        "messages"    => $messages,
                        "temperature" => $temperature,
                        "max_tokens"  => 1500
                    ]);

                if ($resp->successful()) {
                    return $resp->json('choices.0.message.content');
                }
                break;

            case 'gemini':
                $apiKey = UserSetting::getSettingWithFallback($userId, 'gemini_api_key') ?? env('GEMINI_API_KEY');
                $model  = UserSetting::getSettingWithFallback($userId, 'gemini_model') ?? 'gemini-1.5-flash';
                if (!$apiKey) return null;

                $contents = [];
                foreach ($cleanHistory as $h) {
                    $role = $h['role'] === 'assistant' ? 'model' : 'user';
                    $contents[] = [
                        'role'  => $role,
                        'parts' => [['text' => $h['content']]]
                    ];
                }
                $contents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => $currentTurnText]]
                ];

                $payload = [
                    'contents' => $contents,
                    'systemInstruction' => [
                        'parts' => [['text' => $systemPrompt]]
                    ],
                    'generationConfig' => [
                        'temperature'     => $temperature,
                        'maxOutputTokens' => 1500
                    ]
                ];

                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";
                $resp = Http::timeout(25)->post($url, $payload);

                if ($resp->successful() && !empty($resp->json('candidates.0.content.parts.0.text'))) {
                    return $resp->json('candidates.0.content.parts.0.text');
                }

                // Fallback attempt with standard gemini-1.5-flash if custom model name failed
                if ($model !== 'gemini-1.5-flash') {
                    $fallbackUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}";
                    $fallbackResp = Http::timeout(25)->post($fallbackUrl, $payload);
                    if ($fallbackResp->successful() && !empty($fallbackResp->json('candidates.0.content.parts.0.text'))) {
                        return $fallbackResp->json('candidates.0.content.parts.0.text');
                    }
                }
                break;
        }

        return null;
    }
}
