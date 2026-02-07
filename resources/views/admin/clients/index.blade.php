<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-blue-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-cyan-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Client Management</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Manage registered companies and billing cycles.</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex items-center gap-4">
                    {{-- Total Count --}}
                    <div class="bg-emerald-500/20 border border-emerald-500/30 text-white px-5 py-2.5 rounded-xl shadow-lg flex items-center gap-3">
                        <span class="text-emerald-300 text-xs font-bold uppercase tracking-wider">Total Clients</span>
                        <span class="text-2xl font-black">{{ $users->total() }}</span>
                    </div>
                    
                    <a href="{{ route('admin.clients.create') }}" class="inline-flex items-center bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold px-6 py-3 rounded-xl shadow-lg shadow-emerald-600/30 transition transform hover:-translate-y-1">
                        <i class="fa-solid fa-plus mr-2"></i> New Client
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
                
                {{-- 🔍 FILTER BAR --}}
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.clients.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <div class="md:col-span-4 relative">
                            <label class="block text-xs font-bold text-emerald-400 uppercase mb-1 ml-1">Search</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                                </div>
                                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Email or ID..." 
                                    class="w-full pl-10 bg-slate-800 border border-blue-500/30 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 font-medium h-[42px]">
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-emerald-400 uppercase mb-1 ml-1">Status</label>
                            <select name="status" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 font-medium h-[42px]">
                                <option value="" class="text-gray-400">All Statuses</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="restricted" {{ request('status') == 'restricted' ? 'selected' : '' }}>Restricted</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-emerald-400 uppercase mb-1 ml-1">Joined From</label>
                            <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-emerald-400 h-[42px]">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-emerald-400 uppercase mb-1 ml-1">Joined To</label>
                            <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-slate-800 border border-blue-500/30 rounded-xl text-white focus:ring-2 focus:ring-emerald-400 h-[42px]">
                        </div>
                        <div class="md:col-span-2 flex items-end gap-2">
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-500 text-white px-3 py-2 rounded-xl font-bold shadow-lg h-[42px]"><i class="fa-solid fa-filter mr-1"></i> Filter</button>
                            <a href="{{ route('admin.clients.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-2 rounded-xl h-[42px] flex items-center justify-center"><i class="fa-solid fa-xmark"></i></a>
                        </div>
                    </form>
                </div>

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-emerald-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Company Info</th>
                                <th class="px-6 py-5">Billable Cycle</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Joined Date</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($users as $user)
                                <tr class="hover:bg-white/5 transition duration-200 cursor-default group">
                                    {{-- Name (CLICKABLE) --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-11 w-11 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold text-lg shadow-lg ring-1 ring-white/20">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.clients.show', $user->id) }}" class="font-bold text-white text-base flex items-center gap-2 hover:text-emerald-400 transition">
                                                    {{ $user->name }}
                                                    <span class="text-[10px] bg-white/10 text-emerald-300 px-2 py-0.5 rounded border border-white/10 font-mono tracking-wide">
                                                        {{ $user->client_code }}
                                                    </span>
                                                </a>
                                                <div class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    {{-- ... [Cycle, Status, Joined columns same as before] ... --}}
                                    <td class="px-6 py-5">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30"><i class="fa-regular fa-calendar-check mr-2"></i> {{ $user->billable_period_days }} Days</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        @if($user->status === 'active') <span class="text-emerald-400 font-bold text-xs uppercase">Active</span>
                                        @else <span class="text-red-400 font-bold text-xs uppercase">{{ $user->status }}</span> @endif
                                    </td>
                                    <td class="px-6 py-5 text-slate-300">{{ $user->created_at->format('M d, Y') }}</td>

                                    {{-- Actions (WITH VIEW BUTTON) --}}
                                    <td class="px-6 py-5 text-right" x-data>
                                        <div class="flex justify-end items-center gap-2">
                                            
                                            {{-- VIEW BUTTON --}}
                                            <a href="{{ route('admin.clients.show', $user->id) }}" 
                                               class="h-9 w-9 rounded-lg bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white transition flex items-center justify-center border border-emerald-500/30" 
                                               title="View Profile">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <button @click="$dispatch('open-modal', 'pwd-{{ $user->id }}')" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-purple-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10"><i class="fa-solid fa-key"></i></button>
                                            
                                            <a href="{{ route('admin.clients.edit', $user->id) }}" class="h-9 w-9 rounded-lg bg-slate-700/50 hover:bg-blue-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10"><i class="fa-solid fa-pen"></i></a>
                                            
                                            {{-- [Status Forms logic same as before...] --}}
                                        </div>
                                        {{-- [Password Modal logic same as before...] --}}
                                        <x-modal name="pwd-{{ $user->id }}">
                                            <div class="p-6 bg-slate-900 border border-white/20 rounded-2xl text-white text-left">
                                                <h2 class="text-xl font-bold mb-4">Reset Password</h2>
                                                <form method="POST" action="{{ route('admin.users.credentials.update', $user->id) }}">
                                                    @csrf @method('PATCH')
                                                    <div class="mb-4"><label class="block text-xs font-bold text-cyan-300 mb-1">New Password</label><input type="password" name="password" class="w-full bg-slate-800 border-slate-600 rounded-xl text-white"></div>
                                                    <div class="mb-6"><label class="block text-xs font-bold text-cyan-300 mb-1">Confirm</label><input type="password" name="password_confirmation" class="w-full bg-slate-800 border-slate-600 rounded-xl text-white"></div>
                                                    <div class="flex justify-end"><button class="px-6 py-2 bg-purple-600 hover:bg-purple-500 rounded-lg font-bold">Update</button></div>
                                                </form>
                                            </div>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-20 text-center text-white">No clients found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-white/10">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>