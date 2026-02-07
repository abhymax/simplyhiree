<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20 animate-pulse"></div>
        <div class="absolute bottom-0 left-0 w-96 h-96 bg-indigo-600 rounded-full mix-blend-screen filter blur-[150px] opacity-20"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-purple-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Partner Network</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Manage agencies, recruiters, and freelancers.</p>
                </div>
                
                <div class="mt-4 md:mt-0 flex items-center gap-4">
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

            {{-- MAIN GLASS CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- 🔍 FILTER BAR --}}
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

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-purple-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Partner Name</th>
                                <th class="px-6 py-5">Type</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Joined On</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($users as $user)
                                <tr class="hover:bg-white/5 transition duration-200 cursor-default group">
                                    {{-- CLICKABLE NAME --}}
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

                                    {{-- Type (NULL SAFE) --}}
                                    <td class="px-6 py-5">
                                        @php $type = optional($user->partnerProfile)->company_type ?? 'Unknown'; @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-purple-500/20 text-purple-300 border border-purple-500/30">
                                            {{ $type }}
                                        </span>
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
                                <tr><td colspan="5" class="px-6 py-20 text-center text-slate-400">No partners found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 border-t border-white/10">{{ $users->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>