@extends('layouts.client')

@section('client_content')
<style>
    .date-white::-webkit-calendar-picker-indicator { filter: invert(1) brightness(4) contrast(1.5) !important; cursor: pointer; opacity: 1 !important; width: 18px; height: 18px; }
    .date-white { color-scheme: dark; }
    .date-picker-button { position: relative; display: inline-flex; height: 3.2rem; width: 3.45rem; flex-direction: column; align-items: center; justify-content: center; gap: .12rem; border: 1px solid rgba(96,165,250,.38); border-radius: .5rem; background: rgba(5,21,56,.92); color: #fff; cursor: pointer; }
    .date-picker-button input { position: absolute; inset: 0; width: 100%; opacity: 0; cursor: pointer; }
    .date-picker-label { max-width: 3rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: #bae6fd; font-size: .55rem; font-weight: 800; line-height: 1; }
    .apps-table thead th { padding-top: .75rem !important; padding-bottom: .75rem !important; }
    .apps-table tbody td { padding-top: .75rem !important; padding-bottom: .75rem !important; vertical-align: middle; }
    .apps-table .cand-avatar { width: 36px !important; height: 36px !important; font-size: .9rem !important; }
    .status-pill { padding: .35rem .7rem !important; font-size: .7rem !important; gap: .35rem !important; }
    .app-filter-control { background: rgba(5, 21, 56, .92) !important; border-color: rgba(96, 165, 250, .38) !important; box-shadow: inset 0 1px 0 rgba(255,255,255,.04); }
    .app-filter-control:focus { border-color: #67e8f9 !important; box-shadow: 0 0 0 3px rgba(34,211,238,.15) !important; outline: none; }
    .applications-filter-grid { display: grid; grid-template-columns: minmax(0, 1fr); }
    .screened-action-row { background: linear-gradient(90deg, rgba(245,158,11,.24), rgba(251,191,36,.11) 48%, transparent 84%); animation: screened-action-pulse 2.7s ease-in-out infinite; }
    .screened-action-row:hover { background: linear-gradient(90deg, rgba(245,158,11,.32), rgba(251,191,36,.18) 48%, rgba(255,255,255,.05) 84%) !important; animation-play-state: paused; }
    .screened-action-pill { animation: screened-pill-pulse 3.8s ease-in-out infinite; }
    @keyframes screened-action-pulse { 0%,100% { box-shadow: inset 5px 0 0 rgba(251,191,36,.52), inset 0 0 12px rgba(245,158,11,.05); } 50% { box-shadow: inset 5px 0 0 rgba(253,224,71,1), inset 0 0 38px rgba(245,158,11,.23); } }
    @keyframes screened-pill-pulse { 0%,100% { filter: brightness(1); transform: scale(1); } 50% { filter: brightness(1.35); transform: scale(1.035); } }
    @media (prefers-reduced-motion: reduce) { .screened-action-row, .screened-action-pill { animation: none; } }
    @media (min-width: 1100px) {
        .applications-filter-grid { grid-template-columns: minmax(10rem, 1.15fr) 9rem 9rem 10rem 9rem 3.45rem 1.2rem 3.45rem 5.8rem auto; align-items: center; }
    }
</style>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <a href="{{ route('client.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">{{ $pageTitle ?? 'All Applications' }}</h1>
                <p class="text-blue-200 mt-2 text-lg">{{ $pageSubtitle ?? 'Every candidate sent to your jobs.' }}</p>
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
            <form method="GET" action="{{ route('client.applications.index') }}" class="applications-filter-grid gap-2">
                @php $fld = 'app-filter-control h-10 border rounded-lg text-white text-sm font-medium px-3'; @endphp

                <div class="relative grow min-w-[180px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-white/70 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email" class="{{ $fld }} w-full pl-9">
                </div>

                <select name="status" class="{{ $fld }} min-w-[140px] pr-8" style="appearance: auto; -webkit-appearance: menulist;">
                    <option value="" class="text-gray-400">All screened</option>
                    @foreach(['screened' => 'Screened by Super Admin', 'Shortlisted' => 'Shortlisted', 'Maybe' => 'Maybe', 'Interview Scheduled' => 'Interview Scheduled', 'Selected' => 'Selected', 'Joined' => 'Joined'] as $value => $label)
                        <option value="{{ $value }}" class="bg-slate-900" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select name="sort" class="{{ $fld }} min-w-[130px] pr-8" style="appearance: auto; -webkit-appearance: menulist;" title="Sort applications">
                    <option value="latest" class="bg-slate-900" {{ ($sort ?? request('sort', 'latest')) === 'latest' ? 'selected' : '' }}>Latest first</option>
                    <option value="name" class="bg-slate-900" {{ ($sort ?? request('sort')) === 'name' ? 'selected' : '' }}>Candidate name</option>
                    <option value="modified" class="bg-slate-900" {{ ($sort ?? request('sort')) === 'modified' ? 'selected' : '' }}>Recently modified</option>
                </select>

                <select name="job_id" class="{{ $fld }} min-w-[150px] max-w-[220px] pr-8" style="appearance: auto; -webkit-appearance: menulist;">
                    <option value="" class="text-gray-400">All Jobs</option>
                    @foreach($jobs as $j)
                        <option value="{{ $j->id }}" class="bg-slate-900" {{ (int) request('job_id') === (int) $j->id ? 'selected' : '' }}>{{ Str::limit($j->title, 24) }}</option>
                    @endforeach
                </select>

                <input type="text" name="partner_search" value="{{ request('partner_search') }}" list="client-partners" placeholder="Search partner" class="{{ $fld }} min-w-[150px] max-w-[200px]">
                <datalist id="client-partners">@foreach($partners as $p)<option value="{{ $p->name }}">@endforeach</datalist>

                <label class="date-picker-button" title="From date" aria-label="From date"><i class="fa-regular fa-calendar-days"></i><span class="date-picker-label">{{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d M') : 'From' }}</span><input type="date" name="date_from" value="{{ request('date_from') }}" max="{{ date('Y-m-d') }}" onchange="this.parentElement.querySelector('.date-picker-label').textContent = this.value ? new Date(this.value + 'T00:00:00').toLocaleDateString('en-GB',{day:'2-digit',month:'short'}) : 'From'"></label>
                <span class="text-center text-[10px] font-extrabold uppercase tracking-wide text-cyan-100">to</span>
                <label class="date-picker-button" title="To date" aria-label="To date"><i class="fa-regular fa-calendar-days"></i><span class="date-picker-label">{{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d M') : 'To' }}</span><input type="date" name="date_to" value="{{ request('date_to') }}" max="{{ date('Y-m-d') }}" onchange="this.parentElement.querySelector('.date-picker-label').textContent = this.value ? new Date(this.value + 'T00:00:00').toLocaleDateString('en-GB',{day:'2-digit',month:'short'}) : 'To'"></label>

                <button type="submit" class="h-10 px-4 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-sm shadow flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>

                @if(request()->anyFilled(['search','status','sort','job_id','partner_id','partner_search','date_from','date_to']))
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
                                $needsClientAction = $app->status === 'Approved' && empty($app->hiring_status);
                                $awaitingJoiningOutcome = $app->hiring_status === 'Selected' && empty($app->joined_status);
                            @endphp
                            <tr class="{{ $needsClientAction ? 'screened-action-row' : 'hover:bg-white/10' }} transition">
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-3">
                                        <a href="{{ route('client.applications.show', $app->id) }}" class="cand-avatar h-11 w-11 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold ring-2 ring-white/20 shrink-0 hover:scale-105 transition-transform" title="View details for {{ $name }}">
                                            {{ $initial }}
                                        </a>
                                        <div class="min-w-0">
                                            <a href="{{ route('client.applications.show', $app->id) }}" class="font-bold text-white hover:text-cyan-300 transition-colors" title="View details for {{ $name }}">
                                                {{ $name }}
                                            </a>
                                            @if(in_array($app->hiring_status, ['Shortlisted', 'shortlisted'], true))
                                                <span class="ml-1 inline-flex items-center rounded-full border border-emerald-300/35 bg-emerald-500/15 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wide text-emerald-100">Shortlisted</span>
                                            @elseif($app->hiring_status === 'Maybe')
                                                <span class="ml-1 inline-flex items-center rounded-full border border-amber-300/35 bg-amber-500/15 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wide text-amber-100">Maybe</span>
                                            @endif
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
                                    @if($needsClientAction)
                                        <span class="screened-action-pill status-pill inline-flex items-center rounded-full bg-emerald-800 text-emerald-50 border border-emerald-300 font-extrabold shadow-sm shadow-emerald-950/50"><i class="fa-solid fa-shield-halved"></i> Screened by Admin</span>
                                    @elseif($efLc === 'shortlisted')
                                        <span class="status-pill inline-flex items-center rounded-full bg-emerald-500/90 text-white border border-emerald-300 font-extrabold"><i class="fa-solid fa-check"></i> Shortlisted</span>
                                    @elseif($efLc === 'maybe')
                                        <span class="status-pill inline-flex items-center rounded-full bg-amber-400 text-slate-950 border border-amber-200 font-extrabold"><i class="fa-regular fa-bookmark"></i> Maybe</span>
                                    @elseif($efLc === 'pending review')
                                        <span class="status-pill inline-flex items-center rounded-full bg-amber-500 text-black border border-amber-300 font-extrabold animate-pulse"><i class="fa-regular fa-clock"></i> Pending Review</span>
                                    @elseif($efLc === 'approved')
                                        <span class="status-pill inline-flex items-center rounded-full bg-emerald-800 text-emerald-50 border border-emerald-300 font-extrabold shadow-sm shadow-emerald-950/50"><i class="fa-solid fa-shield-halved"></i> Screened by Admin</span>
                                    @elseif($efLc === 'rejected' || $efLc === 'client rejected')
                                        <span class="status-pill inline-flex items-center rounded-full bg-red-600 text-white border border-red-400 font-extrabold"><i class="fa-solid fa-xmark"></i> Rejected</span>
                                    @elseif($efLc === 'interview scheduled' || $efLc === 'interviewed' || $efLc === 'no-show')
                                        <span class="status-pill inline-flex items-center rounded-full bg-indigo-500 text-white border border-indigo-400 font-extrabold"><i class="fa-solid fa-video"></i> {{ $eff }}</span>
                                    @elseif($efLc === 'selected by superadmin')
                                        <span class="status-pill inline-flex items-center rounded-full bg-purple-600 text-white border border-purple-400 font-extrabold"><i class="fa-solid fa-user-shield"></i> Selected by Admin</span>
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
                                        @if($awaitingJoiningOutcome)
                                            <form method="POST" action="{{ route('client.applications.markJoined', $app) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-600 px-3 text-xs font-extrabold text-white shadow-md shadow-emerald-950/40 transition hover:-translate-y-0.5 hover:bg-emerald-500" title="Confirm that {{ $name }} has joined">
                                                    <i class="fa-solid fa-user-check"></i> Mark Joined
                                                </button>
                                            </form>
                                        @endif
                                        @if($resumePath)
                                            <a href="{{ asset('storage/' . $resumePath) }}" target="_blank" title="Download CV"
                                               class="inline-flex items-center justify-center w-9 h-9 bg-slate-700 hover:bg-cyan-600 text-white rounded-lg border border-slate-600 hover:border-cyan-400 shrink-0">
                                                <i class="fa-solid fa-download text-sm"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('client.applications.show', $app->id) }}"
                                           class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-bold border border-indigo-400 shadow-md whitespace-nowrap shrink-0"
                                           style="padding: 0.55rem 1.1rem;" title="View details for {{ $name }}">
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
@endsection
