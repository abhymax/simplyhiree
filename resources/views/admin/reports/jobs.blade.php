@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Master Job Report</h1>

        @if($jobs->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-6 text-center">
                <p class="text-gray-500">No jobs found.</p>
            </div>
        @else
            <div class="space-y-8">
                @foreach($jobs as $job)
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h2 class="text-2xl font-semibold text-gray-800">{{ $job->title }}</h2>
                                    <p class="text-md text-gray-600">{{ $job->user->name }} - {{ $job->location }}</p>
                                    <span class="text-sm font-medium px-2 py-1 rounded-full
                                        @switch($job->status)
                                            @case('approved') bg-green-100 text-green-800 @break
                                            @case('pending_approval') bg-yellow-100 text-yellow-800 @break
                                            @case('rejected') bg-red-100 text-red-800 @break
                                            @default bg-gray-100 text-gray-800
                                        @endswitch">
                                        {{ ucfirst(str_replace('_', ' ', $job->status)) }}
                                    </span>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-semibold text-gray-700">Salary: {{ $job->salary }}</p>
                                    <p class="text-sm text-gray-500">Posted on: {{ $job->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-700 mb-4">Lined-Up Candidates ({{ $job->jobApplications->count() }})</h3>
                            @if($job->jobApplications->isEmpty())
                                <p class="text-gray-500">No candidates have been submitted for this job yet.</p>
                            @else
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="py-3 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Candidate Name</th>
                                                <th class="py-3 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Submitted By (Partner)</th>
                                                <th class="py-3 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">CV</th>
                                                <th class="py-3 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($job->jobApplications as $application)
                                                <tr>
                                                    <td class="py-4 px-6 whitespace-nowrap">
                                                        {{ optional($application->candidateUser)->name ?? 'N/A' }}
                                                    </td>
                                                    <td class="py-4 px-6 whitespace-nowrap">
                                                        {{ optional(optional(optional($application->candidate)->partner)->user)->name ?? 'Direct Application' }}
                                                    </td>
                                                    <td class="py-4 px-6 whitespace-nowrap text-center">
                                                        @if(optional($application->candidate)->cv_path)
                                                            <a href="{{ Storage::url($application->candidate->cv_path) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                                                Download CV
                                                            </a>
                                                        @else
                                                            <span class="text-gray-400">Not Provided</span>
                                                        @endif
                                                    </td>
                                                    <td class="py-4 px-6 whitespace-nowrap">
                                                        <span class="font-medium">{{ $application->status }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
