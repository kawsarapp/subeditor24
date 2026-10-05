@extends('layouts.app')

@section('title', '👑 VIP Enterprise Pricing Manager - Super Admin')

@section('content')
<div class="max-w-6xl mx-auto py-10 px-4 sm:px-6 lg:px-8 font-bangla">
    
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-600 dark:text-amber-400 text-xs font-black uppercase mb-1">
                <i class="fa-solid fa-crown text-[10px]"></i> VIP Dedicated Module
            </div>
            <h1 class="text-3xl font-black text-slate-800 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-crown text-amber-500"></i> VIP Enterprise Pricing Manager
            </h1>
            <p class="text-slate-500 text-sm mt-1">বড় মিডিয়া হাউজ ও এজেন্সির জন্য এক্সক্লুসিভ VIP প্যাকেজ, মূল্য, এন্টারপ্রাইজ ফিচার ও টেক্সট পরিচালনা করুন।</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pricing.vip') }}" target="_blank" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 rounded-xl text-xs font-black transition flex items-center gap-1.5 shadow-md">
                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                <span>View Public VIP Pricing Page</span>
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 mb-6 rounded-xl shadow-xs font-semibold text-xs flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="space-y-8">

        {{-- 1. ACTIVE VIP PLANS SECTION --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center text-lg font-black border border-amber-200 dark:border-amber-800/60">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Active VIP Packages (ভিআইপি প্যাকেজসমূহ)</h2>
                        <p class="text-xs text-slate-500">ভিআইপি প্যাকেজের মূল্য, কাস্টম লিমিট ও এন্টারপ্রাইজ ফিচার ম্যানেজ করুন</p>
                    </div>
                </div>
                <button type="button" onclick="openNewVipPlanModal()" class="px-4 py-2 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:to-orange-600 text-slate-950 rounded-xl text-xs font-black shadow-md transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-plus"></i> + Add New VIP Package
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 text-slate-500 font-extrabold uppercase tracking-wider bg-slate-50 dark:bg-slate-800/50">
                            <th class="py-3 px-4">VIP Plan Name</th>
                            <th class="py-3 px-4">Standard Price</th>
                            <th class="py-3 px-4">6-Month Price</th>
                            <th class="py-3 px-4 text-amber-600 dark:text-amber-400">Special Price</th>
                            <th class="py-3 px-4">Limits & Metrics</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($vipPlans as $p)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800 dark:text-white">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ $p->name }}</span>
                                    @if($p->badge)
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">{{ $p->badge }}</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $p->slug }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-500 font-mono">৳{{ number_format($p->standard_price) }}</td>
                            <td class="py-3.5 px-4 text-indigo-600 font-mono font-bold">৳{{ number_format($p->regular_discount_price) }}</td>
                            <td class="py-3.5 px-4 text-amber-600 font-mono font-black text-sm">৳{{ number_format($p->special_price) }}</td>
                            <td class="py-3.5 px-4 text-slate-600 dark:text-slate-300">
                                @if(!empty($p->custom_limits) && is_array($p->custom_limits))
                                    @foreach(array_slice($p->custom_limits, 0, 3) as $k => $v)
                                        <div class="text-[11px] font-semibold text-slate-700 dark:text-slate-200">
                                            • {{ $k }}: <span class="text-amber-600 dark:text-amber-400 font-bold">{{ (string)$v === '-1' ? 'Unlimited' : $v }}</span>
                                        </div>
                                    @endforeach
                                    @if(count($p->custom_limits) > 3)
                                        <div class="text-[10px] text-slate-400">+{{ count($p->custom_limits) - 3 }} more limit metrics</div>
                                    @endif
                                @else
                                    <div class="text-amber-600 font-bold">👑 Unlimited Full Access</div>
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
                                    <button type="button" onclick="openEditVipPlanModal({{ json_encode($p) }})" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 rounded-lg text-xs font-bold transition cursor-pointer">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.vip-pricing.plans.delete', $p->id) }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ভিআইপি প্ল্যানটি ডিলিট করতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition cursor-pointer" title="Delete VIP Plan">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs font-medium">
                                কোনো VIP প্যাকেজ তৈরি করা হয়নি। উপরের <strong>"+ Add New VIP Package"</strong> বাটনে ক্লিক করে নতুন VIP প্যাকেজ যুক্ত করুন।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 2. VIP PAGE TEXT & LAYOUT CONFIGURATION --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-slate-950 flex items-center justify-center text-lg font-black">
                    <i class="fa-solid fa-crown"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">VIP Page Layout & Text Settings (ভিআইপি পেজের টেক্সটসমূহ)</h2>
                    <p class="text-xs text-slate-500">ভিআইপি পেজের টাইটেল, সাবটাইটেল, এন্টারপ্রাইজ সুবিধা, বুকিং বাটন ও হটলাইন নম্বর পরিচালনা করুন</p>
                </div>
            </div>

            <form action="{{ route('admin.vip-pricing.config.save') }}" method="POST" class="space-y-6 text-xs">
                @csrf

                {{-- VIP Header & Titles --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-heading text-amber-500"></i> ১. ভিআইপি টপ হেডার ও টাইটেল
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">VIP Badge Text</label>
                            <input type="text" name="vip_badge" value="{{ $config['vip_badge'] ?? '👑 VIP / CUSTOM ENTERPRISE' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">VIP Page Main Title</label>
                            <input type="text" name="vip_title" value="{{ $config['vip_title'] ?? 'কাস্টম এন্টারপ্রাইজ ও মিডিয়া এজেন্সি স্যুট' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-extrabold text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">VIP Subtitle / Overview</label>
                        <textarea name="vip_subtitle" rows="2" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-medium">{{ $config['vip_subtitle'] ?? '' }}</textarea>
                    </div>
                </div>

                {{-- Tab Switchers --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-toggle-on text-amber-500"></i> ২. মোড সুইচার ট্যাব লেবেল
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tab 1: Special Price Name</label>
                            <input type="text" name="tab_special_title" value="{{ $config['tab_special_title'] ?? '🔥 Special Price' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-amber-600 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tab 2: Regular 6-Month Name</label>
                            <input type="text" name="tab_regular_title" value="{{ $config['tab_regular_title'] ?? '⏳ Regular 6-Month' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-indigo-600 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Tab 3: Standard Price Name</label>
                            <input type="text" name="tab_standard_title" value="{{ $config['tab_standard_title'] ?? 'Standard Price' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 font-bold">
                        </div>
                    </div>
                </div>

                {{-- Executive Contact & Hotline --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-headset text-cyan-500"></i> ৩. এক্সিকিউটিভ যোগাযোগ ও হটলাইন সেটিংস
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Executive WhatsApp (Digits only)</label>
                            <input type="text" name="whatsapp_number" value="{{ $config['whatsapp_number'] ?? '8801975389599' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Executive Hotline Phone</label>
                            <input type="text" name="contact_phone" value="{{ $config['contact_phone'] ?? '+880 1975-389599' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Executive Email</label>
                            <input type="email" name="contact_email" value="{{ $config['contact_email'] ?? 'support@newsmanage24.com' }}" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-bold">
                        </div>
                    </div>
                </div>

                {{-- VIP Features / Pillars Textarea --}}
                <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <h3 class="font-bold text-slate-800 dark:text-white text-xs uppercase tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-list-check text-emerald-500"></i> ৪. এন্টারপ্রাইজ সুবিধা তালিকা (প্রতি লাইনে ১টি)
                    </h3>
                    @php
                        $vfText = is_array($config['vip_plan_features'] ?? null) ? implode("\n", $config['vip_plan_features']) : ($config['vip_plan_features'] ?? '');
                    @endphp
                    <textarea name="vip_plan_features" rows="6" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-3 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-sans text-xs leading-relaxed">{{ $vfText }}</textarea>
                </div>

                {{-- Save Button --}}
                <div class="flex justify-end pt-2">
                    <button type="submit" class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black rounded-2xl shadow-lg transition cursor-pointer flex items-center gap-2 text-sm">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Save VIP Page Settings</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

{{-- VIP PLAN MODAL --}}
<div id="vipPlanModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200 dark:border-slate-800 max-h-[90vh] flex flex-col">
        <div class="px-6 py-4 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-slate-950 flex justify-between items-center shrink-0">
            <h3 class="text-base font-black flex items-center gap-2" id="vipPlanModalTitle">
                <i class="fa-solid fa-crown"></i> Add VIP Pricing Package
            </h3>
            <button type="button" onclick="closeVipPlanModal()" class="w-8 h-8 rounded-full bg-black/10 hover:bg-black/20 text-slate-950 font-black flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form action="{{ route('admin.vip-pricing.plans.save') }}" method="POST" class="p-6 overflow-y-auto space-y-4 font-bangla text-xs">
            @csrf
            <input type="hidden" name="id" id="vipPlanModalId">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">VIP Plan Name</label>
                    <input type="text" name="name" id="vipPlanModalName" placeholder="e.g. VIP MEDIA NETWORK" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold uppercase" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Slug</label>
                    <input type="text" name="slug" id="vipPlanModalSlug" placeholder="e.g. vip-media-network" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-mono" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Standard Price (৳)</label>
                    <input type="number" name="standard_price" id="vipPlanModalStandardPrice" placeholder="50000" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-bold" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">6-Month Price (৳)</label>
                    <input type="number" name="regular_discount_price" id="vipPlanModalRegularPrice" placeholder="35000" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-indigo-600 font-bold" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Special Price (৳)</label>
                    <input type="number" name="special_price" id="vipPlanModalSpecialPrice" placeholder="25000" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-amber-600 font-bold" required>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Special Offer Name</label>
                    <input type="text" name="special_offer_name" id="vipPlanModalSpecialOfferName" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200" value="October Special" required>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Badge Text (Optional)</label>
                    <input type="text" name="badge" id="vipPlanModalBadge" placeholder="e.g. 👑 VIP ENTERPRISE" class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-2.5 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                </div>
            </div>

            {{-- Dynamic Custom Limit Boxes Repeater --}}
            <div class="bg-slate-50 dark:bg-slate-800/60 p-4 rounded-2xl border border-slate-200 dark:border-slate-700/80">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <label class="block font-bold text-slate-800 dark:text-slate-200 text-xs flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders text-amber-500"></i> VIP Limits & Metrics (লিমিট বক্সসমূহ)
                        </label>
                        <p class="text-[10px] text-slate-400">-1 মানে Unlimited। ইচ্ছামতো নতুন লিমিট মেট্রিক যোগ বা ডিলিট করুন।</p>
                    </div>
                    <button type="button" onclick="addVipLimitBoxRow()" class="px-3 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 rounded-lg text-xs font-bold border border-amber-300 dark:border-amber-800 transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-plus"></i> + Add Metric
                    </button>
                </div>

                <div id="vipLimitsContainer" class="space-y-2">
                    {{-- Injected dynamically --}}
                </div>
            </div>

            {{-- Features Textarea --}}
            <div>
                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">
                    ✨ VIP Plan Features (প্রতি লাইনে একটি করে লিখুন)
                </label>
                <textarea name="features" id="vipPlanModalFeatures" rows="6" placeholder="⚡ Dedicated Cloud Cluster Server&#10;🤖 Custom AI Model Fine-Tuning&#10;🏢 Central Multi-Portal Hub&#10;👥 Unlimited Reporters & News Desks&#10;🎨 Custom Graphic Photocard Engine..." class="w-full border border-slate-300 dark:border-slate-700 rounded-xl p-3 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-200 font-sans text-xs leading-relaxed"></textarea>
            </div>

            <div class="flex items-center gap-6 pt-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold">
                    <input type="checkbox" name="is_popular" id="vipPlanModalIsPopular" value="1" class="w-4 h-4 text-amber-600 rounded">
                    <span>Highlight as Top Choice</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer font-bold">
                    <input type="checkbox" name="is_active" id="vipPlanModalIsActive" value="1" checked class="w-4 h-4 text-emerald-600 rounded">
                    <span>Active Status</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t border-slate-200 dark:border-slate-800 shrink-0">
                <button type="button" onclick="closeVipPlanModal()" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-xl font-bold cursor-pointer">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black rounded-xl shadow-md transition cursor-pointer">Save VIP Plan</button>
            </div>
        </form>
    </div>
</div>

<script>
    function escapeVipHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    function addVipLimitBoxRow(key = '', val = '') {
        const container = document.getElementById('vipLimitsContainer');
        if (!container) return;

        const rowId = 'vipLimitRow_' + Math.random().toString(36).substr(2, 9);
        const row = document.createElement('div');
        row.id = rowId;
        row.className = 'flex items-center gap-2 bg-white dark:bg-slate-900 p-2 rounded-xl border border-slate-200 dark:border-slate-700 shadow-2xs transition-all';
        row.innerHTML = `
            <div class="flex-1">
                <input type="text" name="custom_limit_keys[]" value="${escapeVipHtml(key)}" placeholder="Limit Name (e.g. Daily News, Reporters, AI Limit)" class="w-full text-xs font-bold text-slate-800 dark:text-slate-200 bg-transparent border-0 focus:ring-0 p-1" required>
            </div>
            <div class="w-32">
                <input type="text" name="custom_limit_values[]" value="${escapeVipHtml(val)}" placeholder="Value (-1 for unl.)" class="w-full text-xs font-mono font-bold text-amber-600 dark:text-amber-400 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg p-1.5 text-center focus:ring-1 focus:ring-amber-500" required>
            </div>
            <button type="button" onclick="document.getElementById('${rowId}').remove()" class="w-7 h-7 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 flex items-center justify-center text-xs transition cursor-pointer shrink-0" title="Delete this box">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        container.appendChild(row);
    }

    function openNewVipPlanModal() {
        document.getElementById('vipPlanModalId').value = '';
        document.getElementById('vipPlanModalName').value = '';
        document.getElementById('vipPlanModalSlug').value = '';
        document.getElementById('vipPlanModalStandardPrice').value = '';
        document.getElementById('vipPlanModalRegularPrice').value = '';
        document.getElementById('vipPlanModalSpecialPrice').value = '';
        document.getElementById('vipPlanModalSpecialOfferName').value = 'October Special';
        document.getElementById('vipPlanModalBadge').value = '👑 VIP ENTERPRISE';
        document.getElementById('vipPlanModalFeatures').value = '⚡ Dedicated High-Performance Cloud Cluster\n🤖 Custom Editorial AI Fine-Tuning\n🏢 Multi-Portal Central Hub\n👥 Unlimited Journalists & Desks\n🎨 Unlimited Graphic Photocard Engine\n🔒 White-Label Custom Domain & SLA\n📞 24/7 Dedicated Account Manager';
        document.getElementById('vipPlanModalIsPopular').checked = false;
        document.getElementById('vipPlanModalIsActive').checked = true;
        document.getElementById('vipPlanModalTitle').innerHTML = '<i class="fa-solid fa-crown"></i> Add New VIP Pricing Plan';

        const container = document.getElementById('vipLimitsContainer');
        if (container) {
            container.innerHTML = '';
            addVipLimitBoxRow('Daily News', '-1');
            addVipLimitBoxRow('News Photocard', '-1');
            addVipLimitBoxRow('Quotation Cards', '-1');
            addVipLimitBoxRow('Reporters & Staff', '-1');
            addVipLimitBoxRow('Bangla Websites', '-1');
            addVipLimitBoxRow('English Websites', '-1');
        }

        const modal = document.getElementById('vipPlanModal');
        if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
    }

    function openEditVipPlanModal(plan) {
        document.getElementById('vipPlanModalId').value = plan.id;
        document.getElementById('vipPlanModalName').value = plan.name;
        document.getElementById('vipPlanModalSlug').value = plan.slug;
        document.getElementById('vipPlanModalStandardPrice').value = plan.standard_price;
        document.getElementById('vipPlanModalRegularPrice').value = plan.regular_discount_price;
        document.getElementById('vipPlanModalSpecialPrice').value = plan.special_price;
        document.getElementById('vipPlanModalSpecialOfferName').value = plan.special_offer_name;
        document.getElementById('vipPlanModalBadge').value = plan.badge || '';
        document.getElementById('vipPlanModalFeatures').value = Array.isArray(plan.features) ? plan.features.join('\n') : '';
        document.getElementById('vipPlanModalIsPopular').checked = Boolean(plan.is_popular);
        document.getElementById('vipPlanModalIsActive').checked = Boolean(plan.is_active);
        document.getElementById('vipPlanModalTitle').innerHTML = '<i class="fa-solid fa-crown"></i> Edit VIP Plan: ' + plan.name;

        const container = document.getElementById('vipLimitsContainer');
        if (container) {
            container.innerHTML = '';
            if (plan.custom_limits && typeof plan.custom_limits === 'object' && Object.keys(plan.custom_limits).length > 0) {
                Object.entries(plan.custom_limits).forEach(([k, v]) => {
                    addVipLimitBoxRow(k, v);
                });
            } else {
                addVipLimitBoxRow('Daily News', plan.daily_news_limit ?? -1);
                addVipLimitBoxRow('News Photocard', plan.news_photocard_limit ?? -1);
                addVipLimitBoxRow('Quotation Cards', plan.quotation_cards_limit ?? -1);
                addVipLimitBoxRow('Reporters & Staff', plan.reporters_limit ?? -1);
                addVipLimitBoxRow('Bangla Websites', plan.bangla_websites_limit ?? -1);
                addVipLimitBoxRow('English Websites', plan.english_websites_limit ?? -1);
            }
        }

        const modal = document.getElementById('vipPlanModal');
        if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
    }

    function closeVipPlanModal() {
        const modal = document.getElementById('vipPlanModal');
        if (modal) modal.classList.add('hidden'), modal.classList.remove('flex');
    }
</script>
@endsection
