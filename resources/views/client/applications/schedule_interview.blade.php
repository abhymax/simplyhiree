@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-xl mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 shadow-2xl">

            <div class="mb-6 border-b border-white/10 pb-4">
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Client Workspace
                </span>
                <h1 class="text-3xl font-extrabold mt-3">Schedule Interview</h1>
                <h3 class="text-lg font-bold text-white mt-3">
                    Candidate: {{ $application->candidate->first_name }} {{ $application->candidate->last_name }}
                </h3>
                <p class="text-sm text-blue-200">Role: {{ $application->job->title }}</p>
            </div>

            <form action="{{ route('client.applications.interview.store', $application->id) }}" method="POST">
                @csrf

                <div class="mb-4">
                    <label for="interview_date" class="block text-sm text-blue-100">Interview Date</label>
                    <input id="interview_date" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" type="date" name="interview_date" value="{{ old('interview_date') }}" required />
                    @error('interview_date') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="interview_time" class="block text-sm text-blue-100">Interview Time</label>
                    <input id="interview_time" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" type="time" name="interview_time" value="{{ old('interview_time') }}" required />
                    @error('interview_time') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="meeting_link" class="block text-sm text-blue-100">Meeting Link (Google Meet/Zoom)</label>
                    <input id="meeting_link" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white" type="url" name="meeting_link" value="{{ old('meeting_link') }}" placeholder="https://..." />
                    @error('meeting_link') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mb-6">
                    <label for="notes" class="block text-sm text-blue-100">Instructions / Notes</label>
                    <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">{{ old('notes') }}</textarea>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="text-slate-200 hover:text-white">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-xl font-bold bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white transition shadow-lg">
                        Schedule Interview
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection