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
use App\Models\ClientProfile;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Notifications\JobApproved;
use App\Notifications\JobRejected;
use App\Notifications\ApplicationApprovedByAdmin;
use App\Notifications\ApplicationRejectedByAdmin;
use App\Notifications\ClientJobApprovedForAdmin;
use App\Services\SuperadminActivityService;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard with stats.
     */
    public function index(SuperadminActivityService $activityService)
    {
        $activityService->checkBillingDueAlerts();

        $totalUsers = User::count();
        $totalClients = User::role('client')->count();
        $totalPartners = User::role('partner')->count();
        $pendingJobs = Job::where('status', 'pending_approval')->count();
        $pendingApplications = JobApplication::where('status', 'Pending Review')->count();

        // --- Daily Pulse Data ---
        $todayInterviews = JobApplication::whereDate('interview_at', Carbon::today())->count();

        $dueInvoicesCount = 0;
        $unpaidHires = JobApplication::where('hiring_status', 'Selected')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('joining_date')
            ->with('job.user')
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
            'todayInterviews' => $todayInterviews,
            'dueInvoicesCount' => $dueInvoicesCount
        ]);
    }

    public function dailySchedule()
    {
        $todayInterviews = JobApplication::whereDate('interview_at', Carbon::today())
            ->with(['job', 'candidate', 'candidateUser', 'job.user'])
            ->orderBy('interview_at', 'asc')
            ->get();

        return view('admin.daily_interviews', compact('todayInterviews'));
    }

    // --- CANDIDATE (USER) MANAGEMENT ---

    public function listUsers(Request $request)
    {
        // Load candidate users with their real profile relation (user_profiles table)
        $query = User::role('candidate')->with('profile');

        // 2. Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // 3. Status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->latest()->paginate(25)->withQueryString();

        // Backward compatibility for existing admin blade files that still read $user->candidate
        $users->getCollection()->transform(function ($user) {
            $profile = $user->profile;
            if ($profile) {
                $profile->setAttribute('mobile', $profile->phone_number);
                $profile->setAttribute('dob', $profile->date_of_birth);
            }
            $user->setRelation('candidate', $profile);
            return $user;
        });
        
        // 4. Correct Stats for the View
        $counts = [
            'total' => User::role('candidate')->count(),
            'active' => User::role('candidate')->where('status', 'active')->count(),
            'restricted' => User::role('candidate')->where('status', 'restricted')->count(),
        ];

        return view('admin.users.index', ['users' => $users, 'counts' => $counts]);
    }

    public function showUser(User $user)
    {
        if (!$user->hasRole('candidate')) abort(404);
        
        $user->load('profile');

        // Backward compatibility for existing admin blade file that reads $user->candidate
        $profile = $user->profile;
        if ($profile) {
            $profile->setAttribute('mobile', $profile->phone_number);
            $profile->setAttribute('dob', $profile->date_of_birth);
        }
        $user->setRelation('candidate', $profile);

        return view('admin.users.show', compact('user'));
    }

    public function updateUserStatus(Request $request, User $user)
    {
        if ($user->hasRole('Superadmin')) {
            return redirect()->back()->with('error', 'Cannot change Superadmin status.');
        }
        $validated = $request->validate(['status' => 'required|in:active,pending,on_hold,restricted']);
        $user->update(['status' => $validated['status']]);
        return redirect()->back()->with('success', "User status updated to {$validated['status']}.");
    }

    public function updateUserCredentials(Request $request, User $user)
    {
        if ($user->hasRole('Superadmin') && auth()->id() !== $user->id) {
             return redirect()->back()->with('error', 'Cannot change Superadmin credentials.');
        }
        $validated = $request->validate(['password' => ['required', 'confirmed', Rules\Password::defaults()]]);
        $user->update(['password' => Hash::make($validated['password'])]);
        return redirect()->back()->with('success', 'User credentials updated successfully.');
    }

    // --- CLIENT MANAGEMENT ---

    public function listClients(Request $request)
    {
        $query = User::role('client')->with('roles')->withCount('jobs');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest': $query->oldest(); break;
            case 'name_asc': $query->orderBy('name', 'asc'); break;
            case 'name_desc': $query->orderBy('name', 'desc'); break;
            case 'most_jobs': $query->orderBy('jobs_count', 'desc'); break;
            default: $query->latest(); break;
        }

        $clients = $query->paginate(25)->withQueryString();
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
        
        ClientProfile::create(['user_id' => $user->id]);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function editClient(User $user)
    {
        if (!$user->hasRole('client')) abort(404);
        $user->load('clientProfile');
        return view('admin.clients.edit', ['user' => $user]);
    }

    public function updateClient(Request $request, User $user)
    {
        if (!$user->hasRole('client')) abort(404);

        $validated = $request->validate([
            'billable_period_days' => 'required|integer|min:1',
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'tan_number' => 'nullable|string|max:50',
            'coi_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'pan_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'tan_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'coi_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'other_docs.*' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120', 
        ]);

        $user->update(['billable_period_days' => $validated['billable_period_days']]);

        $profileData = $request->only([
            'company_name', 'website', 'industry', 'company_size', 'description',
            'contact_person_name', 'contact_phone', 'address', 'city', 'state', 'pincode',
            'gst_number', 'pan_number', 'tan_number', 'coi_number'
        ]);

        if ($request->hasFile('logo')) $profileData['logo_path'] = $request->file('logo')->store('client_logos', 'public');
        if ($request->hasFile('pan_file')) $profileData['pan_file_path'] = $request->file('pan_file')->store('client_docs', 'public');
        if ($request->hasFile('tan_file')) $profileData['tan_file_path'] = $request->file('tan_file')->store('client_docs', 'public');
        if ($request->hasFile('coi_file')) $profileData['coi_file_path'] = $request->file('coi_file')->store('client_docs', 'public');

        if ($request->hasFile('other_docs')) {
            $existingDocs = $user->clientProfile->other_docs ?? [];
            $newDocs = [];
            foreach ($request->file('other_docs') as $file) {
                $newDocs[] = $file->store('client_docs/others', 'public');
            }
            $profileData['other_docs'] = array_merge($existingDocs, $newDocs);
        }

        $user->clientProfile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->route('admin.clients.index')->with('success', 'Client profile updated successfully!');
    }

    public function showClient(User $user)
    {
        if (!$user->hasRole('client')) abort(404);
        $jobs = \App\Models\Job::where('user_id', $user->id)->with(['category'])->latest()->paginate(10);
        $totalJobs = \App\Models\Job::where('user_id', $user->id)->count();
        $activeJobs = \App\Models\Job::where('user_id', $user->id)->where('status', 'approved')->count();
        $totalHires = \App\Models\JobApplication::whereHas('job', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereIn('hiring_status', ['Joined', 'Selected'])->count();

        return view('admin.clients.show', compact('user', 'jobs', 'totalJobs', 'activeJobs', 'totalHires'));
    }

    // --- PARTNER MANAGEMENT ---

    public function listPartners(Request $request)
    {
        $query = User::role('partner')->with('partnerProfile');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->whereHas('partnerProfile', function($q) use ($request) {
                $q->where('company_type', $request->input('type'));
            });
        }

        $partners = $query->latest()->paginate(25)->withQueryString();
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

    public function editPartner(User $user)
    {
        if (!$user->hasRole('partner')) abort(404);
        $user->load('partnerProfile');
        return view('admin.partners.edit', ['user' => $user, 'profile' => $user->partnerProfile]);
    }

    public function updatePartner(Request $request, User $user)
    {
        if (!$user->hasRole('partner')) abort(404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'company_type' => 'nullable|string',
            'website' => 'nullable|url',
            'establishment_year' => 'nullable|integer',
            'bio' => 'nullable|string',
            'address' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'beneficiary_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_type' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_name' => 'nullable|string', 
            'pan_number' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048',
            'cancelled_cheque' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'pan_card' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'gst_certificate' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user->update(['name' => $validated['name'], 'email' => $validated['email']]);

        $profileData = $request->only([
            'company_type', 'website', 'establishment_year', 'bio', 'address',
            'linkedin_url', 'facebook_url', 'twitter_url', 'instagram_url',
            'beneficiary_name', 'account_number', 'account_type', 'ifsc_code',
            'pan_name', 'pan_number', 'gst_number'
        ]);

        if ($request->hasFile('profile_picture')) {
            $profileData['profile_picture_path'] = $request->file('profile_picture')->store('partner_profiles/photos', 'public');
        }
        if ($request->hasFile('cancelled_cheque')) {
            $profileData['cancelled_cheque_path'] = $request->file('cancelled_cheque')->store('partner_profiles/docs', 'public');
        }
        if ($request->hasFile('pan_card')) {
            $profileData['pan_card_path'] = $request->file('pan_card')->store('partner_profiles/docs', 'public');
        }
        if ($request->hasFile('gst_certificate')) {
            $profileData['gst_certificate_path'] = $request->file('gst_certificate')->store('partner_profiles/docs', 'public');
        }

        $user->partnerProfile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->route('admin.partners.index')->with('success', 'Partner profile updated successfully.');
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
            'max_experience' => 'required|integer|gte:min_experience|max:50',
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
        } elseif ($request->filled('company_name')) {
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

    public function showJob(Job $job)
    {
        $job->load(['user', 'experienceLevel', 'educationLevel', 'category']);
        return view('admin.jobs.show', compact('job'));
    }

    public function pendingJobs()
    {
        $pendingJobs = Job::where('status', 'pending_approval')->with(['user', 'educationLevel'])->latest()->paginate(20);
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
        $this->sendJobApprovedNotifications($job);
        return redirect()->back()->with('success', 'Job has been approved and is now live.');
    }

    public function rejectJob(Job $job)
    {
        $job->update(['status' => 'rejected']);
        if ($job->user) $job->user->notify(new JobRejected($job));
        return redirect()->back()->with('success', 'Job has been rejected.');
    }

    public function updateJobStatus(Request $request, Job $job)
    {
        $request->validate(['status' => 'required|in:approved,on_hold,closed,rejected']);
        $wasApproved = $job->status === 'approved';
        $job->update(['status' => $request->status]);

        if ($request->status === 'approved' && !$wasApproved) {
            $this->sendJobApprovedNotifications($job);
        }

        return redirect()->back()->with('success', "Job status updated to {$request->status}.");
    }

    private function sendJobApprovedNotifications(Job $job): void
    {
        if ($job->user) {
            $job->user->notify(new JobApproved($job));
        }

        $actorName = auth()->user()?->name;
        $superadmins = User::role('Superadmin')->get();
        foreach ($superadmins as $superadmin) {
            $superadmin->notify(new ClientJobApprovedForAdmin($job, $actorName));
        }
    }

    public function destroyJob(Job $job)
    {
        $job->delete();
        return redirect()->route('admin.jobs.pending')->with('success', 'Job deleted permanently.');
    }

    public function manageJobExclusions(Job $job)
    {
        $job->load(['educationLevel']);
        $partners = User::role('partner')->get();
        $excludedPartnerIds = $job->excludedPartners()->pluck('users.id')->toArray();
        return view('admin.jobs.manage', ['job' => $job, 'allPartners' => $partners, 'excludedPartnerIds' => $excludedPartnerIds]);
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
                })->orWhereHas('job', function($subQ) use ($search) {
                    $subQ->where('title', 'like', "%{$search}%");
                });
            });
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($request->filled('job_id')) $query->where('job_id', $request->input('job_id'));
        if ($request->filled('partner_id')) {
             $query->whereHas('candidate', function($q) use ($request) {
                $q->where('partner_id', $request->input('partner_id'));
             });
        }

        $applications = $query->latest()->paginate(20)->withQueryString();
        $jobs = Job::select('id', 'title')->orderBy('title')->get();
        $partners = User::role('partner')->select('id', 'name')->orderBy('name')->get();

        return view('admin.applications.index', ['applications' => $applications, 'jobs' => $jobs, 'partners' => $partners]);
    }

    public function approveApplication(JobApplication $application)
    {
        $application->update(['status' => 'Approved']);
        if ($application->candidate && $application->candidate->partner) {
            $application->candidate->partner->notify(new ApplicationApprovedByAdmin($application));
        }
        return redirect()->back()->with('success', 'Application approved.');
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
        $applications = $job->jobApplications()->with(['candidate', 'candidate.partner'])->latest()->paginate(20);
        return view('admin.reports.job_applicants', compact('job', 'applications'));
    }

    public function exportJobApplicantsReport(\App\Models\Job $job)
    {
        $applications = $job->jobApplications()
            ->with(['candidate', 'candidate.partner'])
            ->latest()
            ->get();

        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $job->title ?? 'job');
        $fileName = 'job_applicants_' . $safeTitle . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($applications) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Candidate Name',
                'Email',
                'Phone',
                'Source Partner',
                'Admin Status',
                'Client Stage',
                'Applied Date',
                'Interview Date',
                'Joining Date',
            ]);

            foreach ($applications as $application) {
                $candidate = $application->candidate;
                $partnerName = $candidate && $candidate->partner ? $candidate->partner->name : 'Direct';

                fputcsv($handle, [
                    trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? '')),
                    $candidate->email ?? '',
                    $candidate->phone_number ?? '',
                    $partnerName,
                    $application->status ?? '',
                    $application->hiring_status ?? '',
                    optional($application->created_at)->format('Y-m-d H:i:s'),
                    optional($application->interview_at)->format('Y-m-d H:i:s'),
                    optional($application->joining_date)->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // --- BILLING ---

    public function billingReport()
    {
        $placements = JobApplication::where('hiring_status', 'Selected')
                                    ->with(['job.user', 'candidate', 'candidateUser'])
                                    ->get();
        $reportData = [];
        foreach ($placements as $app) {
            if (empty($app->joining_date) || empty($app->job->user)) continue;

            $client = $app->job->user;
            $joiningDate = Carbon::parse($app->joining_date);
            $billableDays = $client->billable_period_days ?? 30;
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
                'payout_amount' => $app->job->payout_amount ?? 0, 
            ];
        }

        $reportData = collect($reportData)->sortBy(function($item) {
            if ($item->payment_status === 'paid') return 3;
            if ($item->is_due) return 1;
            return 2;
        });

        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 20;
        $currentItems = $reportData->slice(($currentPage - 1) * $perPage, $perPage)->all();
        $paginatedItems = new \Illuminate\Pagination\LengthAwarePaginator($currentItems, count($reportData), $perPage, $currentPage, [
            'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()
        ]);

        return view('admin.billing.index', ['placements' => $paginatedItems]);
    }

    public function markAsPaid(JobApplication $application)
    {
        $application->update(['payment_status' => 'paid', 'paid_at' => now()]);
        return redirect()->back()->with('success', 'Invoice marked as PAID.');
    }

    public function jobReport(Request $request)
    {
        $query = Job::with(['user', 'jobApplications.candidate.partner', 'jobApplications.candidateUser'])->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('client_id')) $query->where('user_id', $request->client_id);

        $jobs = $query->paginate(20)->appends($request->query());
        $clients = User::role('client')->orderBy('name')->get();

        return view('admin.reports.jobs', ['jobs' => $jobs, 'clients' => $clients]);
    }

    public function exportJobReport(Request $request)
    {
        $query = Job::with(['user', 'jobApplications'])->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
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

        $jobs = $query->get();
        $fileName = 'master_job_report_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($jobs) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Job ID',
                'Job Title',
                'Company',
                'Client',
                'Status',
                'Applicants',
                'Joined',
                'Posted Date',
            ]);

            foreach ($jobs as $job) {
                $applicationsCount = $job->jobApplications->count();
                $joinedCount = $job->jobApplications->where('joined_status', 'Joined')->count();

                fputcsv($handle, [
                    $job->id,
                    $job->title,
                    $job->company_name,
                    optional($job->user)->name ?? '',
                    $job->status,
                    $applicationsCount,
                    $joinedCount,
                    optional($job->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
