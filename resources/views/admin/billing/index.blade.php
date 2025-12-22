@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h2 class="text-2xl font-bold mb-6">Billing & Invoicing Report</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Candidate</th>
                                <th class="py-3 px-6 text-left">Client / Job</th>
                                <th class="py-3 px-6 text-center">Invoice Date</th>
                                <th class="py-3 px-6 text-center">Status</th>
                                <th class="py-3 px-6 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            @forelse($placements as $item)
                                <tr class="border-b border-gray-200 hover:bg-gray-50 {{ $item->row_class }}">
                                    <td class="py-3 px-6 text-left whitespace-nowrap">
                                        <div class="font-medium">{{ $item->candidate_name }}</div>
                                        <div class="text-xs text-gray-500">Joined: {{ $item->joining_date }}</div>
                                    </td>
                                    <td class="py-3 px-6 text-left">
                                        {{-- UPDATE: Added Client Code logic --}}
                                        {{-- Note: We need to ensure $item has access to client_code. 
                                           The AdminController creates this object manually. 
                                           So we just use the relation if available or pass it in controller. 
                                           Since $item is a plain object created in controller, we need to update Controller too, 
                                           OR we can just fetch user relation here if lazily loaded, BUT
                                           the cleanest way is to access the relationship since it was eager loaded in controller.
                                        --}}
                                        <div class="font-medium">
                                            {{ $item->client_name }}
                                            {{-- Assuming client object is accessible via relation in loop before flattening --}}
                                            {{-- To keep it simple without changing controller logic too much, we will rely on name for now or update controller --}}
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $item->job_title }}</div>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        <div class="font-bold">{{ $item->invoice_date }}</div>
                                        <div class="text-xs text-gray-500">Term: {{ $item->billable_period }}</div>
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        @if($item->payment_status === 'paid')
                                            <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs font-bold">
                                                PAID
                                            </span>
                                            <div class="text-[10px] text-gray-500 mt-1">{{ $item->paid_at }}</div>
                                        @elseif($item->is_due)
                                            <span class="bg-red-200 text-red-800 py-1 px-3 rounded-full text-xs font-bold">
                                                DUE NOW
                                            </span>
                                        @else
                                            <span class="bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs">
                                                Maturing
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-6 text-center">
                                        @if($item->payment_status !== 'paid' && $item->is_due)
                                            <form action="{{ route('admin.applications.markPaid', $item->id) }}" method="POST" onsubmit="return confirm('Confirm payment received for {{ $item->candidate_name }}?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded text-xs font-bold shadow">
                                                    Mark Paid
                                                </button>
                                            </form>
                                        @elseif($item->payment_status === 'paid')
                                            <span class="text-green-600"><i class="fa-solid fa-check"></i></span>
                                        @else
                                            <span class="text-gray-400 text-xs italic">Not eligible yet</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-6 px-6 text-center text-gray-500">
                                        No billable placements found.
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