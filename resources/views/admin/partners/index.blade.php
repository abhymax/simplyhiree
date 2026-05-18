<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-purple-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Partner Network</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Manage agencies, recruiters, and freelancers.</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex items-center gap-4">
                    <div class="bg-purple-500/20 border border-purple-500/30 text-white px-5 py-2.5 rounded-xl shadow-lg flex items-center gap-3">
                        <span class="text-purple-300 text-xs font-bold uppercase tracking-wider">Total Partners</span>
                        <span class="text-2xl font-black">{{ $users->total() }}</span>
                    </div>
                    
                    <a href="{{ route('admin.partners.create') }}" class="inline-flex items-center bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-purple-600/30 transition transform hover:-translate-y-1">
                        <i class="fa-solid fa-user-plus mr-2"></i> New Partner
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-8 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md animate-bounce-short">
                    <i class="fa-solid fa-circle-check mr-3 text-2xl"></i> 
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.partners.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-5 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Name or Email..." 
                                class="w-full pl-10 bg-slate-800 border border-purple-500/30 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 font-medium h-[42px]">
                        </div>
                        <div class="md:col-span-3">
                            <select name="type" class="w-full bg-slate-800 border border-purple-500/30 rounded-xl text-white focus:ring-2 focus:ring-purple-400 focus:border-purple-400 font-medium h-[42px]">
                                <option value="" class="text-gray-400">All Types</option>
                                <option value="Placement Agency" {{ request('type') == 'Placement Agency' ? 'selected' : '' }}>Agencies</option>
                                <option value="Freelancer" {{ request('type') == 'Freelancer' ? 'selected' : '' }}>Freelancers</option>
                                <option value="Recruiter" {{ request('type') == 'Recruiter' ? 'selected' : '' }}>Recruiters</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <select name="status" class="w-full bg-slate-800 border border-purple-500/30 rounded-xl text-white focus:ring-2 focus:ring-purple-400 focus:border-purple-400 font-medium h-[42px]">
                                <option value="" class="text-gray-400">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="restricted" {{ request('status') == 'restricted' ? 'selected' : '' }}>Restricted</option>
                            </select>
                        </div>
                        <div class="md:col-span-2 flex items-center gap-2">
                            <button type="submit" class="flex-1 bg-purple-600 hover:bg-purple-500 text-white px-3 py-2 rounded-xl font-bold shadow-lg transition h-[42px] flex items-center justify-center">Filter</button>
                            @if(request()->anyFilled(['search', 'type', 'status']))
                                <a href="{{ route('admin.partners.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-2 rounded-xl transition h-[42px] flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Bulk action form (lives OUTSIDE the table so we don't nest forms with the per-row action forms inside <td>) --}}
                <form method="POST" action="{{ route('admin.partners.bulk-status') }}" id="partner-bulk-form" onsubmit="return partnerBulkConfirm(event)" class="hidden">
                    @csrf
                    <input type="hidden" name="action" id="partner-bulk-action" value="">
                </form>

                {{-- Bulk action bar --}}
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 px-6 py-4 border-b border-white/10 bg-slate-900/40">
                    <div class="flex items-center gap-3 text-sm">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-white font-semibold">
                            <input type="checkbox" id="partner-select-all" class="h-4 w-4 rounded border-white/30 bg-slate-800 text-purple-500 focus:ring-purple-400">
                            Select All On This Page
                        </label>
                        <span class="text-white/60">|</span>
                        <span class="text-white font-semibold">Selected: <span id="partner-count" class="text-amber-300 font-bold">0</span></span>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" data-action="approve" class="partner-bulk-btn inline-flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 disabled:bg-slate-700 disabled:text-slate-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-xl font-bold shadow-md transition border border-emerald-400/40 disabled:border-white/10" disabled>
                            <i class="fa-solid fa-check"></i> Approve
                        </button>
                        <button type="button" data-action="hold" class="partner-bulk-btn inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-400 disabled:bg-slate-700 disabled:text-slate-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-xl font-bold shadow-md transition border border-amber-400/40 disabled:border-white/10" disabled>
                            <i class="fa-regular fa-clock"></i> Hold
                        </button>
                        <button type="button" data-action="reject" class="partner-bulk-btn inline-flex items-center gap-2 bg-rose-500 hover:bg-rose-400 disabled:bg-slate-700 disabled:text-slate-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-xl font-bold shadow-md transition border border-rose-400/40 disabled:border-white/10" disabled>
                            <i class="fa-solid fa-ban"></i> Reject
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-purple-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-4 py-5 w-10"><span class="sr-only">Select</span></th>
                                <th class="px-6 py-5">Partner Name</th>
                                <th class="px-6 py-5">Mobile</th>
                                <th class="px-6 py-5">Type</th>
                                <th class="px-6 py-5">Tier</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Joined On</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($users as $user)
                                <tr class="hover:bg-white/5 transition duration-200 cursor-default group">
                                    <td class="px-4 py-5 align-top">
                                        <input type="checkbox" form="partner-bulk-form" name="ids[]" value="{{ $user->id }}" class="partner-row-cb h-4 w-4 rounded border-white/30 bg-slate-800 text-purple-500 focus:ring-purple-400">
                                    </td>
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-11 w-11 rounded-full bg-gradient-to-br from-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-2 ring-white/10">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.partners.show', $user->id) }}" class="font-bold text-white text-base hover:text-purple-400 transition underline decoration-transparent hover:decoration-purple-400">
                                                    {{ $user->name }}
                                                </a>
                                                <div class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-5">
                                        <span class="text-white font-semibold">
                                            {{ $user->profile?->phone_number ?? 'N/A' }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        @php $type = optional($user->partnerProfile)->company_type ?? 'Unknown'; @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                            {{ $type }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-5">
                                        @php
                                            $tier = $user->partner_tier ?: 'Bronze';
                                            $tierStyles = [
                                                'Bronze'  => 'background:#78350f; color:#fcd34d; border:1px solid #b45309;',
                                                'Silver'  => 'background:#475569; color:#f1f5f9; border:1px solid #94a3b8;',
                                                'Gold'    => 'background:#a16207; color:#fef08a; border:1px solid #eab308;',
                                                'Diamond' => 'background:#0e7490; color:#cffafe; border:1px solid #22d3ee;',
                                            ];
                                        @endphp
                                        <form method="POST" action="{{ route('admin.partners.tier.update', $user->id) }}" class="inline-flex">
                                            @csrf @method('PATCH')
                                            <select name="partner_tier" onchange="this.form.submit()"
                                                class="px-2.5 py-1 rounded-lg text-xs font-bold cursor-pointer appearance-none pr-7"
                                                style="{{ $tierStyles[$tier] ?? $tierStyles['Bronze'] }} background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 20 20%22 fill=%22currentColor%22><path d=%22M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z%22/></svg>'); background-repeat: no-repeat; background-position: right 0.4rem center;">
                                                @foreach(['Bronze','Silver','Gold','Diamond'] as $t)
                                                    <option value="{{ $t }}" {{ $tier === $t ? 'selected' : '' }} style="background:#0f172a; color:#fff;">{{ $t }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </td>

                                    <td class="px-6 py-5">
                                        @if($user->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-xs font-bold"><span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active</span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/50 text-xs font-bold"><i class="fa-solid fa-ban"></i> Restricted</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-5 text-slate-300 font-medium">{{ $user->created_at->format('M d, Y') }}</td>

                                    <td class="px-6 py-5 text-right" x-data>
                                        <div class="flex justify-end items-center gap-2">
                                            <a href="{{ route('admin.partners.show', $user->id) }}" class="h-9 w-9 rounded-lg bg-purple-600/20 hover:bg-purple-600 text-purple-400 hover:text-white transition flex items-center justify-center border border-purple-500/30 shadow-md" title="View"><i class="fa-solid fa-eye"></i></a>
                                            
                                            <a href="{{ route('admin.partners.edit', $user->id) }}" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-blue-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Edit"><i class="fa-solid fa-pen"></i></a>

                                            <button @click="$dispatch('open-modal', 'pwd-p-{{ $user->id }}')" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-blue-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Password"><i class="fa-solid fa-key"></i></button>

                                            @if($user->status !== 'active')
                                                <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST" class="inline">@csrf @method('PATCH')<input type="hidden" name="status" value="active"><button class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-emerald-500 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10"><i class="fa-solid fa-check"></i></button></form>
                                            @else
                                                <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST" class="inline">@csrf @method('PATCH')<input type="hidden" name="status" value="restricted"><button class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-rose-500 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10"><i class="fa-solid fa-ban"></i></button></form>
                                            @endif
                                        </div>
                                        <x-modal name="pwd-p-{{ $user->id }}">
                                            <div class="p-6 bg-slate-900 border border-white/20 rounded-2xl text-white text-left">
                                                <h2 class="text-xl font-bold mb-4">Reset Password</h2>
                                                <form method="POST" action="{{ route('admin.users.credentials.update', $user->id) }}">
                                                    @csrf @method('PATCH')
                                                    <div class="mb-4"><label class="block text-xs font-bold text-purple-300 uppercase mb-1">New Password</label><input type="password" name="password" class="w-full bg-slate-800 border-slate-600 rounded-xl text-white" required></div>
                                                    <div class="mb-6"><label class="block text-xs font-bold text-purple-300 uppercase mb-1">Confirm</label><input type="password" name="password_confirmation" class="w-full bg-slate-800 border-slate-600 rounded-xl text-white" required></div>
                                                    <div class="flex justify-end"><button class="px-6 py-2 bg-purple-600 hover:bg-purple-500 rounded-lg font-bold">Update</button></div>
                                                </form>
                                            </div>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-20 text-center text-slate-400">No partners found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-6 border-t border-white/10">{{ $users->links() }}</div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const form     = document.getElementById('partner-bulk-form');
        if (!form) return;
        const selectAll = document.getElementById('partner-select-all');
        const counter   = document.getElementById('partner-count');
        const actionFld = document.getElementById('partner-bulk-action');
        const buttons   = Array.from(document.querySelectorAll('.partner-bulk-btn'));
        const rowCbs    = () => Array.from(document.querySelectorAll('.partner-row-cb'));

        const updateState = () => {
            const checked = rowCbs().filter(c => c.checked);
            counter.textContent = checked.length;
            buttons.forEach(b => b.disabled = checked.length === 0);
            if (selectAll) {
                const all = rowCbs();
                selectAll.checked = all.length > 0 && checked.length === all.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < all.length;
            }
        };

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                rowCbs().forEach(c => c.checked = selectAll.checked);
                updateState();
            });
        }
        rowCbs().forEach(c => c.addEventListener('change', updateState));

        buttons.forEach(btn => btn.addEventListener('click', () => {
            actionFld.value = btn.dataset.action;
            form.requestSubmit();
        }));

        updateState();
    });

    function partnerBulkConfirm(e) {
        const checked = document.querySelectorAll('.partner-row-cb:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Select at least one partner.');
            return false;
        }
        const action = document.getElementById('partner-bulk-action').value;
        const verb = { approve: 'APPROVE', hold: 'HOLD', reject: 'REJECT' }[action] || action;
        if (!confirm(`Are you sure you want to ${verb} ${checked} partner(s)?`)) {
            e.preventDefault();
            return false;
        }
        return true;
    }
    </script>
</x-app-layout>
