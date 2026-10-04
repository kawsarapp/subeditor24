@auth
    @if(auth()->user()->role !== 'super_admin')
        @php
            $u = auth()->user();
            $effectiveUser = $u->parent_id ? ($u->parent ?? $u) : $u;
            $isExp = $effectiveUser->isExpired();
            $isSoon = $effectiveUser->isExpiringSoon(3);
            $daysLeft = $effectiveUser->days_remaining;
            $isExemptRoute = request()->routeIs('billing.*') || request()->routeIs('pricing.*');
        @endphp

        @if(!$isExemptRoute && ($isExp || $isSoon))
            <div class="mb-6">
                @if($isExp)
                    {{-- 🚨 EXPIRED BANNER --}}
                    <div class="bg-gradient-to-r from-rose-600 via-red-600 to-rose-700 text-white rounded-2xl p-4 sm:p-5 shadow-xl border border-rose-400/40 flex flex-col md:flex-row items-center justify-between gap-4 animate-pulse">
                        <div class="flex items-center gap-3.5">
                            <div class="w-11 h-11 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 shadow-inner">
                                <i class="fa-solid fa-triangle-exclamation text-2xl text-amber-300"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-black text-sm sm:text-base tracking-tight">আপনার প্যাকেজের মেয়াদ শেষ হয়েছে! (Plan Expired)</h4>
                                    <span class="bg-white/25 text-[10px] uppercase font-black px-2 py-0.5 rounded-full">Inactive</span>
                                </div>
                                <p class="text-xs text-rose-100 mt-1 leading-relaxed">
                                    নিউজ পাবলিশিং ও এআই অটোমেশন সুবিধা সাময়িকভাবে বন্ধ রয়েছে। সকল প্রিমিয়াম ফিচার পুনরায় সক্রিয় করতে এখনই প্যাকেজ রিনিউ করুন।
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0 w-full md:w-auto justify-end">
                            <a href="{{ route('pricing.index') }}" class="w-full md:w-auto text-center px-5 py-2.5 bg-white text-rose-700 hover:bg-rose-50 font-black text-xs rounded-xl shadow-lg transition-transform hover:scale-105 active:scale-95">
                                🚀 এখনই রিনিউ বা আপগ্রেড করুন
                            </a>
                        </div>
                    </div>
                @elseif($isSoon)
                    {{-- ⚠️ EXPIRING SOON BANNER (<= 3 Days) --}}
                    <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 text-white rounded-2xl p-4 sm:p-4.5 shadow-lg border border-amber-300/40 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center shrink-0 shadow-inner">
                                <i class="fa-solid fa-hourglass-half text-xl text-yellow-200 animate-spin" style="animation-duration: 4s;"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="font-black text-sm sm:text-base">সাবস্ক্রিপশনের মেয়াদ আর মাত্র <span class="bg-white/30 text-white px-2 py-0.5 rounded-lg font-black underline decoration-white/40">{{ $daysLeft }} দিন</span> বাকি!</h4>
                                </div>
                                <p class="text-xs text-amber-100 mt-0.5 leading-relaxed">
                                    নিরবচ্ছিন্ন কাজের সুবিধার্থে মেয়াদ শেষ হওয়ার আগেই আপনার পছন্দের প্যাকেজ রিচার্জ বা এক্সটেন্ড করে নিন।
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0 w-full md:w-auto justify-end">
                            <a href="{{ route('pricing.index') }}" class="w-full md:w-auto text-center px-5 py-2.5 bg-white text-amber-900 hover:bg-amber-50 font-black text-xs rounded-xl shadow transition-transform hover:scale-105 active:scale-95">
                                ⚡ মেয়াদ বাড়িয়ে নিন
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    @endif
@endauth
