@extends('layouts.client')

@section('client_content')
<div class="relative z-10 max-w-6xl mx-auto" x-data="{ activePast: null }">

    {{-- HEADER --}}
    <div class="mb-6 border-b border-white/10 pb-6 flex flex-col md:flex-row justify-between items-end gap-4">
        <div>
            <a href="{{ route('client.interviews.calendar') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase mb-2">
                <i class="fa-solid fa-arrow-left mr-2"></i> Interview Calendar
            </a>
            <h1 class="text-4xl font-extrabold tracking-tight">All Past Interviews</h1>
            <p class="text-blue-200 mt-1">Review all your completed and past scheduled interviews and their feedbacks.</p>
        </div>
        <div class="bg-white/10 border border-white/10 px-5 py-2.5 rounded-2xl">
            <div class="text-xs text-blue-300 font-bold uppercase">Total Past</div>
            <div class="text-2xl font-black text-white">{{ $pastInterviews->total() }}</div>
        </div>
    </div>

    {{-- SEARCH/FILTER BAR --}}
    <div class="glass-card rounded-2xl p-4 mb-6 premium-form">
        <form method="GET" action="{{ route('client.interviews.past') }}" class="flex items-center gap-2">
            <div class="relative grow">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-white/60 text-sm pointer-events-none"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by candidate name, email, or job title..."
                       class="h-10 w-full pl-11 pr-3">
            </div>
            <button type="submit" class="h-10 px-5 bg-cyan-600 hover:bg-cyan-500 text-white rounded-xl font-bold text-sm shadow transition">
                <i class="fa-solid fa-filter"></i> Search
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('client.interviews.past') }}" class="h-10 w-10 bg-rose-500 hover:bg-rose-400 text-white rounded-xl flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            @endif
        </form>
    </div>

    {{-- PAST INTERVIEWS LIST --}}
    <div class="glass-card border !border-white/15 rounded-3xl overflow-hidden shadow-2xl">
        <div class="divide-y divide-white/10">
            @forelse($pastInterviews as $e)
                @php
                    $cand = $e->candidate;
                    $name = $cand ? trim(($cand->first_name??'').' '.($cand->last_name??'')) : ($e->candidateUser?->name ?? 'Candidate');
                @endphp
                <div class="px-6 py-4 hover:bg-white/5 cursor-pointer transition-colors" @click="activePast = (activePast === {{ $e->id }} ? null : {{ $e->id }})">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-bold text-white">
                                {{ $name }} 
                                <span class="text-xs text-blue-200 font-normal">· {{ $e->job->title ?? '—' }}</span>
                            </div>
                            <div class="text-[10px] text-slate-400 mt-0.5">
                                <i class="fa-regular fa-clock mr-1"></i> {{ $e->interview_at->format('d M Y, h:i A') }}
                                @if($e->meeting_provider)
                                    · <i class="fa-solid fa-video mr-1"></i> {{ ucfirst($e->meeting_provider) }}
                                @endif
                            </div>
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
                    <div x-show="activePast === {{ $e->id }}" x-transition class="mt-4 pt-4 border-t border-white/10 text-xs text-blue-100" style="display: none;" @click.stop>
                        @if($e->interview_feedback)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-3">
                                <div>
                                    <span class="text-cyan-300 font-bold uppercase block mb-1">Recommendation</span>
                                    <span class="inline-block px-2.5 py-1 rounded bg-[#03071a]/50 border border-white/10 text-white font-extrabold text-[10px] uppercase">
                                        {{ ucwords(str_replace('_', ' ', $e->interview_recommendation ?? 'No Recommendation')) }}
                                    </span>
                                </div>
                                <div>
                                    <span class="text-cyan-300 font-bold uppercase block mb-1">Detailed Feedback</span>
                                    <p class="italic bg-[#03071a]/50 border border-white/10 p-3.5 rounded text-slate-200 leading-relaxed">"{{ $e->interview_feedback }}"</p>
                                </div>
                            </div>
                        @else
                            <div class="text-slate-400 italic flex items-center justify-between py-1">
                                <span>No feedback submitted yet.</span>
                                <a href="{{ route('client.applications.feedback.create', $e) }}" class="inline-flex items-center gap-1.5 bg-cyan-600 hover:bg-cyan-500 text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow">
                                    <i class="fa-solid fa-plus"></i> Add Feedback
                                </a>
                            </div>
                        @endif

                        <!-- Multi-round details if any exist -->
                        @if($e->interviewRounds->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-white/10">
                                <span class="text-cyan-300 font-bold uppercase block mb-3"><i class="fa-solid fa-list-ol mr-1"></i> Round History ({{ $e->interviewRounds->count() }})</span>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($e->interviewRounds as $r)
                                        <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-3">
                                            <div class="flex items-center justify-between mb-1.5">
                                                <span class="font-bold text-white text-sm">Round {{ $r->round_number }}</span>
                                                <span class="text-[9px] px-2 py-0.5 rounded bg-white/10 text-blue-200 font-bold uppercase">{{ $r->status }}</span>
                                            </div>
                                            <div class="text-[10px] text-slate-400"><i class="fa-regular fa-calendar mr-1"></i> {{ $r->scheduled_at->format('d M Y, h:i A') }}</div>
                                            @if($r->recommendation)
                                                <div class="text-[10px] font-bold text-cyan-300 mt-2">Recommendation: {{ $r->recommendation }}</div>
                                            @endif
                                            @if($r->feedback)
                                                <div class="text-xs italic text-slate-300 mt-2 bg-[#03071a]/50 border border-white/5 p-2 rounded">"{{ $r->feedback }}"</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="px-6 py-16 text-center text-blue-200">
                    <i class="fa-regular fa-calendar-xmark text-5xl text-blue-300 mb-3"></i>
                    <p class="font-bold text-white">No past interviews found.</p>
                    @if(request()->filled('search'))
                        <p class="text-xs text-slate-400 mt-1">Try refining or resetting your search term.</p>
                    @endif
                </div>
            @endforelse
        </div>

        @if($pastInterviews->hasPages())
            <div class="p-4 border-t border-white/10 bg-[#03071a]/50">
                {{ $pastInterviews->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
