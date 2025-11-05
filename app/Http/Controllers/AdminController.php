<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard with stats.
     */
    public function index()
    {
        // Fetch all stats
        $totalUsers = User::count();
        $totalClients = User::role('client')->count();
        $totalPartners = User::role('partner')->count();
        $pendingJobs = Job::where('status', 'pending_approval')->count();
        $pendingApplications = JobApplication::where('status', 'Pending Review')->count(); // Added this as a bonus

        // Pass all stats to the view
        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalClients' => $totalClients,
            'totalPartners' => $totalPartners,
            'pendingJobs' => $pendingJobs,
            'pendingApplications' => $pendingApplications
        ]);
    }

    /**
     * *** ADD THIS NEW METHOD ***
     * Show all users in the system.
     */
    public function listUsers()
    {
        // Fetch all users, load their roles, and paginate
        $users = User::with('roles')->latest()->paginate(25);

        // We will create this new view file next
        return view('admin.users.index', [
            'users' => $users
        ]);
    }

    /**
     * *** ADD THIS NEW METHOD ***
     * Show all CLIENT users.
     */
    public function listClients()
    {
        // Fetch only users with the 'client' role
        $clients = User::role('client')
                       ->with('roles') // Eager load role details
                       ->latest()
                       ->paginate(25);

        // We will create this new view file next
        return view('admin.clients.index', [
            'users' => $clients // Pass the 'users' variable for consistency
        ]);
    }

    /**
     * *** ADD THIS NEW METHOD ***
     * Show the form to edit a client's billable period.
     */
    public function editClient(User $user)
    {
        // Ensure we are only editing clients
        if (!$user->hasRole('client')) {
            abort(404);
        }

        return view('admin.clients.edit', [
            'user' => $user
        ]);
    }

    /**
     * *** ADD THIS NEW METHOD ***
     * Update the client's billable period.
     */
    public function updateClient(Request $request, User $user)
    {
        // Ensure we are only editing clients
        if (!$user->hasRole('client')) {
            abort(404);
        }

        $validated = $request->validate([
            'billable_period_days' => 'required|integer|min:1',
        ]);

        $user->update([
            'billable_period_days' => $validated['billable_period_days']
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Client billable period updated successfully!');
    }

    /**
     * *** ADD THIS NEW METHOD ***
     * Show all PARTNER users.
     */
    public function listPartners()
    {
        // Fetch only users with the 'partner' role
        $partners = User::role('partner')
                        ->with('roles') // Eager load role details
                        ->latest()
                        ->paginate(25);

        // We will create this new view file next
        return view('admin.partners.index', [
            'users' => $partners // Pass the 'users' variable for consistency
        ]);
    }

    /**
     * Show all submitted applications.
     */
    public function listApplications()
    {
        $applications = JobApplication::with(['job', 'candidate', 'candidateUser', 'candidate.partner'])
                                    ->latest()
                                    ->paginate(20);

        return view('admin.applications.index', ['applications' => $applications]);
    }

    /**
     * Approve a job application.
     */
    public function approveApplication(JobApplication $application)
    {
        $application->update(['status' => 'Approved']);
        // TODO: Notify partner
        return redirect()->back()->with('success', 'Application approved.');
    }

    /**
     * Reject a job application.
     */
    public function rejectApplication(JobApplication $application)
    {
        $application->update(['status' => 'Rejected']);
        // TODO: Notify partner
        return redirect()->back()->with('success', 'Application rejected.');
    }

    /**
     * Show pending jobs.
     */
    public function pendingJobs()
    {
        $pendingJobs = Job::where('status', 'pending_approval')
                          ->with('user') // Eager load the client who posted it
                          ->latest()
                          ->paginate(20);

        return view('admin.jobs.pending', ['jobs' => $pendingJobs]);
    }

    /**
     * Approve a job posting.
     */
    public function approveJob(Request $request, Job $job)
    {
        $validated = $request->validate([
            'payout_amount' => 'required|numeric|min:0',
            'minimum_stay_days' => 'required|integer|min:1',
        ]);

        $job->update([
            'status' => 'approved',
            'payout_amount' => $validated['payout_amount'],
            'minimum_stay_days' => $validated['minimum_stay_days'],
        ]);

        // TODO: Notify client
        return redirect()->back()->with('success', 'Job has been approved and is now live.');
    }

    /**
     * Reject a job posting.
     */
    public function rejectJob(Job $job)
    {
        $job->update(['status' => 'rejected']);
        // TODO: Notify client
        return redirect()->back()->with('success', 'Job has been rejected.');
    }

    /**
     * Show the page to manage partner exclusions for a job.
     */
    public function manageJobExclusions(Job $job)
    {
        $partners = User::role('partner')->get();
        $excludedPartnerIds = $job->excludedPartners()->pluck('users.id')->toArray();

        return view('admin.jobs.manage', [
            'job' => $job,
            'partners' => $partners,
            'excludedPartnerIds' => $excludedPartnerIds
        ]);
    }

    /**
     * Update the list of excluded partners for a job.
     */
    public function updateJobExclusions(Request $request, Job $job)
    {
        $job->excludedPartners()->sync($request->input('excluded_partners', []));

        return redirect()->route('admin.jobs.pending')->with('success', 'Partner exclusions updated successfully.');
    }

    /**
     * *** THIS IS THE NEW METHOD, CLEANED AND IN THE RIGHT PLACE ***
     * Show the billing report for selected candidates.
     */
    public function billingReport()
    {
        // 1. Get all placements that are "Selected"
        $placements = JobApplication::where('hiring_status', 'Selected')
                                    ->with(['job.user', 'candidate', 'candidateUser']) // Eager load all relationships
                                    ->get();

        $today = Carbon::now()->startOfDay();
        $reportData = [];

        // 2. Process each placement to calculate billing status
        foreach ($placements as $app) {
            
            // Skip if data is incomplete (e.g., no joining date, or client not found)
            if (empty($app->joining_date) || empty($app->job->user) || empty($app->job->user->billable_period_days)) {
                continue;
            }

            // 3. Get all the data points
            $client = $app->job->user;
            $joiningDate = Carbon::parse($app->joining_date);
            $billableDays = $client->billable_period_days;
            $invoiceDate = $joiningDate->copy()->addDays($billableDays);

            // 4. Determine the billing status
            $status = $invoiceDate->isPast() ? 'Billable' : 'Pending';
            
            // Get candidate name (checking both partner and direct apply)
            $candidateName = $app->candidate 
                             ? $app->candidate->first_name . ' ' . $app->candidate->last_name
                             : $app->candidateUser->name;

            // 5. Add to our report
            $reportData[] = (object) [
                'candidate_name' => $candidateName,
                'client_name' => $client->name,
                'job_title' => $app->job->title,
                'joining_date' => $app->joining_date->format('M d, Y'),
                'billable_period' => $billableDays . ' days',
                'invoice_date' => $invoiceDate->format('M d, Y'),
                'status' => $status,
            ];
        }

        // Sort by invoice date, newest first (using a compatible function)
        $reportData = collect($reportData)->sortByDesc(function($item) {
            return Carbon::parse($item->invoice_date);
        });

        return view('admin.billing.index', [
            'placements' => $reportData
        ]);
    }

    /**
     * Show a report of all jobs with their lined-up partners and candidates.
     */
    public function jobReport()
    {
        $jobs = Job::with([
                'user', // The client who posted the job
                'jobApplications.candidate.partner.user', // The partner who submitted the candidate
                'jobApplications.candidateUser' // The candidate's user record for name
            ])
            ->latest()
            ->get();

        return view('admin.reports.jobs', ['jobs' => $jobs]);
    }
}