<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CandidateDashboardController extends Controller
{
    public function index(Request $request)
    {
        $candidate = $request->user();

        if (!$candidate || !$candidate->hasRole('candidate')) {
            return response()->json(['message' => 'Only candidate users can access this endpoint.'], 403);
        }

        $applicationsBase = JobApplication::query()->where('candidate_user_id', $candidate->id);

        $todayInterviews = (int) (clone $applicationsBase)
            ->whereDate('interview_at', Carbon::today())
            ->count();

        $totalApplications = (int) (clone $applicationsBase)->count();

        $inProcess = (int) (clone $applicationsBase)
            ->where(function ($query) {
                $query->whereIn('status', ['Pending Review', 'Approved', 'Interview Scheduled', 'Selected'])
                    ->orWhereIn('hiring_status', ['Pending Action', 'Interview Scheduled', 'Interviewed', 'Selected']);
            })
            ->count();

        $upcomingInterviews = (clone $applicationsBase)
            ->whereNotNull('interview_at')
            ->whereDate('interview_at', '>=', Carbon::today())
            ->with(['job:id,title,company_name'])
            ->orderBy('interview_at')
            ->limit(3)
            ->get()
            ->map(function (JobApplication $application) {
                return [
                    'id' => $application->id,
                    'interview_at' => optional($application->interview_at)->toIso8601String(),
                    'job' => [
                        'id' => $application->job?->id,
                        'title' => $application->job?->title,
                        'company_name' => $application->job?->company_name,
                    ],
                ];
            })
            ->values();

        $profile = $candidate->profile;
        $isProfileComplete = $profile
            && !empty($candidate->name)
            && !empty($candidate->email)
            && !empty($profile->phone_number)
            && !empty($profile->location)
            && !empty($profile->date_of_birth)
            && !empty($profile->gender)
            && !empty($profile->experience_status)
            && !empty($profile->skills);

        return response()->json([
            'summary' => [
                'today_interviews' => $todayInterviews,
                'total_applications' => $totalApplications,
                'in_process' => $inProcess,
            ],
            'upcoming_interviews' => $upcomingInterviews,
            'profile_complete' => $isProfileComplete,
        ]);
    }
}
