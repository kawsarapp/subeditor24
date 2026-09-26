@extends('layouts.app')

@section('title', 'YouTube AI Automation | Channel Manager')

@section('content')
<div class="space-y-8 max-w-7xl mx-auto pb-12">

    {{-- Top Header / Hero --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-red-700 via-rose-700 to-red-950 rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-red-950/20">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-xs font-bold uppercase tracking-wider text-red-100">
                    <i class="fa-brands fa-youtube text-red-400"></i> YouTube AI Studio & Auto-Pilot
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                    Multi-Channel Video SEO & Auto-Publisher
                </h1>
                <p class="text-xs sm:text-sm text-red-100/90 leading-relaxed">
                    ইউটিউবে ড্রাফট/আনলিস্টেড ভিডিও আপলোড করুন — আমাদের এআই স্বয়ংক্রিয়ভাবে ভাইরাল টাইটেল, এসইও ডেসক্রিপশন ও ট্যাগ যুক্ত করে অটো-পাবলিশ করবে।
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('youtube.videos.index') }}" class="px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white font-extrabold text-xs transition flex items-center gap-2 shadow-sm">
                    <i class="fa-solid fa-film"></i> Video Manager
                </a>
                <a href="{{ route('youtube.auth.redirect') }}" class="px-6 py-3 rounded-2xl bg-white text-red-700 hover:bg-red-50 font-black text-xs transition shadow-lg hover:shadow-xl hover:scale-[1.02] flex items-center gap-2">
                    <i class="fa-brands fa-google text-red-600 text-sm"></i>
                    <span>+ Connect YouTube Channel</span>
                </a>
            </div>
        </div>

        {{-- Background Accents --}}
        <div class="absolute -bottom-12 -right-12 w-64 h-64 rounded-full bg-red-500/20 blur-3xl pointer-events-none"></div>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 flex items-center justify-center text-xl font-black">
                <i class="fa-brands fa-youtube"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Connected Channels</p>
                <h4 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total_channels'] }}</h4>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xl font-black">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Unlisted / Drafts</p>
                <h4 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['draft_videos'] }}</h4>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-xl font-black">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">AI SEO Ready</p>
                <h4 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['optimized_ready'] }}</h4>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xl font-black">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Published Live</p>
                <h4 class="text-xl font-black text-slate-900 dark:text-white">{{ $stats['total_published'] }}</h4>
            </div>
        </div>
    </div>

    {{-- Connected Channels List --}}
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fa-solid fa-layer-group text-red-600"></i>
                Your YouTube Channels
            </h2>
            <span class="text-xs text-slate-500 font-semibold">1 Google Login = Permanent Offline Access</span>
        </div>

        @if($channels->isEmpty())
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center border-2 border-dashed border-slate-200 dark:border-slate-700 space-y-4">
                <div class="w-20 h-20 rounded-3xl bg-red-50 dark:bg-red-950/30 text-red-600 mx-auto flex items-center justify-center text-3xl shadow-inner">
                    <i class="fa-brands fa-youtube"></i>
                </div>
                <div class="space-y-1 max-w-md mx-auto">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">কোনো ইউটিউব চ্যানেল কানেক্ট করা নেই</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        আপনার গুগল অ্যাকাউন্ট দিয়ে এক ক্লিকে চ্যানেল কানেক্ট করুন। আপনি চাইলে একসাথে একাধিক চ্যানেল পরিচালনা করতে পারবেন।
                    </p>
                </div>
                <div>
                    <a href="{{ route('youtube.auth.redirect') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black text-xs shadow-lg shadow-red-600/30 transition">
                        <i class="fa-brands fa-google"></i> Connect First Channel
                    </a>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($channels as $channel)
                    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group">
                        <div class="p-6 space-y-5">
                            {{-- Header --}}
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $channel->thumbnail_url ?: asset('images/placeholder.png') }}" alt="{{ $channel->channel_title }}" class="w-14 h-14 rounded-2xl object-cover border-2 border-red-500/20 shadow-sm">
                                    <div>
                                        <h3 class="font-black text-slate-900 dark:text-white text-base leading-snug group-hover:text-red-600 transition">
                                            {{ $channel->channel_title }}
                                        </h3>
                                        <p class="text-[11px] font-semibold text-slate-400">
                                            {{ $channel->custom_url ?: 'ID: ' . \Illuminate\Support\Str::limit($channel->channel_id, 12) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Status indicator --}}
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase tracking-wider {{ $channel->is_active ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-slate-100 text-slate-500' }}">
                                    Connected
                                </span>
                            </div>

                            {{-- Channel Stats --}}
                            <div class="grid grid-cols-3 gap-2 py-3 px-4 bg-slate-50 dark:bg-slate-900/50 rounded-2xl border border-slate-100 dark:border-slate-800 text-center">
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Subs</span>
                                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200">{{ number_format($channel->subscriber_count) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Videos</span>
                                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-200">{{ number_format($channel->video_count) }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold text-slate-400">Drafts</span>
                                    <span class="text-xs font-extrabold text-amber-600">{{ $channel->draft_count }}</span>
                                </div>
                            </div>

                            {{-- Auto-Pilot Toggle Box --}}
                            <div class="flex items-center justify-between p-3.5 bg-gradient-to-r from-red-50/60 to-rose-50/30 dark:from-red-950/20 dark:to-slate-900 rounded-2xl border border-red-100 dark:border-red-900/30">
                                <div class="space-y-0.5">
                                    <span class="text-xs font-black text-slate-900 dark:text-white flex items-center gap-1.5">
                                        <i class="fa-solid fa-bolt text-red-600"></i> Auto-Pilot Mode
                                    </span>
                                    <p class="text-[10px] text-slate-500">নতুন ভিডিও ড্রাফটে পেলে অটো-এসইও করে পাবলিশ করবে</p>
                                </div>
                                <button type="button" onclick="toggleAutoPilot({{ $channel->id }}, this)" class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $channel->auto_pilot_enabled ? 'bg-red-600' : 'bg-slate-300 dark:bg-slate-700' }}">
                                    <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $channel->auto_pilot_enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Footer Actions --}}
                        <div class="p-4 bg-slate-50/80 dark:bg-slate-900/80 border-t border-slate-100 dark:border-slate-700/80 flex items-center justify-between gap-2">
                            <form action="{{ route('youtube.videos.sync', $channel->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:text-red-600 hover:border-red-300 text-xs font-bold transition flex items-center gap-1.5 shadow-sm" title="Sync latest videos from YouTube">
                                    <i class="fa-solid fa-arrows-rotate"></i> Sync Videos
                                </button>
                            </form>

                            <div class="flex items-center gap-2">
                                <button type="button" onclick="openChannelSettings({{ $channel->id }})" class="p-2 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 hover:text-indigo-600 text-xs font-bold transition" title="Channel Settings">
                                    <i class="fa-solid fa-sliders"></i>
                                </button>

                                <a href="{{ route('youtube.videos.index', ['channel_id' => $channel->id]) }}" class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold transition flex items-center gap-1 shadow-md shadow-red-600/20">
                                    <span>Videos</span> <i class="fa-solid fa-arrow-right text-[10px]"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Channel Settings Modal --}}
                    <div id="settings-modal-{{ $channel->id }}" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
                        <div class="bg-white dark:bg-slate-800 rounded-3xl max-w-lg w-full p-6 space-y-6 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-y-auto max-h-[90vh]">
                            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-700">
                                <div>
                                    <h3 class="text-base font-black text-slate-900 dark:text-white">Channel Settings</h3>
                                    <p class="text-xs text-slate-400">{{ $channel->channel_title }}</p>
                                </div>
                                <button type="button" onclick="closeChannelSettings({{ $channel->id }})" class="text-slate-400 hover:text-slate-600 text-lg">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <form action="{{ route('youtube.channels.settings', $channel->id) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">Target Language</label>
                                    <select name="default_language" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-semibold">
                                        <option value="bn" {{ $channel->default_language === 'bn' ? 'selected' : '' }}>Bengali (বাংলা)</option>
                                        <option value="en" {{ $channel->default_language === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="hi" {{ $channel->default_language === 'hi' ? 'selected' : '' }}>Hindi (हिंदी)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">Title Strategy</label>
                                    <select name="title_style" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-semibold">
                                        <option value="viral_curiosity" {{ $channel->title_style === 'viral_curiosity' ? 'selected' : '' }}>Viral Curiosity & High CTR (Recommended)</option>
                                        <option value="breaking_news" {{ $channel->title_style === 'breaking_news' ? 'selected' : '' }}>Breaking News & Direct Lead</option>
                                        <option value="seo_keyword" {{ $channel->title_style === 'seo_keyword' ? 'selected' : '' }}>Search Query & SEO Question</option>
                                        <option value="storytelling" {{ $channel->title_style === 'storytelling' ? 'selected' : '' }}>Emotional & Storytelling Hook</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">Default Privacy When Publishing</label>
                                    <select name="default_privacy" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-semibold">
                                        <option value="public" {{ $channel->default_privacy === 'public' ? 'selected' : '' }}>Public (Live to All Viewers)</option>
                                        <option value="unlisted" {{ $channel->default_privacy === 'unlisted' ? 'selected' : '' }}>Unlisted (Only with link)</option>
                                        <option value="private" {{ $channel->default_privacy === 'private' ? 'selected' : '' }}>Private</option>
                                    </select>
                                </div>

                                {{-- DYNAMIC YES/NO AI SWITCH BOARD --}}
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-700 space-y-3">
                                    <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider flex items-center gap-1.5">
                                        <i class="fa-solid fa-sliders text-indigo-600"></i> AI Optimization Controls (Yes / No)
                                    </span>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-1">
                                        {{-- 1. Title --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">🎯 AI Title Rewrite</span>
                                            <input type="checkbox" name="opt_title" value="1" {{ $channel->opt_title !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 2. Description --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">📝 SEO Description</span>
                                            <input type="checkbox" name="opt_description" value="1" {{ $channel->opt_description !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 3. Search Tags --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">🏷️ Generate Search Tags</span>
                                            <input type="checkbox" name="opt_tags" value="1" {{ $channel->opt_tags !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 4. Hashtags --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">📌 Add Viral Hashtags</span>
                                            <input type="checkbox" name="opt_hashtags" value="1" {{ $channel->opt_hashtags !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 5. Chapters / Timestamps --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">⏱️ Video Chapters</span>
                                            <input type="checkbox" name="opt_chapters" value="1" {{ $channel->opt_chapters !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 6. Thumbnail Punch Ideas --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">🖼️ Thumbnail Punch Text</span>
                                            <input type="checkbox" name="opt_thumbnail_ideas" value="1" {{ $channel->opt_thumbnail_ideas !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 7. Pinned Comment --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">📌 Pinned Engagement Comment</span>
                                            <input type="checkbox" name="opt_pinned_comment" value="1" {{ $channel->opt_pinned_comment !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 8. Dual Language Search --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">🌐 Dual-Intent (Bangla+English)</span>
                                            <input type="checkbox" name="opt_dual_language" value="1" {{ $channel->opt_dual_language !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>

                                        {{-- 9. Append Footer --}}
                                        <label class="flex items-center justify-between p-2.5 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 cursor-pointer">
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">🔗 Append Social Footer</span>
                                            <input type="checkbox" name="append_footer" value="1" {{ $channel->append_footer !== false ? 'checked' : '' }} class="w-4 h-4 text-red-600 rounded border-slate-300 focus:ring-red-500">
                                        </label>
                                    </div>
                                </div>

                                {{-- Custom AI Instructions Prompt --}}
                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">
                                        🤖 Custom AI Prompt / Specific Channel Rules (Optional)
                                    </label>
                                    <textarea name="custom_ai_prompt" rows="2" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-normal" placeholder="e.g. Always include #Cricket in hashtags, keep title under 65 characters, write in casual friendly tone...">{{ $channel->custom_ai_prompt }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">Default Description Footer (Social Links & Credits)</label>
                                    <textarea name="custom_description_footer" rows="3" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-normal" placeholder="Follow us on Facebook: https://...&#10;Subscribe for more updates!">{{ $channel->custom_description_footer }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-xs font-extrabold text-slate-700 dark:text-slate-300 mb-1">Fixed Brand Tags (Comma Separated)</label>
                                    <input type="text" name="custom_tags_template" value="{{ $channel->custom_tags_template }}" class="w-full px-3.5 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-xs font-normal" placeholder="bangla news, subeditor24, viral video">
                                </div>

                                <div class="pt-4 flex items-center justify-between gap-3 border-t border-slate-100 dark:border-slate-700">
                                    <button type="button" onclick="confirmDisconnect({{ $channel->id }}, '{{ $channel->channel_title }}')" class="text-xs font-bold text-red-600 hover:underline">
                                        Disconnect Channel
                                    </button>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="closeChannelSettings({{ $channel->id }})" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600">Cancel</button>
                                        <button type="submit" class="px-5 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-extrabold shadow-md">Save Settings</button>
                                    </div>
                                </div>
                            </form>

                            <form id="disconnect-form-{{ $channel->id }}" action="{{ route('youtube.channels.disconnect', $channel->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Activity & Audit Logs --}}
    @if($recentLogs->isNotEmpty())
    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700 shadow-sm space-y-4">
        <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-slate-400"></i>
            Recent Automation & API Logs
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="text-slate-400 uppercase tracking-wider border-b border-slate-100 dark:border-slate-700 font-bold text-[10px]">
                        <th class="py-2.5 px-3">Time</th>
                        <th class="py-2.5 px-3">Channel</th>
                        <th class="py-2.5 px-3">Action</th>
                        <th class="py-2.5 px-3">Status</th>
                        <th class="py-2.5 px-3">Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 font-semibold text-slate-700 dark:text-slate-300">
                    @foreach($recentLogs as $log)
                    <tr>
                        <td class="py-2.5 px-3 text-slate-400 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                        <td class="py-2.5 px-3 whitespace-nowrap">{{ $log->channel?->channel_title ?: 'System' }}</td>
                        <td class="py-2.5 px-3 uppercase text-[10px] font-black text-slate-500">{{ $log->action }}</td>
                        <td class="py-2.5 px-3">
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase {{ $log->status === 'success' ? 'bg-emerald-50 text-emerald-600' : ($log->status === 'failed' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600') }}">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 max-w-md truncate" title="{{ $log->message }}">{{ $log->message }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
function openChannelSettings(id) {
    document.getElementById('settings-modal-' + id).classList.remove('hidden');
}
function closeChannelSettings(id) {
    document.getElementById('settings-modal-' + id).classList.add('hidden');
}
function confirmDisconnect(id, title) {
    if (confirm('Are you sure you want to disconnect "' + title + '"?')) {
        document.getElementById('disconnect-form-' + id).submit();
    }
}
async function toggleAutoPilot(channelId, btn) {
    try {
        const res = await fetch(`/youtube/channels/${channelId}/toggle-autopilot`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            const dot = btn.querySelector('span');
            if (data.auto_pilot_enabled) {
                btn.classList.remove('bg-slate-300', 'dark:bg-slate-700');
                btn.classList.add('bg-red-600');
                dot.classList.remove('translate-x-0');
                dot.classList.add('translate-x-5');
            } else {
                btn.classList.add('bg-slate-300', 'dark:bg-slate-700');
                btn.classList.remove('bg-red-600');
                dot.classList.add('translate-x-0');
                dot.classList.remove('translate-x-5');
            }
        }
    } catch (e) {
        console.error(e);
    }
}
</script>
@endpush
@endsection
