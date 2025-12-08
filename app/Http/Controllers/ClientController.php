<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Notifications\CandidateRejectedByClient;
use App\Notifications\CandidateSelected;
use App\Notifications\InterviewScheduled;
use App\Notifications\CandidateJoined; 
use App\Notifications\CandidateDidNotJoin;
use App\Notifications\CandidateLeft;
use Illuminate\Support\Facades\Notification;

class ClientController extends Controller
{
    /**
     * Show the client dashboard.
     */
    public function index()
    {
        $client = Auth::user();
        $jobs = Job::where('user_id', $client->id)->latest()->get();
        return view('client.dashboard', [
            'client' => $client,
            'jobs'   => $jobs
        ]);
    }
    
    /**
     * Show the applicants for a specific job.
     */
    public function showApplicants(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $approvedApplications = JobApplication::where('job_id', $job->id)
                                            ->where('status', 'Approved')
                                            ->with(['candidate', 'candidateUser'])
                                            ->latest()
                                            ->paginate(20)
                                            ->through(function ($app) {
                                                if ($app->interview_at) {
                                                    $app->interview_at = \Carbon\Carbon::parse($app->interview_at);
                                                }
                                                if ($app->joining_date) {
                                                    $app->joining_date = \Carbon\Carbon::parse($app->joining_date);
                                                }
                                                if ($app->left_at) {
                                                    $app->left_at = \Carbon\Carbon::parse($app->left_at);
                                                }
                                                return $app;
                                            });

        return view('client.jobs.applicants', [
            'job' => $job,
            'applications' => $approvedApplications
        ]);
    }
    
    /**
     * Client rejects a candidate.
     */
    public function rejectApplicant(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'Client Rejected']);
        $this->notifyAdminAndPartner(new CandidateRejectedByClient($application), $application);
        return redirect()->back()->with('success', 'Candidate has been rejected.');
    }

    // --- INTERVIEW SCHEDULING (POST/NEW) ---
    /**
     * Show the form to schedule an interview (for new status).
     */
    public function showInterviewForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        // Pass a flag to indicate this is a POST/NEW form
        return view('client.jobs.interview', ['application' => $application, 'isEdit' => false]);
    }

    /**
     * Store the interview details (POST/NEW).
     */
    public function scheduleInterview(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'interview_at' => 'required|date|after:now',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        $application->update([
            'hiring_status' => 'Interview Scheduled',
            'interview_at' => $validated['interview_at'],
            'client_notes' => $validated['client_notes'],
        ]);
        $this->notifyAdminAndPartner(new InterviewScheduled($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview scheduled successfully!');
    }
    
    // --- *** NEW EDIT METHODS FOR INTERVIEW DETAILS *** ---

    /**
     * Show the form to edit existing interview details.
     */
    public function editInterviewDetails(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        // Pass a flag to indicate this is a PATCH/EDIT form
        return view('client.jobs.interview', ['application' => $application, 'isEdit' => true]);
    }

    /**
     * Update the existing interview details.
     */
    public function updateInterviewDetails(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'interview_at' => 'required|date|after:now',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        // We do NOT change hiring_status here, as it's already "Interview Scheduled"
        $application->update([
            'interview_at' => $validated['interview_at'],
            'client_notes' => $validated['client_notes'],
        ]);
        // No need to send full notification, just an update confirmation
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview details updated successfully!');
    }
    
    // --- END NEW EDIT METHODS ---


    /**
     * Mark a candidate as 'Interviewed'.
     */
    public function markAsAppeared(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'Interviewed']);
        return redirect()->back()->with('success', 'Candidate marked as \'Interviewed\'.');
    }

    /**
     * Mark a candidate as 'No-Show'.
     */
    public function markAsNoShow(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'No-Show']);
        return redirect()->back()->with('success', 'Candidate marked as \'No-Show\'.');
    }
    
    // --- SELECTION (POST/NEW) ---
    /**
     * Show the form to select a candidate (for new status).
     */
    public function showSelectForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.select', ['application' => $application, 'isEdit' => false]);
    }

    /**
     * Store the final selection and joining date (POST/NEW).
     */
    public function storeSelection(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        $application->update([
            'hiring_status' => 'Selected',
            'joining_date' => $validated['joining_date'],
            'client_notes' => $validated['client_notes'],
        ]);
        $this->notifyAdminAndPartner(new CandidateSelected($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Candidate Selected! Joining date has been set.');
    }

    // --- *** NEW EDIT METHODS FOR SELECTION DETAILS *** ---

    /**
     * Show the form to edit existing selection details.
     */
    public function editSelectionDetails(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.select', ['application' => $application, 'isEdit' => true]);
    }

    /**
     * Update the existing selection and joining date.
     */
    public function updateSelectionDetails(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        $application->update([
            'joining_date' => $validated['joining_date'],
            'client_notes' => $validated['client_notes'],
        ]);
        // No need to send full notification, just an update confirmation
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Selection details updated successfully!');
    }

    // --- END NEW EDIT METHODS ---


    /**
     * Mark a candidate as 'Joined'.
     */
    public function markAsJoined(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['joined_status' => 'Joined']);
        $this->notifyAdminAndPartner(new CandidateJoined($application), $application);
        return redirect()->back()->with('success', 'Candidate marked as \'Joined\'.');
    }

    /**
     * Mark a candidate as 'Did Not Join'.
     */
    public function markAsNotJoined(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['joined_status' => 'Did Not Join']);
        $this->notifyAdminAndPartner(new CandidateDidNotJoin($application), $application);
        return redirect()->back()->with('success', 'Candidate marked as \'Did Not Join\'.');
    }

    /**
     * Show the form to mark a candidate as 'Left'.
     */
    public function showLeftForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.left', ['application' => $application]);
    }

    /**
     * Store the date the candidate 'Left'.
     */
    public function markAsLeft(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'left_at' => 'required|date|after_or_equal:joining_date',
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'joined_status' => 'Left',
            'left_at' => $validated['left_at'],
            'client_notes' => $validated['client_notes'],
        ]);
        
        $this->notifyAdminAndPartner(new CandidateLeft($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Candidate marked as \'Left\'.');
    }


    /**
     * Reusable function to notify admin and partner.
     */
    private function notifyAdminAndPartner($notification, JobApplication $application)
    {
        $application->load(['job.user', 'candidate.partner', 'candidateUser']);
        
        // 1. Notify all Superadmins
        $admins = User::role('Superadmin')->get();
        Notification::send($admins, $notification);

        // 2. Notify the Partner (if one exists for this candidate)
        if ($application->candidate && $application->candidate->partner) {
            $partner = $application->candidate->partner;
            $partner->notify($notification);
        }
    }
}