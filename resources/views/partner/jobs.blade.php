@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-semibold mb-6">Available Jobs</h1>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Salary</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payout Amount</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payout Condition</th>
                                <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($jobs as $job)
                                <tr>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $job->title }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $job->company_name }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $job->location }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap">{{ $job->salary }}</td>
                                    <td class="py-4 px-4 whitespace-nowrap font-semibold text-green-600">
                                        ${{ number_format($job->payout_amount, 2) }}
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        Paid on {{ $job->minimum_stay_days }}th day of joining
                                    </td>
                                    <td class="py-4 px-4 whitespace-nowrap">
                                        <a href="{{ route('partner.jobs.showApplyForm', $job->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-md text-sm">
                                            Submit Candidate
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 px-4 text-center text-gray-500">No approved jobs are currently available.</td>
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