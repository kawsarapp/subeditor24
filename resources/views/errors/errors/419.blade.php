@extends('layouts.app_guest') {{-- Or your login layout --}}

@section('content')
<div class="h-screen flex flex-col items-center justify-center text-center bg-gray-100">
    <h1 class="text-6xl font-bold text-gray-300">419</h1>
    <h2 class="text-2xl font-bold text-gray-800 mt-4">Session Expired!</h2>
    <p class="text-gray-600 mt-2">For your security, your session has expired. Please log in again.</p>
    <a href="{{ route('login') }}" class="mt-6 px-6 py-3 bg-indigo-600 text-white rounded-lg font-bold hover:bg-indigo-700 transition">
        Log In
    </a>
</div>
@endsection