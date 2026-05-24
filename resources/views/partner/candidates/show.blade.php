@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-20"></div>

    <div class="relative z-10 max-w-4xl mx-auto">

        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('partner.candidates.index') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider">
                <i class="fa-solid fa-arrow-left mr-2"></i> My Candidates
            </a>
            <a href="{{ route('partner.candidates.edit', $candidate->id) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold text-slate-900 transition-all hover:-translate-y-0.5"
               style="background: linear-gradient(135deg,#22d3ee,#0ea5e9); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                <i class="fa-solid fa-pen"></i> Edit Candidate
            </a>
        </div>

        {{-- Hero --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-7 mb-6 flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="h-20 w-20 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-3xl font-black text-white ring-4 ring-white/20 shrink-0">
                {{ strtoupper(substr($candidate->first_name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h1 class="text-3xl font-extrabold tracking-tight">{{ $candidate->first_name }} {{ $candidate->last_name }}</h1>
                <div class="flex flex-wrap gap-x-4 gap-y-1 mt-1 text-sm text-blue-200">
                    @if($candidate->email)
                        <span><i class="fa-solid fa-envelope mr-1 text-cyan-400"></i>{{ $candidate->email }}</span>
                    @endif
                    @if($candidate->phone_number)
                        <span><i class="fa-solid fa-phone mr-1 text-cyan-400"></i>{{ $candidate->phone_number }}</span>
                    @endif
                    @if($candidate->alternate_phone_number)
                        <span><i class="fa-solid fa-phone-flip mr-1 text-cyan-400"></i>{{ $candidate->alternate_phone_number }}</span>
                    @endif
                    @if($candidate->location)
                        <span><i class="fa-solid fa-location-dot mr-1 text-cyan-400"></i>{{ $candidate->location }}</span>
                    @endif
                </div>
            </div>
            @php $isExp = ($candidate->experience_status ?? 'Fresher') === 'Experienced'; @endphp
            <span class="px-4 py-1.5 rounded-full text-sm font-bold border shrink-0 {{ $isExp ? 'bg-emerald-500/20 text-emerald-200 border-emerald-400/30' : 'bg-blue-500/20 text-blue-200 border-blue-400/30' }}">
                {{ $candidate->experience_status ?? 'Fresher' }}
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Personal details --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-cyan-300 mb-4">Personal Information</h2>
                <div class="space-y-3 text-sm">
                    @php
                        $personal = [
                            'Gender'         => $candidate->gender,
                            'Date of Birth'  => $candidate->date_of_birth?->format('d M Y'),
                            'Marital Status' => $candidate->marital_status,
                            'Languages'      => $candidate->languages_spoken,
                        ];
                    @endphp
                    @foreach($personal as $label => $value)
                        @if($value)
                        <div class="flex justify-between gap-2">
                            <span class="text-blue-300 font-medium">{{ $label }}</span>
                            <span class="text-white text-right">{{ $value }}</span>
                        </div>
                        @endif
                    @endforeach
                    @if($candidate->preferred_locations)
                        <div>
                            <div class="text-blue-300 font-medium mb-1.5">Preferred Locations</div>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach((array)$candidate->preferred_locations as $loc)
                                    <span class="px-2.5 py-0.5 bg-slate-700/60 border border-white/15 rounded-full text-xs text-white/80">{{ $loc }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Professional details --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-cyan-300 mb-4">Professional Details</h2>
                <div class="space-y-3 text-sm">
                    @php
                        $prof = [
                            'Current Role'    => $candidate->current_designation,
                            'Current Company' => $candidate->current_company,
                            'Experience'      => $candidate->total_experience_years !== null
                                                    ? $candidate->total_experience_years . ' yr ' . ($candidate->total_experience_months ?? 0) . ' mo'
                                                    : null,
                            'Current CTC'     => $candidate->current_ctc,
                            'Expected CTC'    => $candidate->expected_ctc,
                            'Notice Period'   => $candidate->notice_period,
                            'Job Interest'    => $candidate->job_interest,
                            'Role Preference' => $candidate->job_role_preference,
                        ];
                    @endphp
                    @foreach($prof as $label => $value)
                        @if($value)
                        <div class="flex justify-between gap-2">
                            <span class="text-blue-300 font-medium">{{ $label }}</span>
                            <span class="text-white text-right">{{ $value }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- Education + Skills --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6 md:col-span-2">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-cyan-300 mb-4">Education & Skills</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm mb-4">
                    @foreach(['Education Level' => $candidate->education_level, 'Degree' => $candidate->qualification_degree, 'Specialization' => $candidate->specialization] as $label => $value)
                        @if($value)
                        <div class="bg-white/5 rounded-xl px-4 py-3">
                            <div class="text-blue-300 text-xs font-bold uppercase tracking-wide mb-1">{{ $label }}</div>
                            <div class="text-white font-semibold">{{ $value }}</div>
                        </div>
                        @endif
                    @endforeach
                </div>
                @if($candidate->skills)
                    <div>
                        <div class="text-blue-300 text-xs font-bold uppercase tracking-wide mb-2">Skills</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_filter(array_map('trim', explode(',', $candidate->skills))) as $skill)
                                <span class="px-3 py-1 bg-blue-500/20 border border-blue-400/30 rounded-full text-xs text-blue-100 font-semibold">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- Applications --}}
            @php
                $apps = $candidate->applications()->with('job')->latest()->get() ?? collect();
            @endphp
            @if($apps->count())
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-6 md:col-span-2">
                <h2 class="text-xs font-extrabold uppercase tracking-widest text-cyan-300 mb-4">Applications ({{ $apps->count() }})</h2>
                <div class="space-y-2">
                    @foreach($apps as $app)
                        @php $appStatus = $app->effectiveStatus(); @endphp
                        <div class="flex items-center justify-between gap-4 bg-white/5 rounded-xl px-4 py-3">
                            <div class="min-w-0">
                                <div class="font-semibold text-white text-sm truncate">{{ $app->job?->title ?? 'Job No Longer Available' }}</div>
                                @if($app->job && !$app->job->is_company_confidential)
                                    <div class="text-xs text-blue-300">{{ $app->job->company_name }}</div>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-xs text-blue-300">{{ $app->created_at->format('d M Y') }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border
                                    {{ match(true) {
                                        in_array($appStatus, ['Joined','Selected','Approved']) => 'bg-emerald-500/20 text-emerald-100 border-emerald-400/40',
                                        in_array($appStatus, ['Rejected','Client Rejected','Left','Did Not Join']) => 'bg-rose-500/20 text-rose-100 border-rose-400/40',
                                        in_array($appStatus, ['Interview Scheduled','Interviewed']) => 'bg-indigo-500/20 text-indigo-100 border-indigo-400/40',
                                        default => 'bg-amber-500/20 text-amber-100 border-amber-400/40'
                                    } }}">{{ $appStatus }}</span>
                                <a href="{{ route('partner.applications.show', $app->id) }}" class="text-xs text-cyan-300 hover:text-white font-bold">
                                    View <i class="fa-solid fa-arrow-right ml-0.5"></i>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- Resume --}}
        @if($candidate->resume_path)
        <div class="mt-6">
            <a href="{{ Storage::url($candidate->resume_path) }}" target="_blank"
               class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-slate-900 transition-all hover:-translate-y-0.5"
               style="background: linear-gradient(135deg,#22d3ee,#0ea5e9); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                <i class="fa-solid fa-file-arrow-down"></i> Download Resume
            </a>
        </div>
        @endif

    </div>
</div>
@endsection
