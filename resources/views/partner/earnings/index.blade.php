@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-900 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-12 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-emerald-500 rounded-full mix-blend-overlay filter blur-[100px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end mb-8 border-b border-white/10 pb-6">
            <div>
                <span class="px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-blue-200 text-xs font-bold uppercase tracking-wider">
                    Partner Workspace
                </span>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight mt-3">My Earnings</h1>
                <p class="text-blue-200 mt-2 text-sm md:text-base">
                    Payout becomes eligible after candidate minimum stay completion.
                </p>
            </div>
        </div>

        @php
            $eligibleCount = collect($earnings)->where('status', 'Eligible')->count();
            $pendingCount = collect($earnings)->where('status', 'Pending')->count();
            $totalAmount = collect($earnings)->reduce(function ($sum, $item) {
                return $sum + (int) preg_replace('/[^\d]/', '', $item->payout_amount);
            }, 0);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5">
                <p class="text-xs text-blue-200 uppercase font-bold tracking-wider">Total Placements</p>
                <p class="text-3xl font-extrabold text-white mt-1">{{ collect($earnings)->count() }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5">
                <p class="text-xs text-emerald-200 uppercase font-bold tracking-wider">Eligible Payouts</p>
                <p class="text-3xl font-extrabold text-emerald-300 mt-1">{{ $eligibleCount }}</p>
            </div>
            <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-2xl p-5">
                <p class="text-xs text-indigo-200 uppercase font-bold tracking-wider">Total Payout Value</p>
                <p class="text-3xl font-extrabold text-indigo-200 mt-1">₹{{ number_format($totalAmount) }}</p>
            </div>
        </div>

        <div class="bg-white/10 backdrop-blur-md border border-white/10 rounded-3xl overflow-hidden shadow-2xl">
            <div class="p-5 border-b border-white/10 bg-slate-900/40">
                <h3 class="text-xl font-bold text-white">Payout Status</h3>
                <p class="text-sm text-slate-300 mt-1">
                    Shows joined candidates and payout readiness by minimum stay period.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-slate-900/50 border-b border-white/10">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-bold text-blue-100 uppercase tracking-wider">Candidate</th>
                            <th class="py-3 px-4 text-left text-xs font-bold text-blue-100 uppercase tracking-wider">Job Title</th>
                            <th class="py-3 px-4 text-left text-xs font-bold text-blue-100 uppercase tracking-wider">Joining Date</th>
                            <th class="py-3 px-4 text-left text-xs font-bold text-blue-100 uppercase tracking-wider">Payout Amount</th>
                            <th class="py-3 px-4 text-left text-xs font-bold text-blue-100 uppercase tracking-wider">Minimum Stay</th>
                            <th class="py-3 px-4 text-left text-xs font-bold text-blue-100 uppercase tracking-wider">Payout Date</th>
                            <th class="py-3 px-4 text-left text-xs font-bold text-blue-100 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                        @forelse($earnings as $item)
                            <tr class="hover:bg-white/5 transition">
                                <td class="py-4 px-4 whitespace-nowrap font-semibold text-white">{{ $item->candidate_name }}</td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-slate-200">{{ $item->job_title }}</td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-slate-200">{{ $item->joining_date }}</td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-emerald-300 font-bold">{{ $item->payout_amount }}</td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-slate-200">{{ $item->minimum_stay_days }} days</td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm font-semibold text-white">{{ $item->payout_date }}</td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @if($item->status == 'Eligible')
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-emerald-500/20 text-emerald-100 border border-emerald-400/30">
                                            <i class="fa-solid fa-check-circle mr-1 mt-0.5"></i> Eligible
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-bold rounded-full bg-amber-500/20 text-amber-100 border border-amber-400/30">
                                            <i class="fa-solid fa-clock mr-1 mt-0.5"></i> Pending
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center text-slate-300">
                                    <i class="fa-solid fa-wallet text-4xl text-slate-400 mb-3"></i>
                                    <p>You have no candidates who have joined a company yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(collect($earnings)->count() > 0)
                <div class="p-4 border-t border-white/10 bg-slate-900/30 text-sm text-slate-300">
                    Pending: {{ $pendingCount }} | Eligible: {{ $eligibleCount }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection