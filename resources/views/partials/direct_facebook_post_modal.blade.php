@auth
@php
    $modalFbPages = auth()->user()->facebookPages()->where('is_active', true)->get();
@endphp

{{-- ========================================================================= --}}
{{-- 🚀 DIRECT FACEBOOK POST MODAL (WITH FIRST COMMENT SUPPORT)                --}}
{{-- ========================================================================= --}}
<div id="directFacebookPostModal" 
     class="fixed inset-0 z-[120] bg-slate-950/70 backdrop-blur-sm hidden items-center justify-center p-2.5 sm:p-5 overflow-y-auto"
     aria-labelledby="directFbModalTitle" 
     role="dialog" 
     aria-modal="true">

    <div class="relative w-full max-w-2xl bg-white dark:bg-slate-900 rounded-2xl sm:rounded-3xl shadow-2xl border border-slate-200/80 dark:border-slate-800 my-auto overflow-hidden transition-all transform animate-fade-in-up font-bangla flex flex-col max-h-[88vh] sm:max-h-[92vh]">
        
        {{-- MODAL HEADER --}}
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-100 dark:border-slate-800 bg-gradient-to-r from-blue-50/70 via-indigo-50/40 to-white dark:from-slate-800/80 dark:to-slate-900 flex items-center justify-between gap-2.5 sm:gap-3 shrink-0">
            <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-gradient-to-tr from-blue-600 via-blue-500 to-indigo-600 text-white flex items-center justify-center text-lg sm:text-xl shadow-md shadow-blue-500/20 shrink-0">
                    <i class="fa-brands fa-facebook"></i>
                </div>
                <div class="min-w-0">
                    <h3 id="directFbModalTitle" class="text-sm sm:text-lg font-black text-slate-800 dark:text-white flex items-center gap-1.5 sm:gap-2 truncate">
                        <span>ফেসবুকে পোস্ট করুন</span>
                        <span class="text-[10px] bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 font-extrabold px-2 py-0.5 rounded-full border border-blue-200/60 dark:border-blue-800 hidden sm:inline-block">Direct Publish</span>
                    </h3>
                    <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium truncate">
                        সংযুক্ত ফেসবুক পেজে ফটো, ক্যাপশন ও স্বয়ংক্রিয় ১ম কমেন্ট পোস্ট করুন
                    </p>
                </div>
            </div>
            
            <button type="button" 
                    onclick="closeDirectFacebookPostModal()" 
                    class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-700 flex items-center justify-center text-sm transition cursor-pointer shrink-0" 
                    title="বন্ধ করুন (Esc)">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- MODAL BODY (SCROLLABLE) --}}
        <form id="directFacebookPostForm" onsubmit="submitDirectFacebookPost(event)" class="overflow-y-auto custom-scrollbar p-3.5 sm:p-6 space-y-4 sm:space-y-5 text-sm flex-grow">
            @csrf

            {{-- 1. FACEBOOK PAGES SELECTION --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between gap-1.5 sm:gap-2 flex-wrap">
                    <label class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="fa-brands fa-facebook text-blue-600 text-sm"></i>
                        <span>টার্গেট ফেসবুক পেজ নির্বাচন করুন <span class="text-rose-500">*</span></span>
                    </label>

                    @if($modalFbPages->count() > 1)
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-[11px] sm:text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-800 select-none bg-blue-50 dark:bg-blue-950/60 px-2 sm:px-2.5 py-1 rounded-lg border border-blue-200/60 dark:border-blue-800/60 transition shrink-0">
                            <input type="checkbox" 
                                   id="directFbSelectAll" 
                                   checked 
                                   onchange="toggleAllDirectFbPages(this.checked)" 
                                   class="form-checkbox text-blue-600 rounded w-3.5 h-3.5 cursor-pointer">
                            <span>সবগুলো পেজ নির্বাচন (All)</span>
                        </label>
                    @endif
                </div>

                @if($modalFbPages->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-40 overflow-y-auto custom-scrollbar p-0.5" id="directFbPagesList">
                        @foreach($modalFbPages as $page)
                            <label class="direct-fb-page-card flex items-center gap-2 sm:gap-2.5 p-2 sm:p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/40 hover:bg-blue-50/50 dark:hover:bg-blue-950/30 hover:border-blue-300 transition cursor-pointer select-none min-w-0">
                                <input type="checkbox" 
                                       name="pages[]" 
                                       value="{{ $page->id }}" 
                                       class="direct-fb-page-checkbox form-checkbox text-blue-600 rounded w-4 h-4 cursor-pointer shrink-0" 
                                       checked 
                                       onchange="checkDirectFbSelectAllState()">
                                <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-600 dark:text-blue-300 flex items-center justify-center text-xs font-black shrink-0">
                                    <i class="fa-brands fa-facebook-f"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate leading-tight">{{ $page->page_name }}</p>
                                    <p class="text-[10px] text-slate-400 font-mono truncate">ID: {{ $page->page_id }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/60 flex items-start gap-2.5 sm:gap-3 text-amber-800 dark:text-amber-300 text-xs">
                        <i class="fa-solid fa-triangle-exclamation text-base mt-0.5 shrink-0 text-amber-600"></i>
                        <div class="space-y-1">
                            <p class="font-bold">আপনার কোনো সক্রিয় ফেসবুক পেজ সংযুক্ত নেই!</p>
                            <p class="text-[11px] text-amber-700 dark:text-amber-400">সরাসরি ফেসবুকে পোস্ট করতে প্রথমে সেটিংস থেকে আপনার পেজ যুক্ত করুন।</p>
                            <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-1 font-bold underline hover:text-amber-900 mt-1">
                                <span>পেজ কানেক্ট করতে সেটিংস-এ যান</span>
                                <i class="fa-solid fa-arrow-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. POST CAPTION / MESSAGE --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="directFbCaption" class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="fa-solid fa-pen-nib text-indigo-600"></i>
                        <span>পোস্টের মূল বার্তা / ক্যাপশন</span>
                    </label>
                    <span class="text-[10px] text-slate-400 font-medium" id="directFbCharCount">0 অক্ষর</span>
                </div>
                <textarea id="directFbCaption" 
                          name="message" 
                          rows="3" 
                          oninput="updateDirectFbCharCount(this)" 
                          placeholder="এখানে ফেসবুক পোস্টের মূল বক্তব্য, সংবাদ বা ক্যাপশন লিখুন..." 
                          class="w-full border border-slate-200 dark:border-slate-700 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 text-xs sm:text-sm font-medium text-slate-800 dark:text-white bg-slate-50/60 dark:bg-slate-950/60 focus:bg-white dark:focus:bg-slate-950 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition placeholder:text-slate-400"></textarea>
            </div>

            {{-- 3. MEDIA / IMAGE ATTACHMENT --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i class="fa-regular fa-image text-emerald-600"></i>
                    <span>মিডিয়া / ফটো সংযুক্ত করুন <span class="text-slate-400 font-normal text-[11px]">(ঐচ্ছিক)</span></span>
                </label>

                {{-- Hidden Native File Input --}}
                <input type="file" 
                       id="directFbImageFile" 
                       name="image" 
                       accept="image/jpeg,image/png,image/webp,image/gif" 
                       onchange="handleDirectFbImageSelect(this)" 
                       class="hidden">

                {{-- Drag & Drop Upload Box --}}
                <div id="directFbDropzone" 
                     onclick="document.getElementById('directFbImageFile').click()"
                     ondragover="handleDirectFbDragOver(event)"
                     ondragleave="handleDirectFbDragLeave(event)"
                     ondrop="handleDirectFbDrop(event)"
                     class="border-2 border-dashed border-slate-300 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 rounded-xl sm:rounded-2xl p-3 sm:p-4 text-center cursor-pointer bg-slate-50/60 dark:bg-slate-950/40 hover:bg-blue-50/30 dark:hover:bg-blue-950/20 transition-all flex flex-col items-center justify-center gap-1 sm:gap-1.5 group">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-blue-50 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 flex items-center justify-center text-base sm:text-lg group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                        <span class="text-blue-600 dark:text-blue-400 font-extrabold hover:underline">ছবি নির্বাচন করতে ক্লিক করুন</span> <span class="hidden sm:inline">অথবা এখানে টেনে আনুন</span>
                    </div>
                    <p class="text-[10px] sm:text-[11px] text-slate-400">JPG, PNG, WebP (সর্বোচ্চ ২০ মেগাবাইট)</p>
                </div>

                {{-- Selected Image Preview Container --}}
                <div id="directFbImagePreviewContainer" class="hidden relative rounded-xl sm:rounded-2xl border border-slate-200 dark:border-slate-700 p-2 sm:p-2.5 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between gap-2.5 sm:gap-3">
                    <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                        <img id="directFbImagePreviewImg" src="" alt="Preview" class="w-12 h-12 sm:w-14 sm:h-14 object-cover rounded-xl shadow-xs border border-slate-200 dark:border-slate-700 shrink-0">
                        <div class="min-w-0 flex-1">
                            <p id="directFbImageFileName" class="text-xs font-extrabold text-slate-800 dark:text-slate-200 truncate">image.jpg</p>
                            <p id="directFbImageFileSize" class="text-[10px] text-slate-400 font-mono">0 KB</p>
                        </div>
                    </div>
                    <button type="button" 
                            onclick="clearDirectFbImage()" 
                            class="px-2.5 py-1.5 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 hover:bg-rose-100 dark:hover:bg-rose-900/60 font-bold text-xs transition flex items-center gap-1 shrink-0" 
                            title="ছবিটি বাদ দিন">
                        <i class="fa-solid fa-trash-can text-[11px]"></i>
                        <span class="hidden sm:inline">মুছে ফেলুন</span>
                    </button>
                </div>
            </div>

            {{-- 4. FIRST COMMENT INPUT (CORE FEATURE) --}}
            <div class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label for="directFbFirstComment" class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                        <i class="fa-regular fa-comment-dots text-blue-600"></i>
                        <span>💬 প্রথম কমেন্ট (First Comment / Link)</span>
                    </label>
                    <span class="text-[10px] font-bold text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 border border-blue-200/60 dark:border-blue-800/60 px-2 py-0.5 rounded-md">
                        Auto-Comment
                    </span>
                </div>
                <textarea id="directFbFirstComment" 
                          name="first_comment" 
                          rows="2" 
                          placeholder="যেমন: বিস্তারিত খবরের লিংক বা সোর্স কমেন্টে দিন..." 
                          class="w-full border border-slate-200 dark:border-slate-700 rounded-xl sm:rounded-2xl p-2.5 sm:p-3 text-xs sm:text-sm font-medium text-slate-800 dark:text-white bg-slate-50/60 dark:bg-slate-950/60 focus:bg-white dark:focus:bg-slate-950 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition placeholder:text-slate-400"></textarea>
                <p class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 flex items-start sm:items-center gap-1">
                    <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 sm:mt-0 shrink-0"></i>
                    <span>ফেসবুক পেজে পোস্ট হওয়া মাত্রই এই বার্তাটি স্বয়ংক্রিয়ভাবে প্রথম কমেন্ট হিসেবে যুক্ত হবে।</span>
                </p>
            </div>

            {{-- 5. FEEDBACK / RESULTS ALERT CONTAINER --}}
            <div id="directFbAlertContainer" class="hidden space-y-2"></div>
        </form>

        {{-- MODAL FOOTER --}}
        <div class="px-4 sm:px-6 py-3 sm:py-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900 flex items-center justify-between gap-2.5 sm:gap-3 shrink-0">
            <button type="button" 
                    onclick="closeDirectFacebookPostModal()" 
                    class="px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 font-bold text-xs transition cursor-pointer shrink-0">
                বাতিল
            </button>

            <button type="button" 
                    id="directFbSubmitBtn" 
                    onclick="submitDirectFacebookPost(event)" 
                    class="flex-1 sm:flex-initial px-4 sm:px-6 py-2 sm:py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-black text-xs sm:text-sm shadow-md shadow-blue-500/25 transition transform active:scale-95 cursor-pointer flex items-center justify-center gap-2 text-center {{ $modalFbPages->count() === 0 ? 'opacity-50 pointer-events-none' : '' }}">
                <span id="directFbSubmitIcon"><i class="fa-solid fa-paper-plane"></i></span>
                <span id="directFbSubmitText" class="truncate">ফেসবুকে পোস্ট করুন 🚀</span>
            </button>
        </div>
    </div>
</div>

{{-- MODAL SCRIPT LOGIC --}}
<script>
    // Open Direct Facebook Modal
    function openDirectFacebookPostModal(preselectedPageId = null) {
        const modal = document.getElementById('directFacebookPostModal');
        if (!modal) return;

        // Reset alert
        const alertBox = document.getElementById('directFbAlertContainer');
        if (alertBox) {
            alertBox.innerHTML = '';
            alertBox.classList.add('hidden');
        }

        // If a specific page ID is passed, check only that one
        const checkboxes = document.querySelectorAll('.direct-fb-page-checkbox');
        if (preselectedPageId && checkboxes.length > 0) {
            checkboxes.forEach(cb => {
                cb.checked = (cb.value == preselectedPageId);
            });
            checkDirectFbSelectAllState();
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Focus caption
        setTimeout(() => {
            const caption = document.getElementById('directFbCaption');
            if (caption) caption.focus();
        }, 100);
    }

    // Close Direct Facebook Modal
    function closeDirectFacebookPostModal() {
        const modal = document.getElementById('directFacebookPostModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Close on Escape Key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('directFacebookPostModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeDirectFacebookPostModal();
            }
        }
    });

    // Toggle All Pages Checkbox
    function toggleAllDirectFbPages(isChecked) {
        const checkboxes = document.querySelectorAll('.direct-fb-page-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = isChecked;
        });
    }

    // Check "Select All" Checkbox State
    function checkDirectFbSelectAllState() {
        const selectAll = document.getElementById('directFbSelectAll');
        const checkboxes = document.querySelectorAll('.direct-fb-page-checkbox');
        if (!selectAll || checkboxes.length === 0) return;

        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        selectAll.checked = allChecked;
    }

    // Update Caption Character Count
    function updateDirectFbCharCount(textarea) {
        const counter = document.getElementById('directFbCharCount');
        if (counter) {
            counter.innerText = (textarea.value.length) + ' অক্ষর';
        }
    }

    // Image Selection & Preview
    function handleDirectFbImageSelect(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];

        if (file.size > 20 * 1024 * 1024) {
            alert('⚠️ ছবির সাইজ সর্বোচ্চ ২০ মেগাবাইট হতে পারবে।');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const dropzone = document.getElementById('directFbDropzone');
            const previewContainer = document.getElementById('directFbImagePreviewContainer');
            const previewImg = document.getElementById('directFbImagePreviewImg');
            const fileName = document.getElementById('directFbImageFileName');
            const fileSize = document.getElementById('directFbImageFileSize');

            if (previewImg) previewImg.src = e.target.result;
            if (fileName) fileName.innerText = file.name;
            if (fileSize) fileSize.innerText = (file.size / 1024).toFixed(1) + ' KB';

            if (dropzone) dropzone.classList.add('hidden');
            if (previewContainer) previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    // Clear Selected Image
    function clearDirectFbImage() {
        const input = document.getElementById('directFbImageFile');
        const dropzone = document.getElementById('directFbDropzone');
        const previewContainer = document.getElementById('directFbImagePreviewContainer');
        const previewImg = document.getElementById('directFbImagePreviewImg');

        if (input) input.value = '';
        if (previewImg) previewImg.src = '';
        if (previewContainer) previewContainer.classList.add('hidden');
        if (dropzone) dropzone.classList.remove('hidden');
    }

    // Drag and Drop Helpers
    function handleDirectFbDragOver(e) {
        e.preventDefault();
        e.stopPropagation();
        e.currentTarget.classList.add('border-blue-500', 'bg-blue-50/50');
    }

    function handleDirectFbDragLeave(e) {
        e.preventDefault();
        e.stopPropagation();
        e.currentTarget.classList.remove('border-blue-500', 'bg-blue-50/50');
    }

    function handleDirectFbDrop(e) {
        e.preventDefault();
        e.stopPropagation();
        e.currentTarget.classList.remove('border-blue-500', 'bg-blue-50/50');

        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) {
            const input = document.getElementById('directFbImageFile');
            if (input) {
                input.files = e.dataTransfer.files;
                handleDirectFbImageSelect(input);
            }
        }
    }

    // Submit Direct Facebook Post AJAX
    async function submitDirectFacebookPost(e) {
        if (e) e.preventDefault();

        const checkedPages = Array.from(document.querySelectorAll('.direct-fb-page-checkbox:checked')).map(cb => cb.value);
        if (checkedPages.length === 0) {
            alert('⚠️ অনুগ্রহ করে অন্তত একটি ফেসবুক পেজ নির্বাচন করুন।');
            return;
        }

        const captionInput = document.getElementById('directFbCaption');
        const firstCommentInput = document.getElementById('directFbFirstComment');
        const imageInput = document.getElementById('directFbImageFile');
        const alertBox = document.getElementById('directFbAlertContainer');
        const submitBtn = document.getElementById('directFbSubmitBtn');
        const submitIcon = document.getElementById('directFbSubmitIcon');
        const submitText = document.getElementById('directFbSubmitText');

        const caption = captionInput ? captionInput.value.trim() : '';
        const firstComment = firstCommentInput ? firstCommentInput.value.trim() : '';
        const hasImage = imageInput && imageInput.files && imageInput.files.length > 0;

        if (!caption && !hasImage) {
            alert('⚠️ পোস্ট করার জন্য ছবি অথবা ক্যাপশনের যেকোনো একটি অবশ্যই প্রদান করতে হবে।');
            if (captionInput) captionInput.focus();
            return;
        }

        // Prepare FormData
        const formData = new FormData();
        checkedPages.forEach(pageId => formData.append('pages[]', pageId));
        if (caption) formData.append('message', caption);
        if (firstComment) formData.append('first_comment', firstComment);
        if (hasImage) formData.append('image', imageInput.files[0]);

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        formData.append('_token', csrfToken);

        // UI Loading State
        if (submitBtn) submitBtn.disabled = true;
        if (submitIcon) submitIcon.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        if (submitText) submitText.innerText = 'পোস্ট হচ্ছে... অপেক্ষা করুন ⏳';

        if (alertBox) {
            alertBox.classList.remove('hidden');
            alertBox.innerHTML = `
                <div class="p-3.5 rounded-2xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 text-xs flex items-center gap-2.5">
                    <i class="fa-solid fa-spinner fa-spin text-sm shrink-0"></i>
                    <span>ফেসবুক গ্রাফ এপিআই-এর মাধ্যমে নির্বাচিত পেজে পোস্ট পাঠানো হচ্ছে...</span>
                </div>
            `;
        }

        try {
            const endpoint = "{{ route('facebook.direct-publish') }}";
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Generate Success HTML Report
                let resultsHtml = '';
                if (Array.isArray(data.results)) {
                    resultsHtml = data.results.map(r => {
                        const isOk = r.success;
                        const pageTitle = r.page_name || 'Facebook Page';
                        const commentStatus = r.comment_status;
                        const commentInfo = commentStatus === 'published' 
                            ? ' • 💬 ১ম কমেন্ট সফল' 
                            : (commentStatus === 'failed' ? ' • ⚠️ ১ম কমেন্ট ব্যর্থ' : '');

                        return `
                            <div class="p-2.5 rounded-xl border ${isOk ? 'border-emerald-200 bg-emerald-50/70 text-emerald-800 dark:border-emerald-800/60 dark:bg-emerald-950/40 dark:text-emerald-200' : 'border-rose-200 bg-rose-50/70 text-rose-800 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-200'} text-xs flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="font-black">${isOk ? '✅' : '❌'}</span>
                                    <span class="font-bold truncate">${pageTitle}</span>
                                    <span class="text-[11px] opacity-80">${commentInfo}</span>
                                </div>
                                ${r.post_id ? `<span class="text-[10px] font-mono opacity-70">ID: ${r.post_id}</span>` : `<span class="text-[11px] text-rose-600 font-medium">${r.error || 'এরর'}</span>`}
                            </div>
                        `;
                    }).join('');
                }

                alertBox.innerHTML = `
                    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-xs space-y-2.5">
                        <div class="flex items-center gap-2 font-black text-sm">
                            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                            <span>${data.message || 'ফেসবুক পেজে সফলভাবে পোস্ট সম্পন্ন হয়েছে!'}</span>
                        </div>
                        <div class="space-y-1.5 pt-1">${resultsHtml}</div>
                    </div>
                `;

                // Clear input values
                if (captionInput) captionInput.value = '';
                if (firstCommentInput) firstCommentInput.value = '';
                clearDirectFbImage();
                updateDirectFbCharCount({ value: '' });

            } else {
                // Failure handler
                const errMsg = data.message || 'ফেসবুকে পোস্ট করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
                let errorsList = '';
                if (Array.isArray(data.results)) {
                    errorsList = data.results.map(r => `
                        <div class="text-[11px] text-rose-700 dark:text-rose-300">
                            <strong>${r.page_name}:</strong> ${r.error || r.message || 'পোস্ট ব্যর্থ'}
                        </div>
                    `).join('');
                }

                alertBox.innerHTML = `
                    <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs space-y-2">
                        <div class="flex items-center gap-2 font-black">
                            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
                            <span>${errMsg}</span>
                        </div>
                        ${errorsList ? `<div class="pt-1 border-t border-rose-200/60 dark:border-rose-800/60 space-y-1">${errorsList}</div>` : ''}
                    </div>
                `;
            }

        } catch (error) {
            console.error('Direct FB post error:', error);
            if (alertBox) {
                alertBox.innerHTML = `
                    <div class="p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-xs flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                        <span>নেটওয়ার্ক বা সার্ভার সমস্যা: ${error.message}</span>
                    </div>
                `;
            }
        } finally {
            if (submitBtn) submitBtn.disabled = false;
            if (submitIcon) submitIcon.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
            if (submitText) submitText.innerText = 'ফেসবুকে পোস্ট করুন 🚀';
        }
    }
</script>
@endauth
