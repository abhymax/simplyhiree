<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsBell extends Component
{
    public $unreadNotifications;
    public $notificationCount;

    // This 'mount' function runs when the component is first loaded
    public function mount()
    {
        $this->loadNotifications();
    }

    // This function fetches the notifications
    public function loadNotifications()
    {
        $user = Auth::user();
        $this->unreadNotifications = $user->unreadNotifications;
        $this->notificationCount = $user->unreadNotifications->count();
    }

    // This function is called when a user clicks a single notification
    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        
        if($notification) {
            $notification->markAsRead();
        }

        // Refresh the notification list
        $this->loadNotifications();

        // This is a special Livewire event to tell the browser to redirect
        // We'll use the 'application_id' we stored in the notification
        // This is a placeholder, as we haven't built the "view application" link yet.
        // For now, it just reloads.
        return redirect(request()->header('Referer'));
    }

    // This function is called by the "Mark all as read" button
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications(); // Refresh the list
    }

    // This renders the component's view
    public function render()
    {
        return view('livewire.notifications-bell');
    }
}