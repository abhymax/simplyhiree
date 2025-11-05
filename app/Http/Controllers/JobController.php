<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobCategory;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    /**
     * Show the form for creating a new job.
     */
    public function create()
    {
        $categories = JobCategory::orderBy('name')->get();
        return view('jobs.create', ['categories' => $categories]);
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
            'experience_required' => 'required|string|max:100',
            'education_level' => 'required|string|max:100',
            'skills_required' => 'required|string',
            'application_deadline' => 'required|date|after_or_equal:today',
            'company_website' => 'nullable|url|max:255',
            // New fields validation
            'openings' => 'nullable|integer|min:1',
            'min_age' => 'nullable|integer|min:18',
            'max_age' => 'nullable|integer|gt:min_age',
            'gender_preference' => 'nullable|string|in:Any,Male,Female',
            'category' => 'nullable|string|max:255',
            'job_type_tags' => 'nullable|string',
        ]);

        $validatedData['user_id'] = Auth::id();

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
     * Display a listing of approved jobs for candidates.
     */
    public function index()
    {
        // Now only shows jobs that have been approved by an admin.
        $jobs = Job::with('user')->where('status', 'approved')->latest()->get();
        return view('jobs.index', ['jobs' => $jobs]);
    }

    /**
     * Handle a candidate's application to a job.
     */
    public function apply(Request $request, Job $job)
    {
        // This logic remains the same, but candidates can only apply to 'approved' jobs.
        JobApplication::create([
            'job_id' => $job->id,
            'candidate_user_id' => auth()->user()->id,
            'status' => 'applied',
        ]);

        return redirect()->back()->with('success', 'Application submitted successfully!');
    }
}
