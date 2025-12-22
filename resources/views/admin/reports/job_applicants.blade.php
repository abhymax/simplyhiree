<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Job Applicant Report') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 flex justify-between items-center">
                <a href="{{ route('admin.reports.jobs') }}" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Master Report
                </a>
                
                <div class="bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                    <span class="text-gray-500 text-xs uppercase font-bold">Job Profile</span>
                    <h1 class="text-lg font-bold text-gray-900">{{ $job->title }} <span class="text-gray-400">at</span> {{ $job->company_name }}</h1>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Candidate Name</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Source (Partner)</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Applied Date</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Current Pipeline Status</th>
                                <th class="py-4 px-6 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($applications as $application)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900">{{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $application->candidate->email }}</div>
                                        <div class="text-xs text-gray-500">{{ $application->candidate->phone_number }}</div>
                                    </td>
                                    
                                    <td class="py-4 px-6">
                                        @if($application->candidate->partner)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $application->candidate->partner->name }}
                                            </span>
                                        @else
                                            <span class="text-gray-400 text-xs">Direct/Unknown</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-sm text-gray-600">
                                        {{ $application->created_at->format('M d, Y') }}
                                    </td>

                                    <td class="py-4 px-6">
                                        {{-- STATUS LOGIC --}}
                                        @php
                                            $adminStatus = strtolower($application->status);
                                            $clientStatus = $application->hiring_status;
                                        @endphp

                                        @if($adminStatus === 'pending review')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800">
                                                Pending Admin Review
                                            </span>
                                        @elseif($adminStatus === 'rejected')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800">
                                                Rejected by Admin
                                            </span>
                                        @elseif($adminStatus === 'approved')
                                            @if($clientStatus == 'Interview Scheduled')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800">
                                                    Interview Scheduled
                                                </span>
                                            @elseif($clientStatus == 'Selected')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-teal-100 text-teal-800">
                                                    Selected
                                                </span>
                                            @elseif($clientStatus == 'Joined')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800">
                                                    Joined
                                                </span>
                                            @elseif($clientStatus == 'Client Rejected')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-red-600">
                                                    Client Rejected
                                                </span>
                                            @else
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-50 text-green-700">
                                                    With Client (Reviewing)
                                                </span>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-right">
                                        <a href="{{ route('admin.applications.show', $application->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded text-xs font-bold">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        No candidates have applied for this job yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-gray-50">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>