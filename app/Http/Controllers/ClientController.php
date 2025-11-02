<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User; // <-- ADD THIS
use App\Notifications\CandidateRejectedByClient; // <-- ADD THIS
use App\Notifications\CandidateSelected;        // <-- ADD THIS
use App\Notifications\InterviewScheduled;       // <-- ADD THIS
use Illuminate\Support\Facades\Notification; // <-- ADD THIS

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

        $application->update([
            'hiring_status' => 'Client Rejected'
        ]);

        // --- NOTIFICATION LOGIC ---
        $this->notifyAdminAndPartner(new CandidateRejectedByClient($application), $application);
        // --------------------------

        return redirect()->back()->with('success', 'Candidate has been rejected.');
    }

    /**
     * Show the form to schedule an interview.
     */
    public function showInterviewForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.interview', ['application' => $application]);
    }

    /**
     * Store the interview details.
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

        // --- NOTIFICATION LOGIC ---
        $this->notifyAdminAndPartner(new InterviewScheduled($application), $application);
        // --------------------------

        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview scheduled successfully!');
    }

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

    /**
     * Show the form to select a candidate.
     */
    public function showSelectForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.select', ['application' => $application]);
    }

    /**
     * Store the final selection and joining date.
     */
    public function storeSelection(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'joining_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'hiring_status' => 'Selected',
            'joining_date' => $validated['joining_date'],
            'client_notes' => $validated['joining_notes'],
        ]);

        // --- NOTIFICATION LOGIC ---
        $this->notifyAdminAndPartner(new CandidateSelected($application), $application);
        // --------------------------

        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Candidate Selected! Joining date has been set.');
    }


    /**
     * *** ADD THIS NEW HELPER METHOD ***
     * Reusable function to notify admin and partner.
     */
    private function notifyAdminAndPartner($notification, JobApplication $application)
    {
        // 1. Notify all Superadmins
        $admins = User::role('Superadmin')->get();
        Notification::send($admins, $notification);

        // 2. Notify the Partner (if one exists for this candidate)
        // We check if it's a partner candidate (not a direct apply)
        if ($application->candidate && $application->candidate->partner) {
            $partner = $application->candidate->partner;
            $partner->notify($notification);
        }
    }
}