@extends('layouts.app')

@section('title', 'Subscription Orders Management - Super Admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-8 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- TOP HEADER --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800/80 pb-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full bg-indigo-500 animate-pulse"></span>
                    <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">সাবস্ক্রিপশন ও বিলিং অর্ডার ম্যানেজমেন্ট</h1>
                </div>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    গ্রাহকদের ম্যানুয়াল পেমেন্ট অর্ডার যাচাই করুন এবং ১-ক্লিকে স্বয়ংক্রিয়ভাবে প্ল্যান সক্রিয় করুন।
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.billing.payment-settings') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-indigo-400 hover:text-indigo-300 border border-slate-800 text-xs font-bold px-4 py-2.5 rounded-xl transition">
                    <i class="fa-solid fa-gear"></i>
                    <span>পেমেন্ট অ্যাকাউন্ট সেটিংস</span>
                </a>
                <a href="{{ route('admin.pricing.index') }}" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 text-xs font-bold px-4 py-2.5 rounded-xl transition">
                    <i class="fa-solid fa-tags"></i>
                    <span>প্ল্যান ম্যানেজার</span>
                </a>
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/70 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @if(session('info'))
            <div class="p-4 rounded-2xl bg-indigo-950/70 border border-indigo-800 text-indigo-300 text-xs sm:text-sm flex items-center gap-3">
                <i class="fa-solid fa-info-circle text-indigo-400 text-lg shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        {{-- STATS CARDS --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-900/80 border border-amber-500/30 rounded-2xl p-5 space-y-1 relative overflow-hidden">
                <div class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center justify-between">
                    <span>পেন্ডিং অর্ডার</span>
                    <i class="fa-solid fa-clock text-amber-400 text-sm"></i>
                </div>
                <div class="text-3xl font-black text-white font-mono mt-2">{{ $stats['pending_count'] }}</div>
                <div class="text-[11px] text-amber-400/80 font-mono">৳{{ number_format($stats['pending_revenue'], 2) }} অপেক্ষায়</div>
            </div>

            <div class="bg-slate-900/80 border border-emerald-500/30 rounded-2xl p-5 space-y-1 relative overflow-hidden">
                <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider flex items-center justify-between">
                    <span>অনুমোদিত অর্ডার</span>
                    <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                </div>
                <div class="text-3xl font-black text-white font-mono mt-2">{{ $stats['approved_count'] }}</div>
                <div class="text-[11px] text-emerald-400/80 font-mono">সফলভাবে অ্যাক্টিভেটেড</div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-1">
                <div class="text-xs font-bold text-indigo-400 uppercase tracking-wider flex items-center justify-between">
                    <span>মোট সংগৃহীত আয়</span>
                    <i class="fa-solid fa-money-bill-trend-up text-indigo-400 text-sm"></i>
                </div>
                <div class="text-3xl font-black text-white font-mono mt-2">৳{{ number_format($stats['total_revenue'], 2) }}</div>
                <div class="text-[11px] text-slate-400">অনুমোদিত সকল অর্ডার থেকে</div>
            </div>

            <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 space-y-1">
                <div class="text-xs font-bold text-purple-400 uppercase tracking-wider flex items-center justify-between">
                    <span>মোট অর্ডার সংখ্যা</span>
                    <i class="fa-solid fa-receipt text-purple-400 text-sm"></i>
                </div>
                <div class="text-3xl font-black text-white font-mono mt-2">{{ $orders->total() }}</div>
                <div class="text-[11px] text-slate-400">সর্বমোট জমাকৃত আবেদন</div>
            </div>
        </div>

        {{-- FILTER TABS & SEARCH BAR --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-900/70 border border-slate-800 rounded-2xl p-3">
            {{-- Tabs --}}
            <div class="flex items-center gap-1.5 overflow-x-auto">
                <a href="{{ route('admin.billing.orders', ['status' => 'all', 'search' => $search]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition {{ $status === 'all' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    সকল অর্ডার
                </a>
                <a href="{{ route('admin.billing.orders', ['status' => 'pending', 'search' => $search]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition flex items-center gap-1.5 {{ $status === 'pending' ? 'bg-amber-500 text-slate-950 font-black shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <span>পেন্ডিং</span>
                    @if($stats['pending_count'] > 0)
                        <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                    @endif
                </a>
                <a href="{{ route('admin.billing.orders', ['status' => 'approved', 'search' => $search]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition {{ $status === 'approved' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    অনুমোদিত
                </a>
                <a href="{{ route('admin.billing.orders', ['status' => 'rejected', 'search' => $search]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-extrabold transition {{ $status === 'rejected' ? 'bg-rose-600 text-white shadow-md' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    বাতিলকৃত
                </a>
            </div>

            {{-- Search Form --}}
            <form action="{{ route('admin.billing.orders') }}" method="GET" class="flex items-center gap-2">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="অর্ডার নং, TrxID, নাম, ইমেইল..." class="bg-slate-950 border border-slate-800 rounded-xl pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500 w-56 sm:w-64">
                </div>
                @if($search)
                    <a href="{{ route('admin.billing.orders', ['status' => $status]) }}" class="text-slate-400 hover:text-white text-xs px-2">Reset</a>
                @endif
            </form>
        </div>

        {{-- ORDERS TABLE --}}
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 shadow-2xl overflow-hidden">
            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-950/80 text-slate-400 uppercase font-black tracking-wider text-[10px] border-b border-slate-800">
                            <tr>
                                <th class="py-3 px-4">অর্ডার নং ও তারিখ</th>
                                <th class="py-3 px-4">গ্রাহকের তথ্য</th>
                                <th class="py-3 px-4">প্যাকেজ ও মেয়াদ</th>
                                <th class="py-3 px-4">টাকার পরিমাণ</th>
                                <th class="py-3 px-4">পেমেন্ট মেথড</th>
                                <th class="py-3 px-4">TrxID / প্রেরক</th>
                                <th class="py-3 px-4">স্ক্রিনশট</th>
                                <th class="py-3 px-4">স্ট্যাটাস</th>
                                <th class="py-3 px-4 text-right">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @foreach($orders as $order)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="py-4 px-4 font-mono">
                                        <span class="font-bold text-white block">{{ $order->order_number }}</span>
                                        <span class="text-[10px] text-slate-500">{{ $order->created_at->format('d M, Y h:i A') }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <strong class="text-white block">{{ $order->user?->name ?? 'Deleted User' }}</strong>
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $order->user?->email }}</span>
                                        @if($order->user?->phone)
                                            <span class="text-[10px] text-slate-500 font-mono block">{{ $order->user?->phone }}</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="font-black text-slate-200 block">{{ $order->plan_name }}</span>
                                        <span class="text-[10px] text-slate-400">({{ ucfirst(str_replace('_', ' ', $order->billing_cycle)) }})</span>
                                    </td>
                                    <td class="py-4 px-4 font-mono font-black text-amber-400 text-sm">
                                        ৳{{ number_format($order->final_amount, 2) }}
                                        @if($order->discount_amount > 0)
                                            <span class="text-[10px] text-emerald-400 block font-normal">-৳{{ number_format($order->discount_amount, 2) }} ({{ $order->coupon_code }})</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        {!! $order->payment_method_badge !!}
                                    </td>
                                    <td class="py-4 px-4 font-mono">
                                        <strong class="text-indigo-400 font-bold block">{{ $order->transaction_id ?: 'N/A' }}</strong>
                                        <span class="text-[11px] text-slate-400">{{ $order->sender_number }}</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($order->payment_proof)
                                            <a href="{{ asset('storage/' . $order->payment_proof) }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] text-indigo-400 hover:text-indigo-300 font-bold bg-slate-800/80 hover:bg-slate-800 px-2 py-1 rounded-md border border-slate-700/60">
                                                <i class="fa-solid fa-image"></i> View
                                            </a>
                                        @else
                                            <span class="text-[10px] text-slate-600">No Proof</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        {!! $order->status_badge !!}
                                    </td>
                                    <td class="py-4 px-4 text-right space-x-1.5">
                                        @if($order->status === 'pending')
                                            {{-- Approve Button --}}
                                            <form action="{{ route('admin.billing.orders.approve', $order->id) }}" method="POST" class="inline-block" onsubmit="return confirm('আপনি কি এই অর্ডারটি অনুমোদন করে গ্রাহকের একাউন্টে প্ল্যান সক্রিয় করতে চান?');">
                                                @csrf
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-[11px] px-3 py-1.5 rounded-lg shadow-md transition cursor-pointer inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-check"></i>
                                                    <span>Approve</span>
                                                </button>
                                            </form>

                                            {{-- Reject Button --}}
                                            <form action="{{ route('admin.billing.orders.reject', $order->id) }}" method="POST" class="inline-block" onsubmit="return confirm('আপনি কি নিশ্চিতভাবে এই অর্ডারটি বাতিল করতে চান?');">
                                                @csrf
                                                <button type="submit" class="bg-rose-950/80 hover:bg-rose-900 text-rose-300 border border-rose-800/80 font-extrabold text-[11px] px-2.5 py-1.5 rounded-lg transition cursor-pointer inline-flex items-center gap-1">
                                                    <i class="fa-solid fa-xmark"></i>
                                                    <span>Reject</span>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('billing.invoice', $order->order_number) }}" target="_blank" class="text-slate-400 hover:text-white text-[11px] bg-slate-800 px-2.5 py-1.5 rounded-lg inline-flex items-center gap-1 border border-slate-700">
                                                <i class="fa-solid fa-file-invoice"></i> Invoice
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-slate-800">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-12 text-slate-500 space-y-2">
                    <i class="fa-solid fa-folder-open text-3xl"></i>
                    <p class="text-xs">কোনো অর্ডার পাওয়া যায়নি।</p>
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
