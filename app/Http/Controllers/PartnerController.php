<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\Candidate;
use App\Models\JobApplication;
use App\Models\ExperienceLevel;
use App\Models\EducationLevel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PartnerController extends Controller
{
    public function upgrade()
    {
        $partner = Auth::user();
        $existing = \App\Models\PlanChangeRequest::where('partner_id', $partner->partnerOwnerId())
            ->where('status', 'pending')
            ->latest()
            ->first();
        return view('partner.upgrade', ['partner' => $partner, 'pendingRequest' => $existing]);
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

        $myApps = JobApplication::whereHas('candidate', fn ($q) => $q->where('partner_id', $partner->id))
            ->with(['job', 'candidate', 'partnerCreditNote']);

        $activeCount = (clone $myApps)->where('joined_status', 'Joined')->whereNull('replacement_status')->count();
        $underGuaranteeCount = (clone $myApps)
            ->where('joined_status', 'Joined')
            ->whereNotNull('joining_date')
            ->whereRaw('DATE_ADD(joining_date, INTERVAL COALESCE(replacement_window_days, 90) DAY) >= NOW()')
            ->count();
        $replacementRequiredCount = (clone $myApps)->where('replacement_status', 'window_open')->count();

        $credits = \App\Models\PartnerCreditNote::where('partner_id', $partner->id)
            ->with(['sourceApplication.job', 'sourceApplication.candidate'])
            ->latest()
            ->paginate(20);

        $totals = [
            'pending'   => \App\Models\PartnerCreditNote::where('partner_id', $partner->id)->where('status', 'pending')->sum('amount'),
            'applied'   => \App\Models\PartnerCreditNote::where('partner_id', $partner->id)->where('status', 'applied')->sum('amount'),
            'cancelled' => \App\Models\PartnerCreditNote::where('partner_id', $partner->id)->where('status', 'cancelled')->sum('amount'),
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

        // --- Morning Brief Data ---
        $todayInterviews = JobApplication::whereHas('candidate', function ($query) use ($partner) {
                $query->where('partner_id', $partner->id);
            })
            ->whereDate('interview_at', Carbon::today())
            ->count();

        // Replacement requests raised by clients for this partner's candidates
        $replacementRequests = JobApplication::with(['job', 'candidate'])
            ->whereNotNull('replacement_requested_at')
            ->whereHas('candidate', fn ($q) => $q->where('partner_id', $partner->id))
            ->latest('replacement_requested_at')
            ->limit(10)
            ->get();

        return view('partner.dashboard', [
            'partner'             => $partner,
            'todayInterviews'     => $todayInterviews,
            'replacementRequests' => $replacementRequests,
        ]);
    }

    /**
     * Show the applications related to this partner.
     */
    public function applications()
    {
        $partner = Auth::user();

        // Retrieve applications where the candidate belongs to the logged-in partner
        $applications = JobApplication::whereHas('candidate', function ($query) use ($partner) {
                                    $query->where('partner_id', $partner->id);
                                })
                                ->with(['job', 'candidate'])
                                ->latest()
                                ->paginate(20);

        // Strip fields partners must not see (client billing, deal value, etc.)
        $applications->getCollection()->each(function ($app) {
            $app->makeHidden(['client_notes', 'final_ctc', 'invoice_amount', 'fee_percent', 'fee_flat']);
            if ($app->job) $app->job->makeHidden(['user_id', 'invoice_amount']);
        });

        return view('partner.applications', ['applications' => $applications]);
    }

    /**
     * List available jobs with filtering and visibility checks.
     */
    public function jobs(Request $request)
    {
        $partner = Auth::user();

        $query = Job::where('status', 'approved')
            // 1. Check Global Exclusions
            ->whereDoesntHave('excludedPartners', function ($q) use ($partner) {
                $q->where('user_id', $partner->id);
            })
            // 2. Check Visibility Logic (Admin Feature)
            ->where(function ($q) use ($partner) {
                // Show if Visibility is 'all'
                $q->where('partner_visibility', 'all')
                  // OR if Visibility is 'selected' AND partner is in allowed list
                  ->orWhere(function ($subQ) use ($partner) {
                      $subQ->where('partner_visibility', 'selected')
                           ->whereHas('allowedPartners', function ($p) use ($partner) {
                               $p->where('partner_id', $partner->id);
                           });
                  });
            });

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
            // Job-wide stats — every application on the job, used to compute
            // the "how popular is this job" funnel in the listing.
            'jobApplications',
            'experienceLevel',
            'educationLevel',
            'category'
        ])
        ->latest()
        ->paginate(10)
        ->appends($request->query());

        $jobs->each(function ($job) use ($partner) {
            $allApps = $job->jobApplications;

            // Job-wide funnel — every partner's submissions combined.
            // Gives a real signal of how competitive the role is.
            $stats = [
                'applied'   => $allApps->count(),
                'screened'  => $allApps->where('status', 'Approved')->count(),
                'turned_up' => $allApps->whereIn('hiring_status', ['Interviewed', 'Selected'])->count() + $allApps->where('joined_status', 'Joined')->count(),
                'selected'  => $allApps->where('hiring_status', 'Selected')->count() + $allApps->where('joined_status', 'Joined')->count(),
                'joined'    => $allApps->where('joined_status', 'Joined')->count(),
            ];
            $job->stats = (object) $stats;

            // The partner's own funnel — used for the click-through label
            // and the stage chip count.
            $mine = \App\Models\JobApplication::where('job_id', $job->id)
                ->whereHas('candidate', fn ($q) => $q->where('partner_id', $partner->id))
                ->get();
            $job->my_stats = (object) [
                'applied'   => $mine->count(),
                'screened'  => $mine->where('status', 'Approved')->count(),
                'turned_up' => $mine->whereIn('hiring_status', ['Interviewed', 'Selected'])->count() + $mine->where('joined_status', 'Joined')->count(),
                'selected'  => $mine->where('hiring_status', 'Selected')->count() + $mine->where('joined_status', 'Joined')->count(),
                'joined'    => $mine->where('joined_status', 'Joined')->count(),
            ];

            // Hide client/billing fields. Partners only need to see the
            // partner-payout amount, never the full deal value.
            $job->makeHidden(['user_id', 'invoice_amount']);
            foreach ($allApps as $app) {
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
        if ($job->status !== 'approved') {
            abort(404, 'This job is currently not available.');
        }

        $partner = Auth::user();

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
                                             ->whereHas('candidate', function ($query) use ($partner) {
                                                 $query->where('partner_id', $partner->id);
                                             })
                                             ->with('candidate')
                                             ->latest()
                                             ->get();

        $appliedCandidateIds = $appliedApplications->pluck('candidate_id')->toArray();

        // 3. Find Matching Candidates from this Partner's Pool
        $myCandidates = Candidate::where('partner_id', $partner->id)
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

        $placements = JobApplication::where('joined_status', 'Joined')
                                    ->whereHas('candidate', function ($query) use ($partner) {
                                        $query->where('partner_id', $partner->id);
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
        $phone = $request->input('phone_number');

        $existingCandidate = Candidate::where('partner_id', $partner->id)
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

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:candidates,email,NULL,id,partner_id,'.$partner->id,
            'phone_number' => 'required|string|max:20|unique:candidates,phone_number,NULL,id,partner_id,'.$partner->id,
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

        $validatedData['partner_id'] = $partner->id;
        $validatedData['preferred_locations'] = array_values(array_filter(array_map('trim', explode(',', $validatedData['preferred_locations']))));

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }
        unset($validatedData['resume']);

        $candidate = Candidate::create($validatedData);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'candidate' => $candidate,
                'message' => 'Candidate added successfully'
            ]);
        }

        return redirect()->route('partner.candidates.index')->with('success', 'Candidate added successfully!');
    }

    public function listCandidates()
    {
        $partner = Auth::user();
        $candidates = Candidate::where('partner_id', $partner->id)
                               ->latest()
                               ->paginate(20);

        return view('partner.candidates.index', ['candidates' => $candidates]);
    }

    public function editCandidate(Candidate $candidate)
    {
        if ($candidate->partner_id !== Auth::id()) {
            abort(403);
        }
        return view('partner.candidates.edit', compact('candidate'));
    }

    public function updateCandidate(Request $request, Candidate $candidate)
    {
        if ($candidate->partner_id !== Auth::id()) {
            abort(403);
        }

        $partner = Auth::user();

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:candidates,email,'.$candidate->id.',id,partner_id,'.$partner->id,
            'phone_number' => 'required|string|max:20|unique:candidates,phone_number,'.$candidate->id.',id,partner_id,'.$partner->id,
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

        if ($request->hasFile('resume')) {
            if ($candidate->resume_path && Storage::disk('public')->exists($candidate->resume_path)) {
                Storage::disk('public')->delete($candidate->resume_path);
            }
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }
        unset($validatedData['resume']);

        $candidate->update($validatedData);

        return redirect()->route('partner.candidates.index')->with('success', 'Candidate updated successfully!');
    }

    public function showApplyForm(Job $job)
    {
        if ($job->status !== 'approved') {
            abort(404, 'This job is currently not available.');
        }

        $partner = Auth::user();
        $candidates = Candidate::where('partner_id', $partner->id)
                                ->latest()
                                ->get();

        return view('partner.jobs.apply', [
            'job' => $job,
            'candidates' => $candidates
        ]);
    }

    public function submitApplication(Request $request, Job $job)
    {
        if ($job->status !== 'approved') {
            return back()->with('error', 'This job is no longer accepting applications.');
        }

        $request->validate([
            'candidate_ids' => 'required|array|min:1',
            'candidate_ids.*' => 'exists:candidates,id',
        ]);

        $partner = Auth::user();
        // 3. Plan-based monthly submission cap.
        $ownerId   = $partner->partnerOwnerId();
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
            $attemptingToSubmit = count($request->input('candidate_ids'));
            if (($thisMonth + $attemptingToSubmit) > $cap) {
                return redirect()->back()->with('error',
                    "Your {$ownerPlan} plan allows {$cap} submissions / month. You've used {$thisMonth}. Upgrade your plan to submit more.");
            }
        }

        $submittedCount = 0;
        // Screening branching: Mode 3 (client unchecked) skips admin queue.
        $initialStatus = ($job->screening_required ?? true) ? 'Pending Review' : 'Approved';

        foreach ($request->input('candidate_ids') as $candidateId) {
            // Verify candidate belongs to partner
            $candidate = Candidate::where('id', $candidateId)
                                  ->where('partner_id', $partner->id)
                                  ->first();
            
            if (!$candidate) continue;

            $existingApplication = JobApplication::where('job_id', $job->id)
                                                 ->where('candidate_id', $candidateId)
                                                 ->first();
            
            if (!$existingApplication) {
                JobApplication::create([
                    'job_id'              => $job->id,
                    'candidate_id'        => $candidateId,
                    'status'              => $initialStatus,
                    'submitted_by_user_id' => Auth::id(),
                ]);
                $submittedCount++;
            }
        }

        if ($submittedCount > 0) {
            $message = $submittedCount . ' ' . Str::plural('application', $submittedCount) . ' submitted successfully!';
            return redirect()->route('partner.jobs')->with('success', $message);
        } else {
            return redirect()->back()->with('info', 'All selected candidates have already been submitted for this job.');
        }
    }
}