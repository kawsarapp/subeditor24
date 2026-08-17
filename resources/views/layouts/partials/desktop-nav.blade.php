<nav class="hidden lg:block glass-nav sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 xl:px-6 h-16 flex justify-between items-center">
        
        <div class="flex items-center gap-4 xl:gap-6">
            {{-- Brand Logo --}}
            <a href="{{ auth()->user()->role === 'reporter' ? route('reporter.news.index') : route('news.index') }}" class="flex items-center gap-2.5 group shrink-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform font-black">
                    <i class="fa-solid fa-bolt text-base"></i>
                </div>
                <span class="font-extrabold text-xl tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors">Subeditor<span class="text-indigo-600">24</span></span>
            </a>

            @auth
            @if(auth()->user()->role === 'reporter')
                <div class="flex items-center bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 gap-1">
                    <a href="{{ route('reporter.news.create') }}" class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('reporter.news.create') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-700 hover:text-indigo-600 hover:bg-white' }}">
                        <i class="fa-solid fa-pen-to-square text-indigo-400"></i> খবর পাঠান
                    </a>
                    <a href="{{ route('reporter.news.index') }}" class="flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('reporter.news.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20' : 'text-slate-700 hover:text-indigo-600 hover:bg-white' }}">
                        <i class="fa-solid fa-list-check text-emerald-400"></i> আমার খবরসমূহ
                    </a>
                </div>
            @else
                <div class="flex items-center bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 gap-1 flex-wrap">
                    {{-- 1. Feed --}}
                    <a href="{{ route('news.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.index') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/80' }}">
                        <i class="fa-solid fa-newspaper text-indigo-500"></i> Feed
                    </a>

                    {{-- 2. Published --}}
                    <a href="{{ route('news.published') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.published') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/80' }}">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i> Published
                    </a>
                    
                    {{-- 3. Create --}}
                    @if(auth()->user()->hasPermission('can_direct_publish'))
                    <a href="{{ route('news.create') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.create') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/80' }}">
                        <i class="fa-solid fa-pen-to-square text-indigo-500"></i> Create
                    </a>
                    @endif
                    
                    {{-- 4. AI Drafts --}}
                    @if(auth()->user()->hasPermission('can_ai'))
                    <a href="{{ route('news.drafts') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.drafts') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/80' }}">
                        <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i> Drafts
                    </a>
                    @endif

                    {{-- 5. Observe / Sources --}}
                    @if(auth()->user()->hasPermission('can_scrape'))
                    <a href="{{ route('websites.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('websites.*') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/80' }}">
                        <i class="fa-solid fa-globe text-cyan-500"></i> Observe
                    </a>
                    @endif

                    {{-- 6. Templates Builder --}}
                    @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_templates'))
                    <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('admin.templates.*') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/80' }}">
                        <i class="fa-solid fa-layer-group text-purple-500"></i> Templates
                    </a>
                    @endif

                    {{-- 7. Team News & Reporters --}}
                    @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_reporters'))
                    <a href="{{ route('manage.reporters.news') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('manage.reporters.news') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/80 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                        <i class="fa-solid fa-satellite-dish text-rose-500"></i> Team News
                    </a>
                    <a href="{{ route('manage.reporters.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('manage.reporters.index') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/80' }}">
                        <i class="fa-solid fa-users text-blue-500"></i> Team
                    </a>
                    @endif
                </div>
            @endif
            @endauth
        </div>

        <div class="flex items-center gap-3">
            @auth
                <div class="hidden xl:flex items-center gap-3 border-r border-slate-200 pr-3">
                    <div class="text-[11px] font-extrabold uppercase tracking-wide text-slate-500" title="Today's Post Limit">
                        Limit: <span class="text-indigo-600 font-black">{{ auth()->user()->todays_post_count ?? 0 }}/{{ auth()->user()->daily_post_limit ?? 20 }}</span>
                    </div>
                    @if(auth()->user()->role !== 'reporter')
                    <a href="{{ route('credits.index') }}" class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-2.5 py-1 rounded-full text-xs font-bold border border-amber-200 transition-colors shadow-sm cursor-pointer hover:scale-105 transform">🪙 {{ auth()->user()->credits ?? 0 }}</a>
                    @endif
                </div>

                @if(auth()->user()->role !== 'reporter')
                <a href="{{ route('news.create') }}" class="hidden lg:flex items-center gap-1.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-3.5 py-2 rounded-xl text-xs font-extrabold shadow-md shadow-indigo-500/20 transition-all transform hover:-translate-y-0.5 shrink-0">
                    <i class="fa-solid fa-plus"></i> New Post
                </a>
                @endif

                {{-- User Profile Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs border border-indigo-200 uppercase shadow-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span class="font-bold text-xs text-slate-700 hidden 2xl:block">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-2 z-50">
                        <div class="px-4 py-2.5 border-b border-slate-100">
                            <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Signed in as</p>
                            <p class="text-sm font-extrabold text-slate-900 truncate">{{ auth()->user()->email }}</p>
                            <span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase mt-1">{{ auth()->user()->role }}</span>
                        </div>
                        
                        @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold"><i class="fa-solid fa-chart-pie text-indigo-500 w-4"></i> Admin Dashboard</a>
                        <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold"><i class="fa-solid fa-chart-line text-emerald-500 w-4"></i> Analytics</a>
                        <a href="{{ route('admin.scraper-monitor.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold"><i class="fa-solid fa-headset text-amber-500 w-4"></i> Scraper Monitor</a>
                        @endif

                        <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold"><i class="fa-solid fa-sliders text-slate-500 w-4"></i> Settings & API</a>
                        
                        <div class="border-t border-slate-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2.5 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 font-extrabold"><i class="fa-solid fa-right-from-bracket w-4"></i> Logout</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</nav>