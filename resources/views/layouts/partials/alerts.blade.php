<div id="toast-container" class="fixed top-20 right-4 z-[99999] space-y-3 max-w-md pointer-events-none">
    {{-- Dynamic toasts appended here via JS --}}
    @if(session('success'))
        <div id="flash-success" class="flash-message pointer-events-auto bg-emerald-600 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[320px] border border-emerald-500 backdrop-blur-md">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-extrabold text-xs tracking-wide uppercase text-emerald-200">সফল হয়েছে!</h4>
                <p class="text-xs font-semibold text-white mt-0.5">{{ session('success') }}</p>
            </div>
            <button onclick="document.getElementById('flash-success').remove()" class="text-white/80 hover:text-white p-1 transition"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if(session('error'))
        <div id="flash-error" class="flash-message pointer-events-auto bg-rose-600 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[320px] border border-rose-500 backdrop-blur-md">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-extrabold text-xs tracking-wide uppercase text-rose-200">ত্রুটি ঘটেছে!</h4>
                <p class="text-xs font-semibold text-white mt-0.5">{{ session('error') }}</p>
            </div>
            <button onclick="document.getElementById('flash-error').remove()" class="text-white/80 hover:text-white p-1 transition"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if(session('warning'))
        <div id="flash-warning" class="flash-message pointer-events-auto bg-amber-500 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-center gap-3 min-w-[320px] border border-amber-400 backdrop-blur-md">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-extrabold text-xs tracking-wide uppercase text-amber-200">সতর্কতা!</h4>
                <p class="text-xs font-semibold text-white mt-0.5">{{ session('warning') }}</p>
            </div>
            <button onclick="document.getElementById('flash-warning').remove()" class="text-white/80 hover:text-white p-1 transition"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div id="flash-validation" class="flash-message pointer-events-auto bg-rose-700 text-white px-4 py-3 rounded-2xl shadow-2xl flex items-start gap-3 min-w-[320px] border border-rose-600 backdrop-blur-md">
            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-bug text-lg"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-extrabold text-xs tracking-wide uppercase text-rose-200">ভ্যালিডেশন এরর ({{ $errors->count() }}টি):</h4>
                <ul class="text-xs font-medium text-rose-100 list-disc pl-4 mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="document.getElementById('flash-validation').remove()" class="text-white/80 hover:text-white p-1 transition"><i class="fa-solid fa-xmark"></i></button>
        </div>
    @endif
</div>