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

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

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
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Job Title or Company..." class="w-full rounded-lg border-gray-300 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                                <select name="status" class="w-full rounded-lg border-gray-300 text-sm">
                                    <option value="">All Statuses</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Live / Approved</option>
                                    <option value="pending_approval" {{ request('status') == 'pending_approval' ? 'selected' : '' }}>Pending</option>
                                    <option value="on_hold" {{ request('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Client</label>
                                <select name="client_id" class="w-full rounded-lg border-gray-300 text-sm">
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
                            <a href="{{ route('admin.reports.jobs') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50 transition">Reset</a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Apply Filters</button>
                        </div>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Job Details</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stats (Click to View)</th>
                                <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($jobs as $job)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex flex-col">
                                            <a href="{{ route('jobs.show', $job->id) }}" class="font-bold text-indigo-600 text-base hover:underline" target="_blank">
                                                {{ $job->title }}
                                            </a>
                                            <span class="text-sm text-gray-500">{{ $job->company_name }}</span>
                                            <span class="text-xs text-gray-400 mt-1">Posted {{ $job->created_at->format('M d, Y') }}</span>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 text-center align-top">
                                        <div class="flex flex-col items-center gap-2">
                                            @if($job->jobApplications->count() > 0)
                                                <a href="{{ route('admin.reports.jobs.applicants', $job->id) }}" 
                                                   class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 border border-blue-200 transition group"
                                                   title="View all applicants for this job">
                                                    {{ $job->jobApplications->count() }} Applicants
                                                    <i class="fa-solid fa-arrow-right ml-1 text-blue-600 group-hover:translate-x-1 transition-transform"></i>
                                                </a>
                                            @else
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500 border border-gray-200">
                                                    0 Applicants
                                                </span>
                                            @endif

                                            @php
                                                $joinedCount = $job->jobApplications->where('joined_status', 'Joined')->count();
                                            @endphp
                                            @if($joinedCount > 0)
                                                <span class="text-xs text-green-700 font-bold bg-green-50 px-2 py-0.5 rounded border border-green-100">
                                                    <i class="fa-solid fa-check-double mr-1"></i> {{ $joinedCount }} Joined
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-center">
                                        @if($job->status === 'approved')
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800">Live</span>
                                        @elseif($job->status === 'pending_approval')
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($job->status === 'on_hold')
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-orange-100 text-orange-800">On Hold</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($job->status) }}</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('jobs.show', $job->id) }}" class="text-blue-600 hover:text-blue-900 border border-blue-200 bg-blue-50 px-3 py-1 rounded text-xs" target="_blank">View</a>

                                            @if($job->status === 'approved')
                                                <form action="{{ route('admin.jobs.status.update', $job->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="on_hold">
                                                    <button type="submit" class="text-orange-600 hover:text-orange-900 border border-orange-200 bg-orange-50 px-3 py-1 rounded text-xs" title="Put on Hold">Hold</button>
                                                </form>
                                            @elseif($job->status === 'on_hold' || $job->status === 'closed')
                                                <form action="{{ route('admin.jobs.status.update', $job->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="text-green-600 hover:text-green-900 border border-green-200 bg-green-50 px-3 py-1 rounded text-xs" title="Make Live">Activate</button>
                                                </form>
                                            @endif

                                            <form action="{{ route('admin.jobs.destroy', $job->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this job permanently?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900 border border-red-200 bg-red-50 px-3 py-1 rounded text-xs">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-10 text-center text-gray-500">No jobs found.</td></tr>
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