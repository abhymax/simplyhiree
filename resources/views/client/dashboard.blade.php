<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Client Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    {{-- UPDATE: Show Client ID --}}
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900">Dashboard Overview</h1>
                        <span class="bg-blue-100 text-blue-800 text-sm font-bold px-3 py-1 rounded-full border border-blue-200">
                            ID: {{ Auth::user()->client_code }}
                        </span>
                    </div>
                    <p class="text-gray-600 mt-1">Welcome back, {{ Auth::user()->name }}</p>
                </div>
                <a href="{{ route('client.jobs.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg transform hover:scale-105 transition duration-150 flex items-center">
                    <i class="fa-solid fa-plus mr-2"></i> Post New Job
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                <a href="#my-jobs" class="block p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-blue-300 transition">
                    <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900"><i class="fa-solid fa-briefcase mr-2 text-blue-500"></i> Manage Jobs</h5>
                    <p class="font-normal text-gray-600 text-sm">View applicants and edit active postings.</p>
                </a>

                <a href="{{ route('client.profile.company') }}" class="block p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-yellow-300 transition relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-yellow-50 rounded-bl-full -mr-8 -mt-8 transition group-hover:bg-yellow-100"></div>
                    <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900 relative z-10">
                        <i class="fa-solid fa-building mr-2 text-yellow-500"></i> Company Profile
                    </h5>
                    <p class="font-normal text-gray-600 text-sm relative z-10">Update logo, industry, and billing info.</p>
                </a>

                <a href="{{ route('client.billing') }}" class="block p-6 bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md hover:border-green-300 transition">
                    <h5 class="mb-2 text-xl font-bold tracking-tight text-gray-900"><i class="fa-solid fa-file-invoice-dollar mr-2 text-green-500"></i> Billing & Invoices</h5>
                    <p class="font-normal text-gray-600 text-sm">View payment history and download invoices.</p>
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

            <div id="my-jobs" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
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
                                                <a href="{{ route('jobs.show', $job->id) }}" class="font-bold text-blue-600 text-base hover:underline hover:text-blue-800">
                                                    {{ $job->title }}
                                                </a>
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
                                            </div>
                                        </td>

                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if($job->status == 'approved')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 border border-green-200">
                                                    <i class="fa-solid fa-check mr-1 self-center"></i> Active
                                                </span>
                                            @elseif($job->status == 'pending_approval')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                    <i class="fa-solid fa-clock mr-1 self-center"></i> Pending
                                                </span>
                                            @elseif($job->status == 'on_hold')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-orange-100 text-orange-800 border border-orange-200">
                                                    <i class="fa-solid fa-pause mr-1 self-center"></i> On Hold
                                                </span>
                                            @elseif($job->status == 'closed')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800 border border-gray-300">
                                                    <i class="fa-solid fa-eye-slash mr-1 self-center"></i> Closed
                                                </span>
                                            @elseif($job->status == 'rejected')
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 border border-red-200">
                                                    <i class="fa-solid fa-xmark mr-1 self-center"></i> Rejected
                                                </span>
                                            @endif
                                        </td>

                                        <td class="py-4 px-4 whitespace-nowrap text-sm">
                                            @if($job->status == 'approved')
                                                <a href="{{ route('client.jobs.applicants', $job) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                    View Applicants ({{ $job->jobApplications->where('status', 'Approved')->count() }})
                                                </a>
                                            @else
                                                <span class="text-gray-400 text-xs italic">Review N/A</span>
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
</x-app-layout>