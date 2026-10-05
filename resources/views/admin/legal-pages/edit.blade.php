@extends('layouts.app')

@section('title', ($page->id ? 'Edit ' . $page->title : 'Create New Policy Page') . ' - Super Admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-8 px-4 sm:px-6 lg:px-8 font-bangla">
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- Top Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4 bg-slate-900/80 border border-slate-800 p-6 rounded-3xl shadow-xl backdrop-blur-xl">
            <div class="space-y-1">
                <a href="{{ route('admin.legal-pages.index') }}" class="text-xs text-indigo-400 hover:underline inline-flex items-center gap-1.5 font-bold mb-1">
                    <i class="fa-solid fa-arrow-left"></i> সকল পলিসি পেজে ফিরে যান
                </a>
                <h1 class="text-2xl sm:text-3xl font-black text-white">
                    {{ $page->id ? 'পলিসি পেজ এডিট: ' . $page->title : 'নতুন পলিসি পেজ তৈরি' }}
                </h1>
                <p class="text-xs text-slate-400">
                    পেজের শিরোনাম, সাবটাইটেল, মূল কনটেন্ট ও এসইও মেটা ট্যাগ পরিবর্তন করুন।
                </p>
            </div>

            @if($page->id && $page->slug)
            <div>
                <a href="{{ route('legal.show', $page->slug) }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white rounded-xl text-xs font-bold border border-slate-700 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                    <span>লাইভ পেজ দেখুন</span>
                </a>
            </div>
            @endif
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.legal-pages.save') }}" method="POST" class="space-y-6">
            @csrf
            @if($page->id)
                <input type="hidden" name="id" value="{{ $page->id }}">
            @endif

            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-6 shadow-2xl backdrop-blur-xl">

                {{-- Row 1: Title, Slug & Badge --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                    <div class="md:col-span-6 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            পেজের শিরোনাম (Page Title) <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" name="title" value="{{ old('title', $page->title) }}" required placeholder="যেমন: গোপনীয়তা নীতি (Privacy Policy)" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        @error('title') <p class="text-[11px] text-rose-400">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-6 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            URL Slug <span class="text-rose-400">*</span>
                        </label>
                        <div class="flex items-center">
                            <span class="bg-slate-800 border border-r-0 border-slate-700 px-3 py-2.5 rounded-l-xl text-xs text-slate-400 select-none">/</span>
                            <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" required placeholder="privacy-policy" class="w-full bg-slate-950 border border-slate-700 rounded-r-xl px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                        </div>
                        @error('slug') <p class="text-[11px] text-rose-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Row 2: Badge & Subtitle --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                    <div class="md:col-span-4 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            টপ ব্যাজ (Top Badge)
                        </label>
                        <input type="text" name="badge" value="{{ old('badge', $page->badge) }}" placeholder="🛡️ LEGAL & COMPLIANCE" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>

                    <div class="md:col-span-8 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            সাবটাইটেল / বিবরণ (Subtitle)
                        </label>
                        <input type="text" name="subtitle" value="{{ old('subtitle', $page->subtitle) }}" placeholder="সংক্ষিপ্ত বিবরণ" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>
                </div>

                {{-- Content Editor with Format Toolbars --}}
                <div class="space-y-2">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <label class="block text-xs font-bold text-slate-300">
                            পেজের মূল কনটেন্ট (HTML Content) <span class="text-rose-400">*</span>
                        </label>
                        {{-- Quick HTML Format Buttons --}}
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="insertTag('<h3>', '</h3>')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-300 rounded-md border border-slate-700 transition">H3 Heading</button>
                            <button type="button" onclick="insertTag('<p>', '</p>')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-300 rounded-md border border-slate-700 transition">Paragraph</button>
                            <button type="button" onclick="insertTag('<ul>\n  <li>', '</li>\n</ul>')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-300 rounded-md border border-slate-700 transition">List</button>
                            <button type="button" onclick="insertTag('<strong>', '</strong>')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-300 rounded-md border border-slate-700 transition">Bold</button>
                            <button type="button" onclick="insertTag('<a href=\'https://\'>', '</a>')" class="px-2 py-1 bg-slate-800 hover:bg-slate-700 text-[11px] font-bold text-slate-300 rounded-md border border-slate-700 transition">Link</button>
                        </div>
                    </div>

                    {{-- Tabs: Edit & Live Preview --}}
                    <div class="border border-slate-700 rounded-2xl overflow-hidden">
                        <div class="flex border-b border-slate-700 bg-slate-950/80 px-3 pt-2">
                            <button type="button" id="tabEdit" onclick="toggleEditorTab('edit')" class="px-4 py-2 text-xs font-bold text-indigo-400 border-b-2 border-indigo-500">
                                <i class="fa-solid fa-code mr-1"></i> HTML Editor
                            </button>
                            <button type="button" id="tabPreview" onclick="toggleEditorTab('preview')" class="px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-200">
                                <i class="fa-solid fa-eye mr-1"></i> লাইভ প্রিভিউ (Live Preview)
                            </button>
                        </div>

                        <div id="editorContainer" class="p-3 bg-slate-950">
                            <textarea id="pageContent" name="content" rows="18" required placeholder="এখানে HTML ফরম্যাটে পলিসি কনটেন্ট লিখুন..." class="w-full bg-slate-950 border-0 focus:ring-0 text-sm text-slate-200 font-mono leading-relaxed resize-y">{{ old('content', $page->content) }}</textarea>
                        </div>

                        <div id="previewContainer" class="p-6 bg-slate-900 hidden min-h-[350px] overflow-y-auto max-h-[500px]">
                            <div id="previewContent" class="legal-content text-slate-300 text-sm leading-relaxed space-y-4">
                                {{-- Rendered preview inserted by JS --}}
                            </div>
                        </div>
                    </div>
                    @error('content') <p class="text-[11px] text-rose-400">{{ $message }}</p> @enderror
                </div>

                {{-- Row 3: Meta Title, Description, Last Updated Date, Sort Order & Active --}}
                <div class="pt-4 border-t border-slate-800 grid grid-cols-1 md:grid-cols-12 gap-5">
                    <div class="md:col-span-6 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            SEO Meta Title
                        </label>
                        <input type="text" name="meta_title" value="{{ old('meta_title', $page->meta_title) }}" placeholder="Privacy Policy - Subeditor24" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>

                    <div class="md:col-span-6 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            সর্বশেষ আপডেটের তারিখ (Last Updated Label)
                        </label>
                        <input type="text" name="last_updated_date" value="{{ old('last_updated_date', $page->last_updated_date ?: date('F d, Y')) }}" placeholder="October 05, 2026" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>

                    <div class="md:col-span-8 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            SEO Meta Description
                        </label>
                        <textarea name="meta_description" rows="2" placeholder="সার্চ ইঞ্জিন ও ফেসবুক প্রিভিউয়ের জন্য সংক্ষিপ্ত মেটা বিবরণ" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">{{ old('meta_description', $page->meta_description) }}</textarea>
                    </div>

                    <div class="md:col-span-2 space-y-1.5">
                        <label class="block text-xs font-bold text-slate-300">
                            ক্রম নম্বর (Sort Order)
                        </label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $page->sort_order) }}" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-4 py-2 text-xs text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                    </div>

                    <div class="md:col-span-2 flex items-center pt-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 rounded border-slate-700 focus:ring-indigo-500">
                            <span class="text-xs font-bold text-white">Active</span>
                        </label>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="pt-6 border-t border-slate-800 flex items-center justify-between flex-wrap gap-4">
                    <a href="{{ route('admin.legal-pages.index') }}" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition">
                        বাতিল করুন
                    </a>

                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-extrabold text-xs rounded-xl shadow-lg shadow-indigo-600/30 transition transform hover:-translate-y-0.5 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> পলিসি পেজ সংরক্ষণ করুন
                    </button>
                </div>

            </div>
        </form>

    </div>
</div>

<script>
function insertTag(openTag, closeTag) {
    const textarea = document.getElementById('pageContent');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end);
    const replacement = openTag + (selected || 'লেখা লিখুন') + closeTag;
    textarea.value = text.substring(0, start) + replacement + text.substring(end);
    textarea.focus();
    textarea.setSelectionRange(start + openTag.length, start + openTag.length + (selected ? selected.length : 'লেখা লিখুন'.length));
}

function toggleEditorTab(tab) {
    const editTab = document.getElementById('tabEdit');
    const previewTab = document.getElementById('tabPreview');
    const editorContainer = document.getElementById('editorContainer');
    const previewContainer = document.getElementById('previewContainer');
    const previewContent = document.getElementById('previewContent');
    const textarea = document.getElementById('pageContent');

    if (tab === 'preview') {
        editTab.className = "px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-200";
        previewTab.className = "px-4 py-2 text-xs font-bold text-indigo-400 border-b-2 border-indigo-500";
        editorContainer.classList.add('hidden');
        previewContainer.classList.remove('hidden');
        previewContent.innerHTML = textarea.value;
    } else {
        editTab.className = "px-4 py-2 text-xs font-bold text-indigo-400 border-b-2 border-indigo-500";
        previewTab.className = "px-4 py-2 text-xs font-bold text-slate-400 hover:text-slate-200";
        editorContainer.classList.remove('hidden');
        previewContainer.classList.add('hidden');
    }
}
</script>
@endsection
