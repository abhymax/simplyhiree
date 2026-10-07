<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerApplicationResource;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class PartnerApplicationController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $perPage = max(min((int) $request->input('per_page', 20), 100), 1);

        // Candidates belong to the partner-account owner, so scope to the
        // owner's tree; a team member only sees what they submitted.
        $ownerId = (int) $partner->partnerOwnerId();
        $poolIds = \App\Models\User::query()
            ->whereKey($ownerId)
            ->orWhere('parent_partner_id', $ownerId)
            ->pluck('id');

        $applications = JobApplication::query()
            ->whereHas('candidate', function ($query) use ($poolIds) {
                $query->whereIn('partner_id', $poolIds);
            })
            ->when($partner->isPartnerTeamMember(),
                fn ($q) => $q->where('submitted_by_user_id', (int) $partner->id))
            ->with(['job', 'candidate'])
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        return PartnerApplicationResource::collection($applications)->additional([
            'meta' => [
                'current_page' => $applications->currentPage(),
                'last_page' => $applications->lastPage(),
                'per_page' => $applications->perPage(),
                'total' => $applications->total(),
            ],
        ]);
    }
}
