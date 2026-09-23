<!DOCTYPE html>
<html lang="bn" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Website Integration & API Documentation — Subeditor24</title>
    <meta name="description" content="Subeditor24 integration guide and API documentation for Laravel, WordPress, Next.js, Express, and custom CMS. Includes step-by-step setup, payload structure, category synchronization, and troubleshooting.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Fira+Code:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; background: #0d1117; color: #e6edf3; }
        .code-font { font-family: 'Fira Code', monospace; }
        .font-bangla { font-family: 'Hind Siliguri', sans-serif; }

        /* Gradients */
        .gradient-text { background: linear-gradient(135deg, #6366f1, #8b5cf6, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .gradient-text-green { background: linear-gradient(135deg, #10b981, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .gradient-text-amber { background: linear-gradient(135deg, #f59e0b, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Glass Cards */
        .glass-card { background: rgba(22, 27, 34, 0.95); border: 1px solid rgba(48, 54, 61, 0.8); backdrop-filter: blur(20px); }
        .glass-card-light { background: rgba(30, 37, 47, 0.6); border: 1px solid rgba(48, 54, 61, 0.6); }

        /* Nav */
        .nav-blur { background: rgba(13, 17, 23, 0.92); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(48, 54, 61, 0.8); }

        /* Background Orbs */
        .orb-1 { position: fixed; top: -150px; left: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%); pointer-events: none; }
        .orb-2 { position: fixed; bottom: -150px; right: -150px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(6, 182, 212, 0.08) 0%, transparent 70%); pointer-events: none; }
        .orb-3 { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 800px; background: radial-gradient(circle, rgba(139, 92, 246, 0.04) 0%, transparent 70%); pointer-events: none; }

        /* Sidebar Nav */
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 8px 14px; border-radius: 10px; font-size: 13px; font-weight: 500; color: #8b949e; text-decoration: none; transition: all 0.2s; cursor: pointer; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(99, 102, 241, 0.14); color: #a5b4fc; }
        .sidebar-link .dot { width: 6px; height: 6px; border-radius: 50%; background: #30363d; flex-shrink: 0; transition: all 0.2s; }
        .sidebar-link:hover .dot, .sidebar-link.active .dot { background: #6366f1; box-shadow: 0 0 8px rgba(99, 102, 241, 0.6); }

        /* Code Block */
        pre { background: #010409; border: 1px solid #30363d; border-radius: 12px; padding: 18px 20px; overflow-x: auto; position: relative; }
        pre code { font-family: 'Fira Code', monospace; font-size: 13px; line-height: 1.7; }
        .copy-btn { position: absolute; top: 12px; right: 12px; background: rgba(48, 54, 61, 0.8); border: 1px solid #30363d; color: #8b949e; padding: 4px 12px; border-radius: 6px; font-size: 11px; cursor: pointer; transition: all 0.2s; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; z-index: 10; }
        .copy-btn:hover { background: rgba(99, 102, 241, 0.3); color: #a5b4fc; border-color: #6366f1; }
        .copy-btn.copied { background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: #10b981; }

        /* Badges */
        .badge-required { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-optional { background: rgba(59, 130, 246, 0.15); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3); padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-special { background: rgba(245, 158, 11, 0.15); color: #fcd34d; border: 1px solid rgba(245, 158, 11, 0.3); padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th { background: rgba(48, 54, 61, 0.5); padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; color: #8b949e; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #30363d; }
        td { padding: 14px 16px; border-bottom: 1px solid rgba(48, 54, 61, 0.4); font-size: 13.5px; vertical-align: top; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(48, 54, 61, 0.2); }

        /* Callouts */
        .callout-info { background: rgba(6, 182, 212, 0.08); border-left: 3px solid #06b6d4; border-radius: 0 12px 12px 0; padding: 14px 18px; }
        .callout-warn { background: rgba(245, 158, 11, 0.08); border-left: 3px solid #f59e0b; border-radius: 0 12px 12px 0; padding: 14px 18px; }
        .callout-success { background: rgba(16, 185, 129, 0.08); border-left: 3px solid #10b981; border-radius: 0 12px 12px 0; padding: 14px 18px; }
        .callout-danger { background: rgba(239, 68, 68, 0.08); border-left: 3px solid #ef4444; border-radius: 0 12px 12px 0; padding: 14px 18px; }

        section { scroll-margin-top: 80px; }
        @media (max-width: 1024px) { .sidebar { display: none; } .sidebar.open { display: block; position: fixed; inset: 64px 0 0 0; background: #0d1117; z-index: 40; padding: 20px; overflow-y: auto; } }
    </style>
</head>
<body>

<!-- Background Orbs -->
<div class="orb-1"></div>
<div class="orb-2"></div>
<div class="orb-3"></div>

<!-- ===== TOP NAV ===== -->
<nav class="nav-blur sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <button id="sidebarToggle" class="lg:hidden p-2 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-bolt text-white text-sm"></i>
                </div>
                <div>
                    <span class="font-bold text-lg text-white">Subeditor<span class="text-indigo-400">24</span></span>
                    <span class="hidden sm:inline text-slate-500 mx-2">·</span>
                    <span class="hidden sm:inline text-slate-400 text-xs font-semibold uppercase tracking-wider">Integration Docs</span>
                </div>
            </a>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden sm:flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-400/10 border border-emerald-400/20 px-3 py-1.5 rounded-full font-bold">
                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                API & Webhook Reference
            </span>
            @auth
            <a href="{{ route('settings.index') }}" class="text-xs sm:text-sm font-semibold text-indigo-300 hover:text-white bg-indigo-500/10 hover:bg-indigo-600/30 border border-indigo-500/30 px-3 sm:px-4 py-1.5 rounded-xl transition-all">
                ← Back to Settings
            </a>
            @else
            <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-indigo-300 hover:text-white bg-indigo-500/10 hover:bg-indigo-600/30 border border-indigo-500/30 px-3 sm:px-4 py-1.5 rounded-xl transition-all">
                Login →
            </a>
            @endauth
        </div>
    </div>
</nav>

<!-- ===== MAIN LAYOUT ===== -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 flex gap-8 pt-8 pb-24">

    <!-- ===== LEFT SIDEBAR ===== -->
    <aside id="sidebar" class="sidebar w-64 flex-shrink-0">
        <div class="sticky top-24 max-h-[85vh] overflow-y-auto pr-2">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-2">সূচিপত্র (Table of Contents)</p>
            <nav class="space-y-0.5 text-xs">
                <a class="sidebar-link active" onclick="scrollToSection('overview')"><span class="dot"></span>সংক্ষিপ্ত পরিচিতি (Overview)</a>
                <a class="sidebar-link" onclick="scrollToSection('architecture')"><span class="dot"></span>কাজের ধারা (Architecture)</a>
                
                <div class="my-3 border-t border-slate-800"></div>
                <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-2 px-2">🚀 Laravel Setup Guide</p>
                <a class="sidebar-link" onclick="scrollToSection('laravel-step1')"><span class="dot"></span>১. Secret Token ও .env কনফিগারেশন</a>
                <a class="sidebar-link" onclick="scrollToSection('laravel-step2')"><span class="dot"></span>২. News Post Endpoint (routes/api.php)</a>
                <a class="sidebar-link" onclick="scrollToSection('laravel-step3')"><span class="dot"></span>৩. Category Fetch Endpoint</a>
                <a class="sidebar-link" onclick="scrollToSection('laravel-step4')"><span class="dot"></span>৪. Storage Link ও Laravel 11/12</a>

                <div class="my-3 border-t border-slate-800"></div>
                <p class="text-[10px] font-black text-emerald-400 uppercase tracking-widest mb-2 px-2">📂 Categories & Mappings</p>
                <a class="sidebar-link" onclick="scrollToSection('category-fetch')"><span class="dot"></span>ক্যাটাগরি সিঙ্ক্রোনাইজেশন</a>
                <a class="sidebar-link" onclick="scrollToSection('field-mappings')"><span class="dot"></span>পেলোড ফিল্ড ম্যাপিং</a>
                <a class="sidebar-link" onclick="scrollToSection('expected-response')"><span class="dot"></span>প্রত্যাশিত JSON রেসপন্স</a>

                <div class="my-3 border-t border-slate-800"></div>
                <p class="text-[10px] font-black text-cyan-400 uppercase tracking-widest mb-2 px-2">💻 Other Frameworks</p>
                <a class="sidebar-link" onclick="scrollToSection('code-wordpress')"><span class="dot"></span>WordPress (REST API / functions.php)</a>
                <a class="sidebar-link" onclick="scrollToSection('code-nextjs')"><span class="dot"></span>Next.js (App Router)</a>
                <a class="sidebar-link" onclick="scrollToSection('code-express')"><span class="dot"></span>Node.js / Express</a>
                <a class="sidebar-link" onclick="scrollToSection('code-php')"><span class="dot"></span>Raw PHP (Single File)</a>
                <a class="sidebar-link" onclick="scrollToSection('code-python')"><span class="dot"></span>Python (FastAPI)</a>

                <div class="my-3 border-t border-slate-800"></div>
                <a class="sidebar-link" onclick="scrollToSection('ready-api')"><span class="dot"></span>🛠️ Payload JSON Builder</a>
                <a class="sidebar-link" onclick="scrollToSection('faq-troubleshooting')"><span class="dot"></span>❓ সাধারণ সমস্যা ও সমাধান (FAQ)</a>
            </nav>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="flex-1 min-w-0 space-y-16">

        <!-- ===== HERO ===== -->
        <section id="overview">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1 rounded-full">External Website Integration</span>
                <span class="text-xs text-slate-500 font-mono">REST API & Webhooks</span>
            </div>
            <h1 class="text-3xl sm:text-5xl font-black text-white leading-tight mb-4">
                Website Integration &<br>
                <span class="gradient-text">Developer Documentation</span>
            </h1>
            <p class="text-base sm:text-lg text-slate-300 max-w-3xl leading-relaxed mb-6 font-bangla">
                Subeditor24 থেকে আপনার নিজস্ব নিউজ পোর্টাল বা অ্যাপ্লিকেশনে (যেমন <strong class="text-indigo-400 font-bold">Laravel</strong>, <strong class="text-purple-400 font-bold">WordPress</strong>, <strong class="text-cyan-400 font-bold">Next.js</strong>, বা যেকোনো কাস্টম ব্যাকএন্ড) সংবাদ প্রকাশের সম্পূর্ণ গাইডলাইন। এডিটর দ্বারা অনুমোদিত বা অটো-পাবলিশ হওয়া সংবাদসমূহ স্বয়ংক্রিয়ভাবে আপনার ডাটাবেসে ছবি, ক্যাটাগরি ও ট্যাগসহ সংরক্ষিত হবে।
            </p>

            <!-- Feature Pills -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
                <div class="glass-card rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-9 h-9 bg-indigo-500/20 border border-indigo-500/30 rounded-lg flex items-center justify-center text-indigo-400">
                        <i class="fas fa-lock text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-white text-xs">Bearer Token</p>
                        <p class="text-[11px] text-slate-400">নিরাপদ হেডার অথেন্টিকেশন</p>
                    </div>
                </div>
                <div class="glass-card rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-9 h-9 bg-emerald-500/20 border border-emerald-500/30 rounded-lg flex items-center justify-center text-emerald-400">
                        <i class="fas fa-tags text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-white text-xs">Category Sync</p>
                        <p class="text-[11px] text-slate-400">অটোমেটিক ক্যাটাগরি সিঙ্ক</p>
                    </div>
                </div>
                <div class="glass-card rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-9 h-9 bg-cyan-500/20 border border-cyan-500/30 rounded-lg flex items-center justify-center text-cyan-400">
                        <i class="fas fa-image text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-white text-xs">Multipart Upload</p>
                        <p class="text-[11px] text-slate-400">ফিজিক্যাল ইমেজ ফাইল ট্রান্সফার</p>
                    </div>
                </div>
                <div class="glass-card rounded-xl p-3.5 flex items-center gap-3">
                    <div class="w-9 h-9 bg-purple-500/20 border border-purple-500/30 rounded-lg flex items-center justify-center text-purple-400">
                        <i class="fas fa-sliders text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-white text-xs">Custom Mapping</p>
                        <p class="text-[11px] text-slate-400">কাস্টম প্যারামিটার সাপোর্ট</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== ARCHITECTURE & HOW IT WORKS ===== -->
        <section id="architecture">
            <h2 class="text-2xl font-bold text-white mb-2 flex items-center gap-2">
                <i class="fas fa-network-wired text-indigo-400"></i> How It Works (ইন্টিগ্রেশনের কার্যপ্রণালী)
            </h2>
            <p class="text-slate-400 mb-6 font-bangla text-sm">
                Subeditor24 থেকে আপনার এক্সটারনাল ওয়েবসাইটে সংবাদ পাঠানোর প্রতিটি ধাপ নিচে ব্যাখ্যা করা হলো:
            </p>

            <div class="space-y-4">
                <div class="relative flex gap-4">
                    <div class="w-8 h-8 flex-shrink-0 bg-indigo-500/20 border border-indigo-500/40 rounded-full flex items-center justify-center text-xs font-black text-indigo-400">1</div>
                    <div class="flex-1 bg-slate-800/60 p-4 rounded-xl border border-slate-700">
                        <p class="font-bold text-white text-sm mb-1">১. সংবাদ অনুমোদন (Article Approval & Trigger)</p>
                        <p class="text-xs text-slate-300 font-bangla">নিউজ ড্যাশবোর্ডে যখন কোনো সংবাদ এডিটর অনুমোদন করেন অথবা সিস্টেমে অটো-পাবলিশ মোড সক্রিয় থাকে, তখনই পোস্টিং ইঞ্জিন ব্যাকগ্রাউন্ড টাস্ক শুরু করে।</p>
                    </div>
                </div>
                <div class="relative flex gap-4">
                    <div class="w-8 h-8 flex-shrink-0 bg-purple-500/20 border border-purple-500/40 rounded-full flex items-center justify-center text-xs font-black text-purple-400">2</div>
                    <div class="flex-1 bg-slate-800/60 p-4 rounded-xl border border-slate-700">
                        <p class="font-bold text-white text-sm mb-1">২. পেলোড প্রস্তুতকরণ ও ইমেজ প্রসেসিং (Payload & Image Processing)</p>
                        <p class="text-xs text-slate-300 font-bangla">সংবাদের শিরোনাম, মূল বডি কনটেন্ট (HTML), ট্যাগ, ক্যাটাগরি আইডি এবং ফিচার্ড ইমেজ প্রসেস করা হয়। ছবিটি বাইনারি ফাইল হিসেবে <code>multipart/form-data</code> ফরম্যাটে প্রস্তুত হয়।</p>
                    </div>
                </div>
                <div class="relative flex gap-4">
                    <div class="w-8 h-8 flex-shrink-0 bg-cyan-500/20 border border-cyan-500/40 rounded-full flex items-center justify-center text-xs font-black text-cyan-400">3</div>
                    <div class="flex-1 bg-slate-800/60 p-4 rounded-xl border border-slate-700">
                        <p class="font-bold text-white text-sm mb-1">৩. নিরাপদ HTTP POST রিকোয়েস্ট (Secure API Delivery)</p>
                        <p class="text-xs text-slate-300 font-bangla">আপনার নির্ধারিত API এন্ডপয়েন্টে <code>Authorization: Bearer &lt;Secret_Token&gt;</code> হেডার সহ HTTP POST রিকোয়েস্ট পাঠানো হয়। সংযোগের নির্ভরযোগ্যতার জন্য ১২০ সেকেন্ড পর্যন্ত টাইমআউট রাখা হয়।</p>
                    </div>
                </div>
                <div class="relative flex gap-4">
                    <div class="w-8 h-8 flex-shrink-0 bg-emerald-500/20 border border-emerald-500/40 rounded-full flex items-center justify-center text-xs font-black text-emerald-400">4</div>
                    <div class="flex-1 bg-slate-800/60 p-4 rounded-xl border border-slate-700">
                        <p class="font-bold text-white text-sm mb-1">৪. রেসপন্স হ্যান্ডলিং ও স্ট্যাটাস আপডেট (Response Handling)</p>
                        <p class="text-xs text-slate-300 font-bangla">আপনার ওয়েবসাইট থেকে রেসপন্সে আসা <code>post_id</code> এবং <code>url</code> সংরক্ষণ করে সংশ্লিষ্ট সংবাদটিকে সিস্টেমে <strong>Published</strong> হিসেবে মার্ক করা হয়।</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- ===== LARAVEL INTEGRATION MASTER GUIDE ===== -->
        <!-- ========================================================================= -->
        <section id="laravel-step1" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-red-400 bg-red-500/10 border border-red-500/20 px-3 py-1 rounded-full">
                    <i class="fab fa-laravel mr-1"></i> Laravel Integration
                </span>
            </div>
            <h2 class="text-3xl font-black text-white mb-2">
                Laravel ইন্টিগ্রেশন নির্দেশিকা (৪টি ধাপ)
            </h2>
            <p class="text-sm text-slate-400 mb-6 font-bangla">
                আপনার Laravel (Laravel 9, 10, 11, 12) প্রজেক্টে Subeditor24 থেকে সরাসরি সংবাদ রিসিভ ও ক্যাটাগরি সিঙ্ক করতে নিচের ধাপগুলো সম্পন্ন করুন:
            </p>

            <!-- STEP 1: .ENV & TOKEN -->
            <div class="glass-card rounded-2xl p-6 mb-6">
                <div class="flex items-start gap-4 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center flex-shrink-0 text-sm">১</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">ধাপ ১: API Secret Token তৈরি ও .env ফাইলে যুক্ত করা</h3>
                        <p class="text-xs text-slate-400 font-bangla mt-1">
                            Subeditor24 এর Settings পেজে <strong>"API Secret Token"</strong> ফিল্ডে দেওয়া টোকেনটি কপি করুন (বা Generate বাটনে ক্লিক করে নতুন তৈরি করুন)। এরপর আপনার Laravel প্রজেক্টের <code>.env</code> ফাইলে এটি যুক্ত করুন:
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre><code class="text-emerald-400"># আপনার Laravel প্রজেক্টের .env ফাইলে যুক্ত করুন:
SUBEDITOR_API_SECRET=your_generated_secret_token_here
</code></pre>
                </div>
            </div>

            <!-- STEP 2: NEWS POST ROUTE -->
            <div id="laravel-step2" class="glass-card rounded-2xl p-6 mb-6">
                <div class="flex items-start gap-4 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center flex-shrink-0 text-sm">২</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">ধাপ ২: নিউজ রিসিভার এন্ডপয়েন্ট তৈরি (routes/api.php)</h3>
                        <p class="text-xs text-slate-400 font-bangla mt-1">
                            আপনার Laravel প্রজেক্টের <code class="text-indigo-300">routes/api.php</code> ফাইলে নিচের রাউটটি যুক্ত করুন। এটি ডেটা ভ্যালিডেট করে ডাটাবেসে সেভ করবে এবং আপলোড করা ইমেজ স্টোরেজে রাখবে:
                        </p>
                    </div>
                </div>

                {{-- Laravel Version Note Banner --}}
                <div class="mb-4 p-4 rounded-xl bg-indigo-500/10 border border-indigo-500/25 text-xs font-bangla space-y-2">
                    <p class="font-bold text-indigo-300 flex items-center gap-1.5 text-sm">
                        <i class="fab fa-laravel"></i> Laravel ভার্সন নির্দেশিকা (routes/api.php ফাইল সংক্রান্ত):
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-slate-300">
                        <div class="bg-slate-900/70 p-3 rounded-lg border border-slate-800">
                            <strong class="text-emerald-400 block mb-1">✅ Laravel 10 এবং পূর্ববর্তী ভার্সন:</strong>
                            <p class="text-[11px] leading-relaxed text-slate-400">Laravel 6, 7, 8, 9 ও 10 এ <code class="text-indigo-300">routes/api.php</code> ফাইলটি প্রজেক্ট তৈরির সময় ডিফল্টভাবেই বিদ্যমান থাকে। কোনো অতিরিক্ত কমান্ডের প্রয়োজন নেই।</p>
                        </div>
                        <div class="bg-slate-900/70 p-3 rounded-lg border border-slate-800">
                            <strong class="text-amber-400 block mb-1">⚡ Laravel 11 ও 12 (Modern Laravel):</strong>
                            <p class="text-[11px] leading-relaxed text-slate-400">Laravel 11/12 এ ফ্রেশ প্রজেক্টে <code class="text-indigo-300">api.php</code> ডিফল্ট থাকে না। টার্মিনালে কমান্ডটি একবার রান করুন: <code class="text-amber-300 bg-slate-950 px-1 py-0.5 rounded font-mono">php artisan install:api</code> — ফাইলটি তৈরি হয়ে যাবে।</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre><code class="text-emerald-300">&lt;?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\NewsPost; // ⚠️ আপনার প্রজেক্টের নিউজ মডেল

Route::post('/external-news-post', function (Request $request) {
    // ১. অথেন্টিকেশন টোকেন ভেরিফিকেশন
    $authHeader = $request->header('Authorization');
    $secret = env('SUBEDITOR_API_SECRET', 'YOUR_SECRET_TOKEN_HERE');
    $expectedToken = "Bearer " . $secret;

    if (!$authHeader || $authHeader !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid or missing API token'
        ], 401);
    }

    // ২. ইনকামিং রিকোয়েস্ট ভ্যালিডেশন
    $validated = $request->validate([
        'title'       => 'required|string',
        'content'     => 'required|string',
        'image'       => 'nullable', // ফাইল অথবা ইমেজ ইউআরএল
        'category_id' => 'nullable',
        'category'    => 'nullable|string',
        'tags'        => 'nullable|string',
        'slug'        => 'nullable|string',
    ]);

    // ৩. ফিচার্ড ইমেজ সংরক্ষণ (Multipart File অথবা URL)
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('news_images', 'public');
    } elseif ($request->filled('image') && is_string($request->image)) {
        $imagePath = $request->image;
    }

    // ৪. স্লাগ জেনারেট
    $slug = !empty($validated['slug']) 
        ? Str::slug($validated['slug']) 
        : Str::slug($validated['title']) . '-' . time();

    // ৫. ডাটাবেসে নিউজ সংরক্ষণ
    $post = NewsPost::create([
        'title'       => $validated['title'],
        'slug'        => $slug,
        'content'     => $validated['content'],
        'image'       => $imagePath,
        'category_id' => $validated['category_id'] ?? 1,
        'tags'        => $validated['tags'] ?? null,
        'status'      => 'published',
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    // ৬. সফল রেসপন্স ও লাইভ পোস্ট লিংক রিটার্ন
    return response()->json([
        'success' => true,
        'message' => 'News published successfully',
        'post_id' => $post->id,
        'url'     => url('/news/' . $post->slug)
    ], 200);
});
</code></pre>
                </div>
            </div>

            <!-- STEP 3: CATEGORY FETCH ROUTE -->
            <div id="laravel-step3" class="glass-card rounded-2xl p-6 mb-6">
                <div class="flex items-start gap-4 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center flex-shrink-0 text-sm">৩</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">ধাপ ৩: ক্যাটাগরি তালিকা রিটার্ন করার রুট (routes/api.php)</h3>
                        <p class="text-xs text-slate-400 font-bangla mt-1">
                            Subeditor24 ড্যাশবোর্ড থেকে যাতে সরাসরি আপনার ওয়েবসাইটের ক্যাটাগরিগুলো ফেচ করা যায়, সেজন্য <code class="text-indigo-300">routes/api.php</code> ফাইলে এই GET এন্ডপয়েন্টটি যোগ করুন:
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre><code class="text-emerald-300">use App\Models\Category; // ⚠️ আপনার প্রজেক্টের ক্যাটাগরি মডেল

Route::get('/get-categories', function (Request $request) {
    // টোকেন যাচাই (Bearer Header অথবা Query Parameter ?token=...)
    $token = $request->bearerToken() ?? $request->query('token');
    $expectedSecret = env('SUBEDITOR_API_SECRET');

    if ($token !== $expectedSecret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // ক্যাটাগরি তালিকা রিটার্ন করুন (id ও name ফিল্ড সহ)
    return response()->json(
        Category::select('id', 'name')->get()
    );
});
</code></pre>
                </div>

                <div class="callout-info mt-4 rounded-r-xl">
                    <p class="text-xs text-cyan-300 font-bangla leading-relaxed">
                        <i class="fas fa-info-circle mr-1"></i> <strong>রেসপন্স স্ট্রাকচার:</strong> এন্ডপয়েন্টটি সরাসরি <code>[{"id": 1, "name": "জাতীয়"}, {"id": 2, "name": "খেলাধুলা"}]</code> অ্যারে রিটার্ন করবে, যা Subeditor24 ড্যাশবোর্ডে সাথে সাথে লোড হয়ে যাবে।
                    </p>
                </div>
            </div>

            <!-- STEP 4: STORAGE LINK & LARAVEL 11 SETUP -->
            <div id="laravel-step4" class="glass-card rounded-2xl p-6">
                <div class="flex items-start gap-4 mb-4">
                    <span class="w-8 h-8 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center flex-shrink-0 text-sm">৪</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">ধাপ ৪: স্টোরেজ লিংক ও প্রয়োজনীয় আর্টিসান কমান্ড</h3>
                        <p class="text-xs text-slate-400 font-bangla mt-1">
                            আপলোড করা ইমেজগুলো ওয়েবসাইটে দৃশ্যমান করতে এবং কনফিগারেশন আপডেট করতে নিচের কমান্ডগুলো দিন:
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-bangla">
                    <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700">
                        <p class="font-bold text-white mb-2"><i class="fas fa-terminal text-indigo-400 mr-1"></i> Public Storage Link</p>
                        <p class="text-slate-300 mb-2">পাবলিক ডিরেক্টরিতে স্টোরেজ লিংক তৈরি করতে রান করুন:</p>
                        <pre class="!py-2 !px-3"><code>php artisan storage:link</code></pre>
                    </div>
                    <div class="bg-slate-800/80 p-4 rounded-xl border border-slate-700">
                        <p class="font-bold text-white mb-2"><i class="fab fa-laravel text-red-400 mr-1"></i> Config Cache Refresh</p>
                        <p class="text-slate-300 mb-2">.env পরিবর্তনের পর কনফিগ ক্যাশ ক্লিয়ার করতে রান করুন:</p>
                        <pre class="!py-2 !px-3"><code>php artisan config:clear</code></pre>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- ===== CATEGORY SYNC SYSTEM ===== -->
        <!-- ========================================================================= -->
        <section id="category-fetch" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                    <i class="fas fa-tags mr-1"></i> Category System
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                ক্যাটাগরি সিঙ্ক্রোনাইজেশন ও ম্যাপিং
            </h2>
            <p class="text-slate-400 mb-6 font-bangla text-sm">
                Subeditor24 প্রতিটি সংবাদের বিষয়বস্তু বিশ্লেষণ করে উপযুক্ত ক্যাটাগরি নির্ধারণ করে। আপনার ওয়েবসাইটের ক্যাটাগরি তালিকার সাথে মেলাতে সিস্টেম নিচের অগ্রাধিকার ক্রমে ডেটা ফেচ করে:
            </p>

            <div class="glass-card rounded-2xl p-6 mb-6">
                <h3 class="text-base font-bold text-white mb-3">ক্যাটাগরি ফেচিং প্রায়োরিটি অর্ডার (Resolution Order):</h3>
                <div class="space-y-3 font-bangla text-xs">
                    <div class="p-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center">1</span>
                        <div>
                            <strong class="text-emerald-300">Custom Category Fetch URL:</strong> Settings এ যদি কাস্টম ক্যাটাগরি এন্ডপয়েন্ট দেওয়া থাকে, সিস্টেম সর্বপ্রথম সেই URL থেকে ক্যাটাগরি ডেটা ফেচ করে।
                        </div>
                    </div>
                    <div class="p-3 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-400 font-bold flex items-center justify-center">2</span>
                        <div>
                            <strong class="text-indigo-300">Default API Endpoint ({Base_URL}/api/get-categories):</strong> কাস্টম URL খালি থাকলে সিস্টেম স্বয়ংক্রিয়ভাবে আপনার সাইটের <code>/api/get-categories</code> পাথে রিকোয়েস্ট পাঠায়।
                        </div>
                    </div>
                    <div class="p-3 bg-slate-800 border border-slate-700 rounded-xl flex items-center gap-3">
                        <span class="w-6 h-6 rounded-full bg-slate-700 text-slate-300 font-bold flex items-center justify-center">3</span>
                        <div>
                            <strong class="text-slate-300">WordPress REST API Fallback:</strong> যদি প্ল্যাটফর্ম হিসেবে WordPress সিলেক্ট করা থাকে, তবে ডিফল্ট WP REST API (<code>/wp-json/wp/v2/categories</code>) ব্যবহার করা হয়।
                        </div>
                    </div>
                </div>

                <div class="callout-success mt-4 rounded-r-xl">
                    <p class="text-xs text-emerald-300 font-bangla">
                        <i class="fas fa-check-circle mr-1"></i> পারফরম্যান্সের স্বার্থে ক্যাটাগরি ডেটা ক্যাশ রাখা হয়। কোনো নতুন ক্যাটাগরি তৈরি বা পরিবর্তন করলে Settings পেজে <strong>"🔄 Refresh Categories"</strong> বাটনে ক্লিক করে তাৎক্ষণিক আপডেট করতে পারবেন।
                    </p>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- ===== FIELD MAPPINGS ===== -->
        <!-- ========================================================================= -->
        <section id="field-mappings" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-full">
                    <i class="fas fa-sliders mr-1"></i> Dynamic Mapping
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                পেলোড ফিল্ড নেম ম্যাপিং (Field Mapping)
            </h2>
            <p class="text-slate-400 mb-6 font-bangla text-sm">
                যদি আপনার ওয়েবসাইট বা API তে প্যারামিটারের নাম আলাদা হয় (যেমন <code class="text-indigo-300">title</code> এর বদলে <code class="text-indigo-300">news_headline</code>), তবে আপনি কাস্টম ম্যাপিং JSON ব্যবহার করে যেকোনো ফিল্ডের নাম পরিবর্তন করতে পারেন:
            </p>

            <div class="overflow-hidden glass-card rounded-2xl mb-6">
                <table>
                    <thead>
                        <tr>
                            <th>Subeditor24 Default Key</th>
                            <th>ডেটার বিবরণ (Description)</th>
                            <th>ডেটা টাইপ</th>
                            <th>প্রয়োজনীয়তা</th>
                        </tr>
                    </thead>
                    <tbody class="font-bangla text-xs">
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"title"</code></td>
                            <td>সংবাদের প্রধান শিরোনাম (Article Headline)</td>
                            <td><span class="code-font text-slate-400">string</span></td>
                            <td><span class="badge-required">Required</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"content"</code></td>
                            <td>সম্পূর্ণ সংবাদ বডি HTML ফরম্যাটে (Full Article Body HTML)</td>
                            <td><span class="code-font text-slate-400">HTML string</span></td>
                            <td><span class="badge-required">Required</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"image"</code></td>
                            <td>ফিচার্ড ইমেজ (Multipart File Upload)</td>
                            <td><span class="code-font text-slate-400">file</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"category"</code> / <code class="code-font text-sky-400 text-sm">"category_id"</code></td>
                            <td>টার্গেট ওয়েবসাইটের ক্যাটাগরি আইডি (Category ID)</td>
                            <td><span class="code-font text-slate-400">int / int[]</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"tags"</code></td>
                            <td>কমা দিয়ে বিভক্ত ট্যাগের তালিকা (Comma-separated tags)</td>
                            <td><span class="code-font text-slate-400">string</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"slug"</code></td>
                            <td>ইউআরএল ফ্রেন্ডলি স্লাগ (URL Slug)</td>
                            <td><span class="code-font text-slate-400">string</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- ===== EXPECTED JSON RESPONSE ===== -->
        <!-- ========================================================================= -->
        <section id="expected-response" class="border-t border-slate-800 pt-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                Expected Response (প্রত্যাশিত API রেসপন্স ফরম্যাট)
            </h2>
            <p class="text-slate-400 mb-6 font-bangla text-sm">
                সংবাদ সফলভাবে রিসিভ করার পর আপনার এন্ডপয়েন্ট থেকে HTTP <strong class="text-emerald-400">200</strong> বা <strong class="text-emerald-400">201</strong> স্ট্যাটাস কোড সহ নিচের যেকোনো একটি ফরম্যাটে JSON রেসপন্স পাঠাতে হবে:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="glass-card rounded-2xl p-5">
                    <p class="text-xs font-bold text-emerald-400 mb-2">✅ স্ট্যান্ডার্ড ফরম্যাট (Standard & Recommended):</p>
                    <pre><code><span class="text-slate-500">{</span>
  <span class="text-sky-400">"success"</span>: <span class="text-amber-400">true</span>,
  <span class="text-sky-400">"post_id"</span>: <span class="text-amber-400">101</span>,
  <span class="text-sky-400">"url"</span>: <span class="text-emerald-300">"https://mywebsite.com/news/article-slug"</span>
<span class="text-slate-500">}</span></code></pre>
                </div>

                <div class="glass-card rounded-2xl p-5">
                    <p class="text-xs font-bold text-indigo-400 mb-2">✅ নেস্টেড ফরম্যাট (Nested Data Format):</p>
                    <pre><code><span class="text-slate-500">{</span>
  <span class="text-sky-400">"status"</span>: <span class="text-emerald-300">"success"</span>,
  <span class="text-sky-400">"data"</span>: <span class="text-slate-500">{</span>
    <span class="text-sky-400">"post_id"</span>: <span class="text-amber-400">205</span>,
    <span class="text-sky-400">"live_url"</span>: <span class="text-emerald-300">"https://mywebsite.com/205"</span>
  <span class="text-slate-500">}</span>
<span class="text-slate-500">}</span></code></pre>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- ===== CODE SNIPPETS FOR OTHER FRAMEWORKS ===== -->
        <!-- ========================================================================= -->
        <section id="code-wordpress" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-sky-400 bg-sky-500/10 border border-sky-500/20 px-3 py-1 rounded-full">
                    <i class="fab fa-wordpress mr-1"></i> WordPress Integration
                </span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">WordPress (Custom REST Endpoint via functions.php)</h2>
            <p class="text-slate-400 mb-4 font-bangla text-xs">আপনার অ্যাক্টিভ থিমের <code>functions.php</code> ফাইলে অথবা কাস্টম প্লাগইনে নিচের কোড যুক্ত করে এন্ডপয়েন্ট তৈরি করুন:</p>
            <div class="glass-card rounded-2xl p-5 relative">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                <pre><code class="text-emerald-300">add_action('rest_api_init', function () {
    register_rest_route('subeditor/v1', '/post-news', [
        'methods'             => 'POST',
        'callback'            => 'handle_subeditor_post',
        'permission_callback' => '__return_true'
    ]);
});

function handle_subeditor_post($request) {
    $token = $request->get_header('Authorization');
    if ($token !== 'Bearer YOUR_SECRET_TOKEN_HERE') {
        return new WP_Error('unauthorized', 'Invalid Token', ['status' => 401]);
    }

    $params = $request->get_params();
    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($params['title'] ?? ''),
        'post_content' => wp_kses_post($params['content'] ?? ''),
        'post_status'  => 'publish',
        'post_author'  => 1
    ]);

    if (is_wp_error($post_id)) {
        return new WP_Error('db_error', $post_id->get_error_message(), ['status' => 500]);
    }

    return rest_ensure_response([
        'success' => true,
        'post_id' => $post_id,
        'url'     => get_permalink($post_id)
    ]);
}
</code></pre>
            </div>
        </section>

        <section id="code-nextjs" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-slate-300 bg-slate-800 border border-slate-700 px-3 py-1 rounded-full">
                    <i class="fab fa-react mr-1"></i> Next.js App Router (TypeScript)
                </span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Next.js (app/api/external-news-post/route.ts)</h2>
            <div class="glass-card rounded-2xl p-5 relative">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                <pre><code class="text-cyan-300">import { NextRequest, NextResponse } from 'next/server';

export async function POST(req: NextRequest) {
  const authHeader = req.headers.get('authorization');
  const expectedToken = `Bearer ${process.env.SUBEDITOR_API_SECRET || 'YOUR_SECRET_TOKEN'}`;

  if (authHeader !== expectedToken) {
    return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
  }

  const formData = await req.formData();
  const title = formData.get('title') as string;
  const content = formData.get('content') as string;

  // আপনার ডাটাবেসে সেভ করুন (Prisma / Drizzle / MongoDB / PostgreSQL)
  const postId = 101;

  return NextResponse.json({
    success: true,
    post_id: postId,
    url: `/news/${postId}`
  });
}
</code></pre>
            </div>
        </section>

        <section id="code-express" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">
                    <i class="fab fa-node-js mr-1"></i> Node.js (Express.js)
                </span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Express.js API Router</h2>
            <div class="glass-card rounded-2xl p-5 relative">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                <pre><code class="text-emerald-300">const express = require('express');
const router = express.Router();

router.post('/api/external-news-post', (req, res) => {
  const authHeader = req.headers.authorization;
  if (authHeader !== 'Bearer ' + process.env.SUBEDITOR_API_SECRET) {
    return res.status(401).json({ success: false, message: 'Unauthorized' });
  }

  const { title, content, category_id, tags } = req.body;
  
  // আপনার ডাটাবেসে ইনসার্ট করুন
  return res.json({
    success: true,
    post_id: 101,
    url: 'https://mysite.com/news/101'
  });
});

module.exports = router;
</code></pre>
            </div>
        </section>

        <section id="code-php" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-purple-400 bg-purple-500/10 border border-purple-500/20 px-3 py-1 rounded-full">
                    <i class="fab fa-php mr-1"></i> Raw PHP Single Drop-in File
                </span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Single File Drop-in (public/news-receiver.php)</h2>
            <p class="text-slate-400 mb-4 font-bangla text-xs">কোনো ফ্রেমওয়ার্ক ছাড়া সাধারণ PHP সাইটের <code>public</code> ফোল্ডারে এই ফাইলটি রেখে ব্যবহার করুন:</p>
            <div class="glass-card rounded-2xl p-5 relative">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                <pre><code class="text-purple-300">&lt;?php
header('Content-Type: application/json');

$headers = getallheaders();
$auth = $headers['Authorization'] ?? ($headers['authorization'] ?? '');

if ($auth !== "Bearer YOUR_SECRET_TOKEN_HERE") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$title   = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($title)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

// ডাটাবেসে সংরক্ষণ করুন (PDO Example)
// $pdo = new PDO("mysql:host=localhost;dbname=news", "user", "pass");
// $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
// $stmt->execute([$title, $content]);

echo json_encode([
    'success' => true,
    'post_id' => 101,
    'url'     => 'https://mywebsite.com/news/101'
]);
</code></pre>
            </div>
        </section>

        <section id="code-python" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-3 py-1 rounded-full">
                    <i class="fab fa-python mr-1"></i> Python (FastAPI)
                </span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Python (FastAPI API Receiver)</h2>
            <div class="glass-card rounded-2xl p-5 relative">
                <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                <pre><code class="text-amber-300">from fastapi import FastAPI, Header, Form, HTTPException
from typing import Optional

app = FastAPI()

@app.post("/api/external-news-post")
async def receive_news(
    title: str = Form(...),
    content: str = Form(...),
    authorization: Optional[str] = Header(None)
):
    if authorization != "Bearer YOUR_SECRET_TOKEN":
        raise HTTPException(status_code=401, detail="Unauthorized")
    
    # ডাটাবেসে সংরক্ষণ করুন
    return {"success": True, "post_id": 101, "url": "https://mywebsite.com/news/101"}
</code></pre>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- ===== INTERACTIVE JSON BUILDER ===== -->
        <!-- ========================================================================= -->
        <section id="ready-api" class="border-t border-slate-800 pt-8">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1 rounded-full">
                    <i class="fas fa-magic mr-1"></i> Visual Generator
                </span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">🛠️ Custom Payload Mapping Generator</h2>
            <p class="text-slate-400 mb-6 font-bangla text-sm">আপনার ওয়েবসাইটের API ফিল্ড নেমগুলো লিখুন — রিয়েলটাইমে ম্যাপিং JSON তৈরি হয়ে যাবে। এরপর Copy করে Settings পেজে সংরক্ষণ করুন:</p>

            <div class="glass-card rounded-2xl p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Builder Form -->
                    <div class="space-y-4">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Field Names</p>

                        <div class="space-y-3 text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="font-bold text-sky-400 code-font">"title"</span>
                                    <span class="block text-[10px] text-slate-500">আবশ্যক</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_title" type="text" placeholder="e.g. news_title" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 code-font">
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="font-bold text-sky-400 code-font">"content"</span>
                                    <span class="block text-[10px] text-slate-500">আবশ্যক</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_content" type="text" placeholder="e.g. body" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 code-font">
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="font-bold text-sky-400 code-font">"image"</span>
                                    <span class="block text-[10px] text-slate-500">ঐচ্ছিক</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_image" type="text" placeholder="e.g. thumbnail" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 code-font">
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="font-bold text-sky-400 code-font">"category"</span>
                                    <span class="block text-[10px] text-slate-500">ঐচ্ছিক</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_category" type="text" placeholder="e.g. category_id" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 code-font">
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="font-bold text-sky-400 code-font">"tags"</span>
                                    <span class="block text-[10px] text-slate-500">ঐচ্ছিক</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_tags" type="text" placeholder="e.g. tags" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 code-font">
                            </div>
                        </div>

                        <div class="border-t border-slate-700 pt-4">
                            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-2">Authentication Method</p>
                            <div class="flex gap-4 text-xs mb-3 font-bangla">
                                <label class="flex items-center gap-2 cursor-pointer text-slate-300">
                                    <input type="radio" name="auth_type" value="bearer" checked onchange="buildJson()" class="text-indigo-500">
                                    <span>Bearer Header (Standard)</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer text-slate-300">
                                    <input type="radio" name="auth_type" value="body" onchange="buildJson()" class="text-indigo-500">
                                    <span>Body Token Parameter</span>
                                </label>
                            </div>
                            <div id="token_field_wrap" class="hidden items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-xs font-bold text-amber-400 code-font">"token"</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_token" type="text" placeholder="e.g. api_key" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-200 code-font">
                            </div>
                        </div>
                    </div>

                    <!-- Live Preview -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Live JSON Output</p>
                            <button id="copyBuiltJson" onclick="copyBuiltJson()" class="text-xs bg-slate-700 hover:bg-indigo-600 border border-slate-600 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg font-bold transition-all">
                                <i class="fas fa-copy mr-1"></i> Copy JSON
                            </button>
                        </div>
                        <pre id="json_preview" class="min-h-[260px] text-xs"><code id="json_preview_code" class="text-slate-300">// ফিল্ডগুলো পূরণ করলে এখানে JSON তৈরি হবে</code></pre>

                        @auth
                        <button onclick="applyToSettings()" class="mt-4 w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-2.5 rounded-xl transition-all text-xs font-bangla cursor-pointer">
                            ✅ Apply to Settings (সেটিংসে যুক্ত করুন)
                        </button>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        <!-- ========================================================================= -->
        <!-- ===== FAQ & TROUBLESHOOTING ===== -->
        <!-- ========================================================================= -->
        <section id="faq-troubleshooting" class="border-t border-slate-800 pt-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-6">
                ❓ সাধারণ সমস্যা ও সমাধান (Troubleshooting & FAQ)
            </h2>

            <div class="space-y-3 font-bangla" id="faq-list">

                <!-- FAQ 1: 401 -->
                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-400"></span>
                            HTTP 401 Unauthorized এরর আসলে কি করতে হবে?
                        </p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-slate-300 leading-relaxed space-y-2">
                        <p><strong>সম্ভাব্য কারণ:</strong> Subeditor24 সেটিংসের টোকেন এবং আপনার সার্ভারের <code>.env</code> ফাইলের টোকেন ম্যাচ করছে না, অথবা Apache/Nginx সার্ভার <code>Authorization</code> হেডার ফিল্টার করে দিচ্ছে।</p>
                        <p><strong>সমাধান:</strong></p>
                        <ul class="list-disc list-inside space-y-1 ml-2 text-slate-400">
                            <li>আপনার <code>.env</code> ফাইলে <code class="text-indigo-300">SUBEDITOR_API_SECRET</code> মিলিয়ে নিন এবং <code>php artisan config:clear</code> দিন।</li>
                            <li>Apache সার্ভার হলে <code>public/.htaccess</code> ফাইলে <code class="text-indigo-300">CGIPassAuth on</code> লাইনটি যোগ করুন।</li>
                        </ul>
                    </div>
                </div>

                <!-- FAQ 2: 404 -->
                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                            HTTP 404 Not Found এরর দেখালে কি চেক করব?
                        </p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-slate-300 leading-relaxed space-y-2">
                        <p><strong>সম্ভাব্য কারণ:</strong> API রুটটি আপনার প্রজেক্টের <code>routes/api.php</code> তে যোগ করা হয়নি অথবা Settings পেজে Base URL এর ফরম্যাটে ভুল রয়েছে।</p>
                        <p><strong>সমাধান:</strong> নিশ্চিত করুন যে আপনার <code>routes/api.php</code> ফাইলে <code>Route::post('/external-news-post', ...)</code> রুটটি উপস্থিত রয়েছে এবং Settings পেজে Base URL ঠিকভাবে লেখা হয়েছে (যেমন <code>https://mywebsite.com</code>)।</p>
                    </div>
                </div>

                <!-- FAQ 3: 419 / CSRF -->
                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-400"></span>
                            HTTP 419 Page Expired বা CSRF Token Mismatch কেন হয়?
                        </p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-slate-300 leading-relaxed space-y-2">
                        <p>Laravel এ <code>routes/api.php</code> ফাইলের রুটগুলোতে কোনো CSRF যাচাই লাগে না (Stateless)। আপনি যদি ভুলবশত <code>routes/web.php</code> তে রুট লিখে থাকেন, তবে তা সরিয়ে <code>routes/api.php</code> ফাইলে যুক্ত করুন।</p>
                    </div>
                </div>

                <!-- FAQ 4: 422 -->
                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                            HTTP 422 Unprocessable Content এরর কেন দেখায়?
                        </p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-slate-300 leading-relaxed space-y-2">
                        <p>আপনার Laravel রিসিভারের ভ্যালিডেশন রুলসে এমন কোনো ফিল্ড <code class="text-indigo-300">required</code> করা রয়েছে যা Subeditor24 পাঠায়নি (যেমন author_id, views ইত্যাদি)। সেগুলোকে <code class="text-indigo-300">nullable</code> করুন অথবা রিসিভারে ডিফল্ট ভ্যালু সেট করুন।</p>
                    </div>
                </div>

                <!-- FAQ 5: 500 -->
                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                            HTTP 500 Internal Server Error আসলে কিভাবে ডিবাগ করব?
                        </p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-slate-300 leading-relaxed space-y-2">
                        <p>আপনার সার্ভারের <code>storage/logs/laravel.log</code> ফাইলটি ওপেন করে এরর মেসেজ চেক করুন। সাধারণত ডাটাবেস কলাম অনুপস্থিত থাকা (Column not found), মডেল <code>$fillable</code> অ্যারেতে ফিল্ড মিসিং থাকা, অথবা <code>storage/</code> ডিরেক্টরির ফাইল পারমিশনের কারণে এই সমস্যা ঘটে।</p>
                    </div>
                </div>

                <!-- FAQ 6: Localhost / Staging -->
                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            লোকালহোস্ট (Localhost / Staging) বা সেলফ-সাইন্ড SSL এ কাজ করবে কি?
                        </p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5 text-xs text-slate-300 leading-relaxed space-y-2">
                        <p>হ্যাঁ, Subeditor24 লোকাল ডেভেলপমেন্ট এবং সেলফ-সাইন্ড SSL সার্টিফিকেট ভ্যালিডেশন হ্যান্ডল করে। আপনি DDEV, Laragon, Ngrok বা লোকাল সার্ভার লিঙ্ক সরাসরি ব্যবহার করতে পারবেন।</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <div class="border-t border-slate-800 pt-8 text-center text-xs text-slate-500">
            <p>Subeditor24 Integration & Webhook System · Technical Documentation</p>
            <p class="text-slate-600 text-[11px] mt-1">© {{ date('Y') }} Subeditor24. All rights reserved.</p>
        </div>

    </main>
</div>

<script>
// ===== BUILD JSON =====
let builtJson = {};

function buildJson() {
    const title   = document.getElementById('b_title').value.trim();
    const content = document.getElementById('b_content').value.trim();
    const image   = document.getElementById('b_image').value.trim();
    const cat     = document.getElementById('b_category').value.trim();
    const tags    = document.getElementById('b_tags').value.trim();
    const token   = document.getElementById('b_token') ? document.getElementById('b_token').value.trim() : '';
    const authType = document.querySelector('input[name="auth_type"]:checked')?.value || 'bearer';

    const tokenField = document.getElementById('token_field_wrap');
    if (tokenField) {
        tokenField.style.display = (authType === 'body') ? 'flex' : 'none';
    }

    builtJson = {};
    if (title)   builtJson['title']   = title;
    if (content) builtJson['content'] = content;
    if (image)   builtJson['image']   = image;
    if (cat)     builtJson['category']= cat;
    if (tags)    builtJson['tags']    = tags;

    if (authType === 'body' && token) {
        builtJson['token'] = token;
    } else if (authType === 'bearer') {
        builtJson['header_auth'] = 'Bearer';
    }

    const json = JSON.stringify(builtJson, null, 2);
    document.getElementById('json_preview_code').textContent = Object.keys(builtJson).length === 0
        ? '// ফিল্ডগুলো পূরণ করলে এখানে JSON তৈরি হবে'
        : json;
}

// ===== COPY BUILT JSON =====
function copyBuiltJson() {
    const json = JSON.stringify(builtJson, null, 2);
    if (!json || Object.keys(builtJson).length === 0) {
        alert('অনুগ্রহ করে আগে অন্তত একটি ফিল্ড পূরণ করুন।');
        return;
    }
    navigator.clipboard.writeText(json).then(() => {
        const btn = document.getElementById('copyBuiltJson');
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
        btn.classList.add('bg-emerald-600', 'border-emerald-500', 'text-white');
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy mr-1"></i> Copy JSON';
            btn.classList.remove('bg-emerald-600', 'border-emerald-500', 'text-white');
        }, 2000);
    });
}

// ===== APPLY TO SETTINGS =====
function applyToSettings() {
    const json = JSON.stringify(builtJson, null, 2);
    if (!json || Object.keys(builtJson).length === 0) {
        alert('অনুগ্রহ করে আগে অন্তত একটি ফিল্ড পূরণ করুন।');
        return;
    }
    sessionStorage.setItem('pendingApiMapping', json);
    window.location.href = '{{ route("settings.index") }}#custom-api-section';
}

// ===== COPY CODE BLOCKS =====
function copyCode(btn) {
    const pre = btn.closest('.glass-card, pre')?.querySelector('pre') || btn.closest('pre');
    if (!pre) return;
    const text = pre.textContent.replace(/Copy$|Copied!$/g, '').trim();
    navigator.clipboard.writeText(text).then(() => {
        btn.textContent = 'Copied!';
        btn.classList.add('copied');
        setTimeout(() => { btn.textContent = 'Copy'; btn.classList.remove('copied'); }, 2000);
    });
}

// ===== FAQ TOGGLE =====
function toggleFaq(btn) {
    const body = btn.nextElementSibling;
    const icon = btn.querySelector('i.fa-chevron-down');
    body.classList.toggle('hidden');
    if (icon) icon.classList.toggle('rotate-180');
}

// ===== SCROLL TO SECTION =====
function scrollToSection(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    event.currentTarget?.classList.add('active');
}

// ===== MOBILE SIDEBAR TOGGLE =====
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.getElementById('sidebar')?.classList.toggle('open');
});

document.addEventListener('DOMContentLoaded', () => {
    buildJson();
    const sections = document.querySelectorAll('section[id]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                const active = document.querySelector(`.sidebar-link[onclick*="${entry.target.id}"]`);
                if (active) active.classList.add('active');
            }
        });
    }, { threshold: 0.2 });
    sections.forEach(s => observer.observe(s));
});
</script>
</body>
</html>
