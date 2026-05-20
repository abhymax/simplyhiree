@extends('layouts.app')

@section('content')
<style>
    .date-white::-webkit-calendar-picker-indicator { filter: invert(1) brightness(1.5); cursor: pointer; }
    .date-white { color-scheme: dark; }
    .apps-table thead th { padding-top: .75rem !important; padding-bottom: .75rem !important; }
    .apps-table tbody td { padding-top: .75rem !important; padding-bottom: .75rem !important; vertical-align: middle; }
    .apps-table .cand-avatar { width: 36px !important; height: 36px !important; font-size: .9rem !important; }
    .status-pill { padding: .35rem .7rem !important; font-size: .7rem !important; gap: .35rem !important; }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">

    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[120px] opacity-40 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500 rounded-full mix-blend-screen filter blur-[120px] opacity-40"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <a href="{{ route('client.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">All Applications</h1>
                <p class="text-blue-200 mt-2 text-lg">Every candidate sent to your jobs.</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-2xl">
                <p class="text-xs text-blue-300 font-bold uppercase">Total</p>
                <p class="text-white font-extrabold text-2xl">{{ $applications->total() }}</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 p-4 rounded-xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-100">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- FILTER BAR --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-3 mb-5">
            <form method="GET" action="{{ route('client.applications.index') }}" class="flex flex-wrap gap-2 items-center">
                @php $fld = 'h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm font-medium px-3'; @endphp

                <div class="relative grow min-w-[180px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-white/70 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email" class="{{ $fld }} w-full pl-9">
                </div>

                <select name="status" class="{{ $fld }} min-w-[140px]">
                    <option value="" class="text-gray-400">All Statuses</option>
                    @foreach(['Pending Review','Approved','Rejected','Interview Scheduled','Selected','Joined'] as $s)
                        <option value="{{ $s }}" class="bg-slate-900" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>

                <select name="job_id" class="{{ $fld }} min-w-[150px] max-w-[220px]">
                    <option value="" class="text-gray-400">All Jobs</option>
                    @foreach($jobs as $j)
                        <option value="{{ $j->id }}" class="bg-slate-900" {{ (int) request('job_id') === (int) $j->id ? 'selected' : '' }}>{{ Str::limit($j->title, 24) }}</option>
                    @endforeach
                </select>

                <select name="partner_id" class="{{ $fld }} min-w-[150px] max-w-[200px]">
                    <option value="" class="text-gray-400">All Partners</option>
                    @foreach($partners as $p)
                        <option value="{{ $p->id }}" class="bg-slate-900" {{ (int) request('partner_id') === (int) $p->id ? 'selected' : '' }}>{{ Str::limit($p->name, 22) }}</option>
                    @endforeach
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" max="{{ date('Y-m-d') }}" class="{{ $fld }} w-[155px] date-white">
                <span class="text-white font-bold text-sm px-1">to</span>
                <input type="date" name="date_to" value="{{ request('date_to') }}" max="{{ date('Y-m-d') }}" class="{{ $fld }} w-[155px] date-white">

                <select name="per_page" onchange="this.form.submit()" class="{{ $fld }}">
                    @foreach($allowedPerPage as $opt)
                        <option value="{{ $opt }}" class="bg-slate-900" {{ $perPage === $opt ? 'selected' : '' }}>{{ $opt }}/page</option>
                    @endforeach
                </select>

                <button type="submit" class="h-10 px-4 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-sm shadow flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>

                @if(request()->anyFilled(['search','status','job_id','partner_id','date_from','date_to']))
                    <a href="{{ route('client.applications.index') }}" class="h-10 w-10 bg-rose-500 hover:bg-rose-400 text-white rounded-lg flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </form>
        </div>

        {{-- TABLE --}}
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="apps-table min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-5">Candidate</th>
                            <th class="px-6 py-5">Job Details</th>
                            <th class="px-6 py-5">Source</th>
                            <th class="px-6 py-5">Status</th>
                            <th class="px-6 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @forelse($applications as $app)
                            @php
                                $agency = $app->candidate;
                                $direct = $app->candidateUser;
                                $name = trim(($agency?->first_name ?? '') . ' ' . ($agency?->last_name ?? ''));
                                if ($name === '') $name = $direct?->name ?? 'N/A';
                                $email = $agency?->email ?? $direct?->email ?? '';
                                $partner = $agency?->partner;
                                $initial = strtoupper(substr($name, 0, 1)) ?: 'U';
                                $appCode = $app->application_code ?? ('SH-APP-' . str_pad((string) $app->id, 6, '0', STR_PAD_LEFT));
                                $candCode = $agency?->candidate_code ?? $direct?->entity_code ?? 'SH-CND-NA';
                                $jobCode = $app->job?->job_code ?? 'SH-JOB-NA';
                                $resumePath = $agency?->resume_path ?? $direct?->profile?->resume_path;
                            @endphp
                            <tr class="hover:bg-white/10 transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="cand-avatar h-11 w-11 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold ring-2 ring-white/20 shrink-0">{{ $initial }}</div>
                                        <div class="min-w-0">
                                            <div class="font-bold text-white">{{ $name }}</div>
                                            <div class="text-cyan-200 text-xs truncate"><i class="fa-regular fa-envelope mr-1"></i> {{ $email }}</div>
                                            <div class="text-[10px] text-slate-300 mt-0.5">{{ $appCode }} · {{ $candCode }} · {{ $app->created_at->format('M d, Y') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-white">{{ $app->job->title ?? 'Deleted Job' }}</div>
                                    <div class="text-[10px] text-slate-300 mt-0.5">{{ $jobCode }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($partner)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-purple-600 text-white text-[11px] font-bold">
                                            <i class="fa-solid fa-handshake"></i> {{ Str::limit($partner->name, 14) }}
                                        </span>
                                        <div class="text-[10px] text-slate-300 mt-0.5">{{ $partner->entity_code ?? ('SH-PRT-' . str_pad((string) $partner->id, 6, '0', STR_PAD_LEFT)) }}</div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-700 text-white text-[11px] font-bold border border-slate-500"><i class="fa-solid fa-globe"></i> Direct</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    @php $eff = $app->effectiveStatus(); $efLc = strtolower($eff); @endphp
                                    @if($efLc === 'pending review')
                                        <span class="status-pill inline-flex items-center rounded-full bg-amber-500 text-black border border-amber-300 font-extrabold animate-pulse"><i class="fa-regular fa-clock"></i> Pending Review</span>
                                    @elseif($efLc === 'approved')
                                        <span class="status-pill inline-flex items-center rounded-full bg-emerald-500 text-white border border-emerald-400 font-extrabold"><i class="fa-solid fa-check"></i> Approved</span>
                                    @elseif($efLc === 'rejected' || $efLc === 'client rejected')
                                        <span class="status-pill inline-flex items-center rounded-full bg-red-600 text-white border border-red-400 font-extrabold"><i class="fa-solid fa-xmark"></i> Rejected</span>
                                    @elseif($efLc === 'interview scheduled' || $efLc === 'interviewed' || $efLc === 'no-show')
                                        <span class="status-pill inline-flex items-center rounded-full bg-indigo-500 text-white border border-indigo-400 font-extrabold"><i class="fa-solid fa-video"></i> {{ $eff }}</span>
                                    @elseif($efLc === 'selected by superadmin')
                                        <span class="status-pill inline-flex items-center rounded-full bg-purple-600 text-white border border-purple-400 font-extrabold"><i class="fa-solid fa-user-shield"></i> Selected by Superadmin</span>
                                    @elseif($efLc === 'selected')
                                        <span class="status-pill inline-flex items-center rounded-full bg-cyan-500 text-white border border-cyan-400 font-extrabold"><i class="fa-solid fa-circle-check"></i> Selected</span>
                                    @elseif($efLc === 'joined')
                                        <span class="status-pill inline-flex items-center rounded-full bg-emerald-600 text-white border border-emerald-400 font-extrabold"><i class="fa-solid fa-user-check"></i> Joined</span>
                                    @elseif($efLc === 'left')
                                        <span class="status-pill inline-flex items-center rounded-full bg-rose-600 text-white border border-rose-400 font-extrabold"><i class="fa-solid fa-arrow-right-from-bracket"></i> Left</span>
                                    @else
                                        <span class="status-pill inline-flex items-center rounded-full bg-blue-600 text-white border border-blue-400 font-extrabold"><i class="fa-solid fa-circle-info"></i> {{ $eff }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right" style="min-width: 180px;">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        @if($resumePath)
                                            <a href="{{ asset('storage/' . $resumePath) }}" target="_blank" title="Download CV"
                                               class="inline-flex items-center justify-center w-9 h-9 bg-slate-700 hover:bg-cyan-600 text-white rounded-lg border border-slate-600 hover:border-cyan-400 shrink-0">
                                                <i class="fa-solid fa-download text-sm"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('client.jobs.applicants', $app->job_id) }}#app-{{ $app->id }}"
                                           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-bold border border-indigo-400 shadow-md whitespace-nowrap shrink-0"
                                           style="padding: 0.55rem 1.1rem;">
                                            <i class="fa-regular fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center">
                                    <div class="bg-white/10 inline-block p-6 rounded-full mb-3 border border-white/10"><i class="fa-regular fa-folder-open text-5xl text-blue-200"></i></div>
                                    <p class="text-xl font-bold text-white">No applications found.</p>
                                    <p class="text-blue-200 mt-2">Adjust filters or wait for vendors to start submitting.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="p-4 border-t border-white/10 bg-slate-900/60">
                    {{ $applications->onEachSide(1)->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
