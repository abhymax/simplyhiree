@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">{{ $isEdit ? 'Edit Interview Details' : 'Schedule Interview' }}</h1>
                    <p class="text-xl text-gray-700">for {{ $application->job->title }}</p>
                </div>

                {{-- FIX: Route name changed from 'interview.schedule' to 'interview.store' --}}
                <form action="{{ $isEdit ? route('client.applications.interview.update', $application) : route('client.applications.interview.store', $application) }}" 
                      method="POST">
                    @csrf
                    @if($isEdit)
                        @method('PUT') {{-- Note: web.php often uses PUT for updates, ensuring it matches --}}
                    @endif

                    <div class="mb-4 border-b pb-4">
                        <h2 class="text-lg font-medium text-gray-800">Candidate: {{ $application->candidate ? $application->candidate->first_name . ' ' . $application->candidate->last_name : $application->candidateUser->name }}</h2>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="interview_at" :value="__('Interview Date & Time')" />
                        <x-text-input id="interview_at" class="block mt-1 w-full md:w-1/2" 
                                      type="datetime-local" 
                                      name="interview_at" 
                                      :value="old('interview_at', $isEdit && $application->interview_at ? $application->interview_at->format('Y-m-d\TH:i') : '')" 
                                      required 
                                      min="{{ now()->addMinutes(1)->format('Y-m-d\TH:i') }}" />
                        <x-input-error :messages="$errors->get('interview_at')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="client_notes" :value="__('Interview Notes (Optional)')" />
                        <textarea id="client_notes" name="client_notes" rows="4" 
                                  class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                  >{{ old('client_notes', $application->client_notes) }}</textarea>
                        <x-input-error :messages="$errors->get('client_notes')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-start mt-6 space-x-4">
                        <x-primary-button class="{{ $isEdit ? 'bg-orange-600 hover:bg-orange-700' : 'bg-green-600 hover:bg-green-700' }}">
                            {{ $isEdit ? __('Update Interview') : __('Schedule Interview') }}
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