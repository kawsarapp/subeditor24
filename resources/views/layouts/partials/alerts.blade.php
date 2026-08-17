<div class="fixed top-20 right-4 z-[9999] space-y-2 max-w-md">
    @if(session('success'))
        <div id="flash-success" class="flash-message bg-emerald-600 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 min-w-[300px] border border-emerald-500">
            <i class="fa-solid fa-circle-check text-xl"></i>
            <div>
                <h4 class="font-bold text-sm">সফল হয়েছে!</h4>
                <p class="text-xs text-emerald-100">{{ session('success') }}</p>
            </div>
            <button onclick="document.getElementById('flash-success').remove()" class="ml-auto text-white hover:text-emerald-200"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div id="flash-error" class="flash-message bg-rose-600 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 min-w-[300px] border border-rose-500">
            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            <div>
                <h4 class="font-bold text-sm">ত্রুটি ঘটেছে!</h4>
                <p class="text-xs text-rose-100">{{ session('error') }}</p>
            </div>
            <button onclick="document.getElementById('flash-error').remove()" class="ml-auto text-white hover:text-rose-200"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if(session('warning'))
        <div id="flash-warning" class="flash-message bg-amber-500 text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 min-w-[300px] border border-amber-400">
            <i class="fa-solid fa-circle-exclamation text-xl"></i>
            <div>
                <h4 class="font-bold text-sm">সতর্কতা!</h4>
                <p class="text-xs text-amber-100">{{ session('warning') }}</p>
            </div>
            <button onclick="document.getElementById('flash-warning').remove()" class="ml-auto text-white hover:text-amber-200"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if($errors->any())
        <div id="flash-validation" class="flash-message bg-red-600 text-white px-4 py-3 rounded-xl shadow-2xl flex items-start gap-3 min-w-[320px] border border-red-500">
            <i class="fa-solid fa-solid fa-bug text-xl mt-0.5"></i>
            <div>
                <h4 class="font-bold text-sm">ইনপুট ভ্যালিডেশন এরর ({{ $errors->count() }}টি):</h4>
                <ul class="text-xs text-red-100 list-disc pl-4 mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="document.getElementById('flash-validation').remove()" class="ml-auto text-white hover:text-red-200"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif
</div>