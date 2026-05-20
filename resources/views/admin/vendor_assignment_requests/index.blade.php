<x-app-layout title="Vendor Assignment Requests">
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-overlay filter blur-[120px] opacity-30"></div>
    <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-overlay filter blur-[120px] opacity-30"></div>

    <div class="relative z-10 max-w-7xl mx-auto">

        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3 mb-8 border-b border-white/10 pb-6">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase mb-2">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
                <h1 class="text-4xl md:text-5xl font-extrabold tracking-tight text-white">Vendor Assignment Requests</h1>
                <p class="text-blue-200 mt-2">Clients asking SimplyHiree to attach vetted vendors to their account.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 px-5 py-3 rounded-2xl bg-emerald-500/15 border border-emerald-400/40 text-emerald-100">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <a href="?status=pending" class="bg-amber-500/15 border border-amber-400/40 rounded-2xl p-4 hover:bg-amber-500/25 transition">
                <p class="text-amber-200 text-xs font-bold uppercase">Pending</p>
                <p class="text-3xl font-extrabold text-amber-200">{{ $counts['pending'] }}</p>
            </a>
            <a href="?status=in_progress" class="bg-blue-500/15 border border-blue-400/40 rounded-2xl p-4 hover:bg-blue-500/25 transition">
                <p class="text-blue-200 text-xs font-bold uppercase">In Progress</p>
                <p class="text-3xl font-extrabold text-blue-200">{{ $counts['in_progress'] }}</p>
            </a>
            <a href="?status=fulfilled" class="bg-emerald-500/15 border border-emerald-400/40 rounded-2xl p-4 hover:bg-emerald-500/25 transition">
                <p class="text-emerald-200 text-xs font-bold uppercase">Fulfilled</p>
                <p class="text-3xl font-extrabold text-emerald-200">{{ $counts['fulfilled'] }}</p>
            </a>
            <a href="?status=cancelled" class="bg-slate-500/15 border border-slate-400/40 rounded-2xl p-4 hover:bg-slate-500/25 transition">
                <p class="text-slate-200 text-xs font-bold uppercase">Cancelled</p>
                <p class="text-3xl font-extrabold text-slate-200">{{ $counts['cancelled'] }}</p>
            </a>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Client</th>
                            <th class="px-6 py-4 text-center">Vendor Count</th>
                            <th class="px-6 py-4">Industry / Location</th>
                            <th class="px-6 py-4">Notes</th>
                            <th class="px-6 py-4">Requested</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @forelse($requests as $r)
                            <tr class="hover:bg-white/5">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white">{{ $r->client->name ?? '—' }}</div>
                                    <div class="text-xs text-cyan-200">{{ $r->client->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-cyan-500/20 text-cyan-100 border border-cyan-400/40 text-xs font-bold px-3 py-1 rounded-full">
                                        {{ $r->vendor_count }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-blue-100">
                                    @if($r->industry_hint)<div><i class="fa-solid fa-briefcase mr-1 text-cyan-300"></i> {{ $r->industry_hint }}</div>@endif
                                    @if($r->location_hint)<div class="mt-0.5"><i class="fa-solid fa-location-dot mr-1 text-cyan-300"></i> {{ $r->location_hint }}</div>@endif
                                </td>
                                <td class="px-6 py-4 text-xs text-blue-100 max-w-xs">{{ \Illuminate\Support\Str::limit($r->notes, 120) }}</td>
                                <td class="px-6 py-4 text-xs text-blue-200">{{ $r->created_at->format('d M, Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    @php
                                        $colors = [
                                            'pending'     => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                                            'in_progress' => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                                            'fulfilled'   => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                                            'cancelled'   => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                                        ];
                                    @endphp
                                    <span class="text-xs font-bold px-2.5 py-1 rounded-full border {{ $colors[$r->status] ?? '' }}">
                                        {{ ucwords(str_replace('_', ' ', $r->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.vendor-assignment-requests.show', $r) }}"
                                       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-1.5 rounded-lg text-xs font-bold whitespace-nowrap">
                                        @if($r->status === 'pending' || $r->status === 'in_progress')
                                            <i class="fa-solid fa-user-plus"></i> Assign
                                        @else
                                            <i class="fa-regular fa-eye"></i> View
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-16 text-center text-blue-200">
                                <i class="fa-solid fa-handshake text-5xl text-blue-300 mb-3 block"></i>
                                No vendor assignment requests yet.
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="p-4 border-t border-white/10 bg-slate-900/60">{{ $requests->onEachSide(1)->links() }}</div>
            @endif
        </div>

    </div>
</div>
</x-app-layout>
