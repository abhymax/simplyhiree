@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="mb-8 border-b border-white/10 pb-6">
            <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-lg">Billing</h1>
            <p class="text-blue-200 mt-1">Track invoice maturity, raised invoices, and payment status for every successful hire.</p>
        </div>

        @php
            $colors = [
                'Maturing'     => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                'Due to Raise' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                'Raised'       => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                'Overdue'      => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                'Paid'         => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
            ];
        @endphp

        {{-- Status filter chips --}}
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('client.billing') }}" class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusFilter ? 'bg-white/10 text-blue-100 border-white/20' : 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40' }}">All</a>
            @foreach(['Maturing','Due to Raise','Raised','Overdue','Paid'] as $b)
                <a href="{{ route('client.billing', ['status' => $b]) }}"
                   class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusFilter === $b ? ($colors[$b] ?? 'bg-white/10 text-blue-100 border-white/20') : 'bg-white/5 text-slate-300 border-white/10 hover:bg-white/10' }}">
                    {{ $b }} <span class="ml-1 opacity-70">{{ $counts[$b] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-amber-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Candidate</th>
                            <th class="px-5 py-4">Job</th>
                            <th class="px-5 py-4">Joined</th>
                            <th class="px-5 py-4 text-right">Final CTC</th>
                            <th class="px-5 py-4 text-right">Invoice</th>
                            <th class="px-5 py-4">Invoice Due</th>
                            <th class="px-5 py-4">Payment Due</th>
                            <th class="px-5 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($billingData as $row)
                            <tr class="hover:bg-white/5">
                                <td class="px-5 py-4 font-bold text-white">{{ $row['candidate_name'] }}</td>
                                <td class="px-5 py-4 text-blue-100">{{ $row['job_title'] }}</td>
                                <td class="px-5 py-4 text-blue-100">{{ $row['joining_date']?->format('d M, Y') }}</td>
                                <td class="px-5 py-4 text-right text-blue-100">{{ $row['final_ctc'] ? '₹'.number_format($row['final_ctc']) : '—' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <span class="text-emerald-300 font-extrabold">{{ $row['invoice_amount'] ? '₹'.number_format($row['invoice_amount'], 2) : '—' }}</span>
                                    @if($row['gst_applicable'])<div class="text-[10px] text-amber-200 font-bold uppercase">+ GST</div>@endif
                                </td>
                                <td class="px-5 py-4 text-blue-100">{{ $row['invoice_due_at']?->format('d M, Y') ?? '—' }}</td>
                                <td class="px-5 py-4 text-blue-100">{{ $row['payment_due_at']?->format('d M, Y') ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $colors[$row['status']] ?? 'bg-white/10 text-white border-white/20' }}">
                                        {{ $row['status'] }}
                                    </span>
                                    @if($row['status'] === 'Paid' && $row['paid_at'])
                                        <div class="text-[10px] text-emerald-200 mt-1">on {{ $row['paid_at']->format('d M, Y') }}</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-6 py-16 text-center text-blue-200">No billable records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">{{ $billingData->onEachSide(1)->links() }}</div>
        </div>
    </div>
</div>
@endsection
