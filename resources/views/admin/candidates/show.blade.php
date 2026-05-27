<x-app-layout>
@php
    $name = trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')) ?: 'Candidate';
    $initial = strtoupper(substr($name, 0, 1));
    $code = $candidate->candidate_code ?? ('SH-CAN-' . str_pad((string) $candidate->id, 6, '0', STR_PAD_LEFT));
@endphp
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-6xl mx-auto">
        <a href="{{ route('admin.candidates.index') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-xs font-bold uppercase tracking-wider mb-4">
            <i class="fa-solid fa-arrow-left mr-2"></i> Candidate Database
        </a>

        {{-- Hero --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6 shadow-xl mb-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-5">
                <div class="h-20 w-20 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center font-bold text-3xl ring-4 ring-white/10">{{ $initial }}</div>
                <div>
                    <h1 class="text-3xl font-extrabold text-white">{{ $name }}</h1>
                    <div class="text-[11px] font-mono text-cyan-200 mt-1">{{ $code }}</div>
                    <div class="text-blue-200 text-sm mt-2 flex flex-wrap gap-3">
                        <span><i class="fa-regular fa-envelope mr-1 text-cyan-300"></i>{{ $candidate->email ?? '—' }}</span>
                        <span><i class="fa-solid fa-phone mr-1 text-emerald-300"></i>{{ $candidate->phone_number ?? '—' }}</span>
                        <span><i class="fa-solid fa-location-dot mr-1 text-rose-300"></i>{{ $candidate->location ?? '—' }}</span>
                    </div>
                </div>
            </div>
            <div class="flex flex-col items-end gap-2">
                @if($candidate->resume_path)
                    <a href="{{ asset('storage/'.$candidate->resume_path) }}" target="_blank"
                       class="inline-flex items-center gap-2 bg-rose-500/20 hover:bg-rose-500 text-rose-200 hover:text-white px-4 py-2 rounded-xl font-bold text-sm border border-rose-400/40 transition">
                        <i class="fa-solid fa-file-pdf"></i> Download Resume
                    </a>
                @endif
                @if($candidate->partner)
                    <a href="{{ route('admin.partners.show', $candidate->partner->id) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded bg-purple-500/20 hover:bg-purple-500/40 text-purple-100 border border-purple-400/40 text-xs font-bold transition">
                        <i class="fa-solid fa-handshake"></i> Uploaded by {{ $candidate->partner->name }}
                    </a>
                @else
                    <span class="text-slate-400 text-xs italic">Direct candidate</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- Recruitment --}}
            <div class="lg:col-span-2 bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6 shadow-xl">
                <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-4"><i class="fa-solid fa-briefcase mr-1"></i> Recruitment Profile</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Current Company</div><div class="text-white">{{ $candidate->current_company ?? '—' }}</div></div>
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Designation</div><div class="text-white">{{ $candidate->current_designation ?? '—' }}</div></div>
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Total Experience</div><div class="text-white">{{ $candidate->total_experience_years ?? 0 }}y {{ $candidate->total_experience_months ?? 0 }}m</div></div>
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Notice Period</div><div class="text-white">{{ $candidate->notice_period ?? '—' }}</div></div>
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Current CTC</div><div class="text-white">{{ $candidate->current_ctc ? '₹'.number_format($candidate->current_ctc) : '—' }}</div></div>
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Expected CTC</div><div class="text-white">{{ $candidate->expected_ctc ? '₹'.number_format($candidate->expected_ctc) : '—' }}</div></div>
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Education</div><div class="text-white">{{ $candidate->education_level ?? '—' }} · {{ $candidate->qualification_degree ?? '' }}</div></div>
                    <div><div class="text-slate-400 text-[10px] uppercase font-bold">Specialization</div><div class="text-white">{{ $candidate->specialization ?? '—' }}</div></div>
                </div>

                @if($candidate->skills)
                    <div class="mt-5">
                        <div class="text-slate-400 text-[10px] uppercase font-bold mb-2">Skills</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach(array_filter(array_map('trim', preg_split('/[,;]+/', $candidate->skills))) as $skill)
                                <span class="bg-cyan-500/15 border border-cyan-400/40 text-cyan-100 text-xs font-bold px-3 py-1 rounded-full">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($candidate->summary)
                    <div class="mt-5 pt-4 border-t border-white/10">
                        <div class="text-slate-400 text-[10px] uppercase font-bold mb-1">Summary</div>
                        <p class="text-blue-100 text-sm italic">{{ $candidate->summary }}</p>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-5 shadow-xl">
                    <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3"><i class="fa-solid fa-id-card mr-1"></i> Personal</h3>
                    <div class="space-y-2 text-sm">
                        <div><span class="text-slate-400 text-xs">Gender:</span> <span class="text-white">{{ $candidate->gender ?? '—' }}</span></div>
                        <div><span class="text-slate-400 text-xs">DOB:</span> <span class="text-white">{{ optional($candidate->date_of_birth ?? $candidate->dob)->format('d M Y') ?? '—' }}</span></div>
                        <div><span class="text-slate-400 text-xs">Marital:</span> <span class="text-white">{{ $candidate->marital_status ?? '—' }}</span></div>
                        <div><span class="text-slate-400 text-xs">Languages:</span> <span class="text-white">{{ $candidate->languages_spoken ?? '—' }}</span></div>
                        @php $prefLoc = is_array($candidate->preferred_locations) ? implode(', ', $candidate->preferred_locations) : (string) ($candidate->preferred_locations ?? ''); @endphp
                        <div><span class="text-slate-400 text-xs">Pref. Loc:</span> <span class="text-white">{{ $prefLoc ?: '—' }}</span></div>
                    </div>
                </div>

                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-5 shadow-xl">
                    <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3"><i class="fa-solid fa-file-circle-check mr-1"></i> Applications ({{ $candidate->jobApplications->count() }})</h3>
                    @forelse($candidate->jobApplications->take(8) as $app)
                        <div class="py-2 border-b border-white/5 last:border-0 text-xs">
                            <a href="{{ route('admin.applications.show', $app->id) }}" class="text-white hover:text-cyan-300 font-bold">{{ $app->job->title ?? 'Job' }}</a>
                            <div class="text-slate-400 mt-0.5">{{ $app->status }}@if($app->hiring_status) · {{ $app->hiring_status }}@endif</div>
                            <div class="text-slate-500 text-[10px]">{{ $app->created_at->format('d M Y') }}</div>
                        </div>
                    @empty
                        <p class="text-slate-400 text-xs italic">No applications yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
