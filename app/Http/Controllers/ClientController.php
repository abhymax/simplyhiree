<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\JobApplication;
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

        Job::create([
            'user_id' => Auth::id(), // Automatically assign to logged-in client
            'company_name' => Auth::user()->name, // Default to client name
            'status' => 'pending_approval', // Jobs posted by clients need approval
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'location' => $validated['location'],
            'salary' => $salary,
            'job_type' => $validated['job_type'],
            'description' => $this->sanitizeJobDescription($validated['description']),
            'gender_preference' => $validated['gender_preference'],
            
            'min_experience' => $validated['min_experience'],
            'max_experience' => $validated['max_experience'],
            'experience_level_id' => null,
            
            'education_level_id' => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'],
            'skills_required' => $request->skills_required,
            'company_website' => $request->company_website,
            'openings' => $request->openings ?? 1,
            'partner_visibility' => 'all', // Default visibility
            'payout_amount' => $validated['payout_amount'],
            'minimum_stay_days' => $validated['minimum_stay_days'],
            'replacement_guarantee_days' => $validated['replacement_guarantee_days'],
        ]);

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
            'min_experience' => $validated['min_experience'],
            'max_experience' => $validated['max_experience'],
            'experience_level_id' => null,
            'education_level_id' => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'],
            'skills_required' => $validated['skills_required'] ?? null,
            'company_website' => $validated['company_website'] ?? null,
            'openings' => $validated['openings'] ?? 1,
            'payout_amount' => $validated['payout_amount'],
            'minimum_stay_days' => $validated['minimum_stay_days'],
            'replacement_guarantee_days' => $validated['replacement_guarantee_days'],
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
            'payout_amount' => 'required|numeric|min:0',
            'minimum_stay_days' => 'required|integer|min:0|max:365',
            'replacement_guarantee_days' => 'required|integer|min:0|max:365',
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
        $guaranteeDays = (int) ($application->job->replacement_guarantee_days ?? 0);
        if ($guaranteeDays > 0) {
            $tenure = $application->joining_date->diffInDays($application->left_at);
            if ($tenure > $guaranteeDays) {
                return back()->with('error', "Candidate worked {$tenure} day(s), which is beyond the {$guaranteeDays}-day replacement-guarantee window.");
            }
        }

        $data = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);
        $application->update([
            'replacement_requested_at' => now(),
            'replacement_reason'       => $data['reason'] ?? null,
        ]);

        return back()->with('success', 'Replacement request raised. The sourcing partner has been notified.');
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
     * Show the applicants for a specific job.
     */
    public function showApplicants(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $approvedApplications = JobApplication::where('job_id', $job->id)
                                            ->where('status', 'Approved')
                                            ->with(['candidate', 'candidateUser'])
                                            ->latest()
                                            ->paginate(20);

        return view('client.jobs.applicants', [
            'job' => $job,
            'applications' => $approvedApplications
        ]);
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
            'interview_at' => 'required|date|after:now',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'hiring_status' => 'Interview Scheduled',
            'interview_at' => Carbon::parse($validated['interview_at']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        $this->notifyAdminAndPartner(new InterviewScheduled($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview scheduled successfully!');
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
            'interview_at' => 'required|date|after:now',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'interview_at' => Carbon::parse($validated['interview_at']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview details updated successfully!');
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
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'hiring_status' => 'Selected',
            'joining_date' => Carbon::parse($validated['joining_date']),
            'client_notes' => $validated['client_notes'],
        ]);
        
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
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'joining_date' => Carbon::parse($validated['joining_date']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Selection details updated successfully!');
    }

    public function markAsJoined(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['joined_status' => 'Joined']);
        $this->notifyAdminAndPartner(new CandidateJoined($application), $application);
        return redirect()->back()->with('success', 'Candidate marked as \'Joined\'.');
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

    public function billing()
    {
        $client = Auth::user();
        $fallbackDays = (int) ($client->billable_period_days ?? 30);

        // Per-job invoice_release_days takes precedence over the client's
        // global billable_period_days; fall back to 30 if both are null.
        $hires = JobApplication::where('hiring_status', 'Selected')
            ->whereNotNull('job_applications.joining_date')
            ->whereHas('job', function ($q) use ($client) {
                $q->where('user_id', $client->id);
            })
            ->with(['job', 'candidate', 'candidateUser'])
            ->join('jobs', 'jobs.id', '=', 'job_applications.job_id')
            ->selectRaw(
                "job_applications.*, DATE_ADD(job_applications.joining_date, INTERVAL COALESCE(jobs.invoice_release_days, ?) DAY) as invoice_date",
                [$fallbackDays]
            )
            ->orderByRaw('invoice_date DESC')
            ->paginate(25);

        $statusColors = [
            'Paid'            => 'text-green-600 bg-green-100',
            'Due for Payment' => 'text-red-600 bg-red-100',
            'Maturing'        => 'text-yellow-600 bg-yellow-100',
        ];

        $billingData = $hires->through(function ($hire) use ($statusColors) {
            $invoiceDate = Carbon::parse($hire->invoice_date);

            if ($hire->payment_status === 'paid') {
                $status = 'Paid';
            } elseif ($invoiceDate->isPast() || $invoiceDate->isToday()) {
                $status = 'Due for Payment';
            } else {
                $status = 'Maturing';
            }

            return (object) [
                'candidate_name' => $hire->candidate_name,
                'job_title'      => $hire->job->title,
                'joining_date'   => Carbon::parse($hire->joining_date)->format('M d, Y'),
                'amount'         => $hire->job->payout_amount ? '₹' . number_format($hire->job->payout_amount) : 'N/A',
                'invoice_date'   => $invoiceDate->format('M d, Y'),
                'status'         => $status,
                'status_color'   => $statusColors[$status],
                'paid_at'        => $hire->paid_at ? Carbon::parse($hire->paid_at)->format('M d, Y') : null,
            ];
        });

        return view('client.billing.index', compact('billingData'));
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
