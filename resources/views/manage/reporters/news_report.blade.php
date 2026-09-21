@extends('layouts.app')

@section('content')
{{-- 1. Scripts & Fonts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap');
    .font-bangla { font-family: 'Hind Siliguri', sans-serif; }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    .img-zoom:hover { transform: scale(1.05); transition: all 0.4s ease-in-out; cursor: zoom-in; }
    
    /* Mobile table fix */
    @media (max-width: 640px) {
        .mobile-table-card thead { display: none; }
        .mobile-table-card tr { display: block; margin-bottom: 1rem; border: 1px solid #f1f5f9; border-radius: 1.5rem; background: #fff; padding: 1rem; }
        .mobile-table-card td { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border: none; }
        .mobile-table-card td::before { content: attr(data-label); font-weight: 800; color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; }
    }
</style>

<div class="max-w-7xl mx-auto py-6 px-3 sm:px-6 lg:px-8 font-bangla bg-gray-50/30 min-h-screen">
    
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4 mb-8">
        <div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-chart-pie text-indigo-600"></i> Reporter News Submissions
            </h2>
            <p class="text-slate-500 mt-1 text-sm font-medium">Correspondent submission gallery and editorial publishing workflow</p>
        </div>
        <div class="bg-white px-5 py-2.5 rounded-2xl shadow-sm border border-slate-100 w-full sm:w-auto text-center flex items-center justify-between sm:justify-center gap-3">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-xs font-bold text-slate-600 uppercase tracking-wider">Total News: <span class="text-indigo-600 text-lg">{{ $news->total() }}</span></span>
        </div>
    </div>

    {{-- Main Table Section --}}
    <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/50 border border-slate-100 overflow-hidden transition-all duration-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse mobile-table-card">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100">
                        <th class="px-6 py-5 text-xs font-bold uppercase text-slate-400">Thumbnail</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase text-slate-400">Sender & Time</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase text-slate-400">Title & Info</th>
                        <th class="px-6 py-5 text-xs font-bold uppercase text-slate-400 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($news as $item)
                    @php
                        $isProcessed = ($item->is_posted || in_array($item->status, ['publishing', 'published']));
                        $extraImages = json_decode($item->tags, true);
                    @endphp

                    <tr class="transition-colors duration-300 {{ $isProcessed ? 'bg-emerald-50/10' : 'hover:bg-slate-50/80' }}">
                        <td class="px-6 py-4" data-label="Thumbnail">
                            <div class="relative w-20 h-14 sm:w-28 sm:h-20 rounded-xl overflow-hidden shadow-sm border border-slate-200 group">
                                <img src="{{ $item->thumbnail_url }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                @if(is_array($extraImages) && count($extraImages) > 0)
                                    <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-sm">
                                        <span class="text-xs text-white font-black"><i class="fa-solid fa-images mr-1"></i>+{{ count($extraImages) }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4" data-label="Sender">
                            <div class="font-bold text-sm flex items-center gap-2 {{ $isProcessed ? 'text-slate-400' : 'text-slate-700' }}">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-[10px]"><i class="fa-solid fa-user"></i></div>
                                {{ $item->reporter->name ?? $item->reporter_name_manual }}
                            </div>
                            <div class="text-[11px] text-slate-500 font-bold mt-2 flex items-center gap-1.5 ml-8">
                                <i class="fa-regular fa-clock text-indigo-400"></i> {{ $item->created_at->format('d M, h:i A') }}
                            </div>
                        </td>
                        
                        <td class="px-6 py-4" data-label="News">
                            <div class="font-bold text-base sm:text-lg leading-snug line-clamp-2 mb-2 {{ $isProcessed ? 'text-slate-400 line-through decoration-slate-300' : 'text-slate-800' }}">
                                {{ $item->title }}
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded-lg font-bold border border-slate-200">
                                    <i class="fa-solid fa-location-dot mr-1 text-rose-400"></i> {{ $item->location ?? 'No Location' }}
                                </span>
                                @if($isProcessed)
                                    <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-1 rounded-lg font-bold flex items-center gap-1 border border-emerald-200 shadow-sm">
                                        <i class="fa-solid fa-circle-check"></i> Completed
                                    </span>
                                @else
                                    <span class="text-[10px] bg-amber-50 text-amber-600 px-2 py-1 rounded-lg font-bold flex items-center gap-1 border border-amber-200">
                                        <i class="fa-solid fa-hourglass-half"></i> Pending
                                    </span>
                                @endif
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center" data-label="Actions">
                            <div class="flex flex-wrap justify-center sm:justify-center gap-2">
                                @if($isProcessed)
                                    <a href="{{ route('news.drafts') }}" class="w-full sm:w-auto px-5 py-2.5 bg-slate-800 text-white rounded-xl font-bold text-xs hover:bg-black hover:shadow-lg hover:-translate-y-0.5 transition-all text-center flex items-center justify-center gap-2">
                                        <i class="fa-solid fa-folder-open"></i> View in Drafts
                                    </a>
                                @elseif($item->locked_by_user_id)
                                    <button disabled class="w-full sm:w-auto px-5 py-2.5 bg-slate-100 text-slate-400 rounded-xl font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed border border-slate-200">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Busy
                                    </button>
                                @else
                                    <button onclick="openEditModal('{{ $item->id }}')" class="w-full sm:w-auto px-5 py-2.5 bg-amber-50 text-amber-600 rounded-xl font-bold text-xs hover:bg-amber-100 border border-amber-200 transition-all shadow-sm hover:-translate-y-0.5 flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-pen-to-square"></i> Review
                                    </button>
                                    <a href="{{ route('news.studio', $item->id) }}" class="w-full sm:w-auto px-5 py-2.5 bg-indigo-50 text-indigo-600 rounded-xl font-bold text-xs hover:bg-indigo-100 border border-indigo-200 transition-all shadow-sm hover:-translate-y-0.5 text-center flex items-center justify-center gap-1.5">
                                        <i class="fa-solid fa-palette"></i> Design
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-16 text-center">
                            <div class="flex flex-col items-center justify-center text-slate-400">
                                <i class="fa-regular fa-folder-open text-5xl mb-4 opacity-30"></i>
                                <p class="text-lg font-bold text-slate-600">No News Found!</p>
                                <p class="text-sm font-medium mt-1">No correspondents have submitted news yet.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        @if($news->hasPages())
        <div class="p-6 bg-slate-50/50 border-t border-slate-100">
            {{ $news->links() }}
        </div>
        @endif
    </div>
</div>

{{-- 3. News Edit & Gallery Modal --}}
<div id="editModal" class="fixed inset-0 bg-slate-900/90 hidden items-center justify-center z-[100] backdrop-blur-md px-2 sm:px-4">
    <div class="bg-white rounded-[2rem] sm:rounded-[2.5rem] shadow-2xl w-full max-w-6xl max-h-[96vh] overflow-hidden flex flex-col transform transition-all duration-300 scale-95 opacity-0 modal-container border border-slate-200">
        
        {{-- Modal Header --}}
        <div class="px-6 py-4 sm:px-10 sm:py-6 border-b border-slate-100 flex justify-between items-center bg-white/80 backdrop-blur-xl sticky top-0 z-20">
            <div>
                <h3 class="font-black text-xl sm:text-2xl text-slate-800 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-600 w-8 h-8 rounded-lg flex items-center justify-center text-sm"><i class="fa-solid fa-pen-clip"></i></span>
                    News Review Panel
                </h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1 hidden sm:block ml-10">Verify correspondent submission details</p>
            </div>
            <button onclick="closeEditModal()" class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-slate-100 text-slate-500 hover:bg-rose-100 hover:text-rose-600 hover:rotate-90 transition-all text-xl shadow-sm"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        {{-- Modal Body --}}
        <div class="overflow-y-auto p-5 sm:p-10 flex-grow custom-scrollbar bg-white">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
                
                {{-- Left: Gallery & Info --}}
                <div class="lg:col-span-5 space-y-6">
                    <div class="group">
                        <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2"><i class="fa-regular fa-images"></i> News Gallery</label>
                        <div class="relative aspect-video rounded-3xl overflow-hidden bg-slate-100 border border-slate-200 shadow-md group-hover:shadow-indigo-100 transition-all">
                            <img id="modalMainImg" src="" class="w-full h-full object-cover img-zoom" onclick="window.open(this.src)">
                        </div>
                        <div class="grid grid-cols-4 gap-3 mt-4" id="extraImagesGrid"></div>
                    </div>

                    <div class="p-6 bg-slate-50 rounded-[2rem] border border-slate-200 space-y-4 shadow-inner">
                        <div class="flex justify-between items-center border-b border-slate-200 pb-4">
                            <span class="text-xs font-bold text-slate-500 flex items-center gap-1"><i class="fa-solid fa-location-dot text-slate-300"></i> Location:</span>
                            <span class="text-sm font-black text-slate-700 bg-white px-3 py-1 rounded-lg shadow-sm border border-slate-100" id="infoLocation">--</span>
                        </div>
                        <div class="flex justify-between items-center pt-2">
                            <span class="text-xs font-bold text-slate-500 flex items-center gap-1"><i class="fa-solid fa-link text-slate-300"></i> Source Link:</span>
                            <a href="#" id="infoSource" target="_blank" class="text-xs font-black text-indigo-600 truncate max-w-[150px] hover:text-indigo-800 hover:underline flex items-center gap-1 bg-indigo-50 px-3 py-1 rounded-lg">
                                View Source <i class="fa-solid fa-external-link text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Right: Editor --}}
                <div class="lg:col-span-7 space-y-6">
                    <form id="editForm">
                        @csrf
                        <input type="hidden" id="editNewsId">
                        <div class="space-y-6">
                            <div class="group relative">
                                <div class="flex justify-between items-end mb-3">
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest transition-colors group-focus-within:text-indigo-600">Title</label>
                                    <span id="titleCount" class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">0 / 255</span>
                                </div>
                                <input type="text" id="editTitle" class="w-full border-2 border-slate-100 rounded-2xl p-4 sm:p-5 outline-none font-bold text-slate-800 text-lg sm:text-xl shadow-sm bg-slate-50 focus:border-indigo-500 focus:bg-white transition-all" placeholder="Enter news headline...">
                            </div>

                            <div class="p-4 sm:p-5 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-50"><i class="fa-solid fa-robot text-xl"></i></div>
                                    <div class="text-center sm:text-left">
                                        <p class="text-sm font-black text-indigo-900">AI Smart Writer</p>
                                        <p class="text-[11px] text-indigo-500 font-bold mt-0.5">Professionally rewrite the news with 1 click</p>
                                    </div>
                                </div>
                                <button type="button" onclick="triggerAI()" id="aiBtn" class="w-full sm:w-auto bg-indigo-600 text-white px-6 py-3.5 rounded-xl font-black text-xs hover:bg-indigo-700 hover:shadow-lg hover:shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-wand-magic-sparkles"></i> <span>Rewrite News</span>
                                </button>
                            </div>

                            <div class="group">
                                <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3 transition-colors group-focus-within:text-indigo-600">News Content</label>
                                <div class="rounded-2xl overflow-hidden border-2 border-slate-100 focus-within:border-indigo-500 transition-all shadow-sm">
                                    <textarea id="editContent"></textarea>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                                <div class="group">
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Category</label>
                                    <div class="relative">
                                        <select id="editCategory" class="w-full border-2 border-slate-100 rounded-2xl p-4 bg-slate-50 outline-none font-bold text-slate-700 shadow-sm appearance-none focus:border-indigo-500 focus:bg-white transition-all cursor-pointer"></select>
                                        <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                                    </div>
                                </div>
                                <div class="group">
                                    <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Tags</label>
                                    <div class="relative">
                                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-300"><i class="fa-solid fa-hashtag"></i></span>
                                        <input type="text" id="editTags" class="w-full border-2 border-slate-100 rounded-2xl p-4 pl-10 bg-slate-50 outline-none font-bold text-slate-700 shadow-sm focus:border-indigo-500 focus:bg-white transition-all" placeholder="Comma separated (e.g. National, Sports)">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Footer --}}
        <div class="px-6 py-5 sm:px-10 sm:py-6 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-end gap-3 sm:gap-4 sticky bottom-0 z-20">
            <button onclick="closeEditModal()" class="w-full sm:w-auto px-8 py-3.5 text-slate-500 font-black text-sm hover:bg-slate-200 hover:text-slate-700 border border-slate-200 hover:border-slate-300 rounded-2xl transition-all order-3 sm:order-1">Cancel</button>
            <button onclick="saveDraftOnly()" id="draftBtn" class="w-full sm:w-auto px-8 py-3.5 bg-slate-700 text-white font-black text-sm rounded-2xl hover:bg-slate-800 hover:shadow-lg transition-all flex items-center justify-center gap-2 order-2">
                <i class="fa-solid fa-floppy-disk"></i> Save to Drafts
            </button>
            <button onclick="submitFinalPublish()" id="publishBtn" class="w-full sm:w-auto px-10 py-3.5 bg-emerald-600 text-white font-black text-sm rounded-2xl hover:bg-emerald-700 shadow-xl shadow-emerald-200 transition-all flex items-center justify-center gap-2 order-1 sm:order-3">
                <i class="fa-solid fa-paper-plane"></i> Publish News
            </button>
        </div>
    </div>
</div>

<script>
    // 1. TinyMCE Setup
    tinymce.init({
        selector: '#editContent',
        height: 350,
        plugins: 'lists link code wordcount autoresize',
        toolbar: 'undo redo | bold italic | bullist numlist | link code | removeformat',
        menubar: false,
        branding: false,
        statusbar: false,
        mobile: {
            menubar: false,
            toolbar: 'undo redo bold italic bullist link'
        },
        content_style: "@import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri&display=swap'); body { font-family: 'Hind Siliguri', sans-serif; font-size: 16px; padding: 15px; color: #334155; }"
    });

    // Title character counter
    document.getElementById('editTitle').addEventListener('input', function() {
        document.getElementById('titleCount').innerText = this.value.length + " / 255";
        if(this.value.length > 255) {
            this.classList.add('border-rose-500');
            document.getElementById('titleCount').classList.add('text-rose-500', 'bg-rose-100');
        } else {
            this.classList.remove('border-rose-500');
            document.getElementById('titleCount').classList.remove('text-rose-500', 'bg-rose-100');
        }
    });

    // 2. Modal control functions
    function openEditModal(newsId) {
        const modal = document.getElementById('editModal');
        const container = modal.querySelector('.modal-container');
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        setTimeout(() => {
            container.classList.remove('scale-95', 'opacity-0');
            container.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        // Loader While Fetching
        document.getElementById('editTitle').value = "Loading data...";
        if (tinymce.get('editContent')) tinymce.get('editContent').setContent("<p>Please wait...</p>");

        fetch(`/news/${newsId}/get-draft`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('editNewsId').value = newsId;
                document.getElementById('editTitle').value = data.title || '';
                document.getElementById('titleCount').innerText = (data.title ? data.title.length : 0) + " / 255";
                
                if (tinymce.get('editContent')) {
                    tinymce.get('editContent').setContent(data.content || '');
                }

                document.getElementById('modalMainImg').src = data.image_url || 'https://placehold.co/600x400?text=No+Image';
                document.getElementById('infoLocation').innerText = data.location || 'No Location';
                document.getElementById('infoSource').href = data.original_link || '#';
                
                // Backend sends data.hashtags
                document.getElementById('editTags').value = data.hashtags || '';

                const extraGrid = document.getElementById('extraImagesGrid');
                extraGrid.innerHTML = '';
                if(data.extra_images && data.extra_images.length > 0) {
                    data.extra_images.forEach(img => {
                        extraGrid.innerHTML += `
                            <div class="aspect-video rounded-xl overflow-hidden border border-slate-200 shadow-sm hover:shadow-md transition-all cursor-pointer">
                                <img src="${img}" class="w-full h-full object-cover img-zoom" onclick="window.open(this.src)">
                            </div>`;
                    });
                }

                const catSelect = document.getElementById('editCategory');
                catSelect.innerHTML = '<option value="">Select Category</option>';
                if (data.categories) {
                    Object.entries(data.categories).forEach(([name, id]) => {
                        catSelect.innerHTML += `<option value="${id}">${name}</option>`;
                    });
                }
            } else {
                Swal.fire('Error!', data.message, 'error');
                closeEditModal();
            }
        })
        .catch(err => {
            Swal.fire('Server Error!', 'Failed to load data.', 'error');
            closeEditModal();
        });
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        const container = modal.querySelector('.modal-container');
        const id = document.getElementById('editNewsId').value;

        container.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            if(id) {
                fetch(`/news/${id}/unlock`).then(() => location.reload());
            } else {
                location.reload();
            }
        }, 300);
    }

    // 3. AI Rewrite function
    function triggerAI() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Rewriting with AI will use 1 credit.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Yes, Rewrite!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const id = document.getElementById('editNewsId').value;
                const btn = document.getElementById('aiBtn');
                
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

                fetch(`/news/${id}/process-ai`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(res => {
                    // Laravel returns back(), reload on success
                    Swal.fire({
                        icon: 'success',
                        title: 'Processing Started!',
                        text: 'Reloading automatically...',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => location.reload());
                })
                .catch(err => {
                    Swal.fire('Error!', 'Could not connect to server.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i> <span>Rewrite News</span>';
                });
            }
        });
    }

    // 4. Draft & Publish functions
    function saveDraftOnly() { sendPostRequest(`/news/${document.getElementById('editNewsId').value}/update-draft`, 'draftBtn', 'Saved to Drafts successfully!'); }
    function submitFinalPublish() { sendPostRequest(`/news/${document.getElementById('editNewsId').value}/manual-publish`, 'publishBtn', 'Sent to Publishing Queue!'); }

    function sendPostRequest(url, btnId, successMsg) {
        const btn = document.getElementById(btnId);
        const originalText = btn.innerHTML;
        const formData = new FormData();
        
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('title', document.getElementById('editTitle').value);
        formData.append('content', tinymce.get('editContent').getContent());
        formData.append('category', document.getElementById('editCategory').value);
        
        // 🔥 BUG FIX: Backend expects 'hashtags', not 'tags'
        formData.append('hashtags', document.getElementById('editTags').value);

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

        fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: successMsg,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => location.reload());
            } else {
                Swal.fire('Error!', data.message, 'error');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        })
        .catch(err => {
            Swal.fire('Server Error!', 'Could not communicate with server.', 'error');
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    }
</script>
@endsection