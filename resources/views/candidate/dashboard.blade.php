@extends('layouts.app')

@section('content')
<style>
    .fx-card { transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .fx-card:hover { transform: translateY(-5px); box-shadow: 0 18px 34px rgba(14,165,233,.22); border-color: rgba(255,255,255,.32); }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[28rem] h-[28rem] bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-25 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-[22rem] h-[22rem] bg-indigo-500 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <span class="inline-flex items-center text-cyan-300 text-xs font-bold uppercase tracking-wider">Candidate Workspace</span>
                <h1 class="text-4xl font-extrabold tracking-tight mt-2">Overview</h1>
                <p class="text-blue-100 mt-1">Welcome back, <span class="font-bold text-white">{{ Auth::user()->name }}</span>.</p>
            </div>
            <a href="{{ route('jobs.index') }}" class="fx-btn mt-4 md:mt-0 inline-flex items-center bg-cyan-500 hover:bg-cyan-400 text-slate-900 font-black py-3 px-6 rounded-xl shadow-xl">
                <i class="fa-solid fa-briefcase mr-2"></i> Browse Jobs
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2 bg-gradient-to-r from-indigo-600/90 to-blue-600/90 rounded-3xl p-1 shadow-2xl">
                <div class="h-full bg-slate-900/50 backdrop-blur-xl rounded-[20px] p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 p-6 opacity-10">
                        <i class="fa-solid fa-user-check text-9xl text-white"></i>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="bg-white/20 p-2 rounded-lg"><i class="fa-solid fa-heart-pulse"></i></span>
                            <h3 class="font-bold text-xl text-white">Career Pulse</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <a href="{{ route('candidate.applications') }}" class="block rounded-xl px-2 py-1 hover:bg-white/10 transition">
                                <span class="text-5xl font-black text-white tracking-tighter">{{ $todayInterviews ?? 0 }}</span>
                                <p class="text-blue-200 font-medium mt-1">Interviews Today</p>
                            </a>
                            <a href="{{ route('candidate.applications') }}" class="block rounded-xl px-2 py-1 hover:bg-white/10 transition">
                                <span class="text-5xl font-black text-white tracking-tighter">{{ $totalApplications ?? 0 }}</span>
                                <p class="text-blue-200 font-medium mt-1">Total Applications</p>
                            </a>
                            <a href="{{ route('candidate.applications') }}" class="block rounded-xl px-2 py-1 hover:bg-white/10 transition">
                                <span class="text-5xl font-black text-white tracking-tighter">{{ $pendingApplications ?? 0 }}</span>
                                <p class="text-blue-200 font-medium mt-1">In Process</p>
                            </a>
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('candidate.applications') }}" class="fx-btn inline-flex items-center gap-2 bg-white text-blue-900 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg">
                                Check My Applications <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="{{ route('candidate.profile.edit') }}" class="fx-btn inline-flex items-center gap-2 bg-white/15 border border-white/20 text-white px-6 py-3 rounded-xl font-bold hover:bg-white/25 transition">
                                Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 hover:bg-white/15 transition duration-300">
                <div class="p-3 bg-emerald-500/20 rounded-2xl text-emerald-400 border border-emerald-500/20 inline-block mb-6">
                    <i class="fa-solid fa-id-card text-2xl"></i>
                </div>

                <p class="text-blue-300 text-sm font-bold uppercase tracking-wider">Quick Action</p>
                <p class="text-2xl font-extrabold text-white mt-2">Complete Profile</p>
                <p class="text-slate-300 text-sm mt-1">A complete profile improves selection chances.</p>

                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="{{ route('candidate.profile.edit') }}" class="w-full flex items-center justify-between text-white font-bold hover:text-emerald-400 transition-colors">
                        <span>Open</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
            <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('jobs.index') }}" class="fx-card group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 transition-all">
                <div class="h-10 w-10 bg-indigo-500/20 text-indigo-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <h4 class="font-bold text-white">Find Jobs</h4>
                <p class="text-slate-400 text-xs">Browse live opportunities</p>
            </a>

            <a href="{{ route('candidate.applications') }}" class="fx-card group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 transition-all">
                <div class="h-10 w-10 bg-blue-500/20 text-blue-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <h4 class="font-bold text-white">My Applications</h4>
                <p class="text-slate-400 text-xs">Track statuses and interview updates</p>
            </a>

            <a href="{{ route('candidate.profile.edit') }}" class="fx-card group bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 transition-all">
                <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-user-pen"></i>
                </div>
                <h4 class="font-bold text-white">My Profile</h4>
                <p class="text-slate-400 text-xs">Update resume and skills</p>
            </a>
        </div>
    </div>
</div>
@endsection
