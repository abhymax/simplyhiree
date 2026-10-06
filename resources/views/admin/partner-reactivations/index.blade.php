<x-app-layout>
    <div class="max-w-7xl mx-auto px-5 lg:px-8 py-8 text-white">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-7">
            <div>
                <p class="text-cyan-300 text-xs font-bold uppercase">Partner Control</p>
                <h1 class="text-3xl font-black mt-1">Activation Requests</h1>
                <p class="text-slate-400 mt-1">Review partners suspended after inactivity. Approval restores the owner and team.</p>
            </div>
            <div class="rounded-xl border border-amber-400/30 bg-amber-500/10 px-4 py-3">
                <span class="text-amber-300 text-xs font-bold uppercase">Pending</span>
                <strong class="block text-2xl">{{ $counts['pending'] ?? 0 }}</strong>
            </div>
        </div>

        @if(session('success')) <div class="mb-5 rounded-xl border border-emerald-400/30 bg-emerald-500/10 p-4 text-emerald-200">{{ session('success') }}</div> @endif
        @if(session('error')) <div class="mb-5 rounded-xl border border-rose-400/30 bg-rose-500/10 p-4 text-rose-200">{{ session('error') }}</div> @endif

        <div class="flex gap-2 mb-6">
            @foreach(['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $key=>$label)
                <a href="{{ route('admin.partner-reactivations.index', ['status'=>$key]) }}" class="px-4 py-2 rounded-lg border text-sm font-bold {{ $status === $key ? 'bg-indigo-600 border-indigo-400 text-white' : 'bg-slate-900/70 border-white/10 text-slate-300 hover:text-white' }}">
                    {{ $label }} <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-950/55 shadow-xl">
            <div class="grid grid-cols-[1.3fr_.8fr_1fr_1.2fr] gap-4 px-5 py-3 bg-slate-900 text-cyan-300 text-xs font-black uppercase">
                <div>Partner</div><div>Requested</div><div>Status</div><div>Action</div>
            </div>
            @forelse($requests as $item)
                <div class="grid grid-cols-1 lg:grid-cols-[1.3fr_.8fr_1fr_1.2fr] gap-4 items-center px-5 py-5 border-t border-white/10">
                    <div><strong class="block">{{ $item->user?->name ?? 'Deleted partner' }}</strong><span class="text-sm text-slate-400">{{ $item->user?->email }}</span>@if($item->message)<p class="text-xs text-slate-500 mt-2">{{ $item->message }}</p>@endif</div>
                    <div class="text-sm text-slate-300">{{ optional($item->requested_at)->format('d M Y, h:i A') }}</div>
                    <div><span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $item->status === 'pending' ? 'bg-amber-500/15 text-amber-300' : ($item->status === 'approved' ? 'bg-emerald-500/15 text-emerald-300' : 'bg-rose-500/15 text-rose-300') }}">{{ ucfirst($item->status) }}</span></div>
                    <div>
                        @if($item->status === 'pending')
                            <div class="flex gap-2">
                                <form method="POST" action="{{ route('admin.partner-reactivations.approve', $item) }}">@csrf<button class="rounded-lg bg-emerald-600 hover:bg-emerald-500 px-4 py-2 text-xs font-bold">Approve</button></form>
                                <form method="POST" action="{{ route('admin.partner-reactivations.reject', $item) }}">@csrf<button class="rounded-lg bg-rose-600 hover:bg-rose-500 px-4 py-2 text-xs font-bold">Reject</button></form>
                            </div>
                        @else
                            <span class="text-xs text-slate-400">{{ $item->reviewer?->name }} · {{ optional($item->reviewed_at)->format('d M Y') }}</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="py-16 text-center text-slate-400">No {{ $status }} activation requests.</div>
            @endforelse
        </div>
        <div class="mt-5">{{ $requests->links() }}</div>
    </div>
</x-app-layout>
