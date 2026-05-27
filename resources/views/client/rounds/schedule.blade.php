@extends('layouts.app')

@section('content')
@php
    $candidateName = trim(($application->candidate->first_name ?? '').' '.($application->candidate->last_name ?? '')) ?: ($application->candidateUser->name ?? 'Candidate');
@endphp

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">

    <div class="relative z-10 max-w-2xl mx-auto">

        <a href="{{ route('client.jobs.applicants', $application->job_id) }}"
           class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-4">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Applicants
        </a>

        <div class="mb-6 border-b border-white/10 pb-5">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight flex items-center gap-3">
                <i class="fa-solid fa-calendar-plus text-emerald-400"></i> Schedule Round {{ $roundNumber }}
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

        <form action="{{ route('client.applications.rounds.store', $application) }}" method="POST" x-data="{ mode: 'Online' }"
              class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6 shadow-2xl space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Date &amp; Time *</label>
                <input type="datetime-local" name="scheduled_at" required min="{{ now()->format('Y-m-d\TH:i') }}" value="{{ old('scheduled_at') }}"
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
                <input type="url" name="meeting_link" value="{{ old('meeting_link') }}" placeholder="https://meet.google.com/..."
                       class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
            </div>

            <div x-show="mode === 'In-person'" x-cloak>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Location</label>
                <input type="text" name="location" value="{{ old('location') }}" placeholder="Office address / venue"
                       class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-emerald-200 mb-1.5">Interviewer Name (optional)</label>
                <input type="text" name="interviewer_name" value="{{ old('interviewer_name') }}" placeholder="Who's conducting the interview?"
                       class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
            </div>

            <div class="flex gap-2 pt-4 border-t border-white/10 justify-end">
                <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="px-5 py-2.5 text-sm text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-slate-900 text-sm font-bold px-6 py-2.5 rounded-lg shadow-lg">
                    <i class="fa-solid fa-calendar-plus mr-1"></i> Schedule Round {{ $roundNumber }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
