@extends('layouts.app')

@section('content')
<style>
    .fx-row { transition: all .22s ease; border-left: 4px solid transparent; }
    .fx-row:hover { transform: scale(1.004); background: rgba(255,255,255,.10) !important; border-left-color: #22d3ee; }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500 rounded-full mix-blend-screen blur-[120px] opacity-30"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <a href="{{ route('partner.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-extrabold tracking-tight">All Applications</h1>
                <p class="text-blue-100 mt-1">Track your submitted candidates pipeline</p>
            </div>
            <div class="mt-4 md:mt-0 bg-blue-600 border border-blue-400 rounded-2xl px-5 py-3 shadow-xl">
                <div class="text-blue-100 text-xs font-bold uppercase">Total Count</div>
                <div class="text-3xl font-black">{{ $applications->total() }}</div>
            </div>
        </div>

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-5">Candidate</th>
                            <th class="px-6 py-5">Job Details</th>
                            <th class="px-6 py-5">Company</th>
                            <th class="px-6 py-5">Status</th>
                            <th class="px-6 py-5">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($applications as $application)
                            @php
                                $name = $application->candidate ? trim(($application->candidate->first_name ?? '').' '.($application->candidate->last_name ?? '')) : 'Candidate Deleted';
                                $initial = strtoupper(substr($name ?: 'U', 0, 1));
                                $status = $application->status;
                                $statusClasses = [
                                    'Pending Review' => 'bg-amber-500/20 text-amber-100 border-amber-400/40',
                                    'Approved' => 'bg-emerald-500/20 text-emerald-100 border-emerald-400/40',
                                    'Interview Scheduled' => 'bg-indigo-500/20 text-indigo-100 border-indigo-400/40',
                                    'Selected' => 'bg-cyan-500/20 text-cyan-100 border-cyan-400/40',
                                    'Rejected' => 'bg-rose-500/20 text-rose-100 border-rose-400/40',
                                ];
                            @endphp
                            <tr class="fx-row">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-11 w-11 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white ring-2 ring-white/20">{{ $initial }}</div>
                                        <div>
                                            <div class="font-bold text-white">{{ $name }}</div>
                                            <div class="text-cyan-200 text-xs">{{ $application->candidate->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($application->job)
                                        <a href="{{ route('partner.jobs.show', $application->job->id) }}" class="fx-btn inline-flex items-center gap-2 text-white font-bold hover:text-cyan-300">
                                            {{ $application->job->title }}
                                        </a>
                                    @else
                                        <span class="text-slate-400 italic">Job No Longer Available</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-amber-300 font-bold">{{ $application->job->company_name ?? 'N/A' }}</div>
                                    <div class="text-blue-200 text-xs">{{ $application->job->location ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $statusClasses[$status] ?? 'bg-slate-500/20 text-slate-100 border-slate-400/40' }}">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-blue-200 text-xs">{{ $application->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <i class="fa-regular fa-folder-open text-5xl text-blue-200 mb-3"></i>
                                    <p class="text-white font-bold">No applications submitted yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-white/10 bg-slate-900/80">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>
@endsection