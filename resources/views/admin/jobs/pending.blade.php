@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700">
                <h1 class="text-3xl font-bold">Jobs Pending Approval</h1>
                <p class="mt-1 text-gray-500">Review, approve, or manage partner access for the jobs below.</p>
            </div>

            <div class="p-6">
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Job Details</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Requirements</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client</th>
                                <th class="py-3 px-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($jobs as $job)
                                <tr>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $job->title }}</div>
                                        <div class="text-xs text-gray-500">{{ $job->company_name }} - {{ $job->location }}</div>
                                        <div class="text-xs text-gray-500 mt-1">Openings: <span class="font-bold">{{ $job->openings ?? 'N/A' }}</span></div>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        Exp: {{ $job->experienceLevel->name ?? 'Not Specified' }}<br>
                                        Edu: {{ $job->educationLevel->name ?? 'Not Specified' }}<br>
                                        Age: {{ $job->min_age ?? 'N/A' }} - {{ $job->max_age ?? 'N/A' }} yrs<br>
                                        Gender: {{ $job->gender_preference ?? 'Any' }}
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ $job->user->name }}<br>
                                        <span class="text-xs">{{ $job->created_at->format('d M, Y') }}</span>
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        <a href="{{ route('admin.jobs.manage', $job) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">Manage</a>
                                        <form action="{{ route('admin.jobs.approve', $job) }}" method="POST" class="inline space-x-2">
                                            @csrf
                                            <input type="number" name="payout_amount" placeholder="Payout Amount" class="w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm" required>
                                            <input type="number" name="minimum_stay_days" placeholder="Min. Stay (Days)" class="w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-sm" required>
                                            <button type="submit" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 font-semibold">Approve</button>
                                        </form>
                                        
                                        <form action="{{ route('admin.jobs.reject', $job) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-semibold">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 px-4 text-center text-gray-500 dark:text-gray-400">
                                        There are no jobs pending approval right now.
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