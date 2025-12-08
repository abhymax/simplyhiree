<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CandidateDidNotJoin extends Notification implements ShouldQueue
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
        $jobTitle = $this->application->job->title;
        $candidateName = $this->application->candidate 
                         ? $this->application->candidate->first_name . ' ' . $this->application->candidate->last_name 
                         : $this->application->candidateUser->name;

        return [
            'message' => "Update: {$candidateName} (selected for {$jobTitle}) Did Not Join.",
            'application_id' => $this->application->id,
            'icon' => 'user-xmark', // New Icon
        ];
    }
}