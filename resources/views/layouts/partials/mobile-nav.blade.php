{{-- MOBILE TOP HEADER --}}
<div class="lg:hidden fixed top-0 w-full z-40 glass-nav h-14 flex items-center justify-between px-4 shadow-sm border-b border-slate-200/80 transition-all">
    <a href="{{ auth()->check() ? (auth()->user()->role === 'reporter' ? route('reporter.news.index') : route('news.index')) : route('login') }}" class="flex items-center gap-2 group">
        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-sm font-black group-hover:scale-105 transition-transform"><i class="fa-solid fa-bolt text-xs"></i></div>
        <span class="font-extrabold text-lg text-slate-900 tracking-tight">Subeditor<span class="text-indigo-600">24</span></span>
    </a>
    @auth
    <div class="flex items-center gap-2">
        <button type="button" onclick="toggleDarkMode()" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-amber-400 flex items-center justify-center text-xs border border-slate-200 dark:border-slate-700 shadow-sm cursor-pointer" title="Dark Mode">
            <i class="fa-solid fa-moon"></i>
        </button>
        @if(auth()->user()->role !== 'reporter')
            <div class="bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 px-2.5 py-1 rounded-full text-xs font-bold border border-amber-200 dark:border-amber-800/60 shadow-sm">
                🪙 {{ auth()->user()->credits ?? 0 }}
            </div>
        @endif
        <button onclick="toggleMobileDrawer(event)" class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-extrabold text-xs border border-indigo-200 uppercase shadow-sm cursor-pointer">
            {{ substr(auth()->user()->name, 0, 1) }}
        </button>
    </div>
    @else
    @php
        $pricingNavConfig = \App\Http\Controllers\PricingController::getPageConfig();
    @endphp
    <div class="flex items-center gap-1.5">
        @if(!$pricingNavConfig['hide_vip_pricing_from_nav'])
        <a href="{{ route('pricing.vip') }}" class="text-xs font-black text-amber-600 bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-200/80 transition flex items-center gap-1">
            <i class="fa-solid fa-crown text-[10px]"></i> VIP
        </a>
        @endif
        <a href="{{ route('login') }}" class="text-xs font-bold text-slate-700 hover:text-indigo-600 bg-slate-100 px-3 py-1.5 rounded-xl transition">লগইন</a>
        <a href="{{ route('register') }}" class="text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 px-3 py-1.5 rounded-xl shadow-sm transition">ফ্রি ট্রায়াল</a>
    </div>
    @endauth
</div>

{{-- MOBILE BOTTOM NAVIGATION --}}
@auth
<div class="lg:hidden fixed bottom-0 left-0 w-full z-[90] pb-safe">
    @if(auth()->user()->role === 'reporter')
    <div class="glass-sheet grid grid-cols-3 items-center h-16 border-t border-slate-200/80 shadow-[0_-8px_25px_rgba(0,0,0,0.06)] px-2">
        <a href="{{ route('reporter.news.index') }}" class="flex flex-col items-center justify-center h-full gap-1 transition-all {{ request()->routeIs('reporter.news.index') ? 'text-indigo-600 transform -translate-y-1 font-bold' : 'text-slate-500 hover:text-slate-700' }}">
            <i class="fa-solid fa-list-check text-xl"></i><span class="text-[10px] font-bold">My News</span>
            @if(request()->routeIs('reporter.news.index')) <div class="w-1 h-1 bg-indigo-600 rounded-full absolute bottom-1"></div> @endif
        </a>
        <div class="relative flex justify-center h-full items-center">
            <a href="{{ route('reporter.news.create') }}" class="absolute -top-6 bg-gradient-to-tr from-indigo-600 to-violet-600 text-white w-[3.5rem] h-[3.5rem] rounded-full flex items-center justify-center shadow-[0_8px_20px_rgba(79,70,229,0.35)] border-4 border-slate-50 active:scale-95 transition-all font-black">
                <i class="fa-solid fa-plus text-2xl"></i>
            </a>
            <span class="absolute bottom-1 text-[10px] font-bold text-slate-600">Submit</span>
        </div>
        <button onclick="toggleMobileDrawer(event)" class="flex flex-col items-center justify-center h-full gap-1 text-slate-500 hover:text-slate-700 transition-colors relative cursor-pointer">
            <i class="fa-solid fa-bars-staggered text-xl"></i><span class="text-[10px] font-bold">Menu</span>
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
            <i class="fa-regular fa-file-lines text-xl"></i><span class="text-[10px] font-bold">Drafts</span>
            @if(request()->routeIs('news.drafts')) <div class="w-1 h-1 bg-indigo-600 rounded-full absolute bottom-1"></div> @endif
        </a>
        <button onclick="toggleMobileDrawer(event)" class="flex flex-col items-center justify-center h-full gap-1 text-slate-500 hover:text-slate-700 transition-colors relative cursor-pointer">
            <i class="fa-solid fa-bars-staggered text-xl"></i><span class="text-[10px] font-bold">Menu</span>
        </button>
    </div>
    @endif
</div>

{{-- MOBILE FULL SLIDE-OUT DRAWER MODAL --}}
<div id="mobileDrawerBackdrop" onclick="toggleMobileDrawer(event)" class="hidden lg:hidden fixed inset-0 bg-slate-950/60 backdrop-blur-sm z-[100] transition-opacity"></div>

<div id="mobileDrawerModal" class="hidden lg:hidden fixed top-0 right-0 w-[85%] max-w-[320px] h-full bg-white z-[101] shadow-2xl flex flex-col justify-between overflow-y-auto custom-scrollbar">
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
            <button onclick="toggleMobileDrawer(event)" class="w-8 h-8 rounded-full bg-white border border-slate-200 text-slate-500 flex items-center justify-center hover:bg-slate-100">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        {{-- Limit & Credits Status --}}
        @php
            $mDaysRem = auth()->user()->days_remaining;
            $mIsExp = auth()->user()->isExpired();
            $mIsSoon = auth()->user()->isExpiringSoon();
        @endphp
        <div class="p-3.5 bg-indigo-50/70 dark:bg-slate-800/80 border-b border-slate-100 dark:border-slate-700 space-y-2">
            <div class="flex justify-between items-center text-xs">
                <span class="font-bold text-slate-600 dark:text-slate-300">Today's Limit: <span class="text-indigo-600 dark:text-indigo-400 font-black">{{ auth()->user()->todays_post_count ?? 0 }}/{{ auth()->user()->daily_post_limit ?? 20 }}</span></span>
                @if(auth()->user()->role !== 'reporter')
                <a href="{{ route('credits.index') }}" class="bg-amber-100 text-amber-800 dark:bg-amber-900/60 dark:text-amber-300 px-2.5 py-0.5 rounded-full text-[11px] font-bold border border-amber-200 dark:border-amber-700">🪙 {{ auth()->user()->credits ?? 0 }} Credits</a>
                @endif
            </div>
            @if(auth()->user()->role !== 'reporter')
            <div class="pt-1.5 border-t border-indigo-100 dark:border-slate-700 flex justify-between items-center text-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium">প্ল্যানের মেয়াদ:</span>
                <a href="{{ route('billing.my-subscription') }}" class="font-black px-2 py-0.5 rounded-md text-[11px] {{ $mIsExp ? 'bg-rose-100 text-rose-700 animate-pulse' : ($mIsSoon ? 'bg-amber-100 text-amber-800' : 'bg-indigo-100 text-indigo-700') }}">
                    @if($mDaysRem >= 999)
                        👑 Unlimited
                    @elseif($mIsExp)
                        ⛔ মেয়াদ শেষ (রিনিউ করুন)
                    @else
                        ⏳ {{ $mDaysRem }} দিন বাকি
                    @endif
                </a>
            </div>
            @endif
        </div>

        {{-- All Drawer Navigation Links --}}
        <div class="p-3 space-y-1">
            <p class="px-3 pt-2 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">General Menu</p>
            
            <a href="{{ route('news.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.index') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-newspaper text-indigo-500 w-5 text-center text-sm"></i> Latest News
            </a>

            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_central_feed'))
            <a href="{{ route('central-feed.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('central-feed.*') ? 'bg-indigo-600 text-white' : 'text-indigo-700 bg-indigo-50/70 hover:bg-indigo-100' }}">
                <i class="fa-solid fa-bolt text-indigo-500 w-5 text-center text-sm"></i> Live Wire
            </a>
            @endif
            
            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_view_published'))
            <a href="{{ route('news.published') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.published') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-circle-check text-emerald-500 w-5 text-center text-sm"></i> Published News
            </a>
            @endif

            @if(auth()->user()->hasPermission('can_direct_publish'))
            <a href="{{ route('news.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.create') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-pen-to-square text-indigo-500 w-5 text-center text-sm"></i> Create Post
            </a>
            @endif

            @if(auth()->user()->hasPermission('can_ai'))
            <a href="{{ route('news.drafts') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('news.drafts') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-regular fa-file-lines text-amber-500 w-5 text-center text-sm"></i> Drafts & Articles
            </a>
            @endif

            @if(auth()->user()->hasPermission('can_scrape'))
            <a href="{{ route('websites.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('websites.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-globe text-cyan-500 w-5 text-center text-sm"></i> News Sources
            </a>
            @endif

            {{-- YouTube AI Automation --}}
            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_youtube_automate'))
            <a href="{{ route('youtube.channels.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('youtube.*') ? 'bg-red-600 text-white' : 'text-red-700 bg-red-50 hover:bg-red-100' }}">
                <i class="fa-brands fa-youtube text-red-600 w-5 text-center text-sm"></i> YouTube AI Studio
            </a>
            @endif

            {{-- Trending Stories --}}
            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_viral_predictor'))
            <a href="{{ route('trending.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('trending.*') ? 'bg-amber-500 text-white' : 'text-amber-700 bg-amber-50 hover:bg-amber-100' }}">
                <i class="fa-solid fa-fire text-amber-500 w-5 text-center text-sm"></i> Trending Stories
            </a>
            @endif

            {{-- SEO & Website Intelligence --}}
            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_seo_intelligence'))
            @if(Route::has('seo.index'))
            <a href="{{ route('seo.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('seo.*') ? 'bg-indigo-600 text-white' : 'text-indigo-700 bg-indigo-50/70 hover:bg-indigo-100' }}">
                <i class="fa-solid fa-chart-simple text-indigo-500 w-5 text-center text-sm"></i> SEO & Traffic Insights
            </a>
            @endif
            @endif

            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_templates'))
            @if(Route::has('admin.templates.index'))
            <a href="{{ route('admin.templates.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.templates.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-layer-group text-purple-500 w-5 text-center text-sm"></i> Card Templates
            </a>
            @endif
            @endif

            {{-- 🎨 Custom Photo Card Studio & Quick Cards --}}
            @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_custom_photo_card') || auth()->user()->hasPermission('can_studio'))
            <a href="{{ route('custom-photo-card.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('custom-photo-card.*') ? 'bg-indigo-600 text-white' : 'text-violet-700 bg-violet-50 hover:bg-violet-100' }}">
                <i class="fa-solid fa-paintbrush text-violet-500 w-5 text-center text-sm"></i> Photo Card Studio
            </a>

            {{-- ⚡ Quick Card Generator --}}
            <a href="{{ route('free-photocard.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('free-photocard.*') ? 'bg-indigo-600 text-white' : 'text-amber-700 bg-amber-50 hover:bg-amber-100' }}">
                <i class="fa-solid fa-clone text-amber-500 w-5 text-center text-sm"></i> Quick Card Generator
            </a>
            @endif

            {{-- 📊 Analytics & Reports --}}
            @if(Route::has('admin.analytics.index') && (auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_analytics')))
            <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.analytics.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-chart-line text-emerald-500 w-5 text-center text-sm"></i> Analytics & Reports
            </a>
            @endif

            @php
                $userPerms = is_array(auth()->user()->permissions) ? auth()->user()->permissions : (json_decode(auth()->user()->permissions ?? '[]', true) ?? []);
                $canStaff = in_array('can_manage_staff', $userPerms) || auth()->user()->role === 'super_admin' || auth()->user()->role === 'client';
                $canReporters = auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('manage_reporters');
            @endphp

            @if($canStaff || $canReporters)
            <div class="border-t border-slate-100 my-2"></div>
            <p class="px-3 pt-1 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Team Management</p>
            
            @if($canStaff && Route::has('client.staff.index'))
            <a href="{{ route('client.staff.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('client.staff.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-users-gear text-indigo-500 w-5 text-center text-sm"></i> Staff & Roles
            </a>
            @endif

            @if($canReporters && Route::has('manage.reporters.news'))
            <a href="{{ route('manage.reporters.news') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('manage.reporters.news') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-inbox text-rose-500 w-5 text-center text-sm"></i> Reporter Submissions
            </a>
            @endif
            
            @if($canReporters && Route::has('manage.reporters.index'))
            <a href="{{ route('manage.reporters.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('manage.reporters.index') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-users text-blue-500 w-5 text-center text-sm"></i> Reporters
            </a>
            @endif
            @endif

            @if(auth()->user()->role === 'super_admin')
            <div class="border-t border-slate-100 my-2"></div>
            <p class="px-3 pt-1 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Admin Suite</p>
            
            @if(Route::has('admin.dashboard'))
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-chart-pie text-indigo-500 w-5 text-center text-sm"></i> Admin Dashboard
            </a>
            @endif

            @php
                $mPendingOrders = \App\Models\SubscriptionOrder::where('status', 'pending')->count();
            @endphp
            <a href="{{ route('admin.billing.orders') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.billing.orders') ? 'bg-indigo-600 text-white' : 'text-indigo-700 bg-indigo-50/80 hover:bg-indigo-100' }}">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-money-check-dollar text-indigo-600 w-5 text-center text-sm"></i>
                    <span>বিলিং ও পেমেন্ট রিকুয়েস্ট</span>
                </div>
                @if($mPendingOrders > 0)
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-black leading-none text-white bg-rose-600 rounded-full animate-bounce">
                        {{ $mPendingOrders }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.billing.payment-settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.billing.payment-settings') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-wallet text-slate-500 w-5 text-center text-sm"></i> পেমেন্ট গেটওয়ে সেটিংস
            </a>

            <a href="{{ route('admin.pricing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.pricing.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-tags text-indigo-500 w-5 text-center text-sm"></i> Regular Pricing & Coupons
            </a>

            <a href="{{ route('admin.vip-pricing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-black {{ request()->routeIs('admin.vip-pricing.*') ? 'bg-amber-500 text-slate-950' : 'text-amber-700 bg-amber-50/70 hover:bg-amber-100' }}">
                <i class="fa-solid fa-crown text-amber-500 w-5 text-center text-sm"></i> VIP Pricing Manager
            </a>

            @if(Route::has('admin.scraper-monitor'))
            <a href="{{ route('admin.scraper-monitor') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('admin.scraper-monitor') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-heart-pulse text-rose-500 w-5 text-center text-sm"></i> Scraper Monitor
            </a>
            @endif
            @endif

            @php
                $pricingNavConfig = \App\Http\Controllers\PricingController::getPageConfig();
                $isSuperAdmin = auth()->check() && auth()->user()->role === 'super_admin';
            @endphp

            <div class="border-t border-slate-100 my-2"></div>
            <p class="px-3 pt-1 pb-1 text-[10px] font-extrabold text-slate-400 uppercase tracking-wider">Subscriptions & Pricing</p>
            
            @if(auth()->user()->role !== 'reporter')
            <a href="{{ route('billing.my-subscription') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-black {{ request()->routeIs('billing.my-subscription') ? 'bg-indigo-600 text-white' : 'text-indigo-700 bg-indigo-50/60 hover:bg-indigo-100' }}">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-file-invoice-dollar text-indigo-500 w-5 text-center text-sm"></i>
                    <span>আমার সাবস্ক্রিপশন ও বিলিং</span>
                </div>
                <span class="text-[10px] font-black px-2 py-0.5 rounded-md {{ $mIsExp ? 'bg-rose-200 text-rose-900' : 'bg-indigo-200 text-indigo-900' }}">
                    {{ $mDaysRem >= 999 ? 'Active' : ($mIsExp ? 'Expired' : $mDaysRem . 'd') }}
                </span>
            </a>
            @endif

            @if(!$pricingNavConfig['hide_pricing_from_nav'] || $isSuperAdmin)
            <a href="{{ route('pricing.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('pricing.index') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-gem text-purple-500 w-5 text-center text-sm"></i> Special Pricing Plans
            </a>
            @endif

            @if(!$pricingNavConfig['hide_vip_pricing_from_nav'] || $isSuperAdmin)
            <a href="{{ route('pricing.vip') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('pricing.vip') ? 'bg-amber-500 text-slate-950 font-black' : 'text-amber-700 bg-amber-50 hover:bg-amber-100' }}">
                <i class="fa-solid fa-crown text-amber-500 w-5 text-center text-sm"></i> 👑 VIP Enterprise Plans
            </a>
            @endif

            <div class="border-t border-slate-100 my-2"></div>
            @if(Route::has('settings.index') && (auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings')))
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-sliders text-slate-500 w-5 text-center text-sm"></i> Settings & API
            </a>
            @endif
            <a href="{{ route('feedback.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-extrabold {{ request()->routeIs('feedback.*') ? 'bg-emerald-600 text-white' : 'text-slate-700 hover:bg-slate-100' }}">
                <i class="fa-solid fa-lightbulb text-emerald-500 w-5 text-center text-sm"></i> Feedback & Roadmap
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
    function toggleMobileDrawer(e) {
        if (e) e.stopPropagation();
        const modal = document.getElementById('mobileDrawerModal');
        const backdrop = document.getElementById('mobileDrawerBackdrop');
        if (!modal || !backdrop) return;
        
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            backdrop.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.add('hidden');
            backdrop.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }
</script>
@endauth