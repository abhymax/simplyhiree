@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-6xl mx-auto">
        <div class="text-center mb-10">
            <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight">Upgrade Your Plan</h1>
            <p class="text-blue-200 mt-2">Unlock premium jobs, higher payout brackets, and priority client access.</p>
            <div class="mt-3 inline-flex items-center gap-2 text-xs uppercase tracking-wider text-amber-200 font-bold">
                Current plan: <span class="px-2 py-0.5 rounded bg-white/10 border border-white/20">{{ $partner->partner_plan ?? 'Free' }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Free --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 flex flex-col">
                <div class="text-blue-200 font-bold uppercase text-xs tracking-wider">Free</div>
                <div class="text-4xl font-extrabold text-white mt-2">₹0</div>
                <div class="text-blue-300 text-sm mb-5">/month</div>
                <ul class="text-sm text-blue-100 space-y-2 flex-1">
                    <li>✔ Browse standard jobs</li>
                    <li>✔ Up to 1 team member</li>
                    <li>✔ Basic dashboard</li>
                    <li class="opacity-50">✘ No premium jobs</li>
                    <li class="opacity-50">✘ No priority support</li>
                </ul>
                @if(($partner->partner_plan ?? 'Free') === 'Free')
                    <button disabled class="mt-6 w-full bg-slate-700 text-slate-300 font-bold py-3 rounded-xl">Current Plan</button>
                @endif
            </div>

            {{-- Pro --}}
            <div class="bg-gradient-to-br from-blue-700/50 to-indigo-700/50 backdrop-blur-xl border-2 border-cyan-400/50 rounded-3xl p-6 flex flex-col relative shadow-2xl shadow-cyan-500/20">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-cyan-400 text-slate-900 text-xs font-extrabold uppercase px-3 py-1 rounded-full">Most Popular</div>
                <div class="text-cyan-200 font-bold uppercase text-xs tracking-wider">Pro</div>
                <div class="text-4xl font-extrabold text-white mt-2">₹2,999</div>
                <div class="text-cyan-200 text-sm mb-5">/month</div>
                <ul class="text-sm text-white space-y-2 flex-1">
                    <li>✔ All Free benefits</li>
                    <li>✔ Access to premium jobs</li>
                    <li>✔ Up to 5 team members</li>
                    <li>✔ Performance reports</li>
                    <li>✔ Email support</li>
                </ul>
                <a href="mailto:upgrades@simplyhiree.com?subject=Upgrade%20to%20Pro" class="mt-6 w-full bg-cyan-400 hover:bg-cyan-300 text-slate-900 font-extrabold py-3 rounded-xl text-center transition">Contact Sales</a>
            </div>

            {{-- Premium --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-yellow-400/40 rounded-3xl p-6 flex flex-col">
                <div class="text-yellow-200 font-bold uppercase text-xs tracking-wider">Premium</div>
                <div class="text-4xl font-extrabold text-white mt-2">₹7,999</div>
                <div class="text-yellow-200 text-sm mb-5">/month</div>
                <ul class="text-sm text-yellow-50 space-y-2 flex-1">
                    <li>✔ All Pro benefits</li>
                    <li>✔ Unlimited team members</li>
                    <li>✔ Confidential premium jobs first-look</li>
                    <li>✔ Dedicated account manager</li>
                    <li>✔ Priority phone &amp; WhatsApp support</li>
                </ul>
                <a href="mailto:upgrades@simplyhiree.com?subject=Upgrade%20to%20Premium" class="mt-6 w-full bg-yellow-400 hover:bg-yellow-300 text-slate-900 font-extrabold py-3 rounded-xl text-center transition">Contact Sales</a>
            </div>
        </div>

        <p class="text-center text-blue-200 text-sm mt-8">Need a custom enterprise plan? Email <a href="mailto:sales@simplyhiree.com" class="text-cyan-300 underline">sales@simplyhiree.com</a>.</p>
    </div>
</div>
@endsection
