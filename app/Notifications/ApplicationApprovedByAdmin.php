<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ApplicationApprovedByAdmin extends Notification implements ShouldQueue
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
            'message' => "Application Approved: {$candidateName} for '{$jobTitle}' has been forwarded to the client.",
            'application_id' => $this->application->id,
            'icon' => 'check-circle',
        ];
    }
}