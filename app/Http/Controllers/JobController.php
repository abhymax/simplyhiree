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
        $query = Job::with(['user', 'jobCategory', 'experienceLevel', 'educationLevel'])
                    ->where('status', 'approved');

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

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

        $jobs = $query->latest()->paginate(10)->appends($request->query());

        $categories = JobCategory::orderBy('name')->get();
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

        $isOwner = $user && $user->id === $job->user_id;
        $isAdmin = $user && $user->hasRole('Superadmin');

        if ($job->status !== 'approved' && !$isOwner && !$isAdmin) {
            abort(404);
        }

        $job->load(['user', 'experienceLevel', 'educationLevel', 'jobCategory']);

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
        if ($job->status !== 'approved') {
            abort(404);
        }

        $existingApplication = JobApplication::where('job_id', $job->id)
            ->where('candidate_user_id', Auth::id())
            ->first();

        if ($existingApplication) {
            return redirect()->back()->with('error', 'You have already applied for this job.');
        }

        $profile = auth()->user()->profile;
        $isProfileComplete = $profile
            && !empty(auth()->user()->name)
            && !empty(auth()->user()->email)
            && !empty($profile->phone_number)
            && !empty($profile->location)
            && !empty($profile->date_of_birth)
            && !empty($profile->gender)
            && !empty($profile->experience_status)
            && !empty($profile->skills);

        if (!$isProfileComplete) {
            return redirect()->route('candidate.profile.edit')
                ->with('error', 'Please complete your profile details before applying.');
        }

        JobApplication::create([
            'job_id' => $job->id,
            'candidate_user_id' => auth()->user()->id,
            'status' => 'Pending Review',
        ]);

        return redirect()->route('jobs.show', $job->id)->with('success', 'Application submitted successfully!');
    }

    /**
     * Client/Admin - Change Job Status.
     */
    public function updateStatus(Request $request, Job $job)
    {
        if (Auth::id() !== $job->user_id && !(Auth::user() && Auth::user()->hasRole('Superadmin'))) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:approved,on_hold,closed',
        ]);

        $job->update(['status' => $request->status]);

        $statusMessages = [
            'on_hold' => 'Job put on hold. It is now hidden from candidates.',
            'closed' => 'Job marked as closed/unavailable.',
            'approved' => 'Job is now live and visible.',
        ];

        $message = $statusMessages[$request->status] ?? 'Job status updated.';

        return redirect()->back()->with('success', $message);
    }

    /**
     * Deletion disabled for client accounts.
     */
    public function destroy(Job $job)
    {
        abort(403, 'Job deletion is disabled for client accounts.');
    }
}
