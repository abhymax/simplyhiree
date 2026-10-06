<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CandidateSelected extends Notification implements ShouldQueue
{
    use Queueable;

    public JobApplication $application;
    public bool $isUpdate;

    public function __construct(JobApplication $application, bool $isUpdate = false)
    {
        $this->application = $application;
        $this->isUpdate = $isUpdate;
    }

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
        $joiningDate = $this->application->joining_date ? $this->application->joining_date->format('F d, Y') : 'TBD';
        $ctc = $this->application->final_ctc ? '₹' . number_format($this->application->final_ctc, 0) : 'As per offer letter';
        $notes = $this->application->client_notes;

        return (new \Illuminate\Notifications\Messages\MailMessage)
            ->subject("[SimplyHiree] Candidate Selected! - {$candidateName}")
            ->view('emails.vendor_candidate_selected', [
                'partnerName'   => $notifiable->name,
                'candidateName' => $candidateName,
                'jobTitle'      => $jobTitle,
                'companyName'   => $companyName,
                'joiningDate'   => $joiningDate,
                'ctc'           => $ctc,
                'appCode'       => $appCode,
                'notes'         => $notes,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $clientName    = $this->application->job?->user?->name ?? 'Unknown Client';
        $jobTitle      = $this->application->job?->title ?? 'Unknown Job';
        $candidateName = $this->application->candidate
                         ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
                         : ($this->application->candidateUser?->name ?? 'Unknown Candidate');

        $msg = $this->isUpdate
            ? "Notice: Selection details for {$candidateName} (Role: {$jobTitle}) have been revised by {$clientName}."
            : "Success! {$candidateName} has been selected by {$clientName} for the job {$jobTitle}.";

        return [
            'message'        => $msg,
            'application_id' => $this->application->id,
            'icon'           => $this->isUpdate ? 'refresh' : 'check-circle',
        ];
    }
}
