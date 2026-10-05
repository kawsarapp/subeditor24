@extends('layouts.app')

@section('title', 'Special Pricing Plans - SubEditor24')

@section('content')
<div class="min-h-screen bg-slate-900 text-slate-100 py-12 px-4 sm:px-6 lg:px-8 font-bangla relative overflow-hidden">

    {{-- Background Glow & Gradients --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-b from-indigo-600/20 via-purple-600/10 to-transparent blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto relative z-10">

        {{-- Top Header --}}
        <div class="text-center max-w-3xl mx-auto mb-12">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-bold uppercase tracking-wider mb-4 animate-pulse">
                <i class="fa-solid fa-fire text-amber-400"></i> {{ $config['page_badge'] ?? '৬ মাসের জন্য বিশেষ Discount Offer' }}
            </div>
            <h1 class="text-4xl sm:text-5xl font-black tracking-tight text-white mb-4">
                {{ $config['page_title'] ?? 'Special Pricing Plans' }}
            </h1>
            <p class="text-base sm:text-lg text-slate-400 leading-relaxed">
                {!! nl2br(e($config['page_subtitle'] ?? 'প্রতিটি প্যাকেজে থাকছে ৬ মাসের Special Discount। সাবস্ক্রাইব করলে পাবেন অতিরিক্ত Early Bird Discount এবং লাইফটাইম প্রাইস লক সুবিধা!')) !!}
            </p>
        </div>

        {{-- Pricing Mode Selector Tabs --}}
        <div class="flex justify-center mb-10">
            <div class="bg-slate-800/90 p-1.5 rounded-2xl border border-slate-700/80 shadow-2xl flex flex-wrap justify-center gap-1">
                <button type="button" onclick="setPricingMode('special')" id="tabModeSpecial" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 shadow-lg cursor-pointer">
                    <i class="fa-solid fa-fire text-slate-950"></i>
                    <span>{{ $config['tab_special_title'] ?? '🔥 October Special (সর্বোচ্চ ছাড়)' }}</span>
                </button>
                <button type="button" onclick="setPricingMode('regular')" id="tabModeRegular" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <span>{{ $config['tab_regular_title'] ?? '⏳ Regular 6-Month Discount' }}</span>
                </button>
                <button type="button" onclick="setPricingMode('standard')" id="tabModeStandard" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer">
                    <i class="fa-solid fa-tag"></i>
                    <span>{{ $config['tab_standard_title'] ?? 'Standard Price' }}</span>
                </button>
            </div>
        </div>

        {{-- Dynamic Coupon Applicator Bar --}}
        <div class="max-w-2xl mx-auto mb-12 bg-slate-800/60 border border-slate-700/70 p-4 rounded-2xl backdrop-blur-sm shadow-xl">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="flex items-center gap-2 text-xs font-bold text-slate-300 shrink-0">
                    <i class="fa-solid fa-ticket text-amber-400 text-base"></i>
                    <span>Promo Coupon?</span>
                </div>
                <div class="flex-1 w-full relative">
                    <input type="text" id="couponCodeInput" placeholder="Enter coupon code (e.g. OCTOBER57)" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white uppercase placeholder-slate-500 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                </div>
                <div class="w-full sm:w-auto shrink-0 flex gap-2">
                    <select id="couponPlanSelect" class="bg-slate-900 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-slate-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer">
                        @foreach($plans as $p)
                        <option value="{{ $p->slug }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="applyPromoCoupon()" id="btnApplyCoupon" class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer shrink-0">
                        <i class="fa-solid fa-check"></i> Apply
                    </button>
                </div>
            </div>
            <div id="couponResultAlert" class="hidden mt-3 p-3 rounded-xl text-xs font-semibold"></div>
        </div>

        {{-- Free Trial Welcome Banner --}}
        <div class="max-w-4xl mx-auto mb-10 bg-gradient-to-r from-emerald-950/80 via-slate-800 to-indigo-950/80 border border-emerald-500/40 p-4 sm:p-5 rounded-3xl shadow-2xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3.5 text-center sm:text-left">
                <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-xl shrink-0 shadow-inner">
                    <i class="fa-solid fa-gift"></i>
                </div>
                <div>
                    <h3 class="text-sm sm:text-base font-extrabold text-white flex items-center justify-center sm:justify-start gap-2">
                        <span>{{ $config['free_trial_banner_title'] ?? '🎁 ৭ দিনের নো-রিস্ক ফ্রি ট্রায়াল ও ২০ ফ্রি ক্রেডিট!' }}</span>
                        <span class="text-[10px] bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 px-2 py-0.5 rounded-full uppercase font-black">{{ $config['free_trial_banner_badge'] ?? 'Free Access' }}</span>
                    </h3>
                    <p class="text-xs text-slate-300 mt-0.5">
                        {{ $config['free_trial_banner_desc'] ?? 'কোনো ক্রেডিট কার্ড বা অগ্রিম পেমেন্ট ছাড়াই আজই আপনার নিউজ পোর্টালের জন্য অটোমেশন শুরু করুন।' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('register') }}" class="px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-slate-950 font-black text-xs rounded-xl shadow-lg shadow-emerald-500/20 transition shrink-0 transform hover:-translate-y-0.5 flex items-center gap-1.5">
                <span>{{ $config['free_trial_btn_text'] ?? 'ফ্রি সাইন-আপ করুন' }}</span>
                <i class="fa-solid fa-arrow-right text-[11px]"></i>
            </a>
        </div>

        {{-- Pricing Cards Grid (Regular Subscription Plans) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 xl:gap-5 mb-16">
            @foreach(($regularPlans ?? $plans) as $plan)
            @php
                $isFreeTrial = ($plan->slug === 'free-trial' || $plan->standard_price <= 0);
                $isPopular = $plan->is_popular;
                $isEnterprise = $plan->slug === 'enterprise';
            @endphp
            <div class="rounded-3xl transition-all duration-300 flex flex-col justify-between relative {{ $isFreeTrial ? 'bg-gradient-to-b from-emerald-950/40 via-slate-800 to-slate-900 border-2 border-emerald-500/80 shadow-2xl shadow-emerald-500/10' : ($isPopular ? 'bg-gradient-to-b from-indigo-900/60 via-slate-800 to-slate-900 border-2 border-indigo-500 shadow-2xl shadow-indigo-500/20 ring-4 ring-indigo-500/20 transform xl:-translate-y-2' : ($isEnterprise ? 'bg-gradient-to-b from-amber-950/40 via-slate-800 to-slate-900 border-2 border-amber-500/80 shadow-2xl shadow-amber-500/10' : 'bg-slate-800/80 border border-slate-700/80 hover:border-slate-600')) }} p-5 sm:p-6">

                {{-- Badges --}}
                @if($isFreeTrial)
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-gradient-to-r from-emerald-500 to-teal-600 text-slate-950 text-[10px] font-black tracking-widest uppercase shadow-md flex items-center gap-1">
                    <i class="fa-solid fa-gift"></i> FREE TRIAL
                </div>
                @elseif($isPopular)
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-[10px] font-black tracking-widest uppercase shadow-md flex items-center gap-1">
                    <i class="fa-solid fa-star text-amber-300"></i> POPULAR
                </div>
                @elseif($isEnterprise)
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-600 text-slate-950 text-[10px] font-black tracking-widest uppercase shadow-md flex items-center gap-1">
                    <i class="fa-solid fa-crown text-slate-950"></i> ENTERPRISE
                </div>
                @elseif($plan->badge)
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-slate-700 border border-slate-600 text-slate-300 text-[10px] font-bold tracking-wider uppercase">
                    {{ $plan->badge }}
                </div>
                @endif

                <div>
                    {{-- Plan Header --}}
                    <div class="text-center pb-4 border-b border-slate-700/60 mb-4">
                        <h3 class="text-lg xl:text-xl font-black tracking-wide text-white uppercase mb-1">
                            {{ $plan->name }}
                        </h3>
                        
                        @if($isFreeTrial)
                        <p class="text-[11px] text-emerald-400 font-bold">
                            নো-রিস্ক ট্রায়াল • কোনো কার্ড লাগবে না
                        </p>
                        @else
                        <p class="text-[11px] text-slate-400 font-medium">
                            Standard: <span class="line-through text-slate-500">৳{{ number_format($plan->standard_price) }}</span> / Month
                        </p>
                        @endif

                        {{-- Price Container (Dynamic Switching via JS) --}}
                        <div class="mt-3">
                            @if($isFreeTrial)
                            <div class="price-box-special">
                                <div class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 mb-1">
                                    🎁 ৭ দিনের সম্পূর্ণ ফ্রি
                                </div>
                                <div class="text-3xl font-black text-emerald-400">
                                    ৳০ <span class="text-xs text-slate-400 font-normal">/ ৭ দিন</span>
                                </div>
                            </div>
                            @else
                            {{-- Special Price (Default) --}}
                            <div class="price-box-special" id="priceSpecial-{{ $plan->slug }}">
                                <div class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-500/20 text-amber-400 border border-amber-500/40 mb-1">
                                    🔥 {{ $plan->special_offer_name }}: {{ $plan->special_discount_percentage }}% OFF
                                </div>
                                <div class="text-2xl xl:text-3xl font-black text-amber-400">
                                    ৳{{ number_format($plan->special_price) }} <span class="text-xs text-slate-400 font-normal">/ mo</span>
                                </div>
                            </div>

                            {{-- Regular Price (Hidden by default) --}}
                            <div class="price-box-regular hidden" id="priceRegular-{{ $plan->slug }}">
                                <div class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-500/20 text-indigo-400 border border-indigo-500/40 mb-1">
                                    ⏳ Regular 6-Month: {{ $plan->regular_discount_percentage }}% OFF
                                </div>
                                <div class="text-2xl xl:text-3xl font-black text-indigo-400">
                                    ৳{{ number_format($plan->regular_discount_price) }} <span class="text-xs text-slate-400 font-normal">/ mo</span>
                                </div>
                            </div>

                            {{-- Standard Price (Hidden by default) --}}
                            <div class="price-box-standard hidden" id="priceStandard-{{ $plan->slug }}">
                                <div class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-700 text-slate-300 mb-1">
                                    Regular Standard Plan
                                </div>
                                <div class="text-2xl xl:text-3xl font-black text-white">
                                    ৳{{ number_format($plan->standard_price) }} <span class="text-xs text-slate-400 font-normal">/ mo</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    {{-- Dynamic Features List (From Database) --}}
                    <ul class="space-y-2 text-xs text-slate-300 mb-6">
                        @if(!empty($plan->features) && is_array($plan->features))
                            @foreach($plan->features as $idx => $feature)
                            <li class="flex items-start gap-2 {{ $idx === 0 ? 'font-bold text-white' : 'font-semibold' }}">
                                <i class="fa-solid fa-circle-check {{ $isFreeTrial ? 'text-emerald-400' : ($idx === 0 ? 'text-indigo-400' : 'text-emerald-400') }} mt-0.5 shrink-0 text-[11px]"></i>
                                <span class="leading-tight">{{ $feature }}</span>
                            </li>
                            @endforeach
                        @else
                            <li class="flex items-center gap-2 font-bold text-white">
                                <i class="fa-solid fa-newspaper text-indigo-400 shrink-0"></i>
                                <span>{{ $plan->daily_news_label }}</span>
                            </li>
                            <li class="flex items-center gap-2 font-semibold">
                                <i class="fa-solid fa-image text-purple-400 shrink-0"></i>
                                <span>Unlimited News Photocard</span>
                            </li>
                            <li class="flex items-center gap-2 font-semibold">
                                <i class="fa-solid fa-quote-left text-pink-400 shrink-0"></i>
                                <span>{{ $plan->quotation_cards_label }}</span>
                            </li>
                        @endif
                    </ul>
                </div>

                {{-- Action Button --}}
                <div class="mt-4 pt-4 border-t border-slate-700/60 space-y-2">
                    @if(auth()->check())
                        <a href="{{ route('billing.checkout', ['slug' => $plan->slug, 'mode' => 'special']) }}" class="w-full py-3 px-3.5 rounded-xl text-xs font-black text-center transition block shadow-lg cursor-pointer {{ $isFreeTrial ? 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-slate-950 font-black' : ($isPopular ? 'bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white' : ($isEnterprise ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950' : 'bg-slate-700 hover:bg-indigo-600 text-white')) }}">
                            <i class="fa-solid {{ $isFreeTrial ? 'fa-gift' : 'fa-bolt' }} mr-1"></i> {{ $isFreeTrial ? 'ফ্রি ট্রায়াল চালু করুন' : 'প্যাকেজটি অর্ডার করুন 🚀' }}
                        </a>
                    @else
                        <a href="{{ route('billing.checkout', ['slug' => $plan->slug, 'mode' => 'special']) }}" class="w-full py-3 px-3.5 rounded-xl text-xs font-black text-center transition block shadow-lg cursor-pointer {{ $isFreeTrial ? 'bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-slate-950 font-black' : ($isPopular ? 'bg-gradient-to-r from-indigo-500 via-indigo-600 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white' : ($isEnterprise ? 'bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950' : 'bg-slate-700 hover:bg-indigo-600 text-white')) }}">
                            <i class="fa-solid {{ $isFreeTrial ? 'fa-gift' : 'fa-rocket' }} mr-1"></i> {{ $isFreeTrial ? 'ফ্রি ট্রায়াল শুরু করুন' : 'সাবস্ক্রাইব ও অর্ডার করুন 🚀' }}
                        </a>
                    @endif
                    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $config['whatsapp_number'] ?? '8801975389599') }}?text={{ urlencode('Hello, I want to subscribe to SubEditor24 ' . $plan->name . ' Plan.') }}" onclick="if(window.fbq) fbq('track', 'Contact', { content_name: 'WhatsApp Plan: {{ $plan->name }}' });" target="_blank" class="w-full py-2 px-3 rounded-lg text-[11px] font-bold text-center text-slate-400 hover:text-emerald-400 hover:bg-slate-800/80 transition block cursor-pointer">
                        <i class="fa-brands fa-whatsapp text-emerald-400 mr-1"></i> Talk on WhatsApp
                    </a>
                </div>

            </div>
            @endforeach
        </div>

        {{-- 👑 VIP / Enterprise Promo Banner (Redirects to Dedicated VIP Page) --}}
        <div class="mb-16 relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950/90 to-purple-950/90 border-2 border-amber-500/60 p-6 sm:p-8 shadow-2xl shadow-amber-500/10 backdrop-blur-xl flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="space-y-2 text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gradient-to-r from-amber-500/20 to-orange-500/20 border border-amber-500/40 text-amber-300 text-[11px] font-black uppercase tracking-wider">
                    <i class="fa-solid fa-crown text-amber-400"></i> {{ $config['vip_plan_badge'] ?? '👑 VIP / CUSTOM ENTERPRISE' }}
                </div>
                <h3 class="text-xl sm:text-2xl font-black text-white">
                    {{ $config['vip_plan_title'] ?? 'বড় মিডিয়া হাউজ ও এজেন্সির জন্য বিশেষ VIP সলিউশন' }}
                </h3>
                <p class="text-xs text-slate-300 max-w-2xl font-medium leading-relaxed">
                    ডেডিকেটেড ক্লাউড ক্লাস্টার, কাস্টম এআই ফাইন-টিউনিং, মাল্টি-পোর্টাল কন্ট্রোল এবং আনলিমিটেড রিপোর্টার ও ফটো কার্ড ইঞ্জিনের সম্পূর্ণ VIP প্যাকেজসমূহ দেখতে ভিজিট করুন।
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('pricing.vip') }}" class="px-6 py-3.5 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black text-xs rounded-xl shadow-lg shadow-amber-500/20 transition flex items-center gap-2 transform hover:-translate-y-0.5">
                    <span>এক্সক্লুসিভ VIP প্ল্যানসমূহ দেখুন</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Comparison Matrix Table --}}
        <div class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl mb-16 overflow-hidden">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-table-list"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $config['table_heading'] ?? '🔥 OFFER SUMMARY & LIMITS COMPARISON' }}</h2>
                    <p class="text-xs text-slate-400">{{ $config['table_subheading'] ?? 'এক নজরে সকল প্যাকেজের অফার মূল্য ও লিমিট তুলনা' }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-700 text-slate-400 font-extrabold uppercase tracking-wider">
                            <th class="py-3 px-4">Plan</th>
                            <th class="py-3 px-4">Standard Price</th>
                            <th class="py-3 px-4">Regular 6-Month</th>
                            <th class="py-3 px-4 text-amber-400">{{ $config['tab_special_title'] ?? '🔥 Special Price' }}</th>
                            <th class="py-3 px-4">Daily News</th>
                            <th class="py-3 px-4">Reporters</th>
                            <th class="py-3 px-4">Websites</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($plans as $p)
                        @php $isTrial = ($p->slug === 'free-trial' || $p->standard_price <= 0); @endphp
                        <tr class="hover:bg-slate-700/30 transition font-medium {{ $isTrial ? 'bg-emerald-950/20' : '' }}">
                            <td class="py-3.5 px-4 font-bold text-white flex items-center gap-1.5">
                                @if($isTrial)
                                    <i class="fa-solid fa-gift text-emerald-400 text-[11px]"></i>
                                @elseif($p->is_popular) 
                                    <i class="fa-solid fa-star text-amber-400 text-[11px]"></i> 
                                @endif
                                <span>{{ $p->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 {{ $isTrial ? '' : 'line-through' }}">{{ $isTrial ? '৳০' : '৳' . number_format($p->standard_price) }}</td>
                            <td class="py-3.5 px-4 {{ $isTrial ? 'text-emerald-400 font-bold' : 'text-indigo-400 font-bold' }}">{{ $isTrial ? '৳০' : '৳' . number_format($p->regular_discount_price) }}</td>
                            <td class="py-3.5 px-4 {{ $isTrial ? 'text-emerald-400' : 'text-amber-400' }} font-black text-sm">{{ $isTrial ? '৳০ (Free)' : '৳' . number_format($p->special_price) }}</td>
                            <td class="py-3.5 px-4">{{ $p->daily_news_label }}</td>
                            <td class="py-3.5 px-4">{{ $p->reporters_label }}</td>
                            <td class="py-3.5 px-4">{{ $p->websites_label }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Guarantee & Lifetime Price Lock Box --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <div class="bg-gradient-to-br from-indigo-950/40 to-slate-900 border border-indigo-500/30 p-6 rounded-3xl flex items-start gap-4 shadow-xl">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white mb-1">{{ $config['policy_card_1_title'] ?? '⏳ Regular Discount Policy' }}</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ $config['policy_card_1_desc'] ?? 'প্রথম ৬ মাস পর্যন্ত প্রতিটি প্ল্যানে থাকছে সর্বোচ্চ ৫০% থেকে ৬৫% পর্যন্ত নিয়মিত ছাড়ের সুবিধা।' }}
                    </p>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-950/40 to-slate-900 border border-amber-500/30 p-6 rounded-3xl flex items-start gap-4 shadow-xl">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-amber-400 mb-1">{{ $config['policy_card_2_title'] ?? '🔥 Early Bird (Lifetime Lock)' }}</h3>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        {{ $config['policy_card_2_desc'] ?? 'অফারের মধ্যে সাবস্ক্রাইব করলে Special Price-এর ছাড় পাবেন এবং সেই নির্ধারিত মূল্য আপনার জন্য সবসময়ের জন্য (Lifetime) Lock থাকবে!' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Contact Support Footer & Legal Links --}}
        <div class="text-center py-8 border-t border-slate-800 text-xs text-slate-500 space-y-3">
            <p>
                কাস্টম এন্টারপ্রাইজ বা বিশেষ সহায়তার জন্য যোগাযোগ করুন: 
                <a href="mailto:{{ $config['contact_email'] ?? 'support@newsmanage24.com' }}" class="text-indigo-400 hover:underline">{{ $config['contact_email'] ?? 'support@newsmanage24.com' }}</a> 
                অথবা কল করুন <strong class="text-slate-300">{{ $config['contact_phone'] ?? '+880 1975-389599' }}</strong> 
                অথবা <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $config['whatsapp_number'] ?? '8801975389599') }}" target="_blank" onclick="if(window.fbq) fbq('track', 'Contact', { content_name: 'WhatsApp Pricing Footer Support' });" class="text-emerald-400 font-bold hover:underline inline-flex items-center gap-1"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
            </p>
            <div class="flex items-center justify-center gap-4 flex-wrap text-slate-400 text-[11px] pt-1">
                <a href="{{ route('legal.privacy') }}" class="hover:text-indigo-400 transition">গোপনীয়তা নীতি</a>
                <span>•</span>
                <a href="{{ route('legal.terms') }}" class="hover:text-indigo-400 transition">ব্যবহারের শর্তাবলী</a>
                <span>•</span>
                <a href="{{ route('legal.refund') }}" class="hover:text-indigo-400 transition">রিফান্ড নীতি</a>
                <span>•</span>
                <a href="{{ route('legal.about') }}" class="hover:text-indigo-400 transition">আমাদের সম্পর্কে</a>
                <span>•</span>
                <a href="{{ route('legal.contact') }}" class="hover:text-indigo-400 transition">যোগাযোগ</a>
            </div>
            <p class="text-[11px] text-slate-600">
                &copy; {{ date('Y') }} Subeditor24. All rights reserved.
            </p>
        </div>

    </div>
</div>

<script>
    let currentPricingMode = 'special';

    function setPricingMode(mode) {
        currentPricingMode = mode;
        const btnSpecial = document.getElementById('tabModeSpecial');
        const btnRegular = document.getElementById('tabModeRegular');
        const btnStandard = document.getElementById('tabModeStandard');

        // Reset button styles
        [btnSpecial, btnRegular, btnStandard].forEach(btn => {
            btn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer';
        });

        if (mode === 'special') {
            btnSpecial.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 shadow-lg cursor-pointer';
            document.querySelectorAll('.price-box-special').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.price-box-regular').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.price-box-standard').forEach(el => el.classList.add('hidden'));
        } else if (mode === 'regular') {
            btnRegular.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg cursor-pointer';
            document.querySelectorAll('.price-box-special').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.price-box-regular').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.price-box-standard').forEach(el => el.classList.add('hidden'));
        } else {
            btnStandard.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-slate-700 text-white shadow-lg cursor-pointer';
            document.querySelectorAll('.price-box-special').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.price-box-regular').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.price-box-standard').forEach(el => el.classList.remove('hidden'));
        }
    }

    function applyPromoCoupon() {
        const input = document.getElementById('couponCodeInput');
        const planSelect = document.getElementById('couponPlanSelect');
        const alertBox = document.getElementById('couponResultAlert');
        const code = input ? input.value.trim() : '';
        const planSlug = planSelect ? planSelect.value : '';

        if (!code) {
            alert('অনুগ্রহ করে কুপন কোড লিখুন।');
            return;
        }

        const btn = document.getElementById('btnApplyCoupon');
        if (btn) btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking...';

        fetch('{{ route("pricing.apply-coupon") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                code: code,
                plan_slug: planSlug,
                mode: currentPricingMode
            })
        })
        .then(res => res.json())
        .then(data => {
            if (alertBox) alertBox.classList.remove('hidden', 'bg-emerald-950/80', 'text-emerald-300', 'border-emerald-500/50', 'bg-rose-950/80', 'text-rose-300', 'border-rose-500/50');
            
            if (data.success) {
                const d = data.data;
                alertBox.className = 'mt-3 p-3.5 rounded-xl text-xs font-semibold bg-emerald-950/80 text-emerald-300 border border-emerald-500/50 block';
                alertBox.innerHTML = `
                    <div class="flex items-center justify-between">
                        <span>${data.message} <strong>[${d.code}]</strong></span>
                        <span class="text-amber-400 font-bold">ছাড়: ৳${d.discount_amount}</span>
                    </div>
                    <div class="mt-1 text-[11px] text-emerald-400/90">
                        ${data.plan_name} প্ল্যানের জন্য মূল মূল্য: <span class="line-through">৳${d.original_price}</span> ➔ কুপন ডিসকাউন্ট মূল্য: <strong>৳${d.final_price} / Month</strong>
                    </div>
                `;
            } else {
                alertBox.className = 'mt-3 p-3 rounded-xl text-xs font-semibold bg-rose-950/80 text-rose-300 border border-rose-500/50 block';
                alertBox.innerText = data.message || 'কুপনটি সঠিক নয়।';
            }
        })
        .catch(err => {
            if (alertBox) {
                alertBox.className = 'mt-3 p-3 rounded-xl text-xs font-semibold bg-rose-950/80 text-rose-300 border border-rose-500/50 block';
                alertBox.innerText = '❌ নেটওয়ার্ক ত্রুটি: ' + err.message;
            }
        })
        .finally(() => {
            if (btn) btn.innerHTML = '<i class="fa-solid fa-check"></i> Apply';
        });
    }
</script>
@endsection
