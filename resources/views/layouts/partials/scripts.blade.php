@stack('scripts')
<script>
    // 🔥 UPDATED: 3-Dot Toggle Logic (With Smooth Animation)
    const dotBtn = document.getElementById('dotMenuBtn');
    const dotDropdown = document.getElementById('dotDropdown');
    
    if(dotBtn && dotDropdown) {
        dotBtn.addEventListener('click', (e) => { 
            e.stopPropagation(); 
            
            if (dotDropdown.classList.contains('hidden')) {
                // Open Animation
                dotDropdown.classList.remove('hidden');
                // Allow a tiny delay so display block renders before transition
                setTimeout(() => {
                    dotDropdown.classList.remove('opacity-0', 'scale-95');
                    dotDropdown.classList.add('opacity-100', 'scale-100');
                }, 10);
            } else {
                // Close Animation
                dotDropdown.classList.remove('opacity-100', 'scale-100');
                dotDropdown.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    dotDropdown.classList.add('hidden');
                }, 200); // Matches the duration-200 in CSS
            }
        });

        // Click outside to close smoothly
        document.addEventListener('click', (e) => { 
            if (!dotBtn.contains(e.target) && !dotDropdown.contains(e.target) && !dotDropdown.classList.contains('hidden')) {
                dotDropdown.classList.remove('opacity-100', 'scale-100');
                dotDropdown.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    dotDropdown.classList.add('hidden');
                }, 200);
            }
        });
    }

    // Mobile Menu Toggle Logic
    const mobileContainer = document.getElementById('mobileMenuContainer');
    const mobileOverlay = document.getElementById('mobileOverlay');
    const mobileSheet = document.getElementById('mobileMenuSheet');
    const mobileBtn = document.getElementById('mobileMenuBtn');
    let isMenuOpen = false;

    function toggleMobileMenu() {
        isMenuOpen = !isMenuOpen;
        if (isMenuOpen) {
            mobileContainer.classList.remove('hidden');
            setTimeout(() => { 
                mobileOverlay.classList.remove('opacity-0'); 
                mobileSheet.classList.remove('translate-y-full'); 
            }, 10);
            document.body.style.overflow = 'hidden';
        } else {
            mobileOverlay.classList.add('opacity-0');
            mobileSheet.classList.add('translate-y-full');
            setTimeout(() => { mobileContainer.classList.add('hidden'); }, 300);
            document.body.style.overflow = '';
        }
    }
    
    if(mobileBtn) mobileBtn.addEventListener('click', (e) => { 
        e.stopPropagation(); 
        toggleMobileMenu(); 
    });

    // Auto hide flash messages with smooth fade out
    setTimeout(() => {
        const success = document.getElementById('flash-success');
        const error = document.getElementById('flash-error');
        const warning = document.getElementById('flash-warning');
        const validation = document.getElementById('flash-validation');
        [success, error, warning, validation].forEach(el => {
            if(el) {
                el.style.transition = "opacity 0.4s ease, transform 0.4s ease";
                el.style.opacity = "0";
                el.style.transform = "translateX(20px)";
                setTimeout(() => el.remove(), 400);
            }
        });
    }, 4500);

    function markRead() { fetch('{{ route("notifications.read") }}').catch(e => console.error(e)); }

    // ==========================================================
    // 🌟 GLOBAL USER-FRIENDLY TOAST NOTIFICATION ENGINE
    // ==========================================================
    window.showToast = function(message, type = 'success', duration = 3500) {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const toast = document.createElement('div');
        toast.className = "flash-message pointer-events-auto px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[320px] backdrop-blur-md transition-all duration-300 border " + 
            (type === 'success' ? 'bg-emerald-600 border-emerald-500 text-white' : 
            (type === 'error' ? 'bg-rose-600 border-rose-500 text-white' : 
            (type === 'warning' ? 'bg-amber-500 border-amber-400 text-white' : 'bg-indigo-600 border-indigo-500 text-white')));

        const iconClass = type === 'success' ? 'fa-circle-check text-emerald-200' : 
            (type === 'error' ? 'fa-triangle-exclamation text-rose-200' : 
            (type === 'warning' ? 'fa-circle-exclamation text-amber-200' : 'fa-info-circle text-indigo-200'));

        const titleText = type === 'success' ? 'সফল হয়েছে!' : 
            (type === 'error' ? 'ত্রুটি!' : 
            (type === 'warning' ? 'সতর্কতা!' : 'তথ্য'));

        toast.innerHTML = `
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="fa-solid ${iconClass} text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-extrabold text-xs tracking-wide uppercase opacity-90">${titleText}</h4>
                <p class="text-xs font-semibold mt-0.5">${message}</p>
            </div>
            <button type="button" class="text-white/80 hover:text-white p-1 transition"><i class="fa-solid fa-xmark"></i></button>
        `;

        toast.querySelector('button').onclick = () => {
            toast.style.opacity = "0";
            toast.style.transform = "translateX(20px)";
            setTimeout(() => toast.remove(), 300);
        };

        container.appendChild(toast);

        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.opacity = "0";
                toast.style.transform = "translateX(20px)";
                setTimeout(() => toast.remove(), 300);
            }
        }, duration);
    };

    // ==========================================================
    // 📋 ONE-CLICK COPY TO CLIPBOARD HELPER
    // ==========================================================
    window.copyToClipboard = function(text, successMsg = 'ক্লিপবোর্ডে কপি করা হয়েছে!') {
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                window.showToast(successMsg, 'success');
            }).catch(() => {
                fallbackCopy(text, successMsg);
            });
        } else {
            fallbackCopy(text, successMsg);
        }
    };

    function fallbackCopy(text, successMsg) {
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            window.showToast(successMsg, 'success');
        } catch (err) {
            window.showToast('কপি করতে ব্যর্থ হয়েছে!', 'error');
        }
        document.body.removeChild(textarea);
    }

    // ==========================================================
    // ⌨️ GLOBAL KEYBOARD SHORTCUTS ENGINE
    // ==========================================================
    let focusedCardIndex = -1;

    function getFocusableCards() {
        return Array.from(document.querySelectorAll('.news-feed-card, [data-news-id], .luxe-card'))
            .filter(el => el.offsetParent !== null && !el.classList.contains('hidden'));
    }

    function highlightFocusedCard(index) {
        const cards = getFocusableCards();
        if (cards.length === 0) return;
        
        cards.forEach(c => {
            c.classList.remove('ring-4', 'ring-indigo-500', 'shadow-2xl', 'scale-[1.01]', 'border-indigo-500');
        });

        if (index >= 0 && index < cards.length) {
            const card = cards[index];
            card.classList.add('ring-4', 'ring-indigo-500', 'shadow-2xl', 'scale-[1.01]', 'border-indigo-500');
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function openShortcutsModal() {
        const modal = document.getElementById('shortcutsModal');
        if (modal) modal.classList.remove('hidden');
    }

    function closeShortcutsModal() {
        const modal = document.getElementById('shortcutsModal');
        if (modal) modal.classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        const activeTag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
        const isInput = (activeTag === 'input' || activeTag === 'textarea' || document.activeElement.isContentEditable);

        // '?' pressed outside input -> Open/Close Shortcuts Modal
        if (e.key === '?' && !isInput) {
            e.preventDefault();
            const modal = document.getElementById('shortcutsModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeShortcutsModal();
            } else {
                openShortcutsModal();
            }
            return;
        }

        // 'Escape' pressed -> Close any visible modal & remove card focus
        if (e.key === 'Escape') {
            closeShortcutsModal();
            document.querySelectorAll('.fixed:not(.hidden)').forEach(m => {
                if (m.id && (m.id.includes('Modal') || m.id.includes('modal') || m.id.includes('alert') || m.id.includes('Sheet'))) {
                    m.classList.add('hidden');
                }
            });
            if (focusedCardIndex !== -1) {
                const cards = getFocusableCards();
                cards.forEach(c => c.classList.remove('ring-4', 'ring-indigo-500', 'shadow-2xl', 'scale-[1.01]', 'border-indigo-500'));
                focusedCardIndex = -1;
            }
            return;
        }

        // 'Alt + D' -> Toggle Dark Mode
        if (e.altKey && e.key.toLowerCase() === 'd') {
            e.preventDefault();
            toggleDarkMode();
            return;
        }

        // 'Ctrl + S' or 'Cmd + S' -> Save active form
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
            const form = document.querySelector('form');
            if (form && !e.target.closest('div[contenteditable="true"]')) {
                e.preventDefault();
                window.showToast('সংরক্ষণ করা হচ্ছে...', 'info', 1500);
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.click();
                else form.submit();
            }
            return;
        }

        // 'Ctrl + Enter' or 'Cmd + Enter' -> Quick Publish/Submit in modals or drafts
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            const openModal = document.querySelector('.fixed:not(.hidden)');
            if (openModal) {
                const publishBtn = openModal.querySelector('button[type="submit"], button[onclick*="publish"], button[onclick*="save"]');
                if (publishBtn) {
                    e.preventDefault();
                    publishBtn.click();
                    return;
                }
            }
        }

        // News Navigation shortcuts only when NOT typing in inputs
        if (isInput) return;

        const cards = getFocusableCards();
        if (cards.length === 0) return;

        // 'J' -> Next Card
        if (e.key.toLowerCase() === 'j') {
            e.preventDefault();
            focusedCardIndex = (focusedCardIndex < cards.length - 1) ? focusedCardIndex + 1 : 0;
            highlightFocusedCard(focusedCardIndex);
        }

        // 'K' -> Previous Card
        if (e.key.toLowerCase() === 'k') {
            e.preventDefault();
            focusedCardIndex = (focusedCardIndex > 0) ? focusedCardIndex - 1 : cards.length - 1;
            highlightFocusedCard(focusedCardIndex);
        }

        // 'X' -> Toggle Card Selection Checkbox
        if (e.key.toLowerCase() === 'x' && focusedCardIndex >= 0 && focusedCardIndex < cards.length) {
            e.preventDefault();
            const card = cards[focusedCardIndex];
            const cb = card.querySelector('input[type="checkbox"]');
            if (cb) {
                cb.checked = !cb.checked;
                cb.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        // 'R' -> Trigger AI Rewrite for Focused Card
        if (e.key.toLowerCase() === 'r' && focusedCardIndex >= 0 && focusedCardIndex < cards.length) {
            e.preventDefault();
            const card = cards[focusedCardIndex];
            const rewriteBtn = card.querySelector('button[onclick*="startAiProcess"], button[onclick*="processAi"], button[onclick*="aiRewrite"]');
            if (rewriteBtn) {
                window.showToast('🤖 AI Rewrite শুরু হচ্ছে...', 'info', 1500);
                rewriteBtn.click();
            } else {
                window.showToast('এই কার্ডের জন্য AI Rewrite বাটন পাওয়া যায়নি', 'warning', 2000);
            }
        }

        // 'E' -> Trigger Edit / Studio for Focused Card
        if (e.key.toLowerCase() === 'e' && focusedCardIndex >= 0 && focusedCardIndex < cards.length) {
            e.preventDefault();
            const card = cards[focusedCardIndex];
            const editBtn = card.querySelector('button[onclick*="openManualModal"], button[onclick*="editNews"], a[href*="studio"], a[href*="edit"]');
            if (editBtn) {
                editBtn.click();
            }
        }
    });

    // ==========================================================
    // 🌙 ONE-CLICK DARK MODE TOGGLE ENGINE
    // ==========================================================
    function syncDarkModeIcons() {
        const isDark = document.documentElement.classList.contains('dark');
        document.querySelectorAll('button[onclick*="toggleDarkMode"] i, #darkModeIcon').forEach(icon => {
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    }

    function toggleDarkMode() {
        const isDark = document.documentElement.classList.toggle('dark');
        if (isDark) {
            localStorage.setItem('theme', 'dark');
            window.showToast('🌙 ডার্ক মোড চালু করা হয়েছে!', 'info', 2000);
        } else {
            localStorage.setItem('theme', 'light');
            window.showToast('☀️ লাইট মোড চালু করা হয়েছে!', 'info', 2000);
        }
        syncDarkModeIcons();
    }

    // ==========================================================
    // 📲 PWA SERVICE WORKER REGISTRATION
    // ==========================================================
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('{{ asset("sw.js") }}')
                .then(reg => {
                    // PWA Service Worker Registered Successfully
                }).catch(err => {
                    // Service Worker Registration Ignored or Failed
                });
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        syncDarkModeIcons();
    });
</script>