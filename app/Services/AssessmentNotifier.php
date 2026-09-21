<?php

namespace App\Services;

use App\Models\AssessmentSession;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

/**
 * Candidate-facing assessment notifications over email + WhatsApp.
 * (The OTP itself stays email-only; these are result/unlock/reminder nudges.)
 * All sends are best-effort and never throw into the caller.
 */
class AssessmentNotifier
{
    public function __construct(private AiSensyWhatsAppService $whatsapp)
    {
    }

    private function link(AssessmentSession $session): string
    {
        return URL::route('assessment.entry', ['token' => $session->token]);
    }

    private function candidatePhone(AssessmentSession $session): ?string
    {
        $raw = optional($session->candidate)->phone_number;
        return $raw ? $this->whatsapp->normalizeIndianPhone($raw) : null;
    }

    private function email(AssessmentSession $session, string $view, string $subject, array $data): void
    {
        try {
            Mail::send($view, $data, function ($mail) use ($session, $subject) {
                $mail->to($session->email, $session->candidate_name ?: null)->subject($subject);
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function wa(AssessmentSession $session, string $eventKey, string $title, string $message, array $params): void
    {
        try {
            $phone = $this->candidatePhone($session);
            if (!$phone) {
                return;
            }
            $this->whatsapp->sendEventAlert($phone, $eventKey, $title, $message, [
                'user_name'       => $session->candidate_name ?: 'Candidate',
                'template_params' => $params,
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Final outcome: qualified or not-qualified. */
    public function result(AssessmentSession $session, bool $passed): void
    {
        if ($session->result_notified_at !== null) {
            return; // already told them
        }

        $job = $session->job()->first();
        $jobTitle = $job->title ?? 'the role';
        $name = $session->candidate_name ?: 'Candidate';

        if ($passed) {
            $this->email($session, 'emails.assessment-result', 'You qualified — ' . $jobTitle, [
                'session' => $session, 'job' => $job, 'passed' => true, 'url' => $this->link($session),
            ]);
            $this->wa($session, 'assessment.result_qualified', 'Assessment cleared',
                "Congratulations {$name}! You've cleared the assessment for {$jobTitle}.",
                [$name, $jobTitle]);
        } else {
            $this->email($session, 'emails.assessment-result', 'Assessment update — ' . $jobTitle, [
                'session' => $session, 'job' => $job, 'passed' => false, 'url' => $this->link($session),
            ]);
            $this->wa($session, 'assessment.result_not_qualified', 'Assessment update',
                "Hi {$name}, thank you for completing the assessment for {$jobTitle}.",
                [$name, $jobTitle]);
        }

        $session->forceFill(['result_notified_at' => now()])->save();
    }

    /** A new stage has unlocked for the candidate. */
    public function stageUnlocked(AssessmentSession $session, int $stageOrder): void
    {
        if ((int) $session->unlock_notified_stage >= $stageOrder) {
            return; // already nudged for this stage
        }

        $job = $session->job()->first();
        $jobTitle = $job->title ?? 'the role';
        $name = $session->candidate_name ?: 'Candidate';
        $url = $this->link($session);

        $this->email($session, 'emails.assessment-stage-unlocked', 'Next stage unlocked — ' . $jobTitle, [
            'session' => $session, 'job' => $job, 'stageOrder' => $stageOrder, 'url' => $url,
        ]);
        $this->wa($session, 'assessment.stage_unlocked', 'Next stage unlocked',
            "Great work {$name}! Stage {$stageOrder} for {$jobTitle} is now unlocked. Continue: {$url}",
            [$name, (string) $stageOrder, $jobTitle, $url]);

        $session->forceFill(['unlock_notified_stage' => $stageOrder])->save();
    }

    /** Nudge a candidate who hasn't finished yet. */
    public function reminder(AssessmentSession $session): void
    {
        $job = $session->job()->first();
        $jobTitle = $job->title ?? 'the role';
        $name = $session->candidate_name ?: 'Candidate';
        $url = $this->link($session);

        $this->email($session, 'emails.assessment-reminder', 'Reminder: finish your assessment — ' . $jobTitle, [
            'session' => $session, 'job' => $job, 'url' => $url,
        ]);
        $this->wa($session, 'assessment.reminder', 'Assessment reminder',
            "Hi {$name}, a quick reminder to complete your assessment for {$jobTitle}: {$url}",
            [$name, $jobTitle, $url]);

        $session->forceFill([
            'last_reminded_at' => now(),
            'reminder_count'   => (int) $session->reminder_count + 1,
        ])->save();
    }
}
