<x-app-layout>
    {{-- FULL PAGE DEEP BLUE WRAPPER --}}
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-blue-900 to-indigo-950 -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-10 relative">
        
        {{-- Background Glows --}}
        <div class="absolute top-0 center-0 w-full h-96 bg-emerald-600/10 rounded-full mix-blend-screen filter blur-[100px] opacity-30 animate-pulse"></div>

        <div class="relative z-10 max-w-7xl mx-auto">
            
            {{-- HEADER --}}
            <div class="flex flex-col md:flex-row justify-between items-end mb-8 border-b border-white/10 pb-6">
                <div>
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-emerald-300 hover:text-white mb-2 transition-colors text-sm font-bold tracking-wide uppercase">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                    </a>
                    <h1 class="text-4xl font-extrabold text-white tracking-tight drop-shadow-lg">Candidate Management</h1>
                    <p class="text-blue-200 mt-1 text-lg font-medium">Manage registered job seekers.</p>
                </div>
                
                {{-- Quick Stats (FIXED: Uses 'active' and 'restricted' to match Controller) --}}
                <div class="mt-4 md:mt-0 flex gap-3">
                    <div class="bg-white/5 border border-white/10 px-5 py-2 rounded-xl text-center">
                        <span class="block text-xs text-slate-400 font-bold uppercase">Total</span>
                        <span class="text-2xl font-black text-white">{{ $counts['total'] }}</span>
                    </div>
                    <div class="bg-emerald-500/20 border border-emerald-500/30 px-5 py-2 rounded-xl text-center">
                        <span class="block text-xs text-emerald-300 font-bold uppercase">Active</span>
                        <span class="text-2xl font-black text-white">{{ $counts['active'] }}</span>
                    </div>
                    <div class="bg-rose-500/20 border border-rose-500/30 px-5 py-2 rounded-xl text-center">
                        <span class="block text-xs text-rose-300 font-bold uppercase">Restricted</span>
                        <span class="text-2xl font-black text-white">{{ $counts['restricted'] }}</span>
                    </div>
                </div>
            </div>

            @if(session('success'))
                <div class="mb-8 px-6 py-4 bg-emerald-500/20 border border-emerald-500/50 text-emerald-300 rounded-2xl font-bold flex items-center shadow-lg backdrop-blur-md">
                    <i class="fa-solid fa-circle-check mr-3 text-2xl"></i> {{ session('success') }}
                </div>
            @endif

            {{-- MAIN GLASS CONTAINER --}}
            <div class="bg-slate-900/60 backdrop-blur-xl border border-white/20 rounded-3xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- 🔍 FILTER BAR --}}
                <div class="p-6 border-b border-white/10 bg-white/5">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        
                        {{-- Search --}}
                        <div class="md:col-span-6 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fa-solid fa-magnifying-glass text-slate-400"></i>
                            </div>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Candidate Name or Email..." 
                                class="w-full pl-10 bg-slate-800 border border-slate-600 rounded-xl text-white placeholder-slate-500 focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 font-medium h-[42px]">
                        </div>

                        {{-- Status Filter --}}
                        <div class="md:col-span-4">
                            <select name="status" class="w-full bg-slate-800 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-emerald-400 font-medium h-[42px]">
                                <option value="" class="text-gray-400">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="restricted" {{ request('status') == 'restricted' ? 'selected' : '' }}>Restricted</option>
                            </select>
                        </div>

                        {{-- Actions --}}
                        <div class="md:col-span-2 flex gap-2">
                            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-2 rounded-xl font-bold shadow-lg transition h-[42px]">
                                Filter
                            </button>
                            @if(request()->anyFilled(['search', 'status']))
                                <a href="{{ route('admin.users.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white px-3 py-2 rounded-xl transition h-[42px] flex items-center justify-center">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- DATA TABLE --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-blue-950/50 text-emerald-300 uppercase font-extrabold border-b border-white/10 text-xs tracking-wider">
                            <tr>
                                <th class="px-6 py-5">Candidate Name</th>
                                <th class="px-6 py-5">Mobile</th>
                                <th class="px-6 py-5">Resume</th>
                                <th class="px-6 py-5">Status</th>
                                <th class="px-6 py-5">Joined On</th>
                                <th class="px-6 py-5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10 text-white">
                            @forelse($users as $user)
                                @php
                                    $resumePath = optional($user->profile)->resume_path ?? optional($user->candidate)->resume_path;
                                @endphp
                                <tr class="hover:bg-white/5 transition duration-200 group">
                                    
                                    {{-- Candidate Info (Clickable) --}}
                                    <td class="px-6 py-5">
                                        <div class="flex items-center gap-4">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-600 to-teal-700 flex items-center justify-center text-white font-bold text-lg border border-white/10 shadow-lg">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.users.show', $user->id) }}" target="_blank" rel="noopener noreferrer" class="font-bold text-white text-base hover:text-emerald-400 transition underline decoration-transparent hover:decoration-emerald-400">
                                                    {{ $user->name }}
                                                </a>
                                                <div class="text-xs text-slate-400 mt-0.5">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Mobile --}}
                                    <td class="px-6 py-5 text-slate-300 font-mono">
                                        {{ optional($user->profile)->phone_number ?? 'N/A' }}
                                    </td>

                                    {{-- Resume --}}
                                    <td class="px-6 py-5">
                                        @if(!empty($resumePath))
                                            <a href="{{ asset('storage/' . $resumePath) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-cyan-300 hover:text-white font-semibold underline underline-offset-2">
                                                <i class="fa-regular fa-file-lines"></i> View Resume
                                            </a>
                                        @else
                                            <span class="text-slate-400 text-xs font-semibold">Not Uploaded</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-5">
                                        @if($user->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-xs font-bold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span> Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20 text-xs font-bold">
                                                <i class="fa-solid fa-ban"></i> {{ ucfirst($user->status) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-5 text-slate-400 font-mono text-xs">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-5 text-right" x-data>
                                        <div class="flex justify-end items-center gap-2">
                                            
                                            {{-- VIEW BUTTON (This is what you asked for) --}}
                                            <a href="{{ route('admin.users.show', $user->id) }}" target="_blank" rel="noopener noreferrer" class="h-8 w-8 rounded-lg bg-emerald-600/20 hover:bg-emerald-600 text-emerald-400 hover:text-white transition flex items-center justify-center border border-emerald-500/30 shadow-md" title="View Profile">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            {{-- Password Reset --}}
                                            <button @click="$dispatch('open-modal', 'pwd-{{ $user->id }}')" 
                                                class="h-8 w-8 rounded-lg bg-slate-700/50 hover:bg-blue-600 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" 
                                                title="Reset Password">
                                                <i class="fa-solid fa-key"></i>
                                            </button>

                                            {{-- Status Toggle --}}
                                            @if($user->status !== 'active')
                                                <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="active">
                                                    <button type="submit" class="h-8 w-8 rounded-lg bg-slate-700/50 hover:bg-emerald-500 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Activate Account">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.status.update', $user->id) }}" method="POST" class="inline">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="status" value="restricted">
                                                    <button type="submit" class="h-8 w-8 rounded-lg bg-slate-700/50 hover:bg-rose-500 text-slate-300 hover:text-white transition flex items-center justify-center border border-white/10" title="Restrict Access">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        {{-- Password Modal --}}
                                        <x-modal name="pwd-{{ $user->id }}">
                                            <div class="p-6 bg-slate-900 border border-white/20 rounded-2xl text-white text-left">
                                                <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                                                    <i class="fa-solid fa-shield-halved text-emerald-400"></i> Security Update
                                                </h2>
                                                <p class="text-sm text-slate-400 mb-6">Set a new password for <strong>{{ $user->name }}</strong>.</p>
                                                
                                                <form method="POST" action="{{ route('admin.users.credentials.update', $user->id) }}">
                                                    @csrf @method('PATCH')
                                                    <div class="mb-4">
                                                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">New Password</label>
                                                        <input type="password" name="password" class="w-full bg-slate-800 border-slate-600 rounded-xl text-white" required>
                                                    </div>
                                                    <div class="mb-6">
                                                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Confirm Password</label>
                                                        <input type="password" name="password_confirmation" class="w-full bg-slate-800 border-slate-600 rounded-xl text-white" required>
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="$dispatch('close-modal', 'pwd-{{ $user->id }}')" class="px-4 py-2 text-sm font-bold text-white hover:bg-white/10 rounded-lg transition">Cancel</button>
                                                        <button class="px-6 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-lg font-bold text-sm transition shadow-lg">Update Credentials</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </x-modal>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-20 text-center text-slate-400">
                                        <div class="mb-2"><i class="fa-solid fa-users-slash text-4xl opacity-50"></i></div>
                                        No candidates found matching your filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                <div class="p-6 border-t border-white/10 bg-slate-900/80 backdrop-blur-md">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
