<?php

namespace App\Notifications;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CandidateLifecycleUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public JobApplication $application,
        public string $event,
        public ?int $roundNumber = null
    ) {
    }

    public function via(object $notifiable): array
    {
        if (!method_exists($notifiable, 'hasRole') || !$notifiable->hasRole('partner')) {
            return [];
        }

        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $this->application->loadMissing(['job.user', 'candidate', 'candidateUser']);

        $candidateName = $this->application->candidate
            ? trim(($this->application->candidate->first_name ?? '') . ' ' . ($this->application->candidate->last_name ?? ''))
            : ($this->application->candidateUser?->name ?? 'Candidate');
        $jobTitle = $this->application->job?->title ?? 'the job';
        $clientName = $this->application->job?->user?->name ?? 'The client';
        $interviewLabel = $this->roundNumber ? "round {$this->roundNumber} interview" : 'interview';

        $details = match ($this->event) {
            'shortlisted' => ["{$clientName} shortlisted {$candidateName} for {$jobTitle}.", 'user-check'],
            'maybe' => ["{$clientName} saved {$candidateName} in Maybe for {$jobTitle}.", 'bookmark'],
            'interview_appeared' => ["{$candidateName} was marked as appeared for the {$interviewLabel} for {$jobTitle}.", 'user-check'],
            'interview_no_show' => ["{$candidateName} was marked as a no-show for the {$interviewLabel} for {$jobTitle}.", 'user-x'],
            'interview_rescheduled' => ["{$clientName} rescheduled {$candidateName}'s interview for {$jobTitle}.", 'calendar-event'],
            'round_rescheduled' => ["{$clientName} rescheduled round {$this->roundNumber} for {$candidateName} ({$jobTitle}).", 'calendar-event'],
            default => ["{$candidateName}'s status was updated for {$jobTitle}.", 'bell'],
        };

        return [
            'message' => $details[0],
            'application_id' => $this->application->id,
            'icon' => $details[1],
            'event_key' => 'candidate.' . $this->event,
            'audience' => 'partner',
        ];
    }
}
