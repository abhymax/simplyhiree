@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-800 dark:text-white">Superadmin Dashboard</h1>
            <p class="mt-1 text-lg text-gray-500 dark:text-gray-400">Welcome, {{ Auth::user()->name }}. Manage the platform from here.</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- Pending Jobs Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg transform hover:scale-105 transition-transform duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-yellow-600 dark:text-yellow-400 uppercase tracking-wider">Pending Jobs</div>
                            <div class="mt-1 text-4xl font-bold text-yellow-800 dark:text-yellow-200">{{ $pendingJobsCount }}</div>
                        </div>
                        <div class="bg-yellow-100 dark:bg-yellow-500/20 p-3 rounded-full">
                            <svg class="h-8 w-8 text-yellow-600 dark:text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 rounded-b-lg">
                    {{-- THIS IS THE CORRECTED LINK --}}
                    <a href="{{ route('admin.jobs.pending') }}" class="text-sm font-semibold text-yellow-700 dark:text-yellow-300 hover:underline">View Pending Jobs &rarr;</a>
                </div>
            </div>

            <!-- Total Users Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg transform hover:scale-105 transition-transform duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-green-600 dark:text-green-400 uppercase tracking-wider">Total Users</div>
                            <div class="mt-1 text-4xl font-bold text-green-800 dark:text-green-200">...</div>
                        </div>
                        <div class="bg-green-100 dark:bg-green-500/20 p-3 rounded-full">
                            <svg class="h-8 w-8 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.68c.119-.046.237-.092.355-.137m7.533 2.493a4.125 4.125 0 00-4.122-4.122A4.125 4.125 0 0015 15.128c0 1.113.285 2.16.786 3.07M8.624 15.128a4.125 4.125 0 00-4.122-4.122A4.125 4.125 0 000 15.128c0 1.113.285 2.16.786 3.07M8.624 21a12.318 12.318 0 004.755-1.002L11.964 19.4c-1.07.388-2.235.604-3.46.604a11.916 11.916 0 01-6.224-1.957l.001-.109a6.375 6.375 0 0111.964-4.68c.119-.046.237-.092.355-.137m-8.119-4.112A4.125 4.125 0 008.624 7.128c1.113 0 2.16.285 3.07.786A4.125 4.125 0 0015.128 11c1.113 0 2.16.285 3.07.786A4.125 4.125 0 0021.5 11c.475 0 .937.067 1.375.195m-15.048-.02A12.318 12.318 0 018.624 3c2.331 0 4.512.645 6.374 1.766l.001.109a6.375 6.375 0 00-11.964 4.68c-.119.046-.237.092-.355-.137" /></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 rounded-b-lg">
                    <a href="#" class="text-sm font-semibold text-green-700 dark:text-green-300 hover:underline">Manage Users &rarr;</a>
                </div>
            </div>

            <!-- Total Partners Card -->
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg transform hover:scale-105 transition-transform duration-300">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-blue-600 dark:text-blue-400 uppercase tracking-wider">Total Partners</div>
                            <div class="mt-1 text-4xl font-bold text-blue-800 dark:text-blue-200">...</div>
                        </div>
                        <div class="bg-blue-100 dark:bg-blue-500/20 p-3 rounded-full">
                           <svg class="h-8 w-8 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-7.5-2.962A3 3 0 0110.5 9.75v-.75a3 3 0 00-3-3h-1.5a3 3 0 00-3 3v.75a3 3 0 01-1.5 2.622m9 2.962-2.5-2.5-2.5 2.5m0 0l-2.5-2.5 2.5-2.5m2.5 2.5l2.5-2.5-2.5 2.5z" /></svg>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 rounded-b-lg">
                    <a href="#" class="text-sm font-semibold text-blue-700 dark:text-blue-300 hover:underline">Manage Partners &rarr;</a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

