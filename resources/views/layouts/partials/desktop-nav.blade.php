<nav class="hidden lg:block glass-nav sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 h-16 flex justify-between items-center">
        
        <div class="flex items-center gap-6 xl:gap-8">
            <a href="{{ auth()->user()->role === 'reporter' ? route('reporter.news.index') : route('news.index') }}" class="flex items-center gap-2.5 group">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/25 group-hover:scale-105 transition-transform font-black">
                    <i class="fa-solid fa-bolt text-base"></i>
                </div>
                <span class="font-extrabold text-xl tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors">Subeditor<span class="text-indigo-600">24</span></span>
            </a>

            @auth
            @if(auth()->user()->role === 'reporter')
                <div class="flex items-center bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 gap-1">
                    <a href="{{ route('reporter.news.create') }}" class="flex items-center gap-2 px-4 py-1.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('reporter.news.create') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-white' }}">
                        <i class="fa-solid fa-plus"></i> খবর পাঠান
                    </a>
                    <a href="{{ route('reporter.news.index') }}" class="flex items-center gap-2 px-4 py-1.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('reporter.news.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/20 scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-white' }}">
                        <i class="fa-solid fa-list-ul"></i> আমার খবরসমূহ
                    </a>
                </div>
            @else
                <div class="flex items-center bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/80 gap-1">
                    <a href="{{ route('news.index') }}" class="px-4 py-1.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('news.index') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-white/80' }}">Feed</a>
                    <a href="{{ route('news.published') }}" class="px-4 py-1.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('news.published') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-white/80' }}">Published</a>
                    
                    @if(auth()->user()->hasPermission('can_direct_publish'))
                    <a href="{{ route('news.create') }}" class="px-4 py-1.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('news.create') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-white/80' }}">Create</a>
                    @endif
                    
                    @if(auth()->user()->hasPermission('can_ai'))
                    <a href="{{ route('news.drafts') }}" class="px-4 py-1.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('news.drafts') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-white/80' }}">Drafts</a>
                    @endif

                    @if(auth()->user()->hasPermission('can_scrape'))
                    <a href="{{ route('websites.index') }}" class="px-4 py-1.5 rounded-xl text-sm font-bold transition-all duration-200 {{ request()->routeIs('websites.*') ? 'bg-white text-indigo-600 shadow-sm scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-white/80' }}">Observe</a>
                    @endif

                    @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_reporters'))
                    <div class="w-[1px] h-5 bg-slate-300 mx-1"></div>
                    <a href="{{ route('manage.reporters.news') }}" class="px-4 py-1.5 rounded-xl text-sm font-bold flex items-center gap-1.5 transition-all duration-200 {{ request()->routeIs('manage.reporters.news') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200/80 scale-[1.02]' : 'text-slate-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                        <i class="fa-solid fa-satellite-dish text-indigo-500"></i> Team News
                    </a>
                    @endif
                </div>
            @endif
            @endauth
        </div>

        <div class="flex items-center gap-3">
            @auth
                <div class="hidden xl:flex items-center gap-3 border-r border-slate-200 pr-4">
                    <div class="text-[11px] font-extrabold uppercase tracking-wide text-slate-500" title="Today's Post Limit">
                        Limit: <span class="text-indigo-600 font-black">{{ auth()->user()->todays_post_count ?? 0 }}/{{ auth()->user()->daily_post_limit ?? 20 }}</span>
                    </div>
                    @if(auth()->user()->role !== 'reporter')
                    <a href="{{ route('credits.index') }}" class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold border border-amber-200 transition-colors shadow-sm cursor-pointer hover:scale-105 transform">🪙 {{ auth()->user()->credits ?? 0 }}</a>
                    @endif
                </div>

                @if(auth()->user()->role !== 'reporter')
                <a href="{{ route('news.create') }}" class="hidden sm:flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white px-4 py-2 rounded-xl text-sm font-extrabold shadow-md shadow-indigo-500/20 transition-all transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus"></i> New Post
                </a>
                @endif

                {{-- User Profile Dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-xl hover:bg-slate-100 transition-colors">
                        <div class="w-8 h-8 rounded-xl bg-indigo-100 text-indigo-700 font-extrabold flex items-center justify-center text-xs border border-indigo-200 uppercase shadow-sm">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <span class="font-bold text-sm text-slate-700 hidden md:block">{{ auth()->user()->name }}</span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-slate-200/80 py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs font-bold text-slate-400 uppercase">Signed in as</p>
                            <p class="text-sm font-extrabold text-slate-900 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        
                        @if(auth()->user()->role === 'super_admin')
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-semibold"><i class="fa-solid fa-gauge"></i> Admin Dashboard</a>
                        @endif

                        <a href="{{ route('settings.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 font-semibold"><i class="fa-solid fa-gear"></i> Settings</a>
                        
                        <div class="border-t border-slate-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-sm text-rose-600 hover:bg-rose-50 font-bold"><i class="fa-solid fa-right-from-bracket"></i> Logout</button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</nav>