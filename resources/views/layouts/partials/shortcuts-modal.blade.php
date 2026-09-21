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
                    <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Keyboard Shortcuts (Power User Shortcuts)</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">List of keyboard commands for quick navigation & actions</p>
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
                    <i class="fa-solid fa-arrows-up-down-left-right text-xs"></i> News Feed & Card Navigation
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Navigate to Next Card</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">J</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Navigate to Previous Card</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">K</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Toggle Card Selection</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">X</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Cancel Selection Focus</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Esc</kbd>
                    </div>
                </div>
            </div>

            {{-- Category 2: Draft Editor & AI Power Shortcuts --}}
            <div>
                <h4 class="text-xs font-black text-purple-600 dark:text-purple-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-wand-magic-sparkles text-xs"></i> Draft Editor & AI Power Shortcuts
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">🔍 Real-time Fact Check</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Alt</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">F</kbd>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">✨ 3 AI Viral Headlines</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Alt</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">H</kbd>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">🎯 SEO Focus Keywords & Meta</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Alt</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">K</kbd>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">🔀 Switch View Mode (Editor/Side/Social)</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Alt</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-1.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">1/2/3</kbd>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">🚀 Direct Publish / Submit</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Ctrl</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Enter</kbd>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">💾 Save Draft</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Ctrl</kbd>
                            <span class="text-slate-400 font-black">+</span>
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">S</kbd>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Category 3: Quick Actions & Studio --}}
            <div>
                <h4 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-bolt text-xs"></i> Quick Actions & Studio
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">AI Rewrite Selected News</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">R</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Manual News Edit / Studio</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">E</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Studio Object Undo / Redo</span>
                        <div class="flex items-center gap-1">
                            <kbd class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Ctrl+Z / Y</kbd>
                        </div>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Delete Studio Object</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">Del / Backspace</kbd>
                    </div>
                </div>
            </div>

            {{-- Category 4: System & View --}}
            <div>
                <h4 class="text-xs font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-xs"></i> System & Utility
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Keyboard Shortcuts Help</span>
                        <kbd class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 font-mono font-black text-slate-800 dark:text-slate-200 shadow-sm text-xs">?</kbd>
                    </div>
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-800">
                        <span class="font-bold text-slate-700 dark:text-slate-300">Toggle Dark / Light Mode</span>
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
            <span>💡 Shortcuts are disabled while typing inside input or textarea fields.</span>
            <button type="button" onclick="closeShortcutsModal()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition shadow-sm cursor-pointer">
                Got It
            </button>
        </div>

    </div>
</div>
