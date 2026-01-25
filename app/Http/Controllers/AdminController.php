<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\JobCategory;
use App\Models\ExperienceLevel;
use App\Models\EducationLevel;
use App\Models\Candidate;
use App\Models\PartnerProfile;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
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

        // --- NEW: Daily Pulse Data ---
        
        // 1. Interviews Scheduled Today
        $todayInterviews = JobApplication::whereDate('interview_at', Carbon::today())->count();

        // 2. Invoices Due (Calculation)
        $dueInvoicesCount = 0;
        $unpaidHires = JobApplication::where('hiring_status', 'Selected')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('joining_date')
            ->with('job.user') // Eager load client to get billable days
            ->get();

        foreach ($unpaidHires as $hire) {
            if ($hire->job && $hire->job->user) {
                $billableDays = $hire->job->user->billable_period_days ?? 30;
                $invoiceDate = $hire->joining_date->copy()->addDays($billableDays);
                
                if ($invoiceDate->isPast() || $invoiceDate->isToday()) {
                    $dueInvoicesCount++;
                }
            }
        }

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalClients' => $totalClients,
            'totalPartners' => $totalPartners,
            'pendingJobs' => $pendingJobs,
            'pendingApplications' => $pendingApplications,
            // New Variables
            'todayInterviews' => $todayInterviews,
            'dueInvoicesCount' => $dueInvoicesCount
        ]);
    }

    /**
     * Show interviews scheduled for today (Daily Pulse Detail View).
     */
    public function dailySchedule()
    {
        $todayInterviews = JobApplication::whereDate('interview_at', Carbon::today())
            ->with(['job', 'candidate', 'candidateUser', 'job.user'])
            ->orderBy('interview_at', 'asc')
            ->get();

        return view('admin.daily_interviews', compact('todayInterviews'));
    }

    // --- USER MANAGEMENT & ACCESS CONTROL ---

    public function listUsers()
    {
        $users = User::with('roles')->latest()->paginate(25);
        return view('admin.users.index', ['users' => $users]);
    }

    public function updateUserStatus(Request $request, User $user)
    {
        if ($user->hasRole('Superadmin')) {
            return redirect()->back()->with('error', 'Cannot change Superadmin status.');
        }

        $validated = $request->validate([
            'status' => 'required|in:active,pending,on_hold,restricted',
        ]);

        $user->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "User status updated to {$validated['status']}.");
    }

    public function updateUserCredentials(Request $request, User $user)
    {
        if ($user->hasRole('Superadmin') && auth()->id() !== $user->id) {
             return redirect()->back()->with('error', 'Cannot change Superadmin credentials.');
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->back()->with('success', 'User credentials updated successfully.');
    }

    // --- CLIENT MANAGEMENT ---

    public function listClients()
    {
        $clients = User::role('client')->with('roles')->latest()->paginate(25);
        return view('admin.clients.index', ['users' => $clients]);
    }

    public function createClient()
    {
        return view('admin.clients.create');
    }

    public function storeClient(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'billable_period_days' => ['required', 'integer', 'min:1'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'billable_period_days' => $request->billable_period_days,
            'status' => 'active',
        ]);

        $user->assignRole('client');
        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function editClient(User $user)
    {
        if (!$user->hasRole('client')) abort(404);
        return view('admin.clients.edit', ['user' => $user]);
    }

    public function updateClient(Request $request, User $user)
    {
        if (!$user->hasRole('client')) abort(404);
        $validated = $request->validate(['billable_period_days' => 'required|integer|min:1']);
        $user->update(['billable_period_days' => $validated['billable_period_days']]);
        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully!');
    }

    // --- PARTNER MANAGEMENT ---

    public function listPartners()
    {
        $partners = User::role('partner')->with(['roles', 'partnerProfile'])->latest()->paginate(25);
        return view('admin.partners.index', ['users' => $partners]);
    }

    public function createPartner()
    {
        return view('admin.partners.create');
    }

    public function storePartner(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_type' => ['required', 'string', 'in:Placement Agency,Freelancer,Recruiter'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $user->assignRole('partner');

        PartnerProfile::create([
            'user_id' => $user->id,
            'company_type' => $request->company_type,
        ]);

        return redirect()->route('admin.partners.index')->with('success', 'Partner created successfully.');
    }

    public function showPartner(User $user)
    {
        if (!$user->hasRole('partner')) abort(404);
        $user->load('partnerProfile'); 
        return view('admin.partners.show', ['user' => $user, 'profile' => $user->partnerProfile]);
    }

    // --- JOB MANAGEMENT ---

    public function createJob()
    {
        $clients = User::role('client')->where('status', 'active')->get();
        $partners = User::role('partner')->where('status', 'active')->get();
        $candidates = Candidate::select('id', 'first_name', 'last_name', 'email')->latest()->get(); 
        
        $categories = JobCategory::all();
        $experienceLevels = ExperienceLevel::all();
        $educationLevels = EducationLevel::all();

        return view('admin.jobs.create', compact(
            'clients', 'partners', 'candidates', 
            'categories', 'experienceLevels', 'educationLevels'
        ));
    }

    public function storeJob(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'nullable|exists:users,id',
            'partner_visibility' => 'required|in:all,selected',
            'allowed_partners' => 'array|required_if:partner_visibility,selected',
            'restricted_candidates' => 'array|nullable',
            
            'category_id' => 'required|exists:job_categories,id',
            'location' => 'required|string',
            'salary' => 'nullable|string',
            'job_type' => 'required|string',
            'description' => 'required|string',
           'min_experience' => 'required|integer|min:0',
'max_experience' => 'required|integer|gte:min_experience|max:50', // gte ensures max is >= min
'experience_level_id' => 'nullable', // Make this optional
            'education_level_id' => 'required|exists:education_levels,id',
            'application_deadline' => 'nullable|date',
            'payout_amount' => 'nullable|numeric',
            'minimum_stay_days' => 'nullable|integer',
            
            'skills_required' => 'nullable|string',
            'company_website' => 'nullable|url',
            'openings' => 'nullable|integer',
            'min_age' => 'nullable|integer',
            'max_age' => 'nullable|integer',
            'gender_preference' => 'nullable|string',
        ]);

        $companyName = 'Simplyhiree';
        if ($request->filled('client_id')) {
            $client = User::find($request->client_id);
            $companyName = $client->name;
        }
        if ($request->filled('company_name')) {
            $companyName = $request->company_name;
        }

        $job = Job::create([
            'user_id' => $request->client_id,
            'company_name' => $companyName,
            'status' => 'approved',
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'location' => $validated['location'],
            'salary' => $validated['salary'],
            'job_type' => $validated['job_type'],
            'description' => $validated['description'],
            'min_experience' => $request->min_experience,
    'max_experience' => $request->max_experience,
    'experience_level_id' => null, // We are not using this anymore
            'education_level_id' => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'],
            'payout_amount' => $validated['payout_amount'] ?? 0,
            'minimum_stay_days' => $validated['minimum_stay_days'] ?? 0,
            'partner_visibility' => $validated['partner_visibility'],
            
            'skills_required' => $request->skills_required,
            'company_website' => $request->company_website,
            'openings' => $request->openings,
            'min_age' => $request->min_age,
            'max_age' => $request->max_age,
            'gender_preference' => $request->gender_preference,
        ]);

        if ($validated['partner_visibility'] === 'selected' && $request->has('allowed_partners')) {
            $job->allowedPartners()->sync($request->allowed_partners);
        }

        if ($request->has('restricted_candidates')) {
            $job->restrictedCandidates()->sync($request->restricted_candidates);
        }

        return redirect()->route('admin.jobs.pending')->with('success', 'Job created successfully.');
    }
/**
     * Show details of a specific job (for approval review).
     */
    public function showJob(Job $job)
    {
        $job->load(['user', 'experienceLevel', 'educationLevel', 'category']);
        return view('admin.jobs.show', compact('job'));
    }
    public function pendingJobs()
    {
        $pendingJobs = Job::where('status', 'pending_approval')
                          ->with(['user', 'experienceLevel', 'educationLevel']) 
                          ->latest()
                          ->paginate(20);

        return view('admin.jobs.pending', ['jobs' => $pendingJobs]);
    }

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

        if ($job->user) {
            $job->user->notify(new JobApproved($job));
        }

        return redirect()->back()->with('success', 'Job has been approved and is now live.');
    }

    public function rejectJob(Job $job)
    {
        $job->update(['status' => 'rejected']);
        if ($job->user) {
            $job->user->notify(new JobRejected($job));
        }
        return redirect()->back()->with('success', 'Job has been rejected.');
    }

    public function updateJobStatus(Request $request, Job $job)
    {
        $request->validate([
            'status' => 'required|in:approved,on_hold,closed,rejected',
        ]);

        $job->update(['status' => $request->status]);

        return redirect()->back()->with('success', "Job status updated to {$request->status}.");
    }

    public function destroyJob(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.pending')->with('success', 'Job deleted permanently.');
    }

    public function manageJobExclusions(Job $job)
    {
        $job->load(['experienceLevel', 'educationLevel']);
        $partners = User::role('partner')->get();
        $excludedPartnerIds = $job->excludedPartners()->pluck('users.id')->toArray();

        return view('admin.jobs.manage', [
            'job' => $job,
            'allPartners' => $partners, 
            'excludedPartnerIds' => $excludedPartnerIds
        ]);
    }

    public function updateJobExclusions(Request $request, Job $job)
    {
        $job->excludedPartners()->sync($request->input('excluded_partners', []));
        return redirect()->route('admin.jobs.pending')->with('success', 'Partner exclusions updated successfully.');
    }

    // --- APPLICATION MANAGEMENT ---

    public function listApplications(Request $request)
    {
        $query = JobApplication::with(['job', 'candidate', 'candidateUser', 'candidate.partner']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('candidate', function($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('job', function($subQ) use ($search) {
                    $subQ->where('title', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }
        
        if ($request->filled('partner_id')) {
             $query->whereHas('candidate', function($q) use ($request) {
                $q->where('partner_id', $request->input('partner_id'));
             });
        }

        $applications = $query->latest()->paginate(20)->withQueryString();
        
        $jobs = Job::select('id', 'title')->orderBy('title')->get();
        $partners = User::role('partner')->select('id', 'name')->orderBy('name')->get();

        return view('admin.applications.index', [
            'applications' => $applications,
            'jobs' => $jobs,
            'partners' => $partners
        ]);
    }

    public function approveApplication(JobApplication $application)
    {
        $application->update(['status' => 'Approved']);
        if ($application->candidate && $application->candidate->partner) {
            $application->candidate->partner->notify(new ApplicationApprovedByAdmin($application));
        }
        return redirect()->back()->with('success', 'Application approved and forwarded to client.');
    }

    public function rejectApplication(JobApplication $application)
    {
        $application->update(['status' => 'Rejected']);
        if ($application->candidate && $application->candidate->partner) {
            $application->candidate->partner->notify(new ApplicationRejectedByAdmin($application));
        }
        return redirect()->back()->with('success', 'Application rejected.');
    }

    public function showApplication(JobApplication $application)
    {
        $application->load(['candidate', 'job', 'candidate.partner', 'candidateUser']);
        return view('admin.applications.show', compact('application'));
    }

    public function jobApplicantsReport(\App\Models\Job $job)
    {
        $applications = $job->jobApplications()
            ->with(['candidate', 'candidate.partner'])
            ->latest()
            ->paginate(20);

        return view('admin.reports.job_applicants', compact('job', 'applications'));
    }

    // --- BILLING & REPORTS ---

    public function billingReport()
    {
        $placements = JobApplication::where('hiring_status', 'Selected')
                                    ->with(['job.user', 'candidate', 'candidateUser'])
                                    ->get();

        $reportData = [];

        foreach ($placements as $app) {
            if (empty($app->joining_date) || empty($app->job->user) || empty($app->job->user->billable_period_days)) {
                continue;
            }

            $client = $app->job->user;
            $joiningDate = Carbon::parse($app->joining_date);
            $billableDays = $client->billable_period_days;
            $invoiceDate = $joiningDate->copy()->addDays($billableDays);

            $isDue = $invoiceDate->isPast();
            
            $statusLabel = 'Pending Maturity';
            $rowClass = '';

            if ($app->payment_status === 'paid') {
                $statusLabel = 'Paid';
                $rowClass = 'bg-green-50';
            } elseif ($isDue) {
                $statusLabel = 'Due / Billable';
                $rowClass = 'bg-red-50';
            }

            $reportData[] = (object) [
                'id' => $app->id,
                'candidate_name' => $app->candidate_name, 
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

        $reportData = collect($reportData)->sortBy(function($item) {
            if ($item->payment_status === 'paid') return 3;
            if ($item->is_due) return 1;
            return 2;
        });

        return view('admin.billing.index', ['placements' => $reportData]);
    }

    public function markAsPaid(JobApplication $application)
    {
        $application->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
        return redirect()->back()->with('success', 'Invoice marked as PAID.');
    }

    public function jobReport(Request $request)
    {
        $query = Job::with([
                'user',
                'jobApplications.candidate.partner', 
                'jobApplications.candidateUser'
            ])
            ->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('user_id', $request->client_id);
        }

        $jobs = $query->paginate(20)->appends($request->query());
        $clients = User::role('client')->orderBy('name')->get();

        return view('admin.reports.jobs', [
            'jobs' => $jobs,
            'clients' => $clients
        ]);
    }
}