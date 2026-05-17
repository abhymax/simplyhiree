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
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules;
use App\Notifications\JobApproved;
use App\Notifications\JobRejected;
use App\Notifications\ApplicationApprovedByAdmin;
use App\Notifications\ApplicationRejectedByAdmin;
use App\Notifications\ClientJobApprovedForAdmin;
use App\Services\SuperadminActivityService;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    private function candidateUsersQuery()
    {
        $query = User::query()->where(function ($query) {
            $query->whereHas('roles', function ($roleQuery) {
                $roleQuery->whereRaw('LOWER(name) = ?', ['candidate']);
            });

            // Backward compatibility for legacy role column based users.
            if (Schema::hasColumn('users', 'role')) {
                $query->orWhereRaw('LOWER(role) = ?', ['candidate']);
            }
        });

        // Exclude non-candidate role records even if data is mixed in legacy DB.
        $query->whereDoesntHave('roles', function ($roleQuery) {
            $roleQuery->whereIn('name', ['partner', 'client', 'Superadmin', 'Manager', 'superadmin', 'manager']);
        });

        if (Schema::hasColumn('users', 'role')) {
            $query->where(function ($subQuery) {
                $subQuery->whereNull('role')
                    ->orWhereRaw('LOWER(role) = ?', ['candidate']);
            });
        }

        return $query;
    }

    private function isStrictCandidateUser(User $user): bool
    {
        $roleNames = $user->getRoleNames()->map(fn ($role) => strtolower((string) $role));
        $hasCandidateRole = $roleNames->contains('candidate');
        $hasBlockedRole = $roleNames->intersect(['partner', 'client', 'superadmin', 'manager'])->isNotEmpty();

        $isLegacyCandidate = Schema::hasColumn('users', 'role')
            && strtolower((string) $user->getAttribute('role')) === 'candidate';

        return ($hasCandidateRole || $isLegacyCandidate) && !$hasBlockedRole;
    }

    private function applyCandidateListFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }

    /**
     * Show the admin dashboard with stats.
     */
    public function index(SuperadminActivityService $activityService)
    {
        $activityService->checkBillingDueAlerts();

        $totalUsers = User::count();
        $totalClients = User::role('client')->count();
        $totalPartners = User::role('partner')->count();
        $totalCandidates = $this->candidateUsersQuery()->count();
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
            $due = $hire->invoiceDueAt();
            if ($due && ($due->isPast() || $due->isToday())) {
                $dueInvoicesCount++;
            }
        }

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalClients' => $totalClients,
            'totalPartners' => $totalPartners,
            'totalCandidates' => $totalCandidates,
            'pendingJobs' => $pendingJobs,
            'pendingApplications' => $pendingApplications,
            'todayInterviews' => $todayInterviews,
            'dueInvoicesCount' => $dueInvoicesCount
        ]);
    }

    public function dailySchedule()
    {
        $todayInterviews = JobApplication::whereDate('interview_at', Carbon::today())
            ->with(['job', 'candidate', 'candidateUser.profile', 'job.user'])
            ->orderBy('interview_at', 'asc')
            ->get();

        return view('admin.daily_interviews', compact('todayInterviews'));
    }

    // --- CANDIDATE (USER) MANAGEMENT ---

    public function listUsers(Request $request)
    {
        // Load candidate users with their real profile relation (user_profiles table)
        $query = $this->candidateUsersQuery()->with(['profile']);

        $this->applyCandidateListFilters($query, $request);

        $users = $query->latest()->paginate(10)->withQueryString();

        // Backward compatibility alias so existing blades using $user->candidate do not break.
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
        $baseCountQuery = $this->candidateUsersQuery();
        $counts = [
            'total' => (clone $baseCountQuery)->count(),
            'active' => (clone $baseCountQuery)->where('status', 'active')->count(),
            'restricted' => (clone $baseCountQuery)->where('status', 'restricted')->count(),
        ];

        return view('admin.users.index', ['users' => $users, 'counts' => $counts]);
    }

    public function exportUsers(Request $request)
    {
        $query = $this->candidateUsersQuery()->with(['profile']);
        $this->applyCandidateListFilters($query, $request);
        $users = $query->latest()->get();

        $fileName = 'candidates_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Candidate ID',
                'Name',
                'Email',
                'Phone',
                'Status',
                'Resume Uploaded',
                'Resume URL',
                'Joined On',
            ]);

            foreach ($users as $user) {
                $resumePath = $user->profile?->resume_path;
                $resumeUrl = $resumePath ? asset('storage/' . $resumePath) : '';

                fputcsv($handle, [
                    $user->id,
                    (string) $user->name,
                    (string) $user->email,
                    (string) ($user->profile?->phone_number ?? ''),
                    (string) ($user->status ?? ''),
                    $resumePath ? 'Yes' : 'No',
                    $resumeUrl,
                    optional($user->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function showUser(User $user)
    {
        if (!$this->isStrictCandidateUser($user)) {
            abort(404);
        }
        
        $user->load(['profile']);

        // Backward compatibility attributes for profile source.
        if ($user->profile) {
            $user->profile->setAttribute('mobile', $user->profile->phone_number);
            $user->profile->setAttribute('dob', $user->profile->date_of_birth);
        }

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

    /**
     * Bulk-update partner statuses. Action maps:
     *   approve → active, hold → on_hold, reject → restricted.
     */
    public function bulkUpdatePartnerStatus(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,hold,reject',
            'ids'    => 'required|array|min:1|max:200',
            'ids.*'  => 'integer|exists:users,id',
        ]);

        $statusMap = [
            'approve' => 'active',
            'hold'    => 'on_hold',
            'reject'  => 'restricted',
        ];
        $newStatus = $statusMap[$data['action']];

        // Restrict to partner-role users; never touch Superadmins.
        $partnerIds = User::role('partner')
            ->whereIn('id', $data['ids'])
            ->pluck('id');

        if ($partnerIds->isEmpty()) {
            return back()->with('error', 'No partner accounts matched the selection.');
        }

        $count = User::whereIn('id', $partnerIds)->update(['status' => $newStatus]);

        $verb = match ($data['action']) {
            'approve' => 'approved',
            'hold'    => 'put on hold',
            'reject'  => 'rejected (restricted)',
        };

        return back()->with('success', "{$count} partner(s) {$verb}.");
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
        $query = User::role('client')->with(['roles', 'profile'])->withCount('jobs');

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

        $clients = $query->paginate(10)->withQueryString();
        return view('admin.clients.index', ['users' => $clients]);
    }

    public function createClient()
    {
        return view('admin.clients.create');
    }

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'billable_period_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'billable_period_days' => (int) ($validated['billable_period_days'] ?? 30),
            'status' => 'active',
        ]);

        $user->assignRole('client');

        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $validated['phone_number'],
        ]);

        ClientProfile::create([
            'user_id' => $user->id,
            'company_name' => $validated['name'],
        ]);

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

        $userUpdate = ['billable_period_days' => $validated['billable_period_days']];
        if (!empty($validated['company_name'])) {
            // Keep users.name in sync with the editable Company Name on this form,
            // because the client listing displays users.name.
            $userUpdate['name'] = $validated['company_name'];
        }
        $user->update($userUpdate);

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
        $user->load(['profile', 'clientProfile']);
        $jobs = \App\Models\Job::where('user_id', $user->id)->with(['category'])->latest()->paginate(10);
        $totalJobs = \App\Models\Job::where('user_id', $user->id)->count();
        $activeJobs = \App\Models\Job::where('user_id', $user->id)->where('status', 'approved')->count();
        $totalHires = \App\Models\JobApplication::whereHas('job', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereIn('hiring_status', ['Joined', 'Selected'])->count();

        return view('admin.clients.show', compact('user', 'jobs', 'totalJobs', 'activeJobs', 'totalHires'));
    }

    // --- CLIENT COMMERCIALS (Permanent Hiring Format) ---

    /**
     * Default rows pulled from the Permanent Hiring Commercial Format doc.
     */
    private function defaultCommercialContractData(): array
    {
        return [
            'percentage_based' => [
                ['label' => 'Upto 10 Lakh',     'min_ctc' => 0,        'max_ctc' => 1000000,  'fee_percent' => 7,    'replacement_days' => 30],
                ['label' => '10.01 to 20 Lakh', 'min_ctc' => 1000001,  'max_ctc' => 2000000,  'fee_percent' => 8.33, 'replacement_days' => 60],
                ['label' => '20.01 to 30 Lakh', 'min_ctc' => 2000001,  'max_ctc' => 3000000,  'fee_percent' => 10,   'replacement_days' => 90],
                ['label' => '30.01 to 40 Lakh', 'min_ctc' => 3000001,  'max_ctc' => 4000000,  'fee_percent' => 12,   'replacement_days' => 90],
                ['label' => '40.01 Lakh Above', 'min_ctc' => 4000001,  'max_ctc' => null,     'fee_percent' => 15,   'replacement_days' => 90],
            ],
            'profile_wise' => [
                ['profile' => 'Entry Level',     'fee_percent' => 5,    'replacement_days' => 30],
                ['profile' => 'Mid-Level',       'fee_percent' => 8.33, 'replacement_days' => 60],
                ['profile' => 'Sr. Level',       'fee_percent' => 10,   'replacement_days' => 90],
                ['profile' => 'Leader/CXO Level','fee_percent' => 12,   'replacement_days' => 90],
            ],
            'flat' => [
                ['category' => 'BPO/Sales', 'fee_amount' => 5000, 'replacement_days' => 30],
            ],
        ];
    }

    public function editCommercials(User $user)
    {
        if (!$user->hasRole('client')) abort(404);

        $commercial = \App\Models\ClientCommercial::firstOrNew(['user_id' => $user->id]);
        $defaults = $this->defaultCommercialContractData();

        // For a brand-new commercial, pre-seed every billing type with the
        // doc defaults so the admin can see and tweak them in any tab.
        $existing = is_array($commercial->contract_data) ? $commercial->contract_data : [];
        $contract = [
            'percentage_based' => $existing['percentage_based'] ?? $defaults['percentage_based'],
            'profile_wise'     => $existing['profile_wise']     ?? $defaults['profile_wise'],
            'flat'             => $existing['flat']             ?? $defaults['flat'],
        ];

        return view('admin.clients.commercials', [
            'user'       => $user,
            'commercial' => $commercial,
            'contract'   => $contract,
        ]);
    }

    public function updateCommercials(Request $request, User $user)
    {
        if (!$user->hasRole('client')) abort(404);

        $validated = $request->validate([
            'billing_type'       => 'required|in:percentage_based,profile_wise,flat',
            'invoice_raise_days' => 'required|integer|min:0|max:365',
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'is_gst_applicable'  => 'nullable|boolean',

            // Slab rows
            'slab_label.*'           => 'nullable|string|max:60',
            'slab_min_ctc.*'         => 'nullable|integer|min:0',
            'slab_max_ctc.*'         => 'nullable|integer|min:0',
            'slab_fee_percent.*'     => 'nullable|numeric|min:0|max:100',
            'slab_replacement.*'     => 'nullable|integer|min:0|max:365',

            // Profile rows
            'prof_profile.*'         => 'nullable|string|max:60',
            'prof_fee_percent.*'     => 'nullable|numeric|min:0|max:100',
            'prof_replacement.*'     => 'nullable|integer|min:0|max:365',

            // Flat rows
            'flat_category.*'        => 'nullable|string|max:60',
            'flat_fee_amount.*'      => 'nullable|numeric|min:0',
            'flat_replacement.*'     => 'nullable|integer|min:0|max:365',
        ]);

        $slabs = [];
        foreach ((array) $request->input('slab_label', []) as $i => $label) {
            if (!trim((string) $label) && $request->input('slab_fee_percent.' . $i) === null) continue;
            $slabs[] = [
                'label'            => trim((string) $label),
                'min_ctc'          => $request->input("slab_min_ctc.$i") !== null && $request->input("slab_min_ctc.$i") !== '' ? (int) $request->input("slab_min_ctc.$i") : null,
                'max_ctc'          => $request->input("slab_max_ctc.$i") !== null && $request->input("slab_max_ctc.$i") !== '' ? (int) $request->input("slab_max_ctc.$i") : null,
                'fee_percent'      => (float) $request->input("slab_fee_percent.$i", 0),
                'replacement_days' => (int) $request->input("slab_replacement.$i", 0),
            ];
        }

        $profiles = [];
        foreach ((array) $request->input('prof_profile', []) as $i => $profile) {
            if (!trim((string) $profile)) continue;
            $profiles[] = [
                'profile'          => trim((string) $profile),
                'fee_percent'      => (float) $request->input("prof_fee_percent.$i", 0),
                'replacement_days' => (int) $request->input("prof_replacement.$i", 0),
            ];
        }

        $flats = [];
        foreach ((array) $request->input('flat_category', []) as $i => $category) {
            if (!trim((string) $category)) continue;
            $flats[] = [
                'category'         => trim((string) $category),
                'fee_amount'       => (float) $request->input("flat_fee_amount.$i", 0),
                'replacement_days' => (int) $request->input("flat_replacement.$i", 0),
            ];
        }

        \App\Models\ClientCommercial::updateOrCreate(
            ['user_id' => $user->id],
            [
                'billing_type'       => $validated['billing_type'],
                'contract_data'      => [
                    'percentage_based' => $slabs,
                    'profile_wise'     => $profiles,
                    'flat'             => $flats,
                ],
                'invoice_raise_days' => $validated['invoice_raise_days'],
                'payment_terms_days' => $validated['payment_terms_days'],
                'is_gst_applicable'  => (bool) ($request->input('is_gst_applicable') ?? false),
            ]
        );

        return redirect()->route('admin.clients.commercials.edit', $user)
            ->with('success', 'Client commercials saved.');
    }

    // --- PARTNER MANAGEMENT ---

    public function listPartners(Request $request)
    {
        $query = User::role('partner')->with(['partnerProfile', 'profile']);

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

        $partners = $query->latest()->paginate(10)->withQueryString();
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
        $user->load(['partnerProfile', 'profile']);
        return view('admin.partners.show', ['user' => $user, 'profile' => $user->partnerProfile]);
    }

    // --- JOB MANAGEMENT ---

    public function createJob()
    {
        $clients = User::role('client')->where('status', 'active')->get();
        $partners = User::role('partner')->where('status', 'active')->get();
        $candidates = Candidate::select('id', 'first_name', 'last_name', 'email')->latest()->get(); 
        
        $categories = Cache::remember('job_categories', 3600, fn () => JobCategory::orderBy('name')->get());
        $experienceLevels = Cache::remember('experience_levels', 3600, fn () => ExperienceLevel::orderBy('name')->get());
        $educationLevels = Cache::remember('education_levels', 3600, fn () => EducationLevel::orderBy('name')->get());

        return view('admin.jobs.create', compact(
            'clients', 'partners', 'candidates',
            'categories', 'experienceLevels', 'educationLevels'
        ));
    }

    public function storeJob(Request $request)
    {
        $validated = $request->validate([
            // Posting context (admin-only)
            'client_id'             => 'nullable|exists:users,id',
            'partner_visibility'    => 'required|in:all,selected',
            'allowed_partners'      => 'array|required_if:partner_visibility,selected',
            'restricted_candidates' => 'array|nullable',
            'payout_amount'         => 'nullable|numeric',
            'minimum_stay_days'     => 'nullable|integer',
            'replacement_guarantee_days' => 'nullable|integer|min:0|max:365',

            // Job specification (mirrors ClientController::validateClientJob)
            'title'                 => 'required|string|max:255',
            'category_id'           => 'required|exists:job_categories,id',
            'location'              => 'required|string|max:255',
            'job_type'              => 'required|string|max:100',
            'description'           => 'required|string',
            'min_salary'            => 'nullable|integer|min:0|required_with:max_salary',
            'max_salary'            => 'nullable|integer|min:0|gte:min_salary|required_with:min_salary',
            'min_experience'        => 'required|integer|min:0',
            'max_experience'        => 'required|integer|gte:min_experience|max:50',
            'education_level_id'    => 'required|exists:education_levels,id',
            'application_deadline'  => 'nullable|date',
            'skills_required'       => 'nullable|string',
            'company_website'       => 'nullable|url',
            'openings'              => 'nullable|integer|min:1',
            'gender_preference'     => 'required|string|in:Any,Male,Female,Other',
        ]);

        $salary = $this->formatSalaryRange(
            $validated['min_salary'] ?? null,
            $validated['max_salary'] ?? null
        );

        $companyName = 'Simplyhiree';
        if ($request->filled('client_id')) {
            $client = User::find($request->client_id);
            $companyName = $client->name;
        } elseif ($request->filled('company_name')) {
            $companyName = $request->company_name;
        }

        $job = Job::create([
            'user_id'              => $request->client_id,
            'company_name'         => $companyName,
            'status'               => 'approved',
            'title'                => $validated['title'],
            'category_id'          => $validated['category_id'],
            'location'             => $validated['location'],
            'salary'               => $salary,
            'job_type'             => $validated['job_type'],
            'description'          => $this->sanitizeJobDescription($validated['description']),
            'gender_preference'    => $validated['gender_preference'],
            'min_experience'       => $validated['min_experience'],
            'max_experience'       => $validated['max_experience'],
            'experience_level_id'  => null,
            'education_level_id'   => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'] ?? null,
            'payout_amount'        => $validated['payout_amount'] ?? 0,
            'minimum_stay_days'    => $validated['minimum_stay_days'] ?? 0,
            'replacement_guarantee_days' => $validated['replacement_guarantee_days'] ?? null,
            'partner_visibility'   => $validated['partner_visibility'],
            'skills_required'      => $validated['skills_required'] ?? null,
            'company_website'      => $validated['company_website'] ?? null,
            'openings'             => $validated['openings'] ?? 1,
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
        $deactivationRequests = Job::whereNotNull('deactivation_requested_at')
            ->with(['user'])
            ->latest('deactivation_requested_at')
            ->get();
        return view('admin.jobs.pending', [
            'jobs' => $pendingJobs,
            'deactivationRequests' => $deactivationRequests,
        ]);
    }

    public function approveJob(Request $request, Job $job)
    {
        $validated = $request->validate([
            'payout_amount'              => 'required|numeric|min:0',
            'minimum_stay_days'          => 'required|integer|min:1',
            'replacement_guarantee_days' => 'nullable|integer|min:0|max:365',
        ]);
        $update = [
            'status'            => 'approved',
            'payout_amount'     => $validated['payout_amount'],
            'minimum_stay_days' => $validated['minimum_stay_days'],
        ];
        if (array_key_exists('replacement_guarantee_days', $validated) && $validated['replacement_guarantee_days'] !== null) {
            $update['replacement_guarantee_days'] = $validated['replacement_guarantee_days'];
        }
        $job->update($update);
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

    /**
     * "Delete" archives the job. We never hard-delete so applications,
     * candidate history, partner data, etc. remain intact and viewable
     * in the Archived Jobs section.
     */
    public function destroyJob(Job $job)
    {
        if ($job->archived_at) {
            return redirect()->route('admin.jobs.archived.show', $job)
                ->with('info', 'Job is already archived.');
        }

        $job->update([
            'status'                    => 'closed',
            'archived_at'               => now(),
            'archived_by_role'          => 'Superadmin',
            'archived_by_user_id'       => auth()->id(),
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
        ]);

        return redirect()->route('admin.jobs.archived')
            ->with('success', 'Job moved to archive. All applications and candidate data preserved.');
    }

    /**
     * Approve a client's deactivation request — closes the job.
     */
    public function approveDeactivation(Job $job)
    {
        if (!$job->deactivation_requested_at) {
            return back()->with('error', 'This job has no pending deactivation request.');
        }

        $job->update([
            'status'                    => 'closed',
            'archived_at'               => now(),
            'archived_by_role'          => 'Client',
            'archived_by_user_id'       => $job->user_id,
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
        ]);

        return back()->with('success', 'Deactivation approved. Job has been archived.');
    }

    /**
     * List archived jobs (deactivated via Superadmin approval).
     */
    public function archivedJobs()
    {
        $jobs = Job::whereNotNull('archived_at')
            ->with(['user', 'category', 'archivedBy'])
            ->withCount('jobApplications')
            ->latest('archived_at')
            ->paginate(20);

        return view('admin.jobs.archived', compact('jobs'));
    }

    /**
     * Show full archive detail for one job — every application with full lifecycle.
     */
    public function showArchivedJob(Job $job)
    {
        if (!$job->archived_at) {
            return redirect()->route('admin.jobs.show', $job)
                ->with('info', "Job #{$job->id} is currently active — it has not been archived. Use the Archive button on the job page to move it to the archive.");
        }

        $job->load([
            'user',
            'category',
            'experienceLevel',
            'educationLevel',
            'archivedBy',
            'jobApplications.candidate.partner',
            'jobApplications.candidateUser',
        ]);

        return view('admin.jobs.archived_show', compact('job'));
    }

    /**
     * Permanently restore an archived job back to approved state.
     */
    public function restoreArchivedJob(Job $job)
    {
        if (!$job->archived_at) {
            return back()->with('error', 'This job is not archived.');
        }

        $job->update([
            'status'              => 'approved',
            'archived_at'         => null,
            'archived_by_role'    => null,
            'archived_by_user_id' => null,
        ]);

        return back()->with('success', 'Job restored and set back to approved.');
    }

    /**
     * Dismiss a deactivation request without closing the job.
     */
    public function dismissDeactivation(Job $job)
    {
        if (!$job->deactivation_requested_at) {
            return back()->with('error', 'This job has no pending deactivation request.');
        }

        $job->update([
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
        ]);

        return back()->with('success', 'Deactivation request dismissed. Job remains active.');
    }

    public function manageJobExclusions(Job $job)
    {
        $job->load(['educationLevel']);
        $partners = User::role('partner')->get();
        $excludedPartnerIds = $job->excludedPartners()->pluck('id')->toArray();
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
        $query = JobApplication::with(['job', 'candidate', 'candidate.partner', 'candidateUser.profile']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('candidate', function($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('candidateUser', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
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
        if ($request->filled('date_from')) {
            try { $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($request->input('date_from'))->toDateString()); } catch (\Throwable $e) {}
        }
        if ($request->filled('date_to')) {
            try { $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($request->input('date_to'))->toDateString()); } catch (\Throwable $e) {}
        }

        $allowedPerPage = [20, 50, 100, 150, 200];
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        $applications = $query->latest()->paginate($perPage)->withQueryString();
        $jobs = Job::select('id', 'title')->orderBy('title')->get();
        $partners = User::role('partner')->select('id', 'name')->orderBy('name')->get();

        return view('admin.applications.index', [
            'applications'   => $applications,
            'jobs'           => $jobs,
            'partners'       => $partners,
            'allowedPerPage' => $allowedPerPage,
            'perPage'        => $perPage,
        ]);
    }

    /**
     * Tracker Download — stream a CSV of the 16-field candidate data
     * format for the selected job applications. Capped at 500 ids per
     * request so the server never has to hold an unbounded set in memory.
     */
    public function applicationsTrackerExport(Request $request)
    {
        $ids = collect((array) $request->input('ids', []))
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Select at least one candidate to download the tracker.');
        }

        $maxRows = 200;
        if ($ids->count() > $maxRows) {
            return back()->with('error', "You can export at most {$maxRows} candidates at a time. You selected {$ids->count()}. Please refine the selection.");
        }

        $idList = $ids->all();

        $fileName = 'candidate_tracker_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
            'X-Accel-Buffering'   => 'no',
        ];

        return response()->streamDownload(function () use ($idList) {
            // Give the export room to finish on shared hosts and stream as we write.
            @set_time_limit(120);
            @ignore_user_abort(false);
            while (ob_get_level() > 0) { @ob_end_clean(); }

            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, [
                'Date of Application',
                'Name',
                'Email ID',
                'Phone Number',
                'Current Location',
                'Preferred Locations',
                'Total Experience',
                'Current Company Name',
                'Current Designation',
                'Annual Salary (Current)',
                'Notice Period / Availability',
                'Gender',
                'Marital Status',
                'Qualification',
                'Job Title / Applied For',
                'Expected Salary',
                'Source (Partner)',
                'Application Code',
                'Status',
            ]);

            // Stream in chunks of 50 to keep memory bounded.
            JobApplication::with(['job', 'candidate.partner', 'candidateUser.profile'])
                ->whereIn('id', $idList)
                ->orderBy('created_at', 'desc')
                ->chunkById(50, function ($applications) use ($out) {
                    foreach ($applications as $app) {
                $cand   = $app->candidate;
                $prof   = $app->candidateUser?->profile;
                $job    = $app->job;
                $name   = $cand
                    ? trim(($cand->first_name ?? '').' '.($cand->last_name ?? ''))
                    : ($app->candidateUser?->name ?? '');
                $expY   = $cand?->total_experience_years ?? $prof?->total_experience_years;
                $expM   = $cand?->total_experience_months ?? $prof?->total_experience_months;
                $totalExp = ($expY === null && $expM === null)
                    ? ($cand?->experience_status ?? $prof?->experience_status ?? '')
                    : ((int) ($expY ?? 0)).' Year(s) '.((int) ($expM ?? 0)).' Month(s)';

                $prefRaw = $cand?->preferred_locations ?? $prof?->preferred_locations ?? null;
                $prefLoc = is_array($prefRaw) ? implode(', ', $prefRaw) : ($prefRaw ?: '');

                $qualLevel = $cand?->education_level ?? '';
                $qualDeg   = $cand?->qualification_degree ?? $prof?->qualification_degree ?? '';
                $spec      = $cand?->specialization ?? $prof?->specialization ?? '';
                $qualParts = array_filter([$qualDeg, $spec], fn ($v) => $v !== '' && $v !== null);
                $qual      = implode(' — ', $qualParts);
                if ($qualLevel) $qual = trim(($qual ? $qual.' ' : '').'('.$qualLevel.')');

                $partnerName = $cand?->partner?->name ?? 'Direct';

                        fputcsv($out, [
                            optional($app->created_at)->format('d-M-Y'),
                            $name ?: '',
                            $cand?->email ?? $app->candidateUser?->email ?? '',
                            $cand?->phone_number ?? $prof?->phone_number ?? '',
                            $cand?->location ?? $prof?->location ?? '',
                            $prefLoc,
                            $totalExp,
                            $cand?->current_company ?? $prof?->current_company ?? '',
                            $cand?->current_designation ?? $prof?->current_role ?? '',
                            $cand?->current_ctc ?? $prof?->current_ctc ?? '',
                            $cand?->notice_period ?? $prof?->notice_period ?? '',
                            $cand?->gender ?? $prof?->gender ?? '',
                            $cand?->marital_status ?? $prof?->marital_status ?? '',
                            $qual,
                            $job?->title ?? '',
                            $cand?->expected_ctc ?? $prof?->expected_ctc ?? '',
                            $partnerName,
                            $app->application_code ?? ('#'.$app->id),
                            $app->status ?? '',
                        ]);
                    }
                    @flush();
                });

            fclose($out);
        }, $fileName, $headers);
    }

    public function approveApplication(JobApplication $application)
    {
        $application->loadMissing(['job', 'candidate.partner', 'candidateUser']);
        $application->update(['status' => 'Approved']);
        $this->notifyApplicationStakeholder($application, true);

        return redirect()->back()->with('success', 'Application ' . ($application->application_code ?? ('#' . $application->id)) . ' approved.');
    }

    public function rejectApplication(JobApplication $application)
    {
        $application->loadMissing(['job', 'candidate.partner', 'candidateUser']);
        $application->update(['status' => 'Rejected']);
        $this->notifyApplicationStakeholder($application, false);

        return redirect()->back()->with('success', 'Application ' . ($application->application_code ?? ('#' . $application->id)) . ' rejected.');
    }

    public function showApplication(JobApplication $application)
    {
        $application->load(['candidate', 'job', 'candidate.partner', 'candidateUser.profile']);
        return view('admin.applications.show', compact('application'));
    }

    private function notifyApplicationStakeholder(JobApplication $application, bool $approved): void
    {
        $notification = $approved
            ? new ApplicationApprovedByAdmin($application)
            : new ApplicationRejectedByAdmin($application);

        $partner = $application->candidate?->partner;
        if ($partner) {
            $partner->notifyNow($notification);
            return;
        }

        if ($application->candidateUser) {
            $application->candidateUser->notifyNow($notification);
        }
    }

    public function jobApplicantsReport(\App\Models\Job $job)
    {
        $applications = $job->jobApplications()->with(['candidate', 'candidate.partner', 'candidateUser.profile'])->latest()->paginate(20);
        return view('admin.reports.job_applicants', compact('job', 'applications'));
    }

    public function exportJobApplicantsReport(\App\Models\Job $job)
    {
        $applications = $job->jobApplications()
            ->with(['candidate', 'candidate.partner', 'candidateUser.profile'])
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
                $candidateUser = $application->candidateUser;
                $partnerName = $candidate && $candidate->partner ? $candidate->partner->name : 'Direct';
                $fullName = $candidate
                    ? trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? ''))
                    : '';
                if ($fullName === '') {
                    $fullName = $candidateUser?->name ?? 'Unknown Candidate';
                }
                $email = $candidate?->email ?? $candidateUser?->email ?? '';
                $phone = $candidate?->phone_number ?? $candidateUser?->profile?->phone_number ?? '';

                fputcsv($handle, [
                    $fullName,
                    $email,
                    $phone,
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

    public function billingReport(Request $request)
    {
        $query = JobApplication::where('hiring_status', 'Selected')
            ->whereNotNull('joining_date')
            ->with(['job.user', 'candidate', 'candidateUser']);

        if ($request->filled('client_id')) {
            $query->whereHas('job', fn ($q) => $q->where('user_id', (int) $request->client_id));
        }
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->whereHas('candidate', fn ($qq) => $qq->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                  ->orWhereHas('candidateUser', fn ($qq) => $qq->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                  ->orWhereHas('job', fn ($qq) => $qq->where('title', 'like', "%{$term}%"));
            });
        }

        $apps = $query->latest('joining_date')->paginate(25)->withQueryString();
        $rows = $apps->through(fn ($app) => $app->billingSnapshot());

        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $rows->setCollection(
                $rows->getCollection()->filter(fn ($r) => $r['status'] === $statusFilter)->values()
            );
        }

        $current = $rows->getCollection();
        $counts = [
            'Paid'         => $current->where('status', 'Paid')->count(),
            'Overdue'      => $current->where('status', 'Overdue')->count(),
            'Raised'       => $current->where('status', 'Raised')->count(),
            'Due to Raise' => $current->where('status', 'Due to Raise')->count(),
            'Maturing'     => $current->where('status', 'Maturing')->count(),
        ];

        // For the client filter dropdown — clients who have any Selected hire
        $clients = \App\Models\User::role('client')
            ->whereHas('jobs.jobApplications', fn ($q) => $q->where('hiring_status', 'Selected'))
            ->orderBy('name')
            ->get(['id', 'name']);

        // Sanity check — clients with Selected hires but no commercial configured.
        // Pulls a fresh query so it's independent of pagination / status filter.
        $missingCommercials = \App\Models\User::role('client')
            ->whereHas('jobs.jobApplications', fn ($q) => $q->where('hiring_status', 'Selected')->whereNotNull('joining_date'))
            ->whereDoesntHave('clientCommercial')
            ->withCount(['jobs as selected_hires_count' => function ($q) {
                $q->join('job_applications', 'job_applications.job_id', '=', 'jobs.id')
                  ->where('job_applications.hiring_status', 'Selected')
                  ->whereNotNull('job_applications.joining_date');
            }])
            ->orderByDesc('selected_hires_count')
            ->get(['id', 'name', 'email']);

        return view('admin.billing.index', [
            'placements'         => $rows,
            'counts'             => $counts,
            'clients'            => $clients,
            'statusFilter'       => $statusFilter,
            'missingCommercials' => $missingCommercials,
        ]);
    }

    public function markAsPaid(JobApplication $application)
    {
        $application->update(['payment_status' => 'paid', 'paid_at' => now()]);
        return redirect()->back()->with('success', 'Invoice marked as PAID.');
    }

    public function markInvoiceRaised(JobApplication $application)
    {
        // Stamp the resolved amount if it isn't already set; that way the
        // amount used at "raise time" is locked in.
        $stamp = ['invoice_generated_at' => now()];
        if (!$application->invoice_amount) {
            $cb = $application->resolveCommercial();
            if ($cb && $cb['invoice_amount'] > 0) {
                $stamp['invoice_amount'] = $cb['invoice_amount'];
            }
        }
        $application->update($stamp);
        return redirect()->back()->with('success', 'Invoice marked as RAISED.');
    }

    public function jobReport(Request $request)
    {
        $query = Job::with(['user', 'jobApplications.candidate.partner', 'jobApplications.candidateUser'])
            ->whereNull('archived_at')
            ->latest();

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
        $query = Job::with(['user', 'jobApplications'])
            ->whereNull('archived_at')
            ->latest();

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

    /**
     * Format salary min/max into a readable string (mirrors ClientController).
     */
    private function formatSalaryRange(?int $minSalary, ?int $maxSalary): ?string
    {
        if ($minSalary === null && $maxSalary === null) {
            return null;
        }
        if ($minSalary !== null && $maxSalary !== null) {
            if ($minSalary === $maxSalary) {
                return 'Rs. ' . number_format($minSalary);
            }
            return 'Rs. ' . number_format($minSalary) . ' - Rs. ' . number_format($maxSalary);
        }
        if ($minSalary !== null) {
            return 'Rs. ' . number_format($minSalary) . '+';
        }
        return 'Up to Rs. ' . number_format((int) $maxSalary);
    }

    /**
     * Sanitize Quill editor HTML — keep formatting tags, strip scripts and event handlers.
     */
    private function sanitizeJobDescription(?string $html): ?string
    {
        if (!$html) return $html;
        $allowed = '<p><br><b><strong><i><em><u><s><strike><ul><ol><li><h2><h3><blockquote><a><span>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*"(?:[^"\\\\]|\\\\.)*"/i', '', $clean);
        $clean = preg_replace("/\s+on[a-z]+\s*=\s*'(?:[^'\\\\]|\\\\.)*'/i", '', $clean);
        $clean = preg_replace('/href\s*=\s*"\s*javascript:[^"]*"/i', 'href="#"', $clean);
        $clean = preg_replace("/href\s*=\s*'\s*javascript:[^']*'/i", "href='#'", $clean);
        return $clean;
    }
}
