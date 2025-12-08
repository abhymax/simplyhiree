<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobApplication;
use App\Models\ExperienceLevel;
use App\Models\EducationLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * Display a listing of approved jobs with advanced filters.
     */
    public function index(Request $request)
    {
        // 1. Start the query (Approved jobs only)
        $query = Job::with(['user', 'category', 'experienceLevel', 'educationLevel'])
                    ->where('status', 'approved');

        // 2. Keyword Search (Title, Company, Description)
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // 3. Apply Filters
        if ($request->filled('location')) {
            $query->where('location', $request->input('location'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->input('job_type'));
        }

        if ($request->filled('experience_level_id')) {
            $query->where('experience_level_id', $request->input('experience_level_id'));
        }

        // 4. Get Results (Paginated)
        // We append the current query parameters so pagination links work with filters
        $jobs = $query->latest()->paginate(10)->appends($request->query());

        // 5. Get Filter Data for Dropdowns
        $categories = JobCategory::orderBy('name')->get();
        // Only show locations that actually have approved jobs
        $locations = Job::where('status', 'approved')->distinct()->orderBy('location')->pluck('location');
        $experienceLevels = ExperienceLevel::all();
        
        // Hardcoded job types matching the migration validation
        $jobTypes = ['Full-time', 'Part-time', 'Contract', 'Internship'];

        return view('jobs.index', compact('jobs', 'categories', 'locations', 'experienceLevels', 'jobTypes'));
    }

    /**
     * Show the form for creating a new job.
     */
    public function create()
    {
        $categories = JobCategory::orderBy('name')->get();
        $experienceLevels = ExperienceLevel::all();
        $educationLevels = EducationLevel::all();
        return view('jobs.create', compact('categories', 'experienceLevels', 'educationLevels'));
    }

    /**
     * Store a newly created job in the database with a 'pending_approval' status.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'job_type' => 'required|string|in:Full-time,Part-time,Contract,Internship',
            'salary' => 'nullable|string|max:100',
            'category_id' => 'required|exists:job_categories,id',
            'description' => 'required|string',
            'experience_level_id' => 'required|exists:experience_levels,id',
            'education_level_id' => 'required|exists:education_levels,id',
            'skills_required' => 'required|string',
            'application_deadline' => 'required|date|after_or_equal:today',
            'company_website' => 'nullable|url|max:255',
            // Advanced fields
            'openings' => 'nullable|integer|min:1',
            'min_age' => 'nullable|integer|min:18',
            'max_age' => 'nullable|integer|gt:min_age',
            'gender_preference' => 'nullable|string|in:Any,Male,Female',
            'category' => 'nullable|string|max:255',
            'job_type_tags' => 'nullable|string',
            'is_walkin' => 'nullable|boolean',
            'interview_slot' => 'nullable|date',
        ]);

        $validatedData['user_id'] = Auth::id();
        $validatedData['is_walkin'] = $request->has('is_walkin');

        // Process job_type_tags from comma-separated string to array
        if (!empty($validatedData['job_type_tags'])) {
            $validatedData['job_type_tags'] = array_map('trim', explode(',', $validatedData['job_type_tags']));
        }
        
        $job = new Job($validatedData);
        $job->status = 'pending_approval';
        $job->save();

        return redirect()->route('client.dashboard')->with('success', 'Job posted successfully! It is now pending admin approval.');
    }

    /**
     * Display the specified job.
     */
    public function show(Job $job)
    {
        // Ensure the job is approved before showing it
        if ($job->status !== 'approved') {
            abort(404);
        }
        
        // Eager load related data
        $job->load(['user', 'experienceLevel', 'educationLevel', 'category']);

        // Check if the current user has already applied
        $hasApplied = false;
        if (Auth::check()) {
            $hasApplied = JobApplication::where('job_id', $job->id)
                ->where('candidate_user_id', Auth::id())
                ->exists();
        }

        return view('jobs.show', compact('job', 'hasApplied'));
    }

    /**
     * Handle a candidate's application to a job.
     */
    public function apply(Request $request, Job $job)
    {
        // 1. Check if Job is approved
        if ($job->status !== 'approved') {
            abort(404);
        }

        // 2. Prevent Duplicate Applications
        $existingApplication = JobApplication::where('job_id', $job->id)
            ->where('candidate_user_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return redirect()->back()->with('error', 'You have already applied for this job.');
        }

        // 3. Ensure Candidate has a Profile
        // This relies on the User Profile feature we implemented earlier
        if (!auth()->user()->profile) {
            return redirect()->route('candidate.profile.edit')
                ->with('error', 'Please complete your profile details (resume, skills, etc.) before applying.');
        }

        // 4. Create Application
        JobApplication::create([
            'job_id' => $job->id,
            'candidate_user_id' => auth()->user()->id,
            'status' => 'Pending Review', 
        ]);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Application submitted successfully!');
    }
}