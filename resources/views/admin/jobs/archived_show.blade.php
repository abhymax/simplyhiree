<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 left-0 w-96 h-96 bg-rose-500 rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen filter blur-[150px] opacity-15"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between md:items-end mb-8 border-b border-white/10 pb-6 gap-4">
                <div>
                    <a href="{{ route('admin.jobs.archived') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Archived Jobs
                    </a>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">{{ $job->title }}</h1>
                    <p class="text-blue-200 mt-1 font-medium">
                        {{ $job->company_name ?? '—' }} &middot; {{ $job->location }} &middot;
                        Archived {{ $job->archived_at->format('M d, Y') }}
                        <span class="ml-2 inline-flex items-center gap-1 bg-rose-500/20 border border-rose-400/40 text-rose-200 px-2 py-0.5 rounded text-xs font-bold uppercase">
                            <i class="fa-solid fa-box-archive"></i> Archived
                        </span>
                    </p>
                </div>
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('admin.jobs.archived.restore', $job) }}" onsubmit="return confirm('Restore this job back to approved?');">
                        @csrf
                        <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-bold px-4 py-2 rounded-lg">
                            <i class="fa-solid fa-rotate-left mr-1"></i> Restore Job
                        </button>
                    </form>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-6 px-6 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 rounded-xl font-bold">
                    {{ session('success') }}
                </div>
            @endif

            {{-- JOB SNAPSHOT --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
                    <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3">Job Details</h3>
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between gap-3"><dt class="text-blue-200">Type</dt><dd class="text-white font-semibold text-right">{{ $job->job_type ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-blue-200">Category</dt><dd class="text-white font-semibold text-right">{{ $job->category->name ?? $job->category ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-blue-200">Salary</dt><dd class="text-white font-semibold text-right">{{ $job->salary ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-blue-200">Experience</dt><dd class="text-white font-semibold text-right">{{ $job->formatted_experience }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-blue-200">Education</dt><dd class="text-white font-semibold text-right">{{ $job->educationLevel->name ?? '—' }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-blue-200">Openings</dt><dd class="text-white font-semibold text-right">{{ $job->openings ?? '—' }}</dd></div>
                    </dl>
                </div>
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
                    <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3">Posted By</h3>
                    <div class="text-white font-bold">{{ $job->user->name ?? '—' }}</div>
                    <div class="text-blue-200 text-sm">{{ $job->user->email ?? '' }}</div>
                    <hr class="border-white/10 my-3">
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between"><dt class="text-blue-200">Posted</dt><dd class="text-white">{{ $job->created_at->format('M d, Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-blue-200">Archived</dt><dd class="text-white">{{ $job->archived_at->format('M d, Y') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-blue-200">Lifespan</dt><dd class="text-white">{{ $job->created_at->diffInDays($job->archived_at) }} days</dd></div>
                    </dl>
                </div>
                <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-6">
                    <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3">Outcomes</h3>
                    @php
                        $apps = $job->jobApplications;
                        $hired = $apps->whereNotNull('joining_date')->count();
                        $interviewed = $apps->whereNotNull('interview_at')->count();
                        $rejected = $apps->whereIn('status', ['Rejected', 'rejected'])->count();
                        $left = $apps->whereNotNull('left_at')->count();
                    @endphp
                    <dl class="text-sm space-y-2">
                        <div class="flex justify-between"><dt class="text-blue-200">Total Applications</dt><dd class="text-white font-bold">{{ $apps->count() }}</dd></div>
                        <div class="flex justify-between"><dt class="text-blue-200">Interviewed</dt><dd class="text-white font-bold">{{ $interviewed }}</dd></div>
                        <div class="flex justify-between"><dt class="text-blue-200">Hired / Joined</dt><dd class="text-emerald-300 font-bold">{{ $hired }}</dd></div>
                        <div class="flex justify-between"><dt class="text-blue-200">Rejected</dt><dd class="text-rose-300 font-bold">{{ $rejected }}</dd></div>
                        <div class="flex justify-between"><dt class="text-blue-200">Left After Joining</dt><dd class="text-amber-300 font-bold">{{ $left }}</dd></div>
                    </dl>
                </div>
            </div>

            {{-- DESCRIPTION --}}
            @if($job->description)
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-6 mb-8">
                <h3 class="text-cyan-300 text-xs font-bold uppercase tracking-wider mb-3">Job Description</h3>
                <div class="text-blue-100 text-sm leading-relaxed job-desc-html">{!! $job->formatted_description !!}</div>
            </div>
            @endif

            {{-- APPLICATIONS HISTORY --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                    <h2 class="text-xl font-extrabold text-white">Applications History</h2>
                    <span class="text-blue-200 text-xs">All candidate, partner, interview, joining, and exit data</span>
                </div>

                @if($apps->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <i class="fa-regular fa-folder-open text-4xl text-blue-300/40 mb-3"></i>
                        <p class="text-white font-bold">No applications were submitted to this job.</p>
                    </div>
                @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-bold border-b border-white/10 text-[10px] tracking-wider">
                            <tr>
                                <th class="px-4 py-4">Code</th>
                                <th class="px-4 py-4">Candidate</th>
                                <th class="px-4 py-4">Partner</th>
                                <th class="px-4 py-4">Status</th>
                                <th class="px-4 py-4">Interview</th>
                                <th class="px-4 py-4">Joining</th>
                                <th class="px-4 py-4">Outcome</th>
                                <th class="px-4 py-4">CV</th>
                                <th class="px-4 py-4">Notes</th>
                                <th class="px-4 py-4">Applied</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @foreach($apps as $a)
                            <tr class="hover:bg-white/5 align-top">
                                <td class="px-4 py-4 text-cyan-200 font-mono text-xs">{{ $a->application_code ?? ('#' . $a->id) }}</td>
                                <td class="px-4 py-4">
                                    <div class="text-white font-bold">{{ $a->candidate_name ?? '—' }}</div>
                                    <div class="text-blue-300/70 text-xs">{{ $a->candidate->email ?? $a->candidateUser->email ?? '' }}</div>
                                    <div class="text-blue-300/70 text-xs">{{ $a->candidate->phone ?? '' }}</div>
                                </td>
                                <td class="px-4 py-4 text-blue-100 text-xs">
                                    @if($a->candidate && $a->candidate->partner)
                                        {{ $a->candidate->partner->name }}
                                        <div class="text-blue-300/70">{{ $a->candidate->partner->email }}</div>
                                    @else
                                        <span class="text-blue-300/50 italic">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-block bg-white/10 border border-white/20 text-white text-xs font-bold px-2 py-1 rounded">{{ $a->status ?? '—' }}</span>
                                    @if($a->hiring_status)
                                        <div class="mt-1 text-[10px] text-emerald-300 uppercase font-bold">{{ str_replace('_', ' ', $a->hiring_status) }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-blue-100 text-xs">
                                    @if($a->interview_at)
                                        {{ $a->interview_at->format('M d, Y') }}
                                        <div class="text-blue-300/70">{{ $a->interview_at->format('h:i A') }}</div>
                                    @else
                                        <span class="text-blue-300/40">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-blue-100 text-xs">
                                    @if($a->joining_date)
                                        {{ $a->joining_date->format('M d, Y') }}
                                        @if($a->joined_status)
                                            <div class="text-[10px] text-emerald-300 font-bold uppercase mt-0.5">{{ $a->joined_status }}</div>
                                        @endif
                                    @else
                                        <span class="text-blue-300/40">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-xs">
                                    @if($a->left_at)
                                        <span class="text-amber-300 font-bold">Left</span>
                                        <div class="text-blue-300/70">{{ $a->left_at->format('M d, Y') }}</div>
                                    @elseif($a->joining_date)
                                        <span class="text-emerald-300 font-bold">Active hire</span>
                                    @elseif(in_array(strtolower($a->status ?? ''), ['rejected']))
                                        <span class="text-rose-300 font-bold">Rejected</span>
                                    @else
                                        <span class="text-blue-300/60">In pipeline</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-xs">
                                    @php
                                        $cv = $a->candidate->resume_path ?? $a->candidateUser->profile->resume_path ?? null;
                                    @endphp
                                    @if($cv)
                                        <a href="{{ Storage::url($cv) }}" target="_blank" class="text-cyan-300 hover:text-white underline">
                                            <i class="fa-solid fa-file-pdf"></i> Download
                                        </a>
                                    @else
                                        <span class="text-blue-300/40">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-blue-100 text-xs max-w-[240px]">
                                    @if($a->client_notes)
                                        <span class="block whitespace-normal italic text-blue-200/80">"{{ \Illuminate\Support\Str::limit($a->client_notes, 120) }}"</span>
                                    @else
                                        <span class="text-blue-300/40">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-blue-300/70 text-xs">{{ $a->created_at->format('M d, Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
