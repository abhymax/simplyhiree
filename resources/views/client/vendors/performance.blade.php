@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-7xl mx-auto">

        <div class="mb-6 border-b border-white/10 pb-6 flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight">Vendor Performance</h1>
                <p class="text-blue-200 mt-1">Per-vendor metrics across your jobs. Used to rank Top / Average / Low performers.</p>
            </div>
            <a href="{{ route('client.vendors.browse') }}" class="bg-cyan-500 hover:bg-cyan-400 text-white text-sm font-bold px-4 py-2 rounded-lg">Browse Vendors</a>
        </div>

        @if(!empty($suggestions))
            <div class="bg-amber-500/10 border border-amber-400/40 rounded-3xl p-5 mb-6">
                <div class="text-amber-100 font-extrabold mb-2"><i class="fa-solid fa-wand-magic-sparkles mr-1"></i> Suggestions</div>
                <ul class="space-y-1 text-sm">
                    @foreach($suggestions as $s)
                        <li class="{{ $s['type']==='warn' ? 'text-rose-200' : 'text-emerald-200' }}">
                            <i class="fa-solid {{ $s['type']==='warn' ? 'fa-triangle-exclamation' : 'fa-thumbs-up' }} mr-1"></i> {{ $s['text'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $rankColors = [
                'Top'     => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                'Average' => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                'Low'     => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
            ];
        @endphp

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-amber-200 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Vendor</th>
                            <th class="px-5 py-3 text-center">Rank</th>
                            <th class="px-5 py-3 text-center">Submissions</th>
                            <th class="px-5 py-3 text-center">Selected</th>
                            <th class="px-5 py-3 text-center">Sel %</th>
                            <th class="px-5 py-3 text-center">Joined</th>
                            <th class="px-5 py-3 text-center">Drop %</th>
                            <th class="px-5 py-3 text-center">Rating</th>
                            <th class="px-5 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                    @forelse($rows as $r)
                        <tr class="hover:bg-white/5">
                            <td class="px-5 py-3">
                                <div class="text-white font-bold">{{ $r->partner_name }}</div>
                                <div class="text-blue-200 text-xs">{{ $r->vendor_level }} Tier @if($r->vendor_badge) · {{ $r->vendor_badge }}@endif</div>
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $rankColors[$r->rank] ?? '' }}">
                                    @if($r->rank==='Top')🥇 @elseif($r->rank==='Average')🥈 @else🥉 @endif {{ $r->rank }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-center text-blue-100">{{ $r->submitted }}</td>
                            <td class="px-5 py-3 text-center text-blue-100">{{ $r->selected }}</td>
                            <td class="px-5 py-3 text-center text-white font-bold">{{ $r->sel_ratio }}%</td>
                            <td class="px-5 py-3 text-center text-emerald-300 font-bold">{{ $r->joined_count }}</td>
                            <td class="px-5 py-3 text-center {{ $r->drop_rate >= 40 ? 'text-rose-300 font-bold' : 'text-blue-100' }}">{{ $r->drop_rate }}%</td>
                            <td class="px-5 py-3 text-center text-amber-200 font-bold">⭐ {{ $r->avg_rating ?? '—' }}</td>
                            <td class="px-5 py-3 text-right text-emerald-300 font-extrabold">{{ $r->revenue ? '₹'.number_format($r->revenue) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-12 text-center text-blue-200">No vendor activity yet. Post a job and partners will start submitting candidates.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
