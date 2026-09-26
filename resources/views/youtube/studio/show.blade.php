@extends('layouts.app')

@section('title', 'AI Video SEO Studio | ' . ($video->ai_title ?: $video->original_title))

@section('content')
<div class="space-y-6 max-w-7xl mx-auto pb-16">

    {{-- Breadcrumb & Top Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">
                <a href="{{ route('youtube.channels.index') }}" class="hover:text-red-600 transition flex items-center gap-1">
                    <i class="fa-brands fa-youtube text-red-600"></i> {{ $video->channel?->channel_title }}
                </a>
                <span>/</span>
                <a href="{{ route('youtube.videos.index', ['channel_id' => $video->youtube_channel_id]) }}" class="hover:text-red-600 transition">
                    Videos
                </a>
                <span>/</span>
                <span>SEO Studio</span>
            </div>
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white">AI Video SEO Studio</h1>
        </div>

        <div class="flex items-center gap-2.5">
            <a href="{{ $video->watch_url }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-red-600 text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-brands fa-youtube text-red-600"></i> Watch on YouTube
            </a>

            {{-- 1-Click AI SEO Generator --}}
            <form action="{{ route('youtube.studio.optimize', $video->id) }}" method="POST" id="aiOptimizeForm">
                @csrf
                <button type="submit" id="aiOptimizeBtn" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-700 hover:to-pink-700 text-white font-black text-xs transition shadow-lg shadow-indigo-500/25 flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                    <span>{{ $video->status === 'optimized' ? '⚡ Re-Generate AI SEO' : '✨ Generate AI SEO Package' }}</span>
                </button>
            </form>
        </div>
    </div>

    {{-- Main Workspace Grid (Left: Preview & Context, Right: AI Metadata Editor) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- Left Column: Video Preview & Original Info (4 Cols) --}}
        <div class="lg:col-span-4 space-y-6">
            {{-- Video Preview Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700 shadow-sm overflow-hidden p-5 space-y-4">
                <div class="relative aspect-video w-full rounded-2xl overflow-hidden bg-black shadow-inner">
                    <img src="{{ $video->thumbnail_url ?: asset('images/placeholder.png') }}" class="w-full h-full object-cover">
                    <div class="absolute top-2 left-2">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold uppercase tracking-wider {{ $video->current_privacy_status === 'public' ? 'bg-emerald-600' : 'bg-amber-500' }} text-white shadow-md">
                            {{ $video->current_privacy_status }}
                        </span>
                    </div>
                </div>

                {{-- SEO Health Score --}}
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-black text-slate-700 dark:text-slate-300">YouTube SEO Score</span>
                        <span class="text-sm font-black {{ $video->seo_score >= 80 ? 'text-emerald-600' : ($video->seo_score >= 50 ? 'text-indigo-600' : 'text-slate-400') }}">
                            {{ $video->seo_score }}/100
                        </span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all duration-700 {{ $video->seo_score >= 80 ? 'bg-emerald-500' : ($video->seo_score >= 50 ? 'bg-indigo-500' : 'bg-amber-500') }}" style="width: {{ max(10, $video->seo_score) }}%"></div>
                    </div>
                </div>

                {{-- Original Source Draft Metadata --}}
                <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">Original Uploaded Draft</span>
                    <div class="p-3 bg-slate-50 dark:bg-slate-900/40 rounded-xl border border-slate-100 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-300 space-y-1">
                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ $video->original_title }}</p>
                        @if($video->original_description)
                            <p class="text-[11px] text-slate-400 line-clamp-3">{{ $video->original_description }}</p>
                        @endif
                    </div>
                </div>

                {{-- 🎬 Video Script & Transcript Input Box (NEW) --}}
                <div class="space-y-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                            <i class="fa-solid fa-file-lines"></i> Video Script / Transcript
                        </span>
                        <span class="text-[9px] text-slate-400 font-bold uppercase">Optional Context</span>
                    </div>
                    <p class="text-[10px] text-slate-500">ভিডিওর স্ক্রিপ্ট বা বক্তব্য দিলে এআই আরও নিখুঁত সার্চ র‍্যাংকিং ট্যাগ ও সামারি বানাবে:</p>
                    <textarea id="scriptInput" rows="5" class="w-full p-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-normal text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500" placeholder="এখানে ভিডিওর পুরো স্ক্রিপ্ট, মূল বক্তব্য বা টাইমস্ট্যাম্প পেস্ট করুন...">{{ $video->video_script }}</textarea>
                    
                    <button type="button" onclick="optimizeWithScript()" class="w-full py-2 px-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 text-xs font-black transition border border-indigo-200 dark:border-indigo-800 flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>Analyze Script & Run SEO</span>
                    </button>
                </div>
            </div>

            {{-- Live Search Result Mockup (How it looks on YouTube) --}}
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700 p-5 space-y-3 shadow-sm">
                <span class="text-xs font-black text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i class="fa-solid fa-magnifying-glass text-red-600"></i> YouTube Search Preview
                </span>
                
                <div class="p-3 bg-slate-50 dark:bg-slate-900/80 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-2">
                    <div class="relative aspect-video w-full rounded-xl overflow-hidden bg-black">
                        <img src="{{ $video->thumbnail_url ?: asset('images/placeholder.png') }}" class="w-full h-full object-cover">
                    </div>
                    <div class="space-y-1">
                        <h4 id="previewTitle" class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug">
                            {{ $video->ai_title ?: $video->original_title }}
                        </h4>
                        <p class="text-[10px] text-slate-400 flex items-center gap-1.5">
                            <span class="font-bold text-slate-600 dark:text-slate-300">{{ $video->channel?->channel_title }}</span> • <span>Just now</span>
                        </p>
                        <p id="previewDesc" class="text-[10px] text-slate-500 dark:text-slate-400 line-clamp-2 leading-tight">
                            {{ Str::limit($video->ai_description ?: $video->original_description, 90) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: AI SEO Metadata Editor & Publisher (8 Cols) --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Title Variations Picker (if generated) --}}
            @if(!empty($video->ai_title_variations) && is_array($video->ai_title_variations))
            <div class="bg-gradient-to-r from-indigo-50/80 to-purple-50/60 dark:from-slate-800 dark:to-indigo-950/40 rounded-3xl p-5 border border-indigo-100 dark:border-indigo-900/40 shadow-sm space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-lightbulb text-amber-500"></i>
                        AI Title Variations (Click to Apply)
                    </span>
                    <span class="text-[10px] text-indigo-500 font-bold uppercase">High CTR</span>
                </div>

                <div class="space-y-2">
                    @foreach($video->ai_title_variations as $idx => $tVariation)
                        <button type="button" onclick="selectTitle('{{ addslashes($tVariation) }}')" class="w-full text-left p-3 rounded-2xl bg-white dark:bg-slate-800 hover:bg-indigo-50/50 dark:hover:bg-slate-700/60 border border-slate-200/80 dark:border-slate-700 text-xs font-bold text-slate-800 dark:text-slate-200 transition flex items-center justify-between gap-3 group">
                            <span class="leading-snug">{{ $tVariation }}</span>
                            <span class="text-[10px] font-extrabold text-indigo-600 group-hover:underline shrink-0">Use This</span>
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 🖼️ Thumbnail Hook Ideas Banner (if generated) --}}
            @if(!empty($video->ai_thumbnail_ideas) && is_array($video->ai_thumbnail_ideas))
            <div class="bg-gradient-to-r from-rose-50 to-amber-50 dark:from-slate-800 dark:to-rose-950/30 rounded-3xl p-5 border border-rose-200/80 dark:border-rose-900/40 shadow-sm space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-rose-900 dark:text-rose-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-image text-rose-600"></i>
                        Thumbnail Punch Text Ideas (CTR Booster)
                    </span>
                    <span class="text-[10px] text-rose-600 font-bold uppercase">2-4 Words</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($video->ai_thumbnail_ideas as $thIdea)
                        <button type="button" onclick="copyToClipboard('{{ addslashes($thIdea) }}', this)" class="px-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-800 border border-rose-200 dark:border-rose-800 text-xs font-black text-slate-800 dark:text-slate-200 hover:text-rose-600 hover:border-rose-400 transition flex items-center gap-1.5 shadow-sm">
                            <span>"{{ $thIdea }}"</span>
                            <i class="fa-regular fa-copy text-[10px] text-slate-400"></i>
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 📌 Pinned Engagement Comment (if generated) --}}
            @if(!empty($video->ai_pinned_comment))
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-slate-800 dark:to-emerald-950/30 rounded-3xl p-5 border border-emerald-200/80 dark:border-emerald-900/40 shadow-sm space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black text-emerald-900 dark:text-emerald-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-thumbtack text-emerald-600"></i>
                        AI Pinned Engagement Comment
                    </span>
                    <button type="button" onclick="copyToClipboard('{{ addslashes($video->ai_pinned_comment) }}', this)" class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-[10px] font-extrabold hover:bg-emerald-700 transition flex items-center gap-1 shadow-sm">
                        <i class="fa-regular fa-copy"></i> Copy Comment
                    </button>
                </div>
                <div class="p-3 bg-white dark:bg-slate-800 rounded-xl border border-emerald-100 dark:border-emerald-900/40 text-xs font-semibold text-slate-800 dark:text-slate-200 leading-relaxed">
                    {{ $video->ai_pinned_comment }}
                </div>
            </div>
            @endif

            {{-- Main Form: Title, Description, Tags, Publish --}}
            <form action="{{ route('youtube.studio.publish', $video->id) }}" method="POST" class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700 p-6 sm:p-8 space-y-6 shadow-sm">
                @csrf

                {{-- 1. Video Title --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                            YouTube Video Title (Max 100 chars)
                        </label>
                        <span id="titleCounter" class="text-[11px] font-bold text-slate-400">0/100</span>
                    </div>
                    <input type="text" id="titleInput" name="title" value="{{ $video->ai_title ?: $video->original_title }}" maxlength="100" required class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-extrabold text-slate-900 dark:text-white focus:ring-2 focus:ring-red-500" placeholder="Enter high-converting YouTube title...">
                </div>

                {{-- 2. Rich SEO Description --}}
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                            SEO Description (Summary, Timestamps, Links & Hashtags)
                        </label>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="copyToClipboard(document.getElementById('descInput').value, this)" class="text-[10px] font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                <i class="fa-regular fa-copy"></i> Copy Description
                            </button>
                            <span id="descCounter" class="text-[11px] font-bold text-slate-400">0/5000</span>
                        </div>
                    </div>
                    <textarea id="descInput" name="description" rows="10" maxlength="5000" class="w-full px-4 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-medium text-slate-800 dark:text-slate-200 leading-relaxed focus:ring-2 focus:ring-red-500" placeholder="Write full YouTube description...">{{ $video->ai_description ?: $video->original_description }}</textarea>
                </div>

                {{-- 3. Search Tags (Comma separated) --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">
                            YouTube Search Tags (Comma separated, Max 500 chars total)
                        </label>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="copyToClipboard(document.getElementById('tagsInput').value, this)" class="text-[10px] font-bold text-indigo-600 hover:underline flex items-center gap-1">
                                <i class="fa-regular fa-copy"></i> Copy All Tags
                            </button>
                            <span id="tagsCounter" class="text-[11px] font-bold text-slate-400">0/500</span>
                        </div>
                    </div>
                    @php
                        $tagsString = !empty($video->ai_tags) ? (is_array($video->ai_tags) ? implode(', ', $video->ai_tags) : $video->ai_tags) : (!empty($video->original_tags) ? implode(', ', $video->original_tags) : '');
                    @endphp
                    <input type="text" id="tagsInput" name="tags" value="{{ $tagsString }}" class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-semibold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500" placeholder="tag1, tag2, tag3, tag4...">

                    {{-- Tags Pills visualizer --}}
                    <div id="tagsPillsContainer" class="flex flex-wrap gap-1.5 pt-1">
                        @if(!empty($video->ai_tags) && is_array($video->ai_tags))
                            @foreach($video->ai_tags as $tag)
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-extrabold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">
                                    # {{ $tag }}
                                </span>
                            @endforeach
                        @endif
                    </div>
                </div>

                {{-- 4. Publish Settings & Action Controls --}}
                <div class="pt-6 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-black text-slate-700 dark:text-slate-300 shrink-0">Publish Privacy:</label>
                        <select name="privacy_status" class="px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-bold text-slate-800 dark:text-slate-200">
                            <option value="public" {{ $video->current_privacy_status === 'public' ? 'selected' : '' }}>🟢 Public (Live Now)</option>
                            <option value="unlisted" {{ $video->current_privacy_status === 'unlisted' ? 'selected' : '' }}>🟡 Unlisted (Keep Hidden)</option>
                            <option value="private" {{ $video->current_privacy_status === 'private' ? 'selected' : '' }}>🔒 Private</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-3">
                        <button type="button" onclick="saveMetadataOnly()" class="px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-100 text-xs font-bold transition">
                            Save Draft
                        </button>

                        <button type="submit" class="px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black text-xs transition shadow-lg shadow-red-600/30 flex items-center gap-2 hover:scale-[1.02]">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Update & Publish to YouTube</span>
                        </button>
                    </div>
                </div>
            </form>

            <form id="saveMetadataForm" action="{{ route('youtube.studio.save', $video->id) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="ai_title" id="saveAiTitle">
                <input type="hidden" name="ai_description" id="saveAiDescription">
                <input type="hidden" name="ai_tags" id="saveAiTags">
                <input type="hidden" name="video_script" id="saveVideoScript">
            </form>

            <form id="scriptOptimizeForm" action="{{ route('youtube.studio.optimize', $video->id) }}" method="POST" class="hidden">
                @csrf
                <input type="hidden" name="video_script" id="optVideoScript">
            </form>
        </div>

    </div>

</div>

@push('scripts')
<script>
const titleInput = document.getElementById('titleInput');
const descInput = document.getElementById('descInput');
const tagsInput = document.getElementById('tagsInput');
const scriptInput = document.getElementById('scriptInput');
const titleCounter = document.getElementById('titleCounter');
const descCounter = document.getElementById('descCounter');
const tagsCounter = document.getElementById('tagsCounter');
const previewTitle = document.getElementById('previewTitle');
const previewDesc = document.getElementById('previewDesc');

function updateCounters() {
    titleCounter.innerText = titleInput.value.length + '/100';
    descCounter.innerText = descInput.value.length + '/5000';
    tagsCounter.innerText = tagsInput.value.length + '/500';

    previewTitle.innerText = titleInput.value || 'Video Title';
    previewDesc.innerText = descInput.value.substring(0, 90) + '...';
}

titleInput.addEventListener('input', updateCounters);
descInput.addEventListener('input', updateCounters);
tagsInput.addEventListener('input', updateCounters);
updateCounters();

function selectTitle(newTitle) {
    titleInput.value = newTitle;
    updateCounters();
}

function optimizeWithScript() {
    document.getElementById('optVideoScript').value = scriptInput ? scriptInput.value : '';
    document.getElementById('scriptOptimizeForm').submit();
}

function saveMetadataOnly() {
    document.getElementById('saveAiTitle').value = titleInput.value;
    document.getElementById('saveAiDescription').value = descInput.value;
    document.getElementById('saveAiTags').value = tagsInput.value;
    document.getElementById('saveVideoScript').value = scriptInput ? scriptInput.value : '';
    document.getElementById('saveMetadataForm').submit();
}
</script>
@endpush
@endsection
