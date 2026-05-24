@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-rose-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
            <div>
                <a href="{{ route('partner.dashboard') }}" class="inline-flex items-center text-blue-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">Replacement Requests</h1>
                <p class="text-blue-200 mt-2 text-lg">Candidates whose clients have requested a replacement.</p>
            </div>
            <div class="mt-4 md:mt-0 bg-amber-500/20 border border-amber-400/40 px-5 py-3 rounded-2xl shadow-xl">
                <div class="text-amber-100 text-xs font-bold uppercase">Open</div>
                <div class="text-3xl font-black text-white">{{ $requests->total() }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('partner.replacements') }}"
              class="mb-6 bg-white/5 border border-white/10 rounded-2xl p-4 grid grid-cols-1 sm:grid-cols-4 gap-3 backdrop-blur-md">
            @php $fld = 'h-10 bg-slate-900/60 border border-white/20 rounded-lg text-white text-sm px-3 focus:ring-2 focus:ring-amber-400 focus:border-amber-400'; @endphp
            <div class="sm:col-span-2 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-white/70 text-sm pointer-events-none z-10"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search candidate name or email"
                       class="h-10 w-full bg-slate-900/60 border border-white/20 rounded-lg text-white text-sm pl-10 pr-3 focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
            </div>
            <select name="job_id" class="{{ $fld }}">
                <option value="" class="bg-slate-900">All Jobs</option>
                @foreach($jobs as $j)
                    <option value="{{ $j->id }}" class="bg-slate-900" {{ (string) request('job_id') === (string) $j->id ? 'selected' : '' }}>
                        {{ \Illuminate\Support\Str::limit($j->title, 30) }}
                    </option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-amber-500 hover:bg-amber-400 text-slate-900 font-bold rounded-lg px-4">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                @if(request()->anyFilled(['search', 'job_id', 'status']))
                    <a href="{{ route('partner.replacements') }}" class="bg-rose-500 hover:bg-rose-400 text-white font-bold rounded-lg px-3 inline-flex items-center"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </div>
        </form>

        {{-- List --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-3xl shadow-2xl overflow-hidden">
            @forelse($requests as $rr)
                @php
                    $name = trim(($rr->candidate->first_name ?? '').' '.($rr->candidate->last_name ?? '')) ?: 'Candidate';
                    $initial = strtoupper(substr($name, 0, 1));
                    $tenure = ($rr->joining_date && $rr->left_at) ? $rr->joining_date->diffInDays($rr->left_at) : null;
                    $guarantee = (int) ($rr->replacement_window_days ?? $rr->job->replacement_guarantee_days ?? 0);
                @endphp
                <div class="px-6 py-5 border-b border-white/10 last:border-b-0 flex flex-col lg:flex-row lg:items-center gap-4 hover:bg-white/5 transition">
                    <div class="flex items-center gap-4 flex-1 min-w-0">
                        <div class="h-12 w-12 rounded-full bg-gradient-to-r from-amber-400 to-rose-500 flex items-center justify-center text-white font-bold text-lg ring-2 ring-white/20 flex-shrink-0">
                            {{ $initial }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-white font-bold text-base flex flex-wrap items-center gap-2">
                                {{ $name }}
                                <span class="text-amber-300/80 text-sm font-medium">left</span>
                                <span class="text-cyan-200 font-bold">{{ $rr->job->title ?? '—' }}</span>
                            </div>
                            <div class="text-blue-200/80 text-xs mt-1">
                                <i class="fa-solid fa-building text-amber-400 mr-1"></i> {{ $rr->job->company_name ?? '—' }}
                                · Joined {{ optional($rr->joining_date)->format('M d, Y') ?: '—' }}
                                · Left {{ optional($rr->left_at)->format('M d, Y') ?: '—' }}
                                @if($tenure !== null && $guarantee)
                                    · Tenure {{ $tenure }} of {{ $guarantee }} days
                                @endif
                            </div>
                            @if($rr->replacement_reason)
                                <div class="mt-2 bg-amber-500/10 border border-amber-400/30 rounded-lg px-3 py-2 text-sm text-amber-100 italic">
                                    "{{ $rr->replacement_reason }}"
                                </div>
                            @endif
                            <div class="text-[10px] text-slate-400 mt-1.5 uppercase tracking-wider">
                                Requested {{ $rr->replacement_requested_at->diffForHumans() }} · {{ $rr->application_code ?? 'SH-APP-'.str_pad($rr->id, 6, '0', STR_PAD_LEFT) }}
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 lg:flex-shrink-0">
                        @if($rr->job)
                            <a href="{{ route('partner.jobs.show', $rr->job->id) }}"
                               class="inline-flex items-center justify-center gap-2 bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold px-4 py-2.5 rounded-lg whitespace-nowrap shadow-lg transition">
                                <i class="fa-solid fa-paper-plane"></i> Send Replacement
                            </a>
                        @endif
                        <a href="{{ route('partner.applications.show', $rr->id) }}"
                           class="inline-flex items-center justify-center gap-2 bg-white/10 hover:bg-white/20 text-white text-sm font-bold px-4 py-2.5 rounded-lg border border-white/20 whitespace-nowrap transition">
                            <i class="fa-regular fa-eye"></i> View
                        </a>
                    </div>
                </div>
            @empty
                <div class="px-6 py-20 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 mb-4">
                        <i class="fa-solid fa-check text-2xl"></i>
                    </div>
                    <p class="text-white font-bold text-lg">No replacement requests right now.</p>
                    <p class="text-blue-200 text-sm mt-1">Once a client asks for a replacement, it will show up here.</p>
                </div>
            @endforelse

            @if($requests->hasPages())
                <div class="px-6 py-4 border-t border-white/10 bg-slate-900/60">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
