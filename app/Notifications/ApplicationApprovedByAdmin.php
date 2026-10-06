<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\JobApplication;

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
        $channels = ['database'];
        // Spatie hasRole check to ensure email only goes to vendor partners
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
            ->subject("[SimplyHiree] Candidate Application Approved & Forwarded - {$candidateName}")
            ->view('emails.vendor_application_approved', [
                'partnerName'   => $notifiable->name,
                'candidateName' => $candidateName,
                'jobTitle'      => $jobTitle,
                'companyName'   => $companyName,
                'appCode'       => $appCode,
            ]);
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
            'message' => "SimplyHiree approved {$applicationCode}: {$candidateName} for '{$jobTitle}' and forwarded it to client.",
            'application_id' => $this->application->id,
            'icon' => 'check-circle',
        ];
    }
}
