@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-4xl mx-auto space-y-6">
        <div class="border-b border-white/10 pb-5">
            <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                Account Center
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-3">My Profile</h1>
            <p class="text-blue-200 mt-2">Update account details, password, and security settings.</p>
        </div>

        <div class="p-5 sm:p-8 bg-white/10 backdrop-blur-md border border-white/10 shadow rounded-2xl">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="p-5 sm:p-8 bg-white/10 backdrop-blur-md border border-white/10 shadow rounded-2xl">
            @include('profile.partials.update-password-form')
        </div>

        <div class="p-5 sm:p-8 bg-white/10 backdrop-blur-md border border-white/10 shadow rounded-2xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection