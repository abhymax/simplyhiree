<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerJobResource;
use App\Models\Job;
use Illuminate\Http\Request;

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
            ->whereDoesntHave('excludedPartners', function ($q) use ($partner) {
                $q->where('users.id', $partner->id);
            })
            ->where(function ($q) use ($partner) {
                $q->where('partner_visibility', 'all')
                    ->orWhere(function ($subQ) use ($partner) {
                        $subQ->where('partner_visibility', 'selected')
                            ->whereHas('allowedPartners', function ($p) use ($partner) {
                                $p->where('users.id', $partner->id);
                            });
                    });
            });

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

        $jobs = $query
            ->with(['category', 'experienceLevel', 'educationLevel'])
            ->withCount([
                'jobApplications as partner_applications_count' => function ($q) use ($partner) {
                    $q->whereHas('candidate', function ($subQ) use ($partner) {
                        $subQ->where('partner_id', $partner->id);
                    });
                },
            ])
            ->latest()
            ->paginate(10)
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
}
