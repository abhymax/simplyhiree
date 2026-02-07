<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-cyan-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Sub-Admin Managers</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Manage team members with special access permissions.</p>
                </div>
                
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('admin.sub_admins.create') }}" class="inline-flex items-center bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-blue-600/30 transition transform hover:-translate-y-1">
                        <i class="fa-solid fa-user-plus mr-2"></i> Add New Manager
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-8 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md animate-bounce-short">
                    <i class="fa-solid fa-circle-check mr-3 text-2xl"></i> 
                    {{ session('success') }}
                </div>
            @endif

            {{-- MAIN GLASS CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                <div class="px-8 py-6 border-b border-white/10 bg-white/5">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-users-gear text-cyan-400"></i> Team Members
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-cyan-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Manager Details</th>
                                <th class="px-6 py-5">Assigned Clients</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($managers as $manager)
                                <tr class="hover:bg-white/5 transition duration-200 cursor-default group">
                                    {{-- Manager Details --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-11 w-11 rounded-full bg-gradient-to-r from-cyan-500 to-blue-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-2 ring-white/10">
                                                {{ substr($manager->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-white text-base">{{ $manager->name }}</div>
                                                <div class="text-blue-300 text-xs">{{ $manager->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Assigned Clients --}}
                                    <td class="px-6 py-5">
                                        <div class="flex -space-x-2 overflow-hidden">
                                            @foreach($manager->assignedClients->take(3) as $client)
                                                <span class="inline-flex h-8 w-8 rounded-full ring-2 ring-slate-800 bg-indigo-600 items-center justify-center text-xs font-bold text-white shadow-md" title="{{ $client->name }}">
                                                    {{ substr($client->name, 0, 1) }}
                                                </span>
                                            @endforeach
                                            @if($manager->assignedClients->count() > 3)
                                                <span class="inline-flex h-8 w-8 rounded-full ring-2 ring-slate-800 bg-slate-700 items-center justify-center text-xs font-bold text-white shadow-md">
                                                    +{{ $manager->assignedClients->count() - 3 }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($manager->assignedClients->isEmpty())
                                            <span class="text-slate-500 text-xs italic">No clients assigned</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5">
                                        @if($manager->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 text-xs font-bold shadow-lg shadow-emerald-500/10">
                                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-red-500/20 text-red-300 border border-red-500/50 text-xs font-bold shadow-lg shadow-red-500/10">
                                                <i class="fa-solid fa-ban"></i> {{ ucfirst($manager->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-5 text-right" x-data>
                                        <div class="flex justify-end items-center gap-2">
                                            
                                            {{-- Password Reset --}}
                                            <button @click="$dispatch('open-modal', 'pwd-sub-{{ $manager->id }}')" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-indigo-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Reset Password">
                                                <i class="fa-solid fa-key"></i>
                                            </button>

                                            {{-- Edit --}}
                                            <a href="{{ route('admin.sub_admins.edit', $manager->id) }}" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-blue-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Edit Manager">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            {{-- Activate/Restrict --}}
                                            @if($manager->status !== 'active')
                                                <form action="{{ route('admin.users.status.update', $manager->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-emerald-500 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Activate Account">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.status.update', $manager->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="restricted">
                                                    <button type="submit" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-red-500 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Restrict Access">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        {{-- Password Reset Modal (Dark Theme) --}}
                                        <x-modal name="pwd-sub-{{ $manager->id }}">
                                            <div class="p-6 bg-slate-900 border border-white/20 rounded-2xl text-white">
                                                <div class="flex justify-between items-center mb-6 border-b border-white/10 pb-4">
                                                    <h2 class="text-xl font-bold flex items-center gap-2">
                                                        <i class="fa-solid fa-lock text-indigo-400"></i> Reset Password
                                                    </h2>
                                                    <button @click="$dispatch('close-modal', 'pwd-sub-{{ $manager->id }}')" class="text-slate-400 hover:text-white transition">
                                                        <i class="fa-solid fa-xmark text-lg"></i>
                                                    </button>
                                                </div>
                                                
                                                <p class="text-sm text-slate-300 mb-6">Update credentials for <strong>{{ $manager->name }}</strong>.</p>

                                                <form method="POST" action="{{ route('admin.users.credentials.update', $manager->id) }}">
                                                    @csrf @method('PATCH')
                                                    <div class="mb-4">
                                                        <label class="block text-xs font-bold text-cyan-300 uppercase mb-1">New Password</label>
                                                        <input type="password" name="password" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-indigo-500 focus:border-indigo-500" required>
                                                    </div>
                                                    <div class="mb-6">
                                                        <label class="block text-xs font-bold text-cyan-300 uppercase mb-1">Confirm Password</label>
                                                        <input type="password" name="password_confirmation" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-indigo-500 focus:border-indigo-500" required>
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="$dispatch('close-modal', 'pwd-sub-{{ $manager->id }}')" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-bold transition">Cancel</button>
                                                        <button type="submit" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-bold shadow-lg transition">Update Password</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-20 text-center">
                                        <div class="bg-white/10 inline-block p-6 rounded-full mb-4 backdrop-blur-md border border-white/10">
                                            <i class="fa-solid fa-users-slash text-5xl text-blue-200"></i>
                                        </div>
                                        <p class="text-xl font-bold text-white">No managers found.</p>
                                        <p class="text-blue-200 mt-2">Add your first sub-admin to delegate tasks.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>