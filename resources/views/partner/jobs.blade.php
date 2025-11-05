@extends('layouts.app')

@section('content')
<div class="py-12 bg-gray-100">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-8">Vacancies</h1>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">FILTERS</h2>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="col-span-1 md:col-span-2">
                    <input type="text" class="form-input w-full rounded-md border-gray-300 shadow-sm" placeholder="Type for hints...">
                </div>
                <div>
                    <select class="form-select w-full rounded-md border-gray-300 shadow-sm">
                        <option>Select Category</option>
                        <!-- Add categories here -->
                    </select>
                </div>
                <div>
                    <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md shadow-sm">
                        Search
                    </button>
                </div>
            </div>
        </div>

        <!-- Jobs Table -->
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold text-gray-800">Available Jobs</h2>
                    <div class="w-1/3">
                        <input type="text" class="form-input w-full rounded-md border-gray-300 shadow-sm" placeholder="Search...">
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-200">
                            <tr>
                                <th class="py-3 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">#</th>
                                <th class="py-3 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Job Title</th>
                                <th class="py-3 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Payout</th>
                                <th class="py-3 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Min Stay</th>
                                <th class="py-3 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($jobs as $job)
                                <tr>
                                    <td class="py-4 px-6 whitespace-nowrap">{{ $loop->iteration }}</td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        <div class="font-bold text-gray-800">{{ $job->title }}</div>
                                        <div class="text-sm text-gray-500">{{ $job->company_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $job->location }}</div>
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap font-semibold text-green-600 text-lg">
                                        ₹{{ number_format($job->payout_amount, 0) }}
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap">
                                        {{ $job->minimum_stay_days }} days
                                    </td>
                                    <td class="py-4 px-6 whitespace-nowrap text-center">
                                        <a href="{{ route('partner.jobs.showApplyForm', $job->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-md text-sm shadow-md">
                                            View & Apply
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 px-6 text-center text-gray-500">No approved jobs are currently available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="mt-6">
                    {{-- {{ $jobs->links() }} --}}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
