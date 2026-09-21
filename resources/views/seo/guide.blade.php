@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- HEADER & NAVIGATION BACK --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-black uppercase tracking-wider mb-2 border border-indigo-200">
                📖 Comprehensive SEO & Intelligence Master Guide
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                🔍 SEO & Website Intelligence — Comprehensive User & Operations Manual
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 font-medium mt-1">
                A complete operational guide explaining module functions, issue identification, root cause analysis, and remediation workflows.
            </p>
        </div>

        <a href="{{ route('seo.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-5 py-2.5 rounded-2xl text-xs flex items-center gap-2 shadow-md transition shrink-0">
            <i class="fa-solid fa-arrow-left"></i> Return to SEO Dashboard
        </a>
    </div>

    {{-- INTRO NOTE --}}
    <div class="p-6 bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-3xl shadow-lg mb-8 border border-indigo-700/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center text-2xl shrink-0 border border-indigo-400/30">
                💡
            </div>
            <div>
                <h2 class="font-extrabold text-base sm:text-lg text-white mb-1">Welcome! Core Guidelines for Using the SEO Dashboard:</h2>
                <p class="text-xs sm:text-sm text-slate-300 font-medium leading-relaxed">
                    A high-performance newsroom requires synchronization between editorial publishing, Google Search rankings, Google Discover distribution, and server latency. The 15 operational sections below detail every issue and recommended fix.
                </p>
            </div>
        </div>
    </div>

    {{-- DETAILED 15 SECTIONS --}}
    <div class="space-y-8">

        {{-- 1. ORPHAN NEWS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 border border-amber-200">
                    1
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">⚠️ Orphan News (Articles Lacking Internal Links)</h3>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200">Internal Linking Architecture</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Detects published news articles that receive zero incoming internal links from other stories or sections on the portal.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">When journalists publish without interlinking related past articles, search crawlers struggle to discover the pages once off the homepage.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Use AI Internal Link Suggestions in the editor or click <strong>`✅ Approve & Apply`</strong> to inject relevant contextual links into related stories.</p>
                </div>
            </div>
        </div>

        {{-- 2. BROKEN LINKS & 404 --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-800 flex items-center justify-center text-lg font-black shrink-0 border border-rose-200">
                    2
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">💔 Broken Links & 404 Pages</h3>
                    <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-md border border-rose-200">404 Error & Crawl Budget</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Scans and lists internal and external links on your portal that return 404 HTTP Not Found errors.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Modified slugs, deleted articles, or typos create broken links, degrading user retention and wasting Googlebot crawl budget.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Create 301 Permanent Redirects for modified URLs or edit the source news content to repair broken hyperlinks.</p>
                </div>
            </div>
        </div>

        {{-- 3. INSTANT INDEXING --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-violet-100 text-violet-800 flex items-center justify-center text-lg font-black shrink-0 border border-violet-200">
                    3
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">⚡ Instant Indexing (Google & Bing 15-Second Multi-Engine Push)</h3>
                    <span class="text-xs font-bold text-violet-700 bg-violet-50 px-2.5 py-0.5 rounded-md border border-violet-200">Real-Time Multi-Engine IndexNow Push</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700 mb-4">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Dispatches real-time ping notifications directly to Google Indexing API and 17 search engines via IndexNow immediately upon publication.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Standard crawler discovery can take 2-24 hours, missing viral breaking news search spikes and organic reader traffic.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Click **`Push Instant Indexing (15s)`** or enable automatic publish push triggers to notify crawlers within 15-20 seconds.</p>
                </div>
            </div>

            {{-- 17 SEARCH ENGINES LIST BOX --}}
            <div class="p-4 bg-violet-50/80 rounded-2xl border border-violet-200">
                <h4 class="font-extrabold text-violet-900 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    🌐 17 Major Search Engines Receiving Instant Real-Time Indexing Signals:
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 text-[11px] font-bold text-slate-700">
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🟢 Microsoft Bing (Global)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🔴 Yandex (Europe & CIS)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🦆 DuckDuckGo (Private)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇰🇷 Naver (South Korea)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇨🇿 Seznam.cz (Czech)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🟣 Yahoo! Search (Global)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🛡️ Brave Search (Private)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🌿 Ecosia (Green Search)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">❓ Ask.com (Knowledge & Q&A)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">⚡ Yep.com (Ahrefs Engine)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🔍 Qwant (European Search)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇨🇳 Sogou (Asian Partner)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇨🇭 Swisscows (Switzerland)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🎒 Mojeek (UK Search)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">📱 Startpage (Europe)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">💡 Search.com (Information)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">📰 Google Discover Feed</div>
                </div>
            </div>
        </div>

        {{-- 4. GOOGLE DISCOVER OPTIMIZER --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-sky-100 text-sky-800 flex items-center justify-center text-lg font-black shrink-0 border border-sky-200">
                    4
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">📰 Google Discover Optimizer Engine</h3>
                    <span class="text-xs font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-md border border-sky-200">Viral Traffic Engine</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Tests and qualifies articles for high-traffic distribution across mobile Google Discover feeds.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Images under 1200px width or missing `max-image-preview:large` meta tags will automatically disqualify articles from Discover feeds.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Always provide high-res 1200px+ featured images and choose engaging, high-CTR headlines suggested by AI.</p>
                </div>
            </div>
        </div>

        {{-- 5. SOCIAL MEDIA TRAFFIC --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    5
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">📱 Social Media Referral Traffic Analytics</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">Multi-Channel Analytics</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Tracks incoming visitor volumes across Facebook, X (Twitter), Instagram, YouTube, WhatsApp, and Telegram.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Sharing links without custom UTM parameters collapses incoming clicks into generic Direct/Unknown traffic in analytics.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Use the Smart UTM Generator to append channel tracking parameters before distributing to social pages and channels.</p>
                </div>
            </div>
        </div>

        {{-- 6. TECHNICAL AUDIT --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-teal-100 text-teal-800 flex items-center justify-center text-lg font-black shrink-0 border border-teal-200">
                    6
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🔍 Technical SEO Audit & On-Page Diagnostics</h3>
                    <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-md border border-teal-200">Site Health Check</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Scans all portal pages for missing titles, short meta descriptions, duplicate H1 tags, and thin content issues.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Submitting short (<70 chars) or missing meta descriptions produces truncated snippets in Google Search results.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Open flagged articles in the audit list and provide descriptive 120-160 char meta descriptions and single primary H1 headlines.</p>
                </div>
            </div>
        </div>

        {{-- 7. GSC QUICK WINS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 border border-amber-200">
                    7
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🎯 GSC Quick Wins (Google Positions 4-15)</h3>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200">Low-Hanging Ranking Fruit</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Surfaces high-potential queries currently ranked positions 4 to 15 that can easily jump into Top 3 rankings.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Lack of keyword prominence in lead paragraphs and missing H2 subheadings keep articles below position 3.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Incorporate target keywords naturally within the first 100 words and add a supporting H2 subheading.</p>
                </div>
            </div>
        </div>

        {{-- 8. GA4 TRAFFIC & DECAY --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-800 flex items-center justify-center text-lg font-black shrink-0 border border-rose-200">
                    8
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">📉 GA4 Organic Traffic & Content Decay Alerts</h3>
                    <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-md border border-rose-200">Traffic Retention Alert</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Identifies historical evergreen and viral posts whose organic search traffic has decayed by >30% over the last 30 days.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Outdated information causes readers to bounce, signaling search engines to demote rankings in favor of fresh coverage.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Follow AI Refresh Suggestions to update the article with recent developments and refresh the timestamp.</p>
                </div>
            </div>
        </div>

        {{-- 9. CORE WEB VITALS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 border border-amber-200">
                    9
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">⚡ Core Web Vitals (Page Speed & User Experience)</h3>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200">Page Experience Metric</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Tracks real user metrics for Largest Contentful Paint (LCP), Server Latency (TTFB), and Cumulative Layout Shift (CLS).</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Heavy uncompressed images and blocking JavaScript scripts causing >2.5s render times trigger search ranking penalties.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Compress media assets to WebP and maintain Redis / FastCGI server caching to keep TTFB below 200ms.</p>
                </div>
            </div>
        </div>

        {{-- 10. SCHEMA VALIDATOR --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-lg font-black shrink-0 border border-purple-200">
                    10
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🏷️ Schema & Structured Data (NewsArticle Rich Snippets)</h3>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-md border border-purple-200">Structured Data JSON-LD</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Validates that JSON-LD structured data is present so search bots parse headline, author, date, and logo accurately.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Missing or malformed `NewsArticle` schemas prevent rich media thumbnails from appearing in Google News and search snippets.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Verify schema health with the Schema Validator to ensure valid `@type: NewsArticle` JSON-LD is delivered on all articles.</p>
                </div>
            </div>
        </div>

        {{-- 11. COMPETITOR COMPARE --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    11
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🆚 Competitor Ranking & Keyword Gap Finder</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">Competitor Intelligence</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Benchmarks keyword rankings against rival publications to surface untapped editorial topics.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Missing trending competitor search terms results in lost readership and missed breaking news traffic.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Input competitor domain, run **`Analyze Keyword Gap`**, and publish dedicated coverage on identified target keywords.</p>
                </div>
            </div>
        </div>

        {{-- 12. UPTIME & SECURITY --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-cyan-100 text-cyan-800 flex items-center justify-center text-lg font-black shrink-0 border border-cyan-200">
                    12
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🛡️ 24/7 Server Uptime & Instant Telegram Alerts</h3>
                    <span class="text-xs font-bold text-cyan-700 bg-cyan-50 px-2.5 py-0.5 rounded-md border border-cyan-200">Emergency Downtime Guard</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Monitors server availability and response codes every 5 minutes from multi-region probe endpoints.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Server crashes and 502/504 errors block search crawlers and readers, causing immediate rank degradation.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Set up Telegram Bot credentials to receive automated alert notifications within 15 seconds of any server disruption.</p>
                </div>
            </div>
        </div>

        {{-- 13. SITEMAP & ROBOTS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    13
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🗺️ Sitemap & Robots.txt Analyzer</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">Search Indexing Directives</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Ensures search engine crawlers have unobstructed access to Google News XML sitemaps and valid robots.txt rules.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Accidental `Disallow: /` lines in robots.txt or corrupt XML syntax immediately halts search indexation across the entire domain.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Verify green XML validity in the Sitemap section and restrict `Disallow` rules strictly to admin and private paths.</p>
                </div>
            </div>
        </div>

        {{-- 14. IMAGE SEO --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-lg font-black shrink-0 border border-purple-200">
                    14
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🖼️ Image SEO & Asset Optimization</h3>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-md border border-purple-200">Image Search Ranking</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Audits article imagery for descriptive ALT attributes (`alt=""`) and optimized WebP/AVIF media delivery.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Search bots cannot visually interpret images without ALT tags, eliminating Google Image and Discover discovery opportunities.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Include concise ALT descriptions with target keywords upon upload and use 16:9 WebP formats.</p>
                </div>
            </div>
        </div>

        {{-- 15. AI SEO ASSISTANT --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    15
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🤖 AI SEO Assistant (AI Optimization Copilot)</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">AI Intelligent Recommendation</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ What is this for?</strong>
                    <p class="leading-relaxed text-slate-600">Provides continuous automated audits and actionable guidance across editorial and technical SEO parameters.</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ Common Issues & Causes:</strong>
                    <p class="leading-relaxed text-rose-950">Subtle structural and semantic gaps often remain hidden from editors, suppressing domain-wide organic authority.</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ Recommended Solution:</strong>
                    <p class="leading-relaxed text-emerald-950">Follow the priority recommendations generated in the `🤖 AI SEO Assistant` tab for quick title, meta, schema, and link fixes.</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
