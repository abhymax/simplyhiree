<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

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
        $candidateName = $this->application->candidate->first_name . ' ' . $this->application->candidate->last_name;
        $jobTitle = $this->application->job->title;

        return [
            'message' => "Application Returned: {$candidateName} for '{$jobTitle}' was rejected by the admin.",
            'application_id' => $this->application->id,
            'icon' => 'x-circle',
        ];
    }
}