<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\JobApplication; // <-- ADD THIS

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        // Count how many jobs are waiting for approval
        $pendingJobsCount = Job::where('status', 'pending_approval')->count();

        return view('admin.dashboard', ['pendingJobsCount' => $pendingJobsCount]);
    }

    /**
     * Display a list of jobs pending approval.
     */
    public function pendingJobs()
    {
        $pendingJobs = Job::with('user')->where('status', 'pending_approval')->latest()->get();
        return view('admin.jobs.pending', ['jobs' => $pendingJobs]);
    }

    /**
     * Approve a pending job.
     */
    public function approveJob(Job $job)
    {
        $job->status = 'approved';
        $job->save();

        return redirect()->route('admin.jobs.pending')->with('success', 'Job has been approved and is now live.');
    }

    /**
     * Reject a pending job.
     */
    public function rejectJob(Job $job)
    {
        $job->status = 'rejected';
        $job->save();

        return redirect()->route('admin.jobs.pending')->with('success', 'Job has been rejected.');
    }

    /**
     * Show the page to manage partner exclusions for a specific job.
     */
    public function manageJobExclusions(Job $job)
    {
        // Get a list of all users who have the 'partner' role
        $allPartners = User::where('role', 'partner')->orderBy('name')->get();

        // Get a list of IDs for partners who are already excluded from this job
        $excludedPartnerIds = $job->excludedPartners()->pluck('users.id')->toArray();

        return view('admin.jobs.manage', [
            'job' => $job,
            'allPartners' => $allPartners,
            'excludedPartnerIds' => $excludedPartnerIds,
        ]);
    }

    /**
     * Update the list of excluded partners for a job.
     */
    public function updateJobExclusions(Request $request, Job $job)
    {
        // Get the list of partner IDs that were checked in the form
        $excludedIds = $request->input('excluded_partners', []);

        // Use the 'sync' method to update the relationship in the database.
        $job->excludedPartners()->sync($excludedIds);

        return redirect()->route('admin.jobs.pending')->with('success', 'Partner access for the job has been updated successfully.');
    }
    
    /**
     * *** NEW METHOD ***
     * Display a list of all job applications submitted by partners.
     */
    public function listApplications()
    {
        $applications = JobApplication::with(['job', 'candidate', 'partner'])
                                      ->latest()
                                      ->paginate(20); // Paginate for performance

        return view('admin.applications.index', ['applications' => $applications]);
    }
    /**
     * *** NEW METHOD ***
     * Approve a submitted job application.
     */
    public function approveApplication(JobApplication $application)
    {
        $application->update(['status' => 'Approved']);
        
        return redirect()->route('admin.applications.index')->with('success', 'Application approved successfully.');
    }

    /**
     * *** NEW METHOD ***
     * Reject a submitted job application.
     */
    public function rejectApplication(JobApplication $application)
    {
        $application->update(['status' => 'Rejected']);
        
        return redirect()->route('admin.applications.index')->with('success', 'Application rejected successfully.');
    }
    
}

