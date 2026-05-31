@extends('layouts.client')

@section('client_content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 border-b border-white/10 pb-6">
            <a href="{{ route('client.vendors.browse') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase"><i class="fa-solid fa-arrow-left mr-2"></i> Back to Vendors</a>
            <h1 class="text-4xl font-extrabold mt-2">Ask SimplyHiree to Assign Vendors</h1>
            <p class="text-blue-200 mt-1">Tell us how many top vendors you need, the industry and the location — we'll handpick them for you.</p>
        </div>

        @if(session('success'))<div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">{{ session('success') }}</div>@endif

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6">
            <form method="POST" action="{{ route('client.vendors.assign-request.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <input type="number" name="vendor_count" required min="1" max="50" value="5" placeholder="How many vendors? *" class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                <input name="industry_hint" placeholder="Industry (optional)" class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                <input name="location_hint" placeholder="Location (optional)" class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                <textarea name="notes" rows="3" placeholder="Any specifics — roles, volume, urgency…" class="md:col-span-3 bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5"></textarea>
                <button type="submit" class="md:col-span-3 bg-purple-500 hover:bg-purple-400 text-white font-extrabold py-3 rounded-xl">Send Request</button>
            </form>
        </div>

        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 text-white font-extrabold">Past Requests</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-200 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Requested</th>
                            <th class="px-5 py-3">Count</th>
                            <th class="px-5 py-3">Industry / Location</th>
                            <th class="px-5 py-3">Notes</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                    @forelse($requests as $r)
                        <tr class="hover:bg-white/5">
                            <td class="px-5 py-3 text-blue-100">{{ $r->created_at->format('d M, Y') }}</td>
                            <td class="px-5 py-3 text-white font-bold">{{ $r->vendor_count }}</td>
                            <td class="px-5 py-3 text-blue-100 text-xs">{{ $r->industry_hint ?? '—' }}<br>{{ $r->location_hint ?? '—' }}</td>
                            <td class="px-5 py-3 text-blue-100 text-xs max-w-[280px]">{{ \Illuminate\Support\Str::limit($r->notes, 140) ?: '—' }}</td>
                            <td class="px-5 py-3">
                                @php $colors = ['pending'=>'bg-amber-500/20 text-amber-200','in_progress'=>'bg-blue-500/20 text-blue-200','fulfilled'=>'bg-emerald-500/20 text-emerald-200','cancelled'=>'bg-slate-500/20 text-slate-200']; @endphp
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $colors[$r->status] ?? '' }}">{{ ucfirst(str_replace('_',' ', $r->status)) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-blue-200">No requests yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">{{ $requests->links() }}</div>
        </div>
@endsection
