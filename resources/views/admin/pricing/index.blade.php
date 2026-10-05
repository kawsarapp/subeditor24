@extends('layouts.app')

@section('title', 'Pricing & Coupon Manager - Super Admin')

@section('content')
<div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8 font-bangla">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-tags text-indigo-600"></i> Dynamic Pricing & Coupon Manager
            </h1>
            <p class="text-slate-500 text-sm mt-1">প্যাকেজের মূল্য, দৈনিক লিমিট, অফার ডিসকাউন্ট এবং প্রোমো কুপন পরিচালনা করুন।</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pricing.index') }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>View Public Pricing Page</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-xl shadow-xs font-semibold text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Tabs for Plans vs Coupons --}}
    <div class="space-y-8">

        {{-- 1. PRICING PLANS SECTION --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-black">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Active Subscription Plans (প্যাকেজ সমূহ)</h2>
                        <p class="text-xs text-slate-500">প্যাকেজের মূল্য, ফিচার ও দৈনিক লিমিট ডায়নামিক পরিবর্তন করুন</p>
                    </div>
                </div>
                <button type="button" onclick="openNewPlanModal()" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add New Plan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-extrabold uppercase tracking-wider bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-4">Plan Name</th>
                            <th class="py-3 px-4">Standard Price</th>
                            <th class="py-3 px-4">6-Month Price</th>
                            <th class="py-3 px-4 text-amber-600 dark:text-amber-400">Special Price</th>
                            <th class="py-3 px-4">Daily Limits & Features</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @foreach($plans as $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span>{{ $p->name }}</span>
                                    @if($p->is_vip)
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 flex items-center gap-1">
                                            <i class="fa-solid fa-crown text-[8px]"></i> VIP
                                        </span>
                                    @endif
                                    @if($p->is_popular)
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-indigo-100 text-indigo-700">POPULAR</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $p->slug }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">৳{{ number_format($p->standard_price) }}</td>
                            <td class="py-3.5 px-4 text-indigo-600 font-mono font-bold">৳{{ number_format($p->regular_discount_price) }}</td>
                            <td class="py-3.5 px-4 text-amber-600 font-mono font-black">৳{{ number_format($p->special_price) }}</td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                @if(!empty($p->custom_limits) && is_array($p->custom_limits))
                                    @foreach(array_slice($p->custom_limits, 0, 3) as $k => $v)
                                        <div class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                                            • {{ $k }}: <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ (string)$v === '-1' ? 'Unlimited' : $v }}</span>
                                        </div>
                                    @endforeach
                                    @if(count($p->custom_limits) > 3)
                                        <div class="text-[10px] text-slate-400">+{{ count($p->custom_limits) - 3 }} more limit boxes</div>
                                    @endif
                                @else
                                    <div>📰 {{ $p->daily_news_label }}</div>
                                    <div class="text-[10px] text-slate-400">💬 {{ $p->quotation_cards_label }} | 👥 {{ $p->reporters_label }}</div>
                                @endif
                                <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">✨ {{ count($p->features ?? []) }} Features Included</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $p->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $p->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="openEditPlanModal({{ json_encode($p) }})" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.pricing.plans.delete', $p->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই প্ল্যানটি ডিলিট করতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition cursor-pointer" title="Delete Plan">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 2. COUPONS & PROMO CODES SECTION --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-black">
                        <i class="fa-solid fa-ticket"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Promo Coupons & Discounts (কুপন ম্যানেজার)</h2>
                        <p class="text-xs text-slate-500">বিশেষ ক্যাম্পেইনের জন্য কুপন কোড ও ডিসকাউন্ট তৈরি করুন</p>
                    </div>
                </div>
                <button type="button" onclick="openNewCouponModal()" class="px-4 py-2 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> Add New Coupon
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-extrabold uppercase tracking-wider bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-4">Coupon Code</th>
                            <th class="py-3 px-4">Discount</th>
                            <th class="py-3 px-4">Min Order</th>
                            <th class="py-3 px-4">Usage Count</th>
                            <th class="py-3 px-4">Expiry Date</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($coupons as $c)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800 dark:text-white">
                                <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700">{{ $c->code }}</span>
                                @if($c->description)
                                    <div class="text-[10px] text-slate-400 font-sans mt-0.5">{{ $c->description }}</div>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-amber-600 font-mono">
                                {{ $c->discount_type === 'percentage' ? $c->discount_value . '%' : '৳' . number_format($c->discount_value) }} OFF
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-500">৳{{ number_format($c->min_order_amount) }}</td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $c->uses_count }} / {{ $c->max_uses ?? '∞' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">
                                {{ $c->expires_at ? $c->expires_at->format('d M, Y') : 'No Expiry' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $c->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $c->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="openEditCouponModal({{ json_encode($c) }})" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition cursor-pointer">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.pricing.coupons.delete', $c->id) }}" method="POST" onsubmit="return confirm('আপনি কি এই কুপনটি ডিলিট করতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400 text-xs font-medium">কোনো কুপন তৈরি করা হয়নি।</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. PRICING PAGE TEXT & LAYOUT CONFIGURATION --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Page Layout, Banner & Text Settings (পেজের সকল টেক্সট)</h2>
                    <p class="text-xs text-slate-500">হেডার টাইটেল, অফারের নাম, ফ্রি ট্রায়াল ব্যানার, পলিসি কার্ড এবং কন্টাক্ট ইনফো পরিবর্তন করুন</p>
                </div>
            </div>

            <form action="{{ route('admin.pricing.config.save') }}" method="POST" class="space-y-6 text-xs">
                @csrf

                {{-- 0. Navigation Menu Visibility Control --}}
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 rounded-2xl border border-indigo-500/30 shadow-md space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-indigo-600/30 border border-indigo-400/30 flex items-center justify-center text-indigo-300">
                                <i class="fa-solid fa-eye-slash text-sm"></i>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-white text-xs uppercase tracking-wider">ন্যাভিগেশন বার ভিজিবিলিটি কন্ট্রোল (Menu Visibility)</h3>
                                <p class="text-[11px] text-slate-300">সুপার এডমিন ছাড়া বাকি সাধারণ ইউজার ও পাবলিক ভিজিটরদের মেনু থেকে প্রাইসিং অপশন হাইড রাখুন</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                            Super Admin Exclusive
                        </span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 border-t border-white/10">
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 cursor-pointer transition">
                            <input type="checkbox" name="hide_pricing_from_nav" value="1" {{ !empty($config['hide_pricing_from_nav']) ? 'checked' : '' }} class="mt-0.5 w-4 h-4 text-indigo-500 rounded border-slate-600 focus:ring-indigo-400 focus:ring-offset-slate-900">
                            <div>
                                <div class="text-xs font-bold text-white">Hide Regular Pricing Menu</div>
                                <div class="text-[11px] text-slate-400">ন্যাভবার ও ড্রপডাউন থেকে 'Special Pricing Plans' অপশনটি সাধারণদের জন্য হাইড থাকবে।</div>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl bg-white/5 hover:bg-white/10 border border-white/10 cursor-pointer transition">
                            <input type="checkbox" name="hide_vip_pricing_from_nav" value="1" {{ !empty($config['hide_vip_pricing_from_nav']) ? 'checked' : '' }} class="mt-0.5 w-4 h-4 text-amber-500 rounded border-slate-600 focus:ring-amber-400 focus:ring-offset-slate-900">
                            <div>
                                <div class="text-xs font-bold text-amber-300">Hide VIP Pricing Menu</div>
                                <div class="text-[11px] text-slate-400">ন্যাভবার ও ড্রপডাউন থেকে 'VIP Enterprise Plans' অপশনটি সাধারণদের জন্য হাইড থাকবে।</div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Header & Subtitle --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-heading text-indigo-500"></i> ১. টপ হেডার ও টাইটেল সেটিংস
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Top Offer Badge Text</label>
                            <input type="text" name="page_badge" value="{{ $config['page_badge'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-semibold" placeholder="যেমন: ৬ মাসের জন্য বিশেষ Discount Offer">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Page Main Title</label>
                            <input type="text" name="page_title" value="{{ $config['page_title'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-extrabold text-sm" placeholder="যেমন: Special Pricing Plans">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Subtitle / Offer Description</label>
                        <textarea name="page_subtitle" rows="2" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">{{ $config['page_subtitle'] ?? '' }}</textarea>
                    </div>
                </div>

                {{-- Tab Switcher Names --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-toggle-on text-indigo-500"></i> ২. মোড সুইচার ট্যাব লেবেল (৩টি ট্যাব)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tab 1: Special Offer Name</label>
                            <input type="text" name="tab_special_title" value="{{ $config['tab_special_title'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-amber-600 font-bold" placeholder="🔥 October Special (সর্বোচ্চ ছাড়)">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tab 2: Regular 6-Month Name</label>
                            <input type="text" name="tab_regular_title" value="{{ $config['tab_regular_title'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-indigo-600 font-bold" placeholder="⏳ Regular 6-Month Discount">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tab 3: Standard Price Name</label>
                            <input type="text" name="tab_standard_title" value="{{ $config['tab_standard_title'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold" placeholder="Standard Price">
                        </div>
                    </div>
                </div>

                {{-- Free Trial Banner --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-gift text-emerald-500"></i> ৩. ফ্রি ট্রায়াল ব্যানার সেটিংস
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Banner Title</label>
                            <input type="text" name="free_trial_banner_title" value="{{ $config['free_trial_banner_title'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Button Text</label>
                            <input type="text" name="free_trial_btn_text" value="{{ $config['free_trial_btn_text'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold" placeholder="ফ্রি সাইন-আপ করুন">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Banner Description</label>
                        <input type="text" name="free_trial_banner_desc" value="{{ $config['free_trial_banner_desc'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200">
                    </div>
                </div>

                {{-- Policy & Guarantee Cards --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-shield-halved text-purple-500"></i> ৪. পলিসি ও গ্যারান্টি বক্স
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Policy Card 1 Title</label>
                            <input type="text" name="policy_card_1_title" value="{{ $config['policy_card_1_title'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-lg p-2 bg-slate-50 dark:bg-slate-800 font-bold">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Policy Card 1 Description</label>
                            <textarea name="policy_card_1_desc" rows="2" class="w-full border border-slate-300 dark:border-slate-700 rounded-lg p-2 bg-slate-50 dark:bg-slate-800">{{ $config['policy_card_1_desc'] ?? '' }}</textarea>
                        </div>
                        <div class="p-3 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 space-y-2">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Policy Card 2 Title</label>
                            <input type="text" name="policy_card_2_title" value="{{ $config['policy_card_2_title'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-lg p-2 bg-slate-50 dark:bg-slate-800 font-bold">
                            <label class="block font-bold text-slate-700 dark:text-slate-300">Policy Card 2 Description</label>
                            <textarea name="policy_card_2_desc" rows="2" class="w-full border border-slate-300 dark:border-slate-700 rounded-lg p-2 bg-slate-50 dark:bg-slate-800">{{ $config['policy_card_2_desc'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Contact & WhatsApp Settings --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-headset text-cyan-500"></i> ৫. সাপোর্ট ও যোগাযোগ সেটিংস
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">WhatsApp Number (Digits only)</label>
                            <input type="text" name="whatsapp_number" value="{{ $config['whatsapp_number'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold" placeholder="8801975389599">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Phone</label>
                            <input type="text" name="contact_phone" value="{{ $config['contact_phone'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold" placeholder="+880 1975-389599">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ $config['contact_email'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold" placeholder="support@newsmanage24.com">
                        </div>
                    </div>
                </div>

                {{-- 📊 Meta / Facebook Pixel Tracking Settings --}}
                <div class="bg-blue-50/50 dark:bg-blue-950/20 p-4 rounded-2xl border border-blue-200/80 dark:border-blue-900/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-blue-900 dark:text-blue-300 text-xs uppercase tracking-wider flex items-center gap-2">
                            <i class="fa-brands fa-facebook text-blue-600 text-sm"></i> ৬. Meta (Facebook) Pixel কনফিগারেশন
                        </h3>
                        <span class="text-[10px] font-extrabold text-blue-700 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/60 px-2 py-0.5 rounded-full">Boost Tracking</span>
                    </div>
                    <p class="text-[11px] text-slate-600 dark:text-slate-400">
                        ফেসবুক অ্যাডস ম্যানেজার বা ইভেন্ট ম্যানেজার থেকে আপনার <strong>Pixel ID</strong> টি এখানে দিন। এটি স্বয়ংক্রিয়ভাবে পুরো ওয়েবসাইটের সব পেজে <code>PageView</code>, <code>Lead</code>, <code>InitiateCheckout</code> এবং <code>WhatsApp Contact</code> ট্র্যাক করবে।
                    </p>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 text-xs mb-1">Meta Pixel ID (15-16 ডিজিট)</label>
                        <input type="text" name="facebook_pixel_id" value="{{ $config['facebook_pixel_id'] ?? '' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-mono font-bold text-xs" placeholder="e.g. 1138240454378873">
                    </div>
                </div>

                {{-- Link to VIP Module --}}
                <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-crown text-amber-500 text-lg"></i>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-white text-xs">ভিআইপি প্যাকেজ ম্যানেজ করতে চান?</h4>
                            <p class="text-[11px] text-slate-500">বড় মিডিয়া হাউজ ও এজেন্সির প্যাকেজের জন্য ডেডিকেটেড VIP প্রাইসিং ম্যানেজার ব্যবহার করুন।</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.vip-pricing.index') }}" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black rounded-xl text-xs hover:from-amber-600 hover:to-orange-600 transition shrink-0">
                        Go to VIP Manager <i class="fa-solid fa-arrow-right ml-1"></i>
                    </a>
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold rounded-2xl shadow-lg shadow-emerald-600/20 transition cursor-pointer flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Save All Regular Page Settings</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

{{-- EDIT PLAN MODAL --}}
<div id="planModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white flex justify-between items-center shrink-0">
            <h3 class="text-base font-bold flex items-center gap-2" id="planModalTitle">
                <i class="fa-solid fa-pen-to-square"></i> Edit Pricing Plan
            </h3>
            <button type="button" onclick="closePlanModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form action="{{ route('admin.pricing.plans.save') }}" method="POST" class="p-6 overflow-y-auto space-y-4 font-bangla text-xs">
            @csrf
            <input type="hidden" name="id" id="planModalId">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Plan Name</label>
                    <input type="text" name="name" id="planModalName" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold uppercase" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Slug</label>
                    <input type="text" name="slug" id="planModalSlug" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Standard Price (৳)</label>
                    <input type="number" name="standard_price" id="planModalStandardPrice" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">6-Month Price (৳)</label>
                    <input type="number" name="regular_discount_price" id="planModalRegularPrice" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-indigo-600 font-bold" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Special Price (৳)</label>
                    <input type="number" name="special_price" id="planModalSpecialPrice" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-amber-600 font-bold" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Special Offer Name</label>
                    <input type="text" name="special_offer_name" id="planModalSpecialOfferName" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="October Special" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Badge Text (Optional)</label>
                    <input type="text" name="badge" id="planModalBadge" placeholder="e.g. ⭐ MOST POPULAR" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                </div>
            </div>

            {{-- Dynamic Custom Limit Boxes Repeater --}}
            <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders text-indigo-500"></i> Plan Limits & Metrics (লিমিট বক্সসমূহ)
                        </label>
                        <p class="text-[10px] text-slate-400">আপনি ইচ্ছামতো যেকোনো নতুন লিমিট বক্স যোগ বা ডিলিট করতে পারেন (-1 মানে Unlimited)</p>
                    </div>
                    <button type="button" onclick="addCustomLimitBoxRow()" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:hover:bg-indigo-900 dark:text-indigo-300 rounded-lg text-xs font-bold border border-indigo-200 dark:border-indigo-800 transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-plus"></i> + Add Limit Box
                    </button>
                </div>

                <div id="customLimitsContainer" class="space-y-2">
                    {{-- Injected dynamically by JS --}}
                </div>
            </div>

            {{-- Dynamic Custom Features Textarea --}}
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    ✨ Plan Features & Bullet Points (প্রতি লাইনে একটি করে ফিচার লিখুন)
                </label>
                <textarea name="features" id="planModalFeatures" rows="6" placeholder="50 News per Day&#10;Unlimited News Photocard&#10;05 Quotation Cards&#10;Viral Predictor&#10;SEO & Website Intelligence..." class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-3 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-sans text-xs leading-relaxed"></textarea>
                <p class="text-[11px] text-slate-400 mt-1">💡 প্রতি লাইনে যা লিখবেন, সেটি পাবলিক প্রাইসিং কার্ডে চেকমার্ক সহ বুলেট পয়েন্ট হিসেবে ডায়নামিক প্রদর্শিত হবে।</p>
            </div>

            <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold">
                    <input type="checkbox" name="is_popular" id="planModalIsPopular" value="1" class="w-4 h-4 text-indigo-600 rounded">
                    <span>Highlight as Most Popular</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-bold">
                    <input type="checkbox" name="is_active" id="planModalIsActive" value="1" checked class="w-4 h-4 text-emerald-600 rounded">
                    <span>Active Status</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800 shrink-0">
                <button type="button" onclick="closePlanModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold shadow-md transition cursor-pointer">Save Plan</button>
            </div>
        </form>
    </div>
</div>

{{-- COUPON MODAL --}}
<div id="couponModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col">
        <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex justify-between items-center">
            <h3 class="text-base font-bold flex items-center gap-2" id="couponModalTitle">
                <i class="fa-solid fa-ticket"></i> Add New Coupon
            </h3>
            <button type="button" onclick="closeCouponModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form action="{{ route('admin.pricing.coupons.save') }}" method="POST" class="p-6 space-y-4 font-bangla text-xs">
            @csrf
            <input type="hidden" name="id" id="couponModalId">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Coupon Code</label>
                    <input type="text" name="code" id="couponModalCode" placeholder="e.g. OCTOBER57" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono font-bold uppercase" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Discount Type</label>
                    <select name="discount_type" id="couponModalType" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-semibold">
                        <option value="percentage">Percentage (% Discount)</option>
                        <option value="fixed">Fixed Amount (৳ Flat Discount)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Discount Value</label>
                    <input type="number" step="0.01" name="discount_value" id="couponModalValue" placeholder="e.g. 10 or 2000" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-amber-600 font-bold" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Min Order Amount (৳)</label>
                    <input type="number" name="min_order_amount" id="couponModalMinOrder" value="0" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Max Uses (Blank for unl.)</label>
                    <input type="number" name="max_uses" id="couponModalMaxUses" placeholder="e.g. 50" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Expiry Date (Optional)</label>
                    <input type="date" name="expires_at" id="couponModalExpiresAt" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                <input type="text" name="description" id="couponModalDesc" placeholder="e.g. Special 10% promo for media houses" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
            </div>

            <div class="flex items-center gap-4 pt-1">
                <label class="flex items-center gap-2 cursor-pointer font-bold">
                    <input type="checkbox" name="is_active" id="couponModalIsActive" value="1" checked class="w-4 h-4 text-emerald-600 rounded">
                    <span>Active Status</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" onclick="closeCouponModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl font-bold shadow-md transition cursor-pointer">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<script>
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function addCustomLimitBoxRow(key = '', val = '') {
        const container = document.getElementById('customLimitsContainer');
        if (!container) return;

        const rowId = 'limitRow_' + Math.random().toString(36).substr(2, 9);
        const row = document.createElement('div');
        row.id = rowId;
        row.className = 'flex items-center gap-2 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs transition-all';
        row.innerHTML = `
            <div class="flex-1">
                <input type="text" name="custom_limit_keys[]" value="${escapeHtml(key)}" placeholder="Limit Name (e.g. Daily News, Quotes, YouTube Channels)" class="w-full text-xs font-bold text-slate-800 dark:text-slate-200 bg-transparent border-0 focus:ring-0 p-1" required>
            </div>
            <div class="w-32">
                <input type="text" name="custom_limit_values[]" value="${escapeHtml(val)}" placeholder="Value (e.g. 50 / -1)" class="w-full text-xs font-mono font-bold text-indigo-600 dark:text-indigo-400 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-1.5 text-center focus:ring-1 focus:ring-indigo-500" required>
            </div>
            <button type="button" onclick="document.getElementById('${rowId}').remove()" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-xs transition cursor-pointer shrink-0" title="Delete this box">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(row);
    }

    function openNewPlanModal() {
        document.getElementById('planModalId').value = '';
        document.getElementById('planModalName').value = '';
        document.getElementById('planModalSlug').value = '';
        document.getElementById('planModalStandardPrice').value = '';
        document.getElementById('planModalRegularPrice').value = '';
        document.getElementById('planModalSpecialPrice').value = '';
        document.getElementById('planModalSpecialOfferName').value = 'October Special';
        document.getElementById('planModalBadge').value = '';
        document.getElementById('planModalFeatures').value = '';
        document.getElementById('planModalIsPopular').checked = false;
        document.getElementById('planModalIsActive').checked = true;
        document.getElementById('planModalTitle').innerHTML = '<i class="fa-solid fa-plus"></i> Add New Pricing Plan';

        // Render default starter boxes that user can add/remove freely
        const container = document.getElementById('customLimitsContainer');
        if (container) {
            container.innerHTML = '';
            addCustomLimitBoxRow('Daily News', '50');
            addCustomLimitBoxRow('Quotation Cards', '5');
            addCustomLimitBoxRow('Reporters', '10');
            addCustomLimitBoxRow('Bangla Websites', '10');
            addCustomLimitBoxRow('English Websites', '0');
            addCustomLimitBoxRow('News Photocard', '-1');
        }

        const modal = document.getElementById('planModal');
        if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
    }

    function openEditPlanModal(plan) {
        document.getElementById('planModalId').value = plan.id;
        document.getElementById('planModalName').value = plan.name;
        document.getElementById('planModalSlug').value = plan.slug;
        document.getElementById('planModalStandardPrice').value = plan.standard_price;
        document.getElementById('planModalRegularPrice').value = plan.regular_discount_price;
        document.getElementById('planModalSpecialPrice').value = plan.special_price;
        document.getElementById('planModalSpecialOfferName').value = plan.special_offer_name;
        document.getElementById('planModalBadge').value = plan.badge || '';
        document.getElementById('planModalFeatures').value = Array.isArray(plan.features) ? plan.features.join('\n') : '';
        document.getElementById('planModalIsPopular').checked = Boolean(plan.is_popular);
        document.getElementById('planModalIsActive').checked = Boolean(plan.is_active);
        document.getElementById('planModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square"></i> Edit Pricing Plan: ' + plan.name;

        // Render all existing custom limit boxes dynamically
        const container = document.getElementById('customLimitsContainer');
        if (container) {
            container.innerHTML = '';
            if (plan.custom_limits && typeof plan.custom_limits === 'object' && Object.keys(plan.custom_limits).length > 0) {
                Object.entries(plan.custom_limits).forEach(([k, v]) => {
                    addCustomLimitBoxRow(k, v);
                });
            } else {
                addCustomLimitBoxRow('Daily News', plan.daily_news_limit ?? 50);
                addCustomLimitBoxRow('Quotation Cards', plan.quotation_cards_limit ?? 5);
                addCustomLimitBoxRow('Reporters', plan.reporters_limit ?? 10);
                addCustomLimitBoxRow('Bangla Websites', plan.bangla_websites_limit ?? 10);
                addCustomLimitBoxRow('English Websites', plan.english_websites_limit ?? 0);
                addCustomLimitBoxRow('News Photocard', plan.news_photocard_limit ?? -1);
            }
        }

        const modal = document.getElementById('planModal');
        if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
    }

    function closePlanModal() {
        const modal = document.getElementById('planModal');
        if (modal) modal.classList.add('hidden'), modal.classList.remove('flex');
    }

    function openNewCouponModal() {
        document.getElementById('couponModalId').value = '';
        document.getElementById('couponModalCode').value = '';
        document.getElementById('couponModalType').value = 'percentage';
        document.getElementById('couponModalValue').value = '';
        document.getElementById('couponModalMinOrder').value = '0';
        document.getElementById('couponModalMaxUses').value = '';
        document.getElementById('couponModalExpiresAt').value = '';
        document.getElementById('couponModalDesc').value = '';
        document.getElementById('couponModalIsActive').checked = true;
        document.getElementById('couponModalTitle').innerHTML = '<i class="fa-solid fa-ticket"></i> Add New Coupon';

        const modal = document.getElementById('couponModal');
        if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
    }

    function openEditCouponModal(coupon) {
        document.getElementById('couponModalId').value = coupon.id;
        document.getElementById('couponModalCode').value = coupon.code;
        document.getElementById('couponModalType').value = coupon.discount_type;
        document.getElementById('couponModalValue').value = coupon.discount_value;
        document.getElementById('couponModalMinOrder').value = coupon.min_order_amount;
        document.getElementById('couponModalMaxUses').value = coupon.max_uses || '';
        document.getElementById('couponModalExpiresAt').value = coupon.expires_at ? coupon.expires_at.split('T')[0] : '';
        document.getElementById('couponModalDesc').value = coupon.description || '';
        document.getElementById('couponModalIsActive').checked = Boolean(coupon.is_active);
        document.getElementById('couponModalTitle').innerHTML = '<i class="fa-solid fa-ticket"></i> Edit Coupon';

        const modal = document.getElementById('couponModal');
        if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
    }

    function closeCouponModal() {
        const modal = document.getElementById('couponModal');
        if (modal) modal.classList.add('hidden'), modal.classList.remove('flex');
    }
</script>
@endsection
