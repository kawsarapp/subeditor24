@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">⚙️ প্রোফাইল ও সেটিংস</h1>
            <p class="text-gray-500 mt-1">আপনার নিউজ কার্ড এবং অটোমেশন কনফিগারেশন</p>
        </div>
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl shadow-lg text-center">
            <p class="text-xs opacity-80 uppercase tracking-wider">বর্তমান ব্যালেন্স</p>
            <p class="text-2xl font-bold">{{ auth()->user()->credits }} <span class="text-sm font-normal">Credits</span></p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center gap-2" role="alert">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- ১. প্রোফাইল আপডেট সেকশন --}}
    <form action="{{ route('settings.update-profile') }}" method="POST" class="mb-8">
        @csrf
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
                👤 আমার প্রোফাইল
            </h2>
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
            <div class="mt-4 text-right">
                <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-900 transition shadow">
                    প্রোফাইল আপডেট করুন
                </button>
            </div>
        </div>
    </form>

    {{-- ২. মূল সেটিংস ফর্ম শুরু --}}
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-8">
        @csrf

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_proxy'))
        {{-- 🔥 প্রক্সি সেটিংস কার্ড --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
                🌐 Proxy Settings (Premium Scraping)
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Proxy Host (IP/Domain)</label>
                    <input type="text" name="proxy_host" class="w-full border-gray-300 rounded-lg shadow-sm" 
                           value="{{ $settings->proxy_host ?? '' }}" placeholder="e.g. as.smartproxy.net">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Proxy Port</label>
                    <input type="text" name="proxy_port" class="w-full border-gray-300 rounded-lg shadow-sm" 
                           value="{{ $settings->proxy_port ?? '' }}" placeholder="e.g. 3120">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Proxy Username</label>
                    <input type="text" name="proxy_username" class="w-full border-gray-300 rounded-lg shadow-sm" 
                           value="{{ $settings->proxy_username ?? '' }}">
                </div>
                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700 mb-1">Proxy Password</label>
                    <input type="password" name="proxy_password" class="w-full border-gray-300 rounded-lg shadow-sm" 
                           value="{{ $settings->proxy_password ?? '' }}">
                </div>
            </div>

            {{-- Auto Clean Section --}}
            <div class="mt-4 pt-4 border-t border-gray-100">
                <label class="block text-sm font-bold text-gray-700 mb-1">
                    🧹 Auto Clean Pending News After (Days)
                </label>
                <div class="flex items-center gap-3">
                    <input type="number" name="auto_clean_days"
                           min="1" max="90"
                           value="{{ $settings->auto_clean_days ?? 7 }}"
                           class="w-32 border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-center font-bold text-lg">
                    <p class="text-xs text-gray-500">দিন পরে যে নিউজ পোস্ট করা হয়নি সেগুলো অটোমেটিক ডিলিট হবে। (Default: 7 দিন)</p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin')
        {{-- ROI Config Section --}}
        @php
            $roiConfig = isset($settings->roi_config) ? (is_string($settings->roi_config) ? json_decode($settings->roi_config, true) : $settings->roi_config) : [];
        @endphp
        <div class="bg-green-50 p-6 rounded-xl shadow-sm border border-green-200 mt-6 mb-6 relative overflow-hidden text-sm">
            <h2 class="text-xl font-bold text-green-800 mb-2 border-b border-green-200 pb-2 flex items-center gap-2">
                💰 ROI Calculator Config (সুপার অ্যাডমিন)
            </h2>
            <p class="text-[11px] text-green-700 mb-4 font-medium">কাজের মান অনুযায়ী কত টাকা সাশ্রয় হচ্ছে তা কনফিগার করুন।</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">কর্মী খরচ (প্রতি ঘণ্টা BDT)</label>
                    <input type="number" name="roi_hourly_rate" value="{{ $roiConfig['hourly_rate'] ?? 100 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">১টি নিউজ করতে সময় (মিনিট)</label>
                    <input type="number" name="roi_news_minutes" value="{{ $roiConfig['news_minutes'] ?? 20 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">১টি কার্ড বানাতে সময় (মিনিট)</label>
                    <input type="number" name="roi_card_minutes" value="{{ $roiConfig['card_minutes'] ?? 15 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500">
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            {{-- 🎨 Template Manager Link --}}
            <div class="bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-700 relative overflow-hidden flex flex-col justify-between">
                <div class="absolute top-0 right-0 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg shadow-sm uppercase tracking-widest">Super Admin</div>
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">🎨 Studio Template Manager</h2>
                    <p class="text-xs text-slate-400 mt-1 mb-4">Dashboard থেকে নতুন template যোগ করুন — frame URL, position সব এক জায়গায় কনফিগার করুন।</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.templates.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 rounded-lg transition shadow-md text-center">🎨 Templates</a>
                    <a href="{{ route('admin.templates.create') }}" class="flex-1 bg-slate-600 hover:bg-slate-500 text-white font-bold text-xs py-2 rounded-lg transition text-center">+ Add New</a>
                </div>
            </div>

            {{-- 📁 Media Manager Link --}}
            <div class="bg-slate-800 p-5 rounded-xl shadow-sm border border-slate-700 relative overflow-hidden flex flex-col justify-between">
                <div class="absolute top-0 right-0 bg-purple-600 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg shadow-sm uppercase tracking-widest">Super Admin</div>
                <div>
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">📁 Media & Assets Manager</h2>
                    <p class="text-xs text-slate-400 mt-1 mb-4">Template এর Frame PNG এবং Custom Font (.ttf, .woff) আপলোড, রিনেইম, ও URL কপি করুন।</p>
                </div>
                <div>
                    <a href="{{ route('admin.media.index') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs py-2 rounded-lg transition shadow-md text-center">📁 Open Media Manager</a>
                </div>
            </div>
        </div>
        @endif


        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_branding'))
        {{-- ৩. ব্র্যান্ডিং সেকশন --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
                🎨 ব্র্যান্ডিং <span class="text-xs font-normal text-gray-400">(নিউজ কার্ডের জন্য)</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">ব্র্যান্ড নাম (e.g. Dhaka Post)</label>
                    <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name ?? 'My News') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">ডিফল্ট কালার থিম</label>
                    <select name="default_theme_color" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                        <option value="red" {{ ($settings->default_theme_color ?? '') == 'red' ? 'selected' : '' }}>Red (Breaking)</option>
                        <option value="blue" {{ ($settings->default_theme_color ?? '') == 'blue' ? 'selected' : '' }}>Blue (Standard)</option>
                        <option value="green" {{ ($settings->default_theme_color ?? '') == 'green' ? 'selected' : '' }}>Green (Sports/Islamic)</option>
                        <option value="purple" {{ ($settings->default_theme_color ?? '') == 'purple' ? 'selected' : '' }}>Purple (Lifestyle)</option>
                        <option value="black" {{ ($settings->default_theme_color ?? '') == 'black' ? 'selected' : '' }}>Black (Dark)</option>
                    </select>
                </div>
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">লোগো URL (অপশনাল)</label>
                    <input type="url" name="logo_url" value="{{ old('logo_url', $settings->logo_url ?? '') }}" placeholder="https://example.com/logo.png" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                    <p class="text-xs text-gray-500 mt-1">আপনি স্টুডিও থেকেও লোগো আপলোড করতে পারেন।</p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_proxy'))
        {{-- 🔥 SCRAPER PROXY SETTINGS --}}
        <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-200 mt-6 relative overflow-hidden text-sm">
            <h2 class="text-xl font-bold text-gray-800 mb-2 border-b border-gray-200 pb-2 flex items-center gap-2">
                🌐 Scraper & Proxy Settings
            </h2>
            <p class="text-[11px] text-gray-600 mb-6 font-medium">নিউজ স্ক্র্যাপ করার জন্য নিজস্ব প্রক্সি এবং অ্যাডভান্স স্পেশাল API কনফিগারেশন। এগুলো খালি রাখলে গ্লোবাল সিস্টেমের (Super Admin) বা .env এর মান ব্যবহার করা হবে।</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Standard Proxy -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <h3 class="font-bold text-gray-700 mb-3 border-b pb-1">Standard Proxy (Puppeteer & Python)</h3>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Proxy Host</label>
                            <input type="text" name="proxy_host" value="{{ old('proxy_host', $settings->proxy_host ?? '') }}" placeholder="proxy.example.com" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Proxy Port</label>
                            <input type="number" name="proxy_port" value="{{ old('proxy_port', $settings->proxy_port ?? '') }}" placeholder="10000" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Username (Optional)</label>
                            <input type="text" name="proxy_username" value="{{ old('proxy_username', $settings->proxy_username ?? '') }}" placeholder="username" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Password (Optional)</label>
                            <input type="password" name="proxy_password" value="{{ old('proxy_password', $settings->proxy_password ?? '') }}" placeholder="••••••••" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Universal Scraping API -->
                <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                    <h3 class="font-bold text-blue-700 mb-3 border-b pb-1">🚀 SmartProxy Universal API</h3>
                    <p class="text-xs mb-3 text-gray-500">কঠিন সাইট (যমুনা টিভি, Datadome-যুক্ত সাইট) স্ক্র্যাপ করার সবচেয়ে শক্তিশালী API টোকেন।</p>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">Universal Scraping API Token (Basic Auth Token)</label>
                        <input type="password" name="smartproxy_api_token" value="{{ old('smartproxy_api_token', $settings->smartproxy_api_token ?? '') }}" placeholder="Basic c21hc..." class="w-full border-gray-300 rounded shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs">
                        <p class="text-[10px] text-gray-400 mt-1">কমান্ড <code>Basic Base64(User:Pass)</code></p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        </div>
        
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_target_language'))
        {{-- 🔥 TARGET LANGUAGE SETTINGS --}}
        <div class="bg-teal-50 p-6 rounded-xl shadow-sm border border-teal-200 relative overflow-hidden mt-6 text-sm">
            <div class="absolute top-0 right-0 bg-teal-600 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg shadow-sm">Language</div>
            <h2 class="text-xl font-bold text-teal-900 mb-2 border-b border-teal-200 pb-2 flex items-center gap-2">
                🌍 Target Language (ডিফল্ট নিউজ ভাষা)
            </h2>
            <div class="bg-white p-4 rounded-lg border border-teal-300 shadow-sm">
                <select name="target_language" class="w-full border-gray-300 rounded shadow-sm focus:border-teal-500 focus:ring-teal-500 font-semibold">
                    <option value="" {{ empty($settings->target_language) ? 'selected' : '' }}>Website Default (ওয়েবসাইটের সেটিং অনুযায়ী)</option>
                    <option value="bn" {{ ($settings->target_language ?? '') == 'bn' ? 'selected' : '' }}>Always Bengali (বাংলা)</option>
                    <option value="en" {{ ($settings->target_language ?? '') == 'en' ? 'selected' : '' }}>Always English (ইংরেজি)</option>
                </select>
                <p class="text-[11px] text-gray-500 mt-2">আপনি যখন কোনো নিউজ স্ক্র্যাপ করবেন, তখন এই ভাষা অনুযায়ী প্রসেস হবে। (তবে ওয়েবসাইটের সেটিংসে আলাদা ভাষা দেওয়া থাকলে সেটি প্রাধান্য পাবে)।</p>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai'))
        {{-- 🔥 AI CONFIGURATION (NEW) --}}
        <div class="bg-indigo-50 p-6 rounded-xl shadow-sm border border-indigo-200 relative overflow-hidden mt-6 text-sm">
            <div class="absolute top-0 right-0 bg-indigo-600 text-white text-[10px] font-bold px-3 py-1 rounded-bl-lg shadow-sm">AI Configuration</div>
            <h2 class="text-xl font-bold text-indigo-900 mb-2 border-b border-indigo-200 pb-2 flex items-center gap-2">
                🤖 AI অপটিমাইজেশন সেটিংস
            </h2>
            <p class="text-xs text-indigo-700 mb-6 font-medium">প্রতিটি প্রোভাইডারের জন্য API Key এবং Model সেট করতে পারবেন। (খালি রাখলে সিস্টেমের ডিফল্ট .env এরগুলো ব্যবহার হবে)</p>

            <div class="mb-6 bg-white p-4 rounded-lg border border-indigo-300 shadow-sm">
                <label class="block text-sm font-bold text-gray-800 mb-2">⭐ Primary AI Provider</label>
                <select name="primary_ai" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-indigo-900">
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
                    <h3 class="font-bold text-gray-700 mb-2">Gemini (Google)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">API Key</label>
                            <input type="password" name="gemini_api_key" value="{{ old('gemini_api_key', $settings->gemini_api_key ?? '') }}" placeholder="AIzaSy... (খালি রাখলে .env ব্যবহার হবে)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Model Selection</label>
                            <select name="gemini_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                    <h3 class="font-bold text-gray-700 mb-2">DeepSeek</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">API Key</label>
                            <input type="password" name="deepseek_api_key" value="{{ old('deepseek_api_key', $settings->deepseek_api_key ?? '') }}" placeholder="sk-... (খালি রাখলে .env ব্যবহার হবে)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Model Selection</label>
                            <select name="deepseek_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Default (deepseek-chat)</option>
                                <option value="deepseek-chat" {{ ($settings->deepseek_model ?? '') == 'deepseek-chat' ? 'selected' : '' }}>DeepSeek V3 (deepseek-chat)</option>
                                <option value="deepseek-reasoner" {{ ($settings->deepseek_model ?? '') == 'deepseek-reasoner' ? 'selected' : '' }}>DeepSeek R1 (deepseek-reasoner)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Qwen (DashScope) -->
                <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                    <h3 class="font-bold text-gray-700 mb-2">Qwen (DashScope API)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">API Key</label>
                            <input type="password" name="qwen_api_key" value="{{ old('qwen_api_key', $settings->qwen_api_key ?? '') }}" placeholder="sk-... (DashScope Key)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Model Selection</label>
                            <select name="qwen_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Default (qwen-plus)</option>
                                <option value="qwen-plus" {{ ($settings->qwen_model ?? '') == 'qwen-plus' ? 'selected' : '' }}>Qwen Plus (Balanced & Fast)</option>
                                <option value="qwen-max" {{ ($settings->qwen_model ?? '') == 'qwen-max' ? 'selected' : '' }}>Qwen Max (Best Quality)</option>
                                <option value="qwen2.5-72b-instruct" {{ ($settings->qwen_model ?? '') == 'qwen2.5-72b-instruct' ? 'selected' : '' }}>Qwen 2.5 72B (Open Source King)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Groq (Llama) -->
                <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                    <h3 class="font-bold text-gray-700 mb-2">Groq (Llama / Mixtral)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">API Key</label>
                            <input type="password" name="groq_api_key" value="{{ old('groq_api_key', $settings->groq_api_key ?? '') }}" placeholder="gsk_..." class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Model Selection</label>
                            <select name="groq_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Default (llama-3.3-70b-versatile)</option>
                                <option value="llama-3.3-70b-versatile" {{ ($settings->groq_model ?? '') == 'llama-3.3-70b-versatile' ? 'selected' : '' }}>Llama 3.3 70B (Best Logic)</option>
                                <option value="llama3-8b-8192" {{ ($settings->groq_model ?? '') == 'llama3-8b-8192' ? 'selected' : '' }}>Llama 3 8B (Super Fast)</option>
                                <option value="mixtral-8x7b-32768" {{ ($settings->groq_model ?? '') == 'mixtral-8x7b-32768' ? 'selected' : '' }}>Mixtral 8x7B (Great Context)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Hugging Face (Inference API) -->
                <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                    <h3 class="font-bold text-gray-700 mb-2">Hugging Face (Inference API)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">API Key</label>
                            <input type="password" name="huggingface_api_key" value="{{ old('huggingface_api_key', $settings->huggingface_api_key ?? '') }}" placeholder="hf_..." class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Model Selection</label>
                            <select name="huggingface_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Qwen/Qwen2.5-72B-Instruct" {{ ($settings->huggingface_model ?? 'Qwen/Qwen2.5-72B-Instruct') == 'Qwen/Qwen2.5-72B-Instruct' ? 'selected' : '' }}>Qwen 2.5 72B (Best Bengali)</option>
                                <option value="meta-llama/Llama-3.3-70B-Instruct" {{ ($settings->huggingface_model ?? '') == 'meta-llama/Llama-3.3-70B-Instruct' ? 'selected' : '' }}>Llama 3.3 70B (Great Logic)</option>
                                <option value="meta-llama/Meta-Llama-3-8B-Instruct" {{ ($settings->huggingface_model ?? '') == 'meta-llama/Meta-Llama-3-8B-Instruct' ? 'selected' : '' }}>Llama 3 8B (Fast)</option>
                                <option value="mistralai/Mistral-Nemo-Instruct-2407" {{ ($settings->huggingface_model ?? '') == 'mistralai/Mistral-Nemo-Instruct-2407' ? 'selected' : '' }}>Mistral Nemo (Smart)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- OpenAI -->
                <div class="bg-white p-4 rounded-lg border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                    <h3 class="font-bold text-gray-700 mb-2">OpenAI (ChatGPT)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">API Key</label>
                            <input type="password" name="openai_api_key" value="{{ old('openai_api_key', $settings->openai_api_key ?? '') }}" placeholder="sk-proj-... (খালি রাখলে .env ব্যবহার হবে)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-600 mb-1">Model Selection</label>
                            <select name="openai_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_wp_laravel'))
        {{-- 🔥 WordPress কানেকশন --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 relative overflow-hidden">
            <div class="absolute top-0 right-0 bg-blue-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg shadow-sm">Required</div>
            
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-xl font-bold text-gray-700 flex items-center gap-2">
                    🔗 WordPress কানেকশন
                </h2>
                <button type="button" onclick="testWordPress()" class="text-xs bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition font-bold border border-gray-300">
                    ⚡ Test Connection
                </button>
            </div>
            
            <p id="wp_status_msg" class="text-xs font-bold mb-4"></p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-1">ওয়েবসাইট লিংক (URL)</label>
                    <input type="url" id="wp_url" name="wp_url" value="{{ old('wp_url', $settings->wp_url ?? '') }}" placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">ইউজারনেম (Username)</label>
                    <input type="text" id="wp_username" name="wp_username" value="{{ old('wp_username', $settings->wp_username ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">App Password</label>
                    <input type="password" id="wp_app_password" name="wp_app_password" value="{{ old('wp_app_password', $settings->wp_app_password ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition" placeholder="abcd efgh ijkl mnop">
                    <p class="text-xs text-gray-500 mt-1">WP Admin > Users > Profile > Application Passwords এ গিয়ে তৈরি করুন।</p>
                </div>
            </div>
        </div>
        
        {{-- 🔥 LARAVEL CONNECTION SECTION --}}
        {{-- UNIVERSAL & CUSTOM WEBSITE CONNECTION SECTION (Laravel / Next.js / Node.js / Custom CMS) --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mt-6 relative overflow-hidden">
            <div class="absolute top-0 right-0 bg-slate-800 text-slate-200 text-[11px] font-semibold px-3 py-1 rounded-bl-lg shadow-sm tracking-wide border-b border-l border-slate-700">REST API / Webhook</div>
            
            <div class="flex flex-wrap justify-between items-center mb-4 border-b pb-3 gap-2">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-plug text-indigo-600 text-lg"></i> Website API Integration <span class="text-xs font-normal text-gray-500">(Laravel / Next.js / Node.js / Custom CMS)</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">আপনার ওয়েবসাইটের সাথে স্বয়ংক্রিয় সংবাদ প্রকাশের সংযোগ কনফিগারেশন।</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openCodeGeneratorModal()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-3.5 py-2 rounded-lg hover:bg-indigo-100 transition shadow-sm">
                        <i class="fas fa-code"></i> Code Generator
                    </button>
                    <a href="{{ route('docs.api-guide') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-300 px-3.5 py-2 rounded-lg hover:bg-slate-200 transition">
                        <i class="fas fa-book-open text-slate-500"></i> Documentation
                    </a>
                </div>
            </div>

            <!-- Connection Status Message Box -->
            <div id="custom_api_status_box" class="hidden mb-4 p-3 rounded-lg text-xs font-bold"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Base / Website URL -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Website Base URL</label>
                    <input type="url" id="laravel_site_url" name="laravel_site_url" value="{{ old('laravel_site_url', $settings->laravel_site_url ?? '') }}" 
                           placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-sm">
                    <p class="text-[11px] text-gray-500 mt-1">আপনার ওয়েবসাইটের মূল ডোমেইন লিংক (যেমন: <code>https://mywebsite.com</code>)।</p>
                </div>

                <!-- API Secret Token -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">API Secret Token</label>
                    <div class="flex gap-2">
                        <input type="text" id="laravel_api_token" name="laravel_api_token" value="{{ old('laravel_api_token', $settings->laravel_api_token ?? '') }}" 
                                class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition font-mono text-sm" placeholder="e.g. sec_token_2026_xyz">
                        <button type="button" onclick="generateRandomToken()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 flex-shrink-0 flex items-center gap-1" title="নতুন সিকিউর টোকেন জেনারেট করুন">
                            <i class="fas fa-sync-alt text-[10px]"></i> Generate
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">সার্ভার হ্যান্ডশেক ও সিকিউরিটি ভেরিফিকেশনে ব্যবহৃত গোপন চাবি।</p>
                </div>

                <!-- Route Prefix -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">News Link Prefix</label>
                    <div class="flex items-center">
                        <span class="bg-gray-100 border border-r-0 border-gray-300 px-3 py-2 rounded-l text-gray-500 text-sm">/</span>
                        <input type="text" name="laravel_route_prefix" value="{{ old('laravel_route_prefix', $settings->laravel_route_prefix ?? 'news') }}" 
                               class="w-full border-gray-300 rounded-r shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-sm" 
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
                            <span class="font-bold text-gray-800 text-sm block">Enable Auto-Publish to Website</span>
                            <span class="text-xs text-gray-500 block">চালু থাকলে সংবাদ অনুমোদনের পর সরাসরি আপনার ওয়েবসাইটে প্রকাশ হবে।</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Test Connection Button -->
            <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                <span class="text-xs text-slate-500">সেটিংস সেভ করার আগে এন্ডপয়েন্ট কানেকশন যাচাই করুন:</span>
                <button type="button" onclick="testCustomApiConnection()" id="btn_test_custom_api" class="inline-flex items-center gap-2 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg transition shadow-sm">
                    <i class="fas fa-plug"></i> Test Connection
                </button>
            </div>

            {{-- ADVANCED CUSTOM FIELD MAPPER --}}
            <div class="mt-6 border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                <div class="bg-slate-100 p-4 border-b border-slate-200 flex justify-between items-center cursor-pointer select-none" onclick="toggleCustomApiVisual()">
                    <div class="flex items-center gap-2">
                        <i id="mapper_chevron" class="fas fa-chevron-down text-slate-500 text-xs transition-transform"></i>
                        <span class="font-bold text-slate-800 text-sm">Advanced Field Mapping & Custom Endpoints</span>
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

                <div id="custom-api-visual-section" class="p-5 space-y-6">
                    <div class="flex flex-wrap justify-between items-center bg-slate-100 border border-slate-200 p-3 rounded-lg text-xs text-slate-700 gap-2">
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
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-lg border border-slate-200">
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
                    <div class="bg-white p-4 rounded-lg border border-slate-200">
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
                    <div class="bg-white p-4 rounded-lg border border-slate-200">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Additional Static Parameters (ঐচ্ছিক ফিল্ড)</h4>
                            <button type="button" onclick="addExtraFieldRow()" class="text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-300">
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
                            <button type="button" onclick="toggleRawJson()" class="text-[11px] text-indigo-600 hover:underline">View / Edit JSON</button>
                        </div>
                        <textarea id="custom_api_mapping" name="custom_api_mapping" rows="3" 
                                  class="w-full border-slate-300 rounded-lg shadow-sm text-xs font-mono focus:ring-indigo-500 focus:border-indigo-500 bg-slate-100 hidden" 
                                  placeholder='{"title":"title","content":"content"}'>{{ old('custom_api_mapping', is_array($settings->custom_api_mapping) ? json_encode($settings->custom_api_mapping) : ($settings->custom_api_mapping ?? '')) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

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
                    <button type="button" onclick="closeAssistantHelpModal()" class="text-slate-400 hover:text-white text-xl p-2 rounded-lg hover:bg-slate-700">
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
                                ভিজ্যুয়াল ফিল্ড ম্যাপিং (Field Mapping)
                            </h4>
                            <p class="text-xs text-slate-300 leading-relaxed mb-3">
                                আপনার সাইটের এপিআই ফিল্ডের নাম যদি আমাদের স্ট্যান্ডার্ড নামের চেয়ে আলাদা হয়, তবে ম্যাপিং বক্সে প্যারামিটার নাম লিখে দিন:
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                                    <span class="text-indigo-400 font-semibold block mb-1">Title (শিরোনাম):</span>
                                    <span class="text-slate-400">আপনার সাইটের ফিল্ডের নাম <code class="text-slate-200">headline</code> বা <code class="text-slate-200">news_title</code> হলে তা লিখে দিন।</span>
                                </div>
                                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                                    <span class="text-indigo-400 font-semibold block mb-1">Content (সংবাদ বিস্তারিত):</span>
                                    <span class="text-slate-400">আপনার সাইটের ফিল্ডের নাম <code class="text-slate-200">body</code>, <code class="text-slate-200">description</code> বা <code class="text-slate-200">details</code> হলে তা লিখুন।</span>
                                </div>
                                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                                    <span class="text-indigo-400 font-semibold block mb-1">Image Format (ছবির ফরম্যাট):</span>
                                    <span class="text-slate-400">Image URL String, Direct Multipart File Upload, অথবা Base64 Data URI সিলেক্ট করুন।</span>
                                </div>
                                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                                    <span class="text-indigo-400 font-semibold block mb-1">Category (ক্যাটাগরি ফরম্যাট):</span>
                                    <span class="text-slate-400">Numeric Category ID (যেমন <code class="text-slate-200">[1, 5]</code>) নাকি Text Category Name (যেমন <code class="text-slate-200">"জাতীয়"</code>) তা বেছে নিন।</span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4 -->
                        <div class="bg-slate-800/80 p-5 rounded-xl border border-slate-700">
                            <h4 class="text-base font-bold text-white mb-2 flex items-center gap-2">
                                <span class="w-6 h-6 rounded bg-indigo-600 text-white text-xs flex items-center justify-center font-bold">4</span>
                                অতিরিক্ত ফিক্সড প্যারামিটার যোগ করা
                            </h4>
                            <p class="text-xs text-slate-300 leading-relaxed">
                                আপনার সাইটের ডাটাবেজে যদি অতিরিক্ত কিছু বাধ্যতামূলক ফিল্ড থাকে (যেমন: <code class="text-indigo-300 font-mono">author_id = 1</code>, <code class="text-indigo-300 font-mono">status = published</code>, <code class="text-indigo-300 font-mono">language = bn</code>), 
                                তবে <strong>+ Add Parameter</strong> বাটনে ক্লিক করে Key ও Value বসিয়ে দিন। সিস্টেম প্রতিবার পোস্ট পাঠানোর সময় স্বয়ংক্রিয়ভাবে এগুলো যুক্ত করে পাঠাবে।
                            </p>
                        </div>
                    </div>

                    <!-- 2. FAQ CONTENT -->
                    <div id="help_content_faq" class="space-y-3 hidden">
                        <!-- FAQ 1 -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                            <button type="button" onclick="toggleFaqAccordion(1)" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center hover:bg-slate-750">
                                <span>১. আমাদের ফিল্ডের চেয়ে ক্লায়েন্টের ফিল্ড কম বা বেশি হলে কীভাবে হ্যান্ডেল হবে?</span>
                                <i id="faq_icon_1" class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
                            </button>
                            <div id="faq_body_1" class="p-4 pt-0 text-xs text-slate-300 leading-relaxed border-t border-slate-700/50 hidden bg-slate-850">
                                যদি ক্লায়েন্টের ফিল্ড <strong>কম</strong> হয়, তবে ম্যাপিং বক্সে অপ্রয়োজনীয় ফিল্ডগুলো খালি রেখে দিন। আর যদি ক্লায়েন্টের ফিল্ড <strong>অতিরিক্ত</strong> হয়, তবে <strong>+ Add Parameter</strong> বাটনে চাপ দিয়ে অতিরিক্ত ফিল্ডগুলো (যেমন: author_id, post_type ইত্যাদি) যুক্ত করে নিতে পারবেন।
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                            <button type="button" onclick="toggleFaqAccordion(2)" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center hover:bg-slate-750">
                                <span>২. ক্লায়েন্টের সাইটে ক্যাটাগরি গ্রুপ বা সাব-ক্যাটাগরি (Parent > Child) আকারে থাকলে কীভাবে কাজ করবে?</span>
                                <i id="faq_icon_2" class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
                            </button>
                            <div id="faq_body_2" class="p-4 pt-0 text-xs text-slate-300 leading-relaxed border-t border-slate-700/50 hidden bg-slate-850">
                                ক্যাটাগরি ফেচ করার সময় আমাদের সিস্টেম ক্লায়েন্টের সাইট থেকে সব প্যারেন্ট ও সাব-ক্যাটাগরি লোড করে নেয় (যেমন: <code class="text-indigo-300">খেলাধুলা > ক্রিকেট (ID: 12)</code>)। ক্যাটাগরি ম্যাপিং সেকশনের ড্রপডাউন থেকে নির্দিষ্ট সাব-ক্যাটাগরিটি সিলেক্ট করে দিলে সরাসরি সঠিক আইডিতে পোস্ট চলে যাবে।
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                            <button type="button" onclick="toggleFaqAccordion(3)" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center hover:bg-slate-750">
                                <span>৩. ক্লায়েন্টের API কি ইমেজ লিঙ্ক চায় নাকি সরাসরি ফাইল আপলোড?</span>
                                <i id="faq_icon_3" class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
                            </button>
                            <div id="faq_body_3" class="p-4 pt-0 text-xs text-slate-300 leading-relaxed border-t border-slate-700/50 hidden bg-slate-850">
                                আমাদের সিস্টেম দুটিই সাপোর্ট করে। Visual Mapper-এর <strong>Image Format</strong> ড্রপডাউন থেকে:
                                <ul class="list-disc list-inside mt-2 space-y-1">
                                    <li><strong>Image URL String:</strong> ক্লায়েন্ট সার্ভার যদি ছবির URL রিসিভ করে নিজে ডাউনলোড করে নেয়।</li>
                                    <li><strong>Direct Binary File Upload:</strong> ক্লায়েন্ট সার্ভার যদি Multipart ফাইলে ইমেজ আপলোড হিসেবে চায়।</li>
                                    <li><strong>Base64 Data:</strong> ক্লায়েন্ট যদি Base64 এনকোডেড স্ট্রিং চায়।</li>
                                </ul>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                            <button type="button" onclick="toggleFaqAccordion(4)" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center hover:bg-slate-750">
                                <span>৪. Cloudflare বা WAF 403 Forbidden এরর দিলে কী করণীয়?</span>
                                <i id="faq_icon_4" class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
                            </button>
                            <div id="faq_body_4" class="p-4 pt-0 text-xs text-slate-300 leading-relaxed border-t border-slate-700/50 hidden bg-slate-850">
                                আমাদের রিকোয়েস্টে স্ট্যান্ডার্ড <code class="text-indigo-300">User-Agent: Subeditor24-Publisher/2.0</code> পাঠানো হয়। এরপরও যদি ক্লায়েন্টের Cloudflare 'Under Attack Mode' এ থাকে, তবে ক্লায়েন্টকে তাদের Cloudflare WAF Security Rules-এ গিয়ে আমাদের সার্ভার আইপি (Server IP) বা <code class="text-indigo-300">/api/external-news-post</code> পাথটিকে Whitelist / Skip WAF করে দিতে বলুন।
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                            <button type="button" onclick="toggleFaqAccordion(5)" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center hover:bg-slate-750">
                                <span>৫. একই সংবাদ কি ক্লায়েন্টের সাইটে ডুপ্লিকেট পোস্ট হতে পারে?</span>
                                <i id="faq_icon_5" class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
                            </button>
                            <div id="faq_body_5" class="p-4 pt-0 text-xs text-slate-300 leading-relaxed border-t border-slate-700/50 hidden bg-slate-850">
                                না। একবার পোস্ট সফল হলে ক্লায়েন্ট সাইট থেকে রিটার্ন করা <code class="text-indigo-300">post_id</code> আমাদের ডাটাবেজে সংরক্ষিত থাকে। পরবর্তীতে সংবাদটি ড্রাফট থেকে আপডেট করা হলে বা পুনরায় পাবলিশ করা হলে সিস্টেম নতুন পোস্ট তৈরি না করে ক্লায়েন্টের সাইটে <code class="text-indigo-300">remote_id</code> সহ আপডেট মোডে ডাটা পাঠায়।
                            </div>
                        </div>

                        <!-- FAQ 6 -->
                        <div class="bg-slate-800 rounded-xl border border-slate-700 overflow-hidden">
                            <button type="button" onclick="toggleFaqAccordion(6)" class="w-full p-4 text-left font-bold text-sm text-white flex justify-between items-center hover:bg-slate-750">
                                <span>৬. Bearer Token বনাম Custom Header—কোনটি ব্যবহার করব?</span>
                                <i id="faq_icon_6" class="fas fa-chevron-down text-slate-400 text-xs transition-transform"></i>
                            </button>
                            <div id="faq_body_6" class="p-4 pt-0 text-xs text-slate-300 leading-relaxed border-t border-slate-700/50 hidden bg-slate-850">
                                যদি আপনার ক্লায়েন্ট ফ্রেমওয়ার্ক স্ট্যান্ডার্ড JWT বা Bearer অথেন্টিকেশন ব্যবহার করে, তবে <strong>Bearer Token</strong> বেছে নিন। আর যদি তারা নিজস্ব হেডার চায় (যেমন: <code class="text-indigo-300">X-API-KEY: secret</code> বা <code class="text-indigo-300">X-Auth-Token</code>), তবে <strong>Custom Header</strong> সিলেক্ট করে হেডার নামটি লিখে দিন।
                            </div>
                        </div>
                    </div>

                    <!-- 3. DIAGNOSTICS & TROUBLESHOOTING -->
                    <div id="help_content_diagnostics" class="space-y-4 hidden">
                        <p class="text-xs text-slate-400">Test Connection চালানোর পর যদি কোনো এরর কোড আসে, তবে নিচের সমাধানগুলো অনুসরণ করুন:</p>

                        <div class="space-y-3 text-xs">
                            <div class="p-4 rounded-xl bg-slate-800/90 border border-slate-700">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-rose-900/60 text-rose-300 font-mono font-bold px-2 py-0.5 rounded text-[11px]">401 Unauthorized</span>
                                    <span class="font-semibold text-slate-200">টোকেন অমিল বা অনুমোদন ব্যর্থ</span>
                                </div>
                                <p class="text-slate-300"><strong>কারণ:</strong> API Secret Token অমিল বা ভুল।</p>
                                <p class="text-slate-400 mt-1"><strong>সমাধান:</strong> আমাদের প্যানেলের <code>API Secret Token</code> এবং আপনার সাইটের কোডে বসানো টোকেন হুবহু এক কিনা যাচাই করুন।</p>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-800/90 border border-slate-700">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-amber-900/60 text-amber-300 font-mono font-bold px-2 py-0.5 rounded text-[11px]">404 Not Found</span>
                                    <span class="font-semibold text-slate-200">এন্ডপয়েন্ট বা রাউট খুঁজে পাওয়া যায়নি</span>
                                </div>
                                <p class="text-slate-300"><strong>কারণ:</strong> এপিআই এন্ডপয়েন্ট বা রাউট ইউআরএল ভুল।</p>
                                <p class="text-slate-400 mt-1"><strong>সমাধান:</strong> Base URL সঠিক কিনা এবং আপনার সাইটের রাউটে <code>/api/external-news-post</code> রেজিস্টার্ড আছে কিনা চেক করুন।</p>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-800/90 border border-slate-700">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-purple-900/60 text-purple-300 font-mono font-bold px-2 py-0.5 rounded text-[11px]">422 Validation Error</span>
                                    <span class="font-semibold text-slate-200">ডাটা ভ্যালিডেশন ব্যর্থ</span>
                                </div>
                                <p class="text-slate-300"><strong>কারণ:</strong> ক্লায়েন্ট সার্ভারের রিকোয়ার্ড ফিল্ডের সাথে আমাদের পাঠানো ফিল্ডের নাম মিলছে না।</p>
                                <p class="text-slate-400 mt-1"><strong>সমাধান:</strong> Field Mapping সেকশনে Title, Content, Category ইত্যাদির ফিল্ডের নামগুলো ক্লায়েন্টের সাইটের মডেল বা ডাটাবেজ কলাম অনুযায়ী সঠিক করুন।</p>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-800/90 border border-slate-700">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="bg-red-900/60 text-red-300 font-mono font-bold px-2 py-0.5 rounded text-[11px]">500 Internal Server Error</span>
                                    <span class="font-semibold text-slate-200">রিমোট সার্ভার ক্র্যাশ</span>
                                </div>
                                <p class="text-slate-300"><strong>কারণ:</strong> ক্লায়েন্টের সাইটে ডাটাবেজ ইনসার্ট বা কোড এক্সিকিউশনে ইন্টারনাল ক্র্যাশ হয়েছে।</p>
                                <p class="text-slate-400 mt-1"><strong>সমাধান:</strong> ক্লায়েন্ট সাইটের সার্ভার এরর লগ (<code class="text-slate-200">storage/logs/laravel.log</code> বা <code class="text-slate-200">error_log</code>) চেক করুন।</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-800 border-t border-slate-700 flex justify-between items-center text-xs text-slate-400">
                    <span>প্রয়োজনে আমাদের সাপোর্ট টিমের সাথে যোগাযোগ করতে পারেন।</span>
                    <button type="button" onclick="closeAssistantHelpModal()" class="px-5 py-2 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition">
                        বন্ধ করুন (Close)
                    </button>
                </div>
            </div>
        </div>

        {{-- QUICK CODE GENERATOR MODAL --}}
        <div id="codeGeneratorModal" class="fixed inset-0 z-50 bg-black bg-opacity-70 flex items-center justify-center p-4 hidden backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden text-slate-100">
                <!-- Modal Header -->
                <div class="p-5 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg">
                            <i class="fas fa-code"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">Endpoint Code Generator</h3>
                            <p class="text-xs text-slate-400">আপনার ফ্রেমওয়ার্ক সিলেক্ট করে রেডিমেড কোড কপি করে প্রজেক্টে যুক্ত করুন।</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCodeGeneratorModal()" class="text-slate-400 hover:text-white text-xl p-2 rounded-lg hover:bg-slate-700">
                        &times;
                    </button>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-800 bg-slate-950 px-5 gap-2 overflow-x-auto text-xs font-bold">
                    <button type="button" onclick="switchCodeGenTab('nextjs_app')" class="code-tab-btn px-4 py-3 border-b-2 border-indigo-500 text-indigo-400" id="tab_nextjs_app">Next.js (App Router)</button>
                    <button type="button" onclick="switchCodeGenTab('nextjs_pages')" class="code-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200" id="tab_nextjs_pages">Next.js (Pages)</button>
                    <button type="button" onclick="switchCodeGenTab('nodejs_express')" class="code-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200" id="tab_nodejs_express">Node.js (Express)</button>
                    <button type="button" onclick="switchCodeGenTab('laravel')" class="code-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200" id="tab_laravel">Laravel API</button>
                    <button type="button" onclick="switchCodeGenTab('raw_php')" class="code-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200" id="tab_raw_php">Raw PHP (1-File)</button>
                    <button type="button" onclick="switchCodeGenTab('python')" class="code-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200" id="tab_python">Python (FastAPI)</button>
                </div>

                <!-- Code Container -->
                <div class="p-6 overflow-y-auto flex-1 bg-slate-900 space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span id="code_file_path" class="font-mono text-indigo-300 font-semibold bg-slate-800 px-3 py-1 rounded-md border border-slate-700">app/api/external-news-post/route.ts</span>
                        <button type="button" onclick="copyGeneratedCode()" id="btn_copy_code" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1.5 transition shadow">
                            <i class="far fa-copy"></i> Copy Code
                        </button>
                    </div>

                    <pre class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-xs font-mono text-slate-200 overflow-x-auto"><code id="code_snippet_box"></code></pre>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-800 border-t border-slate-700 flex justify-between items-center text-xs text-slate-400">
                    <span>Secret Token স্বয়ংক্রিয়ভাবে কোডে যুক্ত করা হয়েছে।</span>
                    <button type="button" onclick="closeCodeGeneratorModal()" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
        @endif
        
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_social'))
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            {{-- Facebook --}}
            <div class="bg-white p-5 rounded-lg shadow border border-blue-100">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-lg text-blue-700 flex items-center gap-2">
                        <i class="fab fa-facebook"></i> Facebook Pages Connection
                    </h3>
                </div>
                
                <div class="mb-4 bg-blue-50 p-3 rounded border border-blue-100">
                    <label class="block text-sm font-bold text-blue-800 mb-2">Connect New Page</label>
                    <div class="space-y-2">
                        <input type="text" id="new_fb_page_id" class="w-full border p-2 rounded text-sm bg-white" placeholder="Page ID (e.g., 100089...)">
                        <input type="text" id="new_fb_page_name" class="w-full border p-2 rounded text-sm bg-white" placeholder="Page Name (e.g., Daily News)">
                        <textarea id="new_fb_access_token" rows="2" class="w-full border p-2 rounded text-sm bg-white" placeholder="Page Access Token..."></textarea>
                        <label class="flex items-center gap-2 cursor-pointer text-[12px] font-bold text-gray-700">
                            <input type="checkbox" id="new_fb_comment_link" class="w-4 h-4 text-blue-600 rounded">
                            Share Post Link in Comment (Instead of Caption)
                        </label>
                        <button type="button" onclick="saveNewFbPage(this)" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700 transition shadow-sm">
                            + Add Page
                        </button>
                    </div>
                </div>

                <div class="">
                    <label class="block text-sm font-bold text-gray-700 mb-2 border-b pb-1">Connected Pages</label>
                    <div id="fb_pages_list" class="space-y-2 max-h-[300px] overflow-y-auto pr-1">
                        @forelse($fbPages as $fbPage)
                            <div class="fb-page-row flex items-center justify-between p-2 border rounded {{ $fbPage->is_active ? 'bg-white border-gray-200' : 'bg-red-50 border-red-200' }}" data-id="{{ $fbPage->id }}">
                                <div class="flex-1 min-w-0 pr-2">
                                    <p class="font-bold text-gray-800 text-sm truncate flex items-center gap-2">
                                        {{ $fbPage->page_name }}
                                        @if($fbPage->is_studio_default)
                                            <span class="text-[10px] px-1.5 py-0.5 bg-blue-100 text-blue-700 rounded border border-blue-200" title="Studio Modal এ ডিফল্ট হিসেবে সিলেক্ট থাকবে">Default</span>
                                        @endif
                                    </p>
                                    <p class="text-[11px] text-gray-500 font-mono">ID: {{ $fbPage->page_id }}</p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <button type="button" onclick="editPage({{ $fbPage->id }}, '{{ addslashes($fbPage->page_name) }}')" class="text-[11px] bg-gray-50 text-gray-700 px-2 py-0.5 rounded hover:bg-gray-100 font-bold border border-gray-200">
                                            ✏️ Edit
                                        </button>
                                        <button type="button" onclick="testSavedPage({{ $fbPage->id }}, this)" class="text-[11px] bg-blue-50 text-blue-700 px-2 py-0.5 rounded hover:bg-blue-100 font-bold border border-blue-200">
                                            ⚡ Test
                                        </button>
                                        <button type="button" onclick="toggleCommentLink({{ $fbPage->id }}, this)" class="text-[11px] px-2 py-0.5 rounded font-bold border {{ $fbPage->comment_link ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-gray-50 text-gray-600 border-gray-200' }}">
                                            {{ $fbPage->comment_link ? '💬 Comment Link (On)' : '💬 Comment Link (Off)' }}
                                        </button>
                                        <button type="button" onclick="togglePage({{ $fbPage->id }}, this)" class="text-[11px] px-2 py-0.5 rounded font-bold border {{ $fbPage->is_active ? 'bg-yellow-50 text-yellow-700 border-yellow-200' : 'bg-green-50 text-green-700 border-green-200' }}">
                                            {{ $fbPage->is_active ? '⏸ Pause' : '▶️ Resume' }}
                                        </button>
                                        <button type="button" onclick="toggleDefaultPage({{ $fbPage->id }}, this)" class="text-[11px] px-2 py-0.5 rounded font-bold border hover:bg-gray-100 transition {{ $fbPage->is_studio_default ? 'text-gray-400 border-gray-200 bg-gray-50 cursor-not-allowed' : 'text-blue-600 border-blue-200 bg-white shadow-sm' }}" {{ $fbPage->is_studio_default ? 'disabled' : '' }}>
                                            {{ $fbPage->is_studio_default ? '✅ Default' : '⭐ Set Default' }}
                                        </button>
                                        <span class="status-msg ml-auto"></span>
                                    </div>
                                </div>
                                <button type="button" onclick="deletePage({{ $fbPage->id }}, this)" class="text-xs text-red-400 hover:text-red-700 px-2 py-2 rounded hover:bg-red-50 transition" title="Delete Page">
                                    🗑️
                                </button>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 text-center py-4 bg-gray-50 rounded border border-dashed">No pages connected yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <script>
            function saveNewFbPage(btn) {
                const id = document.getElementById('new_fb_page_id').value.trim();
                const name = document.getElementById('new_fb_page_name').value.trim();
                const token = document.getElementById('new_fb_access_token').value.trim();
                const commentLink = document.getElementById('new_fb_comment_link').checked;
                if(!id || !token || !name) { alert("Page ID, Name, and Token are required"); return; }
                
                btn.innerText = 'Saving...'; btn.disabled = true;
                fetch('{{ route("fb-pages.store") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ page_id: id, page_name: name, access_token: token, comment_link: commentLink })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) { location.reload(); } else { alert(d.message || "Failed to add page"); btn.innerText = '+ Add Page'; btn.disabled = false; }
                }).catch(() => { alert("Error connecting to server"); btn.innerText = '+ Add Page'; btn.disabled = false; });
            }

            function testSavedPage(id, btn) {
                const orig = btn.innerText; btn.innerText = 'Testing...'; btn.disabled = true;
                const statusSpan = btn.parentElement.querySelector('.status-msg');
                statusSpan.innerHTML = '<span class="text-[10px] text-gray-500">Wait...</span>';
                
                fetch(`/facebook-pages/${id}/test`, { method: 'POST', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }})
                .then(r => r.json())
                .then(d => {
                    statusSpan.innerHTML = `<span class="text-[10px] font-bold ${d.success ? 'text-green-600' : 'text-red-600'}">${d.message}</span>`;
                }).catch(e => { statusSpan.innerHTML = '<span class="text-[10px] text-red-600">Error</span>'; }).finally(() => { btn.innerText = orig; btn.disabled = false; });
            }

            function togglePage(id, btn) {
                btn.disabled = true;
                fetch(`/facebook-pages/${id}/toggle`, { method: 'PATCH', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }})
                .then(r => r.json())
                .then(d => { if (d.success) location.reload(); else alert(d.message || "Failed."); })
                .catch(e => { alert("Error"); btn.disabled = false; });
            }

            function toggleCommentLink(id, btn) {
                btn.disabled = true;
                fetch(`/facebook-pages/${id}/toggle-comment`, { method: 'PATCH', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }})
                .then(r => r.json())
                .then(d => { if (d.success) location.reload(); else alert(d.message || "Failed."); })
                .catch(e => { alert("Error"); btn.disabled = false; });
            }
            
            function toggleDefaultPage(id, btn) {
                btn.disabled = true;
                fetch(`/facebook-pages/${id}/toggle-default`, { method: 'PATCH', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }})
                .then(r => r.json())
                .then(d => { if (d.success) location.reload(); else alert(d.message || "Failed."); })
                .catch(e => { alert("Error"); btn.disabled = false; });
            }

            function deletePage(id, btn) {
                if(!confirm('Are you sure you want to delete this page?')) return;
                fetch(`/facebook-pages/${id}`, { method: 'DELETE', headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }})
                .then(r => r.json())
                .then(d => {
                    if (d.success) btn.closest('.fb-page-row').remove();
                    else alert(d.message || "Failed to delete.");
                })
                .catch(e => { alert("Network Error or Unauthorized"); });
            }

            function editPage(id, currentName) {
                const newName = prompt("Update Page Name:", currentName);
                if (newName === null) return;
                const newToken = prompt("Update Access Token (required):");
                if (!newToken) {
                    alert('Token is required to update.');
                    return;
                }
                
                fetch(`/facebook-pages/${id}`, {
                    method: 'PUT',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ page_name: newName, access_token: newToken })
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) { alert(d.message); location.reload(); }
                    else { alert(d.message || "Failed to update page"); }
                }).catch(e => alert("Error updating page."));
            }

            function testTwitter() {
                const apiKey = document.getElementById('twitter_api_key').value.trim();
                const apiSecret = document.getElementById('twitter_api_secret').value.trim();
                const accessToken = document.getElementById('twitter_access_token').value.trim();
                const accessSecret = document.getElementById('twitter_access_secret').value.trim();
                const msg = document.getElementById('twitter_status_msg');

                if(!apiKey || !apiSecret || !accessToken || !accessSecret) {
                    msg.innerHTML = '<span class="text-red-500">সবগুলো Keys এবং Tokens দিন।</span>';
                    return;
                }

                msg.innerHTML = '<span class="text-blue-500">টেস্টিং চলছে...</span>';
                
                fetch('{{ route("settings.test-twitter") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ 
                        twitter_api_key: apiKey, 
                        twitter_api_secret: apiSecret, 
                        twitter_access_token: accessToken, 
                        twitter_access_secret: accessSecret 
                    })
                })
                .then(r => r.json())
                .then(d => {
                    msg.innerHTML = `<span class="${d.success ? 'text-green-600' : 'text-red-600'}">${d.message.replace(/\n/g, '<br>')}</span>`;
                }).catch(() => msg.innerHTML = '<span class="text-red-500">Network Error!</span>');
            }
            </script>
            
            {{-- Telegram --}}
            <div class="bg-white p-5 rounded-lg shadow border border-sky-100">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-lg text-sky-600 flex items-center gap-2">
                        <i class="fab fa-telegram"></i> Telegram Channel
                    </h3>
                    <button type="button" onclick="testTelegram()" class="text-xs bg-sky-100 text-sky-700 px-3 py-1 rounded hover:bg-sky-200 transition font-bold border border-sky-200">
                        ⚡ Test Connection
                    </button>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700">Bot Token</label>
                    <input type="text" id="telegram_bot_token" name="telegram_bot_token" value="{{ $settings->telegram_bot_token ?? '' }}" 
                           class="w-full border p-2 rounded text-sm" placeholder="Ex: 123456:ABC-DEF...">
                    <p class="text-[10px] text-gray-400">BotFather থেকে পাওয়া টোকেন দিন।</p>
                </div>

                <div class="mb-3">
                    <label class="block text-sm font-bold text-gray-700">Channel ID</label>
                    <input type="text" id="telegram_channel_id" name="telegram_channel_id" value="{{ $settings->telegram_channel_id ?? '' }}" 
                           class="w-full border p-2 rounded text-sm" placeholder="Ex: -100123456789">
                    
                    <p id="tg_status_msg" class="text-xs mt-2 font-bold"></p>
                    
                    <p class="text-[10px] text-gray-400 mt-1">বটকে চ্যানেলের অ্যাডমিন করতে ভুলবেন না।</p>
                </div>
            </div>

            {{-- Twitter / X --}}
            <div class="bg-white p-5 rounded-lg shadow border border-slate-200">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                        🐦 Twitter / X
                    </h3>
                    <button type="button" onclick="testTwitter()" class="text-xs bg-slate-100 text-slate-700 px-3 py-1 rounded hover:bg-slate-200 transition font-bold border border-slate-200">
                        ⚡ Test Connection
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">API Key</label>
                        <input type="text" id="twitter_api_key" name="twitter_api_key" value="{{ $settings->twitter_api_key ?? '' }}" 
                               class="w-full border p-2 rounded text-sm placeholder-gray-300" placeholder="ex: abc123def...">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">API Key Secret</label>
                        <input type="text" id="twitter_api_secret" name="twitter_api_secret" value="{{ $settings->twitter_api_secret ?? '' }}" 
                               class="w-full border p-2 rounded text-sm placeholder-gray-300" placeholder="ex: abc123def...">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Access Token</label>
                        <input type="text" id="twitter_access_token" name="twitter_access_token" value="{{ $settings->twitter_access_token ?? '' }}" 
                               class="w-full border p-2 rounded text-sm placeholder-gray-300" placeholder="ex: 12345-abc...">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Access Token Secret</label>
                        <input type="text" id="twitter_access_secret" name="twitter_access_secret" value="{{ $settings->twitter_access_secret ?? '' }}" 
                               class="w-full border p-2 rounded text-sm placeholder-gray-300" placeholder="ex: abc123def...">
                    </div>
                </div>
                <p id="twitter_status_msg" class="text-xs mt-3 font-bold"></p>
                <p class="text-[10px] text-gray-400 mt-1">Twitter Developer Portal থেকে প্রাপ্ত Keys এবং Tokens (OAuth 1.0a User Context) দিন।</p>
            </div>
        </div>
        
        <div class="mt-4 bg-white p-4 rounded shadow">
            <h3 class="font-bold mb-3">Auto Post Preferences</h3>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="post_to_fb" value="0">
                    <input type="checkbox" name="post_to_fb" value="1" {{ $settings->post_to_fb ? 'checked' : '' }} class="toggle-checkbox">
                    <span>Facebook</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="post_to_telegram" value="0">
                    <input type="checkbox" name="post_to_telegram" value="1" {{ $settings->post_to_telegram ? 'checked' : '' }} class="toggle-checkbox">
                    <span>Telegram</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="post_to_twitter" value="0">
                    <input type="checkbox" name="post_to_twitter" value="1" {{ $settings->post_to_twitter ? 'checked' : '' }} class="toggle-checkbox">
                    <span>Twitter/X</span>
                </label>
            </div>
        </div>
        @endif
                
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_category'))
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="text-xl font-bold text-gray-700 flex items-center gap-2">
                    📂 ক্যাটাগরি ম্যাপিং
                </h2>
                <button type="button" id="refresh-cat-btn" onclick="fetchWPCategories(true)" class="bg-indigo-50 text-indigo-700 px-3 py-1.5 rounded-lg text-sm font-bold hover:bg-indigo-100 border border-indigo-200 transition flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh Categories
                </button>
            </div>

            <p class="text-sm text-gray-500 mb-6 bg-blue-50 p-3 rounded border border-blue-100">
                💡 বাম পাশে আমাদের ক্যাটাগরি এবং ডান পাশে আপনার ওয়েবসাইটের ক্যাটাগরি সিলেক্ট করুন। যাতে নিউজ সঠিক জায়গায় পোস্ট হয়।
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
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
                    <div class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded transition">
                        <span class="w-1/3 text-sm font-bold text-gray-700">{{ $cat }}</span>
                        <div class="w-2/3 relative">
                            <select name="category_mapping[{{ $cat }}]" class="wp-cat-selector w-full border-gray-300 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select WP Category</option>
                            </select>
                            <input type="hidden" class="saved-val" value="{{ $savedMapping[$cat] ?? '' }}">
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
            <h2 class="text-xl font-bold text-gray-700 mb-4 border-b pb-2 flex items-center gap-2">
                ✈️ টেলিগ্রাম নোটিফিকেশন
            </h2>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">চ্যানেল আইডি (Channel ID)</label>
                <input type="text" name="telegram_channel_id" value="{{ old('telegram_channel_id', $settings->telegram_channel_id ?? '') }}" placeholder="-100xxxxxxxxxx" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition">
                <p class="text-xs text-gray-500 mt-1">আপনার বটকে চ্যানেলে এডমিন করুন এবং চ্যানেল আইডি দিন।</p>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-8 py-3 rounded-xl font-bold text-lg hover:shadow-lg transition transform hover:-translate-y-1 flex items-center gap-2">
                💾 সেটিংস সেভ করুন
            </button>
        </div>
    </form>
</div>

<script>
    // Toggle Custom API Section
    function toggleCustomApi() {
        const section = document.getElementById('custom-api-section');
        if (section.style.display === 'none') {
            section.style.display = 'grid';
        } else {
            section.style.display = 'none';
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
                // Auto-expand the custom API section
                const section = document.getElementById('custom-api-section');
                if (section) section.style.display = 'grid';
                // Scroll to it
                setTimeout(() => {
                    section?.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

        // যদি forceRefresh true হয়, তবে URL-এ refresh=1 যোগ হবে
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

    // ৩. কানেকশন টেস্ট ফাংশনগুলো (WordPress, FB, Telegram)
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
            statusMsg.className = data.success ? "text-xs font-bold mt-2 text-green-600" : "text-xs font-bold mt-2 text-red-600";
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
        const rawJsonBox = document.getElementById('custom_api_mapping');
        rawJsonBox.classList.toggle('hidden');
    }

    function initVisualMappingFromStoredJson() {
        const rawJson = document.getElementById('custom_api_mapping').value;
        if (!rawJson || rawJson.trim() === '') return;

        try {
            const data = JSON.parse(rawJson);
            if (data.auth_type) document.getElementById('v_auth_type').value = data.auth_type;
            if (data.auth_header_name) document.getElementById('v_auth_header_name').value = data.auth_header_name;
            if (data.image_format) document.getElementById('v_image_format').value = data.image_format;
            if (data.category_type) document.getElementById('v_category_type').value = data.category_type;

            if (data.title) document.getElementById('v_field_title').value = data.title;
            if (data.content) document.getElementById('v_field_content').value = data.content;
            if (data.image) document.getElementById('v_field_image').value = data.image;
            if (data.category) document.getElementById('v_field_category').value = data.category;
            if (data.tags) document.getElementById('v_field_tags').value = data.tags;
            if (data.slug) document.getElementById('v_field_slug').value = data.slug;
            if (data.response_id_key) document.getElementById('v_field_response_id_key').value = data.response_id_key;
            if (data.response_url_key) document.getElementById('v_field_response_url_key').value = data.response_url_key;

            const extraContainer = document.getElementById('extra_fields_container');
            extraContainer.innerHTML = '';
            if (data.extra && typeof data.extra === 'object') {
                for (const [key, val] of Object.entries(data.extra)) {
                    addExtraFieldRow(key, val);
                }
            }

            if (data.auth_type === 'custom_header') {
                document.getElementById('v_auth_header_wrapper').style.display = 'block';
            }
        } catch (e) {
            console.warn('Mapping JSON parse error:', e);
        }
    }

    function syncVisualToMappingJson() {
        const authType = document.getElementById('v_auth_type').value;
        const authHeaderName = document.getElementById('v_auth_header_name').value.trim();
        const imageFormat = document.getElementById('v_image_format').value;
        const categoryType = document.getElementById('v_category_type').value;

        const authHeaderWrap = document.getElementById('v_auth_header_wrapper');
        if (authType === 'custom_header') {
            authHeaderWrap.style.display = 'block';
        } else {
            authHeaderWrap.style.display = 'none';
        }

        const mapping = {};
        if (authType !== 'Bearer') mapping.auth_type = authType;
        if (authType === 'custom_header' && authHeaderName) mapping.auth_header_name = authHeaderName;
        if (imageFormat !== 'url') mapping.image_format = imageFormat;
        if (categoryType !== 'id') mapping.category_type = categoryType;

        const titleVal = document.getElementById('v_field_title').value.trim();
        const contentVal = document.getElementById('v_field_content').value.trim();
        const imageVal = document.getElementById('v_field_image').value.trim();
        const categoryVal = document.getElementById('v_field_category').value.trim();
        const tagsVal = document.getElementById('v_field_tags').value.trim();
        const slugVal = document.getElementById('v_field_slug').value.trim();
        const respIdVal = document.getElementById('v_field_response_id_key').value.trim();
        const respUrlVal = document.getElementById('v_field_response_url_key').value.trim();

        if (titleVal) mapping.title = titleVal;
        if (contentVal) mapping.content = contentVal;
        if (imageVal) mapping.image = imageVal;
        if (categoryVal) mapping.category = categoryVal;
        if (tagsVal) mapping.tags = tagsVal;
        if (slugVal) mapping.slug = slugVal;
        if (respIdVal) mapping.response_id_key = respIdVal;
        if (respUrlVal) mapping.response_url_key = respUrlVal;

        const extraKeys = document.querySelectorAll('.extra-field-key');
        const extraVals = document.querySelectorAll('.extra-field-val');
        const extraObj = {};
        extraKeys.forEach((keyInput, i) => {
            const k = keyInput.value.trim();
            const v = extraVals[i] ? extraVals[i].value.trim() : '';
            if (k) extraObj[k] = v;
        });

        if (Object.keys(extraObj).length > 0) {
            mapping.extra = extraObj;
        }

        document.getElementById('custom_api_mapping').value = Object.keys(mapping).length > 0 ? JSON.stringify(mapping) : '';
    }

    function addExtraFieldRow(key = '', val = '') {
        const container = document.getElementById('extra_fields_container');
        const row = document.createElement('div');
        row.className = 'flex items-center gap-2 extra-field-row';
        row.innerHTML = `
            <input type="text" class="extra-field-key border-slate-300 rounded p-1.5 text-xs font-mono w-1/3" placeholder="key (e.g. author_id)" value="${key}" oninput="syncVisualToMappingJson()">
            <span class="text-slate-400 text-xs">=</span>
            <input type="text" class="extra-field-val border-slate-300 rounded p-1.5 text-xs font-mono flex-1" placeholder="value (e.g. 1)" value="${val}" oninput="syncVisualToMappingJson()">
            <button type="button" onclick="removeExtraFieldRow(this)" class="text-rose-500 hover:text-rose-700 p-1 text-sm"><i class="fas fa-times"></i></button>
        `;
        container.appendChild(row);
        syncVisualToMappingJson();
    }

    function removeExtraFieldRow(btn) {
        btn.closest('.extra-field-row').remove();
        syncVisualToMappingJson();
    }

    function generateRandomToken() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let res = 'sec_';
        for (let i = 0; i < 28; i++) {
            res += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('laravel_api_token').value = res;
    }

    // ==========================================================
    // 2. Quick Code Generator Modal Logic
    // ==========================================================
    let currentCodeGenTab = 'nextjs_app';

    function openCodeGeneratorModal() {
        renderCodeSnippet();
        document.getElementById('codeGeneratorModal').classList.remove('hidden');
    }

    function closeCodeGeneratorModal() {
        document.getElementById('codeGeneratorModal').classList.add('hidden');
    }

    function switchCodeGenTab(tab) {
        currentCodeGenTab = tab;
        document.querySelectorAll('.code-tab-btn').forEach(btn => {
            btn.classList.remove('border-indigo-500', 'text-indigo-400');
            btn.classList.add('border-transparent', 'text-slate-400');
        });
        const activeBtn = document.getElementById('tab_' + tab);
        if (activeBtn) {
            activeBtn.classList.add('border-indigo-500', 'text-indigo-400');
            activeBtn.classList.remove('border-transparent', 'text-slate-400');
        }
        renderCodeSnippet();
    }

    function renderCodeSnippet() {
        const token = document.getElementById('laravel_api_token').value || 'YOUR_SECRET_TOKEN_HERE';
        const codeBox = document.getElementById('code_snippet_box');
        const pathBox = document.getElementById('code_file_path');

        if (currentCodeGenTab === 'nextjs_app') {
            pathBox.innerText = 'app/api/external-news-post/route.ts (Next.js App Router)';
            codeBox.innerText = `import { NextRequest, NextResponse } from 'next/server';

export async function POST(req: NextRequest) {
  try {
    const body = await req.json();
    const { token, title, content, image_url, category_name, slug } = body;

    // 1. Verify Secret Token
    if (token !== '${token}') {
      return NextResponse.json({ success: false, error: 'Unauthorized' }, { status: 401 });
    }

    // 2. Save to Database (e.g. Prisma / Drizzle / Mongoose)
    // const post = await prisma.post.create({ data: { title, content, image: image_url, slug } });
    const postId = Date.now();

    return NextResponse.json({
      success: true,
      post_id: postId,
      live_url: \`https://yourwebsite.com/news/\${slug || postId}\`
    });
  } catch (err: any) {
    return NextResponse.json({ success: false, error: err.message }, { status: 500 });
  }
}`;
        } else if (currentCodeGenTab === 'nextjs_pages') {
            pathBox.innerText = 'pages/api/external-news-post.ts (Next.js Pages Router)';
            codeBox.innerText = `import type { NextApiRequest, NextApiResponse } from 'next';

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  if (req.method !== 'POST') return res.status(405).json({ error: 'Method Not Allowed' });

  const { token, title, content, image_url, slug } = req.body;

  if (token !== '${token}') {
    return res.status(401).json({ success: false, error: 'Unauthorized Token' });
  }

  try {
    // Insert into your database
    const postId = Date.now();
    return res.status(200).json({
      success: true,
      post_id: postId,
      live_url: \`https://yourwebsite.com/news/\${slug || postId}\`
    });
  } catch (err: any) {
    return res.status(500).json({ success: false, error: err.message });
  }
}`;
        } else if (currentCodeGenTab === 'nodejs_express') {
            pathBox.innerText = 'routes/newsReceiver.js (Express)';
            codeBox.innerText = `const express = require('express');
const router = express.Router();

router.post('/api/external-news-post', async (req, res) => {
  const { token, title, content, image_url, hashtags, category_name, slug } = req.body;

  // Verify Secret Token
  if (token !== '${token}') {
    return res.status(401).json({ success: false, error: 'Unauthorized Token' });
  }

  try {
    // Insert into your DB (MongoDB / MySQL / PostgreSQL)
    // const newPost = await Post.create({ title, content, image: image_url, slug });
    const postId = 101; 

    return res.json({
      success: true,
      post_id: postId,
      live_url: \`https://yourwebsite.com/news/\${slug || postId}\`
    });
  } catch (err) {
    return res.status(500).json({ success: false, error: err.message });
  }
});

module.exports = router;`;
        } else if (currentCodeGenTab === 'laravel') {
            pathBox.innerText = 'routes/api.php (Laravel)';
            codeBox.innerText = `use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Route;

Route::post('/external-news-post', function (Request $request) {
    // 1. Verify Secret Token
    $secretToken = '${token}';
    if ($request->input('token') !== $secretToken) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    try {
        // 2. Save Post
        $post = new \\App\\Models\\Post();
        $post->title = $request->input('title');
        $post->content = $request->input('content');
        $post->image = $request->input('image_url');
        $post->status = 'published';
        $post->save();

        return response()->json([
            'success' => true,
            'post_id' => $post->id,
            'live_url' => url('/news/' . $post->id)
        ]);
    } catch (\\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});`;
        } else if (currentCodeGenTab === 'raw_php') {
            pathBox.innerText = 'public/news-receiver.php (Drop-in Single File)';
            codeBox.innerText = '<' + '?php\n' +
`header('Content-Type: application/json');

$token = '${token}';
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || ($input['token'] ?? '') !== $token) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized: Token Mismatch']);
    exit;
}

$title   = $input['title'] ?? '';
$content = $input['content'] ?? '';
$image   = $input['image_url'] ?? '';
$slug    = $input['slug'] ?? ('news-' . time());

// DB Insert Example (PDO)
// $pdo = new PDO("mysql:host=localhost;dbname=news_db", "user", "pass");
// $stmt = $pdo->prepare("INSERT INTO posts (title, content, image) VALUES (?, ?, ?)");
// $stmt->execute([$title, $content, $image]);
// $postId = $pdo->lastInsertId();

$postId = time();

echo json_encode([
    'success' => true,
    'post_id' => $postId,
    'live_url' => "https://yourwebsite.com/news/" . $postId
]);`;
        } else if (currentCodeGenTab === 'python') {
            pathBox.innerText = 'main.py (FastAPI / Python)';
            codeBox.innerText = `from fastapi import FastAPI, HTTPException, Header
from pydantic import BaseModel
from typing import Optional, List

app = FastAPI()

class NewsPayload(BaseModel):
    token: str
    title: str
    content: str
    image_url: Optional[str] = None
    category_id: Optional[int] = 1
    slug: Optional[str] = None

@app.post("/api/external-news-post")
async def receive_news(payload: NewsPayload):
    if payload.token != "${token}":
        raise HTTPException(status_code=401, detail="Unauthorized Token")
    
    # Save to your SQL / MongoDB Database
    post_id = 999
    
    return {
        "success": True,
        "post_id": post_id,
        "live_url": f"https://yourwebsite.com/news/{payload.slug or post_id}"
    }`;
        }
    }

    function copyGeneratedCode() {
        const code = document.getElementById('code_snippet_box').innerText;
        navigator.clipboard.writeText(code).then(() => {
            const btn = document.getElementById('btn_copy_code');
            btn.innerHTML = '<i class="fas fa-check text-green-400"></i> Copied!';
            setTimeout(() => {
                btn.innerHTML = '<i class="far fa-copy"></i> Copy Code';
            }, 2000);
        });
    }

    // ==========================================================
    // 3. Test Custom API Connection Logic
    // ==========================================================
    function testCustomApiConnection() {
        const btn = document.getElementById('btn_test_custom_api');
        const statusBox = document.getElementById('custom_api_status_box');
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Connecting...';
        btn.disabled = true;

        statusBox.className = 'mb-4 p-3 rounded-lg text-xs font-bold bg-indigo-50 border border-indigo-200 text-indigo-800';
        statusBox.innerHTML = '⏳ এন্ডপয়েন্টে সংযোগ পরীক্ষা করা হচ্ছে...';
        statusBox.classList.remove('hidden');

        fetch('/settings/test/custom-api', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                laravel_site_url: document.getElementById('laravel_site_url').value,
                laravel_api_token: document.getElementById('laravel_api_token').value,
                custom_api_url: document.getElementById('custom_api_url').value,
                custom_api_mapping: document.getElementById('custom_api_mapping').value
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusBox.className = 'mb-4 p-3 rounded-lg text-xs font-bold bg-green-50 border border-green-200 text-green-800';
                statusBox.innerHTML = `<i class="fas fa-check-circle text-green-600"></i> ${data.message}`;
            } else {
                statusBox.className = 'mb-4 p-3 rounded-lg text-xs font-bold bg-rose-50 border border-rose-200 text-rose-800';
                statusBox.innerHTML = `<i class="fas fa-exclamation-triangle text-rose-600"></i> ${data.message}`;
            }
        })
        .catch(err => {
            statusBox.className = 'mb-4 p-3 rounded-lg text-xs font-bold bg-rose-50 border border-rose-200 text-rose-800';
            statusBox.innerHTML = `<i class="fas fa-times-circle text-rose-600"></i> Connection Request Failed: ${err.message}`;
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ==========================================================
    // 4. Assistant Help Modal & FAQ Logic
    // ==========================================================
    function openAssistantHelpModal() {
        document.getElementById('assistantHelpModal').classList.remove('hidden');
    }

    function closeAssistantHelpModal() {
        document.getElementById('assistantHelpModal').classList.add('hidden');
    }

    function switchHelpTab(tab) {
        document.querySelectorAll('.help-tab-btn').forEach(btn => {
            btn.classList.remove('border-indigo-500', 'text-indigo-400');
            btn.classList.add('border-transparent', 'text-slate-400');
        });
        const activeBtn = document.getElementById('htab_' + tab);
        if (activeBtn) {
            activeBtn.classList.add('border-indigo-500', 'text-indigo-400');
            activeBtn.classList.remove('border-transparent', 'text-slate-400');
        }

        document.getElementById('help_content_walkthrough').classList.add('hidden');
        document.getElementById('help_content_faq').classList.add('hidden');
        document.getElementById('help_content_diagnostics').classList.add('hidden');

        const activeContent = document.getElementById('help_content_' + tab);
        if (activeContent) activeContent.classList.remove('hidden');
    }

    function toggleFaqAccordion(id) {
        const body = document.getElementById('faq_body_' + id);
        const icon = document.getElementById('faq_icon_' + id);
        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            body.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // ৪. পেজ লোড হলে অটোমেটিক ক্যাটাগরি লোড (ক্যাশ থেকে আসবে)
    document.addEventListener('DOMContentLoaded', () => {
        initVisualMappingFromStoredJson();
        @if(($settings->wp_url && $settings->wp_username) || ($settings->laravel_site_url && $settings->laravel_api_token) || $settings->custom_category_url)
            fetchWPCategories(false); 
        @endif
    });
</script>

@endsection