<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CandidateSelected extends Notification implements ShouldQueue
{
    use Queueable;

    public JobApplication $application;
    public bool $isUpdate;

    public function __construct(JobApplication $application, bool $isUpdate = false)
    {
        $this->application = $application;
        $this->isUpdate = $isUpdate;
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

        $msg = $this->isUpdate
            ? "Notice: Selection details for {$candidateName} (Role: {$jobTitle}) have been revised by {$clientName}."
            : "Success! {$candidateName} has been selected by {$clientName} for the job {$jobTitle}.";

        return [
            'message'        => $msg,
            'application_id' => $this->application->id,
            'icon'           => $this->isUpdate ? 'refresh' : 'check-circle',
        ];
    }
}
