<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
        <div class="max-w-6xl mx-auto">
            <div class="mb-8 border-b border-white/10 pb-6">
                <h1 class="text-4xl font-extrabold text-white tracking-tight">Vendor Payments</h1>
                <p class="text-blue-200 mt-1 text-lg">Plan purchases via Razorpay — revenue &amp; reconciliation.</p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-emerald-300">Total revenue (paid)</div>
                    <div class="mt-1 text-2xl font-extrabold text-white">₹{{ number_format($stats['revenue'], 2) }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-sky-300">This month</div>
                    <div class="mt-1 text-2xl font-extrabold text-white">₹{{ number_format($stats['this_month'], 2) }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-blue-200">Paid transactions</div>
                    <div class="mt-1 text-2xl font-extrabold text-white">{{ $stats['paid'] }}</div>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <div class="text-xs font-bold uppercase tracking-wide text-amber-300">Started (unpaid)</div>
                    <div class="mt-1 text-2xl font-extrabold text-white">{{ $stats['created'] }}</div>
                </div>
            </div>

            <form method="GET" class="mb-5 flex flex-wrap gap-3">
                <select name="status" class="rounded-xl border border-white/20 bg-slate-900/40 text-white px-4 py-2.5">
                    <option value="">All statuses</option>
                    @foreach(['paid' => 'Paid', 'created' => 'Started (unpaid)', 'failed' => 'Failed'] as $k => $v)
                        <option value="{{ $k }}" @selected(request('status')===$k)>{{ $v }}</option>
                    @endforeach
                </select>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Vendor, plan, order/payment id…"
                       class="flex-1 min-w-[200px] rounded-xl border border-white/20 bg-slate-900/40 text-white placeholder-slate-400 px-4 py-2.5">
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-xl border border-white/20 bg-slate-900/40 text-white px-3 py-2.5">
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-xl border border-white/20 bg-slate-900/40 text-white px-3 py-2.5">
                <button class="rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold px-5">Filter</button>
            </form>

            <div class="rounded-2xl border border-white/10 bg-slate-900/40 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white/5 text-blue-200/80 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="text-left font-bold px-4 py-3">Date</th>
                                <th class="text-left font-bold px-4 py-3">Vendor</th>
                                <th class="text-left font-bold px-4 py-3">Plan</th>
                                <th class="text-right font-bold px-4 py-3">Base</th>
                                <th class="text-right font-bold px-4 py-3">GST</th>
                                <th class="text-right font-bold px-4 py-3">Total</th>
                                <th class="text-left font-bold px-4 py-3">Status</th>
                                <th class="text-left font-bold px-4 py-3">Payment ref</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5 text-white">
                            @forelse($payments as $p)
                                @php
                                    $sm = ['paid'=>'bg-emerald-500/15 text-emerald-200 border-emerald-400/30','created'=>'bg-amber-500/15 text-amber-200 border-amber-400/30','failed'=>'bg-rose-500/15 text-rose-200 border-rose-400/30'][$p->status] ?? 'bg-white/10 text-slate-200 border-white/20';
                                @endphp
                                <tr class="hover:bg-white/5">
                                    <td class="px-4 py-3 text-blue-200/80 text-xs">{{ optional($p->paid_at ?? $p->created_at)->format('d M Y, h:i A') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="font-bold">{{ optional($p->partner)->name ?? '—' }}</div>
                                        <div class="text-blue-200/60 text-xs">{{ optional($p->partner)->email }}</div>
                                    </td>
                                    <td class="px-4 py-3">{{ $p->plan_name }} <span class="text-blue-200/50 text-xs">/ {{ $p->duration_days }}d</span></td>
                                    <td class="px-4 py-3 text-right">₹{{ number_format($p->base_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-right">₹{{ number_format($p->gst_amount, 2) }}</td>
                                    <td class="px-4 py-3 text-right font-bold">₹{{ number_format($p->total_amount, 2) }}</td>
                                    <td class="px-4 py-3"><span class="inline-block rounded-full border px-2.5 py-1 text-xs font-bold {{ $sm }}">{{ ucfirst($p->status) }}</span></td>
                                    <td class="px-4 py-3 text-blue-200/60 text-[11px]">{{ $p->razorpay_payment_id ?: $p->razorpay_order_id ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-12 text-center text-blue-200/60">No vendor payments yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-5">{{ $payments->links() }}</div>
        </div>
    </div>
</x-app-layout>
