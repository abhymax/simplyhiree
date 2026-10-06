<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateRejectedByClient extends Notification implements ShouldQueue
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
        $channels = ['database'];
        if (method_exists($notifiable, 'hasRole') && $notifiable->hasRole('partner')) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail(object $notifiable): \Illuminate\Notifications\Messages\MailMessage
    {
        $candidateName = $this->application->candidate
            ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
            : ($this->application->candidateUser?->name ?? 'Candidate');

        $jobTitle = $this->application->job ? $this->application->job->title : 'Job';
        $companyName = $this->application->job ? $this->application->job->company_name : 'the company';
        $appCode = $this->application->application_code ?? ('SH-APP-' . str_pad($this->application->id, 6, '0', STR_PAD_LEFT));

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("[SimplyHiree] Application Update: Candidate Rejected - {$candidateName}")
            ->view('emails.vendor_candidate_rejected', [
                'partnerName'   => $notifiable->name,
                'candidateName' => $candidateName,
                'jobTitle'      => $jobTitle,
                'companyName'   => $companyName,
                'appCode'       => $appCode,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $clientName = $this->application->job?->user?->name ?? 'Unknown Client';
        $jobTitle   = $this->application->job?->title ?? 'Unknown Job';
        $candidateName = $this->application->candidate
                         ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
                         : ($this->application->candidateUser?->name ?? 'Unknown Candidate');

        return [
            'message' => "Update: {$candidateName} was rejected by {$clientName} for the job {$jobTitle}.",
            'application_id' => $this->application->id,
            'icon' => 'x-circle',
        ];
    }
}