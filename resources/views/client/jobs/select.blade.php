@extends('layouts.client')

@section('client_content')
    <div class="relative z-10 max-w-3xl mx-auto">
        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl p-6 shadow-2xl">
            <h1 class="text-3xl font-extrabold">{{ $isEdit ? 'Edit Selection Details' : 'Select Candidate for Job' }}</h1>
            <p class="text-blue-200 mt-1">{{ $application->job->title }}</p>

            <div class="mt-6 mb-4 border-b border-white/10 pb-4">
                <h2 class="text-lg font-medium text-white">
                    Candidate:
                    {{ $application->candidate ? $application->candidate->first_name . ' ' . $application->candidate->last_name : $application->candidateUser->name }}
                </h2>
            </div>

            <form action="{{ $isEdit ? route('client.applications.select.update', $application) : route('client.applications.select.store', $application) }}" method="POST">
                @csrf
                @if($isEdit)
                    @method('PATCH')
                @endif

                <div class="mt-4">
                    <label for="joining_date" class="block text-sm text-blue-100">Joining Date</label>
                    <input id="joining_date" class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white"
                        type="date"
                        name="joining_date"
                        value="{{ old('joining_date', $application->joining_date ? $application->joining_date->toDateString() : '') }}"
                        required
                        min="{{ now()->toDateString() }}" />
                    @error('joining_date') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="final_ctc" class="block text-sm text-blue-100">Final Offered CTC (₹ / year)</label>
                    <input id="final_ctc" name="final_ctc" type="number" step="0.01" min="0"
                        value="{{ old('final_ctc', $application->final_ctc) }}"
                        placeholder="e.g. 1200000"
                        class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">
                    <p class="text-xs text-blue-200/70 mt-1">Used to compute the invoice amount based on the client's commercial contract.</p>
                    @error('final_ctc') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="mt-4">
                    <label for="client_notes" class="block text-sm text-blue-100">Final Notes (Optional)</label>
                    <textarea id="client_notes" name="client_notes" rows="4"
                        class="block mt-1 w-full rounded-xl border border-white/20 bg-slate-900/40 text-white">{{ old('client_notes', $application->client_notes) }}</textarea>
                    @error('client_notes') <p class="mt-2 text-rose-300 text-sm">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-start mt-6 space-x-4">
                    <button type="submit" class="px-5 py-2.5 rounded-xl font-bold text-white {{ $isEdit ? 'bg-orange-600 hover:bg-orange-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                        {{ $isEdit ? 'Update Selection' : 'Confirm Selection and Finalize' }}
                    </button>

                    <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="text-slate-200 hover:text-white underline">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection