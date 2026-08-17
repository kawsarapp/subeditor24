<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom API Integration Guide — Newsmanage24</title>
    <meta name="description" content="Complete guide to connect your website via Custom API Mapping in Newsmanage24. Learn all payload fields, authentication methods, and real-world examples.">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #0d1117; color: #e6edf3; }
        .code-font { font-family: 'Fira Code', monospace; }

        /* Gradient Text */
        .gradient-text { background: linear-gradient(135deg, #6366f1, #8b5cf6, #06b6d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .gradient-text-green { background: linear-gradient(135deg, #10b981, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Glass Cards */
        .glass-card { background: rgba(22, 27, 34, 0.95); border: 1px solid rgba(48, 54, 61, 0.8); backdrop-filter: blur(20px); }
        .glass-card-light { background: rgba(30, 37, 47, 0.6); border: 1px solid rgba(48, 54, 61, 0.6); }

        /* Nav */
        .nav-blur { background: rgba(13, 17, 23, 0.9); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(48, 54, 61, 0.8); }

        /* Gradient Background Orbs */
        .orb-1 { position: fixed; top: -150px; left: -150px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(99, 102, 241, 0.12) 0%, transparent 70%); pointer-events: none; }
        .orb-2 { position: fixed; bottom: -150px; right: -150px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(6, 182, 212, 0.08) 0%, transparent 70%); pointer-events: none; }
        .orb-3 { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 800px; height: 800px; background: radial-gradient(circle, rgba(139, 92, 246, 0.04) 0%, transparent 70%); pointer-events: none; }

        /* Sidebar Nav */
        .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 8px 14px; border-radius: 10px; font-size: 13px; font-weight: 500; color: #8b949e; text-decoration: none; transition: all 0.2s; cursor: pointer; }
        .sidebar-link:hover, .sidebar-link.active { background: rgba(99, 102, 241, 0.12); color: #a5b4fc; }
        .sidebar-link .dot { width: 6px; height: 6px; border-radius: 50%; background: #30363d; flex-shrink: 0; transition: all 0.2s; }
        .sidebar-link:hover .dot, .sidebar-link.active .dot { background: #6366f1; box-shadow: 0 0 8px rgba(99, 102, 241, 0.6); }

        /* Code Block */
        pre { background: #010409; border: 1px solid #30363d; border-radius: 12px; padding: 20px 24px; overflow-x: auto; position: relative; }
        pre code { font-family: 'Fira Code', monospace; font-size: 13.5px; line-height: 1.7; }
        .copy-btn { position: absolute; top: 12px; right: 12px; background: rgba(48, 54, 61, 0.8); border: 1px solid #30363d; color: #8b949e; padding: 4px 12px; border-radius: 6px; font-size: 11px; cursor: pointer; transition: all 0.2s; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 600; }
        .copy-btn:hover { background: rgba(99, 102, 241, 0.3); color: #a5b4fc; border-color: #6366f1; }
        .copy-btn.copied { background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: #10b981; }

        /* Syntax highlighting */
        .json-key { color: #79c0ff; }
        .json-str { color: #a5d6ff; }
        .json-val { color: #ffa657; }
        .json-bool { color: #ff7b72; }
        .json-comment { color: #8b949e; font-style: italic; }

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

        /* Field Card */
        .field-card { background: rgba(22, 27, 34, 0.8); border: 1px solid #30363d; border-radius: 16px; padding: 20px 24px; transition: all 0.3s; }
        .field-card:hover { border-color: rgba(99, 102, 241, 0.4); transform: translateY(-1px); box-shadow: 0 8px 30px rgba(99, 102, 241, 0.08); }

        /* Timeline Step */
        .step-line { position: absolute; left: 18px; top: 44px; bottom: -8px; width: 2px; background: linear-gradient(to bottom, #30363d, transparent); }

        /* Callout */
        .callout-info { background: rgba(6, 182, 212, 0.08); border-left: 3px solid #06b6d4; border-radius: 0 10px 10px 0; padding: 14px 18px; }
        .callout-warn { background: rgba(245, 158, 11, 0.08); border-left: 3px solid #f59e0b; border-radius: 0 10px 10px 0; padding: 14px 18px; }
        .callout-success { background: rgba(16, 185, 129, 0.08); border-left: 3px solid #10b981; border-radius: 0 10px 10px 0; padding: 14px 18px; }
        .callout-danger { background: rgba(239, 68, 68, 0.08); border-left: 3px solid #ef4444; border-radius: 0 10px 10px 0; padding: 14px 18px; }

        /* Section scroll margin */
        section { scroll-margin-top: 80px; }

        /* Animated underline */
        .nav-link-underline { position: relative; }
        .nav-link-underline::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 0; height: 2px; background: #6366f1; transition: width 0.3s; }
        .nav-link-underline:hover::after { width: 100%; }

        /* Mobile sidebar toggle */
        @media (max-width: 1024px) { .sidebar { display: none; } .sidebar.open { display: block; } }
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
            <!-- Mobile sidebar toggle -->
            <button id="sidebarToggle" class="lg:hidden p-2 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors">
                <i class="fas fa-bars"></i>
            </button>
            <a href="/" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                    <i class="fa-solid fa-bolt text-white text-sm"></i>
                </div>
                <div>
                    <span class="font-bold text-lg text-white">Newsmanage<span class="text-indigo-400">24</span></span>
                    <span class="hidden sm:inline text-slate-500 mx-2">·</span>
                    <span class="hidden sm:inline text-slate-400 text-sm font-medium">API Integration Guide</span>
                </div>
            </a>
        </div>
        <div class="flex items-center gap-3">
            <span class="hidden sm:flex items-center gap-1.5 text-xs text-emerald-400 bg-emerald-400/10 border border-emerald-400/20 px-3 py-1.5 rounded-full font-bold">
                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                Live Docs
            </span>
            @auth
            <a href="{{ route('settings.index') }}" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 px-4 py-1.5 rounded-lg transition-colors">
                ← Back to Settings
            </a>
            @else
            <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 px-4 py-1.5 rounded-lg transition-colors">
                Login →
            </a>
            @endauth
        </div>
    </div>
</nav>

<!-- ===== MAIN LAYOUT ===== -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 flex gap-8 pt-8 pb-20">

    <!-- ===== LEFT SIDEBAR ===== -->
    <aside id="sidebar" class="sidebar w-64 flex-shrink-0">
        <div class="sticky top-24">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3 px-2">On This Page</p>
            <nav class="space-y-0.5">
                <a class="sidebar-link active" onclick="scrollToSection('overview')"><span class="dot"></span>Overview</a>
                <a class="sidebar-link" onclick="scrollToSection('how-it-works')"><span class="dot"></span>How It Works</a>
                <a class="sidebar-link" onclick="scrollToSection('quick-start')"><span class="dot"></span>Quick Start (3 Steps)</a>
                <div class="my-3 border-t border-slate-700/50"></div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-2">Mapping Keys</p>
                <a class="sidebar-link" onclick="scrollToSection('content-fields')"><span class="dot"></span>Content Fields</a>
                <a class="sidebar-link" onclick="scrollToSection('auth-fields')"><span class="dot"></span>Authentication</a>
                <a class="sidebar-link" onclick="scrollToSection('extra-fields')"><span class="dot"></span>Extra / Static Fields</a>
                <a class="sidebar-link" onclick="scrollToSection('response-parsing')"><span class="dot"></span>Response Parsing</a>
                <div class="my-3 border-t border-slate-700/50"></div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-2">API Response</p>
                <a class="sidebar-link" onclick="scrollToSection('expected-response')"><span class="dot"></span>Expected Response Format</a>
                <div class="my-3 border-t border-slate-700/50"></div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-2">Category Fetch</p>
                <a class="sidebar-link" onclick="scrollToSection('category-fetch')"><span class="dot"></span>How Category Fetch Works</a>
                <a class="sidebar-link" onclick="scrollToSection('category-custom-url')"><span class="dot"></span>Custom Category URL</a>
                <a class="sidebar-link" onclick="scrollToSection('category-default-url')"><span class="dot"></span>Default Laravel URL</a>
                <a class="sidebar-link" onclick="scrollToSection('category-response-format')"><span class="dot"></span>Category Response Format</a>
                <div class="my-3 border-t border-slate-700/50"></div>
                <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest mb-2 px-2">Examples</p>
                <a class="sidebar-link" onclick="scrollToSection('example-1')"><span class="dot"></span>Laravel / Custom API</a>
                <a class="sidebar-link" onclick="scrollToSection('example-2')"><span class="dot"></span>Bearer Auth API</a>
                <a class="sidebar-link" onclick="scrollToSection('example-3')"><span class="dot"></span>TV / Media Portal</a>
                <a class="sidebar-link" onclick="scrollToSection('example-4')"><span class="dot"></span>Minimal Setup</a>
                <div class="my-3 border-t border-slate-700/50"></div>
                <a class="sidebar-link" onclick="scrollToSection('ready-api')"><span class="dot"></span>Build Your JSON →</a>
                <a class="sidebar-link" onclick="scrollToSection('faq')"><span class="dot"></span>FAQ</a>
            </nav>
        </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="flex-1 min-w-0 space-y-16">

        <!-- ===== HERO ===== -->
        <section id="overview">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1 rounded-full">📡 Integration Guide</span>
                <span class="text-xs text-slate-500">v2.0</span>
            </div>
            <h1 class="text-4xl sm:text-5xl font-black text-white leading-tight mb-4">
                Custom API<br>
                <span class="gradient-text">Mapping Guide</span>
            </h1>
            <p class="text-lg text-slate-400 max-w-2xl leading-relaxed mb-8">
                Connect <strong class="text-slate-300">any website</strong> to Newsmanage24 — your own Laravel app, custom CMS, TV portal, or any backend — using a simple JSON configuration called <strong class="text-indigo-400">Custom API Mapping</strong>.
            </p>

            <!-- Feature Pills -->
            <div class="flex flex-wrap gap-3 mb-10">
                <span class="flex items-center gap-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 px-4 py-2 rounded-full">
                    <i class="fas fa-upload text-indigo-400 text-xs"></i> Multipart Image Upload
                </span>
                <span class="flex items-center gap-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 px-4 py-2 rounded-full">
                    <i class="fas fa-key text-amber-400 text-xs"></i> Bearer or Body Token Auth
                </span>
                <span class="flex items-center gap-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 px-4 py-2 rounded-full">
                    <i class="fas fa-sliders text-emerald-400 text-xs"></i> Dynamic Field Mapping
                </span>
                <span class="flex items-center gap-2 text-sm font-medium text-slate-300 bg-slate-800 border border-slate-700 px-4 py-2 rounded-full">
                    <i class="fas fa-infinity text-purple-400 text-xs"></i> Any Framework Compatible
                </span>
            </div>

            <!-- Quick Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="glass-card rounded-2xl p-5">
                    <div class="w-10 h-10 bg-indigo-500/10 border border-indigo-500/20 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-plug text-indigo-400"></i>
                    </div>
                    <p class="font-bold text-white text-sm mb-1">Any API Endpoint</p>
                    <p class="text-xs text-slate-500 leading-relaxed">Point to your own custom endpoint — no changes needed in Newsmanage24's core system.</p>
                </div>
                <div class="glass-card rounded-2xl p-5">
                    <div class="w-10 h-10 bg-purple-500/10 border border-purple-500/20 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-code text-purple-400"></i>
                    </div>
                    <p class="font-bold text-white text-sm mb-1">JSON Configuration</p>
                    <p class="text-xs text-slate-500 leading-relaxed">A single JSON object maps our internal field names to your API's expected parameter names.</p>
                </div>
                <div class="glass-card rounded-2xl p-5">
                    <div class="w-10 h-10 bg-cyan-500/10 border border-cyan-500/20 rounded-xl flex items-center justify-center mb-3">
                        <i class="fas fa-image text-cyan-400"></i>
                    </div>
                    <p class="font-bold text-white text-sm mb-1">Real Image Upload</p>
                    <p class="text-xs text-slate-500 leading-relaxed">We download the image and upload it as a physical file via multipart form — not just a URL.</p>
                </div>
            </div>
        </section>

        <!-- ===== HOW IT WORKS ===== -->
        <section id="how-it-works">
            <h2 class="text-2xl font-bold text-white mb-2">How It Works</h2>
            <p class="text-slate-400 mb-8">When a news item is published, here's what happens behind the scenes:</p>

            <div class="space-y-4">
                <!-- Step 1 -->
                <div class="relative flex gap-5">
                    <div class="step-line"></div>
                    <div class="w-9 h-9 flex-shrink-0 bg-indigo-500/15 border border-indigo-500/30 rounded-full flex items-center justify-center text-sm font-black text-indigo-400 z-10">1</div>
                    <div class="flex-1 pb-6">
                        <p class="font-bold text-white mb-1">Check Configuration</p>
                        <p class="text-sm text-slate-400">System reads your <code class="code-font text-indigo-300 bg-slate-800 px-1.5 py-0.5 rounded">custom_api_url</code> and <code class="code-font text-indigo-300 bg-slate-800 px-1.5 py-0.5 rounded">custom_api_mapping</code> from your Settings. If both exist, Custom API mode is activated.</p>
                    </div>
                </div>
                <!-- Step 2 -->
                <div class="relative flex gap-5">
                    <div class="step-line"></div>
                    <div class="w-9 h-9 flex-shrink-0 bg-purple-500/15 border border-purple-500/30 rounded-full flex items-center justify-center text-sm font-black text-purple-400 z-10">2</div>
                    <div class="flex-1 pb-6">
                        <p class="font-bold text-white mb-1">Build the Payload</p>
                        <p class="text-sm text-slate-400">Your mapping JSON tells us to rename fields. For example, if you write <code class="code-font text-amber-300 bg-slate-800 px-1.5 py-0.5 rounded">"title": "news_title"</code>, we send your API a field named <strong class="text-white">news_title</strong> instead of <strong class="text-white">title</strong>.</p>
                    </div>
                </div>
                <!-- Step 3 -->
                <div class="relative flex gap-5">
                    <div class="step-line"></div>
                    <div class="w-9 h-9 flex-shrink-0 bg-cyan-500/15 border border-cyan-500/30 rounded-full flex items-center justify-center text-sm font-black text-cyan-400 z-10">3</div>
                    <div class="flex-1 pb-6">
                        <p class="font-bold text-white mb-1">Download & Upload Image</p>
                        <p class="text-sm text-slate-400">If <code class="code-font text-cyan-300 bg-slate-800 px-1.5 py-0.5 rounded">"image"</code> is in your mapping, we download the thumbnail from the source and upload it as a <strong class="text-white">real file</strong> (multipart/form-data) — perfect for APIs that don't accept image URLs.</p>
                    </div>
                </div>
                <!-- Step 4 -->
                <div class="relative flex gap-5">
                    <div class="w-9 h-9 flex-shrink-0 bg-emerald-500/15 border border-emerald-500/30 rounded-full flex items-center justify-center text-sm font-black text-emerald-400 z-10">4</div>
                    <div class="flex-1">
                        <p class="font-bold text-white mb-1">Parse Response & Save</p>
                        <p class="text-sm text-slate-400">We read your API's response, extract the post ID and live URL, and save them in our database for tracking. The timeout is <strong class="text-white">120 seconds</strong> to handle slow servers.</p>
                    </div>
                </div>
            </div>

            <div class="callout-info mt-6 rounded-r-xl">
                <p class="text-sm text-cyan-300 font-bold mb-1"><i class="fas fa-info-circle mr-1"></i> Request Type</p>
                <p class="text-sm text-cyan-200/80">All Custom API requests are sent as <strong>multipart/form-data</strong> — compatible with all standard web frameworks (Laravel, CodeIgniter, Django, Express.js, PHP, etc.)</p>
            </div>
        </section>

        <!-- ===== QUICK START ===== -->
        <section id="quick-start">
            <h2 class="text-2xl font-bold text-white mb-2">Quick Start — 3 Steps</h2>
            <p class="text-slate-400 mb-6">Get connected in under 5 minutes.</p>

            <div class="space-y-4">
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <span class="text-2xl font-black gradient-text">01</span>
                        <div class="flex-1">
                            <p class="font-bold text-white mb-2">Go to Settings → Custom API Mapping section</p>
                            <p class="text-sm text-slate-400">In your Newsmanage24 dashboard, navigate to <strong class="text-slate-300">Settings</strong> and scroll to the <strong class="text-indigo-400">⚙️ Custom API Mapping</strong> section (click to expand).</p>
                        </div>
                    </div>
                </div>
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <span class="text-2xl font-black gradient-text">02</span>
                        <div class="flex-1">
                            <p class="font-bold text-white mb-2">Fill in your API URL + API Token</p>
                            <p class="text-sm text-slate-400 mb-3">In the <strong class="text-slate-300">🚀 Laravel Website Connection</strong> section above it, enter your API endpoint URL and the shared secret token.</p>
                            <div class="glass-card-light rounded-xl p-4 text-sm">
                                <p class="text-slate-400 mb-1">Custom News Post API URL:</p>
                                <code class="code-font text-emerald-400">https://your-site.com/api/news</code>
                                <p class="text-slate-400 mt-3 mb-1">API Token (shared secret):</p>
                                <code class="code-font text-amber-400">your-secret-token-here</code>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-start gap-4">
                        <span class="text-2xl font-black gradient-text">03</span>
                        <div class="flex-1">
                            <p class="font-bold text-white mb-2">Paste your JSON Mapping and Save</p>
                            <p class="text-sm text-slate-400 mb-3">In the Payload JSON Mapping textarea, enter the mapping that matches your API's expected fields. See examples below. Then click <strong class="text-white">💾 Save Settings</strong>.</p>
                            <div class="callout-success rounded-r-xl">
                                <p class="text-sm text-emerald-300"><i class="fas fa-check-circle mr-1"></i> That's it! Next time news is published, it will automatically post to your API.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== CONTENT FIELDS ===== -->
        <section id="content-fields">
            <h2 class="text-2xl font-bold text-white mb-2">Content Mapping Keys</h2>
            <p class="text-slate-400 mb-6">These are the main content fields you can map. The <strong class="text-white">left side</strong> (key) is our internal name; the <strong class="text-white">right side</strong> (value) is the field name your API expects.</p>

            <div class="overflow-hidden glass-card rounded-2xl mb-6">
                <table>
                    <thead>
                        <tr>
                            <th>Mapping Key</th>
                            <th>What We Send</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"title"</code></td>
                            <td>
                                <p class="text-slate-200 font-medium">AI-rewritten news headline</p>
                                <p class="text-xs text-slate-500 mt-0.5">Plain text string. Maximum ~200 characters.</p>
                            </td>
                            <td><span class="code-font text-xs text-slate-400">string</span></td>
                            <td><span class="badge-required">Required</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"content"</code></td>
                            <td>
                                <p class="text-slate-200 font-medium">News body content (HTML)</p>
                                <p class="text-xs text-slate-500 mt-0.5">Full article HTML with paragraphs, headings, etc.</p>
                            </td>
                            <td><span class="code-font text-xs text-slate-400">string (HTML)</span></td>
                            <td><span class="badge-required">Required</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"image"</code></td>
                            <td>
                                <p class="text-slate-200 font-medium">Thumbnail — physical file upload</p>
                                <p class="text-xs text-slate-500 mt-0.5">We download from source and upload as multipart file. Not a URL.</p>
                            </td>
                            <td><span class="code-font text-xs text-slate-400">file (multipart)</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"category"</code></td>
                            <td>
                                <p class="text-slate-200 font-medium">Category ID(s) from mapping table</p>
                                <p class="text-xs text-slate-500 mt-0.5">Can be single or array. To send as array, add <code class="code-font text-amber-400">[]</code> to the value: <code class="code-font text-cyan-400">"category[]"</code></p>
                            </td>
                            <td><span class="code-font text-xs text-slate-400">int / int[]</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"tags"</code></td>
                            <td>
                                <p class="text-slate-200 font-medium">Hashtags string</p>
                                <p class="text-xs text-slate-500 mt-0.5">Comma-separated or space-separated tags. Example: <code class="code-font text-slate-400">bangladesh,politics,news</code></p>
                            </td>
                            <td><span class="code-font text-xs text-slate-400">string</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"date"</code></td>
                            <td>
                                <p class="text-slate-200 font-medium">Publish date (today's date)</p>
                                <p class="text-xs text-slate-500 mt-0.5">Format: <code class="code-font text-amber-400">YYYY-MM-DD</code> (e.g. 2026-04-21)</p>
                            </td>
                            <td><span class="code-font text-xs text-slate-400">string (date)</span></td>
                            <td><span class="badge-optional">Optional</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Example of category array -->
            <div class="callout-warn rounded-r-xl">
                <p class="text-sm text-amber-300 font-bold mb-1"><i class="fas fa-triangle-exclamation mr-1"></i> Category as Array</p>
                <p class="text-sm text-amber-200/80">If your API expects category IDs as an array (e.g. <code class="code-font text-amber-300">category_ids[]</code>), add <code class="code-font">[]</code> to the value in your mapping:</p>
                <pre class="mt-3 !py-3 !px-4"><code><span class="json-key">"category"</span><span class="text-white">: </span><span class="json-str">"category_ids[]"</span>  <span class="json-comment">// Sent as: category_ids[]=5&category_ids[]=12</span></code></pre>
            </div>
        </section>

        <!-- ===== AUTH FIELDS ===== -->
        <section id="auth-fields">
            <h2 class="text-2xl font-bold text-white mb-2">Authentication Keys</h2>
            <p class="text-slate-400 mb-6">Two authentication methods are supported. Use only one at a time.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-6">
                <!-- Method 1: Body Token -->
                <div class="field-card">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-amber-500/10 border border-amber-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-lock text-amber-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-white text-sm">Method 1: Body Token</p>
                            <span class="badge-optional">Recommended</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 mb-3">The API token is sent as part of the POST body (multipart field).</p>
                    <pre class="!py-3 !px-4 text-xs"><code><span class="json-key">"token"</span><span class="text-white">: </span><span class="json-str">"api_key"</span></code></pre>
                    <p class="text-xs text-slate-500 mt-2">Your API receives: field <code class="code-font text-amber-400">api_key</code> = your token value</p>
                </div>

                <!-- Method 2: Bearer Header -->
                <div class="field-card">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 bg-purple-500/10 border border-purple-500/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-shield text-purple-400 text-sm"></i>
                        </div>
                        <div>
                            <p class="font-bold text-white text-sm">Method 2: Bearer Header</p>
                            <span class="badge-optional">For JWT APIs</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 mb-3">Token is sent in the HTTP <code class="code-font text-purple-300">Authorization</code> header.</p>
                    <pre class="!py-3 !px-4 text-xs"><code><span class="json-key">"header_auth"</span><span class="text-white">: </span><span class="json-str">"Bearer"</span></code></pre>
                    <p class="text-xs text-slate-500 mt-2">Your API receives: <code class="code-font text-purple-400">Authorization: Bearer {token}</code></p>
                </div>
            </div>

            <div class="callout-danger rounded-r-xl">
                <p class="text-sm text-red-300 font-bold mb-1"><i class="fas fa-xmark-circle mr-1"></i> Don't Use Both Together</p>
                <p class="text-sm text-red-200/80">Never add both <code class="code-font">"token"</code> and <code class="code-font">"header_auth"</code> to the same mapping. If <code class="code-font">"header_auth": "Bearer"</code> is present, it takes priority and no body token will be sent. However, <code class="code-font">"token"</code> alone works as a body field — completely safe.</p>
            </div>
        </section>

        <!-- ===== EXTRA FIELDS ===== -->
        <section id="extra-fields">
            <h2 class="text-2xl font-bold text-white mb-2">Extra / Static Fields</h2>
            <p class="text-slate-400 mb-6">Need to send hardcoded values that don't change per post? Use the <code class="code-font text-amber-300 bg-slate-800 px-1.5 py-0.5 rounded">extra</code> key.</p>

            <div class="glass-card rounded-2xl p-6 mb-6">
                <p class="text-sm font-bold text-slate-300 mb-3">Format:</p>
                <div class="relative">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre><code><span class="text-slate-500">{</span>
  <span class="json-key">"title"</span><span class="text-white">: </span><span class="json-str">"news_title"</span><span class="text-white">,</span>
  <span class="json-key">"content"</span><span class="text-white">: </span><span class="json-str">"body"</span><span class="text-white">,</span>
  <span class="json-key">"extra"</span><span class="text-white">: </span><span class="text-slate-500">{</span>
    <span class="json-key">"news_type"</span><span class="text-white">: </span><span class="json-str">"2"</span><span class="text-white">,</span>         <span class="json-comment">// always sends news_type=2</span>
    <span class="json-key">"source"</span><span class="text-white">: </span><span class="json-str">"automation"</span><span class="text-white">,</span>   <span class="json-comment">// always sends source=automation</span>
    <span class="json-key">"priority"</span><span class="text-white">: </span><span class="json-str">"1"</span>            <span class="json-comment">// always sends priority=1</span>
  <span class="text-slate-500">}</span>
<span class="text-slate-500">}</span></code></pre>
                </div>
                <div class="callout-info rounded-r-xl mt-4">
                    <p class="text-xs text-cyan-300"><i class="fas fa-info-circle mr-1"></i> All values in <code class="code-font">extra</code> must be <strong>strings</strong>. Numbers should be quoted: <code class="code-font">"1"</code> not <code class="code-font">1</code>.</p>
                </div>
            </div>
        </section>

        <!-- ===== RESPONSE PARSING ===== -->
        <section id="response-parsing">
            <h2 class="text-2xl font-bold text-white mb-2">Response Parsing Keys</h2>
            <p class="text-slate-400 mb-6">Tell our system how to read your API's response to extract the post ID and live URL.</p>

            <div class="overflow-hidden glass-card rounded-2xl mb-6">
                <table>
                    <thead>
                        <tr>
                            <th>Mapping Key</th>
                            <th>Purpose</th>
                            <th>Default Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="code-font text-sky-400 text-sm">"response_id_key"</code></td>
                            <td>
                                <p class="text-slate-200 font-medium">Which field in your response JSON holds the new post ID</p>
                                <p class="text-xs text-slate-500 mt-1">We already auto-check: <code class="code-font text-slate-400">id</code>, <code class="code-font text-slate-400">data.post_id</code>, <code class="code-font text-slate-400">post_id</code>. Only set this if yours is different.</p>
                            </td>
                            <td><code class="code-font text-amber-400 text-sm">"post_id"</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="callout-info rounded-r-xl">
                <p class="text-sm text-cyan-300 font-bold mb-2"><i class="fas fa-lightbulb mr-1"></i> Auto-detected Response Keys</p>
                <p class="text-sm text-cyan-200/80 mb-2">Even without <code class="code-font">response_id_key</code>, we automatically try to find the post ID in:</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
                    <code class="code-font text-xs bg-slate-800 px-2 py-1.5 rounded text-slate-300">"post_id"</code>
                    <code class="code-font text-xs bg-slate-800 px-2 py-1.5 rounded text-slate-300">"id"</code>
                    <code class="code-font text-xs bg-slate-800 px-2 py-1.5 rounded text-slate-300">"data.post_id"</code>
                    <code class="code-font text-xs bg-slate-800 px-2 py-1.5 rounded text-slate-300">"data.id"</code>
                </div>
                <p class="text-sm text-cyan-200/80 mt-3">For live URL, we try: <code class="code-font text-cyan-400">"live_url"</code>, <code class="code-font text-cyan-400">"link"</code>, <code class="code-font text-cyan-400">"url"</code>, <code class="code-font text-cyan-400">"data.URLAlies"</code></p>
            </div>
        </section>

        <!-- ===== EXPECTED RESPONSE ===== -->
        <section id="expected-response">
            <h2 class="text-2xl font-bold text-white mb-2">Expected Response Format</h2>
            <p class="text-slate-400 mb-6">Your API should return a <strong class="text-white">JSON response</strong> with HTTP status <strong class="text-emerald-400">200–299</strong> for success. Here are the supported formats:</p>

            <div class="space-y-4">
                <!-- Best Format -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full"></span>
                        <p class="font-bold text-white text-sm">Best Format (Recommended)</p>
                        <span class="badge-special">✨ Ideal</span>
                    </div>
                    <div class="relative">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre><code><span class="text-slate-500">{</span>
  <span class="json-key">"post_id"</span><span class="text-white">: </span><span class="json-val">123</span><span class="text-white">,</span>
  <span class="json-key">"live_url"</span><span class="text-white">: </span><span class="json-str">"https://your-site.com/news/breaking-story-123"</span>
<span class="text-slate-500">}</span></code></pre>
                    </div>
                </div>

                <!-- Nested Data -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 bg-blue-400 rounded-full"></span>
                        <p class="font-bold text-white text-sm">Nested Data Format</p>
                        <span class="badge-optional">Supported</span>
                    </div>
                    <div class="relative">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre><code><span class="text-slate-500">{</span>
  <span class="json-key">"status"</span><span class="text-white">: </span><span class="json-str">"success"</span><span class="text-white">,</span>
  <span class="json-key">"data"</span><span class="text-white">: </span><span class="text-slate-500">{</span>
    <span class="json-key">"post_id"</span><span class="text-white">: </span><span class="json-val">456</span><span class="text-white">,</span>
    <span class="json-key">"URLAlies"</span><span class="text-white">: </span><span class="json-str">"https://your-site.com/news/456"</span>  <span class="json-comment">// for Islamic TV style APIs</span>
  <span class="text-slate-500">}</span>
<span class="text-slate-500">}</span></code></pre>
                    </div>
                </div>

                <!-- Minimal -->
                <div class="glass-card rounded-2xl p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <span class="w-2 h-2 bg-slate-400 rounded-full"></span>
                        <p class="font-bold text-white text-sm">Minimal Format</p>
                        <span class="badge-optional">Supported</span>
                    </div>
                    <div class="relative">
                        <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                        <pre><code><span class="text-slate-500">{</span>
  <span class="json-key">"id"</span><span class="text-white">: </span><span class="json-val">789</span>   <span class="json-comment">// live URL will be auto-built: siteurl/prefix/789</span>
<span class="text-slate-500">}</span></code></pre>
                    </div>
                </div>
            </div>

            <div class="callout-warn rounded-r-xl mt-5">
                <p class="text-sm text-amber-300 font-bold mb-1"><i class="fas fa-triangle-exclamation mr-1"></i> Non-success HTTP Status</p>
                <p class="text-sm text-amber-200/80">If your API returns HTTP <code class="code-font text-amber-300">400</code>, <code class="code-font text-amber-300">401</code>, <code class="code-font text-amber-300">500</code>, etc., our system marks the post as <strong>failed</strong> and logs the error. Make sure your API returns 2xx on success.</p>
            </div>
        </section>

        <!-- ===== CATEGORY FETCH ===== -->
        <section id="category-fetch">
            <div class="mb-3 flex items-center gap-2">
                <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1 rounded-full">📂 Category System</span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">How Category Fetch Works</h2>
            <p class="text-slate-400 mb-6">Before you can map categories in Settings, our system needs to fetch your website's category list. Here's exactly how it works — in order of priority.</p>

            <!-- Priority Flow -->
            <div class="glass-card rounded-2xl p-6 mb-6">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-5">Priority Order (Top = Highest)</p>
                <div class="space-y-3">
                    <div class="flex items-start gap-4 p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-xl">
                        <span class="w-7 h-7 flex-shrink-0 bg-emerald-500/15 border border-emerald-500/30 rounded-full flex items-center justify-center text-xs font-black text-emerald-400">1</span>
                        <div>
                            <p class="font-bold text-emerald-300 text-sm">Custom Category URL <span class="text-xs text-slate-500 font-normal ml-2">(if set in Settings)</span></p>
                            <p class="text-xs text-slate-400 mt-1">Settings → <code class="code-font text-emerald-400">Custom Category Fetch URL</code> field এ আপনার নিজের URL দিলে এটা সবার আগে ব্যবহার হবে। Auth: <code class="code-font text-purple-300">Authorization: Bearer {token}</code> header পাঠানো হয়।</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 p-4 bg-blue-500/5 border border-blue-500/20 rounded-xl">
                        <span class="w-7 h-7 flex-shrink-0 bg-blue-500/15 border border-blue-500/30 rounded-full flex items-center justify-center text-xs font-black text-blue-400">2</span>
                        <div>
                            <p class="font-bold text-blue-300 text-sm">Default Laravel Category API <span class="text-xs text-slate-500 font-normal ml-2">(auto-built URL)</span></p>
                            <p class="text-xs text-slate-400 mt-1">Custom URL না থাকলে, <code class="code-font text-blue-300">{laravel_site_url}/api/get-categories?token={api_token}</code> এ GET request পাঠানো হয়। এই endpoint টি আপনাকে নিজে তৈরি করতে হবে।</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4 p-4 bg-slate-700/30 border border-slate-600/30 rounded-xl">
                        <span class="w-7 h-7 flex-shrink-0 bg-slate-600/30 border border-slate-600/50 rounded-full flex items-center justify-center text-xs font-black text-slate-400">3</span>
                        <div>
                            <p class="font-bold text-slate-300 text-sm">WordPress Fallback</p>
                            <p class="text-xs text-slate-400 mt-1">উপরের দুটো কাজ না করলে, WordPress API থেকে ক্যাটাগরি আনার চেষ্টা করা হয় (যদি WP credentials থাকে)।</p>
                        </div>
                    </div>
                </div>

                <div class="callout-info rounded-r-xl mt-5">
                    <p class="text-sm text-cyan-300 font-bold mb-1"><i class="fas fa-clock mr-1"></i> Cache: 24 Hours</p>
                    <p class="text-sm text-cyan-200/80">Category list একবার fetch হলে <strong>24 ঘণ্টা cache</strong> হয়ে থাকে। Settings পেজে <strong>🔄 Refresh Categories</strong> বাটনে ক্লিক করলে cache clear হয়ে নতুন করে fetch হবে।</p>
                </div>
            </div>
        </section>

        <!-- ===== CUSTOM CATEGORY URL ===== -->
        <section id="category-custom-url">
            <h3 class="text-xl font-bold text-white mb-2">① Custom Category URL <span class="text-emerald-400">(Recommended)</span></h3>
            <p class="text-slate-400 mb-5">আপনার নিজস্ব API endpoint দিয়ে category list পাঠান। Settings এর <strong class="text-slate-300">Custom Category Fetch URL</strong> ফিল্ডে URL দিন।</p>

            <div class="glass-card rounded-2xl p-6 mb-5">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Request Details</p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                        <span class="text-xs font-bold text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded">GET</span>
                        <code class="code-font text-slate-300 text-sm">https://your-site.com/api/your-categories-endpoint</code>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                        <span class="text-xs font-bold text-purple-400 bg-purple-400/10 px-2 py-1 rounded">Header</span>
                        <code class="code-font text-purple-300 text-sm">Authorization: Bearer {your api_token}</code>
                    </div>
                </div>
            </div>

            <div class="glass-card rounded-2xl p-6 mb-5">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">আপনার API যে Response দিতে হবে</p>

                <p class="text-sm text-slate-400 mb-3">আমরা <strong class="text-white">দুটো format</strong> বুঝি:</p>

                <div class="space-y-4">
                    <!-- Format 1: Nested data -->
                    <div>
                        <p class="text-xs font-bold text-emerald-300 mb-2">✅ Format 1 — Nested (TV/Custom CMS Style)</p>
                        <div class="relative">
                            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            <pre><code><span class="text-slate-500">{</span>
  <span class="json-key">"data"</span><span class="text-white">: </span><span class="text-slate-500">[</span>
    <span class="text-slate-500">{</span>
      <span class="json-key">"CategoryID"</span><span class="text-white">: </span><span class="json-val">5</span><span class="text-white">,</span>        <span class="json-comment">// OR: "id": 5</span>
      <span class="json-key">"CategoryName"</span><span class="text-white">: </span><span class="json-str">"রাজনীতি"</span>  <span class="json-comment">// OR: "name": "রাজনীতি"</span>
    <span class="text-slate-500">}</span><span class="text-white">,</span>
    <span class="text-slate-500">{</span>
      <span class="json-key">"CategoryID"</span><span class="text-white">: </span><span class="json-val">12</span><span class="text-white">,</span>
      <span class="json-key">"CategoryName"</span><span class="text-white">: </span><span class="json-str">"খেলাধুলা"</span>
    <span class="text-slate-500">}</span>
  <span class="text-slate-500">]</span>
<span class="text-slate-500">}</span></code></pre>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">System automatically reads <code class="code-font text-emerald-400">CategoryID</code> or <code class="code-font text-emerald-400">id</code> and <code class="code-font text-emerald-400">CategoryName</code> or <code class="code-font text-emerald-400">name</code>.</p>
                    </div>

                    <!-- Format 2: Direct array -->
                    <div>
                        <p class="text-xs font-bold text-blue-300 mb-2">✅ Format 2 — Direct Array</p>
                        <div class="relative">
                            <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                            <pre><code><span class="text-slate-500">[</span>
  <span class="text-slate-500">{</span> <span class="json-key">"id"</span><span class="text-white">: </span><span class="json-val">5</span><span class="text-white">,</span> <span class="json-key">"name"</span><span class="text-white">: </span><span class="json-str">"রাজনীতি"</span> <span class="text-slate-500">}</span><span class="text-white">,</span>
  <span class="text-slate-500">{</span> <span class="json-key">"id"</span><span class="text-white">: </span><span class="json-val">12</span><span class="text-white">,</span> <span class="json-key">"name"</span><span class="text-white">: </span><span class="json-str">"খেলাধুলা"</span> <span class="text-slate-500">}</span>
<span class="text-slate-500">]</span></code></pre>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">Also accepted. Must be a direct JSON array at the root level.</p>
                    </div>
                </div>
            </div>

            <div class="callout-warn rounded-r-xl">
                <p class="text-sm text-amber-300 font-bold mb-1"><i class="fas fa-triangle-exclamation mr-1"></i> Important: Condition for Custom URL</p>
                <p class="text-sm text-amber-200/80">Custom Category URL শুধু তখনই কাজ করবে যখন Settings এ <strong class="text-white">"Enable Posting to Laravel"</strong> checkbox টি চেক করা থাকবে এবং <strong class="text-white">Laravel Site URL</strong> ও <strong class="text-white">API Token</strong> দেওয়া থাকবে।</p>
            </div>
        </section>

        <!-- ===== DEFAULT CATEGORY URL ===== -->
        <section id="category-default-url">
            <h3 class="text-xl font-bold text-white mb-2">② Default Laravel Category API</h3>
            <p class="text-slate-400 mb-5">যদি Custom Category URL না দেওয়া হয়, তাহলে সিস্টেম নিজে থেকেই আপনার <code class="code-font text-indigo-300 bg-slate-800 px-1.5 py-0.5 rounded text-sm">{laravel_site_url}/api/get-categories</code> endpoint এ হিট করবে।</p>

            <div class="glass-card rounded-2xl p-6 mb-5">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Request Details</p>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                        <span class="text-xs font-bold text-emerald-400 bg-emerald-400/10 px-2 py-1 rounded">GET</span>
                        <code class="code-font text-slate-300 text-sm">https://your-site.com/api/get-categories?token={api_token}</code>
                    </div>
                    <div class="flex items-center gap-3 p-3 bg-slate-800 rounded-lg">
                        <span class="text-xs font-bold text-amber-400 bg-amber-400/10 px-2 py-1 rounded">Query Param</span>
                        <code class="code-font text-amber-300 text-sm">token = {your laravel_api_token}</code>
                    </div>
                </div>

                <div class="callout-info rounded-r-xl mt-4">
                    <p class="text-xs text-cyan-300"><i class="fas fa-info-circle mr-1"></i> এই endpoint টি <strong>আপনাকেই তৈরি করতে হবে</strong>। Token দিয়ে verify করুন, তারপর সব category এর list return করুন।</p>
                </div>
            </div>

            <!-- Laravel Sample Implementation -->
            <div class="glass-card rounded-2xl p-6">
                <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">💡 Sample Laravel Route (আপনার সাইটে যোগ করুন)</p>
                <div class="relative">
                    <button class="copy-btn" onclick="copyCode(this)">Copy</button>
                    <pre><code><span class="json-comment">// routes/api.php</span>
<span class="text-white">Route::</span><span class="json-key">get</span><span class="text-white">('/get-categories', function (Request </span><span class="json-val">$request</span><span class="text-white">) {</span>
    <span class="json-comment">// Token verify করুন</span>
    <span class="text-white">if (</span><span class="json-val">$request</span><span class="text-white">->token !== </span><span class="json-str">'your-secret-token'</span><span class="text-white">) {</span>
        <span class="text-white">return response()->json(['error' => 'Unauthorized'], 401);</span>
    <span class="text-white">}</span>
    <span class="json-comment">// Category list return করুন</span>
    <span class="text-white">return response()->json(</span>
        Category::<span class="json-key">select</span><span class="text-white">('id', 'name')->get()</span>
    <span class="text-white">);</span>
<span class="text-white">});</span></code></pre>
                </div>
            </div>
        </section>

        <!-- ===== CATEGORY RESPONSE FORMAT ===== -->
        <section id="category-response-format">
            <h3 class="text-xl font-bold text-white mb-2">Category Response — Field Name Reference</h3>
            <p class="text-slate-400 mb-5">আমরা category response থেকে <code class="code-font text-emerald-300 bg-slate-800 px-1.5 py-0.5 rounded text-sm">id</code> এবং <code class="code-font text-emerald-300 bg-slate-800 px-1.5 py-0.5 rounded text-sm">name</code> বের করি। নিচের যেকোনো field name কাজ করবে:</p>

            <div class="overflow-hidden glass-card rounded-2xl mb-5">
                <table>
                    <thead>
                        <tr>
                            <th>আমরা যা খুঁজি (ID)</th>
                            <th>আমরা যা খুঁজি (Name)</th>
                            <th>কোথায় ব্যবহৃত</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="code-font text-emerald-400 text-sm">CategoryID</code></td>
                            <td><code class="code-font text-emerald-400 text-sm">CategoryName</code></td>
                            <td><span class="text-xs text-slate-400">TV Portal / Custom CMS style</span></td>
                        </tr>
                        <tr>
                            <td><code class="code-font text-blue-400 text-sm">id</code></td>
                            <td><code class="code-font text-blue-400 text-sm">name</code></td>
                            <td><span class="text-xs text-slate-400">Standard REST API / Laravel style</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="callout-success rounded-r-xl">
                <p class="text-sm text-emerald-300 font-bold mb-1"><i class="fas fa-check-circle mr-1"></i> How it appears in Settings</p>
                <p class="text-sm text-emerald-200/80">Fetch সফল হলে, Settings পেজের <strong>ক্যাটাগরি ম্যাপিং</strong> সেকশনে প্রতিটি row এর ড্রপডাউনে আপনার সাইটের category গুলো দেখাবে — <code class="code-font">রাজনীতি (ID: 5)</code> এই format এ। তখন বাম পাশে আমাদের system category এবং ডান পাশে আপনার সাইটের category select করতে পারবেন।</p>
            </div>
        </section>

        <!-- ===== EXAMPLES ===== -->
        <section id="example-1">
            <h2 class="text-2xl font-bold text-white mb-1">Example 1 — Laravel / Custom API</h2>
            <p class="text-slate-400 mb-5">Standard setup for most Laravel or PHP backends that expect common field names.</p>
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs text-slate-500 ml-2 code-font">custom_api_mapping</span>
                    </div>
                    <button class="copy-btn !static" onclick="copyCode(this)">Copy</button>
                </div>
                <pre class="!border-0 !bg-transparent !p-0 !rounded-none"><code><span class="text-slate-500">{</span>
  <span class="json-key">"title"</span><span class="text-white">:    </span><span class="json-str">"news_title"</span><span class="text-white">,</span>       <span class="json-comment">// our title → your news_title</span>
  <span class="json-key">"content"</span><span class="text-white">:  </span><span class="json-str">"news_content"</span><span class="text-white">,</span>     <span class="json-comment">// our HTML body → your news_content</span>
  <span class="json-key">"image"</span><span class="text-white">:    </span><span class="json-str">"thumbnail"</span><span class="text-white">,</span>        <span class="json-comment">// file upload → your thumbnail field</span>
  <span class="json-key">"category"</span><span class="text-white">: </span><span class="json-str">"category_ids[]"</span><span class="text-white">,</span>   <span class="json-comment">// array of IDs → your category_ids[]</span>
  <span class="json-key">"tags"</span><span class="text-white">:     </span><span class="json-str">"post_tags"</span><span class="text-white">,</span>        <span class="json-comment">// hashtags string → your post_tags</span>
  <span class="json-key">"token"</span><span class="text-white">:    </span><span class="json-str">"api_secret"</span><span class="text-white">,</span>       <span class="json-comment">// your API Token → your api_secret field</span>
  <span class="json-key">"response_id_key"</span><span class="text-white">: </span><span class="json-str">"news_id"</span><span class="text-white">  </span><span class="json-comment">// we read response.news_id as the post ID</span>
<span class="text-slate-500">}</span></code></pre>
            </div>
        </section>

        <section id="example-2">
            <h2 class="text-2xl font-bold text-white mb-1">Example 2 — Bearer Token Authentication</h2>
            <p class="text-slate-400 mb-5">For APIs that require JWT or Bearer token in the Authorization header (common in REST APIs, mobile backends).</p>
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs text-slate-500 ml-2 code-font">custom_api_mapping</span>
                    </div>
                    <button class="copy-btn !static" onclick="copyCode(this)">Copy</button>
                </div>
                <pre class="!border-0 !bg-transparent !p-0 !rounded-none"><code><span class="text-slate-500">{</span>
  <span class="json-key">"title"</span><span class="text-white">:       </span><span class="json-str">"title"</span><span class="text-white">,</span>
  <span class="json-key">"content"</span><span class="text-white">:     </span><span class="json-str">"description"</span><span class="text-white">,</span>
  <span class="json-key">"image"</span><span class="text-white">:       </span><span class="json-str">"featured_photo"</span><span class="text-white">,</span>
  <span class="json-key">"category"</span><span class="text-white">:    </span><span class="json-str">"cat_id"</span><span class="text-white">,</span>
  <span class="json-key">"header_auth"</span><span class="text-white">: </span><span class="json-str">"Bearer"</span><span class="text-white">,</span>           <span class="json-comment">// sends: Authorization: Bearer {your token}</span>
  <span class="json-key">"extra"</span><span class="text-white">: </span><span class="text-slate-500">{</span>
    <span class="json-key">"source"</span><span class="text-white">: </span><span class="json-str">"newsmanage24"</span><span class="text-white">,</span>
    <span class="json-key">"auto"</span><span class="text-white">:   </span><span class="json-str">"1"</span>
  <span class="text-slate-500">}</span>
<span class="text-slate-500">}</span></code></pre>
            </div>
        </section>

        <section id="example-3">
            <h2 class="text-2xl font-bold text-white mb-1">Example 3 — TV / Media Portal (Custom CMS)</h2>
            <p class="text-slate-400 mb-5">For media organizations or TV portals with their own proprietary CMS that uses different field names.</p>
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs text-slate-500 ml-2 code-font">custom_api_mapping</span>
                    </div>
                    <button class="copy-btn !static" onclick="copyCode(this)">Copy</button>
                </div>
                <pre class="!border-0 !bg-transparent !p-0 !rounded-none"><code><span class="text-slate-500">{</span>
  <span class="json-key">"title"</span><span class="text-white">:    </span><span class="json-str">"HeadLine"</span><span class="text-white">,</span>             <span class="json-comment">// capital letter field names</span>
  <span class="json-key">"content"</span><span class="text-white">:  </span><span class="json-str">"NewsBody"</span><span class="text-white">,</span>
  <span class="json-key">"image"</span><span class="text-white">:    </span><span class="json-str">"NewsImage"</span><span class="text-white">,</span>
  <span class="json-key">"category"</span><span class="text-white">: </span><span class="json-str">"CategoryID"</span><span class="text-white">,</span>
  <span class="json-key">"tags"</span><span class="text-white">:     </span><span class="json-str">"Keywords"</span><span class="text-white">,</span>
  <span class="json-key">"token"</span><span class="text-white">:    </span><span class="json-str">"APIKey"</span><span class="text-white">,</span>
  <span class="json-key">"extra"</span><span class="text-white">: </span><span class="text-slate-500">{</span>
    <span class="json-key">"NewsType"</span><span class="text-white">:   </span><span class="json-str">"2"</span><span class="text-white">,</span>          <span class="json-comment">// static: always General News type</span>
    <span class="json-key">"IsBreaking"</span><span class="text-white">: </span><span class="json-str">"0"</span><span class="text-white">,</span>
    <span class="json-key">"Language"</span><span class="text-white">:   </span><span class="json-str">"bn"</span>           <span class="json-comment">// always Bengali</span>
  <span class="text-slate-500">}</span><span class="text-white">,</span>
  <span class="json-key">"response_id_key"</span><span class="text-white">: </span><span class="json-str">"NewsID"</span>     <span class="json-comment">// response: { "NewsID": 555 }</span>
<span class="text-slate-500">}</span></code></pre>
            </div>
        </section>

        <section id="example-4">
            <h2 class="text-2xl font-bold text-white mb-1">Example 4 — Minimal Setup</h2>
            <p class="text-slate-400 mb-5">Just starting? Use this bare minimum mapping — title and content are all you absolutely need.</p>
            <div class="glass-card rounded-2xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-red-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-amber-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <span class="text-xs text-slate-500 ml-2 code-font">custom_api_mapping</span>
                    </div>
                    <button class="copy-btn !static" onclick="copyCode(this)">Copy</button>
                </div>
                <pre class="!border-0 !bg-transparent !p-0 !rounded-none"><code><span class="text-slate-500">{</span>
  <span class="json-key">"title"</span><span class="text-white">:   </span><span class="json-str">"title"</span><span class="text-white">,</span>      <span class="json-comment">// same name, just validates it's sent</span>
  <span class="json-key">"content"</span><span class="text-white">: </span><span class="json-str">"content"</span><span class="text-white">,</span>
  <span class="json-key">"token"</span><span class="text-white">:   </span><span class="json-str">"token"</span>
<span class="text-slate-500">}</span></code></pre>
            </div>
        </section>

        <!-- ===== INTERACTIVE JSON BUILDER ===== -->
        <section id="ready-api">
            <h2 class="text-2xl font-bold text-white mb-2">🛠️ Build Your Mapping JSON</h2>
            <p class="text-slate-400 mb-6">Fill in your API's field names below — the JSON will be generated automatically. Then copy and paste it into your Settings.</p>

            <div class="glass-card rounded-2xl p-6 sm:p-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Left: Builder Form -->
                    <div class="space-y-5">
                        <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-4">Field Name Mapping</p>

                        <div class="space-y-3">
                            <!-- Title -->
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-sm font-bold text-sky-400 code-font">"title"</span>
                                    <span class="block text-[10px] text-slate-500">Required</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_title" type="text" placeholder="e.g. news_title" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>

                            <!-- Content -->
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-sm font-bold text-sky-400 code-font">"content"</span>
                                    <span class="block text-[10px] text-slate-500">Required</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_content" type="text" placeholder="e.g. body_text" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>

                            <!-- Image -->
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-sm font-bold text-sky-400 code-font">"image"</span>
                                    <span class="block text-[10px] text-slate-500">Optional</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_image" type="text" placeholder="e.g. featured_image" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>

                            <!-- Category -->
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-sm font-bold text-sky-400 code-font">"category"</span>
                                    <span class="block text-[10px] text-slate-500">Optional</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_category" type="text" placeholder="e.g. cat_ids[]" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>

                            <!-- Tags -->
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-sm font-bold text-sky-400 code-font">"tags"</span>
                                    <span class="block text-[10px] text-slate-500">Optional</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_tags" type="text" placeholder="e.g. post_tags" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>

                            <!-- Date -->
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-sm font-bold text-sky-400 code-font">"date"</span>
                                    <span class="block text-[10px] text-slate-500">Optional</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_date" type="text" placeholder="e.g. publish_date" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>
                        </div>

                        <div class="border-t border-slate-700 pt-5">
                            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Authentication</p>
                            <div class="flex gap-3 mb-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="auth_type" value="body" checked onchange="buildJson()" class="text-indigo-500">
                                    <span class="text-sm text-slate-300">Body Token</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="auth_type" value="bearer" onchange="buildJson()" class="text-indigo-500">
                                    <span class="text-sm text-slate-300">Bearer Header</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="auth_type" value="none" onchange="buildJson()" class="text-indigo-500">
                                    <span class="text-sm text-slate-300">None</span>
                                </label>
                            </div>
                            <div id="token_field_wrap" class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-sm font-bold text-amber-400 code-font">"token"</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_token" type="text" placeholder="e.g. api_key" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>
                        </div>

                        <div class="border-t border-slate-700 pt-5">
                            <p class="text-xs font-black text-slate-500 uppercase tracking-widest mb-3">Response Parsing (Optional)</p>
                            <div class="flex items-center gap-3">
                                <div class="w-28 flex-shrink-0">
                                    <span class="text-xs font-bold text-slate-400 code-font">response_id_key</span>
                                </div>
                                <span class="text-slate-600">→</span>
                                <input id="b_resp_key" type="text" placeholder="e.g. news_id (default: post_id)" oninput="buildJson()" class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition code-font">
                            </div>
                        </div>
                    </div>

                    <!-- Right: Live Preview -->
                    <div>
                        <div class="flex justify-between items-center mb-3">
                            <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Live JSON Preview</p>
                            <button id="copyBuiltJson" onclick="copyBuiltJson()" class="text-xs bg-slate-700 hover:bg-indigo-600 border border-slate-600 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg font-bold transition-all">
                                <i class="fas fa-copy mr-1"></i> Copy JSON
                            </button>
                        </div>
                        <pre id="json_preview" class="min-h-[300px] text-sm"><code id="json_preview_code" class="code-font text-slate-300">// Fill in fields on the left →</code></pre>

                        @auth
                        <button onclick="applyToSettings()" class="mt-4 w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-bold py-3 rounded-xl transition-all shadow-lg hover:shadow-indigo-500/20 text-sm">
                            ✅ Apply to My Settings
                        </button>
                        @else
                        <a href="{{ route('login') }}" class="mt-4 flex items-center justify-center gap-2 w-full bg-slate-700 hover:bg-slate-600 text-slate-300 font-bold py-3 rounded-xl transition-all text-sm">
                            <i class="fas fa-lock text-xs"></i> Login to Apply to Settings
                        </a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== FAQ ===== -->
        <section id="faq">
            <h2 class="text-2xl font-bold text-white mb-6">Frequently Asked Questions</h2>
            <div class="space-y-3" id="faq-list">

                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4">What happens if my API is slow or times out?</p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5">
                        <p class="text-sm text-slate-400">Our system gives your API up to <strong class="text-white">120 seconds</strong> to respond (with a 30-second connection timeout). If it still doesn't respond, the post is marked as <strong class="text-red-400">failed</strong>. You can retry from the news list. Make sure your API doesn't do heavy processing synchronously — return a quick 200 and process in background.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4">Can I use both Custom API AND WordPress at the same time?</p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5">
                        <p class="text-sm text-slate-400">Yes! If both WordPress credentials and Custom API are configured, the system will post to <strong class="text-white">WordPress first</strong>, then Custom API second. Both can run in the same job.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4">My API uses a different image field name. What should I do?</p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5">
                        <p class="text-sm text-slate-400">Easy! In your mapping, set the value of <code class="code-font text-cyan-400">"image"</code> to whatever field name your API expects. For example, if your API expects <code class="code-font text-amber-400">news_photo</code>, write: <code class="code-font text-white">"image": "news_photo"</code>. We'll upload the file under that name.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4">Where do I get the Category IDs to put in the mapping table?</p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5">
                        <p class="text-sm text-slate-400 mb-2">In Settings, there's a <strong class="text-white">Category Mapping</strong> table and a <strong class="text-white">Refresh Categories</strong> button. Click it — if you've set the <code class="code-font text-cyan-400">custom_category_url</code>, it fetches from your API. Otherwise, it tries your WordPress. The IDs shown in the dropdowns are the real IDs from your target site.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4">SSL verify is failing for my local/staging API. What can I do?</p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5">
                        <p class="text-sm text-slate-400">Custom API mode uses Guzzle with <code class="code-font text-amber-400">'verify' => false</code> by default, so self-signed SSL certificates are automatically accepted. No extra configuration needed for staging/dev servers.</p>
                    </div>
                </div>

                <div class="faq-item glass-card rounded-2xl overflow-hidden">
                    <button onclick="toggleFaq(this)" class="w-full flex justify-between items-center p-5 text-left hover:bg-slate-800/50 transition-colors">
                        <p class="font-bold text-white text-sm pr-4">What if I don't include the "image" key in my mapping?</p>
                        <i class="fas fa-chevron-down text-slate-500 transition-transform"></i>
                    </button>
                    <div class="faq-body hidden px-5 pb-5">
                        <p class="text-sm text-slate-400">If <code class="code-font text-cyan-400">"image"</code> is not in your mapping, no image will be sent to your API. This is fine if your API doesn't need images, or if you handle image association separately on your end.</p>
                    </div>
                </div>

            </div>
        </section>

        <!-- ===== FOOTER ===== -->
        <div class="border-t border-slate-800 pt-8 text-center">
            <p class="text-slate-600 text-sm">Newsmanage24 API Integration Guide · Built for client developers</p>
            <p class="text-slate-700 text-xs mt-1">© {{ date('Y') }} Newsmanage24. Questions? Contact your admin.</p>
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
    const date    = document.getElementById('b_date').value.trim();
    const token   = document.getElementById('b_token').value.trim();
    const respKey = document.getElementById('b_resp_key').value.trim();
    const authType = document.querySelector('input[name="auth_type"]:checked').value;

    // Show/hide token field
    document.getElementById('token_field_wrap').style.display = (authType === 'body') ? 'flex' : 'none';

    builtJson = {};
    if (title)   builtJson['title']   = title;
    if (content) builtJson['content'] = content;
    if (image)   builtJson['image']   = image;
    if (cat)     builtJson['category']= cat;
    if (tags)    builtJson['tags']    = tags;
    if (date)    builtJson['date']    = date;

    if (authType === 'body'   && token) builtJson['token']       = token;
    if (authType === 'bearer')          builtJson['header_auth'] = 'Bearer';

    if (respKey) builtJson['response_id_key'] = respKey;

    const json = JSON.stringify(builtJson, null, 2);
    document.getElementById('json_preview_code').textContent = Object.keys(builtJson).length === 0
        ? '// Fill in fields on the left →'
        : json;
}

// ===== COPY BUILT JSON =====
function copyBuiltJson() {
    const json = JSON.stringify(builtJson, null, 2);
    if (!json || Object.keys(builtJson).length === 0) {
        alert('Please fill in at least one field first.');
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

// ===== APPLY TO SETTINGS (redirect) =====
function applyToSettings() {
    const json = JSON.stringify(builtJson, null, 2);
    if (!json || Object.keys(builtJson).length === 0) {
        alert('Please fill in at least one field first.');
        return;
    }
    // Store in sessionStorage, then redirect to settings page
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
    const icon = btn.querySelector('i');
    body.classList.toggle('hidden');
    icon.classList.toggle('rotate-180');
}

// ===== SCROLL TO SECTION =====
function scrollToSection(id) {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
    // Update active sidebar links
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

// ===== MOBILE SIDEBAR =====
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('open');
});

// ===== APPLY PENDING MAPPING from sessionStorage =====
document.addEventListener('DOMContentLoaded', () => {
    const pending = sessionStorage.getItem('pendingApiMapping');
    if (pending) {
        const textarea = document.querySelector('textarea[name="custom_api_mapping"]');
        if (textarea) {
            textarea.value = pending;
            sessionStorage.removeItem('pendingApiMapping');
        }
    }

    // Active sidebar on scroll
    const sections = document.querySelectorAll('section[id]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
                const active = document.querySelector(`.sidebar-link[onclick*="${entry.target.id}"]`);
                if (active) active.classList.add('active');
            }
        });
    }, { threshold: 0.3 });
    sections.forEach(s => observer.observe(s));
});
</script>
</body>
</html>
