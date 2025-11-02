<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InterviewScheduled extends Notification implements ShouldQueue
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
        return ['database']; // This stores the notification in the database
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
            'message' => "{$clientName} scheduled an interview for {$candidateName} (Job: {$jobTitle}).",
            'application_id' => $this->application->id,
            'icon' => 'calendar-event', // Icon for the UI
        ];
    }
}