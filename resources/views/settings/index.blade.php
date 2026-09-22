@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2.5">
                ⚙️ Profile & Settings
            </h1>
            <p class="text-gray-500 mt-1 text-sm">News card customization, AI integrations, proxies, and automation settings</p>
        </div>
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl shadow-lg text-center">
            <p class="text-xs opacity-80 uppercase tracking-wider">Current Balance</p>
            <p class="text-2xl font-bold">{{ auth()->user()->credits }} <span class="text-sm font-normal">Credits</span></p>
        </div>
    </div>

    <!-- Global Accordion Expand/Collapse Controls & Diagnostics -->
    <div class="flex flex-wrap justify-between items-center bg-white dark:bg-slate-900 p-4 rounded-xl border border-gray-200 dark:border-slate-800 shadow-sm mb-6 gap-3">
        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-slate-400 font-semibold">
            <i class="fas fa-layer-group text-indigo-600 text-sm"></i>
            <span>Settings sections are collapsed by default.</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="runDiagnosticsModal()" class="px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                <i class="fa-solid fa-heart-pulse animate-pulse"></i> 🩺 1-Click Health Check
            </button>
            <button type="button" onclick="expandAllSettings()" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                <i class="fas fa-expand-alt"></i> Expand All
            </button>
            <button type="button" onclick="collapseAllSettings()" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg border border-gray-300 flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                <i class="fas fa-compress-alt"></i> Collapse All
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
    
    {{-- 1. Profile Update Section (Collapsible) --}}
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
                            👤 My Profile & Password
                        </h2>
                        <p class="text-xs text-gray-500">Change your name, email, and password</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-indigo-600 font-semibold hidden sm:inline">Click to expand</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Your Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email Address (Login Username)</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep unchanged..." 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Re-enter your new password" 
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                    </div>
                </div>
                <div class="mt-5 text-right">
                    <button type="submit" class="bg-gray-800 text-white px-6 py-2.5 rounded-lg font-bold hover:bg-gray-900 transition shadow cursor-pointer text-sm">
                        Update Profile
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- 2. Main Settings Form Start --}}
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
                            🌐 Scraper & Proxy Settings (Decodo Universal API)
                        </h2>
                        <p class="text-xs text-gray-500">Decodo Universal API, Puppeteer Proxy, and Auto Clean</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Decodo / Proxy</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
                    <p class="text-xs text-gray-600 font-medium">Configure custom proxies and Decodo Universal Scraping API for scraping news. Leave empty to use system defaults.</p>
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
                            <p class="text-xs mb-3 text-gray-500">Powerful API token for scraping Cloudflare-protected news portals and protected sources.</p>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 mb-1">Decodo API Token (Basic Auth Token)</label>
                                <input type="password" id="smartproxy_api_token" name="smartproxy_api_token" value="{{ old('smartproxy_api_token', $settings->smartproxy_api_token ?? '') }}" placeholder="Basic VTAwM..." class="w-full border-gray-300 rounded shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs">
                                <p class="text-[10px] text-gray-400 mt-1">Enter <code>Basic Auth Token</code> obtained from your Decodo dashboard.</p>
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
                        <p class="text-xs text-gray-500">Unpublished news older than this will be automatically deleted (Default: 7 days).</p>
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
                            💰 ROI Calculator Config (Super Admin)
                        </h2>
                        <p class="text-xs text-gray-500">Configure estimated cost savings metrics</p>
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
                        <label class="block text-xs font-bold text-gray-700 mb-1">Staff Hourly Rate (BDT/Hour)</label>
                        <input type="number" name="roi_hourly_rate" value="{{ $roiConfig['hourly_rate'] ?? 100 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Time per News (Minutes)</label>
                        <input type="number" name="roi_news_minutes" value="{{ $roiConfig['news_minutes'] ?? 20 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Time per Card (Minutes)</label>
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
                            🎨 Studio Templates & Media Manager Shortcut
                        </h2>
                        <p class="text-xs text-gray-500">Manage template layout creation, frames, and font uploads</p>
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
                            <p class="text-xs text-gray-500 mt-1 mb-4">Add new templates from dashboard — configure frame URL, positions all in one place.</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.templates.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 rounded-lg transition shadow-sm text-center">🎨 Templates</a>
                            <a href="{{ route('admin.templates.create') }}" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-bold text-xs py-2 rounded-lg transition text-center">+ Add New</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">📁 Media & Assets Manager</h3>
                            <p class="text-xs text-gray-500 mt-1 mb-4">Upload, rename, and copy URLs for template frame PNGs and custom fonts (.ttf, .woff).</p>
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
        {{-- 3. Branding Section (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-base">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🎨 Branding & News Card Styles
                        </h2>
                        <p class="text-xs text-gray-500">Brand Name, default theme colors, and logo</p>
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
                        <label class="block text-xs font-bold text-gray-700 mb-1">Brand Name (e.g. Dhaka Post)</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name ?? 'My News') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Default Color Theme</label>
                        <select name="default_theme_color" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs font-semibold">
                            <option value="red" {{ ($settings->default_theme_color ?? '') == 'red' ? 'selected' : '' }}>Red (Breaking)</option>
                            <option value="blue" {{ ($settings->default_theme_color ?? '') == 'blue' ? 'selected' : '' }}>Blue (Standard)</option>
                            <option value="green" {{ ($settings->default_theme_color ?? '') == 'green' ? 'selected' : '' }}>Green (Sports/Islamic)</option>
                            <option value="purple" {{ ($settings->default_theme_color ?? '') == 'purple' ? 'selected' : '' }}>Purple (Lifestyle)</option>
                            <option value="black" {{ ($settings->default_theme_color ?? '') == 'black' ? 'selected' : '' }}>Black (Dark)</option>
                        </select>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Logo URL (Optional)</label>
                        <input type="url" name="logo_url" value="{{ old('logo_url', $settings->logo_url ?? '') }}" placeholder="https://example.com/logo.png" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                        <p class="text-[11px] text-gray-500 mt-1">You can also upload your logo directly from the Studio.</p>
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
                            🌍 Target Language (Default News Language)
                        </h2>
                        <p class="text-xs text-gray-500">Default language for news processing and scraping</p>
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
                        <option value="" {{ empty($settings->target_language) ? 'selected' : '' }}>Website Default (Based on website settings)</option>
                        <option value="bn" {{ ($settings->target_language ?? '') == 'bn' ? 'selected' : '' }}>Always Bengali</option>
                        <option value="en" {{ ($settings->target_language ?? '') == 'en' ? 'selected' : '' }}>Always English</option>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-2">When you scrape a news item, it will be processed in this language (website-specific language settings will take precedence).</p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai') || auth()->user()->hasPermission('can_settings_ai_prompt'))
        {{-- ✍️ CUSTOM NEWS REWRITE PROMPT (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-teal-50/40 hover:bg-teal-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-base">
                        <i class="fas fa-feather-alt"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            ✍️ Custom News Rewrite Prompt (AI System Prompt)
                        </h2>
                        <p class="text-xs text-gray-500">Configure custom sub-editor writing rules, tone, and formatting rules</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-teal-100 text-teal-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">AI Prompt</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-4 pb-3 border-b border-gray-200">
                    <div>
                        <p class="text-xs font-semibold text-gray-700">Customize the system prompt instructions given to the AI when rewriting raw news reports.</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Leave blank to use the built-in Bangladeshi/International Senior Sub-Editor prompt rules.</p>
                    </div>
                    <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                        <button type="button" onclick="insertDefaultPrompt('bn')" class="text-[11px] bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold px-3 py-1.5 rounded-lg border border-teal-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-file-alt"></i> <span>Insert Default (BN)</span>
                        </button>
                        <button type="button" onclick="insertDefaultPrompt('en')" class="text-[11px] bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold px-3 py-1.5 rounded-lg border border-teal-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-file-alt"></i> <span>Insert Default (EN)</span>
                        </button>
                        <button type="button" onclick="resetDefaultPrompt()" class="text-[11px] bg-red-50 hover:bg-red-100 text-red-600 font-bold px-3 py-1.5 rounded-lg border border-red-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm" title="Clear to use system default">
                            <i class="fas fa-undo"></i> <span>Reset to Default</span>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-2">Custom System Prompt</label>
                    <textarea id="custom_rewrite_prompt" name="custom_rewrite_prompt" rows="12" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 text-xs font-mono bg-white p-3.5 leading-relaxed placeholder-gray-400" placeholder="Leave blank to use the built-in Bangladeshi Senior Sub-Editor prompt rules...">{{ old('custom_rewrite_prompt', $settings->custom_rewrite_prompt ?? '') }}</textarea>
                </div>

                <div class="text-[11px] text-teal-900 flex items-start gap-2 bg-teal-50/70 p-3 rounded-lg border border-teal-200">
                    <i class="fas fa-info-circle text-teal-600 mt-0.5 text-sm"></i>
                    <div>
                        <strong>Automatic JSON Enforcement:</strong> The strict output formatting rules (<code>title</code>, <code>content</code>, <code>meta_description</code>, <code>focus_keyword</code>, <code>tags</code>) are automatically appended by the system, ensuring valid news data across all AI models without syntax breakage.
                    </div>
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
                            🤖 AI Optimization Settings (DeepSeek, Gemini, OpenAI)
                        </h2>
                        <p class="text-xs text-gray-500">Select Primary AI and manage AI API Keys & Models</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-indigo-100 text-indigo-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">AI Engines</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <p class="text-xs text-indigo-700 mb-5 font-medium">Configure API Key and Model for each provider (leaves default .env fallback if empty).</p>

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
                    <p class="text-[11px] text-gray-500 mt-2">The system will prioritize the selected primary AI when generating news. If it fails, fallback AI models will be tried automatically.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Gemini -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fab fa-google text-blue-500"></i> Gemini (Google AI)
                            </h3>
                            <button type="button" onclick="testAiProvider('gemini')" class="text-[11px] bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold px-3 py-1 rounded border border-blue-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="gemini_api_key" name="gemini_api_key" value="{{ old('gemini_api_key', $settings->gemini_api_key ?? '') }}" placeholder="AIzaSy... (Leave empty to use .env value)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="gemini_model" name="gemini_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (gemini-1.5-flash)</option>
                                    <option value="gemini-2.5-flash" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-flash' ? 'selected' : '' }}>Gemini 2.5 Flash (Fast + Balance)</option>
                                    <option value="gemini-2.5-pro" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-pro' ? 'selected' : '' }}>Gemini 2.5 Pro (Complex Reasoning)</option>
                                    <option value="gemini-2.5-flash-lite" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-flash-lite' ? 'selected' : '' }}>Gemini 2.5 Flash-Lite (High Volume)</option>
                                    <option value="gemini-3.1-pro-preview" {{ ($settings->gemini_model ?? '') == 'gemini-3.1-pro-preview' ? 'selected' : '' }}>Gemini 3.1 Pro Preview (Latest Reasoning)</option>
                                    <option value="gemini-3.1-flash-lite-preview" {{ ($settings->gemini_model ?? '') == 'gemini-3.1-flash-lite-preview' ? 'selected' : '' }}>Gemini 3.1 Flash Lite Preview (Efficient)</option>
                                </select>
                            </div>
                        </div>
                        <div id="gemini_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- DeepSeek -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-brain text-indigo-600"></i> DeepSeek AI
                            </h3>
                            <button type="button" onclick="testAiProvider('deepseek')" class="text-[11px] bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1 rounded border border-indigo-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="deepseek_api_key" name="deepseek_api_key" value="{{ old('deepseek_api_key', $settings->deepseek_api_key ?? '') }}" placeholder="sk-... (Leave empty to use .env value)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="deepseek_model" name="deepseek_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (deepseek-chat)</option>
                                    <option value="deepseek-chat" {{ ($settings->deepseek_model ?? '') == 'deepseek-chat' ? 'selected' : '' }}>DeepSeek V3 (deepseek-chat)</option>
                                    <option value="deepseek-reasoner" {{ ($settings->deepseek_model ?? '') == 'deepseek-reasoner' ? 'selected' : '' }}>DeepSeek R1 (deepseek-reasoner)</option>
                                </select>
                            </div>
                        </div>
                        <div id="deepseek_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- Qwen (DashScope) -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-microchip text-orange-500"></i> Qwen (DashScope API)
                            </h3>
                            <button type="button" onclick="testAiProvider('qwen')" class="text-[11px] bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold px-3 py-1 rounded border border-orange-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="qwen_api_key" name="qwen_api_key" value="{{ old('qwen_api_key', $settings->qwen_api_key ?? '') }}" placeholder="sk-... (DashScope API Key)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="qwen_model" name="qwen_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (qwen-turbo)</option>
                                    <option value="qwen-turbo" {{ ($settings->qwen_model ?? '') == 'qwen-turbo' ? 'selected' : '' }}>Qwen Turbo (Fast & Cheap)</option>
                                    <option value="qwen-plus" {{ ($settings->qwen_model ?? '') == 'qwen-plus' ? 'selected' : '' }}>Qwen Plus (Balanced)</option>
                                    <option value="qwen-max" {{ ($settings->qwen_model ?? '') == 'qwen-max' ? 'selected' : '' }}>Qwen Max (Best Quality)</option>
                                </select>
                            </div>
                        </div>
                        <div id="qwen_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- Groq -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-bolt text-yellow-500"></i> Groq (Ultra-Fast LPU)
                            </h3>
                            <button type="button" onclick="testAiProvider('groq')" class="text-[11px] bg-yellow-50 hover:bg-yellow-100 text-yellow-800 font-bold px-3 py-1 rounded border border-yellow-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="groq_api_key" name="groq_api_key" value="{{ old('groq_api_key', $settings->groq_api_key ?? '') }}" placeholder="gsk_... (Groq Console)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="groq_model" name="groq_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (llama-3.3-70b-versatile)</option>
                                    <option value="llama-3.3-70b-versatile" {{ ($settings->groq_model ?? '') == 'llama-3.3-70b-versatile' ? 'selected' : '' }}>Llama 3.3 70B (Versatile)</option>
                                    <option value="llama-3.1-8b-instant" {{ ($settings->groq_model ?? '') == 'llama-3.1-8b-instant' ? 'selected' : '' }}>Llama 3.1 8B (Instant Speed)</option>
                                    <option value="mixtral-8x7b-32768" {{ ($settings->groq_model ?? '') == 'mixtral-8x7b-32768' ? 'selected' : '' }}>Mixtral 8x7B (MoE)</option>
                                </select>
                            </div>
                        </div>
                        <div id="groq_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- Hugging Face -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-smile text-amber-500"></i> Hugging Face (Inference API)
                            </h3>
                            <button type="button" onclick="testAiProvider('huggingface')" class="text-[11px] bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold px-3 py-1 rounded border border-amber-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">User Access Token</label>
                                <input type="password" id="huggingface_api_key" name="huggingface_api_key" value="{{ old('huggingface_api_key', $settings->huggingface_api_key ?? '') }}" placeholder="hf_... (HuggingFace Access Token)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Repository ID</label>
                                <input type="text" id="huggingface_model" name="huggingface_model" value="{{ old('huggingface_model', $settings->huggingface_model ?? '') }}" placeholder="e.g. meta-llama/Llama-3.2-3B-Instruct" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                        </div>
                        <div id="huggingface_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- OpenAI -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-cube text-emerald-600"></i> OpenAI (ChatGPT)
                            </h3>
                            <button type="button" onclick="testAiProvider('openai')" class="text-[11px] bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded border border-emerald-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="openai_api_key" name="openai_api_key" value="{{ old('openai_api_key', $settings->openai_api_key ?? '') }}" placeholder="sk-proj-... (Leave empty to use .env value)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="openai_model" name="openai_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (gpt-4o-mini)</option>
                                    <option value="gpt-4o-mini" {{ ($settings->openai_model ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Fast & Cheap)</option>
                                    <option value="gpt-4o" {{ ($settings->openai_model ?? '') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o (Smartest)</option>
                                    <option value="o1-mini" {{ ($settings->openai_model ?? '') == 'o1-mini' ? 'selected' : '' }}>o1-mini (Reasoning)</option>
                                    <option value="o3-mini" {{ ($settings->openai_model ?? '') == 'o3-mini' ? 'selected' : '' }}>o3-mini (Advanced Reasoning)</option>
                                </select>
                            </div>
                        </div>
                        <div id="openai_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
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
                            📸 PhotoRoom API (AI Background Removal)
                        </h2>
                        <p class="text-xs text-gray-500">1-Click background removal when creating custom photo cards</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-purple-100 text-purple-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Background Remover</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <p class="text-xs text-purple-700 mb-4 font-medium">Provide a PhotoRoom API Key for 1-Click background removal (cutout) in custom photo cards. Settings configured by Super Admin will be automatically available to all sub-editors and staff.</p>

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
                        <i class="fas fa-info-circle text-purple-500"></i> Get your API Key from the <a href="https://www.photoroom.com/api" target="_blank" class="text-purple-600 underline font-semibold">PhotoRoom Developer Console</a>.
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_wp_laravel'))
        {{-- WordPress Connection (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-base">
                        <i class="fab fa-wordpress"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            🔗 WordPress Connection
                        </h2>
                        <p class="text-xs text-gray-500">WordPress Site URL, username, and Application Password</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">WordPress</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-xs text-gray-500">Test your WordPress connection before saving:</p>
                    <button type="button" onclick="testWordPress()" class="text-xs bg-gray-100 text-gray-700 px-3.5 py-2 rounded-lg hover:bg-gray-200 transition font-bold border border-gray-300 cursor-pointer">
                        ⚡ Test Connection
                    </button>
                </div>
                
                <p id="wp_status_msg" class="text-xs font-bold mb-4"></p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Website URL</label>
                        <input type="url" id="wp_url" name="wp_url" value="{{ old('wp_url', $settings->wp_url ?? '') }}" placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Username</label>
                        <input type="text" id="wp_username" name="wp_username" value="{{ old('wp_username', $settings->wp_username ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">App Password</label>
                        <input type="password" id="wp_app_password" name="wp_app_password" value="{{ old('wp_app_password', $settings->wp_app_password ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs" placeholder="abcd efgh ijkl mnop">
                        <p class="text-[11px] text-gray-500 mt-1">Generate from WP Admin > Users > Profile > Application Passwords.</p>
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
                        <p class="text-xs text-gray-500">REST API, Webhooks, Field Mapping, and Code Generator</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-slate-200 text-slate-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">REST API / Webhook</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-4 border-b border-gray-200 pb-3 gap-2">
                    <p class="text-xs text-gray-600 font-medium">Configure automatic news publishing connections with your custom website.</p>
                    
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
                        <div class="flex items-center gap-1.5 mb-1">
                            <label class="block text-xs font-bold text-gray-700">Website Base URL</label>
                            <button type="button" onclick="showFieldHelp('laravel_site_url')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-100 hover:bg-indigo-100 text-slate-400 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on Website Base URL">
                                <i class="fas fa-info"></i>
                            </button>
                        </div>
                        <input type="url" id="laravel_site_url" name="laravel_site_url" value="{{ old('laravel_site_url', $settings->laravel_site_url ?? '') }}" 
                               placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                        <p class="text-[11px] text-gray-500 mt-1">Your website domain URL (e.g. <code>https://mywebsite.com</code>).</p>
                    </div>

                    <!-- API Secret Token -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label class="block text-xs font-bold text-gray-700">API Secret Token</label>
                            <button type="button" onclick="showFieldHelp('laravel_api_token')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-indigo-100 hover:bg-indigo-200 text-indigo-600 transition text-[10px] font-bold cursor-pointer animate-pulse" title="Click to see where & how to paste this token in your Laravel project">
                                <i class="fas fa-info"></i>
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="laravel_api_token" name="laravel_api_token" value="{{ old('laravel_api_token', $settings->laravel_api_token ?? '') }}" 
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition font-mono text-xs" placeholder="e.g. sec_token_2026_xyz">
                            <button type="button" onclick="generateRandomToken()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 flex-shrink-0 flex items-center gap-1 cursor-pointer" title="Generate new secure token">
                                <i class="fas fa-sync-alt text-[10px]"></i> Generate
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Secret key used for secure server-to-server handshake.</p>
                    </div>

                    <!-- Route Prefix -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label class="block text-xs font-bold text-gray-700">News Link Prefix</label>
                            <button type="button" onclick="showFieldHelp('laravel_route_prefix')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-100 hover:bg-indigo-100 text-slate-400 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on News Link Prefix">
                                <i class="fas fa-info"></i>
                            </button>
                        </div>
                        <div class="flex items-center">
                            <span class="bg-gray-100 border border-r-0 border-gray-300 px-3 py-2 rounded-l text-gray-500 text-xs">/</span>
                            <input type="text" name="laravel_route_prefix" value="{{ old('laravel_route_prefix', $settings->laravel_route_prefix ?? 'news') }}" 
                                   class="w-full border-gray-300 rounded-r shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs" 
                                   placeholder="news, post, article">
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Post link format: <code>site.com/<b>news</b>/123</code></p>
                    </div>

                    <!-- Enable Auto Post Checkbox -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer bg-slate-50 hover:bg-slate-100 p-3.5 rounded-lg border border-slate-200 w-full transition">
                            <input type="hidden" name="post_to_laravel" value="0">
                            <input type="checkbox" name="post_to_laravel" value="1" {{ ($settings->post_to_laravel ?? false) ? 'checked' : '' }} class="toggle-checkbox w-5 h-5 text-indigo-600 rounded">
                            <div class="flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-gray-800 text-xs block">Enable Auto-Publish to Website</span>
                                    <button type="button" onclick="event.stopPropagation(); showFieldHelp('post_to_laravel');" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-200 hover:bg-indigo-200 text-slate-500 hover:text-indigo-700 transition text-[10px] cursor-pointer" title="Click for details">
                                        <i class="fas fa-info"></i>
                                    </button>
                                </div>
                                <span class="text-[11px] text-gray-500 block">When enabled, news is automatically published to your website upon approval.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Test Connection Button -->
                <div class="mt-4 pt-3 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">Verify endpoint connection before saving:</span>
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
                                <span>Configure custom paths or non-standard database column names below if necessary.</span>
                            </div>
                            <button type="button" onclick="openAssistantHelpModal()" class="text-indigo-600 font-semibold hover:underline flex items-center gap-1 text-[11px]">
                                View Integration Guide <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Custom URLs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <label class="block text-xs font-bold text-slate-700">Custom News Post Endpoint URL (Optional)</label>
                                    <button type="button" onclick="showFieldHelp('custom_api_url')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-200 hover:bg-indigo-100 text-slate-500 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on Custom News Post Endpoint">
                                        <i class="fas fa-info"></i>
                                    </button>
                                </div>
                                <input type="url" id="custom_api_url" name="custom_api_url" value="{{ old('custom_api_url', $settings->custom_api_url ?? '') }}" 
                                       placeholder="https://mywebsite.com/api/v1/articles/create" class="w-full border-slate-300 rounded-lg shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">If empty, defaults to <code>Base_URL/api/external-news-post</code>.</p>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <label class="block text-xs font-bold text-slate-700">Custom Category Fetch URL (Optional)</label>
                                    <button type="button" onclick="showFieldHelp('custom_category_url')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-200 hover:bg-indigo-100 text-slate-500 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on Category Fetch URL">
                                        <i class="fas fa-info"></i>
                                    </button>
                                </div>
                                <input type="url" id="custom_category_url" name="custom_category_url" value="{{ old('custom_category_url', $settings->custom_category_url ?? '') }}" 
                                       placeholder="https://mywebsite.com/api/v1/categories" class="w-full border-slate-300 rounded-lg shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">API URL to fetch categories from your website.</p>
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
                                    <option value="name">Text Category Name (e.g. "National")</option>
                                </select>
                            </div>
                        </div>

                        <!-- Visual Field Name Mapper -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Field Name Mappings (Default Payload ➔ Your API Parameter)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Title (Headline)</label>
                                    <input type="text" id="v_field_title" oninput="syncVisualToMappingJson()" placeholder="title / headline" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Content (Body Text)</label>
                                    <input type="text" id="v_field_content" oninput="syncVisualToMappingJson()" placeholder="content / body / desc" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Image (Featured Photo)</label>
                                    <input type="text" id="v_field_image" oninput="syncVisualToMappingJson()" placeholder="image / thumbnail_url" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Category (Taxonomy)</label>
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
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Additional Static Parameters (Optional Fields)</h4>
                                <button type="button" onclick="addExtraFieldRow()" class="text-xs bg-white hover:bg-slate-100 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-300 cursor-pointer">
                                    + Add Parameter
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-500 mb-3">E.g., if your database requires static values like <code>author_id = 1</code> or <code>status = published</code>, add them here.</p>
                            
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
            <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-5xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden text-slate-100 font-bangla">
                <!-- Modal Header -->
                <div class="p-5 bg-slate-800/90 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg shadow-inner">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base sm:text-lg text-white font-sans">
                                Website Integration Guide & FAQ (ওয়েবসাইট কানেকশন সহায়িকা)
                            </h3>
                            <p class="text-xs text-slate-400">Laravel, WordPress ও কাস্টম ওয়েবসাইট যুক্ত করার সম্পূর্ণ নির্দেশিকা ও সমস্যার সমাধান।</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('docs.api-guide') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-xs text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1.5 rounded-xl font-sans font-bold">
                            <i class="fas fa-external-link-alt"></i> Full Docs
                        </a>
                        <button type="button" onclick="closeAssistantHelpModal()" class="w-8 h-8 rounded-full bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer font-sans">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-800 bg-slate-950 px-5 gap-2 text-xs font-bold font-sans">
                    <button type="button" onclick="switchHelpTab('walkthrough')" class="help-tab-btn px-4 py-3 border-b-2 border-indigo-500 text-indigo-400 flex items-center gap-2 cursor-pointer" id="htab_walkthrough">
                        <i class="fas fa-list-ol"></i> Step-by-Step Guide
                    </button>
                    <button type="button" onclick="switchHelpTab('faq')" class="help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 cursor-pointer" id="htab_faq">
                        <i class="fas fa-question-circle"></i> Frequently Asked Questions (FAQ)
                    </button>
                    <button type="button" onclick="switchHelpTab('diagnostics')" class="help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 cursor-pointer" id="htab_diagnostics">
                        <i class="fas fa-wrench"></i> Troubleshooting (ত্রুটি ও সমাধান)
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="p-6 overflow-y-auto flex-1 bg-slate-900 space-y-6 text-xs text-slate-300">
                    
                    <!-- 1. WALKTHROUGH CONTENT -->
                    <div id="help_content_walkthrough" class="space-y-4">
                        <!-- Step 1 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">১</span>
                                ১. বেসিক কানেকশন ও API Secret Token (.env সেটআপ)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                Settings পেজে আপনার ওয়েবসাইটের ডোমেইন দিন (যেমন <code class="text-indigo-300 bg-slate-950 px-1.5 py-0.5 rounded font-mono">https://mybanglanews.com</code>)। এরপর <strong>API Secret Token</strong> এর <strong>Generate</strong> বাটনে ক্লিক করে একটি গোপন চাবি তৈরি করুন এবং সেটি আপনার Laravel প্রজেক্টের <code>.env</code> ফাইলে যোগ করুন:
                            </p>
                            <pre class="!py-2 !px-3 bg-slate-950 rounded-xl border border-slate-800 text-emerald-400 font-mono text-[11px]"><code>SUBEDITOR_API_SECRET=আপনার_সিক্রেট_টোকেন_এখানে</code></pre>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">২</span>
                                ২. Laravel প্রজেক্টে API রুট কোড বসানো (routes/api.php)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                আপনার Laravel প্রজেক্টের <code class="text-indigo-300 font-mono">routes/api.php</code> ফাইলে নিউজ রিসিভার এবং ক্যাটাগরি ফেচিং রুট যুক্ত করুন:
                            </p>
                            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 font-mono text-[11px] text-slate-300 space-y-1">
                                <p class="text-indigo-400 font-bold">// 1. News Receiver Route</p>
                                <p>Route::post('/external-news-post', function (Request $request) { ... });</p>
                                <p class="text-emerald-400 font-bold mt-2">// 2. Category Fetch Route</p>
                                <p>Route::get('/get-categories', function (Request $request) { ... });</p>
                            </div>
                            <div class="pt-1 flex gap-2">
                                <button type="button" onclick="openCodeGeneratorModal(); closeAssistantHelpModal();" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold flex items-center gap-1 font-sans">
                                    <i class="fas fa-code"></i> Open Code Generator to Copy Complete Laravel Code →
                                </button>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">৩</span>
                                ৩. ক্যাটাগরি রিফ্রেশ ও ম্যাপিং (Category Sync & Mapping)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                Settings পেজের <strong>"📂 Category Mapping"</strong> সেকশনে গিয়ে <strong>"🔄 Refresh Categories"</strong> বাটনে ক্লিক করুন। সাথে সাথে আপনার Laravel ওয়েবসাইটের সব ক্যাটাগরি চলে আসবে। এরপর বামপাশের AI বিষয়ের সাথে ডানপাশের আপনার ওয়েবসাইটের ক্যাটাগরি সিলেক্ট করে দিন।
                            </p>
                        </div>

                        <!-- Step 4 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">৪</span>
                                ৪. কানেকশন টেস্ট ও অটো-পাবলিশিং (Test Connection & Auto-Publish)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                কোড বসানোর পর <strong>"Test Connection"</strong> বাটনে চাপ দিন। সিস্টেম সাথে সাথে একটি টেস্ট পোস্ট পাঠিয়ে লাইভ কানেকশন চেক করবে। সফল হলে <strong>"Enable Auto-Publish"</strong> অন রাখুন যাতে কোনো নিউজ এপ্রুভ হওয়ার সাথে সাথেই আপনার ওয়েবসাইটে পাবলিশ হয়ে যায়।
                            </p>
                        </div>
                    </div>

                    <!-- 2. FAQ CONTENT -->
                    <div id="help_content_faq" class="space-y-3 hidden">
                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: API Secret Token কোথায় এবং কিভাবে বসাব?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Settings পেজ থেকে টোকেনটি কপি করে আপনার Laravel প্রজেক্টের <code>.env</code> ফাইলে <code class="text-indigo-300 font-mono">SUBEDITOR_API_SECRET=your_token</code> হিসেবে বসান। এরপর <code>routes/api.php</code> তে রিকোয়েস্টের হেডার <code>Authorization: Bearer ...</code> চেক করে টোকেন যাচাই করা হয়।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: ক্যাটাগরি ফেচ কিভাবে কাজ করে?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Subeditor24 আপনার ওয়েবসাইটের <code>/api/get-categories</code> রুটে কল করে ক্যাটাগরি তালিকা নিয়ে আসে। আপনার API থেকে <code>[{"id": 1, "name": "জাতীয়"}, {"id": 2, "name": "আন্তর্জাতিক"}]</code> ফরম্যাটে JSON অ্যারে রিটার্ন করতে হবে।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: ছবি কিভাবে আপলোড হয়?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Subeditor24 মূল সংবাদের ছবি ডাউনলোড করে multipart/form-data হিসেবে ফিজিক্যাল ফাইল আপলোড পাঠায়। আপনার Laravel এ <code>$request->file('image')->store('news', 'public')</code> দিয়ে সরাসরি সেভ করতে পারবেন।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: আমার ডাটাবেসে ফিল্ডের নাম আলাদা (যেমন title এর বদলে headline), কি করব?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Settings পেজের <strong>"⚙️ Custom API Mapping"</strong> অপশনটিতে গিয়ে <code class="text-indigo-300">"title": "headline"</code> লিখে দিন। Subeditor24 স্বয়ংক্রিয়ভাবে ফিল্ডের নাম পরিবর্তন করে পাঠাবে।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: লোকালহোস্ট (Localhost / 127.0.0.1) বা Staging এ কাজ করবে?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: হ্যাঁ! Subeditor24 লোকালহোস্ট ও সেলফ-সাইন্ড SSL সার্টিফিকেট স্বয়ংক্রিয়ভাবে বাইপাস করে কানেক্ট হতে পারে।</p>
                        </div>
                    </div>

                    <!-- 3. DIAGNOSTICS & TROUBLESHOOTING CONTENT -->
                    <div id="help_content_diagnostics" class="space-y-3 hidden">
                        <div class="bg-rose-950/40 border border-rose-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-rose-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i> HTTP 401 Unauthorized (অননুমোদিত রিকোয়েস্ট)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: API Secret Token ম্যাচ করেনি।<br>সমাধান: Settings এর টোকেন এবং আপনার Laravel <code>.env</code> ফাইলের টোকেন মিলিয়ে নিন। এরপর টার্মিনালে <code>php artisan config:clear</code> দিন। Apache সার্ভার হলে <code>.htaccess</code> এ <code>CGIPassAuth on</code> যোগ করুন।</p>
                        </div>

                        <div class="bg-amber-950/40 border border-amber-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-amber-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> HTTP 404 Not Found (রুট খুঁজে পাওয়া যায়নি)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: API রুটটি আপনার <code>routes/api.php</code> ফাইলে ডিফাইন করা হয়নি অথবা Base URL এর শেষে অতিরিক্ত স্ল্যাশ পড়েছে।<br>সমাধান: Base URL চেক করুন (যেমন <code>https://mywebsite.com</code>) এবং <code>routes/api.php</code> ফাইলে <code>Route::post('/external-news-post', ...)</code> রয়েছে কিনা নিশ্চিত হোন।</p>
                        </div>

                        <div class="bg-purple-950/40 border border-purple-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-purple-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-shield-alt"></i> HTTP 419 Page Expired / CSRF Error
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: রুটটি ভুলবশত <code>routes/web.php</code> তে লেখা হয়েছে।<br>সমাধান: API রুটগুলো অবশ্যই <code>routes/api.php</code> ফাইলে লিখতে হবে, যেখানে CSRF টোকেনের প্রয়োজন নেই।</p>
                        </div>

                        <div class="bg-cyan-950/40 border border-cyan-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-cyan-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-file-invoice"></i> HTTP 422 Unprocessable Content (ভ্যালিডেশন ফেইল)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: আপনার রিসিভার কোডে কোনো কাস্টম ফিল্ডকে <code>required</code> করা আছে যা Subeditor24 পাঠায়নি।<br>সমাধান: অপ্রয়োজনীয় ফিল্ডগুলোকে <code>nullable</code> করুন।</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-800/80 border-t border-slate-700/80 flex flex-wrap justify-between items-center gap-3">
                    <a href="{{ route('docs.api-guide') }}" target="_blank" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5 font-sans">
                        <i class="fas fa-book-open"></i> Open Comprehensive Live API Documentation Page →
                    </a>
                    <button type="button" onclick="closeAssistantHelpModal()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer font-sans">
                        Got it (ঠিক আছে)
                    </button>
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
                            <p class="text-xs text-slate-400">Copy drop-in receiver code for your stack.</p>
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

        {{-- 💡 INTERACTIVE FIELD HELP & SETUP MODAL --}}
        <div id="fieldInfoModal" class="fixed inset-0 z-[120] bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden text-slate-100 animate-in fade-in zoom-in-95 duration-150">
                {{-- Modal Header --}}
                <div class="p-5 bg-slate-800/90 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg shadow-inner" id="fieldInfoIcon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white flex items-center gap-2" id="fieldInfoTitle">
                                Field Information
                            </h3>
                            <p class="text-xs text-slate-400" id="fieldInfoSubtitle">Configuration & Integration Guide</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeFieldInfoModal()" class="w-8 h-8 rounded-full bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-300 leading-relaxed font-bangla">
                    {{-- Field Description --}}
                    <div id="fieldInfoDesc" class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/60 text-slate-200">
                        <!-- Injected -->
                    </div>

                    {{-- Step-by-Step Setup --}}
                    <div id="fieldInfoStepsContainer" class="space-y-3">
                        <h4 class="font-bold text-white text-xs flex items-center gap-2 uppercase tracking-wider text-indigo-400">
                            <i class="fas fa-layer-group"></i> আপনার Laravel প্রজেক্টে যেভাবে বসাবেন:
                        </h4>
                        <div id="fieldInfoSteps" class="space-y-2">
                            <!-- Injected -->
                        </div>
                    </div>

                    {{-- Code Snippet Box --}}
                    <div id="fieldInfoCodeWrapper" class="space-y-2">
                        <div class="flex justify-between items-center text-[11px] text-slate-400">
                            <span id="fieldInfoFilePath" class="font-mono text-indigo-300">File: routes/api.php</span>
                            <button type="button" onclick="copyFieldInfoCode()" id="fieldInfoCopyBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-sans px-3 py-1 rounded-lg text-xs flex items-center gap-1 transition cursor-pointer font-bold">
                                <i class="fas fa-copy"></i> Copy Code
                            </button>
                        </div>
                        <pre class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-emerald-400 font-mono text-[11px] leading-relaxed overflow-x-auto select-all whitespace-pre" id="fieldInfoCodeBox"></pre>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-slate-800/80 border-t border-slate-700/80 flex flex-wrap justify-between items-center gap-3">
                    <button type="button" onclick="openCodeGeneratorModal(); closeFieldInfoModal();" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-code"></i> Open Full Code Generator (All Frameworks)
                    </button>
                    <button type="button" onclick="closeFieldInfoModal()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                        Got it (ঠিক আছে)
                    </button>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_category'))
        {{-- Category Mapping (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-base">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            📂 Category Mapping
                        </h2>
                        <p class="text-xs text-gray-500">Map AI-detected topics to target website categories</p>
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
                        Select the target website category for each AI-detected topic.
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

        {{-- Telegram Notifications (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-base">
                        <i class="fab fa-telegram-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            ✈️ Telegram Notifications
                        </h2>
                        <p class="text-xs text-gray-500">Telegram channel ID and alert settings</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-sky-100 text-sky-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Telegram</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Channel ID</label>
                    <input type="text" name="telegram_channel_id" value="{{ old('telegram_channel_id', $settings->telegram_channel_id ?? '') }}" placeholder="-100xxxxxxxxxx" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    <p class="text-[11px] text-gray-500 mt-1">Add your bot as an admin to the channel and enter Channel ID.</p>
                </div>
            </div>
        </div>

        <!-- Sticky or Bottom Save Bar -->
        <div class="flex justify-end pt-4 sticky bottom-4 z-20">
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-8 py-3 rounded-xl font-bold text-base hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2 cursor-pointer shadow-lg">
                <i class="fas fa-save"></i> <span>💾 Save Settings</span>
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

    // 1. Fetch Categories (with caching)
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
                    if(forceRefresh) alert('✅ Category list updated successfully!');
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

    // 2. Populate Dropdowns
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

    // 3. Connection Test Functions
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

    // Button Click Events
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
            statusMsg.innerText = "❌ Please enter a PhotoRoom API Key.";
            statusMsg.className = "text-xs font-bold mt-2 text-red-600";
            return;
        }

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Testing PhotoRoom server handshake...";
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
            statusMsg.innerText = "❌ Network error: " + err.message;
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
        statusMsg.innerHTML = "⏳ Testing Decodo Universal API & Proxy handshake...";
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
            statusMsg.innerText = "❌ Network error: " + err.message;
            statusMsg.className = "text-xs font-bold mb-4 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    const defaultAiPrompts = {
        bn: @json(\App\Services\AIWriterService::getDefaultPrompt('bn')),
        en: @json(\App\Services\AIWriterService::getDefaultPrompt('en'))
    };

    function insertDefaultPrompt(lang = 'bn') {
        const promptArea = document.getElementById('custom_rewrite_prompt');
        if (!promptArea) return;
        if (promptArea.value.trim() !== '' && !confirm('Are you sure you want to replace the current prompt with the default prompt?')) {
            return;
        }
        promptArea.value = defaultAiPrompts[lang] || defaultAiPrompts['bn'];
        promptArea.focus();
    }

    function resetDefaultPrompt() {
        const promptArea = document.getElementById('custom_rewrite_prompt');
        if (!promptArea) return;
        if (promptArea.value.trim() !== '' && !confirm('Are you sure you want to reset to default? This will clear the custom prompt so the built-in system prompt will be used.')) {
            return;
        }
        promptArea.value = '';
    }

    function testAiProvider(provider) {
        const keyInput = document.getElementById(provider + '_api_key');
        const modelInput = document.getElementById(provider + '_model');
        const statusMsg = document.getElementById(provider + '_status_msg');
        const btn = event.currentTarget || document.activeElement;
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Sending test request to AI server...";
        statusMsg.className = "text-xs font-bold mt-2 text-blue-600 bg-blue-50 p-2.5 rounded border border-blue-200 block";

        fetch(`/settings/test/ai-provider`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                provider: provider,
                api_key: keyInput ? keyInput.value.trim() : '',
                model: modelInput ? modelInput.value.trim() : ''
            })
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            if (data.success) {
                statusMsg.className = "text-xs font-bold mt-2 text-green-700 bg-green-50 p-2.5 rounded border border-green-300 block whitespace-pre-line";
            } else {
                statusMsg.className = "text-xs font-bold mt-2 text-red-700 bg-red-50 p-2.5 rounded border border-red-300 block whitespace-pre-line";
            }
        })
        .catch(err => {
            statusMsg.innerText = "❌ Network error: " + err.message;
            statusMsg.className = "text-xs font-bold mt-2 text-red-700 bg-red-50 p-2.5 rounded border border-red-300 block whitespace-pre-line";
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
            box.innerText = 'Please provide Website Base URL or Custom News Post Endpoint URL.';
            return;
        }

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        btn.disabled = true;

        box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 block';
        box.innerText = '⏳ Sending handshake test request...';

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
            box.innerText = '❌ Network error: ' + err.message;
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
            alert('✅ Code copied to clipboard!');
        });
    }

    // Auto load categories & sync visual mapper on load
    document.addEventListener('DOMContentLoaded', function () {
        fetchWPCategories();
        syncMappingJsonToVisual();
    });

    // ==========================================================
    // 🩺 1-CLICK SYSTEM HEALTH & DIAGNOSTICS ENGINE
    // ==========================================================
    function runDiagnosticsModal() {
        const modal = document.getElementById('systemDiagnosticsModal');
        const loading = document.getElementById('diagnosticsLoading');
        const container = document.getElementById('diagnosticsResultsContainer');
        const timestamp = document.getElementById('diagnosticsTimestamp');

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        if (loading) loading.classList.remove('hidden');
        if (container) {
            container.classList.add('hidden');
            container.innerHTML = '';
        }

        fetch("{{ route('settings.diagnostics') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (loading) loading.classList.add('hidden');
            if (timestamp && data.timestamp) timestamp.innerText = `Last checked: ${data.timestamp}`;

            if (data.success && data.checks) {
                if (container) {
                    const statusIcons = {
                        ok: '<div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm"><i class="fa-solid fa-circle-check"></i></div>',
                        warning: '<div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm"><i class="fa-solid fa-triangle-exclamation"></i></div>',
                        error: '<div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm"><i class="fa-solid fa-circle-xmark"></i></div>',
                        info: '<div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center text-sm"><i class="fa-solid fa-circle-info"></i></div>'
                    };

                    const badgeColors = {
                        ok: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800',
                        warning: 'bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800',
                        error: 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800',
                        info: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
                    };

                    container.innerHTML = data.checks.map(item => `
                        <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start gap-3">
                            ${statusIcons[item.status] || statusIcons.info}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-0.5">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">${item.name}</h4>
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md ${badgeColors[item.status] || badgeColors.info}">${item.badge}</span>
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug">${item.message}</p>
                            </div>
                        </div>
                    `).join('');

                    container.classList.remove('hidden');
                }
            }
        })
        .catch(err => {
            if (loading) loading.classList.add('hidden');
            if (container) {
                container.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 rounded-xl text-xs font-bold">Server error: ${err.message}</div>`;
                container.classList.remove('hidden');
            }
        });
    }

    // ==========================================================
    // 💡 FIELD HELP & INTEGRATION MODAL ENGINE
    // ==========================================================
    const fieldHelpData = {
        laravel_api_token: {
            title: 'API Secret Token (সিক্রেট কী)',
            subtitle: 'Laravel ও Subeditor24 এর নিরাপদ সংযোগ চাবি',
            icon: '<i class="fas fa-key text-amber-400"></i>',
            desc: `এই <strong>API Secret Token</strong>-টি একটি গোপন পাসওয়ার্ড। Subeditor24 যখন আপনার Laravel ওয়েবসাইটে নিউজ পোস্ট পাঠায়, তখন রিকোয়েস্টের হেডারে <code>Authorization: Bearer <Secret_Token></code> হিসেবে এই টোকেনটি পাঠানো হয়। আপনার ওয়েবসাইট এই টোকেন মিলিয়ে দেখে রিকোয়েস্টটি বৈধ কিনা।`,
            steps: [
                `<strong>Step 1:</strong> আপনার Laravel নিউজ প্রজেক্টের <code>.env</code> ফাইলে টোকেনটি সংরক্ষণ করুন:<br><code class="text-indigo-300">SUBEDITOR_API_SECRET=আপনার_টোকেন_এখানে</code>`,
                `<strong>Step 2:</strong> আপনার Laravel প্রজেক্টের <code>routes/api.php</code> ফাইলে এই রুট কোডটি যুক্ত করুন:`,
                `<strong>Step 3:</strong> Settings পেজের <strong>"Test Connection"</strong> বাটনে ক্লিক করে লাইভ সংযোগ পরীক্ষা করুন।`
            ],
            filePath: 'Laravel File: routes/api.php',
            code: `use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Route;
use App\\Models\\NewsPost; // আপনার নিউজ মডেল

Route::post('/external-news-post', function (Request $request) {
    // ১. সিক্রেট টোকেন যাচাই (Security Handshake)
    $expectedToken = "Bearer " . env('SUBEDITOR_API_SECRET', 'YOUR_SECRET_TOKEN_HERE');
    if ($request->header('Authorization') !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid API Secret Token'
        ], 401);
    }

    // ২. ডেটা ভ্যালিডেশন
    $validated = $request->validate([
        'title'       => 'required|string',
        'content'     => 'required|string',
        'image'       => 'nullable|string',
        'category'    => 'nullable|string',
        'category_id' => 'nullable',
        'tags'        => 'nullable|string',
        'slug'        => 'nullable|string',
    ]);

    // ৩. আপনার ডাটাবেসে নিউজ সেভ করুন
    $post = NewsPost::create([
        'title'       => $validated['title'],
        'slug'        => $validated['slug'] ?? \\Illuminate\\Support\\Str::slug($validated['title']),
        'content'     => $validated['content'],
        'image'       => $validated['image'] ?? null,
        'category_id' => $validated['category_id'] ?? 1,
        'status'      => 'published',
    ]);

    // ৪. সফল রেসপন্স ও পোস্টের লাইভ লিংক রিটার্ন করুন
    return response()->json([
        'success' => true,
        'message' => 'News published successfully',
        'post_id' => $post->id,
        'url'     => url('/news/' . $post->slug)
    ], 200);
});`
        },
        laravel_site_url: {
            title: 'Website Base URL (ওয়েবসাইটের ডোমেইন)',
            subtitle: 'আপনার নিউজ পোর্টালের মূল লাইভ লিঙ্ক',
            icon: '<i class="fas fa-globe text-indigo-400"></i>',
            desc: `যে Laravel বা কাস্টম ওয়েবসাইটে নিউজগুলো স্বয়ংক্রিয়ভাবে পোস্ট হবে, তার মূল ডোমেইন ইউআরএল এখানে দিন। যেমন: <code>https://mybanglanews.com</code> বা লোকাল ডেভেলপমেন্টে <code>http://127.0.0.1:8000</code>।`,
            steps: [
                `<strong>Step 1:</strong> সম্পূর্ণ প্রোটোকল সহ ডোমেইন দিন (e.g. <code>https://yourdomain.com</code>)। শেষে কোনো স্ল্যাশ (/) দিবেন না।`,
                `<strong>Step 2:</strong> Subeditor24 ডিফল্টভাবে <code>Base_URL/api/external-news-post</code> এ রিকোয়েস্ট পাঠাবে।`
            ],
            filePath: 'Settings Field: Website Base URL',
            code: `// Subeditor24 will send POST request to:
https://yourdomain.com/api/external-news-post`
        },
        laravel_route_prefix: {
            title: 'News Link Prefix (পারমালিংক প্রিফিক্স)',
            subtitle: 'নিউজ ভিজিট লিঙ্কের ফরম্যাট',
            icon: '<i class="fas fa-link text-emerald-400"></i>',
            desc: `আপনার ওয়েবসাইটে প্রতিটি নিউজের ইউআরএল স্ট্রাকচার কেমন তা নির্ধারণ করে। যেমন আপনার নিউজের লিংক যদি <code>https://site.com/news/123</code> হয়, তবে প্রিফিক্স হবে <code>news</code>। যদি <code>https://site.com/post/123</code> হয়, তবে <code>post</code>।`,
            steps: [
                `ডিফল্ট ভ্যালু: <code>news</code>`,
                `আর্টিকেল লিংক তৈরি হবে: <code>https://yourdomain.com/news/{slug_or_id}</code>`
            ],
            filePath: 'URL Format',
            code: `https://yourdomain.com/{prefix}/{news_id_or_slug}`
        },
        post_to_laravel: {
            title: 'Enable Auto-Publish to Website (অটো পাবলিশ)',
            subtitle: 'এপ্রুভালের সাথে সাথে লাইভ পোস্টিং',
            icon: '<i class="fas fa-paper-plane text-cyan-400"></i>',
            desc: `এই অপশনটি চালু রাখলে Subeditor24-এ যখন কোনো এআই রিরাইট করা নিউজ এডিটর এপ্রুভ করবেন, সাথে সাথে তা আপনার ওয়েবসাইটে API কলের মাধ্যমে লাইভ পাবলিশ হয়ে যাবে।`,
            steps: [
                `সুইচটি অন রাখলে রিয়েল-টাইম অটোমেটিক পাবলিশিং চালু থাকবে।`,
                `সুইচটি অফ রাখলে নিউজ শুধুমাত্র Subeditor24 ড্যাশবোর্ডে সেভ থাকবে এবং ম্যানুয়ালি পোস্ট করা যাবে।`
            ],
            filePath: 'Feature Overview',
            code: `Auto Publish: ENABLED -> Triggers API on News Approval`
        },
        custom_api_url: {
            title: 'Custom News Post Endpoint URL',
            subtitle: 'কাস্টম এপিআই রুট ওভাররাইড',
            icon: '<i class="fas fa-route text-rose-400"></i>',
            desc: `যদি আপনার Laravel ওয়েবসাইটে ডিফল্ট <code>/api/external-news-post</code> ছাড়া অন্য কোনো স্পেশাল এপিআই এন্ডপয়েন্ট থাকে (যেমন <code>https://api.site.com/v2/news/create</code>), তবে সম্পূর্ণ লিংকটি এখানে প্রদান করুন।`,
            steps: [
                `ফাঁকা রাখলে স্বয়ংক্রিয়ভাবে <code>Base_URL/api/external-news-post</code> ব্যবহৃত হবে।`
            ],
            filePath: 'Custom API Endpoint',
            code: `POST https://mywebsite.com/api/v1/custom-news-receiver`
        },
        custom_category_url: {
            title: 'Custom Category Fetch URL',
            subtitle: 'ক্যাটাগরি লিস্ট এপিআই',
            icon: '<i class="fas fa-tags text-purple-400"></i>',
            desc: `আপনার ওয়েবসাইট থেকে ক্যাটাগরি তালিকা স্বয়ংক্রিয়ভাবে এনে Subeditor24-এর সাথে ম্যাপ করতে এই এপিআই ব্যবহৃত হয়। এটি থেকে JSON অ্যারে রিটার্ন করতে হবে: <code>[{"id": 1, "name": "জাতীয়"}, {"id": 2, "name": "আন্তর্জাতিক"}]</code>।`,
            steps: [
                `আপনার Laravel প্রজেক্টে একটি ক্যাটাগরি এপিআই রুট তৈরি করুন এবং সেই লিংকটি এখানে দিন।`
            ],
            filePath: 'Laravel File: routes/api.php',
            code: `Route::get('/categories', function () {
    return response()->json(
        \\App\\Models\\Category::select('id', 'name')->get()
    );
});`
        }
    };

    function showFieldHelp(fieldKey) {
        const data = fieldHelpData[fieldKey];
        if (!data) return;

        const modal = document.getElementById('fieldInfoModal');
        const title = document.getElementById('fieldInfoTitle');
        const subtitle = document.getElementById('fieldInfoSubtitle');
        const icon = document.getElementById('fieldInfoIcon');
        const desc = document.getElementById('fieldInfoDesc');
        const stepsContainer = document.getElementById('fieldInfoSteps');
        const filePath = document.getElementById('fieldInfoFilePath');
        const codeBox = document.getElementById('fieldInfoCodeBox');
        const codeWrapper = document.getElementById('fieldInfoCodeWrapper');

        if (title) title.innerHTML = data.title;
        if (subtitle) subtitle.innerText = data.subtitle;
        if (icon && data.icon) icon.innerHTML = data.icon;
        if (desc) desc.innerHTML = data.desc;

        if (stepsContainer && data.steps) {
            stepsContainer.innerHTML = data.steps.map(step => `
                <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="text-indigo-400 font-bold text-xs mt-0.5">•</span>
                    <div class="text-xs text-slate-300 flex-1 leading-relaxed">${step}</div>
                </div>
            `).join('');
        }

        if (data.code) {
            const currentToken = document.getElementById('laravel_api_token')?.value?.trim() || 'YOUR_SECRET_TOKEN_HERE';
            const populatedCode = data.code.replace('YOUR_SECRET_TOKEN_HERE', currentToken);
            if (filePath) filePath.innerText = data.filePath || 'Code Snippet';
            if (codeBox) codeBox.innerText = populatedCode;
            if (codeWrapper) codeWrapper.classList.remove('hidden');
        } else {
            if (codeWrapper) codeWrapper.classList.add('hidden');
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeFieldInfoModal() {
        const modal = document.getElementById('fieldInfoModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function copyFieldInfoCode() {
        const code = document.getElementById('fieldInfoCodeBox')?.innerText;
        if (!code) return;

        navigator.clipboard.writeText(code).then(() => {
            const btn = document.getElementById('fieldInfoCopyBtn');
            if (btn) {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-emerald-400"></i> Copied!';
                setTimeout(() => { btn.innerHTML = orig; }, 2000);
            }
        });
    }

    function closeDiagnosticsModal() {
        const modal = document.getElementById('systemDiagnosticsModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }
</script>

{{-- 🩺 SYSTEM HEALTH & DIAGNOSTICS MODAL --}}
<div id="systemDiagnosticsModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div>
                    <h3 class="text-base font-black">System Health Diagnostics</h3>
                    <p class="text-[11px] text-white/80 font-semibold" id="diagnosticsTimestamp">1-Click Live System Status</p>
                </div>
            </div>
            <button type="button" onclick="closeDiagnosticsModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition cursor-pointer">
                ✕
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 font-bangla">
            {{-- Loading Spinner --}}
            <div id="diagnosticsLoading" class="py-12 flex flex-col items-center justify-center gap-3">
                <div class="w-12 h-12 rounded-full border-4 border-emerald-200 border-t-emerald-600 animate-spin"></div>
                <p class="text-sm font-bold text-slate-600 dark:text-slate-300 animate-pulse">Testing Database, AI, and API connections...</p>
            </div>

            {{-- Diagnostics Results Container --}}
            <div id="diagnosticsResultsContainer" class="hidden space-y-3">
                {{-- Injected dynamically --}}
            </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-850 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center font-bangla">
            <span class="text-xs text-slate-400 font-semibold">Automated live connection audit</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="runDiagnosticsModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-rotate-right"></i> Re-test
                </button>
                <button type="button" onclick="closeDiagnosticsModal()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
