@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto py-10 px-4">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">🎨 Template Manager</h1>
            <p class="text-sm text-gray-500 mt-1">Dashboard থেকে নতুন Studio template যোগ ও ম্যানেজ করুন</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('settings.index') }}" class="text-sm text-gray-500 hover:text-gray-800 border border-gray-300 px-4 py-2 rounded-lg transition">← Settings</a>
            <a href="{{ route('admin.templates.create') }}" class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-bold text-sm hover:bg-indigo-700 transition shadow-sm">
                + নতুন Template
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-5 flex items-center gap-2">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-5">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Info box --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-800">
        <strong>📌 কীভাবে কাজ করে:</strong>
        এখানে template যোগ করলে Studio পেজে automatically দেখাবে।
        <strong>Frame URL</strong> = আসল blank frame PNG (1080×1080px) |
        <strong>Thumbnail</strong> = sidebar preview ছবি |
        Position values সব 1080×1080 canvas এর coordinate অনুযায়ী।
    </div>

    @if($templates->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-16 text-center">
            <p class="text-5xl mb-4">🖼️</p>
            <p class="text-gray-500 font-medium">এখনো কোনো DB template নেই।</p>
            <a href="{{ route('admin.templates.create') }}" class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2 rounded-lg font-bold text-sm hover:bg-indigo-700 transition">+ প্রথম template যোগ করুন</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($templates as $template)
                <div class="bg-white rounded-2xl border {{ $template->is_active ? 'border-gray-200' : 'border-red-100' }} shadow-sm overflow-hidden hover:shadow-md transition group">
                    {{-- Thumbnail --}}
                    <div class="relative bg-gray-100 h-44 flex items-center justify-center overflow-hidden">
                        @if($template->thumbnail_url)
                            <img src="{{ $template->thumbnail_url }}" alt="{{ $template->name }}"
                                 class="w-full h-full object-contain" loading="lazy"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                            <div class="hidden w-full h-full items-center justify-center text-gray-300 text-4xl">🖼️</div>
                        @else
                            <div class="text-gray-300 text-4xl">🖼️</div>
                        @endif
                        <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-1 rounded-full {{ $template->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $template->is_active ? '✅ Active' : '⏸ Inactive' }}
                        </span>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-gray-800 truncate">{{ $template->name }}</h3>
                        <p class="text-[10px] text-gray-400 mt-0.5 truncate" title="{{ $template->frame_url }}">
                            🔗 {{ $template->frame_url }}
                        </p>

                        {{-- Layout Data preview --}}
                        @php $ld = $template->layout_data ?? []; @endphp
                        <div class="mt-2 bg-gray-50 rounded-lg p-2 text-[10px] font-mono text-gray-500 space-y-0.5">
                            <div>📝 Title: top={{ $ld['title']['top'] ?? '?' }}, left={{ $ld['title']['left'] ?? '?' }}, fs={{ $ld['title']['fontSize'] ?? '60' }}</div>
                            <div>📅 Date: top={{ $ld['date']['top'] ?? '?' }}, left={{ $ld['date']['left'] ?? '?' }}</div>
                            <div>🖼️ Image: top={{ $ld['image']['top'] ?? '?' }}, {{ $ld['image']['width'] ?? '?' }}×{{ $ld['image']['height'] ?? '?' }}</div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 mt-4">
                            <a href="{{ route('admin.templates.edit', $template->id) }}"
                               class="flex-1 text-center text-xs font-bold border border-indigo-300 text-indigo-600 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                                ✏️ Edit
                            </a>
                            <form action="{{ route('admin.templates.toggle', $template->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="w-full text-xs font-bold border py-1.5 rounded-lg transition {{ $template->is_active ? 'border-amber-300 text-amber-600 hover:bg-amber-50' : 'border-green-300 text-green-600 hover:bg-green-50' }}">
                                    {{ $template->is_active ? '⏸ Deactivate' : '▶ Activate' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.templates.destroy', $template->id) }}" method="POST"
                                  onsubmit="return confirm('Delete করতে চান?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-bold border border-red-200 text-red-500 py-1.5 px-2.5 rounded-lg hover:bg-red-50 transition">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection