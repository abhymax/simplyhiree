@extends('layouts.app')

@section('content')
<div x-data="{ editMember: null }"
     class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 text-white -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-indigo-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>
    <div class="absolute bottom-0 left-0 w-96 h-96 bg-cyan-500 rounded-full mix-blend-screen blur-[140px] opacity-20"></div>

    {{-- Edit Member Modal --}}
    <div x-show="editMember !== null"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="display:none;">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="editMember = null"></div>
        <div class="relative z-10 bg-slate-900 border border-white/20 rounded-3xl shadow-2xl w-full max-w-lg p-7"
             @click.stop>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-extrabold text-white">Edit Team Member</h2>
                <button @click="editMember = null" class="text-white/50 hover:text-white transition text-xl leading-none">&times;</button>
            </div>

            <template x-if="editMember">
                <form :action="'/partner/team/' + editMember.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Name *</label>
                            <input type="text" name="name" :value="editMember.name" required
                                   class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        </div>
                        <div>
                            <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Email *</label>
                            <input type="email" name="email" :value="editMember.email" required
                                   class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        </div>
                        <div>
                            <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Mobile</label>
                            <input type="text" name="mobile" :value="editMember.mobile"
                                   class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        </div>
                        <div>
                            <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Role *</label>
                            <select name="team_role"
                                    class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                                <option value="Recruiter" :selected="editMember.team_role === 'Recruiter'">Recruiter</option>
                                <option value="Manager"   :selected="editMember.team_role === 'Manager'">Manager</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Access Level *</label>
                            <select name="access_level"
                                    class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                                <option value="full"             :selected="editMember.access_level === 'full'">Full (incl. commercials)</option>
                                <option value="submissions_only" :selected="editMember.access_level === 'submissions_only'">Submissions Only (no commercials)</option>
                                <option value="view_only"        :selected="editMember.access_level === 'view_only'">View Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-t border-white/10 pt-4">
                        <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">New Password <span class="text-white/40 normal-case font-normal">(leave blank to keep current)</span></label>
                        <input type="password" name="password" minlength="8" placeholder="Min 8 characters"
                               class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="editMember = null"
                                class="px-5 py-2.5 rounded-xl text-sm font-bold bg-white/10 hover:bg-white/20 text-white transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-6 py-2.5 rounded-xl text-sm font-bold text-slate-900 transition-all hover:-translate-y-0.5"
                                style="background: linear-gradient(135deg,#22d3ee,#0ea5e9); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                            Save Changes
                        </button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto">
        <div class="mb-6 border-b border-white/10 pb-6 flex justify-between items-end">
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight">Team Management</h1>
                <p class="text-blue-200 mt-1">Add recruiters &amp; managers to your team. Each member gets their own login.</p>
            </div>
            @php
                $tierColors = ['Bronze'=>'bg-orange-700/30 text-orange-200 border-orange-400/40','Silver'=>'bg-slate-400/20 text-slate-200 border-slate-400/40','Gold'=>'bg-yellow-500/20 text-yellow-200 border-yellow-400/40','Diamond'=>'bg-cyan-500/20 text-cyan-200 border-cyan-400/40'];
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
            <div class="mb-5 px-5 py-3 bg-emerald-500/20 border border-emerald-500/50 text-emerald-200 rounded-xl font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-5 px-5 py-3 bg-rose-500/20 border border-rose-500/50 text-rose-100 rounded-xl text-sm">
                <strong>Please fix:</strong>
                <ul class="list-disc list-inside mt-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        {{-- Add member --}}
        @if(auth()->user()->isPartnerOwner())
        <details class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl p-6 mb-6">
            <summary class="cursor-pointer text-white font-bold text-lg select-none">
                <i class="fa-solid fa-plus mr-2 text-cyan-400"></i>Add Team Member
            </summary>
            <form method="POST" action="{{ route('partner.team.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-5">
                @csrf
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Name *</label>
                    <input type="text" name="name" required placeholder="Full name" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Email *</label>
                    <input type="email" name="email" required placeholder="Login email" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Mobile</label>
                    <input type="text" name="mobile" placeholder="Phone number" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Role *</label>
                    <select name="team_role" required class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="Recruiter">Recruiter</option>
                        <option value="Manager">Manager</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Access Level *</label>
                    <select name="access_level" required class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                        <option value="full">Full (incl. commercials)</option>
                        <option value="submissions_only">Submissions Only (no commercials)</option>
                        <option value="view_only">View Only</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Password *</label>
                    <input type="password" name="password" required minlength="8" placeholder="Min 8 characters" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div class="md:col-span-3">
                    <button type="submit"
                            class="px-8 py-2.5 rounded-xl text-sm font-bold text-slate-900 transition-all hover:-translate-y-0.5"
                            style="background: linear-gradient(135deg,#22d3ee,#0ea5e9); box-shadow: 0 8px 20px -6px rgba(34,211,238,.5);">
                        <i class="fa-solid fa-user-plus mr-2"></i>Create Member
                    </button>
                </div>
            </form>
        </details>
        @endif

        {{-- Team table --}}
        @php $rows = collect([$owner])->merge($members); @endphp
        <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl overflow-hidden shadow-2xl">
            <div class="px-6 py-4 border-b border-white/10 flex items-center justify-between">
                <h2 class="text-white font-extrabold text-lg">Team Members</h2>
                <span class="text-blue-300 text-sm">{{ $members->count() }} member{{ $members->count() !== 1 ? 's' : '' }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-blue-950/50 text-cyan-200 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                        <tr>
                            <th class="px-5 py-4">Member</th>
                            <th class="px-5 py-4">Role / Access</th>
                            <th class="px-5 py-4 text-right">Submitted</th>
                            <th class="px-5 py-4 text-right">Shortlisted</th>
                            <th class="px-5 py-4 text-right">Joined</th>
                            <th class="px-5 py-4 text-right">Revenue</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/10 text-white">
                        @foreach($rows as $m)
                            @php
                                $s   = $stats[$m->id] ?? null;
                                $rev = $revenue[$m->id] ?? 0;
                                $isOwner = $m->id === $owner->id;
                                $isArchived = $m->status === 'archived';
                            @endphp
                            <tr class="hover:bg-white/5 align-middle {{ $isArchived ? 'opacity-50' : '' }}">
                                <td class="px-5 py-4">
                                    <div class="font-bold flex items-center gap-2">
                                        {{ $m->name }}
                                        @if($isOwner) <span class="text-cyan-300 text-[10px] uppercase font-bold border border-cyan-400/30 px-1.5 py-0.5 rounded">Owner</span> @endif
                                        @if($isArchived) <span class="text-rose-300 text-[10px] uppercase font-bold border border-rose-400/30 px-1.5 py-0.5 rounded">Archived</span> @endif
                                    </div>
                                    <div class="text-xs text-blue-300">{{ $m->email }}</div>
                                    @if($m->profile?->phone_number)
                                        <div class="text-xs text-blue-400">{{ $m->profile->phone_number }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-xs">
                                    <div class="text-blue-100 font-semibold">{{ $isOwner ? 'Owner' : ($m->team_role ?? '—') }}</div>
                                    @if(!$isOwner)
                                        @php
                                            $accessLabels = ['full'=>'Full access','submissions_only'=>'Submissions only','view_only'=>'View only'];
                                        @endphp
                                        <div class="text-blue-300/70 mt-0.5">{{ $accessLabels[$m->access_level ?? 'full'] ?? $m->access_level }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right text-white/80">{{ $s->submitted ?? 0 }}</td>
                                <td class="px-5 py-4 text-right text-white/80">{{ $s->shortlisted ?? 0 }}</td>
                                <td class="px-5 py-4 text-right text-emerald-300 font-bold">{{ $s->joined ?? 0 }}</td>
                                <td class="px-5 py-4 text-right text-emerald-300 font-extrabold">{{ $rev ? '₹'.number_format($rev) : '—' }}</td>
                                <td class="px-5 py-4">
                                    @php
                                        $statusStyle = match($m->status ?? 'active') {
                                            'active'     => 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40',
                                            'restricted' => 'bg-amber-500/20 text-amber-200 border-amber-400/40',
                                            'archived'   => 'bg-rose-500/20 text-rose-200 border-rose-400/40',
                                            default      => 'bg-slate-500/20 text-slate-200 border-slate-400/40',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $statusStyle }}">
                                        {{ ucfirst($m->status ?? 'active') }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    @if(!$isOwner && auth()->user()->isPartnerOwner())
                                        <div class="flex items-center justify-end gap-2">
                                            @if(!$isArchived)
                                                {{-- Edit --}}
                                                <button type="button"
                                                        @click="editMember = {
                                                            id: {{ $m->id }},
                                                            name: @js($m->name),
                                                            email: @js($m->email),
                                                            mobile: @js($m->profile?->phone_number ?? ''),
                                                            team_role: @js($m->team_role ?? 'Recruiter'),
                                                            access_level: @js($m->access_level ?? 'full')
                                                        }"
                                                        class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition-all">
                                                    <i class="fa-solid fa-pen mr-1"></i>Edit
                                                </button>

                                                {{-- Disable / Enable --}}
                                                <form method="POST" action="{{ route('partner.team.toggle', $m->id) }}" class="inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $m->status === 'active' ? 'bg-amber-500/20 text-amber-200 hover:bg-amber-500/40 border border-amber-400/30' : 'bg-emerald-500/20 text-emerald-200 hover:bg-emerald-500/40 border border-emerald-400/30' }}">
                                                        <i class="fa-solid {{ $m->status === 'active' ? 'fa-ban' : 'fa-circle-check' }} mr-1"></i>
                                                        {{ $m->status === 'active' ? 'Disable' : 'Enable' }}
                                                    </button>
                                                </form>

                                                {{-- Archive --}}
                                                <form method="POST" action="{{ route('partner.team.destroy', $m->id) }}"
                                                      onsubmit="return confirm('Archive {{ addslashes($m->name) }}? Their login will be disabled but all submission history is kept.')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-500/20 text-rose-200 hover:bg-rose-500/40 border border-rose-400/30 transition-all">
                                                        <i class="fa-solid fa-box-archive mr-1"></i>Archive
                                                    </button>
                                                </form>
                                            @else
                                                {{-- Restore archived member --}}
                                                <form method="POST" action="{{ route('partner.team.toggle', $m->id) }}" class="inline">
                                                    @csrf @method('PATCH')
                                                    <button type="submit"
                                                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/20 text-emerald-200 hover:bg-emerald-500/40 border border-emerald-400/30 transition-all">
                                                        <i class="fa-solid fa-rotate-left mr-1"></i>Restore
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
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
