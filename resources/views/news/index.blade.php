@extends('layouts.app')

@section('content')
{{-- 🔥 Latest News ID for Polling --}}
<meta name="latest-news-id" content="{{ $newsItems->first()->id ?? 0 }}">

{{-- 🔥 Floating Alert for New News --}}
<div id="new-news-alert" class="hidden fixed top-24 left-1/2 transform -translate-x-1/2 z-[100] cursor-pointer">
    <button onclick="window.location.reload()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-6 rounded-full shadow-[0_10px_25px_-5px_rgba(79,70,229,0.5)] border-2 border-white flex items-center gap-2 transition-all animate-bounce">
        🔥 <span id="new-news-count">0</span>টি নতুন খবর এসেছে! দেখতে ক্লিক করুন 🔄
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

    /* অন্যান্য স্টাইল অপরিবর্তিত রাখা হয়েছে */
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
    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white font-bangla flex items-center gap-3 tracking-tight">
        📰 আজকের তাজা খবর (Raw News)
        <span class="bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 text-xs px-3 py-1 rounded-full border border-indigo-200/80 dark:border-indigo-800/60 font-bold shadow-sm">{{ $newsItems->total() }}টি খবর</span>
    </h2>
    <div class="flex items-center gap-3 w-full sm:w-auto justify-between sm:justify-end">
        <div id="loadingIndicator" class="hidden items-center gap-2 text-indigo-700 dark:text-indigo-300 text-xs sm:text-sm font-bold bg-indigo-50 dark:bg-indigo-950/60 px-3.5 py-2 rounded-xl border border-indigo-200/80 dark:border-indigo-800/60 animate-pulse">
            <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Scraping...
        </div>
        <button onclick="window.location.reload()" class="bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-400 px-4 py-2.5 rounded-xl text-sm font-bold flex items-center gap-2 shadow-sm transition-all cursor-pointer">
            🔄 Refresh
        </button>
    </div>
</div>

@if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-950/60 border-l-4 border-emerald-500 text-emerald-800 dark:text-emerald-300 p-4 mb-6 rounded-xl shadow-sm text-sm font-bold">{{ session('success') }}</div>
@endif

{{-- ⚡ No-Reload Category Filter & Live Search Toolbar --}}
@php
    $sources = $newsItems->pluck('website.name')->filter()->unique()->values();
@endphp
<div class="bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-3xl p-4 sm:p-5 mb-6 shadow-sm space-y-3.5">
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        {{-- Live Search Input --}}
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input type="text" id="liveSearchInput" oninput="filterNewsCards()" placeholder="খবরের শিরোনাম বা কিওয়ার্ড দিয়ে তাৎক্ষণিক খুঁজুন (Instant Search)..." 
                class="w-full pl-10 pr-10 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 placeholder-slate-400 text-sm font-semibold focus:bg-white dark:focus:bg-slate-900 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
            <button type="button" id="clearSearchBtn" onclick="clearLiveSearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                <i class="fa-solid fa-circle-xmark text-base"></i>
            </button>
        </div>

        {{-- Visible Counter --}}
        <div class="flex items-center justify-between md:justify-end gap-2 text-xs font-bold text-slate-500 dark:text-slate-400 shrink-0">
            <span class="bg-slate-100 dark:bg-slate-800 px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700">
                প্রদর্শিত: <span id="visibleCount" class="text-indigo-600 dark:text-indigo-400 font-black">{{ count($newsItems) }}</span> / {{ $newsItems->total() }}টি খবর
            </span>
        </div>
    </div>

    {{-- Source Quick Filter Chips --}}
    @php
        $dupCount = $newsItems->filter(fn($i) => !empty($i->is_duplicate))->count();
    @endphp
    <div class="flex flex-wrap items-center justify-between gap-3 pt-1 border-t border-slate-100 dark:border-slate-800">
        <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs font-bold" id="sourceFilterChips">
            <span class="text-slate-400 text-[11px] font-extrabold uppercase shrink-0 mr-1"><i class="fa-solid fa-filter text-indigo-500"></i> ফিল্টার:</span>
            <button type="button" onclick="selectSourceFilter('all', this)" class="source-chip active-chip px-3.5 py-1.5 rounded-xl bg-indigo-600 text-white font-extrabold shadow-sm transition-all whitespace-nowrap cursor-pointer">
                🌟 সব খবর
            </button>
            @if($dupCount > 0)
            <button type="button" onclick="selectSourceFilter('__duplicate__', this)" class="source-chip px-3.5 py-1.5 rounded-xl bg-amber-100 hover:bg-amber-200 dark:bg-amber-950/70 dark:hover:bg-amber-900 text-amber-800 dark:text-amber-300 font-extrabold border border-amber-300 dark:border-amber-800 transition-all whitespace-nowrap cursor-pointer">
                ⚠️ ডুপ্লিকেট/সদৃশ খবর ({{ $dupCount }})
            </button>
            @endif
            @foreach($sources as $src)
                <button type="button" onclick="selectSourceFilter('{{ addslashes($src) }}', this)" class="source-chip px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold border border-slate-200 dark:border-slate-700 transition-all whitespace-nowrap cursor-pointer">
                    {{ $src }}
                </button>
            @endforeach
        </div>

        {{-- Select All Checkbox --}}
        <div class="flex items-center gap-2 shrink-0">
            <label class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-300 cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-700 transition select-none">
                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAllCards(this)" class="w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
                <span>সব নির্বাচন (Select All)</span>
            </label>
        </div>
    </div>
</div>

{{-- ⚡ Floating Bulk Action Bar --}}
<div id="floatingBulkBar" class="hidden fixed bottom-6 left-1/2 transform -translate-x-1/2 z-[100] bg-slate-900/95 dark:bg-slate-850/95 text-white backdrop-blur-xl border border-white/20 px-5 py-3 rounded-full shadow-[0_20px_50px_rgba(0,0,0,0.5)] flex items-center gap-3.5">
    <div class="flex items-center gap-2 pr-3 border-r border-slate-700 text-xs font-black">
        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
        <span>সিলেক্টেড: <span id="selectedCountBadge" class="text-indigo-400 font-black">0</span>টি</span>
    </div>
    <button type="button" onclick="executeBulkAiRewrite()" id="bulkAiRewriteBtn" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-full text-xs font-black shadow-md flex items-center gap-1.5 transition cursor-pointer">
        <i class="fa-solid fa-wand-magic-sparkles text-xs"></i> <span>একসাথে AI রিরাইট</span>
    </button>
    <button type="button" onclick="executeBulkDelete()" id="bulkDeleteBtn" class="px-3.5 py-2 bg-rose-600/90 hover:bg-rose-600 text-white rounded-full text-xs font-bold transition flex items-center gap-1 cursor-pointer">
        <i class="fa-solid fa-trash text-xs"></i> <span>মুছুন</span>
    </button>
    <button type="button" onclick="clearCardSelection()" class="text-slate-400 hover:text-white text-xs font-bold px-2 py-1 transition cursor-pointer">
        ✕ বাতিল
    </button>
</div>

{{-- No Matches Found Placeholder --}}
<div id="noNewsMatchAlert" class="hidden bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-700 rounded-3xl p-12 text-center my-6">
    <div class="w-16 h-16 mx-auto mb-4 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center text-2xl">
        <i class="fa-solid fa-magnifying-glass"></i>
    </div>
    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-1">কোন খবর পাওয়া যায়নি!</h3>
    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">আপনার অনুসন্ধানের সাথে মিলে এমন কোনো খবর তালিকায় নেই।</p>
    <button type="button" onclick="resetAllFilters()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-sm transition cursor-pointer">
        🔄 ফিল্টার রিসেট করুন
    </button>
</div>

{{-- Main Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6" id="mainNewsGrid">
    @foreach($newsItems as $item)
    <div id="news-card-{{ $item->id }}" data-card-title="{{ e(mb_strtolower($item->title)) }}" data-card-source="{{ e(mb_strtolower($item->website->name ?? '')) }}" data-card-duplicate="{{ !empty($item->is_duplicate) ? 'true' : 'false' }}" class="news-feed-card group relative luxe-card rounded-3xl transition-all duration-300 hover:-translate-y-1 flex flex-col h-full overflow-hidden border border-slate-200/90 shadow-sm hover:shadow-xl">
        
        {{-- Selection Checkbox --}}
        <div class="absolute top-3.5 left-3.5 z-20">
            <label class="flex items-center justify-center w-7 h-7 rounded-xl bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-300 dark:border-slate-700 shadow-md cursor-pointer hover:scale-110 transition">
                <input type="checkbox" value="{{ $item->id }}" onchange="handleCardSelection(this)" class="card-select-cb w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500 cursor-pointer">
            </label>
        </div>

        {{-- Status Badge --}}
        <div class="absolute top-3.5 right-3.5 z-20 flex flex-col items-end gap-1.5">
            @if(!empty($item->is_duplicate) && !empty($item->duplicate_info))
                <div class="bg-amber-500 text-white text-[9px] font-extrabold px-2.5 py-1 rounded-lg flex items-center gap-1 shadow-md border border-amber-300/40" title="অন্য উৎস ({{ $item->duplicate_info['matched_source'] }})-এর সাথে {{ $item->duplicate_info['similarity'] }}% সাদৃশ্য">
                    <i class="fa-solid fa-clone"></i> ডুপ্লিকেট ({{ $item->duplicate_info['similarity'] }}%)
                </div>
            @endif

            @if($item->status == 'processing')
                <div class="bg-amber-500 text-white text-[10px] font-extrabold px-3 py-1.5 rounded-lg flex items-center gap-1.5 animate-pulse shadow-md">
                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    AI WRITING...
                </div>
            @else
                <div class="bg-slate-900 text-white text-[10px] font-extrabold px-3 py-1.5 rounded-lg flex items-center gap-1.5 border border-white/20 shadow-md">
                    RAW NEWS
                </div>
            @endif
        </div>

        {{-- Image --}}
        <div class="h-48 overflow-hidden relative bg-slate-100">
            @if($item->thumbnail_url)
                <img src="{{ $item->thumbnail_url }}" alt="Thumb" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent opacity-80"></div>
            @else
                <div class="flex items-center justify-center h-full bg-slate-50 text-slate-300 text-xs font-bold uppercase">No Image</div>
            @endif
            <div class="absolute bottom-3 left-3 z-10">
                <span class="bg-white/95 text-[10px] font-extrabold px-3 py-1 rounded-full text-slate-800 shadow-lg flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                    {{ $item->website->name ?? 'UNKNOWN' }}
                </span>
            </div>
        </div>
        
        {{-- Body --}}
        <div class="p-5 flex flex-col flex-1 bg-white relative">
            <h3 class="text-[17px] font-bold leading-snug mb-3 text-slate-800 font-bangla line-clamp-2 group-hover:text-indigo-600">
                {{ $item->title }}
            </h3>
            
            <div class="text-[11px] font-medium text-slate-400 flex items-center justify-between mb-6">
                <span class="bg-slate-50 px-2.5 py-1 rounded-md border border-slate-100">
                    {{ $item->published_at ? \Carbon\Carbon::parse($item->published_at)->diffForHumans() : 'Just now' }}
                </span>
                <a href="{{ $item->original_link }}" target="_blank" class="text-slate-400 hover:text-indigo-500 flex items-center gap-1">
                    SOURCE <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            </div>

            {{-- Action Buttons (Modified Logic) --}}
            <div class="mt-auto pt-4 border-t border-dashed border-slate-100 space-y-3">
                
                {{-- 1. Studio Button (Always Available) --}}
                <a href="{{ route('news.studio', $item->id) }}" class="w-full bg-slate-50 hover:bg-white text-slate-600 border border-slate-200 hover:border-indigo-300 py-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 group/studio">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    OPEN STUDIO
                </a>

                @if($item->status == 'processing')
                    {{-- Processing State --}}
                    <button disabled class="w-full bg-slate-100 text-slate-400 py-2.5 rounded-xl border border-slate-200 text-xs font-bold flex items-center justify-center gap-2 cursor-not-allowed">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        AI is Writing...
                    </button>
                @else
                    {{-- Default State: AI & Edit Only (No Publish) --}}
                    <form action="{{ route('news.process-ai', $item->id) }}" method="POST" class="col-span-2">
                        @csrf
                        <div class="grid grid-cols-2 gap-3">
                            {{-- AI Button --}}
                            <button onclick="startAiProcess({{ $item->id }}, this)" class="bg-slate-900 hover:bg-slate-800 text-white py-2.5 rounded-xl font-bold text-xs shadow-lg flex items-center justify-center gap-1.5 border border-slate-700">
                                <svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                Rewrite
                            </button>

                            {{-- Manual Edit Button --}}
                            <input type="hidden" id="raw-title-{{ $item->id }}" value="{{ $item->title }}">
                            <input type="hidden" id="raw-image-{{ $item->id }}" value="{{ $item->thumbnail_url }}">
                            <div id="raw-content-{{ $item->id }}" style="display:none;">{!! $item->content !!}</div>

                            <button onclick="openManualModal({{ $item->id }})" type="button" class="bg-white border-2 border-slate-200 text-slate-700 hover:border-indigo-600 hover:text-indigo-600 py-2.5 rounded-xl font-bold text-xs transition flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                EDIT
                            </button>
                        </div>
                    </form>
                @endif

                {{-- Delete --}}
                <div class="flex justify-end pt-2">
                    <form action="{{ route('news.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this news?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="opacity-40 hover:opacity-100 text-rose-500 text-[10px] font-bold uppercase tracking-wider flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Delete
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
        
        // প্রতি ১০ সেকেন্ডে চেক করবে নতুন নিউজ এসেছে কি না
        setInterval(() => {
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
        }, 10000);
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
            alert("অনুগ্রহ করে অন্তত একটি খবর সিলেক্ট করুন!");
            return;
        }

        if (!confirm(`আপনি কি নিশ্চিত যে সিলেক্ট করা ${selectedIds.length}টি খবর এআই দিয়ে একসাথে রিরাইট করতে চান?`)) {
            return;
        }

        const btn = document.getElementById('bulkAiRewriteBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>প্রসেসিং হচ্ছে...</span>`;

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
                alert(data.message || "সফলভাবে AI রিরাইট কিউতে পাঠানো হয়েছে!");
                window.location.reload();
            } else {
                alert(data.message || "কোনো ত্রুটি হয়েছে!");
            }
        })
        .catch(err => {
            console.error("Bulk AI Error:", err);
            alert("সার্ভারে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    function executeBulkDelete() {
        const selectedIds = getSelectedCardIds();
        if (selectedIds.length === 0) {
            alert("অনুগ্রহ করে অন্তত একটি খবর সিলেক্ট করুন!");
            return;
        }

        if (!confirm(`⚠️ আপনি কি নিশ্চিত যে সিলেক্ট করা ${selectedIds.length}টি খবর ডিলিট করতে চান? এই কাজ ফিরিয়ে নেওয়া যাবে না!`)) {
            return;
        }

        const btn = document.getElementById('bulkDeleteBtn');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>মুছে ফেলা হচ্ছে...</span>`;

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
                alert(data.message || "সফলভাবে মুছে ফেলা হয়েছে!");
            } else {
                alert(data.message || "কোনো ত্রুটি হয়েছে!");
            }
        })
        .catch(err => {
            console.error("Bulk Delete Error:", err);
            alert("সার্ভারে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।");
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }
</script>
@endsection