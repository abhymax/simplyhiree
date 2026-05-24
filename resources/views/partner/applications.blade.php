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

        @php
            $activeStatus = request('status');
            $kept = request()->only(['search', 'job_id', 'client', 'date_from', 'date_to']);
            $tabs = [
                ['key' => null,                 'label' => 'All',                 'count' => $statusCounts['all'],                 'color' => 'cyan'],
                ['key' => 'Pending Review',     'label' => 'Candidate Applied',   'count' => $statusCounts['Pending Review'],      'color' => 'amber'],
                ['key' => 'Approved',           'label' => 'To Be Lined Up',      'count' => $statusCounts['Approved'],            'color' => 'emerald'],
                ['key' => 'Interview Scheduled','label' => 'Interview Scheduled', 'count' => $statusCounts['Interview Scheduled'], 'color' => 'indigo'],
                ['key' => 'Interviewed',        'label' => 'Interviewed',         'count' => $statusCounts['Interviewed'],         'color' => 'violet'],
                ['key' => 'Selected',           'label' => 'Selected',            'count' => $statusCounts['Selected'],            'color' => 'sky'],
                ['key' => 'Joined',             'label' => 'Joined',              'count' => $statusCounts['Joined'],              'color' => 'emerald'],
                ['key' => 'Did Not Join / Left','label' => 'DNJ / Left',          'count' => $statusCounts['Did Not Join / Left'], 'color' => 'rose'],
                ['key' => 'Rejected',           'label' => 'Rejected',            'count' => $statusCounts['Rejected'],            'color' => 'rose'],
            ];
            $colorMap = [
                'cyan'    => 'bg-cyan-500/20 text-cyan-100 border-cyan-400/60',
                'amber'   => 'bg-amber-500/20 text-amber-100 border-amber-400/60',
                'emerald' => 'bg-emerald-500/20 text-emerald-100 border-emerald-400/60',
                'indigo'  => 'bg-indigo-500/20 text-indigo-100 border-indigo-400/60',
                'violet'  => 'bg-violet-500/20 text-violet-100 border-violet-400/60',
                'sky'     => 'bg-sky-500/20 text-sky-100 border-sky-400/60',
                'rose'    => 'bg-rose-500/20 text-rose-100 border-rose-400/60',
            ];
        @endphp

        {{-- Status pill tabs --}}
        <div class="mb-5 -mx-1 overflow-x-auto pb-2">
            <div class="flex items-center gap-3 px-1 min-w-max">
                @foreach($tabs as $t)
                    @php
                        $isActive = (string) $activeStatus === (string) $t['key'];
                        $linkParams = array_merge($kept, $t['key'] ? ['status' => $t['key']] : []);
                        $activeCls  = $colorMap[$t['color']];
                    @endphp
                    <a href="{{ route('partner.applications', $linkParams) }}"
                       style="padding-left: 1.5rem !important; padding-right: 1rem !important;"
                       class="inline-flex items-center gap-3 py-2 rounded-full text-xs font-bold border whitespace-nowrap transition
                              {{ $isActive ? $activeCls.' shadow-lg' : 'bg-white/5 text-slate-300 border-white/15 hover:bg-white/10 hover:text-white' }}">
                        <span class="leading-none">{{ $t['label'] }}</span>
                        <span class="inline-flex items-center justify-center min-w-[1.75rem] h-6 px-2 rounded-full text-[11px] font-extrabold leading-none border {{ $isActive ? 'bg-white/30 text-white border-white/30' : 'bg-white/15 text-white border-white/20' }}">{{ $t['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Compact filter row --}}
        <form method="GET" action="{{ route('partner.applications') }}" class="flex flex-wrap items-center gap-2 mb-5">
            <input type="hidden" name="status" value="{{ $activeStatus }}">
            @php $fld = 'h-9 bg-slate-900/60 border border-white/15 rounded-md text-white text-sm px-2.5 focus:ring-1 focus:ring-cyan-400 focus:border-cyan-400'; @endphp

            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-white/70 text-sm pointer-events-none z-10"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search candidate name or email"
                       style="padding-left: 2.75rem !important;"
                       class="h-9 w-full bg-slate-900/60 border border-white/15 rounded-md text-white text-sm pr-3 focus:ring-1 focus:ring-cyan-400 focus:border-cyan-400">
            </div>
            <select name="job_id" class="{{ $fld }} max-w-[200px]">
                <option value="" class="bg-slate-900">All Jobs</option>
                @foreach($filterJobs as $j)
                    <option value="{{ $j->id }}" class="bg-slate-900" {{ request('job_id') == $j->id ? 'selected' : '' }}>
                        {{ \Illuminate\Support\Str::limit($j->title, 24) }}
                    </option>
                @endforeach
            </select>
            <select name="client" class="{{ $fld }} max-w-[180px]">
                <option value="" class="bg-slate-900">All Clients</option>
                @foreach($filterClients as $c)
                    <option value="{{ $c }}" class="bg-slate-900" {{ request('client') === $c ? 'selected' : '' }}>{{ \Illuminate\Support\Str::limit($c, 20) }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" title="From" class="{{ $fld }} w-[150px] [color-scheme:dark]">
            <span class="text-slate-400 text-xs">to</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" title="To" class="{{ $fld }} w-[150px] [color-scheme:dark]">
            <button type="submit" class="h-9 px-4 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-md text-sm">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
            @if(request()->anyFilled(['search','client','job_id','date_from','date_to']))
                <a href="{{ route('partner.applications', $activeStatus ? ['status' => $activeStatus] : []) }}" title="Clear filters"
                   class="h-9 w-9 bg-rose-500 hover:bg-rose-400 text-white rounded-md inline-flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </form>

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
                                $status = $application->effectiveStatus();
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
                            <tr class="fx-row">
                                <td class="px-6 py-5">
                                    <a href="{{ route('partner.applications.show', $application->id) }}" class="flex items-center gap-4 group">
                                        <div class="h-11 w-11 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center font-bold text-white ring-2 ring-white/20 group-hover:ring-cyan-400 transition-all">{{ $initial }}</div>
                                        <div>
                                            <div class="font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $name }}</div>
                                            <div class="text-cyan-200 text-xs">{{ $application->candidate->email ?? 'N/A' }}</div>
                                        </div>
                                    </a>
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
                                    <div class="text-amber-300 font-bold">
                                        @if(optional($application->job)->is_company_confidential)
                                            <i class="fa-solid fa-user-secret mr-1"></i> Confidential Client
                                        @else
                                            {{ $application->job->company_name ?? 'N/A' }}
                                        @endif
                                    </div>
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