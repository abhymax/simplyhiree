<x-app-layout>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            <div class="mb-6 border-b border-white/10 pb-6">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 text-sm font-bold uppercase">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl font-extrabold text-white">Plan Upgrade Requests</h1>
                <p class="text-blue-200 mt-1">Partners who have requested a plan change. Reach out and approve or reject below.</p>
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
                    'contacted' => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                    'approved'  => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                    'rejected'  => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                    'cancelled' => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                ];
                $planChip = [
                    'Free'       => 'bg-slate-400/20 text-slate-200',
                    'Basic'      => 'bg-blue-500/20 text-blue-200',
                    'Pro'        => 'bg-purple-500/20 text-purple-200',
                    'Enterprise' => 'bg-rose-500/20 text-rose-200',
                ];
            @endphp

            <div class="flex flex-wrap gap-2 mb-4">
                <a href="{{ route('admin.plan-requests.index') }}" class="px-3 py-1.5 rounded-full text-xs font-bold border {{ request('status') ? 'bg-white/10 text-blue-100 border-white/20' : 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40' }}">All</a>
                @foreach(['pending','contacted','approved','rejected','cancelled'] as $st)
                    <a href="{{ route('admin.plan-requests.index', ['status'=>$st]) }}"
                       class="px-3 py-1.5 rounded-full text-xs font-bold border {{ request('status') === $st ? $colors[$st] : 'bg-white/5 text-slate-300 border-white/10 hover:bg-white/10' }}">
                        {{ ucfirst($st) }} <span class="ml-1 opacity-70">{{ $counts[$st] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-5 py-4">Partner</th>
                                <th class="px-5 py-4">Current → Requested</th>
                                <th class="px-5 py-4">Requested</th>
                                <th class="px-5 py-4">Notes</th>
                                <th class="px-5 py-4">Status</th>
                                <th class="px-5 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($requests as $r)
                                <tr class="hover:bg-white/5 align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-bold">{{ $r->partner?->name ?? '—' }}</div>
                                        <div class="text-xs text-blue-200">{{ $r->partner?->email }}</div>
                                        @php $phone = $r->partner?->profile?->phone_number; @endphp
                                        @if($phone)
                                            <a href="tel:{{ $phone }}" class="text-xs text-emerald-300 hover:text-white"><i class="fa-solid fa-phone mr-1"></i>{{ $phone }}</a>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $planChip[$r->current_plan] ?? 'bg-white/10 text-white' }}">{{ $r->current_plan }}</span>
                                        <i class="fa-solid fa-arrow-right mx-1 text-slate-400"></i>
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $planChip[$r->requested_plan] ?? 'bg-white/10 text-white' }}">{{ $r->requested_plan }}</span>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-blue-100">
                                        {{ $r->created_at->format('d M, Y') }}
                                        <div class="text-blue-300/70">{{ $r->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-xs text-blue-100 max-w-[260px]">
                                        @if($r->notes)
                                            <span class="italic">"{{ \Illuminate\Support\Str::limit($r->notes, 120) }}"</span>
                                        @else <span class="text-slate-400">—</span> @endif
                                        @if($r->admin_notes)
                                            <div class="mt-1 text-amber-200/80 text-[11px]"><i class="fa-solid fa-pen mr-1"></i>{{ \Illuminate\Support\Str::limit($r->admin_notes, 120) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $colors[$r->status] ?? '' }}">{{ ucfirst($r->status) }}</span>
                                        @if($r->actioned_at)
                                            <div class="text-[10px] text-slate-400 mt-1">{{ $r->actioned_at->format('d M, Y') }}<br>by {{ $r->actionedBy?->name ?? '—' }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if(in_array($r->status, ['pending','contacted']))
                                            <div class="flex flex-col items-end gap-1.5">
                                                @if($r->status === 'pending')
                                                    <form method="POST" action="{{ route('admin.plan-requests.contacted', $r->id) }}">
                                                        @csrf
                                                        <button type="submit" class="text-[11px] bg-blue-500 hover:bg-blue-400 text-white font-bold px-2.5 py-1 rounded">Mark Contacted</button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('admin.plan-requests.approve', $r->id) }}" onsubmit="return confirm('Approve plan change to {{ $r->requested_plan }}?');" class="flex flex-col items-end gap-1">
                                                    @csrf
                                                    <input type="text" name="admin_notes" placeholder="Internal note (optional)" class="bg-slate-800 border border-white/10 rounded text-white text-xs px-2 py-1 w-44">
                                                    <button type="submit" class="text-[11px] bg-emerald-500 hover:bg-emerald-400 text-white font-bold px-2.5 py-1 rounded">Approve &amp; Switch</button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.plan-requests.reject', $r->id) }}" onsubmit="return confirm('Reject this request?');">
                                                    @csrf
                                                    <button type="submit" class="text-[11px] text-rose-300 hover:text-rose-200 underline">Reject</button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-6 py-16 text-center text-blue-200">No plan requests.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-white/10">{{ $requests->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
