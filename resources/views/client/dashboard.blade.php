@extends('layouts.app')

@section('content')
<style>
    /* --- Glossy effects --- */
    .gloss { position: relative; isolation: isolate; }
    .gloss::before {
        content: ""; position: absolute; inset: 0;
        background: linear-gradient(120deg, rgba(255,255,255,.18) 0%, rgba(255,255,255,0) 35%, rgba(255,255,255,0) 65%, rgba(255,255,255,.08) 100%);
        pointer-events: none; border-radius: inherit; z-index: 0;
    }
    .gloss::after {
        content: ""; position: absolute; inset: 0;
        background: radial-gradient(120% 60% at 0% 0%, rgba(255,255,255,.22), transparent 60%);
        pointer-events: none; border-radius: inherit; z-index: 0; mix-blend-mode: overlay;
    }
    .gloss > * { position: relative; z-index: 1; }

    .ring-glow { box-shadow: 0 0 0 1px rgba(255,255,255,.08), 0 20px 60px -20px rgba(99,102,241,.55), 0 0 80px -25px rgba(34,211,238,.45); }
    .stat-card { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 22px 50px -20px rgba(56,189,248,.45); border-color: rgba(255,255,255,.35); }

    .neon-btn { background: linear-gradient(135deg, #06b6d4, #6366f1); box-shadow: 0 10px 30px -8px rgba(34,211,238,.55), inset 0 1px 0 rgba(255,255,255,.3); }
    .neon-btn:hover { filter: brightness(1.1); box-shadow: 0 18px 40px -10px rgba(34,211,238,.7), inset 0 1px 0 rgba(255,255,255,.35); }

    .blob { animation: float 18s ease-in-out infinite alternate; }
    @keyframes float { 0% { transform: translate(0, 0) scale(1); } 100% { transform: translate(20px, -20px) scale(1.05); } }

    .fx-row { transition: all .2s ease; border-left: 4px solid transparent; }
    .fx-row:hover { background: rgba(255,255,255,.06) !important; border-left-color: #22d3ee; }
</style>

<div class="min-h-screen text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden"
     style="background: linear-gradient(135deg, #020617 0%, #1e1b4b 50%, #0f172a 100%);">
    {{-- Animated gradient blobs --}}
    <div class="blob absolute -top-32 -left-32 rounded-full mix-blend-screen opacity-25"
         style="width: 28rem; height: 28rem; background: #06b6d4; filter: blur(140px);"></div>
    <div class="blob absolute top-1/3 right-0 rounded-full mix-blend-screen opacity-25"
         style="width: 28rem; height: 28rem; background: #d946ef; filter: blur(140px); animation-delay:-6s"></div>
    <div class="blob absolute bottom-0 left-1/3 rounded-full mix-blend-screen opacity-25"
         style="width: 28rem; height: 28rem; background: #6366f1; filter: blur(140px); animation-delay:-12s"></div>
    <div class="absolute inset-0"
         style="background-image: radial-gradient(rgba(255,255,255,.6) 1px, transparent 1px); background-size: 24px 24px; opacity: 0.07;"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="px-3 py-1.5 rounded-full bg-gradient-to-r from-cyan-400/30 to-indigo-400/30 border border-cyan-300/40 text-cyan-100 text-[10px] font-bold uppercase tracking-[0.18em] shadow-lg shadow-cyan-500/10">
                        ✨ Client Workspace
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-black tracking-tight text-white drop-shadow" style="text-shadow: 0 2px 12px rgba(34,211,238,.35);">Overview</h1>
                <p class="text-blue-200 mt-2 text-lg">Welcome back, <span class="text-white font-semibold">{{ Auth::user()->name }}</span>.</p>
            </div>
            <div class="mt-6 md:mt-0">
                <div class="gloss bg-white/10 backdrop-blur-xl border border-white/20 px-5 py-3 rounded-2xl flex items-center gap-4 ring-glow">
                    <div class="p-2.5 bg-gradient-to-br from-cyan-400 to-indigo-500 rounded-xl shadow-md shadow-cyan-500/40">
                        <i class="fa-regular fa-calendar text-white"></i>
                    </div>
                    <div>
                        <p class="text-[10px] text-cyan-200 font-bold uppercase tracking-widest">Today</p>
                        <p class="text-white font-bold">{{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            @php
                $tiles = [
                    ['icon'=>'fa-video','grad'=>'from-cyan-400 to-blue-500','label'=>'Interviews Today','val'=>($todayInterviews ?? 0),'href'=>route('client.interviews.today')],
                    ['icon'=>'fa-file-invoice-dollar','grad'=>'from-amber-400 to-orange-500','label'=>'Payments Due','val'=>($dueInvoicesCount ?? 0),'href'=>route('client.billing')],
                    ['icon'=>'fa-users','grad'=>'from-emerald-400 to-teal-500','label'=>'Total Applicants','val'=>($totalApplicants ?? 0),'href'=>'#my-jobs'],
                    ['icon'=>'fa-briefcase','grad'=>'from-fuchsia-400 to-purple-500','label'=>'Active Jobs','val'=>($activeJobs ?? 0),'href'=>'#my-jobs'],
                ];
            @endphp
            @foreach($tiles as $t)
                <a href="{{ $t['href'] }}" class="stat-card gloss bg-white/10 backdrop-blur-xl border border-white/15 rounded-2xl p-5 flex items-center gap-4 relative overflow-hidden">
                    <div class="p-3 rounded-xl bg-gradient-to-br {{ $t['grad'] }} shadow-lg shadow-black/40">
                        <i class="fa-solid {{ $t['icon'] }} text-white text-lg"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-3xl font-black tracking-tight text-white" style="text-shadow: 0 2px 8px rgba(0,0,0,.4);">{{ $t['val'] }}</div>
                        <div class="text-blue-200 text-xs font-semibold uppercase tracking-wider">{{ $t['label'] }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Hero CTA + quick action --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-10">
            <div class="lg:col-span-2 relative rounded-3xl overflow-hidden ring-glow"
                 style="background: linear-gradient(135deg, #0c4a6e 0%, #312e81 55%, #4a1d96 100%);">
                {{-- Gradient overlays --}}
                <div class="absolute inset-0" style="background: radial-gradient(120% 60% at 0% 0%, rgba(255,255,255,.18), transparent 55%);"></div>
                <div class="absolute -right-12 -bottom-12 w-72 h-72 rounded-full" style="background: rgba(56,189,248,.18); filter: blur(60px);"></div>
                <div class="relative gloss p-8 md:p-10">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="bg-white/15 backdrop-blur-md p-2.5 rounded-xl border border-white/30 shadow-md"><i class="fa-solid fa-bolt text-yellow-300"></i></span>
                        <h3 class="font-extrabold text-2xl text-white drop-shadow" style="text-shadow: 0 2px 8px rgba(0,0,0,.4);">Daily Pulse</h3>
                    </div>
                    <p class="text-blue-100 text-lg mb-6 max-w-xl" style="text-shadow: 0 1px 4px rgba(0,0,0,.35);">A snapshot of what needs your attention today — interviews on the schedule, pending payments, and how your jobs are performing.</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('client.jobs.create') }}" class="neon-btn inline-flex items-center gap-2 text-white px-6 py-3 rounded-xl font-extrabold transition">
                            <i class="fa-solid fa-plus"></i> Post New Job <i class="fa-solid fa-arrow-right text-sm"></i>
                        </a>
                        <a href="{{ route('client.interviews.today') }}" class="inline-flex items-center gap-2 bg-white/20 hover:bg-white/30 border border-white/30 text-white px-6 py-3 rounded-xl font-bold backdrop-blur-md transition shadow-md">
                            <i class="fa-regular fa-calendar-check"></i> View Interviews
                        </a>
                        <a href="{{ route('client.vendors.browse') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white px-6 py-3 rounded-xl font-bold backdrop-blur-md transition">
                            <i class="fa-solid fa-handshake"></i> Vendors
                        </a>
                    </div>
                </div>
            </div>

            <div class="stat-card gloss backdrop-blur-xl border border-emerald-400/30 rounded-3xl p-6 ring-glow flex flex-col"
                 style="background: linear-gradient(160deg, rgba(16,185,129,.22), rgba(20,184,166,.10));">
                <div class="flex items-center gap-3 mb-4">
                    <div class="p-3 rounded-2xl text-emerald-100 border border-emerald-300/40 shadow-lg shadow-emerald-500/20"
                         style="background: rgba(16,185,129,.30);">
                        <i class="fa-solid fa-briefcase text-xl"></i>
                    </div>
                    <p class="text-emerald-200 text-xs font-bold uppercase tracking-widest">Quick Action</p>
                </div>
                <p class="text-2xl font-extrabold text-white leading-tight">Manage Posted Jobs</p>
                <p class="text-emerald-100/90 text-sm mt-2">Track status and open applicants for each posting.</p>
                <a href="#my-jobs" class="mt-auto pt-5 border-t border-white/10 flex items-center justify-between text-emerald-200 font-bold hover:text-white transition">
                    <span>Jump to My Jobs</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- Jobs table --}}
        <div id="my-jobs" class="gloss bg-white/5 backdrop-blur-xl border border-white/15 rounded-3xl overflow-hidden ring-glow">
            <div class="p-6 border-b border-white/10 bg-gradient-to-r from-slate-900/60 via-slate-900/40 to-slate-900/60 flex flex-col md:flex-row justify-between gap-3 md:items-center">
                <div>
                    <h3 class="text-2xl font-extrabold text-white">My Job Postings</h3>
                    <p class="text-blue-200 text-sm mt-1">Total Jobs: <span class="text-white font-bold">{{ $totalJobs ?? 0 }}</span> · Active: <span class="text-emerald-300 font-bold">{{ $activeJobs ?? 0 }}</span></p>
                </div>
                <a href="{{ route('client.jobs.create') }}" class="neon-btn inline-flex items-center gap-2 text-white px-5 py-2.5 rounded-xl font-extrabold transition">
                    <i class="fa-solid fa-plus"></i> Post New Job
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-widest">
                        <tr>
                            <th class="px-6 py-4">Designation / Role</th>
                            <th class="px-6 py-4">Requirements</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Applicants</th>
                            <th class="px-6 py-4">Posted On</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($jobs as $job)
                            <tr class="fx-row">
                                <td class="px-6 py-4">
                                    <a href="{{ route('jobs.show', $job->id) }}" class="font-bold text-white hover:text-cyan-300 transition">
                                        {{ $job->title }}
                                    </a>
                                    <div class="text-xs text-blue-200 mt-1">{{ $job->location }} · {{ $job->job_type }}</div>
                                </td>
                                <td class="px-6 py-4 text-blue-100">
                                    <div><span class="text-cyan-300 text-[10px] uppercase font-bold">Openings:</span> {{ $job->openings ?? 'N/A' }}</div>
                                    <div><span class="text-cyan-300 text-[10px] uppercase font-bold">Exp:</span> {{ $job->formatted_experience }}</div>
                                    <div><span class="text-cyan-300 text-[10px] uppercase font-bold">Gender:</span> {{ $job->gender_preference ?? 'Any' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusMap = [
                                            'approved' => 'bg-emerald-500/20 text-emerald-100 border-emerald-400/40',
                                            'pending_approval' => 'bg-amber-500/20 text-amber-100 border-amber-400/40',
                                            'on_hold' => 'bg-orange-500/20 text-orange-100 border-orange-400/40',
                                            'closed' => 'bg-slate-500/20 text-slate-100 border-slate-400/40',
                                            'rejected' => 'bg-rose-500/20 text-rose-100 border-rose-400/40',
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusMap[$job->status] ?? 'bg-blue-500/20 text-blue-100 border-blue-400/40' }} shadow-sm">
                                        {{ str_replace('_', ' ', ucfirst($job->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($job->status == 'approved')
                                        <div class="flex flex-col gap-2 items-start">
                                            <a href="{{ route('client.jobs.applicants', $job) }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-500 hover:from-indigo-400 hover:to-purple-400 shadow-md shadow-indigo-500/30 px-3 py-2 rounded-lg font-bold text-xs text-white">
                                                View Applicants ({{ $job->jobApplications->where('status', 'Approved')->count() }})
                                            </a>
                                            @if($job->deactivation_requested_at)
                                                <span class="inline-flex items-center gap-1.5 bg-amber-500/20 border border-amber-400/40 text-amber-200 px-2.5 py-1 rounded-md text-[11px] font-semibold">
                                                    <i class="fa-solid fa-hourglass-half"></i> Deactivation Requested
                                                </span>
                                                <form method="POST" action="{{ route('client.jobs.cancel-deactivation', $job) }}">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="text-[11px] text-blue-200 hover:text-white underline">Cancel request</button>
                                                </form>
                                            @else
                                                <button type="button" onclick="document.getElementById('deact-{{ $job->id }}').classList.toggle('hidden')"
                                                    class="text-[11px] text-rose-200 hover:text-rose-100 underline">
                                                    Request Deactivation
                                                </button>
                                                <form id="deact-{{ $job->id }}" method="POST" action="{{ route('client.jobs.request-deactivation', $job) }}"
                                                      class="hidden mt-1 flex flex-col gap-2 w-64 bg-slate-900/60 border border-rose-400/30 p-3 rounded-lg">
                                                    @csrf
                                                    <textarea name="reason" rows="2" maxlength="1000" placeholder="Reason (optional)"
                                                        class="w-full text-xs bg-slate-900 border border-white/20 rounded p-2 text-white"></textarea>
                                                    <div class="flex gap-2">
                                                        <button type="submit" class="bg-rose-500 hover:bg-rose-400 text-white text-xs font-bold px-3 py-1.5 rounded">Submit</button>
                                                        <button type="button" onclick="document.getElementById('deact-{{ $job->id }}').classList.add('hidden')" class="text-xs text-slate-300 hover:text-white">Cancel</button>
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                    @elseif($job->status === 'pending_approval')
                                        <a href="{{ route('client.jobs.edit', $job) }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-400 to-orange-400 hover:from-amber-300 hover:to-orange-300 shadow-md shadow-amber-500/30 px-3 py-2 rounded-lg font-bold text-xs text-slate-900">
                                            <i class="fa-solid fa-pen-to-square"></i> Edit Pending Job
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Not available</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-blue-200">{{ $job->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-blue-100">
                                    <i class="fa-regular fa-folder-open text-5xl mb-3 text-cyan-300"></i>
                                    <p class="font-bold text-white text-lg">No jobs posted yet</p>
                                    <a href="{{ route('client.jobs.create') }}" class="text-cyan-300 hover:text-white underline">Post your first job</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
