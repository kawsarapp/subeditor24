@extends('layouts.app')

@section('content')

{{-- Import Bengali Fonts & Studio Styles --}}
<script>
@include('partials.studio_fonts')
</script>
@include('partials.studio_styles')

{{-- External Libraries (Fabric.js & Sortable.js) --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.2/Sortable.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

<div class="fixed inset-0 bg-slate-900/5 z-50 flex flex-col font-bangla h-dvh select-none bg-slate-100">
    
    {{-- ========================================================= --}}
    {{-- TOP NAVBAR --}}
    {{-- ========================================================= --}}
    <header class="bg-white border-b border-slate-200 px-4 py-2.5 flex flex-wrap items-center justify-between shadow-xs z-30 shrink-0 gap-3">
        
        {{-- Left: Brand & Dimensions --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('news.index') }}" class="flex items-center gap-1 text-slate-500 hover:text-slate-900 transition font-bold text-xs bg-slate-100 hover:bg-slate-200 px-2.5 py-1.5 rounded-xl">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="hidden sm:inline">ফিরে যান</span>
            </a>
            <div class="flex items-center gap-2">
                <h1 class="text-sm md:text-base font-black text-slate-800 flex items-center gap-1.5">
                    <span class="w-7 h-7 rounded-lg bg-gradient-to-tr from-violet-600 to-indigo-600 flex items-center justify-center text-white text-xs shadow-sm">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </span>
                    <span>কাস্টম ফটো কার্ড</span>
                </h1>
                <span id="canvas-dimensions-badge" class="bg-indigo-50 text-indigo-700 border border-indigo-200/80 px-2.5 py-0.5 rounded-full text-[11px] font-bold">
                    1080 × 1080 px
                </span>
            </div>
        </div>

        {{-- Center: Credits & Limits --}}
        <div class="hidden lg:flex items-center gap-3 bg-slate-50 border border-slate-200/80 px-3 py-1 rounded-xl text-xs font-bold text-slate-600">
            <div class="flex items-center gap-1 text-amber-700">
                <span>🪙 ক্রেডিট:</span>
                <span id="user-credits-display" class="font-black">{{ auth()->user()->credits ?? 0 }}</span>
            </div>
            <div class="w-[1px] h-3 bg-slate-300"></div>
            <div class="flex items-center gap-1 text-indigo-700">
                <span>🪄 আজকের BG Remove:</span>
                <span id="user-daily-bg-display" class="font-black">{{ $dailyUsed }}/{{ $dailyLimit }}</span>
            </div>
        </div>

        {{-- Right: Actions (Undo, Redo, Copy, Download) --}}
        <div class="flex items-center gap-1.5 flex-wrap">
            {{-- Undo / Redo --}}
            <button type="button" id="btn-undo" onclick="window.customStudio.undo()" class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-xl transition text-xs font-bold disabled:opacity-40" title="Undo (Ctrl+Z)">
                <i class="fa-solid fa-rotate-left"></i>
            </button>
            <button type="button" id="btn-redo" onclick="window.customStudio.redo()" class="p-2 text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-xl transition text-xs font-bold disabled:opacity-40" title="Redo (Ctrl+Y)">
                <i class="fa-solid fa-rotate-right"></i>
            </button>

            <div class="w-[1px] h-5 bg-slate-200 mx-1"></div>

            {{-- 1-Click Save Template --}}
            <button type="button" onclick="openSaveTemplateModal()" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 px-3 py-1.5 rounded-xl font-black text-xs transition flex items-center gap-1.5 shadow-xs border border-emerald-300/80" title="বর্তমান ডিজাইনটি ভবিষ্যতে ব্যবহারের জন্য টেমপ্লেট হিসেবে সেভ রাখুন">
                <i class="fa-solid fa-floppy-disk"></i>
                <span class="hidden sm:inline">সেভ টেমপ্লেট</span>
            </button>

            {{-- 1-Click Copy Image --}}
            <button type="button" onclick="window.customStudio.copyToClipboard()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-3 py-1.5 rounded-xl font-black text-xs transition flex items-center gap-1.5 shadow-xs border border-slate-200">
                <i class="fa-regular fa-copy"></i>
                <span class="hidden sm:inline">Copy</span>
            </button>

            {{-- Download Dropdown --}}
            <div class="relative inline-block text-left">
                <button type="button" onclick="toggleDownloadDropdown(event)" class="bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white px-3.5 py-1.5 rounded-xl font-black text-xs shadow-md shadow-indigo-500/25 transition flex items-center gap-1.5">
                    <i class="fa-solid fa-download"></i>
                    <span>ডাউনলোড</span>
                    <i class="fa-solid fa-chevron-down text-[9px] ml-0.5"></i>
                </button>

                <div id="download-dropdown-menu" class="hidden absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-2xl border border-slate-200 py-2 z-50">
                    <button type="button" onclick="window.customStudio.downloadCard('png', 1)" class="w-full px-4 py-2 text-left text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition flex items-center justify-between">
                        <span>Standard PNG (1x)</span>
                        <span class="text-[10px] text-slate-400">Web</span>
                    </button>
                    <button type="button" onclick="window.customStudio.downloadCard('png', 2)" class="w-full px-4 py-2 text-left text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition flex items-center justify-between">
                        <span>High-Res PNG (2x)</span>
                        <span class="text-[10px] text-indigo-500 font-black">HD</span>
                    </button>
                    <button type="button" onclick="window.customStudio.downloadCard('png', 3)" class="w-full px-4 py-2 text-left text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition flex items-center justify-between">
                        <span>Ultra 4K Print (3x)</span>
                        <span class="text-[10px] text-purple-500 font-black">Print</span>
                    </button>
                    <div class="border-t border-slate-100 my-1"></div>
                    <button type="button" onclick="window.customStudio.downloadCard('jpeg', 1)" class="w-full px-4 py-2 text-left text-xs font-bold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                        Standard JPEG (.jpg)
                    </button>
                </div>
            </div>
        </div>

    </header>

    {{-- ========================================================= --}}
    {{-- MAIN STUDIO WORKSPACE (SIDEBAR + CANVAS) --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col md:flex-row flex-1 overflow-hidden relative">
        
        {{-- 1. SIDEBAR PANELS --}}
        @include('news.custom-photo-card.sidebar-panels')

        {{-- 2. CANVAS WORKSPACE VIEWPORT --}}
        <main id="workspace-container" class="flex-1 bg-slate-200/90 relative flex items-center justify-center overflow-hidden p-4 md:p-6 bg-[radial-gradient(#cbd5e1_1px,transparent_1px)] [background-size:16px_16px]">
            
            {{-- Preloader Overlay --}}
            <div id="canvas-preloader" class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs z-40 flex flex-col items-center justify-center gap-3 hidden">
                <div class="w-12 h-12 rounded-2xl bg-white shadow-2xl flex items-center justify-center text-indigo-600 text-xl animate-bounce">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>
                <span id="canvas-preloader-text" class="text-white font-black text-sm bg-slate-900/80 px-4 py-1.5 rounded-full shadow-lg">
                    লোড হচ্ছে...
                </span>
            </div>

            {{-- Floating Dynamic Context Toolbar --}}
            <div id="floating-context-toolbar" class="absolute z-30 bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-xl rounded-2xl p-1.5 flex items-center gap-1.5 hidden select-none">
                
                {{-- Drag & Move Handle Button --}}
                <div id="floating-drag-handle" class="cursor-move py-1 px-2.5 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-xl transition flex items-center gap-1.5 font-bold text-[11px] select-none border border-indigo-200/70 shadow-xs" title="চেপে ধরে ড্র্যাগ করে যেকোনো জায়গায় সরান (Move Element)">
                    <i class="fa-solid fa-up-down-left-right text-xs"></i>
                    <span>মুভ</span>
                </div>

                <div class="w-[1px] h-4 bg-slate-200"></div>

                {{-- White-label Background Remove Button --}}
                <button type="button" id="floating-bg-remove-btn" onclick="window.customStudio.removeBackgroundActive()" 
                    class="px-2.5 py-1 bg-gradient-to-r from-violet-600 to-indigo-600 text-white rounded-xl text-xs font-black hover:from-violet-700 hover:to-indigo-700 transition flex items-center gap-1 shadow-sm">
                    <i class="fa-solid fa-scissors text-[10px]"></i>
                    <span>Background Remove</span>
                </button>

                {{-- Text Quick Font --}}
                <select id="floating-font-select" onchange="changeActiveFont(this.value)" class="text-xs font-bold text-slate-700 border border-slate-200 rounded-lg px-2 py-1 outline-none bg-slate-50">
                    <option value="'SolaimanLipi'">SolaimanLipi</option>
                    <option value="'Hind Siliguri', sans-serif">Hind Siliguri</option>
                    <option value="'Li Alinur Banglaborno'">Li Alinur</option>
                    <option value="'Noto Sans Bengali', sans-serif">Noto Sans</option>
                </select>

                <div class="w-[1px] h-4 bg-slate-200"></div>

                {{-- Duplicate --}}
                <button type="button" onclick="window.customStudio.duplicateActive()" class="p-1.5 text-slate-600 hover:text-indigo-600 hover:bg-slate-100 rounded-lg transition" title="ডুপ্লিকেট (Ctrl+D)">
                    <i class="fa-regular fa-copy text-xs"></i>
                </button>

                {{-- Delete --}}
                <button type="button" onclick="window.customStudio.deleteActive()" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="ডিলিট (Delete)">
                    <i class="fa-regular fa-trash-can text-xs"></i>
                </button>
            </div>

            {{-- Right-Click Context Menu (Canva / Photoshop Grade) --}}
            <div id="canvas-context-menu" class="hidden fixed z-50 bg-white/95 backdrop-blur-md border border-slate-200/90 shadow-2xl rounded-2xl py-1.5 w-56 text-xs font-bold text-slate-700 divide-y divide-slate-100 select-none">
                {{-- Layer Ordering --}}
                <div class="py-1">
                    <button type="button" onclick="window.customStudio.bringActiveToFront()" class="w-full px-3 py-1.5 text-left hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-between transition">
                        <span class="flex items-center gap-2"><span>🔝</span> <span>একদম উপরে আনুন</span></span>
                        <span class="text-[10px] text-slate-400 font-normal">Ctrl+]</span>
                    </button>
                    <button type="button" onclick="window.customStudio.bringActiveForward()" class="w-full px-3 py-1.5 text-left hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-between transition">
                        <span class="flex items-center gap-2"><span>⬆️</span> <span>এক স্তর উপরে</span></span>
                        <span class="text-[10px] text-slate-400 font-normal">]</span>
                    </button>
                    <button type="button" onclick="window.customStudio.sendActiveBackward()" class="w-full px-3 py-1.5 text-left hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-between transition">
                        <span class="flex items-center gap-2"><span>⬇️</span> <span>এক স্তর নিচে</span></span>
                        <span class="text-[10px] text-slate-400 font-normal">[</span>
                    </button>
                    <button type="button" onclick="window.customStudio.sendActiveToBack()" class="w-full px-3 py-1.5 text-left hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-between transition">
                        <span class="flex items-center gap-2"><span>🔻</span> <span>একদম নিচে পাঠান</span></span>
                        <span class="text-[10px] text-slate-400 font-normal">Ctrl+[</span>
                    </button>
                </div>

                {{-- Action Tools --}}
                <div class="py-1">
                    <button type="button" onclick="window.customStudio.toggleLockActive()" class="w-full px-3 py-1.5 text-left hover:bg-amber-50 hover:text-amber-700 flex items-center gap-2 transition">
                        <i class="fa-solid fa-lock w-4 text-center text-amber-500"></i> <span>লক / আনলক</span>
                    </button>
                    <button type="button" onclick="window.customStudio.duplicateActive()" class="w-full px-3 py-1.5 text-left hover:bg-indigo-50 hover:text-indigo-600 flex items-center justify-between transition">
                        <span class="flex items-center gap-2"><i class="fa-regular fa-copy w-4 text-center"></i> <span>ডুপ্লিকেট</span></span>
                        <span class="text-[10px] text-slate-400 font-normal">Ctrl+D</span>
                    </button>
                    <button type="button" id="context-bg-remove-btn" onclick="window.customStudio.removeBackgroundActive()" class="w-full px-3 py-1.5 text-left hover:bg-violet-50 hover:text-violet-600 flex items-center gap-2 transition">
                        <i class="fa-solid fa-scissors w-4 text-center text-violet-500"></i> <span>Background Remove</span>
                    </button>
                </div>

                {{-- Delete --}}
                <div class="py-1">
                    <button type="button" onclick="window.customStudio.deleteActive()" class="w-full px-3 py-1.5 text-left hover:bg-red-50 text-red-600 flex items-center justify-between transition">
                        <span class="flex items-center gap-2"><i class="fa-regular fa-trash-can w-4 text-center"></i> <span>মুছে ফেলুন</span></span>
                        <span class="text-[10px] text-red-400 font-normal">Delete</span>
                    </button>
                </div>
            </div>

            {{-- Zoom Controls Bar --}}
            <div class="absolute bottom-4 right-4 flex items-center bg-white/90 backdrop-blur-md shadow-lg rounded-full px-3 py-1.5 gap-2.5 z-20 border border-slate-200/80">
                <button type="button" onclick="window.customStudio.setZoom(-0.1)" class="text-slate-600 hover:text-indigo-600 font-black text-sm">➖</button>
                <span id="zoom-level-badge" class="text-xs font-black text-slate-500 select-none min-w-[36px] text-center">100%</span>
                <button type="button" onclick="window.customStudio.setZoom(0.1)" class="text-slate-600 hover:text-indigo-600 font-black text-sm">➕</button>
                <button type="button" onclick="window.customStudio.fitToScreen()" class="text-[11px] font-black text-indigo-600 hover:underline border-l border-slate-200 pl-2 ml-0.5">
                    ⟲ Fit
                </button>
            </div>

            {{-- Interactive Fabric Canvas Wrapper --}}
            <div id="canvas-wrapper" class="shadow-2xl transition-transform duration-150 ease-out origin-center ring-8 ring-white rounded-sm">
                <canvas id="customCardCanvas" width="1080" height="1080"></canvas>
            </div>

        </main>

    </div>

    {{-- ========================================================= --}}
    {{-- 💾 SAVE TEMPLATE MODAL --}}
    {{-- ========================================================= --}}
    <div id="save-template-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl shadow-2xl border border-slate-200 max-w-md w-full p-6 space-y-4 font-bangla transform transition-all scale-100">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-black text-slate-800 flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm">
                        <i class="fa-solid fa-bookmark"></i>
                    </span>
                    <span>কাস্টম টেমপ্লেট হিসেবে সংরক্ষণ করুন</span>
                </h3>
                <button type="button" onclick="closeSaveTemplateModal()" class="text-slate-400 hover:text-slate-700 text-lg">✕</button>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                বর্তমান ক্যানভাসের সমস্ত লেআউট, টেক্সট স্টাইল, পজিশন ও উপাদান টেমপ্লেট হিসেবে সংরক্ষিত থাকবে যাতে পরবর্তীতে ১-ক্লিকে আবার ব্যবহার করতে পারেন।
            </p>

            <div>
                <label class="text-xs font-bold text-slate-700 block mb-1.5">টেমপ্লেটের নাম লিখুন *</label>
                <input type="text" id="save-template-name-input" placeholder="যেমন: ব্রেকিং নিউজ ১৬:৯" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs font-bold text-slate-800 outline-none focus:border-emerald-500 bg-slate-50 focus:bg-white">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="button" onclick="closeSaveTemplateModal()" class="flex-1 py-2.5 bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs rounded-xl transition">
                    বাতিল
                </button>
                <button type="button" onclick="confirmSaveTemplate()" class="flex-1 py-2.5 bg-emerald-600 text-white hover:bg-emerald-700 font-black text-xs rounded-xl shadow-md transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-check"></i>
                    <span>সংরক্ষণ করুন</span>
                </button>
            </div>
        </div>
    </div>

</div>

{{-- Editor Engine Script --}}
<script src="{{ asset('js/custom-photo-card/editor-engine.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Initialize Studio
        window.customStudio = new CustomPhotoCardEngine({
            canvasId: 'customCardCanvas',
            workspaceContainerId: 'workspace-container',
            canvasWrapperId: 'canvas-wrapper',
            removeBgUrl: "{{ route('custom-photo-card.remove-bg') }}",
            uploadFrameUrl: "{{ route('custom-photo-card.upload-frame') }}",
            saveCardUrl: "{{ route('custom-photo-card.save') }}",
            csrfToken: "{{ csrf_token() }}",
            userId: {{ Auth::id() ?? 0 }},
            initialWidth: 1080,
            initialHeight: 1080,
            newsData: @json($newsItem ? ['title' => $newsItem->title, 'image_url' => $newsItem->image] : null),
        });
    });

    // Tab Switching
    function switchStudioTab(tabKey) {
        document.querySelectorAll('.studio-panel').forEach(p => p.classList.add('hidden'));
        document.querySelectorAll('.studio-tab-btn').forEach(b => {
            b.classList.remove('active', 'text-indigo-600', 'bg-white', 'shadow-sm');
            b.classList.add('text-slate-600');
        });

        const activePanel = document.getElementById('panel-' + tabKey);
        const activeBtn = document.getElementById('tab-btn-' + tabKey);
        if (activePanel) activePanel.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.add('active', 'text-indigo-600', 'bg-white', 'shadow-sm');
            activeBtn.classList.remove('text-slate-600');
        }

        if (tabKey === 'layers' && window.customStudio) {
            window.customStudio.renderLayersList();
        }
        if (tabKey === 'templates' && window.customStudio) {
            window.customStudio.renderCustomTemplatesList();
        }
    }

    // Canvas Presets
    function setCanvasPreset(w, h) {
        if (window.customStudio) {
            window.customStudio.setCanvasDimensions(w, h);
            window.customStudio.saveState();
        }
    }

    function applyCustomDimensions() {
        const w = parseInt(document.getElementById('custom-width-input').value) || 1080;
        const h = parseInt(document.getElementById('custom-height-input').value) || 1080;
        setCanvasPreset(w, h);
    }

    // Custom Frame Upload
    async function uploadCustomFrameFile(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const formData = new FormData();
        formData.append('frame', file);
        formData.append('_token', "{{ csrf_token() }}");

        if (window.customStudio) window.customStudio.showLoader("ফ্রেম আপলোড ও সাইজ ডিটেক্ট হচ্ছে...");

        try {
            const response = await fetch("{{ route('custom-photo-card.upload-frame') }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                body: formData
            });
            const data = await response.json();
            if (data.success && data.url) {
                window.customStudio.applyFrame(data.url, true);
            } else {
                alert(data.message || 'ফ্রেম আপলোড ব্যর্থ হয়েছে।');
                if (window.customStudio) window.customStudio.hideLoader();
            }
        } catch (e) {
            console.error(e);
            alert('ফ্রেম আপলোড করতে সমস্যা হয়েছে।');
            if (window.customStudio) window.customStudio.hideLoader();
        }
    }

    // Text formatting helpers
    function changeActiveFont(fontFamily) {
        const active = window.customStudio.canvas.getActiveObject();
        if (active && (active.type === 'i-text' || active.type === 'textbox')) {
            active.set('fontFamily', fontFamily);
            window.customStudio.canvas.renderAll();
            window.customStudio.saveState();
        }
    }

    function changeActiveTextColor(color) {
        if (window.customStudio) window.customStudio.setTextColor(color);
    }

    function resetTextColor() {
        if (window.customStudio) {
            window.customStudio.setTextColor('#1e293b');
            const picker = document.getElementById('text-color-picker');
            if (picker) picker.value = '#1e293b';
        }
    }

    function changeActiveTextBgColor(color) {
        if (window.customStudio) window.customStudio.setTextBackground(color);
    }

    function changeActiveFontSize(size) {
        if (window.customStudio) window.customStudio.setTextFontSize(size);
    }

    function applyTextStroke() {
        const color = document.getElementById('text-stroke-color-picker')?.value || '#000000';
        const width = document.getElementById('text-stroke-width-slider')?.value || 0;
        const valBadge = document.getElementById('text-stroke-width-val');
        if (valBadge) valBadge.innerText = width + 'px';

        if (window.customStudio) window.customStudio.setTextStroke(color, width);
    }

    function applyCustomTextShadow() {
        const color = document.getElementById('text-shadow-color-picker')?.value || '#000000';
        const blur = document.getElementById('text-shadow-blur-slider')?.value || 0;
        const ox = document.getElementById('text-shadow-x-slider')?.value || 0;
        const oy = document.getElementById('text-shadow-y-slider')?.value || 0;

        const blurVal = document.getElementById('text-shadow-blur-val');
        const xVal = document.getElementById('text-shadow-x-val');
        const yVal = document.getElementById('text-shadow-y-val');
        if (blurVal) blurVal.innerText = blur + 'px';
        if (xVal) xVal.innerText = ox + 'px';
        if (yVal) yVal.innerText = oy + 'px';

        if (window.customStudio) window.customStudio.setTextShadow(color, blur, ox, oy);
    }

    function toggleActiveTextStyle(style) {
        const active = window.customStudio.canvas.getActiveObject();
        if (!active || (active.type !== 'i-text' && active.type !== 'textbox')) return;

        if (style === 'bold') {
            active.set('fontWeight', active.fontWeight === 'bold' ? 'normal' : 'bold');
        } else if (style === 'italic') {
            active.set('fontStyle', active.fontStyle === 'italic' ? 'normal' : 'italic');
        } else if (style === 'underline') {
            active.set('underline', !active.underline);
        }
        window.customStudio.canvas.renderAll();
        window.customStudio.saveState();
    }

    function changeActiveTextAlign(align) {
        const active = window.customStudio.canvas.getActiveObject();
        if (active && (active.type === 'i-text' || active.type === 'textbox')) {
            active.set('textAlign', align);
            window.customStudio.canvas.renderAll();
            window.customStudio.saveState();
        }
    }

    // Shape helpers
    function changeShapeFill(color) {
        if (window.customStudio) window.customStudio.setShapeFill(color);
    }

    function changeShapeRadius(r) {
        const valBadge = document.getElementById('shape-corner-radius-val');
        if (valBadge) valBadge.innerText = r + 'px';
        if (window.customStudio) window.customStudio.setShapeRadius(r);
    }

    function applyShapeStroke() {
        const color = document.getElementById('shape-stroke-color-picker')?.value || '#000000';
        const width = document.getElementById('shape-stroke-width-slider')?.value || 0;
        const valBadge = document.getElementById('shape-stroke-width-val');
        if (valBadge) valBadge.innerText = width + 'px';
        if (window.customStudio) window.customStudio.setShapeStroke(color, width);
    }

    function applyCustomShapeShadow() {
        const color = document.getElementById('shape-shadow-color-picker')?.value || '#000000';
        const blur = document.getElementById('shape-shadow-blur-slider')?.value || 0;
        if (window.customStudio) window.customStudio.setShapeShadow(color, blur, 2, 3);
    }

    // Image helpers
    function flipActiveImage(axis) {
        const active = window.customStudio.canvas.getActiveObject();
        if (active && active.type === 'image') {
            if (axis === 'X') active.set('flipX', !active.flipX);
            if (axis === 'Y') active.set('flipY', !active.flipY);
            window.customStudio.canvas.renderAll();
            window.customStudio.saveState();
        }
    }

    function changeActiveOpacity(val) {
        const active = window.customStudio.canvas.getActiveObject();
        document.getElementById('opacity-val').innerText = Math.round(val * 100) + '%';
        if (active) {
            active.set('opacity', parseFloat(val));
            window.customStudio.canvas.renderAll();
            window.customStudio.saveState();
        }
    }

    function toggleDownloadDropdown(e) {
        e.stopPropagation();
        const menu = document.getElementById('download-dropdown-menu');
        if (menu) menu.classList.toggle('hidden');
    }

    // Template Modal Helpers
    function openSaveTemplateModal() {
        const modal = document.getElementById('save-template-modal');
        const input = document.getElementById('save-template-name-input');
        if (input) input.value = '';
        if (modal) modal.classList.remove('hidden');
    }

    function closeSaveTemplateModal() {
        const modal = document.getElementById('save-template-modal');
        if (modal) modal.classList.add('hidden');
    }

    function confirmSaveTemplate() {
        const name = document.getElementById('save-template-name-input')?.value;
        if (!name || !name.trim()) {
            alert('দয়া করে টেমপ্লেটের নাম লিখুন।');
            return;
        }
        if (window.customStudio) {
            window.customStudio.saveCurrentAsTemplate(name);
            switchStudioTab('templates');
        }
    }

    // Instant Live Preview for Quote Card
    function onQuoteFieldChange(field, value) {
        if (window.customStudio) {
            window.customStudio.updateQuoteLiveField(field, value);
        }
    }

    function previewQuotePhoto(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const lbl = document.getElementById('quote-photo-label');
            if (lbl) lbl.innerText = file.name.substring(0, 20) + '...';

            const reader = new FileReader();
            reader.onload = (e) => {
                if (window.customStudio) {
                    window.customStudio.previewQuoteImage(e.target.result);
                }
            };
            reader.readAsDataURL(file);
        }
    }

    function changeActiveImageZoom(percent) {
        if (window.customStudio) {
            window.customStudio.setActiveImageScale(percent);
        }
    }

    async function submitQuoteCardForm() {
        const quote = document.getElementById('quote-card-text')?.value;
        const name = document.getElementById('quote-card-name')?.value;
        const designation = document.getElementById('quote-card-desig')?.value;
        const pos = document.getElementById('quote-card-pos')?.value || 'left';
        const theme = document.getElementById('quote-card-theme')?.value || 'soft-blue';
        const font = document.getElementById('quote-card-font')?.value || "'SolaimanLipi'";
        const flipPhoto = document.getElementById('quote-card-flip-check')?.checked === true;
        const removeBg = document.getElementById('quote-card-bg-check')?.checked !== false;
        const photoInput = document.getElementById('quote-card-photo');

        if (!quote || !quote.trim()) {
            alert('দয়া করে মূল বক্তব্য বা উক্তি লিখুন।');
            return;
        }

        let imageSource = null;
        if (photoInput && photoInput.files && photoInput.files[0]) {
            const file = photoInput.files[0];
            imageSource = await new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => resolve(e.target.result);
                reader.readAsDataURL(file);
            });
        }

        if (window.customStudio) {
            window.customStudio.generateQuoteCard({
                quote: quote,
                name: name,
                designation: designation,
                position: pos,
                theme: theme,
                fontFamily: font,
                flipPhoto: flipPhoto,
                removeBg: removeBg,
                imageSource: imageSource
            });
        }
    }

    document.addEventListener('click', () => {
        const menu = document.getElementById('download-dropdown-menu');
        if (menu) menu.classList.add('hidden');
    });
</script>

@endsection
