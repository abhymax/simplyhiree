@extends('layouts.client')

@section('client_content')
<style>
    .responses-shell { max-width: 1280px; }
    .response-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(125, 211, 252, .23);
        background: linear-gradient(128deg, rgba(15, 42, 94, .94), rgba(24, 32, 74, .88) 58%, rgba(18, 65, 91, .75));
        box-shadow: 0 24px 56px rgba(2, 8, 29, .32);
        min-height: 172px;
    }
    .response-hero::after {
        content: '';
        position: absolute;
        right: -5rem;
        top: -8rem;
        width: 20rem;
        height: 20rem;
        border-radius: 999px;
        background: rgba(34, 211, 238, .13);
        filter: blur(42px);
        pointer-events: none;
    }
    .response-tab { transition: color .2s ease, background .2s ease, transform .2s ease; }
    .response-tab:hover { transform: translateY(-1px); }
    .candidate-response {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, .18);
        background: linear-gradient(115deg, rgba(7, 17, 42, .94), rgba(16, 39, 82, .84));
        transition: transform .24s ease, border-color .24s ease, box-shadow .24s ease;
    }
    .candidate-response::before {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: linear-gradient(#22d3ee, #6366f1);
        opacity: .55;
    }
    .candidate-response:hover {
        transform: translateY(-3px);
        border-color: rgba(34, 211, 238, .48);
        box-shadow: 0 18px 38px rgba(3, 12, 34, .34);
    }
    .candidate-action { transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; }
    .candidate-action:hover { transform: translateY(-2px); filter: brightness(1.08); box-shadow: 0 10px 20px rgba(2, 8, 23, .28); }
    .candidate-fact { display: grid; grid-template-columns: 5.6rem minmax(0, 1fr); gap: .75rem; padding: .45rem 0; border-bottom: 1px solid rgba(255,255,255,.07); }
    .candidate-fact:last-child { border-bottom: 0; }
    .candidate-fact-label { color: #67e8f9; font-size: .65rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .candidate-fact-value { color: #e2e8f0; font-size: .82rem; line-height: 1.35; }
    .candidate-summary { background: linear-gradient(145deg, rgba(255,255,255,.09), rgba(255,255,255,.025)); }
    .candidate-response-grid { display: grid; gap: 1.5rem; }
    .candidate-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .55rem; margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,.09); }
    .candidate-response { border-color: rgba(2,6,23,.92); }
    .candidate-response:nth-child(4n+1) { background: linear-gradient(115deg, rgba(8,47,73,.74), rgba(7,17,42,.94) 62%); }
    .candidate-response:nth-child(4n+1)::before { background: linear-gradient(#22d3ee, #2563eb); }
    .candidate-response:nth-child(4n+2) { background: linear-gradient(115deg, rgba(67,20,78,.58), rgba(7,17,42,.94) 62%); }
    .candidate-response:nth-child(4n+2)::before { background: linear-gradient(#e879f9, #7c3aed); }
    .candidate-response:nth-child(4n+3) { background: linear-gradient(115deg, rgba(88,45,5,.52), rgba(7,17,42,.94) 62%); }
    .candidate-response:nth-child(4n+3)::before { background: linear-gradient(#fbbf24, #f97316); }
    .candidate-response:nth-child(4n+4) { background: linear-gradient(115deg, rgba(6,78,59,.54), rgba(7,17,42,.94) 62%); }
    .candidate-response:nth-child(4n+4)::before { background: linear-gradient(#34d399, #0ea5e9); }
    @media (min-width: 1024px) { .candidate-response-grid { grid-template-columns: minmax(15rem, 1.05fr) minmax(20rem, 1.45fr) 12rem; align-items: start; } }
</style>

<div class="responses-shell relative z-10 mx-auto pb-8">
    @if(session('success'))
        <div class="mb-6 flex items-start gap-3 rounded-2xl border border-emerald-300/30 bg-emerald-500/15 px-5 py-4 text-emerald-50 shadow-lg">
            <i class="fa-solid fa-circle-check mt-0.5 text-emerald-300"></i>
            <p class="font-semibold">{{ session('success') }}</p>
        </div>
    @endif

    <section class="response-hero rounded-3xl px-7 py-8 md:px-9 md:py-9">
        <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <a href="{{ route('client.jobs.index') }}" class="inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider text-cyan-300 transition hover:text-white">
                    <i class="fa-solid fa-arrow-left"></i> My Jobs
                </a>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <h1 class="break-words text-3xl font-black leading-tight tracking-tight text-white md:text-4xl">{{ $job->title }}</h1>
                    <span class="rounded-full border border-cyan-300/30 bg-cyan-400/10 px-3 py-1 text-[11px] font-extrabold uppercase tracking-wide text-cyan-100">
                        {{ $responseCounts['all'] }} responses
                    </span>
                </div>
                <div class="mt-5 flex flex-wrap gap-x-7 gap-y-3 pb-1 text-sm leading-relaxed text-blue-100">
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-location-dot text-cyan-300"></i>{{ $job->location ?: 'Location not specified' }}</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-briefcase text-cyan-300"></i>{{ $job->formatted_experience ?? trim(($job->min_experience ?? '').' - '.($job->max_experience ?? '').' years') }}</span>
                    <span class="inline-flex items-center gap-2"><i class="fa-solid fa-indian-rupee-sign text-cyan-300"></i>{{ $job->salary ?: 'Salary not listed' }}</span>
                </div>
            </div>
            <a href="{{ route('client.jobs.edit', $job) }}" class="candidate-action inline-flex items-center justify-center gap-2 rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm font-bold text-white hover:bg-white/20">
                <i class="fa-solid fa-pen-to-square text-cyan-200"></i> Job details
            </a>
        </div>
    </section>

    <section class="mt-6 rounded-3xl border border-white/10 bg-slate-950/35 p-3 shadow-xl backdrop-blur-xl">
        <div class="flex flex-wrap gap-2" role="tablist" aria-label="Candidate response status">
            @php
                $tabs = [
                    'all' => ['All Responses', 'bg-blue-600', 'text-blue-100'],
                    'shortlisted' => ['Shortlisted', 'bg-emerald-600', 'text-emerald-100'],
                    'maybe' => ['Maybe', 'bg-amber-500', 'text-amber-100'],
                    'rejected' => ['Rejected', 'bg-rose-600', 'text-rose-100'],
                ];
            @endphp
            @foreach($tabs as $key => [$label, $activeColor, $textColor])
                <a href="{{ route('client.jobs.applicants', array_filter(['job' => $job, 'response' => $key, 'search' => request('search')])) }}"
                   class="response-tab inline-flex items-center gap-2 rounded-2xl px-4 py-3 text-sm font-extrabold {{ $responseFilter === $key ? $activeColor.' text-white shadow-lg' : $textColor.' hover:bg-white/10 hover:text-white' }}"
                   role="tab" aria-selected="{{ $responseFilter === $key ? 'true' : 'false' }}">
                    {{ $label }}
                    <span class="inline-flex min-w-6 items-center justify-center rounded-full px-1.5 py-0.5 text-[10px] {{ $responseFilter === $key ? 'bg-white/20' : 'bg-white/10' }}">{{ $responseCounts[$key] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <div class="mt-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-xl font-black text-white">Candidate responses</h2>
            <p class="mt-1 text-sm text-blue-200">Review one profile at a time and keep the hiring decision visible.</p>
        </div>
        <form method="GET" action="{{ route('client.jobs.applicants', $job) }}" class="flex w-full gap-2 md:w-auto">
            <input type="hidden" name="response" value="{{ $responseFilter }}">
            <label class="sr-only" for="candidate-search">Search responses</label>
            <input id="candidate-search" name="search" value="{{ request('search') }}" placeholder="Search name, email or code"
                   class="w-full rounded-xl border border-white/15 bg-slate-950/55 px-4 py-2.5 text-sm text-white placeholder:text-slate-400 outline-none transition focus:border-cyan-300 focus:ring-2 focus:ring-cyan-400/25 md:w-72">
            <button type="submit" class="candidate-action inline-flex shrink-0 items-center justify-center rounded-xl bg-cyan-400 px-4 py-2.5 text-sm font-extrabold text-slate-950">
                <i class="fa-solid fa-magnifying-glass"></i><span class="sr-only">Search</span>
            </button>
        </form>
    </div>

    <section class="mt-5 space-y-4">
        @forelse($applications as $app)
            @include('client.jobs.partials.response-card', ['app' => $app])
            <div class="hidden" aria-hidden="true">
            @php
                $candidate = $app->candidate;
                $candidateUser = $app->candidateUser;
                $name = $candidate ? trim(($candidate->first_name ?? '').' '.($candidate->last_name ?? '')) : ($candidateUser->name ?? 'Candidate');
                $name = $name ?: 'Candidate';
                $initial = strtoupper(substr($name, 0, 1));
                $email = $candidate->email ?? $candidateUser->email ?? 'Email not available';
                $phone = $candidate->phone_number ?? $candidateUser->phone ?? null;
                $resume = $candidate?->resume_path ?? $candidateUser?->profile?->resume_path;
                $reviewStatus = $app->hiring_status;
                $isRejected = $reviewStatus === 'Client Rejected';
                $isMaybe = $reviewStatus === 'Maybe';
                $isAwaitingReview = in_array($reviewStatus, [null, ''], true);
                $isShortlisted = in_array($reviewStatus, ['Shortlisted', 'shortlisted'], true);
                $isSelected = $reviewStatus === 'Selected';
                $isInterviewed = $reviewStatus === 'Interviewed';
                $canScheduleInterview = $isShortlisted || in_array($reviewStatus, ['Interview Scheduled', 'Interviewed', 'No-Show'], true);
                $locked = !empty($app->joined_status) || in_array($reviewStatus, ['Selected', 'Joined'], true);
                $statusLabel = $app->joined_status ?: ($isRejected ? 'Rejected' : ($isMaybe ? 'Maybe' : ($isShortlisted ? 'Shortlisted' : ($isAwaitingReview ? 'Screened by Admin' : $reviewStatus))));
                $statusStyle = $isRejected ? 'border-rose-300/30 bg-rose-500/15 text-rose-100' : ($isMaybe ? 'border-amber-300/30 bg-amber-500/15 text-amber-100' : ($isShortlisted ? 'border-emerald-300/30 bg-emerald-500/15 text-emerald-100' : 'border-cyan-300/30 bg-cyan-500/15 text-cyan-100'));
                $skills = $candidate->skills ?? null;
                $experience = $candidate->experience_status ?? null;
                $education = $candidate->education_level ?? null;
                $ctc = $candidate->expected_ctc ?? null;
                $latestRound = $app->interviewRounds->sortBy('round_number')->last();
                $preferredLocation = $candidate?->preferred_location ?? $candidate?->location ?? $candidateUser?->profile?->location ?? null;
                $currentRole = $candidate?->current_job_title ?? $candidate?->current_position ?? $candidateUser?->profile?->current_title ?? null;
                $noticePeriod = $candidate?->notice_period ?? $candidateUser?->profile?->notice_period ?? null;
                $isReadyForReview = $isAwaitingReview && !$locked;
                $contactHref = $phone ? 'tel:'.preg_replace('/[^0-9+]/', '', $phone) : 'mailto:'.$email;
            @endphp
            <article class="candidate-response rounded-3xl p-5 pl-6 md:p-6 md:pl-7">
                <div class="candidate-response-grid">
                    <div class="min-w-0">
                        <div class="min-w-0">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('client.applications.show', $app) }}" class="truncate text-lg font-black text-white transition hover:text-cyan-300">{{ $name }}</a>
                                    @if($isReadyForReview)<span class="rounded-full border border-amber-300/30 bg-amber-400/15 px-2 py-0.5 text-[9px] font-extrabold uppercase tracking-wide text-amber-100">Ready to review</span>@endif
                                    <span class="rounded-full border px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide {{ $statusStyle }}">{{ $statusLabel }}</span>
                                </div>
                                <p class="mt-1 truncate text-sm text-blue-200">{{ $email }}</p>
                                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-300">
                                    @if($phone)<span><i class="fa-solid fa-phone mr-1.5 text-cyan-300"></i>{{ $phone }}</span>@endif
                                    <span><i class="fa-regular fa-clock mr-1.5 text-cyan-300"></i>Applied {{ $app->created_at?->format('d M Y') }}</span>
                                </div>
                                <div class="mt-4 flex flex-wrap gap-2 text-[11px] font-bold text-slate-200">
                                    @if($experience)<span class="rounded-md border border-white/10 bg-white/5 px-2 py-1"><i class="fa-solid fa-briefcase mr-1 text-cyan-300"></i>{{ $experience }}</span>@endif
                                    @if($ctc)<span class="rounded-md border border-white/10 bg-white/5 px-2 py-1"><i class="fa-solid fa-indian-rupee-sign mr-1 text-cyan-300"></i>{{ $ctc }}</span>@endif
                                    @if($preferredLocation)<span class="rounded-md border border-white/10 bg-white/5 px-2 py-1"><i class="fa-solid fa-location-dot mr-1 text-cyan-300"></i>{{ $preferredLocation }}</span>@endif
                                </div>
                                <x-interview-rounds :rounds="$app->interviewRounds" />
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 rounded-2xl border border-white/8 bg-slate-950/25 px-4 py-3">
                        <div class="candidate-fact"><span class="candidate-fact-label">Current</span><span class="candidate-fact-value">{{ $currentRole ?: 'Current role not specified' }}</span></div>
                        <div class="candidate-fact"><span class="candidate-fact-label">Education</span><span class="candidate-fact-value">{{ $education ?: 'Not specified' }}</span></div>
                        <div class="candidate-fact"><span class="candidate-fact-label">Skills</span><span class="candidate-fact-value line-clamp-2">{{ $skills ?: 'Not specified' }}</span></div>
                        <div class="candidate-fact"><span class="candidate-fact-label">Availability</span><span class="candidate-fact-value">{{ $noticePeriod ?: 'Not specified' }}</span></div>
                    </div>

                    <div class="candidate-summary flex min-h-40 flex-col items-center justify-center rounded-2xl border border-white/10 p-4 text-center">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full border border-cyan-200/25 bg-cyan-400/10 text-xl font-black text-cyan-100">{{ $initial }}</div>
                        <p class="mt-3 text-xs font-bold text-slate-100">{{ $resume ? 'CV attached' : 'Profile available' }}</p>
                        <p class="mt-1 text-[11px] text-slate-400">Candidate snapshot</p>
                        <a href="{{ $contactHref }}" class="candidate-action mt-3 inline-flex items-center gap-2 rounded-xl border border-cyan-300/30 bg-cyan-500/10 px-3 py-2 text-xs font-extrabold text-cyan-100 hover:bg-cyan-500/20"><i class="fa-solid fa-phone"></i> Contact</a>
                    </div>
                </div>

                @if($app->assessmentBadge())
                    <div class="mt-3 inline-flex flex-wrap items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2">
                        <span class="text-[11px] font-bold uppercase tracking-wide text-slate-400"><i class="fa-solid fa-clipboard-check mr-1 text-violet-300"></i>Assessment</span>
                        @include('applications._assessment_cell', ['app' => $app, 'showRoute' => 'client.assessment-results.show'])
                    </div>
                @endif

                <div class="candidate-actions">
                    <a href="{{ route('client.applications.show', $app) }}" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-cyan-200/45 bg-gradient-to-r from-cyan-500 to-blue-600 px-3 py-2.5 text-xs font-extrabold text-white shadow-md shadow-cyan-950/35 hover:from-cyan-400 hover:to-blue-500">
                            <i class="fa-regular fa-user"></i> Profile
                        </a>
                        @if($resume)
                            <a href="{{ asset('storage/'.$resume) }}" target="_blank" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-violet-200/35 bg-gradient-to-r from-indigo-500 to-violet-600 px-3 py-2.5 text-xs font-extrabold text-white shadow-md shadow-indigo-950/35 hover:from-indigo-400 hover:to-violet-500">
                                <i class="fa-regular fa-file-lines"></i> CV
                            </a>
                        @endif
                    @if(!$locked)
                        @if($isShortlisted)
                            <form method="POST" action="{{ route('client.applications.undo-review', $app) }}">@csrf<button type="submit" title="Click to unshortlist" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-emerald-300/50 bg-emerald-500/30 px-3 py-2.5 text-xs font-extrabold text-emerald-50"><i class="fa-solid fa-check"></i> Shortlisted <i class="fa-solid fa-rotate-left text-[10px]"></i></button></form>
                        @else
                            <form method="POST" action="{{ route('client.applications.shortlist', $app) }}">@csrf<button type="submit" class="candidate-action rounded-xl border border-emerald-200/45 bg-gradient-to-r from-emerald-500 to-teal-600 px-3 py-2.5 text-xs font-extrabold text-white shadow-md shadow-emerald-950/35 hover:from-emerald-400 hover:to-teal-500"><i class="fa-solid fa-check mr-1"></i> Shortlist</button></form>
                        @endif

                        @if($isMaybe)
                            <form method="POST" action="{{ route('client.applications.undo-review', $app) }}">@csrf<button type="submit" title="Click to remove from Maybe" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-amber-300/55 bg-amber-400/30 px-3 py-2.5 text-xs font-extrabold text-amber-50"><i class="fa-regular fa-bookmark"></i> Saved to Maybe <i class="fa-solid fa-rotate-left text-[10px]"></i></button></form>
                        @else
                            <form method="POST" action="{{ route('client.applications.maybe', $app) }}">@csrf<button type="submit" class="candidate-action rounded-xl border border-amber-200/45 bg-gradient-to-r from-amber-400 to-orange-500 px-3 py-2.5 text-xs font-extrabold text-slate-950 shadow-md shadow-amber-950/35 hover:from-amber-300 hover:to-orange-400"><i class="fa-regular fa-bookmark mr-1"></i> Maybe</button></form>
                        @endif

                        @if($isRejected)
                            <form method="POST" action="{{ route('client.applications.undo-review', $app) }}">@csrf<button type="submit" title="Click to reopen this candidate" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-rose-300/50 bg-rose-500/30 px-3 py-2.5 text-xs font-extrabold text-rose-50"><i class="fa-solid fa-xmark"></i> Rejected <i class="fa-solid fa-rotate-left text-[10px]"></i></button></form>
                        @else
                            <form method="POST" action="{{ route('client.applications.reject', $app) }}">@csrf<button type="submit" class="candidate-action rounded-xl border border-rose-200/40 bg-gradient-to-r from-rose-500 to-pink-600 px-3 py-2.5 text-xs font-extrabold text-white shadow-md shadow-rose-950/35 hover:from-rose-400 hover:to-pink-500"><i class="fa-solid fa-xmark mr-1"></i> Reject</button></form>
                        @endif
                    @endif

                    @if($canScheduleInterview && !$locked)
                        <a href="{{ route('client.applications.rounds.create', $app) }}" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-indigo-300/45 bg-gradient-to-r from-indigo-500/65 to-violet-500/65 px-3 py-2.5 text-xs font-extrabold text-white hover:from-indigo-400 hover:to-violet-400"><i class="fa-regular fa-calendar-plus"></i> {{ $latestRound ? 'Schedule next round' : 'Schedule interview' }}</a>
                    @endif

                    @if($isInterviewed)
                        <a href="{{ route('client.applications.select.show', $app) }}" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-fuchsia-300/45 bg-gradient-to-r from-fuchsia-500/70 to-purple-500/70 px-3 py-2.5 text-xs font-extrabold text-white hover:from-fuchsia-400 hover:to-purple-400"><i class="fa-solid fa-user-check"></i> Select Candidate</a>
                    @elseif($isSelected && empty($app->joined_status))
                        <a href="{{ route('client.applications.select.edit', $app) }}" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-fuchsia-300/45 bg-gradient-to-r from-fuchsia-500/70 to-purple-500/70 px-3 py-2.5 text-xs font-extrabold text-white hover:from-fuchsia-400 hover:to-purple-400"><i class="fa-solid fa-pen"></i> Edit offer</a>
                        <form method="POST" action="{{ route('client.applications.undo-review', $app) }}">@csrf<button type="submit" title="Reopen this selection" class="candidate-action inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-3 py-2.5 text-xs font-extrabold text-slate-100 hover:bg-white/18"><i class="fa-solid fa-rotate-left"></i> Undo selection</button></form>
                    @endif
                </div>
                @if($latestRound)
                    <div class="mt-5 border-t border-white/8 pt-4 text-xs text-blue-200">
                        <i class="fa-solid fa-calendar-check mr-2 text-cyan-300"></i>
                        Latest interview: Round {{ $latestRound->round_number }} - {{ $latestRound->status }}
                        @if($latestRound->scheduled_at) on {{ $latestRound->scheduled_at->format('d M, g:i A') }} @endif
                    </div>
                @endif
            </article>
            </div>
        @empty
            <div class="rounded-3xl border border-dashed border-cyan-300/25 bg-slate-950/35 px-6 py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-400/10 text-2xl text-cyan-300"><i class="fa-regular fa-folder-open"></i></div>
                <h3 class="mt-4 text-lg font-black text-white">No responses in this view</h3>
                <p class="mt-2 text-sm text-blue-200">Try another response tab or clear your search.</p>
            </div>
        @endforelse

        <x-interview-round-modal />
    </section>

    @if($applications->hasPages())
        <div class="mt-7">{{ $applications->links() }}</div>
    @endif
</div>
@endsection
