@extends('layouts.app')

@section('content')

{{-- Replacement Requests pop-up (shows once per login session) --}}
@if(!empty($showReplacementModal) && $showReplacementModal && $replacementRequests->isNotEmpty())
<div id="replacement-modal"
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/70 backdrop-blur-sm px-4"
     role="dialog" aria-modal="true">
    <div class="bg-gradient-to-br from-slate-900 to-indigo-950 border border-amber-400/40 rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-400/30 bg-amber-500/10 flex items-start justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-amber-500/20 border border-amber-400/40 rounded-xl text-amber-300">
                    <i class="fa-solid fa-rotate text-xl"></i>
                </div>
                <div>
                    <h3 class="text-white font-extrabold text-lg leading-tight">Replacement Requests</h3>
                    <p class="text-amber-100/80 text-xs mt-0.5">
                        {{ $replacementRequests->count() }} candidate{{ $replacementRequests->count() === 1 ? '' : 's' }} need replacement
                    </p>
                </div>
            </div>
            <button type="button" onclick="document.getElementById('replacement-modal').remove()"
                    class="text-slate-300 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition flex-shrink-0"
                    aria-label="Close">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>
        <div class="px-6 py-4 max-h-72 overflow-y-auto">
            <ul class="divide-y divide-white/10">
                @foreach($replacementRequests->take(5) as $rr)
                    <li class="py-3">
                        <div class="text-white font-bold text-sm">
                            {{ trim(($rr->candidate->first_name ?? '').' '.($rr->candidate->last_name ?? '')) ?: 'Candidate' }}
                            <span class="text-amber-200/80 font-medium">left</span>
                            <span class="text-white">{{ $rr->job->title ?? '—' }}</span>
                        </div>
                        <div class="text-blue-200/80 text-[11px] mt-0.5">
                            Requested {{ $rr->replacement_requested_at->diffForHumans() }}
                        </div>
                    </li>
                @endforeach
            </ul>
            @if($replacementRequests->count() > 5)
                <p class="mt-2 text-xs text-amber-200/80 italic">
                    + {{ $replacementRequests->count() - 5 }} more &mdash; see the Replacements page.
                </p>
            @endif
        </div>
        <div class="px-6 py-4 border-t border-white/10 bg-slate-900/50 flex flex-col sm:flex-row gap-2 justify-end">
            <button type="button" onclick="document.getElementById('replacement-modal').remove()"
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white text-sm font-bold rounded-xl transition border border-white/20">
                Close
            </button>
            <a href="{{ route('partner.replacements') }}"
               class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold rounded-xl transition shadow-lg inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-right"></i> View All Replacements
            </a>
        </div>
    </div>
</div>
@endif

<style>
    .fx-card {
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease, background-color .25s ease;
    }
    .fx-card:hover {
        transform: translateY(-6px) scale(1.01);
        box-shadow: 0 20px 40px rgba(14, 165, 233, 0.22);
        border-color: rgba(255, 255, 255, 0.35);
    }
    .fx-btn {
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }
    .fx-btn:hover {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 10px 24px rgba(59, 130, 246, 0.35);
        filter: brightness(1.05);
    }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if (session('success'))
            <div class="mb-8 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md">
                <i class="fa-solid fa-circle-check mr-3 text-2xl"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(!empty($replacementRequests) && $replacementRequests->count())
            <div class="mb-8 bg-amber-500/10 border border-amber-400/40 rounded-2xl shadow-xl backdrop-blur-md overflow-hidden">
                <div class="px-6 py-4 border-b border-amber-400/30 flex items-center justify-between">
                    <h3 class="text-amber-200 font-extrabold text-lg flex items-center gap-2">
                        <i class="fa-solid fa-rotate"></i>
                        Replacement Requests
                        <span class="bg-amber-500 text-slate-900 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ $replacementRequests->count() }}</span>
                    </h3>
                    <p class="text-amber-100/80 text-xs">Clients have asked for replacements on these candidates. Send fresh candidates for the listed jobs.</p>
                </div>
                <div class="divide-y divide-amber-400/10">
                    @foreach($replacementRequests as $rr)
                    <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <div class="text-white font-bold">
                                {{ trim(($rr->candidate->first_name ?? '').' '.($rr->candidate->last_name ?? '')) ?: 'Candidate' }}
                                <span class="text-amber-200/80 text-sm font-medium">left</span>
                                <span class="text-white">{{ $rr->job->title ?? '—' }}</span>
                            </div>
                            <div class="text-blue-200/80 text-xs mt-0.5">
                                Joined {{ optional($rr->joining_date)->format('M d, Y') ?: '—' }}
                                · Left {{ optional($rr->left_at)->format('M d, Y') ?: '—' }}
                                · Requested {{ $rr->replacement_requested_at->diffForHumans() }}
                            </div>
                            @if($rr->replacement_reason)
                                <div class="mt-1 text-amber-100/90 text-sm italic">"{{ \Illuminate\Support\Str::limit($rr->replacement_reason, 200) }}"</div>
                            @endif
                        </div>
                        <a href="{{ route('partner.jobs.show', $rr->job->id) }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold px-4 py-2 rounded-lg whitespace-nowrap">
                            <i class="fa-solid fa-paper-plane"></i> Send Candidates
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
            <div>
                @php
                    $tier = auth()->user()->partner_tier ?? 'Bronze';
                    $plan = auth()->user()->partner_plan ?? 'Free';
                    $tierColors = [
                        'Bronze' => 'bg-orange-700/30 text-orange-200 border-orange-400/40',
                        'Silver' => 'bg-slate-400/20 text-slate-200 border-slate-400/40',
                        'Gold'   => 'bg-yellow-500/20 text-yellow-200 border-yellow-400/40',
                        'Diamond'=> 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40',
                    ];
                @endphp
                @php
                    $partnerOwner = auth()->user();
                    $avgRating    = $partnerOwner->avg_rating;
                    $totalRatings = $partnerOwner->total_ratings ?? 0;
                    $badge        = $partnerOwner->vendor_badge;
                    $level        = $partnerOwner->vendor_level ?? 'Basic';
                    $levelColors = [
                        'Elite'      => 'bg-purple-500/20 text-purple-200 border-purple-400/40',
                        'Pro'        => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                        'Basic'      => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                        'Restricted' => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                    ];
                    $badgeColors = [
                        'Rising Talent'  => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                        'Top Recruiter'  => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                        'Elite Partner'  => 'bg-purple-500/20 text-purple-200 border-purple-400/40',
                        'Trusted Vendor' => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                    ];
                @endphp
                <div class="flex items-center gap-2 mb-2 flex-wrap">
                    <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                        Partner Workspace
                    </span>
                    @if($avgRating !== null)
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 border border-amber-400/40 text-amber-200">
                            ⭐ {{ number_format($avgRating, 1) }} / 5 <span class="opacity-70">({{ $totalRatings }})</span>
                        </span>
                    @endif
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $levelColors[$level] ?? '' }}">
                        <i class="fa-solid fa-medal"></i> {{ $level }} Tier
                    </span>
                    @if($badge)
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $badgeColors[$badge] ?? '' }}">
                            <i class="fa-solid fa-trophy"></i> {{ $badge }}
                        </span>
                    @endif
                    <a href="{{ route('partner.upgrade') }}"
                       class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold transition"
                       style="background: linear-gradient(90deg, #f59e0b 0%, #f97316 100%); color: #1e293b; box-shadow: 0 6px 18px -6px rgba(245,158,11,.5), inset 0 1px 0 rgba(255,255,255,.35);"
                       onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">
                        <i class="fa-solid fa-rocket"></i> Upgrade Plan ({{ $plan }})
                    </a>
                </div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">
                    Overview
                </h1>
                <p class="text-blue-200 mt-2 text-lg">
                    Welcome back, <span class="text-white font-semibold">{{ Auth::user()->name }}</span>.
                </p>
            </div>

            <div class="mt-6 md:mt-0">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 px-6 py-3 rounded-2xl flex items-center gap-4">
                    <div class="p-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg shadow-lg">
                        <i class="fa-regular fa-calendar text-white"></i>
                    </div>
                    <div>
                        <p class="text-xs text-blue-300 font-bold uppercase">Today's Date</p>
                        <p class="text-white font-bold">{{ date('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-12">
            <div class="col-span-1 lg:col-span-2 bg-gradient-to-r from-indigo-600/90 to-blue-600/90 rounded-3xl p-1 shadow-2xl">
                <div class="h-full bg-slate-900/50 backdrop-blur-xl rounded-[20px] p-8 relative overflow-hidden">
                    <div class="absolute right-0 top-0 p-6 opacity-10">
                        <i class="fa-solid fa-users-viewfinder text-9xl text-white"></i>
                    </div>

                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <span class="bg-white/20 p-2 rounded-lg"><i class="fa-solid fa-heart-pulse"></i></span>
                            <h3 class="font-bold text-xl text-white">Daily Pulse</h3>
                        </div>

                        <a href="{{ route('partner.applications', ['interview_today' => 1]) }}" class="inline-flex items-baseline gap-4 rounded-xl px-2 py-1 hover:bg-white/10 transition">
                            <span class="text-6xl font-black text-white tracking-tighter">{{ $todayInterviews ?? 0 }}</span>
                            <span class="text-blue-200 font-medium">Interviews Today</span>
                        </a>

                        <div class="mt-8">
                            <a href="{{ route('partner.applications') }}" class="fx-btn inline-flex items-center gap-2 bg-white text-blue-900 px-6 py-3 rounded-xl font-bold hover:bg-blue-50 transition shadow-lg">
                                Check Application Status <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-8 hover:bg-white/15 transition duration-300">
                <div class="flex justify-between items-start mb-6">
                    <div class="p-3 bg-emerald-500/20 rounded-2xl text-emerald-400 border border-emerald-500/20">
                        <i class="fa-solid fa-user-plus text-2xl"></i>
                    </div>
                </div>

                <p class="text-blue-300 text-sm font-bold uppercase tracking-wider">Quick Action</p>
                <p class="text-2xl font-extrabold text-white mt-2">Add Candidate</p>
                <p class="text-slate-300 text-sm mt-1">Start the candidate onboarding flow.</p>

                <div class="mt-8 pt-6 border-t border-white/10">
                    <a href="{{ route('partner.candidates.check') }}" class="w-full flex items-center justify-between text-white font-bold hover:text-emerald-400 transition-colors">
                        <span>Open</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <h3 class="text-xl font-bold text-white mb-6 flex items-center gap-3">
            <span class="w-1.5 h-8 bg-blue-500 rounded-full"></span> Quick Actions
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-5 gap-4">
            <a href="{{ route('partner.profile.business') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-blue-500/20 text-blue-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-user-gear"></i>
                </div>
                <h4 class="font-bold text-white">My Profile</h4>
                <p class="text-slate-400 text-xs">Manage business details</p>
            </a>

            <a href="{{ route('partner.candidates.index') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-emerald-500/20 text-emerald-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h4 class="font-bold text-white">Candidate Pool</h4>
                <p class="text-slate-400 text-xs">View and manage candidates</p>
            </a>

            <a href="{{ route('partner.jobs') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-indigo-500/20 text-indigo-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-briefcase"></i>
                </div>
                <h4 class="font-bold text-white">Available Jobs</h4>
                <p class="text-slate-400 text-xs">Browse approved roles</p>
            </a>

            <a href="{{ route('partner.applications') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-purple-500/20 text-purple-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-file-circle-check"></i>
                </div>
                <h4 class="font-bold text-white">Applications</h4>
                <p class="text-slate-400 text-xs">Track submissions</p>
            </a>

            @if(Auth::user()->canSeeCommercials())
            <a href="{{ route('partner.earnings') }}" class="group fx-card bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5 hover:bg-white/20 hover:-translate-y-1 transition-all">
                <div class="h-10 w-10 bg-amber-500/20 text-amber-400 rounded-lg flex items-center justify-center mb-3">
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <h4 class="font-bold text-white">Earnings</h4>
                <p class="text-slate-400 text-xs">Track payouts</p>
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
