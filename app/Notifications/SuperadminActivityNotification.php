<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuperadminActivityNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $eventKey,
        public string $title,
        public string $message,
        public string $icon = 'check-circle',
        public array $extra = []
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return array_merge([
            'event_key' => $this->eventKey,
            'title' => $this->title,
            'message' => $this->message,
            'icon' => $this->icon,
        ], $this->extra);
    }
}
