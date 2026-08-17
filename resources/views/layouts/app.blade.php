<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ config('app.name', 'Subeditor24') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Tailwind & Icons --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    
    {{-- Page Specific Styles --}}
    @stack('styles')

    <style>
        :root { 
            --primary: #059669;
            --primary-accent: #06b6d4;
        }
        body { 
            font-family: 'Outfit', 'Hind Siliguri', 'Plus Jakarta Sans', sans-serif; 
            background: #0f172a; 
            color: #f8fafc;
            -webkit-tap-highlight-color: transparent; 
        }
        .bg-mesh-subeditor {
            background-color: #090d16;
            background-image: 
                radial-gradient(at 15% 15%, rgba(16, 185, 129, 0.12) 0px, transparent 50%),
                radial-gradient(at 85% 20%, rgba(6, 182, 212, 0.10) 0px, transparent 50%),
                radial-gradient(at 50% 80%, rgba(99, 102, 241, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
        }
        .glass-nav { 
            background: rgba(15, 23, 42, 0.88); 
            backdrop-filter: blur(24px); 
            -webkit-backdrop-filter: blur(24px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); 
        }
        .glass-sheet { 
            background: rgba(15, 23, 42, 0.95); 
            backdrop-filter: blur(25px); 
            border-top: 1px solid rgba(255, 255, 255, 0.1); 
        }
        .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #334155; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #10b981; }
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
        
        /* Flash Message Animation */
        .flash-message { animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    </style>
</head>

<body class="bg-mesh-subeditor text-slate-100 antialiased min-h-screen flex flex-col relative custom-scrollbar">

    {{-- 🔥 Included Components (Partials) --}}
    @include('layouts.partials.alerts')
    @include('layouts.partials.impersonate')
    @include('layouts.partials.mobile-nav')
    @include('layouts.partials.desktop-nav')

    {{-- MAIN CONTENT AREA --}}
    <main class="flex-grow container mx-auto mt-4 px-4 pb-24 lg:pb-12 lg:mt-6 pt-14 lg:pt-0">
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="mt-auto py-6 text-center text-slate-500 text-xs hidden lg:block border-t border-slate-800/60">
        <p>© {{ date('Y') }} <span class="font-bold text-emerald-400">Subeditor24</span> | Automation & Content Suite v2.5</p>
    </footer>

    {{-- Scripts --}}
    @include('layouts.partials.scripts')

</body>
</html>