@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">Choose Your Plan</h1>
            <p class="text-blue-200 mt-2">Higher tiers = lower commission, faster payouts, and access to exclusive bulk-hiring projects.</p>
            @php $currentPlan = $partner->partner_plan ?? 'Free'; @endphp
            <div class="mt-3 inline-flex items-center gap-2 text-xs uppercase tracking-wider text-amber-200 font-bold">
                Current plan: <span class="px-2 py-0.5 rounded bg-white/10 border border-white/20">{{ $currentPlan }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-5 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl font-bold">{{ session('error') }}</div>
        @endif

        @if($pendingRequest)
            <div class="mb-6 bg-amber-500/10 border border-amber-400/40 rounded-2xl p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div>
                    <div class="text-amber-100 font-extrabold flex items-center gap-2">
                        <i class="fa-solid fa-hourglass-half"></i>
                        Pending request: {{ $pendingRequest->current_plan }} → {{ $pendingRequest->requested_plan }}
                    </div>
                    <div class="text-amber-100/80 text-xs mt-1">
                        Submitted {{ $pendingRequest->created_at->diffForHumans() }}. A SimplyHiree manager will contact you shortly.
                    </div>
                    @if($pendingRequest->notes)
                        <div class="mt-1 text-amber-100/80 text-sm italic">"{{ $pendingRequest->notes }}"</div>
                    @endif
                </div>
                <form method="POST" action="{{ route('partner.upgrade.cancel', $pendingRequest->id) }}" onsubmit="return confirm('Cancel this plan request?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold px-4 py-2 rounded-lg">Cancel Request</button>
                </form>
            </div>
        @endif

        @php
            $cards = [
                ['key'=>'Free','label'=>'🟢 Free','price'=>'₹0','priceSub'=>'/month','badge'=>'Entry / Freshers','frame'=>'bg-slate-900/60 border border-white/15','features'=>[
                    '✔ 5–10 job submissions / month',
                    '✔ 20–30% commission per closure',
                    '✔ Basic profile visibility',
                    '<span class="opacity-50">✘ Bulk hiring projects</span>',
                    '<span class="opacity-50">✘ Priority support</span>',
                ]],
                ['key'=>'Basic','label'=>'🔵 Basic','price'=>'₹499<span class="text-base font-semibold">–999</span>','priceSub'=>'/month','badge'=>'Starter Paid · Serious Freelancers','frame'=>'bg-slate-900/60 border border-blue-400/30','features'=>[
                    '✔ 30–50 job submissions / month',
                    '✔ 15–20% commission per closure',
                    '✔ WhatsApp support group',
                    '✔ Medium profile visibility boost',
                    '✔ Early access (2–4 hrs before Free)',
                ]],
                ['key'=>'Pro','label'=>'🟣 Pro','price'=>'₹1,999<span class="text-base font-semibold">–2,999</span>','priceSub'=>'/month','badge'=>'High Performer · Experienced Recruiters','frame'=>'bg-gradient-to-br from-purple-800/40 to-fuchsia-700/40 border-2 border-purple-400/60 shadow-2xl shadow-purple-500/20','popular'=>true,'features'=>[
                    '✔ <strong>Unlimited</strong> job submissions',
                    '✔ 10–15% commission (lowest)',
                    '✔ Dedicated Account Manager',
                    '✔ Priority payouts',
                    '✔ Bulk hiring projects access',
                    '✔ Featured profile (top listing)',
                ]],
                ['key'=>'Enterprise','label'=>'🔴 Enterprise','price'=>'₹5,000<span class="text-base font-semibold">–15,000</span>','priceSub'=>'/month (custom)','badge'=>'Big Vendors · Agencies','frame'=>'bg-slate-900/60 border border-rose-400/40','features'=>[
                    '✔ Dedicated hiring projects',
                    '✔ Direct client connection',
                    '✔ Unlimited team logins',
                    '✔ Zero / very-low commission',
                    '✔ SLA-based hiring contracts',
                    '✔ Dashboard + reporting',
                ]],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            @foreach($cards as $card)
                @php $isCurrent = $currentPlan === $card['key']; @endphp
                <div class="{{ $card['frame'] }} backdrop-blur-xl rounded-3xl p-6 flex flex-col relative {{ $isCurrent ? 'ring-2 ring-white/40' : '' }}">
                    @if(!empty($card['popular']))
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-purple-400 text-slate-900 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full tracking-wider">Most Popular</div>
                    @endif
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold uppercase text-[11px] tracking-wider">{{ $card['label'] }}</span>
                        @if($isCurrent)<span class="text-[10px] bg-white/20 text-white px-2 py-0.5 rounded-full font-bold uppercase">Current</span>@endif
                    </div>
                    <div class="text-4xl font-extrabold text-white">{!! $card['price'] !!}</div>
                    <div class="text-sm mb-1">{{ $card['priceSub'] }}</div>
                    <div class="text-xs opacity-70 mb-5">{{ $card['badge'] }}</div>
                    <ul class="text-sm space-y-2 flex-1">
                        @foreach($card['features'] as $f)
                            <li>{!! $f !!}</li>
                        @endforeach
                    </ul>

                    @if($isCurrent)
                        <button disabled class="mt-6 w-full bg-slate-700 text-slate-300 font-bold py-3 rounded-xl cursor-not-allowed">Current Plan</button>
                    @elseif($pendingRequest)
                        <button disabled class="mt-6 w-full bg-slate-700 text-slate-300 font-bold py-3 rounded-xl cursor-not-allowed" title="Cancel your pending request first">Request Pending</button>
                    @else
                        <form method="POST" action="{{ route('partner.upgrade.request') }}" onsubmit="return confirm('Request a plan change to {{ $card['key'] }}? A SimplyHiree manager will contact you.');" class="mt-6">
                            @csrf
                            <input type="hidden" name="requested_plan" value="{{ $card['key'] }}">
                            <button type="submit" class="w-full bg-cyan-400 hover:bg-cyan-300 text-slate-900 font-extrabold py-3 rounded-xl transition">
                                @if(array_search($card['key'], ['Free','Basic','Pro','Enterprise']) < array_search($currentPlan, ['Free','Basic','Pro','Enterprise']))
                                    Request Downgrade
                                @else
                                    Request Upgrade
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="text-center text-blue-200 text-sm mt-8">
            All requests are reviewed by a SimplyHiree manager. You'll be contacted by phone or email shortly after submitting.
        </p>
    </div>
</div>
@endsection
