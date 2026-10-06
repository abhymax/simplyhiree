<?php

namespace App\Http\Controllers;

use App\Models\ReferralLead;
use App\Models\ReferralPartnerProfile;
use App\Models\ReferralCommissionLedger;
use App\Models\ReferralWithdrawalRequest;
use App\Models\User;
use App\Notifications\SuperadminActivityNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ReferralPartnerController extends Controller
{
    public function enroll()
    {
        $profile = Auth::user()->referralPartnerProfile;
        return view('referral.enroll', compact('profile'));
    }

    public function storeEnrollment(Request $request)
    {
        $data = $request->validate([
            'partner_type' => ['required', 'in:Individual,Consultant,Freelancer,Ex HR,Sales Person,Channel Partner'],
            'pan_number' => ['nullable', 'string', 'max:20'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_ifsc' => ['nullable', 'string', 'max:20'],
            'agreement_accepted' => ['accepted'],
        ]);
        $user = Auth::user();
        ReferralPartnerProfile::updateOrCreate(['user_id' => $user->id], [
            ...collect($data)->except('agreement_accepted')->all(),
            'referral_code' => $user->referralPartnerProfile?->referral_code ?: 'SHR-'.strtoupper(Str::random(8)),
            'status' => 'pending',
            'agreement_version' => '2026-08-23',
            'agreement_accepted_at' => now(),
            'agreement_accepted_ip' => $request->ip(),
            'agreement_accepted_user_agent' => Str::limit((string) $request->userAgent(), 1000, ''),
            'agreement_acceptance_method' => 'authenticated_checkbox',
        ]);
        return back()->with('success', 'Your referral partner request has been submitted for Superadmin approval.');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $profile = $user->referralPartnerProfile;
        abort_unless($profile?->status === 'active', 403, 'Your referral partner profile is awaiting approval.');

        $referralsQuery = $user->referredClients();
        $totalClients = (clone $referralsQuery)->count();
        $activeClients = (clone $referralsQuery)->where('status', 'active')->count();
        $closedClients = (clone $referralsQuery)->where('status', 'closed')->count();
        $lostClients = (clone $referralsQuery)->where('status', 'lost')->count();
        $referrals = (clone $referralsQuery)
            ->with(['client.clientProfile', 'rule'])
            ->latest()
            ->paginate(15, ['*'], 'clients');

        $ledgerQuery = ReferralCommissionLedger::where('referral_partner_id', $user->id);
        $ledger = (clone $ledgerQuery)
            ->with('clientReferral.client.clientProfile')
            ->latest('earned_at')
            ->paginate(15, ['*'], 'ledger');
        $monthlyRevenue = (float) (clone $ledgerQuery)
            ->whereBetween('earned_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('revenue_amount');
        $invoiceTracker = (clone $ledgerQuery)
            ->selectRaw("DATE_FORMAT(earned_at, '%Y-%m') as month_key, COUNT(*) as invoice_count, SUM(revenue_amount) as billing_amount, SUM(commission_amount) as commission_amount")
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->limit(12)
            ->get();
        $grossAvailable = ReferralCommissionLedger::where('referral_partner_id', $user->id)->where('status', 'available')->sum('commission_amount');
        $reserved = ReferralWithdrawalRequest::where('referral_partner_id', $user->id)->whereIn('status', ['requested', 'approved', 'paid'])->sum('amount');
        $available = max(0, (float) $grossAvailable - (float) $reserved);
        $pending = ReferralCommissionLedger::where('referral_partner_id', $user->id)->where('status', 'pending')->sum('commission_amount');
        $paid = ReferralWithdrawalRequest::where('referral_partner_id', $user->id)->where('status', 'paid')->sum('amount');
        $leads = ReferralLead::where('referral_partner_id', $user->id)->latest()->paginate(10, ['*'], 'leads');
        $withdrawals = ReferralWithdrawalRequest::where('referral_partner_id', $user->id)->latest()->get();
        return view('referral.dashboard', compact(
            'profile', 'referrals', 'ledger', 'available', 'pending', 'paid', 'leads', 'withdrawals',
            'totalClients', 'activeClients', 'closedClients', 'lostClients', 'monthlyRevenue', 'invoiceTracker'
        ));
    }

    public function storeLead(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required','string','max:255'], 'contact_name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255'], 'phone_number' => ['nullable','string','max:20'],
            'service_required' => ['nullable','string','max:100'], 'notes' => ['nullable','string','max:2000'],
        ]);
        $partner = Auth::user();
        $lead = ReferralLead::create([...$data, 'referral_partner_id' => $partner->id]);

        $admins = User::role(['Superadmin', 'Manager'])->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new SuperadminActivityNotification(
                'referral.lead_submitted',
                'New referral lead submitted',
                "{$partner->name} submitted {$lead->contact_name} from {$lead->company_name}.",
                'user-plus',
                [
                    'lead_id' => $lead->id,
                    'referral_partner_id' => $partner->id,
                    'url' => route('admin.referrals.index', ['ref_tab' => 'leads']),
                ]
            ));
        }

        return back()->with('success', 'Lead submitted. The Superadmin team has been notified for duplicate review and follow-up.');
    }

    public function storeWithdrawal(Request $request)
    {
        $data = $request->validate(['amount' => ['required','numeric','min:1']]);
        $user = Auth::user();
        DB::transaction(function () use ($data, $user) {
            $grossAvailable = ReferralCommissionLedger::where('referral_partner_id', $user->id)->where('status', 'available')->lockForUpdate()->sum('commission_amount');
            $reserved = ReferralWithdrawalRequest::where('referral_partner_id', $user->id)->whereIn('status', ['requested', 'approved', 'paid'])->lockForUpdate()->sum('amount');
            $available = max(0, (float) $grossAvailable - (float) $reserved);
            abort_if((float) $data['amount'] > (float) $available, 422, 'Withdrawal amount exceeds your available balance.');
            ReferralWithdrawalRequest::create(['referral_partner_id' => $user->id, 'amount' => $data['amount']]);
        });
        return back()->with('success', 'Withdrawal request submitted for the monthly Finance cycle.');
    }
}
