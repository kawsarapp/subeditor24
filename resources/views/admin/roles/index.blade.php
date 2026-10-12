@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-slate-50 min-h-screen">

    {{-- Breadcrumb & Title Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-bold text-slate-500 mb-1">
                <a href="{{ route('admin.dashboard') }}" class="hover:text-indigo-600">Admin</a>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
                <span class="text-indigo-600">Roles & Permissions</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                <i class="fa-solid fa-user-shield text-indigo-600"></i>
                রোলস ও পারমিশন ম্যাট্রিক্স (RBAC Matrix)
            </h1>
            <p class="text-sm text-slate-600 mt-1">সফটওয়্যারের বিভিন্ন রোলের ডিফল্ট পারমিশন নির্ধারণ ও মডিউল নিয়ন্ত্রণ করুন</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-white border border-slate-200 text-slate-700 font-bold text-xs rounded-xl hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> অ্যাডমিন ড্যাশবোর্ড
            </a>
            <a href="{{ route('client.staff.index') }}" class="px-4 py-2 bg-indigo-600 text-white font-bold text-xs rounded-xl hover:bg-indigo-700 transition shadow-md shadow-indigo-600/20 flex items-center gap-1.5">
                <i class="fa-solid fa-users"></i> স্টাফ ম্যানেজমেন্ট
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 text-sm font-bold flex items-center justify-between shadow-xs animate-in fade-in">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 text-lg">&times;</button>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 border-l-4 border-rose-500 rounded-r-xl text-rose-800 text-sm font-bold flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 text-lg">&times;</button>
    </div>
    @endif

    {{-- Stats Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        @foreach($roles as $rKey => $rData)
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs hover:shadow-md transition">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-black uppercase tracking-wider text-slate-500">
                    {{ $rData['name'] }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase tracking-wide
                    {{ match($rKey) {
                        'editor' => 'bg-indigo-100 text-indigo-800 border border-indigo-200',
                        'sub_editor' => 'bg-blue-100 text-blue-800 border border-blue-200',
                        'staff' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                        'reporter' => 'bg-amber-100 text-amber-800 border border-amber-200',
                        default => 'bg-slate-100 text-slate-700'
                    } }}">
                    {{ count($rData['resolved_permissions']) }} Perms
                </span>
            </div>
            <h3 class="font-black text-slate-900 text-sm leading-tight mb-1">{{ $rData['display_name'] }}</h3>
            <p class="text-[11px] text-slate-500 line-clamp-2 mb-3">{{ $rData['description'] }}</p>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs">
                <span class="text-slate-500">মোট একাউন্ট:</span>
                <span class="font-black text-slate-800">{{ $userCounts[$rKey] ?? 0 }} জন</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Main Roles Matrix & Editor --}}
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
        
        {{-- Table Header / Toolbar --}}
        <div class="p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-50/60">
            <div>
                <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
                    <i class="fa-solid fa-table-cells text-indigo-600"></i>
                    পারমিশন ম্যাট্রিক্স গ্রিড (Permission Matrix Grid)
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">প্রতিটি রোলের জন্য পারমিশন চেক/আনচেক করে নিচের সংশ্লিষ্ট রোল কার্ড থেকে সংরক্ষণ করুন</p>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="flex items-center gap-1.5 font-bold text-slate-600">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> অ্যাক্টিভ
                </span>
                <span class="flex items-center gap-1.5 font-bold text-slate-400 ml-3">
                    <span class="w-3 h-3 rounded-full bg-slate-200 inline-block"></span> অননুমোদিত
                </span>
            </div>
        </div>

        {{-- Desktop Matrix Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-100/80 text-[11px] font-black uppercase tracking-wider text-slate-700 border-b border-slate-200">
                        <th class="py-3.5 px-6 min-w-[260px]">মডিউল / ফিচার নাম</th>
                        <th class="py-3.5 px-4 text-center min-w-[130px]">
                            <span class="text-indigo-700">👑 Editor</span>
                        </th>
                        <th class="py-3.5 px-4 text-center min-w-[130px]">
                            <span class="text-blue-700">📝 Sub-Editor</span>
                        </th>
                        <th class="py-3.5 px-4 text-center min-w-[130px]">
                            <span class="text-emerald-700">👥 Staff</span>
                        </th>
                        <th class="py-3.5 px-4 text-center min-w-[130px]">
                            <span class="text-amber-700">🎙️ Reporter</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach($categories as $catKey => $catData)
                        {{-- Category Banner Row --}}
                        <tr class="bg-slate-50/90 font-black text-slate-800 border-t-2 border-slate-200">
                            <td colspan="5" class="py-2.5 px-6">
                                <span class="flex items-center gap-2 text-indigo-900 font-extrabold uppercase tracking-wider text-[11px]">
                                    <i class="{{ $catData['icon'] }} text-indigo-500"></i>
                                    {{ $catData['name'] }} ({{ $catKey }})
                                </span>
                            </td>
                        </tr>

                        @foreach($catData['permissions'] as $pKey => $pLabel)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="py-3 px-6">
                                <div class="font-bold text-slate-800">{{ $pLabel }}</div>
                                <code class="text-[10px] text-slate-400 bg-slate-100 px-1 py-0.5 rounded">{{ $pKey }}</code>
                            </td>
                            @foreach(['editor', 'sub_editor', 'staff', 'reporter'] as $rKey)
                                @php
                                    $hasPerm = in_array($pKey, $roles[$rKey]['resolved_permissions']);
                                @endphp
                                <td class="py-3 px-4 text-center">
                                    @if($hasPerm)
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 shadow-xs">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-300">
                                            <i class="fa-solid fa-minus text-xs"></i>
                                        </span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Interactive Role Editors (Manage Default Permissions) --}}
    <div class="mb-8">
        <div class="mb-4">
            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-indigo-600"></i>
                রোলের ডিফল্ট পারমিশন সম্পাদনা (Role Configuration Forms)
            </h2>
            <p class="text-xs text-slate-600">নিচের প্রতিটি রোলের কার্ড থেকে চেকবক্স পরিবর্তন করে 'সংরক্ষণ করুন' বাটনে ক্লিক করুন</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($roles as $rKey => $rData)
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden flex flex-col">
                {{-- Role Card Header --}}
                <div class="p-5 border-b border-slate-100 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-300">{{ $rKey }}</span>
                        <h3 class="font-black text-sm text-white">{{ $rData['display_name'] }}</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-black bg-white/10 text-white border border-white/20">
                        {{ count($rData['resolved_permissions']) }} টি চালু
                    </span>
                </div>

                <form action="{{ route('admin.roles.update-permissions', $rKey) }}" method="POST" class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    @csrf

                    <div class="space-y-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                        @foreach($categories as $catKey => $catData)
                        <div class="border border-slate-100 rounded-xl p-3 bg-slate-50/50">
                            <span class="block text-[11px] font-black uppercase text-slate-500 mb-2">
                                {{ $catData['name'] }}
                            </span>
                            <div class="space-y-1.5">
                                @foreach($catData['permissions'] as $pKey => $pLabel)
                                    @php
                                        $isChecked = in_array($pKey, $rData['resolved_permissions']);
                                    @endphp
                                    <label class="flex items-center space-x-2.5 p-1.5 rounded-lg text-xs cursor-pointer hover:bg-white transition border {{ $isChecked ? 'border-indigo-100 bg-white' : 'border-transparent' }}">
                                        <input type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $pKey }}" 
                                               {{ $isChecked ? 'checked' : '' }}
                                               class="form-checkbox text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                                        <span class="font-bold text-slate-700 text-[11px] select-none">{{ $pLabel }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <label class="flex items-center space-x-2 text-xs font-bold text-slate-600 cursor-pointer">
                            <input type="checkbox" name="sync_existing" value="1" class="form-checkbox text-indigo-600 rounded">
                            <span>এই রোলের বিদ্যমান সকল ইউজারের পারমিশনও ওভাররাইট করুন</span>
                        </label>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2.5 rounded-xl font-black text-xs shadow-md shadow-indigo-600/20 transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i>
                            {{ $rData['display_name'] }} পারমিশন সংরক্ষণ করুন
                        </button>
                    </div>
                </form>
            </div>
            @endforeach
        </div>
    </div>

</div>
@endsection
