@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 bg-gray-100 min-h-screen">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">📊 স্ক্র্যাপার মনিটর ড্যাশবোর্ড</h1>
            <p class="text-slate-500 mt-1">সব সোর্সের লাইভ স্ক্র্যাপিং পারফরম্যান্স এবং এরর ট্র্যাকিং</p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <span class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md">
                Super Admin Only
            </span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition">
            <div class="p-4 bg-blue-50 text-blue-600 rounded-xl text-2xl">🔄</div>
            <div>
                <p class="text-slate-500 text-[10px] font-black uppercase tracking-wider">মোট রান (২৪ ঘণ্টা)</p>
                <h3 class="text-3xl font-bold text-slate-800">{{ $totalRunsToday }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition">
            <div class="p-4 bg-green-50 text-green-600 rounded-xl text-2xl">✅</div>
            <div>
                <p class="text-slate-500 text-[10px] font-black uppercase tracking-wider">সফল স্ক্র্যাপ</p>
                <h3 class="text-3xl font-bold text-slate-800 text-green-600">{{ $successRunsToday }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition">
            <div class="p-4 bg-rose-50 text-rose-600 rounded-xl text-2xl">❌</div>
            <div>
                <p class="text-slate-500 text-[10px] font-black uppercase tracking-wider">ব্যর্থ স্ক্র্যাপ</p>
                <h3 class="text-3xl font-bold text-slate-800 text-rose-600">{{ $failedRunsToday }}</h3>
            </div>
        </div>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 flex items-center gap-4 hover:shadow-md transition">
            <div class="p-4 bg-indigo-50 text-indigo-600 rounded-xl text-2xl">📈</div>
            <div>
                <p class="text-slate-500 text-[10px] font-black uppercase tracking-wider">সাকসেস রেট</p>
                <h3 class="text-3xl font-bold text-slate-800 text-indigo-600">{{ $globalSuccessRate }}%</h3>
            </div>
        </div>
    </div>

    {{-- Sources Performance List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden mb-10">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-700">🌐 স্ক্র্যাপার সোর্স পারফরম্যান্স</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold">সোর্সের নাম ও ইউআরএল</th>
                        <th class="px-6 py-4 font-bold text-center">স্ট্যাটাস (৩ ঘণ্টা)</th>
                        <th class="px-6 py-4 font-bold">২৪ ঘণ্টা সাকসেস রেট</th>
                        <th class="px-6 py-4 font-bold text-center">মোট রান</th>
                        <th class="px-6 py-4 font-bold text-center">ব্যর্থ</th>
                        <th class="px-6 py-4 font-bold">শেষ সফল রান</th>
                        <th class="px-6 py-4 font-bold">শেষ চেষ্টার সময়</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($websiteStats as $stat)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800">{{ $stat['website']->name }}</div>
                            <div class="text-xs text-slate-500"><a href="{{ $stat['website']->url }}" target="_blank" class="hover:underline text-indigo-600">{{ $stat['website']->url }}</a></div>
                        </td>

                        <td class="px-6 py-4 text-center">
                            @if($stat['status'] === 'active')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-green-50 text-green-700 border border-green-200">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Active
                                </span>
                            @elseif($stat['status'] === 'failing')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-rose-50 text-rose-700 border border-rose-200">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> Failing
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Inactive
                                </span>
                            @endif
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-24 bg-slate-100 rounded-full h-2 overflow-hidden border border-slate-200">
                                    <div class="h-2 rounded-full {{ $stat['success_rate_24h'] > 85 ? 'bg-green-500' : ($stat['success_rate_24h'] > 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $stat['success_rate_24h'] }}%"></div>
                                </div>
                                <span class="text-sm font-bold {{ $stat['success_rate_24h'] > 85 ? 'text-green-600' : ($stat['success_rate_24h'] > 50 ? 'text-amber-600' : 'text-red-600') }}">
                                    {{ $stat['success_rate_24h'] }}%
                                </span>
                            </div>
                        </td>

                        <td class="px-6 py-4 text-center font-bold text-slate-700">
                            {{ $stat['total_runs_24h'] }}
                        </td>

                        <td class="px-6 py-4 text-center font-bold text-rose-600">
                            {{ $stat['failed_runs_24h'] }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-600">
                            {{ $stat['last_scraped_at'] ? \Carbon\Carbon::parse($stat['last_scraped_at'])->timezone('Asia/Dhaka')->format('d M h:i A') : 'Never' }}
                        </td>

                        <td class="px-6 py-4 text-sm text-slate-500">
                            {{ $stat['last_attempt_at'] ? \Carbon\Carbon::parse($stat['last_attempt_at'])->timezone('Asia/Dhaka')->format('d M h:i A') : 'Never' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Error Log / Failed Jobs Details --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-700">❌ ব্যর্থ স্ক্র্যাপ জবগুলোর বিস্তারিত এরর লগ (Recent Scraper Failures)</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-bold">সোর্স</th>
                        <th class="px-6 py-4 font-bold">টাইপ</th>
                        <th class="px-6 py-4 font-bold">টার্গেট ইউআরএল</th>
                        <th class="px-6 py-4 font-bold text-center">HTTP কোড</th>
                        <th class="px-6 py-4 font-bold">ব্যবহৃত স্ট্র্যাটেজি</th>
                        <th class="px-6 py-4 font-bold">ব্যর্থতার কারণ</th>
                        <th class="px-6 py-4 font-bold">সময়</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($failedLogs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-800">
                            {{ $log->website->name ?? 'Deleted Source' }}
                        </td>

                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider {{ $log->job_type === 'list' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $log->job_type }}
                            </span>
                        </td>

                        <td class="px-6 py-4 text-xs text-slate-600 font-mono truncate max-w-xs" title="{{ $log->url }}">
                            <a href="{{ $log->url }}" target="_blank" class="hover:underline text-indigo-600">{{ $log->url }}</a>
                        </td>

                        <td class="px-6 py-4 text-center font-mono font-bold text-rose-600">
                            {{ $log->http_status ?? 'N/A' }}
                        </td>

                        <td class="px-6 py-4 text-xs font-bold text-slate-700">
                            {{ $log->strategy ?? 'None' }}
                            @if($log->retry_count > 0)
                                <span class="bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded text-[9px]">Retry: {{ $log->retry_count }}</span>
                            @endif
                        </td>

                        <td class="px-6 py-4 text-xs text-rose-700 font-medium whitespace-pre-wrap max-w-md">
                            {{ $log->error_message }}
                        </td>

                        <td class="px-6 py-4 text-xs text-slate-500 font-medium">
                            {{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Dhaka')->format('d M, h:i:s A') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-slate-400 font-medium">
                            কোনো ব্যর্থ স্ক্র্যাপ রেকর্ড পাওয়া যায়নি।
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($failedLogs->hasPages())
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200">
            {{ $failedLogs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
