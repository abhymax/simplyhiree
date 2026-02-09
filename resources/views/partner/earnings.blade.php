@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-4xl mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 text-center shadow-2xl">
            <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                Partner Workspace
            </span>
            <h1 class="text-4xl font-extrabold mt-4">Your Earnings</h1>
            <p class="text-slate-200 mt-3">
                This page is kept for compatibility. Use the detailed earnings page from dashboard navigation.
            </p>

            <a href="{{ route('partner.earnings') }}" class="mt-6 inline-flex items-center px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 transition shadow-lg">
                Open Earnings Report
            </a>
        </div>
    </div>
</div>
@endsection