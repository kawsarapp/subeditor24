@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2.5">
                ⚙️ প্রোফাইল ও সেটিংস
            </h1>
            <p class="text-gray-500 mt-1 text-sm">আপনার নিউজ কার্ড, AI ইন্টিগ্রেশন, প্রক্সি এবং অটোমেশন কনফিগারেশন</p>
        </div>
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl shadow-lg text-center">
            <p class="text-xs opacity-80 uppercase tracking-wider">বর্তমান ব্যালেন্স</p>
            <p class="text-2xl font-bold">{{ auth()->user()->credits }} <span class="text-sm font-normal">Credits</span></p>
        </div>
    </div>

    <!-- Global Accordion Expand/Collapse Controls -->
    <div class="flex flex-wrap justify-between items-center bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6 gap-3">
        <div class="flex items-center gap-2 text-xs text-gray-600 font-semibold">
            <i class="fas fa-layer-group text-indigo-600 text-sm"></i>
            <span>সেটিংস সেকশনগুলো ডিফল্টভাবে ফোল্ড করা (Collapsed) রয়েছে।</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="expandAllSettings()" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                <i class="fas fa-expand-alt"></i> সব খুলুন (Expand All)
            </button>
            <button type="button" onclick="collapseAllSettings()" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg border border-gray-300 flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                <i class="fas fa-compress-alt"></i> সব বন্ধ করুন (Collapse All)
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center gap-2" role="alert">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <ul class="list-disc pl-5 font-medium text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- ১. প্রোফাইল আপডেট সেকশন (Collapsible) --}}
    <form action="{{ route('settings.update-profile') }}" method="POST" class="mb-6">
        @csrf
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            👤 আমার প্রোফাইল ও পাসওয়ার্ড
                        </h2>
                        <p class="text-xs text-gray-500">নাম, ইমেইল এবং পাসওয়ার্ড পরিবর্তন করুন</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-indigo-600 font-semibold hidden sm:inline">খুলতে ক্লিক করুন</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">আপনার নাম</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">ইমেইল (লগিন ইউজারনেম)</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">নতুন পাসওয়ার্ড</label>
                        <input type="password" name="password" placeholder="পরিবর্তন করতে চাইলে লিখুন..." 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">পাসওয়ার্ড নিশ্চিত করুন</label>
                        <input type="password" name="password_confirmation" placeholder="একই পাসওয়ার্ড আবার লিখুন" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                </div>
                <div class="mt-5 text-right">
                    <button type="submit" class="bg-gray-800 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-gray-900 transition shadow cursor-pointer text-sm">
                        প্রোফাইল আপডেট করুন
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- ২. মূল সেটিংস ফর্ম শুরু --}}
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_proxy'))
        {{-- 🔥 SCRAPER PROXY & DECODO SETTINGS (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-blue-50/40 hover:bg-blue-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-base">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🌐 স্ক্র্যাপার ও প্রক্সি সেটিংস (Decodo Universal API)
                        </h2>
                        <p class="text-xs text-gray-500">Decodo Universal API, Puppeteer Proxy ও Auto Clean</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Decodo / Proxy</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
                    <p class="text-xs text-gray-600 font-medium">নিউজ স্ক্র্যাপ করার জন্য নিজস্ব প্রক্সি এবং Decodo Universal Scraping API কনফিগারেশন। এগুলো খালি রাখলে গ্লোবাল সিস্টেমের (Super Admin) বা .env এর মান ব্যবহার করা হবে।</p>
                    <button type="button" onclick="testDecodoProxy()" class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-lg transition font-bold shadow-sm flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                        <i class="fas fa-vial"></i> <span>⚡ Test Connection</span>
                    </button>
                </div>
                <div id="decodo_proxy_status_msg" class="text-xs font-bold mb-4 whitespace-pre-line"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Standard Proxy -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <h3 class="font-bold text-gray-700 mb-3 border-b pb-1">Standard Proxy (Puppeteer & Python)</h3>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Proxy Host</label>
                                <input type="text" id="proxy_host" name="proxy_host" value="{{ old('proxy_host', $settings->proxy_host ?? '') }}" placeholder="proxy.example.com" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Proxy Port</label>
                                <input type="number" id="proxy_port" name="proxy_port" value="{{ old('proxy_port', $settings->proxy_port ?? '') }}" placeholder="10000" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Username (Optional)</label>
                                <input type="text" id="proxy_username" name="proxy_username" value="{{ old('proxy_username', $settings->proxy_username ?? '') }}" placeholder="username" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Password (Optional)</label>
                                <input type="password" id="proxy_password" name="proxy_password" value="{{ old('proxy_password', $settings->proxy_password ?? '') }}" placeholder="••••••••" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                            </div>
                        </div>
                    </div>

                    <!-- Universal Scraping API -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-blue-700 mb-3 border-b pb-1">🚀 Decodo / SmartProxy Universal API</h3>
                            <p class="text-xs mb-3 text-gray-500">কঠিন সাইট (যমুনা টিভি, প্রথম আলো, Cloudflare-যুক্ত সাইট) স্ক্র্যাপ করার শক্তিশালী API টোকেন।</p>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Decodo API Token (Basic Auth Token)</label>
                                <input type="password" id="smartproxy_api_token" name="smartproxy_api_token" value="{{ old('smartproxy_api_token', $settings->smartproxy_api_token ?? '') }}" placeholder="Basic VTAwM..." class="w-full border-gray-300 rounded shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs">
                                <p class="text-[10px] text-gray-400 mt-1">Decodo ড্যাশবোর্ড থেকে পাওয়া <code>Basic Auth Token</code> দিন।</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Auto Clean Section --}}
                <div class="mt-5 pt-4 border-t border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        🧹 Auto Clean Pending News After (Days)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="auto_clean_days"
                               min="1" max="90"
                               value="{{ $settings->auto_clean_days ?? 7 }}"
                               class="w-28 border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-center font-bold text-base">
                        <p class="text-xs text-gray-500">দিন পরে যে নিউজ পোস্ট করা হয়নি সেগুলো অটোমেটিক ডিলিট হবে। (Default: 7 দিন)</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin')
        {{-- ROI Config Section (Collapsible) --}}
        @php
            $roiConfig = isset($settings->roi_config) ? (is_string($settings->roi_config) ? json_decode($settings->roi_config, true) : $settings->roi_config) : [];
        @endphp
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-green-50/40 hover:bg-green-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-700 flex items-center justify-center text-base">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            💰 ROI Calculator Config (সুপার অ্যাডমিন)
                        </h2>
                        <p class="text-xs text-gray-500">কাজের মান অনুযায়ী কত টাকা সাশ্রয় হচ্ছে তা কনফিগার করুন</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-green-100 text-green-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">ROI Settings</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">কর্মী খরচ (প্রতি ঘণ্টা BDT)</label>
                        <input type="number" name="roi_hourly_rate" value="{{ $roiConfig['hourly_rate'] ?? 100 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">১টি নিউজ করতে সময় (মিনিট)</label>
                        <input type="number" name="roi_news_minutes" value="{{ $roiConfig['news_minutes'] ?? 20 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">১টি কার্ড বানাতে সময় (মিনিট)</label>
                        <input type="number" name="roi_card_minutes" value="{{ $roiConfig['card_minutes'] ?? 15 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                </div>
            </div>
        </div>

        {{-- 🎨 Studio Template & Media Manager Links (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-slate-50 hover:bg-slate-100 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-base">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🎨 স্টুডিও টেমপ্লেট ও মিডিয়া ম্যানেজার শর্টকাট
                        </h2>
                        <p class="text-xs text-gray-500">টেমপ্লেট লেআউট তৈরি, ফ্রেম ও ফন্ট আপলোড পরিচালনা</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-indigo-100 text-indigo-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Templates & Fonts</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">🎨 Studio Template Manager</h3>
                            <p class="text-xs text-gray-500 mt-1 mb-4">Dashboard থেকে নতুন template যোগ করুন — frame URL, position সব এক জায়গায় কনফিগার করুন।</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.templates.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 rounded-lg transition shadow-sm text-center">🎨 Templates</a>
                            <a href="{{ route('admin.templates.create') }}" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-bold text-xs py-2 rounded-lg transition text-center">+ Add New</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">📁 Media & Assets Manager</h3>
                            <p class="text-xs text-gray-500 mt-1 mb-4">Template এর Frame PNG এবং Custom Font (.ttf, .woff) আপলোড, রিনেইম, ও URL কপি করুন।</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.media.index') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs py-2 rounded-lg transition shadow-sm text-center">📁 Open Media Manager</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_branding'))
        {{-- ৩. ব্র্যান্ডিং সেকশন (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-base">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🎨 ব্র্যান্ডিং ও নিউজ কার্ড স্টাইল
                        </h2>
                        <p class="text-xs text-gray-500">ব্র্যান্ডের নাম, ডিফল্ট থিম কালার এবং লোগো</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-pink-100 text-pink-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Branding</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ব্র্যান্ড নাম (e.g. Dhaka Post)</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name ?? 'My News') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ডিফল্ট কালার থিম</label>
                        <select name="default_theme_color" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs font-semibold">
                            <option value="red" {{ ($settings->default_theme_color ?? '') == 'red' ? 'selected' : '' }}>Red (Breaking)</option>
                            <option value="blue" {{ ($settings->default_theme_color ?? '') == 'blue' ? 'selected' : '' }}>Blue (Standard)</option>
                            <option value="green" {{ ($settings->default_theme_color ?? '') == 'green' ? 'selected' : '' }}>Green (Sports/Islamic)</option>
                            <option value="purple" {{ ($settings->default_theme_color ?? '') == 'purple' ? 'selected' : '' }}>Purple (Lifestyle)</option>
                            <option value="black" {{ ($settings->default_theme_color ?? '') == 'black' ? 'selected' : '' }}>Black (Dark)</option>
                        </select>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">লোগো URL (অপশনাল)</label>
                        <input type="url" name="logo_url" value="{{ old('logo_url', $settings->logo_url ?? '') }}" placeholder="https://example.com/logo.png" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                        <p class="text-[11px] text-gray-500 mt-1">আপনি স্টুডিও থেকেও সরাসরি লোগো আপলোড করতে পারেন।</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_target_language'))
        {{-- 🔥 TARGET LANGUAGE SETTINGS (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-teal-50/40 hover:bg-teal-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-base">
                        <i class="fas fa-language"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🌍 Target Language (ডিফল্ট নিউজ ভাষা)
                        </h2>
                        <p class="text-xs text-gray-500">নিউজ প্রসেসিং ও স্ক্র্যাপিং এর ডিফল্ট ভাষা</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-teal-100 text-teal-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Language</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="bg-white p-4 rounded-lg border border-teal-200 shadow-sm">
                    <select name="target_language" class="w-full border-gray-300 rounded shadow-sm focus:border-teal-500 focus:ring-teal-500 font-semibold text-xs">
                        <option value="" {{ empty($settings->target_language) ? 'selected' : '' }}>Website Default (ওয়েবসাইটের সেটিং অনুযায়ী)</option>
                        <option value="bn" {{ ($settings->target_language ?? '') == 'bn' ? 'selected' : '' }}>Always Bengali (বাংলা)</option>
                        <option value="en" {{ ($settings->target_language ?? '') == 'en' ? 'selected' : '' }}>Always English (ইংরেজি)</option>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-2">আপনি যখন কোনো নিউজ স্ক্র্যাপ করবেন, তখন এই ভাষা অনুযায়ী প্রসেস হবে। (তবে ওয়েবসাইটের সেটিংসে আলাদা ভাষা দেওয়া থাকলে সেটি প্রাধান্য পাবে)।</p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai'))
        {{-- 🔥 AI CONFIGURATION (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-indigo-50/40 hover:bg-indigo-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-base">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🤖 AI অপটিমাইজেশন সেটিংস (DeepSeek, Gemini, OpenAI)
                        </h2>
                        <p class="text-xs text-gray-500">Primary AI নির্বাচন এবং বিভিন্ন AI API Keys ও Models</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-indigo-100 text-indigo-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">AI Engines</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <p class="text-xs text-indigo-700 mb-5 font-medium">প্রতিটি প্রোভাইডারের জন্য API Key এবং Model সেট করতে পারবেন। (খালি রাখলে সিস্টেমের ডিফল্ট .env এরগুলো ব্যবহার হবে)</p>

                <div class="mb-6 bg-white p-4 rounded-lg border border-indigo-200 shadow-sm">
                    <label class="block text-xs font-bold text-gray-800 mb-2">⭐ Primary AI Provider</label>
                    <select name="primary_ai" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-indigo-900 text-xs">
                        <option value="deepseek" {{ ($settings->primary_ai ?? 'deepseek') == 'deepseek' ? 'selected' : '' }}>DeepSeek</option>
                        <option value="qwen" {{ ($settings->primary_ai ?? '') == 'qwen' ? 'selected' : '' }}>Qwen (Alibaba / DashScope)</option>
                        <option value="groq" {{ ($settings->primary_ai ?? '') == 'groq' ? 'selected' : '' }}>Groq (Llama / Mixtral)</option>
                        <option value="huggingface" {{ ($settings->primary_ai ?? '') == 'huggingface' ? 'selected' : '' }}>Hugging Face (Inference API)</option>
                        <option value="openai" {{ ($settings->primary_ai ?? '') == 'openai' ? 'selected' : '' }}>OpenAI (ChatGPT)</option>
                        <option value="gemini" {{ ($settings->primary_ai ?? '') == 'gemini' ? 'selected' : '' }}>Gemini (Google)</option>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-2">উপরের লিস্ট থেকে যে AI টি সিলেক্ট করবেন, নিউজ লেখার সময় সিস্টেম সবার আগে সেটি ব্যবহার করার চেষ্টা করবে। সেটি ফেইল করলে বাকিগুলো অটোমেটিক চেষ্টা করবে।</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Gemini -->
                    <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <h3 class="font-bold text-gray-700 mb-2 text-xs">Gemini (Google)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" name="gemini_api_key" value="{{ old('gemini_api_key', $settings->gemini_api_key ?? '') }}" placeholder="AIzaSy... (খালি রাখলে .env ব্যবহার হবে)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select name="gemini_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (gemini-1.5-flash)</option>
                                    <option value="gemini-2.5-flash" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-flash' ? 'selected' : '' }}>Gemini 2.5 Flash (Fast + Balance)</option>
                                    <option value="gemini-2.5-pro" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-pro' ? 'selected' : '' }}>Gemini 2.5 Pro (Complex Reasoning)</option>
                                    <option value="gemini-2.5-flash-lite" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-flash-lite' ? 'selected' : '' }}>Gemini 2.5 Flash-Lite (High Volume)</option>
                                    <option value="gemini-3.1-pro-preview" {{ ($settings->gemini_model ?? '') == 'gemini-3.1-pro-preview' ? 'selected' : '' }}>Gemini 3.1 Pro Preview (Latest Reasoning)</option>
                                    <option value="gemini-3.1-flash-lite-preview" {{ ($settings->gemini_model ?? '') == 'gemini-3.1-flash-lite-preview' ? 'selected' : '' }}>Gemini 3.1 Flash Lite Preview (Efficient)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- DeepSeek -->
                    <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <h3 class="font-bold text-gray-700 mb-2 text-xs">DeepSeek</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" name="deepseek_api_key" value="{{ old('deepseek_api_key', $settings->deepseek_api_key ?? '') }}" placeholder="sk-... (খালি রাখলে .env ব্যবহার হবে)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select name="deepseek_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (deepseek-chat)</option>
                                    <option value="deepseek-chat" {{ ($settings->deepseek_model ?? '') == 'deepseek-chat' ? 'selected' : '' }}>DeepSeek V3 (deepseek-chat)</option>
                                    <option value="deepseek-reasoner" {{ ($settings->deepseek_model ?? '') == 'deepseek-reasoner' ? 'selected' : '' }}>DeepSeek R1 (deepseek-reasoner)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Qwen (DashScope) -->
                    <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <h3 class="font-bold text-gray-700 mb-2 text-xs">Qwen (DashScope API)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" name="qwen_api_key" value="{{ old('qwen_api_key', $settings->qwen_api_key ?? '') }}" placeholder="sk-... (DashScope API Key)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select name="qwen_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (qwen-turbo)</option>
                                    <option value="qwen-turbo" {{ ($settings->qwen_model ?? '') == 'qwen-turbo' ? 'selected' : '' }}>Qwen Turbo (Fast & Cheap)</option>
                                    <option value="qwen-plus" {{ ($settings->qwen_model ?? '') == 'qwen-plus' ? 'selected' : '' }}>Qwen Plus (Balanced)</option>
                                    <option value="qwen-max" {{ ($settings->qwen_model ?? '') == 'qwen-max' ? 'selected' : '' }}>Qwen Max (Best Quality)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Groq -->
                    <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <h3 class="font-bold text-gray-700 mb-2 text-xs">Groq (Ultra-Fast LPU)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" name="groq_api_key" value="{{ old('groq_api_key', $settings->groq_api_key ?? '') }}" placeholder="gsk_... (Groq Console)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select name="groq_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (llama-3.3-70b-versatile)</option>
                                    <option value="llama-3.3-70b-versatile" {{ ($settings->groq_model ?? '') == 'llama-3.3-70b-versatile' ? 'selected' : '' }}>Llama 3.3 70B (Versatile)</option>
                                    <option value="llama-3.1-8b-instant" {{ ($settings->groq_model ?? '') == 'llama-3.1-8b-instant' ? 'selected' : '' }}>Llama 3.1 8B (Instant Speed)</option>
                                    <option value="mixtral-8x7b-32768" {{ ($settings->groq_model ?? '') == 'mixtral-8x7b-32768' ? 'selected' : '' }}>Mixtral 8x7B (MoE)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Hugging Face -->
                    <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <h3 class="font-bold text-gray-700 mb-2 text-xs">Hugging Face (Inference API)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">User Access Token</label>
                                <input type="password" name="huggingface_api_key" value="{{ old('huggingface_api_key', $settings->huggingface_api_key ?? '') }}" placeholder="hf_... (HuggingFace Access Token)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Repository ID</label>
                                <input type="text" name="huggingface_model" value="{{ old('huggingface_model', $settings->huggingface_model ?? '') }}" placeholder="e.g. meta-llama/Llama-3.2-3B-Instruct" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                        </div>
                    </div>

                    <!-- OpenAI -->
                    <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <h3 class="font-bold text-gray-700 mb-2 text-xs">OpenAI (ChatGPT)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" name="openai_api_key" value="{{ old('openai_api_key', $settings->openai_api_key ?? '') }}" placeholder="sk-proj-... (খালি রাখলে .env ব্যবহার হবে)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select name="openai_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (gpt-4o-mini)</option>
                                    <option value="gpt-4o-mini" {{ ($settings->openai_model ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Fast & Cheap)</option>
                                    <option value="gpt-4o" {{ ($settings->openai_model ?? '') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o (Smartest)</option>
                                    <option value="o1-mini" {{ ($settings->openai_model ?? '') == 'o1-mini' ? 'selected' : '' }}>o1-mini (Reasoning)</option>
                                    <option value="o3-mini" {{ ($settings->openai_model ?? '') == 'o3-mini' ? 'selected' : '' }}>o3-mini (Advanced Reasoning)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai'))
        {{-- 🔥 PHOTOROOM API SETTINGS (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-purple-50/40 hover:bg-purple-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-base">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            📸 PhotoRoom API (ছবি ব্যাকগ্রাউন্ড রিমুভাল)
                        </h2>
                        <p class="text-xs text-gray-500">কাস্টম ফটো কার্ড তৈরির সময় ফটোর ব্যাকগ্রাউন্ড এক ক্লিকে রিমুভ</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-purple-100 text-purple-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Background Remover</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <p class="text-xs text-purple-700 mb-4 font-medium">কাস্টম ফটো কার্ড তৈরির সময় ফটোর ব্যাকগ্রাউন্ড এক ক্লিকে রিমুভ (Cutout) করার জন্য PhotoRoom API Key দিন। Super Admin যা সেট করবেন, সকল সাব-এডিটর ও স্টাফ এটি স্বয়ংক্রিয়ভাবে ব্যবহার করতে পারবে।</p>

                <div class="bg-white p-5 rounded-lg border border-purple-200 shadow-sm">
                    <label class="block text-xs font-bold text-gray-700 mb-1">PhotoRoom API Key (x-api-key)</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="password" id="photoroom_api_key" name="photoroom_api_key" value="{{ old('photoroom_api_key', $settings->photoroom_api_key ?? '') }}" placeholder="sk_pr_..." class="w-full border-gray-300 rounded shadow-sm focus:border-purple-500 focus:ring-purple-500 font-mono text-xs">
                        <button type="button" onclick="testPhotoRoom()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded shadow-sm flex items-center justify-center gap-2 whitespace-nowrap transition cursor-pointer">
                            <i class="fas fa-vial"></i> <span>Test Connection</span>
                        </button>
                    </div>
                    <div id="photoroom_status_msg" class="text-xs font-bold mt-2"></div>
                    <p class="text-[11px] text-gray-500 mt-2">
                        <i class="fas fa-info-circle text-purple-500"></i> আপনার <a href="https://www.photoroom.com/api" target="_blank" class="text-purple-600 underline font-semibold">PhotoRoom Developer Console</a> থেকে API Key টি সংগ্রহ করুন।
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_wp_laravel'))
        {{-- 🔥 WordPress কানেকশন (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-base">
                        <i class="fab fa-wordpress"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🔗 WordPress কানেকশন
                        </h2>
                        <p class="text-xs text-gray-500">ওয়ার্ডপ্রেস সাইট URL, ইউজারনেম এবং অ্যাপ্লিকেশন পাসওয়ার্ড</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">WordPress</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-xs text-gray-500">সেটিংস সেভ করার আগে ওয়ার্ডপ্রেস সাইট কানেকশন যাচাই করুন:</p>
                    <button type="button" onclick="testWordPress()" class="text-xs bg-gray-100 text-gray-700 px-3.5 py-2 rounded-lg hover:bg-gray-200 transition font-bold border border-gray-300 cursor-pointer">
                        ⚡ Test Connection
                    </button>
                </div>
                
                <p id="wp_status_msg" class="text-xs font-bold mb-4"></p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">ওয়েবসাইট লিংক (URL)</label>
                        <input type="url" id="wp_url" name="wp_url" value="{{ old('wp_url', $settings->wp_url ?? '') }}" placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ইউজারনেম (Username)</label>
                        <input type="text" id="wp_username" name="wp_username" value="{{ old('wp_username', $settings->wp_username ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">App Password</label>
                        <input type="password" id="wp_app_password" name="wp_app_password" value="{{ old('wp_app_password', $settings->wp_app_password ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs" placeholder="abcd efgh ijkl mnop">
                        <p class="text-[11px] text-gray-500 mt-1">WP Admin > Users > Profile > Application Passwords এ গিয়ে তৈরি করুন।</p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- 🔥 UNIVERSAL & CUSTOM WEBSITE CONNECTION SECTION (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-slate-50 hover:bg-slate-100 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-base">
                        <i class="fas fa-plug"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🔌 Website API Integration (Laravel / Next.js / Custom CMS)
                        </h2>
                        <p class="text-xs text-gray-500">REST API, Webhook, ফিল্ড ম্যাপিং ও কোড জেনারেটর</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-slate-200 text-slate-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">REST API / Webhook</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-4 border-b border-gray-200 pb-3 gap-2">
                    <p class="text-xs text-gray-600 font-medium">আপনার ওয়েবসাইটের সাথে স্বয়ংক্রিয় সংবাদ প্রকাশের সংযোগ কনফিগারেশন।</p>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openCodeGeneratorModal()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-3.5 py-1.5 rounded-lg hover:bg-indigo-100 transition shadow-sm cursor-pointer">
                            <i class="fas fa-code"></i> Code Generator
                        </button>
                        <a href="{{ route('docs.api-guide') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-300 px-3.5 py-1.5 rounded-lg hover:bg-slate-200 transition">
                            <i class="fas fa-book-open text-slate-500"></i> Documentation
                        </a>
                    </div>
                </div>

                <!-- Connection Status Message Box -->
                <div id="custom_api_status_box" class="hidden mb-4 p-3 rounded-lg text-xs font-bold"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Base / Website URL -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Website Base URL</label>
                        <input type="url" id="laravel_site_url" name="laravel_site_url" value="{{ old('laravel_site_url', $settings->laravel_site_url ?? '') }}" 
                               placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                        <p class="text-[11px] text-gray-500 mt-1">আপনার ওয়েবসাইটের মূল ডোমেইন লিংক (যেমন: <code>https://mywebsite.com</code>)।</p>
                    </div>

                    <!-- API Secret Token -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">API Secret Token</label>
                        <div class="flex gap-2">
                            <input type="text" id="laravel_api_token" name="laravel_api_token" value="{{ old('laravel_api_token', $settings->laravel_api_token ?? '') }}" 
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition font-mono text-xs" placeholder="e.g. sec_token_2026_xyz">
                            <button type="button" onclick="generateRandomToken()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 flex-shrink-0 flex items-center gap-1 cursor-pointer" title="নতুন সিকিউর টোকেন জেনারেট করুন">
                                <i class="fas fa-sync-alt text-[10px]"></i> Generate
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">সার্ভার হ্যান্ডশেক ও সিকিউরিটি ভেরিফিকেশনে ব্যবহৃত গোপন চাবি।</p>
                    </div>

                    <!-- Route Prefix -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">News Link Prefix</label>
                        <div class="flex items-center">
                            <span class="bg-gray-100 border border-r-0 border-gray-300 px-3 py-2 rounded-l text-gray-500 text-xs">/</span>
                            <input type="text" name="laravel_route_prefix" value="{{ old('laravel_route_prefix', $settings->laravel_route_prefix ?? 'news') }}" 
                                   class="w-full border-gray-300 rounded-r shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs" 
                                   placeholder="news, post, article">
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">পোস্টের লিংক ফরম্যাট: <code>site.com/<b>news</b>/123</code></p>
                    </div>

                    <!-- Enable Auto Post Checkbox -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer bg-slate-50 hover:bg-slate-100 p-3.5 rounded-lg border border-slate-200 w-full transition">
                            <input type="hidden" name="post_to_laravel" value="0">
                            <input type="checkbox" name="post_to_laravel" value="1" {{ ($settings->post_to_laravel ?? false) ? 'checked' : '' }} class="toggle-checkbox w-5 h-5 text-indigo-600 rounded">
                            <div>
                                <span class="font-bold text-gray-800 text-xs block">Enable Auto-Publish to Website</span>
                                <span class="text-[11px] text-gray-500 block">চালু থাকলে সংবাদ অনুমোদনের পর সরাসরি আপনার ওয়েবসাইটে প্রকাশ হবে।</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Test Connection Button -->
                <div class="mt-4 pt-3 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">সেটিংস সেভ করার আগে এন্ডপয়েন্ট কানেকশন যাচাই করুন:</span>
                    <button type="button" onclick="testCustomApiConnection()" id="btn_test_custom_api" class="inline-flex items-center gap-2 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg transition shadow-sm cursor-pointer">
                        <i class="fas fa-plug"></i> Test Connection
                    </button>
                </div>

                {{-- ADVANCED CUSTOM FIELD MAPPER --}}
                <div class="mt-6 border border-slate-200 rounded-xl overflow-hidden bg-white">
                    <div class="bg-slate-100 p-4 border-b border-slate-200 flex justify-between items-center cursor-pointer select-none" onclick="toggleCustomApiVisual()">
                        <div class="flex items-center gap-2">
                            <i id="mapper_chevron" class="fas fa-chevron-down text-slate-500 text-xs transition-transform"></i>
                            <span class="font-bold text-slate-800 text-xs">Advanced Field Mapping & Custom Endpoints</span>
                            <span class="text-[10px] bg-slate-200 text-slate-700 font-semibold px-2 py-0.5 rounded">Optional</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-indigo-600 font-semibold hover:underline flex items-center gap-1">
                                <i class="fas fa-sliders-h"></i> Configure Fields
                            </span>
                            <button type="button" onclick="event.stopPropagation(); openAssistantHelpModal();" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 px-3 py-1 rounded-lg transition shadow-sm">
                                <i class="fas fa-info-circle text-indigo-600"></i> Guide & FAQ
                            </button>
                        </div>
                    </div>

                    <div id="custom-api-visual-section" class="p-5 space-y-6 hidden">
                        <div class="flex flex-wrap justify-between items-center bg-slate-50 border border-slate-200 p-3 rounded-lg text-xs text-slate-700 gap-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-info-circle text-slate-500 text-sm"></i>
                                <span>ডিফল্ট <code>/api/external-news-post</code> এর বাইরে কাস্টম পাথ বা ভিন্ন ফিল্ডের নাম থাকলে নিচে নির্ধারণ করুন।</span>
                            </div>
                            <button type="button" onclick="openAssistantHelpModal()" class="text-indigo-600 font-semibold hover:underline flex items-center gap-1 text-[11px]">
                                ইনটিগ্রেশন গাইড দেখুন <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Custom URLs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Custom News Post Endpoint URL (Optional)</label>
                                <input type="url" id="custom_api_url" name="custom_api_url" value="{{ old('custom_api_url', $settings->custom_api_url ?? '') }}" 
                                       placeholder="https://mywebsite.com/api/v1/articles/create" class="w-full border-slate-300 rounded-lg shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">খালি রাখলে <code>Base_URL/api/external-news-post</code> ব্যবহার হবে।</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Custom Category Fetch URL (Optional)</label>
                                <input type="url" id="custom_category_url" name="custom_category_url" value="{{ old('custom_category_url', $settings->custom_category_url ?? '') }}" 
                                       placeholder="https://mywebsite.com/api/v1/categories" class="w-full border-slate-300 rounded-lg shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">আপনার সাইটের ক্যাটাগরি ফেচ করার API URL।</p>
                            </div>
                        </div>

                        <!-- Auth & Format Options -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Authentication Method</label>
                                <select id="v_auth_type" onchange="syncVisualToMappingJson()" class="w-full border-slate-300 rounded shadow-sm text-xs focus:ring-indigo-500">
                                    <option value="Bearer">Bearer Token (Authorization: Bearer ...)</option>
                                    <option value="custom_header">Custom Header (e.g. X-API-KEY)</option>
                                    <option value="basic">Basic Auth (Authorization: Basic ...)</option>
                                    <option value="body">Inside Body / Payload (token: ...)</option>
                                </select>
                            </div>
                            <div id="v_auth_header_wrapper" style="display: none;">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Custom Header Name</label>
                                <input type="text" id="v_auth_header_name" oninput="syncVisualToMappingJson()" placeholder="X-API-KEY" class="w-full border-slate-300 rounded shadow-sm text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Image Format</label>
                                <select id="v_image_format" onchange="syncVisualToMappingJson()" class="w-full border-slate-300 rounded shadow-sm text-xs focus:ring-indigo-500">
                                    <option value="url">Image URL String (https://...)</option>
                                    <option value="file">Direct Binary File Upload (Multipart)</option>
                                    <option value="base64">Base64 Encoded Image Data</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Category Format</label>
                                <select id="v_category_type" onchange="syncVisualToMappingJson()" class="w-full border-slate-300 rounded shadow-sm text-xs focus:ring-indigo-500">
                                    <option value="id">Numeric Category ID (e.g. [1, 2])</option>
                                    <option value="name">Text Category Name (e.g. "জাতীয়")</option>
                                </select>
                            </div>
                        </div>

                        <!-- Visual Field Name Mapper -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Field Name Mappings (Default Payload ➔ Your API Parameter)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Title (শিরোনাম)</label>
                                    <input type="text" id="v_field_title" oninput="syncVisualToMappingJson()" placeholder="title / headline" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Content (বিস্তারিত)</label>
                                    <input type="text" id="v_field_content" oninput="syncVisualToMappingJson()" placeholder="content / body / desc" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Image (ছবি)</label>
                                    <input type="text" id="v_field_image" oninput="syncVisualToMappingJson()" placeholder="image / thumbnail_url" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Category (ক্যাটাগরি)</label>
                                    <input type="text" id="v_field_category" oninput="syncVisualToMappingJson()" placeholder="category / category_id" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tags / Hashtags</label>
                                    <input type="text" id="v_field_tags" oninput="syncVisualToMappingJson()" placeholder="tags / hashtags" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Slug (URL Slug)</label>
                                    <input type="text" id="v_field_slug" oninput="syncVisualToMappingJson()" placeholder="slug / alias" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Response ID Key</label>
                                    <input type="text" id="v_field_response_id_key" oninput="syncVisualToMappingJson()" placeholder="post_id / id" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Response URL Key</label>
                                    <input type="text" id="v_field_response_url_key" oninput="syncVisualToMappingJson()" placeholder="live_url / link / url" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                            </div>
                        </div>

                        <!-- Extra Static Key-Value Fields -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Additional Static Parameters (ঐচ্ছিক ফিল্ড)</h4>
                                <button type="button" onclick="addExtraFieldRow()" class="text-xs bg-white hover:bg-slate-100 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-300 cursor-pointer">
                                    + Add Parameter
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-500 mb-3">যেমন: আপনার ডাটাবেজে যদি <code>author_id = 1</code> বা <code>status = published</code> বাধ্যতামূলক থাকে, তবে এখানে যোগ করুন।</p>
                            
                            <div id="extra_fields_container" class="space-y-2">
                                <!-- Dynamic rows appended via JS -->
                            </div>
                        </div>

                        <!-- Raw JSON Mapping (Hidden sync & toggle) -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-[11px] font-bold text-slate-500">Raw JSON Mapping (Synchronized)</label>
                                <button type="button" onclick="toggleRawJson()" class="text-[11px] text-indigo-600 hover:underline cursor-pointer">View / Edit JSON</button>
                            </div>
                            <textarea id="custom_api_mapping" name="custom_api_mapping" rows="3" 
                                      class="w-full border-slate-300 rounded-lg shadow-sm text-xs font-mono focus:ring-indigo-500 focus:border-indigo-500 bg-slate-100 hidden" 
                                      placeholder='{"title":"title","content":"content"}'>{{ old('custom_api_mapping', is_array($settings->custom_api_mapping) ? json_encode($settings->custom_api_mapping) : ($settings->custom_api_mapping ?? '')) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- INTERACTIVE ASSISTANT HELP & FAQ MODAL --}}
        <div id="assistantHelpModal" class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-5xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden text-slate-100">
                <!-- Modal Header -->
                <div class="p-5 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">
                                Website Integration Guide & FAQ
                            </h3>
                            <p class="text-xs text-slate-400">যেকোনো ফ্রেমওয়ার্ক ও CMS ওয়েবসাইটের সাথে নিরাপদ ও নির্ভুল কানেকশনের পুঙ্খানুপুঙ্খ নির্দেশিকা।</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeAssistantHelpModal()" class="text-slate-400 hover:text-white text-xl p-2 rounded-lg hover:bg-slate-700 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-800 bg-slate-950 px-5 gap-2 text-xs font-bold">
                    <button type="button" onclick="switchHelpTab('walkthrough')" class="help-tab-btn px-4 py-3 border-b-2 border-indigo-500 text-indigo-400 flex items-center gap-2" id="htab_walkthrough">
                        <i class="fas fa-list-ol"></i> Step-by-Step Guide
                    </button>
                    <button type="button" onclick="switchHelpTab('faq')" class="help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2" id="htab_faq">
                        <i class="fas fa-question-circle"></i> Frequently Asked Questions
                    </button>
                    <button type="button" onclick="switchHelpTab('diagnostics')" class="help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2" id="htab_diagnostics">
                        <i class="fas fa-wrench"></i> Diagnostics & Troubleshooting
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="p-6 overflow-y-auto flex-1 bg-slate-900 space-y-6 text-sm text-slate-300">
                    
                    <!-- 1. WALKTHROUGH CONTENT -->
                    <div id="help_content_walkthrough" class="space-y-6">
                        <!-- Step 1 -->
                        <div class="bg-slate-800/80 p-5 rounded-xl border border-slate-700">
                            <h4 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">1</span>
                                প্রাথমিক সংযোগ (Base URL ও Secret Token)
                            </h4>
                            <p class="text-xs text-slate-300 leading-relaxed mb-3">
                                আপনার ওয়েবসাইটের মূল ডোমেইন দিন (যেমন: <code class="text-indigo-300 bg-slate-950 px-1.5 py-0.5 rounded">https://mywebsite.com</code>)। 
                                এরপর <strong>Generate</strong> বাটনে চাপ দিয়ে একটি স্ট্রং <strong>API Secret Token</strong> তৈরি করুন। এই টোকেনটি আপনার সার্ভার ও Subeditor24 এর মধ্যে সিকিউরিটি হ্যান্ডশেক করতে ব্যবহৃত হবে।
                            </p>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-slate-800/80 p-5 rounded-xl border border-slate-700">
                            <h4 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">2</span>
                                কোড জেনারেটর ব্যবহার করে এন্ডপয়েন্ট তৈরি
                            </h4>
                            <p class="text-xs text-slate-300 leading-relaxed mb-2">
                                উপরের <strong>Code Generator</strong> বাটনে চাপুন। আপনার ফ্রেমওয়ার্ক সিলেক্ট করে রেডিমেড কোড কপি করে আপনার প্রজেক্টের নির্দিষ্ট ফাইলে পেস্ট করুন:
                            </p>
                            <ul class="list-disc list-inside text-xs text-slate-400 space-y-1 ml-2">
                                <li><strong>Next.js (App Router):</strong> <code class="text-indigo-300 font-mono">app/api/external-news-post/route.ts</code></li>
                                <li><strong>Next.js (Pages Router):</strong> <code class="text-indigo-300 font-mono">pages/api/external-news-post.ts</code></li>
                                <li><strong>Node.js (Express):</strong> <code class="text-indigo-300 font-mono">routes/newsReceiver.js</code></li>
                                <li><strong>Laravel:</strong> <code class="text-indigo-300 font-mono">routes/api.php</code></li>
                                <li><strong>Raw PHP:</strong> <code class="text-indigo-300 font-mono">public/news-receiver.php</code> (সিঙ্গেল ড্রপ-ইন ফাইল)</li>
                                <li><strong>Python (FastAPI/Django):</strong> <code class="text-indigo-300 font-mono">main.py</code></li>
                            </ul>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-slate-800/80 p-5 rounded-xl border border-slate-700">
                            <h4 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">3</span>
                                লাইভ কানেকশন টেস্ট
                            </h4>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                কোড বসানো শেষ হলে <strong>Test Connection</strong> বাটনে ক্লিক করুন। সিস্টেম টেস্ট পে-লোড পাঠিয়ে হ্যান্ডশেক সফল হলে সবুজ সংকেত ও আপনার ডাটাবেজে তৈরি হওয়া টেস্ট পোস্ট আইডি দেখাবে।
                            </p>
                        </div>
                    </div>

                    <!-- 2. FAQ CONTENT -->
                    <div id="help_content_faq" class="space-y-4 hidden">
                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <h4 class="font-bold text-white text-sm mb-1.5">প্রশ্ন: সাব-এডিটর24 থেকে আমার সাইটে কী কী ডাটা পাঠানো হয়?</h4>
                            <p class="text-xs text-slate-300 leading-relaxed">উত্তর: শিরোনাম (title), বিস্তারিত সংবাদ (content/body), নির্বাচিত ক্যাটাগরি (category ID/name), ছবি লিংক বা বাইনারি ফাইল (image), ট্যাগ (tags) এবং স্ল্যাগ (slug)।</p>
                        </div>
                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <h4 class="font-bold text-white text-sm mb-1.5">প্রশ্ন: আমার সাইটের ফিল্ডের নাম ভিন্ন (যেমন headline বা description), কীভাবে মিলাবো?</h4>
                            <p class="text-xs text-slate-300 leading-relaxed">উত্তর: <strong>Advanced Field Mapping</strong> সেকশনে যান। সেখানে Title এর জায়গায় আপনার ফিল্ডের নাম (e.g. headline) এবং Content এর জায়গায় আপনার ফিল্ডের নাম (e.g. description) লিখে দিলেই সাব-এডিটর24 অটোমেটিক সেই নামে ডাটা পাঠাবে।</p>
                        </div>
                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700">
                            <h4 class="font-bold text-white text-sm mb-1.5">প্রশ্ন: সিকিউরিটি টোকেন কীভাবে যাচাই করব?</h4>
                            <p class="text-xs text-slate-300 leading-relaxed">উত্তর: সাব-এডিটর24 প্রতিটি রিকোয়েস্টের হেডারে <code>Authorization: Bearer <Secret_Token></code> পাঠায়। আপনার এন্ডপয়েন্টে শুধু চেক করবেন ইনকামিং টোকেনটি আপনার গোপন টোকেনের সমান কি না।</p>
                        </div>
                    </div>

                    <!-- 3. DIAGNOSTICS & TROUBLESHOOTING CONTENT -->
                    <div id="help_content_diagnostics" class="space-y-4 hidden">
                        <div class="bg-red-950/40 border border-red-800/60 p-4 rounded-xl">
                            <h4 class="font-bold text-red-300 text-sm flex items-center gap-2 mb-1.5">
                                <i class="fas fa-exclamation-triangle"></i> HTTP 401 Unauthorized
                            </h4>
                            <p class="text-xs text-slate-300 leading-relaxed">সমস্যা: API Secret Token মিলছে না।<br>সমাধান: Settings পেজের টোকেন এবং আপনার সার্ভারের <code>.env</code> বা রিসিভার ফাইলের গোপন টোকেন একই আছে কি না চেক করুন।</p>
                        </div>
                        <div class="bg-amber-950/40 border border-amber-800/60 p-4 rounded-xl">
                            <h4 class="font-bold text-amber-300 text-sm flex items-center gap-2 mb-1.5">
                                <i class="fas fa-exclamation-circle"></i> HTTP 404 Not Found
                            </h4>
                            <p class="text-xs text-slate-300 leading-relaxed">সমস্যা: API রুট/ইউআরএল খুঁজে পাওয়া যাচ্ছে না।<br>সমাধান: আপনার ডোমেইনের পর <code>/api/external-news-post</code> রুটটি কার্যকর আছে কি না ব্রাউজারে বা পোস্টম্যানে চেক করুন। ভিন্ন পাথ হলে Custom News Post Endpoint URL এ সম্পূর্ণ URL টি লিখুন।</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CODE GENERATOR MODAL --}}
        <div id="codeGeneratorModal" class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden text-slate-100">
                <!-- Modal Header -->
                <div class="p-5 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg">
                            <i class="fas fa-code"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Instant API Receiver Code Generator</h3>
                            <p class="text-xs text-slate-400">আপনার টেক-স্ট্যাক অনুযায়ী ড্রপ-ইন রিসিভার কোড কপি করুন।</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCodeGeneratorModal()" class="text-slate-400 hover:text-white text-xl p-2 rounded-lg hover:bg-slate-700 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Framework Selector Tabs -->
                <div class="flex flex-wrap border-b border-slate-800 bg-slate-950 px-5 gap-2 text-xs font-bold py-2">
                    <button type="button" onclick="switchCodeGenTab('next_app')" class="cg-tab-btn px-3 py-2 rounded-lg bg-indigo-600 text-white" id="cg_tab_next_app">Next.js (App Router)</button>
                    <button type="button" onclick="switchCodeGenTab('next_pages')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white" id="cg_tab_next_pages">Next.js (Pages)</button>
                    <button type="button" onclick="switchCodeGenTab('express')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white" id="cg_tab_express">Node.js (Express)</button>
                    <button type="button" onclick="switchCodeGenTab('laravel')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white" id="cg_tab_laravel">Laravel</button>
                    <button type="button" onclick="switchCodeGenTab('php')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white" id="cg_tab_php">Raw PHP File</button>
                    <button type="button" onclick="switchCodeGenTab('python')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white" id="cg_tab_python">Python (FastAPI)</button>
                </div>

                <!-- Code Container -->
                <div class="p-5 overflow-y-auto flex-1 bg-slate-950 font-mono text-xs text-slate-200">
                    <div class="flex justify-between items-center mb-2 pb-2 border-b border-slate-800 text-[11px] text-slate-400">
                        <span id="cg_target_file_path" class="text-indigo-400">Target File: app/api/external-news-post/route.ts</span>
                        <button type="button" onclick="copyGeneratedCode()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-sans px-3 py-1 rounded text-xs flex items-center gap-1 transition cursor-pointer">
                            <i class="fas fa-copy"></i> Copy Code
                        </button>
                    </div>
                    <pre><code id="cg_code_content" class="text-emerald-400 leading-relaxed block overflow-x-auto whitespace-pre"></code></pre>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_category'))
        {{-- 📂 ক্যাটাগরি ম্যাপিং (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-base">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            📂 ক্যাটাগরি ম্যাপিং (Category Mapping)
                        </h2>
                        <p class="text-xs text-gray-500">AI এর ডিটেক্ট করা বিষয়ের সাথে আপনার ওয়েবসাইটের ক্যাটাগরি মিলান</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Category Map</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
                    <p class="text-xs text-gray-500">
                        AI দ্বারা ডিটেক্ট করা বিষয়গুলো আপনার ওয়েবসাইটের কোন ক্যাটাগরিতে পোস্ট হবে তা নির্ধারণ করুন।
                    </p>
                    <button type="button" id="refresh-cat-btn" onclick="fetchWPCategories(true)" class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-100 font-bold flex items-center gap-1 transition cursor-pointer">
                        🔄 Refresh Categories
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                    @php
                        $aiCategories = [
                            'Politics', 'International', 'Sports', 'Cricket', 'Football', 
                            'Entertainment', 'Technology', 'Economy', 'Business', 
                            'Bangladesh', 'National', 'Crime', 'Education', 'Health', 
                            'Lifestyle', 'Religion', 'Travel', 'Jobs', 'Opinion', 
                            'Feature', 'Others', 'Science', 'Environment', 'Weather', 
                            'Agriculture', 'Startup', 'Finance', 'Stock Market', 'Banking', 
                            'Law & Justice', 'Defense', 'Cyber Security', 'AI & Robotics', 
                            'Gadgets', 'Mobile', 'Automobile', 'Real Estate', 'Energy', 
                            'Tourism', 'Food & Recipe', 'Fashion', 'Art & Culture', 
                            'History', 'Women', 'Youth', 'Editorial', 'Breaking News', 
                            'Exclusive', 'Investigation', 'Human Rights', 'Social Issues', 
                            'Public Health', 'Mental Health', 'Child Care', 'Parenting', 
                            'Senior Citizens', 'Immigration', 'Expat Life', 'Remittance', 
                            'Development', 'Infrastructure', 'Rural Life', 'Urban Life', 
                            'Local News', 'City News', 'Media & Press', 'Telecom', 
                            'Internet', 'E-Commerce', 'Digital Lifestyle', 'Gaming', 
                            'E-Sports', 'Movies', 'Music', 'TV & OTT', 'Books & Literature' 
                        ];
                        $savedMapping = $settings->category_mapping ?? [];
                    @endphp

                    @foreach($aiCategories as $cat)
                        <div class="flex items-center gap-3 p-2 hover:bg-white rounded transition border border-transparent hover:border-gray-200">
                            <span class="w-1/3 text-xs font-bold text-gray-700">{{ $cat }}</span>
                            <div class="w-2/3 relative">
                                <select name="category_mapping[{{ $cat }}]" class="wp-cat-selector w-full border-gray-300 rounded-lg text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Category</option>
                                </select>
                                <input type="hidden" class="saved-val" value="{{ $savedMapping[$cat] ?? '' }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- 📱 টেলিগ্রাম নোটিফিকেশন (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-base">
                        <i class="fab fa-telegram-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            ✈️ টেলিগ্রাম নোটিফিকেশন
                        </h2>
                        <p class="text-xs text-gray-500">টেলিগ্রাম চ্যানেল আইডি ও অ্যালার্ট সেটিংস</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-sky-100 text-sky-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Telegram</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">চ্যানেল আইডি (Channel ID)</label>
                    <input type="text" name="telegram_channel_id" value="{{ old('telegram_channel_id', $settings->telegram_channel_id ?? '') }}" placeholder="-100xxxxxxxxxx" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    <p class="text-[11px] text-gray-500 mt-1">আপনার বটকে চ্যানেলে এডমিন করুন এবং চ্যানেল আইডি দিন।</p>
                </div>
            </div>
        </div>

        <!-- Sticky or Bottom Save Bar -->
        <div class="flex justify-end pt-4 sticky bottom-4 z-20">
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-8 py-3 rounded-xl font-bold text-base hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2 cursor-pointer shadow-lg">
                <i class="fas fa-save"></i> <span>💾 সেটিংস সেভ করুন</span>
            </button>
        </div>
    </form>
</div>

<script>
    // ==========================================================
    // Accordion Expand / Collapse Handlers
    // ==========================================================
    function toggleSettingsAccordion(headerEl) {
        const card = headerEl.closest('.settings-accordion-card');
        if (!card) return;
        const body = card.querySelector('.settings-accordion-body');
        const arrow = headerEl.querySelector('.accordion-arrow');
        
        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-180');
        } else {
            body.classList.add('hidden');
            if (arrow) arrow.classList.remove('rotate-180');
        }
    }

    function expandAllSettings() {
        document.querySelectorAll('.settings-accordion-card').forEach(card => {
            const body = card.querySelector('.settings-accordion-body');
            const arrow = card.querySelector('.accordion-arrow');
            if (body) body.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-180');
        });
    }

    function collapseAllSettings() {
        document.querySelectorAll('.settings-accordion-card').forEach(card => {
            const body = card.querySelector('.settings-accordion-body');
            const arrow = card.querySelector('.accordion-arrow');
            if (body) body.classList.add('hidden');
            if (arrow) arrow.classList.remove('rotate-180');
        });
    }

    // Toggle Custom API Section
    function toggleCustomApi() {
        const section = document.getElementById('custom-api-section');
        if (section) {
            section.style.display = (section.style.display === 'none') ? 'grid' : 'none';
        }
    }

    // 🔥 Apply JSON from API Guide page (sessionStorage)
    document.addEventListener('DOMContentLoaded', function () {
        const pending = sessionStorage.getItem('pendingApiMapping');
        if (pending) {
            const textarea = document.querySelector('textarea[name="custom_api_mapping"]');
            if (textarea) {
                textarea.value = pending;
                sessionStorage.removeItem('pendingApiMapping');
                const card = textarea.closest('.settings-accordion-card');
                if (card) {
                    const body = card.querySelector('.settings-accordion-body');
                    const arrow = card.querySelector('.accordion-arrow');
                    if (body) body.classList.remove('hidden');
                    if (arrow) arrow.classList.add('rotate-180');
                }
                setTimeout(() => {
                    textarea?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }
    });

    // ১. ক্যাটাগরি ফেচ করা (Cache logic সহ)
    function fetchWPCategories(forceRefresh = false) {
        const btn = document.getElementById('refresh-cat-btn');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '⏳ Loading...';
        btn.disabled = true;

        let url = "{{ route('settings.fetch-categories') }}";
        if (forceRefresh) {
            url += "?refresh=1";
        }
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    alert(data.error);
                } else {
                    populateDropdowns(data);
                    if(forceRefresh) alert('✅ ক্যাটাগরি লিস্ট আপডেট করা হয়েছে!');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Connection Failed! Please check Settings.');
            })
            .finally(() => {
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
    }

    // ২. ড্রপডাউন পপুলেট করা
    function populateDropdowns(categories) {
        const selectors = document.querySelectorAll('.wp-cat-selector');
        selectors.forEach(select => {
            const savedVal = select.nextElementSibling.value;
            let options = '<option value="">Select Category</option>';
            
            if (Array.isArray(categories)) {
                categories.forEach(cat => {
                    const isSelected = (cat.id == savedVal) ? 'selected' : '';
                    options += `<option value="${cat.id}" ${isSelected}>${cat.name} (ID: ${cat.id})</option>`;
                });
            }
            select.innerHTML = options;
        });
    }

    // ৩. কানেকশন টেস্ট ফাংশনগুলো
    function genericTest(type, data, statusId, btn) {
        const statusMsg = document.getElementById(statusId);
        const originalBtnText = btn.innerHTML;

        btn.innerHTML = "Checking...";
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Connecting...";

        fetch(`/settings/test/${type}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            statusMsg.className = data.success ? "text-xs font-bold mt-2 text-green-600 whitespace-pre-line" : "text-xs font-bold mt-2 text-red-600 whitespace-pre-line";
        })
        .finally(() => {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        });
    }

    // বাটন ক্লিক ইভেন্টগুলো
    function testWordPress() {
        genericTest('wordpress', {
            wp_url: document.getElementById('wp_url').value,
            wp_username: document.getElementById('wp_username').value,
            wp_app_password: document.getElementById('wp_app_password').value
        }, 'wp_status_msg', document.activeElement);
    }

    function testFacebook() {
        genericTest('facebook', {
            fb_page_id: document.getElementById('fb_page_id').value,
            fb_access_token: document.getElementById('fb_access_token').value
        }, 'fb_status_msg', document.activeElement);
    }

    function testTelegram() {
        genericTest('telegram', {
            telegram_bot_token: document.getElementById('telegram_bot_token').value,
            telegram_channel_id: document.getElementById('telegram_channel_id').value
        }, 'tg_status_msg', document.activeElement);
    }

    function testPhotoRoom() {
        const keyInput = document.getElementById('photoroom_api_key');
        const statusMsg = document.getElementById('photoroom_status_msg');
        const btn = event.currentTarget || document.activeElement;
        const originalText = btn.innerHTML;

        if (!keyInput.value.trim()) {
            statusMsg.innerText = "❌ অনুগ্রহ করে PhotoRoom API Key দিন।";
            statusMsg.className = "text-xs font-bold mt-2 text-red-600";
            return;
        }

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ PhotoRoom সার্ভারে হ্যান্ডশেক টেস্ট করা হচ্ছে...";
        statusMsg.className = "text-xs font-bold mt-2 text-blue-600";

        fetch(`/settings/test/photoroom`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ photoroom_api_key: keyInput.value.trim() })
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            statusMsg.className = data.success ? "text-xs font-bold mt-2 text-green-600" : "text-xs font-bold mt-2 text-red-600";
        })
        .catch(err => {
            statusMsg.innerText = "❌ নেটওয়ার্ক এরর: " + err.message;
            statusMsg.className = "text-xs font-bold mt-2 text-red-600";
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function testDecodoProxy() {
        const tokenInput = document.getElementById('smartproxy_api_token');
        const hostInput = document.getElementById('proxy_host');
        const portInput = document.getElementById('proxy_port');
        const userInput = document.getElementById('proxy_username');
        const passInput = document.getElementById('proxy_password');
        const statusMsg = document.getElementById('decodo_proxy_status_msg');
        const btn = event.currentTarget || document.activeElement;
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing Proxy...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Decodo Universal API ও প্রক্সি সার্ভারে লাইভ হ্যান্ডশেক টেস্ট করা হচ্ছে...";
        statusMsg.className = "text-xs font-bold mb-4 text-blue-600 bg-blue-50 p-3 rounded-lg border border-blue-200 block";

        const payload = {
            smartproxy_api_token: tokenInput ? tokenInput.value.trim() : '',
            proxy_host: hostInput ? hostInput.value.trim() : '',
            proxy_port: portInput ? portInput.value.trim() : '',
            proxy_username: userInput ? userInput.value.trim() : '',
            proxy_password: passInput ? passInput.value.trim() : ''
        };

        fetch(`/settings/test/decodo-proxy`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            if (data.success) {
                statusMsg.className = "text-xs font-bold mb-4 text-green-700 bg-green-50 p-3 rounded-lg border border-green-300 block whitespace-pre-line";
            } else {
                statusMsg.className = "text-xs font-bold mb-4 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
            }
        })
        .catch(err => {
            statusMsg.innerText = "❌ নেটওয়ার্ক এরর: " + err.message;
            statusMsg.className = "text-xs font-bold mb-4 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ==========================================================
    // 1. Custom API Visual Field Mapper Synchronization
    // ==========================================================
    function toggleCustomApiVisual() {
        const sec = document.getElementById('custom-api-visual-section');
        const chev = document.getElementById('mapper_chevron');
        if (sec.classList.contains('hidden')) {
            sec.classList.remove('hidden');
            if (chev) chev.classList.add('rotate-180');
        } else {
            sec.classList.add('hidden');
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function toggleRawJson() {
        const t = document.getElementById('custom_api_mapping');
        if (t.classList.contains('hidden')) {
            t.classList.remove('hidden');
        } else {
            t.classList.add('hidden');
        }
    }

    function syncVisualToMappingJson() {
        const authType = document.getElementById('v_auth_type').value;
        const authHeaderName = document.getElementById('v_auth_header_name').value.trim();
        const imageFormat = document.getElementById('v_image_format').value;
        const categoryType = document.getElementById('v_category_type').value;

        const authHeaderWrapper = document.getElementById('v_auth_header_wrapper');
        if (authType === 'custom_header') {
            authHeaderWrapper.style.display = 'block';
        } else {
            authHeaderWrapper.style.display = 'none';
        }

        const fields = {};
        const titleField = document.getElementById('v_field_title').value.trim();
        if (titleField) fields.title = titleField;

        const contentField = document.getElementById('v_field_content').value.trim();
        if (contentField) fields.content = contentField;

        const imageField = document.getElementById('v_field_image').value.trim();
        if (imageField) fields.image = imageField;

        const categoryField = document.getElementById('v_field_category').value.trim();
        if (categoryField) fields.category = categoryField;

        const tagsField = document.getElementById('v_field_tags').value.trim();
        if (tagsField) fields.tags = tagsField;

        const slugField = document.getElementById('v_field_slug').value.trim();
        if (slugField) fields.slug = slugField;

        const responseIdKey = document.getElementById('v_field_response_id_key').value.trim();
        const responseUrlKey = document.getElementById('v_field_response_url_key').value.trim();

        // Extra static key-values
        const extraData = {};
        document.querySelectorAll('.extra-field-row').forEach(row => {
            const k = row.querySelector('.extra-key').value.trim();
            const v = row.querySelector('.extra-val').value.trim();
            if (k) extraData[k] = v;
        });

        const mappingObj = {
            auth_type: authType,
            image_format: imageFormat,
            category_type: categoryType
        };

        if (authType === 'custom_header' && authHeaderName) {
            mappingObj.auth_header_name = authHeaderName;
        }

        if (Object.keys(fields).length > 0) {
            mappingObj.fields = fields;
        }

        if (Object.keys(extraData).length > 0) {
            mappingObj.extra_data = extraData;
        }

        if (responseIdKey) mappingObj.response_id_key = responseIdKey;
        if (responseUrlKey) mappingObj.response_url_key = responseUrlKey;

        document.getElementById('custom_api_mapping').value = JSON.stringify(mappingObj, null, 2);
    }

    function syncMappingJsonToVisual() {
        const rawJson = document.getElementById('custom_api_mapping').value.trim();
        if (!rawJson) return;

        try {
            const obj = JSON.parse(rawJson);
            if (obj.auth_type) document.getElementById('v_auth_type').value = obj.auth_type;
            if (obj.auth_header_name) document.getElementById('v_auth_header_name').value = obj.auth_header_name;
            if (obj.image_format) document.getElementById('v_image_format').value = obj.image_format;
            if (obj.category_type) document.getElementById('v_category_type').value = obj.category_type;

            if (obj.auth_type === 'custom_header') {
                document.getElementById('v_auth_header_wrapper').style.display = 'block';
            }

            if (obj.fields) {
                if (obj.fields.title) document.getElementById('v_field_title').value = obj.fields.title;
                if (obj.fields.content) document.getElementById('v_field_content').value = obj.fields.content;
                if (obj.fields.image) document.getElementById('v_field_image').value = obj.fields.image;
                if (obj.fields.category) document.getElementById('v_field_category').value = obj.fields.category;
                if (obj.fields.tags) document.getElementById('v_field_tags').value = obj.fields.tags;
                if (obj.fields.slug) document.getElementById('v_field_slug').value = obj.fields.slug;
            }

            if (obj.response_id_key) document.getElementById('v_field_response_id_key').value = obj.response_id_key;
            if (obj.response_url_key) document.getElementById('v_field_response_url_key').value = obj.response_url_key;

            if (obj.extra_data) {
                const container = document.getElementById('extra_fields_container');
                container.innerHTML = '';
                for (const [k, v] of Object.entries(obj.extra_data)) {
                    addExtraFieldRow(k, v);
                }
            }
        } catch (e) {
            // raw json might be custom or invalid, ignore
        }
    }

    function addExtraFieldRow(key = '', val = '') {
        const container = document.getElementById('extra_fields_container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 extra-field-row';
        div.innerHTML = `
            <input type="text" placeholder="Key (e.g. author_id)" value="${key}" oninput="syncVisualToMappingJson()" class="extra-key w-1/2 border-slate-300 rounded p-1.5 font-mono text-xs">
            <input type="text" placeholder="Value (e.g. 1)" value="${val}" oninput="syncVisualToMappingJson()" class="extra-val w-1/2 border-slate-300 rounded p-1.5 font-mono text-xs">
            <button type="button" onclick="this.parentElement.remove(); syncVisualToMappingJson();" class="text-red-500 hover:text-red-700 px-2 py-1 text-sm font-bold">&times;</button>
        `;
        container.appendChild(div);
    }

    function generateRandomToken() {
        const randStr = 'sec_' + Math.random().toString(36).substring(2, 10) + '_' + Math.random().toString(36).substring(2, 10);
        document.getElementById('laravel_api_token').value = randStr;
    }

    // ==========================================================
    // 2. Custom API Live Test
    // ==========================================================
    function testCustomApiConnection() {
        const btn = document.getElementById('btn_test_custom_api');
        const box = document.getElementById('custom_api_status_box');
        const originalText = btn.innerHTML;

        const siteUrl = document.getElementById('laravel_site_url').value.trim();
        const customApiUrl = document.getElementById('custom_api_url').value.trim();
        const token = document.getElementById('laravel_api_token').value.trim();
        const mapping = document.getElementById('custom_api_mapping').value.trim();

        if (!siteUrl && !customApiUrl) {
            box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 block';
            box.innerText = 'দয়া করে Website Base URL অথবা Custom News Post Endpoint URL দিন।';
            return;
        }

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        btn.disabled = true;

        box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 block';
        box.innerText = '⏳ হ্যান্ডশেক টেস্ট রিকোয়েস্ট পাঠানো হচ্ছে...';

        fetch("{{ route('settings.test-custom-api') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                laravel_site_url: siteUrl,
                custom_api_url: customApiUrl,
                laravel_api_token: token,
                custom_api_mapping: mapping
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 block whitespace-pre-line';
                box.innerText = data.message;
            } else {
                box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-red-50 text-red-800 border border-red-300 block whitespace-pre-line';
                box.innerText = data.message;
            }
        })
        .catch(err => {
            box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-red-50 text-red-800 border border-red-300 block';
            box.innerText = '❌ নেটওয়ার্ক এরর: ' + err.message;
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ==========================================================
    // 3. Assistant Help Modal Controls
    // ==========================================================
    function openAssistantHelpModal() {
        document.getElementById('assistantHelpModal').classList.remove('hidden');
    }

    function closeAssistantHelpModal() {
        document.getElementById('assistantHelpModal').classList.add('hidden');
    }

    function switchHelpTab(tabKey) {
        document.querySelectorAll('.help-tab-btn').forEach(b => {
            b.className = 'help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2';
        });
        document.getElementById('htab_' + tabKey).className = 'help-tab-btn px-4 py-3 border-b-2 border-indigo-500 text-indigo-400 flex items-center gap-2';

        document.getElementById('help_content_walkthrough').classList.add('hidden');
        document.getElementById('help_content_faq').classList.add('hidden');
        document.getElementById('help_content_diagnostics').classList.add('hidden');

        document.getElementById('help_content_' + tabKey).classList.remove('hidden');
    }

    // ==========================================================
    // 4. Code Generator Modal & Dynamic Snippets
    // ==========================================================
    function openCodeGeneratorModal() {
        document.getElementById('codeGeneratorModal').classList.remove('hidden');
        switchCodeGenTab('next_app');
    }

    function closeCodeGeneratorModal() {
        document.getElementById('codeGeneratorModal').classList.add('hidden');
    }

    function switchCodeGenTab(langKey) {
        document.querySelectorAll('.cg-tab-btn').forEach(b => {
            b.className = 'cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white';
        });
        document.getElementById('cg_tab_' + langKey).className = 'cg-tab-btn px-3 py-2 rounded-lg bg-indigo-600 text-white';

        const token = document.getElementById('laravel_api_token').value.trim() || 'YOUR_SECRET_TOKEN_HERE';
        const codeBox = document.getElementById('cg_code_content');
        const pathBox = document.getElementById('cg_target_file_path');

        if (langKey === 'next_app') {
            pathBox.innerText = 'Target File: app/api/external-news-post/route.ts';
            codeBox.innerText = `import { NextRequest, NextResponse } from 'next/server';

export async function POST(req: NextRequest) {
  try {
    const authHeader = req.headers.get('authorization');
    const expectedToken = "Bearer ${token}";

    if (authHeader !== expectedToken) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const data = await req.json();
    console.log("Received News Payload:", data);

    // Save article to your Database (Prisma, Drizzle, MongoDB, etc.)
    // const post = await db.article.create({ data: { title: data.title, content: data.content, ... } });

    return NextResponse.json({
      success: true,
      message: 'Article published successfully',
      post_id: 101, // Return your generated post ID
      url: \`/news/article-101\`
    }, { status: 200 });

  } catch (error: any) {
    return NextResponse.json({ success: false, message: error.message }, { status: 500 });
  }
}`;
        } else if (langKey === 'next_pages') {
            pathBox.innerText = 'Target File: pages/api/external-news-post.ts';
            codeBox.innerText = `import type { NextApiRequest, NextApiResponse } from 'next';

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'POST') {
    return res.status(405).json({ message: 'Method Not Allowed' });
  }

  const authHeader = req.headers.authorization;
  if (authHeader !== "Bearer ${token}") {
    return res.status(401).json({ success: false, message: 'Unauthorized' });
  }

  const { title, content, image, category_id, tags } = req.body;
  // TODO: Insert into database

  return res.status(200).json({
    success: true,
    post_id: 101,
    url: '/news/101'
  });
}`;
        } else if (langKey === 'express') {
            pathBox.innerText = 'Target File: routes/newsReceiver.js';
            codeBox.innerText = `const express = require('express');
const router = express.Router();

router.post('/api/external-news-post', (req, res) => {
  const authHeader = req.headers.authorization;
  if (authHeader !== "Bearer ${token}") {
    return res.status(401).json({ success: false, message: 'Unauthorized' });
  }

  const { title, content, image, category, tags, slug } = req.body;
  console.log("New Article:", title);

  // TODO: Save to your DB (e.g. Mongoose, Sequelize, Postgres)

  return res.json({
    success: true,
    post_id: 101,
    url: '/news/' + (slug || '101')
  });
});

module.exports = router;`;
        } else if (langKey === 'laravel') {
            pathBox.innerText = 'Target File: routes/api.php';
            codeBox.innerText = `use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/external-news-post', function (Request $request) {
    $expectedToken = "Bearer ${token}";
    if ($request->header('Authorization') !== $expectedToken) {
        return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
    }

    $validated = $request->validate([
        'title'   => 'required|string',
        'content' => 'required|string',
        'image'   => 'nullable|string',
    ]);

    // \App\Models\Post::create([...]);

    return response()->json([
        'success' => true,
        'post_id' => 101,
        'url'     => url('/news/101')
    ], 200);
});`;
        } else if (langKey === 'php') {
            pathBox.innerText = 'Target File: public/news-receiver.php';
            codeBox.innerText = '<\x3Fphp\n' +
`header('Content-Type: application/json');

$headers = getallheaders();
$auth = $headers['Authorization'] ?? ($headers['authorization'] ?? '');

if ($auth !== "Bearer ${token}") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['title'])) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Invalid Data']);
    exit;
}

// Database Connection
// $pdo = new PDO("mysql:host=localhost;dbname=mydb", "user", "pass");
// $stmt = $pdo->prepare("INSERT INTO posts (title, content, image) VALUES (?, ?, ?)");
// $stmt->execute([$input['title'], $input['content'], $input['image'] ?? '']);

echo json_encode([
    'success' => true,
    'post_id' => 101,
    'message' => 'Created successfully'
]);`;
        } else if (langKey === 'python') {
            pathBox.innerText = 'Target File: main.py (FastAPI)';
            codeBox.innerText = `from fastapi import FastAPI, Header, HTTPException, status
from pydantic import BaseModel
from typing import Optional

app = FastAPI()

class NewsPayload(BaseModel):
    title: str
    content: str
    image: Optional[str] = None
    category: Optional[str] = None

` + '@' + `app.post("/api/external-news-post")
async def receive_news(data: NewsPayload, authorization: Optional[str] = Header(None)):
    if authorization != "Bearer ${token}":
        raise HTTPException(status_code=401, detail="Unauthorized")
    
    print(f"Received article: {data.title}")
    # Save to database
    return {"success": True, "post_id": 101, "message": "Saved"}
`;
        }
    }

    function copyGeneratedCode() {
        const code = document.getElementById('cg_code_content').innerText;
        navigator.clipboard.writeText(code).then(() => {
            alert('✅ কোড ক্লিপবোর্ডে কপি করা হয়েছে!');
        });
    }

    // Auto load categories & sync visual mapper on load
    document.addEventListener('DOMContentLoaded', function () {
        fetchWPCategories();
        syncMappingJsonToVisual();
    });
</script>
@endsection
