<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 left-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-rose-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <div class="mb-6 border-b border-white/10 pb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 text-sm font-bold uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-extrabold text-white">Partner Credit Notes</h1>
                <p class="text-blue-200 mt-1">Credits issued to partners for failed-replacement hires. Apply against future payouts or cancel.</p>
            </div>

            @if(session('success'))
                <div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-5 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl font-bold">{{ session('error') }}</div>
            @endif

            @php
                $colors = [
                    'pending'   => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                    'applied'   => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                    'cancelled' => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                ];
            @endphp

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.credit-notes.index') }}" class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-3 mb-4 flex flex-wrap items-center gap-2">
                <select name="partner_id" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm font-medium px-3 min-w-[180px]">
                    <option value="">All Partners</option>
                    @foreach($partners as $p)
                        <option value="{{ $p->id }}" {{ (int) request('partner_id') === $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-10 px-4 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-sm">Filter</button>
                @if(request()->anyFilled(['partner_id','status']))
                    <a href="{{ route('admin.credit-notes.index') }}" class="h-10 w-10 bg-rose-500 hover:bg-rose-400 text-white rounded-lg flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
                @endif
            </form>

            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('admin.credit-notes.index', request()->except('status')) }}" class="px-3 py-1.5 rounded-full text-xs font-bold border {{ request('status') ? 'bg-white/10 text-blue-100 border-white/20' : 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40' }}">All</a>
                @foreach(['pending','applied','cancelled'] as $st)
                    <a href="{{ route('admin.credit-notes.index', array_merge(request()->except('page'), ['status'=>$st])) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-bold border {{ request('status') === $st ? $colors[$st] : 'bg-white/5 text-slate-300 border-white/10 hover:bg-white/10' }}">
                        {{ ucfirst($st) }} <span class="ml-1 opacity-70">{{ $counts[$st] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-amber-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-5 py-4">ID</th>
                                <th class="px-5 py-4">Partner</th>
                                <th class="px-5 py-4">Source Hire</th>
                                <th class="px-5 py-4 text-right">Amount</th>
                                <th class="px-5 py-4">Reason</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4">Issued</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($notes as $note)
                                <tr class="hover:bg-white/5 align-top">
                                    <td class="px-5 py-4 text-cyan-300 font-mono text-xs">#CN-{{ str_pad($note->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-5 py-4 text-blue-100">
                                        {{ $note->partner?->name ?? '—' }}
                                        <div class="text-xs text-blue-300/70">{{ $note->partner?->email }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        @php $src = $note->sourceApplication; @endphp
                                        @if($src)
                                            <div class="font-bold">{{ $src->candidate_name }}</div>
                                            <div class="text-xs text-blue-200">{{ $src->job?->title }}</div>
                                            <a href="{{ route('admin.applications.show', $src->id) }}" class="text-[11px] text-cyan-300 hover:text-white underline">View →</a>
                                        @else
                                            <span class="text-rose-200">deleted</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right text-emerald-300 font-extrabold">₹{{ number_format($note->amount, 2) }}</td>
                                    <td class="px-5 py-4 text-xs text-blue-100 max-w-[260px]">{{ $note->reason }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $colors[$note->status] ?? '' }}">
                                            {{ ucfirst($note->status) }}
                                        </span>
                                        @if($note->status === 'applied' && $note->applied_at)
                                            <div class="text-[10px] text-emerald-200 mt-1">on {{ $note->applied_at->format('d M, Y') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-xs text-blue-100">{{ $note->created_at->format('d M, Y') }}</td>
                                    <td class="px-5 py-4 text-right">
                                        @if($note->status === 'pending')
                                            <div class="flex flex-col items-end gap-1.5">
                                                <form method="POST" action="{{ route('admin.credit-notes.apply', $note->id) }}" onsubmit="return confirm('Mark this credit as applied to partner payout?');">
                                                    @csrf
                                                    <button type="submit" class="bg-emerald-500 hover:bg-emerald-400 text-white text-[11px] font-bold px-2.5 py-1 rounded">Mark Applied</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.credit-notes.cancel', $note->id) }}" onsubmit="return confirm('Cancel this credit note?');">
                                                    @csrf
                                                    <button type="submit" class="text-[11px] text-slate-300 hover:text-white underline">Cancel</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-6 py-16 text-center text-blue-200">No credit notes issued.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-white/10">{{ $notes->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
