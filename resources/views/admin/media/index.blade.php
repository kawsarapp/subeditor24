@extends('layouts.app')

@push('styles')
<style>
    .media-card { transition: all 0.3s ease; }
    .media-card:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
    #dropzone { transition: all 0.2s ease; }
    #dropzone.dragover { background-color: #eef2ff; border-color: #6366f1; }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('settings.index') }}" class="text-slate-400 hover:text-slate-700 text-sm border border-slate-300 px-3 py-1.5 rounded-lg transition">← Back to Settings</a>
                <a href="{{ route('admin.templates.create') }}" class="text-indigo-600 hover:text-indigo-800 text-sm border border-indigo-200 bg-indigo-50 px-3 py-1.5 rounded-lg transition font-bold">➕ Go to Template Builder</a>
            </div>
            <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-photo-film text-indigo-500"></i> Media & Assets Manager
            </h1>
            <p class="text-sm text-slate-500 mt-1">Upload and manage Frames (.png) and Custom Fonts (.ttf, .woff) for Studio Templates</p>
        </div>
    </div>

    {{-- File Upload Dropzone --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 mb-8">
        <h3 class="font-bold text-slate-700 mb-4 text-lg">📤 Upload New Assets</h3>
        
        <div id="dropzone" class="border-2 border-dashed border-slate-300 rounded-xl p-8 text-center cursor-pointer hover:bg-slate-50 relative">
            <input type="file" id="fileInput" multiple class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".png,.jpg,.jpeg,.webp,.ttf,.otf,.woff,.woff2">
            
            <div class="w-16 h-16 bg-indigo-50 rounded-full flex items-center justify-center mx-auto text-indigo-500 mb-4">
                <i class="fa-solid fa-cloud-arrow-up text-2xl"></i>
            </div>
            <h4 class="text-slate-700 font-bold text-lg mb-1">Click to upload or drag and drop</h4>
            <p class="text-sm text-slate-500">PNG, JPG, TTF, WOFF files allowed (Max. 10MB)</p>
            
            <div id="uploadProgress" class="mt-4 hidden max-w-sm mx-auto">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
                <p id="progressText" class="text-xs text-indigo-600 mt-2 font-bold">Uploading... 0%</p>
            </div>
        </div>
    </div>

    {{-- Files Grid --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        <h3 class="font-bold text-slate-700 mb-6 text-lg flex justify-between items-center">
            <span>📁 Your Uploaded Assets ({{ count($mediaFiles) }})</span>
            <button onclick="location.reload()" class="text-sm font-normal text-slate-500 hover:text-indigo-600 bg-slate-100 hover:bg-indigo-50 px-3 py-1.5 rounded-lg border border-slate-200 transition">
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
            </button>
        </h3>

        @if(count($mediaFiles) > 0)
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                @foreach($mediaFiles as $file)
                    @php
                        $isImage = in_array(strtolower($file['type']), ['png', 'jpg', 'jpeg', 'webp', 'gif']);
                        $isFont = in_array(strtolower($file['type']), ['ttf', 'otf', 'woff', 'woff2']);
                    @endphp
                    
                    <div class="media-card bg-slate-50 rounded-xl border border-slate-200 overflow-hidden relative group flex flex-col h-full">
                        
                        {{-- Preview Box --}}
                        <div class="h-32 bg-slate-100 border-b border-slate-200 flex items-center justify-center p-2 relative overflow-hidden">
                            @if($isImage)
                                <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}" class="max-h-full max-w-full object-contain">
                            @elseif($isFont)
                                <div class="text-center text-slate-400">
                                    <i class="fa-solid fa-font text-4xl text-purple-400 mb-2"></i>
                                    <p class="text-xs font-bold text-purple-600">FONT FILE</p>
                                </div>
                            @else
                                <i class="fa-solid fa-file text-4xl text-slate-300"></i>
                            @endif

                            {{-- Overlay Actions --}}
                            <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2 backdrop-blur-sm">
                                <button onclick="copyToClipboard('{{ $file['url'] }}', this)" class="bg-indigo-500 hover:bg-indigo-600 text-white p-2.5 rounded-lg shadow-lg transition" title="Copy URL">
                                    <i class="fa-solid fa-link"></i>
                                </button>
                                <a href="{{ $file['url'] }}" target="_blank" class="bg-slate-700 hover:bg-slate-800 text-white p-2.5 rounded-lg shadow-lg transition" title="Open in new tab">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                </a>
                            </div>
                        </div>

                        {{-- Details Box --}}
                        <div class="p-3 flex-grow flex flex-col justify-between bg-white">
                            <div>
                                <p class="text-xs font-bold text-slate-700 truncate mb-1" title="{{ $file['name'] }}">{{ $file['name'] }}</p>
                                <div class="flex justify-between text-[10px] text-slate-500 mb-3">
                                    <span>{{ $file['size'] }}</span>
                                    <span>{{ date('M d, y', $file['time']) }}</span>
                                </div>
                            </div>
                            
                            {{-- Action buttons --}}
                            <div class="flex border-t border-slate-100 pt-2 gap-2 mt-auto">
                                <button onclick="renameFile('{{ $file['name'] }}')" class="flex-1 text-center py-1.5 text-xs font-bold text-amber-600 bg-amber-50 rounded-md hover:bg-amber-100 transition">
                                    <i class="fa-solid fa-pen-to-square"></i> Rename
                                </button>
                                <button onclick="deleteFile('{{ $file['name'] }}')" class="flex-1 text-center py-1.5 text-xs font-bold text-rose-600 bg-rose-50 rounded-md hover:bg-rose-100 transition">
                                    <i class="fa-solid fa-trash-can"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 px-4">
                <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-4">
                    <i class="fa-regular fa-folder-open text-3xl"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-700 mb-1">No assets found</h3>
                <p class="text-sm text-slate-500">Upload your first PNG frame or Custom Font above</p>
            </div>
        @endif
    </div>
</div>

<script>
    const csrfToken = '{{ csrf_token() }}';

    // Copy to clipboard
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const icon = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i>';
            btn.classList.add('bg-emerald-500');
            btn.classList.remove('bg-indigo-500');
            setTimeout(() => {
                btn.innerHTML = icon;
                btn.classList.remove('bg-emerald-500');
                btn.classList.add('bg-indigo-500');
            }, 2000);
        });
    }

    // Rename file
    async function renameFile(oldName) {
        const newName = prompt('Enter new filename (with extension):', oldName);
        if (!newName || newName === oldName) return;

        try {
            const res = await fetch('{{ route('admin.media.rename') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ old_name: oldName, new_name: newName })
            });
            const data = await res.json();
            if (data.success) {
                alert('File renamed successfully!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            alert('Something went wrong!');
        }
    }

    // Delete file
    async function deleteFile(filename) {
        if (!confirm(`Are you sure you want to delete "${filename}"? Any templates using this asset will be broken.`)) return;

        try {
            const res = await fetch('{{ route('admin.media.delete') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ filename: filename })
            });
            const data = await res.json();
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            alert('Something went wrong!');
        }
    }

    // Drag and Drop Upload
    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('fileInput');
    const uploadProgress = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');

    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.add('dragover'), false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => dropzone.classList.remove('dragover'), false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        handleFiles(files);
    });

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length === 0) return;
        
        uploadProgress.classList.remove('hidden');
        dropzone.classList.add('pointer-events-none');
        
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', '{{ route('admin.media.upload') }}', true);
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
                progressText.innerText = 'Uploading... ' + Math.round(percentComplete) + '%';
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    progressText.innerText = 'Upload Complete! Reloading...';
                    progressText.classList.add('text-emerald-600');
                    progressBar.classList.add('bg-emerald-500');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    alert('Upload failed: ' + response.message);
                    resetUploader();
                }
            } else {
                alert('Upload failed. Check file types (PNG/JPG/TTF/WOFF) and size (10MB max).');
                resetUploader();
            }
        };

        xhr.onerror = function() {
            alert('Something went wrong during upload.');
            resetUploader();
        };

        xhr.send(formData);
    }

    function resetUploader() {
        uploadProgress.classList.add('hidden');
        dropzone.classList.remove('pointer-events-none');
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-emerald-500');
        progressText.classList.remove('text-emerald-600');
        progressText.innerText = 'Uploading... 0%';
        fileInput.value = '';
    }
</script>
@endsection
