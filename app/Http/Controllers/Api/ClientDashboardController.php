<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobApplication;
use App\Services\SuperadminActivityService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function index(Request $request, SuperadminActivityService $activityService)
    {
        $activityService->checkBillingDueAlerts();

        $client = $request->user();

        if (!$client || !$client->hasRole('client')) {
            return response()->json(['message' => 'Only client users can access this endpoint.'], 403);
        }

        $jobsQuery = Job::query()->where('user_id', $client->id);
        $jobIds = $jobsQuery->pluck('id');

        $totalJobs = (int) $jobIds->count();
        $activeJobs = (int) Job::query()
            ->where('user_id', $client->id)
            ->where('status', 'approved')
            ->count();

        $applicationsBase = JobApplication::query()->whereIn('job_id', $jobIds);
        $totalApplicants = (int) (clone $applicationsBase)->count();
        $todayInterviews = (int) (clone $applicationsBase)
            ->whereDate('interview_at', Carbon::today())
            ->count();

        $paymentsDue = 0;
        $billableDays = $client->billable_period_days ?? 30;
        $selectedApps = (clone $applicationsBase)
            ->where('hiring_status', 'Selected')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('joining_date')
            ->get();

        foreach ($selectedApps as $app) {
            if (!$app->joining_date) {
                continue;
            }
            $invoiceDate = Carbon::parse($app->joining_date)->addDays((int) $billableDays);
            if ($invoiceDate->isPast() || $invoiceDate->isToday()) {
                $paymentsDue++;
            }
        }

        $perPage = max(min((int) $request->input('per_page', 10), 100), 1);
        $jobs = Job::query()
            ->where('user_id', $client->id)
            ->withCount('jobApplications')
            ->latest()
            ->paginate($perPage)
            ->appends($request->query());

        $jobRows = $jobs->getCollection()->map(function (Job $job) {
            return [
                'id' => $job->id,
                'title' => $job->title,
                'location' => $job->location,
                'job_type' => $job->job_type,
                'status' => $job->status,
                'openings' => $job->openings,
                'min_experience' => $job->min_experience,
                'max_experience' => $job->max_experience,
                'applications_count' => (int) $job->job_applications_count,
                'created_at' => optional($job->created_at)->toDateString(),
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total_jobs' => $totalJobs,
                'active_jobs' => $activeJobs,
                'total_applicants' => $totalApplicants,
                'today_interviews' => $todayInterviews,
                'payments_due' => $paymentsDue,
            ],
            'data' => $jobRows,
            'meta' => [
                'current_page' => $jobs->currentPage(),
                'last_page' => $jobs->lastPage(),
                'per_page' => $jobs->perPage(),
                'total' => $jobs->total(),
            ],
        ]);
    }
}
