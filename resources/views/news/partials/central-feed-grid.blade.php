@if($newsItems->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8" id="centralFeedGrid">
        @foreach($newsItems as $item)
            @include('news.partials.central-feed-card', ['item' => $item])
        @endforeach
    </div>

    {{-- Pagination (AJAX Intercepted) --}}
    <div class="mt-6 ajax-pagination-wrapper">
        {{ $newsItems->links() }}
    </div>
@else
    <div class="bg-white rounded-3xl border border-slate-200/90 p-12 text-center shadow-sm">
        <div class="w-20 h-20 bg-indigo-50 text-indigo-500 rounded-full flex items-center justify-center mx-auto text-3xl mb-4 font-black">
            ⚡
        </div>
        <h3 class="text-lg sm:text-xl font-bold font-bangla text-slate-800 mb-2">কোনো সেন্ট্রাল খবর পাওয়া যায়নি</h3>
        <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mb-6">
            স্বয়ংক্রিয় ব্যাকগ্রাউন্ড স্ক্র্যাপার প্রতিটি সোর্স থেকে প্রতি ৫ মিনিটে নতুন খবর সংগ্রহ করছে।
        </p>
        <button onclick="fetchCentralFeedAjax()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-md transition cursor-pointer">
            🔄 এখনই নতুন খবর চেক করুন
        </button>
    </div>
@endif
