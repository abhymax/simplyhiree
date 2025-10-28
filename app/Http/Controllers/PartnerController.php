<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request; // <-- THE TYPO IS FIXED HERE
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\Candidate;
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
        // Placeholder for now.
        $applications = []; 
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
                $query->where('partner_id', $partner->id);
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
}