<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Subeditor24</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        input::-ms-reveal, input::-ms-clear { display: none; }
        body { 
            font-family: 'Outfit', 'Hind Siliguri', sans-serif; 
            background-color: #090d16;
            background-image: 
                radial-gradient(at 15% 15%, rgba(16, 185, 129, 0.15) 0px, transparent 50%),
                radial-gradient(at 85% 20%, rgba(6, 182, 212, 0.12) 0px, transparent 50%),
                radial-gradient(at 50% 80%, rgba(99, 102, 241, 0.08) 0px, transparent 50%);
            background-attachment: fixed;
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
            color: #f8fafc;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .face-container { transition: all 0.3s ease; }
        .hands-up #eye-l, .hands-up #eye-r { opacity: 0; }
        .hands-up #hand-l { transform: translateY(-15px) translateX(10px) rotate(15deg); }
        .hands-up #hand-r { transform: translateY(-15px) translateX(-10px) rotate(-15deg); }
        .is-smiling #mouth-normal { opacity: 0; }
        .is-smiling #mouth-smile { opacity: 1; }
        #hand-l, #hand-r, #mouth-normal, #mouth-smile { transition: all 0.3s ease; transform-origin: center; }
    </style>
</head>
<body class="overflow-x-hidden">

    {{-- HEADER BRANDING --}}
    <header class="fixed top-0 w-full glass-card py-4 px-6 z-50 text-center border-b border-slate-800/80">
        <a href="/" class="inline-flex items-center gap-2.5 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-400 to-cyan-500 flex items-center justify-center text-slate-950 shadow-lg shadow-emerald-500/20 group-hover:scale-105 transition-transform font-black">
                <i class="fa-solid fa-feather-pointed text-base"></i>
            </div>
            <span class="font-extrabold text-2xl tracking-tight text-white group-hover:text-emerald-400 transition-colors">Subeditor<span class="text-emerald-400">24</span></span>
        </a>
    </header>

    <main class="flex-grow flex items-center justify-center p-4 mt-20 md:mt-16">
        <div class="glass-card p-6 md:p-10 rounded-3xl shadow-2xl w-full max-w-[420px] relative overflow-hidden">
            
            <div class="text-center mb-6">
                <h2 class="text-2xl font-extrabold text-white tracking-tight">স্বাগতম! 👋</h2>
                <p class="text-xs text-slate-400 mt-1">আপনার অ্যাকাউন্ট লগইন করুন</p>
            </div>

            {{-- INTERACTIVE AVATAR --}}
            <div class="flex justify-center mb-6">
                <div id="avatar" class="face-container relative w-20 h-20 md:w-24 md:h-24 bg-slate-900/90 rounded-full flex items-center justify-center border-4 border-emerald-500/30 shadow-inner">
                    <svg viewBox="0 0 100 100" class="w-16 h-16 md:w-20 md:h-20">
                        <circle cx="50" cy="50" r="40" fill="#0f172a" stroke="#10b981" stroke-width="2"/>
                        <g id="eyes">
                            <circle id="eye-l" cx="35" cy="45" r="4" fill="#38bdf8"/>
                            <circle id="eye-r" cx="65" cy="45" r="4" fill="#38bdf8"/>
                        </g>
                        <path id="hand-l" d="M15,80 Q25,60 35,80" stroke="#10b981" stroke-width="8" fill="none" stroke-linecap="round"/>
                        <path id="hand-r" d="M85,80 Q75,60 65,80" stroke="#10b981" stroke-width="8" fill="none" stroke-linecap="round"/>
                        <path id="mouth-normal" d="M40,65 Q50,75 60,65" stroke="#10b981" stroke-width="2" fill="none"/>
                        <path id="mouth-smile" d="M35,65 Q50,85 65,65" stroke="#10b981" stroke-width="2" fill="none" class="opacity-0"/>
                    </svg>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-950/80 border border-red-800 text-red-300 p-3.5 rounded-2xl mb-5 text-xs">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1.5 ml-1">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required 
                           placeholder="yourname@email.com"
                           class="w-full bg-slate-950/90 border border-slate-800 text-slate-100 p-3.5 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all text-sm placeholder-slate-500">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase mb-1.5 ml-1">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required 
                               placeholder="••••••••"
                               class="w-full bg-slate-950/90 border border-slate-800 text-slate-100 p-3.5 rounded-2xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none pr-12 transition-all text-sm placeholder-slate-500">
                        
                        <button type="button" id="toggleBtn" class="absolute inset-y-0 right-0 px-4 flex items-center text-slate-400 hover:text-emerald-400 transition-colors">
                            <svg id="eyeSvg" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 py-3.5 rounded-2xl font-extrabold text-base transition-all shadow-lg shadow-emerald-500/25 transform hover:-translate-y-0.5">
                    Log In
                </button>

                <div class="text-center pt-2">
                    <a href="{{ route('password.request') }}" class="text-xs text-emerald-400 hover:text-emerald-300 font-bold hover:underline">
                        🔐 পাসওয়ার্ড ভুলে গেছেন?
                    </a>
                </div>
            </form>

            @if (session('success'))
                <div class="mt-4 bg-emerald-950/80 border border-emerald-800 text-emerald-300 p-3 rounded-2xl text-xs text-center font-bold">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mt-8 text-center border-t border-slate-800/80 pt-4">
                <p class="text-xs text-slate-400">Don't have an account? <a href="https://wa.me/8801771545972" target="_blank" class="text-emerald-400 font-extrabold hover:underline">Contact Admin</a></p>
            </div>
        </div>
    </main>

    <script>
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const avatar = document.getElementById('avatar');
        const toggleBtn = document.getElementById('toggleBtn');

        emailInput.addEventListener('focus', () => {
            avatar.classList.remove('hands-up');
            avatar.classList.add('is-smiling');
        });

        emailInput.addEventListener('blur', () => {
            avatar.classList.remove('is-smiling');
        });

        passwordInput.addEventListener('focus', () => {
            avatar.classList.remove('is-smiling');
            avatar.classList.add('hands-up');
        });

        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            if(isPassword) {
                avatar.classList.remove('hands-up');
                avatar.classList.add('is-smiling'); 
            } else {
                avatar.classList.add('hands-up');
                avatar.classList.remove('is-smiling');
            }
        });

        document.addEventListener('click', (e) => {
            if (!emailInput.contains(e.target) && !passwordInput.contains(e.target) && !toggleBtn.contains(e.target)) {
                avatar.classList.remove('hands-up');
                avatar.classList.remove('is-smiling');
            }
        });
    </script>
</body>
</html>