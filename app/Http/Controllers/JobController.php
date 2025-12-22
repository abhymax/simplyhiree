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
        // 1. Start the query (Approved jobs only for public listing)
        // Ensure we use 'jobCategory' relationship to avoid model conflicts
        $query = Job::with(['user', 'jobCategory', 'experienceLevel', 'educationLevel'])
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
        $jobs = $query->latest()->paginate(10)->appends($request->query());

        // 5. Get Filter Data for Dropdowns
        $categories = JobCategory::orderBy('name')->get();
        // Only show locations from approved jobs
        $locations = Job::where('status', 'approved')->distinct()->orderBy('location')->pluck('location');
        $experienceLevels = ExperienceLevel::all();
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
     * Store a newly created job in the database.
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
            'category' => 'nullable|string|max:255', // Text column fallback
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
        $user = Auth::user();
        
        // Check permissions
        $isOwner = $user && $user->id === $job->user_id;
        $isAdmin = $user && $user->hasRole('Superadmin');
        
        // Access Control Rule:
        // If job is NOT approved, ONLY the Owner or Admin can see it.
        if ($job->status !== 'approved' && !$isOwner && !$isAdmin) {
            abort(404);
        }
        
        // Eager load using 'jobCategory'
        $job->load(['user', 'experienceLevel', 'educationLevel', 'jobCategory']);

        // Check if the current user has already applied
        $hasApplied = false;
        if (Auth::check()) {
            $hasApplied = JobApplication::where('job_id', $job->id)
                ->where('candidate_user_id', Auth::id())
                ->exists();
        }

        return view('jobs.show', compact('job', 'hasApplied', 'isOwner', 'isAdmin'));
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
        if (!auth()->user()->profile) {
            return redirect()->route('candidate.profile.edit')
                ->with('error', 'Please complete your profile details before applying.');
        }

        // 4. Create Application
        JobApplication::create([
            'job_id' => $job->id,
            'candidate_user_id' => auth()->user()->id,
            'status' => 'Pending Review', 
        ]);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Application submitted successfully!');
    }

    /**
     * NEW: Client - Change Job Status (Hold / Close / Re-open).
     * Fixed: Replaced match() with array lookup to prevent syntax errors.
     */
    public function updateStatus(Request $request, Job $job)
    {
        // Security: Ensure user owns this job
        if (Auth::id() !== $job->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:approved,on_hold,closed',
        ]);

        $job->update(['status' => $request->status]);

        // Using array lookup instead of match() for compatibility
        $statusMessages = [
            'on_hold' => 'Job put on hold. It is now hidden from candidates.',
            'closed' => 'Job marked as closed/unavailable.',
            'approved' => 'Job is now live and visible.',
        ];

        $message = $statusMessages[$request->status] ?? 'Job status updated.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * NEW: Client - Delete/Archive Job.
     */
    public function destroy(Job $job)
    {
        // Security: Ensure user owns this job
        if (Auth::id() !== $job->user_id) {
            abort(403, 'Unauthorized action.');
        }

        $job->delete();

        return redirect()->route('client.dashboard')->with('success', 'Job deleted successfully.');
    }
}