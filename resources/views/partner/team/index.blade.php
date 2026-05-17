@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="mb-6 border-b border-white/10 pb-6 flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight">Team Management</h1>
                <p class="text-blue-200 mt-1">Add recruiters &amp; managers to your team. Each member gets their own login.</p>
            </div>
            @php
                $tierColors = [
                    'Bronze' => 'bg-orange-700/30 text-orange-200 border-orange-400/40',
                    'Silver' => 'bg-slate-400/20 text-slate-200 border-slate-400/40',
                    'Gold'   => 'bg-yellow-500/20 text-yellow-200 border-yellow-400/40',
                    'Diamond'=> 'bg-cyan-500/20 text-cyan-200 border-cyan-400/40',
                ];
                $tier = $owner->partner_tier ?? 'Bronze';
                $plan = $owner->partner_plan ?? 'Free';
            @endphp
            <div class="text-right">
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold border {{ $tierColors[$tier] ?? '' }}">
                    <i class="fa-solid fa-award"></i> {{ $tier }} Tier
                </div>
                <div class="text-blue-200 text-xs mt-1">Plan: <span class="font-bold text-white">{{ $plan }}</span></div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mb-5 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl text-sm">
                <strong>Please fix:</strong>
                <ul class="list-disc list-inside mt-1">@foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
            </div>
        @endif

        {{-- Add member --}}
        @if(auth()->user()->isPartnerOwner())
        <details class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6">
            <summary class="cursor-pointer text-white font-bold text-lg">+ Add Team Member</summary>
            <form method="POST" action="{{ route('partner.team.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-4">
                @csrf
                <input type="text" name="name" required placeholder="Name *" class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                <input type="email" name="email" required placeholder="Email *" class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                <input type="text" name="mobile" placeholder="Mobile" class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                <select name="team_role" required class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                    <option value="Recruiter">Role: Recruiter</option>
                    <option value="Manager">Role: Manager</option>
                </select>
                <select name="access_level" required class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                    <option value="full">Access: Full (incl. commercials)</option>
                    <option value="submissions_only">Access: Submissions Only (no commercials)</option>
                    <option value="view_only">Access: View Only</option>
                </select>
                <input type="password" name="password" required minlength="8" placeholder="Set Password (min 8) *" class="bg-slate-800 border border-white/10 rounded-lg text-white px-3 py-2.5">
                <button type="submit" class="md:col-span-3 bg-emerald-500 hover:bg-emerald-400 text-white font-bold py-2.5 px-4 rounded-xl">Create Member</button>
            </form>
        </details>
        @endif

        {{-- Team performance --}}
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-white/10">
                <h2 class="text-white font-extrabold text-lg">Team Performance</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Member</th>
                            <th class="px-5 py-4">Role / Access</th>
                            <th class="px-5 py-4 text-right">Submitted</th>
                            <th class="px-5 py-4 text-right">Shortlisted</th>
                            <th class="px-5 py-4 text-right">Interviewed</th>
                            <th class="px-5 py-4 text-right">Joined</th>
                            <th class="px-5 py-4 text-right">Revenue</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @php
                            $rows = collect([$owner])->merge($members);
                        @endphp
                        @foreach($rows as $m)
                            @php $s = $stats[$m->id] ?? null; $rev = $revenue[$m->id] ?? 0; @endphp
                            <tr class="hover:bg-white/5 align-top">
                                <td class="px-5 py-4">
                                    <div class="font-bold">{{ $m->name }} @if($m->id === $owner->id)<span class="text-cyan-300 text-[10px] uppercase font-bold ml-1">Owner</span>@endif</div>
                                    <div class="text-xs text-blue-200">{{ $m->email }}</div>
                                </td>
                                <td class="px-5 py-4 text-xs text-blue-100">
                                    {{ $m->id === $owner->id ? 'Owner' : ($m->team_role ?? '—') }}
                                    @if($m->id !== $owner->id)
                                        <div class="text-blue-300/70">Access: {{ str_replace('_',' ', $m->access_level ?? 'full') }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">{{ $s->submitted ?? 0 }}</td>
                                <td class="px-5 py-4 text-right">{{ $s->shortlisted ?? 0 }}</td>
                                <td class="px-5 py-4 text-right">{{ $s->interviewed ?? 0 }}</td>
                                <td class="px-5 py-4 text-right text-emerald-300 font-bold">{{ $s->joined ?? 0 }}</td>
                                <td class="px-5 py-4 text-right text-emerald-300 font-extrabold">{{ $rev ? '₹'.number_format($rev) : '—' }}</td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ ($m->status ?? 'active') === 'active' ? 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40' : 'bg-rose-500/20 text-rose-200 border-rose-400/40' }}">
                                        {{ ucfirst($m->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if($m->id !== $owner->id && auth()->user()->isPartnerOwner())
                                        <form method="POST" action="{{ route('partner.team.toggle', $m->id) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-[11px] text-rose-200 hover:text-rose-100 underline">{{ $m->status === 'active' ? 'Disable' : 'Enable' }}</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
