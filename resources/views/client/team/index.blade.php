@extends('layouts.client')

@section('client_content')
<div class="max-w-6xl mx-auto" x-data="{ editOpen: false, editMember: { id: null, name: '', team_name: '', email: '', mobile: '', modules: [] } }">

    <div class="mb-6">
        <h1 class="text-3xl md:text-4xl font-extrabold text-white">Team Management</h1>
        <p class="text-blue-200 mt-1">Add teammates with their own login. Choose which modules each member can use. Billing, commercials and company settings stay with you.</p>
    </div>

    @if(session('success'))
        <div class="mb-5 px-5 py-3 rounded-2xl bg-emerald-500/20 border border-emerald-500/50 text-emerald-100 font-bold flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error') || $errors->any())
        <div class="mb-5 px-5 py-3 rounded-2xl bg-rose-500/20 border border-rose-500/50 text-rose-100 font-bold">
            <i class="fa-solid fa-triangle-exclamation mr-1"></i> {{ session('error') ?? $errors->first() }}
        </div>
    @endif

    {{-- Add member --}}
    <details class="mb-6 rounded-3xl border border-white/15 bg-slate-900/60 backdrop-blur-xl p-6 shadow-2xl">
        <summary class="cursor-pointer text-lg font-bold text-white"><i class="fa-solid fa-user-plus mr-2 text-cyan-400"></i>Add Team Member</summary>
        <form method="POST" action="{{ route('client.team.store') }}" class="mt-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Full name" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Team name *</label>
                    <input type="text" name="team_name" value="{{ old('team_name') }}" required placeholder="e.g. Hiring Desk" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Mobile *</label>
                    <input type="text" name="mobile" value="{{ old('mobile') }}" required placeholder="Phone number" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="Login email" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
                <div>
                    <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-1">Password *</label>
                    <input type="password" name="password" required minlength="8" placeholder="Min 8 characters" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-xs text-blue-200 font-bold uppercase tracking-wide mb-2">Modules this member can access</label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
                    @foreach($modules as $key => $label)
                        <label class="flex items-center gap-2 rounded-xl border border-white/15 bg-slate-800/60 px-3 py-2.5 cursor-pointer hover:border-cyan-400/50 transition">
                            <input type="checkbox" name="modules[]" value="{{ $key }}" @checked(in_array($key, old('modules', [])))
                                   class="rounded bg-slate-900 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                            <span class="text-sm text-slate-200">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="mt-2 text-[11px] text-slate-400">Financial &amp; commercial areas (billing, commercials, plan) and company settings are never delegated.</p>
            </div>

            <div class="mt-5">
                <button class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold"><i class="fa-solid fa-plus mr-1"></i>Add member</button>
            </div>
        </form>
    </details>

    {{-- Members list --}}
    <div class="rounded-3xl border border-white/15 bg-slate-900/60 backdrop-blur-xl shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
            <h2 class="text-lg font-bold text-white">Team members</h2>
            <span class="text-blue-300 text-sm">{{ $members->count() }} member{{ $members->count() !== 1 ? 's' : '' }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-blue-950/50 text-cyan-300 uppercase text-xs tracking-wider border-b border-white/10">
                    <tr>
                        <th class="px-5 py-3 text-left">Member</th>
                        <th class="px-5 py-3 text-left">Modules</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @php $ownerRow = $owner; @endphp
                    <tr class="bg-white/5">
                        <td class="px-5 py-4">
                            <div class="font-bold text-white flex items-center gap-2">{{ $ownerRow->name }}
                                <span class="text-cyan-300 text-[10px] uppercase font-bold border border-cyan-400/30 px-1.5 py-0.5 rounded">Owner</span>
                            </div>
                            <div class="text-xs text-blue-300">{{ $ownerRow->email }}</div>
                            @if($ownerRow->profile?->phone_number)<div class="text-xs text-blue-400"><i class="fa-solid fa-phone mr-1 text-[9px]"></i>{{ $ownerRow->profile->phone_number }}</div>@endif
                        </td>
                        <td class="px-5 py-4 text-xs text-emerald-200">Full access</td>
                        <td class="px-5 py-4"><span class="text-emerald-300 text-xs font-bold">Active</span></td>
                        <td class="px-5 py-4 text-right text-slate-500 text-xs">—</td>
                    </tr>

                    @forelse($members as $m)
                        @php
                            $isArchived = $m->status === 'archived';
                            $mods = is_array($m->team_modules) ? $m->team_modules : [];
                        @endphp
                        <tr class="hover:bg-white/5 {{ $isArchived ? 'opacity-50' : '' }}">
                            <td class="px-5 py-4">
                                <div class="font-bold text-white flex items-center gap-2">
                                    {{ $m->name }}
                                    @if(filled($m->team_name))<span class="text-emerald-200 text-[10px] uppercase font-bold border border-emerald-400/30 bg-emerald-500/10 px-1.5 py-0.5 rounded"><i class="fa-solid fa-users-line mr-1"></i>{{ $m->team_name }}</span>@endif
                                    @if($isArchived)<span class="text-rose-300 text-[10px] uppercase font-bold border border-rose-400/30 px-1.5 py-0.5 rounded">Archived</span>@endif
                                </div>
                                <div class="text-xs text-blue-300">{{ $m->email }}</div>
                                @if($m->profile?->phone_number)<div class="text-xs text-blue-400"><i class="fa-solid fa-phone mr-1 text-[9px]"></i>{{ $m->profile->phone_number }}</div>@endif
                            </td>
                            <td class="px-5 py-4">
                                @if(count($mods))
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($mods as $mk)
                                            <span class="text-[10px] font-bold rounded-md border border-white/15 bg-white/5 text-slate-200 px-1.5 py-0.5">{{ $modules[$mk] ?? $mk }}</span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-500 italic">No modules</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-xs font-bold {{ $m->status === 'active' ? 'text-emerald-300' : 'text-amber-300' }}">{{ ucfirst(str_replace('_',' ',$m->status)) }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end items-center gap-2">
                                    @unless($isArchived)
                                        <button type="button"
                                            @click="editMember = { id: {{ $m->id }}, name: @js($m->name), team_name: @js($m->team_name ?? ''), email: @js($m->email), mobile: @js($m->profile?->phone_number ?? ''), modules: @js($mods) }; editOpen = true"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold bg-white/10 hover:bg-white/20 text-white transition"><i class="fa-solid fa-pen mr-1"></i>Edit</button>
                                        <form method="POST" action="{{ route('client.team.toggle', $m) }}">@csrf @method('PATCH')
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500/20 hover:bg-amber-500 text-amber-100 hover:text-white transition">{{ $m->status === 'active' ? 'Suspend' : 'Activate' }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('client.team.destroy', $m) }}" onsubmit="return confirm('Archive {{ addslashes($m->name) }}? Their login will be disabled.')">@csrf @method('DELETE')
                                            <button class="px-3 py-1.5 rounded-lg text-xs font-bold bg-rose-500/20 hover:bg-rose-500 text-rose-100 hover:text-white transition"><i class="fa-solid fa-box-archive"></i></button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500">Archived</span>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-slate-400">No team members yet. Add your first above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Edit modal --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-[70] flex items-start justify-center overflow-y-auto px-4 py-10" style="display:none;">
        <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" @click="editOpen = false"></div>
        <div class="relative z-10 w-full max-w-2xl rounded-2xl border border-white/15 bg-slate-900 p-6 text-white shadow-2xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-black">Edit team member</h3>
                <button type="button" @click="editOpen = false" class="text-slate-400 hover:text-white"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form method="POST" x-bind:action="'{{ url('client/team') }}/' + editMember.id">
                @csrf @method('PATCH')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div><label class="block text-xs text-blue-200 font-bold uppercase mb-1">Name *</label><input type="text" name="name" x-model="editMember.name" required class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm"></div>
                    <div><label class="block text-xs text-blue-200 font-bold uppercase mb-1">Team name *</label><input type="text" name="team_name" x-model="editMember.team_name" required class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm"></div>
                    <div><label class="block text-xs text-blue-200 font-bold uppercase mb-1">Email *</label><input type="email" name="email" x-model="editMember.email" required class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm"></div>
                    <div><label class="block text-xs text-blue-200 font-bold uppercase mb-1">Mobile *</label><input type="text" name="mobile" x-model="editMember.mobile" required class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm"></div>
                    <div class="md:col-span-2"><label class="block text-xs text-blue-200 font-bold uppercase mb-1">New password (leave blank to keep)</label><input type="password" name="password" minlength="8" placeholder="Min 8 characters" class="w-full bg-slate-800 border border-white/20 rounded-xl px-3 py-2.5 text-white text-sm"></div>
                </div>
                <div class="mt-4">
                    <label class="block text-xs text-blue-200 font-bold uppercase mb-2">Modules</label>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
                        @foreach($modules as $key => $label)
                            <label class="flex items-center gap-2 rounded-xl border border-white/15 bg-slate-800/60 px-3 py-2.5 cursor-pointer hover:border-cyan-400/50 transition">
                                <input type="checkbox" name="modules[]" value="{{ $key }}" :checked="editMember.modules.includes('{{ $key }}')" class="rounded bg-slate-900 border-slate-600 text-cyan-500 focus:ring-cyan-500">
                                <span class="text-sm text-slate-200">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" @click="editOpen = false" class="px-5 py-2.5 rounded-xl border border-white/15 text-slate-200 font-bold">Cancel</button>
                    <button class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-white font-bold">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
