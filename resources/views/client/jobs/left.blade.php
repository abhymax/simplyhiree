@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">Mark Candidate as Left</h1>
                    <p class="text-xl text-gray-700">{{ $application->job->title }}</p>
                </div>

                <div class="mb-4 border-b pb-4">
                    <h2 class="text-lg font-medium text-gray-800">Candidate Details</h2>
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
                        <x-input-label for="left_at" :value="__('Left Date')" />
                        <x-text-input id="left_at" class="block mt-1 w-full md:w-1/2" 
                                      type="date" 
                                      name="left_at" 
                                      :value="old('left_at', now()->toDateString())" 
                                      required 
                                      min="{{ $application->joining_date->toDateString() }}" />
                        <x-input-error :messages="$errors->get('left_at')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="client_notes" :value="__('Notes (Optional)')" />
                        <textarea id="client_notes" name="client_notes" rows="4" 
                                  class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                  >{{ old('client_notes') }}</textarea>
                        <x-input-error :messages="$errors->get('client_notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-start mt-6 space-x-4">
                        <x-primary-button class="bg-red-600 hover:bg-red-700">
                            {{ __('Confirm Candidate Left') }}
                        </x-primary-button>
                        
                        <a href="{{ route('client.jobs.applicants', $application->job_id) }}" 
                           class="text-gray-600 hover:text-gray-900 underline">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection