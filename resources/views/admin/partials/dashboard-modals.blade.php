{{-- 1. Template Modal --}}
<div id="templateModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm transition-opacity">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transform scale-100 transition-transform">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-lg text-gray-800">Manage Templates for <span id="modalUserName" class="text-indigo-600"></span></h3>
            <button onclick="closeTemplateModal()" class="text-gray-400 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        <form id="templateForm" method="POST" class="p-6">
            @csrf
            <div class="mb-5">
                <label class="block text-sm font-bold text-gray-700 mb-2">Default Template</label>
                <select name="default_template" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 outline-none text-sm bg-white">
                    @foreach($allTemplates as $key => $name)
                        <option value="{{ $key }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Allowed Templates</label>
                <div class="grid grid-cols-2 gap-2 max-h-60 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-gray-50 custom-scrollbar">
                    {{-- 📋 Built-in Templates --}}
                    <div class="col-span-2 text-xs font-bold text-gray-500 uppercase tracking-widest mt-2 px-1">📋 Built-in Templates</div>
                    @foreach(\App\Models\UserSetting::AVAILABLE_TEMPLATES as $key => $name)
                        <label class="flex items-center space-x-3 p-2 bg-white rounded border border-gray-100 cursor-pointer hover:bg-indigo-50 hover:border-indigo-200 transition">
                            <input type="checkbox" name="templates[]" value="{{ $key }}" class="form-checkbox text-indigo-600 rounded w-4 h-4 focus:ring-indigo-500">
                            <span class="text-sm text-gray-700 font-medium">{{ $name }}</span>
                        </label>
                    @endforeach

                    {{-- 🎨 Custom DB Templates --}}
                    @php
                        $dbTemplatesOnly = array_diff_key($allTemplates, \App\Models\UserSetting::AVAILABLE_TEMPLATES);
                    @endphp
                    @if(count($dbTemplatesOnly) > 0)
                        <div class="col-span-2 text-xs font-bold text-indigo-500 uppercase tracking-widest mt-3 border-t pt-3 px-1">🎨 Custom DB Templates</div>
                        @foreach($dbTemplatesOnly as $key => $name)
                            <label class="flex items-center space-x-3 p-2 bg-indigo-50 rounded border border-indigo-100 cursor-pointer hover:bg-indigo-100 hover:border-indigo-300 transition">
                                <input type="checkbox" name="templates[]" value="{{ $key }}" class="form-checkbox text-indigo-600 rounded w-4 h-4 focus:ring-indigo-500">
                                <span class="text-sm text-indigo-800 font-bold flex items-center gap-1">
                                    {{ $name }}
                                </span>
                            </label>
                        @endforeach
                    @endif
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeTemplateModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg font-bold hover:bg-gray-200 transition text-sm">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition text-sm shadow-md">Save Changes</button>
            </div>
        </form>
    </div>
</div>

{{-- 2. Limit Modal --}}
<div id="limitModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden transform transition-all">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Set Limit for <span id="limitModalUserName" class="text-indigo-600"></span></h3>
            <button onclick="closeLimitModal()" class="text-slate-400 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        <form id="limitForm" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-2">Limit Type (লিমিট এর ধরন)</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/60">
                        <input type="radio" name="post_limit_type" value="daily" id="limitTypeDaily" class="text-indigo-600" onchange="toggleLimitInputs('daily')">
                        <span class="text-xs font-bold text-slate-700">📅 Daily (দৈনিক)</span>
                    </label>
                    <label class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 cursor-pointer hover:bg-slate-50 transition has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/60">
                        <input type="radio" name="post_limit_type" value="monthly" id="limitTypeMonthly" class="text-indigo-600" onchange="toggleLimitInputs('monthly')">
                        <span class="text-xs font-bold text-slate-700">🗓️ Monthly (মাসিক)</span>
                    </label>
                </div>
            </div>

            <div id="dailyLimitWrapper">
                <label class="block text-xs font-bold text-slate-600 mb-1">Daily Post Limit (পোস্ট/দিন)</label>
                <input type="number" name="daily_post_limit" id="limitInput" min="1" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none text-center text-xl font-black text-slate-800">
            </div>

            <div id="monthlyLimitWrapper" class="hidden">
                <label class="block text-xs font-bold text-slate-600 mb-1">Monthly Post Pool (পোস্ট/মাস)</label>
                <input type="number" name="monthly_post_limit" id="monthlyLimitInput" min="1" class="w-full border border-slate-300 rounded-xl p-3 focus:ring-2 focus:ring-indigo-500 outline-none text-center text-xl font-black text-indigo-700">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="closeLimitModal()" class="px-4 py-2 text-slate-600 bg-slate-100 rounded-xl font-bold hover:bg-slate-200 transition text-sm">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-md text-sm">Update Limit</button>
            </div>
        </form>
    </div>
</div>

{{-- 3. Source Modal --}}
<div id="sourceModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-lg text-gray-800">Manage Sources for <span id="sourceModalUserName" class="text-emerald-600"></span></h3>
            <button onclick="closeSourceModal()" class="text-gray-400 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        <form id="sourceForm" method="POST" class="p-6">
            @csrf
            <div class="mb-6">
                <div class="grid grid-cols-2 gap-3 max-h-60 overflow-y-auto p-3 border border-gray-200 rounded-lg bg-gray-50 custom-scrollbar">
                    @foreach($allWebsites as $site)
                        <label class="flex items-center space-x-3 p-2 bg-white rounded border border-gray-100 cursor-pointer hover:bg-emerald-50 hover:border-emerald-200 transition">
                            <input type="checkbox" name="websites[]" value="{{ $site->id }}" class="form-checkbox text-emerald-600 rounded w-4 h-4 focus:ring-emerald-500">
                            <span class="text-sm font-medium text-gray-700">{{ $site->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeSourceModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg font-bold hover:bg-gray-200 transition text-sm">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg font-bold hover:bg-emerald-700 transition shadow-md text-sm">Save Access</button>
            </div>
        </form>
    </div>
</div>

{{-- 4. Scraper Modal --}}
<div id="scraperModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden transform transition-all">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-gray-700">Scraper Config: <span id="scraperUserName" class="text-purple-600"></span></h3>
            <button onclick="closeScraperModal()" class="text-gray-400 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        <form id="scraperForm" method="POST" class="p-6">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-600 mb-2">Scraper Engine</label>
                <select name="scraper_method" id="scraperInput" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none text-sm bg-white">
                    <option value="">Global Default</option>
                    <option value="node">Node.js (Puppeteer) - Fast ⚡</option>
                    <option value="python">Python (Playwright) - Stable 🐍</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-600 mb-2">Auto Clean News After (Days)</label>
                <input type="number" name="auto_clean_days" id="autoCleanDaysInput" min="1" max="90" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none text-sm bg-white" required>
                <p class="text-[10px] text-gray-400 mt-1">Pending news will be automatically deleted after these many days.</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-bold text-gray-600 mb-2">Site Cooldown (Minutes)</label>
                <input type="number" name="scrape_cooldown_minutes" id="cooldownMinutesInput" min="1" max="1440" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none text-sm bg-white" required>
                <p class="text-[10px] text-gray-400 mt-1">Cooldown duration in minutes before re-scraping the same website.</p>
            </div>
            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-600 mb-2">Concurrent Scrapes Limit (5 Mins)</label>
                <input type="number" name="scrape_concurrent_limit" id="concurrentLimitInput" min="1" max="100" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-purple-500 outline-none text-sm bg-white" required>
                <p class="text-[10px] text-gray-400 mt-1">Maximum number of concurrent scrapes allowed per 5 minutes.</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeScraperModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg font-bold hover:bg-gray-200 transition text-sm">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg font-bold hover:bg-purple-700 transition shadow-md text-sm">Save Config</button>
            </div>
        </form>
    </div>
</div>

{{-- 5. Create User Modal --}}
<div id="createUserModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-bold text-lg text-gray-800">Add New User</h3>
            <button onclick="closeCreateUserModal()" class="text-gray-400 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Full Name</label>
                <input type="text" name="name" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500" required>
            </div>
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500" required minlength="8">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Credits</label>
                    <input type="number" name="credits" value="10" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Daily Limit</label>
                    <input type="number" name="daily_post_limit" value="10" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500">
                </div>
            </div>
            {{-- Staff Limit Input (Create) --}}
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Max Staff Limit</label>
                <input type="number" name="staff_limit" value="0" min="0" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500">
                <p class="text-[10px] text-gray-400 mt-1">Maximum number of staff/sub-users this client can create (0 to disable).</p>
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold hover:bg-blue-700 shadow-md w-full">Create User</button>
            </div>
        </form>
    </div>
</div>

{{-- 6. Edit User Modal --}}
<div id="editUserModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden max-h-[90vh] flex flex-col">
        <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
            <div>
                <h3 class="font-black text-lg text-slate-800">Edit User & Subscription</h3>
                <p class="text-xs text-slate-500">ইউজার প্রোফাইল, প্যাকেজ ও মেয়াদ নিয়ন্ত্রণ করুন</p>
            </div>
            <button onclick="closeEditUserModal()" class="text-slate-400 hover:text-red-500 text-2xl transition">&times;</button>
        </div>
        <form id="editUserForm" method="POST" class="p-6 space-y-4 overflow-y-auto custom-scrollbar">
            @csrf @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                    <input type="text" name="name" id="editName" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="editEmail" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">New Password <span class="text-slate-400 font-normal">(Leave empty to keep current)</span></label>
                <input type="password" name="password" placeholder="••••••••" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            {{-- Subscription & Plan Settings Section --}}
            <div class="pt-3 border-t border-slate-100">
                <div class="text-xs font-black text-indigo-600 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <i class="fa-solid fa-crown text-amber-500"></i> সাবস্ক্রিপশন ও প্যাকেজ সেটিংস
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span>Assigned Pricing Plan</span>
                            <span class="text-[10px] text-amber-600 font-extrabold">👑 VIP vs Regular</span>
                        </label>
                        <select name="pricing_plan_id" id="editPricingPlanId" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white font-medium" onchange="onPlanSelectionChanged(this)">
                            <option value="">-- No Plan / Custom Manual --</option>
                            @if(isset($pricingPlans))
                                @php
                                    $vips = $pricingPlans->where('is_vip', true);
                                    $regulars = $pricingPlans->where('is_vip', false);
                                @endphp

                                @if($vips->count() > 0)
                                    <optgroup label="👑 VIP Enterprise Tier (সর্বোচ্চ সুবিধা ও প্রায় শূন্য ডাউনটাইম)">
                                        @foreach($vips as $plan)
                                            <option value="{{ $plan->id }}" data-is-vip="1" class="font-bold text-amber-800 bg-amber-50">
                                                👑 {{ $plan->name }} — ৳{{ number_format($plan->price) }} [VIP Dedicated]
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                @if($regulars->count() > 0)
                                    <optgroup label="⭐ Regular Standard Plans (মাসিক/সাধারণ প্যাকেজ)">
                                        @foreach($regulars as $plan)
                                            <option value="{{ $plan->id }}" data-is-vip="0" class="text-slate-800">
                                                ⭐ {{ $plan->name }} — ৳{{ number_format($plan->price) }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endif
                        </select>
                        <div id="vipPlanNotice" class="hidden text-[11px] font-bold text-amber-900 bg-amber-50 border border-amber-300 rounded-xl p-2.5 mt-2 flex items-start gap-2 shadow-sm animate-pulse">
                            <i class="fa-solid fa-crown text-amber-600 text-sm mt-0.5 shrink-0"></i>
                            <div>
                                <strong class="text-amber-800 block text-xs">👑 ভিআইপি এন্টারপ্রাইজ প্ল্যান নির্বাচিত:</strong>
                                <span class="text-[10px] text-amber-700 leading-tight block mt-0.5">এই ক্লায়েন্ট ডেডিকেটেড হাই-স্পিড সার্ভার, সর্বোচ্চ অটোমেশন ফ্রিডম, প্রায় ০% ডাউনটাইম এবং ২৪/৭ ডেডিকেটেড সাপোর্ট অ্যাক্সেস পাবে।</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Subscription Status</label>
                        <select name="subscription_status" id="editSubscriptionStatus" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="active">Active (সক্রিয়)</option>
                            <option value="expired">Expired (মেয়াদ শেষ)</option>
                            <option value="trial">Trial (ট্রায়াল)</option>
                            <option value="lifetime">Lifetime (আজীবন ফ্রি/ভিআইপি)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Expiry Date (মেয়াদ শেষ হওয়ার তারিখ)</label>
                        <input type="date" name="expire_date" id="editExpireDate" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Staff Limit (সর্বোচ্চ সাব-ইউজার)</label>
                        <input type="number" name="staff_limit" id="editStaffLimit" min="0" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Limit Mode (লিমিট এর ধরন)</label>
                        <select name="post_limit_type" id="editPostLimitType" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                            <option value="daily">📅 Daily Cap (দৈনিক লিমিট)</option>
                            <option value="monthly">🗓️ Monthly Pool (মাসিক মোট পুল)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Post Limit Count (পোস্ট সংখ্যা)</label>
                        <input type="number" name="daily_post_limit" id="editDailyPostLimit" min="0" placeholder="e.g. 20 (Daily) or 600 (Monthly)" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">AI Credits Balance</label>
                        <input type="number" name="credits" id="editCredits" min="0" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Monthly Custom Cap <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="number" name="monthly_post_limit" id="editMonthlyPostLimit" min="0" placeholder="Monthly custom cap" class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="closeEditUserModal()" class="px-4 py-2.5 text-slate-600 bg-slate-100 rounded-xl font-bold hover:bg-slate-200 transition text-sm">Cancel</button>
                <button type="submit" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg text-sm">Update User & Subscription</button>
            </div>
        </form>
    </div>
</div>

{{-- 7. Permission Modal --}}
<div id="permissionModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
            <h3 class="font-bold text-gray-800">Set Permissions for <span id="permUserName" class="text-pink-600"></span></h3>
            <button onclick="closePermissionModal()" class="text-gray-400 hover:text-red-500 text-2xl">&times;</button>
        </div>
        <form id="permissionForm" method="POST" class="p-6">
            @csrf
            <div class="grid grid-cols-1 gap-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                @php
                    $perms = [
                        'can_scrape'            => '🌐 News Scraper Access',
                        'can_central_feed'      => '⚡ Central Live Feed & Wire Pool',
                        'can_direct_publish'    => '📝 Direct Create (News Feed)',
                        'can_ai'                => '🤖 AI Content Rewriter & Drafts',
                        'can_view_published'    => '📋 View Published News Log',
                        'can_youtube_automate'  => '🎬 YouTube AI Automation & Video SEO',
                        'can_studio'            => '🎨 Studio Design Access',
                        'can_custom_photo_card' => '🪄 Photo Card Studio & Quick Cards',
                        'manage_templates'      => '🎴 Card Frame Templates',
                        'can_viral_predictor'   => '🔥 AI Viral & Trending Predictor',
                        'can_seo_intelligence'  => '🔍 SEO & Website Intelligence (Full Suite)',
                        'can_seo_audit'         => '🔍 SEO: Technical Audit Access',
                        'can_seo_gsc'           => '🏆 SEO: Google Top 1-3 & Quick Win Keywords',
                        'can_seo_ga4'           => '📊 SEO: GA4 Traffic Trends & Decay Alert',
                        'can_seo_cwv'           => '⚡ SEO: Core Web Vitals Performance',
                        'can_seo_links'         => '🔗 SEO: Orphan News & Broken Links Detector',
                        'can_seo_ai'            => '🤖 SEO: AI Meta Auto-Fixer & Generator',
                        'can_fact_check'        => '🔍 Fact Check & Plagiarism Finder',
                        'can_auto_post'         => '🚀 Automation & Auto Post',
                        'can_manage_staff'      => '👥 Client can create Sub-Users/Staff',
                        'manage_reporters'      => '👥 Reporter Management & Submissions',
                        'reporter_direct'       => '✍️ Reporter Direct Publish',
                        'can_analytics'         => '📊 View Analytics & Performance',
                        'can_settings'          => '⚙️ Settings Page Access',
                        'can_settings_branding' => '🎨 Branding Settings',
                        'can_settings_proxy'    => '🌐 Proxy & Scraper Settings',
                        'can_settings_target_language' => '🌍 Target Language Settings',
                        'can_settings_ai'       => '🤖 AI API Settings',
                        'can_settings_ai_prompt'=> '✍️ Custom AI Rewrite Prompt Settings',
                        'can_settings_wp_laravel'=> '🔗 WordPress & Laravel API',
                        'can_settings_social'   => '📱 Social Media (FB, X, Telegram)',
                        'can_settings_category' => '📂 Category Mapping',
                    ];
                @endphp
                @foreach($perms as $key => $label)
                <label class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg border border-gray-100 cursor-pointer hover:bg-pink-50 transition">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}" class="form-checkbox text-pink-600 rounded">
                    <span class="text-sm font-bold text-gray-700">{{ $label }}</span>
                </label>
                @endforeach
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t mt-4">
                <button type="button" onclick="closePermissionModal()" class="px-4 py-2 text-gray-600 bg-gray-100 rounded-lg font-bold text-sm">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-pink-600 text-white rounded-lg font-bold hover:bg-pink-700 shadow-md text-sm">Save Permissions</button>
            </div>
        </form>
    </div>
</div>