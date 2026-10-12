{{-- 1.5 Edit Staff Info & Role Modal --}}
<div id="editStaffModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 transition-all">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl flex flex-col max-h-[92vh] overflow-hidden border border-slate-100 animate-in fade-in zoom-in-95 duration-200">
        
        {{-- Header --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 px-6 py-4.5 border-b border-indigo-900/50 flex justify-between items-center flex-shrink-0 text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-300">
                    <i class="fa-solid fa-user-gear text-lg"></i>
                </div>
                <div>
                    <h3 class="font-black text-base tracking-tight text-white flex items-center gap-2">
                        টিম মেম্বার তথ্য ও রোল পরিবর্তন
                        <span id="editModalStaffNameBadge" class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">Edit</span>
                    </h3>
                    <p class="text-xs text-slate-300">রোল, পারমিশন ও প্রোফাইল ডাটা হালনাগাদ করুন</p>
                </div>
            </div>
            <button type="button" onclick="closeEditStaffModal()" class="w-8 h-8 rounded-lg bg-white/10 hover:bg-rose-500/20 text-slate-300 hover:text-rose-300 flex items-center justify-center transition-colors">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <form id="editStaffForm" method="POST" class="flex flex-col flex-1 overflow-hidden">
            @csrf @method('PUT')
            
            <div class="p-6 space-y-6 overflow-y-auto flex-1 custom-scrollbar bg-slate-50/50">
                
                {{-- 1. Role Selection Banner --}}
                <div class="bg-indigo-50/80 border border-indigo-100 rounded-2xl p-4.5 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 mb-2">
                        <div>
                            <label class="block text-xs font-black uppercase tracking-wider text-indigo-900">
                                <i class="fa-solid fa-shield-halved text-indigo-600 mr-1.5"></i>
                                দায়িত্ব ও রোল (Assignable Role) <span class="text-rose-500">*</span>
                            </label>
                            <p class="text-[11px] text-indigo-700/80">রোল পরিবর্তন করলে নিচের পারমিশন সেকশনটি স্বয়ংক্রিয়ভাবে সিঙ্ক হবে</p>
                        </div>
                        <button type="button" onclick="applyRoleDefaults('edit')" class="text-xs font-extrabold text-indigo-700 hover:text-indigo-900 bg-white hover:bg-indigo-100/70 border border-indigo-200 px-3 py-1.5 rounded-xl shadow-xs transition flex items-center gap-1.5 self-start md:self-auto">
                            <i class="fa-solid fa-wand-magic-sparkles text-indigo-600"></i>
                            ডিফল্ট পারমিশন সেট করুন
                        </button>
                    </div>

                    <div class="relative">
                        <select name="role" id="edit_role" onchange="onRoleChanged('edit')" class="w-full bg-white border-2 border-indigo-200 rounded-xl px-4 py-3 text-sm font-black text-slate-800 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-600 transition outline-none cursor-pointer" required>
                            <option value="editor">👑 নিউজ এডিটর (News Editor) — পূর্ণ সম্পাদকীয় ও ক্রিয়েটিভ এক্সেস</option>
                            <option value="sub_editor">📝 সহ-সম্পাদক (Sub-Editor) — সংবাদ ড্রাফট, এআই রিরাইট ও ফটো কার্ড</option>
                            <option value="staff">👥 স্টাফ / অপারেটর (Newsroom Staff) — প্রাথমিক নিউজ রুম অপারেটর</option>
                            <option value="reporter">🎙️ মাঠ পর্যায়ের রিপোর্টার (Reporter) — সংবাদ তৈরি ও নিজস্ব রিপোর্ট</option>
                        </select>
                    </div>
                </div>

                {{-- 2. Basic Account Information --}}
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-500 flex items-center gap-2">
                        <i class="fa-solid fa-id-badge text-indigo-500"></i>
                        প্রাথমিক তথ্য ও লগইন ক্রেডেনশিয়াল
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">পূর্ণ নাম <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" id="edit_name" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">ইমেইল <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" id="edit_email" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">নতুন পাসওয়ার্ড (পরিবর্তন না করতে চাইলে ফাঁকা রাখুন)</label>
                            <input type="password" name="password" placeholder="অপরিবর্তিত থাকবে" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none" minlength="6">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">বিভাগ (Department)</label>
                            <select name="department_id" id="edit_department_id" onchange="loadDesignations('edit')" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                <option value="">-- বিভাগ নির্বাচন করুন --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">পদবী (Designation)</label>
                            <select name="designation_id" id="edit_designation_id" class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 outline-none bg-white">
                                <option value="">-- নির্বাচন করুন --</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- 3. Visual Categorized Permissions Section --}}
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                        <div>
                            <h4 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-key text-pink-500"></i>
                                মডিউল ও ফিচার পারমিশন (Feature Matrix)
                            </h4>
                            <p class="text-[11px] text-slate-500">স্টাফ সদস্যটির বর্তমান পারমিশন কাস্টমাইজ করুন</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="toggleAllPerms('edit', true)" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-2.5 py-1 rounded-lg">
                                সব সিলেক্ট
                            </button>
                            <button type="button" onclick="toggleAllPerms('edit', false)" class="text-[11px] font-bold text-slate-600 hover:text-slate-800 bg-slate-100 px-2.5 py-1 rounded-lg">
                                ক্লিয়ার
                            </button>
                        </div>
                    </div>

                    @php
                        $adminPerms = is_array($admin->permissions) ? $admin->permissions : json_decode($admin->permissions, true) ?? [];
                        if ($admin->role === 'admin' && empty($adminPerms)) {
                            $adminPerms = [
                                'can_latest_news', 'can_settings', 'can_settings_branding', 'can_settings_target_language',
                                'can_settings_wp_laravel', 'can_settings_social', 'can_settings_category',
                                'can_settings_ai_prompt', 'can_scrape', 'can_ai', 'can_studio', 
                                'can_direct_publish', 'can_view_published', 'can_auto_post', 
                                'can_central_feed', 'can_custom_photo_card', 'can_manage_staff',
                                'manage_reporters', 'reporter_direct'
                            ];
                        }
                    @endphp

                    <div class="space-y-4">
                        @foreach($permissionCategories as $catKey => $catData)
                        <div class="border border-slate-200/80 rounded-xl p-3.5 bg-slate-50/40">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-xs font-black text-slate-700 flex items-center gap-2">
                                    <i class="{{ $catData['icon'] }} text-indigo-500"></i>
                                    {{ $catData['name'] }} ({{ $catKey }})
                                </span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Category</span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                @foreach($catData['permissions'] as $pKey => $pLabel)
                                    @php
                                        $canGrant = ($admin->role === 'super_admin') || in_array($pKey, $adminPerms);
                                    @endphp
                                    <label class="flex items-center space-x-2.5 p-2 rounded-lg border text-xs cursor-pointer transition {{ $canGrant ? 'bg-white hover:bg-indigo-50/60 border-slate-200' : 'bg-slate-100/80 border-slate-200/50 opacity-50 cursor-not-allowed' }}">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $pKey }}" 
                                               class="edit-perm-check form-checkbox text-indigo-600 rounded border-slate-300 focus:ring-indigo-500"
                                               data-permission="{{ $pKey }}"
                                               {{ !$canGrant ? 'disabled' : '' }}>
                                        <span class="font-bold text-slate-700 select-none">{{ $pLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- 4. Additional HR Details (Collapsible) --}}
                <details class="group bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <summary class="flex justify-between items-center p-4.5 cursor-pointer font-bold text-xs text-slate-700 select-none hover:bg-slate-50">
                        <span class="flex items-center gap-2">
                            <i class="fa-solid fa-address-card text-emerald-500"></i>
                            অতিরিক্ত এইচআর ও ব্যক্তিগত তথ্য
                        </span>
                        <i class="fa-solid fa-chevron-down text-slate-400 group-open:rotate-180 transition-transform"></i>
                    </summary>
                    <div class="p-5 pt-2 border-t border-slate-100 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">জেলা (District)</label>
                                <input type="text" name="district" id="edit_district" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">উপজেলা / থানা</label>
                                <input type="text" name="upazila" id="edit_upazila" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">কর্মক্ষেত্র / অফিস লোকেশন</label>
                            <input type="text" name="working_location" id="edit_working_location" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">মোবাইল নম্বর</label>
                                <input type="text" name="phone" id="edit_phone" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">জরুরি যোগাযোগ নম্বর</label>
                                <input type="text" name="emergency_contact" id="edit_emergency_contact" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">এনআইডি নম্বর (NID)</label>
                                <input type="text" name="nid" id="edit_nid" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">রক্তের গ্রুপ</label>
                                <select name="blood_group" id="edit_blood_group" class="w-full border rounded-xl p-2.5 text-xs bg-white outline-none">
                                    <option value="">-- নির্বাচন করুন --</option>
                                    <option value="A+">A+</option><option value="A-">A-</option>
                                    <option value="B+">B+</option><option value="B-">B-</option>
                                    <option value="O+">O+</option><option value="O-">O-</option>
                                    <option value="AB+">AB+</option><option value="AB-">AB-</option>
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">বর্তমান ঠিকানা</label>
                                <textarea name="present_address" id="edit_present_address" rows="2" class="w-full border rounded-xl p-2 text-xs bg-white"></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">স্থায়ী ঠিকানা</label>
                                <textarea name="permanent_address" id="edit_permanent_address" rows="2" class="w-full border rounded-xl p-2 text-xs bg-white"></textarea>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">যোগদানের তারিখ</label>
                                <input type="date" name="joining_date" id="edit_joining_date" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">মেয়াদ উত্তীর্ণের তারিখ</label>
                                <input type="date" name="expire_date" id="edit_expire_date" class="w-full border rounded-xl p-2.5 text-xs bg-white">
                            </div>
                        </div>
                    </div>
                </details>

            </div>

            {{-- Footer --}}
            <div class="p-4 px-6 bg-slate-100/80 border-t border-slate-200 flex justify-between items-center flex-shrink-0">
                <button type="button" onclick="closeEditStaffModal()" class="px-5 py-2.5 text-xs font-bold text-slate-600 hover:text-slate-800 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition">
                    বাতিল
                </button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-7 py-2.5 rounded-xl font-black text-xs shadow-md shadow-indigo-600/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-check"></i>
                    পরিবর্তন সংরক্ষণ করুন
                </button>
            </div>
        </form>
    </div>
</div>
