<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EducationLevel;
use App\Models\Job;
use App\Models\JobCategory;
use Illuminate\Http\Request;

class ClientJobController extends Controller
{
    public function formData(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        return response()->json([
            'data' => [
                'categories' => JobCategory::query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get(),
                'education_levels' => EducationLevel::query()
                    ->select(['id', 'name'])
                    ->orderBy('name')
                    ->get(),
                'job_types' => ['Full-Time', 'Part-Time', 'Contract', 'Internship', 'Remote'],
            ],
        ]);
    }

    public function index(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);

        $jobs = Job::query()
            ->where('user_id', $client->id)
            ->with(['jobCategory', 'educationLevel'])
            ->withCount('jobApplications')
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $data = $jobs->getCollection()->map(function (Job $job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company_name,
                'location' => $job->location,
                'salary' => $job->salary,
                'status' => $job->status,
                'job_type' => $job->job_type,
                'description' => $job->description,
                'openings' => $job->openings,
                'category' => [
                    'id' => $job->jobCategory?->id ?? $job->category_id,
                    'name' => $job->jobCategory?->name,
                ],
                'education' => [
                    'id' => $job->educationLevel?->id,
                    'name' => $job->educationLevel?->name,
                ],
                'application_deadline' => optional($job->application_deadline)->toDateString(),
                'applications_count' => (int) $job->job_applications_count,
                'created_at' => optional($job->created_at)->toIso8601String(),
            ];
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

    public function store(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:job_categories,id'],
            'location' => ['required', 'string', 'max:255'],
            'salary' => ['nullable', 'string', 'max:255'],
            'job_type' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'min_experience' => ['required', 'integer', 'min:0', 'max:50'],
            'max_experience' => ['required', 'integer', 'gte:min_experience', 'max:50'],
            'education_level_id' => ['required', 'exists:education_levels,id'],
            'application_deadline' => ['nullable', 'date'],
            'skills_required' => ['nullable', 'string'],
            'company_website' => ['nullable', 'url'],
            'openings' => ['nullable', 'integer', 'min:1'],
        ]);

        $job = Job::create([
            'user_id' => $client->id,
            'company_name' => $client->name,
            'status' => 'pending_approval',
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'location' => $validated['location'],
            'salary' => $validated['salary'] ?? null,
            'job_type' => $validated['job_type'],
            'description' => $validated['description'],
            'min_experience' => $validated['min_experience'],
            'max_experience' => $validated['max_experience'],
            'experience_level_id' => null,
            'education_level_id' => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'] ?? null,
            'skills_required' => $validated['skills_required'] ?? null,
            'company_website' => $validated['company_website'] ?? null,
            'openings' => $validated['openings'] ?? 1,
            'partner_visibility' => 'all',
        ]);

        $job->load(['jobCategory', 'educationLevel']);

        return response()->json([
            'message' => 'Job posted successfully. Waiting for admin approval.',
            'data' => [
                'id' => $job->id,
                'title' => $job->title,
                'status' => $job->status,
                'location' => $job->location,
                'job_type' => $job->job_type,
                'category' => $job->jobCategory?->name,
                'education' => $job->educationLevel?->name,
            ],
        ], 201);
    }
}

