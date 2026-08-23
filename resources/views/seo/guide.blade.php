@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

    {{-- HEADER & NAVIGATION BACK --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-black uppercase tracking-wider mb-2 border border-indigo-200">
                📖 সহজিয়া এসইও গাইডবুক (Human Master Guide)
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">
                🔍 SEO & Website Intelligence — সম্পূর্ণ বাংলা ব্যবহার নির্দেশিকা
            </h1>
            <p class="text-xs sm:text-sm text-slate-600 font-medium mt-1">
                মডিউলের প্রতিটি সেকশন কিসের জন্য কাজ করে, কী কী সমস্যা খুঁজে বের করে, কেন সমস্যাগুলো হয় এবং কীভাবে সমাধান করবেন—সবকিছুর সহজ মানবিক ব্যাখ্যা।
            </p>
        </div>

        <a href="{{ route('seo.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold px-5 py-2.5 rounded-2xl text-xs flex items-center gap-2 shadow-md transition shrink-0">
            <i class="fa-solid fa-arrow-left"></i> SEO Dashboard-এ ফিরে যান
        </a>
    </div>

    {{-- INTRO NOTE --}}
    <div class="p-6 bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-3xl shadow-lg mb-8 border border-indigo-700/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-500/20 text-indigo-300 flex items-center justify-center text-2xl shrink-0 border border-indigo-400/30">
                💡
            </div>
            <div>
                <h2 class="font-extrabold text-base sm:text-lg text-white mb-1">স্বাগতম! এসইও ড্যাশবোর্ড ব্যবহারের মূল নিয়মাবলী:</h2>
                <p class="text-xs sm:text-sm text-slate-300 font-medium leading-relaxed">
                    সংবাদের পোর্টালে প্রতিদিন অগণিত ব্রেকিং নিউজ প্রকাশিত হয়। গুগল সার্চে ১ নম্বরে থাকা, গুগল ডিসকভারে আসা এবং সাইট দ্রুত স্পিডে রাখা—সবকিছুর পেছনে একটি টেকনিক্যাল চেইন কাজ করে। নিচের ১৫টি সেকশনে প্রতিটি বিষয়ের বিস্তারিত সমাধান তুলে ধরা হলো।
                </p>
            </div>
        </div>
    </div>

    {{-- DETAILED 15 SECTIONS --}}
    <div class="space-y-8">

        {{-- 1. ORPHAN NEWS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 border border-amber-200">
                    1
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">⚠️ Orphan News (অনাথ সংবাদ বা ইন্টারনাল লিঙ্কহীন খবর)</h3>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200">Internal Linking Architecture</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">যেসব সংবাদ প্রকাশের পর ওয়েবসাইটের অন্য কোনো নিউজ বা ক্যাটাগরি পেজ থেকে একটি লিঙ্কও (Internal Link) পায়নি, গুগল সেগুলোকে 'Orphan Page' বলে মনে করে।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">সাংবাদিক দ্রুত খবর লিখে পাবলিশ করে দেন, কিন্তু অন্য পুরোনো খবরের ভেতরে এই সংবাদের লিংক দেন না। ফলে গুগলের বট (Googlebot) পেজটি সহজে খুঁজে পায় না এবং র্যাঙ্কিং কমে যায়।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">মডিউলের এআই স্বয়ংক্রিয়ভাবে প্রাসঙ্গিক পুরোনো খবর খুঁজে বের করে লিঙ্ক সাজেস্ট করবে। অ্যাডমিন শুধু <strong>`✅ Approve & Apply`</strong> বাটনে চাপ দিলেই লিঙ্কটি পোস্টে যুক্ত হয়ে যাবে।</p>
                </div>
            </div>
        </div>

        {{-- 2. BROKEN LINKS & 404 --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-800 flex items-center justify-center text-lg font-black shrink-0 border border-rose-200">
                    2
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">💔 Broken Links & 404 Pages (ভাঙা লিঙ্ক ও হারানো পাতা)</h3>
                    <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-md border border-rose-200">404 Error & Crawl Budget</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">ওয়েবসাইটের ভেতরে এমন কোনো লিঙ্ক আছে যাতে পাঠক বা গুগল বট ক্লিক করলে 404 Page Not Found এরর আসে।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">সংবাদের ইউআরএল পরিবর্তন করা হলে বা কোনো খবর ডিলিট করে দিলে পুরোনো লিঙ্কগুলো ভাঙা থেকে যায়। এতে গুগলের ট্রাস্ট কমে যায় এবং পাঠক বিরক্ত হয়ে সাইট ছেড়ে চলে যান।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">ড্যাশবোর্ডে চিহ্নিত ৪০৪ ইউআরএলগুলোকে নতুন চলমান নিউজের সাথে **301 Permanent Redirect** সেট করুন অথবা নিবন্ধের ভেতরের মৃত লিঙ্কটি এডিট করে দিন।</p>
                </div>
            </div>
        </div>

        {{-- 3. INSTANT INDEXING --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-violet-100 text-violet-800 flex items-center justify-center text-lg font-black shrink-0 border border-violet-200">
                    3
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">⚡ Instant Indexing (গুগল ও বিং ১৫ সেকেন্ড পুশ এবং ১৭টি সার্চ ইঞ্জিন)</h3>
                    <span class="text-xs font-bold text-violet-700 bg-violet-50 px-2.5 py-0.5 rounded-md border border-violet-200">Real-Time Multi-Engine IndexNow Push</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700 mb-4">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">ব্রেকিং নিউজ প্রকাশের পরপরই গুগলের স্বাভাবিক ক্রলিংয়ের জন্য ঘণ্টার পর ঘণ্টা অপেক্ষা না করে সরাসরি গুগল ও বিং সহ ১৭টি প্রধান সার্চ ইঞ্জিনে পুশ পাঠানোর জন্য।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">অন্য পত্রিকা একই খবর পাবলিশ করে গুগলে আগে ইনডেক্স করে ফেললে আপনার পত্রিকার আসল ব্রেকিং নিউজটি গুগলের পেছনে পড়ে যায় এবং আপনি হাজার হাজার ভিজিটর হারান।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">`⚡ Instant Indexing` ট্যাবে গিয়ে নতুন নিউজের লিংক ইনপুট দিয়ে **`Push Instant Indexing (15s)`** চাপুন। ১৫-২০ সেকেন্ডে সকল সার্চ ইঞ্জিনে পুশ পৌঁছে যাবে।</p>
                </div>
            </div>

            {{-- 17 SEARCH ENGINES LIST BOX --}}
            <div class="p-4 bg-violet-50/80 rounded-2xl border border-violet-200">
                <h4 class="font-extrabold text-violet-900 text-xs uppercase tracking-wider mb-2 flex items-center gap-1.5">
                    🌐 IndexNow প্রোটোকল দিয়ে যে ১৭টি প্রধান সার্চ ইঞ্জিনে খবর ১৫ সেকেন্ডে পুশ হয়:
                </h4>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 text-[11px] font-bold text-slate-700">
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🟢 Microsoft Bing (আন্তর্জাতিক)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🔴 Yandex (রাশিয়া ও ইউরোপ)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🦆 DuckDuckGo (গোপনীয়তা)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇰🇷 Naver (দক্ষিণ কোরিয়া)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇨🇿 Seznam.cz (চেক রিপাবলিক)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🟣 Yahoo! Search (গ্লোবাল)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🛡️ Brave Search (প্রাইভেট)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🌿 Ecosia (গ্রিন সার্চ)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">❓ Ask.com (কোশ্চেন ও সার্চ)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">⚡ Yep.com (Ahrefs ইঞ্জিন)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🔍 Qwant (ইউরোপ)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇨🇳 Sogou (এশিয়ান পার্টনার)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🇨🇭 Swisscows (সুইজারল্যান্ড)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">🎒 Mojeek (ইউকে সার্চ)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">📱 Startpage (ইউরোপিয়ান)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">💡 Search.com (নলেজ)</div>
                    <div class="p-2 bg-white rounded-xl border border-violet-100 flex items-center gap-1.5">📰 Google Discover Feed</div>
                </div>
            </div>
        </div>

        {{-- 4. GOOGLE DISCOVER OPTIMIZER --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-sky-100 text-sky-800 flex items-center justify-center text-lg font-black shrink-0 border border-sky-200">
                    4
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">📰 Google Discover Optimizer Engine (গুগল ডিসকভার বুস্টার)</h3>
                    <span class="text-xs font-bold text-sky-700 bg-sky-50 px-2.5 py-0.5 rounded-md border border-sky-200">Viral Traffic Engine</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">স্মার্টফোনের Google App এবং গুগল ডিসকভার ফিডে খবরটি লাখ লাখ মোবাইল ইউজারের হোমপেজে ভাইরাল ক্যান্ডিডেট হিসেবে জায়গা পাওয়ার উপযোগী কি না তা পরীক্ষা করা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">ফিচার্ড ইমেজের সাইজ ১২০০ পিক্সেলের কম হলে বা মেটা ট্যাগে `max-image-preview:large` না থাকলে গুগল কখনোই সংবাদ ডিসকভারে পাঠায় না।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">খবরের ফিচার্ড ইমেজ অন্তত ১২০০px চওড়া রাখুন এবং এআই-এর তৈরি ৩টি হাই-সিটিআর আকর্ষণীয় শিরোনামের যেকোনো একটি ব্যবহার করুন।</p>
                </div>
            </div>
        </div>

        {{-- 5. SOCIAL MEDIA TRAFFIC --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    5
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">📱 Social Media Referral Traffic (সোশ্যাল ভিজিটর ট্র্যাকার)</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">Multi-Channel Analytics</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">ফেসবুক, এক্স (টুইটার), ইনস্টাগ্রাম, ইউটিউব, হোয়াটসঅ্যাপ এবং টেলিগ্রাম—কোন সোশ্যাল মিডিয়া চ্যানেল থেকে ঠিক কতজন ভিজিটর আসছে তা ট্র্যাক করা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">সোশ্যাল মিডিয়ায় শেয়ার করার সময় কাস্টম UTM লিঙ্ক ব্যবহার না করলে গুগলে এই ট্র্যাফিক 'Direct/Unknown' হিসেবে জমা হয়, ফলে আসল ভিজিটর বোঝা যায় না।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">মডিউলের Smart UTM Generator দিয়ে সোশ্যাল লিঙ্কের সাথে প্যারামিটার যুক্ত করে আপনার ফেসবুক পেজ ও টেলিগ্রাম চ্যানেলে পোস্ট করুন।</p>
                </div>
            </div>
        </div>

        {{-- 6. TECHNICAL AUDIT --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-teal-100 text-teal-800 flex items-center justify-center text-lg font-black shrink-0 border border-teal-200">
                    6
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🔍 Technical SEO Audit (কারিগরি এসইও অডিট)</h3>
                    <span class="text-xs font-bold text-teal-700 bg-teal-50 px-2.5 py-0.5 rounded-md border border-teal-200">Site Health Check</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">ওয়েবসাইটের ভেতরের সংবাদের টাইটেল মিসিং, মেটা ডেসক্রিপশন ছোট হওয়া, একাধিক H1 ট্যাগ থাকা বা পাতলা কন্টেন্ট (Thin Content) স্ক্যান করার জন্য।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">তাড়াহুড়ো করে ৭০ অক্ষরের কম মেটা ডেসক্রিপশন লিখলে বা টাইটেল না দিলে গুগল সার্চ রেজাল্টে সংবাদের সারসংক্ষেপ অসম্পূর্ণ দেখায়।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">অডিট লিফটে লাল চিহ্নিত নিবন্ধগুলোর সম্পাদন পাতায় গিয়ে অন্তত ১৫০ অক্ষরের মেটা ডেসক্রিপশন এবং একটি মাত্র মূল H1 হেডার নিশ্চিত করুন।</p>
                </div>
            </div>
        </div>

        {{-- 7. GSC QUICK WINS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 border border-amber-200">
                    7
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🎯 GSC Quick Wins (গুগল ৪-১৫ পজিশনের কিউওয়ার্ড)</h3>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200">Low-Hanging Ranking Fruit</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">যেসব কিউওয়ার্ড বর্তমানে গুগলে ৪ থেকে ১৫ নম্বর স্থানে ঝুলছে। এগুলোকে অল্প একটু টিউন করলেই সহজে ১, ২ ও ৩ নম্বরে (Top 3) আনা সম্ভব।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">সংবাদের ভেতরে কিউওয়ার্ডটির ঘনত্ব কম থাকা বা সাব-হেডিং (H2) এ কিউওয়ার্ডটি না থাকার কারণে গুগল খবরটিকে প্রথম পেজের শীর্ষে তুলতে পারে না।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">কুইক-উইন কিউওয়ার্ডটি সংবাদের প্রথম ১০০ শব্দের মধ্যে এবং একটি সাব-হেডিংয়ে (H2) যুক্ত করে আপডেট দিন।</p>
                </div>
            </div>
        </div>

        {{-- 8. GA4 TRAFFIC & DECAY --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-800 flex items-center justify-center text-lg font-black shrink-0 border border-rose-200">
                    8
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">📉 GA4 Organic Traffic & Content Decay (কন্টেন্ট ক্ষয় অ্যালার্ট)</h3>
                    <span class="text-xs font-bold text-rose-700 bg-rose-50 px-2.5 py-0.5 rounded-md border border-rose-200">Traffic Retention Alert</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">যেসব পুরোনো জনপ্রিয় সংবাদের অর্গানিক ক্লিক ও ট্র্যাফিক গত ৩০ দিনে ৩০%-এর বেশি কমে গেছে (Content Decay) তা ধরা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">তথ্য পুরোনো হয়ে গেলে পাঠকেরা পেজে ঢুকে ব্যাক করে চলে যান, ফলে গুগল আস্তে আস্তে র্যাঙ্কিং কমিয়ে দেয়।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">মডিউলের AI Refresh Suggestions অনুসরণ করে সংবাদের ভেতরে নতুন ব্রেকিং তথ্য ও আজকের তারিখ যুক্ত করে কন্টেন্ট রিফ্রেশ করুন।</p>
                </div>
            </div>
        </div>

        {{-- 9. CORE WEB VITALS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-lg font-black shrink-0 border border-amber-200">
                    9
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">⚡ Core Web Vitals (পেজ লোডিং ও ইউজার এক্সপেরিয়েন্স)</h3>
                    <span class="text-xs font-bold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-md border border-amber-200">Page Experience Metric</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">মোবাইল ও কম্পিউটারে খবর লোড হওয়ার আসল সময় (LCP), সার্ভার রেসপন্স স্পিড (TTFB) এবং পেজের লেআউট কেঁপে ওঠা (CLS) মাপার জন্য।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">ভারী ছবি ও বড় বড় জাভাস্ক্রিপ্ট ফাইলের কারণে পেজ লোড হতে ২.৫ সেকেন্ডের বেশি সময় লাগলে গুগল সাইটের র‍্যাঙ্কিং নিচে নামিয়ে দেয়।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">ছবিগুলোকে WebP ফরম্যাটে কম্প্রেস করুন এবং সার্ভারে Redis / Page Caching চালু রেখে TTFB স্পিড ২০০ms-এর নিচে রাখুন।</p>
                </div>
            </div>
        </div>

        {{-- 10. SCHEMA VALIDATOR --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-lg font-black shrink-0 border border-purple-200">
                    10
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🏷️ Schema & Structured Data (নিউজ রিচ স্নিপেট স্কিমা)</h3>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-md border border-purple-200">Structured Data JSON-LD</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">গুগল বটকে সংবাদের শিরোনাম, লেখক, প্রকাশের সময় ও লোগো পরিষ্কারভাবে বোঝানোর জন্য JSON-LD স্নিপেট যাচাই করা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">`NewsArticle` স্কিমা না থাকলে গুগল সার্চে সংবাদের ছবি ও লোগোসহ সুন্দর রিচ স্নিপেট বা থাম্বনেইল প্রদর্শন করে না।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">মডিউলের Schema Validator দিয়ে নিশ্চিত করুন যে প্রকাশিত প্রতিটি নিউজের হেডার কোডে সঠিক `@type: NewsArticle` ট্যাগ সক্রিয় রয়েছে।</p>
                </div>
            </div>
        </div>

        {{-- 11. COMPETITOR COMPARE --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    11
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🆚 Competitor Ranking & Keyword Gap Finder (প্রতিদ্বন্দ্বী তুলনা)</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">Competitor Intelligence</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">অন্যান্য প্রধান নিউজ পোটালের (যেমন: prothomalo.com) সাথে আপনার পত্রিকার র্যাঙ্কিং এবং হারানো কিউওয়ার্ড গ্যাপ তুলনা করা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">প্রতিদ্বন্দ্বী পত্রিকা কোন কোন সার্চ কিউওয়ার্ড দিয়ে লাখ লাখ পাঠক পাচ্ছে কিন্তু আপনার সাইটে সেই নিউজগুলো নেই তা না জানা।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">ইনপুট বক্সে প্রতিদ্বন্দ্বী পোটালের নাম দিয়ে **`Analyze Keyword Gap`** চাপুন এবং সাজেস্ট করা কিউওয়ার্ডগুলো নিয়ে রিপোর্ট লিখুন।</p>
                </div>
            </div>
        </div>

        {{-- 12. UPTIME & SECURITY --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-cyan-100 text-cyan-800 flex items-center justify-center text-lg font-black shrink-0 border border-cyan-200">
                    12
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🛡️ 24/7 Server Uptime & Telegram Instant Emergency Alert (সার্ভার অ্যালার্ট)</h3>
                    <span class="text-xs font-bold text-cyan-700 bg-cyan-50 px-2.5 py-0.5 rounded-md border border-cyan-200">Emergency Downtime Guard</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">আমেরিকা, ইউরোপ ও এশিয়া—বিশ্বের ৩টি স্থান থেকে প্রতি ৫ মিনিটে আপনার সংবাদের সার্ভার চালু আছে কি না তা টেস্ট করা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">সার্ভার ডাউন হলে (HTTP 502/504) গুগল বট সাইটে ঢুকতে পারে না এবং পাঠকেরা সাইট ব্লক দেখে ফিরে যান।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">টেলিগ্রাম বোট বক্সে আপনার বোট টোকেন বসিয়ে দিয়ে নোটিফিকেশন অন রাখুন। সাইট ডাউন হলেই ১৫ সেকেন্ডের মধ্যে আপনার ফোনে মেসেজ যাবে।</p>
                </div>
            </div>
        </div>

        {{-- 13. SITEMAP & ROBOTS --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    13
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🗺️ Sitemap & Robots.txt Analyzer (সাইটম্যাপ ও রোবটস অডিট)</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">Search Indexing Directives</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">গুগল বটকে ওয়েবসাইটের সমস্ত সংবাদের লিংক সঠিকভাবে জোগান দেওয়া এবং অনাকাঙ্ক্ষিত পেজ ব্লক করা থেকে বিরত রাখা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">Robots.txt ফাইলে ভুলবশত `Disallow: /` পড়ে থাকলে বা সাইটম্যাপ ইনভ্যালিড থাকলে পুরো ওয়েবসাইটের ইনডেক্সিং বন্ধ হয়ে যায়।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">মডিউলের Sitemap সেকশনে সাইটম্যাপের XML ভ্যালিডিটি সবুজ (Valid ✅) এবং Robots.txt ফাইলে `Disallow: /admin/` সীমাবদ্ধ রাখুন।</p>
                </div>
            </div>
        </div>

        {{-- 14. IMAGE SEO --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-800 flex items-center justify-center text-lg font-black shrink-0 border border-purple-200">
                    14
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🖼️ Image SEO & Asset Optimization (ছবি অপটিমাইজেশন)</h3>
                    <span class="text-xs font-bold text-purple-700 bg-purple-50 px-2.5 py-0.5 rounded-md border border-purple-200">Image Search Ranking</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">সংবাদের ছবিগুলোতে অল্টার টেক্সট (`alt=""`) আছে কি না এবং ছবিগুলো আধুনিক WebP/AVIF ফরম্যাটে আছে কি না তা স্ক্যান করা।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">গুগল ইমেজের বট ছবি দেখে বুঝতে পারে না। অল্টার টেক্সট না থাকলে গুগল ইমেজ সার্চ থেকে কোনো ভিজিটর আসে না।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">ছবি আপলোড করার সময় ছবির বিষয়টি লিখে Alt Text ঘরে টাইটেল দিন এবং ছবিগুলোকে ছোট সাইজের WebP ফরম্যাটে সেভ করুন।</p>
                </div>
            </div>
        </div>

        {{-- 15. AI SEO ASSISTANT --}}
        <div class="luxe-card bg-white p-6 md:p-8 rounded-3xl border border-slate-200 shadow-md">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-800 flex items-center justify-center text-lg font-black shrink-0 border border-indigo-200">
                    15
                </div>
                <div>
                    <h3 class="text-lg font-black text-slate-900">🤖 AI SEO Assistant (এআই এসইও অটো-ফিক্স সহকারী)</h3>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-200">AI Intelligent Recommendation</span>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs font-medium text-slate-700">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-1">
                    <strong class="text-indigo-900 font-extrabold block text-sm">❓ এটি কিসের জন্য দেখাচ্ছে?</strong>
                    <p class="leading-relaxed text-slate-600">পুরো ওয়েবসাইটের টেকনিক্যাল ও কন্টেন্ট দুর্বলতাগুলো সমাধান করার জন্য এক নজরে এআই-এর অটোমেটিক স্মার্ট গাইডলাইন পাওয়া।</p>
                </div>
                <div class="p-4 bg-rose-50/70 rounded-2xl border border-rose-200 space-y-1">
                    <strong class="text-rose-900 font-extrabold block text-sm">⚠️ কী সমস্যা হয় ও কেন হয়?</strong>
                    <p class="leading-relaxed text-rose-950">ছোটখাটো অনেক এসইও ভুল সাধারণ মানুষের চোখে পড়ে না, যার ফলে গুগল ডোমেইনের ওভারঅল হেলথ স্কোর কম দেখিয়ে র‍্যাঙ্ক ডাউন করে রাখে।</p>
                </div>
                <div class="p-4 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-1">
                    <strong class="text-emerald-900 font-extrabold block text-sm">🛠️ কীভাবে সমাধান করবেন?</strong>
                    <p class="leading-relaxed text-emerald-950">`🤖 AI SEO Assistant` ট্যাবে এআই-এর দেওয়া ৪-৫টি দিকনির্দেশনা যেমন: টাইটেল অপটিমাইজেশন, স্কিমা ফিক্স ও লিংক যোগ করা অনুসরণ করে আপডেট দিন।</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
