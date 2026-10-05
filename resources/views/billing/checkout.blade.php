@extends('layouts.app')

@section('title', 'Order Checkout - ' . $plan->name)

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-10 px-4 sm:px-6 lg:px-8 relative overflow-hidden font-sans">
    {{-- Glow Background Blobs --}}
    <div class="absolute -top-32 -left-32 w-96 h-96 bg-indigo-600/20 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/2 -right-32 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto relative z-10">
        
        {{-- Header Breadcrumb & Back --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('pricing.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-400 hover:text-white transition group bg-slate-900/60 border border-slate-800 px-3.5 py-2 rounded-xl">
                <i class="fa-solid fa-arrow-left transition-transform group-hover:-translate-x-1"></i>
                <span>প্রাইসিং পেজে ফিরে যান</span>
            </a>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-bold text-slate-400">নিরাপদ ম্যানুয়াল পেমেন্ট চেকআউট</span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- LEFT COLUMN: Plan Summary Card --}}
            <div class="lg:col-span-5 flex flex-col gap-6">
                <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/10 rounded-full blur-2xl"></div>

                    {{-- Plan Badge --}}
                    @if($plan->badge)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-black uppercase tracking-wider bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 mb-3 shadow-md">
                            {{ $plan->badge }}
                        </span>
                    @endif

                    <h2 class="text-2xl font-black text-white tracking-tight">{{ $plan->name }}</h2>
                    <p class="text-xs text-amber-400 font-bold mt-1" id="planDurationText">
                        {{ $cycle === 'yearly' ? '১ বছর (১২ মাস / ৩৬৫ দিন) মেয়াদ' : ($cycle === 'monthly' ? '১ মাস (৩০ দিন) মেয়াদ' : '৬ মাস (১৮০ দিন) স্পেশাল মেয়াদ') }}
                    </p>

                    <hr class="border-slate-800 my-5">

                    {{-- Features List --}}
                    <div class="space-y-3">
                        <div class="flex items-center gap-2.5 text-xs text-slate-300">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                            <span><strong>{{ $plan->daily_news_limit == -1 ? 'আনলিমিটেড' : $plan->daily_news_limit }} টি</strong> দৈনিক নিউজ পোস্ট লিমিট</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-slate-300">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                            <span><strong>{{ $plan->reporters_limit == -1 ? 'আনলিমিটেড' : $plan->reporters_limit }} জন</strong> রিপোর্টার / স্টাফ অ্যাক্সেস</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-slate-300">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                            <span><strong>{{ $plan->bangla_websites_limit == -1 ? 'সকল' : $plan->bangla_websites_limit }} টি</strong> বাংলা ওয়েবসাইট স্ক্র্যাপ সুবিধা</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-slate-300">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                            <span>AI অটোমেটিক নিউজ রিরাইট ও সামারি</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-xs text-slate-300">
                            <i class="fa-solid fa-circle-check text-emerald-400 text-sm"></i>
                            <span>WordPress, Telegram ও Social অটো-পোস্টিং</span>
                        </div>
                    </div>

                    <hr class="border-slate-800 my-5">

                    {{-- Price Breakdown --}}
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between text-slate-400">
                            <span>মূল্য ({{ ucfirst($mode) }} Rate):</span>
                            <span class="font-bold text-slate-200" id="basePriceBreakdownText">৳{{ number_format($basePrice, 2) }}</span>
                        </div>
                        <div id="couponDiscountRow" class="hidden flex justify-between text-emerald-400 font-bold">
                            <span>কুপন ডিসকাউন্ট:</span>
                            <span id="couponDiscountText">-৳0.00</span>
                        </div>
                        <div class="pt-3 border-t border-slate-800 flex justify-between items-baseline">
                            <span class="font-extrabold text-sm text-white">সর্বমোট প্রদেয়:</span>
                            <span id="finalPriceText" class="text-2xl font-black text-amber-400">৳{{ number_format($basePrice, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Support Banner --}}
                <div class="bg-indigo-950/40 border border-indigo-900/60 rounded-2xl p-4 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600/20 text-indigo-400 flex items-center justify-center shrink-0 text-lg">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white">যেকোনো সহায়তায় হটলাইন</h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            WhatsApp: <a href="https://wa.me/{{ $paymentConfig['support_whatsapp'] ?? '8801975389599' }}" target="_blank" class="text-emerald-400 font-bold hover:underline">{{ $paymentConfig['support_phone'] ?? '+880 1975-389599' }}</a>
                        </p>
                    </div>
                </div>
            </div>

            {{-- RIGHT COLUMN: Manual Payment Instructions & Submission Form --}}
            <div class="lg:col-span-7">
                <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
                    
                    {{-- 🌟 STEP 1: BILLING CYCLE SELECTION (USER FREEDOM) --}}
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-base font-black text-white flex items-center gap-2">
                                <span class="w-6 h-6 rounded-lg bg-indigo-600 flex items-center justify-center text-xs text-white">1</span>
                                <span>সাবস্ক্রিপশন মেয়াদ নির্বাচন করুন</span>
                            </h3>
                            <span class="text-[11px] font-bold text-indigo-400"><i class="fa-solid fa-clock mr-1"></i> আপনার পছন্দমত মেয়াদ বেছে নিন</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3" id="billingCycleSelectors">
                            {{-- 1 Month --}}
                            <button type="button" onclick="selectBillingCycle('monthly')" id="btnCycle_monthly" class="cycle-btn p-3.5 rounded-2xl border text-left transition flex flex-col justify-between gap-2 cursor-pointer {{ $cycle === 'monthly' ? 'border-2 border-amber-500 bg-amber-950/30 text-amber-300 shadow-lg shadow-amber-500/10' : 'bg-slate-950/70 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs font-black text-white">১ মাস</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-semibold">মাসিক</span>
                                </div>
                                <div>
                                    <div class="text-base font-black font-mono text-white">৳{{ number_format($monthlyRate, 0) }}</div>
                                    <div class="text-[10px] text-slate-400">৩০ দিন অ্যাক্টিভেশন</div>
                                </div>
                            </button>

                            {{-- 6 Months (Special Offer) --}}
                            <button type="button" onclick="selectBillingCycle('half_yearly')" id="btnCycle_half_yearly" class="cycle-btn p-3.5 rounded-2xl border text-left transition flex flex-col justify-between gap-2 cursor-pointer {{ $cycle === 'half_yearly' ? 'border-2 border-amber-500 bg-amber-950/30 text-amber-300 shadow-lg shadow-amber-500/10' : 'bg-slate-950/70 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs font-black text-amber-300">৬ মাস</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 font-bold border border-amber-500/40">🔥 Best Value</span>
                                </div>
                                <div>
                                    <div class="text-base font-black font-mono text-amber-400">৳{{ number_format($monthlyRate * 6, 0) }}</div>
                                    <div class="text-[10px] text-slate-400">১৮০ দিন স্পেশাল অফার</div>
                                </div>
                            </button>

                            {{-- 1 Year (12 Months) --}}
                            <button type="button" onclick="selectBillingCycle('yearly')" id="btnCycle_yearly" class="cycle-btn p-3.5 rounded-2xl border text-left transition flex flex-col justify-between gap-2 cursor-pointer {{ $cycle === 'yearly' ? 'border-2 border-amber-500 bg-amber-950/30 text-amber-300 shadow-lg shadow-amber-500/10' : 'bg-slate-950/70 border-slate-800 text-slate-400 hover:border-slate-700' }}">
                                <div class="flex items-center justify-between w-full">
                                    <span class="text-xs font-black text-white">১ বছর</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-bold border border-emerald-500/40">💰 Save 15%</span>
                                </div>
                                <div>
                                    <div class="text-base font-black font-mono text-white">৳{{ number_format(round($monthlyRate * 12 * 0.85), 0) }}</div>
                                    <div class="text-[10px] text-slate-400">৩৬৫ দিন বার্ষিক মেয়াদ</div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <hr class="border-slate-800">

                    {{-- 🌟 STEP 2: PAYMENT METHOD --}}
                    <div>
                        <h3 class="text-base font-black text-white flex items-center gap-2 mb-3">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 flex items-center justify-center text-xs text-white">2</span>
                            <span>পেমেন্ট মাধ্যম বেছে নিন ও টাকা পাঠান</span>
                        </h3>

                        {{-- Payment Method Tabs --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5" id="paymentMethodSelectors">
                            {{-- bKash --}}
                            <button type="button" onclick="selectPaymentMethod('bkash')" id="btnMethod_bkash" class="method-btn border-2 border-pink-500 bg-pink-950/30 text-pink-300 p-3 rounded-2xl flex flex-col items-center gap-1.5 transition text-xs font-extrabold cursor-pointer">
                                <span class="text-base">🌸</span>
                                <span>bKash</span>
                            </button>
                            {{-- Nagad --}}
                            <button type="button" onclick="selectPaymentMethod('nagad')" id="btnMethod_nagad" class="method-btn border border-slate-800 bg-slate-950/60 text-slate-400 p-3 rounded-2xl flex flex-col items-center gap-1.5 transition text-xs font-extrabold hover:border-slate-700 cursor-pointer">
                                <span class="text-base">🔥</span>
                                <span>Nagad</span>
                            </button>
                            {{-- Rocket --}}
                            <button type="button" onclick="selectPaymentMethod('rocket')" id="btnMethod_rocket" class="method-btn border border-slate-800 bg-slate-950/60 text-slate-400 p-3 rounded-2xl flex flex-col items-center gap-1.5 transition text-xs font-extrabold hover:border-slate-700 cursor-pointer">
                                <span class="text-base">🚀</span>
                                <span>Rocket</span>
                            </button>
                            {{-- Bank --}}
                            <button type="button" onclick="selectPaymentMethod('bank_transfer')" id="btnMethod_bank_transfer" class="method-btn border border-slate-800 bg-slate-950/60 text-slate-400 p-3 rounded-2xl flex flex-col items-center gap-1.5 transition text-xs font-extrabold hover:border-slate-700 cursor-pointer">
                                <span class="text-base">🏦</span>
                                <span>Bank</span>
                            </button>
                        </div>

                        {{-- Dynamic Payment Instructions Box --}}
                        <div id="methodInstructionsBox" class="mt-4 p-4 rounded-2xl bg-slate-950/80 border border-slate-800 text-xs">
                            {{-- Injected dynamically by JS --}}
                        </div>
                    </div>

                    <hr class="border-slate-800">

                    {{-- 🌟 STEP 3: SUBMISSION FORM --}}
                    <form action="{{ route('billing.order.submit') }}" method="POST" enctype="multipart/form-data" id="orderSubmitForm">
                        @csrf
                        <input type="hidden" name="plan_slug" value="{{ $plan->slug }}">
                        <input type="hidden" name="pricing_mode" value="{{ $mode }}">
                        <input type="hidden" name="billing_cycle" id="selectedBillingCycleInput" value="{{ $cycle }}">
                        <input type="hidden" name="payment_method" id="selectedPaymentMethodInput" value="bkash">

                        <h3 class="text-base font-black text-white flex items-center gap-2 mb-4">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 flex items-center justify-center text-xs text-white">3</span>
                            <span>পেমেন্ট তথ্য প্রদান করুন</span>
                        </h3>

                        {{-- Coupon Code Apply Box --}}
                        <div class="mb-4">
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">কুপন কোড (যদি থাকে)</label>
                            <div class="flex gap-2">
                                <input type="text" id="couponCodeInput" name="coupon_code" placeholder="যেমন: OCTOBER57" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white uppercase tracking-wider focus:outline-none focus:border-indigo-500 font-mono">
                                <button type="button" onclick="applyCouponCode()" id="applyCouponBtn" class="bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shrink-0 cursor-pointer">
                                    Apply
                                </button>
                            </div>
                            <p id="couponMsg" class="text-[11px] mt-1.5 hidden"></p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Sender Number --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5">
                                    যে নম্বর/অ্যাকাউন্ট থেকে টাকা পাঠিয়েছেন <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="sender_number" required placeholder="যেমন: 017xxxxxxxx" value="{{ old('sender_number') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500 font-mono">
                                @error('sender_number')
                                    <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Transaction ID --}}
                            <div>
                                <label class="block text-xs font-bold text-slate-300 mb-1.5">
                                    Transaction ID (TrxID) <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="transaction_id" required placeholder="যেমন: 9J4K8L7M2N" value="{{ old('transaction_id') }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white uppercase focus:outline-none focus:border-indigo-500 font-mono font-bold tracking-wider">
                                @error('transaction_id')
                                    <p class="text-rose-400 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Payment Proof Screenshot (Optional) --}}
                        <div class="mt-4">
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">
                                পেমেন্ট স্ক্রিনশট / ব্যাংক ডিপোজিট স্লিপ (ঐচ্ছিক)
                            </label>
                            <input type="file" name="payment_proof" accept="image/*" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-indigo-600 file:text-white hover:file:bg-indigo-500 cursor-pointer">
                            <p class="text-[10px] text-slate-500 mt-1">ছবি দিলে দ্রুত ভেরিফিকেশন সুবিধা পাওয়া যাবে (সর্বোচ্চ ৫ MB)।</p>
                        </div>

                        {{-- Additional Notes --}}
                        <div class="mt-4">
                            <label class="block text-xs font-bold text-slate-300 mb-1.5">গ্রাহক মন্তব্য / বিশেষ নির্দেশনা (ঐচ্ছিক)</label>
                            <textarea name="customer_notes" rows="2" placeholder="কোনো বিশেষ অনুরোধ থাকলে লিখুন..." class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
                        </div>

                        {{-- Submit Button --}}
                        <div class="mt-6">
                            <button type="submit" id="submitOrderBtn" class="w-full bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-black py-3.5 px-6 rounded-2xl shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2 cursor-pointer text-sm">
                                <i class="fa-solid fa-lock"></i>
                                <span>অর্ডার সাবমিট ও অ্যাক্টিভেশন নিশ্চিত করুন 🚀</span>
                            </button>
                            <p class="text-[11px] text-center text-slate-400 mt-2.5">
                                <i class="fa-solid fa-shield-halved text-emerald-400 mr-1"></i> আপনার তথ্য সম্পূর্ণ সুরক্ষিত। পেমেন্ট যাচাই করে অ্যাডমিন শীঘ্রই একাউন্ট সক্রিয় করবেন।
                            </p>
                        </div>
                    </form>

                </div>
            </div>

        </div>

    </div>
</div>

<script>
    const paymentMethodsConfig = @json($paymentConfig);
    let currentMethod = 'bkash';

    const monthlyRate = {{ $monthlyRate }};
    const cyclePrices = {
        monthly: {{ $monthlyRate }},
        half_yearly: {{ $monthlyRate * 6 }},
        yearly: {{ round($monthlyRate * 12 * 0.85) }}
    };

    let currentCycle = '{{ $cycle }}' || 'half_yearly';
    let baseOrderPrice = cyclePrices[currentCycle] !== undefined ? cyclePrices[currentCycle] : {{ $basePrice }};
    let currentDiscount = 0;

    function selectBillingCycle(cycle) {
        currentCycle = cycle;
        const cycleInput = document.getElementById('selectedBillingCycleInput');
        if (cycleInput) cycleInput.value = cycle;

        // Reset Cycle button styles
        ['monthly', 'half_yearly', 'yearly'].forEach(c => {
            const btn = document.getElementById('btnCycle_' + c);
            if (btn) {
                btn.className = 'cycle-btn p-3.5 rounded-2xl border text-left transition flex flex-col justify-between gap-2 cursor-pointer bg-slate-950/70 border-slate-800 text-slate-400 hover:border-slate-700';
            }
        });

        const activeBtn = document.getElementById('btnCycle_' + cycle);
        if (activeBtn) {
            activeBtn.className = 'cycle-btn p-3.5 rounded-2xl border-2 text-left transition flex flex-col justify-between gap-2 cursor-pointer border-amber-500 bg-amber-950/30 text-amber-300 shadow-lg shadow-amber-500/10';
        }

        // Update Base price
        baseOrderPrice = cyclePrices[cycle] !== undefined ? cyclePrices[cycle] : monthlyRate;

        // Update Duration label in summary
        const durLabel = cycle === 'yearly' 
            ? '১ বছর (১২ মাস / ৩৬৫ দিন) মেয়াদ' 
            : (cycle === 'monthly' ? '১ মাস (৩০ দিন) মেয়াদ' : '৬ মাস (১৮০ দিন) স্পেশাল মেয়াদ');
        
        const durEl = document.getElementById('planDurationText');
        if (durEl) durEl.textContent = durLabel;

        // Update rate breakdown
        const baseEl = document.getElementById('basePriceBreakdownText');
        if (baseEl) baseEl.textContent = '৳' + baseOrderPrice.toFixed(2);

        // Recalculate Final Price
        const finalAmt = Math.max(0, baseOrderPrice - currentDiscount);
        const finalEl = document.getElementById('finalPriceText');
        if (finalEl) finalEl.textContent = '৳' + finalAmt.toFixed(2);
    }

    function selectPaymentMethod(method) {
        currentMethod = method;
        document.getElementById('selectedPaymentMethodInput').value = method;

        // Update button styles
        const buttons = document.querySelectorAll('.method-btn');
        buttons.forEach(btn => {
            btn.classList.remove('border-pink-500', 'bg-pink-950/30', 'text-pink-300', 'border-orange-500', 'bg-orange-950/30', 'text-orange-300', 'border-purple-500', 'bg-purple-950/30', 'text-purple-300', 'border-blue-500', 'bg-blue-950/30', 'text-blue-300', 'border-2');
            btn.classList.add('border-slate-800', 'bg-slate-950/60', 'text-slate-400', 'border');
        });

        const activeBtn = document.getElementById('btnMethod_' + method);
        if (activeBtn) {
            activeBtn.classList.remove('border-slate-800', 'bg-slate-950/60', 'text-slate-400', 'border');
            if (method === 'bkash') activeBtn.classList.add('border-2', 'border-pink-500', 'bg-pink-950/30', 'text-pink-300');
            if (method === 'nagad') activeBtn.classList.add('border-2', 'border-orange-500', 'bg-orange-950/30', 'text-orange-300');
            if (method === 'rocket') activeBtn.classList.add('border-2', 'border-purple-500', 'bg-purple-950/30', 'text-purple-300');
            if (method === 'bank_transfer') activeBtn.classList.add('border-2', 'border-blue-500', 'bg-blue-950/30', 'text-blue-300');
        }

        renderMethodInstructions();
    }

    function renderMethodInstructions() {
        const box = document.getElementById('methodInstructionsBox');
        let html = '';

        if (currentMethod === 'bkash') {
            const num = paymentMethodsConfig.bkash_number || '01975-389599';
            const type = paymentMethodsConfig.bkash_type || 'Personal (Send Money)';
            const inst = paymentMethodsConfig.bkash_instruction || 'বিকাশ অ্যাপ থেকে Send Money করুন।';
            html = `
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-pink-400 text-sm">bKash ${type}:</span>
                            <span class="font-mono font-black text-white text-base">${num}</span>
                        </div>
                        <p class="text-slate-400 mt-1">${inst}</p>
                    </div>
                    <button type="button" onclick="copyToClipboard('${num}')" class="bg-pink-900/40 hover:bg-pink-900/60 text-pink-300 border border-pink-700/50 px-3 py-1.5 rounded-lg text-[11px] font-bold shrink-0 cursor-pointer flex items-center gap-1">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
            `;
        } else if (currentMethod === 'nagad') {
            const num = paymentMethodsConfig.nagad_number || '01975-389599';
            const type = paymentMethodsConfig.nagad_type || 'Personal (Send Money)';
            const inst = paymentMethodsConfig.nagad_instruction || 'নগদ অ্যাপ থেকে Send Money করুন।';
            html = `
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-orange-400 text-sm">Nagad ${type}:</span>
                            <span class="font-mono font-black text-white text-base">${num}</span>
                        </div>
                        <p class="text-slate-400 mt-1">${inst}</p>
                    </div>
                    <button type="button" onclick="copyToClipboard('${num}')" class="bg-orange-900/40 hover:bg-orange-900/60 text-orange-300 border border-orange-700/50 px-3 py-1.5 rounded-lg text-[11px] font-bold shrink-0 cursor-pointer flex items-center gap-1">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
            `;
        } else if (currentMethod === 'rocket') {
            const num = paymentMethodsConfig.rocket_number || '01975-389599-8';
            const type = paymentMethodsConfig.rocket_type || 'Personal';
            const inst = paymentMethodsConfig.rocket_instruction || 'রকেট থেকে Send Money করুন।';
            html = `
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-extrabold text-purple-400 text-sm">Rocket ${type}:</span>
                            <span class="font-mono font-black text-white text-base">${num}</span>
                        </div>
                        <p class="text-slate-400 mt-1">${inst}</p>
                    </div>
                    <button type="button" onclick="copyToClipboard('${num}')" class="bg-purple-900/40 hover:bg-purple-900/60 text-purple-300 border border-purple-700/50 px-3 py-1.5 rounded-lg text-[11px] font-bold shrink-0 cursor-pointer flex items-center gap-1">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
            `;
        } else if (currentMethod === 'bank_transfer') {
            const bank = paymentMethodsConfig.bank_name || 'Islami Bank Bangladesh Ltd.';
            const accName = paymentMethodsConfig.bank_account_name || 'NewsManage24 Technologies';
            const accNo = paymentMethodsConfig.bank_account_no || '20501234567890123';
            const branch = paymentMethodsConfig.bank_branch || 'Dhaka Principal Branch';
            const routing = paymentMethodsConfig.bank_routing_no || '125272847';
            const inst = paymentMethodsConfig.bank_instruction || 'ব্যাংক ডিপোজিট করে স্লিপ আপলোড করুন।';
            html = `
                <div class="space-y-1.5">
                    <div class="text-blue-400 font-extrabold text-sm">${bank}</div>
                    <div class="text-slate-300">Account Name: <strong class="text-white">${accName}</strong></div>
                    <div class="flex items-center gap-2">
                        <span class="text-slate-300">A/C No:</span>
                        <strong class="font-mono text-white text-sm">${accNo}</strong>
                        <button type="button" onclick="copyToClipboard('${accNo}')" class="text-blue-400 hover:underline text-[10px] ml-1 cursor-pointer"><i class="fa-regular fa-copy"></i> Copy</button>
                    </div>
                    <div class="text-slate-400 text-[11px]">Branch: ${branch} | Routing: ${routing}</div>
                    <p class="text-slate-400 mt-1">${inst}</p>
                </div>
            `;
        }

        box.innerHTML = html;
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('কপিকৃত: ' + text);
        });
    }

    function applyCouponCode() {
        const code = document.getElementById('couponCodeInput').value.trim();
        const msg = document.getElementById('couponMsg');
        if (!code) {
            msg.textContent = 'অনুগ্রহ করে কুপন কোড লিখুন।';
            msg.className = 'text-[11px] mt-1.5 text-amber-400 block';
            return;
        }

        fetch('{{ route("pricing.apply-coupon") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                code: code,
                plan_slug: '{{ $plan->slug }}',
                mode: '{{ $mode }}'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data) {
                currentDiscount = parseFloat(data.data.discount_amount || 0);
                const finalAmt = Math.max(0, baseOrderPrice - currentDiscount);

                document.getElementById('couponDiscountRow').classList.remove('hidden');
                document.getElementById('couponDiscountText').textContent = '-৳' + currentDiscount.toFixed(2);
                document.getElementById('finalPriceText').textContent = '৳' + finalAmt.toFixed(2);

                msg.textContent = '🎉 ' + data.message;
                msg.className = 'text-[11px] mt-1.5 text-emerald-400 block';
            } else {
                msg.textContent = data.message || '❌ অবৈধ কুপন কোড!';
                msg.className = 'text-[11px] mt-1.5 text-rose-400 block';
            }
        })
        .catch(err => {
            msg.textContent = 'কুপন যাচাইকরণে সমস্যা হয়েছে।';
            msg.className = 'text-[11px] mt-1.5 text-rose-400 block';
        });
    }

    // Initialize default method
    selectPaymentMethod('bkash');
</script>
@endsection
