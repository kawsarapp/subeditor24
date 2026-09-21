@extends('layouts.app')

@section('content')
{{-- 🔥 Latest News ID for Polling --}}
<meta name="latest-news-id" content="{{ $newsItems->first()->id ?? 0 }}">

{{-- 🔥 Floating Alert for New News --}}
<div id="new-news-alert" class="hidden fixed top-24 left-1/2 transform -translate-x-1/2 z-[100] cursor-pointer">
    <button onclick="window.location.reload()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-full shadow-[0_10px_25px_-5px_rgba(79,70,229,0.5)] border-2 border-white flex items-center gap-2 transition-all animate-bounce">
        🔥 <span id="new-news-count">0</span> new articles available! Click to refresh 🔄
    </button>
</div>

{{-- 🔥 TinyMCE Script --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<style>
    /* SolaimanLipi Font Import */
    @import url('https://fonts.maateen.me/solaiman-lipi/font.css');

    /* Font Family Update */
    .font-bangla { 
        font-family: 'SolaimanLipi', Arial, sans-serif; 
    }

    @keyframes shimmer { 
        0% { background-position: -200% 0; } 
        100% { background-position: 200% 0; } 
    }
    
    .skeleton { 
        background: linear-gradient(90deg, #f3f4f6 25%, #e5e7eb 50%, #f3f4f6 75%); 
        background-size: 200% 100%; 
        animation: shimmer 1.5s infinite; 
    }
    
    .tox-tinymce-aux { 
        z-index: 99999 !important; 
    }
</style>

{{-- Header --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8 gap-4">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
        </div>
        <div>
            <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                <span>Latest News</span>
                <span class="bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs px-2.5 py-0.5 rounded-md border border-indigo-200/70 dark:border-indigo-800/60 font-semibold shadow-sm">{{ $newsItems->total() }} Articles</span>
            </h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Aggregated live news feed & editor workspace</p>
        </div>
    </div>
    <div class="flex items-center gap-2.5 w-full sm:w-auto justify-between sm:justify-end">
        <div id="loadingIndicator" class="hidden items-center gap-2 text-indigo-700 dark:text-indigo-300 text-xs sm:text-sm font-semibold bg-indigo-50 dark:bg-indigo-950/60 px-3 py-1.5 rounded-lg border border-indigo-200/80 dark:border-indigo-800/60 animate-pulse">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>Scraping...</span>
        </div>
        <button onclick="window.location.reload()" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 dark:hover:border-indigo-600 text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 px-3.5 py-2 rounded-lg text-xs font-bold flex items-center gap-2 shadow-sm transition-all cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
            <span>Refresh</span>
        </button>
    </div>
</div>

@if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 p-4 mb-6 rounded-xl shadow-sm text-sm font-semibold">{{ session('success') }}</div>
@endif

{{-- ⚡ No-Reload Category Filter & Live Search Toolbar --}}
@php
    $sources = $newsItems->pluck('website.name')->filter()->unique()->values();
@endphp
<div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-4 sm:p-5 mb-6 shadow-sm space-y-3.5">
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        {{-- Live Search Input --}}
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </span>
            <input type="text" id="liveSearchInput" oninput="filterNewsCards()" placeholder="Search news by headline or keyword..." 
                class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/80 text-slate-800 dark:text-slate-100 placeholder-slate-400 text-sm font-medium focus:bg-white dark:focus:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            <button type="button" id="clearSearchBtn" onclick="clearLiveSearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        {{-- Visible Counter --}}
        <div class="flex items-center justify-between md:justify-end gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 shrink-0">
            <span class="bg-slate-100/80 dark:bg-slate-800/80 px-3 py-2 rounded-lg border border-slate-200/80 dark:border-slate-700">
                Showing: <span id="visibleCount" class="text-indigo-600 dark:text-indigo-400 font-bold">{{ count($newsItems) }}</span> / {{ $newsItems->total() }} articles
            </span>
        </div>
    </div>

    {{-- Source Quick Filter Chips --}}
    @php
        $dupCount = $newsItems->filter(fn($i) => !empty($i->is_duplicate))->count();
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs" id="sourceFilterChips">
            <span class="text-slate-400 text-[11px] font-bold uppercase shrink-0 mr-1 flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter:
            </span>
            <button type="button" onclick="selectSourceFilter('all', this)" class="source-chip active-chip px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold shadow-sm transition-all whitespace-nowrap cursor-pointer">
                All News
            </button>
            @if($dupCount > 0)
            <button type="button" onclick="selectSourceFilter('__duplicate__', this)" class="source-chip px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/70 dark:hover:bg-amber-900 text-amber-800 dark:text-amber-300 font-bold border border-amber-200 dark:border-amber-800 transition-all whitespace-nowrap cursor-pointer">
                Duplicates ({{ $dupCount }})
            </button>
            @endif
            @foreach($sources as $src)
                @php
                    // Clean human-friendly display name
                    $displaySrc = preg_replace('/^https?:\/\//i', '', $src);
                    $displaySrc = preg_replace('/^www\./i', '', $displaySrc);
                @endphp
                <button type="button" onclick="selectSourceFilter('{{ addslashes($src) }}', this)" class="source-chip px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-semibold border border-slate-200 dark:border-slate-700 transition-all whitespace-nowrap cursor-pointer" title="{{ $src }}">
                    {{ $displaySrc }}
                </button>
            @endforeach
        </div>

        {{-- Select All Checkbox --}}
        <div class="flex items-center gap-2 shrink-0">
            <label class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition select-none">
                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllCards(this)" class="w-3.5 h-3.5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                <span>Select All</span>
            </label>
        </div>
    </div>
</div>

{{-- ⚡ Floating Bulk Action Bar --}}
<div id="floatingBulkBar" class="hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 z-[100] bg-slate-900/95 dark:bg-slate-850/95 text-white backdrop-blur-xl border border-white/20 px-5 py-2.5 rounded-2xl shadow-[0_20px_50px_rgba(0,0,0,0.4)] flex items-center gap-3.5">
    <div class="flex items-center gap-2 pr-3 border-r border-slate-700 text-xs font-bold">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span>Selected: <span id="selectedCountBadge" class="text-indigo-400 font-bold">0</span></span>
    </div>
    <button type="button" onclick="executeBulkAiRewrite()" id="bulkAiRewriteBtn" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold shadow-sm flex items-center gap-1.5 transition cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        <span>Bulk AI Rewrite</span>
    </button>
    <button type="button" onclick="executeBulkDelete()" id="bulkDeleteBtn" class="px-3 py-1.5 bg-rose-600/90 hover:bg-rose-600 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1 cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        <span>Delete</span>
    </button>
    <button type="button" onclick="clearCardSelection()" class="text-slate-400 hover:text-white text-xs font-semibold px-2 py-1 transition cursor-pointer">
        ✕ Cancel
    </button>
</div>

{{-- No Matches Found Placeholder --}}
<div id="noNewsMatchAlert" class="hidden bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-12 text-center my-6">
    <div class="w-14 h-14 mx-auto mb-3 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl flex items-center justify-center text-xl">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>
    <h3 class="text-base font-bold text-slate-800 dark:text-slate-200 mb-1">No news articles found!</h3>
    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">No news matches your current search or filter criteria.</p>
    <button type="button" onclick="resetAllFilters()" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow-sm transition cursor-pointer">
        Reset Filters
    </button>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5" id="mainNewsGrid">
    @foreach($newsItems as $item)
    <div id="news-card-{{ $item->id }}" data-card-title="{{ e(mb_strtolower($item->title)) }}" data-card-source="{{ e(mb_strtolower($item->website->name ?? '')) }}" data-card-duplicate="{{ !empty($item->is_duplicate) ? 'true' : 'false' }}" class="news-feed-card group relative bg-white dark:bg-slate-900 rounded-2xl transition-all duration-200 hover:-translate-y-0.5 flex flex-col h-full overflow-hidden border border-slate-200/90 dark:border-slate-800 shadow-[0_2px_8px_rgba(15,23,42,0.04)] hover:shadow-lg">
        
        {{-- Selection Checkbox --}}
        <div class="absolute top-3 left-3 z-20">
            <label class="flex items-center justify-center w-6 h-6 rounded-lg bg-white/95 dark:bg-slate-900/95 backdrop-blur border border-slate-300 dark:border-slate-700 shadow-sm cursor-pointer hover:scale-105 transition">
                <input type="checkbox" value="{{ $item->id }}" onchange="handleCardSelection(this)" class="card-select-cb w-3.5 h-3.5 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
            </label>
        </div>

        {{-- Status Badge --}}
        <div class="absolute top-3 right-3 z-20 flex flex-col items-end gap-1">
            @if(!empty($item->is_duplicate) && !empty($item->duplicate_info))
                <div class="bg-amber-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-md flex items-center gap-1 shadow-sm border border-amber-300/40" title="{{ $item->duplicate_info['similarity'] }}% similarity with {{ $item->duplicate_info['matched_source'] }}">
                    <span>Duplicate ({{ $item->duplicate_info['similarity'] }}%)</span>
                </div>
            @endif

            @if($item->status == 'processing')
                <div class="bg-amber-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-md flex items-center gap-1.5 animate-pulse shadow-sm">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>AI Writing...</span>
                </div>
            @else
                <div class="bg-slate-900/80 dark:bg-slate-950/80 backdrop-blur-md text-white/90 text-[10px] font-bold px-2.5 py-0.5 rounded-md border border-white/15 shadow-sm">
                    RAW NEWS
                </div>
            @endif
        </div>

        {{-- Image --}}
        <div class="h-44 overflow-hidden relative bg-slate-100 dark:bg-slate-800">
            @if($item->thumbnail_url)
                <img src="{{ $item->thumbnail_url }}" alt="Thumb" loading="lazy" decoding="async" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-transparent to-transparent opacity-80"></div>
            @else
                <div class="flex items-center justify-center h-full bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600 text-xs font-semibold uppercase">No Image</div>
            @endif
            <div class="absolute bottom-2.5 left-2.5 z-10">
                <span class="bg-slate-900/80 backdrop-blur-md text-[10px] font-bold px-2.5 py-0.5 rounded-md text-slate-200 border border-white/10 shadow-sm flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    {{ $item->website->name ?? 'Unknown' }}
                </span>
            </div>
        </div>
        
        {{-- Body --}}
        <div class="p-4 flex flex-col flex-1 bg-white dark:bg-slate-900 relative">
            <h3 class="text-[15px] font-bold leading-snug mb-2.5 text-slate-800 dark:text-slate-100 font-bangla line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                {{ $item->title }}
            </h3>
            
            <div class="text-[11px] font-medium text-slate-400 dark:text-slate-500 flex items-center justify-between mb-4">
                <span class="flex items-center gap-1">
                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    {{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->diffForHumans() : 'Just now' }}
                </span>
                <a href="{{ $item->original_link }}" target="_blank" class="text-slate-400 hover:text-indigo-500 flex items-center gap-1 transition">
                    <span>Source</span>
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-auto pt-3 border-t border-slate-100 dark:border-slate-800 space-y-2">
                
                {{-- 1. Studio Button --}}
                <a href="{{ route('news.studio', $item->id) }}" class="w-full bg-slate-50 hover:bg-indigo-50/60 dark:bg-slate-800/80 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200/90 dark:border-slate-700 hover:border-indigo-200 py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Open Studio</span>
                </a>

                @if($item->status == 'processing')
                    {{-- Processing State --}}
                    <button disabled class="w-full bg-slate-100 dark:bg-slate-800 text-slate-400 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span>AI Writing...</span>
                    </button>
                @else
                    {{-- Default State: AI & Edit --}}
                    <form action="{{ route('news.process-ai', $item->id) }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-2 gap-2">
                            {{-- AI Button --}}
                            <button onclick="startAiProcess({{ $item->id }}, this)" class="bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500 text-white py-2 rounded-xl font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition">
                                <svg class="w-3.5 h-3.5 text-purple-300 dark:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                <span>Rewrite</span>
                            </button>

                            {{-- Manual Edit Button --}}
                            <input type="hidden" id="raw-title-{{ $item->id }}" value="{{ $item->title }}">
                            <input type="hidden" id="raw-image-{{ $item->id }}" value="{{ $item->thumbnail_url }}">
                            <div id="raw-content-{{ $item->id }}" style="display:none;">{!! $item->content !!}</div>

                            <button onclick="openManualModal({{ $item->id }})" type="button" class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:border-indigo-400 hover:text-indigo-600 dark:hover:text-indigo-400 py-2 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span>Edit</span>
                            </button>
                        </div>
                    </form>
                @endif

                {{-- Delete Link --}}
                <div class="flex justify-end pt-1">
                    <form action="{{ route('news.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this news?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-500 text-[10px] font-semibold flex items-center gap-1 transition">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            <span>Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="mt-8">{{ $newsItems->links() }}</div>

{{-- MANUAL EDIT MODAL --}}
<div id="manualEditModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">
        <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
            <h3 class="text-lg font-bold text-gray-800">📝 Edit & Save to Drafts</h3>
            <button onclick="closeManualModal()" class="text-gray-500 hover:text-red-500 text-2xl">&times;</button>
        </div>
        
        <div class="p-6 overflow-y-auto flex-1">
            <input type="hidden" id="manualNewsId">
            
            <div class="mb-5 bg-white p-3 rounded-lg border border-gray-200 shadow-sm">
                <label class="block text-sm font-bold text-gray-700 mb-2">Image</label>
                <div class="flex gap-4 items-start">
                    <div class="w-24 h-24 flex-shrink-0 bg-gray-100 rounded overflow-hidden border">
                        <img id="manualPreviewImg" src="" class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        {{-- Disabled file input for now, just informative --}}
                        <p class="text-xs text-gray-500 mb-2">Images can be changed in Drafts/Studio.</p>
                        <input type="url" id="manualImageUrl" class="w-full border border-gray-300 rounded p-2 text-xs" readonly>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Title</label>
                <input type="text" id="manualTitle" class="w-full border border-gray-300 rounded-lg p-3 font-bold text-gray-800">
            </div>

            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-700 mb-2">Content</label>
                <textarea id="manualContent" rows="15" class="w-full border border-gray-300 rounded-lg p-3 text-sm"></textarea>
            </div>
        </div>

        <div class="bg-white px-6 py-4 border-t flex justify-end gap-3">
            <button onclick="closeManualModal()" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg font-bold hover:bg-gray-200">Cancel</button>
            {{-- 🔥 SAVE TO DRAFT BUTTON --}}
            <button onclick="submitManualDraft()" id="btnManualPub" class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 shadow-lg flex items-center gap-2">
                💾 Save to Drafts
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('scraping')) startScrapingMonitor();
        
        // Start background polling for new news
        startNewNewsPoller();
    });

    function initTinyMCE() {
        if (tinymce.get('manualContent')) tinymce.get('manualContent').remove();
        tinymce.init({
            selector: '#manualContent',
            height: 400,
            plugins: 'link lists code wordcount',
            toolbar: 'undo redo | blocks | bold italic | bullist numlist | link | code',
            menubar: false,
            statusbar: true
        });
    }

    // --- Scraping Monitor ---
    function startScrapingMonitor() {
        showLoading(); 
        let checkCount = 0;
        const poller = setInterval(() => {
            checkCount++;
            const forceWait = checkCount <= 3 ? 'true' : 'false';
            fetch(`{{ route('news.check-scrape-status') }}?force_wait=${forceWait}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.scraping && checkCount > 3) {
                        clearInterval(poller);
                        finishLoading();
                    }
                });
        }, 2000);
    }

    function showLoading() {
        const indicator = document.getElementById('loadingIndicator');
        if(indicator) indicator.classList.remove('hidden'); indicator.classList.add('flex');
        document.getElementById('mainNewsGrid')?.classList.add('opacity-50', 'pointer-events-none');
    }

    function finishLoading() {
        window.location.href = "{{ route('news.index') }}";
    }

    // --- New News Poller (Background checker) ---
    function startNewNewsPoller() {
        const lastId = document.querySelector('meta[name="latest-news-id"]')?.content || 0;
        
        // Check every 10 seconds for new incoming news (polling pauses when tab is inactive)
        setInterval(() => {
            if (document.hidden) return;

            fetch(`{{ route('news.check-new-news') }}?last_id=${lastId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.new_count > 0) {
                        document.getElementById('new-news-count').innerText = data.new_count;
                        const alertBox = document.getElementById('new-news-alert');
                        alertBox.classList.remove('hidden');
                        alertBox.classList.add('flex');
                    }
                })
                .catch(err => console.error("Polling error:", err));
        }, 12000);
    }

    // --- Manual Edit Logic ---
    function openManualModal(id) {
        const title = document.getElementById(`raw-title-${id}`).value;
        const image = document.getElementById(`raw-image-${id}`).value;
        const content = document.getElementById(`raw-content-${id}`).innerHTML;
        
        document.getElementById('manualNewsId').value = id;
        document.getElementById('manualTitle').value = title;
        document.getElementById('manualContent').value = content;
        document.getElementById('manualPreviewImg').src = image || 'https://via.placeholder.com/150';
        document.getElementById('manualImageUrl').value = image;
        
        const modal = document.getElementById('manualEditModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            initTinyMCE();
            if(tinymce.get('manualContent')) tinymce.get('manualContent').setContent(content);
        }, 100);
    }

    function closeManualModal() {
        const modal = document.getElementById('manualEditModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        if (tinymce.get('manualContent')) tinymce.get('manualContent').remove();
    }

    function submitManualDraft() {
        const id = document.getElementById('manualNewsId').value;
        const title = document.getElementById('manualTitle').value;
        let content = tinymce.get('manualContent') ? tinymce.get('manualContent').getContent() : document.getElementById('manualContent').value;
        const btn = document.getElementById('btnManualPub');

        if(!title || !content) { alert("Title and Content required!"); return; }

        btn.disabled = true;
        btn.innerHTML = "Saving...";

        fetch(`/news/${id}/update-draft`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ title, content })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeManualModal();
                // Remove card from UI
                const card = document.getElementById(`news-card-${id}`);
                if (card) {
                    card.style.transition = "all 0.5s ease";
                    card.style.opacity = "0";
                    card.style.transform = "translateX(100px)";
                    setTimeout(() => card.remove(), 500);
                }
                alert("✅ Saved to Drafts!");
            }
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = "💾 Save to Drafts";
        });
    }

    // --- AI Logic ---
    function startAiProcess(id, btn) {
        // Form submit automatically handles the request, 
        // Controller will set status to 'processing' and redirect back.
    }

    // ==========================================================
    // ⚡ ZERO-RELOAD LIVE SEARCH & SOURCE FILTER ENGINE
    // ==========================================================
    let activeSource = 'all';

    function selectSourceFilter(source, btn) {
        activeSource = source.toLowerCase().trim();
        document.querySelectorAll('.source-chip').forEach(c => {
            c.className = 'source-chip px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-700 transition-all whitespace-nowrap cursor-pointer';
        });
        btn.className = 'source-chip active-chip px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white font-extrabold shadow-sm transition-all whitespace-nowrap cursor-pointer';
        filterNewsCards();
    }

    function clearLiveSearch() {
        const input = document.getElementById('liveSearchInput');
        if (input) {
            input.value = '';
            filterNewsCards();
            input.focus();
        }
    }

    function resetAllFilters() {
        const input = document.getElementById('liveSearchInput');
        if (input) input.value = '';
        const allBtn = document.querySelector('.source-chip');
        if (allBtn) {
            selectSourceFilter('all', allBtn);
        } else {
            activeSource = 'all';
            filterNewsCards();
        }
    }

    function filterNewsCards() {
        const searchInput = document.getElementById('liveSearchInput');
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) {
            if (query.length > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        const cards = document.querySelectorAll('.news-feed-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const title = (card.getAttribute('data-card-title') || '').toLowerCase();
            const source = (card.getAttribute('data-card-source') || '').toLowerCase();
            const isDup = card.getAttribute('data-card-duplicate') === 'true';

            const matchesQuery = query === '' || title.includes(query) || source.includes(query);
            let matchesSource = false;

            if (activeSource === 'all') {
                matchesSource = true;
            } else if (activeSource === '__duplicate__') {
                matchesSource = isDup;
            } else {
                matchesSource = (source === activeSource);
            }

            if (matchesQuery && matchesSource) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const countEl = document.getElementById('visibleCount');
        if (countEl) countEl.innerText = visibleCount;

        const emptyEl = document.getElementById('noNewsMatchAlert');
        if (emptyEl) {
            if (visibleCount === 0 && cards.length > 0) {
                emptyEl.classList.remove('hidden');
            } else {
                emptyEl.classList.add('hidden');
            }
        }
    }

    // ==========================================================
    // ⚡ BULK ACTIONS & SELECTION ENGINE
    // ==========================================================
    function getSelectedCardIds() {
        const checkedBoxes = document.querySelectorAll('.card-select-cb:checked');
        return Array.from(checkedBoxes).map(cb => cb.value);
    }

    function handleCardSelection(cb) {
        const card = cb.closest('.news-feed-card');
        if (card) {
            if (cb.checked) {
                card.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
            } else {
                card.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
            }
        }
        updateBulkBar();
    }

    function toggleSelectAllCards(masterCb) {
        const visibleCards = Array.from(document.querySelectorAll('.news-feed-card')).filter(card => card.style.display !== 'none');
        visibleCards.forEach(card => {
            const cb = card.querySelector('.card-select-cb');
            if (cb) {
                cb.checked = masterCb.checked;
                if (masterCb.checked) {
                    card.classList.add('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
                } else {
                    card.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
                }
            }
        });
        updateBulkBar();
    }

    function updateBulkBar() {
        const selectedIds = getSelectedCardIds();
        const bulkBar = document.getElementById('floatingBulkBar');
        const badge = document.getElementById('selectedCountBadge');
        const masterCb = document.getElementById('selectAllCheckbox');

        if (badge) badge.innerText = selectedIds.length;

        if (bulkBar) {
            if (selectedIds.length > 0) {
                bulkBar.classList.remove('hidden');
                bulkBar.classList.add('flex');
            } else {
                bulkBar.classList.add('hidden');
                bulkBar.classList.remove('flex');
            }
        }

        if (masterCb) {
            const allVisibleCbs = Array.from(document.querySelectorAll('.news-feed-card'))
                .filter(card => card.style.display !== 'none')
                .map(card => card.querySelector('.card-select-cb'))
                .filter(Boolean);
            masterCb.checked = allVisibleCbs.length > 0 && allVisibleCbs.every(cb => cb.checked);
        }
    }

    function clearCardSelection() {
        document.querySelectorAll('.card-select-cb').forEach(cb => {
            cb.checked = false;
            const card = cb.closest('.news-feed-card');
            if (card) card.classList.remove('ring-2', 'ring-indigo-500', 'bg-indigo-50/20');
        });
        const masterCb = document.getElementById('selectAllCheckbox');
        if (masterCb) masterCb.checked = false;
        updateBulkBar();
    }

    function executeBulkAiRewrite() {
        const selectedIds = getSelectedCardIds();
        if (selectedIds.length === 0) {
            alert("Please select at least one news item!");
            return;
        }

        if (!confirm(`Are you sure you want to AI rewrite ${selectedIds.length} selected articles?`)) {
            return;
        }

        const btn = document.getElementById('bulkAiRewriteBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Processing...</span>`;

        fetch("{{ route('news.bulk-process-ai') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert(data.message || "Successfully queued for AI Rewrite!");
                window.location.reload();
            } else {
                alert(data.message || "An error occurred!");
            }
        })
        .catch(err => {
            console.error("Bulk AI Error:", err);
            alert("A server error occurred. Please try again.");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    function executeBulkDelete() {
        const selectedIds = getSelectedCardIds();
        if (selectedIds.length === 0) {
            alert("Please select at least one news item!");
            return;
        }

        if (!confirm(`Are you sure you want to delete ${selectedIds.length} selected articles? This action cannot be undone.`)) {
            return;
        }

        const btn = document.getElementById('bulkDeleteBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Deleting...</span>`;

        fetch("{{ route('news.bulk-destroy') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                selectedIds.forEach(id => {
                    const card = document.getElementById(`news-card-${id}`);
                    if (card) {
                        card.style.transition = "all 0.4s ease";
                        card.style.opacity = "0";
                        card.style.transform = "scale(0.9)";
                        setTimeout(() => card.remove(), 400);
                    }
                });
                clearCardSelection();
                alert(data.message || "Successfully deleted!");
            } else {
                alert(data.message || "An error occurred!");
            }
        })
        .catch(err => {
            console.error("Bulk Delete Error:", err);
            alert("A server error occurred. Please try again.");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }
</script>
@endsection