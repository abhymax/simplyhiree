<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schedule Interview') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="mb-6 border-b pb-4">
                    <h3 class="text-lg font-bold text-gray-900">Candidate: {{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</h3>
                    <p class="text-sm text-gray-600">Role: {{ $application->job->title }}</p>
                </div>

                <form action="{{ route('client.applications.interview.store', $application->id) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <x-input-label for="interview_date" :value="__('Interview Date')" />
                        <x-text-input id="interview_date" class="block mt-1 w-full" type="date" name="interview_date" :value="old('interview_date')" required />
                        <x-input-error :messages="$errors->get('interview_date')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="interview_time" :value="__('Interview Time')" />
                        <x-text-input id="interview_time" class="block mt-1 w-full" type="time" name="interview_time" :value="old('interview_time')" required />
                        <x-input-error :messages="$errors->get('interview_time')" class="mt-2" />
                    </div>

                    <div class="mb-4">
                        <x-input-label for="meeting_link" :value="__('Meeting Link (Google Meet/Zoom)')" />
                        <x-text-input id="meeting_link" class="block mt-1 w-full" type="url" name="meeting_link" :value="old('meeting_link')" placeholder="https://..." />
                        <x-input-error :messages="$errors->get('meeting_link')" class="mt-2" />
                    </div>

                    <div class="mb-6">
                        <x-input-label for="notes" :value="__('Instructions / Notes')" />
                        <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex items-center justify-end gap-4">
                        <a href="{{ route('client.jobs.applicants', $application->job_id) }}" class="text-gray-600 hover:text-gray-900">Cancel</a>
                        <x-primary-button>
                            {{ __('Schedule Interview') }}
                        </x-primary-button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>