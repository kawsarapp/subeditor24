@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    <!-- Top Header -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2.5">
                <i class="fas fa-sliders text-indigo-600"></i> Settings & Integrations
            </h1>
            <p class="text-gray-500 mt-1 text-sm">Configure user profile, AI models, website webhooks, scrapers, and publishing preferences.</p>
        </div>
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl shadow-lg text-center">
            <p class="text-xs opacity-80 uppercase tracking-wider">Available Balance</p>
            <p class="text-2xl font-bold">{{ auth()->user()->credits }} <span class="text-sm font-normal">Credits</span></p>
        </div>
    </div>

    <!-- Global Accordion Expand/Collapse Controls & Diagnostics -->
    <div class="flex flex-wrap justify-between items-center bg-white dark:bg-slate-900 p-4 rounded-xl border border-gray-200 dark:border-slate-800 shadow-sm mb-6 gap-3">
        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-slate-400 font-semibold">
            <i class="fas fa-layer-group text-indigo-600 text-sm"></i>
            <span>Settings sections are grouped by category.</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" onclick="runDiagnosticsModal()" class="px-3.5 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold rounded-lg shadow-sm flex items-center gap-1.5 transition cursor-pointer">
                <i class="fa-solid fa-heart-pulse"></i> System Diagnostics
            </button>
            <button type="button" onclick="expandAllSettings()" class="px-3.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold rounded-lg border border-indigo-200 flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                <i class="fas fa-expand-alt"></i> Expand All
            </button>
            <button type="button" onclick="collapseAllSettings()" class="px-3.5 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg border border-gray-300 flex items-center gap-1.5 transition cursor-pointer shadow-sm">
                <i class="fas fa-compress-alt"></i> Collapse All
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm flex items-center gap-2" role="alert">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            <ul class="list-disc pl-5 font-medium text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    {{-- 1. Profile Update Section (Collapsible) --}}
    <form action="{{ route('settings.update-profile') }}" method="POST" class="mb-6" novalidate>
        @csrf
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center text-base">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            User Profile & Organization Information
                        </h2>
                        <p class="text-xs text-gray-500">আপনার নাম, পোর্টাল নাম, ওয়েবসাইট লিঙ্ক, মোবাইল নম্বর ও লগইন ক্রেডেনশিয়াল</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-indigo-600 font-semibold hidden sm:inline">Click to expand</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">আপনার নাম (Full Name)</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" 
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">ইমেইল এড্রেস (Email / Login ID)</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" 
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">মোবাইল নম্বর (Phone Number)</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="01XXXXXXXXX"
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">নিউজ পোর্টালের নাম (Brand Name)</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name ?? '') }}" placeholder="Ex: ঢাকা পোস্ট ২৪"
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">নিউজ ওয়েবসাইট URL (Website Link)</label>
                        <input type="url" name="website_url" value="{{ old('website_url', $settings->wp_url ?? '') }}" placeholder="https://yourportal.com"
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">জেলা / লোকেশন (District)</label>
                        <input type="text" name="district" value="{{ old('district', auth()->user()->district) }}" placeholder="Ex: ঢাকা / চট্টগ্রাম"
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">নতুন পাসওয়ার্ড (Optional)</label>
                        <input type="password" name="password" placeholder="পরিবর্তন না করতে চাইলে ফাঁকা রাখুন..." 
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">পাসওয়ার্ড নিশ্চিত করুন</label>
                        <input type="password" name="password_confirmation" placeholder="পুনরায় নতুন পাসওয়ার্ড লিখুন" 
                               class="w-full bg-white border border-gray-300 text-gray-900 px-3.5 py-2.5 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                </div>
                <div class="mt-5 text-right">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold transition shadow-md cursor-pointer text-sm">
                        💾 Update Profile & Info
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- ⚡ DYNAMIC CRON & AUTOMATION SCHEDULER (Collapsible) --}}
    @if(auth()->user()->role === 'super_admin')
    <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-emerald-200 overflow-hidden transition-all duration-200 mb-6">
        <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-emerald-50/50 hover:bg-emerald-50/80 transition" onclick="toggleSettingsAccordion(this)">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-base font-black">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                        Dynamic Cron & Automation Scheduler
                        <span id="cronHealthPill" class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full {{ ($cronHealth['healthy'] ?? false) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                            {{ $cronHealth['status_label'] ?? '🟢 Active' }}
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500">Self-healing web-cron, 1-click Linux server installer, and real-time scheduler health</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-emerald-700 font-semibold hidden sm:inline">Scheduler Monitor</span>
                <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
            </div>
        </div>

        <div class="settings-accordion-body hidden p-6 border-t border-emerald-100 bg-emerald-50/20 text-sm space-y-4">
            
            {{-- Metrics Grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-xs">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Health Status</span>
                    <span id="cronMetricStatus" class="font-extrabold text-xs text-emerald-700 block mt-0.5">{{ $cronHealth['status_label'] ?? 'Active' }}</span>
                </div>
                <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-xs">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Last Executed</span>
                    <span id="cronMetricLastRun" class="font-bold text-xs text-gray-800 block mt-0.5">{{ $cronHealth['last_run_at'] ?? 'Never' }}</span>
                </div>
                <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-xs">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Last Source</span>
                    <span id="cronMetricSource" class="font-bold text-xs text-indigo-700 block mt-0.5 uppercase">{{ $cronHealth['last_source'] ?? 'CLI / Web' }}</span>
                </div>
                <div class="bg-white p-3 rounded-xl border border-gray-200 shadow-xs">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Recorded Runs</span>
                    <span id="cronMetricRuns" class="font-extrabold text-xs text-gray-900 block mt-0.5">{{ $cronHealth['total_runs'] ?? 0 }} times</span>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <button type="button" id="btnTestRunCron" onclick="triggerCronAdminAction('test_run')" class="px-4 py-2 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-bolt"></i>
                    <span>Run Scheduler Now (Test Run)</span>
                </button>
                <button type="button" id="btnInstallCron" onclick="triggerCronAdminAction('install')" class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <i class="fa-solid fa-server"></i>
                    <span>1-Click Auto Install Server Crontab</span>
                </button>
                <button type="button" id="btnRefreshCron" onclick="triggerCronAdminAction('status')" class="px-3.5 py-2 bg-white hover:bg-gray-100 text-gray-700 border border-gray-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Refresh Health</span>
                </button>
            </div>

            {{-- Live Status Alert Box --}}
            <div id="cronActionAlertBox" class="hidden p-3 rounded-xl text-xs font-semibold"></div>

            {{-- Technical Details Box --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                {{-- Web-Cron URL --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        <i class="fa-solid fa-link text-indigo-500"></i> External Web-Cron URL (UptimeRobot / Cron-Job.org):
                    </label>
                    <div class="flex gap-1.5">
                        <input type="text" readonly value="{{ route('cron.web-run') }}" id="webCronUrlInput" class="w-full bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono px-2.5 py-1.5 text-gray-700">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('webCronUrlInput').value);alert('Web-Cron URL Copied!');" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs shrink-0 cursor-pointer">
                            Copy
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Free ping service দিয়ে প্রতি ১ বা ৫ মিনিট পর পর এই লিঙ্কে GET রিকোয়েস্ট পাঠাতে পারেন।</p>
                </div>

                {{-- Server Crontab Command --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        <i class="fa-solid fa-terminal text-emerald-600"></i> Linux Server Crontab Command:
                    </label>
                    <div class="flex gap-1.5">
                        <input type="text" readonly value="* * * * * cd {{ base_path() }} && php artisan schedule:run >> /dev/null 2>&1" id="serverCrontabCommandInput" class="w-full bg-gray-50 border border-gray-200 rounded-lg text-xs font-mono px-2.5 py-1.5 text-gray-700">
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('serverCrontabCommandInput').value);alert('Crontab Command Copied!');" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg text-xs shrink-0 cursor-pointer">
                            Copy
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">টার্মিনালে <code>crontab -e</code> দিয়ে পেস্ট করতে পারেন অথবা উপরের <strong>1-Click Install</strong> চাপুন।</p>
                </div>
            </div>

        </div>
    </div>

    {{-- 🗄️ FULL DATABASE BACKUP & DISASTER RECOVERY (Super Admin Only) --}}
    <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-cyan-200 overflow-hidden transition-all duration-200 mb-6" id="dbBackupAccordionCard">
        <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-cyan-50/50 hover:bg-cyan-50/80 transition" onclick="toggleSettingsAccordion(this)">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center text-base font-black">
                    <i class="fa-solid fa-database"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                        Full Database Backup & Disaster Recovery
                        <span id="backupCountBadge" class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full bg-cyan-100 text-cyan-800 border border-cyan-300">
                            {{ count($dbBackups ?? []) }} Backups Available
                        </span>
                    </h2>
                    <p class="text-xs text-gray-500">1-Click Full DB Export, GZIP Compression, Upload & Restore, and Auto-Safety snapshots</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-xs text-cyan-700 font-semibold hidden sm:inline">Backup Manager</span>
                <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
            </div>
        </div>

        <div class="settings-accordion-body hidden p-6 border-t border-cyan-100 bg-cyan-50/15 text-sm space-y-5">
            
            {{-- Action Toolbar --}}
            <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="flex flex-wrap items-center gap-2.5">
                    <button type="button" onclick="createDatabaseBackup(true)" id="btnBackupGzip" class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs cursor-pointer">
                        <i class="fa-solid fa-file-zipper"></i>
                        <span>Create Compressed Backup (.sql.gz)</span>
                    </button>
                    <button type="button" onclick="createDatabaseBackup(false)" id="btnBackupSql" class="px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-file-code text-indigo-500"></i>
                        <span>Plain SQL (.sql)</span>
                    </button>
                    <button type="button" onclick="openRestoreUploadModal()" class="px-3.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-upload"></i>
                        <span>Upload & Restore (.sql / .sql.gz)</span>
                    </button>
                </div>
                <div>
                    <button type="button" onclick="refreshDatabaseBackupsList()" id="btnRefreshBackups" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-semibold transition flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-arrows-rotate" id="refreshBackupIcon"></i>
                        <span>Refresh List</span>
                    </button>
                </div>
            </div>

            {{-- Status/Alert Box --}}
            <div id="dbBackupAlertBox" class="hidden p-3.5 rounded-xl text-xs font-semibold transition-all"></div>

            {{-- Saved Backups Table --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                        <i class="fa-solid fa-server text-cyan-600"></i> Server Saved Backups
                    </h3>
                    <span class="text-[11px] text-gray-400 font-semibold">Location: <code>storage/app/backups/</code></span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse" id="backupsTable">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50/50 text-[11px] font-extrabold text-gray-500 uppercase tracking-wider">
                                <th class="py-2.5 px-4">Backup File</th>
                                <th class="py-2.5 px-4">Format</th>
                                <th class="py-2.5 px-4">Size</th>
                                <th class="py-2.5 px-4">Created Date</th>
                                <th class="py-2.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="backupsTableBody" class="divide-y divide-gray-100 text-xs">
                            @forelse($dbBackups ?? [] as $backup)
                            <tr class="hover:bg-slate-50/80 transition" id="row-{{ md5($backup['filename']) }}">
                                <td class="py-3 px-4 font-mono font-medium text-slate-800 flex items-center gap-2">
                                    @if($backup['is_safety'])
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                            🛡️ Auto-Safety
                                        </span>
                                    @else
                                        <i class="fa-solid fa-database text-cyan-600"></i>
                                    @endif
                                    <span>{{ $backup['filename'] }}</span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $backup['is_compressed'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $backup['is_compressed'] ? 'GZIP (.sql.gz)' : 'SQL (.sql)' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-slate-600">
                                    {{ $backup['formatted_size'] }}
                                </td>
                                <td class="py-3 px-4 text-slate-500">
                                    <div>{{ $backup['created_at'] }}</div>
                                    <div class="text-[10px] text-slate-400 font-semibold">{{ $backup['relative_time'] }}</div>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('database.backups.download', $backup['filename']) }}" class="px-2.5 py-1 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-download"></i> Download
                                        </a>
                                        <button type="button" onclick="confirmRestoreBackup('{{ $backup['filename'] }}')" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-rotate-left"></i> Restore
                                        </button>
                                        <button type="button" onclick="deleteBackupFile('{{ $backup['filename'] }}')" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr id="noBackupsRow">
                                <td colspan="5" class="py-8 text-center text-gray-400 text-xs font-medium">
                                    <i class="fa-solid fa-box-open text-2xl mb-1 block text-gray-300"></i>
                                    কোনো ব্যাকআপ ফাইল এখনো তৈরি করা হয়নি। উপরের বাটনগুলো ব্যবহার করে ব্যাকআপ তৈরি করুন।
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Help & Security Information Note --}}
            <div class="bg-amber-50/60 border border-amber-200 p-4 rounded-xl flex items-start gap-3 text-xs text-amber-900 leading-relaxed">
                <i class="fa-solid fa-shield-halved text-amber-600 text-base shrink-0 mt-0.5"></i>
                <div>
                    <strong class="font-bold">গুরুত্বপূর্ণ নিরাপত্তা নির্দেশিকা:</strong>
                    <ul class="list-disc pl-4 mt-1 space-y-0.5 text-[11.5px] text-amber-800">
                        <li>রিস্টোর (Restore) বাটনে চাপ দিলে বর্তমান ডেটাবেজের সমস্ত টেবিল ব্যাকআপ ফাইলের ডেটা দিয়ে প্রতিস্থাপিত হবে।</li>
                        <li>যেকোনো রিস্টোর শুরু করার আগে সিস্টেম স্বয়ংক্রিয়ভাবে বর্তমান অবস্থার একটি <strong>Auto-Safety Snapshot</strong> ব্যাকআপ নিয়ে রাখে।</li>
                        <li>ব্যাকআপ ফাইলগুলো স্ট্যান্ডার্ড SQL ফরম্যাটে তৈরি, যা phpMyAdmin, cPanel বা যেকোনো লিনাক্স টার্মিনালেও সরাসরি ইমপোর্ট করা যাবে।</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
    @endif

    {{-- 2. Main Settings Form Start --}}
    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6" novalidate id="mainSettingsForm">
        @csrf

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_proxy'))
        {{-- SCRAPER PROXY & DECODO SETTINGS (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-blue-50/40 hover:bg-blue-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-base">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Scraper & Proxy Configuration
                        </h2>
                        <p class="text-xs text-gray-500">Configure proxies, scraping API tokens, and automatic cleanup schedules</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Scraper / Proxy</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-3 gap-2">
                    <p class="text-xs text-gray-600 font-medium">Configure custom proxies and Web Scraping APIs (Scrape.do / Decodo) for anti-bot bypass. Leave empty to use system defaults.</p>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="testDecodoProxy()" class="text-xs bg-gray-700 hover:bg-gray-800 text-white px-3 py-1.5 rounded-lg transition font-bold shadow-sm flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                            <i class="fas fa-network-wired"></i> <span>Test Standard Proxy</span>
                        </button>
                        <button type="button" onclick="testActiveScrapingApi()" class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg transition font-bold shadow-sm flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                            <i class="fas fa-bolt"></i> <span>Test Scraping API</span>
                        </button>
                    </div>
                </div>
                <div id="decodo_proxy_status_msg" class="text-xs font-bold mb-4 whitespace-pre-line"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Standard Proxy -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-gray-700 mb-3 border-b pb-1 flex items-center gap-2">
                                <i class="fas fa-network-wired text-gray-500"></i> Standard Proxy (Puppeteer & Python)
                            </h3>
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Proxy Host</label>
                                    <input type="text" id="proxy_host" name="proxy_host" value="{{ old('proxy_host', $settings->proxy_host ?? '') }}" placeholder="proxy.example.com" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Proxy Port</label>
                                    <input type="number" id="proxy_port" name="proxy_port" value="{{ old('proxy_port', $settings->proxy_port ?? '') }}" placeholder="10000" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Username (Optional)</label>
                                    <input type="text" id="proxy_username" name="proxy_username" value="{{ old('proxy_username', $settings->proxy_username ?? '') }}" placeholder="username" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 mb-1">Password (Optional)</label>
                                    <input type="password" id="proxy_password" name="proxy_password" value="{{ old('proxy_password', $settings->proxy_password ?? '') }}" placeholder="••••••••" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Web Scraping API (Anti-Bot & Cloudflare Bypass) -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="border-b pb-2 mb-3">
                                <h3 class="font-bold text-indigo-700 flex items-center gap-1.5">
                                    <i class="fas fa-shield-virus"></i> Web Scraping API (Anti-Bot / Cloudflare Bypass)
                                </h3>
                                <p class="text-[11px] text-gray-500 mt-0.5">Bypasses Cloudflare Turnstile, DataDome, JS-rendering & protects IP.</p>
                            </div>

                            <!-- Provider Selection -->
                            <div class="mb-3">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Active Scraping API Provider</label>
                                <select name="scraping_api_provider" id="scraping_api_provider" onchange="toggleScrapingApiFields()" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-semibold">
                                    <option value="scrape_do" {{ old('scraping_api_provider', $settings->scraping_api_provider ?? 'scrape_do') === 'scrape_do' ? 'selected' : '' }}>
                                        🚀 Scrape.do (Recommended - Cloudflare, Turnstile, DataDome bypass)
                                    </option>
                                    <option value="decodo" {{ old('scraping_api_provider', $settings->scraping_api_provider ?? '') === 'decodo' ? 'selected' : '' }}>
                                        🌐 Decodo / SmartProxy Universal API
                                    </option>
                                </select>
                            </div>

                            <!-- Scrape.do Token -->
                            <div id="scrape_do_field" class="mb-3">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold text-gray-600">Scrape.do API Token</label>
                                    <a href="https://scrape.do" target="_blank" class="text-[11px] text-indigo-600 hover:underline flex items-center gap-1"><i class="fas fa-external-link-alt text-[10px]"></i> Get Token</a>
                                </div>
                                <input type="password" id="scrape_do_token" name="scrape_do_token" value="{{ old('scrape_do_token', $settings->scrape_do_token ?? '') }}" placeholder="Enter your Scrape.do API Token" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-mono text-xs">
                                <p class="text-[10px] text-gray-400 mt-1">High-speed residential proxies with JS render & BD geo-targeting.</p>
                            </div>

                            <!-- Decodo Token -->
                            <div id="decodo_field" class="mb-3">
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-xs font-bold text-gray-600">Decodo API Token (Basic Auth Token)</label>
                                    <a href="https://decodo.com" target="_blank" class="text-[11px] text-blue-600 hover:underline flex items-center gap-1"><i class="fas fa-external-link-alt text-[10px]"></i> Get Token</a>
                                </div>
                                <input type="password" id="smartproxy_api_token" name="smartproxy_api_token" value="{{ old('smartproxy_api_token', $settings->smartproxy_api_token ?? '') }}" placeholder="Basic VTAwM..." class="w-full border-gray-300 rounded shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-xs">
                                <p class="text-[10px] text-gray-400 mt-1">Enter <code>Basic Auth Token</code> obtained from your Decodo / SmartProxy dashboard.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Auto Clean Section --}}
                <div class="mt-5 pt-4 border-t border-gray-200">
                    <label class="block text-xs font-bold text-gray-700 mb-1">
                        Auto-Cleanup Unprocessed Articles (Days)
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="auto_clean_days"
                               min="1" max="90"
                               value="{{ $settings->auto_clean_days ?? 7 }}"
                               class="w-28 border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-center font-bold text-base">
                        <p class="text-xs text-gray-500">Unpublished articles older than this threshold will be automatically pruned (Default: 7 days).</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin')
        {{-- ROI Config Section (Collapsible) --}}
        @php
            $roiConfig = isset($settings->roi_config) ? (is_string($settings->roi_config) ? json_decode($settings->roi_config, true) : $settings->roi_config) : [];
        @endphp
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-green-50/40 hover:bg-green-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-700 flex items-center justify-center text-base">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Editorial Metrics & Cost Calculation (Super Admin)
                        </h2>
                        <p class="text-xs text-gray-500">Configure estimated editorial workload and cost metrics</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-green-100 text-green-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Editorial Metrics</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Staff Hourly Rate (BDT/Hour)</label>
                        <input type="number" name="roi_hourly_rate" value="{{ $roiConfig['hourly_rate'] ?? 100 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Time per News (Minutes)</label>
                        <input type="number" name="roi_news_minutes" value="{{ $roiConfig['news_minutes'] ?? 20 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Time per Card (Minutes)</label>
                        <input type="number" name="roi_card_minutes" value="{{ $roiConfig['card_minutes'] ?? 15 }}" class="w-full border-gray-300 rounded shadow-sm focus:border-green-500 focus:ring-green-500 text-xs">
                    </div>
                </div>
            </div>
        </div>

        {{-- Studio Template & Media Manager Links (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-slate-50 hover:bg-slate-100 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-base">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Template Studio & Asset Manager
                        </h2>
                        <p class="text-xs text-gray-500">Manage news card templates, frames, and font assets</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-indigo-100 text-indigo-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Templates & Assets</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">News Card Templates</h3>
                            <p class="text-xs text-gray-500 mt-1 mb-4">Manage layout templates, frame overlays, and typography positions.</p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.templates.index') }}" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-2 rounded-lg transition shadow-sm text-center">Templates</a>
                            <a href="{{ route('admin.templates.create') }}" class="flex-1 bg-slate-600 hover:bg-slate-700 text-white font-bold text-xs py-2 rounded-lg transition text-center">+ New Template</a>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between">
                        <div>
                            <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">Media & Asset Storage</h3>
                            <p class="text-xs text-gray-500 mt-1 mb-4">Upload and manage frame overlays (PNG) and custom typography fonts (.ttf, .woff).</p>
                        </div>
                        <div>
                            <a href="{{ route('admin.media.index') }}" class="block w-full bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs py-2 rounded-lg transition shadow-sm text-center">Open Asset Manager</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_branding'))
        {{-- 3. Branding Section (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-pink-100 text-pink-600 flex items-center justify-center text-base">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Newsroom Branding & Identity
                        </h2>
                        <p class="text-xs text-gray-500">Publication name, primary visual palette, and masthead logo</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-pink-100 text-pink-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Branding</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Publication Name (e.g. Dhaka Post)</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $settings->brand_name ?? 'My News') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Default Visual Theme</label>
                        <select name="default_theme_color" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs font-semibold">
                            <option value="red" {{ ($settings->default_theme_color ?? '') == 'red' ? 'selected' : '' }}>Red (Breaking)</option>
                            <option value="blue" {{ ($settings->default_theme_color ?? '') == 'blue' ? 'selected' : '' }}>Blue (Standard)</option>
                            <option value="green" {{ ($settings->default_theme_color ?? '') == 'green' ? 'selected' : '' }}>Green (Sports/Islamic)</option>
                            <option value="purple" {{ ($settings->default_theme_color ?? '') == 'purple' ? 'selected' : '' }}>Purple (Lifestyle)</option>
                            <option value="black" {{ ($settings->default_theme_color ?? '') == 'black' ? 'selected' : '' }}>Black (Dark)</option>
                        </select>
                    </div>
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Publication Logo URL (Optional)</label>
                        <input type="text" name="logo_url" value="{{ old('logo_url', $settings->logo_url ?? '') }}" placeholder="https://example.com/logo.png" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                        <p class="text-[11px] text-gray-500 mt-1">Enter public image URL or upload directly inside Template Studio.</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_target_language'))
        {{-- TARGET LANGUAGE SETTINGS (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-teal-50/40 hover:bg-teal-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-base">
                        <i class="fas fa-language"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Default News Language
                        </h2>
                        <p class="text-xs text-gray-500">Default language for article processing and aggregation</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-teal-100 text-teal-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Language</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="bg-white p-4 rounded-lg border border-teal-200 shadow-sm">
                    <select name="target_language" class="w-full border-gray-300 rounded shadow-sm focus:border-teal-500 focus:ring-teal-500 font-semibold text-xs">
                        <option value="" {{ empty($settings->target_language) ? 'selected' : '' }}>Inherit Website Default</option>
                        <option value="bn" {{ ($settings->target_language ?? '') == 'bn' ? 'selected' : '' }}>Bengali (বাংলা)</option>
                        <option value="en" {{ ($settings->target_language ?? '') == 'en' ? 'selected' : '' }}>English</option>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-2">Standard incoming articles will be drafted in this language unless overridden by a website-specific profile.</p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai') || auth()->user()->hasPermission('can_settings_ai_prompt'))
        {{-- EDITORIAL REWRITE PROMPT (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-teal-50/40 hover:bg-teal-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-teal-100 text-teal-700 flex items-center justify-center text-base">
                        <i class="fas fa-feather-alt"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Editorial Writing Rules & AI Prompt
                        </h2>
                        <p class="text-xs text-gray-500">Configure sub-editor rewrite guidelines, tone, and formatting rules</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-teal-100 text-teal-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Editorial Prompt</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-4 pb-3 border-b border-gray-200">
                    <div>
                        <p class="text-xs font-semibold text-gray-700">Define system-level editorial guidelines used when converting source reports into finished articles.</p>
                        <p class="text-[11px] text-gray-500 mt-0.5">Leave empty to use standard professional newsroom editorial rules.</p>
                    </div>
                    <div class="flex items-center gap-2 self-start sm:self-auto flex-wrap">
                        <button type="button" onclick="insertDefaultPrompt('bn')" class="text-[11px] bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold px-3 py-1.5 rounded-lg border border-teal-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-file-alt"></i> <span>Insert Default (BN)</span>
                        </button>
                        <button type="button" onclick="insertDefaultPrompt('en')" class="text-[11px] bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold px-3 py-1.5 rounded-lg border border-teal-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-file-alt"></i> <span>Insert Default (EN)</span>
                        </button>
                        <button type="button" onclick="resetDefaultPrompt()" class="text-[11px] bg-red-50 hover:bg-red-100 text-red-600 font-bold px-3 py-1.5 rounded-lg border border-red-200 transition cursor-pointer flex items-center gap-1.5 shadow-sm" title="Clear to use system default">
                            <i class="fas fa-undo"></i> <span>Reset to Default</span>
                        </button>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-700 mb-2">Custom Editorial System Prompt</label>
                    <textarea id="custom_rewrite_prompt" name="custom_rewrite_prompt" rows="12" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-teal-500 focus:ring-teal-500 text-xs font-mono bg-white p-3.5 leading-relaxed placeholder-gray-400" placeholder="Leave blank to use built-in newsroom sub-editor guidelines...">{{ old('custom_rewrite_prompt', $settings->custom_rewrite_prompt ?? '') }}</textarea>
                </div>

                <div class="text-[11px] text-teal-900 flex items-start gap-2 bg-teal-50/70 p-3 rounded-lg border border-teal-200">
                    <i class="fas fa-info-circle text-teal-600 mt-0.5 text-sm"></i>
                    <div>
                        <strong>Structured Output Enforcement:</strong> Standard article structure fields (<code>title</code>, <code>content</code>, <code>meta_description</code>, <code>focus_keyword</code>, <code>tags</code>) are enforced by the engine across all models.
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai_prompt'))
        {{-- 🧠 AI COPILOT TRAINING & SYSTEM KNOWLEDGE BASE (Super Admin) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-emerald-50/50 hover:bg-emerald-50/90 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-base">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            AI Copilot Training & Knowledge Base
                            <span class="text-[10px] bg-emerald-600 text-white font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">Super Admin</span>
                        </h2>
                        <p class="text-xs text-gray-500">Train your live on-site AI Assistant with custom guidelines, editorial rules, and zero-hallucination examples</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Live AI Training</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm space-y-6">
                {{-- Knowledge Base Card --}}
                <div>
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                <i class="fas fa-book-reader text-emerald-600"></i>
                                Custom Knowledge Base, Persona & Brand Rules (কাস্টম নলেজ ও নিয়মাবলী)
                            </label>
                            <p class="text-[11px] text-gray-500">এই বক্সে আপনার ওয়েবসাইট, ক্লায়েন্ট সাপোর্ট পলিসি, নিষিদ্ধ শব্দ এবং এআই-এর আচরণবিধি লিখে রাখুন।</p>
                        </div>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="insertTrainingTemplate('newsroom')" class="text-[10px] bg-white hover:bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded border border-emerald-200 transition cursor-pointer shadow-2xs flex items-center gap-1">
                                <i class="fas fa-newspaper"></i> <span>Newsroom Rules</span>
                            </button>
                            <button type="button" onclick="insertTrainingTemplate('youtube')" class="text-[10px] bg-white hover:bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded border border-emerald-200 transition cursor-pointer shadow-2xs flex items-center gap-1">
                                <i class="fab fa-youtube text-red-500"></i> <span>YouTube SEO Rules</span>
                            </button>
                            <button type="button" onclick="insertTrainingTemplate('troubleshooting')" class="text-[10px] bg-white hover:bg-emerald-50 text-emerald-700 font-bold px-2.5 py-1 rounded border border-emerald-200 transition cursor-pointer shadow-2xs flex items-center gap-1">
                                <i class="fas fa-tools text-amber-500"></i> <span>Support & Fixes</span>
                            </button>
                        </div>
                    </div>
                    <textarea id="ai_copilot_custom_knowledge" name="ai_copilot_custom_knowledge" rows="8" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-xs font-mono bg-white p-3.5 leading-relaxed placeholder-gray-400" placeholder="উদাহরণ:&#10;1. সবসময় শ্রদ্ধাশীল সাংবাদিকের ভাষায় উত্তর দেবে।&#10;2. অপ্রয়োজনীয় বা বিভ্রান্তিকর তথ্য দেবে না।&#10;3. কোনো জটিল টেকনিক্যাল এরর আসলে Settings পেজে গিয়ে টেস্ট কানেকশন করার পরামর্শ দেবে।">{{ old('ai_copilot_custom_knowledge', $settings->ai_copilot_custom_knowledge ?? '') }}</textarea>
                </div>

                {{-- Few Shot Examples Card --}}
                <div>
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-2 mb-2">
                        <div>
                            <label class="block text-xs font-bold text-gray-800 flex items-center gap-1.5">
                                <i class="fas fa-lightbulb text-amber-500"></i>
                                Few-Shot Examples (আদর্শ প্রশ্নোত্তর ও অ্যাকশন উদাহরণ)
                            </label>
                            <p class="text-[11px] text-gray-500">AI-কে বাস্তব উদাহরণ দিলে নির্ভুলতা বহুগুণ বাড়ে এবং ভুলভাল উত্তর দেওয়া সম্পূর্ণ বন্ধ হয়ে যায়।</p>
                        </div>
                        <button type="button" onclick="insertTrainingTemplate('few_shot')" class="text-[10px] bg-white hover:bg-amber-50 text-amber-700 font-bold px-2.5 py-1 rounded border border-amber-200 transition cursor-pointer shadow-2xs flex items-center gap-1 self-start sm:self-auto">
                            <i class="fas fa-magic"></i> <span>Insert Example Template</span>
                        </button>
                    </div>
                    <textarea id="ai_copilot_few_shot_examples" name="ai_copilot_few_shot_examples" rows="7" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-xs font-mono bg-white p-3.5 leading-relaxed placeholder-gray-400" placeholder="User: WordPress কানেকশন এরর দেখাচ্ছে, কী করব?&#10;Assistant: অনুগ্রহ করে Settings পেজে গিয়ে Application Password রি-জেনারেট করুন এবং সাইট URL-এর শেষে স্ল্যাশ (/) ছাড়া দিন।">{{ old('ai_copilot_few_shot_examples', $settings->ai_copilot_few_shot_examples ?? '') }}</textarea>
                </div>

                {{-- Engine & Strictness Control --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                            <i class="fas fa-microchip text-emerald-600"></i>
                            Copilot AI Engine (অন-সাইট এআই মডেল নির্বাচন)
                        </label>
                        <p class="text-[11px] text-gray-500 mb-2">
                            চ্যাট অ্যাসিস্ট্যান্ট কোন এআই দিয়ে চলবে তা নির্দিষ্ট করে দিন (যেমন: DeepSeek, ChatGPT, Gemini, Groq, HuggingFace)।
                        </p>
                        <select name="ai_copilot_provider" id="ai_copilot_provider" class="w-full border-gray-300 rounded-lg text-xs font-bold focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/50 p-2.5">
                            <option value="default" {{ ($settings->ai_copilot_provider ?? 'default') === 'default' ? 'selected' : '' }}>⚡ Auto / Follow Primary AI (সিস্টেম প্রাইমারি এআই)</option>
                            <option value="deepseek" {{ ($settings->ai_copilot_provider ?? '') === 'deepseek' ? 'selected' : '' }}>🤖 DeepSeek-V3 (অত্যন্ত বুদ্ধিমান ও সাশ্রয়ী - প্রস্তাবিত)</option>
                            <option value="openai" {{ ($settings->ai_copilot_provider ?? '') === 'openai' ? 'selected' : '' }}>🧠 OpenAI (GPT-4o-mini / GPT-4o)</option>
                            <option value="gemini" {{ ($settings->ai_copilot_provider ?? '') === 'gemini' ? 'selected' : '' }}>✨ Google Gemini (1.5 Flash)</option>
                            <option value="groq" {{ ($settings->ai_copilot_provider ?? '') === 'groq' ? 'selected' : '' }}>⚡ Groq (Llama 3.3 - আল্ট্রা ফাস্ট স্পিড)</option>
                            <option value="huggingface" {{ ($settings->ai_copilot_provider ?? '') === 'huggingface' ? 'selected' : '' }}>🤗 Hugging Face (Qwen / Llama Open-Source)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                            <i class="fas fa-sliders-h text-emerald-600"></i>
                            AI Strictness / Temperature (নির্ভুলতার মাত্রা)
                        </label>
                        <p class="text-[11px] text-gray-500 mb-2">
                            <strong>0.1 - 0.3 (Strict & Fact-Based):</strong> কোনো বানিয়ে কথা বলবে না। (প্রস্তাবিত)
                        </p>
                        <div class="flex items-center gap-3">
                            <input type="range" id="ai_copilot_temperature_range" min="0.1" max="1.0" step="0.05" value="{{ old('ai_copilot_temperature', $settings->ai_copilot_temperature ?? 0.30) }}" class="w-full accent-emerald-600 cursor-pointer" oninput="document.getElementById('ai_copilot_temperature').value = this.value">
                            <input type="number" id="ai_copilot_temperature" name="ai_copilot_temperature" min="0.1" max="1.0" step="0.05" value="{{ old('ai_copilot_temperature', $settings->ai_copilot_temperature ?? 0.30) }}" class="w-20 border-gray-300 rounded-lg text-xs font-bold text-center text-emerald-700 bg-emerald-50 focus:border-emerald-500" oninput="document.getElementById('ai_copilot_temperature_range').value = this.value">
                        </div>
                    </div>
                </div>

                <div class="text-[11px] text-emerald-950 flex items-start gap-2 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
                    <i class="fas fa-shield-alt text-emerald-600 mt-0.5 text-sm"></i>
                    <div>
                        <strong>Live Zero-Downtime Training:</strong> এখানে সেভ করার সাথে সাথে আপনার প্ল্যাটফর্মের সব পেজের অন-সাইট AI Copilot তাৎক্ষণিকভাবে এই নতুন জ্ঞান ও নির্দেশনা অনুযায়ী কাজ শুরু করবে।
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai'))
        {{-- AI CONFIGURATION (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-indigo-50/40 hover:bg-indigo-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-base">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            AI Models & Providers
                        </h2>
                        <p class="text-xs text-gray-500">Configure primary model provider, fallback models, and provider credentials</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-indigo-100 text-indigo-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">AI Providers</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <p class="text-xs text-indigo-700 mb-5 font-medium">Set custom credentials and model variants per provider. If left blank, environment defaults from <code>.env</code> are used.</p>

                <div class="mb-6 bg-white p-4 rounded-lg border border-indigo-200 shadow-sm">
                    <label class="block text-xs font-bold text-gray-800 mb-2">Primary AI Provider</label>
                    <select name="primary_ai" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 font-semibold text-indigo-900 text-xs">
                        <option value="deepseek" {{ ($settings->primary_ai ?? 'deepseek') == 'deepseek' ? 'selected' : '' }}>DeepSeek</option>
                        <option value="qwen" {{ ($settings->primary_ai ?? '') == 'qwen' ? 'selected' : '' }}>Qwen (Alibaba / DashScope)</option>
                        <option value="groq" {{ ($settings->primary_ai ?? '') == 'groq' ? 'selected' : '' }}>Groq (Llama / Mixtral)</option>
                        <option value="huggingface" {{ ($settings->primary_ai ?? '') == 'huggingface' ? 'selected' : '' }}>Hugging Face (Inference API)</option>
                        <option value="openai" {{ ($settings->primary_ai ?? '') == 'openai' ? 'selected' : '' }}>OpenAI (ChatGPT)</option>
                        <option value="gemini" {{ ($settings->primary_ai ?? '') == 'gemini' ? 'selected' : '' }}>Gemini (Google)</option>
                    </select>
                    <p class="text-[11px] text-gray-500 mt-2">Articles are processed using this primary provider. If the request encounters rate limits or errors, configured fallbacks are attempted sequentially.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Gemini -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fab fa-google text-blue-500"></i> Gemini (Google AI)
                            </h3>
                            <button type="button" onclick="testAiProvider('gemini')" class="text-[11px] bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold px-3 py-1 rounded border border-blue-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="gemini_api_key" name="gemini_api_key" value="{{ old('gemini_api_key', $settings->gemini_api_key ?? '') }}" placeholder="AIzaSy... (Leave empty to use .env value)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="gemini_model" name="gemini_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (gemini-1.5-flash)</option>
                                    <option value="gemini-2.5-flash" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-flash' ? 'selected' : '' }}>Gemini 2.5 Flash (Fast + Balance)</option>
                                    <option value="gemini-2.5-pro" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-pro' ? 'selected' : '' }}>Gemini 2.5 Pro (Complex Reasoning)</option>
                                    <option value="gemini-2.5-flash-lite" {{ ($settings->gemini_model ?? '') == 'gemini-2.5-flash-lite' ? 'selected' : '' }}>Gemini 2.5 Flash-Lite (High Volume)</option>
                                    <option value="gemini-3.1-pro-preview" {{ ($settings->gemini_model ?? '') == 'gemini-3.1-pro-preview' ? 'selected' : '' }}>Gemini 3.1 Pro Preview (Latest Reasoning)</option>
                                    <option value="gemini-3.1-flash-lite-preview" {{ ($settings->gemini_model ?? '') == 'gemini-3.1-flash-lite-preview' ? 'selected' : '' }}>Gemini 3.1 Flash Lite Preview (Efficient)</option>
                                </select>
                            </div>
                        </div>
                        <div id="gemini_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- DeepSeek -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-brain text-indigo-600"></i> DeepSeek AI
                            </h3>
                            <button type="button" onclick="testAiProvider('deepseek')" class="text-[11px] bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold px-3 py-1 rounded border border-indigo-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="deepseek_api_key" name="deepseek_api_key" value="{{ old('deepseek_api_key', $settings->deepseek_api_key ?? '') }}" placeholder="sk-... (Leave empty to use .env value)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="deepseek_model" name="deepseek_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (deepseek-chat)</option>
                                    <option value="deepseek-chat" {{ ($settings->deepseek_model ?? '') == 'deepseek-chat' ? 'selected' : '' }}>DeepSeek V3 (deepseek-chat)</option>
                                    <option value="deepseek-reasoner" {{ ($settings->deepseek_model ?? '') == 'deepseek-reasoner' ? 'selected' : '' }}>DeepSeek R1 (deepseek-reasoner)</option>
                                </select>
                            </div>
                        </div>
                        <div id="deepseek_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- Qwen (DashScope) -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-microchip text-orange-500"></i> Qwen (DashScope API)
                            </h3>
                            <button type="button" onclick="testAiProvider('qwen')" class="text-[11px] bg-orange-50 hover:bg-orange-100 text-orange-700 font-bold px-3 py-1 rounded border border-orange-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="qwen_api_key" name="qwen_api_key" value="{{ old('qwen_api_key', $settings->qwen_api_key ?? '') }}" placeholder="sk-... (DashScope API Key)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="qwen_model" name="qwen_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (qwen-turbo)</option>
                                    <option value="qwen-turbo" {{ ($settings->qwen_model ?? '') == 'qwen-turbo' ? 'selected' : '' }}>Qwen Turbo (Fast & Cost-Effective)</option>
                                    <option value="qwen-plus" {{ ($settings->qwen_model ?? '') == 'qwen-plus' ? 'selected' : '' }}>Qwen Plus (Balanced)</option>
                                    <option value="qwen-max" {{ ($settings->qwen_model ?? '') == 'qwen-max' ? 'selected' : '' }}>Qwen Max (High Quality)</option>
                                </select>
                            </div>
                        </div>
                        <div id="qwen_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- Groq -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-bolt text-yellow-500"></i> Groq (Fast Inference)
                            </h3>
                            <button type="button" onclick="testAiProvider('groq')" class="text-[11px] bg-yellow-50 hover:bg-yellow-100 text-yellow-800 font-bold px-3 py-1 rounded border border-yellow-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="groq_api_key" name="groq_api_key" value="{{ old('groq_api_key', $settings->groq_api_key ?? '') }}" placeholder="gsk_... (Groq Console)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="groq_model" name="groq_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (llama-3.3-70b-versatile)</option>
                                    <option value="llama-3.3-70b-versatile" {{ ($settings->groq_model ?? '') == 'llama-3.3-70b-versatile' ? 'selected' : '' }}>Llama 3.3 70B (Versatile)</option>
                                    <option value="llama-3.1-8b-instant" {{ ($settings->groq_model ?? '') == 'llama-3.1-8b-instant' ? 'selected' : '' }}>Llama 3.1 8B (Low Latency)</option>
                                    <option value="mixtral-8x7b-32768" {{ ($settings->groq_model ?? '') == 'mixtral-8x7b-32768' ? 'selected' : '' }}>Mixtral 8x7B (MoE)</option>
                                </select>
                            </div>
                        </div>
                        <div id="groq_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- Hugging Face -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-smile text-amber-500"></i> Hugging Face (Inference API)
                            </h3>
                            <button type="button" onclick="testAiProvider('huggingface')" class="text-[11px] bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold px-3 py-1 rounded border border-amber-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">User Access Token</label>
                                <input type="password" id="huggingface_api_key" name="huggingface_api_key" value="{{ old('huggingface_api_key', $settings->huggingface_api_key ?? '') }}" placeholder="hf_... (HuggingFace Access Token)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Repository ID</label>
                                <input type="text" id="huggingface_model" name="huggingface_model" value="{{ old('huggingface_model', $settings->huggingface_model ?? '') }}" placeholder="e.g. meta-llama/Llama-3.2-3B-Instruct" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                        </div>
                        <div id="huggingface_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>

                    <!-- OpenAI -->
                    <div class="bg-white p-4 rounded-xl border border-indigo-100 shadow-sm col-span-1 md:col-span-2">
                        <div class="flex justify-between items-center mb-3 pb-2 border-b border-gray-100">
                            <h3 class="font-bold text-gray-800 text-xs flex items-center gap-1.5">
                                <i class="fas fa-cube text-emerald-600"></i> OpenAI (ChatGPT)
                            </h3>
                            <button type="button" onclick="testAiProvider('openai')" class="text-[11px] bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold px-3 py-1 rounded border border-emerald-200 transition cursor-pointer flex items-center gap-1">
                                <i class="fas fa-vial"></i> <span>Test Connection</span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">API Key</label>
                                <input type="password" id="openai_api_key" name="openai_api_key" value="{{ old('openai_api_key', $settings->openai_api_key ?? '') }}" placeholder="sk-proj-... (Leave empty to use .env value)" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Model Selection</label>
                                <select id="openai_model" name="openai_model" class="w-full border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-xs">
                                    <option value="">Default (gpt-4o-mini)</option>
                                    <option value="gpt-4o-mini" {{ ($settings->openai_model ?? '') == 'gpt-4o-mini' ? 'selected' : '' }}>GPT-4o Mini (Cost-Effective)</option>
                                    <option value="gpt-4o" {{ ($settings->openai_model ?? '') == 'gpt-4o' ? 'selected' : '' }}>GPT-4o (High Performance)</option>
                                    <option value="o1-mini" {{ ($settings->openai_model ?? '') == 'o1-mini' ? 'selected' : '' }}>o1-mini (Reasoning)</option>
                                    <option value="o3-mini" {{ ($settings->openai_model ?? '') == 'o3-mini' ? 'selected' : '' }}>o3-mini (Advanced Reasoning)</option>
                                </select>
                            </div>
                        </div>
                        <div id="openai_status_msg" class="text-xs font-bold mt-2 whitespace-pre-line"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai'))
        {{-- PHOTOROOM API SETTINGS (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-purple-50/40 hover:bg-purple-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center text-base">
                        <i class="fas fa-magic"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Background Removal API (PhotoRoom)
                        </h2>
                        <p class="text-xs text-gray-500">Automatic subject isolation and background removal for photo cards</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-purple-100 text-purple-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Background Removal</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <p class="text-xs text-purple-700 mb-4 font-medium">Enter your PhotoRoom API key for subject isolation in news photo cards. Super Admin configuration applies globally across all editorial accounts.</p>

                <div class="bg-white p-5 rounded-lg border border-purple-200 shadow-sm">
                    <label class="block text-xs font-bold text-gray-700 mb-1">PhotoRoom API Key (x-api-key)</label>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="password" id="photoroom_api_key" name="photoroom_api_key" value="{{ old('photoroom_api_key', $settings->photoroom_api_key ?? '') }}" placeholder="sk_pr_..." class="w-full border-gray-300 rounded shadow-sm focus:border-purple-500 focus:ring-purple-500 font-mono text-xs">
                        <button type="button" onclick="testPhotoRoom()" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded shadow-sm flex items-center justify-center gap-2 whitespace-nowrap transition cursor-pointer">
                            <i class="fas fa-vial"></i> <span>Test Connection</span>
                        </button>
                    </div>
                    <div id="photoroom_status_msg" class="text-xs font-bold mt-2"></div>
                    <p class="text-[11px] text-gray-500 mt-2">
                        <i class="fas fa-info-circle text-purple-500"></i> Get your API Key from the <a href="https://www.photoroom.com/api" target="_blank" class="text-purple-600 underline font-semibold">PhotoRoom Developer Console</a>.
                    </p>
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_ai') || auth()->user()->hasPermission('can_settings_tts'))
        {{-- AI VOICE NARRATION & TTS ENGINE (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-gradient-to-r from-amber-500/10 via-orange-500/5 to-transparent hover:bg-amber-50/80 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-amber-500 to-orange-500 text-white flex items-center justify-center text-base shadow-sm">
                        <i class="fas fa-microphone-lines"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            AI Voice Narration & TTS Newsroom Engine
                            @if(!empty($settings->tts_enabled))
                                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full font-extrabold flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Active
                                </span>
                            @else
                                <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-bold">Disabled</span>
                            @endif
                        </h2>
                        <p class="text-xs text-gray-500">স্বয়ংক্রিয় অডিও নিউজ জেনারেশন, মাল্টি-প্রোভাইডার ভয়েস (EdgeTTS/OpenAI/Google/ElevenLabs) ও প্লেয়ার এমবেডিং</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Audio News</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm space-y-6">
                
                {{-- Master Toggles --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="flex items-start gap-3 p-4 bg-white rounded-xl border border-gray-200 shadow-sm cursor-pointer hover:border-amber-400 transition">
                        <input type="checkbox" name="tts_enabled" value="1" {{ !empty($settings->tts_enabled) ? 'checked' : '' }} class="mt-1 w-4 h-4 text-amber-600 rounded border-gray-300 focus:ring-amber-500">
                        <div>
                            <span class="block text-xs font-bold text-gray-800">Enable AI Audio Narration (ভয়েস ন্যারেশন চালু করুন)</span>
                            <span class="block text-[11px] text-gray-500 mt-0.5">নিউজ আর্টিকেলের জন্য স্বয়ংক্রিয় প্রফেশনাল ব্রডকাস্ট ভয়েস ও অডিও প্লেয়ার সক্রিয় থাকবে।</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-4 bg-white rounded-xl border border-gray-200 shadow-sm cursor-pointer hover:border-amber-400 transition">
                        <input type="checkbox" name="tts_auto_generate_on_draft" value="1" {{ ($settings->tts_auto_generate_on_draft ?? true) ? 'checked' : '' }} class="mt-1 w-4 h-4 text-amber-600 rounded border-gray-300 focus:ring-amber-500">
                        <div>
                            <span class="block text-xs font-bold text-gray-800">Auto-Generate on Draft (ড্রাফটে স্বয়ংক্রিয় ভয়েস)</span>
                            <span class="block text-[11px] text-gray-500 mt-0.5">স্ক্র্যাপ বা রিরাইট হয়ে ড্রাফটে যাওয়ার সাথে সাথে ব্যাকগ্রাউন্ডে স্বয়ংক্রিয় অডিও তৈরি হবে।</span>
                        </div>
                    </label>
                </div>

                {{-- Provider & Voice Configuration --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm space-y-5">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                        <i class="fas fa-sliders text-amber-500"></i> ১. প্রাইমারি TTS প্রোভাইডার ও ভয়েস সেটিংস
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- TTS Provider --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">TTS Voice Provider</label>
                            <select name="tts_provider" id="tts_provider" onchange="toggleTtsProviderFields()" class="w-full border-gray-300 rounded-lg text-xs font-bold focus:border-amber-500 focus:ring-amber-500 bg-amber-50/40 p-2.5">
                                <option value="edgetts" {{ ($settings->tts_provider ?? 'edgetts') === 'edgetts' ? 'selected' : '' }}>🟢 Microsoft Edge TTS (100% Free - সেরা বাংলা)</option>
                                <option value="openai" {{ ($settings->tts_provider ?? '') === 'openai' ? 'selected' : '' }}>🟣 OpenAI TTS (tts-1 / tts-1-hd)</option>
                                <option value="google" {{ ($settings->tts_provider ?? '') === 'google' ? 'selected' : '' }}>🔵 Google Cloud TTS (Wavenet & Neural2)</option>
                                <option value="elevenlabs" {{ ($settings->tts_provider ?? '') === 'elevenlabs' ? 'selected' : '' }}>👑 ElevenLabs (VIP Human Broadcast Voice)</option>
                            </select>
                        </div>

                        {{-- Default Gender --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Default Voice Gender</label>
                            <select name="tts_selected_gender" id="tts_selected_gender" class="w-full border-gray-300 rounded-lg text-xs font-semibold focus:border-amber-500 focus:ring-amber-500 p-2.5">
                                <option value="male" {{ ($settings->tts_selected_gender ?? 'male') === 'male' ? 'selected' : '' }}>👨 পুরুষ কণ্ঠ (Male Voice - যেমন: Pradeep / Onyx)</option>
                                <option value="female" {{ ($settings->tts_selected_gender ?? '') === 'female' ? 'selected' : '' }}>👩 নারী কণ্ঠ (Female Voice - যেমন: Nabanita / Nova)</option>
                            </select>
                        </div>

                        {{-- Speech Speed --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Speaking Speed (গতি)</label>
                            <select name="tts_speed" id="tts_speed" class="w-full border-gray-300 rounded-lg text-xs font-semibold focus:border-amber-500 focus:ring-amber-500 p-2.5">
                                <option value="0.85" {{ (string)($settings->tts_speed ?? 1.0) === '0.85' ? 'selected' : '' }}>0.85x (ধীরগতির স্পষ্ট পাঠ)</option>
                                <option value="0.95" {{ (string)($settings->tts_speed ?? 1.0) === '0.95' ? 'selected' : '' }}>0.95x (স্বাভাবিক ধীরগতি)</option>
                                <option value="1.00" {{ (string)($settings->tts_speed ?? 1.0) === '1.00' || empty($settings->tts_speed) ? 'selected' : '' }}>1.00x (প্রমিত নিউজরুম স্পিড - প্রস্তাবিত)</option>
                                <option value="1.05" {{ (string)($settings->tts_speed ?? 1.0) === '1.05' ? 'selected' : '' }}>1.05x (দ্রুতগতির তাজা খবর)</option>
                                <option value="1.15" {{ (string)($settings->tts_speed ?? 1.0) === '1.15' ? 'selected' : '' }}>1.15x (ফাস্ট বুলেটিন)</option>
                            </select>
                        </div>

                        {{-- Target Website Embed Mode --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Target Website Embed Mode</label>
                            <select name="tts_embed_mode" id="tts_embed_mode" class="w-full border-gray-300 rounded-lg text-xs font-semibold focus:border-amber-500 focus:ring-amber-500 p-2.5">
                                <option value="top" {{ ($settings->tts_embed_mode ?? 'top') === 'top' ? 'selected' : '' }}>🔝 আর্টিকেলের শুরুতে (Top / Before 1st Paragraph)</option>
                                <option value="after_p1" {{ ($settings->tts_embed_mode ?? '') === 'after_p1' ? 'selected' : '' }}>1️⃣ ১ম অনুচ্ছেদের পরে (After 1st Paragraph)</option>
                                <option value="after_p2" {{ ($settings->tts_embed_mode ?? '') === 'after_p2' ? 'selected' : '' }}>2️⃣ ২য় অনুচ্ছেদের পরে (After 2nd Paragraph - সেরা এঙ্গেজমেন্ট)</option>
                                <option value="after_p3" {{ ($settings->tts_embed_mode ?? '') === 'after_p3' ? 'selected' : '' }}>3️⃣ ৩য় অনুচ্ছেদের পরে (After 3rd Paragraph)</option>
                                <option value="middle" {{ ($settings->tts_embed_mode ?? '') === 'middle' ? 'selected' : '' }}>🔀 আর্টিকেলের মাঝামাঝি স্থানে (Middle of Article)</option>
                                <option value="bottom" {{ ($settings->tts_embed_mode ?? '') === 'bottom' ? 'selected' : '' }}>🔚 আর্টিকেলের শেষে (Bottom of Article)</option>
                                <option value="manual" {{ ($settings->tts_embed_mode ?? '') === 'manual' ? 'selected' : '' }}>✍️ ম্যানুয়াল শর্টকোড পজিশন ([audio_player] শর্টকোড)</option>
                                <option value="api_only" {{ ($settings->tts_embed_mode ?? '') === 'api_only' ? 'selected' : '' }}>📡 শুধুমাত্র API পেলোডে audio_url যাবে (বডিতে নয়)</option>
                                <option value="none" {{ ($settings->tts_embed_mode ?? '') === 'none' ? 'selected' : '' }}>🔒 টার্গেট সাইটে যাবে না (শুধু ইন্টারনাল ড্রাফটে)</option>
                            </select>
                        </div>
                    </div>

                    {{-- Voice Overrides per Gender --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Custom Male Voice Identifier (ঐচ্ছিক)</label>
                            <input type="text" name="tts_voice_male" id="tts_voice_male" value="{{ old('tts_voice_male', $settings->tts_voice_male ?? '') }}" placeholder="e.g. bn-BD-PradeepNeural, onyx, bn-BD-Wavenet-B" class="w-full border-gray-300 rounded-lg text-xs font-mono p-2">
                            <p class="text-[10px] text-gray-400 mt-1">খালি রাখলে সিলেক্টেড প্রোভাইডারের ডিফল্ট পুরুষ কণ্ঠ ব্যবহার হবে।</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Custom Female Voice Identifier (ঐচ্ছিক)</label>
                            <input type="text" name="tts_voice_female" id="tts_voice_female" value="{{ old('tts_voice_female', $settings->tts_voice_female ?? '') }}" placeholder="e.g. bn-BD-NabanitaNeural, nova, bn-BD-Wavenet-A" class="w-full border-gray-300 rounded-lg text-xs font-mono p-2">
                            <p class="text-[10px] text-gray-400 mt-1">খালি রাখলে সিলেক্টেড প্রোভাইডারের ডিফল্ট নারী কণ্ঠ ব্যবহার হবে।</p>
                        </div>
                    </div>
                </div>

                {{-- External Provider API Keys --}}
                <div class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm space-y-4">
                    <h3 class="text-xs font-extrabold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                        <i class="fas fa-key text-indigo-500"></i> ২. পেইড প্রোভাইডার API Keys (EdgeTTS ব্যবহার করলে খালি রাখুন)
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">OpenAI TTS API Key (ঐচ্ছিক)</label>
                            <input type="password" name="tts_openai_key" id="tts_openai_key" value="{{ old('tts_openai_key', $settings->tts_openai_key ?? '') }}" placeholder="sk-proj-..." class="w-full border-gray-300 rounded-lg text-xs font-mono p-2.5">
                            <p class="text-[10px] text-gray-400 mt-1">খালি রাখলে প্রাইমারি OpenAI Key ব্যবহার হবে।</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">ElevenLabs API Key (xi-api-key)</label>
                            <input type="password" name="tts_elevenlabs_key" id="tts_elevenlabs_key" value="{{ old('tts_elevenlabs_key', $settings->tts_elevenlabs_key ?? '') }}" placeholder="sk_..." class="w-full border-gray-300 rounded-lg text-xs font-mono p-2.5">
                            <p class="text-[10px] text-gray-400 mt-1">ElevenLabs ড্যাশবোর্ড থেকে প্রাপ্ত এপিআই কি।</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Google Cloud TTS API Key</label>
                            <input type="password" name="tts_google_key" id="tts_google_key" value="{{ old('tts_google_key', $settings->tts_google_key ?? '') }}" placeholder="AIzaSy..." class="w-full border-gray-300 rounded-lg text-xs font-mono p-2.5">
                            <p class="text-[10px] text-gray-400 mt-1">Google Cloud Console থেকে Text-to-Speech Key।</p>
                        </div>
                    </div>
                </div>

                {{-- Interactive Voice Synthesis Audio Tester --}}
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 rounded-2xl border border-indigo-500/30 shadow-md space-y-4">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2.5">
                            <span class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-sm border border-amber-500/30">
                                <i class="fas fa-play"></i>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-white text-xs uppercase tracking-wider">লাইভ ভয়েস টেস্টিং ল্যাব (Interactive Voice Testing)</h3>
                                <p class="text-[11px] text-slate-300">সেভ করার আগেই আপনার নির্বাচিত প্রোভাইডার ও ভয়েস দিয়ে বাংলা অডিও প্লে করে শুনুন</p>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-indigo-500/30 text-indigo-300 border border-indigo-400/30">
                            Instant Audio Preview
                        </span>
                    </div>

                    <div class="space-y-3 pt-2 border-t border-white/10">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 mb-1">টেস্ট করার জন্য বাংলা নমুনা বাক্য লিখুন:</label>
                            <input type="text" id="tts_test_sample_text" value="স্বাগতম! সাব-এডিটর টোয়েন্টিফোর এআই ভয়েস ন্যারেশন সিস্টেম সক্রিয় রয়েছে। আজকের প্রধান সংবাদ শুনুন।" class="w-full bg-slate-800/80 border border-slate-700 text-white rounded-xl p-2.5 text-xs focus:ring-amber-500 focus:border-amber-500">
                        </div>

                        <div class="flex flex-col sm:flex-row items-center gap-3">
                            <button type="button" onclick="testTtsVoiceSynthesis()" id="tts_test_btn" class="w-full sm:w-auto px-5 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-amber-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                                <i class="fas fa-volume-high"></i>
                                <span>🎙️ টেস্ট ভয়েস তৈরি ও প্লে করুন</span>
                            </button>

                            <div id="tts_test_status_msg" class="text-xs font-semibold text-slate-300"></div>
                        </div>

                        {{-- Hidden/Visible Preview Player --}}
                        <div id="tts_test_player_container" class="hidden mt-3 p-3 bg-white/5 rounded-xl border border-white/10">
                            <audio id="tts_test_audio_player" controls class="w-full h-8"></audio>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_wp_laravel'))
        {{-- WordPress Connection (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-base">
                        <i class="fab fa-wordpress"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            WordPress REST API Integration
                        </h2>
                        <p class="text-xs text-gray-500">Endpoint URL, editorial username, and Application Password</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-blue-100 text-blue-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">WordPress</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex justify-between items-center mb-3">
                    <p class="text-xs text-gray-500">Verify WordPress REST API connectivity and authentication:</p>
                    <button type="button" onclick="testWordPress()" class="text-xs bg-gray-100 text-gray-700 px-3.5 py-2 rounded-lg hover:bg-gray-200 transition font-bold border border-gray-300 cursor-pointer">
                        Test Connection
                    </button>
                </div>
                
                <p id="wp_status_msg" class="text-xs font-bold mb-4"></p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Website URL</label>
                        <input type="text" id="wp_url" name="wp_url" value="{{ old('wp_url', $settings->wp_url ?? '') }}" placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Username</label>
                        <input type="text" id="wp_username" name="wp_username" value="{{ old('wp_username', $settings->wp_username ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">App Password</label>
                        <input type="password" id="wp_app_password" name="wp_app_password" value="{{ old('wp_app_password', $settings->wp_app_password ?? '') }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs" placeholder="abcd efgh ijkl mnop">
                        <p class="text-[11px] text-gray-500 mt-1">Generate from WP Admin > Users > Profile > Application Passwords.</p>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- EXTERNAL & CUSTOM WEBSITE CONNECTION SECTION (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-slate-50 hover:bg-slate-100 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center text-base">
                        <i class="fas fa-plug"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            External Website API & Webhooks (Laravel / Next.js / Custom CMS)
                        </h2>
                        <p class="text-xs text-gray-500">REST API publishing, webhooks, payload mapping, and integration snippets</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-slate-200 text-slate-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">REST API / Webhook</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-4 border-b border-gray-200 pb-3 gap-2">
                    <p class="text-xs text-gray-600 font-medium">Configure REST endpoints for automated publishing to your Laravel, Next.js, or custom newsroom CMS.</p>
                    
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="openCodeGeneratorModal()" class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 px-3.5 py-1.5 rounded-lg hover:bg-indigo-100 transition shadow-sm cursor-pointer">
                            <i class="fas fa-code"></i> Integration Snippets
                        </button>
                        <a href="{{ route('docs.api-guide') }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-slate-100 border border-slate-300 px-3.5 py-1.5 rounded-lg hover:bg-slate-200 transition">
                            <i class="fas fa-book-open text-slate-500"></i> API Documentation
                        </a>
                    </div>
                </div>

                <!-- Connection Status Message Box -->
                <div id="custom_api_status_box" class="hidden mb-4 p-3 rounded-lg text-xs font-bold"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Base / Website URL -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label class="block text-xs font-bold text-gray-700">Website Base URL</label>
                            <button type="button" onclick="showFieldHelp('laravel_site_url')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-100 hover:bg-indigo-100 text-slate-400 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on Website Base URL">
                                <i class="fas fa-info"></i>
                            </button>
                        </div>
                        <input type="text" id="laravel_site_url" name="laravel_site_url" value="{{ old('laravel_site_url', $settings->laravel_site_url ?? '') }}" 
                               placeholder="https://mywebsite.com" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                        <p class="text-[11px] text-gray-500 mt-1">Your website domain URL (e.g. <code>https://mywebsite.com</code>).</p>
                    </div>

                    <!-- API Secret Token -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label class="block text-xs font-bold text-gray-700">API Secret Token</label>
                            <button type="button" onclick="showFieldHelp('laravel_api_token')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-indigo-100 hover:bg-indigo-200 text-indigo-600 transition text-[10px] font-bold cursor-pointer" title="Click to see where & how to configure this token in your Laravel project">
                                <i class="fas fa-info"></i>
                            </button>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" id="laravel_api_token" name="laravel_api_token" value="{{ old('laravel_api_token', $settings->laravel_api_token ?? '') }}" 
                                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition font-mono text-xs" placeholder="e.g. sec_token_2026_xyz">
                            <button type="button" onclick="generateRandomToken()" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg border border-gray-300 flex-shrink-0 flex items-center gap-1 cursor-pointer" title="Generate new secure token">
                                <i class="fas fa-sync-alt text-[10px]"></i> Generate
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Secret key used for secure server-to-server handshake.</p>
                    </div>

                    <!-- Route Prefix -->
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label class="block text-xs font-bold text-gray-700">News Link Prefix</label>
                            <button type="button" onclick="showFieldHelp('laravel_route_prefix')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-100 hover:bg-indigo-100 text-slate-400 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on News Link Prefix">
                                <i class="fas fa-info"></i>
                            </button>
                        </div>
                        <div class="flex items-center">
                            <span class="bg-gray-100 border border-r-0 border-gray-300 px-3 py-2 rounded-l text-gray-500 text-xs">/</span>
                            <input type="text" name="laravel_route_prefix" value="{{ old('laravel_route_prefix', $settings->laravel_route_prefix ?? 'news') }}" 
                                   class="w-full border-gray-300 rounded-r shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs" 
                                   placeholder="news, post, article">
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Post link format: <code>site.com/<b>news</b>/123</code></p>
                    </div>

                    <!-- Enable Auto Post Checkbox -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer bg-slate-50 hover:bg-slate-100 p-3.5 rounded-lg border border-slate-200 w-full transition">
                            <input type="hidden" name="post_to_laravel" value="0">
                            <input type="checkbox" name="post_to_laravel" value="1" {{ ($settings->post_to_laravel ?? false) ? 'checked' : '' }} class="toggle-checkbox w-5 h-5 text-indigo-600 rounded">
                            <div class="flex-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-gray-800 text-xs block">Enable Automatic Publishing</span>
                                    <button type="button" onclick="event.stopPropagation(); showFieldHelp('post_to_laravel');" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-200 hover:bg-indigo-200 text-slate-500 hover:text-indigo-700 transition text-[10px] cursor-pointer" title="Click for details">
                                        <i class="fas fa-info"></i>
                                    </button>
                                </div>
                                <span class="text-[11px] text-gray-500 block">When active, approved articles are immediately dispatched to your external website API.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Test Connection Button -->
                <div class="mt-4 pt-3 border-t border-gray-200 flex flex-wrap items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">Test endpoint availability and authentication handshake:</span>
                    <button type="button" onclick="testCustomApiConnection()" id="btn_test_custom_api" class="inline-flex items-center gap-2 text-xs bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-lg transition shadow-sm cursor-pointer">
                        <i class="fas fa-plug"></i> Test Connection
                    </button>
                </div>

                {{-- ADVANCED CUSTOM FIELD MAPPER --}}
                <div class="mt-6 border border-slate-200 rounded-xl overflow-hidden bg-white">
                    <div class="bg-slate-100 p-4 border-b border-slate-200 flex justify-between items-center cursor-pointer select-none" onclick="toggleCustomApiVisual()">
                        <div class="flex items-center gap-2">
                            <i id="mapper_chevron" class="fas fa-chevron-down text-slate-500 text-xs transition-transform"></i>
                            <span class="font-bold text-slate-800 text-xs">Payload Field Mapping & Custom Endpoints</span>
                            <span class="text-[10px] bg-slate-200 text-slate-700 font-semibold px-2 py-0.5 rounded">Optional</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-indigo-600 font-semibold hover:underline flex items-center gap-1">
                                <i class="fas fa-sliders-h"></i> Field Mapping
                            </span>
                            <button type="button" onclick="event.stopPropagation(); openAssistantHelpModal();" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 px-3 py-1 rounded-lg transition shadow-sm">
                                <i class="fas fa-info-circle text-indigo-600"></i> Setup Guide
                            </button>
                        </div>
                    </div>

                    <div id="custom-api-visual-section" class="p-5 space-y-6 hidden">
                        <div class="flex flex-wrap justify-between items-center bg-slate-50 border border-slate-200 p-3 rounded-lg text-xs text-slate-700 gap-2">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-info-circle text-slate-500 text-sm"></i>
                                <span>Specify custom endpoint routes or map internal payload keys to match your database schema.</span>
                            </div>
                            <button type="button" onclick="openAssistantHelpModal()" class="text-indigo-600 font-semibold hover:underline flex items-center gap-1 text-[11px]">
                                Open Setup Guide <i class="fas fa-arrow-right text-[10px]"></i>
                            </button>
                        </div>

                        <!-- Custom URLs -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <label class="block text-xs font-bold text-slate-700">Custom Article Post Endpoint (Optional)</label>
                                    <button type="button" onclick="showFieldHelp('custom_api_url')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-200 hover:bg-indigo-100 text-slate-500 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on Custom News Post Endpoint">
                                        <i class="fas fa-info"></i>
                                    </button>
                                </div>
                                <input type="text" id="custom_api_url" name="custom_api_url" value="{{ old('custom_api_url', $settings->custom_api_url ?? '') }}" 
                                       placeholder="https://mywebsite.com/api/v1/articles/create" class="w-full border-slate-300 rounded-lg shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">If empty, defaults to <code>Base_URL/api/external-news-post</code>.</p>
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5 mb-1">
                                    <label class="block text-xs font-bold text-slate-700">Custom Category Endpoint (Optional)</label>
                                    <button type="button" onclick="showFieldHelp('custom_category_url')" class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-slate-200 hover:bg-indigo-100 text-slate-500 hover:text-indigo-600 transition text-[10px] cursor-pointer" title="Click for help on Category Fetch URL">
                                        <i class="fas fa-info"></i>
                                    </button>
                                </div>
                                <input type="text" id="custom_category_url" name="custom_category_url" value="{{ old('custom_category_url', $settings->custom_category_url ?? '') }}" 
                                       placeholder="https://mywebsite.com/api/v1/categories" class="w-full border-slate-300 rounded-lg shadow-sm text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-[10px] text-slate-400 mt-1">API URL to fetch categories from your website.</p>
                            </div>
                        </div>

                        <!-- Auth & Format Options -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Authentication Type</label>
                                <select id="v_auth_type" onchange="syncVisualToMappingJson()" class="w-full border-slate-300 rounded shadow-sm text-xs focus:ring-indigo-500">
                                    <option value="Bearer">Bearer Token (Authorization: Bearer ...)</option>
                                    <option value="custom_header">Custom Header (e.g. X-API-KEY)</option>
                                    <option value="basic">Basic Auth (Authorization: Basic ...)</option>
                                    <option value="body">Inside Body / Payload (token: ...)</option>
                                </select>
                            </div>
                            <div id="v_auth_header_wrapper" style="display: none;">
                                <label class="block text-xs font-bold text-slate-700 mb-1">Custom Header Name</label>
                                <input type="text" id="v_auth_header_name" oninput="syncVisualToMappingJson()" placeholder="X-API-KEY" class="w-full border-slate-300 rounded shadow-sm text-xs font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Image Delivery Mode</label>
                                <select id="v_image_format" onchange="syncVisualToMappingJson()" class="w-full border-slate-300 rounded shadow-sm text-xs focus:ring-indigo-500">
                                    <option value="url">Image URL (Remote link)</option>
                                    <option value="file">Binary File Upload (Multipart)</option>
                                    <option value="base64">Base64 Encoded String</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Category Identifier Type</label>
                                <select id="v_category_type" onchange="syncVisualToMappingJson()" class="w-full border-slate-300 rounded shadow-sm text-xs focus:ring-indigo-500">
                                    <option value="id">Category ID (Integer)</option>
                                    <option value="name">Category Name (String)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Visual Field Name Mapper -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-3">Field Mapping (Default Payload Key ➔ Target API Parameter)</h4>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Title (Headline)</label>
                                    <input type="text" id="v_field_title" oninput="syncVisualToMappingJson()" placeholder="title / headline" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Content (Body Text)</label>
                                    <input type="text" id="v_field_content" oninput="syncVisualToMappingJson()" placeholder="content / body / desc" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Image (Featured Photo)</label>
                                    <input type="text" id="v_field_image" oninput="syncVisualToMappingJson()" placeholder="image / thumbnail_url" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Category (Taxonomy)</label>
                                    <input type="text" id="v_field_category" oninput="syncVisualToMappingJson()" placeholder="category / category_id" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Tags / Hashtags</label>
                                    <input type="text" id="v_field_tags" oninput="syncVisualToMappingJson()" placeholder="tags / hashtags" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Slug (URL Slug)</label>
                                    <input type="text" id="v_field_slug" oninput="syncVisualToMappingJson()" placeholder="slug / alias" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Response ID Key</label>
                                    <input type="text" id="v_field_response_id_key" oninput="syncVisualToMappingJson()" placeholder="post_id / id" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                                <div>
                                    <label class="block font-bold text-slate-600 mb-1">Response URL Key</label>
                                    <input type="text" id="v_field_response_url_key" oninput="syncVisualToMappingJson()" placeholder="live_url / link / url" class="w-full border-slate-300 rounded p-1.5 font-mono text-xs">
                                </div>
                            </div>
                        </div>

                        <!-- Extra Static Key-Value Fields -->
                        <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                            <div class="flex justify-between items-center mb-2">
                                <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Additional Payload Parameters (Optional)</h4>
                                <button type="button" onclick="addExtraFieldRow()" class="text-xs bg-white hover:bg-slate-100 text-slate-700 font-semibold px-2.5 py-1 rounded border border-slate-300 cursor-pointer">
                                    + Add Parameter
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-500 mb-3">Define fixed parameters required by your receiving controller (e.g. <code>author_id = 1</code> or <code>status = published</code>).</p>
                            
                            <div id="extra_fields_container" class="space-y-2">
                                <!-- Dynamic rows appended via JS -->
                            </div>
                        </div>

                        <!-- Raw JSON Mapping (Hidden sync & toggle) -->
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="text-[11px] font-bold text-slate-500">Raw JSON Configuration (Synchronized)</label>
                                <button type="button" onclick="toggleRawJson()" class="text-[11px] text-indigo-600 hover:underline cursor-pointer">View / Edit JSON</button>
                            </div>
                            <textarea id="custom_api_mapping" name="custom_api_mapping" rows="3" 
                                      class="w-full border-slate-300 rounded-lg shadow-sm text-xs font-mono focus:ring-indigo-500 focus:border-indigo-500 bg-slate-100 hidden" 
                                      placeholder='{"title":"title","content":"content"}'>{{ old('custom_api_mapping', is_array($settings->custom_api_mapping) ? json_encode($settings->custom_api_mapping) : ($settings->custom_api_mapping ?? '')) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- INTERACTIVE ASSISTANT HELP & FAQ MODAL --}}
        <div id="assistantHelpModal" class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-5xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden text-slate-100 font-bangla">
                <!-- Modal Header -->
                <div class="p-5 bg-slate-800/90 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg shadow-inner">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base sm:text-lg text-white font-sans">
                                Website Integration & Publishing Guide
                            </h3>
                            <p class="text-xs text-slate-400">Laravel, WordPress ও কাস্টম ফ্রন্টএন্ড সংযোগের বিস্তারিত নির্দেশিকা ও ট্রাবলশুটিং।</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('docs.api-guide') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-xs text-indigo-400 hover:text-indigo-300 bg-indigo-500/10 border border-indigo-500/20 px-3 py-1.5 rounded-xl font-sans font-bold">
                            <i class="fas fa-external-link-alt"></i> Full Docs
                        </a>
                        <button type="button" onclick="closeAssistantHelpModal()" class="w-8 h-8 rounded-full bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer font-sans">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="flex border-b border-slate-800 bg-slate-950 px-5 gap-2 text-xs font-bold font-sans">
                    <button type="button" onclick="switchHelpTab('walkthrough')" class="help-tab-btn px-4 py-3 border-b-2 border-indigo-500 text-indigo-400 flex items-center gap-2 cursor-pointer" id="htab_walkthrough">
                        <i class="fas fa-list-ol"></i> Setup Steps
                    </button>
                    <button type="button" onclick="switchHelpTab('faq')" class="help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 cursor-pointer" id="htab_faq">
                        <i class="fas fa-question-circle"></i> Frequently Asked Questions
                    </button>
                    <button type="button" onclick="switchHelpTab('diagnostics')" class="help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2 cursor-pointer" id="htab_diagnostics">
                        <i class="fas fa-wrench"></i> Troubleshooting & Errors
                    </button>
                </div>

                <!-- Tab Contents -->
                <div class="p-6 overflow-y-auto flex-1 bg-slate-900 space-y-6 text-xs text-slate-300">
                    
                    <!-- 1. WALKTHROUGH CONTENT -->
                    <div id="help_content_walkthrough" class="space-y-4">
                        <!-- Step 1 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">১</span>
                                ১. বেসিক কানেকশন ও API Secret Token (.env সেটআপ)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                Settings পেজে আপনার ওয়েবসাইটের ডোমেইন দিন (যেমন <code class="text-indigo-300 bg-slate-950 px-1.5 py-0.5 rounded font-mono">https://mybanglanews.com</code>)। এরপর <strong>API Secret Token</strong> এর <strong>Generate</strong> বাটনে ক্লিক করে একটি গোপন চাবি তৈরি করুন এবং সেটি আপনার Laravel প্রজেক্টের <code>.env</code> ফাইলে যোগ করুন:
                            </p>
                            <pre class="!py-2 !px-3 bg-slate-950 rounded-xl border border-slate-800 text-emerald-400 font-mono text-[11px]"><code>SUBEDITOR_API_SECRET=আপনার_সিক্রেট_টোকেন_এখানে</code></pre>
                        </div>

                        <!-- Step 2 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">২</span>
                                ২. Laravel প্রজেক্টে API রুট কোড বসানো (routes/api.php)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                আপনার Laravel প্রজেক্টের <code class="text-indigo-300 font-mono">routes/api.php</code> ফাইলে নিউজ রিসিভার এবং ক্যাটাগরি ফেচিং রুট যুক্ত করুন:
                            </p>
                            <div class="p-2.5 rounded-lg bg-slate-950/80 border border-slate-800 text-[11px] text-slate-300 space-y-1">
                                <p><strong class="text-emerald-400">Laravel 10 ও পূর্ববর্তী:</strong> <code class="text-indigo-300">routes/api.php</code> ফাইলটি ডিফল্টভাবেই থাকে।</p>
                                <p><strong class="text-amber-400">Laravel 11 ও 12:</strong> ডিফল্ট না থাকলে টার্মিনালে রান করুন: <code class="text-amber-300 font-mono">php artisan install:api</code></p>
                            </div>
                            <div class="p-3 bg-slate-950 rounded-xl border border-slate-800 font-mono text-[11px] text-slate-300 space-y-1">
                                <p class="text-indigo-400 font-bold">// 1. News Receiver Route</p>
                                <p>Route::post('/external-news-post', function (Request $request) { ... });</p>
                                <p class="text-emerald-400 font-bold mt-2">// 2. Category Fetch Route</p>
                                <p>Route::get('/get-categories', function (Request $request) { ... });</p>
                            </div>
                            <div class="pt-1 flex gap-2">
                                <button type="button" onclick="openCodeGeneratorModal(); closeAssistantHelpModal();" class="text-xs text-indigo-400 hover:text-indigo-300 font-bold flex items-center gap-1 font-sans cursor-pointer">
                                    <i class="fas fa-code"></i> Open Code Generator to Copy Complete Laravel Code →
                                </button>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">৩</span>
                                ৩. ক্যাটাগরি রিফ্রেশ ও ম্যাপিং (Category Sync & Mapping)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                Settings পেজের <strong>"📂 Category Mapping"</strong> সেকশনে গিয়ে <strong>"🔄 Refresh Categories"</strong> বাটনে ক্লিক করুন। সাথে সাথে আপনার Laravel ওয়েবসাইটের সব ক্যাটাগরি চলে আসবে। এরপর বামপাশের AI বিষয়ের সাথে ডানপাশের আপনার ওয়েবসাইটের ক্যাটাগরি সিলেক্ট করে দিন।
                            </p>
                        </div>

                        <!-- Step 4 -->
                        <div class="bg-slate-800/80 p-4 sm:p-5 rounded-2xl border border-slate-700 space-y-2">
                            <h4 class="text-sm font-bold text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs flex items-center justify-center font-bold font-sans">৪</span>
                                ৪. কানেকশন টেস্ট ও অটো-পাবলিশিং (Test Connection & Auto-Publish)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">
                                কোড বসানোর পর <strong>"Test Connection"</strong> বাটনে চাপ দিন। সিস্টেম সাথে সাথে একটি টেস্ট পোস্ট পাঠিয়ে লাইভ কানেকশন চেক করবে। সফল হলে <strong>"Enable Auto-Publish"</strong> অন রাখুন যাতে কোনো নিউজ এপ্রুভ হওয়ার সাথে সাথেই আপনার ওয়েবসাইটে পাবলিশ হয়ে যায়।
                            </p>
                        </div>
                    </div>

                    <!-- 2. FAQ CONTENT -->
                    <div id="help_content_faq" class="space-y-3 hidden">
                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: API Secret Token কোথায় এবং কিভাবে বসাব?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Settings পেজ থেকে টোকেনটি কপি করে আপনার Laravel প্রজেক্টের <code>.env</code> ফাইলে <code class="text-indigo-300 font-mono">SUBEDITOR_API_SECRET=your_token</code> হিসেবে বসান। এরপর <code>routes/api.php</code> তে রিকোয়েস্টের হেডার <code>Authorization: Bearer ...</code> চেক করে টোকেন যাচাই করা হয়।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: ক্যাটাগরি ফেচ কিভাবে কাজ করে?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Subeditor24 আপনার ওয়েবসাইটের <code>/api/get-categories</code> রুটে কল করে ক্যাটাগরি তালিকা নিয়ে আসে। আপনার API থেকে <code>[{"id": 1, "name": "জাতীয়"}, {"id": 2, "name": "আন্তর্জাতিক"}]</code> ফরম্যাটে JSON অ্যারে রিটার্ন করতে হবে।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: ছবি কিভাবে আপলোড হয়?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Subeditor24 মূল সংবাদের ছবি ডাউনলোড করে multipart/form-data হিসেবে ফিজিক্যাল ফাইল আপলোড পাঠায়। আপনার Laravel এ <code>$request->file('image')->store('news', 'public')</code> দিয়ে সরাসরি সেভ করতে পারবেন।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: আমার ডাটাবেসে ফিল্ডের নাম আলাদা (যেমন title এর বদলে headline), কি করব?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: Settings পেজের <strong>"⚙️ Custom API Mapping"</strong> অপশনটিতে গিয়ে <code class="text-indigo-300">"title": "headline"</code> লিখে দিন। Subeditor24 স্বয়ংক্রিয়ভাবে ফিল্ডের নাম পরিবর্তন করে পাঠাবে।</p>
                        </div>

                        <div class="bg-slate-800 p-4 rounded-xl border border-slate-700 space-y-1">
                            <h4 class="font-bold text-white text-xs sm:text-sm">প্রশ্ন: লোকালহোস্ট (Localhost / 127.0.0.1) বা Staging এ কাজ করবে?</h4>
                            <p class="text-slate-300 leading-relaxed">উত্তর: হ্যাঁ! Subeditor24 লোকালহোস্ট ও সেলফ-সাইন্ড SSL সার্টিফিকেট স্বয়ংক্রিয়ভাবে বাইপাস করে কানেক্ট হতে পারে।</p>
                        </div>
                    </div>

                    <!-- 3. DIAGNOSTICS & TROUBLESHOOTING CONTENT -->
                    <div id="help_content_diagnostics" class="space-y-3 hidden">
                        <div class="bg-rose-950/40 border border-rose-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-rose-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i> HTTP 401 Unauthorized (অননুমোদিত রিকোয়েস্ট)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: API Secret Token ম্যাচ করেনি।<br>সমাধান: Settings এর টোকেন এবং আপনার Laravel <code>.env</code> ফাইলের টোকেন মিলিয়ে নিন। এরপর টার্মিনালে <code>php artisan config:clear</code> দিন। Apache সার্ভার হলে <code>.htaccess</code> এ <code>CGIPassAuth on</code> যোগ করুন।</p>
                        </div>

                        <div class="bg-amber-950/40 border border-amber-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-amber-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-exclamation-circle"></i> HTTP 404 Not Found (রুট খুঁজে পাওয়া যায়নি)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: API রুটটি আপনার <code>routes/api.php</code> ফাইলে ডিফাইন করা হয়নি অথবা Base URL এর শেষে অতিরিক্ত স্ল্যাশ পড়েছে।<br>সমাধান: Base URL চেক করুন (যেমন <code>https://mywebsite.com</code>) এবং <code>routes/api.php</code> ফাইলে <code>Route::post('/external-news-post', ...)</code> রয়েছে কিনা নিশ্চিত হোন।</p>
                        </div>

                        <div class="bg-purple-950/40 border border-purple-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-purple-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-shield-alt"></i> HTTP 419 Page Expired / CSRF Error
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: রুটটি ভুলবশত <code>routes/web.php</code> তে লেখা হয়েছে।<br>সমাধান: API রুটগুলো অবশ্যই <code>routes/api.php</code> ফাইলে লিখতে হবে, যেখানে CSRF টোকেনের প্রয়োজন নেই।</p>
                        </div>

                        <div class="bg-cyan-950/40 border border-cyan-800/60 p-4 rounded-xl space-y-1">
                            <h4 class="font-bold text-cyan-300 text-xs sm:text-sm flex items-center gap-2">
                                <i class="fas fa-file-invoice"></i> HTTP 422 Unprocessable Content (ভ্যালিডেশন ফেইল)
                            </h4>
                            <p class="text-slate-300 leading-relaxed">কারণ: আপনার রিসিভার কোডে কোনো কাস্টম ফিল্ডকে <code>required</code> করা আছে যা Subeditor24 পাঠায়নি।<br>সমাধান: অপ্রয়োজনীয় ফিল্ডগুলোকে <code>nullable</code> করুন।</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 bg-slate-800/80 border-t border-slate-700/80 flex flex-wrap justify-between items-center gap-3">
                    <a href="{{ route('docs.api-guide') }}" target="_blank" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5 font-sans">
                        <i class="fas fa-book-open"></i> Open Comprehensive Live API Documentation Page →
                    </a>
                    <button type="button" onclick="closeAssistantHelpModal()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer font-sans">
                        Got it (ঠিক আছে)
                    </button>
                </div>
            </div>
        </div>

        {{-- CODE GENERATOR MODAL --}}
        <div id="codeGeneratorModal" class="fixed inset-0 z-50 bg-black bg-opacity-75 flex items-center justify-center p-4 hidden backdrop-blur-sm">
            <div class="bg-slate-900 border border-slate-700 rounded-2xl max-w-4xl w-full max-h-[92vh] flex flex-col shadow-2xl overflow-hidden text-slate-100">
                <!-- Modal Header -->
                <div class="p-5 bg-slate-800 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg">
                            <i class="fas fa-code"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-white">API Receiver Code Generator</h3>
                            <p class="text-xs text-slate-400">Select your backend framework to get clean, copy-pasteable receiver code.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeCodeGeneratorModal()" class="text-slate-400 hover:text-white text-xl p-2 rounded-lg hover:bg-slate-700 cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Framework Selector Tabs -->
                <div class="flex flex-wrap border-b border-slate-800 bg-slate-950 px-5 gap-2 text-xs font-bold py-2">
                    <button type="button" onclick="switchCodeGenTab('laravel')" class="cg-tab-btn px-3 py-2 rounded-lg bg-indigo-600 text-white cursor-pointer" id="cg_tab_laravel">Laravel (Full routes/api.php)</button>
                    <button type="button" onclick="switchCodeGenTab('wp')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white cursor-pointer" id="cg_tab_wp">WordPress (functions.php)</button>
                    <button type="button" onclick="switchCodeGenTab('next_app')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white cursor-pointer" id="cg_tab_next_app">Next.js (App Router)</button>
                    <button type="button" onclick="switchCodeGenTab('next_pages')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white cursor-pointer" id="cg_tab_next_pages">Next.js (Pages)</button>
                    <button type="button" onclick="switchCodeGenTab('express')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white cursor-pointer" id="cg_tab_express">Node.js (Express)</button>
                    <button type="button" onclick="switchCodeGenTab('php')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white cursor-pointer" id="cg_tab_php">Raw PHP Drop-in</button>
                    <button type="button" onclick="switchCodeGenTab('python')" class="cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white cursor-pointer" id="cg_tab_python">Python (FastAPI)</button>
                </div>

                <!-- Code Container -->
                <div class="p-5 overflow-y-auto flex-1 bg-slate-950 font-mono text-xs text-slate-200">
                    <div class="flex justify-between items-center mb-2 pb-2 border-b border-slate-800 text-[11px] text-slate-400">
                        <span id="cg_target_file_path" class="text-indigo-400">Target File: app/api/external-news-post/route.ts</span>
                        <button type="button" onclick="copyGeneratedCode()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-sans px-3 py-1 rounded text-xs flex items-center gap-1 transition cursor-pointer">
                            <i class="fas fa-copy"></i> Copy Code
                        </button>
                    </div>
                    <pre><code id="cg_code_content" class="text-emerald-400 leading-relaxed block overflow-x-auto whitespace-pre"></code></pre>
                </div>
            </div>
        </div>

        {{-- INTERACTIVE FIELD HELP & SETUP MODAL --}}
        <div id="fieldInfoModal" class="fixed inset-0 z-[120] bg-slate-950/80 backdrop-blur-sm hidden items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-700 rounded-3xl max-w-2xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden text-slate-100 animate-in fade-in zoom-in-95 duration-150">
                {{-- Modal Header --}}
                <div class="p-5 bg-slate-800/90 border-b border-slate-700 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/20 border border-indigo-500/30 flex items-center justify-center text-indigo-400 text-lg shadow-inner" id="fieldInfoIcon">
                            <i class="fas fa-info-circle"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-base text-white flex items-center gap-2" id="fieldInfoTitle">
                                Field Information
                            </h3>
                            <p class="text-xs text-slate-400" id="fieldInfoSubtitle">Configuration & Integration Guide</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeFieldInfoModal()" class="w-8 h-8 rounded-full bg-slate-700 hover:bg-slate-600 text-slate-300 hover:text-white flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>

                {{-- Modal Body --}}
                <div class="p-6 overflow-y-auto space-y-5 text-xs text-slate-300 leading-relaxed font-bangla">
                    {{-- Field Description --}}
                    <div id="fieldInfoDesc" class="p-4 bg-slate-800/60 rounded-2xl border border-slate-700/60 text-slate-200">
                        <!-- Injected -->
                    </div>

                    {{-- Step-by-Step Setup --}}
                    <div id="fieldInfoStepsContainer" class="space-y-3">
                        <h4 class="font-bold text-white text-xs flex items-center gap-2 uppercase tracking-wider text-indigo-400">
                            <i class="fas fa-layer-group"></i> আপনার প্রজেক্টে যেভাবে কনফিগার করবেন:
                        </h4>
                        <div id="fieldInfoSteps" class="space-y-2">
                            <!-- Injected -->
                        </div>
                    </div>

                    {{-- Code Snippet Box --}}
                    <div id="fieldInfoCodeWrapper" class="space-y-2">
                        <div class="flex justify-between items-center text-[11px] text-slate-400">
                            <span id="fieldInfoFilePath" class="font-mono text-indigo-300">File: routes/api.php</span>
                            <button type="button" onclick="copyFieldInfoCode()" id="fieldInfoCopyBtn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-sans px-3 py-1 rounded-lg text-xs flex items-center gap-1 transition cursor-pointer font-bold">
                                <i class="fas fa-copy"></i> Copy Code
                            </button>
                        </div>
                        <pre class="p-4 bg-slate-950 rounded-xl border border-slate-800 text-emerald-400 font-mono text-[11px] leading-relaxed overflow-x-auto select-all whitespace-pre" id="fieldInfoCodeBox"></pre>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-slate-800/80 border-t border-slate-700/80 flex flex-wrap justify-between items-center gap-3">
                    <button type="button" onclick="openCodeGeneratorModal(); closeFieldInfoModal();" class="text-xs font-bold text-indigo-400 hover:text-indigo-300 flex items-center gap-1.5 cursor-pointer">
                        <i class="fas fa-code"></i> Open Full Code Generator (All Frameworks)
                    </button>
                    <button type="button" onclick="closeFieldInfoModal()" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition shadow-xs cursor-pointer">
                        Got it (ঠিক আছে)
                    </button>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_category'))
        {{-- Category Mapping (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-base">
                        <i class="fas fa-tags"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Category & Topic Mapping
                        </h2>
                        <p class="text-xs text-gray-500">Map editorial topics to external website categories</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-amber-100 text-amber-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Category Mapping</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
                    <p class="text-xs text-gray-500">
                        Assign corresponding website taxonomy categories to detected editorial topics.
                    </p>
                    <button type="button" id="refresh-cat-btn" onclick="fetchWPCategories(true)" class="text-xs bg-indigo-50 text-indigo-700 border border-indigo-200 px-3 py-1.5 rounded-lg hover:bg-indigo-100 font-bold flex items-center gap-1 transition cursor-pointer">
                        🔄 Sync Categories
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                    @php
                        $aiCategories = [
                            'Politics', 'International', 'Sports', 'Cricket', 'Football', 
                            'Entertainment', 'Technology', 'Economy', 'Business', 
                            'Bangladesh', 'National', 'Crime', 'Education', 'Health', 
                            'Lifestyle', 'Religion', 'Travel', 'Jobs', 'Opinion', 
                            'Feature', 'Others', 'Science', 'Environment', 'Weather', 
                            'Agriculture', 'Startup', 'Finance', 'Stock Market', 'Banking', 
                            'Law & Justice', 'Defense', 'Cyber Security', 'AI & Robotics', 
                            'Gadgets', 'Mobile', 'Automobile', 'Real Estate', 'Energy', 
                            'Tourism', 'Food & Recipe', 'Fashion', 'Art & Culture', 
                            'History', 'Women', 'Youth', 'Editorial', 'Breaking News', 
                            'Exclusive', 'Investigation', 'Human Rights', 'Social Issues', 
                            'Public Health', 'Mental Health', 'Child Care', 'Parenting', 
                            'Senior Citizens', 'Immigration', 'Expat Life', 'Remittance', 
                            'Development', 'Infrastructure', 'Rural Life', 'Urban Life', 
                            'Local News', 'City News', 'Media & Press', 'Telecom', 
                            'Internet', 'E-Commerce', 'Digital Lifestyle', 'Gaming', 
                            'E-Sports', 'Movies', 'Music', 'TV & OTT', 'Books & Literature' 
                        ];
                        $savedMapping = $settings->category_mapping ?? [];
                    @endphp

                    @foreach($aiCategories as $cat)
                        <div class="flex items-center gap-3 p-2 hover:bg-white rounded transition border border-transparent hover:border-gray-200">
                            <span class="w-1/3 text-xs font-bold text-gray-700">{{ $cat }}</span>
                            <div class="w-2/3 relative">
                                <select name="category_mapping[{{ $cat }}]" class="wp-cat-selector w-full border-gray-300 rounded-lg text-xs focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Select Category</option>
                                </select>
                                <input type="hidden" class="saved-val" value="{{ $savedMapping[$cat] ?? '' }}">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_settings_social'))
        {{-- Facebook Pages & 1-Click OAuth Integration --}}
        @include('settings.partials.facebook-pages-section')

        {{-- Telegram Notifications (Collapsible) --}}
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center text-base">
                        <i class="fab fa-telegram-plane"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Telegram Alert Channel
                        </h2>
                        <p class="text-xs text-gray-500">Channel ID and editorial notification dispatch</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-sky-100 text-sky-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Telegram</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm">
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Channel ID</label>
                    <input type="text" name="telegram_channel_id" value="{{ old('telegram_channel_id', $settings->telegram_channel_id ?? '') }}" placeholder="-100xxxxxxxxxx" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition text-xs">
                    <p class="text-[11px] text-gray-500 mt-1">Add your configured Telegram bot as an administrator to your channel and enter the Channel ID.</p>
                </div>
            </div>
        </div>
        @endif

        {{-- 🚀 Automation & Auto-Drip Post Settings --}}
        @if(auth()->user()->role === 'super_admin' || auth()->user()->hasPermission('can_auto_post'))
        <div class="settings-accordion-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden transition-all duration-200">
            <div class="p-4 sm:p-5 flex justify-between items-center cursor-pointer select-none bg-white hover:bg-gray-50 transition" onclick="toggleSettingsAccordion(this)">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-base">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            Automation & Auto-Drip Post Settings
                        </h2>
                        <p class="text-xs text-gray-500">Auto-publish queued articles to WordPress/Laravel at regular intervals</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs bg-emerald-100 text-emerald-800 font-bold px-2.5 py-0.5 rounded-full hidden sm:inline">Auto Drip</span>
                    <i class="fas fa-chevron-down text-gray-400 text-sm accordion-arrow transition-transform duration-300"></i>
                </div>
            </div>
            
            <div class="settings-accordion-body hidden p-6 border-t border-gray-100 bg-gray-50/50 text-sm space-y-4">
                <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200">
                    <div>
                        <h4 class="font-bold text-gray-800 text-sm">Enable Auto-Posting / Auto-Drip Queue</h4>
                        <p class="text-xs text-gray-500 mt-0.5">When enabled, queued news will automatically post to your website and social channels on schedule</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_auto_posting" value="1" {{ !empty($settings->is_auto_posting) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white p-4 rounded-xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Post Interval (মিনিট পর পর পোস্ট হবে)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="auto_post_interval" min="1" max="1440" value="{{ old('auto_post_interval', $settings->auto_post_interval ?? 10) }}" class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 text-sm font-semibold">
                            <span class="text-xs font-bold text-gray-500 shrink-0">Minutes</span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Default: 10 minutes. Minimum: 1 minute.</p>
                    </div>

                    <div class="bg-white p-4 rounded-xl border border-gray-200">
                        <label class="block text-xs font-bold text-gray-700 mb-1">Last Auto-Posted At</label>
                        <p class="text-sm font-semibold text-gray-800 mt-2">
                            {{ $settings->last_auto_post_at ? \Carbon\Carbon::parse($settings->last_auto_post_at)->diffForHumans() . ' (' . \Carbon\Carbon::parse($settings->last_auto_post_at)->format('d M Y, h:i A') . ')' : 'No post executed yet' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Sticky or Bottom Save Bar -->
        <div class="flex justify-end pt-4 sticky bottom-4 z-20">
            <button type="submit" id="btnSaveSettings" form="mainSettingsForm" class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-8 py-3 rounded-xl font-bold text-base hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center gap-2 cursor-pointer shadow-lg">
                <i class="fas fa-save"></i> <span>Save Changes</span>
            </button>
        </div>
    </form>
</div>

<script>
    // ==========================================================
    // Save Button explicit submission safety
    // ==========================================================
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('mainSettingsForm');
        const btn = document.getElementById('btnSaveSettings');
        if (form && btn) {
            btn.addEventListener('click', function(e) {
                // Ensure form submits smoothly
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Saving Changes...</span>';
                form.submit();
            });
        }
    });

    // ==========================================================
    // Accordion Expand / Collapse Handlers
    // ==========================================================
    function toggleSettingsAccordion(headerEl) {
        const card = headerEl.closest('.settings-accordion-card');
        if (!card) return;
        const body = card.querySelector('.settings-accordion-body');
        const arrow = headerEl.querySelector('.accordion-arrow');
        
        if (body.classList.contains('hidden')) {
            body.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-180');
        } else {
            body.classList.add('hidden');
            if (arrow) arrow.classList.remove('rotate-180');
        }
    }

    function expandAllSettings() {
        document.querySelectorAll('.settings-accordion-card').forEach(card => {
            const body = card.querySelector('.settings-accordion-body');
            const arrow = card.querySelector('.accordion-arrow');
            if (body) body.classList.remove('hidden');
            if (arrow) arrow.classList.add('rotate-180');
        });
    }

    function collapseAllSettings() {
        document.querySelectorAll('.settings-accordion-card').forEach(card => {
            const body = card.querySelector('.settings-accordion-body');
            const arrow = card.querySelector('.accordion-arrow');
            if (body) body.classList.add('hidden');
            if (arrow) arrow.classList.remove('rotate-180');
        });
    }

    // ==========================================================
    // 🎙️ AI Voice Narration & TTS Test Synthesis Handler
    // ==========================================================
    function testTtsVoiceSynthesis() {
        const btn = document.getElementById('tts_test_btn');
        const statusMsg = document.getElementById('tts_test_status_msg');
        const playerContainer = document.getElementById('tts_test_player_container');
        const audioPlayer = document.getElementById('tts_test_audio_player');
        
        const provider = document.getElementById('tts_provider')?.value || 'edgetts';
        const gender = document.getElementById('tts_selected_gender')?.value || 'male';
        const speed = document.getElementById('tts_speed')?.value || 1.00;
        const customVoice = (gender === 'female') ? (document.getElementById('tts_voice_female')?.value || '') : (document.getElementById('tts_voice_male')?.value || '');
        const sampleText = document.getElementById('tts_test_sample_text')?.value || 'স্বাগতম! সাব-এডিটর টোয়েন্টিফোর এআই ভয়েস ন্যারেশন টেস্ট।';

        let apiKey = '';
        if (provider === 'openai') apiKey = document.getElementById('tts_openai_key')?.value || '';
        else if (provider === 'elevenlabs') apiKey = document.getElementById('tts_elevenlabs_key')?.value || '';
        else if (provider === 'google') apiKey = document.getElementById('tts_google_key')?.value || '';

        btn.disabled = true;
        btn.classList.add('opacity-75', 'cursor-not-allowed');
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>ভয়েস তৈরি হচ্ছে...</span>';
        statusMsg.className = 'text-xs font-semibold text-amber-300 animate-pulse';
        statusMsg.innerText = 'অডিও সিন্থেসিস হচ্ছে... অনুগ্রহ করে কয়েক সেকেন্ড অপেক্ষা করুন।';

        fetch('{{ route("settings.tts.test-synthesis") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                provider: provider,
                gender: gender,
                speed: speed,
                voice: customVoice,
                sample: sampleText,
                api_key: apiKey
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = '<i class="fas fa-volume-high"></i> <span>🎙️ টেস্ট ভয়েস তৈরি ও প্লে করুন</span>';

            if (data.success && data.audio_url) {
                statusMsg.className = 'text-xs font-bold text-emerald-400';
                statusMsg.innerText = data.message || '✅ ভয়েস টেস্ট সফল!';
                
                audioPlayer.src = data.audio_url;
                playerContainer.classList.remove('hidden');
                audioPlayer.play().catch(e => console.log('Audio autoplay prevented:', e));
            } else {
                statusMsg.className = 'text-xs font-bold text-rose-400';
                statusMsg.innerText = data.message || '❌ ভয়েস তৈরি করতে সমস্যা হয়েছে।';
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.classList.remove('opacity-75', 'cursor-not-allowed');
            btn.innerHTML = '<i class="fas fa-volume-high"></i> <span>🎙️ টেস্ট ভয়েস তৈরি ও প্লে করুন</span>';
            statusMsg.className = 'text-xs font-bold text-rose-400';
            statusMsg.innerText = '❌ নেটওয়ার্ক বা সার্ভার ত্রুটি: ' + err.message;
        });
    }

    // Toggle Custom API Section
    function toggleCustomApi() {
        const section = document.getElementById('custom-api-section');
        if (section) {
            section.style.display = (section.style.display === 'none') ? 'grid' : 'none';
        }
    }

    // 🔥 Apply JSON from API Guide page (sessionStorage)
    document.addEventListener('DOMContentLoaded', function () {
        const pending = sessionStorage.getItem('pendingApiMapping');
        if (pending) {
            const textarea = document.querySelector('textarea[name="custom_api_mapping"]');
            if (textarea) {
                textarea.value = pending;
                sessionStorage.removeItem('pendingApiMapping');
                const card = textarea.closest('.settings-accordion-card');
                if (card) {
                    const body = card.querySelector('.settings-accordion-body');
                    const arrow = card.querySelector('.accordion-arrow');
                    if (body) body.classList.remove('hidden');
                    if (arrow) arrow.classList.add('rotate-180');
                }
                setTimeout(() => {
                    textarea?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 300);
            }
        }
    });

    // 1. Fetch Categories (with caching)
    function fetchWPCategories(forceRefresh = false) {
        const btn = document.getElementById('refresh-cat-btn');
        const originalText = btn ? btn.innerHTML : '';
        
        if (btn) {
            btn.innerHTML = '⏳ Loading...';
            btn.disabled = true;
        }

        let url = "{{ route('settings.fetch-categories') }}";
        if (forceRefresh) {
            url += "?refresh=1";
        }
        
        fetch(url)
            .then(res => res.json())
            .then(data => {
                if(data.error) {
                    if (forceRefresh) alert(data.error);
                } else {
                    populateDropdowns(data);
                    if(forceRefresh) alert('✅ Category list updated successfully!');
                }
            })
            .catch(err => {
                console.error(err);
                if (forceRefresh) alert('Connection Failed! Please check Settings.');
            })
            .finally(() => {
                if (btn) {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            });
    }

    // 2. Populate Dropdowns
    function populateDropdowns(categories) {
        const selectors = document.querySelectorAll('.wp-cat-selector');
        selectors.forEach(select => {
            const hiddenSibling = select.nextElementSibling;
            const savedVal = hiddenSibling ? hiddenSibling.value : (select.value || '');
            let options = '<option value="">Select Category</option>';
            
            if (Array.isArray(categories)) {
                categories.forEach(cat => {
                    const isSelected = (cat.id == savedVal) ? 'selected' : '';
                    options += `<option value="${cat.id}" ${isSelected}>${cat.name} (ID: ${cat.id})</option>`;
                });
            }
            select.innerHTML = options;
        });
    }

    // 3. Connection Test Functions
    function genericTest(type, data, statusId, btn) {
        const statusMsg = document.getElementById(statusId);
        const originalBtnText = btn.innerHTML;

        btn.innerHTML = "Checking...";
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Connecting...";
        statusMsg.className = "text-xs font-bold mt-2 text-blue-600 p-2.5 rounded bg-blue-50 border border-blue-200 block";

        fetch(`/settings/test/${type}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                statusMsg.innerText = data.message;
                statusMsg.className = "text-xs font-bold mt-2 text-green-700 bg-green-50 p-3 rounded-lg border border-green-300 block whitespace-pre-line";
            } else {
                if (data.ai_diagnostics) {
                    statusMsg.innerHTML = renderDiagnosticsHtml(data.ai_diagnostics, data.message);
                    statusMsg.className = "mt-3 p-4 rounded-2xl bg-rose-50/95 text-rose-900 border border-rose-300 shadow-sm block text-left";
                } else {
                    statusMsg.innerText = data.message;
                    statusMsg.className = "text-xs font-bold mt-2 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
                }
            }
        })
        .catch(err => {
            statusMsg.innerText = "❌ Network error: " + err.message;
            statusMsg.className = "text-xs font-bold mt-2 text-red-700 bg-red-50 p-2.5 rounded border border-red-300 block";
        })
        .finally(() => {
            btn.innerHTML = originalBtnText;
            btn.disabled = false;
        });
    }

    // Button Click Events
    function testWordPress() {
        genericTest('wordpress', {
            wp_url: document.getElementById('wp_url').value,
            wp_username: document.getElementById('wp_username').value,
            wp_app_password: document.getElementById('wp_app_password').value
        }, 'wp_status_msg', document.activeElement);
    }

    function testFacebook() {
        genericTest('facebook', {
            fb_page_id: document.getElementById('fb_page_id').value,
            fb_access_token: document.getElementById('fb_access_token').value
        }, 'fb_status_msg', document.activeElement);
    }

    function testTelegram() {
        genericTest('telegram', {
            telegram_bot_token: document.getElementById('telegram_bot_token').value,
            telegram_channel_id: document.getElementById('telegram_channel_id').value
        }, 'tg_status_msg', document.activeElement);
    }

    function testPhotoRoom() {
        const keyInput = document.getElementById('photoroom_api_key');
        const statusMsg = document.getElementById('photoroom_status_msg');
        const btn = event.currentTarget || document.activeElement;
        const originalText = btn.innerHTML;

        if (!keyInput.value.trim()) {
            statusMsg.innerText = "❌ Please enter a PhotoRoom API Key.";
            statusMsg.className = "text-xs font-bold mt-2 text-red-600";
            return;
        }

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Testing PhotoRoom server handshake...";
        statusMsg.className = "text-xs font-bold mt-2 text-blue-600";

        fetch(`/settings/test/photoroom`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ photoroom_api_key: keyInput.value.trim() })
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            statusMsg.className = data.success ? "text-xs font-bold mt-2 text-green-600" : "text-xs font-bold mt-2 text-red-600";
        })
        .catch(err => {
            statusMsg.innerText = "❌ Network error: " + err.message;
            statusMsg.className = "text-xs font-bold mt-2 text-red-600";
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function testDecodoProxy() {
        const tokenInput = document.getElementById('smartproxy_api_token');
        const hostInput = document.getElementById('proxy_host');
        const portInput = document.getElementById('proxy_port');
        const userInput = document.getElementById('proxy_username');
        const passInput = document.getElementById('proxy_password');
        const statusMsg = document.getElementById('decodo_proxy_status_msg');
        const btn = event.currentTarget || document.activeElement;
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing Proxy...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Testing Decodo Universal API & Proxy handshake...";
        statusMsg.className = "text-xs font-bold mb-4 text-blue-600 bg-blue-50 p-3 rounded-lg border border-blue-200 block";

        const payload = {
            smartproxy_api_token: tokenInput ? tokenInput.value.trim() : '',
            proxy_host: hostInput ? hostInput.value.trim() : '',
            proxy_port: portInput ? portInput.value.trim() : '',
            proxy_username: userInput ? userInput.value.trim() : '',
            proxy_password: passInput ? passInput.value.trim() : ''
        };

        fetch(`/settings/test/decodo-proxy`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            if (data.success) {
                statusMsg.className = "text-xs font-bold mb-4 text-green-700 bg-green-50 p-3 rounded-lg border border-green-300 block whitespace-pre-line";
            } else {
                statusMsg.className = "text-xs font-bold mb-4 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
            }
        })
        .catch(err => {
            statusMsg.innerText = "❌ Network error: " + err.message;
            statusMsg.className = "text-xs font-bold mb-4 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    function toggleScrapingApiFields() {
        const provider = document.getElementById('scraping_api_provider')?.value || 'scrape_do';
        const scrapeDoField = document.getElementById('scrape_do_field');
        const decodoField = document.getElementById('decodo_field');
        
        if (provider === 'scrape_do') {
            if (scrapeDoField) scrapeDoField.classList.remove('opacity-60');
            if (decodoField) decodoField.classList.add('opacity-60');
        } else {
            if (scrapeDoField) scrapeDoField.classList.add('opacity-60');
            if (decodoField) decodoField.classList.remove('opacity-60');
        }
    }

    function testActiveScrapingApi() {
        const provider = document.getElementById('scraping_api_provider')?.value || 'scrape_do';
        if (provider === 'scrape_do') {
            testScrapeDo();
        } else {
            testDecodoProxy();
        }
    }

    function testScrapeDo() {
        const tokenInput = document.getElementById('scrape_do_token');
        const statusMsg = document.getElementById('decodo_proxy_status_msg');
        const btn = event.currentTarget || document.activeElement;
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing Scrape.do...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Testing Scrape.do Web Scraping API (Residential BD IP & Cloudflare bypass)...";
        statusMsg.className = "text-xs font-bold mb-4 text-indigo-600 bg-indigo-50 p-3 rounded-lg border border-indigo-200 block";

        const payload = {
            scrape_do_token: tokenInput ? tokenInput.value.trim() : ''
        };

        fetch(`/settings/test/scrape-do`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            if (data.success) {
                statusMsg.className = "text-xs font-bold mb-4 text-green-700 bg-green-50 p-3 rounded-lg border border-green-300 block whitespace-pre-line";
            } else {
                statusMsg.className = "text-xs font-bold mb-4 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
            }
        })
        .catch(err => {
            statusMsg.innerText = "❌ Network error: " + err.message;
            statusMsg.className = "text-xs font-bold mb-4 text-red-700 bg-red-50 p-3 rounded-lg border border-red-300 block whitespace-pre-line";
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    const defaultAiPrompts = {
        bn: @json(\App\Services\AIWriterService::getDefaultPrompt('bn')),
        en: @json(\App\Services\AIWriterService::getDefaultPrompt('en'))
    };

    function insertDefaultPrompt(lang = 'bn') {
        const promptArea = document.getElementById('custom_rewrite_prompt');
        if (!promptArea) return;
        if (promptArea.value.trim() !== '' && !confirm('Are you sure you want to replace the current prompt with the default prompt?')) {
            return;
        }
        promptArea.value = defaultAiPrompts[lang] || defaultAiPrompts['bn'];
        promptArea.focus();
    }

    function resetDefaultPrompt() {
        const promptArea = document.getElementById('custom_rewrite_prompt');
        if (!promptArea) return;
        if (promptArea.value.trim() !== '' && !confirm('Are you sure you want to reset to default? This will clear the custom prompt so the built-in system prompt will be used.')) {
            return;
        }
        promptArea.value = '';
    }

    const trainingTemplates = {
        newsroom: `1. সর্বদা শ্রদ্ধাশীল ও পেশাদার সিনিয়র সহ-সম্পাদকের ভাষায় মার্জিত বাংলায় কথা বলবে।
2. কোনো সংবাদ রিরাইট বা বিশ্লেষণের সময় মূল তথ্যের কোনো বিকৃতি ঘটাবে না।
3. চটকদার বা বিভ্রান্তিকর ক্লিকবেইট পরিহার করে আকর্ষণীয় ও এসইও-বান্ধব শিরোনাম সাজাবে।
4. ইউজারের যেকোনো প্রশ্নের উত্তর স্পষ্ট ও পরিচ্ছন্ন পয়েন্ট আকারে উপস্থাপন করবে।`,
        
        youtube: `1. ইউটিউব ভিডিও এসইও-এর ক্ষেত্রে উচ্চ সার্চ ভলিউম (High Search Intent) ট্যাগ ও কিওয়ার্ড প্রাধান্য দেবে।
2. টাইটেল ৬০-৭০ অক্ষরের মধ্যে রাখবে যাতে মোবাইল ও ডেস্কটপে পুরোটা দৃশ্যমান থাকে।
3. ট্যাগের ক্ষেত্রে ৫০০ অক্ষরের সীমা বজায় রেখে ৪-টিয়ার সার্চ কুয়েরি সাজাবে।
4. অডিয়েন্স এনগেজমেন্ট বাড়ানোর জন্য আকর্ষণীয় থাম্বনেইল পাঞ্চ লাইন ও কল-টু-অ্যাকশন পিন কমেন্ট তৈরি করবে।`,

        troubleshooting: `1. WordPress কানেকশন ফেইল হলে ইউজারকে WP Admin > Users > Profile থেকে Application Password ব্যবহার করতে বলবে এবং URL-এর শেষে স্ল্যাশ (/) না দেওয়ার পরামর্শ দেবে।
2. AI রিরাইট ব্যর্থ হলে Settings পেজে API Key এবং ব্যালেন্স চেক করতে বলবে।
3. Google / YouTube 403 Error আসলে Google Cloud Console-এ "Test users" তালিকায় জিমেইল যোগ করতে বলবে।
4. যেকোনো টেকনিক্যাল সমস্যায় ইউজারের বিভ্রান্তি না বাড়িয়ে সরাসরি Settings পেজে এসে "Test Connection" করার গাইডলাইন দেবে।`,

        few_shot: `User: ওয়ার্ডপ্রেসের সাথে কানেকশন পাচ্ছে না, কী করব?
Assistant: ১. আপনার ওয়ার্ডপ্রেস অ্যাডমিন প্যানেলে যান: Users > Profile।
২. নিচে স্ক্রোল করে "Application Passwords"-এ একটি নতুন পাসওয়ার্ড তৈরি করুন (লগইন পাসওয়ার্ড ব্যবহার করবেন না)।
৩. আমাদের Settings পেজে এসে সাইট URL (যেমন: https://yoursite.com) ও Application Password দিয়ে "Test Connection" বাটনে চাপুন।

User: ইউটিউব ভিডিওর জন্য ক্লিক-থ্রু টাইটেল কীভাবে বানাব?
Assistant: ১. ভিডিওর মূল চমক বা কিউরিওসিটি হুক প্রথমাংশে রাখুন।
২. ৬০-৭০ অক্ষরের ভেতরে টাইটেল সীমাবদ্ধ রাখুন যাতে মোবাইল ব্যবহারকারীরা পুরোটা পড়তে পারেন।
৩. আমাদের ইউটিউব স্টুডিওর "Generate Title" অপশনটি ব্যবহার করলে AI স্বয়ংক্রিয়ভাবে ৩টি আকর্ষণীয় টাইটেল ভ্যারিয়েশন তৈরি করে দেবে।`
    };

    function insertTrainingTemplate(type) {
        const targetArea = (type === 'few_shot') 
            ? document.getElementById('ai_copilot_few_shot_examples') 
            : document.getElementById('ai_copilot_custom_knowledge');
            
        if (!targetArea) return;
        
        if (targetArea.value.trim() !== '' && !confirm('Are you sure you want to append/insert this training template?')) {
            return;
        }

        const template = trainingTemplates[type] || '';
        if (targetArea.value.trim() === '') {
            targetArea.value = template;
        } else {
            targetArea.value = targetArea.value.trim() + "\n\n" + template;
        }
        targetArea.focus();
    }

    function testAiProvider(provider) {
        const keyInput = document.getElementById(provider + '_api_key');
        const modelInput = document.getElementById(provider + '_model');
        const statusMsg = document.getElementById(provider + '_status_msg');
        const btn = event.currentTarget || document.activeElement;
        const originalText = btn.innerHTML;

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        btn.disabled = true;
        statusMsg.innerHTML = "⏳ Sending test request to AI server...";
        statusMsg.className = "text-xs font-bold mt-2 text-blue-600 bg-blue-50 p-2.5 rounded border border-blue-200 block";

        fetch(`/settings/test/ai-provider`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                provider: provider,
                api_key: keyInput ? keyInput.value.trim() : '',
                model: modelInput ? modelInput.value.trim() : ''
            })
        })
        .then(res => res.json())
        .then(data => {
            statusMsg.innerText = data.message;
            if (data.success) {
                statusMsg.className = "text-xs font-bold mt-2 text-green-700 bg-green-50 p-2.5 rounded border border-green-300 block whitespace-pre-line";
            } else {
                statusMsg.className = "text-xs font-bold mt-2 text-red-700 bg-red-50 p-2.5 rounded border border-red-300 block whitespace-pre-line";
            }
        })
        .catch(err => {
            statusMsg.innerText = "❌ Network error: " + err.message;
            statusMsg.className = "text-xs font-bold mt-2 text-red-700 bg-red-50 p-2.5 rounded border border-red-300 block whitespace-pre-line";
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ==========================================================
    // 1. Custom API Visual Field Mapper Synchronization
    // ==========================================================
    function toggleCustomApiVisual() {
        const sec = document.getElementById('custom-api-visual-section');
        const chev = document.getElementById('mapper_chevron');
        if (sec.classList.contains('hidden')) {
            sec.classList.remove('hidden');
            if (chev) chev.classList.add('rotate-180');
        } else {
            sec.classList.add('hidden');
            if (chev) chev.classList.remove('rotate-180');
        }
    }

    function toggleRawJson() {
        const t = document.getElementById('custom_api_mapping');
        if (t.classList.contains('hidden')) {
            t.classList.remove('hidden');
        } else {
            t.classList.add('hidden');
        }
    }

    function syncVisualToMappingJson() {
        const customApiMappingEl = document.getElementById('custom_api_mapping');
        const authTypeEl = document.getElementById('v_auth_type');
        if (!customApiMappingEl || !authTypeEl) return;

        const authType = authTypeEl.value || 'bearer';
        const authHeaderName = document.getElementById('v_auth_header_name')?.value?.trim() || '';
        const imageFormat = document.getElementById('v_image_format')?.value || 'url';
        const categoryType = document.getElementById('v_category_type')?.value || 'slug';

        const authHeaderWrapper = document.getElementById('v_auth_header_wrapper');
        if (authHeaderWrapper) {
            authHeaderWrapper.style.display = (authType === 'custom_header') ? 'block' : 'none';
        }

        const fields = {};
        const titleField = document.getElementById('v_field_title')?.value?.trim();
        if (titleField) fields.title = titleField;

        const contentField = document.getElementById('v_field_content')?.value?.trim();
        if (contentField) fields.content = contentField;

        const imageField = document.getElementById('v_field_image')?.value?.trim();
        if (imageField) fields.image = imageField;

        const categoryField = document.getElementById('v_field_category')?.value?.trim();
        if (categoryField) fields.category = categoryField;

        const tagsField = document.getElementById('v_field_tags')?.value?.trim();
        if (tagsField) fields.tags = tagsField;

        const slugField = document.getElementById('v_field_slug')?.value?.trim();
        if (slugField) fields.slug = slugField;

        const responseIdKey = document.getElementById('v_field_response_id_key')?.value?.trim();
        const responseUrlKey = document.getElementById('v_field_response_url_key')?.value?.trim();

        // Extra static key-values
        const extraData = {};
        document.querySelectorAll('.extra-field-row').forEach(row => {
            const k = row.querySelector('.extra-key')?.value?.trim();
            const v = row.querySelector('.extra-val')?.value?.trim();
            if (k) extraData[k] = v;
        });

        const mappingObj = {
            auth_type: authType,
            image_format: imageFormat,
            category_type: categoryType
        };

        if (authType === 'custom_header' && authHeaderName) {
            mappingObj.auth_header_name = authHeaderName;
        }

        if (Object.keys(fields).length > 0) {
            mappingObj.fields = fields;
        }

        if (Object.keys(extraData).length > 0) {
            mappingObj.extra_data = extraData;
        }

        if (responseIdKey) mappingObj.response_id_key = responseIdKey;
        if (responseUrlKey) mappingObj.response_url_key = responseUrlKey;

        customApiMappingEl.value = JSON.stringify(mappingObj, null, 2);
    }

    function syncMappingJsonToVisual() {
        const customApiMappingEl = document.getElementById('custom_api_mapping');
        if (!customApiMappingEl) return;
        const rawJson = customApiMappingEl.value.trim();
        if (!rawJson) return;

        try {
            const obj = JSON.parse(rawJson);
            if (obj.auth_type && document.getElementById('v_auth_type')) document.getElementById('v_auth_type').value = obj.auth_type;
            if (obj.auth_header_name && document.getElementById('v_auth_header_name')) document.getElementById('v_auth_header_name').value = obj.auth_header_name;
            if (obj.image_format && document.getElementById('v_image_format')) document.getElementById('v_image_format').value = obj.image_format;
            if (obj.category_type && document.getElementById('v_category_type')) document.getElementById('v_category_type').value = obj.category_type;

            const wrapper = document.getElementById('v_auth_header_wrapper');
            if (wrapper && obj.auth_type === 'custom_header') {
                wrapper.style.display = 'block';
            }

            if (obj.fields) {
                if (obj.fields.title && document.getElementById('v_field_title')) document.getElementById('v_field_title').value = obj.fields.title;
                if (obj.fields.content && document.getElementById('v_field_content')) document.getElementById('v_field_content').value = obj.fields.content;
                if (obj.fields.image && document.getElementById('v_field_image')) document.getElementById('v_field_image').value = obj.fields.image;
                if (obj.fields.category && document.getElementById('v_field_category')) document.getElementById('v_field_category').value = obj.fields.category;
                if (obj.fields.tags && document.getElementById('v_field_tags')) document.getElementById('v_field_tags').value = obj.fields.tags;
                if (obj.fields.slug && document.getElementById('v_field_slug')) document.getElementById('v_field_slug').value = obj.fields.slug;
            }

            if (obj.response_id_key && document.getElementById('v_field_response_id_key')) document.getElementById('v_field_response_id_key').value = obj.response_id_key;
            if (obj.response_url_key && document.getElementById('v_field_response_url_key')) document.getElementById('v_field_response_url_key').value = obj.response_url_key;

            if (obj.extra_data) {
                const container = document.getElementById('extra_fields_container');
                if (container) {
                    container.innerHTML = '';
                    for (const [k, v] of Object.entries(obj.extra_data)) {
                        addExtraFieldRow(k, v);
                    }
                }
            }
        } catch (e) {
            // raw json might be custom or invalid, ignore
        }
    }

    function addExtraFieldRow(key = '', val = '') {
        const container = document.getElementById('extra_fields_container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 extra-field-row';
        div.innerHTML = `
            <input type="text" placeholder="Key (e.g. author_id)" value="${key}" oninput="syncVisualToMappingJson()" class="extra-key w-1/2 border-slate-300 rounded p-1.5 font-mono text-xs">
            <input type="text" placeholder="Value (e.g. 1)" value="${val}" oninput="syncVisualToMappingJson()" class="extra-val w-1/2 border-slate-300 rounded p-1.5 font-mono text-xs">
            <button type="button" onclick="this.parentElement.remove(); syncVisualToMappingJson();" class="text-red-500 hover:text-red-700 px-2 py-1 text-sm font-bold">&times;</button>
        `;
        container.appendChild(div);
    }

    function generateRandomToken() {
        const randStr = 'sec_' + Math.random().toString(36).substring(2, 10) + '_' + Math.random().toString(36).substring(2, 10);
        document.getElementById('laravel_api_token').value = randStr;
    }

    // ==========================================================
    // 2. Custom API Live Test & AI Diagnostics Renderer
    // ==========================================================
    function renderDiagnosticsHtml(diag, fallbackMsg = '') {
        if (!diag) {
            return `<div class="font-bold whitespace-pre-line">${fallbackMsg}</div>`;
        }

        const badge = diag.badge || 'Connection Diagnostic';
        const problem = diag.problem || fallbackMsg;
        const reason = diag.reason || '';
        const fixInstructions = diag.fix_instructions || '';
        const fixCode = diag.fix_code || '';
        const codeId = 'diag_code_' + Math.random().toString(36).substring(2, 9);

        let html = `
        <div class="space-y-3 font-sans">
            <div class="flex items-center justify-between gap-2 border-b border-rose-200 pb-2.5">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-rose-600 text-white text-xs shadow-sm">
                        <i class="fas fa-robot"></i>
                    </span>
                    <div>
                        <span class="font-bold text-rose-950 text-sm block leading-none">AI কানেকশন ডায়াগনস্টিকস</span>
                        <span class="text-[10px] text-rose-700">স্বয়ংক্রিয় সমস্যা বিশ্লেষণ ও সমাধান</span>
                    </div>
                </div>
                <span class="text-[11px] font-black uppercase px-2.5 py-0.5 rounded-md bg-rose-100 text-rose-800 border border-rose-300 shadow-xs">${badge}</span>
            </div>

            <div class="text-xs space-y-2.5 pt-1">
                <div>
                    <div class="font-bold text-rose-950 flex items-center gap-1.5 mb-0.5">
                        <i class="fas fa-circle-exclamation text-rose-600"></i> সমস্যা:
                    </div>
                    <div class="pl-4 text-slate-800 font-semibold leading-relaxed">${problem}</div>
                </div>

                ${reason ? `
                <div>
                    <div class="font-bold text-rose-950 flex items-center gap-1.5 mb-0.5">
                        <i class="fas fa-magnifying-glass text-indigo-600"></i> কারণ:
                    </div>
                    <div class="pl-4 text-slate-700 leading-relaxed">${reason}</div>
                </div>
                ` : ''}

                ${fixInstructions ? `
                <div>
                    <div class="font-bold text-rose-950 flex items-center gap-1.5 mb-0.5">
                        <i class="fas fa-wrench text-emerald-600"></i> সমাধানের উপায়:
                    </div>
                    <div class="pl-4 text-slate-700 leading-relaxed">${fixInstructions}</div>
                </div>
                ` : ''}
            </div>

            ${fixCode ? `
            <div class="mt-3 pt-2.5 border-t border-rose-200">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[11px] font-bold text-slate-800 flex items-center gap-1">
                        <i class="fas fa-code text-indigo-600"></i> ড্রপ-ইন কোড ফিক্স (কপি করে আপনার প্রজেক্টে বসান):
                    </span>
                    <button type="button" onclick="copyDiagSnippet('${codeId}', this)" class="text-[11px] bg-slate-800 hover:bg-slate-900 text-white font-bold px-3 py-1 rounded-md transition flex items-center gap-1 cursor-pointer shadow-sm">
                        <i class="fas fa-copy"></i> Copy Code
                    </button>
                </div>
                <pre id="${codeId}" class="p-3.5 bg-slate-950 text-emerald-400 font-mono text-[11px] rounded-xl border border-slate-800 overflow-x-auto whitespace-pre leading-relaxed">${fixCode.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</pre>
            </div>
            ` : ''}
        </div>
        `;

        return html;
    }

    function copyDiagSnippet(id, btn) {
        const el = document.getElementById(id);
        if (!el) return;
        navigator.clipboard.writeText(el.innerText).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check text-emerald-400"></i> Copied!';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        });
    }

    function testCustomApiConnection() {
        const btn = document.getElementById('btn_test_custom_api');
        const box = document.getElementById('custom_api_status_box');
        const originalText = btn.innerHTML;

        const siteUrl = document.getElementById('laravel_site_url').value.trim();
        const customApiUrl = document.getElementById('custom_api_url').value.trim();
        const token = document.getElementById('laravel_api_token').value.trim();
        const mapping = document.getElementById('custom_api_mapping').value.trim();

        if (!siteUrl && !customApiUrl) {
            box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 block';
            box.innerText = 'Please provide Website Base URL or Custom News Post Endpoint URL.';
            return;
        }

        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';
        btn.disabled = true;

        box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-800 border border-blue-200 block';
        box.innerText = '⏳ Sending handshake test request...';

        fetch("{{ route('settings.test-custom-api') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                laravel_site_url: siteUrl,
                custom_api_url: customApiUrl,
                laravel_api_token: token,
                custom_api_mapping: mapping
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                box.className = 'mb-4 p-4 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-800 border border-emerald-300 block whitespace-pre-line shadow-sm';
                box.innerHTML = `<div class="flex items-start gap-2.5"><i class="fas fa-circle-check text-emerald-600 text-base mt-0.5"></i><div>${data.message.replace(/\n/g, '<br>')}</div></div>`;
            } else {
                if (data.ai_diagnostics) {
                    box.className = 'mb-5 p-5 rounded-2xl bg-rose-50/95 text-rose-900 border border-rose-300 shadow-md block text-left';
                    box.innerHTML = renderDiagnosticsHtml(data.ai_diagnostics, data.message);
                } else {
                    box.className = 'mb-4 p-4 rounded-xl text-xs font-bold bg-red-50 text-red-800 border border-red-300 block whitespace-pre-line shadow-sm';
                    box.innerText = data.message;
                }
            }
        })
        .catch(err => {
            box.className = 'mb-4 p-3.5 rounded-lg text-xs font-bold bg-red-50 text-red-800 border border-red-300 block';
            box.innerText = '❌ Network error: ' + err.message;
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }

    // ==========================================================
    // 3. Assistant Help Modal Controls
    // ==========================================================
    function openAssistantHelpModal() {
        document.getElementById('assistantHelpModal').classList.remove('hidden');
    }

    function closeAssistantHelpModal() {
        document.getElementById('assistantHelpModal').classList.add('hidden');
    }

    function switchHelpTab(tabKey) {
        document.querySelectorAll('.help-tab-btn').forEach(b => {
            b.className = 'help-tab-btn px-4 py-3 border-b-2 border-transparent text-slate-400 hover:text-slate-200 flex items-center gap-2';
        });
        document.getElementById('htab_' + tabKey).className = 'help-tab-btn px-4 py-3 border-b-2 border-indigo-500 text-indigo-400 flex items-center gap-2';

        document.getElementById('help_content_walkthrough').classList.add('hidden');
        document.getElementById('help_content_faq').classList.add('hidden');
        document.getElementById('help_content_diagnostics').classList.add('hidden');

        document.getElementById('help_content_' + tabKey).classList.remove('hidden');
    }

    // ==========================================================
    // 4. Code Generator Modal & Dynamic Snippets
    // ==========================================================
    function openCodeGeneratorModal() {
        document.getElementById('codeGeneratorModal').classList.remove('hidden');
        switchCodeGenTab('laravel');
    }

    function closeCodeGeneratorModal() {
        document.getElementById('codeGeneratorModal').classList.add('hidden');
    }

    function switchCodeGenTab(langKey) {
        document.querySelectorAll('.cg-tab-btn').forEach(b => {
            b.className = 'cg-tab-btn px-3 py-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white cursor-pointer';
        });
        const activeTab = document.getElementById('cg_tab_' + langKey);
        if (activeTab) activeTab.className = 'cg-tab-btn px-3 py-2 rounded-lg bg-indigo-600 text-white cursor-pointer';

        const token = document.getElementById('laravel_api_token').value.trim() || 'YOUR_SECRET_TOKEN_HERE';
        const codeBox = document.getElementById('cg_code_content');
        const pathBox = document.getElementById('cg_target_file_path');

        if (langKey === 'laravel') {
            pathBox.innerText = 'Target File: routes/api.php (Laravel 10 এ ডিফল্ট থাকে | Laravel 11/12 এ না থাকলে: php artisan install:api)';
            codeBox.innerText = '<\x3Fphp\n\n' +
`use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\NewsPost; // ⚠️ আপনার নিউজ মডেল
use App\Models\Category; // ⚠️ আপনার ক্যাটাগরি মডেল

/*
|--------------------------------------------------------------------------
| ১. NEWS POST RECEIVER (নিউজ রিসিভ ও ডাটাবেসে সংরক্ষণ)
|--------------------------------------------------------------------------
*/
Route::post('/external-news-post', function (Request $request) {
    // ১.১ সিকিউরিটি টোকেন যাচাই (Authorization: Bearer <token>)
    $authHeader = $request->header('Authorization');
    $expectedToken = "Bearer " . env('SUBEDITOR_API_SECRET', '${token}');

    if (!$authHeader || $authHeader !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid API Secret Token'
        ], 401);
    }

    // ১.২ ডেটা ভ্যালিডেশন
    $validated = $request->validate([
        'title'       => 'required|string',
        'content'     => 'required|string',
        'image'       => 'nullable',
        'category_id' => 'nullable',
        'tags'        => 'nullable|string',
        'slug'        => 'nullable|string',
    ]);

    // ১.৩ ফিচার্ড ইমেজ হ্যান্ডলিং (Multipart File বা URL)
    $imagePath = null;
    if ($request->hasFile('image')) {
        $imagePath = $request->file('image')->store('news', 'public');
    } elseif ($request->filled('image') && is_string($request->image)) {
        $imagePath = $request->image;
    }

    // ১.৪ স্লাগ তৈরি
    $slug = !empty($validated['slug']) 
        ? Str::slug($validated['slug']) 
        : Str::slug($validated['title']) . '-' . time();

    // ১.৫ ডাটাবেসে নিউজ তৈরি করুন
    $post = NewsPost::create([
        'title'       => $validated['title'],
        'slug'        => $slug,
        'content'     => $validated['content'],
        'image'       => $imagePath,
        'category_id' => $validated['category_id'] ?? 1,
        'tags'        => $validated['tags'] ?? null,
        'status'      => 'published',
    ]);

    // ১.৬ সফল রেসপন্স ও লাইভ পোস্টের লিঙ্ক রিটার্ন করুন
    return response()->json([
        'success' => true,
        'message' => 'News published successfully',
        'post_id' => $post->id,
        'url'     => url('/news/' . $post->slug)
    ], 200);
});

/*
|--------------------------------------------------------------------------
| ২. CATEGORY FETCHER (Subeditor24 এর জন্য ক্যাটাগরি তালিকা)
|--------------------------------------------------------------------------
*/
Route::get('/get-categories', function (Request $request) {
    $token = $request->bearerToken() ?? $request->query('token');
    $expectedSecret = env('SUBEDITOR_API_SECRET', '${token}');

    if ($token !== $expectedSecret) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // আপনার ডাটাবেস থেকে ক্যাটাগরি তালিকা রিটার্ন করুন
    return response()->json(
        Category::select('id', 'name')->get()
    );
});`;
        } else if (langKey === 'wp') {
            pathBox.innerText = 'Target File: wp-content/themes/your-theme/functions.php';
            codeBox.innerText = `// Subeditor24 Universal WordPress API Integration
add_action('rest_api_init', function () {
    // 1. News Post Receiver Endpoint
    register_rest_route('subeditor/v1', '/post-news', [
        'methods'  => 'POST',
        'callback' => 'handle_subeditor_post',
        'permission_callback' => '__return_true'
    ]);

    // 2. Category Fetcher Endpoint
    register_rest_route('subeditor/v1', '/categories', [
        'methods'  => 'GET',
        'callback' => 'handle_subeditor_categories',
        'permission_callback' => '__return_true'
    ]);
});

function handle_subeditor_post($request) {
    $token = $request->get_header('Authorization');
    if ($token !== 'Bearer ${token}') {
        return new WP_Error('unauthorized', 'Invalid Secret Token', ['status' => 401]);
    }

    $params = $request->get_params();
    $post_id = wp_insert_post([
        'post_title'   => sanitize_text_field($params['title'] ?? ''),
        'post_content' => wp_kses_post($params['content'] ?? ''),
        'post_status'  => 'publish',
        'post_author'  => 1
    ]);

    return rest_ensure_response([
        'success' => true,
        'post_id' => $post_id,
        'url'     => get_permalink($post_id)
    ]);
}

function handle_subeditor_categories($request) {
    $categories = get_categories(['hide_empty' => false]);
    $data = [];
    foreach ($categories as $cat) {
        $data[] = ['id' => $cat->term_id, 'name' => $cat->name];
    }
    return rest_ensure_response($data);
}`;
        } else if (langKey === 'next_app') {
            pathBox.innerText = 'Target File: app/api/external-news-post/route.ts';
            codeBox.innerText = `import { NextRequest, NextResponse } from 'next/server';

// 1. News Receiver Endpoint
export async function POST(req: NextRequest) {
  try {
    const authHeader = req.headers.get('authorization');
    const expectedToken = "Bearer ${token}";

    if (authHeader !== expectedToken) {
      return NextResponse.json({ success: false, message: 'Unauthorized' }, { status: 401 });
    }

    const data = await req.json();
    console.log("Received News Payload:", data);

    // Save article to your Database (Prisma, Drizzle, MongoDB, etc.)
    // const post = await db.article.create({ data: { title: data.title, content: data.content, ... } });

    return NextResponse.json({
      success: true,
      message: 'Article published successfully',
      post_id: 101,
      url: \`/news/article-101\`
    }, { status: 200 });

  } catch (error: any) {
    return NextResponse.json({ success: false, message: error.message }, { status: 500 });
  }
}

// 2. Category Fetcher Endpoint (app/api/get-categories/route.ts)
export async function GET(req: NextRequest) {
  const authHeader = req.headers.get('authorization');
  if (authHeader !== "Bearer ${token}") {
    return NextResponse.json({ error: 'Unauthorized' }, { status: 401 });
  }

  // const categories = await db.category.findMany({ select: { id: true, name: true } });
  return NextResponse.json([
    { id: 1, name: 'জাতীয়' },
    { id: 2, name: 'আন্তর্জাতিক' },
    { id: 3, name: 'খেলাধুলা' }
  ]);
}`;
        } else if (langKey === 'next_pages') {
            pathBox.innerText = 'Target File: pages/api/external-news-post.ts';
            codeBox.innerText = `import type { NextApiRequest, NextApiResponse } from 'next';

export default async function handler(req: NextApiRequest, res: NextApiResponse) {
  const authHeader = req.headers.authorization;
  if (authHeader !== "Bearer ${token}") {
    return res.status(401).json({ success: false, message: 'Unauthorized' });
  }

  if (req.method === 'POST') {
    const { title, content, image, category_id, tags } = req.body;
    // Insert post into database...
    return res.status(200).json({
      success: true,
      post_id: 101,
      url: '/news/101'
    });
  }

  if (req.method === 'GET') {
    // Return categories list
    return res.status(200).json([
      { id: 1, name: 'জাতীয়' },
      { id: 2, name: 'খেলাধুলা' }
    ]);
  }

  return res.status(405).json({ message: 'Method Not Allowed' });
}`;
        } else if (langKey === 'express') {
            pathBox.innerText = 'Target File: routes/newsReceiver.js';
            codeBox.innerText = `const express = require('express');
const router = express.Router();

// 1. News Post Receiver
router.post('/api/external-news-post', (req, res) => {
  const authHeader = req.headers.authorization;
  if (authHeader !== "Bearer ${token}") {
    return res.status(401).json({ success: false, message: 'Unauthorized' });
  }

  const { title, content, image, category, tags, slug } = req.body;
  // TODO: Save to your DB (e.g. Mongoose, Sequelize, Postgres)
  return res.json({
    success: true,
    post_id: 101,
    url: '/news/' + (slug || '101')
  });
});

// 2. Category Fetcher
router.get('/api/get-categories', (req, res) => {
  const authHeader = req.headers.authorization || req.query.token;
  if (authHeader !== "Bearer ${token}" && req.query.token !== "${token}") {
    return res.status(401).json({ error: 'Unauthorized' });
  }

  // Return categories from DB
  return res.json([
    { id: 1, name: 'জাতীয়' },
    { id: 2, name: 'খেলাধুলা' }
  ]);
});

module.exports = router;`;
        } else if (langKey === 'php') {
            pathBox.innerText = 'Target File: public/news-receiver.php';
            codeBox.innerText = '<\x3Fphp\n' +
`header('Content-Type: application/json');

$headers = getallheaders();
$auth = $headers['Authorization'] ?? ($headers['authorization'] ?? '');

if ($auth !== "Bearer ${token}") {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check GET request for categories
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        ['id' => 1, 'name' => 'জাতীয়'],
        ['id' => 2, 'name' => 'আন্তর্জাতিক'],
        ['id' => 3, 'name' => 'খেলাধুলা']
    ]);
    exit;
}

// Handle POST request for news
$title   = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';

if (empty($title)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit;
}

// Database Connection & Insert
// $pdo = new PDO("mysql:host=localhost;dbname=mydb", "user", "pass");
// $stmt = $pdo->prepare("INSERT INTO posts (title, content) VALUES (?, ?)");
// $stmt->execute([$title, $content]);

echo json_encode([
    'success' => true,
    'post_id' => 101,
    'url'     => 'https://mywebsite.com/news/101'
]);`;
        } else if (langKey === 'python') {
            pathBox.innerText = 'Target File: main.py (FastAPI)';
            codeBox.innerText = `from fastapi import FastAPI, Header, Form, HTTPException, Depends
from typing import Optional, List
from pydantic import BaseModel

app = FastAPI()

# 1. News Post Receiver
@app.post("/api/external-news-post")
async def receive_news(
    title: str = Form(...),
    content: str = Form(...),
    category_id: Optional[int] = Form(None),
    authorization: Optional[str] = Header(None)
):
    if authorization != "Bearer ${token}":
        raise HTTPException(status_code=401, detail="Unauthorized")
    
    # Save to Database
    return {"success": True, "post_id": 101, "url": "https://mywebsite.com/news/101"}

# 2. Category Fetcher
@app.get("/api/get-categories")
async def get_categories(authorization: Optional[str] = Header(None)):
    if authorization != "Bearer ${token}":
        raise HTTPException(status_code=401, detail="Unauthorized")
    
    return [
        {"id": 1, "name": "জাতীয়"},
        {"id": 2, "name": "খেলাধুলা"}
    ]
`;
        }
    }

    function copyGeneratedCode() {
        const code = document.getElementById('cg_code_content').innerText;
        navigator.clipboard.writeText(code).then(() => {
            alert('✅ Code copied to clipboard!');
        });
    }

    // Auto load categories & sync visual mapper on load
    document.addEventListener('DOMContentLoaded', function () {
        fetchWPCategories();
        syncMappingJsonToVisual();
        toggleScrapingApiFields();
    });

    // ==========================================================
    // SYSTEM HEALTH & DIAGNOSTICS
    // ==========================================================
    function runDiagnosticsModal() {
        const modal = document.getElementById('systemDiagnosticsModal');
        const loading = document.getElementById('diagnosticsLoading');
        const container = document.getElementById('diagnosticsResultsContainer');
        const timestamp = document.getElementById('diagnosticsTimestamp');

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        if (loading) loading.classList.remove('hidden');
        if (container) {
            container.classList.add('hidden');
            container.innerHTML = '';
        }

        fetch("{{ route('settings.diagnostics') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (loading) loading.classList.add('hidden');
            if (timestamp && data.timestamp) timestamp.innerText = `Last checked: ${data.timestamp}`;

            if (data.success && data.checks) {
                if (container) {
                    const statusIcons = {
                        ok: '<div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-sm"><i class="fa-solid fa-circle-check"></i></div>',
                        warning: '<div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center text-sm"><i class="fa-solid fa-triangle-exclamation"></i></div>',
                        error: '<div class="w-8 h-8 rounded-xl bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 flex items-center justify-center text-sm"><i class="fa-solid fa-circle-xmark"></i></div>',
                        info: '<div class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center text-sm"><i class="fa-solid fa-circle-info"></i></div>'
                    };

                    const badgeColors = {
                        ok: 'bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800',
                        warning: 'bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800',
                        error: 'bg-rose-100 dark:bg-rose-950 text-rose-800 dark:text-rose-300 border border-rose-300 dark:border-rose-800',
                        info: 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700'
                    };

                    container.innerHTML = data.checks.map(item => `
                        <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 shadow-sm flex items-start gap-3">
                            ${statusIcons[item.status] || statusIcons.info}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-0.5">
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">${item.name}</h4>
                                    <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-md ${badgeColors[item.status] || badgeColors.info}">${item.badge}</span>
                                </div>
                                <p class="text-[11px] text-slate-600 dark:text-slate-300 leading-snug">${item.message}</p>
                            </div>
                        </div>
                    `).join('');

                    container.classList.remove('hidden');
                }
            }
        })
        .catch(err => {
            if (loading) loading.classList.add('hidden');
            if (container) {
                container.innerHTML = `<div class="p-4 bg-rose-50 text-rose-700 rounded-xl text-xs font-bold">Server error: ${err.message}</div>`;
                container.classList.remove('hidden');
            }
        });
    }

    // ==========================================================
    // FIELD HELP & INTEGRATION MODAL ENGINE
    // ==========================================================
    const fieldHelpData = {
        laravel_api_token: {
            title: 'API Secret Token (সিক্রেট কী)',
            subtitle: 'Laravel ও Subeditor24 এর নিরাপদ সংযোগ চাবি',
            icon: '<i class="fas fa-key text-amber-400"></i>',
            desc: `এই <strong>API Secret Token</strong>-টি একটি গোপন পাসওয়ার্ড। Subeditor24 যখন আপনার Laravel ওয়েবসাইটে নিউজ পোস্ট পাঠায়, তখন রিকোয়েস্টের হেডারে <code>Authorization: Bearer <Secret_Token></code> হিসেবে এই টোকেনটি পাঠানো হয়। আপনার ওয়েবসাইট এই টোকেন মিলিয়ে দেখে রিকোয়েস্টটি বৈধ কিনা।`,
            steps: [
                `<strong>Step 1:</strong> আপনার Laravel নিউজ প্রজেক্টের <code>.env</code> ফাইলে টোকেনটি সংরক্ষণ করুন:<br><code class="text-indigo-300">SUBEDITOR_API_SECRET=আপনার_টোকেন_এখানে</code>`,
                `<strong>Step 2:</strong> আপনার Laravel প্রজেক্টের <code>routes/api.php</code> ফাইলে এই রুট কোডটি যুক্ত করুন:`,
                `<strong>Step 3:</strong> Settings পেজের <strong>"Test Connection"</strong> বাটনে ক্লিক করে লাইভ সংযোগ পরীক্ষা করুন।`
            ],
            filePath: 'Laravel File: routes/api.php',
            code: `use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Route;
use App\\Models\\NewsPost; // আপনার নিউজ মডেল

Route::post('/external-news-post', function (Request $request) {
    // ১. সিক্রেট টোকেন যাচাই (Security Handshake)
    $expectedToken = "Bearer " . env('SUBEDITOR_API_SECRET', 'YOUR_SECRET_TOKEN_HERE');
    if ($request->header('Authorization') !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized: Invalid API Secret Token'
        ], 401);
    }

    // ২. ডেটা ভ্যালিডেশন
    $validated = $request->validate([
        'title'       => 'required|string',
        'content'     => 'required|string',
        'image'       => 'nullable|string',
        'category'    => 'nullable|string',
        'category_id' => 'nullable',
        'tags'        => 'nullable|string',
        'slug'        => 'nullable|string',
    ]);

    // ৩. আপনার ডাটাবেসে নিউজ সেভ করুন
    $post = NewsPost::create([
        'title'       => $validated['title'],
        'slug'        => $validated['slug'] ?? \\Illuminate\\Support\\Str::slug($validated['title']),
        'content'     => $validated['content'],
        'image'       => $validated['image'] ?? null,
        'category_id' => $validated['category_id'] ?? 1,
        'status'      => 'published',
    ]);

    // ৪. সফল রেসপন্স ও পোস্টের লাইভ লিংক রিটার্ন করুন
    return response()->json([
        'success' => true,
        'message' => 'News published successfully',
        'post_id' => $post->id,
        'url'     => url('/news/' . $post->slug)
    ], 200);
});`
        },
        laravel_site_url: {
            title: 'Website Base URL (ওয়েবসাইটের ডোমেইন)',
            subtitle: 'আপনার নিউজ পোর্টালের মূল লাইভ লিঙ্ক',
            icon: '<i class="fas fa-globe text-indigo-400"></i>',
            desc: `যে Laravel বা কাস্টম ওয়েবসাইটে নিউজগুলো স্বয়ংক্রিয়ভাবে পোস্ট হবে, তার মূল ডোমেইন ইউআরএল এখানে দিন। যেমন: <code>https://mybanglanews.com</code> বা লোকাল ডেভেলপমেন্টে <code>http://127.0.0.1:8000</code>।`,
            steps: [
                `<strong>Step 1:</strong> সম্পূর্ণ প্রোটোকল সহ ডোমেইন দিন (e.g. <code>https://yourdomain.com</code>)। শেষে কোনো স্ল্যাশ (/) দিবেন না।`,
                `<strong>Step 2:</strong> Subeditor24 ডিফল্টভাবে <code>Base_URL/api/external-news-post</code> এ রিকোয়েস্ট পাঠাবে।`
            ],
            filePath: 'Settings Field: Website Base URL',
            code: `// Subeditor24 will send POST request to:
https://yourdomain.com/api/external-news-post`
        },
        laravel_route_prefix: {
            title: 'News Link Prefix (পারমালিংক প্রিফিক্স)',
            subtitle: 'নিউজ ভিজিট লিঙ্কের ফরম্যাট',
            icon: '<i class="fas fa-link text-emerald-400"></i>',
            desc: `আপনার ওয়েবসাইটে প্রতিটি নিউজের ইউআরএল স্ট্রাকচার কেমন তা নির্ধারণ করে। যেমন আপনার নিউজের লিংক যদি <code>https://site.com/news/123</code> হয়, তবে প্রিফিক্স হবে <code>news</code>। যদি <code>https://site.com/post/123</code> হয়, তবে <code>post</code>।`,
            steps: [
                `ডিফল্ট ভ্যালু: <code>news</code>`,
                `আর্টিকেল লিংক তৈরি হবে: <code>https://yourdomain.com/news/{slug_or_id}</code>`
            ],
            filePath: 'URL Format',
            code: `https://yourdomain.com/{prefix}/{news_id_or_slug}`
        },
        post_to_laravel: {
            title: 'Enable Auto-Publish to Website (অটো পাবলিশ)',
            subtitle: 'এপ্রুভালের সাথে সাথে লাইভ পোস্টিং',
            icon: '<i class="fas fa-paper-plane text-cyan-400"></i>',
            desc: `এই অপশনটি চালু রাখলে Subeditor24-এ যখন কোনো এআই রিরাইট করা নিউজ এডিটর এপ্রুভ করবেন, সাথে সাথে তা আপনার ওয়েবসাইটে API কলের মাধ্যমে লাইভ পাবলিশ হয়ে যাবে।`,
            steps: [
                `সুইচটি অন রাখলে রিয়েল-টাইম অটোমেটিক পাবলিশিং চালু থাকবে।`,
                `সুইচটি অফ রাখলে নিউজ শুধুমাত্র Subeditor24 ড্যাশবোর্ডে সেভ থাকবে এবং ম্যানুয়ালি পোস্ট করা যাবে।`
            ],
            filePath: 'Feature Overview',
            code: `Auto Publish: ENABLED -> Triggers API on News Approval`
        },
        custom_api_url: {
            title: 'Custom News Post Endpoint URL',
            subtitle: 'কাস্টম এপিআই রুট ওভাররাইড',
            icon: '<i class="fas fa-route text-rose-400"></i>',
            desc: `যদি আপনার Laravel ওয়েবসাইটে ডিফল্ট <code>/api/external-news-post</code> ছাড়া অন্য কোনো স্পেশাল এপিআই এন্ডপয়েন্ট থাকে (যেমন <code>https://api.site.com/v2/news/create</code>), তবে সম্পূর্ণ লিংকটি এখানে প্রদান করুন।`,
            steps: [
                `ফাঁকা রাখলে স্বয়ংক্রিয়ভাবে <code>Base_URL/api/external-news-post</code> ব্যবহৃত হবে।`
            ],
            filePath: 'Custom API Endpoint',
            code: `POST https://mywebsite.com/api/v1/custom-news-receiver`
        },
        custom_category_url: {
            title: 'Custom Category Fetch URL',
            subtitle: 'ক্যাটাগরি লিস্ট এপিআই',
            icon: '<i class="fas fa-tags text-purple-400"></i>',
            desc: `আপনার ওয়েবসাইট থেকে ক্যাটাগরি তালিকা স্বয়ংক্রিয়ভাবে এনে Subeditor24-এর সাথে ম্যাপ করতে এই এপিআই ব্যবহৃত হয়। এটি থেকে JSON অ্যারে রিটার্ন করতে হবে: <code>[{"id": 1, "name": "জাতীয়"}, {"id": 2, "name": "আন্তর্জাতিক"}]</code>।`,
            steps: [
                `আপনার Laravel প্রজেক্টে একটি ক্যাটাগরি এপিআই রুট তৈরি করুন এবং সেই লিংকটি এখানে দিন।`
            ],
            filePath: 'Laravel File: routes/api.php',
            code: `Route::get('/categories', function () {
    return response()->json(
        \\App\\Models\\Category::select('id', 'name')->get()
    );
});`
        }
    };

    function showFieldHelp(fieldKey) {
        const data = fieldHelpData[fieldKey];
        if (!data) return;

        const modal = document.getElementById('fieldInfoModal');
        const title = document.getElementById('fieldInfoTitle');
        const subtitle = document.getElementById('fieldInfoSubtitle');
        const icon = document.getElementById('fieldInfoIcon');
        const desc = document.getElementById('fieldInfoDesc');
        const stepsContainer = document.getElementById('fieldInfoSteps');
        const filePath = document.getElementById('fieldInfoFilePath');
        const codeBox = document.getElementById('fieldInfoCodeBox');
        const codeWrapper = document.getElementById('fieldInfoCodeWrapper');

        if (title) title.innerHTML = data.title;
        if (subtitle) subtitle.innerText = data.subtitle;
        if (icon && data.icon) icon.innerHTML = data.icon;
        if (desc) desc.innerHTML = data.desc;

        if (stepsContainer && data.steps) {
            stepsContainer.innerHTML = data.steps.map(step => `
                <div class="flex items-start gap-2.5 p-2.5 rounded-xl bg-slate-950/60 border border-slate-800">
                    <span class="text-indigo-400 font-bold text-xs mt-0.5">•</span>
                    <div class="text-xs text-slate-300 flex-1 leading-relaxed">${step}</div>
                </div>
            `).join('');
        }

        if (data.code) {
            const currentToken = document.getElementById('laravel_api_token')?.value?.trim() || 'YOUR_SECRET_TOKEN_HERE';
            const populatedCode = data.code.replace('YOUR_SECRET_TOKEN_HERE', currentToken);
            if (filePath) filePath.innerText = data.filePath || 'Code Snippet';
            if (codeBox) codeBox.innerText = populatedCode;
            if (codeWrapper) codeWrapper.classList.remove('hidden');
        } else {
            if (codeWrapper) codeWrapper.classList.add('hidden');
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeFieldInfoModal() {
        const modal = document.getElementById('fieldInfoModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    function copyFieldInfoCode() {
        const code = document.getElementById('fieldInfoCodeBox')?.innerText;
        if (!code) return;

        navigator.clipboard.writeText(code).then(() => {
            const btn = document.getElementById('fieldInfoCopyBtn');
            if (btn) {
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check text-emerald-400"></i> Copied!';
                setTimeout(() => { btn.innerHTML = orig; }, 2000);
            }
        });
    }

    function closeDiagnosticsModal() {
        const modal = document.getElementById('systemDiagnosticsModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // ⚡ Dynamic Cron Action Handler
    function triggerCronAdminAction(action) {
        const alertBox = document.getElementById('cronActionAlertBox');
        const testBtn = document.getElementById('btnTestRunCron');
        const installBtn = document.getElementById('btnInstallCron');
        const refreshBtn = document.getElementById('btnRefreshCron');

        if (alertBox) {
            alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-indigo-50 text-indigo-700 block animate-pulse border border-indigo-200';
            alertBox.innerText = action === 'install' ? 'সার্ভার ক্রনট্যাব ইনস্টল করা হচ্ছে...' : (action === 'test_run' ? 'শিডিউলার এক্সিকিউট করা হচ্ছে...' : 'স্ট্যাটাস আপডেট করা হচ্ছে...');
            alertBox.classList.remove('hidden');
        }

        if (testBtn) testBtn.disabled = true;
        if (installBtn) installBtn.disabled = true;
        if (refreshBtn) refreshBtn.disabled = true;

        fetch('{{ route("settings.cron-action") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ action: action })
        })
        .then(r => r.json())
        .then(data => {
            if (testBtn) testBtn.disabled = false;
            if (installBtn) installBtn.disabled = false;
            if (refreshBtn) refreshBtn.disabled = false;

            if (data.success) {
                if (data.health) {
                    const h = data.health;
                    if (document.getElementById('cronMetricStatus')) document.getElementById('cronMetricStatus').innerText = h.status_label;
                    if (document.getElementById('cronMetricLastRun')) document.getElementById('cronMetricLastRun').innerText = h.last_run_at;
                    if (document.getElementById('cronMetricSource')) document.getElementById('cronMetricSource').innerText = (h.last_source || '').toUpperCase();
                    if (document.getElementById('cronMetricRuns')) document.getElementById('cronMetricRuns').innerText = h.total_runs + ' times';
                    if (document.getElementById('cronHealthPill')) {
                        const pill = document.getElementById('cronHealthPill');
                        pill.innerText = h.status_label;
                        pill.className = 'text-[10px] font-extrabold px-2.5 py-0.5 rounded-full ' + (h.healthy ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300');
                    }
                }

                if (alertBox) {
                    alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                    alertBox.innerText = '✅ ' + (data.message || 'স্ট্যাটাস সফলভাবে আপডেট হয়েছে!');
                    setTimeout(() => { if(alertBox) alertBox.classList.add('hidden'); }, 6000);
                }
            } else {
                if (alertBox) {
                    alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200 block';
                    alertBox.innerText = '❌ ' + (data.message || 'কিছু সমস্যা হয়েছে।');
                }
            }
        })
        .catch(err => {
            if (testBtn) testBtn.disabled = false;
            if (installBtn) installBtn.disabled = false;
            if (refreshBtn) refreshBtn.disabled = false;
            if (alertBox) {
                alertBox.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200 block';
                alertBox.innerText = '❌ নেটওয়ার্ক ত্রুটি: ' + err.message;
            }
        });
    }

    // ==========================================================
    // 🗄️ DATABASE BACKUP & RESTORE JAVASCRIPT ENGINE
    // ==========================================================
    function showDbAlert(msg, type = 'success') {
        const box = document.getElementById('dbBackupAlertBox');
        if (!box) return;
        box.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-200', 'bg-rose-50', 'text-rose-800', 'border-rose-200', 'bg-amber-50', 'text-amber-800', 'border-amber-200');
        
        if (type === 'success') {
            box.classList.add('bg-emerald-50', 'text-emerald-800', 'border', 'border-emerald-200', 'block');
        } else if (type === 'warning') {
            box.classList.add('bg-amber-50', 'text-amber-800', 'border', 'border-amber-200', 'block');
        } else {
            box.classList.add('bg-rose-50', 'text-rose-800', 'border', 'border-rose-200', 'block');
        }
        box.innerHTML = msg;
        setTimeout(() => { if (box && type === 'success') box.classList.add('hidden'); }, 8000);
    }

    function createDatabaseBackup(compress = true) {
        const btn = compress ? document.getElementById('btnBackupGzip') : document.getElementById('btnBackupSql');
        const origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-70', 'pointer-events-none');
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Exporting ${compress ? 'GZIP' : 'SQL'}...</span>`;
        }

        showDbAlert(`⏳ সম্পূর্ণ ডেটাবেজ এক্সপোর্ট হচ্ছে (${compress ? '.sql.gz' : '.sql'}). অনুগ্রহ করে অপেক্ষা করুন...`, 'warning');

        fetch('{{ route("database.backups.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ compress: compress })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showDbAlert(`✅ <strong>${data.message}</strong> ফাইল সাইজ: <strong>${data.backup.formatted_size}</strong> (${data.backup.filename})`, 'success');
                if (data.backups) renderBackupTable(data.backups);
            } else {
                showDbAlert(`❌ ${data.message || 'ব্যাকআপ তৈরিতে সমস্যা হয়েছে।'}`, 'error');
            }
        })
        .catch(err => {
            showDbAlert(`❌ নেটওয়ার্ক ত্রুটি: ${err.message}`, 'error');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-70', 'pointer-events-none');
                btn.innerHTML = origHtml;
            }
        });
    }

    function refreshDatabaseBackupsList() {
        const icon = document.getElementById('refreshBackupIcon');
        if (icon) icon.classList.add('fa-spin');

        fetch('{{ route("database.backups.index") }}', {
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.backups) {
                renderBackupTable(data.backups);
                showDbAlert('🔄 ব্যাকআপ তালিকা রিফ্রেশ করা হয়েছে।', 'success');
            }
        })
        .catch(err => console.error(err))
        .finally(() => {
            if (icon) icon.classList.remove('fa-spin');
        });
    }

    function renderBackupTable(backups) {
        const tbody = document.getElementById('backupsTableBody');
        const badge = document.getElementById('backupCountBadge');
        if (badge) badge.innerText = `${backups.length} Backups Available`;
        if (!tbody) return;

        if (backups.length === 0) {
            tbody.innerHTML = `
                <tr id="noBackupsRow">
                    <td colspan="5" class="py-8 text-center text-gray-400 text-xs font-medium">
                        <i class="fa-solid fa-box-open text-2xl mb-1 block text-gray-300"></i>
                        কোনো ব্যাকআপ ফাইল এখনো তৈরি করা হয়নি। উপরের বাটনগুলো ব্যবহার করে ব্যাকআপ তৈরি করুন।
                    </td>
                </tr>
            `;
            return;
        }

        let html = '';
        backups.forEach(b => {
            const safetyTag = b.is_safety ? `<span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">🛡️ Auto-Safety</span>` : `<i class="fa-solid fa-database text-cyan-600"></i>`;
            const formatTag = b.is_compressed ? `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">GZIP (.sql.gz)</span>` : `<span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">SQL (.sql)</span>`;
            const downloadUrl = `{{ url('/admin/database-backups/download') }}/${encodeURIComponent(b.filename)}`;

            html += `
                <tr class="hover:bg-slate-50/80 transition" id="row-${b.filename.replace(/[^a-zA-Z0-9]/g, '')}">
                    <td class="py-3 px-4 font-mono font-medium text-slate-800 flex items-center gap-2">
                        ${safetyTag}
                        <span class="truncate max-w-xs" title="${b.filename}">${b.filename}</span>
                    </td>
                    <td class="py-3 px-4">${formatTag}</td>
                    <td class="py-3 px-4 font-semibold text-slate-600">${b.formatted_size}</td>
                    <td class="py-3 px-4 text-slate-500">
                        <div>${b.created_at}</div>
                        <div class="text-[10px] text-slate-400 font-semibold">${b.relative_time}</div>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="${downloadUrl}" class="px-2.5 py-1 bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                            <button type="button" onclick="confirmRestoreBackup('${b.filename}')" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold transition flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-rotate-left"></i> Restore
                            </button>
                            <button type="button" onclick="deleteBackupFile('${b.filename}')" class="px-2 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold transition cursor-pointer">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    function confirmRestoreBackup(filename) {
        if (!confirm(`⚠️ সতর্কবার্তা!\n\nআপনি কি নিশ্চিত যে "${filename}" ফাইলটি থেকে সম্পূর্ণ ডেটাবেজ রিস্টোর করতে চান?\n\nএটি বর্তমান টেবিলগুলোকে প্রতিস্থাপন করবে। তবে কাজ শুরুর পূর্বে স্বয়ংক্রিয়ভাবে একটি Auto-Safety Snapshot ব্যাকআপ তৈরি হবে।`)) {
            return;
        }

        showDbAlert(`⏳ ডেটাবেজ রিস্টোর হচ্ছে (${filename}). অনুগ্রহ করে ব্রাউজার বন্ধ করবেন না...`, 'warning');

        fetch('{{ route("database.backups.restore") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ filename: filename })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showDbAlert(`🎉 <strong>${data.message}</strong>` + (data.details.safety_backup ? `<br><small class="text-emerald-700">রিস্টোরের পূর্বে সেফটি ব্যাকআপ সংরক্ষিত হয়েছে: ${data.details.safety_backup}</small>` : ''), 'success');
                if (data.backups) renderBackupTable(data.backups);
            } else {
                showDbAlert(`❌ ${data.message || 'রিস্টোরে সমস্যা হয়েছে।'}`, 'error');
            }
        })
        .catch(err => {
            showDbAlert(`❌ নেটওয়ার্ক ত্রুটি: ${err.message}`, 'error');
        });
    }

    function deleteBackupFile(filename) {
        if (!confirm(`🗑️ আপনি কি "${filename}" ব্যাকআপ ফাইলটি ডিলিট করতে চান?`)) {
            return;
        }

        fetch(`{{ url('/admin/database-backups/delete') }}/${encodeURIComponent(filename)}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showDbAlert(data.message, 'success');
                if (data.backups) renderBackupTable(data.backups);
            } else {
                showDbAlert(data.message, 'error');
            }
        })
        .catch(err => showDbAlert(`❌ এরর: ${err.message}`, 'error'));
    }

    // Modal Handlers
    function openRestoreUploadModal() {
        const modal = document.getElementById('restoreUploadModal');
        if (modal) modal.classList.remove('hidden'), modal.classList.add('flex');
    }

    function closeRestoreUploadModal() {
        const modal = document.getElementById('restoreUploadModal');
        if (modal) modal.classList.add('hidden'), modal.classList.remove('flex');
    }

    function submitRestoreUploadForm(event) {
        event.preventDefault();
        const fileInput = document.getElementById('restoreFileInput');
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            alert('অনুগ্রহ করে একটি .sql বা .sql.gz ফাইল সিলেক্ট করুন।');
            return;
        }

        if (!confirm('⚠️ সতর্কবার্তা!\n\nআপনি কি আপলোডকৃত ব্যাকআপ ফাইলটি সরাসরি ডেটাবেজে ইমপোর্ট ও রিস্টোর করতে চান?\n\n(রিস্টোরের পূর্বে বর্তমান ডেটাবেজের একটি Auto-Safety Snapshot স্বয়ংক্রিয়ভাবে সংরক্ষিত হবে)')) {
            return;
        }

        const formData = new FormData();
        formData.append('backup_file', fileInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        const btn = document.getElementById('btnSubmitUploadRestore');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Uploading & Restoring...</span>`;
        }

        closeRestoreUploadModal();
        showDbAlert(`⏳ ব্যাকআপ ফাইল আপলোড ও রিস্টোর হচ্ছে (${fileInput.files[0].name}). অনুগ্রহ করে অপেক্ষা করুন...`, 'warning');

        fetch('{{ route("database.backups.restore") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showDbAlert(`🎉 <strong>${data.message}</strong>`, 'success');
                if (data.backups) renderBackupTable(data.backups);
            } else {
                showDbAlert(`❌ ${data.message || 'রিস্টোরে সমস্যা হয়েছে।'}`, 'error');
            }
        })
        .catch(err => {
            showDbAlert(`❌ নেটওয়ার্ক ত্রুটি: ${err.message}`, 'error');
        })
        .finally(() => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<i class="fa-solid fa-rotate-left"></i> <span>Upload & Restore Database</span>`;
            }
            if (fileInput) fileInput.value = '';
        });
    }
</script>

{{-- 📤 UPLOAD & RESTORE MODAL --}}
<div id="restoreUploadModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col">
        <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-orange-600 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-upload"></i>
                </div>
                <div>
                    <h3 class="text-base font-black">Upload & Restore Database</h3>
                    <p class="text-[11px] text-white/80 font-semibold">Import .sql or .sql.gz backup file</p>
                </div>
            </div>
            <button type="button" onclick="closeRestoreUploadModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition cursor-pointer">
                ✕
            </button>
        </div>

        <form onsubmit="submitRestoreUploadForm(event)" class="p-6 space-y-4">
            <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl p-6 text-center hover:border-amber-500 transition bg-slate-50 dark:bg-slate-800/50">
                <i class="fa-solid fa-file-arrow-up text-3xl text-amber-500 mb-2"></i>
                <label for="restoreFileInput" class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1 cursor-pointer">
                    Click to select or drag backup file here
                </label>
                <p class="text-[11px] text-slate-400 mb-3">Supported formats: <strong>.sql</strong> or <strong>.sql.gz</strong> (Max: 100MB)</p>
                <input type="file" id="restoreFileInput" name="backup_file" accept=".sql,.gz,.sql.gz" class="w-full text-xs text-slate-600 dark:text-slate-300 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-100 file:text-amber-800 hover:file:bg-amber-200 cursor-pointer">
            </div>

            <div class="bg-rose-50 border border-rose-200 p-3.5 rounded-xl text-xs text-rose-800 flex items-start gap-2.5">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm mt-0.5 shrink-0"></i>
                <div>
                    <strong>সতর্কতা:</strong> ব্যাকআপ ফাইলটি আপলোড হয়ে রিস্টোর হওয়ার সাথে সাথে ডেটাবেজের বর্তমান টেবিলগুলো মুছে গিয়ে ব্যাকআপের ডেটা ইনসার্ট হবে। রিস্টোরের আগে স্বয়ংক্রিয় সেফটি স্ন্যাপশট সংরক্ষিত হবে।
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" onclick="closeRestoreUploadModal()" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 rounded-xl text-xs font-bold transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" id="btnSubmitUploadRestore" class="px-5 py-2.5 bg-gradient-to-r from-amber-600 to-orange-600 hover:from-amber-700 hover:to-orange-700 text-white rounded-xl text-xs font-bold shadow-md transition flex items-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Upload & Restore Database</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SYSTEM HEALTH & DIAGNOSTICS MODAL --}}
<div id="systemDiagnosticsModal" class="fixed inset-0 bg-slate-950/70 hidden items-center justify-center z-[110] backdrop-blur-md transition-all">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden border border-slate-200 dark:border-slate-800 flex flex-col max-h-[90vh]">
        <div class="px-6 py-4 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-lg shadow-inner">
                    <i class="fa-solid fa-heart-pulse"></i>
                </div>
                <div>
                    <h3 class="text-base font-black">System Health Diagnostics</h3>
                    <p class="text-[11px] text-white/80 font-semibold" id="diagnosticsTimestamp">Live Service & Connectivity Check</p>
                </div>
            </div>
            <button type="button" onclick="closeDiagnosticsModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition cursor-pointer">
                ✕
            </button>
        </div>

        <div class="p-6 overflow-y-auto space-y-4 font-bangla">
            {{-- Loading Spinner --}}
            <div id="diagnosticsLoading" class="py-12 flex flex-col items-center justify-center gap-3">
                <div class="w-12 h-12 rounded-full border-4 border-emerald-200 border-t-emerald-600 animate-spin"></div>
                <p class="text-sm font-bold text-slate-600 dark:text-slate-300 animate-pulse">Testing Database, AI, and API connections...</p>
            </div>

            {{-- Diagnostics Results Container --}}
            <div id="diagnosticsResultsContainer" class="hidden space-y-3">
                {{-- Injected dynamically --}}
            </div>
        </div>

        <div class="p-4 bg-slate-50 dark:bg-slate-850 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center font-bangla">
            <span class="text-xs text-slate-400 font-semibold">Automated live connectivity audit</span>
            <div class="flex items-center gap-2">
                <button type="button" onclick="runDiagnosticsModal()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                    <i class="fa-solid fa-rotate-right"></i> Re-test
                </button>
                <button type="button" onclick="closeDiagnosticsModal()" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
