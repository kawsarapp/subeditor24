<header class="hidden lg:block sticky top-0 z-50 transition-all duration-300">
    <div class="glass-nav border-b border-slate-200/80 shadow-[0_4px_25px_-5px_rgba(15,23,42,0.06)]">
        <div class="max-w-[1440px] mx-auto px-3 xl:px-6 h-16 flex items-center justify-between gap-1.5 xl:gap-3 min-w-0">
            
            {{-- BRAND & LOGO --}}
            <div class="flex items-center gap-2 xl:gap-4 shrink-0">
                <a href="{{ auth()->check() ? (auth()->user()->role === 'reporter' ? route('reporter.news.index') : route('news.index')) : route('login') }}" class="flex items-center gap-2 group">
                    <div class="w-8 h-8 xl:w-9 xl:h-9 rounded-xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-violet-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/30 group-hover:scale-105 transition-transform duration-300 font-black">
                        <i class="fa-solid fa-bolt text-sm xl:text-base"></i>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-black text-base xl:text-lg tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors leading-none">Subeditor<span class="text-indigo-600">24</span></span>
                        <span class="text-[9px] font-extrabold text-slate-400 tracking-wider uppercase mt-1 hidden 2xl:flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Newsroom CMS
                        </span>
                    </div>
                </a>
            </div>

            {{-- CENTER NAVIGATION PILLS (CLEAN, COMPACT & RESPONSIVE) --}}
            @auth
            <div class="flex items-center py-1 min-w-0">
                @if(auth()->user()->role === 'reporter')
                    <div class="flex items-center bg-slate-100/90 dark:bg-slate-800/80 p-1 xl:p-1.5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 gap-1 xl:gap-1.5 shadow-inner">
                        <a href="{{ route('reporter.news.create') }}" class="flex items-center gap-1.5 px-3 xl:px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('reporter.news.create') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25 scale-[1.02]' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-white dark:hover:bg-slate-700' }}">
                            <i class="fa-solid fa-pen-to-square"></i> Submit News
                        </a>
                        <a href="{{ route('reporter.news.index') }}" class="flex items-center gap-1.5 px-3 xl:px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('reporter.news.index') ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25 scale-[1.02]' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-white dark:hover:bg-slate-700' }}">
                            <i class="fa-solid fa-list-check"></i> My Articles
                        </a>
                    </div>
                @else
                    @php
                        $isToolsActive = request()->routeIs('free-photocard.*') 
                            || request()->routeIs('custom-photo-card.*')
                            || request()->routeIs('admin.templates.*')
                            || request()->routeIs('youtube.*')
                            || request()->routeIs('websites.*')
                            || request()->routeIs('trending.*')
                            || request()->routeIs('seo.*')
                            || request()->routeIs('client.staff.*')
                            || request()->routeIs('manage.reporters.*')
                            || request()->routeIs('admin.scraper-monitor*')
                            || request()->routeIs('admin.analytics.*')
                            || request()->routeIs('admin.posts.*')
                            || request()->routeIs('feedback.*');
                    @endphp
                    <div class="flex items-center bg-slate-100/90 dark:bg-slate-800/80 p-1 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 gap-0.5 xl:gap-1 shadow-inner shrink-0">
                        {{-- 1. Feed --}}
                        <a href="{{ route('news.index') }}" class="flex items-center gap-1 px-2.5 xl:px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.index') ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md border border-slate-200/60 dark:border-slate-600 scale-[1.02]' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-white/60 dark:hover:bg-slate-700/60' }}">
                            <span class="hidden xl:inline">Latest </span><span>News</span>
                        </a>

                        {{-- 1.5 Central Live Wire Feed --}}
                        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_central_feed'))
                        <a href="{{ route('central-feed.index') }}" class="flex items-center gap-1.5 px-2.5 xl:px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('central-feed.*') ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-md scale-[1.02]' : 'text-indigo-700 dark:text-indigo-400 hover:text-indigo-800 hover:bg-white/60 dark:hover:bg-slate-700/60' }}" title="Central Live Wire Feed">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Live Wire</span>
                        </a>
                        @endif

                        {{-- 2. Published --}}
                        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_view_published'))
                        <a href="{{ route('news.published') }}" class="flex items-center gap-1 px-2.5 xl:px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.published') ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md border border-slate-200/60 dark:border-slate-600 scale-[1.02]' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-white/60 dark:hover:bg-slate-700/60' }}">
                            <span>Published</span>
                        </a>
                        @endif
                        
                        {{-- 3. Create --}}
                        @if(auth()->user()->hasPermission('can_direct_publish'))
                        <a href="{{ route('news.create') }}" class="flex items-center gap-1 px-2.5 xl:px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.create') ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md border border-slate-200/60 dark:border-slate-600 scale-[1.02]' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-white/60 dark:hover:bg-slate-700/60' }}">
                            <span>Create</span><span class="hidden xl:inline"> Post</span>
                        </a>
                        @endif
                        
                        {{-- 4. Drafts --}}
                        @if(auth()->user()->hasPermission('can_ai'))
                        <a href="{{ route('news.drafts') }}" class="flex items-center gap-1 px-2.5 xl:px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ request()->routeIs('news.drafts') ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md border border-slate-200/60 dark:border-slate-600 scale-[1.02]' : 'text-slate-700 dark:text-slate-300 hover:text-indigo-600 hover:bg-white/60 dark:hover:bg-slate-700/60' }}">
                            <span>Drafts</span>
                        </a>
                        @endif

                        {{-- VERTICAL DIVIDER --}}
                        <div class="w-[1px] h-4 bg-slate-300 dark:bg-slate-600 mx-0.5 xl:mx-1"></div>

                        {{-- EXTRA TOOLS DROPDOWN --}}
                        <div class="relative">
                            <button id="toolsMenuBtn" onclick="toggleToolsDropdown(event)" class="flex items-center gap-1.5 px-2.5 xl:px-3 py-1.5 rounded-xl text-xs font-extrabold transition-all duration-200 {{ $isToolsActive ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/25' : 'text-indigo-700 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700' }} cursor-pointer">
                                <i class="fa-solid fa-toolbox text-xs {{ $isToolsActive ? 'text-white' : 'text-indigo-500' }}"></i>
                                <span>Tools</span>
                                <i class="fa-solid fa-chevron-down text-[9px] ml-0.5 opacity-80"></i>
                            </button>

                            <div id="toolsMenuDropdown" class="hidden absolute left-0 mt-2 w-72 bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border border-slate-200/90 dark:border-slate-800 py-2 z-[100] max-h-[80vh] overflow-y-auto custom-scrollbar">
                                {{-- Group 1: Creative & Studio --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_custom_photo_card') || auth()->user()->hasPermission('can_studio') || auth()->user()->hasPermission('manage_templates') || auth()->user()->hasPermission('can_youtube_automate'))
                                <div class="px-3.5 pt-1.5 pb-1">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Creative & Automation</span>
                                </div>

                                {{-- Quick Card Generator --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_custom_photo_card') || auth()->user()->hasPermission('can_studio'))
                                <a href="{{ route('free-photocard.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('free-photocard.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-clone text-amber-500 w-4 text-center"></i>
                                        <span>Quick Card Generator</span>
                                    </div>
                                    @if(request()->routeIs('free-photocard.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>

                                {{-- Custom Photo Card Studio --}}
                                <a href="{{ route('custom-photo-card.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('custom-photo-card.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-paintbrush text-violet-500 w-4 text-center"></i>
                                        <span>Photo Card Studio</span>
                                    </div>
                                    @if(request()->routeIs('custom-photo-card.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                {{-- Photocard Templates --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_templates'))
                                @if(Route::has('admin.templates.index'))
                                <a href="{{ route('admin.templates.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('admin.templates.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-layer-group text-purple-500 w-4 text-center"></i>
                                        <span>Card Templates</span>
                                    </div>
                                    @if(request()->routeIs('admin.templates.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif
                                @endif

                                {{-- YouTube AI Automation --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_youtube_automate'))
                                <a href="{{ route('youtube.channels.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('youtube.*') ? 'bg-red-50 dark:bg-red-950/60 text-red-600 font-extrabold' : 'text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-brands fa-youtube text-red-600 w-4 text-center text-sm"></i>
                                        <span>YouTube AI Studio</span>
                                    </div>
                                    @if(request()->routeIs('youtube.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-600"></span>
                                    @endif
                                </a>
                                @endif

                                {{-- Divider --}}
                                <div class="my-1.5 border-t border-slate-100 dark:border-slate-800"></div>
                                @endif

                                {{-- Group 2: Editorial & Intelligence --}}
                                <div class="px-3.5 pt-1.5 pb-1">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Editorial & Intelligence</span>
                                </div>

                                {{-- Sources --}}
                                @if(auth()->user()->hasPermission('can_scrape'))
                                <a href="{{ route('websites.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('websites.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-globe text-indigo-500 w-4 text-center"></i>
                                        <span>News Sources (Scrapers)</span>
                                    </div>
                                    @if(request()->routeIs('websites.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                {{-- Trending Stories --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_viral_predictor'))
                                <a href="{{ route('trending.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('trending.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-fire text-amber-500 w-4 text-center"></i>
                                        <span>Trending Stories</span>
                                    </div>
                                    @if(request()->routeIs('trending.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                {{-- SEO Intelligence --}}
                                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_intelligence'))
                                @if(Route::has('seo.index'))
                                <a href="{{ route('seo.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('seo.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-chart-simple text-indigo-500 w-4 text-center"></i>
                                        <span>SEO & Traffic Insights</span>
                                    </div>
                                    @if(request()->routeIs('seo.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif
                                @endif

                                {{-- Divider --}}
                                <div class="my-1.5 border-t border-slate-100 dark:border-slate-800"></div>

                                {{-- Group 3: Management & Roles --}}
                                <div class="px-3.5 pt-1.5 pb-1">
                                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 dark:text-slate-500">Management & System</span>
                                </div>

                                @php
                                    $userPerms = is_array(auth()->user()->permissions) ? auth()->user()->permissions : (json_decode(auth()->user()->permissions ?? '[]', true) ?? []);
                                    $canStaff = in_array('can_manage_staff', $userPerms) || auth()->user()->role === 'super_admin' || auth()->user()->role === 'client';
                                    $canReporters = auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_reporters');
                                @endphp

                                @if($canStaff && Route::has('client.staff.index'))
                                <a href="{{ route('client.staff.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('client.staff.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-users-gear text-indigo-500 w-4 text-center"></i>
                                        <span>Staff & Roles</span>
                                    </div>
                                    @if(request()->routeIs('client.staff.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                @if($canReporters && Route::has('manage.reporters.index'))
                                <a href="{{ route('manage.reporters.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('manage.reporters.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-users text-indigo-500 w-4 text-center"></i>
                                        <span>Reporters & Submissions</span>
                                    </div>
                                    @if(request()->routeIs('manage.reporters.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                @if(Route::has('admin.analytics.index') && (auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_analytics')))
                                <a href="{{ route('admin.analytics.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-chart-line text-emerald-500 w-4 text-center"></i>
                                        <span>Analytics & Reports</span>
                                    </div>
                                    @if(request()->routeIs('admin.analytics.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                @if(Route::has('admin.posts.index') && auth()->user()->role === 'super_admin')
                                <a href="{{ route('admin.posts.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('admin.posts.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-clock-rotate-left text-blue-500 w-4 text-center"></i>
                                        <span>Publish Logs</span>
                                    </div>
                                    @if(request()->routeIs('admin.posts.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                @if(Route::has('admin.scraper-monitor') && auth()->user()->role === 'super_admin')
                                <a href="{{ route('admin.scraper-monitor') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('admin.scraper-monitor*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-heart-pulse text-rose-500 w-4 text-center"></i>
                                        <span>Scraper Health Monitor</span>
                                    </div>
                                    @if(request()->routeIs('admin.scraper-monitor*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                                @endif

                                <a href="{{ route('feedback.index') }}" class="flex items-center justify-between px-3.5 py-2 mx-1.5 rounded-xl text-xs font-bold transition-colors {{ request()->routeIs('feedback.*') ? 'bg-indigo-50 dark:bg-indigo-950/70 text-indigo-600 dark:text-indigo-400 font-extrabold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-lightbulb text-emerald-500 w-4 text-center"></i>
                                        <span>Feedback & Roadmap</span>
                                    </div>
                                    @if(request()->routeIs('feedback.*'))
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600 dark:bg-indigo-400"></span>
                                    @endif
                                </a>
                            </div>
                        </div>

                    </div>
                @endif
            </div>
            @endauth

            {{-- RIGHT CONTROL PANEL WITH USER PROFILE & CREDITS --}}
            <div class="flex items-center gap-2 shrink-0">
                @auth
                    {{-- 👑 Ultra-Compact Subscription Validity Badge --}}
                    @if(auth()->user()->role !== 'reporter')
                        @php
                            $subStatus = auth()->user()->subscription_status ?? 'active';
                            $daysRem = auth()->user()->days_remaining;
                            $isExp = auth()->user()->isExpired();
                            $isSoon = auth()->user()->isExpiringSoon();
                            $planName = auth()->user()->pricingPlan ? auth()->user()->pricingPlan->name : (auth()->user()->role === 'super_admin' ? 'Unlimited Admin' : 'Active Plan');
                        @endphp
                        <a href="{{ route('billing.my-subscription') }}" class="flex items-center gap-1 px-2.5 py-1 rounded-xl text-xs font-black transition-all shadow-sm border {{ $isExp ? 'bg-rose-50 dark:bg-rose-950/60 text-rose-600 border-rose-300 dark:border-rose-800 animate-pulse' : ($isSoon ? 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 border-amber-300 dark:border-amber-800' : 'bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100') }}" title="প্ল্যান: {{ $planName }} ({{ $daysRem }} দিন বাকি)">
                            <i class="fa-solid {{ $isExp ? 'fa-triangle-exclamation text-rose-500' : 'fa-crown text-amber-500' }} text-[11px]"></i>
                            <span class="text-[11px] font-black">
                                @if($daysRem >= 999)
                                    Unlimited
                                @elseif($isExp)
                                    Expired
                                @else
                                    {{ $daysRem }}d
                                @endif
                            </span>
                        </a>
                    @endif

                    {{-- 🌙 Dark Mode Toggle Button --}}
                    <button type="button" onclick="toggleDarkMode()" id="darkModeToggleBtn" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-amber-400 flex items-center justify-center text-xs transition border border-slate-200 dark:border-slate-700 cursor-pointer shadow-sm shrink-0" title="Toggle Dark / Light Mode">
                        <i id="darkModeIcon" class="fa-solid fa-moon"></i>
                    </button>

                    {{-- PROFILE DROPDOWN WITH CREDITS INSIDE --}}
                    <div class="relative shrink-0">
                        <button id="userProfileBtn" onclick="toggleUserProfileDropdown(event)" class="relative flex items-center gap-1.5 p-1 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800 border border-transparent hover:border-slate-200 dark:hover:border-slate-700 transition-all cursor-pointer">
                            <div class="relative w-8 h-8 xl:w-9 xl:h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-black flex items-center justify-center text-xs xl:text-sm border border-indigo-400 uppercase shadow-md shadow-indigo-500/20">
                                {{ substr(auth()->user()->name, 0, 1) }}
                                @if(auth()->user()->role === 'super_admin' && \App\Models\SubscriptionOrder::where('status', 'pending')->count() > 0)
                                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full animate-ping"></span>
                                    <span class="absolute -top-1 -right-1 w-3 h-3 bg-rose-500 border-2 border-white rounded-full"></span>
                                @endif
                            </div>
                            <i class="fa-solid fa-chevron-down text-[9px] text-slate-400"></i>
                        </button>

                        <div id="userProfileMenu" class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200/90 dark:border-slate-800 py-2.5 z-[100]">
                            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 rounded-t-3xl">
                                <p class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Signed in as</p>
                                <p class="text-xs font-extrabold text-slate-900 dark:text-white truncate">{{ auth()->user()->email }}</p>
                                <div class="mt-2 flex items-center justify-between gap-1">
                                    <span class="inline-block bg-indigo-50 dark:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 text-[10px] px-2.5 py-0.5 rounded-full font-extrabold uppercase border border-indigo-200/60 dark:border-indigo-800">{{ auth()->user()->role }}</span>
                                    
                                    {{-- CREDITS INSIDE PROFILE DROPDOWN --}}
                                    @if(auth()->user()->role !== 'reporter')
                                    <a href="{{ route('credits.index') }}" class="bg-amber-50 dark:bg-amber-950/70 hover:bg-amber-100 text-amber-800 dark:text-amber-300 px-2.5 py-0.5 rounded-full text-[10px] font-extrabold border border-amber-200 dark:border-amber-800 transition-all flex items-center gap-1">
                                        🪙 {{ auth()->user()->credits ?? 0 }} Credits
                                    </a>
                                    @endif
                                </div>
                            </div>

                            {{-- 🎯 DYNAMIC GOAL & TARGET PROGRESS TRACKER INSIDE DROPDOWN --}}
                            @if(auth()->user()->role !== 'reporter')
                            @php
                                $userLimitType = auth()->user()->post_limit_type ?? 'daily';
                                $isMonthlyLimit = ($userLimitType === 'monthly');
                                $activePosts = $isMonthlyLimit ? (auth()->user()->this_month_post_count ?? 0) : (auth()->user()->todays_post_count ?? 0);
                                $targetLimit = auth()->user()->active_post_limit ?? 20;
                                $isUnlimited = $targetLimit >= 9999;
                                $percent = $isUnlimited ? 100 : min(100, round(($activePosts / max($targetLimit, 1)) * 100));
                                $trackerTitle = $isMonthlyLimit ? "This Month's Posts" : "Today's Posts";
                            @endphp
                            <div class="px-4 py-2.5 bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800">
                                <div class="flex items-center justify-between text-[10px] font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                                    <span class="flex items-center gap-1"><i class="fa-solid fa-bullseye text-indigo-500"></i> {{ $trackerTitle }}</span>
                                    <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ $activePosts }} @if(!$isUnlimited)/ {{ $targetLimit }} ({{ $percent }}%)@endif</span>
                                </div>
                                @if(!$isUnlimited)
                                <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-indigo-500 to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                                </div>
                                @endif
                            </div>
                            @endif

                                {{-- MY SUBSCRIPTION & PLAN MANAGEMENT --}}
                                @if(auth()->user()->role !== 'reporter')
                                <a href="{{ route('billing.my-subscription') }}" class="flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-file-invoice-dollar text-indigo-500 w-4"></i>
                                        <span>আমার সাবস্ক্রিপশন ও বিলিং</span>
                                    </div>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full {{ $isExp ? 'bg-rose-100 text-rose-600' : 'bg-indigo-100 text-indigo-700' }}">
                                        {{ $daysRem >= 999 ? 'Active' : ($isExp ? 'Expired' : $daysRem . 'd') }}
                                    </span>
                                </a>
                                @endif
                            
                                @if(auth()->user()->role === 'super_admin')
                                @php
                                    $pendingOrdersCount = \App\Models\SubscriptionOrder::where('status', 'pending')->count();
                                @endphp
                                <div class="border-t border-slate-100 my-1"></div>
                                <div class="px-4 pt-1 pb-0.5 text-[9px] font-extrabold uppercase text-slate-400">Admin Control</div>
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-shield-halved text-indigo-500 w-4"></i> Admin Panel
                                </a>
                                <a href="{{ route('admin.billing.orders') }}" class="flex items-center justify-between px-4 py-2 text-xs font-black text-indigo-700 hover:bg-indigo-50 transition-colors">
                                    <div class="flex items-center gap-2.5">
                                        <i class="fa-solid fa-money-check-dollar text-indigo-600 w-4"></i>
                                        <span>বিলিং ও পেমেন্ট অর্ডার</span>
                                    </div>
                                    @if($pendingOrdersCount > 0)
                                        <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-black leading-none text-white bg-rose-600 rounded-full animate-pulse shadow-sm">
                                            {{ $pendingOrdersCount }}
                                        </span>
                                    @endif
                                </a>
                                <a href="{{ route('admin.billing.payment-settings') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-wallet text-slate-500 w-4"></i> পেমেন্ট গেটওয়ে সেটিংস
                                </a>
                                <a href="{{ route('admin.pricing.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-tags text-indigo-500 w-4"></i> Regular Pricing & Coupons
                                </a>
                                <a href="{{ route('admin.vip-pricing.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-black text-amber-700 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                                    <i class="fa-solid fa-crown text-amber-500 w-4"></i> VIP Pricing Manager
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                @endif

                                @php
                                    $pricingNavConfig = \App\Http\Controllers\PricingController::getPageConfig();
                                    $isSuperAdmin = auth()->user()->role === 'super_admin';
                                @endphp

                                @if(!$pricingNavConfig['hide_pricing_from_nav'] || $isSuperAdmin)
                                <a href="{{ route('pricing.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-gem text-purple-500 w-4"></i> Special Pricing Plans
                                </a>
                                @endif

                                @if(!$pricingNavConfig['hide_vip_pricing_from_nav'] || $isSuperAdmin)
                                <a href="{{ route('pricing.vip') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-amber-700 hover:bg-amber-50 transition-colors">
                                    <i class="fa-solid fa-crown text-amber-500 w-4"></i> 👑 VIP Enterprise Plans
                                </a>
                                @endif

                                @if(Route::has('settings.index') && (auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings')))
                                <a href="{{ route('settings.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                    <i class="fa-solid fa-gear text-slate-500 w-4"></i> System Settings
                                </a>
                                @endif

                                <a href="{{ route('feedback.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 transition-colors">
                                    <i class="fa-solid fa-lightbulb text-emerald-500 w-4"></i> Feature Requests & Roadmap
                                </a>

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
                @else
                    @php
                        $pricingNavConfig = \App\Http\Controllers\PricingController::getPageConfig();
                    @endphp

                    {{-- 🌙 Dark Mode Toggle Button for Guests --}}
                    <button type="button" onclick="toggleDarkMode()" id="darkModeToggleBtnGuest" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-amber-400 flex items-center justify-center text-xs transition border border-slate-200 dark:border-slate-700 cursor-pointer shadow-sm" title="Toggle Dark / Light Mode">
                        <i class="fa-solid fa-moon"></i>
                    </button>

                    <div class="flex items-center gap-2">
                        @if(!$pricingNavConfig['hide_pricing_from_nav'])
                        <a href="{{ route('pricing') }}" class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-indigo-600 px-3 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <i class="fa-solid fa-tags text-indigo-500 mr-1"></i> প্রাইসিং
                        </a>
                        @endif

                        @if(!$pricingNavConfig['hide_vip_pricing_from_nav'])
                        <a href="{{ route('pricing.vip') }}" class="text-xs font-black text-amber-600 dark:text-amber-400 hover:text-amber-500 px-3 py-2 rounded-xl hover:bg-amber-50 dark:hover:bg-slate-800 transition flex items-center gap-1">
                            <i class="fa-solid fa-crown text-amber-500 text-xs"></i> VIP Pricing
                        </a>
                        @endif

                        <a href="{{ route('login') }}" class="text-xs font-bold text-slate-700 dark:text-slate-300 hover:text-indigo-600 px-3.5 py-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                            <i class="fa-solid fa-arrow-right-to-bracket mr-1"></i> লগইন
                        </a>
                        <a href="{{ route('register') }}" class="text-xs font-extrabold text-white bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 px-4 py-2 rounded-xl shadow-md shadow-indigo-500/20 transition transform hover:-translate-y-0.5">
                            <i class="fa-solid fa-rocket mr-1"></i> ৭ দিনের ফ্রি ট্রায়াল
                        </a>
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