<div class="bg-white border border-slate-200/90 rounded-3xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between group relative feed-card animate-fadeIn" data-id="{{ $item->id }}" id="central-card-{{ $item->id }}">
    {{-- Top Section --}}
    <div>
        {{-- Image & Badge Header --}}
        <div class="relative h-48 sm:h-52 bg-slate-100 overflow-hidden">
            @if($item->thumbnail_url)
                <img src="{{ route('proxy.image', ['url' => $item->thumbnail_url]) }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" onerror="this.onerror=null;this.src='https://placehold.co/600x400/e2e8f0/475569?text=News+Image';">
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-slate-400 bg-gradient-to-br from-slate-100 to-slate-200">
                    <i class="fa-regular fa-image text-3xl mb-1"></i>
                    <span class="text-[11px] font-bold">নো ইমেজ</span>
                </div>
            @endif

            {{-- Gradient Overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/30 pointer-events-none"></div>

            {{-- Source & Checkbox on top --}}
            <div class="absolute top-3 left-3 right-3 flex items-center justify-between">
                <span class="bg-black/75 backdrop-blur-md text-white text-[11px] font-black px-3 py-1 rounded-xl border border-white/20 shadow-sm flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                    {{ $item->source_name ?: ($item->website->name ?? 'সোর্স') }}
                </span>

                <input type="checkbox" value="{{ $item->id }}" class="feed-checkbox rounded border-white/40 text-indigo-600 bg-white/80 backdrop-blur-md shadow-md focus:ring-0 w-5 h-5 cursor-pointer" onchange="updateSelectedFeedCount()">
            </div>

            {{-- Time Badge on bottom --}}
            <div class="absolute bottom-3 left-3 right-3 flex items-center justify-between text-white text-[11px] font-bold">
                <span class="flex items-center gap-1 drop-shadow-md">
                    <i class="fa-regular fa-clock text-indigo-300"></i>
                    {{ $item->created_at->diffForHumans() }}
                </span>
                @if($item->source_domain)
                    <span class="bg-white/20 backdrop-blur-md px-2 py-0.5 rounded-md text-[10px] text-slate-100">
                        {{ $item->source_domain }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Content Body --}}
        <div class="p-5">
            <h3 class="font-bangla font-extrabold text-slate-900 text-base sm:text-lg leading-snug line-clamp-2 group-hover:text-indigo-600 transition-colors mb-2" title="{{ $item->title }}">
                {{ $item->title }}
            </h3>

            @if($item->content)
                <p class="font-bangla text-slate-500 text-xs sm:text-sm line-clamp-3 leading-relaxed mb-4">
                    {{ strip_tags($item->content) }}
                </p>
            @endif
        </div>
    </div>

    {{-- Bottom Action Buttons (100% AJAX Enabled) --}}
    <div class="p-5 pt-0 border-t border-slate-100/80 mt-2 space-y-2.5">
        <div class="grid grid-cols-2 gap-2">
            {{-- 1. AI Rewrite Action (AJAX) --}}
            <button type="button" onclick="handleFeedAction('{{ $item->id }}', 'ai', this)" class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white py-2.5 px-3 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 shadow-sm shadow-indigo-500/20 active:scale-95 btn-ai-{{ $item->id }} cursor-pointer">
                ⚡ AI রিরাইট
            </button>

            {{-- 2. Photocard Studio Action (AJAX) --}}
            <button type="button" onclick="handleFeedAction('{{ $item->id }}', 'studio', this)" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-2.5 px-3 rounded-xl text-xs font-black transition flex items-center justify-center gap-1.5 shadow-sm active:scale-95 btn-studio-{{ $item->id }} cursor-pointer">
                🎨 স্টুডিও
            </button>
        </div>

        <div class="grid grid-cols-2 gap-2">
            {{-- 3. Save to Private Drafts (AJAX) --}}
            <button type="button" onclick="handleFeedAction('{{ $item->id }}', 'draft', this)" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 btn-draft-{{ $item->id }} cursor-pointer">
                📥 ড্রাফটে নিন
            </button>

            {{-- 4. Original Source Link --}}
            <a href="{{ $item->original_link }}" target="_blank" rel="noopener noreferrer" class="w-full bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-indigo-600 py-2 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 text-center border border-slate-200/80">
                <span>মূল খবর</span> ↗
            </a>
        </div>
    </div>
</div>
