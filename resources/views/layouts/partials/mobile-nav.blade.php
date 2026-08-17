{{-- MOBILE TOP HEADER --}}
<div class="lg:hidden fixed top-0 w-full z-40 glass-nav h-14 flex items-center justify-between px-4 shadow-sm border-b border-slate-200/80 transition-all">
    <a href="{{ auth()->user()->role === 'reporter' ? route('reporter.news.index') : route('news.index') }}" class="flex items-center gap-2 group">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-sm font-black group-hover:scale-105 transition-transform"><i class="fa-solid fa-bolt text-xs"></i></div>
        <span class="font-extrabold text-lg text-slate-900 tracking-tight">Subeditor<span class="text-indigo-600">24</span></span>
    </a>
    @auth
    <div class="flex items-center gap-2">
        @if(auth()->user()->role !== 'reporter')
            <div class="bg-amber-50 text-amber-700 px-2.5 py-1 rounded-full text-xs font-bold border border-amber-200 shadow-sm">
                🪙 {{ auth()->user()->credits ?? 0 }}
            </div>
        @endif
        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-extrabold text-xs border border-indigo-200 uppercase shadow-sm">
            {{ substr(auth()->user()->name, 0, 1) }}
        </div>
    </div>
    @endauth
</div>

{{-- MOBILE BOTTOM NAVIGATION & SHEET --}}
@auth
<div class="lg:hidden fixed bottom-0 left-0 w-full z-[90] pb-safe">
    @if(auth()->user()->role === 'reporter')
    <div class="glass-sheet grid grid-cols-3 items-center h-16 border-t border-slate-200/80 shadow-[0_-8px_25px_rgba(0,0,0,0.06)] px-2">
        <a href="{{ route('reporter.news.index') }}" class="flex flex-col items-center justify-center h-full gap-1 transition-all {{ request()->routeIs('reporter.news.index') ? 'text-indigo-600 transform -translate-y-1 font-bold' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fa-solid fa-list-ul text-xl"></i><span class="text-[10px] font-bold">আমার খবর</span>
            @if(request()->routeIs('reporter.news.index')) <div class="w-1 h-1 bg-indigo-600 rounded-full absolute bottom-1"></div> @endif
        </a>
        <div class="relative flex justify-center h-full items-center">
            <a href="{{ route('reporter.news.create') }}" class="absolute -top-6 bg-gradient-to-tr from-indigo-600 to-violet-600 text-white w-[3.5rem] h-[3.5rem] rounded-full flex items-center justify-center shadow-[0_8px_20px_rgba(79,70,229,0.35)] border-4 border-slate-50 active:scale-95 transition-all font-black">
                <i class="fa-solid fa-plus text-2xl"></i>
            </a>
            <span class="absolute bottom-1 text-[10px] font-bold text-slate-600">পাঠান</span>
        </div>
        <button id="mobileMenuBtn" class="flex flex-col items-center justify-center h-full gap-1 text-slate-500 hover:text-slate-700 transition-colors relative">
            <i class="fa-solid fa-bars text-xl"></i><span class="text-[10px] font-bold">মেনু</span>
        </button>
    </div>
    @else
    <div class="glass-sheet grid grid-cols-4 items-center h-16 border-t border-slate-200/80 shadow-[0_-8px_25px_rgba(0,0,0,0.06)] px-2">
        <a href="{{ route('news.index') }}" class="flex flex-col items-center justify-center h-full gap-1 transition-all relative {{ request()->routeIs('news.index') ? 'text-indigo-600 transform -translate-y-1 font-bold' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fa-solid fa-house-chimney text-xl"></i><span class="text-[10px] font-bold">Feed</span>
            @if(request()->routeIs('news.index')) <div class="w-1 h-1 bg-indigo-600 rounded-full absolute bottom-1"></div> @endif
        </a>
        <div class="relative flex justify-center h-full items-center">
            <a href="{{ route('news.create') }}" class="absolute -top-6 bg-gradient-to-tr from-indigo-600 to-violet-600 text-white w-[3.5rem] h-[3.5rem] rounded-full flex items-center justify-center shadow-[0_8px_20px_rgba(79,70,229,0.35)] border-4 border-slate-50 active:scale-95 transition-transform font-black">
                <i class="fa-solid fa-plus text-2xl"></i>
            </a>
            <span class="absolute bottom-1 text-[10px] font-bold text-slate-600">Create</span>
        </div>
        <a href="{{ route('news.drafts') }}" class="flex flex-col items-center justify-center h-full gap-1 transition-all relative {{ request()->routeIs('news.drafts') ? 'text-indigo-600 transform -translate-y-1 font-bold' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fa-solid fa-wand-magic-sparkles text-xl"></i><span class="text-[10px] font-bold">AI</span>
            @if(request()->routeIs('news.drafts')) <div class="w-1 h-1 bg-indigo-600 rounded-full absolute bottom-1"></div> @endif
        </a>
        <button id="mobileMenuBtn" class="flex flex-col items-center justify-center h-full gap-1 text-slate-500 hover:text-slate-700 transition-colors relative">
            <i class="fa-solid fa-bars-staggered text-xl"></i><span class="text-[10px] font-bold">Menu</span>
        </button>
    </div>
    @endif
</div>
@endauth