<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // <-- THE TYPO IS FIXED HERE
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\Candidate;
use App\Models\JobApplication;
use Illuminate\Support\Facades\Storage;

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

        // Get all applications where the related candidate
        // belongs to this partner.
        $applications = JobApplication::whereHas('candidate', function ($query) use ($partner) {
                                          $query->where('partner_id', $partner->id);
                                      })
                                      ->with(['job', 'candidate'])
                                      ->latest()
                                      ->paginate(20); // Paginate the results

        return view('partner.applications', ['applications' => $applications]);
    }

    /**
     * Show the available job vacancies for the partner.
     */
    public function jobs()
    {
        $partner = Auth::user();
        $jobs = Job::where('status', 'approved')
            ->whereDoesntHave('excludedPartners', function ($query) use ($partner) {
                $query->where('user_id', $partner->id);
            })
            ->latest()
            ->get();
        return view('partner.jobs', ['jobs' => $jobs]);
    }

    /**
     * Show the partner's earnings.
     */
    public function earnings()
    {
        // This is a placeholder for now.
        return view('partner.earnings');
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
            'resume' => 'nullable|file|mimes:pdf,doc,docx|max:2048', // Max 2MB for resume
        ]);

        $validatedData['partner_id'] = $partner->id;

        // Handle the resume upload
        if ($request->hasFile('resume')) {
            $path = $request->file('resume')->store('resumes', 'public');
            $validatedData['resume_path'] = $path;
        }

        Candidate::create($validatedData);

        return redirect()->route('partner.dashboard')->with('success', 'Candidate added successfully!');
    }

    /**
     * *** NEW METHOD ***
     * Display a list of all candidates owned by this partner.
     */
    public function listCandidates()
    {
        $partner = Auth::user();
        $candidates = Candidate::where('partner_id', $partner->id)
                                ->latest()
                                ->paginate(20); // Paginate the list

        return view('partner.candidates.index', ['candidates' => $candidates]);
    }
    /**
     * *** NEW METHOD (STEP 4) ***
     * Show the form for submitting candidates for a specific job.
     */
    public function showApplyForm(Job $job)
    {
        $partner = Auth::user();

        // Get all candidates belonging to this partner
        $candidates = Candidate::where('partner_id', $partner->id)
                                ->latest()
                                ->get();

        // You might want to add logic here to filter out candidates
        // who have already applied for this job. We can add that later.

        return view('partner.jobs.apply', [
            'job' => $job,
            'candidates' => $candidates
        ]);
    }
    /**
     * *** NEW METHOD (STEP 5) ***
     * Store the new job applications from the partner.
     */
    public function submitApplication(Request $request, Job $job)
    {
        $request->validate([
            'candidate_ids' => 'required|array|min:1',
            'candidate_ids.*' => 'exists:candidates,id', // Ensure all IDs are valid candidates
        ], [
            'candidate_ids.required' => 'You must select at least one candidate to submit.',
            'candidate_ids.min' => 'You must select at least one candidate to submit.'
        ]);

        $partner = Auth::user();
        $submittedCount = 0;

        foreach ($request->input('candidate_ids') as $candidateId) {
            
            // --- Check for duplicates (Optional but Recommended) ---
            // This prevents a partner from submitting the same candidate twice for the same job.
            $existingApplication = JobApplication::where('job_id', $job->id)
                                                ->where('candidate_id', $candidateId)
                                                ->first();
            
            if (!$existingApplication) {
                JobApplication::create([
                    'job_id' => $job->id,
                    'candidate_id' => $candidateId,
                    'partner_id' => $partner->id,
                    'status' => 'Pending Review', // Set the initial status
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