@extends('layouts.app')
@section('content')
<div class="max-w-5xl mx-auto py-10 px-4">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.templates.index') }}" class="text-gray-400 hover:text-gray-700 text-sm border border-gray-300 px-3 py-1.5 rounded-lg transition">← Back</a>
        <h1 class="text-xl font-bold text-gray-800">
            {{ $template ? '✏️ Edit: ' . $template->name : '➕ Add New Template' }}
        </h1>
    </div>

    {{-- How-to guide --}}
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4 mb-5 text-sm text-indigo-800 space-y-1">
        <p class="font-bold">📌 How to configure templates?</p>
        <ol class="list-decimal pl-5 space-y-1 text-xs text-indigo-700">
            <li>Provide <strong>Frame URL</strong> → Preview will appear on the right</li>
            <li>Use <strong>Clone From Existing</strong> to copy positions from an existing template</li>
            <li>Adjust <strong>Position numbers</strong> → JSON auto-updates and overlays update in preview</li>
            <li>Select <strong>Fonts</strong> independently for title and date</li>
            <li>Click <strong>Save</strong> when done → It will appear in Studio</li>
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
                            <label class="block text-sm font-bold text-gray-700 mb-1">Template Name <span class="text-red-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $template->name ?? '') }}"
                                   placeholder="e.g. NTV Top Frame"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none transition" required>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Frame URL <span class="text-red-500">*</span>
                                <span class="font-normal text-gray-400 text-xs ml-1">— Original blank frame PNG (1080×1080px)</span>
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
                            <p class="text-xs text-gray-400 mt-1">If left empty, Frame URL will be used as the thumbnail.</p>
                        </div>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded text-indigo-600">
                            <span class="text-sm font-bold text-gray-700">Active — Show in Studio</span>
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
                                <p><strong>How to use:</strong> Upload font file (.ttf, .woff, .woff2) to server or provide Google Fonts CDN URL.</p>
                                <p class="text-purple-400">Example: <code class="bg-purple-100 px-1 rounded">https://cdn.example.com/fonts/MyFont.woff2</code></p>
                                <p>When using this URL, select <strong class="text-purple-700">"Custom Font (from URL)"</strong> in Title/Date font selector.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Clone from Hardcoded Template --}}
                <div class="bg-amber-50 rounded-2xl border border-amber-200 shadow-sm p-5">
                    <h3 class="font-bold text-amber-800 border-b border-amber-200 pb-3 mb-4">⚡ Clone from Existing Template</h3>
                    <p class="text-xs text-amber-700 mb-3">Select an existing template below to automatically fill in standard position coordinates.</p>
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
                        <span class="text-xs font-normal text-gray-400 ml-1">— All values based on 1080×1080 canvas</span>
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
                                        @foreach($fontFamilies as $fKey => $fLabel)
                                            <option value="{{ $fKey }}" {{ ($template->layout_data['date']['fontFamily'] ?? 'SolaimanLipi') == $fKey ? 'selected' : '' }}>{{ $fLabel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="lbl">Color</label>
                                    <input type="text" id="date_fill" oninput="syncJSON()" placeholder="#FFFFFF"
                                           value="{{ old('date_fill', $template->layout_data['date']['fill'] ?? '#FFFFFF') }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Align</label>
                                    <select id="date_textAlign" onchange="syncJSON()" class="pos-input w-full">
                                        <option value="center" {{ ($template->layout_data['date']['textAlign'] ?? 'center') == 'center' ? 'selected' : '' }}>Center</option>
                                        <option value="left" {{ ($template->layout_data['date']['textAlign'] ?? '') == 'left' ? 'selected' : '' }}>Left</option>
                                        <option value="right" {{ ($template->layout_data['date']['textAlign'] ?? '') == 'right' ? 'selected' : '' }}>Right</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- IMAGE --}}
                        <div class="p-4 rounded-xl border border-emerald-100 bg-emerald-50/30">
                            <h4 class="font-bold text-emerald-700 text-sm mb-3">🖼️ News Image</h4>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                <div>
                                    <label class="lbl">Top</label>
                                    <input type="number" id="img_top" oninput="syncJSON()" placeholder="280"
                                           value="{{ old('img_top', $template->layout_data['image']['top'] ?? 280) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Left</label>
                                    <input type="number" id="img_left" oninput="syncJSON()" placeholder="540"
                                           value="{{ old('img_left', $template->layout_data['image']['left'] ?? 540) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Width</label>
                                    <input type="number" id="img_width" oninput="syncJSON()" placeholder="760"
                                           value="{{ old('img_width', $template->layout_data['image']['width'] ?? 760) }}"
                                           class="pos-input w-full">
                                </div>
                                <div>
                                    <label class="lbl">Height</label>
                                    <input type="number" id="img_height" oninput="syncJSON()" placeholder="430"
                                           value="{{ old('img_height', $template->layout_data['image']['height'] ?? 430) }}"
                                           class="pos-input w-full">
                                </div>
                                <div class="col-span-2">
                                    <label class="lbl">Zoom <span class="text-gray-400 font-normal">(1.0 = normal, 1.2 = 20% zoom)</span></label>
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
                    <p class="text-xs text-gray-400 mt-1">Modifying fields above auto-updates JSON, or edit JSON directly.</p>
                </div>

                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition shadow-md text-sm">
                    {{ $template ? '💾 Update Template' : '✅ Save Template' }}
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
                                <span style="font-size:0.75rem;">Enter Frame URL to view preview</span>
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
                            <p>Top=<span id="info_date_top" class="font-mono">990</span>, Left=<span id="info_date_left" class="font-mono">540</span>, fs=<span id="info_date_fs" class="font-mono">22</span></p>
                        </div>
                        <div class="bg-emerald-50 rounded-lg p-2 text-emerald-700">
                            <p class="font-bold">🖼️ Image</p>
                            <p>Top=<span id="info_img_top" class="font-mono">280</span>, Left=<span id="info_img_left" class="font-mono">540</span></p>
                            <p>W=<span id="info_img_w" class="font-mono">760</span>, H=<span id="info_img_h" class="font-mono">430</span>, Zoom=<span id="info_img_z" class="font-mono">1.0</span></p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </form>
</div>

<style>
.lbl { display:block; font-size:0.75rem; font-weight:700; color:#4b5563; margin-bottom:0.25rem; }
.pos-input { border:1px solid #d1d5db; border-radius:0.5rem; padding:0.375rem 0.5rem; font-size:0.875rem; outline:none; transition:all 0.15s; }
.pos-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.2); }
</style>

<script>
const HARDCODED_TEMPLATES = {
    bottom: {
        title: { top: 800, left: 540, width: 1000, fontSize: 60, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 1010, left: 540, fontSize: 22, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 350, left: 540, width: 1000, height: 600, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    ntv: {
        title: { top: 705, left: 555, width: 900, fontSize: 45, fontFamily: 'HindSiliguri', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 855, left: 555, fontSize: 18, fontFamily: 'HindSiliguri', fill: '#E0E7FF', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 310, left: 555, width: 730, height: 410, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    rtv: {
        title: { top: 603, left: 540, width: 880, fontSize: 48, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 825, left: 540, fontSize: 20, fontFamily: 'SolaimanLipi', fill: '#93C5FD', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 250, left: 540, width: 800, height: 450, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    dhakapost: {
        title: { top: 772, left: 545, width: 940, fontSize: 50, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 965, left: 545, fontSize: 20, fontFamily: 'SolaimanLipi', fill: '#CBD5E1', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 320, left: 545, width: 880, height: 495, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    todayevents: {
        title: { top: 760, left: 560, width: 860, fontSize: 46, fontFamily: 'AnekBangla', fill: '#1E293B', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 945, left: 560, fontSize: 20, fontFamily: 'AnekBangla', fill: '#64748B', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 320, left: 560, width: 820, height: 460, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    BanglaLiveNews: {
        title: { top: 685, left: 540, width: 900, fontSize: 48, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 860, left: 540, fontSize: 20, fontFamily: 'SolaimanLipi', fill: '#E2E8F0', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 275, left: 540, width: 820, height: 460, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    Jaijaidin1: {
        title: { top: 750, left: 540, width: 920, fontSize: 48, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 940, left: 540, fontSize: 20, fontFamily: 'SolaimanLipi', fill: '#F1F5F9', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 310, left: 540, width: 850, height: 478, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    ShotterKhoje: {
        title: { top: 730, left: 540, width: 900, fontSize: 46, fontFamily: 'HindSiliguri', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 920, left: 540, fontSize: 20, fontFamily: 'HindSiliguri', fill: '#CBD5E1', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 295, left: 540, width: 840, height: 472, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    jonomot: {
        title: { top: 770, left: 545, width: 920, fontSize: 48, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 960, left: 545, fontSize: 20, fontFamily: 'SolaimanLipi', fill: '#E2E8F0', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 320, left: 545, width: 870, height: 490, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    TodayEventsDualFrame: {
        title: { top: 780, left: 560, width: 880, fontSize: 46, fontFamily: 'AnekBangla', fill: '#0F172A', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 960, left: 560, fontSize: 20, fontFamily: 'AnekBangla', fill: '#475569', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 330, left: 560, width: 830, height: 467, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    Thenews24Main: {
        title: { top: 720, left: 540, width: 900, fontSize: 48, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 905, left: 540, fontSize: 20, fontFamily: 'SolaimanLipi', fill: '#E2E8F0', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 290, left: 540, width: 830, height: 467, zoom: 1.0, originX: 'center', originY: 'center' }
    },
    ITVNews: {
        title: { top: 770, left: 540, width: 920, fontSize: 48, fontFamily: 'SolaimanLipi', fill: '#FFFFFF', textAlign: 'center', maxLines: 2, originX: 'center', originY: 'center' },
        date:  { top: 960, left: 540, fontSize: 20, fontFamily: 'SolaimanLipi', fill: '#E2E8F0', textAlign: 'center', originX: 'center', originY: 'center' },
        image: { top: 320, left: 540, width: 870, height: 490, zoom: 1.0, originX: 'center', originY: 'center' }
    }
};

function cloneFromHardcoded(key) {
    if (!key || !HARDCODED_TEMPLATES[key]) return;
    const t = HARDCODED_TEMPLATES[key];

    // Set Title
    document.getElementById('title_top').value       = t.title.top;
    document.getElementById('title_left').value      = t.title.left;
    document.getElementById('title_width').value     = t.title.width;
    document.getElementById('title_fontSize').value  = t.title.fontSize;
    document.getElementById('title_fontFamily').value= t.title.fontFamily || 'SolaimanLipi';
    document.getElementById('title_fill').value      = t.title.fill || '#FFFFFF';
    document.getElementById('title_textAlign').value = t.title.textAlign || 'center';
    document.getElementById('title_maxLines').value  = t.title.maxLines || 2;

    // Set Date
    document.getElementById('date_top').value        = t.date.top;
    document.getElementById('date_left').value       = t.date.left;
    document.getElementById('date_fontSize').value   = t.date.fontSize;
    document.getElementById('date_fontFamily').value = t.date.fontFamily || 'SolaimanLipi';
    document.getElementById('date_fill').value       = t.date.fill || '#FFFFFF';
    document.getElementById('date_textAlign').value  = t.date.textAlign || 'center';

    // Set Image
    document.getElementById('img_top').value         = t.image.top;
    document.getElementById('img_left').value        = t.image.left;
    document.getElementById('img_width').value       = t.image.width;
    document.getElementById('img_height').value      = t.image.height;
    document.getElementById('img_zoom').value        = t.image.zoom || 1.0;

    syncJSON();
    alert('✅ Positions cloned from "' + key + '"! Adjust as needed.');
}

function syncJSON() {
    const ld = {
        title: {
            top: parseInt(document.getElementById('title_top').value) || 0,
            left: parseInt(document.getElementById('title_left').value) || 0,
            width: parseInt(document.getElementById('title_width').value) || 0,
            fontSize: parseInt(document.getElementById('title_fontSize').value) || 0,
            fontFamily: document.getElementById('title_fontFamily').value,
            fill: document.getElementById('title_fill').value,
            textAlign: document.getElementById('title_textAlign').value,
            maxLines: parseInt(document.getElementById('title_maxLines').value) || 2,
            originX: 'center',
            originY: 'center'
        },
        date: {
            top: parseInt(document.getElementById('date_top').value) || 0,
            left: parseInt(document.getElementById('date_left').value) || 0,
            fontSize: parseInt(document.getElementById('date_fontSize').value) || 0,
            fontFamily: document.getElementById('date_fontFamily').value,
            fill: document.getElementById('date_fill').value,
            textAlign: document.getElementById('date_textAlign').value,
            originX: 'center',
            originY: 'center'
        },
        image: {
            top: parseInt(document.getElementById('img_top').value) || 0,
            left: parseInt(document.getElementById('img_left').value) || 0,
            width: parseInt(document.getElementById('img_width').value) || 0,
            height: parseInt(document.getElementById('img_height').value) || 0,
            zoom: parseFloat(document.getElementById('img_zoom').value) || 1.0,
            originX: 'center',
            originY: 'center'
        }
    };

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

        // Font: If __custom__, save as 'CustomFont', Studio loads @font-face via URL
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
