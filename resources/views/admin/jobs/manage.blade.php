@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('admin.jobs.exclusions.update', $job) }}" method="POST">
            @csrf
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                    <h1 class="text-3xl font-bold">Manage Partner Access</h1>
                    <p class="mt-1 text-gray-500">For the job: <span class="font-semibold">{{ $job->title }}</span></p>
                </div>

                <div class="p-6">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Exclude Partners</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Check the box next to any partner you want to **prevent** from seeing this job vacancy.</p>

                    <div class="mt-6 space-y-4">
                        @forelse ($allPartners as $partner)
                            <label for="partner_{{ $partner->id }}" class="flex items-center p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <input type="checkbox"
                                       id="partner_{{ $partner->id }}"
                                       name="excluded_partners[]"
                                       value="{{ $partner->id }}"
                                       class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                       @if(in_array($partner->id, $excludedPartnerIds)) checked @endif
                                >
                                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">{{ $partner->name }} ({{ $partner->email }})</span>
                            </label>
                        @empty
                            <p class="text-gray-500">There are no partners registered on the platform yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/50 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm  bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Save Exclusions
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
