<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\JobApplication;

class ApplicationRejectedByAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public $application;

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
        $candidateName = trim(
            (string) (($this->application->candidate?->first_name ?? '') . ' ' . ($this->application->candidate?->last_name ?? ''))
        );
        if ($candidateName === '') {
            $candidateName = (string) ($this->application->candidateUser?->name ?? 'Candidate');
        }
            
        $jobTitle = $this->application->job ? $this->application->job->title : 'Job';
        $applicationCode = (string) ($this->application->application_code ?? ('#' . $this->application->id));

        return [
            'message' => "SimplyHiree rejected {$applicationCode}: {$candidateName} for '{$jobTitle}'.",
            'application_id' => $this->application->id,
            'icon' => 'x-circle',
        ];
    }
}
