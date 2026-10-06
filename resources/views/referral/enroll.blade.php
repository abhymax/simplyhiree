@php
    $isClient = auth()->user()->hasRole('client');
    $referralLayout = $isClient ? 'layouts.client' : 'layouts.app';
    $referralSection = $isClient ? 'client_content' : 'content';
@endphp
@extends($referralLayout)

@section($referralSection)
<style>
    .referral-client-page .referral-card { background: rgba(7, 13, 36, 0.95); border: 1px solid rgba(59, 130, 246, 0.18); }
    .referral-client-page .referral-input { background: rgba(15, 23, 42, 0.82); border: 1px solid rgba(148, 163, 184, 0.22); color: #f8fafc; }
    .referral-client-page .referral-input:focus { border-color: #3b82f6; outline: none; }
    .referral-client-page .referral-input option { background: #0f172a; }
</style>

<div class="{{ $isClient ? 'referral-client-page relative z-10 max-w-5xl mx-auto' : 'max-w-3xl mx-auto px-4 py-10' }}">
    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-wider {{ $isClient ? 'text-blue-400' : 'text-indigo-600' }}">Refer &amp; Earn</p>
            <h1 class="mt-1 text-3xl font-extrabold {{ $isClient ? 'text-white' : 'text-slate-900' }}">Become a Referral Partner</h1>
            <p class="mt-2 {{ $isClient ? 'text-slate-400' : 'text-slate-500' }}">Refer companies to SimplyHiree and earn commission after Finance verifies payment.</p>
        </div>
        @if($profile?->status === 'active')
            <a href="{{ route('referral.dashboard') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-500"><i class="fa-solid fa-arrow-right"></i> Open Referral Dashboard</a>
        @endif
    </div>

    <section class="referral-card mb-6 rounded-lg p-5 {{ $isClient ? 'text-slate-200' : 'border border-indigo-200 bg-indigo-50 text-indigo-950' }}">
        <h2 class="font-bold">How you earn</h2>
        <ol class="mt-4 grid gap-3 text-sm md:grid-cols-2">
            <li><span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">1</span>Submit this agreement and payout profile.</li>
            <li><span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">2</span>Superadmin approves your referral-partner account.</li>
            <li><span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">3</span>Share your unique client link/code or submit a client lead.</li>
            <li><span class="mr-2 inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">4</span>Finance verifies payment excluding GST and adds commission to your ledger.</li>
        </ol>
        <p class="mt-4 text-sm {{ $isClient ? 'text-slate-400' : 'text-indigo-900' }}">There is no withdrawal threshold. Approved payouts are processed monthly.</p>
    </section>

    @if(session('success'))<div class="mb-5 rounded-lg border px-4 py-3 {{ $isClient ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-200' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }}">{{ session('success') }}</div>@endif

    <form method="POST" action="{{ route('referral.enroll.store') }}" class="referral-card rounded-lg p-6 space-y-5 {{ $isClient ? 'text-slate-200' : 'bg-white border border-slate-200' }}">@csrf
        <div class="grid md:grid-cols-2 gap-4">
            <label class="block text-sm font-medium">Partner type<select name="partner_type" class="referral-input mt-1 w-full rounded-lg" @if(!$isClient) style="border: 1px solid #cbd5e1" @endif>@foreach(['Individual','Consultant','Freelancer','Ex HR','Sales Person','Channel Partner'] as $type)<option value="{{ $type }}" @selected(old('partner_type',$profile?->partner_type)===$type)>{{ $type }}</option>@endforeach</select></label>
            <label class="block text-sm font-medium">PAN<input name="pan_number" value="{{ old('pan_number',$profile?->pan_number) }}" class="referral-input mt-1 w-full rounded-lg" maxlength="20" @if(!$isClient) style="border: 1px solid #cbd5e1" @endif></label>
            <label class="block text-sm font-medium">Account holder<input name="bank_account_name" value="{{ old('bank_account_name',$profile?->bank_account_name) }}" class="referral-input mt-1 w-full rounded-lg" @if(!$isClient) style="border: 1px solid #cbd5e1" @endif></label>
            <label class="block text-sm font-medium">Account number<input name="bank_account_number" value="{{ old('bank_account_number',$profile?->bank_account_number) }}" class="referral-input mt-1 w-full rounded-lg" @if(!$isClient) style="border: 1px solid #cbd5e1" @endif></label>
            <label class="block text-sm font-medium">IFSC<input name="bank_ifsc" value="{{ old('bank_ifsc',$profile?->bank_ifsc) }}" class="referral-input mt-1 w-full rounded-lg" maxlength="20" @if(!$isClient) style="border: 1px solid #cbd5e1" @endif></label>
        </div>
        @include('referral.partials.agreement', ['dark' => $isClient])
        @if($errors->any())<div class="text-sm text-rose-400">{{ $errors->first() }}</div>@endif
        <button class="rounded-lg bg-blue-600 px-5 py-2.5 text-white font-bold hover:bg-blue-500">{{ $profile?->status === 'active' ? 'Update profile' : 'Submit for approval' }}</button>
    </form>
</div>
@endsection
