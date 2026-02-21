<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
// Added missing models for Job Creation
use App\Models\JobCategory; 
use App\Models\EducationLevel;
// Notifications
use App\Notifications\CandidateRejectedByClient;
use App\Notifications\CandidateSelected;
use App\Notifications\InterviewScheduled;
use App\Notifications\CandidateJoined; 
use App\Notifications\CandidateDidNotJoin;
use App\Notifications\CandidateLeft;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use App\Services\SuperadminActivityService;

class ClientController extends Controller
{
    /**
     * Show the client dashboard.
     */
    public function index(SuperadminActivityService $activityService)
    {
        $activityService->checkBillingDueAlerts();

        $client = Auth::user();
        
        $jobs = Job::where('user_id', $client->id)
                    ->with(['educationLevel']) // Removed experienceLevel as we use min/max now
                    ->latest()
                    ->get();
        
        $totalJobs = $jobs->count();
        $activeJobs = $jobs->where('status', 'approved')->count();
        $totalApplicants = JobApplication::whereIn('job_id', $jobs->pluck('id'))->count();
        
        $totalHires = JobApplication::whereIn('job_id', $jobs->pluck('id'))
                                ->whereIn('hiring_status', ['Selected', 'Joined'])
                                ->count();

        // --- Daily Pulse Data ---
        $todayInterviews = JobApplication::whereIn('job_id', $jobs->pluck('id'))
            ->whereDate('interview_at', Carbon::today())
            ->count();

        $dueInvoicesCount = 0;
        $myHires = JobApplication::whereIn('job_id', $jobs->pluck('id'))
            ->where('hiring_status', 'Selected')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('joining_date')
            ->get();

        $billableDays = $client->billable_period_days ?? 30;

        foreach ($myHires as $hire) {
            $invoiceDate = $hire->joining_date->copy()->addDays($billableDays);
            if ($invoiceDate->isPast() || $invoiceDate->isToday()) {
                $dueInvoicesCount++;
            }
        }

        return view('client.dashboard', [
            'client' => $client,
            'jobs'   => $jobs,
            'totalJobs' => $totalJobs,
            'activeJobs' => $activeJobs,
            'totalApplicants' => $totalApplicants,
            'totalHires' => $totalHires,
            'todayInterviews' => $todayInterviews,
            'dueInvoicesCount' => $dueInvoicesCount
        ]);
    }

    // --- NEW: JOB CREATION METHODS ---

    /**
     * Show the form to create a new job.
     */
    public function createJob()
    {
        $categories = JobCategory::all();
        $educationLevels = EducationLevel::all();
        // Note: ExperienceLevels are no longer needed for the form
        
        return view('client.jobs.create', compact('categories', 'educationLevels'));
    }

    /**
     * Store the newly created job.
     */
    public function storeJob(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:job_categories,id',
            'location' => 'required|string',
            'salary' => 'nullable|string',
            'job_type' => 'required|string',
            'description' => 'required|string',
            
            // FIX: Validating manual experience range
            'min_experience' => 'required|integer|min:0',
            'max_experience' => 'required|integer|gte:min_experience|max:50',
            
            'education_level_id' => 'required|exists:education_levels,id',
            'application_deadline' => 'nullable|date',
            'skills_required' => 'nullable|string',
            'company_website' => 'nullable|url',
            'openings' => 'nullable|integer|min:1',
        ]);

        Job::create([
            'user_id' => Auth::id(), // Automatically assign to logged-in client
            'company_name' => Auth::user()->name, // Default to client name
            'status' => 'pending_approval', // Jobs posted by clients need approval
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'location' => $validated['location'],
            'salary' => $validated['salary'],
            'job_type' => $validated['job_type'],
            'description' => $validated['description'],
            
            // FIX: Saving manual experience
            'min_experience' => $validated['min_experience'],
            'max_experience' => $validated['max_experience'],
            'experience_level_id' => null, // No longer used
            
            'education_level_id' => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'],
            'skills_required' => $request->skills_required,
            'company_website' => $request->company_website,
            'openings' => $request->openings ?? 1,
            'partner_visibility' => 'all', // Default visibility
        ]);

        return redirect()->route('client.dashboard')->with('success', 'Job posted successfully! Waiting for admin approval.');
    }

    // ---------------------------------
    
    /**
     * Show interviews scheduled for today.
     */
    public function dailySchedule()
    {
        $client = Auth::user();
        
        $todayInterviews = JobApplication::whereHas('job', function($q) use ($client){
                $q->where('user_id', $client->id);
            })
            ->whereDate('interview_at', Carbon::today())
            ->with(['job', 'candidate', 'candidateUser'])
            ->orderBy('interview_at', 'asc')
            ->get();

        return view('client.daily_interviews', compact('todayInterviews'));
    }

    /**
     * Show the applicants for a specific job.
     */
    public function showApplicants(Job $job)
    {
        if ($job->user_id !== Auth::id()) {
            abort(403, 'UNAUTHORIZED ACTION.');
        }

        $approvedApplications = JobApplication::where('job_id', $job->id)
                                            ->where('status', 'Approved')
                                            ->with(['candidate', 'candidateUser'])
                                            ->latest()
                                            ->paginate(20);

        return view('client.jobs.applicants', [
            'job' => $job,
            'applications' => $approvedApplications
        ]);
    }
    
    public function rejectApplicant(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'Client Rejected']);
        $this->notifyAdminAndPartner(new CandidateRejectedByClient($application), $application);
        return redirect()->back()->with('success', 'Candidate has been rejected.');
    }

    // --- INTERVIEW SCHEDULING ---

    public function showInterviewForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.interview', ['application' => $application, 'isEdit' => false]);
    }

    public function scheduleInterview(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'interview_at' => 'required|date|after:now',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'hiring_status' => 'Interview Scheduled',
            'interview_at' => Carbon::parse($validated['interview_at']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        $this->notifyAdminAndPartner(new InterviewScheduled($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview scheduled successfully!');
    }
    
    public function editInterviewDetails(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.interview', ['application' => $application, 'isEdit' => true]);
    }

    public function updateInterviewDetails(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'interview_at' => 'required|date|after:now',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'interview_at' => Carbon::parse($validated['interview_at']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Interview details updated successfully!');
    }

    public function markAsAppeared(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'Interviewed']);
        return redirect()->back()->with('success', 'Candidate marked as \'Interviewed\'.');
    }

    public function markAsNoShow(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['hiring_status' => 'No-Show']);
        return redirect()->back()->with('success', 'Candidate marked as \'No-Show\'.');
    }
    
    // --- SELECTION ---

    public function showSelectForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.select', ['application' => $application, 'isEdit' => false]);
    }

    public function storeSelection(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'hiring_status' => 'Selected',
            'joining_date' => Carbon::parse($validated['joining_date']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        $this->notifyAdminAndPartner(new CandidateSelected($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Candidate Selected! Joining date has been set.');
    }

    public function editSelection(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.select', ['application' => $application, 'isEdit' => true]);
    }

    public function updateSelectionDetails(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'client_notes' => 'nullable|string|max:1000',
        ]);
        
        $application->update([
            'joining_date' => Carbon::parse($validated['joining_date']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Selection details updated successfully!');
    }

    public function markAsJoined(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['joined_status' => 'Joined']);
        $this->notifyAdminAndPartner(new CandidateJoined($application), $application);
        return redirect()->back()->with('success', 'Candidate marked as \'Joined\'.');
    }

    public function markAsNotJoined(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->update(['joined_status' => 'Did Not Join']);
        $this->notifyAdminAndPartner(new CandidateDidNotJoin($application), $application);
        return redirect()->back()->with('success', 'Candidate marked as \'Did Not Join\'.');
    }

    public function showLeftForm(JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }
        $application->load(['job', 'candidate', 'candidateUser']);
        return view('client.jobs.left', ['application' => $application]);
    }

    public function markAsLeft(Request $request, JobApplication $application)
    {
        if ($application->job->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'left_at' => 'required|date|after_or_equal:joining_date',
            'client_notes' => 'nullable|string|max:1000',
        ]);

        $application->update([
            'joined_status' => 'Left',
            'left_at' => Carbon::parse($validated['left_at']),
            'client_notes' => $validated['client_notes'],
        ]);
        
        $this->notifyAdminAndPartner(new CandidateLeft($application), $application);
        return redirect()->route('client.jobs.applicants', $application->job_id)->with('success', 'Candidate marked as \'Left\'.');
    }

    public function billing()
    {
        $client = Auth::user();

        $hires = JobApplication::where('hiring_status', 'Selected')
            ->whereHas('job', function($q) use ($client) {
                $q->where('user_id', $client->id);
            })
            ->with(['job', 'candidate', 'candidateUser'])
            ->get();

        $billingData = [];

        foreach ($hires as $hire) {
            if (empty($hire->joining_date)) {
                continue;
            }

            $joiningDate = Carbon::parse($hire->joining_date);
            $billableDays = $client->billable_period_days ?? 30; 
            $invoiceDate = $joiningDate->copy()->addDays($billableDays);
            
            $isDue = $invoiceDate->isPast();
            
            if ($hire->payment_status === 'paid') {
                $status = 'Paid';
                $color = 'text-green-600 bg-green-100';
            } elseif ($isDue) {
                $status = 'Due for Payment';
                $color = 'text-red-600 bg-red-100';
            } else {
                $status = 'Maturing';
                $color = 'text-yellow-600 bg-yellow-100';
            }

            $billingData[] = (object) [
                'candidate_name' => $hire->candidate_name,
                'job_title' => $hire->job->title,
                'joining_date' => $joiningDate->format('M d, Y'),
                'amount' => $hire->job->payout_amount ? '₹' . number_format($hire->job->payout_amount) : 'N/A',
                'invoice_date' => $invoiceDate->format('M d, Y'),
                'status' => $status,
                'status_color' => $color,
                'paid_at' => $hire->paid_at ? Carbon::parse($hire->paid_at)->format('M d, Y') : null,
            ];
        }

        $billingData = collect($billingData)->sortByDesc('invoice_date');
        return view('client.billing.index', compact('billingData'));
    }

    private function notifyAdminAndPartner($notification, JobApplication $application)
    {
        $application->load(['job.user', 'candidate.partner', 'candidateUser']);
        
        $admins = User::role('Superadmin')->get();
        Notification::send($admins, $notification);

        if ($application->candidate && $application->candidate->partner) {
            $partner = $application->candidate->partner;
            $partner->notify($notification);
        }
    }
}
