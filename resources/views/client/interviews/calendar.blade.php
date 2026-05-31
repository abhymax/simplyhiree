@extends('layouts.client')

@section('client_content')
    <div class="relative z-10 max-w-6xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
            <div>
                <a href="{{ route('client.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">Interview Calendar</h1>
                <p class="text-blue-200 mt-2">All your scheduled and past interviews in one place.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        @php
            $now = now();
            $upcoming = $events->filter(fn ($e) => $e->interview_at && $e->interview_at->isFuture());
            $past     = $events->filter(fn ($e) => $e->interview_at && $e->interview_at->isPast());
            $byDay    = $upcoming->groupBy(fn ($e) => $e->interview_at->format('Y-m-d'));
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-500/15 border border-blue-400/30 rounded-2xl p-4"><p class="text-xs uppercase font-bold text-blue-200">Upcoming</p><p class="text-3xl font-extrabold text-white">{{ $upcoming->count() }}</p></div>
            <div class="bg-amber-500/10 border border-amber-400/30 rounded-2xl p-4"><p class="text-xs uppercase font-bold text-amber-200">Today</p><p class="text-3xl font-extrabold text-amber-200">{{ $events->filter(fn($e)=>$e->interview_at && $e->interview_at->isToday())->count() }}</p></div>
            <div class="bg-emerald-500/10 border border-emerald-400/30 rounded-2xl p-4"><p class="text-xs uppercase font-bold text-emerald-200">Past 7 days</p><p class="text-3xl font-extrabold text-emerald-200">{{ $past->filter(fn($e)=>$e->interview_at->gt(now()->subDays(7)))->count() }}</p></div>
            <div class="bg-slate-500/15 border border-slate-400/30 rounded-2xl p-4"><p class="text-xs uppercase font-bold text-slate-300">All time</p><p class="text-3xl font-extrabold text-white">{{ $events->count() }}</p></div>
        </div>

        {{-- Upcoming, grouped by day --}}
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-3">
            <span class="w-1.5 h-7 bg-cyan-400 rounded-full"></span>
            <i class="fa-regular fa-calendar text-cyan-300"></i> Upcoming Interviews
        </h2>

        @forelse($byDay as $day => $list)
            <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden mb-5">
                <div class="px-6 py-3 bg-blue-950/60 text-cyan-200 font-bold tracking-wider text-sm uppercase">
                    <i class="fa-regular fa-calendar-days mr-2"></i> {{ \Carbon\Carbon::parse($day)->format('l, d M Y') }}
                </div>
                <div class="divide-y divide-white/10">
                    @foreach($list as $e)
                        @php
                            $cand = $e->candidate;
                            $name = $cand ? trim(($cand->first_name??'').' '.($cand->last_name??'')) : ($e->candidateUser?->name ?? 'Candidate');
                        @endphp
                        <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shrink-0">{{ strtoupper(substr($name,0,1)) }}</div>
                                <div class="min-w-0">
                                    <div class="font-bold text-white">{{ $name }}</div>
                                    <div class="text-xs text-blue-200">{{ $e->job->title ?? 'Deleted job' }}</div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        <i class="fa-regular fa-clock mr-1"></i> {{ $e->interview_at->format('h:i A') }}
                                        @if($e->meeting_provider)
                                            · <i class="fa-solid fa-video mr-1"></i> {{ ucfirst($e->meeting_provider) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if($e->meeting_link)
                                    <a href="{{ $e->meeting_link }}" target="_blank" class="inline-flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                                        <i class="fa-solid fa-video"></i> Join
                                    </a>
                                @endif
                                <a href="{{ route('client.applications.interview.edit', $e) }}" class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                                    <i class="fa-solid fa-pen"></i> Edit
                                </a>
                                <a href="{{ route('client.applications.feedback.create', $e) }}" class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg">
                                    <i class="fa-regular fa-clipboard"></i> Feedback
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white/5 border border-white/10 rounded-3xl p-10 text-center mb-6">
                <i class="fa-regular fa-calendar text-5xl text-blue-300 mb-3"></i>
                <p class="text-white font-bold text-lg">No upcoming interviews</p>
                <p class="text-blue-200 text-sm mt-1">Approve candidates and schedule interviews from the Applicants page.</p>
            </div>
        @endforelse

        {{-- Past --}}
        @if($past->isNotEmpty())
            <h2 class="text-xl font-bold text-white mb-4 mt-8 flex items-center gap-3">
                <span class="w-1.5 h-7 bg-slate-400 rounded-full"></span>
                <i class="fa-regular fa-clock-rotate-left text-slate-300"></i> Past Interviews
            </h2>
            <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
                <div class="divide-y divide-white/10">
                    @foreach($past->take(20) as $e)
                        @php
                            $cand = $e->candidate;
                            $name = $cand ? trim(($cand->first_name??'').' '.($cand->last_name??'')) : ($e->candidateUser?->name ?? 'Candidate');
                        @endphp
                        <div class="px-6 py-3 flex flex-col md:flex-row md:items-center md:justify-between gap-3 hover:bg-white/5">
                            <div class="min-w-0">
                                <div class="font-bold text-white">{{ $name }} <span class="text-xs text-blue-200 font-normal">· {{ $e->job->title ?? '—' }}</span></div>
                                <div class="text-[10px] text-slate-400 mt-0.5">{{ $e->interview_at->format('d M Y, h:i A') }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($e->interview_rating)
                                    <span class="text-amber-300 text-sm">{{ str_repeat('★', $e->interview_rating) }}{{ str_repeat('☆', 5 - $e->interview_rating) }}</span>
                                @else
                                    <a href="{{ route('client.applications.feedback.create', $e) }}" class="text-xs text-cyan-300 hover:text-white underline">Add feedback</a>
                                @endif
                            </div>
                        </div>
                    @endforeach
            </div>
        @endif
    </div>
@endsection
