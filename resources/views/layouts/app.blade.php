<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ config('app.name', 'Subeditor24') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Tailwind, Alpine.js & Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
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