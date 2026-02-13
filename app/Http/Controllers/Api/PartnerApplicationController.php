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

        $applications = JobApplication::query()
            ->whereHas('candidate', function ($query) use ($partner) {
                $query->where('partner_id', $partner->id);
            })
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
