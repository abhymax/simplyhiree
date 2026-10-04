<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerJobResource;
use App\Models\Candidate;
use App\Models\Job;
use App\Models\JobApplication;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\DuplicateCandidateService;

class PartnerJobController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $query = Job::query()
            ->where('status', 'approved')
            ->visibleToPartner($partner);

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

        if ((string) $job->status !== 'approved' || !$job->isVisibleToPartner($partner)) {
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

        if ((string) $job->status !== 'approved' || !$job->isVisibleToPartner($partner)) {
            return response()->json(['message' => 'Job not available.'], 422);
        }

        $validated = $request->validate([
            'candidate_ids' => ['required', 'array', 'min:1'],
            'candidate_ids.*' => ['required', 'integer', 'exists:candidates,id'],
            'interview_at' => $job->screening_required ? ['nullable', 'date'] : ['required', 'date', 'after:now'],
        ]);

        $submittedCount = 0;
        $blockedCount = 0;
        $duplicateService = app(DuplicateCandidateService::class);

        foreach ($validated['candidate_ids'] as $candidateId) {
            $candidate = Candidate::query()
                ->where('id', $candidateId)
                ->where('partner_id', $partner->id)
                ->first();

            if (!$candidate) {
                continue;
            }

            if (!$duplicateService->canSubmit($candidate)) {
                $duplicateService->rememberBlockedJob($candidate, $job);
                $blockedCount++;
                continue;
            }

            $identityConflict = $duplicateService->isReleased($candidate)
                ? null
                : $duplicateService->submissionConflict($candidate, $job);
            if ($identityConflict) {
                $duplicateService->quarantineForJobConflict($candidate, $identityConflict, $job);
                $duplicateService->rememberBlockedJob($candidate->refresh(), $job);
                $blockedCount++;
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
                'status' => $job->screening_required ? 'Pending Review' : 'Approved',
                'hiring_status' => $job->screening_required ? null : 'Interview Scheduled',
                'interview_at' => $job->screening_required ? null : $validated['interview_at'],
            ]);

            $submittedCount++;
        }

        if ($submittedCount === 0) {
            return response()->json([
                'message' => $blockedCount > 0
                    ? 'Submission blocked: candidate identity is duplicated or awaiting Superadmin review.'
                    : 'All selected candidates have already been submitted for this job.',
                'submitted_count' => 0,
                'blocked_count' => $blockedCount,
            ], 422);
        }

        return response()->json([
            'message' => $submittedCount . ' ' . Str::plural('application', $submittedCount) . ' submitted successfully.',
            'submitted_count' => $submittedCount,
            'blocked_count' => $blockedCount,
        ]);
    }
}
