<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            ->with(['job', 'candidate', 'candidateUser.profile'])
            ->latest();

        if ($request->filled('job_id')) {
            $query->where('job_id', (int) $request->input('job_id'));
        }

        if ($request->filled('hiring_status')) {
            $query->where('hiring_status', $request->input('hiring_status'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $applications = $query->paginate($perPage)->appends($request->query());

        $data = $applications->getCollection()->map(function (JobApplication $application) {
            $candidateName = $application->candidate_name;
            $candidateEmail = $application->candidate?->email ?? $application->candidateUser?->email;
            $candidatePhone = $application->candidate?->phone_number ?? $application->candidateUser?->profile?->phone;
            $resumePath = $application->candidate?->resume_path ?? $application->candidateUser?->profile?->resume_path;
            $resumeUrl = $resumePath ? Storage::disk('public')->url($resumePath) : null;

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
                    'skills' => $application->candidate?->skills,
                    'experience_status' => $application->candidate?->experience_status,
                    'education_level' => $application->candidate?->education_level,
                    'expected_ctc' => $application->candidate?->expected_ctc,
                    'resume_path' => $resumePath,
                    'resume_url' => $resumeUrl ? url($resumeUrl) : null,
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

    public function reject(Request $request, JobApplication $application)
    {
        $client = $this->authorizedClient($request);
        if ($client === null) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }
        if (!$this->ownsApplication($client->id, $application)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $application->update(['hiring_status' => 'Client Rejected']);
        return response()->json(['message' => 'Candidate rejected successfully.']);
    }

    public function scheduleInterview(Request $request, JobApplication $application)
    {
        $client = $this->authorizedClient($request);
        if ($client === null) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }
        if (!$this->ownsApplication($client->id, $application)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'interview_at' => ['required', 'date', 'after:now'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'hiring_status' => 'Interview Scheduled',
            'interview_at' => Carbon::parse($validated['interview_at']),
            'client_notes' => $validated['client_notes'] ?? null,
        ]);

        return response()->json(['message' => 'Interview scheduled successfully.']);
    }

    public function markAppeared(Request $request, JobApplication $application)
    {
        $client = $this->authorizedClient($request);
        if ($client === null) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }
        if (!$this->ownsApplication($client->id, $application)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $application->update(['hiring_status' => 'Interviewed']);
        return response()->json(['message' => 'Candidate marked as interviewed.']);
    }

    public function markNoShow(Request $request, JobApplication $application)
    {
        $client = $this->authorizedClient($request);
        if ($client === null) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }
        if (!$this->ownsApplication($client->id, $application)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $application->update(['hiring_status' => 'No-Show']);
        return response()->json(['message' => 'Candidate marked as no-show.']);
    }

    public function selectCandidate(Request $request, JobApplication $application)
    {
        $client = $this->authorizedClient($request);
        if ($client === null) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }
        if (!$this->ownsApplication($client->id, $application)) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'joining_date' => ['required', 'date', 'after_or_equal:today'],
            'client_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $application->update([
            'hiring_status' => 'Selected',
            'joining_date' => Carbon::parse($validated['joining_date']),
            'client_notes' => $validated['client_notes'] ?? null,
        ]);

        return response()->json(['message' => 'Candidate selected successfully.']);
    }

    private function authorizedClient(Request $request)
    {
        $client = $request->user();
        if (!$client || !$client->hasRole('client')) {
            return null;
        }

        return $client;
    }

    private function ownsApplication(int $clientId, JobApplication $application): bool
    {
        $application->loadMissing('job');
        return (int) $application->job?->user_id === $clientId;
    }
}
