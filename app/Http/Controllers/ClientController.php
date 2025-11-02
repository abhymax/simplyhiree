<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job; // Import the Job model
use App\Models\JobApplication; // <-- ADD THIS
use App\Models\JobCategory; // <-- This might already exist, if not, add it

class ClientController extends Controller
{
    /**
     * Show the client dashboard.
     */
    public function index()
    {
        $client = Auth::user();

        // Fetch all jobs posted by this client, ordered by the newest first.
        $jobs = Job::where('user_id', $client->id)->latest()->get();

        // Pass both the client and their jobs to the view.
        return view('client.dashboard', [
            'client' => $client,
            'jobs'   => $jobs
        ]);
    }
    /**
     * *** NEW METHOD ***
     * Show the applicants for a specific job.
     */
    public function showApplicants(Job $job)
    {
        // Check if the authenticated user is the one who posted the job
        if ($job->user_id !== Auth::id()) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        // Load the applications for this job that have been approved by the admin
        $approvedApplications = JobApplication::where('job_id', $job->id)
                                            ->where('status', 'Approved')
                                            ->with(['candidate', 'candidateUser']) // Load candidate details
                                            ->latest()
                                            ->paginate(20);

        return view('client.jobs.applicants', [
            'job' => $job,
            'applications' => $approvedApplications
        ]);
    }
    /**
 * *** NEW METHOD ***
 * Client rejects a candidate.
 */
public function rejectApplicant(JobApplication $application)
{
    // Security check: ensure this job application belongs to this client
    if ($application->job->user_id !== Auth::id()) {
        abort(403);
    }

    $application->update([
        'hiring_status' => 'Client Rejected'
    ]);

    return redirect()->back()->with('success', 'Candidate has been rejected.');
}

/**
 * *** NEW METHOD ***
 * Show the form to schedule an interview.
 */
public function showInterviewForm(JobApplication $application)
{
    // Security check
    if ($application->job->user_id !== Auth::id()) {
        abort(403);
    }

    // We must eager load the relations for the view
    $application->load(['job', 'candidate', 'candidateUser']);

    return view('client.jobs.interview', ['application' => $application]);
}

/**
 * *** NEW METHOD ***
 * Store the interview details.
 */
public function scheduleInterview(Request $request, JobApplication $application)
{
    // Security check
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

    // TODO: This is where we will dispatch notifications to Admin and Partner.

    return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview scheduled successfully!');
}
}

