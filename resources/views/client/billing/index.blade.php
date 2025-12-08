@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-6">My Invoices & Payments</h2>
                
                @if($billingData->isEmpty())
                    <div class="text-center py-10 bg-gray-50 rounded-lg">
                        <i class="fa-solid fa-file-invoice-dollar text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">No billable placements found yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border border-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase">Candidate / Job</th>
                                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase">Dates</th>
                                    <th class="px-6 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-600 uppercase">Amount</th>
                                    <th class="px-6 py-3 border-b border-gray-200 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($billingData as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->candidate_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->job_title }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">Joined: {{ $item->joining_date }}</div>
                                            <div class="text-xs text-gray-500 font-semibold">Invoice Due: {{ $item->invoice_date }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-800">{{ $item->amount }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $item->status_color }}">
                                                {{ $item->status }}
                                            </span>
                                            @if($item->paid_at)
                                                <div class="text-[10px] text-gray-400 mt-1">Paid on {{ $item->paid_at }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection