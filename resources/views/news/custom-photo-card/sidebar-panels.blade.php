{{-- SIDEBAR CONTAINER --}}
<div class="w-full md:w-[390px] bg-white border-r border-slate-200 flex flex-col h-full z-20 shadow-lg select-none shrink-0 font-bangla">
    
    {{-- NAVIGATION TABS --}}
    <div class="flex border-b border-slate-200 bg-slate-50/80 p-1.5 gap-1 shrink-0 overflow-x-auto no-scrollbar">
        <button type="button" onclick="switchStudioTab('frames')" id="tab-btn-frames" class="studio-tab-btn active flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-indigo-600 bg-white shadow-sm">
            <span class="text-base">🖼️</span>
            <span>ফ্রেম</span>
        </button>
        <button type="button" onclick="switchStudioTab('quote')" id="tab-btn-quote" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🎙️</span>
            <span>উক্তি কার্ড</span>
        </button>
        <button type="button" onclick="switchStudioTab('templates')" id="tab-btn-templates" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">💾</span>
            <span>টেমপ্লেট</span>
        </button>
        <button type="button" onclick="switchStudioTab('image')" id="tab-btn-image" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🪄</span>
            <span>ইমেজ / BG</span>
        </button>
        <button type="button" onclick="switchStudioTab('text')" id="tab-btn-text" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🅰️</span>
            <span>টেক্সট</span>
        </button>
        <button type="button" onclick="switchStudioTab('elements')" id="tab-btn-elements" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🏷️</span>
            <span>শেপ/ব্যাজ</span>
        </button>
        <button type="button" onclick="switchStudioTab('layers')" id="tab-btn-layers" class="studio-tab-btn flex-1 py-2 px-1.5 rounded-xl text-[11px] font-black transition-all flex flex-col items-center gap-1 text-slate-600 hover:text-indigo-600 hover:bg-white/60">
            <span class="text-base">🗂️</span>
            <span>লেয়ার</span>
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
                    <i class="fa-solid fa-quote-left"></i> ১-ক্লিক উক্তি ও বিবৃতি কার্ড জেনারেটর
                </h3>
                <p class="text-[10px] text-white/80 leading-relaxed">
                    বক্তার নাম, উক্তি ও ছবি দিন—সিস্টেম স্বয়ংক্রিয়ভাবে ব্যাকগ্রাউন্ড রিমুভ করে পারফেক্ট লেআউটে কার্ড বানিয়ে দেবে।
                </p>
            </div>

            {{-- Quote Textarea --}}
            <div>
                <label class="text-xs font-black text-slate-700 block mb-1">💬 মূল বক্তব্য বা উক্তি *</label>
                <textarea id="quote-card-text" rows="3" oninput="onQuoteFieldChange('text', this.value)" placeholder="যেমন: সংবিধান সংশোধন করেই আমরা জুলাই সনদ বাস্তবায়ন করব।" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500 bg-slate-50 focus:bg-white"></textarea>
            </div>

            {{-- Speaker Name & Designation --}}
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">বক্তার নাম *</label>
                    <input type="text" id="quote-card-name" oninput="onQuoteFieldChange('name', this.value)" placeholder="যেমন: নূরুল ইসলাম মনি" class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500 bg-slate-50 focus:bg-white">
                </div>
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">পদবী</label>
                    <input type="text" id="quote-card-desig" oninput="onQuoteFieldChange('designation', this.value)" placeholder="যেমন: চিফ হুইপ" class="w-full border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-bold text-slate-800 outline-none focus:border-indigo-500 bg-slate-50 focus:bg-white">
                </div>
            </div>

            {{-- Speaker Photo Upload --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 block mb-1">বক্তার ছবি (Photo)</label>
                <label class="cursor-pointer border-2 border-dashed border-indigo-200 bg-indigo-50/50 hover:bg-indigo-50 hover:border-indigo-400 p-3 rounded-2xl text-center font-bold text-xs text-indigo-700 transition flex items-center justify-center gap-2 group">
                    <input type="file" id="quote-card-photo" accept="image/*" class="hidden" onchange="previewQuotePhoto(this)">
                    <i class="fa-solid fa-camera text-base text-indigo-600 group-hover:scale-110 transition-transform"></i>
                    <span id="quote-photo-label">ছবি নির্বাচন করুন</span>
                </label>
            </div>

            {{-- Quote Font Family Selector --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 block mb-1">🔤 উক্তির ফন্ট (Font Family)</label>
                <select id="quote-card-font" onchange="onQuoteFieldChange('font', this.value)" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none bg-slate-50 focus:bg-white focus:border-indigo-500">
                    <option value="'SolaimanLipi'" selected>SolaimanLipi (সোলাইমানলিপি)</option>
                    <option value="'Hind Siliguri', sans-serif">Hind Siliguri (হিন্দ শিলিগুড়ি)</option>
                    <option value="'Noto Sans Bengali', sans-serif">Noto Sans Bengali</option>
                    <option value="'Baloo Da 2', cursive">Baloo Da 2</option>
                    <option value="'Anek Bangla', sans-serif">Anek Bangla</option>
                    <option value="'Li Alinur Banglaborno'">Li Alinur Banglaborno</option>
                    <option value="'Tiro Bangla', serif">Tiro Bangla</option>
                    <option value="'Galada', cursive">Galada</option>
                    @if(isset($dynamicMediaFonts) && count($dynamicMediaFonts) > 0)
                        <optgroup label="📂 Media Library (আপলোড করা ফন্ট)">
                            @foreach($dynamicMediaFonts as $dmf)
                                <option value="'{{ $dmf['family'] }}'">{{ $dmf['family'] }}</option>
                            @endforeach
                        </optgroup>
                    @endif
                </select>
            </div>

            {{-- Theme Selector & Layout Options --}}
            <div>
                <label class="text-[10px] font-bold text-slate-500 block mb-1">🎨 কার্ড থিম ও কালার স্টাইল (৩৫টি প্রিসেট)</label>
                <select id="quote-card-theme" onchange="onQuoteFieldChange('theme', this.value)" class="w-full border border-slate-200 rounded-xl p-2.5 text-xs font-bold text-slate-800 outline-none bg-slate-50 focus:bg-white focus:border-indigo-500">
                    <optgroup label="⚪ ১. লাইট ও মিনিমাল থিম (Light & Clean)">
                        <option value="clean-white">⚪ ক্লিন মিনিমাল হোয়াইট (Pure White)</option>
                        <option value="soft-sky" selected>🔵 সফট স্কাই ব্লু (Soft Sky Blue)</option>
                        <option value="warm-cream">📜 ওয়ার্ম ক্রিম নিউজপ্রিন্ট (Warm Cream)</option>
                        <option value="mint-fresh">🍃 সফট মিন্ট গ্রিন (Mint Fresh)</option>
                        <option value="lavender-soft">💜 সফট ল্যাভেন্ডার (Soft Lavender)</option>
                        <option value="blush-rose">🌸 সফট ব্লাশ পিঙ্ক (Blush Rose)</option>
                        <option value="silver-pearl">🔘 সিলভার পার্ল গ্রে (Silver Pearl)</option>
                    </optgroup>
                    <optgroup label="⚫ ২. ডার্ক ও মিডনাইট সিরিজ (Dark & Midnight)">
                        <option value="dark-slate">⚫ ডার্ক স্লেট / মিডনাইট (Midnight Slate)</option>
                        <option value="dark-navy">🌌 রয়্যাল নেভি ব্লু (Royal Deep Navy)</option>
                        <option value="dark-emerald">🌲 ডিপ এমারেল্ড গ্রিন (Deep Emerald)</option>
                        <option value="dark-burgundy">🍷 ডিপ বার্গান্ডি / ওয়াইন (Deep Burgundy)</option>
                        <option value="dark-charcoal">🖤 চারকোল ও লাক্সারি গোল্ড (Charcoal Gold)</option>
                        <option value="dark-obsidian">🌑 অবসিডিয়ান পিচ ব্ল্যাক (Obsidian Black)</option>
                        <option value="dark-plum">🍇 ডিপ ডার্ক প্লাম (Deep Plum)</option>
                        <option value="dark-teal">🦚 ডিপ ফরেস্ট টিল (Deep Forest Teal)</option>
                    </optgroup>
                    <optgroup label="🔥 ৩. বোল্ড, ব্রেকিং ও প্রাইম কালার (Bold & Breaking)">
                        <option value="breaking-red">🔴 ব্রেকিং ক্রিমসন রেড (Crimson Red)</option>
                        <option value="scarlet-fire">🏮 স্কারলেট ফায়ার রেড (Scarlet Fire)</option>
                        <option value="royal-purple">🔮 রয়্যাল ভায়োলেট পার্পল (Royal Violet)</option>
                        <option value="sunset-orange">🌅 সানসেট গোল্ডেন অরেঞ্জ (Sunset Orange)</option>
                        <option value="amber-flame">⚡ আম্বার গোল্ডেন ফ্লেম (Amber Flame)</option>
                        <option value="ocean-cyan">🌊 ডিপ ওশান সায়ান (Deep Ocean Cyan)</option>
                        <option value="electric-blue">⚡ ইলেকট্রিক কোবাল্ট ব্লু (Electric Blue)</option>
                    </optgroup>
                    <optgroup label="🌈 ৪. আধুনিক গ্রেডিয়েন্ট ও মেটালিক (Modern Gradients)">
                        <option value="aurora-borealis">🌌 অরোরা নর্দার্ন লাইটস (Aurora Teal-Purple)</option>
                        <option value="cosmic-glow">🪐 কসমিক ব্লু-পার্পল গ্লো (Cosmic Glow)</option>
                        <option value="cyber-neon">🧪 সাইবার নিয়ন লাইম (Cyber Neon Lime)</option>
                        <option value="rose-gold">👑 লাক্সারি রোজ গোল্ড (Rose Gold Metallic)</option>
                        <option value="bronze-copper">🪙 ক্লাসিক ব্রোঞ্জ ও কপার (Bronze Copper)</option>
                        <option value="crimson-noir">🩸 ক্রিমসন টু ব্ল্যাক ফেড (Crimson Noir)</option>
                        <option value="sapphire-glow">💎 স্যাফায়ার ব্লু গ্লো (Sapphire Glow)</option>
                    </optgroup>
                    <optgroup label="🍃 ৫. ন্যাচারাল ও আর্দি টোন (Earthy & Nature)">
                        <option value="olive-moss">🫒 অলিভ ও মস গ্রিন (Olive Moss)</option>
                        <option value="terracotta">🏺 টেরাকোটা ক্লে অরেঞ্জ (Terracotta Clay)</option>
                        <option value="espresso-coffee">☕ ডিপ এসপ্রেসো ব্রাউন (Espresso Coffee)</option>
                        <option value="desert-sand">🏜️ ডেজার্ট স্যান্ড গোল্ড (Desert Sand)</option>
                        <option value="sage-green">🌿 ক্লাসিক সেজ গ্রিন (Sage Green)</option>
                        <option value="caramel-mocha">🍮 ক্যারামেল মোকা টোন (Caramel Mocha)</option>
                    </optgroup>
                </select>
            </div>

            {{-- Position & AI Options --}}
            <div class="grid grid-cols-2 gap-2 pt-1">
                <div>
                    <label class="text-[10px] font-bold text-slate-500 block mb-1">ছবির পজিশন</label>
                    <select id="quote-card-pos" onchange="onQuoteFieldChange('position', this.value)" class="w-full border border-slate-200 rounded-xl p-2 text-xs font-bold text-slate-800 outline-none bg-slate-50">
                        <option value="left" selected>ছবি বামে + উক্তি ডানে</option>
                        <option value="right">ছবি ডানে + উক্তি বামে</option>
                    </select>
                </div>
                <div class="space-y-1.5 pt-2">
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="quote-card-bg-check" checked class="rounded accent-indigo-600">
                        <span>AI BG Remove</span>
                    </label>
                    <label class="flex items-center gap-1.5 text-xs font-bold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="quote-card-flip-check" class="rounded accent-indigo-600">
                        <span>↔️ মুখ ঘোরান (Flip)</span>
                    </label>
                </div>
            </div>

            {{-- Unique Textures & Dot Shadow Overlays --}}
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-[10px] font-black text-slate-700 uppercase tracking-wider flex items-center gap-1">
                        <span>✨ ইউনিক ডট প্যাটার্ন ও ব্যাকগ্রাউন্ড টেক্সচার</span>
                    </label>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('none')" class="text-[9px] text-red-500 font-bold hover:underline">রিমুভ</button>
                </div>
                <div class="grid grid-cols-3 gap-1.5">
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('dot-grid')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>⠶ ডট গ্রিড</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('halftone-dots')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>🏁 হাফটোন</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('spotlight-glow')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>🔆 স্পটলাইট</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('vignette-shadow')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>🌫️ ভিনিয়েট</span>
                    </button>
                    <button type="button" onclick="window.customStudio.applyCardTextureOverlay('diagonal-mesh')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>📐 স্ট্রাইপস</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addDecorativeDotCluster('top-right')" class="py-1.5 px-2 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-xs">
                        <span>➕ ডট প্যাচ</span>
                    </button>
                </div>
            </div>

            {{-- Submit Action Button --}}
            <div class="pt-2">
                <button type="button" onclick="submitQuoteCardForm()" class="w-full py-3 bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white font-black text-xs rounded-xl shadow-lg shadow-indigo-500/25 transition transform active:scale-95 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>✨ ১-ক্লিকে উক্তি কার্ড তৈরি করুন</span>
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
        {{-- 💾 SAVED CUSTOM TEMPLATES / PRESETS TAB --}}
        {{-- ========================================================= --}}
        <div id="panel-templates" class="studio-panel space-y-4 hidden">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 p-4 rounded-2xl text-white shadow-lg space-y-2">
                <h3 class="text-xs font-black flex items-center gap-1.5">
                    <i class="fa-solid fa-floppy-disk"></i> আমার সেভ করা টেমপ্লেট ও প্রিসেট
                </h3>
                <p class="text-[10px] text-white/85 leading-relaxed">
                    আপনার তৈরি করা কার্ড ডিজাইন ১-ক্লিকে টেমপ্লেট হিসেবে সেভ করে রাখুন এবং পরবর্তীতে যখন খুশি রি-ইউজ করুন।
                </p>
                <button type="button" onclick="openSaveTemplateModal()" class="w-full mt-1 py-2 bg-white text-emerald-800 rounded-xl font-black text-xs hover:bg-emerald-50 transition shadow-sm flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>বর্তমান ডিজাইনটি সেভ করুন</span>
                </button>
            </div>

            {{-- Saved Templates List Container --}}
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-black text-slate-700">⭐ সংরক্ষিত টেমপ্লেটসমূহ</label>
                    <button type="button" onclick="window.customStudio.renderCustomTemplatesList()" class="text-[10px] font-bold text-indigo-600 hover:underline">
                        🔄 রিফ্রেশ
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
                    <span>+ ছবি যোগ</span>
                </label>
                <button type="button" onclick="window.customStudio.triggerReplaceActiveImage()" class="bg-indigo-50 border border-indigo-200 text-indigo-700 p-2.5 rounded-2xl shadow-xs text-xs font-bold hover:bg-indigo-100 transition-all flex items-center justify-center gap-1.5 text-center" title="সিলেক্ট করা ছবিটি নতুন ছবি দিয়ে পরিবর্তন করুন">
                    <i class="fa-solid fa-arrows-rotate text-xs"></i>
                    <span>🔄 ছবি পরিবর্তন</span>
                </button>
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
                
                {{-- Image Zoom Drag Slider & Percentage Input --}}
                <div class="bg-slate-50 border border-slate-200/80 p-3 rounded-2xl space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-[10px] font-black text-slate-700 flex items-center gap-1">
                            <i class="fa-solid fa-magnifying-glass-plus text-indigo-500"></i>
                            <span>ছবির সাইজ / জুম (Zoom Scale)</span>
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
                            ➖ ছোট
                        </button>
                        <button type="button" onclick="window.customStudio.zoomActiveImage(0.05)" class="flex-1 py-1 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-slate-700 hover:bg-slate-100 transition text-center shadow-xs">
                            ➕ বড়
                        </button>
                        <button type="button" onclick="changeActiveImageZoom(100)" class="flex-1 py-1 bg-white border border-slate-200 rounded-lg text-[11px] font-bold text-indigo-600 hover:bg-slate-100 transition text-center shadow-xs">
                            ↺ ১০০%
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
                        <span>অপাসিটি (স্বচ্ছতা)</span>
                        <span id="opacity-val">100%</span>
                    </label>
                    <input type="range" id="image-opacity-slider" min="0.1" max="1" step="0.05" value="1" oninput="changeActiveOpacity(this.value)" class="w-full accent-indigo-600">
                </div>

                {{-- Photo Color Filter Presets --}}
                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-black text-slate-700">🎚️ ফটো কালার ফিল্টার (Filters)</label>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('reset')" class="text-[10px] text-indigo-600 font-bold hover:underline">রিসেট</button>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5">
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('bw')" class="py-1.5 px-1 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">⚫ B&W</button>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('vintage')" class="py-1.5 px-1 bg-amber-50 hover:bg-amber-100 text-amber-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">🎞️ ভিন্টেজ</button>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('vibrant')" class="py-1.5 px-1 bg-rose-50 hover:bg-rose-100 text-rose-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">✨ ভাইব্রেন্ট</button>
                        <button type="button" onclick="window.customStudio.applyActiveImagePreset('cool')" class="py-1.5 px-1 bg-sky-50 hover:bg-sky-100 text-sky-800 rounded-xl text-[10px] font-bold text-center transition shadow-2xs">🧊 কোল্ড</button>
                    </div>

                    {{-- Manual Filter Sliders --}}
                    <div class="space-y-2 pt-1">
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>ব্রাইটনেস (Brightness)</span>
                            </div>
                            <input type="range" id="filter-brightness-slider" min="-0.5" max="0.5" step="0.02" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('brightness', this.value)" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>কনট্রাস্ট (Contrast)</span>
                            </div>
                            <input type="range" id="filter-contrast-slider" min="-0.5" max="0.5" step="0.02" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('contrast', this.value)" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>স্যাচুরেশন (Saturation)</span>
                            </div>
                            <input type="range" id="filter-saturation-slider" min="-1" max="1" step="0.05" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('saturation', this.value)" class="w-full accent-indigo-600">
                        </div>
                        <div>
                            <div class="flex justify-between text-[9px] font-bold text-slate-400 mb-0.5">
                                <span>ব্লার / ঝাপসা (Blur)</span>
                            </div>
                            <input type="range" id="filter-blur-slider" min="0" max="0.8" step="0.05" value="0" 
                                oninput="window.customStudio.applyActiveImageFilter('blur', this.value)" class="w-full accent-indigo-600">
                        </div>
                    </div>
                </div>

                {{-- Brand Logo / Watermark 1-Click Stamper --}}
                <div class="border-t border-slate-100 pt-3 space-y-2">
                    <label class="text-xs font-black text-slate-700 block">🏷️ ব্র্যান্ড লোগো ও ওয়াটারমার্ক স্ট্যাম্প</label>
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" onclick="window.customStudio.applyBrandLogoStamp('top-left')" class="py-2 px-1.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-2xs">
                            <span>↖️ টপ-লেফট</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applyBrandLogoStamp('top-right')" class="py-2 px-1.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-2xs">
                            <span>↗️ টপ-রাইট</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applyBrandLogoStamp('bottom-right')" class="py-2 px-1.5 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-400 rounded-xl text-[10px] font-bold text-slate-700 transition flex items-center justify-center gap-1 shadow-2xs">
                            <span>↘️ ওয়াটারমার্ক</span>
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
                    @if(isset($dynamicMediaFonts) && count($dynamicMediaFonts) > 0)
                        <optgroup label="📂 Media Library (আপলোড করা ফন্ট)">
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

                {{-- Quick Background Pill Presets --}}
                <div class="bg-slate-50 p-2 rounded-xl border border-slate-200/80 space-y-1.5">
                    <label class="text-[9px] font-black text-slate-500 block uppercase tracking-wider">🏷️ ১-ক্লিক ব্যাকগ্রাউন্ড পিল (TV News Style)</label>
                    <div class="grid grid-cols-4 gap-1">
                        <button type="button" onclick="window.customStudio.applyTextBackgroundPill('#dc2626')" class="py-1 px-1.5 bg-red-600 text-white rounded-lg text-[10px] font-bold hover:bg-red-700 transition text-center shadow-xs">🔴 লাল</button>
                        <button type="button" onclick="window.customStudio.applyTextBackgroundPill('#0f172a')" class="py-1 px-1.5 bg-slate-900 text-white rounded-lg text-[10px] font-bold hover:bg-black transition text-center shadow-xs">⚫ কালো</button>
                        <button type="button" onclick="window.customStudio.applyTextBackgroundPill('#ffffff')" class="py-1 px-1.5 bg-white text-slate-900 border border-slate-300 rounded-lg text-[10px] font-bold hover:bg-slate-100 transition text-center shadow-xs">⚪ সাদা</button>
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
                        <button type="button" onclick="window.customStudio.removeTextShadow()" class="text-[10px] text-red-500 font-bold hover:underline">❌ শ্যাডো রিমুভ</button>
                    </div>

                    {{-- 1-Click Smart Contrast Shadow Presets --}}
                    <div class="grid grid-cols-3 gap-1.5">
                        <button type="button" onclick="window.customStudio.applySmartContrastShadow('dark')" class="py-1 px-1.5 bg-slate-800 text-white rounded-lg text-[10px] font-bold hover:bg-black transition text-center shadow-xs flex items-center justify-center gap-1">
                            <span>🌑 ডার্ক শ্যাডো</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applySmartContrastShadow('glow')" class="py-1 px-1.5 bg-amber-500 text-slate-900 rounded-lg text-[10px] font-bold hover:bg-amber-600 transition text-center shadow-xs flex items-center justify-center gap-1">
                            <span>✨ সফট গ্লো</span>
                        </button>
                        <button type="button" onclick="window.customStudio.applySmartContrastShadow('none')" class="py-1 px-1.5 bg-slate-100 text-red-600 border border-slate-200 rounded-lg text-[10px] font-bold hover:bg-slate-200 transition text-center shadow-xs flex items-center justify-center gap-1">
                            <span>❌ নো শ্যাডো</span>
                        </button>
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
            
            {{-- News Ribbons & Special Banners --}}
            <div>
                <label class="text-xs font-black text-slate-700 block mb-2">🎀 এডিটরিয়াল ফিতা ও ব্যানার (News Ribbons)</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addNewsRibbon('breaking-ribbon')" class="py-2.5 px-2 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-xl text-xs font-black hover:from-red-700 hover:to-rose-700 transition shadow-sm text-center">
                        🔴 ব্রেকিং ফিতা
                    </button>
                    <button type="button" onclick="window.customStudio.addNewsRibbon('exclusive-gold')" class="py-2.5 px-2 bg-gradient-to-r from-amber-600 to-yellow-600 text-white rounded-xl text-xs font-black hover:from-amber-700 hover:to-yellow-700 transition shadow-sm text-center">
                        ⚡ বিশেষ ব্যানার
                    </button>
                </div>
            </div>

            {{-- News Badges & Verified Stickers --}}
            <div class="border-t border-slate-100 pt-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="text-xs font-black text-slate-700">🔴 সংবাদ ব্যাজ ও স্টিকার</label>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addVerifiedBadge()" class="py-2 px-2.5 bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <span>✅ ভেরিফাইড টিক</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addLocationBadge('ঢাকা')" class="py-2 px-2.5 bg-slate-100 border border-slate-200 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-2xs">
                        <span>📍 স্পট লোকেশন</span>
                    </button>
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

            {{-- Decorative Accents & Watermarks --}}
            <div class="border-t border-slate-100 pt-3 space-y-2">
                <label class="text-xs font-black text-slate-700 block">✨ ডেকোরেটিভ উপাদান ও অ্যাকসেন্ট</label>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="window.customStudio.addDecorativeDotCluster('top-right')" class="py-2 px-2.5 bg-slate-50 border border-slate-200 hover:border-indigo-500 rounded-xl text-xs font-bold text-slate-700 transition flex items-center justify-center gap-1.5 shadow-xs">
                        <span>⠶ ডট ম্যাট্রিক্স প্যাচ</span>
                    </button>
                    <button type="button" onclick="window.customStudio.addHugeWatermarkQuoteMark()" class="py-2 px-2.5 bg-slate-50 border border-slate-200 hover:border-indigo-500 rounded-xl text-xs font-bold text-slate-700 transition flex items-center justify-center gap-1.5 shadow-xs">
                        <span>❝ ওয়াটারমার্ক কোট</span>
                    </button>
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
