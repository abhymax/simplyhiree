<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CandidateLeft extends Notification implements ShouldQueue
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
        $candidateName = $this->application->candidate
                         ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
                         : ($this->application->candidateUser?->name ?? 'Unknown Candidate');
        $leftDate      = $this->application->left_at
                         ? $this->application->left_at->format('M d, Y')
                         : 'Unknown Date';

        return [
            'message'        => "Update: {$candidateName} (hired for {$jobTitle}) left on {$leftDate}.",
            'application_id' => $this->application->id,
            'icon'           => 'user-clock',
        ];
    }
}
