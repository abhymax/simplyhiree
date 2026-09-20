@extends('assessment.layout')
@section('title', 'Your assessment')

@section('content')
    @if($session->job)
        <span class="job-chip">{{ $session->job->title }}</span>
    @endif

    @if($session->status === 'passed')
        <h1>All stages cleared 🎉</h1>
        <p class="lead">Great work, {{ $session->candidate_name ?: 'there' }}! You've completed every stage. Your responses have been shared with the recruiter — no further action is needed.</p>
    @elseif($session->status === 'failed')
        <h1>Assessment complete</h1>
        <p class="lead">Thanks for taking the assessment, {{ $session->candidate_name ?: 'there' }}. Unfortunately you didn't meet the pass mark for this role. You can reach out to the recruiter for next steps.</p>
    @else
        <h1>Your assessment</h1>
        <p class="lead">Hi {{ $session->candidate_name ?: 'there' }}, complete the stages below in order. Clear each stage to unlock the next one.</p>
    @endif

    @if(session('assessment_notice'))
        <div class="alert alert-info">{{ session('assessment_notice') }}</div>
    @endif
    @if(session('assessment_error'))
        <div class="alert alert-error">{{ session('assessment_error') }}</div>
    @endif

    @php
        $activeStage = $session->current_stage;
        $firstOpenOrder = null;
    @endphp

    <div style="display:flex;flex-direction:column;gap:12px;margin-top:6px;">
        @forelse($stages as $stage)
            @php
                $latest = optional($attemptsByStage->get($stage->stage_order))->first();
                $isPassed = $latest && $latest->passed;
                $a = $stage->assessment;
                $maxAttempts = max(1, (int) optional($a)->max_attempts);
                $used = ($attemptsByStage->get($stage->stage_order) ?? collect())->whereNotNull('submitted_at')->count();
                $attemptsLeft = max(0, $maxAttempts - $used);
                $isLocked = $stage->stage_order > $activeStage && !$isPassed;
                $isCurrent = !$isPassed && !$isLocked && $session->status !== 'failed';
                if ($isCurrent && $firstOpenOrder === null) { $firstOpenOrder = $stage->stage_order; }
            @endphp
            <div style="border:1px solid rgba(148,163,184,.2);background:rgba(2,6,23,.45);border-radius:14px;padding:14px 16px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="flex:0 0 auto;width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:800;
                        background:{{ $isPassed ? 'rgba(16,185,129,.2)' : ($isLocked ? 'rgba(100,116,139,.2)' : 'rgba(99,102,241,.25)') }};
                        color:{{ $isPassed ? '#6ee7b7' : ($isLocked ? '#94a3b8' : '#c7d2fe') }};">{{ $stage->stage_order }}</span>
                    <div style="flex:1;min-width:0;">
                        <div style="color:#fff;font-weight:700;font-size:.95rem;">{{ $a->name ?? 'Questionnaire' }}</div>
                        <div style="color:#94a3b8;font-size:.78rem;margin-top:2px;">
                            @if($a)
                                {{ $a->questions()->count() }} questions · pass {{ $a->passing_percentage }}%
                                @if($a->time_limit_minutes) · {{ $a->time_limit_minutes }} min @endif
                                @if(!$isPassed && !$isLocked) · {{ $attemptsLeft }} attempt{{ $attemptsLeft == 1 ? '' : 's' }} left @endif
                            @endif
                        </div>
                    </div>
                    <div style="flex:0 0 auto;text-align:right;">
                        @if($isPassed)
                            <span style="color:#6ee7b7;font-weight:700;font-size:.82rem;">Passed · {{ rtrim(rtrim((string)$latest->percentage,'0'),'.') }}%</span>
                        @elseif($isLocked)
                            <span style="color:#94a3b8;font-size:.82rem;">Locked</span>
                        @elseif($latest && !$latest->passed)
                            <span style="color:#fca5a5;font-weight:700;font-size:.82rem;">{{ rtrim(rtrim((string)$latest->percentage,'0'),'.') }}%</span>
                        @else
                            <span style="color:#a5b4fc;font-weight:700;font-size:.82rem;">Ready</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info">No assessment stages are configured for this role.</div>
        @endforelse
    </div>

    @php
        $current = $stages->firstWhere('stage_order', $firstOpenOrder);
        $currentLatest = $current ? optional($attemptsByStage->get($current->stage_order))->first() : null;
        if ($current) {
            if ($currentLatest && $currentLatest->submitted_at === null) {
                $ctaLabel = 'Continue stage ' . $current->stage_order;
            } elseif ($currentLatest) {
                $ctaLabel = 'Retake stage ' . $current->stage_order;
            } else {
                $ctaLabel = 'Start stage ' . $current->stage_order;
            }
        }
    @endphp

    @if($current && $session->status !== 'failed' && $session->status !== 'passed')
        <a href="{{ route('assessment.stage.start', $session->token) }}" class="btn" style="text-decoration:none;text-align:center;">
            {{ $ctaLabel }}
        </a>
    @endif
@endsection
