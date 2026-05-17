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

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            {{-- FREE --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-3xl p-6 flex flex-col {{ $currentPlan === 'Free' ? 'ring-2 ring-white/30' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-slate-300 font-bold uppercase text-[11px] tracking-wider">🟢 Free</span>
                    @if($currentPlan === 'Free')<span class="text-[10px] bg-white/10 text-white px-2 py-0.5 rounded-full font-bold uppercase">Current</span>@endif
                </div>
                <div class="text-4xl font-extrabold text-white">₹0</div>
                <div class="text-blue-300 text-sm mb-1">/month</div>
                <div class="text-xs text-blue-300/70 mb-5">Entry / Freshers</div>
                <ul class="text-sm text-blue-100 space-y-2 flex-1">
                    <li>✔ 5–10 job submissions / month</li>
                    <li>✔ 20–30% commission per closure</li>
                    <li>✔ Basic profile visibility</li>
                    <li class="opacity-50">✘ Bulk hiring projects</li>
                    <li class="opacity-50">✘ Priority support</li>
                </ul>
                @if($currentPlan === 'Free')
                    <button disabled class="mt-6 w-full bg-slate-700 text-slate-300 font-bold py-3 rounded-xl cursor-not-allowed">Current Plan</button>
                @else
                    <a href="mailto:downgrades@simplyhiree.com?subject=Downgrade%20to%20Free" class="mt-6 w-full block text-center bg-white/10 hover:bg-white/20 text-white font-bold py-3 rounded-xl transition">Downgrade</a>
                @endif
            </div>

            {{-- BASIC --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-blue-400/30 rounded-3xl p-6 flex flex-col {{ $currentPlan === 'Basic' ? 'ring-2 ring-blue-400/60' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-blue-200 font-bold uppercase text-[11px] tracking-wider">🔵 Basic</span>
                    @if($currentPlan === 'Basic')<span class="text-[10px] bg-blue-500/30 text-blue-100 px-2 py-0.5 rounded-full font-bold uppercase">Current</span>@endif
                </div>
                <div class="text-4xl font-extrabold text-white">₹499<span class="text-base font-semibold text-blue-200">–999</span></div>
                <div class="text-blue-200 text-sm mb-1">/month</div>
                <div class="text-xs text-blue-300/70 mb-5">Starter Paid · Serious Freelancers</div>
                <ul class="text-sm text-blue-100 space-y-2 flex-1">
                    <li>✔ 30–50 job submissions / month</li>
                    <li>✔ 15–20% commission per closure</li>
                    <li>✔ WhatsApp support group</li>
                    <li>✔ Medium profile visibility boost</li>
                    <li>✔ Early access (2–4 hrs before Free)</li>
                </ul>
                @if($currentPlan !== 'Basic')
                    <a href="mailto:upgrades@simplyhiree.com?subject=Upgrade%20to%20Basic" class="mt-6 w-full block text-center bg-blue-500 hover:bg-blue-400 text-white font-extrabold py-3 rounded-xl transition shadow-md shadow-blue-500/30">Upgrade to Basic</a>
                @else
                    <button disabled class="mt-6 w-full bg-slate-700 text-slate-300 font-bold py-3 rounded-xl cursor-not-allowed">Current Plan</button>
                @endif
            </div>

            {{-- PRO --}}
            <div class="bg-gradient-to-br from-purple-800/40 to-fuchsia-700/40 backdrop-blur-xl border-2 border-purple-400/60 rounded-3xl p-6 flex flex-col relative shadow-2xl shadow-purple-500/20 {{ $currentPlan === 'Pro' ? 'ring-2 ring-purple-300' : '' }}">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-purple-400 text-slate-900 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full tracking-wider">Most Popular</div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-purple-200 font-bold uppercase text-[11px] tracking-wider">🟣 Pro</span>
                    @if($currentPlan === 'Pro')<span class="text-[10px] bg-purple-500/30 text-purple-100 px-2 py-0.5 rounded-full font-bold uppercase">Current</span>@endif
                </div>
                <div class="text-4xl font-extrabold text-white">₹1,999<span class="text-base font-semibold text-purple-200">–2,999</span></div>
                <div class="text-purple-200 text-sm mb-1">/month</div>
                <div class="text-xs text-purple-200/70 mb-5">High Performer · Experienced Recruiters</div>
                <ul class="text-sm text-white space-y-2 flex-1">
                    <li>✔ <strong>Unlimited</strong> job submissions</li>
                    <li>✔ 10–15% commission (lowest)</li>
                    <li>✔ Dedicated Account Manager</li>
                    <li>✔ Priority payouts</li>
                    <li>✔ Bulk hiring projects access</li>
                    <li>✔ Featured profile (top listing)</li>
                </ul>
                @if($currentPlan !== 'Pro')
                    <a href="mailto:upgrades@simplyhiree.com?subject=Upgrade%20to%20Pro" class="mt-6 w-full block text-center bg-purple-400 hover:bg-purple-300 text-slate-900 font-extrabold py-3 rounded-xl transition">Upgrade to Pro</a>
                @else
                    <button disabled class="mt-6 w-full bg-slate-700 text-slate-300 font-bold py-3 rounded-xl cursor-not-allowed">Current Plan</button>
                @endif
            </div>

            {{-- ENTERPRISE --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-rose-400/40 rounded-3xl p-6 flex flex-col {{ $currentPlan === 'Enterprise' ? 'ring-2 ring-rose-400' : '' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-rose-200 font-bold uppercase text-[11px] tracking-wider">🔴 Enterprise</span>
                    @if($currentPlan === 'Enterprise')<span class="text-[10px] bg-rose-500/30 text-rose-100 px-2 py-0.5 rounded-full font-bold uppercase">Current</span>@endif
                </div>
                <div class="text-4xl font-extrabold text-white">₹5,000<span class="text-base font-semibold text-rose-200">–15,000</span></div>
                <div class="text-rose-200 text-sm mb-1">/month (custom)</div>
                <div class="text-xs text-rose-200/70 mb-5">Big Vendors · Agencies</div>
                <ul class="text-sm text-rose-50 space-y-2 flex-1">
                    <li>✔ Dedicated hiring projects</li>
                    <li>✔ Direct client connection</li>
                    <li>✔ Unlimited team logins</li>
                    <li>✔ Zero / very-low commission</li>
                    <li>✔ SLA-based hiring contracts</li>
                    <li>✔ Dashboard + reporting</li>
                </ul>
                @if($currentPlan !== 'Enterprise')
                    <a href="mailto:sales@simplyhiree.com?subject=Enterprise%20Plan%20Enquiry" class="mt-6 w-full block text-center bg-rose-400 hover:bg-rose-300 text-slate-900 font-extrabold py-3 rounded-xl transition">Talk to Sales</a>
                @else
                    <button disabled class="mt-6 w-full bg-slate-700 text-slate-300 font-bold py-3 rounded-xl cursor-not-allowed">Current Plan</button>
                @endif
            </div>
        </div>

        <p class="text-center text-blue-200 text-sm mt-8">
            Have a question? Email <a href="mailto:sales@simplyhiree.com" class="text-cyan-300 underline">sales@simplyhiree.com</a> or call your account manager.
        </p>
    </div>
</div>
@endsection
