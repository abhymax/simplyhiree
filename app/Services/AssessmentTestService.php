<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentAttemptAnswer;
use App\Models\AssessmentSession;
use App\Models\JobAssessmentStage;
use Illuminate\Support\Facades\DB;

class AssessmentTestService
{
    /** Ordered stages for the session's job. */
    public function stages(AssessmentSession $session)
    {
        return $session->job
            ? $session->job->assessmentStages()->with('assessment')->orderBy('stage_order')->get()
            : collect();
    }

    /** The stage the candidate should currently be on (first not-passed), or null if all cleared. */
    public function currentStage(AssessmentSession $session): ?JobAssessmentStage
    {
        foreach ($this->stages($session) as $stage) {
            if (!$this->hasPassed($session, $stage->stage_order)) {
                return $stage;
            }
        }
        return null;
    }

    public function hasPassed(AssessmentSession $session, int $stageOrder): bool
    {
        return $session->attempts()
            ->where('stage_order', $stageOrder)
            ->where('passed', true)
            ->exists();
    }

    /** Finalized (submitted) attempts for a stage. */
    public function attemptsUsed(AssessmentSession $session, int $stageOrder): int
    {
        return $session->attempts()
            ->where('stage_order', $stageOrder)
            ->whereNotNull('submitted_at')
            ->count();
    }

    public function latestAttempt(AssessmentSession $session, int $stageOrder): ?AssessmentAttempt
    {
        return $session->attempts()
            ->where('stage_order', $stageOrder)
            ->orderByDesc('attempt_number')
            ->first();
    }

    /**
     * Begin or resume the attempt for a stage.
     * Returns ['status' => 'ok'|'passed'|'exhausted', 'attempt' => ?AssessmentAttempt].
     */
    public function startOrResume(AssessmentSession $session, JobAssessmentStage $stage): array
    {
        $assessment = $stage->assessment;
        if (!$assessment) {
            return ['status' => 'exhausted', 'attempt' => null];
        }

        if ($this->hasPassed($session, $stage->stage_order)) {
            return ['status' => 'passed', 'attempt' => null];
        }

        // Resume a live in-progress attempt.
        $open = $session->attempts()
            ->where('stage_order', $stage->stage_order)
            ->whereNull('submitted_at')
            ->orderByDesc('attempt_number')
            ->first();

        if ($open) {
            if ($open->isExpired()) {
                $this->finalize($open, true);
            } else {
                return ['status' => 'ok', 'attempt' => $open];
            }
        }

        // Start a fresh attempt if attempts remain.
        $used = $this->attemptsUsed($session, $stage->stage_order);
        $max = max(1, (int) $assessment->max_attempts);
        if ($used >= $max) {
            return ['status' => 'exhausted', 'attempt' => null];
        }

        // Enforce the per-stage start window: the candidate must begin this
        // stage within N hours of clearing the previous one (first start only).
        if ($used === 0 && $this->windowMissed($session, $stage)) {
            $session->update(['status' => 'failed']);
            $this->syncApplication($session, 'failed');
            return ['status' => 'window_missed', 'attempt' => null];
        }

        $questionIds = $assessment->questions()->pluck('id')->all();
        if ($assessment->shuffle_questions) {
            shuffle($questionIds);
        }

        $attempt = $session->attempts()->create([
            'assessment_id'  => $assessment->id,
            'stage_order'    => $stage->stage_order,
            'attempt_number' => $used + 1,
            'started_at'     => now(),
            'expires_at'     => $assessment->time_limit_minutes
                ? now()->addMinutes((int) $assessment->time_limit_minutes)
                : null,
            'total_marks'    => $assessment->totalMarks(),
            'question_order' => $questionIds,
            'status'         => 'in_progress',
        ]);

        if ($session->status === 'pending' || $session->status === 'verified') {
            $session->update(['status' => 'in_progress']);
        }

        return ['status' => 'ok', 'attempt' => $attempt];
    }

    /** Persist a single answer during the attempt. Returns true if accepted. */
    public function saveAnswer(AssessmentAttempt $attempt, int $questionId, ?int $optionId): bool
    {
        if ($attempt->submitted_at !== null || $attempt->isExpired()) {
            return false;
        }

        $question = $attempt->assessment->questions()->with('options')->find($questionId);
        if (!$question) {
            return false;
        }

        $isCorrect = false;
        $validOptionId = null;
        if ($optionId !== null) {
            $option = $question->options->firstWhere('id', $optionId);
            if (!$option) {
                return false; // option must belong to this question
            }
            $validOptionId = $option->id;
            $isCorrect = (bool) $option->is_correct;
        }

        AssessmentAttemptAnswer::updateOrCreate(
            ['assessment_attempt_id' => $attempt->id, 'assessment_question_id' => $questionId],
            ['assessment_question_option_id' => $validOptionId, 'is_correct' => $isCorrect]
        );

        return true;
    }

    /**
     * Score and close an attempt, then advance the session.
     * $timedOut only affects nothing scoring-wise (saved answers are graded).
     */
    public function finalize(AssessmentAttempt $attempt, bool $timedOut = false): AssessmentAttempt
    {
        if ($attempt->submitted_at !== null) {
            return $attempt; // already finalized
        }

        return DB::transaction(function () use ($attempt, $timedOut) {
            $assessment = $attempt->assessment;
            $total = (int) $assessment->totalMarks();

            // Score = sum of marks for questions whose saved answer is correct.
            $correctQuestionIds = $attempt->answers()
                ->where('is_correct', true)
                ->pluck('assessment_question_id')
                ->all();

            $score = 0;
            if (!empty($correctQuestionIds)) {
                $score = (int) $assessment->questions()
                    ->whereIn('id', $correctQuestionIds)
                    ->sum('marks');
            }

            $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0;
            $passed = $percentage >= (int) $assessment->passing_percentage;

            $attempt->update([
                'submitted_at' => now(),
                'score'        => $score,
                'total_marks'  => $total,
                'percentage'   => $percentage,
                'passed'       => $passed,
                'status'       => $timedOut ? 'expired' : 'submitted',
            ]);

            $this->advanceSession($attempt->session, $attempt);

            return $attempt->refresh();
        });
    }

    private function advanceSession(AssessmentSession $session, AssessmentAttempt $attempt): void
    {
        $stages = $this->stages($session);
        $lastOrder = (int) ($stages->max('stage_order') ?? $attempt->stage_order);

        if ($attempt->passed) {
            if ($attempt->stage_order >= $lastOrder) {
                $session->update(['status' => 'passed', 'current_stage' => $lastOrder]);
                $this->syncApplication($session, 'passed');
            } else {
                $session->update([
                    'status'        => 'in_progress',
                    'current_stage' => $attempt->stage_order + 1,
                ]);
            }
            return;
        }

        // Failed: out of attempts on this stage means the whole journey fails.
        $used = $this->attemptsUsed($session, $attempt->stage_order);
        $max = max(1, (int) $attempt->assessment->max_attempts);
        if ($used >= $max) {
            $session->update(['status' => 'failed']);
            $this->syncApplication($session, 'failed');
        }
    }

    /** Reflect a finished assessment session onto its job application (the gate). */
    private function syncApplication(AssessmentSession $session, string $outcome): void
    {
        if (empty($session->job_application_id)) {
            return;
        }
        $application = $session->application()->first();
        if (!$application) {
            return;
        }

        if ($outcome === 'passed') {
            $application->update([
                'assessment_status'       => \App\Models\JobApplication::ASSESSMENT_QUALIFIED,
                'assessment_qualified_at' => now(),
            ]);
        } else {
            $application->update([
                'assessment_status' => \App\Models\JobApplication::ASSESSMENT_NOT_QUALIFIED,
            ]);
        }
    }

    /**
     * Has the candidate blown the start window for this stage? Defined by the
     * PREVIOUS stage's next_stage_start_hours, measured from when they cleared it.
     */
    public function windowMissed(AssessmentSession $session, JobAssessmentStage $stage): bool
    {
        if ($stage->stage_order <= 1) {
            return false;
        }

        $prev = $this->stages($session)->firstWhere('stage_order', $stage->stage_order - 1);
        $hours = $prev ? $prev->next_stage_start_hours : null;
        if ($prev === null || $hours === null) {
            return false; // no deadline configured
        }

        $prevPass = $session->attempts()
            ->where('stage_order', $prev->stage_order)
            ->where('passed', true)
            ->whereNotNull('submitted_at')
            ->orderByDesc('submitted_at')
            ->first();
        if (!$prevPass || !$prevPass->submitted_at) {
            return false;
        }

        return now()->greaterThan($prevPass->submitted_at->copy()->addHours((int) $hours));
    }

    public function attemptsRemaining(AssessmentSession $session, JobAssessmentStage $stage): int
    {
        $max = max(1, (int) optional($stage->assessment)->max_attempts);
        return max(0, $max - $this->attemptsUsed($session, $stage->stage_order));
    }
}
