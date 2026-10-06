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
            <a href="{{ route('client.interviews.past') }}" class="mt-4 inline-flex items-center gap-2 rounded-xl border border-cyan-300/30 bg-cyan-400/10 px-4 py-2.5 text-sm font-extrabold text-cyan-100 transition hover:bg-cyan-400/20 md:mt-0"><i class="fa-regular fa-clock"></i> Past Interviews</a>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        @php
            $now = now();
            $todayStart = $now->copy()->startOfDay();
            $upcoming = $events->filter(fn ($e) => $e->interview_at && $e->interview_at->gte($todayStart));
            $past     = $events->filter(fn ($e) => $e->interview_at && $e->interview_at->lt($todayStart));
            $byDay    = $upcoming->groupBy(fn ($e) => $e->interview_at->format('Y-m-d'));
            $rangeLabels = [
                'upcoming' => 'Upcoming interviews',
                'today' => "Today's interviews",
                'past7' => 'Interviews from the past 7 days',
                'all' => 'All interviews',
            ];
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <a href="{{ route('client.interviews.calendar', ['range' => 'upcoming', 'month' => request('month')]) }}#interview-results" aria-label="View {{ $upcoming->count() }} upcoming interviews" class="group glass-card rounded-2xl p-4 border !border-blue-500/30 shadow-md transition duration-200 hover:-translate-y-1 hover:!border-blue-300 hover:shadow-lg hover:shadow-blue-500/20 {{ $selectedRange === 'upcoming' ? '!border-blue-300 ring-2 ring-blue-400/30 bg-blue-500/10' : '' }}">
                <div class="flex items-start justify-between"><div><p class="text-xs uppercase font-extrabold text-blue-200">Upcoming</p><p class="text-3xl font-black text-white mt-1">{{ $upcoming->count() }}</p></div><i class="fa-solid fa-arrow-right text-blue-300 opacity-60 transition group-hover:translate-x-1 group-hover:opacity-100"></i></div>
            </a>
            <a href="{{ route('client.interviews.calendar', ['range' => 'today', 'month' => request('month')]) }}#interview-results" aria-label="View today's interviews" class="group glass-card rounded-2xl p-4 border !border-amber-500/30 shadow-md transition duration-200 hover:-translate-y-1 hover:!border-amber-300 hover:shadow-lg hover:shadow-amber-500/20 {{ $selectedRange === 'today' ? '!border-amber-300 ring-2 ring-amber-400/30 bg-amber-500/10' : '' }}">
                <div class="flex items-start justify-between"><div><p class="text-xs uppercase font-extrabold text-amber-200">Today</p><p class="text-3xl font-black text-amber-200 mt-1">{{ $events->filter(fn($e)=>$e->interview_at && $e->interview_at->isToday())->count() }}</p></div><i class="fa-solid fa-arrow-right text-amber-300 opacity-60 transition group-hover:translate-x-1 group-hover:opacity-100"></i></div>
            </a>
            <a href="{{ route('client.interviews.calendar', ['range' => 'past7', 'month' => request('month')]) }}#interview-results" aria-label="View interviews from the past 7 days" class="group glass-card rounded-2xl p-4 border !border-emerald-500/30 shadow-md transition duration-200 hover:-translate-y-1 hover:!border-emerald-300 hover:shadow-lg hover:shadow-emerald-500/20 {{ $selectedRange === 'past7' ? '!border-emerald-300 ring-2 ring-emerald-400/30 bg-emerald-500/10' : '' }}">
                <div class="flex items-start justify-between"><div><p class="text-xs uppercase font-extrabold text-emerald-200">Past 7 days</p><p class="text-3xl font-black text-emerald-200 mt-1">{{ $past->filter(fn($e) => $e->interview_at->gte($todayStart->copy()->subDays(7)))->count() }}</p></div><i class="fa-solid fa-arrow-right text-emerald-300 opacity-60 transition group-hover:translate-x-1 group-hover:opacity-100"></i></div>
            </a>
            <a href="{{ route('client.interviews.calendar', ['range' => 'all', 'month' => request('month')]) }}#interview-results" aria-label="View all {{ $events->count() }} interviews" class="group glass-card rounded-2xl p-4 border !border-violet-500/30 shadow-md transition duration-200 hover:-translate-y-1 hover:!border-violet-300 hover:shadow-lg hover:shadow-violet-500/20 {{ $selectedRange === 'all' ? '!border-violet-300 ring-2 ring-violet-400/30 bg-violet-500/10' : '' }}">
                <div class="flex items-start justify-between"><div><p class="text-xs uppercase font-extrabold text-violet-200">All time</p><p class="text-3xl font-black text-white mt-1">{{ $events->count() }}</p></div><i class="fa-solid fa-arrow-right text-violet-300 opacity-60 transition group-hover:translate-x-1 group-hover:opacity-100"></i></div>
            </a>
        </div>

        @if($selectedRange && $filteredInterviews)
            <section id="interview-results" class="mb-8 scroll-mt-24 overflow-hidden rounded-3xl border border-white/15 bg-slate-950/45 shadow-2xl shadow-blue-950/30">
                <div class="flex flex-col gap-2 border-b border-white/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-xs font-extrabold uppercase text-cyan-300">Selected view</p>
                        <h2 class="mt-1 text-xl font-black text-white">{{ $rangeLabels[$selectedRange] }}</h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="rounded-full border border-white/15 bg-white/5 px-3 py-1 text-xs font-bold text-blue-100">{{ $filteredInterviews->total() }} record{{ $filteredInterviews->total() === 1 ? '' : 's' }}</span>
                        <a href="{{ route('client.interviews.calendar', array_filter(['month' => request('month')])) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-white/15 text-slate-300 transition hover:bg-white/10 hover:text-white" title="Clear interview filter" aria-label="Clear interview filter"><i class="fa-solid fa-xmark"></i></a>
                    </div>
                </div>

                <div class="divide-y divide-white/10">
                    @forelse($filteredInterviews as $event)
                        @php
                            $filteredCandidate = $event->candidate;
                            $filteredName = $filteredCandidate
                                ? trim(($filteredCandidate->first_name ?? '').' '.($filteredCandidate->last_name ?? ''))
                                : ($event->candidateUser?->name ?? 'Candidate');
                        @endphp
                        <div class="flex flex-col gap-4 px-5 py-4 transition hover:bg-white/5 md:flex-row md:items-center md:justify-between">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 font-black text-white shadow-lg shadow-cyan-500/15">{{ strtoupper(substr($filteredName, 0, 1)) }}</div>
                                <div class="min-w-0">
                                    <a href="{{ route('client.applications.show', $event) }}" class="font-bold text-white transition hover:text-cyan-200">{{ $filteredName }}</a>
                                    <p class="truncate text-sm text-blue-200">{{ $event->job->title ?? 'Deleted job' }}</p>
                                    <p class="mt-1 text-xs text-slate-400"><i class="fa-regular fa-calendar mr-1 text-cyan-300"></i>{{ $event->interview_at->format('d M Y') }} <span class="mx-1 text-white/20">|</span> <i class="fa-regular fa-clock mr-1 text-cyan-300"></i>{{ $event->interview_at->format('h:i A') }}</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                @if($event->meeting_link && $event->interview_at->gte($todayStart))
                                    <a href="{{ $event->meeting_link }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-500"><i class="fa-solid fa-video"></i> Join</a>
                                @endif
                                <a href="{{ route('client.applications.show', $event) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-cyan-300/25 bg-cyan-400/10 px-3 py-2 text-xs font-bold text-cyan-100 transition hover:bg-cyan-400/20"><i class="fa-regular fa-eye"></i> View details</a>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-12 text-center">
                            <i class="fa-regular fa-calendar-xmark mb-3 text-4xl text-blue-300"></i>
                            <p class="font-bold text-white">No interviews in this view</p>
                            <p class="mt-1 text-sm text-blue-200">Choose another summary card to see its records.</p>
                        </div>
                    @endforelse
                </div>

                @if($filteredInterviews->hasPages())
                    <div class="border-t border-white/10 bg-slate-950/40 p-4">
                        {{ $filteredInterviews->onEachSide(1)->links() }}
                    </div>
                @endif
            </section>
        @endif

        <section class="mb-8 overflow-hidden rounded-3xl border border-white/15 bg-slate-950/35 shadow-2xl shadow-blue-950/30">
            <div class="flex items-center justify-between gap-4 border-b border-white/10 px-5 py-4">
                <a href="{{ route('client.interviews.calendar', ['month' => $calendarMonth->copy()->subMonth()->format('Y-m')]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/5 text-white transition hover:-translate-x-0.5 hover:bg-white/15" aria-label="Previous month">
                    <i class="fa-solid fa-chevron-left"></i>
                </a>
                <div class="text-center">
                    <p class="text-xs font-extrabold uppercase tracking-widest text-cyan-300">Interview schedule</p>
                    <h2 class="mt-1 text-xl font-black text-white">{{ $calendarMonth->format('F Y') }}</h2>
                </div>
                <a href="{{ route('client.interviews.calendar', ['month' => $calendarMonth->copy()->addMonth()->format('Y-m')]) }}" class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-white/15 bg-white/5 text-white transition hover:translate-x-0.5 hover:bg-white/15" aria-label="Next month">
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <div style="min-width: 760px;">
                    <div class="grid border-b border-white/10 bg-slate-950/45" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                        @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $weekday)
                            <div class="px-3 py-2.5 text-center text-[10px] font-extrabold uppercase tracking-widest text-blue-200">{{ $weekday }}</div>
                        @endforeach
                    </div>
                    <div class="grid" style="grid-template-columns: repeat(7, minmax(0, 1fr));">
                        @foreach($calendarDays as $day)
                            @php
                                $dayEvents = $eventsByDate->get($day->format('Y-m-d'), collect());
                                $isCurrentMonth = $day->month === $calendarMonth->month;
                                $isToday = $day->isToday();
                            @endphp
                            <div class="min-h-[116px] border-b border-r border-white/[0.08] p-2.5 transition hover:bg-white/[0.06] {{ $isCurrentMonth ? 'bg-transparent' : 'bg-slate-950/30' }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-lg text-xs font-black {{ $isToday ? 'bg-cyan-400 text-slate-950 shadow-lg shadow-cyan-400/30' : ($isCurrentMonth ? 'text-white' : 'text-slate-600') }}">{{ $day->day }}</span>
                                    @if($dayEvents->isNotEmpty())
                                        <span class="rounded-full bg-amber-400/15 px-2 py-0.5 text-[9px] font-extrabold text-amber-200">{{ $dayEvents->count() }}</span>
                                    @endif
                                </div>
                                <div class="space-y-1.5">
                                    @foreach($dayEvents->take(2) as $event)
                                        @php
                                            $candidate = $event->candidate;
                                            $candidateName = $candidate ? trim(($candidate->first_name ?? '').' '.($candidate->last_name ?? '')) : ($event->candidateUser?->name ?? 'Candidate');
                                        @endphp
                                        <a href="{{ route('client.applications.show', $event) }}" class="block truncate rounded-lg border border-cyan-300/15 bg-cyan-400/10 px-2 py-1.5 text-[10px] font-bold text-cyan-100 transition hover:border-cyan-300/35 hover:bg-cyan-400/20" title="{{ $event->interview_at->format('h:i A') }} - {{ $candidateName }}">
                                            <span class="text-cyan-300">{{ $event->interview_at->format('h:i A') }}</span> {{ $candidateName }}
                                        </a>
                                    @endforeach
                                    @if($dayEvents->count() > 2)
                                        <details class="group/more rounded-lg border border-amber-300/20 bg-amber-400/[0.08] px-1.5 py-1">
                                            <summary class="flex cursor-pointer list-none items-center justify-between gap-1 text-[9px] font-extrabold text-amber-100 transition hover:text-white [&::-webkit-details-marker]:hidden">
                                                <span>+{{ $dayEvents->count() - 2 }} more</span>
                                                <i class="fa-solid fa-chevron-down text-[8px] transition-transform group-open/more:rotate-180"></i>
                                            </summary>
                                            <div class="mt-1.5 space-y-1.5 border-t border-white/10 pt-1.5">
                                                @foreach($dayEvents->skip(2) as $event)
                                                    @php
                                                        $candidate = $event->candidate;
                                                        $candidateName = $candidate ? trim(($candidate->first_name ?? '').' '.($candidate->last_name ?? '')) : ($event->candidateUser?->name ?? 'Candidate');
                                                    @endphp
                                                    <a href="{{ route('client.applications.show', $event) }}" class="block truncate rounded-md border border-cyan-300/15 bg-cyan-400/10 px-1.5 py-1 text-[9px] font-bold text-cyan-100 transition hover:border-cyan-300/40 hover:bg-cyan-400/20" title="{{ $event->interview_at->format('h:i A') }} - {{ $candidateName }}">
                                                        <span class="text-cyan-300">{{ $event->interview_at->format('h:i A') }}</span> {{ $candidateName }}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </details>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

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
                                <a href="{{ route('client.applications.show', $e->id) }}" class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold shrink-0 hover:scale-105 transition-transform" title="View details for {{ $name }}">
                                    {{ strtoupper(substr($name,0,1)) }}
                                </a>
                                <div class="min-w-0">
                                    <a href="{{ route('client.applications.show', $e->id) }}" class="font-bold text-cyan-300 hover:text-cyan-200 hover:underline transition-colors">{{ $name }}</a>
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
                                    <div class="font-bold text-white"><a href="{{ route('client.applications.show', $e->id) }}" class="text-cyan-300 hover:text-cyan-200 hover:underline transition-colors">{{ $name }}</a> <span class="text-xs text-blue-200 font-normal">· {{ $e->job->title ?? '—' }}</span></div>
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
