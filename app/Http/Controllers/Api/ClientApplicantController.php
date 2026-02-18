<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class ClientApplicantController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $perPage = max(min((int) $request->input('per_page', 20), 100), 1);

        $query = JobApplication::query()
            ->whereHas('job', function ($q) use ($client) {
                $q->where('user_id', $client->id);
            })
            ->with(['job', 'candidate', 'candidateUser'])
            ->latest();

        if ($request->filled('job_id')) {
            $query->where('job_id', (int) $request->input('job_id'));
        }

        if ($request->filled('hiring_status')) {
            $query->where('hiring_status', $request->input('hiring_status'));
        }

        $applications = $query->paginate($perPage)->appends($request->query());

        $data = $applications->getCollection()->map(function (JobApplication $application) {
            $candidateName = $application->candidate_name;
            $candidateEmail = $application->candidate?->email ?? $application->candidateUser?->email;
            $candidatePhone = $application->candidate?->phone_number;

            return [
                'id' => $application->id,
                'status' => $application->status,
                'hiring_status' => $application->hiring_status,
                'joined_status' => $application->joined_status,
                'interview_at' => optional($application->interview_at)?->toIso8601String(),
                'joining_date' => optional($application->joining_date)?->toDateString(),
                'left_at' => optional($application->left_at)?->toDateString(),
                'client_notes' => $application->client_notes,
                'applied_at' => optional($application->created_at)?->toIso8601String(),
                'job' => [
                    'id' => $application->job?->id,
                    'title' => $application->job?->title,
                    'company_name' => $application->job?->company_name,
                    'location' => $application->job?->location,
                ],
                'candidate' => [
                    'name' => $candidateName,
                    'email' => $candidateEmail,
                    'phone' => $candidatePhone,
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

