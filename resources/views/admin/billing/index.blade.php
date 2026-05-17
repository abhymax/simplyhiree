<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 left-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-6 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Billing Report</h1>
                    <p class="text-blue-200 mt-1 text-base">Per-job commercial breakdown, invoice timeline and payment status across every client.</p>
                </div>
                @if(session('success'))
                    <div class="px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">
                        <i class="fa-solid fa-circle-check mr-2"></i> {{ session('success') }}
                    </div>
                @endif
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

            {{-- FILTERS --}}
            <form method="GET" action="{{ route('admin.billing.index') }}" class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-3 mb-4 flex flex-wrap gap-2 items-center">
                <div class="relative grow min-w-[200px]">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-white/70 text-sm pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search candidate, job…" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm font-medium px-3 pl-9 w-full">
                </div>
                <select name="client_id" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm font-medium min-w-[180px]">
                    <option value="" class="text-gray-400">All Clients</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}" {{ (int) request('client_id') === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-10 px-4 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-sm shadow-md flex items-center gap-2">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                @if(request()->anyFilled(['search','client_id','status']))
                    <a href="{{ route('admin.billing.index') }}" class="h-10 w-10 bg-rose-500 hover:bg-rose-400 text-white rounded-lg flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </form>

            {{-- STATUS CHIPS --}}
            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('admin.billing.index', request()->except('status')) }}" class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusFilter ? 'bg-white/10 text-blue-100 border-white/20' : 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40' }}">All</a>
                @foreach(['Maturing','Due to Raise','Raised','Overdue','Paid'] as $b)
                    <a href="{{ route('admin.billing.index', array_merge(request()->except('page'), ['status' => $b])) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-bold border {{ $statusFilter === $b ? ($colors[$b] ?? '') : 'bg-white/5 text-slate-300 border-white/10 hover:bg-white/10' }}">
                        {{ $b }} <span class="ml-1 opacity-70">{{ $counts[$b] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-amber-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-5 py-4">Candidate / Job</th>
                                <th class="px-5 py-4">Client</th>
                                <th class="px-5 py-4">Joined</th>
                                <th class="px-5 py-4 text-right">Final CTC</th>
                                <th class="px-5 py-4">Breakdown</th>
                                <th class="px-5 py-4 text-right">Invoice</th>
                                <th class="px-5 py-4">Invoice / Payment Due</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($placements as $row)
                                @php $app = $row['application']; @endphp
                                <tr class="hover:bg-white/5 align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-white">{{ $row['candidate_name'] }}</div>
                                        <div class="text-xs text-blue-200 mt-0.5">{{ $row['job_title'] }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-blue-100">{{ $row['client_name'] }}</td>
                                    <td class="px-5 py-4 text-blue-100">{{ $row['joining_date']?->format('d M, Y') }}</td>
                                    <td class="px-5 py-4 text-right text-blue-100">{{ $row['final_ctc'] ? '₹'.number_format($row['final_ctc']) : '—' }}</td>
                                    <td class="px-5 py-4 text-xs text-blue-100">
                                        @if($row['matched_row'])
                                            <div class="font-semibold text-white">
                                                @if($row['billing_type'] === 'percentage_based')
                                                    {{ $row['matched_row']['label'] ?? '' }}
                                                @elseif($row['billing_type'] === 'profile_wise')
                                                    {{ $row['matched_row']['profile'] ?? '' }}
                                                @else
                                                    {{ $row['matched_row']['category'] ?? '' }}
                                                @endif
                                            </div>
                                            <div>
                                                @if($row['fee_percent'] !== null) {{ $row['fee_percent'] }}% @endif
                                                @if($row['fee_amount_flat'] !== null) ₹{{ number_format($row['fee_amount_flat']) }} flat @endif
                                            </div>
                                            @if($row['replacement_days'] !== null)
                                                <div class="text-amber-200/70 text-[10px] uppercase tracking-wide">Replace {{ $row['replacement_days'] }}d</div>
                                            @endif
                                        @else
                                            <span class="text-amber-200 italic">No commercial yet</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <span class="text-emerald-300 font-extrabold">{{ $row['invoice_amount'] ? '₹'.number_format($row['invoice_amount'], 2) : '—' }}</span>
                                        @if($row['gst_applicable'])<div class="text-[10px] text-amber-200 font-bold uppercase">+ GST</div>@endif
                                    </td>
                                    <td class="px-5 py-4 text-xs text-blue-100">
                                        <div><span class="text-amber-200/70 uppercase font-bold tracking-wider text-[10px]">Inv:</span> {{ $row['invoice_due_at']?->format('d M, Y') ?? '—' }}</div>
                                        <div><span class="text-amber-200/70 uppercase font-bold tracking-wider text-[10px]">Pay:</span> {{ $row['payment_due_at']?->format('d M, Y') ?? '—' }}</div>
                                        @if($row['invoice_generated_at'])
                                            <div class="mt-1 text-emerald-200 text-[10px]">Raised {{ $row['invoice_generated_at']->format('d M') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $colors[$row['status']] ?? '' }}">
                                            {{ $row['status'] }}
                                        </span>
                                        @if($row['status'] === 'Paid' && $row['paid_at'])
                                            <div class="text-[10px] text-emerald-200 mt-1">{{ $row['paid_at']->format('d M, Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex flex-col items-end gap-1.5">
                                            <a href="{{ route('admin.applications.show', $app->id) }}" class="text-xs text-cyan-300 hover:text-white underline">View</a>
                                            @if(!$row['invoice_generated_at'] && in_array($row['status'], ['Due to Raise', 'Maturing']))
                                                <form method="POST" action="{{ route('admin.applications.markRaised', $app->id) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-[11px] bg-blue-500 hover:bg-blue-400 text-white font-bold px-2.5 py-1 rounded">Mark Raised</button>
                                                </form>
                                            @endif
                                            @if($row['payment_status'] !== 'paid' && $row['invoice_generated_at'])
                                                <form method="POST" action="{{ route('admin.applications.markPaid', $app->id) }}">
                                                    @csrf @method('PATCH')
                                                    <button type="submit" class="text-[11px] bg-emerald-500 hover:bg-emerald-400 text-white font-bold px-2.5 py-1 rounded">Mark Paid</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-6 py-16 text-center text-blue-200">No billable records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-white/10">{{ $placements->onEachSide(1)->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
