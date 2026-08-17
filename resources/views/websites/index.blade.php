@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3 tracking-tight">
            🌐 নিউজ সোর্স <span class="text-xs bg-emerald-950/90 text-emerald-400 border border-emerald-800/80 px-3.5 py-1 rounded-full font-bold shadow-inner">{{ $websites->count() }}টি সক্রিয়</span>
        </h1>
    </div>

    @if(session('success'))
        <div class="bg-emerald-900/20 border-l-4 border-emerald-500 text-emerald-300 p-4 mb-6 rounded-lg shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-900/20 border-l-4 border-red-500 text-red-300 p-4 mb-6 rounded-lg shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-900/20 border-l-4 border-red-500 text-red-300 p-4 mb-6 rounded-lg shadow-sm">
            <ul class="list-disc pl-5 text-sm font-bold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- 🔥 SUPER ADMIN ONLY: ADD NEW WEBSITE FORM --}}
    @if(auth()->user()->role === 'super_admin')
    <div class="glass-card p-6 md:p-8 rounded-2xl border border-slate-800/80 shadow-2xl mb-10">
        <h2 class="text-xl font-extrabold text-white mb-6 border-b border-slate-800 pb-3 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-sm border border-emerald-500/20">➕</span> 
            নতুন সোর্স যুক্ত করুন
        </h2>
        <form action="{{ route('websites.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
            @csrf
            
            <div class="lg:col-span-2">
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Website Name</label>
                <input type="text" name="name" class="w-full bg-slate-950/80 border-slate-800 text-slate-100 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 text-sm" required placeholder="Ex: Prothom Alo">
            </div>
            <div class="lg:col-span-2">
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-xs font-bold text-slate-300 uppercase">URL (List Page)</label>
                    <button type="button" onclick="autoDiscoverSelectors()" id="discoverBtn" class="text-xs bg-emerald-950/90 text-emerald-400 hover:bg-emerald-900/90 border border-emerald-700/60 px-3 py-1 rounded-lg font-bold transition flex items-center gap-1 shadow-sm">
                        ⚡ Auto-Detect Selectors & RSS
                    </button>
                </div>
                <input type="url" id="targetUrlInput" name="url" class="w-full bg-slate-950/80 border-slate-800 text-slate-100 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 text-sm" required placeholder="https://prothomalo.com">
                <div id="discoverNotice" class="hidden text-xs mt-1.5 font-bold"></div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Scraper Method</label>
                <select name="scraper_method" class="w-full bg-slate-950/80 border-slate-800 text-slate-100 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 text-sm">
                    <option value="">System Default</option>
                    <option value="node">Node.js (Puppeteer)</option>
                    <option value="python">Python (Playwright)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Target Language</label>
                <select name="target_language" class="w-full bg-slate-950/80 border-slate-800 text-slate-100 rounded-xl focus:ring-emerald-500 focus:border-emerald-500 p-2.5 text-sm">
                    <option value="bn">Bengali (Default)</option>
                    <option value="en">English</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Container</label>
                <input type="text" name="selector_container" class="w-full bg-slate-950/80 border-slate-800 text-slate-300 rounded-xl text-xs font-mono p-2.5" placeholder="Ex: .news-card">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Title</label>
                <input type="text" name="selector_title" class="w-full bg-slate-950/80 border-slate-800 text-slate-300 rounded-xl text-xs font-mono p-2.5" placeholder="Ex: h1, h2">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Content (Body)</label>
                <input type="text" name="selector_content" class="w-full bg-slate-950/80 border-slate-800 text-slate-300 rounded-xl text-xs font-mono p-2.5" placeholder="Ex: .description">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Image (Optional)</label>
                <input type="text" name="selector_image" class="w-full bg-slate-950/80 border-slate-800 text-slate-300 rounded-xl text-xs font-mono p-2.5">
            </div>

            <div class="col-span-1 md:col-span-2 lg:col-span-4 mt-2">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="use_scraping_api" value="1" class="rounded border-slate-700 bg-slate-950 text-emerald-500 shadow-sm focus:border-emerald-400 focus:ring focus:ring-emerald-500/20 w-5 h-5">
                    <span class="ml-2.5 font-bold text-slate-200 text-sm">Use Universal Scraping API (Bypass Proxy)</span>
                </label>
                <p class="text-xs text-slate-400 mt-1 pl-7">Check this if the default proxy gets blocked by the site (e.g. jamuna.tv).</p>
            </div>

            <div class="col-span-1 md:col-span-2 lg:col-span-4 flex justify-end mt-4">
                <button type="submit" class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 px-8 py-3 rounded-xl font-extrabold shadow-lg shadow-emerald-500/20 transition-all transform hover:-translate-y-0.5">
                    💾 Save Website
                </button>
            </div>
        </form>
    </div>
    @endif

    {{-- WEBSITE LIST TABLE --}}
<div class="glass-card rounded-2xl border border-slate-800/80 shadow-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[600px] md:min-w-full">
            <thead>
                <tr class="bg-slate-900/90 text-emerald-400 text-xs uppercase tracking-wider border-b border-slate-800">
                    @if(auth()->user()->role === 'super_admin')
                        <th class="px-4 md:px-6 py-4 font-bold">Name & URL</th>
                        <th class="px-4 md:px-6 py-4 font-bold">Engine</th>
                        <th class="px-4 md:px-6 py-4 font-bold">Selectors</th>
                        <th class="px-4 md:px-6 py-4 font-bold text-right">Actions</th>
                    @else
                        {{-- NORMAL USER HEADER --}}
                        <th class="px-4 md:px-6 py-4 font-bold">Website Name</th>
                        <th class="px-4 md:px-6 py-4 font-bold text-right">Action</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-200 text-sm">
                @foreach($websites as $site)
                
                @php
                    $isDisabled = false;
                    $remainingSeconds = 0;

                    $adminId = in_array(auth()->user()->role, ['staff', 'reporter']) ? auth()->user()->parent_id : auth()->id();
                    $lastScraped = \Illuminate\Support\Facades\Cache::get('scrape_time_user_' . $adminId . '_website_' . $site->id);

                    if ($lastScraped) {
                        $lastScrapedTime = \Carbon\Carbon::parse($lastScraped)->timezone(config('app.timezone'));
                        $now = now()->timezone(config('app.timezone'));
                        $diff = $now->diffInSeconds($lastScrapedTime);
                        
                        if ($diff < 300) { 
                            $isDisabled = true;
                            $remainingSeconds = 300 - $diff;
                        }
                    }
                @endphp

                <tr class="hover:bg-slate-50 transition">
                    @if(auth()->user()->role === 'super_admin')
                        {{-- SUPER ADMIN VIEW --}}
                        <td class="px-4 md:px-6 py-4">
                            <div class="font-bold text-gray-800 text-sm md:text-base">{{ $site->name }}</div>
                            <a href="{{ $site->url }}" target="_blank" class="text-[10px] md:text-xs text-blue-500 hover:underline block truncate max-w-[120px] md:max-w-[200px]">{{ $site->url }} ↗</a>
                        </td>
                        <td class="px-4 md:px-6 py-4">
                            @if($site->scraper_method == 'python')
                                <span class="bg-yellow-100 text-yellow-800 text-[10px] font-bold px-2 py-0.5 rounded border border-yellow-200">Python</span>
                            @elseif($site->scraper_method == 'node')
                                <span class="bg-green-100 text-green-800 text-[10px] font-bold px-2 py-0.5 rounded border border-green-200">Node</span>
                            @else
                                <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded border border-gray-200">Default</span>
                            @endif
                        </td>
                        <td class="px-4 md:px-6 py-4 text-[10px] font-mono text-slate-500">
                            C: {{ $site->selector_container }} <br> 
                            API: {!! $site->use_scraping_api ? '<span class="text-green-600 font-bold">YES</span>' : '<span class="text-red-500">NO</span>' !!}
                        </td>
                        <td class="px-4 md:px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <button onclick='openEditModal(@json($site))' 
                                        class="bg-blue-100 text-blue-700 px-2 py-1 md:px-3 md:py-1.5 rounded-lg text-[10px] md:text-xs font-bold hover:bg-blue-200" title="সম্পাদনা">
                                    ✏️
                                </button>
                                <button onclick="openDeleteModal('{{ $site->id }}', '{{ addslashes($site->name) }}')" 
                                        class="bg-red-100 text-red-700 px-2 py-1 md:px-3 md:py-1.5 rounded-lg text-[10px] md:text-xs font-bold hover:bg-red-200" title="ডিলিট করুন">
                                    🗑️
                                </button>
                                <a href="{{ route('websites.scrape', $site->id) }}" 
                                   id="btn-{{ $site->id }}"
                                   class="scrape-btn bg-green-100 text-green-700 px-2 py-1 md:px-3 md:py-1.5 rounded-lg text-[10px] md:text-xs font-bold {{ $isDisabled ? 'disabled opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                                   data-id="{{ $site->id }}"
                                   data-remaining="{{ $remainingSeconds }}"
                                   onclick="return handleScrapeClick(this)">
                                    @if($isDisabled)
                                        ⏳ <span id="timer-{{ $site->id }}">Wait</span>
                                    @else
                                        <span id="text-{{ $site->id }}">⚡ Observe</span>
                                    @endif
                                </a>
                            </div>
                        </td>

                    @else
                        {{-- NORMAL USER VIEW --}}
                        <td class="px-4 md:px-6 py-4">
                            <div class="flex items-center gap-2 md:gap-3">
                                <div class="bg-indigo-50 text-indigo-600 w-8 h-8 md:w-10 md:h-10 rounded-full flex items-center justify-center font-bold text-sm md:text-lg">
                                    {{ substr($site->name, 0, 1) }}
                                </div>
                                <div class="font-bold text-gray-800 text-sm md:text-base">{{ $site->name }}</div>
                            </div>
                        </td>
                        <td class="px-4 md:px-6 py-4 text-right">
                             <a href="{{ route('websites.scrape', $site->id) }}" 
                               id="btn-{{ $site->id }}"
                               class="scrape-btn bg-indigo-600 text-white px-4 py-2 md:px-6 md:py-2.5 rounded-lg text-xs md:text-sm font-bold hover:bg-indigo-700 shadow-md transition-all inline-flex items-center gap-2 justify-center {{ $isDisabled ? 'disabled opacity-50 cursor-not-allowed pointer-events-none' : '' }}"
                               data-id="{{ $site->id }}"
                               data-remaining="{{ $remainingSeconds }}"
                               onclick="return handleScrapeClick(this)">
                               
                               @if($isDisabled)
                                   ⏳ <span id="timer-{{ $site->id }}">Wait</span>
                               @else
                                   <span id="text-{{ $site->id }}">📥 Click</span>
                               @endif
                            </a>
                        </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</div>

{{-- Edit Modal Structure (Only for Admin) --}}
@if(auth()->user()->role === 'super_admin')
<div id="editModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transform transition-all scale-100">
        <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-bold text-lg text-gray-800">Edit Website Settings</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
        </div>
        
        <form id="editForm" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" id="editName" class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Scraper Method</label>
                    <select name="scraper_method" id="editScraper" class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500">
                        <option value="">Default</option>
                        <option value="node">Node.js</option>
                        <option value="python">Python</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">URL</label>
                    <input type="url" name="url" id="editUrl" class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Target Language</label>
                    <select name="target_language" id="editTargetLanguage" class="w-full border-gray-300 rounded-lg p-2 focus:ring-indigo-500">
                        <option value="bn">Bengali (Default)</option>
                        <option value="en">English</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Container</label>
                    <input type="text" name="selector_container" id="editContainer" class="w-full border-gray-300 rounded-lg p-2 text-sm font-mono bg-slate-50">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Title</label>
                    <input type="text" name="selector_title" id="editTitle" class="w-full border-gray-300 rounded-lg p-2 text-sm font-mono bg-slate-50">
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Content Selector (News Body)</label>
                <input type="text" name="selector_content" id="editContent" class="w-full border-gray-300 rounded-lg p-2 text-sm font-mono bg-slate-50" placeholder="Ex: .description">
                <p class="text-xs text-gray-400 mt-1">Leave empty for auto-detection.</p>
            </div>

            <div class="mb-6">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Image Selector (Optional)</label>
                <input type="text" name="selector_image" id="editImage" class="w-full border-gray-300 rounded-lg p-2 text-sm font-mono bg-slate-50">
            </div>
            
            <div class="mb-4">
                <label class="inline-flex items-center cursor-pointer">
                    <input type="checkbox" name="use_scraping_api" id="editUseScrapingApi" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-5 h-5">
                    <span class="ml-2 font-bold text-gray-700">Use Universal Scraping API</span>
                </label>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg font-bold hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 shadow-sm">Update Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEditModal(site) {
        document.getElementById('editForm').action = `/websites/${site.id}`;
        document.getElementById('editName').value = site.name;
        document.getElementById('editUrl').value = site.url;
        document.getElementById('editScraper').value = site.scraper_method || "";
        document.getElementById('editContainer').value = site.selector_container;
        document.getElementById('editTitle').value = site.selector_title;
        document.getElementById('editImage').value = site.selector_image || "";
        document.getElementById('editContent').value = site.selector_content || "";
        document.getElementById('editUseScrapingApi').checked = site.use_scraping_api ? true : false;
        document.getElementById('editTargetLanguage').value = site.target_language || "bn";
        
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
    }
</script>

{{-- Delete Confirmation Safety Modal (Super Admin Only) --}}
<div id="deleteModal" class="fixed inset-0 bg-black/60 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden transform transition-all">
        <div class="bg-red-50 border-b border-red-100 px-6 py-4 flex justify-between items-center">
            <h3 class="font-bold text-lg text-red-800 flex items-center gap-2">
                ⚠️ সোর্স ডিলিট কনফার্মেশন
            </h3>
            <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-red-500 text-2xl font-bold">&times;</button>
        </div>
        
        <form id="deleteForm" method="POST" class="p-6">
            @csrf
            @method('DELETE')
            
            <p class="text-sm text-gray-700 mb-3">
                আপনি কি নিশ্চিত যে <strong id="deleteSiteName" class="text-red-600"></strong> নিউজ সোর্সটি মুছে ফেলতে চান?
            </p>

            <div class="bg-amber-50 border-l-4 border-amber-500 p-3 rounded text-xs text-amber-800 font-medium mb-4">
                🚨 <strong>সতর্কতা:</strong> ভুলবশত সোর্স মুছে ফেলা রোধ করতে নিচে স্পষ্টভাবে <strong>DELETE</strong> শব্দটি টাইপ করুন।
            </div>

            <div class="mb-4">
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">নিশ্চিত করতে "DELETE" টাইপ করুন:</label>
                <input type="text" name="confirm_text" id="deleteConfirmInput" oninput="checkDeleteInput(this)" class="w-full border-gray-300 rounded-lg p-2.5 text-sm font-mono focus:ring-red-500 uppercase" placeholder="DELETE" required autocomplete="off">
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold hover:bg-gray-200">
                    বাতিল
                </button>
                <button type="submit" id="confirmDeleteBtn" disabled class="px-5 py-2 bg-red-400 text-white rounded-lg text-xs font-bold shadow-md cursor-not-allowed opacity-60 transition">
                    🗑️ হ্যাঁ, মুছে ফেলুন
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openDeleteModal(id, name) {
        document.getElementById('deleteForm').action = '/websites/' + id;
        document.getElementById('deleteSiteName').innerText = name;
        const input = document.getElementById('deleteConfirmInput');
        input.value = '';
        checkDeleteInput(input);
        document.getElementById('deleteModal').classList.remove('hidden');
        document.getElementById('deleteModal').classList.add('flex');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.getElementById('deleteModal').classList.remove('flex');
    }

    function checkDeleteInput(input) {
        const btn = document.getElementById('confirmDeleteBtn');
        if (input.value.trim().toUpperCase() === 'DELETE') {
            btn.disabled = false;
            btn.classList.remove('bg-red-400', 'cursor-not-allowed', 'opacity-60');
            btn.classList.add('bg-red-600', 'hover:bg-red-700', 'cursor-pointer');
        } else {
            btn.disabled = true;
            btn.classList.add('bg-red-400', 'cursor-not-allowed', 'opacity-60');
            btn.classList.remove('bg-red-600', 'hover:bg-red-700', 'cursor-pointer');
        }
    }
</script>
@endif

<script>
    // 🔥🔥 LocalStorage Based Timer Logic 🔥🔥
    document.addEventListener("DOMContentLoaded", function() {
        document.querySelectorAll('.scrape-btn').forEach(button => {
            let id = button.getAttribute('data-id');
            let lastClicked = localStorage.getItem('scrape_time_' + id);
            
            if (lastClicked) {
                let now = new Date().getTime();
                let diff = Math.floor((now - parseInt(lastClicked)) / 1000);
                let waitTime = 300; 

                if (diff < waitTime) {
                    let remaining = waitTime - diff;
                    startTimer(button, id, remaining);
                } else {
                    localStorage.removeItem('scrape_time_' + id);
                }
            }
        });
    });

    function handleScrapeClick(btn) {
        if (btn.classList.contains('disabled')) return false;

        let id = btn.getAttribute('data-id');
        localStorage.setItem('scrape_time_' + id, new Date().getTime());

        btn.classList.add('disabled', 'opacity-50', 'cursor-not-allowed');
        btn.style.pointerEvents = 'none';
        
        let textSpan = document.getElementById('text-' + id);
        if(textSpan) textSpan.innerHTML = '⏳ Starting...';
        else btn.innerHTML = '⏳ Starting...';

        return true; 
    }

    function startTimer(button, id, seconds) {
        button.classList.add('disabled', 'opacity-50', 'cursor-not-allowed');
        button.style.pointerEvents = 'none';
        button.removeAttribute('href');
        button.onclick = null;

        let counter = seconds;
        let timerSpan = document.getElementById('text-' + id) || button;

        const interval = setInterval(() => {
            counter--;
            let m = Math.floor(counter / 60);
            let s = counter % 60;
            
            timerSpan.innerHTML = `Wait ${m}m ${s}s`;

            if (counter <= 0) {
                clearInterval(interval);
                localStorage.removeItem('scrape_time_' + id);
                window.location.reload(); 
            }
        }, 1000);
    }

    function autoDiscoverSelectors() {
        const urlInput = document.getElementById('targetUrlInput');
        const discoverBtn = document.getElementById('discoverBtn');
        const discoverNotice = document.getElementById('discoverNotice');

        if (!urlInput || !urlInput.value) {
            alert('❌ অনুগ্রহ করে প্রথমে একটি সঠিক URL দিন (Ex: https://prothomalo.com)');
            return;
        }

        discoverBtn.innerText = '⏳ খতিয়ে দেখা হচ্ছে...';
        discoverBtn.disabled = true;
        discoverNotice.classList.remove('hidden', 'text-green-600', 'text-red-600');
        discoverNotice.classList.add('text-indigo-600');
        discoverNotice.innerText = '🔍 RSS Feed, Sitemap এবং CSS Selectors স্ক্যান করা হচ্ছে...';

        fetch('{{ route("websites.discover") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ url: urlInput.value })
        })
        .then(res => res.json())
        .then(data => {
            discoverBtn.innerText = '⚡ Auto-Detect Selectors & RSS';
            discoverBtn.disabled = false;

            if (data.success) {
                discoverNotice.classList.remove('text-indigo-600');
                discoverNotice.classList.add('text-green-600');
                let infoMsg = '✅ ' + data.message;
                if (data.rss_feed) infoMsg += ' [RSS Feed পাওয়া গেছে]';
                discoverNotice.innerText = infoMsg;

                if (data.container) document.querySelector('input[name="selector_container"]').value = data.container;
                if (data.title) document.querySelector('input[name="selector_title"]').value = data.title;
                if (data.content) document.querySelector('input[name="selector_content"]').value = data.content;
                if (data.image) document.querySelector('input[name="selector_image"]').value = data.image;
            } else {
                discoverNotice.classList.remove('text-indigo-600');
                discoverNotice.classList.add('text-red-600');
                discoverNotice.innerText = '⚠️ ' + data.message;
            }
        })
        .catch(err => {
            discoverBtn.innerText = '⚡ Auto-Detect Selectors & RSS';
            discoverBtn.disabled = false;
            discoverNotice.classList.remove('text-indigo-600');
            discoverNotice.classList.add('text-red-600');
            discoverNotice.innerText = '❌ নেটওয়ার্ক বা সার্ভার এরর ঘটেছে।';
        });
    }
</script>
@endsection