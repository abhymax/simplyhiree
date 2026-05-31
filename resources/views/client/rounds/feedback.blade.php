@extends('layouts.client')

@section('client_content')
@php
    $candidateName = trim(($application->candidate->first_name ?? '').' '.($application->candidate->last_name ?? '')) ?: ($application->candidateUser->name ?? 'Candidate');
@endphp

    <div class="relative z-10 max-w-2xl mx-auto">

        <a href="{{ route('client.jobs.applicants', $application->job_id) }}"
           class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase tracking-wider mb-4">
            <i class="fa-solid fa-arrow-left mr-2"></i> Back to Applicants
        </a>

        <div class="mb-6 border-b border-white/10 pb-5">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight flex items-center gap-3">
                <i class="fa-regular fa-clipboard text-cyan-400"></i> Round {{ $round->round_number }} Feedback
            </h1>
            <p class="text-blue-200 mt-2 text-sm">
                Candidate: <span class="text-white font-bold">{{ $candidateName }}</span>
                &middot; {{ $round->scheduled_at->format('M d, Y g:i A') }}
                &middot; {{ $round->mode }}
                @if($round->interviewer_name) &middot; Interviewer: <span class="text-cyan-200">{{ $round->interviewer_name }}</span>@endif
            </p>
        </div>

        @if($errors->any())
            <div class="mb-4 px-4 py-3 bg-rose-500/20 border border-rose-400/40 text-rose-100 rounded-xl text-sm">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('client.rounds.feedback', $round) }}" method="POST"
              class="bg-slate-900/60 backdrop-blur-xl border border-white/15 rounded-2xl p-6 shadow-2xl space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-cyan-200 mb-1.5">Interview Notes (optional)</label>
                <textarea name="feedback" rows="6" maxlength="5000" placeholder="What stood out? Strengths, gaps, concerns, communication, technical depth..."
                          class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-cyan-400 focus:border-cyan-400">{{ old('feedback') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-cyan-200 mb-1.5">Rating</label>
                    <select name="rating" class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
                        <option value="">Optional</option>
                        @for($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }} ★</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-cyan-200 mb-1.5">Recommendation *</label>
                    <select name="recommendation" required class="w-full bg-slate-800 border border-white/20 text-white text-sm rounded-lg px-3 py-2.5">
                        <option value="">Choose...</option>
                        @if($allowNext)
                            <option value="Pass to Next Round" {{ old('recommendation') == 'Pass to Next Round' ? 'selected' : '' }}>Pass to Next Round</option>
                        @endif
                        <option value="Select Candidate" {{ old('recommendation') == 'Select Candidate' ? 'selected' : '' }}>Select Candidate</option>
                        <option value="Reject" {{ old('recommendation') == 'Reject' ? 'selected' : '' }}>Reject</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-2 pt-4 border-t border-white/10 justify-end">
                <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="px-5 py-2.5 text-sm text-slate-300 hover:text-white">Cancel</a>
                <button type="submit" class="bg-cyan-500 hover:bg-cyan-400 text-slate-900 text-sm font-bold px-6 py-2.5 rounded-lg shadow-lg">
                    <i class="fa-solid fa-floppy-disk mr-1"></i> Save Feedback
                </button>
            </div>
        </form>
    </div>
@endsection
