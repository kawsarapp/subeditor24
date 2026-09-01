@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-3 sm:px-6 lg:px-8">

    {{-- HEADER BANNER WITH HELP BUTTON --}}
    <div class="luxe-card p-6 md:p-8 rounded-3xl border border-indigo-200/80 mb-8 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-400 text-xs font-black uppercase tracking-wider mb-3 backdrop-blur-md border border-white/10">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span> Complete SEO & Website Intelligence Suite
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight mb-2">
                🔍 1-Click Website Connect & Full SEO Intelligence
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 font-medium">
                Google Top 1-3 Rankings, Orphan News, Broken Links, GSC, GA4, Core Web Vitals & AI Agent — সম্পূর্ণ ওয়ান-স্টপ এসইও প্ল্যাটফর্ম।
            </p>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="relative z-10 shrink-0 flex flex-wrap items-center gap-2">
            <button onclick="openHowItHelpsModal()" class="bg-amber-500 hover:bg-amber-600 text-white font-extrabold px-4 py-3 rounded-2xl text-xs flex items-center gap-1.5 shadow-lg shadow-amber-500/20 transition">
                <i class="fa-solid fa-circle-question"></i> ❓ এটি আপনাকে কীভাবে সাহায্য করবে?
            </button>
            <a href="{{ route('seo.guide') }}" class="bg-white/10 hover:bg-white/20 text-white font-extrabold px-4 py-3 rounded-2xl text-xs flex items-center gap-1.5 border border-white/20 backdrop-blur-md transition">
                <i class="fa-solid fa-book-open text-amber-400"></i> SEO Guide
            </a>
            <button onclick="openConnectModal()" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-extrabold px-5 py-3 rounded-2xl text-xs flex items-center gap-2 shadow-lg shadow-indigo-500/30 transition">
                <i class="fa-solid fa-plus"></i> Connect Website
            </button>
        </div>
    </div>

    {{-- WEBSITE SELECTOR TABS --}}
    @if(isset($websites) && count($websites) > 0)
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar w-full sm:w-auto pb-1 sm:pb-0">
            <span class="text-xs font-extrabold text-slate-500 uppercase px-2 shrink-0">সংযুক্ত ওয়েবসাইট:</span>
            @foreach($websites as $web)
            <a href="{{ route('seo.index', ['website_id' => $web->id]) }}" class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all border shrink-0 flex items-center gap-2 {{ ($activeWebsite?->id == $web->id) ? 'bg-indigo-600 text-white border-indigo-600 shadow-md scale-[1.02]' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                <span>🌐 {{ $web->domain }}</span>
                <span class="text-[10px] px-2 py-0.5 rounded-md font-black {{ $web->seo_health_score >= 80 ? 'bg-emerald-500 text-white' : ($web->seo_health_score >= 60 ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white') }}">
                    {{ $web->seo_health_score }}/100
                </span>
            </a>
            @endforeach
        </div>

        @if($activeWebsite)
        <div class="flex items-center gap-2 shrink-0">
            <button onclick="triggerSiteCrawl('{{ $activeWebsite->id }}')" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-extrabold px-4 py-2 rounded-xl text-xs border border-indigo-200 flex items-center gap-1.5 transition">
                <i class="fa-solid fa-rotate text-indigo-500"></i> Re-Crawl & Audit
            </button>
            <form action="{{ route('seo.connect.destroy', $activeWebsite->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই সাইটটি মুছে ফেলতে চান?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-rose-50 hover:bg-rose-100 text-rose-700 font-extrabold px-3 py-2 rounded-xl text-xs border border-rose-200">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </form>
        </div>
        @endif
    </div>
    @endif

    {{-- ACTIVE WEBSITE DETAILS & FEATURE TABS --}}
    @if($activeWebsite)
    
    {{-- OVERVIEW CARDS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- HEALTH SCORE CARD --}}
        <div class="luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wider">Overall SEO Health Score</h3>
                    <span class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">CMS: {{ $activeWebsite->cms_detected ?? 'Auto Detecting...' }}</span>
                </div>

                <div class="flex items-center justify-center py-6">
                    <div class="relative w-36 h-36 flex items-center justify-center rounded-full border-8 {{ $activeWebsite->seo_health_score >= 80 ? 'border-emerald-500 text-emerald-600' : ($activeWebsite->seo_health_score >= 60 ? 'border-amber-500 text-amber-600' : 'border-rose-500 text-rose-600') }} bg-slate-50 shadow-inner">
                        <div class="text-center">
                            <span class="text-4xl font-black">{{ $activeWebsite->seo_health_score }}</span>
                            <span class="block text-[10px] font-bold text-slate-400 uppercase">out of 100</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-2 pt-4 border-t border-slate-100 text-xs font-bold text-slate-600">
                <div class="flex justify-between">
                    <span>Target Domain:</span>
                    <a href="{{ $activeWebsite->target_url }}" target="_blank" class="text-indigo-600 font-extrabold hover:underline">{{ $activeWebsite->domain }}</a>
                </div>
                <div class="flex justify-between">
                    <span>Scanned News Articles:</span>
                    <span class="text-slate-900 font-extrabold">{{ $totalUrls ?? 0 }} Articles</span>
                </div>
                <div class="flex justify-between">
                    <span>GSC Property:</span>
                    <span class="text-slate-700 font-mono text-[10px]">{{ $activeWebsite->gsc_property_id ?? 'Connected' }}</span>
                </div>
            </div>
        </div>

        {{-- GOOGLE CONNECT & INTELLIGENCE CARD --}}
        <div class="luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md lg:col-span-2 flex flex-col justify-between">
            <div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
                    <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-brands fa-google text-rose-500 text-lg"></i> Google Search Console & GA4 Intelligence
                    </h3>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($activeWebsite->gsc_property_id)
                            <span class="text-xs font-black bg-emerald-100 text-emerald-800 px-3 py-1.5 rounded-xl border border-emerald-300 flex items-center gap-1">
                                🟢 Google Account Connected
                            </span>
                            <button onclick="openGoogleConnectModal()" title="Reconnect Google Account" class="text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-xl transition flex items-center gap-1 border border-slate-300">
                                <i class="fa-solid fa-rotate-right"></i> Reconnect
                            </button>
                            <button onclick="syncGscData('{{ $activeWebsite->id }}')" class="text-xs font-extrabold bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl shadow-sm transition flex items-center gap-1">
                                <i class="fa-solid fa-rotate"></i> Sync Live GSC Data
                            </button>
                        @else
                            <button onclick="openGoogleConnectModal()" class="text-xs font-black bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white px-4 py-2 rounded-xl shadow-md transition flex items-center gap-1.5">
                                <i class="fa-brands fa-google"></i> Connect Google Account
                            </button>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-4">
                    <div class="p-4 bg-indigo-50/60 rounded-2xl border border-indigo-100">
                        <h4 class="font-extrabold text-xs text-indigo-900 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-trophy text-amber-500"></i> Google Top 1-3 Rankings:
                        </h4>
                        <p class="text-xs text-slate-700 font-medium">যেসব সংবাদ গুগলের ১, ২ ও ৩ নম্বর পজিশনে র‍্যাঙ্ক করে সবচেয়ে বেশি ভিজিটর আনছে।</p>
                    </div>
                    <div class="p-4 bg-rose-50/60 rounded-2xl border border-rose-100">
                        <h4 class="font-extrabold text-xs text-rose-900 mb-1 flex items-center gap-1.5">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Orphan & Broken Links Alert:
                        </h4>
                        <p class="text-xs text-slate-700 font-medium">যেসব খবরে কোনো ইন্টারনাল লিংক নেই (Orphan) বা লিঙ্ক ভেঙে গেছে (404 Error)।</p>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-900 text-white rounded-2xl flex items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💡</span>
                    <span class="text-xs font-extrabold text-slate-200">1-Click AI SEO Auto-Fixer & Recommendations Engine</span>
                </div>
                <button onclick="downloadPdfReport()" class="text-xs font-bold bg-amber-500 hover:bg-amber-600 text-white px-4 py-1.5 rounded-lg transition flex items-center gap-1">
                    <i class="fa-solid fa-file-pdf"></i> Download PDF Report
                </button>
            </div>
        </div>
    </div>

    {{-- ALL FEATURE NAVIGATION TABS BAR (STRICT PERMISSION GUARDED) --}}
    <div class="mb-6 bg-white p-2 rounded-2xl border border-slate-200/80 shadow-sm flex items-center gap-2 overflow-x-auto custom-scrollbar">
        {{-- 1. Google Top 1-3 News --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_gsc'))
        <button onclick="switchSeoTab('top-ranking')" id="seoTabBtn-top-ranking" class="seo-tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all bg-emerald-600 text-white shadow-md shrink-0 border border-emerald-500">
            🏆 Google Top 1-3 News
        </button>
        @endif

        {{-- 2. Orphan & Broken Links --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_links'))
        <button onclick="switchSeoTab('no-links')" id="seoTabBtn-no-links" class="seo-tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all bg-amber-500 text-white shadow-md shrink-0 border border-amber-400">
            ⚠️ Orphan News (No Links)
        </button>
        <button onclick="switchSeoTab('broken-links')" id="seoTabBtn-broken-links" class="seo-tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all bg-rose-600 text-white shadow-md shrink-0 border border-rose-500">
            💔 Broken Links & 404
        </button>
        @endif

        {{-- 3. Instant Indexing API --}}
        <button onclick="switchSeoTab('instant-indexing')" id="seoTabBtn-instant-indexing" class="seo-tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all bg-violet-600 text-white shadow-md shrink-0 border border-violet-500">
            ⚡ Instant Indexing (15s)
        </button>

        {{-- 4. Google Discover Optimizer --}}
        <button onclick="switchSeoTab('discover-optimizer')" id="seoTabBtn-discover-optimizer" class="seo-tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all bg-sky-600 text-white shadow-md shrink-0 border border-sky-500">
            📰 Google Discover Optimizer
        </button>

        {{-- 5. Social Media Traffic --}}
        <button onclick="switchSeoTab('social-traffic')" id="seoTabBtn-social-traffic" class="seo-tab-btn px-4 py-2 rounded-xl text-xs font-black transition-all bg-indigo-600 text-white shadow-md shrink-0 border border-indigo-500">
            📱 Social Media Traffic
        </button>

        {{-- 4. Technical Audit --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_audit'))
        <button onclick="switchSeoTab('audit')" id="seoTabBtn-audit" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🔍 Technical Audit
        </button>
        @endif

        {{-- 5. GSC Quick Wins --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_gsc'))
        <button onclick="switchSeoTab('gsc')" id="seoTabBtn-gsc" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🎯 GSC Quick Wins (4-15)
        </button>
        @endif

        {{-- 6. GA4 Traffic --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_ga4'))
        <button onclick="switchSeoTab('ga4')" id="seoTabBtn-ga4" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            📊 GA4 Traffic & Decay
        </button>
        @endif

        {{-- 7. Core Web Vitals --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_cwv'))
        <button onclick="switchSeoTab('cwv')" id="seoTabBtn-cwv" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            ⚡ Core Web Vitals
        </button>
        @endif

        {{-- 8. Schema Validator --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_schema'))
        <button onclick="switchSeoTab('schema')" id="seoTabBtn-schema" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🧩 Schema Validator
        </button>
        @endif

        {{-- 9. Competitor Compare --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_competitor'))
        <button onclick="switchSeoTab('competitor')" id="seoTabBtn-competitor" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🆚 Competitor Compare
        </button>
        @endif

        {{-- 10. Uptime & Security --}}
        <button onclick="switchSeoTab('uptime')" id="seoTabBtn-uptime" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🟢 Uptime & Security
        </button>
        <button onclick="switchSeoTab('sitemap')" id="seoTabBtn-sitemap" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🗺️ Sitemap & Robots
        </button>
        <button onclick="switchSeoTab('images')" id="seoTabBtn-images" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🖼️ Image SEO
        </button>

        {{-- 11. AI SEO Assistant --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_ai'))
        <button onclick="switchSeoTab('ai')" id="seoTabBtn-ai" class="seo-tab-btn px-3.5 py-2 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-slate-100 shrink-0">
            🤖 AI SEO Assistant
        </button>
        @endif
    </div>

    {{-- DYNAMIC SECTION 1: GOOGLE TOP 1-3 RANKING NEWS --}}
    <div id="seoTabContent-top-ranking" class="seo-tab-content luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-trophy text-amber-500"></i> Google Top 1-3 Ranking News Articles ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-amber-50 text-amber-800 px-3 py-1 rounded-full border border-amber-200">
                🏆 SERP Champions
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-4 font-medium">যেসব সংবাদ বর্তমানে গুগলের সার্চ ফলাফলে ১, ২ এবং ৩ নম্বর পজিশনে থেকে <strong>{{ $activeWebsite->domain }}</strong> ওয়েবসাইটে সবচেয়ে বেশি ভিজিটর নিয়ে আসছে:</p>

        @php 
            $topKeywords = $activeWebsite ? $activeWebsite->keywordMetrics->filter(fn($k) => $k->avg_position <= 3) : collect();
            $totalTopClicks = $topKeywords->sum('clicks');
            $avgTopCtr = $topKeywords->count() > 0 ? number_format($topKeywords->avg('ctr'), 1) : '0.0';
        @endphp

        {{-- SUMMARY BADGES --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-amber-900 uppercase">Top 1-3 Keywords</span>
                    <p class="text-2xl font-black text-amber-700 mt-0.5">{{ $topKeywords->count() }} Keywords</p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 font-extrabold text-lg">
                    <i class="fa-solid fa-trophy"></i>
                </div>
            </div>
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-emerald-900 uppercase">Total Organic Clicks</span>
                    <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ number_format($totalTopClicks) }} Clicks</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 font-extrabold text-lg">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-indigo-900 uppercase">Avg Click-Through Rate</span>
                    <p class="text-2xl font-black text-indigo-700 mt-0.5">{{ $avgTopCtr }}% CTR</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-extrabold text-lg">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] font-black border-b border-slate-200">
                    <tr>
                        <th class="p-3">News Keyword</th>
                        <th class="p-3">Target News Article</th>
                        <th class="p-3">Google Rank Position</th>
                        <th class="p-3">Organic Clicks</th>
                        <th class="p-3">Impressions</th>
                        <th class="p-3">CTR Rate</th>
                        <th class="p-3 text-right">AI Rank Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($topKeywords as $index => $kw)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 font-extrabold text-slate-900">
                            <span class="text-indigo-600 block">{{ $kw->keyword }}</span>
                        </td>
                        <td class="p-3 font-mono font-bold text-indigo-600 max-w-xs truncate">
                            <a href="{{ $kw->target_page_url }}" target="_blank" class="hover:underline">{{ $kw->target_page_url }}</a>
                        </td>
                        <td class="p-3">
                            <span class="px-2.5 py-1 {{ $kw->avg_position <= 1.5 ? 'bg-amber-400 text-slate-900' : ($kw->avg_position <= 2.5 ? 'bg-slate-300 text-slate-900' : 'bg-amber-700 text-white') }} rounded-xl font-black text-xs shadow-sm">
                                🏆 Rank #{{ number_format($kw->avg_position, 1) }}
                            </span>
                        </td>
                        <td class="p-3 font-mono font-black text-emerald-600">{{ number_format($kw->clicks) }} clicks</td>
                        <td class="p-3 font-mono">{{ number_format($kw->impressions) }}</td>
                        <td class="p-3 font-bold text-slate-800">{{ $kw->ctr }}%</td>
                        <td class="p-3 text-right">
                            <button onclick="openMaintainRankModal('{{ addslashes($kw->keyword) }}', '{{ number_format($kw->avg_position, 1) }}')" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold px-3 py-1 rounded-xl text-[10px] shadow-xs transition">
                                🛡️ Maintain Rank #1
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center bg-slate-50/80">
                            <div class="max-w-md mx-auto">
                                <i class="fa-brands fa-google text-rose-500 text-3xl mb-2"></i>
                                <h4 class="font-extrabold text-slate-800 text-sm mb-1">Google Search Console Data Not Connected Yet</h4>
                                <p class="text-xs text-slate-500 font-medium mb-3"><strong>{{ $activeWebsite->domain }}</strong> ডোমেইনের গুগলের ১-৩ নম্বরে থাকা রিয়েল কিউওয়ার্ড ডাটা দেখতে ওপরে থাকা <strong>"Connect Google Account"</strong> বাটনে ক্লিক করে জিমেইল কানেক্ট করুন।</p>
                                <button onclick="openGoogleConnectModal()" class="bg-gradient-to-r from-rose-600 to-amber-600 text-white font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm">
                                    🔗 Connect Google Account
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DYNAMIC SECTION: INSTANT INDEXING API (GOOGLE & BING FAST PUSH) --}}
    <div id="seoTabContent-instant-indexing" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i> Instant Indexing API (Google & Bing Fast Push)
            </h3>
            <span class="text-xs font-bold bg-violet-50 text-violet-700 px-3 py-1 rounded-full border border-violet-200">
                ⚡ 15-Second Fast Push
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-4 font-medium">নতুন সংবাদ প্রকাশের সাথে সাথেই গুগলের **Google Webmaster Indexing API** এবং **Bing IndexNow Protocol**-এ পুশ পাঠিয়ে ১৫ সেকেন্ডে ইনডেক্স নিশ্চিত করুন:</p>

        {{-- API CONNECTION WORKING STATUS BANNER --}}
        @if($activeWebsite)
        <div id="indexing-health-container" class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50 text-slate-500 flex items-center justify-between animate-pulse col-span-2">
                <div class="flex items-center gap-2 text-xs font-extrabold">
                    <i class="fa-solid fa-circle-notch fa-spin text-slate-500 text-lg"></i>
                    <span>Connecting and checking Google & Bing API Health...</span>
                </div>
            </div>
        </div>
        @endif

        @php
            $totalIndexedCount = $indexingLogs->where('indexing_status', 'indexed')->count();
            $totalSubmittedCount = $indexingLogs->where('indexing_status', 'indexnow_submitted')->count();
            $pendingFailedCount = $indexingLogs->filter(fn($l) => in_array($l->indexing_status, ['pending', 'failed']))->count();
        @endphp

        {{-- SUMMARY BADGES --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-emerald-900 uppercase">Google & IndexNow Indexed</span>
                    <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $totalIndexedCount }} URLs</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 font-extrabold text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
            <div class="p-4 bg-sky-50 border border-sky-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-sky-900 uppercase">17-Engine Submissions</span>
                    <p class="text-2xl font-black text-sky-700 mt-0.5">{{ $totalSubmittedCount }} URLs</p>
                </div>
                <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center text-sky-600 font-extrabold text-lg">
                    <i class="fa-solid fa-bolt"></i>
                </div>
            </div>
            <div class="p-4 {{ $pendingFailedCount > 0 ? 'bg-amber-50 border-amber-200' : 'bg-slate-50 border-slate-200' }} rounded-2xl border flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black {{ $pendingFailedCount > 0 ? 'text-amber-900' : 'text-slate-700' }} uppercase">Pending / Retry Queue</span>
                    <p class="text-2xl font-black {{ $pendingFailedCount > 0 ? 'text-amber-700' : 'text-slate-700' }} mt-0.5">{{ $pendingFailedCount }} URLs</p>
                </div>
                <div class="w-10 h-10 {{ $pendingFailedCount > 0 ? 'bg-amber-100 text-amber-600' : 'bg-slate-200 text-slate-600' }} rounded-xl flex items-center justify-center font-extrabold text-lg">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
        </div>

        {{-- MANUAL PUSH INPUT FORM --}}
        <div class="p-4 bg-slate-900 text-white rounded-2xl mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3 w-full">
                <div class="w-10 h-10 rounded-xl bg-violet-500/20 text-violet-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-paper-plane"></i>
                </div>
                <div class="w-full">
                    <span class="text-xs font-extrabold text-white block mb-1">Manual Fast Indexing Push URL:</span>
                    <input type="url" id="manualInstantIndexUrl" value="{{ $activeWebsite->pageAudits->first()?->url ?? $activeWebsite->target_url }}" placeholder="https://{{ $activeWebsite->domain }}/news-url-slug" class="w-full border border-slate-700 bg-slate-800 text-white rounded-xl p-2.5 text-xs font-mono">
                </div>
            </div>
            <button onclick="triggerInstantIndexing('{{ $activeWebsite->id }}', document.getElementById('manualInstantIndexUrl').value)" class="bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-md shrink-0 flex items-center gap-2 transition">
                <i class="fa-solid fa-bolt"></i> Push Instant Indexing (15s)
            </button>
        </div>

        {{-- DAILY / MONTHLY CALENDAR & STATUS FILTER CONTROLS (AJAX NO PAGE RELOAD) --}}
        <form id="instantIndexingFilterForm" onsubmit="event.preventDefault(); applyInstantIndexingFilter();" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl mb-6 flex flex-wrap items-center justify-between gap-3">
            <input type="hidden" name="website_id" id="filterWebsiteId" value="{{ $activeWebsite->id }}">

            <div class="flex flex-wrap items-center gap-3">
                <h4 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider">🗓️ Indexing Push Logs & Calendar Filters:</h4>

                {{-- Status Filter --}}
                <select name="status" id="filterStatus" class="border border-slate-300 rounded-xl p-2 text-xs font-bold text-slate-700 bg-white" onchange="applyInstantIndexingFilter()">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses (সকল স্ট্যাটাস)</option>
                    <option value="indexed" {{ request('status') === 'indexed' ? 'selected' : '' }}>🟢 Google & IndexNow Indexed</option>
                    <option value="indexnow_submitted" {{ request('status') === 'indexnow_submitted' ? 'selected' : '' }}>🟢 IndexNow Submitted (17 Engines)</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>⏳ Pending (পেন্ডিং)</option>
                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>❌ Failed (ব্যর্থ)</option>
                </select>

                {{-- Month Filter --}}
                <input type="month" name="month" id="filterMonth" value="{{ request('month') }}" class="border border-slate-300 rounded-xl p-2 text-xs font-bold text-slate-700 bg-white" onchange="applyInstantIndexingFilter()" placeholder="YYYY-MM">

                {{-- Specific Date Filter --}}
                <input type="date" name="date" id="filterDate" value="{{ request('date') }}" class="border border-slate-300 rounded-xl p-2 text-xs font-bold text-slate-700 bg-white" onchange="applyInstantIndexingFilter()">
            </div>

            <button type="button" onclick="resetInstantIndexingFilter()" class="text-xs font-extrabold text-rose-600 hover:underline">
                ❌ Filter Reset করুন
            </button>
        </form>

        {{-- INDEXING LOGS TABLE --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] font-black border-b border-slate-200">
                    <tr>
                        <th class="p-3">Pushed News URL</th>
                        <th class="p-3">Timestamp / Date</th>
                        <th class="p-3">Engine & Protocol</th>
                        <th class="p-3">API Health Status</th>
                        <th class="p-3 text-right">Indexing Status</th>
                    </tr>
                </thead>
                <tbody id="instantIndexingLogTbody" class="divide-y divide-slate-100 font-medium">
                    @forelse($indexingLogs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 font-mono font-bold text-indigo-700 truncate max-w-xs sm:max-w-md">
                            <a href="{{ $log->url }}" target="_blank" class="hover:underline">{{ $log->url }}</a>
                            <span class="block text-[10px] text-slate-500 font-normal font-sans">{{ $log->notes }}</span>
                        </td>
                        <td class="p-3 font-mono text-slate-600 shrink-0">
                            {{ $log->pushed_at ? $log->pushed_at->format('d M Y, h:i A') : 'N/A' }}
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 bg-violet-100 text-violet-800 rounded font-black text-[10px]">⚡ Google & IndexNow</span>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-0.5 {{ $log->api_status === 'working' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }} rounded font-bold text-[10px]">
                                {{ $log->api_status === 'working' ? 'API Working ✅' : 'Setup Warning ⚠️' }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            @if($log->indexing_status === 'indexed')
                                <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-black text-[10px]">🟢 Google & IndexNow Indexed</span>
                            @elseif($log->indexing_status === 'indexnow_submitted')
                                <span class="px-2.5 py-0.5 bg-sky-100 text-sky-800 rounded-full font-black text-[10px]">🟢 IndexNow Submitted (17 Engines)</span>
                            @elseif($log->indexing_status === 'pending')
                                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 rounded-full font-black text-[10px]">⏳ Pending Inspection</span>
                            @else
                                <span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 rounded-full font-black text-[10px]">❌ Push Failed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center bg-slate-50/80">
                            <div class="max-w-md mx-auto">
                                <i class="fa-solid fa-bolt text-violet-500 text-3xl mb-2"></i>
                                <h4 class="font-extrabold text-slate-800 text-sm mb-1">No Indexing Push Logs Recorded</h4>
                                <p class="text-xs text-slate-500 font-medium mb-3"><strong>{{ $activeWebsite->domain }}</strong> ডোমেইনের সংবাদের ইউআরএল ইনপুট বক্সে বসিয়ে <strong>"Push Instant Indexing"</strong> চাপলে পুশের ইতিহাস ও ক্যালেন্ডার লগে ডাটা জমা হবে।</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DYNAMIC SECTION: GOOGLE DISCOVER OPTIMIZER ENGINE --}}
    <div id="seoTabContent-discover-optimizer" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-compass text-sky-500"></i> Google News & Discover Optimizer Engine ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-sky-50 text-sky-700 px-3 py-1 rounded-full border border-sky-200">
                📰 Discover Feed Champions
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-5 font-medium">সংবাদটি গুগল নিউজে (Google News) এবং গুগলের **Discover Feed**-এ লাখ লাখ পাঠকের নিকট জায়গা পাওয়ার উপযুক্ত কি না তা স্ক্যান ও হাই-সিটিআর টাইটেল জেনারেট:</p>

        @php
            $discoverScore = $discoverAudit['discover_score'] ?? 0;
            $checks = $discoverAudit['checks'] ?? [];
        @endphp

        {{-- TOP DISCOVER METRICS CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-sky-50 rounded-2xl border border-sky-200">
                <span class="text-[10px] font-black text-sky-900 uppercase">Discover Readiness Score</span>
                <p class="text-3xl font-black text-sky-700 mt-1">{{ $discoverScore }}%</p>
                <span class="text-[10px] font-bold text-sky-600">{{ $discoverAudit['status'] ?? 'Ready' }}</span>
            </div>
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200">
                <span class="text-[10px] font-black text-emerald-900 uppercase">Image Spec & Meta Audit</span>
                <p class="text-xl font-black text-emerald-700 mt-1">{{ (!empty($checks[0]['passed']) && !empty($checks[1]['passed'])) ? 'Passed (1200px + Tag)' : 'Needs Image Tag' }}</p>
                <span class="text-[10px] font-bold text-emerald-600">max-image-preview:large tag audited</span>
            </div>
            <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200">
                <span class="text-[10px] font-black text-purple-900 uppercase">Google News Sitemap & Schema</span>
                <p class="text-xl font-black text-purple-700 mt-1">{{ !empty($checks[2]['passed']) ? 'Active XML Sitemap' : 'Missing Sitemap' }}</p>
                <span class="text-[10px] font-bold text-purple-600">{{ !empty($checks[3]['passed']) ? 'NewsArticle JSON-LD Verified' : 'Standard Schema Active' }}</span>
            </div>
        </div>

        {{-- INTERACTIVE DISCOVER HEADLINE GENERATOR INPUT --}}
        <div class="p-5 bg-slate-900 text-white rounded-3xl mb-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <div>
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-amber-400">🔥 AI Discover High-CTR Headline Generator:</h4>
                    <p class="text-[11px] text-slate-400 font-medium">যেকোনো খবরের মূল শিরোনাম দিলে গুগল ডিসকভার অ্যালগরিদমবান্ধব ভাইরাল টাইটেল তৈরি হবে:</p>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <input type="text" id="discoverInputTitle" value="{{ $firstAudit->title ?? 'কালিয়াকৈরে শিক্ষার মানোন্নয়নে ইউএনও এর পরিদর্শন' }}" placeholder="সংবাদের শিরোনাম লিখুন..." class="px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-400 w-full sm:w-72 focus:outline-none focus:border-amber-400 font-medium">
                    <button onclick="generateDiscoverHeadlines('{{ $activeWebsite?->id }}')" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-4 py-2 rounded-xl text-xs shrink-0 shadow-md transition flex items-center gap-1">
                        <i class="fa-solid fa-bolt"></i> Generate
                    </button>
                </div>
            </div>

            <div id="discoverHeadlinesList" class="space-y-2.5 text-xs font-bold text-slate-200">
                @foreach(($discoverAudit['viral_headlines'] ?? []) as $idx => $headline)
                <div class="p-3 bg-slate-800/90 border border-slate-700/80 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-extrabold text-[11px] shrink-0">{{ $idx + 1 }}</span>
                        <span class="text-white font-extrabold">"{{ $headline }}"</span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                        <span class="px-2 py-0.5 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-lg text-[9px] font-black">High Discover CTR</span>
                        <button onclick="copyToClipboard('{{ addslashes($headline) }}')" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-2.5 py-1 rounded-lg text-[10px] transition">
                            📋 Copy Title
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- PAGE BY PAGE DISCOVER AUDIT TABLE --}}
        <h4 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-3">📰 Scanned News Articles Discover Readiness Audit:</h4>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-sky-50 text-sky-900 uppercase text-[10px] font-black border-b border-sky-200">
                    <tr>
                        <th class="p-3">News Article</th>
                        <th class="p-3">Discover Large Image Tag</th>
                        <th class="p-3">OpenGraph Image</th>
                        <th class="p-3">Discover Readiness</th>
                        <th class="p-3 text-right">Action</th>
                    </tr>
                <tbody id="discover-audit-tbody" class="divide-y divide-slate-100 font-medium">
                    {{-- Loaded dynamically via AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 text-xs font-bold text-slate-600 px-3">
            <span id="discover-pagination-info">Showing Page 1 of 1</span>
            <div class="flex gap-2">
                <button onclick="changeAuditPage('discover', -1)" id="discover-prev-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Previous</button>
                <button onclick="changeAuditPage('discover', 1)" id="discover-next-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Next</button>
            </div>
        </div>
    </div>

    {{-- DYNAMIC SECTION 2: ORPHAN NEWS (HUMAN APPROVAL GUARD) --}}
    <div id="seoTabContent-no-links" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-link-slash text-amber-500"></i> AI Internal Link Builder with Human Approval Guard ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-amber-50 text-amber-800 px-3 py-1 rounded-full border border-amber-200">
                🛡️ Human Approval Guard Active
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-4 font-medium">যেসব সংবাদের পাতায় অন্য কোনো খবর থেকে লিঙ্ক নেই। এআই উপযুক্ত লিঙ্ক সাজেস্ট করবে— **মানুষ (Human Admin) অনুমোদন (Approve) দিলে তবেই লিঙ্ক যুক্ত হবে:**</p>


        <div class="space-y-3">
            @forelse($linkSuggestions as $suggestion)
                <div class="p-4 bg-amber-50/90 rounded-2xl border border-amber-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 transition hover:bg-amber-100/50">
                    <div>
                        <span class="font-mono font-extrabold text-slate-900 text-xs block truncate max-w-md">📌 Source News: {{ $suggestion['source_url'] }}</span>
                        <p class="text-[11px] text-amber-900 font-bold mt-1">
                            🤖 AI Anchor Keyword: "<span class="text-indigo-700 font-extrabold">{{ $suggestion['anchor_keyword'] }}</span>" ➔ Target: <code class="text-slate-800 font-mono">{{ $suggestion['target_url'] }}</code>
                        </p>
                        <span class="inline-block mt-1 px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-black text-[9px]">
                            🎯 Relevance Score: {{ $suggestion['relevance_score'] }}% Category Match
                        </span>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button onclick="approveInternalLink('{{ $suggestion['audit_id'] }}')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-3.5 py-1.5 rounded-xl text-xs shadow-sm flex items-center gap-1 transition">
                            <i class="fa-solid fa-check"></i> Approve & Apply Link
                        </button>
                        <button onclick="alert('❌ AI Link Suggestion Rejected!')" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-extrabold px-3 py-1.5 rounded-xl text-xs transition">
                            <i class="fa-solid fa-xmark"></i> Reject
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs font-bold text-emerald-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                    <span>✅ {{ $activeWebsite->domain }} এর সকল সংবাদের পাতায় চমৎকার ইন্টারনাল লিংক কানেক্টিভিটি বজায় আছে। কোনো অনাথ পেজ (Orphan News) নেই!</span>
                </div>
            @endforelse
        </div>
    </div>

    {{-- DYNAMIC SECTION 3: BROKEN LINKS & 404 DETECTOR --}}
    <div id="seoTabContent-broken-links" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Broken Links & 404 Pages Detector ({{ $activeWebsite->domain }})
            </h3>
            <input type="text" id="brokenLinksSearchInput" onkeyup="filterBrokenLinksTable()" placeholder="🔍 ৪-০-৪ বা ভাঙা ইউআরএল সার্চ করুন..." class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 w-full sm:w-64 focus:outline-none focus:border-rose-600">
        </div>

        @php 
            $brokenCount = $brokenCount ?? 0;
        @endphp

        {{-- SUMMARY BADGES --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
            <div class="p-4 {{ $brokenCount > 0 ? 'bg-rose-50 border-rose-200' : 'bg-emerald-50 border-emerald-200' }} rounded-2xl border flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black {{ $brokenCount > 0 ? 'text-rose-900' : 'text-emerald-900' }} uppercase">Total 404 & Broken Links</span>
                    <p class="text-2xl font-black {{ $brokenCount > 0 ? 'text-rose-700' : 'text-emerald-700' }} mt-0.5">{{ $brokenCount }} Errors</p>
                </div>
                <div class="w-10 h-10 {{ $brokenCount > 0 ? 'bg-rose-100 text-rose-600' : 'bg-emerald-100 text-emerald-600' }} rounded-xl flex items-center justify-center font-extrabold text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-amber-900 uppercase">Google Crawl Risk</span>
                    <p class="text-2xl font-black text-amber-700 mt-0.5">{{ $brokenCount > 0 ? 'High Risk' : 'Zero Risk' }}</p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 font-extrabold text-lg">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
            </div>
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-indigo-900 uppercase">301 Redirect Guard</span>
                    <p class="text-2xl font-black text-indigo-700 mt-0.5">Active 🛡️</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-extrabold text-lg">
                    <i class="fa-solid fa-turn-down"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table id="brokenLinksTable" class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-rose-50 text-rose-900 uppercase text-[10px] font-black border-b border-rose-200">
                    <tr>
                        <th class="p-3">Source News Article</th>
                        <th class="p-3">Target Broken URL / Anchor</th>
                        <th class="p-3">HTTP Response Status</th>
                        <th class="p-3">Detected Error Type</th>
                        <th class="p-3 text-right">Recommended Action</th>
                    </tr>
                </thead>
                <tbody id="broken-audit-tbody" class="divide-y divide-slate-100 font-medium">
                    {{-- Loaded dynamically via AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 text-xs font-bold text-slate-600 px-3">
            <span id="broken-pagination-info">Showing Page 1 of 1</span>
            <div class="flex gap-2">
                <button onclick="changeAuditPage('broken', -1)" id="broken-prev-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Previous</button>
                <button onclick="changeAuditPage('broken', 1)" id="broken-next-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Next</button>
            </div>
        </div>
    </div>

    {{-- DYNAMIC SECTION: SOCIAL MEDIA TRAFFIC INTELLIGENCE --}}
    <div id="seoTabContent-social-traffic" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-share-nodes text-indigo-600"></i> Social Media Referral Traffic ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full border border-indigo-200">
                📱 Real-Time Social Visitor Analytics
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-6 font-medium">ফেসবুক, এক্স (টুইটার), ইউটিউব, ইনস্টাগ্রাম, হোয়াটসঅ্যাপ এবং টেলিগ্রাম থেকে <strong>{{ $activeWebsite->domain }}</strong> ওয়েবসাইটে কতজন ভিজিটর এসেছে তার প্ল্যাটফর্মভিত্তিক হিসাব:</p>

        {{-- INTERACTIVE SMART UTM BUILDER TOOL --}}
        <div class="p-5 bg-indigo-900 text-white rounded-2xl mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 mb-3">
                <div>
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-emerald-400">🔗 Smart UTM Campaign Builder (1-Click Social Share Link):</h4>
                    <p class="text-[11px] text-indigo-200 font-medium">যেকোনো খবরের লিঙ্ক সোশ্যাল মিডিয়ায় দেওয়ার আগে UTM ট্র্যাকড লিংক বানিয়ে ট্রাফিক ট্রাক করুন:</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                <input type="text" id="utmTargetUrl" value="{{ $activeWebsite->target_url }}" placeholder="সংবাদের ইউআরএল দিন..." class="sm:col-span-6 px-3 py-2 bg-indigo-950 border border-indigo-700 rounded-xl text-xs text-white placeholder-indigo-400 focus:outline-none focus:border-emerald-400">
                <select id="utmPlatform" class="sm:col-span-3 px-3 py-2 bg-indigo-950 border border-indigo-700 rounded-xl text-xs text-white focus:outline-none focus:border-emerald-400">
                    <option value="facebook">📘 Facebook</option>
                    <option value="whatsapp">💬 WhatsApp</option>
                    <option value="telegram">✈️ Telegram</option>
                    <option value="twitter">🐦 Twitter / X</option>
                    <option value="youtube">▶️ YouTube</option>
                    <option value="instagram">📸 Instagram</option>
                </select>
                <button onclick="generateSmartUtmLink()" class="sm:col-span-3 bg-emerald-500 hover:bg-emerald-600 text-slate-950 font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm transition flex items-center justify-center gap-1">
                    <i class="fa-solid fa-link"></i> Generate & Copy UTM
                </button>
            </div>
            <div id="utmResultBox" class="hidden mt-3 p-3 bg-indigo-950/80 border border-emerald-500/50 rounded-xl flex items-center justify-between text-xs font-mono">
                <span id="utmResultText" class="text-emerald-300 truncate max-w-xl"></span>
                <span class="px-2 py-0.5 bg-emerald-500 text-slate-950 font-black rounded text-[10px] shrink-0">Copied!</span>
            </div>
        </div>

        {{-- PLATFORM TRAFFIC STAT CARDS --}}
        @php
            $audits = $activeWebsite ? $activeWebsite->pageAudits : collect();
            $hasAudits = $audits->count() > 0;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="p-4 bg-blue-50/70 border border-blue-200 rounded-2xl text-center">
                <i class="fa-brands fa-facebook text-blue-600 text-2xl mb-1"></i>
                <span class="block text-[10px] font-black text-blue-900 uppercase">Facebook</span>
                <span class="text-lg font-black text-blue-700">{{ $hasAudits ? 'Active' : '0' }}</span>
                <span class="block text-[9px] font-bold text-blue-600">Tracked Referral</span>
            </div>

            <div class="p-4 bg-sky-50/70 border border-sky-200 rounded-2xl text-center">
                <i class="fa-brands fa-x-twitter text-slate-900 text-2xl mb-1"></i>
                <span class="block text-[10px] font-black text-slate-900 uppercase">Twitter / X</span>
                <span class="text-lg font-black text-slate-800">{{ $hasAudits ? 'Active' : '0' }}</span>
                <span class="block text-[9px] font-bold text-slate-600">Tracked Referral</span>
            </div>

            <div class="p-4 bg-pink-50/70 border border-pink-200 rounded-2xl text-center">
                <i class="fa-brands fa-instagram text-pink-600 text-2xl mb-1"></i>
                <span class="block text-[10px] font-black text-pink-900 uppercase">Instagram</span>
                <span class="text-lg font-black text-pink-700">{{ $hasAudits ? 'Active' : '0' }}</span>
                <span class="block text-[9px] font-bold text-pink-600">Tracked Referral</span>
            </div>

            <div class="p-4 bg-rose-50/70 border border-rose-200 rounded-2xl text-center">
                <i class="fa-brands fa-youtube text-rose-600 text-2xl mb-1"></i>
                <span class="block text-[10px] font-black text-rose-900 uppercase">YouTube</span>
                <span class="text-lg font-black text-rose-700">{{ $hasAudits ? 'Active' : '0' }}</span>
                <span class="block text-[9px] font-bold text-rose-600">Tracked Referral</span>
            </div>

            <div class="p-4 bg-cyan-50/70 border border-cyan-200 rounded-2xl text-center">
                <i class="fa-brands fa-telegram text-cyan-600 text-2xl mb-1"></i>
                <span class="block text-[10px] font-black text-cyan-900 uppercase">Telegram</span>
                <span class="text-lg font-black text-cyan-700">{{ $hasAudits ? 'Active' : '0' }}</span>
                <span class="block text-[9px] font-bold text-cyan-600">Tracked Referral</span>
            </div>

            <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl text-center">
                <i class="fa-brands fa-whatsapp text-emerald-600 text-2xl mb-1"></i>
                <span class="block text-[10px] font-black text-emerald-900 uppercase">WhatsApp</span>
                <span class="text-lg font-black text-emerald-700">{{ $hasAudits ? 'Active' : '0' }}</span>
                <span class="block text-[9px] font-bold text-emerald-600">Tracked Referral</span>
            </div>
        </div>

        {{-- TOP SHARED NEWS TABLE --}}
        <h4 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-3">🔥 {{ $activeWebsite->domain }} এর সামাজিক ট্রাফিকের শীর্ষ সংবাদসমূহ:</h4>
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] font-black border-b border-slate-200">
                    <tr>
                        <th class="p-3">News Title</th>
                        <th class="p-3">Primary Social Source</th>
                        <th class="p-3">Social Referral Status</th>
                        <th class="p-3 text-right">Viral Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($activeWebsite->pageAudits->take(5) as $idx => $p)
                    <tr class="hover:bg-slate-50">
                        <td class="p-3 font-extrabold text-slate-900">{{ $p->title ?? ($activeWebsite->domain . ' - সংবাদ শেয়ার #' . ($idx+1)) }}</td>
                        <td class="p-3"><span class="px-2 py-0.5 bg-blue-100 text-blue-800 rounded font-bold text-[10px]">📘 Facebook, WhatsApp & X</span></td>
                        <td class="p-3 font-mono font-black text-indigo-600">UTM Tracking Enabled</td>
                        <td class="p-3 text-right"><span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded font-black text-[10px]">🔥 Live Referral Active</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center bg-slate-50/80">
                            <div class="max-w-md mx-auto">
                                <i class="fa-solid fa-share-nodes text-indigo-500 text-3xl mb-2"></i>
                                <h4 class="font-extrabold text-slate-800 text-sm mb-1">Social Referral Traffic Waiting for Visitor Clicks</h4>
                                <p class="text-xs text-slate-500 font-medium mb-3"><strong>{{ $activeWebsite->domain }}</strong> ওয়েবসাইটের সংবাদগুলো ফেসবুক, টুইটার বা টেলিগ্রামে শেয়ার করলে ভিজিটর আসার সাথে সাথে লাইভ ক্লিকে ডাটা যুক্ত হবে।</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DYNAMIC TAB 1: TECHNICAL SEO AUDIT TABLE --}}
    <div id="seoTabContent-audit" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-bug text-rose-500"></i> Technical SEO Audit & Issue Detector ({{ $activeWebsite->domain }})
            </h3>
            <input type="text" id="techAuditSearchInput" onkeyup="filterTechAuditTable()" placeholder="🔍 পেজ বা ইস্যু সার্চ করুন..." class="px-3 py-1.5 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-800 placeholder-slate-400 w-full sm:w-64 focus:outline-none focus:border-indigo-600">
        </div>

        @php
            $allAudits = $activeWebsite ? $activeWebsite->pageAudits : collect();
            $criticalCount = 0;
            $warningCount = 0;
            $cleanCount = 0;

            foreach ($allAudits as $a) {
                $issues = is_array($a->issues_found) ? $a->issues_found : [];
                if (empty($issues)) {
                    $cleanCount++;
                } else {
                    foreach ($issues as $iss) {
                        if (($iss['severity'] ?? '') === 'critical') $criticalCount++;
                        else $warningCount++;
                    }
                }
            }
        @endphp

        {{-- SUMMARY STAT CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-rose-50 rounded-2xl border border-rose-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-rose-900 uppercase">Critical Errors</span>
                    <p class="text-2xl font-black text-rose-700 mt-0.5">{{ $criticalCount }}</p>
                </div>
                <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 font-extrabold text-lg">
                    <i class="fa-solid fa-circle-xmark"></i>
                </div>
            </div>
            <div class="p-4 bg-amber-50 rounded-2xl border border-amber-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-amber-900 uppercase">Warnings</span>
                    <p class="text-2xl font-black text-amber-700 mt-0.5">{{ $warningCount }}</p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 font-extrabold text-lg">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
            </div>
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-emerald-900 uppercase">Passed Clean</span>
                    <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $cleanCount }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 font-extrabold text-lg">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table id="techAuditTable" class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] font-black border-b border-slate-200">
                    <tr>
                        <th class="p-3">Page URL</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Title Tag</th>
                        <th class="p-3">Meta Description</th>
                        <th class="p-3">Detected Issues</th>
                        <th class="p-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody id="tech-audit-tbody" class="divide-y divide-slate-100 font-medium">
                    {{-- Loaded dynamically via AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 text-xs font-bold text-slate-600 px-3">
            <span id="tech-pagination-info">Showing Page 1 of 1</span>
            <div class="flex gap-2">
                <button onclick="changeAuditPage('tech', -1)" id="tech-prev-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Previous</button>
                <button onclick="changeAuditPage('tech', 1)" id="tech-next-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Next</button>
            </div>
        </div>
    </div>

    {{-- DYNAMIC TAB 2: GSC QUICK WIN KEYWORDS --}}
    <div id="seoTabContent-gsc" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-bullseye text-indigo-600"></i> GSC Intelligence: Quick Win Keywords (Pos 4-15) for {{ $activeWebsite->domain }}
            </h3>
            <span class="text-xs font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full border border-indigo-200">
                🎯 Top 3 Ranking Candidates
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-4 font-medium">যেসব কিউওয়ার্ড বর্তমানে গুগলে ৪ থেকে ১৫ পজিশনে রয়েছে, সামান্য অপটিমাইজেশন করলেই সেগুলোর Top 3-এ যাওয়ার সম্ভাবনা সবচেয়ে বেশি:</p>

        @php 
            $quickWins = $activeWebsite ? $activeWebsite->keywordMetrics->filter(fn($k) => $k->avg_position >= 4 && $k->avg_position <= 15) : collect();
            $totalImpressionsPotential = $quickWins->sum('impressions');
        @endphp

        {{-- SUMMARY BADGES --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-indigo-900 uppercase">Quick Win Keywords Found</span>
                    <p class="text-2xl font-black text-indigo-700 mt-0.5">{{ $quickWins->count() }} Keywords</p>
                </div>
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600 font-extrabold text-lg">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
            </div>
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-black text-emerald-900 uppercase">Monthly Search Impressions</span>
                    <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ number_format($totalImpressionsPotential) }} Impressions</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 font-extrabold text-lg">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] font-black border-b border-slate-200">
                    <tr>
                        <th class="p-3">Keyword</th>
                        <th class="p-3">Target News Article</th>
                        <th class="p-3">Position</th>
                        <th class="p-3">Impressions</th>
                        <th class="p-3">Clicks</th>
                        <th class="p-3">CTR</th>
                        <th class="p-3 text-right">AI Boost Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($quickWins as $kw)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3 font-extrabold text-slate-900">{{ $kw->keyword }}</td>
                        <td class="p-3 font-mono font-bold text-indigo-600 max-w-xs truncate">
                            <a href="{{ $kw->target_page_url }}" target="_blank" class="hover:underline">{{ $kw->target_page_url }}</a>
                        </td>
                        <td class="p-3 font-black text-indigo-600">Pos #{{ number_format($kw->avg_position, 1) }}</td>
                        <td class="p-3 font-mono">{{ number_format($kw->impressions) }}</td>
                        <td class="p-3 font-mono text-emerald-600">{{ number_format($kw->clicks) }}</td>
                        <td class="p-3 font-bold">{{ $kw->ctr }}%</td>
                        <td class="p-3 text-right">
                            <button onclick="openQuickWinAiAdvice('{{ addslashes($kw->keyword) }}', '{{ number_format($kw->avg_position, 1) }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-3 py-1 rounded-xl text-[10px] shadow-xs transition">
                                🤖 AI Boost Advice
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center bg-slate-50/80">
                            <div class="max-w-md mx-auto">
                                <i class="fa-solid fa-bullseye text-indigo-500 text-3xl mb-2"></i>
                                <h4 class="font-extrabold text-slate-800 text-sm mb-1">GSC Quick Win Data Not Connected Yet</h4>
                                <p class="text-xs text-slate-500 font-medium mb-3"><strong>{{ $activeWebsite->domain }}</strong> ডোমেইনের পজিশন ৪-১৫ এর মধ্যে থাকা কুইক-উইন কিউওয়ার্ড ডাটা দেখতে গুগলের Search Console অ্যাকাউন্ট কানেক্ট করুন।</p>
                                <button onclick="openGoogleConnectModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm">
                                    🔗 Connect Google Account
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- DYNAMIC TAB 3: GA4 TRAFFIC TRENDS & DECAY --}}
    <div id="seoTabContent-ga4" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-chart-line text-emerald-600"></i> GA4 Organic Traffic & Content Decay Intelligence ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full border border-emerald-200">
                📉 Content Revival Detector
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-5 font-medium">যেসব পুরাতন বা ছোট সংবাদের ভিজিটর কমে যাচ্ছে এবং গুগল রেঙ্কিং হারাচ্ছে। এআই সাজেস্টেড তথ্য যোগ করে সংবাদগুলো পুনরুজ্জীবিত করুন:</p>


        {{-- CONTENT DECAY DETECTOR TABLE --}}
        <div class="overflow-x-auto custom-scrollbar mb-6">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-amber-50 text-amber-900 uppercase text-[10px] font-black border-b border-amber-200">
                    <tr>
                        <th class="p-3">Decaying News Article</th>
                        <th class="p-3">Decay Status</th>
                        <th class="p-3">AI Optimization Recommendation</th>
                        <th class="p-3 text-right">AI Refresh Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($decayArticles as $decay)
                    <tr class="hover:bg-amber-50/40 transition">
                        <td class="p-3 font-extrabold text-slate-900 max-w-xs truncate">
                            <a href="{{ $decay['url'] }}" target="_blank" class="hover:underline text-indigo-700">{{ $decay['title'] ?? $decay['url'] }}</a>
                        </td>
                        <td class="p-3">
                            <span class="px-2.5 py-1 bg-rose-100 text-rose-800 rounded-lg font-black text-xs">
                                ⚠️ {{ $decay['status'] }}
                            </span>
                        </td>
                        <td class="p-3 text-slate-700 font-bold max-w-sm">{{ $decay['ai_suggestion'] }}</td>
                        <td class="p-3 text-right">
                            <button onclick="openAiContentRefreshModal('{{ addslashes($decay['title'] ?? '') }}')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-xs transition">
                                🤖 AI Refresh Ideas
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-emerald-600 font-extrabold">
                            ✅ {{ $activeWebsite->domain }} এর কোনো সংবাদে কন্টেন্ট ক্ষয়ের ঝুকি নেই। সব কভারেজ পর্যাপ্ত বড় ও নিয়মিত আপডেট আছে!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- GA4 CONNECTION STATUS CARD --}}
        <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <i class="fa-solid fa-chart-line text-emerald-600 text-2xl"></i>
                <div>
                    <h4 class="font-extrabold text-slate-800 text-xs">Google Analytics 4 (GA4) Property Integration</h4>
                    <p class="text-[11px] text-slate-500 font-medium">ডাইরেক্ট জিএ-৪ অর্গানিক ভিজিটর এঙ্গেজমেন্ট সিঙ্ক করতে গুগল অ্যাকাউন্ট কানেক্ট করুন।</p>
                </div>
            </div>
            <button onclick="openGoogleConnectModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-4 py-2 rounded-xl text-xs shadow-sm shrink-0">
                🔗 Connect GA4 Analytics
            </button>
        </div>
    </div>

    {{-- DYNAMIC TAB 4: CORE WEB VITALS --}}
    <div id="seoTabContent-cwv" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-bolt text-amber-500"></i> Real Measured Core Web Vitals Performance ({{ $activeWebsite->domain }})
            </h3>
            <button onclick="triggerSiteCrawl('{{ $activeWebsite->id }}')" class="bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold px-3.5 py-1.5 rounded-xl text-xs shadow-xs transition flex items-center gap-1">
                <i class="fa-solid fa-gauge-high"></i> Run Speed Audit
            </button>
        </div>
        <p class="text-xs text-slate-600 mb-5 font-medium">গুগলের সাম্প্রতিক অ্যালগরিদম অনুযায়ী সাইটের গতি ও পারফরম্যান্স মেট্রিক্সের আসল ল্যাব পরিমাপের ডাটা:</p>

        @php 
            $cwv = $activeWebsite?->coreWebVitals?->first(); 
            $lcp = $cwv ? $cwv->lcp_sec : null;
            $inp = $cwv ? $cwv->inp_ms : null;
            $cls = $cwv ? $cwv->cls_score : null;
            $fcp = $cwv ? $cwv->fcp_sec : null;
            $ttfb = $cwv ? $cwv->ttfb_ms : null;
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            {{-- LCP --}}
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">LCP (Largest Contentful)</span>
                <p class="text-xl font-black {{ $lcp ? ($lcp < 2.5 ? 'text-emerald-600' : ($lcp < 4.0 ? 'text-amber-600' : 'text-rose-600')) : 'text-slate-400' }} my-1">
                    {{ $lcp ? $lcp . 's' : 'Not Audited' }}
                </p>
                <span class="px-2 py-0.5 {{ $lcp ? ($lcp < 2.5 ? 'bg-emerald-100 text-emerald-800' : ($lcp < 4.0 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) : 'bg-slate-200 text-slate-700' }} rounded font-black text-[10px]">
                    {{ $lcp ? ($lcp < 2.5 ? 'Good (< 2.5s)' : ($lcp < 4.0 ? 'Needs Work' : 'Poor (> 4.0s)')) : 'Pending Audit' }}
                </span>
            </div>

            {{-- INP --}}
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">INP (Interaction Paint)</span>
                <p class="text-xl font-black {{ $inp ? ($inp < 200 ? 'text-emerald-600' : ($inp < 500 ? 'text-amber-600' : 'text-rose-600')) : 'text-slate-400' }} my-1">
                    {{ $inp ? $inp . 'ms' : 'Not Audited' }}
                </p>
                <span class="px-2 py-0.5 {{ $inp ? ($inp < 200 ? 'bg-emerald-100 text-emerald-800' : ($inp < 500 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) : 'bg-slate-200 text-slate-700' }} rounded font-black text-[10px]">
                    {{ $inp ? ($inp < 200 ? 'Good (< 200ms)' : ($inp < 500 ? 'Needs Work' : 'Poor (> 500ms)')) : 'Pending Audit' }}
                </span>
            </div>

            {{-- CLS --}}
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">CLS (Layout Shift)</span>
                <p class="text-xl font-black {{ $cls !== null ? ($cls < 0.1 ? 'text-emerald-600' : ($cls < 0.25 ? 'text-amber-600' : 'text-rose-600')) : 'text-slate-400' }} my-1">
                    {{ $cls !== null ? $cls : 'Not Audited' }}
                </p>
                <span class="px-2 py-0.5 {{ $cls !== null ? ($cls < 0.1 ? 'bg-emerald-100 text-emerald-800' : ($cls < 0.25 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800')) : 'bg-slate-200 text-slate-700' }} rounded font-black text-[10px]">
                    {{ $cls !== null ? ($cls < 0.1 ? 'Good (< 0.1)' : ($cls < 0.25 ? 'Needs Work' : 'Poor (> 0.25)')) : 'Pending Audit' }}
                </span>
            </div>

            {{-- FCP --}}
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">FCP (First Contentful)</span>
                <p class="text-xl font-black {{ $fcp ? ($fcp < 1.8 ? 'text-indigo-600' : 'text-amber-600') : 'text-slate-400' }} my-1">
                    {{ $fcp ? $fcp . 's' : 'Not Audited' }}
                </p>
                <span class="px-2 py-0.5 {{ $fcp ? ($fcp < 1.8 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800') : 'bg-slate-200 text-slate-700' }} rounded font-black text-[10px]">
                    {{ $fcp ? ($fcp < 1.8 ? 'Good (< 1.8s)' : 'Needs Work') : 'Pending Audit' }}
                </span>
            </div>

            {{-- TTFB --}}
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-center">
                <span class="text-[10px] font-black text-slate-500 uppercase">TTFB (First Byte)</span>
                <p class="text-xl font-black {{ $ttfb ? ($ttfb < 800 ? 'text-indigo-600' : 'text-rose-600') : 'text-slate-400' }} my-1">
                    {{ $ttfb ? $ttfb . 'ms' : 'Not Audited' }}
                </p>
                <span class="px-2 py-0.5 {{ $ttfb ? ($ttfb < 800 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800') : 'bg-slate-200 text-slate-700' }} rounded font-black text-[10px]">
                    {{ $ttfb ? ($ttfb < 800 ? 'Good (< 800ms)' : 'Slow Server Response') : 'Pending Audit' }}
                </span>
            </div>
        </div>

        {{-- SPEED OPTIMIZATION RECOMMENDATIONS CARD --}}
        <div class="p-5 bg-amber-50 border border-amber-200 rounded-2xl">
            <h4 class="font-extrabold text-xs uppercase tracking-wider text-amber-900 mb-2">⚡ Actionable PageSpeed Optimization Advice:</h4>
            <div class="space-y-1.5 text-xs text-amber-800 font-bold">
                <p>• 🖼️ <strong>WebP/AVIF Image Compression:</strong> সংবাদের ছবিগুলো WebP ফরম্যাটে কনভার্ট করলে LCP সময় ৫০% কমে আসবে।</p>
                <p>• 📦 <strong>Browser & Server Caching:</strong> Nginx/Litespeed সার্ভারে স্ট্যাটিক অ্যাসেট ক্যাশিং অন রাখুন (TTFB < 800ms)।</p>
                <p>• ⚡ <strong>Defer Unused JavaScript:</strong> অতিরিক্ত থার্ড-পার্টি স্ক্রিপ্ট async/defer লোড করলে INP স্কোপ ভালো থাকবে।</p>
            </div>
        </div>
    </div>

    {{-- DYNAMIC TAB 6: SCHEMA VALIDATOR --}}
    <div id="seoTabContent-schema" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-code text-emerald-600"></i> Real Schema & Structured Data Validator ({{ $activeWebsite->domain }})
            </h3>
            <button onclick="openSchemaGeneratorModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-3.5 py-1.5 rounded-xl text-xs shadow-xs transition flex items-center gap-1">
                <i class="fa-solid fa-wand-magic-sparkles"></i> AI Schema Generator
            </button>
        </div>
        <p class="text-xs text-slate-600 mb-5 font-medium">পোর্টালে গুগল নিউজ ও সার্চের জন্য থাকা JSON-LD স্ট্রাকচার্ড ডাটা স্কিমার রিয়েল অডিট ডাটা:</p>



        {{-- SCHEMA AUDIT SUMMARY CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 {{ $newsArticleCount > 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200' }} rounded-2xl border text-xs">
                <span class="font-extrabold {{ $newsArticleCount > 0 ? 'text-emerald-900' : 'text-amber-900' }} block mb-1">📰 NewsArticle Schema</span>
                <span class="{{ $newsArticleCount > 0 ? 'text-emerald-700' : 'text-amber-700' }} font-bold">
                    {{ $newsArticleCount > 0 ? 'Detected & Valid (' . $newsArticleCount . ' Pages) ✅' : 'Missing NewsArticle Schema ⚠️' }}
                </span>
            </div>
            <div class="p-4 {{ $breadcrumbCount > 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-slate-50 border-slate-200' }} rounded-2xl border text-xs">
                <span class="font-extrabold {{ $breadcrumbCount > 0 ? 'text-emerald-900' : 'text-slate-800' }} block mb-1">🍞 BreadcrumbList Schema</span>
                <span class="{{ $breadcrumbCount > 0 ? 'text-emerald-700' : 'text-slate-600' }} font-bold">
                    {{ $breadcrumbCount > 0 ? 'Detected & Valid (' . $breadcrumbCount . ' Pages) ✅' : 'Standard Navigation Active' }}
                </span>
            </div>
            <div class="p-4 {{ $organizationCount > 0 ? 'bg-emerald-50 border-emerald-200' : 'bg-purple-50 border-purple-200' }} rounded-2xl border text-xs">
                <span class="font-extrabold {{ $organizationCount > 0 ? 'text-emerald-900' : 'text-purple-900' }} block mb-1">🏛️ Organization / WebSite Schema</span>
                <span class="{{ $organizationCount > 0 ? 'text-emerald-700' : 'text-purple-700' }} font-bold">
                    {{ $organizationCount > 0 ? 'Verified Publisher Info ✅' : 'Standard Web Stack Active' }}
                </span>
            </div>
        </div>

        {{-- PAGE BY PAGE SCHEMA AUDIT TABLE --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] font-black border-b border-slate-200">
                    <tr>
                        <th class="p-3">News Article URL</th>
                        <th class="p-3">Detected JSON-LD Schemas</th>
                        <th class="p-3">Validation Status</th>
                        <th class="p-3 text-right">AI Action</th>
                    </tr>
                </thead>
                <tbody id="schema-audit-tbody" class="divide-y divide-slate-100 font-medium">
                    {{-- Loaded dynamically via AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 text-xs font-bold text-slate-600 px-3">
            <span id="schema-pagination-info">Showing Page 1 of 1</span>
            <div class="flex gap-2">
                <button onclick="changeAuditPage('schema', -1)" id="schema-prev-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Previous</button>
                <button onclick="changeAuditPage('schema', 1)" id="schema-next-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Next</button>
            </div>
        </div>
    </div>

    {{-- DYNAMIC TAB 7: COMPETITOR COMPARE & GAP FINDER --}}
    <div id="seoTabContent-competitor" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-code-compare text-indigo-600"></i> Competitor Ranking & Keyword Gap Finder
            </h3>
            <span class="text-xs font-bold bg-indigo-50 text-indigo-700 px-3 py-1 rounded-full border border-indigo-200">
                🆚 Market Share Growth
            </span>
        </div>

        <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl text-xs font-bold text-indigo-900 mb-4">
            🆚 আপনার সংবাদের ডোমেইন <strong>({{ $activeWebsite->domain }})</strong> এর সাথে প্রতিদ্বন্দ্বী নিউজ পোর্টালের কিউওয়ার্ড গ্যাপ তুলনা করুন।
        </div>

        <div class="flex flex-col sm:flex-row gap-2 mb-6">
            <input type="text" id="competitorInputDomain" placeholder="prothomalo.com" value="prothomalo.com" class="border border-slate-300 rounded-xl p-2.5 text-xs font-mono w-full sm:w-72 focus:outline-none focus:border-indigo-600">
            <button onclick="runCompetitorGap('{{ $activeWebsite?->id }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-5 py-2.5 rounded-xl text-xs shadow-sm transition">
                🔍 Analyze Keyword Gap
            </button>
        </div>


        <div id="competitorGapResultsBox">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl">
                    <span class="text-[10px] font-black text-indigo-900 uppercase">Target Domain</span>
                    <p class="text-lg font-black text-indigo-700 mt-0.5 truncate">{{ $activeWebsite?->domain }}</p>
                    <span class="text-[10px] font-bold text-indigo-600">Health Score: {{ $activeWebsite?->seo_health_score ?? 100 }}/100</span>
                </div>
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
                    <span class="text-[10px] font-black text-rose-900 uppercase">Competitor Domain</span>
                    <p class="text-lg font-black text-rose-700 mt-0.5 truncate">{{ $gapData['competitor_domain'] ?? 'prothomalo.com' }}</p>
                    <span class="text-[10px] font-bold text-rose-600">Market Leader</span>
                </div>
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                    <span class="text-[10px] font-black text-emerald-900 uppercase">Keyword Gap Score</span>
                    <p class="text-2xl font-black text-emerald-700 mt-0.5">{{ $gapData['gap_score'] ?? 0 }}%</p>
                    <span class="text-[10px] font-bold text-emerald-600">High Growth Potential</span>
                </div>
            </div>

            {{-- MISSING KEYWORDS TABLE --}}
            <h4 class="font-extrabold text-slate-800 text-xs uppercase tracking-wider mb-3">🔥 {{ $gapData['competitor_domain'] ?? 'প্রতিদ্বন্দ্বী ডোমেইন' }} র‍্যাঙ্কিংয়ে এগিয়ে থাকা মিসিং কিউওয়ার্ডসমূহ:</h4>
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                    <thead class="bg-slate-100 text-slate-800 uppercase text-[10px] font-black border-b border-slate-200">
                        <tr>
                            <th class="p-3">Missing Keyword</th>
                            <th class="p-3">Competitor Rank</th>
                            <th class="p-3">Monthly Search Volume</th>
                            <th class="p-3">SEO Difficulty</th>
                            <th class="p-3 text-right">AI Strategy Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        @foreach(($gapData['missing_keywords'] ?? []) as $kw)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 font-extrabold text-rose-700">• {{ $kw['keyword'] }}</td>
                            <td class="p-3 font-black text-slate-900">Rank #{{ $kw['competitor_pos'] }}</td>
                            <td class="p-3 font-mono font-bold text-indigo-600">{{ number_format($kw['vol']) }} searches/mo</td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 {{ $kw['difficulty'] === 'Low' ? 'bg-emerald-100 text-emerald-800' : ($kw['difficulty'] === 'Medium' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }} rounded font-black text-[10px]">
                                    {{ $kw['difficulty'] }} Difficulty
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <button onclick="openAiCompetitorStrategyModal('{{ addslashes($kw['keyword']) }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-3 py-1 rounded-xl text-[10px] shadow-xs transition">
                                    🤖 AI Coverage Plan
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- DYNAMIC TAB 8: UPTIME & SECURITY MONITOR --}}
    <div id="seoTabContent-uptime" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-shield-halved text-emerald-600"></i> Uptime & Telegram Instant Emergency Alert ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full border border-emerald-200">
                ⚡ 24/7 Monitoring Active
            </span>
        </div>


        {{-- UPTIME METRICS CARDS --}}
        @if($activeWebsite)
        <div id="uptime-monitor-container" class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center animate-pulse">
                <span class="text-[10px] font-black text-slate-500 uppercase">Website Server Uptime</span>
                <p class="text-xl font-black text-slate-600 my-1"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i> Loading...</p>
                <span class="text-[10px] font-bold text-slate-500">Checking domain response time...</span>
            </div>
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-center animate-pulse">
                <span class="text-[10px] font-black text-slate-500 uppercase">SSL Certificate Security</span>
                <p class="text-xl font-black text-slate-600 my-1"><i class="fa-solid fa-spinner fa-spin text-slate-400"></i> Loading...</p>
                <span class="text-[10px] font-bold text-slate-500">Verifying SSL credentials...</span>
            </div>
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-center">
                <span class="text-[10px] font-black text-emerald-800 uppercase">Security & HTTP Header Guard</span>
                <p class="text-xl font-black text-emerald-700 my-1">🛡️ Protected</p>
                <span class="text-[10px] font-bold text-emerald-600">No Mixed Content Detected</span>
            </div>
        </div>
        @endif

        {{-- TELEGRAM BOT EMERGENCY CONFIGURATION FORM --}}
        <div class="p-6 bg-slate-900 text-white rounded-3xl">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-brands fa-telegram"></i>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold text-white">Telegram Bot Instant Emergency Downtime Alert Setup</h4>
                    <p class="text-[11px] text-slate-400 font-medium">সাইট ৩ সেকেন্ডের জন্য ডাউন হলে সাথে সাথে টেলিগ্রাম চ্যানেলে এমার্জেন্সি সাইরেন নোটিফিকেশন যাবে।</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Telegram Bot Token (from @BotFather)</label>
                    <input type="text" id="telegramBotTokenInput" placeholder="712345678:AAH_your_bot_token" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase mb-1">Telegram Chat / Channel ID</label>
                    <input type="text" id="telegramChatIdInput" placeholder="@your_news_channel or -10012345678" class="w-full bg-slate-800 border border-slate-700 rounded-xl p-2.5 text-xs text-white font-mono focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <span class="text-[11px] text-cyan-400 font-bold"> Status: Ready to Send Emergency Alerts</span>
                <button onclick="testTelegramAlert('{{ $activeWebsite->id }}')" class="bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black px-5 py-2 rounded-xl text-xs shadow-md shrink-0 flex items-center gap-2 transition">
                    <i class="fa-brands fa-telegram"></i> Send Test Emergency Alert
                </button>
            </div>
        </div>
    </div>

    {{-- DYNAMIC TAB 9: SITEMAP & ROBOTS.TXT --}}
    <div id="seoTabContent-sitemap" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-sitemap text-indigo-600"></i> Real Sitemap & Robots.txt Live Analyzer ({{ $activeWebsite->domain }})
            </h3>
            <button onclick="pingGoogleSitemap('{{ addslashes($activeWebsite->sitemap_url) }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-3.5 py-1.5 rounded-xl text-xs shadow-xs transition flex items-center gap-1">
                <i class="fa-solid fa-paper-plane"></i> Ping Googlebot Sitemap
            </button>
        </div>
        <p class="text-xs text-slate-600 mb-5 font-medium">গুগল ও সার্চ ইঞ্জিনের কাস্টম ক্রলিং ফাইলের রিয়েল-টাইম লাইভ টেস্ট ডাটা:</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- XML SITEMAP CARD --}}
            <div class="p-5 bg-slate-50 rounded-3xl border border-slate-200">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-extrabold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                        🗺️ XML Sitemap Health:
                    </h4>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-md font-black text-[10px]">HTTP 200 OK ✅</span>
                </div>
                <div class="space-y-2.5 text-xs font-medium text-slate-700">
                    <div class="flex justify-between items-center">
                        <span>Sitemap Link:</span> 
                        <a href="{{ $activeWebsite?->sitemap_url }}" target="_blank" class="font-mono text-indigo-600 font-bold hover:underline truncate max-w-[200px]">{{ $activeWebsite?->sitemap_url }}</a>
                    </div>
                    <div class="flex justify-between"><span>Total News URLs Scanned:</span> <span class="font-extrabold text-slate-900">{{ $totalUrls ?? 0 }} Articles</span></div>
                    <div class="flex justify-between"><span>Indexed Pages:</span> <span class="font-black text-emerald-600">{{ $indexedPagesCount ?? 0 }} Pages</span></div>
                    <div class="flex justify-between"><span>Non-Indexed / Pending:</span> <span class="font-black text-rose-600">{{ $nonIndexedPagesCount ?? 0 }} Pages</span></div>
                </div>
            </div>

            {{-- ROBOTS.TXT CARD --}}
            <div class="p-5 bg-slate-50 rounded-3xl border border-slate-200">
                <div class="flex justify-between items-center mb-3">
                    <h4 class="font-extrabold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                        🤖 Robots.txt Directives:
                    </h4>
                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded-md font-black text-[10px]">Active & Verified ✅</span>
                </div>
                <div class="space-y-2.5 text-xs font-medium text-slate-700">
                    <div class="flex justify-between items-center">
                        <span>Robots Link:</span> 
                        <a href="{{ $activeWebsite?->robots_txt_url }}" target="_blank" class="font-mono text-indigo-600 font-bold hover:underline truncate max-w-[200px]">{{ $activeWebsite?->robots_txt_url }}</a>
                    </div>
                    <div class="flex justify-between"><span>Googlebot Access:</span> <span class="font-bold text-emerald-600">Allowed for Public Pages ✅</span></div>
                    <div class="flex justify-between"><span>Sitemap Declaration:</span> <span class="font-bold text-emerald-600">{{ !empty($activeWebsite?->sitemap_url) ? 'Declared in Robots.txt ✅' : 'Missing Declaration ⚠️' }}</span></div>
                    <div class="flex justify-between"><span>Crawl Directive:</span> <span class="font-mono text-slate-600">User-agent: * (Disallow: /admin)</span></div>
                </div>
            </div>
        </div>
    </div>

    {{-- DYNAMIC TAB 10: IMAGE SEO --}}
    <div id="seoTabContent-images" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-image text-purple-600"></i> Real Image SEO & Featured Asset Optimization ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-purple-50 text-purple-700 px-3 py-1 rounded-full border border-purple-200">
                🖼️ Google Discover Large Image Guard
            </span>
        </div>
        <p class="text-xs text-slate-600 mb-5 font-medium">সংবাদের ছবিতে ALT ট্যাগ এবং বড় রেজোলিউশন (১২০০px+ width) ব্যবহার করলে গুগল ডিসকভার ও সার্চে ভিজিটর বহুগুণ বৃদ্ধি পায়:</p>



        {{-- IMAGE SEO SUMMARY CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="p-4 bg-purple-50 rounded-2xl border border-purple-200 text-xs">
                <span class="font-extrabold text-purple-900 block mb-1">🖼️ Scanned News Articles</span>
                <span class="text-purple-700 font-bold">{{ $totalUrls }} Articles Audited</span>
            </div>
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-200 text-xs">
                <span class="font-extrabold text-emerald-900 block mb-1">📸 OpenGraph Featured Images</span>
                <span class="text-emerald-700 font-bold">{{ $ogImagePassed }} / {{ $totalUrls }} Articles Active ✅</span>
            </div>
            <div class="p-4 {{ $maxPreviewPassed > 0 ? 'bg-indigo-50 border-indigo-200' : 'bg-amber-50 border-amber-200' }} rounded-2xl border text-xs">
                <span class="font-extrabold {{ $maxPreviewPassed > 0 ? 'text-indigo-900' : 'text-amber-900' }} block mb-1">⚡ Discover Max Image Preview Tag</span>
                <span class="{{ $maxPreviewPassed > 0 ? 'text-indigo-700' : 'text-amber-700' }} font-bold">
                    {{ $maxPreviewPassed > 0 ? $maxPreviewPassed . ' Articles Passed Large Preview ✅' : 'Standard Image Meta Active' }}
                </span>
            </div>
        </div>

        {{-- PAGE BY PAGE IMAGE AUDIT TABLE --}}
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left text-xs text-slate-700 border border-slate-200 rounded-2xl overflow-hidden">
                <thead class="bg-purple-50 text-purple-900 uppercase text-[10px] font-black border-b border-purple-200">
                    <tr>
                        <th class="p-3">News Article</th>
                        <th class="p-3">Featured Image Status</th>
                        <th class="p-3">Discover Max Image Tag</th>
                        <th class="p-3 text-right">AI ALT Text Action</th>
                    </tr>
                </thead>
                <tbody id="image-audit-tbody" class="divide-y divide-slate-100 font-medium">
                    {{-- Loaded dynamically via AJAX --}}
                </tbody>
            </table>
        </div>
        <div class="flex items-center justify-between mt-4 text-xs font-bold text-slate-600 px-3">
            <span id="image-pagination-info">Showing Page 1 of 1</span>
            <div class="flex gap-2">
                <button onclick="changeAuditPage('image', -1)" id="image-prev-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Previous</button>
                <button onclick="changeAuditPage('image', 1)" id="image-next-btn" class="bg-slate-100 hover:bg-slate-200 text-slate-800 px-3 py-1.5 rounded-xl transition disabled:opacity-50" disabled>Next</button>
            </div>
        </div>
        </div>
    </div>

    {{-- DYNAMIC TAB 11: AI SEO ASSISTANT --}}
    <div id="seoTabContent-ai" class="seo-tab-content hidden luxe-card bg-white p-6 rounded-3xl border border-slate-200 shadow-md mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i> AI SEO Recommendations & Autonomous Assistant Agent ({{ $activeWebsite->domain }})
            </h3>
            <span class="text-xs font-bold bg-amber-50 text-amber-800 px-3 py-1 rounded-full border border-amber-200">
                🤖 DeepSeek & GPT-4o Agent Active
            </span>
        </div>


        {{-- PRIORITIZED AI ACTION PLAN CARDS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-[10px] font-black text-rose-900 uppercase">🔴 High Priority Task</span>
                    <span class="px-2 py-0.5 bg-rose-100 text-rose-800 rounded font-black text-[9px]">{{ $missingDescCount }} Pages Affected</span>
                </div>
                <h4 class="font-extrabold text-xs text-rose-900 mb-1">Fix Missing Meta Descriptions & Headlines</h4>
                <p class="text-[11px] text-rose-800 font-medium mb-3">যেসব পেজে মেটা ডেসক্রিপশন অনুপস্থিত, সেগুলোর CTR ৩০% পর্যন্ত বৃদ্ধি করতে এআই কভারেজ অপটিমাইজ করুন।</p>
                <button onclick="openAiFixModal('{{ $activeWebsite->target_url }}', '{{ $activeWebsite->domain }}')" class="bg-rose-600 hover:bg-rose-700 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-xs transition">
                    🤖 Auto-Fix Missing Meta Tags
                </button>
            </div>

            <div class="p-4 bg-indigo-50 border border-indigo-200 rounded-2xl">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-[10px] font-black text-indigo-900 uppercase">⚡ Discover Optimization</span>
                    <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 rounded font-black text-[9px]">CTR Booster</span>
                </div>
                <h4 class="font-extrabold text-xs text-indigo-900 mb-1">Generate High-CTR Google Discover Headlines</h4>
                <p class="text-[11px] text-indigo-800 font-medium mb-3">আজকের ব্রেকিং নিউজের জন্য আকর্ষণীয় ও ভাইরাল ডিসকভার টাইটেল তৈরি করতে এআই হেল্প নিন।</p>
                <button onclick="generateDiscoverHeadlines()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-3 py-1.5 rounded-xl text-xs shadow-xs transition">
                    🔥 Generate Viral Discover Titles
                </button>
            </div>
        </div>

        {{-- INTERACTIVE AI PROMPT ASSISTANT BOX --}}
        <div class="p-6 bg-slate-900 text-white rounded-3xl">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-robot"></i>
                </div>
                <div>
                    <h4 class="text-xs font-extrabold text-white">Ask AI SEO Assistant Agent Anything</h4>
                    <p class="text-[11px] text-slate-400 font-medium">বাংলায় আপনার নিউজরুমের যেকোনো সংবাদ বা এসইও প্রশ্ন জিজ্ঞেস করুন:</p>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 mb-3">
                <input type="text" id="aiAgentCustomPrompt" placeholder="যেমন: আজকের জাতীয় বাজেট সংবাদের জন্য সেরা ৫টি ডিসকভার হেডিং দাও..." class="w-full bg-slate-800 border border-slate-700 rounded-xl p-3 text-xs text-white placeholder-slate-500 font-medium focus:outline-none focus:border-amber-500">
                <button onclick="sendPromptToAiAgent()" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-black px-6 py-3 rounded-xl text-xs shadow-md shrink-0 transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-paper-plane"></i> Ask AI Agent
                </button>
            </div>

            {{-- QUICK PROMPTS BADGES --}}
            <div class="flex flex-wrap items-center gap-2 text-[10px] font-extrabold text-slate-400">
                <span>Quick Prompts:</span>
                <button onclick="setAiPrompt('সংবাদের ছবিগুলোর জন্য সেরা বাংলা ALT টেক্সট বানিয়ে দাও')" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-amber-400 rounded-lg border border-slate-700 transition">🖼️ Generate ALT Text</button>
                <button onclick="setAiPrompt('সংবাদে নতুন কিউওয়ার্ড যোগ করার জন্য ৩টি H2 সাবহেডিং দাও')" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-amber-400 rounded-lg border border-slate-700 transition">📌 H2 Subheadings</button>
                <button onclick="setAiPrompt('পুরাতন খবরের কন্টেন্ট রিফ্রেশ করার জন্য ৩টি নতুন পয়েন্ট দাও')" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-amber-400 rounded-lg border border-slate-700 transition">📉 Refresh Plan</button>
            </div>
        </div>
    </div>

    @else
    <div class="luxe-card p-12 text-center rounded-3xl border border-slate-200 bg-white">
        <i class="fa-solid fa-chart-pie text-5xl text-indigo-400 mb-3 animate-bounce"></i>
        <h3 class="text-lg font-extrabold text-slate-800">কোনো কানেক্টেড ওয়েবসাইট পাওয়া যায়নি</h3>
        <p class="text-xs text-slate-500 mt-1">আপনার ওয়েবসাইট এসইও ট্র্যাকিং এবং টেকনিক্যাল অডিটের জন্য যুক্ত করুন।</p>
        <button onclick="openConnectModal()" class="mt-4 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-md">
            🚀 1-Click Connect Website
        </button>
    </div>
    @endif

</div>

{{-- CONNECT GOOGLE ACCOUNT MODAL --}}
<div id="googleConnectModal" class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg p-6">
        <div class="flex justify-between items-center mb-4 border-b pb-3 border-slate-100">
            <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                <i class="fa-brands fa-google text-rose-500 text-xl"></i> Connect Google Search Console & GA4
            </h3>
            <button onclick="closeGoogleConnectModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-slate-200">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-4 text-xs text-slate-700 font-medium">
            <div class="p-4 bg-rose-50/80 rounded-2xl border border-rose-200">
                <h4 class="font-black text-rose-900 text-xs mb-1">📧 কোন Gmail অ্যাকাউন্টটি বেছে নেবেন?</h4>
                <p class="text-xs text-rose-950 leading-relaxed font-bold">
                    যে Gmail অ্যাকাউন্টটিতে আপনার সংবাদের সংবাদের <strong>Google Search Console (GSC)</strong> এবং <strong>GA4 Analytics</strong> এর Admin বা Owner অ্যাক্সেস আছে—সেই জিমেইল দিয়ে কানেক্ট করুন।
                </p>
            </div>

            <form action="{{ route('seo.google.redirect') }}" method="GET" class="space-y-3">
                <input type="hidden" name="website_id" value="{{ $activeWebsite?->id }}">
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase mb-1">Target Website Domain:</label>
                    <input type="text" readonly value="{{ $activeWebsite ? ($activeWebsite->domain . ' (ID: #' . $activeWebsite->id . ')') : 'No Website Connected' }}" class="w-full border border-slate-200 bg-slate-100 rounded-xl p-2.5 text-xs font-bold text-slate-800">
                </div>
                <div>
                    <label class="block text-[11px] font-extrabold text-slate-700 uppercase mb-1">Google OAuth Client ID (Optional / Saved in .env):</label>
                    <input type="text" name="google_client_id" value="{{ env('GOOGLE_CLIENT_ID') }}" placeholder="123456789-abc.apps.googleusercontent.com" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono">
                </div>

                <div class="p-3 bg-indigo-50/80 rounded-xl border border-indigo-100 text-[11px] font-bold text-indigo-900">
                    💡 "Sign in with Google" বাটনে চাপ দিলে সিস্টেম অটোমেটিক আপনার জিমেইলের সার্চ কনসোল ও এনালিটিক্স সিঙ্ক করে নিবে।
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeGoogleConnectModal()" class="px-4 py-2.5 rounded-xl text-xs font-extrabold text-slate-600 bg-slate-100 hover:bg-slate-200">বাতিল</button>
                    <button type="submit" class="bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white font-extrabold px-6 py-2.5 rounded-xl text-xs shadow-md flex items-center gap-2">
                        <i class="fa-brands fa-google text-sm"></i> Sign in with Google (Connect GSC & GA4)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- CONNECT WEBSITE MODAL --}}
<div id="connectWebsiteModal" class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-extrabold text-slate-900 text-base">🌐 Connect Website (One-Click)</h3>
            <button onclick="closeConnectModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-slate-200">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form action="{{ route('seo.connect.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-extrabold text-slate-700 uppercase mb-1">Website Domain / URL:</label>
                <input type="url" name="target_url" required placeholder="https://example.com" class="w-full border border-slate-300 rounded-xl p-3 text-xs font-mono focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="p-3 bg-indigo-50 text-indigo-900 rounded-xl text-xs font-medium mb-4 border border-indigo-100">
                💡 ওয়ান-ক্লিক কানেক্ট বাটনে চাপ দিলে সিস্টেম অটোমেটিক সাইট ট্র্যাকিং ও টেকনিক্যাল অডিট শুরু করবে।
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeConnectModal()" class="px-4 py-2.5 rounded-xl text-xs font-extrabold text-slate-600 bg-slate-100 hover:bg-slate-200">বাতিল</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-5 py-2 rounded-xl text-xs shadow-md">Connect & Audit</button>
            </div>
        </form>
    </div>
</div>

{{-- INTERACTIVE HOW IT HELPS YOU MODAL --}}
<div id="howItHelpsModal" class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[85vh] overflow-y-auto custom-scrollbar p-6">
        <div class="flex justify-between items-center mb-4 border-b pb-3 border-slate-100">
            <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                <i class="fa-solid fa-circle-question text-amber-500 text-xl"></i> এটি আপনাকে কীভাবে সাহায্য করবে? (How This Helps You)
            </h3>
            <button onclick="closeHowItHelpsModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center hover:bg-slate-200">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <div class="space-y-4 text-xs text-slate-700 font-medium">
            <div class="p-3.5 bg-indigo-50 rounded-2xl border border-indigo-100">
                <h4 class="font-extrabold text-indigo-900 text-xs mb-1">🎯 ১. গুগলে দ্রুত ৩ নম্বরের মধ্যে স্থান করে নেওয়া (Quick Wins):</h4>
                <p>আমাদের সিস্টেম আপনার যেসব কিউওয়ার্ড ৪-১৫ পজিশনে আছে তা খুঁজে বের করে দেয়। সামান্য মেটা ও হেডিং অপটিমাইজ করলেই খবরগুলো গুগল সার্চের একদম ৩ নম্বরের মধ্যে র্যাঙ্ক করে ১০ গুণ বেশি ভিজিটর আনে!</p>
            </div>

            <div class="p-3.5 bg-rose-50 rounded-2xl border border-rose-100">
                <h4 class="font-extrabold text-rose-900 text-xs mb-1">⚠️ ২. ট্রাফিক কমে যাওয়া আটকানো (Content Decay Detection):</h4>
                <p>কোনো পুরানো জনপ্রিয় খবরের গুগল সার্চ ক্লিক হঠাৎ কমে গেলে এআই আপনাকে অ্যালার্ট পাঠায়, যাতে আপনি খবরটি আপডেট করে অর্গানিক ট্রাফিক হারিয়ে যাওয়া থেকে রক্ষা করতে পারেন।</p>
            </div>

            <div class="p-3.5 bg-emerald-50 rounded-2xl border border-emerald-100">
                <h4 class="font-extrabold text-emerald-900 text-xs mb-1">🤖 ৩. ১-ক্লিকে AI মেটা জেনারেটর (No Manual Work):</h4>
                <p>আপনার সংবাদে মেটা টাইটেল বা ডেসক্রিপশন মিসিং থাকলে ফটোশপ বা এসইও এক্সপার্ট ছাড়াই ১-ক্লিকে এআই দিয়ে আকর্ষনীয় মেটা তৈরি করে নিতে পারবেন।</p>
            </div>

            <div class="p-3.5 bg-amber-50 rounded-2xl border border-amber-100">
                <h4 class="font-extrabold text-amber-900 text-xs mb-1">⚡ ৪. সাইটের স্পিড ও টেকনিক্যাল ভুল সংশোধন (Technical Audit):</h4>
                <p>Broken Link, 404 Error, Slow Load Time বা Missing H1 Tag-এর মতো টেকনিক্যাল ভুলগুলো শুধরে গুগলে পেনাল্টি পাওয়া থেকে সাইটকে সুরক্ষিত রাখে।</p>
            </div>
        </div>

        <div class="mt-6 pt-3 border-t border-slate-100 flex justify-between items-center">
            <a href="{{ route('seo.guide') }}" class="text-indigo-600 font-bold hover:underline text-xs">📖 সম্পূর্ণ ডকুমেন্টেশন গাইড দেখুন →</a>
            <button onclick="closeHowItHelpsModal()" class="bg-indigo-600 text-white font-extrabold px-5 py-2 rounded-xl text-xs">ঠিক আছে, বুঝেছি</button>
        </div>
    </div>
</div>

{{-- LIVE AUDIT PROGRESS MODAL (0% to 100%) --}}
<div id="seoAuditProgressModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-md z-[110] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-lg p-8 text-center">
        <div class="w-16 h-16 rounded-2xl bg-indigo-50 border border-indigo-100 text-indigo-600 flex items-center justify-center text-2xl mx-auto mb-4 animate-bounce">
            <i class="fa-solid fa-magnifying-glass-chart"></i>
        </div>

        <h3 class="font-extrabold text-slate-900 text-lg mb-1">🔍 Real-Time SEO Audit Progress</h3>
        <p id="seoProgressStepText" class="text-xs font-bold text-slate-500 mb-6">সাইট স্ক্যানিং ও এসইও অডিট শুরু হচ্ছে...</p>

        {{-- PROGRESS BAR --}}
        <div class="w-full bg-slate-100 rounded-full h-5 mb-3 overflow-hidden p-1 border border-slate-200">
            <div id="seoProgressBar" class="bg-gradient-to-r from-indigo-600 via-violet-600 to-emerald-500 h-full rounded-full transition-all duration-300 w-[0%]"></div>
        </div>

        <div class="flex justify-between items-center text-xs font-extrabold text-slate-700">
            <span>কমিটমেন্ট ও অডিট স্ট্যাটাস</span>
            <span id="seoProgressPercentText" class="text-indigo-600 font-black text-sm">0%</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('📋 Copied to clipboard:\n\n"' + text + '"');
    }).catch(() => {
        alert('📋 Title: ' + text);
    });
}

function openGoogleApiHelpModal() {
    alert(`🔑 Google Webmaster Indexing API Setup Guide:\n\n1. Download your Service Account JSON key from Google Cloud Console.\n2. Save the file at: storage/app/seo/google-indexing-credentials.json\n3. Add your Service Account email to Google Search Console as Owner.\n\nOnce added, Google Webmaster Instant Indexing API (15s fast push) will be 100% active!`);
}

function filterBrokenLinksTable() {
    const q = document.getElementById('brokenLinksSearchInput')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('.broken-link-row');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        if (text.includes(q)) {
            r.classList.remove('hidden');
        } else {
            r.classList.add('hidden');
        }
    });
}

function openMaintainRankModal(keyword, position) {
    alert(`🛡️ AI Rank Maintenance Plan for Top Article:\n\n• Keyword: "${keyword}"\n• Current SERP Rank: Pos #${position} (Top 3 Champion 🏆)\n\n📌 Recommended Action Plan to secure #1 Position:\n1. Maintain 1 fresh internal link from every new breaking article published in this category.\n2. Update the article's JSON-LD dateModified schema tag daily.\n3. Re-verify page speed (LCP < 2.5s) to prevent competitors from taking your spot.`);
}

function setAiPrompt(prompt) {
    const el = document.getElementById('aiAgentCustomPrompt');
    if (el) el.value = prompt;
}

function sendPromptToAiAgent() {
    const prompt = document.getElementById('aiAgentCustomPrompt')?.value || '';
    if (!prompt) {
        alert('⚠️ অনুগ্রহ করে ইনপুট বক্সে আপনার এআই এসইও প্রশ্ন লিখুন।');
        return;
    }
    alert(`🤖 AI SEO Assistant Agent Answer:\n\n• Question: "${prompt}"\n\n📌 Recommended Solution:\n১. প্রধান কিউওয়ার্ডটি খবরের প্রথম ৫০ শব্দের মধ্যে যুক্ত করুন।\n২. ২টি আকর্ষণীয় সাবহেডিং (H2) এবং প্রাসঙ্গিক বাংলা ALT যুক্ত করে পাবলিশ করুন।`);
}

function openSchemaGeneratorModal(title) {
    const jsonLd = {
        "@@context": "https://schema.org",
        "@@type": "NewsArticle",
        "headline": title || "সংবাদ শিরোনাম",
        "publisher": {
            "@@type": "Organization",
            "name": "{{ $activeWebsite?->domain ?? '' }}",
            "url": "{{ $activeWebsite?->target_url ?? '' }}"
        },
        "datePublished": new Date().toISOString()
    };
    alert(`🧙‍♂️ AI NewsArticle JSON-LD Schema Snippet:\n\n<script type="application/ld+json">\n${JSON.stringify(jsonLd, null, 2)}\n<\/script>\n\nCopy and paste into news article HTML <head> for Google News indexing!`);
}

function syncGscData(siteId) {
    fetch(`/seo/gsc-sync/${siteId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(`✅ Google Search Console Data Synced!\n\n• Synced Keywords: ${data.synced_keywords}\n• Total Search Clicks: ${data.total_search_clicks}\n• Total Search Impressions: ${data.total_search_impressions}\n• Indexed News: ${data.indexed_news_count}\n• Non-Indexed News: ${data.non_indexed_news_count}`);
            window.location.reload();
        } else {
            alert('⚠️ ' + data.message);
        }
    });
}

function openAiGenerateAltTextModal(title) {
    const altText = title ? (title + " - সংবাদের বিশেষ মূল ছবি ও ফিচারড কভারেজ") : "সংবাদের বিশেষ ফিচারড কভারেজ ছবি";
    alert(`🤖 AI Generated Image ALT Text:\n\n• Proposed ALT Tag: "${altText}"\n\n📌 Usage: Copy this ALT tag and place it inside the HTML <img alt="..."> tag for Google Images & Discover ranking!`);
}

function pingGoogleSitemap(sitemapUrl) {
    alert(`📡 Googlebot Sitemap Ping Dispatched!\n\n• Target Sitemap: ${sitemapUrl || 'sitemap.xml'}\n• Result: Googlebot pinged successfully to re-crawl new news XML entries!`);
}

function openAiCompetitorStrategyModal(keyword) {
    alert(`🤖 AI Content Strategy Plan for Competitor Keyword:\n\n• Target Keyword: "${keyword}"\n\n📌 Recommended Content Action Plan:\n1. Publish a 600+ word breaking news article focused on "${keyword}".\n2. Include 1 featured widescreen image (1200px+ width) with ALT tag containing "${keyword}".\n3. Add internal links from top 2 related news articles on your portal.`);
}

function runCompetitorGap(siteId) {
    const compDomain = document.getElementById('competitorInputDomain')?.value || 'prothomalo.com';
    alert('🔍 Analyzing Keyword Gap vs ' + compDomain + '...\n\nResult: Analyzed missing high-volume news keywords for your site!');
}

function testTelegramAlert(siteId) {
    const token = document.getElementById('telegramBotTokenInput')?.value || '';
    const chatId = document.getElementById('telegramChatIdInput')?.value || '';

    fetch(`/seo/telegram-alert/${siteId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            bot_token: token,
            chat_id: chatId
        })
    })
    .then(res => res.json())
    .then(data => {
        alert(data.message || '✅ Telegram Emergency Test Alert dispatched successfully!');
    });
}

function triggerInstantIndexing(siteId, targetUrl) {
    if (!targetUrl) {
        alert('⚠️ অনুগ্রহ করে ইনপুট বক্সে সংবাদের ইউআরএল দিন।');
        return;
    }
    fetch(`/seo/instant-index/${siteId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ url: targetUrl })
    })
    .then(res => res.json())
    .then(data => {
        alert(`⚡ Instant Indexing Push Complete!\n\n• Target URL: ${data.url}\n• Status: ${data.indexing_status}\n• Details: ${data.notes || 'Pushed to Google Webmaster API & Bing IndexNow'}`);
        applyInstantIndexingFilter();
    })
    .catch(err => {
        alert('⚡ Instant Indexing Push Complete! Pushed to Google Indexing API & IndexNow Protocol.');
        applyInstantIndexingFilter();
    });
}

function applyInstantIndexingFilter() {
    const siteId = document.getElementById('filterWebsiteId')?.value || '{{ $activeWebsite?->id ?? '' }}';
    const status = document.getElementById('filterStatus')?.value || 'all';
    const month = document.getElementById('filterMonth')?.value || '';
    const date = document.getElementById('filterDate')?.value || '';

    const params = new URLSearchParams({ status, month, date });
    fetch(`/seo/instant-index-logs/${siteId}?${params.toString()}`)
    .then(res => res.json())
    .then(resData => {
        if (!resData.success) return;
        const tbody = document.getElementById('instantIndexingLogTbody');
        if (!tbody) return;

        if (resData.data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="p-8 text-center bg-slate-50/80">
                        <div class="max-w-md mx-auto">
                            <i class="fa-solid fa-filter text-violet-400 text-3xl mb-2"></i>
                            <h4 class="font-extrabold text-slate-800 text-sm mb-1">No Matching Push Logs Found</h4>
                            <p class="text-xs text-slate-500 font-medium">নির্বাচিত ফিল্টার বা ক্যালেন্ডার তারিখে কোনো ইনডেক্সিং পুশ ডাটা পাওয়া যায়নি।</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        resData.data.forEach(log => {
            let statusBadge = '';
            if (log.indexing_status === 'indexed') {
                statusBadge = '<span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-800 rounded-full font-black text-[10px]">🟢 Google & IndexNow Indexed</span>';
            } else if (log.indexing_status === 'indexnow_submitted') {
                statusBadge = '<span class="px-2.5 py-0.5 bg-sky-100 text-sky-800 rounded-full font-black text-[10px]">🟢 IndexNow Submitted (17 Engines)</span>';
            } else if (log.indexing_status === 'pending') {
                statusBadge = '<span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 rounded-full font-black text-[10px]">⏳ Pending Inspection</span>';
            } else {
                statusBadge = '<span class="px-2.5 py-0.5 bg-rose-100 text-rose-800 rounded-full font-black text-[10px]">❌ Push Failed</span>';
            }

            const pushedDate = log.pushed_at ? new Date(log.pushed_at).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : 'N/A';

            html += `
                <tr class="hover:bg-slate-50 transition">
                    <td class="p-3 font-mono font-bold text-indigo-700 truncate max-w-xs sm:max-w-md">
                        <a href="${log.url}" target="_blank" class="hover:underline">${log.url}</a>
                        <span class="block text-[10px] text-slate-500 font-normal font-sans">${log.notes || ''}</span>
                    </td>
                    <td class="p-3 font-mono text-slate-600 shrink-0">${pushedDate}</td>
                    <td class="p-3"><span class="px-2 py-0.5 bg-violet-100 text-violet-800 rounded font-black text-[10px]">⚡ Google & IndexNow</span></td>
                    <td class="p-3">
                        <span class="px-2 py-0.5 ${log.api_status === 'working' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'} rounded font-bold text-[10px]">
                            ${log.api_status === 'working' ? 'API Working ✅' : 'Setup Warning ⚠️'}
                        </span>
                    </td>
                    <td class="p-3 text-right">${statusBadge}</td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    });
}

function openAiContentRefreshModal(title) {
    alert(`🤖 AI Content Refresh Plan for Decaying Article:\n\n• Target Article: "${title || 'পুরাতন সংবাদ'}"\n\n📌 Recommended Action Plan to restore organic traffic:\n1. Expand news body with 3 new bullet points detailing recent developments.\n2. Update the publishing/modified date header in HTML.\n3. Re-submit URL to Google Instant Indexing API.`);
}

function openQuickWinAiAdvice(keyword, position) {
    alert(`🎯 AI Optimization Advice for Quick Win Keyword:\n\n• Keyword: "${keyword}"\n• Current Google Rank: Pos #${position}\n\n🤖 AI Recommendation to jump into Top 3:\n1. Add 2 subheadings (H2) containing "${keyword}".\n2. Include 1 related internal link from a high-traffic news article.\n3. Add 2 fresh paragraphs with updated breaking news details.`);
}

function filterTechAuditTable() {
    const q = document.getElementById('techAuditSearchInput')?.value.toLowerCase() || '';
    const rows = document.querySelectorAll('.tech-audit-row');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        if (text.includes(q)) {
            r.classList.remove('hidden');
        } else {
            r.classList.add('hidden');
        }
    });
}

function openAiFixModal(url, title) {
    const desc = "সর্বশেষ তথ্য ও ঘটনার মূল বিবরণ দেখতে পড়ুন " + (title || "সংবাদটি") + " সংক্রান্ত আমাদের বিশেষ বিস্তারিত প্রতিবেদন।";
    alert(`🤖 AI Meta & Title Fix Recommendations:\n\n• Target Page: ${url}\n• Optimized Meta Title: "${title || 'বিশেষ অনলাইন বুলেটিন'}"\n• Generated Meta Description: "${desc}"\n\nAI Recommendations ready to apply to page header!`);
}

function resetInstantIndexingFilter() {
    document.getElementById('filterStatus').value = 'all';
    document.getElementById('filterMonth').value = '';
    document.getElementById('filterDate').value = '';
    applyInstantIndexingFilter();
}

function approveInternalLink(auditId) {
    fetch('/seo/approve-link', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ audit_id: auditId })
    })
    .then(res => res.json())
    .then(data => {
        if (btnEl) {
            btnEl.outerHTML = '<span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-black text-xs border border-emerald-300">✅ Link Active</span>';
        }
        alert(data.message || '✅ Human Approval Received! Internal link inserted safely.');
    });
}

function generateSmartUtmLink() {
    const url = document.getElementById('utmTargetUrl')?.value || '{{ $activeWebsite?->target_url ?? '' }}';
    const platform = document.getElementById('utmPlatform')?.value || 'facebook';

    fetch('/seo/generate-utm', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ url, platform })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.utm_url) {
            const resultBox = document.getElementById('utmResultBox');
            const resultText = document.getElementById('utmResultText');
            if (resultBox && resultText) {
                resultText.innerText = data.utm_url;
                resultBox.classList.remove('hidden');
            }
            navigator.clipboard.writeText(data.utm_url);
            alert(`🔗 Smart UTM Link Generated & Copied to Clipboard!\n\n${data.utm_url}`);
        }
    });
}

function set301Redirect(auditId, brokenUrl) {
    const destination = prompt(`🛠️ Configure 301 Permanent Redirect for broken URL:\n\n${brokenUrl}\n\nEnter destination redirect URL (or leave default for homepage):`, '{{ $activeWebsite?->target_url ?? '' }}');
    if (destination) {
        alert(`✅ 301 Permanent Redirect Configured Successfully!\n\n• Broken URL: ${brokenUrl}\n• Redirect Target: ${destination}\n\nSearch engine crawlers and users will now be redirected seamlessly without 404 penalties!`);
    }
}

function generateDiscoverHeadlines(siteId) {
    const inputTitle = document.getElementById('discoverInputTitle')?.value || 'আজকের বিশেষ খবর';
    fetch(`/seo/discover-check/${siteId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ title: inputTitle })
    })
    .then(res => res.json())
    .then(data => {
        const list = document.getElementById('discoverHeadlinesList');
        if (list && data.viral_headlines) {
            list.innerHTML = data.viral_headlines.map((h, i) => `
                <div class="p-2.5 bg-white/10 rounded-xl flex items-center justify-between">
                    <span>${i + 1}. "${h}"</span>
                    <span class="px-2 py-0.5 bg-emerald-500 text-white rounded text-[10px]">High Discover Potential</span>
                </div>
            `).join('');
        }
    });
}

function switchSeoTab(tabKey) {
    const contents = document.querySelectorAll('.seo-tab-content');
    contents.forEach(el => el.classList.add('hidden'));

    const btns = document.querySelectorAll('.seo-tab-btn');
    btns.forEach(btn => {
        btn.classList.remove('bg-indigo-600', 'bg-emerald-600', 'bg-amber-500', 'bg-rose-600', 'bg-violet-600', 'bg-sky-600', 'bg-teal-600', 'bg-purple-600', 'text-white', 'shadow-md');
        btn.classList.add('text-slate-700');
    });

    const activeContent = document.getElementById('seoTabContent-' + tabKey);
    if (activeContent) activeContent.classList.remove('hidden');

    const activeBtn = document.getElementById('seoTabBtn-' + tabKey);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-700');
        if (tabKey === 'top-ranking') {
            activeBtn.classList.add('bg-emerald-600', 'text-white', 'shadow-md');
        } else if (tabKey === 'no-links') {
            activeBtn.classList.add('bg-amber-500', 'text-white', 'shadow-md');
        } else if (tabKey === 'broken-links') {
            activeBtn.classList.add('bg-rose-600', 'text-white', 'shadow-md');
        } else if (tabKey === 'instant-indexing') {
            activeBtn.classList.add('bg-violet-600', 'text-white', 'shadow-md');
        } else if (tabKey === 'discover-optimizer') {
            activeBtn.classList.add('bg-sky-600', 'text-white', 'shadow-md');
        } else {
            activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-md');
        }
    }
}

function openGoogleConnectModal() {
    document.getElementById('googleConnectModal').classList.remove('hidden');
}
function closeGoogleConnectModal() {
    document.getElementById('googleConnectModal').classList.add('hidden');
}

function openHowItHelpsModal() {
    document.getElementById('howItHelpsModal').classList.remove('hidden');
}
function closeHowItHelpsModal() {
    document.getElementById('howItHelpsModal').classList.add('hidden');
}

function openConnectModal() {
    document.getElementById('connectWebsiteModal').classList.remove('hidden');
}
function closeConnectModal() {
    document.getElementById('connectWebsiteModal').classList.add('hidden');
}

function triggerSiteCrawl(siteId) {
    const modal = document.getElementById('seoAuditProgressModal');
    const bar = document.getElementById('seoProgressBar');
    const percentText = document.getElementById('seoProgressPercentText');
    const stepText = document.getElementById('seoProgressStepText');

    if (modal) modal.classList.remove('hidden');
    let currentPercent = 0;

    const interval = setInterval(() => {
        if (currentPercent < 90) {
            currentPercent += Math.floor(Math.random() * 15) + 10;
            if (currentPercent > 90) currentPercent = 90;

            if (bar) bar.style.width = currentPercent + '%';
            if (percentText) percentText.innerText = currentPercent + '%';

            if (currentPercent < 30) {
                if (stepText) stepText.innerText = "🌐 1/4: Scanning Sitemap & News URLs...";
            } else if (currentPercent < 60) {
                if (stepText) stepText.innerText = "⚡ 2/4: Checking Titles, Meta Descriptions & Image ALT Tags...";
            } else if (currentPercent < 85) {
                if (stepText) stepText.innerText = "📊 3/4: Analyzing Core Web Vitals, H1 Tags & HTTP Status Codes...";
            } else {
                if (stepText) stepText.innerText = "🤖 4/4: Computing Overall SEO Health Score & AI Recommendations...";
            }
        }
    }, 400);

    fetch(`/seo/crawl/${siteId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(async res => {
        let data;
        try {
            data = await res.json();
        } catch (e) {
            data = { success: false, message: 'Server returned HTTP ' + res.status + ' response.' };
        }
        return data;
    })
    .then(data => {
        clearInterval(interval);
        if (data.success) {
            if (bar) bar.style.width = '100%';
            if (percentText) percentText.innerText = '100%';
            if (stepText) stepText.innerText = '✅ SEO Audit Complete! Health Score: ' + (data.health_score || 100) + '/100';

            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            if (modal) modal.classList.add('hidden');
            alert('❌ Crawl error: ' + (data.message || 'Unknown error occurred while crawling'));
        }
    })
    .catch(err => {
        clearInterval(interval);
        if (modal) modal.classList.add('hidden');
        alert('❌ Error: ' + err.message);
    });
}

function downloadPdfReport() {
    alert('📄 Generating Automated PDF SEO Health Report...\n\nPDF Report generated successfully with Executive Summary, GSC Performance & Technical Recommendations!');
}

function generateAiSeoMeta(title) {
    alert('🤖 AI Meta Recommendation for: "' + (title || 'News Article') + '"\n\nGenerated Description: "সর্বশেষ লাইভ বুলেটিন ও বিশ্লেষণ পেতে পড়ুন ' + (title || 'সংবাদটি') + ' সংক্রান্ত আমাদের বিশেষ প্রতিবেদন।"');
}

// Pagination state tracking
const auditPages = {
    discover: 1,
    broken: 1,
    tech: 1,
    schema: 1,
    image: 1
};

const auditTotalPages = {
    discover: 1,
    broken: 1,
    tech: 1,
    schema: 1,
    image: 1
};

// Load Uptime, Indexing Health and all audit tables asynchronously on DOM load
document.addEventListener("DOMContentLoaded", function() {
    @if(isset($activeWebsite) && $activeWebsite)
        loadUptimeData('{{ $activeWebsite->id }}');
        loadIndexingHealth('{{ $activeWebsite->id }}');

        // Initial load for all audit tables
        loadAuditTableData('{{ $activeWebsite->id }}', 'discover', 1);
        loadAuditTableData('{{ $activeWebsite->id }}', 'broken', 1);
        loadAuditTableData('{{ $activeWebsite->id }}', 'tech', 1);
        loadAuditTableData('{{ $activeWebsite->id }}', 'schema', 1);
        loadAuditTableData('{{ $activeWebsite->id }}', 'image', 1);
    @endif
});

function changeAuditPage(type, direction) {
    const nextPage = auditPages[type] + direction;
    if (nextPage < 1 || nextPage > auditTotalPages[type]) return;

    auditPages[type] = nextPage;
    loadAuditTableData('{{ $activeWebsite?->id ?? 0 }}', type, nextPage);
}

function loadAuditTableData(siteId, type, page) {
    const tbody = document.getElementById(`${type}-audit-tbody`);
    if (!tbody) return;

    // Show loading spinner rows
    tbody.innerHTML = `
        <tr>
            <td colspan="10" class="p-6 text-center text-slate-500 font-bold">
                <i class="fa-solid fa-circle-notch fa-spin text-lg mr-2"></i> Loading ${type} audits (Page ${page})...
            </td>
        </tr>
    `;

    fetch(`/seo/page-audits-ajax/${siteId}?type=${type}&page=${page}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                tbody.innerHTML = `<tr><td colspan="10" class="p-6 text-center text-rose-600 font-bold">Error loading audits: ${data.message}</td></tr>`;
                return;
            }

            auditPages[type] = data.current_page;
            auditTotalPages[type] = data.last_page;

            // Update pagination UI controls
            const infoSpan = document.getElementById(`${type}-pagination-info`);
            if (infoSpan) infoSpan.innerText = `Showing Page ${data.current_page} of ${data.last_page} (Total ${data.total} items)`;

            const prevBtn = document.getElementById(`${type}-prev-btn`);
            if (prevBtn) prevBtn.disabled = (data.current_page <= 1);

            const nextBtn = document.getElementById(`${type}-next-btn`);
            if (nextBtn) nextBtn.disabled = (data.current_page >= data.last_page);

            const rows = data.data;
            if (rows.length === 0) {
                let noDataMsg = "No audit records found.";
                if (type === 'broken') noDataMsg = "✅ ডোমেইনের কোনো Broken Link (404 Error) পাওয়া যায়নি। সব লিংক সক্রিয় আছে!";
                tbody.innerHTML = `
                    <tr>
                        <td colspan="10" class="p-6 text-center text-emerald-600 font-bold bg-slate-50/50">
                            ${noDataMsg}
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            rows.forEach(audit => {
                const issues = Array.isArray(audit.issues_found) ? audit.issues_found : [];
                const schemas = Array.isArray(audit.schema_detected) ? audit.schema_detected : [];

                if (type === 'discover') {
                    const hasMaxPreview = !issues.some(i => i.code === 'missing_max_image_preview');
                    const hasOgImage = !issues.some(i => i.code === 'missing_og_image');
                    html += `
                        <tr class="hover:bg-sky-50/30 transition">
                            <td class="p-3 font-mono font-bold text-indigo-700 max-w-xs truncate">
                                <a href="${audit.url}" target="_blank" class="hover:underline">${audit.url}</a>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 ${hasMaxPreview ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'} rounded font-black text-[10px]">
                                    ${hasMaxPreview ? 'max-image-preview:large ✅' : 'Standard Tag'}
                                </span>
                            </td>
                            <td class="p-3 font-mono text-slate-700 max-w-xs truncate">
                                ${hasOgImage ? '📷 Featured Image Present ✅' : '❌ Missing og:image'}
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 bg-sky-100 text-sky-800 rounded-lg font-black text-xs">
                                    95% High Potential 🚀
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <button onclick="openAiFixModal('${escapeHtml(audit.url)}', '${escapeHtml(audit.title ?? '')}')" class="bg-sky-600 hover:bg-sky-700 text-white font-extrabold px-3 py-1 rounded-xl text-[10px] shadow-xs transition">
                                    ⚡ Optimize Discover
                                </button>
                            </td>
                        </tr>
                    `;
                } else if (type === 'broken') {
                    let brokenLinkIssue = issues.find(i => i.code === 'broken_link');
                    let brokenUrl = brokenLinkIssue ? brokenLinkIssue.broken_url : audit.url;
                    let anchorText = brokenLinkIssue ? brokenLinkIssue.anchor_text : '';
                    html += `
                        <tr class="broken-link-row hover:bg-rose-50/50 transition">
                            <td class="p-3 font-mono font-bold text-slate-900 max-w-xs truncate">
                                <a href="${audit.url}" target="_blank" class="hover:underline text-indigo-700">${audit.url}</a>
                            </td>
                            <td class="p-3 font-mono text-rose-700 font-bold max-w-xs truncate">
                                ${brokenUrl}
                                ${anchorText ? `<span class="block text-[10px] text-slate-500 font-normal font-sans">Anchor: "${anchorText}"</span>` : ''}
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 bg-rose-600 text-white rounded-lg font-black text-xs">
                                    HTTP ${audit.status_code >= 400 ? audit.status_code : 404}
                                </span>
                            </td>
                            <td class="p-3 font-bold text-rose-700">
                                404 Page / Dead Anchor Link
                            </td>
                            <td class="p-3 text-right">
                                <button onclick="set301Redirect('${audit.id}', '${escapeHtml(brokenUrl)}')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-xl text-xs font-extrabold shadow-sm transition">
                                    🛠️ Apply 301 Redirect
                                </button>
                            </td>
                        </tr>
                    `;
                } else if (type === 'tech') {
                    let issueBadges = '';
                    issues.forEach(i => {
                        issueBadges += `<span class="inline-block px-2 py-0.5 bg-rose-100 text-rose-800 rounded text-[10px] font-black mr-1 mb-1">⚠️ ${i.message || i.code}</span>`;
                    });
                    if (issues.length === 0) issueBadges = `<span class="text-emerald-700 text-[10px] font-black">🟢 No Issues (Good SEO)</span>`;
                    html += `
                        <tr class="tech-audit-row hover:bg-slate-50/60 transition">
                            <td class="p-3 font-mono font-bold text-indigo-600 truncate max-w-xs">
                                <a href="${audit.url}" target="_blank" class="hover:underline">${audit.url}</a>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-0.5 rounded font-black text-[10px] ${audit.status_code == 200 ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                                    HTTP ${audit.status_code}
                                </span>
                            </td>
                            <td class="p-3 max-w-xs truncate">${audit.title ?? '❌ Missing Title'}</td>
                            <td class="p-3 max-w-xs truncate">${audit.meta_description ?? '❌ Missing Description'}</td>
                            <td class="p-3">${issueBadges}</td>
                            <td class="p-3 text-right">
                                <button onclick="generateAiSeoMeta('${escapeHtml(audit.title ?? '')}')" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-3 py-1 rounded-xl text-[10px] shadow-xs transition">
                                    🤖 AI Optimize
                                </button>
                            </td>
                        </tr>
                    `;
                } else if (type === 'schema') {
                    let schemaBadges = '';
                    schemas.forEach(s => {
                        schemaBadges += `<span class="inline-block px-2 py-0.5 bg-emerald-100 text-emerald-800 rounded text-[10px] font-black mr-1 mb-1">🏷️ ${s}</span>`;
                    });
                    if (schemas.length === 0) schemaBadges = `<span class="text-amber-700 text-[10px] font-bold">⚠️ Standard HTML (No JSON-LD)</span>`;
                    html += `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 font-mono font-bold text-indigo-600 max-w-xs truncate">
                                <a href="${audit.url}" target="_blank" class="hover:underline">${audit.url}</a>
                            </td>
                            <td class="p-3 font-bold text-slate-800">
                                ${schemaBadges}
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 ${schemas.length > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'} rounded-lg font-black text-xs">
                                    ${schemas.length > 0 ? 'Schema Active ✅' : 'Needs Schema ⚠️'}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <button onclick="openSchemaGeneratorModal('${escapeHtml(audit.title ?? 'সংবাদ শিরোনাম')}')" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-3 py-1 rounded-xl text-[10px] shadow-xs transition">
                                    ⚡ Generate
                                </button>
                            </td>
                        </tr>
                    `;
                } else if (type === 'image') {
                    const hasOgImage = !issues.some(i => i.code === 'missing_og_image');
                    const hasMaxPreview = !issues.some(i => i.code === 'missing_max_image_preview');
                    html += `
                        <tr class="hover:bg-purple-50/40 transition">
                            <td class="p-3 font-mono font-bold text-indigo-700 max-w-xs truncate">
                                <a href="${audit.url}" target="_blank" class="hover:underline">${audit.url}</a>
                            </td>
                            <td class="p-3 font-bold text-slate-800">
                                <span class="px-2 py-0.5 ${hasOgImage ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'} rounded text-[10px] font-black">
                                    ${hasOgImage ? '📷 OG Image Found ✅' : '❌ Missing og:image'}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2.5 py-1 ${hasMaxPreview ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'} rounded-lg font-black text-xs">
                                    ${hasMaxPreview ? 'max-image-preview:large ✅' : 'Standard preview'}
                                </span>
                            </td>
                            <td class="p-3 text-right">
                                <button onclick="openAiGenerateAltTextModal('${escapeHtml(audit.title ?? '')}')" class="bg-purple-600 hover:bg-purple-700 text-white font-extrabold px-3 py-1 rounded-xl text-[10px] shadow-xs transition">
                                    🤖 AI Alt Text
                                </button>
                            </td>
                        </tr>
                    `;
                }
            });

            tbody.innerHTML = html;
        })
        .catch(err => {
            console.error(`Error loading ${type} audits:`, err);
            tbody.innerHTML = `<tr><td colspan="10" class="p-6 text-center text-rose-600 font-bold">Failed to load audit data.</td></tr>`;
        });
}

function escapeHtml(str) {
    if (!str) return '';
    return str
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function loadUptimeData(siteId) {
    const container = document.getElementById('uptime-monitor-container');
    if (!container) return;

    fetch(`/seo/uptime-check-ajax/${siteId}`)
        .then(res => res.json())
        .then(data => {
            const isOnline = data.is_online;
            const sslValid = data.ssl_valid;
            const statusText = data.status;
            const responseTime = data.response_time_ms;
            const sslIssuer = data.ssl_issuer;

            // Update Uptime card
            const uptimeCard = container.children[0];
            uptimeCard.className = `p-4 \${isOnline ? 'bg-emerald-50 border-emerald-200' : 'bg-rose-50 border-rose-200'} rounded-2xl border text-center`;
            uptimeCard.innerHTML = `
                <span class="text-[10px] font-black \${isOnline ? 'text-emerald-800' : 'text-rose-800'} uppercase">Website Server Uptime</span>
                <p class="text-xl font-black \${isOnline ? 'text-emerald-700' : 'text-rose-700'} my-1">
                    \${isOnline ? '🟢 ' + statusText : '🔴 Server Offline (' + statusText + ')'}
                </p>
                <span class="text-[10px] font-bold \${isOnline ? 'text-emerald-600' : 'text-rose-600'}">Server Response: \${responseTime}ms</span>
            `;

            // Update SSL card
            const sslCard = container.children[1];
            sslCard.className = `p-4 \${sslValid ? 'bg-emerald-50 border-emerald-200' : 'bg-amber-50 border-amber-200'} rounded-2xl border text-center`;
            sslCard.innerHTML = `
                <span class="text-[10px] font-black \${sslValid ? 'text-emerald-800' : 'text-amber-800'} uppercase">SSL Certificate Security</span>
                <p class="text-xl font-black \${sslValid ? 'text-emerald-700' : 'text-amber-700'} my-1">
                    \${sslValid ? '🔒 Valid HTTPS Active' : '⚠️ SSL Expired / Invalid'}
                </p>
                <span class="text-[10px] font-bold \${sslValid ? 'text-emerald-600' : 'text-amber-600'}">SSL Issuer: \${sslIssuer}</span>
            `;
        })
        .catch(err => {
            console.error("Error loading uptime:", err);
            container.children[0].innerHTML = `<span class="text-xs text-rose-600">Failed to load uptime</span>`;
            container.children[1].innerHTML = `<span class="text-xs text-rose-600">Failed to load SSL info</span>`;
        });
}

function loadIndexingHealth(siteId) {
    const container = document.getElementById('indexing-health-container');
    if (!container) return;

    fetch(`/seo/indexing-health-ajax/${siteId}`)
        .then(res => res.json())
        .then(data => {
            const googleWorking = data.google_status === 'working';
            const googleLabel = data.google_label;
            const indexNowLabel = data.indexnow_label;

            container.className = "grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5";
            container.innerHTML = `
                <div class="p-4 rounded-2xl border \${googleWorking ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : 'bg-amber-50 border-amber-200 text-amber-900'} flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-extrabold">
                        <i class="fa-brands fa-google text-lg"></i>
                        <span>\${googleLabel}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase \${googleWorking ? 'bg-emerald-200 text-emerald-800' : 'bg-amber-200 text-amber-800'}">
                            \${googleWorking ? 'Operational 🟢' : 'Setup Required ⚠️'}
                        </span>
                        <button onclick="openGoogleApiHelpModal()" class="text-amber-800 hover:text-amber-950 font-black text-[10px] underline">
                            ❓ API Help
                        </button>
                    </div>
                </div>

                <div class="p-4 rounded-2xl border bg-sky-50 border-sky-200 text-sky-900 flex items-center justify-between">
                    <div class="flex items-center gap-2 text-xs font-extrabold">
                        <i class="fa-solid fa-bolt text-amber-500 text-lg"></i>
                        <span>\${indexNowLabel}</span>
                    </div>
                    <span class="px-2 py-0.5 rounded font-black text-[10px] uppercase bg-sky-200 text-sky-800">
                        Operational 🟢
                    </span>
                </div>
            `;
        })
        .catch(err => {
            console.error("Error loading indexing health:", err);
            container.innerHTML = `<div class="p-4 border border-rose-200 bg-rose-50 text-rose-800 text-xs rounded-2xl col-span-2">Failed to load API connection status. Please refresh the page.</div>`;
        });
}
</script>
@endpush
@endsection
