@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800 dark:text-white">My Candidate Pool</h1>
                <p class="mt-1 text-lg text-gray-500 dark:text-gray-400">Manage all your sourced candidates here.</p>
            </div>
            <a href="{{ route('partner.candidates.create') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition duration-300">
                Add New Candidate
            </a>
        </div>

        <!-- Filters (Placeholder for now, as per your screenshot) -->
        <div class="mb-6 p-4 bg-white dark:bg-gray-800 rounded-lg shadow-md">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <input type="text" placeholder="Search by name, email, or phone..." class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                <select class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Filter by Job</option>
                    <!-- Job options will be populated later -->
                </select>
                <select class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">Filter by Status</option>
                    <!-- Status options will be populated later -->
                </select>
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700">Filter</button>
            </div>
        </div>

        <!-- Candidate Table -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Candidate Name</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Contact</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Experience</th>
                            <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current Status</th>
                            <th class="py-3 px-4 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($candidates as $candidate)
                            <tr>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $candidate->first_name }} {{ $candidate->last_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $candidate->job_interest }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $candidate->phone_number }}</div>
                                    <div class="text-sm text-gray-500">{{ $candidate->email }}</div>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $candidate->experience_status }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        In Pool
                                    </span>
                                </td>
                                <td class="py-4 px-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-semibold">View</a>
                                    <a href="#" class="ml-4 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 font-semibold">Assign to Job</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 px-4 text-center text-gray-500 dark:text-gray-400">
                                    You have not added any candidates to your pool yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

