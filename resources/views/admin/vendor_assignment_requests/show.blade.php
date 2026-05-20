<x-app-layout title="Assign Vendors">
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-5xl mx-auto">

        <a href="{{ route('admin.vendor-assignment-requests.index') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase mb-3">
            <i class="fa-solid fa-arrow-left mr-2"></i> All Requests
        </a>

        <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white mb-2">Vendor Assignment Request</h1>

        @if(session('error'))<div class="mb-4 px-5 py-3 rounded-2xl bg-rose-500/15 border border-rose-400/40 text-rose-100">{{ session('error') }}</div>@endif
        @if($errors->any())
            <div class="mb-4 px-5 py-3 rounded-2xl bg-rose-500/15 border border-rose-400/40 text-rose-100">
                <p class="font-bold">Please fix:</p>
                <ul class="list-disc ml-6 text-sm">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Request summary --}}
        <div class="bg-white/10 border border-white/10 rounded-3xl p-6 md:p-8 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div>
                    <p class="text-cyan-300 text-xs font-bold uppercase mb-1">Client</p>
                    <p class="text-white font-bold text-lg">{{ $assignmentRequest->client->name ?? '—' }}</p>
                    <p class="text-blue-200 text-sm">{{ $assignmentRequest->client->email ?? '' }}</p>
                </div>
                <div>
                    <p class="text-cyan-300 text-xs font-bold uppercase mb-1">Wants</p>
                    <p class="text-white font-bold text-lg">Top {{ $assignmentRequest->vendor_count }} vendor(s)</p>
                </div>
                <div>
                    <p class="text-cyan-300 text-xs font-bold uppercase mb-1">Requested</p>
                    <p class="text-white font-bold">{{ $assignmentRequest->created_at->format('d M Y, H:i') }}</p>
                    @php
                        $colors = [
                            'pending'     => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                            'in_progress' => 'bg-blue-500/20 text-blue-200 border-blue-400/40',
                            'fulfilled'   => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                            'cancelled'   => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                        ];
                    @endphp
                    <span class="inline-block mt-1 text-xs font-bold px-2.5 py-1 rounded-full border {{ $colors[$assignmentRequest->status] ?? '' }}">
                        {{ ucwords(str_replace('_', ' ', $assignmentRequest->status)) }}
                    </span>
                </div>
            </div>

            @if($assignmentRequest->industry_hint || $assignmentRequest->location_hint)
                <div class="mt-5 pt-5 border-t border-white/10 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    @if($assignmentRequest->industry_hint)
                        <div><span class="text-cyan-300 text-xs font-bold uppercase">Industry hint:</span> <span class="text-white">{{ $assignmentRequest->industry_hint }}</span></div>
                    @endif
                    @if($assignmentRequest->location_hint)
                        <div><span class="text-cyan-300 text-xs font-bold uppercase">Location hint:</span> <span class="text-white">{{ $assignmentRequest->location_hint }}</span></div>
                    @endif
                </div>
            @endif

            @if($assignmentRequest->notes)
                <div class="mt-5 pt-5 border-t border-white/10">
                    <p class="text-cyan-300 text-xs font-bold uppercase mb-1">Client notes</p>
                    <p class="text-white text-sm whitespace-pre-line">{{ $assignmentRequest->notes }}</p>
                </div>
            @endif

            @if($assignmentRequest->status === 'fulfilled' || $assignmentRequest->status === 'cancelled')
                <div class="mt-5 pt-5 border-t border-white/10 text-xs text-blue-200">
                    {{ ucfirst($assignmentRequest->status) }} by <strong>{{ $assignmentRequest->fulfilledBy->name ?? '—' }}</strong>
                    on {{ optional($assignmentRequest->fulfilled_at)->format('d M Y, H:i') }}
                    @if($assignmentRequest->admin_notes)
                        <div class="mt-2 text-blue-100"><strong>Admin notes:</strong> {{ $assignmentRequest->admin_notes }}</div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Fulfillment form --}}
        @if(in_array($assignmentRequest->status, ['pending', 'in_progress']))
            <form method="POST" action="{{ route('admin.vendor-assignment-requests.fulfill', $assignmentRequest) }}" class="bg-white/10 border border-white/10 rounded-3xl p-6 md:p-8">
                @csrf
                <h2 class="text-xl font-bold text-white mb-1 flex items-center gap-3">
                    <span class="w-1.5 h-7 bg-emerald-500 rounded-full"></span>
                    <i class="fa-solid fa-user-plus text-emerald-400"></i> Pick Vendors to Assign
                </h2>
                <p class="text-blue-200 text-sm mb-5 ml-5">Tick {{ $assignmentRequest->vendor_count }}+ vendors to attach to this client's preferred list. They'll appear immediately in the client's "My Vendors" page.</p>

                <div class="mb-3 flex items-center gap-3 text-sm">
                    <input type="text" id="vendor-filter" placeholder="Filter by name / email / level…"
                           class="flex-1 bg-slate-900/40 border border-white/10 rounded-lg text-white text-sm px-3 py-2"
                           style="background-color:#0f172a !important; color:#fff !important;">
                    <span class="text-blue-200">Selected: <strong id="vendor-count" class="text-emerald-300">0</strong></span>
                </div>

                <div class="border border-white/10 rounded-2xl overflow-hidden max-h-[480px] overflow-y-auto bg-slate-900/40">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/70 text-cyan-300 uppercase font-extrabold text-xs sticky top-0">
                            <tr>
                                <th class="px-4 py-2 w-10"><input type="checkbox" id="select-all-v" class="rounded"></th>
                                <th class="px-4 py-2">Vendor</th>
                                <th class="px-4 py-2">Level</th>
                                <th class="px-4 py-2">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($eligibleVendors as $v)
                                <tr class="vendor-row hover:bg-white/5">
                                    <td class="px-4 py-3"><input type="checkbox" name="partner_ids[]" value="{{ $v->id }}" class="vendor-cb rounded"></td>
                                    <td class="px-4 py-3 vendor-search-blob" data-blob="{{ strtolower($v->name . ' ' . $v->email . ' ' . ($v->vendor_level ?? '')) }}">
                                        <div class="font-bold text-white">{{ $v->name }}</div>
                                        <div class="text-xs text-cyan-200">{{ $v->email }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($v->vendor_level)
                                            <span class="text-xs font-bold px-2 py-0.5 rounded-md bg-purple-500/20 text-purple-200 border border-purple-400/40">{{ $v->vendor_level }}</span>
                                        @else
                                            <span class="text-slate-500 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-amber-300 text-xs font-bold">{{ $v->avg_rating ? '★ ' . number_format((float) $v->avg_rating, 1) : '—' }} <span class="text-slate-400">({{ $v->total_ratings ?? 0 }})</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-6 py-8 text-center text-blue-200">No eligible vendors (all active partners are already attached to this client).</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-5">
                    <label class="block text-xs font-bold text-blue-200 uppercase tracking-wider mb-2">Admin notes (optional)</label>
                    <textarea name="admin_notes" rows="2" maxlength="2000"
                              placeholder="Anything to log about this assignment…"
                              class="w-full bg-slate-900/40 border border-white/10 rounded-xl text-white px-4 py-3"
                              style="background-color:#0f172a !important; color:#fff !important;"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-white/10 mt-5">
                    <form method="POST" action="{{ route('admin.vendor-assignment-requests.cancel', $assignmentRequest) }}" onsubmit="return confirm('Cancel this request?');">
                        @csrf
                        <button type="submit" class="bg-rose-500/20 hover:bg-rose-500 text-rose-100 hover:text-white border border-rose-400/40 font-bold py-2.5 px-5 rounded-xl">Cancel Request</button>
                    </form>
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold py-2.5 px-7 rounded-xl shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane"></i> Assign &amp; Notify Client
                    </button>
                </div>
            </form>
        @endif

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const all = document.getElementById('select-all-v');
        const cbs = () => document.querySelectorAll('.vendor-cb');
        const counter = document.getElementById('vendor-count');
        const update = () => counter.textContent = Array.from(cbs()).filter(c => c.checked).length;
        if (all) all.addEventListener('change', () => {
            document.querySelectorAll('.vendor-row:not([style*="display: none"]) .vendor-cb').forEach(c => c.checked = all.checked);
            update();
        });
        cbs().forEach(c => c.addEventListener('change', update));

        const filter = document.getElementById('vendor-filter');
        if (filter) filter.addEventListener('input', () => {
            const q = filter.value.toLowerCase().trim();
            document.querySelectorAll('.vendor-row').forEach(row => {
                const blob = row.querySelector('.vendor-search-blob')?.dataset.blob || '';
                row.style.display = blob.includes(q) ? '' : 'none';
            });
        });
    });
</script>
</x-app-layout>
