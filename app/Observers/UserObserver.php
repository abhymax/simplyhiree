<?php

namespace App\Observers;

use App\Models\User;
use App\Services\SuperadminActivityService;

class UserObserver
{
    public function updated(User $user): void
    {
        if (!$user->wasChanged('status')) {
            return;
        }

        $from = (string) $user->getOriginal('status');
        $to = (string) $user->status;

        if ($from === 'pending' && $to === 'active') {
            if ($user->hasRole('client')) {
                app(SuperadminActivityService::class)->logEvent(
                    eventKey: 'profile.approved.client',
                    title: 'Client Profile Approved',
                    message: "Client profile approved for {$user->name} ({$user->email}).",
                    icon: 'check-circle',
                    subject: $user,
                    metadata: ['user_id' => $user->id, 'role' => 'client']
                );

                app(SuperadminActivityService::class)->sendProfileApprovedWhatsApp($user, 'client');
            }

            if ($user->hasRole('partner')) {
                app(SuperadminActivityService::class)->logEvent(
                    eventKey: 'profile.approved.partner',
                    title: 'Partner Profile Approved',
                    message: "Partner profile approved for {$user->name} ({$user->email}).",
                    icon: 'check-circle',
                    subject: $user,
                    metadata: ['user_id' => $user->id, 'role' => 'partner']
                );

                app(SuperadminActivityService::class)->sendProfileApprovedWhatsApp($user, 'partner');
            }
        }
    }
}
