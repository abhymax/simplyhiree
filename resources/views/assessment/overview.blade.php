@extends('assessment.layout')
@section('title', 'Your assessment')

@section('content')
    @if($session->job)
        <span class="job-chip">{{ $session->job->title }}</span>
    @endif
    <h1>You're verified ✓</h1>
    <p class="lead">
        Hi {{ $session->candidate_name ?: 'there' }}, complete the stages below in order. You must clear each
        stage to unlock the next one and to be considered for this role.
    </p>

    @php
        $startRouteExists = \Illuminate\Support\Facades\Route::has('assessment.stage.start');
        $activeStage = $session->current_stage;
    @endphp

    <div style="display:flex;flex-direction:column;gap:12px;margin-top:6px;">
        @forelse($stages as $stage)
            @php
                $latest = optional($attemptsByStage->get($stage->stage_order))->first();
                $isPassed = $latest && $latest->passed;
                $isLocked = $stage->stage_order > $activeStage && !$isPassed;
                $a = $stage->assessment;
            @endphp
            <div style="border:1px solid rgba(148,163,184,.2);background:rgba(2,6,23,.45);border-radius:14px;padding:14px 16px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="flex:0 0 auto;width:30px;height:30px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-weight:800;
                        background:{{ $isPassed ? 'rgba(16,185,129,.2)' : ($isLocked ? 'rgba(100,116,139,.2)' : 'rgba(99,102,241,.25)') }};
                        color:{{ $isPassed ? '#6ee7b7' : ($isLocked ? '#94a3b8' : '#c7d2fe') }};">
                        {{ $stage->stage_order }}
                    </span>
                    <div style="flex:1;min-width:0;">
                        <div style="color:#fff;font-weight:700;font-size:.95rem;">{{ $a->name ?? 'Questionnaire' }}</div>
                        <div style="color:#94a3b8;font-size:.78rem;margin-top:2px;">
                            @if($a)
                                {{ $a->questions()->count() }} questions · pass {{ $a->passing_percentage }}%
                                @if($a->time_limit_minutes) · {{ $a->time_limit_minutes }} min @endif
                            @endif
                        </div>
                    </div>
                    <div style="flex:0 0 auto;text-align:right;">
                        @if($isPassed)
                            <span style="color:#6ee7b7;font-weight:700;font-size:.82rem;">Passed</span>
                        @elseif($isLocked)
                            <span style="color:#94a3b8;font-size:.82rem;">Locked</span>
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

    @if($stages->isNotEmpty())
        @if($startRouteExists)
            <a href="{{ route('assessment.stage.start', $session->token) }}" class="btn" style="text-decoration:none;text-align:center;">
                Start assessment
            </a>
        @else
            <p class="muted">Your assessment will open here shortly. Please keep this link — you can return anytime before it expires.</p>
        @endif
    @endif
@endsection
