@extends('layouts.client')

@section('client_content')
    <style>
        .job-posting-card {
            isolation: isolate;
            border: 1px solid rgba(191, 219, 254, .22);
            background: rgba(6, 15, 42, .94);
            transition: transform .3s ease, border-color .3s ease, box-shadow .3s ease;
        }
        .job-posting-card::before {
            content: '';
            position: absolute;
            z-index: -1;
            inset: 0;
            background: var(--job-gradient);
            opacity: .22;
        }
        .job-posting-card::after {
            content: '';
            position: absolute;
            z-index: 0;
            top: -25%;
            left: -65%;
            width: 36%;
            height: 155%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.16), transparent);
            transform: skewX(-18deg);
            transition: left .7s ease;
            pointer-events: none;
        }
        .job-posting-card:hover {
            transform: translateY(-6px);
            border-color: rgba(125, 211, 252, .58);
            box-shadow: 0 22px 44px rgba(3, 12, 38, .34), 0 0 0 1px rgba(255,255,255,.05) inset;
        }
        .job-posting-card:hover::after { left: 135%; }
        .job-posting-card > * { position: relative; z-index: 1; }
        .job-filter-control {
            border: 1px solid rgba(148, 163, 184, .2);
            background: rgba(3, 12, 36, .57);
            color: #eff6ff;
        }
        .job-filter-control:focus { border-color: rgba(34, 211, 238, .72); box-shadow: 0 0 0 3px rgba(34, 211, 238, .13); }
        .job-filter-grid { display: grid; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 980px) {
            .job-filter-grid {
                grid-template-columns: minmax(11rem, 1.35fr) minmax(8rem, .85fr) minmax(9rem, .95fr) 8.5rem 10rem 8.5rem auto;
                align-items: center;
            }
        }
    </style>
    <div class="relative z-10 max-w-7xl mx-auto">
        
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-end mb-10 border-b border-white/10 pb-6">
            <div>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">My Job Postings</h1>
                <p class="text-blue-200 mt-2 text-lg">Manage all your requirements and track their status.</p>
            </div>
            <div class="mt-6 md:mt-0">
                <a href="{{ route('client.jobs.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white px-5 py-2.5 rounded-xl font-bold transition shadow-lg hover:shadow-blue-500/50">
                    <i class="fa-solid fa-plus"></i> Post New Job
                </a>
            </div>
        </div>

        {{-- Status Pills --}}
        <div class="flex flex-wrap items-center gap-3 mb-4 glass-card p-2 rounded-2xl">
            <a href="{{ route('client.jobs.index', request()->except(['status', 'page'])) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ !request('status') ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                All <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ !request('status') ? 'bg-blue-400/40 text-white border border-blue-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', array_merge(request()->except(['status', 'page']), ['status' => 'approved'])) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'approved' ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                Active <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'approved' ? 'bg-emerald-400/40 text-white border border-emerald-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['approved'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'pending' ? 'bg-amber-500 text-slate-900 shadow-lg shadow-amber-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                Pending Approval <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'pending' ? 'bg-amber-700/20 text-slate-900 border border-slate-900/10' : 'bg-white/10 text-blue-200' }}">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', array_merge(request()->except(['status', 'page']), ['status' => 'hold'])) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'hold' ? 'bg-orange-500 text-white shadow-lg shadow-orange-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                On Hold <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'hold' ? 'bg-orange-400/40 text-white border border-orange-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['hold'] }}</span>
            </a>
            <a href="{{ route('client.jobs.index', array_merge(request()->except(['status', 'page']), ['status' => 'closed'])) }}" class="px-5 py-2 text-sm font-bold rounded-xl transition-all {{ request('status') === 'closed' ? 'bg-rose-600 text-white shadow-lg shadow-rose-500/30' : 'text-blue-200 hover:bg-white/10 hover:text-white' }}">
                Closed <span class="ml-1.5 inline-flex items-center justify-center px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ request('status') === 'closed' ? 'bg-rose-400/40 text-white border border-rose-300/30' : 'bg-white/10 text-blue-200' }}">{{ $counts['closed'] }}</span>
            </a>
        </div>

        <form method="GET" action="{{ route('client.jobs.index') }}" class="job-filter-grid mb-8 gap-3 rounded-2xl border border-white/10 bg-slate-950/35 p-3 backdrop-blur-xl">
            <label class="sr-only" for="job-search">Job title</label>
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-cyan-300"></i>
                <input id="job-search" name="search" value="{{ request('search') }}" placeholder="Job title"
                       class="job-filter-control w-full rounded-xl py-3 pl-10 pr-4 text-sm outline-none placeholder:text-slate-400">
            </div>
            <label class="sr-only" for="job-location">Location</label>
            <input id="job-location" name="location" value="{{ request('location') }}" placeholder="Location" class="job-filter-control w-full rounded-xl px-3 py-3 text-sm outline-none placeholder:text-slate-400">
            <label class="sr-only" for="job-keywords">Skills or keywords</label>
            <input id="job-keywords" name="keywords" value="{{ request('keywords') }}" placeholder="Skills / keywords" class="job-filter-control w-full rounded-xl px-3 py-3 text-sm outline-none placeholder:text-slate-400">
            <label class="sr-only" for="job-status">Job status</label>
            <select id="job-status" name="status" class="job-filter-control w-full rounded-xl px-3 py-3 pr-8 text-sm font-semibold outline-none" style="appearance: auto; -webkit-appearance: menulist;">
                <option value="">All statuses</option>
                <option value="approved" @selected(request('status') === 'approved')>Active</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="hold" @selected(request('status') === 'hold')>On hold</option>
                <option value="closed" @selected(request('status') === 'closed')>Closed</option>
            </select>
            <label class="sr-only" for="job-flow">Hiring flow</label>
            <select id="job-flow" name="flow" class="job-filter-control w-full rounded-xl px-3 py-3 pr-8 text-sm font-semibold outline-none" style="appearance: auto; -webkit-appearance: menulist;">
                <option value="">All hiring flows</option>
                <option value="screening" @selected(request('flow') === 'screening')>Screening required</option>
                <option value="direct" @selected(request('flow') === 'direct')>Direct interview</option>
            </select>
            <label class="sr-only" for="job-sort">Sort jobs</label>
            <select id="job-sort" name="sort" class="job-filter-control w-full rounded-xl px-3 py-3 pr-8 text-sm font-semibold outline-none" style="appearance: auto; -webkit-appearance: menulist;">
                <option value="recent" @selected(request('sort', 'recent') === 'recent')>Newest first</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest first</option>
                <option value="title" @selected(request('sort') === 'title')>Title A-Z</option>
                <option value="responses" @selected(request('sort') === 'responses')>Most responses</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-cyan-400 px-4 py-3 text-sm font-extrabold text-slate-950 transition hover:bg-cyan-300">
                    <i class="fa-solid fa-sliders"></i> Apply
                </button>
                @if(request()->anyFilled(['search', 'location', 'keywords', 'status', 'flow']) || request('sort', 'recent') !== 'recent')
                    <a href="{{ route('client.jobs.index') }}" class="inline-flex items-center justify-center rounded-xl border border-white/15 bg-white/5 px-3 text-sm font-bold text-white transition hover:bg-white/10" title="Clear filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        @if(session('job_posted'))
            <style>
                @keyframes jobPostedBackdropIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes jobPostedCardIn {
                    from { transform: translateY(18px) scale(.96); }
                    to { transform: translateY(0) scale(1); }
                }
                @keyframes jobPostedCheckIn {
                    0% { opacity: 0; transform: scale(.55) rotate(-12deg); }
                    70% { opacity: 1; transform: scale(1.08) rotate(2deg); }
                    100% { opacity: 1; transform: scale(1) rotate(0); }
                }
                #job-post-success-modal {
                    position: fixed !important;
                    inset: 0 !important;
                    z-index: 2147483000 !important;
                    isolation: isolate;
                    animation: jobPostedBackdropIn .22s ease-out both;
                }
                #job-post-success-modal .job-post-success-backdrop {
                    position: absolute;
                    inset: 0;
                    z-index: 0;
                    background: rgba(2, 6, 23, .88) !important;
                    -webkit-backdrop-filter: blur(10px);
                    backdrop-filter: blur(10px);
                }
                #job-post-success-modal .job-post-success-card {
                    z-index: 1;
                    max-height: calc(100vh - 2rem);
                    overflow-y: auto;
                    background: #050b1d !important;
                    opacity: 1 !important;
                    box-shadow: 0 30px 90px rgba(0, 0, 0, .7), 0 0 0 1px rgba(110, 231, 183, .14) inset !important;
                    animation: jobPostedCardIn .38s cubic-bezier(.2,.8,.2,1) both;
                }
                #job-post-success-modal .job-post-success-check {
                    animation: jobPostedCheckIn .5s .12s cubic-bezier(.2,.8,.2,1) both;
                }
            </style>

            <div id="job-post-success-modal"
                 class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
                 role="dialog"
                 aria-modal="true"
                 aria-labelledby="job-post-success-title">
                <button type="button"
                        class="job-post-success-backdrop h-full w-full cursor-default"
                        aria-label="Close success message"
                        onclick="closeJobPostSuccessModal()"></button>

                <div class="job-post-success-card relative w-full max-w-md overflow-hidden rounded-3xl border border-emerald-300/25 bg-slate-950 px-7 py-8 text-center shadow-2xl shadow-emerald-950/50">
                    <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-400 via-emerald-400 to-lime-300"></div>
                    <button type="button"
                            class="absolute right-4 top-4 flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-slate-300 transition hover:bg-white/10 hover:text-white"
                            aria-label="Close success message"
                            onclick="closeJobPostSuccessModal()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>

                    <div class="job-post-success-check mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-full border border-emerald-300/35 bg-emerald-400/15 text-4xl text-emerald-300 shadow-lg shadow-emerald-500/15">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <p class="mb-2 text-xs font-extrabold uppercase text-emerald-300">Submission received</p>
                    <h2 id="job-post-success-title" class="mb-3 text-2xl font-extrabold text-white">Job posted successfully!</h2>
                    <p class="mx-auto mb-7 max-w-sm text-sm leading-6 text-slate-300">
                        {{ session('job_posted') }}
                    </p>
                    <button type="button"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 px-5 py-3 text-sm font-extrabold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:bg-emerald-400"
                            onclick="closeJobPostSuccessModal()">
                        <i class="fa-solid fa-briefcase"></i>
                        View my jobs
                    </button>
                </div>
            </div>
            <script>
                function closeJobPostSuccessModal() {
                    document.getElementById('job-post-success-modal')?.remove();
                    document.documentElement.style.overflow = '';
                    document.body.style.overflow = '';
                }

                (() => {
                    const mountModal = () => {
                        const modal = document.getElementById('job-post-success-modal');
                        if (!modal) return;
                        document.body.appendChild(modal);
                        document.documentElement.style.overflow = 'hidden';
                        document.body.style.overflow = 'hidden';
                        document.addEventListener('keydown', event => {
                            if (event.key === 'Escape') closeJobPostSuccessModal();
                        });
                    };
                    document.readyState === 'loading'
                        ? document.addEventListener('DOMContentLoaded', mountModal, { once: true })
                        : mountModal();
                })();
            </script>
        @endif

        @if(session('success'))
            <div class="mb-6 px-5 py-3 rounded-2xl flex items-start gap-3 shadow-lg"
                 style="background: rgba(16,185,129,0.15); border: 1px solid rgba(52,211,153,0.4); color: #d1fae5;"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(()=>show=false, 6000)" x-transition>
                <i class="fa-solid fa-circle-check text-emerald-300 text-xl mt-0.5"></i>
                <div class="flex-1 font-semibold">{{ session('success') }}</div>
                <button type="button" @click="show=false" class="text-emerald-200 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        {{-- Job List --}}
        @if($jobs->isEmpty())
            <div class="glass-card rounded-3xl shadow-xl p-16 text-center">
                <div class="w-24 h-24 bg-blue-500/10 border border-blue-400/20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fa-regular fa-folder-open text-5xl text-blue-300"></i>
                </div>
                <h3 class="text-2xl font-bold text-white mb-2">No jobs found</h3>
                <p class="text-blue-200 text-sm max-w-md mx-auto mb-8">You don't have any job postings matching the current criteria. Start by posting a new requirement.</p>
                @if(!request('status'))
                    <a href="{{ route('client.jobs.create') }}" class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-500 text-white font-extrabold rounded-xl shadow-lg transition-colors gap-2">
                        <i class="fa-solid fa-plus"></i> Post Your First Job
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($jobs as $job)
                    @php
                        $jobGradients = [
                            'linear-gradient(135deg, rgba(236, 72, 153, .62), rgba(74, 18, 73, .22) 43%, rgba(8, 145, 178, .34) 100%)',
                            'linear-gradient(135deg, rgba(59, 130, 246, .63), rgba(29, 42, 102, .2) 43%, rgba(129, 140, 248, .48) 100%)',
                            'linear-gradient(135deg, rgba(245, 158, 11, .65), rgba(91, 42, 14, .2) 43%, rgba(244, 63, 94, .43) 100%)',
                            'linear-gradient(135deg, rgba(16, 185, 129, .62), rgba(9, 65, 68, .2) 43%, rgba(6, 182, 212, .45) 100%)',
                            'linear-gradient(135deg, rgba(168, 85, 247, .62), rgba(60, 26, 113, .2) 43%, rgba(236, 72, 153, .44) 100%)',
                            'linear-gradient(135deg, rgba(249, 115, 22, .64), rgba(90, 35, 26, .2) 43%, rgba(234, 179, 8, .44) 100%)',
                        ];
                    @endphp
                    <div class="job-posting-card rounded-2xl shadow-xl flex flex-col active:scale-[0.98] relative group overflow-hidden" style="--job-gradient: {{ $jobGradients[$loop->index % count($jobGradients)] }};">
                        
                        {{-- Top Border color based on status --}}
                        <div class="absolute top-0 left-0 w-full h-px rounded-t-2xl
                            @if($job->status === 'approved') bg-emerald-500
                            @elseif($job->status === 'pending_approval') bg-amber-400
                            @elseif($job->status === 'on_hold') bg-orange-500
                            @elseif($job->status === 'closed') bg-rose-500
                            @else bg-slate-400 @endif
                        "></div>

                        <div class="p-6 flex-1 pt-8">
                            <div class="flex justify-between items-start mb-4 gap-3">
                                <a href="{{ route('client.jobs.applicants', $job) }}" class="text-lg font-bold text-white line-clamp-2 leading-tight group-hover:text-cyan-100 transition duration-200" title="Review responses for {{ $job->title }}">{{ $job->title }}</a>
                                
                                <div class="shrink-0">
                                    @if($job->status === 'approved')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            <i class="fa-solid fa-circle-play mr-1"></i> Active
                                        </span>
                                    @elseif($job->status === 'pending_approval')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider bg-amber-500/25 text-amber-400 border border-amber-500/35 animate-pulse">
                                            <i class="fa-regular fa-clock mr-1"></i> Pending
                                        </span>
                                    @elseif($job->status === 'on_hold')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider bg-orange-500/20 text-orange-300 border border-orange-500/30">
                                            <i class="fa-solid fa-pause mr-1"></i> On Hold
                                        </span>
                                    @elseif($job->status === 'closed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            <i class="fa-solid fa-circle-stop mr-1"></i> Closed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-extrabold uppercase tracking-wider bg-slate-500/20 text-slate-300 border border-slate-500/30">
                                            <i class="fa-solid fa-circle-info mr-1"></i> {{ ucwords(str_replace('_', ' ', $job->status)) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="space-y-2.5 mt-5 text-xs">
                                <div class="flex items-center gap-3">
                                    <div class="w-5 flex justify-center text-cyan-400 shrink-0"><i class="fa-solid fa-location-dot"></i></div>
                                    <span class="truncate text-slate-300 font-medium">{{ $job->location ?: 'Location not specified' }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-5 flex justify-center text-cyan-400 shrink-0"><i class="fa-solid fa-briefcase"></i></div>
                                    <span class="text-slate-300 font-medium">{{ $job->formatted_experience ?? (($job->min_experience ?? '') . '-' . ($job->max_experience ?? '') . ' yrs') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-5 flex justify-center text-violet-300 shrink-0"><i class="fa-solid fa-business-time"></i></div>
                                    <span class="text-slate-300 font-medium">{{ $job->job_type ?? 'Type not specified' }}</span>
                                </div>
                                <div class="flex items-center gap-3" title="Posted {{ $job->created_at->format('d M Y') }}">
                                    <div class="w-5 flex justify-center text-amber-300 shrink-0"><i class="fa-regular fa-clock"></i></div>
                                    <span class="text-slate-300 font-medium">Posted {{ $job->created_at->diffForHumans() }} &middot; {{ $job->created_at->format('d M Y') }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-5 flex justify-center text-cyan-400 shrink-0"><i class="fa-solid fa-indian-rupee-sign"></i></div>
                                    <span class="text-slate-300 font-medium">{{ $job->salary ?: 'Salary unlisted' }}</span>
                                </div>
                                <div class="mt-3 rounded-xl border border-amber-300/20 bg-amber-300/[0.07] px-3 py-2.5">
                                    <div class="flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-wider text-amber-200">
                                        <i class="fa-solid fa-coins"></i>
                                        Client commercial
                                    </div>
                                    @if($job->commercial_source === 'manual')
                                        <div class="mt-1 text-xs font-bold text-white">
                                            Manual override:
                                            @if($job->fee_type === 'percentage')
                                                {{ rtrim(rtrim(number_format((float) $job->fee_amount, 2), '0'), '.') }}%
                                            @else
                                                ₹{{ number_format((float) $job->fee_amount, 2) }}
                                            @endif
                                        </div>
                                        <div class="mt-1 text-[10px] text-amber-100/75">
                                            {{ $job->client_payout_days ?? $job->minimum_stay_days ?? '—' }}-day maturity
                                            &middot;
                                            {{ $job->replacement_period_days ?? $job->replacement_guarantee_days ?? '—' }}-day replacement
                                        </div>
                                    @else
                                        <div class="mt-1 text-xs font-bold text-cyan-100">SimplyHire client agreement</div>
                                        <div class="mt-1 text-[10px] text-blue-200/75">Your account-level commercial applies.</div>
                                    @endif
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2 border-t border-white/5 pt-3">
                                    <div class="w-5 flex justify-center text-emerald-400 shrink-0"><i class="fa-solid fa-users"></i></div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-emerald-500/15 border border-emerald-500/30 text-[10px] font-extrabold text-emerald-300 uppercase tracking-wide">
                                        {{ $job->job_applications_count }} Responses
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-cyan-500/15 border border-cyan-500/25 text-[10px] font-extrabold text-cyan-200 uppercase tracking-wide">
                                        {{ $job->shortlisted_responses_count }} Shortlisted
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-500/15 border border-amber-500/25 text-[10px] font-extrabold text-amber-200 uppercase tracking-wide">
                                        {{ $job->maybe_responses_count }} Maybe
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-rose-500/15 border border-rose-500/25 text-[10px] font-extrabold text-rose-200 uppercase tracking-wide">
                                        {{ $job->rejected_responses_count }} Rejected
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-[#03071a]/50 border-t border-white/10 p-4 flex items-center justify-between gap-3 relative">
                            <a href="{{ route('client.jobs.applicants', $job->id) }}" class="flex-1 inline-flex justify-center items-center px-4 py-2.5 bg-blue-600/15 hover:bg-blue-600 border border-blue-500/30 shadow-sm text-xs font-bold rounded-xl text-white transition-all duration-200 gap-2">
                                <i class="fa-regular fa-eye text-cyan-200"></i> Review Responses
                            </a>
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" class="p-2.5 text-blue-200 hover:text-white rounded-xl hover:bg-white/10 transition border border-transparent">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                
                                <div x-show="open" x-transition style="display: none;" class="absolute bottom-full right-0 mb-2 w-48 rounded-2xl shadow-xl glass-card border border-white/15 ring-1 ring-black/20 divide-y divide-white/5 overflow-hidden z-30">
                                    <div class="py-1">
                                        <a href="{{ route('client.jobs.edit', $job->id) }}" class="group flex items-center px-4 py-2.5 text-sm text-slate-200 hover:bg-white/5 hover:text-white">
                                            <i class="fa-solid fa-pen-to-square w-5 text-slate-400 group-hover:text-blue-400"></i> Edit Job
                                        </a>
                                        @if($job->status === 'approved')
                                            <form action="{{ route('client.jobs.request-deactivation', $job->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full text-left group flex items-center px-4 py-2.5 text-sm text-slate-200 hover:bg-rose-500/10 hover:text-rose-400 transition">
                                                    <i class="fa-solid fa-ban w-5 text-slate-400 group-hover:text-rose-400"></i> Request Closure
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-8">
                {{ $jobs->links() }}
            </div>
        @endif

    </div>
@endsection
