@extends('layouts.app')

@section('content')
{{-- 🔥 Latest News ID for Polling --}}
<meta name="latest-feed-id" content="{{ $newsItems->first()->id ?? 0 }}">

<style>
    @import url('https://fonts.maateen.me/solaiman-lipi/font.css');
    .font-bangla { 
        font-family: 'SolaimanLipi', Arial, sans-serif; 
    }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-3 {
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    @keyframes fadeInHighlight {
        0% { opacity: 0; transform: translateY(-12px) scale(0.97); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.4); }
        100% { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-fadeIn {
        animation: fadeInHighlight 0.6s ease-out forwards;
    }
</style>

{{-- Toast Notification Container --}}
<div id="centralToast" class="fixed bottom-6 right-6 z-[110] hidden transition-all duration-300 transform translate-y-4">
    <div class="bg-slate-900/95 text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700/80 flex items-center gap-3 backdrop-blur-md">
        <span id="toastIcon" class="text-emerald-400 text-lg">✓</span>
        <span id="toastMessage" class="text-xs sm:text-sm font-bold font-bangla">Notification Message</span>
    </div>
</div>

<div class="max-w-7xl mx-auto py-6 px-3 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 sm:mb-8 gap-4 bg-gradient-to-r from-indigo-900 via-slate-900 to-indigo-950 p-6 sm:p-8 rounded-3xl text-white shadow-xl relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10">
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> LIVE WIRE STREAM
                </span>
                <span class="text-xs text-slate-300 font-semibold">Real-time Ingestion</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight flex items-center gap-3">
                Central Live Wire Feed
            </h1>
            <p class="text-slate-300 text-xs sm:text-sm font-medium mt-1.5 max-w-2xl leading-relaxed">
                Incoming news stream across monitored sources. Review articles, generate photo cards, or import stories directly to your drafts.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3 relative z-10 w-full md:w-auto justify-between md:justify-end">
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/10 text-xs font-bold">
                <span class="text-emerald-400 font-black text-sm" id="statToday">{{ $stats['total_today'] }}</span>
                <span class="text-slate-300">Today's Feed</span>
            </div>
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/10 text-xs font-bold">
                <span class="text-indigo-300 font-black text-sm">{{ $stats['active_sources'] }}</span>
                <span class="text-slate-300">Active Sources</span>
            </div>
            <button onclick="fetchCentralFeedAjax()" class="bg-white hover:bg-slate-100 text-slate-900 px-4 py-2.5 rounded-2xl text-xs font-black flex items-center gap-2 shadow-md transition transform hover:scale-105 cursor-pointer" id="manualRefreshBtn">
                <span id="refreshSpinner" class="hidden animate-spin">🔄</span>
                <span>🔄 Refresh</span>
            </button>
        </div>
    </div>

    {{-- Live AJAX Filter Toolbar (Zero Reload) --}}
    <div class="bg-white border border-slate-200/90 rounded-3xl p-4 sm:p-5 mb-6 shadow-sm space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
            {{-- Instant Live Search --}}
            <div class="md:col-span-6 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input type="text" id="feedSearchInput" placeholder="Search headlines or keywords (Instant Search)..." 
                    oninput="debounceFeedSearch()"
                    class="w-full pl-10 pr-10 py-2.5 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 placeholder-slate-400 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition">
                <button type="button" id="clearSearchBtn" onclick="clearFeedSearch()" class="hidden absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                    ✕
                </button>
            </div>

            {{-- Website Source Filter --}}
            <div class="md:col-span-3">
                <select id="feedWebsiteFilter" class="w-full py-2.5 px-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition cursor-pointer" onchange="fetchCentralFeedAjax()">
                    <option value="">🌐 All Sources ({{ $websites->count() }} Sites)</option>
                    @foreach($websites as $site)
                        <option value="{{ $site->id }}">{{ $site->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Time Filter --}}
            <div class="md:col-span-3">
                <select id="feedHoursFilter" class="w-full py-2.5 px-3.5 rounded-2xl border border-slate-200 bg-slate-50 text-slate-800 text-sm font-semibold focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition cursor-pointer" onchange="fetchCentralFeedAjax()">
                    <option value="">⏱️ All Time (48 Hours)</option>
                    <option value="1">Past 1 Hour</option>
                    <option value="3">Past 3 Hours</option>
                    <option value="6">Past 6 Hours</option>
                    <option value="12">Past 12 Hours</option>
                    <option value="24">Past 24 Hours</option>
                </select>
            </div>
        </div>

        {{-- Bulk Selection Bar & Stats --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs font-bold text-slate-600">
            <div class="flex items-center gap-3">
                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="selectAllFeed" onchange="toggleSelectAllFeed(this)" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-4 h-4">
                    <span class="text-slate-700">Select All</span>
                </label>
                <span id="selectedFeedCount" class="text-indigo-600 font-black hidden">(<span id="selectedCountNum">0</span> selected)</span>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" id="bulkImportBtn" onclick="executeBulkImport()" class="hidden bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md transition items-center gap-1.5 cursor-pointer">
                    📥 Import Selected to Drafts
                </button>
                <span class="text-slate-400">Total Pool Feed: <strong class="text-slate-700" id="totalFeedCount">{{ $newsItems->total() }}</strong> articles</span>
            </div>
        </div>
    </div>

    {{-- Live Dynamic News Container (AJAX Target) --}}
    <div id="centralFeedContainer">
        @include('news.partials.central-feed-grid', ['newsItems' => $newsItems])
    </div>
</div>

<script>
    let searchDebounceTimer = null;

    function showToast(message, isError = false) {
        const toast = document.getElementById('centralToast');
        const msg = document.getElementById('toastMessage');
        const icon = document.getElementById('toastIcon');

        if (!toast || !msg) return;

        msg.innerText = message;
        icon.innerText = isError ? '⚠️' : '✓';
        icon.className = isError ? 'text-amber-400 text-lg' : 'text-emerald-400 text-lg';

        toast.classList.remove('hidden', 'translate-y-4');
        toast.classList.add('translate-y-0');

        setTimeout(() => {
            toast.classList.add('translate-y-4');
            setTimeout(() => toast.classList.add('hidden'), 300);
        }, 3500);
    }

    // ⚡ Debounced Search (Instant Filter without Reload)
    function debounceFeedSearch() {
        const input = document.getElementById('feedSearchInput');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (input.value.trim().length > 0) {
            clearBtn.classList.remove('hidden');
        } else {
            clearBtn.classList.add('hidden');
        }

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            fetchCentralFeedAjax();
        }, 350);
    }

    function clearFeedSearch() {
        const input = document.getElementById('feedSearchInput');
        input.value = '';
        document.getElementById('clearSearchBtn').classList.add('hidden');
        fetchCentralFeedAjax();
    }

    // ⚡ AJAX Load Feed Grid (Zero Page Reload)
    function fetchCentralFeedAjax(pageUrl = null) {
        const search = document.getElementById('feedSearchInput').value;
        const websiteId = document.getElementById('feedWebsiteFilter').value;
        const hours = document.getElementById('feedHoursFilter').value;
        const refreshSpinner = document.getElementById('refreshSpinner');

        if (refreshSpinner) refreshSpinner.classList.remove('hidden');

        let url = pageUrl || "{{ route('central-feed.index') }}";
        const urlObj = new URL(url, window.location.origin);
        if (search) urlObj.searchParams.set('search', search);
        if (websiteId) urlObj.searchParams.set('website_id', websiteId);
        if (hours) urlObj.searchParams.set('hours', hours);

        fetch(urlObj.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (refreshSpinner) refreshSpinner.classList.add('hidden');
            if (data.success) {
                document.getElementById('centralFeedContainer').innerHTML = data.html;
                if (data.total !== undefined) {
                    document.getElementById('totalFeedCount').innerText = data.total;
                }
                if (data.latest_id) {
                    const meta = document.querySelector('meta[name="latest-feed-id"]');
                    if (meta) meta.setAttribute('content', data.latest_id);
                }
                attachAjaxPagination();
            }
        })
        .catch(err => {
            if (refreshSpinner) refreshSpinner.classList.add('hidden');
            console.error(err);
        });
    }

    // ⚡ AJAX Action Dispatcher (AI Rewrite, Studio, Drafts)
    function handleFeedAction(id, action, btn) {
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '⏳ Loading...';

        fetch(`/central-feed/import/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ action: action })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;

            if (data.success) {
                showToast(data.message);

                if (action === 'draft') {
                    btn.classList.remove('bg-slate-100', 'text-slate-700', 'hover:bg-slate-200');
                    btn.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
                    btn.innerHTML = '✅ In Drafts';
                } else if (action === 'ai') {
                    btn.classList.remove('from-indigo-600', 'to-violet-600');
                    btn.classList.add('bg-emerald-600', 'text-white');
                    btn.innerHTML = '⚡ AI Processing Started!';
                    showToast('⚡ AI Rewrite queued! Added to your dashboard feed.');
                } else if (action === 'studio' && data.studio_url) {
                    window.location.href = data.studio_url;
                }
            } else {
                showToast(data.message || 'Action failed.', true);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            showToast('Network error occurred.', true);
        });
    }

    // ⚡ Multi-Select Logic
    function toggleSelectAllFeed(master) {
        document.querySelectorAll('.feed-checkbox').forEach(cb => {
            cb.checked = master.checked;
        });
        updateSelectedFeedCount();
    }

    function updateSelectedFeedCount() {
        const checked = document.querySelectorAll('.feed-checkbox:checked');
        const countSpan = document.getElementById('selectedFeedCount');
        const countNum = document.getElementById('selectedCountNum');
        const bulkBtn = document.getElementById('bulkImportBtn');

        if (checked.length > 0) {
            countSpan.classList.remove('hidden');
            countNum.innerText = checked.length;
            bulkBtn.classList.remove('hidden');
            bulkBtn.classList.add('inline-flex');
        } else {
            countSpan.classList.add('hidden');
            bulkBtn.classList.add('hidden');
            bulkBtn.classList.remove('inline-flex');
            const selectAll = document.getElementById('selectAllFeed');
            if (selectAll) selectAll.checked = false;
        }
    }

    function executeBulkImport() {
        const checked = Array.from(document.querySelectorAll('.feed-checkbox:checked')).map(cb => cb.value);
        if (checked.length === 0) {
            showToast('Please select at least one news item.', true);
            return;
        }

        const btn = document.getElementById('bulkImportBtn');
        btn.disabled = true;
        btn.innerText = '⏳ Importing...';

        fetch("{{ route('central-feed.bulk-import') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: checked })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerText = '📥 Import Selected to Drafts';
            if (data.success) {
                showToast(data.message);
                // Reset checkboxes
                document.querySelectorAll('.feed-checkbox:checked').forEach(cb => {
                    cb.checked = false;
                    const card = document.getElementById(`central-card-${cb.value}`);
                    if (card) {
                        const draftBtn = card.querySelector(`.btn-draft-${cb.value}`);
                        if (draftBtn) {
                            draftBtn.innerHTML = '✅ In Drafts';
                            draftBtn.classList.add('bg-emerald-50', 'text-emerald-700');
                        }
                    }
                });
                updateSelectedFeedCount();
            } else {
                showToast(data.message || 'Import failed.', true);
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerText = '📥 Import Selected to Drafts';
            showToast('Network error occurred.', true);
        });
    }

    // ⚡ Intercept Pagination Click to prevent full page reload
    function attachAjaxPagination() {
        document.querySelectorAll('.ajax-pagination-wrapper a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                if (url) {
                    fetchCentralFeedAjax(url);
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
    }

    // ⚡ Real-Time Auto-Injection (Zero Reload Push)
    document.addEventListener("DOMContentLoaded", function () {
        attachAjaxPagination();

        setInterval(() => {
            const metaLastId = document.querySelector('meta[name="latest-feed-id"]');
            const lastId = metaLastId ? parseInt(metaLastId.getAttribute('content')) || 0 : 0;
            const searchVal = document.getElementById('feedSearchInput')?.value || '';
            const websiteFilter = document.getElementById('feedWebsiteFilter')?.value || '';

            // Only auto-inject if user is not actively searching with a custom query
            if (lastId > 0 && searchVal === '' && websiteFilter === '') {
                fetch(`{{ route('central-feed.check-new') }}?last_id=${lastId}&fetch_items=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.new_count && data.new_count > 0 && data.html) {
                        const grid = document.getElementById('centralFeedGrid');
                        if (grid) {
                            grid.insertAdjacentHTML('afterbegin', data.html);
                            showToast(`⚡ ${data.new_count} new articles automatically added to Central Feed!`);
                        }
                        if (data.latest_id) {
                            metaLastId.setAttribute('content', data.latest_id);
                        }
                        const countSpan = document.getElementById('totalFeedCount');
                        if (countSpan) {
                            countSpan.innerText = parseInt(countSpan.innerText || 0) + data.new_count;
                        }
                    }
                })
                .catch(() => {});
            }
        }, 12000); // Check every 12 seconds
    });
</script>
@endsection
