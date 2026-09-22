@php
    $statusMap = [
        'pending' => ['Invited', 'bg-amber-500/15 text-amber-200 border-amber-400/30'],
        'verified' => ['Verified', 'bg-amber-500/15 text-amber-200 border-amber-400/30'],
        'in_progress' => ['In progress', 'bg-sky-500/15 text-sky-200 border-sky-400/30'],
        'passed' => ['Qualified', 'bg-emerald-500/15 text-emerald-200 border-emerald-400/30'],
        'failed' => ['Not qualified', 'bg-rose-500/15 text-rose-200 border-rose-400/30'],
        'expired' => ['Expired', 'bg-slate-500/15 text-slate-300 border-slate-400/30'],
    ];
    $sm = $statusMap[$session->status] ?? ['Unknown','bg-white/10 text-white border-white/20'];
    $attemptsByStage = $session->attempts->groupBy('stage_order');
@endphp

<a href="{{ route($routes['index']) }}" class="text-cyan-300 hover:text-white text-sm font-bold"><i class="fa-solid fa-arrow-left mr-1"></i> Back to results</a>

<div class="mt-4 rounded-2xl border border-white/10 bg-slate-900/40 p-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-white">{{ $session->candidate_name ?: (optional($session->candidate)->first_name ?? 'Candidate') }}</h2>
            <div class="text-blue-200/70 text-sm mt-1">{{ $session->email }}</div>
            <div class="text-blue-200/70 text-sm">Role: <span class="text-white font-semibold">{{ optional($session->job)->title ?? '—' }}</span></div>
            @if($isAdmin)<div class="text-blue-200/70 text-sm">Partner: <span class="text-white font-semibold">{{ optional($session->partner)->name ?? '—' }}</span></div>@endif
        </div>
        <span class="inline-block rounded-full border px-3 py-1.5 text-sm font-bold {{ $sm[1] }}">{{ $sm[0] }}</span>
    </div>
</div>

@foreach(($session->job->assessmentStages ?? collect()) as $stage)
    @php
        $attempts = $attemptsByStage->get($stage->stage_order) ?? collect();
        $best = $attempts->sortByDesc('percentage')->first();
        $a = $stage->assessment;
    @endphp
    <div class="mt-5 rounded-2xl border border-white/10 bg-slate-900/40 overflow-hidden">
        <div class="px-6 py-4 bg-white/5 flex items-center justify-between">
            <div>
                <div class="text-white font-bold">Stage {{ $stage->stage_order }}: {{ $a->name ?? 'Questionnaire' }}</div>
                <div class="text-blue-200/60 text-xs mt-0.5">Pass {{ optional($a)->passing_percentage }}%@if(optional($a)->time_limit_minutes) · {{ $a->time_limit_minutes }} min @endif · {{ $attempts->count() }} attempt(s)</div>
            </div>
            @if($best)
                <span class="text-sm font-extrabold {{ $best->passed ? 'text-emerald-300' : 'text-rose-300' }}">
                    Best {{ rtrim(rtrim((string)$best->percentage,'0'),'.') }}%
                </span>
            @else
                <span class="text-sm text-blue-200/60">Not attempted</span>
            @endif
        </div>

        @forelse($attempts as $att)
            <div class="px-6 py-4 border-t border-white/5">
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="font-bold text-white">Attempt {{ $att->attempt_number }}</span>
                    <span class="{{ $att->passed ? 'text-emerald-300' : 'text-rose-300' }} font-bold">{{ rtrim(rtrim((string)$att->percentage,'0'),'.') }}% ({{ $att->score }}/{{ $att->total_marks }})</span>
                    <span class="text-blue-200/60">{{ $att->submitted_at ? $att->submitted_at->format('d M Y, h:i A') : 'in progress' }}</span>
                    @if($att->focus_lost_count > 0)
                        <span class="text-amber-300 text-xs"><i class="fa-solid fa-eye-slash mr-1"></i>{{ $att->focus_lost_count }} focus loss</span>
                    @endif
                </div>
                @php $isWeighted = optional($att->assessment)->scoring_type === 'weighted'; @endphp

                {{-- Weighted: competency breakdown --}}
                @if($isWeighted && !empty($att->category_scores))
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($att->category_scores as $c)
                            <div class="rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-white/90 font-semibold">{{ $c['category'] }}</span>
                                    <span class="text-violet-200 font-bold">{{ $c['score'] }}/{{ $c['max'] }} · {{ rtrim(rtrim((string)$c['percentage'],'0'),'.') }}%</span>
                                </div>
                                <div class="mt-1.5 h-1.5 rounded-full bg-white/10 overflow-hidden">
                                    <div class="h-full bg-violet-400" style="width: {{ min(100,(float)$c['percentage']) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @if($att->answers->isNotEmpty())
                    <div class="mt-3 space-y-2">
                        @foreach($att->answers as $ans)
                            @if($isWeighted)
                                <div class="text-xs rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2">
                                    <div class="text-white/90 font-semibold">{{ optional($ans->question)->question_text }}
                                        @if(optional($ans->question)->category)<span class="text-violet-300/70 font-normal">· {{ $ans->question->category }}</span>@endif
                                    </div>
                                    <div class="mt-1 text-slate-200">
                                        Answered: <span class="text-white">{{ optional($ans->option)->option_text ?? '—' }}</span>
                                        <span class="text-violet-300 font-bold ml-1">(+{{ (int) optional($ans->option)->weight }})</span>
                                    </div>
                                </div>
                            @else
                                @php $correct = optional($ans->question)->options->firstWhere('is_correct', true); @endphp
                                <div class="text-xs rounded-lg border border-white/10 bg-slate-950/40 px-3 py-2">
                                    <div class="text-white/90 font-semibold">{{ optional($ans->question)->question_text }}</div>
                                    <div class="mt-1 {{ $ans->is_correct ? 'text-emerald-300' : 'text-rose-300' }}">
                                        <i class="fa-solid {{ $ans->is_correct ? 'fa-check' : 'fa-xmark' }} mr-1"></i>
                                        Chosen: {{ optional($ans->option)->option_text ?? '—' }}
                                    </div>
                                    @unless($ans->is_correct)
                                        <div class="text-blue-200/70 mt-0.5">Correct: {{ optional($correct)->option_text ?? '—' }}</div>
                                    @endunless
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <div class="px-6 py-4 border-t border-white/5 text-blue-200/60 text-sm">No attempts recorded for this stage.</div>
        @endforelse
    </div>
@endforeach
