@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Today's Scheduled Interviews</h1>
                        <p class="text-gray-600">{{ date('F j, Y') }}</p>
                    </div>
                    <a href="{{ route('client.dashboard') }}" class="text-blue-600 hover:underline">
                        &larr; Back to Dashboard
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Time</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Candidate</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Job Role</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($todayInterviews as $app)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-blue-700">
                                            {{ $app->interview_at->format('g:i A') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="font-medium text-gray-900">{{ $app->candidate_name }}</div>
                                        @if($app->candidate)
                                            <div class="text-xs text-gray-500">{{ $app->candidate->phone_number }}</div>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="text-sm text-gray-800">{{ $app->job->title }}</div>
                                    </td>
                                    <td class="py-4 px-4">
                                        <a href="{{ route('client.jobs.applicants', $app->job_id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded">
                                            View & Manage
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-gray-500">
                                        No interviews scheduled for today.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection