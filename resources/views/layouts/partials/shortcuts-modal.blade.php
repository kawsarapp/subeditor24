{{-- ⌨️ Keyboard Shortcuts Cheat-sheet Modal --}}
<div id="shortcutsModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto bg-slate-900/80 dark:bg-slate-950/90 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-slate-200 dark:border-slate-800 w-full max-w-2xl overflow-hidden transition-all transform animate-in fade-in zoom-in-95 duration-200">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-600/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg font-bold">
                    <i class="fa-solid fa-keyboard"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">কিবোর্ড শর্টকাটস (Power User Shortcuts)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">দ্রুত কাজ সম্পাদনের জন্য কীবোর্ড কমান্ড তালিকা</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-slate-400 dark:text-slate-500 bg-slate-200/60 dark:bg-slate-800 px-2.5 py-1 rounded-lg border border-slate-300/40 dark:border-slate-700">ESC to close</span>
                <button type="button" onclick="closeShortcutsModal()" class="w-8 h-8 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-center transition cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Content Body --}}
        <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
            
            {{-- Category 1: News Navigation --}}
            <div>
                <h4 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-arrows-up-down-left-right text-xs"></i> নিউজ ফিড ও কার্ড নেভিগেশন
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">পরবর্তী নিউজ কার্ডে যাওয়া</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">J</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">পূর্ববর্তী নিউজ কার্ডে যাওয়া</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">K</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">কার্ড নির্বাচন / সিলেক্ট টগল</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">X</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">সিলেকশন ফোকাস বাতিল</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Esc</kbd>
                    </div>
                </div>
            </div>

            {{-- Category 2: Quick Actions --}}
            <div>
                <h4 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-xs"></i> দ্রুত অ্যাকশন ও এডিটিং
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">সিলেক্টেড নিউজ AI Rewrite</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">R</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">নিউজ ম্যানুয়াল এডিট / স্টুডিও</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">E</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">ড্রাফট পাবলিশ / সাবমিট</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Ctrl</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Enter</kbd>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">ফরম / সেটিংস সেভ করুন</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Ctrl</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">S</kbd>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category 3: System & View --}}
            <div>
                <h4 class="text-xs font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-xs"></i> সিস্টেম ও ইউটিলিটি
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">কিবোর্ড শর্টকাট হেল্প</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">?</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">ডার্ক / লাইট মোড টগল</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Alt</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">D</kbd>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/50 flex justify-between items-center text-xs text-slate-500 dark:text-slate-400 font-semibold">
            <span>💡 যেকোনো ইনপুট ফিল্ডে লেখার সময় নেভিগেশন শর্টকাট নিষ্ক্রিয় থাকে।</span>
            <button type="button" onclick="closeShortcutsModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm cursor-pointer">
                বুঝেছি (Got It)
            </button>
        </div>

    </div>
</div>
