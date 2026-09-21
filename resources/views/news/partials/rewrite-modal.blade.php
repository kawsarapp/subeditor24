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
                    <h3 class="text-base font-black text-slate-900 dark:text-white font-bangla">Edit, Compare & Publish News</h3>
                    <p class="text-[11px] font-semibold text-slate-500 dark:text-slate-400" id="modalSourceBadge">Source: Loading...</p>
                </div>
            </div>

            {{-- View Switcher Buttons --}}
            <div class="flex items-center gap-1.5 bg-slate-200/70 dark:bg-slate-800 p-1 rounded-2xl border border-slate-300/60 dark:border-slate-700/60">
                <button type="button" onclick="switchModalView('editor')" id="viewBtnEditor" class="modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm transition-all cursor-pointer">
                    <i class="fa-solid fa-pen-to-square"></i> <span class="hidden md:inline">Editor</span>
                </button>
                <button type="button" onclick="switchModalView('sidebyside')" id="viewBtnSideBySide" class="modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all cursor-pointer">
                    <i class="fa-solid fa-columns"></i> <span class="hidden md:inline">Side-by-Side Compare</span>
                </button>
                <button type="button" onclick="switchModalView('social')" id="viewBtnSocial" class="modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all cursor-pointer">
                    <i class="fa-solid fa-share-nodes"></i> <span class="hidden md:inline">Social Preview</span>
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
                <span id="modalDuplicateAlertText">Duplicate / related articles found.</span>
            </div>
            <button type="button" onclick="toggleDuplicateDetails()" class="text-indigo-700 dark:text-indigo-300 underline text-xs font-bold cursor-pointer">
                View List ▾
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
                            Original Source
                        </span>
                        <h4 class="text-xs font-black text-slate-700 dark:text-slate-300 uppercase">Original Raw Content</h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <a id="sideOriginalLinkBtn" href="#" target="_blank" class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
                            Source Link 🔗
                        </a>
                        <button type="button" onclick="copyOriginalContent()" class="text-[11px] font-bold px-2 py-1 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg text-slate-700 dark:text-slate-300 transition cursor-pointer" title="Copy to clipboard">
                            <i class="fa-solid fa-copy"></i> Copy
                        </button>
                        <button type="button" onclick="insertOriginalToEditor()" class="text-[11px] font-bold px-2 py-1 bg-indigo-50 dark:bg-indigo-950/60 hover:bg-indigo-100 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/60 rounded-lg transition cursor-pointer" title="Insert into editor">
                            ➕ Insert to Editor
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Original Title</label>
                    <h3 id="sideOriginalTitle" class="text-base font-extrabold text-slate-900 dark:text-white leading-snug font-bangla"></h3>
                </div>

                <div class="flex-1 flex flex-col">
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-1">Raw Article Content / Source Body</label>
                    <div id="sideOriginalContent" class="flex-1 p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 text-slate-800 dark:text-slate-200 text-sm leading-relaxed overflow-y-auto max-h-[500px] whitespace-pre-line font-bangla select-text">
                        Loading...
                    </div>
                </div>
            </div>

            {{-- 📝 EDITOR & AI CONTENT PANEL --}}
            <div id="editorMainPanel" class="flex-1 flex flex-col">
                <input type="hidden" id="previewNewsId">
                
                {{-- Feature Image Card --}}
                <div class="mb-5 bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm">
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Featured Image</label>
                    <div class="flex gap-4 items-start">
                        <div class="w-24 h-24 flex-shrink-0 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700 relative group">
                            <img id="previewImageDisplay" src="" class="w-full h-full object-cover">
                            <button type="button" onclick="resetImage()" class="absolute top-1 right-1 bg-rose-600 text-white p-1 rounded-full text-xs opacity-0 group-hover:opacity-100 transition shadow cursor-pointer">✕</button>
                        </div>
                        <div class="flex-1 space-y-2">
                            <input type="file" id="newImageFile" onchange="previewSelectedImage(this)" accept="image/*" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-950 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 cursor-pointer">
                            <div class="text-[10px] font-bold text-slate-400 text-center">- Or enter image URL -</div>
                            <input type="url" id="newImageUrl" oninput="previewImageUrl(this.value)" placeholder="https://example.com/image.jpg" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2 text-xs focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-indigo-500 text-slate-800 dark:text-slate-100">
                        </div>
                    </div>
                </div>
                
                {{-- Title --}}
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300">News Title / Headline</label>
                        <button type="button" onclick="generateViralHeadlinesModal()" id="btnGenerateViralHeadlines" class="text-xs font-bold px-3 py-1 bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white rounded-xl shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                            <i class="fa-solid fa-wand-magic-sparkles text-[11px]"></i> ✨ 3 AI Viral Headlines
                        </button>
                    </div>
                    <input type="text" id="previewTitle" oninput="syncSocialCardPreview()" class="w-full border border-slate-200 dark:border-slate-700 rounded-2xl p-3.5 focus:ring-2 focus:ring-indigo-500 font-bangla text-lg font-bold text-slate-900 dark:text-white bg-white dark:bg-slate-900 shadow-sm transition">
                    
                    {{-- 3-Option Viral Headline Suggestions Box --}}
                    <div id="viralHeadlineSuggestionsBox" class="hidden mt-3 p-4 bg-gradient-to-br from-indigo-50/90 to-purple-50/90 dark:from-slate-800 dark:to-slate-850 border border-indigo-200/70 dark:border-slate-700 rounded-2xl space-y-2.5 transition-all">
                        <div class="flex items-center justify-between pb-2 border-b border-indigo-100 dark:border-slate-700">
                            <span class="text-xs font-black text-indigo-900 dark:text-indigo-200 flex items-center gap-1.5">
                                <i class="fa-solid fa-sparkles text-indigo-600"></i> AI Recommended Headlines (1-Click Selection):
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
                    <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-2">Article Body & Full Content</label>
                    <textarea id="previewContent" rows="15" class="w-full border border-slate-200 dark:border-slate-700 rounded-2xl"></textarea>
                </div>
            </div>

            {{-- 📱 SOCIAL MEDIA & SEARCH PREVIEW TAB PANEL (Active in social mode) --}}
            <div id="socialPreviewTabPanel" class="hidden flex-1 flex-col gap-5">
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-sm space-y-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-bullhorn text-indigo-500"></i> Live Social Card & Search Preview
                        </h4>
                        <span class="text-xs font-semibold text-slate-400">Realtime Preview</span>
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
                                        <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ optional(optional(auth()->user())->settings)->site_name ?? 'News Portal' }}</span>
                                        <i class="fa-solid fa-circle-check text-blue-500 text-[10px]"></i>
                                    </div>
                                    <span class="text-[10px] text-slate-400">Just now · 🌐</span>
                                </div>
                            </div>
                            <p class="px-3.5 pb-2 text-xs font-semibold text-slate-800 dark:text-slate-100 line-clamp-2" id="fbPreviewPostText">Loading article details...</p>
                            <div class="aspect-video w-full bg-slate-100 dark:bg-slate-700 overflow-hidden relative">
                                <img id="fbPreviewImage" src="" class="w-full h-full object-cover">
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-850 border-t border-slate-100 dark:border-slate-700/80">
                                <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider" id="fbPreviewDomain">YOURSITE.COM</span>
                                <h5 class="text-xs font-bold text-slate-900 dark:text-white line-clamp-2 leading-snug mt-0.5" id="fbPreviewTitle">Article Headline</h5>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1 mt-0.5" id="fbPreviewDesc">Article summary and key takeaways...</p>
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
                                <h5 class="text-xs font-bold line-clamp-1 leading-tight text-slate-100" id="twitterPreviewTitle">Article Headline</h5>
                                <p class="text-[11px] text-slate-400 line-clamp-2 mt-1" id="twitterPreviewDesc">Article summary and key takeaways...</p>
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
                                Article Headline
                            </h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 line-clamp-2" id="googlePreviewDesc">
                                Article meta description will appear here...
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 🚀 RIGHT SIDEBAR: SEO & METADATA --}}
            <div id="editorSidebarPanel" class="w-full lg:w-80 flex flex-col gap-5 h-auto lg:h-[92vh] lg:overflow-y-auto lg:sticky lg:top-4 pr-2">
                {{-- 🚀 SEO & Meta Data Card --}}
                {{-- 🚀 SEO & Focus Keywords Card --}}
                <div class="bg-white dark:bg-slate-900 border border-indigo-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex-shrink-0">
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-4 py-3 flex justify-between items-center shadow-sm">
                        <h5 class="m-0 font-bold text-xs flex items-center gap-2">
                            <i class="fa-solid fa-chart-line"></i> 🚀 SEO & Focus Keywords
                        </h5>
                        <span class="bg-white text-indigo-700 px-2.5 py-0.5 rounded-full text-xs font-black shadow-sm">
                            <span id="seo-score">0</span>/100
                        </span>
                    </div>
                    <div class="p-4 space-y-4">
                        {{-- SEO Progress Bar --}}
                        <div>
                            <div class="flex justify-between items-center text-[11px] font-bold text-slate-500 mb-1">
                                <span>SEO Optimization</span>
                                <span id="seo-score-text" class="text-rose-500 font-extrabold">Needs Work</span>
                            </div>
                            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                <div id="seo-progress" class="bg-red-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                            </div>
                        </div>

                        {{-- Focus Keywords Section --}}
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                    🎯 Focus Keywords
                                </label>
                                <button type="button" id="btnAiKeywords" onclick="generateFocusKeywordsModal()" class="text-[10px] bg-gradient-to-r from-indigo-50 to-violet-50 hover:from-indigo-100 hover:to-violet-100 text-indigo-700 border border-indigo-200/80 px-2.5 py-1 rounded-lg font-black transition flex items-center gap-1 shadow-sm cursor-pointer" title="Automatically extract keywords with AI">
                                    <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i>
                                    <span>AI Auto Keywords</span>
                                </button>
                            </div>

                            {{-- Interactive Keyword Tag Container --}}
                            <div id="keywordPillWrapper" onclick="document.getElementById('keywordTagInput').focus()" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2 min-h-[46px] flex flex-wrap items-center gap-1.5 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 focus-within:bg-white dark:focus-within:bg-slate-900 transition cursor-text">
                                <div id="keywordPillsList" class="flex flex-wrap items-center gap-1.5"></div>
                                <input type="text" id="keywordTagInput" placeholder="Type keyword and press Enter or comma..." class="flex-1 min-w-[110px] bg-transparent text-xs text-slate-800 dark:text-slate-100 border-none outline-none focus:ring-0 p-1 font-semibold">
                            </div>
                            {{-- Hidden input for form sync --}}
                            <input type="hidden" id="focus_keyword" name="focus_keyword" value="">
                        </div>

                        {{-- Real-Time SEO Audit Checklist & Density Meter --}}
                        <div class="bg-slate-50 dark:bg-slate-800/60 rounded-xl p-3 border border-slate-200 dark:border-slate-700 space-y-2 text-[11px] font-semibold">
                            <div class="text-[10px] font-black uppercase text-slate-400 tracking-wider flex items-center justify-between">
                                <span>SEO Audit Checklist</span>
                                <span id="seo-density-badge" class="px-2 py-0.5 rounded text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold">Density: 0%</span>
                            </div>
                            
                            <div class="space-y-1.5">
                                <div id="seoCheckTitle" class="flex items-center gap-1.5 text-slate-500 transition-colors">
                                    <span class="status-icon">⚪</span> <span>Focus keyword in headline</span>
                                </div>
                                <div id="seoCheckLead" class="flex items-center gap-1.5 text-slate-500 transition-colors">
                                    <span class="status-icon">⚪</span> <span>Focus keyword in first 100 words</span>
                                </div>
                                <div id="seoCheckMeta" class="flex items-center gap-1.5 text-slate-500 transition-colors">
                                    <span class="status-icon">⚪</span> <span>Focus keyword in meta description</span>
                                </div>
                                <div id="seoCheckLength" class="flex items-center gap-1.5 text-slate-500 transition-colors">
                                    <span class="status-icon">⚪</span> <span>Content length (minimum 300 words)</span>
                                </div>
                            </div>
                        </div>

                        {{-- Meta Description --}}
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center">
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span>📝 Meta Description</span>
                                </label>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" onclick="extractMetaFromLead()" class="text-[9px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded font-bold transition flex items-center gap-1 cursor-pointer" title="Generate summary from lead paragraph">
                                        <i class="fa-solid fa-align-left text-slate-500"></i> From Lead
                                    </button>
                                    <button type="button" id="btnAiMetaGen" onclick="generateFocusKeywordsModal()" class="text-[9px] bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-2 py-0.5 rounded font-bold transition flex items-center gap-1 cursor-pointer" title="Generate meta description with AI">
                                        <i class="fa-solid fa-wand-magic-sparkles text-indigo-500"></i> AI Gen
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <textarea id="meta_description" oninput="syncSocialCardPreview(); calculateSEO();" class="seo-input w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl p-2.5 text-xs focus:bg-white dark:focus:bg-slate-900 text-slate-800 dark:text-slate-100 resize-none font-medium leading-relaxed" rows="3" maxlength="160" placeholder="Engaging news summary for search engines and social media (120-160 chars)..."></textarea>
                            </div>
                            <div class="flex justify-between items-center text-[10px]">
                                <span id="meta-length-status" class="font-bold text-slate-400">Empty</span>
                                <span class="text-slate-400 font-bold"><span id="meta-count">0</span> / 160 chars</span>
                            </div>
                        </div>

                        {{-- 🔍 Google SERP Live Search Box Snippet --}}
                        <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-3 bg-white dark:bg-slate-900 shadow-sm space-y-1.5">
                            <div class="flex items-center justify-between text-[10px] font-black uppercase text-slate-400">
                                <span class="flex items-center gap-1"><i class="fa-brands fa-google text-indigo-500"></i> Google SERP Preview</span>
                                <span class="text-[9px] text-emerald-600 dark:text-emerald-400 font-bold">Live</span>
                            </div>
                            
                            {{-- SERP Box --}}
                            <div class="pt-1">
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-500 truncate">
                                    <span class="w-3.5 h-3.5 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-[8px]">G</span>
                                    <span class="font-bold text-slate-700 dark:text-slate-300">{{ request()->getHost() }}</span>
                                    <span>› news › <span id="serpSlug" class="truncate text-slate-400">article</span></span>
                                </div>
                                <h4 id="googleSerpTitle" class="text-xs font-bold text-blue-700 dark:text-blue-400 hover:underline cursor-pointer line-clamp-1 mt-0.5 font-bangla">
                                    Article Headline
                                </h4>
                                <p id="googleSerpSnippet" class="text-[11px] text-slate-600 dark:text-slate-400 line-clamp-2 mt-0.5 leading-snug font-bangla">
                                    Meta description will be shown here...
                                </p>
                            </div>
                        </div>

                        <hr class="my-2 border-slate-100 dark:border-slate-800">
                        
                        {{-- 🔗 Internal Link Suggestions --}}
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <h6 class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1.5 m-0">
                                    <i class="fa-solid fa-link text-indigo-500"></i>
                                    <span>🔗 Internal Link Suggestions</span>
                                </h6>
                                <button type="button" onclick="fetchRelatedLinks('', true)" class="text-[9px] bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded font-bold transition flex items-center gap-1 cursor-pointer" title="Refresh auto suggestions">
                                    <i class="fa-solid fa-arrows-rotate text-[10px]"></i> Refresh
                                </button>
                            </div>

                            <div class="flex gap-1.5">
                                <div class="relative flex-1">
                                    <input type="text" id="link-search-keyword" onkeydown="if(event.key==='Enter'){event.preventDefault();fetchRelatedLinks();}" class="w-full border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 rounded-xl px-3 py-1.5 text-xs focus:bg-white dark:focus:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder:text-slate-400" placeholder="Search by keywords...">
                                </div>
                                <button type="button" id="btn-search-links" class="bg-slate-900 hover:bg-slate-800 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 cursor-pointer shrink-0" onclick="fetchRelatedLinks()">
                                    <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                                    <span>Search</span>
                                </button>
                            </div>

                            {{-- Skeleton Loader --}}
                            <div id="link-suggestions-skeleton" class="space-y-2 hidden">
                                <div class="p-2.5 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/40 animate-pulse space-y-1.5">
                                    <div class="h-2.5 bg-slate-200 dark:bg-slate-700 rounded w-3/4"></div>
                                    <div class="h-2 bg-slate-200 dark:bg-slate-700 rounded w-1/2"></div>
                                </div>
                            </div>

                            {{-- Suggestions List --}}
                            <div id="link-suggestions" class="flex flex-col gap-2 max-h-56 overflow-y-auto pr-1"></div>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_fact_check'))
                {{-- 🔍 Enterprise Fact Checking & Credibility Suite --}}
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden flex-shrink-0">
                    <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white px-4 py-3 flex justify-between items-center shadow-sm">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-shield-halved text-sm text-amber-300"></i>
                            <h5 class="m-0 font-bold text-xs tracking-wide">AI Fact Checker & Verification</h5>
                        </div>
                        <span id="factcheck-status-badge" class="bg-white/90 text-slate-800 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider hidden shadow-sm">
                            Unverified
                        </span>
                    </div>
                    
                    <div class="p-4 space-y-4">
                        {{-- Trigger Button --}}
                        <button type="button" id="btn-run-factcheck" class="w-full bg-gradient-to-r from-slate-900 to-indigo-950 hover:from-slate-800 hover:to-indigo-900 text-white py-2.5 px-4 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 shadow-md cursor-pointer group" onclick="runFactCheckAndPlagiarism()">
                            <i class="fa-solid fa-magnifying-glass-chart text-indigo-400 group-hover:scale-110 transition-transform"></i>
                            <span>Real-time Fact Checking & Verification</span>
                        </button>

                        {{-- Skeleton Loader --}}
                        <div id="factcheck-skeleton" class="space-y-3 hidden">
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-2 border border-slate-200 dark:border-slate-700 animate-pulse">
                                <div class="flex justify-between items-center">
                                    <div class="h-3 w-1/2 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                    <div class="h-3 w-12 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2"></div>
                            </div>
                            <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl space-y-2 border border-slate-200 dark:border-slate-700 animate-pulse">
                                <div class="h-3 w-3/4 bg-slate-200 dark:bg-slate-700 rounded"></div>
                                <div class="h-10 bg-slate-200 dark:bg-slate-700 rounded"></div>
                            </div>
                            <p class="text-[11px] text-center text-indigo-600 dark:text-indigo-400 font-bold animate-pulse">
                                📡 Verifying claims against Google Fact Check Database and authoritative sources...
                            </p>
                        </div>

                        {{-- Results Container --}}
                        <div id="factcheck-results" class="space-y-4 hidden font-bangla">
                            
                            {{-- Dual Meters: Credibility & Uniqueness --}}
                            <div class="grid grid-cols-2 gap-2.5">
                                {{-- Credibility Meter --}}
                                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Truth Score</span>
                                        <span id="credibility-score-val" class="text-xs font-black text-emerald-600">--%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                        <div id="credibility-progress" class="bg-emerald-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                                    </div>
                                </div>

                                {{-- Uniqueness Meter --}}
                                <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300">Originality</span>
                                        <span id="uniqueness-score" class="text-xs font-black text-indigo-600">--%</span>
                                    </div>
                                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                        <div id="uniqueness-progress" class="bg-indigo-500 h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Verdict & Summary Card --}}
                            <div id="factcheck-verdict-card" class="p-3 rounded-xl border bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-700 space-y-1.5">
                                <div class="flex items-center gap-1.5">
                                    <span id="factcheck-verdict-icon" class="text-sm">🔍</span>
                                    <h6 id="factcheck-verdict-heading" class="text-xs font-black text-slate-900 dark:text-white leading-tight"></h6>
                                </div>
                                <p id="factcheck-report-text" class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed"></p>
                            </div>

                            {{-- Official Debunk / Fact Check Match Alert --}}
                            <div id="official-factcheck-alert" class="hidden p-3 rounded-xl bg-rose-50 dark:bg-rose-950/70 border border-rose-300 dark:border-rose-800 space-y-2">
                                <div class="flex items-center gap-2 text-rose-700 dark:text-rose-300 text-xs font-black">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <span>Official Fact-Checker Database Matches (Google ClaimReview)</span>
                                </div>
                                <div id="official-factcheck-list" class="space-y-1.5 text-[11px]"></div>
                            </div>

                            {{-- Claim-by-Claim Breakdown --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-[11px] font-black uppercase text-slate-500 dark:text-slate-400 tracking-wider flex items-center gap-1.5">
                                        <i class="fa-solid fa-list-check text-indigo-500"></i> Claim-by-Claim Breakdown
                                    </label>
                                    <span id="claims-count-badge" class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300">0 Claims</span>
                                </div>
                                <div id="claims-breakdown-list" class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                    {{-- Dynamically populated via JS --}}
                                </div>
                            </div>

                            {{-- Red Flags / Sensationalism Warnings --}}
                            <div id="factcheck-redflags-box" class="hidden p-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 border border-amber-200 dark:border-amber-800/60 space-y-1.5">
                                <span class="text-[11px] font-black text-amber-900 dark:text-amber-200 flex items-center gap-1.5">
                                    <i class="fa-solid fa-flag text-amber-600"></i> Flags & Disclaimers
                                </span>
                                <ul id="factcheck-redflags-list" class="text-[11px] text-amber-800 dark:text-amber-300 list-disc pl-4 space-y-0.5"></ul>
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
                    <i class="fa-solid fa-bolt text-amber-500"></i> Publish Directly
                </label>
                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300 has-[:checked]:bg-white dark:has-[:checked]:bg-slate-900 has-[:checked]:text-indigo-600 dark:has-[:checked]:text-indigo-400 has-[:checked]:shadow-sm transition select-none">
                    <input type="radio" name="modal_schedule_type" value="drip" onchange="toggleModalScheduleInput(this.value)" class="hidden">
                    <i class="fa-solid fa-droplet text-blue-500"></i> Auto-Drip Queue
                </label>
                <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl cursor-pointer text-xs font-bold text-slate-700 dark:text-slate-300 has-[:checked]:bg-white dark:has-[:checked]:bg-slate-900 has-[:checked]:text-indigo-600 dark:has-[:checked]:text-indigo-400 has-[:checked]:shadow-sm transition select-none">
                    <input type="radio" name="modal_schedule_type" value="custom" onchange="toggleModalScheduleInput(this.value)" class="hidden">
                    <i class="fa-solid fa-calendar-days text-purple-500"></i> Scheduled Time
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