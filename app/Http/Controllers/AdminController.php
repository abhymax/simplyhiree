<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;
use App\Models\JobCategory;
use App\Models\ExperienceLevel;
use App\Models\EducationLevel;
use App\Models\Candidate;
use App\Models\PartnerProfile;
use App\Models\ClientProfile;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules;
use App\Notifications\JobApproved;
use App\Notifications\JobRejected;
use App\Notifications\ApplicationApprovedByAdmin;
use App\Notifications\ApplicationRejectedByAdmin;
use App\Notifications\ClientJobApprovedForAdmin;
use App\Services\SuperadminActivityService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    private function candidateUsersQuery()
    {
        $query = User::query()->where(function ($query) {
            $query->whereHas('roles', function ($roleQuery) {
                $roleQuery->whereRaw('LOWER(name) = ?', ['candidate']);
            });

            // Backward compatibility for legacy role column based users.
            if (Schema::hasColumn('users', 'role')) {
                $query->orWhereRaw('LOWER(role) = ?', ['candidate']);
            }
        });

        // Exclude non-candidate role records even if data is mixed in legacy DB.
        $query->whereDoesntHave('roles', function ($roleQuery) {
            $roleQuery->whereIn('name', ['partner', 'client', 'Superadmin', 'Manager', 'superadmin', 'manager']);
        });

        if (Schema::hasColumn('users', 'role')) {
            $query->where(function ($subQuery) {
                $subQuery->whereNull('role')
                    ->orWhereRaw('LOWER(role) = ?', ['candidate']);
            });
        }

        return $query;
    }

    private function isStrictCandidateUser(User $user): bool
    {
        $roleNames = $user->getRoleNames()->map(fn ($role) => strtolower((string) $role));
        $hasCandidateRole = $roleNames->contains('candidate');
        $hasBlockedRole = $roleNames->intersect(['partner', 'client', 'superadmin', 'manager'])->isNotEmpty();

        $isLegacyCandidate = Schema::hasColumn('users', 'role')
            && strtolower((string) $user->getAttribute('role')) === 'candidate';

        return ($hasCandidateRole || $isLegacyCandidate) && !$hasBlockedRole;
    }

    private function applyCandidateListFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($pq) use ($search) {
                        $pq->where('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return $query;
    }

    /**
     * Show the admin dashboard with stats.
     */
    public function index(SuperadminActivityService $activityService)
    {
        $activityService->checkBillingDueAlerts();

        $totalUsers = User::count();
        $totalClients = User::role('client')->count();
        $activeClients = User::role('client')->where('status', 'active')->count();
        $inactiveClients = $totalClients - $activeClients;
        $totalPartners = User::role('partner')->count();
        $activePartners = User::role('partner')->where('status', 'active')->count();
        // "restricted" is the platform's enforced blacklist state.
        $blacklistedPartners = User::role('partner')->where('status', 'restricted')->count();
        // Candidate counts:
        //  - direct  = users with role 'candidate' (signed up themselves)
        //  - vendor  = rows in candidates table (uploaded by partner agencies)
        //  - total   = sum of both
        $directCandidates  = $this->candidateUsersQuery()->count();
        $vendorCandidates  = \App\Models\Candidate::count();
        $totalCandidates   = $directCandidates + $vendorCandidates;
        $pendingJobs = Job::where('status', 'pending_approval')->count();
        $openJobs = Job::where('status', 'approved')->count();
        $closedJobs = Job::where('status', 'closed')->count();
        $totalSubmissions = JobApplication::count();
        $pendingApplications = JobApplication::where('status', 'Pending Review')->count();

        // --- Daily Pulse Data ---
        $todayInterviews = JobApplication::whereDate('interview_at', Carbon::today())->count();
        $scheduledInterviews = JobApplication::where('hiring_status', 'Interview Scheduled')
            ->where('interview_at', '>=', Carbon::now())
            ->count();
        $joiningsThisMonth = JobApplication::where('joined_status', 'Joined')
            ->whereYear('joining_date', Carbon::now()->year)
            ->whereMonth('joining_date', Carbon::now()->month)
            ->count();

        $billingApplications = JobApplication::whereIn('joined_status', ['Joined', 'Left'])
            ->whereNotNull('joining_date')
            ->with(['job.user'])
            ->get();
        $billingSnapshots = $billingApplications->map(fn ($application) => $application->billingSnapshot());
        $outstandingStatuses = ['Raised', 'Overdue', 'Due to Raise'];
        $outstandingPayments = (float) $billingSnapshots
            ->whereIn('status', $outstandingStatuses)
            ->sum('invoice_amount');
        $unpaidClients = $billingSnapshots
            ->whereIn('status', $outstandingStatuses)
            ->map(fn ($row) => $row['application']->job?->user_id)
            ->filter()
            ->unique()
            ->count();

        $paidBilling = $billingSnapshots->where('status', 'Paid');
        $revenueThisMonth = (float) $paidBilling
            ->filter(fn ($row) => $row['paid_at']?->betweenIncluded(Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()))
            ->sum('invoice_amount');
        $revenueThisQuarter = (float) $paidBilling
            ->filter(fn ($row) => $row['paid_at']?->betweenIncluded(Carbon::now()->startOfQuarter(), Carbon::now()->endOfQuarter()))
            ->sum('invoice_amount');
        $revenueThisYear = (float) $paidBilling
            ->filter(fn ($row) => $row['paid_at']?->betweenIncluded(Carbon::now()->startOfYear(), Carbon::now()->endOfYear()))
            ->sum('invoice_amount');

        $replacementUnderGuarantee = JobApplication::where('joined_status', 'Joined')
            ->whereNotNull('joining_date')
            ->with('job')
            ->get()
            ->filter(function ($application) {
                $days = (int) ($application->replacement_window_days
                    ?? $application->job?->replacement_guarantee_days
                    ?? 0);
                if ($days <= 0 || $application->replacement_status === 'closed') {
                    return false;
                }
                $deadline = $application->replacement_deadline
                    ?? $application->joining_date->copy()->addDays($days);
                return $deadline->isFuture() || $deadline->isToday();
            })
            ->count();

        $dueInvoicesCount = 0;
        $unpaidHires = JobApplication::where('hiring_status', 'Selected')
            ->where('payment_status', '!=', 'paid')
            ->whereNotNull('joining_date')
            ->with('job.user')
            ->get();

        foreach ($unpaidHires as $hire) {
            $due = $hire->invoiceDueAt();
            if ($due && ($due->isPast() || $due->isToday())) {
                $dueInvoicesCount++;
            }
        }

        $pendingPlanRequests = \App\Models\PlanChangeRequest::with('partner')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();
        $pendingPlanRequestsCount = \App\Models\PlanChangeRequest::where('status', 'pending')->count();
        $pendingVendorAssignmentRequests = \App\Models\ClientVendorAssignmentRequest::with('client')
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();
        $pendingVendorAssignmentCount = \App\Models\ClientVendorAssignmentRequest::where('status', 'pending')->count();

        return view('admin.dashboard', [
            'totalUsers'              => $totalUsers,
            'totalClients'            => $totalClients,
            'activeClients'           => $activeClients,
            'inactiveClients'         => $inactiveClients,
            'unpaidClients'           => $unpaidClients,
            'totalPartners'           => $totalPartners,
            'activePartners'          => $activePartners,
            'blacklistedPartners'     => $blacklistedPartners,
            'totalCandidates'         => $totalCandidates,
            'directCandidates'        => $directCandidates,
            'vendorCandidates'        => $vendorCandidates,
            'pendingJobs'             => $pendingJobs,
            'openJobs'                => $openJobs,
            'closedJobs'              => $closedJobs,
            'totalSubmissions'        => $totalSubmissions,
            'pendingApplications'     => $pendingApplications,
            'todayInterviews'         => $todayInterviews,
            'scheduledInterviews'     => $scheduledInterviews,
            'joiningsThisMonth'       => $joiningsThisMonth,
            'revenueThisMonth'        => $revenueThisMonth,
            'revenueThisQuarter'      => $revenueThisQuarter,
            'revenueThisYear'         => $revenueThisYear,
            'outstandingPayments'     => $outstandingPayments,
            'replacementUnderGuarantee' => $replacementUnderGuarantee,
            'dueInvoicesCount'        => $dueInvoicesCount,
            'pendingPlanRequests'     => $pendingPlanRequests,
            'pendingPlanRequestsCount'=> $pendingPlanRequestsCount,
            'pendingVendorAssignmentRequests' => $pendingVendorAssignmentRequests,
            'pendingVendorAssignmentCount'    => $pendingVendorAssignmentCount,
        ]);
    }

    public function dailySchedule()
    {
        $todayInterviews = JobApplication::whereDate('interview_at', Carbon::today())
            ->with(['job', 'candidate', 'candidateUser.profile', 'job.user'])
            ->orderBy('interview_at', 'asc')
            ->get();

        return view('admin.daily_interviews', compact('todayInterviews'));
    }

    // --- CANDIDATE (USER) MANAGEMENT ---

    /**
     * Unified Candidate Database for Superadmin.
     *
     * Source = candidates table (vendor-uploaded). Direct candidates (users
     * with role 'candidate') are reachable via the Direct tab which links to
     * the existing admin.users.index page. This split keeps query/filters
     * tractable while still showing both counts at the top.
     *
     * Filter spec aligns with the Phase 1 columns we have today; Phase 2
     * filters (tags, work mode, certifications, CXO fields, etc.) are
     * rendered in the UI but disabled with a "coming soon" tooltip.
     */
    public function listAllCandidates(Request $request)
    {
        $query = \App\Models\Candidate::query()->with(['partner', 'jobApplications.interviewRounds']);

        // --- Basic ---
        if ($s = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%$s%")
                  ->orWhere('last_name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('phone_number', 'like', "%$s%")
                  ->orWhere('alternate_phone_number', 'like', "%$s%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$s%"]);
            });
        }
        if ($from = $request->input('date_from')) $query->whereDate('created_at', '>=', $from);
        if ($to   = $request->input('date_to'))   $query->whereDate('created_at', '<=', $to);

        // --- Smart: Source / Recruiter / Client ---
        if ($partnerId = $request->input('partner_id')) {
            $query->where('partner_id', $partnerId);
        }
        if ($clientId = $request->input('client_id')) {
            $query->whereHas('jobApplications.job', function ($jq) use ($clientId) {
                $jq->where('user_id', $clientId);
            });
        }
        if ($request->boolean('duplicates_only')) {
            $dups = \App\Models\Candidate::select('email')
                ->whereNotNull('email')->where('email', '!=', '')
                ->groupBy('email')->havingRaw('COUNT(*) > 1')
                ->pluck('email');
            $query->whereIn('email', $dups);
        }

        // --- Recruitment ---
        if ($company     = $request->input('current_company'))     $query->where('current_company', 'like', "%$company%");
        if ($designation = $request->input('current_designation')) $query->where('current_designation', 'like', "%$designation%");
        if ($jobRole     = $request->input('job_role')) {
            $query->where(function ($q) use ($jobRole) {
                $q->where('job_role_preference', 'like', "%$jobRole%")
                  ->orWhere('current_designation', 'like', "%$jobRole%");
            });
        }
        if ($notice      = $request->input('notice_period'))       $query->where('notice_period', $notice);
        if ($request->boolean('immediate_joiner')) {
            $query->whereIn('notice_period', ['0', 'Immediate', '0 days', 'Immediately', 'Serving notice (immediate)']);
        }
        if (is_numeric($v = $request->input('exp_min')))           $query->where('total_experience_years', '>=', (int) $v);
        if (is_numeric($v = $request->input('exp_max')))           $query->where('total_experience_years', '<=', (int) $v);
        if (is_numeric($v = $request->input('current_ctc_min'))) {
            $val = (float) $v;
            if ($val <= 100) $val *= 100000;
            $query->where('current_ctc', '>=', $val);
        }
        if (is_numeric($v = $request->input('current_ctc_max'))) {
            $val = (float) $v;
            if ($val <= 100) $val *= 100000;
            $query->where('current_ctc', '<=', $val);
        }
        if (is_numeric($v = $request->input('expected_ctc_min'))) {
            $val = (float) $v;
            if ($val <= 100) $val *= 100000;
            $query->where('expected_ctc', '>=', $val);
        }
        if (is_numeric($v = $request->input('expected_ctc_max'))) {
            $val = (float) $v;
            if ($val <= 100) $val *= 100000;
            $query->where('expected_ctc', '<=', $val);
        }

        // --- Skills ---
        if ($skill = $request->input('skill')) $query->where('skills', 'like', "%$skill%");

        // --- Location ---
        if ($loc     = $request->input('current_location'))  $query->where('location', 'like', "%$loc%");
        if ($prefLoc = $request->input('preferred_location')) $query->where('preferred_locations', 'like', "%$prefLoc%");

        // --- Resume ---
        if ($request->input('resume_uploaded') === 'yes') $query->whereNotNull('resume_path')->where('resume_path', '!=', '');
        if ($request->input('resume_uploaded') === 'no')  $query->where(fn($q) => $q->whereNull('resume_path')->orWhere('resume_path', ''));

        // --- Hiring Workflow ---
        if ($hs = $request->input('hiring_workflow')) {
            $map = [
                'applied'   => fn($q) => $q->whereHas('jobApplications'),
                'screening' => fn($q) => $q->whereHas('jobApplications', fn($a) => $a->where('status', 'Pending Review')),
                'approved'  => fn($q) => $q->whereHas('jobApplications', fn($a) => $a->where('status', 'Approved')),
                'interview' => fn($q) => $q->whereHas('jobApplications', fn($a) => $a->where('hiring_status', 'Interview Scheduled')),
                'selected'  => fn($q) => $q->whereHas('jobApplications', fn($a) => $a->where('hiring_status', 'Selected')),
                'joined'    => fn($q) => $q->whereHas('jobApplications', fn($a) => $a->where('joined_status', 'Joined')),
                'rejected'  => fn($q) => $q->whereHas('jobApplications', function ($a) {
                    $a->where('status', 'Rejected')->orWhere('hiring_status', 'Client Rejected');
                }),
            ];
            if (isset($map[$hs])) ($map[$hs])($query);
        }

        // Which source tab is active. "all" (vendor + direct) is the default.
        $source = in_array($request->input('source'), ['all', 'vendor', 'direct'], true)
            ? $request->input('source')
            : 'all';

        // Build a normalized, merged row set across both candidate sources.
        $rows = collect();
        if ($source === 'all' || $source === 'vendor') {
            $rows = $rows->concat($this->mapVendorCandidateRows($query->latest()->get()));
        }
        if ($source === 'all' || $source === 'direct') {
            $rows = $rows->concat($this->directCandidateRows($request));
        }
        $rows = $rows->sortByDesc(fn ($r) => optional($r->created_at)->timestamp ?? 0)->values();

        // Paginate the merged collection.
        $perPage = 20;
        $page = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $candidates = new \Illuminate\Pagination\LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Source-tab counts
        $vendorCount = \App\Models\Candidate::count();
        $directCount = $this->candidateUsersQuery()->count();
        $totalCount  = $vendorCount + $directCount;

        // Dropdown options
        $partners = \App\Models\User::role('partner')->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $clients = \App\Models\User::role('client')->where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $noticePeriods = \App\Models\Candidate::query()
            ->whereNotNull('notice_period')->where('notice_period', '!=', '')
            ->distinct()->pluck('notice_period')->sort()->values();

        return view('admin.candidates.index', compact(
            'candidates', 'vendorCount', 'directCount', 'totalCount', 'partners', 'noticePeriods', 'clients', 'source'
        ));
    }

    /**
     * Normalize vendor Candidate models into the shared row shape used by the
     * unified candidate table and CSV export.
     */
    private function mapVendorCandidateRows($candidates)
    {
        return $candidates->map(function ($c) {
            return (object) [
                'source_type'             => 'vendor',
                'id'                      => $c->id,
                'first_name'              => $c->first_name,
                'last_name'               => $c->last_name,
                'candidate_code'          => $c->candidate_code ?? ('SH-CAN-' . str_pad((string) $c->id, 6, '0', STR_PAD_LEFT)),
                'email'                   => $c->email,
                'phone_number'            => $c->phone_number,
                'alternate_phone_number'  => $c->alternate_phone_number,
                'current_company'         => $c->current_company,
                'current_designation'     => $c->current_designation,
                'total_experience_years'  => $c->total_experience_years,
                'total_experience_months' => $c->total_experience_months,
                'current_ctc'             => $c->current_ctc,
                'expected_ctc'            => $c->expected_ctc,
                'notice_period'           => $c->notice_period,
                'skills'                  => $c->skills,
                'location'                => $c->location,
                'preferred_locations'     => $c->preferred_locations,
                'resume_path'             => $c->resume_path,
                'partner_name'            => optional($c->partner)->name,
                'created_at'              => $c->created_at,
                'detail_url'              => route('admin.candidates.show', $c->id),
                'interview_rounds'        => optional($c->jobApplications->sortByDesc('id')->first(fn ($a) => $a->interviewRounds->isNotEmpty()))->interviewRounds ?? collect(),
            ];
        });
    }

    /**
     * Build normalized rows for direct-registration candidates (User + user_profile).
     * Vendor-workflow filters (partner / client / hiring stage) don't apply to direct
     * registrations, so when any of those is active, direct users are excluded.
     */
    private function directCandidateRows(Request $request)
    {
        if ($request->filled('partner_id') || $request->filled('client_id') || $request->filled('hiring_workflow')) {
            return collect();
        }

        $query = $this->candidateUsersQuery()->with(['profile', 'roles']);

        if ($s = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhereHas('profile', fn ($p) => $p->where('phone_number', 'like', "%$s%"));
            });
        }
        if ($from = $request->input('date_from')) $query->whereDate('created_at', '>=', $from);
        if ($to   = $request->input('date_to'))   $query->whereDate('created_at', '<=', $to);

        $profileLike = function ($col, $val) use ($query) {
            $query->whereHas('profile', fn ($p) => $p->where($col, 'like', "%$val%"));
        };
        if ($skill = $request->input('skill'))                  $profileLike('skills', $skill);
        if ($company = $request->input('current_company'))      $profileLike('current_company', $company);
        if ($designation = $request->input('current_designation')) $profileLike('current_role', $designation);
        if ($jobRole = $request->input('job_role'))             $profileLike('current_role', $jobRole);
        if ($notice = $request->input('notice_period'))         $query->whereHas('profile', fn ($p) => $p->where('notice_period', $notice));
        if ($request->boolean('immediate_joiner')) {
            $query->whereHas('profile', fn ($p) => $p->whereIn('notice_period', ['0', 'Immediate', '0 days', 'Immediately', 'Serving notice (immediate)']));
        }
        if (is_numeric($v = $request->input('exp_min'))) $query->whereHas('profile', fn ($p) => $p->where('total_experience_years', '>=', (int) $v));
        if (is_numeric($v = $request->input('exp_max'))) $query->whereHas('profile', fn ($p) => $p->where('total_experience_years', '<=', (int) $v));
        foreach (['current_ctc' => ['current_ctc_min', 'current_ctc_max'], 'expected_ctc' => ['expected_ctc_min', 'expected_ctc_max']] as $col => $keys) {
            if (is_numeric($v = $request->input($keys[0]))) { $val = (float) $v; if ($val <= 100) $val *= 100000; $query->whereHas('profile', fn ($p) => $p->where($col, '>=', $val)); }
            if (is_numeric($v = $request->input($keys[1]))) { $val = (float) $v; if ($val <= 100) $val *= 100000; $query->whereHas('profile', fn ($p) => $p->where($col, '<=', $val)); }
        }
        if ($loc = $request->input('current_location'))      $profileLike('location', $loc);
        if ($prefLoc = $request->input('preferred_location')) $profileLike('preferred_locations', $prefLoc);
        if ($request->input('resume_uploaded') === 'yes') $query->whereHas('profile', fn ($p) => $p->whereNotNull('resume_path')->where('resume_path', '!=', ''));
        if ($request->input('resume_uploaded') === 'no') {
            $query->where(fn ($q) => $q->whereDoesntHave('profile')
                ->orWhereHas('profile', fn ($p) => $p->whereNull('resume_path')->orWhere('resume_path', '')));
        }
        if ($request->boolean('duplicates_only')) {
            $dups = \App\Models\User::query()->whereNotNull('email')->where('email', '!=', '')
                ->groupBy('email')->havingRaw('COUNT(*) > 1')->pluck('email');
            $query->whereIn('email', $dups);
        }

        return $query->latest()->get()->map(function ($u) {
            $p = $u->profile;
            return (object) [
                'source_type'             => 'direct',
                'id'                      => $u->id,
                'first_name'              => $u->name,
                'last_name'               => '',
                'candidate_code'          => $u->entity_code ?? ('SH-USR-' . str_pad((string) $u->id, 6, '0', STR_PAD_LEFT)),
                'email'                   => $u->email,
                'phone_number'            => $p->phone_number ?? null,
                'alternate_phone_number'  => null,
                'current_company'         => $p->current_company ?? null,
                'current_designation'     => $p->current_role ?? null,
                'total_experience_years'  => $p->total_experience_years ?? null,
                'total_experience_months' => $p->total_experience_months ?? null,
                'current_ctc'             => $p->current_ctc ?? null,
                'expected_ctc'            => $p->expected_ctc ?? null,
                'notice_period'           => $p->notice_period ?? null,
                'skills'                  => $p->skills ?? null,
                'location'                => $p->location ?? null,
                'preferred_locations'     => $p->preferred_locations ?? null,
                'resume_path'             => $p->resume_path ?? null,
                'partner_name'            => null,
                'created_at'              => $u->created_at,
                'detail_url'              => route('admin.users.show', $u->id),
                'interview_rounds'        => collect(),
            ];
        });
    }

    /**
     * CSV export honoring the current filter state.
     */
    public function exportAllCandidates(Request $request)
    {
        // Honor the active source tab (all | vendor | direct) and export the
        // same normalized rows shown in the unified candidate table.
        $source = in_array($request->input('source'), ['all', 'vendor', 'direct'], true)
            ? $request->input('source')
            : 'all';

        $rows = collect();
        if ($source === 'all' || $source === 'vendor') {
            $query = \App\Models\Candidate::query()->with(['partner']);
            if ($s = trim((string) $request->input('search'))) {
                $query->where(function ($q) use ($s) {
                    $q->where('first_name', 'like', "%$s%")
                      ->orWhere('last_name', 'like', "%$s%")
                      ->orWhere('email', 'like', "%$s%")
                      ->orWhere('phone_number', 'like', "%$s%");
                });
            }
            if ($partnerId = $request->input('partner_id')) $query->where('partner_id', $partnerId);
            if ($company   = $request->input('current_company')) $query->where('current_company', 'like', "%$company%");
            if ($skill     = $request->input('skill')) $query->where('skills', 'like', "%$skill%");
            if ($loc       = $request->input('current_location')) $query->where('location', 'like', "%$loc%");
            if (is_numeric($v = $request->input('exp_min'))) $query->where('total_experience_years', '>=', (int) $v);
            if (is_numeric($v = $request->input('exp_max'))) $query->where('total_experience_years', '<=', (int) $v);
            $rows = $rows->concat($this->mapVendorCandidateRows($query->latest()->get()));
        }
        if ($source === 'all' || $source === 'direct') {
            $rows = $rows->concat($this->directCandidateRows($request));
        }
        $rows = $rows->sortByDesc(fn ($r) => optional($r->created_at)->timestamp ?? 0)->values();

        $fileName = 'candidates_' . $source . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fputcsv($h, [
                'Source', 'Candidate Code', 'First Name', 'Last Name', 'Email', 'Mobile', 'Alt Mobile',
                'Current Company', 'Designation', 'Total Experience (yrs)', 'Notice Period',
                'Current CTC', 'Expected CTC', 'Skills', 'Current Location', 'Preferred Locations',
                'Resume Uploaded', 'Source Partner', 'Created At',
            ]);
            foreach ($rows as $c) {
                $pref = is_array($c->preferred_locations) ? implode(', ', $c->preferred_locations) : (string) $c->preferred_locations;
                fputcsv($h, [
                    $c->source_type === 'vendor' ? 'Vendor-uploaded' : 'Direct registration',
                    $c->candidate_code,
                    $c->first_name, $c->last_name, $c->email, $c->phone_number, $c->alternate_phone_number,
                    $c->current_company, $c->current_designation, $c->total_experience_years, $c->notice_period,
                    $c->current_ctc, $c->expected_ctc, $c->skills, $c->location, $pref,
                    $c->resume_path ? 'Yes' : 'No', $c->partner_name,
                    optional($c->created_at)->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($h);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Candidate detail page (Superadmin view).
     */
    public function showCandidateDetail(\App\Models\Candidate $candidate)
    {
        $candidate->load(['partner', 'jobApplications.job', 'jobApplications.interviewRounds']);
        return view('admin.candidates.show', compact('candidate'));
    }

    public function listUsers(Request $request)
    {
        // Load candidate users with their real profile relation (user_profiles table)
        $query = $this->candidateUsersQuery()->with(['profile']);

        $this->applyCandidateListFilters($query, $request);

        $users = $query->latest()->paginate(10)->withQueryString();

        // Backward compatibility alias so existing blades using $user->candidate do not break.
        $users->getCollection()->transform(function ($user) {
            $profile = $user->profile;
            if ($profile) {
                $profile->setAttribute('mobile', $profile->phone_number);
                $profile->setAttribute('dob', $profile->date_of_birth);
            }
            $user->setRelation('candidate', $profile);
            return $user;
        });
        
        // 4. Correct Stats for the View
        $baseCountQuery = $this->candidateUsersQuery();
        $counts = [
            'total' => (clone $baseCountQuery)->count(),
            'active' => (clone $baseCountQuery)->where('status', 'active')->count(),
            'restricted' => (clone $baseCountQuery)->where('status', 'restricted')->count(),
        ];

        return view('admin.users.index', ['users' => $users, 'counts' => $counts]);
    }

    public function exportUsers(Request $request)
    {
        $query = $this->candidateUsersQuery()->with(['profile']);
        $this->applyCandidateListFilters($query, $request);
        $users = $query->latest()->get();

        $fileName = 'candidates_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Candidate ID',
                'Name',
                'Email',
                'Phone',
                'Status',
                'Resume Uploaded',
                'Resume URL',
                'Joined On',
            ]);

            foreach ($users as $user) {
                $resumePath = $user->profile?->resume_path;
                $resumeUrl = $resumePath ? asset('storage/' . $resumePath) : '';

                fputcsv($handle, [
                    $user->id,
                    (string) $user->name,
                    (string) $user->email,
                    (string) ($user->profile?->phone_number ?? ''),
                    (string) ($user->status ?? ''),
                    $resumePath ? 'Yes' : 'No',
                    $resumeUrl,
                    optional($user->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function showUser(User $user)
    {
        if (!$this->isStrictCandidateUser($user)) {
            abort(404);
        }
        
        $user->load(['profile']);

        // Backward compatibility attributes for profile source.
        if ($user->profile) {
            $user->profile->setAttribute('mobile', $user->profile->phone_number);
            $user->profile->setAttribute('dob', $user->profile->date_of_birth);
        }

        return view('admin.users.show', compact('user'));
    }

    public function updateUserStatus(Request $request, User $user)
    {
        if ($user->hasRole('Superadmin')) {
            return redirect()->back()->with('error', 'Cannot change Superadmin status.');
        }
        $validated = $request->validate(['status' => 'required|in:active,pending,on_hold,restricted']);
        $user->update(['status' => $validated['status']]);
        return redirect()->back()->with('success', "User status updated to {$validated['status']}.");
    }

    /**
     * Bulk-update partner statuses. Action maps:
     *   approve → active, hold → on_hold, reject → restricted.
     */
    public function bulkUpdatePartnerStatus(Request $request)
    {
        $data = $request->validate([
            'action' => 'required|in:approve,hold,reject',
            'ids'    => 'required|array|min:1|max:200',
            'ids.*'  => 'integer|exists:users,id',
        ]);

        $statusMap = [
            'approve' => 'active',
            'hold'    => 'on_hold',
            'reject'  => 'restricted',
        ];
        $newStatus = $statusMap[$data['action']];

        // Restrict to partner-role users; never touch Superadmins.
        $partnerIds = User::role('partner')
            ->whereIn('id', $data['ids'])
            ->pluck('id');

        if ($partnerIds->isEmpty()) {
            return back()->with('error', 'No partner accounts matched the selection.');
        }

        $count = User::whereIn('id', $partnerIds)->update(['status' => $newStatus]);

        $verb = match ($data['action']) {
            'approve' => 'approved',
            'hold'    => 'put on hold',
            'reject'  => 'rejected (restricted)',
        };

        return back()->with('success', "{$count} partner(s) {$verb}.");
    }

    public function updateUserCredentials(Request $request, User $user)
    {
        if ($user->hasRole('Superadmin') && auth()->id() !== $user->id) {
             return redirect()->back()->with('error', 'Cannot change Superadmin credentials.');
        }
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ]);
        if ($validator->fails()) {
            // Surface the reason AND re-open this user's modal so it is not a silent no-op.
            return redirect()->back()
                ->withErrors($validator)
                ->with('pwd_modal_user', $user->id)
                ->with('error', 'Password not updated for '.$user->name.': '.$validator->errors()->first('password'));
        }
        // Plain value: the User model's 'hashed' cast hashes it once on save.
        $user->update(['password' => $request->input('password')]);
        return redirect()->back()->with('success', 'Password updated for '.$user->name.'. They can log in with the new password now.');
    }

    // --- CLIENT MANAGEMENT ---

    public function listClients(Request $request)
    {
        $query = User::role('client')->with(['roles', 'profile'])->withCount('jobs');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sort = $request->input('sort', 'newest');
        switch ($sort) {
            case 'oldest': $query->oldest(); break;
            case 'name_asc': $query->orderBy('name', 'asc'); break;
            case 'name_desc': $query->orderBy('name', 'desc'); break;
            case 'most_jobs': $query->orderBy('jobs_count', 'desc'); break;
            default: $query->latest(); break;
        }

        $clients = $query->paginate(10)->withQueryString();
        return view('admin.clients.index', ['users' => $clients]);
    }

    public function createClient()
    {
        return view('admin.clients.create');
    }

    public function storeClient(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:user_profiles,phone_number'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'billable_period_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'billable_period_days' => (int) ($validated['billable_period_days'] ?? 30),
            'status' => 'active',
        ]);

        $user->assignRole('client');

        UserProfile::create([
            'user_id' => $user->id,
            'phone_number' => $validated['phone_number'],
        ]);

        ClientProfile::create([
            'user_id' => $user->id,
            'company_name' => $validated['name'],
        ]);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    }

    public function editClient(User $user)
    {
        if (!$user->hasRole('client')) abort(404);
        $user->load('clientProfile');
        return view('admin.clients.edit', ['user' => $user]);
    }

    public function updateClient(Request $request, User $user)
    {
        if (!$user->hasRole('client')) abort(404);

        $validated = $request->validate([
            'billable_period_days' => 'required|integer|min:1',
            'email' => ['required', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($user->id)],
            'company_name' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'contact_person_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'gst_number' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:50',
            'tan_number' => 'nullable|string|max:50',
            'coi_number' => 'nullable|string|max:50',
            'logo' => 'nullable|image|max:2048',
            'pan_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'tan_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'coi_file' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'other_docs.*' => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120', 
        ]);

        $userUpdate = [
            'billable_period_days' => $validated['billable_period_days'],
            'email' => $validated['email'],
        ];
        if (!empty($validated['company_name'])) {
            // Keep users.name in sync with the editable Company Name on this form,
            // because the client listing displays users.name.
            $userUpdate['name'] = $validated['company_name'];
        }
        $user->update($userUpdate);

        $profileData = $request->only([
            'company_name', 'website', 'industry', 'company_size', 'description',
            'contact_person_name', 'contact_phone', 'address', 'city', 'state', 'pincode',
            'gst_number', 'pan_number', 'tan_number', 'coi_number'
        ]);

        if ($request->hasFile('logo')) $profileData['logo_path'] = $request->file('logo')->store('client_logos', 'public');
        if ($request->hasFile('pan_file')) $profileData['pan_file_path'] = $request->file('pan_file')->store('client_docs', 'public');
        if ($request->hasFile('tan_file')) $profileData['tan_file_path'] = $request->file('tan_file')->store('client_docs', 'public');
        if ($request->hasFile('coi_file')) $profileData['coi_file_path'] = $request->file('coi_file')->store('client_docs', 'public');

        if ($request->hasFile('other_docs')) {
            $existingDocs = $user->clientProfile->other_docs ?? [];
            $newDocs = [];
            foreach ($request->file('other_docs') as $file) {
                $newDocs[] = $file->store('client_docs/others', 'public');
            }
            $profileData['other_docs'] = array_merge($existingDocs, $newDocs);
        }

        $user->clientProfile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->route('admin.clients.index')->with('success', 'Client profile updated successfully!');
    }

    public function showClient(User $user)
    {
        if (!$user->hasRole('client')) abort(404);
        $user->load(['profile', 'clientProfile']);
        $jobs = \App\Models\Job::where('user_id', $user->id)->with(['category'])->latest()->paginate(10);
        $totalJobs = \App\Models\Job::where('user_id', $user->id)->count();
        $activeJobs = \App\Models\Job::where('user_id', $user->id)->where('status', 'approved')->count();
        $totalHires = \App\Models\JobApplication::whereHas('job', function($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereIn('hiring_status', ['Joined', 'Selected'])->count();

        return view('admin.clients.show', compact('user', 'jobs', 'totalJobs', 'activeJobs', 'totalHires'));
    }

    // --- CLIENT COMMERCIALS (Permanent Hiring Format) ---

    /**
     * Default rows pulled from the Permanent Hiring Commercial Format doc.
     */
    private function defaultCommercialContractData(): array
    {
        return [
            'percentage_based' => [
                ['label' => 'Upto 10 Lakh',     'min_ctc' => 0,        'max_ctc' => 1000000,  'fee_percent' => 7,    'replacement_days' => 30],
                ['label' => '10.01 to 20 Lakh', 'min_ctc' => 1000001,  'max_ctc' => 2000000,  'fee_percent' => 8.33, 'replacement_days' => 60],
                ['label' => '20.01 to 30 Lakh', 'min_ctc' => 2000001,  'max_ctc' => 3000000,  'fee_percent' => 10,   'replacement_days' => 90],
                ['label' => '30.01 to 40 Lakh', 'min_ctc' => 3000001,  'max_ctc' => 4000000,  'fee_percent' => 12,   'replacement_days' => 90],
                ['label' => '40.01 Lakh Above', 'min_ctc' => 4000001,  'max_ctc' => null,     'fee_percent' => 15,   'replacement_days' => 90],
            ],
            'profile_wise' => [
                ['profile' => 'Entry Level',     'fee_percent' => 5,    'replacement_days' => 30],
                ['profile' => 'Mid-Level',       'fee_percent' => 8.33, 'replacement_days' => 60],
                ['profile' => 'Sr. Level',       'fee_percent' => 10,   'replacement_days' => 90],
                ['profile' => 'Leader/CXO Level','fee_percent' => 12,   'replacement_days' => 90],
            ],
            'flat' => [
                ['category' => 'BPO/Sales', 'fee_amount' => 5000, 'replacement_days' => 30],
            ],
        ];
    }

    public function editCommercials(User $user)
    {
        if (!$user->hasRole('client')) abort(404);

        $commercial = \App\Models\ClientCommercial::firstOrNew(['user_id' => $user->id]);

        // Start every billing type empty for a brand-new client. Only saved slabs
        // are shown; the admin adds rows via the "+ Add" buttons. (Previously this
        // pre-seeded doc defaults, which looked like real, pre-filled commercials.)
        $existing = is_array($commercial->contract_data) ? $commercial->contract_data : [];
        $contract = [
            'percentage_based' => $existing['percentage_based'] ?? [],
            'profile_wise'     => $existing['profile_wise']     ?? [],
            'flat'             => $existing['flat']             ?? [],
        ];

        return view('admin.clients.commercials', [
            'user'       => $user,
            'commercial' => $commercial,
            'contract'   => $contract,
        ]);
    }

    public function updateCommercials(Request $request, User $user)
    {
        if (!$user->hasRole('client')) abort(404);

        $validated = $request->validate([
            'billing_type'       => 'required|in:percentage_based,profile_wise,flat',
            'invoice_raise_days' => 'required|integer|min:0|max:365',
            'payment_terms_days' => 'required|integer|min:0|max:365',
            'is_gst_applicable'  => 'nullable|boolean',

            // Slab rows
            'slab_label.*'           => 'nullable|string|max:60',
            'slab_min_ctc.*'         => 'nullable|integer|min:0',
            'slab_max_ctc.*'         => 'nullable|integer|min:0',
            'slab_fee_percent.*'     => 'nullable|numeric|min:0|max:100',
            'slab_replacement.*'     => 'nullable|integer|min:0|max:365',

            // Profile rows
            'prof_profile.*'         => 'nullable|string|max:60',
            'prof_fee_type.*'        => 'nullable|in:percent,flat',
            'prof_fee_value.*'       => 'nullable|numeric|min:0',
            'prof_replacement.*'     => 'nullable|integer|min:0|max:365',

            // Flat rows
            'flat_category.*'        => 'nullable|string|max:60',
            'flat_fee_amount.*'      => 'nullable|numeric|min:0',
            'flat_replacement.*'     => 'nullable|integer|min:0|max:365',
        ]);

        $slabs = [];
        foreach ((array) $request->input('slab_label', []) as $i => $label) {
            if (!trim((string) $label) && $request->input('slab_fee_percent.' . $i) === null) continue;
            $slabs[] = [
                'label'            => trim((string) $label),
                'min_ctc'          => $request->input("slab_min_ctc.$i") !== null && $request->input("slab_min_ctc.$i") !== '' ? (int) $request->input("slab_min_ctc.$i") : null,
                'max_ctc'          => $request->input("slab_max_ctc.$i") !== null && $request->input("slab_max_ctc.$i") !== '' ? (int) $request->input("slab_max_ctc.$i") : null,
                'fee_percent'      => (float) $request->input("slab_fee_percent.$i", 0),
                'replacement_days' => (int) $request->input("slab_replacement.$i", 0),
            ];
        }

        $profiles = [];
        foreach ((array) $request->input('prof_profile', []) as $i => $profile) {
            if (!trim((string) $profile)) continue;
            $type  = $request->input("prof_fee_type.$i", 'percent') === 'flat' ? 'flat' : 'percent';
            $value = (float) $request->input("prof_fee_value.$i", 0);
            // Clamp percent values to <=100
            if ($type === 'percent' && $value > 100) $value = 100;
            $profiles[] = [
                'profile'          => trim((string) $profile),
                'fee_type'         => $type,
                'fee_percent'      => $type === 'percent' ? $value : 0,
                'fee_flat'         => $type === 'flat'    ? $value : 0,
                'replacement_days' => (int) $request->input("prof_replacement.$i", 0),
            ];
        }

        $flats = [];
        foreach ((array) $request->input('flat_category', []) as $i => $category) {
            if (!trim((string) $category)) continue;
            $flats[] = [
                'category'         => trim((string) $category),
                'fee_amount'       => (float) $request->input("flat_fee_amount.$i", 0),
                'replacement_days' => (int) $request->input("flat_replacement.$i", 0),
            ];
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($user, $validated, $slabs, $profiles, $flats, $request) {
                \App\Models\ClientCommercial::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'billing_type'       => $validated['billing_type'],
                        'contract_data'      => [
                            'percentage_based' => $slabs,
                            'profile_wise'     => $profiles,
                            'flat'             => $flats,
                        ],
                        'invoice_raise_days' => $validated['invoice_raise_days'],
                        'payment_terms_days' => $validated['payment_terms_days'],
                        'is_gst_applicable'  => (bool) ($request->input('is_gst_applicable') ?? false),
                    ]
                );
            });
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Commercials could not be saved. Please try again. If the problem continues, contact support.');
        }

        return redirect()->route('admin.clients.commercials.edit', $user)
            ->with('success', 'Commercials updated successfully.');
    }

    // --- PARTNER MANAGEMENT ---

    public function listPartners(Request $request)
    {
        $query = User::role('partner')->with(['partnerProfile', 'profile']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('type')) {
            $query->whereHas('partnerProfile', function($q) use ($request) {
                $q->where('company_type', $request->input('type'));
            });
        }

        $partners = $query->latest()->paginate(10)->withQueryString();
        return view('admin.partners.index', ['users' => $partners]);
    }

    public function createPartner()
    {
        return view('admin.partners.create');
    }

    public function storePartner(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_type' => ['required', 'string', 'in:Placement Agency,Freelancer,Recruiter'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => 'active',
        ]);

        $user->assignRole('partner');

        PartnerProfile::create([
            'user_id' => $user->id,
            'company_type' => $request->company_type,
        ]);

        return redirect()->route('admin.partners.index')->with('success', 'Partner created successfully.');
    }

    public function editPartner(User $user)
    {
        if (!$user->hasRole('partner')) abort(404);
        $user->load('partnerProfile');
        return view('admin.partners.edit', ['user' => $user, 'profile' => $user->partnerProfile]);
    }

    public function updatePartnerTier(Request $request, User $user)
    {
        if (!$user->hasRole('partner')) abort(404);

        $validated = $request->validate([
            'partner_tier' => 'required|in:Bronze,Silver,Gold,Diamond',
        ]);

        $user->update(['partner_tier' => $validated['partner_tier']]);

        return back()->with('success', "Tier updated to {$validated['partner_tier']} for {$user->name}.");
    }

    public function managePartnerPlans()
    {
        $plans = \App\Models\PartnerPlan::all();
        return view('admin.partners.plans', compact('plans'));
    }

    public function updatePartnerPlan(Request $request, \App\Models\PartnerPlan $plan)
    {
        $validated = $request->validate([
            'subtitle'                 => 'nullable|string|max:255',
            'monthly_submission_limit' => 'nullable|integer|min:1',
            'max_team_members'         => 'required|integer|min:1',
            'price'                    => 'required|numeric|min:0',
            'price_max'                => 'nullable|numeric|min:0',
            'price_suffix'             => 'nullable|string|max:40',
            'commission_min'           => 'nullable|numeric|min:0|max:100',
            'commission_max'           => 'nullable|numeric|min:0|max:100',
            'accent_color'             => 'nullable|in:slate,blue,purple,rose,emerald',
            'sort_order'               => 'nullable|integer|min:0',
            'features'                 => 'nullable|string',
            'non_features'             => 'nullable|string',
        ]);

        // Convert line-separated textareas into clean arrays
        $linesToArr = function ($txt) {
            if (!$txt) return [];
            return collect(preg_split('/\r?\n/', $txt))
                ->map(fn ($l) => trim($l))
                ->filter()
                ->values()
                ->all();
        };

        $validated['features']              = $linesToArr($validated['features'] ?? null);
        $validated['non_features']          = $linesToArr($validated['non_features'] ?? null);
        $validated['can_view_premium_jobs'] = $request->has('can_view_premium_jobs');
        $validated['is_most_popular']       = $request->has('is_most_popular');

        $plan->update($validated);

        return redirect()->back()->with('success', "Plan '{$plan->name}' updated successfully.");
    }

    public function updatePartner(Request $request, User $user)
    {
        if (!$user->hasRole('partner')) abort(404);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'company_type' => 'nullable|string',
            'website' => 'nullable|url',
            'contact_phone' => 'nullable|string',
            'establishment_year' => 'nullable|integer',
            'bio' => 'nullable|string',
            'address' => 'nullable|string',
            'linkedin_url' => 'nullable|url',
            'facebook_url' => 'nullable|url',
            'twitter_url' => 'nullable|url',
            'instagram_url' => 'nullable|url',
            'beneficiary_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'account_type' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'pan_name' => 'nullable|string', 
            'pan_number' => 'nullable|string',
            'gst_number' => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048',
            'cancelled_cheque' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'pan_card' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
            'gst_certificate' => 'nullable|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user->update(['name' => $validated['name'], 'email' => $validated['email']]);

        $profileData = $request->only([
            'company_type', 'website', 'contact_phone', 'establishment_year', 'bio', 'address',
            'linkedin_url', 'facebook_url', 'twitter_url', 'instagram_url',
            'beneficiary_name', 'account_number', 'account_type', 'ifsc_code',
            'pan_name', 'pan_number', 'gst_number'
        ]);

        if ($request->hasFile('profile_picture')) {
            $profileData['profile_picture_path'] = $request->file('profile_picture')->store('partner_profiles/photos', 'public');
        }
        if ($request->hasFile('cancelled_cheque')) {
            $profileData['cancelled_cheque_path'] = $request->file('cancelled_cheque')->store('partner_profiles/docs', 'public');
        }
        if ($request->hasFile('pan_card')) {
            $profileData['pan_card_path'] = $request->file('pan_card')->store('partner_profiles/docs', 'public');
        }
        if ($request->hasFile('gst_certificate')) {
            $profileData['gst_certificate_path'] = $request->file('gst_certificate')->store('partner_profiles/docs', 'public');
        }

        $user->partnerProfile()->updateOrCreate(['user_id' => $user->id], $profileData);

        return redirect()->route('admin.partners.index')->with('success', 'Partner profile updated successfully.');
    }

    public function showPartner(User $user)
    {
        if (!$user->hasRole('partner')) abort(404);
        $user->load(['partnerProfile', 'profile']);
        return view('admin.partners.show', ['user' => $user, 'profile' => $user->partnerProfile]);
    }

    // --- JOB MANAGEMENT ---

    public function createJob()
    {
        $clients = User::role('client')->where('status', 'active')->get();
        $partners = User::role('partner')->where('status', 'active')->get();
        $candidates = Candidate::select('id', 'first_name', 'last_name', 'email')->latest()->get(); 
        
        $categories = Cache::remember('job_categories', 3600, fn () => JobCategory::orderBy('name')->get());
        $experienceLevels = Cache::remember('experience_levels', 3600, fn () => ExperienceLevel::orderBy('name')->get());
        $educationLevels = Cache::remember('education_levels', 3600, fn () => EducationLevel::orderBy('name')->get());

        return view('admin.jobs.create', compact(
            'clients', 'partners', 'candidates',
            'categories', 'experienceLevels', 'educationLevels'
        ));
    }

    public function storeJob(Request $request)
    {
        $validated = $request->validate([
            // Posting context (admin-only)
            'client_id'             => 'nullable|exists:users,id',
            'partner_visibility'    => 'required|in:all,selected',
            'allowed_partners'      => 'array|required_if:partner_visibility,selected',
            'restricted_candidates' => 'array|nullable',
            'payout_amount'         => 'nullable|numeric',
            'minimum_stay_days'     => 'nullable|integer',
            'replacement_guarantee_days' => 'nullable|integer|min:0|max:365',

            // Job specification (mirrors ClientController::validateClientJob)
            'title'                 => 'required|string|max:255',
            'category_id'           => 'required|exists:job_categories,id',
            'location'              => 'required|string|max:255',
            'job_type'              => 'required|string|max:100',
            'description'           => 'required|string',
            'min_salary'            => 'nullable|integer|min:0|required_with:max_salary',
            'max_salary'            => 'nullable|integer|min:0|gte:min_salary|required_with:min_salary',
            'min_experience'        => 'required|integer|min:0',
            'max_experience'        => 'required|integer|gte:min_experience|max:50',
            'education_level_id'    => 'required|exists:education_levels,id',
            'application_deadline'  => 'nullable|date',
            'skills_required'       => 'nullable|string',
            'company_website'       => 'nullable|url',
            'openings'              => 'nullable|integer|min:1',
            'gender_preference'     => 'required|string|in:Any,Male,Female,Other',
            'min_age'               => 'nullable|integer|min:18|max:80',
            'max_age'               => 'nullable|integer|min:18|max:80|gte:min_age',
            'is_company_confidential' => 'nullable|boolean',
            // Extended job-detail fields (all optional)
            'work_mode'         => 'nullable|string|in:On-site,Hybrid,Remote,Field Job,Work From Home (WFH)',
            'shift'             => 'nullable|string|in:Day Shift,Night Shift,Rotational Shift,Flexible Shift,Weekend Shift',
            'specialization'    => 'nullable|string|max:255',
            'notice_period'     => 'nullable|string|max:100',
            'languages'         => 'nullable|string|max:255',
            'industry'          => 'nullable|string|max:255',
            'department'        => 'nullable|string|max:255',
            'reporting_manager' => 'nullable|string|max:255',
            'travel_required'   => 'nullable|boolean',
            'benefits'          => 'nullable|array',
            'benefits.*'        => 'string|in:PF,ESIC,Insurance,Food,Transport,Accommodation,Laptop,Mobile,Joining Bonus,Relocation',
        ]);

        $salary = $this->formatSalaryRange(
            $validated['min_salary'] ?? null,
            $validated['max_salary'] ?? null
        );

        $companyName = 'Simplyhiree';
        if ($request->filled('client_id')) {
            $client = User::find($request->client_id);
            $companyName = $client->name;
        } elseif ($request->filled('company_name')) {
            $companyName = $request->company_name;
        }

        $job = Job::create([
            'user_id'              => $request->client_id,
            'company_name'         => $companyName,
            'status'               => 'approved',
            'title'                => $validated['title'],
            'category_id'          => $validated['category_id'],
            'location'             => $validated['location'],
            'salary'               => $salary,
            'job_type'             => $validated['job_type'],
            'description'          => $this->sanitizeJobDescription($validated['description']),
            'gender_preference'    => $validated['gender_preference'],
            'min_age'              => $validated['min_age'] ?? null,
            'max_age'              => $validated['max_age'] ?? null,
            'min_experience'       => $validated['min_experience'],
            'max_experience'       => $validated['max_experience'],
            'experience_level_id'  => null,
            'education_level_id'   => $validated['education_level_id'],
            'application_deadline' => $validated['application_deadline'] ?? null,
            'payout_amount'        => $validated['payout_amount'] ?? 0,
            'minimum_stay_days'    => $validated['minimum_stay_days'] ?? 0,
            'replacement_guarantee_days' => $validated['replacement_guarantee_days'] ?? null,
            'partner_visibility'   => $validated['partner_visibility'],
            'skills_required'      => $validated['skills_required'] ?? null,
            'company_website'      => $validated['company_website'] ?? null,
            'openings'             => $validated['openings'] ?? 1,
            'is_company_confidential' => (bool) ($validated['is_company_confidential'] ?? false),
            // Extended job-detail fields
            'work_mode'         => $validated['work_mode'] ?? null,
            'shift'             => $validated['shift'] ?? null,
            'specialization'    => $validated['specialization'] ?? null,
            'notice_period'     => $validated['notice_period'] ?? null,
            'languages'         => $validated['languages'] ?? null,
            'industry'          => $validated['industry'] ?? null,
            'department'        => $validated['department'] ?? null,
            'reporting_manager' => $validated['reporting_manager'] ?? null,
            'travel_required'   => array_key_exists('travel_required', $validated) ? (bool) $validated['travel_required'] : null,
            'benefits'          => $validated['benefits'] ?? null,
        ]);

        if ($validated['partner_visibility'] === 'selected' && $request->has('allowed_partners')) {
            $job->allowedPartners()->sync($request->allowed_partners);
        }
        if ($request->has('restricted_candidates')) {
            $job->restrictedCandidates()->sync($request->restricted_candidates);
        }

        return redirect()->route('admin.jobs.pending')->with('success', 'Job created successfully.');
    }

    public function showJob(Job $job)
    {
        $job->load(['user', 'experienceLevel', 'educationLevel', 'category']);
        return view('admin.jobs.show', compact('job'));
    }

    public function editJob(Job $job)
    {
        $job->load(['allowedPartners']);

        return view('admin.jobs.edit', [
            'job' => $job,
            'clients' => User::role('client')->orderBy('name')->get(),
            'partners' => User::role('partner')->where('status', 'active')->orderBy('name')->get(),
            'categories' => Cache::remember('job_categories', 3600, fn () => JobCategory::orderBy('name')->get()),
            'educationLevels' => Cache::remember('education_levels', 3600, fn () => EducationLevel::orderBy('name')->get()),
        ]);
    }

    public function updateJob(Request $request, Job $job)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:users,id',
            'company_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:job_categories,id',
            'location' => 'required|string|max:255',
            'salary' => 'nullable|string|max:255',
            'job_type' => 'required|string|max:100',
            'description' => 'required|string',
            'min_experience' => 'required|integer|min:0|max:50',
            'max_experience' => 'required|integer|gte:min_experience|max:50',
            'education_level_id' => 'required|exists:education_levels,id',
            'application_deadline' => 'nullable|date',
            'status' => 'required|in:pending_approval,approved,on_hold,closed,rejected',
            'skills_required' => 'nullable|string',
            'company_website' => 'nullable|url',
            'openings' => 'required|integer|min:1',
            'gender_preference' => 'required|in:Any,Male,Female,Other',
            'min_age' => 'nullable|integer|min:18|max:80',
            'max_age' => 'nullable|integer|min:18|max:80|gte:min_age',
            'is_company_confidential' => 'required|boolean',
            'screening_required' => 'required|boolean',
            'commercial_source' => 'required|in:simplyhire,manual',
            'fee_type' => 'nullable|required_if:commercial_source,manual|in:flat,percentage',
            'fee_amount' => 'nullable|required_if:commercial_source,manual|numeric|min:0',
            'payout_amount' => 'required|numeric|min:0',
            'minimum_stay_days' => 'required|integer|min:0|max:3650',
            'replacement_guarantee_days' => 'nullable|integer|min:0|max:365',
            'invoice_release_days' => 'nullable|integer|min:0|max:365',
            'replacement_period_days' => 'nullable|integer|min:0|max:365',
            'partner_visibility' => 'required|in:all,selected',
            'allowed_partners' => 'array|required_if:partner_visibility,selected',
            'allowed_partners.*' => 'integer|exists:users,id',
            'auto_forward_hours' => 'nullable|integer|min:0|max:720',
            // Extended job-detail fields (all optional)
            'work_mode'         => 'nullable|string|in:On-site,Hybrid,Remote,Field Job,Work From Home (WFH)',
            'shift'             => 'nullable|string|in:Day Shift,Night Shift,Rotational Shift,Flexible Shift,Weekend Shift',
            'specialization'    => 'nullable|string|max:255',
            'notice_period'     => 'nullable|string|max:100',
            'languages'         => 'nullable|string|max:255',
            'industry'          => 'nullable|string|max:255',
            'department'        => 'nullable|string|max:255',
            'reporting_manager' => 'nullable|string|max:255',
            'travel_required'   => 'nullable|boolean',
            'benefits'          => 'nullable|array',
            'benefits.*'        => 'string|in:PF,ESIC,Insurance,Food,Transport,Accommodation,Laptop,Mobile,Joining Bonus,Relocation',
        ]);

        $wasApproved = $job->status === 'approved';
        $manual = $validated['commercial_source'] === 'manual';
        DB::transaction(function () use ($job, $validated, $manual) {
            $job->update([
                'user_id' => $validated['client_id'] ?: null,
                'company_name' => $validated['company_name'],
                'title' => $validated['title'],
                'category_id' => $validated['category_id'],
                'location' => $validated['location'],
                'salary' => $validated['salary'] ?? null,
                'job_type' => $validated['job_type'],
                'description' => $this->sanitizeJobDescription($validated['description']),
                'min_experience' => $validated['min_experience'],
                'max_experience' => $validated['max_experience'],
                'education_level_id' => $validated['education_level_id'],
                'application_deadline' => $validated['application_deadline'] ?? null,
                'status' => $validated['status'],
                'skills_required' => $validated['skills_required'] ?? null,
                'company_website' => $validated['company_website'] ?? null,
                'openings' => $validated['openings'],
                'gender_preference' => $validated['gender_preference'],
                'min_age' => $validated['min_age'] ?? null,
                'max_age' => $validated['max_age'] ?? null,
                'is_company_confidential' => (bool) $validated['is_company_confidential'],
                'screening_required' => (bool) $validated['screening_required'],
                'commercial_source' => $validated['commercial_source'],
                'fee_type' => $manual ? $validated['fee_type'] : null,
                'fee_amount' => $manual ? $validated['fee_amount'] : null,
                'payout_amount' => $validated['payout_amount'],
                'minimum_stay_days' => $validated['minimum_stay_days'],
                'replacement_guarantee_days' => $validated['replacement_guarantee_days'] ?? null,
                'invoice_release_days' => $validated['invoice_release_days'] ?? null,
                'replacement_period_days' => $validated['replacement_period_days'] ?? null,
                'partner_visibility' => $validated['partner_visibility'],
                'vendor_assignment_mode' => $validated['partner_visibility'] === 'selected' ? 'selected' : 'open',
                'auto_forward_hours' => $validated['auto_forward_hours'] ?? null,
                // Extended job-detail fields
                'work_mode'         => $validated['work_mode'] ?? null,
                'shift'             => $validated['shift'] ?? null,
                'specialization'    => $validated['specialization'] ?? null,
                'notice_period'     => $validated['notice_period'] ?? null,
                'languages'         => $validated['languages'] ?? null,
                'industry'          => $validated['industry'] ?? null,
                'department'        => $validated['department'] ?? null,
                'reporting_manager' => $validated['reporting_manager'] ?? null,
                'travel_required'   => array_key_exists('travel_required', $validated) ? (bool) $validated['travel_required'] : null,
                'benefits'          => $validated['benefits'] ?? null,
            ]);

            $job->allowedPartners()->sync(
                $validated['partner_visibility'] === 'selected'
                    ? ($validated['allowed_partners'] ?? [])
                    : []
            );
        });

        if ($validated['status'] === 'approved' && !$wasApproved) {
            $this->sendJobApprovedNotifications($job);
        }

        return redirect()->route('admin.jobs.show', $job)->with(
            'success',
            $validated['status'] === 'approved'
                ? 'Live job, commercials, and vendor controls updated. Existing applications were preserved.'
                : 'Job details, commercials, and vendor controls updated.'
        );
    }

    public function pendingJobs()
    {
        $pendingJobs = Job::where('status', 'pending_approval')->with(['user', 'educationLevel', 'experienceLevel'])->latest()->paginate(20);
        $deactivationRequests = Job::whereNotNull('deactivation_requested_at')
            ->with(['user'])
            ->latest('deactivation_requested_at')
            ->get();
        return view('admin.jobs.pending', [
            'jobs' => $pendingJobs,
            'deactivationRequests' => $deactivationRequests,
        ]);
    }

    public function approveJob(Request $request, Job $job)
    {
        $validated = $request->validate([
            'payout_amount'              => 'required|numeric|min:0',
            'minimum_stay_days'          => 'required|integer|min:1',
            'replacement_guarantee_days' => 'nullable|integer|min:0|max:365',
        ]);
        $update = [
            'status'            => 'approved',
            'payout_amount'     => $validated['payout_amount'],
            'minimum_stay_days' => $validated['minimum_stay_days'],
        ];
        if (array_key_exists('replacement_guarantee_days', $validated) && $validated['replacement_guarantee_days'] !== null) {
            $update['replacement_guarantee_days'] = $validated['replacement_guarantee_days'];
        }
        $job->update($update);
        $this->sendJobApprovedNotifications($job);
        return redirect()->back()->with('success', 'Job has been approved and is now live.');
    }

    public function rejectJob(Job $job)
    {
        $job->update(['status' => 'rejected']);
        if ($job->user) $job->user->notify(new JobRejected($job));
        return redirect()->back()->with('success', 'Job has been rejected.');
    }

    public function updateJobStatus(Request $request, Job $job)
    {
        $request->validate(['status' => 'required|in:approved,on_hold,closed,rejected']);
        $wasApproved = $job->status === 'approved';
        $job->update(['status' => $request->status]);

        if ($request->status === 'approved' && !$wasApproved) {
            $this->sendJobApprovedNotifications($job);
        }

        return redirect()->back()->with('success', "Job status updated to {$request->status}.");
    }

    private function sendJobApprovedNotifications(Job $job): void
    {
        if ($job->user) {
            $job->user->notify(new JobApproved($job));
        }

        $actorName = auth()->user()?->name;
        $superadmins = User::role(['Superadmin', 'Manager'])->get();
        foreach ($superadmins as $superadmin) {
            $superadmin->notify(new ClientJobApprovedForAdmin($job, $actorName));
        }
    }

    /**
     * "Delete" archives the job. We never hard-delete so applications,
     * candidate history, partner data, etc. remain intact and viewable
     * in the Archived Jobs section.
     */
    public function destroyJob(Job $job)
    {
        if ($job->archived_at) {
            return back()->with('info', 'Job is already archived.');
        }

        $job->update([
            'status'                    => 'closed',
            'archived_at'               => now(),
            'archived_by_role'          => 'Superadmin',
            'archived_by_user_id'       => auth()->id(),
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
        ]);

        // Stay on whichever page the admin came from (Master Job Report,
        // Pending Jobs, etc.) with the success flash. Archived job will
        // simply disappear from the list since those pages filter
        // whereNull('archived_at').
        return back()->with('success', 'Job moved to archive. All applications and candidate data preserved.');
    }

    /**
     * Approve a client's deactivation request — closes the job.
     */
    public function approveDeactivation(Job $job)
    {
        if (!$job->deactivation_requested_at) {
            return back()->with('error', 'This job has no pending deactivation request.');
        }

        $job->update([
            'status'                    => 'closed',
            'archived_at'               => now(),
            'archived_by_role'          => 'Client',
            'archived_by_user_id'       => $job->user_id,
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
        ]);

        return back()->with('success', 'Deactivation approved. Job has been archived.');
    }

    /**
     * List archived jobs (deactivated via Superadmin approval).
     */
    public function archivedJobs()
    {
        $jobs = Job::whereNotNull('archived_at')
            ->with(['user', 'category', 'archivedBy'])
            ->withCount('jobApplications')
            ->latest('archived_at')
            ->paginate(20);

        return view('admin.jobs.archived', compact('jobs'));
    }

    /**
     * Show full archive detail for one job — every application with full lifecycle.
     */
    public function showArchivedJob(Job $job)
    {
        if (!$job->archived_at) {
            return redirect()->route('admin.jobs.show', $job)
                ->with('info', "Job #{$job->id} is currently active — it has not been archived. Use the Archive button on the job page to move it to the archive.");
        }

        $job->load([
            'user',
            'category',
            'experienceLevel',
            'educationLevel',
            'archivedBy',
            'jobApplications.candidate.partner',
            'jobApplications.candidateUser',
        ]);

        return view('admin.jobs.archived_show', compact('job'));
    }

    /**
     * Permanently restore an archived job back to approved state.
     */
    public function restoreArchivedJob(Job $job)
    {
        if (!$job->archived_at) {
            return back()->with('error', 'This job is not archived.');
        }

        $job->update([
            'status'              => 'approved',
            'archived_at'         => null,
            'archived_by_role'    => null,
            'archived_by_user_id' => null,
        ]);

        return back()->with('success', 'Job restored and set back to approved.');
    }

    /**
     * Dismiss a deactivation request without closing the job.
     */
    public function dismissDeactivation(Job $job)
    {
        if (!$job->deactivation_requested_at) {
            return back()->with('error', 'This job has no pending deactivation request.');
        }

        $job->update([
            'deactivation_requested_at' => null,
            'deactivation_reason'       => null,
        ]);

        return back()->with('success', 'Deactivation request dismissed. Job remains active.');
    }

    public function manageJobExclusions(Job $job)
    {
        $job->load(['educationLevel']);
        $partners = User::role('partner')->where('status', 'active')->with(['partnerProfile'])->orderBy('name')->get();
        
        $allowedPartnerIds = $job->allowedPartners()->pluck('users.id')->toArray();
        if (($job->partner_visibility === 'all' || empty($job->partner_visibility)) && empty($allowedPartnerIds)) {
            $allowedPartnerIds = $partners->pluck('id')->toArray();
        }
        
        return view('admin.jobs.manage', [
            'job' => $job, 
            'allPartners' => $partners, 
            'allowedPartnerIds' => $allowedPartnerIds
        ]);
    }
 
    public function updateJobExclusions(Request $request, Job $job)
    {
        $allowedIds = $request->input('allowed_partners', []);
        
        // Sync to allowedPartners
        $job->allowedPartners()->sync($allowedIds);
        
        // Clear old legacy exclusions completely
        $job->excludedPartners()->sync([]);
        
        // Mark visibility as selected (only allowed list can view)
        $job->update(['partner_visibility' => 'selected']);
        
        return redirect()->route('admin.jobs.pending')->with('success', 'Partner visibility updated successfully.');
    }

    // --- APPLICATION MANAGEMENT ---

    public function listApplications(Request $request)
    {
        $query = JobApplication::with(['job', 'candidate', 'candidate.partner', 'candidateUser.profile']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('candidate', function($subQ) use ($search) {
                    $subQ->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('candidateUser', function($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('job', function($subQ) use ($search) {
                    $subQ->where('title', 'like', "%{$search}%");
                });
            });
        }
        if ($request->filled('status')) $query->where('status', $request->input('status'));
        if ($request->filled('job_id')) $query->where('job_id', $request->input('job_id'));
        if ($request->filled('partner_id')) {
             $query->whereHas('candidate', function($q) use ($request) {
                $q->where('partner_id', $request->input('partner_id'));
             });
        }
        if ($request->filled('client_id')) {
            $query->whereHas('job', fn ($q) => $q->where('user_id', (int) $request->input('client_id')));
        }
        if ($request->filled('date_from')) {
            try { $query->whereDate('created_at', '>=', \Carbon\Carbon::parse($request->input('date_from'))->toDateString()); } catch (\Throwable $e) {}
        }
        if ($request->filled('date_to')) {
            try { $query->whereDate('created_at', '<=', \Carbon\Carbon::parse($request->input('date_to'))->toDateString()); } catch (\Throwable $e) {}
        }

        $allowedPerPage = [20, 50, 100, 150, 200];
        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 20;
        }

        $applications = $query->latest()->paginate($perPage)->withQueryString();
        $jobs = Job::select('id', 'title')->orderBy('title')->get();
        $partners = User::role('partner')->select('id', 'name')->orderBy('name')->get();
        $clients = User::role('client')->select('id', 'name')->orderBy('name')->get();

        return view('admin.applications.index', [
            'applications'   => $applications,
            'jobs'           => $jobs,
            'partners'       => $partners,
            'clients'        => $clients,
            'allowedPerPage' => $allowedPerPage,
            'perPage'        => $perPage,
        ]);
    }

    /**
     * Superadmin marks a candidate Selected on behalf of the client.
     * Stamps selected_by_admin_id + selected_by_admin_at so the client
     * UI can render a distinct "Selected by Superadmin" chip.
     */
    public function adminSelectApplicant(Request $request, JobApplication $application)
    {
        $application->load('job');
        if (!$application->job || $application->status !== 'Approved') {
            return back()->with('error', 'Only approved applications can be marked Selected.');
        }
        if (in_array($application->hiring_status, ['Selected', 'Joined'], true)) {
            return back()->with('error', 'This candidate is already marked Selected.');
        }

        $validated = $request->validate([
            'joining_date' => 'required|date|after_or_equal:today',
            'final_ctc'    => 'nullable|numeric|min:0',
            'admin_notes'  => 'nullable|string|max:1000',
        ]);

        $application->update([
            'hiring_status'         => 'Selected',
            'joining_date'          => Carbon::parse($validated['joining_date']),
            'final_ctc'             => $validated['final_ctc'] ?? null,
            'client_notes'          => $validated['admin_notes'] ?? null,
            'selected_by_admin_id'  => Auth::id(),
            'selected_by_admin_at'  => now(),
        ]);

        // Stamp the resolved invoice amount and replacement window.
        $resolved = $application->fresh(['job.user'])->resolveCommercial();
        if ($resolved) {
            $stamp = [];
            if ($application->invoice_amount === null && $resolved['invoice_amount'] > 0) {
                $stamp['invoice_amount'] = $resolved['invoice_amount'];
            }
            if ($application->replacement_window_days === null && $resolved['replacement_days'] !== null) {
                $stamp['replacement_window_days'] = $resolved['replacement_days'];
            }
            if (!empty($stamp)) $application->update($stamp);
        }

        try {
            \Illuminate\Support\Facades\Notification::send(
                array_filter([$application->job?->user, $application->candidate?->partner]),
                new \App\Notifications\CandidateSelected($application->fresh())
            );
        } catch (\Throwable $e) {}

        return back()->with('success', 'Candidate marked Selected on behalf of the client. The client will see this as "Selected by Superadmin".');
    }

    /**
     * Tracker Download — stream a CSV of the 16-field candidate data
     * format for the selected job applications. Capped at 500 ids per
     * request so the server never has to hold an unbounded set in memory.
     */
    public function applicationsTrackerExport(Request $request)
    {
        $ids = collect((array) $request->input('ids', []))
            ->map(fn ($v) => (int) $v)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return back()->with('error', 'Select at least one candidate to download the tracker.');
        }

        $maxRows = 200;
        if ($ids->count() > $maxRows) {
            return back()->with('error', "You can export at most {$maxRows} candidates at a time. You selected {$ids->count()}. Please refine the selection.");
        }

        $idList = $ids->all();

        $fileName = 'candidate_tracker_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
            'X-Accel-Buffering'   => 'no',
        ];

        return response()->streamDownload(function () use ($idList) {
            // Give the export room to finish on shared hosts and stream as we write.
            @set_time_limit(120);
            @ignore_user_abort(false);
            while (ob_get_level() > 0) { @ob_end_clean(); }

            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, [
                'Date of Application',
                'Name',
                'Email ID',
                'Phone Number',
                'Current Location',
                'Preferred Locations',
                'Total Experience',
                'Current Company Name',
                'Current Designation',
                'Annual Salary (Current)',
                'Notice Period / Availability',
                'Gender',
                'Marital Status',
                'Qualification',
                'Job Title / Applied For',
                'Expected Salary',
                'Source (Partner)',
                'Application Code',
                'Status',
            ]);

            // Stream in chunks of 50 to keep memory bounded.
            JobApplication::with(['job', 'candidate.partner', 'candidateUser.profile'])
                ->whereIn('id', $idList)
                ->orderBy('created_at', 'desc')
                ->chunkById(50, function ($applications) use ($out) {
                    foreach ($applications as $app) {
                $cand   = $app->candidate;
                $prof   = $app->candidateUser?->profile;
                $job    = $app->job;
                $name   = $cand
                    ? trim(($cand->first_name ?? '').' '.($cand->last_name ?? ''))
                    : ($app->candidateUser?->name ?? '');
                $expY   = $cand?->total_experience_years ?? $prof?->total_experience_years;
                $expM   = $cand?->total_experience_months ?? $prof?->total_experience_months;
                $totalExp = ($expY === null && $expM === null)
                    ? ($cand?->experience_status ?? $prof?->experience_status ?? '')
                    : ((int) ($expY ?? 0)).' Year(s) '.((int) ($expM ?? 0)).' Month(s)';

                $prefRaw = $cand?->preferred_locations ?? $prof?->preferred_locations ?? null;
                $prefLoc = is_array($prefRaw) ? implode(', ', $prefRaw) : ($prefRaw ?: '');

                $qualLevel = $cand?->education_level ?? '';
                $qualDeg   = $cand?->qualification_degree ?? $prof?->qualification_degree ?? '';
                $spec      = $cand?->specialization ?? $prof?->specialization ?? '';
                $qualParts = array_filter([$qualDeg, $spec], fn ($v) => $v !== '' && $v !== null);
                $qual      = implode(' — ', $qualParts);
                if ($qualLevel) $qual = trim(($qual ? $qual.' ' : '').'('.$qualLevel.')');

                $partnerName = $cand?->partner?->name ?? 'Direct';

                        fputcsv($out, [
                            optional($app->created_at)->format('d-M-Y'),
                            $name ?: '',
                            $cand?->email ?? $app->candidateUser?->email ?? '',
                            $cand?->phone_number ?? $prof?->phone_number ?? '',
                            $cand?->location ?? $prof?->location ?? '',
                            $prefLoc,
                            $totalExp,
                            $cand?->current_company ?? $prof?->current_company ?? '',
                            $cand?->current_designation ?? $prof?->current_role ?? '',
                            $cand?->current_ctc ?? $prof?->current_ctc ?? '',
                            $cand?->notice_period ?? $prof?->notice_period ?? '',
                            $cand?->gender ?? $prof?->gender ?? '',
                            $cand?->marital_status ?? $prof?->marital_status ?? '',
                            $qual,
                            $job?->title ?? '',
                            $cand?->expected_ctc ?? $prof?->expected_ctc ?? '',
                            $partnerName,
                            $app->application_code ?? ('#'.$app->id),
                            $app->status ?? '',
                        ]);
                    }
                    @flush();
                });

            fclose($out);
        }, $fileName, $headers);
    }

    public function bulkApproveApplications(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return redirect()->back()->with('error', 'No applications selected.');
        }

        $applications = JobApplication::whereIn('id', $ids)
            ->where('status', 'Pending Review')
            ->with(['job', 'candidate.partner', 'candidateUser'])
            ->get();

        if ($applications->isEmpty()) {
            return redirect()->back()->with('error', 'No pending-review applications found in your selection.');
        }

        foreach ($applications as $application) {
            $application->update(['status' => 'Approved']);
            $this->notifyApplicationStakeholder($application, true);
        }

        return redirect()->back()->with('success', $applications->count() . ' application(s) approved successfully.');
    }

    public function approveApplication(Request $request, JobApplication $application)
    {
        $application->loadMissing(['job', 'candidate.partner', 'candidateUser']);
        $application->update(['status' => 'Approved']);
        $this->notifyApplicationStakeholder($application, true);

        $msg = 'Application ' . ($application->application_code ?? ('#' . $application->id)) . ' approved.';
        $redirectTo = $request->input('redirect_to');
        return $redirectTo
            ? redirect($redirectTo)->with('success', $msg)
            : back()->with('success', $msg);
    }

    public function rejectApplication(Request $request, JobApplication $application)
    {
        $application->loadMissing(['job', 'candidate.partner', 'candidateUser']);
        $application->update(['status' => 'Rejected']);
        $this->notifyApplicationStakeholder($application, false);

        $msg = 'Application ' . ($application->application_code ?? ('#' . $application->id)) . ' rejected.';
        $redirectTo = $request->input('redirect_to');
        return $redirectTo
            ? redirect($redirectTo)->with('success', $msg)
            : back()->with('success', $msg);
    }

    public function showApplication(JobApplication $application)
    {
        $application->load(['candidate', 'job', 'candidate.partner', 'candidateUser.profile', 'interviewRounds']);
        return view('admin.applications.show', compact('application'));
    }

    public function updateApplicationResume(Request $request, JobApplication $application)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf,doc,docx|max:10240', // max 10MB
        ]);

        $file = $request->file('resume');
        $path = $file->store('resumes', 'public');

        // Check if there is an agency candidate or direct candidate
        if ($application->candidate) {
            $candidate = $application->candidate;
            // Delete old resume if exists
            if ($candidate->resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($candidate->resume_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($candidate->resume_path);
            }
            $candidate->update(['resume_path' => $path]);
        } elseif ($application->candidateUser && $application->candidateUser->profile) {
            $profile = $application->candidateUser->profile;
            // Delete old resume if exists
            if ($profile->resume_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($profile->resume_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($profile->resume_path);
            }
            $profile->update(['resume_path' => $path]);
        }

        return redirect()->back()->with('success', 'Candidate resume updated successfully!');
    }

    private function notifyApplicationStakeholder(JobApplication $application, bool $approved): void
    {
        $notification = $approved
            ? new ApplicationApprovedByAdmin($application)
            : new ApplicationRejectedByAdmin($application);

        $partner = $application->candidate?->partner;
        if ($partner) {
            $partner->notifyNow($notification);
            return;
        }

        if ($application->candidateUser) {
            $application->candidateUser->notifyNow($notification);
        }
    }

    public function jobApplicantsReport(\App\Models\Job $job)
    {
        $applications = $job->jobApplications()->with(['candidate', 'candidate.partner', 'candidateUser.profile'])->latest()->paginate(20);
        return view('admin.reports.job_applicants', compact('job', 'applications'));
    }

    public function exportJobApplicantsReport(\App\Models\Job $job)
    {
        $applications = $job->jobApplications()
            ->with(['candidate', 'candidate.partner', 'candidateUser.profile'])
            ->latest()
            ->get();

        $safeTitle = preg_replace('/[^A-Za-z0-9_\-]/', '_', $job->title ?? 'job');
        $fileName = 'job_applicants_' . $safeTitle . '_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($applications) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Candidate Name',
                'Email',
                'Phone',
                'Source Partner',
                'Admin Status',
                'Client Stage',
                'Applied Date',
                'Interview Date',
                'Joining Date',
            ]);

            foreach ($applications as $application) {
                $candidate = $application->candidate;
                $candidateUser = $application->candidateUser;
                $partnerName = $candidate && $candidate->partner ? $candidate->partner->name : 'Direct';
                $fullName = $candidate
                    ? trim(($candidate->first_name ?? '') . ' ' . ($candidate->last_name ?? ''))
                    : '';
                if ($fullName === '') {
                    $fullName = $candidateUser?->name ?? 'Unknown Candidate';
                }
                $email = $candidate?->email ?? $candidateUser?->email ?? '';
                $phone = $candidate?->phone_number ?? $candidateUser?->profile?->phone_number ?? '';

                fputcsv($handle, [
                    $fullName,
                    $email,
                    $phone,
                    $partnerName,
                    $application->status ?? '',
                    $application->hiring_status ?? '',
                    optional($application->created_at)->format('Y-m-d H:i:s'),
                    optional($application->interview_at)->format('Y-m-d H:i:s'),
                    optional($application->joining_date)->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    // --- BILLING ---

    public function billingReport(Request $request)
    {
        $query = JobApplication::where('hiring_status', 'Selected')
            ->whereNotNull('joining_date')
            ->with(['job.user', 'candidate', 'candidateUser']);

        if ($request->filled('client_id')) {
            $query->whereHas('job', fn ($q) => $q->where('user_id', (int) $request->client_id));
        }
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->whereHas('candidate', fn ($qq) => $qq->where('first_name', 'like', "%{$term}%")->orWhere('last_name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                  ->orWhereHas('candidateUser', fn ($qq) => $qq->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"))
                  ->orWhereHas('job', fn ($qq) => $qq->where('title', 'like', "%{$term}%"));
            });
        }

        $apps = $query->latest('joining_date')->paginate(25)->withQueryString();
        $rows = $apps->through(fn ($app) => $app->billingSnapshot());

        $statusFilter = $request->input('status');
        if ($statusFilter) {
            $rows->setCollection(
                $rows->getCollection()->filter(fn ($r) => $r['status'] === $statusFilter)->values()
            );
        }

        $current = $rows->getCollection();
        $counts = [
            'Paid'         => $current->where('status', 'Paid')->count(),
            'Overdue'      => $current->where('status', 'Overdue')->count(),
            'Raised'       => $current->where('status', 'Raised')->count(),
            'Due to Raise' => $current->where('status', 'Due to Raise')->count(),
            'Maturing'     => $current->where('status', 'Maturing')->count(),
        ];

        // For the client filter dropdown — clients who have any Selected hire
        $clients = \App\Models\User::role('client')
            ->whereHas('jobs.jobApplications', fn ($q) => $q->where('hiring_status', 'Selected'))
            ->orderBy('name')
            ->get(['id', 'name']);

        // Sanity check — clients with Selected hires but no commercial configured.
        // Pulls a fresh query so it's independent of pagination / status filter.
        $missingCommercials = \App\Models\User::role('client')
            ->whereHas('jobs.jobApplications', fn ($q) => $q->where('hiring_status', 'Selected')->whereNotNull('joining_date'))
            ->whereDoesntHave('clientCommercial')
            ->withCount(['jobs as selected_hires_count' => function ($q) {
                $q->join('job_applications', 'job_applications.job_id', '=', 'jobs.id')
                  ->where('job_applications.hiring_status', 'Selected')
                  ->whereNotNull('job_applications.joining_date');
            }])
            ->orderByDesc('selected_hires_count')
            ->get(['id', 'name', 'email']);

        return view('admin.billing.index', [
            'placements'         => $rows,
            'counts'             => $counts,
            'clients'            => $clients,
            'statusFilter'       => $statusFilter,
            'missingCommercials' => $missingCommercials,
        ]);
    }

    public function markAsPaid(JobApplication $application)
    {
        $application->update(['payment_status' => 'paid', 'paid_at' => now()]);
        return redirect()->back()->with('success', 'Invoice marked as PAID.');
    }

    // --- VENDOR RATINGS ---

    public function vendorRatingsIndex(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\User::role('partner')
            ->whereNull('parent_partner_id')
            ->select(['id','name','email','status','partner_plan','partner_tier','avg_rating','total_ratings','selection_ratio','closure_rate','repeat_hire_count','vendor_badge','vendor_level','penalty_active','penalty_reason']);

        if ($request->filled('level')) {
            $query->where('vendor_level', $request->level);
        }
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(fn ($q) => $q->where('name','like',"%{$term}%")->orWhere('email','like',"%{$term}%"));
        }

        $partners = $query->orderByDesc('avg_rating')->orderByDesc('total_ratings')->paginate(25)->withQueryString();
        $recentRatings = \App\Models\VendorRating::with(['partner','ratedBy','job'])->latest()->limit(10)->get();

        return view('admin.vendor_ratings.index', compact('partners','recentRatings'));
    }

    public function vendorRatingPenalty(\Illuminate\Http\Request $request, \App\Models\User $user)
    {
        if (!$user->hasRole('partner')) abort(404);
        $data = $request->validate(['penalty_reason' => 'nullable|string|max:500']);
        $user->update([
            'penalty_active' => true,
            'penalty_reason' => $data['penalty_reason'] ?? 'Manual admin penalty',
            'vendor_level'   => 'Restricted',
        ]);
        return back()->with('success', "Penalty applied to {$user->name}.");
    }

    public function vendorRatingLiftPenalty(\App\Models\User $user)
    {
        if (!$user->hasRole('partner')) abort(404);
        $user->update(['penalty_active' => false, 'penalty_reason' => null]);
        \App\Models\VendorRating::recomputeFor($user->id); // recalc level from data
        return back()->with('success', "Penalty lifted for {$user->name}.");
    }

    // --- PLAN CHANGE REQUESTS ---

    public function planRequestsIndex(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\PlanChangeRequest::with(['partner', 'actionedBy']);
        if ($request->filled('status')) $query->where('status', $request->status);
        $requests = $query->latest()->paginate(25)->withQueryString();

        $counts = [
            'pending'   => \App\Models\PlanChangeRequest::where('status', 'pending')->count(),
            'contacted' => \App\Models\PlanChangeRequest::where('status', 'contacted')->count(),
            'approved'  => \App\Models\PlanChangeRequest::where('status', 'approved')->count(),
            'rejected'  => \App\Models\PlanChangeRequest::where('status', 'rejected')->count(),
            'cancelled' => \App\Models\PlanChangeRequest::where('status', 'cancelled')->count(),
        ];

        return view('admin.plan_requests.index', compact('requests', 'counts'));
    }

    public function planRequestMarkContacted(\App\Models\PlanChangeRequest $planChangeRequest)
    {
        $planChangeRequest->update([
            'status'             => 'contacted',
            'actioned_at'        => now(),
            'actioned_by_user_id'=> auth()->id(),
        ]);
        return back()->with('success', 'Marked as contacted.');
    }

    public function planRequestApprove(\Illuminate\Http\Request $request, \App\Models\PlanChangeRequest $planChangeRequest)
    {
        $data = $request->validate(['admin_notes' => 'nullable|string|max:1000']);

        // Apply the plan change to the partner
        $partner = \App\Models\User::find($planChangeRequest->partner_id);
        $oldPlan = $partner?->partner_plan ?? '—';
        \App\Models\User::where('id', $planChangeRequest->partner_id)->update([
            'partner_plan' => $planChangeRequest->requested_plan,
        ]);
        $planChangeRequest->update([
            'status'              => 'approved',
            'admin_notes'         => $data['admin_notes'] ?? null,
            'actioned_at'         => now(),
            'actioned_by_user_id' => auth()->id(),
        ]);

        // Branded confirmation email to the partner
        if ($partner && $partner->email) {
            try {
                $planRow = \App\Models\PartnerPlan::where('name', $planChangeRequest->requested_plan)->first();
                \Illuminate\Support\Facades\Mail::send('partner.email_plan_approved', [
                    'partner'     => $partner,
                    'oldPlan'     => $oldPlan,
                    'newPlan'     => $planChangeRequest->requested_plan,
                    'monthlyCap'  => $planRow?->monthly_submission_limit,
                    'maxTeam'     => $planRow?->max_team_members ?? 1,
                    'premiumJobs' => (bool) ($planRow?->can_view_premium_jobs),
                    'adminNotes'  => $data['admin_notes'] ?? null,
                ], function ($m) use ($partner, $planChangeRequest) {
                    $m->to($partner->email, $partner->name)
                      ->subject('🎉 Your plan has been upgraded to ' . $planChangeRequest->requested_plan . ' — SimplyHiree');
                });
            } catch (\Throwable $e) {
                \Log::warning('Plan approval email failed for partner ' . $partner->id . ': ' . $e->getMessage());
            }
        }

        return back()->with('success', "Plan changed to {$planChangeRequest->requested_plan} for {$partner?->name}. Confirmation email sent.");
    }

    public function planRequestReject(\Illuminate\Http\Request $request, \App\Models\PlanChangeRequest $planChangeRequest)
    {
        $data = $request->validate(['admin_notes' => 'nullable|string|max:1000']);
        $planChangeRequest->update([
            'status'              => 'rejected',
            'admin_notes'         => $data['admin_notes'] ?? null,
            'actioned_at'         => now(),
            'actioned_by_user_id' => auth()->id(),
        ]);
        return back()->with('success', 'Plan request rejected.');
    }

    // --- REPLACEMENT LIFECYCLE ---

    public function replacementsIndex(\Illuminate\Http\Request $request)
    {
        $tracked = JobApplication::query()
            ->whereNotNull('joining_date')
            ->with(['job.user', 'candidate.partner', 'candidateUser', 'partnerCreditNote', 'replacementApplication.candidate'])
            ->get()
            ->filter(function (JobApplication $application) {
                $days = (int) ($application->replacement_window_days ?? $application->job?->replacement_guarantee_days ?? 0);
                return $days > 0 || $application->replacement_requested_at || filled($application->replacement_status);
            })
            ->map(function (JobApplication $application) {
                $days = (int) ($application->replacement_window_days ?? $application->job?->replacement_guarantee_days ?? 0);
                $guaranteeDeadline = $application->joining_date?->copy()->addDays($days);
                $remaining = $guaranteeDeadline ? max(0, now()->startOfDay()->diffInDays($guaranteeDeadline->copy()->startOfDay(), false)) : 0;
                $raw = (string) $application->replacement_status;
                $status = match (true) {
                    $raw === 'closed' => 'closed',
                    in_array($raw, ['in_progress', 'replacement_given'], true) => 'in_progress',
                    in_array($raw, ['window_open', 'credit_pending'], true) || $application->replacement_requested_at !== null => 'pending',
                    $remaining > 0 && $application->joined_status === 'Joined' => 'under_guarantee',
                    default => 'expired',
                };
                $application->setAttribute('monitor_status', $status);
                $application->setAttribute('guarantee_deadline_at', $guaranteeDeadline);
                $application->setAttribute('guarantee_days_remaining', $remaining);
                $application->setAttribute('replacement_cost_adjustment', (float) ($application->partnerCreditNote?->amount ?? 0));
                return $application;
            });

        $counts = [
            'under_guarantee' => $tracked->where('monitor_status', 'under_guarantee')->count(),
            'pending' => $tracked->where('monitor_status', 'pending')->count(),
            'in_progress' => $tracked->where('monitor_status', 'in_progress')->count(),
            'closed' => $tracked->where('monitor_status', 'closed')->count(),
            'expired' => $tracked->where('monitor_status', 'expired')->count(),
        ];
        $costSummary = [
            'pending' => (float) \App\Models\PartnerCreditNote::where('status', 'pending')->sum('amount'),
            'applied' => (float) \App\Models\PartnerCreditNote::where('status', 'applied')->sum('amount'),
        ];
        if ($request->filled('status')) {
            $tracked = $tracked->where('monitor_status', $request->status);
        }
        $tracked = $tracked->sortByDesc(fn ($application) => $application->replacement_requested_at ?? $application->joining_date)->values();
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 25;
        $apps = new \Illuminate\Pagination\LengthAwarePaginator(
            $tracked->forPage($page, $perPage)->values(), $tracked->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.replacements.index', compact('apps', 'counts', 'costSummary'));
    }

    public function replacementCandidateForm(JobApplication $application)
    {
        $application->load(['job', 'candidate.partner', 'replacementApplication.candidate']);
        $partnerId = $application->candidate?->partner_id;
        abort_unless($application->replacement_requested_at && $partnerId, 404);

        $candidates = Candidate::where('partner_id', $partnerId)
            ->whereKeyNot($application->candidate_id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        return view('admin.replacements.submit', compact('application', 'candidates'));
    }

    public function replacementCandidateStore(\Illuminate\Http\Request $request, JobApplication $application)
    {
        $application->load(['job', 'candidate']);
        $partnerId = $application->candidate?->partner_id;
        abort_unless($application->replacement_requested_at && $partnerId && $application->job, 404);

        $data = $request->validate([
            'candidate_id' => 'required|integer|exists:candidates,id',
            'interview_at' => $application->job->screening_required ? 'nullable|date' : 'required|date|after:now',
        ]);
        $candidate = Candidate::whereKey($data['candidate_id'])->where('partner_id', $partnerId)->firstOrFail();

        $replacement = JobApplication::firstOrCreate(
            ['job_id' => $application->job_id, 'candidate_id' => $candidate->id],
            [
                'status' => $application->job->screening_required ? 'Pending Review' : 'Approved',
                'hiring_status' => $application->job->screening_required ? null : 'Interview Scheduled',
                'interview_at' => $application->job->screening_required ? null : Carbon::parse($data['interview_at']),
                'submitted_by_user_id' => auth()->id(),
            ]
        );
        if ($replacement->id === $application->id) {
            return back()->with('error', 'The failed hire cannot replace themselves.');
        }

        $application->update([
            'replacement_status' => 'in_progress',
            'replacement_application_id' => $replacement->id,
        ]);
        $replacement->update(['replacement_status' => 'replacement_given']);

        return redirect()->route('admin.replacements.index')->with('success', 'Replacement candidate nominated and linked to the case.');
    }

    /**
     * Link an application as the replacement for a failed hire.
     * Guards: same candidate cannot be its own replacement; the replacement
     * application must belong to the same job and same partner.
     */
    public function replacementsApprove(\Illuminate\Http\Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'replacement_application_id' => 'required|integer|exists:job_applications,id',
        ]);
        $replacementId = (int) $data['replacement_application_id'];

        if ($replacementId === (int) $application->id) {
            return back()->with('error', 'A candidate cannot be their own replacement.');
        }

        $repl = JobApplication::with('candidate')->find($replacementId);
        if (!$repl) return back()->with('error', 'Replacement application not found.');

        // Same job + same partner
        if ($repl->job_id !== $application->job_id) {
            return back()->with('error', 'Replacement must be for the same job.');
        }
        $srcPartnerId = $application->candidate?->partner_id;
        $replPartnerId = $repl->candidate?->partner_id;
        if (!$srcPartnerId || $srcPartnerId !== $replPartnerId) {
            return back()->with('error', 'Replacement must come from the same sourcing partner.');
        }
        // Same candidate ID guard
        if ($repl->candidate_id && $application->candidate_id && $repl->candidate_id === $application->candidate_id) {
            return back()->with('error', 'The replacement cannot be the same candidate as the failed hire.');
        }

        $application->update([
            'replacement_status'         => 'in_progress',
            'replacement_application_id' => $repl->id,
        ]);
        // The new application becomes the active hire — tag it for traceability.
        $repl->update(['replacement_status' => 'replacement_given']);

        return back()->with('success', "Replacement linked. Case for application #{$application->id} is now in progress until final closure.");
    }

    public function replacementsClose(JobApplication $application)
    {
        $application->update(['replacement_status' => 'closed']);
        return back()->with('success', 'Case manually closed.');
    }

    public function replacementCostAdjustment(\Illuminate\Http\Request $request, JobApplication $application)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'reason' => ['required', 'string', 'max:1000'],
        ]);
        $partner = $application->candidate?->partner;
        if (!$partner) return back()->with('error', 'This replacement case has no sourcing partner.');

        \App\Models\PartnerCreditNote::updateOrCreate(
            ['source_application_id' => $application->id],
            ['partner_id' => $partner->id, 'amount' => $data['amount'], 'status' => 'pending', 'reason' => $data['reason']]
        );

        return back()->with('success', 'Replacement cost adjustment saved as a pending vendor payout deduction.');
    }

    /**
     * ============================================================
     * Vendor Assignment Requests (admin side)
     * ============================================================
     */
    public function vendorAssignmentRequestsIndex(Request $request)
    {
        $query = \App\Models\ClientVendorAssignmentRequest::with(['client', 'fulfilledBy']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $requests = $query->latest()->paginate(15)->withQueryString();

        $counts = [
            'pending'     => \App\Models\ClientVendorAssignmentRequest::where('status', 'pending')->count(),
            'in_progress' => \App\Models\ClientVendorAssignmentRequest::where('status', 'in_progress')->count(),
            'fulfilled'   => \App\Models\ClientVendorAssignmentRequest::where('status', 'fulfilled')->count(),
            'cancelled'   => \App\Models\ClientVendorAssignmentRequest::where('status', 'cancelled')->count(),
        ];

        return view('admin.vendor_assignment_requests.index', compact('requests', 'counts'));
    }

    public function vendorAssignmentRequestShow(\App\Models\ClientVendorAssignmentRequest $assignmentRequest)
    {
        $assignmentRequest->load(['client.preferredVendors', 'fulfilledBy']);

        // Suggest vendors that aren't already attached to this client
        $existingPartnerIds = $assignmentRequest->client
            ? $assignmentRequest->client->preferredVendors()->pluck('users.id')->all()
            : [];

        $eligibleVendors = User::role('partner')
            ->whereNull('parent_partner_id')
            ->where('status', 'active')
            ->whereNotIn('id', $existingPartnerIds)
            ->with('partnerProfile')
            ->orderByDesc('avg_rating')
            ->orderByDesc('total_ratings')
            ->get(['id', 'name', 'email', 'avg_rating', 'vendor_level', 'vendor_badge', 'total_ratings']);

        return view('admin.vendor_assignment_requests.show', compact('assignmentRequest', 'eligibleVendors', 'existingPartnerIds'));
    }

    public function vendorAssignmentRequestFulfill(Request $request, \App\Models\ClientVendorAssignmentRequest $assignmentRequest)
    {
        $validated = $request->validate([
            'partner_ids'   => 'required|array|min:1',
            'partner_ids.*' => 'integer|exists:users,id',
            'admin_notes'   => 'nullable|string|max:2000',
        ]);

        $client = User::find($assignmentRequest->client_id);
        if (!$client) {
            return back()->with('error', 'Client account not found.');
        }

        // Attach each partner to the client's preferred-vendors list (idempotent)
        $payload = [];
        foreach ($validated['partner_ids'] as $pid) {
            $payload[(int) $pid] = ['added_at' => now()];
        }
        $client->preferredVendors()->syncWithoutDetaching($payload);

        $assignmentRequest->update([
            'status'               => 'fulfilled',
            'admin_notes'          => $validated['admin_notes'] ?? null,
            'fulfilled_by_user_id' => Auth::id(),
            'fulfilled_at'         => now(),
        ]);

        return redirect()->route('admin.vendor-assignment-requests.index')
            ->with('success', count($validated['partner_ids']) . ' vendor(s) assigned to ' . $client->name . '.');
    }

    public function vendorAssignmentRequestCancel(\App\Models\ClientVendorAssignmentRequest $assignmentRequest)
    {
        $assignmentRequest->update([
            'status'               => 'cancelled',
            'fulfilled_by_user_id' => Auth::id(),
            'fulfilled_at'         => now(),
        ]);
        return back()->with('success', 'Request cancelled.');
    }

    // --- CREDIT NOTES ---

    public function creditNotesIndex(\Illuminate\Http\Request $request)
    {
        $query = \App\Models\PartnerCreditNote::with(['partner', 'sourceApplication.job', 'sourceApplication.candidate']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('partner_id')) {
            $query->where('partner_id', (int) $request->partner_id);
        }
        $notes = $query->latest()->paginate(25)->withQueryString();

        $counts = [
            'pending'   => \App\Models\PartnerCreditNote::where('status', 'pending')->count(),
            'applied'   => \App\Models\PartnerCreditNote::where('status', 'applied')->count(),
            'cancelled' => \App\Models\PartnerCreditNote::where('status', 'cancelled')->count(),
        ];

        $partners = \App\Models\User::role('partner')
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id','name']);

        return view('admin.credit_notes.index', compact('notes', 'counts', 'partners'));
    }

    public function creditNotesIssue(JobApplication $application)
    {
        $partner = $application->candidate?->partner;
        if (!$partner) return back()->with('error', 'This application has no sourcing partner.');
        $amount = (float) ($application->job?->payout_amount ?? 0);
        if ($amount <= 0) return back()->with('error', 'Source job has no payout amount; nothing to credit.');

        \App\Models\PartnerCreditNote::updateOrCreate(
            ['source_application_id' => $application->id],
            [
                'partner_id' => $partner->id,
                'amount'     => $amount,
                'status'     => 'pending',
                'reason'     => 'Manually issued by admin.',
            ]
        );
        $application->update(['replacement_status' => 'credit_pending']);
        return back()->with('success', 'Credit note issued.');
    }

    public function creditNotesApply(\App\Models\PartnerCreditNote $creditNote)
    {
        $creditNote->update(['status' => 'applied', 'applied_at' => now()]);
        return back()->with('success', "Credit note #{$creditNote->id} marked as applied.");
    }

    public function creditNotesCancel(\App\Models\PartnerCreditNote $creditNote)
    {
        $creditNote->update(['status' => 'cancelled']);
        return back()->with('success', "Credit note #{$creditNote->id} cancelled.");
    }

    public function markInvoiceRaised(JobApplication $application)
    {
        // Lock in everything resolvable at raise-time so the contract can
        // be edited later without rewriting history.
        $stamp = ['invoice_generated_at' => now()];
        $cb = $application->resolveCommercial();
        if ($cb) {
            if (!$application->invoice_amount && $cb['invoice_amount'] > 0) {
                $stamp['invoice_amount'] = $cb['invoice_amount'];
            }
            if ($application->replacement_window_days === null && $cb['replacement_days'] !== null) {
                $stamp['replacement_window_days'] = $cb['replacement_days'];
            }
        }
        $application->update($stamp);
        return redirect()->back()->with('success', 'Invoice marked as RAISED.');
    }

    public function jobReport(Request $request)
    {
        $query = Job::with(['user', 'allowedPartners', 'jobApplications.candidate.partner', 'jobApplications.candidateUser'])
            ->whereNull('archived_at')
            ->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('company_name', 'like', "%{$searchTerm}%");
            });
        }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('client_id')) $query->where('user_id', $request->client_id);

        $jobs = $query->paginate(20)->appends($request->query());
        $clients = User::role('client')->orderBy('name')->get();

        return view('admin.reports.jobs', ['jobs' => $jobs, 'clients' => $clients]);
    }

    public function exportJobReport(Request $request)
    {
        $query = Job::with(['user', 'jobApplications'])
            ->whereNull('archived_at')
            ->latest();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('company_name', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('client_id')) {
            $query->where('user_id', $request->client_id);
        }

        $jobs = $query->get();
        $fileName = 'master_job_report_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($jobs) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Job Code',
                'Internal ID',
                'Job Title',
                'Company',
                'Client',
                'Status',
                'Applicants',
                'Joined',
                'Posted Date',
            ]);

            foreach ($jobs as $job) {
                $applicationsCount = $job->jobApplications->count();
                $joinedCount = $job->jobApplications->where('joined_status', 'Joined')->count();

                fputcsv($handle, [
                    $job->job_code ?? ('SH-JOB-' . str_pad((string) $job->id, 6, '0', STR_PAD_LEFT)),
                    $job->id,
                    $job->title,
                    $job->company_name,
                    optional($job->user)->name ?? '',
                    $job->status,
                    $applicationsCount,
                    $joinedCount,
                    optional($job->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Format salary min/max into a readable string (mirrors ClientController).
     */
    private function formatSalaryRange(?int $minSalary, ?int $maxSalary): ?string
    {
        if ($minSalary === null && $maxSalary === null) {
            return null;
        }
        if ($minSalary !== null && $maxSalary !== null) {
            if ($minSalary === $maxSalary) {
                return 'Rs. ' . number_format($minSalary);
            }
            return 'Rs. ' . number_format($minSalary) . ' - Rs. ' . number_format($maxSalary);
        }
        if ($minSalary !== null) {
            return 'Rs. ' . number_format($minSalary) . '+';
        }
        return 'Up to Rs. ' . number_format((int) $maxSalary);
    }

    /**
     * Sanitize Quill editor HTML — keep formatting tags, strip scripts and event handlers.
     */
    private function sanitizeJobDescription(?string $html): ?string
    {
        if (!$html) return $html;
        $allowed = '<p><br><b><strong><i><em><u><s><strike><ul><ol><li><h2><h3><blockquote><a><span>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*"(?:[^"\\\\]|\\\\.)*"/i', '', $clean);
        $clean = preg_replace("/\s+on[a-z]+\s*=\s*'(?:[^'\\\\]|\\\\.)*'/i", '', $clean);
        $clean = preg_replace('/href\s*=\s*"\s*javascript:[^"]*"/i', 'href="#"', $clean);
        $clean = preg_replace("/href\s*=\s*'\s*javascript:[^']*'/i", "href='#'", $clean);
        return $clean;
    }
}
