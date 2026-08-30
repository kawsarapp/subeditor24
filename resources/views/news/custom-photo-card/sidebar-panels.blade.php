{{-- SIDEBAR CONTAINER --}}
<div class="w-full md:w-[390px] bg-white border-r border-slate-200 flex flex-col h-full z-20 shadow-lg select-none shrink-0 font-bangla">
    
    {{-- NAVIGATION TABS --}}
    <div class="flex border-b border-slate-200 bg-slate-50/80 p-1.5 gap-1 shrink-0 overflow-x-auto no-scrollbar">
        <button type="button" onclick="switchStudioTab('frames')" id="tab-btn-frames" class="studio-tab-btn active flex-1 py-2 px-2 rounded-xl text-xs font-black transition-all flex flex-col items-center gap-1 text-indigo-600 bg-white shadow-sm">
            <span class="text-base">🖼️</span>
            <span>ফ্রেম</span>
        </button>
        <button type="button" onclick="switchStudioTab('image')" id="tab-btn-image" class="studio-tab-btn flex-1 py-2 px-2 rounded-xl text-xs font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🪄</span>
            <span>ইমেজ / BG</span>
        </button>
        <button type="button" onclick="switchStudioTab('text')" id="tab-btn-text" class="studio-tab-btn flex-1 py-2 px-2 rounded-xl text-xs font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🅰️</span>
            <span>টেক্সট</span>
        </button>
        <button type="button" onclick="switchStudioTab('elements')" id="tab-btn-elements" class="studio-tab-btn flex-1 py-2 px-2 rounded-xl text-xs font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🏷️</span>
            <span>শেপ/ব্যাজ</span>
        </button>
        <button type="button" onclick="switchStudioTab('layers')" id="tab-btn-layers" class="studio-tab-btn flex-1 py-2 px-2 rounded-xl text-xs font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🗂️</span>
            <span>লেয়ার</span>
        </button>
    </div>

    {{-- TAB CONTENT AREA --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-6">
        
        {{-- ========================================================= --}}
        {{-- 1. FRAMES & TEMPLATES TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-frames" class="studio-panel space-y-5">
            
            {{-- Save Customized Design as Template Button --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-3.5 rounded-2xl text-white shadow-md flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-black flex items-center gap-1.5">
                        <i class="fa-solid fa-bookmark"></i> কাস্টম টেমপ্লেট সংরক্ষণ
                    </h4>
                    <p class="text-[10px] text-white/80 mt-0.5">বর্তমান লেআউট সেভ করে ভবিষ্যতে রি-ইউজ করুন</p>
                </div>
                <button type="button" onclick="openSaveTemplateModal()" class="px-3 py-1.5 bg-white text-emerald-800 font-black text-xs rounded-xl shadow hover:bg-emerald-50 transition transform active:scale-95">
                    + Save
                </button>
            </div>

            {{-- Saved Custom Templates Section --}}
            @if(isset($dbTemplates) && count($dbTemplates) > 0)
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-black text-slate-700 flex items-center gap-1.5">
                        <span>⭐ আমার সংরক্ষিত টেমপ্লেট</span>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ count($dbTemplates) }}টি</span>
                    </label>
                </div>
                <div id="saved-templates-container" class="grid grid-cols-2 gap-2.5 max-h-[220px] overflow-y-auto custom-scrollbar p-1">
                    @foreach($dbTemplates as $tpl)
                        <div class="saved-template-card cursor-pointer border border-slate-200 rounded-xl p-1.5 bg-slate-50 hover:bg-white hover:border-emerald-500 hover:shadow-md transition-all group relative flex flex-col items-center">
                            <div onclick="window.customStudio.loadCustomTemplate({{ json_encode($tpl->layout_data) }}, '{{ $tpl->frame_url }}')" 
                                class="w-full h-20 rounded-lg overflow-hidden bg-slate-200 flex items-center justify-center p-1 relative">
                                <img src="{{ $tpl->thumbnail_url ?: ($tpl->frame_url ?: asset('placeholder.png')) }}" alt="{{ $tpl->name }}" loading="lazy" class="w-full h-full object-contain">
                            </div>
                            <div class="flex items-center justify-between w-full mt-1.5 px-1">
                                <span class="text-[10px] font-bold text-slate-700 truncate">{{ $tpl->name }}</span>
                                <button type="button" onclick="window.customStudio.deleteCustomTemplate({{ $tpl->id }}, this)" class="text-red-400 hover:text-red-600 p-0.5 text-xs" title="মুছে ফেলুন">🗑️</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Custom PNG Frame Upload --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 flex items-center justify-between mb-2">
                    <span>📤 কাস্টম PNG ফ্রেম আপলোড</span>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Auto-Resize Size</span>
                </label>
                <label class="cursor-pointer border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-400 p-3.5 rounded-2xl text-center font-black text-xs text-indigo-700 transition-all flex flex-col items-center justify-center gap-1.5 group shadow-sm">
                    <input type="file" class="hidden" accept="image/png,image/webp" onchange="uploadCustomFrameFile(this)">
                    <div class="w-9 h-9 rounded-xl bg-white shadow flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                    </div>
                    <span>ফ্রেম নির্বাচন করুন (PNG)</span>
                    <span class="text-[10px] text-slate-400 font-normal">ফ্রেমের অরিজিনাল সাইজে ক্যানভাস কনভার্ট হবে</span>
                </label>
            </div>

            {{-- Preset Aspect Ratios --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-2">📐 ক্যানভাস সাইজ প্রিসেট</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="setCanvasPreset(1200, 675)" class="flex flex-col items-start p-2.5 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition text-left">
                        <span class="text-xs font-bold text-slate-800">16:9 Landscape</span>
                        <span class="text-[10px] text-slate-400">1200 × 675 px (News/YT)</span>
                    </button>
                    <button type="button" onclick="setCanvasPreset(1080, 1080)" class="flex flex-col items-start p-2.5 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition text-left">
                        <span class="text-xs font-bold text-slate-800">1:1 Square Post</span>
                        <span class="text-[10px] text-slate-400">1080 × 1080 px (FB/Insta)</span>
                    </button>
                    <button type="button" onclick="setCanvasPreset(1080, 1350)" class="flex flex-col items-start p-2.5 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition text-left">
                        <span class="text-xs font-bold text-slate-800">4:5 Portrait Feed</span>
                        <span class="text-[10px] text-slate-400">1080 × 1350 px</span>
                    </button>
                    <button type="button" onclick="setCanvasPreset(1080, 1920)" class="flex flex-col items-start p-2.5 rounded-xl border border-slate-200 hover:border-indigo-500 hover:bg-indigo-50/30 transition text-left">
                        <span class="text-xs font-bold text-slate-800">9:16 Story / Reel</span>
                        <span class="text-[10px] text-slate-400">1080 × 1920 px (Vertical)</span>
                    </button>
                </div>
            </div>

            {{-- Custom Width & Height Inputs --}}
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center gap-2">
                    <div class="flex-1">
                        <label class="text-[10px] font-bold text-slate-500 block mb-1">Width (px)</label>
                        <input type="number" id="custom-width-input" value="1080" class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500">
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] font-bold text-slate-500 block mb-1">Height (px)</label>
                        <input type="number" id="custom-height-input" value="1080" class="w-full border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500">
                    </div>
                    <button type="button" onclick="applyCustomDimensions()" class="mt-4 px-3 py-1.5 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition">
                        Set
                    </button>
                </div>
            </div>

            {{-- Available Admin Media Frames --}}
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-black text-slate-700 flex items-center gap-1.5">
                        <span>🖼️ মিডিয়া ফ্রেম লাইব্রেরি</span>
                        <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ count($frames) }}টি</span>
                    </label>
                    <button type="button" onclick="window.customStudio.removeFrame()" class="text-[10px] font-bold text-red-500 hover:text-red-700 hover:underline">
                        ফ্রেম রিমুভ
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2.5 max-h-[260px] overflow-y-auto custom-scrollbar p-1">
                    @forelse($frames as $f)
                        <div onclick="window.customStudio.applyFrame('{{ $f['url'] }}')" 
                            class="cursor-pointer border border-slate-200 rounded-xl p-1.5 bg-slate-50 hover:bg-white hover:border-indigo-500 hover:shadow-md transition-all group flex flex-col items-center">
                            <div class="w-full h-20 rounded-lg overflow-hidden bg-slate-200 flex items-center justify-center p-1 relative">
                                <img src="{{ $f['url'] }}" alt="{{ $f['name'] }}" loading="lazy" class="w-full h-full object-contain">
                                @if($f['width'] && $f['height'])
                                    <span class="absolute bottom-1 right-1 bg-black/70 text-white text-[8px] font-bold px-1 py-0.5 rounded backdrop-blur-xs">
                                        {{ $f['width'] }}×{{ $f['height'] }}
                                    </span>
                                @endif
                            </div>
                            <span class="text-[10px] font-bold text-slate-600 group-hover:text-indigo-600 truncate w-full text-center mt-1.5">
                                {{ $f['name'] }}
                            </span>
                        </div>
                    @empty
                        <div class="col-span-2 text-center py-6 text-slate-400 text-xs">
                            কোনো ফ্রেম পাওয়া যায়নি। উপরে কাস্টম PNG ফ্রেম আপলোড করুন।
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- 2. IMAGE & BACKGROUND REMOVER TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-image" class="studio-panel space-y-5 hidden">
            
            {{-- Upload Extra Image --}}
            <div>
                <label class="w-full cursor-pointer bg-slate-800 text-white p-3 rounded-2xl shadow-sm text-xs font-black hover:bg-black transition-all flex items-center justify-center gap-2">
                    <input type="file" accept="image/*" onchange="window.customStudio.addImageFromFile(this)" class="hidden">
                    <i class="fa-solid fa-plus-circle text-sm"></i>
                    <span>নতুন ছবি আপলোড করুন</span>
                </label>
            </div>

            {{-- Prominent White-Label Background Remover Button --}}
            <div class="bg-gradient-to-r from-violet-600 to-indigo-600 p-4 rounded-2xl text-white shadow-lg space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black tracking-wide flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> AI ব্যাকগ্রাউন্ড রিমুভার
                    </span>
                    <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-bold">
                        খরচ: {{ $creditCost }} ক্রেডিট
                    </span>
                </div>
                <p class="text-[11px] text-white/80 leading-snug">
                    ক্যানভাসে যেকোনো ছবি সিলেক্ট করে নিচের বাটনে ক্লিক করলেই মুহূর্তেই ব্যাকগ্রাউন্ড রিমুভ হয়ে যাবে।
                </p>
                <button type="button" onclick="window.customStudio.removeBackgroundActive()" 
                    class="w-full py-2.5 bg-white text-indigo-700 hover:bg-indigo-50 font-black text-xs rounded-xl shadow transition-transform transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-scissors"></i>
                    <span>Background Remove</span>
                </button>
            </div>

            {{-- Photo Extracted Colors & Reset --}}
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-black text-slate-700">🎨 ফটো কালার ও ক্যানভাস</span>
                    <button type="button" onclick="window.customStudio.resetCanvasBackground()" class="text-[10px] font-bold text-slate-500 hover:text-indigo-600 hover:underline">
                        ↺ হোয়াইট রিসেট
                    </button>
                </div>
                <div id="photo-color-palette"></div>
            </div>

            {{-- Dark Bottom Gradient Overlay --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-2">🌑 ডার্ক গ্রেডিয়েন্ট শ্যাডো (ফ্রেম ছাড়া লেখার জন্য)</label>
                <button type="button" onclick="window.customStudio.addShape('darkGradientOverlay')" 
                    class="w-full py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-black transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-fill-drip"></i>
                    <span>+ বটম ডার্ক শ্যাডো যোগ করুন</span>
                </button>
            </div>

            {{-- Main Image Adjustments --}}
            <div class="border-t border-slate-100 pt-3 space-y-3">
                <label class="text-xs font-black text-slate-700 block">🖼️ সিলেক্টেড ইমেজ কন্ট্রোল</label>
                
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="flipActiveImage('X')" class="py-2 px-3 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-arrows-left-right"></i> Flip H
                    </button>
                    <button type="button" onclick="flipActiveImage('Y')" class="py-2 px-3 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-arrows-up-down"></i> Flip V
                    </button>
                </div>

                <div>
                    <label class="text-[10px] font-bold text-slate-500 flex justify-between mb-1">
                        <span>অপাসিটি (স্বচ্ছতা)</span>
                        <span id="opacity-val">100%</span>
                    </label>
                    <input type="range" id="image-opacity-slider" min="0.1" max="1" step="0.05" value="1" oninput="changeActiveOpacity(this.value)" class="w-full accent-indigo-600">
                </div>
            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- 3. TEXT & TYPOGRAPHY TAB (CANVA/PHOTOSHOP GRADE) --}}
        {{-- ========================================================= --}}
        <div id="panel-text" class="studio-panel space-y-5 hidden">
            
            {{-- Add Text Buttons --}}
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="window.customStudio.addText('বড় হেডলাইন এখানে লিখুন', { fontSize: 48, fontWeight: 'bold' })" 
                    class="py-2.5 px-3 bg-slate-900 text-white rounded-xl font-bold text-xs hover:bg-black transition flex items-center justify-center gap-1.5 shadow-sm">
                    <span>+ হেডলাইন</span>
                </button>
                <button type="button" onclick="window.customStudio.addText('সাবটাইটেল বা বিস্তারিত বিবরণ', { fontSize: 28, fontWeight: 'normal' })" 
                    class="py-2.5 px-3 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-200 transition flex items-center justify-center gap-1.5">
                    <span>+ সাবটাইটেল</span>
                </button>
            </div>

            {{-- Bengali Font Selector --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-1.5">🅰️ বাংলা ফন্ট সিলেক্ট করুন</label>
                <select id="studio-font-select" onchange="changeActiveFont(this.value)" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500 bg-slate-50">
                    <optgroup label="🔥 Li Series (Stylish)">
                        <option value="'Li Alinur Banglaborno'">Li Alinur Banglaborno</option>
                        <option value="'Li Alinur Kuyasha'">Li Alinur Kuyasha</option>
                        <option value="'Li Alinur Sangbadpatra'">Li Alinur Sangbadpatra</option>
                        <option value="'Li Alinur Tumatul'">Li Alinur Tumatul</option>
                        <option value="'Li MA Hai'">Li M.A. Hai</option>
                        <option value="'Li Purno Pran'">Li Purno Pran</option>
                        <option value="'Li Sabbir Sorolota'">Li Sabbir Sorolota</option>
                        <option value="'Li Shohid Abu Sayed'">Li Shohid Abu Sayed</option>
                        <option value="'Li Shadhinata'">Li Shadhinata</option>
                    </optgroup>
                    <optgroup label="📰 Popular Bangla">
                        <option value="'SolaimanLipi'" selected>সোলাইমান লিপি (SolaimanLipi)</option>
                        <option value="'Hind Siliguri', sans-serif">হিন্দ শিলিগুড়ি (Hind Siliguri)</option>
                        <option value="'Noto Sans Bengali', sans-serif">নোটো স্যান্স (Noto Sans)</option>
                        <option value="'Noto Serif Bengali', serif">নোটো শেরিফ (Noto Serif)</option>
                        <option value="'Anek Bangla', sans-serif">অনেক বাংলা (Anek Bangla)</option>
                        <option value="'Kalpurush', sans-serif">কালপুরুষ (Kalpurush)</option>
                    </optgroup>
                </select>
            </div>

            {{-- Text Color & Background (with Reset Buttons) --}}
            <div class="border-t border-slate-100 pt-3 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[10px] font-bold text-slate-500">টেক্সট কালার</label>
                            <button type="button" onclick="resetTextColor()" class="text-[9px] text-indigo-600 font-bold hover:underline">রিসেট</button>
                        </div>
                        <input type="color" id="text-color-picker" value="#1e293b" oninput="changeActiveTextColor(this.value)" class="w-full h-9 rounded-xl border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[10px] font-bold text-slate-500">ব্যাকগ্রাউন্ড হাইলাইট</label>
                            <button type="button" onclick="window.customStudio.removeTextBackground()" class="text-[9px] text-red-500 font-bold hover:underline">❌ No BG</button>
                        </div>
                        <input type="color" id="text-bg-color-picker" value="#ffffff" oninput="changeActiveTextBgColor(this.value)" class="w-full h-9 rounded-xl border border-slate-200 cursor-pointer p-0.5">
                    </div>
                </div>

                {{-- Formatting Bar (Bold, Italic, Underline, Align) --}}
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <button type="button" onclick="toggleActiveTextStyle('bold')" class="flex-1 py-1.5 rounded-lg text-xs font-black hover:bg-white transition text-slate-700">B</button>
                    <button type="button" onclick="toggleActiveTextStyle('italic')" class="flex-1 py-1.5 rounded-lg text-xs italic font-black hover:bg-white transition text-slate-700">I</button>
                    <button type="button" onclick="toggleActiveTextStyle('underline')" class="flex-1 py-1.5 rounded-lg text-xs underline font-black hover:bg-white transition text-slate-700">U</button>
                    <div class="w-[1px] h-4 bg-slate-300"></div>
                    <button type="button" onclick="changeActiveTextAlign('left')" class="flex-1 py-1.5 rounded-lg text-xs hover:bg-white transition text-slate-700">⬅</button>
                    <button type="button" onclick="changeActiveTextAlign('center')" class="flex-1 py-1.5 rounded-lg text-xs hover:bg-white transition text-slate-700">⬇</button>
                    <button type="button" onclick="changeActiveTextAlign('right')" class="flex-1 py-1.5 rounded-lg text-xs hover:bg-white transition text-slate-700">➡</button>
                </div>

                {{-- Extended Font Size Slider + Direct Numeric Input (up to 300px+) --}}
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="text-[10px] font-bold text-slate-500">ফন্ট সাইজ (Custom Size)</label>
                        <div class="flex items-center gap-1">
                            <input type="number" id="text-font-size-input" min="8" max="500" value="48" oninput="changeActiveFontSize(this.value)" class="w-14 border border-slate-200 rounded-md px-1.5 py-0.5 text-[11px] font-bold text-center text-slate-800 outline-none">
                            <span class="text-[10px] text-slate-400 font-bold">px</span>
                        </div>
                    </div>
                    <input type="range" id="text-font-size-slider" min="8" max="300" value="48" oninput="changeActiveFontSize(this.value)" class="w-full accent-indigo-600">
                </div>

                {{-- Text Stroke / Border Outline --}}
                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-700">🔤 টেক্সট স্ট্রোক / বর্ডার</label>
                        <button type="button" onclick="window.customStudio.removeTextStroke()" class="text-[10px] text-red-500 font-bold hover:underline">স্ট্রোক অফ</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2 items-center">
                        <div class="col-span-1">
                            <label class="text-[9px] font-bold text-slate-400 block mb-0.5">কালার</label>
                            <input type="color" id="text-stroke-color-picker" value="#000000" oninput="applyTextStroke()" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                        </div>
                        <div class="col-span-2">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>থিকনেস</span>
                                <span id="text-stroke-width-val">0px</span>
                            </div>
                            <input type="range" id="text-stroke-width-slider" min="0" max="30" value="0" oninput="applyTextStroke()" class="w-full accent-indigo-600">
                        </div>
                    </div>
                </div>

                {{-- Advanced Text Shadow Engine (Photoshop/Canva Grade) --}}
                <div class="border-t border-slate-100 pt-3 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-700">🌑 টেক্সট শ্যাডো (Advanced Shadow)</label>
                        <button type="button" onclick="window.customStudio.removeTextShadow()" class="text-[10px] text-red-500 font-bold hover:underline">শ্যাডো রিমুভ</button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 block mb-0.5">শ্যাডো কালার</label>
                            <input type="color" id="text-shadow-color-picker" value="#000000" oninput="applyCustomTextShadow()" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Blur (ঝাপসা)</span>
                                <span id="text-shadow-blur-val">0px</span>
                            </div>
                            <input type="range" id="text-shadow-blur-slider" min="0" max="60" value="0" oninput="applyCustomTextShadow()" class="w-full accent-indigo-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Offset X (ডানে/বামে)</span>
                                <span id="text-shadow-x-val">0px</span>
                            </div>
                            <input type="range" id="text-shadow-x-slider" min="-50" max="50" value="0" oninput="applyCustomTextShadow()" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Offset Y (উপরে/নিচে)</span>
                                <span id="text-shadow-y-val">0px</span>
                            </div>
                            <input type="range" id="text-shadow-y-slider" min="-50" max="50" value="0" oninput="applyCustomTextShadow()" class="w-full accent-indigo-600">
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- 4. ELEMENTS, SHAPES & BADGES TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-elements" class="studio-panel space-y-5 hidden">
            
            {{-- News Badges --}}
            <div>
                <label class="text-xs font-black text-slate-700 block mb-2">🔴 সংবাদ ব্যাজসমূহ (1-Click Badges)</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addBadge('ব্রেকিং নিউজ', '#dc2626', '#ffffff')" class="py-2 px-2.5 bg-red-600 text-white rounded-xl text-xs font-black hover:bg-red-700 transition shadow-sm text-center">
                        🔴 ব্রেকিং নিউজ
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('বিশেষ সংবাদ', '#2563eb', '#ffffff')" class="py-2 px-2.5 bg-blue-600 text-white rounded-xl text-xs font-black hover:bg-blue-700 transition shadow-sm text-center">
                        ⭐ বিশেষ সংবাদ
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('এক্সক্লুসিভ', '#7c3aed', '#ffffff')" class="py-2 px-2.5 bg-purple-600 text-white rounded-xl text-xs font-black hover:bg-purple-700 transition shadow-sm text-center">
                        🔥 এক্সক্লুসিভ
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('সরাসরি', '#059669', '#ffffff')" class="py-2 px-2.5 bg-emerald-600 text-white rounded-xl text-xs font-black hover:bg-emerald-700 transition shadow-sm text-center">
                        🎥 সরাসরি
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('শোক সংবাদ', '#000000', '#ffffff')" class="py-2 px-2.5 bg-black text-white rounded-xl text-xs font-black hover:bg-slate-900 transition shadow-sm text-center">
                        🖤 শোক সংবাদ
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('খেলাধুলা', '#d97706', '#ffffff')" class="py-2 px-2.5 bg-amber-600 text-white rounded-xl text-xs font-black hover:bg-amber-700 transition shadow-sm text-center">
                        ⚽ খেলাধুলা
                    </button>
                </div>
            </div>

            {{-- Basic Shapes --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-2">⏹️ বেসিক শেপস (Shapes)</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addShape('rect')" class="py-2 px-3 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                        <span>⏹️ চারকোনা বক্স</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addShape('circle')" class="py-2 px-3 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                        <span>⚪ গোল বৃত্ত</span>
                    </button>
                </div>
            </div>

            {{-- Shape Pro Controls (Stroke, Shadow, Corner Radius, Transparent Fill) --}}
            <div class="border-t border-slate-100 pt-3 space-y-3">
                <label class="text-xs font-black text-slate-700 block">🎨 সিলেক্টেড শেপ কাস্টমাইজেশন</label>

                {{-- Fill & Transparent Toggle --}}
                <div class="grid grid-cols-2 gap-2 items-center">
                    <div>
                        <label class="text-[9px] font-bold text-slate-400 block mb-0.5">ফিল কালার</label>
                        <input type="color" id="shape-fill-color-picker" value="#3b82f6" oninput="changeShapeFill(this.value)" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div class="pt-3">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="shape-no-fill-check" onchange="window.customStudio.toggleShapeNoFill(this.checked)" class="rounded accent-indigo-600">
                            <span>No Fill (হলো বক্স)</span>
                        </label>
                    </div>
                </div>

                {{-- Corner Radius --}}
                <div>
                    <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                        <span>Corner Radius (রাউন্ডেড কর্নার)</span>
                        <span id="shape-corner-radius-val">12px</span>
                    </div>
                    <input type="range" id="shape-corner-radius-slider" min="0" max="150" value="12" oninput="changeShapeRadius(this.value)" class="w-full accent-indigo-600">
                </div>

                {{-- Shape Stroke --}}
                <div class="grid grid-cols-3 gap-2 items-center">
                    <div class="col-span-1">
                        <label class="text-[9px] font-bold text-slate-400 block mb-0.5">বর্ডার কালার</label>
                        <input type="color" id="shape-stroke-color-picker" value="#000000" oninput="applyShapeStroke()" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div class="col-span-2">
                        <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                            <span>বর্ডার সাইজ</span>
                            <span id="shape-stroke-width-val">0px</span>
                        </div>
                        <input type="range" id="shape-stroke-width-slider" min="0" max="40" value="0" oninput="applyShapeStroke()" class="w-full accent-indigo-600">
                    </div>
                </div>

                {{-- Shape Shadow --}}
                <div class="border-t border-slate-100 pt-2 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-600">শেপ শ্যাডো (Shadow)</span>
                        <button type="button" onclick="window.customStudio.removeShapeShadow()" class="text-[9px] text-red-500 font-bold hover:underline">রিমুভ</button>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <input type="color" id="shape-shadow-color-picker" value="#000000" oninput="applyCustomShapeShadow()" class="w-full h-7 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                        </div>
                        <div>
                            <input type="range" id="shape-shadow-blur-slider" min="0" max="50" value="0" oninput="applyCustomShapeShadow()" class="w-full accent-indigo-600">
                        </div>
                    </div>
                </div>

            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- 5. DRAG & DROP LAYERS TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-layers" class="studio-panel space-y-4 hidden">
            <div class="flex items-center justify-between">
                <label class="text-xs font-black text-slate-700">🗂️ লেয়ার তালিকা (Drag to Reorder)</label>
                <button type="button" onclick="window.customStudio.renderLayersList()" class="text-[10px] font-bold text-indigo-600 hover:underline">
                    রিফ্রেশ
                </button>
            </div>

            {{-- Drag and Drop Sortable Container --}}
            <div id="layers-sortable-list" class="space-y-1.5 max-h-[380px] overflow-y-auto custom-scrollbar p-1">
                {{-- Dynamic via JavaScript --}}
            </div>

            <div class="border-t border-slate-100 pt-3">
                <button type="button" onclick="window.customStudio.deleteActive()" class="w-full py-2.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>সিলেক্টেড লেয়ার ডিলিট করুন</span>
                </button>
            </div>
        </div>

    </div>

</div>
