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

            {{-- CENTER NAVIGATION PILLS (NO SCROLLBAR) --}}
            @auth
            <div class="flex items-center py-1">
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
                        <a href="{{ route('news.index') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.index') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            Feed
                        </a>

                        {{-- 2. Published --}}
                        <a href="{{ route('news.published') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.published') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            Published
                        </a>
                        
                        {{-- 3. Create --}}
                        @if(auth()->user()->hasPermission('can_direct_publish'))
                        <a href="{{ route('news.create') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.create') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            Create
                        </a>
                        @endif
                        
                        {{-- 4. Drafts --}}
                        @if(auth()->user()->hasPermission('can_ai'))
                        <a href="{{ route('news.drafts') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.drafts') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            Drafts
                        </a>
                        @endif

                        {{-- 5. Observe --}}
                        @if(auth()->user()->hasPermission('can_scrape'))
                        <a href="{{ route('websites.index') }}" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('websites.*') ? 'bg-white text-indigo-600 shadow-md border border-slate-200/60 scale-[1.02]' : 'text-slate-700 hover:text-indigo-600 hover:bg-white/60' }}">
                            Observe
                        </a>
                        @endif

                        {{-- VERTICAL DIVIDER --}}
                        <div class="w-[1px] h-4 bg-slate-300 mx-1"></div>

                        {{-- EXTRA TOOLS DROPDOWN --}}
                        <div class="relative">
                            <button id="toolsMenuBtn" onclick="toggleToolsDropdown(event)" class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('trending.*') || request()->routeIs('admin.templates.*') || request()->routeIs('manage.reporters.*') || request()->routeIs('admin.analytics.*') || request()->routeIs('admin.posts.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-indigo-700 hover:bg-indigo-50' }} cursor-pointer">
                                <i class="fa-solid fa-grid-2 text-indigo-500"></i>
                                <span>Tools & AI</span>
                                <i class="fa-solid fa-chevron-down text-[10px] ml-0.5"></i>
                            </button>

                            <div id="toolsMenuDropdown" class="hidden absolute left-0 mt-2 w-60 bg-white rounded-2xl shadow-2xl border border-slate-200/90 py-2.5 z-[100]">
                                {{-- 1. AI Viral Predictor --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_viral_predictor'))
                                <a href="{{ route('trending.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition-colors">
                                    <i class="fa-solid fa-fire text-amber-500 w-4 text-center"></i>
                                    <span>AI Viral Predictor</span>
                                </a>
                                @endif

                                {{-- SEO Intelligence --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_intelligence'))
                                @if(Route::has('seo.index'))
                                <a href="{{ route('seo.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                    <i class="fa-solid fa-magnifying-glass-chart text-indigo-500 w-4 text-center"></i>
                                    <span>SEO & Web Intelligence</span>
                                </a>
                                @endif
                                @endif

                                {{-- 2. Photocard Templates --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_templates'))
                                @if(Route::has('admin.templates.index'))
                                <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-purple-50 hover:text-purple-700 transition-colors">
                                    <i class="fa-solid fa-layer-group text-purple-500 w-4 text-center"></i>
                                    <span>Photocard Studio</span>
                                </a>
                                @endif
                                @endif

                                {{-- 🎨 Custom Photo Card & AI BG Remover --}}
                                <a href="{{ route('custom-photo-card.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-violet-50 hover:text-violet-700 transition-colors">
                                    <i class="fa-solid fa-wand-magic-sparkles text-violet-500 w-4 text-center"></i>
                                    <span>Custom Photo Card (AI)</span>
                                </a>

                                {{-- 3. Analytics & ROI --}}
                                @if(Route::has('admin.analytics.index') && (auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_analytics')))
                                <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                    <i class="fa-solid fa-chart-line text-emerald-500 w-4 text-center"></i>
                                    <span>Analytics & ROI</span>
                                </a>
                                @endif

                                {{-- 4. Auto Post Logs --}}
                                @if(Route::has('admin.posts.index') && auth()->user()->role === 'super_admin')
                                <a href="{{ route('admin.posts.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                    <i class="fa-solid fa-clock-rotate-left text-blue-500 w-4 text-center"></i>
                                    <span>Auto Post Logs</span>
                                </a>
                                @endif

                                {{-- 5. Team Staff & Reporters --}}
                                @php
                                    $userPerms = is_array(auth()->user()->permissions) ? auth()->user()->permissions : (json_decode(auth()->user()->permissions, true) ?? []);
                                    $canStaff = in_array('can_manage_staff', $userPerms) || auth()->user()->role === 'super_admin' || auth()->user()->role === 'client';
                                    $canReporters = auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_reporters');
                                @endphp

                                @if($canStaff && Route::has('client.staff.index'))
                                <a href="{{ route('client.staff.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                    <i class="fa-solid fa-users-gear text-indigo-500 w-4 text-center"></i>
                                    <span>Staff Management</span>
                                </a>
                                @endif

                                @if($canReporters && Route::has('manage.reporters.index'))
                                <a href="{{ route('manage.reporters.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 transition-colors">
                                    <i class="fa-solid fa-users text-indigo-500 w-4 text-center"></i>
                                    <span>Team Members</span>
                                </a>
                                @endif
                                @if($canReporters && Route::has('manage.reporters.news'))
                                <a href="{{ route('manage.reporters.news') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-rose-50 hover:text-rose-700 transition-colors">
                                    <i class="fa-solid fa-satellite-dish text-rose-500 w-4 text-center"></i>
                                    <span>Team News Stream</span>
                                </a>
                                @endif

                                {{-- 6. Scraper Monitor --}}
                                @if(Route::has('admin.scraper-monitor') && auth()->user()->role === 'super_admin')
                                <a href="{{ route('admin.scraper-monitor') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-extrabold text-slate-700 hover:bg-amber-50 hover:text-amber-700 transition-colors">
                                    <i class="fa-solid fa-headset text-amber-500 w-4 text-center"></i>
                                    <span>Scraper Monitor</span>
                                </a>
                                @endif
                            </div>
                        </div>

                    </div>
                @endif
            </div>
            @endauth

            {{-- RIGHT CONTROL PANEL WITH USER PROFILE & CREDITS --}}
            <div class="flex items-center gap-3 shrink-0">
                @auth
                    {{-- 🎯 Daily Goal & Target Progress Tracker --}}
                    @php
                        $todayPosts = auth()->user()->todays_post_count ?? 0;
                        $dailyTarget = auth()->user()->daily_post_limit ?? 20;
                        $percent = min(100, round(($todayPosts / max($dailyTarget, 1)) * 100));
                    @endphp
                    <div class="hidden xl:flex items-center gap-2 bg-slate-50 dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700/80 px-3 py-1.5 rounded-2xl shadow-sm transition-all" title="দৈনিক পোস্টের লক্ষ্যমাত্রা ও অগ্রগতি">
                        <div class="flex flex-col gap-0.5">
                            <div class="flex items-center justify-between gap-3 text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <span class="flex items-center gap-1"><i class="fa-solid fa-bullseye text-indigo-500"></i> আজকের গোল</span>
                                <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ $todayPosts }}/{{ $dailyTarget }} ({{ $percent }}%)</span>
                            </div>
                            <div class="w-28 h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    </div>

                    {{-- 🌙 Dark Mode Toggle Button --}}
                    <button type="button" onclick="toggleDarkMode()" id="darkModeToggleBtn" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-amber-400 flex items-center justify-center text-xs transition border border-slate-200 dark:border-slate-700 cursor-pointer shadow-sm" title="ডার্ক / লাইট মোড পরিবর্তন">
                        <i id="darkModeIcon" class="fa-solid fa-moon"></i>
                    </button>

                    {{-- ⌨️ Keyboard Shortcuts Button --}}
                    <button type="button" onclick="openShortcutsModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center justify-center text-xs transition border border-slate-200 dark:border-slate-700 cursor-pointer shadow-sm" title="কিবোর্ড শর্টকাট (?)">
                        <i class="fa-solid fa-keyboard"></i>
                    </button>

                    {{-- PROFILE DROPDOWN WITH CREDITS INSIDE --}}
                    <div class="relative">
                        <button id="userProfileBtn" onclick="toggleUserProfileDropdown(event)" class="flex items-center gap-2 p-1.5 rounded-2xl hover:bg-slate-100 border border-transparent hover:border-slate-200 transition-all cursor-pointer">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-black flex items-center justify-center text-sm border border-indigo-400 uppercase shadow-md shadow-indigo-500/20">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="flex flex-col text-left hidden 2xl:block">
                                <span class="font-extrabold text-xs text-slate-900 leading-tight">{{ auth()->user()->name }}</span>
                                <span class="text-[9px] font-bold text-indigo-600 uppercase">{{ auth()->user()->role }}</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400"></i>
                        </button>

                        <div id="userProfileMenu" class="hidden absolute right-0 mt-2 w-64 bg-white rounded-3xl shadow-2xl border border-slate-200/90 py-2.5 z-[100]">
                            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/60 rounded-t-3xl">
                                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Signed in as</p>
                                <p class="text-sm font-extrabold text-slate-900 truncate">{{ auth()->user()->email }}</p>
                                <div class="mt-1 flex items-center justify-between gap-1">
                                    <span class="inline-block bg-indigo-50 text-indigo-700 text-[10px] px-2.5 py-0.5 rounded-full font-extrabold uppercase border border-indigo-200/60">{{ auth()->user()->role }}</span>
                                    
                                    {{-- CREDITS INSIDE PROFILE DROPDOWN --}}
                                    @if(auth()->user()->role !== 'reporter')
                                    <a href="{{ route('credits.index') }}" class="bg-amber-50 hover:bg-amber-100 text-amber-800 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border border-amber-200 transition-all flex items-center gap-1">
                                        🪙 {{ auth()->user()->credits ?? 0 }} Credits
                                    </a>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="py-1">
                                @if(auth()->user()->role === 'super_admin')
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-shield-halved text-indigo-500 w-4"></i> Admin Panel
                                </a>
                                @endif

                                @if(Route::has('settings.index') && (auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings')))
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-gear text-slate-500 w-4"></i> System Settings
                                </a>
                                @endif
                            </div>

                            <div class="border-t border-slate-100 pt-1 mt-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors text-left">
                                        <i class="fa-solid fa-right-from-bracket w-4"></i> Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endauth
            </div>

        </div>
    </div>
</header>

<script>
function toggleToolsDropdown(event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    const toolsMenu = document.getElementById('toolsMenuDropdown');
    const profileMenu = document.getElementById('userProfileMenu');
    if (profileMenu) profileMenu.classList.add('hidden');
    if (toolsMenu) {
        toolsMenu.classList.toggle('hidden');
    }
}

function toggleUserProfileDropdown(event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    const toolsMenu = document.getElementById('toolsMenuDropdown');
    const profileMenu = document.getElementById('userProfileMenu');
    if (toolsMenu) toolsMenu.classList.add('hidden');
    if (profileMenu) {
        profileMenu.classList.toggle('hidden');
    }
}

document.addEventListener('click', function(event) {
    const toolsMenu = document.getElementById('toolsMenuDropdown');
    const toolsBtn = document.getElementById('toolsMenuBtn');
    if (toolsMenu && toolsBtn && !toolsMenu.contains(event.target) && !toolsBtn.contains(event.target)) {
        toolsMenu.classList.add('hidden');
    }

    const profileMenu = document.getElementById('userProfileMenu');
    const profileBtn = document.getElementById('userProfileBtn');
    if (profileMenu && profileBtn && !profileMenu.contains(event.target) && !profileBtn.contains(event.target)) {
        profileMenu.classList.add('hidden');
    }
});
</script>