@extends('layouts.client')

@section('client_content')
@php
    $candidateName = trim(($application->candidate->first_name ?? '').' '.($application->candidate->last_name ?? '')) ?: ($application->candidateUser->name ?? 'Candidate');
@endphp

    <div class="relative z-10 max-w-2xl mx-auto">

        @php
            $backUrl = route('client.jobs.applicants', $application->job_id);
            if (url()->previous() && (str_contains(url()->previous(), 'client/applications') || str_contains(url()->previous(), 'client/dashboard'))) {
                $backUrl = url()->previous();
            }
        @endphp
        <a href="{{ $backUrl }}"
           class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-4">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back
        </a>

        <div class="mb-6 border-b border-white/10 pb-5">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight flex items-center gap-3">
                <i class="fa-regular fa-calendar-check text-emerald-400"></i> Reschedule Round {{ $roundNumber }}
            </h1>
            <p class="text-blue-200 mt-2 text-sm">
                Candidate: <span class="text-white font-bold">{{ $candidateName }}</span>
                &middot; Job: <span class="text-cyan-200 font-bold">{{ $application->job->title ?? '—' }}</span>
            </p>
        </div>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-rose-500/20 border border-rose-400/40 text-rose-100 rounded-xl text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('client.rounds.update', $round) }}" method="POST" x-data="{ mode: '{{ old('mode', $round->mode) }}' }"
              class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6 shadow-2xl space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Date &amp; Time *</label>
                <input type="datetime-local" name="scheduled_at" required min="{{ now()->format('Y-m-d\TH:i') }}" 
                       value="{{ old('scheduled_at', $round->scheduled_at ? $round->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                       class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5 [color-scheme:dark] focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Mode *</label>
                <select name="mode" x-model="mode" required class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
                    <option value="Online">Online</option>
                    <option value="In-person">In-person</option>
                    <option value="Phone">Phone</option>
                </select>
            </div>

            <div x-show="mode === 'Online'">
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Meeting Link</label>
                <input type="url" name="meeting_link" value="{{ old('meeting_link', $round->meeting_link) }}" placeholder="https://meet.google.com/..."
                       class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
            </div>

            <div x-show="mode === 'In-person'" x-cloak>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Location</label>
                <input type="text" name="location" value="{{ old('location', $round->location) }}" placeholder="Office address / venue"
                       class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Message / Instructions for Candidate (optional)</label>
                <textarea name="candidate_message" rows="4" maxlength="2000"
                          placeholder="e.g. Please join 5 minutes early. Carry a hard copy of your CV and a valid government ID. Reach Tower B, 2nd floor reception."
                          class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">{{ old('candidate_message', $round->candidate_message) }}</textarea>
                <p class="text-[11px] text-blue-300 mt-1">This message will be shown to the candidate alongside the interview details.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Also notify (CC emails, comma-separated)</label>
                <input type="text" name="cc_emails" value="{{ old('cc_emails', $round->cc_emails) }}" placeholder="panel1@company.com, hr@company.com"
                       class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
                <p class="text-[11px] text-blue-300 mt-1">The interview details &amp; meeting link are also emailed to these addresses (interviewers, panel, coordinators).</p>
            </div>


            <div class="flex gap-2 pt-4 border-t border-white/10 justify-end">
                <a href="{{ $backUrl }}" class="px-5 py-2.5 text-sm text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 text-sm font-bold px-6 py-2.5 rounded-lg shadow-lg">
                    <i class="fa-solid fa-calendar-check mr-1"></i> Save Reschedule
                </button>
            </div>
        </form>
    </div>
@endsection
