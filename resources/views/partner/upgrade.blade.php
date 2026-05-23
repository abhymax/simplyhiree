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
            // Map DB accent_color → Tailwind classes for the card frame and dot
            $accentMap = [
                'slate'   => ['frame'=>'bg-slate-900/60 border border-white/15',                                                              'dot'=>'bg-emerald-400'],
                'blue'    => ['frame'=>'bg-slate-900/60 border border-blue-400/30',                                                            'dot'=>'bg-blue-400'],
                'purple'  => ['frame'=>'bg-gradient-to-br from-purple-800/40 to-fuchsia-700/40 border-2 border-purple-400/60 shadow-2xl shadow-purple-500/20', 'dot'=>'bg-purple-400'],
                'rose'    => ['frame'=>'bg-slate-900/60 border border-rose-400/40',                                                            'dot'=>'bg-rose-400'],
                'emerald' => ['frame'=>'bg-slate-900/60 border border-emerald-400/40',                                                         'dot'=>'bg-emerald-400'],
            ];
            // Cached ordered plan-name list for upgrade/downgrade detection
            $planOrder = $plans->pluck('name')->all();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-5">
            @foreach($plans as $plan)
                @php
                    $isCurrent = $currentPlan === $plan->name;
                    $accent = $accentMap[$plan->accent_color] ?? $accentMap['slate'];
                @endphp
                <div class="{{ $accent['frame'] }} backdrop-blur-xl rounded-2xl p-5 flex flex-col relative {{ $isCurrent ? 'ring-2 ring-white/40' : '' }}">
                    @if($plan->is_most_popular)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-purple-400 text-slate-900 text-[10px] font-extrabold uppercase px-3 py-1 rounded-full tracking-wider">Most Popular</div>
                    @endif
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-bold uppercase text-[11px] tracking-wider text-white inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full {{ $accent['dot'] }}"></span> {{ $plan->name }}
                        </span>
                        @if($isCurrent)<span class="text-[10px] bg-white/20 text-white px-2 py-0.5 rounded-full font-bold uppercase">Current</span>@endif
                    </div>
                    {{-- Fixed-height pricing block so feature lists across all 4 cards start at exactly the same Y --}}
                    <div style="min-height: 76px;">
                        <div class="text-3xl font-extrabold text-white leading-none">
                            ₹{{ number_format((float) $plan->price) }}@if($plan->price_max && (float) $plan->price_max > (float) $plan->price)<span class="text-base font-semibold">–{{ number_format((float) $plan->price_max) }}</span>@endif
                        </div>
                        <div class="text-xs text-white/70 mt-1">{{ $plan->price_suffix ?: '/month' }}</div>
                        @if($plan->subtitle)
                            <div class="text-[11px] text-white/60 mt-1.5 leading-tight">{{ $plan->subtitle }}</div>
                        @endif
                    </div>

                    <ul class="text-[13px] space-y-2.5 mt-3 flex-1 text-white">
                        @foreach((array) $plan->features as $f)
                            <li class="flex items-start gap-3.5 group cursor-default transition-all duration-200 hover:translate-x-1">
                                <span class="flex-shrink-0 w-5 h-5 mt-0.5 rounded-full flex items-center justify-center transition-all"
                                      style="background:#10b981; box-shadow: 0 0 0 2px rgba(16,185,129,.25), 0 4px 10px -2px rgba(16,185,129,.55);">
                                    <i class="fa-solid fa-check text-[10px]" style="color:#ffffff;"></i>
                                </span>
                                <span class="flex-1 leading-snug group-hover:text-white">{{ $f }}</span>
                            </li>
                        @endforeach
                        @foreach((array) $plan->non_features as $f)
                            <li class="flex items-start gap-3.5 group cursor-default">
                                <span class="flex-shrink-0 w-5 h-5 mt-0.5 rounded-full flex items-center justify-center"
                                      style="background:#ef4444; box-shadow: 0 0 0 2px rgba(239,68,68,.25), 0 4px 10px -2px rgba(239,68,68,.5);">
                                    <i class="fa-solid fa-xmark text-[10px]" style="color:#ffffff;"></i>
                                </span>
                                <span class="flex-1 leading-snug text-white/50 line-through decoration-rose-400/40">{{ $f }}</span>
                            </li>
                        @endforeach
                    </ul>

                    @if($isCurrent)
                        <button disabled class="mt-3 w-full font-bold py-2 rounded-xl cursor-not-allowed flex items-center justify-center gap-2 text-sm"
                                style="background:#475569; color:#cbd5e1;">
                            <i class="fa-solid fa-check-circle"></i> Current Plan
                        </button>
                    @elseif($pendingRequest)
                        <button disabled class="mt-3 w-full font-bold py-2 rounded-xl cursor-not-allowed flex items-center justify-center gap-2 text-sm"
                                style="background:#475569; color:#cbd5e1;"
                                title="Cancel your pending request first">
                            <i class="fa-regular fa-clock"></i> Request Pending
                        </button>
                    @else
                        @php
                            $isDowngrade = array_search($plan->name, $planOrder) !== false
                                && array_search($currentPlan, $planOrder) !== false
                                && array_search($plan->name, $planOrder) < array_search($currentPlan, $planOrder);
                        @endphp
                        <form method="POST" action="{{ route('partner.upgrade.request') }}" onsubmit="return confirm('Request a plan change to {{ $plan->name }}? A SimplyHiree manager will contact you.');" class="mt-3">
                            @csrf
                            <input type="hidden" name="requested_plan" value="{{ $plan->name }}">
                            <button type="submit"
                                    class="w-full font-bold py-2 rounded-xl transition-all flex items-center justify-center gap-2 transform hover:-translate-y-0.5 hover:scale-[1.02] text-sm"
                                    style="{{ $isDowngrade
                                        ? 'background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%); color: #1e293b; box-shadow: 0 10px 25px -8px rgba(245,158,11,.5), inset 0 1px 0 rgba(255,255,255,.35);'
                                        : 'background: linear-gradient(135deg, #22d3ee 0%, #0ea5e9 100%); color: #0f172a; box-shadow: 0 10px 25px -8px rgba(34,211,238,.55), inset 0 1px 0 rgba(255,255,255,.45);' }}"
                                    onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
                                @if($isDowngrade)
                                    <i class="fa-solid fa-arrow-down"></i> Request Downgrade
                                @else
                                    <i class="fa-solid fa-arrow-up"></i> Request Upgrade
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>

        <p class="text-center text-white text-sm mt-8 bg-white/5 border border-white/10 rounded-xl py-3 px-4">
            <i class="fa-solid fa-info-circle text-cyan-300 mr-1"></i>
            All requests are reviewed by a SimplyHiree manager. You'll be contacted by phone or email shortly after submitting.
        </p>
    </div>
</div>
@endsection
