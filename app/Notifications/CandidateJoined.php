<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class CandidateJoined extends Notification implements ShouldQueue
{
    use Queueable;

    public JobApplication $application;

    public function __construct(JobApplication $application)
    {
        $this->application = $application;
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // If the notifiable user is the Sourcing Partner who submitted this candidate, send an email
        if ($this->application->candidate && $this->application->candidate->partner_id === $notifiable->id) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $jobTitle = $this->application->job?->title ?? 'Unknown Job';
        
        $candidateName = $this->application->candidate
            ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
            : ($this->application->candidateUser?->name ?? 'Unknown Candidate');

        $job = $this->application->job;
        $company = $job?->company_name ?: (optional($job?->user)->name ?? 'the company');
        if ($job && $job->is_company_confidential) {
            $company = 'Confidential (details on call)';
        }

        $joiningDate = $this->application->joining_date ? $this->application->joining_date->format('F d, Y') : 'TBD';
        $ctc = $this->application->final_ctc ? '₹' . number_format($this->application->final_ctc, 0) : 'As per offer letter';

        return (new MailMessage)
            ->subject("Placement Successful! {$candidateName} has joined {$company} — SimplyHiree")
            ->view('emails.partner_candidate_joined', [
                'name'         => $candidateName,
                'partnerName'  => $notifiable->name,
                'company'      => $company,
                'role'         => $jobTitle,
                'joining_date' => $joiningDate,
                'ctc'          => $ctc,
            ]);
    }

    public function toDatabase(object $notifiable): array
    {
        $jobTitle      = $this->application->job?->title ?? 'Unknown Job';
        $candidateName = $this->application->candidate
                         ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
                         : ($this->application->candidateUser?->name ?? 'Unknown Candidate');

        return [
            'message'        => "{$candidateName} has successfully joined for the job: {$jobTitle}.",
            'application_id' => $this->application->id,
            'icon'           => 'user-check',
        ];
    }
}

