@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="mb-6 border-b border-white/10 pb-6">
            <h1 class="text-4xl font-extrabold tracking-tight">Wallet &amp; Credits</h1>
            <p class="text-blue-200 mt-1">Your pipeline health, guarantee window candidates and any credit adjustments against future payouts.</p>
        </div>

        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-emerald-200 font-bold mb-1">Active Candidates</div>
                <div class="text-3xl font-extrabold text-white">{{ $activeCount }}</div>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/10 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-blue-200 font-bold mb-1">Under Guarantee</div>
                <div class="text-3xl font-extrabold text-white">{{ $underGuaranteeCount }}</div>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-xl border border-amber-400/30 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-amber-200 font-bold mb-1">Replacement Required</div>
                <div class="text-3xl font-extrabold text-amber-200">{{ $replacementRequiredCount }}</div>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-xl border border-rose-400/30 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-rose-200 font-bold mb-1">Credits Pending</div>
                <div class="text-2xl font-extrabold text-rose-200">₹{{ number_format($totals['pending'], 0) }}</div>
            </div>
            <div class="bg-slate-900/60 backdrop-blur-xl border border-emerald-400/30 rounded-2xl p-5">
                <div class="text-xs uppercase tracking-wider text-emerald-200 font-bold mb-1">Credits Applied</div>
                <div class="text-2xl font-extrabold text-emerald-200">₹{{ number_format($totals['applied'], 0) }}</div>
            </div>
        </div>

        {{-- Replacement Required panel --}}
        @if($replacementsRequired->count())
            <div class="bg-amber-500/10 border border-amber-400/40 rounded-3xl mb-6 overflow-hidden">
                <div class="px-6 py-4 border-b border-amber-400/20 flex items-center justify-between">
                    <h2 class="text-amber-100 font-extrabold flex items-center gap-2">
                        <i class="fa-solid fa-rotate"></i> Replacement Required
                        <span class="bg-amber-500 text-slate-900 text-xs font-bold px-2 py-0.5 rounded-full">{{ $replacementRequiredCount }}</span>
                    </h2>
                    <p class="text-amber-100/80 text-xs">Send fresh candidates for these jobs before the deadline to avoid a credit adjustment.</p>
                </div>
                <div class="divide-y divide-amber-400/10">
                    @foreach($replacementsRequired as $rr)
                        <div class="px-6 py-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <div class="text-white font-bold">{{ trim(($rr->candidate->first_name ?? '').' '.($rr->candidate->last_name ?? '')) ?: 'Candidate' }}
                                    <span class="text-amber-200/80 text-sm font-medium">left</span>
                                    <span class="text-white">{{ $rr->job->title ?? '—' }}</span>
                                </div>
                                <div class="text-blue-200/80 text-xs mt-0.5">
                                    Requested {{ $rr->replacement_requested_at?->diffForHumans() }} ·
                                    @if($rr->replacement_deadline)
                                        Deadline <span class="{{ $rr->replacement_deadline->isPast() ? 'text-rose-200 font-bold' : 'text-amber-200' }}">{{ $rr->replacement_deadline->format('d M') }} ({{ $rr->replacement_deadline->diffForHumans() }})</span>
                                    @endif
                                </div>
                                @if($rr->replacement_reason)
                                    <div class="mt-1 text-amber-100/80 text-sm italic">"{{ \Illuminate\Support\Str::limit($rr->replacement_reason, 160) }}"</div>
                                @endif
                            </div>
                            <a href="{{ route('partner.jobs.show', $rr->job->id) }}" class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 text-slate-900 text-xs font-bold px-4 py-2 rounded-lg whitespace-nowrap">
                                <i class="fa-solid fa-paper-plane"></i> Send Candidates
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Credit ledger --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h2 class="text-white font-extrabold text-lg">Credit Adjustments Ledger</h2>
                <span class="text-blue-200 text-xs">Cancelled total: ₹{{ number_format($totals['cancelled'], 0) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-amber-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4">ID</th>
                            <th class="px-5 py-4">Source Hire</th>
                            <th class="px-5 py-4 text-right">Amount</th>
                            <th class="px-5 py-4">Reason</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Issued</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($credits as $note)
                            @php
                                $colors = [
                                    'pending'   => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                                    'applied'   => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                                    'cancelled' => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                                ];
                            @endphp
                            <tr class="hover:bg-white/5 align-top">
                                <td class="px-5 py-4 text-cyan-300 font-mono text-xs">#CN-{{ str_pad($note->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td class="px-5 py-4">
                                    @if($note->sourceApplication)
                                        <div class="font-bold text-white">{{ $note->sourceApplication->candidate_name }}</div>
                                        <div class="text-xs text-blue-200">{{ $note->sourceApplication->job?->title }}</div>
                                    @else
                                        <span class="text-rose-200">—</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right text-rose-200 font-extrabold">−₹{{ number_format($note->amount, 2) }}</td>
                                <td class="px-5 py-4 text-xs text-blue-100">{{ $note->reason }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $colors[$note->status] }}">
                                        {{ ucfirst($note->status) }}
                                    </span>
                                    @if($note->status === 'applied' && $note->applied_at)
                                        <div class="text-[10px] text-emerald-200 mt-1">on {{ $note->applied_at->format('d M') }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs text-blue-100">{{ $note->created_at->format('d M, Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-16 text-center text-blue-200">No credit notes — your replacement record is clean. 👏</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">{{ $credits->links() }}</div>
        </div>
    </div>
</div>
@endsection
