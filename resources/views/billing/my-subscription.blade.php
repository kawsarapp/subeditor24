@extends('layouts.app')

@section('title', 'My Subscription & Billing - Subeditor24')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-8 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-6xl mx-auto space-y-8">

        {{-- TOP HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full {{ $adminUser->isSubscriptionActive() ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">সাবস্ক্রিপশন ও প্ল্যান স্ট্যাটাস</h1>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    আপনার বর্তমান প্যাকেজের মেয়াদ, ব্যবহারিক পরিসংখ্যান এবং বিলিং হিস্ট্রি
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black text-xs px-5 py-2.5 rounded-xl shadow-lg shadow-amber-500/20 transition transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-crown"></i>
                    <span>প্ল্যান আপগ্রেড / রিনিউ করুন</span>
                </a>
            </div>
        </div>

        {{-- FLASH MESSAGES & EXPIRY WARNINGS --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-800/80 text-emerald-300 text-xs sm:text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('subscription_warning') || $adminUser->isExpired())
            <div class="p-5 rounded-2xl bg-rose-950/80 border-2 border-rose-600 text-rose-200 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xl animate-pulse">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center text-xl shrink-0">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="font-black text-sm text-white">আপনার সাবস্ক্রিপশনের মেয়াদ শেষ হয়ে গেছে!</h4>
                        <p class="text-rose-300 text-xs mt-0.5">নিউজ স্ক্র্যাপিং, এআই রিরাইট এবং অটো-পোস্টিং সেবা চালু রাখতে অনুগ্রহ করে আপনার প্ল্যানটি রিনিউ করুন।</p>
                    </div>
                </div>
                <a href="{{ route('pricing.index') }}" class="bg-white text-rose-950 font-black text-xs px-4 py-2.5 rounded-xl hover:bg-rose-100 transition shrink-0 text-center shadow-lg">
                    রিনিউ করুন 🚀
                </a>
            </div>
        @elseif($adminUser->isExpiringSoon())
            <div class="p-4 rounded-2xl bg-amber-950/60 border border-amber-800 text-amber-200 text-xs sm:text-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-lg">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-clock text-amber-400 text-lg shrink-0"></i>
                    <span>⚠️ <strong>সাবধানতা:</strong> আপনার সাবস্ক্রিপশনের মেয়াদ আর মাত্র <strong>{{ $adminUser->days_remaining }} দিন</strong> বাকি আছে!</span>
                </div>
                <a href="{{ route('pricing.index') }}" class="bg-amber-500 text-slate-950 font-extrabold text-xs px-4 py-1.5 rounded-xl hover:bg-amber-400 transition shrink-0 text-center">
                    নবায়ন করুন
                </a>
            </div>
        @endif

        {{-- 1. MAIN PLAN VALIDITY CARD & COUNTDOWN PROGRESS --}}
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="absolute -right-20 -top-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                {{-- Left: Current Plan Title & Details --}}
                <div class="lg:col-span-6 space-y-4">
                    <div class="flex items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider {{ $adminUser->isSubscriptionActive() ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30' }}">
                            <span class="w-2 h-2 rounded-full {{ $adminUser->isSubscriptionActive() ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                            {{ $adminUser->subscription_status === 'lifetime' ? '👑 Lifetime Plan' : ($adminUser->isSubscriptionActive() ? 'Active Subscription' : 'Expired Plan') }}
                        </span>
                        <span class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/30 px-2.5 py-0.5 rounded-lg">
                            {{ $adminUser->subscription_cycle_label }}
                        </span>
                    </div>

                    <h2 class="text-3xl font-black text-white tracking-tight flex items-center gap-3">
                        <span>{{ $currentPlan?->name ?? 'STARTER' }}</span>
                        @if($currentPlan?->badge)
                            <span class="text-[10px] px-2.5 py-0.5 rounded-md bg-amber-400 text-slate-950 font-bold uppercase">{{ $currentPlan->badge }}</span>
                        @endif
                    </h2>

                    <div class="grid grid-cols-2 gap-3 text-xs pt-2">
                        <div class="bg-slate-950/60 p-3 rounded-2xl border border-slate-800">
                            <span class="text-slate-400 block text-[11px]">শুরুর তারিখ:</span>
                            <strong class="text-slate-200 text-sm font-mono mt-0.5 block">
                                {{ $adminUser->joining_date ? \Carbon\Carbon::parse($adminUser->joining_date)->format('d M, Y') : ($adminUser->created_at ? $adminUser->created_at->format('d M, Y') : 'N/A') }}
                            </strong>
                        </div>
                        <div class="bg-slate-950/60 p-3 rounded-2xl border border-slate-800">
                            <span class="text-slate-400 block text-[11px]">মেয়াদ শেষ:</span>
                            <strong class="text-slate-200 text-sm font-mono mt-0.5 block {{ $adminUser->isExpired() ? 'text-rose-400' : 'text-amber-400' }}">
                                {{ $adminUser->expire_date ? \Carbon\Carbon::parse($adminUser->expire_date)->format('d M, Y') : ($adminUser->created_at ? $adminUser->created_at->addDays(7)->format('d M, Y') : 'N/A') }}
                            </strong>
                        </div>
                    </div>
                </div>

                {{-- Right: Live Days Counter & Progress Bar --}}
                <div class="lg:col-span-6 bg-slate-950/70 border border-slate-800/80 rounded-2xl p-5 sm:p-6 flex flex-col justify-center space-y-4">
                    
                    {{-- Counters Row --}}
                    <div class="flex items-center justify-between text-center divide-x divide-slate-800">
                        <div class="flex-1 px-2">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">অতিবাহিত হয়েছে</span>
                            <span class="text-2xl sm:text-3xl font-black text-white font-mono mt-1 block">
                                {{ $adminUser->days_used }} <span class="text-xs text-slate-400 font-normal">দিন</span>
                            </span>
                        </div>
                        <div class="flex-1 px-2">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">মেয়াদ বাকি আছে</span>
                            <span class="text-2xl sm:text-3xl font-black {{ $adminUser->isExpired() ? 'text-rose-400' : ($adminUser->isExpiringSoon() ? 'text-amber-400' : 'text-emerald-400') }} font-mono mt-1 block">
                                {{ $adminUser->days_remaining }} <span class="text-xs text-slate-400 font-normal">দিন</span>
                            </span>
                        </div>
                        <div class="flex-1 px-2">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">মোট মেয়াদ</span>
                            <span class="text-2xl sm:text-3xl font-black text-slate-300 font-mono mt-1 block">
                                {{ $adminUser->total_plan_days }} <span class="text-xs text-slate-400 font-normal">দিন</span>
                            </span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="space-y-1.5 pt-2">
                        <div class="flex justify-between text-[11px] font-bold text-slate-400">
                            <span>প্ল্যান ব্যবহারের অগ্রগতি</span>
                            <span class="font-mono text-white">{{ $adminUser->subscription_progress_percent }}%</span>
                        </div>
                        <div class="w-full h-3.5 bg-slate-900 rounded-full overflow-hidden p-0.5 border border-slate-800">
                            <div class="h-full rounded-full transition-all duration-500 {{ $adminUser->isExpired() ? 'bg-rose-500' : ($adminUser->isExpiringSoon() ? 'bg-gradient-to-r from-amber-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-400') }}" style="width: {{ $adminUser->subscription_progress_percent }}%"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- 2. QUOTAS & LIMITS METERS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- AI Credits --}}
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-2">
                <div class="flex items-center justify-between text-slate-400 text-xs font-bold">
                    <span>AI রিরাইট ক্রেডিট</span>
                    <i class="fa-solid fa-bolt text-indigo-400"></i>
                </div>
                <div class="text-2xl font-black text-white font-mono">
                    {{ $adminUser->credits }} <span class="text-xs font-normal text-slate-400">ব্যালেন্স</span>
                </div>
                <div class="text-[11px] text-slate-400">সর্বমোট ক্রেডিট ক্যাপাসিটি: {{ $adminUser->total_credits_limit ?: 500 }}</div>
            </div>

            {{-- Daily Post Quota --}}
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-2">
                <div class="flex items-center justify-between text-slate-400 text-xs font-bold">
                    <span>দৈনিক পোস্ট লিমিট</span>
                    <i class="fa-solid fa-newspaper text-emerald-400"></i>
                </div>
                <div class="text-2xl font-black text-white font-mono">
                    {{ $adminUser->todays_post_count }} <span class="text-xs font-normal text-slate-400">/ {{ $adminUser->daily_post_limit ?? 50 }} আজ পোস্ট</span>
                </div>
                <div class="text-[11px] text-slate-400">প্রতিদিন মধ্যরাতে অটোমেটিক রিসেট হয়</div>
            </div>

            {{-- Staff / Reporters --}}
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-2">
                <div class="flex items-center justify-between text-slate-400 text-xs font-bold">
                    <span>রিপোর্টার / স্টাফ স্লট</span>
                    <i class="fa-solid fa-users text-purple-400"></i>
                </div>
                <div class="text-2xl font-black text-white font-mono">
                    {{ $adminUser->reporters()->count() }} <span class="text-xs font-normal text-slate-400">/ {{ $adminUser->staff_limit ?? 10 }} যুক্ত</span>
                </div>
                <div class="text-[11px] text-slate-400">সাব-এডিটর ও ফিল্ড রিপোর্টার ম্যানেজমেন্ট</div>
            </div>

            {{-- Scrape Sources --}}
            <div class="bg-slate-900/70 border border-slate-800 rounded-2xl p-5 space-y-2">
                <div class="flex items-center justify-between text-slate-400 text-xs font-bold">
                    <span>স্ক্র্যাপ ওয়েবসাইট সোর্স</span>
                    <i class="fa-solid fa-globe text-cyan-400"></i>
                </div>
                <div class="text-2xl font-black text-white font-mono">
                    {{ $adminUser->websites()->count() }} <span class="text-xs font-normal text-slate-400">সক্রিয় সোর্স</span>
                </div>
                <div class="text-[11px] text-slate-400">সর্বোচ্চ {{ $currentPlan?->bangla_websites_limit == -1 ? 'আনলিমিটেড' : ($currentPlan?->bangla_websites_limit ?? 'সকল') }} সাইট সাপোর্ট</div>
            </div>
        </div>

        {{-- 3. SUBSCRIPTION ORDERS & BILLING HISTORY --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-black text-white tracking-tight">অর্ডার ও পেমেন্ট হিস্ট্রি</h3>
                    <p class="text-xs text-slate-400 mt-0.5">আপনার সকল সাবস্ক্রিপশন ও ম্যানুয়াল পেমেন্ট অর্ডারের তালিকা</p>
                </div>
                <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">
                    <span>নতুন প্যাকেজ অর্ডার করুন</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>

            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/80 text-slate-400 uppercase font-black tracking-wider text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">অর্ডার নং</th>
                                <th class="py-3 px-4">প্যাকেজ</th>
                                <th class="py-3 px-4">পরিমাণ</th>
                                <th class="py-3 px-4">পেমেন্ট মেথড</th>
                                <th class="py-3 px-4">TrxID / প্রেরক</th>
                                <th class="py-3 px-4">স্ট্যাটাস</th>
                                <th class="py-3 px-4">তারিখ</th>
                                <th class="py-3 px-4 text-right">ইনভয়েস</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @foreach($orders as $order)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-3.5 px-4 font-mono font-bold text-white">{{ $order->order_number }}</td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-bold text-slate-200">{{ $order->plan_name }}</span>
                                        <span class="text-[10px] text-slate-400 block">({{ $order->billing_cycle_label }})</span>
                                    </td>
                                    <td class="py-3.5 px-4 font-mono font-black text-amber-400">৳{{ number_format($order->final_amount, 2) }}</td>
                                    <td class="py-3.5 px-4">{!! $order->payment_method_badge !!}</td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-mono font-bold text-white">{{ $order->transaction_id ?: 'N/A' }}</div>
                                        <div class="text-[10px] text-slate-400 font-mono">{{ $order->sender_number }}</div>
                                    </td>
                                    <td class="py-3.5 px-4">{!! $order->status_badge !!}</td>
                                    <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px]">{{ $order->created_at->format('d M, Y') }}</td>
                                    <td class="py-3.5 px-4 text-right">
                                        <a href="{{ route('billing.invoice', $order->order_number) }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 font-bold bg-slate-800/60 hover:bg-slate-800 px-2.5 py-1 rounded-lg transition border border-slate-700/50">
                                            <i class="fa-solid fa-file-invoice text-xs"></i>
                                            <span>Invoice</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pt-2">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-12 border border-dashed border-slate-800 rounded-2xl p-8 space-y-3">
                    <div class="w-14 h-14 rounded-full bg-slate-900 flex items-center justify-center text-slate-500 text-2xl mx-auto">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                    <h4 class="text-sm font-bold text-white">কোনো পূর্ববর্তী অর্ডার পাওয়া যায়নি</h4>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">আপনি এখনও কোনো ম্যানুয়াল বিলিং অর্ডার তৈরি করেননি। স্পেশাল অফারে নতুন প্ল্যান নিতে নিচের বাটনে ক্লিক করুন।</p>
                    <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs px-4 py-2 rounded-xl transition mt-2 cursor-pointer">
                        <i class="fa-solid fa-tags"></i>
                        <span>প্রাইসিং প্ল্যানসমূহ দেখুন</span>
                    </a>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
