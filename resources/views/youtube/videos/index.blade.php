@extends('layouts.app')

@section('title', 'YouTube Videos | AI SEO & Auto-Publisher')

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-12">

    {{-- Header & Filters --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">
                <a href="{{ route('youtube.channels.index') }}" class="hover:text-red-600 transition flex items-center gap-1">
                    <i class="fa-brands fa-youtube text-red-600"></i> YouTube Automation
                </a>
                <span>/</span>
                <span>Videos</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">Video Manager & SEO Studio</h1>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            {{-- Channel Selector Dropdown --}}
            <form action="{{ route('youtube.videos.index') }}" method="GET" class="flex items-center gap-2">
                @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
                <select name="channel_id" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 shadow-sm cursor-pointer">
                    <option value="all">📺 All Channels ({{ $channels->count() }})</option>
                    @foreach($channels as $ch)
                        <option value="{{ $ch->id }}" {{ $selectedChannelId == $ch->id ? 'selected' : '' }}>{{ $ch->channel_title }}</option>
                    @endforeach
                </select>
            </form>

            @if($selectedChannelId && $selectedChannelId !== 'all')
                <form action="{{ route('youtube.videos.sync', $selectedChannelId) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-xs transition shadow-md shadow-red-600/20 flex items-center gap-1.5">
                        <i class="fa-solid fa-arrows-rotate"></i> Sync Channel
                    </button>
                </form>
            @endif

            <a href="{{ route('youtube.channels.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-bold text-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-gear"></i> Channel Settings
            </a>
        </div>
    </div>

    {{-- Tabs & Search Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-2.5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm">
        {{-- Status Filter Tabs --}}
        <div class="flex items-center gap-1 overflow-x-auto custom-scrollbar pb-1 sm:pb-0">
            <a href="{{ route('youtube.videos.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition {{ $status === 'all' ? 'bg-red-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                All Videos
            </a>
            <a href="{{ route('youtube.videos.index', array_merge(request()->except('status', 'page'), ['status' => 'drafts'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 {{ $status === 'drafts' ? 'bg-amber-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <span class="w-2 h-2 rounded-full bg-amber-400"></span> Unlisted / Drafts
            </a>
            <a href="{{ route('youtube.videos.index', array_merge(request()->except('status', 'page'), ['status' => 'optimized'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 {{ $status === 'optimized' ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i> AI SEO Ready
            </a>
            <a href="{{ route('youtube.videos.index', array_merge(request()->except('status', 'page'), ['status' => 'published'])) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 {{ $status === 'published' ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-700' }}">
                <i class="fa-solid fa-circle-check text-[10px]"></i> Published Live
            </a>
        </div>

        {{-- Search Input --}}
        <form action="{{ route('youtube.videos.index') }}" method="GET" class="relative shrink-0 sm:w-64">
            @if(request('channel_id')) <input type="hidden" name="channel_id" value="{{ request('channel_id') }}"> @endif
            @if(request('status')) <input type="hidden" name="status" value="{{ request('status') }}"> @endif
            <input type="text" name="q" value="{{ $search }}" placeholder="Search videos..." class="w-full pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-medium focus:ring-2 focus:ring-red-500">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
        </form>
    </div>

    {{-- Videos Grid --}}
    @if($videos->isEmpty())
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 space-y-4">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-700 text-slate-400 mx-auto flex items-center justify-center text-2xl">
                <i class="fa-solid fa-video-slash"></i>
            </div>
            <div class="space-y-1">
                <h3 class="text-base font-bold text-slate-900 dark:text-white">কোনো ভিডিও পাওয়া যায়নি</h3>
                <p class="text-xs text-slate-500">ইউটিউবে ভিডিও আপলোড করার পর উপরের 'Sync' বাটনে ক্লিক করে ডেটাবেজে নিয়ে আসুন।</p>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($videos as $video)
                <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between group">
                    <div>
                        {{-- Video Thumbnail & Badges --}}
                        <div class="relative aspect-video w-full bg-slate-950 overflow-hidden">
                            <img src="{{ $video->thumbnail_url ?: asset('images/placeholder.png') }}" alt="{{ $video->ai_title ?: $video->original_title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            {{-- Privacy Status Badge --}}
                            <div class="absolute top-2.5 left-2.5">
                                @if($video->current_privacy_status === 'public')
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-emerald-600 text-white shadow-md">
                                        Live Public
                                    </span>
                                @elseif($video->current_privacy_status === 'unlisted')
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-amber-500 text-white shadow-md">
                                        Unlisted Draft
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold uppercase tracking-wider bg-slate-700 text-white shadow-md">
                                        Private
                                    </span>
                                @endif
                            </div>

                            {{-- SEO Score Badge --}}
                            @if($video->seo_score > 0)
                            <div class="absolute top-2.5 right-2.5">
                                <span class="px-2 py-0.5 rounded-lg text-[10px] font-black bg-indigo-600/90 backdrop-blur-sm text-white shadow-md flex items-center gap-1">
                                    <i class="fa-solid fa-chart-line text-[9px]"></i> {{ $video->seo_score }}/100
                                </span>
                            </div>
                            @endif

                            {{-- Channel Name Watermark --}}
                            <div class="absolute bottom-2.5 left-2.5">
                                <span class="px-2 py-0.5 rounded-md text-[9px] font-black bg-black/70 backdrop-blur-sm text-white truncate max-w-[150px] inline-block">
                                    {{ $video->channel?->channel_title }}
                                </span>
                            </div>
                        </div>

                        {{-- Card Details --}}
                        <div class="p-4 space-y-2">
                            <h3 class="font-extrabold text-slate-900 dark:text-white text-xs leading-snug line-clamp-2 group-hover:text-red-600 transition" title="{{ $video->ai_title ?: $video->original_title }}">
                                {{ $video->ai_title ?: $video->original_title }}
                            </h3>

                            @if($video->ai_title && $video->ai_title !== $video->original_title)
                                <p class="text-[10px] text-slate-400 line-clamp-1 italic">
                                    <span class="font-bold text-slate-500">Original:</span> {{ $video->original_title }}
                                </p>
                            @endif

                            {{-- AI Optimization Status Badge --}}
                            <div class="pt-1">
                                @if($video->status === 'optimized')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-circle-check"></i> AI SEO Optimized
                                    </span>
                                @elseif($video->status === 'publishing')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md animate-pulse">
                                        <i class="fa-solid fa-spinner fa-spin"></i> Publishing...
                                    </span>
                                @elseif($video->status === 'published')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-check-double"></i> Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">
                                        <i class="fa-solid fa-clock"></i> Draft Sync
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="p-3.5 bg-slate-50/80 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-700/80 flex items-center justify-between gap-2">
                        <a href="{{ $video->watch_url }}" target="_blank" class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-red-600 text-xs font-bold transition shadow-sm" title="Watch on YouTube">
                            <i class="fa-brands fa-youtube"></i>
                        </a>

                        <a href="{{ route('youtube.studio.show', $video->id) }}" class="flex-1 px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white font-extrabold text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-red-600/20">
                            <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i>
                            <span>AI SEO Studio</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="pt-4">
            {{ $videos->links() }}
        </div>
    @endif

</div>
@endsection
