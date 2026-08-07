<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\InterviewRound;
use App\Models\User;
// Added missing models for Job Creation
use App\Models\JobCategory; 
use App\Models\EducationLevel;
// Notifications
use App\Notifications\CandidateRejectedByClient;
use App\Notifications\CandidateSelected;
use App\Notifications\InterviewScheduled;
use App\Notifications\CandidateJoined; 
use App\Notifications\CandidateDidNotJoin;
use App\Notifications\CandidateLeft;
use App\Notifications\CandidateLifecycleUpdated;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Services\AiSensyWhatsAppService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Dompdf\Dompdf;
use Dompdf\Options;
use Carbon\Carbon;
use App\Services\SuperadminActivityService;
use App\Services\InvoiceDocumentService;

class ClientController extends Controller
{
    /**
     * Show the client dashboard.
     */
    public function index(SuperadminActivityService $activityService)
    {
        $activityService->checkBillingDueAlerts();

        $client = Auth::user();
        
        $allJobs = Job::where('user_id', $client->id)
                    ->with(['educationLevel', 'jobApplications'])
                    ->latest()
                    ->get();
        
        $totalJobs = $allJobs->count();
        $activeJobs = $allJobs->where('status', 'approved')->count();
        
        // Count only approved candidate submissions
        $totalApplicants = JobApplication::whereIn('job_id', $allJobs->pluck('id'))
                                    ->where('status', 'Approved')
                                    ->count();
        
        $totalHires = JobApplication::whereIn('job_id', $allJobs->pluck('id'))
                                ->whereIn('hiring_status', ['Selected', 'Joined'])
                                ->count();

        // --- Daily Pulse Data ---
        $todayInterviews = JobApplication::whereIn('job_id', $allJobs->pluck('id'))
            ->whereDate('interview_at', Carbon::today())
            ->count();

        // Calculate actual outstanding and paid invoice amounts
        $allBillingSnapshot = JobApplication::where('hiring_status', 'Selected')
            ->whereNotNull('joining_date')
            ->whereHas('job', fn ($q) => $q->where('user_id', $client->id))
            ->get()
            ->map(fn ($a) => $a->billingSnapshot());

        $totalOutstandingInvoices = $allBillingSnapshot->whereIn('status', ['Raised', 'Overdue', 'Due to Raise'])->sum('invoice_amount');
        $totalPaidInvoices = $allBillingSnapshot->where('status', 'Paid')->sum('invoice_amount');

        $dueInvoicesCount = 0;
        foreach ($allBillingSnapshot as $bill) {
            if ($bill['status'] === 'Overdue') {
                $dueInvoicesCount++;
            }
        }

        // Fetch actual recent candidate applications for recent activity feed
        $recentApplications = JobApplication::whereIn('job_id', $allJobs->pluck('id'))
            ->where('status', 'Approved')
            ->with(['candidate', 'job'])
            ->latest()
            ->take(3)
            ->get();

        // Fetch actual upcoming scheduled interviews
        $recentInterviews = JobApplication::whereIn('job_id', $allJobs->pluck('id'))
            ->whereNotNull('interview_at')
            ->with(['candidate', 'job'])
            ->latest('interview_at')
            ->take(2)
            ->get();

        // ============================================================
        // REAL-DATA WIDGETS (replacing the mock dashboard values)
        // ============================================================
        $jobIds = $allJobs->pluck('id');

        // Client-visible pipeline: screened jobs expose only Admin-approved profiles;
        // direct-interview jobs expose their submissions immediately.
        $visiblePipeline = JobApplication::whereIn('job_id', $jobIds)->where(function ($visibility) {
            $visibility->where('status', 'Approved')
                ->orWhereHas('job', fn ($jobQuery) => $jobQuery->where('screening_required', false));
        });
        $funnelApplied     = (clone $visiblePipeline)->count();
        $funnelShortlisted = (clone $visiblePipeline)->whereIn('hiring_status', ['Shortlisted', 'shortlisted'])->count();
        $funnelMaybe       = (clone $visiblePipeline)->where('hiring_status', 'Maybe')->count();
        $funnelInterview   = (clone $visiblePipeline)->whereIn('hiring_status', ['Interview Scheduled', 'Interviewed', 'No-Show'])->count();
        // Selected is the current system's offer stage: CTC/joining details are recorded here.
        $funnelOffered     = (clone $visiblePipeline)->where('hiring_status', 'Selected')->count();
        $funnelJoined      = (clone $visiblePipeline)->where('joined_status', 'Joined')->count();

        $funnel = [
            ['label' => 'Applied',     'count' => $funnelApplied,     'link' => route('client.applications.index')],
            ['label' => 'Shortlisted', 'count' => $funnelShortlisted, 'link' => route('client.applications.index', ['status' => 'Shortlisted'])],
            ['label' => 'Maybe',       'count' => $funnelMaybe,       'link' => route('client.applications.index', ['status' => 'Maybe'])],
            ['label' => 'Interviewed', 'count' => $funnelInterview,   'link' => route('client.applications.index', ['hiring_status' => 'Interviewed'])],
            ['label' => 'Offered',     'count' => $funnelOffered,     'link' => route('client.applications.index', ['hiring_status' => 'Selected'])],
            ['label' => 'Joined',      'count' => $funnelJoined,      'link' => route('client.applications.index', ['joined_status' => 'Joined'])],
        ];

        // --- Interview Activity Trend (last 14 days, real scheduled interviews per-day) ---
        $trendStart = Carbon::today()->subDays(13);
        $rawTrend = JobApplication::whereIn('job_id', $jobIds)
            ->whereNotNull('interview_at')
            ->where('interview_at', '>=', $trendStart)
            ->selectRaw('DATE(interview_at) as d, COUNT(*) as c')
            ->groupBy('d')->pluck('c', 'd');
        $submissionTrend = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $submissionTrend[] = [
                'label' => $day->isToday() ? 'Today' : $day->format('d M'),
                'count' => (int) ($rawTrend[$day->toDateString()] ?? 0),
            ];
        }

        // --- Daily Pulse (real client-relevant metrics) ---
        // Base ratios on the total client-visible pipeline. 'Shortlisted' is an
        // optional stage (candidates can be Selected without being Shortlisted),
        // so using it as the denominator produced >100% figures.
        $pipelineBase = max(1, $funnelApplied);
        $selectionRatio = min(100, (int) round(($funnelOffered + $funnelJoined) / $pipelineBase * 100));

        // Client response = % of surfaced candidates the client has acted on.
        $actedOn = JobApplication::whereIn('job_id', $jobIds)
            ->where('status', 'Approved')
            ->where(function ($q) {
                $q->whereNotNull('hiring_status')->orWhereNotNull('joined_status');
            })->count();
        $clientResponseRate = min(100, (int) round($actedOn / $pipelineBase * 100));
        
        // Pending follow-ups = approved candidates with no hiring action yet
        $pendingFollowUps = JobApplication::whereIn('job_id', $jobIds)
            ->where('status', 'Approved')
            ->whereNull('hiring_status')
            ->whereNull('joined_status')
            ->count();

        $dailyPulse = [
            ['label' => 'Interviews Today',  'value' => $todayInterviews,          'icon' => 'fa-video',          'color' => 'blue',    'link' => route('client.interviews.calendar')],
            ['label' => 'Awaiting Review',   'value' => $pendingFollowUps,         'icon' => 'fa-user-clock',     'color' => 'indigo',  'link' => route('client.applications.index', ['status' => 'Approved'])],
            ['label' => 'Selection Ratio',   'value' => $selectionRatio.'%',       'icon' => 'fa-chart-line',     'color' => 'emerald', 'link' => route('client.applications.index', ['view' => 'hires'])],
            ['label' => 'Offers Extended',   'value' => $funnelOffered,            'icon' => 'fa-handshake',      'color' => 'amber',   'link' => route('client.applications.index', ['hiring_status' => 'Selected'])],
            ['label' => 'Candidates Hired',  'value' => $funnelJoined,             'icon' => 'fa-trophy',         'color' => 'rose',    'link' => route('client.applications.index', ['joined_status' => 'Joined'])],
        ];

        // --- Top Requirements (jobs ranked by submission count) ---
        $topRequirements = $allJobs->map(function ($job) {
            return [
                'title'       => $job->title,
                'company'     => $job->company_name,
                'location'    => $job->location,
                'submissions' => $job->jobApplications->where('status', 'Approved')->count(),
                'id'          => $job->id,
            ];
        })->sortByDesc('submissions')->take(4)->values();

        // --- Performance Overview (real ratios) ---
        $performance = [
            'selection_ratio'   => $selectionRatio,
            'response_rate'     => $clientResponseRate,
            'fill_rate'         => min(100, $totalJobs > 0 ? (int) round($funnelJoined / max($totalJobs, 1) * 100) : 0),
            'interview_rate'    => min(100, (int) round($funnelInterview / $pipelineBase * 100)),
        ];

        // --- Sourcing Channel Performance (real: submissions split by channel) ---
        // Partner/vendor-sourced submissions carry a vendor-pool candidate_id;
        // direct-registration candidates apply with a candidate_user_id.
        $channelWin = function ($channelCol) use ($jobIds) {
            return JobApplication::whereIn('job_id', $jobIds)
                ->whereNotNull($channelCol)
                ->where(fn ($q) => $q->where('hiring_status', 'Selected')->orWhere('joined_status', 'Joined'))
                ->count();
        };
        $partnerSubs = JobApplication::whereIn('job_id', $jobIds)->whereNotNull('candidate_id')->count();
        $directSubs  = JobApplication::whereIn('job_id', $jobIds)->whereNotNull('candidate_user_id')->count();
        $channelTotal = max(1, $partnerSubs + $directSubs);
        $sourcingChannels = [
            [
                'label' => 'Sourcing Partner Networks',
                'count' => $partnerSubs,
                'share' => (int) round($partnerSubs / $channelTotal * 100),
                'rate'  => $partnerSubs > 0 ? (int) round($channelWin('candidate_id') / $partnerSubs * 100) : 0,
                'color' => 'emerald',
            ],
            [
                'label' => 'Direct Applicant Pool',
                'count' => $directSubs,
                'share' => (int) round($directSubs / $channelTotal * 100),
                'rate'  => $directSubs > 0 ? (int) round($channelWin('candidate_user_id') / $directSubs * 100) : 0,
                'color' => 'blue',
            ],
        ];
        $sourcingTotal = $partnerSubs + $directSubs;

        // Retrieve paginated jobs list for dashboard display
        $jobs = Job::where('user_id', $client->id)
                    ->with(['educationLevel', 'jobApplications'])
                    ->latest()
                    ->paginate(10);

        return view('client.dashboard', [
            'client' => $client,
            'jobs'   => $jobs,
            'totalJobs' => $totalJobs,
            'activeJobs' => $activeJobs,
            'totalApplicants' => $totalApplicants,
            'totalHires' => $totalHires,
            'awaitingReview' => $pendingFollowUps,
            'todayInterviews' => $todayInterviews,
            'dueInvoicesCount' => $dueInvoicesCount,
            'totalOutstandingInvoices' => $totalOutstandingInvoices,
            'totalPaidInvoices' => $totalPaidInvoices,
            'recentApplications' => $recentApplications,
            'recentInterviews' => $recentInterviews,
            // Real-data widgets
            'funnel' => $funnel,
            'funnelShortlisted' => $funnelShortlisted,
            'funnelJoined' => $funnelJoined,
            'submissionTrend' => $submissionTrend,
            'dailyPulse' => $dailyPulse,
            'topRequirements' => $topRequirements,
            'performance' => $performance,
            'sourcingChannels' => $sourcingChannels,
            'sourcingTotal' => $sourcingTotal,
        ]);
    }

    // --- NEW: JOB CREATION METHODS ---

    public function listJobs(Request $request)
    {
        $clientId = Auth::id();
        $query = \App\Models\Job::where('user_id', $clientId)
            ->withCount(['jobApplications' => function($q) {
                $q->where('status', 'Approved');
            }])
            ->withCount([
                'jobApplications as shortlisted_responses_count' => function ($q) {
                    $q->where('status', 'Approved')
                        ->whereIn('hiring_status', ['Shortlisted', 'shortlisted']);
                },
                'jobApplications as maybe_responses_count' => function ($q) {
                    $q->where('status', 'Approved')->where('hiring_status', 'Maybe');
                },
                'jobApplications as rejected_responses_count' => function ($q) {
                    $q->where('status', 'Approved')->where('hiring_status', 'Client Rejected');
                },
            ]);

        // Map UI status keys → actual DB values so the tabs filter correctly.
        // DB stores: approved | pending_approval | on_hold | closed | rejected
        $filterMap = [
            'approved' => 'approved',
            'pending'  => 'pending_approval',
            'hold'     => 'on_hold',
            'closed'   => 'closed',
            'rejected' => 'rejected',
        ];
        if ($request->filled('status') && isset($filterMap[$request->status])) {
            $query->where('status', $filterMap[$request->status]);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where('title', 'like', "%{$search}%");
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%'.trim((string) $request->input('location')).'%');
        }

        if ($request->filled('keywords')) {
            $keywords = trim((string) $request->input('keywords'));
            $query->where(function ($jobQuery) use ($keywords) {
                $jobQuery->where('skills_required', 'like', "%{$keywords}%")
                    ->orWhere('description', 'like', "%{$keywords}%");
            });
        }

        if ($request->input('flow') === 'screening') {
            $query->where('screening_required', true);
        } elseif ($request->input('flow') === 'direct') {
            $query->where('screening_required', false);
        }

        $sort = $request->input('sort', 'recent');
        match ($sort) {
            'oldest' => $query->orderBy('created_at'),
            'title' => $query->orderBy('title'),
            'responses' => $query->orderByDesc('job_applications_count'),
            default => $query->orderByDesc('created_at'),
        };

        $jobs = $query->paginate(10)->withQueryString();

        $base = \App\Models\Job::where('user_id', $clientId);
        $counts = [
            'all'      => (clone $base)->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'pending'  => (clone $base)->where('status', 'pending_approval')->count(),
            'hold'     => (clone $base)->where('status', 'on_hold')->count(),
            'closed'   => (clone $base)->where('status', 'closed')->count(),
        ];

        return view('client.jobs.index', compact('jobs', 'counts'));
    }

    /**
     * Show the form to create a new job.
     */
    public function createJob()
    {
        return view('client.jobs.create', array_merge($this->jobFormDropdowns(), [
            'job' => null,
            'formMode' => 'create',
        ]));
    }

    public function editJob(Job $job)
    {
        $this->ensureClientCanEditJob($job);

        return view('client.jobs.create', array_merge($this->jobFormDropdowns(), [
            'job' => $job,
            'formMode' => 'edit',
        ]));
    }

    private function jobFormDropdowns(): array
    {
        $categories = Cache::remember('job_categories', 3600, fn () => JobCategory::orderBy('name')->get());
        $educationLevels = Cache::remember('education_levels', 3600, fn () => EducationLevel::orderBy('name')->get());
        $indianCities = Cache::remember('indian_cities', 86400, function () {
            $citiesPath = resource_path('data/indian-cities.json');
            if (!is_file($citiesPath)) {
                return [];
            }
            $decoded = json_decode((string) file_get_contents($citiesPath), true);
            return collect(is_array($decoded) ? $decoded : [])
                ->filter(fn ($city) => is_string($city) && trim($city) !== '')
                ->map(fn ($city) => trim($city))
                ->unique()->sort()->values()->all();
        });

        return compact('categories', 'educationLevels', 'indianCities');
    }

    /**
     * Store the newly created job.
     */
    public function storeJob(Request $request)
    {
        $validated = $this->validateClientJob($request);

        $salary = $this->formatSalaryRange(
            $validated['min_salary'] ?? null,
            $validated['max_salary'] ?? null
        );

        // Map the new vendor_assignment_mode onto the legacy partner_visibility column.
        $assignMode = $validated['vendor_assignment_mode'] ?? 'open';
        $legacyVisibility = $assignMode === 'open' ? 'all' : 'selected';

        $job = Job::create([
            'user_id' => Auth::id(),
            'company_name' => Auth::user()->name,
            'status' => 'pending_approval',
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'location' => $validated['location'],
            'salary' => $salary,
            'job_type' => $validated['job_type'],
            'description' => $this->sanitizeJobDescription($validated['description']),
            'gender_preference' => $validated['gender_preference'],
            'min_age' => $validated['min_age'] ?? null,
            'max_age' => $validated['max_age'] ?? null,

            'min_experience' => $validated['min_experience'],
            'max_experience' => $validated['max_experience'],
            'experience_level_id' => null,

            'education_level_id' => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'] ?? null,
            'skills_required' => (string) ($request->skills_required ?? ''),
            'company_website' => (string) ($request->company_website ?? ''),
            'openings' => $request->openings ?? 1,
            'partner_visibility' => $legacyVisibility,
            'vendor_assignment_mode' => $assignMode,
            'max_vendors_per_job' => $validated['max_vendors_per_job'] ?? null,
            'commercial_source' => $validated['commercial_source'],
            'fee_type' => $validated['commercial_source'] === 'manual' ? $validated['manual_fee_type'] : null,
            'fee_amount' => $validated['commercial_source'] === 'manual' ? $validated['manual_fee_amount'] : null,
            // fee_* records the client's job-level commercial proposal.
            // payout_amount is reserved for the final partner payout set by Admin.
            'payout_amount' => null,
            'client_payout_days' => $validated['commercial_source'] === 'manual' ? $validated['minimum_stay_days'] : null,
            'replacement_period_days' => $validated['commercial_source'] === 'manual' ? $validated['replacement_guarantee_days'] : null,
            'minimum_stay_days' => $validated['commercial_source'] === 'manual' ? $validated['minimum_stay_days'] : null,
            'replacement_guarantee_days' => $validated['commercial_source'] === 'manual' ? $validated['replacement_guarantee_days'] : null,
            'is_company_confidential' => (bool) ($validated['is_company_confidential'] ?? false),
            'screening_required' => $this->shouldRequireScreening($validated),
            // Extended job-detail fields
            'work_mode'         => $validated['work_mode'] ?? null,
            'shift'             => $validated['shift'] ?? null,
            'specialization'    => $validated['specialization'] ?? null,
            'notice_period'     => $validated['notice_period'] ?? null,
            'languages'         => $validated['languages'] ?? null,
            'industry'          => $validated['industry'] ?? null,
            'department'        => $validated['department'] ?? null,
            'reporting_manager' => $validated['reporting_manager'] ?? null,
            'travel_required'   => array_key_exists('travel_required', $validated) ? (bool) $validated['travel_required'] : null,
            'benefits'          => $validated['benefits'] ?? null,
        ]);

        $this->sendJobPostedCommercialEmails($job, Auth::user());

        // Resolve the allowed-partner list according to mode
        if ($assignMode === 'preferred') {
            $preferredIds = Auth::user()->preferredVendors()->pluck('users.id')->all();
            $job->allowedPartners()->sync($preferredIds);
        } elseif ($assignMode === 'selected') {
            $job->allowedPartners()->sync($request->input('allowed_partners', []));
        }

        return redirect()
            ->route('client.jobs.index')
            ->with([
                'job_posted' => 'Your job has been posted successfully and sent to SimplyHiree for approval. A confirmation email has also been sent.',
            ]);
    }

    public function updateJob(Request $request, Job $job)
    {
        $this->ensureClientCanEditJob($job);

        $validated = $this->validateClientJob($request);

        $salary = $this->formatSalaryRange(
            $validated['min_salary'] ?? null,
            $validated['max_salary'] ?? null
        );

        $job->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'location' => $validated['location'],
            'salary' => $salary,
            'job_type' => $validated['job_type'],
            'description' => $this->sanitizeJobDescription($validated['description']),
            'gender_preference' => $validated['gender_preference'],
            'min_age' => $validated['min_age'] ?? null,
            'max_age' => $validated['max_age'] ?? null,
            'min_experience' => $validated['min_experience'],
            'max_experience' => $validated['max_experience'],
            'experience_level_id' => null,
            'education_level_id' => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'] ?? null,
            'skills_required' => (string) ($validated['skills_required'] ?? ''),
            'company_website' => (string) ($validated['company_website'] ?? ''),
            'openings' => $validated['openings'] ?? 1,
            'commercial_source' => $validated['commercial_source'],
            'fee_type' => $validated['commercial_source'] === 'manual' ? $validated['manual_fee_type'] : null,
            'fee_amount' => $validated['commercial_source'] === 'manual' ? $validated['manual_fee_amount'] : null,
            // Preserve Admin's final partner payout while the edited client
            // proposal returns to the approval queue.
            'client_payout_days' => $validated['commercial_source'] === 'manual' ? $validated['minimum_stay_days'] : null,
            'replacement_period_days' => $validated['commercial_source'] === 'manual' ? $validated['replacement_guarantee_days'] : null,
            'is_company_confidential' => (bool) ($validated['is_company_confidential'] ?? false),
            'screening_required' => $this->shouldRequireScreening($validated),
            'status' => 'pending_approval',
            // Extended job-detail fields
            'work_mode'         => $validated['work_mode'] ?? null,
            'shift'             => $validated['shift'] ?? null,
            'specialization'    => $validated['specialization'] ?? null,
            'notice_period'     => $validated['notice_period'] ?? null,
            'languages'         => $validated['languages'] ?? null,
            'industry'          => $validated['industry'] ?? null,
            'department'        => $validated['department'] ?? null,
            'reporting_manager' => $validated['reporting_manager'] ?? null,
            'travel_required'   => array_key_exists('travel_required', $validated) ? (bool) $validated['travel_required'] : null,
            'benefits'          => $validated['benefits'] ?? null,
        ]);

        return redirect()->route('client.dashboard')->with('success', 'Pending job updated successfully.');
    }

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

    private function validateClientJob(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:job_categories,id',
            'location' => 'required|string|max:255',
            'job_type' => 'required|string|max:100',
            'description' => 'required|string',
            'min_salary' => 'nullable|integer|min:0|required_with:max_salary',
            'max_salary' => 'nullable|integer|min:0|gte:min_salary|required_with:min_salary',
            'min_experience' => 'required|integer|min:0',
            'max_experience' => 'required|integer|gte:min_experience|max:50',
            'education_level_id' => 'required|exists:education_levels,id',
            'application_deadline' => 'nullable|date',
            'skills_required' => 'nullable|string',
            'company_website' => 'nullable|url',
            'openings' => 'nullable|integer|min:1',
            'gender_preference' => 'required|string|in:Any,Male,Female,Other',
            'min_age' => 'nullable|integer|min:18|max:80',
            'max_age' => 'nullable|integer|min:18|max:80|gte:min_age',
            'commercial_source' => 'required|in:simplyhire,manual',
            'manual_fee_type' => 'required_if:commercial_source,manual|nullable|in:flat,percentage',
            'manual_fee_amount' => 'required_if:commercial_source,manual|nullable|numeric|min:0',
            'minimum_stay_days' => 'required_if:commercial_source,manual|nullable|integer|min:0|max:365',
            'replacement_guarantee_days' => 'required_if:commercial_source,manual|nullable|integer|min:0|max:365',
            'vendor_assignment_mode' => 'nullable|in:open,preferred,selected',
            'max_vendors_per_job'    => 'nullable|integer|min:1|max:50',
            'allowed_partners'       => 'nullable|array',
            'allowed_partners.*'     => 'integer|exists:users,id',
            'is_company_confidential' => 'nullable|boolean',
            'screening_required' => 'nullable|boolean',
            // Extended job-detail fields (all optional)
            'work_mode'         => 'nullable|string|in:On-site,Hybrid,Remote,Field Job,Work From Home (WFH)',
            'shift'             => 'nullable|string|in:Day Shift,Night Shift,Rotational Shift,Flexible Shift,Weekend Shift',
            'specialization'    => 'nullable|string|max:255',
            'notice_period'     => 'nullable|string|max:100',
            'languages'         => 'nullable|string|max:255',
            'industry'          => 'nullable|string|max:255',
            'department'        => 'nullable|string|max:255',
            'reporting_manager' => 'nullable|string|max:255',
            'travel_required'   => 'nullable|boolean',
            'benefits'          => 'nullable|array',
            'benefits.*'        => 'string|in:PF,ESIC,Insurance,Food,Transport,Accommodation,Laptop,Mobile,Joining Bonus,Relocation',
        ]);
    }

    private function sendJobPostedCommercialEmails(Job $job, User $client): void
    {
        $clientSubject = 'Job posted successfully: '.$job->title;
        try {
            Mail::send('emails.job-posted-commercial', [
                'job' => $job,
                'client' => $client,
                'isAdmin' => false,
                'subjectLine' => $clientSubject,
                'actionUrl' => route('client.jobs.index'),
                'actionLabel' => 'View my jobs',
            ], function ($mail) use ($client, $clientSubject) {
                $mail->to($client->email, $client->name)->subject($clientSubject);
            });
        } catch (\Throwable $exception) {
            report($exception);
        }

        $adminSubject = 'New job awaiting approval: '.$job->title;
        try {
            Mail::send('emails.job-posted-commercial', [
                'job' => $job,
                'client' => $client,
                'isAdmin' => true,
                'subjectLine' => $adminSubject,
                'actionUrl' => route('admin.jobs.show', $job),
                'actionLabel' => 'Review job',
            ], function ($mail) use ($adminSubject) {
                $mail->to([
                    'simplyhire1@gmail.com',
                    'client@simplyhiree.com',
                ])->subject($adminSubject);
            });
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function shouldRequireScreening(array $job): bool
    {
        // The client form calculates the platform's recommended route and submits
        // the validated result so the chosen workflow is persisted with the job.
        if (array_key_exists('screening_required', $job)) {
            return (bool) $job['screening_required'];
        }

        $configured = \App\Models\MarketplaceSetting::where('key', 'screening_mandatory')->first();
        if ($configured) {
            return (bool) filter_var($configured->value, FILTER_VALIDATE_BOOLEAN);
        }
        $title = strtolower((string) ($job['title'] ?? ''));
        $directTerms = ['bpo', 'telecaller', 'sales executive', 'walk-in', 'walkin', 'urgent'];
        return !((int) ($job['openings'] ?? 1) >= 10 || ((int) ($job['min_experience'] ?? 0) <= 1 && collect($directTerms)->contains(fn ($term) => str_contains($title, $term))));
    }

    /**
     * Client requests a replacement for a candidate who joined and left
     * before the replacement-guarantee window. One-shot per application.
     */
    public function requestCandidateReplacement(Request $request, \App\Models\JobApplication $application)
    {
        $clientId = Auth::id();
        $application->loadMissing(['job', 'candidate.partner']);
        if (!$application->job || (int) $application->job->user_id !== (int) $clientId) {
            abort(403, 'Unauthorized.');
        }
        if (!$application->joining_date) {
            return back()->with('error', 'Replacement can only be requested for candidates who joined the role.');
        }
        if (!$application->left_at) {
            return back()->with('error', 'This candidate has not been marked as Left. Mark them as left before requesting a replacement.');
        }
        if ($application->replacement_requested_at) {
            return back()->with('error', 'A replacement has already been requested for this candidate.');
        }
        // Prefer the locked-in window stamped at hire-time from the resolved
        // commercial row; fall back to the job-level posting value.
        $guaranteeDays = (int) ($application->replacement_window_days
            ?? $application->job->replacement_guarantee_days
            ?? \App\Models\MarketplaceSetting::valueOf('replacement_period_days', 0));
        if ($guaranteeDays > 0) {
            $tenure = $application->joining_date->diffInDays($application->left_at);
            if ($tenure > $guaranteeDays) {
                return back()->with('error', "Candidate worked {$tenure} day(s), which is beyond the {$guaranteeDays}-day replacement-guarantee window.");
            }
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $partnerWindowDays = 15; // Default partner window for replacement.
        $application->update([
            'replacement_requested_at'        => now(),
            'replacement_reason'              => $data['reason'] ?? null,
            'replacement_status'              => 'window_open',
            'partner_replacement_window_days' => $partnerWindowDays,
            'replacement_deadline'            => now()->addDays($partnerWindowDays),
        ]);

        // Notify the sourcing partner via the existing database channel.
        $partner = $application->candidate?->partner;
        if ($partner) {
            try {
                $partner->notify(new \App\Notifications\ReplacementRequested($application));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('ReplacementRequested notify failed: '.$e->getMessage());
            }
        }

        return back()->with('success', "Replacement request raised. The sourcing partner has {$partnerWindowDays} days to provide a replacement.");
    }

    private function ensureClientCanEditJob(Job $job): void
    {
        if ((int) $job->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!in_array((string) $job->status, ['pending_approval', 'approved'])) {
            abort(403, 'Only pending or active approved jobs can be edited.');
        }
    }

    /**
     * Sanitize Quill editor HTML — keep formatting tags, strip scripts and event handlers.
     */
    private function sanitizeJobDescription(?string $html): ?string
    {
        if (!$html) return $html;
        $allowed = '<p><br><b><strong><i><em><u><s><strike><ul><ol><li><h2><h3><blockquote><a><span>';
        $clean = strip_tags($html, $allowed);
        // Drop any on*="..." or on*='...' event handler attributes
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*"(?:[^"\\\\]|\\\\.)*"/i', '', $clean);
        $clean = preg_replace("/\s+on[a-z]+\s*=\s*'(?:[^'\\\\]|\\\\.)*'/i", '', $clean);
        // Strip javascript: in href
        $clean = preg_replace('/href\s*=\s*"\s*javascript:[^"]*"/i', 'href="#"', $clean);
        $clean = preg_replace("/href\s*=\s*'\s*javascript:[^']*'/i", "href='#'", $clean);
        return $clean;
    }

    /**
     * Client requests an approved job be deactivated. Awaits Superadmin action.
     */
    public function requestDeactivation(Request $request, Job $job)
    {
        if ((int) $job->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if ((string) $job->status !== 'approved') {
            return back()->with('error', 'Only approved jobs can be requested for deactivation.');
        }

        if ($job->deactivation_requested_at) {
            return back()->with('error', 'Deactivation has already been requested for this job.');
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $job->update([
            'deactivation_requested_at' => now(),
            'deactivation_reason'       => $data['reason'] ?? null,
        ]);

        return back()->with('success', 'Deactivation requested. A Superadmin will review it shortly.');
    }

    /**
     * Client cancels their own pending deactivation request.
     */
    public function cancelDeactivationRequest(Job $job)
    {
        if ((int) $job->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        if (!$job->deactivation_requested_at) {
            return back()->with('error', 'No deactivation request to cancel.');
        }

        $job->update([
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
        ]);

        return back()->with('success', 'Deactivation request cancelled.');
    }

    // ---------------------------------
    
    /**
     * Show interviews scheduled for today.
     */
    public function dailySchedule()
    {
        $client = Auth::user();
        
        $todayInterviews = JobApplication::whereHas('job', function($q) use ($client){
                $q->where('user_id', $client->id);
            })
            ->whereDate('interview_at', Carbon::today())
            ->with(['job', 'candidate', 'candidateUser'])
            ->orderBy('interview_at', 'asc')
            ->get();

        return view('client.daily_interviews', compact('todayInterviews'));
    }

    /**
     * Global "All Applications" listing across every job this client owns.
     * Mirrors the admin AllApplications page but scoped to the client's jobs.
     */
    public function listAllApplications(Request $request)
    {
        $clientId = Auth::id();

        $query = JobApplication::with(['job.category', 'candidate.partner', 'candidateUser'])
            ->whereHas('job', function ($q) use ($clientId) {
                $q->where('user_id', $clientId);
            })
            // Screened jobs show only Admin-approved profiles; direct jobs show submissions immediately.
            ->where(function ($visibility) {
                $visibility->where('status', 'Approved')
                    ->orWhereHas('job', fn ($jobQuery) => $jobQuery->where('screening_required', false));
            });

        // Determine base status scoping for the funnel stages
        $statusParam = $request->input('status');
        if (in_array($statusParam, [null, '', 'all'], true)) {
            // All client-visible applications are already Super Admin screened.
        } elseif (in_array($statusParam, ['screened', 'Approved'], true)) {
            $query->where(function ($statusQuery) {
                $statusQuery->whereNull('hiring_status')->orWhere('hiring_status', '');
            });
        } elseif ($statusParam === 'Shortlisted') {
            $query->whereIn('hiring_status', ['Shortlisted', 'shortlisted']);
        } elseif ($statusParam === 'Maybe') {
            $query->where('hiring_status', 'Maybe');
        } elseif ($request->filled('status')) {
            $query->where('hiring_status', $statusParam);
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('candidate', function ($c) use ($search) {
                    $c->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('candidateUser', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            });
        }

        // Filter by joining status (Joined / Left / Did Not Join)
        if ($request->filled('joined_status')) {
            $query->where('joined_status', $request->input('joined_status'));
        }

        // Filter by hiring status (Interview Scheduled / Selected / etc.)
        if ($request->filled('hiring_status')) {
            $hiringStatus = $request->input('hiring_status');
            if ($hiringStatus === 'Interviewed') {
                $query->whereIn('hiring_status', ['Interview Scheduled', 'Interviewed', 'No-Show']);
            } else {
                $query->where('hiring_status', $hiringStatus);
            }
        }

        // Special view modes used by dashboard deep-links — keep the page
        // heading and the count in sync with the dashboard cards.
        $pageTitle    = 'All Applications';
        $pageSubtitle = 'Manage candidate pipeline';
        if ($request->input('view') === 'hires') {
            $query->whereIn('hiring_status', ['Selected', 'Joined']);
            $pageTitle    = 'Hired Candidates';
            $pageSubtitle = 'Candidates you selected or who have joined';
        }
        if ($request->input('view') === 'replacements') {
            $pageTitle = 'Replacements';
            $pageSubtitle = 'Candidates who left and may need a replacement.';
        }

        if ($request->filled('job_id')) {
            $query->where('job_id', (int) $request->input('job_id'));
        }

        if ($request->filled('partner_id')) {
            $pid = (int) $request->input('partner_id');
            $query->whereHas('candidate', function ($c) use ($pid) {
                $c->where('partner_id', $pid);
            });
        }
        if ($request->filled('partner_search')) {
            $partnerSearch = trim((string) $request->input('partner_search'));
            $query->whereHas('candidate.partner', function ($partnerQuery) use ($partnerSearch) {
                $partnerQuery->where('name', 'like', "%{$partnerSearch}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // The initial queue is action-first. Once the client applies any filter
        // or explicit sort, respect that requested result ordering instead.
        $hasActiveFilters = $request->anyFilled([
            'search', 'status', 'job_id', 'partner_id', 'partner_search',
            'date_from', 'date_to', 'joined_status', 'hiring_status', 'view', 'sort',
        ]);
        if (!$hasActiveFilters) {
            $query->orderByRaw("CASE WHEN status = 'Approved' AND (hiring_status IS NULL OR hiring_status = '') THEN 0 ELSE 1 END");
        }

        $sort = $request->input('sort', 'latest');
        if ($sort === 'name') {
            $query->orderByRaw("COALESCE((SELECT CONCAT(first_name, ' ', last_name) FROM candidates WHERE candidates.id = job_applications.candidate_id), (SELECT name FROM users WHERE users.id = job_applications.candidate_user_id), '') ASC");
        } elseif ($sort === 'modified') {
            $query->orderByDesc('updated_at');
        } else {
            $sort = 'latest';
            $query->orderByDesc('created_at');
        }

        $perPage = 10;
        $allowedPerPage = [10];
        $applications = $query->paginate($perPage)->withQueryString();

        $jobs = Job::where('user_id', $clientId)->select('id', 'title')->orderBy('title')->get();
        $partners = User::role('partner')
            ->whereNull('parent_partner_id')
            ->where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('client.applications.index', compact(
            'applications', 'jobs', 'partners', 'perPage', 'allowedPerPage', 'sort',
            'pageTitle', 'pageSubtitle'
        ));
    }

    /** Client replacement queue on its own route. */
    public function replacements(Request $request)
    {
        $request->merge(['joined_status' => 'Left', 'view' => 'replacements']);
        return $this->listAllApplications($request);
    }

    /**
     * Full notification history for the current client account.
     */
    public function notificationHistory()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);

        return view('client.notifications.index', compact('notifications'));
    }

    /**
     * Show the applicants for a specific job.
     */
    public function showApplicants(Request $request, Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $baseResponses = JobApplication::where('job_id', $job->id);
        if ($job->screening_required) {
            $baseResponses->where('status', 'Approved');
        }
        $responseCounts = [
            'all' => (clone $baseResponses)->count(),
            'shortlisted' => (clone $baseResponses)->whereIn('hiring_status', ['Shortlisted', 'shortlisted'])->count(),
            'maybe' => (clone $baseResponses)->where('hiring_status', 'Maybe')->count(),
            'rejected' => (clone $baseResponses)->where('hiring_status', 'Client Rejected')->count(),
        ];

        $query = (clone $baseResponses)->with(['candidate', 'candidateUser.profile', 'interviewRounds']);

        $responseFilter = $request->input('response', 'all');
        if ($responseFilter === 'shortlisted') {
            $query->whereIn('hiring_status', ['Shortlisted', 'shortlisted']);
        } elseif ($responseFilter === 'maybe') {
            $query->where('hiring_status', 'Maybe');
        } elseif ($responseFilter === 'rejected') {
            $query->where('hiring_status', 'Client Rejected');
        } else {
            $responseFilter = 'all';
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('candidate', function($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone_number', 'like', "%{$search}%");
                })->orWhereHas('candidateUser', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('application_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('hiring_status')) {
            $hStatus = $request->input('hiring_status');
            if ($hStatus === 'Shortlisted') {
                $query->where(function($q) {
                    $q->whereIn('hiring_status', ['Shortlisted', 'shortlisted'])
                      ->orWhereNull('hiring_status')
                      ->orWhere('hiring_status', '');
                });
            } else {
                $query->where('hiring_status', $hStatus);
            }
        }

        if ($request->filled('resume')) {
            $resumeFilter = $request->input('resume');
            if ($resumeFilter === 'has_resume') {
                $query->where(function ($q) {
                    $q->whereHas('candidate', function ($subQ) {
                        $subQ->whereNotNull('resume_path')->where('resume_path', '!=', '');
                    })->orWhereHas('candidateUser.profile', function ($subQ) {
                        $subQ->whereNotNull('resume_path')->where('resume_path', '!=', '');
                    });
                });
            } elseif ($resumeFilter === 'no_resume') {
                $query->where(function ($q) {
                    $q->where(function ($innerQ) {
                        $innerQ->whereHas('candidate', function ($subQ) {
                            $subQ->whereNull('resume_path')->orWhere('resume_path', '');
                        })->orWhereDoesntHave('candidate');
                    })->where(function ($innerQ) {
                        $innerQ->whereHas('candidateUser.profile', function ($subQ) {
                            $subQ->whereNull('resume_path')->orWhere('resume_path', '');
                        })->orWhereDoesntHave('candidateUser.profile');
                    });
                });
            }
        }

        if ($request->filled('source')) {
            $sourceFilter = $request->input('source');
            if ($sourceFilter === 'agency') {
                $query->whereNotNull('candidate_id');
            } elseif ($sourceFilter === 'direct') {
                $query->whereNull('candidate_id');
            }
        }

        $approvedApplications = $query->latest()
                                      ->paginate(20)
                                      ->withQueryString();

        return view('client.jobs.applicants', [
            'job' => $job,
            'applications' => $approvedApplications,
            'responseCounts' => $responseCounts,
            'responseFilter' => $responseFilter,
        ]);
    }
    
    public function showApplicantDetail(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id() || ($application->job->screening_required && $application->status !== 'Approved')) {
            abort(403, 'You can only view applicants who applied to your own jobs.');
        }
        $application->load(['job', 'candidate.partner', 'candidateUser.profile', 'interviewRounds']);
        return view('client.applications.show', ['application' => $application]);
    }

    public function rejectApplicant(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'Client Rejected']);
        $this->notifyAdminAndPartner(new CandidateRejectedByClient($application), $application);
        return redirect()->back()->with('success', 'Candidate has been rejected.');
    }

    public function shortlistApplicant(JobApplication $application)
    {
        $this->setApplicantReviewStatus($application, 'Shortlisted');
        return back()->with('success', 'Candidate moved to Shortlisted.');
    }

    public function markApplicantMaybe(JobApplication $application)
    {
        $this->setApplicantReviewStatus($application, 'Maybe');
        return back()->with('success', 'Candidate saved to Maybe for later review.');
    }

    public function clearApplicantReviewStatus(JobApplication $application)
    {
        $application->loadMissing('job');
        if ((int) $application->job?->user_id !== (int) Auth::id()) {
            abort(403);
        }

        if (!in_array($application->hiring_status, ['Shortlisted', 'shortlisted', 'Maybe', 'Client Rejected', 'Selected'], true) || !empty($application->joined_status)) {
            return back()->with('error', 'This decision can no longer be changed from the response queue.');
        }

        $wasSelected = $application->hiring_status === 'Selected';
        $application->update($wasSelected ? [
            'hiring_status' => 'Interviewed',
            'joining_date' => null,
            'final_ctc' => null,
            'invoice_amount' => null,
            'invoice_generated_at' => null,
            'finance_offer_status' => null,
            'offer_updated_at' => null,
            'offer_updated_by' => null,
        ] : ['hiring_status' => null]);

        return back()->with('success', $wasSelected ? 'Selection reopened. Candidate moved back to Interviewed.' : 'Candidate moved back to Awaiting review.');
    }

    private function setApplicantReviewStatus(JobApplication $application, string $status): void
    {
        $application->loadMissing('job');
        if ((int) $application->job?->user_id !== (int) Auth::id()) {
            abort(403);
        }
        if ($application->status !== 'Approved' || !empty($application->joined_status)) {
            abort(422, 'This candidate is no longer available for review.');
        }
        $application->update(['hiring_status' => $status]);
        $this->notifySourcingPartner(
            new CandidateLifecycleUpdated($application, strtolower($status)),
            $application
        );
    }

    // --- INTERVIEW SCHEDULING ---

    public function showInterviewForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.interview', ['application' => $application, 'isEdit' => false]);
    }

    public function scheduleInterview(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'interview_at'        => 'required|date|after:now',
            'meeting_provider'    => 'nullable|in:zoom,meet,teams,inperson,other',
            'meeting_link'        => 'nullable|url|max:500',
            'interview_location'  => 'nullable|string|max:255',
            'client_notes'        => 'nullable|string|max:1000',
        ]);

        $application->update([
            'hiring_status'      => 'Interview Scheduled',
            'interview_at'       => Carbon::parse($validated['interview_at']),
            'meeting_provider'   => $validated['meeting_provider'] ?? null,
            'meeting_link'       => $validated['meeting_link'] ?? null,
            'interview_location' => $validated['interview_location'] ?? null,
            'client_notes'       => $validated['client_notes'] ?? null,
            'interview_reminder_sent_at' => null, // Reset so the cron re-sends a reminder
        ]);

        $this->notifyAdminAndPartner(new InterviewScheduled($application), $application);
        $this->sendInterviewConfirmationToCandidate($application->fresh(['job', 'candidate', 'candidateUser.profile']));
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview scheduled — candidate has been notified on WhatsApp + email.');
    }

    public function editInterviewDetails(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.interview', ['application' => $application, 'isEdit' => true]);
    }

    public function updateInterviewDetails(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'interview_at'        => 'required|date|after:now',
            'meeting_provider'    => 'nullable|in:zoom,meet,teams,inperson,other',
            'meeting_link'        => 'nullable|url|max:500',
            'interview_location'  => 'nullable|string|max:255',
            'client_notes'        => 'nullable|string|max:1000',
        ]);

        $application->update([
            'interview_at'       => Carbon::parse($validated['interview_at']),
            'meeting_provider'   => $validated['meeting_provider'] ?? null,
            'meeting_link'       => $validated['meeting_link'] ?? null,
            'interview_location' => $validated['interview_location'] ?? null,
            'client_notes'       => $validated['client_notes'] ?? null,
            'interview_reminder_sent_at' => null,
        ]);

        app(SuperadminActivityService::class)->logApplicationLifecycle($application, 'client.interview_scheduled');
        $this->notifySourcingPartner(
            new CandidateLifecycleUpdated($application, 'interview_rescheduled'),
            $application
        );
        $this->sendInterviewConfirmationToCandidate($application->fresh(['job', 'candidate', 'candidateUser.profile']), true);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview updated — candidate has been re-notified.');
    }

    /**
     * Push an immediate WhatsApp + email notification to the candidate
     * with their interview time, company, location/link.
     *
     * @param  ?string  $extraNote     Override for the "Note" line in the body / email.
     *                                 Used by the multi-round flow to surface the round's
     *                                 candidate_message instead of legacy client_notes.
     * @param  ?int     $roundNumber   If passed, prepends "Round N" to the subject/body.
     */
    private function sendInterviewConfirmationToCandidate(JobApplication $application, bool $isUpdate = false, ?string $extraNote = null, ?int $roundNumber = null): void
    {
        $application->loadMissing('candidate.partner');
        $whatsapp = app(AiSensyWhatsAppService::class);
        $cand     = $application->candidate;
        $direct   = $application->candidateUser;

        $name = $cand
            ? trim(($cand->first_name ?? '') . ' ' . ($cand->last_name ?? ''))
            : ($direct?->name ?? 'Candidate');
        $email = $cand?->email ?? $direct?->email ?? null;
        $phone = $whatsapp->normalizeIndianPhone($cand?->phone_number ?? optional($direct?->profile)->phone_number ?? null);

        $partnerName = ($cand && $cand->partner) ? $cand->partner->name : null;

        $job  = $application->job;
        $company = $job?->company_name ?: (optional($job?->user)->name ?? 'the company');
        if ($job && $job->is_company_confidential) $company = 'Confidential (details on call)';

        $time = $application->interview_at?->format('h:i A, D d M Y') ?? 'TBD';
        $where = $application->meeting_link
            ?: ($application->interview_location ?: 'Details will be shared by the recruiter');
        $verb = $isUpdate ? 'updated' : 'scheduled';

        // Round labelling — used by the multi-round flow
        $roundLabel = $roundNumber ? "Round {$roundNumber}: " : '';

        // Note to surface — prefer the round's candidate_message, fallback to client_notes
        $noteText = $extraNote ?: $application->client_notes;

        $body  = ($isUpdate
            ? "Hi {$name}, your {$roundLabel}interview time has been updated."
            : "Hi {$name}, your {$roundLabel}interview has been scheduled.") . "\n\n";
        $body .= "🏢 Company: {$company}\n";
        $body .= "💼 Role: " . ($job?->title ?? '—') . "\n";
        $body .= "🕒 When: {$time}\n";
        if ($application->meeting_link) {
            $body .= "🔗 Join: {$application->meeting_link}\n";
        } elseif ($application->interview_location) {
            $body .= "📍 Where: {$application->interview_location}\n";
        }
        if ($partnerName) {
            $body .= "📞 Partner Coordinator: {$partnerName}\n";
        }
        if ($noteText) {
            $body .= "\n📝 Note: {$noteText}\n";
        }
        $body .= "\nGood luck!\n— SimplyHiree";

        // --- WhatsApp via AiSensy ---
        if ($phone) {
            try {
                $whatsapp->sendEventAlert(
                    $phone,
                    'interview_scheduled',
                    'Interview ' . $verb . ($roundNumber ? " — Round {$roundNumber}" : ''),
                    $body,
                    ['template_params' => [
                        $name,
                        ($roundNumber ? "Round {$roundNumber} — " : '') . ($job?->title ?? 'the role'),
                        $company,
                        $time,
                        $application->meeting_link ?: ($application->interview_location ?: 'TBD'),
                    ]]
                );
            } catch (\Throwable $e) {
                \Log::warning('Interview WA confirmation failed app=' . $application->id . ': ' . $e->getMessage());
            }
        }

        // --- Email ---
        if ($email) {
            try {
                Mail::send('client.interviews.email_confirmation', [
                    'name'         => $name,
                    'company'      => $company,
                    'role'         => ($roundNumber ? "Round {$roundNumber} — " : '') . ($job?->title ?? '—'),
                    'time'         => $time,
                    'meeting_link' => $application->meeting_link,
                    'location'     => $application->interview_location,
                    'notes'        => $noteText,
                    'isUpdate'     => $isUpdate,
                    'partnerName'  => $partnerName,
                ], function ($m) use ($email, $name, $verb, $roundNumber) {
                    $subject = '[SimplyHiree] Your interview is ' . $verb;
                    if ($roundNumber) $subject = "[SimplyHiree] Round {$roundNumber} interview is {$verb}";
                    $m->to($email, $name)->subject($subject);
                });
            } catch (\Throwable $e) {
                \Log::warning('Interview email confirmation failed app=' . $application->id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Interview feedback form (client-side).
     */
    public function showInterviewFeedbackForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) abort(403);
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.interview_feedback', compact('application'));
    }

    public function submitInterviewFeedback(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) abort(403);
        $validated = $request->validate([
            'interview_rating'         => 'required|integer|min:1|max:5',
            'interview_feedback'       => 'required|string|max:5000',
            'interview_recommendation' => 'required|in:select,reject,second_round,on_hold',
        ]);

        $application->update([
            'interview_rating'         => $validated['interview_rating'],
            'interview_feedback'       => $validated['interview_feedback'],
            'interview_feedback_at'    => now(),
            'interview_recommendation' => $validated['interview_recommendation'],
            'hiring_status'            => $application->hiring_status === 'Interview Scheduled' ? 'Interviewed' : $application->hiring_status,
        ]);

        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview feedback saved.');
    }

    /**
     * Interview calendar — all upcoming + past interviews this client owns.
     */
    public function interviewCalendar(Request $request)
    {
        $clientId = Auth::id();
        $events = JobApplication::with(['job', 'candidate', 'candidateUser'])
            ->whereHas('job', fn ($q) => $q->where('user_id', $clientId))
            ->whereNotNull('interview_at')
            ->orderBy('interview_at', 'asc')
            ->get();

        $selectedRange = $request->string('range')->toString();
        $selectedRange = in_array($selectedRange, ['upcoming', 'today', 'past7', 'all'], true)
            ? $selectedRange
            : null;
        $filteredInterviews = null;

        if ($selectedRange) {
            $now = now();
            $todayStart = $now->copy()->startOfDay();
            $filtered = match ($selectedRange) {
                'upcoming' => $events->filter(fn (JobApplication $event) => $event->interview_at
                    && $event->interview_at->gte($todayStart)),
                'today' => $events->filter(fn (JobApplication $event) => $event->interview_at?->isToday()),
                'past7' => $events->filter(fn (JobApplication $event) => $event->interview_at
                    && $event->interview_at->gte($todayStart->copy()->subDays(7))
                    && $event->interview_at->lt($todayStart)),
                default => $events,
            };

            $filtered = $selectedRange === 'upcoming'
                ? $filtered->sortBy('interview_at')->values()
                : $filtered->sortByDesc('interview_at')->values();
            $page = max(1, $request->integer('page', 1));
            $perPage = 10;

            $filteredInterviews = new \Illuminate\Pagination\LengthAwarePaginator(
                $filtered->forPage($page, $perPage)->values(),
                $filtered->count(),
                $perPage,
                $page,
                [
                    'path' => $request->url(),
                    'query' => $request->query(),
                ]
            );
        }

        try {
            $calendarMonth = $request->filled('month')
                ? Carbon::createFromFormat('!Y-m', $request->string('month')->toString())->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $calendarMonth = now()->startOfMonth();
        }

        $calendarStart = $calendarMonth->copy()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $calendarMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        $calendarDays = collect();

        for ($day = $calendarStart->copy(); $day->lte($calendarEnd); $day->addDay()) {
            $calendarDays->push($day->copy());
        }

        $eventsByDate = $events->groupBy(fn (JobApplication $event) => $event->interview_at->format('Y-m-d'));

        return view('client.interviews.calendar', compact(
            'events',
            'calendarMonth',
            'calendarDays',
            'eventsByDate',
            'selectedRange',
            'filteredInterviews'
        ));
    }

    /**
     * Dedicated paginated and searchable past interviews list for client.
     */
    public function pastInterviews(\Illuminate\Http\Request $request)
    {
        $clientId = Auth::id();
        $query = JobApplication::with(['job', 'candidate', 'candidateUser', 'interviewRounds'])
            ->whereHas('job', fn ($q) => $q->where('user_id', $clientId))
            ->whereNotNull('interview_at')
            ->where('interview_at', '<', now()->startOfDay());

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('candidate', function($c) use ($search) {
                    $c->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('candidateUser', function($cu) use ($search) {
                    $cu->where('name', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('job', function($j) use ($search) {
                    $j->where('title', 'like', "%{$search}%");
                });
            });
        }

        $pastInterviews = $query->orderBy('interview_at', 'desc')->paginate(10);

        return view('client.interviews.past', compact('pastInterviews'));
    }

    public function markAsAppeared(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'Interviewed']);
        $this->notifySourcingPartner(
            new CandidateLifecycleUpdated($application, 'interview_appeared'),
            $application
        );
        return redirect()->back()->with('success', 'Candidate marked as \'Interviewed\'.');
    }

    public function markAsNoShow(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'No-Show']);
        $this->notifySourcingPartner(
            new CandidateLifecycleUpdated($application, 'interview_no_show'),
            $application
        );
        return redirect()->back()->with('success', 'Candidate marked as \'No-Show\'.');
    }
    
    // --- MULTI-ROUND INTERVIEWS ---

    /**
     * Show the schedule-new-round form for a candidate.
     */
    public function showScheduleRoundForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) abort(403);

        $application->load(['candidate', 'job', 'interviewRounds']);
        $roundCount = $application->interviewRounds->count();
        if ($roundCount >= InterviewRound::MAX_ROUNDS) {
            return redirect()->route('client.jobs.applicants', $application->job_id)
                ->with('error', 'Maximum '.InterviewRound::MAX_ROUNDS.' rounds already scheduled.');
        }

        return view('client.rounds.schedule', [
            'application' => $application,
            'roundNumber' => $roundCount + 1,
        ]);
    }

    /**
     * Show the feedback form for an interview round.
     */
    public function showRoundFeedbackForm(InterviewRound $round)
    {
        $application = $round->application;
        if ($application->job->user_id !== Auth::id()) abort(403);

        $application->load(['candidate', 'job', 'interviewRounds']);
        $roundCount = $application->interviewRounds->count();

        return view('client.rounds.feedback', [
            'application' => $application,
            'round'       => $round,
            'allowNext'   => $roundCount < InterviewRound::MAX_ROUNDS,
        ]);
    }

    /**
     * Schedule the next interview round (auto-numbered, capped at 5).
     */
    public function scheduleInterviewRound(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $existing = $application->interviewRounds()->count();
        if ($existing >= InterviewRound::MAX_ROUNDS) {
            return redirect()->back()->with('error', 'Maximum '.InterviewRound::MAX_ROUNDS.' rounds reached.');
        }

        $validated = $request->validate([
            'scheduled_at'      => 'required|date|after:now',
            'mode'              => 'required|in:Online,In-person,Phone',
            'meeting_link'      => 'nullable|url|max:500',
            'location'          => 'nullable|string|max:255',
            'candidate_message' => 'nullable|string|max:2000',
        ]);

        $round = $application->interviewRounds()->create([
            'round_number'      => $existing + 1,
            'scheduled_at'      => Carbon::parse($validated['scheduled_at']),
            'mode'              => $validated['mode'],
            'meeting_link'      => $validated['mode'] === 'Online' ? $validated['meeting_link'] : null,
            'location'          => $validated['mode'] === 'In-person' ? $validated['location'] : null,
            'candidate_message' => $validated['candidate_message'] ?? null,
            'status'            => 'Scheduled',
        ]);

        // Mirror to legacy columns so existing notifications / queries still work,
        // and clear the reminder flag so the cron picks THIS round up too
        $application->update([
            'hiring_status'              => 'Interview Scheduled',
            'interview_at'               => $round->scheduled_at,
            'meeting_link'               => $round->meeting_link,
            'interview_location'         => $round->location,
            'interview_reminder_sent_at' => null,
        ]);

        // In-app notification for admin + partner
        $this->notifyAdminAndPartner(new InterviewScheduled($application), $application);

        // WhatsApp + email to the candidate (Round N)
        $this->sendInterviewConfirmationToCandidate(
            $application->fresh(['job', 'candidate', 'candidateUser.profile']),
            false,
            $round->candidate_message,
            $round->round_number
        );

        return redirect()->back()->with('success', "Round {$round->round_number} scheduled — candidate notified on WhatsApp + email.");
    }

    public function editInterviewRound(InterviewRound $round)
    {
        $application = $round->application;
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['candidate', 'job', 'interviewRounds']);
        return view('client.rounds.edit', [
            'application' => $application,
            'round'       => $round,
            'roundNumber' => $round->round_number,
        ]);
    }

    public function updateInterviewRound(Request $request, InterviewRound $round)
    {
        $application = $round->application;
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'scheduled_at'      => 'required|date',
            'mode'              => 'required|in:Online,In-person,Phone',
            'meeting_link'      => 'nullable|url|max:500',
            'location'          => 'nullable|string|max:255',
            'candidate_message' => 'nullable|string|max:2000',
        ]);

        $round->update([
            'scheduled_at'      => Carbon::parse($validated['scheduled_at']),
            'mode'              => $validated['mode'],
            'meeting_link'      => $validated['mode'] === 'Online' ? $validated['meeting_link'] : null,
            'location'          => $validated['mode'] === 'In-person' ? $validated['location'] : null,
            'candidate_message' => $validated['candidate_message'] ?? null,
            'status'            => 'Scheduled', // Reset round status to Scheduled upon edit/reschedule
        ]);

        // If editing the latest round, mirror back to legacy columns and re-notify
        $latest = $application->interviewRounds()->latest('round_number')->first();
        $isLatest = $latest && $latest->id === $round->id;
        if ($isLatest) {
            $application->update([
                'hiring_status'              => 'Interview Scheduled', // Ensure mirrored hiring status is scheduled
                'interview_at'               => $round->scheduled_at,
                'meeting_link'               => $round->meeting_link,
                'interview_location'         => $round->location,
                'interview_reminder_sent_at' => null,
            ]);

            $this->sendInterviewConfirmationToCandidate(
                $application->fresh(['job', 'candidate', 'candidateUser.profile']),
                true,
                $round->candidate_message,
                $round->round_number
            );
        }

        app(SuperadminActivityService::class)->logApplicationLifecycle($application, 'client.interview_scheduled');
        $this->notifySourcingPartner(
            new CandidateLifecycleUpdated($application, 'round_rescheduled', $round->round_number),
            $application
        );

        $msg = "Round {$round->round_number} rescheduled successfully";
        if ($isLatest) $msg .= ' — candidate re-notified on WhatsApp + email';
        
        // Redirect to candidate details page
        return redirect()->route('client.applications.show', $application->id)->with('success', $msg . '.');
    }

    public function markRoundAppeared(InterviewRound $round)
    {
        if ($round->application->job->user_id !== Auth::id()) abort(403);
        $round->update(['status' => 'Appeared']);
        $round->application->update(['hiring_status' => 'Interviewed']);
        $this->notifySourcingPartner(
            new CandidateLifecycleUpdated($round->application, 'interview_appeared', $round->round_number),
            $round->application
        );
        return redirect()->back()->with('success', "Round {$round->round_number}: marked as appeared.");
    }

    public function markRoundNoShow(InterviewRound $round)
    {
        if ($round->application->job->user_id !== Auth::id()) abort(403);
        $round->update(['status' => 'No-Show']);
        $round->application->update(['hiring_status' => 'No-Show']);
        $this->notifySourcingPartner(
            new CandidateLifecycleUpdated($round->application, 'interview_no_show', $round->round_number),
            $round->application
        );
        return redirect()->back()->with('success', "Round {$round->round_number}: marked as no-show.");
    }

    public function submitRoundFeedback(Request $request, InterviewRound $round)
    {
        if ($round->application->job->user_id !== Auth::id()) abort(403);

        $validated = $request->validate([
            'feedback'       => 'nullable|string|max:5000',
            'rating'         => 'nullable|integer|min:1|max:5',
            'recommendation' => 'required|in:Pass to Next Round,Select Candidate,Reject',
        ]);

        $round->update([
            'feedback'              => $validated['feedback'] ?? null,
            'rating'                => $validated['rating'] ?? null,
            'recommendation'        => $validated['recommendation'],
            'feedback_submitted_at' => now(),
            'status'                => $round->status === 'Scheduled' ? 'Appeared' : $round->status,
        ]);

        // Mirror to legacy columns
        $round->application->update([
            'interview_feedback'        => $validated['feedback'] ?? $round->application->interview_feedback,
            'interview_rating'          => $validated['rating'] ?? $round->application->interview_rating,
            'interview_recommendation'  => $validated['recommendation'],
            'interview_feedback_at'     => now(),
        ]);

        // Auto-reject / select based on recommendation
        if ($validated['recommendation'] === 'Reject') {
            $round->application->update(['hiring_status' => 'Client Rejected']);
            $message = "Round {$round->round_number} feedback saved. Candidate rejected.";
            
            // Trigger rejection notifications for admin and vendor (sourcing partner)
            $this->notifyAdminAndPartner(new CandidateRejectedByClient($round->application), $round->application);
        } elseif ($validated['recommendation'] === 'Select Candidate') {
            $round->application->update([
                'hiring_status' => 'Selected',
                'joining_date'  => now()->addDays(15),
            ]);
            $message = "Round {$round->round_number} feedback saved. Candidate selected.";
            
            // Trigger selection notifications for admin and vendor (sourcing partner)
            $this->notifyAdminAndPartner(new CandidateSelected($round->application), $round->application);
            
            // Trigger selection email and WhatsApp to candidate
            $this->sendSelectionConfirmationToCandidate($round->application->fresh(['job', 'candidate', 'candidateUser.profile']), false);
        } else {
            $message = "Round {$round->round_number} feedback saved. Passed to next round.";
        }

        return redirect()->route('client.jobs.applicants', $round->application->job_id)->with('success', $message);
    }

    // --- SELECTION ---

    public function showSelectForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.select', ['application' => $application, 'isEdit' => false]);
    }

    public function storeSelection(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'final_ctc'    => 'nullable|numeric|min:0',
            'client_notes' => 'nullable|string|max:1000', 'offer_letter' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $application->update([
            'hiring_status' => 'Selected',
            'joining_date'  => Carbon::parse($validated['joining_date']),
            'final_ctc'     => $validated['final_ctc'] ?? null,
            'client_notes'  => $validated['client_notes'] ?? null, 'offer_letter_path' => $request->hasFile('offer_letter') ? $request->file('offer_letter')->store('offer-letters','public') : null, 'finance_offer_status' => 'pending_review', 'offer_updated_at' => now(), 'offer_updated_by' => Auth::id(),
        ]);

        $this->stampResolvedInvoice($application->fresh(['job.user']));

        $this->notifyAdminAndPartner(new CandidateSelected($application), $application);
        $this->sendSelectionConfirmationToCandidate($application->fresh(['job', 'candidate', 'candidateUser.profile']), false);

        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Candidate Selected! Joining date has been set.');
    }

    public function editSelection(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.select', ['application' => $application, 'isEdit' => true]);
    }

    public function updateSelectionDetails(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'final_ctc'    => 'nullable|numeric|min:0',
            'client_notes' => 'nullable|string|max:1000', 'offer_letter' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        $application->update([
            'joining_date' => Carbon::parse($validated['joining_date']),
            'final_ctc'    => $validated['final_ctc'] ?? $application->final_ctc,
            'client_notes' => $validated['client_notes'] ?? null, 'offer_letter_path' => $request->hasFile('offer_letter') ? $request->file('offer_letter')->store('offer-letters','public') : $application->offer_letter_path, 'finance_offer_status' => 'pending_review', 'offer_updated_at' => now(), 'offer_updated_by' => Auth::id(),
        ]);

        $this->stampResolvedInvoice($application->fresh(['job.user']));
        $this->notifyAdminAndPartner(new CandidateSelected($application, true), $application);
        $this->sendSelectionConfirmationToCandidate($application->fresh(['job', 'candidate', 'candidateUser.profile']), true);

        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Selection details updated successfully!');
    }

    /**
     * Compute and stamp invoice_amount on the application from the
     * client's permanent-hiring commercial contract.
     */
    private function stampResolvedInvoice(JobApplication $application): void
    {
        $resolved = $application->resolveCommercial();
        if (!$resolved) return;

        $stamp = [];
        if ($application->invoice_amount === null && $resolved['invoice_amount'] > 0) {
            $stamp['invoice_amount'] = $resolved['invoice_amount'];
        }
        // Lock in the replacement window for this hire so subsequent edits
        // to the client's contract don't retroactively change it.
        if ($application->replacement_window_days === null && $resolved['replacement_days'] !== null) {
            $stamp['replacement_window_days'] = $resolved['replacement_days'];
        }
        if (!empty($stamp)) {
            $application->update($stamp);
        }
    }

    public function markAsJoined(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['joined_status' => 'Joined']);
        $this->notifyAdminAndPartner(new CandidateJoined($application), $application);
        $this->sendJoinedNotificationToCandidate($application->fresh(['job', 'candidate', 'candidateUser.profile']));

        // Redirect the client to the rating page if the candidate came via a partner
        $partnerId = $application->candidate?->partner_id;
        if ($partnerId && !\App\Models\VendorRating::where('application_id', $application->id)->exists()) {
            return redirect()->route('client.applications.rate', $application->id)
                ->with('success', "Candidate marked as 'Joined'. Please rate the sourcing partner.");
        }
        return redirect()->back()->with('success', 'Candidate marked as \'Joined\'.');
    }

    public function showRatePartner(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) abort(403);
        $partner = $application->candidate?->partner;
        if (!$partner) abort(404, 'This candidate has no sourcing partner.');
        if (\App\Models\VendorRating::where('application_id', $application->id)->exists()) {
            return redirect()->route('client.jobs.applicants', $application->job_id)->with('info', 'Partner already rated for this hire.');
        }
        return view('client.applications.rate', compact('application', 'partner'));
    }

    public function storeRatePartner(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) abort(403);
        $partner = $application->candidate?->partner;
        if (!$partner) abort(404);

        $data = $request->validate([
            'score'               => 'required|integer|min:1|max:5',
            'speed_score'         => 'nullable|integer|min:1|max:5',
            'quality_score'       => 'nullable|integer|min:1|max:5',
            'communication_score' => 'nullable|integer|min:1|max:5',
            'feedback'            => 'nullable|string|max:1500',
        ]);

        \App\Models\VendorRating::updateOrCreate(
            ['application_id' => $application->id],
            array_merge($data, [
                'partner_id'       => $partner->id,
                'rated_by_user_id' => Auth::id(),
                'job_id'           => $application->job_id,
            ])
        );

        \App\Models\VendorRating::recomputeFor($partner->id);

        return redirect()->route('client.jobs.applicants', $application->job_id)
            ->with('success', 'Thanks! Your rating has been recorded.');
    }

    public function markAsNotJoined(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['joined_status' => 'Did Not Join']);
        $this->notifyAdminAndPartner(new CandidateDidNotJoin($application), $application);
        return redirect()->back()->with('success', 'Candidate marked as \'Did Not Join\'.');
    }

    public function showLeftForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.left', ['application' => $application]);
    }

    public function markAsLeft(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'left_at' => 'required|date|after_or_equal:' . $application->joining_date,
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'joined_status' => 'Left',
            'left_at' => Carbon::parse($validated['left_at']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        $this->notifyAdminAndPartner(new CandidateLeft($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Candidate marked as \'Left\'.');
    }

    public function billing(Request $request)
    {
        $client = Auth::user();

        $query = JobApplication::where('hiring_status', 'Selected')
            ->whereNotNull('joining_date')
            ->whereHas('job', fn ($q) => $q->where('user_id', $client->id))
            ->with(['job.user', 'candidate', 'candidateUser']);

        // Filters
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('candidate', fn ($qq) => $qq->where('first_name', 'like', "%$search%")
                                                       ->orWhere('last_name', 'like', "%$search%")
                                                       ->orWhere('email', 'like', "%$search%"))
                  ->orWhereHas('candidateUser', fn ($qq) => $qq->where('name', 'like', "%$search%")
                                                              ->orWhere('email', 'like', "%$search%"))
                  ->orWhereHas('job', fn ($qq) => $qq->where('title', 'like', "%$search%"));
            });
        }
        if ($jobId = $request->input('job_id')) {
            $query->where('job_id', $jobId);
        }
        if ($from = $request->input('date_from')) {
            $query->whereDate('joining_date', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->whereDate('joining_date', '<=', $to);
        }

        $statusFilter = $request->input('status');
        // Billing status is resolved from commercial terms, so we must filter the
        // complete result set before building a page of invoices.
        $allFiltered = (clone $query)
            ->latest('joining_date')
            ->get()
            ->map(fn ($a) => $a->billingSnapshot());
        $counts = [
            'Paid'         => $allFiltered->where('status', 'Paid')->count(),
            'Overdue'      => $allFiltered->where('status', 'Overdue')->count(),
            'Raised'       => $allFiltered->where('status', 'Raised')->count(),
            'Due to Raise' => $allFiltered->where('status', 'Due to Raise')->count(),
            'Maturing'     => $allFiltered->where('status', 'Maturing')->count(),
        ];

        // Summary numbers (computed off the same slim collection)
        $summary = [
            'outstanding'    => $allFiltered->whereIn('status', ['Raised', 'Overdue', 'Due to Raise'])->sum('invoice_amount'),
            'paid_total'     => $allFiltered->where('status', 'Paid')->sum('invoice_amount'),
            'maturing_total' => $allFiltered->where('status', 'Maturing')->sum('invoice_amount'),
            'overdue_count'  => $allFiltered->where('status', 'Overdue')->count(),
        ];

        $displayRows = $statusFilter
            ? $allFiltered->filter(fn ($row) => $row['status'] === $statusFilter)->values()
            : $allFiltered->values();
        $allowedPerPage = [10, 20, 50];
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $billingData = new LengthAwarePaginator(
            $displayRows->forPage($currentPage, $perPage)->values(),
            $displayRows->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Dropdown options
        $clientJobs = Job::where('user_id', $client->id)
            ->whereHas('jobApplications', fn ($q) => $q->where('hiring_status', 'Selected')->whereNotNull('joining_date'))
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('client.billing.index', compact('billingData', 'counts', 'statusFilter', 'summary', 'clientJobs', 'perPage', 'allowedPerPage'));
    }

    /**
     * Download a deliberately non-tax demo invoice for a billable placement.
     * Real issuer, GST and bank details must be configured before issuing a tax invoice.
     */
    public function downloadDemoInvoice(JobApplication $application, InvoiceDocumentService $invoiceDocuments)
    {
        $application->load(['job.user.clientProfile', 'candidate', 'candidateUser']);

        abort_unless($application->job?->user_id === Auth::id(), 403);
        abort_unless($application->hiring_status === 'Selected' && $application->joining_date, 422, 'This application is not billable yet.');

        $invoice = $application->billingSnapshot();
        abort_if((float) ($invoice['invoice_amount'] ?? 0) <= 0, 422, 'Commercials must be configured before a demo invoice can be generated.');

        $document = $invoiceDocuments->build($application, $invoice);

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('invoices.placement-invoice-pdf', compact('document'))->render());
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.str_replace('/', '-', $document['invoiceNumber']).'.pdf"',
        ]);
    }

    /**
     * Client confirms they've paid an invoice. Records paid_at and an
     * optional payment reference (UTR / cheque no / transaction id).
     */
    public function markBillingPaid(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'paid_at'           => 'required|date|before_or_equal:today',
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $application->update([
            'payment_status' => 'paid',
            'paid_at'        => Carbon::parse($validated['paid_at']),
            'client_notes'   => trim(($application->client_notes ?? '').' [PAID: '.($validated['payment_reference'] ?? 'no ref').' on '.$validated['paid_at'].']'),
        ]);

        return redirect()->back()->with('success', 'Payment recorded successfully.');
    }

    public function unmarkBillingPaid(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['payment_status' => null, 'paid_at' => null]);
        return redirect()->back()->with('success', 'Payment status reverted.');
    }

    private function notifyAdminAndPartner($notification, JobApplication $application)
    {
        $application->load(['job.user', 'candidate.partner', 'candidateUser']);
        
        $admins = User::role(['Superadmin', 'Manager'])->get();
        Notification::send($admins, $notification);

        if ($application->candidate && $application->candidate->partner?->hasRole('partner')) {
            $partner = $application->candidate->partner;
            $partner->notify($notification);
        }
    }

    private function notifySourcingPartner($notification, JobApplication $application): void
    {
        $application->loadMissing('candidate.partner');
        $partner = $application->candidate?->partner;

        if ($partner?->hasRole('partner')) {
            $partner->notify($notification);
        }
    }

    private function sendSelectionConfirmationToCandidate(JobApplication $application, bool $isUpdate = false): void
    {
        $application->loadMissing('candidate.partner');
        $whatsapp = app(AiSensyWhatsAppService::class);
        $cand     = $application->candidate;
        $direct   = $application->candidateUser;

        $name = $cand
            ? trim(($cand->first_name ?? '') . ' ' . ($cand->last_name ?? ''))
            : ($direct?->name ?? 'Candidate');
        $email = $cand?->email ?? $direct?->email ?? null;
        $phone = $whatsapp->normalizeIndianPhone($cand?->phone_number ?? optional($direct?->profile)->phone_number ?? null);

        $partnerName = ($cand && $cand->partner) ? $cand->partner->name : null;

        $job  = $application->job;
        $company = $job?->company_name ?: (optional($job?->user)->name ?? 'the company');
        if ($job && $job->is_company_confidential) $company = 'Confidential (details on call)';

        $joiningDate = $application->joining_date ? $application->joining_date->format('F d, Y') : 'TBD';
        $ctc = $application->final_ctc ? '₹' . number_format($application->final_ctc, 0) : 'As per offer letter';

        $body  = ($isUpdate
            ? "Hi {$name}, your selection details for the " . ($job?->title ?? 'role') . " role at {$company} have been revised."
            : "Congratulations {$name}! You have been selected for the " . ($job?->title ?? 'role') . " role at {$company}.") . "\n\n";
        $body .= "🏢 Company: {$company}\n";
        $body .= "💼 Role: " . ($job?->title ?? '—') . "\n";
        $body .= "📅 Joining Date: {$joiningDate}\n";
        $body .= "💰 CTC: {$ctc}\n";
        if ($partnerName) {
            $body .= "📞 Partner Coordinator: {$partnerName}\n";
        }
        if ($application->client_notes) {
            $body .= "\n📝 Note: {$application->client_notes}\n";
        }
        $body .= "\nWe look forward to having you on board!\n— SimplyHiree";

        // --- WhatsApp via AiSensy ---
        if ($phone) {
            try {
                $details = "Role: " . ($job?->title ?? '—') . " | Company: {$company} | Joining Date: {$joiningDate} | CTC: {$ctc}";
                if ($partnerName) {
                    $details .= " | Partner: {$partnerName} (Please contact them for onboarding support)";
                }
                if ($application->client_notes) {
                    $notesClean = str_replace(["\r", "\n", "\t"], " ", $application->client_notes);
                    $notesClean = preg_replace('/\s+/', ' ', $notesClean);
                    $details .= " | Note: {$notesClean}";
                }

                $whatsapp->sendEventAlert(
                    $phone,
                    'candidate.selected',
                    'Selection ' . ($isUpdate ? 'Revised' : 'Confirmed'),
                    $body,
                    ['template_params' => [
                        $isUpdate ? 'Revised Offer Details' : 'Candidate Selection',
                        $details,
                        now()->format('F d, Y, h:i A')
                    ]]
                );
            } catch (\Throwable $e) {
                \Log::warning('Selection WA confirmation failed app=' . $application->id . ': ' . $e->getMessage());
            }
        }

        // --- Email ---
        if ($email) {
            try {
                Mail::send('emails.candidate_selection', [
                    'name'         => $name,
                    'company'      => $company,
                    'role'         => $job?->title ?? '—',
                    'joining_date' => $joiningDate,
                    'ctc'          => $ctc,
                    'notes'        => $application->client_notes,
                    'isUpdate'     => $isUpdate,
                    'partnerName'  => $partnerName,
                ], function ($m) use ($email, $name, $isUpdate) {
                    $subject = $isUpdate ? '[SimplyHiree] Revised Job Offer Selection' : '[SimplyHiree] Congratulations! You are Selected';
                    $m->to($email, $name)->subject($subject);
                });
            } catch (\Throwable $e) {
                \Log::warning('Selection email confirmation failed app=' . $application->id . ': ' . $e->getMessage());
            }
        }
    }

    private function sendJoinedNotificationToCandidate(JobApplication $application): void
    {
        $application->loadMissing('candidate.partner');
        $whatsapp = app(AiSensyWhatsAppService::class);
        $cand     = $application->candidate;
        $direct   = $application->candidateUser;

        $name = $cand
            ? trim(($cand->first_name ?? '') . ' ' . ($cand->last_name ?? ''))
            : ($direct?->name ?? 'Candidate');
        $email = $cand?->email ?? $direct?->email ?? null;
        $phone = $whatsapp->normalizeIndianPhone($cand?->phone_number ?? optional($direct?->profile)->phone_number ?? null);

        $partnerName = ($cand && $cand->partner) ? $cand->partner->name : null;

        $job  = $application->job;
        $company = $job?->company_name ?: (optional($job?->user)->name ?? 'the company');
        if ($job && $job->is_company_confidential) $company = 'Confidential (details on call)';

        $joiningDate = $application->joining_date ? $application->joining_date->format('F d, Y') : 'today';
        $ctc = $application->final_ctc ? '₹' . number_format($application->final_ctc, 0) : 'As per offer letter';

        $body  = "Welcome aboard {$name}! We are excited to confirm that you have officially joined {$company} for the " . ($job?->title ?? 'role') . " role.\n\n";
        $body .= "🏢 Company: {$company}\n";
        $body .= "💼 Role: " . ($job?->title ?? '—') . "\n";
        $body .= "📅 Joining Date: {$joiningDate}\n";
        $body .= "💰 CTC: {$ctc}\n";
        $body .= "\nCongratulations and wishing you a successful career ahead!\n— SimplyHiree";

        // --- WhatsApp via AiSensy ---
        if ($phone) {
            try {
                $details = "Role: " . ($job?->title ?? '—') . " | Company: {$company} | Joining Date: {$joiningDate} | CTC: {$ctc}";
                if ($partnerName) {
                    $details .= " | Partner: {$partnerName} (Please contact them for onboarding support)";
                }

                $whatsapp->sendEventAlert(
                    $phone,
                    'candidate.selected',
                    'Welcome Aboard!',
                    $body,
                    ['template_params' => [
                        'Welcome Aboard!',
                        $details,
                        now()->format('F d, Y, h:i A')
                    ]]
                );
            } catch (\Throwable $e) {
                \Log::warning('Joined WA confirmation failed app=' . $application->id . ': ' . $e->getMessage());
            }
        }

        // --- Email ---
        if ($email) {
            try {
                Mail::send('emails.candidate_joined', [
                    'name'         => $name,
                    'company'      => $company,
                    'role'         => $job?->title ?? '—',
                    'joining_date' => $joiningDate,
                    'ctc'          => $ctc,
                    'partnerName'  => $partnerName,
                ], function ($m) use ($email, $name) {
                    $m->to($email, $name)->subject('[SimplyHiree] Welcome to the Team!');
                });
            } catch (\Throwable $e) {
                \Log::warning('Joined email confirmation failed app=' . $application->id . ': ' . $e->getMessage());
            }
        }
    }

    public function smokeTestJoining()
    {
        $whatsapp = app(AiSensyWhatsAppService::class);

        $name = 'Smoke Test Candidate';
        $email = 'abhymax@gmail.com';
        $phone = $whatsapp->normalizeIndianPhone('9123732174');

        $company = 'Smoke Test Corp';
        $jobTitle = 'Senior Software Engineer';
        $joiningDate = now()->addDays(7)->format('F d, Y');
        $ctc = '₹1,500,000';
        $partnerName = 'Smoke Test Partner';

        $body = "Congratulations {$name}! You have been selected for the {$jobTitle} role at {$company}.\n\n";
        $body .= "🏢 Company: {$company}\n";
        $body .= "💼 Role: {$jobTitle}\n";
        $body .= "📅 Joining Date: {$joiningDate}\n";
        $body .= "💰 CTC: {$ctc}\n";
        $body .= "\nWe look forward to having you on board!\n— SimplyHiree";

        $waSent = false;
        $emailSent = false;
        $waError = null;
        $emailError = null;

        // --- WhatsApp via AiSensy ---
        try {
            $details = "Role: {$jobTitle} | Company: {$company} | Joining Date: {$joiningDate} | CTC: {$ctc} | Partner: {$partnerName} (Please contact them for onboarding support)";

            $res = $whatsapp->sendEventAlert(
                $phone,
                'candidate.selected',
                'Selection Confirmed (Smoke Test)',
                $body,
                ['template_params' => [
                    'Candidate Selection (Smoke Test)',
                    $details,
                    now()->format('F d, Y, h:i A')
                ]]
            );
            $waSent = $res['ok'] ?? false;
            if (!$waSent) $waError = $res['error'] ?? 'Unknown Error';
        } catch (\Throwable $e) {
            $waError = $e->getMessage();
        }

        // --- Email ---
        try {
            Mail::send('emails.candidate_selection', [
                'name'         => $name,
                'company'      => $company,
                'role'         => $jobTitle,
                'joining_date' => $joiningDate,
                'ctc'          => $ctc,
                'notes'        => 'This is a smoke test email confirmation.',
                'isUpdate'     => false,
                'partnerName'  => $partnerName,
            ], function ($m) use ($email, $name) {
                $m->to($email, $name)->subject('[SimplyHiree] [Smoke Test] Congratulations! You are Selected');
            });
            $emailSent = true;
        } catch (\Throwable $e) {
            $emailError = $e->getMessage();
        }

        return response()->json([
            'success' => true,
            'message' => 'Smoke test complete.',
            'whatsapp' => [
                'recipient' => $phone,
                'sent' => $waSent,
                'error' => $waError,
            ],
            'email' => [
                'recipient' => $email,
                'sent' => $emailSent,
                'error' => $emailError,
            ]
        ]);
    }
}
