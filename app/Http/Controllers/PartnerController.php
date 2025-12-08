<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Job;
use App\Models\Candidate;
use App\Models\JobApplication;
use App\Models\ExperienceLevel;
use App\Models\EducationLevel;

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

        $applications = JobApplication::whereHas('candidate', function ($query) use ($partner) {
                                          $query->where('partner_id', $partner->id);
                                      })
                                      ->with(['job', 'candidate'])
                                      ->latest()
                                      ->paginate(20);

        return view('partner.applications', ['applications' => $applications]);
    }

    public function jobs(Request $request)
    {
        $partner = Auth::user();

        $query = Job::where('status', 'approved')
            ->whereDoesntHave('excludedPartners', function ($query) use ($partner) {
                $query->where('user_id', $partner->id);
            });

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

        $jobs = $query->with([
            'jobApplications' => function ($query) use ($partner) {
                $query->whereHas('candidate', function ($subQuery) use ($partner) {
                    $subQuery->where('partner_id', $partner->id);
                });
            },
            'experienceLevel',
            'educationLevel'
        ])
        ->latest()
        ->paginate(10)
        ->appends($request->query());

        $jobs->each(function ($job) {
            $stats = [
                'applied' => $job->jobApplications->count(),
                'screened' => $job->jobApplications->where('status', 'Approved')->count(),
                'turned_up' => $job->jobApplications->where('interview_status', 'Appeared')->count(),
                'selected' => $job->jobApplications->where('hiring_status', 'Selected')->count(),
                'joined' => $job->jobApplications->where('hiring_status', 'Joined')->count(),
            ];
            $job->stats = (object)$stats;
        });

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
     * Show the partner's earnings.
     */
    public function earnings()
    {
        $partner = Auth::user();

        $placements = JobApplication::where('hiring_status', 'Joined')
                                    ->whereHas('candidate', function ($query) use ($partner) {
                                        $query->where('partner_id', $partner->id);
                                    })
                                    ->with(['job', 'candidate'])
                                    ->get();

        $today = \Carbon\Carbon::now()->startOfDay();
        $earningsData = [];

        foreach ($placements as $app) {
            if (empty($app->joining_date) || empty($app->job->payout_amount) || empty($app->job->minimum_stay_days)) {
                continue;
            }

            $joiningDate = \Carbon\Carbon::parse($app->joining_date);
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
            return \Carbon\Carbon::parse($item->payout_date);
        });

        return view('partner.earnings.index', [
            'earnings' => $earningsData
        ]);
    }
    
    // --- CANDIDATE MANAGEMENT METHODS ---

    /**
     * Show the form for creating a new candidate profile.
     */
    public function createCandidate()
    {
        return view('partner.candidates.create');
    }

    /**
     * Store a newly created candidate in the database.
     */
    public function storeCandidate(Request $request)
    {
        $partner = Auth::user();

        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:candidates,email,NULL,id,partner_id,'.$partner->id,
            'phone_number' => 'required|string|max:20|unique:candidates,phone_number,NULL,id,partner_id,'.$partner->id,
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

        $validatedData['partner_id'] = $partner->id;

        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }

        Candidate::create($validatedData);

        return redirect()->route('partner.candidates.index')->with('success', 'Candidate added successfully!');
    }

    /**
     * Display a list of all candidates owned by this partner.
     */
    public function listCandidates()
    {
        $partner = Auth::user();
        $candidates = Candidate::where('partner_id', $partner->id)
                                ->latest()
                                ->paginate(20);

        return view('partner.candidates.index', ['candidates' => $candidates]);
    }

    /**
     * *** ADD THIS METHOD ***
     * Show the form for editing an existing candidate.
     */
    public function editCandidate(Candidate $candidate)
    {
        // Ensure the candidate belongs to the logged-in partner
        if ($candidate->partner_id !== Auth::id()) {
            abort(403);
        }

        return view('partner.candidates.edit', compact('candidate'));
    }

    /**
     * *** ADD THIS METHOD ***
     * Update an existing candidate's information.
     */
    public function updateCandidate(Request $request, Candidate $candidate)
    {
        // Ensure the candidate belongs to the logged-in partner
        if ($candidate->partner_id !== Auth::id()) {
            abort(403);
        }

        $partner = Auth::user();

        // Note: The unique validation rule ignores the current candidate's ID ($candidate->id)
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

        // Handle Resume Upload
        if ($request->hasFile('resume')) {
            // Delete old resume if it exists
            if ($candidate->resume_path && Storage::disk('public')->exists($candidate->resume_path)) {
                Storage::disk('public')->delete($candidate->resume_path);
            }
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }

        $candidate->update($validatedData);

        return redirect()->route('partner.candidates.index')->with('success', 'Candidate updated successfully!');
    }

    /**
     * Show the form for submitting candidates for a specific job.
     */
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

    /**
     * Store the new job applications from the partner.
     */
    public function submitApplication(Request $request, Job $job)
    {
        $request->validate([
            'candidate_ids' => 'required|array|min:1',
            'candidate_ids.*' => 'exists:candidates,id',
        ], [
            'candidate_ids.required' => 'You must select at least one candidate to submit.',
            'candidate_ids.min' => 'You must select at least one candidate to submit.'
        ]);

        $partner = Auth::user();
        $submittedCount = 0;

        foreach ($request->input('candidate_ids') as $candidateId) {
            
            $existingApplication = JobApplication::where('job_id', $job->id)
                                                ->where('candidate_id', $candidateId)
                                                ->first();
            
            if (!$existingApplication) {
                JobApplication::create([
                    'job_id' => $job->id,
                    'candidate_id' => $candidateId,
                    'partner_id' => $partner->id,
                    'status' => 'Pending Review',
                ]);
                $submittedCount++;
            }
        }

        if ($submittedCount > 0) {
            $message = $submittedCount . ' ' . \Illuminate\Support\Str::plural('application', $submittedCount) . ' submitted successfully!';
            return redirect()->route('partner.jobs')->with('success', $message);
        } else {
            return redirect()->back()->with('info', 'All selected candidates have already been submitted for this job.');
        }
    }
}