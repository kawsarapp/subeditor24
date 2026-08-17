<script>
    let globalCategories = [];
    let originalImageSrc = ''; 

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
                });
            }
        });
        loadCategoriesOnce();
        
        document.querySelectorAll('.seo-input, #previewTitle').forEach(el => {
            if(el) el.addEventListener('keyup', calculateSEO);
        });
    });

    function calculateSEO() {
        let score = 0;
        let title = document.getElementById('previewTitle') ? document.getElementById('previewTitle').value : '';
        let editor = tinymce.get('previewContent');
        let contentHtml = editor ? editor.getContent() : ''; 
        let contentText = editor ? editor.getContent({format: 'text'}) : ''; 

        let keyword = document.getElementById('focus_keyword').value;
        let metaDesc = document.getElementById('meta_description').value;

        if(title.length >= 40 && title.length <= 70) score += 20;
        else if(title.length > 0) score += 10;

        let wordCount = contentText.split(/\s+/).filter(word => word.length > 0).length;
        if(wordCount > 300) score += 30;
        else if(wordCount > 100) score += 15;

        if(metaDesc.length >= 120 && metaDesc.length <= 160) score += 20;
        else if(metaDesc.length > 0) score += 10;

        if(keyword.length > 0) {
            let keywords = keyword.split(',').map(k => k.trim().toLowerCase());
            let keywordFound = false;
            let lowerTitle = title.toLowerCase();
            let lowerContent = contentText.toLowerCase();
            
            keywords.forEach(kw => {
                if(kw !== "" && (lowerTitle.includes(kw) || lowerContent.includes(kw))) {
                    keywordFound = true;
                }
            });
            if(keywordFound) score += 20;
        }

        if(contentHtml.includes('<a href=')) score += 10;

        document.getElementById('seo-score').innerText = score;
        let progressBar = document.getElementById('seo-progress');
        progressBar.style.width = score + '%';
        
        if(score > 79) progressBar.className = 'bg-green-500 h-2 rounded-full transition-all duration-500';
        else if(score > 49) progressBar.className = 'bg-yellow-500 h-2 rounded-full transition-all duration-500';
        else progressBar.className = 'bg-red-500 h-2 rounded-full transition-all duration-500';

        document.getElementById('meta-count').innerText = metaDesc.length;
    }

    function fetchRelatedLinks() {
        let keyword = document.getElementById('link-search-keyword').value;
        if(keyword.length < 2) return alert('অন্তত ২ টি অক্ষর লিখুন');

        let btn = event.target;
        btn.innerText = 'খুঁজছে...';

        fetch(`/news/suggest-links?keyword=${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(data => {
                btn.innerText = 'খুঁজুন';
                let list = document.getElementById('link-suggestions');
                list.innerHTML = '';
                list.classList.remove('hidden');
                
                if(data.length === 0) {
                    list.innerHTML = '<div class="text-xs text-red-500 p-2 bg-red-50 rounded">কোনো নিউজ পাওয়া যায়নি!</div>';
                    return;
                }

                data.forEach(news => {
                    list.innerHTML += `
                        <div class="flex flex-col gap-2 p-3 bg-white border border-indigo-100 rounded shadow-sm hover:bg-indigo-50 transition">
                            <span class="text-xs font-bold text-gray-800 line-clamp-2" title="${news.title}">${news.title}</span>
                            <div class="flex flex-wrap gap-2 justify-end mt-1">
                                <button type="button" class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded text-[10px] font-bold transition flex-1 sm:flex-none text-center" onclick="insertLinkToEditor('${news.title}', '${news.live_url}')">🔗 Link</button>
                                <button type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded text-[10px] font-bold transition flex-1 sm:flex-none text-center" onclick="insertReadMoreToEditor('${news.title}', '${news.live_url}')">📖 আরও পড়ুন</button>
                            </div>
                        </div>
                    `;
                });
            }).catch(() => { btn.innerText = 'খুঁজুন'; });
    }

    function addManualLink(type = 'normal') {
        let text = document.getElementById('manual-link-text').value;
        let url = document.getElementById('manual-link-url').value;
        
        if(!text || !url) return alert('লিংকের লেখা এবং URL দুটোই দিন!');
        
        if (type === 'readmore') {
            insertReadMoreToEditor(text, url);
        } else {
            insertLinkToEditor(text, url);
        }
        
        document.getElementById('manual-link-text').value = '';
        document.getElementById('manual-link-url').value = '';
    }

    function insertLinkToEditor(text, url) {
        let linkHtml = `<a href="${url}" target="_blank" rel="noopener noreferrer" style="color: blue; text-decoration: underline;"><strong>${text}</strong></a>&nbsp;`;
        if (tinymce.get('previewContent')) {
            tinymce.get('previewContent').execCommand('mceInsertContent', false, linkHtml);
            calculateSEO(); 
        } else {
            alert('Editor is not loaded yet!');
        }
    }

    function insertReadMoreToEditor(text, url) {
        let readMoreHtml = `
            <p style="margin: 15px 0; padding: 10px; border-left: 4px solid #e11d48; background-color: #f8fafc;">
                <strong style="color: #e11d48; font-size: 16px;">আরও পড়ুন: </strong>
                <a href="${url}" target="_blank" rel="noopener noreferrer" style="color: #2563eb; font-weight: bold; font-size: 16px; text-decoration: none;">
                    ${text}
                </a>
            </p>
            <p>&nbsp;</p>
        `;
        if (tinymce.get('previewContent')) {
            tinymce.get('previewContent').execCommand('mceInsertContent', false, readMoreHtml);
            calculateSEO(); 
        } else {
            alert('Editor is not loaded yet!');
        }
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

        fetch(`/news/${id}/get-draft`)
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    titleInput.value = data.title;
                    hashtagsInput.value = data.hashtags || ''; 

                    if (tinymce.get('previewContent')) {
                        tinymce.get('previewContent').setContent(data.content);
                    } else {
                        document.getElementById('previewContent').value = data.content;
                    }
                    
                    displayFactCheckResults(data.plagiarism_score, data.fact_check_status, data.fact_check_report);
                    setTimeout(calculateSEO, 500);
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
            alert("✅ প্রিভিউ লিঙ্ক কপি হয়েছে! বসের হোয়াটসঅ্যাপ বা মেসেঞ্জারে পাঠিয়ে দিন।");
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
            // চেক করবে এডিট মোডাল ওপেন আছে কি না
            let isModalOpen = !document.getElementById('rewriteModal').classList.contains('hidden');
            let needsReload = false;

            data.forEach(news => {
                if (news.status === 'draft' || news.status === 'published' || news.status === 'failed') {
                    
                    let card = document.querySelector(`div[data-news-id="${news.id}"]`);
                    
                    if (card) {
                        // কার্ডের স্ট্যাটাস আপডেট করে দিলাম, যাতে এটি আর পোলিং না করে
                        card.setAttribute('data-status', news.status); 
                        
                        if (isModalOpen) {
                            // মোডাল ওপেন থাকলে পেজ রিলোড না করে শুধু বাটন চেঞ্জ করে দেব
                            let btnArea = card.querySelector('.cursor-wait');
                            if (btnArea) {
                                btnArea.innerHTML = '✅ কাজ শেষ! রিফ্রেশ দিন';
                                btnArea.className = 'w-full bg-emerald-100 hover:bg-emerald-200 text-emerald-700 py-2.5 rounded-lg text-xs font-bold flex items-center justify-center border border-emerald-200 cursor-pointer transition';
                                btnArea.onclick = function() { window.location.reload(); };
                            }
                        } else {
                            // মোডাল ক্লোজ থাকলে অটো রিলোড হবে
                            needsReload = true;
                        }
                    }
                }
            });

            // যদি কোনো আইটেমের কাজ শেষ হয় এবং মোডাল ক্লোজ থাকে, তবেই পেজ রিলোড হবে
            if (needsReload) {
                window.location.reload(); 
            }
        })
        .catch(err => console.error("Polling Error:", err));
    }

    setInterval(checkNewsStatus, 5000);

    function displayFactCheckResults(plagiarismScore, status, report) {
        const resultsDiv = document.getElementById('factcheck-results');
        const skeletonDiv = document.getElementById('factcheck-skeleton');
        const badge = document.getElementById('factcheck-status-badge');
        const scoreSpan = document.getElementById('uniqueness-score');
        const progressBar = document.getElementById('uniqueness-progress');
        const reportText = document.getElementById('factcheck-report-text');
        const checkBtn = document.getElementById('btn-run-factcheck');

        if (!skeletonDiv) return;

        skeletonDiv.classList.add('hidden');

        if (plagiarismScore === null || plagiarismScore === undefined) {
            resultsDiv.classList.add('hidden');
            badge.classList.add('hidden');
            checkBtn.classList.remove('hidden');
            checkBtn.innerHTML = '<i class="fa-solid fa-circle-check"></i> মৌলিকতা ও তথ্য যাচাই করুন';
            return;
        }

        resultsDiv.classList.remove('hidden');
        badge.classList.remove('hidden');
        checkBtn.classList.remove('hidden');
        checkBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> পুনরায় যাচাই করুন';

        // Calculate Uniqueness
        const uniqueness = 100 - parseInt(plagiarismScore);
        scoreSpan.innerText = uniqueness + '%';
        progressBar.style.width = uniqueness + '%';

        // Colors based on uniqueness score
        if (uniqueness > 79) {
            progressBar.className = 'bg-emerald-500 h-2 rounded-full transition-all duration-500';
            scoreSpan.className = 'text-xs font-bold text-emerald-600';
        } else if (uniqueness > 49) {
            progressBar.className = 'bg-amber-500 h-2 rounded-full transition-all duration-500';
            scoreSpan.className = 'text-xs font-bold text-amber-600';
        } else {
            progressBar.className = 'bg-rose-500 h-2 rounded-full transition-all duration-500';
            scoreSpan.className = 'text-xs font-bold text-rose-600';
        }

        // Status Badge Style
        badge.innerText = status.toUpperCase();
        if (status === 'verified') {
            badge.className = 'bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider shadow-sm';
        } else if (status === 'warning') {
            badge.className = 'bg-amber-100 text-amber-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider shadow-sm';
        } else {
            badge.className = 'bg-rose-100 text-rose-700 px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider shadow-sm';
        }

        // Report Text
        reportText.innerText = report || 'কোনো এআই রিপোর্ট পাওয়া যায়নি।';
    }

    function runFactCheckAndPlagiarism() {
        const id = document.getElementById('previewNewsId').value;
        const checkBtn = document.getElementById('btn-run-factcheck');
        const skeletonDiv = document.getElementById('factcheck-skeleton');
        const resultsDiv = document.getElementById('factcheck-results');
        const badge = document.getElementById('factcheck-status-badge');

        let currentContent = '';
        if (tinymce.get('previewContent')) {
            currentContent = tinymce.get('previewContent').getContent();
        } else {
            currentContent = document.getElementById('previewContent').value;
        }

        if (!currentContent) return alert('কম্পেয়ার করার জন্য কোনো কন্টেন্ট নেই!');

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
            body: JSON.stringify({ content: currentContent })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                displayFactCheckResults(data.plagiarism_score, data.fact_check_status, data.fact_check_report);
            } else {
                alert('❌ ' + (data.message || 'ভুল হয়েছে।'));
                displayFactCheckResults(null);
            }
        })
        .catch(err => {
            console.error(err);
            alert('⚠️ সার্ভার কানেকশন এরর!');
            displayFactCheckResults(null);
        });
    }
</script>