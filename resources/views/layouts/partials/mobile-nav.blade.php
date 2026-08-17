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
        <button onclick="toggleMobileDrawer()" class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-extrabold text-xs border border-indigo-200 uppercase shadow-sm">
            {{ substr(auth()->user()->name, 0, 1) }}
        </button>
    </div>
    @endauth
</div>

{{-- MOBILE BOTTOM NAVIGATION --}}
@auth
<div class="lg:hidden fixed bottom-0 left-0 w-full z-[90] pb-safe">
    @if(auth()->user()->role === 'reporter')
    <div class="glass-sheet grid grid-cols-3 items-center h-16 border-t border-slate-200/80 shadow-[0_-8px_25px_rgba(0,0,0,0.06)] px-2">
        <a href="{{ route('reporter.news.index') }}" class="flex flex-col items-center justify-center h-full gap-1 transition-all {{ request()->routeIs('reporter.news.index') ? 'text-indigo-600 transform -translate-y-1 font-bold' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fa-solid fa-list-check text-xl"></i><span class="text-[10px] font-bold">আমার খবর</span>
            @if(request()->routeIs('reporter.news.index')) <div class="w-1 h-1 bg-indigo-600 rounded-full absolute bottom-1"></div> @endif
        </a>
        <div class="relative flex justify-center h-full items-center">
            <a href="{{ route('reporter.news.create') }}" class="absolute -top-6 bg-gradient-to-tr from-indigo-600 to-violet-600 text-white w-[3.5rem] h-[3.5rem] rounded-full flex items-center justify-center shadow-[0_8px_20px_rgba(79,70,229,0.35)] border-4 border-slate-50 active:scale-95 transition-all font-black">
                <i class="fa-solid fa-plus text-2xl"></i>
            </a>
            <span class="absolute bottom-1 text-[10px] font-bold text-slate-600">পাঠান</span>
        </div>
        <button onclick="toggleMobileDrawer()" class="flex flex-col items-center justify-center h-full gap-1 text-slate-500 hover:text-slate-700 transition-colors relative">
            <i class="fa-solid fa-bars-staggered text-xl"></i><span class="text-[10px] font-bold">মেনু</span>
        </button>
    </div>
    @else
    <div class="glass-sheet grid grid-cols-4 items-center h-16 border-t border-slate-200/80 shadow-[0_-8px_25px_rgba(0,0,0,0.06)] px-2">
        <a href="{{ route('news.index') }}" class="flex flex-col items-center justify-center h-full gap-1 transition-all relative {{ request()->routeIs('news.index') ? 'text-indigo-600 transform -translate-y-1 font-bold' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fa-solid fa-newspaper text-xl"></i><span class="text-[10px] font-bold">Feed</span>
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
        <button onclick="toggleMobileDrawer()" class="flex flex-col items-center justify-center h-full gap-1 text-slate-500 hover:text-slate-700 transition-colors relative">
            <i class="fa-solid fa-bars-staggered text-xl"></i><span class="text-[10px] font-bold">Menu</span>
        </button>
    </div>
    @endif
</div>

{{-- MOBILE FULL SLIDE-OUT DRAWER MODAL --}}
<div id="mobileDrawerBackdrop" onclick="toggleMobileDrawer()" class="hidden lg:hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[100] transition-opacity"></div>

<div id="mobileDrawerModal" class="fixed top-0 right-0 w-[85%] max-w-[320px] h-full bg-white z-[101] translate-x-full transition-transform duration-300 ease-in-out shadow-2xl flex flex-col justify-between overflow-y-auto custom-scrollbar lg:hidden">
    <div>
        {{-- Drawer Header --}}
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-sm border border-indigo-200 uppercase shadow-sm">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="font-extrabold text-sm text-slate-900 leading-tight">{{ auth()->user()->name }}</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ auth()->user()->role }}</p>
                </div>
            </div>
            <button onclick="toggleMobileDrawer()" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-100">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Limit & Credits Status --}}
        <div class="p-4 bg-indigo-50/50 border-b border-slate-100 flex justify-between items-center">
            <div class="text-xs font-bold text-slate-600">
                Today's Limit: <span class="text-indigo-600 font-black">{{ auth()->user()->todays_post_count ?? 0 }}/{{ auth()->user()->daily_post_limit ?? 20 }}</span>
            </div>
            @if(auth()->user()->role !== 'reporter')
            <a href="{{ route('credits.index') }}" class="bg-amber-100 text-amber-800 px-3 py-1 rounded-full text-xs font-bold border border-amber-200">🪙 {{ auth()->user()->credits ?? 0 }} Credits</a>
            @endif
        </div>

        {{-- All Drawer Navigation Links --}}
        <div class="p-3 space-y-1">
            <p class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">General Menu</p>
            
            <a href="{{ route('news.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.index') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-newspaper text-indigo-500 w-5 text-center text-sm"></i> Feed
            </a>
            
            <a href="{{ route('news.published') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.published') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-circle-check text-emerald-500 w-5 text-center text-sm"></i> Published News
            </a>

            @if(auth()->user()->hasPermission('can_direct_publish'))
            <a href="{{ route('news.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.create') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-pen-to-square text-indigo-500 w-5 text-center text-sm"></i> Create New Post
            </a>
            @endif

            @if(auth()->user()->hasPermission('can_ai'))
            <a href="{{ route('news.drafts') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.drafts') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-wand-magic-sparkles text-amber-500 w-5 text-center text-sm"></i> AI Rewrite Drafts
            </a>
            @endif

            @if(auth()->user()->hasPermission('can_scrape'))
            <a href="{{ route('websites.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('websites.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-globe text-cyan-500 w-5 text-center text-sm"></i> Observe (Sources)
            </a>
            @endif

            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_templates'))
            <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.templates.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-layer-group text-purple-500 w-5 text-center text-sm"></i> Card Templates
            </a>
            @endif

            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_reporters'))
            <div class="border-t border-slate-100 my-2"></div>
            <p class="px-3 pt-1 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Team Management</p>
            
            <a href="{{ route('manage.reporters.news') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('manage.reporters.news') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-satellite-dish text-rose-500 w-5 text-center text-sm"></i> Reporter Feed (Team News)
            </a>
            
            <a href="{{ route('manage.reporters.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('manage.reporters.index') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-users text-blue-500 w-5 text-center text-sm"></i> Team Members
            </a>
            @endif

            @if(auth()->user()->role === 'super_admin')
            <div class="border-t border-slate-100 my-2"></div>
            <p class="px-3 pt-1 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Admin Suite</p>
            
            @if(Route::has('admin.dashboard'))
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-chart-pie text-indigo-500 w-5 text-center text-sm"></i> Admin Dashboard
            </a>
            @endif

            @if(Route::has('admin.analytics.index'))
            <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-chart-line text-emerald-500 w-5 text-center text-sm"></i> Analytics Report
            </a>
            @endif

            @if(Route::has('admin.scraper-monitor'))
            <a href="{{ route('admin.scraper-monitor') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.scraper-monitor') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-headset text-amber-500 w-5 text-center text-sm"></i> Scraper Monitor
            </a>
            @endif
            @endif

            <div class="border-t border-slate-100 my-2"></div>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-sliders text-slate-500 w-5 text-center text-sm"></i> Settings & API
            </a>
        </div>
    </div>

    {{-- Logout Section --}}
    <div class="p-4 border-t border-slate-100 bg-slate-50">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 py-2.5 rounded-xl text-xs font-extrabold flex items-center justify-center gap-2">
                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </button>
        </form>
    </div>
</div>

<script>
    function toggleMobileDrawer() {
        const modal = document.getElementById('mobileDrawerModal');
        const backdrop = document.getElementById('mobileDrawerBackdrop');
        if (!modal || !backdrop) return;
        
        if (modal.classList.contains('translate-x-full')) {
            modal.classList.remove('translate-x-full');
            backdrop.classList.remove('hidden');
        } else {
            modal.classList.add('translate-x-full');
            backdrop.classList.add('hidden');
        }
    }
</script>
@endauth