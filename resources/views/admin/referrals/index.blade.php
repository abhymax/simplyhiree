<x-app-layout>
@php
    $referralTabs = ['partners', 'clients', 'billing', 'leads', 'withdrawals'];
    $leadReviewCount = $leads->total();
    $activeReferralTab = in_array(request('ref_tab'), $referralTabs, true)
        ? request('ref_tab')
        : ($leadReviewCount > 0 ? 'leads' : 'partners');
@endphp
<div class="referral-stack max-w-7xl mx-auto px-4 py-8 space-y-10 text-slate-100">
    <header class="border-b border-white/10 pb-6"><a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 text-xs font-bold uppercase tracking-wider mb-2"><i class="fa-solid fa-arrow-left mr-2"></i>Dashboard</a><h1 class="text-4xl font-extrabold text-white">Referral Management</h1><p class="text-blue-200 mt-1">Approve partners and leads, manage client rules, verify revenue, and process monthly payouts.</p></header>
    @if(session('success'))<div class="rounded-xl bg-emerald-500/20 border border-emerald-400/30 p-4 text-emerald-100">{{ session('success') }}</div>@endif
    @if(isset($errors) && $errors->any())<div class="rounded-xl bg-rose-500/20 border border-rose-400/30 p-4 text-rose-100">{{ $errors->first() }}</div>@endif

    <nav class="sticky top-2 z-30 flex gap-2 overflow-x-auto rounded-2xl border border-white/10 bg-slate-950/90 p-2 shadow-xl backdrop-blur-xl" aria-label="Referral sections">
        @foreach([['partners','Partners','fa-user-group'],['clients','Clients & Rules','fa-building'],['billing','Billing','fa-file-invoice-dollar'],['leads','Lead Review','fa-magnifying-glass'],['withdrawals','Withdrawals','fa-money-bill-transfer']] as [$tab,$label,$icon])
            <button type="button" data-referral-tab="{{ $tab }}" class="referral-tab shrink-0 rounded-xl border px-4 py-2.5 text-xs font-bold transition {{ $activeReferralTab === $tab ? 'border-cyan-400/50 bg-cyan-500/15 text-cyan-100 shadow-lg shadow-cyan-950/30' : 'border-white/10 bg-white/5 text-slate-300 hover:border-cyan-400/40 hover:bg-cyan-500/10 hover:text-cyan-200' }}"><i class="fa-solid {{ $icon }} mr-2"></i>{{ $label }}@if($tab === 'leads' && $leadReviewCount > 0)<span class="ml-2 inline-flex min-w-5 items-center justify-center rounded-full bg-amber-400 px-1.5 py-0.5 text-[10px] font-extrabold text-slate-950 animate-pulse">{{ $leadReviewCount }}</span>@endif</button>
        @endforeach
    </nav>

    <div class="grid gap-5 md:grid-cols-4">
        @foreach([['Total referred clients',$totalReferralClients,'fa-users','text-cyan-300'],['Active clients',$activeReferralClients,'fa-circle-check','text-emerald-300'],['Monthly revenue','₹'.number_format($monthlyRevenue,2),'fa-chart-line','text-amber-300'],['Monthly commission','₹'.number_format($monthlyCommission,2),'fa-coins','text-violet-300']] as [$label,$value,$icon,$color])
            <section class="referral-kpi rounded-2xl border border-white/10 p-5 relative overflow-hidden"><div class="flex items-start justify-between gap-3"><div><p class="text-xs uppercase font-bold tracking-wider text-slate-300">{{ $label }}</p><p class="mt-2 text-3xl font-extrabold text-white">{{ $value }}</p></div><span class="w-11 h-11 rounded-xl bg-white/10 border border-white/10 flex items-center justify-center {{ $color }}"><i class="fa-solid {{ $icon }}"></i></span></div></section>
        @endforeach
    </div>

    <div data-referral-panel="partners" class="referral-panel {{ $activeReferralTab === 'partners' ? '' : 'hidden' }}">
    <section id="referral-partners" class="bg-slate-900/75 backdrop-blur-xl rounded-2xl border border-white/10 overflow-x-auto">
        <div class="p-5 border-b border-white/10"><h2 class="font-bold">Referral partners and generated links</h2><p class="text-sm text-slate-400">Approve enrollment, then copy the stable referral link. Existing links are never regenerated, so shared URLs remain valid.</p></div>
        <table class="min-w-full text-sm"><thead class="bg-blue-950/70 text-left"><tr><th class="p-3">Partner</th><th class="p-3">Type / Status</th><th class="p-3">Referral link</th><th class="p-3">Action</th></tr></thead><tbody>
        @forelse($profiles as $profile)
            @php($phone = $profile->user?->profile?->phone_number)
            <tr class="border-t"><td class="p-3"><b>{{ $profile->user->name }}</b><br><span class="text-slate-400">{{ $profile->user->email }} · {{ $profile->referral_code }}</span><br>@if($phone)<a href="tel:{{ preg_replace('/\D+/', '', $phone) }}" class="mt-1 inline-flex items-center gap-1.5 font-semibold text-cyan-300 hover:text-cyan-200"><i class="fa-solid fa-phone text-[11px]"></i>{{ $phone }}</a>@else<span class="mt-1 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-300"><i class="fa-solid fa-phone-slash"></i>No phone provided</span>@endif</td><td class="p-3 capitalize">{{ $profile->partner_type }} · {{ $profile->status }}</td><td class="p-3"><div class="flex min-w-[320px] gap-2"><input id="admin-referral-link-{{ $profile->id }}" readonly value="{{ url('/register/client?ref='.$profile->referral_code) }}" class="min-w-0 flex-1 rounded-lg bg-slate-950 border-slate-700 text-white text-xs"><button type="button" data-copy-target="admin-referral-link-{{ $profile->id }}" class="copy-referral-link rounded bg-indigo-600 px-3 text-white" @disabled($profile->status !== 'active')><i class="fa-regular fa-copy"></i></button></div></td><td class="p-3">@if($profile->status === 'pending')<form method="POST" action="{{ route('admin.referrals.profiles.approve',$profile) }}">@csrf<button class="rounded bg-emerald-600 px-3 py-1.5 text-white">Approve &amp; activate link</button></form>@else<span class="font-semibold text-emerald-300">Link active</span>@endif</td></tr>
        @empty<tr><td colspan="4" class="p-5">No referral partner profiles.</td></tr>@endforelse
        </tbody></table><div class="p-4">{{ $profiles->appends(['ref_tab' => 'partners'])->links() }}</div>
    </section>
    </div>

    <div data-referral-panel="clients" class="referral-panel space-y-6 {{ $activeReferralTab === 'clients' ? '' : 'hidden' }}">
    <section id="referred-clients" class="bg-slate-900/75 backdrop-blur-xl rounded-2xl border border-white/10 overflow-x-auto">
        <div class="p-5 border-b border-white/10"><h2 class="font-bold">Referred client list</h2><p class="text-sm text-slate-400">Client lifecycle and manually configured percentage/flat commission rule.</p></div>
        <table class="min-w-full text-sm"><thead class="bg-blue-950/70 text-left"><tr><th class="p-3">Client</th><th class="p-3">Referral partner</th><th class="p-3">Commission rule</th><th class="p-3">Status</th></tr></thead><tbody>
        @forelse($referrals as $referral)
            <tr class="border-t"><td class="p-3"><b>{{ $referral->client?->clientProfile?->company_name ?? $referral->client?->name }}</b><br><span class="text-slate-400">{{ $referral->client?->email }}</span></td><td class="p-3">{{ $referral->referralPartner?->name }}</td><td class="p-3">@if($referral->rule)<span class="font-semibold">{{ str_contains($referral->rule->commission_type, 'percentage') ? number_format($referral->rule->commission_value,2).'%' : '₹'.number_format($referral->rule->commission_value,2) }}</span><br><span class="text-xs text-slate-400">{{ str_replace('_',' ',$referral->rule->commission_type) }}</span>@else<span class="text-amber-300">Not set</span>@endif</td><td class="p-3"><form method="POST" action="{{ route('admin.referrals.clients.status',$referral) }}" class="flex gap-2">@csrf<select name="status" class="rounded-lg bg-slate-950 border-slate-700 text-white"><option value="active" @selected($referral->status === 'active')>Active</option><option value="closed" @selected($referral->status === 'closed')>Closed</option><option value="lost" @selected($referral->status === 'lost')>Lost</option></select><button class="rounded bg-slate-900 px-3 text-white">Save</button></form></td></tr>
        @empty<tr><td colspan="4" class="p-5">No referred clients.</td></tr>@endforelse
        </tbody></table><div class="p-4">{{ $referrals->appends(['ref_tab' => 'clients'])->links() }}</div>
    </section>

    <section class="bg-slate-900/75 backdrop-blur-xl rounded-2xl border border-white/10 p-5"><h2 class="font-bold mb-1">Link client and set commission rule</h2><p class="mb-4 text-sm text-slate-400">Set a percentage or flat amount manually for each referred client.</p><form method="POST" action="{{ route('admin.referrals.clients.link') }}" class="grid md:grid-cols-4 gap-3">@csrf<select name="client_id" required class="referral-select rounded-lg bg-slate-950 border-slate-700 text-white"><option value="">Select client</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }} — {{ $client->email }}</option>@endforeach</select><select name="referral_partner_id" required class="referral-select rounded-lg bg-slate-950 border-slate-700 text-white"><option value="">Select referral partner</option>@foreach($partners as $partner)<option value="{{ $partner->id }}">{{ $partner->name }} — {{ $partner->email }}</option>@endforeach</select><select name="commission_type" class="referral-select rounded-lg bg-slate-950 border-slate-700 text-white"><option value="percentage_net_revenue">% of net revenue</option><option value="flat_per_closure">Flat per closure</option><option value="recurring_percentage">Recurring %</option><option value="recurring_flat">Recurring flat</option></select><div class="flex gap-2"><input required name="commission_value" type="number" step="0.01" min="0" placeholder="Value" class="w-full rounded-lg bg-slate-950 border-slate-700 text-white"><button class="rounded bg-indigo-600 px-4 text-white">Save</button></div></form></section>
    </div>

    <div data-referral-panel="billing" class="referral-panel space-y-6 {{ $activeReferralTab === 'billing' ? '' : 'hidden' }}">
    <section id="referral-billing" class="bg-slate-900/75 backdrop-blur-xl rounded-2xl border border-white/10 overflow-x-auto"><div class="p-5 border-b border-white/10"><h2 class="font-bold">Invoice tracker</h2><p class="text-sm text-slate-400">Month-wise Finance-verified billing excluding GST.</p></div><table class="min-w-full text-sm"><thead class="bg-blue-950/70 text-left"><tr><th class="p-3">Month</th><th class="p-3">Invoices</th><th class="p-3">Billing</th><th class="p-3">Referral commission</th></tr></thead><tbody>@forelse($invoiceTracker as $month)<tr class="border-t"><td class="p-3 font-semibold">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m',$month->month_key)->format('F Y') }}</td><td class="p-3">{{ $month->invoice_count }}</td><td class="p-3">₹{{ number_format($month->billing_amount,2) }}</td><td class="p-3">₹{{ number_format($month->commission_amount,2) }}</td></tr>@empty<tr><td colspan="4" class="p-5 text-slate-400">No verified billing entries yet.</td></tr>@endforelse</tbody></table></section>

    <section class="bg-slate-900/75 backdrop-blur-xl rounded-2xl border border-white/10 overflow-x-auto"><div class="p-5 border-b border-white/10"><h2 class="font-bold">Finance-confirmed payments</h2><p class="text-sm text-slate-400">Confirm only after Finance verifies actual payment. Enter the amount excluding GST.</p></div><table class="min-w-full text-sm"><tbody>@forelse($paymentCandidates as $application)<tr class="border-t"><td class="p-3"><b>{{ $application->job?->user?->name }}</b><br>{{ $application->job?->title }} · Client marked paid {{ optional($application->paid_at)->format('d M Y') }}</td><td class="p-3"><form method="POST" action="{{ route('admin.referrals.applications.payment',$application) }}" class="flex gap-2">@csrf<input name="received_amount" required type="number" step="0.01" min="0.01" value="{{ $application->invoice_amount }}" class="w-32 rounded-lg bg-slate-950 border-slate-700 text-white"><input name="notes" placeholder="Finance note / UTR" class="rounded-lg bg-slate-950 border-slate-700 text-white"><button class="rounded bg-emerald-600 px-3 text-white">Confirm</button></form></td></tr>@empty<tr><td class="p-5">No payments awaiting Finance confirmation.</td></tr>@endforelse</tbody></table></section>
    </div>

    <div data-referral-panel="leads" class="referral-panel {{ $activeReferralTab === 'leads' ? '' : 'hidden' }}">
        <section id="lead-review" class="bg-slate-900/75 backdrop-blur-xl rounded-2xl border border-white/10 overflow-x-auto">
            <div class="p-5 border-b border-white/10"><h2 class="font-bold">Lead review and conversion</h2><p class="text-sm text-slate-400">Review new leads for duplicates. Qualified leads can then be linked to the client account with a commission rule.</p></div>
            <table class="min-w-full text-sm"><thead class="bg-blue-950/70 text-left"><tr><th class="p-3">Lead</th><th class="p-3">Referral partner</th><th class="p-3">Action</th></tr></thead><tbody>
            @forelse($leads as $lead)
                <tr class="border-t">
                    <td class="p-3"><b>{{ $lead->company_name }}</b><br><span class="text-slate-400">{{ $lead->contact_name }} · {{ $lead->email ?: 'No email' }} · {{ $lead->phone_number ?: 'No phone' }}</span></td>
                    <td class="p-3"><b>{{ $lead->referralPartner->name }}</b><br><span class="text-slate-400">{{ $lead->referralPartner->email }}</span></td>
                    <td class="p-3">
                        @if($lead->status === 'submitted')
                            <form method="POST" action="{{ route('admin.referrals.leads.review',$lead) }}" class="flex flex-wrap gap-2">@csrf
                                <select name="status" class="referral-select rounded-lg bg-slate-950 border-slate-700 text-white"><option value="qualified">Qualified</option><option value="duplicate">Duplicate</option><option value="rejected">Rejected</option></select>
                                <input name="admin_notes" placeholder="Review note" class="min-w-[190px] rounded-lg bg-slate-950 border-slate-700 text-white">
                                <button class="rounded-lg bg-cyan-600 px-4 py-2 font-bold text-white hover:bg-cyan-500">Save review</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.referrals.leads.link-client',$lead) }}" class="grid min-w-[680px] grid-cols-4 gap-2">@csrf
                                <select name="client_id" required class="referral-select rounded-lg bg-slate-950 border-slate-700 text-white"><option value="">Select converted client</option>@foreach($clients as $client)<option value="{{ $client->id }}">{{ $client->name }} — {{ $client->email }}</option>@endforeach</select>
                                <select name="commission_type" class="referral-select rounded-lg bg-slate-950 border-slate-700 text-white"><option value="percentage_net_revenue">% of net revenue</option><option value="flat_per_closure">Flat per closure</option><option value="recurring_percentage">Recurring %</option><option value="recurring_flat">Recurring flat</option></select>
                                <input required name="commission_value" type="number" step="0.01" min="0" placeholder="Commission value" class="rounded-lg bg-slate-950 border-slate-700 text-white">
                                <button class="rounded-lg bg-emerald-600 px-4 py-2 font-bold text-white hover:bg-emerald-500"><i class="fa-solid fa-link mr-2"></i>Link client</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty<tr><td colspan="3" class="p-5 text-slate-400">No leads awaiting review or conversion.</td></tr>@endforelse
            </tbody></table><div class="p-4">{{ $leads->appends(['ref_tab' => 'leads'])->links() }}</div>
        </section>
    </div>

    <div data-referral-panel="withdrawals" class="referral-panel {{ $activeReferralTab === 'withdrawals' ? '' : 'hidden' }}">
    <section id="withdrawals" class="bg-slate-900/75 backdrop-blur-xl rounded-2xl border border-white/10 overflow-x-auto"><div class="p-5 border-b border-white/10"><h2 class="font-bold">Withdrawal requests</h2></div><table class="min-w-full text-sm"><tbody>@forelse($withdrawals as $request)<tr class="border-t"><td class="p-3"><b>{{ $request->referralPartner->name }}</b><br>₹{{ number_format($request->amount,2) }}</td><td class="p-3 capitalize">{{ $request->status }}</td><td class="p-3"><form method="POST" action="{{ route('admin.referrals.withdrawals.update',$request) }}" class="flex gap-2">@csrf<select name="status" class="referral-select rounded-lg bg-slate-950 border-slate-700 text-white"><option value="approved">Approve</option><option value="paid">Mark paid</option><option value="rejected">Reject</option></select><input name="payment_reference" placeholder="Payment reference" class="rounded-lg bg-slate-950 border-slate-700 text-white"><button class="rounded bg-slate-900 px-3 text-white">Update</button></form></td></tr>@empty<tr><td class="p-5">No requests.</td></tr>@endforelse</tbody></table></section>
    </div>
</div>
<style>
    .referral-stack > * + * { margin-top: 2.25rem; }
    .referral-stack > nav + .grid { margin-top: 1rem; }
    .referral-select { color-scheme: dark; background-color: #020617 !important; color: #f8fafc !important; }
    .referral-select option { background-color: #0f172a; color: #f8fafc; }
    .referral-panel { animation: referral-panel-in .22s ease-out; }
    @keyframes referral-panel-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    @media (prefers-reduced-motion: reduce) { .referral-panel { animation: none; } }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = Array.from(document.querySelectorAll('[data-referral-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-referral-panel]'));
    const activateTab = function (tabName) {
        tabs.forEach(function (tab) {
            const active = tab.dataset.referralTab === tabName;
            tab.classList.toggle('border-cyan-400/50', active);
            tab.classList.toggle('bg-cyan-500/15', active);
            tab.classList.toggle('text-cyan-100', active);
            tab.classList.toggle('border-white/10', !active);
            tab.classList.toggle('bg-white/5', !active);
            tab.classList.toggle('text-slate-300', !active);
            tab.setAttribute('aria-selected', active ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
            panel.classList.toggle('hidden', panel.dataset.referralPanel !== tabName);
        });
        const url = new URL(window.location.href);
        url.searchParams.set('ref_tab', tabName);
        window.history.replaceState({}, '', url);
    };
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { activateTab(tab.dataset.referralTab); });
    });

    document.querySelectorAll('.copy-referral-link').forEach(function (button) {
        button.addEventListener('click', async function () {
            const input = document.getElementById(button.dataset.copyTarget);
            await navigator.clipboard.writeText(input.value);
            button.innerHTML = '<i class="fa-solid fa-check"></i>';
            window.setTimeout(function () { button.innerHTML = '<i class="fa-regular fa-copy"></i>'; }, 1800);
        });
    });
});
</script>
</x-app-layout>
