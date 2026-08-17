<header class="hidden lg:block sticky top-0 z-50 transition-all duration-300">
    <div class="glass-nav border-b border-slate-200/80 shadow-[0_4px_25px_-5px_rgba(15,23,42,0.06)]">
        <div class="max-w-[1440px] mx-auto px-4 xl:px-6 h-16 flex items-center justify-between gap-3">
            
            {{-- BRAND & LOGO --}}
            <div class="flex items-center gap-4 xl:gap-6 shrink-0">
                <a href="{{ auth()->user()->role === 'reporter' ? route('reporter.news.index') : route('news.index') }}" class="flex items-center gap-2.5 group">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform duration-300 font-black">
                        <i class="fa-solid fa-bolt text-lg"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-xl tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors leading-none">Subeditor<span class="text-indigo-600">24</span></span>
                        <span class="text-[9px] font-extrabold text-slate-400 tracking-wider uppercase mt-1 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Automation Suite
                        </span>
                    </div>
                </a>
            </div>

            {{-- CENTER NAVIGATION PILLS --}}
            @auth
            <div class="flex items-center overflow-x-auto custom-scrollbar max-w-full py-1">
                @if(auth()->user()->role === 'reporter')
                    <div class="flex items-center bg-slate-100/90 p-1.5 rounded-2xl border border-slate-200/80 gap-1.5 shadow-inner">
                        <a href="{{ route('reporter.news.create') }}" class="flex items-center gap-2 px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('reporter.news.create') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white' }}">
                            <i class="fa-solid fa-pen-to-square"></i> খবর পাঠান
                        </a>
                        <a href="{{ route('reporter.news.index') }}" class="flex items-center gap-2 px-4 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('reporter.news.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white' }}">
                            <i class="fa-solid fa-list-check"></i> আমার খবরসমূহ
                        </a>
                    </div>
                @else
                    <div class="flex items-center bg-slate-100/90 p-1.5 rounded-2xl border border-slate-200/80 gap-1 shadow-inner shrink-0">
                        {{-- 1. Feed --}}
                        <a href="{{ route('news.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.index') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            <i class="fa-solid fa-newspaper text-indigo-500"></i> Feed
                        </a>

                        {{-- 2. Published --}}
                        <a href="{{ route('news.published') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.published') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            <i class="fa-solid fa-circle-check text-emerald-500"></i> Published
                        </a>
                        
                        {{-- 3. Create --}}
                        @if(auth()->user()->hasPermission('can_direct_publish'))
                        <a href="{{ route('news.create') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.create') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            <i class="fa-solid fa-pen-to-square text-indigo-500"></i> Create
                        </a>
                        @endif
                        
                        {{-- 4. AI Drafts --}}
                        @if(auth()->user()->hasPermission('can_ai'))
                        <a href="{{ route('news.drafts') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.drafts') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            <i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i> Drafts
                        </a>
                        @endif

                        {{-- 5. Observe / Sources --}}
                        @if(auth()->user()->hasPermission('can_scrape'))
                        <a href="{{ route('websites.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('websites.*') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            <i class="fa-solid fa-globe text-cyan-500"></i> Observe
                        </a>
                        @endif

                        {{-- 6. Card Templates --}}
                        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_templates'))
                        @if(Route::has('admin.templates.index'))
                        <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('admin.templates.*') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            <i class="fa-solid fa-layer-group text-purple-500"></i> Templates
                        </a>
                        @endif
                        @endif

                        {{-- 7. Team News & Team Members --}}
                        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_reporters'))
                        <div class="w-[1px] h-5 bg-slate-300 mx-1"></div>
                        @if(Route::has('manage.reporters.news'))
                        <a href="{{ route('manage.reporters.news') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('manage.reporters.news') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                            <i class="fa-solid fa-satellite-dish text-rose-500"></i> Team News
                        </a>
                        @endif
                        @if(Route::has('manage.reporters.index'))
                        <a href="{{ route('manage.reporters.index') }}" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('manage.reporters.index') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            <i class="fa-solid fa-users text-blue-500"></i> Team
                        </a>
                        @endif
                        @endif
                    </div>
                @endif
            </div>
            @endauth

            {{-- RIGHT CONTROL PANEL WITH DROPDOWN --}}
            <div class="flex items-center gap-3 shrink-0">
                @auth
                    {{-- Daily Limit Indicator --}}
                    <div class="hidden xl:flex items-center gap-3 border-r border-slate-200 pr-3">
                        <div class="bg-indigo-50/80 border border-indigo-100 px-3 py-1.5 rounded-xl flex items-center gap-2 shadow-sm" title="Today's Post Limit">
                            <i class="fa-solid fa-chart-simple text-indigo-500 text-xs"></i>
                            <span class="text-[11px] font-extrabold text-slate-700 uppercase tracking-wide">
                                Limit: <span class="text-indigo-600 font-black">{{ auth()->user()->todays_post_count ?? 0 }}/{{ auth()->user()->daily_post_limit ?? 20 }}</span>
                            </span>
                        </div>

                        {{-- Credit Balance Badge --}}
                        @if(auth()->user()->role !== 'reporter')
                        <a href="{{ route('credits.index') }}" class="bg-amber-50 hover:bg-amber-100 text-amber-800 px-3 py-1.5 rounded-xl text-xs font-extrabold border border-amber-200/80 transition-all shadow-sm flex items-center gap-1.5 hover:scale-105 transform">
                            <span class="text-sm">🪙</span> {{ auth()->user()->credits ?? 0 }} Credits
                        </a>
                        @endif
                    </div>

                    {{-- CTA Post Button --}}
                    @if(auth()->user()->role !== 'reporter')
                    <a href="{{ route('news.create') }}" class="hidden lg:flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-4 py-2 rounded-xl text-xs font-extrabold shadow-md shadow-indigo-500/25 transition-all transform hover:-translate-y-0.5 active:scale-98">
                        <i class="fa-solid fa-plus text-xs"></i> New Post
                    </a>
                    @endif

                    {{-- 100% RELIABLE PROFILE DROPDOWN (ALPINJS + VANILLA JS HYBRID) --}}
                    <div class="relative" x-data="{ open: false }">
                        <button id="userProfileBtn" @click="open = !open" onclick="toggleUserProfileDropdown(event)" class="flex items-center gap-2 p-1.5 rounded-2xl hover:bg-slate-100 border border-transparent hover:border-slate-200 transition-all cursor-pointer">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-black flex items-center justify-center text-sm border border-indigo-400 uppercase shadow-md shadow-indigo-500/20">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="flex flex-col text-left hidden 2xl:block">
                                <span class="font-extrabold text-xs text-slate-900 leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[9px] font-bold text-indigo-600 uppercase">{{ auth()->user()->role }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div id="userProfileMenu" x-show="open" @click.away="open = false" x-transition class="hidden absolute right-0 mt-2 w-64 bg-white rounded-3xl shadow-2xl border border-slate-200/90 py-2.5 z-50">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 rounded-t-3xl">
                                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Signed in as</p>
                                <p class="text-sm font-extrabold text-slate-900 truncate">{{ auth()->user()->email }}</p>
                                <div class="mt-1">
                                    <span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] px-2.5 py-0.5 rounded-full font-extrabold uppercase border border-indigo-200/60">{{ auth()->user()->role }}</span>
                                </div>
                            </div>
                            
                            <div class="py-1">
                                @if(auth()->user()->role === 'super_admin')
                                @if(Route::has('admin.dashboard'))
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold transition-colors"><i class="fa-solid fa-chart-pie text-indigo-500 w-4 text-center"></i> Admin Dashboard</a>
                                @endif

                                @if(Route::has('admin.analytics.index'))
                                <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold transition-colors"><i class="fa-solid fa-chart-line text-emerald-500 w-4 text-center"></i> Analytics Report</a>
                                @endif

                                @if(Route::has('admin.scraper-monitor'))
                                <a href="{{ route('admin.scraper-monitor') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold transition-colors"><i class="fa-solid fa-headset text-amber-500 w-4 text-center"></i> Scraper Monitor</a>
                                @endif
                                @endif

                                <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-bold transition-colors"><i class="fa-solid fa-sliders text-slate-500 w-4 text-center"></i> Settings & API</a>
                            </div>
                            
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-3 px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50 font-extrabold transition-colors"><i class="fa-solid fa-right-from-bracket w-4 text-center"></i> Logout</button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</header>

<script>
    function toggleUserProfileDropdown(e) {
        if (e) e.stopPropagation();
        const menu = document.getElementById('userProfileMenu');
        if (!menu) return;
        
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    }

    document.addEventListener('click', function(e) {
        const btn = document.getElementById('userProfileBtn');
        const menu = document.getElementById('userProfileMenu');
        if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
            menu.classList.add('hidden');
        }
    });
</script>