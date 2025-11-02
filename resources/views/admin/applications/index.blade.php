@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-semibold mb-6">All Submitted Applications</h1>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted By</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($applications as $app)
                                <tr>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($app->candidate) <div class="font-medium text-gray-900">{{ $app->candidate->first_name }} {{ $app->candidate->last_name }}</div>
                                            <div class="text-sm text-gray-500">{{ $app->candidate->email }}</div>
                                        @elseif($app->candidateUser) <div class="font-medium text-gray-900">{{ $app->candidateUser->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $app->candidateUser->email }}</div>
                                        @else
                                            <div class="text-sm text-gray-500">Candidate not found</div>
                                        @endif
                                    </td>
                                    
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($app->job)
                                            <div class="font-medium text-gray-900">{{ $app->job->title }}</div>
                                            <div class="text-sm text-gray-500">{{ $app->job->company_name }}</div>
                                        @else
                                            <div class="text-sm text-gray-500">Job not found</div>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($app->candidate && $app->candidate->partner) <div class="font-medium text-gray-900">{{ $app->candidate->partner->name }}</div>
                                            <div class="text-sm text-gray-500">(Partner)</div>
                                        @elseif($app->candidateUser) <div class="font-medium text-gray-900">{{ $app->candidateUser->name }}</div>
                                            <div class="text-sm text-gray-500">(Direct Apply)</div>
                                        @else
                                            <div class="text-sm text-gray-500">Unknown</div>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap">{{ $app->created_at->format('M d, Y') }}</td>
                                    
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($app->status == 'Approved')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Approved
                                            </span>
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

                                    <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                        @if($app->status == 'Pending Review')
                                            <div class="flex space-x-2">
                                                <form action="{{ route('admin.applications.approve', $app) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900 font-semibold">Approve</button>
                                                </form>
                                                
                                                <form action="{{ route('admin.applications.reject', $app) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">Reject</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-gray-500">Processed</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-4 px-4 text-center text-gray-500">
                                        No applications have been submitted yet.
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