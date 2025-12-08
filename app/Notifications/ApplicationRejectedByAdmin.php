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
        $candidateName = $this->application->candidate 
            ? $this->application->candidate->first_name 
            : 'Candidate';
            
        $jobTitle = $this->application->job ? $this->application->job->title : 'Job';

        return [
            'message' => "Application Returned: {$candidateName} for '{$jobTitle}' was rejected.",
            'application_id' => $this->application->id,
            'icon' => 'x-circle',
        ];
    }
}