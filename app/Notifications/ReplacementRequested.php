<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ReplacementRequested extends Notification implements ShouldQueue
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
        $jobTitle      = $this->application->job?->title ?? 'Unknown Job';
        $companyName   = $this->application->job?->is_company_confidential ? 'SimplyHiree Client' : ($this->application->job?->company_name ?? 'Client');
        $candidateName = $this->application->candidate
                         ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
                         : ($this->application->candidateUser?->name ?? 'Unknown Candidate');

        return [
            'message'        => "Replacement Requested! {$companyName} requires a replacement for {$candidateName} (Job: {$jobTitle}).",
            'application_id' => $this->application->id,
            'icon'           => 'rotate',
        ];
    }
}
