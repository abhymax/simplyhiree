<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateSelected extends Notification implements ShouldQueue
{
    use Queueable;

    public $application; // <-- THIS LINE WAS CHANGED (removed "JobApplication")

    /**
     * Create a new notification instance.
     */
    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $clientName = $this->application->job->user->name;
        $jobTitle = $this->application->job->title;
        $candidateName = $this->application->candidate 
                         ? $this->application->candidate->first_name . ' ' . $this->application->candidate->last_name 
                         : $this->application->candidateUser->name;

        return [
            'message' => "Success! {$candidateName} has been selected by {$clientName} for the job {$jobTitle}.",
            'application_id' => $this->application->id,
            'icon' => 'check-circle', // Icon for the UI
        ];
    }
}