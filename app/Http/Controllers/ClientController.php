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
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use App\Services\AiSensyWhatsAppService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use App\Services\SuperadminActivityService;

class ClientController extends Controller
{
    /**
     * Show the client dashboard.
     */
    public function index(SuperadminActivityService $activityService)
    {
        $activityService->checkBillingDueAlerts();

        $client = Auth::user();
        
        $jobs = Job::where('user_id', $client->id)
                    ->with(['educationLevel']) // Removed experienceLevel as we use min/max now
                    ->latest()
                    ->get();
        
        $totalJobs = $jobs->count();
        $activeJobs = $jobs->where('status', 'approved')->count();
        $totalApplicants = JobApplication::whereIn('job_id', $jobs->pluck('id'))->count();
        
        $totalHires = JobApplication::whereIn('job_id', $jobs->pluck('id'))
                                ->whereIn('hiring_status', ['Selected', 'Joined'])
                                ->count();

        // --- Daily Pulse Data ---
        $todayInterviews = JobApplication::whereIn('job_id', $jobs->pluck('id'))
            ->whereDate('interview_at', Carbon::today())
            ->count();

        $dueInvoicesCount = 0;
        $myHires = JobApplication::whereIn('job_id', $jobs->pluck('id'))
            ->where('hiring_status', 'Selected')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('joining_date')
            ->with('job.user')
            ->get();

        foreach ($myHires as $hire) {
            $due = $hire->invoiceDueAt();
            if ($due && ($due->isPast() || $due->isToday())) {
                $dueInvoicesCount++;
            }
        }

        return view('client.dashboard', [
            'client' => $client,
            'jobs'   => $jobs,
            'totalJobs' => $totalJobs,
            'activeJobs' => $activeJobs,
            'totalApplicants' => $totalApplicants,
            'totalHires' => $totalHires,
            'todayInterviews' => $todayInterviews,
            'dueInvoicesCount' => $dueInvoicesCount
        ]);
    }

    // --- NEW: JOB CREATION METHODS ---

    public function listJobs(Request $request)
    {
        $clientId = Auth::id();
        $query = \App\Models\Job::where('user_id', $clientId)
            ->withCount('jobApplications')
            ->orderBy('created_at', 'desc');

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

        $jobs = $query->paginate(15)->withQueryString();

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
            'payout_amount' => $validated['payout_amount'],
            'minimum_stay_days' => $validated['minimum_stay_days'],
            'replacement_guarantee_days' => $validated['replacement_guarantee_days'],
            'is_company_confidential' => (bool) ($validated['is_company_confidential'] ?? false),
        ]);

        // Resolve the allowed-partner list according to mode
        if ($assignMode === 'preferred') {
            $preferredIds = Auth::user()->preferredVendors()->pluck('users.id')->all();
            $job->allowedPartners()->sync($preferredIds);
        } elseif ($assignMode === 'selected') {
            $job->allowedPartners()->sync($request->input('allowed_partners', []));
        }

        return redirect()->route('client.dashboard')->with('success', 'Job posted successfully! Waiting for admin approval.');
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
            'application_deadline' => $validated['application_deadline'],
            'skills_required' => (string) ($validated['skills_required'] ?? ''),
            'company_website' => (string) ($validated['company_website'] ?? ''),
            'openings' => $validated['openings'] ?? 1,
            'payout_amount' => $validated['payout_amount'],
            'minimum_stay_days' => $validated['minimum_stay_days'],
            'replacement_guarantee_days' => $validated['replacement_guarantee_days'],
            'is_company_confidential' => (bool) ($validated['is_company_confidential'] ?? false),
            'status' => 'pending_approval',
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
            'payout_amount' => 'required|numeric|min:0',
            'minimum_stay_days' => 'required|integer|min:0|max:365',
            'replacement_guarantee_days' => 'required|integer|min:0|max:365',
            'vendor_assignment_mode' => 'nullable|in:open,preferred,selected',
            'max_vendors_per_job'    => 'nullable|integer|min:1|max:50',
            'allowed_partners'       => 'nullable|array',
            'allowed_partners.*'     => 'integer|exists:users,id',
            'is_company_confidential' => 'nullable|boolean',
        ]);
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
            ?? 0);
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

        if ((string) $job->status !== 'pending_approval') {
            abort(403, 'Only pending jobs can be edited.');
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
            });

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

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
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

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $allowedPerPage = [10, 20, 50, 100];
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, $allowedPerPage, true)) $perPage = 20;

        $applications = $query->latest()->paginate($perPage)->withQueryString();

        $jobs = Job::where('user_id', $clientId)->select('id', 'title')->orderBy('title')->get();
        $partners = User::role('partner')
            ->whereNull('parent_partner_id')
            ->where('status', 'active')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('client.applications.index', compact(
            'applications', 'jobs', 'partners', 'perPage', 'allowedPerPage'
        ));
    }

    /**
     * Show the applicants for a specific job.
     */
    public function showApplicants(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $approvedApplications = JobApplication::where('job_id', $job->id)
                                            ->where('status', 'Approved')
                                            ->with(['candidate', 'candidateUser', 'interviewRounds'])
                                            ->latest()
                                            ->paginate(20);

        return view('client.jobs.applicants', [
            'job' => $job,
            'applications' => $approvedApplications
        ]);
    }
    
    public function showApplicantDetail(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
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

        $this->sendInterviewConfirmationToCandidate($application->fresh(['job', 'candidate', 'candidateUser.profile']), true);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview updated — candidate has been re-notified.');
    }

    /**
     * Push an immediate WhatsApp + email notification to the candidate
     * with their interview time, company, location/link.
     */
    private function sendInterviewConfirmationToCandidate(JobApplication $application, bool $isUpdate = false): void
    {
        $whatsapp = app(AiSensyWhatsAppService::class);
        $cand     = $application->candidate;
        $direct   = $application->candidateUser;

        $name = $cand
            ? trim(($cand->first_name ?? '') . ' ' . ($cand->last_name ?? ''))
            : ($direct?->name ?? 'Candidate');
        $email = $cand?->email ?? $direct?->email ?? null;
        $phone = $cand?->phone ?? optional($direct?->profile)->phone_number ?? null;

        $job  = $application->job;
        $company = $job?->company_name ?: (optional($job?->user)->name ?? 'the company');
        if ($job && $job->is_company_confidential) $company = 'Confidential (details on call)';

        $time = $application->interview_at?->format('h:i A, D d M Y') ?? 'TBD';
        $where = $application->meeting_link
            ?: ($application->interview_location ?: 'Details will be shared by the recruiter');
        $verb = $isUpdate ? 'updated' : 'scheduled';

        $body  = ($isUpdate ? "Hi {$name}, your interview time has been updated." : "Hi {$name}, your interview has been scheduled.") . "\n\n";
        $body .= "🏢 Company: {$company}\n";
        $body .= "💼 Role: " . ($job?->title ?? '—') . "\n";
        $body .= "🕒 When: {$time}\n";
        if ($application->meeting_link) {
            $body .= "🔗 Join: {$application->meeting_link}\n";
        } elseif ($application->interview_location) {
            $body .= "📍 Where: {$application->interview_location}\n";
        }
        if ($application->client_notes) {
            $body .= "\n📝 Note: {$application->client_notes}\n";
        }
        $body .= "\nGood luck!\n— SimplyHiree";

        // --- WhatsApp via AiSensy ---
        if ($phone) {
            try {
                $whatsapp->sendEventAlert(
                    $phone,
                    'interview_scheduled',
                    'Interview ' . $verb,
                    $body,
                    ['template_params' => [
                        $name,
                        $job?->title ?? 'the role',
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
                    'role'         => $job?->title ?? '—',
                    'time'         => $time,
                    'meeting_link' => $application->meeting_link,
                    'location'     => $application->interview_location,
                    'notes'        => $application->client_notes,
                    'isUpdate'     => $isUpdate,
                ], function ($m) use ($email, $name, $verb) {
                    $m->to($email, $name)
                      ->subject('[SimplyHiree] Your interview is ' . $verb);
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
    public function interviewCalendar()
    {
        $clientId = Auth::id();
        $events = JobApplication::with(['job', 'candidate', 'candidateUser'])
            ->whereHas('job', fn ($q) => $q->where('user_id', $clientId))
            ->whereNotNull('interview_at')
            ->orderBy('interview_at', 'asc')
            ->get();

        return view('client.interviews.calendar', compact('events'));
    }

    public function markAsAppeared(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'Interviewed']);
        return redirect()->back()->with('success', 'Candidate marked as \'Interviewed\'.');
    }

    public function markAsNoShow(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'No-Show']);
        return redirect()->back()->with('success', 'Candidate marked as \'No-Show\'.');
    }
    
    // --- MULTI-ROUND INTERVIEWS ---

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
            'scheduled_at'     => 'required|date|after:now',
            'mode'             => 'required|in:Online,In-person,Phone',
            'meeting_link'     => 'nullable|url|max:500',
            'location'         => 'nullable|string|max:255',
            'interviewer_name' => 'nullable|string|max:255',
        ]);

        $round = $application->interviewRounds()->create([
            'round_number'     => $existing + 1,
            'scheduled_at'     => Carbon::parse($validated['scheduled_at']),
            'mode'             => $validated['mode'],
            'meeting_link'     => $validated['mode'] === 'Online' ? $validated['meeting_link'] : null,
            'location'         => $validated['mode'] === 'In-person' ? $validated['location'] : null,
            'interviewer_name' => $validated['interviewer_name'] ?? null,
            'status'           => 'Scheduled',
        ]);

        // Mirror to legacy columns so existing notifications / queries still work
        $application->update([
            'hiring_status'      => 'Interview Scheduled',
            'interview_at'       => $round->scheduled_at,
            'meeting_link'       => $round->meeting_link,
            'interview_location' => $round->location,
        ]);

        $this->notifyAdminAndPartner(new InterviewScheduled($application), $application);

        return redirect()->back()->with('success', "Round {$round->round_number} scheduled.");
    }

    public function updateInterviewRound(Request $request, InterviewRound $round)
    {
        $application = $round->application;
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'scheduled_at'     => 'required|date',
            'mode'             => 'required|in:Online,In-person,Phone',
            'meeting_link'     => 'nullable|url|max:500',
            'location'         => 'nullable|string|max:255',
            'interviewer_name' => 'nullable|string|max:255',
        ]);

        $round->update([
            'scheduled_at'     => Carbon::parse($validated['scheduled_at']),
            'mode'             => $validated['mode'],
            'meeting_link'     => $validated['mode'] === 'Online' ? $validated['meeting_link'] : null,
            'location'         => $validated['mode'] === 'In-person' ? $validated['location'] : null,
            'interviewer_name' => $validated['interviewer_name'] ?? null,
        ]);

        // If editing the latest round, mirror back to legacy columns
        $latest = $application->interviewRounds()->latest('round_number')->first();
        if ($latest && $latest->id === $round->id) {
            $application->update([
                'interview_at'       => $round->scheduled_at,
                'meeting_link'       => $round->meeting_link,
                'interview_location' => $round->location,
            ]);
        }

        return redirect()->back()->with('success', "Round {$round->round_number} updated.");
    }

    public function markRoundAppeared(InterviewRound $round)
    {
        if ($round->application->job->user_id !== Auth::id()) abort(403);
        $round->update(['status' => 'Appeared']);
        $round->application->update(['hiring_status' => 'Interviewed']);
        return redirect()->back()->with('success', "Round {$round->round_number}: marked as appeared.");
    }

    public function markRoundNoShow(InterviewRound $round)
    {
        if ($round->application->job->user_id !== Auth::id()) abort(403);
        $round->update(['status' => 'No-Show']);
        $round->application->update(['hiring_status' => 'No-Show']);
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

        // Auto-reject if recommendation is Reject
        if ($validated['recommendation'] === 'Reject') {
            $round->application->update(['hiring_status' => 'Client Rejected']);
        }

        return redirect()->back()->with('success', "Round {$round->round_number} feedback saved.");
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
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'hiring_status' => 'Selected',
            'joining_date'  => Carbon::parse($validated['joining_date']),
            'final_ctc'     => $validated['final_ctc'] ?? null,
            'client_notes'  => $validated['client_notes'] ?? null,
        ]);

        $this->stampResolvedInvoice($application->fresh(['job.user']));

        $this->notifyAdminAndPartner(new CandidateSelected($application), $application);
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
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'joining_date' => Carbon::parse($validated['joining_date']),
            'final_ctc'    => $validated['final_ctc'] ?? $application->final_ctc,
            'client_notes' => $validated['client_notes'] ?? null,
        ]);

        $this->stampResolvedInvoice($application->fresh(['job.user']));

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

        $hires = JobApplication::where('hiring_status', 'Selected')
            ->whereNotNull('joining_date')
            ->whereHas('job', fn ($q) => $q->where('user_id', $client->id))
            ->with(['job.user', 'candidate', 'candidateUser'])
            ->latest('joining_date')
            ->paginate(25)
            ->withQueryString();

        $billingData = $hires->through(fn ($app) => $app->billingSnapshot());

        // Optional status filter
        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $billingData->setCollection(
                $billingData->getCollection()->filter(fn ($row) => $row['status'] === $statusFilter)->values()
            );
        }

        // Tally per bucket
        $allOnPage = $billingData->getCollection();
        $counts = [
            'Paid'         => $allOnPage->where('status', 'Paid')->count(),
            'Overdue'      => $allOnPage->where('status', 'Overdue')->count(),
            'Raised'       => $allOnPage->where('status', 'Raised')->count(),
            'Due to Raise' => $allOnPage->where('status', 'Due to Raise')->count(),
            'Maturing'     => $allOnPage->where('status', 'Maturing')->count(),
        ];

        return view('client.billing.index', compact('billingData', 'counts', 'statusFilter'));
    }

    private function notifyAdminAndPartner($notification, JobApplication $application)
    {
        $application->load(['job.user', 'candidate.partner', 'candidateUser']);
        
        $admins = User::role('Superadmin')->get();
        Notification::send($admins, $notification);

        if ($application->candidate && $application->candidate->partner) {
            $partner = $application->candidate->partner;
            $partner->notify($notification);
        }
    }
}
