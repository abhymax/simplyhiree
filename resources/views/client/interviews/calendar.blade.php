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
            <div class="glass-card rounded-2xl p-4 border !border-blue-500/30 shadow-md"><p class="text-xs uppercase font-extrabold text-blue-200">Upcoming</p><p class="text-3xl font-black text-white mt-1">{{ $upcoming->count() }}</p></div>
            <div class="glass-card rounded-2xl p-4 border !border-amber-500/30 shadow-md"><p class="text-xs uppercase font-extrabold text-amber-200">Today</p><p class="text-3xl font-black text-amber-200 mt-1">{{ $events->filter(fn($e)=>$e->interview_at && $e->interview_at->isToday())->count() }}</p></div>
            <div class="glass-card rounded-2xl p-4 border !border-emerald-500/30 shadow-md"><p class="text-xs uppercase font-extrabold text-emerald-200">Past 7 days</p><p class="text-3xl font-black text-emerald-200 mt-1">{{ $past->filter(fn($e)=>$e->interview_at->gt(now()->subDays(7)))->count() }}</p></div>
            <div class="glass-card rounded-2xl p-4 border !border-slate-500/30 shadow-md"><p class="text-xs uppercase font-extrabold text-slate-300">All time</p><p class="text-3xl font-black text-white mt-1">{{ $events->count() }}</p></div>
        </div>

        {{-- Upcoming, grouped by day --}}
        <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-3">
            <span class="w-1.5 h-7 bg-cyan-400 rounded-full"></span>
            <i class="fa-regular fa-calendar text-cyan-300"></i> Upcoming Interviews
        </h2>

        @forelse($byDay as $day => $list)
            <div class="glass-card border !border-white/15 rounded-3xl overflow-hidden mb-5">
                <div class="px-6 py-3 bg-[#03071a]/70 text-cyan-200 font-bold tracking-wider text-sm uppercase border-b border-white/10">
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
            <div class="glass-card border !border-white/15 rounded-3xl p-10 text-center mb-6">
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
            <div class="glass-card border !border-white/15 rounded-3xl overflow-hidden shadow-xl" x-data="{ activePast: null }">
                <div class="divide-y divide-white/10">
                    @foreach($past->take(5) as $e)
                        @php
                            $cand = $e->candidate;
                            $name = $cand ? trim(($cand->first_name??'').' '.($cand->last_name??'')) : ($e->candidateUser?->name ?? 'Candidate');
                        @endphp
                        <div class="px-6 py-4 hover:bg-white/5 cursor-pointer transition-colors" @click="activePast = (activePast === {{ $e->id }} ? null : {{ $e->id }})">
                            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-bold text-white">{{ $name }} <span class="text-xs text-blue-200 font-normal">· {{ $e->job->title ?? '—' }}</span></div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $e->interview_at->format('d M Y, h:i A') }}</div>
                                </div>
                                <div class="flex items-center gap-2" @click.stop>
                                    @if($e->interview_rating)
                                        <span class="text-amber-300 text-sm">{{ str_repeat('★', $e->interview_rating) }}{{ str_repeat('☆', 5 - $e->interview_rating) }}</span>
                                    @else
                                        <a href="{{ route('client.applications.feedback.create', $e) }}" class="text-xs text-cyan-300 hover:text-white underline">Add feedback</a>
                                    @endif
                                </div>
                            </div>

                            <!-- Collapsible Feedback Drawer -->
                            <div x-show="activePast === {{ $e->id }}" x-transition class="mt-3 pt-3 border-t border-white/10 text-xs text-blue-100" style="display: none;" @click.stop>
                                @if($e->interview_feedback)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <span class="text-cyan-300 font-bold uppercase block mb-1">Recommendation</span>
                                            <span class="inline-block px-2.5 py-1 rounded bg-[#03071a]/50 border border-white/10 text-white font-extrabold text-[10px] uppercase">
                                                {{ ucwords(str_replace('_', ' ', $e->interview_recommendation ?? 'No Recommendation')) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-cyan-300 font-bold uppercase block mb-1">Detailed Feedback</span>
                                            <p class="italic bg-[#03071a]/50 border border-white/10 p-2 rounded text-slate-200">"{{ $e->interview_feedback }}"</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-slate-400 italic flex items-center justify-between">
                                        <span>No feedback submitted yet.</span>
                                        <a href="{{ route('client.applications.feedback.create', $e) }}" class="inline-flex items-center gap-1.5 bg-cyan-600 hover:bg-cyan-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg">
                                            <i class="fa-solid fa-plus"></i> Add Feedback
                                        </a>
                                    </div>
                                @endif

                                <!-- Multi-round details if any exist -->
                                @if($e->interviewRounds->isNotEmpty())
                                    <div class="mt-3 pt-3 border-t border-white/10">
                                        <span class="text-cyan-300 font-bold uppercase block mb-2">Round History ({{ $e->interviewRounds->count() }})</span>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                            @foreach($e->interviewRounds as $r)
                                                <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-2.5">
                                                    <div class="flex items-center justify-between mb-1.5">
                                                        <span class="font-bold text-white">Round {{ $r->round_number }}</span>
                                                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-white/10 text-blue-200 uppercase font-bold">{{ $r->status }}</span>
                                                    </div>
                                                    <div class="text-[10px] text-slate-400">{{ $r->scheduled_at->format('d M Y, h:i A') }}</div>
                                                    @if($r->recommendation)
                                                        <div class="text-[9px] font-bold text-cyan-300 mt-1">Rec: {{ $r->recommendation }}</div>
                                                    @endif
                                                    @if($r->feedback)
                                                        <div class="text-[10px] italic text-slate-300 mt-1">"{{ $r->feedback }}"</div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 bg-[#03071a]/50 text-center border-t border-white/10">
                    <a href="{{ route('client.interviews.past') }}" class="inline-flex items-center gap-2 text-cyan-300 hover:text-white font-bold text-sm">
                        <i class="fa-solid fa-calendar-days"></i> View All Past Interviews &rarr;
                    </a>
                </div>
            </div>
        @endif
    </div>
@endsection
