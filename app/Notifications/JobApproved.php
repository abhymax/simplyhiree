<?php

namespace App\Notifications;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class JobRejected extends Notification implements ShouldQueue
{
    use Queueable;

    public $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'message' => "Your job posting '{$this->job->title}' was rejected by the admin.",
            'job_id' => $this->job->id,
            'icon' => 'x-circle',
        ];
    }
}