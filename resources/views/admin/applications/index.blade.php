<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Application Management') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-indigo-600 font-medium transition duration-150">
                    <i class="fa-solid fa-arrow-left-long mr-2"></i> Back to Dashboard
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                
                <div class="border-b border-gray-100 bg-white p-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">All Applications</h3>
                            <p class="text-gray-500 text-sm mt-1">Track candidates from application to hiring.</p>
                        </div>
                        <div class="mt-4 md:mt-0">
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                                Total: {{ $applications->total() }}
                            </span>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.applications.index') }}" class="flex flex-col lg:flex-row gap-4 lg:items-end bg-gray-50 p-4 rounded-xl border border-gray-200">
                        
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email..." class="w-full pl-10 text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                            <select name="status" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Statuses</option>
                                @foreach(['Pending Review', 'Approved', 'Rejected', 'Interview Scheduled', 'Selected', 'Joined'] as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ $status }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Job Role</label>
                            <select name="job_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Jobs</option>
                                @foreach($jobs as $job)
                                    <option value="{{ $job->id }}" {{ request('job_id') == $job->id ? 'selected' : '' }}>{{ Str::limit($job->title, 20) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Partner</label>
                            <select name="partner_id" class="w-full text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">All Partners</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>{{ Str::limit($partner->name, 20) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full lg:w-auto flex items-center gap-2">
                            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg text-sm font-bold shadow hover:bg-indigo-700 transition h-[38px] flex items-center justify-center whitespace-nowrap">
                                Filter
                            </button>
                            @if(request()->anyFilled(['search', 'status', 'job_id', 'partner_id']))
                                <a href="{{ route('admin.applications.index') }}" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg text-sm font-bold hover:bg-gray-100 h-[38px] flex items-center justify-center transition" title="Reset Filters">
                                    <i class="fa-solid fa-rotate-left"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Candidate</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Job Applied For</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Submitted By</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Live Status</th>
                                <th class="py-4 px-6 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Admin Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($applications as $application)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="py-4 px-6">
                                        <a href="{{ route('admin.applications.show', $application->id) }}" class="font-bold text-indigo-600 hover:text-indigo-900 hover:underline">
                                            {{ $application->candidate->first_name ?? 'N/A' }} {{ $application->candidate->last_name ?? '' }}
                                        </a>
                                        <div class="text-xs text-gray-500">{{ $application->candidate->email ?? '' }}</div>
                                        <div class="text-xs text-gray-400 mt-1">
                                            Applied: {{ $application->created_at->format('M d, Y') }}
                                        </div>
                                    </td>
                                    
                                    <td class="py-4 px-6">
                                        <div class="text-sm text-gray-900 font-medium">{{ $application->job->title ?? 'Deleted Job' }}</div>
                                        <div class="text-xs text-gray-500">{{ $application->job->company_name ?? '' }}</div>
                                    </td>
                                    
                                    <td class="py-4 px-6">
                                        @if($application->candidate && $application->candidate->partner)
                                            <div class="flex items-center">
                                                <div class="h-6 w-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold mr-2">
                                                    {{ substr($application->candidate->partner->name, 0, 1) }}
                                                </div>
                                                <span class="text-sm text-gray-600">{{ $application->candidate->partner->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400">Self/Unknown</span>
                                        @endif
                                    </td>

                                    <td class="py-4 px-6">
                                        @php
                                            $status = strtolower($application->status);
                                            $hiringStatus = $application->hiring_status;
                                        @endphp

                                        @if($status === 'pending review')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                                                <i class="fa-solid fa-clock mr-1 self-center"></i> Pending Review
                                            </span>
                                        
                                        @elseif($status === 'rejected')
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                                <i class="fa-solid fa-ban mr-1 self-center"></i> Rejected
                                            </span>

                                        @elseif($status === 'approved')
                                            @if($hiringStatus == 'Interview Scheduled')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-blue-100 text-blue-800 border border-blue-200">
                                                    <i class="fa-solid fa-calendar-check mr-1 self-center"></i> Interview Scheduled
                                                </span>
                                            @elseif($hiringStatus == 'Selected')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-teal-100 text-teal-800 border border-teal-200">
                                                    <i class="fa-solid fa-trophy mr-1 self-center"></i> Selected
                                                </span>
                                            @elseif($hiringStatus == 'Joined')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-100 text-green-800 border border-green-200">
                                                    <i class="fa-solid fa-check-double mr-1 self-center"></i> Joined
                                                </span>
                                            @elseif($hiringStatus == 'Client Rejected')
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-gray-100 text-red-600 border border-red-200">
                                                    <i class="fa-solid fa-xmark mr-1 self-center"></i> Client Rejected
                                                </span>
                                            @else
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-green-50 text-green-700 border border-green-200">
                                                    <i class="fa-solid fa-check mr-1 self-center"></i> Approved (With Client)
                                                </span>
                                            @endif
                                        @endif
                                    </td>

                                    <td class="py-4 px-6 text-right text-sm font-medium">
                                        @php
                                            $status = strtolower($application->status);
                                        @endphp
                                        
                                        @if($status === 'pending review')
                                            <div class="flex justify-end gap-2">
                                                <form action="{{ route('admin.applications.approve', $application->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 p-2 rounded transition" title="Approve & Forward">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.applications.reject', $application->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-2 rounded transition" title="Reject">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <a href="{{ route('admin.applications.show', $application->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-2 rounded transition inline-block" title="View Details">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fa-regular fa-folder-open text-3xl mb-2 text-gray-300"></i>
                                            <p>No applications found matching your filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    {{ $applications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>