@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Client Dashboard</h1>
                <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name }}</p>
            </div>
            <a href="{{ route('client.jobs.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-sm transition duration-150 flex items-center">
                <i class="fa-solid fa-plus mr-2"></i> Post New Job
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                        <i class="fa-solid fa-briefcase text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Jobs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalJobs }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                        <i class="fa-solid fa-circle-check text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Jobs</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeJobs }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-orange-100 text-orange-600 mr-4">
                        <i class="fa-solid fa-users text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Applicants</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalApplicants }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100 p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                        <i class="fa-solid fa-handshake text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Hires</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalHires }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h3 class="text-xl font-bold text-gray-800 mb-4">My Job Postings</h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Designation / Role</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Requirements</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Applicants</th>
                                <th class="py-3 px-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Posted On</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($jobs as $job)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="py-4 px-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 text-base">{{ $job->title }}</span>
                                            <span class="text-xs text-gray-500 mt-1">
                                                <i class="fa-solid fa-location-dot mr-1"></i> {{ $job->location }}
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                <i class="fa-solid fa-briefcase mr-1"></i> {{ $job->job_type }}
                                            </span>
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 text-sm text-gray-700">
                                        <div class="space-y-1">
                                            <div class="flex items-center">
                                                <span class="w-20 text-xs font-semibold text-gray-500 uppercase">Openings:</span>
                                                <span class="font-bold text-blue-600">{{ $job->openings ?? 'N/A' }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="w-20 text-xs font-semibold text-gray-500 uppercase">Exp:</span>
                                                <span>{{ $job->experienceLevel->name ?? 'Any' }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="w-20 text-xs font-semibold text-gray-500 uppercase">Edu:</span>
                                                <span>{{ $job->educationLevel->name ?? 'Any' }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="w-20 text-xs font-semibold text-gray-500 uppercase">Age:</span>
                                                <span>
                                                    @if($job->min_age && $job->max_age)
                                                        {{ $job->min_age }} - {{ $job->max_age }} yrs
                                                    @else
                                                        Any
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap">
                                        @if($job->status == 'approved')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                                <i class="fa-solid fa-check mr-1 self-center"></i> Approved
                                            </span>
                                        @elseif($job->status == 'rejected')
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                                <i class="fa-solid fa-xmark mr-1 self-center"></i> Rejected
                                            </span>
                                        @else
                                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fa-solid fa-clock mr-1 self-center"></i> Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap text-sm">
                                        @if($job->status == 'approved')
                                            <a href="{{ route('client.jobs.applicants', $job) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                View Applicants ({{ $job->jobApplications->where('status', 'Approved')->count() }})
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Waiting for approval</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $job->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-12 px-4 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fa-regular fa-folder-open text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-lg font-medium text-gray-900">No jobs posted yet</p>
                                            <p class="text-sm text-gray-500 mb-4">Get started by creating your first job listing.</p>
                                            <a href="{{ route('client.jobs.create') }}" class="text-blue-600 hover:underline">Post a Job Now</a>
                                        </div>
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