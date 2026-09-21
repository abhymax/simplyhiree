<?php

namespace App\Services;

use App\Models\AssessmentSession;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class AssessmentSessionService
{
    /**
     * Ensure an assessment session exists for a vendor-submitted application
     * when the job has questionnaire stages, and email the candidate their
     * magic link. Returns the session, or null when the job has no stages.
     */
    public function startForApplication(JobApplication $application): ?AssessmentSession
    {
        $job = $application->job()->first();
        if (!$job || $job->assessmentStages()->count() === 0) {
            return null;
        }

        $candidate = $application->candidate()->first();
        $email = $candidate?->email;
        if (empty($email)) {
            // Email is mandatory for assessed jobs; nothing to send to.
            return null;
        }

        $existing = AssessmentSession::where('job_id', $job->id)
            ->where('candidate_id', $candidate->id)
            ->first();
        if ($existing) {
            // Keep the existing session/link; make sure it points at this application.
            if (empty($existing->job_application_id)) {
                $existing->update(['job_application_id' => $application->id]);
            }
            return $existing;
        }

        $partnerId = null;
        if ($application->submitted_by_user_id) {
            $submitter = \App\Models\User::find($application->submitted_by_user_id);
            $partnerId = $submitter ? (int) $submitter->partnerOwnerId() : null;
        }

        $session = AssessmentSession::create([
            'job_id'             => $job->id,
            'job_application_id'  => $application->id,
            'candidate_id'       => $candidate->id,
            'partner_id'         => $partnerId,
            'email'              => $email,
            'candidate_name'     => trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')),
            'status'             => 'pending',
        ]);

        // Gate this application until the candidate clears the assessment.
        $application->update(['assessment_status' => \App\Models\JobApplication::ASSESSMENT_PENDING]);

        $this->sendInvite($session);

        return $session;
    }

    /** Email the candidate the magic link to begin their assessment. */
    public function sendInvite(AssessmentSession $session): void
    {
        $url = URL::route('assessment.entry', ['token' => $session->token]);
        $job = $session->job()->first();

        try {
            Mail::send('emails.assessment-invite', [
                'session'  => $session,
                'job'      => $job,
                'url'      => $url,
            ], function ($mail) use ($session, $job) {
                $mail->to($session->email, $session->candidate_name ?: null)
                     ->subject('Complete your assessment for ' . ($job->title ?? 'your application'));
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Generate and email a fresh OTP for email verification. */
    public function sendOtp(AssessmentSession $session): void
    {
        $code = $session->generateOtp();
        $job = $session->job()->first();

        try {
            Mail::send('emails.assessment-otp', [
                'session' => $session,
                'job'     => $job,
                'code'    => $code,
                'ttl'     => AssessmentSession::OTP_TTL_MINUTES,
            ], function ($mail) use ($session, $code) {
                $mail->to($session->email, $session->candidate_name ?: null)
                     ->subject('Your verification code: ' . $code);
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
