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
                'gender_preferences' => ['Any', 'Male', 'Female', 'Other'],
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
                'gender_preference' => $job->gender_preference ?? 'Any',
                'description' => $job->description,
                'openings' => $job->openings,
                'work_mode' => $job->work_mode,
                'shift' => $job->shift,
                'specialization' => $job->specialization,
                'notice_period' => $job->notice_period,
                'languages' => $job->languages,
                'industry' => $job->industry,
                'department' => $job->department,
                'reporting_manager' => $job->reporting_manager,
                'travel_required' => is_null($job->travel_required) ? null : (bool) $job->travel_required,
                'benefits' => is_array($job->benefits) ? $job->benefits : [],
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
            'gender_preference' => ['required', 'string', 'in:Any,Male,Female,Other'],
            'application_deadline' => ['nullable', 'date'],
            'skills_required' => ['nullable', 'string'],
            'company_website' => ['nullable', 'url'],
            'openings' => ['nullable', 'integer', 'min:1'],
            // Extended job-detail fields (all optional)
            'work_mode' => ['nullable', 'string', 'in:On-site,Hybrid,Remote,Field Job,Work From Home (WFH)'],
            'shift' => ['nullable', 'string', 'in:Day Shift,Night Shift,Rotational Shift,Flexible Shift,Weekend Shift'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'notice_period' => ['nullable', 'string', 'max:100'],
            'languages' => ['nullable', 'string', 'max:255'],
            'industry' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'reporting_manager' => ['nullable', 'string', 'max:255'],
            'travel_required' => ['nullable', 'boolean'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'in:PF,ESIC,Insurance,Food,Transport,Accommodation,Laptop,Mobile,Joining Bonus,Relocation'],
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
            'gender_preference' => $validated['gender_preference'],
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
            // Extended job-detail fields
            'work_mode' => $validated['work_mode'] ?? null,
            'shift' => $validated['shift'] ?? null,
            'specialization' => $validated['specialization'] ?? null,
            'notice_period' => $validated['notice_period'] ?? null,
            'languages' => $validated['languages'] ?? null,
            'industry' => $validated['industry'] ?? null,
            'department' => $validated['department'] ?? null,
            'reporting_manager' => $validated['reporting_manager'] ?? null,
            'travel_required' => array_key_exists('travel_required', $validated) ? (bool) $validated['travel_required'] : null,
            'benefits' => $validated['benefits'] ?? null,
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
                'gender_preference' => $job->gender_preference ?? 'Any',
                'category' => $job->jobCategory?->name,
                'education' => $job->educationLevel?->name,
            ],
        ], 201);
    }
}
