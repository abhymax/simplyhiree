<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h3 class="text-2xl font-semibold mb-2">Invoice Status</h3>
                    <p class="text-gray-600 mb-6">This report shows all selected candidates and their calculated invoice date based on the client's billable period.</p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Title</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joining Date</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Billable Period</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Date</th>
                                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($placements as $item)
                                    <tr>
                                        <td class="py-4 px-4 whitespace-nowrap font-medium text-gray-900">{{ $item->candidate_name }}</td>
                                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-600">{{ $item->client_name }}</td>
                                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-600">{{ $item->job_title }}</td>
                                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-600">{{ $item->joining_date }}</td>
                                        <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-600">{{ $item->billable_period }}</td>
                                        <td class="py-4 px-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ $item->invoice_date }}</td>
                                        <td class="py-4 px-4 whitespace-nowrap">
                                            @if($item->status == 'Billable')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    <i class="fa-solid fa-check mr-1 mt-0.5"></i>
                                                    Billable
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    <i class="fa-solid fa-clock mr-1 mt-0.5"></i>
                                                    Pending
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-6 px-4 text-center text-gray-500">
                                            No "Selected" candidates found with complete billing information.
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