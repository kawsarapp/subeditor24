@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50/50 py-4 sm:py-10 px-3 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-3xl shadow-2xl shadow-indigo-100/50 overflow-hidden border border-gray-100 transition-all duration-300">
            
            {{-- Header Section --}}
            <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-5 py-6 sm:px-8 sm:py-7 relative">
                <div class="absolute top-0 right-0 p-4 opacity-10">
                    <i class="fa-solid fa-paper-plane text-6xl rotate-12 text-white"></i>
                </div>
                <div class="relative z-10 text-center sm:text-left">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-white flex items-center justify-center sm:justify-start gap-3">
                        <i class="fa-solid fa-paper-plane text-indigo-200 animate-pulse"></i>
                        Reporter Panel: Submit News Article
                    </h2>
                    <p class="text-indigo-100 text-xs sm:text-sm mt-2 font-medium opacity-90 italic">
                        Please fill in all details accurately before submitting.
                    </p>
                </div>
            </div>

            <form action="{{ route('reporter.news.store') }}" method="POST" enctype="multipart/form-data" class="p-5 sm:p-10 space-y-8">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                    {{-- Title - Full Width on all screens --}}
                    <div class="md:col-span-2 group">
                        <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">
                            News Title <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required 
                               class="w-full border-2 border-gray-100 rounded-2xl p-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 bg-gray-50/30 text-gray-800 placeholder:text-gray-400" 
                               placeholder="Enter an engaging headline...">
                    </div>

                    {{-- Location --}}
                    <div class="group">
                        <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">Your Area / Location</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <input type="text" name="location" 
                                   class="w-full border-2 border-gray-100 rounded-2xl p-3.5 pl-11 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 bg-gray-50/30 text-gray-800" 
                                   placeholder="e.g. Dhaka, Mirpur">
                        </div>
                    </div>

                    {{-- Reporter Name (Read Only) --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-800 mb-2">Reporter Name</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <i class="fa-solid fa-user-check"></i>
                            </span>
                            <input type="text" name="reporter_name" value="{{ auth()->user()->name }}" 
                                   class="w-full border-2 border-gray-50 rounded-2xl p-3.5 pl-11 bg-gray-100/80 font-bold text-gray-500 cursor-not-allowed italic" 
                                   readonly>
                        </div>
                    </div>

                    {{-- Image Upload --}}
                    <div class="group">
                        <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">
                            Featured Image (Max 10MB) <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-200 border-dashed rounded-2xl bg-gray-50/50 hover:bg-indigo-50/30 transition-colors">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label class="relative cursor-pointer bg-white rounded-md font-bold text-indigo-600 hover:text-indigo-500">
                                        <span>Upload Image</span>
                                        <input type="file" name="image_file" required class="sr-only">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500">PNG, JPG up to 10MB</p>
                            </div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2 italic text-center sm:text-left leading-relaxed">
                             Image will not be compressed; original file is saved directly.
                        </p>
                    </div>

                    {{-- Image Caption --}}
                    <div class="group">
                        <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">Image Caption</label>
                        <textarea name="image_caption" rows="4" 
                                  class="w-full border-2 border-gray-100 rounded-2xl p-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 bg-gray-50/30 text-gray-800" 
                                  placeholder="Detailed description of the image..."></textarea>
                    </div>
                </div>

                {{-- Short Summary --}}
                <div class="group">
                    <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">Short Summary</label>
                    <textarea name="short_summary" rows="2" 
                              class="w-full border-2 border-gray-100 rounded-2xl p-3.5 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 bg-gray-50/30 text-gray-800" 
                              placeholder="Write a concise summary in 2-3 lines..."></textarea>
                </div>

                {{-- Body Content --}}
                <div class="group">
                    <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">Full Article Content <span class="text-red-500">*</span></label>
                    <textarea name="content" rows="10" required 
                              class="w-full border-2 border-gray-100 rounded-2xl p-4 sm:p-6 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 bg-gray-50/30 text-gray-800 text-base leading-relaxed" 
                              placeholder="Type the detailed article content here..."></textarea>
                </div>

                {{-- Footer Section --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-gray-100 pt-8">
                    <div class="group">
                        <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">Source URL (Optional)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <i class="fa-solid fa-link"></i>
                            </span>
                            <input type="url" name="source_url" 
                                   class="w-full border-2 border-gray-100 rounded-2xl p-3.5 pl-11 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 bg-gray-50/30 text-gray-800" 
                                   placeholder="https://example.com/source">
                        </div>
                    </div>
                    <div class="group">
                        <label class="block text-sm font-bold text-gray-800 mb-2 transition-colors group-focus-within:text-indigo-600">Tags (Comma separated)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <i class="fa-solid fa-tags"></i>
                            </span>
                            <input type="text" name="tags" 
                                   class="w-full border-2 border-gray-100 rounded-2xl p-3.5 pl-11 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 outline-none transition-all duration-200 bg-gray-50/30 text-gray-800" 
                                   placeholder="Breaking News, Politics, Entertainment">
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex flex-col space-y-4 pt-6 border-t border-gray-50">
                    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl">
                        <p class="text-xs sm:text-sm text-amber-800 font-medium italic">
                            <i class="fa-solid fa-circle-info mr-1"></i>
                            Upon submission, this will be saved as a draft for editorial review before publication.
                        </p>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-indigo-600 text-white py-4 sm:py-5 rounded-2xl font-black text-lg hover:bg-indigo-700 hover:shadow-2xl hover:shadow-indigo-200 transition-all duration-300 transform active:scale-[0.98] flex items-center justify-center gap-3 shadow-lg shadow-indigo-100">
                        Submit News Article 
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection