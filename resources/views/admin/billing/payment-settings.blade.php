@extends('layouts.app')

@section('title', 'Manual Payment Gateway Settings - Super Admin')

@section('content')
<div class="min-h-screen bg-slate-950 text-slate-100 py-8 px-4 sm:px-6 lg:px-8 font-sans">
    <div class="max-w-4xl mx-auto space-y-8">

        {{-- TOP HEADER --}}
        <div class="flex items-center justify-between border-b border-slate-800/80 pb-6">
            <div>
                <div class="flex items-center gap-2.5">
                    <a href="{{ route('admin.billing.orders') }}" class="text-slate-400 hover:text-white transition text-xs font-bold mr-2">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <h1 class="text-2xl font-black text-white tracking-tight">ম্যানুয়াল পেমেন্ট গেটওয়ে ও অ্যাকাউন্ট সেটিংস</h1>
                </div>
                <p class="text-xs text-slate-400 mt-1">
                    গ্রাহকরা চেকআউট পেজে যে বিকাশ, নগদ, রকেট এবং ব্যাংক অ্যাকাউন্ট তথ্য দেখতে পাবে তা কনফিগার করুন।
                </p>
            </div>
        </div>

        {{-- FLASH MESSAGES --}}
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-950/70 border border-emerald-800 text-emerald-300 text-xs sm:text-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-emerald-400 text-lg shrink-0"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- FORM --}}
        <form action="{{ route('admin.billing.payment-settings.update') }}" method="POST" class="space-y-6">
            @csrf

            {{-- 1. bKash Settings --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-4">
                <h3 class="text-base font-extrabold text-pink-400 flex items-center gap-2">
                    <span class="text-lg">🌸</span>
                    <span>bKash (বিকাশ) অ্যাকাউন্ট কনফিগারেশন</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">বিকাশ নম্বর</label>
                        <input type="text" name="bkash_number" value="{{ $config['bkash_number'] ?? '01975-389599' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:border-pink-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">অ্যাকাউন্ট টাইপ</label>
                        <input type="text" name="bkash_type" value="{{ $config['bkash_type'] ?? 'Merchant (Make Payment)' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-pink-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">বিকাশ পেমেন্ট নির্দেশনা (গ্রাহকের জন্য)</label>
                    <textarea name="bkash_instruction" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-pink-500 focus:outline-none">{{ $config['bkash_instruction'] ?? 'বিকাশ অ্যাপ থেকে Make Payment অপশন নির্বাচন করে পেমেন্ট সম্পন্ন করে Transaction ID (TrxID) নিচে প্রদান করুন।' }}</textarea>
                </div>
            </div>

            {{-- 2. Nagad Settings --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-4">
                <h3 class="text-base font-extrabold text-orange-400 flex items-center gap-2">
                    <span class="text-lg">🔥</span>
                    <span>Nagad (নগদ) অ্যাকাউন্ট কনফিগারেশন</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">নগদ নম্বর</label>
                        <input type="text" name="nagad_number" value="{{ $config['nagad_number'] ?? '01771-545972' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:border-orange-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">অ্যাকাউন্ট টাইপ</label>
                        <input type="text" name="nagad_type" value="{{ $config['nagad_type'] ?? 'Personal (Send Money)' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-orange-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">নগদ পেমেন্ট নির্দেশনা</label>
                    <textarea name="nagad_instruction" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-orange-500 focus:outline-none">{{ $config['nagad_instruction'] ?? 'নগদ অ্যাপ থেকে Send Money করুন এবং Transaction ID (TrxID) নিচে প্রদান করুন।' }}</textarea>
                </div>
            </div>

            {{-- 3. Rocket & Upay Settings --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-4">
                <h3 class="text-base font-extrabold text-purple-400 flex items-center gap-2">
                    <span class="text-lg">🚀</span>
                    <span>Rocket & Upay (রকেট ও উপায়) অ্যাকাউন্ট কনফিগারেশন</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">রকেট / উপায় নম্বর</label>
                        <input type="text" name="rocket_number" value="{{ $config['rocket_number'] ?? '01771-545972' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:border-purple-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">অ্যাকাউন্ট টাইপ</label>
                        <input type="text" name="rocket_type" value="{{ $config['rocket_type'] ?? 'Personal (Rocket / Upay)' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-purple-500 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">রকেট ও উপায় পেমেন্ট নির্দেশনা</label>
                    <textarea name="rocket_instruction" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-purple-500 focus:outline-none">{{ $config['rocket_instruction'] ?? 'রকেট বা উপায় (Upay) অ্যাপ থেকে Send Money করুন এবং TrxID নিচে লিখুন।' }}</textarea>
                </div>
            </div>

            {{-- 4. Bank Transfer Settings --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-4">
                <h3 class="text-base font-extrabold text-blue-400 flex items-center gap-2">
                    <span class="text-lg">🏦</span>
                    <span>Bank Transfer (ব্যাংক অ্যাকাউন্ট) কনফিগারেশন</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">ব্যাংকের নাম</label>
                        <input type="text" name="bank_name" value="{{ $config['bank_name'] ?? 'Dhaka Bank PLC' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">অ্যাকাউন্ট নাম (Account Title)</label>
                        <input type="text" name="bank_account_name" value="{{ $config['bank_account_name'] ?? 'Kawsar Ahmed' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">অ্যাকাউন্ট নম্বর</label>
                        <input type="text" name="bank_account_no" value="{{ $config['bank_account_no'] ?? '1142750023075' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:border-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">ব্রাঞ্চ ও রাউটিং নম্বর</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" name="bank_branch" value="{{ $config['bank_branch'] ?? 'Pragati Sarani Branch' }}" placeholder="Branch" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white focus:border-blue-500 focus:outline-none">
                            <input type="text" name="bank_routing_no" value="{{ $config['bank_routing_no'] ?? '085260344' }}" placeholder="Routing" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2.5 text-xs text-white font-mono focus:border-blue-500 focus:outline-none">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-300 mb-1.5">ব্যাংক ডিপোজিট নির্দেশনা</label>
                    <textarea name="bank_instruction" rows="2" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2 text-xs text-white focus:border-blue-500 focus:outline-none">{{ $config['bank_instruction'] ?? 'ব্যাংক ডিপোজিট বা ফান্ড ট্রান্সফার (BEFTN/NPSB/RTGS) করে ট্রানজেকশন স্লিপ/স্ক্রিনশট আপলোড করুন।' }}</textarea>
                </div>
            </div>

            {{-- 5. Support Helpline Settings --}}
            <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-7 shadow-xl space-y-4">
                <h3 class="text-base font-extrabold text-emerald-400 flex items-center gap-2">
                    <span class="text-lg">📞</span>
                    <span>হটলাইন ও হোয়াটসঅ্যাপ সাপোর্ট</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">ফোন সাপোর্ট নম্বর</label>
                        <input type="text" name="support_phone" value="{{ $config['support_phone'] ?? '+880 1975-389599' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1.5">WhatsApp নম্বর (International format, no +)</label>
                        <input type="text" name="support_whatsapp" value="{{ $config['support_whatsapp'] ?? '8801975389599' }}" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono focus:border-emerald-500 focus:outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-black text-sm px-6 py-3 rounded-2xl shadow-xl shadow-indigo-600/30 transition transform hover:-translate-y-0.5 cursor-pointer flex items-center gap-2">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>সেটিংস সংরক্ষণ করুন</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection
