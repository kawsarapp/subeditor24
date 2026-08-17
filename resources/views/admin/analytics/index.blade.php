@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center justify-center md:justify-start gap-2">
                📊 Analytics & ROI Dashboard
            </h1>
            <p class="text-gray-500 mt-1">আপনার স্টাফ এবং সাব-এডিটরদের কাজের বিস্তারিত রিপোর্ট।</p>
        </div>
        <div>
            <form id="days-filter-form" action="{{ route('admin.analytics.index') }}" method="GET" class="flex items-center gap-2">
                <label for="days" class="text-sm font-medium text-gray-700">Filter:</label>
                <select name="days" id="days" onchange="document.getElementById('days-filter-form').submit()" 
                        class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="15" {{ $days == 15 ? 'selected' : '' }}>Last 15 Days</option>
                    <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                    <option value="60" {{ $days == 60 ? 'selected' : '' }}>Last 60 Days</option>
                </select>
            </form>
        </div>
    </div>

    <!-- ROI Overview -->
    <div class="bg-gradient-to-br from-green-500 to-teal-600 rounded-2xl shadow-xl p-6 text-white mb-8 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 opacity-10">
            <svg class="w-48 h-48" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path><path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path></svg>
        </div>
        <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex-1">
                <h2 class="text-xl font-bold mb-2">💰 Estimated Savings & ROI (Last {{ $days }} Days)</h2>
                <div class="flex flex-wrap gap-4 mt-4">
                    <div class="bg-white bg-opacity-20 rounded-lg p-4 flex-1 min-w-[200px]">
                        <p class="text-sm uppercase tracking-wider font-semibold opacity-90">Time Saved</p>
                        <p class="text-3xl font-bold">{{ $timeSavedHours }} <span class="text-lg font-normal">Hours</span></p>
                        <p class="text-xs mt-1 opacity-80">(News: {{ $newsMinutes }}m, Card: {{ $cardMinutes }}m)</p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-lg p-4 flex-1 min-w-[200px]">
                        <p class="text-sm uppercase tracking-wider font-semibold opacity-90">Cost Saved</p>
                        <p class="text-3xl font-bold">BDT {{ number_format($costSaved) }}</p>
                        <p class="text-xs mt-1 opacity-80">Based on BDT {{ $hourlyRate }}/hr rate</p>
                    </div>
                </div>
            </div>
            @if(auth()->user()->role === 'super_admin')
            <div class="text-center bg-white bg-opacity-10 p-4 rounded-xl border border-white border-opacity-20">
                 <p class="text-xs mb-2">Configure Rates</p>
                 <a href="{{ route('settings.index') }}" class="bg-white text-green-700 hover:bg-gray-100 font-bold py-2 px-4 rounded shadow transition text-sm">
                     ROI Settings
                 </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Analytics Chart -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4">📊 Daily Trend Analysis</h2>
        <div class="relative h-72 w-full">
            <canvas id="analyticsChart"></canvas>
        </div>
    </div>

    <!-- Summary Stats -->
    <h2 class="text-xl font-bold text-gray-800 mb-4">📈 Overall Pipeline Activities</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Auto Scraped</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->scraped ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">AI Rewritten</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->ai_rewritten ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-orange-100 text-orange-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Manual / Custom</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->manual ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Reporter Posts</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->reporter_submitted ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Media & Social Stats -->
    <h2 class="text-xl font-bold text-gray-800 mb-4">🎨 Photo Cards & Social Media</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-pink-100 text-pink-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Cards Created</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->cards_created ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Local Downloads</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->card_downloads ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-blue-50 text-blue-500">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Facebook Posts</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->fb_posts ?? 0 }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
            <div class="p-3 rounded-full bg-sky-50 text-sky-500">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 2.193-1.077 7.291-1.536 9.616-.194.982-.544 1.309-.882 1.34-.763.069-1.341-.5-2.083-.984-1.161-.758-1.819-1.226-2.95-1.972-1.3-.864-.457-1.34.28-2.106.192-.2.353-3.08-3.111-5.111-.069-.044-.047-.11.026-.145.059-.029 1.078-.344 3.011 1.758 4.298-2.915 5.864-4.041 6.33-4.381.109-.08.21-.122.3-.086.088.034.139.124.126.236-.013.112-.511.079-5.32 4.471-.059.053-.19.167-.403.355l.835 2.193c.123.339.231.543.327.561.106.02.215-.027.324-.136l1.371-1.34 2.871 2.126c.451.249.771.121.88-.415.14-.68.647-3.702.946-6.025.106-.827-.14-1.421-.59-1.637-.411-.197-1.144-.22-2.164-.092z"/></svg>
            </div>
            <div>
                <p class="text-sm font-medium text-gray-500">Telegram Posts</p>
                <p class="text-2xl font-bold text-gray-800">{{ $newsStats->tg_posts ?? 0 }}</p>
            </div>
        </div>
    </div>

    <!-- Staff Breakdown Table -->
    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
        <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        Staff & Sub-Editor Performance
    </h2>
    <div class="bg-white shadow-sm border border-gray-200 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">User Name</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Scraped</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Manual</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">AI Rewritten</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Cards Created</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Card Downloads</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($staffBreakdown as $staff)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold">
                                    {{ substr($staff->name, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $staff->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $staff->scraped > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $staff->scraped }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700 font-medium">
                            {{ $staff->manual }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700 font-medium">
                            {{ $staff->ai_rewritten }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $staff->cards_created > 0 ? 'bg-pink-100 text-pink-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $staff->cards_created }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $staff->card_downloads > 0 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $staff->card_downloads }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('analyticsChart')?.getContext('2d');
        if (!ctx) return;
        
        const data = @json($chartTrendData ?? []);
        if (!data || !data.labels) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Auto Scraped',
                        data: data.scraped,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Manual News',
                        data: data.manual,
                        borderColor: '#f97316',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4
                    },
                    {
                        label: 'AI Rewritten',
                        data: data.ai,
                        borderColor: '#a855f7',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4
                    },
                    {
                        label: 'Cards Created',
                        data: data.cards,
                        borderColor: '#ec4899',
                        backgroundColor: 'transparent',
                        borderWidth: 2,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { usePointStyle: true, boxWidth: 8, font: { family: "'Plus Jakarta Sans', sans-serif" } }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { family: "'Plus Jakarta Sans', sans-serif", size: 13 },
                        bodyFont: { family: "'Plus Jakarta Sans', sans-serif", size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        grid: { display: true, color: '#f1f5f9' },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif" } }
                    },
                    x: { 
                        grid: { display: false },
                        ticks: { font: { family: "'Plus Jakarta Sans', sans-serif" } }
                    }
                }
            }
        });
    });
</script>

@endsection
