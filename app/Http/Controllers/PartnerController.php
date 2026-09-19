<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\Candidate;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\ExperienceLevel;
use App\Models\EducationLevel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\DuplicateCandidateService;
use App\Services\AssessmentSessionService;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    private function partnerPoolIds($partner): array
    {
        $ownerId = (int) $partner->partnerOwnerId();

        return User::query()
            ->whereKey($ownerId)
            ->orWhere('parent_partner_id', $ownerId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function ownsCandidate($partner, Candidate $candidate): bool
    {
        return in_array((int) $candidate->partner_id, $this->partnerPoolIds($partner), true);
    }

    public function upgrade()
    {
        $partner = Auth::user();
        $existing = \App\Models\PlanChangeRequest::where('partner_id', $partner->partnerOwnerId())
            ->where('status', 'pending')
            ->latest()
            ->first();

        // Pull plans from the DB so superadmin edits on /admin/partner-plans
        // reflect here immediately.
        $plans = \App\Models\PartnerPlan::orderBy('sort_order')->orderBy('price')->get();

        return view('partner.upgrade', [
            'partner'        => $partner,
            'pendingRequest' => $existing,
            'plans'          => $plans,
        ]);
    }

    public function requestPlanChange(Request $request)
    {
        $partner = Auth::user();
        // Only the partner-owner can change plan
        if (!$partner->isPartnerOwner()) {
            return back()->with('error', 'Only the partner-account owner can request a plan change.');
        }

        $data = $request->validate([
            'requested_plan' => 'required|in:Free,Basic,Pro,Enterprise',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $currentPlan = $partner->partner_plan ?? 'Free';
        if ($data['requested_plan'] === $currentPlan) {
            return back()->with('error', "You are already on the {$currentPlan} plan.");
        }

        // Block duplicates while a request is pending
        $existing = \App\Models\PlanChangeRequest::where('partner_id', $partner->id)
            ->where('status', 'pending')
            ->first();
        if ($existing) {
            return back()->with('error', "You already have a pending plan-change request to {$existing->requested_plan}. Wait for the admin to action it, or cancel it first.");
        }

        \App\Models\PlanChangeRequest::create([
            'partner_id'     => $partner->id,
            'current_plan'   => $currentPlan,
            'requested_plan' => $data['requested_plan'],
            'notes'          => $data['notes'] ?? null,
            'status'         => 'pending',
        ]);

        return back()->with('success', "Plan change to {$data['requested_plan']} requested. A SimplyHiree manager will reach out to you shortly.");
    }

    public function cancelPlanChange(\App\Models\PlanChangeRequest $planChangeRequest)
    {
        $partner = Auth::user();
        if ((int) $planChangeRequest->partner_id !== (int) $partner->partnerOwnerId()) {
            abort(403);
        }
        if ($planChangeRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be cancelled.');
        }
        $planChangeRequest->update(['status' => 'cancelled']);
        return back()->with('success', 'Request cancelled.');
    }

    public function wallet()
    {
        $partner = Auth::user();
        abort_unless($partner->isPartnerOwner(), 403, 'Wallet and financial information are restricted to the partner account owner.');
        $ownerId = $partner->partnerOwnerId();
        $poolIds = $this->partnerPoolIds($partner);

        $myApps = JobApplication::whereHas('candidate', fn ($q) => $q->whereIn('partner_id', $poolIds))
            ->with(['job', 'candidate', 'partnerCreditNote']);

        $activeCount = (clone $myApps)->where('joined_status', 'Joined')->whereNull('replacement_status')->count();
        $underGuaranteeCount = (clone $myApps)
            ->where('joined_status', 'Joined')
            ->whereNotNull('joining_date')
            ->whereRaw('DATE_ADD(joining_date, INTERVAL COALESCE(replacement_window_days, 90) DAY) >= NOW()')
            ->count();
        $replacementRequiredCount = (clone $myApps)->where('replacement_status', 'window_open')->count();

        $credits = \App\Models\PartnerCreditNote::where('partner_id', $ownerId)
            ->with(['sourceApplication.job', 'sourceApplication.candidate'])
            ->latest()
            ->paginate(20);

        $totals = [
            'pending'   => \App\Models\PartnerCreditNote::where('partner_id', $ownerId)->where('status', 'pending')->sum('amount'),
            'applied'   => \App\Models\PartnerCreditNote::where('partner_id', $ownerId)->where('status', 'applied')->sum('amount'),
            'cancelled' => \App\Models\PartnerCreditNote::where('partner_id', $ownerId)->where('status', 'cancelled')->sum('amount'),
        ];

        $replacementsRequired = (clone $myApps)
            ->where('replacement_status', 'window_open')
            ->latest('replacement_requested_at')
            ->limit(20)
            ->get();

        return view('partner.wallet', compact(
            'activeCount', 'underGuaranteeCount', 'replacementRequiredCount',
            'credits', 'totals', 'replacementsRequired'
        ));
    }

    /**
     * Show the partner dashboard.
     */
    public function index()
    {
        $partner = Auth::user();
        $partnerOwnerId = $partner->partnerOwnerId();

        // --- Morning Brief Data ---
        $todayInterviews = JobApplication::whereHas('candidate', function ($query) use ($partnerOwnerId) {
                $query->where('partner_id', $partnerOwnerId);
            })
            ->whereDate('interview_at', Carbon::today())
            ->count();

        // Replacement requests raised by clients for this partner's candidates
        $replacementRequests = JobApplication::with(['job', 'candidate'])
            ->whereNotNull('replacement_requested_at')
            ->whereHas('candidate', fn ($q) => $q->where('partner_id', $partnerOwnerId))
            ->latest('replacement_requested_at')
            ->limit(10)
            ->get();

        // Pop-up modal: show once per login session if there are open requests
        $showReplacementModal = $replacementRequests->isNotEmpty()
            && !session()->has('replacement_modal_shown');
        if ($showReplacementModal) {
            session()->put('replacement_modal_shown', true);
        }

        return view('partner.dashboard', [
            'partner'              => $partner,
            'todayInterviews'      => $todayInterviews,
            'replacementRequests'  => $replacementRequests,
            'showReplacementModal' => $showReplacementModal,
        ]);
    }

    /**
     * Dedicated page listing every replacement request for this partner's
     * candidates so the team can action them in one place.
     */
    public function replacements(Request $request)
    {
        $partner = Auth::user();
        $partnerOwnerId = $partner->parent_partner_id ?? $partner->id;

        $query = JobApplication::with(['job', 'candidate'])
            ->whereNotNull('replacement_requested_at')
            ->whereHas('candidate', fn ($q) => $q->where('partner_id', $partnerOwnerId));

        // Optional filters
        if ($status = $request->input('status')) {
            if ($status === 'open') {
                $query->whereNull('replacement_resolved_at');
            } elseif ($status === 'resolved') {
                $query->whereNotNull('replacement_resolved_at');
            }
        }
        if ($jobId = $request->input('job_id')) {
            $query->where('job_id', $jobId);
        }
        if ($search = $request->input('search')) {
            $query->whereHas('candidate', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $requests = $query->latest('replacement_requested_at')->paginate(20)->withQueryString();

        $jobs = JobApplication::whereNotNull('replacement_requested_at')
            ->whereHas('candidate', fn ($q) => $q->where('partner_id', $partnerOwnerId))
            ->with('job:id,title')
            ->get()
            ->pluck('job')
            ->unique('id')
            ->filter()
            ->values();

        return view('partner.replacements', [
            'requests' => $requests,
            'jobs'     => $jobs,
        ]);
    }

    public function replacementCandidateForm(JobApplication $application)
    {
        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();
        $application->load(['job', 'candidate', 'replacementApplication.candidate']);

        abort_unless(
            $application->replacement_requested_at
            && (int) $application->candidate?->partner_id === (int) $ownerId
            && $application->job,
            404
        );

        $candidates = Candidate::where('partner_id', $ownerId)
            ->whereKeyNot($application->candidate_id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('partner.replacements-submit', compact('application', 'candidates'));
    }

    public function replacementCandidateStore(Request $request, JobApplication $application)
    {
        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();
        $application->load(['job', 'candidate']);

        abort_unless(
            $application->replacement_requested_at
            && (int) $application->candidate?->partner_id === (int) $ownerId
            && $application->job,
            404
        );

        $data = $request->validate([
            'candidate_id' => 'required|integer|exists:candidates,id',
            'interview_at' => $application->job->screening_required ? 'nullable|date' : 'required|date|after:now',
        ]);
        $candidate = Candidate::whereKey($data['candidate_id'])->where('partner_id', $ownerId)->firstOrFail();
        $duplicateService = app(DuplicateCandidateService::class);
        if (!$duplicateService->canSubmit($candidate)) {
            return back()->with('error', 'This candidate is awaiting duplicate review and cannot be nominated yet.');
        }

        $replacement = JobApplication::firstOrCreate(
            ['job_id' => $application->job_id, 'candidate_id' => $candidate->id],
            [
                'status' => $application->job->screening_required ? 'Pending Review' : 'Approved',
                'hiring_status' => $application->job->screening_required ? null : 'Interview Scheduled',
                'interview_at' => $application->job->screening_required ? null : Carbon::parse($data['interview_at']),
                'submitted_by_user_id' => Auth::id(),
            ]
        );
        if ($replacement->id === $application->id) {
            return back()->with('error', 'The failed hire cannot replace themselves.');
        }

        $application->update([
            'replacement_status' => 'in_progress',
            'replacement_application_id' => $replacement->id,
        ]);
        $replacement->update(['replacement_status' => 'replacement_given']);

        return redirect()->route('partner.replacements')->with('success', 'Replacement candidate submitted and linked to this case.');
    }

    /**
     * Show the applications related to this partner.
     */
    public function applications(Request $request)
    {
        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();
        $teamPartnerIds = User::query()
            ->where('id', $ownerId)
            ->orWhere('parent_partner_id', $ownerId)
            ->pluck('id');

        // Applications belong to the shared owner/team candidate pool.
        $baseScope = fn($q) => $q->whereIn('partner_id', $teamPartnerIds);

        $query = JobApplication::whereHas('candidate', $baseScope)
                    ->with(['job', 'candidate', 'interviewRounds']);

        // Filter: Interviews Today
        if ($request->has('interview_today')) {
            $query->whereDate('interview_at', Carbon::today());
        }

        // Search by candidate name/email
        if ($search = $request->input('search')) {
            $query->whereHas('candidate', function ($q) use ($search, $teamPartnerIds) {
                $q->whereIn('partner_id', $teamPartnerIds)
                  ->where(function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $statusMap = [
                'Pending Review'      => fn($q) => $q->where('status', 'Pending Review'),
                'Approved'            => fn($q) => $q->where('status', 'Approved'),
                'Rejected'            => fn($q) => $q->where('status', 'Rejected'),
                'Interview Scheduled' => fn($q) => $q->where('hiring_status', 'Interview Scheduled'),
                'Interviewed'         => fn($q) => $q->where('hiring_status', 'Interviewed'),
                'No-Show'             => fn($q) => $q->where('hiring_status', 'No-Show'),
                'Client Rejected'     => fn($q) => $q->where('hiring_status', 'Client Rejected'),
                'Selected'            => fn($q) => $q->where('hiring_status', 'Selected')->whereNull('selected_by_admin_id'),
                'Joined'              => fn($q) => $q->where('joined_status', 'Joined'),
                'Left'                => fn($q) => $q->where('joined_status', 'Left'),
                'Did Not Join'        => fn($q) => $q->where('joined_status', 'Did Not Join'),
                'Did Not Join / Left' => fn($q) => $q->whereIn('joined_status', ['Left', 'Did Not Join']),
            ];
            if (isset($statusMap[$status])) {
                ($statusMap[$status])($query);
            }
        }

        // Job filter
        if ($jobId = $request->input('job_id')) {
            $query->where('job_id', $jobId);
        }

        // Client filter (company_name on job)
        if ($client = $request->input('client')) {
            $query->whereHas('job', fn($q) => $q->where('company_name', $client));
        }

        // Date range filter
        if ($from = $request->input('date_from')) {
            $query->whereDate('job_applications.created_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('job_applications.created_at', '<=', $to);
        }

        $applications = $query->latest()->paginate(20)->withQueryString();

        // Strip fields partners must not see
        $applications->getCollection()->each(function ($app) {
            $app->makeHidden(['client_notes', 'final_ctc', 'invoice_amount', 'fee_percent', 'fee_flat']);
            if ($app->job) $app->job->makeHidden(['user_id', 'invoice_amount']);
        });

        // Dropdown data: jobs and clients this partner has applications for
        $partnerJobIds = JobApplication::whereHas('candidate', $baseScope)
                            ->whereNotNull('job_id')
                            ->pluck('job_id')
                            ->unique();

        $filterJobs = \App\Models\Job::whereIn('id', $partnerJobIds)
                        ->select('id', 'title', 'company_name', 'is_company_confidential')
                        ->orderBy('title')
                        ->get();

        $filterClients = $filterJobs->where('is_company_confidential', false)
                            ->pluck('company_name')
                            ->filter()
                            ->unique()
                            ->sort()
                            ->values();

        // Status pill counts — computed against the SAME filters except status itself,
        // in a single aggregated query using CASE WHEN buckets (was 9 separate count
        // queries, each cloning a whereHas EXISTS subquery — major bottleneck).
        $countsBase = JobApplication::whereHas('candidate', $baseScope);
        if ($search = $request->input('search')) {
            $countsBase->whereHas('candidate', function ($q) use ($search, $teamPartnerIds) {
                $q->whereIn('partner_id', $teamPartnerIds)
                  ->where(function ($q2) use ($search) {
                      $q2->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        if ($jobId = $request->input('job_id')) $countsBase->where('job_id', $jobId);
        if ($client = $request->input('client')) $countsBase->whereHas('job', fn($q) => $q->where('company_name', $client));
        if ($from = $request->input('date_from')) $countsBase->whereDate('job_applications.created_at', '>=', $from);
        if ($to = $request->input('date_to')) $countsBase->whereDate('job_applications.created_at', '<=', $to);

        $row = $countsBase->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Pending Review' THEN 1 ELSE 0 END) as pending_review,
            SUM(CASE WHEN status = 'Approved' AND hiring_status IS NULL THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN hiring_status = 'Interview Scheduled' THEN 1 ELSE 0 END) as interview_scheduled,
            SUM(CASE WHEN hiring_status = 'Interviewed' THEN 1 ELSE 0 END) as interviewed,
            SUM(CASE WHEN hiring_status = 'Selected' AND joined_status IS NULL THEN 1 ELSE 0 END) as selected_status,
            SUM(CASE WHEN joined_status = 'Joined' THEN 1 ELSE 0 END) as joined_status_count,
            SUM(CASE WHEN status = 'Rejected' OR hiring_status = 'Client Rejected' THEN 1 ELSE 0 END) as rejected,
            SUM(CASE WHEN joined_status IN ('Left', 'Did Not Join') THEN 1 ELSE 0 END) as dnj_left
        ")->first();

        $statusCounts = [
            'all'                 => (int) $row->total,
            'Pending Review'      => (int) $row->pending_review,
            'Approved'            => (int) $row->approved,
            'Interview Scheduled' => (int) $row->interview_scheduled,
            'Interviewed'         => (int) $row->interviewed,
            'Selected'            => (int) $row->selected_status,
            'Joined'              => (int) $row->joined_status_count,
            'Rejected'            => (int) $row->rejected,
            'Did Not Join / Left' => (int) $row->dnj_left,
        ];

        return view('partner.applications', compact('applications', 'filterJobs', 'filterClients', 'statusCounts'));
    }

    public function showApplication(\App\Models\JobApplication $application)
    {
        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();
        $teamPartnerIds = User::query()
            ->where('id', $ownerId)
            ->orWhere('parent_partner_id', $ownerId)
            ->pluck('id');

        // Ensure this application's candidate belongs to this partner team.
        if (!$application->candidate || !$teamPartnerIds->contains((int) $application->candidate->partner_id)) {
            abort(403);
        }

        $application->load(['job', 'candidate', 'interviewRounds']);
        $application->makeHidden(['client_notes', 'final_ctc', 'invoice_amount', 'fee_percent', 'fee_flat']);

        return view('partner.application-show', compact('application'));
    }

    /**
     * List available jobs with filtering and visibility checks.
     */
    public function jobs(Request $request)
    {
        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();
        $teamPartnerIds = $this->partnerPoolIds($partner);

        $query = Job::where('status', 'approved')
            ->visibleToPartner($partner);

        // 3. Premium / bulk-hiring jobs are reserved for Pro & Enterprise plans
        //    AND partners whose rating tier is at least Pro (>= 4.0).
        $ownerId    = $partner->partnerOwnerId();
        $owner      = \App\Models\User::find($ownerId);
        $ownerPlan  = $owner?->partner_plan ?? 'Free';
        $ownerLevel = $owner?->vendor_level ?? 'Basic';
        $hasPremiumPlan = in_array($ownerPlan,  ['Pro', 'Enterprise'], true);
        $hasPremiumTier = in_array($ownerLevel, ['Pro', 'Elite'], true);
        if (!$hasPremiumPlan || !$hasPremiumTier) {
            $query->where(function ($q) {
                $q->where('is_premium', false)->orWhereNull('is_premium');
            });
        }
        // Restricted-tier partners (avg < 3.5) lose visibility everywhere.
        if ($ownerLevel === 'Restricted') {
            // Show jobs but cut volume in half by date — soft penalty.
            $query->where('created_at', '>=', now()->subDays(30));
        }

        // --- Search Filters ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%")
                  ->orWhere('skills_required', 'like', "%{$searchTerm}%");
            });
        }

        // FIX: Location Search using LIKE to match partial strings (e.g., "Delhi" inside "Mumbai, Delhi")
        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->input('job_type'));
        }
        if ($request->filled('experience_level_id')) {
            $query->where('experience_level_id', $request->input('experience_level_id'));
        }
        if ($request->filled('education_level_id')) {
            $query->where('education_level_id', $request->input('education_level_id'));
        }

        // --- Eager Load ---
        // Using 'category' based on previous fixes (ensure Job model has category() or jobCategory())
        $jobs = $query->with([
            // Include submissions made by the owner and every member of the
            // same partner team so the job funnel matches My Applications.
            'jobApplications' => function ($query) use ($teamPartnerIds) {
                $query->whereHas('candidate', function ($subQuery) use ($teamPartnerIds) {
                    $subQuery->whereIn('partner_id', $teamPartnerIds);
                });
            },
            'experienceLevel',
            'educationLevel',
            'category'
        ])
        ->latest()
        ->paginate(10)
        ->appends($request->query());

        $jobs->each(function ($job) use ($partner) {
            $myApps = $job->jobApplications;

            // Stats scoped specifically to the logged-in partner's submissions
            $stats = [
                'applied'   => $myApps->count(),
                'screened'  => $myApps->where('status', 'Approved')->count(),
                'turned_up' => $myApps->whereIn('hiring_status', ['Interviewed', 'Selected'])->count() + $myApps->where('joined_status', 'Joined')->count(),
                'selected'  => $myApps->where('hiring_status', 'Selected')->count() + $myApps->where('joined_status', 'Joined')->count(),
                'joined'    => $myApps->where('joined_status', 'Joined')->count(),
            ];
            $job->stats = (object) $stats;
            $job->my_stats = (object) $stats;

            // Hide client/billing fields. Partners only need to see the
            // partner-payout amount, never the full deal value.
            $job->makeHidden(['user_id', 'invoice_amount']);
            foreach ($myApps as $app) {
                $app->makeHidden(['client_notes', 'final_ctc', 'invoice_amount', 'fee_percent', 'fee_flat', 'candidate_id', 'candidate']);
            }
        });

        // --- FIX: Normalize Filter Options ---
        // 1. Get all location strings
        // 2. Explode by comma
        // 3. Trim whitespace
        // 4. Flatten into one list and remove duplicates
        $locations = Job::where('status', 'approved')
            ->pluck('location')
            ->flatMap(function ($values) {
                return array_map('trim', explode(',', $values));
            })
            ->unique()
            ->sort()
            ->values();

        $job_types = Job::select('job_type')->distinct()->orderBy('job_type')->pluck('job_type');
        $experienceLevels = Cache::remember('experience_levels', 3600, fn () => ExperienceLevel::orderBy('name')->get());
        $educationLevels = Cache::remember('education_levels', 3600, fn () => EducationLevel::orderBy('name')->get());

        return view('partner.jobs', [
            'jobs' => $jobs,
            'locations' => $locations,
            'job_types' => $job_types,
            'experienceLevels' => $experienceLevels,
            'educationLevels' => $educationLevels,
        ]);
    }

    /**
     * Show a single job with matching candidates and application history.
     */
    public function showJob(Job $job)
    {
        if ($job->status !== 'approved' || !$job->isVisibleToPartner(Auth::user())) {
            abort(404, 'This job is currently not available.');
        }

        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();

        // 1. Get Job Details. We DELIBERATELY do not eager-load the 'user'
        //    relation here — that's the client owner, and partners should not
        //    see client contact details (email/phone/address).
        $job->load(['experienceLevel', 'educationLevel', 'category']);

        // Also strip any sensitive fields from the model that might be
        // dumped to the view by accident. Partners should never see:
        //   - invoice_amount / fee_percent (deal value with client)
        //   - client_notes (private notes between client and admin)
        //   - user_id of the client owner
        $job->makeHidden(['user_id', 'invoice_amount']);

        // 2. Fetch Already Applied Candidates for this Partner
        $appliedApplications = JobApplication::where('job_id', $job->id)
                                             ->whereHas('candidate', function ($query) use ($ownerId) {
                                                 $query->where('partner_id', $ownerId);
                                             })
                                             ->with('candidate')
                                             ->latest()
                                             ->get();

        $appliedCandidateIds = $appliedApplications->pluck('candidate_id')->toArray();

        // 3. Find Matching Candidates from this Partner's Pool
        $myCandidates = Candidate::where('partner_id', $ownerId)
                                 ->whereNotIn('id', $appliedCandidateIds)
                                 ->get();
        
        $matchingCandidates = $myCandidates->filter(function ($candidate) use ($job) {
            // Simple Match Logic
            $candidateKeywords = strtolower($candidate->skills . ' ' . $candidate->job_interest . ' ' . $candidate->job_role_preference);
            $titleWords = explode(' ', strtolower($job->title));
            
            foreach ($titleWords as $word) {
                if (strlen($word) > 2 && str_contains($candidateKeywords, $word)) {
                    return true;
                }
            }
            return false;
        });

        return view('partner.jobs.show', [
            'job' => $job,
            'matchingCandidates' => $matchingCandidates,
            'appliedApplications' => $appliedApplications,
            'allCandidates' => $myCandidates
        ]);
    }

    /**
     * Show the partner's earnings.
     */
    public function earnings()
    {
        $partner = Auth::user();
        abort_unless($partner->isPartnerOwner(), 403, 'Earnings and payout information are restricted to the partner account owner.');
        $poolIds = $this->partnerPoolIds($partner);

        $placements = JobApplication::where('joined_status', 'Joined')
                                    ->whereHas('candidate', function ($query) use ($poolIds) {
                                        $query->whereIn('partner_id', $poolIds);
                                    })
                                    ->with(['job', 'candidate'])
                                    ->get();

        $earningsData = [];

        foreach ($placements as $app) {
            if (!$app->joining_date || !$app->job || empty($app->job->payout_amount) || empty($app->job->minimum_stay_days)) {
                continue;
            }

            $joiningDate = Carbon::parse($app->joining_date);
            $payoutDate = $joiningDate->copy()->addDays($app->job->minimum_stay_days);

            $isEligible = $payoutDate->isPast() && is_null($app->left_at);
            $status = $isEligible ? 'Eligible' : 'Pending';

            $earningsData[] = (object) [
                'candidate_name' => $app->candidate
                    ? trim(($app->candidate->first_name ?? '') . ' ' . ($app->candidate->last_name ?? ''))
                    : ($app->candidateUser?->name ?? 'Unknown Candidate'),
                'job_title' => $app->job->title,
                'joining_date' => $app->joining_date->format('M d, Y'),
                'payout_amount' => '₹' . number_format($app->job->payout_amount, 0),
                'minimum_stay_days' => $app->job->minimum_stay_days,
                'payout_date' => $payoutDate->format('M d, Y'),
                'status' => $status,
            ];
        }

        $earningsData = collect($earningsData)->sortByDesc(function($item) {
            return Carbon::parse($item->payout_date);
        });

        return view('partner.earnings.index', [
            'earnings' => $earningsData
        ]);
    }
    
    // --- CANDIDATE MANAGEMENT ---

    public function checkCandidateMobile()
    {
        return view('partner.candidates.check-mobile');
    }

    public function verifyCandidateMobile(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|digits:10',
        ]);

        $partner = Auth::user();
        $poolIds = $this->partnerPoolIds($partner);
        $phone = $request->input('phone_number');

        $existingCandidate = Candidate::whereIn('partner_id', $poolIds)
                                      ->where('phone_number', $phone)
                                      ->first();

        if ($existingCandidate) {
            return redirect()->route('partner.candidates.edit', $existingCandidate->id)
                             ->with('info', 'A candidate with this mobile number already exists in your pool.');
        }

        return redirect()->route('partner.candidates.create', ['mobile' => $phone]);
    }

    public function createCandidate(Request $request)
    {
        if (!$request->has('mobile')) {
            return redirect()->route('partner.candidates.check');
        }

        $mobile = $request->input('mobile');
        return view('partner.candidates.create', compact('mobile'));
    }

    public function storeCandidate(Request $request)
    {
        $partner = Auth::user();
        $ownerId = (int) $partner->partnerOwnerId();
        $poolIds = $this->partnerPoolIds($partner);

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('candidates', 'email')->where(fn ($query) => $query->whereIn('partner_id', $poolIds))],
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('candidates', 'phone_number')->where(fn ($query) => $query->whereIn('partner_id', $poolIds))],
            'alternate_phone_number' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'preferred_locations' => 'required|string|max:500',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|in:Male,Female,Other',
            'marital_status' => 'required|string|max:30',
            'job_interest' => 'required|string|max:255',
            'education_level' => 'required|string|max:255',
            'qualification_degree' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'experience_status' => 'required|string|in:Experienced,Fresher',
            'total_experience_years' => 'required|integer|min:0|max:60',
            'total_experience_months' => 'required|integer|min:0|max:11',
            'current_company' => 'required|string|max:255',
            'current_designation' => 'required|string|max:255',
            'current_ctc' => 'required|string|max:100',
            'expected_ctc' => 'required|string|max:100',
            'notice_period' => 'required|string|max:100',
            'job_role_preference' => 'nullable|string',
            'languages_spoken' => 'nullable|string',
            'skills' => 'nullable|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $duplicateService = app(DuplicateCandidateService::class);
        $resumeFingerprint = $duplicateService->fingerprint($request->file('resume'));
        $assessment = $duplicateService->assess($ownerId, $validatedData['email'] ?? null, $validatedData['phone_number'], $resumeFingerprint);
        $validatedData['partner_id'] = $ownerId;
        $validatedData['resume_fingerprint'] = $resumeFingerprint;
        $validatedData['duplicate_status'] = 'clear';
        $validatedData['preferred_locations'] = array_values(array_filter(array_map('trim', explode(',', $validatedData['preferred_locations']))));

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }
        unset($validatedData['resume']);

        $candidate = Candidate::create($validatedData);
        $duplicateService->quarantine($candidate, $assessment, 'candidate_create_web');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'candidate' => $candidate,
                'message' => 'Candidate added successfully'
            ]);
        }

        return redirect()->route('partner.candidates.index')->with('success', 'Candidate added successfully!');
    }

    public function listCandidates(Request $request)
    {
        $partner = Auth::user();
        $poolIds = $this->partnerPoolIds($partner);

        $query = Candidate::whereIn('partner_id', $poolIds);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($exp = $request->input('experience')) {
            $query->where('experience_status', $exp);
        }

        if ($location = $request->input('location')) {
            $query->where('location', 'like', "%{$location}%");
        }

        $candidates = $query->latest()->paginate(20)->withQueryString();

        $locations = Candidate::whereIn('partner_id', $poolIds)
                        ->whereNotNull('location')
                        ->distinct()
                        ->orderBy('location')
                        ->pluck('location');

        return view('partner.candidates.index', compact('candidates', 'locations'));
    }

    public function showCandidate(Candidate $candidate)
    {
        if (!$this->ownsCandidate(Auth::user(), $candidate)) {
            abort(403);
        }
        return view('partner.candidates.show', compact('candidate'));
    }

    public function editCandidate(Candidate $candidate)
    {
        if (!$this->ownsCandidate(Auth::user(), $candidate)) {
            abort(403);
        }
        return view('partner.candidates.edit', compact('candidate'));
    }

    public function updateCandidate(Request $request, Candidate $candidate)
    {
        if (!$this->ownsCandidate(Auth::user(), $candidate)) {
            abort(403);
        }

        $partner = Auth::user();
        $poolIds = $this->partnerPoolIds($partner);

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('candidates', 'email')->ignore($candidate->id)->where(fn ($query) => $query->whereIn('partner_id', $poolIds))],
            'phone_number' => ['required', 'string', 'max:20', Rule::unique('candidates', 'phone_number')->ignore($candidate->id)->where(fn ($query) => $query->whereIn('partner_id', $poolIds))],
            'alternate_phone_number' => 'nullable|string|max:20',
            'location' => 'required|string|max:255',
            'preferred_locations' => 'required|string|max:500',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|string|in:Male,Female,Other',
            'marital_status' => 'required|string|max:30',
            'job_interest' => 'nullable|string|max:255',
            'education_level' => 'required|string|max:255',
            'qualification_degree' => 'required|string|max:255',
            'specialization' => 'required|string|max:255',
            'experience_status' => 'required|string|in:Experienced,Fresher',
            'total_experience_years' => 'required|integer|min:0|max:60',
            'total_experience_months' => 'required|integer|min:0|max:11',
            'current_company' => 'required|string|max:255',
            'current_designation' => 'required|string|max:255',
            'current_ctc' => 'required|string|max:100',
            'expected_ctc' => 'required|string|max:100',
            'notice_period' => 'required|string|max:100',
            'job_role_preference' => 'nullable|string',
            'languages_spoken' => 'nullable|string',
            'skills' => 'nullable|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $validatedData['preferred_locations'] = array_values(array_filter(array_map('trim', explode(',', $validatedData['preferred_locations']))));
        $duplicateService = app(DuplicateCandidateService::class);
        $resumeFingerprint = $request->hasFile('resume') ? $duplicateService->fingerprint($request->file('resume')) : $candidate->resume_fingerprint;
        $assessment = $duplicateService->assess($partner->partnerOwnerId(), $validatedData['email'] ?? null, $validatedData['phone_number'], $resumeFingerprint, $candidate->id);
        $validatedData['resume_fingerprint'] = $resumeFingerprint;

        if ($request->hasFile('resume')) {
            if ($candidate->resume_path && Storage::disk('public')->exists($candidate->resume_path)) {
                Storage::disk('public')->delete($candidate->resume_path);
            }
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }
        unset($validatedData['resume']);

        $candidate->update($validatedData);
        $duplicateService->quarantine($candidate, $assessment, 'candidate_update_web');

        return redirect()->route('partner.candidates.show', $candidate->id)->with('success', 'Candidate updated successfully!');
    }

    public function updateCandidateResume(Request $request, Candidate $candidate)
    {
        if (!$this->ownsCandidate(Auth::user(), $candidate)) {
            abort(403);
        }

        $request->validate([
            'resume' => 'required|file|mimes:pdf,doc,docx|max:10240', // max 10MB
        ]);

        $file = $request->file('resume');
        $duplicateService = app(DuplicateCandidateService::class);
        $resumeFingerprint = $duplicateService->fingerprint($file);
        $assessment = $duplicateService->assess($candidate->partner_id, $candidate->email, $candidate->phone_number, $resumeFingerprint, $candidate->id);
        $path = $file->store('resumes', 'public');

        // Delete old resume if exists
        if ($candidate->resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($candidate->resume_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($candidate->resume_path);
        }

        $candidate->update(['resume_path' => $path, 'resume_fingerprint' => $resumeFingerprint]);
        $duplicateService->quarantine($candidate, $assessment, 'candidate_resume_update_web');

        return redirect()->back()->with('success', 'Candidate resume updated successfully!');
    }

    public function showApplyForm(Job $job)
    {
        if ($job->status !== 'approved' || !$job->isVisibleToPartner(Auth::user())) {
            abort(404, 'This job is currently not available.');
        }

        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();
        $poolIds = $this->partnerPoolIds($partner);
        $candidates = Candidate::whereIn('partner_id', $poolIds)
                                ->latest()
                                ->get();
        $appliedCandidateIds = JobApplication::where('job_id', $job->id)
            ->whereIn('candidate_id', $candidates->pluck('id'))
            ->pluck('candidate_id')
            ->map(fn ($candidateId) => (int) $candidateId)
            ->all();

        return view('partner.jobs.apply', [
            'job' => $job,
            'candidates' => $candidates,
            'appliedCandidateIds' => $appliedCandidateIds,
        ]);
    }

    public function submitApplication(Request $request, Job $job)
    {
        if ($job->status !== 'approved' || !$job->isVisibleToPartner(Auth::user())) {
            return back()->with('error', 'This job is no longer accepting applications.');
        }

        $request->validate([
            'candidate_ids' => 'required|array|min:1',
            'candidate_ids.*' => 'exists:candidates,id',
            'interview_at' => $job->screening_required ? 'nullable|date' : 'required|date|after:now',
        ]);

        $partner = Auth::user();
        $ownerId = $partner->partnerOwnerId();
        $poolIds = $this->partnerPoolIds($partner);
        $requestedCandidateIds = collect($request->input('candidate_ids'))
            ->map(fn ($candidateId) => (int) $candidateId)
            ->unique()
            ->values();
        $candidateIds = Candidate::whereIn('partner_id', $poolIds)
            ->whereIn('id', $requestedCandidateIds)
            ->pluck('id')
            ->map(fn ($candidateId) => (int) $candidateId);

        if ($candidateIds->isEmpty()) {
            return redirect()->back()->with('error', 'No valid candidates from your candidate pool were selected.');
        }

        $alreadySubmittedIds = JobApplication::where('job_id', $job->id)
            ->whereIn('candidate_id', $candidateIds)
            ->pluck('candidate_id')
            ->map(fn ($candidateId) => (int) $candidateId);
        $newCandidateIds = $candidateIds->diff($alreadySubmittedIds)->values();

        if ($newCandidateIds->isEmpty()) {
            return redirect()->back()->with('error', 'All selected candidates have already been submitted for this job.');
        }

        // Email is mandatory for every submitted candidate (assessment OTP is
        // email-only). Block any selected candidate that has no email on file.
        $missingEmail = Candidate::whereIn('id', $newCandidateIds)
            ->where(fn ($q) => $q->whereNull('email')->orWhere('email', ''))
            ->pluck('first_name', 'id');
        if ($missingEmail->isNotEmpty()) {
            $names = collect($missingEmail)->take(5)->implode(', ');
            return redirect()->back()->with('error',
                'Email is required to submit a candidate. Please add an email for: ' . $names .
                ($missingEmail->count() > 5 ? ' and others' : '') . ', then submit again.');
        }

        // 3. Plan-based monthly submission cap.
        $ownerPlan = \App\Models\User::where('id', $ownerId)->value('partner_plan') ?? 'Free';
        $plan = \App\Models\PartnerPlan::where('name', $ownerPlan)->first();
        if ($plan && $plan->monthly_submission_limit !== null) {
            $cap = $plan->monthly_submission_limit;
            // Count submissions this month across the whole team
            $teamIds = \App\Models\User::where('parent_partner_id', $ownerId)->pluck('id')->push($ownerId)->all();
            $thisMonth = JobApplication::whereIn('submitted_by_user_id', $teamIds)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
            // Previously submitted candidates must never consume quota again.
            $attemptingToSubmit = $newCandidateIds->count();
            if (($thisMonth + $attemptingToSubmit) > $cap) {
                return redirect()->back()->with('error',
                    "Your {$ownerPlan} plan allows {$cap} submissions / month. You've used {$thisMonth}. Upgrade your plan to submit more.");
            }
        }

        $submittedCount = 0;
        $blockedCount = 0;
        $duplicateService = app(DuplicateCandidateService::class);
        // Screening branching: Mode 3 (client unchecked) skips admin queue.
        $initialStatus = ($job->screening_required ?? true) ? 'Pending Review' : 'Approved';

        foreach ($newCandidateIds as $candidateId) {
            // Verify candidate belongs to partner
            $candidate = Candidate::where('id', $candidateId)
                                  ->whereIn('partner_id', $poolIds)
                                  ->first();
            
            if (!$candidate) continue;

            if (!$duplicateService->canSubmit($candidate)) {
                $blockedCount++;
                continue;
            }

            $identityConflict = $duplicateService->submissionConflict($candidate, $job);
            if ($identityConflict) {
                $duplicateService->quarantineForJobConflict($candidate, $identityConflict, $job);
                $blockedCount++;
                continue;
            }

            $existingApplication = JobApplication::where('job_id', $job->id)
                                                 ->where('candidate_id', $candidateId)
                                                 ->first();
            
            if (!$existingApplication) {
                $application = JobApplication::create([
                    'job_id'              => $job->id,
                    'candidate_id'        => $candidateId,
                    'status'              => $initialStatus,
                    'hiring_status'       => $job->screening_required ? null : 'Interview Scheduled',
                    'interview_at'        => $job->screening_required ? null : Carbon::parse($request->input('interview_at')),
                    'submitted_by_user_id' => Auth::id(),
                ]);
                $submittedCount++;

                // If the job carries questionnaire stages, create the candidate's
                // assessment session and email them the magic link.
                try {
                    app(AssessmentSessionService::class)->startForApplication($application);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        if ($submittedCount > 0) {
            $message = $submittedCount . ' ' . Str::plural('application', $submittedCount) . ' submitted successfully!';
            if ($blockedCount > 0) $message .= " {$blockedCount} duplicate candidate(s) were blocked and sent for Superadmin review.";
            return redirect()->route('partner.jobs')->with('success', $message);
        } else {
            return redirect()->back()->with('error', $blockedCount > 0
                ? 'Submission blocked: selected candidate(s) are duplicates or awaiting Superadmin review.'
                : 'All selected candidates have already been submitted for this job.');
        }
    }
}
