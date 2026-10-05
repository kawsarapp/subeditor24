@extends('layouts.app')

@section('title', $page->meta_title ?: ($page->title . ' - Subeditor24'))

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 sm:px-6 lg:px-8 font-bangla relative overflow-hidden">

    {{-- Background Glow --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-80 bg-gradient-to-b from-indigo-600/15 via-purple-600/10 to-transparent blur-3xl pointer-events-none"></div>

    <div class="max-w-6xl mx-auto relative z-10">

        {{-- Breadcrumb --}}
        <nav class="flex items-center gap-2 text-xs text-slate-400 mb-6" aria-label="Breadcrumb">
            <a href="{{ route('login') }}" class="hover:text-indigo-400 transition">হোম</a>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-600"></i>
            <span class="text-slate-400">আইনি নীতিমালা</span>
            <i class="fa-solid fa-chevron-right text-[9px] text-slate-600"></i>
            <span class="text-indigo-400 font-semibold truncate">{{ $page->title }}</span>
        </nav>

        {{-- Header Section --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 mb-8 backdrop-blur-xl shadow-2xl relative overflow-hidden">
            <div class="max-w-3xl space-y-3">
                @if(!empty($page->badge))
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/30 text-indigo-400 text-xs font-black tracking-wider uppercase">
                    <i class="fa-solid fa-shield-halved text-indigo-400"></i> {{ $page->badge }}
                </div>
                @endif
                <h1 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                    {{ $page->title }}
                </h1>
                @if(!empty($page->subtitle))
                <p class="text-xs sm:text-sm text-slate-300 font-medium leading-relaxed">
                    {{ $page->subtitle }}
                </p>
                @endif

                @if(!empty($page->last_updated_date))
                <div class="flex items-center gap-2 text-[11px] text-slate-400 pt-2 font-medium">
                    <i class="fa-regular fa-clock text-indigo-400"></i>
                    <span>সর্বশেষ আপডেট: <span class="text-slate-200 font-semibold">{{ $page->last_updated_date }}</span></span>
                </div>
                @endif
            </div>
        </div>

        {{-- Main Content Grid with Sidebar --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            {{-- Sticky Sidebar Links --}}
            <aside class="lg:col-span-4 lg:sticky lg:top-24 space-y-4">
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-xl">
                    <h2 class="text-xs font-black text-slate-400 uppercase tracking-wider px-3 mb-3">
                        <i class="fa-solid fa-folder-open text-indigo-400 mr-1.5"></i> আইনি ও পলিসি পেজসমূহ
                    </h2>
                    <nav class="space-y-1">
                        @foreach($allPages as $navPage)
                        <a href="{{ route('legal.show', $navPage->slug) }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition {{ $page->slug === $navPage->slug ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                            <span class="truncate">{{ $navPage->title }}</span>
                            <i class="fa-solid fa-chevron-right text-[10px] {{ $page->slug === $navPage->slug ? 'text-white' : 'text-slate-500' }}"></i>
                        </a>
                        @endforeach
                    </nav>
                </div>

                {{-- Direct Support Quick Box --}}
                <div class="bg-gradient-to-br from-indigo-950/60 to-purple-950/60 border border-indigo-500/30 rounded-2xl p-5 shadow-xl space-y-3">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center text-base">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-black text-white">কোনো প্রশ্ন আছে?</h3>
                        <p class="text-[11px] text-slate-300 mt-1 leading-relaxed">
                            পলিসি বা প্ল্যাটফর্ম ব্যবহার সম্পর্কে বিস্তারিত জানতে আমাদের এক্সিকিউটিভ টিমের সাথে যোগাযোগ করুন।
                        </p>
                    </div>
                    <div class="pt-1 space-y-2">
                        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $pricingConfig['whatsapp_number'] ?? '8801975389599') }}?text={{ urlencode('Hello, I have a question regarding Subeditor24 legal policies.') }}" target="_blank" onclick="if(window.fbq) fbq('track', 'Contact', { content_name: 'WhatsApp Legal Inquiry' });" class="w-full py-2.5 px-3 bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs rounded-xl shadow transition flex items-center justify-center gap-2">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                            <span>WhatsApp এ চ্যাট করুন</span>
                        </a>
                        <a href="{{ route('register') }}" class="w-full py-2.5 px-3 bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold text-xs rounded-xl shadow transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-rocket text-xs"></i>
                            <span>ফ্রি ট্রায়াল শুরু করুন</span>
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Main Content Article --}}
            <main class="lg:col-span-8 bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl backdrop-blur-xl">
                <article class="legal-content prose prose-invert prose-indigo max-w-none text-slate-300 text-sm sm:text-base leading-relaxed space-y-6">
                    {!! $page->content !!}
                </article>

                {{-- Bottom Quick Action Bar --}}
                <div class="mt-10 pt-6 border-t border-slate-800 flex flex-wrap items-center justify-between gap-4">
                    <div class="text-xs text-slate-400">
                        &copy; {{ date('Y') }} Subeditor24. All rights reserved.
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('pricing.index') }}" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 transition">
                            <i class="fa-solid fa-tags mr-1"></i> প্যাকেজসমূহ
                        </a>
                        <span class="text-slate-600">•</span>
                        <a href="https://wa.me/{{ preg_replace('/[^\d]/', '', $pricingConfig['whatsapp_number'] ?? '8801975389599') }}" target="_blank" onclick="if(window.fbq) fbq('track', 'Contact', { content_name: 'WhatsApp Footer Support' });" class="text-xs font-bold text-emerald-400 hover:text-emerald-300 transition">
                            <i class="fa-brands fa-whatsapp mr-1"></i> হেল্পডেস্ক
                        </a>
                    </div>
                </div>
            </main>

        </div>

    </div>
</div>

<style>
/* Policy HTML Styling */
.legal-content h2, .legal-content h3 {
    color: #ffffff;
    font-weight: 800;
    margin-top: 1.75rem;
    margin-bottom: 0.75rem;
    font-size: 1.15rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 0.4rem;
}
.legal-content p {
    margin-bottom: 1rem;
    line-height: 1.75;
    color: #cbd5e1;
}
.legal-content ul, .legal-content ol {
    margin-bottom: 1.25rem;
    padding-left: 1.25rem;
    space-y: 0.5rem;
}
.legal-content ul {
    list-style-type: disc;
}
.legal-content ol {
    list-style-type: decimal;
}
.legal-content li {
    margin-bottom: 0.4rem;
    color: #cbd5e1;
    line-height: 1.6;
}
.legal-content strong {
    color: #f8fafc;
}
.legal-content a {
    color: #818cf8;
    text-decoration: underline;
    font-weight: 600;
}
.legal-content a:hover {
    color: #a5b4fc;
}
.legal-content blockquote {
    border-left: 4px solid #6366f1;
    padding-left: 1rem;
    color: #94a3b8;
    font-style: italic;
    background: rgba(99, 102, 241, 0.05);
    padding: 0.75rem 1rem;
    border-radius: 0 0.75rem 0.75rem 0;
    margin: 1.25rem 0;
}
</style>
@endsection
