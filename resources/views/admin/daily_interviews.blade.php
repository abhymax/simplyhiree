@extends('layouts.app')

@section('content')
<div class="py-7 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-3xl border border-white/10 bg-slate-950/45 shadow-2xl backdrop-blur-xl">
            <div class="p-5 sm:p-7">
                <div class="mb-6 flex flex-col gap-4 border-b border-white/10 pb-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="mb-2 flex items-center gap-2 text-cyan-300"><i class="fa-solid fa-calendar-day"></i><span class="text-xs font-extrabold uppercase tracking-wider">Interview Board</span></div>
                        <h1 class="text-2xl font-extrabold text-white sm:text-3xl">Today's Scheduled Interviews</h1>
                        <p class="mt-1 text-sm text-slate-300">{{ date('F j, Y') }}</p>
                    </div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 self-start rounded-xl border border-cyan-300/25 bg-cyan-400/10 px-4 py-2 text-sm font-bold text-cyan-200 transition hover:border-cyan-200/50 hover:bg-cyan-400/20 sm:self-auto">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>

                <div class="overflow-x-auto rounded-2xl border border-white/10">
                    <table class="min-w-full">
                        <thead class="bg-slate-900/90">
                            <tr>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-cyan-300">Time</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-cyan-300">Candidate</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-cyan-300">Job Role</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-cyan-300">Client</th>
                                <th class="px-5 py-4 text-left text-xs font-extrabold uppercase tracking-wider text-cyan-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 bg-slate-900/40">
                            @forelse($todayInterviews as $app)
                                <tr class="transition-colors hover:bg-cyan-400/10">
                                    <td class="whitespace-nowrap px-5 py-4">
                                        <div class="text-lg font-extrabold text-cyan-300">
                                            {{ $app->interview_at->format('g:i A') }}
                                        </div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-white">{{ $app->candidate_name }}</div>
                                        @php
                                            $phone = $app->candidate?->phone_number ?? $app->candidateUser?->profile?->phone_number;
                                        @endphp
                                        @if($phone)
                                            <div class="mt-0.5 text-xs text-slate-400">{{ $phone }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-medium text-slate-200">{{ $app->job->title }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-medium text-slate-200">{{ $app->job->company_name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <a href="{{ route('admin.applications.show', $app->id) }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-500 px-4 py-2 text-xs font-extrabold text-white transition hover:bg-indigo-400 hover:shadow-lg hover:shadow-indigo-500/25">
                                            <i class="fa-regular fa-eye"></i> View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-slate-400">
                                        <i class="fa-regular fa-calendar-xmark mb-3 block text-3xl text-slate-500"></i>
                                        No interviews scheduled for today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
