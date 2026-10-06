<?php

namespace App\Observers;

use App\Models\JobApplication;
use App\Services\SuperadminActivityService;

class JobApplicationObserver
{
    public function updated(JobApplication $application): void
    {
        $activity = app(SuperadminActivityService::class);

        if ($application->wasChanged('hiring_status')) {
            $status = (string) $application->hiring_status;

            $eventKey = match ($status) {
                'Shortlisted', 'shortlisted' => 'client.candidate_shortlisted',
                'Maybe' => 'client.candidate_maybe',
                'Interviewed' => 'client.interview_appeared',
                'No-Show' => 'client.interview_no_show',
                default => null,
            };

            if ($eventKey) {
                $activity->logApplicationLifecycle($application, $eventKey);
            }

            if ($status === 'Interview Scheduled') {
                $activity->logApplicationLifecycle($application, 'client.interview_scheduled');
                $activity->sendCandidateInterviewScheduledWhatsApp($application);
            }

            if ($status === 'Selected') {
                $activity->logApplicationLifecycle($application, 'client.candidate_selected');
                $activity->sendCandidateSelectedWhatsApp($application);
            }
        }

        if ($application->wasChanged('joined_status') && (string) $application->joined_status === 'Left') {
            $activity->logApplicationLifecycle($application, 'candidate.left_company');
        }

        if ($application->wasChanged('joined_status') && (string) $application->joined_status === 'Joined') {
            $activity->logApplicationLifecycle($application, 'candidate.joined_company');
        }
    }
}
