@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8 text-gray-900">
                
                <div class="mb-6 border-b pb-4">
                    <h1 class="text-2xl font-semibold">Schedule Interview</h1>
                    <p class="text-lg text-gray-700 mt-1">
                        For: 
                        <span class="font-bold">
                            @if($application->candidate)
                                {{ $application->candidate->first_name }} {{ $application->candidate->last_name }}
                            @else
                                {{ $application->candidateUser->name }}
                            @endif
                        </span>
                    </p>
                    <p class="text-md text-gray-500">
                        Job: {{ $application->job->title }}
                    </p>
                </div>

                <form action="{{ route('client.applications.interview.schedule', $application) }}" method="POST">
                    @csrf

                    <div>
                        <label for="interview_at" class="block text-sm font-medium text-gray-700">Interview Date & Time</label>
                        <input type="datetime-local" 
                               name="interview_at" 
                               id="interview_at"
                               value="{{ old('interview_at') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('interview_at')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6">
                        <label for="client_notes" class="block text-sm font-medium text-gray-700">
                            Notes (e.g., Interview location, video call link)
                        </label>
                        <textarea name="client_notes" 
                                  id="client_notes" 
                                  rows="4"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('client_notes') }}</textarea>
                        @error('client_notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="text-gray-600 hover:underline py-2 px-4">
                            Cancel
                        </a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md ml-4">
                            Schedule Interview
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</div>
@endsection