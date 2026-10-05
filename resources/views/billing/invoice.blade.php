<!DOCTYPE html>
<html lang="bn" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->order_number }} - Subeditor24</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Hind+Siliguri:wght@400;600;700&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', 'Hind Siliguri', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .print-clean { border: 1px solid #ddd !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen py-10 px-4 sm:px-6">

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Top Action Bar (No Print) --}}
        <div class="no-print flex items-center justify-between bg-slate-900 border border-slate-800 p-4 rounded-2xl">
            <a href="{{ route('billing.my-subscription') }}" class="text-xs font-bold text-slate-400 hover:text-white flex items-center gap-1.5 transition">
                <i class="fa-solid fa-arrow-left"></i>
                <span>সাবস্ক্রিপশন ড্যাশবোর্ডে ফিরে যান</span>
            </a>
            <button onclick="window.print()" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black px-4 py-2 rounded-xl transition flex items-center gap-2 shadow-lg cursor-pointer">
                <i class="fa-solid fa-print"></i>
                <span>ইনভয়েস প্রিন্ট / PDF সেভ করুন</span>
            </button>
        </div>

        {{-- INVOICE CONTAINER --}}
        <div class="print-clean bg-slate-900/90 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl relative overflow-hidden">
            
            {{-- Header Row --}}
            <div class="flex flex-col sm:flex-row justify-between items-start gap-6 border-b border-slate-800 pb-8">
                <div>
                    <div class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 flex items-center justify-center text-white font-black text-lg">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <span class="font-black text-2xl tracking-tight text-white">Subeditor<span class="text-indigo-500">24</span></span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Next-Gen Newsroom CMS & Scraping Automation</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Hotline: {{ $paymentConfig['support_phone'] ?? '+880 1975-389599' }}</p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-xs font-black uppercase tracking-widest text-indigo-400 block">OFFICIAL INVOICE</span>
                    <h2 class="text-xl font-black text-white font-mono mt-1">{{ $order->order_number }}</h2>
                    <div class="mt-2">
                        {!! $order->status_badge !!}
                    </div>
                </div>
            </div>

            {{-- Bill To & Metadata --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 py-6 border-b border-slate-800 text-xs">
                <div>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">বিল গ্রহীতা (CUSTOMER):</span>
                    <h3 class="text-base font-extrabold text-white mt-1">{{ $order->user?->name ?? 'Customer' }}</h3>
                    <p class="text-slate-400 font-mono mt-0.5">{{ $order->user?->email }}</p>
                    @if($order->user?->phone)
                        <p class="text-slate-400 font-mono">{{ $order->user?->phone }}</p>
                    @endif
                </div>

                <div class="sm:text-right space-y-1">
                    <div>
                        <span class="text-slate-500">অর্ডার তারিখ:</span>
                        <strong class="text-slate-300 font-mono ml-1">{{ $order->created_at->format('d M, Y h:i A') }}</strong>
                    </div>
                    @if($order->approved_at)
                    <div>
                        <span class="text-slate-500">অনুমোদনের তারিখ:</span>
                        <strong class="text-emerald-400 font-mono ml-1">{{ $order->approved_at->format('d M, Y h:i A') }}</strong>
                    </div>
                    @endif
                    <div>
                        <span class="text-slate-500">পেমেন্ট মেথড:</span>
                        <strong class="text-slate-300 ml-1 uppercase">{{ $order->payment_method }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500">TrxID:</span>
                        <strong class="text-indigo-400 font-mono ml-1 font-bold">{{ $order->transaction_id ?: 'N/A' }}</strong>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="py-6">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] font-black">
                            <th class="py-3">বিবরণ / প্যাকেজের নাম</th>
                            <th class="py-3 text-center">মেয়াদ</th>
                            <th class="py-3 text-right">মূল্য</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <tr>
                            <td class="py-4">
                                <strong class="text-sm font-black text-white">{{ $order->plan_name }}</strong>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Subeditor24 SaaS Newsroom CMS License ({{ ucfirst($order->pricing_mode) }} Rate)</span>
                            </td>
                            <td class="py-4 text-center font-bold text-amber-400">
                                {{ $order->billing_cycle_label }}
                            </td>
                            <td class="py-4 text-right font-mono font-bold text-white">
                                ৳{{ number_format($order->base_price, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Calculation Summary --}}
            <div class="border-t border-slate-800 pt-4 flex justify-end">
                <div class="w-full sm:w-64 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>সাব-টোটাল:</span>
                        <span class="font-mono text-slate-200">৳{{ number_format($order->base_price, 2) }}</span>
                    </div>
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-emerald-400 font-bold">
                        <span>ডিসকাউন্ট ({{ $order->coupon_code }}):</span>
                        <span class="font-mono">-৳{{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-sm font-black text-white pt-2 border-t border-slate-800">
                        <span>সর্বমোট প্রদেয়:</span>
                        <span class="font-mono text-amber-400 text-lg">৳{{ number_format($order->final_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Footer Notes --}}
            <div class="mt-10 pt-6 border-t border-slate-800 text-[11px] text-slate-500 text-center space-y-1">
                <p>এটি একটি কম্পিউটার-জেনারেটেড অফিসিয়াল ইনভয়েস। কোনো ম্যানুয়াল স্বাক্ষরের প্রয়োজন নেই।</p>
                <p>© {{ date('Y') }} Subeditor24. All rights reserved.</p>
            </div>

        </div>

    </div>

</body>
</html>
