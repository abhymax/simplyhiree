@extends('layouts.client')

@section('client_content')
@php
    $candidate     = $application->candidate;
    $candidateUser = $application->candidateUser;
    $name = $candidate
        ? trim(($candidate->first_name ?? '').' '.($candidate->last_name ?? ''))
        : ($candidateUser->name ?? 'Candidate');
    $email     = $candidate->email ?? $candidateUser->email ?? '—';
    $phone     = $candidate->phone_number ?? $candidateUser->profile->phone_number ?? '—';
    $location  = $candidate->location ?? $candidateUser->profile->location ?? '—';
    $resume    = $candidate->resume_path ?? $candidateUser->profile->resume_path ?? null;
    $initial   = strtoupper(substr($name ?: 'C', 0, 1));
    $partner   = $candidate?->partner;
    $skills    = $candidate->skills ?? null;
    $exp       = $candidate->experience_status ?? null;
    $education = $candidate->education_level ?? null;
    $ctcRaw    = $candidate->expected_ctc ?? null;
    $ctcNum    = is_numeric($ctcRaw) ? (float) $ctcRaw : (float) preg_replace('/[^0-9.]/', '', (string) $ctcRaw);
@endphp

    <div class="relative z-10 max-w-6xl mx-auto">

        {{-- Back link --}}
        <a href="javascript:history.back()"
           class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-4">
           <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>

        {{-- Hero card --}}
        <div class="glass-card rounded-3xl p-8 shadow-2xl mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="h-20 w-20 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-3xl shadow-lg ring-4 ring-white/10">{{ $initial }}</div>
                    <div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight">{{ $name }}</h1>
                        <div class="mt-2 flex flex-wrap gap-3 text-sm">
                            <span class="text-blue-100"><i class="fa-regular fa-envelope mr-1.5 text-cyan-300"></i> {{ $email }}</span>
                            <span class="text-blue-100"><i class="fa-solid fa-phone mr-1.5 text-cyan-300"></i> {{ $phone }}</span>
                            <span class="text-blue-100"><i class="fa-solid fa-location-dot mr-1.5 text-cyan-300"></i> {{ $location }}</span>
                        </div>
                        <div class="mt-2 text-[11px] text-slate-300">
                            {{ $application->application_code ?? 'SH-APP-'.str_pad($application->id, 6, '0', STR_PAD_LEFT) }}
                            @if($candidate)
                                · {{ $candidate->candidate_code ?? '' }}
                            @endif
                            · Applied {{ $application->created_at->format('M d, Y') }}
                        </div>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                    @if($resume)
                        <a href="{{ asset('storage/'.$resume) }}" target="_blank"
                           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2.5 rounded-xl font-bold text-sm shadow-lg transition">
                            <i class="fa-solid fa-file-arrow-down"></i> Download CV
                        </a>
                    @endif
                    @if($partner)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-600/30 border border-purple-400/40 text-purple-100 text-xs font-bold">
                            <i class="fa-solid fa-handshake"></i> Sourced by {{ $partner->name }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-700/50 border border-slate-500/40 text-slate-100 text-xs font-bold">
                            <i class="fa-solid fa-globe"></i> Direct Application
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Applied Job --}}
            <div class="lg:col-span-1 glass-card rounded-3xl p-6 shadow-xl">
                <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3"><i class="fa-solid fa-briefcase mr-1"></i> Applied For</h3>
                <div class="font-extrabold text-white text-lg leading-tight">{{ $application->job->title ?? 'Deleted Job' }}</div>
                <div class="text-blue-200 text-sm mt-1">{{ $application->job->company_name ?? '—' }}</div>
                <div class="text-blue-100 text-xs mt-3 space-y-1">
                    <div><i class="fa-solid fa-location-dot text-rose-400 mr-1.5"></i> {{ $application->job->location ?? '—' }}</div>
                    <div><i class="fa-solid fa-tag text-amber-400 mr-1.5"></i> {{ $application->job->job_type ?? '—' }}</div>
                </div>
            </div>

            {{-- Status --}}
            <div class="lg:col-span-2 glass-card rounded-3xl p-6 shadow-xl">
                <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3"><i class="fa-solid fa-list-check mr-1"></i> Hiring Pipeline</h3>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
                    <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-3">
                        <div class="text-[10px] uppercase font-bold text-slate-300">Application</div>
                        <div class="text-white font-bold mt-1">{{ $application->status ?? '—' }}</div>
                    </div>
                    <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-3">
                        <div class="text-[10px] uppercase font-bold text-slate-300">Hiring</div>
                        <div class="text-white font-bold mt-1">{{ $application->hiring_status ?: 'Pending Action' }}</div>
                    </div>
                    <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-3">
                        <div class="text-[10px] uppercase font-bold text-slate-300">Joining</div>
                        <div class="text-white font-bold mt-1">{{ $application->joined_status ?: '—' }}</div>
                    </div>
                    @if($application->interview_at)
                        <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-3">
                            <div class="text-[10px] uppercase font-bold text-slate-300">Interview</div>
                            <div class="text-white font-bold mt-1">{{ $application->interview_at->format('M d, Y g:i A') }}</div>
                        </div>
                    @endif
                    @if($application->joining_date)
                        <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-3">
                            <div class="text-[10px] uppercase font-bold text-slate-300">Joining Date</div>
                            <div class="text-white font-bold mt-1">{{ $application->joining_date->format('M d, Y') }}</div>
                        </div>
                    @endif
                    @if($application->left_at)
                        <div class="bg-[#03071a]/50 border border-white/10 rounded-xl p-3">
                            <div class="text-[10px] uppercase font-bold text-slate-300">Left On</div>
                            <div class="text-white font-bold mt-1">{{ $application->left_at->format('M d, Y') }}</div>
                        </div>
                    @endif
                </div>

                {{-- Immediate Actions Section --}}
                <div class="mt-6 pt-5 border-t border-white/10">
                    <h4 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3"><i class="fa-solid fa-bolt mr-1"></i> Immediate Actions</h4>
                    <div class="flex flex-wrap gap-3">
                        @if(empty($application->joined_status) && $application->hiring_status !== 'Client Rejected')
                            @if($application->hiring_status == 'Selected')
                                <a href="{{ route('client.applications.select.edit', $application) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-lg transition">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit Join / CTC
                                </a>
                                <form action="{{ route('client.applications.markJoined', $application) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-lg transition">
                                        <i class="fa-solid fa-check"></i> Joined
                                    </button>
                                </form>
                                <form action="{{ route('client.applications.markNotJoined', $application) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-lg transition">
                                        <i class="fa-solid fa-xmark"></i> DID NOT JOINED
                                    </button>
                                </form>
                            @else
                                @php
                                    $rounds = $application->interviewRounds;
                                    $latestRound = $rounds->last();
                                    $canSelectNow = $latestRound && $latestRound->feedback_submitted_at && !in_array($latestRound->recommendation, ['Reject']);
                                @endphp
                                @if($canSelectNow)
                                    <a href="{{ route('client.applications.select.show', $application) }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-lg transition">
                                        <i class="fa-solid fa-user-check"></i> Select Candidate
                                    </a>
                                @endif
                                <form action="{{ route('client.applications.reject', $application) }}" method="POST" onsubmit="return confirm('Reject this candidate?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-lg transition">
                                        <i class="fa-solid fa-user-minus"></i> Reject Candidate
                                    </button>
                                </form>
                            @endif
                        @elseif($application->joined_status == 'Joined')
                            <a href="{{ route('client.applications.showLeftForm', $application) }}" class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold py-2.5 px-4 rounded-xl shadow-lg transition">
                                <i class="fa-solid fa-door-open"></i> Mark Left / Exited
                            </a>
                        @else
                            <span class="text-slate-400 text-xs italic">No actions available for the current state.</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Interview Rounds Timeline --}}
            @if($application->interviewRounds->isNotEmpty())
            <div class="lg:col-span-3 glass-card rounded-3xl p-6 shadow-xl">
                <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-4"><i class="fa-solid fa-list-ol mr-1"></i> Interview Rounds ({{ $application->interviewRounds->count() }})</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($application->interviewRounds as $r)
                        @php
                            $bg = [
                                'Scheduled' => 'border-indigo-400/40 bg-indigo-500/10',
                                'Appeared'  => 'border-emerald-400/40 bg-emerald-500/10',
                                'No-Show'   => 'border-amber-400/40 bg-amber-500/10',
                                'Cancelled' => 'border-slate-400/40 bg-slate-500/10',
                            ][$r->status] ?? 'border-slate-400/40 bg-slate-500/10';
                        @endphp
                        <div class="border rounded-xl p-3 {{ $bg }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-white text-sm">Round {{ $r->round_number }}</span>
                                <span class="text-[10px] uppercase font-bold text-blue-100 bg-white/10 border border-white/20 px-2 py-0.5 rounded">{{ $r->status }}</span>
                            </div>
                            <div class="text-xs text-blue-100">{{ $r->scheduled_at->format('d M Y, h:i A') }}</div>
                            <div class="text-xs text-slate-300">{{ $r->mode }}@if($r->interviewer_name) · {{ $r->interviewer_name }}@endif</div>
                            @if($r->candidate_message)
                                <div class="mt-1.5 bg-amber-500/10 border border-amber-400/30 rounded p-1.5 text-[11px] text-amber-100 line-clamp-3">
                                    <span class="font-bold text-amber-300 text-[10px] uppercase">Candidate note:</span> {{ $r->candidate_message }}
                                </div>
                            @endif
                            @if($r->recommendation)
                                <div class="mt-1 inline-block px-2 py-0.5 rounded text-[10px] font-bold border bg-cyan-500/15 text-cyan-100 border-cyan-400/30">{{ $r->recommendation }}</div>
                            @endif
                            @if($r->rating)
                                <div class="text-amber-300 text-xs mt-1">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}</div>
                            @endif
                            @if($r->feedback)
                                <div class="mt-2 bg-[#03071a]/50 border border-white/10 rounded p-2 text-[11px] text-blue-100 italic line-clamp-3">"{{ $r->feedback }}"</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Profile --}}
            <div class="lg:col-span-3 glass-card rounded-3xl p-6 shadow-xl">
                <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-4"><i class="fa-solid fa-user mr-1"></i> Candidate Profile</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                    <div>
                        <div class="text-slate-300 text-[10px] uppercase font-bold">Experience</div>
                        <div class="text-white">{{ $exp ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-300 text-[10px] uppercase font-bold">Education</div>
                        <div class="text-white">{{ $education ?? '—' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-300 text-[10px] uppercase font-bold">Expected CTC</div>
                        <div class="text-white">{{ $ctcRaw ? '₹'.number_format($ctcNum, 2) : '—' }}</div>
                    </div>
                    <div>
                        <div class="text-slate-300 text-[10px] uppercase font-bold">Current Location</div>
                        <div class="text-white">{{ $location }}</div>
                    </div>
                </div>

                @if($skills)
                    <div class="mt-5">
                        <div class="text-slate-300 text-[10px] uppercase font-bold mb-2">Skills</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_filter(array_map('trim', preg_split('/[,;]+/', $skills))) as $skill)
                                <span class="bg-cyan-500/20 border border-cyan-400/40 text-cyan-100 text-xs font-bold px-3 py-1 rounded-full">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($application->interview_feedback)
                    <div class="mt-6 pt-5 border-t border-white/10">
                        <div class="text-slate-300 text-[10px] uppercase font-bold mb-1">Your Interview Feedback</div>
                        <p class="text-blue-100 text-sm italic">"{{ $application->interview_feedback }}"</p>
                    </div>
                @endif
            </div>
    </div>
@endsection
