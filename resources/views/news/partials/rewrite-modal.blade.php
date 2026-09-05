{{-- PUBLISH & SIDE-BY-SIDE MODAL --}}
<div id="rewriteModal" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-50 backdrop-blur-md transition-opacity">
    <div id="rewriteModalContainer" class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-6xl mx-4 overflow-hidden flex flex-col max-h-[92vh] border border-slate-200 dark:border-slate-800 transition-all duration-300">
        
        {{-- Header & View Mode Switcher --}}
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200 dark:border-slate-700/80 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black shadow-md shadow-indigo-500/20">
                    <i class="fa-solid fa-pen-nib text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-white font-bangla">নিউজ এডিট, তুলনা ও পাবলিশ</h3>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400" id="modalSourceBadge">সোর্স: লোড হচ্ছে...</p>
                </div>
            </div>

            {{-- View Switcher Buttons --}}
            <div class="flex items-center gap-1.5 bg-slate-200/70 dark:bg-slate-800 p-1 rounded-2xl border border-slate-300/60 dark:border-slate-700/60">
                <button type="button" onclick="switchModalView('editor')" id="viewBtnEditor" class="modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm transition-all cursor-pointer">
                    <i class="fa-solid fa-pen-to-square"></i> <span class="hidden md:inline">এডিটর</span>
                </button>
                <button type="button" onclick="switchModalView('sidebyside')" id="viewBtnSideBySide" class="modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all cursor-pointer">
                    <i class="fa-solid fa-columns"></i> <span class="hidden md:inline">পাশাপাশি তুলনা</span>
                </button>
                <button type="button" onclick="switchModalView('social')" id="viewBtnSocial" class="modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all cursor-pointer">
                    <i class="fa-solid fa-share-nodes"></i> <span class="hidden md:inline">সোশ্যাল প্রিভিউ</span>
                </button>
            </div>

            <button onclick="closeRewriteModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1.5 rounded-xl transition cursor-pointer">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- ⚠️ Smart Duplicate News Detection Alert Banner --}}
        <div id="modalDuplicateAlert" class="hidden px-6 py-3 bg-amber-50 dark:bg-amber-950/70 border-b border-amber-200 dark:border-amber-800/60 text-xs font-bold text-amber-900 dark:text-amber-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-amber-600 dark:text-amber-400 text-sm"></i>
                <span id="modalDuplicateAlertText">একই খবরের আরও তথ্য পাওয়া গেছে।</span>
            </div>
            <button type="button" onclick="toggleDuplicateDetails()" class="text-indigo-700 dark:text-indigo-300 underline text-xs font-bold cursor-pointer">
                তালিকা দেখুন ▾
            </button>
        </div>
        <div id="modalDuplicateDetailsList" class="hidden px-6 py-3 bg-amber-100/50 dark:bg-amber-950/40 border-b border-amber-200 dark:border-amber-800/40 space-y-1.5 text-xs font-semibold text-amber-900 dark:text-amber-300">
            {{-- Injected dynamically via JS --}}
        </div>

        {{-- Modal Main Body --}}
        <div class="p-6 overflow-y-auto flex-1 bg-slate-50/50 dark:bg-slate-900/50 flex flex-col lg:flex-row gap-6 font-bangla">
            
            {{-- 🔀 SIDE-BY-SIDE ORIGINAL SOURCE PANEL (Hidden in pure editor mode, visible in side-by-side mode) --}}
            <div id="sideBySideOriginalPanel" class="hidden w-full lg:w-1/2 flex-col gap-4 bg-white dark:bg-slate-900 p-5 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700" id="sideOriginalSourceTag">
                            মূল সোর্স
                        </span>
                        <h4 class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase">মূল কাঁচা খবর (Original Raw)</h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <a id="sideOriginalLinkBtn" href="#" target="_blank" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            সোর্স লিঙ্ক 🔗
                        </a>
                        <button type="button" onclick="copyOriginalContent()" class="text-[11px] font-bold px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg text-slate-700 dark:text-slate-300 transition cursor-pointer" title="কপি করুন">
                            <i class="fa-solid fa-copy"></i> কপি
                        </button>
                        <button type="button" onclick="insertOriginalToEditor()" class="text-[11px] font-bold px-2 py-1 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 rounded-lg transition cursor-pointer" title="এডিটরে ইনসার্ট করুন">
                            ➕ এডিটরে নিন
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">মূল শিরোনাম</label>
                    <h3 id="sideOriginalTitle" class="text-base font-extrabold text-slate-900 dark:text-white leading-snug font-bangla"></h3>
                </div>

                <div class="flex-1 flex flex-col">
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">মূল বিবরণ (Raw Article Text)</label>
                    <div id="sideOriginalContent" class="flex-1 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm leading-relaxed overflow-y-auto max-h-[500px] whitespace-pre-line font-bangla select-text">
                        লোড হচ্ছে...
                    </div>
                </div>
            </div>

            {{-- 📝 EDITOR & AI CONTENT PANEL --}}
            <div id="editorMainPanel" class="flex-1 flex flex-col">
                <input type="hidden" id="previewNewsId">
                
                {{-- Feature Image Card --}}
                <div class="mb-5 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Feature Image (ফিচার ছবি)</label>
                    <div class="flex gap-4 items-start">
                        <div class="w-24 h-24 flex-shrink-0 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 relative group">
                            <img id="previewImageDisplay" src="" class="w-full h-full object-cover">
                            <button type="button" onclick="resetImage()" class="absolute top-1 right-1 bg-rose-600 text-white p-1 rounded-full text-xs opacity-0 group-hover:opacity-100 transition shadow cursor-pointer">✕</button>
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="file" id="newImageFile" onchange="previewSelectedImage(this)" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-950 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 cursor-pointer">
                            <div class="text-[10px] font-bold text-slate-400 text-center">- অথবা ছবির লিঙ্ক দিন -</div>
                            <input type="url" id="newImageUrl" oninput="previewImageUrl(this.value)" placeholder="https://example.com/image.jpg" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2 text-xs focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-100">
                        </div>
                    </div>
                </div>
                
                {{-- Title --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">খবরের শিরোনাম (Title)</label>
                        <button type="button" onclick="generateViralHeadlinesModal()" id="btnGenerateViralHeadlines" class="text-xs font-bold px-3 py-1 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i> ✨ ৩টি AI ভাইরাল শিরোনাম
                        </button>
                    </div>
                    <input type="text" id="previewTitle" oninput="syncSocialCardPreview()" class="w-full border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 focus:ring-2 focus:ring-indigo-500 font-bangla text-lg font-bold text-slate-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm transition">
                    
                    {{-- 3-Option Viral Headline Suggestions Box --}}
                    <div id="viralHeadlineSuggestionsBox" class="hidden mt-3 p-4 bg-gradient-to-br from-indigo-50/90 to-purple-50/90 dark:from-slate-800 dark:to-slate-850 border border-indigo-200/70 dark:border-slate-700 rounded-2xl space-y-2.5 transition-all">
                        <div class="flex items-center justify-between pb-2 border-b border-indigo-100 dark:border-slate-700">
                            <span class="text-xs font-black text-indigo-900 dark:text-indigo-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-sparkles text-indigo-600"></i> AI প্রস্তাবিত ভাইরাল শিরোনাম (১-ক্লিকে সিলেক্ট করুন):
                            </span>
                            <button type="button" onclick="document.getElementById('viralHeadlineSuggestionsBox').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs cursor-pointer">✕</button>
                        </div>
                        <div class="grid grid-cols-1 gap-2 text-xs" id="viralHeadlineCardsContainer">
                            {{-- Dynamically injected via JS --}}
                        </div>
                    </div>
                </div>

                {{-- News Content (TinyMCE) --}}
                <div class="mb-5">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">খবরের বিস্তারিত বিবরণ (Content)</label>
                    <textarea id="previewContent" rows="15" class="w-full border border-slate-200 dark:border-slate-700 rounded-2xl"></textarea>
                </div>
            </div>

            {{-- 📱 SOCIAL MEDIA & SEARCH PREVIEW TAB PANEL (Active in social mode) --}}
            <div id="socialPreviewTabPanel" class="hidden flex-1 flex-col gap-5">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-indigo-500"></i> লাইভ সোশ্যাল কার্ড ও সার্চ প্রিভিউ
                        </h4>
                        <span class="text-xs font-semibold text-slate-400">রিয়েলটাইম প্রিভিউ</span>
                    </div>

                    {{-- 📘 Facebook Preview Card --}}
                    <div>
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="fa-brands fa-facebook text-base"></i> Facebook Feed Card
                        </span>
                        <div class="max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl overflow-hidden shadow-md">
                            <div class="p-3.5 flex items-center gap-2.5">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-black flex items-center justify-center text-sm shadow">
                                    {{ substr(auth()->user()->name ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ optional(optional(auth()->user())->settings)->site_name ?? 'নিউজ পোর্টাল' }}</span>
                                        <i class="fa-solid fa-circle-check text-blue-500 text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] text-slate-400">Just now · 🌐</span>
                                </div>
                            </div>
                            <p class="px-3.5 pb-2 text-xs font-semibold text-slate-800 dark:text-slate-100 line-clamp-2" id="fbPreviewPostText">খবরের বিবরণ লোড হচ্ছে...</p>
                            <div class="aspect-video w-full bg-slate-100 dark:bg-slate-700 overflow-hidden relative">
                                <img id="fbPreviewImage" src="" class="w-full h-full object-cover">
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-850 border-t border-slate-100 dark:border-slate-700/80">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider" id="fbPreviewDomain">YOURSITE.COM</span>
                                <h5 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug mt-0.5" id="fbPreviewTitle">খবরের শিরোনাম</h5>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5" id="fbPreviewDesc">খবরের সারসংক্ষেপ...</p>
                            </div>
                        </div>
                    </div>

                    {{-- 🐦 Twitter / X Card Preview --}}
                    <div>
                        <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="fa-brands fa-x-twitter text-base"></i> Twitter / X Large Summary Card
                        </span>
                        <div class="max-w-lg bg-black text-white rounded-2xl overflow-hidden border border-slate-800 shadow-md">
                            <div class="aspect-video w-full bg-slate-900 overflow-hidden relative">
                                <img id="twitterPreviewImage" src="" class="w-full h-full object-cover">
                                <span class="absolute bottom-2 left-2 bg-black/80 backdrop-blur-md px-2 py-0.5 rounded text-[10px] font-bold text-white uppercase" id="twitterPreviewDomain">yoursite.com</span>
                            </div>
                            <div class="p-3 bg-slate-950">
                                <h5 class="text-xs font-bold line-clamp-1 leading-tight text-slate-100" id="twitterPreviewTitle">খবরের শিরোনাম</h5>
                                <p class="text-[11px] text-slate-400 line-clamp-2 mt-1" id="twitterPreviewDesc">খবরের সারসংক্ষেপ...</p>
                            </div>
                        </div>
                    </div>

                    {{-- 🔍 Google Search Snippet Preview --}}
                    <div>
                        <span class="text-xs font-bold text-red-600 dark:text-red-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <i class="fa-brands fa-google text-base"></i> Google Search Snippet
                        </span>
                        <div class="max-w-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-4 shadow-sm space-y-1">
                            <div class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                                <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-[10px] font-bold">🌐</span>
                                <span class="text-[11px]" id="googlePreviewUrl">https://yoursite.com › news › ...</span>
                            </div>
                            <h4 class="text-sm font-bold text-indigo-700 dark:text-indigo-400 hover:underline cursor-pointer line-clamp-1" id="googlePreviewTitle">
                                খবরের শিরোনাম
                            </h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2" id="googlePreviewDesc">
                                খবরের মেটা বিবরণ এখানে প্রদর্শিত হবে...
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🚀 RIGHT SIDEBAR: SEO & METADATA --}}
            <div id="editorSidebarPanel" class="w-full lg:w-80 flex flex-col gap-5 h-auto lg:h-[92vh] lg:overflow-y-auto lg:sticky lg:top-4 pr-2">
                {{-- 🚀 SEO & Meta Data Card --}}
                <div class="bg-white dark:bg-slate-900 border border-indigo-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex-shrink-0">
                    <div class="bg-indigo-600 text-white px-4 py-3 flex justify-between items-center">
                        <h5 class="m-0 font-bold text-xs flex items-center gap-2">🚀 SEO Score</h5>
                        <span class="bg-white text-indigo-700 px-2 py-0.5 rounded text-xs font-bold">
                            <span id="seo-score">0</span>/100
                        </span>
                    </div>
                    <div class="p-4">
                        <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 mb-4">
                            <div id="seo-progress" class="bg-red-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                        </div>
                        <div class="mb-3">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Focus Keywords</label>
                            <input type="text" id="focus_keyword" oninput="syncSocialCardPreview()" class="seo-input w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2 text-xs focus:bg-white dark:focus:bg-slate-900 text-slate-800 dark:text-slate-100" placeholder="e.g. বাংলাদেশ, রাজনীতি">
                        </div>
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Meta Description <span class="text-slate-400 font-normal">(<span id="meta-count">0</span>/160)</span></label>
                            <textarea id="meta_description" oninput="syncSocialCardPreview()" class="seo-input w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2 text-xs focus:bg-white dark:focus:bg-slate-900 text-slate-800 dark:text-slate-100 resize-none" rows="3" maxlength="160" placeholder="নিউজের মূল সারসংক্ষেপ..."></textarea>
                        </div>
                        <hr class="my-4 border-slate-100 dark:border-slate-800">
                        <h6 class="text-xs font-bold text-slate-800 dark:text-slate-200 mb-2">🔗 ইন্টারনাল লিংক সাজেশন</h6>
                        <div class="flex gap-2 mb-2">
                            <input type="text" id="link-search-keyword" class="flex-1 border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2 text-xs focus:bg-white dark:focus:bg-slate-900 text-slate-800 dark:text-slate-100" placeholder="কী-ওয়ার্ড লিখুন...">
                            <button type="button" class="bg-slate-900 hover:bg-slate-800 text-white px-3 py-2 rounded-xl text-xs font-bold transition cursor-pointer" onclick="fetchRelatedLinks()">খুঁজুন</button>
                        </div>
                        <div id="link-suggestions" class="flex flex-col gap-2 mb-3 hidden max-h-48 overflow-y-auto pr-1"></div>
                    </div>
                </div>

                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_fact_check'))
                {{-- 🔍 Fact Check & Uniqueness Card --}}
                <div class="bg-white dark:bg-slate-900 border border-rose-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex-shrink-0">
                    <div class="bg-gradient-to-r from-rose-600 to-pink-600 text-white px-4 py-3 flex justify-between items-center shadow-sm">
                        <h5 class="m-0 font-bold text-xs flex items-center gap-1.5"><i class="fa-solid fa-square-poll-horizontal text-sm"></i> Fact & Plagiarism</h5>
                        <span id="factcheck-status-badge" class="bg-white text-rose-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider hidden shadow-sm">
                            Verified
                        </span>
                    </div>
                    <div class="p-4 space-y-4">
                        <button type="button" id="btn-run-factcheck" class="w-full bg-slate-900 hover:bg-slate-800 text-white py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-sm cursor-pointer" onclick="runFactCheckAndPlagiarism()">
                            <i class="fa-solid fa-circle-check"></i> মৌলিকতা ও তথ্য যাচাই করুন
                        </button>

                        <div id="factcheck-skeleton" class="space-y-3 hidden">
                            <div class="flex justify-between items-center">
                                <div class="h-3 w-2/3 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
                                <div class="h-3 w-10 bg-slate-200 dark:bg-slate-700 rounded animate-pulse"></div>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 animate-pulse"></div>
                        </div>

                        <div id="factcheck-results" class="space-y-4 hidden">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Uniqueness (মৌলিকতা)</span>
                                    <span id="uniqueness-score" class="text-xs font-bold text-emerald-600">100%</span>
                                </div>
                                <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden border border-slate-200 dark:border-slate-700">
                                    <div id="uniqueness-progress" class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: 100%"></div>
                                </div>
                            </div>
                            <div class="bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200 dark:border-slate-700">
                                <label class="block text-[10px] font-bold text-slate-500 mb-1 uppercase tracking-wider">এআই রিপোর্ট (Report)</label>
                                <p id="factcheck-report-text" class="text-[11px] text-slate-600 dark:text-slate-300 font-bangla leading-relaxed whitespace-pre-line"></p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Categories & Hashtags --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-4 flex-shrink-0">
                    <div class="mb-4">
                        <label class="block text-xs font-extrabold uppercase text-slate-700 dark:text-slate-300 mb-2">Hashtags</label>
                        <input type="text" id="previewHashtags" placeholder="#News #Bangladesh" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2.5 text-xs text-indigo-600 font-bold focus:bg-white dark:focus:bg-slate-900">
                    </div>
                    <div class="mb-2">
                        <label class="block text-xs font-extrabold uppercase text-indigo-600 dark:text-indigo-400 mb-1">Primary Category</label>
                        <select id="previewCategory" class="wp-cat-dropdown w-full border border-slate-200 dark:border-slate-700 rounded-xl p-2.5 text-xs font-bold text-slate-800 dark:text-slate-100 bg-white dark:bg-slate-800">
                            <option value="">Loading...</option>
                        </select>
                    </div>
                    <label class="text-xs font-bold text-slate-400 block mb-1 mt-3">Additional Categories</label>
                    <div class="grid grid-cols-2 gap-2 p-2 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                        @for ($i = 1; $i <= 4; $i++)
                            <select id="extraCategory{{ $i }}" class="wp-cat-dropdown w-full border border-slate-200 dark:border-slate-700 rounded-lg p-1.5 text-[11px] font-bold bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-200">
                                <option value="">-- Select --</option>
                            </select>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Modal Footer Actions --}}
        <div class="bg-white dark:bg-slate-900 px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
            <button onclick="closeRewriteModal()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition cursor-pointer">✕ Cancel</button>
            
            {{-- ⏰ Publishing Schedule Options --}}
            <div class="flex flex-wrap items-center gap-2 bg-slate-50 dark:bg-slate-800/60 p-1.5 rounded-2xl border border-slate-200 dark:border-slate-700">
                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300 has-[:checked]:bg-white dark:has-[:checked]:bg-slate-900 has-[:checked]:text-indigo-600 dark:has-[:checked]:text-indigo-400 has-[:checked]:shadow-sm transition select-none">
                    <input type="radio" name="modal_schedule_type" value="instant" checked onchange="toggleModalScheduleInput(this.value)" class="hidden">
                    <i class="fa-solid fa-bolt text-amber-500"></i> সরাসরি পাবলিশ
                </label>
                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300 has-[:checked]:bg-white dark:has-[:checked]:bg-slate-900 has-[:checked]:text-indigo-600 dark:has-[:checked]:text-indigo-400 has-[:checked]:shadow-sm transition select-none">
                    <input type="radio" name="modal_schedule_type" value="drip" onchange="toggleModalScheduleInput(this.value)" class="hidden">
                    <i class="fa-solid fa-droplet text-blue-500"></i> অটো-ড্রিপ কিউ
                </label>
                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300 has-[:checked]:bg-white dark:has-[:checked]:bg-slate-900 has-[:checked]:text-indigo-600 dark:has-[:checked]:text-indigo-400 has-[:checked]:shadow-sm transition select-none">
                    <input type="radio" name="modal_schedule_type" value="custom" onchange="toggleModalScheduleInput(this.value)" class="hidden">
                    <i class="fa-solid fa-calendar-days text-purple-500"></i> শিডিউল টাইম
                </label>
                
                <input type="datetime-local" id="modalScheduledAtInput" class="hidden px-2.5 py-1 text-xs border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans focus:outline-none focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="flex items-center gap-2">
                <button onclick="saveDraftOnly()" id="btnSave" class="px-5 py-2.5 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 rounded-xl text-xs font-bold shadow-sm flex items-center gap-2 transition cursor-pointer">💾 Save Draft</button>
                <button onclick="publishDraft()" id="btnPublish" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-500/20 flex items-center gap-2 transition cursor-pointer">🚀 Publish Now</button>
            </div>
        </div>
    </div>
</div>