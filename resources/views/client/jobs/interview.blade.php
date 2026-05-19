@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-3xl mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 shadow-2xl">
            <h1 class="text-3xl font-extrabold">{{ $isEdit ? 'Edit Interview Details' : 'Schedule Interview' }}</h1>
            <p class="text-blue-200 mt-1">for {{ $application->job->title }}</p>

            <div class="mt-6 mb-4 border-b border-white/10 pb-4">
                <h2 class="text-lg font-medium text-white">
                    Candidate:
                    {{ $application->candidate ? $application->candidate->first_name . ' ' . $application->candidate->last_name : $application->candidateUser->name }}
                </h2>
            </div>

            <form action="{{ $isEdit ? route('client.applications.interview.update', $application) : route('client.applications.interview.store', $application) }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div class="mt-4">
                    <label for="interview_at" class="block text-sm text-blue-100">Interview Date & Time</label>
                    <input id="interview_at" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white"
                        type="datetime-local"
                        name="interview_at"
                        value="{{ old('interview_at', $isEdit && $application->interview_at ? $application->interview_at->format('Y-m-d\TH:i') : '') }}"
                        required
                        min="{{ now()->addMinutes(1)->format('Y-m-d\TH:i') }}" />
                    @error('interview_at') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="meeting_provider" class="block text-sm text-blue-100">Meeting Type</label>
                        <select id="meeting_provider" name="meeting_provider" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white"
                            onchange="document.getElementById('meeting_link_wrap').style.display = (this.value==='inperson') ? 'none':'block'; document.getElementById('inperson_wrap').style.display = (this.value==='inperson')?'block':'none';">
                            @foreach(['zoom'=>'🎥 Zoom','meet'=>'📹 Google Meet','teams'=>'💼 Microsoft Teams','inperson'=>'📍 In-person','other'=>'🔗 Other'] as $k=>$v)
                                <option value="{{ $k }}" {{ old('meeting_provider', $application->meeting_provider ?? 'meet') === $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="meeting_link_wrap" style="display: {{ ($application->meeting_provider ?? 'meet') === 'inperson' ? 'none' : 'block' }};">
                        <label for="meeting_link" class="block text-sm text-blue-100">Meeting Link</label>
                        <input id="meeting_link" name="meeting_link" type="url" placeholder="Paste Zoom / Meet / Teams URL"
                            value="{{ old('meeting_link', $application->meeting_link) }}"
                            class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                        <p class="text-xs text-blue-200/70 mt-1">Open <a href="https://meet.google.com/new" target="_blank" class="underline text-cyan-300">meet.google.com/new</a> or your Zoom dashboard, then paste the URL here.</p>
                    </div>
                </div>

                <div id="inperson_wrap" class="mt-4" style="display: {{ ($application->meeting_provider ?? 'meet') === 'inperson' ? 'block' : 'none' }};">
                    <label for="interview_location" class="block text-sm text-blue-100">Address / Location</label>
                    <input id="interview_location" name="interview_location" type="text" placeholder="Office address, room, floor..."
                        value="{{ old('interview_location', $application->interview_location) }}"
                        class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                </div>

                <div class="mt-4">
                    <label for="client_notes" class="block text-sm text-blue-100">Interview Notes (Optional)</label>
                    <textarea id="client_notes" name="client_notes" rows="4"
                        class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">{{ old('client_notes', $application->client_notes) }}</textarea>
                    @error('client_notes') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-start mt-6 space-x-4">
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white {{ $isEdit ? 'bg-orange-600 hover:bg-orange-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $isEdit ? 'Update Interview' : 'Schedule Interview' }}
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