<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use App\Notifications\JobApproved;
use App\Notifications\JobRejected;
use App\Notifications\ApplicationApprovedByAdmin;
use App\Notifications\ApplicationRejectedByAdmin;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard with stats.
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalClients = User::role('client')->count();
        $totalPartners = User::role('partner')->count();
        $pendingJobs = Job::where('status', 'pending_approval')->count();
        $pendingApplications = JobApplication::where('status', 'Pending Review')->count();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalClients' => $totalClients,
            'totalPartners' => $totalPartners,
            'pendingJobs' => $pendingJobs,
            'pendingApplications' => $pendingApplications
        ]);
    }

    /**
     * Show all users in the system.
     */
    public function listUsers()
    {
        $users = User::with('roles')->latest()->paginate(25);

        return view('admin.users.index', [
            'users' => $users
        ]);
    }

    /**
     * Show all CLIENT users.
     */
    public function listClients()
    {
        $clients = User::role('client')
                       ->with('roles')
                       ->latest()
                       ->paginate(25);

        return view('admin.clients.index', [
            'users' => $clients 
        ]);
    }

    /**
     * Show the form to edit a client's billable period.
     */
    public function editClient(User $user)
    {
        if (!$user->hasRole('client')) {
            abort(404);
        }

        return view('admin.clients.edit', [
            'user' => $user
        ]);
    }

    /**
     * Update the client's billable period.
     */
    public function updateClient(Request $request, User $user)
    {
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
     * Show all PARTNER users.
     */
    public function listPartners()
    {
        $partners = User::role('partner')
                        ->with('roles')
                        ->latest()
                        ->paginate(25);

        return view('admin.partners.index', [
            'users' => $partners
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

        // Notify Partner if the candidate belongs to one
        if ($application->candidate && $application->candidate->partner) {
            $application->candidate->partner->notify(new ApplicationApprovedByAdmin($application));
        }

        return redirect()->back()->with('success', 'Application approved and forwarded to client.');
    }

    /**
     * Reject a job application.
     */
    public function rejectApplication(JobApplication $application)
    {
        $application->update(['status' => 'Rejected']);

        // Notify Partner if the candidate belongs to one
        if ($application->candidate && $application->candidate->partner) {
            $application->candidate->partner->notify(new ApplicationRejectedByAdmin($application));
        }

        return redirect()->back()->with('success', 'Application rejected.');
    }

    /**
     * Show pending jobs.
     */
    public function pendingJobs()
    {
        $pendingJobs = Job::where('status', 'pending_approval')
                          ->with('user')
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

        // Notify the Client who posted the job
        if ($job->user) {
            $job->user->notify(new JobApproved($job));
        }

        return redirect()->back()->with('success', 'Job has been approved and is now live.');
    }

    /**
     * Reject a job posting.
     */
    public function rejectJob(Job $job)
    {
        $job->update(['status' => 'rejected']);

        // Notify the Client who posted the job
        if ($job->user) {
            $job->user->notify(new JobRejected($job));
        }

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
     * Show the billing report for selected candidates.
     */
    public function billingReport()
    {
        $placements = JobApplication::where('hiring_status', 'Selected')
                                    ->with(['job.user', 'candidate', 'candidateUser'])
                                    ->get();

        $reportData = [];

        foreach ($placements as $app) {
            // Skip if data is incomplete
            if (empty($app->joining_date) || empty($app->job->user) || empty($app->job->user->billable_period_days)) {
                continue;
            }

            $client = $app->job->user;
            $joiningDate = Carbon::parse($app->joining_date);
            $billableDays = $client->billable_period_days;
            $invoiceDate = $joiningDate->copy()->addDays($billableDays);

            // Logic: It is "Due" if the invoice date has passed and it hasn't been paid yet.
            $isDue = $invoiceDate->isPast();
            
            // Determine display status
            $statusLabel = 'Pending Maturity';
            $rowClass = '';

            if ($app->payment_status === 'paid') {
                $statusLabel = 'Paid';
                $rowClass = 'bg-green-50'; // Visual cue for Paid
            } elseif ($isDue) {
                $statusLabel = 'Due / Billable';
                $rowClass = 'bg-red-50'; // Visual cue for Overdue/Due
            }

            $reportData[] = (object) [
                'id' => $app->id,
                'candidate_name' => $app->candidate_name, // Relies on the Accessor in JobApplication model
                'client_name' => $client->name,
                'job_title' => $app->job->title,
                'joining_date' => $app->joining_date->format('M d, Y'),
                'billable_period' => $billableDays . ' days',
                'invoice_date' => $invoiceDate->format('M d, Y'),
                'payment_status' => $app->payment_status,
                'paid_at' => $app->paid_at ? $app->paid_at->format('M d, Y') : '-',
                'status_label' => $statusLabel,
                'row_class' => $rowClass,
                'is_due' => $isDue,
            ];
        }

        // Sort: Unpaid & Due first (1), then Pending (2), then Paid (3)
        $reportData = collect($reportData)->sortBy(function($item) {
            if ($item->payment_status === 'paid') return 3;
            if ($item->is_due) return 1;
            return 2;
        });

        return view('admin.billing.index', [
            'placements' => $reportData
        ]);
    }

    /**
     * Mark an application as Paid.
     */
    public function markAsPaid(JobApplication $application)
    {
        $application->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Invoice marked as PAID.');
    }

    /**
     * Show a report of all jobs with their lined-up partners and candidates.
     */
    public function jobReport()
    {
        $jobs = Job::with([
                'user',
                'jobApplications.candidate.partner',
                'jobApplications.candidateUser'
            ])
            ->latest()
            ->get();

        return view('admin.reports.jobs', ['jobs' => $jobs]);
    }
}