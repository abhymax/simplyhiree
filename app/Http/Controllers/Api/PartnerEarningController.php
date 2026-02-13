<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PartnerEarningController extends Controller
{
    public function index(Request $request)
    {
        $partner = $request->user();

        if (!$partner || !$partner->hasRole('partner')) {
            return response()->json(['message' => 'Only partner users can access this endpoint.'], 403);
        }

        $placements = JobApplication::query()
            ->where('joined_status', 'Joined')
            ->whereHas('candidate', function ($query) use ($partner) {
                $query->where('partner_id', $partner->id);
            })
            ->with(['job', 'candidate'])
            ->latest()
            ->get();

        $rows = [];
        $eligibleCount = 0;
        $pendingCount = 0;
        $eligibleAmount = 0.0;
        $pendingAmount = 0.0;

        foreach ($placements as $app) {
            if (empty($app->joining_date) || empty($app->job?->payout_amount) || empty($app->job?->minimum_stay_days)) {
                continue;
            }

            $joiningDate = Carbon::parse($app->joining_date);
            $payoutDate = $joiningDate->copy()->addDays((int) $app->job->minimum_stay_days);

            $isEligible = $payoutDate->isPast() && is_null($app->left_at);
            $status = $isEligible ? 'Eligible' : 'Pending';
            $amount = (float) $app->job->payout_amount;

            if ($isEligible) {
                $eligibleCount++;
                $eligibleAmount += $amount;
            } else {
                $pendingCount++;
                $pendingAmount += $amount;
            }

            $rows[] = [
                'application_id' => $app->id,
                'candidate_name' => trim(($app->candidate?->first_name ?? '') . ' ' . ($app->candidate?->last_name ?? '')),
                'job_title' => $app->job?->title,
                'joining_date' => $joiningDate->toDateString(),
                'minimum_stay_days' => (int) $app->job->minimum_stay_days,
                'payout_date' => $payoutDate->toDateString(),
                'payout_amount' => $amount,
                'payout_amount_formatted' => '₹' . number_format($amount, 0),
                'status' => $status,
            ];
        }

        usort($rows, function ($a, $b) {
            return strcmp($b['payout_date'], $a['payout_date']);
        });

        return response()->json([
            'data' => $rows,
            'summary' => [
                'total_items' => count($rows),
                'eligible_count' => $eligibleCount,
                'pending_count' => $pendingCount,
                'eligible_amount' => $eligibleAmount,
                'pending_amount' => $pendingAmount,
                'eligible_amount_formatted' => '₹' . number_format($eligibleAmount, 0),
                'pending_amount_formatted' => '₹' . number_format($pendingAmount, 0),
            ],
        ]);
    }
}
