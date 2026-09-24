{{-- 🤖 SUBEDITOR24 PREMIUM AI EDITORIAL COPILOT ASSISTANT --}}
<div id="aiCopilotWidget" class="fixed bottom-5 right-5 z-[999] font-sans select-none">
    
    {{-- 1. Floating Toggle Trigger Button --}}
    <button type="button" id="aiCopilotToggleBtn" onclick="toggleAiCopilot()" 
            class="group relative flex items-center gap-2.5 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 hover:from-indigo-500 hover:to-pink-500 text-white p-3.5 sm:px-4 sm:py-3 rounded-full shadow-2xl transition-all duration-300 transform hover:scale-105 cursor-pointer border-2 border-white/20">
        <span class="absolute -top-1 -right-1 flex h-3.5 w-3.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 border-2 border-white"></span>
        </span>
        <div class="w-6 h-6 flex items-center justify-center text-lg">
            <i class="fa-solid fa-wand-magic-sparkles animate-pulse"></i>
        </div>
        <span class="hidden sm:inline font-bold text-xs tracking-wide">AI Assistant</span>
    </button>

    {{-- 2. Floating Chat Window / Modal --}}
    <div id="aiCopilotWindow" class="hidden flex-col fixed bottom-20 right-4 sm:right-6 w-[calc(100vw-2rem)] sm:w-[440px] max-h-[85vh] h-[640px] bg-slate-900/95 border border-slate-700/80 rounded-3xl shadow-2xl overflow-hidden backdrop-blur-2xl transition-all duration-300 z-[1000]">
        
        {{-- Header --}}
        <div class="p-4 bg-slate-850/90 border-b border-slate-700/80 flex items-center justify-between text-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-user-pen text-base"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-extrabold text-sm text-white tracking-tight">সহকারী সম্পাদক</h3>
                        <span class="text-[9px] bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Subeditor24</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-semibold mt-0.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span id="copilotActiveModeText">লাইভ ডেস্ক সহকারী</span>
                    </div>
                </div>
            </div>
            
            {{-- Header Actions --}}
            <div class="flex items-center gap-1.5 text-slate-400">
                <button type="button" onclick="toggleCopilotExpand()" id="copilotExpandBtn" class="p-1.5 hover:text-white hover:bg-slate-800 rounded-lg transition text-xs cursor-pointer" title="Expand View">
                    <i class="fa-solid fa-expand"></i>
                </button>
                <button type="button" onclick="clearCopilotChat()" class="p-1.5 hover:text-white hover:bg-slate-800 rounded-lg transition text-xs cursor-pointer" title="নতুন চ্যাট শুরু করুন">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
                <button type="button" onclick="toggleAiCopilot()" class="p-1.5 hover:text-white hover:bg-slate-800 rounded-lg transition text-xs cursor-pointer" title="বন্ধ করুন">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        {{-- Active Page Location Banner --}}
        <div id="copilotLocationBanner" class="px-3.5 py-2 bg-slate-950/90 border-b border-slate-800 flex items-center justify-between text-[11px]">
            <div class="flex items-center gap-1.5 text-indigo-300 font-semibold truncate">
                <i class="fa-solid fa-location-dot text-indigo-400 text-xs"></i>
                <span id="copilotPageLocationLabel">লোড হচ্ছে...</span>
            </div>
            <span class="text-[10px] text-slate-500 shrink-0 font-medium">Auto-detected Desk</span>
        </div>

        {{-- Dynamic Scenario Chips (Injected based on active page) --}}
        <div id="copilotDynamicChips" class="p-2.5 bg-slate-950/70 border-b border-slate-800 flex gap-2 overflow-x-auto custom-scrollbar text-[11px] whitespace-nowrap">
            {{-- Dynamic chips populated via JS --}}
        </div>

        {{-- Toast Feedback Notification --}}
        <div id="copilotToast" class="hidden absolute top-28 left-1/2 -translate-x-1/2 z-[1010] px-4 py-2 bg-emerald-600 text-white font-bold text-xs rounded-full shadow-lg items-center gap-2 animate-bounce">
            <i class="fa-solid fa-circle-check"></i> <span id="copilotToastText">এডিটরে যুক্ত করা হয়েছে!</span>
        </div>

        {{-- Message History Container --}}
        <div id="copilotMessages" class="flex-1 p-4 overflow-y-auto space-y-4 text-xs text-slate-200 custom-scrollbar bg-slate-900/90 font-bangla">
            {{-- Welcome Message Populated Dynamically via JS --}}
        </div>

        {{-- Typing Indicator --}}
        <div id="copilotTyping" class="hidden px-5 py-2.5 bg-slate-900 flex items-center gap-2.5 text-slate-400 text-[11px] font-bangla border-t border-slate-800/60">
            <div class="flex gap-1.5">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce"></span>
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-bounce [animation-delay:0.4s]"></span>
            </div>
            <span class="text-indigo-300 font-medium">সহকারী চিন্তা করছেন ও লিখছেন...</span>
        </div>

        {{-- Input Area --}}
        <div class="p-3.5 bg-slate-850/95 border-t border-slate-700/80">
            <form onsubmit="handleCopilotSubmit(event)" class="flex items-end gap-2.5">
                <div class="flex-1 bg-slate-950/90 border border-slate-700/90 rounded-2xl px-4 py-2.5 focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20 transition shadow-inner">
                    <textarea id="copilotInput" rows="1" placeholder="এখানে বাংলায় বার্তা লিখুন..." 
                              class="w-full bg-transparent border-0 p-0 text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-0 resize-none max-h-28 custom-scrollbar font-bangla leading-relaxed"
                              onkeydown="handleCopilotKeydown(event)" oninput="autoGrowCopilotTextarea(this)"></textarea>
                </div>
                <button type="submit" id="copilotSendBtn" 
                        class="w-11 h-11 rounded-2xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white flex items-center justify-center transition shadow-lg shadow-indigo-600/30 shrink-0 cursor-pointer disabled:opacity-50">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                </button>
            </form>
        </div>

    </div>
</div>

<script>
    let copilotChatHistory = [];
    let isCopilotExpanded = false;

    // 📋 Page Registry & Scenarios
    const pageScenarios = {
        settings: {
            name: 'সেটিংস ও ইন্টিগ্রেশন ডেস্ক',
            badge: '⚙️ Settings Desk',
            welcome: 'আপনি এখন <strong>সেটিংস পেজে</strong> আছেন। ওয়েবসাইট API টোকেন, cPanel .htaccess, ক্যাটাগরি ম্যাপিং বা ফেসবুক পেজ কানেকশনে কোনো সমস্যা হলে আমাকে বলুন।',
            chips: [
                { label: '🔌 কানেকশন ফেইল কেন হচ্ছে?', prompt: 'আমার ওয়েবসাইটে টেস্ট কানেকশন ফেইল করছে, কী কী চেক করব?' },
                { label: '🔑 API Token কোথায় বসাব?', prompt: 'লারাভেল .env ও routes/api.php ফাইলে কীভাবে সিক্রেট টোকেন সেট করতে হয়?' },
                { label: '🔄 ক্যাটাগরি কীভাবে রিফ্রেশ করব?', prompt: 'ক্যাটাগরি রিফ্রেশ ও ম্যাপিং কীভাবে করতে হয়?' },
                { label: '📘 এই পেজের সব ফিচার গাইড', prompt: 'সেটিংস পেজের সব ফিচার ব্যবহারের নিয়ম বুঝিয়ে দাও।' }
            ]
        },
        news_create: {
            name: 'নিউজ এডিটর ও এসইও ডেস্ক',
            badge: '✍️ News Editor Desk',
            welcome: 'আপনি এখন <strong>নিউজ ক্রিয়েট/এডিটর পেজে</strong> আছেন। আপনার খসড়া লেখার হেডলাইন অপশন, ফোকাস কিওয়ার্ড নির্ধারণ বা প্রফেশনাল রিরাইটের জন্য আমাকে নির্দেশ দিন।',
            chips: [
                { label: '🎯 Focus Keyword সাজেস্ট করো', prompt: 'এই নিউজের জন্য সেরা Focus Keyword এবং এসইও স্কোর অপ্টিমাইজেশন টিপস দাও।' },
                { label: '🏷️ ৩টি আকর্ষণীয় শিরোনাম', prompt: 'নিউজটির জন্য ৩টি আকর্ষণীয় শিরোনাম (ব্রেকিং, এসইও ও ব্যাখ্যামূলক) সাজেস্ট করো।' },
                { label: '✍️ সাংবাদিকের ভাষায় রিরাইট', prompt: 'নিউজটি প্রফেশনাল ও মার্জিত সাংবাদিকতার ভাষায় রিরাইট করো।' },
                { label: '💡 এই পেজের সব ফিচার গাইড', prompt: 'নিউজ এডিটর পেজের ফিচার ও অপশনগুলো কীভাবে ব্যবহার করব?' }
            ]
        },
        news_drafts: {
            name: 'ড্রাফট ও এআই রিভিউ ডেস্ক',
            badge: '📂 Drafts Desk',
            welcome: 'আপনি এখন <strong>ড্রাফট ও এআই প্রসেসড নিউজ পেজে</strong> আছেন। এখানে প্রস্তুত হওয়া এআই নিউজগুলো রিভিউ, এডিট ও ওয়েবসাইটে পাবলিশ করতে পারবেন।',
            chips: [
                { label: '🚀 ড্রাফট কীভাবে পাবলিশ করব?', prompt: 'ড্রাফট নিউজগুলো কীভাবে এডিট বা সরাসরি ওয়েবসাইটে পাবলিশ করতে হয়?' },
                { label: '🔍 ফ্যাক্ট-চেক কীভাবে করব?', prompt: 'ড্রাফট নিউজের ফ্যাক্ট-চেক ও নির্ভুলতা কীভাবে যাচাই করব?' },
                { label: '💡 এই পেজের সব ফিচার গাইড', prompt: 'ড্রাফটস পেজের ফিচার ও ওয়ার্কফ্লো বুঝিয়ে দাও।' }
            ]
        },
        trending: {
            name: 'ভাইরাল ট্রেন্ড ও প্রেডিকশন ডেস্ক',
            badge: '🔥 Trends Desk',
            welcome: 'আপনি এখন <strong>ভাইরাল ট্রেন্ডস পেজে</strong> আছেন। সোশ্যাল মিডিয়ায় চলমান ট্রেন্ডিং খবর ও ভিডিও স্ক্রিপ্ট তৈরিতে আমি আপনাকে সাহায্য করতে পারি।',
            chips: [
                { label: '🔥 আজকের সেরা ট্রেন্ড কী?', prompt: 'আজকের ভাইরাল ট্রেন্ডগুলো কীভাবে নিউজ কাভারেজে ব্যবহার করব?' },
                { label: '🎬 ভিডিও স্ক্রিপ্ট কীভাবে বানাব?', prompt: 'ট্রেন্ডিং বিষয় থেকে ভিডিও স্ক্রিপ্ট তৈরির নিয়ম কী?' },
                { label: '💡 এই পেজের সব ফিচার গাইড', prompt: 'ট্রেন্ডিং ও ভাইরাল প্রেডিকশন পেজের ফিচার কীভাবে কাজ করে?' }
            ]
        },
        free_photocard: {
            name: 'ফ্রি ফটো কার্ড জেনারেটর',
            badge: '🎨 Photo Card Desk',
            welcome: 'আপনি এখন <strong>ফ্রি ফটো কার্ড পেজে</strong> আছেন। যেকোনো নিউজ লিংক থেকে সোশ্যাল মিডিয়া কার্ড তৈরি ও ডাউনলোড করতে পারেন।',
            chips: [
                { label: '🖼️ ফটো কার্ড কীভাবে বানাব?', prompt: 'যেকোনো নিউজ লিংক থেকে ফটো কার্ড তৈরি করার নিয়ম কী?' },
                { label: '📐 ফ্রেম ও লোগো পরিবর্তন', prompt: 'ফটো কার্ডে ফ্রেম ও লোগো কীভাবে কাস্টমাইজ করব?' },
                { label: '💡 এই পেজের সব ফিচার গাইড', prompt: 'ফ্রি ফটো কার্ড পেজের সব ফিচার বুঝিয়ে দাও।' }
            ]
        },
        news_feed: {
            name: 'সেন্ট্রাল নিউজ ফিড ও স্ক্র্যাপার',
            badge: '📰 Live News Feed',
            welcome: 'আপনি এখন <strong>সেন্ট্রাল নিউজ ফিডে</strong> আছেন। এখানে স্বয়ংক্রিয়ভাবে বিভিন্ন জাতীয় পোর্টাল থেকে লাইভ নিউজ পর্যবেক্ষণ ও স্ক্র্যাপ হচ্ছে।',
            chips: [
                { label: '🔍 নিউজ কীভাবে স্ক্র্যাপ হয়?', prompt: 'সেন্ট্রাল ফিডে কীভাবে স্বয়ংক্রিয়ভাবে নিউজ স্ক্র্যাপ ও পর্যবেক্ষণ হয়?' },
                { label: '⚡ ১-ক্লিকে AI প্রসেসিং কীভাবে?', prompt: 'ফিড থেকে কোনো নিউজকে ১-ক্লিকে কীভাবে AI রিরাইট কিউতে পাঠাব?' },
                { label: '💡 এই পেজের সব ফিচার গাইড', prompt: 'নিউজ ফিড পেজের সব ফিচার ও ফিল্টারিং কীভাবে কাজ করে?' }
            ]
        },
        general: {
            name: 'ডিজিটাল নিউজরুম ড্যাশবোর্ড',
            badge: '🌐 Global Newsroom',
            welcome: 'স্বাগতম! আমি Subeditor24 AI Copilot। প্ল্যাটফর্মের যেকোনো ফিচার কীভাবে ব্যবহার করবেন কিংবা সম্পাদকীয় বিষয়ে জানতে নিচে লিখুন।',
            chips: [
                { label: '🚀 পুরো প্রজেক্টের ফিচার গাইড', prompt: 'Subeditor24 এর সব ফিচার ও ওয়ার্কফ্লো বিস্তারিত বুঝিয়ে দাও।' },
                { label: '✍️ নিউজ ক্রিয়েট করতে চাই', prompt: 'আমি একটি নতুন নিউজ লিখতে চাই, কীভাবে শুরু করব?' },
                { label: '🔌 ওয়েবসাইট কানেক্ট করতে চাই', prompt: 'আমার ওয়েবসাইট কীভাবে Subeditor24 এর সাথে কানেক্ট করব?' }
            ]
        }
    };

    function detectCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('settings')) return 'settings';
        if (path.includes('news/create') || path.includes('reporter/news/create') || path.includes('edit')) return 'news_create';
        if (path.includes('news/drafts')) return 'news_drafts';
        if (path.includes('trending')) return 'trending';
        if (path.includes('free-photocard')) return 'free_photocard';
        if (path.includes('news')) return 'news_feed';
        return 'general';
    }

    function toggleAiCopilot() {
        const win = document.getElementById('aiCopilotWindow');
        const input = document.getElementById('copilotInput');
        if (win.classList.contains('hidden')) {
            win.classList.remove('hidden');
            win.classList.add('flex');
            renderActivePageEnvironment();
            if (input) setTimeout(() => input.focus(), 150);
        } else {
            win.classList.add('hidden');
            win.classList.remove('flex');
        }
    }

    function renderActivePageEnvironment() {
        const pageKey = detectCurrentPage();
        const scenario = pageScenarios[pageKey] || pageScenarios.general;

        // 1. Update Header & Location Labels
        document.getElementById('copilotActiveModeText').innerText = scenario.badge;
        document.getElementById('copilotPageLocationLabel').innerHTML = `📍 ডেস্ক: <strong>${scenario.name}</strong>`;

        // 2. Render Dynamic Chips
        const chipsContainer = document.getElementById('copilotDynamicChips');
        chipsContainer.innerHTML = scenario.chips.map(c => `
            <button type="button" onclick="sendQuickPrompt('${escapeHtml(c.prompt)}')" class="bg-slate-850 hover:bg-slate-800 text-indigo-300 hover:text-white px-3 py-1.5 rounded-full border border-slate-700/80 transition flex items-center gap-1.5 cursor-pointer shadow-xs">
                ${c.label}
            </button>
        `).join('');

        // 3. Render Welcome message if chat history is empty
        if (copilotChatHistory.length === 0) {
            const container = document.getElementById('copilotMessages');
            container.innerHTML = `
                <div class="flex items-start gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-600/30 to-purple-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400 text-sm shrink-0 mt-0.5 shadow-sm">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div class="bg-slate-850 border border-slate-700/80 p-4 rounded-2xl rounded-tl-none leading-relaxed text-slate-200 space-y-2.5 max-w-[90%] shadow-md">
                        <div class="flex items-center justify-between border-b border-slate-750 pb-2">
                            <span class="font-bold text-white text-xs">আসসালামু আলাইকুম! 👋</span>
                            <span class="text-[10px] text-indigo-300 font-semibold">${scenario.badge}</span>
                        </div>
                        <p class="text-slate-300 text-[11.5px] leading-relaxed">
                            ${scenario.welcome}
                        </p>
                        <div class="p-2.5 rounded-xl bg-slate-950/60 border border-slate-800 text-[11px] text-indigo-300 font-semibold space-y-1">
                            <p>💡 <strong>এই ডেস্কের যেকোনো সহায়তা চান?</strong></p>
                            <p class="text-slate-400 font-normal">উপরের বাটনগুলোতে ক্লিক করুন অথবা নিচে আপনার প্রশ্ন লিখে জানান।</p>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    function toggleCopilotExpand() {
        const win = document.getElementById('aiCopilotWindow');
        const btn = document.getElementById('copilotExpandBtn');
        isCopilotExpanded = !isCopilotExpanded;

        if (isCopilotExpanded) {
            win.classList.remove('sm:w-[440px]', 'h-[640px]');
            win.classList.add('sm:w-[820px]', 'h-[85vh]', 'sm:right-12');
            btn.innerHTML = '<i class="fa-solid fa-compress"></i>';
            btn.title = "Normal View";
        } else {
            win.classList.remove('sm:w-[820px]', 'h-[85vh]', 'sm:right-12');
            win.classList.add('sm:w-[440px]', 'h-[640px]');
            btn.innerHTML = '<i class="fa-solid fa-expand"></i>';
            btn.title = "Expand View";
        }
    }

    function autoGrowCopilotTextarea(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 112) + 'px';
    }

    function handleCopilotKeydown(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            handleCopilotSubmit(e);
        }
    }

    function sendQuickPrompt(promptText) {
        const input = document.getElementById('copilotInput');
        input.value = promptText;
        handleCopilotSubmit(new Event('submit'));
    }

    function clearCopilotChat() {
        copilotChatHistory = [];
        renderActivePageEnvironment();
    }

    function showCopilotToast(msg) {
        const toast = document.getElementById('copilotToast');
        const text = document.getElementById('copilotToastText');
        if (!toast) return;
        text.innerText = msg;
        toast.classList.remove('hidden');
        toast.classList.add('flex');
        setTimeout(() => {
            toast.classList.add('hidden');
            toast.classList.remove('flex');
        }, 2200);
    }

    // 🚀 Direct In-Editor Injections
    function insertHeadlineDirectly(headlineText) {
        const titleInput = document.getElementById('newsTitleInput') || document.querySelector('input[name="title"]');
        if (titleInput) {
            titleInput.value = headlineText.trim().replace(/^["']|["']$/g, '');
            titleInput.dispatchEvent(new Event('input', { bubbles: true }));
            showCopilotToast('✅ শিরোনাম এডিটরে বসানো হয়েছে!');
        } else {
            navigator.clipboard.writeText(headlineText);
            showCopilotToast('📋 শিরোনাম কপি করা হয়েছে!');
        }
    }

    function insertContentDirectly(rawText) {
        if (typeof tinymce !== 'undefined' && tinymce.activeEditor) {
            tinymce.activeEditor.setContent(rawText);
            showCopilotToast('✅ নিউজ টেক্সট এডিটরে বসানো হয়েছে!');
            return;
        }

        const textarea = document.querySelector('textarea[name="content"], textarea[name="body"], #content, #editor');
        if (textarea) {
            textarea.value = rawText;
            textarea.dispatchEvent(new Event('input', { bubbles: true }));
            showCopilotToast('✅ নিউজ টেক্সট এডিটরে বসানো হয়েছে!');
            return;
        }

        navigator.clipboard.writeText(rawText);
        showCopilotToast('📋 টেক্সট কপি করা হয়েছে!');
    }

    function handleCopilotSubmit(e) {
        if (e) e.preventDefault();
        const input = document.getElementById('copilotInput');
        const msg = input.value.trim();
        if (!msg) return;

        appendUserMessage(msg);
        copilotChatHistory.push({ role: 'user', content: msg });
        if (copilotChatHistory.length > 12) copilotChatHistory.shift();

        input.value = '';
        input.style.height = 'auto';

        const typing = document.getElementById('copilotTyping');
        const sendBtn = document.getElementById('copilotSendBtn');
        typing.classList.remove('hidden');
        sendBtn.disabled = true;

        const pageKey = detectCurrentPage();
        const scenario = pageScenarios[pageKey] || pageScenarios.general;

        // Context extraction
        const context = {
            page_key: pageKey,
            page_name: scenario.name,
            page_url: window.location.href,
            article_title: document.querySelector('input[name="title"]')?.value || '',
            article_content: (typeof tinymce !== 'undefined' && tinymce.activeEditor) ? tinymce.activeEditor.getContent({format: 'text'}) : (document.querySelector('textarea[name="content"], textarea[name="body"], #content, #editor')?.value || ''),
            error_context: document.getElementById('custom_api_status_box')?.innerText || document.getElementById('wp_status_msg')?.innerText || ''
        };

        fetch("{{ route('ai-assistant.chat') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                message: msg, 
                context: context,
                history: copilotChatHistory 
            })
        })
        .then(res => res.json())
        .then(data => {
            typing.classList.add('hidden');
            sendBtn.disabled = false;
            if (data.reply) {
                copilotChatHistory.push({ role: 'assistant', content: data.reply });
                if (copilotChatHistory.length > 12) copilotChatHistory.shift();
                appendAssistantMessage(data.reply, data.provider);
            } else {
                appendAssistantMessage('দুঃখিত, কোনো উত্তর পাওয়া যায়নি। একটু পরে আবার চেষ্টা করবেন কি?');
            }
        })
        .catch(err => {
            typing.classList.add('hidden');
            sendBtn.disabled = false;
            appendAssistantMessage('❌ সংযোগ ত্রুটি হয়েছে: ' + err.message);
        });
    }

    function appendUserMessage(text) {
        const container = document.getElementById('copilotMessages');
        const div = document.createElement('div');
        div.className = 'flex justify-end';
        div.innerHTML = `
            <div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white p-3.5 rounded-2xl rounded-tr-none text-xs max-w-[85%] leading-relaxed shadow-md">
                ${escapeHtml(text).replace(/\n/g, '<br>')}
            </div>
        `;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function appendAssistantMessage(rawMarkdown, provider = '') {
        const container = document.getElementById('copilotMessages');
        const div = document.createElement('div');
        div.className = 'flex items-start gap-2.5';

        const formattedHtml = formatMarkdownForCopilot(rawMarkdown);

        div.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-indigo-600/30 to-purple-600/30 border border-indigo-500/40 flex items-center justify-center text-indigo-400 text-sm shrink-0 mt-0.5 shadow-sm">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div class="bg-slate-850 border border-slate-700/80 p-4 rounded-2xl rounded-tl-none text-slate-200 text-xs max-w-[90%] leading-relaxed shadow-md space-y-2.5">
                <div class="flex items-center justify-between border-b border-slate-750 pb-1.5">
                    <span class="text-[9px] font-bold uppercase text-indigo-400 tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-bolt text-indigo-500"></i> ${provider ? provider.toUpperCase() : 'AI'} ASSISTANT
                    </span>
                    <span class="text-[10px] text-slate-400 font-sans">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                </div>
                <div class="copilot-rendered-content text-[11.8px] leading-relaxed space-y-2">${formattedHtml}</div>
            </div>
        `;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    function formatMarkdownForCopilot(md) {
        if (!md) return '';
        let html = md;
        
        // 1. Code blocks with copy button
        html = html.replace(/```([a-zA-Z0-9_\-]*)\n([\s\S]*?)```/g, function(match, lang, code) {
            const codeId = 'copilot_code_' + Math.random().toString(36).substring(2, 9);
            return `
                <div class="my-2.5 rounded-xl overflow-hidden border border-slate-700/90 bg-slate-950 shadow-sm">
                    <div class="flex justify-between items-center px-3.5 py-1.5 bg-slate-900 border-b border-slate-800 text-[10px] text-slate-400 font-sans font-bold">
                        <span class="font-mono uppercase text-indigo-400">${lang || 'code'}</span>
                        <button type="button" onclick="copyDiagSnippet('${codeId}', this)" class="hover:text-white transition flex items-center gap-1 cursor-pointer bg-slate-800 px-2 py-0.5 rounded">
                            <i class="fa-regular fa-copy"></i> Copy
                        </button>
                    </div>
                    <pre id="${codeId}" class="p-3.5 text-[11px] font-mono text-emerald-400 overflow-x-auto whitespace-pre leading-snug">${escapeHtml(code.trim())}</pre>
                </div>
            `;
        });

        // 2. Clickable Links / Page Redirections [Text](/url)
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, function(match, text, url) {
            return `<a href="${url}" class="inline-flex items-center gap-1 text-indigo-400 hover:text-indigo-300 font-bold underline bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20 transition">${text} <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i></a>`;
        });

        // 3. Inline code
        html = html.replace(/`([^`]+)`/g, '<code class="bg-slate-950 text-indigo-300 px-1.5 py-0.5 rounded text-[11px] font-mono border border-slate-800">$1</code>');
        
        // 4. Bold
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong class="text-white font-bold">$1</strong>');
        
        // 5. Headers
        html = html.replace(/^### (.*$)/gim, '<h4 class="font-bold text-white text-xs mt-2.5 mb-1 pb-0.5 border-b border-slate-750">$1</h4>');
        html = html.replace(/^## (.*$)/gim, '<h3 class="font-extrabold text-white text-sm mt-3 mb-1 pb-0.5 border-b border-slate-750">$1</h3>');

        // 6. Bullet lists
        html = html.replace(/^\* (.*$)/gim, '<div class="flex items-start gap-2 pl-2 my-0.5"><span class="text-indigo-400 font-bold">•</span><span class="flex-1">$1</span></div>');
        html = html.replace(/^- (.*$)/gim, '<div class="flex items-start gap-2 pl-2 my-0.5"><span class="text-indigo-400 font-bold">•</span><span class="flex-1">$1</span></div>');

        // 7. Numbered lists
        html = html.replace(/^(\d+)\. (.*$)/gim, '<div class="flex items-start gap-2 pl-2 my-0.5"><span class="text-indigo-400 font-bold font-sans text-[11px]">$1.</span><span class="flex-1">$2</span></div>');

        // 8. Line breaks
        html = html.replace(/\n\n/g, '<div class="h-2"></div>');
        html = html.replace(/\n/g, '<br>');

        return html;
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
