@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-3xl mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 shadow-2xl">
            <h1 class="text-3xl font-extrabold">Mark Candidate as Left</h1>
            <p class="text-blue-200 mt-1">{{ $application->job->title }}</p>

            <div class="mt-6 mb-4 border-b border-white/10 pb-4">
                <h2 class="text-lg font-medium text-white mb-2">Candidate Details</h2>
                @if($application->candidate)
                    <p><strong>Name:</strong> {{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</p>
                    <p><strong>Joined On:</strong> {{ $application->joining_date->format('M d, Y') }}</p>
                @elseif($application->candidateUser)
                    <p><strong>Name:</strong> {{ $application->candidateUser->name }}</p>
                    <p><strong>Joined On:</strong> {{ $application->joining_date->format('M d, Y') }}</p>
                @endif
            </div>

            <form action="{{ route('client.applications.markLeft', $application) }}" method="POST">
                @csrf

                <div class="mt-4">
                    <label for="left_at" class="block text-sm text-blue-100">Left Date</label>
                    <input id="left_at" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white"
                        type="date"
                        name="left_at"
                        value="{{ old('left_at', now()->toDateString()) }}"
                        required
                        min="{{ $application->joining_date->toDateString() }}" />
                    @error('left_at') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="client_notes" class="block text-sm text-blue-100">Notes (Optional)</label>
                    <textarea id="client_notes" name="client_notes" rows="4"
                        class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">{{ old('client_notes') }}</textarea>
                    @error('client_notes') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-start mt-6 space-x-4">
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white bg-rose-600 hover:bg-rose-700">
                        Confirm Candidate Left
                    </button>

                    <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="text-slate-200 hover:text-white underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection