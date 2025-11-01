@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 md:p-8 text-gray-900">

                <h1 class="text-3xl font-bold mb-2">{{ $job->title }}</h1>
                <p class="text-lg text-gray-700 mb-1">{{ $job->company_name }} - {{ $job->location }}</p>
                <p class="text-md text-gray-600 mb-6">Salary: <span class="font-semibold">{{ $job->salary }}</span></p>

                <div class="prose max-w-none mb-8">
                    <h3 class="text-xl font-semibold mb-2">Job Description</h3>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $job->description }}</p>
                </div>

                <form action="{{ route('partner.jobs.submit', $job->id) }}" method="POST">
                    @csrf

                    <h3 class="text-2xl font-semibold mb-4 border-t pt-6">Select Candidates from Your Pool</h3>

                    @error('candidate_ids')
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <strong class="font-bold">Error!</strong>
                            <span class="block sm:inline">{{ $message }}</span>
                        </div>
                    @enderror

                    <div class="space-y-4">
                        @forelse($candidates as $candidate)
                            <label for="candidate-{{ $candidate->id }}" class="flex items-center p-4 border rounded-lg hover:bg-gray-50 cursor-pointer transition duration-150">
                                <input type="checkbox"
                                       id="candidate-{{ $candidate->id }}"
                                       name="candidate_ids[]"
                                       value="{{ $candidate->id }}"
                                       class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">

                                <div class="ml-4 flex-grow">
                                    <div class="font-medium text-gray-900">{{ $candidate->first_name }} {{ $candidate->last_name }}</div>
                                    <div class="text-sm text-gray-600">{{ $candidate->email }} | {{ $candidate->phone_number }}</div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        <span class="font-medium">Skills:</span> {{ $candidate->skills ?? 'Not specified' }}
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="text-center text-gray-500 p-6 border-2 border-dashed rounded-lg">
                                <p class="mb-2">You have not added any candidates to your pool yet.</p>
                                <a href="{{ route('partner.candidates.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-3 rounded-md text-sm">
                                    + Add New Candidate
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-8">
                        @if($candidates->isNotEmpty())
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-md text-lg">
                                Submit Selected Candidates
                            </button>
                        @endif
                        <a href="{{ route('partner.jobs') }}" class="ml-4 text-gray-600 hover:underline">
                            Cancel
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection