<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerCandidateResource;
use App\Models\Candidate;
use Illuminate\Http\Request;

class PartnerCandidateController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $query = Candidate::query()
            ->where('partner_id', $partner->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->input('location') . '%');
        }

        if ($request->filled('experience_status')) {
            $query->where('experience_status', $request->input('experience_status'));
        }

        $perPage = max(min((int) $request->input('per_page', 20), 100), 1);

        $candidates = $query
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return PartnerCandidateResource::collection($candidates)->additional([
            'meta' => [
                'current_page' => $candidates->currentPage(),
                'last_page' => $candidates->lastPage(),
                'per_page' => $candidates->perPage(),
                'total' => $candidates->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:candidates,email,NULL,id,partner_id,' . $partner->id],
            'phone_number' => ['required', 'string', 'max:20', 'unique:candidates,phone_number,NULL,id,partner_id,' . $partner->id],
            'alternate_phone_number' => ['nullable', 'string', 'max:20'],
            'location' => ['nullable', 'string', 'max:255'],
            'job_interest' => ['nullable', 'string', 'max:255'],
            'education_level' => ['nullable', 'string', 'max:255'],
            'experience_status' => ['nullable', 'string', 'in:Experienced,Fresher'],
            'expected_ctc' => ['nullable', 'numeric', 'min:0'],
            'notice_period' => ['nullable', 'string', 'max:100'],
            'job_role_preference' => ['nullable', 'string'],
            'languages_spoken' => ['nullable', 'string'],
            'skills' => ['nullable', 'string'],
        ]);

        $validated['partner_id'] = $partner->id;
        $candidate = Candidate::create($validated);

        return (new PartnerCandidateResource($candidate))
            ->additional(['message' => 'Candidate added successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Candidate $candidate)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        if ((int) $candidate->partner_id !== (int) $partner->id) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        return new PartnerCandidateResource($candidate);
    }

    public function update(Request $request, Candidate $candidate)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        if ((int) $candidate->partner_id !== (int) $partner->id) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:candidates,email,' . $candidate->id . ',id,partner_id,' . $partner->id],
            'phone_number' => ['required', 'string', 'max:20', 'unique:candidates,phone_number,' . $candidate->id . ',id,partner_id,' . $partner->id],
            'alternate_phone_number' => ['nullable', 'string', 'max:20'],
            'location' => ['nullable', 'string', 'max:255'],
            'job_interest' => ['nullable', 'string', 'max:255'],
            'education_level' => ['nullable', 'string', 'max:255'],
            'experience_status' => ['nullable', 'string', 'in:Experienced,Fresher'],
            'expected_ctc' => ['nullable', 'numeric', 'min:0'],
            'notice_period' => ['nullable', 'string', 'max:100'],
            'job_role_preference' => ['nullable', 'string'],
            'languages_spoken' => ['nullable', 'string'],
            'skills' => ['nullable', 'string'],
        ]);

        $candidate->update($validated);

        return (new PartnerCandidateResource($candidate))
            ->additional(['message' => 'Candidate updated successfully.']);
    }
}
