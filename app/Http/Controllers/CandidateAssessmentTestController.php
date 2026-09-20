<?php

namespace App\Http\Controllers;

use App\Models\AssessmentSession;
use App\Services\AssessmentTestService;
use Illuminate\Http\Request;

class CandidateAssessmentTestController extends Controller
{
    public function __construct(private AssessmentTestService $tests)
    {
    }

    private function resolve(string $token): AssessmentSession
    {
        return AssessmentSession::where('token', $token)->firstOrFail();
    }

    /** Guard: link valid + email verified. Returns a redirect response, or null to proceed. */
    private function guard(AssessmentSession $session, string $token)
    {
        if ($session->isLinkExpired()) {
            return response()->view('assessment.expired', compact('session'), 410);
        }
        if (!$session->isVerified()) {
            return redirect()->route('assessment.entry', $token);
        }
        return null;
    }

    /** Entry from the overview "Start" button — begins/resumes the current stage. */
    public function start(string $token)
    {
        $session = $this->resolve($token);
        if ($r = $this->guard($session, $token)) return $r;

        $stage = $this->tests->currentStage($session);
        if (!$stage) {
            return redirect()->route('assessment.overview', $token);
        }

        $res = $this->tests->startOrResume($session, $stage);
        if ($res['status'] === 'exhausted') {
            return redirect()->route('assessment.overview', $token)
                ->with('assessment_error', 'You have used all attempts for this stage.');
        }
        if ($res['status'] === 'passed') {
            return redirect()->route('assessment.overview', $token);
        }

        return redirect()->route('assessment.stage.take', $token);
    }

    /** The timed MCQ runner for the current stage. */
    public function take(string $token)
    {
        $session = $this->resolve($token);
        if ($r = $this->guard($session, $token)) return $r;

        $stage = $this->tests->currentStage($session);
        if (!$stage) {
            return redirect()->route('assessment.overview', $token);
        }

        $attempt = $session->attempts()
            ->where('stage_order', $stage->stage_order)
            ->whereNull('submitted_at')
            ->orderByDesc('attempt_number')
            ->first();

        if (!$attempt) {
            return redirect()->route('assessment.stage.start', $token);
        }
        if ($attempt->isExpired()) {
            $this->tests->finalize($attempt, true);
            return redirect()->route('assessment.overview', $token)
                ->with('assessment_notice', 'Time expired — your answers were submitted automatically.');
        }

        $assessment = $stage->assessment;
        $order = $attempt->question_order ?: $assessment->questions()->pluck('id')->all();
        $questions = $assessment->questions()->with('options')->get()->keyBy('id');
        $ordered = collect($order)->map(fn ($id) => $questions->get($id))->filter()->values();

        $answers = $attempt->answers()->pluck('assessment_question_option_id', 'assessment_question_id');
        $remaining = $attempt->expires_at ? max(0, now()->diffInSeconds($attempt->expires_at, false)) : null;

        return view('assessment.take', [
            'session'    => $session,
            'stage'      => $stage,
            'assessment' => $assessment,
            'attempt'    => $attempt,
            'questions'  => $ordered,
            'answers'    => $answers,
            'remaining'  => $remaining,
            'totalStages' => $this->tests->stages($session)->count(),
        ]);
    }

    private function currentAttempt(AssessmentSession $session)
    {
        $stage = $this->tests->currentStage($session);
        if (!$stage) return [null, null];
        $attempt = $session->attempts()
            ->where('stage_order', $stage->stage_order)
            ->whereNull('submitted_at')
            ->orderByDesc('attempt_number')
            ->first();
        return [$stage, $attempt];
    }

    /** Autosave a single answer (AJAX). */
    public function answer(Request $request, string $token)
    {
        $session = $this->resolve($token);
        if ($session->isLinkExpired() || !$session->isVerified()) {
            return response()->json(['ok' => false, 'redirect' => route('assessment.entry', $token)], 409);
        }

        $data = $request->validate([
            'question_id' => 'required|integer',
            'option_id'   => 'nullable|integer',
        ]);

        [, $attempt] = $this->currentAttempt($session);
        if (!$attempt) {
            return response()->json(['ok' => false, 'redirect' => route('assessment.overview', $token)], 409);
        }
        if ($attempt->isExpired()) {
            $this->tests->finalize($attempt, true);
            return response()->json(['ok' => false, 'expired' => true, 'redirect' => route('assessment.overview', $token)], 409);
        }

        $ok = $this->tests->saveAnswer($attempt, (int) $data['question_id'], $data['option_id'] ?? null);

        return response()->json(['ok' => $ok]);
    }

    /** Record a focus-loss event (basic anti-cheat). */
    public function focusLost(string $token)
    {
        $session = $this->resolve($token);
        [, $attempt] = $this->currentAttempt($session);
        if ($attempt && !$attempt->isExpired()) {
            $attempt->increment('focus_lost_count');
            return response()->json(['ok' => true, 'count' => $attempt->focus_lost_count]);
        }
        return response()->json(['ok' => false]);
    }

    /** Submit the attempt (manual or auto on timeout). */
    public function submit(Request $request, string $token)
    {
        $session = $this->resolve($token);
        if ($r = $this->guard($session, $token)) return $r;

        [, $attempt] = $this->currentAttempt($session);
        if (!$attempt) {
            return redirect()->route('assessment.overview', $token);
        }

        // Persist any answers posted with the final submit.
        $answers = $request->input('answers', []);
        if (is_array($answers) && !$attempt->isExpired()) {
            foreach ($answers as $questionId => $optionId) {
                $this->tests->saveAnswer($attempt, (int) $questionId, $optionId !== null && $optionId !== '' ? (int) $optionId : null);
            }
        }

        $attempt = $this->tests->finalize($attempt, $attempt->isExpired());

        $msg = $attempt->passed
            ? 'Stage cleared! Score: ' . $attempt->percentage . '%.'
            : 'You scored ' . $attempt->percentage . '%, below the ' . $attempt->assessment->passing_percentage . '% pass mark.';

        return redirect()->route('assessment.overview', $token)->with('assessment_notice', $msg);
    }
}
