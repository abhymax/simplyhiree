<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class CandidateApplicationController extends Controller
{
    public function index(Request $request)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        $query = JobApplication::query()
            ->where('candidate_user_id', $candidate->id)
            ->with(['job:id,title,company_name,location,job_type,salary,status']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('hiring_status')) {
            $query->where('hiring_status', $request->input('hiring_status'));
        }

        $perPage = max(min((int) $request->input('per_page', 20), 100), 1);

        $applications = $query
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $data = $applications->getCollection()->map(function (JobApplication $application) {
            return [
                'id' => $application->id,
                'status' => $application->status,
                'hiring_status' => $application->hiring_status,
                'interview_at' => optional($application->interview_at)->toIso8601String(),
                'joining_date' => optional($application->joining_date)->toDateString(),
                'joined_status' => $application->joined_status,
                'left_at' => optional($application->left_at)->toIso8601String(),
                'applied_at' => optional($application->created_at)->toIso8601String(),
                'job' => [
                    'id' => $application->job?->id,
                    'title' => $application->job?->title,
                    'company_name' => $application->job?->company_name,
                    'location' => $application->job?->location,
                    'job_type' => $application->job?->job_type,
                    'salary' => $application->job?->salary,
                    'status' => $application->job?->status,
                ],
            ];
        })->values();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }
}
