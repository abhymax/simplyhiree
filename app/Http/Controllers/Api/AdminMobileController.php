<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\UserProfile;
use App\Notifications\ApplicationApprovedByAdmin;
use App\Notifications\ApplicationRejectedByAdmin;
use App\Notifications\ClientJobApprovedForAdmin;
use App\Notifications\JobApproved;
use App\Notifications\JobRejected;
use App\Services\SuperadminActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;

class AdminMobileController extends Controller
{
    private function adminUser(Request $request): ?User
    {
        $user = $request->user();
        if (!$user || !($user->hasRole('Superadmin') || $user->hasRole('Manager'))) {
            return null;
        }

        return $user;
    }

    private function adminOnlyResponse()
    {
        return response()->json(['message' => 'Only admin users can access this endpoint.'], 403);
    }

    private function ensureCanManageManagers(User $admin)
    {
        if ($admin->hasRole('Superadmin') || $admin->can('manage_sub_admins')) {
            return null;
        }

        return response()->json(['message' => 'You are not allowed to manage managers.'], 403);
    }

    private function publicFileUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    private function paginateCollection(Collection $items, Request $request): LengthAwarePaginator
    {
        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $currentPage = max((int) $request->input('page', 1), 1);
        $items = $items->values();
        $pageItems = $items->forPage($currentPage, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $items->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    private function mapApplication(JobApplication $application): array
    {
        $agencyCandidateName = null;
        if ($application->candidate) {
            $first = trim((string) $application->candidate->first_name);
            $last = trim((string) $application->candidate->last_name);
            $agencyCandidateName = trim("{$first} {$last}");
            if ($agencyCandidateName === '') {
                $agencyCandidateName = null;
            }
        }

        $candidateName = $agencyCandidateName
            ?? $application->candidateUser?->name
            ?? 'Unknown Candidate';
        $candidateEmail = $application->candidate?->email
            ?? $application->candidateUser?->email;
        $candidatePhone = $application->candidate?->phone_number
            ?? $application->candidateUser?->profile?->phone_number;
        $partnerName = $application->candidate?->partner?->name;
        $resumePath = $application->candidate?->resume_path
            ?? $application->candidateUser?->profile?->resume_path;

        return [
            'id' => $application->id,
            'application_code' => (string) ($application->application_code ?? ''),
            'hiring_code' => (string) ($application->hiring_code ?? ''),
            'status' => (string) ($application->status ?? ''),
            'hiring_status' => (string) ($application->hiring_status ?? ''),
            'joined_status' => (string) ($application->joined_status ?? ''),
            'candidate_name' => $candidateName,
            'candidate_code' => (string) ($application->candidate?->candidate_code ?? $application->candidateUser?->entity_code ?? ''),
            'candidate_email' => $candidateEmail,
            'candidate_phone' => $candidatePhone,
            'candidate_skills' => $application->candidate?->skills
                ?? $application->candidateUser?->profile?->skills,
            'candidate_experience' => $application->candidate?->experience_status
                ?? $application->candidateUser?->profile?->experience_status,
            'candidate_education' => $application->candidate?->education_level
                ?? $application->candidateUser?->profile?->education_level,
            'candidate_ctc' => $application->candidate?->expected_ctc
                ?? $application->candidateUser?->profile?->expected_ctc,
            'candidate_location' => $application->candidate?->location
                ?? $application->candidateUser?->profile?->location,
            'candidate_gender' => $application->candidate?->gender
                ?? $application->candidateUser?->profile?->gender,
            'candidate_dob' => (string) ($application->candidate?->date_of_birth
                ?? $application->candidateUser?->profile?->date_of_birth
                ?? ''),
            'resume_url' => $this->publicFileUrl($resumePath),
            'job_id' => $application->job?->id,
            'job_code' => (string) ($application->job?->job_code ?? ''),
            'job_title' => $application->job?->title,
            'company_name' => $application->job?->company_name,
            'source_type' => $partnerName ? 'partner' : 'direct',
            'partner_name' => $partnerName,
            'partner_code' => (string) ($application->candidate?->partner?->entity_code ?? ''),
            'interview_at' => optional($application->interview_at)->toIso8601String(),
            'joining_date' => optional($application->joining_date)->toIso8601String(),
            'left_at' => optional($application->left_at)->toIso8601String(),
            'created_at' => optional($application->created_at)->toIso8601String(),
            'can_review' => strtolower((string) $application->status) === 'pending review',
        ];
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

    public function dashboard(Request $request, SuperadminActivityService $activityService)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $activityService->checkBillingDueAlerts();

        $totalUsers = (int) User::count();
        $totalClients = (int) User::role('client')->count();
        $totalPartners = (int) User::role('partner')->count();
        $totalManagers = (int) User::role('Manager')->count();
        $totalJobs = (int) Job::count();
        $pendingJobs = (int) Job::where('status', 'pending_approval')->count();
        $pendingApplications = (int) JobApplication::where('status', 'Pending Review')->count();
        $todayInterviews = (int) JobApplication::whereDate('interview_at', Carbon::today())->count();

        $dueInvoicesCount = 0;
        $unpaidHires = JobApplication::where('hiring_status', 'Selected')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('joining_date')
            ->with('job.user')
            ->get();

        foreach ($unpaidHires as $hire) {
            if (!$hire->job || !$hire->job->user) {
                continue;
            }

            $billableDays = (int) ($hire->job->user->billable_period_days ?? 30);
            $invoiceDate = Carbon::parse($hire->joining_date)->addDays($billableDays);
            if ($invoiceDate->isPast() || $invoiceDate->isToday()) {
                $dueInvoicesCount++;
            }
        }

        return response()->json([
            'summary' => [
                'total_users' => $totalUsers,
                'total_clients' => $totalClients,
                'total_partners' => $totalPartners,
                'total_managers' => $totalManagers,
                'total_jobs' => $totalJobs,
                'pending_jobs' => $pendingJobs,
                'pending_applications' => $pendingApplications,
                'today_interviews' => $todayInterviews,
                'due_invoices' => $dueInvoicesCount,
            ],
        ]);
    }

    public function clients(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $query = User::role('client')
            ->with(['clientProfile', 'profile'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('clientProfile', function ($profileQuery) use ($search) {
                        $profileQuery
                            ->where('company_name', 'like', "%{$search}%")
                            ->orWhere('contact_person_name', 'like', "%{$search}%")
                            ->orWhere('contact_phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $clients = $query->paginate($perPage)->appends($request->query());

        $rows = $clients->getCollection()->map(function (User $client) {
            $profile = $client->clientProfile;

            return [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'status' => $client->status,
                'company_name' => $profile?->company_name ?: $client->name,
                'industry' => $profile?->industry,
                'company_size' => $profile?->company_size,
                'contact_person_name' => $profile?->contact_person_name,
                'phone' => $profile?->contact_phone ?: $client->profile?->phone_number,
                'city' => $profile?->city,
                'state' => $profile?->state,
                'created_at' => optional($client->created_at)->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
            ],
        ]);
    }

    public function storeClient(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        if (!($admin->hasRole('Superadmin') || $admin->can('manage_clients'))) {
            return response()->json(['message' => 'You are not allowed to create client accounts.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'billable_period_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'company_name' => ['nullable', 'string', 'max:255'],
        ]);

        $client = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'billable_period_days' => (int) ($validated['billable_period_days'] ?? 30),
            'status' => 'active',
        ]);

        $client->assignRole('client');

        UserProfile::create([
            'user_id' => $client->id,
            'phone_number' => $validated['phone_number'],
        ]);

        $client->clientProfile()->create([
            'company_name' => $validated['company_name'] ?? $validated['name'],
        ]);

        return response()->json([
            'message' => 'Client created successfully.',
            'data' => [
                'id' => $client->id,
                'name' => $client->name,
                'email' => $client->email,
                'status' => $client->status,
                'company_name' => $client->clientProfile?->company_name ?? $client->name,
                'phone' => $validated['phone_number'],
                'billable_period_days' => $client->billable_period_days,
            ],
        ], 201);
    }

    public function activityLogs(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $perPage = max(min((int) $request->input('per_page', 20), 100), 1);

        $query = AdminActivityLog::query()->latest('occurred_at');

        if ($request->filled('event_key')) {
            $query->where('event_key', 'like', '%' . $request->input('event_key') . '%');
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('actor_name', 'like', "%{$search}%");
            });
        }

        $logs = $query->paginate($perPage)->appends($request->query());

        $data = $logs->getCollection()->map(function (AdminActivityLog $log) {
            return [
                'id' => $log->id,
                'event_key' => $log->event_key,
                'title' => $log->title,
                'message' => $log->message,
                'icon' => $log->icon,
                'actor_id' => $log->actor_id,
                'actor_name' => $log->actor_name,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'metadata' => $log->metadata,
                'whatsapp_status' => $log->whatsapp_status,
                'whatsapp_last_error' => $log->whatsapp_last_error,
                'occurred_at' => optional($log->occurred_at)->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }

    public function pendingJobs(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $query = Job::query()
            ->where('status', 'pending_approval')
            ->with(['user', 'educationLevel', 'category'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $jobs = $query->paginate($perPage)->appends($request->query());

        $rows = $jobs->getCollection()->map(function (Job $job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'location' => $job->location,
                'salary' => $job->salary,
                'job_type' => $job->job_type,
                'category' => $job->category?->name,
                'education_level' => $job->educationLevel?->name,
                'description' => $job->description,
                'openings' => $job->openings,
                'min_experience' => $job->min_experience,
                'max_experience' => $job->max_experience,
                'requested_by' => $job->user?->name,
                'requested_by_email' => $job->user?->email,
                'created_at' => optional($job->created_at)->toIso8601String(),
            ];
        })->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ],
        ]);
    }

    public function approveJob(Request $request, Job $job)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

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

        return response()->json(['message' => 'Job approved successfully.']);
    }

    public function rejectJob(Request $request, Job $job)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $job->update(['status' => 'rejected']);
        if ($job->user) {
            $job->user->notify(new JobRejected($job));
        }

        return response()->json(['message' => 'Job rejected successfully.']);
    }

    public function pendingApplications(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $applications = JobApplication::query()
            ->with(['job', 'candidate', 'candidateUser', 'candidateUser.profile', 'candidate.partner'])
            ->where('status', 'Pending Review')
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $rows = $applications->getCollection()
            ->map(fn (JobApplication $application) => $this->mapApplication($application))
            ->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    public function approveApplication(Request $request, JobApplication $application)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $application->loadMissing(['job', 'candidate.partner', 'candidateUser']);
        $application->update(['status' => 'Approved']);
        $this->notifyApplicationStakeholder($application, true);

        return response()->json(['message' => 'Application approved successfully.']);
    }

    public function rejectApplication(Request $request, JobApplication $application)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $application->loadMissing(['job', 'candidate.partner', 'candidateUser']);
        $application->update(['status' => 'Rejected']);
        $this->notifyApplicationStakeholder($application, false);

        return response()->json(['message' => 'Application rejected successfully.']);
    }

    private function notifyApplicationStakeholder(JobApplication $application, bool $approved): void
    {
        $notification = $approved
            ? new ApplicationApprovedByAdmin($application)
            : new ApplicationRejectedByAdmin($application);

        $partner = $application->candidate?->partner;
        if ($partner) {
            $partner->notify($notification);
            return;
        }

        if ($application->candidateUser) {
            $application->candidateUser->notify($notification);
        }
    }

    public function showApplication(Request $request, JobApplication $application)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $application->load(['job', 'candidate', 'candidate.partner', 'candidateUser', 'candidateUser.profile']);

        return response()->json([
            'data' => $this->mapApplication($application),
        ]);
    }

    public function applications(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);

        $query = JobApplication::query()
            ->with(['job', 'candidate', 'candidate.partner', 'candidateUser', 'candidateUser.profile']);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->whereHas('candidate', function ($candidateQ) use ($search) {
                    $candidateQ->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhereHas('candidateUser', function ($userQ) use ($search) {
                        $userQ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHas('job', function ($jobQ) use ($search) {
                        $jobQ->where('title', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', (int) $request->input('job_id'));
        }

        if ($request->filled('partner_id')) {
            $partnerId = (int) $request->input('partner_id');
            $query->whereHas('candidate', function ($candidateQ) use ($partnerId) {
                $candidateQ->where('partner_id', $partnerId);
            });
        }

        $applications = $query->latest()->paginate($perPage)->appends($request->query());
        $rows = $applications->getCollection()
            ->map(fn (JobApplication $application) => $this->mapApplication($application))
            ->values();

        $jobs = Job::query()
            ->select(['id', 'title'])
            ->orderBy('title')
            ->get()
            ->map(fn (Job $job) => [
                'id' => $job->id,
                'title' => $job->title,
            ])
            ->values();

        $partners = User::role('partner')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $partner) => [
                'id' => $partner->id,
                'name' => $partner->name,
            ])
            ->values();

        return response()->json([
            'data' => $rows,
            'filters' => [
                'jobs' => $jobs,
                'partners' => $partners,
            ],
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    public function billing(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $search = trim((string) $request->input('search', ''));
        $status = strtolower(trim((string) $request->input('status', '')));
        $startDate = trim((string) $request->input('start_date', ''));
        $endDate = trim((string) $request->input('end_date', ''));

        $placements = JobApplication::query()
            ->where('hiring_status', 'Selected')
            ->with(['job.user', 'candidate', 'candidateUser'])
            ->get();

        $rows = collect();
        foreach ($placements as $application) {
            if (!$application->job || !$application->job->user || !$application->joining_date) {
                continue;
            }

            $client = $application->job->user;
            $joiningDate = Carbon::parse($application->joining_date);
            $billableDays = (int) ($client->billable_period_days ?? 30);
            $invoiceDate = $joiningDate->copy()->addDays($billableDays);
            $isDue = $invoiceDate->isPast() || $invoiceDate->isToday();

            $statusLabel = 'Pending Maturity';
            if ($application->payment_status === 'paid') {
                $statusLabel = 'Paid';
            } elseif ($isDue) {
                $statusLabel = 'Overdue';
            }

            $candidateName = $application->candidate_name;
            $row = [
                'id' => $application->id,
                'invoice_number' => 'INV-' . $application->id,
                'candidate_name' => $candidateName,
                'client_name' => (string) $client->name,
                'job_title' => (string) ($application->job->title ?? ''),
                'joining_date' => $joiningDate->format('Y-m-d'),
                'invoice_date' => $invoiceDate->format('Y-m-d'),
                'billable_period' => "{$billableDays} days",
                'amount' => (float) ($application->job->payout_amount ?? 0),
                'amount_formatted' => '₹' . number_format((float) ($application->job->payout_amount ?? 0)),
                'payment_status' => (string) ($application->payment_status ?? 'pending'),
                'status_label' => $statusLabel,
                'paid_at' => optional($application->paid_at)->toIso8601String(),
                'is_due' => $isDue,
                'can_mark_paid' => $application->payment_status !== 'paid' && $isDue,
            ];

            if ($search !== '') {
                $haystack = strtolower(implode(' ', [
                    $row['invoice_number'],
                    $row['candidate_name'],
                    $row['client_name'],
                    $row['job_title'],
                ]));
                if (!str_contains($haystack, strtolower($search))) {
                    continue;
                }
            }

            if ($status === 'paid' && $application->payment_status !== 'paid') {
                continue;
            }
            if ($status === 'pending' && ($application->payment_status === 'paid' || $isDue)) {
                continue;
            }
            if ($status === 'overdue' && ($application->payment_status === 'paid' || !$isDue)) {
                continue;
            }

            if ($startDate !== '' && $invoiceDate->lt(Carbon::parse($startDate)->startOfDay())) {
                continue;
            }
            if ($endDate !== '' && $invoiceDate->gt(Carbon::parse($endDate)->endOfDay())) {
                continue;
            }

            $rows->push($row);
        }

        $rows = $rows->sortBy(function (array $row) {
            if ($row['payment_status'] === 'paid') {
                return 3;
            }

            return $row['is_due'] ? 1 : 2;
        })->values();

        $totalRevenue = (float) $rows
            ->where('payment_status', 'paid')
            ->sum('amount');
        $pendingAmount = (float) $rows
            ->where('payment_status', '!=', 'paid')
            ->sum('amount');
        $dueCount = $rows
            ->where('payment_status', '!=', 'paid')
            ->where('is_due', true)
            ->count();

        $paginator = $this->paginateCollection($rows, $request);
        $currentItems = $paginator->getCollection()->values();

        return response()->json([
            'data' => $currentItems,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_revenue_formatted' => '₹' . number_format($totalRevenue),
                'pending_amount' => $pendingAmount,
                'pending_amount_formatted' => '₹' . number_format($pendingAmount),
                'due_count' => $dueCount,
                'total_records' => $rows->count(),
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function markApplicationPaid(Request $request, JobApplication $application)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $application->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json(['message' => 'Invoice marked as paid.']);
    }

    public function jobReports(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $query = Job::query()
            ->with('user')
            ->withCount('jobApplications')
            ->withCount([
                'jobApplications as joined_count' => function ($q) {
                    $q->where('joined_status', 'Joined');
                },
            ])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('client_id')) {
            $query->where('user_id', (int) $request->input('client_id'));
        }

        $jobs = $query->paginate($perPage)->appends($request->query());
        $rows = $jobs->getCollection()->map(function (Job $job) use ($admin) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'client_id' => $job->user?->id,
                'client_name' => $job->user?->name,
                'status' => $job->status,
                'applications_count' => (int) ($job->job_applications_count ?? 0),
                'joined_count' => (int) ($job->joined_count ?? 0),
                'posted_at' => optional($job->created_at)->toIso8601String(),
                'can_delete' => $admin->hasRole('Superadmin'),
            ];
        })->values();

        $clients = User::role('client')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $client) => [
                'id' => $client->id,
                'name' => $client->name,
            ])
            ->values();

        return response()->json([
            'data' => $rows,
            'filters' => [
                'clients' => $clients,
            ],
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ],
        ]);
    }

    public function updateJobStatus(Request $request, Job $job)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,on_hold,closed,rejected',
        ]);

        $wasApproved = $job->status === 'approved';
        $job->update(['status' => $validated['status']]);

        if ($validated['status'] === 'approved' && !$wasApproved) {
            $this->sendJobApprovedNotifications($job);
        }

        return response()->json([
            'message' => "Job status updated to {$validated['status']}.",
            'status' => $job->status,
        ]);
    }

    public function deleteJob(Request $request, Job $job)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        if (!$admin->hasRole('Superadmin')) {
            return response()->json(['message' => 'Only Superadmin can delete jobs.'], 403);
        }

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully.']);
    }

    public function jobApplicants(Request $request, Job $job)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $query = $job->jobApplications()
            ->with(['candidate', 'candidate.partner', 'candidateUser', 'candidateUser.profile'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        if ($request->filled('hiring_status')) {
            $query->where('hiring_status', (string) $request->input('hiring_status'));
        }

        $applications = $query->paginate($perPage)->appends($request->query());
        $rows = $applications->getCollection()
            ->map(fn (JobApplication $application) => $this->mapApplication($application))
            ->values();

        return response()->json([
            'job' => [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
            ],
            'data' => $rows,
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }

    public function managers(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $denied = $this->ensureCanManageManagers($admin);
        if ($denied) {
            return $denied;
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $query = User::role('Manager')
            ->with(['assignedClients:id,name', 'permissions:id,name'])
            ->latest();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $managers = $query->paginate($perPage)->appends($request->query());
        $rows = $managers->getCollection()->map(function (User $manager) {
            return [
                'id' => $manager->id,
                'name' => $manager->name,
                'email' => $manager->email,
                'status' => $manager->status,
                'assigned_clients' => $manager->assignedClients
                    ->map(fn (User $client) => ['id' => $client->id, 'name' => $client->name])
                    ->values(),
                'permissions' => $manager->permissions
                    ->pluck('name')
                    ->values(),
            ];
        })->values();

        return response()->json([
            'data' => $rows,
            'meta' => [
                'current_page' => $managers->currentPage(),
                'last_page' => $managers->lastPage(),
                'per_page' => $managers->perPage(),
                'total' => $managers->total(),
            ],
        ]);
    }

    public function managerMeta(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $denied = $this->ensureCanManageManagers($admin);
        if ($denied) {
            return $denied;
        }

        $permissions = Permission::query()
            ->orderBy('name')
            ->pluck('name')
            ->values();

        $clients = User::role('client')
            ->where('status', 'active')
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $client) => [
                'id' => $client->id,
                'name' => $client->name,
            ])
            ->values();

        return response()->json([
            'permissions' => $permissions,
            'clients' => $clients,
        ]);
    }

    public function storeManager(Request $request)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $denied = $this->ensureCanManageManagers($admin);
        if ($denied) {
            return $denied;
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
            'clients' => ['sometimes', 'array'],
            'clients.*' => ['integer', 'exists:users,id'],
        ]);

        $manager = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);
        $manager->assignRole('Manager');
        $manager->syncPermissions($validated['permissions'] ?? []);
        $manager->assignedClients()->sync($validated['clients'] ?? []);

        return response()->json([
            'message' => 'Manager created successfully.',
            'manager_id' => $manager->id,
        ], 201);
    }

    public function updateManager(Request $request, User $manager)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $denied = $this->ensureCanManageManagers($admin);
        if ($denied) {
            return $denied;
        }

        if (!$manager->hasRole('Manager')) {
            return response()->json(['message' => 'Selected user is not a manager.'], 404);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $manager->id],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string'],
            'clients' => ['sometimes', 'array'],
            'clients.*' => ['integer', 'exists:users,id'],
        ]);

        $manager->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (!empty($validated['password'])) {
            $manager->update(['password' => Hash::make($validated['password'])]);
        }

        $manager->syncPermissions($validated['permissions'] ?? []);
        $manager->assignedClients()->sync($validated['clients'] ?? []);

        return response()->json(['message' => 'Manager updated successfully.']);
    }

    public function updateManagerStatus(Request $request, User $manager)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $denied = $this->ensureCanManageManagers($admin);
        if ($denied) {
            return $denied;
        }

        if (!$manager->hasRole('Manager')) {
            return response()->json(['message' => 'Selected user is not a manager.'], 404);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:active,pending,on_hold,restricted'],
        ]);

        $manager->update(['status' => $validated['status']]);

        return response()->json(['message' => "Manager status updated to {$validated['status']}."]);
    }

    public function updateManagerPassword(Request $request, User $manager)
    {
        $admin = $this->adminUser($request);
        if (!$admin) {
            return $this->adminOnlyResponse();
        }

        $denied = $this->ensureCanManageManagers($admin);
        if ($denied) {
            return $denied;
        }

        if (!$manager->hasRole('Manager')) {
            return response()->json(['message' => 'Selected user is not a manager.'], 404);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $manager->update(['password' => Hash::make($validated['password'])]);

        return response()->json(['message' => 'Manager password updated successfully.']);
    }
}
