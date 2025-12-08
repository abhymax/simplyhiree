@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edit Selection Details' : 'Select Candidate for Job' }}</h1>
                    <p class="text-xl text-gray-700">{{ $application->job->title }}</p>
                </div>

                <form action="{{ $isEdit ? route('client.applications.select.update', $application) : route('client.applications.select.store', $application) }}" 
                      method="POST">
                    @csrf
                    @if($isEdit)
                        @method('PATCH')
                    @endif

                    <div class="mb-4 border-b pb-4">
                        <h2 class="text-lg font-medium text-gray-800">Candidate: {{ $application->candidate ? $application->candidate->first_name . ' ' . $application->candidate->last_name : $application->candidateUser->name }}</h2>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="joining_date" :value="__('Joining Date')" />
                        <x-text-input id="joining_date" class="block mt-1 w-full md:w-1/2" 
                                      type="date" 
                                      name="joining_date" 
                                      :value="old('joining_date', $application->joining_date ? $application->joining_date->toDateString() : '')" 
                                      required 
                                      min="{{ now()->toDateString() }}" />
                        <x-input-error :messages="$errors->get('joining_date')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="client_notes" :value="__('Final Notes (Optional)')" />
                        <textarea id="client_notes" name="client_notes" rows="4" 
                                  class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                  >{{ old('client_notes', $application->client_notes) }}</textarea>
                        <x-input-error :messages="$errors->get('client_notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-start mt-6 space-x-4">
                        <x-primary-button class="{{ $isEdit ? 'bg-orange-600 hover:bg-orange-700' : 'bg-blue-600 hover:bg-blue-700' }}">
                            {{ $isEdit ? __('Update Selection') : __('Confirm Selection and Finalize') }}
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