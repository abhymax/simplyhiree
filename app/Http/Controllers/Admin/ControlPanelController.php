<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Candidate;
use App\Models\ClientProfile;
use App\Models\JobApplication;
use App\Models\PartnerCreditNote;
use App\Models\ReferralCommissionLedger;
use App\Models\User;
use App\Models\UserProfile;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Services\SuperadminActivityService;
use App\Services\InvoiceDocumentService;

class ControlPanelController extends Controller
{
    public function analytics()
    {
        $applications = JobApplication::with(['job.user.clientProfile', 'candidate.partner'])->get();
        $submitted = $applications->count();
        $interviewed = $applications->filter(fn ($app) => $app->interview_at || in_array($app->hiring_status, ['Interview Scheduled', 'Interviewed', 'Selected'], true) || $app->joined_status === 'Joined')->count();
        $joined = $applications->where('joined_status', 'Joined')->count();
        $ratio = fn (int $part, int $whole) => $whole > 0 ? round(($part / $whole) * 100, 1) : 0;

        $vendors = User::role('partner')->whereNull('parent_partner_id')->get()->map(function (User $vendor) use ($applications, $ratio) {
            $rows = $applications->filter(fn ($app) => (int) $app->candidate?->partner_id === (int) $vendor->id);
            $submissions = $rows->count();
            $interviews = $rows->filter(fn ($app) => $app->interview_at || in_array($app->hiring_status, ['Interview Scheduled', 'Interviewed', 'Selected'], true))->count();
            $selections = $rows->where('hiring_status', 'Selected')->count();
            $joinings = $rows->where('joined_status', 'Joined')->count();
            $score = round(min(100, (($vendor->avg_rating ?? 0) / 5 * 40) + ($ratio($selections, $submissions) * .3) + ($ratio($joinings, max(1, $selections)) * .3)));
            return compact('vendor', 'submissions', 'interviews', 'selections', 'joinings', 'score');
        })->sortByDesc('score')->values();

        $managers = User::role('Manager')->with('assignedClients')->get()->map(function (User $manager) use ($applications) {
            $clientIds = $manager->assignedClients->pluck('id');
            $rows = $applications->filter(fn ($app) => $clientIds->contains($app->job?->user_id));
            return [
                'manager' => $manager, 'clients' => $clientIds->count(),
                'submissions' => $rows->count(), 'joinings' => $rows->where('joined_status', 'Joined')->count(),
                'revenue' => (float) $rows->where('payment_status', 'Paid')->sum(fn ($app) => (float) ($app->billingSnapshot()['invoice_amount'] ?? 0)),
            ];
        })->sortByDesc('revenue')->values();

        $clientSpeed = $applications->where('joined_status', 'Joined')->filter(fn ($app) => $app->job?->user)->groupBy(fn ($app) => $app->job->user_id)->map(function (Collection $rows) {
            return [
                'client' => $rows->first()->job->user,
                'industry' => $rows->first()->job->user->clientProfile?->industry ?: 'Unspecified',
                'hires' => $rows->count(),
                'days' => round($rows->avg(fn ($app) => $app->created_at->diffInDays($app->joining_date)), 1),
            ];
        })->sortBy('days')->values();

        $paid = $applications->where('payment_status', 'Paid');
        $industryRevenue = $paid->groupBy(fn ($app) => $app->job?->user?->clientProfile?->industry ?: 'Unspecified')->map(fn (Collection $rows, string $industry) => [
            'industry' => $industry, 'placements' => $rows->count(),
            'revenue' => (float) $rows->sum(fn ($app) => (float) ($app->billingSnapshot()['invoice_amount'] ?? 0)),
        ])->sortByDesc('revenue')->values();

        $months = collect(range(11, 0))->map(function (int $offset) use ($applications) {
            $month = now()->startOfMonth()->subMonths($offset);
            $inMonth = fn ($date) => $date && $date->betweenIncluded($month->copy()->startOfMonth(), $month->copy()->endOfMonth());
            $paid = $applications->filter(fn ($app) => $inMonth($app->paid_at));
            return [
                'label' => $month->format('M y'),
                'submissions' => $applications->filter(fn ($app) => $inMonth($app->created_at))->count(),
                'interviews' => $applications->filter(fn ($app) => $inMonth($app->interview_at))->count(),
                'joinings' => $applications->filter(fn ($app) => $inMonth($app->joining_date) && $app->joined_status === 'Joined')->count(),
                'revenue' => (float) $paid->sum(fn ($app) => (float) ($app->billingSnapshot()['invoice_amount'] ?? 0)),
            ];
        });

        return view('admin.control_panels.analytics', [
            'conversion' => ['submitted' => $submitted, 'interviewed' => $interviewed, 'joined' => $joined, 'submission_interview' => $ratio($interviewed, $submitted), 'interview_joining' => $ratio($joined, $interviewed), 'submission_joining' => $ratio($joined, $submitted)],
            'vendors' => $vendors, 'managers' => $managers, 'clientSpeed' => $clientSpeed,
            'industryRevenue' => $industryRevenue, 'months' => $months,
        ]);
    }

    public function revenue()
    {
        $applications = JobApplication::where('hiring_status', 'Selected')
            ->whereNotNull('joining_date')
            ->with(['job.user.clientProfile', 'candidate.partner', 'candidateUser'])
            ->latest('joining_date')
            ->get();
        $billing = $applications->map(fn (JobApplication $application) => $application->billingSnapshot());
        $billingPageNumber = max(1, (int) request()->input('billing_page', 1));
        $billingPage = new \Illuminate\Pagination\LengthAwarePaginator(
            $billing->forPage($billingPageNumber, 8)->values(),
            $billing->count(),
            8,
            $billingPageNumber,
            ['path' => request()->url(), 'query' => request()->query(), 'pageName' => 'billing_page']
        );

        $vendorPlacements = $applications
            ->filter(fn (JobApplication $application) => $application->joined_status === 'Joined' && $application->candidate?->partner)
            ->map(function (JobApplication $application) {
                return [
                    'application' => $application,
                    'partner' => $application->candidate->partner,
                    'gross' => (float) ($application->job?->payout_amount ?? 0),
                ];
            });
        $creditNotes = PartnerCreditNote::with('partner')->whereIn('status', ['pending', 'applied'])->get();
        $vendorGross = (float) $vendorPlacements->sum('gross');
        $deductionsPending = (float) $creditNotes->where('status', 'pending')->sum('amount');
        $deductionsApplied = (float) $creditNotes->where('status', 'applied')->sum('amount');
        $vendorSummary = $vendorPlacements->groupBy(fn ($row) => $row['partner']->id)->map(function (Collection $rows, int $partnerId) use ($creditNotes) {
            $gross = (float) $rows->sum('gross');
            $pending = (float) $creditNotes->where('partner_id', $partnerId)->where('status', 'pending')->sum('amount');
            $applied = (float) $creditNotes->where('partner_id', $partnerId)->where('status', 'applied')->sum('amount');
            return ['partner' => $rows->first()['partner'], 'placements' => $rows->count(), 'gross' => $gross, 'pending' => $pending, 'applied' => $applied, 'net' => max(0, $gross - $applied)];
        })->sortByDesc('gross')->values();

        $referralLedger = ReferralCommissionLedger::with(['referralPartner', 'clientReferral.client'])
            ->latest('earned_at')
            ->limit(100)
            ->get();

        return view('admin.control_panels.revenue', [
            'billing' => $billing,
            'billingPage' => $billingPage,
            'vendorPlacements' => $vendorPlacements->take(50),
            'vendorSummary' => $vendorSummary,
            'creditNotes' => $creditNotes,
            'referralLedger' => $referralLedger,
            'baseBilling' => (float) $billing->sum('invoice_amount'),
            'gstTotal' => (float) $billing->sum('gst_amount'),
            'billingWithGst' => (float) $billing->sum('invoice_total'),
            'outstanding' => (float) $billing->whereIn('status', ['Due to Raise', 'Raised', 'Overdue'])->sum('invoice_total'),
            'vendorGross' => $vendorGross,
            'deductionsPending' => $deductionsPending,
            'deductionsApplied' => $deductionsApplied,
            'vendorNet' => max(0, $vendorGross - $deductionsApplied),
            'referralCommission' => (float) $referralLedger->sum('commission_amount'),
            'manualRevenueEntries' => \App\Models\ManualRevenueEntry::with(['client', 'creator'])->latest('recognized_on')->limit(25)->get(),
            'revenueClients' => User::role('client')->orderBy('name')->get(),
        ]);
    }

    public function risk()
    {
        $duplicateResumes = Cache::remember('risk.duplicate_resumes.v1', now()->addHour(), fn () => $this->duplicateResumes());
        $duplicateClients = $this->duplicateClients();
        $duplicateCandidateEmails = Candidate::query()
            ->selectRaw('LOWER(TRIM(email)) as identity_value, COUNT(*) as total, COUNT(DISTINCT partner_id) as partner_count')
            ->whereNotNull('email')->where('email', '!=', '')
            ->groupBy('identity_value')->havingRaw('COUNT(*) > 1')->orderByDesc('total')->limit(50)->get();
        $duplicateCandidatePhones = Candidate::query()
            ->selectRaw("REPLACE(REPLACE(REPLACE(phone_number, ' ', ''), '-', ''), '+91', '') as identity_value, COUNT(*) as total, COUNT(DISTINCT partner_id) as partner_count")
            ->whereNotNull('phone_number')->where('phone_number', '!=', '')
            ->groupBy('identity_value')->havingRaw('COUNT(*) > 1')->orderByDesc('total')->limit(50)->get();
        $restrictedVendors = User::role('partner')->where('status', 'restricted')->orderBy('name')->get();
        $penalizedVendors = User::role('partner')->where('penalty_active', true)->orderBy('name')->get();
        $sharedIps = AdminActivityLog::query()
            ->selectRaw('ip_address, COUNT(*) as event_count, COUNT(DISTINCT actor_id) as actor_count')
            ->whereNotNull('ip_address')
            ->groupBy('ip_address')
            ->havingRaw('COUNT(DISTINCT actor_id) > 1')
            ->orderByDesc('event_count')
            ->limit(50)
            ->get();
        $recentIpLogs = AdminActivityLog::whereNotNull('ip_address')->latest('occurred_at')->limit(100)->get();
        $pendingCandidateReviews = Candidate::with(['partner', 'duplicateOf.partner'])
            ->where('duplicate_status', 'pending_review')
            ->latest()
            ->get();

        return view('admin.control_panels.risk', compact(
            'duplicateResumes', 'duplicateClients', 'duplicateCandidateEmails', 'duplicateCandidatePhones',
            'restrictedVendors', 'penalizedVendors', 'sharedIps', 'recentIpLogs', 'pendingCandidateReviews'
        ));
    }

    public function reviewCandidate(Request $request, Candidate $candidate, SuperadminActivityService $activityService)
    {
        $data = $request->validate([
            'decision' => ['required', 'in:genuine,duplicate,fraud'],
            'review_notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $candidate->update([
            'duplicate_status' => $data['decision'],
            'duplicate_reviewed_by' => auth()->id(),
            'duplicate_reviewed_at' => now(),
        ]);

        if ($data['decision'] === 'fraud' && $candidate->partner) {
            $candidate->partner->update([
                'status' => 'restricted',
                'penalty_active' => true,
                'penalty_reason' => 'Confirmed duplicate candidate fraud: candidate #'.$candidate->id,
            ]);
        }

        $activityService->logEvent(
            'risk.duplicate_reviewed',
            'Duplicate candidate reviewed',
            'Candidate #'.$candidate->id.' classified as '.$data['decision'].'.',
            $data['decision'] === 'genuine' ? 'check-circle' : 'triangle-exclamation',
            $candidate,
            ['decision' => $data['decision'], 'notes' => $data['review_notes'] ?? null]
        );
        Cache::forget('risk.duplicate_resumes.v1');

        if ($data['decision'] === 'fraud') {
            return back()->with('success', 'Fraud confirmed. Candidate remains blocked and the vendor has been restricted.');
        }
        if ($data['decision'] !== 'genuine') {
            return back()->with('success', 'Marked duplicate. Candidate stays blocked.');
        }

        // Genuine: complete the submission that was blocked, so it appears in All Applications.
        $job = $candidate->duplicate_blocked_job_id ? \App\Models\Job::find($candidate->duplicate_blocked_job_id) : null;
        if (!$job) {
            return back()->with('success', 'Candidate released. No blocked job on record — the vendor can now submit them to a job.');
        }
        $application = \App\Models\JobApplication::where('job_id', $job->id)->where('candidate_id', $candidate->id)->first();
        if (!$application) {
            $screening = $job->screening_required ?? true;
            $application = \App\Models\JobApplication::create([
                'job_id'               => $job->id,
                'candidate_id'         => $candidate->id,
                'status'               => $screening ? 'Pending Review' : 'Approved',
                'hiring_status'        => $screening ? null : 'Interview Scheduled',
                'submitted_by_user_id' => $candidate->partner_id,
            ]);
            try {
                app(\App\Services\AssessmentSessionService::class)->startForApplication($application);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $candidate->forceFill(['duplicate_blocked_job_id' => null])->save();

        return back()->with('success', 'Candidate released and submitted to "'.$job->title.'" (application '
            .($application->application_code ?? '#'.$application->id).'). It now appears in All Applications.');
    }

    public function invoicePdf(JobApplication $application, InvoiceDocumentService $invoiceDocuments)
    {
        $application->load(['job.user.clientProfile', 'candidate', 'candidateUser']);
        abort_unless($application->hiring_status === 'Selected' && $application->joining_date, 422, 'This application is not billable yet.');
        $invoice = $application->billingSnapshot();
        abort_if((float) ($invoice['invoice_amount'] ?? 0) <= 0, 422, 'Invoice amount is not configured.');

        $document = $invoiceDocuments->build($application, $invoice);
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('invoices.placement-invoice-pdf', compact('document'))->render());
        $dompdf->setPaper('A4');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.str_replace('/', '-', $document['invoiceNumber']).'.pdf"',
        ]);
    }

    private function duplicateResumes(): Collection
    {
        $records = collect();
        Candidate::whereNotNull('resume_path')->where('resume_path', '!=', '')->with('partner')->get()
            ->each(fn (Candidate $candidate) => $records->push([
                'source' => 'Vendor candidate', 'name' => trim($candidate->first_name.' '.$candidate->last_name),
                'owner' => $candidate->partner?->name, 'path' => $candidate->resume_path,
            ]));
        UserProfile::whereNotNull('resume_path')->where('resume_path', '!=', '')->with('user')->get()
            ->each(fn (UserProfile $profile) => $records->push([
                'source' => 'Direct candidate', 'name' => $profile->user?->name,
                'owner' => 'Direct', 'path' => $profile->resume_path,
            ]));

        return $records->map(function (array $record) {
            if (!Storage::disk('public')->exists($record['path'])) return null;
            $record['fingerprint'] = hash_file('sha256', Storage::disk('public')->path($record['path']));
            return $record;
        })->filter()->groupBy('fingerprint')->filter(fn (Collection $group) => $group->count() > 1)->values();
    }

    private function duplicateClients(): Collection
    {
        $profiles = ClientProfile::with('user')->get();
        $matches = collect();
        foreach (['contact_phone' => 'Phone', 'gst_number' => 'GST', 'pan_number' => 'PAN'] as $field => $label) {
            $profiles->filter(fn (ClientProfile $profile) => filled($profile->{$field}))
                ->groupBy(fn (ClientProfile $profile) => strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $profile->{$field})))
                ->filter(fn (Collection $group) => $group->count() > 1)
                ->each(fn (Collection $group, string $value) => $matches->push(['type' => $label, 'value' => $value, 'profiles' => $group]));
        }
        return $matches;
    }
}
