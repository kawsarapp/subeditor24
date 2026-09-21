@extends('layouts.app')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700;800&display=swap');
    .font-bangla { font-family: 'Hind Siliguri', sans-serif; }
    
    /* Mobile Responsive Table Fix */
    @media (max-width: 640px) {
        .mobile-card-table thead { display: none; }
        .mobile-card-table tr { 
            display: block; 
            margin-bottom: 1.5rem; 
            border: 1px solid #f1f5f9; 
            border-radius: 1.5rem; 
            background: #fff; 
            padding: 1rem;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        .mobile-card-table td { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 0.75rem 0; 
            border-bottom: 1px solid #f8fafc;
        }
        .mobile-card-table td:last-child { border-bottom: none; }
        .mobile-card-table td::before { 
            content: attr(data-label); 
            font-weight: 800; 
            color: #94a3b8; 
            font-size: 0.7rem; 
            text-transform: uppercase; 
        }
    }
    
    /* Modal Animation */
    .modal-enter { opacity: 0; transform: scale(0.95) translateY(10px); }
    .modal-enter-active { opacity: 1; transform: scale(1) translateY(0); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
</style>

<div class="min-h-screen bg-gray-50/50 py-6 sm:py-10 px-3 sm:px-6 lg:px-8 font-bangla">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header Section --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-800 flex items-center gap-3">
                    <span class="p-3 bg-indigo-100 text-indigo-600 rounded-2xl shadow-sm">
                        <i class="fa-solid fa-users-gears"></i>
                    </span>
                    My Reporters
                </h2>
                <p class="text-slate-500 mt-2 text-sm font-medium">Correspondent credentials, activity, and access management</p>
            </div>
            
            <button onclick="openModal()" class="w-full sm:w-auto bg-indigo-600 text-white px-6 py-3.5 rounded-2xl font-bold hover:bg-indigo-700 transition-all transform active:scale-95 shadow-xl shadow-indigo-100 flex items-center justify-center gap-2 group">
                <i class="fa-solid fa-user-plus group-hover:rotate-12 transition-transform"></i>
                Add New Reporter
            </button>
        </div>

        {{-- 🔥 NEW: Stats & Top Reporter Section --}}
        @php
            $totalReporters = method_exists($reporters, 'total') ? $reporters->total() : $reporters->count();
            // Fallback to 0 if news_count is missing from DB
            $topRep = collect(method_exists($reporters, 'items') ? $reporters->items() : $reporters)->sortByDesc('news_count')->first();
        @endphp
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl"><i class="fa-solid fa-users"></i></div>
                <div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Total Reporters</p>
                    <h4 class="text-2xl font-black text-slate-800">{{ $totalReporters }} Members</h4>
                </div>
            </div>

            <div class="bg-gradient-to-r from-amber-50 to-orange-50 p-5 rounded-2xl border border-amber-100 shadow-sm flex items-center gap-4 md:col-span-2 relative overflow-hidden">
                <i class="fa-solid fa-trophy absolute -right-4 -bottom-4 text-7xl text-amber-500/10 transform -rotate-12"></i>
                <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-xl flex items-center justify-center text-2xl shadow-inner border border-amber-200"><i class="fa-solid fa-crown"></i></div>
                <div class="z-10">
                    <p class="text-xs font-black text-amber-600/70 uppercase tracking-widest">Top Contributor</p>
                    @if($topRep)
                        <h4 class="text-xl font-black text-amber-700 flex items-center gap-2">
                            {{ $topRep->name }} 
                            <span class="text-xs bg-amber-200 text-amber-800 px-2 py-0.5 rounded-lg border border-amber-300">
                                {{ $topRep->news_count ?? collect($topRep->news)->count() ?? 0 }} Articles
                            </span>
                        </h4>
                    @else
                        <h4 class="text-lg font-bold text-amber-700">No Data Available</h4>
                    @endif
                </div>
            </div>
        </div>

        {{-- 🔥 NEW: Search & Filter Bar --}}
        <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm mb-6">
            <form action="{{ route('manage.reporters.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search reporter by name or email..." class="w-full border border-slate-200 rounded-xl p-3 pl-11 bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition-all text-sm font-bold text-slate-700">
                </div>
                <div class="w-full md:w-56">
                    <select name="sort" class="w-full border border-slate-200 rounded-xl p-3 bg-slate-50 outline-none focus:border-indigo-500 text-sm font-bold text-slate-700 cursor-pointer">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest Added</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest Added</option>
                        <option value="active" {{ request('sort') == 'active' ? 'selected' : '' }}>Most Active</option>
                    </select>
                </div>
                <button type="submit" class="bg-slate-800 text-white px-6 py-3 rounded-xl font-bold text-sm hover:bg-black transition-colors shadow-md">
                    Filter
                </button>
                @if(request()->has('search') || request()->has('sort'))
                    <a href="{{ route('manage.reporters.index') }}" class="bg-rose-50 text-rose-600 border border-rose-200 px-6 py-3 rounded-xl font-bold text-sm hover:bg-rose-100 text-center transition-colors">Clear</a>
                @endif
            </form>
        </div>

        {{-- Table Container --}}
        <div class="bg-white rounded-[2rem] shadow-xl shadow-slate-200/40 overflow-hidden border border-slate-100">
            @if($reporters->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse mobile-card-table">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-8 py-5 text-xs font-black uppercase text-slate-400 tracking-widest">Name & Email</th>
                            <th class="px-8 py-5 text-xs font-black uppercase text-slate-400 tracking-widest">Performance</th>
                            <th class="px-8 py-5 text-xs font-black uppercase text-slate-400 tracking-widest">Status & Date</th>
                            <th class="px-8 py-5 text-xs font-black uppercase text-slate-400 tracking-widest text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($reporters as $rep)
                            @php
                                $newsCount = $rep->news_count ?? collect($rep->news)->count() ?? 0;
                            @endphp
                            <tr class="hover:bg-indigo-50/30 transition-colors group">
                                <td class="px-8 py-6" data-label="Reporter">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 border border-indigo-200 flex items-center justify-center text-indigo-600 font-black text-xl shadow-sm relative">
                                            {{ mb_substr($rep->name, 0, 1) }}
                                            @if($topRep && $topRep->id == $rep->id)
                                                <div class="absolute -top-1 -right-1 w-5 h-5 bg-amber-400 rounded-full border-2 border-white flex items-center justify-center text-[10px] text-white shadow-sm" title="Top Reporter">⭐</div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-base">{{ $rep->name }}</div>
                                            <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5 mt-0.5">
                                                <i class="fa-regular fa-envelope text-slate-400"></i> {{ $rep->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-8 py-6" data-label="Performance">
                                    <div class="flex flex-col gap-1">
                                        <span class="text-xs font-black text-slate-500">Total News Submitted:</span>
                                        <span class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-lg text-sm font-black w-fit border border-indigo-100 shadow-sm flex items-center gap-1.5">
                                            <i class="fa-solid fa-newspaper text-indigo-400"></i> {{ $newsCount }} 
                                        </span>
                                    </div>
                                </td>

                                <td class="px-8 py-6" data-label="Date">
                                    <div class="flex flex-col gap-2">
                                        <span class="bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-md text-[10px] font-black uppercase tracking-wider w-fit border border-emerald-200">
                                            ● Active
                                        </span>
                                        <span class="text-xs font-bold text-slate-500 flex items-center gap-1.5">
                                            <i class="fa-regular fa-calendar-check text-slate-400"></i>
                                            {{ $rep->created_at->format('d M, Y') }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-8 py-6 text-center" data-label="Actions">
                                    <div class="flex items-center justify-center gap-2">
                                        {{-- 🔥 NEW: View Profile Button --}}
                                        <button onclick="openProfileModal('{{ $rep->name }}', '{{ $rep->email }}', '{{ $rep->created_at->format('d M, Y') }}', '{{ $newsCount }}')" 
                                                class="px-3 py-2 bg-indigo-50 text-indigo-600 rounded-xl font-black text-[11px] uppercase tracking-wider hover:bg-indigo-600 hover:text-white transition-all border border-indigo-100 shadow-sm flex items-center gap-1.5">
                                            <i class="fa-solid fa-id-card"></i> Profile
                                        </button>

                                        <form action="{{ route('manage.reporters.destroy', $rep->id) }}" method="POST" 
                                              onsubmit="return confirm('Are you sure you want to remove this reporter?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="px-3 py-2 bg-white text-rose-500 rounded-xl font-black text-[11px] uppercase tracking-wider hover:bg-rose-50 border border-slate-200 hover:border-rose-200 transition-all shadow-sm flex items-center gap-1.5" title="Remove Reporter">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            {{-- Empty State (If no reporters) --}}
            <div class="p-16 sm:p-24 text-center flex flex-col items-center justify-center">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6 border-8 border-white shadow-sm">
                    <i class="fa-solid fa-user-shield text-4xl text-slate-300"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">No Reporters Found!</h3>
                <p class="text-slate-500 font-medium mb-8 max-w-sm">No reporters added yet or none match your search criteria.</p>
                
                <button onclick="openModal()" class="bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 px-6 py-2.5 rounded-xl font-bold transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-plus"></i> Add First Reporter
                </button>
            </div>
            @endif
        </div>
        
        {{-- Pagination --}}
        @if(method_exists($reporters, 'hasPages') && $reporters->hasPages())
            <div class="mt-6">
                {{ $reporters->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Reporter Profile Modal --}}
<div id="profileModal" class="fixed inset-0 bg-slate-900/60 hidden items-center justify-center z-[110] backdrop-blur-sm px-4 transition-opacity duration-300 opacity-0">
    <div id="profileModalContent" class="bg-white rounded-[2rem] w-full max-w-sm shadow-2xl overflow-hidden modal-enter border border-slate-200 relative">
        <button onclick="closeProfileModal()" class="absolute top-4 right-4 w-8 h-8 bg-white/20 hover:bg-white/40 backdrop-blur-md rounded-full flex items-center justify-center text-white transition-colors z-20"><i class="fa-solid fa-xmark"></i></button>
        
        <div class="bg-gradient-to-br from-indigo-600 to-purple-700 px-6 pt-10 pb-6 text-center relative">
            <div class="w-24 h-24 mx-auto bg-white rounded-full p-1 shadow-lg relative mb-4">
                <div class="w-full h-full bg-slate-100 rounded-full flex items-center justify-center text-3xl font-black text-indigo-600" id="profAvatar">A</div>
                <div class="absolute bottom-0 right-2 w-5 h-5 bg-emerald-500 border-2 border-white rounded-full"></div>
            </div>
            <h3 class="text-2xl font-black text-white" id="profName">Name</h3>
            <p class="text-indigo-100 text-sm mt-1" id="profEmail">Email</p>
            <span class="inline-block mt-3 px-3 py-1 bg-white/20 text-white rounded-full text-[10px] font-black uppercase tracking-widest border border-white/30 backdrop-blur-sm">News Reporter</span>
        </div>
        
        <div class="p-6 bg-slate-50">
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                    <i class="fa-solid fa-newspaper text-2xl text-indigo-500 mb-2"></i>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total News</p>
                    <h4 class="text-xl font-black text-slate-800 mt-1" id="profNews">0</h4>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm text-center">
                    <i class="fa-solid fa-calendar-days text-2xl text-emerald-500 mb-2"></i>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Joined Date</p>
                    <h4 class="text-sm font-black text-slate-800 mt-2" id="profDate">Date</h4>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Add Reporter Modal --}}
<div id="modalOverlay" class="fixed inset-0 bg-slate-900/60 hidden items-center justify-center z-[100] backdrop-blur-sm px-4 transition-opacity duration-300 opacity-0">
    <div id="addReporterModal" class="bg-white rounded-[2rem] w-full max-w-md shadow-2xl overflow-hidden modal-enter p-1">
        
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-8 rounded-t-[1.8rem] text-center relative overflow-hidden">
            <div class="absolute -top-4 -right-4 p-4 opacity-10 transform rotate-12">
                <i class="fa-solid fa-id-badge text-8xl text-white"></i>
            </div>
            <h3 class="text-2xl font-black text-white relative z-10">Add New Reporter</h3>
            <p class="text-indigo-100 text-xs mt-2 relative z-10 uppercase tracking-widest font-bold">Fill out account information</p>
        </div>

        <form action="{{ route('manage.reporters.store') }}" method="POST" class="p-8 space-y-5">
            @csrf
            <div class="space-y-4">
                <div class="group">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Full Name <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="name" placeholder="e.g. John Doe" required 
                               class="w-full border-2 border-slate-100 rounded-2xl p-3.5 pl-11 bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition-all font-bold text-slate-700">
                    </div>
                </div>

                <div class="group">
                    <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest mb-2 ml-1">Email Address <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" placeholder="example@mail.com" required 
                               class="w-full border-2 border-slate-100 rounded-2xl p-3.5 pl-11 bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition-all font-bold text-slate-700">
                    </div>
                </div>

                <div class="group">
                    <div class="flex justify-between items-center mb-2 ml-1">
                        <label class="block text-[11px] font-black text-slate-500 uppercase tracking-widest">Password <span class="text-rose-500">*</span></label>
                        <span id="passError" class="text-[10px] text-rose-500 font-bold hidden">Must be at least 8 characters</span>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="passwordInput" placeholder="••••••••" required minlength="8"
                               class="w-full border-2 border-slate-100 rounded-2xl p-3.5 pl-11 pr-12 bg-slate-50 outline-none focus:border-indigo-500 focus:bg-white transition-all font-bold text-slate-700">
                        
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-indigo-600 transition-colors">
                            <i class="fa-solid fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-slate-100 mt-6">
                <button type="button" onclick="closeModal()" 
                        class="order-2 sm:order-1 w-full sm:w-auto px-6 py-3 text-slate-500 font-bold hover:bg-slate-100 rounded-xl transition-all">
                    Cancel
                </button>
                <button type="submit" id="submitBtn"
                        class="order-1 sm:order-2 w-full sm:w-auto bg-indigo-600 text-white px-8 py-3.5 rounded-2xl font-black text-sm shadow-lg shadow-indigo-200 hover:bg-indigo-700 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check"></i> Create Account
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const overlay = document.getElementById('modalOverlay');
    const modal = document.getElementById('addReporterModal');
    const passInput = document.getElementById('passwordInput');
    const submitBtn = document.getElementById('submitBtn');
    const passError = document.getElementById('passError');

    // Add Reporter Modal Handlers
    function openModal() {
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        void overlay.offsetWidth;
        overlay.classList.remove('opacity-0');
        modal.classList.add('modal-enter-active');
    }

    function closeModal() {
        overlay.classList.add('opacity-0');
        modal.classList.remove('modal-enter-active');
        setTimeout(() => { overlay.classList.add('hidden'); overlay.classList.remove('flex'); }, 300);
    }

    overlay.addEventListener('click', function(e) { if (e.target === overlay) closeModal(); });

    // Password Logic
    function togglePassword() {
        const icon = document.getElementById('toggleIcon');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    passInput.addEventListener('input', function() {
        if (this.value.length > 0 && this.value.length < 8) {
            this.classList.replace('focus:border-indigo-500', 'focus:border-rose-500');
            this.classList.replace('border-slate-100', 'border-rose-300');
            passError.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            this.classList.replace('focus:border-rose-500', 'focus:border-indigo-500');
            this.classList.replace('border-rose-300', 'border-slate-100');
            passError.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    });

    // 🔥 NEW: Profile Modal Logic
    const profileOverlay = document.getElementById('profileModal');
    const profileContent = document.getElementById('profileModalContent');

    function openProfileModal(name, email, date, news) {
        document.getElementById('profName').innerText = name;
        document.getElementById('profEmail').innerText = email;
        document.getElementById('profDate').innerText = date;
        document.getElementById('profNews').innerText = news;
        document.getElementById('profAvatar').innerText = name.charAt(0);

        profileOverlay.classList.remove('hidden');
        profileOverlay.classList.add('flex');
        void profileOverlay.offsetWidth;
        profileOverlay.classList.remove('opacity-0');
        profileContent.classList.add('modal-enter-active');
    }

    function closeProfileModal() {
        profileOverlay.classList.add('opacity-0');
        profileContent.classList.remove('modal-enter-active');
        setTimeout(() => { profileOverlay.classList.add('hidden'); profileOverlay.classList.remove('flex'); }, 300);
    }

    profileOverlay.addEventListener('click', function(e) { if (e.target === profileOverlay) closeProfileModal(); });
</script>
@endsection