{{-- ========================================================================= --}}
{{-- 🔵 FACEBOOK PAGES INTEGRATION & 1-CLICK OAUTH SECTION --}}
{{-- ========================================================================= --}}
<div class="settings-accordion-card bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden transition-all duration-200">
    <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-slate-50 transition" onclick="toggleSettingsAccordion(this)">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-xs border border-blue-100">
                <i class="fa-brands fa-facebook"></i>
            </div>
            <div>
                <h2 class="text-base font-black text-slate-800 flex items-center gap-2">
                    <span>Facebook Pages & 1-Click Auto Post</span>
                    <span class="text-[10px] bg-blue-100 text-blue-800 font-extrabold px-2 py-0.5 rounded-full border border-blue-200">1-Click Connect</span>
                </h2>
                <p class="text-xs text-slate-500 font-medium mt-0.5">১-ক্লিকে ফেসবুক পেজ কানেক্ট করুন এবং সোশ্যাল অটো-পোস্টিং ও ফটো কার্ড শেয়ারিং নিয়ন্ত্রণ করুন</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $fbPages->count() > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                {{ $fbPages->count() }} টি পেজ যুক্ত
            </span>
            <i class="fas fa-chevron-down text-slate-400 text-sm accordion-arrow transition-transform duration-300"></i>
        </div>
    </div>
    
    <div class="settings-accordion-body hidden p-5 sm:p-6 border-t border-slate-100 bg-slate-50/40 text-sm space-y-6">
        
        {{-- 1. HERO 1-CLICK CONNECT ACTION BAR --}}
        <div class="bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 rounded-2xl p-5 sm:p-6 text-white shadow-lg relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div class="space-y-1.5 relative z-10 max-w-xl">
                <div class="inline-flex items-center gap-1.5 bg-white/20 text-white text-[11px] font-extrabold px-3 py-0.5 rounded-full backdrop-blur-xs">
                    <i class="fa-solid fa-bolt text-amber-300"></i> 1-Click Direct OAuth
                </div>
                <h3 class="text-lg sm:text-xl font-black tracking-tight">সহজতম উপায়ে ফেসবুক পেজ সংযুক্ত করুন</h3>
                <p class="text-xs text-blue-100 leading-relaxed font-medium">
                    নিচের বাটনে ক্লিক করলেই ফেসবুক ডায়ালগ আসবে। আইডি পাসওয়ার্ড দিয়ে পেজ সিলেক্ট করুন—সিস্টেম স্বয়ংক্রিয়ভাবে পার্মানেন্ট টোকেন ও পেজ আইডি সেভ করে নিবে।
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0 relative z-10">
                <a href="{{ route('fb-pages.connect') }}" class="inline-flex items-center gap-2 bg-white hover:bg-blue-50 text-blue-700 font-black text-xs sm:text-sm px-5 py-3 rounded-xl shadow-md transition transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-brands fa-facebook text-base text-blue-600"></i>
                    <span>Connect with Facebook 🚀</span>
                </a>
                <button type="button" onclick="openSmartTokenModal()" class="inline-flex items-center gap-1.5 bg-blue-800/80 hover:bg-blue-800 text-white font-bold text-xs px-3.5 py-3 rounded-xl border border-white/20 transition cursor-pointer" title="টোকেন পেস্ট করে সব পেজ অটো-ফেচ করুন">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i>
                    <span>স্মার্ট টোকেন ফেচ</span>
                </button>
                <button type="button" onclick="openManualFbModal()" class="inline-flex items-center gap-1.5 bg-white/10 hover:bg-white/20 text-white font-bold text-xs px-3 py-3 rounded-xl border border-white/15 transition cursor-pointer" title="ম্যানুয়ালি পেজ আইডি ও টোকেন বসান">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </div>
        </div>

        {{-- 2. CONNECTED FACEBOOK PAGES LIST --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-black uppercase tracking-wider text-slate-600 flex items-center gap-1.5">
                    <i class="fa-solid fa-list-check text-blue-600"></i> সংযুক্ত ফেসবুক পেজসমূহ ({{ $fbPages->count() }})
                </h4>
                <div class="text-[11px] text-slate-400">
                    নিউজ তৈরি হলে একটিভ পেজগুলোতে স্বয়ংক্রিয়ভাবে পাবলিশ হবে
                </div>
            </div>

            @if($fbPages->count() > 0)
                <div class="grid grid-cols-1 gap-3">
                    @foreach($fbPages as $page)
                        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-2xs hover:border-blue-200 transition flex flex-col md:flex-row md:items-center justify-between gap-4" id="fb-page-card-{{ $page->id }}">
                            
                            {{-- Left: Page Info --}}
                            <div class="flex items-center gap-3.5">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-black shrink-0 border border-blue-100">
                                    <i class="fa-brands fa-facebook"></i>
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <a href="https://facebook.com/{{ $page->page_id }}" target="_blank" class="font-black text-slate-800 hover:text-blue-600 transition text-sm flex items-center gap-1">
                                            <span>{{ $page->page_name }}</span>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                                        </a>
                                        @if($page->test_status === 'connected')
                                            <span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded-md border border-emerald-200">✅ Active</span>
                                        @else
                                            <span class="text-[10px] bg-amber-50 text-amber-700 font-bold px-2 py-0.5 rounded-md border border-amber-200">⚠️ Untested</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-[11px] text-slate-400 mt-0.5 font-mono">
                                        <span>ID: {{ $page->page_id }}</span>
                                        @if($page->last_tested_at)
                                            <span class="text-slate-400">Tested: {{ $page->last_tested_at->diffForHumans() }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Right: Controls & Toggles --}}
                            <div class="flex flex-wrap items-center gap-3 justify-end pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                                
                                {{-- Comment Link Toggle --}}
                                <label class="inline-flex items-center gap-1.5 cursor-pointer bg-slate-50 hover:bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 transition" title="নিউজ সোর্স লিংক ১ম কমেন্টে যাবে নাকি ক্যাপশনে">
                                    <input type="checkbox" onchange="toggleFbCommentLink('{{ $page->id }}')" {{ $page->comment_link ? 'checked' : '' }} class="form-checkbox text-blue-600 rounded">
                                    <span class="text-[11px]">{{ $page->comment_link ? '💬 কমেন্টে লিংক' : '📝 ক্যাপশনে লিংক' }}</span>
                                </label>

                                {{-- Studio Default Toggle --}}
                                <label class="inline-flex items-center gap-1.5 cursor-pointer bg-slate-50 hover:bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-700 transition" title="ফটো কার্ড স্টুডিও মোডালে অটোমেটিক সিলেক্ট থাকবে">
                                    <input type="checkbox" onchange="toggleFbStudioDefault('{{ $page->id }}')" {{ $page->is_studio_default ? 'checked' : '' }} class="form-checkbox text-indigo-600 rounded">
                                    <span class="text-[11px]">🎨 Studio Target</span>
                                </label>

                                {{-- Active Switch --}}
                                <button type="button" onclick="toggleFbActive('{{ $page->id }}')" class="px-2.5 py-1.5 rounded-lg text-xs font-bold border transition {{ $page->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}">
                                    {{ $page->is_active ? '🟢 On' : '⚪ Off' }}
                                </button>

                                {{-- Test Button --}}
                                <button type="button" onclick="testFbPageConnection('{{ $page->id }}', this)" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 rounded-lg text-xs font-bold transition flex items-center gap-1">
                                    <i class="fa-solid fa-bolt"></i> <span>Test</span>
                                </button>

                                {{-- Delete Button --}}
                                <button type="button" onclick="deleteFbPage('{{ $page->id }}', '{{ $page->page_name }}')" class="p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition" title="মুছে ফেলুন">
                                    <i class="fa-solid fa-trash-can text-sm"></i>
                                </button>
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-8 text-center space-y-3">
                    <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto">
                        <i class="fa-brands fa-facebook"></i>
                    </div>
                    <h4 class="text-sm font-bold text-slate-800">কোনো ফেসবুক পেজ কানেক্ট করা নেই</h4>
                    <p class="text-xs text-slate-500 max-w-md mx-auto">
                        আপনার ফেসবুক পেজে অটোমেটিক ফটো কার্ড ও নিউজ পোস্ট করতে উপরের <strong>"Connect with Facebook"</strong> বাটনে ক্লিক করুন।
                    </p>
                </div>
            @endif
        </div>

        {{-- 3. META DEVELOPER APP CONFIGURATION (SUPER ADMIN ONLY) --}}
        @if(auth()->user()->role === 'super_admin')
            <div class="border-t border-slate-200 pt-5 space-y-3">
                <div class="flex items-center justify-between cursor-pointer select-none" onclick="toggleMetaAppConfig()">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                        <i class="fa-solid fa-gears text-indigo-600"></i> Meta Developer App Credentials (গ্লোবাল ১-ক্লিক সেটিংস)
                    </h4>
                    <span class="text-xs text-indigo-600 font-bold hover:underline">⚙️ কনফিগার করুন</span>
                </div>

                <div id="metaAppConfigContainer" class="hidden bg-slate-900 text-slate-200 rounded-2xl p-5 border border-slate-800 space-y-4 font-sans">
                    <div class="space-y-1">
                        <div class="text-xs font-black text-amber-400 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info"></i> ১-ক্লিক লগইন চালুর জন্য প্রয়োজনীয় Meta App সেটিংস:
                        </div>
                        <p class="text-[11px] text-slate-300">
                            <strong>[developers.facebook.com](https://developers.facebook.com)</strong>-এ একটি অ্যাপ তৈরি করে নিচের ক্রেডেনশিয়ালগুলো বসান।
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Facebook App ID</label>
                            <input type="text" id="globalFbAppId" value="{{ $settings->fb_app_id ?? config('services.facebook.app_id') }}" placeholder="e.g. 123456789012345" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-white focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Facebook App Secret</label>
                            <input type="password" id="globalFbAppSecret" value="{{ $settings->fb_app_secret ?? config('services.facebook.app_secret') }}" placeholder="••••••••••••••••" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs font-mono text-white focus:ring-1 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-slate-300">Valid OAuth Redirect URIs (Meta Developer App $\rightarrow$ Facebook Login $\rightarrow$ Settings-এ নিচের লিংকগুলো বসান):</label>
                        
                        {{-- 🌐 Live Production Domain --}}
                        <div class="space-y-1">
                            <span class="text-[10px] text-emerald-400 font-bold uppercase tracking-wider flex items-center gap-1">
                                <i class="fa-solid fa-globe"></i> Live Production Domain (অফিশিয়াল লাইভ লিংক):
                            </span>
                            <div class="flex items-center gap-2">
                                <input type="text" id="fbOAuthLiveUri" value="https://subeditor24.com/facebook-pages/callback" readonly class="w-full bg-slate-950 border border-emerald-800/80 rounded-xl px-3 py-2 text-xs font-mono text-emerald-300 select-all outline-none">
                                <button type="button" onclick="copyTextValue('https://subeditor24.com/facebook-pages/callback', '✅ Live Domain URI কপি হয়েছে!')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shrink-0 transition flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-copy"></i> <span>Copy Live URI</span>
                                </button>
                            </div>
                        </div>

                        {{-- 💻 Current Environment / Local / DDEV --}}
                        <div class="space-y-1 pt-1">
                            <span class="text-[10px] text-blue-400 font-bold uppercase tracking-wider flex items-center gap-1">
                                <i class="fa-solid fa-server"></i> Current Active Host:
                            </span>
                            <div class="flex items-center gap-2">
                                <input type="text" id="fbOAuthRedirectUri" value="{{ route('fb-pages.callback') }}" readonly class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs font-mono text-blue-400 select-all outline-none">
                                <button type="button" onclick="copyTextValue('{{ route('fb-pages.callback') }}', '✅ Current Callback URI কপি হয়েছে!')" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold shrink-0 transition flex items-center gap-1 cursor-pointer">
                                    <i class="fa-solid fa-copy"></i> <span>Copy Current</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-950/80 rounded-xl border border-slate-800 text-xs space-y-2 text-slate-300">
                        <strong class="text-amber-300 block text-xs">👥 ক্লায়েন্টদের টেস্টার হিসেবে অ্যাড করার নিয়ম:</strong>
                        <ol class="list-decimal list-inside space-y-1 text-[11px] text-slate-400">
                            <li>Meta App-এর বাম পাশের মেনু থেকে <strong>App Roles $\rightarrow$ Roles</strong>-এ যান।</li>
                            <li><strong>Add Testers</strong> বাটনে ক্লিক করে ক্লায়েন্টের ফেসবুক নাম/আইডি দিয়ে ইনভাইট দিন।</li>
                            <li>ক্লায়েন্ট <strong>[developers.facebook.com/requests](https://developers.facebook.com/requests)</strong> এ ঢুকে <strong>Accept</strong> চাপলেই ১-ক্লিকে সব কাজ করতে পারবে!</li>
                        </ol>
                    </div>

                    <div class="flex justify-end pt-1">
                        <button type="button" onclick="saveMetaAppCredentials()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-save"></i> <span>Save Meta App Credentials</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>

{{-- ========================================================================= --}}
{{-- ⚡ SMART TOKEN AUTO-FETCH MODAL --}}
{{-- ========================================================================= --}}
<div id="smartTokenModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200 flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-300"></i>
                </div>
                <div>
                    <h3 class="text-base font-black">স্মার্ট ফেসবুক পেজ ফেচ</h3>
                    <p class="text-[11px] text-white/80 font-semibold">টোকেন পেস্ট করলেই সব পেজ অটো-ডিটেক্ট হবে</p>
                </div>
            </div>
            <button type="button" onclick="closeSmartTokenModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 font-bangla">
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">User or Page Access Token</label>
                <textarea id="smartTokenInput" rows="3" placeholder="EAAB... টোকেনটি এখানে পেস্ট করুন" class="w-full border border-slate-300 rounded-xl p-3 text-xs font-mono focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
            </div>

            <div class="flex justify-end">
                <button type="button" id="btnFetchPages" onclick="fetchPagesWithToken()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-magnifying-glass"></i> <span>পেজসমূহ খুঁজুন</span>
                </button>
            </div>

            {{-- Resulting Pages List --}}
            <div id="smartFetchedPagesContainer" class="hidden space-y-2 pt-3 border-t border-slate-100">
                <div class="text-xs font-bold text-slate-700">পাওয়া গেছে এমন পেজসমূহ:</div>
                <div id="smartFetchedPagesList" class="space-y-2 max-h-60 overflow-y-auto"></div>
            </div>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
            <button type="button" onclick="closeSmartTokenModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition">বন্ধ করুন</button>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- ✏️ MANUAL ADD FACEBOOK PAGE MODAL --}}
{{-- ========================================================================= --}}
<div id="manualFbModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md mx-4 overflow-hidden border border-slate-200 flex flex-col">
        <div class="px-6 py-4 bg-slate-900 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base">
                    <i class="fa-brands fa-facebook"></i>
                </div>
                <div>
                    <h3 class="text-sm font-black">ম্যানুয়াল ফেসবুক পেজ যুক্ত করুন</h3>
                    <p class="text-[10px] text-slate-400">Page ID ও Never-Expiring Token বসান</p>
                </div>
            </div>
            <button type="button" onclick="closeManualFbModal()" class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition cursor-pointer">✕</button>
        </div>

        <form onsubmit="submitManualFbPage(event)" class="p-6 space-y-4 font-bangla text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Facebook Page ID <span class="text-rose-500">*</span></label>
                <input type="text" id="manualFbPageId" placeholder="e.g. 1000928374658" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono focus:ring-2 focus:ring-blue-500 outline-none" required>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Page Access Token <span class="text-rose-500">*</span></label>
                <textarea id="manualFbToken" rows="3" placeholder="EAAB..." class="w-full border border-slate-300 rounded-xl p-2.5 text-xs font-mono focus:ring-2 focus:ring-blue-500 outline-none" required></textarea>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Page Name (ঐচ্ছিক)</label>
                <input type="text" id="manualFbPageName" placeholder="My News Page" class="w-full border border-slate-300 rounded-xl p-2.5 text-xs focus:ring-2 focus:ring-blue-500 outline-none">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <label class="flex items-center gap-2 cursor-pointer font-bold text-slate-700">
                    <input type="checkbox" id="manualFbCommentLink" class="form-checkbox text-blue-600 rounded">
                    <span>মূল খবরের লিংক ১ম কমেন্টে পোস্ট করুন</span>
                </label>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeManualFbModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl font-bold transition">Cancel</button>
                <button type="submit" id="btnSaveManualFb" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-md transition flex items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i> <span>Save & Test</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Toggle Meta App Config
    function toggleMetaAppConfig() {
        const container = document.getElementById('metaAppConfigContainer');
        if (container) container.classList.toggle('hidden');
    }

    function copyTextValue(text, msg) {
        navigator.clipboard.writeText(text).then(() => {
            alert(msg || '✅ কপি সম্পন্ন হয়েছে!');
        });
    }

    function copyFbRedirectUri() {
        const input = document.getElementById('fbOAuthRedirectUri');
        if (!input) return;
        copyTextValue(input.value, '✅ OAuth Redirect URI কপি করা হয়েছে!');
    }

    function saveMetaAppCredentials() {
        const appId = document.getElementById('globalFbAppId').value.trim();
        const appSecret = document.getElementById('globalFbAppSecret').value.trim();

        if (!appId || !appSecret) {
            alert('❌ Facebook App ID এবং App Secret উভয়ই আবশ্যক!');
            return;
        }

        fetch('{{ route("fb-pages.save-credentials") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ fb_app_id: appId, fb_app_secret: appSecret })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message || '✅ Credentials সফলভাবে সেভ হয়েছে!');
            } else {
                alert(data.message || '❌ সেভ ব্যর্থ হয়েছে!');
            }
        })
        .catch(err => alert('❌ Error: ' + err.message));
    }

    // Toggle Handlers
    function toggleFbActive(pageId) {
        fetch(`/facebook-pages/${pageId}/toggle`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function toggleFbCommentLink(pageId) {
        fetch(`/facebook-pages/${pageId}/toggle-comment`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function toggleFbStudioDefault(pageId) {
        fetch(`/facebook-pages/${pageId}/toggle-default`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }

    function testFbPageConnection(pageId, btn) {
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

        fetch(`/facebook-pages/${pageId}/test`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            alert(data.message);
            window.location.reload();
        })
        .catch(err => alert('❌ Error: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = orig;
        });
    }

    function deleteFbPage(pageId, name) {
        if (!confirm(`আপনি কি নিশ্চিতভাবে '${name}' পেজটি মুছে ফেলতে চান?`)) return;

        fetch(`/facebook-pages/${pageId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const card = document.getElementById(`fb-page-card-${pageId}`);
                if (card) card.remove();
                window.location.reload();
            }
        });
    }

    // Smart Token Modal Handlers
    function openSmartTokenModal() {
        const modal = document.getElementById('smartTokenModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    function closeSmartTokenModal() {
        const modal = document.getElementById('smartTokenModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function fetchPagesWithToken() {
        const token = document.getElementById('smartTokenInput').value.trim();
        if (!token) {
            alert('❌ অনুগ্রহ করে একটি টোকেন পেস্ট করুন!');
            return;
        }

        const btn = document.getElementById('btnFetchPages');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ফেচ হচ্ছে...';

        fetch('{{ route("fb-pages.fetch-token") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ access_token: token })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.pages && data.pages.length > 0) {
                const container = document.getElementById('smartFetchedPagesContainer');
                const list = document.getElementById('smartFetchedPagesList');
                container.classList.remove('hidden');
                list.innerHTML = '';

                data.pages.forEach(p => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl';
                    row.innerHTML = `
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-sm">
                                <i class="fa-brands fa-facebook"></i>
                            </div>
                            <div>
                                <strong class="text-xs text-slate-800 block">${p.name}</strong>
                                <span class="text-[10px] text-slate-400 font-mono">ID: ${p.id}</span>
                            </div>
                        </div>
                        <button type="button" onclick="connectSinglePage('${p.id}', '${escape(p.name)}', '${p.access_token}')" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition shadow-xs">
                            Connect 🚀
                        </button>
                    `;
                    list.appendChild(row);
                });
            } else {
                alert(data.message || '❌ কোনো পেজ পাওয়া যায়নি!');
            }
        })
        .catch(err => alert('❌ Error: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = orig;
        });
    }

    function connectSinglePage(id, name, token) {
        fetch('{{ route("fb-pages.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                page_id: id,
                page_name: unescape(name),
                access_token: token,
                comment_link: false
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message);
            }
        });
    }

    // Manual FB Modal Handlers
    function openManualFbModal() {
        const modal = document.getElementById('manualFbModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }
    function closeManualFbModal() {
        const modal = document.getElementById('manualFbModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function submitManualFbPage(e) {
        e.preventDefault();
        const pageId = document.getElementById('manualFbPageId').value.trim();
        const token = document.getElementById('manualFbToken').value.trim();
        const name = document.getElementById('manualFbPageName').value.trim();
        const commentLink = document.getElementById('manualFbCommentLink').checked;

        const btn = document.getElementById('btnSaveManualFb');
        const orig = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';

        fetch('{{ route("fb-pages.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                page_id: pageId,
                access_token: token,
                page_name: name,
                comment_link: commentLink
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(err => alert('❌ Error: ' + err.message))
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = orig;
        });
    }
</script>
