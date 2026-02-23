@extends('layouts.app')

@section('content')
<style>
    .fx-card {
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background-color .25s ease;
    }
    .fx-card:hover {
        transform: translateY(-6px) scale(1.01);
        box-shadow: 0 20px 40px rgba(14, 165, 233, 0.22);
        border-color: rgba(255, 255, 255, 0.35);
    }
    .fx-btn {
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }
    .fx-btn:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 10px 24px rgba(59, 130, 246, 0.35);
        filter: brightness(1.05);
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if (session('success'))
            <div class="mb-8 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md">
                <i class="fa-solid fa-circle-check mr-3 text-2xl"></i>
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Partner Workspace
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">
                    Overview
                </h1>
                <p class="text-blue-200 mt-2 text-lg">
                    Welcome back, <span class="text-white font-semibold">{{ Auth::user()->name }}</span>.
                </p>
            </div>

            <div class="mt-6 md:mt-0">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-2xl flex items-center gap-4">
                    <div class="p-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg shadow-lg">
                        <i class="fa-regular fa-calendar text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-300 font-bold uppercase">Today's Date</p>
                        <p class="text-white font-bold">{{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <div class="col-span-1 lg:col-span-2 bg-gradient-to-r from-indigo-600/90 to-blue-600/90 rounded-3xl p-1 shadow-2xl">
                <div class="h-full bg-slate-900/50 backdrop-blur-xl rounded-[20px] p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 p-6 opacity-10">
                        <i class="fa-solid fa-users-viewfinder text-9xl text-white"></i>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="bg-white/20 p-2 rounded-lg"><i class="fa-solid fa-heart-pulse"></i></span>
                            <h3 class="font-bold text-xl text-white">Daily Pulse</h3>
                        </div>

                        <a href="{{ route('partner.applications') }}" class="inline-flex items-baseline gap-4 rounded-xl px-2 py-1 hover:bg-white/10 transition">
                            <span class="text-6xl font-black text-white tracking-tighter">{{ $todayInterviews ?? 0 }}</span>
                            <span class="text-blue-200 font-medium">Interviews Today</span>
                        </a>

                        <div class="mt-8">
                            <a href="{{ route('partner.applications') }}" class="fx-btn inline-flex items-center gap-2 bg-white text-blue-900 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg">
                                Check Application Status <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 hover:bg-white/15 transition duration-300">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-emerald-500/20 rounded-2xl text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-user-plus text-2xl"></i>
                    </div>
                </div>

                <p class="text-blue-300 text-sm font-bold uppercase tracking-wider">Quick Action</p>
                <p class="text-2xl font-extrabold text-white mt-2">Add Candidate</p>
                <p class="text-slate-300 text-sm mt-1">Start the candidate onboarding flow.</p>

                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="{{ route('partner.candidates.check') }}" class="w-full flex items-center justify-between text-white font-bold hover:text-emerald-400 transition-colors">
                        <span>Open</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
            <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <a href="{{ route('partner.profile.business') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-blue-500/20 text-blue-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <h4 class="font-bold text-white">My Profile</h4>
                <p class="text-slate-400 text-xs">Manage business details</p>
            </a>

            <a href="{{ route('partner.candidates.index') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h4 class="font-bold text-white">Candidate Pool</h4>
                <p class="text-slate-400 text-xs">View and manage candidates</p>
            </a>

            <a href="{{ route('partner.jobs') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-indigo-500/20 text-indigo-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <h4 class="font-bold text-white">Available Jobs</h4>
                <p class="text-slate-400 text-xs">Browse approved roles</p>
            </a>

            <a href="{{ route('partner.applications') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-purple-500/20 text-purple-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <h4 class="font-bold text-white">Applications</h4>
                <p class="text-slate-400 text-xs">Track submissions</p>
            </a>

            <a href="{{ route('partner.earnings') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-amber-500/20 text-amber-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <h4 class="font-bold text-white">Earnings</h4>
                <p class="text-slate-400 text-xs">Track payouts</p>
            </a>
        </div>
    </div>
</div>
@endsection
