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
                
                <div class="mb-6">
                    <h1 class="text-2xl font-semibold">Approved Applicants for:</h1>
                    <p class="text-xl text-gray-700">{{ $job->title }}</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resume</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hiring Status</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($applications as $app)
                                <tr>
                                    <td class="py-4 px-4 whitespace-nowrap align-top">
                                        @if($app->candidate) <div class="font-medium text-gray-900">{{ $app->candidate->first_name }} {{ $app->candidate->last_name }}</div>
                                            <div class="text-sm text-gray-600">{{ $app->candidate->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $app->candidate->phone_number }}</div>
                                        @elseif($app->candidateUser) <div class="font-medium text-gray-900">{{ $app->candidateUser->name }}</div>
                                            <div class="text-sm text-gray-600">{{ $app->candidateUser->email }}</div>
                                        @else
                                            <span class="text-gray-500">N/A</span>
                                        @endif
                                    </td>
                                    
                                    <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-600 align-top">
                                        @if($app->candidate)
                                            <div><strong>Skills:</strong> {{ $app->candidate->skills ?? 'N/A' }}</div>
                                            <div><strong>Experience:</strong> {{ $app->candidate->experience_status ?? 'N/A' }}</div>
                                            <div><strong>Education:</strong> {{ $app->candidate->education_level ?? 'N/A' }}</div>
                                            <div><strong>Expected CTC:</strong> {{ $app->candidate->expected_ctc ? '₹' . number_format($app->candidate->expected_ctc, 2) : 'N/A' }}</div>
                                        @else
                                            N/A
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap text-sm align-top">
                                        @if($app->candidate && $app->candidate->resume_path)
                                            <a href="{{ asset('storage/' . $app->candidate->resume_path) }}" 
                                               target="_blank" 
                                               class="text-blue-600 hover:text-blue-900 font-semibold">
                                                Download CV
                                            </a>
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-600 align-top">
                                        {{-- UPDATED STATUS LOGIC --}}
                                        @if($app->hiring_status == 'Interview Scheduled')
                                            <div class="font-semibold text-blue-700">Interview Scheduled</div>
                                            <div class="text-xs">{{ $app->interview_at->format('M d, Y \a\t g:i A') }}</div>
                                        @elseif($app->hiring_status == 'Client Rejected')
                                            <div class="font-semibold text-red-700">Rejected</div>
                                        @elseif($app->hiring_status == 'Interviewed')
                                            <div class="font-semibold text-purple-700">Interviewed</div>
                                        @elseif($app->hiring_status == 'No-Show')
                                            <div class="font-semibold text-yellow-700">Interview No-Show</div>
                                        @elseif($app->hiring_status == 'Selected')
                                            <div class="font-semibold text-green-700">Selected</div>
                                            <div class="text-xs">Joining: {{ $app->joining_date->format('M d, Y') }}</div>
                                        @else
                                            <div class="font-semibold text-gray-500">Pending Action</div>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap text-sm font-medium align-top">
                                        {{-- *** ENTIRE ACTION BLOCK IS UPDATED *** --}}
                                        
                                        @if(empty($app->hiring_status))
                                            {{-- 1. PENDING (SCHEDULE / REJECT) --}}
                                            <div class="flex flex-col space-y-2">
                                                <a href="{{ route('client.applications.interview.show', $app) }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-1 px-3 rounded text-center text-xs">
                                                    Schedule Interview
                                                </a>
                                                
                                                <form action="{{ route('client.applications.reject', $app) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this candidate?');">
                                                    @csrf
                                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-center text-xs">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        
                                        @elseif($app->hiring_status == 'Interview Scheduled')
                                            {{-- 2. POST-INTERVIEW (APPEARED / NO-SHOW) --}}
                                            <div class="flex flex-col space-y-2">
                                                <form action="{{ route('client.applications.interview.appeared', $app) }}" method="POST" onsubmit="return confirm('Mark this candidate as \'Interview Appeared\'?');">
                                                    @csrf
                                                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-1 px-3 rounded text-center text-xs">
                                                        Interview Appeared
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('client.applications.interview.noshow', $app) }}" method="POST" onsubmit="return confirm('Mark this candidate as \'No-Show\'?');">
                                                    @csrf
                                                    <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-bold py-1 px-3 rounded text-center text-xs">
                                                        No-Show
                                                    </button>
                                                </form>
                                            </div>

                                        @elseif($app->hiring_status == 'Interviewed' || $app->hiring_status == 'No-Show')
                                            {{-- 3. FINAL DECISION (SELECT / REJECT) --}}
                                            <div class="flex flex-col space-y-2">
                                                <a href="{{ route('client.applications.select.show', $app) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-center text-xs">
                                                    Select for Job
                                                </a>
                                                
                                                <form action="{{ route('client.applications.reject', $app) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this candidate?');">
                                                    @csrf
                                                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-3 rounded text-center text-xs">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>

                                        @else
                                            {{-- 4. FINALIZED (REJECTED / SELECTED) --}}
                                            <span class="text-gray-400">--</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 px-4 text-center text-gray-500">
                                        No applicants have been approved for this job yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $applications->links() }}
                </div>
                
                <div class="mt-8">
                    <a href="{{ route('client.dashboard') }}" class="text-blue-600 hover:underline">&larr; Back to Dashboard</a>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection