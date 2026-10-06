<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientReferral;
use App\Models\ClientReferralCommissionRule;
use App\Models\JobApplication;
use App\Models\ReferralCommissionLedger;
use App\Models\ReferralLead;
use App\Models\ReferralPartnerProfile;
use App\Models\ReferralWithdrawalRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class ReferralAdminController extends Controller
{
    public function index()
    {
        $referralClientIds = ClientReferral::where('status', 'active')->pluck('client_id');
        $ledgerQuery = ReferralCommissionLedger::query();
        $invoiceTracker = (clone $ledgerQuery)
            ->selectRaw("DATE_FORMAT(earned_at, '%Y-%m') as month_key, COUNT(*) as invoice_count, SUM(revenue_amount) as billing_amount, SUM(commission_amount) as commission_amount")
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->limit(12)
            ->get();

        return view('admin.referrals.index', [
            'profiles' => ReferralPartnerProfile::with('user.profile')->latest()->paginate(20, ['*'], 'profiles'),
            'leads' => ReferralLead::with('referralPartner')->whereIn('status', ['submitted', 'qualified'])->latest()->paginate(15, ['*'], 'leads'),
            'withdrawals' => ReferralWithdrawalRequest::with('referralPartner')->whereIn('status', ['requested','approved'])->latest()->paginate(15, ['*'], 'withdrawals'),
            'referrals' => ClientReferral::with(['client.clientProfile','referralPartner','rule'])->latest()->paginate(20, ['*'], 'referrals'),
            'clients' => User::role('client')->orderBy('name')->get(['id','name','email']),
            'partners' => User::whereHas('referralPartnerProfile', fn ($q) => $q->where('status', 'active'))->orderBy('name')->get(['id','name','email']),
            'paymentCandidates' => JobApplication::with(['job.user'])
                ->where('payment_status', 'paid')
                ->whereNotIn('id', ReferralCommissionLedger::whereNotNull('job_application_id')->pluck('job_application_id'))
                ->whereHas('job', fn ($q) => $q->whereIn('user_id', $referralClientIds))
                ->latest('paid_at')->limit(30)->get(),
            'totalReferralClients' => ClientReferral::count(),
            'activeReferralClients' => ClientReferral::where('status', 'active')->count(),
            'monthlyRevenue' => (float) (clone $ledgerQuery)
                ->whereBetween('earned_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->sum('revenue_amount'),
            'monthlyCommission' => (float) (clone $ledgerQuery)
                ->whereBetween('earned_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->sum('commission_amount'),
            'invoiceTracker' => $invoiceTracker,
        ]);
    }

    public function approveProfile(ReferralPartnerProfile $profile)
    {
        $profile->loadMissing('user');
        abort_unless($profile->user, 422, 'This referral profile has no linked user account.');

        DB::transaction(function () use ($profile) {
            $profile->update([
                'status' => 'active',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
            $profile->user->update(['status' => 'active']);
            $profile->user->assignRole('referral_partner');
        });

        $dashboardUrl = route('referral.dashboard');
        $referralUrl = url('/register/client?ref='.$profile->referral_code);
        try {
            Mail::send('emails.referral_partner_approved', [
                'user' => $profile->user,
                'profile' => $profile,
                'dashboardUrl' => $dashboardUrl,
                'referralUrl' => $referralUrl,
                'passwordResetUrl' => route('password.request'),
            ], function ($mail) use ($profile) {
                $mail->to($profile->user->email, $profile->user->name)
                    ->subject('Welcome to the SimplyHiree Referral Partner Program');
            });
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Referral partner activated and welcome email sent.');
    }

    public function reviewLead(Request $request, ReferralLead $lead)
    {
        $data = $request->validate(['status' => ['required','in:qualified,duplicate,rejected'], 'admin_notes' => ['nullable','string','max:2000']]);
        $lead->update([...$data, 'reviewed_by' => auth()->id(), 'reviewed_at' => now()]);
        return back()->with('success', 'Lead review recorded.');
    }

    public function linkClient(Request $request)
    {
        $data = $request->validate([
            'client_id' => ['required','exists:users,id'], 'referral_partner_id' => ['required','exists:users,id'],
            'commission_type' => ['required','in:percentage_net_revenue,flat_per_closure,recurring_percentage,recurring_flat'],
            'commission_value' => ['required','numeric','min:0'],
        ]);
        $profile = ReferralPartnerProfile::where('user_id', $data['referral_partner_id'])->where('status', 'active')->firstOrFail();
        DB::transaction(function () use ($data, $profile) {
            $referral = ClientReferral::updateOrCreate(['client_id' => $data['client_id']], [
                'referral_partner_id' => $data['referral_partner_id'], 'referral_code_snapshot' => $profile->referral_code,
                'status' => 'active', 'approved_by' => auth()->id(), 'approved_at' => now(),
            ]);
            ClientReferralCommissionRule::updateOrCreate(['client_referral_id' => $referral->id], [
                'commission_type' => $data['commission_type'], 'commission_value' => $data['commission_value'],
                'effective_from' => today(), 'is_active' => true, 'configured_by' => auth()->id(),
            ]);
        });
        return back()->with('success', 'Client attribution and commission rule saved.');
    }

    public function linkLeadToClient(Request $request, ReferralLead $lead)
    {
        abort_unless($lead->status === 'qualified', 422, 'Only a qualified lead can be converted to a client.');

        $data = $request->validate([
            'client_id' => ['required', 'exists:users,id'],
            'commission_type' => ['required', 'in:percentage_net_revenue,flat_per_closure,recurring_percentage,recurring_flat'],
            'commission_value' => ['required', 'numeric', 'min:0'],
        ]);

        $profile = ReferralPartnerProfile::where('user_id', $lead->referral_partner_id)
            ->where('status', 'active')
            ->firstOrFail();
        abort_unless(User::role('client')->whereKey($data['client_id'])->exists(), 422, 'The selected account is not a client.');

        DB::transaction(function () use ($data, $lead, $profile) {
            $existing = ClientReferral::where('client_id', $data['client_id'])->lockForUpdate()->first();
            abort_if(
                $existing && $existing->referral_partner_id !== $lead->referral_partner_id,
                422,
                'This client is already linked to another referral partner.'
            );

            $referral = ClientReferral::updateOrCreate(['client_id' => $data['client_id']], [
                'referral_partner_id' => $lead->referral_partner_id,
                'referral_lead_id' => $lead->id,
                'referral_code_snapshot' => $profile->referral_code,
                'status' => 'active',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            ClientReferralCommissionRule::updateOrCreate(['client_referral_id' => $referral->id], [
                'commission_type' => $data['commission_type'],
                'commission_value' => $data['commission_value'],
                'effective_from' => today(),
                'is_active' => true,
                'configured_by' => auth()->id(),
            ]);

            $lead->update([
                'status' => 'converted',
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        });

        return redirect()->route('admin.referrals.index', ['ref_tab' => 'clients'])
            ->with('success', 'Qualified lead linked to the client and commission rule activated.');
    }

    public function updateClientStatus(Request $request, ClientReferral $referral)
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,closed,lost'],
        ]);

        $referral->update($data);

        return back()->with('success', 'Referred client status updated.');
    }

    public function verifyPayment(Request $request, JobApplication $application)
    {
        $data = $request->validate(['received_amount' => ['required','numeric','min:0.01'], 'notes' => ['nullable','string','max:1000']]);
        $application->load('job');
        $referral = ClientReferral::with('rule')->where('client_id', $application->job->user_id)->where('status', 'active')->firstOrFail();
        $rule = $referral->rule;
        abort_unless($rule?->is_active, 422, 'No active referral commission rule exists for this client.');
        $amount = str_contains($rule->commission_type, 'percentage')
            ? round($data['received_amount'] * ((float) $rule->commission_value / 100), 2)
            : (float) $rule->commission_value;
        ReferralCommissionLedger::firstOrCreate([
            'client_referral_id' => $referral->id, 'job_application_id' => $application->id, 'entry_type' => 'commission',
        ], [
            'referral_partner_id' => $referral->referral_partner_id, 'status' => 'available', 'revenue_amount' => $data['received_amount'],
            'commission_amount' => $amount, 'commission_type_snapshot' => $rule->commission_type,
            'commission_value_snapshot' => $rule->commission_value, 'earned_at' => now(), 'verified_by' => auth()->id(), 'notes' => $data['notes'] ?? null,
        ]);
        return back()->with('success', 'Finance-confirmed payment recorded and referral commission added to the ledger.');
    }

    public function updateWithdrawal(Request $request, ReferralWithdrawalRequest $withdrawal)
    {
        $data = $request->validate(['status' => ['required','in:approved,rejected,paid'], 'payment_reference' => ['nullable','string','max:255'], 'admin_notes' => ['nullable','string','max:2000']]);
        $withdrawal->update([...$data, 'reviewed_by' => auth()->id(), 'reviewed_at' => now(), 'paid_at' => $data['status'] === 'paid' ? now() : null]);
        return back()->with('success', 'Withdrawal status updated.');
    }
}
