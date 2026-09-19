<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSession;
use App\Services\AssessmentSessionService;
use Illuminate\Http\Request;

class CandidateAssessmentController extends Controller
{
    public function __construct(private AssessmentSessionService $sessions)
    {
    }

    private function resolve(string $token): AssessmentSession
    {
        return AssessmentSession::where('token', $token)->firstOrFail();
    }

    /** Landing page for the magic link — sends an OTP and shows the verify form. */
    public function entry(string $token)
    {
        $session = $this->resolve($token);

        if ($session->isLinkExpired()) {
            return response()->view('assessment.expired', compact('session'), 410);
        }

        if ($session->isVerified()) {
            return redirect()->route('assessment.overview', $token);
        }

        // Auto-issue a code on arrival if there isn't a live one already.
        $needsCode = $session->otp_hash === null
            || $session->otp_expires_at === null
            || $session->otp_expires_at->isPast();
        if ($needsCode && $session->canResendOtp()) {
            $this->sessions->sendOtp($session);
        }

        return view('assessment.entry', compact('session'));
    }

    /** Resend the OTP (throttled at the route + a per-session cooldown). */
    public function sendOtp(string $token)
    {
        $session = $this->resolve($token);

        if ($session->isLinkExpired() || $session->isVerified()) {
            return redirect()->route('assessment.entry', $token);
        }

        if (!$session->canResendOtp()) {
            return back()->with('otp_notice', 'Please wait a moment before requesting another code.');
        }

        $this->sessions->sendOtp($session);

        return back()->with('otp_notice', 'A new code has been sent to ' . $session->maskedEmail() . '.');
    }

    /** Verify the submitted OTP. */
    public function verifyOtp(Request $request, string $token)
    {
        $session = $this->resolve($token);

        if ($session->isLinkExpired()) {
            return response()->view('assessment.expired', compact('session'), 410);
        }
        if ($session->isVerified()) {
            return redirect()->route('assessment.overview', $token);
        }

        $data = $request->validate([
            'otp' => 'required|string|regex:/^\d{6}$/',
        ], [
            'otp.regex' => 'Enter the 6-digit code from your email.',
        ]);

        $result = $session->verifyOtp($data['otp']);

        return match ($result) {
            'ok'      => redirect()->route('assessment.overview', $token),
            'expired' => back()->withErrors(['otp' => 'That code has expired. We can send you a new one.']),
            'locked'  => back()->withErrors(['otp' => 'Too many incorrect attempts. Please request a new code.']),
            default   => back()->withErrors(['otp' => 'Incorrect code. Please try again.']),
        };
    }

    /** Verified overview of the candidate's assessment stages. */
    public function overview(string $token)
    {
        $session = $this->resolve($token);

        if ($session->isLinkExpired()) {
            return response()->view('assessment.expired', compact('session'), 410);
        }
        if (!$session->isVerified()) {
            return redirect()->route('assessment.entry', $token);
        }

        $job = $session->job()->with('assessmentStages.assessment')->first();
        $stages = $job ? $job->assessmentStages : collect();

        // Latest attempt per stage for status display (take-test flow arrives in M5).
        $attemptsByStage = $session->attempts()
            ->orderByDesc('attempt_number')
            ->get()
            ->groupBy('stage_order');

        return view('assessment.overview', compact('session', 'job', 'stages', 'attemptsByStage'));
    }
}
