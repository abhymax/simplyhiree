@extends('layouts.app')

@section('content')
<style>
    .fx-card { transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
    .fx-card:hover { transform: translateY(-5px); box-shadow: 0 18px 34px rgba(14,165,233,.22); border-color: rgba(255,255,255,.32); }
    .fx-row { transition: all .2s ease; border-left: 4px solid transparent; }
    .fx-row:hover { transform: scale(1.003); background: rgba(255,255,255,.08) !important; border-left-color: #22d3ee; }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Client Workspace
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">Overview</h1>
                <p class="text-blue-200 mt-2 text-lg">
                    Welcome back, <span class="text-white font-semibold">{{ Auth::user()->name }}</span>.
                </p>
            </div>

            <div class="mt-6 md:mt-0">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-2xl flex items-center gap-4">
                    <div class="p-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg shadow-lg">
                        <i class="fa-regular fa-calendar text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-300 font-bold uppercase">Today's Date</p>
                        <p class="text-white font-bold">{{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <div class="col-span-1 lg:col-span-2 bg-gradient-to-r from-indigo-600/90 to-blue-600/90 rounded-3xl p-1 shadow-2xl">
                <div class="h-full bg-slate-900/50 backdrop-blur-xl rounded-[20px] p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 p-6 opacity-10">
                        <i class="fa-solid fa-chart-line text-9xl text-white"></i>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="bg-white/20 p-2 rounded-lg"><i class="fa-solid fa-heart-pulse"></i></span>
                            <h3 class="font-bold text-xl text-white">Daily Pulse</h3>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <a href="{{ route('client.interviews.today') }}" class="block rounded-xl px-2 py-1 hover:bg-white/10 transition">
                                <span class="text-5xl font-black text-white tracking-tighter">{{ $todayInterviews ?? 0 }}</span>
                                <p class="text-blue-200 font-medium mt-1">Interviews Today</p>
                            </a>
                            <a href="{{ route('client.billing') }}" class="block rounded-xl px-2 py-1 hover:bg-white/10 transition">
                                <span class="text-5xl font-black text-white tracking-tighter">{{ $dueInvoicesCount ?? 0 }}</span>
                                <p class="text-blue-200 font-medium mt-1">Payments Due</p>
                            </a>
                            <a href="{{ route('client.dashboard') }}#my-jobs" class="block rounded-xl px-2 py-1 hover:bg-white/10 transition">
                                <span class="text-5xl font-black text-white tracking-tighter">{{ $totalApplicants ?? 0 }}</span>
                                <p class="text-blue-200 font-medium mt-1">Total Applicants</p>
                            </a>
                        </div>

                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('client.jobs.create') }}" class="fx-btn inline-flex items-center gap-2 bg-white text-blue-900 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg">
                                Post New Job <i class="fa-solid fa-arrow-right"></i>
                            </a>
                            <a href="{{ route('client.interviews.today') }}" class="fx-btn inline-flex items-center gap-2 bg-white/15 border border-white/20 text-white px-6 py-3 rounded-xl font-bold hover:bg-white/25 transition">
                                View Interviews
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 hover:bg-white/15 transition duration-300">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-emerald-500/20 rounded-2xl text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-briefcase text-2xl"></i>
                    </div>
                </div>

                <p class="text-blue-300 text-sm font-bold uppercase tracking-wider">Quick Action</p>
                <p class="text-2xl font-extrabold text-white mt-2">Manage Posted Jobs</p>
                <p class="text-slate-300 text-sm mt-1">Track status and open applicants for each posting.</p>

                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="#my-jobs" class="w-full flex items-center justify-between text-white font-bold hover:text-emerald-400 transition-colors">
                        <span>Open</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div id="my-jobs" class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-6 border-b border-white/10 bg-slate-900/40 flex flex-col md:flex-row justify-between gap-3 md:items-center">
                <div>
                    <h3 class="text-2xl font-bold text-white">My Job Postings</h3>
                    <p class="text-blue-100 text-sm mt-1">Total Jobs: {{ $totalJobs ?? 0 }} | Active: {{ $activeJobs ?? 0 }}</p>
                </div>
                <a href="{{ route('client.jobs.create') }}" class="fx-btn inline-flex items-center gap-2 bg-cyan-500 hover:bg-cyan-400 text-slate-900 px-4 py-2 rounded-lg font-black">
                    <i class="fa-solid fa-plus"></i> New Job
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
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
                                    <div class="text-xs text-blue-200 mt-1">{{ $job->location }} | {{ $job->job_type }}</div>
                                </td>

                                <td class="px-6 py-4 text-blue-100">
                                    <div><span class="text-cyan-300 text-xs uppercase font-bold">Openings:</span> {{ $job->openings ?? 'N/A' }}</div>
                                    <div><span class="text-cyan-300 text-xs uppercase font-bold">Exp:</span> {{ $job->experienceLevel->name ?? 'Any' }}</div>
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
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusMap[$job->status] ?? 'bg-blue-500/20 text-blue-100 border-blue-400/40' }}">
                                        {{ str_replace('_', ' ', ucfirst($job->status)) }}
                                    </span>
                                </td>

                                <td class="px-6 py-4">
                                    @if($job->status == 'approved')
                                        <a href="{{ route('client.jobs.applicants', $job) }}" class="fx-btn inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-3 py-2 rounded-lg font-bold text-xs text-white">
                                            View Applicants ({{ $job->jobApplications->where('status', 'Approved')->count() }})
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Not available</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-blue-200">{{ $job->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center text-blue-100">
                                    <i class="fa-regular fa-folder-open text-4xl mb-3"></i>
                                    <p class="font-bold text-white">No jobs posted yet</p>
                                    <a href="{{ route('client.jobs.create') }}" class="text-cyan-300 hover:text-white">Post your first job</a>
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
