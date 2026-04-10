<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification implements ShouldQueue
{
    use Queueable;

    public JobApplication $application;

    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $clientName    = $this->application->job?->user?->name ?? 'Unknown Client';
        $jobTitle      = $this->application->job?->title ?? 'Unknown Job';
        $candidateName = $this->application->candidate
                         ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
                         : ($this->application->candidateUser?->name ?? 'Unknown Candidate');

        return [
            'message'        => "{$clientName} scheduled an interview for {$candidateName} (Job: {$jobTitle}).",
            'application_id' => $this->application->id,
            'icon'           => 'calendar-event',
        ];
    }
}
