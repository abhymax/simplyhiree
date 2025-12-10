<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Master Job Report') }}
        </h2>
    </x-slot>

    <div class="py-10 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-500 hover:text-indigo-600 font-medium transition duration-150">
                    <i class="fa-solid fa-arrow-left-long mr-2"></i> Back to Dashboard
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                
                <div class="px-8 py-6 border-b border-gray-100">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Job Performance Report</h3>
                            <p class="text-gray-500 text-sm">Overview of all jobs, applications, and hiring status.</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="bg-blue-100 text-blue-800 text-xs font-bold px-3 py-1 rounded-full">
                                Total: {{ $jobs->total() }} Jobs
                            </span>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.reports.jobs') }}" class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            
                            <div class="col-span-1 md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Search</label>
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-gray-400"></i>
                                    <input type="text" name="search" value="{{ request('search') }}" 
                                           placeholder="Search by Job Title or Company..." 
                                           class="w-full pl-10 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                                <select name="status" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">All Statuses</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Live / Approved</option>
                                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Client</label>
                                <select name="client_id" class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                    <option value="">All Clients</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>
                                            {{ $client->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-4 flex justify-end gap-2">
                            <a href="{{ route('admin.reports.jobs') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">
                                Reset
                            </a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition shadow-sm">
                                <i class="fa-solid fa-filter mr-1"></i> Apply Filters
                            </button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Job Details</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Applications</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Pipeline</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Hires</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Posted Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($jobs as $job)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 text-base">{{ $job->title }}</span>
                                            <span class="text-sm text-gray-500">{{ $job->company_name }}</span>
                                            <div class="mt-1">
                                                @if($job->status === 'approved')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Live</span>
                                                @elseif($job->status === 'pending_approval')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($job->status) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center">
                                        <span class="text-xl font-bold text-indigo-600">{{ $job->jobApplications->count() }}</span>
                                        <p class="text-xs text-gray-400">Total</p>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm text-gray-600">
                                            <div>Interview: <strong>{{ $job->jobApplications->where('hiring_status', 'Interviewed')->count() }}</strong></div>
                                            <div>Selected: <strong>{{ $job->jobApplications->where('hiring_status', 'Selected')->count() }}</strong></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $joinedCount = $job->jobApplications->where('joined_status', 'Joined')->count();
                                        @endphp
                                        <span class="text-xl font-bold {{ $joinedCount > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $joinedCount }}
                                        </span>
                                        <p class="text-xs text-gray-400">Joined</p>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm text-gray-500">
                                        {{ $job->created_at->format('M d, Y') }}
                                        <p class="text-xs text-gray-400">{{ $job->created_at->diffForHumans() }}</p>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                        <div class="flex flex-col items-center">
                                            <i class="fa-regular fa-folder-open text-4xl mb-3 text-gray-300"></i>
                                            <p>No jobs found matching your filters.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $jobs->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>