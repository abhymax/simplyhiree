<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerCandidateResource;
use App\Models\Candidate;
use Illuminate\Http\Request;
use App\Services\DuplicateCandidateService;

class PartnerCandidateController extends Controller
{
    /** The partner account owner plus its team members. */
    private function poolIds($partner): array
    {
        $ownerId = (int) $partner->partnerOwnerId();

        return \App\Models\User::query()
            ->whereKey($ownerId)
            ->orWhere('parent_partner_id', $ownerId)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function index(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $query = Candidate::query()
            ->whereIn('partner_id', $this->poolIds($partner))
            ->when($partner->isPartnerTeamMember(),
                fn ($q) => $q->where('added_by_user_id', (int) $partner->id));

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
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:candidates,email,NULL,id,partner_id,' . $partner->id, function ($attribute, $value, $fail) use ($partner) { if (\App\Models\Candidate::where('email', $value)->whereNotIn('partner_id', [$partner->id])->exists()) $fail('This candidate is already registered on SimplyHiree by another vendor, so they cannot be added again. Please contact the SimplyHiree team if this is a different person.'); }],
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

        $duplicateService = app(DuplicateCandidateService::class);
        $assessment = $duplicateService->assess($partner->id, $validated['email'] ?? null, $validated['phone_number']);
        $validated['partner_id'] = (int) $partner->partnerOwnerId();
        $validated['added_by_user_id'] = (int) $partner->id;
        $validated['duplicate_status'] = 'clear';
        $candidate = Candidate::create($validated);
        $duplicateService->quarantine($candidate, $assessment, 'candidate_create_api');

        return (new PartnerCandidateResource($candidate))
            ->additional(['message' => $assessment['is_duplicate']
                ? 'Candidate saved but quarantined from submission pending Superadmin duplicate review.'
                : 'Candidate added successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Candidate $candidate)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        if (!in_array((int) $candidate->partner_id, $this->poolIds($partner), true)
            || ($partner->isPartnerTeamMember()
                && (int) $candidate->added_by_user_id !== (int) $partner->id)) {
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

        if (!in_array((int) $candidate->partner_id, $this->poolIds($partner), true)
            || ($partner->isPartnerTeamMember()
                && (int) $candidate->added_by_user_id !== (int) $partner->id)) {
            return response()->json(['message' => 'Candidate not found.'], 404);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:candidates,email,' . $candidate->id . ',id,partner_id,' . $partner->id, function ($attribute, $value, $fail) use ($partner, $candidate) { if (\App\Models\Candidate::where('email', $value)->where('id', '!=', $candidate->id)->whereNotIn('partner_id', [$partner->id])->exists()) $fail('This candidate is already registered on SimplyHiree by another vendor, so they cannot be added again. Please contact the SimplyHiree team if this is a different person.'); }],
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

        $duplicateService = app(DuplicateCandidateService::class);
        $assessment = $duplicateService->assess($partner->id, $validated['email'] ?? null, $validated['phone_number'], $candidate->resume_fingerprint, $candidate->id);
        $candidate->update($validated);
        $duplicateService->quarantine($candidate, $assessment, 'candidate_update_api');

        return (new PartnerCandidateResource($candidate))
            ->additional(['message' => 'Candidate updated successfully.']);
    }
}
