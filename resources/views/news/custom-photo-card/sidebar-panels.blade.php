{{-- SIDEBAR CONTAINER --}}
<div class="w-full md:w-[390px] bg-white border-r border-slate-200 flex flex-col h-full z-20 shadow-lg select-none shrink-0 font-bangla">
    
    {{-- NAVIGATION TABS --}}
    <div class="flex border-b border-slate-200 bg-slate-50/80 p-1.5 gap-1 shrink-0 overflow-x-auto no-scrollbar">
        <button type="button" onclick="switchStudioTab('frames')" id="tab-btn-frames" class="studio-tab-btn active flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-indigo-600 bg-white shadow-sm">
            <span class="text-base">🖼️</span>
            <span>Frames</span>
        </button>
        <button type="button" onclick="switchStudioTab('quote')" id="tab-btn-quote" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🎙️</span>
            <span>Quote Card</span>
        </button>
        <button type="button" onclick="switchStudioTab('templates')" id="tab-btn-templates" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">💾</span>
            <span>Templates</span>
        </button>
        <button type="button" onclick="switchStudioTab('image')" id="tab-btn-image" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🪄</span>
            <span>Image / BG</span>
        </button>
        <button type="button" onclick="switchStudioTab('text')" id="tab-btn-text" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🅰️</span>
            <span>Typography</span>
        </button>
        <button type="button" onclick="switchStudioTab('elements')" id="tab-btn-elements" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🏷️</span>
            <span>Shapes/Badges</span>
        </button>
        <button type="button" onclick="switchStudioTab('layers')" id="tab-btn-layers" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🗂️</span>
            <span>Layers</span>
        </button>
    </div>

    {{-- TAB CONTENT AREA --}}
    <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-6">
        
        {{-- ========================================================= --}}
        {{-- 🎙️ SMART STATEMENT / QUOTE CARD TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-quote" class="studio-panel space-y-4 hidden">
            <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-violet-600 p-4 rounded-2xl text-white shadow-lg space-y-1.5">
                <h3 class="text-xs font-black flex items-center gap-1.5">
                    <i class="fa-solid fa-quote-left"></i> 1-Click Quote & Statement Card Generator
                </h3>
                <p class="text-[10px] text-white/80 leading-relaxed">
                    Enter speaker name, quote, and photo — system generates an auto background-removed card layout.
                </p>
            </div>

            {{-- Quote Textarea --}}
            <div>
                <label class="text-xs font-black text-slate-700 block mb-1">💬 Quote / Statement Body *</label>
                <textarea id="quote-card-text" rows="3" oninput="onQuoteFieldChange('text', this.value)" placeholder="e.g. Through constitutional reform, we will establish true democratic accountability." class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500 bg-slate-50 focus:bg-white"></textarea>
            </div>

            {{-- Speaker Name & Designation --}}
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Speaker Name *</label>
                    <input type="text" id="quote-card-name" oninput="onQuoteFieldChange('name', this.value)" placeholder="e.g. John Doe" class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500 bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Designation / Title</label>
                    <input type="text" id="quote-card-desig" oninput="onQuoteFieldChange('designation', this.value)" placeholder="e.g. Chief Editor / Political Analyst" class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500 bg-slate-50 focus:bg-white">
                </div>
            </div>

            {{-- Speaker Photo Upload --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 block mb-1">Speaker Photo</label>
                <label class="cursor-pointer border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-400 p-3 rounded-2xl text-center font-bold text-xs text-indigo-700 transition flex items-center justify-center gap-2 group">
                    <input type="file" id="quote-card-photo" accept="image/*" class="hidden" onchange="previewQuotePhoto(this)">
                    <i class="fa-solid fa-camera text-base text-indigo-600 group-hover:scale-110 transition-transform"></i>
                    <span id="quote-photo-label">Choose Photo</span>
                </label>
            </div>

            {{-- Quote Font Family Selector --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 block mb-1">🔤 Quote Typography (Font Family)</label>
                <select id="quote-card-font" onchange="onQuoteFieldChange('font', this.value)" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none bg-slate-50 focus:bg-white focus:border-indigo-500">
                    <option value="'SolaimanLipi'" selected>SolaimanLipi </option>
                    <option value="'Hind Siliguri', sans-serif">Hind Siliguri </option>
                    <option value="'Noto Sans Bengali', sans-serif">Noto Sans Bengali</option>
                    <option value="'Baloo Da 2', cursive">Baloo Da 2</option>
                    <option value="'Anek Bangla', sans-serif">Anek Bangla</option>
                    <option value="'Li Alinur Banglaborno'">Li Alinur Banglaborno</option>
                    <option value="'Tiro Bangla', serif">Tiro Bangla</option>
                    <option value="'Galada', cursive">Galada</option>
                    @if(isset($dynamicMediaFonts) && count($dynamicMediaFonts) > 0)
                        <optgroup label="📂 Media Library Fonts">
                            @foreach($dynamicMediaFonts as $dmf)
                                <option value="'{{ $dmf['family'] }}'">{{ $dmf['family'] }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            {{-- Typography Controls: Size, Line-Height, Weight --}}
            <div class="bg-slate-50 border border-slate-200/80 p-2.5 rounded-2xl space-y-2.5">
                {{-- Font Size Slider & Number Box --}}
                <div>
                    <div class="flex items-center justify-between text-[10px] font-bold text-slate-600 mb-1">
                        <span>📏 Font Size</span>
                        <div class="flex items-center gap-1">
                            <input type="number" id="quote-card-font-size-num" value="44" min="16" max="100" 
                                oninput="document.getElementById('quote-card-font-size').value = this.value; onQuoteFieldChange('fontSize', this.value)" 
                                class="w-12 text-center border border-slate-300 rounded-lg text-[11px] font-bold py-0.5 bg-white text-indigo-700 outline-none">
                            <span class="text-[10px] text-slate-400 font-bold">px</span>
                        </div>
                    </div>
                    <input type="range" id="quote-card-font-size" min="16" max="90" value="44" 
                        oninput="document.getElementById('quote-card-font-size-num').value = this.value; onQuoteFieldChange('fontSize', this.value)" 
                        class="w-full accent-indigo-600">
                </div>

                {{-- Line Spacing & Weight --}}
                <div class="grid grid-cols-2 gap-2 pt-0.5">
                    <div>
                        <div class="flex justify-between text-[10px] font-bold text-slate-600 mb-1">
                            <span>↕️ Line Height</span>
                            <span id="quote-line-height-val" class="text-indigo-600 font-black text-[10px]">1.2</span>
                        </div>
                        <input type="range" id="quote-card-line-height" min="1.0" max="1.8" step="0.05" value="1.2" 
                            oninput="document.getElementById('quote-line-height-val').innerText = this.value; onQuoteFieldChange('lineHeight', this.value)" 
                            class="w-full accent-indigo-600">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-slate-600 block mb-1">Font Weight</label>
                        <select id="quote-card-font-weight" onchange="onQuoteFieldChange('fontWeight', this.value)" class="w-full border border-slate-200 rounded-xl p-1.5 text-[11px] font-bold text-slate-800 outline-none bg-white">
                            <option value="bold" selected>Bold</option>
                            <option value="900">Black (900)</option>
                            <option value="normal">Normal (400)</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Text Alignment Selector --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 block mb-1">📐 Text Alignment</label>
                <select id="quote-card-align" onchange="onQuoteFieldChange('align', this.value)" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none bg-slate-50 focus:bg-white focus:border-indigo-500">
                    <option value="star-news" selected>✨ Smart Contour Wrap (Flush Right & Contour)</option>
                    <option value="left">📄 Left Align</option>
                    <option value="center">📑 Center Align</option>
                    <option value="right">👉 Right Align</option>
                </select>
            </div>

            {{-- Theme Selector & Layout Options --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 block mb-1">🎨 Card Theme & Color Styles (35 Presets)</label>
                <select id="quote-card-theme" onchange="onQuoteFieldChange('theme', this.value)" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none bg-slate-50 focus:bg-white focus:border-indigo-500">
                    <optgroup label="1. Light & Minimal Themes">
                        <option value="clean-white">⚪ Pure White</option>
                        <option value="soft-sky" selected>🔵 Soft Sky Blue</option>
                        <option value="warm-cream">📜 Warm Cream Newsprint</option>
                        <option value="mint-fresh">🍃 Mint Fresh</option>
                        <option value="lavender-soft">💜 Soft Lavender</option>
                        <option value="blush-rose">🌸 Blush Rose</option>
                        <option value="silver-pearl">🔘 Silver Pearl</option>
                    </optgroup>
                    <optgroup label="⚫ 2. Dark & Midnight Series">
                        <option value="dark-slate">⚫ Midnight Slate</option>
                        <option value="dark-navy">🌌 Royal Deep Navy</option>
                        <option value="dark-emerald">🌲 Deep Emerald</option>
                        <option value="dark-burgundy">🍷 Deep Burgundy</option>
                        <option value="dark-charcoal">🖤 Charcoal Gold</option>
                        <option value="dark-obsidian">🌑 Obsidian Black</option>
                        <option value="dark-plum">🍇 Deep Plum</option>
                        <option value="dark-teal">🦚 Deep Forest Teal</option>
                    </optgroup>
                    <optgroup label="🔥 3. Bold & Breaking Colors">
                        <option value="breaking-red">🔴 Crimson Red</option>
                        <option value="scarlet-fire">🏮 Scarlet Fire</option>
                        <option value="royal-purple">🔮 Royal Violet</option>
                        <option value="sunset-orange">🌅 Sunset Orange</option>
                        <option value="amber-flame">⚡ Amber Flame</option>
                        <option value="ocean-cyan">🌊 Deep Ocean Cyan</option>
                        <option value="electric-blue">⚡ Electric Blue</option>
                    </optgroup>
                    <optgroup label="🌈 4. Modern Gradients & Metallic">
                        <option value="aurora-borealis">🌌 Aurora Teal-Purple</option>
                        <option value="cosmic-glow">🪐 Cosmic Glow</option>
                        <option value="cyber-neon">🧪 Cyber Neon Lime</option>
                        <option value="rose-gold">👑 Rose Gold Metallic</option>
                        <option value="bronze-copper">🪙 Bronze Copper</option>
                        <option value="crimson-noir">🩸 Crimson Noir</option>
                        <option value="sapphire-glow">💎 Sapphire Glow</option>
                    </optgroup>
                    <optgroup label="🍃 5. Earthy & Nature Tones">
                        <option value="olive-moss">🫒 Olive Moss</option>
                        <option value="terracotta">🏺 Terracotta Clay</option>
                        <option value="espresso-coffee">☕ Espresso Coffee</option>
                        <option value="desert-sand">🏜️ Desert Sand</option>
                        <option value="sage-green">🌿 Sage Green</option>
                        <option value="caramel-mocha">🍮 Caramel Mocha</option>
                    </optgroup>
                </select>
            </div>

            {{-- Position & AI Options --}}
            <div class="grid grid-cols-2 gap-2 pt-1">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">Photo Position</label>
                    <select id="quote-card-pos" onchange="onQuoteFieldChange('position', this.value)" class="w-full border border-slate-200 rounded-xl p-2 text-xs font-bold text-slate-800 outline-none bg-slate-50">
                        <option value="left" selected>Photo Left + Quote Right</option>
                        <option value="right">Photo Right + Quote Left</option>
                    </select>
                </div>
                <div class="space-y-1.5 pt-2">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="quote-card-bg-check" checked class="rounded accent-indigo-600">
                        <span>AI BG Remove</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="quote-card-flip-check" class="rounded accent-indigo-600">
                        <span>↔️ Flip Horizontal</span>
                    </label>
                </div>
            </div>

            {{-- Text Wrap around Speaker / Subject --}}
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="quote-card-wrap-check" checked onchange="window.customStudio.updateQuoteLiveField('wrap', this.checked)" class="rounded accent-indigo-600">
                        <span>🔀 Contour Text Wrap</span>
                    </label>
                </div>
                <div>
                    <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                        <span>Wrap Margin / Padding</span>
                        <span id="quote-wrap-margin-val">25px</span>
                    </div>
                    <input type="range" id="quote-card-wrap-margin" min="5" max="60" value="25" oninput="document.getElementById('quote-wrap-margin-val').innerText = this.value + 'px'; window.customStudio.updateQuoteLiveField('wrapMargin', this.value)" class="w-full accent-indigo-600">
                </div>
            </div>

            {{-- Unique Textures & Dot Shadow Overlays --}}
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-[10px] font-black text-slate-700 uppercase tracking-wider flex items-center gap-1">
                        <span>✨ Background Textures & Dot Patterns</span>
                    </label>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('none')" class="text-[9px] text-red-500 font-bold hover:underline">Remove</button>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('dot-grid')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>⠶ Dot Grid</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('halftone-dots')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>🏁 Halftone</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('spotlight-glow')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>🔆 Spotlight</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('vignette-shadow')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>🌫️ Vignette</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('diagonal-mesh')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>📐 Stripes</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addDecorativeDotCluster('top-right')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>➕ Dot Patch</span>
                    </button>
                </div>
            </div>

            {{-- Submit Action Button --}}
            <div class="pt-2">
                <button type="button" onclick="submitQuoteCardForm()" class="w-full py-3 bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-black text-xs rounded-xl shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>✨ Generate Quote Card in 1-Click</span>
                </button>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 1. FRAMES & TEMPLATES TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-frames" class="studio-panel space-y-5">
            
            {{-- Save Customized Design as Template Button --}}
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-3.5 rounded-2xl text-white shadow-md flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-black flex items-center gap-1.5">
                        <i class="fa-solid fa-bookmark"></i> Save Custom Template
                    </h4>
                    <p class="text-[10px] text-white/80 mt-0.5">Save current layout for future reuse</p>
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
                        <span>⭐ Saved Templates</span>
                        <span class="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ count($dbTemplates) }}</span>
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
                                <button type="button" onclick="window.customStudio.deleteCustomTemplate({{ $tpl->id }}, this)" class="text-red-400 hover:text-red-600 p-0.5 text-xs" title="Delete">🗑️</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Custom PNG Frame Upload --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 flex items-center justify-between mb-2">
                    <span>📤 Upload Custom PNG Frame</span>
                    <span class="text-[10px] text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Auto-Resize Size</span>
                </label>
                <label class="cursor-pointer border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-400 p-3.5 rounded-2xl text-center font-black text-xs text-indigo-700 transition-all flex flex-col items-center justify-center gap-1.5 group shadow-sm">
                    <input type="file" class="hidden" accept="image/png,image/webp" onchange="uploadCustomFrameFile(this)">
                    <div class="w-9 h-9 rounded-xl bg-white shadow flex items-center justify-center text-indigo-600 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                    </div>
                    <span>Select Frame (PNG)</span>
                    <span class="text-[10px] text-slate-400 font-normal">Canvas automatically conforms to original frame dimensions</span>
                </label>
            </div>

            {{-- Preset Aspect Ratios --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-2">📐 Canvas Dimensions Preset</label>
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
                        <span>🖼️ Media Frame Library</span>
                        <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-1.5 py-0.5 rounded-md">{{ count($frames) }}</span>
                    </label>
                    <button type="button" onclick="window.customStudio.removeFrame()" class="text-[10px] font-bold text-red-500 hover:text-red-700 hover:underline">
                        Remove Frame
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
                            No frames found. Upload a custom PNG frame above.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- 💾 SAVED CUSTOM TEMPLATES / PRESETS TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-templates" class="studio-panel space-y-4 hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-4 rounded-2xl text-white shadow-lg space-y-2">
                <h3 class="text-xs font-black flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> Saved Templates & Layout Presets
                </h3>
                <p class="text-[10px] text-white/85 leading-relaxed">
                    Save your custom card designs as reusable templates to apply across future stories.
                </p>
                <button type="button" onclick="openSaveTemplateModal()" class="w-full mt-1 py-2 bg-white text-emerald-800 rounded-xl font-black text-xs hover:bg-emerald-50 transition shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>Save Current Design</span>
                </button>
            </div>

            {{-- Saved Templates List Container --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-black text-slate-700">⭐ Saved Templates</label>
                    <button type="button" onclick="window.customStudio.renderCustomTemplatesList()" class="text-[10px] font-bold text-indigo-600 hover:underline">
                        🔄 Refresh
                    </button>
                </div>

                <div id="custom-templates-list" class="space-y-3">
                    {{-- Dynamically populated by engine --}}
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- 2. IMAGE & BACKGROUND REMOVER TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-image" class="studio-panel space-y-5 hidden">
            
            {{-- Upload or Replace Image --}}
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer bg-slate-800 text-white p-2.5 rounded-2xl shadow-sm text-xs font-bold hover:bg-black transition-all flex items-center justify-center gap-1.5 text-center">
                    <input type="file" accept="image/*" onchange="window.customStudio.addImageFromFile(this)" class="hidden">
                    <i class="fa-solid fa-plus-circle text-xs"></i>
                    <span>+ Add Photo</span>
                </label>
                <button type="button" onclick="window.customStudio.triggerReplaceActiveImage()" class="bg-indigo-50 border border-indigo-200 text-indigo-700 p-2.5 rounded-2xl shadow-xs text-xs font-bold hover:bg-indigo-100 transition-all flex items-center justify-center gap-1.5 text-center" title="Replace active image with new file">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    <span>🔄 Replace Photo</span>
                </button>
            </div>

            {{-- Prominent White-Label Background Remover Button --}}
            <div class="bg-gradient-to-r from-violet-600 to-indigo-600 p-4 rounded-2xl text-white shadow-lg space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-black tracking-wide flex items-center gap-1.5">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> AI Background Remover
                    </span>
                    <span class="text-[10px] bg-white/20 px-2 py-0.5 rounded-full font-bold">
                        Cost: {{ $creditCost }} Credit
                    </span>
                </div>
                <p class="text-[11px] text-white/80 leading-snug">
                    Select an image on the canvas and click below to remove background instantly with AI.
                </p>
                <button type="button" onclick="window.customStudio.removeBackgroundActive()" 
                    class="w-full py-2.5 bg-white text-indigo-700 hover:bg-indigo-50 font-black text-xs rounded-xl shadow transition-transform transform active:scale-95 flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-scissors"></i>
                    <span>Background Remove</span>
                </button>
                <button type="button" onclick="window.customStudio.trimActiveImageTransparent()" 
                    class="w-full py-2 bg-indigo-700/60 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1.5 border border-white/20 shadow-xs" title="Crop surrounding transparent whitespace">
                    <i class="fa-solid fa-crop-simple"></i>
                    <span>✂️ Auto Crop / Trim Canvas Whitespace</span>
                </button>
            </div>

            {{-- Photo Extracted Colors & Reset --}}
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-black text-slate-700">🎨 Canvas Background & Colors</span>
                    <button type="button" onclick="window.customStudio.resetCanvasBackground()" class="text-[10px] font-bold text-slate-500 hover:text-indigo-600 hover:underline">
                        ↺ Reset White
                    </button>
                </div>
                <div id="photo-color-palette"></div>
            </div>

            {{-- Dark Bottom Gradient Overlay --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-2">🌑 Bottom Dark Gradient Shadow</label>
                <button type="button" onclick="window.customStudio.addShape('darkGradientOverlay')" 
                    class="w-full py-2 bg-slate-900 text-white rounded-xl text-xs font-bold hover:bg-black transition flex items-center justify-center gap-2 shadow-sm">
                    <i class="fa-solid fa-fill-drip"></i>
                    <span>+ Add Bottom Dark Shadow</span>
                </button>
            </div>

            {{-- Main Image Adjustments --}}
            <div class="border-t border-slate-100 pt-3 space-y-3">
                <label class="text-xs font-black text-slate-700 block">🖼️ Selected Image Controls</label>
                
                {{-- Image Zoom Drag Slider & Percentage Input --}}
                <div class="bg-slate-50 border border-slate-200/80 p-3 rounded-2xl space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-black text-slate-700 flex items-center gap-1">
                            <i class="fa-solid fa-magnifying-glass-plus text-indigo-500"></i>
                            <span>Image Zoom Scale</span>
                        </label>
                        <div class="flex items-center gap-1">
                            <input type="number" id="image-zoom-num" value="100" min="10" max="400" step="1"
                                oninput="changeActiveImageZoom(this.value)"
                                class="w-14 text-center border border-slate-300 rounded-lg text-xs font-bold py-0.5 px-1 bg-white text-indigo-700 outline-none focus:border-indigo-500">
                            <span class="text-xs font-bold text-slate-500">%</span>
                        </div>
                    </div>
                    
                    <input type="range" id="image-zoom-slider" min="10" max="300" step="1" value="100" 
                        oninput="changeActiveImageZoom(this.value)" 
                        class="w-full accent-indigo-600 cursor-pointer">

                    <div class="flex items-center justify-between pt-1 gap-1.5">
                        <button type="button" onclick="window.customStudio.zoomActiveImage(-0.05)" class="flex-1 py-1 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 hover:bg-slate-100 transition text-center shadow-xs">
                            ➖ Smaller
                        </button>
                        <button type="button" onclick="window.customStudio.zoomActiveImage(0.05)" class="flex-1 py-1 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 hover:bg-slate-100 transition text-center shadow-xs">
                            ➕ Larger
                        </button>
                        <button type="button" onclick="changeActiveImageZoom(100)" class="flex-1 py-1 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-indigo-600 hover:bg-slate-100 transition text-center shadow-xs">
                            ↺ 100%
                        </button>
                    </div>
                </div>

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
                        <span>Opacity</span>
                        <span id="opacity-val">100%</span>
                    </label>
                    <input type="range" id="image-opacity-slider" min="0.1" max="1" step="0.05" value="1" oninput="changeActiveOpacity(this.value)" class="w-full accent-indigo-600">
                </div>

                {{-- Photo Color Filter Presets --}}
                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-700">🎚️ Photo Color Filters</label>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('reset')" class="text-[10px] text-indigo-600 font-bold hover:underline">Reset</button>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('bw')" class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">⚫ B&W</button>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('vintage')" class="py-1.5 px-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">🎞️ Vintage</button>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('vibrant')" class="py-1.5 px-1 bg-rose-50 hover:bg-rose-100 text-rose-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">✨ Vibrant</button>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('cool')" class="py-1.5 px-1 bg-sky-50 hover:bg-sky-100 text-sky-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">🧊 Cool</button>
                    </div>

                    {{-- Manual Filter Sliders --}}
                    <div class="space-y-2 pt-1">
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Brightness</span>
                            </div>
                            <input type="range" id="filter-brightness-slider" min="-0.5" max="0.5" step="0.02" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('brightness', this.value)" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Contrast</span>
                            </div>
                            <input type="range" id="filter-contrast-slider" min="-0.5" max="0.5" step="0.02" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('contrast', this.value)" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Saturation</span>
                            </div>
                            <input type="range" id="filter-saturation-slider" min="-1" max="1" step="0.05" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('saturation', this.value)" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Blur</span>
                            </div>
                            <input type="range" id="filter-blur-slider" min="0" max="0.8" step="0.05" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('blur', this.value)" class="w-full accent-indigo-600">
                        </div>
                    </div>
                </div>

                {{-- Brand Logo / Watermark 1-Click Stamper --}}
                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <label class="text-xs font-black text-slate-700 block">🏷️ Brand Logo & Watermark</label>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" onclick="window.customStudio.applyBrandLogoStamp('top-left')" class="py-2 px-1.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-2xs">
                            <span>↖️ Top-Left</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applyBrandLogoStamp('top-right')" class="py-2 px-1.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-2xs">
                            <span>↗️ Top-Right</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applyBrandLogoStamp('bottom-right')" class="py-2 px-1.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-2xs">
                            <span>↘️ Watermark</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- 3. TEXT & TYPOGRAPHY TAB (CANVA/PHOTOSHOP GRADE) --}}
        {{-- ========================================================= --}}
        <div id="panel-text" class="studio-panel space-y-5 hidden">
            
            {{-- Add Text Buttons --}}
            <div class="grid grid-cols-2 gap-2">
                <button type="button" onclick="window.customStudio.addText('Enter Primary Headline Here', { fontSize: 48, fontWeight: 'bold' })" 
                    class="py-2.5 px-3 bg-slate-900 text-white rounded-xl font-bold text-xs hover:bg-black transition flex items-center justify-center gap-1.5 shadow-sm">
                    <span>+ Headline</span>
                </button>
                <button type="button" onclick="window.customStudio.addText('Enter Subtitle or Secondary Details Here', { fontSize: 28, fontWeight: 'normal' })" 
                    class="py-2.5 px-3 bg-slate-100 border border-slate-200 text-slate-700 rounded-xl font-bold text-xs hover:bg-slate-200 transition flex items-center justify-center gap-1.5">
                    <span>+ Subtitle</span>
                </button>
            </div>

            {{-- Bengali Font Selector --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-1.5">🅰️ Typography & Font Selection</label>
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
                        <option value="'SolaimanLipi'" selected>SolaimanLipi</option>
                        <option value="'Hind Siliguri', sans-serif">Hind Siliguri</option>
                        <option value="'Noto Sans Bengali', sans-serif">Noto Sans Bengali</option>
                        <option value="'Noto Serif Bengali', serif">Noto Serif Bengali</option>
                        <option value="'Anek Bangla', sans-serif">Anek Bangla</option>
                        <option value="'Kalpurush', sans-serif">Kalpurush</option>
                    </optgroup>
                    @if(isset($dynamicMediaFonts) && count($dynamicMediaFonts) > 0)
                        <optgroup label="📂 Media Library Fonts">
                            @foreach($dynamicMediaFonts as $dmf)
                                <option value="'{{ $dmf['family'] }}'">{{ $dmf['family'] }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            {{-- Text Color & Background (with Reset Buttons) --}}
            <div class="border-t border-slate-100 pt-3 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[10px] font-bold text-slate-500">Text Color</label>
                            <button type="button" onclick="resetTextColor()" class="text-[9px] text-indigo-600 font-bold hover:underline">Reset</button>
                        </div>
                        <input type="color" id="text-color-picker" value="#1e293b" oninput="changeActiveTextColor(this.value)" class="w-full h-9 rounded-xl border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="text-[10px] font-bold text-slate-500">Background Highlight</label>
                            <button type="button" onclick="window.customStudio.removeTextBackground()" class="text-[9px] text-red-500 font-bold hover:underline">❌ No BG</button>
                        </div>
                        <input type="color" id="text-bg-color-picker" value="#ffffff" oninput="changeActiveTextBgColor(this.value)" class="w-full h-9 rounded-xl border border-slate-200 cursor-pointer p-0.5">
                    </div>
                </div>

                {{-- Quick Background Pill Presets --}}
                <div class="bg-slate-50 p-2 rounded-xl border border-slate-200/80 space-y-1.5">
                    <label class="text-[9px] font-black text-slate-500 block uppercase tracking-wider">🏷️ 1-Click Background Pill (TV News Style)</label>
                    <div class="grid grid-cols-4 gap-1">
                        <button type="button" onclick="window.customStudio.applyTextBackgroundPill('#dc2626')" class="py-1 px-1.5 bg-red-600 text-white rounded-lg text-[10px] font-bold hover:bg-red-700 transition text-center shadow-xs">🔴 Red</button>
                        <button type="button" onclick="window.customStudio.applyTextBackgroundPill('#0f172a')" class="py-1 px-1.5 bg-slate-900 text-white rounded-lg text-[10px] font-bold hover:bg-black transition text-center shadow-xs">⚫ Black</button>
                        <button type="button" onclick="window.customStudio.applyTextBackgroundPill('#ffffff')" class="py-1 px-1.5 bg-white text-slate-900 border border-slate-300 rounded-lg text-[10px] font-bold hover:bg-slate-100 transition text-center shadow-xs">⚪ White</button>
                        <button type="button" onclick="window.customStudio.applyTextBackgroundPill('none')" class="py-1 px-1.5 bg-slate-200 text-slate-700 rounded-lg text-[10px] font-bold hover:bg-slate-300 transition text-center shadow-xs">❌ No BG</button>
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
                        <label class="text-[10px] font-bold text-slate-500">Font Size</label>
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
                        <label class="text-xs font-black text-slate-700">🔤 Text Stroke / Outline</label>
                        <button type="button" onclick="window.customStudio.removeTextStroke()" class="text-[10px] text-red-500 font-bold hover:underline">Disable Stroke</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2 items-center">
                        <div class="col-span-1">
                            <label class="text-[9px] font-bold text-slate-400 block mb-0.5">Color</label>
                            <input type="color" id="text-stroke-color-picker" value="#000000" oninput="applyTextStroke()" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                        </div>
                        <div class="col-span-2">
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Thickness</span>
                                <span id="text-stroke-width-val">0px</span>
                            </div>
                            <input type="range" id="text-stroke-width-slider" min="0" max="30" value="0" oninput="applyTextStroke()" class="w-full accent-indigo-600">
                        </div>
                    </div>
                </div>

                {{-- Advanced Text Shadow Engine (Photoshop/Canva Grade) --}}
                <div class="border-t border-slate-100 pt-3 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-700">🌑 Text Drop Shadow</label>
                        <button type="button" onclick="window.customStudio.removeTextShadow()" class="text-[10px] text-red-500 font-bold hover:underline">❌ Remove Shadow</button>
                    </div>

                    {{-- 1-Click Smart Contrast Shadow Presets --}}
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" onclick="window.customStudio.applySmartContrastShadow('dark')" class="py-1 px-1.5 bg-slate-800 text-white rounded-lg text-[10px] font-bold hover:bg-black transition text-center shadow-xs flex items-center justify-center gap-1">
                            <span>🌑 Dark Shadow</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applySmartContrastShadow('glow')" class="py-1 px-1.5 bg-amber-500 text-slate-900 rounded-lg text-[10px] font-bold hover:bg-amber-600 transition text-center shadow-xs flex items-center justify-center gap-1">
                            <span>✨ Soft Glow</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applySmartContrastShadow('none')" class="py-1 px-1.5 bg-slate-100 text-red-600 border border-slate-200 rounded-lg text-[10px] font-bold hover:bg-slate-200 transition text-center shadow-xs flex items-center justify-center gap-1">
                            <span>❌ No Shadow</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="text-[9px] font-bold text-slate-400 block mb-0.5">Shadow Color</label>
                            <input type="color" id="text-shadow-color-picker" value="#000000" oninput="applyCustomTextShadow()" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Blur</span>
                                <span id="text-shadow-blur-val">0px</span>
                            </div>
                            <input type="range" id="text-shadow-blur-slider" min="0" max="60" value="0" oninput="applyCustomTextShadow()" class="w-full accent-indigo-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Offset X (Horizontal)</span>
                                <span id="text-shadow-x-val">0px</span>
                            </div>
                            <input type="range" id="text-shadow-x-slider" min="-50" max="50" value="0" oninput="applyCustomTextShadow()" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>Offset Y (Vertical)</span>
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
            
            {{-- News Ribbons & Special Banners --}}
            <div>
                <label class="text-xs font-black text-slate-700 block mb-2">🎀 Editorial Ribbons & Banners</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addNewsRibbon('breaking-ribbon')" class="py-2.5 px-2 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl text-xs font-black hover:from-red-700 hover:to-rose-700 transition shadow-sm text-center">
                        🔴 Breaking Ribbon
                    </button>
                    <button type="button" onclick="window.customStudio.addNewsRibbon('exclusive-gold')" class="py-2.5 px-2 bg-gradient-to-r from-amber-600 to-yellow-600 text-white rounded-xl text-xs font-black hover:from-amber-700 hover:to-yellow-700 transition shadow-sm text-center">
                        ⚡ Special Banner
                    </button>
                </div>
            </div>

            {{-- News Badges & Verified Stickers --}}
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-black text-slate-700">🔴 News Badges & Stickers</label>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addVerifiedBadge()" class="py-2 px-2.5 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <span>✅ Verified Badge</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addLocationBadge('DHAKA')" class="py-2 px-2.5 bg-slate-100 border border-slate-200 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <span>📍 Location Tag</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('BREAKING NEWS', '#dc2626', '#ffffff')" class="py-2 px-2.5 bg-red-600 text-white rounded-xl text-xs font-black hover:bg-red-700 transition shadow-sm text-center">
                        🔴 BREAKING NEWS
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('SPECIAL REPORT', '#2563eb', '#ffffff')" class="py-2 px-2.5 bg-blue-600 text-white rounded-xl text-xs font-black hover:bg-blue-700 transition shadow-sm text-center">
                        ⭐ SPECIAL REPORT
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('EXCLUSIVE', '#7c3aed', '#ffffff')" class="py-2 px-2.5 bg-purple-600 text-white rounded-xl text-xs font-black hover:bg-purple-700 transition shadow-sm text-center">
                        🔥 EXCLUSIVE
                    </button>
                    <button type="button" onclick="window.customStudio.addBadge('LIVE', '#059669', '#ffffff')" class="py-2 px-2.5 bg-emerald-600 text-white rounded-xl text-xs font-black hover:bg-emerald-700 transition shadow-sm text-center">
                        🎥 LIVE
                    </button>
                </div>
            </div>

            {{-- Basic Shapes --}}
            <div class="border-t border-slate-100 pt-3">
                <label class="text-xs font-black text-slate-700 block mb-2">⏹️ Basic Shapes</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addShape('rect')" class="py-2 px-3 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                        <span>⏹️ Rectangle</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addShape('circle')" class="py-2 px-3 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 hover:bg-slate-50 transition flex items-center justify-center gap-1.5">
                        <span>⚪ Circle</span>
                    </button>
                </div>
            </div>

            {{-- Shape Pro Controls (Stroke, Shadow, Corner Radius, Transparent Fill) --}}
            <div class="border-t border-slate-100 pt-3 space-y-3">
                <label class="text-xs font-black text-slate-700 block">🎨 Selected Shape Customization</label>

                {{-- Fill & Transparent Toggle --}}
                <div class="grid grid-cols-2 gap-2 items-center">
                    <div>
                        <label class="text-[9px] font-bold text-slate-400 block mb-0.5">Fill Color</label>
                        <input type="color" id="shape-fill-color-picker" value="#3b82f6" oninput="changeShapeFill(this.value)" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div class="pt-3">
                        <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="shape-no-fill-check" onchange="window.customStudio.toggleShapeNoFill(this.checked)" class="rounded accent-indigo-600">
                            <span>No Fill (Hollow Outline)</span>
                        </label>
                    </div>
                </div>

                {{-- Corner Radius --}}
                <div>
                    <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                        <span>Corner Radius</span>
                        <span id="shape-corner-radius-val">12px</span>
                    </div>
                    <input type="range" id="shape-corner-radius-slider" min="0" max="150" value="12" oninput="changeShapeRadius(this.value)" class="w-full accent-indigo-600">
                </div>

                {{-- Shape Stroke --}}
                <div class="grid grid-cols-3 gap-2 items-center">
                    <div class="col-span-1">
                        <label class="text-[9px] font-bold text-slate-400 block mb-0.5">Border Color</label>
                        <input type="color" id="shape-stroke-color-picker" value="#000000" oninput="applyShapeStroke()" class="w-full h-8 rounded-lg border border-slate-200 cursor-pointer p-0.5">
                    </div>
                    <div class="col-span-2">
                        <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                            <span>Border Width</span>
                            <span id="shape-stroke-width-val">0px</span>
                        </div>
                        <input type="range" id="shape-stroke-width-slider" min="0" max="40" value="0" oninput="applyShapeStroke()" class="w-full accent-indigo-600">
                    </div>
                </div>

                {{-- Shape Shadow --}}
                <div class="border-t border-slate-100 pt-2 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-black text-slate-600">Shape Shadow</span>
                        <button type="button" onclick="window.customStudio.removeShapeShadow()" class="text-[9px] text-red-500 font-bold hover:underline">Remove</button>
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

            {{-- Decorative Accents & Watermarks --}}
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <label class="text-xs font-black text-slate-700 block">✨ Decorative Elements & Accents</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addDecorativeDotCluster('top-right')" class="py-2 px-2.5 bg-slate-50 border border-slate-200 hover:border-indigo-500 rounded-xl text-xs font-bold text-slate-700 transition flex items-center justify-center gap-1.5 shadow-xs">
                        <span>⠶ Dot Matrix Patch</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addHugeWatermarkQuoteMark()" class="py-2 px-2.5 bg-slate-50 border border-slate-200 hover:border-indigo-500 rounded-xl text-xs font-bold text-slate-700 transition flex items-center justify-center gap-1.5 shadow-xs">
                        <span>❝ Watermark Quote Accent</span>
                    </button>
                </div>
            </div>

        </div>

        {{-- ========================================================= --}}
        {{-- 5. DRAG & DROP LAYERS TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-layers" class="studio-panel space-y-4 hidden">
            <div class="flex items-center justify-between">
                <label class="text-xs font-black text-slate-700">🗂️ Layer Management (Drag to Reorder)</label>
                <button type="button" onclick="window.customStudio.renderLayersList()" class="text-[10px] font-bold text-indigo-600 hover:underline">
                    Refresh
                </button>
            </div>

            {{-- Drag and Drop Sortable Container --}}
            <div id="layers-sortable-list" class="space-y-1.5 max-h-[380px] overflow-y-auto custom-scrollbar p-1">
                {{-- Dynamic via JavaScript --}}
            </div>

            <div class="border-t border-slate-100 pt-3">
                <button type="button" onclick="window.customStudio.deleteActive()" class="w-full py-2.5 bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Delete Selected Layer</span>
                </button>
            </div>
        </div>

    </div>

</div>
