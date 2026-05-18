<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-amber-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <div class="mb-6 border-b border-white/10 pb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 text-sm font-bold uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-extrabold text-white">Vendor Ratings &amp; Tiers</h1>
                <p class="text-blue-200 mt-1">Leaderboard of partners by avg rating. Apply a penalty to force a Restricted tier.</p>
            </div>

            @if(session('success'))<div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">{{ session('success') }}</div>@endif

            @php
                $levelColors = [
                    'Elite'      => 'bg-purple-500/20 text-purple-200 border-purple-400/40',
                    'Pro'        => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                    'Basic'      => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                    'Restricted' => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                ];
            @endphp

            <form method="GET" action="{{ route('admin.vendor-ratings.index') }}" class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-2xl p-3 mb-4 flex flex-wrap items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search partner…" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm font-medium px-3 grow min-w-[200px]">
                <select name="level" class="h-10 bg-slate-800 border border-blue-500/30 rounded-lg text-white text-sm font-medium px-3">
                    <option value="">All Tiers</option>
                    @foreach(['Elite','Pro','Basic','Restricted'] as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-10 px-4 bg-cyan-600 hover:bg-cyan-500 text-white rounded-lg font-bold text-sm">Filter</button>
            </form>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl mb-6">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-amber-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-5 py-4">Partner</th>
                                <th class="px-5 py-4 text-center">Avg ⭐</th>
                                <th class="px-5 py-4 text-center">Ratings</th>
                                <th class="px-5 py-4 text-center">Selection</th>
                                <th class="px-5 py-4 text-center">Closure</th>
                                <th class="px-5 py-4 text-center">Repeats</th>
                                <th class="px-5 py-4">Tier</th>
                                <th class="px-5 py-4">Badge</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($partners as $p)
                                <tr class="hover:bg-white/5 align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-bold">{{ $p->name }}</div>
                                        <div class="text-xs text-blue-200">{{ $p->email }}</div>
                                        @if($p->penalty_active)
                                            <div class="text-[10px] mt-1 text-rose-200 italic">Penalty: {{ $p->penalty_reason }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center text-amber-200 font-extrabold text-lg">{{ $p->avg_rating ?? '—' }}</td>
                                    <td class="px-5 py-4 text-center text-blue-100">{{ $p->total_ratings }}</td>
                                    <td class="px-5 py-4 text-center text-blue-100">{{ $p->selection_ratio !== null ? round($p->selection_ratio * 100, 1).'%' : '—' }}</td>
                                    <td class="px-5 py-4 text-center text-blue-100">{{ $p->closure_rate !== null ? round($p->closure_rate * 100, 1).'%' : '—' }}</td>
                                    <td class="px-5 py-4 text-center text-blue-100">{{ $p->repeat_hire_count }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $levelColors[$p->vendor_level] ?? '' }}">{{ $p->vendor_level }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        @if($p->vendor_badge)<span class="text-xs text-amber-200 font-bold">🏆 {{ $p->vendor_badge }}</span>@else <span class="text-slate-500">—</span> @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if($p->penalty_active)
                                            <form method="POST" action="{{ route('admin.vendor-ratings.lift', $p->id) }}" onsubmit="return confirm('Lift penalty for {{ $p->name }}?');">
                                                @csrf
                                                <button type="submit" class="text-[11px] bg-emerald-500 hover:bg-emerald-400 text-white font-bold px-2.5 py-1 rounded">Lift Penalty</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.vendor-ratings.penalty', $p->id) }}" onsubmit="return confirm('Apply penalty — partner gets Restricted tier?');" class="flex flex-col items-end gap-1">
                                                @csrf
                                                <input type="text" name="penalty_reason" placeholder="Reason (optional)" class="bg-slate-800 border border-white/10 rounded text-white text-xs px-2 py-1 max-w-[180px]">
                                                <button type="submit" class="text-[11px] bg-rose-500 hover:bg-rose-400 text-white font-bold px-2.5 py-1 rounded">Apply Penalty</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-6 py-12 text-center text-blue-200">No partners found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-white/10">{{ $partners->onEachSide(1)->links() }}</div>
            </div>

            {{-- Recent ratings strip --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-xl">
                <div class="px-6 py-4 border-b border-white/10 text-white font-extrabold">Recent Client Feedback</div>
                <div class="divide-y divide-white/10">
                    @forelse($recentRatings as $r)
                        <div class="px-6 py-3 flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <div class="text-amber-200 font-bold">⭐ {{ $r->score }} / 5 <span class="text-white text-sm font-normal">on</span> {{ $r->partner?->name }}</div>
                                <div class="text-blue-200 text-xs">{{ $r->ratedBy?->name }} · {{ $r->job?->title ?? '—' }} · {{ $r->created_at->diffForHumans() }}</div>
                                @if($r->feedback)<div class="mt-1 text-blue-100 text-sm italic">"{{ \Illuminate\Support\Str::limit($r->feedback, 200) }}"</div>@endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-6 text-center text-blue-200">No ratings yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
