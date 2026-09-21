@extends('layouts.app')

@section('title', 'Free Photo Card Generator - Subeditor24')

@push('styles')
<style>
    @import url('https://fonts.maateen.me/solaiman-lipi/font.css');
    @import url('https://fonts.maateen.me/kalpurush/font.css');
    @import url('https://fonts.googleapis.com/css2?family=Anek+Bangla:wght@600;700;800&family=Hind+Siliguri:wght@600;700&display=swap');

    .font-solaiman { font-family: 'SolaimanLipi', sans-serif; }
    .font-kalpurush { font-family: 'Kalpurush', sans-serif; }
    .font-anek { font-family: 'Anek Bangla', sans-serif; }
    .font-hind { font-family: 'Hind Siliguri', sans-serif; }

    #photoCardCanvas {
        cursor: default;
        touch-action: none;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-5 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg border border-indigo-100 dark:border-indigo-900/60 shrink-0 shadow-xs">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Free Photo Card Generator</h1>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Canva-Style Drag & Drop</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Drag & move images, headline text, and date directly on the canvas. Save positions to your PNG frame for instant reuse.</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto flex-wrap">
            <button type="button" onclick="saveCurrentLayoutToActiveTemplate()" id="saveLayoutBtn" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition cursor-pointer" title="Save current image, title and date positions to this template">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Save Layout Positions</span>
            </button>
            <button type="button" onclick="openNewTemplateModal()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>+ New Frame</span>
            </button>
        </div>
    </div>

    {{-- SAVED FRAME TEMPLATES SELECTOR --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-4 shadow-xs space-y-3">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                <i class="fa-solid fa-layer-group text-indigo-600"></i>
                <span>Your Saved Frame Templates:</span>
            </span>
            <span class="text-xs text-slate-500">Click any frame to switch templates</span>
        </div>

        <div class="flex items-center gap-3 overflow-x-auto pb-1 scrollbar-none" id="templatesPillContainer">
            @forelse($templates as $index => $tmpl)
                @php
                    $lData = $tmpl->layout_data ?? [];
                    $cw = $lData['canvas_width'] ?? 1200;
                    $ch = $lData['canvas_height'] ?? 1200;
                @endphp
                <div onclick="selectTemplate({{ $tmpl->id }})" id="tmpl-card-{{ $tmpl->id }}" class="tmpl-pill-card shrink-0 flex items-center gap-3 p-2.5 pr-4 rounded-xl border transition-all cursor-pointer {{ $index === 0 ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/60 shadow-xs' : 'border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-slate-50/50 dark:bg-slate-800/50' }}">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 border border-slate-700 overflow-hidden flex items-center justify-center shrink-0">
                        <img src="{{ $tmpl->frame_path }}" class="max-w-full max-h-full object-contain" alt="">
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-900 dark:text-white leading-tight">{{ $tmpl->name }}</p>
                        <p class="text-[10px] font-semibold text-slate-500 dark:text-slate-400 mt-0.5">{{ $cw }} × {{ $ch }} px</p>
                    </div>
                    <button type="button" onclick="event.stopPropagation(); editExistingTemplate({{ $tmpl->id }});" class="ml-2 text-slate-400 hover:text-indigo-600 p-1 text-xs" title="Edit template name & frame">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button type="button" onclick="event.stopPropagation(); deleteSavedTemplate({{ $tmpl->id }});" class="text-slate-400 hover:text-rose-600 p-1 text-xs" title="Delete frame">
                        <i class="fa-regular fa-trash-can"></i>
                    </button>
                </div>
            @empty
                <div class="w-full text-center py-4 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 space-y-1">
                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300">You have not added any PNG Frame Templates yet.</p>
                    <p class="text-[11px] text-slate-500">Upload your news portal's PNG overlay frame to save image & text coordinates for instant reuse.</p>
                    <button type="button" onclick="openNewTemplateModal()" class="mt-2 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition cursor-pointer">
                        <i class="fa-solid fa-plus text-xs"></i> Upload First Frame
                    </button>
                </div>
            @endforelse
        </div>
    </div>

    {{-- MAIN GENERATOR WORKSPACE --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT COLUMN: INPUTS & CONTROLS --}}
        <div class="lg:col-span-5 space-y-4">
            
            {{-- 1. URL INPUT CARD --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-3 shadow-xs">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                        <i class="fa-solid fa-link text-indigo-600"></i>
                        <span>Paste News Article Link</span>
                    </label>
                    <span class="text-[11px] text-slate-400">Instant scraper</span>
                </div>

                <div class="flex gap-2">
                    <input type="url" id="newsUrlInput" placeholder="https://yournewsportal.com/article/123..." class="flex-1 px-3.5 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                    <button type="button" id="fetchUrlBtn" onclick="fetchNewsFromUrl()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <i class="fa-solid fa-bolt"></i>
                        <span>Fetch</span>
                    </button>
                </div>
                <p id="fetchStatusMsg" class="hidden text-xs font-medium"></p>
            </div>

            {{-- 2. ARTICLE CONTENT CONTROLS --}}
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-4 shadow-xs">
                
                {{-- Headline Text --}}
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300">Headline / Title</label>
                        <span id="activeResolutionLabel" class="text-[11px] font-semibold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 px-2 py-0.5 rounded">1200 × 1200 px</span>
                    </div>
                    <textarea id="headlineInput" rows="3" oninput="renderCanvas()" placeholder="Enter or fetch headline..." class="w-full py-2.5 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 leading-relaxed font-solaiman">সংবাদের শিরোনাম এখানে দিন অথবা লিংক পেস্ট করে ফেচ করুন...</textarea>
                </div>

                {{-- Date Tag & Category Section --}}
                <div class="p-3.5 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="showDateToggle" checked onchange="toggleShowDate(event)" class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span>Show Date / Timestamp</span>
                        </label>
                        <span class="text-[10px] text-indigo-600 font-semibold">Draggable on Canvas</span>
                    </div>

                    <div id="dateConfigRow" class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1">
                        <div>
                            <input type="text" id="dateTextInput" value="{{ date('d M Y') }}" oninput="renderCanvas()" placeholder="e.g. ২২ সেপ্টেম্বর ২০২৬" class="w-full py-1.5 px-2.5 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg text-xs text-slate-900 dark:text-white font-medium">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="color" id="dateColorInput" value="#f3f4f6" onchange="renderCanvas()" class="w-7 h-7 rounded border border-slate-200 cursor-pointer">
                            <span class="text-[11px] text-slate-500">Size:</span>
                            <input type="number" id="dateFontSizeInput" value="28" min="14" max="60" oninput="renderCanvas()" class="w-16 py-1 px-2 text-xs bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-lg">
                        </div>
                    </div>
                </div>

                {{-- Featured Image Upload / Change --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Featured Image</label>
                    <div class="flex items-center gap-2">
                        <input type="file" id="localImageInput" accept="image/*" onchange="handleLocalImageUpload(event)" class="hidden">
                        <button type="button" onclick="document.getElementById('localImageInput').click()" class="w-full py-2 px-3 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 transition cursor-pointer flex items-center justify-center gap-2">
                            <i class="fa-solid fa-image text-slate-400"></i>
                            <span>Replace / Upload Image</span>
                        </button>
                    </div>
                </div>

                {{-- Typography & Styling Controls --}}
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3">
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Title Typography</p>
                    
                    <div class="grid grid-cols-2 gap-3">
                        {{-- Font Family --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Font Family</label>
                            <select id="fontFamilySelect" onchange="renderCanvas()" class="w-full py-1.5 px-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-900 dark:text-white">
                                <option value="SolaimanLipi" selected>SolaimanLipi</option>
                                <option value="Kalpurush">Kalpurush</option>
                                <option value="Hind Siliguri">Hind Siliguri</option>
                                <option value="Anek Bangla">Anek Bangla</option>
                            </select>
                        </div>

                        {{-- Font Size Slider --}}
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-[11px] font-semibold text-slate-600 dark:text-slate-400">Title Font Size</label>
                                <span id="fontSizeVal" class="text-[11px] font-bold text-slate-700 dark:text-slate-300">52px</span>
                            </div>
                            <input type="range" id="fontSizeRange" min="20" max="100" value="52" oninput="document.getElementById('fontSizeVal').innerText = this.value + 'px'; renderCanvas();" class="w-full accent-indigo-600 cursor-pointer">
                        </div>

                        {{-- Text Color --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Title Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="textColorInput" value="#ffffff" onchange="renderCanvas()" class="w-8 h-8 rounded-lg border border-slate-200 cursor-pointer">
                                <input type="text" id="textColorText" value="#ffffff" oninput="document.getElementById('textColorInput').value = this.value; renderCanvas();" class="w-20 px-2 py-1 text-xs bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg font-mono">
                            </div>
                        </div>

                        {{-- Text Alignment --}}
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Title Align</label>
                            <div class="flex rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-0.5">
                                <button type="button" onclick="setTextAlign('left')" class="flex-1 py-1 text-xs text-center text-slate-600 hover:text-indigo-600 font-bold" title="Left"><i class="fa-solid fa-align-left"></i></button>
                                <button type="button" onclick="setTextAlign('center')" class="flex-1 py-1 text-xs text-center text-indigo-600 font-bold bg-white dark:bg-slate-700 rounded shadow-xs" title="Center"><i class="fa-solid fa-align-center"></i></button>
                                <button type="button" onclick="setTextAlign('right')" class="flex-1 py-1 text-xs text-center text-slate-600 hover:text-indigo-600 font-bold" title="Right"><i class="fa-solid fa-align-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- RIGHT COLUMN: CANVA-LIKE INTERACTIVE CANVAS --}}
        <div class="lg:col-span-7 space-y-4">
            
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-5 space-y-4 shadow-xs">
                
                {{-- TOP PREVIEW BAR --}}
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <i class="fa-solid fa-arrows-up-down-left-right text-indigo-500"></i>
                            <span>Interactive Canvas (Drag & Move Elements)</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="copyCardToClipboard()" id="copyCardBtn" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-regular fa-copy"></i>
                            <span>Copy Image</span>
                        </button>
                        <button type="button" onclick="downloadCardHD()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-download"></i>
                            <span>Download HD</span>
                        </button>
                    </div>
                </div>

                {{-- CANVAS CONTAINER WITH DIRECT MOUSE INTERACTION --}}
                <div class="w-full flex items-center justify-center bg-slate-100 dark:bg-slate-950/80 rounded-2xl p-3 sm:p-6 border border-slate-200/80 dark:border-slate-800 overflow-hidden min-h-[460px] relative select-none">
                    <canvas id="photoCardCanvas" width="1200" height="1200" class="max-w-full max-h-[640px] h-auto object-contain rounded-xl shadow-lg border border-slate-300 dark:border-slate-700 bg-slate-900"></canvas>
                </div>

                {{-- USER GUIDES & CONTROLS FOOTER --}}
                <div class="text-[11px] text-slate-500 dark:text-slate-400 flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-1 border-t border-slate-100 dark:border-slate-800">
                    <span class="flex items-center gap-2">
                        <span>👆 <strong>Click & Drag</strong> any element (Image, Title, Date) directly on the canvas to move it.</span>
                    </span>
                    <button type="button" onclick="saveCurrentLayoutToActiveTemplate()" class="text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1 self-start sm:self-auto">
                        <i class="fa-solid fa-check"></i> Save Positions to Template
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- 🖼️ MODAL: CREATE / EDIT PNG FRAME TEMPLATE --}}
<div id="templateModal" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
        
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2" id="modalHeaderTitle">
                    <i class="fa-solid fa-crop-simple text-indigo-600"></i> Add New Frame Template
                </h3>
                <p class="text-xs text-slate-500">Upload your PNG frame with transparency.</p>
            </div>
            <button type="button" onclick="closeTemplateModal()" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 flex items-center justify-center text-sm cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="saveTemplateForm" onsubmit="handleSaveTemplate(event)" class="space-y-4">
            @csrf
            <input type="hidden" id="modal_template_id" name="template_id" value="">
            <input type="hidden" id="modal_existing_frame_path" name="frame_path" value="">

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Template Name *</label>
                <input type="text" id="modal_tmpl_name" name="name" required placeholder="e.g. Breaking News 1:1, Facebook Landscape, etc." class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Upload PNG Frame *</label>
                <input type="file" id="modal_frame_file" name="frame_image" accept="image/png,image/webp" onchange="handleModalFrameUpload(event)" class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-white font-medium file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700">
                <p class="text-[11px] text-slate-400 mt-1">Upload any resolution PNG frame. The canvas will automatically adapt to its size.</p>
            </div>

            {{-- DETECTED DIMENSIONS BADGE --}}
            <div id="detectedDimBadge" class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 text-xs font-semibold text-indigo-900 dark:text-indigo-200 flex items-center justify-between">
                <span>Detected Dimensions: <strong id="detectedDimText">1200 × 1200 px</strong></span>
                <span class="text-[11px] text-indigo-600 font-bold">Auto Set</span>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeTemplateModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="saveTmplSubmitBtn" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition cursor-pointer flex items-center gap-1.5">
                    <i class="fa-solid fa-save"></i> <span>Save Template</span>
                </button>
            </div>
        </form>

    </div>
</div>

@push('scripts')
<script>
// All Saved Templates Data
let savedTemplates = @json($templates);
let activeTemplateId = savedTemplates.length > 0 ? savedTemplates[0].id : null;

// Canvas & Engine State
let canvas, ctx;
let currentNewsImage = null;
let currentFrameImage = null;
let currentTextAlign = 'center';

// Active Layout State
let activeLayout = {
    canvas_width: 1200,
    canvas_height: 1200,
    image_x: 0,
    image_y: 0,
    image_w: 1200,
    image_h: 800,
    title_x: 60,
    title_y: 860,
    title_w: 1080,
    font_size: 52,
    font_family: 'SolaimanLipi',
    font_color: '#ffffff',
    text_align: 'center',
    line_height: 1.35,
    bg_color: '#111827',
    show_date: true,
    date_text: '{{ date("d M Y") }}',
    date_x: 60,
    date_y: 810,
    date_font_size: 26,
    date_font_color: '#f3f4f6'
};

// Interactive Drag & Select State
let selectedElement = null; // 'image', 'title', 'date', or null
let isDragging = false;
let dragStartX = 0;
let dragStartY = 0;
let elementOrigX = 0;
let elementOrigY = 0;

// Computed bounding boxes for hit testing
let hitBoxes = {
    image: { x: 0, y: 0, w: 0, h: 0 },
    title: { x: 0, y: 0, w: 0, h: 0 },
    date:  { x: 0, y: 0, w: 0, h: 0 }
};

let modalCanvasWidth = 1200;
let modalCanvasHeight = 1200;

document.addEventListener('DOMContentLoaded', () => {
    canvas = document.getElementById('photoCardCanvas');
    ctx = canvas.getContext('2d');

    // Attach Mouse & Touch Events for direct on-canvas drag & drop
    canvas.addEventListener('mousedown', handleCanvasPointerDown);
    window.addEventListener('mousemove', handleCanvasPointerMove);
    window.addEventListener('mouseup', handleCanvasPointerUp);

    canvas.addEventListener('touchstart', handleCanvasTouchStart, { passive: false });
    window.addEventListener('touchmove', handleCanvasTouchMove, { passive: false });
    window.addEventListener('touchend', handleCanvasTouchEnd);

    // Default sample news image
    const sampleImg = new Image();
    sampleImg.crossOrigin = "anonymous";
    sampleImg.onload = () => {
        currentNewsImage = sampleImg;
        if (savedTemplates.length > 0) {
            selectTemplate(savedTemplates[0].id);
        } else {
            renderCanvas();
        }
    };
    sampleImg.src = "https://images.unsplash.com/photo-1585829365295-ab7cd400c167?w=1200&q=80";
});

// 🔄 Select and apply a template
function selectTemplate(tmplId) {
    const tmpl = savedTemplates.find(t => t.id === tmplId);
    if (!tmpl) return;

    activeTemplateId = tmpl.id;

    // Highlight active pill
    document.querySelectorAll('.tmpl-pill-card').forEach(c => {
        c.className = 'tmpl-pill-card shrink-0 flex items-center gap-3 p-2.5 pr-4 rounded-xl border transition-all cursor-pointer border-slate-200 dark:border-slate-700 hover:border-slate-300 dark:hover:border-slate-600 bg-slate-50/50 dark:bg-slate-800/50';
    });
    const activePill = document.getElementById('tmpl-card-' + tmpl.id);
    if (activePill) {
        activePill.className = 'tmpl-pill-card shrink-0 flex items-center gap-3 p-2.5 pr-4 rounded-xl border transition-all cursor-pointer border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/60 shadow-xs';
    }

    // Parse layout data
    if (tmpl.layout_data) {
        const parsed = typeof tmpl.layout_data === 'string' ? JSON.parse(tmpl.layout_data) : tmpl.layout_data;
        activeLayout = Object.assign(activeLayout, parsed);
    }

    // Set font controls from layout
    if (activeLayout.font_size) {
        document.getElementById('fontSizeRange').value = activeLayout.font_size;
        document.getElementById('fontSizeVal').innerText = activeLayout.font_size + 'px';
    }
    if (activeLayout.font_family) {
        document.getElementById('fontFamilySelect').value = activeLayout.font_family;
    }
    if (activeLayout.font_color) {
        document.getElementById('textColorInput').value = activeLayout.font_color;
        document.getElementById('textColorText').value = activeLayout.font_color;
    }
    if (activeLayout.text_align) {
        currentTextAlign = activeLayout.text_align;
    }
    if (activeLayout.date_font_size) {
        document.getElementById('dateFontSizeInput').value = activeLayout.date_font_size;
    }
    if (activeLayout.date_font_color) {
        document.getElementById('dateColorInput').value = activeLayout.date_font_color;
    }
    if (activeLayout.show_date !== undefined) {
        document.getElementById('showDateToggle').checked = activeLayout.show_date;
        document.getElementById('dateConfigRow').style.display = activeLayout.show_date ? 'grid' : 'none';
    }

    const cw = activeLayout.canvas_width || 1200;
    const ch = activeLayout.canvas_height || 1200;
    document.getElementById('activeResolutionLabel').innerText = `${cw} × ${ch} px`;

    // Load Frame Image
    if (tmpl.frame_path && tmpl.frame_path.trim() !== '') {
        const frameImg = new Image();
        frameImg.crossOrigin = "anonymous";
        frameImg.onload = () => {
            currentFrameImage = frameImg;
            activeLayout.canvas_width = frameImg.naturalWidth || cw;
            activeLayout.canvas_height = frameImg.naturalHeight || ch;
            document.getElementById('activeResolutionLabel').innerText = `${activeLayout.canvas_width} × ${activeLayout.canvas_height} px`;
            renderCanvas();
        };
        frameImg.src = tmpl.frame_path;
    } else {
        currentFrameImage = null;
        renderCanvas();
    }
}

function setTextAlign(align) {
    currentTextAlign = align;
    activeLayout.text_align = align;
    renderCanvas();
}

function toggleShowDate(e) {
    activeLayout.show_date = e.target.checked;
    document.getElementById('dateConfigRow').style.display = e.target.checked ? 'grid' : 'none';
    renderCanvas();
}

function handleLocalImageUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
            currentNewsImage = img;
            renderCanvas();
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

// ⚡ Fetch news from URL
function fetchNewsFromUrl() {
    const urlInput = document.getElementById('newsUrlInput');
    const statusMsg = document.getElementById('fetchStatusMsg');
    const btn = document.getElementById('fetchUrlBtn');
    const url = urlInput.value.trim();

    if (!url) {
        alert('Please enter a valid news article URL.');
        return;
    }

    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Fetching...';
    btn.disabled = true;
    statusMsg.className = 'text-xs font-semibold text-indigo-600 block animate-pulse';
    statusMsg.innerText = 'Extracting news headline & featured image...';

    fetch('{{ route("free-photocard.fetch-url") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ url: url })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            statusMsg.className = 'text-xs font-semibold text-emerald-600 block';
            statusMsg.innerText = '✅ Article successfully loaded!';
            
            if (data.title) {
                document.getElementById('headlineInput').value = data.title;
            }

            if (data.date) {
                const formattedDate = formatBanglaDate(data.date);
                document.getElementById('dateTextInput').value = formattedDate;
                activeLayout.date_text = formattedDate;
            }

            if (data.image_url) {
                const img = new Image();
                img.crossOrigin = "anonymous";
                img.onload = () => {
                    currentNewsImage = img;
                    renderCanvas();
                };
                img.onerror = () => {
                    renderCanvas();
                };
                img.src = data.image_url;
            } else {
                renderCanvas();
            }
        } else {
            statusMsg.className = 'text-xs font-semibold text-rose-600 block';
            statusMsg.innerText = '❌ ' + (data.message || 'Failed to fetch article.');
        }
    })
    .catch(err => {
        statusMsg.className = 'text-xs font-semibold text-rose-600 block';
        statusMsg.innerText = '❌ Error: ' + err.message;
    })
    .finally(() => {
        btn.innerHTML = '<i class="fa-solid fa-bolt"></i> Fetch';
        btn.disabled = false;
    });
}

// Convert Date into readable Bangla string
function formatBanglaDate(dateStr) {
    if (!dateStr) return '';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;

        const months = ['জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];
        const day = d.getDate();
        const month = months[d.getMonth()];
        const year = d.getFullYear();

        const toBnDigit = (num) => String(num).replace(/\d/g, d => "০১২৩৪৫৬৭৮৯"[d]);
        return `${toBnDigit(day)} ${month} ${toBnDigit(year)}`;
    } catch(e) {
        return dateStr;
    }
}

// ====================================================
// 🎨 CORE CANVAS RENDERER & INTERACTIVE DRAG PIPELINE
// ====================================================
function renderCanvas(isExport = false) {
    if (!ctx) return;

    const width = activeLayout.canvas_width || (currentFrameImage ? currentFrameImage.naturalWidth : 1200);
    const height = activeLayout.canvas_height || (currentFrameImage ? currentFrameImage.naturalHeight : 1200);

    canvas.width = width;
    canvas.height = height;

    // 1. Clear background
    ctx.fillStyle = activeLayout.bg_color || '#111827';
    ctx.fillRect(0, 0, width, height);

    // 2. Draw News Featured Image
    const imgX = activeLayout.image_x ?? 0;
    const imgY = activeLayout.image_y ?? 0;
    const imgW = activeLayout.image_w ?? width;
    const imgH = activeLayout.image_h ?? Math.round(height * 0.65);

    hitBoxes.image = { x: imgX, y: imgY, w: imgW, h: imgH };

    if (currentNewsImage && currentNewsImage.complete) {
        ctx.save();
        ctx.beginPath();
        ctx.rect(imgX, imgY, imgW, imgH);
        ctx.clip();

        // Proportional Cover-fit calculation
        const imgRatio = currentNewsImage.width / currentNewsImage.height;
        const targetRatio = imgW / imgH;
        let drawW, drawH, drawX, drawY;

        if (imgRatio > targetRatio) {
            drawH = imgH;
            drawW = imgH * imgRatio;
            drawX = imgX + (imgW - drawW) / 2;
            drawY = imgY;
        } else {
            drawW = imgW;
            drawH = imgW / imgRatio;
            drawX = imgX;
            drawY = imgY + (imgH - drawH) / 2;
        }

        ctx.drawImage(currentNewsImage, drawX, drawY, drawW, drawH);
        ctx.restore();
    }

    // 3. Draw User's PNG Frame Overlay
    if (currentFrameImage && currentFrameImage.complete) {
        ctx.drawImage(currentFrameImage, 0, 0, width, height);
    }

    // 4. Draw Date / Timestamp (if enabled)
    const showDate = document.getElementById('showDateToggle') ? document.getElementById('showDateToggle').checked : true;
    const dateText = document.getElementById('dateTextInput') ? document.getElementById('dateTextInput').value : '';
    const dateFontSize = parseInt(document.getElementById('dateFontSizeInput')?.value) || (activeLayout.date_font_size || 26);
    const dateColor = document.getElementById('dateColorInput')?.value || (activeLayout.date_font_color || '#f3f4f6');
    const dateX = activeLayout.date_x ?? 60;
    const dateY = activeLayout.date_y ?? (activeLayout.title_y ? activeLayout.title_y - 45 : 810);

    if (showDate && dateText.trim() !== '') {
        ctx.font = `600 ${dateFontSize}px "${document.getElementById('fontFamilySelect').value || 'SolaimanLipi'}", "SolaimanLipi", sans-serif`;
        ctx.fillStyle = dateColor;
        ctx.textBaseline = 'top';
        ctx.textAlign = 'left';

        const dateMetrics = ctx.measureText(dateText);
        hitBoxes.date = { x: dateX, y: dateY, w: dateMetrics.width, h: dateFontSize * 1.3 };

        ctx.fillText(dateText, dateX, dateY);
    } else {
        hitBoxes.date = { x: 0, y: 0, w: 0, h: 0 };
    }

    // 5. Draw Article Headline with Multi-line Wrapping
    const titleText = document.getElementById('headlineInput').value || '';
    const fontSize = parseInt(document.getElementById('fontSizeRange').value) || 52;
    const fontFamily = document.getElementById('fontFamilySelect').value || 'SolaimanLipi';
    const textColor = document.getElementById('textColorInput').value || '#ffffff';
    
    const titleX = activeLayout.title_x ?? 60;
    const titleY = activeLayout.title_y ?? Math.round(height * 0.72);
    const titleW = activeLayout.title_w ?? (width - 120);
    const lineHeight = fontSize * (activeLayout.line_height || 1.35);

    ctx.font = `bold ${fontSize}px "${fontFamily}", "SolaimanLipi", sans-serif`;
    ctx.fillStyle = textColor;
    ctx.textBaseline = 'top';
    ctx.textAlign = currentTextAlign;

    let posX = titleX;
    if (currentTextAlign === 'center') posX = titleX + titleW / 2;
    else if (currentTextAlign === 'right') posX = titleX + titleW;

    const linesCount = drawWrappedText(ctx, titleText, posX, titleY, titleW, lineHeight, activeLayout.title_max_lines || 3);
    const calculatedTitleH = Math.max(lineHeight, linesCount * lineHeight);

    hitBoxes.title = { x: titleX, y: titleY, w: titleW, h: calculatedTitleH };

    // 6. Draw Canva-style Selection Bounding Box & Handles (Only in interactive mode, NOT in exported image)
    if (!isExport && selectedElement) {
        const box = hitBoxes[selectedElement];
        if (box && box.w > 0 && box.h > 0) {
            ctx.save();
            ctx.strokeStyle = '#6366f1';
            ctx.lineWidth = 3;
            ctx.setLineDash([6, 4]);
            ctx.strokeRect(box.x, box.y, box.w, box.h);

            // Draw 4 Corner Handles
            ctx.fillStyle = '#ffffff';
            ctx.strokeStyle = '#4338ca';
            ctx.lineWidth = 2;
            ctx.setLineDash([]);
            
            const handleSize = 10;
            const corners = [
                { x: box.x, y: box.y },
                { x: box.x + box.w, y: box.y },
                { x: box.x, y: box.y + box.h },
                { x: box.x + box.w, y: box.y + box.h }
            ];

            corners.forEach(c => {
                ctx.fillRect(c.x - handleSize/2, c.y - handleSize/2, handleSize, handleSize);
                ctx.strokeRect(c.x - handleSize/2, c.y - handleSize/2, handleSize, handleSize);
            });

            // Element Label Badge
            ctx.fillStyle = '#4f46e5';
            ctx.font = 'bold 16px sans-serif';
            ctx.textAlign = 'left';
            const labelText = selectedElement.toUpperCase();
            const labelMetrics = ctx.measureText(labelText);
            ctx.fillRect(box.x, box.y - 24, labelMetrics.width + 12, 22);
            ctx.fillStyle = '#ffffff';
            ctx.fillText(labelText, box.x + 6, box.y - 20);

            ctx.restore();
        }
    }
}

// Multi-line Text Wrapping Helper
function drawWrappedText(context, text, x, y, maxWidth, lineHeight, maxLines) {
    const words = text.split(' ');
    let line = '';
    let currentY = y;
    let linesDrawn = 1;

    for (let n = 0; n < words.length; n++) {
        const testLine = line + words[n] + ' ';
        const metrics = context.measureText(testLine);
        const testWidth = metrics.width;

        if (testWidth > maxWidth && n > 0) {
            context.fillText(line.trim(), x, currentY);
            line = words[n] + ' ';
            currentY += lineHeight;
            linesDrawn++;
            if (linesDrawn >= maxLines) {
                let remainingWords = words.slice(n).join(' ');
                while (context.measureText(remainingWords + '...').width > maxWidth && remainingWords.length > 0) {
                    remainingWords = remainingWords.substring(0, remainingWords.length - 1);
                }
                context.fillText(remainingWords + '...', x, currentY);
                return linesDrawn;
            }
        } else {
            line = testLine;
        }
    }
    context.fillText(line.trim(), x, currentY);
    return linesDrawn;
}

// ==========================================
// 🖱️ CANVA-STYLE POINTER DRAGGING SYSTEM
// ==========================================
function getCanvasCoordinates(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    const clientX = e.clientX !== undefined ? e.clientX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
    const clientY = e.clientY !== undefined ? e.clientY : (e.touches && e.touches[0] ? e.touches[0].clientY : 0);

    return {
        x: (clientX - rect.left) * scaleX,
        y: (clientY - rect.top) * scaleY
    };
}

function handleCanvasPointerDown(e) {
    const pos = getCanvasCoordinates(e);

    // Hit test order: Date -> Title -> Image
    if (hitBoxes.date.w > 0 && isInsideBox(pos, hitBoxes.date)) {
        selectedElement = 'date';
        elementOrigX = activeLayout.date_x ?? 60;
        elementOrigY = activeLayout.date_y ?? (activeLayout.title_y - 45);
    } else if (isInsideBox(pos, hitBoxes.title)) {
        selectedElement = 'title';
        elementOrigX = activeLayout.title_x ?? 60;
        elementOrigY = activeLayout.title_y ?? Math.round(canvas.height * 0.72);
    } else if (isInsideBox(pos, hitBoxes.image)) {
        selectedElement = 'image';
        elementOrigX = activeLayout.image_x ?? 0;
        elementOrigY = activeLayout.image_y ?? 0;
    } else {
        selectedElement = null;
        renderCanvas();
        return;
    }

    isDragging = true;
    dragStartX = pos.x;
    dragStartY = pos.y;
    canvas.style.cursor = 'move';
    renderCanvas();
}

function handleCanvasPointerMove(e) {
    const pos = getCanvasCoordinates(e);

    if (isDragging && selectedElement) {
        const deltaX = Math.round(pos.x - dragStartX);
        const deltaY = Math.round(pos.y - dragStartY);

        if (selectedElement === 'date') {
            activeLayout.date_x = elementOrigX + deltaX;
            activeLayout.date_y = elementOrigY + deltaY;
        } else if (selectedElement === 'title') {
            activeLayout.title_x = elementOrigX + deltaX;
            activeLayout.title_y = elementOrigY + deltaY;
        } else if (selectedElement === 'image') {
            activeLayout.image_x = elementOrigX + deltaX;
            activeLayout.image_y = elementOrigY + deltaY;
        }

        renderCanvas();
    } else {
        // Change cursor on hover over elements
        if (isInsideBox(pos, hitBoxes.date) || isInsideBox(pos, hitBoxes.title) || isInsideBox(pos, hitBoxes.image)) {
            canvas.style.cursor = 'move';
        } else {
            canvas.style.cursor = 'default';
        }
    }
}

function handleCanvasPointerUp() {
    if (isDragging) {
        isDragging = false;
        canvas.style.cursor = 'default';
    }
}

// Touch event wrappers
function handleCanvasTouchStart(e) {
    if (e.touches.length === 1) {
        e.preventDefault();
        handleCanvasPointerDown(e.touches[0]);
    }
}
function handleCanvasTouchMove(e) {
    if (e.touches.length === 1 && isDragging) {
        e.preventDefault();
        handleCanvasPointerMove(e.touches[0]);
    }
}
function handleCanvasTouchEnd() {
    handleCanvasPointerUp();
}

function isInsideBox(pos, box) {
    return pos.x >= box.x && pos.x <= (box.x + box.w) &&
           pos.y >= box.y && pos.y <= (box.y + box.h);
}

// ==========================================
// 💾 SAVE CURRENT LAYOUT TO ACTIVE TEMPLATE
// ==========================================
function saveCurrentLayoutToActiveTemplate() {
    if (!activeTemplateId) {
        alert('Please create and select a frame template first.');
        openNewTemplateModal();
        return;
    }

    const tmpl = savedTemplates.find(t => t.id === activeTemplateId);
    if (!tmpl) return;

    const btn = document.getElementById('saveLayoutBtn');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    btn.disabled = true;

    // Compile active layout coordinates
    const layoutToSave = {
        canvas_width: activeLayout.canvas_width || 1200,
        canvas_height: activeLayout.canvas_height || 1200,
        image_x: activeLayout.image_x ?? 0,
        image_y: activeLayout.image_y ?? 0,
        image_w: activeLayout.image_w ?? (activeLayout.canvas_width || 1200),
        image_h: activeLayout.image_h ?? 800,
        title_x: activeLayout.title_x ?? 60,
        title_y: activeLayout.title_y ?? 860,
        title_w: activeLayout.title_w ?? 1080,
        font_size: parseInt(document.getElementById('fontSizeRange').value) || 52,
        font_family: document.getElementById('fontFamilySelect').value || 'SolaimanLipi',
        font_color: document.getElementById('textColorInput').value || '#ffffff',
        text_align: currentTextAlign,
        line_height: 1.35,
        show_date: document.getElementById('showDateToggle').checked,
        date_x: activeLayout.date_x ?? 60,
        date_y: activeLayout.date_y ?? 810,
        date_font_size: parseInt(document.getElementById('dateFontSizeInput').value) || 26,
        date_font_color: document.getElementById('dateColorInput').value || '#f3f4f6'
    };

    const formData = new FormData();
    formData.append('template_id', tmpl.id);
    formData.append('name', tmpl.name);
    formData.append('frame_path', tmpl.frame_path);
    formData.append('layout_data', JSON.stringify(layoutToSave));

    fetch('{{ route("free-photocard.save-template") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Positions Saved!';
            tmpl.layout_data = layoutToSave;
            setTimeout(() => { btn.innerHTML = originalText; btn.disabled = false; }, 2500);
        } else {
            alert('❌ ' + (data.message || 'Error saving layout.'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(err => {
        alert('❌ Error: ' + err.message);
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

// ⬇️ Download HD PNG (Clean render without bounding boxes)
function downloadCardHD() {
    selectedElement = null; // deselect for clean render
    renderCanvas(true);
    const link = document.createElement('a');
    link.download = 'photocard_' + Date.now() + '.png';
    link.href = canvas.toDataURL('image/png', 1.0);
    link.click();
    renderCanvas(); // restore
}

// 📋 Copy Image to Clipboard
function copyCardToClipboard() {
    selectedElement = null;
    renderCanvas(true);
    const btn = document.getElementById('copyCardBtn');
    const originalText = btn.innerHTML;

    canvas.toBlob(blob => {
        if (!blob) return;
        try {
            const item = new ClipboardItem({ 'image/png': blob });
            navigator.clipboard.write([item]).then(() => {
                btn.innerHTML = '<i class="fa-solid fa-check text-emerald-500"></i> Copied!';
                setTimeout(() => { btn.innerHTML = originalText; }, 2500);
            }).catch(err => {
                alert('Clipboard copy failed. Please use Download HD.');
            });
        } catch(err) {
            alert('Clipboard copy is not supported in this browser. Please use Download HD.');
        }
        renderCanvas();
    }, 'image/png');
}

// ==========================================
// 🖼️ MODAL TEMPLATE MANAGEMENT
// ==========================================
function openNewTemplateModal() {
    document.getElementById('modal_template_id').value = '';
    document.getElementById('modal_existing_frame_path').value = '';
    document.getElementById('modal_tmpl_name').value = '';
    document.getElementById('modal_frame_file').value = '';
    document.getElementById('modalHeaderTitle').innerHTML = '<i class="fa-solid fa-crop-simple text-indigo-600"></i> Upload New PNG Frame';
    document.getElementById('detectedDimText').innerText = '1200 × 1200 px';
    document.getElementById('templateModal').classList.remove('hidden');
}

function editExistingTemplate(tmplId) {
    const tmpl = savedTemplates.find(t => t.id === tmplId);
    if (!tmpl) return;

    document.getElementById('modal_template_id').value = tmpl.id;
    document.getElementById('modal_existing_frame_path').value = tmpl.frame_path;
    document.getElementById('modal_tmpl_name').value = tmpl.name;
    document.getElementById('modalHeaderTitle').innerHTML = '<i class="fa-solid fa-pen-to-square text-indigo-600"></i> Edit Frame: ' + tmpl.name;

    const lData = typeof tmpl.layout_data === 'string' ? JSON.parse(tmpl.layout_data) : (tmpl.layout_data || {});
    const cw = lData.canvas_width || 1200;
    const ch = lData.canvas_height || 1200;
    document.getElementById('detectedDimText').innerText = `${cw} × ${ch} px`;

    document.getElementById('templateModal').classList.remove('hidden');
}

function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
}

function handleModalFrameUpload(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
            modalCanvasWidth = img.naturalWidth || 1200;
            modalCanvasHeight = img.naturalHeight || 1200;
            document.getElementById('detectedDimText').innerText = `${modalCanvasWidth} × ${modalCanvasHeight} px`;
        };
        img.src = event.target.result;
    };
    reader.readAsDataURL(file);
}

function handleSaveTemplate(e) {
    e.preventDefault();
    const form = document.getElementById('saveTemplateForm');
    const submitBtn = document.getElementById('saveTmplSubmitBtn');
    const formData = new FormData(form);

    const layoutData = {
        canvas_width: modalCanvasWidth || 1200,
        canvas_height: modalCanvasHeight || 1200,
        image_x: 0,
        image_y: 0,
        image_w: modalCanvasWidth || 1200,
        image_h: Math.round((modalCanvasHeight || 1200) * 0.65),
        title_x: Math.round((modalCanvasWidth || 1200) * 0.05),
        title_y: Math.round((modalCanvasHeight || 1200) * 0.72),
        title_w: Math.round((modalCanvasWidth || 1200) * 0.90),
        font_size: 52,
        font_family: 'SolaimanLipi',
        font_color: '#ffffff',
        text_align: 'center',
        line_height: 1.35,
        show_date: true,
        date_x: Math.round((modalCanvasWidth || 1200) * 0.05),
        date_y: Math.round((modalCanvasHeight || 1200) * 0.67),
        date_font_size: 26,
        date_font_color: '#f3f4f6'
    };

    formData.append('layout_data', JSON.stringify(layoutData));

    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;

    fetch('{{ route("free-photocard.save-template") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Template saved! You can now drag and position elements on the canvas.');
            window.location.reload();
        } else {
            alert('❌ ' + (data.message || 'Error saving template.'));
            submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Template';
            submitBtn.disabled = false;
        }
    })
    .catch(err => {
        alert('❌ Error: ' + err.message);
        submitBtn.innerHTML = '<i class="fa-solid fa-save"></i> Save Template';
        submitBtn.disabled = false;
    });
}

function deleteSavedTemplate(id) {
    if (!confirm('Are you sure you want to delete this frame template?')) return;

    fetch(`/free-photocard/template/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>
@endpush
@endsection
