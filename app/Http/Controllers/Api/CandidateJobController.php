<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class CandidateJobController extends Controller
{
    public function index(Request $request)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        $query = Job::query()->where('status', 'approved');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('skills_required', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->input('job_type'));
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);

        $jobs = $query
            ->with(['jobCategory', 'experienceLevel', 'educationLevel'])
            ->withCount([
                'jobApplications as has_applied_count' => function ($q) use ($candidate) {
                    $q->where('candidate_user_id', $candidate->id);
                },
            ])
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $data = $jobs->getCollection()->map(function (Job $job) {
            return $this->transformJob($job);
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ],
        ]);
    }

    public function show(Request $request, Job $job)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        if ((string) $job->status !== 'approved') {
            return response()->json(['message' => 'Job not available.'], 404);
        }

        $job->load(['jobCategory', 'experienceLevel', 'educationLevel']);
        $job->loadCount([
            'jobApplications as has_applied_count' => function ($q) use ($candidate) {
                $q->where('candidate_user_id', $candidate->id);
            },
        ]);

        return response()->json([
            'data' => $this->transformJob($job),
        ]);
    }

    public function apply(Request $request, Job $job)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        if ((string) $job->status !== 'approved') {
            return response()->json(['message' => 'Job not available.'], 422);
        }

        $alreadyApplied = JobApplication::query()
            ->where('job_id', $job->id)
            ->where('candidate_user_id', $candidate->id)
            ->exists();

        if ($alreadyApplied) {
            return response()->json(['message' => 'You have already applied for this job.'], 422);
        }

        $profile = $candidate->profile;
        $isProfileComplete = $profile
            && !empty($profile->phone_number)
            && !empty($profile->location)
            && !empty($profile->experience_status)
            && !empty($profile->skills);

        if (!$isProfileComplete) {
            return response()->json([
                'message' => 'Please complete your profile before applying.',
                'profile_required' => true,
            ], 422);
        }

        JobApplication::create([
            'job_id' => $job->id,
            'candidate_user_id' => $candidate->id,
            'status' => 'Pending Review',
        ]);

        return response()->json([
            'message' => 'Application submitted successfully.',
        ], 201);
    }

    private function transformJob(Job $job): array
    {
        $experienceLevel = $job->experienceLevel;
        $educationLevel = $job->educationLevel;
        $category = $job->jobCategory;

        return [
            'id' => $job->id,
            'title' => $job->title,
            'company_name' => $job->company_name,
            'location' => $job->location,
            'job_type' => $job->job_type,
            'salary' => $job->salary,
            'description' => $job->description,
            'skills_required' => $job->skills_required,
            'openings' => $job->openings,
            'status' => $job->status,
            'application_deadline' => optional($job->application_deadline)->toDateString(),
            'created_at' => optional($job->created_at)->toIso8601String(),
            'category' => [
                'id' => $category?->id ?? $job->category_id,
                'name' => $category?->name ?? (is_string($job->category) ? $job->category : null),
            ],
            'experience' => [
                'min' => $job->min_experience,
                'max' => $job->max_experience,
                'level' => $experienceLevel?->name,
            ],
            'education' => [
                'id' => $educationLevel?->id,
                'name' => $educationLevel?->name,
            ],
            'has_applied' => ((int) ($job->has_applied_count ?? 0)) > 0,
        ];
    }
}
