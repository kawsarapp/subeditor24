@extends('layouts.app')

@section('content')
{{-- 🔥 ১. TinyMCE স্ক্রিপ্ট যুক্ত করা হলো --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap');
    .font-bangla { font-family: 'Hind Siliguri', sans-serif; }
</style>

<div class="max-w-4xl mx-auto font-bangla">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                ✍️ নতুন খবর তৈরি করুন
            </h2>
            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1">খবরের শিরোনাম, ছবি ও বিস্তারিত বিবরণ লিখুন</p>
        </div>
        <a href="{{ route('news.index') }}" class="text-xs font-extrabold text-slate-500 hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-4 py-2 rounded-xl transition shadow-sm">
            ← ফিডে ফিরে যান
        </a>
    </div>

    {{-- 💾 Auto-Save Recovery Alert Banner --}}
    <div id="autoSaveRecoveryAlert" class="hidden bg-amber-50 dark:bg-amber-950/70 border border-amber-300 dark:border-amber-700/80 p-4 mb-6 rounded-2xl shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <h4 class="text-xs font-extrabold text-amber-900 dark:text-amber-200 uppercase tracking-wide">অসংরক্ষিত ড্রাফট উদ্ধার!</h4>
                <p class="text-xs font-semibold text-amber-800 dark:text-amber-300 mt-0.5" id="autoSaveRecoveryInfo">পূর্বে সংরক্ষিত ড্রাফট পাওয়া গেছে।</p>
            </div>
        </div>
        <div class="flex items-center gap-2 self-end sm:self-center">
            <button type="button" onclick="restoreAutoSaveDraft()" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition shadow-sm cursor-pointer">
                📂 রিকভার করুন
            </button>
            <button type="button" onclick="discardAutoSaveDraft()" class="px-3.5 py-1.5 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold hover:bg-slate-50 transition cursor-pointer">
                ✕ মুছুন
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm p-6 sm:p-8 border border-slate-200/90 dark:border-slate-800">
        <form id="createNewsForm" action="{{ route('news.store-custom') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Title --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">খবরের শিরোনাম (Title) <span class="text-rose-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <span id="titleDuplicateCheckingSpinner" class="hidden text-[10px] font-bold text-slate-400 animate-pulse flex items-center gap-1">
                            <i class="fa-solid fa-spinner fa-spin text-indigo-500"></i> ডুপ্লিকেট চেক হচ্ছে...
                        </span>
                        <button type="button" onclick="generateCreateViralHeadlines()" id="btnCreateViralHeadlines" class="text-xs font-bold px-3 py-1 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i> ✨ ৩টি AI ভাইরাল শিরোনাম
                        </button>
                    </div>
                </div>
                <input type="text" name="title" id="newsTitleInput" oninput="debouncedCheckTitleDuplicates()" required value="{{ old('title', request('title')) }}"
                    class="w-full border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 focus:ring-2 focus:ring-indigo-500 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white font-bangla text-lg font-bold placeholder-slate-400 focus:bg-white dark:focus:bg-slate-900 transition"
                    placeholder="এখানে আকর্ষণীয় শিরোনাম লিখুন...">
                
                {{-- 3-Option Viral Headline Suggestions Box --}}
                <div id="createViralHeadlineBox" class="hidden mt-3 p-4 bg-gradient-to-br from-indigo-50/90 to-purple-50/90 dark:from-slate-800 dark:to-slate-850 border border-indigo-200/70 dark:border-slate-700 rounded-2xl space-y-2.5 transition-all">
                    <div class="flex items-center justify-between pb-2 border-b border-indigo-100 dark:border-slate-700">
                        <span class="text-xs font-black text-indigo-900 dark:text-indigo-200 flex items-center gap-1.5">
                            <i class="fa-solid fa-sparkles text-indigo-600"></i> AI প্রস্তাবিত ভাইরাল শিরোনাম (১-ক্লিকে সিলেক্ট করুন):
                        </span>
                        <button type="button" onclick="document.getElementById('createViralHeadlineBox').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs cursor-pointer">✕</button>
                    </div>
                    <div class="grid grid-cols-1 gap-2 text-xs" id="createViralHeadlineContainer">
                        {{-- Dynamically injected via JS --}}
                    </div>
                </div>
                
                {{-- ⚠️ Real-time Duplicate Alert --}}
                <div id="createTitleDuplicateAlert" class="hidden mt-2 p-3 bg-amber-50 dark:bg-amber-950/70 border border-amber-300 dark:border-amber-800/60 rounded-xl text-xs font-bold text-amber-900 dark:text-amber-200 flex items-start gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 mt-0.5"></i>
                    <div class="flex-1">
                        <span id="createTitleDuplicateText">এই শিরোনামের অনুরূপ আরেকটি খবর ইতিমধ্যে তালিকায় রয়েছে।</span>
                    </div>
                </div>
            </div>

            {{-- Image Upload Section --}}
            <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Option A: Upload File --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">📷 ছবি আপলোড করুন</label>
                    <input type="file" name="image_file" accept="image/*"
                        class="w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2 text-xs focus:ring-2 focus:ring-indigo-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-950 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 cursor-pointer">
                    <p class="text-[10px] font-semibold text-slate-400 mt-1.5">ফরম্যাট: JPG, PNG, WEBP (সর্বোচ্চ: ৫MB)</p>
                </div>

                {{-- Option B: Image Link --}}
                <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">🔗 অথবা, ছবির লিংক দিন</label>
                    <input type="url" name="image_url" id="newsImageUrlInput"
                        class="w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 focus:ring-2 focus:ring-indigo-500 text-xs bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 placeholder-slate-400"
                        placeholder="https://example.com/image.jpg">
                    <p class="text-[10px] font-semibold text-slate-400 mt-1.5">অনলাইন ছবির সরাসরি লিঙ্ক পেস্ট করুন</p>
                </div>
            </div>

            {{-- Content --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">বিস্তারিত খবর (Content)</label>
                    <span id="autoSaveStatusBadge" class="text-[10px] font-bold text-slate-400 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> অটো-সেভ সক্রিয়
                    </span>
                </div>
                
                {{-- TinyMCE Content Textarea --}}
                <textarea name="content" id="newsContent" rows="15"
                    class="w-full border border-slate-200 dark:border-slate-700 rounded-2xl p-4 focus:ring-2 focus:ring-indigo-500 font-bangla"
                    placeholder="এখানে বিস্তারিত লিখুন..."></textarea>
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                {{-- ১. ড্রাফট বাটন --}}
                <button type="submit" class="flex-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 py-3.5 rounded-2xl font-bold text-xs transition flex items-center justify-center gap-2 cursor-pointer">
                    💾 ড্রাফটে সেভ করুন
                </button>

                {{-- ২. AI বাটন --}}
                <button type="submit" name="process_ai" value="1" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3.5 rounded-2xl font-bold text-xs shadow-md shadow-indigo-500/20 transition flex justify-center items-center gap-2 cursor-pointer">
                    🤖 AI রিরাইট + সেভ 
                </button>

                {{-- ৩. ডাইরেক্ট পাবলিশ বাটন --}}
                <button type="submit" name="direct_publish" value="1" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white py-3.5 rounded-2xl font-bold text-xs shadow-md shadow-emerald-500/20 transition flex justify-center items-center gap-2 cursor-pointer">
                    🚀 সরাসরি পাবলিশ
                </button>
            </div>
			
        </form>
    </div>
</div>

{{-- 🔥 ৩. TinyMCE ইনিশিয়ালাইজেশন & ড্রাফট অটো-সেভ স্ক্রিপ্ট --}}
<script>
    const AUTOSAVE_KEY = 'subeditor24_news_create_draft';

    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: '#newsContent',
            height: 400,
            plugins: 'link lists code table preview wordcount',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code preview',
            menubar: false,
            statusbar: true,
            branding: false,
            setup: function(editor) {
                editor.on('input change keyup', () => {
                    triggerAutoSaveDebounced();
                });
            }
        });

        checkAutoSaveRecovery();
        startAutoSaveInterval();

        // On form submit, clear autosaved draft
        const form = document.getElementById('createNewsForm');
        if (form) {
            form.addEventListener('submit', () => {
                localStorage.removeItem(AUTOSAVE_KEY);
            });
        }
    });

    // --- Check for recoverable draft ---
    function checkAutoSaveRecovery() {
        try {
            const saved = localStorage.getItem(AUTOSAVE_KEY);
            if (!saved) return;

            const draft = JSON.parse(saved);
            const titleInput = document.getElementById('newsTitleInput');
            
            // If current title input is already filled from server old() or query param, don't force alert
            if (titleInput && titleInput.value.trim() !== '') return;

            if (draft.title || draft.content) {
                const alertEl = document.getElementById('autoSaveRecoveryAlert');
                const infoEl = document.getElementById('autoSaveRecoveryInfo');
                if (alertEl) {
                    if (infoEl) infoEl.innerText = `পূর্বে সংরক্ষিত ড্রাফট (${draft.time || 'কিছুক্ষণ আগে'}): "${(draft.title || 'শিরোনামবিহীন').substring(0, 40)}..."`;
                    alertEl.classList.remove('hidden');
                }
            }
        } catch (e) {
            console.error(e);
        }
    }

    function restoreAutoSaveDraft() {
        try {
            const saved = localStorage.getItem(AUTOSAVE_KEY);
            if (!saved) return;
            const draft = JSON.parse(saved);

            const titleInput = document.getElementById('newsTitleInput');
            const imageInput = document.getElementById('newsImageUrlInput');

            if (titleInput && draft.title) titleInput.value = draft.title;
            if (imageInput && draft.image_url) imageInput.value = draft.image_url;
            if (tinymce.get('newsContent') && draft.content) {
                tinymce.get('newsContent').setContent(draft.content);
            } else {
                document.getElementById('newsContent').value = draft.content || '';
            }

            document.getElementById('autoSaveRecoveryAlert').classList.add('hidden');
            if (window.showToast) window.showToast('✅ ড্রাফট সফলভাবে পুনরুদ্ধার করা হয়েছে!', 'success');
        } catch (e) {
            console.error(e);
        }
    }

    function discardAutoSaveDraft() {
        localStorage.removeItem(AUTOSAVE_KEY);
        document.getElementById('autoSaveRecoveryAlert').classList.add('hidden');
        if (window.showToast) window.showToast('ড্রাফট মুছে ফেলা হয়েছে', 'info');
    }

    // --- Auto-save engine ---
    let autoSaveTimer = null;

    function triggerAutoSaveDebounced() {
        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(performAutoSave, 3000);
    }

    function performAutoSave() {
        const titleInput = document.getElementById('newsTitleInput');
        const imageInput = document.getElementById('newsImageUrlInput');
        const title = titleInput ? titleInput.value.trim() : '';
        const image_url = imageInput ? imageInput.value.trim() : '';
        const content = tinymce.get('newsContent') ? tinymce.get('newsContent').getContent() : (document.getElementById('newsContent') ? document.getElementById('newsContent').value : '');

        if (!title && !content) return;

        const draft = {
            title: title,
            image_url: image_url,
            content: content,
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        };

        try {
            localStorage.setItem(AUTOSAVE_KEY, JSON.stringify(draft));
            const badge = document.getElementById('autoSaveStatusBadge');
            if (badge) {
                badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-ping"></span> সংরক্ষিত (${draft.time})`;
                setTimeout(() => {
                    badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> অটো-সেভ সক্রিয়`;
                }, 2500);
            }
        } catch (e) {
            console.error(e);
        }
    }

    function startAutoSaveInterval() {
        // Auto-save every 10 seconds in background
        setInterval(performAutoSave, 10000);
        
        const titleInput = document.getElementById('newsTitleInput');
        const imageInput = document.getElementById('newsImageUrlInput');
        if (titleInput) titleInput.addEventListener('input', triggerAutoSaveDebounced);
        if (imageInput) imageInput.addEventListener('input', triggerAutoSaveDebounced);
    }

    // ==========================================================
    // 🔍 REAL-TIME SMART TITLE DUPLICATE CHECKER
    // ==========================================================
    let titleDupTimer = null;

    function debouncedCheckTitleDuplicates() {
        clearTimeout(titleDupTimer);
        titleDupTimer = setTimeout(checkTitleDuplicates, 600);
    }

    function checkTitleDuplicates() {
        const titleInput = document.getElementById('newsTitleInput');
        const title = titleInput ? titleInput.value.trim() : '';
        const spinner = document.getElementById('titleDuplicateCheckingSpinner');
        const alertBox = document.getElementById('createTitleDuplicateAlert');
        const alertText = document.getElementById('createTitleDuplicateText');

        if (!title || title.length < 5) {
            if (alertBox) alertBox.classList.add('hidden');
            if (spinner) spinner.classList.add('hidden');
            return;
        }

        if (spinner) spinner.classList.remove('hidden');

        fetch("{{ route('news.check-duplicates') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ title: title })
        })
        .then(res => res.json())
        .then(data => {
            if (spinner) spinner.classList.add('hidden');
            if (data.success && data.duplicates && data.duplicates.length > 0) {
                const top = data.duplicates[0];
                if (alertText) {
                    alertText.innerHTML = `⚠️ <strong>সতর্কতা:</strong> অনুরূপ খবর পাওয়া গেছে: "<u>${top.title}</u>" (${top.website_name} - ${top.similarity}% মিল)। ডুপ্লিকেট এড়াতে শিরোনাম বা কনটেন্ট পরিবর্তন করুন।`;
                }
                if (alertBox) alertBox.classList.remove('hidden');
            } else {
                if (alertBox) alertBox.classList.add('hidden');
            }
        })
        .catch(() => {
            if (spinner) spinner.classList.add('hidden');
        });
    }

    // ==========================================================
    // ✨ 1-CLICK 3-OPTION VIRAL HEADLINE GENERATOR
    // ==========================================================
    function generateCreateViralHeadlines() {
        const titleInput = document.getElementById('newsTitleInput');
        const currentTitle = titleInput ? titleInput.value.trim() : '';
        let contentText = '';
        if (tinymce.get('newsContent')) {
            contentText = tinymce.get('newsContent').getContent({ format: 'text' }).trim();
        } else {
            const contentEl = document.getElementById('newsContent');
            if (contentEl) contentText = contentEl.value.trim();
        }

        if (!currentTitle && !contentText) {
            alert('অনুগ্রহ করে শিরোনাম বা কন্টেন্ট লিখুন!');
            return;
        }

        const btn = document.getElementById('btnCreateViralHeadlines');
        const origBtnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>জেনারেট হচ্ছে...</span>`;

        fetch("{{ route('news.generate-headlines') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: currentTitle,
                content: contentText
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.headlines) {
                const box = document.getElementById('createViralHeadlineBox');
                const container = document.getElementById('createViralHeadlineContainer');
                if (box && container) {
                    const h = data.headlines;
                    const items = [
                        { type: '💡 তথ্যবহুল ও প্রমিত (Informative)', text: h.informative, color: 'border-blue-300 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200' },
                        { type: '🔥 ভাইরাল / হাই-সিটিআর (Viral & High-CTR)', text: h.viral, color: 'border-purple-300 dark:border-purple-800 bg-purple-50/80 dark:bg-purple-950/40 text-purple-900 dark:text-purple-200' },
                        { type: '⚡ ছোট ও ব্রেকিং (Short & Breaking)', text: h.breaking, color: 'border-rose-300 dark:border-rose-800 bg-rose-50/80 dark:bg-rose-950/40 text-rose-900 dark:text-rose-200' }
                    ];

                    container.innerHTML = items.map(item => `
                        <div class="p-3 rounded-xl border ${item.color} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 transition hover:shadow-sm">
                            <div class="flex-1">
                                <span class="text-[10px] font-black uppercase tracking-wider block mb-0.5 opacity-80">${item.type}</span>
                                <p class="text-xs font-bold leading-snug font-bangla">${item.text}</p>
                            </div>
                            <button type="button" onclick="applyCreateViralHeadline('${item.text.replace(/'/g, "\\'")}')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shrink-0 shadow-sm flex items-center gap-1 transition cursor-pointer">
                                <span>ব্যবহার করুন</span> ↵
                            </button>
                        </div>
                    `).join('');

                    box.classList.remove('hidden');
                }
            } else {
                alert(data.message || 'শিরোনাম তৈরি করতে সমস্যা হয়েছে!');
            }
        })
        .catch(err => {
            console.error('Viral Headlines Error:', err);
            alert('সার্ভারে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origBtnText;
        });
    }

    function applyCreateViralHeadline(headlineText) {
        const titleInput = document.getElementById('newsTitleInput');
        if (titleInput) {
            titleInput.value = headlineText;
            debouncedCheckTitleDuplicates();
            const box = document.getElementById('createViralHeadlineBox');
            if (box) box.classList.add('hidden');
        }
    }
</script>
@endsection