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

            if ($status === 'Interviewed') {
                $activity->logApplicationLifecycle($application, 'client.candidate_approved');
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
