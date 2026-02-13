<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerJobResource;
use App\Models\Candidate;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PartnerJobController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $query = Job::query()->where('status', 'approved');

        if (Schema::hasTable('job_partner_exclusions')) {
            $query->whereDoesntHave('excludedPartners', function ($q) use ($partner) {
                $q->where('users.id', $partner->id);
            });
        }

        if (Schema::hasTable('job_partner_access')) {
            $query->where(function ($q) use ($partner) {
                $q->where('partner_visibility', 'all')
                    ->orWhereNull('partner_visibility')
                    ->orWhere(function ($subQ) use ($partner) {
                        $subQ->where('partner_visibility', 'selected')
                            ->whereHas('allowedPartners', function ($p) use ($partner) {
                                $p->where('users.id', $partner->id);
                            });
                    });
            });
        }

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('company_name', 'like', "%{$searchTerm}%")
                    ->orWhere('skills_required', 'like', "%{$searchTerm}%");
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
                'jobApplications as partner_applications_count' => function ($q) use ($partner) {
                    $q->whereHas('candidate', function ($subQ) use ($partner) {
                        $subQ->where('partner_id', $partner->id);
                    });
                },
            ])
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return PartnerJobResource::collection($jobs)->additional([
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
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        if ((string) $job->status !== 'approved') {
            return response()->json(['message' => 'Job not available.'], 404);
        }

        $job->load(['jobCategory', 'experienceLevel', 'educationLevel']);

        return (new PartnerJobResource($job))->additional([
            'meta' => [
                'can_apply' => true,
            ],
        ]);
    }

    public function apply(Request $request, Job $job)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        if ((string) $job->status !== 'approved') {
            return response()->json(['message' => 'Job not available.'], 422);
        }

        $validated = $request->validate([
            'candidate_ids' => ['required', 'array', 'min:1'],
            'candidate_ids.*' => ['required', 'integer', 'exists:candidates,id'],
        ]);

        $submittedCount = 0;

        foreach ($validated['candidate_ids'] as $candidateId) {
            $candidate = Candidate::query()
                ->where('id', $candidateId)
                ->where('partner_id', $partner->id)
                ->first();

            if (!$candidate) {
                continue;
            }

            $exists = JobApplication::query()
                ->where('job_id', $job->id)
                ->where('candidate_id', $candidateId)
                ->exists();

            if ($exists) {
                continue;
            }

            JobApplication::create([
                'job_id' => $job->id,
                'candidate_id' => $candidateId,
                'status' => 'Pending Review',
            ]);

            $submittedCount++;
        }

        if ($submittedCount === 0) {
            return response()->json([
                'message' => 'All selected candidates have already been submitted for this job.',
                'submitted_count' => 0,
            ], 422);
        }

        return response()->json([
            'message' => $submittedCount . ' ' . Str::plural('application', $submittedCount) . ' submitted successfully.',
            'submitted_count' => $submittedCount,
        ]);
    }
}
