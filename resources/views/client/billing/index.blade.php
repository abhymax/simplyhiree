@extends('layouts.client')

@section('client_content')
    <div class="relative z-10 max-w-7xl mx-auto" x-data="{ payRow: null, viewRow: null }">

        <div class="mb-6 border-b border-white/10 pb-6">
            <h1 class="text-4xl font-extrabold tracking-tight drop-shadow-lg">Billing</h1>
            <p class="text-blue-200 mt-1">Track invoice maturity, raised invoices, and payment status for every successful hire.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 bg-emerald-500/20 border border-emerald-400/40 text-emerald-100 rounded-xl">
                <i class="fa-solid fa-check mr-2"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 px-4 py-3 bg-rose-500/20 border border-rose-400/40 text-rose-100 rounded-xl">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Summary cards: flat, compact, no gloss --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
            <div class="bg-slate-900/40 border border-rose-400/30 rounded-lg px-4 py-3">
                <div class="flex items-center gap-1.5 text-rose-300 text-[11px] font-bold uppercase tracking-wider"><i class="fa-solid fa-circle-exclamation text-[10px]"></i> Outstanding</div>
                <div class="text-2xl font-bold text-white mt-1">₹{{ number_format($summary['outstanding'], 0) }}</div>
                <div class="text-slate-400 text-[11px]">{{ ($counts['Raised'] ?? 0) + ($counts['Overdue'] ?? 0) + ($counts['Due to Raise'] ?? 0) }} invoice(s)</div>
            </div>
            <div class="bg-slate-900/40 border border-amber-400/30 rounded-lg px-4 py-3">
                <div class="flex items-center gap-1.5 text-amber-300 text-[11px] font-bold uppercase tracking-wider"><i class="fa-solid fa-fire text-[10px]"></i> Overdue</div>
                <div class="text-2xl font-bold text-white mt-1">{{ $summary['overdue_count'] }}</div>
                <div class="text-slate-400 text-[11px]">Needs action</div>
            </div>
            <div class="bg-slate-900/40 border border-emerald-400/30 rounded-lg px-4 py-3">
                <div class="flex items-center gap-1.5 text-emerald-300 text-[11px] font-bold uppercase tracking-wider"><i class="fa-solid fa-check text-[10px]"></i> Total Paid</div>
                <div class="text-2xl font-bold text-white mt-1">₹{{ number_format($summary['paid_total'], 0) }}</div>
                <div class="text-slate-400 text-[11px]">{{ $counts['Paid'] ?? 0 }} hire(s)</div>
            </div>
            <div class="bg-slate-900/40 border border-slate-400/30 rounded-lg px-4 py-3">
                <div class="flex items-center gap-1.5 text-slate-300 text-[11px] font-bold uppercase tracking-wider"><i class="fa-solid fa-hourglass-half text-[10px]"></i> Maturing</div>
                <div class="text-2xl font-bold text-white mt-1">₹{{ number_format($summary['maturing_total'], 0) }}</div>
                <div class="text-slate-400 text-[11px]">{{ $counts['Maturing'] ?? 0 }} hire(s)</div>
            </div>
        </div>

        @php
            $colors = [
                'Maturing'     => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                'Due to Raise' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                'Raised'       => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                'Overdue'      => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                'Paid'         => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
            ];
            $fld = 'h-9 bg-slate-900/60 border border-white/15 rounded-md text-white text-sm px-2.5 focus:ring-1 focus:ring-cyan-400 focus:border-cyan-400';
        @endphp

        {{-- Compact single-row filter bar --}}
        <form method="GET" action="{{ route('client.billing') }}" class="flex flex-wrap items-center gap-2 mb-3">
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <div class="relative flex-1 min-w-[180px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-white/60 text-sm pointer-events-none z-10"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search candidate or job"
                       class="h-9 w-full bg-slate-900/60 border border-white/15 rounded-md text-white text-sm pl-11 pr-3 focus:ring-1 focus:ring-cyan-400 focus:border-cyan-400">
            </div>
            <select name="job_id" class="{{ $fld }} max-w-[180px]">
                <option value="" class="bg-slate-900">All Jobs</option>
                @foreach($clientJobs as $j)
                    <option value="{{ $j->id }}" class="bg-slate-900" {{ (string) request('job_id') === (string) $j->id ? 'selected' : '' }}>
                        {{ \Illuminate\Support\Str::limit($j->title, 22) }}
                    </option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" title="Joined from" class="{{ $fld }} w-[150px] [color-scheme:dark]">
            <span class="text-slate-400 text-xs">to</span>
            <input type="date" name="date_to" value="{{ request('date_to') }}" title="Joined to" class="{{ $fld }} w-[150px] [color-scheme:dark]">
            <button type="submit" class="h-9 px-4 bg-cyan-600 hover:bg-cyan-500 text-white font-bold rounded-md text-sm">
                <i class="fa-solid fa-filter mr-1"></i> Filter
            </button>
            @if(request()->anyFilled(['search', 'job_id', 'date_from', 'date_to', 'status']))
                <a href="{{ route('client.billing') }}" title="Clear filters"
                   class="h-9 w-9 bg-rose-500 hover:bg-rose-400 text-white rounded-md inline-flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
            @endif
        </form>

        {{-- Status filter chips --}}
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="{{ route('client.billing', request()->except(['status', 'page'])) }}" class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusFilter ? 'bg-white/10 text-blue-100 border-white/20' : 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40' }}">All</a>
            @foreach(['Maturing','Due to Raise','Raised','Overdue','Paid'] as $b)
                <a href="{{ route('client.billing', array_merge(request()->except('page'), ['status' => $b])) }}"
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
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($billingData as $row)
                            @php $app = $row['application']; @endphp
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
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button" @click="viewRow = viewRow === {{ $app->id }} ? null : {{ $app->id }}"
                                                class="px-2.5 py-1.5 bg-white/10 hover:bg-white/20 text-white text-xs font-bold rounded-lg border border-white/20 transition" title="View">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        @if(in_array($row['status'], ['Raised', 'Overdue', 'Due to Raise']))
                                            <button type="button" @click="payRow = payRow === {{ $app->id }} ? null : {{ $app->id }}"
                                                    class="px-2.5 py-1.5 bg-emerald-500 hover:bg-emerald-400 text-slate-900 text-xs font-bold rounded-lg transition" title="Mark Paid">
                                                <i class="fa-solid fa-check"></i> Mark Paid
                                            </button>
                                        @elseif($row['status'] === 'Paid')
                                            <form action="{{ route('client.billing.unmarkPaid', $app) }}" method="POST" onsubmit="return confirm('Revert this payment status?')">
                                                @csrf
                                                <button type="submit" class="px-2.5 py-1.5 bg-amber-500/30 hover:bg-amber-500/50 text-amber-100 text-xs font-bold rounded-lg border border-amber-400/40 transition" title="Revert payment">
                                                    <i class="fa-solid fa-rotate-left"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Inline View drawer --}}
                            <tr x-show="viewRow === {{ $app->id }}" x-cloak>
                                <td colspan="9" class="px-5 py-4 bg-slate-900/80 border-t border-cyan-400/30">
                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <div class="text-cyan-300 text-[10px] font-bold uppercase">Fee Type</div>
                                            <div class="text-white font-bold">{{ $row['billing_type'] ?? '—' }}</div>
                                        </div>
                                        <div>
                                            <div class="text-cyan-300 text-[10px] font-bold uppercase">Fee Rate</div>
                                            <div class="text-white font-bold">
                                                @if($row['fee_percent']) {{ $row['fee_percent'] }}%
                                                @elseif($row['fee_amount_flat']) ₹{{ number_format($row['fee_amount_flat']) }} flat
                                                @else — @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-cyan-300 text-[10px] font-bold uppercase">Replacement Window</div>
                                            <div class="text-white font-bold">{{ $row['replacement_days'] ?? '—' }} days</div>
                                        </div>
                                        <div>
                                            <div class="text-cyan-300 text-[10px] font-bold uppercase">Invoice Raised On</div>
                                            <div class="text-white font-bold">{{ $row['invoice_generated_at']?->format('d M, Y') ?? '—' }}</div>
                                        </div>
                                    </div>
                                    @if($app->client_notes)
                                        <div class="mt-3 text-xs bg-white/5 border border-white/10 rounded p-2 text-blue-100 italic">{{ $app->client_notes }}</div>
                                    @endif
                                </td>
                            </tr>

                            {{-- Inline Mark-Paid form --}}
                            <tr x-show="payRow === {{ $app->id }}" x-cloak>
                                <td colspan="9" class="px-5 py-4 bg-slate-900/90 border-t border-emerald-400/30">
                                    <form action="{{ route('client.billing.markPaid', $app) }}" method="POST" class="flex flex-col md:flex-row md:items-end gap-3">
                                        @csrf
                                        <div>
                                            <label class="block text-emerald-200 text-[10px] font-bold uppercase tracking-wider mb-1">Paid On *</label>
                                            <input type="date" name="paid_at" required max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}"
                                                   class="bg-slate-800 border border-white/20 rounded text-white text-sm px-3 py-2 [color-scheme:dark]">
                                        </div>
                                        <div class="flex-1">
                                            <label class="block text-emerald-200 text-[10px] font-bold uppercase tracking-wider mb-1">Reference / UTR (optional)</label>
                                            <input type="text" name="payment_reference" maxlength="255" placeholder="e.g. UTR1234567890 or Cheque #001234"
                                                   class="w-full bg-slate-800 border border-white/20 rounded text-white text-sm px-3 py-2">
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="button" @click="payRow = null" class="px-4 py-2 text-sm text-slate-300 hover:text-white">Cancel</button>
                                            <button type="submit" class="px-4 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-900 font-bold text-sm rounded-lg">
                                                <i class="fa-solid fa-check mr-1"></i> Confirm Payment
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-6 py-16 text-center text-blue-200">
                                <i class="fa-solid fa-receipt text-4xl text-blue-300 mb-3"></i>
                                <p class="font-bold">No billable records match your filters.</p>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">{{ $billingData->onEachSide(1)->links() }}</div>
    </div>
@endsection
