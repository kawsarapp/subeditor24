@extends('layouts.app')

@section('title', '👑 VIP & Custom Enterprise Solutions - Subeditor24')

@section('content')
<div class="min-h-screen bg-gradient-to-b from-slate-950 via-slate-900 to-indigo-950 text-slate-100 font-bangla py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        
        {{-- Navigation Breadcrumb & Back Link --}}
        <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-800">
            <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-amber-400 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>সকল স্ট্যান্ডার্ড সাবস্ক্রিপশন প্ল্যান দেখুন</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-black uppercase">
                    <i class="fa-solid fa-crown text-[10px]"></i> VIP Solutions
                </span>
            </div>
        </div>

        {{-- Hero Header --}}
        <div class="text-center space-y-4 mb-12 relative">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-amber-500/20 via-purple-500/20 to-orange-500/20 border border-amber-500/50 text-amber-300 text-xs font-black uppercase tracking-wider shadow-lg shadow-amber-500/10 relative z-10">
                <i class="fa-solid fa-crown text-amber-400"></i>
                <span>{{ $config['vip_plan_badge'] ?? '👑 VIP / CUSTOM ENTERPRISE' }}</span>
            </div>

            <h1 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight relative z-10">
                {{ $config['vip_plan_title'] ?? 'কাস্টম এন্টারপ্রাইজ ও মিডিয়া এজেন্সি স্যুট' }}
            </h1>

            <p class="max-w-3xl mx-auto text-sm sm:text-base text-slate-300 font-medium leading-relaxed relative z-10">
                {{ $config['vip_plan_subtitle'] ?? 'বড় মিডিয়া হাউজ, টিভি চ্যানেল, মাল্টি-পোর্টাল ও জাতীয় দৈনিকের জন্য আনলিমিটেড পাওয়ার, ডেডিকেটেড সার্ভার ও কাস্টম এআই ইন্টিগ্রেশন।' }}
            </p>

            {{-- 3-Mode Pricing Switcher Tabs (If VIP plans exist) --}}
            @if($vipPlans->count() > 0)
            <div class="flex justify-center pt-4 relative z-10">
                <div class="inline-flex p-1.5 rounded-2xl bg-slate-900/90 border border-slate-700/80 shadow-2xl backdrop-blur-xl">
                    <button type="button" onclick="setVipPricingMode('special')" id="vipTabModeSpecial" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 shadow-lg cursor-pointer">
                        <span>{{ $config['tab_special_title'] ?? '🔥 Special Price' }}</span>
                    </button>
                    <button type="button" onclick="setVipPricingMode('regular')" id="vipTabModeRegular" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer">
                        <span>{{ $config['tab_regular_title'] ?? '⏳ Regular 6-Month' }}</span>
                    </button>
                    <button type="button" onclick="setVipPricingMode('standard')" id="vipTabModeStandard" class="px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer">
                        <span>{{ $config['tab_standard_title'] ?? 'Standard Price' }}</span>
                    </button>
                </div>
            </div>
            @endif
        </div>

        {{-- 👑 DYNAMIC VIP PLAN CARDS GRID (If Admin created VIP packages) --}}
        @if($vipPlans->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-16">
            @foreach($vipPlans as $plan)
            <div class="rounded-3xl bg-gradient-to-b from-slate-900 via-purple-950/40 to-slate-900 border-2 border-amber-500/70 p-6 sm:p-7 shadow-2xl shadow-amber-500/10 flex flex-col justify-between relative transform hover:-translate-y-1 transition duration-300">
                
                {{-- Badge --}}
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-[10px] font-black tracking-widest uppercase shadow-md flex items-center gap-1">
                    <i class="fa-solid fa-crown text-[9px]"></i> {{ $plan->badge ?: '👑 VIP SPECIAL' }}
                </div>

                <div>
                    {{-- Header & Price --}}
                    <div class="text-center pb-4 border-b border-amber-500/20 mb-4">
                        <h3 class="text-xl font-black text-white uppercase tracking-wide mb-1">
                            {{ $plan->name }}
                        </h3>
                        <p class="text-[11px] text-amber-300/80 font-medium">
                            Standard: <span class="line-through text-slate-500">৳{{ number_format($plan->standard_price) }}</span> / Month
                        </p>

                        <div class="mt-3">
                            {{-- Special Price (Default) --}}
                            <div class="vip-price-box-special" id="vipPriceSpecial-{{ $plan->slug }}">
                                <div class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-amber-500/20 text-amber-400 border border-amber-500/40 mb-1">
                                    🔥 {{ $plan->special_offer_name }}: {{ $plan->special_discount_percentage }}% OFF
                                </div>
                                <div class="text-3xl font-black text-amber-400">
                                    ৳{{ number_format($plan->special_price) }} <span class="text-xs text-slate-400 font-normal">/ mo</span>
                                </div>
                            </div>

                            {{-- Regular Price (Hidden by default) --}}
                            <div class="vip-price-box-regular hidden" id="vipPriceRegular-{{ $plan->slug }}">
                                <div class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-indigo-500/20 text-indigo-400 border border-indigo-500/40 mb-1">
                                    ⏳ Regular 6-Month: {{ $plan->regular_discount_percentage }}% OFF
                                </div>
                                <div class="text-3xl font-black text-indigo-400">
                                    ৳{{ number_format($plan->regular_discount_price) }} <span class="text-xs text-slate-400 font-normal">/ mo</span>
                                </div>
                            </div>

                            {{-- Standard Price (Hidden by default) --}}
                            <div class="vip-price-box-standard hidden" id="vipPriceStandard-{{ $plan->slug }}">
                                <div class="inline-block px-2.5 py-0.5 rounded-md text-[10px] font-extrabold bg-slate-700 text-slate-300 mb-1">
                                    Standard Price
                                </div>
                                <div class="text-3xl font-black text-white">
                                    ৳{{ number_format($plan->standard_price) }} <span class="text-xs text-slate-400 font-normal">/ mo</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Limits Summary --}}
                    @if(!empty($plan->custom_limits) && is_array($plan->custom_limits))
                    <div class="grid grid-cols-2 gap-2 mb-4 p-3 rounded-2xl bg-slate-950/60 border border-amber-500/20 text-[11px]">
                        @foreach($plan->custom_limits as $k => $v)
                        <div class="flex flex-col">
                            <span class="text-slate-400 text-[10px]">{{ $k }}</span>
                            <span class="font-bold text-amber-400">{{ (string)$v === '-1' ? 'Unlimited' : $v }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    {{-- Features Checklist --}}
                    <ul class="space-y-2 text-xs text-slate-200 mb-6">
                        @if(!empty($plan->features) && is_array($plan->features))
                            @foreach($plan->features as $idx => $vf)
                            <li class="flex items-start gap-2 font-semibold">
                                <i class="fa-solid fa-check-double text-amber-400 mt-0.5 shrink-0 text-[11px]"></i>
                                <span class="leading-tight">{{ $vf }}</span>
                            </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                {{-- Action Buttons --}}
                <div class="mt-4 pt-4 border-t border-amber-500/20 space-y-2">
                    <a href="{{ route('register', ['plan' => $plan->slug]) }}" class="w-full py-3.5 px-4 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black text-xs rounded-xl shadow-lg shadow-amber-500/20 transition block text-center transform hover:-translate-y-0.5 cursor-pointer">
                        <i class="fa-solid fa-crown mr-1"></i> ভিআইপি প্যাকেজ নিন 🚀
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $config['whatsapp_number'] ?? '8801771545972') }}?text={{ urlencode('Hello, I am interested in the Subeditor24 VIP Plan: ' . $plan->name) }}" target="_blank" class="w-full py-2 px-3 rounded-lg text-xs font-bold text-center text-slate-300 hover:text-emerald-400 hover:bg-slate-800/80 transition block cursor-pointer">
                        <i class="fa-brands fa-whatsapp text-emerald-400 mr-1"></i> Executive WhatsApp Chat
                    </a>
                </div>

            </div>
            @endforeach
        </div>
        @else
        {{-- Custom Tailored Banner if no standalone VIP cards --}}
        <div class="mb-16 relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950/80 to-purple-950/90 border-2 border-amber-500/60 p-6 sm:p-10 shadow-2xl shadow-amber-500/10 backdrop-blur-xl">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                <div class="lg:col-span-8 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-gradient-to-r from-amber-500/20 to-orange-500/20 border border-amber-500/40 text-amber-300 text-xs font-black uppercase tracking-wider">
                        <i class="fa-solid fa-crown text-amber-400"></i> {{ $config['vip_plan_badge'] ?? '👑 VIP / CUSTOM ENTERPRISE' }}
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-black text-white">
                        {{ $config['vip_plan_title'] ?? 'কাস্টম এন্টারপ্রাইজ ও মিডিয়া এজেন্সি স্যুট' }}
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed font-medium">
                        {{ $config['vip_plan_subtitle'] ?? 'বড় মিডিয়া হাউজ, টিভি চ্যানেল, মাল্টি-পোর্টাল ও জাতীয় দৈনিকের জন্য আনলিমিটেড পাওয়ার, ডেডিকেটেড সার্ভার ও কাস্টম এআই ইন্টিগ্রেশন।' }}
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
                        @foreach(($config['vip_plan_features'] ?? []) as $vf)
                        <div class="flex items-start gap-2 text-xs font-semibold text-slate-200">
                            <i class="fa-solid fa-check-double text-amber-400 mt-0.5 shrink-0 text-[11px]"></i>
                            <span class="leading-tight">{{ $vf }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="lg:col-span-4 bg-slate-900/90 border border-amber-500/30 p-6 rounded-2xl text-center space-y-5 shadow-xl">
                    <div>
                        <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block">Custom Tailored Plan</span>
                        <div class="text-2xl font-black text-amber-400 mt-1">
                            {{ $config['vip_plan_price_label'] ?? 'Custom Tailored Pricing' }}
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">
                            {{ $config['vip_plan_price_sub'] ?? 'আপনার পোর্টাল ও ট্রাফিক সাইজ অনুযায়ী বিশেষ মূল্য নির্ধারণ' }}
                        </p>
                    </div>
                    @php
                        $vipWhatsApp = preg_replace('/[^\d]/', '', $config['whatsapp_number'] ?? '8801771545972');
                        $vipCustomUrl = !empty($config['vip_plan_btn_url']) ? $config['vip_plan_btn_url'] : "https://wa.me/{$vipWhatsApp}?text=" . urlencode('Hello, I am interested in Subeditor24 VIP Custom Enterprise.');
                    @endphp
                    <div class="space-y-2.5">
                        <a href="{{ $vipCustomUrl }}" target="_blank" class="w-full py-3.5 px-4 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-xs rounded-xl shadow-lg shadow-amber-500/20 transition block cursor-pointer">
                            {{ $config['vip_plan_btn_text'] ?? 'ভিআইপি কনসালটেশন ও ডেমো বুক করুন 🚀' }}
                        </a>
                        <a href="https://wa.me/{{ $vipWhatsApp }}" target="_blank" class="w-full py-2 px-3 rounded-lg text-xs font-bold text-slate-300 hover:text-emerald-400 transition block">
                            <i class="fa-brands fa-whatsapp text-emerald-400 mr-1"></i> Executive WhatsApp Chat
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- 🌟 7 ENTERPRISE PILLARS FOR MEDIA HOUSES --}}
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl p-6 sm:p-10 mb-16 shadow-2xl">
            <div class="text-center max-w-2xl mx-auto mb-10">
                <span class="text-xs font-black uppercase tracking-wider text-amber-400">Enterprise Infrastructure</span>
                <h2 class="text-2xl sm:text-3xl font-black text-white mt-1">কেন শীর্ষ মিডিয়া হাউজ আমাদের ভিআইপি সমাধান বেছে নেয়?</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-slate-950/60 border border-slate-800 p-5 rounded-2xl space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <h3 class="font-bold text-sm text-white">ডেডিকেটেড ক্লাউড ক্লাস্টার</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">ব্রেকিং নিউজের লাখ লাখ ভিজিটরেও কোনো ট্রাফিক ল্যাগ ছাড়া সর্বোচ্চ গতির নিশ্চয়তা।</p>
                </div>

                <div class="bg-slate-950/60 border border-slate-800 p-5 rounded-2xl space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-brain"></i>
                    </div>
                    <h3 class="font-bold text-sm text-white">কাস্টম এআই ফাইন-টিউনিং</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">আপনার নিউজরুমের সম্পাদকীয় লেখার স্টাইল অনুযায়ী বিশেষ এআই মডেল ফাইন-টিউনিং।</p>
                </div>

                <div class="bg-slate-950/60 border border-slate-800 p-5 rounded-2xl space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-network-wired"></i>
                    </div>
                    <h3 class="font-bold text-sm text-white">মাল্টি-পোর্টাল সেন্ট্রাল হাব</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">একাধিক নিউজ পোর্টাল, সাব-ডোমেইন ও আঞ্চলিক সংস্করণ এক ড্যাশবোর্ড থেকে অটোমেশন।</p>
                </div>

                <div class="bg-slate-950/60 border border-slate-800 p-5 rounded-2xl space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-users-viewfinder"></i>
                    </div>
                    <h3 class="font-bold text-sm text-white">আনলিমিটেড রিপোর্টার ও স্টাফ</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">সারাদেশের জেলা ও উপজেলা প্রতিনিধিদের জন্য কাস্টম ডেস্ক ও সেন্ট্রাল ইনবক্স সুবিধা।</p>
                </div>

                <div class="bg-slate-950/60 border border-slate-800 p-5 rounded-2xl space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-pink-500/10 text-pink-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <h3 class="font-bold text-sm text-white">ব্র্যান্ডেড ফটো কার্ড ইঞ্জিন</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">সোশ্যাল মিডিয়ার জন্য হাই-রেজুলেশন কাস্টম ফ্রেম, ব্র্যান্ডিং ও অটো ওয়াটারমার্ক সাপোর্ট।</p>
                </div>

                <div class="bg-slate-950/60 border border-slate-800 p-5 rounded-2xl space-y-2">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <h3 class="font-bold text-sm text-white">২৪/৭ ভিআইপি অ্যাকাউন্ট ম্যানেজার</h3>
                    <p class="text-xs text-slate-400 leading-relaxed">সার্বক্ষণিক টেকনিক্যাল সাপোর্ট, প্রায়োরিটি ফোন ও ডেডিকেটেড এক্সিকিউটিভ অ্যাসিস্ট্যান্ট।</p>
                </div>
            </div>
        </div>

        {{-- Direct WhatsApp & Call Booking Box --}}
        <div class="bg-gradient-to-r from-amber-500/10 via-purple-500/10 to-indigo-500/10 border-2 border-amber-500/40 rounded-3xl p-8 text-center space-y-4">
            <h3 class="text-2xl font-black text-white">আপনার নিউজ হাউজের জন্য কাস্টম সমাধান প্রয়োজন?</h3>
            <p class="text-xs text-slate-300 max-w-xl mx-auto leading-relaxed">
                আমাদের এক্সিকিউটিভ টিমের সাথে সরাসরি আলোচনা করে আপনার পোর্টালের জন্য সবচেয়ে সাশ্রয়ী ও পাওয়ারফুল VIP প্যাকেজ নির্ধারণ করুন।
            </p>
            <div class="flex flex-wrap items-center justify-center gap-4 pt-2">
                <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $config['whatsapp_number'] ?? '8801771545972') }}?text={{ urlencode('Hello, I want to discuss a VIP Enterprise plan for our news organization.') }}" target="_blank" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow-lg transition flex items-center gap-2">
                    <i class="fa-brands fa-whatsapp text-sm"></i>
                    <span>সরাসরি WhatsApp এ কথা বলুন</span>
                </a>
                <a href="tel:{{ $config['contact_phone'] ?? '+8801771545972' }}" class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-extrabold text-xs rounded-xl border border-slate-700 transition flex items-center gap-2">
                    <i class="fa-solid fa-phone text-xs"></i>
                    <span>হটলাইনে কল করুন: {{ $config['contact_phone'] ?? '+880 1771-545972' }}</span>
                </a>
            </div>
        </div>

        {{-- Footer Link --}}
        <div class="text-center py-8 text-xs text-slate-500">
            <p>© {{ date('Y') }} Subeditor24 • অল-ইন-ওয়ান নিউজ পোর্টাল অটোমেশন ও এআই নিউজরুম</p>
        </div>

    </div>
</div>

<script>
    function setVipPricingMode(mode) {
        const btnSpecial = document.getElementById('vipTabModeSpecial');
        const btnRegular = document.getElementById('vipTabModeRegular');
        const btnStandard = document.getElementById('vipTabModeStandard');

        if (!btnSpecial || !btnRegular || !btnStandard) return;

        [btnSpecial, btnRegular, btnStandard].forEach(btn => {
            btn.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-300 hover:text-white cursor-pointer';
        });

        if (mode === 'special') {
            btnSpecial.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 shadow-lg cursor-pointer';
            document.querySelectorAll('.vip-price-box-special').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.vip-price-box-regular').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.vip-price-box-standard').forEach(el => el.classList.add('hidden'));
        } else if (mode === 'regular') {
            btnRegular.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg cursor-pointer';
            document.querySelectorAll('.vip-price-box-special').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.vip-price-box-regular').forEach(el => el.classList.remove('hidden'));
            document.querySelectorAll('.vip-price-box-standard').forEach(el => el.classList.add('hidden'));
        } else {
            btnStandard.className = 'px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 bg-slate-700 text-white shadow-lg cursor-pointer';
            document.querySelectorAll('.vip-price-box-special').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.vip-price-box-regular').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.vip-price-box-standard').forEach(el => el.classList.remove('hidden'));
        }
    }
</script>
@endsection
