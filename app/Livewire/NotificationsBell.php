<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\JobApplication; // Import this so we can look up Job IDs

class NotificationsBell extends Component
{
    public $unreadNotifications;
    public $notificationCount;

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $user = Auth::user();
        // We load unread notifications to show in the list
        $this->unreadNotifications = $user->unreadNotifications;
        $this->notificationCount = $user->unreadNotifications->count();
    }

    /**
     * Mark a single notification as read and redirect the user
     * to the relevant page based on the notification type.
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = $user->notifications()->find($notificationId);

        if (!$notification) {
            return;
        }

        // 1. Mark as read
        $notification->markAsRead();

        // 2. Extract Data
        $data = $notification->data;
        
        // 3. Handle "Job Application" Related Notifications
        if (isset($data['application_id'])) {
            $application = JobApplication::find($data['application_id']);
            
            // If the application was deleted, just stay on the page
            if (!$application) {
                $this->loadNotifications();
                return;
            }

            // Redirect logic based on Role
            if ($user->hasRole('client')) {
                // Clients go to the specific Job's applicant list
                return redirect()->route('client.jobs.applicants', $application->job_id);
            } 
            elseif ($user->hasRole('partner')) {
                // Partners go to their applications list
                return redirect()->route('partner.applications');
            } 
            elseif ($user->hasRole('candidate')) {
                // Candidates go to their applications list
                return redirect()->route('candidate.applications');
            } 
            elseif ($user->hasRole('Superadmin')) {
                // Admins go to the master list
                return redirect()->route('admin.applications.index');
            }
        }

        // 4. Handle "Job Posting" Related Notifications (Approved/Rejected)
        if (isset($data['job_id'])) {
            if ($user->hasRole('client')) {
                return redirect()->route('client.dashboard');
            }
            if ($user->hasRole('Superadmin')) {
                return redirect()->route('admin.jobs.pending');
            }
        }

        // 5. Default Fallback (Reload current page)
        $this->loadNotifications();
        return redirect(request()->header('Referer'));
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.notifications-bell');
    }
}