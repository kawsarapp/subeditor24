@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-3 sm:px-6 lg:px-8">

    {{-- FLOATING HIGH VIRAL ALERT TOAST --}}
    <div id="highViralAlertToast" class="hidden fixed top-20 right-5 z-[90] max-w-md bg-rose-900 text-white p-4 rounded-2xl shadow-2xl border border-rose-500 animate-bounce">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-rose-600 flex items-center justify-center text-white font-black text-xl shrink-0">
                🚨
            </div>
            <div>
                <h4 class="font-extrabold text-xs text-rose-300 uppercase tracking-wider">High Viral Alert (90+ Score)</h4>
                <p id="alertToastTitle" class="font-black text-sm text-white leading-snug mt-0.5"></p>
                <div class="mt-2 flex items-center gap-2">
                    <button onclick="dismissAlertToast()" class="bg-white/20 hover:bg-white/30 text-white text-[10px] font-bold px-3 py-1 rounded-lg">বন্ধ করুন</button>
                    <span id="alertToastScore" class="bg-rose-500 text-white text-[10px] font-black px-2 py-0.5 rounded-md"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- HEADER BANNER WITH SOUND TOGGLE --}}
    <div class="luxe-card p-6 md:p-8 rounded-3xl border border-indigo-200/80 mb-8 bg-gradient-to-r from-indigo-900 via-indigo-800 to-violet-900 text-white shadow-2xl relative overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="relative z-10 max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-amber-300 text-xs font-black uppercase tracking-wider mb-3 backdrop-blur-md border border-white/10">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-ping"></span> Real-Time External & Internal Intelligence Engine
            </div>
            <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight mb-2">
                🌐 AI Viral Predictor & Social Buzz Engine
            </h1>
            <p class="text-xs sm:text-sm text-indigo-100 font-medium">
                বাংলাদেশের ৩৫+ প্রধান নিউজ পোর্টাল (প্রথম আলো, বিডিনিউজ২৪, যমুনা টিভি, সময় টিভি ইত্যাদি) এবং সোশ্যাল মিডিয়া (Facebook Buzz, Twitter/X Trends, Google Search Spikes) এনালাইসিস করে ৩ ঘণ্টার ভাইরাল নিউজ স্পটার!
            </p>
        </div>

        {{-- SOUND ALERT TOGGLE BUTTON --}}
        <div class="relative z-10 shrink-0">
            <button id="alertSoundToggleBtn" onclick="toggleAlertSound()" class="bg-white/10 hover:bg-white/20 text-white font-extrabold px-4 py-2.5 rounded-2xl text-xs flex items-center gap-2 border border-white/20 backdrop-blur-md shadow-md transition">
                <i id="alertSoundIcon" class="fa-solid fa-bell text-amber-400"></i>
                <span id="alertSoundText">🔔 অ্যালার্ট সাউন্ড: চালু</span>
            </button>
        </div>

        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none text-white text-9xl">
            <i class="fa-solid fa-fire"></i>
        </div>
    </div>

    {{-- TIMEFRAME & EXTERNAL LIVE FILTER BAR --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4 bg-white p-4 rounded-2xl border border-slate-200/80 shadow-sm">
        <h2 class="text-xs font-extrabold text-slate-800 flex items-center gap-2 uppercase tracking-wider">
            <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i> সময়সীমা & উৎস:
        </h2>
        <div class="flex items-center gap-2 overflow-x-auto custom-scrollbar w-full sm:w-auto pb-1 sm:pb-0">
            <a href="{{ route('trending.index', ['timeframe' => 'external']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border shrink-0 flex items-center gap-1.5 {{ $timeframe == 'external' ? 'bg-rose-600 text-white border-rose-600 shadow-md animate-pulse' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' }}">
                <i class="fa-solid fa-satellite-dish"></i> 📡 External Live Trends (ইন্টারনেট লাইভ)
            </a>
            <a href="{{ route('trending.index', ['timeframe' => 3]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border shrink-0 {{ $timeframe == '3' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                ⚡ গত ৩ ঘণ্টা
            </a>
            <a href="{{ route('trending.index', ['timeframe' => 6]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border shrink-0 {{ $timeframe == '6' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                🔥 গত ৬ ঘণ্টা
            </a>
            <a href="{{ route('trending.index', ['timeframe' => 12]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border shrink-0 {{ $timeframe == '12' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                📈 গত ১২ ঘণ্টা
            </a>
            <a href="{{ route('trending.index', ['timeframe' => 'all']) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all border shrink-0 {{ $timeframe == 'all' ? 'bg-indigo-600 text-white border-indigo-600 shadow-md' : 'bg-slate-50 text-slate-700 border-slate-200 hover:bg-slate-100' }}">
                🌐 সব খবর (All)
            </a>
        </div>
    </div>

    {{-- TOPIC CATEGORY FILTER BAR --}}
    <div class="mb-6 bg-slate-100/80 p-2.5 rounded-2xl border border-slate-200/80 flex items-center gap-2 overflow-x-auto custom-scrollbar">
        <span class="text-[11px] font-extrabold text-slate-500 uppercase px-2 shrink-0">বিট ফিল্টার:</span>
        <button onclick="filterCategory('all')" id="catBtn-all" class="cat-filter-btn px-3 py-1 rounded-xl text-xs font-extrabold transition-all bg-white text-indigo-700 shadow-sm border border-slate-200 shrink-0">
            🌐 সব বিট (All)
        </button>
        <button onclick="filterCategory('politics')" id="catBtn-politics" class="cat-filter-btn px-3 py-1 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-white shrink-0">
            🏛️ রাজনীতি
        </button>
        <button onclick="filterCategory('crime')" id="catBtn-crime" class="cat-filter-btn px-3 py-1 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-white shrink-0">
            ⚖️ অপরাধ & আইন
        </button>
        <button onclick="filterCategory('sports')" id="catBtn-sports" class="cat-filter-btn px-3 py-1 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-white shrink-0">
            🏏 খেলাধুলা
        </button>
        <button onclick="filterCategory('entertainment')" id="catBtn-entertainment" class="cat-filter-btn px-3 py-1 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-white shrink-0">
            🎬 বিনোদন
        </button>
        <button onclick="filterCategory('international')" id="catBtn-international" class="cat-filter-btn px-3 py-1 rounded-xl text-xs font-extrabold transition-all text-slate-700 hover:bg-white shrink-0">
            🌍 আন্তর্জাতিক
        </button>
    </div>

    {{-- TRENDING NEWS GRID --}}
    <div id="trendingGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
        @forelse($trendingList as $item)
        <div class="trending-card luxe-card rounded-3xl p-5 border border-slate-200/90 shadow-md hover:shadow-xl transition-all flex flex-col justify-between h-full bg-white relative group" data-category="{{ $item->category ?? 'general' }}" data-viral-score="{{ $item->viral_score }}" data-title="{{ addslashes($item->title) }}">
            
            <div>
                {{-- Viral Velocity Header --}}
                <div class="flex items-center justify-between gap-2 mb-3">
                    <span class="text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-wide shadow-sm {{ $item->viral_badge_color }}">
                        {{ $item->viral_level }}
                    </span>
                    <div class="flex items-center gap-1 text-xs font-black text-slate-700 bg-slate-100 px-2.5 py-1 rounded-full border border-slate-200">
                        <i class="fa-solid fa-fire text-amber-500"></i> Score: <span class="text-indigo-600 font-extrabold">{{ $item->viral_score }}/100</span>
                    </div>
                </div>

                {{-- Category Pill & Multi-Portal Coverage --}}
                <div class="mb-3 bg-indigo-50/60 p-2.5 rounded-2xl border border-indigo-100">
                    <div class="flex items-center justify-between text-[11px] font-bold text-slate-600 mb-1">
                        <span class="text-indigo-700 font-extrabold flex items-center gap-1">
                            <i class="fa-solid {{ $item->category_icon ?? 'fa-newspaper' }} text-indigo-500"></i> {{ $item->category_label ?? 'সাধারণ' }}
                        </span>
                        <span class="bg-indigo-200 text-indigo-800 px-2 py-0.5 rounded-md font-black text-[10px]">
                            {{ count($item->matching_portals ?? []) }} Sources
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-1 mt-1.5">
                        @foreach(($item->matching_portals ?? [($item->source ?? ($item->website->name ?? 'Portal'))]) as $portal)
                        <span class="inline-block bg-white text-slate-800 text-[10px] font-extrabold px-2 py-0.5 rounded-md border border-slate-200 shadow-2xs">
                            🌐 {{ $portal }}
                        </span>
                        @endforeach
                    </div>
                </div>

                {{-- Social Media Velocity Gauges --}}
                <div class="space-y-1.5 mb-3 bg-slate-50 p-2.5 rounded-2xl border border-slate-200">
                    {{-- Facebook Buzz Bar --}}
                    <div>
                        <div class="flex justify-between text-[10px] font-bold text-slate-600">
                            <span><i class="fa-brands fa-facebook text-blue-600"></i> Facebook Share Rate</span>
                            <span class="text-blue-700 font-black">{{ $item->fb_buzz }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden mt-0.5">
                            <div class="bg-blue-600 h-full rounded-full" style="width: {{ $item->fb_buzz }}%"></div>
                        </div>
                    </div>
                    {{-- Twitter & Google Spikes --}}
                    <div class="flex justify-between text-[10px] font-extrabold text-slate-600 pt-1 border-t border-slate-200">
                        <span class="flex items-center gap-1 text-slate-700">
                            <i class="fa-brands fa-x-twitter"></i> Twitter Match: <strong class="text-slate-900">{{ $item->twitter_trend }}%</strong>
                        </span>
                        <span class="flex items-center gap-1 text-emerald-600">
                            <i class="fa-solid fa-chart-line"></i> Google Spike: <strong class="text-emerald-700">{{ $item->google_search_spike }}%</strong>
                        </span>
                    </div>
                </div>

                {{-- Sentiment & Lifespan Indicators --}}
                <div class="flex items-center justify-between gap-1 text-[10px] font-bold mb-3">
                    <span class="px-2.5 py-1 rounded-lg border {{ $item->sentiment_badge_color }}">
                        {{ $item->sentiment_label }}
                    </span>
                    <span class="text-slate-500 font-extrabold flex items-center gap-1">
                        <i class="fa-solid fa-hourglass-half text-amber-500"></i> {{ $item->lifespan }}
                    </span>
                </div>

                {{-- Title --}}
                <h3 class="font-extrabold text-slate-900 text-base leading-snug mb-3 group-hover:text-indigo-600 transition-colors">
                    {{ $item->title }}
                </h3>
            </div>

            {{-- Action Button --}}
            <div class="pt-4 border-t border-slate-100 mt-2">
                <button onclick="generateViralScript('{{ $item->id ?? 'ext' }}', '{{ addslashes($item->title) }}', '{{ addslashes($item->description ?? $item->title) }}')" class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-extrabold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 shadow-md shadow-indigo-500/20 transition-all active:scale-95">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i> ⚡ ভাইরাল স্ক্রিপ্ট, ফটোকার্ড ও প্যাকেজ
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-full luxe-card p-12 text-center rounded-3xl border border-slate-200">
            <i class="fa-solid fa-satellite-dish text-5xl text-rose-400 mb-3 animate-pulse"></i>
            <h3 class="text-lg font-extrabold text-slate-700">লাইভ ট্রেন্ড ডাটা লোড হচ্ছে...</h3>
            <p class="text-xs text-slate-500 mt-1">ইন্টারনেট লাইভ পোর্টাল অথবা ডাটাবেজ খবর দেখতে নিচের বাটনে চাপ দিন।</p>
            <div class="mt-4 flex justify-center gap-3">
                <a href="{{ route('trending.index', ['timeframe' => 'external']) }}" class="bg-rose-600 text-white font-bold px-4 py-2 rounded-xl text-xs shadow-md">📡 External Live Trends (ইন্টারনেট লাইভ)</a>
                <a href="{{ route('trending.index', ['timeframe' => 'all']) }}" class="bg-indigo-600 text-white font-bold px-4 py-2 rounded-xl text-xs shadow-md">🌐 সব খবর দেখুন</a>
            </div>
        </div>
        @endforelse
    </div>

</div>

{{-- VIRAL SCRIPT MODAL --}}
<div id="viralScriptModal" class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar flex flex-col justify-between">
        
        {{-- Modal Header --}}
        <div class="p-5 border-b border-slate-100 bg-slate-50 rounded-t-3xl flex justify-between items-center sticky top-0 z-10">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-black shadow-md">
                    <i class="fa-solid fa-bolt text-base"></i>
                </div>
                <div>
                    <h3 class="font-black text-slate-900 text-base">🔥 AI 3-Hour Viral Package</h3>
                    <p class="text-[10px] font-bold text-indigo-600 uppercase">আগামী ৩ ঘণ্টার জন্য প্রস্তুতকৃত ভাইরাল কন্টেন্ট ও ফটোকার্ড</p>
                </div>
            </div>
            <button onclick="closeViralModal()" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-100">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="p-6 space-y-5" id="viralModalBody">
            <div class="flex items-center justify-center py-10">
                <div class="text-center">
                    <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto mb-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <p class="text-sm font-extrabold text-slate-800">AI সোশ্যাল মিডিয়া কন্টেন্ট ও ফটোকার্ড পাঞ্চলাইন তৈরি করছে...</p>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="p-4 border-t border-slate-100 bg-slate-50 rounded-b-3xl flex justify-between items-center">
            <button onclick="closeViralModal()" class="px-5 py-2 rounded-xl text-xs font-extrabold text-slate-600 hover:bg-slate-200 transition">
                বন্ধ করুন
            </button>
            <a id="createPostModalBtn" href="{{ route('news.create') }}" class="bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-extrabold px-6 py-2.5 rounded-xl text-xs flex items-center gap-2 shadow-lg shadow-indigo-500/20">
                🚀 ১-ক্লিকে পোস্ট বা কার্ড তৈরি করুন
            </a>
        </div>

    </div>
</div>

@push('scripts')
<script>
let soundAlertEnabled = true;

function toggleAlertSound() {
    soundAlertEnabled = !soundAlertEnabled;
    const btnText = document.getElementById('alertSoundText');
    const btnIcon = document.getElementById('alertSoundIcon');
    if (soundAlertEnabled) {
        btnText.innerText = '🔔 অ্যালার্ট সাউন্ড: চালু';
        btnIcon.className = 'fa-solid fa-bell text-amber-400';
    } else {
        btnText.innerText = '🔕 অ্যালার্ট সাউন্ড: বন্ধ';
        btnIcon.className = 'fa-solid fa-bell-slash text-slate-400';
    }
}

function playHighViralChime() {
    if (!soundAlertEnabled) return;
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, audioCtx.currentTime); // D5
        osc.frequency.exponentialRampToValueAtTime(880, audioCtx.currentTime + 0.15); // A5
        
        gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
        
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        
        osc.start();
        osc.stop(audioCtx.currentTime + 0.5);
    } catch(e) {}
}

function triggerHighViralCheck() {
    const cards = document.querySelectorAll('.trending-card');
    let highestHighViral = null;

    cards.forEach(card => {
        const score = parseInt(card.getAttribute('data-viral-score') || '0');
        if (score >= 90 && !highestHighViral) {
            highestHighViral = {
                title: card.getAttribute('data-title') || '',
                score: score
            };
        }
    });

    if (highestHighViral) {
        const toast = document.getElementById('highViralAlertToast');
        document.getElementById('alertToastTitle').innerText = highestHighViral.title;
        document.getElementById('alertToastScore').innerText = 'Viral Score: ' + highestHighViral.score + '/100';
        toast.classList.remove('hidden');
        playHighViralChime();
    }
}

function dismissAlertToast() {
    document.getElementById('highViralAlertToast').classList.add('hidden');
}

function filterCategory(catSlug) {
    const btns = document.querySelectorAll('.cat-filter-btn');
    btns.forEach(btn => {
        btn.classList.remove('bg-white', 'text-indigo-700', 'shadow-sm', 'border', 'border-slate-200');
        btn.classList.add('text-slate-700');
    });

    const activeBtn = document.getElementById('catBtn-' + catSlug);
    if (activeBtn) {
        activeBtn.classList.remove('text-slate-700');
        activeBtn.classList.add('bg-white', 'text-indigo-700', 'shadow-sm', 'border', 'border-slate-200');
    }

    const cards = document.querySelectorAll('.trending-card');
    cards.forEach(card => {
        const cardCat = card.getAttribute('data-category');
        if (catSlug === 'all' || cardCat === catSlug) {
            card.classList.remove('hidden');
        } else {
            card.classList.add('hidden');
        }
    });
}

function generateViralScript(newsId, title, content) {
    const modal = document.getElementById('viralScriptModal');
    const body = document.getElementById('viralModalBody');

    modal.classList.remove('hidden');
    body.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <div class="text-center">
                <svg class="animate-spin h-9 w-9 text-indigo-600 mx-auto mb-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <p class="text-sm font-extrabold text-slate-800">AI আগামী ৩ ঘণ্টার ভাইরাল স্ক্রিপ্ট, ফটোকার্ড পাঞ্চলাইন ও হেডলাইন প্রস্তুত করছে...</p>
            </div>
        </div>
    `;

    fetch('{{ route("trending.generate-script") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            news_id: newsId,
            title: title || '',
            content: content || ''
        })
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            body.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 rounded-2xl text-xs font-bold">${data.message || 'Error occurred.'}</div>`;
            return;
        }

        let headlinesHtml = (data.catchy_headlines || []).map((h) => `
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-2 mb-2">
                <span class="text-xs font-extrabold text-slate-900">💥 ${h}</span>
                <button onclick="navigator.clipboard.writeText('${h.replace(/'/g, "\\'")}')" class="text-[10px] bg-indigo-50 text-indigo-600 hover:bg-indigo-100 font-bold px-2.5 py-1 rounded-lg border border-indigo-200">কপি</button>
            </div>
        `).join('');

        body.innerHTML = `
            {{-- Viral Angle --}}
            <div class="p-4 bg-amber-50 border border-amber-200/80 rounded-2xl">
                <h4 class="text-xs font-extrabold text-amber-900 uppercase mb-1 flex items-center gap-1.5">
                    🎯 Viral Angle (কেন ভাইরাল হবে):
                </h4>
                <p class="text-xs font-bold text-amber-950 leading-relaxed">${data.viral_angle}</p>
            </div>

            {{-- Photocard Punchline --}}
            <div class="p-4 bg-indigo-900 text-white rounded-2xl border border-indigo-700 shadow-md">
                <div class="flex justify-between items-center mb-1">
                    <h4 class="text-xs font-extrabold text-amber-300 uppercase flex items-center gap-1">
                        🖼️ ফটোকার্ডের ১-লাইনার পাঞ্চলাইন:
                    </h4>
                    <button onclick="navigator.clipboard.writeText('${(data.photocard_punchline || '').replace(/'/g, "\\'")}')" class="text-[10px] bg-white/20 hover:bg-white/30 text-white font-bold px-2.5 py-1 rounded-lg border border-white/20">কপি টেক্সট</button>
                </div>
                <p class="text-sm font-black text-white leading-snug">${data.photocard_punchline || ''}</p>
            </div>

            {{-- Catchy Headlines --}}
            <div>
                <h4 class="text-xs font-extrabold text-slate-700 uppercase mb-2">💥 3 High-CTR Catchy Headlines:</h4>
                ${headlinesHtml}
            </div>

            {{-- Reels Script --}}
            <div>
                <div class="flex justify-between items-center mb-1">
                    <h4 class="text-xs font-extrabold text-slate-700 uppercase">📹 30-Second Reels/Shorts Script:</h4>
                    <button onclick="navigator.clipboard.writeText(\`${(data.reels_script || '').replace(/`/g, "\\`")}\`)" class="text-[10px] bg-indigo-50 text-indigo-600 font-bold px-2.5 py-1 rounded-lg border border-indigo-200">কপি স্ক্রিপ্ট</button>
                </div>
                <div class="p-3.5 bg-slate-900 text-slate-100 rounded-2xl text-xs font-mono whitespace-pre-line leading-relaxed border border-slate-800">
                    ${data.reels_script}
                </div>
            </div>

            {{-- Facebook Caption --}}
            <div>
                <div class="flex justify-between items-center mb-1">
                    <h4 class="text-xs font-extrabold text-slate-700 uppercase">💬 Facebook Caption & Engagement Hook:</h4>
                    <button onclick="navigator.clipboard.writeText(\`${(data.facebook_caption || '').replace(/`/g, "\\`")}\`)" class="text-[10px] bg-indigo-50 text-indigo-600 font-bold px-2.5 py-1 rounded-lg border border-indigo-200">কপি ক্যাপশন</button>
                </div>
                <div class="p-3.5 bg-slate-50 border border-slate-200 text-slate-800 rounded-2xl text-xs whitespace-pre-line leading-relaxed font-semibold">
                    ${data.facebook_caption}
                </div>
            </div>
        `;
    })
    .catch(() => {
        body.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 rounded-2xl text-xs font-bold">Failed to connect to AI server.</div>`;
    });
}

function closeViralModal() {
    document.getElementById('viralScriptModal').classList.add('hidden');
}

// Check for High Viral Score (>90) on page load
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(triggerHighViralCheck, 800);
});
</script>
@endpush
@endsection
