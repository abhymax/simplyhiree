@extends('layouts.app')

@section('content')
<style>
    .fx-row { transition: all .22s ease; border-left: 4px solid transparent; }
    .fx-row:hover { transform: scale(1.004); background: rgba(255,255,255,.10) !important; border-left-color: #22d3ee; }
    .fx-btn { transition: transform .18s ease, box-shadow .18s ease; }
    .fx-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 12px 24px rgba(59,130,246,.35); }
</style>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen blur-[120px] opacity-30 animate-pulse"></div>
    <div class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500 rounded-full mix-blend-screen blur-[120px] opacity-30"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        @if(session('success'))
            <div class="bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 px-4 py-3 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/20 pb-6">
            <div>
                <a href="{{ route('client.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
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
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center font-bold ring-2 ring-white/20">{{ $initial }}</div>
                                        <div>
                                            <div class="font-bold text-white">{{ $name }}</div>
                                            <div class="text-xs text-cyan-200">{{ $app->candidate->email ?? $app->candidateUser->email ?? 'N/A' }}</div>
                                            <div class="text-xs text-blue-200">{{ $app->candidate->phone_number ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-5 align-top text-sm text-blue-100">
                                    @if($app->candidate)
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">Skills:</span> {{ $app->candidate->skills ?? 'N/A' }}</div>
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">Exp:</span> {{ $app->candidate->experience_status ?? 'N/A' }}</div>
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">Edu:</span> {{ $app->candidate->education_level ?? 'N/A' }}</div>
                                        <div><span class="text-cyan-300 text-xs uppercase font-bold">CTC:</span> {{ $app->candidate->expected_ctc ? '₹'.number_format($app->candidate->expected_ctc,2) : 'N/A' }}</div>
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
                                    @elseif($app->hiring_status == 'Selected')
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-500/20 text-cyan-100 border border-cyan-400/40">Selected</span>
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
                                    @if(empty($app->hiring_status))
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('client.applications.interview.create', $app) }}" class="fx-btn bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Interview</a>
                                            <form action="{{ route('client.applications.reject', $app) }}" method="POST" onsubmit="return confirm('Reject this candidate?');">
                                                @csrf
                                                <button type="submit" class="fx-btn bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Reject</button>
                                            </form>
                                        </div>
                                    @elseif($app->hiring_status == 'Interview Scheduled')
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('client.applications.interview.edit', $app) }}" class="fx-btn bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Edit</a>
                                            <form action="{{ route('client.applications.interview.appeared', $app) }}" method="POST">@csrf <button type="submit" class="fx-btn bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Appeared</button></form>
                                            <form action="{{ route('client.applications.interview.noshow', $app) }}" method="POST">@csrf <button type="submit" class="fx-btn bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold py-2 px-3 rounded-lg">No-Show</button></form>
                                        </div>
                                    @elseif($app->hiring_status == 'Interviewed' || $app->hiring_status == 'No-Show')
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('client.applications.select.show', $app) }}" class="fx-btn bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Select</a>
                                            <form action="{{ route('client.applications.reject', $app) }}" method="POST">@csrf <button type="submit" class="fx-btn bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Reject</button></form>
                                        </div>
                                    @elseif($app->hiring_status == 'Selected' && empty($app->joined_status))
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('client.applications.select.edit', $app) }}" class="fx-btn bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Edit Join</a>
                                            <form action="{{ route('client.applications.markJoined', $app) }}" method="POST">@csrf <button type="submit" class="fx-btn bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2 px-3 rounded-lg">Joined</button></form>
                                            <form action="{{ route('client.applications.markNotJoined', $app) }}" method="POST">@csrf <button type="submit" class="fx-btn bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2 px-3 rounded-lg">DNJ</button></form>
                                        </div>
                                    @elseif($app->joined_status == 'Joined')
                                        <a href="{{ route('client.applications.showLeftForm', $app) }}" class="fx-btn inline-block bg-slate-700 hover:bg-slate-600 text-white text-xs font-bold py-2 px-3 rounded-lg">Mark Left</a>
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
</div>
@endsection