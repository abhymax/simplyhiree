@extends('layouts.client')

@section('client_content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 border-b border-white/10 pb-6">
            <a href="{{ route('client.vendors.browse') }}" class="inline-flex items-center text-cyan-300 hover:text-white text-sm font-bold uppercase"><i class="fa-solid fa-arrow-left mr-2"></i> Back to Vendors</a>
            <h1 class="text-4xl font-extrabold mt-2">Invite Your Vendor</h1>
            <p class="text-blue-200 mt-1">Add a vendor you already work with. We'll generate an invite link you can share so they can join SimplyHiree.</p>
        </div>

        @if(session('success'))<div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl font-bold">{{ session('error') }}</div>@endif

        <div class="glass-card rounded-3xl p-6 mb-6">
            <form method="POST" action="{{ route('client.vendors.invite.store') }}" class="premium-form grid grid-cols-1 md:grid-cols-2 gap-4">
                @csrf
                <div class="flex flex-col">
                    <label class="text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wide">Vendor Name *</label>
                    <input name="name" required placeholder="Enter vendor name..." class="bg-slate-950/40 border border-white/10 rounded-lg text-white px-3 py-2.5">
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wide">Company Name</label>
                    <input name="company" placeholder="Enter company name (optional)..." class="bg-slate-950/40 border border-white/10 rounded-lg text-white px-3 py-2.5">
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wide">Email Address *</label>
                    <input name="email" type="email" required placeholder="Enter vendor email..." class="bg-slate-950/40 border border-white/10 rounded-lg text-white px-3 py-2.5">
                </div>
                <div class="flex flex-col">
                    <label class="text-xs font-bold text-slate-400 mb-1.5 uppercase tracking-wide">Phone Number *</label>
                    <input name="phone" required placeholder="Enter vendor phone..." class="bg-slate-950/40 border border-white/10 rounded-lg text-white px-3 py-2.5">
                </div>
                <button type="submit" class="md:col-span-2 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] transition-all text-white font-extrabold py-3 rounded-xl shadow-lg shadow-blue-500/20 mt-2">Create & Send Invite</button>
            </form>
        </div>

        <div class="glass-card rounded-3xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 text-white font-extrabold bg-slate-950/30">Sent Invites</div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm premium-table">
                    <thead class="bg-blue-950/30 text-cyan-200 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Vendor</th>
                            <th class="px-5 py-3">Contact</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Invite Link</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10">
                    @forelse($invitations as $i)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="px-5 py-3">
                                <div class="text-white font-bold">{{ $i->name }}</div>
                                <div class="text-blue-300 text-xs">{{ $i->company ?: 'No company info' }}</div>
                            </td>
                            <td class="px-5 py-3 text-blue-100 text-xs">
                                <div class="font-semibold text-slate-300">{{ $i->email }}</div>
                                <div class="text-slate-400">{{ $i->phone }}</div>
                            </td>
                            <td class="px-5 py-3">
                                @php $colors = ['pending'=>'bg-amber-500/15 border border-amber-500/30 text-amber-300','joined'=>'bg-emerald-500/15 border border-emerald-500/30 text-emerald-300','cancelled'=>'bg-slate-500/15 border border-slate-500/30 text-slate-300']; @endphp
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full border {{ $colors[$i->status] ?? '' }}">{{ ucfirst($i->status) }}</span>
                                @if($i->status === 'joined' && $i->joined_at)<div class="text-[10px] text-emerald-300 mt-1.5 font-semibold">on {{ $i->joined_at->format('d M, Y') }}</div>@endif
                            </td>
                            <td class="px-5 py-3 text-xs">
                                @php $link = url('/register/partner?invite=' . $i->invite_token); @endphp
                                <div class="flex items-center gap-2">
                                    <code id="invite-link-{{ $i->id }}" class="flex-1 bg-slate-950/50 border border-white/5 rounded px-2.5 py-1.5 text-cyan-300 select-all break-all font-mono">{{ $link }}</code>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button type="button"
                                                onclick="(function(btn){ const el = document.getElementById('invite-link-{{ $i->id }}'); navigator.clipboard.writeText(el.textContent.trim()).then(() => { btn.innerHTML = '<i class=\'fa-solid fa-check\'></i>'; btn.classList.add('bg-emerald-600'); setTimeout(() => { btn.innerHTML = '<i class=\'fa-regular fa-copy\'></i>'; btn.classList.remove('bg-emerald-600'); }, 1500); }).catch(() => alert('Copy failed — please select the text manually.')); })(this)"
                                                title="Copy link"
                                                class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-cyan-600 hover:bg-cyan-500 text-white border border-cyan-400 transition hover:scale-105 active:scale-95">
                                            <i class="fa-regular fa-copy"></i>
                                        </button>
                                        <form method="POST" action="{{ route('client.vendors.invite.email', $i->id) }}" class="m-0">
                                            @csrf
                                            <button type="submit"
                                                    title="Resend branded invitation email"
                                                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white border border-indigo-400 transition hover:scale-105 active:scale-95">
                                                <i class="fa-solid fa-envelope"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-slate-400">No invites yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-white/10">{{ $invitations->links() }}</div>
        </div>
@endsection
