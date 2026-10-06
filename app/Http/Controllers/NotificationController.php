<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    public function markAllRead(Request $request): RedirectResponse
    {
        $this->markUnreadNotificationsRead($request);

        return back();
    }

    public function markRead(Request $request, string $notificationId): RedirectResponse
    {
        $user = $request->user();

        if ($user && Schema::hasTable('notifications')) {
            $user->notifications()->whereKey($notificationId)->whereNull('read_at')->update(['read_at' => now()]);
        }

        return back();
    }

    private function markUnreadNotificationsRead(Request $request): void
    {
        $user = $request->user();

        if ($user && Schema::hasTable('notifications')) {
            $user->unreadNotifications()->update(['read_at' => now()]);
        }
    }
}
