@extends('assessment.layout')
@section('title', 'Verify your email')

@section('content')
    @if($session->job)
        <span class="job-chip">{{ $session->job->title }}</span>
    @endif
    <h1>Verify it's you</h1>
    <p class="lead">
        We've emailed a 6-digit verification code to
        <strong style="color:#e2e8f0;">{{ $session->maskedEmail() }}</strong>.
        Enter it below to start your assessment.
    </p>

    @if(session('otp_notice'))
        <div class="alert alert-info">{{ session('otp_notice') }}</div>
    @endif
    @error('otp')
        <div class="alert alert-error">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('assessment.verify', $session->token) }}" autocomplete="one-time-code">
        @csrf
        <label for="otp">Verification code</label>
        <input type="text" id="otp" name="otp" inputmode="numeric" pattern="\d{6}" maxlength="6"
               placeholder="------" autocomplete="one-time-code" autofocus required>
        <button type="submit" class="btn">Verify &amp; continue</button>
    </form>

    <form method="POST" action="{{ route('assessment.otp.send', $session->token) }}">
        @csrf
        <button type="submit" class="btn-link">Didn't get it? Resend code</button>
    </form>

    <p class="muted">The code expires in {{ \App\Models\AssessmentSession::OTP_TTL_MINUTES }} minutes. Check your spam folder if you don't see it.</p>
@endsection
