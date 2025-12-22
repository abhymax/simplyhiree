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
use Illuminate\Support\Str;
use Carbon\Carbon;

class PartnerController extends Controller
{
    /**
     * Show the partner dashboard.
     */
    public function index()
    {
        $partner = Auth::user();
        return view('partner.dashboard', ['partner' => $partner]);
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

        // --- Search Filters ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%")
                  ->orWhere('skills_required', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('location')) {
            $query->where('location', $request->input('location'));
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
        // Note: Using 'jobCategory' to avoid conflict with text column
        $jobs = $query->with([
            'jobApplications' => function ($query) use ($partner) {
                $query->whereHas('candidate', function ($subQuery) use ($partner) {
                    $subQuery->where('partner_id', $partner->id);
                });
            },
            'experienceLevel',
            'educationLevel',
            'jobCategory' 
        ])
        ->latest()
        ->paginate(10)
        ->appends($request->query());

        // --- Calculate Stats ---
        $jobs->each(function ($job) {
            $stats = [
                'applied' => $job->jobApplications->count(),
                'screened' => $job->jobApplications->where('status', 'Approved')->count(),
                'turned_up' => $job->jobApplications->where('hiring_status', 'Interviewed')->count(),
                'selected' => $job->jobApplications->where('hiring_status', 'Selected')->count(),
                'joined' => $job->jobApplications->where('joined_status', 'Joined')->count(),
            ];
            $job->stats = (object)$stats;
        });

        // Filter Options
        $locations = Job::select('location')->distinct()->orderBy('location')->pluck('location');
        $job_types = Job::select('job_type')->distinct()->orderBy('job_type')->pluck('job_type');
        $experienceLevels = ExperienceLevel::all();
        $educationLevels = EducationLevel::all();

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
        $partner = Auth::user();

        // 1. Get Job Details (Eager load using 'jobCategory')
        $job->load(['experienceLevel', 'educationLevel', 'jobCategory', 'user']);

        // 2. Fetch Already Applied Candidates for this Partner
        // Using whereHas to check candidate ownership
        $appliedApplications = JobApplication::where('job_id', $job->id)
                                             ->whereHas('candidate', function ($query) use ($partner) {
                                                 $query->where('partner_id', $partner->id);
                                             })
                                             ->with('candidate')
                                             ->latest()
                                             ->get();

        $appliedCandidateIds = $appliedApplications->pluck('candidate_id')->toArray();

        // 3. Find Matching Candidates from this Partner's Pool
        // Exclude candidates who have already applied
        $myCandidates = Candidate::where('partner_id', $partner->id)
                                 ->whereNotIn('id', $appliedCandidateIds)
                                 ->get();
        
        $matchingCandidates = $myCandidates->filter(function ($candidate) use ($job) {
            // Simple Match Logic
            $jobKeywords = strtolower($job->title . ' ' . $job->skills_required);
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
            if (empty($app->joining_date) || empty($app->job->payout_amount) || empty($app->job->minimum_stay_days)) {
                continue;
            }

            $joiningDate = Carbon::parse($app->joining_date);
            $payoutDate = $joiningDate->copy()->addDays($app->job->minimum_stay_days);

            $isEligible = $payoutDate->isPast() && is_null($app->left_at);
            $status = $isEligible ? 'Eligible' : 'Pending';

            $earningsData[] = (object) [
                'candidate_name' => $app->candidate->first_name . ' ' . $app->candidate->last_name,
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
    
    // --- CANDIDATE MANAGEMENT (Mobile-First Workflow) ---

    /**
     * Step 1: Check Mobile Screen
     */
    public function checkCandidateMobile()
    {
        return view('partner.candidates.check-mobile');
    }

    /**
     * Step 2: Verify Mobile Existence
     */
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

    /**
     * Step 3: Create Form (Requires Mobile)
     */
    public function createCandidate(Request $request)
    {
        if (!$request->has('mobile')) {
            return redirect()->route('partner.candidates.check');
        }

        $mobile = $request->input('mobile');
        return view('partner.candidates.create', compact('mobile'));
    }

    /**
     * Step 4: Store Candidate
     */
    public function storeCandidate(Request $request)
    {
        $partner = Auth::user();

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:candidates,email,NULL,id,partner_id,'.$partner->id,
            'phone_number' => 'required|string|max:20|unique:candidates,phone_number,NULL,id,partner_id,'.$partner->id,
            // ... (keep other validations optional for quick add, or add them if needed)
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        $validatedData['partner_id'] = $partner->id;

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }

        $candidate = Candidate::create($validatedData);

        // Check if the request expects JSON (AJAX request from the Apply page)
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
            'location' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'job_interest' => 'nullable|string|max:255',
            'education_level' => 'nullable|string|max:255',
            'experience_status' => 'nullable|string|in:Experienced,Fresher',
            'expected_ctc' => 'nullable|numeric|min:0',
            'notice_period' => 'nullable|string|max:100',
            'job_role_preference' => 'nullable|string',
            'languages_spoken' => 'nullable|string',
            'skills' => 'nullable|string',
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('resume')) {
            if ($candidate->resume_path && Storage::disk('public')->exists($candidate->resume_path)) {
                Storage::disk('public')->delete($candidate->resume_path);
            }
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }

        $candidate->update($validatedData);

        return redirect()->route('partner.candidates.index')->with('success', 'Candidate updated successfully!');
    }

    public function showApplyForm(Job $job)
    {
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
        $request->validate([
            'candidate_ids' => 'required|array|min:1',
            'candidate_ids.*' => 'exists:candidates,id',
        ]);

        $partner = Auth::user();
        $submittedCount = 0;

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
                    'job_id' => $job->id,
                    'candidate_id' => $candidateId,
                    // No partner_id column needed, implied via candidate
                    'status' => 'Pending Review',
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