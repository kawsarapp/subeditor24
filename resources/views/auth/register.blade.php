<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>রেজিস্টার — Subeditor24 AI News Automation Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        input::-ms-reveal, input::-ms-clear { display: none; }
        body { 
            font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; 
            background-color: #f8fafc;
            background-image: 
                radial-gradient(circle at 50% -10%, rgba(99, 102, 241, 0.15) 0%, rgba(248, 250, 252, 0) 55%),
                linear-gradient(180deg, #eef2ff 0%, #f8fafc 260px, #f8fafc 100%);
            background-attachment: fixed;
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
            color: #0f172a;
        }
        .face-container { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .hands-up #eye-l, .hands-up #eye-r { opacity: 0; }
        .hands-up #hand-l { transform: translateY(-15px) translateX(10px) rotate(15deg); }
        .hands-up #hand-r { transform: translateY(-15px) translateX(-10px) rotate(-15deg); }
        .is-smiling #mouth-normal { opacity: 0; }
        .is-smiling #mouth-smile { opacity: 1; }
        #hand-l, #hand-r, #mouth-normal, #mouth-smile { transition: all 0.3s ease; transform-origin: center; }
        
        /* Honeypot hidden */
        .hp-trap-field {
            opacity: 0;
            position: absolute;
            top: 0;
            left: 0;
            height: 0;
            width: 0;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body class="overflow-x-hidden antialiased">

    {{-- HEADER BRANDING --}}
    <header class="fixed top-0 w-full bg-white/80 backdrop-blur-md py-3.5 px-6 z-50 flex items-center justify-between border-b border-slate-200/80 shadow-sm">
        <a href="/" class="inline-flex items-center gap-2.5 group">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform font-black">
                <i class="fa-solid fa-bolt text-base"></i>
            </div>
            <span class="font-extrabold text-2xl tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors">Subeditor<span class="text-indigo-600">24</span></span>
        </a>

        <div class="flex items-center gap-3">
            @if(!(\App\Http\Controllers\PricingController::getPageConfig()['hide_pricing_from_nav'] ?? false))
            <a href="{{ route('pricing') }}" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-indigo-600 px-3 py-1.5 rounded-lg hover:bg-slate-100 transition">
                <i class="fa-solid fa-tags text-indigo-500"></i> প্রাইসিং প্ল্যান
            </a>
            @endif
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3.5 py-1.5 rounded-xl transition">
                <i class="fa-solid fa-arrow-right-to-bracket"></i> লগইন
            </a>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4 mt-20 sm:p-6 my-6">
        <div class="bg-white p-6 sm:p-10 rounded-3xl shadow-2xl shadow-slate-950/10 w-full max-w-[560px] border border-slate-200/90 relative overflow-hidden">
            
            {{-- TOP PROMO BADGE --}}
            <div class="mb-5 flex justify-center">
                <div class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-indigo-500/10 text-emerald-700 border border-emerald-200/80 px-4 py-1.5 rounded-full text-xs font-bold shadow-sm animate-pulse">
                    <span>🎁 ৭ দিনের ফ্রি ট্রায়াল</span>
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>২০ ফ্রি এআই ক্রেডিট</span>
                </div>
            </div>

            <div class="text-center mb-6">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">নতুন অ্যাকাউন্ট খুলুন 🚀</h1>
                <p class="text-xs sm:text-sm font-semibold text-slate-500 mt-1.5">আপনার নিউজ পোর্টালের জন্য স্মার্ট এআই অটোমেশন প্ল্যাটফর্ম</p>
            </div>

            {{-- INTERACTIVE AVATAR --}}
            <div class="flex justify-center mb-6">
                <div id="avatar" class="face-container relative w-20 h-20 bg-indigo-50/70 rounded-full flex items-center justify-center border-4 border-indigo-100 shadow-inner">
                    <svg viewBox="0 0 100 100" class="w-16 h-16">
                        <circle cx="50" cy="50" r="40" fill="#ffffff" stroke="#4f46e5" stroke-width="2"/>
                        <g id="eyes">
                            <circle id="eye-l" cx="35" cy="45" r="4" fill="#1e293b"/>
                            <circle id="eye-r" cx="65" cy="45" r="4" fill="#1e293b"/>
                        </g>
                        <path id="hand-l" d="M15,80 Q25,60 35,80" stroke="#4f46e5" stroke-width="8" fill="none" stroke-linecap="round"/>
                        <path id="hand-r" d="M85,80 Q75,60 65,80" stroke="#4f46e5" stroke-width="8" fill="none" stroke-linecap="round"/>
                        <path id="mouth-normal" d="M40,65 Q50,75 60,65" stroke="#4f46e5" stroke-width="2" fill="none"/>
                        <path id="mouth-smile" d="M35,65 Q50,85 65,65" stroke="#4f46e5" stroke-width="2" fill="none" class="opacity-0"/>
                    </svg>
                </div>
            </div>

            {{-- ERROR ALERTS --}}
            @if (isset($errors) && $errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-4 rounded-2xl mb-6 text-xs">
                    <div class="font-bold flex items-center gap-2 mb-1">
                        <i class="fa-solid fa-triangle-exclamation"></i> অনুগ্রহ করে নিচের ভুলগুলো সংশোধন করুন:
                    </div>
                    <ul class="list-disc pl-5 space-y-1 font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-4" autocomplete="on">
                @csrf

                {{-- HONEYPOT TRAP (Hidden from human eyes) --}}
                <div class="hp-trap-field" aria-hidden="true">
                    <input type="text" name="extra_website_trap" tabindex="-1" autocomplete="off" value="">
                    <input type="text" name="b_username_field" tabindex="-1" autocomplete="off" value="">
                </div>

                {{-- FULL NAME & BRAND NAME --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 ml-1">
                            আপনার নাম <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-user text-sm"></i>
                            </span>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required 
                                   placeholder="মো: কাওসার আহমেদ"
                                   class="w-full bg-slate-50 border border-slate-300 text-slate-900 pl-10 pr-3.5 py-3 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white outline-none transition-all text-sm placeholder-slate-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 ml-1">
                            নিউজ পোর্টালের নাম <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-newspaper text-sm"></i>
                            </span>
                            <input type="text" name="brand_name" id="brand_name" value="{{ old('brand_name') }}" required 
                                   placeholder="ঢাকা পোস্ট ২৪"
                                   class="w-full bg-slate-50 border border-slate-300 text-slate-900 pl-10 pr-3.5 py-3 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white outline-none transition-all text-sm placeholder-slate-400">
                        </div>
                    </div>
                </div>

                {{-- WEBSITE URL --}}
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 ml-1">
                        নিউজ ওয়েবসাইট লিঙ্ক (URL) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-globe text-sm"></i>
                        </span>
                        <input type="text" name="website_url" id="website_url" value="{{ old('website_url') }}" required 
                               placeholder="https://yournewsportal.com"
                               class="w-full bg-slate-50 border border-slate-300 text-slate-900 pl-10 pr-3.5 py-3 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white outline-none transition-all text-sm placeholder-slate-400">
                    </div>
                </div>

                {{-- EMAIL & PHONE --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 ml-1">
                            ইমেইল এড্রেস <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-regular fa-envelope text-sm"></i>
                            </span>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required 
                                   placeholder="editor@portal.com"
                                   class="w-full bg-slate-50 border border-slate-300 text-slate-900 pl-10 pr-3.5 py-3 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white outline-none transition-all text-sm placeholder-slate-400">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 ml-1">
                            মোবাইল নম্বর (BD) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-phone text-sm"></i>
                            </span>
                            <input type="text" name="phone" id="phone" value="{{ old('phone') }}" required 
                                   placeholder="01712345678"
                                   class="w-full bg-slate-50 border border-slate-300 text-slate-900 pl-10 pr-3.5 py-3 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white outline-none transition-all text-sm placeholder-slate-400">
                        </div>
                    </div>
                </div>

                {{-- PASSWORD & CONFIRM PASSWORD --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 ml-1">
                            পাসওয়ার্ড <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password" name="password" required 
                                   placeholder="কমপক্ষে ৮ অক্ষর"
                                   class="w-full bg-slate-50 border border-slate-300 text-slate-900 px-3.5 py-3 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white outline-none pr-10 transition-all text-sm placeholder-slate-400">
                            
                            <button type="button" onclick="togglePass('password', 'eyeSvg1')" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg id="eyeSvg1" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5 ml-1">
                            পাসওয়ার্ড নিশ্চিত করুন <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" id="password_confirmation" name="password_confirmation" required 
                                   placeholder="পুনরায় টাইপ করুন"
                                   class="w-full bg-slate-50 border border-slate-300 text-slate-900 px-3.5 py-3 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 focus:bg-white outline-none pr-10 transition-all text-sm placeholder-slate-400">
                            
                            <button type="button" onclick="togglePass('password_confirmation', 'eyeSvg2')" class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-400 hover:text-indigo-600 transition-colors">
                                <svg id="eyeSvg2" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- TERMS & POLICY --}}
                <div class="pt-2">
                    <label class="flex items-start gap-2.5 cursor-pointer select-none">
                        <input type="checkbox" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required
                               class="mt-1 w-4 h-4 rounded text-indigo-600 border-slate-300 focus:ring-indigo-500">
                        <span class="text-xs text-slate-600 font-medium">
                            আমি প্ল্যাটফর্মের <a href="#" class="text-indigo-600 font-bold hover:underline">ব্যবহারের শর্তাবলী</a> এবং <a href="#" class="text-indigo-600 font-bold hover:underline">গোপনীয়তা নীতি</a> মেনে নিচ্ছি।
                        </span>
                    </label>
                </div>

                {{-- SUBMIT BUTTON --}}
                <div class="pt-2">
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 via-indigo-700 to-violet-700 hover:from-indigo-500 hover:to-violet-600 text-white py-3.5 rounded-2xl font-extrabold text-base transition-all shadow-lg shadow-indigo-500/25 transform hover:-translate-y-0.5 active:scale-98 flex items-center justify-center gap-2">
                        <span>অ্যাকাউন্ট তৈরি করুন</span>
                        <i class="fa-solid fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center border-t border-slate-100 pt-5">
                <p class="text-xs text-slate-500">
                    আগে থেকেই অ্যাকাউন্ট আছে? 
                    <a href="{{ route('login') }}" class="text-indigo-600 font-extrabold hover:underline ml-1">লগইন করুন</a>
                </p>
                <p class="text-[11px] text-slate-400 mt-2">
                    সহায়তা প্রয়োজন? সরাসরি যোগাযোগ করুন 
                    <a href="https://wa.me/8801975389599" target="_blank" class="text-emerald-600 font-bold hover:underline inline-flex items-center gap-1">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp সাপোর্ট
                    </a>
                </p>
            </div>
        </div>
    </main>

    <footer class="py-4 text-center text-xs text-slate-400">
        &copy; {{ date('Y') }} Subeditor24. All rights reserved.
    </footer>

    <script>
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const pass1 = document.getElementById('password');
        const pass2 = document.getElementById('password_confirmation');
        const avatar = document.getElementById('avatar');

        function togglePass(id, eyeId) {
            const input = document.getElementById(id);
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        }

        [nameInput, emailInput].forEach(el => {
            if(el) {
                el.addEventListener('focus', () => {
                    avatar.classList.remove('hands-up');
                    avatar.classList.add('is-smiling');
                });
                el.addEventListener('blur', () => {
                    avatar.classList.remove('is-smiling');
                });
            }
        });

        [pass1, pass2].forEach(el => {
            if(el) {
                el.addEventListener('focus', () => {
                    avatar.classList.remove('is-smiling');
                    avatar.classList.add('hands-up');
                });
                el.addEventListener('blur', () => {
                    avatar.classList.remove('hands-up');
                });
            }
        });
    </script>
</body>
</html>
