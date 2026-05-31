@extends('layouts.client')

@section('client_content')
<style>
    .fx-row { transition: all .22s ease; border-left: 4px solid transparent; }
    .fx-row:hover { transform: scale(1.004); background: rgba(255,255,255,.10) !important; border-left-color: #22d3ee; }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 px-4 py-3 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <a href="javascript:history.back()" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back
                </a>
                <h1 class="text-4xl font-extrabold tracking-tight">Approved Applicants</h1>
                <p class="text-blue-100 mt-1">{{ $job->title }}</p>
            </div>
            <div class="mt-4 md:mt-0 bg-blue-600 border border-blue-400 rounded-2xl px-5 py-3 shadow-xl">
                <div class="text-blue-100 text-xs font-bold uppercase">Total Count</div>
                <div class="text-3xl font-black">{{ $applications->total() }}</div>
            </div>
        </div>

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-5">Candidate</th>
                            <th class="px-6 py-5">Details</th>
                            <th class="px-6 py-5">Resume</th>
                            <th class="px-6 py-5">Hiring Status</th>
                            <th class="px-6 py-5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($applications as $app)
                            @php
                                $name = $app->candidate ? trim(($app->candidate->first_name ?? '').' '.($app->candidate->last_name ?? '')) : ($app->candidateUser->name ?? 'N/A');
                                $initial = strtoupper(substr($name ?: 'U', 0, 1));
                            @endphp
                            <tr class="fx-row">
                                <td class="px-6 py-5 align-top">
                                    <a href="{{ route('client.applications.show', $app) }}" class="flex items-center gap-3 group">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center font-bold ring-2 ring-white/20 group-hover:ring-cyan-400 transition">{{ $initial }}</div>
                                        <div>
                                            <div class="font-bold text-white group-hover:text-cyan-300 transition-colors">{{ $name }}</div>
                                            <div class="text-xs text-cyan-200">{{ $app->candidate->email ?? $app->candidateUser->email ?? 'N/A' }}</div>
                                            <div class="text-xs text-blue-200">{{ $app->candidate->phone_number ?? '' }}</div>
                                        </div>
                                    </a>
                                </td>

                                <td class="px-6 py-5 align-top text-sm text-blue-100">
                                    @if($app->candidate)
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">Skills:</span> {{ $app->candidate->skills ?? 'N/A' }}</div>
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">Exp:</span> {{ $app->candidate->experience_status ?? 'N/A' }}</div>
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">Edu:</span> {{ $app->candidate->education_level ?? 'N/A' }}</div>
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">CTC:</span>
                                            @php
                                                $ctcRaw = $app->candidate->expected_ctc ?? null;
                                                $ctcNum = is_numeric($ctcRaw) ? (float) $ctcRaw : (float) preg_replace('/[^0-9.]/', '', (string) $ctcRaw);
                                            @endphp
                                            {{ $ctcRaw ? '₹'.number_format($ctcNum, 2) : 'N/A' }}
                                        </div>
                                    @else
                                        N/A
                                    @endif
                                </td>

                                <td class="px-6 py-5 align-top">
                                    @if($app->candidate && $app->candidate->resume_path)
                                        <a href="{{ asset('storage/'. $app->candidate->resume_path) }}" target="_blank" class="fx-btn inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-3 py-2 rounded-lg font-bold text-xs">
                                            <i class="fa-solid fa-file-arrow-down"></i> Download CV
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-xs">N/A</span>
                                    @endif
                                </td>

                                <td class="px-6 py-5 align-top text-sm">
                                    @if($app->joined_status == 'Joined')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-100 border border-emerald-400/40">Joined</span>
                                        <div class="text-xs text-blue-200 mt-1">On: {{ $app->joining_date->format('M d, Y') }}</div>
                                    @elseif($app->joined_status == 'Did Not Join')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-100 border border-rose-400/40">Did Not Join</span>
                                    @elseif($app->joined_status == 'Left')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-100 border border-rose-400/40">Left</span>
                                        <div class="text-xs text-blue-200 mt-1">On: {{ $app->left_at->format('M d, Y') }}</div>
                                        @if($app->replacement_requested_at)
                                            <div class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider bg-amber-500/20 text-amber-200 border border-amber-400/40 px-2 py-0.5 rounded">
                                                <i class="fa-solid fa-rotate"></i> Replacement Requested
                                            </div>
                                        @endif
                                    @elseif($app->hiring_status == 'Selected')
                                        @if($app->selected_by_admin_id)
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-500/25 text-purple-100 border border-purple-400/50 inline-flex items-center gap-1.5">
                                                <i class="fa-solid fa-user-shield"></i> Selected by Superadmin
                                            </span>
                                        @else
                                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-100 border border-cyan-400/40">Selected</span>
                                        @endif
                                        <div class="text-xs text-blue-200 mt-1">Joining: {{ $app->joining_date->format('M d, Y') }}</div>
                                    @elseif($app->hiring_status == 'Interview Scheduled')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-500/20 text-indigo-100 border border-indigo-400/40">Interview Scheduled</span>
                                        <div class="text-xs text-blue-200 mt-1">{{ $app->interview_at->format('M d, Y \a\t g:i A') }}</div>
                                    @elseif($app->hiring_status == 'Client Rejected')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500/20 text-rose-100 border border-rose-400/40">Rejected</span>
                                    @elseif($app->hiring_status == 'Interviewed')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-violet-500/20 text-violet-100 border border-violet-400/40">Interviewed</span>
                                    @elseif($app->hiring_status == 'No-Show')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-100 border border-amber-400/40">No-Show</span>
                                    @else
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-500/20 text-slate-100 border border-slate-400/40">Pending Action</span>
                                    @endif
                                </td>

                                <td class="px-6 py-5 align-top text-right">
                                    @php
                                        $rounds          = $app->interviewRounds;
                                        $latestRound     = $rounds->last();
                                        $roundCount      = $rounds->count();
                                        $maxRounds       = 5;
                                        $canScheduleNew  = $roundCount < $maxRounds
                                            && !in_array($app->hiring_status, ['Selected', 'Client Rejected'])
                                            && empty($app->joined_status)
                                            && (!$latestRound || in_array($latestRound->status, ['Appeared', 'No-Show', 'Cancelled']))
                                            && (!$latestRound || $latestRound->recommendation !== 'Reject');
                                        $canSelectNow    = $latestRound
                                            && $latestRound->feedback_submitted_at
                                            && !in_array($latestRound->recommendation, ['Reject'])
                                            && empty($app->joined_status)
                                            && $app->hiring_status !== 'Selected';
                                    @endphp

                                    {{-- Round timeline --}}
                                    @if($roundCount > 0)
                                        <div class="mb-3 space-y-1.5">
                                            @foreach($rounds as $r)
                                                @php
                                                    $statusColors = [
                                                        'Scheduled' => 'bg-indigo-500/20 text-indigo-100 border-indigo-400/40',
                                                        'Appeared'  => 'bg-emerald-500/20 text-emerald-100 border-emerald-400/40',
                                                        'No-Show'   => 'bg-amber-500/20 text-amber-100 border-amber-400/40',
                                                        'Cancelled' => 'bg-slate-500/20 text-slate-100 border-slate-400/40',
                                                    ];
                                                    $cls = $statusColors[$r->status] ?? 'bg-slate-500/20 text-slate-100 border-slate-400/40';
                                                @endphp
                                                <div class="flex items-center justify-end gap-2 text-[11px]">
                                                    <span class="px-2 py-0.5 rounded font-bold bg-blue-600/30 text-blue-100 border border-blue-400/40">R{{ $r->round_number }}</span>
                                                    <span class="text-slate-300">{{ $r->scheduled_at->format('d M, h:i A') }}</span>
                                                    <span class="text-slate-400">·</span>
                                                    <span class="text-slate-300">{{ $r->mode }}</span>
                                                    <span class="px-2 py-0.5 rounded border font-bold {{ $cls }}">{{ $r->status }}</span>
                                                    @if($r->recommendation)
                                                        <span class="px-2 py-0.5 rounded bg-cyan-500/15 text-cyan-100 border border-cyan-400/30 text-[10px]">{{ $r->recommendation }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Action buttons based on latest round state --}}
                                    @if($app->hiring_status == 'Selected' && empty($app->joined_status))
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('client.applications.select.edit', $app) }}" class="fx-btn bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Edit Join</a>
                                            <form action="{{ route('client.applications.markJoined', $app) }}" method="POST">@csrf <button type="submit" class="fx-btn bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Joined</button></form>
                                            <form action="{{ route('client.applications.markNotJoined', $app) }}" method="POST">@csrf <button type="submit" class="fx-btn bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2 px-3 rounded-lg">DID NOT JOINED</button></form>
                                        </div>
                                    @elseif(empty($app->joined_status) && $app->hiring_status !== 'Client Rejected')
                                        <div class="flex flex-wrap justify-end gap-2">
                                            @if($latestRound && $latestRound->status === 'Scheduled')
                                                @if($latestRound->meeting_link)
                                                    <a href="{{ $latestRound->meeting_link }}" target="_blank" class="fx-btn bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2 px-3 rounded-lg"><i class="fa-solid fa-video"></i> Join R{{ $latestRound->round_number }}</a>
                                                @endif
                                                <form action="{{ route('client.rounds.appeared', $latestRound) }}" method="POST">@csrf
                                                    <button type="submit" class="fx-btn bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Appeared</button>
                                                </form>
                                                <form action="{{ route('client.rounds.noshow', $latestRound) }}" method="POST">@csrf
                                                    <button type="submit" class="fx-btn bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold py-2 px-3 rounded-lg">No-Show</button>
                                                </form>
                                            @elseif($latestRound && in_array($latestRound->status, ['Appeared','No-Show']) && !$latestRound->feedback_submitted_at)
                                                <a href="{{ route('client.rounds.feedback.create', $latestRound) }}"
                                                   class="fx-btn bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-bold py-2 px-3 rounded-lg"><i class="fa-regular fa-clipboard"></i> R{{ $latestRound->round_number }} Feedback</a>
                                            @endif

                                            @if($canSelectNow)
                                                <a href="{{ route('client.applications.select.show', $app) }}" class="fx-btn bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2 px-3 rounded-lg"><i class="fa-solid fa-user-check"></i> Select Candidate</a>
                                            @endif

                                            @if($canScheduleNew)
                                                <a href="{{ route('client.applications.rounds.create', $app) }}"
                                                   class="fx-btn bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2 px-3 rounded-lg">
                                                    <i class="fa-solid fa-calendar-plus"></i> Schedule Round {{ $roundCount + 1 }}
                                                </a>
                                            @endif

                                            <form action="{{ route('client.applications.reject', $app) }}" method="POST" onsubmit="return confirm('Reject this candidate?');">@csrf
                                                <button type="submit" class="fx-btn bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Reject</button>
                                            </form>
                                        </div>
                                    @elseif($app->joined_status == 'Joined')
                                        <a href="{{ route('client.applications.showLeftForm', $app) }}" class="fx-btn inline-block bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold py-2 px-3 rounded-lg">Mark Left</a>
                                    @elseif($app->joined_status == 'Left')
                                        @php
                                            $guaranteeDays = (int) ($app->replacement_window_days ?? $app->job->replacement_guarantee_days ?? 0);
                                            $tenureDays = ($app->joining_date && $app->left_at) ? $app->joining_date->diffInDays($app->left_at) : null;
                                            $withinGuarantee = $guaranteeDays === 0 || ($tenureDays !== null && $tenureDays <= $guaranteeDays);
                                        @endphp
                                        @if($app->replacement_requested_at)
                                            <span class="text-amber-200 text-xs font-semibold">Replacement requested {{ $app->replacement_requested_at->diffForHumans() }}</span>
                                        @elseif($withinGuarantee)
                                            <button type="button" onclick="document.getElementById('repl-{{ $app->id }}').classList.toggle('hidden')"
                                                class="fx-btn inline-block bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold py-2 px-3 rounded-lg">
                                                <i class="fa-solid fa-rotate mr-1"></i> Request Replacement
                                            </button>
                                            <form id="repl-{{ $app->id }}" method="POST" action="{{ route('client.applications.request-replacement', $app) }}"
                                                  class="hidden mt-2 flex flex-col gap-2 w-64 bg-slate-900/70 border border-amber-400/30 p-3 rounded-lg text-left">
                                                @csrf
                                                <p class="text-amber-200 text-[11px]">Tenure was {{ $tenureDays }} day(s) — within the {{ $guaranteeDays }}-day guarantee. The sourcing partner will be notified.</p>
                                                <textarea name="reason" rows="2" maxlength="1000" placeholder="Reason (optional)"
                                                    class="w-full text-xs bg-slate-900 border border-white/20 rounded p-2 text-white"></textarea>
                                                <div class="flex gap-2">
                                                    <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold px-3 py-1.5 rounded">Submit</button>
                                                    <button type="button" onclick="document.getElementById('repl-{{ $app->id }}').classList.add('hidden')" class="text-xs text-slate-300 hover:text-white">Cancel</button>
                                                </div>
                                            </form>
                                        @else
                                            <span class="text-slate-400 text-xs">Beyond guarantee ({{ $tenureDays ?? '?' }}/{{ $guaranteeDays }} days)</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-xs">--</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-blue-100">
                                    <i class="fa-regular fa-folder-open text-5xl text-blue-200 mb-3"></i>
                                    <p class="font-bold text-white">No approved applicants yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-6 border-t border-white/10 bg-slate-900/80">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
@endsection