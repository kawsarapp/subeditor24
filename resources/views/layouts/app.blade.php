<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ config('app.name', 'Subeditor24') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Tailwind, Alpine.js & Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            DEFAULT: '#4f46e5',
                            dark: '#4338ca',
                            light: '#6366f1'
                        }
                    }
                }
            }
        }
    </script>
    <script>
        // Immediate dark mode class check to prevent screen flicker
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    
    {{-- Page Specific Styles --}}
    @stack('styles')

    <style>
        :root { 
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #6366f1;
        }
        body { 
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; 
            background-color: #f8fafc;
            color: #0f172a;
            -webkit-tap-highlight-color: transparent; 
            transition: background-color 0.25s ease, color 0.25s ease;
        }
        .bg-subeditor-gradient {
            background: radial-gradient(circle at 50% -20%, rgba(99, 102, 241, 0.12) 0%, rgba(248, 250, 252, 0) 50%),
                        linear-gradient(180deg, #f1f5f9 0%, #f8fafc 180px, #f8fafc 100%);
            background-attachment: fixed;
        }
        .glass-nav { 
            background: rgba(255, 255, 255, 0.92); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8); 
        }
        .glass-sheet { 
            background: rgba(255, 255, 255, 0.96); 
            backdrop-filter: blur(20px); 
            border-top: 1px solid rgba(226, 232, 240, 0.9); 
        }
        .luxe-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 4px 20px -2px rgba(15, 23, 42, 0.05);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .luxe-card:hover {
            box-shadow: 0 12px 30px -4px rgba(79, 70, 229, 0.12);
            border-color: rgba(99, 102, 241, 0.3);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6366f1; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        
        /* Flash Message Animation */
        .flash-message { animation: slideIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        /* 🌙 ULTRA-CLEAN DARK MODE THEME */
        html.dark { color-scheme: dark; }
        html.dark body { background-color: #090d16; color: #f1f5f9; }
        html.dark .bg-subeditor-gradient {
            background: radial-gradient(circle at 50% -20%, rgba(99, 102, 241, 0.18) 0%, rgba(9, 13, 22, 0) 60%),
                        linear-gradient(180deg, #0f172a 0%, #090d16 200px, #090d16 100%);
            background-attachment: fixed;
        }
        html.dark .glass-nav {
            background: rgba(15, 23, 42, 0.92);
            border-bottom: 1px solid rgba(51, 65, 85, 0.6);
        }
        html.dark .glass-sheet {
            background: rgba(15, 23, 42, 0.96);
            border-top: 1px solid rgba(51, 65, 85, 0.8);
        }
        html.dark .luxe-card {
            background: #1e293b;
            border-color: rgba(51, 65, 85, 0.8);
            box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.4);
        }
        html.dark .bg-white { background-color: #1e293b !important; color: #f8fafc; }
        html.dark .bg-gray-50, html.dark .bg-slate-50 { background-color: #0f172a !important; }
        html.dark .bg-slate-100, html.dark .bg-gray-100 { background-color: #1e293b !important; }
        html.dark .text-slate-900, html.dark .text-gray-900, html.dark .text-gray-800, html.dark .text-slate-800, html.dark .text-gray-700, html.dark .text-slate-700 {
            color: #f1f5f9 !important;
        }
        html.dark .text-slate-600, html.dark .text-gray-600, html.dark .text-slate-500, html.dark .text-gray-500 {
            color: #94a3b8 !important;
        }
        html.dark .border-gray-200, html.dark .border-slate-200, html.dark .border-gray-300, html.dark .border-slate-300, html.dark .border-gray-100, html.dark .border-slate-100 {
            border-color: #334155 !important;
        }
        html.dark input:not([type="checkbox"]):not([type="radio"]), html.dark select, html.dark textarea {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        html.dark input::placeholder, html.dark textarea::placeholder { color: #64748b !important; }
        html.dark .settings-accordion-body { background-color: #0f172a !important; }
        html.dark footer { background-color: #0f172a !important; border-color: #1e293b !important; }
    </style>
</head>

<body class="bg-subeditor-gradient text-slate-900 antialiased min-h-screen flex flex-col relative custom-scrollbar">

    {{-- 🔥 Included Components (Partials) --}}
    @include('layouts.partials.alerts')
    @include('layouts.partials.impersonate')
    @include('layouts.partials.mobile-nav')
    @include('layouts.partials.desktop-nav')

    {{-- MAIN CONTENT AREA --}}
    <main class="flex-grow container mx-auto px-4 sm:px-6 lg:px-8 pt-20 lg:pt-6 pb-24 lg:pb-12">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-auto py-6 text-center text-slate-500 text-xs hidden lg:block border-t border-slate-200/60 bg-white/50">
        <p>© {{ date('Y') }} <span class="font-extrabold text-indigo-600">Subeditor24</span> | Content Automation & Editorial Platform</p>
    </footer>

    {{-- Scripts --}}
    @include('layouts.partials.scripts')

</body>
</html>