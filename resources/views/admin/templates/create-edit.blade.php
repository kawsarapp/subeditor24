@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.templates.index') }}" class="text-gray-400 hover:text-gray-700 text-sm border border-gray-300 px-3 py-1.5 rounded-lg transition">← Back</a>
        <h1 class="text-xl font-bold text-gray-800">
            {{ $template ? '✏️ Edit: ' . $template->name : '➕ নতুন Template যোগ করুন' }}
        </h1>
    </div>

    {{-- 💡 How-to guide --}}
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-5 text-sm text-indigo-800 space-y-1">
        <p class="font-bold">📌 কীভাবে template যোগ করবেন?</p>
        <ol class="list-decimal pl-5 space-y-1 text-xs text-indigo-700">
            <li><strong>Frame URL</strong> দিন → ডানদিকে preview দেখাবে</li>
            <li><strong>Clone From Existing</strong> বাটন দিয়ে বিদ্যমান template এর position copy করুন</li>
            <li><strong>Position নম্বর</strong> পরিবর্তন করুন → JSON auto-update হবে + preview এ overlay দেখাবে</li>
            <li><strong>Font</strong> আলাদাভাবে title ও date এর জন্য select করুন</li>
            <li>সব ঠিক হলে <strong>Save</strong> করুন → Studio তে দেখাবে</li>
        </ol>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-5 text-sm text-red-700">
            <ul class="list-disc pl-4 space-y-1">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $template ? route('admin.templates.update', $template->id) : route('admin.templates.store') }}" method="POST">
        @csrf
        @if($template) @method('PUT') @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- LEFT: Form (wider) --}}
            <div class="lg:col-span-3 space-y-5">

                {{-- Basic Info --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="font-bold text-gray-700 border-b pb-3 mb-4">📋 Basic Info</h3>
                    <div class="space-y-4">

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Template নাম <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}"
                                   placeholder="e.g. NTV Top Frame"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Frame URL <span class="text-red-500">*</span>
                                <span class="font-normal text-gray-400 text-xs ml-1">— আসল blank frame PNG (1080×1080px)</span>
                            </label>
                            <input type="url" name="frame_url" id="frame_url_input"
                                   value="{{ old('frame_url', $template->frame_url ?? '') }}"
                                   placeholder="https://your-cdn.com/frames/my-frame.png"
                                   oninput="updatePreview()"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition font-mono" required>
                            <div class="mt-1 flex justify-end">
                                <a href="{{ route('admin.media.index') }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                                    📁 Open Media Manager to upload / copy link
                                </a>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Thumbnail URL <span class="text-gray-400 font-normal text-xs">(optional — sidebar preview)</span>
                            </label>
                            <input type="url" name="thumbnail_url"
                                   value="{{ old('thumbnail_url', $template->thumbnail_url ?? '') }}"
                                   placeholder="https://your-cdn.com/thumbs/preview.jpg"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition font-mono">
                            <p class="text-xs text-gray-400 mt-1">খালি রাখলে Frame URL ই thumbnail হিসেবে কাজ করবে।</p>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-indigo-600">
                            <span class="text-sm font-bold text-gray-700">Active — Studio তে দেখাবে</span>
                        </label>

                        {{-- Custom Font URL --}}
                        <div class="border border-purple-100 bg-purple-50/40 rounded-xl p-4">
                            <label class="block text-sm font-bold text-purple-800 mb-1">
                                🔤 Custom Font URL <span class="font-normal text-purple-500 text-xs">(optional)</span>
                            </label>
                            <input type="url" name="font_url" id="font_url_input"
                                   value="{{ old('font_url', $template->font_url ?? '') }}"
                                   placeholder="https://your-cdn.com/fonts/MyFont.ttf"
                                   class="w-full border border-purple-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-400 outline-none transition font-mono">
                            <div class="mt-1 flex justify-end">
                                <a href="{{ route('admin.media.index') }}" target="_blank" class="text-xs text-purple-600 hover:text-purple-800 font-bold flex items-center gap-1">
                                    📁 Open Media Manager to upload font
                                </a>
                            </div>
                            <div class="mt-2 text-xs text-purple-600 space-y-1">
                                <p><strong>কিভাবে দেবেন:</strong> Font file (.ttf, .woff, .woff2) server এ upload করুন বা Google Fonts CDN URL দিন।</p>
                                <p class="text-purple-400">উদাহরণ: <code class="bg-purple-100 px-1 rounded">https://cdn.example.com/fonts/MyFont.woff2</code></p>
                                <p>এই URL দিলে title ও date এর Font selector এ <strong class="text-purple-700">"Custom Font (from URL)"</strong> option select করুন।</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🔥 Clone from Hardcoded Template --}}
                <div class="bg-amber-50 rounded-2xl border border-amber-200 shadow-sm p-5">
                    <h3 class="font-bold text-amber-800 border-b border-amber-200 pb-3 mb-4">⚡ বিদ্যমান Template থেকে Clone করুন</h3>
                    <p class="text-xs text-amber-700 mb-3">কোনো বিদ্যমান hardcoded template এর position values copy করতে চাইলে এখান থেকে select করুন — সব fields auto-fill হবে।</p>
                    <select onchange="cloneFromHardcoded(this.value)" class="w-full border border-amber-300 rounded-lg px-3 py-2 text-sm bg-white outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">-- Select a template to clone positions --</option>
                        <option value="bottom">bottom (Title: top=800, left=540)</option>
                        <option value="ntv">ntv (Title: top=705, left=555)</option>
                        <option value="rtv">rtv (Title: top=603, left=540)</option>
                        <option value="dhakapost">dhakapost (Title: top=772, left=545)</option>
                        <option value="todayevents">todayevents (Title: top=760, left=560)</option>
                        <option value="BanglaLiveNews">BanglaLiveNews (Title: top=685, left=540)</option>
                        <option value="Jaijaidin1">Jaijaidin1 (Title: top=750, left=540)</option>
                        <option value="ShotterKhoje">ShotterKhoje (Title: top=730, left=540)</option>
                        <option value="jonomot">jonomot (Title: top=770, left=545)</option>
                        <option value="TodayEventsDualFrame">TodayEventsDualFrame (Title: top=780, left=560)</option>
                        <option value="Thenews24Main">Thenews24Main (Title: top=720, left=540)</option>
                        <option value="ITVNews">ITVNews (Title: top=770, left=540)</option>
                    </select>
                </div>

                {{-- Position Config --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <h3 class="font-bold text-gray-700 border-b pb-3 mb-4">
                        📐 Position Configuration
                        <span class="text-xs font-normal text-gray-400 ml-1">— সব values 1080×1080 canvas এ</span>
                    </h3>

                    <div class="space-y-5">

                        {{-- ===== TITLE ===== --}}
                        <div class="border border-indigo-100 bg-indigo-50/40 rounded-xl p-4">
                            <p class="text-xs font-bold text-indigo-700 uppercase tracking-wider mb-4">📝 Title (Headline Text)</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="lbl">Top <span class="text-gray-400 font-normal">(px)</span></label>
                                    <input type="number" id="title_top" oninput="syncJSON()" placeholder="770"
                                           value="{{ old('title_top', $template->layout_data['title']['top'] ?? 770) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Left <span class="text-gray-400 font-normal">(px)</span></label>
                                    <input type="number" id="title_left" oninput="syncJSON()" placeholder="540"
                                           value="{{ old('title_left', $template->layout_data['title']['left'] ?? 540) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Width <span class="text-gray-400 font-normal">(px)</span></label>
                                    <input type="number" id="title_width" oninput="syncJSON()" placeholder="1000"
                                           value="{{ old('title_width', $template->layout_data['title']['width'] ?? 1000) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Font Size</label>
                                    <input type="number" id="title_fontSize" oninput="syncJSON()" placeholder="60"
                                           value="{{ old('title_fontSize', $template->layout_data['title']['fontSize'] ?? 60) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Text Color</label>
                                    <div class="flex gap-1 items-center">
                                        <input type="color" id="title_fill" oninput="syncJSON()"
                                               value="{{ old('title_fill', $template->layout_data['title']['fill'] ?? '#ffffff') }}"
                                               class="w-10 h-9 rounded border border-gray-300 cursor-pointer p-0.5 flex-shrink-0">
                                        <input type="text" id="title_fill_hex" oninput="syncColorHex('title')"
                                               value="{{ old('title_fill', $template->layout_data['title']['fill'] ?? '#ffffff') }}"
                                               placeholder="#ffffff"
                                               class="pos-input flex-1 min-w-0">
                                    </div>
                                </div>
                                <div>
                                    <label class="lbl">BG Color</label>
                                    <input type="text" id="title_bg" oninput="syncJSON()"
                                           value="{{ old('title_bg', $template->layout_data['title']['backgroundColor'] ?? '') }}"
                                           placeholder="transparent"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">TextAlign</label>
                                    <select id="title_textAlign" onchange="syncJSON()" class="pos-input w-full">
                                        <option value="center" {{ ($template->layout_data['title']['textAlign'] ?? 'center') === 'center' ? 'selected' : '' }}>center</option>
                                        <option value="left" {{ ($template->layout_data['title']['textAlign'] ?? '') === 'left' ? 'selected' : '' }}>left</option>
                                        <option value="right" {{ ($template->layout_data['title']['textAlign'] ?? '') === 'right' ? 'selected' : '' }}>right</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="lbl">OriginX</label>
                                    <select id="title_originX" onchange="syncJSON()" class="pos-input w-full">
                                        <option value="center" {{ ($template->layout_data['title']['originX'] ?? 'center') === 'center' ? 'selected' : '' }}>center</option>
                                        <option value="left" {{ ($template->layout_data['title']['originX'] ?? '') === 'left' ? 'selected' : '' }}>left</option>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label class="lbl">🔤 Title Font</label>
                                    <select id="title_fontFamily" onchange="syncJSON()" class="pos-input w-full">
                                        @foreach([
                                            "Hind Siliguri, sans-serif" => "Hind Siliguri (Default)",
                                            "SolaimanLipi" => "SolaimanLipi",
                                            "SutonnyOMJRegular" => "SutonnyOMJ Regular",
                                            "Noto Serif Cond Black" => "Noto Serif Condensed Black",
                                            "Noto Serif Cond Bold" => "Noto Serif Condensed Bold",
                                            "Noto Serif Cond SemiBold" => "Noto Serif Condensed SemiBold",
                                            "Noto Serif Bengali SemiBold" => "Noto Serif Bengali SemiBold",
                                            "NotoSerifBengali-Regular" => "Noto Serif Bengali Regular",
                                            "Li Alinur Banglaborno" => "Li Alinur Banglaborno",
                                            "Li Alinur Kuyasha" => "Li Alinur Kuyasha",
                                            "Li Alinur Sangbadpatra" => "Li Alinur Sangbadpatra",
                                            "Li MA Hai" => "Li MA Hai",
                                            "Li Purno Pran" => "Li Purno Pran",
                                            "Noto Sans Bengali, sans-serif" => "Noto Sans Bengali",
                                            "Baloo Da 2, cursive" => "Baloo Da 2",
                                            "__custom__" => "⭐ Custom Font (from URL above)",
                                        ] as $val => $label)
                                        @php
                                            $currentFont = $template->layout_data['title']['fontFamily'] ?? "Hind Siliguri, sans-serif";
                                            $isSelected = str_contains($currentFont, explode(',', $val)[0]) ? 'selected' : '';
                                        @endphp
                                        <option value="{{ $val }}" {{ $isSelected }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ===== DATE ===== --}}
                        <div class="border border-amber-100 bg-amber-50/30 rounded-xl p-4">
                            <p class="text-xs font-bold text-amber-700 uppercase tracking-wider mb-4">📅 Date Text</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="lbl">Top</label>
                                    <input type="number" id="date_top" oninput="syncJSON()" placeholder="50"
                                           value="{{ old('date_top', $template->layout_data['date']['top'] ?? 50) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Left</label>
                                    <input type="number" id="date_left" oninput="syncJSON()" placeholder="50"
                                           value="{{ old('date_left', $template->layout_data['date']['left'] ?? 50) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Font Size</label>
                                    <input type="number" id="date_fontSize" oninput="syncJSON()" placeholder="28"
                                           value="{{ old('date_fontSize', $template->layout_data['date']['fontSize'] ?? 28) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Text Color</label>
                                    <div class="flex gap-1 items-center">
                                        <input type="color" id="date_fill" oninput="syncJSON()"
                                               value="{{ old('date_fill', $template->layout_data['date']['fill'] ?? '#ffffff') }}"
                                               class="w-10 h-9 rounded border border-gray-300 cursor-pointer p-0.5 flex-shrink-0">
                                        <input type="text" id="date_fill_hex" oninput="syncColorHex('date')"
                                               value="{{ old('date_fill', $template->layout_data['date']['fill'] ?? '#ffffff') }}"
                                               placeholder="#ffffff"
                                               class="pos-input flex-1 min-w-0">
                                    </div>
                                </div>
                                <div>
                                    <label class="lbl">OriginX</label>
                                    <select id="date_originX" onchange="syncJSON()" class="pos-input w-full">
                                        <option value="left" {{ ($template->layout_data['date']['originX'] ?? 'left') === 'left' ? 'selected' : '' }}>left</option>
                                        <option value="center" {{ ($template->layout_data['date']['originX'] ?? '') === 'center' ? 'selected' : '' }}>center</option>
                                        <option value="right" {{ ($template->layout_data['date']['originX'] ?? '') === 'right' ? 'selected' : '' }}>right</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="lbl">BG Color</label>
                                    <input type="text" id="date_bg" oninput="syncJSON()"
                                           value="{{ old('date_bg', $template->layout_data['date']['backgroundColor'] ?? '') }}"
                                           placeholder="transparent or red"
                                           class="pos-input w-full">
                                </div>
                                <div class="col-span-3">
                                    <label class="lbl">🔤 Date Font</label>
                                    <select id="date_fontFamily" onchange="syncJSON()" class="pos-input w-full">
                                        @foreach([
                                            "Hind Siliguri, sans-serif" => "Hind Siliguri (Default)",
                                            "SolaimanLipi" => "SolaimanLipi",
                                            "SutonnyOMJRegular" => "SutonnyOMJ Regular",
                                            "Noto Serif Cond Black" => "Noto Serif Condensed Black",
                                            "Noto Serif Cond Bold" => "Noto Serif Condensed Bold",
                                            "Noto Serif Bengali SemiBold" => "Noto Serif Bengali SemiBold",
                                            "NotoSerifBengali-Regular" => "Noto Serif Bengali Regular",
                                            "__custom__" => "⭐ Custom Font (from URL above)",
                                        ] as $val => $label)
                                        @php
                                            $currentDateFont = $template->layout_data['date']['fontFamily'] ?? "Hind Siliguri, sans-serif";
                                            $isSelected = str_contains($currentDateFont, explode(',', $val)[0]) ? 'selected' : '';
                                        @endphp
                                        <option value="{{ $val }}" {{ $isSelected }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ===== IMAGE ===== --}}
                        <div class="border border-emerald-100 bg-emerald-50/30 rounded-xl p-4">
                            <p class="text-xs font-bold text-emerald-700 uppercase tracking-wider mb-4">🖼️ News Image Area</p>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="lbl">Left</label>
                                    <input type="number" id="img_left" oninput="syncJSON()" placeholder="45"
                                           value="{{ old('img_left', $template->layout_data['image']['left'] ?? 45) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Top</label>
                                    <input type="number" id="img_top" oninput="syncJSON()" placeholder="100"
                                           value="{{ old('img_top', $template->layout_data['image']['top'] ?? 100) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Width</label>
                                    <input type="number" id="img_width" oninput="syncJSON()" placeholder="1000"
                                           value="{{ old('img_width', $template->layout_data['image']['width'] ?? 1000) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Height</label>
                                    <input type="number" id="img_height" oninput="syncJSON()" placeholder="430"
                                           value="{{ old('img_height', $template->layout_data['image']['height'] ?? 430) }}"
                                           class="pos-input w-full">
                                </div>
                                <div class="col-span-2">
                                    <label class="lbl">Zoom <span class="text-gray-400 font-normal">(1.0 = সাধারণ, 1.2 = 20% বড়)</span></label>
                                    <input type="number" id="img_zoom" oninput="syncJSON()" step="0.05" min="0.1" max="5" placeholder="1.0"
                                           value="{{ old('img_zoom', $template->layout_data['image']['zoom'] ?? 1.0) }}"
                                           class="pos-input w-full">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- JSON Output --}}
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-bold text-gray-700">📄 Generated layout_data (JSON)</h3>
                        <button type="button" onclick="copyJson()" class="text-xs border border-gray-300 text-gray-600 px-3 py-1 rounded-lg hover:bg-gray-50 transition">📋 Copy</button>
                    </div>
                    <textarea name="layout_data" id="layout_data_output" rows="14"
                              class="w-full border border-gray-300 rounded-xl px-3 py-2 text-xs font-mono focus:ring-2 focus:ring-indigo-500 outline-none bg-gray-50 transition"
                              required>{{ old('layout_data', $template ? json_encode($template->layout_data, JSON_PRETTY_PRINT) : '') }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">উপরের fields পরিবর্তন করলে এটা auto-update হবে। অথবা সরাসরি edit করুন।</p>
                </div>

                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-md text-sm">
                    {{ $template ? '💾 Update Template' : '✅ Template Save করুন' }}
                </button>
            </div>

            {{-- RIGHT: Preview --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 sticky top-4">
                    <h3 class="font-bold text-gray-700 mb-3">👁️ Live Preview</h3>

                    {{-- Canvas area --}}
                    <div class="bg-gray-100 rounded-xl overflow-hidden" style="position:relative; padding-bottom:100%;">
                        <div id="preview_area" style="position:absolute; inset:0;">
                            <img id="preview_img"
                                 src="{{ $template->frame_url ?? '' }}"
                                 style="width:100%; height:100%; object-fit:cover; display:{{ $template ? 'block' : 'none' }};"
                                 onerror="this.style.display='none'; document.getElementById('preview_placeholder').style.display='flex';"
                                 alt="Frame Preview">
                            <div id="preview_placeholder" style="display:{{ $template ? 'none' : 'flex' }}; position:absolute; inset:0; flex-direction:column; align-items:center; justify-content:center; color:#9ca3af; gap:8px;">
                                <span style="font-size:2.5rem;">🖼️</span>
                                <span style="font-size:0.75rem;">Frame URL দিলে preview দেখাবে</span>
                            </div>
                            {{-- Overlays --}}
                            <div id="overlay_container" style="position:absolute; inset:0; pointer-events:none; display:{{ $template ? 'block' : 'none' }};">
                                <div id="overlay_title" style="position:absolute; display:none; background:rgba(99,102,241,0.15); border:2px solid #6366f1; font-size:8px; font-weight:bold; color:#3730a3; align-items:center; justify-content:center;">📝 Title</div>
                                <div id="overlay_date"  style="position:absolute; display:none; background:rgba(245,158,11,0.15); border:2px solid #f59e0b; font-size:8px; font-weight:bold; color:#92400e; align-items:center; justify-content:center;">📅 Date</div>
                                <div id="overlay_image" style="position:absolute; display:none; background:rgba(16,185,129,0.08); border:2px dashed #10b981; font-size:8px; font-weight:bold; color:#065f46; align-items:center; justify-content:center;">🖼️ Image</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 text-center">Scaled preview (approximate)</p>

                    {{-- Position legend --}}
                    <div class="mt-4 space-y-2 text-xs">
                        <p class="font-bold text-gray-600 border-b pb-1">📖 Position Guide</p>
                        <div class="bg-indigo-50 rounded-lg p-2 text-indigo-700">
                            <p class="font-bold">📝 Title</p>
                            <p>Top=<span id="info_title_top" class="font-mono">770</span>, Left=<span id="info_title_left" class="font-mono">540</span></p>
                            <p>Width=<span id="info_title_width" class="font-mono">1000</span>, fs=<span id="info_title_fs" class="font-mono">60</span></p>
                        </div>
                        <div class="bg-amber-50 rounded-lg p-2 text-amber-700">
                            <p class="font-bold">📅 Date</p>
                            <p>Top=<span id="info_date_top" class="font-mono">50</span>, Left=<span id="info_date_left" class="font-mono">50</span></p>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-2 text-emerald-700">
                            <p class="font-bold">🖼️ Image</p>
                            <p>Left=<span id="info_img_left" class="font-mono">45</span>, Top=<span id="info_img_top" class="font-mono">100</span></p>
                            <p><span id="info_img_width" class="font-mono">1000</span>×<span id="info_img_height" class="font-mono">430</span>, zoom=<span id="info_img_zoom" class="font-mono">1.0</span></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<style>
.lbl { display:block; font-size:0.7rem; font-weight:700; color:#374151; margin-bottom:0.2rem; }
.pos-input { border:1px solid #d1d5db; border-radius:0.5rem; padding:0.3rem 0.5rem; font-size:0.78rem; width:100%; outline:none; transition:border-color .15s; background:#fff; }
.pos-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>

<script>
    // ==========================================
    // Hardcoded template positions for cloning
    // ==========================================
    const HARDCODED = {
        'bottom':              { title:{top:800,left:540,width:980,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:50,left:50,fontSize:30,fill:'#000000',originX:'left',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:0,top:0,width:1080,height:1080,zoom:1.0} },
        'ntv':                 { title:{top:705,left:555,width:1000,fontSize:50,fill:'#000000',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:633,left:240,fontSize:30,fill:'#000000',originX:'right',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:17,top:62,width:1080,height:520,zoom:1.0} },
        'rtv':                 { title:{top:603,left:540,width:950,fontSize:45,fill:'#d90429',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:43,left:500,fontSize:30,fill:'#d90429',originX:'left',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:40,top:115,width:1000,height:430,zoom:0.9} },
        'dhakapost':           { title:{top:772,left:545,width:980,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:20,left:975,fontSize:30,fill:'#000000',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:40,top:130,width:1000,height:430,zoom:1.3} },
        'todayevents':         { title:{top:760,left:560,width:900,fontSize:60,fill:'#000000',textAlign:'center',originX:'center',fontFamily:"Noto Serif Cond Black"}, date:{top:1015,left:640,fontSize:26,fill:'#ffffff',originX:'right',backgroundColor:'red',fontFamily:"SolaimanLipi"}, image:{left:40,top:120,width:1000,height:430,zoom:1.2} },
        'BanglaLiveNews':      { title:{top:685,left:540,width:980,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:43,left:850,fontSize:30,fill:'#000000',originX:'left',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:50,top:150,width:980,height:550,zoom:1.0} },
        'Jaijaidin1':          { title:{top:750,left:540,width:950,fontSize:55,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:38,left:1042,fontSize:28,fill:'#000000',originX:'right',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:40,top:160,width:1000,height:450,zoom:1.1} },
        'ShotterKhoje':        { title:{top:730,left:540,width:900,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:15,left:460,fontSize:28,fill:'#ffffff',originX:'left',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:40,top:80,width:980,height:520,zoom:1.2} },
        'jonomot':             { title:{top:770,left:545,width:1050,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, date:{top:45,left:120,fontSize:30,fill:'#000000',originX:'center',fontFamily:"Hind Siliguri, sans-serif"}, image:{left:1,top:160,width:1080,height:540,zoom:1.0} },
        'TodayEventsDualFrame':{ title:{top:780,left:560,width:1080,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Noto Serif Cond Black"}, date:{top:1015,left:1045,fontSize:25,fill:'#ffffff',originX:'right',fontFamily:"SolaimanLipi"}, image:{left:45,top:100,width:1000,height:480,zoom:1.2} },
        'Thenews24Main':       { title:{top:720,left:540,width:1000,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"Noto Serif Bengali SemiBold"}, date:{top:50,left:1045,fontSize:28,fill:'#000000',originX:'right',fontFamily:"NotoSerifBengali-Regular"}, image:{left:45,top:100,width:1000,height:430,zoom:1.1} },
        'ITVNews':             { title:{top:770,left:540,width:1000,fontSize:60,fill:'#ffffff',textAlign:'center',originX:'center',fontFamily:"SutonnyOMJRegular"}, date:{top:1030,left:1050,fontSize:30,fill:'#ffffff',originX:'right',fontFamily:"SutonnyOMJRegular"}, image:{left:45,top:100,width:1000,height:450,zoom:1.3} },
    };

    function cloneFromHardcoded(key) {
        if (!key || !HARDCODED[key]) return;
        const tpl = HARDCODED[key];

        // Title
        setVal('title_top',      tpl.title.top);
        setVal('title_left',     tpl.title.left);
        setVal('title_width',    tpl.title.width);
        setVal('title_fontSize', tpl.title.fontSize);
        setColor('title', tpl.title.fill || '#ffffff');
        setVal('title_bg',       tpl.title.backgroundColor || '');
        setSelVal('title_textAlign', tpl.title.textAlign || 'center');
        setSelVal('title_originX',   tpl.title.originX || 'center');
        setFontSel('title_fontFamily', tpl.title.fontFamily || 'Hind Siliguri, sans-serif');

        // Date
        setVal('date_top',      tpl.date.top);
        setVal('date_left',     tpl.date.left);
        setVal('date_fontSize', tpl.date.fontSize);
        setColor('date', tpl.date.fill || '#ffffff');
        setVal('date_bg',       tpl.date.backgroundColor || '');
        setSelVal('date_originX', tpl.date.originX || 'left');
        setFontSel('date_fontFamily', tpl.date.fontFamily || 'Hind Siliguri, sans-serif');

        // Image
        setVal('img_left',   tpl.image.left);
        setVal('img_top',    tpl.image.top);
        setVal('img_width',  tpl.image.width);
        setVal('img_height', tpl.image.height);
        setVal('img_zoom',   tpl.image.zoom);

        syncJSON();
        alert('✅ "' + key + '" এর positions clone করা হয়েছে! প্রয়োজনমতো adjust করুন।');
    }

    function setVal(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val;
    }
    function setSelVal(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val;
    }
    function setColor(prefix, hex) {
        const picker = document.getElementById(prefix + '_fill');
        const hexInput = document.getElementById(prefix + '_fill_hex');
        if (picker) picker.value = hex;
        if (hexInput) hexInput.value = hex;
    }
    function setFontSel(id, fontFamily) {
        const el = document.getElementById(id);
        if (!el) return;
        // Try to match the first font name
        const firstName = fontFamily.split(',')[0].replace(/'/g, '').trim();
        for (let opt of el.options) {
            if (opt.value.split(',')[0].trim().toLowerCase() === firstName.toLowerCase()) {
                el.value = opt.value;
                return;
            }
        }
    }
    function syncColorHex(prefix) {
        const hex = document.getElementById(prefix + '_fill_hex').value;
        if (/^#[0-9a-fA-F]{6}$/.test(hex)) {
            document.getElementById(prefix + '_fill').value = hex;
            syncJSON();
        }
    }

    function syncJSON() {
        const titleFill = document.getElementById('title_fill').value;
        const dateFill  = document.getElementById('date_fill').value;

        // Update hex text inputs
        document.getElementById('title_fill_hex').value = titleFill;
        document.getElementById('date_fill_hex').value  = dateFill;

        // Font: __custom__ হলে 'CustomFont' নামে save হবে, Studio তে URL দিয়ে @font-face load হবে
        const titleFontSel = document.getElementById('title_fontFamily').value;
        const dateFontSel  = document.getElementById('date_fontFamily').value;
        const titleFont = titleFontSel === '__custom__' ? 'CustomFont' : ("'" + titleFontSel + "'");
        const dateFont  = dateFontSel  === '__custom__' ? 'CustomFont' : ("'" + dateFontSel  + "'");

        const ld = {
            title: {
                top:             parseInt(document.getElementById('title_top').value)      || 770,
                left:            parseInt(document.getElementById('title_left').value)     || 540,
                width:           parseInt(document.getElementById('title_width').value)    || 1000,
                fontSize:        parseInt(document.getElementById('title_fontSize').value) || 60,
                fill:            titleFill,
                textAlign:       document.getElementById('title_textAlign').value,
                originX:         document.getElementById('title_originX').value,
                backgroundColor: document.getElementById('title_bg').value || '',
                fontFamily:      titleFont
            },
            date: {
                top:             parseInt(document.getElementById('date_top').value)       || 50,
                left:            parseInt(document.getElementById('date_left').value)      || 50,
                fontSize:        parseInt(document.getElementById('date_fontSize').value)  || 28,
                fill:            dateFill,
                originX:         document.getElementById('date_originX').value,
                backgroundColor: document.getElementById('date_bg').value || '',
                fontFamily:      dateFont
            },
            image: {
                left:   parseInt(document.getElementById('img_left').value)    || 45,
                top:    parseInt(document.getElementById('img_top').value)     || 100,
                width:  parseInt(document.getElementById('img_width').value)   || 1000,
                height: parseInt(document.getElementById('img_height').value)  || 430,
                zoom:   parseFloat(document.getElementById('img_zoom').value)  || 1.0
            }
        };

        document.getElementById('layout_data_output').value = JSON.stringify(ld, null, 2);
        updateInfoPanel(ld);
        updateOverlays(ld);
    }

    function updateInfoPanel(ld) {
        const set = (id, val) => { const el = document.getElementById(id); if(el) el.textContent = val; };
        set('info_title_top', ld.title.top);
        set('info_title_left', ld.title.left);
        set('info_title_width', ld.title.width);
        set('info_title_fs', ld.title.fontSize);
        set('info_date_top', ld.date.top);
        set('info_date_left', ld.date.left);
        set('info_img_left', ld.image.left);
        set('info_img_top', ld.image.top);
        set('info_img_width', ld.image.width);
        set('info_img_height', ld.image.height);
        set('info_img_zoom', ld.image.zoom);
    }

    function updateOverlays(ld) {
        const container = document.getElementById('preview_area');
        if (!container) return;
        const containerW = container.offsetWidth || 300;
        const scale = containerW / 1080;

        function setOverlay(id, top, left, width, height) {
            const el = document.getElementById(id);
            if (!el) return;
            el.style.display = 'flex';
            el.style.top    = (top  * scale) + 'px';
            el.style.left   = (left * scale) + 'px';
            el.style.width  = (width  * scale) + 'px';
            el.style.height = Math.max(height * scale, 14) + 'px';
        }

        const t = ld.title;
        const originLeft = t.originX === 'center' ? t.left - t.width / 2 : t.left;
        setOverlay('overlay_title', t.top, originLeft, t.width, t.fontSize + 20);
        setOverlay('overlay_date',  ld.date.top, ld.date.left, 220, ld.date.fontSize + 12);
        setOverlay('overlay_image', ld.image.top, ld.image.left, ld.image.width, ld.image.height);
        document.getElementById('overlay_container').style.display = 'block';
    }

    function updatePreview() {
        const url = document.getElementById('frame_url_input').value.trim();
        const img = document.getElementById('preview_img');
        const placeholder = document.getElementById('preview_placeholder');
        if (url) {
            img.src = url;
            img.style.display = 'block';
            placeholder.style.display = 'none';
        }
    }

    function copyJson() {
        const ta = document.getElementById('layout_data_output');
        navigator.clipboard.writeText(ta.value).then(() => {
            const btn = event.target;
            btn.textContent = '✅ Copied!';
            setTimeout(() => btn.textContent = '📋 Copy', 2000);
        });
    }

    // Init
    document.addEventListener('DOMContentLoaded', function () {
        const existing = document.getElementById('layout_data_output').value.trim();
        if (existing) {
            try {
                const ld = JSON.parse(existing);
                updateInfoPanel(ld);
                updateOverlays(ld);
            } catch(e) {}
        } else {
            syncJSON(); // generate default JSON on create page
        }
    });
</script>
@endsection
