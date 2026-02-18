<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientBillingController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $applications = JobApplication::query()
            ->where('hiring_status', 'Selected')
            ->whereHas('job', function ($q) use ($client) {
                $q->where('user_id', $client->id);
            })
            ->with(['job', 'candidate', 'candidateUser'])
            ->get();

        $rows = [];
        $statusCount = [
            'Paid' => 0,
            'Due for Payment' => 0,
            'Maturing' => 0,
        ];
        $statusAmount = [
            'Paid' => 0.0,
            'Due for Payment' => 0.0,
            'Maturing' => 0.0,
        ];

        $billableDays = $client->billable_period_days ?? 30;

        foreach ($applications as $application) {
            if (empty($application->joining_date)) {
                continue;
            }

            $joiningDate = Carbon::parse($application->joining_date);
            $invoiceDate = $joiningDate->copy()->addDays((int) $billableDays);
            $amount = (float) ($application->job?->payout_amount ?? 0);

            if ($application->payment_status === 'paid') {
                $status = 'Paid';
            } elseif ($invoiceDate->isPast() || $invoiceDate->isToday()) {
                $status = 'Due for Payment';
            } else {
                $status = 'Maturing';
            }

            $statusCount[$status]++;
            $statusAmount[$status] += $amount;

            $rows[] = [
                'application_id' => $application->id,
                'candidate_name' => $application->candidate_name,
                'job_title' => $application->job?->title,
                'joining_date' => $joiningDate->toDateString(),
                'invoice_date' => $invoiceDate->toDateString(),
                'amount' => $amount,
                'amount_formatted' => $amount > 0 ? '₹' . number_format($amount, 0) : 'N/A',
                'status' => $status,
                'paid_at' => optional($application->paid_at)?->toDateString(),
            ];
        }

        usort($rows, function ($a, $b) {
            return strcmp($b['invoice_date'], $a['invoice_date']);
        });

        $page = max((int) $request->input('page', 1), 1);
        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $total = count($rows);
        $lastPage = max((int) ceil($total / $perPage), 1);
        $offset = ($page - 1) * $perPage;
        $pagedRows = array_slice($rows, $offset, $perPage);

        return response()->json([
            'data' => $pagedRows,
            'summary' => [
                'total_items' => $total,
                'paid_count' => $statusCount['Paid'],
                'due_count' => $statusCount['Due for Payment'],
                'maturing_count' => $statusCount['Maturing'],
                'paid_amount' => $statusAmount['Paid'],
                'due_amount' => $statusAmount['Due for Payment'],
                'maturing_amount' => $statusAmount['Maturing'],
                'paid_amount_formatted' => '₹' . number_format($statusAmount['Paid'], 0),
                'due_amount_formatted' => '₹' . number_format($statusAmount['Due for Payment'], 0),
                'maturing_amount_formatted' => '₹' . number_format($statusAmount['Maturing'], 0),
            ],
            'meta' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'per_page' => $perPage,
                'total' => $total,
            ],
        ]);
    }
}

