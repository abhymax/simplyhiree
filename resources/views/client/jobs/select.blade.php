@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">Select Candidate for Job</h1>
                    <p class="text-xl text-gray-700">{{ $application->job->title }}</p>
                </div>

                <div class="mb-4 border-b pb-4">
                    <h2 class="text-lg font-medium text-gray-800">Candidate Details</h2>
                    @if($application->candidate)
                        <p><strong>Name:</strong> {{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</p>
                        <p><strong>Email:</strong> {{ $application->candidate->email }}</p>
                    @elseif($application->candidateUser)
                        <p><strong>Name:</strong> {{ $application->candidateUser->name }}</p>
                        <p><strong>Email:</strong> {{ $application->candidateUser->email }}</p>
                    @endif
                </div>

                <form action="{{ route('client.applications.select.store', $application) }}" method="POST">
                    @csrf

                    <div class="mt-4">
                        <x-input-label for="joining_date" :value="__('Joining Date')" />
                        <x-text-input id="joining_date" class="block mt-1 w-full md:w-1/2" 
                                      type="date" 
                                      name="joining_date" 
                                      :value="old('joining_date')" 
                                      required 
                                      min="{{ now()->toDateString() }}" />
                        <x-input-error :messages="$errors->get('joining_date')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="joining_notes" :value="__('Final Notes (Optional)')" />
                        <textarea id="joining_notes" name="joining_notes" rows="4" 
                                  class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                  >{{ old('joining_notes', $application->client_notes) }}</textarea>
                        <x-input-error :messages="$errors->get('joining_notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-start mt-6 space-x-4">
                        <x-primary-button class="bg-blue-600 hover:bg-blue-700">
                            {{ __('Confirm Selection and Finalize') }}
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