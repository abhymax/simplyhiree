<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ClientJobApprovedForAdmin extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Job $job,
        public ?string $approvedBy = null
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $clientName = $this->job->user?->name ?? 'Unknown Client';
        $actor = $this->approvedBy ? " by {$this->approvedBy}" : '';

        return [
            'message' => "Client job '{$this->job->title}' for {$clientName} was approved{$actor}.",
            'job_id' => $this->job->id,
            'icon' => 'check-circle',
        ];
    }
}

