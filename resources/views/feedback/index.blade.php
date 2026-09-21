@extends('layouts.app')

@section('title', 'Feature Requests & Roadmap - Subeditor24')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 space-y-6">

    {{-- PAGE HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-slate-200 dark:border-slate-800 gap-4">
        <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 flex items-center justify-center text-lg border border-slate-200 dark:border-slate-700 shrink-0 shadow-sm">
                <i class="fa-solid fa-lightbulb text-amber-500"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight">Feature Requests & Roadmap</h1>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">{{ $stats['total'] }} items</span>
                </div>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-0.5">Submit ideas, vote on requested features, and follow what we're building next.</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <button type="button" onclick="openNewFeedbackModal()" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-xs sm:text-sm font-semibold shadow-sm transition-all cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Submit Idea</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-xl text-xs sm:text-sm font-medium flex items-center gap-2 shadow-sm">
            <i class="fa-solid fa-circle-check text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- MAIN GRID LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        {{-- LEFT / MAIN CONTENT AREA --}}
        <div class="lg:col-span-8 space-y-4">

            {{-- TOOLBAR & TABS --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-3.5 space-y-3 shadow-sm">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    
                    {{-- Status Tabs --}}
                    <div class="flex items-center gap-1 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                        <a href="{{ route('feedback.index', array_merge(request()->query(), ['tab' => 'all'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($tab === 'all') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            All
                        </a>
                        <a href="{{ route('feedback.index', array_merge(request()->query(), ['tab' => 'roadmap'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($tab === 'roadmap') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            In Progress ({{ $stats['in_progress'] }})
                        </a>
                        <a href="{{ route('feedback.index', array_merge(request()->query(), ['tab' => 'completed'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($tab === 'completed') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            Completed ({{ $stats['completed'] }})
                        </a>
                        <a href="{{ route('feedback.index', array_merge(request()->query(), ['tab' => 'my'])) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ ($tab === 'my') ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                            My Submissions ({{ $stats['my_count'] }})
                        </a>
                    </div>

                    {{-- Sort Dropdown --}}
                    <div class="flex items-center gap-2 self-end sm:self-auto shrink-0">
                        <span class="text-xs text-slate-500 font-medium">Sort by:</span>
                        <div class="inline-flex rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-0.5">
                            <a href="{{ route('feedback.index', array_merge(request()->query(), ['sort' => 'top'])) }}" class="px-2.5 py-1 text-xs font-medium rounded-md transition {{ ($sort === 'top') ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200' }}">
                                Top Voted
                            </a>
                            <a href="{{ route('feedback.index', array_merge(request()->query(), ['sort' => 'newest'])) }}" class="px-2.5 py-1 text-xs font-medium rounded-md transition {{ ($sort === 'newest') ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-white shadow-xs font-semibold' : 'text-slate-500 hover:text-slate-900 dark:hover:text-slate-200' }}">
                                Newest
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Search & Filters --}}
                <form method="GET" action="{{ route('feedback.index') }}" class="pt-2.5 border-t border-slate-100 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="sort" value="{{ $sort }}">

                    <div class="sm:col-span-6 relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by title or keyword..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 focus:outline-hidden focus:ring-1 focus:ring-indigo-500">
                    </div>

                    <div class="sm:col-span-3">
                        <select name="category" onchange="this.form.submit()" class="w-full py-1.5 px-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium">
                            <option value="all">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="sm:col-span-3 flex gap-1.5">
                        <select name="type" onchange="this.form.submit()" class="w-full py-1.5 px-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-800 dark:text-slate-100 focus:outline-hidden focus:ring-1 focus:ring-indigo-500 font-medium">
                            <option value="">All Types</option>
                            <option value="feature" {{ request('type') === 'feature' ? 'selected' : '' }}>Feature</option>
                            <option value="bug" {{ request('type') === 'bug' ? 'selected' : '' }}>Bug</option>
                            <option value="improvement" {{ request('type') === 'improvement' ? 'selected' : '' }}>Improvement</option>
                        </select>
                        @if(request()->filled('q') || request()->filled('category') || request()->filled('type'))
                            <a href="{{ route('feedback.index', ['tab' => $tab, 'sort' => $sort]) }}" class="px-2.5 py-1.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 rounded-lg text-xs flex items-center justify-center shrink-0" title="Clear Filters">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- FEEDBACK ITEMS LIST --}}
            <div class="space-y-3">
                @forelse($feedbacks as $item)
                    @php
                        $hasVoted = in_array($item->id, $userVotedIds);
                        $isAuthor = $item->user_id === auth()->id();
                    @endphp
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 p-4 sm:p-5 transition-all shadow-xs flex items-start gap-4">
                        
                        {{-- UPVOTE BUTTON --}}
                        <button type="button" onclick="toggleFeedbackVote({{ $item->id }}, this)" data-feedback-id="{{ $item->id }}" class="vote-btn shrink-0 w-12 py-2 rounded-xl border flex flex-col items-center justify-center transition-all cursor-pointer select-none {{ $hasVoted ? 'bg-indigo-50 border-indigo-300 text-indigo-700 dark:bg-indigo-950/60 dark:border-indigo-800 dark:text-indigo-300' : 'bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900' }}">
                            <i class="fa-solid fa-chevron-up text-xs mb-0.5 {{ $hasVoted ? 'text-indigo-600 dark:text-indigo-400' : 'text-slate-400' }}"></i>
                            <span class="vote-count text-xs font-bold leading-none">{{ $item->votes_count }}</span>
                        </button>

                        {{-- ITEM DETAILS --}}
                        <div class="flex-1 min-w-0 space-y-2">
                            
                            {{-- TITLE & BADGES --}}
                            <div>
                                <div class="flex flex-wrap items-center gap-1.5 mb-1">
                                    {{-- Type badge --}}
                                    @if($item->type === 'feature')
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Feature</span>
                                    @elseif($item->type === 'bug')
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-300 border border-rose-200 dark:border-rose-800">Bug</span>
                                    @else
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-purple-50 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300 border border-purple-200 dark:border-purple-800">Improvement</span>
                                    @endif

                                    {{-- Category badge --}}
                                    <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ $item->category }}</span>

                                    {{-- Status badge --}}
                                    @if($item->status === 'under_review')
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-amber-50 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 border border-amber-200 dark:border-amber-800">Under Review</span>
                                    @elseif($item->status === 'planned')
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-blue-50 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300 border border-blue-200 dark:border-blue-800">Planned</span>
                                    @elseif($item->status === 'in_progress')
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">In Progress</span>
                                    @elseif($item->status === 'completed')
                                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">Completed</span>
                                    @elseif($item->status === 'declined')
                                        <span class="text-[10px] font-medium px-2 py-0.5 rounded bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">Closed</span>
                                    @endif
                                </div>

                                <h2 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white leading-snug">
                                    {{ $item->title }}
                                </h2>
                            </div>

                            {{-- DESCRIPTION --}}
                            <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed whitespace-pre-line">
                                {{ $item->description }}
                            </p>

                            {{-- OFFICIAL ADMIN RESPONSE --}}
                            @if($item->admin_response)
                                <div class="bg-slate-50 dark:bg-slate-800/60 border-l-2 border-indigo-500 p-3 rounded-r-lg space-y-1">
                                    <div class="text-[11px] font-semibold text-indigo-900 dark:text-indigo-300 flex items-center gap-1.5">
                                        <i class="fa-solid fa-reply text-indigo-500 text-[10px]"></i>
                                        <span>Team Response:</span>
                                    </div>
                                    <p class="text-xs text-slate-700 dark:text-slate-300 leading-relaxed">
                                        {{ $item->admin_response }}
                                    </p>
                                </div>
                            @endif

                            {{-- FOOTER META & ACTIONS --}}
                            <div class="flex items-center justify-between pt-2 text-xs text-slate-400">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-slate-600 dark:text-slate-400">{{ $item->anonymous_author }}</span>
                                    @if($isAuthor)
                                        <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 dark:bg-indigo-950/60 px-1.5 py-0.2 rounded">You</span>
                                    @endif
                                    @if($isSuperAdmin && $item->user)
                                        <span class="text-[10px] text-amber-600 font-mono" title="Visible only to Super Admin">({{ $item->user->email }})</span>
                                    @endif
                                    <span>•</span>
                                    <span>{{ $item->created_at->diffForHumans() }}</span>
                                </div>

                                <div class="flex items-center gap-2">
                                    @if($isSuperAdmin)
                                        <button type="button" onclick="openAdminStatusModal({{ $item->id }}, '{{ $item->status }}', @js($item->admin_response))" class="text-xs font-medium text-slate-600 hover:text-indigo-600 dark:text-slate-400 transition cursor-pointer">
                                            Manage Status
                                        </button>
                                    @endif

                                    @if($isSuperAdmin || $isAuthor)
                                        <form method="POST" action="{{ route('feedback.destroy', $item->id) }}" onsubmit="return confirm('Delete this request?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-slate-400 hover:text-rose-600 transition cursor-pointer" title="Delete">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                @empty
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-10 text-center space-y-3">
                        <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 mx-auto flex items-center justify-center text-sm">
                            <i class="fa-solid fa-inbox"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-900 dark:text-white">No requests found</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Submit the first idea or try adjusting your filter.</p>
                        </div>
                        <button type="button" onclick="openNewFeedbackModal()" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-medium hover:bg-indigo-700 transition cursor-pointer">
                            <i class="fa-solid fa-plus text-xs"></i> Submit Request
                        </button>
                    </div>
                @endforelse

                {{-- PAGINATION --}}
                <div class="pt-2">
                    {{ $feedbacks->links() }}
                </div>
            </div>
        </div>

        {{-- RIGHT SIDEBAR --}}
        <div class="lg:col-span-4 space-y-4">
            
            {{-- QUICK SUBMIT CARD --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 space-y-3 shadow-xs">
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-paper-plane text-indigo-600"></i>
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Have a suggestion?</h3>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Let us know what feature or improvement would make your work faster and easier.
                </p>
                <button type="button" onclick="openNewFeedbackModal()" class="w-full py-2 px-3 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-700 text-white text-xs font-semibold transition cursor-pointer flex items-center justify-center gap-1.5">
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Submit New Idea</span>
                </button>
            </div>

            {{-- ROADMAP STATUS SUMMARY --}}
            <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 p-5 space-y-3 shadow-xs">
                <h3 class="text-xs font-semibold text-slate-900 dark:text-white uppercase tracking-wider text-slate-400">Roadmap Overview</h3>
                
                <div class="space-y-2 text-xs">
                    <a href="{{ route('feedback.index', ['tab' => 'under_review']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-amber-400"></span> Under Review
                        </span>
                        <span class="font-medium text-slate-500">{{ \App\Models\Feedback::where('status', 'under_review')->count() }}</span>
                    </a>

                    <a href="{{ route('feedback.index', ['tab' => 'planned']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span> Planned
                        </span>
                        <span class="font-medium text-slate-500">{{ \App\Models\Feedback::where('status', 'planned')->count() }}</span>
                    </a>

                    <a href="{{ route('feedback.index', ['tab' => 'in_progress']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span> In Progress
                        </span>
                        <span class="font-medium text-slate-500">{{ \App\Models\Feedback::where('status', 'in_progress')->count() }}</span>
                    </a>

                    <a href="{{ route('feedback.index', ['tab' => 'completed']) }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <span class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Completed
                        </span>
                        <span class="font-medium text-slate-500">{{ \App\Models\Feedback::where('status', 'completed')->count() }}</span>
                    </a>
                </div>
            </div>

            {{-- PRIVACY CARD --}}
            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-800 p-4 space-y-1.5 text-xs text-slate-500">
                <div class="flex items-center gap-1.5 font-semibold text-slate-700 dark:text-slate-300">
                    <i class="fa-solid fa-lock text-slate-400"></i>
                    <span>Privacy Policy</span>
                </div>
                <p class="leading-relaxed">
                    Your real name and email address are never shown publicly to other users.
                </p>
            </div>

        </div>

    </div>

</div>

{{-- SUBMIT NEW FEEDBACK MODAL --}}
<div id="newFeedbackModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Submit New Request</h3>
            <button type="button" onclick="closeNewFeedbackModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center text-sm cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('feedback.store') }}" class="space-y-3.5">
            @csrf

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Type</label>
                    <select name="type" required class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-indigo-500">
                        <option value="feature">Feature Request</option>
                        <option value="bug">Bug Report</option>
                        <option value="improvement">Improvement</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Category</label>
                    <select name="category" required class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-indigo-500">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Title</label>
                <input type="text" name="title" required maxlength="255" placeholder="e.g. Add 1-click batch image generator" class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:outline-hidden focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Description</label>
                <textarea name="description" rows="4" required maxlength="5000" placeholder="Explain what you need and how it will help your news publishing workflow..." class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:outline-hidden focus:ring-1 focus:ring-indigo-500 leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeNewFeedbackModal()" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>

{{-- SUPER ADMIN STATUS MODAL --}}
@if($isSuperAdmin)
<div id="adminStatusModal" class="hidden fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200 dark:border-slate-800 space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
            <h3 class="text-base font-semibold text-slate-900 dark:text-white">Update Status & Response</h3>
            <button type="button" onclick="closeAdminStatusModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 flex items-center justify-center text-sm cursor-pointer">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="adminStatusForm" method="POST" action="" class="space-y-3.5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                <select id="modal_status_select" name="status" required class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-900 dark:text-white focus:outline-hidden focus:ring-1 focus:ring-indigo-500">
                    <option value="under_review">Under Review</option>
                    <option value="planned">Planned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="declined">Closed</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Team Note / Response (Optional)</label>
                <textarea id="modal_response_text" name="admin_response" rows="3" placeholder="e.g. Added to next sprint or completed in v2.4..." class="w-full py-2 px-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white font-medium focus:outline-hidden focus:ring-1 focus:ring-indigo-500 leading-relaxed"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" onclick="closeAdminStatusModal()" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 transition cursor-pointer">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endif

@push('scripts')
<script>
function openNewFeedbackModal() {
    const modal = document.getElementById('newFeedbackModal');
    if (modal) modal.classList.remove('hidden');
}

function closeNewFeedbackModal() {
    const modal = document.getElementById('newFeedbackModal');
    if (modal) modal.classList.add('hidden');
}

function openAdminStatusModal(id, currentStatus, currentResponse) {
    const modal = document.getElementById('adminStatusModal');
    const form = document.getElementById('adminStatusForm');
    const statusSelect = document.getElementById('modal_status_select');
    const responseText = document.getElementById('modal_response_text');

    if (!modal || !form) return;

    form.action = `/feedback/${id}/status`;
    if (statusSelect) statusSelect.value = currentStatus || 'under_review';
    if (responseText) responseText.value = currentResponse || '';

    modal.classList.remove('hidden');
}

function closeAdminStatusModal() {
    const modal = document.getElementById('adminStatusModal');
    if (modal) modal.classList.add('hidden');
}

// 1-Click AJAX Upvote Handler
function toggleFeedbackVote(feedbackId, btnElement) {
    if (!feedbackId || !btnElement) return;

    const countElement = btnElement.querySelector('.vote-count');
    const iconElement = btnElement.querySelector('i');
    const originalCount = parseInt(countElement.innerText) || 0;
    const isCurrentlyVoted = btnElement.classList.contains('bg-indigo-50');

    // Optimistic UI update
    const newCount = isCurrentlyVoted ? Math.max(0, originalCount - 1) : originalCount + 1;
    countElement.innerText = newCount;

    if (isCurrentlyVoted) {
        btnElement.className = 'vote-btn shrink-0 w-12 py-2 rounded-xl border flex flex-col items-center justify-center transition-all cursor-pointer select-none bg-slate-50 dark:bg-slate-800/80 border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900';
        iconElement.className = 'fa-solid fa-chevron-up text-xs mb-0.5 text-slate-400';
    } else {
        btnElement.className = 'vote-btn shrink-0 w-12 py-2 rounded-xl border flex flex-col items-center justify-center transition-all cursor-pointer select-none bg-indigo-50 border-indigo-300 text-indigo-700 dark:bg-indigo-950/60 dark:border-indigo-800 dark:text-indigo-300';
        iconElement.className = 'fa-solid fa-chevron-up text-xs mb-0.5 text-indigo-600 dark:text-indigo-400';
    }

    fetch(`/feedback/${feedbackId}/vote`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            countElement.innerText = data.votes_count;
        } else {
            countElement.innerText = originalCount;
        }
    })
    .catch(err => {
        console.error('Vote error:', err);
        countElement.innerText = originalCount;
    });
}
</script>
@endpush
@endsection
