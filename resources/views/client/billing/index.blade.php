@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="mb-6 border-b border-white/10 pb-5">
            <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                Client Workspace
            </span>
            <h1 class="text-4xl md:text-5xl font-extrabold mt-3">My Invoices & Payments</h1>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            @if($billingData->isEmpty())
                <div class="text-center py-14">
                    <i class="fa-solid fa-file-invoice-dollar text-4xl text-slate-400 mb-3"></i>
                    <p class="text-slate-300">No billable placements found yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-slate-900/50 border-b border-white/10">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-blue-100 uppercase">Candidate / Job</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-blue-100 uppercase">Dates</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-blue-100 uppercase">Amount</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-blue-100 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            @foreach($billingData as $item)
                                <tr class="hover:bg-white/5">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-semibold text-white">{{ $item->candidate_name }}</div>
                                        <div class="text-xs text-slate-300">{{ $item->job_title }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-slate-100">Joined: {{ $item->joining_date }}</div>
                                        <div class="text-xs text-slate-300 font-semibold">Invoice Due: {{ $item->invoice_date }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-emerald-300">{{ $item->amount }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $item->status_color }}">
                                            {{ $item->status }}
                                        </span>
                                        @if($item->paid_at)
                                            <div class="text-[10px] text-slate-300 mt-1">Paid on {{ $item->paid_at }}</div>
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
@endsection