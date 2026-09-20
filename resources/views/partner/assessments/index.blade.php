@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-25"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-cyan-500 rounded-full mix-blend-screen blur-[120px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        @if(session('success'))
            <div class="mb-6 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md">
                <i class="fa-solid fa-circle-check mr-3 text-xl"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 px-6 py-4 bg-rose-500/20 border border-rose-500/50 text-rose-200 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md">
                <i class="fa-solid fa-triangle-exclamation mr-3 text-xl"></i>{{ session('error') }}
            </div>
        @endif

        <div class="mb-6">
            <h1 class="text-3xl font-extrabold tracking-tight">Assessment Tracking</h1>
            <p class="mt-1 text-blue-200/80 text-sm">Track every candidate you lined up through their questionnaire stages. Attribution and commission stay linked to your account.</p>
        </div>

        {{-- Stat filters --}}
        @php
            $chips = [
                'all' => ['All', 'fa-layer-group', 'text-white'],
                'pending' => ['Invited', 'fa-paper-plane', 'text-amber-300'],
                'in_progress' => ['In progress', 'fa-spinner', 'text-sky-300'],
                'passed' => ['Qualified', 'fa-circle-check', 'text-emerald-300'],
                'failed' => ['Not qualified', 'fa-circle-xmark', 'text-rose-300'],
            ];
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            @foreach($chips as $key => $c)
                <a href="{{ route('partner.assessments.index', ['status' => $key]) }}"
                   class="rounded-2xl border p-4 transition {{ $filter === $key ? 'border-white/40 bg-white/10' : 'border-white/10 bg-white/5 hover:bg-white/10' }}">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wide {{ $c[2] }}">
                        <i class="fa-solid {{ $c[1] }}"></i>{{ $c[0] }}
                    </div>
                    <div class="mt-1 text-2xl font-extrabold">{{ $counts[$key] }}</div>
                </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" class="mb-5 flex gap-3">
            <input type="hidden" name="status" value="{{ $filter }}">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search candidate, email or job…"
                   class="flex-1 rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-2.5 focus:ring-2 focus:ring-cyan-400">
            <button class="rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-5">Search</button>
        </form>

        <div class="rounded-2xl border border-white/10 bg-slate-900/40 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-white/5 text-blue-200/80 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="text-left font-bold px-5 py-3">Candidate</th>
                            <th class="text-left font-bold px-5 py-3">Job</th>
                            <th class="text-left font-bold px-5 py-3">Progress</th>
                            <th class="text-left font-bold px-5 py-3">Status</th>
                            <th class="text-left font-bold px-5 py-3">Updated</th>
                            <th class="text-right font-bold px-5 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($sessions as $s)
                            @php
                                $stages = optional($s->job)->assessmentStages ?? collect();
                                $byStage = $s->attempts->sortByDesc('attempt_number')->groupBy('stage_order');
                                $statusMap = [
                                    'pending' => ['Invited', 'bg-amber-500/15 text-amber-200 border-amber-400/30'],
                                    'verified' => ['Verified', 'bg-amber-500/15 text-amber-200 border-amber-400/30'],
                                    'in_progress' => ['In progress', 'bg-sky-500/15 text-sky-200 border-sky-400/30'],
                                    'passed' => ['Qualified', 'bg-emerald-500/15 text-emerald-200 border-emerald-400/30'],
                                    'failed' => ['Not qualified', 'bg-rose-500/15 text-rose-200 border-rose-400/30'],
                                    'expired' => ['Expired', 'bg-slate-500/15 text-slate-300 border-slate-400/30'],
                                ];
                                $sm = $statusMap[$s->status] ?? ['Unknown','bg-white/10 text-white border-white/20'];
                                $passedCount = $s->attempts->where('passed', true)->pluck('stage_order')->unique()->count();
                            @endphp
                            <tr class="hover:bg-white/5">
                                <td class="px-5 py-3">
                                    <div class="font-bold">{{ $s->candidate_name ?: ($s->candidate->first_name ?? 'Candidate') }}</div>
                                    <div class="text-blue-200/60 text-xs">{{ $s->maskedEmail() }}</div>
                                </td>
                                <td class="px-5 py-3">{{ optional($s->job)->title ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-1.5">
                                        @forelse($stages as $stage)
                                            @php
                                                $latest = optional($byStage->get($stage->stage_order))->first();
                                                $passed = $latest && $latest->passed;
                                                $failedFinal = $latest && !$latest->passed && $latest->submitted_at;
                                                $isCurrent = $stage->stage_order == $s->current_stage;
                                                $dot = $passed ? 'bg-emerald-400' : ($failedFinal ? 'bg-rose-400' : ($isCurrent ? 'bg-indigo-400' : 'bg-slate-600'));
                                                $title = 'Stage '.$stage->stage_order.($latest ? ' · '.rtrim(rtrim((string)$latest->percentage,'0'),'.').'%' : '');
                                            @endphp
                                            <span class="inline-block w-3 h-3 rounded-full {{ $dot }}" title="{{ $title }}"></span>
                                        @empty
                                            <span class="text-blue-200/50 text-xs">—</span>
                                        @endforelse
                                        @if($stages->isNotEmpty())
                                            <span class="ml-2 text-xs text-blue-200/70">{{ $passedCount }}/{{ $stages->count() }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="inline-block rounded-full border px-2.5 py-1 text-xs font-bold {{ $sm[1] }}">{{ $sm[0] }}</span>
                                </td>
                                <td class="px-5 py-3 text-blue-200/70 text-xs">{{ $s->updated_at?->diffForHumans() }}</td>
                                <td class="px-5 py-3 text-right">
                                    @if(!in_array($s->status, ['passed','failed']))
                                        <form method="POST" action="{{ route('partner.assessments.resend', $s) }}">
                                            @csrf
                                            <button class="rounded-lg border border-cyan-400/40 bg-cyan-500/15 text-cyan-200 text-xs font-bold px-3 py-1.5 hover:bg-cyan-500/25">
                                                <i class="fa-solid fa-paper-plane mr-1"></i>Resend link
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-blue-200/40 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-10 text-center text-blue-200/60">No assessment candidates yet. When you submit a candidate to a job that has a questionnaire, they'll appear here.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-5">{{ $sessions->links() }}</div>
    </div>
</div>
@endsection
