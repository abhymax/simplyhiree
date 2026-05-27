@extends('layouts.app')

@section('content')
@php
    $candidate = $application->candidate;
    $job       = $application->job;
    $name      = $candidate ? trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')) : 'Candidate Deleted';
    $initial   = strtoupper(substr($name ?: 'U', 0, 1));
    $status    = $application->effectiveStatus();

    $statusClasses = [
        'Pending Review'        => 'bg-amber-500/20 text-amber-100 border-amber-400/40',
        'Approved'              => 'bg-emerald-500/20 text-emerald-100 border-emerald-400/40',
        'Interview Scheduled'   => 'bg-indigo-500/20 text-indigo-100 border-indigo-400/40',
        'Interviewed'           => 'bg-violet-500/20 text-violet-100 border-violet-400/40',
        'No-Show'               => 'bg-amber-500/20 text-amber-100 border-amber-400/40',
        'Selected'              => 'bg-cyan-500/20 text-cyan-100 border-cyan-400/40',
        'Selected by Superadmin'=> 'bg-purple-500/25 text-purple-100 border-purple-400/50',
        'Joined'                => 'bg-emerald-600/30 text-emerald-100 border-emerald-400/50',
        'Left'                  => 'bg-rose-500/20 text-rose-100 border-rose-400/40',
        'Did Not Join'          => 'bg-rose-500/20 text-rose-100 border-rose-400/40',
        'Rejected'              => 'bg-rose-500/20 text-rose-100 border-rose-400/40',
        'Client Rejected'       => 'bg-rose-500/20 text-rose-100 border-rose-400/40',
    ];
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-20"></div>

    <div class="relative z-10 max-w-4xl mx-auto">

        {{-- Back --}}
        <a href="{{ route('partner.applications') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-6">
            <i class="fa-solid fa-arrow-left mr-2"></i> All Applications
        </a>

        {{-- Hero card --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-7 mb-6 flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-3xl font-black text-white ring-4 ring-white/20 shrink-0">
                {{ $initial }}
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-extrabold tracking-tight">{{ $name }}</h1>
                @if($candidate)
                    <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-sm text-blue-200">
                        @if($candidate->email)
                            <span><i class="fa-solid fa-envelope mr-1 text-cyan-400"></i>{{ $candidate->email }}</span>
                        @endif
                        @if($candidate->phone_number)
                            <span><i class="fa-solid fa-phone mr-1 text-cyan-400"></i>{{ $candidate->phone_number }}</span>
                        @endif
                        @if($candidate->location)
                            <span><i class="fa-solid fa-location-dot mr-1 text-cyan-400"></i>{{ $candidate->location }}</span>
                        @endif
                    </div>
                @endif
            </div>
            <span class="px-4 py-1.5 rounded-full text-sm font-bold border shrink-0 {{ $statusClasses[$status] ?? 'bg-slate-500/20 text-slate-100 border-slate-400/40' }}">
                {{ $status }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Job details --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-cyan-300 mb-4">Applied Job</h2>
                @if($job)
                    <div class="space-y-2 text-sm">
                        <div class="font-bold text-white text-lg leading-snug">
                            <a href="{{ route('partner.jobs.show', $job->id) }}" class="hover:text-cyan-300 transition-colors">{{ $job->title }}</a>
                        </div>
                        <div class="text-amber-300 font-semibold">
                            @if($job->is_company_confidential)
                                <i class="fa-solid fa-user-secret mr-1"></i> Confidential Client
                            @else
                                {{ $job->company_name }}
                            @endif
                        </div>
                        @if($job->location)
                            <div class="text-blue-200"><i class="fa-solid fa-location-dot mr-1"></i>{{ $job->location }}</div>
                        @endif
                        @if($job->job_type)
                            <div class="text-blue-200"><i class="fa-solid fa-briefcase mr-1"></i>{{ $job->job_type }}</div>
                        @endif
                    </div>
                @else
                    <p class="text-slate-400 italic text-sm">Job no longer available</p>
                @endif
                <div class="mt-4 pt-4 border-t border-white/10 text-xs text-blue-300">
                    Applied on {{ $application->created_at->format('d M Y') }}
                </div>
            </div>

            {{-- Interview / timeline --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-cyan-300 mb-4">Application Timeline</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 mt-1.5 rounded-full bg-blue-400 shrink-0"></span>
                        <div><span class="font-semibold text-white">Submitted</span><span class="text-blue-300 ml-2">{{ $application->created_at->format('d M Y') }}</span></div>
                    </div>
                    @forelse($application->interviewRounds as $r)
                        @php
                            $dotColor = [
                                'Scheduled' => 'bg-indigo-400',
                                'Appeared'  => 'bg-emerald-400',
                                'No-Show'   => 'bg-amber-400',
                                'Cancelled' => 'bg-slate-400',
                            ][$r->status] ?? 'bg-slate-400';
                        @endphp
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 mt-1.5 rounded-full {{ $dotColor }} shrink-0"></span>
                            <div class="flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-white">Round {{ $r->round_number }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-white/10 text-blue-100 border-white/20">{{ $r->mode }}</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-white/10 text-cyan-100 border-white/20">{{ $r->status }}</span>
                                    @if($r->recommendation)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold border bg-cyan-500/15 text-cyan-100 border-cyan-400/30">{{ $r->recommendation }}</span>
                                    @endif
                                </div>
                                <div class="text-blue-300 text-xs mt-0.5">{{ $r->scheduled_at->format('d M Y, h:i A') }}@if($r->interviewer_name) · with {{ $r->interviewer_name }}@endif</div>
                                @if($r->candidate_message)
                                    <div class="mt-1 bg-amber-500/10 border border-amber-400/30 rounded px-2 py-1.5 text-xs text-amber-100">
                                        <span class="font-bold text-amber-300 text-[10px] uppercase tracking-wider">Note for candidate:</span><br>{{ $r->candidate_message }}
                                    </div>
                                @endif
                                @if($r->rating)
                                    <div class="text-amber-300 text-xs mt-0.5">{{ str_repeat('★', $r->rating) }}{{ str_repeat('☆', 5 - $r->rating) }}</div>
                                @endif
                                @if($r->feedback)
                                    <div class="mt-1 bg-white/5 border border-white/10 rounded px-2 py-1.5 text-xs text-blue-100 italic">"{{ $r->feedback }}"</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        @if($application->interview_at)
                            <div class="flex items-start gap-3">
                                <span class="w-2 h-2 mt-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                <div>
                                    <span class="font-semibold text-white">Interview Scheduled</span>
                                    <span class="text-blue-300 ml-2">{{ $application->interview_at->format('d M Y, h:i A') }}</span>
                                </div>
                            </div>
                        @endif
                    @endforelse
                    @if(in_array($application->hiring_status, ['Selected', 'Client Rejected']))
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 mt-1.5 rounded-full {{ $application->hiring_status === 'Selected' ? 'bg-cyan-400' : 'bg-rose-400' }} shrink-0"></span>
                            <span class="font-semibold text-white">{{ $application->hiring_status }}</span>
                        </div>
                    @endif
                    @if($application->joining_date)
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 mt-1.5 rounded-full {{ $application->joined_status === 'Joined' ? 'bg-emerald-400' : 'bg-rose-400' }} shrink-0"></span>
                            <div>
                                <span class="font-semibold text-white">{{ $application->joined_status ?? 'Joining Date' }}</span>
                                <span class="text-blue-300 ml-2">{{ $application->joining_date->format('d M Y') }}</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Candidate profile --}}
            @if($candidate)
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6 md:col-span-2">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-cyan-300 mb-4">Candidate Profile</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 text-sm">
                    @php
                        $fields = [
                            'Experience'       => $candidate->total_experience_years !== null
                                                    ? $candidate->total_experience_years . ' yr' . ($candidate->total_experience_months ? ' ' . $candidate->total_experience_months . ' mo' : '')
                                                    : null,
                            'Current Role'     => $candidate->current_designation,
                            'Current Company'  => $candidate->current_company,
                            'Current CTC'      => $candidate->current_ctc,
                            'Expected CTC'     => $candidate->expected_ctc,
                            'Notice Period'    => $candidate->notice_period,
                            'Education'        => $candidate->education_level,
                            'Degree'           => $candidate->qualification_degree,
                            'Specialization'   => $candidate->specialization,
                            'Gender'           => $candidate->gender,
                            'Languages'        => $candidate->languages_spoken,
                            'Job Interest'     => $candidate->job_interest,
                        ];
                    @endphp
                    @foreach($fields as $label => $value)
                        @if($value)
                        <div class="bg-white/5 rounded-xl px-4 py-3">
                            <div class="text-blue-300 text-xs font-bold uppercase tracking-wide mb-1">{{ $label }}</div>
                            <div class="text-white font-semibold">{{ $value }}</div>
                        </div>
                        @endif
                    @endforeach
                </div>

                @if($candidate->skills)
                <div class="mt-4">
                    <div class="text-blue-300 text-xs font-bold uppercase tracking-wide mb-2">Skills</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach(array_filter(array_map('trim', explode(',', $candidate->skills))) as $skill)
                            <span class="px-3 py-1 bg-blue-500/20 border border-blue-400/30 rounded-full text-xs text-blue-100 font-semibold">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($candidate->preferred_locations)
                <div class="mt-4">
                    <div class="text-blue-300 text-xs font-bold uppercase tracking-wide mb-2">Preferred Locations</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach((array)$candidate->preferred_locations as $loc)
                            <span class="px-3 py-1 bg-slate-700/60 border border-white/15 rounded-full text-xs text-white/80">{{ $loc }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($candidate->resume_path)
                <div class="mt-5">
                    <a href="{{ Storage::url($candidate->resume_path) }}" target="_blank"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-slate-900 transition-all hover:-translate-y-0.5"
                       style="background: linear-gradient(135deg,#22d3ee,#0ea5e9); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                        <i class="fa-solid fa-file-arrow-down"></i> Download Resume
                    </a>
                </div>
                @endif
            </div>
            @endif

        </div>
    </div>
</div>
@endsection
