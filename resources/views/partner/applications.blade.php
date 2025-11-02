@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-semibold mb-6">My Submitted Applications</h1>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($applications as $app)
                                <tr>
                                    <td class="py-4 px-4 whitespace-nowrap align-top">
                                        <div class="font-medium text-gray-900">{{ $app->candidate->first_name }} {{ $app->candidate->last_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $app->candidate->email }}</div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap align-top">{{ $app->job->title }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap align-top">{{ $app->job->company_name }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap align-top">{{ $app->created_at->format('M d, Y') }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap align-top">
                                        {{-- *** STATUS BLOCK UPDATED *** --}}
                                        @if($app->status == 'Approved')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Approved
                                            </span>

                                            {{-- Show client hiring status --}}
                                            @if($app->hiring_status)
                                                <div class="text-sm text-gray-600 mt-1 pt-1 border-t">
                                                    Hiring Status:
                                                    <strong class="font-medium">
                                                        @if($app->hiring_status == 'Interview Scheduled')
                                                            <span class="text-blue-700">Interview Scheduled</span>
                                                        @elseif($app->hiring_status == 'Interviewed')
                                                            <span class="text-purple-700">Interviewed</span>
                                                        @elseif($app->hiring_status == 'Selected')
                                                            <span class="text-green-700">Selected!</span>
                                                        @elseif($app->hiring_status == 'Client Rejected')
                                                            <span class="text-red-700">Rejected by Client</span>
                                                        @else
                                                            <span class="text-gray-600">{{ $app->hiring_status }}</span>
                                                        @endif
                                                    </strong>
                                                </div>
                                            @elseif(empty($app->hiring_status))
                                                <div class="text-sm text-gray-500 mt-1 pt-1 border-t">
                                                    Pending Client Action
                                                </div>
                                            @endif

                                        @elseif($app->status == 'Rejected')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ $app->status }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-4 px-4 text-center text-gray-500">
                                        You have not submitted any applications yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $applications->links() }}
                </div>

            </div>
        </div>
    </div>
</div>
@endsection