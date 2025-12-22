<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Applications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('partner.dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Dashboard
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Application History</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Candidate Name</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Job Applied For</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Company</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Applied On</th>
                                    <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($applications as $application)
                                    <tr>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if($application->candidate)
                                                <div class="font-medium text-gray-900">{{ $application->candidate->first_name }} {{ $application->candidate->last_name }}</div>
                                                <div class="text-xs text-gray-500">{{ $application->candidate->email }}</div>
                                            @else
                                                <span class="text-red-500 italic">Candidate Deleted</span>
                                            @endif
                                        </td>

                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if($application->job)
                                                <a href="{{ route('partner.jobs.show', $application->job->id) }}" class="text-blue-600 hover:underline font-medium">
                                                    {{ $application->job->title }}
                                                </a>
                                            @else
                                                <span class="text-gray-400 italic">Job No Longer Available</span>
                                            @endif
                                        </td>

                                        <td class="py-4 px-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $application->job->company_name ?? 'N/A' }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ $application->job->location ?? 'N/A' }}
                                            </div>
                                        </td>

                                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $application->created_at->format('M d, Y') }}
                                        </td>

                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @php
                                                $statusClasses = [
                                                    'Pending Review' => 'bg-yellow-100 text-yellow-800',
                                                    'Approved' => 'bg-blue-100 text-blue-800',
                                                    'Interview Scheduled' => 'bg-purple-100 text-purple-800',
                                                    'Selected' => 'bg-green-100 text-green-800',
                                                    'Rejected' => 'bg-red-100 text-red-800',
                                                ];
                                                $class = $statusClasses[$application->status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                                                {{ $application->status }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-6 px-4 text-center text-gray-500">
                                            No applications submitted yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $applications->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>