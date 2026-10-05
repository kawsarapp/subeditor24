@extends('layouts.app')

@section('title', 'Legal & Policy Pages Management - Super Admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-8 px-4 sm:px-6 lg:px-8 font-bangla">
    <div class="max-w-7xl mx-auto space-y-6">

        {{-- Top Breadcrumb & Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900/80 border border-slate-800 p-6 rounded-3xl shadow-xl backdrop-blur-xl">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 text-indigo-400 text-xs font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-scale-balanced text-indigo-400"></i> Legal & Compliance CMS
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white">
                    আইনি ও পলিসি পেজসমূহ (Legal & Policy Pages)
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Privacy Policy, Terms of Service, Refund Policy সহ সকল পাবলিক পেজের কনটেন্ট ডায়নামিকভাবে পরিবর্তন ও নিয়ন্ত্রণ করুন।
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.legal-pages.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-indigo-500/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>নতুন পলিসি পেজ তৈরি করুন</span>
                </a>
            </div>
        </div>

        {{-- Flash Notification --}}
        @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-500/40 text-emerald-300 text-xs sm:text-sm font-bold flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-400 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-950/60 border border-rose-500/40 text-rose-300 text-xs sm:text-sm font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-400 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        {{-- Pages Table / Card Grid --}}
        <div class="bg-slate-900/80 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-5 border-b border-slate-800 flex items-center justify-between">
                <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                    <i class="fa-solid fa-list-check text-indigo-400 mr-1.5"></i> মোট পেজ: {{ $pages->count() }} টি
                </div>
            </div>

            <div class="divide-y divide-slate-800">
                @foreach($pages as $page)
                <div class="p-5 hover:bg-slate-800/40 transition flex flex-col md:flex-row md:items-center justify-between gap-4">
                    
                    {{-- Page Info --}}
                    <div class="space-y-1.5 max-w-2xl">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                #{{ $page->sort_order }}
                            </span>
                            @if($page->is_active)
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Active
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-md text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-500/30 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span> Inactive
                            </span>
                            @endif

                            <h3 class="text-base font-extrabold text-white">
                                {{ $page->title }}
                            </h3>
                        </div>

                        <div class="flex items-center gap-3 text-xs text-slate-400">
                            <span><strong class="text-slate-300">Slug:</strong> <code class="text-indigo-300 bg-slate-950 px-2 py-0.5 rounded text-[11px]">/{{ $page->slug }}</code></span>
                            <span>•</span>
                            <span>সর্বশেষ আপডেট: <strong class="text-slate-300">{{ $page->last_updated_date ?: 'N/A' }}</strong></span>
                        </div>

                        @if(!empty($page->subtitle))
                        <p class="text-xs text-slate-400 line-clamp-1">
                            {{ $page->subtitle }}
                        </p>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('legal.show', $page->slug) }}" target="_blank" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold border border-slate-700 transition flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px] text-slate-400"></i>
                            <span>ভিউ পেজ</span>
                        </a>

                        <a href="{{ route('admin.legal-pages.edit', $page->id) }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/20">
                            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                            <span>এডিট করুন</span>
                        </a>

                        <form action="{{ route('admin.legal-pages.reset', $page->id) }}" method="POST" onsubmit="return confirm('আপনি কি এই পেজটির কনটেন্টকে মূল ডিফল্ট টেমপ্লেটে রিসেট করতে চান?');">
                            @csrf
                            <button type="submit" title="Reset to Default Template" class="px-3 py-2 bg-slate-800 hover:bg-amber-500/20 text-slate-400 hover:text-amber-300 rounded-xl text-xs font-bold border border-slate-700 transition cursor-pointer">
                                <i class="fa-solid fa-rotate-left"></i>
                            </button>
                        </form>
                    </div>

                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection
