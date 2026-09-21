<script>
    let globalCategories = [];
    let originalImageSrc = ''; 
    let activeKeywords = [];

    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({
            selector: '#previewContent',
            height: 500,
            plugins: 'link lists code table preview wordcount',
            toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code preview',
            menubar: false,
            statusbar: true,
            branding: false,
            setup: function (editor) {
                editor.on('keyup change', function () {
                    calculateSEO();
                    syncSocialCardPreview();
                });
                editor.addShortcut('ctrl+enter', 'Publish Draft', function () {
                    publishDraft();
                });
                editor.addShortcut('meta+enter', 'Publish Draft', function () {
                    publishDraft();
                });
                editor.addShortcut('ctrl+s', 'Save Draft', function () {
                    saveDraftOnly();
                });
                editor.addShortcut('meta+s', 'Save Draft', function () {
                    saveDraftOnly();
                });
                editor.addShortcut('alt+f', 'Run Fact Check', function () {
                    runFactCheckAndPlagiarism();
                });
            }
        });
        loadCategoriesOnce();
        
        document.querySelectorAll('.seo-input, #previewTitle').forEach(el => {
            if(el) {
                el.addEventListener('keyup', () => { calculateSEO(); syncSocialCardPreview(); });
                el.addEventListener('input', () => { calculateSEO(); syncSocialCardPreview(); });
            }
        });

        const tagInput = document.getElementById('keywordTagInput');
        if (tagInput) {
            tagInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ',') {
                    e.preventDefault();
                    const val = this.value.trim();
                    if (val) {
                        addKeywordPill(val);
                        this.value = '';
                    }
                } else if (e.key === 'Backspace' && this.value === '' && activeKeywords.length > 0) {
                    removeKeywordPill(activeKeywords.length - 1);
                }
            });
            tagInput.addEventListener('blur', function() {
                const val = this.value.trim();
                if (val) {
                    addKeywordPill(val);
                    this.value = '';
                }
            });
        }
    });

    // ==========================================================
    // 🏷️ KEYWORD TAG PILLS ENGINE
    // ==========================================================
    function renderKeywordPills() {
        const list = document.getElementById('keywordPillsList');
        if (!list) return;
        list.innerHTML = '';

        activeKeywords.forEach((kw, index) => {
            const isPrimary = index === 0;
            const pill = document.createElement('span');
            pill.className = isPrimary
                ? 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-black bg-indigo-600 text-white shadow-sm border border-indigo-700 select-none animate-fadeIn'
                : 'inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-bold bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-600 select-none animate-fadeIn';
            
            const textSpan = document.createElement('span');
            textSpan.innerHTML = isPrimary ? `<i class="fa-solid fa-star text-[10px] text-amber-300"></i> ${kw}` : kw;
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = isPrimary 
                ? 'text-indigo-200 hover:text-white font-bold ml-1 text-xs leading-none p-0.5 rounded transition' 
                : 'text-slate-400 hover:text-rose-500 font-bold ml-1 text-xs leading-none p-0.5 rounded transition';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = (e) => {
                e.stopPropagation();
                removeKeywordPill(index);
            };

            pill.appendChild(textSpan);
            pill.appendChild(removeBtn);
            list.appendChild(pill);
        });

        syncKeywordsToInput();
        calculateSEO();
    }

    function addKeywordPill(rawText) {
        if (!rawText) return;
        const parts = rawText.split(',').map(s => s.trim().replace(/^#/, '')).filter(s => s.length > 0);
        parts.forEach(part => {
            if (!activeKeywords.some(k => k.toLowerCase() === part.toLowerCase())) {
                activeKeywords.push(part);
            }
        });
        renderKeywordPills();
    }

    function removeKeywordPill(index) {
        activeKeywords.splice(index, 1);
        renderKeywordPills();
    }

    function syncKeywordsToInput() {
        const hiddenInput = document.getElementById('focus_keyword');
        if (hiddenInput) {
            hiddenInput.value = activeKeywords.join(', ');
        }
    }

    function loadKeywordsFromInput(val) {
        activeKeywords = [];
        if (!val) {
            renderKeywordPills();
            return;
        }

        let rawList = [];
        if (Array.isArray(val)) {
            rawList = val;
        } else if (typeof val === 'string' && val.trim() !== '') {
            if (val.includes(',')) {
                rawList = val.split(',');
            } else if (val.includes('#')) {
                rawList = val.split(/\s+/);
            } else {
                rawList = [val];
            }
        }

        rawList.forEach(item => {
            if (typeof item === 'string') {
                const clean = item.trim().replace(/^#+/, '').trim();
                if (clean.length > 0 && !activeKeywords.some(k => k.toLowerCase() === clean.toLowerCase())) {
                    activeKeywords.push(clean);
                }
            }
        });

        renderKeywordPills();
    }

    // ==========================================================
    // ✨ 1-CLICK AI FOCUS KEYWORDS & META GENERATOR
    // ==========================================================
    function generateFocusKeywordsModal() {
        const titleInput = document.getElementById('previewTitle');
        const newsId = document.getElementById('previewNewsId') ? document.getElementById('previewNewsId').value : null;
        const currentTitle = titleInput ? titleInput.value : '';
        let contentText = '';
        if (tinymce.get('previewContent')) {
            contentText = tinymce.get('previewContent').getContent({ format: 'text' });
        } else if (document.getElementById('previewContent')) {
            contentText = document.getElementById('previewContent').value;
        }

        if (!currentTitle.trim() && !contentText.trim()) {
            alert('Please provide headline or content first!');
            return;
        }

        const btn = document.getElementById('btnAiKeywords');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin inline-block mr-1 text-indigo-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Generating...</span>`;

        fetch("{{ route('news.generate-focus-keywords') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: currentTitle,
                content: contentText,
                news_id: newsId
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = origHtml;

            if (data.success) {
                activeKeywords = [];
                if (data.primary_keyword) {
                    activeKeywords.push(data.primary_keyword.trim());
                }
                if (Array.isArray(data.keywords)) {
                    data.keywords.forEach(kw => {
                        const cleanKw = kw.trim();
                        if (cleanKw && !activeKeywords.some(k => k.toLowerCase() === cleanKw.toLowerCase())) {
                            activeKeywords.push(cleanKw);
                        }
                    });
                }
                renderKeywordPills();

                // Populate Meta Description if empty or provided
                const metaInput = document.getElementById('meta_description');
                if (metaInput && data.meta_description) {
                    if (!metaInput.value || metaInput.value.trim() === '') {
                        metaInput.value = data.meta_description;
                    }
                }

                // Populate Hashtags if tags provided and empty
                const hashtagsInput = document.getElementById('previewHashtags');
                if (hashtagsInput && Array.isArray(data.tags) && data.tags.length > 0) {
                    if (!hashtagsInput.value || hashtagsInput.value.trim() === '') {
                        hashtagsInput.value = data.tags.map(t => t.startsWith('#') ? t : '#' + t.replace(/\s+/g, '')).join(' ');
                    }
                }

                calculateSEO();
                syncSocialCardPreview();
                if (window.showToast) {
                    window.showToast('🎯 AI Focus Keywords & Meta Description generated!', 'success');
                }
            } else {
                alert('❌ ' + (data.message || 'Failed to generate keywords.'));
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = origHtml;
            console.error('Focus keywords error:', err);
            alert('⚠️ Server error occurred. Please try again.');
        });
    }

    // ==========================================================
    // 📊 REAL-TIME SEO AUDIT & KEYWORD DENSITY CALCULATION
    // ==========================================================
    function calculateSEO() {
        let score = 0;
        const titleEl = document.getElementById('previewTitle');
        const title = titleEl ? titleEl.value.trim() : '';
        
        let editor = tinymce.get('previewContent');
        let contentHtml = editor ? editor.getContent() : (document.getElementById('previewContent') ? document.getElementById('previewContent').value : ''); 
        let contentText = editor ? editor.getContent({format: 'text'}) : contentHtml.replace(/<[^>]*>?/gm, ' ');
        contentText = contentText.replace(/\s+/g, ' ').trim();

        const focusKeywordInput = document.getElementById('focus_keyword');
        const keywordStr = focusKeywordInput ? focusKeywordInput.value.trim() : '';
        const metaDescEl = document.getElementById('meta_description');
        const metaDesc = metaDescEl ? metaDescEl.value.trim() : '';

        const words = contentText.split(/\s+/).filter(w => w.length > 0);
        const wordCount = words.length;

        // Get primary keyword
        const primaryKeyword = activeKeywords.length > 0 ? activeKeywords[0].toLowerCase() : (keywordStr.split(',')[0] || '').trim().toLowerCase();
        const allKeywords = activeKeywords.length > 0 ? activeKeywords.map(k => k.toLowerCase()) : keywordStr.split(',').map(k => k.trim().toLowerCase()).filter(k => k.length > 0);

        const lowerTitle = title.toLowerCase();
        const lowerContent = contentText.toLowerCase();
        const lowerMeta = metaDesc.toLowerCase();

        // 1. Title Audit
        let titleHasKw = primaryKeyword.length > 0 && lowerTitle.includes(primaryKeyword);
        let titleGoodLength = title.length >= 40 && title.length <= 80;
        const checkTitleEl = document.getElementById('seoCheckTitle');
        if (checkTitleEl) {
            const icon = checkTitleEl.querySelector('.status-icon');
            if (!primaryKeyword) {
                if (icon) icon.innerText = '⚪';
                checkTitleEl.className = 'flex items-center gap-1.5 text-slate-400';
            } else if (titleHasKw) {
                if (icon) icon.innerText = '🟢';
                checkTitleEl.className = 'flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold';
                score += 25;
            } else {
                if (icon) icon.innerText = '❌';
                checkTitleEl.className = 'flex items-center gap-1.5 text-rose-500 font-semibold';
            }
        }
        if (titleGoodLength) score += 10;
        else if (title.length > 0) score += 5;

        // 2. Lead Paragraph (first 100 words) Audit
        const first100Words = words.slice(0, 100).join(' ').toLowerCase();
        let leadHasKw = primaryKeyword.length > 0 && first100Words.includes(primaryKeyword);
        const checkLeadEl = document.getElementById('seoCheckLead');
        if (checkLeadEl) {
            const icon = checkLeadEl.querySelector('.status-icon');
            if (!primaryKeyword) {
                if (icon) icon.innerText = '⚪';
                checkLeadEl.className = 'flex items-center gap-1.5 text-slate-400';
            } else if (leadHasKw) {
                if (icon) icon.innerText = '🟢';
                checkLeadEl.className = 'flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold';
                score += 20;
            } else {
                if (icon) icon.innerText = '❌';
                checkLeadEl.className = 'flex items-center gap-1.5 text-rose-500 font-semibold';
            }
        }

        // 3. Meta Description Audit
        let metaHasKw = primaryKeyword.length > 0 && lowerMeta.includes(primaryKeyword);
        let metaGoodLength = metaDesc.length >= 100 && metaDesc.length <= 160;
        const checkMetaEl = document.getElementById('seoCheckMeta');
        if (checkMetaEl) {
            const icon = checkMetaEl.querySelector('.status-icon');
            if (!primaryKeyword && metaDesc.length === 0) {
                if (icon) icon.innerText = '⚪';
                checkMetaEl.className = 'flex items-center gap-1.5 text-slate-400';
            } else if (metaHasKw && metaGoodLength) {
                if (icon) icon.innerText = '🟢';
                checkMetaEl.className = 'flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold';
                score += 20;
            } else if (metaHasKw || metaGoodLength) {
                if (icon) icon.innerText = '🟡';
                checkMetaEl.className = 'flex items-center gap-1.5 text-amber-500 font-semibold';
                score += 10;
            } else {
                if (icon) icon.innerText = '❌';
                checkMetaEl.className = 'flex items-center gap-1.5 text-rose-500 font-semibold';
            }
        }

        // 4. Content Length Audit
        const checkLengthEl = document.getElementById('seoCheckLength');
        if (checkLengthEl) {
            const icon = checkLengthEl.querySelector('.status-icon');
            if (wordCount >= 300) {
                if (icon) icon.innerText = '🟢';
                checkLengthEl.className = 'flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-bold';
                score += 15;
            } else if (wordCount >= 120) {
                if (icon) icon.innerText = '🟡';
                checkLengthEl.className = 'flex items-center gap-1.5 text-amber-500 font-semibold';
                score += 8;
            } else {
                if (icon) icon.innerText = '❌';
                checkLengthEl.className = 'flex items-center gap-1.5 text-rose-500 font-semibold';
            }
        }

        // 5. Internal / External Links
        if (contentHtml.includes('<a href=')) {
            score += 10;
        }

        // 6. Keyword Density Calculation
        let densityBadge = document.getElementById('seo-density-badge');
        if (densityBadge) {
            if (primaryKeyword && wordCount > 0) {
                const regex = new RegExp(primaryKeyword.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&'), 'gi');
                const matches = lowerContent.match(regex);
                const count = matches ? matches.length : 0;
                const kwWords = primaryKeyword.split(/\s+/).length;
                const density = ((count * kwWords) / wordCount) * 100;
                const densityFormatted = density.toFixed(1);

                if (density >= 0.8 && density <= 3.0) {
                    densityBadge.innerText = `Density: ${densityFormatted}% (Good)`;
                    densityBadge.className = 'px-2 py-0.5 rounded text-[10px] bg-emerald-100 dark:bg-emerald-900/60 text-emerald-700 dark:text-emerald-300 font-black';
                } else if (density > 3.0) {
                    densityBadge.innerText = `Density: ${densityFormatted}% (High)`;
                    densityBadge.className = 'px-2 py-0.5 rounded text-[10px] bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 font-bold';
                } else {
                    densityBadge.innerText = `Density: ${densityFormatted}% (Low)`;
                    densityBadge.className = 'px-2 py-0.5 rounded text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold';
                }
            } else {
                densityBadge.innerText = 'Density: 0%';
                densityBadge.className = 'px-2 py-0.5 rounded text-[10px] bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold';
            }
        }

        // Cap Score at 100
        if (score > 100) score = 100;

        // Update UI Score & Progress Bar
        const scoreEl = document.getElementById('seo-score');
        if (scoreEl) scoreEl.innerText = score;

        const scoreTextEl = document.getElementById('seo-score-text');
        const progressBar = document.getElementById('seo-progress');
        if (progressBar) {
            progressBar.style.width = score + '%';
            if (score >= 80) {
                progressBar.className = 'bg-emerald-500 h-2 rounded-full transition-all duration-500 shadow-sm';
                if (scoreTextEl) {
                    scoreTextEl.innerText = 'Excellent 🚀';
                    scoreTextEl.className = 'text-emerald-600 dark:text-emerald-400 font-extrabold';
                }
            } else if (score >= 50) {
                progressBar.className = 'bg-amber-500 h-2 rounded-full transition-all duration-500 shadow-sm';
                if (scoreTextEl) {
                    scoreTextEl.innerText = 'Good 👍';
                    scoreTextEl.className = 'text-amber-500 font-extrabold';
                }
            } else {
                progressBar.className = 'bg-rose-500 h-2 rounded-full transition-all duration-500 shadow-sm';
                if (scoreTextEl) {
                    scoreTextEl.innerText = 'Needs Work';
                    scoreTextEl.className = 'text-rose-500 font-extrabold';
                }
            }
        }

        const metaCountEl = document.getElementById('meta-count');
        const metaStatusEl = document.getElementById('meta-length-status');
        if (metaCountEl) metaCountEl.innerText = metaDesc.length;
        if (metaStatusEl) {
            if (metaDesc.length === 0) {
                metaStatusEl.innerText = 'Empty';
                metaStatusEl.className = 'font-bold text-slate-400';
            } else if (metaDesc.length >= 120 && metaDesc.length <= 160) {
                metaStatusEl.innerText = 'Ideal Length';
                metaStatusEl.className = 'font-bold text-emerald-600 dark:text-emerald-400';
            } else if (metaDesc.length < 120) {
                metaStatusEl.innerText = 'Too Short';
                metaStatusEl.className = 'font-bold text-amber-500';
            } else {
                metaStatusEl.innerText = 'Too Long';
                metaStatusEl.className = 'font-bold text-rose-500';
            }
        }

        // Live SERP Preview box updates
        const serpTitleEl = document.getElementById('googleSerpTitle');
        const serpSnippetEl = document.getElementById('googleSerpSnippet');
        const serpSlugEl = document.getElementById('serpSlug');

        if (serpTitleEl) {
            serpTitleEl.innerText = title.trim() || 'News Headline';
        }
        if (serpSnippetEl) {
            serpSnippetEl.innerText = metaDesc.trim() || (words.slice(0, 25).join(' ') + (words.length > 25 ? '...' : ''));
        }
        if (serpSlugEl) {
            let slug = (primaryKeyword || title).toLowerCase().replace(/[^\w\u0980-\u09FF\s-]/g, '').trim().replace(/\s+/g, '-').substring(0, 40);
            serpSlugEl.innerText = slug || 'article';
        }
    }

    // ==========================================================
    // 📝 EXTRACT META DESCRIPTION FROM LEAD PARAGRAPH
    // ==========================================================
    function extractMetaFromLead() {
        let contentText = '';
        if (tinymce.get('previewContent')) {
            contentText = tinymce.get('previewContent').getContent({ format: 'text' });
        } else if (document.getElementById('previewContent')) {
            contentText = document.getElementById('previewContent').value;
        }
        contentText = contentText.replace(/\s+/g, ' ').trim();
        if (!contentText) {
            alert('No text found in content!');
            return;
        }
        
        let lead = contentText.substring(0, 155);
        let lastSpace = lead.lastIndexOf(' ');
        if (lastSpace > 100) lead = lead.substring(0, lastSpace);
        
        const metaInput = document.getElementById('meta_description');
        if (metaInput) {
            metaInput.value = lead.trim();
        }
        calculateSEO();
        syncSocialCardPreview();
        if (window.showToast) {
            window.showToast('📝 Meta description extracted from lead content', 'success');
        }
    }

    // ==========================================================
    // 🔗 ENTERPRISE INTERNAL LINK ENGINE (Multi-Framework)
    // ==========================================================
    function fetchRelatedLinks(customKeyword = null, isAuto = false) {
        let keywordInput = document.getElementById('link-search-keyword');
        let keyword = customKeyword !== null ? customKeyword : (keywordInput ? keywordInput.value.trim() : '');
        let title = document.getElementById('previewTitle') ? document.getElementById('previewTitle').value.trim() : '';
        let newsId = document.getElementById('previewNewsId') ? document.getElementById('previewNewsId').value : null;

        const list = document.getElementById('link-suggestions');
        const skeleton = document.getElementById('link-suggestions-skeleton');
        const searchBtn = document.getElementById('btn-search-links');

        if (skeleton) skeleton.classList.remove('hidden');
        if (list) list.innerHTML = '';
        if (searchBtn && !isAuto) searchBtn.disabled = true;

        let queryParams = new URLSearchParams();
        if (keyword) queryParams.append('keyword', keyword);
        if (title) queryParams.append('title', title);
        if (newsId) queryParams.append('news_id', newsId);

        let focusKw = (typeof activeKeywords !== 'undefined' && activeKeywords.length > 0) 
            ? activeKeywords.join(', ') 
            : (document.getElementById('focus_keyword') ? document.getElementById('focus_keyword').value.trim() : '');
        if (focusKw) queryParams.append('focus_keyword', focusKw);

        fetch(`/news/suggest-links?${queryParams.toString()}`)
            .then(res => res.json())
            .then(data => {
                if (skeleton) skeleton.classList.add('hidden');
                if (searchBtn) searchBtn.disabled = false;
                if (!list) return;

                if (!data || data.length === 0) {
                    list.innerHTML = `
                        <div class="text-center p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                            <i class="fa-solid fa-link-slash text-slate-400 text-lg mb-1"></i>
                            <p class="text-[11px] font-bold text-slate-500 m-0">No published news found!</p>
                            <span class="text-[10px] text-slate-400">Try searching with different keywords.</span>
                        </div>
                    `;
                    return;
                }

                list.innerHTML = data.map(news => {
                    const safeTitle = (news.title || '').replace(/'/g, "\\'");
                    const safeUrl = (news.live_url || '').replace(/'/g, "\\'");
                    const safeImg = (news.thumbnail_url || '').replace(/'/g, "\\'");
                    const timeBadge = news.time_ago ? `<span class="text-[9px] text-slate-400">${news.time_ago}</span>` : '';

                    return `
                        <div class="flex flex-col gap-2 p-2.5 bg-white dark:bg-slate-800/90 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xs hover:border-indigo-300 dark:hover:border-indigo-600 transition group/card">
                            <div class="flex items-start gap-2">
                                <img src="${news.thumbnail_url}" class="w-12 h-10 rounded-lg object-cover shrink-0 bg-slate-100 dark:bg-slate-700 border border-slate-100 dark:border-slate-700" alt="thumb" onerror="this.src='/images/placeholder.png'">
                                <div class="flex-1 min-w-0">
                                    <h6 class="text-xs font-bold text-slate-800 dark:text-slate-100 line-clamp-2 leading-tight font-bangla group-hover/card:text-indigo-600 dark:group-hover/card:text-indigo-400 transition cursor-pointer" onclick="insertLinkToEditor('${safeTitle}', '${safeUrl}')" title="${news.title}">
                                        ${news.title}
                                    </h6>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-1.5 py-0.5 rounded">Live</span>
                                        ${timeBadge}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-1 pt-1 border-t border-slate-100 dark:border-slate-700/60 justify-end">
                                <button type="button" class="bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white dark:bg-indigo-950/60 dark:text-indigo-300 dark:hover:bg-indigo-600 dark:hover:text-white px-2 py-1 rounded text-[10px] font-black transition flex items-center gap-1 cursor-pointer" onclick="insertLinkToEditor('${safeTitle}', '${safeUrl}')" title="Insert as inline link into editor">
                                    <i class="fa-solid fa-link text-[9px]"></i> Inline
                                </button>
                                <button type="button" class="bg-rose-50 hover:bg-rose-600 text-rose-700 hover:text-white dark:bg-rose-950/60 dark:text-rose-300 dark:hover:bg-rose-600 dark:hover:text-white px-2 py-1 rounded text-[10px] font-black transition flex items-center gap-1 cursor-pointer" onclick="insertReadMoreToEditor('${safeTitle}', '${safeUrl}')" title="Insert as 'Read More' callout box">
                                    <i class="fa-solid fa-bookmark text-[9px]"></i> Read More
                                </button>
                                <button type="button" class="bg-slate-100 hover:bg-slate-800 hover:text-white dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600 px-2 py-1 rounded text-[10px] font-bold transition flex items-center gap-1 cursor-pointer" onclick="insertRelatedMediaCard('${safeTitle}', '${safeUrl}', '${safeImg}')" title="Insert photo card into editor">
                                    <i class="fa-solid fa-id-card text-[9px]"></i> Card
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            })
            .catch(err => {
                if (skeleton) skeleton.classList.add('hidden');
                if (searchBtn) searchBtn.disabled = false;
                console.error('Suggest links error:', err);
            });
    }

    function addManualLink(type = 'normal') {
        let text = document.getElementById('manual-link-text').value;
        let url = document.getElementById('manual-link-url').value;
        
        if(!text || !url) return alert('Please provide both link text and URL!');
        
        if (type === 'readmore') {
            insertReadMoreToEditor(text, url);
        } else {
            insertLinkToEditor(text, url);
        }
        
        document.getElementById('manual-link-text').value = '';
        document.getElementById('manual-link-url').value = '';
    }

    function insertLinkToEditor(text, url) {
        if (!tinymce.get('previewContent')) {
            alert('Editor is not loaded yet!');
            return;
        }

        const editor = tinymce.get('previewContent');
        const selectedText = editor.selection.getContent({ format: 'text' }).trim();
        
        let linkText = selectedText || text;
        let linkHtml = `<a href="${url}" target="_blank" rel="noopener noreferrer" style="color: #2563eb; text-decoration: underline; font-weight: bold;">${linkText}</a>&nbsp;`;
        
        editor.execCommand('mceInsertContent', false, linkHtml);
        calculateSEO();
        if (window.showToast) window.showToast('🔗 Inline link added to editor', 'success');
    }

    function insertReadMoreToEditor(text, url) {
        if (!tinymce.get('previewContent')) {
            alert('Editor is not loaded yet!');
            return;
        }

        const readMoreHtml = `
            <div style="margin: 18px 0; padding: 12px 16px; border-left: 4px solid #e11d48; background: #fff1f2; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                <span style="color: #e11d48; font-size: 14px; font-weight: 800; text-transform: uppercase; margin-right: 6px;">📌 Read More:</span>
                <a href="${url}" target="_blank" rel="noopener noreferrer" style="color: #1e40af; font-size: 15px; font-weight: bold; text-decoration: underline;">${text}</a>
            </div>
            <p>&nbsp;</p>
        `;

        tinymce.get('previewContent').execCommand('mceInsertContent', false, readMoreHtml);
        calculateSEO();
        if (window.showToast) window.showToast('📌 "Read More" callout added to editor', 'success');
    }

    function insertRelatedMediaCard(text, url, imageUrl) {
        if (!tinymce.get('previewContent')) {
            alert('Editor is not loaded yet!');
            return;
        }

        const imgTag = imageUrl && imageUrl !== '/images/placeholder.png' 
            ? `<img src="${imageUrl}" alt="${text}" style="width: 80px; height: 60px; object-fit: cover; border-radius: 6px; margin-right: 12px; float: left;" />` 
            : '';

        const cardHtml = `
            <div style="margin: 20px 0; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; overflow: hidden; display: flex; align-items: center;">
                ${imgTag}
                <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 2px;">Related News</span>
                    <a href="${url}" target="_blank" rel="noopener noreferrer" style="color: #0f172a; font-size: 14px; font-weight: bold; text-decoration: none;">${text}</a>
                </div>
                <div style="clear: both;"></div>
            </div>
            <p>&nbsp;</p>
        `;

        tinymce.get('previewContent').execCommand('mceInsertContent', false, cardHtml);
        calculateSEO();
        if (window.showToast) window.showToast('🖼️ Related card added to editor', 'success');
    }

    function previewSelectedImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) { document.getElementById('previewImageDisplay').src = e.target.result; }
            reader.readAsDataURL(input.files[0]);
            document.getElementById('newImageUrl').value = '';
        }
    }

    function previewImageUrl(url) {
        if(url) {
            document.getElementById('previewImageDisplay').src = url;
            document.getElementById('newImageFile').value = '';
        }
    }

    function resetImage() {
        document.getElementById('previewImageDisplay').src = originalImageSrc;
        document.getElementById('newImageFile').value = '';
        document.getElementById('newImageUrl').value = '';
    }

    function loadCategoriesOnce() {
        fetch("{{ route('settings.fetch-categories') }}")
            .then(res => res.json())
            .then(data => {
                if(!data.error) {
                    globalCategories = data;
                    populateAllDropdowns();
                }
            });
    }

    function populateAllDropdowns() {
        const allDropdowns = document.querySelectorAll('.wp-cat-dropdown');
        if (globalCategories.length === 0) return;

        allDropdowns.forEach(select => {
            if (select.options.length > 1) return;
            const defaultText = select.id === 'previewCategory' ? '-- Primary Category --' : '-- Select --';
            select.innerHTML = `<option value="">${defaultText}</option>`;
            globalCategories.forEach(cat => {
                let option = document.createElement('option');
                option.value = cat.id;
                option.text = `${cat.name} (ID: ${cat.id})`;
                select.appendChild(option);
            });
        });
    }

    let currentOriginalData = {
        title: '',
        content: '',
        source_name: '',
        original_link: '',
        duplicates: []
    };

    function fetchDraftContent(id, imageUrl) {
        const modal = document.getElementById('rewriteModal');
        const titleInput = document.getElementById('previewTitle');
        const hashtagsInput = document.getElementById('previewHashtags');
        populateAllDropdowns();

        titleInput.value = "Loading...";
        if (tinymce.get('previewContent')) tinymce.get('previewContent').setContent("<p>Fetching content...</p>");

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        document.getElementById('previewNewsId').value = id;
        originalImageSrc = imageUrl ? imageUrl : 'https://via.placeholder.com/150';
        document.getElementById('previewImageDisplay').src = originalImageSrc;
        document.getElementById('newImageFile').value = '';
        document.getElementById('newImageUrl').value = '';

        document.getElementById('focus_keyword').value = '';
        document.getElementById('meta_description').value = '';
        document.getElementById('seo-score').innerText = '0';
        document.getElementById('seo-progress').style.width = '0%';
        document.getElementById('link-suggestions').innerHTML = '';
        displayFactCheckResults(null);

        // Reset duplicate alerts
        const dupAlert = document.getElementById('modalDuplicateAlert');
        const dupList = document.getElementById('modalDuplicateDetailsList');
        if (dupAlert) dupAlert.classList.add('hidden');
        if (dupList) {
            dupList.classList.add('hidden');
            dupList.innerHTML = '';
        }

        fetch(`/news/${id}/get-draft`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    titleInput.value = data.title;
                    hashtagsInput.value = data.hashtags || ''; 

                    // 🎯 Load Focus Keywords and Meta Description
                    loadKeywordsFromInput(data.focus_keyword || data.tags || data.hashtags || '');
                    if (document.getElementById('meta_description')) {
                        document.getElementById('meta_description').value = data.meta_description || data.short_summary || '';
                    }

                    if (tinymce.get('previewContent')) {
                        tinymce.get('previewContent').setContent(data.content);
                    } else {
                        document.getElementById('previewContent').value = data.content;
                    }

                    // 🔀 Store & Populate Side-by-Side Original Source Data
                    currentOriginalData = {
                        title: data.original_title || data.title,
                        content: data.original_content || data.content,
                        source_name: data.source_name || 'Online Source',
                        original_link: data.original_link || '#',
                        duplicates: data.duplicates || []
                    };

                    const sideTitle = document.getElementById('sideOriginalTitle');
                    const sideContent = document.getElementById('sideOriginalContent');
                    const sideTag = document.getElementById('sideOriginalSourceTag');
                    const sideLink = document.getElementById('sideOriginalLinkBtn');
                    const sourceBadge = document.getElementById('modalSourceBadge');

                    if (sideTitle) sideTitle.innerText = currentOriginalData.title;
                    if (sideContent) sideContent.innerHTML = currentOriginalData.content || '<p class="text-slate-400">Original article text unavailable.</p>';
                    if (sideTag) sideTag.innerText = currentOriginalData.source_name;
                    if (sideLink) {
                        sideLink.href = currentOriginalData.original_link;
                        if (currentOriginalData.original_link === '#' || !currentOriginalData.original_link) sideLink.style.display = 'none';
                        else sideLink.style.display = 'inline-flex';
                    }
                    if (sourceBadge) sourceBadge.innerText = `Source: ${currentOriginalData.source_name}`;

                    // ⚠️ Smart News Deduplication Detection inside Modal
                    if (data.duplicates && data.duplicates.length > 0) {
                        const topDup = data.duplicates[0];
                        if (dupAlert) {
                            document.getElementById('modalDuplicateAlertText').innerHTML = `⚠️ <strong>Warning:</strong> Found <strong>${data.duplicates.length}</strong> duplicate articles on this event (e.g. <u>${topDup.website_name}</u> - ${topDup.similarity}% match)`;
                            dupAlert.classList.remove('hidden');
                        }
                        if (dupList) {
                            dupList.innerHTML = data.duplicates.map(d => `
                                <div class="flex items-center justify-between p-2 rounded-xl bg-white/70 dark:bg-slate-900/60 border border-amber-200 dark:border-amber-800/40">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-100">${d.website_name}</span>
                                        <span class="truncate font-bold">${d.title}</span>
                                    </div>
                                    <span class="text-[10px] font-extrabold text-amber-700 dark:text-amber-300 shrink-0 ml-2">${d.similarity}% match</span>
                                </div>
                            `).join('');
                        }
                    }
                    
                    if (data.fact_check_status && data.fact_check_report) {
                        displayFactCheckResults(data.plagiarism_score, data.fact_check_status, data.fact_check_report);
                    } else {
                        displayFactCheckResults(null);
                    }
                    setTimeout(() => {
                        calculateSEO();
                        syncSocialCardPreview();
                        fetchRelatedLinks('', true);
                    }, 400);
                } else {
                    if (tinymce.get('previewContent')) tinymce.get('previewContent').setContent("Error loading content.");
                }
            })
            .catch(err => console.error(err));
    }

    function publishDraft() {
        const id = document.getElementById('previewNewsId').value;
        const btn = document.getElementById('btnPublish');
        let formData = new FormData();
        
        formData.append('title', document.getElementById('previewTitle').value);
        formData.append('hashtags', document.getElementById('previewHashtags').value);
        formData.append('focus_keyword', document.getElementById('focus_keyword') ? document.getElementById('focus_keyword').value : '');
        formData.append('meta_description', document.getElementById('meta_description') ? document.getElementById('meta_description').value : '');
        
        let content = tinymce.get('previewContent') ? tinymce.get('previewContent').getContent() : document.getElementById('previewContent').value;
        formData.append('content', content);

        formData.append('category', document.getElementById('previewCategory').value);
        for (let i = 1; i <= 4; i++) {
            let el = document.getElementById(`extraCategory${i}`);
            if (el && el.value) formData.append('extra_categories[]', el.value);
        }

        const fileInput = document.getElementById('newImageFile');
        if (fileInput && fileInput.files[0]) formData.append('image_file', fileInput.files[0]);
        
        const urlInput = document.getElementById('newImageUrl');
        if (urlInput && urlInput.value) formData.append('image_url', urlInput.value);

        // ⏰ Drip & Scheduling Options
        const scheduleTypeEl = document.querySelector('input[name="modal_schedule_type"]:checked');
        const scheduleType = scheduleTypeEl ? scheduleTypeEl.value : 'instant';
        formData.append('schedule_type', scheduleType);

        if (scheduleType === 'custom') {
            const scheduledAtInput = document.getElementById('modalScheduledAtInput');
            if (scheduledAtInput && scheduledAtInput.value) {
                formData.append('scheduled_at', scheduledAtInput.value);
            }
        }

        btn.innerText = "Publishing...";
        btn.disabled = true;

        fetch(`/news/${id}/publish-draft`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json' 
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("✅ " + data.message);
                window.location.href = "{{ route('news.index') }}"; 
            } else {
                alert("❌ Failed: " + data.message);
                btn.innerText = "🚀 Publish Now";
                btn.disabled = false;
            }
        })
        .catch(err => {
            alert("⚠️ Error: " + err.message);
            btn.innerText = "🚀 Publish Now";
            btn.disabled = false;
        });
    }

    function saveDraftOnly() {
        const id = document.getElementById('previewNewsId').value;
        const btn = document.getElementById('btnSave');
        
        let formData = new FormData();
        formData.append('title', document.getElementById('previewTitle').value);
        formData.append('hashtags', document.getElementById('previewHashtags').value);
        formData.append('focus_keyword', document.getElementById('focus_keyword') ? document.getElementById('focus_keyword').value : '');
        formData.append('meta_description', document.getElementById('meta_description') ? document.getElementById('meta_description').value : '');
        
        let content = tinymce.get('previewContent') ? tinymce.get('previewContent').getContent() : document.getElementById('previewContent').value;
        formData.append('content', content);

        const fileInput = document.getElementById('newImageFile');
        if (fileInput && fileInput.files[0]) formData.append('image_file', fileInput.files[0]);
        
        const urlInput = document.getElementById('newImageUrl');
        if (urlInput && urlInput.value) formData.append('image_url', urlInput.value);

        btn.innerText = "Saving...";
        btn.disabled = true;

        fetch(`/news/${id}/update-draft`, {
            method: 'POST',
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json' 
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("✅ " + data.message);
                closeRewriteModal();
                window.location.reload();
            } else {
                alert("❌ Failed: " + data.message);
                btn.innerText = "💾 Save Draft";
                btn.disabled = false;
            }
        }).catch(err => {
            btn.innerText = "💾 Save Draft";
            btn.disabled = false;
        });
    }

    function closeRewriteModal() {
        document.getElementById('rewriteModal').classList.add('hidden');
        document.getElementById('rewriteModal').classList.remove('flex');
    }
    
    function copyBossLink(id) {
        const previewUrl = "{{ url('/preview') }}/" + id;
        navigator.clipboard.writeText(previewUrl).then(() => {
            alert("✅ Preview link copied! Share via WhatsApp or Messenger.");
        });
    }

    
    function checkNewsStatus() {
        let processingItems = document.querySelectorAll('div[data-status="processing"], div[data-status="publishing"]');
        let ids = [];

        processingItems.forEach(item => {
            ids.push(item.getAttribute('data-news-id'));
        });

        if (ids.length === 0) return;

        fetch("{{ route('news.check-status') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            // Checks if edit modal is open
            let isModalOpen = !document.getElementById('rewriteModal').classList.contains('hidden');
            let needsReload = false;

            data.forEach(news => {
                if (news.status === 'draft' || news.status === 'published' || news.status === 'failed') {
                    
                    let card = document.querySelector(`div[data-news-id="${news.id}"]`);
                    
                    if (card) {
                        // Update card status to stop polling
                        card.setAttribute('data-status', news.status); 
                        
                        if (isModalOpen) {
                            // Update button instead of reloading page if modal is open
                            let btnArea = card.querySelector('.cursor-wait');
                            if (btnArea) {
                                btnArea.innerHTML = '✅ Done! Please refresh';
                                btnArea.className = 'w-full bg-emerald-100 hover:bg-emerald-200 text-emerald-700 py-2.5 rounded-lg text-xs font-bold flex items-center justify-center border border-emerald-200 cursor-pointer transition';
                                btnArea.onclick = function() { window.location.reload(); };
                            }
                        } else {
                            // Auto reload if modal is closed
                            needsReload = true;
                        }
                    }
                }
            });

            // Reload page only if item is done and modal is closed
            if (needsReload) {
                window.location.reload(); 
            }
        })
        .catch(err => console.error("Polling Error:", err));
    }

    setInterval(checkNewsStatus, 5000);

    function displayFactCheckResults(data, legacyStatus, legacyReport) {
        const resultsDiv = document.getElementById('factcheck-results');
        const skeletonDiv = document.getElementById('factcheck-skeleton');
        const badge = document.getElementById('factcheck-status-badge');
        const checkBtn = document.getElementById('btn-run-factcheck');
        
        const uniquenessScoreSpan = document.getElementById('uniqueness-score');
        const uniquenessProgressBar = document.getElementById('uniqueness-progress');
        const credScoreSpan = document.getElementById('credibility-score-val');
        const credProgressBar = document.getElementById('credibility-progress');
        
        const verdictHeading = document.getElementById('factcheck-verdict-heading');
        const verdictIcon = document.getElementById('factcheck-verdict-icon');
        const verdictCard = document.getElementById('factcheck-verdict-card');
        const reportText = document.getElementById('factcheck-report-text');
        
        const officialAlert = document.getElementById('official-factcheck-alert');
        const officialList = document.getElementById('official-factcheck-list');
        
        const claimsList = document.getElementById('claims-breakdown-list');
        const claimsCountBadge = document.getElementById('claims-count-badge');
        
        const redflagsBox = document.getElementById('factcheck-redflags-box');
        const redflagsList = document.getElementById('factcheck-redflags-list');

        if (!skeletonDiv) return;
        skeletonDiv.classList.add('hidden');

        // Normalize data
        let res = null;
        if (typeof data === 'object' && data !== null) {
            res = data;
        } else if (data !== null && data !== undefined) {
            res = {
                plagiarism_score: data,
                uniqueness_score: 100 - parseInt(data || 0),
                credibility_score: legacyStatus === 'verified' ? 90 : (legacyStatus === 'warning' ? 65 : 40),
                overall_verdict: legacyStatus || 'unverified',
                verdict_title: legacyStatus === 'verified' ? 'Fact Check Complete & Reliable' : 'Fact Check Caution',
                summary_report: legacyReport || 'No AI report found.',
                claims: [],
                official_factchecks: [],
                red_flags: []
            };
        }

        if (!res) {
            if (resultsDiv) resultsDiv.classList.add('hidden');
            if (badge) badge.classList.add('hidden');
            if (checkBtn) {
                checkBtn.classList.remove('hidden');
                checkBtn.innerHTML = '<i class="fa-solid fa-magnifying-glass-chart text-indigo-400"></i> <span>Verify Real-time Facts</span>';
            }
            return;
        }

        if (resultsDiv) resultsDiv.classList.remove('hidden');
        if (badge) badge.classList.remove('hidden');
        if (checkBtn) {
            checkBtn.classList.remove('hidden');
            checkBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> <span>Re-verify Claims</span>';
        }

        // 1. Uniqueness Meter
        const uniqueness = res.uniqueness_score !== undefined ? parseInt(res.uniqueness_score) : (100 - parseInt(res.plagiarism_score || 0));
        if (uniquenessScoreSpan && uniquenessProgressBar) {
            uniquenessScoreSpan.innerText = uniqueness + '%';
            uniquenessProgressBar.style.width = uniqueness + '%';
            if (uniqueness > 79) {
                uniquenessProgressBar.className = 'bg-emerald-500 h-2 rounded-full transition-all duration-500';
                uniquenessScoreSpan.className = 'text-xs font-black text-emerald-600';
            } else if (uniqueness > 49) {
                uniquenessProgressBar.className = 'bg-amber-500 h-2 rounded-full transition-all duration-500';
                uniquenessScoreSpan.className = 'text-xs font-black text-amber-600';
            } else {
                uniquenessProgressBar.className = 'bg-rose-500 h-2 rounded-full transition-all duration-500';
                uniquenessScoreSpan.className = 'text-xs font-black text-rose-600';
            }
        }

        // 2. Credibility & Truth Meter
        const credScore = parseInt(res.credibility_score || 70);
        if (credScoreSpan && credProgressBar) {
            credScoreSpan.innerText = credScore + '%';
            credProgressBar.style.width = credScore + '%';
            if (credScore >= 80) {
                credProgressBar.className = 'bg-emerald-500 h-2 rounded-full transition-all duration-500';
                credScoreSpan.className = 'text-xs font-black text-emerald-600';
            } else if (credScore >= 55) {
                credProgressBar.className = 'bg-amber-500 h-2 rounded-full transition-all duration-500';
                credScoreSpan.className = 'text-xs font-black text-amber-600';
            } else {
                credProgressBar.className = 'bg-rose-500 h-2 rounded-full transition-all duration-500';
                credScoreSpan.className = 'text-xs font-black text-rose-600';
            }
        }

        // 3. Verdict & Status Badge
        const status = (res.overall_verdict || res.fact_check_status || 'unverified').toLowerCase();
        if (badge) {
            if (status === 'verified' || status === 'verified_true') {
                badge.innerText = '🟢 Verified & Factual';
                badge.className = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 px-2.5 py-0.5 rounded-full text-[10px] font-black shadow-sm';
            } else if (status === 'warning' || status === 'partially_true') {
                badge.innerText = '🟡 Partially True / Caution';
                badge.className = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 px-2.5 py-0.5 rounded-full text-[10px] font-black shadow-sm';
            } else if (status === 'false') {
                badge.innerText = '🔴 False / Misleading';
                badge.className = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 px-2.5 py-0.5 rounded-full text-[10px] font-black shadow-sm';
            } else {
                badge.innerText = '⚪ Verification Required';
                badge.className = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 px-2.5 py-0.5 rounded-full text-[10px] font-black shadow-sm';
            }
        }

        // 4. Verdict Card Heading & Report Text
        if (verdictHeading) {
            verdictHeading.innerText = res.verdict_title || (status === 'verified' ? 'Information is verified & factual' : 'Caution advised in claims review');
        }
        if (verdictIcon) {
            verdictIcon.innerText = (status === 'verified' ? '✅' : (status === 'false' ? '🚨' : (status === 'warning' ? '⚠️' : '🔍')));
        }
        if (reportText) {
            reportText.innerText = res.summary_report || res.fact_check_report || 'No AI report available.';
        }

        // 5. Official Fact-Checks (Google ClaimReview Matches)
        if (officialAlert && officialList) {
            const ofcs = res.official_factchecks || [];
            if (ofcs.length > 0) {
                officialAlert.classList.remove('hidden');
                officialList.innerHTML = ofcs.map(item => `
                    <div class="p-2 rounded-lg bg-white dark:bg-slate-900 border border-rose-200 dark:border-rose-900 space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="font-black text-rose-600 dark:text-rose-400 font-sans uppercase text-[10px]">${item.publisher || 'Fact Checker'}</span>
                            <span class="px-1.5 py-0.2 text-[9px] font-black rounded bg-rose-100 text-rose-700">${item.rating}</span>
                        </div>
                        <p class="text-slate-800 dark:text-slate-200 text-xs font-semibold leading-snug">${item.text}</p>
                        ${item.review_url ? `<a href="${item.review_url}" target="_blank" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">View Fact-Check Report 🔗</a>` : ''}
                    </div>
                `).join('');
            } else {
                officialAlert.classList.add('hidden');
                officialList.innerHTML = '';
            }
        }

        // 6. Claim-by-Claim Breakdown
        if (claimsList && claimsCountBadge) {
            const claims = res.claims || [];
            claimsCountBadge.innerText = claims.length + ' Claims';
            
            if (claims.length > 0) {
                claimsList.innerHTML = claims.map((c, idx) => {
                    const cStatus = (c.status || 'unverified').toLowerCase();
                    let badgeClass = 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300';
                    let badgeLabel = '⚪ Unverified';
                    let borderClass = 'border-slate-200 dark:border-slate-800';

                    if (cStatus.includes('true') || cStatus === 'verified') {
                        badgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200';
                        badgeLabel = '🟢 True / Verified';
                        borderClass = 'border-emerald-100 dark:border-emerald-900/40';
                    } else if (cStatus.includes('partial') || cStatus === 'warning') {
                        badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200';
                        badgeLabel = '🟡 Partially True';
                        borderClass = 'border-amber-100 dark:border-amber-900/40';
                    } else if (cStatus.includes('false')) {
                        badgeClass = 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200';
                        badgeLabel = '🔴 False / Debunked';
                        borderClass = 'border-rose-100 dark:border-rose-900/40';
                    }

                    return `
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/50 border ${borderClass} space-y-1.5 transition-all text-xs">
                            <div class="flex justify-between items-start gap-2">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[10px] font-bold text-slate-400">#${idx + 1}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black border ${badgeClass}">${badgeLabel}</span>
                                    ${c.type ? `<span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider">(${c.type})</span>` : ''}
                                </div>
                                ${c.confidence ? `<span class="text-[10px] font-bold text-slate-400">${c.confidence}% Confidence</span>` : ''}
                            </div>
                            <p class="font-extrabold text-slate-900 dark:text-slate-100 leading-snug">${c.claim_text}</p>
                            <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-relaxed">${c.explanation || ''}</p>
                            ${c.source_hint ? `<div class="text-[10px] text-indigo-600 dark:text-indigo-400 font-bold flex items-center gap-1"><i class="fa-solid fa-link text-[8px]"></i> Source: ${c.source_hint}</div>` : ''}
                            ${c.suggested_correction ? `
                                <div class="mt-1.5 p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/40 text-[11px] text-emerald-900 dark:text-emerald-200 flex items-start justify-between gap-2">
                                    <div>
                                        <strong class="font-black text-emerald-800 dark:text-emerald-300 block mb-0.5">💡 Verified Correction:</strong>
                                        <span>${c.suggested_correction}</span>
                                    </div>
                                    <button type="button" onclick="navigator.clipboard.writeText('${c.suggested_correction.replace(/'/g, "\\'")}'); showNotification('Correction copied to clipboard!');" class="shrink-0 px-2 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] font-bold cursor-pointer">
                                        Copy
                                    </button>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }).join('');
            } else {
                claimsList.innerHTML = '<div class="text-xs text-slate-400 p-2 text-center">No specific claims breakdown found.</div>';
            }
        }

        // 7. Red Flags & Sensationalism
        if (redflagsBox && redflagsList) {
            const rfs = res.red_flags || [];
            if (rfs.length > 0) {
                redflagsBox.classList.remove('hidden');
                redflagsList.innerHTML = rfs.map(rf => `<li>${rf}</li>`).join('');
            } else {
                redflagsBox.classList.add('hidden');
                redflagsList.innerHTML = '';
            }
        }
    }

    function runFactCheckAndPlagiarism() {
        const id = document.getElementById('previewNewsId').value;
        const checkBtn = document.getElementById('btn-run-factcheck');
        const skeletonDiv = document.getElementById('factcheck-skeleton');
        const resultsDiv = document.getElementById('factcheck-results');
        const badge = document.getElementById('factcheck-status-badge');
        const titleInput = document.getElementById('previewTitle');

        let currentContent = '';
        if (tinymce.get('previewContent')) {
            currentContent = tinymce.get('previewContent').getContent();
        } else {
            currentContent = document.getElementById('previewContent').value;
        }

        const currentTitle = titleInput ? titleInput.value : '';

        if (!currentContent && !currentTitle) return alert('No headline or content available to verify!');

        // Show skeletons, hide button & results
        checkBtn.classList.add('hidden');
        resultsDiv.classList.add('hidden');
        badge.classList.add('hidden');
        skeletonDiv.classList.remove('hidden');

        fetch(`/news/${id}/analyze-plagiarism`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: currentTitle,
                content: currentContent
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayFactCheckResults(data);
            } else {
                alert('❌ ' + (data.message || 'An error occurred.'));
                displayFactCheckResults(null);
            }
        })
        .catch(err => {
            console.error(err);
            alert('⚠️ Server connection error!');
            displayFactCheckResults(null);
        });
    }

    // ==========================================================
    // 🔀 MODAL VIEW MODE SWITCHER (Editor / Side-by-Side / Social)
    // ==========================================================
    let currentModalView = 'editor';

    function switchModalView(mode) {
        currentModalView = mode;
        const container = document.getElementById('rewriteModalContainer');
        const sidePanel = document.getElementById('sideBySideOriginalPanel');
        const editorPanel = document.getElementById('editorMainPanel');
        const socialPanel = document.getElementById('socialPreviewTabPanel');
        const sidebarPanel = document.getElementById('editorSidebarPanel');

        // Update button states
        document.querySelectorAll('.modal-view-btn').forEach(btn => {
            btn.className = 'modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-300 transition-all cursor-pointer';
        });

        if (mode === 'sidebyside') {
            document.getElementById('viewBtnSideBySide').className = 'modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm transition-all cursor-pointer';
            if (container) container.className = 'bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-7xl mx-4 overflow-hidden flex flex-col max-h-[92vh] border border-slate-200 dark:border-slate-800 transition-all duration-300';
            if (sidePanel) sidePanel.classList.remove('hidden');
            if (editorPanel) editorPanel.classList.remove('hidden');
            if (socialPanel) socialPanel.classList.add('hidden');
            if (sidebarPanel) sidebarPanel.classList.remove('hidden');
        } else if (mode === 'social') {
            document.getElementById('viewBtnSocial').className = 'modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm transition-all cursor-pointer';
            if (container) container.className = 'bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-6xl mx-4 overflow-hidden flex flex-col max-h-[92vh] border border-slate-200 dark:border-slate-800 transition-all duration-300';
            if (sidePanel) sidePanel.classList.add('hidden');
            if (editorPanel) editorPanel.classList.add('hidden');
            if (socialPanel) socialPanel.classList.remove('hidden');
            if (sidebarPanel) sidebarPanel.classList.remove('hidden');
            syncSocialCardPreview();
        } else {
            // Default editor view
            document.getElementById('viewBtnEditor').className = 'modal-view-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm transition-all cursor-pointer';
            if (container) container.className = 'bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-5xl mx-4 overflow-hidden flex flex-col max-h-[92vh] border border-slate-200 dark:border-slate-800 transition-all duration-300';
            if (sidePanel) sidePanel.classList.add('hidden');
            if (editorPanel) editorPanel.classList.remove('hidden');
            if (socialPanel) socialPanel.classList.add('hidden');
            if (sidebarPanel) sidebarPanel.classList.remove('hidden');
        }
    }

    // ==========================================================
    // 📱 LIVE SOCIAL CARD & GOOGLE SEARCH PREVIEW ENGINE
    // ==========================================================
    function syncSocialCardPreview() {
        const title = document.getElementById('previewTitle') ? document.getElementById('previewTitle').value : '';
        const metaDesc = document.getElementById('meta_description') ? document.getElementById('meta_description').value : '';
        const imgDisplay = document.getElementById('previewImageDisplay');
        const imgSrc = imgDisplay ? imgDisplay.src : '';

        let contentText = '';
        if (tinymce.get('previewContent')) {
            contentText = tinymce.get('previewContent').getContent({ format: 'text' });
        }

        const excerpt = metaDesc.trim() || contentText.substring(0, 150) || 'Article content details...';
        const displayTitle = title.trim() || 'Untitled Article';

        // Facebook Card
        const fbTitle = document.getElementById('fbPreviewTitle');
        const fbDesc = document.getElementById('fbPreviewDesc');
        const fbPost = document.getElementById('fbPreviewPostText');
        const fbImg = document.getElementById('fbPreviewImage');
        if (fbTitle) fbTitle.innerText = displayTitle;
        if (fbDesc) fbDesc.innerText = excerpt;
        if (fbPost) fbPost.innerText = displayTitle;
        if (fbImg && imgSrc) fbImg.src = imgSrc;

        // Twitter Card
        const twTitle = document.getElementById('twitterPreviewTitle');
        const twDesc = document.getElementById('twitterPreviewDesc');
        const twImg = document.getElementById('twitterPreviewImage');
        if (twTitle) twTitle.innerText = displayTitle;
        if (twDesc) twDesc.innerText = excerpt;
        if (twImg && imgSrc) twImg.src = imgSrc;

        // Google Search Snippet
        const gTitle = document.getElementById('googlePreviewTitle');
        const gDesc = document.getElementById('googlePreviewDesc');
        if (gTitle) gTitle.innerText = displayTitle.substring(0, 60);
        if (gDesc) gDesc.innerText = (metaDesc || excerpt).substring(0, 155);
    }

    // ==========================================================
    // 📋 SIDE-BY-SIDE HELPER FUNCTIONS
    // ==========================================================
    function copyOriginalContent() {
        if (currentOriginalData.content) {
            const cleanText = currentOriginalData.content.replace(/<[^>]*>?/gm, '').trim();
            if (window.copyToClipboard) window.copyToClipboard(cleanText, '📋 Original article text copied!');
            else alert('Copied to clipboard!');
        }
    }

    function insertOriginalToEditor() {
        if (currentOriginalData.content && tinymce.get('previewContent')) {
            tinymce.get('previewContent').execCommand('mceInsertContent', false, `<p>${currentOriginalData.content}</p>`);
            if (window.showToast) window.showToast('➕ Original text inserted into editor', 'success');
            calculateSEO();
        }
    }

    function toggleDuplicateDetails() {
        const details = document.getElementById('modalDuplicateDetailsList');
        if (details) details.classList.toggle('hidden');
    }

    // ==========================================================
    // ✨ 1-CLICK 3-OPTION VIRAL HEADLINE GENERATOR
    // ==========================================================
    function generateViralHeadlinesModal() {
        const titleInput = document.getElementById('previewTitle');
        const newsId = document.getElementById('previewNewsId') ? document.getElementById('previewNewsId').value : null;
        const currentTitle = titleInput ? titleInput.value : '';
        let contentText = '';
        if (tinymce.get('previewContent')) {
            contentText = tinymce.get('previewContent').getContent({ format: 'text' });
        }

        if (!currentTitle.trim() && !contentText.trim()) {
            alert('Please provide headline or content first!');
            return;
        }

        const btn = document.getElementById('btnGenerateViralHeadlines');
        const origBtnText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> <span>Generating...</span>`;

        fetch("{{ route('news.generate-headlines') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                title: currentTitle,
                content: contentText,
                news_id: newsId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.headlines) {
                const box = document.getElementById('viralHeadlineSuggestionsBox');
                const container = document.getElementById('viralHeadlineCardsContainer');
                if (box && container) {
                    const h = data.headlines;
                    const items = [
                        { type: '💡 Informative & Standard', text: h.informative, color: 'border-blue-300 dark:border-blue-800 bg-blue-50/80 dark:bg-blue-950/40 text-blue-900 dark:text-blue-200' },
                        { type: '🔥 Viral & High-CTR', text: h.viral, color: 'border-purple-300 dark:border-purple-800 bg-purple-50/80 dark:bg-purple-950/40 text-purple-900 dark:text-purple-200' },
                        { type: '⚡ Short & Breaking', text: h.breaking, color: 'border-rose-300 dark:border-rose-800 bg-rose-50/80 dark:bg-rose-950/40 text-rose-900 dark:text-rose-200' }
                    ];

                    container.innerHTML = items.map(item => `
                        <div class="p-3 rounded-xl border ${item.color} flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 transition hover:shadow-sm">
                            <div class="flex-1">
                                <span class="text-[10px] font-black uppercase tracking-wider block mb-0.5 opacity-80">${item.type}</span>
                                <p class="text-xs font-bold leading-snug font-bangla">${item.text}</p>
                            </div>
                            <button type="button" onclick="applyViralHeadline('${item.text.replace(/'/g, "\\'")}')" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shrink-0 shadow-sm flex items-center gap-1 transition cursor-pointer">
                                <span>Use Headline</span> ↵
                            </button>
                        </div>
                    `).join('');

                    box.classList.remove('hidden');
                }
            } else {
                alert(data.message || 'Failed to generate headlines.');
            }
        })
        .catch(err => {
            console.error('Viral Headlines Error:', err);
            alert('Server error occurred. Please try again.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = origBtnText;
        });
    }

    function applyViralHeadline(headlineText) {
        const titleInput = document.getElementById('previewTitle');
        if (titleInput) {
            titleInput.value = headlineText;
            calculateSEO();
            syncSocialCardPreview();
            const box = document.getElementById('viralHeadlineSuggestionsBox');
            if (box) box.classList.add('hidden');
        }
    }

    function toggleModalScheduleInput(type) {
        const input = document.getElementById('modalScheduledAtInput');
        if (input) {
            if (type === 'custom') {
                input.classList.remove('hidden');
                input.focus();
            } else {
                input.classList.add('hidden');
            }
        }
    }
</script>